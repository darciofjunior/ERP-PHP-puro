<?
require('../../lib/segurancas.php');
require('../../lib/data.php');
session_start('funcionarios');

if(empty($indice)) $indice = 0;

//Todas as NFS ...
$sql = "SELECT COUNT(nfs.id_nf) AS total_registro 
        FROM `nfs_vs_bancos` nb 
        INNER JOIN `nfs` ON nfs.id_nf = nb.id_nf AND nfs.id_banco = '0' ";
$campos_total = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'];
echo '<br>';

if($total_registro == $indice) {//P/ não ficar em loop infinito ...
    exit;
}

$sql = "SELECT nfs.id_nf, nb.id_banco 
        FROM `nfs_vs_bancos` nb 
        INNER JOIN `nfs` ON nfs.id_nf = nb.id_nf AND nfs.id_banco = '0' ";
$campos = bancos::sql($sql, $indice, 1);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    echo $sql = "UPDATE `nfs` SET `id_banco` = '".$campos[0]['id_banco']."' WHERE `id_nf` = '".$campos[0]['id_nf']."' LIMIT 1 ";
    echo '<br>';
    bancos::sql($sql);
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_nfs_bancos.php?indice=<?=++$indice;?>'
</Script>