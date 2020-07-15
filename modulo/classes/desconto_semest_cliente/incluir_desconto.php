<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral('/erp/albafer/modulo/classes/desconto_semest_cliente/desconto_semestral.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>FAIXA DE DESCONTO DO CLIENTE INCLUÍDA COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>FAIXA DE DESCONTO DO CLIENTE JÁ EXISTENTE.</font>";

if(!empty($_POST['txt_desconto_cliente'])) {
    $sql = "SELECT id_descontos_clientes 
            FROM `descontos_clientes` 
            WHERE `desconto_cliente` = '$_POST[txt_desconto_cliente]' 
            AND `valor_semestral` = '$_POST[txt_valor_semestral]' 
            AND `tabela_analise` = '$_POST[tabela_analise]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Faixa de Desconto não existente
        $sql = "INSERT INTO `descontos_clientes` (`id_descontos_clientes`, `desconto_cliente`, `valor_semestral`, `tabela_analise`) VALUES (NULL, '$_POST[txt_desconto_cliente]', '$_POST[txt_valor_semestral]', '$_POST[tabela_analise]') ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Faixa de Desconto já existente
        $valor = 2;
    }
}
?>
<html>
<title>.:: Incluir Faixa de Desconto do Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Valor Semestral
    if(!texto('form', 'txt_valor_semestral', '3', '1234567890,.', 'VALOR SEMESTRAL', '2')) {
        return false
    }
//Desconto do Cliente
    if(!texto('form', 'txt_desconto_cliente', '3', '1234567890,', 'DESCONTO DO CLIENTE', '2')) {
        return false
    }
    return limpeza_moeda('form', 'txt_valor_semestral, txt_desconto_cliente, ')
}
</Script>
</head>
<body onload='document.form.txt_desconto_cliente.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='tabela_analise' value='<?=$tabela_analise;?>'>
<table width='50%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Faixa de Desconto do Cliente
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor Semestral:</b>
        </td>
        <td>
            <input type='text' name='txt_valor_semestral' title='Digite o Valor Semestral' size='12' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Desconto Cliente:</b>
        </td>
        <td>
            <input type='text' name='txt_desconto_cliente' title='Digite o Desconto do Cliente' size='8' maxlength='6' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'> %
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'desconto_semestral.php'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_desconto_cliente.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>