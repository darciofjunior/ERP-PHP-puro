<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/sistema/criar_menu/criar_menu.php', '../../../');

$mensagem[1] = '<font class="confirmacao">M�DULO INCLUIDO COM SUCESSO.</font>';
$mensagem[2] = '<font class="erro">M�DULO J� EXISTENTE.</font>';

if(!empty($_POST['txt_modulo'])) {
    //Verifica se o m�dulo que foi digitado pelo Usu�rio j� existe ...
    $sql = "SELECT id_modulo 
            FROM `modulos` 
            WHERE `modulo` = '$_POST[txt_modulo]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
        $sql = "INSERT INTO `modulos` (`id_modulo`, `modulo`) VALUES (null, '$_POST[txt_modulo]') ";
        bancos::sql($sql);
        $valor = 1;
    }else {
        $valor = 2;
    }
}
?>
<html>
<head>
<title>.:: Gerar M�dulo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(!texto('form', 'txt_modulo', '3', 'qwertyuiop�lkjhgfdsazxcvbnm�������������� QWERTYUIOP�LKJHGFDSAZXCVBNM��������������1234567890\/���������\;][=-|<>.,:?}{+_!@#$%�&*()', 'M�DULO', '2')) {
        return false
    }
//Aqui � para n�o atualizar os frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que s� atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.location = 'criar_menu.php'
}
</Script>
</head>
<body onload='document.form.txt_modulo.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<table width='600' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Gerar M�dulo(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Nome:</b>
        </td>
        <td>
            <input type='text' name='txt_modulo' title='Digite o M�dulo' size='25' maxlength='25' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' style='color:#ff9900' onclick='document.form.txt_modulo.focus()' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick="fechar(window)" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>