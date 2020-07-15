<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/os/itens/consultar.php', '../../../../');
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'Cache-Control' content = 'no-store'>
<meta http-equiv = 'Pragma' content = 'no-cache'>
</head>
<frameset rows='90, 10' frameborder='no' border='0' framespacing='0'>
    <frame name='itens' src='itens.php?id_os=<?=$_GET['id_os'];?>'>
<?
/*Esse parâmetro -> $clique_automatico_cabecalho

Dispara um clique automático no botão de Alterar Cabeçalho, assim que acaba de ser gerado um Novo 
Pedido de lá do Orçamento pela Opção Gerar Pedido da Opção -> Outras Opções*/
?>
    <frame name='rodape' src='rodape.php?id_os=<?=$_GET['id_os'];?>&clique_automatico_cabecalho=<?=$clique_automatico_cabecalho;?>' scrolling='no'>
</frameset>
</html>