<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/genericas.php');
require('../../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/relatorios/pedidos/consultar_relatorio_pedido.php', '../../../../../');

//Busca de Algumas Variáveis que serão utilizadas mais abaixo ...
$fator_custo_importacao = genericas::variavel(1);
$valor_dolar_dia = genericas::moeda_dia('dolar');
$valor_euro_dia = genericas::moeda_dia('euro');
$ano_atual = date('Y');
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function alterar_ano() {
	window.location = 'todos_pedidos.php?cmb_ano='+document.form.cmb_ano.value
}
</Script>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<form name="form">
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
	<tr class="linhacabecalho" align="center">
		<td colspan='3'>
			Relatório de Todos os Pedidos
			<select name="cmb_ano" class="combo" onchange="alterar_ano()">
				<option value='2004'>2004</option>
				<?
					for($i = 2005;$i < $ano_atual + 20; $i++) {
						$selected = ($i == $cmb_ano) ? 'selected' : '';
				  	}
				?>
				<option value="<?=$i;?>" <?=$selected;?>><?=$i;?></option>
			</select>
<?
			if(empty($cmb_ano)) {
				$cmb_ano = $ano_atual;
			}
?>
		</td>
	</tr>
	<tr class="linhacabecalho">
		<td width="350">
			<font color="yellow">
				Valor U$ Dia: <?=number_format($valor_dolar_dia, 4, ',', '.');?> Reais
			</font>
		</td>
		<td width="350" align="left" colspan="2">
			<font color="yellow">
				Valor &euro; Dia: <?=number_format($valor_euro_dia, 4, ',', '.');?> Reais
			</font>
		</td>
	</tr>
<?
//Busca de Todos os Pedidos do Ano que foi selecionado na Combo ...
$sql = "Select count(id_pedido) as qtde_pedido, substring(data_emissao, 6, 2) as mes 
        from pedidos 
        where substring(data_emissao,1,4) = '$cmb_ano' 
        and ativo = '1' group by substring(data_emissao, 6, 2) ";
$campos = bancos::sql($sql);
for($i = 0;$i < count($campos);$i++) {
	$mes = $campos[$i]['mes'];
//Busca o Valor Total de Pedidos no Mês Corrente do Loop ...
	$sql = "Select sum(ip.valor_total) as valor_total 
                from pedidos p 
                inner join itens_pedidos ip on ip.id_pedido = p.id_pedido 
                where substring(p.data_emissao, 6, 2) = '$mes' 
                and p.status = 1 
                and p.ativo = 1 order by p.data_emissao ";
	$campos_total_pedidos_mes = bancos::sql($sql);
	$valor_total = $campos_total_pedidos_mes[0]['valor_total'];
?>
	<tr class="linhadestaque">
		<td colspan="3" align="center">
			<img width="15" height="15" src="../../../../../imagem/icones/mao1.png" title="Visualizar Pedidos / Itens do Mês <?=data::mes(intval($mes));?>" style="cursor:hand" onclick="nova_janela('consultar_pedidos_itens.php?mes=<?=$mes?>', 'POP', 'F', '', '', '', '', '', '', '', '', '', 's', 's', '', '', '')">&nbsp;
			<a href="javascript:nova_janela('consultar_pedidos_itens.php?mes=<?=$mes?>', 'POP', 'F', '', '', '', '', '', '', '', '', '', 's', 's', '', '', '')">
				<font color="#FFFFFF">
					Pedidos do Mês de <?=data::mes(intval($mes));?>
				</font>
			</a>
		</td>
	</tr>
<?
//Busca o Total dos Pedidos no Mês e Ano selecionado pelo Usuário que estão em Reais ...
	$sql = "Select sum(valor_total) as total_reais 
			from pedidos p 
			inner join itens_pedidos ip on ip.id_pedido = p.id_pedido 
			where substring(p.data_emissao, 6, 2) = '$mes' 
			and substring(p.data_emissao, 1, 4) = '$cmb_ano' 
			and p.tp_moeda = '1' ";
	$campos_reais = bancos::sql($sql);
	$total_reais = $campos_reais[0]['total_reais'];
//Busca o Total dos Pedidos no Mês e Ano selecionado pelo Usuário que estão em Dólar ...
	$sql = "Select sum(valor_total) as total_reais 
			from pedidos p 
			inner join itens_pedidos ip on ip.id_pedido = p.id_pedido 
			where substring(p.data_emissao, 6, 2) = '$mes' 
			and substring(p.data_emissao, 1, 4) = '$cmb_ano' 
			and p.tp_moeda = '2' ";
	$campos_dolar = bancos::sql($sql);
	$total_dolar = $campos_dolar[0]['total_reais'];
//Busca o Total dos Pedidos no Mês e Ano selecionado pelo Usuário que estão em Euro ...
	$sql = "Select sum(valor_total) as total_reais 
			from pedidos p 
			inner join itens_pedidos ip on ip.id_pedido = p.id_pedido 
			where substring(p.data_emissao, 6, 2) = '$mes' 
			and substring(p.data_emissao, 1, 4) = '$cmb_ano' 
			and p.tp_moeda = '3' ";
	$campos_euro = bancos::sql($sql);
	$total_euro = $campos_euro[0]['total_reais'];

	$valor_cif_dolar = $total_dolar * $valor_dolar_dia * $fator_custo_importacao;
	$valor_euro_cif = $total_euro * $valor_euro_dia * $fator_custo_importacao;
	$valor_total_calculo = $total_reais + $valor_cif_dolar + $valor_euro_cif;
?>
    <tr class="linhanormal">
		<td>
			<font color="#0000FF">
				FOB em U$:
			</font>
			<?=number_format($total_dolar, 2, ',', '.');?>
			<br>
			<font color="#660099">
				CIF em R$:
			</font>
			<?=number_format($valor_cif_dolar, 2, ',', '.');?>
		</td>
		<td>
			<font color="#0000FF">
				FOB em &euro;:
			</font>
			<?=number_format($total_euro, 2, ',', '.');?>
			<br>
			<font color="#660066">
				CIF em R$:
			</font>
			<?=number_format($valor_euro_cif, 2, ',', '.');?>
		</td>
		<td>
			<p><font color="#0000FF">
				FOB em R$:
			</font>
			<?=number_format($total_reais, 2, ',', '.');?>
			<br><b>
			<font color="#FF0000">
				Valor Total R$:
			</font></b>
			<?=number_format($valor_total_calculo, 2, ',', '.');?>
			</p>
		</td>
	</tr>
<?}?>
</table>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
<?
//Aqui eu Busco o Total de Pedidos no Ano selecionado pelo usuário ...
$sql = "Select count(p.id_pedido) as total_pedidos 
        from pedidos p 
        inner join fornecedores f on f.id_fornecedor = p.id_fornecedor 
        inner join empresas e on e.id_empresa = p.id_empresa 
        where substring(p.data_emissao, 1, 4) = '$cmb_ano' 
        and p.ativo = '1' ";
$campos = bancos::sql($sql);
$total_pedidos = $campos[0]['total_pedidos'];
if($total_pedidos == 0) {
?>
	<tr align='center'>
		<td colspan='2'>
			<b>NÃO HÁ PEDIDO(S) PENDENTE(S).</b>
		</td>
	</tr>
<?
}else {
//Aqui eu Busco o Total de Itens de Pedidos no Ano selecionado pelo usuário ...
	$sql = "Select count(p.id_pedido) as total_itens_pedidos 
			from pedidos p 
			inner join fornecedores f on f.id_fornecedor = p.id_fornecedor 
			inner join empresas e on e.id_empresa = p.id_empresa 
			inner join itens_pedidos ip on ip.id_pedido = p.id_pedido 
			inner join produtos_insumos pi on pi.id_produto_insumo = ip.id_produto_insumo 
			inner join unidades u on u.id_unidade = pi.id_unidade 
			where substring(p.data_emissao, 1, 4) = '$cmb_ano' 
			and p.ativo = 1 ";
	$campos = bancos::sql($sql);
	$total_itens_pedidos = $campos[0]['total_itens_pedidos'];
	
//Aqui eu Busco o Total de Pedidos em Real de Forma Geral do Ano ...
	$sql = "Select sum(valor_total) as total_real 
			from pedidos p 
			inner join itens_pedidos ip on ip.id_pedido = p.id_pedido 
			where substring(p.data_emissao, 1, 4) = '$cmb_ano' 
			and p.tp_moeda = '1' ";
	$campos_euro = bancos::sql($sql);
	$total_real = $campos_euro[0]['total_real'];

//Aqui eu Busco o Total de Pedidos em Dólar de Forma Geral do Ano ...
	$sql = "Select sum(valor_total) as total_dolar 
			from pedidos p 
			inner join itens_pedidos ip on ip.id_pedido = p.id_pedido 
			where substring(p.data_emissao, 1, 4) = '$cmb_ano' 
			and p.tp_moeda = '2' ";
	$campos_euro = bancos::sql($sql);
	$total_dolar = $campos_euro[0]['total_dolar'];
	
//Aqui eu Busco o Total de Pedidos em Euro de Forma Geral do Ano ...
	$sql = "Select sum(valor_total) as total_euro 
			from pedidos p 
			inner join itens_pedidos ip on ip.id_pedido = p.id_pedido 
			where substring(p.data_emissao, 1, 4) = '$cmb_ano' 
			and p.tp_moeda = '3' ";
	$campos_euro = bancos::sql($sql);
	$total_euro = $campos_euro[0]['total_euro'];

	$valor_cif_dolar = $total_dolar * $valor_dolar_dia * $fator_custo_importacao;
	$valor_euro_cif = $total_euro * $valor_euro_dia * $fator_custo_importacao;
	$valor_total_calculo = $total_reais + $valor_cif_dolar + $valor_euro_cif;
?>
	<tr class="linhacabecalho" align="center">
		<td colspan="3">
			<font color="yellow">
				<b>Totais</b>
			</font>
		</td>
	</tr>
	<tr class="linhanormal">
		<td>
			<font color="darkblue">
				<b>Total de Pedidos Pendentes: <?=$total_pedidos;?></b>
			</font>
		</td>
		<td colspan="2">
			<font color="darkblue">
				<b>Total de Itens de Pedidos Pendentes: <?=$total_itens_pedidos;?></b>
			</font>
		</td>
	</tr>
	<tr class="linhanormal">
		<td>
			<font color="#0000FF">
				FOB em U$:
			</font> 
			<?=number_format($total_dolar, 2, ',', '.');?>
			<br>
			<font color="#660099">
				CIF em R$:
			</font> 
			<?=number_format($valor_cif_dolar, 2, ',', '.');?>
		</td>
		<td>
			<font color="#0000FF">
				FOB em &euro;:
			</font> 
			<?=number_format($total_euro, 2, ',', '.');?>
			<br>
			<font color="#660066">
				CIF em R$:
			</font> 
			<?=number_format($valor_euro_cif, 2, ',', '.');?>
		</td>
		<td>
			<p><font color="#0000FF">
				FOB em R$: 
			</font> 
			<?=number_format($total_reais, 2, ',', '.');?>
			<br>
			<b><font color="#FF0000">
				Valor Total R$: 
			</font></b>
			<?=number_format($valor_total_calculo, 2, ',', '.');?>
			</p>
		</td>
	</tr>
<?
	}
?>
	<tr class="linhacabecalho" align="center">
		<td colspan='11'>
			<input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' style='color:red' onclick="window.location = '../consultar_relatorio_pedido.php'" class='botao'>
			<input type="button" name="cmd_atualizar" value="Atualizar" title="Atualizar" onclick="window.location = 'todos_pedidos.php'" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>