<?
/*Gambiarra -> aki eu falo que está vindo do faturamento, mas as vezes posso estar acessandoessa 
tela de Itens de Pedido ...*/
$veio_faturamento   = 1;
$_GET['pop_up']     = 1;//Aqui é para não carregar o Menu do Sistema ...
require('../../orcamentos/itens/itens.php');
?>
<html>
<head>
<title>.:: Detalhes de Orçamento(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<form name='form'>
<body>
<table width='98%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</body>
</form>
</html>