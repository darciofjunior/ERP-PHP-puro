<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>PRODUTO FINANCEIRO INCLUIDO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>PRODUTO FINANCEIRO J¡ EXISTENTE.</font>";

if(!empty($_POST['txt_discriminacao'])) {
//Verifico se esse Produto Financeiro j· est· cadastrado na Base de Dados ...
    $sql = "SELECT discriminacao 
            FROM `produtos_financeiros` 
            WHERE `discriminacao` = '$_POST[txt_discriminacao]' 
            AND `ativo` = '1' ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//N„o existe ...
/*********************************Controle com o Checkbox*********************************/
        $forcar_preenchimento_icms  = (empty($_POST['chkt_forcar_preenchimento_icms'])) ? 'N' : 'S';
        $data_sys                   = date('Y-m-d H:i:s');
        $txt_observacao             = ucfirst(strtolower($txt_observacao));
        $sql = "INSERT INTO `produtos_financeiros` (`id_produto_financeiro`, `id_grupo`, `discriminacao`, `forcar_icms`, `observacao`, `data_sys`) VALUES (NULL, '$_POST[cmb_grupo]', '$_POST[txt_discriminacao]', '$forcar_preenchimento_icms', '$_POST[txt_observacao]', '$data_sys') ";
        bancos::sql($sql);
        $valor = 1;
    }else {//J· existe ...
        $valor = 2;
    }
}
?>
<html>
<head>
<title>.:: Incluir Produto Financeiro ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Grupo
    if(!combo('form', 'cmb_grupo', '', 'SELECIONE UM GRUPO !')) {
        return false
    }
//DiscriminaÁ„o
    if(!texto('form', 'txt_discriminacao', '3', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,.‹¸·ÈßÌÛ˙¡…Õ¿'‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'DISCRIMINA«√O', '1')) {
        return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit="return validar()">
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='2'>
            Incluir Produto Financeiro
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Grupo:</b>
        </td>
        <td>
            <b>DiscriminaÁ„o:</b>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
        <?
            $sql = "SELECT g.id_grupo, g.nome 
                    FROM `grupos` g 
                    INNER JOIN `contas_caixas_pagares` ccp ON g.id_conta_caixa_pagar = ccp.id_conta_caixa_pagar 
                    INNER JOIN `modulos` m ON ccp.id_modulo = m.id_modulo 
                    WHERE g.`ativo` = '1' ORDER BY g.nome ";
        ?>
        <select name="cmb_grupo" title="Selecione o Grupo" class="combo">
                <?=combos::combo($sql);?>
        </select>
        </td>
        <td>
            <input type='text' name='txt_discriminacao' size='50' maxlength='55' title='Digite a DiscriminaÁ„o' class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td colspan="2">
            <input type="checkbox" name="chkt_forcar_preenchimento_icms" value="S" title="ForÁar preenchimento de ICMS" id="forcar" class="checkbox">
            <label for="forcar">
                ForÁar preenchimento de ICMS
            </label>
        </td>
    </tr>
    <tr class="linhanormal">
        <td colspan='2'>
            ObservaÁ„o:
        </td>
    </tr>
    <tr class="linhanormal">
        <td colspan="2">
            <textarea name="txt_observacao" rows='1' cols='80' maxlength='80' class="caixadetexto"></textarea>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR')" style="color:#ff9900;" class="botao">
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>