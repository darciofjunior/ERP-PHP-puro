<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');
?>
<html>
<head>
<title>.:: Relatório de Estoque P.A. ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <iframe name='ifr_relatorio_semanal_visita' width='100%' height='500' src='imprimir_relatorio.php' frameborder='0'></iframe>
    
    <tr id='linha_imprimir' style='visibility:hidden' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='parent.ifr_relatorio_semanal_visita.print()' class='botao'>
        </td>
    </tr>
</table>
</body>
</html>