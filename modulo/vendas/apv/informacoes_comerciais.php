<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/vendas/apv/apv.php', '../../../');
?>
<html>
<title>.:: Dados do Solicitante ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../css/layout.css'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Razão Social
    if(!texto('form','txt_razao_social','3',"-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,'.Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ",'RAZÃO SOCIAL','1')) {
        return false
    }
//Contato
    if(!texto('form', 'txt_contato', '3', "-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,'.Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ", 'CONTATO', '2')) {
        return false
    }
//Cep
    if(!texto('form', 'txt_cep', '9', '-1234567890', 'CEP', '2')) {
        return false
    }
//Número / Complemento
    if(!texto('form', 'txt_num_complemento', '1', "-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,'.Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ", 'NÚMERO / COMPLEMENTO', '2')) {
        return false
    }		
//E-mail
    if (!new_email('form', 'txt_email')) {
        return false
    }		
    //Desabilita todos os objetos para poder gravar no Banco de Dados ...
    for(var i = 0; i < document.form.elements.length; i++) {
        document.form.elements[i].disabled = false
    }
    nova_janela('informacoes_clientes.php', 'INFORMACOES', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
}

//Atualiza o frame de baixo para controle do CEP
function buscar_cep() {
    if(document.form.txt_cep.value == '') {//Verifico se o CEP é válido ...
        document.form.txt_endereco.value = ''
        document.form.txt_bairro.value  = ''
        document.form.txt_cidade.value  = ''
        document.form.cmb_uf.value      = ''
    }else {
        if(document.form.txt_cep.value.length < 9) {//Verifico se o CEP é válido ...
            alert('CEP INVÁLIDO !')
            document.form.txt_cep.focus()
            document.form.txt_cep.select()
            return false
        }else {
            cep.location = '../../classes/cep/buscar_cep.php?txt_cep='+document.form.txt_cep.value
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_razao_social.focus()'>
<form name='form' method='post' action='impressao_informacoes_comerciais.php' target='INFORMACOES' onsubmit='return validar()'>
<input type='hidden' name='hdd_cliente_solicitado' value='<?=$_GET['id_cliente'];?>'>
<input type='hidden' name='hdd_cliente_solicitante'>
<table width='90%' border="0" cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Dados do Solicitante
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='50%'>
            <b>Raz&atilde;o Social:</b>
        </td>
        <td width='50%'>
            <b>Contato:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name="txt_razao_social" title="Digite a Raz&atilde;o Social" size='50' maxlength='80' class='caixadetexto'>
            &nbsp;&nbsp;<input type="button" name="cmd_consultar_cliente" value="Consultar Cliente" title="Consultar Cliente" onclick="nova_janela('consultar_cliente.php', 'INFORMACOES', '', '', '', '', 350, 950, 'c', 'c', '', '', 's', 's', '', '', '')" style="color:red" class='botao'>
        </td>
        <td>
            <input type='text' name="txt_contato" title="Digite o Contato" size='35' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td rowspan='2'>
            <!-- O campo criado na Base de dados se chama é cliente, só o rotulo que é oposto ...-->
            <input type='checkbox' name='chkt_cliente' id='chkt_cliente' value='N' class='checkbox'>
            <font color='red'>
                <label for='chkt_cliente'>
                    <b>N&Atilde;O &Eacute; CLIENTE</b>
                </label>
            </font>
        </td>
        <td>
            Ramo de Atividade:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_ramo_atividade' title='Digite o Ramo de Atividade' size='35' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>CEP:</b>
        </td>
        <td>
            <b>Email:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name="txt_cep" size="20" maxlength="9" title="Digite o Cep" onkeyup="verifica(this, 'cep', '', '', event)" onblur="buscar_cep()" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name="txt_email" size="50" maxlength="70" title="Digite o Email" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Endere&ccedil;o:</b>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <b>N.&#176; / Complemento</b>
        </td>
        <td>
            <b>Bairro:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_endereco' title='Endere&ccedil;o' size='45' maxlength='50' class='textdisabled'>
            &nbsp;
            <input type='text' name='txt_num_complemento' title='Digite o N&uacute;mero, Complemento, ...' size='10' maxlength='50' class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_bairro' title='Bairro' size='35' class='textdisabled'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Cidade:</b>
        </td>
        <td>
            <b>Estado:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_cidade' title='Cidade' size='35' onfocus='document.form.txt_num_complemento.focus()' class='textdisabled'>
        </td>
        <td>
            <input type='text' name='txt_estado' title='Estado' maxlength='2' size='3' onfocus='document.form.txt_num_complemento.focus()' class='textdisabled'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='submit' name='cmd_imprimir' value='Imprimir' title='Imprimir' class='botao'>
        </td>
    </tr>
    <!--Aqui busco o Endereço através do Cep do Cliente ...-->
    <iframe name='cep' id='cep' marginwidth='0' marginheight='0' frameborder='0' height='0' width='0'></iframe>
</table>
</form>
</body>
</html>