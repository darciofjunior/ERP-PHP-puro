<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>CARGO INCLUIDO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>CARGO JÁ EXISTENTE.</font>";

if(!empty($_POST['txt_cargo'])) {
//Aqui eu verifico no Sistema se já existe cadastrado o cargo digitado pelo usuário ...
    $sql = "SELECT id_cargo 
            FROM `cargos` 
            WHERE `cargo` = '$_POST[txt_cargo]' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if ($linhas == 0) {//Não existe ...
        $sql = "INSERT INTO `cargos` (`id_cargo`, `cargo`) VALUES (NULL, '$_POST[txt_cargo]') ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Já existe ...
        $valor = 2;
    }
}
?>
<html>
<head>
<title>.:: Incluir Cargo(s) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Cargo ...
    if(!texto('form', 'txt_cargo', '3', '0123456789ãõÃÕáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛabcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZçÇ _-/', 'CARGO', '2')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_cargo.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='60%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2" >
            Incluir Cargo(s)
        </td>
    </tr>
    <tr class="linhanormal">
        <td width='10%'>
            <b>Cargo:</b>
        </td>
        <td width='90%'>
            <input type='text' name='txt_cargo' title='Digite o Cargo' size="20" maxlength="25" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="redefinir('document.form', 'LIMPAR');document.form.txt_cargo.focus()" class='botao'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>