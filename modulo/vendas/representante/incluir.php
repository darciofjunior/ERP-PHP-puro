<?
require('../../../lib/segurancas.php');
segurancas::geral($PHP_SELF, '../../../');
?>
<html>
<head>
<title>.:: Incluir Representante(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<frameset rows='100%, 0' frameborder='yes' border='0' framespacing='0'>
    <frame name='corpo' src='incluir2.php' scrolling='yes'>
    <frame name='cep' src='buscar_cep.php' scrolling='no'>
</frameset>
</html>