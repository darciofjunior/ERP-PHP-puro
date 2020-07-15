<?
require('../../lib/segurancas.php');

$sql = "SELECT pv.id_pedido_venda, pv.status 
        FROM `pedidos_vendas` pv 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda 
        AND pvi.status = '2' 
        WHERE pv.status < '2' 
        GROUP BY pv.id_pedido_venda ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {	
    $sql = "SELECT id_pedido_venda, status 
            FROM `pedidos_vendas_itens` 
            WHERE `id_pedido_venda` = '".$campos[$i]['id_pedido_venda']."' 
            AND `status` < '2' LIMIT 1 ";
    $campos_itens = bancos::sql($sql);
    if(count($campos_itens) == 0) echo $campos[$i]['id_pedido_venda'].'<br>';
}
?>