<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
session_start('funcionarios');

$mensagem[1] = "<font class='confirmacao'>TRANSPORTADORA INCLUIDA COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>TRANSPORTADORA J¡ EXISTENTE.</font>";

if(!empty($_POST['txt_transportadora'])) {
    $data_sys = date('Y-m-d H:i:s');
//Verifica no Banco de Dados se existe alguma transportadora com esse CNPJ, as ˙nicas que podem manter os mesmos CNPJS s„o as de Correio ...
    $sql = "SELECT id_transportadora 
            FROM `transportadoras` 
            WHERE `cnpj` = '$_POST[txt_cnpj]' 
            AND (`nome` NOT LIKE '%CORREIO%' AND `nome_fantasia` NOT LIKE '%CORREIO%') 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//N„o existe ...
        $sql = "INSERT INTO `transportadoras` (`id_transportadora`, `nome`, `nome_fantasia`, `email`, `tipo_transporte`, `endereco`, `num_complemento`, `bairro`, `cidade`, `uf`, `cep`, `fone`, `fone2`, `cnpj`, `ie`, `data_sys`, `ativo`) values (null, '$_POST[txt_transportadora]', '$_POST[txt_nome_fantasia]', '$_POST[txt_email]', '$_POST[cmb_tipo_transporte]', '$_POST[txt_endereco]', '$_POST[txt_num_complemento]', '$_POST[txt_bairro]', '$_POST[txt_cidade]', '$_POST[txt_estado]', '$_POST[txt_cep]', '$_POST[txt_telefone1]', '$_POST[txt_telefone2]', '$_POST[txt_cnpj]', '$_POST[txt_ie]', '$data_sys', '1') ";
        bancos::sql($sql);
        $valor = 1;
    }else {//J· existe ...
        $valor = 2;
    }
}
?>
<html>
<title>.:: Incluir Transportadora(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Transportadora
	if(!texto('form', 'txt_transportadora', '3', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'TRANSPORTADORA (RAZ√O SOCIAL)', '1')) {
		return false
	}
//Nome Fantasia
	if(document.form.txt_nome_fantasia.value != '') {
		if(!texto('form', 'txt_nome_fantasia', '3', "-1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}&*()™∫∞;\/ ", 'NOME FANTASIA', '2')) {
			return false
		}
	}
//E-mail
	if(document.form.txt_email.value != '') {
		if (!new_email('form', 'txt_email')) {
			return false
		}
	}
//Tipo de Transporte
	if(!combo('form', 'cmb_tipo_transporte', '', 'SELECIONE O TIPO DE TRANSPORTE !')) {
		return false
	}
//Cep
	if(!texto('form', 'txt_cep', '9', '-1234567890', 'CEP', '2')) {
		return false
	}
//N˙mero / Complemento
	if(!texto('form', 'txt_num_complemento', '1', "-¢{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,()™∫∞ ", 'N⁄MERO / COMPLEMENTO', '2')) {
		return false
	}
//Telefone 1
	if(!texto('form', 'txt_telefone1', '3', '0123456789', 'TELEFONE 1', '2')) {
		return false
	}
//Telefone 2
	if(document.form.txt_telefone2.value != '') {
		if(!texto('form', 'txt_telefone2', '3', '0123456789', 'TELEFONE 2', '2')) {
			return false
		}
	}
//CNPJ
	if(document.form.txt_cnpj.value == '') {
		alert('DIGITE O CNPJ !')
		document.form.txt_cnpj.focus()
		return false
	}
//Controle com o CNPJ ...
	if(document.form.txt_cnpj.value == '0') {
		alert('CNPJ INV¡LIDO !')
		document.form.txt_cnpj.focus()
		document.form.txt_cnpj.select()
		return false
	}

	nro= document.form.txt_cnpj.value
	if(nro.length > 14) {
		for(i=0; i < nro.length; i++) {
			letra = nro.charAt(i)
			if((letra == '.') || (letra == '/') || (letra == '-')){
				nro = nro.replace(letra,'')
			}
		}
		document.form.txt_cnpj.value = nro
		if (!cnpj('form','txt_cnpj')) {
			return false
		}
	}else {
		if (!cnpj('form','txt_cnpj')) {
			return false
		}
	}
//IE
	if(!texto('form', 'txt_ie', '3', '0123456789', 'INSCRI«√O ESTADUAL', '1')) {
		return false
	}
//Desabilito esses campos para poder gravar no BD ...
	document.form.txt_endereco.disabled = false
	document.form.txt_bairro.disabled = false
	document.form.txt_cidade.disabled = false
	document.form.txt_estado.disabled = false
}

function buscar_cep(cep_digitado) {
    iframe_buscar_cep.location = '../../classes/cep/buscar_cep.php?txt_cep='+cep_digitado
}
</Script>
<body onload="document.form.txt_transportadora.focus()">
<form name="form" method="post" action='' onSubmit='return validar()'>
<table border="0" width="80%" cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Transportadora(s)
        </td>
    </tr>
	<tr class='linhanormal'>
		<td width='40%'>
			<b>Transportadora (Raz„o Social):</b>
		</td>
		<td width='40%'>
			Nome Fantasia:
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<input type="text" name="txt_transportadora" title="Digite a Transportadora" size="40" maxlength="50" class="caixadetexto">
		</td>
		<td>
			<input type="text" name="txt_nome_fantasia" title="Digite o Nome Fantasia" size="40" maxlength="50" class="caixadetexto">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>Tipo de Transporte:</b>
		</td>
		<td>
			Email:
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<select name="cmb_tipo_transporte" title="Selecione o Tipo de Transporte" class="combo">
				<option value="" style="color:red">SELECIONE</option>
				<option value="A">A…REO</option>
				<option value="R" selected>RODOVI¡RIO</option>
				<option value="RA">RODOVI¡RIO / A…REO</option>
			</select>
		</td>
		<td>
			<input type="text" name="txt_email" title="Digite o E-mail" size="50" maxlength="80" class="caixadetexto">
		</td>
	</tr>
	<tr class='linhadestaque' align="center">
		<td colspan="2">
			<font color='#FFFFFF' size='-1'>
				Dados de EndereÁo
			</font>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan="2">
			<b>Cep:</b>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan="2">
			<input type="text" name="txt_cep" title="Digite o CEP" size="11" maxlength="9" onkeyup="verifica(this, 'cep', '', '', event)" onblur="buscar_cep(this.value)" class="caixadetexto">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			EndereÁo:
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<b>N.∫ / Complemento:</b>
		</td>
		<td>
			Bairro:
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<input type="text" name="txt_endereco" title="EndereÁo" size="55" maxlength="50" class="textdisabled" disabled>
			&nbsp;
			<input type="text" name="txt_num_complemento" size="10" maxlength="50" class="caixadetexto" title="N˙mero, Complemento, ...">
		</td>
		<td>
			<input type="text" name="txt_bairro" title="Bairro" size="25" maxlength="20" class="textdisabled" disabled>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			Cidade:
		</td>
		<td>
			Estado:
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
                    <input type="text" name="txt_cidade" title="Cidade" maxlength='30' size='25' class="textdisabled" disabled>
		</td>
		<td>
                    <input type="text" name="txt_estado" title="Estado" maxlength="2" size="3" class="textdisabled" disabled>
		</td>
	</tr>
	<tr class='linhadestaque' align="center">
		<td colspan="2">
			&nbsp;
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>Telefone 1:</b>
		</td>
		<td>
			Telefone 2:
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<input type="text" name="txt_telefone1" title="Digite o Telefone 1" size="17" maxlength="15" class="caixadetexto">
		</td>
		<td>
			<input type="text" name="txt_telefone2" title="Digite o Telefone 2" size="17" maxlength="15" class="caixadetexto">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>CNPJ:</b>
		</td>
		<td>
			<b>InscriÁ„o Estadual:</b>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<input type="text"  name="txt_cnpj" title="Digite o CNPJ" size="20" maxlength="18" class="caixadetexto">
		</td>
		<td>
			<input type="text" name="txt_ie" title="Digite a InscriÁ„o Estadual" size="20" maxlength="20" class="caixadetexto">
		</td>
	</tr>
	<tr class="linhacabecalho" align='center'>
		<td colspan='2'>
			<input type="button" name="cmd_limpar" value="Limpar" title="Limpar" onclick="redefinir('document.form', 'LIMPAR');document.form.txt_transportadora.focus()" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
		</td>
	</tr>
	<tr>
		<td colspan='2'>
			<iframe name='iframe_buscar_cep' width='0' height='0' style='border-width:0px'></iframe>
		</td>
	</tr>
</table>
</form>
</body>
</html>