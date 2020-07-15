<?
require('../../../../lib/segurancas.php');
segurancas::geral($PHP_SELF, '../../../../');
session_start('funcionarios');
?>
<html>
<head>
<title>.:: Incluir Vários ::.</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Cache-Control" content="no-store">
<meta http-equiv="Pragma" content="no-cache">
</head>
<frameset rows="215,*" frameborder="no" border=0 framespacing="0">
 <frameset cols="50%,50%" frameborder="no" border=0 framespacing="0">
  <frame name="consultar_maquina" src="consultar_maquina.php" scrolling="NO" noresize>
  <frame name="consultar_funcionario" src="consultar_funcionario.php" scrolling="NO" noresize>
 </frameset>
 <frameset frameborder="no" border=0 framespacing="0">
  <frame name="juncao" src="juncao.php" scrolling="NO">
 </frameset>
</frameset>
<noframes>
	<body bgcolor="#FFFFFF" text="#000000">
	</body>
</noframes>
</html>
