<?
require('../../lib/segurancas.php');

//Todos os Itens de NF ...
$sql = "SELECT COUNT(nfsi.id_pedido_venda_item) AS total_registro 
        FROM `pedidos_vendas_itens` pvi 
        INNER JOIN `nfs_itens` nfsi ON nfsi.id_pedido_venda_item = pvi.id_pedido_venda_item AND nfsi.comissao_new = '0.00' 
        WHERE pvi.`comissao_new` > '0.00' ";
$campos_total = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'];

//Busca o id_representante na Tabela de Pedidos Vendas ...
$sql = "SELECT pvi.id_representante, pvi.comissao_new, nfsi.id_nfs_item 
        FROM `pedidos_vendas_itens` pvi 
        INNER JOIN `nfs_itens` nfsi ON nfsi.id_pedido_venda_item = pvi.id_pedido_venda_item AND nfsi.comissao_new = '0.00' 
        WHERE pvi.`comissao_new` > '0.00' ORDER BY nfsi.id_nfs_item DESC LIMIT 1 ";
$campos_representante = bancos::sql($sql);

//Abastece a Tabela de Pedidos com o id_representante ...
echo '<br>'.$sql = "UPDATE `nfs_itens` SET `id_representante` = '".$campos_representante[0]['id_representante']."', `comissao_new` = '".$campos_representante[0]['comissao_new']."' WHERE `id_nfs_item` = '".$campos_representante[0]['id_nfs_item']."' LIMIT 1 ";
bancos::sql($sql);
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_atualizar_representante.php'
</Script>