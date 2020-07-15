<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/menu/menu.php');
require('../../../../../../lib/genericas.php');
session_start('funcionarios');
session_register('id_emp2');

if($id_emp2 == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/cheque_cliente/classes/manipular/opcoes.php?id_emp2=1';
}else if($id_emp2 == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/cheque_cliente/classes/manipular/opcoes.php?id_emp2=2';
}else if($id_emp2 == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/cheque_cliente/classes/manipular/opcoes.php?id_emp2=4';
}
segurancas::geral($endereco, '../../../../../../');
?>
<html>
<head>
<title>.:: Op��es de Cheque de Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'Javascript'>
function avancar() {
    if(document.form.opt_item[0].checked == true) {
        window.location = '../../classes/manipular/consultar.php'
    }else if(document.form.opt_item[1].checked == true) {
        window.location = '../../classes/manipular/compensar.php'
    }else if(document.form.opt_item[2].checked == true) {
        window.location = '../../classes/manipular/efetuar_devolucao.php'
    }else if(document.form.opt_item[3].checked == true) {
        window.location = '../../classes/manipular/substituir.php'
    }
}
</Script>
</head>
<body>
<form name='form'>
<table width='60%' cellpadding='1' cellspacing='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            Op��es de Cheque de Cliente 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='1' title='Consultar Cheque' id='opt1' checked>
            <label for='opt1'>Consultar Cheque</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='2' title='Compensar Cheque' id='opt2'>
            <label for='opt2'>Compensar Cheque</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='3' title='Efetuar Devolu��o de Cheque' id='opt3'>
            <label for='opt3'>Efetuar Devolu��o de Cheque</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='4' title='Substituir Cheque' id='opt4' disabled>
            <label for='opt4'>Substituir Cheque <b>(Desabilitado Temporariamente)</b></label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            <input type='button' name='cmd_avancar' value='&gt;&gt; Avan�ar &gt;&gt;' title='Avan�ar' onclick='avancar()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>