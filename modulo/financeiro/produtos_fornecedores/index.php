<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');
?>
<html>
<head>
<title>.:: Incluir Vários ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'Cache-Control' content= 'no-store'>
<meta http-equiv = 'Pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<body>
<table border='0' width='90%' align="center" cellspacing='1' cellpadding='1'>
    <tr>
        <td>
            <iframe name='consultar_fornec' src='consultar_fornec.php' width='100%' height='250' scrolling='no' frameborder='0' noresize></iframe>
        </td>
        <td>
            <iframe name='consultar_prod' src='consultar_prod.php' width='100%' height='250' scrolling='no' frameborder='0' noresize></iframe>
        </td>
    </tr>
    <tr>
        <td colspan='2'>
            <iframe name='juncao' src='juncao.php' width='100%' height='300' scrolling='no' frameborder='0' noresize></iframe>
        </td>
    </tr>
</table>
</body>
</html>