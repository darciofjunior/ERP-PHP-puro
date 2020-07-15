<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral('/erp/albafer/modulo/rh/hora_extra/opcoes_gerenciar_hora_extra.php', '../../../');
?>
<html>
<head>
<title>.:: Imprimir Gerenciar ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<iframe name='ifr_imprimir_gerenciar' src='imprimir_gerenciar.php?txt_data_inicial=<?=$_GET['txt_data_inicial'];?>&txt_data_final=<?=$_GET['txt_data_final'];?>&txt_data_pagamento=<?=$_GET['txt_data_pagamento'];?>' width='100%' height='520' frameborder='0'></iframe>
<center>
    <input type='button' name='cmd_imprimir_html' value='Imprimir HTML' title='Imprimir HTML' onclick='parent.ifr_imprimir_gerenciar.print()' style='color:red' class='botao'>
</center>
</body>
</html>