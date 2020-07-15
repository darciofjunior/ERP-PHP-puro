<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/nota_entrada/itens/consultar.php', '../../../../../');
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
</head>
<frameset rows='90%, 10%' frameborder='NO' border='0' framespacing='0'>
    <frame name='itens' src='itens.php?id_nfe=<?=$_GET['id_nfe'];?>&pop_up=<?=$_GET['pop_up'];?>'>
<?
/*Esse parâmetro -> $clique_automatico_incluir_itens
Dispara um clique automático no botão de Incluir itens de NF, assim que acaba de ser gerado a Nova NF ...*/
?>
    <frame name='rodape' src='rodape.php?id_nfe=<?=$_GET['id_nfe'];?>&clique_automatico_incluir_itens=<?=$_GET['clique_automatico_incluir_itens'];?>&pop_up=<?=$_GET['pop_up'];?>' scrolling='NO'>
</frameset>
<noframes>
    <body>
    </body>
</noframes>
</html>