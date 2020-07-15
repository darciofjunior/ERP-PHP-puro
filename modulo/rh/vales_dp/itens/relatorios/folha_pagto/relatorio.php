<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/menu/menu.php');
?>
<html>
<head>
<title>.:: Imprimir Folha de Pagamento ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel='stylesheet' href = '../../../../../../css/layout.css' type='text/css'>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
    document.write(' * IMPRIMIR FOLHA DE PAGAMENTO ...')
    nova_janela('relatorio_pdf.php', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')
</Script>
</head>
</html>