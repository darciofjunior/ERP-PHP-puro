<?
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/programacao/estoque/gerenciar/consultar.php', '../../../../../');
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
</head>
<frameset rows='90, 10' frameborder='no' border='0' framespacing='0'>
    <frame name='itens' src="itens.php?id_pedido_venda=<?=$_GET['id_pedido_venda'];?>&id_cliente=<?=$_GET['id_cliente'];?>">
    <frame name='rodape' src="rodape.php?id_pedido_venda=<?=$_GET['id_pedido_venda'];?>&id_cliente=<?=$_GET['id_cliente'];?>" scrolling='no'>
</frameset>
</html>