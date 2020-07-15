<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/vendas/ocs/itens/consultar.php', '../../../../');
?>
<html>
<head>
<title>.:: Outras Opções ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function avancar() {
    if(document.form.opt_opcao[0].checked == true) {//Excluir Itens ...
        window.location = 'excluir_todos_itens_ocs.php?id_oc=<?=$_GET['id_oc'];?>'
    }else if(document.form.opt_opcao[1].checked == true) {//Incluir Atendimento Diario ...
        window.location = '../../atendimento_diario/incluir.php?id_oc=<?=$_GET['id_oc'];?>'			
    }else {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho'>
        <td colspan='2' align='center'>
            Outras Opções
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value="1" title="Excluir Todos os Itens de OC" id='label' checked>
            <label for='label'>Excluir Todos os Itens de OC</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value="2" title="Incluir Atendimento Diário" id='label2'>
            <label for='label2'>Incluir Atendimento Diário</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_avançar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' onclick="avancar()" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>