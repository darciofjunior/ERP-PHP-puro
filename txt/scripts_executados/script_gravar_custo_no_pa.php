<?
require('../../lib/segurancas.php');
require('../../lib/custos.php');

if(empty($indice)) $indice = 0;

//Todos os Produtos ...
$sql = "SELECT COUNT(id_produto_acabado) AS total_registro 
        FROM `produtos_acabados` 
        WHERE `custo_ml60` = '0' ";
$campos_total   = bancos::sql($sql);
$total_registro = $campos_total[0]['total_registro'];

echo $indice.' / '.$total_registro;

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

//Somente as Notas Fiscais que são Estrangeiras ...
$sql = "SELECT id_produto_acabado 
        FROM `produtos_acabados` 
        WHERE `custo_ml60` = '0' ";
$campos = bancos::sql($sql, $indice, 1);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $custo_ml60 = custos::preco_custo_pa($campos[$i]['id_produto_acabado']);
    
    $sql = "UPDATE `produtos_acabados` SET `custo_ml60` = '$custo_ml60' WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
    bancos::sql($sql);
    
    echo '<br/><br/>'.$sql;
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_gravar_custo_no_pa.php?indice=<?=++$indice;?>'
</Script>