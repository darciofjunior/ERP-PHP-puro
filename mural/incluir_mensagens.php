<?
require('../lib/segurancas.php');
require('../lib/data.php');
session_start('funcionarios');
$mensagem[1] = "<font class='confirmacao'>MENSAGEM INCLUÕDA COM SUCESSO.</font>";


/*****************************Exclus„o dos Item(ns) de Mensagem do Mural*****************************/
if(!empty($_POST['id_mural_msgs'])) {
    $sql = "DELETE FROM `mural_msgs` WHERE `id_mural_msgs` = '$_POST[id_mural_msgs]' LIMIT 1 ";
    bancos::sql($sql);
?>
<Script Language = 'JavaScript'>
    alert('MENSAGEM EXCLUÕDA COM SUCESSO !')
</Script>
<?
}
/****************************************************************************************************/

$sql = "SELECT login 
	FROM `logins` 
	WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
$campos = bancos::sql($sql);
$login  = $campos[0]['login'];

//InserÁ„o de Mensagens ...
if(!empty($_POST['txt_mensagem'])) {
    $data_inicial   = data::datatodate($_POST['txt_data_inicial'], '-').' '.$_POST['txt_hora_inicial'];
    $data_final     = data::datatodate($_POST['txt_data_final'], '-').' '.$_POST['txt_hora_final'];
    $data_sys       = date('H:i:s Y-m-d');

    $sql = "INSERT INTO `mural_msgs` (`id_mural_msgs`, `mensagem`, `login_responsavel`, `tipo_apresentacao`, `data_show_inicial`, `data_show_final`, `data_sys`, `ativo`) VALUES (NULL, '$_POST[txt_mensagem]', '$login', '$_POST[cmb_tipo_apresentacao]', '$data_inicial', '$data_final', '$data_sys', '1') ";
    bancos::sql($sql);
    $valor = 1;
}
?>
<html>
<head>
<title>.:: Incluir Mensagem(ns) do Mural ::.</title>
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

//Exclus„o de Mensagem do Mural
function alterar_mural_mensagem(id_mural_msgs) {
    window.location = 'alterar_mensagens.php?id_mural_msgs='+id_mural_msgs
}

//Exclus„o de Mensagem do Mural
function excluir_mural_mensagem(valor) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM DE MENSAGEM ?')
    if(mensagem == false) {
        return false
    }else {
        document.form.passo.value = 0
        document.form.id_mural_msgs.value = valor
        document.form.submit()
    }
}
</Script>
<body onload='document.form.cmb_tipo_apresentacao.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Caixa que faz controle para submeter a tela de Mensagem(ns)-->
<input type='hidden' name='id_mural_msgs'>  
<input type='hidden' name='passo'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='atencao' align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Mensagem(ns) do Mural
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <b>Login:</b>
        </td>
        <td width='80%'>
        <?
            $sql = "SELECT login 
                    FROM `logins` 
                    WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
            $campos = bancos::sql($sql);
            echo $campos[0]['login'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de ApresentaÁ„o:</b>
        </td>
        <td>
            <select name='cmb_tipo_apresentacao' title='Selecione o Tipo de ApresentaÁ„o' onchange='alterar_tipo_apresentacao()' class='combo'>
                <option value='' style="color:red">SELECIONE</option>
                <option value='C'>CONSTANTE</option>
                <option value='T'>TEMPOR¡RIA</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Mensagem:</b>
        </td>
        <td>
            <input type='text' name='txt_mensagem' title='Digite a Mensagem' size='55' maxlength='255' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data Inicial:</b>
        </td>
        <td>
            <input type='text' name='txt_data_inicial' title='Digite a Data Inicial' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='textdisabled' disabled>
            &nbsp;<img src = '../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="if(document.form.txt_data_inicial.disabled == false) {nova_janela('../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALEND¡RIO', '', '', '', '', 270, 240, 'c', 'c')}">&nbsp;Calend&aacute;rio &nbsp;
            <input type='text' name='txt_hora_inicial' title='Digite a Hora Inicial' onkeyup="verifica(this, 'hora', '', '', event)" size="6" maxlength="5" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data Final:</b>
        </td>
        <td>
            <input type='text' name='txt_data_final' title='Digite a Data Final' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='textdisabled' disabled>
            &nbsp;<img src = '../imagem/calendario.gif' width='12' height='12' border='0' alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="if(document.form.txt_data_inicial.disabled == false) {nova_janela('../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALEND¡RIO', '', '', '', '', 270, 240, 'c', 'c')}">&nbsp;Calend&aacute;rio &nbsp;
            <input type='text' name='txt_hora_final' title='Digite a Hora Final' onkeyup="verifica(this, 'hora', '', '', event)" size='6' maxlength='5' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.cmb_tipo_apresentacao.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
<?
//Aqui traz todas as Mensagens que foram cadastrada(s) no Mural ...
    $sql = "SELECT * 
            FROM `mural_msgs` 
            WHERE `ativo` = '1' ORDER BY id_mural_msgs DESC ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
<br/>
<table width='95%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhadestaque' align='center'>
        <td colspan='7'>
            Mensagem(ns)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <b><i>Login</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Tipo de ApresentaÁ„o</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Mensagem</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Data Inicial</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Data Final</i></b>
        </td>
        <td width='25' bgcolor='#CCCCCC'>
            &nbsp;
        </td>
        <td width='25' bgcolor='#CCCCCC'>
            &nbsp;
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align="center">
        <td>
            <?=$campos[$i]['login_responsavel'];?>
        </td>
        <td>
        <?
            if($campos[$i]['tipo_apresentacao'] == 'C') {
                echo 'CONSTANTE';
            }else {
                echo 'TEMPOR¡RIA';
            }
        ?>
        </td>
        <td align='left'>
            <?=$campos[$i]['mensagem'];?>
        </td>
        <td>
        <?
            if($campos[$i]['data_show_inicial'] != '0000-00-00 00:00:00') echo data::datetodata(substr($campos[$i]['data_show_inicial'], 0, 10), '/').' '.substr($campos[$i]['data_show_inicial'], 11, 8);
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['data_show_final'] != '0000-00-00 00:00:00') echo data::datetodata(substr($campos[$i]['data_show_final'], 0, 10), '/').' '.substr($campos[$i]['data_show_final'], 11, 8);
        ?>
        </td>
        <td>
        <?
/*Se o usu·rio logado for o mesmo que registrou a Mensagem ent„o este pode estar alterando ou se for 
o "D·rcio" pq programa ...*/
            if($login == $campos[$i]['login_responsavel'] || $_SESSION['id_funcionario'] == 98) {
        ?>
                <img src = "../imagem/menu/alterar.png" border='0' title="Alterar" alt="Alterar" onClick="alterar_mural_mensagem('<?=$campos[$i]['id_mural_msgs'];?>')">
        <?
            }
        ?>
        </td>
        <td>
        <?
/*Se o usu·rio logado for o mesmo que registrou a Mensagem ent„o este pode estar excluindo ou se for 
o "D·rcio" pq programa ...*/
            if($login == $campos[$i]['login_responsavel'] || $_SESSION['id_funcionario'] == 98) {
        ?>
                <img src = "../imagem/menu/excluir.png" border='0' title="Excluir" alt="Excluir" onClick="excluir_mural_mensagem('<?=$campos[$i]['id_mural_msgs'];?>')">
        <?
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            &nbsp;
        </td>
    </tr>
<?
    }
?>
</table>
</form>
</body>
</html>