<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/consultar.php', '../../../../');
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
</head>
<frameset rows='90%, 10%' frameborder='NO' border='0' framespacing='0'>
    <frame name='itens' src='itens.php?id_pedido=<?=$_GET['id_pedido'];?>&pop_up=<?=$_GET['pop_up'];?>'>
<?
/*Esse parâmetro -> $clique_automatico_cabecalho
Dispara um clique automático no botão de Alterar Cabeçalho, assim que acaba de ser gerado um Novo Pedido*/
?>
    <frame name='rodape' src='rodape.php?id_pedido=<?=$_GET['id_pedido'];?>&clique_automatico_cabecalho=<?=$_GET['clique_automatico_cabecalho'];?>&pop_up=<?=$_GET['pop_up'];?>' scrolling='NO'>
</frameset>
<noframes>
    <body>
    </body>
</noframes>
</html>