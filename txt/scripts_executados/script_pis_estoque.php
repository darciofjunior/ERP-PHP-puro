<?
require('../../lib/segurancas.php');

$sql = "SELECT id_produto_insumo 
        FROM `produtos_insumos` 
        WHERE `ativo` = '1' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
//Busca o id_estoque_insumo através do id_produto_insumo ...
    $sql = "SELECT id_estoque_insumo 
            FROM `estoques_insumos` 
            WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
    $campos_estoque = bancos::sql($sql);
    if(count($campos_estoque) == 0) {//Se não existir Estoque então ...
        $sql = "INSERT INTO `estoques_insumos` (`id_estoque_insumo`, `id_produto_insumo`, `qtde`, `data_atualizacao`) VALUES (null, '".$campos[$i]['id_produto_insumo']."', '0', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
        $contador++;
    }
}
echo $contador;
?>