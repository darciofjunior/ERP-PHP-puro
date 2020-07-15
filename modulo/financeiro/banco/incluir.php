<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>BANCO INCLUÍDO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>BANCO JÁ EXISTE.</font>";

if(!empty($_POST['txt_banco'])) {
    //Verifico se o Banco que está sendo cadastrado já existe no BD ...
    $sql = "SELECT id_banco 
            FROM `bancos` 
            WHERE `banco` = '$_POST[txt_banco]' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Não existe, então o Banco será cadastrado ...
        $sql = "INSERT INTO `bancos` (`id_banco`, `banco`, `pagweb`) VALUES (NULL, '$_POST[txt_banco]', '$_POST[txt_pagina_web]')";
        bancos::sql($sql);
        $valor = 1;
    }else {
        $valor = 2;
    }
}
?>
<html>
<head>
<title>.:: Incluir Banco(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Banco
    if(!texto('form','txt_banco','2',"-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,.Üüáé§íóúÁÉÍÀ'àºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ","BANCO","2")) {
        return false
    }
//Página Web
    if(!texto('form','txt_pagina_web','5',"-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,.Üüáé§íóúÁÉÍÀ'àºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ",'PÁGINA WEB','1')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_banco.focus()'>
<form name='form' method='post' action='' onsubmit="return validar()">
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='2'>
            Incluir Banco
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Banco:</b>
        </td>
        <td>
            <input type="text" name="txt_banco" title="Digite o Banco" size='35' maxlength='30' class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>P&aacute;gina Web:</b>
        </td>
        <td>
            <input type='text' name='txt_pagina_web' title='Digite a Página Web' size='85' maxlength='80' class="caixadetexto">
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick='redefinir("document.form", "LIMPAR");document.form.txt_banco.focus()' style="color:#ff9900;" class="botao">
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>