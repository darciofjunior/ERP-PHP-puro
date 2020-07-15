<?
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/estoque_i_c/conversoes/consultar.php', '../../../../../');
?>
<html>
<head>
<title>.:: Convers&otilde;es ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'Cache-Control' content = 'no-store'>
<meta http-equiv = 'Pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<frameset rows='90, 10' frameborder='no' border='0' framespacing='0'>
    <frame name='itens' src="itens.php?id_conversoes_temps=<?=$id_conversoes_temps;?>">
    <frame name='rodape' src="rodape.php?id_conversoes_temps=<?=$id_conversoes_temps;?>" scrolling='no'>
</frameset>
</html>