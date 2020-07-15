<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/genericas.php');
session_start('funcionarios');

if($id_emp2 == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp2 == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp2 == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../../');
?>
<html>
<head>
<title>.:: Estorno / Reembolso de Comissoes ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript'>
function enviar() {
    if(document.form.opt_opcao[0].checked == true) {
        window.location = 'incluir_nova_devolucao.php?id_emp2=<?=$id_emp2;?>'
    }else if(document.form.opt_opcao[1].checked == true) {
        window.location = 'consultar_devolucao.php?id_emp2=<?=$id_emp2;?>'
    }else if(document.form.opt_opcao[2].checked == true) {
        window.location = 'alterar_devolucao.php?id_emp2=<?=$id_emp2;?>'
    }else if(document.form.opt_opcao[3].checked == true) {
        window.location = 'excluir_devolucao.php?id_emp2=<?=$id_emp2;?>'
    }else {
        window.location = 'estornar_devolucao.php?id_emp2=<?=$id_emp2;?>'
    }
}
</Script>
</head>
<body>
<form name='form'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            Estorno / Reembolso de Comissoes
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='1' title='Incluir (Manual)' id='opt1' disabled>
            <label for='opt1'>Incluir (Manual)</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='2' title='Consultar (Manual)' id='opt2' checked>
            <label for='opt2'>Consultar (Manual)</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='3' title='Alterar (Manual)' id='opt3' disabled>
            <label for='opt3'>Alterar (Manual)</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='4' title='Excluir (Manual)' id='opt4' disabled>
            <label for='opt4'>Excluir (Manual)</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='5' title='Estornar Devolução (Automática)' id='opt5' disabled>
            <label for='opt5'>Estornar Devolução (Automática)</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            <input type='button' name='cmd_avancar' value='&gt;&gt; Avan&ccedil;ar &gt;&gt;' title='Avançar' onclick='enviar()' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>