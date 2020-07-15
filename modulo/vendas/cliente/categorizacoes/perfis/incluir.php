<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
segurancas::geral('/erp/albafer/modulo/vendas/cliente/categorizacoes/categorizacoes.php', '../../../../../');

$mensagem[1] = "<font class='confirmacao'>PERFIL DE CLIENTE INCLUÍDO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>PERFIL DE CLIENTE JÁ EXISTENTE.</font>";

if(!empty($_POST['txt_perfil'])) {
    //Verifica se este Perfil de Cliente digitado pelo usuário já está cadastrado ...
    $sql = "SELECT id_cliente_perfil 
            FROM `clientes_perfils` 
            WHERE `perfil` = '$_POST[txt_perfil]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Perfil não existente
        $sql = "INSERT INTO `clientes_perfils` (`id_cliente_perfil`, `perfil`, `observacao`) VALUES (null, '$_POST[txt_perfil]', '$_POST[txt_observacao]') ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Perfil já existente
        $valor = 2;
    }
}
?>
<html>
<title>.:: Incluir Perfil de Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Perfil
    if(!texto('form', 'txt_perfil', '3', ' abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 'PERFIL', '2')) {
        return false
    }
}
</Script>
</head>
<body onLoad='document.form.txt_perfil.focus()'>
<form name='form' method='post' action='' onSubmit='return validar()'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Perfil de Cliente
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Perfil:</b>
        </td>
        <td>
            <input type='text' name='txt_perfil' title='Digite o Perfil de Cliente' size='22' maxlength='20' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação:
        </td>
      	<td>
            <textarea name='txt_observacao' cols='85' rows='3' maxlength='255' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = '../categorizacoes.php'" class="botao">
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="redefinir('document.form', 'LIMPAR');document.form.txt_perfil.focus()" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>