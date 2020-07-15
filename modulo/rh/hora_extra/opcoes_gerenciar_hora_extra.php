<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../');
?>
<html>
<head>
<title>.:: Opções de Hora Extra ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function controlar_objetos() {
    if(document.form.opt_opcao[0].checked == true) {//Incluir ...
//Limpa os Objetos
        document.form.cmb_periodo.value = ''
//Layout de Habilitado ...
        document.form.txt_data_final.className      = 'caixadetexto'
        document.form.txt_data_pagamento.className  = 'caixadetexto'
//Habilita os Objetos ...
        document.form.txt_data_final.disabled       = false
        document.form.txt_data_pagamento.disabled   = false
//Layout de Desabilitado ...
        document.form.cmb_periodo.className         = 'textdisabled'
        document.form.cmd_alterar_periodo.className = 'textdisabled'
//Desabilita o Período e o botão alterar Data
        document.form.cmb_periodo.disabled          = true
        document.form.cmd_alterar_periodo.disabled  = true
        document.form.txt_data_final.focus()
    }else {//Alterar ...
//Limpa os Objetos
        document.form.txt_data_final.value          = ''
        document.form.txt_data_pagamento.value      = ''
//Layout de Desabilitado ...
        document.form.txt_data_final.className      = 'textdisabled'
        document.form.txt_data_pagamento.className  = 'textdisabled'
//Desabilita os Objetos ...
        document.form.txt_data_final.disabled       = true
        document.form.txt_data_pagamento.disabled   = true
//Layout de Habilitado ...
        document.form.cmb_periodo.className         = 'caixadetexto'
        document.form.cmd_alterar_periodo.className = 'botao'
//Habilita o Período e o botão Alterar Data
        document.form.cmb_periodo.disabled          = false
        document.form.cmd_alterar_periodo.disabled  = false
        document.form.cmb_periodo.focus()
    }
}

function avancar() {
    if(document.form.opt_opcao[0].checked == true) {//Incluir ...
//Data Final
        if(!data('form', 'txt_data_final', '4000', 'FIM')) {
            return false
        }
//Data de Pagamento
        if(!data('form', 'txt_data_pagamento', '4000', 'PAGAMENTO')) {
            return false
        }
/*****************************Seguranças com as Datas*****************************/
//Comparações entre as Datas de Prazo ...
        var data_inicial    = document.form.txt_data_inicial.value
        var data_final      = document.form.txt_data_final.value

        data_inicial        = data_inicial.substr(6,4)+data_inicial.substr(3,2)+data_inicial.substr(0,2)
        data_final          = data_final.substr(6,4)+data_final.substr(3,2)+data_final.substr(0,2)

        data_inicial        = eval(data_inicial)
        data_final          = eval(data_final)

        if(data_final < data_inicial) {
            alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
            document.form.txt_data_final.focus()
            document.form.txt_data_final.select()
            return false
        }
//Comparações com a Data de Pagamento ...
        var data_pagamento  = document.form.txt_data_pagamento.value
        data_pagamento      = data_pagamento.substr(6,4)+data_pagamento.substr(3,2)+data_pagamento.substr(0,2)
        data_pagamento      = eval(data_pagamento)
//1) Comparação da Data de Pagamento com a Data Final ...
        if(data_pagamento < data_final) {
            alert('DATA DE PAGAMENTO INVÁLIDA !!!\n DATA DE PAGAMENTO MENOR DO QUE A DATA FINAL !')
            document.form.txt_data_pagamento.focus()
            document.form.txt_data_pagamento.select()
            return false
        }
//2) Comparação da Data de Pagamento com a Data de Emissão ...
        var data_atual      = eval('<?=date("Ymd");?>')
        if(data_pagamento < data_atual) {
            alert('DATA DE PAGAMENTO INVÁLIDA !!!\n DATA DE PAGAMENTO MENOR DO QUE A DATA ATUAL !')
            document.form.txt_data_pagamento.focus()
            document.form.txt_data_pagamento.select()
            return false
        }
//Desabilito p/ poder gravar no Banco de Dados ...
        document.form.txt_data_inicial.disabled = false

        var txt_data_inicial= document.form.txt_data_inicial.value
        var txt_data_final  = document.form.txt_data_final.value
        var txt_data_pagamento = document.form.txt_data_pagamento.value
        window.location     = 'gerenciar.php?txt_data_inicial='+txt_data_inicial+'&txt_data_final='+txt_data_final+'&txt_data_pagamento='+txt_data_pagamento
/*********************************************************************************/
    }else {//Alterar ...
//Período ...
        if(!combo('form', 'cmb_periodo', '', 'SELECIONE O PERÍODO !')) {
            return false
        }
//Nessa combo eu faço a separação das Datas, uma referente a Data Inicial, Data Final e Data de Pagamento
        var separador           = 0
        var txt_data_inicial    = ''
        var txt_data_final      = ''
        var txt_data_pagamento  = ''

        for(i = 0; i < document.form.cmb_periodo.value.length; i++) {
            if(document.form.cmb_periodo.value.charAt(i) == '|') {//Começará tratar a Data Final
                separador = 1
            }else if(document.form.cmb_periodo.value.charAt(i) == '-') {//Data de Pagto ...
                separador = 2
            }
//Separação entre as Datas ...
            if(separador == 0) {//Data Inicial ...
                txt_data_inicial+= document.form.cmb_periodo.value.charAt(i)
            }else if(separador == 1) {//Data Final ...
                if(document.form.cmb_periodo.value.charAt(i) != '|') {
                    txt_data_final+= document.form.cmb_periodo.value.charAt(i)
                }
            }else if(separador == 2) {//Data de Pagto ...
                if(document.form.cmb_periodo.value.charAt(i) != '-') {
                    txt_data_pagamento+= document.form.cmb_periodo.value.charAt(i)
                }
            }
        }
        window.location = 'gerenciar.php?txt_data_inicial='+txt_data_inicial+'&txt_data_final='+txt_data_final+'&txt_data_pagamento='+txt_data_pagamento
    }
}

function alterar_periodo() {
//Período ...
    if(!combo('form', 'cmb_periodo', '', 'SELECIONE O PERÍODO !')) {
        return false
    }
//Nessa combo eu faço a separação das Datas, uma referente a Data Inicial, Data Final e Data de Pagamento
    var separador           = 0
    var txt_data_inicial    = ''
    var txt_data_final      = ''
    var txt_data_pagamento  = ''

    for(i = 0; i < document.form.cmb_periodo.value.length; i++) {
        if(document.form.cmb_periodo.value.charAt(i) == '|') {//Começará tratar a Data Final
            separador = 1
        }else if(document.form.cmb_periodo.value.charAt(i) == '-') {//Data de Pagto ...
            separador = 2
        }
//Separação entre as Datas ...
        if(separador == 0) {//Data Inicial ...
            txt_data_inicial+= document.form.cmb_periodo.value.charAt(i)
        }else if(separador == 1) {//Data Final ...
            if(document.form.cmb_periodo.value.charAt(i) != '|') {
                txt_data_final+= document.form.cmb_periodo.value.charAt(i)
            }
        }else if(separador == 2) {//Data de Pagto ...
            if(document.form.cmb_periodo.value.charAt(i) != '-') {
                txt_data_pagamento+= document.form.cmb_periodo.value.charAt(i)
            }
        }
    }
    nova_janela('alterar_periodo.php?txt_data_inicial='+txt_data_inicial+'&txt_data_final='+txt_data_final+'&txt_data_pagamento='+txt_data_pagamento, 'ALTERAR_DATAS', '', '', '', '', 200, 700, 'c', 'c', '', '', 's', 's', '', '', '')
}
</Script>
</head>
<body onload='document.form.txt_data_final.focus()'>
<form name='form' method='post' action=''>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            Opções de Hora Extra
        </td>
    </tr>
    <tr class='linhanormal'>
        <?
/*Aqui eu busco a última Data Final de lançamento de Hora Extra p/ sugerir mais abaixo a próxima 
Data de lançamento de Hora Extra ...*/
            $sql = "SELECT DATE_FORMAT(data_final, '%d/%m/%Y') AS data_final_formatada 
                    FROM `funcionarios_hes_rel` 
                    ORDER BY `data_final` DESC LIMIT 1 ";
            $campos         = bancos::sql($sql);
            $data_inicial   = data::adicionar_data_hora($campos[0]['data_final_formatada'], 1);
        ?>
        <td>
            <input type='radio' name='opt_opcao' value="1" title="Incluir Período" onclick="controlar_objetos()" id='label' checked>
            <label for='label'>Incluir Período</label> - 
            <b>Data Inicial: </b>
            <input type='text' name='txt_data_inicial' value='<?=$data_inicial;?>' title="Digite a Data Inicial" onkeyup="verifica(this, 'data', '', '', event)" size='11' maxlength='10' class='textdisabled' disabled>
            <img src="../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="if(document.form.txt_data_inicial.disabled == false) {javascript:nova_janela('../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')}">
            &nbsp;
            <b>Data Final: </b>
            <input type='text' name='txt_data_final' title='Digite a Data Final' onkeyup="verifica(this, 'data', '', '', event)" size='11' maxlength='10' class='caixadetexto'>
            <img src="../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;
            <b>Data do Pagamento: </b>
            <input type='text' name='txt_data_pagamento' title='Digite a Data de Pagamento' onkeyup="verifica(this, 'data', '', '', event)" size='11' maxlength='10' class='caixadetexto'>
            <img src="../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_pagamento&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='2' title='Alterar Período já existente' onclick='controlar_objetos()' id='label2'>
            <label for='label2'>Alterar Período já existente</label> - 
            <b>Período: </b>
            <select name='cmb_periodo' title='Selecione o Período' class='textdisabled' disabled>
            <?
                $sql = "SELECT DISTINCT(CONCAT(DATE_FORMAT(data_inicial, '%d/%m/%Y'), '|', DATE_FORMAT(data_final, '%d/%m/%Y'), '-', DATE_FORMAT(data_pagamento, '%d/%m/%Y'))), CONCAT(DATE_FORMAT(data_inicial, '%d/%m/%Y'), ' à ', DATE_FORMAT(data_final, '%d/%m/%Y')) AS periodo 
                        FROM `funcionarios_hes_rel` 
                        ORDER BY `id_funcionario_he_rel` DESC ";
                echo combos::combo($sql);
            ?>
            </select>
            &nbsp;
            <input type='button' name='cmd_alterar_periodo' value='Alterar Período' title='Alterar Período' onclick='alterar_periodo()' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_avançar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' onclick='avancar()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>