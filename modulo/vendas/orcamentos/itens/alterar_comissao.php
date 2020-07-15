<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../../');

if(!empty($_POST['id_orcamento_venda'])) {
    $sql = "UPDATE `orcamentos_vendas_itens` SET `comissao_perc` = '$_POST[txt_perc_comissao]', `comissao_new` = '$_POST[txt_perc_comissao]' WHERE `id_orcamento_venda` = '$_POST[id_orcamento_venda]' ";
    bancos::sql($sql);
?>
    <Script language = 'JavaScript'>
        parent.ativar_loading()
        parent.fechar_pop_up_div()
    </Script>
<?
    exit;
}
?>
<html>
<title>.:: Alterar % de Comissão ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//% da Comissão ...
    if(!texto('form', 'txt_perc_comissao', '1', '1234567890,.', '% DA COMISSÃO', '1')) {
        return false
    }
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA ALTERAR A COMISSÃO P/ ESSE REPRESENTANTE ?')
    if(resposta == true) {
        limpeza_moeda('form', 'txt_perc_comissao, ')
        return true
    }else {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_perc_comissao.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_orcamento_venda' value='<?=$_GET['id_orcamento_venda'];?>'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar % de Comissão
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='30%'>
            <b>% de Comissão:</b>
        </td>
        <td>
            <input type='text' name='txt_perc_comissao' title='Digite a % de Comissão' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_perc_comissao.focus()" style='color:#ff9900' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>