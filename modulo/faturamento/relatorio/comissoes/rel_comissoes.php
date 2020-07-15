<?
$cmb_empresa="t";//passo direto pq tive que desabilitar o combo, por causa que o premio pe pago pelo grupo e é emcima das três empresas.
//////////referente ao premio que eles conquista com a META /////////
$mes_ref_sg=(string)data::mes((int)date('m'));
$mes_ref_sg=substr($mes_ref_sg,0,3).date("/Y");
$mes_ref=date('m');
$ano_ref=date('Y');
$sql="select id_representante, comissao_meta_atingida, comissao_meta_atingida_sup 
	from comissoes_extras 
	where MONTH(data_periodo_fat)='$mes_ref' and YEAR(data_periodo_fat)='$ano_ref' and id_representante='$cmb_representante'";
$campos_perc_extra=bancos::sql($sql,0,1);
if(count($campos_perc_extra)==1) {
	$comissao_meta_atingida_perc		= $campos_perc_extra[0]['comissao_meta_atingida'];
	$comissao_meta_atingida_sup_perc	= $campos_perc_extra[0]['comissao_meta_atingida_sup'];
} else {
	$comissao_meta_atingida_perc		= 0;
	$comissao_meta_atingida_sup_perc	= 0;
}
//	$comissao_meta_atingida_sup_perc	= 10;//somente teste


//Busca a próxima Data do Holerith, maior do que a Data Final digitada pelo usuário no Filtro ...
$sql = "Select data, qtde_dias_uteis_mes, qtde_dias_inuteis_mes 
		from `vales_datas` 
		where data > '$data_final' limit 1 ";
$campos_data = bancos::sql($sql);
if(count($campos_data) == 1) {
	$data_holerith = data::datetodata($campos_data[0]['data'], '/');
	$qtde_dias_uteis_mes = $campos_data[0]['qtde_dias_uteis_mes'];
	$qtde_dias_inuteis_mes = $campos_data[0]['qtde_dias_inuteis_mes'];
}

if(strtoupper($cmb_empresa) == 'T') {
	$empresas[]=1;//alba
	$empresas[]=2;//tool
	$empresas[]=4;//grupo
}else {
	$empresas[]=$cmb_empresa;//empresa selecionada pela combo
}

if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST' || $pop == 1) {//existe essa linha abaixo tb
//Aqui eu verifico se o Representante é um Funcionário ...
	$sql = "Select f.id_cargo, f.id_empresa, f.id_pais 
			from representantes r 
			inner join representantes_vs_funcionarios rf on rf.id_representante=r.id_representante 
			inner join funcionarios f on f.id_funcionario=rf.id_funcionario 
			where r.id_representante='$cmb_representante'";
	$campos_rep_func = bancos::sql($sql,0,1);//certifico que o rep é funcionario

	if(count($campos_rep_func)>0) {//Signifca que o representante é funcionario
		$id_pais = $campos_rep_func[0]['id_pais'];
		$id_cargo_func   = $campos_rep_func[0]['id_cargo'];//representante externo id_cargo=>27 ou id_cargo=>25 => supervisor é para tratar como vend. externo nova lógica 109=> super interno de vendas
		$id_empresa_func = $campos_rep_func[0]['id_empresa'];
	}else {
//Se não for, então eu busco o id_pais direto da Tabela de Representantes ...
		$sql = "Select r.id_pais 
				from representantes r 
				where r.id_representante = '$cmb_representante' ";
		$campos=bancos::sql($sql,0,1);
		$id_pais = $campos[0]['id_pais'];
		$id_cargo_func   = 0;
		$id_empresa_func = 0;
	}
	for($emp=0;$emp<count($empresas);$emp++) {//caso for mais de uma empresa criará um for para disparar
		$id_empresas=$empresas[$emp];
		$total_geral=0; //zero esta variavel para nao acumular o valor na segunda empresa quando for todas empresas
		$total_geral_desconto=0;
		$sub_tot_mercadoria=0;
		if($emp > 0) { echo "<tr><td colspan='9'>&nbsp;</td></tr>";}//Estética de relatório
?>
	<tr class='linhacabecalho' align='center'>
		<td colspan='9' align='center'>
			<font color="yellow"><?=genericas::nome_empresa($id_empresas);?></font>
		</td>
	</tr>
<?

//Se o Representante for do Brasil então ...
		$campo_valor = ($id_pais == 31) ? ' nfsi.valor_unitario ' : ' nfsi.valor_unitario_exp ';
		$moeda = ($id_pais == 31) ? 'R$ ' : 'U$ ';
		$sql = "Select nfs.id_nf, nfs.id_empresa, nfs.data_emissao, nfs.suframa, ovi.comissao_perc, nfs.status, nfs.snf_devolvida, 
				if(c.nomefantasia='', c.razaosocial, c.nomefantasia) cliente, c.id_pais, 
				if(nfs.status=6, (sum(round((nfsi.qtde_devolvida*$campo_valor),2))*(-1)), sum(round((nfsi.qtde*$campo_valor),2)) ) tot_mercadoria, 
				if(nfs.status=6, (sum(round((((nfsi.qtde_devolvida*$campo_valor)*ovi.comissao_perc)/100),2))*(-1)), sum(round((((nfsi.qtde*$campo_valor)*ovi.comissao_perc)/100),2))) valor_comissao 
				from nfs_itens nfsi 
				inner join nfs on nfs.id_nf=nfsi.id_nf 
				inner join pedidos_vendas_itens pvi on pvi.id_pedido_venda_item=nfsi.id_pedido_venda_item 
				inner join orcamentos_vendas_itens ovi on ovi.id_orcamento_venda_item=pvi.id_orcamento_venda_item 
				inner join clientes c on c.id_cliente = nfs.id_cliente 
				where nfs.data_emissao between '$data_inicial' and '$data_final' and ovi.id_representante='$cmb_representante' and nfs.id_empresa='$id_empresas' 
				group by nfsi.id_nf order by nfs.data_emissao ";
		$campos = bancos::sql($sql);
		$linhas = count($campos);
		if($linhas > 0) {
?>
	<tr class='linhadestaque' align='center'>
		<td colspan='9' align='center'><font color='blue'><b>
			Vendas Direta
		</td>
	</tr>
	<tr class='linhadestaque' align="center">
		<td>Data Emissão(NF)</td>
		<td>N&ordm; da NF</td>
		<td colspan='4'>Cliente</td>
		<td>Vendas. <?=$moeda;?></td>
		<td>Comis. <?=$moeda;?></td>
		<td>Comis. Média %</td>
	</tr>
<?
		}
		for($i = 0; $i < $linhas; $i++) {
?>
	<tr class='linhanormal'>
		<td>
			<?=data::datetodata($campos[$i]['data_emissao'], '/');?>
		</td>
		<td align="center">
			<a href="javascript:nova_janela('../../../faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=<?=$campos[$i]['id_nf'];?>', 'DETALHES', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Detalhes" class="link">
				<?=faturamentos::buscar_numero_nf($campos[$i]['id_nf'], 'S');?>
			</a>
		</td>
		<td colspan='4' align="left">
		<?
			echo $campos[$i]['cliente'];
			if($campos[$i]['status'] == 6) {
				echo " <font color='red'>(DEVOLUÇÃO)</font>";
			}
		?>
		</td>
		<td align="right">
		<?
//Aqui verifica o Tipo de Nota que irá surtir efeito lá embaixo ...
			$nota_sgd = ($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2) ? 'N' : 'S';
//Valor da Mercadoria em R$
			$tot_mercadoria = $campos[$i]['tot_mercadoria'];
			$sub_tot_mercadoria+=$campos[$i]['tot_mercadoria'];
			echo number_format($tot_mercadoria, 2, ',', '.');
		?>
		</td>
		<td align="right">
		<?
			$valor_comissao = $campos[$i]['valor_comissao'];
			echo number_format($valor_comissao, 2, ',', '.');
		?>
		</td>
		<td align="right">
		<?
//Comissão Média %
			if($tot_mercadoria == 0) {
				echo '';
			}else {
				$comissao_media = ($valor_comissao / $tot_mercadoria) * 100;
				echo number_format($comissao_media, 1, ',', '.');
			}
		?>
		</td>
	</tr>
<?
			$total_geral+=$valor_comissao;
		}
		if($total_geral > 0) {
?>
	<tr class="linhanormal" align="center">
		<td colspan="7" align="right">
			Vendas <?=$moeda;?>: <?=number_format($sub_tot_mercadoria,2, ',', '.');?> 
		</td>
		<td colspan="2" align="right"><font color="blue"><b>
			Sub Total <?=$moeda;?>: <?=number_format($total_geral,2, ',', '.');?>
		</td>
	</tr>
<?
		$total_geral_premio+=$total_geral;
		}
                //Estorno de Comissões ...
		$sql= "Select date_format(ce.data_lancamento, '%d/%m/%Y %h:%i:%s') data_lancamento, ce.num_nf_devolvida, ce.tipo_lancamento, ce.porc_devolucao, ce.valor_duplicata, 
				if(c.nomefantasia = '', c.razaosocial, c.nomefantasia) cliente, nfs.id_nf, nfs.id_empresa 
				from comissoes_estornos ce 
				inner join nfs on nfs.id_nf = ce.id_nf 
				inner join clientes c on c.id_cliente = nfs.id_cliente 
				where substring(ce.data_lancamento,1,10) between '$data_inicial' and '$data_final' and ce.id_representante = '$cmb_representante' 
				and nfs.id_empresa = '$id_empresas'
				order by ce.data_lancamento ";
		$campos_devolucao=bancos::sql($sql);
		$linhas_devolucao = count($campos_devolucao);
		if($linhas_devolucao > 0) {
?>
	<tr class='linhadestaque' align='center'>
		<td colspan='9' align='center'><font color='red'><b>
			Devoluções
		</td>
	</tr>
	<tr class='linhadestaque' align='center'>
		<td>
			Data de Lançamento
		</td>
		<td>
			Tipo de Lançamento
		</td>
		<td>
			NF
		</td>
		<td>
			NF Baseada
		</td>
		<td colspan=2>
			Cliente
		</td>
		<td>
			Empresa
		</td>
		<td>
			Valor
		</td>
		<td>
			Comissão
		</td>
	</tr>
<?
		}
		for($i = 0; $i < $linhas_devolucao; $i++) { 
?>
	<tr class='linhanormal' align="center">
		<td>
			<?=$campos_devolucao[$i]['data_lancamento'];?>
		</td>
		<td align="left">
		<?
			if($campos_devolucao[$i]['tipo_lancamento'] == 0) {
				echo 'DEVOLUÇÃO DE CANCELAMENTO';
			}else if($campos_devolucao[$i]['tipo_lancamento'] == 1) {
				echo 'ATRASO DE PAGAMENTO';
			}else if($campos_devolucao[$i]['tipo_lancamento'] == 2) {
				echo 'ABATIMENTO';
			}else if($campos_devolucao[$i]['tipo_lancamento'] == 3) {
				echo 'REEMBOLSO';
			}
		?>
		</td>
		<td>
			<?=$campos_devolucao[$i]['num_nf_devolvida'];?>
		</td>
		<td>
			<?=faturamentos::buscar_numero_nf($campos_devolucao[$i]['id_nf'], 'D');?>
		</td>
		<td colspan="2" align="left">
			<?=$campos_devolucao[$i]['cliente'];?>
		</td>
		<td>
		<?
			if($campos_devolucao[$i]['id_empresa']==1) {
				echo 'ALBAFER';
			}else if($campos_devolucao[$i]['id_empresa']==2) {
				echo 'TOOL MASTER';
			}else if($campos_devolucao[$i]['id_empresa']==4) {
				echo 'GRUPO';
			}else {
				echo 'OUTROS';
			}
		?>
		</td>
		<td align="right">
			<font color="red">
				<?=$moeda.number_format($campos_devolucao[$i]['valor_duplicata'], 2, ',', '.');?>
			</font>
		</td>
		<td align="right">
		<?
			$comissao = ($campos_devolucao[$i]['valor_duplicata'] * $campos_devolucao[$i]['porc_devolucao']) / 100;
				
			if($campos_devolucao[$i]['tipo_lancamento'] == 3) {//REEMBOLSO
				echo '<font color="blue">';
				$total_geral_desconto+=$comissao;
			}else {//DEVOLUÇÃO DE CANCELAMENTO, ATRASO DE PAGAMENTO, ABATIMENTO
				echo '<font color="red">';
				$total_geral_desconto-=$comissao;
			}
			echo $moeda.number_format($comissao, 2, ',', '.');
		?>
		</td>
	</tr>
<? 
		}
		if($total_geral_desconto != 0) {
?>
	<tr class="linhanormal" align="center">
		<td colspan="9" align="right">
			<font color="red">
				<b>Sub Total <?=$moeda;?>: <?=number_format($total_geral_desconto,2, ',', '.');?></b>
			</font> 
		</td>
	</tr>
<?
		}
/**********************************Parte de Supervisão**********************************/
		if(($id_cargo_func == 25 || $id_cargo_func == 109) && $id_empresas == 4) {//Supervisor e Emp = Grupo, este relatório só irá aparecer em grupo
?>
		<tr class='linhadestaque' align='center'>
			<td colspan='9' align='center'>
				<font color='green'>
					<b>Supervisão</b>
				</font>
			</td>
		</tr>
		<tr class='linhadestaque' align='center'>
	  		<td colspan='5'>Representante</td>
	  		<td colspan='2'>Empresa</td>
	  		<td>Vendas <?=$moeda;?></td>
	  		<td>Vendas Sup. 1% em <?=$moeda;?></td>
		</tr>
	<?
			$desconto_dev_super = 0;
			$sql = "Select rs.id_representante, if(r.nome_fantasia='',r.nome_representante,r.nome_fantasia) representante 
					from representantes r 
					inner join representantes_vs_supervisores rs on rs.id_representante = r.id_representante 
					where rs.id_representante_supervisor = '$cmb_representante' order by representante ";
			$campos_sub = bancos::sql($sql);
			$linhas_sub = count($campos_sub);
			for($r = 0; $r < $linhas_sub; $r++) {
				$id_representante_sub = $campos_sub[$r]['id_representante'];
				$sql = "Select nfs.data_emissao, nfs.id_empresa, ovi.comissao_perc, 
						(sum(round(((nfsi.qtde*$campo_valor)),2)) - sum(round((nfsi.qtde_devolvida*$campo_valor),2))) valor_nota 
						from  nfs_itens nfsi 
						inner join nfs on nfs.id_nf=nfsi.id_nf 
						inner join pedidos_vendas_itens pvi on pvi.id_pedido_venda_item=nfsi.id_pedido_venda_item 
						inner join orcamentos_vendas_itens ovi on ovi.id_orcamento_venda_item=pvi.id_orcamento_venda_item 
						inner join clientes c on c.id_cliente=nfs.id_cliente 
						where nfs.data_emissao between '$data_inicial' and '$data_final' and ovi.id_representante='$id_representante_sub'
						group by ovi.id_representante, nfs.id_empresa ";
				$campos = bancos::sql($sql);
				for($i = 0; $i < count($campos); $i++) {
?>
	<tr class='linhanormal'>
		<td colspan='5'>
			<a href="#" class="link" onClick="javascript:nova_janela('comissoes.php?txt_data_inicial=<?=data::datetodata($data_inicial,"/");?>&txt_data_final=<?=data::datetodata($data_final,"/");?>&cmb_representante=<?=$id_representante_sub;?>&pop=1&cmb_empresa=<?=$campos[$i]['id_empresa'];?>', 'CONSULTAR', '', '', '', '', '750', '1000', 'c', 'c', '', '', 's', 's', '', '', '')">
				<?=$campos_sub[$r]['representante'];?>
			</a>
		</td>
		<td colspan='2' align="left">
		<?
				if($campos[$i]['id_empresa'] == 1) {
					echo 'ALBAFER';
				}else if($campos[$i]['id_empresa'] == 2) {
					echo 'TOOL MASTER';
				}else if($campos[$i]['id_empresa'] == 4) {
					echo 'GRUPO';
				}else {
					echo 'OUTROS';
				}
		?>
		</td>
		<td align="right">
			<?=number_format($campos[$i]['valor_nota'],2, ',', '.');?>
		</td>
		<td align="right">
			<?=number_format($campos[$i]['valor_nota']*0.01, 2, ',', '.');?>
		</td>
	</tr>
<?
					$sub_total_supervisor+=$campos[$i]['valor_nota'];
				}
                                //Estorno de Comissões ...
				$sql = "Select if(ce.tipo_lancamento=3, ce.valor_duplicata, ce.valor_duplicata*(-1)) valor_descontar 
						from `comissoes_estornos` ce 
						inner join nfs on nfs.id_nf = ce.id_nf 
						inner join clientes c on c.id_cliente = nfs.id_cliente 
						where substring(ce.data_lancamento,1,10) between '$data_inicial' and '$data_final' and ce.id_representante='$id_representante_sub' 
						order by ce.data_lancamento ";
				$campos_dev_super = bancos::sql($sql);
				$linhas_dev_super = count($campos_dev_super);
				for($i = 0; $i < $linhas_dev_super; $i++) {
					$desconto_dev_super+=$campos_dev_super[$i]['valor_descontar'];					
				}
			}
		}
		$sub_total_supervisor+=$desconto_dev_super;
		if($sub_total_supervisor > 0) {
?>
<!-- Mostra os desconto de atrasado pgto / reembolso / devoluções manuias do Sub do supervidor -->
	<tr class='linhanormal'>
		<td colspan='7'>
		<font color='red'>TOTAL DE REEMBOLSOS, ATRASOS DE PGTO E ABATIMENTOS DE PREÇOS DOS REPRESENTANTES SUPERVISIONADOS</font>
		</td>
		<td align="right"><?=number_format($desconto_dev_super,2, ',', '.');?></td>
		<td align="right"><?=number_format($desconto_dev_super*0.01, 2, ',', '.');?></td>
	</tr>
	<tr class="linhanormal" align="center">
		<td colspan="8" align="right"><font color="green"><b>
			Sub Total <?=$moeda;?>: <?=number_format($sub_total_supervisor, 2, ',', '.');?> 
		</td>
		<td align="right"><font color="green"><b>
			Sub Total de 1% <?=$moeda;?>: <?=number_format(($sub_total_supervisor*0.01), 2, ',', '.');?>
		</td>
	</tr>
<?
		}
?>
	<tr class="linhanormal" align="center">
		<td colspan="8" align="right">
			<font color='blue'>
				SUB TOTAL SOBRE VENDAS DIRETAS <?=$moeda;?>:
			</font>
		</td>
		<td align="right">
			<font color='blue'>
				<?=number_format($total_geral, 2, ',', '.');?>
			</font>
		</td>
	</tr>
	
<? 
	if($id_empresas==4) {//somente se for igual a 4, ou seja, Empresa Grupo 
				
?>
	<tr class="linhanormal" align="center">
		<td colspan="8" align="right">
			<font color='blue'>
				Prêmio <?=$mes_ref_sg;?> (<?=(int)$comissao_meta_atingida_perc;?>% sobre <?=number_format($total_geral_premio+$total_geral_desconto, 2, ',', '.');?>) <?=$moeda;?>:
			</font>
		</td>
		<td align="right">
			<font color='blue'>
				<? 
					$total_premio_rs =(($total_geral_premio+$total_geral_desconto)*$comissao_meta_atingida_perc/100);
					echo number_format($total_premio_rs,2, ',', '.');
				?> 
			</font>
		</td>
	</tr>
	<tr class="linhanormal" align="center">
		<td colspan="8" align="right">
			<font color='green'>
				Prêmio Sup. <?=$mes_ref_sg;?> (<?=(int)$comissao_meta_atingida_sup_perc;?>% sobre <?=number_format(($sub_total_supervisor*0.01), 2, ',', '.');?>) <?=$moeda;?>:
			</font>
		</td>
		<td align="right">
			<font color='green'>
				<? 
					$total_premio_sup_rs =(($sub_total_supervisor*0.01)*$comissao_meta_atingida_sup_perc/100);
					echo number_format($total_premio_sup_rs,2, ',', '.');
				?> 
			</font>
		</td>
	</tr>
<? } ?>
	<tr class="linhanormal" align="center">
		<td colspan="8" align="right">
			<font color='green'>
				SUB TOTAL DE SUPERVISÃO 1% <?=$moeda;?>:
			</font>
		</td>
		<td align="right">
			<font color='green'>
				<?=number_format(($sub_total_supervisor*0.01), 2, ',', '.');?>
			</font>
		</td>
	</tr>
	<tr class="linhanormal" align="center">
		<td colspan="8" align="right">
			<font color='Teal'>
				IMPOSTO DE RENDA <?=$moeda;?>: 
			</font>
		</td>
		<td align="right">
			<font color='teal'>
			<?
				if(empty($id_cargo_func) && ($id_empresas!=4)) {//automo tem IR, menos para empresa grupo
					$sql = "Select tipo_pessoa, id_pais, descontar_ir 
							from representantes r 
							where r.id_representante = '$cmb_representante' ";
					$campos_rep= bancos::sql($sql,0,1);
					if(strtoupper($campos_rep[0]['descontar_ir'])=='S') {
						if($campos_rep[0]['id_pais']==31) {
							if($campos_rep[0]['tipo_pessoa']=='J') {// se for juridico fazer o calculo de I.R.
								$ir=-(round(($total_geral*0.015),2));
								if(abs($ir)>10.00) {
									echo number_format($ir,2, ',', '.');
								} else {
									$ir=0;//ignora o desconto pois ele é muito simples
									echo "Valor Baixo 0,00";	
								}
							}else {
								$ir=0;//ignora o desconto pois ele é muito simples
								echo "PF 0,00";	
							}
						}else {
							$ir=0;//ignora o desconto pois ele é muito simples
							echo "Internacional 0,00";	
						}
					}else {
						$ir=0;//ignora o desconto IR
						echo "Marcação Cadastro 0,00";	
					}
				}else {
					$ir=0;//ignora o desconto pois ele é muito simples
					echo "0,00";	
				}
			?>
			</font>
		</td>
	</tr>
	<tr class="linhanormal" align="center">
		<td colspan="8" align="right">
			<font color='red'>
				SUB TOTAL DAS DEVOLUÇÕES / REEMBOLSOS <?=$moeda;?>: 
			</font>
		<td align="right">
			<font color='red'>
				<?=number_format($total_geral_desconto, 2, ',', '.');?> 
			</font>
		</td>
	</tr>
	<tr class="linhanormal">
		<td colspan="8" align="right">
			<font color='blue'>
				DSR <?=$moeda;?>:
			</font>
		</td>
		<td align="right">
			<font color='blue'>
			<?
				$total_global=$total_geral+($sub_total_supervisor*0.01)+$ir+$total_geral_desconto;
				/*Por enquanto não apagar até porque não se tem certeza ...
				if(($id_empresa_func!=$id_empresas || $id_empresas==4) && !empty($id_cargo_func) && $cmb_representante!=14) {
					$aditivo = $total_global*0.20;
					$total_global+=$aditivo;
				}else {
					$aditivo = 0;
				}*/
/*Se a Qtde de Dias Úteis ou Qtde de Dias Inúteis = 0 ou o Representante for a Mercedes, então não existe 
cálculo p/ o DSR ...*/
				if($qtde_dias_uteis_mes==0 || $qtde_dias_inuteis_mes==0 || empty($id_cargo_func) || $cmb_representante==14) {
					$dsr = 0;
				}else {
					$dsr = $total_global / $qtde_dias_uteis_mes * $qtde_dias_inuteis_mes;
					if($dsr<0) {
						$dsr=0;
					}
				}
				$total_global+=$dsr;
				echo number_format($dsr, 2, ',', '.');
			?> 
			</font>
		</td>
	</tr>
	<tr class="linhadestaque">
		<td colspan="8" align="right">
			<font color='black'><b>
				TOTAL GERAL <?=$moeda;?>:
			</b></font>
		</td>
		<td align="right">
			<font color='black'><b>
			<?
				$total_global+=$total_premio_rs+$total_premio_sup_rs;//adiciono os premios por META do rep + supervisor 
				
				$total_geral_global+=$total_global; //guardo o total geral de comissao que o representante ganhou
				echo number_format($total_global, 2, ',', '.');
			?>
			</b></font>
		</td>
	</tr>
<?
	}//fim do if do submt do method post
}

if(strtoupper($_SERVER['REQUEST_METHOD']) == "POST" || $pop == 1) {
?>
	<tr class="linhadestaque">
		<td colspan="3">
			<font color='yellow' size='1'><b>
				Data de Holerith: 
			</b></font>
			<font color='white' size='1'><b>
				<?=$data_holerith;?>
			</b></font>
			- 
			<font color='yellow' size='1'><b>
				Dias Úteis: 
			</b></font>
			<font color='white' size='1'><b>
				<?=$qtde_dias_uteis_mes;?>
			</b></font>
			- 
			<font color='yellow' size='1'><b>
				Dom. e Fer: 
			</b></font>
			<font color='white' size='1'><b>
				<?=$qtde_dias_inuteis_mes;?>
			</b></font>
		</td>
		<td colspan="5" align="right">
			<font color='black'><b>
				TOTAL GERAL DE TODAS AS EMPRESAS <?=$moeda;?>: 
			</b></font>
		</td>
		<td align="right">
			<font color='black'><b>
				<?=number_format($total_geral_global,2, ',', '.');?>
			</b></font>
		</td>
	</tr>
<?
	if($_POST['cmb_representante'] == 71) {
?>
	<tr class="linhadestaque">
		<td colspan="9">
			<font color='black'><b>
				PAGAMENTO DO PME => * NISHIMURA R$ <?=number_format($total_geral_global * 0.5, 2, ',', '.');?> - * ADRIANA E IVAIR R$ <?=number_format($total_geral_global * 0.25, 2, ',', '.');?>
			</b></font>
		</td>
	</tr>
<?
	}
?>
	<tr class="linhacabecalho" align="center">
		<td colspan="9">
			<input type="submit" name="cmd_atualizar" value="Atualizar Relatório" title='Atualizar Relatório' class="botao">
			<?
//Significa que essa Tela, foi acessada de algum outro lugar, fora do Menu ...
				if($veio_outra_tela == 1) {//Então sempre terá q manter esse botão travado
					$class = 'textdisabled';
					$disabled = 'disabled';
				}else {
/*Aqui eu faço esse controle com as Datas p/ impedir de que o usuário imprima caso a Data Final de comissão 
seje menor do que a Data Atual ...*/
					$dia_atual = date('d');
					if($dia_atual >= 26 && $dia_atual <= date('t')) {//Tolerância 5 dias
						$class = 'botao';
						$disabled = '';
					}else {
						$class = 'textdisabled';
						$disabled = 'disabled';
					}
				}
			?>
			<input type="button" name="cmd_imprimir" value="Imprimir" title='Imprimir' onclick='imprimir()' class='textdisabled' disabled>
		</td>
	</tr>
<?}?>
</table>
</body>
</html>
<Script Language = 'JavaScript'>
function imprimir() {
	var qtde_dias_uteis_mes = '<?=$qtde_dias_uteis_mes;?>'
	var qtde_dias_inuteis_mes = '<?=$qtde_dias_inuteis_mes;?>'
//Essas variáveis vão servir p/ o controle de Impressão do Relatório de Comissão em PDF ...
	if(qtde_dias_uteis_mes == 0 && qtde_dias_inuteis_mes == 0) {
		alert('O "CAMPO DIAS ÚTEIS" E O "CAMPO DOMINGOS E FERIADOS" SÃO = 0 !\nENTRE EM CONTATO COM O DEPARTAMENTO PESSOAL !')
	}else if(qtde_dias_uteis_mes > 0 && qtde_dias_inuteis_mes == 0) {
		alert('O "CAMPO DIAS ÚTEIS" = 0 !\nENTRE EM CONTATO COM O DEPARTAMENTO PESSOAL !')
	}else if(qtde_dias_uteis_mes == 0 && qtde_dias_inuteis_mes > 0) {
		alert('O "CAMPO DOMINGOS E FERIADOS" = 0 !\ENTRE EM CONTATO COM O DEPARTAMENTO PESSOAL !')
	}else {
//Levo essas variáveis por parâmetro porque vou utilizar no SQL do relatório ...
		var data_inicial = '<?=$data_inicial;?>'
		var data_final = '<?=$data_final;?>'
		var cmb_representante = '<?=$cmb_representante;?>'
		var cmb_empresa = '<?=$cmb_empresa;?>'
		return nova_janela('relatorio_pdf/relatorio.php?data_inicial='+data_inicial+'&data_final='+data_final+'&cmb_representante='+cmb_representante+'&cmb_empresa='+cmb_empresa, 'CONSULTAR', 'F')
	}
}
</Script>