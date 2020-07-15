<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>DEPARTAMENTO INCLUIDO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>DEPARTAMENTO J¡ EXISTENTE.</font>";

if(!empty($_POST['txt_departamento'])) {
//Verifico se j· existe esse Departamento cadastrado no BD ...
    $sql = "SELECT id_departamento 
            FROM `departamentos` 
            WHERE `departamento` = '$_POST[txt_departamento]' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//N„o existe, ent„o atualizo normalmente ...
        $sql = "INSERT INTO `departamentos` (`id_departamento`, `departamento`) VALUES (NULL, '$_POST[txt_departamento]') ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Se j· existe, ent„o, o sistema retorna erro ...
        $valor = 2;
    }
}
?>
<html>
<head>
<title>.:: Incluir Departamento(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Departamento
    if(!texto('form', 'txt_departamento', '3', '·ÈÌÛ˙¡…Õ”⁄Á«abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 ', 'DEPARTAMENTO', '2')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_departamento.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Departamento(s)
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Departamento:</b>
        </td>
        <td>
            <input type='text' name='txt_departamento' title='Digite o Departamento' size='28' maxlength='25' class="caixadetexto">
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick='redefinir("document.form", "LIMPAR");document.form.txt_departamento.focus()' style="color:#ff9900;" class="botao">
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>