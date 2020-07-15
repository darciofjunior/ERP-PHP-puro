<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/intermodular.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
	if(!empty($chkt_pa_migrado)) {//Preenchido
		$condicao_pa_migrado = '';
	}else {//N�o Preenchido
		$condicao_pa_migrado = ' and pa.pa_migrado = 0';
	}

	if(!empty($chkt_so_custos_nao_liberados)) {//Preenchido
		$condicao = ' and pa.status_custo = 0';
	}else {//N�o Preenchido
		$condicao = '';
	}
	global $sql, $tela;
	$tela = 1;
	switch($opt_opcao) {
		case 1:
			$sql = "Select pa.*, date_format(substring(pa.data_sys, 1, 10), '%d/%m/%Y') as data_inclusao, ed.razaosocial, gpa.nome, u.unidade 
					from empresas_divisoes ed, gpas_vs_emps_divs ged, grupos_pas gpa, produtos_acabados pa, unidades u 
					where pa.referencia like '%$txt_consultar%' 
					and pa.operacao_custo = 0 
					and pa.ativo = 1 
					and pa.id_gpa_vs_emp_div = ged.id_gpa_vs_emp_div 
					and ged.id_grupo_pa = gpa.id_grupo_pa 
					and ged.id_empresa_divisao = ed.id_empresa_divisao 
					and pa.id_unidade = u.id_unidade $condicao $condicao_pa_migrado order by pa.referencia ";
		break;
		case 2:
			$sql = "Select pa.*, date_format(substring(pa.data_sys, 1, 10), '%d/%m/%Y') as data_inclusao, ed.razaosocial, gpa.nome, u.unidade 
					from empresas_divisoes ed, gpas_vs_emps_divs ged, grupos_pas gpa, produtos_acabados pa, unidades u 
					where pa.discriminacao like '%$txt_consultar%' 
					and pa.operacao_custo = 0 
					and pa.ativo = 1 
					and pa.id_gpa_vs_emp_div = ged.id_gpa_vs_emp_div 
					and ged.id_grupo_pa = gpa.id_grupo_pa 
					and ged.id_empresa_divisao = ed.id_empresa_divisao 
					and pa.id_unidade = u.id_unidade $condicao $condicao_pa_migrado order by pa.discriminacao ";
		break;
		case 3:
			$sql = "Select pa.*, date_format(substring(pa.data_sys, 1, 10), '%d/%m/%Y') as data_inclusao, ed.razaosocial, gpa.nome, u.unidade 
					from empresas_divisoes ed, gpas_vs_emps_divs ged, grupos_pas gpa, produtos_acabados pa, unidades u 
					where ed.razaosocial like '%$txt_consultar%' 
					and pa.operacao_custo = 0 
					and pa.ativo = 1 
					and pa.id_gpa_vs_emp_div = ged.id_gpa_vs_emp_div 
					and ged.id_grupo_pa = gpa.id_grupo_pa 
					and ged.id_empresa_divisao = ed.id_empresa_divisao 
					and pa.id_unidade = u.id_unidade $condicao $condicao_pa_migrado order by ed.razaosocial ";
		break;
		default:
			$sql = "Select pa.*, date_format(substring(pa.data_sys, 1, 10), '%d/%m/%Y') as data_inclusao, ed.razaosocial, gpa.nome, u.unidade 
					from empresas_divisoes ed, gpas_vs_emps_divs ged, grupos_pas gpa, produtos_acabados pa, unidades u 
					where pa.operacao_custo = 0 
					and pa.ativo=1 
					and pa.id_gpa_vs_emp_div = ged.id_gpa_vs_emp_div 
					and ged.id_grupo_pa = gpa.id_grupo_pa 
					and ged.id_empresa_divisao = ed.id_empresa_divisao 
					and pa.id_unidade = u.id_unidade $condicao $condicao_pa_migrado order by pa.discriminacao ";
		break;
	}
//Significa que foi solicitado o relat�rio
	if($modo_relatorio == 1) {
		require('relatorio_pa_componente.php');
		exit;
	}
	$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
	$linhas = count($campos);
	if($linhas < 1) {
?>
		<Script Language = 'JavaScript'>
			window.location = 'pa_componente_todos.php?valor=1'
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
//Quando passo o modo_relatorio = 1, significa que � para exibir do modo relat�rio
	window.location = 'pa_componente_todos.php<?=$parametro;?>&modo_relatorio=1'
}
</Script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<table width='1200' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)";>
	<tr></tr>
	<tr class="linhacabecalho" align="center">
		<td colspan='12' height="21">
			<font color='#FFFFFF' size='-1'>
				Consultar Produtos Acabados
			</font>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td colspan="2">
			<font color='#FFFFFF' size='-1'>
				<p title="Grupo P.A. (Empresa Divis�o)">Grupo P.A. (E.D.)</p>
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Produto
			</font>
		</td>
		<td>
			<font color='#FFFFFF' title="Data de Inclus�o" size='-1'>
				Data Inc
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1' title="Quantidade em Estoque">
				Qtde<br> Estoque
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1' title="Quantidade em Produ��o">
				Qtde<br> Produ��o
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				<p title="Opera��o de Custo">O. C.</p>
			</font>
		</td>
		<td>
                    Origem - ST
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				<p title="Opera��o (Fat)">O. F.</p>
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				<p title="Peso Unit�rio">P. U.</p>
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				<p title="Quantidade de P�s. / Embalagem">Qtde P�s. / Emb</p>
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Observa��o
			</font>
		</td>
	</tr>
<?
		for($i = 0; $i < $linhas; $i++) {
                    $dados_produto = intermodular::dados_impostos_pa($campos[$i]['id_produto_acabado']);
                    
                    
			$url = "javascript:window.location = 'prod_acabado_componente2.php?tela=".$tela."&id_produto_acabado=".$campos[$i]['id_produto_acabado']."&parametro=".$parametro."'";
?>
	<tr class="linhanormal" onclick="return cor_clique_celula(this, '#C6E2FF')" onmouseover="return sobre_celula(this, '#CCFFCC')" onmouseout="return fora_celula(this, '#E8E8E8')">
		<td onclick="<?=$url;?>">
                    <a href="#">
                        <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                    </a>
		</td>
		<td onclick="<?=$url;?>">
			<a href="#">
				<?=$campos[$i]['nome'].' / '.$campos[$i]['razaosocial'];?>
			</a>
		</td>
		<td>
		<?
			echo intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);
//Aki � a Marca��o de PA Migrado
			if($campos[$i]['pa_migrado'] == 1) {
				echo '<font color="red" title="PA MIGRADO" style="cursor:help"><b>MIG</b></font>';
			}
		?>
		</td>
		<td align="center">
			<?
			//Se for Diferente de 00/00/0000, ent�o a Data Normal
				if($campos[$i]['data_inclusao'] != '00/00/0000') {
					if($campos[$i]['id_funcionario'] != 0) {
//Aqui eu busco qual foi o login respons�vel pela Inclus�o ou Altera��o do Prod
					$sql = "Select l.login from funcionarios f, logins l where f.id_funcionario = ".$campos[$i]['id_funcionario']." and f.id_funcionario = l.id_funcionario limit 1";
					$campos2 = bancos::sql($sql);
?>
					<font title="Respons�vel pela altera��o: <?=$campos2[0]['login'];?>"><?=$campos[$i]['data_inclusao']?></font>
<?
					}else {
						echo $campos[$i]['data_inclusao'];
					}
				}
			?>
		</td>
		<?
//Aqui eu trago a qtde em Estoque e a qtde em Produ��o
			$sql = "Select qtde, qtde_producao 
					from estoques_acabados 
					where id_produto_acabado = ".$campos[$i]['id_produto_acabado']." limit 1 ";
			$campos2 = bancos::sql($sql);
			if(count($campos2) == 1) {
				$estoque = $campos2[0]['qtde'];
				$producao = $campos2[0]['qtde_producao'];
			}else {
				$estoque = 0;
				$producao = 0;
			}
		?>
		<td align="center">
			<?=number_format($estoque, 2, ',', '.');?>
		</td>
		<td align="center">
			<?=number_format($producao, 2, ',', '.');?>
		</td>
		<td align="center">
		<?
			if($campos[$i]['operacao_custo'] == 0) {
				echo '<font title="Industrial" style="cursor:help">I</font>';
//Se a Opera��o de Custo for Industrial, ent�o eu apresento a Sub-Opera��o de Custo do PA ...
				if($campos[$i]['operacao_custo_sub'] == 0) {
					echo '-<font title="Industrial" style="cursor:help">I</font>';
				}else if($campos[$i]['operacao_custo_sub'] == 1) {
					echo '-<font title="Revenda" style="cursor:help">R</font>';
				}else {
					echo '-';
				}
			}else if($campos[$i]['operacao_custo'] == 1) {
				echo '<font title="Revenda" style="cursor:help">R</font>';
			}else {
				echo '-';
			}
		?>
		</td>
		<td align='center'>
                    <?=$campos[$i]['origem_mercadoria'].$dados_produto['situacao_tributaria'];?>
		</td>
		<td align="center">
		<?
			if($campos[$i]['operacao'] == 0) {
		?>
			<p title="Industrializa��o (c/ IPI)">I - C</p>
		<?
			}else {
		?>
			<p title="Revenda (s/ IPI)">R - S</p>
		<?
			}
		?>
		</td>
		<td align='right'>
			<?=number_format($campos[$i]['peso_unitario'], 3, ',', '.');?>
		</td>
		<td>
		<?
			$sql = "Select pi.discriminacao, ppe.pecas_por_emb, ppe.embalagem_default 
					from produtos_insumos pi, pas_vs_pis_embs ppe 
					where ppe.id_produto_acabado = ".$campos[$i]['id_produto_acabado']." 
					and ppe.id_produto_insumo = pi.id_produto_insumo order by pi.discriminacao ";
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
		<td align='left'>
			<?=$campos[$i]['observacao'];?>
		</td>
	</tr>
<?
		}
?>
	<tr class="linhacabecalho" align="center">
		<td colspan='12'>
			<input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = '<?=$PHP_SELF;?>'" class="botao">
			<input type="button" name="cmd_modo_relatorio" value="Modo Relat�rio" title="Modo Relat�rio" onclick="modo_relatorio()" class="botao">
		</td>
	</tr>
</table>
<center>
	<?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<pre>
<font color='red'><b>Observa��o:</b></font>

<font><b>Discrimina��o </b></font>-> Custo(s) Liberado(s)
<font color='red'><b>Discrimina��o </b></font>-> Custo(s) n�o Liberado(s)
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
		for(i = 0; i < 3; i ++) {
			document.form.opt_opcao[i].disabled = true
		}
		document.form.txt_consultar.disabled = true
		document.form.txt_consultar.value = ''
	}else {
		for(i = 0; i < 3;i ++) {
			document.form.opt_opcao[i].disabled = false
		}
		document.form.txt_consultar.disabled = false
		document.form.txt_consultar.value = ''
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
<body onLoad="document.form.txt_consultar.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
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
	<tr class='linhanormal'>
		<td colspan='2'>
			<div align='center'>Consultar
				<input type="text" name="txt_consultar" size=45 maxlength=45 class="caixadetexto">
			</div>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="1" onclick="document.form.txt_consultar.focus()" title="Consultar Produtos Acabados por: Refer�ncia" id='label'>
			<label for='label'>
				Refer�ncia
			</label>
		</td>
		<td width="20%">
			<input type="radio" name="opt_opcao" checked value="2" onClick="document.form.txt_consultar.focus()" title="Consultar Produtos Acabados por: Discrimina��o" id='label2'>
			<label for='label2'>
				Discrimina&ccedil;&atilde;o
			</label>
		</td>
	</tr>
	<tr class="linhanormal">
		<td width="20%">
			<input type="radio" name="opt_opcao" value="3" onClick="document.form.txt_consultar.focus()" title="Consultar Produtos Acabados por: Empresa Divis�o" id='label3'>
			<label for='label3'>
				Empresa Divis�o
			</label>
		</td>
		<td width="20%">
			<input type='checkbox' name='chkt_so_custos_nao_liberados' value='1' title="S� Custos n�o Liberados" class="checkbox" id='label4'>
			<label for='label4'>
				S� Custos n�o Liberados
			</label>
		</td>
	</tr>
	<tr class="linhanormal">
		<td width="20%">
			<input type='checkbox' name='chkt_pa_migrado' value='1' title="Incluir P.A. Migrado" class="checkbox" id='label5' checked>
			<label for='label5'>
				Incluir P.A. Migrado
			</label>
		</td>
		<td width="20%">
			<input type='checkbox' name='opcao' onClick='limpar()' value='1' title="Consultar todos os Produtos Acabados" class="checkbox" id='label6'>
			<label for='label6'>
				Todos os registros
			</label>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = false;limpar();" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>
<pre>
<font color='red'><b>Observa��o:</b></font>

* Traz somente P.A(s) do:

<b>* Tipo de O.C. = Industrializado.</b>
</pre>