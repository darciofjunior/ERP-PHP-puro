<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>GRUPO INCLUÕDO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>GRUPO J¡ EXISTENTE.</font>";

if(!empty($_POST['txt_grupo'])) {
    $data = date('Y-m-d H:i:s');
    $sql = "SELECT id_grupo 
            FROM `grupos` 
            WHERE `nome` = '{$_POST['txt_grupo']}' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $txt_observacao = strtolower($_POST['txt_observacao']);
    if(count($campos) == 0) {
        $sql = "INSERT INTO `grupos` ( `id_grupo` , `id_conta_caixa_pagar` , `referencia` , `nome` , `observacao` , `tipo_custo`, `ativo`) VALUES (NULL, '$_POST[cmb_conta_caixa_pagar]', '$_POST[txt_referencia]', '$_POST[txt_grupo]', '$txt_observacao', '$_POST[cmb_tipo_custo]', '1') ";
        bancos::sql($sql);
        $valor = 1;
    }else {
        $valor = 2;
    }
}
?>
<html>
<title>.:: Incluir Grupo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Conta Caixa ‡ Pagar
    if(!combo('form', 'cmb_conta_caixa_pagar', '', 'SELECIONE UMA CONTA CAIXA ¿ PAGAR !')) {
        return false
    }
//ReferÍncia
    if(document.form.txt_referencia.value != '') {
        if(!texto('form', 'txt_referencia', '3', "1234567890QWERTYUIOP«LKJHGFDSAZXCVBNM zaqwsxcderfvbgtyhnmjuiklopÁ'", 'REFER NCIA', '1')) {
            return false
        }
    }
//Grupo
    if(!texto('form', 'txt_grupo', '1', "abcdefghijkÁ«lmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ ¡…Õ”⁄·ÈÌÛ˙„ı√’‡¿ '1234567890", 'GRUPO', '2')) {
        return false
    }	
//Tipo de Custo
    if(!combo('form', 'cmb_tipo_custo', '', 'SELECIONE O TIPO DE CUSTO !')) {
        return false
    }
}
</Script>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width="70%" border="0" cellspacing ='1' cellpadding='1' align="center">
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Grupo
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Conta Caixa ‡ Pagar:</b>
        </td>
        <td>
            <select name='cmb_conta_caixa_pagar' title='Selecione uma Conta Caixa ‡ Pagar' class='combo'>
            <?
                $sql = "SELECT id_conta_caixa_pagar, conta_caixa 
                        FROM `contas_caixas_pagares` 
                        WHERE `ativo` = '1' ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            ReferÍncia:
        </td>
        <td>
            <input type='text' name='txt_referencia' title='Digite a ReferÍncia' maxlength='7' size='8' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Grupo:</b>
        </td>
        <td>
            <input type='text' name='txt_grupo' title='Digite o Grupo' maxlength='50' size='55' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Custo:</b>
        </td>
        <td>
            <select name='cmb_tipo_custo' title='Selecione o Tipo de Custo' class='combo'>
                <option value='' selected>SELECIONE</option>
                <option value='P'>Processo</option>
                <option value='V'>Vari·vel</option>
                <option value='F'>Fixo</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            ObservaÁ„o:
        </td>
        <td>
                <textarea name='txt_observacao' title="Digite a ObservaÁ„o" cols='50' rows='5' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="button" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="redefinir('document.form', 'LIMPAR')" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>