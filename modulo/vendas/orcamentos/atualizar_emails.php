<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../');
?>
<html>
<head>
<title>.:: Atualizar E-mails ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'Cache-Control' content = 'no-store'>
<meta http-equiv = 'Pragma' content = 'no-cache'>
<Script Language = 'JavaScript'>
//Atualiza a tela de baixo com a qual chamou o Pop-UP ...
function atualizar_abaixo() {
    window.top.opener.document.location = ''
}
</Script>
</head>
<frameset cols='50%, 50%' frameborder='no' border='0' framespacing='0' onunload='atualizar_abaixo()'>
    <frame name='alterar_dados_basicos' src = '../../classes/cliente/alterar_dados_basicos.php?id_cliente=<?=$_GET['id_cliente'];?>&nao_exibir_menu=1'>
    <frame name='rodape' src = '../../classes/cliente/contatos.php?id_cliente=<?=$_GET['id_cliente'];?>' scrolling='no'>
</frameset>
</html>