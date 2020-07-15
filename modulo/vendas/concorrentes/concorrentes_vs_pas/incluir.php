<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/data.php');
session_start('funcionarios');
segurancas::geral('/erp/albafer/modulo/vendas/concorrentes/concorrentes.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>PA(S) INCLUÍDO(S) COM SUCESSO P/ ESTE CONCORRENTE.</font>";
$mensagem[3] = "<font class='confirmacao'>ALGUM(NS) PA(S) FORAM INCLUÍDO(S) E OUTRO(S) JÁ EXISTIA(M) P/ ESTE CONCORRENTE.</font>";
$mensagem[4] = "<font class='erro'>P.A(S) JÁ EXISTENTE(S) P/ ESTE CONCORRENTE.</font>";
$mensagem[5] = "<font class='confirmacao'>CONCORRENTE DESATRELADO COM SUCESSO P/ ESTE PA.</font>";

if($passo == 1) {
//Tratamento com as variáveis que vem por parâmetro ...
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$chkt_mostrar_especiais = $_POST['chkt_mostrar_especiais'];
		$txt_referencia = $_POST['txt_referencia'];
		$txt_discriminacao = $_POST['txt_discriminacao'];
		$cmb_gpa_vs_emp_div = $_POST['cmb_gpa_vs_emp_div'];
		$id_concorrente = $_POST['id_concorrente'];
		$id_concorrente_prod_acabado = $_POST['id_concorrente_prod_acabado'];
	}else {
		$chkt_mostrar_especiais = $_GET['chkt_mostrar_especiais'];
		$txt_referencia = $_GET['txt_referencia'];
		$txt_discriminacao = $_GET['txt_discriminacao'];
		$cmb_gpa_vs_emp_div = $_GET['cmb_gpa_vs_emp_div'];
		$id_concorrente = $_GET['id_concorrente'];
		$id_concorrente_prod_acabado = $_GET['id_concorrente_prod_acabado'];
	}
/*****************************************************************************************************/
//Aqui eu desatrelo o Concorrente do PA ...
	if(!empty($id_concorrente_prod_acabado)) {
		$sql = "Delete from `concorrentes_vs_prod_acabados` where `id_concorrente_prod_acabado` = '$id_concorrente_prod_acabado' limit 1 ";
		bancos::sql($sql);
		$valor = 5;
	}
/*Aqui eu tenho esse Tratamento devido com o % e |, devido o usuário utilizar o % 
como caracter ...*/
	$txt_discriminacao = str_replace('|', '%', $txt_discriminacao);
	if(empty($cmb_gpa_vs_emp_div)) { $cmb_gpa_vs_emp_div = "%"; }
//Se essa opção estiver desmarcada, então eu só mostro os P.A(s) que são do Tipo normais de Linha ...
	if(empty($chkt_mostrar_especiais)) {
		$condicao_esp = " and pa.referencia <> 'ESP' ";
	}
//Busca de Todos os PA(s) com exceção dos que já foram atrelados p/ esse Concorrente ...
	$sql = "Select ged.id_empresa_divisao as id_empresa_divisao_produto, pa.id_produto_acabado, pa.referencia, pa.discriminacao, pa.operacao_custo, pa.preco_promocional 
			from produtos_acabados pa 
			inner join gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div and ged.id_gpa_vs_emp_div like '$cmb_gpa_vs_emp_div' 
			inner join grupos_pas gpa on gpa.id_grupo_pa = ged.id_grupo_pa 
			where pa.referencia like '%$txt_referencia%' 
			and pa.discriminacao like '%$txt_discriminacao%' 
			and gpa.id_familia <> 23 
			and pa.ativo = 1 
			and pa.id_produto_acabado not in (
/*Aqui eu trago todos os PA(s) já atrelados p/ esse Concorrente ...*/
				Select id_produto_acabado 
				from `concorrentes_vs_prod_acabados` 
				where id_concorrente = '$id_concorrente' 
			)
			$condicao_esp order by pa.referencia ";
	$campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
	$linhas = count($campos);
	
	if($linhas == 0) {//Não encontrou nenhum registro ...
?>
	<Script Language = 'Javascript'>
		window.location = 'incluir.php?id_concorrente=<?=$id_concorrente;?>&valor=1'
	</Script>
<?	
	}else {
?>
<html>
<head>
<title>.:: Atrelar PA(S) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function desatrelar_concorrente(id_concorrente_prod_acabado) {
	var mensagem = confirm('DESEJA REALMENTE DESATRELAR ESTE CONCORRENTE DESTE PA ?')
	if(mensagem == false) {
		return false
	}else {
		window.location = 'incluir.php<?=$parametro;?>&passo=1&id_concorrente=<?=$id_concorrente;?>&id_concorrente_prod_acabado='+id_concorrente_prod_acabado
	}
}

function validar() {
	var x, mensagem = '', valor = false, elementos = document.form.elements
	for (x = 0; x < elementos.length; x ++) {
		if (elementos[x].type == 'checkbox')  {
			if (elementos[x].checked == true) {
				valor = true
			}
		}
	}
	if (valor == false) {
		window.alert('SELECIONE UMA OPÇÃO !')
		return false
	}else {
//Aqui é para não atualizar a Tela de Baixo quando eu enviar esses PA(s) p/ o Banco de Dados ...
		document.form.nao_atualizar.value = 1
		return true
	}
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
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4' onunload="atualizar_abaixo()">
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit="return validar()">
<table width='700' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
	<tr align='center'>
		<td colspan='2'>
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan='2'>
			<font color='#FFFFFF' size='-1'>
				Atrelar PA(S)
			</font>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td>
			<font color='#FFFFFF' size='-1'>
				<label for="todos">Todos</label>
				<input type='checkbox' name='chkt' id="todos" onClick="selecionar('form', 'chkt', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox">
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Produto
			</font>
		</td>
	</tr>
<?
		for($i = 0; $i < $linhas; $i++) {
?>
	<tr class="linhanormal" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" onmouseover="return sobre_celula(this, '#CCFFCC')" onmouseout="return fora_celula(this, '#E8E8E8')" align"center">
		<td align="center">
			<input type='checkbox' name='chkt_produto_acabado[]' value="<?=$campos[$i]['id_produto_acabado'];?>" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
		</td>
		<td>
		<?
			echo intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0);
//Busca dos Concorrentes atrelados ao Produto Acabado ...
			$sql = "Select cpa.id_concorrente_prod_acabado, cc.nome 
					from concorrentes_vs_prod_acabados cpa 
					inner join concorrentes cc on cc.id_concorrente = cpa.id_concorrente 
					where cpa.id_produto_acabado = '".$campos[$i]['id_produto_acabado']."' ";
			$campos_concorrentes = bancos::sql($sql);
			$linhas_concorrentes = count($campos_concorrentes);
			for($j = 0; $j < $linhas_concorrentes; $j++) {
				if($j == 0) {echo '<br>';}
				echo '<font color="darkblue"><b>'.$campos_concorrentes[$j]['nome'].'</b></font>';
		?>
				<img src="../../../../imagem/menu/excluir.png" border='0' onClick="desatrelar_concorrente('<?=$campos_concorrentes[$j]['id_concorrente_prod_acabado'];?>')" alt="Excluir Concorrente" title="Excluir Concorrente">
		<?
				echo '&nbsp;&nbsp;&nbsp;&nbsp;';
			}
		?>
		</td>
	</tr>
<?
		}
?>
	<tr class="linhacabecalho" align="center">
		<td colspan='2'>
			<input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'incluir.php?id_concorrente=<?=$id_concorrente;?>'" class="botao">
			<input type="submit" name="cmd_atrelar" value="Atrelar" title="Atrelar" style="color:green" class="botao">
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="window.close()" class="botao">
		</td>
	</tr>
</table>
<input type='hidden' name='id_concorrente' value="<?=$id_concorrente;?>">
<input type='hidden' name='nao_atualizar'>
</form>
<center>
	<?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
	}
}else if($passo == 2) {
	$data_sys = date('Y-m-d H:i:s');
	$excedido_limite = 0;
//Aqui eu verifico todos os PA(s) que estão selecionados ...
	for($i = 0; $i < count($_POST['chkt_produto_acabado']); $i++) {
/*Um PA só pode ser incluso p/ até 3 concorrentes no máximo, se passar disso, então é necessário excluir 
algum Concorrente desse PA p/ que libere uma vaga p/ a inclusão de um outro Concorrente ...

Verifico se esse PA já possui 3 concorrentes ...***/
		$sql = "Select id_concorrente_prod_acabado 
				from `concorrentes_vs_prod_acabados` 
				where id_produto_acabado = '".$_POST['chkt_produto_acabado'][$i]."' 
				and ativo = '1' ";
		$campos = bancos::sql($sql);
		if(count($campos) == 3) {//Não posso estar mais incluindo esse PA p/ um 4º Concorrente ...
			$excedido_limite++;
		}else {//Pode incluir normalmente ...
//Verifico se o PA selecionado corrente está atrelado p/ o Concorrente em questão ...
			$sql = "Select id_concorrente_prod_acabado 
					from `concorrentes_vs_prod_acabados` 
					where `id_concorrente` = '$id_concorrente' 
					and id_produto_acabado = '".$_POST['chkt_produto_acabado'][$i]."' and ativo = '1' limit 1 ";
			$campos = bancos::sql($sql);
			if(count($campos) == 0) {
				$insert_extend.= " ('$id_concorrente', '".$_POST['chkt_produto_acabado'][$i]."', '$data_sys'), ";
				$incluidos++;
			}else {
				$nao_incluidos++;
			}
		}
	}

	if(!empty($insert_extend)) {
		$insert_extend = substr($insert_extend, 0, strlen($insert_extend) - 2).';';
		$sql = "INSERT INTO `concorrentes_vs_prod_acabados` (`id_concorrente`, `id_produto_acabado`, `data_sys_ult_alt`) values ".$insert_extend;
		bancos::sql($sql);
	}
/*********************Controle p/ Retornar a Mensagem*********************/	
//Significa que todo(s) o(s) PA(s) foram incluidos devido já existir o(s) mesmo(s) p/ o Concorrente ...
	if($incluidos != 0 && $nao_incluidos == 0) {
		$valor = 2;
//Significa que alguns PA(s) foram incluídos e outros não devido já existir o(s) mesmo(s) p/ o Concorrente ...
	}else if($incluidos != 0 && $nao_incluidos != 0) {
		$valor = 3;
//Significa que nenhum PA foi incluido devido já existir o(s) mesmo(s) p/ o Concorrente ...
	}else if($incluidos == 0 && $nao_incluidos != 0) {
		$valor = 4;
	}
/*************************************************************************/
?>
	<Script Language = 'Javascript'>
		var excedido_limite = eval('<?=$excedido_limite;?>')
//Caso excedeu o limite de Atrelamento de um PA p/ um Concorrente, então retorno esse Alert ...
		if(excedido_limite > 0) {
			alert('EXISTE(M) PA(S) QUE NÃO PODE(M) SER ATRELADO(S), DEVIDO JÁ ESTAR ATRELADO(S) P/ 3 CONCORRENTE(S) AO MESMO TEMPO !')
		}
		//window.location = 'incluir.php?id_concorrente=<?=$id_concorrente;?>&valor=<?=$valor;?>'
		window.location = 'incluir.php<?=$parametro;?>&valor=<?=$valor;?>'
	</Script>
<?
}else {
?>
<html>
<head>
<title>.:: Atrelar PA(S) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript'>
//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
	if(document.form.nao_atualizar.value == 0) {
		window.opener.document.form.submit()
	}
}
</Script>
</head>
<body onLoad="document.form.txt_referencia.focus()" onunload="atualizar_abaixo()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="document.form.nao_atualizar.value = 1">
<input type='hidden' name='id_concorrente' value="<?=$id_concorrente;?>">
<input type='hidden' name='passo' value="1">
<input type='hidden' name='nao_atualizar'>
<table border="0" width="70%" align="center" cellspacing='1' cellpadding='1'>
	<tr align='center'>
		<td colspan='2' width="750">
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class='linhacabecalho' align='center'>
		<td colspan="2">
			<font color='#FFFFFF' size='-1'>
				Atrelar PA(S)
			</font>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			Referência
		</td>
		<td>
			<input type="text" name="txt_referencia" title="Digite a Referência" class="caixadetexto">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			Discriminação
		</td>
		<td>
			<input type="text" name="txt_discriminacao" title="Digite a Discriminação" size="30" class="caixadetexto">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			Grupo P.A. (Empresa Divisão)
		</td>
		<td>
			<select name="cmb_gpa_vs_emp_div" title="Selecione o Grupo P.A. (Empresa Divisão)" class="combo">
			<?
				$sql = "Select ged.id_gpa_vs_emp_div, concat(gpa.nome, ' (', ed.razaosocial, ') ') as rotulo 
						from gpas_vs_emps_divs ged 
						inner join grupos_pas gpa on gpa.id_grupo_pa = ged.id_grupo_pa 
						inner join empresas_divisoes ed on ed.id_empresa_divisao = ged.id_empresa_divisao 
						where gpa.ativo = 1 order by rotulo ";
				echo combos::combo($sql);
			?>
			</select>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			&nbsp;
		</td>
		<td>
			<input type='checkbox' name='chkt_mostrar_especiais' value='1' title="Mostrar Especiais" class="checkbox" id='label1'>
			<label for='label1'>
				Mostrar Especiais
			</label>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
			<input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.txt_referencia.focus()" style="color:#ff9900" class="botao">
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="window.close()" style="color:red" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>