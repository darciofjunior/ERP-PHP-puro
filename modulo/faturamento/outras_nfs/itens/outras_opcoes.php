<?
require('../../../../lib/segurancas.php');
require('../../../../lib/faturamentos.php');
segurancas::geral('/erp/albafer/modulo/faturamento/outras_nfs/itens/alterar_imprimir.php', '../../../../');

//Logo de cara já verifico se está Nota já foi importada p/ o Financeiro ...
$importado_financeiro = faturamentos::importado_financeiro_outras_nfs($_GET['id_nf_outra']);
if($importado_financeiro == 'S') {//Significa que a NF já está importada no Financeiro ...
    echo '<font color="red"><div align="center"><b>ESTÁ NF NÃO PODE SER + ALTERADA DEVIDO ESTAR IMPORTADA NO FINANCEIRO !</b></div></font>';
    exit;
}

$status = faturamentos::situacao_outras_nfs($_GET['id_nf_outra']);
//Função q verifica se a Nota está liberada_para_faturar, faturada, empacotada, despachada, cancelada
//caso sim, então o usuário não pode + incluir, alterar ou excluir nenhum item
if($status >= 1) {//Está liberado, então ñ posso excluir nada
    $disabled           = 'disabled';
    $checked            = '';
    $checked_imprimir   = 'checked';
}else {
    $checked            = 'checked';
    $disabled_imprimir  = 'disabled';
    $checked_imprimir   = '';
}
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
function avancar() {
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    if(document.form.opt_opcao.checked == true) {//Cancelar Nota ...
        window.location = 'cancelar_nota.php?id_nf_outra=<?=$_GET['id_nf_outra'];?>'
    }else {//Se não estiver nenhum opção selecionada, então ...
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }
}
    
//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    }
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form' method='post'>
<input type='hidden' name='nao_atualizar'>
<!--Controle de Tela-->
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Outras Opções
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Cancelar Nota Fiscal' id='label1' <?=$disabled;?>>
            <label for='label1'>Cancelar Nota Fiscal</label>
            <?=$mensagem;?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_avancar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' onclick='avancar()' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>