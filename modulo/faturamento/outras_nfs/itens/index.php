<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/faturamento/outras_nfs/itens/alterar_imprimir.php', '../../../../');
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'Cache-Control' content = 'no-store'>
<meta http-equiv = 'Pragma' content = 'no-cache'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<frameset rows='90, 10' frameborder='no' border='0' framespacing='0'>
    <frame name='itens' src='itens.php?id_nf_outra=<?=$_GET['id_nf_outra'];?>'>
    <frame name='rodape' src='rodape.php?id_nf_outra=<?=$_GET['id_nf_outra'];?>' scrolling='NO'>
</frameset>
</html>