<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
segurancas::geral('/erp/albafer/modulo/vendas/representante/comissoes/margem_lucro/comissao.php', '../../../../../');

$mensagem[1] = "<font class='confirmacao'>COMISSÃO MARGEM DE LUCRO ALTERADA COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>COMISSÃO MARGEM DE LUCRO JÁ EXISTENTE.</font>";

if(!empty($_POST['txt_margem_lucro'])) {
    $data_sys = date('Y-m-d H:i:s');
    /*Verifico se já existe uma Comissão Margem de Lucro cadastrada com esses valores digitados pelo usuário, 
    diferente da atual que está sendo alterada no momento ...*/
    $sql = "SELECT id_comissao_margem_lucro 
            FROM `comissoes_margens_lucros` 
            WHERE `percentual` = '$_POST[txt_margem_lucro]' 
            AND `com_perc_interno` = '$_POST[txt_com_vend_interno]' 
            AND `com_perc_externo` = '$_POST[txt_com_vend_externo]' 
            AND `com_perc_externo_esp` = '$_POST[txt_comissao_autonomo]' 
            AND `id_comissao_margem_lucro` <> '$_POST[id_comissao_margem_lucro]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Comissão Margem de Lucro não Existente
        $sql = "UPDATE `comissoes_margens_lucros` SET `percentual` = '$_POST[txt_margem_lucro]', `com_perc_interno` = '$_POST[txt_com_vend_interno]', `com_perc_externo` = '$_POST[txt_com_vend_externo]', `com_perc_externo_esp` = '$_POST[txt_comissao_autonomo]', `data_sys` = '$data_sys' WHERE `id_comissao_margem_lucro` = '$_POST[id_comissao_margem_lucro]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Comissão Margem de Lucro Existente
        $valor = 2;
    }
}

//Procedimento normal de quando se carrega a Tela ...
$id_comissao_margem_lucro = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_comissao_margem_lucro'] : $_GET['id_comissao_margem_lucro'];

//Aqui traz dados da Comissão Margem de Lucro passada por parâmetro ...
$sql = "SELECT * 
        FROM `comissoes_margens_lucros` 
        WHERE `id_comissao_margem_lucro` = '$id_comissao_margem_lucro' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<title>.:: Alterar Comissão Margem de Lucro ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../js/arred.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Margem de Lucro
	if(!texto('form', 'txt_margem_lucro', '3', '-1234567890,', 'MARGEM DE LUCRO', '1')) {
		return false
	}
//Comissão Vend. Interno
	if(document.form.txt_com_vend_interno.value != '') {
		if(!texto('form', 'txt_com_vend_interno', '3', '1234567890,', 'COMISSÃO VENDEDOR INTERNO', '1')) {
			return false
		}
	}
//Comissão Vend. Externo
	if(document.form.txt_com_vend_externo.value != '') {
		if(!texto('form', 'txt_com_vend_externo', '3', '1234567890,', 'COMISSÃO VENDEDOR EXTERNO', '1')) {
			return false
		}
	}
//Comissão Autônomo
	if(document.form.txt_comissao_autonomo.value != '') {
		if(!texto('form', 'txt_comissao_autonomo', '3', '1234567890,', 'COMISSÃO AUTONÔMO', '1')) {
			return false
		}
	}
	return limpeza_moeda('form', 'txt_margem_lucro, txt_com_vend_interno, txt_com_vend_externo, txt_comissao_autonomo, ')
}

function comissao_direta_fora_sp() {
	if(document.form.txt_com_vend_externo.value != '') {
		var comissao_vendedor_externo = eval(strtofloat(document.form.txt_com_vend_externo.value))
		document.form.txt_com_direta_fora_sp.value = comissao_vendedor_externo - 1.0
		document.form.txt_com_direta_fora_sp.value = arred(document.form.txt_com_direta_fora_sp.value, 2, 1)
	}else {
		document.form.txt_com_direta_fora_sp.value = ''
	}
}

function comissao_interior() {
	if(document.form.txt_comissao_autonomo.value != '') {
		var comissao_autonomo = eval(strtofloat(document.form.txt_comissao_autonomo.value))
		document.form.txt_com_interior.value = comissao_autonomo - 0.5
		document.form.txt_com_interior.value = arred(document.form.txt_com_interior.value, 2, 1)
	}else {
		document.form.txt_com_interior.value = ''
	}
}
</Script>
</head>
<body onLoad="document.form.txt_margem_lucro.focus()">
<form name="form" method="post" action='' onSubmit="return validar()">
<input type="hidden" name="id_comissao_margem_lucro" value="<?=$campos[0]['id_comissao_margem_lucro']?>">
<table border="0" width="750" align="center" cellspacing='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Comissão Margem de Lucro
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Desconto Total :</b>
        </td>
        <td>
            <input type='text' name='txt_margem_lucro' value='<?=number_format($campos[0]['percentual'], 2, ',', '.')?>' title='Digite a Margem de Lucro' size='8' maxlength='9' onkeyup="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'> %
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Comiss&atilde;o Vendedor Interno / Sup. Interno:
        </td>
        <td>
            <input type='text' name='txt_com_vend_interno' value='<?=number_format($campos[0]['com_perc_interno'], 2, ',', '.')?>' title='Digite a Comissão Vend. Interno' size='8' maxlength='6' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'> %
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Comiss&atilde;o Vendedor Externo - SP:
        </td>
        <td>
            <input type='text' name='txt_com_vend_externo' value='<?=number_format($campos[0]['com_perc_externo'], 2, ',', '.')?>' title='Digite a Comissão Vend. Externo' size='8' maxlength='6' onkeyup="verifica(this, 'moeda_especial', '2', '', event);comissao_direta_fora_sp()" class='caixadetexto'> %
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Comiss&atilde;o Vendedor Externo Fora do Estado:
        </td>
        <td>
            <input type='text' name='txt_com_direta_fora_sp' value='<?=number_format($campos[0]['com_perc_externo'] - 0.5, 2, ',', '.')?>' title='Comissão Direta fora de SP' size='8' maxlength='6' class='textdisabled' disabled> %
            (Comiss&atilde;o Vendedor Externo - SP) - 1,00%
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Comiss&atilde;o Vendedor Externo no Interior:
        </td>
        <td>
            <input type='text' name='txt_com_interior' value='<?=number_format($campos[0]['com_perc_externo_esp'] - 0.5, 2, ',', '.')?>' title='Comissão Interior' size='8' maxlength='6' class='textdisabled' disabled> %
            (Comiss&atilde;o Representantes Aut&ocirc;nomo:) - 0,50%
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Comiss&atilde;o Representantes Aut&ocirc;nomo:
        </td>
        <td>
            <input type='text' name='txt_comissao_autonomo' value='<?=number_format($campos[0]['com_perc_externo_esp'], 2, ',', '.')?>' title='Digite a Comissão Autônomo' size='8' maxlength='6' onkeyup="verifica(this, 'moeda_especial', '2', '', event);comissao_interior()" class='caixadetexto'> %
        </td>
    </tr>	
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'comissao.php'" class='botao'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_margem_lucro.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>