<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/sistema/logins/alterar.php', '../../../');
$mensagem[1] = '<font class="confirmacao">DADO(S) DE LOGIN ALTERADO(S) COM SUCESSO.</font>';

//Procedimento normal de quando se carrega a Tela ...
$id_login_loop = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_login_loop'] : $_GET['id_login_loop'];

if (!empty($_POST['id_login_loop'])) {
    /***********************************************************************/
    //Controle só para a parte de Senha, se o usuário preencheu uma Senha ...
    if(!empty($_POST['txt_senha1']) && !empty($_POST['txt_senha2'])) {
        if($_POST['txt_senha1'] == $_POST['txt_senha2']) {
            $criptografia = segurancas::criptografia($_POST['hdd_login'], $_POST['txt_senha1']);
            $sql = "UPDATE `logins` SET `senha` = '$criptografia' WHERE `id_login` = '$_POST[id_login_loop]' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
    /***********************************************************************/
    //Atualização dos demais campos ...
    $sql = "UPDATE `logins` SET `tipo_login` = '$_POST[cmb_tipo_login]', `ativo` = '$_POST[cmb_tipo_acesso]' WHERE `id_login` = '$_POST[id_login_loop]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}
?>
<html>
<head>
<title>.:: Alterar Dados(s) de Login ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Login ...
    if(!combo('form', 'cmb_tipo_login', '', 'SELECIONE O TIPO DE LOGIN !')) {
        return false
    }
//Tipo de Acesso ...
    if(!combo('form', 'cmb_tipo_acesso', '', 'SELECIONE O TIPO DE ACESSO !')) {
        return false
    }
/************************************************************************************/
//Se uma das Senhas estiverem preenchidas ...
    if(document.form.txt_senha1.value != '' || document.form.txt_senha2.value != '') {
//Senha 1 ...
        if(!texto('form', 'txt_senha1', '3', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ<>.,:;/?}]{[!12344567890_-', 'NOVA SENHA', '1')) {
            return false
        }
//Senha 2 ...
        if(!texto('form', 'txt_senha2', '3', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ<>.,:;/?}]{[!12344567890_-', 'CONFIRMAR NOVA SENHA', '2')) {
            return false
        }
//Comparação com as Senhas ...
        if(document.form.txt_senha1.value != document.form.txt_senha2.value) {
            alert('A NOVA SENHA NÃO CONFERE COM O CONFIRMAR A NOVA SENHA !')
            document.form.txt_senha2.focus()
            document.form.txt_senha2.select()
            return false
        }
    }
/************************************************************************************/
//Aqui é para não atualizar a Tela abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
    //Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.location = parent.location.href
}
</Script>
</head>
<body onload='document.form.cmb_tipo_login.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--***********************Controle de Tela***********************-->
<input type='hidden' name='id_login_loop' value='<?=$id_login_loop;?>'>
<input type='hidden' name='nao_atualizar'>
<!--**************************************************************-->
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Alterar Dados(s) de Login
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Login:
        </td>
        <td>
        <?
            $sql = "SELECT * 
                    FROM `logins` 
                    WHERE `id_login` = '$id_login_loop' LIMIT 1 ";
            $campos = bancos::sql($sql);
            echo $campos[0]['login'];
        ?>
            <!--Guardo esse valor no Hidden porque o mesmo será utilizado depois que submeter essa Tela, de modo 
            a evitar um SQL desnecessário ...-->
            <input type='hidden' name='hdd_login' value='<?=$campos[0]['login'];?>'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Login:</b>
        </td>
        <td>
            <select name='cmb_tipo_login' title='Selecione o Tipo de Login' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    if($campos[0]['tipo_login'] == 'CLIENTE') {
                        $selected_cliente = 'selected';
                    }else if($campos[0]['tipo_login'] == 'FORNECEDOR') {
                        $selected_fornecedor = 'selected';
                    }else if($campos[0]['tipo_login'] == 'FUNCIONARIO') {
                        $selected_funcionario = 'selected';
                    }else if($campos[0]['tipo_login'] == 'REPRESENTANTE') {
                        $selected_representante = 'selected';
                    }
                ?>
                <option value='CLIENTE' <?=$selected_cliente;?>>CLIENTE</option>
                <option value='FORNECEDOR' <?=$selected_fornecedor;?>>FORNECEDOR</option>
                <option value='FUNCIONARIO' <?=$selected_funcionario;?>>FUNCIONARIO</option>
                <option value='REPRESENTANTE' <?=$selected_representante;?>>REPRESENTANTE</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Acesso:</b>
        </td>
        <td>
            <select name='cmb_tipo_acesso' title='Selecione o Tipo de Acesso' class='combo'>
                <?
                    if($campos[0]['ativo'] == 0) {
                        $selected0 = 'selected';
                    }else if($campos[0]['ativo'] == 1) {
                        $selected1 = 'selected';
                    }else if($campos[0]['ativo'] == 2) {
                        $selected2 = 'selected';
                    }
                ?>
                <option value='0' <?=$selected0;?>>SEM ACESSO</option>
                <option value='1' <?=$selected1;?>>ACESSO INTERNO</option>
                <option value='2' <?=$selected2;?>>ACESSO INTERNO E EXTERNO</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Nova senha:
        </td>
        <td> 
            <input type='password' name='txt_senha1' title='Digite a Nova Senha' size='20' maxlength='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Confirmar Nova Senha:
        </td>
        <td> 
            <input type='password' name='txt_senha2' title='Digite o Confirmar Nova Senha' size='20' maxlength='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.cmb_tipo_login.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>