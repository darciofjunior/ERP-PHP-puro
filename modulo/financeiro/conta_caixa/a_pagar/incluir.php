<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');
$mensagem[1] = "<font class='comfirmacao'>CONTA CAIXA A PAGAR INCLUIDA COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>CONTA CAIXA A PAGAR J¡ EXISTENTE. </font>";

if(!empty($_POST['txt_conta_caixa'])) {
//Verifico se j· existe essa Conta Caixa em Cadastro ...
    $sql = "SELECT conta_caixa 
            FROM `contas_caixas_pagares` 
            WHERE `conta_caixa` = '$_POST[txt_conta_caixa]' 
            AND `ativo` = '1' ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//N„o existe ...
        $sql = "INSERT INTO `contas_caixas_pagares` (`id_conta_caixa_pagar`, `id_modulo`, `conta_caixa`, `descricao`) VALUES (NULL, '$_POST[cmb_modulo]', '$_POST[txt_conta_caixa]', '$_POST[txt_descricao]') ";
        bancos::sql($sql);
        $valor = 1;
    }else {//J· existe ...
        $valor = 2;
    }
}
?>
<html>
<head>
<title>.:: Incluir Conta(s) Caixa(s) ‡ Pagar(es) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' src = '../../../../js/validar.js'></script>
<Script Language = 'JavaScript' src = '../../../../js/geral.js'></script>
<Script Language = 'JavaScript'>
function validar() {
//MÛdulo
    if(!combo('form', 'cmb_modulo', '', 'SELECIONE UM M”DULO !')) {
        return false
    }
//Conta Caixa ‡ Pagar
    if(!texto('form', 'txt_conta_caixa', '2', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,.‹¸·ÈßÌÛ˙¡…Õ¿'‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'CONTA CAIXA', '1')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_conta_caixa.focus()'>
<form name='form' method='post' action='' onsubmit="return validar()">
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan="2">
            Incluir Conta Caixa &agrave; Pagar
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>MÛdulo:</b>
        </td>
        <td>
            <select name="cmb_modulo" title="Selecione o MÛdulo" class="combo">
            <?
                $sql = "SELECT id_modulo, modulo 
                        FROM `modulos` 
                        ORDER BY modulo ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Conta Caixa:</b>
        </td>
        <td>
            <input type="text" name="txt_conta_caixa" title="Digite a Conta Caixa" size='35' maxlength='30' class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>DescriÁ„o:</td>
        <td>
            <textarea name="txt_descricao" title="Digite a DescriÁ„o" cols='80' rows='1' class="caixadetexto"></textarea>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan="2">
            <input type="button" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="redefinir('document.form','LIMPAR');document.form.txt_conta_caixa.focus()" class="botao">
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>