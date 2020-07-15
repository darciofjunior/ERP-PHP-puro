<?
require('../../../../lib/segurancas.php');
segurancas::geral($PHP_SELF, '../../../../');
?>
<html>
<head>
<title>.:: Comparativo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
</head>
<frameset frameborder="0"  rows="90%,*">
    <frame border='0' name="itens" Src="itens.php?id_prods_insumos=<?=$id_prods_insumos;?>" scrolling="auto">
    <frame border='0' name="rodape" Src="rodape.php?id_prods_insumos=<?=$id_prods_insumos;?>" scrolling="NO">
</frameset>
</html>