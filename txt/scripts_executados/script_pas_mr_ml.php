<?
require('../../lib/segurancas.php');

//Somente as Notas Fiscais que são Estrangeiras ...
$sql = "SELECT id_produto_acabado, CONCAT(SUBSTRING(referencia, 1, 2), '-', SUBSTRING(referencia, 5, 3)) AS referencia 
        FROM `produtos_acabados` 
        WHERE `referencia` LIKE 'MLH-%' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $sql = "SELECT mmv 
            FROM `produtos_acabados` 
            WHERE `referencia` = '".$campos[$i]['referencia']."' LIMIT 1 ";
    $campos_mmv = bancos::sql($sql);
    echo $sql = "UPDATE `produtos_acabados` SET `mmv` = '".$campos_mmv[0]['mmv']."' WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
    echo ';<br>';
    bancos::sql($sql);
}
?>