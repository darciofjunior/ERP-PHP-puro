<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/pedidos_pendentes/pedidos_pendentes.php', '../../../../');

$valor_dolar_dia = genericas::moeda_dia('dolar');

/*Aqui eu busco tudo o que foi faturado da Empresa passada por parâmetro, nas respectivas Datas 

Nesse bolo de Notas, estão inclusas as Devoluções também ...*/
$sql = "SELECT nfs.id_nf, SUM(nfsi.qtde * nfsi.valor_unitario) AS total_nf, DATE_FORMAT(nfs.`data_emissao`, '%d/%m/%Y') AS date_emissao_nf, nnn.numero_nf, IF(c.`nomefantasia` = '', c.`razaosocial`, c.`nomefantasia`) AS cliente 
        FROM `nfs_itens` nfsi 
        INNER JOIN `nfs` ON nfs.`id_nf` = nfsi.`id_nf` 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
        INNER JOIN `nfs_num_notas` nnn ON nnn.`id_nf_num_nota` = nfs.`id_nf_num_nota` 
        WHERE nfs.`data_emissao` BETWEEN '$_GET[data_inicial]' AND '$_GET[data_final]' 
        AND nfs.`id_empresa` = '$_GET[id_empresa_parametro]' 
        GROUP BY nfs.`id_nf` ORDER BY nnn.numero_nf ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Consultar ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Relat&oacute;rio de Faturamento por Empresa
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='4'>
            <font color='yellow'>
                Empresa: 
            </font>
            <?=genericas::nome_empresa($_GET['id_empresa_parametro']);?>
            -
            <font color='yellow'>
                Período de 
                <?=data::datetodata($_GET['data_inicial'], '/');?>
                à
                <?=data::datetodata($_GET['data_final'], '/');?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Número NF
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            Cliente
        </td>
        <td>
            Valor NF em R$
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <a href="javascript:nova_janela('../../../faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=<?=$campos[$i]['id_nf'];?>&pop_up=1', 'DETALHES', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Detalhes' class='link'>
                <?=$campos[$i]['numero_nf'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['date_emissao_nf'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['cliente'];?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($campos[$i]['total_nf'], 2, '.');?>
        </td>
    </tr>
<?
        $total_geral+= $campos[$i]['total_nf'];
    }
?>
    <tr class='linhanormal'>
        <td colspan='3'>
            <font color='darkblue' size='2'>
                <b>Valor Dólar do dia => R$ <?=number_format($valor_dolar_dia, 4, ',', '.');?></b>
            </font>
        </td>
        <td align='right'>
            <font color='red' size='2'>
                <b>Total Geral => </b>R$ <?=number_format($total_geral, 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='print()' class='botao'>
        </td>
    </tr>
</table>
</body>
</html>