<?
require('../../lib/segurancas.php');

$sql = "SELECT DATE_FORMAT(pv.data_emissao, '%d/%m/%Y') AS data_emissao, pvi.id_pedido_venda, pvi.margem_lucro, pa.referencia, pa.discriminacao 
        FROM `pedidos_vendas_itens` pvi 
        INNER JOIN pedidos_vendas pv ON pv.id_pedido_venda = pvi.id_pedido_venda 
        AND pv.data_emissao >= '2008-01-01' 
        INNER JOIN produtos_acabados pa ON pa.id_produto_acabado = pvi.id_produto_acabado 
        WHERE pvi.margem_lucro > '300' order by pv.data_emissao desc ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Pedidos com Margem de Lucro > 300 % ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../js/sessao.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class="linhacabecalho" align="center">
        <td colspan='5'>
            Pedidos com Margem de Lucro > 300 %
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            N.º Ped
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Margem de Lucro
        </td>
    </tr>
<?
    for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal" align='center'>
        <td>
            <?=$campos[$i]['id_pedido_venda'];?>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>
        <td align="left">
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align="left">
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td>
            <?=number_format($campos[$i]['margem_lucro'], 1, ',', '.');?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>