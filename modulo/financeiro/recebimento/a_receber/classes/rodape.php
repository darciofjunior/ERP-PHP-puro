<?
require('../../../../../lib/segurancas.php');
session_start('funcionarios');

if($id_emp == 1) {//Albafer
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp == 2) {//Tool Master
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp == 4) {//Grupo
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}else if($id_emp == 0) {//Todas Empresas
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/todas_empresas/index.php';
}
segurancas::geral($endereco, '../../../../../');
?>
<html>
<head>
<title>.:: Rodapé de Itens de Contas à Receber ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function selecionar(valor) {
    var id_conta_receber = ''
//Serve para verificar quantos checkbox eu tenho selecionado no frame de cima
    var checkbox    = 0
    var elementos   = parent.itens.document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true && elementos[i].name == 'chkt_conta_receber[]') {
                id_conta_receber = id_conta_receber + elementos[i].value + ','
                checkbox ++
            }
        }
    }
    id_conta_receber = id_conta_receber.substr(0, id_conta_receber.length - 1)

    if (checkbox == 0 && valor != 4) {
        alert('SELECIONE UM ITEM !')
        return false
    }else {
        if(valor == 1) {
            if(checkbox > 1) {
                alert('SELECIONE SOMENTE UM ITEM !')
                return false
            }else {
                location = 'rodape.php?passo=1&id_conta_receber='+id_conta_receber+'&linhas=<?=$linhas;?>'
                return false
            }
        }else if(valor == 2) {
            if(checkbox > 1) {
                alert('SELECIONE SOMENTE UM ITEM !')
                return false
            }else {
                nova_janela('excluir.php?id_conta_receber='+id_conta_receber, 'POP', '', '', '', '', 520, 950, 'c', 'c')
            }
        }else if(valor == 3) {//Novo Controle de recebimento
            nova_janela('controle_recebimento.php?id_conta_receber='+id_conta_receber, 'POP', '', '', '', '', 580, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
        }else if(valor == 4) {
            nova_janela('outras_opcoes.php?id_emp=<?=$id_emp;?>', 'OUTRAS', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
        }else if(valor == 5) {
            if(checkbox > 1) {
                alert('SELECIONE SOMENTE UM ITEM !')
                return false
            }else {
                nova_janela('../classes/alterar_vencimentos.php?id_conta_receber='+id_conta_receber, 'POP', '', '', '', '', 550, 950, 'c', 'c', '', '', 's', 's', '', '', '')
            }
        }
    }
}
</Script>
</head>
<body>
<form name='form'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr>
        <td align='center'>
<?
/*Quando o usuário for o Ferreirinha, então não mostro todos os botões porque ele só vai ficar 
na parte de cobrança*/
	if($_SESSION['id_login'] == 59) {
?>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="parent.location = 'consultar_contas.php?itens=1&id_emp2=<?=$id_emp;?>'" class='botao'>
            <input type='button' name='cmd_detalhes' value='Detalhes' title='Detalhes' onclick="return selecionar(3)" class='botao'>
<?
//Qualquer outro usuário, eu mostro todos os botões normalmente ...
	}else {
?>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="parent.location = 'consultar_contas.php?itens=1&id_emp2=<?=$id_emp;?>'" class='botao'>
            <input type='button' name='cmd_incluir' value='Incluir' title='Incluir' onclick="nova_janela('opcoes_incluir.php', 'OPCOES_INCLUIR', '', '', '', '', 550, 950, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
<?
            if($linhas > 0) {
?>
                <input type='button' name='cmd_alterar' value='Alterar' title='Alterar' onclick="return selecionar(1)" class='botao'>
                <input type='button' name='cmd_excluir' value='Excluir' title='Excluir' onclick="return selecionar(2)" class='botao'>
                <input type='button' name='cmd_recebimento' value='Controle de Recebimento' title='Controle de Recebimento' class='botao' onclick="return selecionar(3)">
                <input type='button' name='cmd_outras_opcoes' value='Outras Opções' title='Outras Opções' onclick="selecionar(4)" class='botao'>
<?
            }
?>
            <input type='button' name='cmd_alterar_vencimentos' value='Alterar Vencimentos' title='Alterar Vencimentos' onclick="selecionar(5)" style='color:black' class='botao'>
<?
	}
?>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
//Ação = 0 - Significa que deseja alterar
//Ação = 1 - Significa que deseja excluir
if($passo == 1) {
?>
<head>
<title>.:: Rodapé de Itens de Contas à Receber ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
</head>
<?
//Verifica o status da conta para poder saber se pode alterar ou excluir ...
    $sql = "SELECT `status` 
            FROM `contas_receberes` 
            WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if($campos[0]['status'] == 2) {
?>
        <Script Language = 'JavaScript'>
            alert('ESTA CONTA NÃO PODE SER ALTERADA !')
        </Script>
<?
    }else {
?>
        <Script Language = 'JavaScript'>
            nova_janela('../../alterar.php?id_conta_receber=<?=$id_conta_receber;?>', 'ALTERAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')
        </Script>
<?
    }
}
?>