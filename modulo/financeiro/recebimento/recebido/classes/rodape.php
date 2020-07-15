<?
require('../../../../../lib/segurancas.php');
session_start('funcionarios');

if($id_emp == 1) {//Albafer
	$endereco = '/erp/albafer/modulo/financeiro/recebimento/recebido/albafer/index.php';
}else if($id_emp == 2) {//Tool Master
	$endereco = '/erp/albafer/modulo/financeiro/recebimento/recebido/tool_master/index.php';
}else if($id_emp == 4) {//Grupo
	$endereco = '/erp/albafer/modulo/financeiro/recebimento/recebido/grupo/index.php';
}else if($id_emp == 0) {//Todas Empresas
	$endereco = '/erp/albafer/modulo/financeiro/recebimento/recebido/todas_empresas/index.php';
}
segurancas::geral($endereco, '../../../../../');
?>
<html>
<head>
<title>.:: Consultar Conta(s) Recebida(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'Javascript'>
function detalhes() {
    var elementos = parent.itens.document.form, option = 0
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].checked == true && elementos[i].type == 'radio') option ++
    }

    if(option == 0) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        for (var i = 0; i < elementos.length; i++) {
            if(elementos[i].checked == true && elementos[i].type == 'radio') {
                var id_conta_receber = elementos[i].value
                break;
            }
        }
        //Para que a tela seja aberta como Pop-UP ...
        nova_janela('../../alterar.php?id_conta_receber='+id_conta_receber+'&pop_up=1', 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}
</Script>
</head>
<body>
<form name='form'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align="center">
    <tr align='center'>
        <td>
<?
	if($existe == 1) {//Serve para saber que existe pelo menos um registro, acho que vem por parâmetro ???
?>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="parent.location = 'index.php?itens=1&id_emp2=<?=$id_emp;?>'" class='botao'>
            <input type='button' name='cmd_detalhes' value='Detalhes' title='Detalhes' onclick='return detalhes()' class='botao'>
<?
	}
?>
        </td>
    </tr>
</table>
</form>
</body>
</html>