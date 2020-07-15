<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro do Custos ...
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');///Essa biblioteca é requerida dentro do Custos ...

$valor_dolar_dia = genericas::moeda_dia('dolar');

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
        if($cmb_periodo == 6) {//Busca no Período de 6 Meses ...
            $data_inicial   = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -180), '-');
            $data_final     = date('Y-m-d');//Sempre será a Data Atual ...
        }else if($cmb_periodo == 12) {//Busca no Período de 12 Meses (1 Ano) ...
            $data_inicial   = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -365), '-');
            $data_final     = date('Y-m-d');//Sempre será a Data Atual ...
        }else {//Exibe a opção Todos - no caso de ESP e não existe período ... 
            $data_inicial   = '';
            $data_final     = '';//Sempre será a Data Atual ...
        }
    }else {//Quando a carrega a Tela, o Default é de 6 Meses, quando ñ é passado algum parâm ...
        if(empty($data_inicial)) {
//O Período sugerido pra consulta é de 6 Meses ...
            $data_inicial = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -180), '-');
            $data_final = date('Y-m-d');//Sempre será a Data Atual ...
            $cmb_periodo = 6;
        }
    }
//Se existir Datas ...
    if(!empty($data_inicial)) $condicao_datas = " AND ov.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' ";
//Aqui eu busco todos os Itens de Pedidos que estão atrelados a este Produto ...
    $sql = "SELECT IF(c.nomefantasia = '', c.razaosocial, c.nomefantasia) AS cliente, c.id_uf, c.credito, ov.id_orcamento_venda, ov.id_cliente, ov.nota_sgd, date_format(ov.data_emissao, '%d/%m/%Y') as data_emissao, ov.prazo_a, ov.prazo_b, ov.prazo_c, ov.prazo_d, ovi.id_orcamento_venda_item, ovi.id_produto_acabado, ovi.qtde, ovi.preco_liq_final, ovi.margem_lucro, ovi.margem_lucro_estimada, pa.id_produto_acabado, pa.operacao_custo, pa.operacao, pa.peso_unitario, pa.observacao 
            FROM `produtos_acabados` pa 
            INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_produto_acabado = pa.id_produto_acabado 
            INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda 
            INNER JOIN `clientes` c ON c.id_cliente = ov.id_cliente 
            WHERE pa.`id_produto_acabado` = '$id_produto_acabado' $condicao_datas 
            ORDER BY ov.data_emissao DESC, ovi.qtde ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina, 'ajax', 'div_listar_orcamentos');
    $linhas = count($campos);
?>
<html>
<head>
<title>.:: Orçamento(s) que contém esse Produto atrelado ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<table width='90%' border='1' cellspacing='0' cellpadding='0' align='center' onmouseover='total_linhas(this)'>
<?
//Verifica se tem pelo menos um item no Orçamento que está Pendente
    if($linhas > 0) {
?>
    <tr class='linhacabecalho' align='center'>
        <td>
            Qtde <br/>ORC
        </td>
        <td>
            Qtde <br/>PED
        </td>
        <td>
            Cliente
        </td>
        <td>
            <font title='Preço Líquido Final <?=$tipo_moeda;?>'>
                P. L.<br/>Final <?=$tipo_moeda;?>
            </font>
        </td>
        <td>
            M.L.
        </td>
        <td>
            Total<br/><?=$tipo_moeda;?> Lote
        </td>
        <td>
            Tp Nota<br/> / Prazo Pgto
        </td>
        <td>
            Data de <br/>Emiss&aacute;o
        </td>
        <td>
            N.&ordm;&nbsp;Orc
        </td>
    </tr>
<?
        $vetor_logins_com_acesso_margens_lucro  = vendas::logins_com_acesso_margens_lucro();
        $id_orcamento_venda_antigo              = '';//Variável para controle das cores no Orçamento
        
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <?=segurancas::number_format($campos[$i]['qtde'], 0, '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <?
                    $sql = "SELECT SUM(qtde) AS qtde_pedido, id_pedido_venda 
                            FROM `pedidos_vendas_itens` 
                            WHERE `id_orcamento_venda_item` = '".$campos[$i]['id_orcamento_venda_item']."' GROUP BY id_pedido_venda ";
                    $campos_qtde_pedido = bancos::sql($sql);
                ?>
                <a href="javascript:nova_janela('../../../faturamento/nota_saida/itens/detalhes_pedido.php?id_pedido_venda=<?=$campos_qtde_pedido[0]['id_pedido_venda'];?>', 'PED', '', '', '', '', 450, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Detalhes de Pedido" class='link'>
                    <?=segurancas::number_format($campos_qtde_pedido[0]['qtde_pedido'], 0, '.');?>
                </a>	
            </font>
        </td>
        <td align='left'>
            <a href="javascript:nova_janela('../../../classes/pedido_vendas/relatorio_pendencias.php?id_cliente=<?=$campos[$i]['id_cliente'];?>', 'RELATORIO', '', '', '', '', 450, 900, 'c', 'c', '', '', 's', 's', '', '', '')" title='Relatório de Pendências' class='link'>
                <?=$campos[$i]['cliente'];?>
            </a>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['preco_liq_final'], 2, ',', '.');?>
        </td>
        <td>
        <?
            if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                $tx_financeira          = custos::calculo_taxa_financeira($campos[$i]['id_orcamento_venda']);
                $margem                 = custos::margem_lucro($campos[$i]['id_orcamento_venda_item'], $tx_financeira, $campos[$i]['id_uf'], $campos[$i]['preco_liq_final']);
                
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
            $preco_total_lote = $campos[$i]['preco_liq_final'] * $campos[$i]['qtde'];
            echo number_format($preco_total_lote, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['prazo_d'] > 0) $prazo_faturamento = '/'.$campos[$i]['prazo_d'];
            if($campos[$i]['prazo_c'] > 0) $prazo_faturamento = '/'.$campos[$i]['prazo_c'].$prazo_faturamento;
            if($campos[$i]['prazo_b'] > 0) {
                $prazo_faturamento = $campos[$i]['prazo_a'].'/'.$campos[$i]['prazo_b'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos[$i]['prazo_a'] == 0) ? 'À vista' : $campos[$i]['prazo_a'];
            }
            if($campos[$i]['nota_sgd'] == 'S') {
                echo '(NF) / '.$prazo_faturamento;
            }else {
                echo '(SGD) / '.$prazo_faturamento;
            }
            $total_pagina+= $preco_total_lote;
//Aki eu limpo essa variável para não dar problema quando voltar no próximo loop
            $prazo_faturamento = '';
        ?>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>
        <td>
            <a href="javascript:nova_janela('../../../vendas/pedidos/itens/detalhes_orcamento.php?id_orcamento_venda=<?=$campos[$i]['id_orcamento_venda'];?>', 'ORC', '', '', '', '', 450, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Detalhes de Orçamento" class="link">
            <?
                if($id_orcamento_venda_antigo != $campos[$i]['id_orcamento_venda']) {
//Aki significa que mudou para outro N. de Pedido e vai exibir uma nova sequência desses mesmos
                    $id_orcamento_venda_antigo = $campos[$i]['id_orcamento_venda'];
            ?>
                    <font color='red'>
                        <?=$campos[$i]['id_orcamento_venda'];?>
                    </font>
            <?
//Ainda são os mesmos Orçamentos
                }else {
                    echo $campos[$i]['id_orcamento_venda'];
                }
            ?>
            </a>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhanormal' align='right'>
        <td colspan='9' bgcolor='#CECECE'>
            <b>Total: <?=$tipo_moeda.number_format($total_pagina, 2, ',', '.');?></b>
        </td>
    </tr>
    <tr class='atencao' align='center' valign='center'>
        <td colspan='9'>
            <?=paginacao::print_paginacao('sim');?>
        </td>
    </tr>
<?
    }else {
?>
    <tr class='atencao' align='center'>
        <td>
            N&Atilde;O H&Aacute; OR&Ccedil;AMENTO(S) PENDENTE(S).
        </td>
    </tr>
<?
    }
?>
</table>
</body>
</html>