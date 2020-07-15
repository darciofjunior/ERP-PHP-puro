<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_revenda/pa_componente_revenda_esp.php', '../../../../');

$parametros = '';
$id_fornecedor_prod_insumos = '';

foreach ($chkt_fornecedor_prod_insumo as $dados) {
	$id_fornecedor_prod_insumos = $id_fornecedor_prod_insumos.$dados.' ,';
	$parametros = $parametros."'".$dados."', ";
}
$id_fornecedor_prod_insumos = substr($id_fornecedor_prod_insumos, 0, strlen($id_fornecedor_prod_insumos) - 2);
$parametros = substr($parametros, 0, strlen($parametros) - 2);

//Só trazemos PA(s) que são de Revenda ...
$sql = "SELECT pa.referencia, pa.discriminacao, gpa.nome, fpi.id_fornecedor_prod_insumo 
        FROM `fornecedores_x_prod_insumos` fpi 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = fpi.`id_produto_insumo` AND pa.`ativo` = '1' AND pa.`operacao_custo` = '1' 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
        WHERE fpi.`id_fornecedor_prod_insumo` IN ($id_fornecedor_prod_insumos) ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas < 1) {
?>
	<Script Language = 'JavaScript'>
		window.location = 'pa_componente_revenda_todos.php?passo=0&valor=2'
	</Script>
<?
}else {
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Cache-Control" content="no-store">
<meta http-equiv="Pragma" content="no-cache">
</head>
<frameset rows="*,0" frameborder="NO" border="0" framespacing="0">
	<frame name="cabecalho" scrolling="auto" src="cabecalho.php?id_fornecedor=<?=$id_fornecedor;?>&razaosocial=<?=$razaosocial;?>&chkt_fornecedor_prod_insumo=<?=$parametros;?>&atalho=<?=$atalho;?>" noresize>
</frameset>
<noframes>
	<body bgcolor="#FFFFFF" text="#000000">
	</body>
</noframes>
</html>
<?}?>
