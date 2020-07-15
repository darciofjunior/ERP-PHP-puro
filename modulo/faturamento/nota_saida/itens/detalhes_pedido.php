<?
//Aqui chama a parte de Pedidos
$nao_verificar_sessao  = 1;
$veio_faturamento = 1;
require('../../../vendas/pedidos/itens/itens.php');
?>
<html>
<head>
<title>.:: Detalhes de Pedido(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<form name='form'>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<table width='1004' border=0 align='center' cellspacing=1 cellpadding=1>
	<tr align="center">
		<td>
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="window.close()" style="color:red" class="botao">
		</td>
	</tr>
</table>
</body>
</form>
</html>