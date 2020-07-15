<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral('/erp/albafer/modulo/sistema/patrimonios/opcoes.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>PATRIM‘NIO INCLUIDO COM SUCESSO.</font>";

//Depois que o usu·rio submeteu esse patrimonio...
if(!empty($_POST['cmb_tipo_patrimonio'])) {        
    $sql = "INSERT INTO `patrimonios` (`id_patrimonio`, `id_funcionario_registrou`, `id_departamento`, 
            `id_funcionario`, `tipo_patrimonio`, `marca_modelo`, `numero_serie`, `sistema_operacional`, 
            `processador`, `memoria`, `hd`, `valor`, `observacao`, `data_sys`) 
            VALUES 
            (NULL, '$_SESSION[id_funcionario]', '$_POST[cmb_departamento]', '$_POST[cmb_funcionario]', 
            '$_POST[cmb_tipo_patrimonio]', '$_POST[txt_marca_modelo]', '$_POST[txt_numero_serie]','$_POST[txt_so]', 
            '$_POST[txt_processador]', '$_POST[txt_memoria]', '$_POST[txt_hd]', '$_POST[txt_valor]', 
            '$_POST[txt_observacao]', '".date('Y-m-d H:i:s')."') ";
    bancos::sql($sql);
    $valor = 1;
}
?>
<html>
<title>.:: Incluir PatrimÙnio(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Departamento ...
    if(!combo('form', 'cmb_departamento', '', 'SELECIONE UM DEPARTAMENTO !')) {
        return false
    }
//Tipo de PatrimÙnio ...
    if(!combo('form', 'cmb_tipo_patrimonio', '', 'SELECIONE UM TIPO DE PATRIM‘NIO !')) {
        return false
    }
//Marca / Modelo ...
    if(!texto('form', 'txt_marca_modelo', '3', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ„ı√’·ÈÌÛ˙¡…Õ”⁄Á«‚ÍÓÙ˚¬ Œ‘€ 1234567890.,-_()[]{},.:;*+/', 'MARCA / MODELO', '1')) {
        return false
    }
//Valor ...
    if(document.form.txt_valor.value != '') {
        if(!texto('form', 'txt_valor', '1', '0123456789,.', 'VALOR', '2')) {
            return false
        }
    }
    limpeza_moeda('form', 'txt_valor, ')
}

function carregar_funcionarios() {
    ajax('carregar_funcionarios.php', 'cmb_funcionario')
}
</Script>
<body onload='document.form.cmb_departamento.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='60%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir PatrimÙnio(s)
        </td>
    </tr>    
    <tr class='linhanormal'>
        <td>
            <b>Departamento:</b>
        </td>
        <td>
            <select name='cmb_departamento' title='Selecione o Departamento' onchange='carregar_funcionarios()' class='combo'>
            <?   
                $sql = "SELECT `id_departamento`, `departamento` 
                        FROM `departamentos`                         
                        WHERE `ativo` = '1' ORDER BY `departamento` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Para Funcion·rio:
        </td>
        <td>
            <select name='cmb_funcionario' title='Selecione o Para Funcion·rio' class='combo'>
                <option value=''>SELECIONE</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de PatrimÙnio:</b>
        </td>
        <td>
            <select name='cmb_tipo_patrimonio' title='Selecione o Tipo de PatrimÙnio' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='CELULAR'>CELULAR</option>
                <option value='COMPUTADOR'>COMPUTADOR</option>
                <option value='IMPRESSORA'>IMPRESSORA</option>
                <option value='INSTRUMENTO DE MEDI«√O'>INSTRUMENTO DE MEDI«√O</option>
                <option value='MONITOR'>MONITOR</option>
                <option value='TELEFONE'>TELEFONE</option>
                <option value='UMIDIFICADOR'>UMIDIFICADOR</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Marca / Modelo:</b>
        </td>
        <td>
            <input type='text' name='txt_marca_modelo' title='Digite a Marca / Modelo' size='60' maxlength='65' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            N˙mero de SÈrie:
        </td>
        <td>
            <input type='text' name='txt_numero_serie' title='Digite o N˙mero de SÈrie' size='40' maxlength='35' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Sistema Operacional:
        </td>
        <td>
            <input type='text' name='txt_so' title='Digite o Sistema Operacional' size='35' maxlength='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Processador:
        </td>
        <td>
            <input type='text' name='txt_processador' title='Digite o processador' size='65' maxlength='60' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            MemÛria:
        </td>
        <td>
            <input type='text' name='txt_memoria' title='Digite a MemÛria' size='20' maxlength='20' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            HD:
        </td>
        <td>
            <input type='text' name='txt_hd' title='Digite o HD' size='17' maxlength='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor:
        </td>
        <td>
            <input type='text' name='txt_valor' title='Digite o Valor' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='12' maxlength='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            ObservaÁ„o:
        </td>
        <td>
            <textarea name='txt_observacao' title='Digite a ObservaÁ„o' maxlength='255' cols='64' rows='4' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'opcoes.php'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.cmb_departamento.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>