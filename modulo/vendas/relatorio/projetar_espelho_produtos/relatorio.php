<?
require('../../../../lib/segurancas.php');
//Se essa essa Tela n�o foi aberta como sendo Pop-UP ent�o exibo o menu no Cabe�alho da P�gina ...
if(empty($_GET['pop_up'])) require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');
?>
<html>
<head>
<title>.:: Relat�rio de Pedido(s) Fam�lia / Grupo dos �ltimos (6 anos) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<iframe name='iframe_relatorio' src='iframe_relatorio.php?cmb_tipo_relatorio=<?=$_GET['cmb_tipo_relatorio'];?>&cmb_representante=<?=$_GET['cmb_representante'];?>&cmb_cliente=<?=$_GET['cmb_cliente'];?>' width='100%' height='100%' frameborder='0'>