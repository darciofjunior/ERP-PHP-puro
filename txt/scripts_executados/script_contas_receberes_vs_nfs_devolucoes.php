<?
require('../../lib/segurancas.php');

$sql = "SELECT id_conta_receber, id_nf_devolucao, valor_abat_devolucao 
        FROM `contas_receberes` 
        WHERE `id_nf_devolucao` > '0' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $sql = "INSERT INTO `contas_receberes_vs_nfs_devolucoes` (`id_conta_receber_nf_devolucao`, `id_conta_receber`, `id_nf_devolucao`, `valor_devolucao`) VALUES (NULL, '".$campos[$i]['id_conta_receber']."', '".$campos[$i]['id_nf_devolucao']."', '".$campos[$i]['valor_abat_devolucao']."') ";
    bancos::sql($sql);
    echo $sql.'<br>';
}

echo 'Total de Registro(s) => '.$linhas;
?>