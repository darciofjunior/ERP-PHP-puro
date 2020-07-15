<?
require('../../../../lib/segurancas.php');

switch($opcao) {
    case 1://Significa que veio do Menu Abertas / Liberadas ...
    case 2://Significa que veio do Menu de Liberadas / Faturadas ...
    case 3://Significa que veio do Menu de Faturadas / Empacotadas / Despachadas ...
        segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
    break;
    case 4://Significa que veio do Menu de Devolução 
        segurancas::geral('/erp/albafer/modulo/faturamento/nota_saida/itens/devolucao.php', '../../../../');
    break;
    default://Significa que veio do Menu de Devolução ...
        segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
    break;
}

$mensagem[1] = "<font class='confirmacao'>AJUSTE(S) ALTERADO(S) COM SUCESSO.</font>";

if(!empty($_POST['id_nf'])) {
    $sql = "UPDATE nfs SET `ajuste_valor_icms` = '$_POST[txt_ajuste_valor_icms]', `ajuste_base_calc_icms_st` = '$_POST[txt_ajuste_base_calc_icms_st]', `ajuste_valor_icms_st` = '$_POST[txt_ajuste_valor_icms_st]' WHERE `id_nf` = '$_POST[id_nf]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_nf      = $_POST['id_nf'];
    $seguranca  = $_POST['seguranca'];
}else {
    $id_nf      = $_GET['id_nf'];
    $seguranca  = $_GET['seguranca'];
}

//Aqui eu busco alguns dados da NF passada por parâmetro ...	
$sql = "SELECT ajuste_valor_icms, ajuste_base_calc_icms_st, ajuste_valor_icms_st 
        FROM `nfs` 
        WHERE `id_nf` = '$id_nf' LIMIT 1 ";
$campos_nf = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Ajustes de NF ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../../css/layout.css'>
<Script Language = 'Javascript' src = '../../../../js/geral.js'></Script>
<Script Language = 'Javascript' src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Ajuste de Valor de ICMS ...
    if(document.form.txt_ajuste_valor_icms.value != '') {
        if(!texto('form', 'txt_ajuste_valor_icms', '1', '-0123456789,.', 'AJUSTE DE VALOR DE ICMS', '2')) {
            return false
        }
    }
//Ajuste de Base de Cálculo ICMS ST ...
    if(document.form.txt_ajuste_base_calc_icms_st.value != '') {
        if(!texto('form', 'txt_ajuste_base_calc_icms_st', '1', '-0123456789,.', 'AJUSTE DE BASE DE CÁLCULO ICMS ST', '2')) {
            return false
        }
    }
//Ajuste de Valor de ICMS ST ...
    if(document.form.txt_ajuste_valor_icms_st.value != '') {
        if(!texto('form', 'txt_ajuste_valor_icms_st', '1', '-0123456789,.', 'AJUSTE DE VALOR DE ICMS ST', '2')) {
            return false
        }
    }
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
    return limpeza_moeda('form', 'txt_ajuste_valor_icms, txt_ajuste_base_calc_icms_st, txt_ajuste_valor_icms_st, ')
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
<body onload='document.form.txt_ajuste_valor_icms.focus()' onunload='atualizar_abaixo()' topmargin='20'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='seguranca' value='<?=$seguranca;?>'>
<input type='hidden' name='id_nf' value='<?=$id_nf;?>'>
<!--******************************************************-->
<table width='70%' cellpadding='1' cellspacing='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Ajustes de NF
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor de ICMS:
        </td>
        <td>
            <input type='text' name='txt_ajuste_valor_icms' value='<?=number_format($campos_nf[0]['ajuste_valor_icms'], 2, ',', '.');?>' title='Digite o Ajuste de NF' onkeyup="verifica(this, 'moeda_especial', '2', '1', event)" size='15' maxlength='8' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Base de Cálc. ICMS ST:
        </td>
        <td>
            <input type='text' name='txt_ajuste_base_calc_icms_st' value='<?=number_format($campos_nf[0]['ajuste_base_calc_icms_st'], 2, ',', '.');?>' title='Digite o Ajuste de NF' onkeyup="verifica(this, 'moeda_especial', '2', '1', event)" size='15' maxlength='8' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor de ICMS ST:
        </td>
        <td>
            <input type='text' name='txt_ajuste_valor_icms_st' value='<?=number_format($campos_nf[0]['ajuste_valor_icms_st'], 2, ',', '.');?>' title='Digite o Ajuste de NF' onkeyup="verifica(this, 'moeda_especial', '2', '1', event)" size='15' maxlength='8' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_ajuste_valor_icms.focus()" style="color:#ff9900;" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="fechar(window)" style="color:red" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>