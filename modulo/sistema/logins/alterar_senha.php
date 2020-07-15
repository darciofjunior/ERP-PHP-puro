<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
session_start('funcionarios');

$mensagem[1] = "<font class='confirmacao'>SENHA ALTERADA COM SUCESSO.</font>";
$mensagem[2] = "<font class='atencao'>SENHA ANTIGA INCORRETA.</font>";
$mensagem[3] = "<font class='atencao'>A NOVA SENHA NÃO CONFERE COM O CONFIRMAR NOVA SENHA.</font>";

if(!empty($_POST['txt_antiga'])) {
    $sql = "SELECT login, senha 
            FROM `logins` 
            WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
    $campos     = bancos::sql($sql);
    $data_sys 	= date('Y-m-d H:i:s');
    $data_atual	= date('Y-m-d');//Não trabalho com hora por isto preciso pegar novamente a data ...
    if($_POST['txt_senha1'] == $_POST['txt_senha2']) {
        if(segurancas::descriptografia($campos[0]['login'], $campos[0]['senha']) == $_POST['txt_antiga']) {
            $criptografia = segurancas::criptografia($campos[0]['login'], $_POST['txt_senha1']);
            $sql = "UPDATE `logins` SET `login` = '".$campos[0]['login']."', `senha` = '$criptografia', `dica_senha` = '$_POST[txt_dica_senha]', `data_sys` = '$data_sys' WHERE `id_login` = '$_SESSION[id_login]' AND `login` = '".$campos[0]['login']."' LIMIT 1 ";
            bancos::sql($sql);
            $valor = 1;
        }else {
            $valor = 2;
        }
    }else {
        $valor = 3;
    }
}
?>
<html>
<head>
<title>.:: Alterar Senha ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(!texto('form', 'txt_antiga', '3', 'abcdefghijlkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ<>.,:;/?}]{[!12344567890_-', 'SENHA ANTIGA', '1')) {
        return false
    }
    if(!texto('form', 'txt_senha1', '3', 'abcdefghijlkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ<>.,:;/?}]{[!12344567890_-', 'NOVA SENHA', '1')) {
        return false
    }
    if(!texto('form', 'txt_senha2', '3', 'abcdefghijlkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ<>.,:;/?}]{[!12344567890_-', 'NOVA SENHA NOVAMENTE', '1')) {
        return false
    }
//Verifica se está em branco a Dica de Senha
    if(document.form.txt_dica_senha.value == '') {
        alert('DIGITE A DICA DA SENHA !')
        document.form.txt_dica_senha.focus()
        return false
    }
//Se não conferir a Nova Senha então tem que barrar
    if(document.form.txt_senha1.value != document.form.txt_senha2.value) {
        alert('A NOVA SENHA NÃO CONFERE COM O CONFIRMAR A NOVA SENHA !')
        document.form.txt_senha2.focus()
        document.form.txt_senha2.select()
        return false
    }
}
</Script>
</head>
<body onload="document.form.txt_antiga.focus()">
<form name='form' method='post' action='' onsubmit="return validar()">
<table width='60%' border="0" cellspacing="1" cellpadding="1" align="center">
    <tr class='atencao' align='center'>
        <td colspan='4'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='4'>
            Alterar Senha
        </td>
    </tr>
    <?
        $sql = "SELECT f.nome, l.login, l.data_sys 
                FROM `logins` l 
                INNER JOIN `funcionarios` f ON f.id_funcionario = l.id_funcionario 
                WHERE l.id_login = '$_SESSION[id_login]' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {
            $nome               = $campos[0]['nome'];
            $login              = $campos[0]['login'];
            $ultima_data	= $campos[0]['data_sys'];
            if($ultima_data != '0000-00-00 00:00:00') $ultima_data = data::datetodata(substr($ultima_data, 0, 10),'/').' '.substr($ultima_data, 11, 8);
        }
    ?>
    <tr class="linhanormal">
        <td>
            <b>Nome:</b>
        </td>
        <td colspan='3'>
            <?=$nome;?>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Usuário:</b>
        </td>
        <td colspan='3'>
            <?=$login;?>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Última Atualização:</b>
        </td>
        <td colspan='3'>
            <?=$ultima_data;?>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Senha Antiga:</b>
        </td>
        <td colspan='3'>
            <input type='password' name="txt_antiga" title="Digite a Senha Antiga" size="20" maxlength="50" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Nova Senha:</b>
        </td>
        <td>
            <input type='password' name="txt_senha1" title="Digite a Nova Senha" size="20" maxlength="15" class="caixadetexto">
        </td>
        <td>
            <b>Confirmar:</b>
        </td>
        <td>
            <input type='password' name="txt_senha2" title="Confirme a Nova Senha" size="20" maxlength="15" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Dica de Senha:</b>
        </td>
        <td colspan='3'>
            <input type="text" name="txt_dica_senha" title="Digite a Dica de Senha" size="20" maxlength="15" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='4'>
            <input type="button" name="cmd_redefinir" value="Limpar" title="Limpar" style="color:#ff9900;"  onclick="redefinir('document.form', 'LIMPAR');document.form.txt_antiga.focus()" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>