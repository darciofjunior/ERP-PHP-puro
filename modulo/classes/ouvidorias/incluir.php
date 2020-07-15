<?
require('../../../lib/segurancas.php');
require('../../../lib/comunicacao.php');
session_start('fucionarios');
$mensagem[1] = "<font class='confirmacao'>OUVIDORIA REGISTRADA E E-MAIL ENVIADO COM SUCESSO.</font>";

if(!empty($_POST['txt_assunto'])) {//Registrando a Ouvidoria na Base de Dados ...
    $sql = "INSERT INTO `ouvidorias` (`id_ouvidoria`, `assunto`, `id_departamento`, `ocorrencia`, `data_sys`) VALUES (NULL, '$_POST[txt_assunto]', '$_POST[cmb_departamento]', '$_POST[txt_ocorrencia]', '".date('Y-m-d H:i:s')."') ";
    bancos::sql($sql);
    //Aqui eu busco o nome do Departamento pra quem foi mandado o E-mail ...
    $sql = "SELECT `departamento` 
            FROM `departamentos` 
            WHERE `id_departamento` = '$_POST[cmb_departamento]' LIMIT 1 ";
    $campos_departamentos = bancos::sql($sql);
    comunicacao::email('erp@grupoalbafer.com.br', 'roberto@grupoalbafer.com.br; wilson@grupoalbafer.com.br', '', $_POST['txt_assunto'].' - '.$campos_departamentos[0]['departamento'].' (Ouvidoria)', $_POST['txt_ocorrencia'].'<br><br>***Ouvidoria');
    $valor = 1;
}
?>
<html>
<head>
<title>.:: Registrar Ouvidoria ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Assunto ...
    if(!texto('form', 'txt_assunto', '1', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789‚ÍÓÙ˚¬ Œ‘€„ı√’·ÈÌÛ˙¡…Õ”⁄Á«*()_-[]{}$#@!?. ', 'ASSUNTO', '2')) {
        return false
    }
//Departamento
    if(!combo('form', 'cmb_departamento', '', 'SELECIONE O DEPARTAMENTO !')) {
        return false
    }
//OcorrÍncia
    if(document.form.txt_ocorrencia.value == '') {
        alert('DIGITE A OCORR NCIA !')
        document.form.txt_ocorrencia.focus()
        return false
    }
    document.getElementById('loading').style.display = 'block'
}
</Script>
</head>
<body onload="document.form.txt_assunto.focus()">
<form name="form" method="post" action='' onSubmit="return validar()">
<table border="0" width='90%' align="center" cellspacing ='1' cellpadding='1'>
    <tr class="atencao" align='center'>
        <td colspan="2">
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            Registrar Ouvidoria
        </td>
    </tr>
    <tr class="linhanormal">
        <td><b>Assunto:</b>
        <td>
            <input type='text' name='txt_assunto' title='Digite o Assunto' size='55' maxlength='50' class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td><b>Departamento:</b></td>
        <td>
            <select name='cmb_departamento' title="Selecione o Departamento" class="combo">
            <?
                    //Aqui eu sÛ listo os Depto. que est„o ativos  ...
                    $sql = "SELECT `id_departamento`, `departamento` 
                            FROM `departamentos` 
                            WHERE `ativo` = '1' ORDER BY `departamento` ";
                    echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class="linhanormal">
        <td><b>OcorrÍncia:</b></td>
        <td>
            <textarea name='txt_ocorrencia' cols='125' rows='16' maxlength='2000' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="button" name="cmd_limpar" value="Limpar" title="Limpar" class="botao" style="color:#ff9900;" onclick="redefinir('document.form', 'LIMPAR');document.form.txt_assunto.focus()">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
    <tr class="atencao" align="center">
        <td colspan="2">
            <div id='loading' style='display:none'>
                <img src="../../../css/little_loading.gif"> <font size="2" color="brown"><b>ENVIANDO E-MAIL ...</b></font>
            </div>
        </td>
    </tr>
</table>
</form>
</body>
</html>