<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/genericas.php');
session_start('funcionarios');
session_register('id_emp2');

if($id_emp2 == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/albafer/index.php';
}else if($id_emp2 == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/tool_master/index.php';
}else if($id_emp2 == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../');
?>
<html>
<head>
<title>.:: Opções de Cheque ::.</title>
<link rel='stylesheet' type='text/css' href = '../../../../../css/layout.css'>
<Script Language = 'JavaScript' src = '../../../../../js/sessao.js'></Script>
<Script Language = 'Javascript'>
function avancar() {
    if(document.form.opt_item[0].checked == true) {
        window.location = '../classes/manipular/consultar.php'
    }else if(document.form.opt_item[1].checked == true) {
        window.location = '../classes/manipular/substituir.php'
    }else if(document.form.opt_item[2].checked == true) {
        window.location = '../classes/manipular/desvincular.php'
    }else if(document.form.opt_item[3].checked == true) {
        window.location = '../classes/manipular/cancelar.php'
    }else if(document.form.opt_item[4].checked == true) {
        window.location = '../classes/manipular/compensar.php'
    }else {
        window.location = '../classes/manipular/incluir.php'
    }
}
</Script>
</head>
<body>
<form name='form'>
<table width='60%' cellpadding='1' cellspacing='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            Opções de Cheque 
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
            <input type='radio' name='opt_item' value='2' title='Substituir Cheque' id='opt2'>
            <label for='opt2'>Substituir Cheque</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='3' title='Desvincular Cheque' id='opt3'>
            <label for='opt3'>Desvincular Cheque</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='4' title='Cancelar Cheque' id='opt4'>
            <label for='opt4'>Cancelar Cheque</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='5' title='Compensar Cheque' id='opt5'>
            <label for='opt5'>Compensar Cheque</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='6' title='Incluir Cheque' id='opt6'>
            <label for='opt6'>Incluir Cheque</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            <input type='button' name='cmd_avancar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' onclick='avancar()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>