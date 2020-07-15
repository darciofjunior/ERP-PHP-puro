<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../');
$mensagem[1] = "<font class='atencao'>JÁ FOI INCLUIDA HORA EXTRA NESSA DATA.</font>";
?>
<html>
<head>
<title>.:: Opções de Relação Funcionário Hora Extra ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function controlar_objetos() {
    if(document.form.opt_opcao[0].checked == true) {//Incluir ...
        //Layout de Habilitado ...
        document.form.txt_data_hora_extra.className = 'caixadetexto'
        //Habilita a Data de Hora Extra ...
        document.form.txt_data_hora_extra.disabled  = false
        //Layout de Desabilitado ...
        document.form.cmb_data_hora_extra.className = 'textdisabled'
        //Desabilita o Período ...
        document.form.cmb_data_hora_extra.disabled = true
        document.form.txt_data_hora_extra.focus()
    }else {//Alterar ...
        //Layout de Desabilitado
        document.form.txt_data_hora_extra.className = 'textdisabled'
        //Desabilita a Data de Hora Extra
        document.form.txt_data_hora_extra.disabled  = true
        //Layout de Habilitado ...
        document.form.cmb_data_hora_extra.className = 'combo'
        //Habilita o Período
        document.form.cmb_data_hora_extra.disabled = false
    }
}

function avancar() {
    if(document.form.opt_opcao[0].checked == true) {//Incluir ...
//Data de Hora Extra
        if(!data('form', 'txt_data_hora_extra', '4000', 'HORA EXTRA')) {
            return false
        }
//A Data de Inclusão da Hora Extra, não pode ser Inferior a Data Atual com menos 3 dias ...
        var data_atual_menos_tres_dias  = '<?=data::adicionar_data_hora(date('d/m/Y'), -3);?>'
        var data_hora_extra             = document.form.txt_data_hora_extra.value
        data_atual_menos_tres_dias      = data_atual_menos_tres_dias.substr(6,4) + data_atual_menos_tres_dias.substr(3,2) + data_atual_menos_tres_dias.substr(0,2)
        data_hora_extra                 = data_hora_extra.substr(6,4) + data_hora_extra.substr(3,2) + data_hora_extra.substr(0,2)
        data_atual_menos_tres_dias      = eval(data_atual_menos_tres_dias)
        data_hora_extra                 = eval(data_hora_extra)
//Comentado temporariamente ...
        /*
//O usuário não pode incluir uma Data de Hora Extra com uma Data que seje menor do que a Data Atual - 3 dias
        if(data_hora_extra < data_atual_menos_tres_dias) {
            alert('DATA DE HORA EXTRA INVÁLIDA !')
            document.form.txt_data_hora_extra.focus()
            document.form.txt_data_hora_extra.select()
            return false
        }*/
//Passo a Data em Formato americano ...
        var data_hora_extra = document.form.txt_data_hora_extra.value
        data_hora_extra     = data_hora_extra.substr(6,4) + '-' + data_hora_extra.substr(3,2) + '-' + data_hora_extra.substr(0,2)
        window.location     = 'relacao_func_hora_extra.php?incluir_data_hora_extra=1&cmb_data_hora_extra='+data_hora_extra
/*********************************************************************************/
    }else {//Alterar ...
//Data do Dia de Lançamento da Hora Extra ...
        if(!combo('form', 'cmb_data_hora_extra', '', 'SELECIONE A DATA DE HORA EXTRA !')) {
            return false
        }
        window.location = 'relacao_func_hora_extra.php?cmb_data_hora_extra='+document.form.cmb_data_hora_extra.value
    }
}
</Script>
</head>
<body onload='document.form.txt_data_hora_extra.focus()'>
<form name='form' method='post' action=''>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Opções de Relação Funcionário Hora Extra
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Incluir Nova Hora Extra' onclick='controlar_objetos()' id='label' checked>
            <label for='label'>
                    Incluir Nova Hora Extra - p/ 
            </label>
            <b>Data de Hora Extra: </b>
            <input type='text' name='txt_data_hora_extra' value='<?=date('d/m/Y');?>' title='Digite a Data de Hora Extra' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            &nbsp;<img src='../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onClick="nova_janela('../../../calendario/calendario.php?campo=txt_data_hora_extra&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calendário
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='2' title='Alterar Hora Extra já Existente' onclick='controlar_objetos()' id='label2'>
            <label for='label2'>Alterar Hora Extra já Existente</label> - 
            <b>Data de Hora Extra: </b>
            <select name='cmb_data_hora_extra' title='Selecione a Data' class='textdisabled' disabled>
            <?
                $sql = "SELECT DISTINCT(data_hora_extra), DATE_FORMAT(data_hora_extra, '%d/%m/%Y') 
                        FROM `funcionarios_horas_extras` 
                        ORDER BY data_hora_extra DESC ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_avancar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' onclick='avancar()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>