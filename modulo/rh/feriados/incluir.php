<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/rh/feriados/feriados.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>FERIADO INCLU�DO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>FERIADO J� EXISTENTE.</font>";

if(!empty($_POST['txt_data_feriado'])) {
    //Tratamento com as vari�veis p/ poder gravar no BD ...
    $data_feriado = data::datatodate($_POST['txt_data_feriado'], '-');
    
    //Verifico se j� n�o foi cadastrado anteriormente esse Feriado nas mesmas data_feriado e data_comemorativa ...
    $sql = "SELECT `id_feriado` 
            FROM `feriados` 
            WHERE `data_feriado` = '$data_feriado' 
            AND `data_comemorativa` = '$_POST[txt_data_comemorativa]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
        $sql = "INSERT INTO `feriados` (`id_feriado`, `data_feriado`, `data_comemorativa`) values (NULL, '$data_feriado', '$_POST[txt_data_comemorativa]') ";
        bancos::sql($sql);
        $valor = 1;
    }else {
        $valor = 2;
    }
}
?>
<html>
<head>
<title>.:: Incluir Feriado(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data Feriado ...
    if(!data('form', 'txt_data_feriado', '4000', 'FERIADO')) {
        return false
    }
//Data Comemorativa ...
    if(!texto('form', 'txt_data_comemorativa', '3', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ��������������������������-() ', 'DATA COMEMORATIVA', '1')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_data_feriado.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Feriado(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data do Feriado: </b>
        </td>
        <td>
            <input type='text' name='txt_data_feriado' title='Digite a Data do Feriado' size='13' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            &nbsp;<img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_feriado&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data Comemorativa: </b>
        </td>
        <td>
            <input type='text' name='txt_data_comemorativa' title='Digite a Data Comemorativa' maxlength='50' size='52' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'feriados.php'" class='botao'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_data_feriado.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>