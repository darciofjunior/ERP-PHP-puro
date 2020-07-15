<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/os/itens/consultar.php', '../../../../');

/**********************************************************************************************/
//Esse controle eu vou utilizar um pouco mais abaixo para controle dos Botões do Rodapé
//Se esta OS já estiver importada para Pedido, então eu travo os Botões do Rodapé
$sql = "SELECT id_pedido 
        FROM `oss` 
        WHERE `id_os` = '$_GET[id_os]' LIMIT 1 ";
$campos_os = bancos::sql($sql);
if($campos_os[0]['id_pedido'] != 0) {//Se a O.S. estiver importada p/ pedido, então ... 
    $disabled_opcao_1 = 'disabled';//Não posso mais atualizar os preços com o da Lista de Preço
    $disabled_opcao_2 = 'disabled';//Eu não posso excluir nenhum Item de O.S.
}
/**********************************************************************************************/
?>
<html>
<head>
<title>.:: Outras Opções ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    }
}

function avancar() {
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    if(document.form.opt_opcao[0].checked == true) {
        window.location = 'atualizar_precos.php?id_os=<?=$id_os;?>'
    }else if(document.form.opt_opcao[1].checked == true) {
        var mensagem = confirm('DESEJA REALMENTE EXCLUIR TODO(S) O(S) ITEM(NS) DA OS ?')
        if(mensagem == true) window.location = 'excluir_todos_itens_os.php?id_os=<?=$id_os;?>'
    }else {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }
}
</Script>
</head>
<body onunload="atualizar_abaixo()">
<form name="form" method="post">
<input type='hidden' name='nao_atualizar'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Outras Opções
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="1" title="Atualizar Preços" id='label' <?=$disabled_opcao_1;?> checked>
            <label for='label'>Atualizar Preços</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="2" title="Excluir Todos os Itens da OS" id='label2' <?=$disabled_opcao_2;?>>
            <label for='label2'>Excluir Todos os Itens da OS</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_avançar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' onclick="avancar()" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style="color:red" onclick="window.close()" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>