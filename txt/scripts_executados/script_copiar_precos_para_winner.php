<?
require('../../lib/segurancas.php');

$sql = "SELECT fpi.id_produto_insumo, fpi.preco_faturado 
        FROM `fornecedores_x_prod_insumos` fpi 
        INNER JOIN `produtos_acabados` pa ON pa.id_produto_insumo = fpi.id_produto_insumo 
        INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = fpi.id_produto_insumo AND pi.id_grupo = '9' 
        AND pa.referencia <> 'ESP'
        AND pa.id_produto_insumo > '0' 
        WHERE fpi.id_fornecedor = '657' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $sql = "UPDATE `fornecedores_x_prod_insumos` SET `preco_faturado` = '".$campos[$i]['preco_faturado']."', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_fornecedor` = '2162' AND `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
    bancos::sql($sql);
}
echo 'TOTAL DE REGISTROS: '.$linhas;
?>