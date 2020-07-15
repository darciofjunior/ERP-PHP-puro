<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>TIPO DE VALE TRANSPORTE INCLUIDO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>TIPO DE VALE TRANSPORTE J¡ EXISTENTE.</font>";

if(!empty($_POST['txt_tipo_vt'])) {
//Aqui eu verifico no Sistema se j· existe cadastrado o Tipo de Vale Transporte digitado pelo usu·rio ...
    $sql = "SELECT id_vale_transporte 
            FROM `vales_transportes` 
            WHERE `tipo_vt` = '$_POST[txt_tipo_vt]' 
            AND `valor_unitario` = '$_POST[txt_valor_unitario]' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {//N„o existe ...
        $sql = "INSERT INTO `vales_transportes` (`id_vale_transporte`, `tipo_vt`, `valor_unitario`, `ativo`) VALUES (NULL, '$_POST[txt_tipo_vt]', '$_POST[txt_valor_unitario]', '1') ";
        bancos::sql($sql);
        $valor = 1;
    }else {//J· existe ...
        $valor = 2;
    }
}
?>
<html>
<head>
<title>.:: Incluir Vale(s) Transporte(s) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Tipo de VT
    if(!texto('form', 'txt_tipo_vt', '3', '0123456789,.()[]{} +&‚ÍÓÙ˚¬ Œ‘€„ı√’·ÈÌÛ˙¡…Õ”⁄abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZÁ« _-', 'TIPO DE VT', '2')) {
        return false
    }
//Valor Unit·rio
    if(!texto('form', 'txt_valor_unitario', '1', '1234567890,.', 'VALOR UNIT¡RIO', '2')) {
        return false
    }
    return limpeza_moeda('form', 'txt_valor_unitario, ')
}
</Script>
</head>
<body onload='document.form.txt_tipo_vt.focus()'>
<form name="form" method="post" action='' onsubmit="return validar()">
<table border="0" width='60%' align="center" cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            Incluir Vale(s) Transporte(s)
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Tipo de VT:</b>
        </td>
        <td>
            <input type='text' name='txt_tipo_vt' title='Digite o Tipo de VT' size="35" maxlength="50" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Valor Unit·rio:</b>
        </td>
        <td>
            <input type='text' name='txt_valor_unitario' title='Digite o Valor Unit·rio' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size="15" maxlength="12" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="redefinir('document.form', 'LIMPAR');document.form.txt_tipo_vt.focus()" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>