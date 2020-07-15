<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>UNIDADE INSUMO INCLUIDA COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>UNIDADE INSUMO J¡ EXISTENTE.</font>";

if(!empty($_POST['txt_unidade'])) {
//Verifico se j· existe essa Unidade cadastrada na Base de Dados ...
    $sql = "SELECT id_unidade 
            FROM unidades 
            WHERE `unidade` = '$_POST[txt_unidade]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//N„o existe ...
        $sql = "INSERT INTO `unidades` (`id_unidade`, `unidade`, `sigla`, `descricao`, `ativo`) VALUES (NULL, '$_POST[txt_unidade]', '$_POST[txt_sigla]', '$_POST[txt_descricao]', '1') ";
        bancos::sql($sql);
        $valor = 1;
    }else {//J· existe ...
        $valor = 2;
    }
}
?>
<html>
<title>.:: Incluir Unidade Insumo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Unidade
    if(!texto('form', 'txt_unidade', '1', "'abcdefghijkÁ«lmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ ¡…Õ”⁄·ÈÌÛ˙„ı√’‡¿ 1234567890", 'UNIDADE', '1')) {
        return false
    }
//Sigla
    if(!texto('form', 'txt_sigla', '1', "'abcdefghijkÁ«lmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ ¡…Õ”⁄·ÈÌÛ˙„ı√’‡¿ 1234567890", 'SIGLA', '1')) {
        return false
    }
//DescriÁ„o
    if(document.form.txt_descricao.value != '') {
        if(!texto('form', 'txt_descricao', '1', "!'#$&*(Á«){[]}/|-_+abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ ¡…Õ”⁄·ÈÌÛ˙„ı√’‡¿ 1234567890", 'DESCRICAO', '1')) {
            return false
        }
    }
}	
</Script>
<body onLoad="document.form.txt_unidade.focus()">
<form name="form" method="post" action='' onSubmit="return validar()">
<table border="0" width='60%' align="center" cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Unidade Insumo
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Unidade:</b>
        </td>
        <td>
            <input type="text" name="txt_unidade" title="Digite a Unidade" size="12" maxlength="10" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Sigla:</b>
        </td>
        <td>
            <input type="text" name="txt_sigla" title="Digite a Sigla" size="6" maxlength="5" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            DescriÁ„o:
        </td>
        <td>
            <input type="text" name="txt_descricao" title="Digite a DescriÁ„o" size="40" maxlength="50" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick='document.form.txt_unidade.focus()' class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>