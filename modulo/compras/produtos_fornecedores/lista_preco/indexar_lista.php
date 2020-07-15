<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/produtos_fornecedores/lista_preco/lista_precos.php', '../../../../');
?>
<html>
<head>
<title>.:: Indexar Lista de Preço(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function calcular() {
    if(!confirm('DESEJA ATUALIZAR A LISTA DE PREÇO ?')) {
        return false
    }else {
        if(document.form.txt_reajuste_nacional.value == '' && document.form.txt_reajuste_exportacao.value == '') {
            alert('PREENCHA PELO MENOS UM CAMPO !')
            document.form.txt_reajuste_nacional.focus()
            return false
        }else {
            var elementos   = opener.document.form.elements
            var linhas      = (typeof(elementos['txt_preco_faturado[]'][0]) == 'undefined') ? 1 : (elementos['txt_preco_faturado[]'].length)
            if(document.form.txt_reajuste_nacional.value != '') {
                //Chama a função de acordo com a qtde dos PIs ...
                for(var j = 0; j < linhas; j++) {
                    if(opener.document.getElementById('txt_preco_faturado'+j).value != '') {
                        var valor_aumento = strtofloat(opener.document.getElementById('txt_preco_faturado'+j).value) * strtofloat(document.form.txt_reajuste_nacional.value) / 100
                        opener.document.getElementById('txt_preco_faturado'+j).value = eval(strtofloat(opener.document.getElementById('txt_preco_faturado'+j).value)) + valor_aumento
                        opener.document.getElementById('txt_preco_faturado'+j).value = arred(opener.document.getElementById('txt_preco_faturado'+j).value, 2, 1)
                    }
                }
            }
            if(document.form.txt_reajuste_exportacao.value != '') {
                //Chama a função de acordo com a qtde dos PIs ...
                for(var j = 0; j < linhas; j++) {
                    if(opener.document.getElementById('txt_preco_fat_exp'+j).value != '') {
                        var valor_aumento = strtofloat(opener.document.getElementById('txt_preco_fat_exp'+j).value) * strtofloat(document.form.txt_reajuste_exportacao.value) / 100
                        opener.document.getElementById('txt_preco_fat_exp'+j).value = eval(strtofloat(opener.document.getElementById('txt_preco_fat_exp'+j).value)) + valor_aumento
                        opener.document.getElementById('txt_preco_fat_exp'+j).value = arred(opener.document.getElementById('txt_preco_fat_exp'+j).value, 2, 1)
                    }
                }
            }
        }
    }
    opener.precisa_salvar_lista()
//Aqui chamará a funcao que ira calcular a lista
    opener.document.form.cmd_recalcular.onclick()
/*****************************Mensagem de Retorno*****************************/
//Controle p/ compor a Mensagem de Retorno ...
    var mensagem = 'Indexado '
//Se estiver preenchido o Reajuste de Nacional e o Reajuste de Exportação ...
    if(document.form.txt_reajuste_nacional.value != '' && document.form.txt_reajuste_exportacao.value != '') {
        mensagem+= arred(document.form.txt_reajuste_nacional.value, 2, 1)+' % Nacional + '+arred(document.form.txt_reajuste_exportacao.value, 2, 1)+' % Estrangeiro'
//Se estiver preenchido o Reajuste de Nacional ...
    }else if(document.form.txt_reajuste_nacional.value != '' && document.form.txt_reajuste_exportacao.value == '') {
        mensagem+= arred(document.form.txt_reajuste_nacional.value, 2, 1)+' % Nacional '
//Se estiver preenchido o Reajuste de Exportação ...
    }else if(document.form.txt_reajuste_nacional.value == '' && document.form.txt_reajuste_exportacao.value != '') {
        mensagem+= arred(document.form.txt_reajuste_exportacao.value, 2, 1)+' % Estrangeiro'
    }
    mensagem+= '<br>Salve p/ gravar os Dados !!!'
/*****************************************************************************/
//E atualizará a Layer Informando que houve uma Indexação na Lista de Preços
    opener.document.getElementById('texto_indexar_lista').innerHTML = mensagem
//Trava o Botão de Indexação da Tela de Baixo p/ o usuário não tentar Indexar novamente ...
    opener.document.form.cmd_indexar.disabled   = true
    opener.document.form.cmd_indexar.className  = 'textdisabled'
    window.close()
}
</Script>
</head>
<body onload='document.form.txt_reajuste_nacional.focus()'>
<form name='form'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align="center">
        <td colspan='2'>
            Indexar Lista de Preço(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='30%'>
            Reaj. % Nacional
        </td>
        <td>
            <input type='text' name='txt_reajuste_nacional' size='7' maxlength='12' onkeyup="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Reaj. % Exp/Imp
        </td>
        <td>
            <input type='text' name='txt_reajuste_exportacao' size='7' maxlength='12' onkeyup="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            <input type='button' name='cmd_atualizar_lista' value='Atualizar Lista' title='Atualizar Lista' onclick='calcular()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>
<pre>

* O sistema só vai sugerir o(s) Novo(s) Preço(s) de Compra na tela de Itens abaixo.
</pre>