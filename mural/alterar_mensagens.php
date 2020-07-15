<?
require('../lib/segurancas.php');
require('../lib/data.php');
session_start('funcionarios');

$mensagem[1] = "<font class='confirmacao'>MENSAGEM ALTERADA COM SUCESSO.</font>";

$sql = "SELECT login 
	FROM `logins` 
	WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
$campos = bancos::sql($sql);
$login  = $campos[0]['login'];

//InserÁ„o de Mensagens ...
if(!empty($_POST['txt_mensagem'])) {
    $data_inicial   = data::datatodate($_POST['txt_data_inicial'], '-').' '.$_POST['txt_hora_inicial'];
    $data_final     = data::datatodate($_POST['txt_data_final'], '-').' '.$_POST['txt_hora_final'];

    $sql = "UPDATE `mural_msgs` SET `mensagem` = '$_POST[txt_mensagem]', `login_responsavel` = '$login', `tipo_apresentacao` = '$_POST[cmb_tipo_apresentacao]', `data_show_inicial` = '$data_inicial', `data_show_final` = '$data_final', `data_sys` = '".date('H:i:s Y-m-d')."' WHERE `id_mural_msgs` = '$_POST[id_mural_msgs]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}

//Procedimento quando carrega a Tela ...
$id_mural_msgs = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_mural_msgs'] : $_GET['id_mural_msgs'];

//Busca de Dados p/ alterar a Mensagem com o $id_mural_msgs passado por par‚metro ...
$sql = "SELECT * 
        FROM `mural_msgs` 
        WHERE `id_mural_msgs` = '$id_mural_msgs' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Mensagem(ns) do Mural ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Tipo de ApresentaÁ„o
    if(!combo('form', 'cmb_tipo_apresentacao', '', 'SELECIONE UM TIPO DE APRESENTA«√O !')) {
        return false
    }
//Mensagem
    if(!texto('form', 'txt_mensagem', '3', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'MENSAGEM', '1')) {
        return false
    }
//Data de Show Inicial
    if(document.form.txt_data_inicial.disabled == false) {//Somente se a caixa estiver habilitada ..
//Data Show Inicial
        if(!data('form', 'txt_data_inicial', "4000", 'SHOW INICIAL')) {
            return false
        }
//Hora Inicial
        if(!texto('form', 'txt_hora_inicial', '4', '1234567890:', 'HORA INICIAL', '1')) {
            return false
        }
    }
//Data de Show Final
    if(document.form.txt_data_final.disabled == false) {//Somente se a caixa estiver habilitada ..
        if(!data('form', 'txt_data_final', "4000", 'SHOW FINAL')) {
            return false
        }
//Hora Final
        if(!texto('form', 'txt_hora_final', '4', '1234567890:', 'HORA FINAL', '1')) {
            return false
        }
    }
//ComparaÁ„o entre a Data Inicial e a Data Final ...
    var data_inicial    = document.form.txt_data_inicial.value
    var data_final      = document.form.txt_data_final.value
    data_inicial        = data_inicial.substr(6,4)+data_inicial.substr(3,2)+data_inicial.substr(0,2)
    data_final          = data_final.substr(6,4)+data_final.substr(3,2)+data_final.substr(0,2)
    data_inicial        = eval(data_inicial)
    data_final          = eval(data_final)

    if(data_final < data_inicial) {
        alert('DATA FINAL INV¡LIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
    document.form.passo.value = 1
}

function alterar_tipo_apresentacao() {
    if(document.form.cmb_tipo_apresentacao.value == '' || document.form.cmb_tipo_apresentacao.value == 'C') {
//Se o Tipo de ApresentaÁ„o for Constante, ent„o eu desabilito as caixas ...
        document.form.txt_data_inicial.disabled     = true
        document.form.txt_data_final.disabled       = true
        document.form.txt_hora_inicial.disabled     = true
        document.form.txt_hora_final.disabled       = true
//Designer de Desabilitado ...
        document.form.txt_data_inicial.className    = 'textdisabled'
        document.form.txt_data_final.className      = 'textdisabled'
        document.form.txt_hora_inicial.className    = 'textdisabled'
        document.form.txt_hora_final.className      = 'textdisabled'
    }else {
//Se o Tipo de ApresentaÁ„o for Tempor·ria, ent„o eu habilito as caixas de Data e Hora ...
        document.form.txt_data_inicial.disabled     = false
        document.form.txt_data_final.disabled       = false
        document.form.txt_hora_inicial.disabled     = false
        document.form.txt_hora_final.disabled       = false
//Designer de Habilitado ...
        document.form.txt_data_inicial.className    = 'caixadetexto'
        document.form.txt_data_final.className      = 'caixadetexto'
        document.form.txt_hora_inicial.className    = 'caixadetexto'
        document.form.txt_hora_final.className      = 'caixadetexto'
    }
}
</Script>
<body onload='alterar_tipo_apresentacao();document.form.cmb_tipo_apresentacao.focus()' onunload="top.opener.parent.corpo.document.location = 'mural.php'">
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Caixa que faz controle para submeter a tela de Mensagem(ns)-->
<input type='hidden' name='id_mural_msgs' value='<?=$id_mural_msgs;?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            Alterar Mensagem(ns) do Mural
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <b>Login:</b>
        </td>
        <td width='80%'>
            <?=$login;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de ApresentaÁ„o:</b>
        </td>
        <td>
            <select name='cmb_tipo_apresentacao' title='Selecione o Tipo de ApresentaÁ„o' onchange='alterar_tipo_apresentacao()' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
            <?
                if($campos[0]['tipo_apresentacao'] == 'C') {
            ?>
                <option value='C' selected>CONSTANTE</option>
                <option value='T'>TEMPOR¡RIA</option>
            <?
                }else {
            ?>
                <option value='C'>CONSTANTE</option>
                <option value='T' selected>TEMPOR¡RIA</option>
            <?
                }
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Mensagem:</b>
        </td>
        <td>
            <input type='text' name='txt_mensagem' value='<?=$campos[0]['mensagem'];?>' title='Digite a Mensagem' size='55' maxlength='255' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data Inicial:</b>
        </td>
        <td>
        <?
            if($campos[0]['data_show_inicial'] != '0000-00-00 00:00:00') {
                $data_inicial = data::datetodata(substr($campos[0]['data_show_inicial'], 0, 10), '/');
                $hora_inicial = substr($campos[0]['data_show_inicial'], 11, 8);
            }
        ?>
            <input type='text' name='txt_data_inicial' value='<?=$data_inicial;?>' title="Digite a Data Inicial" onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='textdisabled' disabled>
            &nbsp;<img src = '../imagem/calendario.gif' width='12' height='12' border='0' alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="if(document.form.txt_data_inicial.disabled == false) {nova_janela('../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALEND¡RIO', '', '', '', '', 270, 240, 'c', 'c')}">&nbsp;Calend&aacute;rio &nbsp;
            <input type='text' name='txt_hora_inicial' value='<?=$hora_inicial;?>' title='Digite a Hora Inicial' onkeyup="verifica(this, 'hora', '', '', event)" size='6' maxlength='5' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data Final:</b>
        </td>
        <td>
        <?
            if($campos[0]['data_show_final'] != '0000-00-00 00:00:00') {
                $data_final = data::datetodata(substr($campos[0]['data_show_final'], 0, 10), '/');
                $hora_final = substr($campos[0]['data_show_final'], 11, 8);
            }
        ?>
            <input type='text' name='txt_data_final' value='<?=$data_final;?>' title="Digite a Data Final" onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='textdisabled' disabled>
            &nbsp;<img src='../imagem/calendario.gif' width='12' height='12' border='0' alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="if(document.form.txt_data_inicial.disabled == false) {nova_janela('../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALEND¡RIO', '', '', '', '', 270, 240, 'c', 'c')}">&nbsp;Calend&aacute;rio &nbsp;
            <input type='text' name='txt_hora_final' value='<?=$hora_final;?>' title='Digite a Hora Final' onkeyup="verifica(this, 'hora', '', '', event)" size='6' maxlength='5' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'incluir_mensagens.php'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.cmb_tipo_apresentacao.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>