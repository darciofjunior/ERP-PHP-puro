<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_revenda/pa_componente_revenda_esp.php', '../../../../');

//Busca de Algumas Variáveis que serão utilizadas mais abaixo ...
$fator_custo_importacao = genericas::variavel(1);
$valor_dolar_custo = genericas::variavel(7);
$valor_euro_custo = genericas::variavel(8);
$taxa_financeira_vendas = genericas::variavel(16);
$vetor = '';

$caracteres = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', ',', ' ');
for($i =0; $i < strlen($chkt_fornecedor_prod_insumo); $i++) {
	for($j = 0; $j < count($caracteres); $j++) {
		if(substr($chkt_fornecedor_prod_insumo, $i, 1) == $caracteres[$j]) {
			$vetor.= $caracteres[$j];
		}
	}
}
$parametros = $vetor;

//Para funcionar o SQL
$chkt_fornecedor_prod_insumo = $vetor;

$sql = "Select count(fpi.id_fornecedor_prod_insumo) as total_registro 
		from fornecedores_x_prod_insumos fpi 
		inner join produtos_acabados pa on pa.id_produto_insumo = fpi.id_produto_insumo and pa.ativo = 1 and pa.operacao_custo = 1 
		inner join gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
		inner join grupos_pas gpa on gpa.id_grupo_pa = ged.id_grupo_pa 
		where fpi.id_fornecedor_prod_insumo in ($chkt_fornecedor_prod_insumo) ";
$campos = bancos::sql($sql);
$total_registro = $campos[0]['total_registro'];

//Busca a Data da Última Atualização da Lista de Preço ...
$sql = "Select data_sys 
		from fornecedores_x_prod_insumos 
		where id_fornecedor = '$id_fornecedor' 
		order by id_fornecedor_prod_insumo desc limit 1 ";
$campos_data_lista = bancos::sql($sql);
$data_atualizada = data::datetodata($campos_data_lista[0]['data_sys'], '/');
?>
<html>
<head>
	<title>.:: Itens ::.</title>
	<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
	<meta http-equiv='cache-control' content='no-store'>
	<meta http-equiv='pragma' content='no-cache'>
	<link href='../../../../css/layout.css' type=text/css rel=stylesheet>
	<Script Language='JavaScript' Src='../../../../js/validar.js'></Script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<form name='form1'>
<input type = 'hidden' name = 'id_fornecedor' value="<?=$id_fornecedor;?>">
<input type = 'hidden' name = 'razaosocial' value="<?=$razaosocial;?>">
	<table width='1000' border=0 align='left' cellspacing=1 cellpadding=1>
		<tr class="linhacabecalho" align="center">
			<td colspan="16">
				<font color='#FFFFFF' size='-1'>
					Fornecedor:&nbsp;<?=$razaosocial;?> - Produto(s) Insumo(s) Total(s):&nbsp;<?=$total_registro;?>&nbsp;&nbsp;
				</font>
			</td>
		</tr>
		<tr class='linhadestaque' align='center'>
			<td width='40'><b>Compra</b></td>
			<td width='280'><b>Produto</b></td>
			<td width='80'><b>Preço Fat. Nac. R$</b></td>
			<td width='80'><b>Prazo Pgto Dias</b></td>
			<td width='90'><b>Fator Margem de Lucro</b></td>
			<td width='90'><b>Custo P.A. Indust.</b></td>
			<td width='90'><b>Vlr Custo Nac Min R$</b></td>
			<td width='90'><b>Preço Fat. Moeda Est.</b></td>
			<td width='80'><b>Vlr Moeda p/ Compra</b></td>
			<td width='80'><b>Vlr Custo Inter Min R$</b></td>
		</tr>
	</table>
<br><!-- Precisa destes 3 <br> para dá certo na tela  dos navegadores -->
<br>
<br>
<Script Language = 'JavaScript'>
//Aqui esse desvio é para q funcione no Internet Explorer
	if(navigator.appName != 'Netscape') {
		document.write('<br>')
	}
</Script>
<iframe name="gomes" id="gomes" frameborder="0" vspace="0" hspace="0" marginheight="0" marginwidth="0" scrolling="yes" title="Bem Vindo" width='1023' height='70%' src='itens.php?id_fornecedor=<?=$id_fornecedor;?>&razaosocial=<?=$razaosocial;?>&chkt_fornecedor_prod_insumo=<?=$parametros;?>&atalho=<?=$atalho;?>'>
</iframe>
<input type="hidden" value="enviar" onclick="JavaScript:parent.gomes.document.form.submit()">
</body>
</form>
</html>
<pre>
<b><font color="blue">Variáveis:</font></b>
	<b><font color="green">* Taxa Financeira de Vendas: </font><?=number_format($taxa_financeira_vendas, 2, ',', '.');?></b>
	<b><font color="green">* Valor Moeda Dólar Custo: </font><?=number_format($valor_dolar_custo, 2, ',', '.');?></b>
	<b><font color="green">* Valor Moeda &euro; Custo: </font><?=number_format($valor_euro_custo, 2, ',', '.');?></b>
	<b><font color="green">* Fator Custo Importação(Numerário): </font><?=number_format($fator_custo_importacao, 2, ',', '.');?></b>
<b><font color="blue">Fórmulas:</font></b>
=> Se fornecedor for Brasil
	valor_custo_nac	  = fator_margem_lucro_pa*preco_faturado*tx_financeira_vendas;
	valor_custo_inter = fator_margem_lucro_pa*preco_faturado_export*tx_financeira_vendas*valor_moeda_compra_estrang;
=> Se não
	valor_custo_inter = fator_margem_lucro_pa*preco_faturado_export*tx_financeira_vendas*moeda_estrag_custo*fator_importacao;
</pre>