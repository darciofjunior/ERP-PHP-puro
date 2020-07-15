<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');
$mensagem[1] = "<font class='confirmacao'>CTT INCLUIDO COM SUCESSO.</font>";

if(!empty($_POST['txt_aplicacao_usual'])) {
//Aqui eu busco o núm. do último CTT, p/ auxiliar na geração do próximo núm. de CTT
    $sql = "SELECT id_ctt 
            FROM `ctts` 
            ORDER BY id_ctt DESC LIMIT 1 ";
    $campos = bancos::sql($sql);
    $id_ctt = $campos[0]['id_ctt'];
//Novo Número de CTT
    $codigo = 'CTT'.($id_ctt + 1);
    $sql = "INSERT INTO `ctts` (`id_ctt`, `codigo`, `aplicacao_usual`, `dureza_interna`, `descricao`) VALUES (NULL, '$codigo', '$_POST[txt_aplicacao_usual]', '$_POST[txt_dureza_interna]', '$_POST[txt_descricao]') ";
    bancos::sql($sql);
    $id_ctt = bancos::id_registro();
    echo '<font class="erro"><center>CTT N.º "'.$id_ctt.'"</center></font>';
    $valor = 1;
}
?>
<html>
<title>.:: Incluir CTT ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Aplicação Usual
    if(!texto('form', 'txt_aplicacao_usual', '3', "-=!@¹²³£¢¬{} 1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJHGFDSAZXCVBNM,.'ÜüáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛãõÃÕ.,%&*$()@#<>ªº°:;\/", 'APLICAÇÃO USUAL', '1')) {
        return false
    }
//Dureza Interna
    if(!texto('form', 'txt_dureza_interna', '3', "-=!@¹²³£¢¬{} 1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJHGFDSAZXCVBNM,.'ÜüáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛãõÃÕ.,%&*$()@#<>ªº°:;\/", 'DUREZA INTERNA', '1')) {
        return false
    }
}
</Script>
<body onload="document.form.txt_aplicacao_usual.focus()">
<form name="form" method="post" action='' onsubmit="return validar()">
<table border='0' width='60%' cellspacing ='1' cellpadding='1' align="center">
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir CTT
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Aplicação Usual:</b>
        </td>
        <td>
            <input type="text" name="txt_aplicacao_usual" title="Digite a Aplicação Usual" maxlength="50" size="60" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Dureza Interna:</b>
        </td>
        <td>
            <input type="text" name="txt_dureza_interna" title="Digite a Dureza Interna" maxlength='30' size='35' class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Descrição:
        </td>
        <td>
            <textarea name='txt_descricao' title="Digite a Descrição" cols='85' rows='3' maxlength='255' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="redefinir('document.form', 'LIMPAR');document.form.txt_aplicacao_usual.focus()" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>