<?
require('../../lib/segurancas.php');

$sql = "SELECT p.data_emissao, ip.id_item_pedido, ip.preco_unitario, ip.qtde 
        FROM `pedidos` p 
        INNER JOIN `itens_pedidos` ip ON ip.id_pedido = p.id_pedido AND ip.status < '2' 
        INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = ip.id_produto_insumo 
        INNER JOIN `produtos_acabados` pa ON pa.id_produto_insumo = pi.id_produto_insumo 
        AND pa.referencia LIKE 'PBS-%' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $preco_unitario = ($campos[$i]['data_emissao'] <= '2014-01-31') ? $campos[$i]['preco_unitario'] * 1.04 : $campos[$i]['preco_unitario'] * 1.08;
    $preco_unitario = round($preco_unitario, 2);

    $sql = "UPDATE `itens_pedidos` SET `preco_unitario` = '".$preco_unitario."', `valor_total` = '".($campos[$i]['qtde'] * $preco_unitario)."' WHERE `id_item_pedido` = '".$campos[$i]['id_item_pedido']."' LIMIT 1 ";
    bancos::sql($sql);
}
?>