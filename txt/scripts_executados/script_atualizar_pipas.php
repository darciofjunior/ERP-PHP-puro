<?
require('../../lib/segurancas.php');
session_start('funcionarios');

//Nesse Script eu atualizo todos os PI´s do Tipo PRAC, de forma que fiquem com a mesma discriminação do PA ...
$sql = "SELECT id_produto_acabado, id_produto_insumo, discriminacao 
        FROM `produtos_acabados` 
        WHERE `id_produto_insumo` > '0' 
        AND `ativo` = '1' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $sql = "UPDATE `produtos_insumos` SET `discriminacao` = '".$campos[$i]['discriminacao']."' WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
    echo $sql.'<br>';
    bancos::sql($sql);
}
?>