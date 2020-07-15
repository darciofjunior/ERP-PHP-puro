<?
require('../../lib/segurancas.php');

//Trago todos os Itens dessas NF's em Específico porque estão com Preços errados no Fornecedor Intertaps ...
/*$sql = "SELECT id_nfe_historico, id_produto_insumo 
        FROM `nfe_historicos` 
        WHERE `id_nfe` IN (27959, 28019, 28075) ORDER BY id_nfe ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    //Trago o Preço da Lista p/ atualizar na NF ...
    $sql = "SELECT preco 
            FROM `fornecedores_x_prod_insumos` 
            WHERE `id_fornecedor` = '657' 
            AND `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
    $campos_lista = bancos::sql($sql);
    //Atualizo a NF com o Preço encontrado na Lista de Preços ...
    echo $sql = "UPDATE `nfe_historicos` SET `valor_entregue` = '".$campos_lista[0]['preco']."' WHERE `id_nfe_historico` = '".$campos[$i]['id_nfe_historico']."' LIMIT 1 ";
    bancos::sql($sql);
    
    echo '<br/>';
}*/

//Trago todos os Itens desses NF's em Específico porque estão com Preços errados no Fornecedor Intertaps ...
$sql = "SELECT ip.id_item_pedido, ip.id_pedido, ip.id_produto_insumo, ip.preco_unitario 
        FROM `itens_pedidos` ip 
        INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` AND p.`id_fornecedor` = '657' 
        WHERE ip.`status` < '2' 
        AND `preco_unitario` <> '0' ORDER BY ip.id_pedido ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    echo $campos[$i]['id_pedido'].'|'.$campos[$i]['id_produto_insumo'].'|'.$campos[$i]['preco_unitario'].'<br/>';

    //Trago o Preço da Lista p/ atualizar na NF ...
    /*$sql = "SELECT preco 
            FROM `fornecedores_x_prod_insumos` 
            WHERE `id_fornecedor` = '657' 
            AND `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
    $campos_lista = bancos::sql($sql);
    //Atualizo a NF com o Preço encontrado na Lista de Preços ...
    echo $sql = "UPDATE `itens_pedidos` SET `preco_unitario` = '".$campos_lista[0]['preco']."' WHERE `id_item_pedido` = '".$campos[$i]['id_item_pedido']."' LIMIT 1 ";
    //bancos::sql($sql);*/
}
?>