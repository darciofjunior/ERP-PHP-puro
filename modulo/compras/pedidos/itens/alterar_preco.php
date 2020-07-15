<?
require('../../../../lib/data.php');
require('../../../../lib/segurancas.php');
session_start('funcionarios');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../../');

//Busca o Preço do Item passado por parâmetro ...
$sql = "SELECT preco_unitario 
        FROM `itens_pedidos` 
        WHERE `id_item_pedido` = '$_GET[id_item_pedido]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Preço Unitário de Item de Pedido ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    if(!texto('form', 'txt_preco_unitario', '1', '0123456789,.', 'PREÇO UNITÁRIO', '2')) {
        return false
    }
    window.opener.document.form.txt_preco_unitario.value = document.form.txt_preco_unitario.value
    window.opener.calcular()
    window.close()
}
</Script>
</head>
<body topmargin='10' onload='document.form.txt_preco_unitario.focus()'>
<form name="form" method="post">
<table width='90%' border="0" align="center" cellpadding='1' cellspacing ='1'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Preço Unitário de Item de Pedido
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Preço:</b>
        </td>
        <td>
            <input type="text" name="txt_preco_unitario" value="<?=number_format($campos[0]['preco_unitario'], 2, ',', '.');?>" title="Digite o Preço Unitário" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size="12" maxlength="10" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form','REDEFINIR');document.form.txt_preco_unitario.focus()" style="color:#ff9900;" class="botao">
            <input type="button" name="cmd_atualizar" value="Atualizar" title="Atualizar" style="color:green" onclick="return validar()" class="botao">
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="window.close()" style="color:red" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>