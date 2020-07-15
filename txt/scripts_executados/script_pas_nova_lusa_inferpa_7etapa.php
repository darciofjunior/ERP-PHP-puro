<?
require('../../lib/segurancas.php');
session_start('funcionarios');

//Busca todos os PAs dos Grupos Limas Agrícola Inferpa e Limas Mecânica Inferpa ...
$sql = "SELECT COUNT(pa.id_produto_acabado) AS total_registro 
		FROM `produtos_acabados` pa 
		INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
		INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.id_grupo_pa IN (12, 59) 
		WHERE pa.`referencia` <> 'ESP' 
		AND pa.`operacao_custo` = '1' ";
$campos_total 	= bancos::sql($sql);
$total_registro = $campos_total[0]['total_registro'];

$sql = "SELECT pa.id_produto_acabado, pa.referencia 
		FROM `produtos_acabados` pa 
		INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
		INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.id_grupo_pa IN (12, 59) 
		WHERE pa.`referencia` <> 'ESP' 
		AND pa.`operacao_custo` = '1' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
	echo $campos[$i]['referencia'].'<br>';
	
	
	//Aqui eu busco o mesmo PA como sendo NL ...
	$sql = "SELECT id_produto_acabado 
			FROM `produtos_acabados` 
			WHERE `referencia` = '".$campos[$i]['referencia']."NL' LIMIT 1 ";
	$campos_pa_nl = bancos::sql($sql);
	//Aqui eu busco o Custo do PA NL ...
	$sql = "SELECT id_produto_acabado_custo 
			FROM `produtos_acabados_custos` 
			WHERE `id_produto_acabado` = '".$campos_pa_nl[0]['id_produto_acabado']."' 
			AND `operacao_custo` = '1' LIMIT 1 ";
	$campos_custo = bancos::sql($sql);
	//Na 7ª Etapa do PA NL eu atrelo o PA Inferpa ...
	//echo $sql = "INSERT INTO `pacs_vs_pas` (`id_pac_pa`, `id_produto_acabado_custo`, `id_produto_acabado`, `qtde`) VALUES (NULL, '".$campos_custo[0]['id_produto_acabado_custo']."', '".$campos[$i]['id_produto_acabado']."', '0') ";
	//bancos::sql($sql);
	echo '<br>';
}
?>