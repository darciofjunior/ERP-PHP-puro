<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

//Somente as Notas Fiscais que são de Devolução ...
$sql = "SELECT COUNT(nfs.id_nf) AS total_registro 
        FROM `nfs` 
        INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = nfsi.`id_pedido_venda_item` AND pvi.`qtde_faturada` = '0' AND pvi.`status` > '0' 
        WHERE nfs.`ativo` = '1' 
        AND nfs.`status` >= '0' ";
$campos_total = bancos::sql($sql);
$total_registro = $campos_total[0]['total_registro'];

if($total_registro == $indice) {//P/ não ficar em loop infinito ...
    exit;
}

$sql = "SELECT nfsi.qtde, pvi.id_pedido_venda_item 
        FROM `nfs` 
        INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = nfsi.`id_pedido_venda_item` AND pvi.`qtde_faturada` = '0' AND pvi.`status` > '0' 
        WHERE nfs.`ativo` = '1' 
        AND nfs.`status` >= '0' ";
$campos = bancos::sql($sql, $indice, 1);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
//Atualizando a Qtde ...
    $sql = "UPDATE `pedidos_vendas_itens` SET `qtde_faturada` = `qtde_faturada` + '".$campos[$i]['qtde']."' WHERE `id_pedido_venda_item` = '".$campos[$i]['id_pedido_venda_item']."' LIMIT 1 ";
    bancos::sql($sql);
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_nfs_faturadas.php?indice=<?=++$indice;?>'
</Script>