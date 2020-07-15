<?
require('../../lib/segurancas.php');
session_start('funcionarios');

/********************************AMATOOLS********************************/
//1) Macho HSS ...

$sql = "SELECT pa.referencia, cpa.preco_bruto,	cpa.desc_a,	cpa.desc_b,	cpa.desc_c,	cpa.desc_d,	cpa.desc_e,	cpa.ipi, cpa.icms, cpa.preco_liquido, cpa.data_sys_ult_alt 
		FROM `concorrentes_vs_prod_acabados` cpa 
		INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = cpa.id_produto_acabado AND pa.id_gpa_vs_emp_div = '83' AND pa.referencia <> 'ESP' 
		WHERE cpa.`id_concorrente` = '6' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
	$referencia = substr($campos[$i]['referencia'], 0, 2).'H'.substr($campos[$i]['referencia'], 2, 6);

	$sql = "SELECT id_produto_acabado 
			FROM `produtos_acabados` 
			WHERE `referencia` = '$referencia' 
			AND `id_gpa_vs_emp_div` = '122' LIMIT 1 ";
	$campos_pa_heinz = bancos::sql($sql);
	if(count($campos_pa_heinz) == 1) {
		$sql = "INSERT INTO `concorrentes_vs_prod_acabados` (`id_concorrente_prod_acabado`, `id_concorrente`, `id_produto_acabado`, `preco_bruto`, `desc_a`, `desc_b`, `desc_c`, `desc_d`, `desc_e`, `ipi`, `icms`, `preco_liquido`, `data_sys_ult_alt`) VALUES (NULL, '6', '".$campos_pa_heinz[0]['id_produto_acabado']."', '".$campos[$i]['preco_bruto']."', '".$campos[$i]['desc_a']."', '".$campos[$i]['desc_b']."', '".$campos[$i]['desc_c']."', '".$campos[$i]['desc_d']."', '".$campos[$i]['desc_e']."', '".$campos[$i]['ipi']."', '".$campos[$i]['icms']."', '".$campos[$i]['preco_liquido']."', '".date('Y-m-d H:i:s')."') ";
		bancos::sql($sql);
	}
}
echo 'TOTAL AMATOOLS HSS -> '.$linhas.'<br>';

//2) Macho WS ...

$sql = "SELECT pa.referencia, cpa.preco_bruto,	cpa.desc_a,	cpa.desc_b,	cpa.desc_c,	cpa.desc_d,	cpa.desc_e,	cpa.ipi, cpa.icms, cpa.preco_liquido, cpa.data_sys_ult_alt 
		FROM `concorrentes_vs_prod_acabados` cpa 
		INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = cpa.id_produto_acabado AND pa.id_gpa_vs_emp_div = '22' AND pa.referencia <> 'ESP' 
		WHERE cpa.`id_concorrente` = '6' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
	$referencia = substr($campos[$i]['referencia'], 0, 2).'H'.substr($campos[$i]['referencia'], 2, 6);

	$sql = "SELECT id_produto_acabado 
			FROM `produtos_acabados` 
			WHERE `referencia` = '$referencia' 
			AND `id_gpa_vs_emp_div` = '123' LIMIT 1 ";
	$campos_pa_heinz = bancos::sql($sql);
	if(count($campos_pa_heinz) == 1) {
		$sql = "INSERT INTO `concorrentes_vs_prod_acabados` (`id_concorrente_prod_acabado`, `id_concorrente`, `id_produto_acabado`, `preco_bruto`, `desc_a`, `desc_b`, `desc_c`, `desc_d`, `desc_e`, `ipi`, `icms`, `preco_liquido`, `data_sys_ult_alt`) VALUES (NULL, '6', '".$campos_pa_heinz[0]['id_produto_acabado']."', '".$campos[$i]['preco_bruto']."', '".$campos[$i]['desc_a']."', '".$campos[$i]['desc_b']."', '".$campos[$i]['desc_c']."', '".$campos[$i]['desc_d']."', '".$campos[$i]['desc_e']."', '".$campos[$i]['ipi']."', '".$campos[$i]['icms']."', '".$campos[$i]['preco_liquido']."', '".date('Y-m-d H:i:s')."') ";
		bancos::sql($sql);
	}
}
echo 'TOTAL AMATOOLS WS -> '.$linhas.'<br>';

/********************************KING TOOLS********************************/
//1) Macho HSS ...
$sql = "SELECT pa.referencia, cpa.preco_bruto,	cpa.desc_a,	cpa.desc_b,	cpa.desc_c,	cpa.desc_d,	cpa.desc_e,	cpa.ipi, cpa.icms, cpa.preco_liquido, cpa.data_sys_ult_alt 
		FROM `concorrentes_vs_prod_acabados` cpa 
		INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = cpa.id_produto_acabado AND pa.id_gpa_vs_emp_div = '83' AND pa.referencia <> 'ESP' 
		WHERE cpa.`id_concorrente` = '2' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
	$referencia = substr($campos[$i]['referencia'], 0, 2).'H'.substr($campos[$i]['referencia'], 2, 6);

	$sql = "SELECT id_produto_acabado 
			FROM `produtos_acabados` 
			WHERE `referencia` = '$referencia' 
			AND `id_gpa_vs_emp_div` = '122' LIMIT 1 ";
	$campos_pa_heinz = bancos::sql($sql);
	if(count($campos_pa_heinz) == 1) {
		$sql = "INSERT INTO `concorrentes_vs_prod_acabados` (`id_concorrente_prod_acabado`, `id_concorrente`, `id_produto_acabado`, `preco_bruto`, `desc_a`, `desc_b`, `desc_c`, `desc_d`, `desc_e`, `ipi`, `icms`, `preco_liquido`, `data_sys_ult_alt`) VALUES (NULL, '2', '".$campos_pa_heinz[0]['id_produto_acabado']."', '".$campos[$i]['preco_bruto']."', '".$campos[$i]['desc_a']."', '".$campos[$i]['desc_b']."', '".$campos[$i]['desc_c']."', '".$campos[$i]['desc_d']."', '".$campos[$i]['desc_e']."', '".$campos[$i]['ipi']."', '".$campos[$i]['icms']."', '".$campos[$i]['preco_liquido']."', '".date('Y-m-d H:i:s')."') ";
		bancos::sql($sql);
	}
}
echo 'TOTAL KING TOOLS HSS -> '.$linhas.'<br>';

//2) Macho WS ...
$sql = "SELECT pa.referencia, cpa.preco_bruto,	cpa.desc_a,	cpa.desc_b,	cpa.desc_c,	cpa.desc_d,	cpa.desc_e,	cpa.ipi, cpa.icms, cpa.preco_liquido, cpa.data_sys_ult_alt 
		FROM `concorrentes_vs_prod_acabados` cpa 
		INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = cpa.id_produto_acabado AND pa.id_gpa_vs_emp_div = '22' AND pa.referencia <> 'ESP' 
		WHERE cpa.`id_concorrente` = '2' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
	$referencia = substr($campos[$i]['referencia'], 0, 2).'H'.substr($campos[$i]['referencia'], 2, 6);

	$sql = "SELECT id_produto_acabado 
			FROM `produtos_acabados` 
			WHERE `referencia` = '$referencia' 
			AND `id_gpa_vs_emp_div` = '123' LIMIT 1 ";
	$campos_pa_heinz = bancos::sql($sql);
	if(count($campos_pa_heinz) == 1) {
		$sql = "INSERT INTO `concorrentes_vs_prod_acabados` (`id_concorrente_prod_acabado`, `id_concorrente`, `id_produto_acabado`, `preco_bruto`, `desc_a`, `desc_b`, `desc_c`, `desc_d`, `desc_e`, `ipi`, `icms`, `preco_liquido`, `data_sys_ult_alt`) VALUES (NULL, '2', '".$campos_pa_heinz[0]['id_produto_acabado']."', '".$campos[$i]['preco_bruto']."', '".$campos[$i]['desc_a']."', '".$campos[$i]['desc_b']."', '".$campos[$i]['desc_c']."', '".$campos[$i]['desc_d']."', '".$campos[$i]['desc_e']."', '".$campos[$i]['ipi']."', '".$campos[$i]['icms']."', '".$campos[$i]['preco_liquido']."', '".date('Y-m-d H:i:s')."') ";
		bancos::sql($sql);
	}
}
echo 'TOTAL KING TOOLS WS -> '.$linhas.'<br>';

/********************************HANSATÉCNICA********************************/
//1) Macho HSS ...
$sql = "SELECT pa.referencia, cpa.preco_bruto,	cpa.desc_a,	cpa.desc_b,	cpa.desc_c,	cpa.desc_d,	cpa.desc_e,	cpa.ipi, cpa.icms, cpa.preco_liquido, cpa.data_sys_ult_alt 
		FROM `concorrentes_vs_prod_acabados` cpa 
		INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = cpa.id_produto_acabado AND pa.id_gpa_vs_emp_div = '83' AND pa.referencia <> 'ESP' 
		WHERE cpa.`id_concorrente` = '3' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
	$referencia = substr($campos[$i]['referencia'], 0, 2).'H'.substr($campos[$i]['referencia'], 2, 6);

	$sql = "SELECT id_produto_acabado 
			FROM `produtos_acabados` 
			WHERE `referencia` = '$referencia' 
			AND `id_gpa_vs_emp_div` = '122' LIMIT 1 ";
	$campos_pa_heinz = bancos::sql($sql);
	if(count($campos_pa_heinz) == 1) {
		$sql = "INSERT INTO `concorrentes_vs_prod_acabados` (`id_concorrente_prod_acabado`, `id_concorrente`, `id_produto_acabado`, `preco_bruto`, `desc_a`, `desc_b`, `desc_c`, `desc_d`, `desc_e`, `ipi`, `icms`, `preco_liquido`, `data_sys_ult_alt`) VALUES (NULL, '3', '".$campos_pa_heinz[0]['id_produto_acabado']."', '".$campos[$i]['preco_bruto']."', '".$campos[$i]['desc_a']."', '".$campos[$i]['desc_b']."', '".$campos[$i]['desc_c']."', '".$campos[$i]['desc_d']."', '".$campos[$i]['desc_e']."', '".$campos[$i]['ipi']."', '".$campos[$i]['icms']."', '".$campos[$i]['preco_liquido']."', '".date('Y-m-d H:i:s')."') ";
		bancos::sql($sql);
	}
}
echo 'TOTAL HANSATÉCNICA HSS -> '.$linhas.'<br>';

//2) Macho WS ...
$sql = "SELECT pa.referencia, cpa.preco_bruto,	cpa.desc_a,	cpa.desc_b,	cpa.desc_c,	cpa.desc_d,	cpa.desc_e,	cpa.ipi, cpa.icms, cpa.preco_liquido, cpa.data_sys_ult_alt 
		FROM `concorrentes_vs_prod_acabados` cpa 
		INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = cpa.id_produto_acabado AND pa.id_gpa_vs_emp_div = '22' AND pa.referencia <> 'ESP' 
		WHERE cpa.`id_concorrente` = '3' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
	$referencia = substr($campos[$i]['referencia'], 0, 2).'H'.substr($campos[$i]['referencia'], 2, 6);

	$sql = "SELECT id_produto_acabado 
			FROM `produtos_acabados` 
			WHERE `referencia` = '$referencia' 
			AND `id_gpa_vs_emp_div` = '123' LIMIT 1 ";
	$campos_pa_heinz = bancos::sql($sql);
	if(count($campos_pa_heinz) == 1) {
		$sql = "INSERT INTO `concorrentes_vs_prod_acabados` (`id_concorrente_prod_acabado`, `id_concorrente`, `id_produto_acabado`, `preco_bruto`, `desc_a`, `desc_b`, `desc_c`, `desc_d`, `desc_e`, `ipi`, `icms`, `preco_liquido`, `data_sys_ult_alt`) VALUES (NULL, '3', '".$campos_pa_heinz[0]['id_produto_acabado']."', '".$campos[$i]['preco_bruto']."', '".$campos[$i]['desc_a']."', '".$campos[$i]['desc_b']."', '".$campos[$i]['desc_c']."', '".$campos[$i]['desc_d']."', '".$campos[$i]['desc_e']."', '".$campos[$i]['ipi']."', '".$campos[$i]['icms']."', '".$campos[$i]['preco_liquido']."', '".date('Y-m-d H:i:s')."') ";
		bancos::sql($sql);
	}
}
echo 'TOTAL HANSATÉCNICA WS -> '.$linhas.'<br>';

/********************************TDC********************************/
//1) Macho HSS ...
$sql = "SELECT pa.referencia, cpa.preco_bruto,	cpa.desc_a,	cpa.desc_b,	cpa.desc_c,	cpa.desc_d,	cpa.desc_e,	cpa.ipi, cpa.icms, cpa.preco_liquido, cpa.data_sys_ult_alt 
		FROM `concorrentes_vs_prod_acabados` cpa 
		INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = cpa.id_produto_acabado AND pa.id_gpa_vs_emp_div = '83' AND pa.referencia <> 'ESP' 
		WHERE cpa.`id_concorrente` = '10' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
	$referencia = substr($campos[$i]['referencia'], 0, 2).'H'.substr($campos[$i]['referencia'], 2, 6);

	$sql = "SELECT id_produto_acabado 
			FROM `produtos_acabados` 
			WHERE `referencia` = '$referencia' 
			AND `id_gpa_vs_emp_div` = '122' LIMIT 1 ";
	$campos_pa_heinz = bancos::sql($sql);
	if(count($campos_pa_heinz) == 1) {
		$sql = "INSERT INTO `concorrentes_vs_prod_acabados` (`id_concorrente_prod_acabado`, `id_concorrente`, `id_produto_acabado`, `preco_bruto`, `desc_a`, `desc_b`, `desc_c`, `desc_d`, `desc_e`, `ipi`, `icms`, `preco_liquido`, `data_sys_ult_alt`) VALUES (NULL, '10', '".$campos_pa_heinz[0]['id_produto_acabado']."', '".$campos[$i]['preco_bruto']."', '".$campos[$i]['desc_a']."', '".$campos[$i]['desc_b']."', '".$campos[$i]['desc_c']."', '".$campos[$i]['desc_d']."', '".$campos[$i]['desc_e']."', '".$campos[$i]['ipi']."', '".$campos[$i]['icms']."', '".$campos[$i]['preco_liquido']."', '".date('Y-m-d H:i:s')."') ";
		bancos::sql($sql);
	}
}
echo 'TOTAL TDC HSS -> '.$linhas.'<br>';

//2) Macho WS ...
$sql = "SELECT pa.referencia, cpa.preco_bruto,	cpa.desc_a,	cpa.desc_b,	cpa.desc_c,	cpa.desc_d,	cpa.desc_e,	cpa.ipi, cpa.icms, cpa.preco_liquido, cpa.data_sys_ult_alt 
		FROM `concorrentes_vs_prod_acabados` cpa 
		INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = cpa.id_produto_acabado AND pa.id_gpa_vs_emp_div = '22' AND pa.referencia <> 'ESP' 
		WHERE cpa.`id_concorrente` = '10' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
	$referencia = substr($campos[$i]['referencia'], 0, 2).'H'.substr($campos[$i]['referencia'], 2, 6);

	$sql = "SELECT id_produto_acabado 
			FROM `produtos_acabados` 
			WHERE `referencia` = '$referencia' 
			AND `id_gpa_vs_emp_div` = '123' LIMIT 1 ";
	$campos_pa_heinz = bancos::sql($sql);
	if(count($campos_pa_heinz) == 1) {
		$sql = "INSERT INTO `concorrentes_vs_prod_acabados` (`id_concorrente_prod_acabado`, `id_concorrente`, `id_produto_acabado`, `preco_bruto`, `desc_a`, `desc_b`, `desc_c`, `desc_d`, `desc_e`, `ipi`, `icms`, `preco_liquido`, `data_sys_ult_alt`) VALUES (NULL, '10', '".$campos_pa_heinz[0]['id_produto_acabado']."', '".$campos[$i]['preco_bruto']."', '".$campos[$i]['desc_a']."', '".$campos[$i]['desc_b']."', '".$campos[$i]['desc_c']."', '".$campos[$i]['desc_d']."', '".$campos[$i]['desc_e']."', '".$campos[$i]['ipi']."', '".$campos[$i]['icms']."', '".$campos[$i]['preco_liquido']."', '".date('Y-m-d H:i:s')."') ";
		bancos::sql($sql);
	}
}
echo 'TOTAL TDC WS -> '.$linhas.'<br>';
?>