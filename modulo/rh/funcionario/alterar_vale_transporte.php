<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/rh/funcionario/alterar.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>VALE TRANSPORTE ALTERADO COM SUCESSO.</font>";

//Inserção do Contato para o Cliente
if(!empty($_POST['id_funcionario_vale_transporte'])) {
    $sql = "UPDATE `funcionarios_vs_vales_transportes` SET `qtde_vale` = '$_POST[txt_qtde_vale]' WHERE `id_funcionario_vale_transporte` = '$_POST[id_funcionario_vale_transporte]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}

$id_funcionario_vale_transporte = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_funcionario_vale_transporte'] : $_GET['id_funcionario_vale_transporte'];

//Busca dados do Vale Transporte do Funcionário que foi passado por parâmetro ...
$sql = "SELECT f.`nome`, vt.`tipo_vt`, vt.`valor_unitario`, fvt.* 
        FROM `funcionarios_vs_vales_transportes` fvt 
        INNER JOIN `funcionarios` f ON f.`id_funcionario` = fvt.`id_funcionario` 
        INNER JOIN `vales_transportes` vt ON vt.`id_vale_transporte` = fvt.`id_vale_transporte` 
        WHERE fvt.`id_funcionario_vale_transporte` = '$id_funcionario_vale_transporte' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Vale(s) Transporte(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/arred.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Qtde de Vale ...
    if(!texto('form', 'txt_qtde_vale', '1', '1234567890', 'QTDE DE VALE', '1')) {
        return false
    }
}

function calcular_item_vt() {
    var valor_unitario = strtofloat(document.form.txt_valor_unitario.value)
    if(document.form.txt_qtde_vale.value != '') {//Se a Qtde estiver preenchida ...
        var qtde = document.form.txt_qtde_vale.value
        document.form.txt_valor_total.value = valor_unitario * qtde
        document.form.txt_valor_total.value = arred(document.form.txt_valor_total.value, 2, 1)
    }else {
        document.form.txt_valor_total.value = ''
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.opener.document.form.passo.value = 0
        window.opener.document.form.submit()
    }
}
</Script>
</head>
<body onload="document.form.txt_qtde_vale.focus()" onunload="atualizar_abaixo()">
<form name='form' method="post" action='' onsubmit='return validar()'>
<input type='hidden' name='id_funcionario_vale_transporte' value="<?=$id_funcionario_vale_transporte;?>">
<input type='hidden' name='nao_atualizar'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='3'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Alterar Vale(s) Transporte(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Funcionário:</b>
        </td>
        <td>
            <?=$campos[0]['nome'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo VT:</b>
        </td>
        <td>
            <?=$campos[0]['tipo_vt'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor Unitário:</b>
        </td>
        <td>
            <input type="text" name="txt_valor_unitario" value="<?=number_format($campos[0]['valor_unitario'], 2, ',', '.');?>" title="Valor Unitário" maxlength="11" size="12" class="textdisabled" disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qtde de Vale:</b>
        </td>
        <td>
            <input type="text" name="txt_qtde_vale" value="<?=$campos[0]['qtde_vale'];?>" title="Valor Unitário" onkeyup="verifica(this, 'verifica', 'moeda_especial', '', event);calcular_item_vt();if(this.value == 0) {this.value = ''}" maxlength="11" size="12" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor Total:</b>
        </td>
        <td>
            <input type="text" name="txt_valor_total" value="<?=number_format($campos[0]['valor_unitario'] * $campos[0]['qtde_vale'], 2, ',', '.');?>" title="Valor Total" maxlength="11" size="12" class="textdisabled" disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" style="color:#ff9900;" onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_qtde_vale.focus()" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="fechar(window)" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>