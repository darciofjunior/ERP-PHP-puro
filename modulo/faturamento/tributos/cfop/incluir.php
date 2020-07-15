<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../classes/array_sistema/array_sistema.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='confirmacao'>CFOP INCLUIDO COM SUCESSO.</font>";

if(!empty($_POST['txt_cfop'])) {
//CFOP
    $achou_ponto = 0;
    $num_cfop = '';
    if(!empty($_POST['txt_cfop'])) {//Caixa Preenchida
        for($i = 0; $i < strlen($_POST['txt_cfop']); $i++) {
            if(substr($_POST['txt_cfop'], $i, 1) == '.') {
                $achou_ponto++;
            }else {
                if($achou_ponto == 0) {//Parte antes do número
                    $cfop.= substr($_POST['txt_cfop'], $i, 1);
                }else {
                    $num_cfop.= substr($_POST['txt_cfop'], $i, 1);
                }
            }
        }
    }
    $nf_venda           = (!empty($_POST['chkt_nf_venda'])) ? 'S' : 'N';

    $sql = "INSERT INTO `cfops` (`id_cfop`, `id_cfop_revenda`, `cfop`, `num_cfop`, `natureza_operacao`, `natureza_operacao_resumida`, `icms`, `ipi`, `descricao`, `cfop_nf_venda`, `ativo`) VALUES (NULL, '$_POST[cmb_cfop_revenda]', '$cfop', '$num_cfop', '$_POST[txt_natureza_operacao]', '$_POST[txt_natureza_operacao_resumida]', '$_POST[cmb_icms]', '$_POST[cmb_ipi]', '$_POST[txt_descricao_obs]', '$nf_venda', '1') ";
    bancos::sql($sql);
    $valor = 1;
}
?>
<html>
<title>.:: Incluir CFOP ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//ICMS
    if(!combo('form', 'cmb_icms', '', 'SELECIONE O ICMS !')) {
        return false
    }
//IPI
    if(!combo('form', 'cmb_ipi', '', 'SELECIONE O IPI !')) {
        return false
    }
//CFOP
    if(!texto('form', 'txt_cfop', '0', '1234567890.,', 'CFOP', '2')) {
        return false
    }
//Natureza Operação
    if(document.form.txt_natureza_operacao.value == '') {
        alert('DIGITE A NATUREZA DE OPERAÇÃO !')
        document.form.txt_natureza_operacao.focus()
        return false
    }
}
</Script>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Incluir CFOP
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>ICMS:</b>
        </td>
        <td>
            <b>IPI:</b>
        </td>
        <td>
            <b>CFOP:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_icms' title='Selecione o ICMS' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='1'>TRIBUTAÇÃO NORMAL</option>
                <option value='2'>ISENTO</option>
                <option value='3'>DIGITAR NA NF</option>
            </select>
        </td>
        <td>
            <select name='cmb_ipi' title='Selecione o IPI' class='combo'>
                <option value='' style="color:red">SELECIONE</option>
                <option value='1'>TRIBUTAÇÃO NORMAL</option>
                <option value='2'>ISENTO</option>
                <option value='3'>DIGITAR NA NF</option>
            </select>
        </td>
        <td>
            <input type='text' name='txt_cfop' size='20' maxlength='15' title='Digite o CFOP' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal' style="cursor:help">
        <td>
            <font title='Essa CFOP é como se fosse extensão da Principal, p/ evitar seleção de 2 CFOPS na NF'>
                CFOP de Revenda:
            </font>
        </td>
        <td colspan='2'>
            <input type='checkbox' name='chkt_nf_venda' title='Utilizada em NF de Venda e Devolução de Venda' id='nf_venda' onclick='controlar_duplicatas()' class='checkbox'>
            <label for='nf_venda'>Utilizada em NF de Venda e Devolução de Venda</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <select name="cmb_cfop_revenda" title="Selecione a CFOP Revenda" class='combo'>
            <?
                $sql = "SELECT id_cfop, CONCAT(cfop, '.', num_cfop, ' - ', natureza_operacao_resumida) AS cfop 
                        FROM `cfops` 
                        WHERE `id_cfop_revenda` = '0' 
                        AND `ativo` = '1' ORDER BY cfop ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <b>Natureza de Operação:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <textarea name="txt_natureza_operacao" title="Digite a Natureza de Operação" maxlength='255' cols='85' rows='3' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            Natureza de Operação Resumida:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <textarea name="txt_natureza_operacao_resumida" title="Digite a Natureza de Operação Resumida" maxlength='150' cols='50' rows='3' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            Descrição / Obs (Texto da Nota):
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <textarea name='txt_descricao_obs' title='Digite a Descrição / Obs (Texto da Nota)' maxlength='255' cols='85' rows='3' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR')" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>