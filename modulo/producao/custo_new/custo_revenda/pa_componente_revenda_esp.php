<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/data.php');
require('../../../../lib/custos_new.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
	$tres_meses_atras 	= data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -90), '-');
	$condicao 			= (!empty($chkt_so_custos_nao_liberados)) ? ' AND pa.status_custo = 0' : '';
	//Traz todos os PA q forem = DEPTO T�CNICO do Tipo Industrial
	if(!empty($chkt_depto_tecnico)) {//Habilitou a op��o de trazer os PA = DEPTO T�CNICO do tipo Industrial
		$sql = "SELECT pa.id_produto_acabado 
				FROM `orcamentos_vendas_itens` ovi 
				INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado AND pa.operacao_custo = '1' $condicao 
				WHERE ovi.preco_liq_fat_disc = 'DEPTO T�CNICO' ";
		$campos_depto_tecnico = bancos::sql($sql);
		$linhas_depto_tecnico = count($campos_depto_tecnico);
		for($i = 0; $i < $linhas_depto_tecnico; $i++) $id_produtos_acabados.= $campos_depto_tecnico[$i]['id_produto_acabado'].', ';
	}
	//Traz todos os PA normal segundo a cla�sula
	switch($opt_opcao) {
		case 1:
			$sql = "SELECT pa.id_produto_acabado 
					FROM `produtos_acabados` pa 
					WHERE pa.discriminacao LIKE '%$txt_consultar%' 
					AND pa.operacao_custo = '1' 
					AND pa.ativo = '1' 
					AND pa.referencia = 'ESP' 
					$condicao ORDER BY pa.discriminacao ";
		break;
		default:
			$sql = "SELECT pa.id_produto_acabado 
					FROM `produtos_acabados` pa 
					WHERE pa.operacao_custo = '1' 
					AND pa.ativo = '1' 
					AND pa.referencia = 'ESP' 
					$condicao ORDER BY pa.discriminacao ";
		break;
	}
	$campos_revenda = bancos::sql($sql);
	$linhas_revenda = count($campos_revenda);
//Unifica os PA -> DEPTO T�CNICO do Tipo Revenda com os da cl�usula normal
	for($i = 0; $i < $linhas_revenda; $i++) $id_produtos_acabados.= $campos_revenda[$i]['id_produto_acabado'].', ';
	$id_produtos_acabados = (!empty($id_produtos_acabados)) ? substr($id_produtos_acabados, 0, strlen($id_produtos_acabados) - 2) : 0;
	
	if($chkt_pas_com_orcamento == 1) {
		/*Aqui eu verifico se dos PA�s que foram retornardos dos SQL�s acima, existem aqueles q est�o 
		vinculados a algum or�amento N�o Congelado e dos �ltimos 90 dias, pois s� estes que me interessam ...*/
		$sql = "SELECT pa.id_produto_acabado 
				FROM `orcamentos_vendas_itens` ovi 
				INNER JOIN `orcamentos_vendas` ov ON ovi.id_orcamento_venda = ov.id_orcamento_venda AND ov.congelar = 'N' AND ov.data_emissao > '$tres_meses_atras' 
				INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado AND pa.operacao_custo = '1' $condicao 
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
	$sql = "SELECT pa.id_produto_acabado, pa.id_gpa_vs_emp_div, pa.id_funcionario, pa.operacao, pa.operacao_custo, pa.operacao_custo_sub, pa.peso_unitario, 
                pa.pa_migrado, pa.observacao, DATE_FORMAT(SUBSTRING(pa.data_sys, 1, 10), '%d/%m/%Y') AS data_inclusao, ed.razaosocial, gpa.nome 
                FROM `produtos_acabados` pa 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
                WHERE $condicao_pas ORDER BY pa.discriminacao ";
	$campos_principal = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
	$linhas_principal = count($campos_principal);

	if($linhas_principal < 1) {
?>
		<Script Language = 'JavaScript'>
			window.location = 'pa_componente_revenda_esp.php?valor=1'
		</Script>
<?
	}else {
?>
<html>
<head>
<title>.:: Consultar Produto(s) Insumo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<form name='form'>
<table width='1200' border='0' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)" align='center'>
	<tr>
		<td colspan='8'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-1'>
			</font>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan='8'>
			<font color='#FFFFFF' size='-1'>
				Consultar Produto(s) Acabado(s)
			</font>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td colspan="2">
			<font size="-1">
				<p title="Grupo P.A.">Grupo P.A.</p>
			</font>
		</td>
		<td>
			<font color='#FFFFFF' title='Qtde do �ltimo Or�amento' size='-1'>
				Qtde
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Produto
			</font>
		</td>
		<td width="180">
			<font color='#FFFFFF' size='-1'>
				N.� Orc(s) <br>em Aberto
			</font>
		</td>
		<td>
			<font color='#FFFFFF' title="Data de Inclus�o" size='-1'>
				Data Inc
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Fornecedor
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				<p title="Fator Margem de Lucro">F. M. L</p>
			</font>
		</td>
	</tr>
<?
/*Listagem de Todos os Itens que s�o 'ESP', que o Prazo do P.A. = 'Depart T�cnico', O.C. = 'Revenda' e que o Custo esteja liberado 
- O Sistema s� lista or�amentos que n�o estejam congelados ...*/
		$sql = "SELECT pa.id_produto_acabado 
				FROM `produtos_acabados` pa 
				WHERE pa.referencia = 'ESP' 
				AND pa.ativo = '1' 
				AND pa.status_custo = '1' 
				AND pa.operacao_custo = '1' ORDER BY pa.id_produto_acabado ";
		$campos_pas = bancos::sql($sql);
		$linhas_pas = count($campos_pas);
		for($i = 0; $i < $linhas_pas; $i++) $id_pas_custo_liberado.= $campos_pas[$i]['id_produto_acabado'].', ';
		$id_pas_custo_liberado = (!empty($id_pas_custo_liberado)) ? substr($id_pas_custo_liberado, 0, strlen($id_pas_custo_liberado) - 2) : 0;
		$condicao_pas_custo_liberado.= " AND ovi.id_produto_acabado IN ($id_pas_custo_liberado) ";

		$sql = "SELECT ovi.id_produto_acabado 
				FROM `orcamentos_vendas_itens` ovi 
				INNER JOIN `orcamentos_vendas` ov ON ovi.id_orcamento_venda = ov.id_orcamento_venda AND ov.congelar = 'N' AND ov.data_emissao > '$tres_meses_atras' 
				WHERE ovi.prazo_entrega_tecnico = '0.0' 
				$condicao_pas_custo_liberado ORDER BY ovi.id_orcamento_venda LIMIT 20 ";
		$campos_esp_custo_liberado = bancos::sql($sql);
		$linhas_esp_custo_liberado = count($campos_esp_custo_liberado);
		if($linhas_esp_custo_liberado > 0) {
			for($i = 0;  $i < $linhas_esp_custo_liberado; $i++) {
?>
	<tr onclick="window.location = 'pa_componente_revenda_esp2.php?id_produto_acabado=<?=$campos_esp_custo_liberado[$i]['id_produto_acabado'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" class="linhanormal" align='center'>
            <td>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </td>
		<td align='left'>
		<?
                    $sql = "SELECT ed.razaosocial, gpa.nome, pa.id_funcionario, pa.operacao, pa.operacao_custo, pa.operacao_custo_sub, pa.peso_unitario, 
                            pa.pa_migrado, pa.observacao, DATE_FORMAT(SUBSTRING(pa.data_sys, 1, 10), '%d/%m/%Y') AS data_inclusao  
                            FROM `produtos_acabados` pa 
                            INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                            INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                            INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
                            WHERE pa.id_produto_acabado = '".$campos_esp_custo_liberado[$i]['id_produto_acabado']."' LIMIT 1 ";
                    $campos_dados_pa = bancos::sql($sql);
                    echo $campos_dados_pa[0]['nome'].' / '.$campos_dados_pa[0]['razaosocial'];
                ?>
		</td>
		<td>
			<?=number_format($campos_esp_custo_liberado[$i]['qtde'], 0, ',', '.');?>
		</td>
		<td align='left'>
		<?
			if($campos_esp_custo_liberado[$i]['status_custo'] == 1) {//J� est� liberado
		?>
			<font title="Custo Liberado">
		<?
				echo intermodular::pa_discriminacao($campos_esp_custo_liberado[$i]['id_produto_acabado']);
			}else {//N�o est� liberado
		?>
			<font title="Custo n�o Liberado" color="red">
		<?
				echo intermodular::pa_discriminacao($campos_esp_custo_liberado[$i]['id_produto_acabado']);
		?>
			</font>
		<?
			}
		?>
		</td>
		<td align="center">
		<?
			//Aqui eu verifico os Orc(s) dos �ltimos 3 meses em aberto que cont�m esse PA ...
			$sql = "SELECT DATE_FORMAT(ov.data_emissao, '%d/%m/%Y') AS data_emissao, ovi.id_orcamento_venda, l.login 
					FROM `orcamentos_vendas_itens` ovi 
					INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda AND ov.status < '2' AND ov.data_emissao >= '$tres_meses_atras' 
					INNER JOIN `funcionarios` f ON f.id_funcionario = ov.id_funcionario 
					INNER JOIN `logins` l ON l.id_funcionario = f.id_funcionario 
					WHERE ovi.id_produto_acabado = ".$campos_esp_custo_liberado[$i]['id_produto_acabado']." 
					AND ovi.prazo_entrega_tecnico = '0.0' ORDER BY ov.id_orcamento_venda DESC LIMIT 1 ";
			$campos_orcs = bancos::sql($sql);
			$linhas_orcs = count($campos_orcs);
			if($linhas_orcs == 0) {
				echo '<center> - </center>';
			}else {
				echo $campos_orcs[0]['data_emissao'].' | '.$campos_orcs[0]['id_orcamento_venda'].'<font color="blue"> ('.$campos_orcs[0]['login'].')</font><br> ';
			}
		?>
		</td>
		<td align="center">
			<?
			//Se for Diferente de 00/00/0000, ent�o a Data Normal
				if($campos_esp_custo_liberado[$i]['data_inclusao'] != '00/00/0000') {
					if($campos_esp_custo_liberado[$i]['id_funcionario'] != 0) {
//Aqui eu busco qual foi o login respons�vel pela Inclus�o ou Altera��o do Prod
					$sql = "Select l.login 
							from funcionarios f 
							inner join logins l on l.id_funcionario = f.id_funcionario 
							where f.id_funcionario = ".$campos_esp_custo_liberado[$i]['id_funcionario']." limit 1 ";
					$campos2 = bancos::sql($sql);
?>
					<font title="Respons�vel pela altera��o: <?=$campos2[0]['login'];?>"><?=$campos[$i]['data_inclusao']?></font>
<?
					}else {
						echo $campos_esp_custo_liberado[$i]['data_inclusao'];
					}
				}
			?>
		</td>
		<td align='left'>
		<?
			//Verifica se existe fornecedor e margem de lucro atrav�s do id_produto_acabado
			$sql = "Select f.id_fornecedor, f.razaosocial, fpi.fator_margem_lucro_pa 
                                from produtos_acabados pa 
                                inner join fornecedores_x_prod_insumos fpi on fpi.id_produto_insumo = pa.`id_produto_insumo` and fpi.ativo = 1 
                                inner join fornecedores f on f.id_fornecedor = fpi.id_fornecedor 
                                where pa.`id_produto_acabado` = ".$campos_esp_custo_liberado[$i]['id_produto_acabado']." 
                                AND pa.`ativo` = '1' ";
			$campos2 = bancos::sql($sql);
			$linhas2 = count($campos2);
			$id_fornecedor_setado = custos::procurar_fornecedor_default_revenda($campos_esp_custo_liberado[$i]['id_produto_acabado'], "", "", 1); //busco somente o id_forncedor default para saber de qual forncedor q estou pegando para calcular o custo do PA revenda
			if($linhas2 > 0) {//Encontrou
				for($j = 0; $j < $linhas2; $j++) {
					echo "<font color='red'>-> </font>";
					if($campos2[$j]['id_fornecedor']==$id_fornecedor_setado) {
						echo "<b title='Fornecedor default'>".$campos2[$j]['razaosocial']."</b>";
					}else {
						echo $campos2[$j]['razaosocial'];
					}
					echo '<br>';
				}
			}else {//N�o encontrou
				echo "<font color='red'>SEM FORNECEDOR</font>";
			}
		?>
		</td>
		<td align='center'>
		<?
			if($linhas2 > 0) {//Encontrou
				for($j = 0; $j < $linhas2; $j++) {
		?>
				<?=number_format($campos2[$j]['fator_margem_lucro_pa'], 2, ',', '.').'<br>';?>
		<?
				}
			}else {//N�o encontrou
		?>
				<font color="red">-</font>
		<?
			}
		?>
		</td>
	</tr>
<?
			}
?>
	<tr class="linhanormal">
		<td colspan='8' bgcolor='#CECECE'>
			<font color='white' size="1">
				<b>Total de Item(ns): 
				<font color='darkblue'>
					<b><?=$linhas_esp_custo_liberado;?></b>
				</font> 
				<font color='black'><b>sem Prazo de Entrega</b></font> nos �ltimos 3 meses
			</font>
		</td>
	</tr>
<?
		}
//Listagem de Itens do SQL Principal ...
		for ($i = 0;  $i < $linhas_principal; $i++) {
?>
	<tr onclick="window.location = 'pa_componente_revenda_esp2.php?id_produto_acabado=<?=$campos_principal[$i]['id_produto_acabado'];?>'" onmouseover="return sobre_celula(this, '#CCFFCC')" onmouseout="return fora_celula(this, '#E8E8E8')" class="linhanormal" align='center'>
            <td>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </td>
		<td align='left'>
			<?=$campos_principal[$i]['nome'];?>
		</td>
		<td>
<?
//Verifica a �ltima qtde desse item em or�amento
			$sql = "Select qtde, id_orcamento_venda 
					from orcamentos_vendas_itens 
					where id_produto_acabado = '".$campos_principal[$i]['id_produto_acabado']."' order by id_orcamento_venda desc limit 1 ";
			$campos2 = bancos::sql($sql);
			if(count($campos2) == 1) {//Achou esse item em algum or�amento
		?>
			<font title="Or�amento N.� <?=$campos2[0]['id_orcamento_venda'];?>">
				<?=number_format($campos2[0]['qtde'], 0, ',', '.');?>
			</font>
		<?
			}else {//N�o achou esse item
		?>
			<font title="Item sem Or�amento">
				0
			</font>
		<?
			}
?>
		</td>
		<td align='left'>
	
		<?
				echo intermodular::pa_discriminacao($campos_principal[$i]['id_produto_acabado']);			
		?>
		</td>
		<td align="center">
		<?
			//Aqui eu verifico os Orc(s) dos �ltimos 3 meses em aberto que cont�m esse PA ...
			$sql = "SELECT DATE_FORMAT(ov.data_emissao, '%d/%m/%Y') AS data_emissao, ovi.id_orcamento_venda, l.login 
					FROM `orcamentos_vendas_itens` ovi 
					INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda AND ov.status < '2' AND ov.data_emissao >= '$tres_meses_atras' 
					INNER JOIN `funcionarios` f ON f.id_funcionario = ov.id_funcionario 
					INNER JOIN `logins` l ON l.id_funcionario = f.id_funcionario 
					WHERE ovi.id_produto_acabado = ".$campos_principal[$i]['id_produto_acabado']." ORDER BY ov.id_orcamento_venda DESC LIMIT 1 ";
			$campos_orcs = bancos::sql($sql);
			$linhas_orcs = count($campos_orcs);
			if($linhas_orcs == 0) {
				echo '<center> - </center>';
			}else {
				echo $campos_orcs[0]['data_emissao'].' | '.$campos_orcs[0]['id_orcamento_venda'].'<font color="blue"> ('.$campos_orcs[0]['login'].')</font><br> ';
			}
		?>
		</td>
		<td align="center">
			<?
				//Se for Diferente de 00/00/0000, ent�o a Data Normal
				if($campos_principal[$i]['data_inclusao'] != '00/00/0000') {
					if($campos_principal[$i]['id_funcionario'] != 0) {
					//Aqui eu busco qual foi o login respons�vel pela Inclus�o ou Altera��o do Prod ...
					$sql = "SELECT l.login 
							FROM `funcionarios` f 
							INNER JOIN `logins` l ON l.id_funcionario = f.id_funcionario 
							WHERE f.id_funcionario = ".$campos_principal[$i]['id_funcionario']." LIMIT 1 ";
					$campos_login = bancos::sql($sql);
			?>
					<font title="Respons�vel pela altera��o: <?=$campos_login[0]['login'];?>"><?=$campos_principal[$i]['data_inclusao']?></font>
			<?
					}else {
						echo $campos_principal[$i]['data_inclusao'];
					}
				}
			?>
		</td>
		<td align='left'>
		<?
			//Verifica se existe fornecedor e margem de lucro atrav�s do id_produto_acabado
			$sql = "Select f.id_fornecedor, f.razaosocial, fpi.fator_margem_lucro_pa 
                                FROM `produtos_acabados` pa 
                                inner join fornecedores_x_prod_insumos fpi on fpi.id_produto_insumo = pa.`id_produto_insumo` AND fpi.ativo = '1' 
                                inner join fornecedores f on f.id_fornecedor = fpi.id_fornecedor 
                                where pa.`id_produto_acabado` = ".$campos_principal[$i]['id_produto_acabado']." 
                                AND pa.`ativo` = '1' ";
			$campos2 = bancos::sql($sql);
			$linhas2 = count($campos2);
			$id_fornecedor_setado = custos::procurar_fornecedor_default_revenda($campos_principal[$i]['id_produto_acabado'], "", "", 1); //busco somente o id_forncedor default para saber de qual forncedor q estou pegando para calcular o custo do PA revenda
			if($linhas2 > 0) {//Encontrou
				for($j = 0; $j < $linhas2; $j++) {
					echo "<font color='red'>-> </font>";
					if($campos2[$j]['id_fornecedor']==$id_fornecedor_setado) {
						echo "<b title='Fornecedor default'>".$campos2[$j]['razaosocial']."</b>";
					}else {
						echo $campos2[$j]['razaosocial'];
					}
					echo '<br>';
				}
			}else {//N�o encontrou
				echo "<font color='red'>SEM FORNECEDOR</font>";
			}
		?>
		</td>
		<td align='center'>
		<?
			if($linhas2 > 0) {//Encontrou
				for($j = 0; $j < $linhas2; $j++) {
		?>
				<?=number_format($campos2[$j]['fator_margem_lucro_pa'], 2, ',', '.').'<br>';?>
		<?
				}
			}else {//N�o encontrou
		?>
				<font color="red">-</font>
		<?	}	?>
		</td>
	</tr>
<?
		}
?>
	<tr class="linhacabecalho" align="center">
		<td colspan='8'>
			<input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'pa_componente_revenda_esp.php'" class='botao'>
			<input type='submit' name='cmd_avan�ar' value='&gt;&gt; Avan�ar &gt;&gt;' title='Avan�ar' class='botao'>
		</td>
	</tr>
</table>
<input type='hidden' name='id_fornecedor' value='<?=$id_fornecedor;?>'>
<input type='hidden' name='razaosocial' value="<?=$razaosocial;?>">
</form>
<center>
	<?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
	}
}else {
?>
<html>
<head>
<title>.:: Consultar Produto(s) Acabado(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
	if(document.form.opcao.checked == true) {
		document.form.opt_opcao.disabled = true
		document.form.txt_consultar.disabled = true
		document.form.txt_consultar.value = ''
	}else {
		document.form.opt_opcao.disabled = false
		document.form.txt_consultar.disabled = false
		document.form.txt_consultar.value = ''
		document.form.txt_consultar.focus()
	}
}

function iniciar() {
	if(document.form.txt_consultar.disabled == false) document.form.txt_consultar.focus()
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
</Script>
</head>
<body onLoad="iniciar()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_fornecedor' value='<?=$id_fornecedor;?>'>
<input type='hidden' name='razaosocial' value='<?=$razaosocial;?>'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
	<tr align='center'>
		<td colspan='2' width="750">
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
    <tr class='linhacabecalho' align='center'>
		<td colspan="2">
			<font color='#FFFFFF' size='-1'>
				Consultar Produto(s) Acabado(s)
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
			<input type="radio" name="opt_opcao" value="1" title="Consultar Produtos Insumos por: Discrimina&ccedil;&atilde;o" onclick="iniciar()" id='label1' checked disabled>
			<label for='label1' title="Consultar Produtos Insumos pela Discrimina&ccedil;&atilde;o">
				Discrimina&ccedil;&atilde;o
			</label>
		</td>
		<td width="20%">
			<input type='checkbox' name='chkt_so_custos_nao_liberados' value='1' title="S� Custos n�o Liberados" class="checkbox" checked id='label2'>
			<label for='label2'>
				S� Custos n�o Liberados
			</label>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="20%">
			<input type='checkbox' name='chkt_depto_tecnico' value='1' title="Consultar todos os DEPTO. T�CNICO" onClick='limpar()' class="checkbox" checked id='label3'>
			<label for='label3' title="Consultar todos os DEPTO. T�CNICO">
				Todos os DEPTO. T�CNICO
			</label>
		</td>
		<td width="20%">
			<input type='checkbox' name='chkt_pas_com_orcamento' value='1' title="Consultar Somente PA(s) com Or�amento" id='label4' class="checkbox" checked>
			<label for='label4'>
				Somente PA(s) com Or�amento
			</label>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan="2">
			<input type='checkbox' name='opcao' value='1' title="Consultar todos os registros" onClick='limpar()' class="checkbox" checked id='label5'>
			<label for='label5' title="Consultar todos os registros">
				Todos os registros
			</label>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="document.form.opcao.checked = true;limpar();" class="botao">
			<input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>