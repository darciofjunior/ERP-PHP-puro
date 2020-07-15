<?
require('../../lib/segurancas.php');

//Trago todos os PI's da Lista de Preço da Intertaps que não são normais de Linha ...
$sql = "SELECT id_fornecedor_prod_insumo, preco_faturado 
        FROM `fornecedores_x_prod_insumos` fpi 
        INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = fpi.id_produto_insumo 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = pi.`id_produto_insumo` AND pa.`ativo` = '1' 
        AND pa.referencia <> 'ESP' 
        WHERE `id_fornecedor` = '657' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $sql = "UPDATE `fornecedores_x_prod_insumos` SET `preco_faturado` = '".round($campos[$i]['preco_faturado'] * 1.1, 2)."' WHERE `id_fornecedor_prod_insumo` = '".$campos[$i]['id_fornecedor_prod_insumo']."' LIMIT 1";
    echo $sql.';<br/>';
}
echo 'TOTAL DE REGISTROS: '.$linhas;
?>