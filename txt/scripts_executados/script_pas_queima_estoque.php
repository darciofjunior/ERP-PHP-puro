<?
require('../../lib/segurancas.php');
require('../../lib/intermodular.php');

if(empty($indice)) $indice = 0;

//Todos os PA´s com Queima de Estoque ...
$sql = "SELECT COUNT(pa.`id_produto_acabado`) AS total_registro 
        FROM `produtos_acabados` pa 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` = '2' 
        WHERE pa.`referencia` <> 'ESP' 
        AND pa.`ativo` = '1' ";
$campos_total = bancos::sql($sql);
echo $indice.'/'.$total_registro = $campos_total[0]['total_registro'];
echo '<br>';

if($total_registro == $indice) {exit;}

//Todos os PA´s com Queima de Estoque ...
$sql = "SELECT gpa.`id_familia`, pa.`id_produto_acabado` 
        FROM `produtos_acabados` pa 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` = '2' 
        WHERE pa.`referencia` <> 'ESP' 
        AND pa.`ativo` = '1' ";
$campos = bancos::sql($sql, $indice, 1);
$linhas = count($campos);

$valores            = intermodular::calculo_estoque_queima_pas_atrelados($campos[0]['id_produto_acabado']);
$estoque_queima     = $valores['total_eq_pas_atrelados'];

//Se for componente, não existe queima ...
/*$sql = "SELECT gpa.id_familia 
        FROM `produtos_acabados` pa 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
        INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
        WHERE pa.`id_produto_acabado` = '".$campos[0]['id_produto_acabado']."' LIMIT 1 ";
$campos_componente = bancos::sql($sql);*/
if($campos[0]['id_familia'] == 23 || $campos[0]['id_familia'] == 24) $estoque_queima = 0;
echo $sql = "UPDATE `produtos_acabados` SET `qtde_queima_estoque` = '$estoque_queima' WHERE `id_produto_acabado` = '".$campos[0]['id_produto_acabado']."' LIMIT 1 ";
bancos::sql($sql);
echo '<br>';
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_pas_queima_estoque.php?indice=<?=++$indice;?>'
</Script>