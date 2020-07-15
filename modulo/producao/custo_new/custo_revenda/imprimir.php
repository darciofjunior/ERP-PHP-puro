<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_revenda/pa_componente_revenda_esp.php', '../../../../');

//Aqui listo todos os PA(s) selecionados na Tela de Itens pelo usuário para serem Impressos ...
$sql = "Select pa.id_produto_acabado, pa.referencia, pa.discriminacao, gpa.nome, fpi.* 
        FROM `fornecedores_x_prod_insumos` fpi 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = pa.`id_produto_insumo` AND pa.`ativo` = '1' AND pa.`operacao_custo` = '1' 
        inner join gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
        inner join grupos_pas gpa on gpa.id_grupo_pa = ged.id_grupo_pa 
        where fpi.id_fornecedor_prod_insumo in ($chkt_fornecedor_prod_insumo) ";
$campos_lista = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
$linhas = count($campos_lista);
?>
<html>
<head>
<title>.:: Imprimir ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<form name='form'>
<table width='980' border='0' cellspacing="1" cellpadding="1" align="center">
	<tr class="linhacabecalho" align="center">
		<td colspan="10">
			<font color='#FFFFFF' size='-1'>
				Imprimir PA(s) do Fornecedor: 
			</font>
			&nbsp;<?=$_GET['razao_social'];?>
		</td>
	</tr>
	<tr class='linhadestaque' align='center'>
		<td>Produto</td>
		<td>Preço Fat. Nac. R$</td>
		<td>Prazo Pgto Dias</td>
		<td>Fator Margem de Lucro</td>
		<td>Custo P.A. Indust.</td>
		<td>Vlr Custo Nac Min R$</td>
		<td>Preço Fat. Moeda Est.</td>
		<td>Vlr Moeda p/ Compra</td>
		<td>Vlr Custo Inter Min R$</td>
	</tr>
<?
for($i = 0; $i < $linhas; $i++) {
?>
	<tr class="linhanormal"  align="center" title="<?=$campos_lista[$i]['referencia'].' | '.$campos_lista[$i]['discriminacao'];?>   ">
		<td align="left">
			<?=intermodular::pa_discriminacao($campos_lista[$i]['id_produto_acabado']);?>
		</td>
		<td>
			<input type="text" name="txt_preco_faturado[]" size="10" maxlength="9" class="textdisabled" disabled>
		</td>
		<td>
			<input type="text" name="txt_prazo_pgto_ddl[]" size="8" maxlength="7" class="textdisabled" disabled>
		</td>
		<td>
			<input type="text" name="txt_fator_margem_lucro_pa[]" size="8" maxlength="7" class="textdisabled" disabled>
		</td>
		<td>
			<input type="text" name="txt_custo_pa_indust[]" size="8" maxlength="7" class="textdisabled" disabled>
		</td>
		<td>
			<input type="text" name="txt_valor_custo_nacional[]" size="8" maxlength="7" class="textdisabled" disabled>
		</td>
		<td>
			<input type="text" name="txt_preco_fat_exp[]" size="8" maxlength="7" class="textdisabled" disabled>
			<?
				if($id_tipo_moeda == 1) {
					echo 'U$';
				}else if($id_tipo_moeda == 2) {
					echo '&euro;&nbsp;&nbsp;';
				}
			?>
		</td>
		<td>
			<input type="text" name="txt_valor_moeda_compra[]" size="8" maxlength="7" class="textdisabled" disabled>
		</td>
		<td>
			<input type="text" name="txt_valor_custo_internacional[]" size="8" maxlength="7" class="textdisabled" disabled>
			<input type="hidden" name="tipo_moeda[]">
		</td>
	</tr>
<?
	}
?>
	<tr class="linhacabecalho" align="center">
		<td colspan="10">
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onClick="window.close()" style="color:red" class="botao">
			&nbsp;&nbsp;<img src="../../../../imagem/icones2/print.png" title='Imprimir' onclick="window.print()">
		</td>
	</tr>
	<tr>
		<td colspan="10">
			&nbsp;
		</td>
	</tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<br><!-- Precisa destes 3 <br> para dá certo na tela  dos navegadores -->
<Script Language = 'JavaScript'>
//Aqui esse desvio é para q funcione no Internet Explorer
if(navigator.appName != 'Netscape') {
	document.write('<br>')
}
/*************************************************************************************************************/
//Coloca essa função aqui embaixo, porque assim, já tenho carregado todos os objetos dessa Tela de Impressão ...
/*******Chamada da Função quando carregar*********/
buscar_valores_tela_itens()
/*************************************************************************************************************/
function buscar_valores_tela_itens() {
	var elementos_cima = document.form.elements
	
	var elementos_tela_itens = opener.document.form.elements
	var qtde_objetos_tela_itens_inicio = 0//Qtde de Objetos antes de entrar no Loop ...
	var qtde_objetos_tela_itens_linha = 9//Qtde de Objetos dentro do Loop ...
	var qtde_objetos_tela_itens_fim = 14//Qtde de Objetos após o Loop ...
	
//Aqui eu transporto os valores da Tela de Baixo de Itens que foram digitados pelo usuário, p/ essa Tela aqui ...
	for(var m = qtde_objetos_tela_itens_inicio; m < (elementos_tela_itens.length) - qtde_objetos_tela_itens_fim; m+=qtde_objetos_tela_itens_linha) {
		elementos_cima[m].value = elementos_tela_itens[m].value
		elementos_cima[m + 1].value = elementos_tela_itens[m + 1].value
		elementos_cima[m + 2].value = elementos_tela_itens[m + 2].value
		elementos_cima[m + 3].value = elementos_tela_itens[m + 3].value
		elementos_cima[m + 4].value = elementos_tela_itens[m + 4].value
		elementos_cima[m + 5].value = elementos_tela_itens[m + 5].value
		elementos_cima[m + 6].value = elementos_tela_itens[m + 6].value
		elementos_cima[m + 7].value = elementos_tela_itens[m + 7].value
		elementos_cima[m + 8].value = elementos_tela_itens[m + 8].value
	}
}
</Script>
</body>
</form>
</html>