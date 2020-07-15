<?
require('../../../lib/data.php');
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/cascates.php');
session_start('funcionarios');
segurancas::geral('/erp/albafer/modulo/rh/funcionario/incluir.php', '../../../');
?>
<html>
<head>
<title>.:: Incluir Funcionários ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function gerenciar_telas(tela) {
	if(tela == 1) {//Dados Pessoais ...
		window.parent.corpo.tela.location = 'incluir_dados_pessoais.php'
	}else if(tela == 2) {//Dados Profissionais ...
		alert('SALVE ANTES OS DADOS PESSOAIS !')
		window.parent.corpo.tela.document.form.txt_nome.focus()
	}else if(tela == 3) {//Acompanhamento ...
		alert('SALVE ANTES OS DADOS PESSOAIS !')
		window.parent.corpo.tela.document.form.txt_nome.focus()
	}
}
</Script>
</head>
<body onload='gerenciar_telas(1)'>
<form name='form' method='post'>
<table width='55%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align="center">
        <td id="aba0" onclick="javascript:gerenciar_telas(1);aba(this, 3, 650)" class="aba_ativa" height="10">
            Dados Pessoais
        </td>
        <td id="aba1" onclick="javascript:gerenciar_telas(2)" class="aba_inativa">
            Dados Profissionais
        </td>
        <td id="aba2" onclick="javascript:gerenciar_telas(3)" class="aba_inativa">
            Acompanhamento
        </td>
    </tr>
</table>
<table border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align="center">
        <td colspan='2'>
            <iframe name="tela" marginwidth="0" marginheight="0" frameborder="0" height="1050" width="1000"></iframe>
        </td>
    </tr>
</table>
</form>
</body>
</html>