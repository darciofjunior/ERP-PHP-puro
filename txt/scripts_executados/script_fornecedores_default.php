<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

//Todas as NFS ...
$sql = "SELECT COUNT(fppf.id_fornec_pis_func) AS total_registro 
        FROM `fornec_pis_vs_pis_vs_func` fppf 
        INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_fornecedor_prod_insumo` = fppf.`id_fornecedor_prod_insumo` ";
$campos_total = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'];
echo '<br>';

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

$sql = "SELECT fpi.`id_produto_insumo`, fppf.`id_funcionario` 
        FROM `fornec_pis_vs_pis_vs_func` fppf 
        INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_fornecedor_prod_insumo` = fppf.`id_fornecedor_prod_insumo` ";
$campos = bancos::sql($sql, $indice, 1);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    echo $sql = "UPDATE `produtos_insumos` SET `id_funcionario_fornecedor_default` = '".$campos[$i]['id_funcionario']."' WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
    bancos::sql($sql);
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_fornecedores_default.php?indice=<?=++$indice;?>'
</Script>