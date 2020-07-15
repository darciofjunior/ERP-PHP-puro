<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/custos_new.php');
require('../../../../lib/vendas.php');
session_start('funcionarios');
if($tela == 1) {//Veio da tela de Todos os P.A.
    segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_todos.php', '../../../../');
}else if($tela == 2) {//Veio da tela dos P.A. do Tipo Esp.
    segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_esp.php', '../../../../');
}
$mensagem[1] = "<font class='confirmacao'>CUSTO ATUALIZADO COM SUCESSO.</font>";

if($passo == 1) {
	$sql = "Update produtos_acabados_custos set qtde_lote = '$txt_qtde_lote', id_funcionario = '$id_funcionario', data_sys = '$data_sys' where id_produto_acabado_custo = '$id_produto_acabado_custo' limit 1";
	bancos::sql($sql);
	$valor = 1;
/*Atualização do Funcionário que alterou os dados no custo*/
	$data_sys = date('Y-m-d H:i:s');
	$sql = "Update produtos_acabados_custos set id_funcionario = '$id_funcionario', data_sys = '$data_sys' where id_produto_acabado_custo = '$id_produto_acabado_custo' limit 1";
	bancos::sql($sql);
//Aqui chama a função para recalcular do preço do item do orçamento
	vendas::recalcular_item_orcamento($id_produto_acabado_custo);
}

$sql = "Select pa.id_produto_acabado, pa.referencia, pa.discriminacao, pa.operacao_custo, pac.qtde_lote, pac.peca_corte from produtos_acabados pa, produtos_acabados_custos pac where pac.id_produto_acabado_custo = '$id_produto_acabado_custo' and pac.id_produto_acabado = pa.id_produto_acabado limit 1";
$campos = bancos::sql($sql);
$id_produto_acabado = $campos[0]['id_produto_acabado'];
$referencia = $campos[0]['referencia'];
$discriminacao = $campos[0]['discriminacao'];
$operacao_custo = $campos[0]['operacao_custo'];
$qtde_lote = $campos[0]['qtde_lote'];

//Peça Corte
if($campos[0]['peca_corte'] == 0) {
	$pecas_corte = 1;
}else {
	$pecas_corte = $campos[0]['peca_corte'];
}
?>
<html>
<head>
<title>.:: Alterar Quantidade do Lote ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
	if(document.form.txt_qtde_lote.value < 1) {
		alert('QUANTIDADE DO LOTE INVÁLIDA ! \nVALOR INFERIOR A HUM !')
		document.form.txt_qtde_lote.focus()
		document.form.txt_qtde_lote.select()
		return false
	}
//Aqui é uma verificação para saber se a qtde do lote é multiplo das peças / corte
	var pecas_corte = eval('<?=$pecas_corte;?>')
	var referencia = '<?=$referencia;?>'
	var operacao_custo = '<?=$operacao_custo;?>'
//Só pode fazer a comparação se o Produto for do tipo Esp e a Operação de Custo for do Tipo Industrial
	if(referencia == 'ESP' && operacao_custo == 0) {
		if(document.form.txt_qtde_lote.value % pecas_corte != 0) {//Significa que não é múltiplo de peças por corte
			alert('QUANTIDADE DO LOTE INVÁLIDA ! QUANTIDADE DO LOTE NÃO ESTÁ COMPATÍVEL COM A QTDE DE PEÇAS/CORTE !')
			document.form.txt_qtde_lote.focus()
			document.form.txt_qtde_lote.select()
			return false
		}
	}
//Aqui é para não atualizar o frames abaixo desse Pop-UP
	document.form.nao_atualizar.value = 1
	atualizar_abaixo()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
	if(document.form.nao_atualizar.value == 0) {
		window.opener.document.form.submit()
	}
}
</Script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4' onload="document.form.txt_qtde_lote.focus()" onunload="atualizar_abaixo()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onsubmit="return validar()">
<input type='hidden' name='id_produto_acabado_custo' value="<?=$id_produto_acabado_custo;?>">
<input type='hidden' name='nao_atualizar'>
<table width='750' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr align='center'>
		<td colspan='2'> <b>
			<?=$mensagem[$valor];?>
		</b> </td>
	</tr>
	<tr class='linhacabecalho' align='center'> 
		<td colspan="2">
			Alterar Quantidade do Lote
		</td>
	</tr>
	<tr class='linhadestaque'> 
		<td colspan="2"> <font color='#FFFFFF' size='-1'> <font color="#FFFF00">Ref.:</font> 
			<?=$campos[0]['referencia'];?>
			- <font color="#FFFF00">Discrim.:</font> 
			<?=$campos[0]['discriminacao'];?>
			</font> <font color='#FFFFFF' size='-1'>&nbsp; </font>
		</td>
	</tr>
	<tr class='linhanormal'> 
		<td width="146"> <b>Operação de Custo:</b> </td>
		<td width="347"> 
		<?
			if($operacao_custo == 0) {//Industrialização
				echo 'Industrialização';
			}else {//Revenda
				echo 'Revenda';
			}
		?>
		</td>
	</tr>
	<tr class='linhanormal'> 
		<td> <b>Quantidade do Lote:</b> </td>
		<td> 
<!--Força o Arredondamento da caixa, fazendo ignorar os zeros à esquerda-->
			<input type="text" name="txt_qtde_lote" value="<?=$campos[0]['qtde_lote'];?>" id="txt_qtde_lote" onKeyUp="verifica(this, 'aceita', 'numeros', '', event);if(this.value != '') {this.value = Math.round(this.value)}" size="12" class="caixadetexto"> 
		</td>
	</tr>
	<tr class="linhacabecalho" align="center"> 
		<td colspan="2">
			<input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.txt_qtde_lote.focus()" style="color:#ff9900;" class="botao"> 
			<input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao"> 
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="fechar(window)" class="botao"> 
		</td>
	</tr>
</table>
</form>
</body>
</html>