<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/data.php');
require('../../../../lib/custos_new.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
	$tres_meses_atras 		= data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -90), '-');
	$condicao_pa_migrado 	= (!empty($chkt_pa_migrado)) ?	'' : ' AND pa.pa_migrado = 0';
	$condicao 				= (!empty($chkt_so_custos_nao_liberados)) ? ' AND pa.status_custo = 0' : '';
	//Traz todos os PA q forem = DEPTO TÉCNICO do Tipo Industrial
	if(!empty($chkt_depto_tecnico)) {//Habilitou a opção de trazer os PA = DEPTO TÉCNICO do tipo Industrial
		$sql = "SELECT pa.id_produto_acabado 
				FROM `orcamentos_vendas_itens` ovi 
				INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado AND pa.operacao_custo = '0' $condicao $condicao_pa_migrado 
				WHERE ovi.preco_liq_fat_disc = 'DEPTO TÉCNICO' ";
		$campos_depto_tecnico = bancos::sql($sql);
		$linhas_depto_tecnico = count($campos_depto_tecnico);
		for($i = 0; $i < $linhas_depto_tecnico; $i++) $id_produtos_acabados.= $campos_depto_tecnico[$i]['id_produto_acabado'].', ';
	}
	//Traz todos os PA normal segundo a claúsula
	switch($opt_opcao) {
		case 1:
			$sql = "SELECT pa.id_produto_acabado 
					FROM `produtos_acabados` pa 
					WHERE pa.discriminacao LIKE '%$txt_consultar%' 
					AND pa.operacao_custo = '0' 
					AND pa.ativo = '1' 
					AND pa.referencia = 'ESP' 
					$condicao $condicao_pa_migrado ORDER BY pa.discriminacao ";
		break;
		case 2:
			$sql = "SELECT pa.id_produto_acabado 
					FROM `produtos_acabados` pa 
					INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
					INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
					WHERE ed.razaosocial like '%$txt_consultar%' 
					AND pa.operacao_custo = '0' 
					AND pa.ativo = '1' 
					AND pa.referencia = 'ESP' 
					$condicao $condicao_pa_migrado ORDER BY ed.razaosocial ";
		break;
		default:
			$sql = "SELECT pa.id_produto_acabado 
					FROM `produtos_acabados` pa 
					WHERE pa.operacao_custo = '0' 
					AND pa.ativo = '1' 
					AND pa.referencia = 'ESP' 
					$condicao $condicao_pa_migrado ORDER BY pa.discriminacao ";
		break;
	}	
	$campos_industrial = bancos::sql($sql);
	$linhas_industrial = count($campos_industrial);
	//Unifica os PA -> DEPTO TÉCNICO do Tipo Industrial com os da cláusula normal
	for($i = 0; $i < $linhas_industrial; $i++) $id_produtos_acabados.= $campos_industrial[$i]['id_produto_acabado'].', ';
	$id_produtos_acabados = (!empty($id_produtos_acabados)) ? substr($id_produtos_acabados, 0, strlen($id_produtos_acabados) - 2) : 0;
	
	if($chkt_pas_com_orcamento == 1) {
		/*Aqui eu verifico se dos PA´s que foram retornardos dos SQL´s acima, existem aqueles q estão 
		vinculados a algum orçamento Não Congelado e dos últimos 90 dias, pois só estes que me interessam ...*/
		$sql = "SELECT pa.id_produto_acabado 
				FROM `orcamentos_vendas_itens` ovi 
				INNER JOIN `orcamentos_vendas` ov ON ovi.id_orcamento_venda = ov.id_orcamento_venda AND ov.congelar = 'N' AND ov.data_emissao > '$tres_meses_atras' 
				INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado AND pa.operacao_custo = '0' $condicao 
				WHERE ovi.id_produto_acabado IN ($id_produtos_acabados) ";
		$campos_pas_com_orcs = bancos::sql($sql);
		$linhas_pas_com_orcs = count($campos_pas_com_orcs);
		for($i = 0; $i < $linhas_pas_com_orcs; $i++) $id_pas_com_orcs.= $campos_pas_com_orcs[$i]['id_produto_acabado'].', ';
		$id_pas_com_orcs = (!empty($id_pas_com_orcs)) ? substr($id_pas_com_orcs, 0, strlen($id_pas_com_orcs) - 2) : 0;
		$condicao_pas = " pa.id_produto_acabado IN ($id_pas_com_orcs) ";
	}else {
		$condicao_pas = " pa.id_produto_acabado IN ($id_produtos_acabados) ";
	}
	//Select Principal ...
	$sql = "SELECT pa.id_produto_acabado, pa.id_funcionario, pa.operacao, pa.operacao_custo, pa.operacao_custo_sub, pa.peso_unitario, 
                pa.origem_mercadoria, pa.pa_migrado, pa.observacao, DATE_FORMAT(SUBSTRING(pa.data_sys, 1, 10), '%d/%m/%Y') AS data_inclusao, ed.razaosocial, gpa.nome 
                FROM `produtos_acabados` pa 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
                WHERE $condicao_pas ORDER BY pa.discriminacao ";
	if($modo_relatorio == 1) {//Significa que foi solicitado o relatório ...
		require('relatorio_pa_componente.php');
		exit;
	}
	$campos_principal = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
	$linhas_principal = count($campos_principal);
	if($linhas_principal < 1) {
?>
		<Script Language = 'JavaScript'>
			window.location = 'pa_componente_esp.php?valor=1'
		</Script>
<?
	}else {
?>
<html>
<head>
<title>.:: Consultar Produtos Acabados ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href='../../../../css/layout.css' type='text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function modo_relatorio() {
//Quando passo o modo_relatorio = 1, significa que é para exibir do modo relatório
	window.location = 'pa_componente_esp.php<?=$parametro;?>&modo_relatorio=1'
}
</Script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<table width='1400' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
	<tr></tr>
	<tr class="linhacabecalho" align='center'>
		<td colspan='12' height="21">
			<font color='#FFFFFF' size='-1'>
				Consultar Produtos Acabados
			</font>
		</td>
	</tr>
	<tr class="linhadestaque" align='center'>
		<td colspan="2">
			<font color='#FFFFFF' size='-1'>
				<p title="Grupo P.A. (Empresa Divisão)">Grupo P.A. (E.D.)</p>
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Produto
			</font>
		</td>
		<td width="180">
			<font color='#FFFFFF' size='-1'>
				N.º Orc(s) <br>em Aberto
			</font>
		</td>
		<td>
			<font color='#FFFFFF' title="Data de Inclusão" size='-1'>
				Data Inc
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1' title="Quantidade em Estoque">
				Qtde<br> Estoque
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1' title="Quantidade em Produção">
				Qtde<br> Produção
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				<p title="Operação de Custo">O. C.</p>
			</font>
		</td>
		<td>
                    Origem - ST
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				<p title="Operação (Fat)">O. F.</p>
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				<p title="Peso Unitário">P. U.</p>
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Observação
			</font>
		</td>
	</tr>
<?
/*Listagem de Todos os Itens que são 'ESP', que o Prazo do P.A. = 'Depart Técnico', O.C. = 'Industrial' e que o Custo esteja liberado 
- O Sistema só lista orçamentos que não estejam congelados ...*/
		$sql = "SELECT pa.id_produto_acabado 
				FROM `produtos_acabados` pa 
				WHERE pa.referencia = 'ESP' 
				AND pa.ativo = '1' 
				AND pa.status_custo = '1' 
				AND pa.operacao_custo = '0' ORDER BY pa.id_produto_acabado ";
		$campos_pas = bancos::sql($sql);
		$linhas_pas = count($campos_pas);
		for($i = 0; $i < $linhas_pas; $i++) $id_pas_custo_liberado.= $campos_pas[$i]['id_produto_acabado'].', ';
		$id_pas_custo_liberado = (!empty($id_pas_custo_liberado)) ? substr($id_pas_custo_liberado, 0, strlen($id_pas_custo_liberado) - 2) : 0;
		$condicao_pas_custo_liberado.= " AND ovi.id_produto_acabado IN ($id_pas_custo_liberado) ";

		$sql = "SELECT ovi.id_produto_acabado 
				FROM `orcamentos_vendas_itens` ovi 
				INNER JOIN `orcamentos_vendas` ov ON ovi.id_orcamento_venda = ov.id_orcamento_venda AND ov.congelar = 'N' AND ov.data_emissao > '$tres_meses_atras' 
				WHERE (ovi.prazo_entrega_tecnico = '0.0' OR ovi.preco_liq_fat_disc = 'DEPTO TÉCNICO') 
				$condicao_pas_custo_liberado ORDER BY ovi.id_orcamento_venda LIMIT 20 ";
		$campos_esp_custo_liberado = bancos::sql($sql);
		$linhas_esp_custo_liberado = count($campos_esp_custo_liberado);
		if($linhas_esp_custo_liberado > 0) {
			for($i = 0; $i < $linhas_esp_custo_liberado; $i++) {
                            $dados_produto = intermodular::dados_impostos_pa($campos_esp_custo_liberado[$i]['id_produto_acabado']);
                            
				$url = "javascript:window.location = 'prod_acabado_componente2.php?tela=2&id_produto_acabado=".$campos_esp_custo_liberado[$i]['id_produto_acabado']."&parametro=".$parametro."'";
?>
	<tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
		<td onclick="<?=$url;?>">
                    <a href="<?=$url;?>" class="link">
                        <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                    </a>
		</td>
		<td onclick="<?=$url;?>">
			<a href="<?=$url;?>" class="link">
			<?
				$sql = "SELECT ed.razaosocial, gpa.nome, pa.id_funcionario, pa.operacao, pa.operacao_custo, pa.operacao_custo_sub, pa.peso_unitario, 
                                        pa.origem_mercadoria, pa.pa_migrado, pa.observacao, DATE_FORMAT(SUBSTRING(pa.data_sys, 1, 10), '%d/%m/%Y') AS data_inclusao  
                                        FROM `produtos_acabados` pa 
                                        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                                        INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                                        INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
                                        WHERE pa.id_produto_acabado = '".$campos_esp_custo_liberado[$i]['id_produto_acabado']."' LIMIT 1 ";
				$campos_dados_pa = bancos::sql($sql);
				echo $campos_dados_pa[0]['nome'].' / '.$campos_dados_pa[0]['razaosocial'];
			?>
			</a>
		</td>
		<td>
		<?
			echo intermodular::pa_discriminacao($campos_esp_custo_liberado[$i]['id_produto_acabado']);
//Aki é a Marcação de PA Migrado
			if($campos_esp_custo_liberado[$i]['pa_migrado'] == 1) echo '<font color="red" title="PA MIGRADO" style="cursor:help"><b>MIG</b></font>';
		?>
		</td>
		<td align='center'>
		<?
			//Aqui eu verifico os Orc(s) dos Últimos 3 meses em aberto que contém esse PA ...
			$sql = "SELECT DATE_FORMAT(ov.data_emissao, '%d/%m/%Y') AS data_emissao, ovi.id_orcamento_venda, l.login 
					FROM `orcamentos_vendas_itens` ovi 
					INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda AND ov.status < '2' AND ov.data_emissao >= '$tres_meses_atras' 
					INNER JOIN `funcionarios` f ON f.id_funcionario = ov.id_funcionario 
					INNER JOIN `logins` l ON l.id_funcionario = f.id_funcionario 
					WHERE ovi.id_produto_acabado = ".$campos_esp_custo_liberado[$i]['id_produto_acabado']." 
					AND (ovi.prazo_entrega_tecnico = '0.0' OR ovi.preco_liq_fat_disc = 'DEPTO TÉCNICO') ORDER BY ov.id_orcamento_venda DESC LIMIT 1 ";
			$campos_orcs = bancos::sql($sql);
			$linhas_orcs = count($campos_orcs);
			if($linhas_orcs == 0) {
				echo '<center> - </center>';
			}else {
				echo $campos_orcs[0]['data_emissao'].' | '.$campos_orcs[0]['id_orcamento_venda'].'<font color="blue"> ('.$campos_orcs[0]['login'].')</font><br> ';
			}
		?>
		</td>
		<td align='center'>
			<?
			//Se for Diferente de 00/00/0000, então a Data Normal
				if($campos_dados_pa[0]['data_inclusao'] != '00/00/0000') {
					if($campos_dados_pa[0]['id_funcionario'] != 0) {
//Aqui eu busco qual foi o login responsável pela Inclusão ou Alteração do Prod
					$sql = "SELECT l.login 
							FROM `funcionarios` f 
							INNER JOIN `logins` l ON l.id_funcionario = f.id_funcionario 
							WHERE f.id_funcionario = ".$campos_dados_pa[0]['id_funcionario']." LIMIT 1 ";
					$campos_login = bancos::sql($sql);
?>
					<font title="Responsável pela alteração: <?=$campos_login[0]['login'];?>"><?=$campos_dados_pa[0]['data_inclusao']?></font>
<?
					}else {
						echo $campos_esp_custo_liberado[$i]['data_inclusao'];
					}
				}
			?>
		</td>
		<?
			//Aqui eu trago a qtde em Estoque e a qtde em Produção
			$sql = "SELECT qtde, qtde_producao 
					FROM `estoques_acabados` 
					WHERE `id_produto_acabado` = '".$campos_esp_custo_liberado[$i]['id_produto_acabado']."' LIMIT 1 ";
			$campos_estoque = bancos::sql($sql);
		?>
		<td align='center'>
			<?=number_format($campos_estoque[0]['qtde'], 2, ',', '.');?>
		</td>
		<td align='center'>
			<?=number_format($campos_estoque[0]['qtde_producao'], 2, ',', '.');?>
		</td>
		<td align='center'>
		<?
			if($campos_dados_pa[0]['operacao_custo'] == 0) {
				echo '<font title="Industrial" style="cursor:help">I</font>';
//Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
				if($campos_dados_pa[0]['operacao_custo_sub'] == 0) {
					echo '-<font title="Industrial" style="cursor:help">I</font>';
				}else if($campos_dados_pa[0]['operacao_custo_sub'] == 1) {
					echo '-<font title="Revenda" style="cursor:help">R</font>';
				}else {
					echo '-';
				}
			}else if($campos_dados_pa[0]['operacao_custo'] == 1) {
				echo '<font title="Revenda" style="cursor:help">R</font>';
			}else {
				echo '-';
			}
		?>
		</td>
		<td align='center'>
                    <?=$campos_dados_pa[0]['origem_mercadoria'].$dados_produto['situacao_tributaria'];?>
		</td>
		<td align='center'>
		<?
			if($campos_dados_pa[0]['operacao'] == 0) {
		?>
			<p title="Industrialização (c/ IPI)">I - C</p>
		<?
			}else {
		?>
			<p title="Revenda (s/ IPI)">R - S</p>
		<?
			}
		?>
		</td>
		<td align='right'>
			<?=number_format($campos_dados_pa[0]['peso_unitario'], 2, ',', '.');?>
		</td>
		<td align='left'>
			<?=$campos_dados_pa[0]['observacao'];?>
		</td>
	</tr>
<?
			}
?>
	<tr class="linhanormal">
		<td colspan='13' bgcolor='#CECECE'>
			<font color='white' size="1">
				<b>Total de Item(ns): 
				<font color='darkblue'>
					<b><?=$linhas_esp_custo_liberado;?></b>
				</font> 
				<font color='black'><b>sem Prazo de Entrega</b></font> ou <font color='black'><b>com Qtde do Lote Incompatível</b></font> nos Últimos 3 meses
			</font>
		</td>
	</tr>
<?
		}
//Listagem de Itens do SQL Principal ...
		for($i = 0; $i < $linhas_principal; $i++) {
                    $dados_produto = intermodular::dados_impostos_pa($campos_principal[$i]['id_produto_acabado']);
                    
                    $url = "javascript:window.location = 'prod_acabado_componente2.php?tela=2&id_produto_acabado=".$campos_principal[$i]['id_produto_acabado']."&parametro=".$parametro."'";
?>
	<tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
		<td onclick="<?=$url;?>">
                    <a href="<?=$url;?>">
                        <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                    </a>
		</td>
		<td onclick="<?=$url;?>">
			<a href="<?=$url;?>" class="link">
				<?=$campos_principal[$i]['nome'].' / '.$campos_principal[$i]['razaosocial'];?>
			</a>
		</td>
		<td>
		<?
			echo intermodular::pa_discriminacao($campos_principal[$i]['id_produto_acabado']);
//Aki é a Marcação de PA Migrado
			if($campos_principal[$i]['pa_migrado'] == 1) {
				echo '<font color="red" title="PA MIGRADO" style="cursor:help"><b>MIG</b></font>';
			}
		?>
		</td>
		<td align='center'>
		<?
			//Aqui eu verifico os Orc(s) dos Últimos 3 meses em aberto que contém esse PA ...
			$sql = "SELECT DATE_FORMAT(ov.data_emissao, '%d/%m/%Y') AS data_emissao, ovi.id_orcamento_venda, l.login 
					FROM `orcamentos_vendas_itens` ovi 
					INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda AND ov.status < '2' AND ov.data_emissao >= '$tres_meses_atras' 
					INNER JOIN `funcionarios` f ON f.id_funcionario = ov.id_funcionario 
					INNER JOIN `logins` l ON l.id_funcionario = f.id_funcionario 
					WHERE ovi.id_produto_acabado = ".$campos_principal[$i]['id_produto_acabado']." 
					AND (ovi.prazo_entrega_tecnico = '0.0' OR ovi.preco_liq_fat_disc = 'DEPTO TÉCNICO') ORDER BY ov.id_orcamento_venda DESC LIMIT 1 ";
			$campos_orcs = bancos::sql($sql);
			$linhas_orcs = count($campos_orcs);
			if($linhas_orcs == 0) {
				echo '<center> - </center>';
			}else {
				echo $campos_orcs[0]['data_emissao'].' | '.$campos_orcs[0]['id_orcamento_venda'].'<font color="blue"> ('.$campos_orcs[0]['login'].')</font><br> ';
			}
		?>
		</td>
		<td align='center'>
			<?
				//Se for Diferente de 00/00/0000, então a Data Normal
				if($campos_principal[$i]['data_inclusao'] != '00/00/0000') {
					if($campos_principal[$i]['id_funcionario'] != 0) {
					//Aqui eu busco qual foi o login responsável pela Inclusão ou Alteração do Prod ...
					$sql = "SELECT l.login 
							FROM `funcionarios` f 
							INNER JOIN `logins` l ON l.id_funcionario = f.id_funcionario 
							WHERE f.id_funcionario = ".$campos_principal[$i]['id_funcionario']." LIMIT 1 ";
					$campos_login = bancos::sql($sql);
			?>
					<font title="Responsável pela alteração: <?=$campos_login[0]['login'];?>"><?=$campos_principal[$i]['data_inclusao']?></font>
			<?
					}else {
						echo $campos_principal[$i]['data_inclusao'];
					}
				}
			?>
		</td>
		<?
			//Aqui eu trago a qtde em Estoque e a qtde em Produção
			$sql = "SELECT qtde, qtde_producao 
					FROM `estoques_acabados` 
					WHERE `id_produto_acabado` = '".$campos_principal[$i]['id_produto_acabado']."' LIMIT 1 ";
			$campos_estoque = bancos::sql($sql);
		?>
		<td align='center'>
			<?=number_format($campos_estoque[0]['qtde'], 2, ',', '.');?>
		</td>
		<td align='center'>
			<?=number_format($campos_estoque[0]['qtde_producao'], 2, ',', '.');?>
		</td>
		<td align='center'>
		<?
			if($campos_principal[$i]['operacao_custo'] == 0) {
				echo '<font title="Industrial" style="cursor:help">I</font>';
//Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
				if($campos_principal[$i]['operacao_custo_sub'] == 0) {
					echo '-<font title="Industrial" style="cursor:help">I</font>';
				}else if($campos_principal[$i]['operacao_custo_sub'] == 1) {
					echo '-<font title="Revenda" style="cursor:help">R</font>';
				}else {
					echo '-';
				}
			}else if($campos_principal[$i]['operacao_custo'] == 1) {
				echo '<font title="Revenda" style="cursor:help">R</font>';
			}else {
				echo '-';
			}
		?>
		</td>
		<td align='center'>
                    <?=$campos_principal[$i]['origem_mercadoria'].$dados_produto['situacao_tributaria'];?>
		</td>
		<td align='center'>
		<?
			if($campos_principal[$i]['operacao'] == 0) {
		?>
			<p title="Industrialização (c/ IPI)">I - C</p>
		<?
			}else {
		?>
			<p title="Revenda (s/ IPI)">R - S</p>
		<?
			}
		?>
		</td>
		<td align='right'>
			<?=number_format($campos_principal[$i]['peso_unitario'], 2, ',', '.');?>
		</td>
		<td align='left'>
			<?=$campos_principal[$i]['observacao'];?>
		</td>
	</tr>
<?
		}
?>
	<tr class="linhacabecalho" align='center'>
		<td colspan='12'>
			<input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = '<?=$PHP_SELF;?>'" class="botao">
			<input type="button" name="cmd_modo_relatorio" value="Modo Relatório" title="Modo Relatório" onclick="modo_relatorio()" class="botao">
		</td>
	</tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<pre>
<font color='red'><b>Observação:</b></font>

<font><b>Discriminação </b></font>-> Custo(s) Liberado(s)
<font color='red'><b>Discriminação </b></font>-> Custo(s) não Liberado(s)
</pre>
<?
	}
}else {
?>
<html>
<head>
<title>.:: Consultar Produtos Acabados ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
	if(document.form.opcao.checked == true) {
		for(i = 0; i < 2; i ++) {
			document.form.opt_opcao[i].disabled = true
		}
		document.form.txt_consultar.disabled = true
		document.form.txt_consultar.value = ''
	}else {
		for(i = 0; i < 2;i ++) {
			document.form.opt_opcao[i].disabled = false
		}
		document.form.txt_consultar.disabled = false
		document.form.txt_consultar.value = ''
		document.form.txt_consultar.focus()
	}
}
function iniciar() {
	if(document.form.txt_consultar.disabled == false) {
		document.form.txt_consultar.focus()
	}
}
function validar() {
//Consultar
	if(document.form.txt_consultar.disabled == false) {
		if(document.form.txt_consultar.value == '') {
			alert('DIGITE O CAMPO CONSULTAR !')
			document.form.txt_consultar.focus()
			return false
		}
	}
}
</script>
</head>
<body onLoad="iniciar()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align='center' cellspacing ='1' cellpadding='1'>
	<tr align='center'>
		<td colspan='2' width="750">
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class='linhacabecalho' align='center'>
		<td colspan="2">
			<font color='#FFFFFF' size='-1'>
				Consultar Produtos Acabados
			</font>
		</td>
    </tr>
	<tr class='linhanormal' align='center'>
		<td colspan='2'>
			Consultar <input type="text" name="txt_consultar" size='45' maxlength='45' class="caixadetexto" disabled>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="1" onClick="iniciar()" title="Consultar Produtos Acabados por: Discriminação" id='label1' checked disabled>
			<label for='label1'>
				Discrimina&ccedil;&atilde;o
			</label>
		</td>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="2" onClick="iniciar()" title="Consultar Produtos Acabados por: Empresa Divisão" id='label2' disabled>
			<label for='label2'>
				Empresa Divisão
			</label>
		</td>
	</tr>
	<tr class="linhanormal">
		<td width="20%">
			<input type='checkbox' name='chkt_so_custos_nao_liberados' value='1' title="Só Custos não Liberados" id='label3' class="checkbox" checked>
			<label for='label3'>
				Só Custos não Liberados
			</label>
		</td>
		<td width="20%">
			<input type='checkbox' name='chkt_pa_migrado' value='1' title="Incluir P.A. Migrado" id='label4' class="checkbox">
			<label for='label4'>
				Incluir P.A. Migrado
			</label>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="20%">
			<input type='checkbox' name='chkt_depto_tecnico' value='1' title="Consultar todos os DEPTO. TÉCNICO" onClick='limpar()' class="checkbox" checked id='label5'>
			<label for='label5' title="Consultar todos os DEPTO. TÉCNICO">
				Todos os DEPTO. TÉCNICO
			</label>
		</td>
		<td width="20%">
			<input type='checkbox' name='chkt_pas_com_orcamento' value='1' title="Consultar Somente PA(s) com Orçamento" id='label6' class="checkbox" checked>
			<label for='label6'>
				Somente PA(s) com Orçamento
			</label>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan="2">
			<input type='checkbox' name='opcao' onClick='limpar()' value='1' title="Consultar Todos os Produtos Acabados" id='label7' class="checkbox" checked>
			<label for='label7'>
				Todos os registros
			</label>
		</td>
	</tr>
	<tr class="linhacabecalho" align='center'>
		<td colspan="2">
			<input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" tabindex="7" onclick="document.form.opcao.checked = true;limpar();" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" tabindex="6" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>
<pre>
<font color='red'><b>Observação:</b></font>

* Traz somente P.A(s) do:

<b>* Tipo de O.C. = Industrializado.</b>
</pre>