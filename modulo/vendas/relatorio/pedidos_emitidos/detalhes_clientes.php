<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/pedidos_emitidos/pedidos_emitidos.php', '../../../../');

$sql = "SELECT IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, SUM(pvi.`qtde` * pvi.`preco_liq_final`) AS total_pedidos 
        FROM `pedidos_vendas` pv 
        INNER JOIN `clientes` c on c.id_cliente = pv.id_cliente AND c.id_uf = '$_GET[id_uf]' 
        INNER JOIN `pedidos_vendas_itens` pvi on pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`id_representante` = '$_GET[id_representante]' 
        WHERE pv.`data_emissao` BETWEEN '$_GET[data_inicial]' and '$_GET[data_final]' 
        AND pv.`liberado` = '1' 
        GROUP BY cliente ORDER BY cliente ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Detalhes Clientes ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Detalhe(s) Cliente(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Cliente(s)
        </td>
        <td>
            Total em R$
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal'>
        <td>
            <?=$campos[$i]['cliente'];?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['total_pedidos'], 2, ',', '.');?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="window.close()" style="color:red" class="botao">
        </td>
    </tr>
</table>
</body>
</html>