<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='confirmacao'>GEOMETRIA DO A�O INCLU�DA COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>GEOMETRIA DO A�O J� EXISTENTE.</font>";

if(!empty($_POST['txt_nome'])) {
//Verifico se j� existe essa Geometria de A�o cadastrada na Base de Dados ...
    $sql = "SELECT id_geometria_aco 
            FROM `geometrias_acos` 
            WHERE `nome` = '$_POST[txt_nome]' 
            AND `nome` <> '' 
            AND `ativo` = '1' ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//N�o existe ...
        $sql = "INSERT INTO `geometrias_acos` (`id_geometria_aco`, `nome`) VALUES (NULL, '$_POST[txt_nome]') ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Existe ...
        $valor = 2;
    }
}
?>
<html>
<title>.:: Incluir Geometria do A�o ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Geometria do a�o ...
    if(!texto('form', 'txt_nome', '1', 'qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOP�LKJHGFDSAZXCVBNM<>.,;:/?]}()!@#$%�&*() _-+=��������������������������:;������?/��1234567890,.', 'GEOMETRIA DO A�O', '1')) {
        return false
    }
}
</Script>
<body onload='document.form.txt_nome.focus()'>
<form name='form' method='post' action='' onSubmit='return validar()'>
<table width='60%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Geometria do A�o
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Nome:</b>
        </td>
        <td>
            <input type='text' name='txt_nome' title='Digite a Geometria do A�o' size='20' maxlength='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_nome.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>