<?
require('../../lib/segurancas.php');
?>
<html>
<body>
<table width='70%' border='1'>
<?
$sql = "SELECT c.nomefantasia, pv.id_pedido_venda, date_format( pv.data_emissao, '%d/%m/%Y' ) AS data_emissao_show, pa.referencia, pa.discriminacao, ovi.preco_liq_final 
        FROM clientes c
        INNER JOIN orcamentos_vendas ov ON ov.id_cliente = c.id_cliente
        INNER JOIN orcamentos_vendas_itens ovi ON ovi.id_orcamento_venda = ov.id_orcamento_venda
        INNER JOIN produtos_acabados pa ON pa.id_produto_acabado = ovi.id_produto_acabado
        INNER JOIN pedidos_vendas_itens pvi ON pvi.id_orcamento_venda_item = ovi.id_orcamento_venda_item
        INNER JOIN pedidos_vendas pv ON pv.id_pedido_venda = pvi.id_pedido_venda
        WHERE c.id_cliente
        IN ( 647, 649, 687, 801, 802, 854, 3573, 3820, 3971 )
        ORDER BY pa.referencia, pv.data_emissao DESC ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    if($referencia_atual != $campos[$i]['referencia']) {
?>
    <tr>
        <td><?=$campos[$i]['nomefantasia'];?></td>	
        <td><?=$campos[$i]['id_pedido_venda'];?></td>	
        <td><?=$campos[$i]['data_emissao_show'];?></td>	
        <td><?=$campos[$i]['referencia'];?></td>	
        <td><?=$campos[$i]['discriminacao'];?></td>	
        <td><?=number_format($campos[$i]['preco_liq_final'], 2, ',', '.');?></td>	
    </tr>
<?
        $referencia_atual = $campos[$i]['referencia'];
    }
}
?>
</table>
</body>
</html>