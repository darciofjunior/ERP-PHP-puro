<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/genericas.php');
session_start('funcionarios');//Não posso retirar essa Sessão porque a variável $id_emp2 está dentro da mesma ...

if($id_emp2 == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp2 == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp2 == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}else if($id_emp2 == 0) {//Todas Empresas
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/todas_empresas/index.php';
}
segurancas::geral($endereco, '../../../../../../');
?>
<html>
<head>
<title>.:: Opções de Bordero ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'Javascript'>
function enviar() {
    if(document.form.opt_opcao[0].checked == true) {
        window.location = 'incluir_novo_bordero.php?id_emp2=<?=$id_emp2;?>'
    }else if(document.form.opt_opcao[1].checked == true) {
        window.location = 'incluir_contas_em_bordero.php?id_emp2=<?=$id_emp2;?>'
    }else if(document.form.opt_opcao[2].checked == true) {
        window.location = 'estornar_contas_bordero.php?id_emp2=<?=$id_emp2;?>'    
    }else {
        window.location = 'visualizar_bordero.php?id_emp2=<?=$id_emp2;?>'
    }
}
</Script>
</head>
<body>
<form name='form'>
<table width='70%' border="0" cellspacing="1" cellpadding="1" align='center'>
    <tr class="linhacabecalho" align="center">
        <td>
            Op&ccedil;&otilde;es de Bordero
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <input type='radio' name='opt_opcao' value='1' title='Incluir Novo Bordero' id='opt1' checked>
            <label for='opt1'>Incluir Novo Bordero</label>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <input type="radio" name='opt_opcao' value='2' title='Incluir Conta(s) em Bordero (Já existente)' id='opt2'>
            <label for='opt2'>Incluir Conta(s) em Bordero (Já existente)</label>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <input type='radio' name='opt_opcao' value='3' title='Estornar Conta(s) de Bordero' id='opt3'>
            <label for='opt3'>Estornar Conta(s) de Bordero</label>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <input type='radio' name='opt_opcao' value='4' title='Visualizar Bordero(s)' id='opt4'>
            <label for='opt4'>Visualizar Bordero(s)</label>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td>
            <input type="button" name="cmd_avancar" value="&gt;&gt; Avan&ccedil;ar &gt;&gt;" title="Avançar" onclick="enviar()" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>