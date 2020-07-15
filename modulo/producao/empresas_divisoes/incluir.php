<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = '<font class="confirmacao">EMPRESA DIVIS√O INCLUIDA COM SUCESSO.</font>';
$mensagem[2] = '<font class="erro">EMPRESA DIVIS√O J¡ EXISTENTE.</font>';

if(!empty($_POST['txt_divisao'])) {
    //Verifico se j· existe alguma Empresa Divis„o cadastrada de acordo com o que foi digitado pelo Usu·rio ...
    $sql = "SELECT razaosocial 
            FROM `empresas_divisoes` 
            WHERE `razaosocial` = '$_POST[txt_divisao]' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
        $sql = "INSERT INTO `empresas_divisoes` (`id_empresa_divisao` , `id_empresa` ,`razaosocial` , `ativo`) VALUES (NULL, '$_POST[cmb_empresa]', '$_POST[txt_divisao]', '1') ";
        bancos::sql($sql);
        $valor = 1;
    }else {
        $valor = 2;
    }
}
?>
<html>
<title>.:: Incluir Empresa Divis„o ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
// Empresa
    if(!combo('form', 'cmb_empresa', '', 'SELECIONE UMA EMPRESA !')) {
        return false
    }
//Divis„o
    if(!texto('form', 'txt_divisao', '3', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'DIVIS√O', '1')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.cmb_empresa.focus()'>
<form name='form' method='post' action='' onSubmit='return validar()'>
<table width='60%' border="0" cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Empresa Divis„o
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Empresa:
        </td>
        <td>
            <select name="cmb_empresa" title="Selecione a Empresa" class="combo">
            <?
                $sql = "SELECT id_empresa, nomefantasia 
                        FROM `empresas` 
                        WHERE `ativo` = '1' ORDER BY nomefantasia ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Divis„o:</b>
        </td>
        <td>
            <input type="text" name="txt_divisao" title="Digite a Divis„o" size="30" maxlength="80" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="redefinir('document.form', 'LIMPAR');document.form.cmb_empresa.focus()" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>