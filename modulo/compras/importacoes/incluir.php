<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
session_start('funcionarios');
segurancas::geral($PHP_SELF, '../../../');
$mensagem[1] = "<font class='confirmacao'>IMPORTAÇÃO INCLUIDA COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>IMPORTAÇÃO JÁ EXISTENTE.</font>";

if(!empty($_POST['txt_nome'])) {
    $sql = "SELECT id_importacao 
            FROM `importacoes` 
            WHERE `nome` = '$_POST[txt_nome]' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $txt_observacao = strtolower($txt_observacao);
    if(count($campos) == 0) {
        $sql = "INSERT INTO `importacoes` (`id_importacao`, `nome`, `observacao`) VALUES (NULL, '$_POST[txt_nome]', '$txt_observacao')";
        bancos::sql($sql);
        $valor = 1;
    }else {
        $valor = 2;
    }
}
?>
<html>
<head>
<title>.:: Incluir Importa&ccedil;&atilde;o ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Nome
    if(!texto('form', 'txt_nome', '2', "-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,.Üüáé§íóúÁÉÍÀ'àºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ", 'NOME', '2')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_nome.focus()'>
<form name='form' method='post' action='' onsubmit="return validar()">
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='2'>
            Incluir Importa&ccedil;&atilde;o
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Nome:</b>
        </td>
        <td>
            <input type="text" name="txt_nome" title="Digite a Importação" maxlength='20' size='22' class='caixadetexto'>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Observa&ccedil;&atilde;o:
        </td>
        <td>
            <textarea name='txt_observacao' title="Digite a Observação" maxlength='80' cols='80' rows='1' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' style="color:#ff9900;" onclick="redefinir('document.form', 'LIMPAR');document.form.txt_nome.focus()" class="botao">
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>