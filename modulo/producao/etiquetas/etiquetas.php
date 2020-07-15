<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');
?>
<html>
<head>
<title>.:: Etiqueta(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/jquery.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function consultar_dados() {
//N.° da OP ...
    if(!texto('form', 'txt_consultar', '1', '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_. ', 'CAMPO CONSULTAR', '2')) {
        return false
    }
    if(document.form.opt_opcao[0].checked == true) {
        var opt_opcao = 1
    }else if(document.form.opt_opcao[1].checked == true) {
        var opt_opcao = 2
        ajax('consultar_pas_substitutivos.php?id_op='+document.form.txt_consultar.value, 'cmb_pa_substitutivo')
        document.getElementById('lbl_pa_substitutivo').style.visibility = 'visible'
        document.getElementById('cmb_pa_substitutivo').style.visibility = 'visible'
    }else if(document.form.opt_opcao[2].checked == true) {
        var opt_opcao = 3
    }else if(document.form.opt_opcao[3].checked == true) {
        var opt_opcao = 4
    }else if(document.form.opt_opcao[4].checked == true) {
        var opt_opcao = 5
    }
    iframe_consultar_dados.location = 'consultar_dados.php?txt_consultar='+document.form.txt_consultar.value+'&opt_opcao='+opt_opcao
}

function validar() {
//Referência ...
    if(!texto('form', 'txt_referencia', '1', '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_/. ', 'REFERÊNCIA', '1')) {
        return false
    }
//Discriminação ...	
    if(!texto('form', 'txt_discriminacao', '1', '0123456789ãõáéíóúâêîôûçabcdefghijklmnopqrstuvwxyzÃÕÁÉÍÓÚÂÊÎÔÛÇABCDEFGHIJKLMNOPQRSTUVWXYZ-+_/°ºª%.():;* ', 'DISCRIMINAÇÃO', '1')) {
        return false
    }
//Só forço a preencher essa opção quando estiver selecionadas as Opções de N.º OP / OE / OC ...
    if(document.form.opt_opcao[1].checked == true || document.form.opt_opcao[2].checked == true || document.form.opt_opcao[3].checked == true) {
        if(!texto('form', 'txt_numero', '1', '0123456789', 'N.º DE OP / OE / OC / Lote', '2')) {
            return false
        }
    }
//Quantidade ...
    if(!texto('form', 'txt_quantidade', '1', '0123456789', 'QUANTIDADE DE ETIQUETAS', '1')) {
        return false
    }
//Tipo de Etiqueta ...
    if(document.form.cmb_tipo_etiqueta.value == '') {
        alert('SELECIONE UM TIPO DE ETIQUETA !')
        document.form.cmb_tipo_etiqueta.focus()
        return false
    }
    
    //Desabilito esse objetos para poder submeter os valores via Pop-UP e Imprimir ...
    document.form.txt_numero.disabled           = false
    document.form.txt_pcs_embalagem.disabled 	= false
//Verifico se no campo "txt_referencia" foi digitada parte de texto como sendo "MLH" ...
    if(document.form.txt_referencia.value.indexOf('MLH') != -1) {//Foi digitado ...
        document.form.action = 'perfil_etiqueta.php'
        nova_janela('perfil_etiqueta.php', 'pop_up', '', '', '', '', '500', '850', 'c', 'c', '', '', 's', 's', '', '', '')
    }else {//Não foi digitado ...
        /*Se o usuário selecionou este Tipo de Impressora, então a mesma só imprime de maneira alinhada 
        por dentro do Navegador "Firefox" ...*/
        if(document.form.cmb_impressora.value == 'EPSON L375') {
            if(navigator.userAgent.indexOf('Firefox') == -1) {//Significa que o Usuário não está dentro do Navegador Firefox ...
                alert('ESTAS ETIQUETAS SÓ PODEM SER IMPRESSAS POR DENTRO DO NAVEGADOR "FIREFOX" !')
                return false
            }
        }
        document.form.action = document.form.cmb_impressora.value+'/'+document.form.cmb_tipo_etiqueta.value
        nova_janela(document.form.cmb_tipo_etiqueta.value, 'pop_up', '', '', '', '', '500', '850', 'c', 'c', '', '', 's', 's', '', '', '')
    }
    document.form.submit()
}

function controlar_options() {
    document.form.hdd_produto_acabado.value = ''
    document.form.txt_referencia.value      = ''
    document.form.txt_discriminacao.value   = ''
    document.form.txt_qtde_op.value         = ''
    document.form.txt_numero.value          = ''
    document.form.txt_pcs_embalagem.value   = ''
    document.getElementById('lbl_pa_substitutivo').style.visibility = 'hidden'
    document.getElementById('cmb_pa_substitutivo').style.visibility = 'hidden'
    document.getElementById('cmb_pa_substitutivo').length = 0
    
    /*Se a opção selecionada for OE então desabilito o campo de Data para que na hora da impressão de 
    Etiqueta não haja vazamento de uma Etiqueta com a do lado sobrepondo conteúdo ...*/
    if(document.form.opt_opcao[2].checked == true) {//Impressão de Etiqueta para OE ...
        document.form.txt_data.value        = ''
        document.form.txt_data.disabled     = true
        document.form.txt_data.className    = 'textdisabled'
    }else {//Demais Opções, normal ...
        document.form.txt_data.value        = '<?=date('d/m/Y');?>'
        document.form.txt_data.disabled     = false
        document.form.txt_data.className    = 'caixadetexto'
    }
    /*Se o usuário selecionou essa opção "A4255 Caixa Master - Máx. 27 Etiq.", o sistema automaticamente sugere 
    essa opção de Etiqueta ...*/
    if(document.form.opt_opcao[4].checked) document.form.cmb_tipo_etiqueta.value = 'imprimir_A4255caixa_master.php'
    document.form.txt_referencia.disabled       = true
    document.form.txt_discriminacao.disabled    = true
    document.form.txt_numero.disabled           = true
    document.form.txt_discriminacao.className   = 'textdisabled'
    document.form.txt_referencia.className      = 'textdisabled'
    document.form.txt_numero.className          = 'textdisabled'
    //Habilito a Caixa e o Botão de Consultar ...
    document.form.txt_consultar.disabled        = false
    document.form.cmd_consultar.disabled        = false
    document.form.txt_consultar.className       = 'caixadetexto'
    document.form.cmd_consultar.className       = 'botao'
    document.form.txt_consultar.focus()
}

function controlar_qtde_etiquetas(qtde) {
    if(document.form.cmb_tipo_etiqueta.value == 'imprimir_26x15.php') {
        if(qtde.value >= 86) {
            alert('A QUANTIDADE MÁXIMA PARA IMPRESSÃO É DE 85 ETIQUETAS POR VEZ !')
            qtde.value = ''
        }
    }else if(document.form.cmb_tipo_etiqueta.value == 'imprimir_A4251.php') {
        if(qtde.value >= 66) {
            alert('A QUANTIDADE MÁXIMA PARA IMPRESSÃO É DE 65 ETIQUETAS POR VEZ !')
            qtde.value = ''
        }
    }else if(document.form.cmb_tipo_etiqueta.value == 'imprimir_A4255.php' || document.form.cmb_tipo_etiqueta.value == 'imprimir_A4255caixa_master.php') {
        if(qtde.value >= 28) {
            alert('A QUANTIDADE MÁXIMA PARA IMPRESSÃO É DE 27 ETIQUETAS POR VEZ !')
            qtde.value = ''
        }
    }else if(document.form.cmb_tipo_etiqueta.value == 'imprimir_6288.php') {
        if(qtde.value >= 5) {
            alert('A QUANTIDADE MÁXIMA PARA IMPRESSÃO É DE 4 ETIQUETAS POR VEZ !')
            qtde.value = ''
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' target='pop_up'>
<table width='85%' border='0' cellspacing='1' cellpadding='1' align='center'>
<input type='hidden' name='hdd_produto_acabado'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Imprimir Etiqueta(s)
            <br/>
            Impressora 
            <select name='cmb_impressora' title='Selecione a Impressora' class='combo'>
                <option value='HP LaserJet P1505n'>HP LaserJet P1505n</option>
                <option value='EPSON L375' selected>EPSON L375 (Mozilla Firefox somente nas Versões 48 e 49)</option>
            </select>
            &nbsp;-&nbsp;Tipo(s) de Etiqueta 
            <select name='cmb_tipo_etiqueta' title='Selecione o Tipo de Etiqueta' onchange='controlar_qtde_etiquetas(document.form.txt_quantidade)' class='combo'>
                <option value='imprimir_26x15.php' selected>26x15 - Máx. 85 Etiq.</option>
                <option value='imprimir_A4251.php'>A4251 - Máx. 65 Etiq.</option>
                <option value='imprimir_A4255.php'>A4255 - Máx. 27 Etiq.</option>
                <option value='imprimir_6288.php'>6288 - Máx. 4 Etiq.</option>
                <option value='imprimir_A4255caixa_master.php'>A4255 Caixa Master - Máx. 27 Etiq.</option>
            </select>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='6'>
            <input type='radio' name='opt_opcao' value='1' onclick='controlar_options()' id='label'>
            <label for='label'>
                Referência 
            </label>
            &nbsp;
            <input type='radio' name='opt_opcao' value='2' onclick='controlar_options()' id='label2' checked>
            <label for='label2'>
                OP
            </label>
            &nbsp;
            <input type='radio' name='opt_opcao' value='3' onclick='controlar_options()' id='label3'>
            <label for='label3'>
                OE
            </label>
            &nbsp;
            <input type='radio' name='opt_opcao' value='4' onclick='controlar_options()' id='label4'>
            <label for='label4'>
                OC
            </label>
            &nbsp;
            <input type='radio' name='opt_opcao' value='5' onclick='controlar_options()' id='label5'>
            <label for='label5'>
                Referência Caixa Master
            </label>
            &nbsp;
            <input type='text' name='txt_consultar' size='20' maxlength='18' class='caixadetexto'>
            &nbsp;
            <input type='button' name='cmd_consultar' value='Consultar' title='Consultar' onclick='return consultar_dados()' class='botao'>
            <label id='lbl_pa_substitutivo' style='visibility:hidden'>
                &nbsp;-&nbsp;PA(s) Substitutivo(s):
            </label> 
            <select name='cmb_pa_substitutivo' id='cmb_pa_substitutivo' title='Selecione o P.A. Substitutivo' style='visibility:hidden' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência
        </td>
        <td>
            <input type='text' name='txt_referencia' size='13' class='textdisabled' disabled>
        </td>
        <td>
            Discriminação
        </td>
        <td>
            <input type='text' name='txt_discriminacao' size='60' class='textdisabled' disabled>
        </td>
        <td>
            Qtde OP
            <input type='text' name='txt_qtde_op' size='10' class='textdisabled' disabled>
        </td>
        <td>
            N.º OP / OE / OC / Lote
            <input type='text' name='txt_numero' size='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Pçs. Embalagem
        </td>
        <td>
            <input type='text' name='txt_pcs_embalagem' maxlength='4' size='6' title='Digite a Qtde de Peças por Embalagem' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='caixadetexto'>
        </td>
        <td colspan='2'>
            <b>Qtde de Etiquetas</b>
            &nbsp;
            <input type='text' name='txt_quantidade' maxlength='2' size='6' title='Digite a Quantidade' onkeyup="verifica(this, 'aceita', 'numeros', '', event);controlar_qtde_etiquetas(this)" class='caixadetexto'>
            &nbsp;| Pular&nbsp;
            <input type='text' name='txt_pular' maxlength='2' size='6' title='Digite a Quantidade que deseja Pular' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value >= 85) this.value = ''" class='caixadetexto'> Etiquetas
        </td>
        <td colspan='2'>
            Data&nbsp;
            <input type='text' name='txt_data' value='<?=date('d/m/Y');?>' maxlength='10' size='12' title='Digite a Data' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            &nbsp;-&nbsp;
            Importação
            <input type='text' name='txt_importacao' maxlength='18' size='20' title='Digite a Importação' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="document.form.reset();document.form.txt_consultar.focus()" style='color:#ff9900' class='botao'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='return validar()' class='botao'>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='6'>
            <div id='div_detalhes' style='background-color: #FFFFFF; position:relative; left:0px; top:3px; height:42px; width:800px; border-width:0px;border-style:solid;border-color:#000000; color:darkblue; font:bold 16px verdana'></div>
        </td>
    </tr>
</table>
</form>
<!--Aqui busco os dados do Produto de acordo com a opção Filtrada pelo usuário ...-->
<iframe name='iframe_consultar_dados' marginwidth='0' marginheight='0' frameborder='0' height='0' width='0'></iframe>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>
    <pre>
    * O padrão de configuração p/ Impressão dessas Etiquetas na Impressora é do tipo Papel Ofício "Legal".

    * À partir da Versão 50 já desalinha os PDF´s novamente.

    <b>* NUNCA deixar marcada a opção de atualização automática de Versão no computador em que for rodar essas Etiquetas.</b>
    </pre>
</pre>