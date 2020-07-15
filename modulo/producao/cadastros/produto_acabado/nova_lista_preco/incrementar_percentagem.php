<?
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/nova_lista_preco/lista_preco.php', '../../../../../');
?>
<html>
<title>.:: Alterar Nova Margem de Lucro ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
//Essa atualização implica na tela de baixo desse Pop-UP
function atualizar() {
    if(document.form.txt_incrementar_promo_a.value != '') {//Incremento de Promoção A atual ...
        if(!texto('form', 'txt_incrementar_promo_a', '1', '1234567890,.', 'INCREMENTO PARA PROMOÇÃO A', '2')) {
            return false
        }
    }
    if(document.form.txt_incrementar_promo_b.value != '') {//Incremento de Promoção B atual ...
        if(!texto('form', 'txt_incrementar_promo_b', '1', '1234567890,.', 'INCREMENTO PARA PROMOÇÃO B', '2')) {
            return false
        }
    }
    if(document.form.txt_percentagem_sobre_preco_b.value != '') {//Preço Promo A Novo Assumir ...
        if(!texto('form', 'txt_percentagem_sobre_preco_b', '1', '1234567890,.', '% SOBRE O PREÇO B', '1')) {
            return false
        }
    }
    //Se estiverem vazios os incrementos, então eu forço o Usuário a preencher algum Incremento ...
    if(document.form.txt_incrementar_promo_a.value == '' && document.form.txt_incrementar_promo_b.value == '' && document.form.txt_percentagem_sobre_preco_b.value == '') {
        alert('DIGITE ALGUM INCREMENTO !')
        document.form.txt_incrementar_promo_a.focus()
        return false
    }
    var elementos 			= parent.document.form.elements
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['id_produto_acabado[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['id_produto_acabado[]'].length)
    }
    var incrementar_promo_a 		= eval(strtofloat(document.form.txt_incrementar_promo_a.value))
    var incrementar_promo_b 		= eval(strtofloat(document.form.txt_incrementar_promo_b.value))
    var percentagem_sobre_preco_b 	= eval(strtofloat(document.form.txt_percentagem_sobre_preco_b.value))
    for(var i = 0; i < linhas; i++) {
        if(typeof(incrementar_promo_a) != 'undefined') {//Se preenchido ...
            parent.document.getElementById('txt_preco_promocional_novo'+i).value = eval(strtofloat(parent.document.getElementById('txt_preco_promocional_atual'+i).value)) * (1 + incrementar_promo_a / 100)
            parent.document.getElementById('txt_preco_promocional_novo'+i).value = arred(parent.document.getElementById('txt_preco_promocional_novo'+i).value, 2, 1)
        }
        if(typeof(incrementar_promo_b) != 'undefined') {//Se preenchido ...
            parent.document.getElementById('txt_preco_promocional_novo_b'+i).value = eval(strtofloat(parent.document.getElementById('txt_preco_promocional_atual_b'+i).value)) * (1 + incrementar_promo_b / 100)
            parent.document.getElementById('txt_preco_promocional_novo_b'+i).value = arred(parent.document.getElementById('txt_preco_promocional_novo_b'+i).value, 2, 1)
        }
        if(typeof(percentagem_sobre_preco_b) != 'undefined') {//Se preenchido ...
            parent.document.getElementById('txt_preco_promocional_novo'+i).value = eval(strtofloat(parent.document.getElementById('txt_preco_promocional_novo_b'+i).value)) * (1 + percentagem_sobre_preco_b / 100)
            parent.document.getElementById('txt_preco_promocional_novo'+i).value = arred(parent.document.getElementById('txt_preco_promocional_novo'+i).value, 2, 1)
        }
        //Funções da própria Tela ...
        parent.calcular_preco_promocional_a_dif(i)
        parent.calcular_preco_promocional_b_dif(i)
        parent.calcular_preco_liq_fat_20_desc_dif(i)
    }
    alert('INCREMENTO DE % SOBRE PROMOÇÃO(ÕES) REALIZADO(S) C/ SUCESSO !!!')
    parent.html5Lightbox.finish()
}
</Script>
</head>
<body onload='document.form.txt_incrementar_promo_a.focus()'>
<form name='form'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incrementar %
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Incrementar Promoção A atual em %:</b>
        </td>
        <td>
            <input type='text' name='txt_incrementar_promo_a' title='Digite o Incremento Promocional A atual em %' size='15' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Incrementar Promoção B atual em %:</b>
        </td>
        <td>
            <input type='text' name='txt_incrementar_promo_b' title='Digite o Incremento Promocional B atual em %' size='15' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Preço Promo A Novo Assumir </b>
        </td>
        <td>
            <input type='text' name='txt_percentagem_sobre_preco_b' title='Digite a % sobre o Preço B' size='15' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'><b> % sobre Promo B:</b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_incrementar_promo_a.focus()" style='color:#ff9900' class='botao'>
            <input type='button' name='cmd_atualizar' value='Atualizar' title='Atualizar' onclick='atualizar()' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>