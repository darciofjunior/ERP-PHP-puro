<?
require('../../../../lib/segurancas.php');

switch($_GET['opcao']) {
    case 1://Significa que veio do Menu Abertas / Liberadas ...
    case 2://Significa que veio do Menu de Liberadas / Faturadas ...
    case 3://Significa que veio do Menu de Faturadas / Empacotadas / Despachadas ...
        segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
    break;
    default://Significa que veio do Menu de Devolução ...
        segurancas::geral('/erp/albafer/modulo/faturamento/nota_saida/itens/devolucao.php', '../../../../');
    break;
}
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
</head>
<frameset rows='90, 10' frameborder='no' border='0' framespacing='0'>
    <frame name='itens' src="itens.php?id_nf=<?=$_GET['id_nf'];?>&opcao=<?=$_GET['opcao'];?>">
    <frame name='rodape' src="rodape.php?id_nf=<?=$_GET['id_nf'];?>&opcao=<?=$_GET['opcao'];?>" scrolling='no'>
</frameset>
</html>