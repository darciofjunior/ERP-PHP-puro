<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/faturamento/tributos/ufs_convenios/ufs_convenios.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>UF INCLUIDA COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>UF J¡ EXISTENTE.</font>";

//Tratamento com as vari·veis que vem por par‚metro ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_uf = $_POST['id_uf'];
}else {
    $id_uf = $_GET['id_uf'];
}

if(!empty($_POST['txt_sigla'])) {
//Verifico se j· existe uma outra UF com a mesma sigla da que j· est· sendo cadastrada ...
    $sql = "SELECT id_uf 
            FROM `ufs` 
            WHERE `sigla` = '$_POST[txt_sigla]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//UF n„o existente ...
        $sql = "INSERT INTO `ufs` (`id_uf`, `sigla`, `estado`, `capital`, `regiao`, `codigo`, `convenio`) VALUES (NULL, '$_POST[txt_sigla]', '$_POST[txt_estado]', '$_POST[txt_capital]', '$_POST[txt_regiao]', '$_POST[txt_codigo]', '$_POST[txt_convenio]') ";
        bancos::sql($sql);
        $valor = 1;
    }else {//UF j· existente
        $valor = 2;
    }
}
?>
<html>
<title>.:: Incluir UF ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Sigla ...
    if(!texto('form', 'txt_sigla', '2', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 'SIGLA', '1')) {
        return false
    }
//Estado ...
    if(!texto('form', 'txt_estado', '4', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ·ÈÌÛ˙¡…Õ”⁄„ı√’‚ÍÓÙ˚¬ Œ‘€Á« ', 'ESTADO', '2')) {
        return false
    }
//Capital ...
    if(document.form.txt_estado.value != '') {
        if(!texto('form', 'txt_estado', '3', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ·ÈÌÛ˙¡…Õ”⁄„ı√’‚ÍÓÙ˚¬ Œ‘€Á« ', 'CAPITAL', '1')) {
            return false
        }
    }
//Regi„o ...
    if(document.form.txt_regiao.value != '') {
        if(!texto('form', 'txt_regiao', '3', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ·ÈÌÛ˙¡…Õ”⁄„ı√’‚ÍÓÙ˚¬ Œ‘€Á« ', 'REGI√O', '1')) {
            return false
        }
    }
//CÛdigo ...
    if(document.form.txt_codigo.value != '') {
        if(!texto('form', 'txt_codigo', '1', '0123456789', 'C”DIGO', '2')) {
            return false
        }
    }
//ConvÍnio ...
    if(document.form.txt_convenio.value != '') {
        if(!texto('form', 'txt_convenio', '3', '·ÈÌÛ˙¡…Õ”⁄„ı√’Á«‚ÍÓÙ˚¬ Œ‘€abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-/._()0123456789+ ', 'CONV NIO', '2')) {
            return false
        }
    }
}
</Script>
</head>
<body onLoad="document.form.txt_sigla.focus()">
<form name="form" method="post" action='' onSubmit="return validar()">
<table border="0" width='60%' align="center" cellspacing='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir UF
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Sigla:</b>
        </td>
        <td>
            <input type="text" name="txt_sigla" title="Digite a Sigla" size="3" maxlength="2" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Estado:</b>
        </td>
        <td>
            <input type="text" name="txt_estado" title="Digite o Estado" size="25" maxlength="23" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Capital:
        </td>
        <td>
            <input type="text" name="txt_capital" title="Digite a Capital" size="30" maxlength="28" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Regi„o:
        </td>
        <td>
            <input type="text" name="txt_regiao" title="Digite a Regi„o" size="30" maxlength="28" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            CÛdigo:
        </td>
        <td>
            <input type="text" name="txt_codigo" title="Digite o CÛdigo" onkeyup="verifica(this, 'moeda_especial', '0', '', event);if(this.value == 0) this.value = ''" size="5" maxlength="2" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            ConvÍnio:
        </td>
        <td> 
            <input type="text" name="txt_convenio" title="Digite o ConvÍnio" size="50" maxlength="50" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'ufs_convenios.php'" class="botao">
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="redefinir('document.form', 'LIMPAR');document.form.txt_sigla.focus()" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>