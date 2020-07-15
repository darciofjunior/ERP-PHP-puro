<?
require('../../lib/segurancas.php');

$mensagem[1] = "<font class='confirmacao'>CEP ALTERADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>CEP J¡ EXISTENTE.</font>";

if(!empty($_POST['hdd_cep'])) {
//Verifico se j· existe esse CEP cadastrado na Base de Dados ...
    $sql = "SELECT id_cep 
            FROM `ceps` 
            WHERE `cep` = '$_POST[txt_cep]' 
            AND `id_cep` <> '$_POST[hdd_cep]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Cep n„o existente ...
        $sql = "UPDATE `ceps` SET `cep` = '$_POST[txt_cep]', `uf` = '$_POST[txt_uf]', `cidade` = '$_POST[txt_cidade]', `bairro` = '$_POST[txt_bairro]', `logradouro` = '$_POST[txt_logradouro]' WHERE `id_cep` = '$_POST[hdd_cep]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Cep j· existente ...
        $valor = 2;
    }
}

//Busco dados do CEP passado por par‚metro ...
$sql = "SELECT * 
        FROM `ceps` 
        WHERE `cep` = '$_GET[txt_cep]' LIMIT 1 ";
$campos = bancos::sql($sql);
if(count($campos) == 0) {//Se n„o existir esse CEP cadastro na Base de Dados ...
?>
    <Script Language = 'JavaScript'>
        var resposta = confirm('ESSE CEP N√O EST¡ CADASTRADO NO SISTEMA !!! DESEJA INCLUIR O MESMO ?')
        if(resposta == true) {
            window.location = 'incluir.php'
        }else {
            window.location = 'opcoes.php'
        }
    </Script>
<?
    exit;
}
?>
<html>
<head>
<title>.:: Alterar CEP ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Cep
    if(!texto('form', 'txt_cep', '9', '0123456789-', 'CEP', '2')) {
        return false
    }
//UF
    if(document.form.txt_uf.value != '') {
        if(!texto('form', 'txt_uf', '2', 'qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«JHGFDSAZXCVBNM 0123456789', 'UF', '1')) {
            return false
        }
    }
//Cidade
    if(document.form.txt_cidade.value != '') {
        if(!texto('form', 'txt_cidade', '3', "qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«JHGFDSAZXCVBNM‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’'.- 0123456789", 'CIDADE', '1')) {
            return false
        }
    }
//Bairro
    if(document.form.txt_bairro.value != '') {
        if(!texto('form', 'txt_bairro', '3', 'qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«JHGFDSAZXCVBNM‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’.- 0123456789', 'BAIRRO', '2')) {
            return false
        }
    }
//Logradouro
    if(!texto('form', 'txt_logradouro', '3', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ 0123456789", 'LOGRADOURO', '2')) {
        return false
    }
//Transformando em Mai˙sculo p/ poder gravar no BD ...
    document.form.txt_uf.value = document.form.txt_uf.value.toUpperCase()
    document.form.txt_cidade.value = document.form.txt_cidade.value.toUpperCase()
    document.form.txt_bairro.value = document.form.txt_bairro.value.toUpperCase()
    document.form.txt_logradouro.value = document.form.txt_logradouro.value.toUpperCase()
}
</Script>
<body onload="document.form.txt_cep.focus()">
<form name='form' method='post' action='' onSubmit="return validar()">
<input type='hidden' name='hdd_cep' value="<?=$campos[0]['id_cep'];?>">
<table width='80%' border="0" cellspacing ='1' cellpadding='1' align="center">
    <tr class="atencao" align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Cep
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Cep:</b>
        </td>
        <td>
            <input type="text" name="txt_cep" value="<?=$campos[0]['cep'];?>" title="Digite o CEP" onkeyup="verifica(this, 'cep', '', '', event)" size="10" maxlength="9" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            UF:
        </td>
        <td>
            <input type="text" name="txt_uf" value="<?=$campos[0]['uf'];?>" title="Digite a UF" size="3" maxlength="2" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Cidade:
        </td>
        <td>
            <input type="text" name="txt_cidade" value="<?=$campos[0]['cidade'];?>" title="Digite a Cidade" size="36" maxlength="35" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Bairro:
        </td>
        <td>
            <input type="text" name="txt_bairro" value="<?=$campos[0]['bairro'];?>" title="Digite o Bairro" size="36" maxlength="35" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Logradouro:</b>
        </td>
        <td>
            <input type="text" name="txt_logradouro" value="<?=$campos[0]['logradouro'];?>" title="Digite o Logradouro" size="40" maxlength="75" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'opcoes.php'" class="botao">
            <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" class="botao" style="color:#ff9900;" onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_cep.focus()">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>