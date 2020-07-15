<?
require('../../lib/segurancas.php');
?>
<html>
<head>
<title>.:: Opções de CEP ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function avancar() {
//Aqui eu verifico se existe pelo menos 1 option selecionado ...
    var elementos = document.form.elements
    var radios_selec = 0
    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'radio') {
            if(elementos[i].checked == true) radios_selec++
        }
    }
//Se não existir nenhuma opção selecionada ...
    if(radios_selec == 0) {
        alert('SELECIONE PELO MENOS UMA OPÇÃO !')
        document.form.opt_item[0].focus()
        return false
    }else {
//Se já existir alguma opção selecionada então ...
        if(document.form.opt_item[0].checked == true) {//Incluir Cep
            window.location = 'incluir.php'
        }else if(document.form.opt_item[1].checked == true) {//Alterar Cep
//Cep
            if(!texto('form', 'txt_cep', '9', '0123456789-', 'CEP', '2')) {
                return false
            }
            window.location = 'alterar.php?txt_cep='+document.form.txt_cep.value
        }
    }
}

function habilitar() {
//Se já existir alguma opção selecionada então ...
    if(document.form.opt_item[0].checked == true) {//Incluir Cep
        document.form.txt_cep.className = 'textdisabled'
        document.form.txt_cep.disabled  = true
        document.form.txt_cep.value     = ''
    }else if(document.form.opt_item[1].checked == true) {//Alterar Cep
        document.form.txt_cep.className = 'caixadetexto'
        document.form.txt_cep.disabled  = false
        document.form.txt_cep.focus()
    }
}
</Script>
</head>
<body>
<form name='form'>
<input type='hidden' name='passo' onclick="atualizar()">
<table width='70%' border="0" cellspacing ='1' cellpadding='1' align="center">
    <tr align='center'>
        <td>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td>
            Opções de CEP
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <input type="radio" name="opt_item" value="1" title="Incluir Cep" onclick="habilitar()" id="opt1" checked>
            <label for="opt1">
                Incluir Cep
            </label>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <input type="radio" name="opt_item" value="2" title="Alterar Cep" onclick="habilitar()" id="opt2">
            <label for="opt2">
                Alterar Cep
            </label>
            &nbsp;<input type="text" name="txt_cep" title="Digite o CEP" onkeyup="verifica(this, 'cep', '', '', event)" size="10" maxlength="9" class="textdisabled" disabled>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td>
            <input type="button" name="cmd_avancar" value="&gt;&gt; Avançar &gt;&gt;" title="Avançar" onclick="avancar()" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>