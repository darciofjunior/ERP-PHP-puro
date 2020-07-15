<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='confirmacao'>QUALIDADE DE A«O INCLUÕDO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>QUALIDADE DE A«O J¡ EXISTENTE.</font>";

if(!empty($_POST['txt_nome'])) {
    //Verifico se j· existe essa Qualidade de AÁo cadastrada na Base de Dados ...
    $sql = "SELECT id_qualidade_aco 
            FROM `qualidades_acos` 
            WHERE `nome` = '$_POST[txt_nome]' 
            AND `nome` <> '' 
            AND `ativo` = '1' ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//N„o existe ...
        $sql = "INSERT INTO `qualidades_acos` (`id_qualidade_aco`, `nome`, `densidade_material`, `valor_perc`) VALUES (NULL, '$_POST[txt_nome]', '$_POST[txt_densidade_material]', '$_POST[txt_valor_perc]') ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Existe ...
        $valor = 2;
    }
}
?>
<html>
<title>.:: Incluir Qualidade do AÁo ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Qualidade do AÁo ...
    if(!texto('form', 'txt_nome', '1', 'qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOP«LKJHGFDSAZXCVBNM<>.,;:/?]}()!@#$%®&*() _-+=ß™∫∞¡…Õ”⁄·ÈÌÛ˙‚ÍÓÙ˚¬ Œ‘€¿‡:;π≤≥£¢¨?/¸‹1234567890,.', 'QUALIDADE DO A«O', '1')) {
        return false
    }
//Densidade do Material ...
    if(!texto('form', 'txt_densidade_material', '1', '1234567890,.', 'DENSIDADE DO MATERIAL (G/CM≥ OU TON/M≥)', '2')) {
        return false
    }
//SeguranÁa se a Densidade do Material estiver preenchida ...
    if(document.form.txt_densidade_material.value != '') {
        var densidade_material = eval(strtofloat(document.form.txt_densidade_material.value))
        if(densidade_material < 7) {
            var resposta = confirm('A DENSIDADE DO MATERIAL EST¡ MENOR DO QUE 7 !!!\n\nDESEJA CONTINUAR ?')
            if(resposta == false) return false
        }
    }
    return limpeza_moeda('form', 'txt_densidade_material, txt_valor_perc, ')
}

function calcular_valor_perc() {
    if(document.form.txt_densidade_material.value != '') {
        var densidade_material              = eval(strtofloat(document.form.txt_densidade_material.value))
        document.form.txt_valor_perc.value  = (densidade_material / 7.86 - 1) * 100
        document.form.txt_valor_perc.value  = arred(document.form.txt_valor_perc.value, 2, 1)
    }else {
        document.form.txt_valor_perc.value  = ''
    }
}
</Script>
<body onload='document.form.txt_nome.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Qualidade do AÁo
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qualidade do AÁo:</b>
        </td>
        <td>
            <input type='text' name='txt_nome' title='Digite a Qualidade do AÁo' size='35' maxlength='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Densidade do Material (g/cm≥ ou ton/m≥):</b>
        </td>
        <td>
            <input type='text' name='txt_densidade_material' title='Densidade do Material (g/cm≥ ou ton/m≥)' onkeyup="verifica(this, 'moeda', '2', '', event);calcular_valor_perc()" size='20' maxlength='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor Percentual:</b>
        </td>
        <td>
            <input type='text' name='txt_valor_perc' title='Digite o Valor Percentual' onfocus='document.form.txt_densidade_material.focus()' size='20' maxlength='15' class='textdisabled'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_nome.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
    <tr class='atencao'>
        <td colspan='2'>
            <marquee loop='100' scrollamount='5'>
                <font size='2' color='blue'>
                    <b>TOMAR CUIDADO AO CRIAR UM TIPO DE A«O, PORQUE … PRECISO VERIFICAR SE ESTE ESTAR¡ COMPATÕVEL COM A DISCRIMINA«√O, POR FAVOR CONSULTAR EM M”DULO PRODU«√O, RELAT”RIO A«O VS DISCRIMINA«√O !</b>
                </font>
            </marquee>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color='red'>ObservaÁ„o:</font></b>
<pre>
* Densidade do AÁo serve como base e È 7,85 (g/cm≥ ou ton/m≥)
</pre>