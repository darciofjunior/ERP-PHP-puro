<?
require('../../lib/segurancas.php');

$sql = "SELECT id_produto_insumo, discriminacao 
        FROM `produtos_insumos` 
        WHERE `discriminacao` LIKE '%lima%nvo%' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $sql = "UPDATE `produtos_insumos` SET `discriminacao` = '".str_replace('nvo', 'inferpa', strtolower($campos[$i]['discriminacao']))."' WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
    bancos::sql($sql);
}

$sql = "SELECT id_produto_acabado, discriminacao 
        FROM `produtos_acabados` 
        WHERE `discriminacao` LIKE '%lima%nvo%' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $sql = "UPDATE `produtos_acabados` SET `discriminacao` = '".str_replace('NVO', 'INFERPA', $campos[$i]['discriminacao'])."' WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
    bancos::sql($sql);
}
?>