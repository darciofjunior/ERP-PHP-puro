<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = '<font class="confirmacao">TIPO DA MOEDA INCLUIDO COM SUCESSO. </font>';
$mensagem[2] = '<font class="confirmacao">TIPO DE MOEDA JÁ EXISTENTE. </font>';

if(!empty($_POST['txt_moeda'])) {
//Verifico se esse Tipo de Moeda já existe cadastrado na Base de Dados ...
    $sql = "SELECT moeda 
            FROM `tipos_moedas` 
            WHERE `moeda` = '$_POST[txt_moeda]' 
            AND ativo = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Não existe ...
        $sql = "INSERT INTO `tipos_moedas` (`id_tipo_moeda`, `moeda`, `simbolo`, `origem`, `descricao`) VALUES (NULL, '$_POST[txt_moeda]', '$_POST[txt_simbolo]', '$_POST[txt_origem]', '$_POST[txt_descricao]') ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Já existe ...
        $valor = 2;
    }
}
?>
<html>
<head>
<title>.:: Incluir Tipo de Moeda ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Moeda
    if(!texto('form', 'txt_moeda', '2', "qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPÇLKJHGFDSAZXCVBNMáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛàÀãÃÕ1234567890,..', !@#$%¨&*()-_=§¹²³£¢/¬", 'MOEDA', '1')) {
        return false
    }
//Símbolo
    if(!texto('form', 'txt_simbolo', '1', "qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPÇLKJHGFDSAZXCVBNMáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛàÀãÃÕ1234567890,.€.', !@#$%¨&*/()-_=§¹²³£¢¬", 'SÍMBOLO DA MOEDA', '2')) {
        return false
    }
//Origem
    if(!texto('form', 'txt_origem', '2', "-=€!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,.Üüáé§íóúÁÉÍÀ'àºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ", 'ORIGEM', '1')) {
        return false
    }
}
</Script>
</head>
<body onload="document.form.txt_moeda.focus()">
<form name='form' method='post' action='' onsubmit="return validar()">
<table width='60%' align="center" cellspacing ='1' cellpadding='1' border="0">
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='2'>
            Incluir Tipo da Moeda
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Moeda:</b>
        </td>
        <td>
            <input type="text" name="txt_moeda" title="Digite a Moeda" size='35' maxlength='30' class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>S&iacute;mbolo:</b>
        </td>
        <td>
            <input type='text' name='txt_simbolo' title="Digite o S&iacute;mbolo" size='5' maxlength='3' class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Origem:</b>
        </td>
        <td>
            <input type="text" name="txt_origem" title="Digite a Origem" size='35' maxlength='30' class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Descri&ccedil;&atilde;o:
        </td>
        <td>
            <textarea name="txt_descricao" title="Digite a Descri&ccedil;&atilde;o" rows='1' cols='80' maxlength='80' class="caixadetexto"></textarea>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_moeda.focus()" style="color:#ff9900;" class="botao">
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>