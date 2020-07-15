<?
require('../../lib/segurancas.php');
require('../../lib/data.php');

if(empty($indice)) $indice = 0;

//Todas as NFS ...
$sql = "SELECT COUNT(distinct(nfs.id_nf)) AS total_registro 
        FROM `nfs` 
        INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf AND nfsi.`id_produto_acabado` = '0' 
        WHERE `ativo` = '1' ";
$campos_total   = bancos::sql($sql);
$total_registro = $campos_total[0]['total_registro'];

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

$sql = "SELECT DISTINCT(nfs.id_nf) 
        FROM `nfs` 
        INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf AND nfsi.id_produto_acabado = '0' 
        WHERE nfs.`ativo` = '1' ";
$campos = bancos::sql($sql, $indice, 1);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $id_nf = $campos[0]['id_nf'];
//Aqui eu busco o id_item_pedido_venda da NF de Saída ...
    $sql = "SELECT id_nfs_item, id_pedido_venda_item 
            FROM `nfs_itens` 
            WHERE `id_nf` = '$id_nf' ";
    $campos_itens = bancos::sql($sql);
    $linhas_itens = count($campos_itens);
    for($j = 0; $j < $linhas_itens; $j++) {
//Busca o id_produto_acabado do Pedido de Vendas ...
        $sql = "SELECT id_produto_acabado 
                FROM `pedidos_vendas_itens` 
                WHERE `id_pedido_venda_item` = ".$campos_itens[$j]['id_pedido_venda_item']." LIMIT 1 ";
        $campos_nf_item_principal   = bancos::sql($sql);
        $id_produto_acabado         = $campos_nf_item_principal[0]['id_produto_acabado'];
/************Atualizando com os Dados de Produto na NF Principal************/
//Atualizando a Qtde ...
        echo $sql = "UPDATE `nfs_itens` SET `id_produto_acabado` = '$id_produto_acabado' WHERE `id_nfs_item` = '".$campos_itens[$j]['id_nfs_item']."' LIMIT 1 ";
        echo '<br>';
        bancos::sql($sql);
    }
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_nfs_pas_itens.php?indice=<?=++$indice;?>'
</Script>