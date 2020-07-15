<?
require('../../../../lib/segurancas.php');

//Tratamento com as vari·veis que vem por par‚metro ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_uf  = $_POST['id_uf'];
    $pop_up = $_POST['pop_up'];
}else {
    $id_uf  = $_GET['id_uf'];
    $pop_up = $_GET['pop_up'];
}
if(empty($pop_up)) require('../../../../lib/menu/menu.php');//Significa que essa Tela "N√O" foi aberta como sendo Pop-UP ...

require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/faturamento/tributos/ufs_convenios/ufs_convenios.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>UF ALTERADA COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>UF J¡ EXISTENTE.</font>";

if(!empty($_POST['txt_sigla'])) {
//Verifico se j· existe uma outra UF com a mesma sigla da Atual que eu estou alterando ...
    $sql = "SELECT id_uf 
            FROM `ufs` 
            WHERE `sigla` = '$_POST[txt_sigla]' 
            AND `id_uf` <> '$_POST[id_uf]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//UF n„o existente ...
        $sql = "UPDATE `ufs` SET `sigla` = '$_POST[txt_sigla]', `estado` = '$_POST[txt_estado]', `capital` = '$_POST[txt_capital]', `regiao` = '$_POST[txt_regiao]', `codigo` = '$_POST[txt_codigo]', `convenio` = '$_POST[txt_convenio]' WHERE `id_uf` = '$id_uf' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else {//UF j· existente
        $valor = 2;
    }
}

$sql = "SELECT * 
        FROM `ufs` 
        WHERE `id_uf` = '$id_uf' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<title>.:: Alterar UF ::.</title>
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
<body onload='document.form.txt_sigla.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--************Controles de Tela************-->
<input type='hidden' name='id_uf' value='<?=$id_uf;?>'>
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<!--*****************************************-->
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar UF
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Sigla:</b>
        </td>
        <td>
            <input type='text' name="txt_sigla" value="<?=$campos[0]['sigla'];?>" title="Digite a Sigla" size="3" maxlength="2" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Estado:</b>
        </td>
        <td>
            <input type='text' name="txt_estado" value="<?=$campos[0]['estado'];?>" title="Digite o Estado" size="25" maxlength="23" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Capital:
        </td>
        <td>
            <input type='text' name="txt_capital" value="<?=$campos[0]['capital'];?>" title="Digite a Capital" size="30" maxlength="28" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Regi„o:
        </td>
        <td>
            <input type='text' name="txt_regiao" value="<?=$campos[0]['regiao'];?>" title="Digite a Regi„o" size="30" maxlength="28" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            CÛdigo:
        </td>
        <td>
            <input type='text' name="txt_codigo" value="<?=$campos[0]['codigo'];?>" title="Digite o CÛdigo" onkeyup="verifica(this, 'moeda_especial', '0', '', event);if(this.value == 0) this.value = ''" size="5" maxlength="2" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            ConvÍnio:
        </td>
        <td> 
            <input type='text' name="txt_convenio" value="<?=$campos[0]['convenio'];?>" title="Digite o ConvÍnio" size="50" maxlength="50" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
        <?
            if(empty($pop_up)) {//Significa que essa Tela foi aberta do Modo Normal ...
        ?>
                <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'ufs_convenios.php'" class='botao'>
        <?
            }
        ?>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_sigla.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>