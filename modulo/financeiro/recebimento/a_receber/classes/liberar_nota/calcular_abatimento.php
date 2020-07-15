<?
require('../../../../../../lib/segurancas.php');
session_start('funcionarios');

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../../');
?>
<html>
<head>
<title>.:: Cálculo Abatimento ::.</title>
<meta http-equiv ='Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv ='pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>  
function calcular_abatimento() {
//Taxa ao Mês ...
    if(!texto('form', 'txt_taxa_am', '2', '0123456789,.', 'TAXA AO MÊS', '1')) {
        return false
    }
//Data de Pagamento ...
    if(!data('form', 'txt_data_pagamento', '4000', 'PAGAMENTO')) {
        return false
    }
/***************Segurança com a Data de Pagamento***************/
//A data de Pagamento não pode ser menor do que a Data Atual ...
    var data_pagamento      = document.form.txt_data_pagamento.value
    data_pagamento          = data_pagamento.substr(6, 4) + data_pagamento.substr(3, 2) + data_pagamento.substr(0, 2)
    data_atual              = eval('<?=date('Ymd')?>')
    if(data_pagamento < data_atual) {
        alert('DATA DE PAGAMENTO INVÁLIDA !!!\n\nDATA DE PAGAMENTO MENOR QUE A DATA ATUAL !')
        document.form.txt_data_pagamento.focus()
        document.form.txt_data_pagamento.select()
        return false
    }
/***************************************************************/
    var dias_corridos       = diferenca_datas(document.form.txt_data_pagamento.value, '<?=$_GET['data_vencimento'];?>')
    var valor_reajustado    = '<?=str_replace(',', '.', $_GET['valor_reajustado']);?>'
    var valor_abatimento    = dias_corridos / 30 * document.form.txt_taxa_am.value.replace(',', '.') / 100 * valor_reajustado
    opener.document.form.txt_valor_abatimento.value = arred(String(valor_abatimento), 2, 1)
    opener.calcular(1)
    window.close()
}
</Script>
</head>
<body onload='document.form.txt_taxa_am.focus()'>
<form name="form" method='post'>
<table width='80%' border="0" cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Cálculo Abatimento p/ Pagamento Antecipado
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Vencimento Alterada:
        </td>
        <td>
            <input type='text' name="txt_data_vencimento_alterada" value="<?=$_GET['data_vencimento_alterada'];?>" title="Data de Vencimento Alterada" size="20" maxlength="10" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor Reajustado R$:
        </td>
        <td>
            <input type='text' name="txt_valor" value="<?=$_GET['valor_reajustado'];?>" title="Valor Reajustado R$" size="20" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Taxa A.M:</b>
        </td>
        <td>
            <input type='text' name="txt_taxa_am" title="Taxa A.M" size="20" onkeyup="verifica(this, 'moeda_especial', '1', '', event)" class='caixadetexto'>
        </td>
    </tr>   
    <tr class='linhanormal'>
        <td>
            <b>Data Pagamento:</b>
        </td>   
        <td>
            <input type='text' name="txt_data_pagamento" onkeyup="verifica(this, 'data', '', '', event)" size="11" maxlength="10" class='caixadetexto'>
            <img src = "../../../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../../../../calendario/calendario.php?campo=txt_data_pagamento&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>  
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_calcular' value='Calcular' title='Calcular' style="color:green" onclick='calcular_abatimento()' class='botao'>
            <input type='button' name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="fechar(window)" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>