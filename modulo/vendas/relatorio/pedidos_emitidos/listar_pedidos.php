<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro do Custos ...
require('../../../../lib/custos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Vendas ...
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');//Essa Biblioteca é requirida de dentro da Biblioteca de Custos ...
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/pedidos_emitidos/pedidos_emitidos.php', '../../../../');

$valor_dolar_dia    = genericas::moeda_dia('dolar');
$impostos_federais  = genericas::variavel(34);

//Procedimento normal de quando se carrega a Tela ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_gpa_vs_emp_div  = $_POST['id_gpa_vs_emp_div'];
        $data_inicial       = $_POST['data_inicial'];
        $data_final         = $_POST['data_final'];
        $sumir_botao        = $_POST['sumir_botao'];
        $id_produto_acabado = $_POST['id_produto_acabado'];
        $passo              = $_POST['passo'];
    }else {
        $id_gpa_vs_emp_div  = $_GET['id_gpa_vs_emp_div'];
        $data_inicial       = $_GET['data_inicial'];
        $data_final         = $_GET['data_final'];
        $sumir_botao        = $_GET['sumir_botao'];
        $id_produto_acabado = $_GET['id_produto_acabado'];
        $passo              = $_GET['passo'];
    }
//Se a Combo de Período foi alterada então ...
    if(!empty($cmb_periodo)) {
        if($cmb_periodo == 3) {//Busca no Período de 3 Meses ...
            $data_inicial = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -90), '-');
        }else if($cmb_periodo == 6) {//Busca no Período de 6 Meses ...
            $data_inicial   = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -180), '-');
        }else if($cmb_periodo == 12) {//Busca no Período de 12 Meses (1 Ano) ...
            $data_inicial   = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -365), '-');
        }else {//Exibe a opção Todos - no caso de ESP e não existe período ... 
            $data_inicial   = '';
        }
    }else {//Quando a carrega a Tela, o Default é de 6 Meses, quando ñ é passado algum parâm ...
        if(empty($data_inicial)) {
//O Período sugerido pra consulta é de 6 Meses ...
            $data_inicial   = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -180), '-');
            $cmb_periodo    = 6;
        }
    }
    
    /*Se a soma dos Itens Pendentes + Separados > '0' que equivale aos campos -> 
    "pvi.`qtde` - pvi.`qtde_pendente` - pvi.`vale` - pvi.`qtde_faturada`" ...*/
    if($cmb_forma_listagem == 'A') $condicao_forma_listagem = " AND (pvi.`qtde_pendente` + (pvi.`qtde` - pvi.`qtde_pendente` - pvi.`vale` - pvi.`qtde_faturada`) > '0') ";
//Se existir Datas ...
    if(!empty($data_inicial)) $condicao_datas = " AND pv.`data_emissao` >= '$data_inicial' ";
//Utilizo essa variável mais abaixo para auxiliar nos cálculos ...
    $data_atual_mais_um = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), 1), '-');
    
    $vetor_pa_atrelados = custos::pas_atrelados($id_produto_acabado);
    $id_pas_atrelados   = implode(',', $vetor_pa_atrelados);
    
    //Aqui eu busco todos os Itens de Pedidos que estão atrelados a este Produto passado por parâmetro e aos seus atrelados ...
    $sql = "SELECT IF(c.`nomefantasia` = '', c.`razaosocial`, c.`nomefantasia`) AS cliente, c.`id_pais`, 
            c.`id_uf`, c.`credito`, ov.`id_orcamento_venda`, ov.`id_cliente`, ov.`finalidade`, 
            ov.`artigo_isencao`, ovi.`id_orcamento_venda_item`, ovi.`id_produto_acabado`, 
            ovi.`id_produto_acabado_discriminacao`, pa.`referencia`, pv.`id_empresa`, pv.`id_pedido_venda`, 
            pv.`faturar_em`, pv.`vencimento1`, pv.`vencimento2`, pv.`vencimento3`, pv.`vencimento4`, 
            pv.`liberado`, pvi.`id_pedido_venda_item`, pvi.`qtde`, pvi.`vale`, pvi.`qtde_pendente`, 
            pvi.`qtde_faturada`, pvi.`preco_liq_final`, pvi.`margem_lucro`, pvi.`margem_lucro_estimada`, 
            pvi.`status` AS status_item, 
            IF(c.`id_pais` = 31, (pvi.`qtde` * pvi.`preco_liq_final`), (pvi.`qtde` * pvi.`preco_liq_final` * $valor_dolar_dia)) AS total, 
            IF(c.`id_pais` = 31, (pvi.`qtde_pendente` * pvi.`preco_liq_final`), (pvi.`qtde_pendente` * pvi.`preco_liq_final` * $valor_dolar_dia)) AS total_pendente, 
            IF(c.`id_pais` = 31, 'R$ ', 'U$ ') AS tipo_moeda 
            FROM `orcamentos_vendas_itens` ovi 
            INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` AND ovi.`status` > '0' 
            INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_orcamento_venda_item = ovi.id_orcamento_venda_item 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
            INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
            WHERE pvi.id_produto_acabado IN ($id_pas_atrelados) $condicao_datas $condicao_forma_listagem 
            ORDER BY pv.`faturar_em` DESC, pvi.`qtde` ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina, 'ajax', 'div_listar_pedidos');
    $linhas = count($campos);
?>
<html>
<head>
<title>.:: Pedido(s) que contém esse Produto atrelado ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<body>
<table width='90%' border='1' cellspacing='0' cellpadding='0' align='center' onmouseover='total_linhas(this)'>
<?
//Verifica se tem pelo menos um item no Pedido que está Pendente
	if($linhas > 0) {
?>
    <tr class='linhacabecalho' align='center'>
        <td rowspan='2'>
            Refer&ecirc;ncia
        </td>
        <td colspan='6'>
            Quantidade
        </td>
        <td rowspan='2'>
            Cliente / Cr&eacute;dito
        </td>
        <td rowspan='2'>
            <font title='Preço Líquido Final R$' style='cursor:help'>
                P. L.<br/>Final
            </font>
        </td>
        <td rowspan='2'>
            M.L.
        </td>
        <td rowspan='2'>
            Total<br/> Lote R$
        </td>
        <td rowspan='2'>
            Total<br/> Pend&ecirc;ncia R$
        </td>
        <td rowspan='2'>
            Emp / Tp Nota<br/> / Prazo Pgto
        </td>
        <td rowspan='2'>
            Faturar em
        </td>
        <td rowspan='2'>
            N.&ordm;&nbsp;Ped
        </td>
        <td rowspan='2'>
            Pre&ccedil;o NF SP <br/>30 ddl R$
        </td>
        <td rowspan='2'>
            Total Lote NF SP <br/>30 ddl R$
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            Ini
        </td>
        <td>
            Fat
        </td>
        <td>
            Sep
        </td>
        <td>
            Pend
        </td>
        <td>
            Vale
        </td>
        <td>
            E.D.
        </td>
    </tr>
<?
        $vetor_logins_com_acesso_margens_lucro  = vendas::logins_com_acesso_margens_lucro();
        $id_pedido_venda_antigo                 = '';//Variável para controle das cores no Orçamento
        
        for ($i = 0; $i < $linhas; $i++) {
            $vetor = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado']);
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0, 1, 1, $campos[$i]['id_produto_acabado_discriminacao']);?>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <?=segurancas::number_format($campos[$i]['qtde'], 0, '.');?>
            </font>
        </td>
        <td>
            <?=segurancas::number_format($campos[$i]['qtde_faturada'], 0, '.');?>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
            <?
                $separado = $campos[$i]['qtde'] - $campos[$i]['qtde_pendente'] - $campos[$i]['vale'] - $campos[$i]['qtde_faturada'];
                echo segurancas::number_format($separado, 0, '.');
            ?>
            </font>
        </td>
        <td>
        <?
            if($campos[$i]['qtde_pendente'] > $vetor[3]) {
                echo '<font color="red"><b>'.segurancas::number_format($campos[$i]['qtde_pendente'], 0, '.').'</b></font>';
            }else {
                echo segurancas::number_format($campos[$i]['qtde_pendente'], 0, '.');
            }
        ?>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <?=segurancas::number_format($campos[$i]['vale'], 0, '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <?=segurancas::number_format($vetor[3], 0, '.');?>
            </font>
        </td>
        <td align='left'>
            <a href="javascript:nova_janela('../../../classes/pedido_vendas/relatorio_pendencias.php?id_cliente=<?=$campos[$i]['id_cliente'];?>', 'RELATORIO', '', '', '', '', 450, 900, 'c', 'c', '', '', 's', 's', '', '', '')" title='Relatório de Pendências' class='link'>
            <?
                echo $campos[$i]['cliente'].' / '.$campos[$i]['credito'];
                if($campos[$i]['liberado'] == 0) {
                    echo "<font color='red' title='Não Liberado' style='cursor:help'>
                        <b>Ñ LIB</b>
                    </font>";
                }
            ?>
            </a>
        </td>
        <td align='right'>
            <?=$campos[$i]['tipo_moeda'].number_format($campos[$i]['preco_liq_final'], 2, ',', '.');?>
        </td>
        <td>
        <?
            if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                $tx_financeira          = custos::calculo_taxa_financeira($campos[$i]['id_orcamento_venda']);
                $margem                 = custos::margem_lucro($campos[$i]['id_orcamento_venda_item'], $tx_financeira, $id_uf_cliente, $campos[$i]['preco_liq_final']);
                
                $cor_instantanea        = ($margem[0] < 0) ? 'red' : '#E8E8E8';//$margem[0] valor real da margem
                $cor_gravada            = ($campos[$i]['margem_lucro'] < 0) ? 'red': '#E8E8E8';
                $cor_estimada           = ($campos[$i]['margem_lucro_estimada'] < 0) ? 'red': '#E8E8E8';
        ?>
            <!--************************************************************************-->
            <!--A folha de estilo fica aqui dentro do Loop porque as cores de fonte 
            dos IDs se comportaram de acordo com o que foi definido acima pelo PHP ...-->
            <style type='text/css'>
                #id_ml_instantanea<?=$i;?>::-moz-selection {
                    background:#A9A9A9;
                    color:<?=$cor_instantanea;?>
                }
                #id_ml_gravada<?=$i;?>::-moz-selection {
                    background:#A9A9A9;
                    color:<?=$cor_gravada;?>
                }
                #id_ml_estimada<?=$i;?>::-moz-selection {
                    background:#A9A9A9;
                    color:<?=$cor_estimada;?>
                }
            </style>
            <!--************************************************************************-->
            
            <font color="<?=$cor_instantanea;?>" id='id_ml_instantanea<?=$i;?>' title='Margem de Lucro Instant&acirc;nea' style='cursor:help'><br/>
                <?='ML='.$margem[1];//Valor Descritivo da Margem ...?>
            </font>
            <font color="<?=$cor_gravada;?>" id='id_ml_gravada<?=$i;?>' title='Margem de Lucro Instant&acirc;nea' style='cursor:help'>
                <br/><?='MLG='.number_format($campos[$i]['margem_lucro'], 2, ',', '.');?>
            </font>
            <font color="<?=$cor_estimada;?>" id='id_ml_estimada<?=$i;?>' title='Margem de Lucro Estimada' style='cursor:help'>
                <br/><?='MLEst='.number_format($campos[$i]['margem_lucro_estimada'], 2, ',', '.');?>
            </font>
        <?
            }
        ?>
        </td>
        <td align='right'>
        <?
            $preco_total_lote = $campos[$i]['total'];
            echo number_format($preco_total_lote, 2, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
            $preco_total_lote_pendente 	= $campos[$i]['total_pendente'];
            echo number_format($preco_total_lote_pendente, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[$i]['vencimento4'];
            if($campos[$i]['vencimento3'] > 0) $prazo_faturamento= '/'.$campos[$i]['vencimento3'].$prazo_faturamento;
            if($campos[$i]['vencimento2'] > 0) {
                $prazo_faturamento = $campos[$i]['vencimento1'].'/'.$campos[$i]['vencimento2'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos[$i]['vencimento1'] == 0) ? 'À vista' : $campos[$i]['vencimento1'];
            }

            if($campos[$i]['id_empresa'] == 1) {
                $nomefantasia = 'ALBA - NF';
                echo '(A - NF) / '.$prazo_faturamento;
            }else if($campos[$i]['id_empresa']==2) {
                $nomefantasia = 'TOOL - NF';
                echo '(T - NF) / '.$prazo_faturamento;
            }else if($campos[$i]['id_empresa']==4) {
                $nomefantasia = 'GRUPO - SGD';
                echo '(G - SGD) / '.$prazo_faturamento;
            }
//Aki eu limpo essa variável para não dar problema quando voltar no próximo loop
            $prazo_faturamento = '';
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['faturar_em'] != '0000-00-00') {//Coloca no formato de Data
                if($campos[$i]['faturar_em'] > $data_atual_mais_um) {
                    echo '<font color="red">'.data::datetodata($campos[$i]['faturar_em'], '/').'</font>';
                }else {
                    echo '<font color="green">'.data::datetodata($campos[$i]['faturar_em'], '/').'</b></font>';
                }
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td>
            <a href="javascript:nova_janela('../../../faturamento/nota_saida/itens/detalhes_pedido.php?id_pedido_venda=<?=$campos[$i]['id_pedido_venda'];?>', 'PED', '', '', '', '', 450, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Detalhes de Pedido" class="link">
            <?
                if($id_pedido_venda_antigo != $campos[$i]['id_pedido_venda']) {
//Aki significa que mudou para outro N. de Pedido e vai exibir uma nova sequência desses mesmos
                    $id_pedido_venda_antigo = $campos[$i]['id_pedido_venda'];
            ?>
                <font color='red'>
                    <?=$campos[$i]['id_pedido_venda'];?>
                </font>
            <?
//Ainda são os mesmos Orçamentos
                }else {
                    echo $campos[$i]['id_pedido_venda'];
                }
            ?>
            </a>
        </td>
        <td align='right'>
        <?
            $valores_preco_venda_media = vendas::calculo_preco_venda_medio_nf_sp_30ddl_rs($campos[$i]['id_pedido_venda_item'], 'PVI', $cmb_forma_listagem);
            echo number_format($valores_preco_venda_media['preco_NF_SP_30_ddl'], 2, ',', '.');
        ?>
        </td>
        <td align='right'>
            <?=number_format($valores_preco_venda_media['total_lote_NF_SP_30_ddl'], 2, ',', '.');?>
        </td>
    </tr>
    <?
//Se o Cliente estiver com o crédito OK, então realiza os cálculos
            if($campos[$i]['credito'] == 'A' || $campos[$i]['credito'] == 'B') {
    //Se a Data de Programação for até a Data de Amanhã então é faturável
                if($campos[$i]['faturar_em'] <= $data_atual_mais_um) {
                    if($campos[$i]['qtde_pendente'] >= $vetor[3]) {
                        $resultado = $vetor[3];
                    }else {
                        $resultado = $campos[$i]['qtde_pendente'];
                    }
                }
            }
        }
?>
    <tr class='atencao' align='center'>
        <td colspan='17'>
            <?=paginacao::print_paginacao('sim');?>
        </td>
    </tr>
<?
    }else {
?>
    <tr class='atencao' align='center'>
        <td>
            N&Atilde;O H&Aacute; PEDIDOS(S) PENDENTE(S).
        </td>
    </tr>
<?
    }
?>
</table>
</body>
</html>