<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/produtos_fornecedores/lista_preco/lista_precos.php', '../../../../');
?>
<html>
<head>
<title>.:: Cálculo do Desconto ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function calcular() {
    if(document.form.txt_desconto_av_sgd.value != '') {
        desconto_av     = strtofloat(document.form.txt_desconto_av.value)
        desconto_av_sgd = strtofloat(document.form.txt_desconto_av_sgd.value)

        if(desconto_av == '' || desconto_av_sgd == '') document.form.txt_desconto_sgd.value = ''
        document.form.txt_desconto_sgd.value = (1 - (1 / ((100 - desconto_av) / (100 - desconto_av_sgd)))) * 100

        if(document.form.txt_desconto_sgd.value == 'NaN') {
            document.form.txt_desconto_sgd.value = ''
        }else {
            if(document.form.txt_desconto_sgd.value < 0) {
                document.form.txt_desconto_sgd.style.color = '#FF0000'
            }else {
                document.form.txt_desconto_sgd.style.color = '#00229E'
            }
            document.form.txt_desconto_sgd.value = arred(document.form.txt_desconto_sgd.value, 2, 1)
        }
    }else {
        document.form.txt_desconto_sgd.value = ''
    }
}

function retirar(formulario, campo) {
    var elemento = eval('document.'+formulario+'.'+campo+'')
    var caracteres = '-0123456789,'

    for (x = 0; x < elemento.value.length; x ++) {
        if (caracteres.indexOf(elemento.value.charAt(x), 0) == -1) elemento.value = elemento.value.replace(elemento.value.charAt(x), '')
    }
}
</Script>
</head>
<body onload='document.form.txt_desconto_av.focus()'>
<form name='form' method='post'>
<table width='90%' border='1' cellspacing='0' cellpadding='0' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            CÁLCULO DO DESCONTO SGD, QUANDO TIVERMOS O DESCONTO AV + SGD JUNTO:
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>DESCONTO AV:</b>
        </td>
        <td>
            <input type='text' name='txt_desconto_av' title='Desconto AV' onkeyup="verifica(this,'moeda_especial', '2', '', event);calcular()" size='15' maxlength='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>DESCONTO AV + SGD:</b>
        </td>
        <td>
            <input type='text' name='txt_desconto_av_sgd' title='Desconto AV + SGD' size='15' maxlength='15' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" class='caixadetexto'>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>DESCONTO SGD CALCULADO:</b>
        </td>
        <td>
            <input type='text' name='txt_desconto_sgd' title='Desconto SGD Calculado' size='15' maxlength='15' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>