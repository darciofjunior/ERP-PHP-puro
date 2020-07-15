<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
session_start('funcionarios');
segurancas::geral($PHP_SELF, '../../../../');
?>
<html>
<head>
<title>.:: Relatório de Pedido ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function relatorio() {
    if(document.form.opt_opcao[0].checked == true) {
        window.location = 'pedidos_pendentes/pedidos_pendentes.php'
    }else if(document.form.opt_opcao[1].checked == true) {
        window.location = 'todos_pedidos/todos_pedidos.php'
    }
}
</Script>
</head>
<body>
<form name='form'>
<table width='40%' cellpadding="1" cellspacing="1" align="center">
    <tr class="linhacabecalho" align="center">
        <td>
            Relatório de Pedidos
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
                <input type="radio" checked name="opt_opcao" value="1" id="opt1">
                <label for="opt1">Pedidos em Aberto</label>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <input type="radio" name="opt_opcao" value="2" id="todos">
            <label for="todos">Todos os Pedidos</label>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td>
            <input type="button" name="cmd_avancar" value="&gt;&gt; Avançar &gt;&gt;" title="Avançar" onclick="relatorio()" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>