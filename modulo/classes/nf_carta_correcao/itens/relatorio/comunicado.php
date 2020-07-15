<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
require('../../../../../lib/faturamentos.php');
require('../../../../classes/nf_carta_correcao/class_carta_correcao.php');
session_start('funcionarios');

if($_SESSION['id_modulo'] == 3) {//Se foi acessado do Módulo de Compras ou Principal ...
    segurancas::geral('/erp/albafer/modulo/compras/nf_carta_correcao/itens/consultar.php', '../../../../../');
}else {//Se foi acessado do Módulo de Faturamento ...
    segurancas::geral('/erp/albafer/modulo/faturamento/nf_carta_correcao/itens/consultar.php', '../../../../../');
}

if(!empty($_POST['txt_valor_ipi']) || !empty($_POST['txt_valor_icms']) || !empty($_POST['txt_valor_icms_st'])) {
    $sql = "UPDATE `cartas_correcoes` SET `valor_ipi` = '$_POST[txt_valor_ipi]', `valor_icms` = '$_POST[txt_valor_icms]', `valor_icms_st` = '$_POST[txt_valor_icms_st]' WHERE `id_carta_correcao` = '$_POST[id_carta_correcao]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'Javascript' src = '../../../../../js/nova_janela.js'></Script>
    <Script Language = 'JavaScript'>
        nova_janela('imprimir_comunicado.php?id_carta_correcao=<?=$_POST['id_carta_correcao'];?>', 'POP', 'F')
        parent.fechar_pop_up_div()
    </Script>
<?
}

$especific_selecionadas         = substr($_GET['especific_selecionadas'], 0, strlen($_GET['especific_selecionadas'] - 2));
$vetor_especific_selecionadas   = explode(',', $especific_selecionadas);
//Busca de alguns valores na própria Carta de Correção ...
$sql = "SELECT valor_ipi, valor_icms, valor_icms_st 
        FROM `cartas_correcoes` 
        WHERE `id_carta_correcao` = '$_GET[id_carta_correcao]' LIMIT 1 ";
$campos_cartas_correcoes = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Comunicado de não Apropriação de Imposto ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../../../css/layout.css'>
<Script Language = 'Javascript' src = '../../../../../js/geral.js'></Script>
<Script Language = 'Javascript' src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Valor do IPI ...
    if(typeof(document.form.txt_valor_ipi) == 'object') {
        if(!texto('form', 'txt_valor_ipi', '1', '0123456789,.', 'VALOR DO IPI', '2')) {
            return false
        }
    }
//Valor do ICMS ...
    if(typeof(document.form.txt_valor_icms) == 'object') {
        if(!texto('form', 'txt_valor_icms', '1', '0123456789,.', 'VALOR DO ICMS', '2')) {
            return false
        }
    }
//Valor do ICMS ST ...
    if(typeof(document.form.txt_valor_icms_st) == 'object') {
        if(!texto('form', 'txt_valor_icms_st', '1', '0123456789,.', 'VALOR DO ICMS ST', '2')) {
            return false
        }
    }
    if(typeof(document.form.txt_valor_ipi) == 'object')     limpeza_moeda('form', 'txt_valor_ipi, ')
    if(typeof(document.form.txt_valor_icms) == 'object')    limpeza_moeda('form', 'txt_valor_icms, ')
    if(typeof(document.form.txt_valor_icms_st) == 'object') limpeza_moeda('form', 'txt_valor_icms_st, ')
}
</Script>
</head>
<body onload="document.form.elements[1].focus()" topmargin="20">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onsubmit="return validar()">
<!--Controle de Tela-->
<input type='hidden' name='id_carta_correcao' value='<?=$_GET['id_carta_correcao'];?>'>
<!--******************************************************-->
<table width='60%' cellpadding="1" cellspacing="1" align="center">
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            Comunicado de não Apropriação de Imposto
        </td>
    </tr>
<?
    if(in_array('18', $vetor_especific_selecionadas)) {
		
?>
    <tr class="linhanormal">
        <td>
            Valor do IPI:
        </td>
        <td>
            <input type='text' name='txt_valor_ipi' value='<?=number_format($campos_cartas_correcoes[0]['valor_ipi'], 2, ',', '.');?>' title='Digite o Valor do IPI' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size="18" maxlength="16" class='caixadetexto'>
        </td>
    </tr>
<?
    }
	
    if(in_array('23', $vetor_especific_selecionadas)) {
?>
    <tr class="linhanormal">
        <td>
            Valor do ICMS:
        </td>
        <td>
            <input type='text' name='txt_valor_icms' value='<?=number_format($campos_cartas_correcoes[0]['valor_icms'], 2, ',', '.');?>' title='Digite o Valor do ICMS' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size="18" maxlength="16" class='caixadetexto'>
        </td>
    </tr>
<?
    }
	
    if(in_array('43', $vetor_especific_selecionadas)) {
?>
    <tr class="linhanormal">
        <td>
            Valor do ICMS ST:
        </td>
        <td>
            <input type='text' name='txt_valor_icms_st' value='<?=number_format($campos_cartas_correcoes[0]['valor_icms_st'], 2, ',', '.');?>' title='Digite o Valor do ICMS ST' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size="18" maxlength="16" class='caixadetexto'>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.elements[1].focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_imprimir' value='Imprimir' title='Imprimir' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>