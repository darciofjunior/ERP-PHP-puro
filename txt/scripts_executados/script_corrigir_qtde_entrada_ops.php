<?
require('../../lib/segurancas.php');

$sql = "SELECT bmp.`qtde`, bop.`id_baixa_op_vs_pa`, pa.`pecas_por_jogo` 
        FROM `baixas_ops_vs_pas` bop 
        INNER JOIN `baixas_manipulacoes_pas` bmp ON bmp.`id_baixa_manipulacao_pa` = bop.`id_baixa_manipulacao_pa` AND bmp.`tipo_manipulacao` IN (1, 2, 3) 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = bmp.`id_produto_acabado` 
        WHERE SUBSTRING(bmp.`data_sys`, 1, 10) >= '2016-01-01' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $sql = "UPDATE `baixas_ops_vs_pas` SET `qtde_baixa` = '".($campos[$i]['qtde'] * $campos[$i]['pecas_por_jogo'])."' WHERE `id_baixa_op_vs_pa` = '".$campos[$i]['id_baixa_op_vs_pa']."' LIMIT 1;";
    echo $sql.'<br/>';
}
?>