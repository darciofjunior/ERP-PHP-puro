<?
require('../../lib/segurancas.php');

$sql = "SELECT id_importacao, id_pedido 
        FROM `importacoes_vs_pedidos` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $sql = "UPDATE `pedidos` SET `id_importacao` = '".$campos[$i]['id_importacao']."' WHERE `id_pedido` = '".$campos[$i]['id_pedido']."' LIMIT 1 ";
    bancos::sql($sql);
}
echo 'TOTAL DE REGISTROS: '.$linhas;
?>