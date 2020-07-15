<?
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/nova_lista_preco/lista_preco.php', '../../../../../');
?>
<html>
<title>.:: Alterar Margem de Lucro ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function atualizar() {
    if(!texto('form', 'txt_margem_lucro', '3', '0123456789,.', 'MARGEM DE LUCRO', '1')) {
        return false
    }
    var indice_coluna = eval('<?=$_GET['indice_coluna'];?>')
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA ATUALIZAR COM ESSE VALOR NA LISTA DE PREÇO ?')
    if(resposta == true) {//Atualiza a Tela de Lista que chamou este Pop-Up ...
        margem_lucro_zero       = parent.document.form.elements[14].value//Valor que está na Lista de Preço
        margem_lucro_digitada   = eval(strtofloat(document.form.txt_margem_lucro.value))
//Atribuindo os Valores p/ a Lista de Preço ...
        parent.document.form.elements[indice_coluna].value = document.form.txt_margem_lucro.value
        parent.document.form.elements[indice_coluna - 1].value = margem_lucro_zero * (1 + margem_lucro_digitada / 100)//Vlr Novo Pço na Lista ...
        parent.document.form.elements[indice_coluna - 1].value = arred(parent.document.form.elements[indice_coluna - 1].value, 2, 1)
        parent.calcular_fator(0)
        parent.fechar_pop_up_div()
    }
}
</Script>
</head>
<body onload='document.form.txt_margem_lucro.focus()'>
<form name='form'>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Margem de Lucro
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Margem de Lucro:</b>
        </td>
        <td>
            <input type='text' name='txt_margem_lucro' title='Digite a Margem de Lucro' size='20' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_margem_lucro.focus()" style='color:#ff9900' class='botao'>
            <input type='button' name='cmd_atualizar' value='Atualizar' title='Atualizar' style='color:green' onclick='atualizar()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>