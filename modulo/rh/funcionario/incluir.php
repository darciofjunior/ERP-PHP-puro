<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/custos.php');
session_start('funcionarios');
segurancas::geral($PHP_SELF, '../../../');
?>
<html>
<head>
<title>.:: Incluir Funcionários ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
</head>
<frameset rows="100%,0" frameborder="yes" border="0" framespacing="0">
  <frame name="corpo" src="incluir2.php" scrolling="yes">
  <frame name="cep" src="buscar_cep.php" scrolling="no">
</frameset>
<noframes>
	<body bgcolor="#FFFFFF" text="#000000">
	</body>
</noframes>
</html>