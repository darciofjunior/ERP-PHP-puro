<?
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/programacao/estoque/gerenciar/consultar.php', '../../../../../../../');
?>
<html>
<head>
<title>.:: Calcular Frete ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function atualizar() {
    if(document.form.txt_valor_frete.value == '') {
        alert('DIGITE O VALOR DO FRETE ! ')
        document.form.txt_valor_frete.focus()
        document.form.txt_valor_frete.select()
        return false
    }
//Pergunta se deseja atualizar o Valor Frete na Tela de Baixo ...
    var resposta = confirm('DESEJA ATUALIZAR ESSE VALOR DO FRETE PARA A TELA DE VALE ?')
    if(resposta == true) {
        opener.document.form.txt_valor_frete.value = document.form.txt_valor_frete.value
        window.close()
    }
}
</Script>
</head>
<body onload='document.form.txt_valor_frete.focus()'>
<form name='form'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Calcular Frete
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='35%'>
            <b>Valor do Frete Site Correio: </b>
        </td>
        <td width='65%'>
            <input type='text' name='txt_valor_frete' title='Valor do Frete' size='15' maxlength='12' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick='document.form.txt_valor_frete.focus()' style='color:#ff9900' class='botao'>
            <input type='button' name='cmd_atualizar' value='Atualizar' title='Atualizar' onclick='atualizar()' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>
<pre>
* O Frete será acrescido de Impostos no Faturamento apenas para Notas Fiscais do Tipo "NF".
</pre>