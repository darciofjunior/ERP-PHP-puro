<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../../');
?>
<html>
<head>
<title>.:: Requisição de Materiais ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<frameset rows='90,10' frameborder='no' border='0' framespacing='0'>
<!--
/*Aqui não tem jeito além do id_pedido que eu já levava, eu tenho que levar esses 2 parâmetros que são 
daqui de requisição: os Itens de Pedido que foram escolhidos e as Qtdes Digitadas, 
+ o parâmetro de criar 1 antecipação*/
-->
    <frame name='itens' src = 'itens.php?id_pedido=<?=$_GET['id_pedido'];?>&chkt_item_pedido=<?=$_GET['chkt_item_pedido'];?>&txt_qtde=<?=$_GET['txt_qtde'];?>&criar_antecipacao=<?=$_GET['criar_antecipacao'];?>' noresize>
    <frame name='rodape' src = 'rodape.php?id_pedido=<?=$_GET['id_pedido'];?>&chkt_item_pedido=<?=$_GET['chkt_item_pedido'];?>&txt_qtde=<?=$_GET['txt_qtde'];?>&criar_antecipacao=<?=$_GET['criar_antecipacao'];?>' scrolling='no'>
</frameset>
</html>