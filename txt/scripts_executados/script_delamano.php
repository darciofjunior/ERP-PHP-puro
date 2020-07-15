<?
require('../../lib/segurancas.php');

$sql = "SELECT DISTINCT(id_produto_acabado) 
        FROM `nfs_itens` ni
        INNER JOIN `nfs` n ON n.id_nf = ni.id_nf
        AND id_cliente IN (37550, 37869, 38018)";
$campos = bancos::sql($sql);
$linhas = count($campos);

for($i = 0; $i < $linhas; $i++) {
    $sql = "INSERT INTO `pas_cod_clientes` (`id_produto_acabado`, `id_cliente`) VALUES ('".$campos[$i]['id_produto_acabado']."', '37550')";
    bancos::sql($sql);
}
?>