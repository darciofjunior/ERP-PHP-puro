<?
require('../../lib/segurancas.php');

//Somente as Notas Fiscais que são Estrangeiras ...
$sql = "SELECT id_pedido_venda_item, id_orcamento_venda_item 
        FROM pedidos_vendas_itens 
        WHERE preco_liq_final = '0.00' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    //Busca o preco_liq_final na Tabela de Orçamentos ...
    $sql = "SELECT preco_liq_final 
            FROM `orcamentos_vendas_itens` 
            WHERE `id_orcamento_venda_item` = '".$campos[$i]['id_orcamento_venda_item']."' LIMIT 1 ";
    $campos_preco_liq = bancos::sql($sql);
    //Abastece a Tabela de Pedidos com o preco_liq_final ...
    echo $sql = "UPDATE `pedidos_vendas_itens` SET `preco_liq_final` = '".$campos_preco_liq[0]['preco_liq_final']."' WHERE `id_pedido_venda_item` = '".$campos[$i]['id_pedido_venda_item']."' LIMIT 1 ";
    echo '<br>';
    bancos::sql($sql);
}
?>