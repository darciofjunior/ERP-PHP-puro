<?
require('../../../../../lib/segurancas.php');
if($itens != 1) {
	segurancas::geral($PHP_SELF, '../../../../../');
}
session_start("funcionarios");
session_unregister('id_emp');
$GLOBALS['id_emp']=$id_emp2;
session_start("funcionarios");
session_register('id_emp');
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Cache-Control" content="no-store">
<meta http-equiv="Pragma" content="no-cache">
</head>
<frameset rows="75,10" frameborder="NO" border="0" framespacing="0">
  <frame name="itens" src="../classes/cabecalho.php?valor=<?=$valor;?>">
  <frame name="rodape" src="../classes/rodape.php" scrolling="NO">
</frameset>
<noframes>
	<body bgcolor="#FFFFFF" text="#000000">
	</body>
</noframes>
</html>
