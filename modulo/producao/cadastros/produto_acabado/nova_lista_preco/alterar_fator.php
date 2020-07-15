<?
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/lista_preco/lista_preco.php', '../../../../../');
?>
<html>
<title>.:: Alterar Fator ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function calcular() {
    var desconto_a, desconto_b, desconto_c, desconto_d = 0
//Desconto A ...
    if(document.form.txt_desconto_a.value != '') {
        desconto_a = eval(strtofloat(document.form.txt_desconto_a.value))
    }
//Desconto B ...
    if(document.form.txt_desconto_b.value != '') {
        desconto_b = eval(strtofloat(document.form.txt_desconto_b.value))
    }
//Desconto C ...	
    if(document.form.txt_desconto_c.value != '') {
        desconto_c = eval(strtofloat(document.form.txt_desconto_c.value))
    }
//Desconto D ...
    if(document.form.txt_desconto_d.value != '') {
        desconto_d = eval(strtofloat(document.form.txt_desconto_d.value))
    }
//Cálculo do Fator de acordo com a Quantidade de Descontos Preenchidos ...	
    if(desconto_a > 0 && desconto_b > 0 && desconto_c > 0 && desconto_d > 0) {
        fator = (100 - desconto_a) / 100 * (100 - desconto_b) / 100 * (100 - desconto_c) / 100 * (100 - desconto_d) / 100
    }else if(desconto_a > 0 && desconto_b > 0 && desconto_c > 0) {
        fator = (100 - desconto_a) / 100 * (100 - desconto_b) / 100 * (100 - desconto_c) / 100
    }else if(desconto_a > 0 && desconto_b > 0) {
        fator = (100 - desconto_a) / 100 * (100 - desconto_b) / 100
    }else if(desconto_a > 0) {
        fator = (100 - desconto_a) / 100
    }
    document.form.txt_fator.value = fator
    document.form.txt_fator.value = arred(document.form.txt_fator.value, 4, 1)
}

function atualizar() {
//Desconto A ...
    if(!texto('form', 'txt_desconto_a', '3', '0123456789,.', 'DESCONTO A', '2')) {
        return false
    }
//Desconto B ...
    if(document.form.txt_desconto_b.value != '') {
        if(!texto('form', 'txt_desconto_b', '3', '0123456789,.', 'DESCONTO B', '2')) {
            return false
        }
    }
//Desconto C ...
    if(document.form.txt_desconto_c.value != '') {
        if(!texto('form', 'txt_desconto_c', '3', '0123456789,.', 'DESCONTO C', '2')) {
            return false
        }
    }
//Desconto D ...
    if(document.form.txt_desconto_d.value != '') {
        if(!texto('form', 'txt_desconto_d', '3', '0123456789,.', 'DESCONTO D', '2')) {
            return false
        }
    }
    var indice_coluna = eval('<?=$_GET['indice_coluna'];?>')
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA ATUALIZAR COM ESSE VALOR NA LISTA DE PREÇO ?')
    if(resposta == true) {//Atualiza a Tela de Lista que chamou este Pop-Up ...
//Atribuindo os Valores p/ a Lista de Preço ...
        opener.document.form.elements[indice_coluna].value = document.form.txt_fator.value
        opener.calcular_preco(0)
        window.close()
    }
}
</Script>
</head>
<body onload='document.form.txt_desconto_a.focus()'>
<form name='form'>
<table width='90%' border="0" cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align="center">
        <td colspan='2'>
            Alterar Fator
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Desconto A:</b>
        </td>
        <td>
            <input type='text' name='txt_desconto_a' title='Digite o Desconto A' size='15' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Desconto B:
        </td>
        <td>
            <input type='text' name='txt_desconto_b' title='Digite o Desconto B' size='15' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Desconto C:
        </td>
        <td>
            <input type='text' name='txt_desconto_c' title='Digite o Desconto C' size='15' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Desconto D:
        </td>
        <td>
            <input type='text' name='txt_desconto_d' title='Digite o Desconto D' size='15' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fator:
        </td>
        <td>
            <input type='text' name="txt_fator" title="Fator" size="15" maxlength="7" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_desconto_a.focus()" style='color:#ff9900' class='botao'>
            <input type='button' name='cmd_atualizar' value='Atualizar' title='Atualizar' style='color:green' onclick='atualizar()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>