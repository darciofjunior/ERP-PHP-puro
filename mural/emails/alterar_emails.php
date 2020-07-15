<?
require('../../lib/segurancas.php');
session_start('funcionarios');

$mensagem[1] = "<font class='confirmacao'>CONTA DE E-MAIL ALTERADA COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>CONTA DE E-MAIL UTILIZADA POR OUTRO FUNCION¡RIO.</font>";

$id_funcionario_current = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_funcionario_current'] : $_GET['id_funcionario_current'];

if(!empty($_POST['txt_email_externo'])) {
//Tratamento com a condiÁ„o ...
    if(!empty($_POST['txt_email_externo'])) {
        $email_externo = $_POST['txt_email_externo'].'@grupoalbafer.com.br';
        $condicao = " and `email_externo` = '$email_externo' ";
//Como eu n„o tenho nenhum dos e-mails, ent„o simplesmente excluo as contas p/ o mesmo ...
    }else {
        $limpar_emails = 1;//Vari·vel de Controle ...
    }

    if($limpar_emails != 1) {
//Aqui eu verifico se esse e-mail est· sendo usado por outro funcion·rio ...
        $sql = "SELECT id_funcionario 
                FROM `funcionarios` 
                WHERE `id_funcionario` <> '".$_POST['id_funcionario_current']."' $condicao LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 0) {//N„o est· sendo usado ...
            $sql = "UPDATE `funcionarios` SET `email_externo` = '$email_externo' WHERE `id_funcionario` = '".$_POST['id_funcionario_current']."' LIMIT 1 ";
            bancos::sql($sql);
            $valor = 1;
        }else {//J· est· sendo usado ...
            $valor = 2;
        }
    }else {//Aqui eu simplesmente limpo os e-mails, afinal n„o preenchido nenhum campo ...
        $sql = "UPDATE `funcionarios` SET `email_externo` = '' where `id_funcionario` = '$_POST[id_funcionario_current]' LIMIT 1 ";
        bancos::sql($sql);
/*Se o usu·rio n„o possui + nenhuma conta de E-mail, ent„o n„o faz sentido ele ainda continuar 
atrelado a algum grupo, e sendo assim desatrelo ele de todos os Grupo(s) de E-mail ...*/
        $sql = "DELETE FROM `grupos_emails_vs_funcionarios` WHERE `id_funcionario` = '$_POST[id_funcionario_current]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }
}

//Busco dados do E-mail Externo do Funcion·rio ...
$sql = "SELECT nome, email_externo 
        FROM `funcionarios` 
        WHERE `id_funcionario` = '$id_funcionario_current' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar E-mails ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//E-mail Externo
    if(document.form.txt_email_externo.value != '') {
        if(!texto('form', 'txt_email_externo', '1', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZÁ«„ı√’·ÈÌÛ˙¡…Õ”⁄‚ÍÓÙ˚¬ Œ‘€_.- ', 'E-MAIL EXTERNO', '2')) {
            return false
        }
    }
//Aqui È para n„o atualizar os frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que sÛ atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) window.opener.document.form.submit()
}
</Script>
</head>
<body onload='document.form.txt_email_externo.focus()' onunload="atualizar_abaixo()">
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Coloquei esse nome de $id_funcionario_current, p/ n„o dar conflito com a vari·vel "id_funcion·rio" da sess„o-->
<input type='hidden' name='id_funcionario_current' value="<?=$id_funcionario_current;?>">
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td>
            Alterar E-mail(s) do Funcion·rio 
            <font face='Verdana, Arial, Helvetica, sans-serif' color='yellow'>
                <?=$campos[0]['nome'];?>
            </font>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Email Externo:
        </td>
    </tr>
    <tr class="linhanormal" >
        <td>
            <input type="text" name="txt_email_externo" value="<?=strtok($campos[0]['email_externo'], '@');?>" size="35" maxlength="50" title="Digite o Email Externo" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td>
            <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_email_externo.focus()" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick='fechar(window)' class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color="red">ObservaÁ„o:</font></b>
<pre>
* N„o È necess·rio digitar @grupoalbafer.com.br, porque o prÛprio Sistema coloca automaticamente.
</pre>