<?
require('../../lib/segurancas.php');

$sql = "SELECT distinct(nfsi.id_nfs_item), nfsi.id_classific_fiscal, c.id_uf 
        FROM nfs 
        INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
        INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf 
        WHERE nfs.`ativo` = '1' 
        AND nfs.`id_empresa` <> '4' 
        AND nfs.`status` <= '4' 
        AND nfsi.`iva` > 0 
        AND nfsi.`icms_intraestadual` = '0' 
        AND SUBSTRING(nfs.`data_emissao`, 1, 10) >= '2009-04-01' ";
$campos_nfs = bancos::sql($sql);
$linhas = count($campos_nfs);
for($i = 0; $i < $linhas; $i++) {
    $id_uf                  = $campos_nfs[$i]['id_uf'];
    $id_classific_fiscal    = $campos_nfs[$i]['id_classific_fiscal'];
    $id_nfs_item            = $campos_nfs[$i]['id_nfs_item'];
	
    $sql = "SELECT icms_intraestadual 
            FROM `icms` 
            WHERE `id_classific_fiscal` = '$id_classific_fiscal' 
            AND `id_uf` = '$id_uf' LIMIT 1 ";
    $campos_icms = bancos::sql($sql);
//Atualizando o Item de Nota Fiscal com o ICMS a Creditar ...
    echo $sql = "UPDATE `nfs_itens` SET `icms_intraestadual` = '".$campos_icms[0]['icms_intraestadual']."' WHERE `id_nfs_item` = '$id_nfs_item' LIMIT 1 ";
    echo '<br>';
    bancos::sql($sql);
}
?>