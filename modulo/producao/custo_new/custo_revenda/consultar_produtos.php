<?
require('../../../../lib/segurancas.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/custos_new.php');
segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_revenda/pa_componente_revenda_esp.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
//Significa que veio da tela P.A. Componente (Revenda) P.A. Especial
	if($tipo == 'ESP') {
		if($opt_opcao == 1) {//Significa que foi feito o filtro pela opção de Ref.
			$txt_consultar = 'ESP ';
		}else {//Demais opções
			$condicao = "and pa.referencia = 'ESP' ";
		}
//Significa que veio da tela P.A. Componente (Revenda) P.A. Todos
	}else {
		$condicao = '';
	}
	if(!empty($chkt_so_custos_nao_liberados)) {//Preenchido
		$condicao2 = 'and pa.status_custo = 0';
	}else {//Não Preenchido
		$condicao2 = '';
	}
	switch($opt_opcao) {
		case 1://Referência
			$sql = "Select pa.id_produto_acabado, pa.referencia, pa.discriminacao, pa.status_custo, pa.id_funcionario, date_format(substring(pa.data_sys, 1, 10), '%d/%m/%Y') as data_inclusao, gpa.nome, fpi.id_fornecedor_prod_insumo, fpi.fator_margem_lucro_pa 
				from fornecedores_x_prod_insumos fpi 
				inner join produtos_acabados pa on pa.id_produto_insumo = fpi.id_produto_insumo 
				inner join gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
				inner join grupos_pas gpa on ged.id_grupo_pa = gpa.id_grupo_pa 
				where pa.referencia like '%$txt_consultar%' 
				and fpi.id_fornecedor = '$id_fornecedor' 
				and pa.ativo = 1 
				and pa.operacao_custo = 1 
				and fpi.ativo = 1 $condicao2 order by pa.discriminacao ";
		break;
		case 2://Discriminação
			$sql = "Select pa.id_produto_acabado, pa.referencia, pa.discriminacao, pa.status_custo, pa.id_funcionario, date_format(substring(pa.data_sys, 1, 10), '%d/%m/%Y') as data_inclusao, gpa.nome, fpi.id_fornecedor_prod_insumo, fpi.fator_margem_lucro_pa 
				from fornecedores_x_prod_insumos fpi 
				inner join produtos_acabados pa on pa.id_produto_insumo = fpi.id_produto_insumo 
				inner join gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
				inner join grupos_pas gpa on ged.id_grupo_pa = gpa.id_grupo_pa 
				where pa.discriminacao like '%$txt_consultar%' $condicao 
				and fpi.id_fornecedor = '$id_fornecedor' 
				and pa.ativo = 1 
				and pa.operacao_custo = 1 
				and fpi.ativo = 1 $condicao2 order by pa.discriminacao ";
		break;
		default://Todos
			$sql = "Select pa.id_produto_acabado, pa.referencia, pa.discriminacao, pa.status_custo, pa.id_funcionario, date_format(substring(pa.data_sys, 1, 10), '%d/%m/%Y') as data_inclusao, gpa.nome, fpi.id_fornecedor_prod_insumo, fpi.fator_margem_lucro_pa 
				from fornecedores_x_prod_insumos fpi 
				inner join produtos_acabados pa on pa.id_produto_insumo = fpi.id_produto_insumo 
				inner join gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
				inner join grupos_pas gpa on ged.id_grupo_pa = gpa.id_grupo_pa 
				where fpi.id_fornecedor = '$id_fornecedor' $condicao 
				and pa.ativo = 1 
				and pa.operacao_custo = 1 
				and fpi.ativo = 1 $condicao2 order by pa.discriminacao ";
		break;
	}
	$campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
	$linhas = count($campos);
	if($linhas < 1) {
?>
		<Script Language = 'JavaScript'>
			window.location = 'consultar_produtos.php?passo=0&id_fornecedor=<?=$id_fornecedor;?>&razaosocial=<?=$razaosocial;?>&id_produto_acabado=<?=$id_produto_acabado;?>&tipo=<?=$tipo;?>&valor=1'
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
<Script Language = 'JavaScript' Src = 'cores.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
	var x, mensagem = '', valor = false, elementos = document.form.elements
	for (x = 0; x < elementos.length; x ++)   {
		if (elementos[x].type == 'checkbox')  {
			if (elementos[x].checked == true) {
				valor = true
			}
		}
	}
	if (valor == false) {
		window.alert('SELECIONE UMA OPÇÃO !')
		return false
	}
}
</Script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<form name='form' method='post' action="index.php" onsubmit="return validar()">
<table width='950' border=0 align='center' cellspacing=1 cellpadding=1 onmouseover="total_linhas(this)";>
	<tr>
		<td colspan='6'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-1'>
				<div align='center'>
				</div>
			</font>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan='6'>
			<font color='#FFFFFF' size='-1'>
				Consultar Produto(s) Acabado(s) do Fornecedor: <?=$razaosocial;?>
			</font>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td>
			<font color='#FFFFFF' size='-1'>
				<label for='todos'>Itens </label>
				<input type="checkbox" name="chkt" onClick="return selecionar('form', 'chkt', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox" id='todos'>
			</font>
		</td>
		<td>
			<font size="-1">
				Grupo / Refência
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Discriminação
			</font>
		</td>
		<td>
			<font color='#FFFFFF' title="Data de Inclusão" size='-1'>
				Data Inc
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				<p title="Fator Margem de Lucro">F. M. L</p>
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				<p title="Quantidade de Pçs. / Embalagem">Qtde Pçs. / Emb</p>
			</font>
		</td>
	</tr>
<?
		for ($i=0;$i<$linhas;$i++) {
			/*Significa que veio da tela P.A. Componente (Revenda) P.A. Especial e
			compara o produto acabado passado por parâmetro com o do loop*/
			$id_fornecedor_setado=custos::procurar_fornecedor_default_revenda($campos[$i]['id_produto_acabado'], "", "", 1); //busco somente o id_forncedor default para saber de qual forncedor q estou pegando para calcular o custo do PA revenda
			if($tipo == 'ESP' && ($id_produto_acabado == $campos[$i]['id_produto_acabado'])) { ?>
	<tr align="center" class="linhasub" onclick="checkbox_2('form', 'chkt','<?=$i;?>', '#C6E2FF', '#E8E8E8')" onmouseover="return sobre_celula_2(this, '#CCFFCC')" onmouseout="return fora_celula(this, '#E8E8E8')">
		<td bgcolor='<?=$color;?>'>
			<input type="checkbox" name="chkt_fornecedor_prod_insumo[]" value="<?=$campos[$i]['id_fornecedor_prod_insumo'];?>" onclick="checkbox('form', 'chkt','<?=$i;?>', '#E8E8E8')" <?=$checked;?> class="checkbox" checked>
		</td>
		<td align='left' bgcolor='<?=$color;?>'>
			<?=$campos[$i]['nome'].' / '.$campos[$i]['referencia'];?>
		</td>
		<td align='left' bgcolor='<?=$color;?>'>
		<? 
			if($campos[$i]['status_custo'] == 1) {//Já está liberado
				if($id_fornecedor==$id_fornecedor_setado) {
					echo "<font title='Custo Liberado'><b>".$campos[$i]['discriminacao']."</b></font>";
				} else {
					echo "<font title='Custo Liberado'>".$campos[$i]['discriminacao']."</font>";
				}
			}else {//Não está liberado
				if($id_fornecedor==$id_fornecedor_setado) {
					echo "<font title='Custo não Liberado' color='red'><b>".$campos[$i]['discriminacao']."</b></font>";
				} else {
					echo "<font title='Custo não Liberado' color='red'>".$campos[$i]['discriminacao']."</font>";
				}
			}
		?>
		</td>
		<td align="center">
			<?
			//Se for Diferente de 00/00/0000, então a Data Normal
				if($campos[$i]['data_inclusao'] != '00/00/0000') {
					if($campos[$i]['id_funcionario'] != 0) {
//Aqui eu busco qual foi o login responsável pela Inclusão ou Alteração do Prod
					$sql = "Select l.login from funcionarios f, logins l where f.id_funcionario = ".$campos[$i]['id_funcionario']." and f.id_funcionario = l.id_funcionario limit 1";
					$campos2 = bancos::sql($sql);
?>
					<font title="Responsável pela alteração: <?=$campos2[0]['login'];?>"><?=$campos[$i]['data_inclusao']?></font>
<?
					}else {
						echo $campos[$i]['data_inclusao'];
					}
				}
			?>
		</td>
		<td align='center' bgcolor='<?=$color;?>'>
			<?=number_format($campos[$i]['fator_margem_lucro_pa'], 2, ',', '.');?>
		</td>
		<td align='left'>
		<?
			$sql = "Select pi.discriminacao, ppe.pecas_por_emb, ppe.embalagem_default from produtos_insumos pi, pas_vs_pis_embs ppe where ppe.id_produto_acabado = ".$campos[$i]['id_produto_acabado']." and ppe.id_produto_insumo = pi.id_produto_insumo order by pi.discriminacao ";
			$campos2 = bancos::sql($sql);
			$linhas2 = count($campos2);
			if($linhas2 > 0) {
				for($j = 0; $j < $linhas2; $j++) {
					if($campos2[$j]['embalagem_default'] == 1) {//Principal
				?>
					<img src="../../../../imagem/certo.gif">
					<font title="Embalagem Principal">
				<?
						echo '<b>* </b>'.$campos2[$j]['pecas_por_emb'].' - '.$campos2[$j]['discriminacao'].'<br>';
				?>
					</font>
				<?
					}else {
						echo '<b>* </b>'.$campos2[$j]['pecas_por_emb'].' - '.$campos2[$j]['discriminacao'].'<br>';
				?>
						<!--<font color="red">
							<b>* </b><?=$campos2[$j]['pecas_por_emb'].' - '.$campos2[$j]['discriminacao'];?><br>
						</font>-->
				<?
					}
				}
			}else {
				echo '<p align="center">&nbsp;-&nbsp;</p>';
			}
		?>
		</td>
	</tr>
<?			} else { //Significa que veio da tela P.A. Componente (Revenda) P.A. Todos ?>
	<tr class="linhanormal" onclick="checkbox('form', 'chkt','<?=$i;?>', '#E8E8E8')" onmouseover="return sobre_celula(this, '#CCFFCC')" onmouseout="return fora_celula(this, '#E8E8E8')" align='center'>
		<td bgcolor='<?=$color;?>'>
			<input type="checkbox" name="chkt_fornecedor_prod_insumo[]" value="<?=$campos[$i]['id_fornecedor_prod_insumo'];?>" onclick="checkbox('form', 'chkt','<?=$i;?>', '#E8E8E8')" <?=$checked;?> class="checkbox">
		</td>
		<td align='left' bgcolor='<?=$color;?>'>
			<?=$campos[$i]['nome'].' / '.$campos[$i]['referencia'];?>
		</td>
		<td align='left' bgcolor='<?=$color;?>'>
<?
				if($id_fornecedor==$id_fornecedor_setado) {
					echo "<b>".intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0)."</b>";
				} else {
					echo intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0);
				}
?>
		</td>
		<td align="center">
			<?
			//Se for Diferente de 00/00/0000, então a Data Normal
				if($campos[$i]['data_inclusao'] != '00/00/0000') {
					echo $campos[$i]['data_inclusao'];
				}
			?>
		</td>
		<td align='center' bgcolor='<?=$color;?>'>
			<?=number_format($campos[$i]['fator_margem_lucro_pa'], 2, ',', '.');?>
		</td>
		<td align='left'>
		<?
			$sql = "Select pi.discriminacao, ppe.pecas_por_emb, ppe.embalagem_default from produtos_insumos pi, pas_vs_pis_embs ppe where ppe.id_produto_acabado = ".$campos[$i]['id_produto_acabado']." and ppe.id_produto_insumo = pi.id_produto_insumo order by pi.discriminacao ";
			$campos2 = bancos::sql($sql);
			$linhas2 = count($campos2);
			if($linhas2 > 0) {
				for($j = 0; $j < $linhas2; $j++) {
					if($campos2[$j]['embalagem_default'] == 1) {//Principal
				?>
					<img src="../../../../imagem/certo.gif">
					<font title="Embalagem Principal">
				<?
						echo '<b>* </b>'.$campos2[$j]['pecas_por_emb'].' - '.$campos2[$j]['discriminacao'].'<br>';
				?>
					</font>
				<?
					}else {
						echo '<b>* </b>'.$campos2[$j]['pecas_por_emb'].' - '.$campos2[$j]['discriminacao'].'<br>';
				?>
						<!--<font color="red">
							<b>* </b><?=$campos2[$j]['pecas_por_emb'].' - '.$campos2[$j]['discriminacao'];?><br>
						</font>-->
				<?
					}
				}
			}else {
				echo '<p align="center">&nbsp;-&nbsp;</p>';
			}
		?>
		</td>
	</tr>
<?
			}
		}
?>
	<tr class="linhacabecalho" align="center">
		<td colspan='6'>
			<input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar_produtos.php?passo=0&id_fornecedor=<?=$id_fornecedor;?>&razaosocial=<?=$razaosocial;?>&id_produto_acabado=<?=$id_produto_acabado;?>&tipo=<?=$tipo;?>'" class='botao'>
			<input type='submit' name='cmd_avançar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' class='botao'>
		</td>
	</tr>
</table>
<input type='hidden' name='id_fornecedor' value='<?=$id_fornecedor;?>'>
<input type='hidden' name='razaosocial' value='<?=$razaosocial;?>'>
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<input type='hidden' name='tipo' value='<?=$tipo;?>'>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
	}
}else {
//Significa que veio da tela P.A. Componente (Revenda) P.A. Especial
	if($tipo == 'ESP') {
		$referencia = '(ESP)';
		$endereco = 'pa_componente_revenda_esp2.php?id_produto_acabado='.$id_produto_acabado;
//Significa que veio da tela P.A. Componente (Revenda) P.A. Todos
	}else {
		$referencia = '';
		$endereco = 'pa_componente_revenda_todos.php?passo=1&parametro='.$parametro;
	}
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
	document.form.txt_consultar.focus()
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
<body onLoad="return iniciar()";>
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_fornecedor' value='<?=$id_fornecedor;?>'>
<input type='hidden' name='razaosocial' value='<?=$razaosocial;?>'>
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<input type='hidden' name='tipo' value='<?=$tipo;?>'>
<input type='hidden' name='endereco' value='<?=$endereco;?>'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
	<tr align='center'>
		<td colspan='2' width="750">
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
    <tr class='linhacabecalho' align='center'>
		<td colspan="2">
			<font color='#FFFFFF' size='-1'>
				Consultar Produto(s) Acabado(s) do Fornecedor: <?=$razaosocial;?>
			</font>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan='2'>
			<div align='center'>Consultar
				<input type="text" name="txt_consultar" size='45' maxlength='45' class="caixadetexto">
			</div>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="1" title="Consultar Produtos Insumos por: Referência" onclick="return iniciar()" id='label'>
			<label for='label' title="Consultar Produtos Insumos pela Refer&ecirc;ncia">
				Referência <?=$referencia;?>
			</label>
		</td>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="2" title="Consultar Produtos Insumos por: Discrimina&ccedil;&atilde;o" onclick="return iniciar()"; id='label2' checked>
			<label for='label2' title="Consultar Produtos Insumos pela Discrimina&ccedil;&atilde;o">
				Discrimina&ccedil;&atilde;o
			</label>
		</td>
	</tr>
	<tr class="linhanormal">
		<td width="20%">
			<input type='checkbox' name='chkt_so_custos_nao_liberados' value='1' tabindex='3' title="Só Custos não Liberados" class="checkbox" id='label3'>
			<label for='label3'>
				Só Custos não Liberados
			</label>
		</td>
		<td width="20%">
			<input type='checkbox' name='opcao' onClick='limpar()' value='3' tabindex='3' title="Consultar todos os Produtos Insumos" class="checkbox" id='label4'>
			<label for='label4' title ="Consultar todos os produtos acabados">
				Todos os registros
			</label>
		</td>
	</tr>
	<tr align="center">
		<td colspan="2" class="linhacabecalho">
			<input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = '<?=$endereco;?>'" class="botao">
			<input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="document.form.opcao.checked = false;limpar();" class="botao">
			<input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
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

<b>* Tipo PI (PRAC) que estejam relacionados com o PIPA;</b>
<b>* Tipo de O.C. = Revenda;</b>
<b>* Fornecedor selecionado que estejam ativos.</b>
</pre>
