<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/custos.php');
segurancas::geral('/erp/albafer/modulo/compras/variaveis/variaveis.php', '../../../');
session_start('funcionarios');

if($passo == 1) {
//Inserindo uma nova variável ...
	$sql = "Insert into variaveis (`id_variavel`, `valor`, `opcao`, `modulo_obs`) values (null, '$_POST[txt_valor]', '$_POST[txt_opcao]', '$_POST[txt_modulo_obs]') ";
	bancos::sql($sql);
?>
	<Script Language = 'Javascript'>
		alert('VARIÁVEL INCLUÍDA COM SUCESSO !')
		window.location = 'variaveis.php'
	</Script>
<?
}else {
?>
<html>
<head>
<title>.:: Incluir Variável(is) ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../css/layout.css'>
<Script Language = 'JavaScript' src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Opção ...
    if(!texto('form', 'txt_opcao', '1', '1234567890QWERTYUIOPÇLKJHGFDSAZXCVBNM zaqwsxcderfvbgtyhnmjuik,.lopç;áéíóúÁÉÍÓÚÂÊÎÔÛâêîôûãõÃÕÜüÀà!@#$%¨&*()(_-+¹²³££¢¬§ªº°|\,.<>;:{[}]/Ø= "', 'OPÇÃO', '1')) {
        return false
    }
//Valor ...
    if(!texto('form', 'txt_valor', '1', '1234567890,.', 'VALOR', '2')) {
        return false
    }
    limpeza_moeda('form', 'txt_valor, ')
}
</Script>
</head>
<body onload="document.form.txt_opcao.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onsubmit="return validar()">
<table align="center" width="60%" cellpadding="1" cellspacing="1">
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			Incluir Variável
		</td>
	</tr>
	<tr class="linhanormal">
		<td>
			<b>Opção:</b>
		</td>
		<td>
			<input type="text" name="txt_opcao" title="Digite a Opção" maxlength="75" size="70" class="caixadetexto">
		</td>
	</tr>
	<tr class="linhanormal">
		<td>
			<b>Valor:</b>
		</td>
		<td>
			<input type="text" name="txt_valor" title="Digite o Valor" onkeyup="verifica(this, 'moeda_especial', '4', '', event)" class="caixadetexto">
		</td>
	</tr>
	<tr class="linhanormal">
		<td>
			Módulo Observação:
		</td>
		<td>
			<textarea name='txt_modulo_obs' cols='43' rows='2' maxlength='85' class='caixadetexto'></textarea>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'variaveis.php'" class="botao">
			<input type="button" name="cmd_limpar" value="Limpar" title="Limpar" onclick="redefinir('document.form', 'LIMPAR');document.form.txt_valor.focus()" style="color:#ff9900" class="botao">
			<input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>