<?
require('../../../lib/segurancas.php');
require('../../../lib/calculos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro do Custos ...
require('../../../lib/custos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Vendas ...
require('../../../lib/data.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/financeiros.php');
require('../../../lib/intermodular.php');
require('../../../lib/vendas.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro do Custo ...
session_start('funcionarios');

/*Eu tenho esse desvio aki para não verificar a sessão desse arkivo, faço isso pq esse arquivo aki é um 
pop-up em outras partes do sistema e se eu não fizer esse desvio dá erro de permissão*/
if($nao_verificar_sessao != 1) segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../');

//Busca o nome do Cliente, o Contato + o id_cliente_contato p/ poder buscar com + detalhes os dados do cliente
$sql = "SELECT `cod_cliente`, `id_pais`, `id_uf`, IF(`nomefantasia` = '', `razaosocial`, `nomefantasia`) AS cliente, `credito`, 
        `cidade`, `tipo_faturamento`, `cod_suframa` 
        FROM `clientes` 
        WHERE `id_cliente` = '$_GET[id_cliente]' LIMIT 1 ";
$campos_cliente     = bancos::sql($sql);
$id_pais            = $campos_cliente[0]['id_pais'];
$id_uf_cliente      = $campos_cliente[0]['id_uf'];
$cliente            = $campos_cliente[0]['cliente'];
$tipo_moeda         = ($id_pais != 31) ? 'U$ ' : 'R$ ';//Verifica se o Cliente é do Tipo Internacional ...
$data_atual_mais_um = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), 1), '-');
?>
<html>
<head>
<title>.:: Relatório de Pendências ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<?
//Aqui eu busco todos os Itens de Pedidos que estão Pendentes p/ este Cliente
$sql = "SELECT ovi.`id_orcamento_venda_item`, ovi.`id_orcamento_venda`, 
        ovi.`id_produto_acabado_discriminacao`, pv.`id_cliente`, pv.`id_empresa`, pv.`id_pedido_venda`, 
        pv.`vencimento1`, pv.`vencimento2`, pv.`vencimento3`, pv.`vencimento4`, pv.`faturar_em`, 
        pvi.`id_pedido_venda_item`, pvi.`qtde`, pvi.`vale`, pvi.`qtde_pendente`, pvi.`qtde_faturada`, 
        pvi.`preco_liq_final`, pvi.`margem_lucro`, pvi.`margem_lucro_estimada`, 
        pvi.`status` AS status_item, pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, 
        pa.`operacao_custo`, pa.`operacao`, pa.`peso_unitario`, pa.`observacao` 
        FROM `pedidos_vendas` pv 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`status` < '2' 
        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
        WHERE pv.`id_cliente` = '$_GET[id_cliente]' ORDER BY pv.`id_empresa`, pv.`id_pedido_venda` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
//Verifica se tem pelo menos um item no Pedido que está Pendente
if($linhas > 0) {
?>
<table width='95%' border='1' cellspacing='0' cellpadding='0' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
            Relatório de Pedido(s) Pendente(s)
        </td>
    </tr>
    <tr class='linhadestaque' style='cursor:pointer'>
        <td colspan='2' align='left'>
            <a href="javascript:nova_janela('../cliente/alterar.php?passo=1&id_cliente=<?=$id_cliente;?>&detalhes=1', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" title="Alterar Cliente" class="link">
                <font color='yellow'>
                    Cliente:
                    <font color='#FFFFFF'>
                    <?
                        echo $campos_cliente[0]['cod_cliente'].' - '.$cliente.' / '.$campos_cliente[0]['cidade'];
                        
                        if($id_uf_cliente > 0) {
                            $sql = "SELECT `sigla` 
                                    FROM `ufs`
                                    WHERE `id_uf` = '$id_uf_cliente' LIMIT 1 ";
                            $campos_uf = bancos::sql($sql);
                            echo ' / '.$campos_uf[0]['sigla'];
                        }else {
                            $sql = "SELECT `pais` 
                                    FROM `paises` 
                                    WHERE `id_pais` = '$id_pais' LIMIT 1 ";
                            $campos_pais = bancos::sql($sql);
                            echo ' / '.$campos_pais[0]['pais'];
                        }
                    ?>
                    </font>
                </font>
                <img src="../../../imagem/propriedades.png" title="Detalhes de Cliente" alt="Detalhes de Cliente" style="cursor:pointer" border="0">
            </a>
            <font color='yellow' size='2'>
                / Crédito:
            </font>
            <?=$campos_cliente[0]['credito'];?>
            <font color='yellow' size='2'>
                / Tipo de Faturamento:
            </font>
            <?
                if($campos_cliente[0]['tipo_faturamento'] == 1) {
                    echo 'TUDO PELA ALBAFER';
                }else if($campos_cliente[0]['tipo_faturamento'] == 2) {
                    echo 'TUDO PELA TOOL MASTER';
                }else if($campos_cliente[0]['tipo_faturamento'] == 'Q') {
                    echo 'QUALQUER EMPRESA';
                }else if($campos_cliente[0]['tipo_faturamento'] == 'S') {
                    echo 'SEPARADAMENTE';
                }
            ?>
        </td>
    </tr>
</table>
<table width='95%' border='1' cellspacing='0' cellpadding='0' onmouseover='total_linhas(this)' align='center'>
    <tr class='linhadestaque' align='center'>
        <td colspan='6'>
            Quantidade
        </td>
        <td rowspan='2'>
            Produto
        </td>
        <td rowspan='2'>
            <font title="Preço Líquido Final <?=$tipo_moeda;?>" style='cursor:help'>P. L.<br>
                Final <?=$tipo_moeda;?>
            </font>
        </td>
        <td rowspan='2'>
            IPI<br>%
        </td>
        <td rowspan='2'>
            <font title='Total <?=$tipo_moeda;?> Lote'>
                Total<br><?=$tipo_moeda;?> Lote
            </font>
        </td>
        <td rowspan='2'>
            <font title='Empresa / Tipo de Nota / Prazo de Pagamento' style='cursor:help'>
                Emp / Tp Nota<br> / Prazo Pgto
            </font>
        </td>
        <td rowspan='2'>
            Faturar em
        </td>
        <td rowspan='2'>
            <font title='N.º do Pedido' style='cursor:help'>
                N.º&nbsp;Ped
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <font title='Inicial' style='cursor:help'>
                Ini
            </font>
        </td>
        <td>
            Fat
        </td>
        <td>
            <font title='Separada'>
                Sep
            </font>
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
    $vetor_logins_com_acesso_margens_lucro      = vendas::logins_com_acesso_margens_lucro();
    $id_pedido_venda_antigo                     = '';//Variável para controle das cores no Orçamento
    $id_empresa_corrente                        = $campos[0]['id_empresa'];
    
    for($i = 0; $i < $linhas; $i++) {
        $vetor = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado']);
?>
    <tr class='linhanormal' align='center'>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <?=segurancas::number_format($campos[$i]['qtde'], 0, '.');?>
            </font>
        </td>
        <td>
<?
        //Só aparecerá o Link do que já foi faturado, se tiver pelo menos 1 item q já está em NF
        if($campos[$i]['qtde_faturada'] > 0) {
?>
            <a href="javascript:nova_janela('../faturamento/faturado.php?id_pedido_venda_item=<?=$campos[$i]['id_pedido_venda_item'];?>', 'FATURADO', '', '', '', '', 350, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Faturamento" class="link">
                <?=segurancas::number_format($campos[$i]['qtde_faturada'], 0, '.');?>
            </a>
<?
        }else {
            echo segurancas::number_format($campos[$i]['qtde_faturada'], 0, '.');
        }
?>
        </td>
        <td>
        <?
            $separado = $campos[$i]['qtde'] - $campos[$i]['qtde_pendente'] - $campos[$i]['vale'] - $campos[$i]['qtde_faturada'];
            echo segurancas::number_format($separado, 0, '.');
        ?>
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
            <?=segurancas::number_format($campos[$i]['vale'], 0, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($vetor[3], 0, '.');?>
        </td>
        <td align='left'>
            <a href="javascript:nova_janela('../estoque/visualizar_estoque.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>', 'ESTOQUE', '', '', '', '', 300, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Estoque'>
            <?
//Produto Normal
                if($campos[$i]['referencia'] != 'ESP') {
                    echo intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0, '', '', $campos[$i]['id_produto_acabado_discriminacao']);
                }else {
//Quando for ESP printa de Verde ...
            ?>
                <font color="green">
                    <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0, '', '', $campos[$i]['id_produto_acabado_discriminacao']);?>
                </font>
            <?
                }
            ?>
            </a>
            <?
                /*Somente esses funcionários poderão desempenhar essa função: 
                Agueda 32, Roberto 62, Dárcio 98 porque programa e Nishimura 136 ...*/
                if($_SESSION['id_funcionario'] == 32 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 136) {
            ?>
            &nbsp;
            <img src = '../../../imagem/menu/excluir.png' border='0' title='Excluir Item(ns)' alt='Excluir Item(ns)' onclick="nova_janela('../../vendas/pedidos/itens/excluir_itens.php?id_pedido_venda=<?=$campos[$i]['id_pedido_venda'];?>', 'POP', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')">
            <?
                }
            ?>
        </td>
        <td align='right'>
        <?
            echo number_format($campos[$i]['preco_liq_final'], 2, ',', '.');
            
            if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                $tx_financeira  = custos::calculo_taxa_financeira($campos[$i]['id_orcamento_venda']);
                $margem         = custos::margem_lucro($campos[$i]['id_orcamento_venda_item'], $tx_financeira, $id_uf_cliente, $campos[$i]['preco_liq_final']);
                
                $cor_instantanea    = ($margem[0] < 0) ? 'red' : '#E8E8E8';//$margem[0] valor real da margem
                $cor_gravada        = ($campos_itens[$i]['margem_lucro'] < 0) ? 'red': '#E8E8E8';
                $cor_estimada       = ($campos_itens[$i]['margem_lucro_estimada'] < 0) ? 'red': '#E8E8E8';
        ?>
                <br/>
                <font color='<?=$cor_instantanea;?>' title='Margem de Lucro Instant&acirc;nea' style='cursor:help'>
                    <?='ML='.$margem[1];//Valor Descritivo da Margem ...?>
                </font>
                <br/>
                <font color='<?=$cor_gravada;?>' title='Margem de Lucro Instant&acirc;nea' style='cursor:help'>
                    <?='MLG='.number_format($campos[$i]['margem_lucro'], 2, ',', '.');?>
                </font>
                <br/>
                <font color='<?=$cor_estimada;?>' title='Margem de Lucro Estimada' style='cursor:help'>
                    <?='MLEst='.number_format($campos[$i]['margem_lucro_estimada'], 2, ',', '.');?>
                </font>
        <?
            }
        ?>
        </td>
        <td align='right'>
        <?
//Quando o país é do Tipo Internacional, ou o Pedido for do Tipo SGD ou o Cliente possuir suframa, então não existe IPI ...
            $nota_sgd = ($campos[$i]['id_empresa'] == 4) ? 'S' : 'N';

            if($id_pais != 31 || $nota_sgd == 'S' || !empty($campos_cliente[0]['cod_suframa']) || $campos[$i]['operacao'] == 1) {
                $ipi = 'S/IPI';
                $id_classific_fiscal = '';
            }else { //Aqui tem que buscar a Classificação Fiscal para poder buscar o IPI
                $sql = "SELECT cf.ipi, cf.id_classific_fiscal 
                        FROM `produtos_acabados` pa 
                        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                        INNER JOIN `grupos_pas` gp ON gp.id_grupo_pa = ged.id_grupo_pa 
                        INNER JOIN `familias` fm ON fm.id_familia = gp.id_familia 
                        INNER JOIN `classific_fiscais` cf ON cf.id_classific_fiscal = fm.id_classific_fiscal 
                        WHERE pa.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1";
                $campos_temp = bancos::sql($sql);
                if(count($campos_temp) > 0) {
                    $ipi = number_format($campos_temp[0]['ipi'], 1, ',', '.');
                    $id_classific_fiscal=$campos_temp[0]['id_classific_fiscal'];
                }else {
                    $id_classific_fiscal= '';
                    $ipi = '&nbsp;';
                }
            }
            echo $ipi;
        ?>
        </td>
        <td align='right'>
        <?
            $preco_total_lote = $campos[$i]['preco_liq_final'] * ($campos[$i]['qtde'] - $campos[$i]['qtde_faturada']);
            $total_geral+= $preco_total_lote;
            echo number_format($preco_total_lote, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[$i]['vencimento4'];
            if($campos[$i]['vencimento3'] > 0) $prazo_faturamento= '/'.$campos[$i]['vencimento3'].$prazo_faturamento;
            if($campos[$i]['vencimento2'] > 0) {
                $prazo_faturamento= $campos[$i]['vencimento1'].'/'.$campos[$i]['vencimento2'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos[$i]['vencimento1'] == 0) ? 'À vista' : $campos[$i]['vencimento1'];
            }
            if($campos[$i]['id_empresa'] == 1) {
                $nomefantasia = 'ALBA - NF';
                $total_empresa+= $preco_total_lote;

                echo '(A - NF) / '.$prazo_faturamento;
            }else if($campos[$i]['id_empresa'] == 2) {
                $nomefantasia = 'TOOL - NF';
                $total_empresa+= $preco_total_lote;

                echo '(T - NF) / '.$prazo_faturamento;
            }else if($campos[$i]['id_empresa'] == 4) {
                $nomefantasia = 'GRUPO - SGD';
                $total_empresa+= $preco_total_lote;

                echo '(G - SGD) / '.$prazo_faturamento;
            }else {
                echo 'Erro';
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
        <?
            $url = "javascript:nova_janela('../../faturamento/nota_saida/itens/detalhes_pedido.php?veio_faturamento=1&id_pedido_venda=".$campos[$i]['id_pedido_venda']."', 'PED', '', '', '', '', 440, 780, 'c', 'c', '', '', 's', 's', '', '', '')";
        ?>
            <a href="<?=$url;?>" title="Visualizar Detalhes de Pedido" class="link">
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
    </tr>
    <?
//Se a Data de Programação for até a Data de Amanhã então é faturável
        if($campos[$i]['faturar_em'] <= $data_atual_mais_um) {
            if($campos[$i]['qtde_pendente'] >= $vetor[3]) {
                $resultado = $vetor[3];
            }else {
                $resultado = $campos[$i]['qtde_pendente'];
            }
            $valor_faturavel+= ($separado + $campos[$i]['vale'] + $resultado) * ($campos[$i]['preco_liq_final']);
        }
        if($id_empresa_corrente != $campos[$i+1]['id_empresa']) {
    ?>
    <tr class='linhanormal'>
        <td colspan='7' bgcolor='#CECECE' align='left'>
            <font color='green'>
                <b>Valor Faturável:</b>
                <?
                    echo 'R$ '.number_format($valor_faturavel, 2, ',', '.');
                    
                    if($campos_cliente[0]['credito'] == 'C' || $campos_cliente[0]['credito'] == 'D') echo ' - <font color="red"><b>Cliente com Crédito '.$campos_cliente[0]['credito'].'</b></font>';
                ?>
            </font>
        </td>
        <td colspan='6' bgcolor='#CECECE' align='right'>
            <b>Total: <?=$nomefantasia.' => '.$tipo_moeda.number_format($total_empresa, 2, ',', '.');?></b>
        </td>
    </tr>
    <?
            $valor_faturavel    = 0;
            $total_empresa      = 0;
            $id_empresa_corrente = $campos[$i + 1]['id_empresa'];
        }
    }
?>
    <tr class='linhadestaque' align='right'>
        <td colspan='10'>
            <font color='yellow'>Dólar Dia:</font>
            <?=number_format(genericas::moeda_dia('dolar'), 4, ',', '.');?>
        </td>
        <td colspan='3' align='right'>
            <font color='yellow'>TOTAL GERAL: </font>
            <?=$tipo_moeda.number_format($total_geral, 2, ',', '.');?>
        </td>
    </tr>
<?
}

$data_emissao_menor_60_dias = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -60), '-');
//Relatório de Orçamento(s) em Aberto com Data de Emissão <= 60 dias
//Aqui eu busco todos os Itens de Orçamento que estão Pendentes p/ este Cliente
$sql = "SELECT c.id_cliente, cc.nome, ov.nota_sgd, ov.prazo_a, ov.prazo_b, ov.prazo_c, ov.prazo_d, ovi.id_orcamento_venda, ovi.qtde, ovi.preco_liq_final, pa.id_produto_acabado, pa.referencia, pa.discriminacao, pa.operacao_custo, pa.operacao, pa.peso_unitario, pa.observacao 
        FROM `clientes` c 
        INNER JOIN `clientes_contatos` cc ON cc.id_cliente = c.id_cliente 
        INNER JOIN `orcamentos_vendas` ov ON ov.id_cliente_contato = cc.id_cliente_contato AND ov.`data_emissao` >= '$data_emissao_menor_60_dias' AND ov.status < '2' 
        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_orcamento_venda = ov.id_orcamento_venda AND ovi.`status` < '2' 
        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado 
        WHERE c.`id_cliente` = '$_GET[id_cliente]' ORDER BY ov.id_orcamento_venda ";
$campos = bancos::sql($sql);
$linhas = count($campos);
//Verifica se tem pelo menos um item no Orçamento que está Pendente
if($linhas > 0) {
?>
<table width='95%' border='1' cellspacing='0' cellpadding='0' onmouseover="total_linhas(this)" align='center'>
    <tr class="linhacabecalho" align="center">
        <td colspan='8'>
            Relatório de Orçamento(s) em Aberto com Data de Emissão <= 60 dias
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Qtde
        </td>
        <td>
            Produto
        </td>
        <td>
            <font title="Preço Líquido Final <?=$tipo_moeda;?>" style='cursor:help'>
                P. L.<br> Final <?=$tipo_moeda;?>
            </font>
        </td>
        <td>
            IPI<br>%
        </td>
        <td>
            <font title='Total <?=$tipo_moeda;?> Lote' style='cursor:help'>
                Total<br><?=$tipo_moeda;?> Lote
            </font>
        </td>
        <td>
            <font title='Tipo de Nota / Prazo de Pagamento' style='cursor:help'>
                Tp Nota<br> / Prazo Pgto
            </font>
        </td>
        <td>
            Contato
        </td>
        <td>
            <font title="N.º do Orçamento" style='cursor:help'>
                N.º&nbsp;Orc
            </font>
        </td>
    </tr>
<?
    $total_geral = 0;
    $id_orcamento_venda_antigo = '';//Variável para controle das cores no Orçamento
    for ($i = 0; $i < $linhas; $i++) {
        $vetor = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado']);
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=segurancas::number_format($campos[$i]['qtde'], 0, '.');?>
        </td>
        <td align='left'>
            <a href="javascript:nova_janela('../estoque/visualizar_estoque.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>', 'ESTOQUE', '', '', '', '', 300, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Estoque" class="link">
                <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0, '', '', $campos[$i]['id_produto_acabado_discriminacao']);?>
            </a>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['preco_liq_final'], 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
/*Quando o país é do Tipo Internacional, ou o Pedido for do Tipo SGD ou o Cliente possuir suframa, 
então não existe IPI*/
            if($id_pais != 31 || $campos[$i]['nota_sgd'] == 'S' || !empty($campos_cliente[0]['cod_suframa']) || $campos[$i]['operacao'] == 1) {
                $ipi = 'S/IPI';
                $id_classific_fiscal = '';
            }else { //Aqui tem que buscar a Classificação Fiscal para poder buscar o IPI
                $sql = "SELECT cf.ipi, cf.id_classific_fiscal 
                        FROM `produtos_acabados` pa 
                        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                        INNER JOIN `grupos_pas` gp ON gp.id_grupo_pa = ged.id_grupo_pa 
                        INNER JOIN `familias` fm ON fm.id_familia = gp.id_familia 
                        INNER JOIN `classific_fiscais` cf ON cf.id_classific_fiscal = fm.id_classific_fiscal 
                        WHERE pa.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1";
                $campos_temp = bancos::sql($sql);
                if(count($campos_temp) > 0) {
                    $ipi = number_format($campos_temp[0]['ipi'], 1, ',', '.');
                    $id_classific_fiscal = $campos_temp[0]['id_classific_fiscal'];
                }else {
                    $ipi = '&nbsp;';
                    $id_classific_fiscal= '';
                }
            }
            echo $ipi;
        ?>
        </td>
        <td align='right'>
        <?
            $preco_total_lote = $campos[$i]['preco_liq_final'] * $campos[$i]['qtde'];
            $total_geral+= $preco_total_lote;
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
//Verifica o Tipo de Orçamento ...
            if($campos[$i]['nota_sgd'] == 'S') {
                echo 'SGD / '.$prazo_faturamento;
            }else {
                echo 'NF / '.$prazo_faturamento;
            }
//Aki eu limpo essa variável para não dar problema quando voltar no próximo loop
            $prazo_faturamento = '';
        ?>
        </td>
        <td>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
        <?
            $url = "javascript:nova_janela('../../vendas/pedidos/itens/detalhes_orcamento.php?id_orcamento_venda=".$campos[$i]['id_orcamento_venda']."', 'ORC', '', '', '', '', 450, 800, 'c', 'c', '', '', 's', 's', '', '', '') ";
        ?>
            <a href="<?=$url;?>" title="Visualizar Detalhes de Orçamento" class="link">
            <?
                if($id_orcamento_venda_antigo != $campos[$i]['id_orcamento_venda']) {
//Aki significa que mudou para outro N. de Orçamento e vai exibir uma nova sequência desses mesmos
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
}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
    <!--****************************Follow-UPs***************************-->
    <tr align='center'>
        <td colspan='13'>
            <iframe name='detalhes' id='detalhes' src='../follow_ups/detalhes.php?id_cliente=<?=$id_cliente;?>&origem=9' marginwidth='0' marginheight='0' frameborder='0' height='260' width='100%'></iframe>
        </td>
    </tr>
    <!--*****************************************************************-->
</table>
</body>
</html>