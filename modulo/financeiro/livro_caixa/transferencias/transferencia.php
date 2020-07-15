<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = '<font class="confirmacao">TRANSFERÊNCIA(S) DE CAIXA INCLUÍDA COM SUCESSO.</font>';

if(!empty($_POST['cmb_empresa'])) {
    $data_transferencia = data::datatodate($_POST['txt_data_transferencia'], '-');
    //Insere na Base de Dados a(s) Transferência(s) de Caixa(s) que foram realizadas pelo funcionário ...
    $sql = "INSERT INTO `transferencias_caixas` (`id_transferencia_caixa`, `id_funcionario`, `id_empresa`, `id_contacorrente_debito`, `id_contacorrente_credito`, `data_transferencia`, `valor_transferencia`) VALUES (NULL, '$_SESSION[id_funcionario]', '$_POST[cmb_empresa]', '$_POST[cmb_conta_corrente_debitar]', '$_POST[cmb_conta_corrente_creditar]', '$data_transferencia', '$_POST[txt_valor_transferencia]') ";
    bancos::sql($sql);
    $valor = 1;
}
?>
<html>
<head>
<title>.:: Transferência(s) de Caixa ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Empresa ...
    if(!combo('form', 'cmb_empresa', '', 'SELECIONE UMA EMPRESA !')) {
        return false
    }
//Conta Corrente à Debitar ...
    if(!combo('form', 'cmb_conta_corrente_debitar', '', 'SELECIONE UMA CONTA CORRENTE À DEBITAR !')) {
        return false
    }
//Conta Corrente à Creditar ...
    if(!combo('form', 'cmb_conta_corrente_creditar', '', 'SELECIONE UMA CONTA CORRENTE À CREDITAR !')) {
        return false
    }
//Data de Transferência ...
    if(!data('form', 'txt_data_transferencia', '4000', 'DATA DE TRANSFERÊNCIA')) {
        return false
    }
//Valor da Transferência ...
    if(!texto('form', 'txt_valor_transferencia', '1', '0123456789,.', 'VALOR DA TRANSFERÊNCIA', '2')) {
        return false
    }
    return limpeza_moeda('form', 'txt_valor_transferencia, ')
}

function carregar_contas_correntes() {
    ajax('carregar_contas_correntes.php', 'cmb_conta_corrente_debitar')
    ajax('carregar_contas_correntes.php', 'cmb_conta_corrente_creditar')
}
</Script>
<body onload='document.form.cmb_empresa.focus()'>
<form name='form' method='post' action='' onSubmit='return validar()'>
<table width='60%' cellspacing='1' cellpadding='1' border='0' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Transferência(s) de Caixa
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Empresa:</b>
        </td>
        <td>
            <select name='cmb_empresa' title='Selecione a Empresa' onchange='carregar_contas_correntes()' class='combo'>
            <?
                /*Não posso trazer a empresa 'Grupo' dentre essa relação de Empresas porque essa tela gerará um 
                registro de Transferência que será acrescentado em um documento que será apresentado para o Fisco ...*/
                $sql = "SELECT id_empresa, nomefantasia 
                        FROM `empresas` 
                        WHERE `id_empresa` <> '4' 
                        AND `ativo` = '1' ORDER BY nomefantasia ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Conta Corrente à Debitar:</b>
        </td>
        <td>
            <select name='cmb_conta_corrente_debitar' title='Selecione a Conta Corrente à Debitar' class='combo'>
                <option value=''>SELECIONE</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Conta Corrente à Creditar:</b>
        </td>
        <td>
            <select name='cmb_conta_corrente_creditar' title='Selecione a Conta Corrente à Creditar' class='combo'>
                <option value=''>SELECIONE</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Transferência:</b>
        </td>
        <td>
            <input type='text' name='txt_data_transferencia' title='Digite a Data de Transferência' onkeyup="verifica(this, 'data', '', '', event)" size='11' maxlength='10' class='caixadetexto'>
            &nbsp;<img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style="cursor:hand" onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_transferencia&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor da Transferência:</b>
        </td>
        <td>
            <input type='text' name='txt_valor_transferencia' title='Digite o Valor da Transferência' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='12' maxlength='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_conta_corrente.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</body>
</html>