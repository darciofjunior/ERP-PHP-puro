<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/producao/relatorio/analises_ferramentas/analises_ferramentas.php', '../../../../');

//Para não chamar as bibliotecas internas q estão dentro desse arquivo e evitar erro de redeclare ...
$nao_chamar_biblioteca = 1;
?>
<html>
<head>
<title>.:: Visualizar Qtde Vendida ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
</head>
<body>
<form name="form">
<?
//Aqui nessa primeira parte eu visualizo o Estoque do P.A. ...
require('../../../classes/estoque/visualizar_estoque.php');

/*Aqui eu busco todos os Itens de Pedido que contêm o P.A. passado por parâmetro, nessa parte calcula 
e exibe os Itens no Intervalo em que a Data de Emissão do Pedido corresponde a Data Inicial e 
Final do Filtro de Relatório */
$sql = "Select ov.valor_dolar, ovi.*, pv.id_pedido_venda, pv.data_emissao, 
	pvi.id_pedido_venda_item, pvi.qtde, pvi.vale, pvi.qtde_pendente, pvi.qtde_faturada, pvi.status as status_item, 
	pa.operacao_custo, pa.operacao, pa.peso_unitario, pa.observacao, c.id_cliente, c.id_pais, c.id_uf, IF(c.nomefantasia = '', c.razaosocial, c.nomefantasia) AS cliente
	from pedidos_vendas_itens pvi 
	inner join pedidos_vendas pv on pv.id_pedido_venda = pvi.id_pedido_venda and pv.data_emissao between '$data_inicial' and '$data_final' 
	inner join clientes_contatos cc on cc.id_cliente_contato = pv.id_cliente_contato 
	inner join clientes c on c.id_cliente = cc.id_cliente 
	inner join orcamentos_vendas_itens ovi on ovi.id_orcamento_venda_item = pvi.id_orcamento_venda_item 
	inner join orcamentos_vendas ov on ov.id_orcamento_venda = ovi.id_orcamento_venda 
	inner join produtos_acabados pa on pa.id_produto_acabado = ovi.id_produto_acabado 
	inner join gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
	where pvi.id_produto_acabado = '$id_produto_acabado' 
	order by pvi.id_pedido_venda ";
$campos = bancos::sql($sql);
$linhas = count($campos);
//Verifica se tem pelo menos um item no pedido
if($linhas > 0) {
?>
<br>
<table width='950' border='1' align='center' cellspacing='0' cellpadding='0'>
	<tr class="linhacabecalho" align="left">
		<td colspan="20">
			<font color="yellow">
				<?=intermodular::pa_discriminacao($id_produto_acabado, 0, 1, 1);?>
			</font>
			- Pedido(s) de Venda(s) no Período de <?=data::datetodata($data_inicial, '/');?> à <?=data::datetodata($data_final, '/');?>
		</td>
	</tr>
	<tr class='linhanormal' align='center'>
		<td rowspan="2" bgcolor='#CECECE'><b title="N.º do Pedido" style="cursor:help">N.º do Pedido</b></td>
                <td rowspan="2" bgcolor='#CECECE'><b title="Cliente" style="cursor:help">Cliente</b></td>
		<td rowspan="2" bgcolor='#CECECE'><b title="Data de Emissão" style="cursor:help">Data de Emissão</b></td>
		<td bgcolor='#CECECE' colspan="5"><b>Quantidade</b></td>
		<td rowspan="2" bgcolor='#CECECE'><b title="Preço Liquido Faturado / Peça" style="cursor:help">Preço<br>
		L.F. /Pç</b></td>
		<td colspan="3" bgcolor='#CECECE'><b title="Desconto SGD/ICMS" style="cursor:help">Descontos % </b></td>
		<td rowspan="2" bgcolor='#CECECE'><b title="Acrescimo Extra" style="cursor:help">Ac. Ext. %</b></td>
		<td colspan="3" bgcolor='#CECECE'><b title="Comiss&atilde;o" style="cursor:help">Comiss&atilde;o</b></td>
		<td rowspan="2" bgcolor='#CECECE'><b title="Preço Líquido Final" style="cursor:help">P. L.<br>
			Final
		</b></td>
		<td rowspan="2" bgcolor='#CECECE'><b title="Margem de Lucro Guardada %" style="cursor:help">M. G. L. %</b></td>
		<td rowspan="2" bgcolor='#CECECE'><b>Total<br> do Lote</b></td>
		<td rowspan="2" bgcolor='#CECECE'><b title="N.º do Orçamento" style="cursor:help"> N.º&nbsp;Orc</b></td>
	</tr>
	<tr class='linhanormal' align='center'>
		<td bgcolor='#CECECE'><b title="Quantidade">Ini</b></td>
		<td bgcolor='#CECECE'><strong>Fat</strong></td>
		<td bgcolor='#CECECE'><b title="Quantidade">Sep</b></td>
		<td bgcolor='#CECECE'><strong>Pend</strong></td>
		<td bgcolor='#CECECE'><strong>Vale</strong></td>
		<td bgcolor='#CECECE'><b title="Desconto do Cliente %"> Cliente </b></td>
		<td bgcolor='#CECECE'><b title="Desconto Extra">Extra</b></td>
		<td bgcolor='#CECECE'><b title="Desconto SGD/ICMS">SGD/ICMS</b></td>
		<td colspan="2" bgcolor='#CECECE'><b>Representante</b></td>
		<td bgcolor='#CECECE'><b>Comissão</b></td>
	</tr>
<?
	$id_orcamento_venda_antigo = '';//Variável para controle das cores no Orçamento
	$peso_total_geral = 0;//Essa variável é printada lá em baixo depois do for na linha 633 ...
	for($i = 0;  $i < $linhas; $i++) {
		$tx_financeira = custos::calculo_taxa_financeira($campos[$i]['id_orcamento_venda']);
//Dados do Cliente do Cliente e Pedido Corrente ...
		$id_pais = $campos[$i]['id_pais'];
		if($id_pais == 31) {//Significa que o Cliente é Nacional, ou seja a moeda é em R$
			$tipo_moeda = 'R$ ';
		}else {//Significa que o Cliente é Internacional, portanto moeda em U$
			$tipo_moeda = 'U$ ';
		}
		$id_uf_cliente = $campos[$i]['id_uf'];
?>
	<tr class='linhanormal'>
		<td align='center'>
			<?=$campos[$i]['id_pedido_venda'];?>
		</td>
		<td align='left'>
			<?=$campos[$i]['cliente'];?>
		</td>                
		<td align='center'>
			<?=data::datetodata($campos[$i]['data_emissao'], '/');?>
		</td>
		<td align='center'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
				<?=number_format($campos[$i]['qtde'], 0, ',', '.');?>
			</font>
		</td>
		<td align='center'>
			<?=number_format($campos[$i]['qtde_faturada'], 0, '.', '');?>
		</td>
		<td align='center'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
				<?=number_format($campos[$i]['qtde'] - $campos[$i]['qtde_pendente'] - $campos[$i]['vale'] - $campos[$i]['qtde_faturada'], 0, '.', '');?>
			</font>
		</td>
		<td align='center'>
			<?=number_format($campos[$i]['qtde_pendente'], 0, ',', '.');?>
		</td>
		<td align='center'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
				<?=number_format($campos[$i]['vale'], 0, ',', '.');?>
			</font>
		</td>
		<td align='center'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
			<?
				if(empty($campos[$i]['preco_liq_fat_disc'])) {
					echo $tipo_moeda.number_format($campos[$i]['preco_liq_fat'], 2, ',', '.');
				}else {
					if($campos[$i]['preco_liq_fat_disc']=='Orçar') {
						echo "<font color='red'>".$tipo_moeda.$campos[$i]['preco_liq_fat_disc']."</font>";
					}else {
						echo "<font color='blue'>".$tipo_moeda.$campos[$i]['preco_liq_fat_disc']."</font>";
					}
				}
			?>
			</font>
		</td>
		<td align='center'>
		<?
			$id_representante = $campos[$i]['id_representante'];
			$desconto_cliente = $campos[$i]['desc_cliente'];//busco do banco de dados dos itens
			echo number_format($desconto_cliente, 2, ',', '.');
		?>
		</td>
		<td align='center'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
				<?=number_format($campos[$i]['desc_extra'], 2, ',', '.');?>
			</font>
		</td>
		<td align='center'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
				<?=number_format($campos[$i]['desc_sgd_icms'], 2, ',', '.');?>
			</font>
		</td>
		<td align='center'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
				<?=number_format($campos[$i]['acrescimo_extra'], 2, ',', '.');?>
			</font>
		</td>
		<td align='center'>
		<?
			$sql = "Select nome_fantasia 
				from representantes 
				where id_representante = '$id_representante' LIMIT 1 ";
			$campos_rep = bancos::sql($sql);
			echo $campos_rep[0]['nome_fantasia'];
		?>
		</td>
		<td align='center'>
		<?
			$comissao = $campos[$i]['comissao_perc'];
			////$comissao=vendas::comissao_representante($id_representante, $id_cliente, $desconto_cliente, $campos_itens[$i]['desc_extra'], $campos_itens[$i]['acrescimo_extra']);
			echo number_format($comissao, 2, ',', '.');
		?>
		</td>
		<td align='center'>
		<?
			//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			$preco_liq_final = number_format($campos[$i]['preco_liq_final'], 2);
			$preco_total_lote = $campos[$i]['preco_liq_final'] * $campos[$i]['qtde'];
			echo $tipo_moeda.number_format(vendas::comissao_representante_reais($preco_total_lote, $comissao), 2, ',', '.');
		?>
		</td>
		<td align='right'>
		<?
			$preco_liq_final = $campos[$i]['preco_liq_final'];
			echo $tipo_moeda.number_format($preco_liq_final, 2, ',', '.')
		?>
		</td>
		<td align='right'>
			<?=number_format($campos[$i]['margem_lucro'], 1, ',', '.');?>
		</td>
		<td align='right'>
		<?
			$preco_total_lote = $preco_liq_final * $campos[$i]['qtde'];
			if($id_pais == 31) {//Significa que o Cliente é Nacional, ou seja a moeda é em R$
//Aqui eu cálculo o Valor Total da Margem de Lucro ...
				$total_margem_lucro_zero+= $preco_total_lote / (1 + $campos[$i]['margem_lucro'] / 100);
				$total_geral+= $preco_total_lote;
			}else {//Significa que o Cliente é Internacional, portanto moeda em U$
//Aqui eu cálculo o Valor Total da Margem de Lucro em Reais também mesmo quando a moeda está em Dólar ...
				$total_margem_lucro_zero+= ($preco_total_lote * $campos[$i]['valor_dolar']) / (1 + $campos[$i]['margem_lucro'] / 100);
				$total_geral+= $preco_total_lote * $campos[$i]['valor_dolar'];
			}
			echo $tipo_moeda.number_format($preco_total_lote, 2, ',', '.');
		?>
		</td>
		<td align="center">
			<?$url = "javascript:nova_janela('../../../vendas/pedidos/itens/detalhes_orcamento.php?veio_faturamento=1&id_orcamento_venda=".$campos[$i]['id_orcamento_venda']."', 'ORC', '', '', '', '', 440, 780, 'c', 'c', '', '', 's', 's', '', '', '')";?>
			<a href="<?=$url;?>" title="Visualizar Detalhes de Orçamento" class="link">
		<?
			if($id_orcamento_venda_antigo != $campos[$i]['id_orcamento_venda']) {
//Aki significa que mudou para outro N. de Orçamento e vai exibir uma nova sequência desses mesmos
				$id_orcamento_venda_antigo = $campos[$i]['id_orcamento_venda'];
		?>
				<font color='red'>
					<?=$campos[$i]['id_orcamento_venda'];?>
				</font>
		<?
//Ainda são os mesmos Orçamentos
			}else {
				echo $campos[$i]['id_orcamento_venda'];
			}
		?>
			</a>
		</td>
	</tr>
<?
	}//fim do for
?>
	<tr class='linhadestaque' align="center">
		<td colspan="6">
			Falta Cálculo da Tx Financeira - 
			<?=number_format(genericas::moeda_dia('dolar'), 4, ',', '.');?>
		</td>
		<td colspan="14" align="right">
			<font color="yellow">
				<b>M.L.M: </b>
			</font>
			<?=number_format(($total_geral / $total_margem_lucro_zero - 1) * 100, 2, ',', '.').' % - ';?>
			<font color="yellow">
				<b>TOTAL: </b>
			</font>
			<?='R$ '.number_format($total_geral, 2, ',', '.');?>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="20">
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="window.close()" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>