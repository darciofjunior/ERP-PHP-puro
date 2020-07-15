<?
require('../../lib/segurancas.php');
require('../../lib/data.php');

$sql = "SELECT SUBSTRING_INDEX(cfops.`situacao_tributaria`, ' ', 1) AS situacao_tributaria, nfso.id_nf_outra 
        FROM `nfs_outras` nfso 
        INNER JOIN `nfs_outras_itens` nfsoi ON nfsoi.`id_nf_outra` = nfso.`id_nf_outra` 
        INNER JOIN `cfops` ON cfops.id_cfop = nfso.id_cfop AND cfops.`situacao_tributaria` <> '' 
        ORDER BY nfso.id_nf_outra ";
$campos = bancos::sql($sql, $indice, 1);
//Aqui eu busco o id_item_pedido_venda da NF de Saída ...
$sql = "SELECT id_nf_outra_item 
        FROM `nfs_outras_itens` 
        WHERE `id_nf_outra` = '".$campos[0]['id_nf_outra']."' ";
$campos_itens = bancos::sql($sql);
$linhas_itens = count($campos_itens);
for($i = 0; $i < $linhas_itens; $i++) {
    $sql = "UPDATE `nfs_outras_itens` SET `situacao_tributaria` = '".$campos[0]['situacao_tributaria']."' WHERE `id_nf_outra_item` = '".$campos_itens[$i]['id_nf_outra_item']."' LIMIT 1 ";
    bancos::sql($sql);
    echo $sql.'<br/>';
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_nfso_situacao_tributaria.php?indice=<?=++$indice;?>'
</Script>