<?
require('../../../../lib/segurancas.php');
require('../../../../lib/cascates.php');
require('../../../../lib/data.php');
require('../../../../lib/faturamentos.php');
session_start('funcionarios');
segurancas::geral('/erp/albafer/modulo/faturamento/outras_nfs/itens/alterar_imprimir.php', '../../../../');
$mensagem[1] = "<font class='confirmacao'>AJUSTE(S) ALTERADO(S) COM SUCESSO.</font>";

if($passo == 1) {
    $sql = "UPDATE `nfs_outras` SET `ajuste_total_produtos` = '$_POST[txt_ajuste_total_produtos]', `ajuste_total_nf` = '$_POST[txt_ajuste_total_nf]', `ajuste_ipi` = '$_POST[txt_ajuste_ipi]', `ajuste_icms` = '$_POST[txt_ajuste_icms]' WHERE `id_nf_outra` = '$_POST[id_nf_outra]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        window.location = 'ajustes_impostos_nf.php?id_nf_outra=<?=$_POST['id_nf_outra'];?>&valor=1'
    </Script>
<?
}else {
    $sql = "SELECT ajuste_total_produtos, ajuste_total_nf, ajuste_ipi, ajuste_icms 
            FROM `nfs_outras` 
            WHERE `id_nf_outra` = '$_GET[id_nf_outra]' LIMIT 1 ";
    $campos_nf_outras = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Ajustes / Impostos de NF ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../../css/layout.css'>
<Script Language = 'Javascript' src = '../../../../js/validar.js'></Script>
<Script Language = 'Javascript' src = '../../../../js/geral.js'></Script>
<Script Language = 'Javascript' src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Ajuste do Total dos Produtos ...
	if(document.form.txt_ajuste_total_produtos.value != '') {
		if(!texto('form', 'txt_ajuste_total_produtos', '1', '-0123456789,.', 'AJUSTE DO TOTAL DOS PRODUTOS', '2')) {
			return false
		}
	}
//Ajuste do Total da NF ...
	if(document.form.txt_ajuste_total_nf.value != '') {
		if(!texto('form', 'txt_ajuste_total_nf', '1', '-0123456789,.', 'AJUSTE DO TOTAL DA NF', '2')) {
			return false
		}
	}
//Ajuste de IPI ...
	if(document.form.txt_ajuste_ipi.value != '') {
		if(!texto('form', 'txt_ajuste_ipi', '1', '-0123456789,.', 'AJUSTE DE IPI', '2')) {
			return false
		}
	}
//Ajuste de ICMS ...
	if(document.form.txt_ajuste_icms.value != '') {
		if(!texto('form', 'txt_ajuste_icms', '1', '-0123456789,.', 'AJUSTE DE ICMS', '2')) {
			return false
		}
	}
//Aqui é para não atualizar o frames abaixo desse Pop-UP
	document.form.nao_atualizar.value = 1
	atualizar_abaixo()
	return limpeza_moeda('form', 'txt_ajuste_total_produtos, txt_ajuste_total_nf, txt_ajuste_ipi, txt_ajuste_icms, ')
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
<body onload="document.form.txt_ajuste_total_produtos.focus()" onunload="atualizar_abaixo()" topmargin="20">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onsubmit="return validar()">
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='id_nf_outra' value='<?=$_GET['id_nf_outra'];?>'>
<!--******************************************************-->
<table width="550" cellpadding="1" cellspacing="1" align="center">
	<tr align="center">
		<td colspan="2">
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			Ajustes / Impostos de NF
		</td>
	</tr>
	<tr class="linhanormal">
		<td width="200">Ajuste Total dos Produtos:</td>
		<td width="350">
			<input type='text' name='txt_ajuste_total_produtos' value='<?=number_format($campos_nf_outras[0]['ajuste_total_produtos'], 2, ',', '.');?>' title='Digite o Ajuste de NF' onkeyup="verifica(this, 'moeda_especial', '2', '1', event)" size="15" maxlength="10" class='caixadetexto'>
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Ajuste Total da NF:</td>
		<td>
			<input type='text' name='txt_ajuste_total_nf' value='<?=number_format($campos_nf_outras[0]['ajuste_total_nf'], 2, ',', '.');?>' title='Digite o Ajuste de NF' onkeyup="verifica(this, 'moeda_especial', '2', '1', event)" size="15" maxlength="10" class='caixadetexto'>
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Ajuste de IPI:</td>
		<td>
			<input type='text' name='txt_ajuste_ipi' value='<?=number_format($campos_nf_outras[0]['ajuste_ipi'], 2, ',', '.');?>' title='Digite o Ajuste de NF' onkeyup="verifica(this, 'moeda_especial', '2', '1', event)" size="15" maxlength="10" class='caixadetexto'>
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Ajuste de ICMS:</td>
		<td>
			<input type='text' name='txt_ajuste_icms' value='<?=number_format($campos_nf_outras[0]['ajuste_icms'], 2, ',', '.');?>' title='Digite o Ajuste de NF' onkeyup="verifica(this, 'moeda_especial', '2', '1', event)" size="15" maxlength="10" class='caixadetexto'>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" class="botao" style="color:#ff9900;" onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_ajuste_total_produtos.focus()">
			<input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="fechar(window)" style="color:red" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>