<?
require('../../lib/segurancas.php');

$sql 	= "SHOW TABLE STATUS FROM `erp_albafer` WHERE COMMENT = '' ";
$tables	= bancos::sql($sql);
$linhas = count($tables);

for($i = 0; $i < $linhas; $i++) echo "ALTER TABLE `".$tables[$i]['Name']."` COMMENT = 'tabela_por_ano';<br>";
?>