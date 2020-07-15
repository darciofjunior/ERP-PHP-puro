<?
require('../../../lib/segurancas.php');
require('../../../lib/biblioteca.php');
segurancas::geral('/erp/albafer/modulo/financeiro/produtos_fornecedores/index.php', '../../../');
session_start('funcionarios');

$mensagem[1] = 'PRODUTO FINANCEIRO INCLUIDO PARA O FORNECEDOR COM SUCESSO !';
$mensagem[2] = 'PRODUTO FINANCEIRO JÁ EXISTENTE PARA ESTE FORNECEDOR !';

$vetor_fornecedores = biblioteca::controle_itens($id_fornecedor, $id_fornecedor2, $acao);
$vetor_produtos     = biblioteca::controle_itens($id_produto, $id_produto2, $acao);

if($passo == 1) {
	$data = date('Y-m-d H:i:s');
	
	foreach($_POST['cmb_fornecedor'] as $id_fornecedor) {
		foreach($_POST['cmb_produto'] as $id_produto_insumo) {
			$sql = "SELECT id_produto_financeiro_vs_fornecedor, ativo 
					FROM `produtos_financeiros_vs_fornecedor` 
					WHERE `id_fornecedor` = '$id_fornecedor' 
					AND `id_produto_financeiro` = '$id_produto_insumo' LIMIT 1 ";
			$campos = bancos::sql($sql);
			$linhas = count($campos);
			if($linhas == 1) {
				if($campos[0]['ativo'] == 0) {//Se esse registro estiver inativo, eu reabilito o mesmo ...
					$sql = "UPDATE `produtos_financeiros_vs_fornecedor` SET `ativo` = '1' WHERE `id_produto_financeiro_vs_fornecedor` = '".$campos[0]['id_produto_financeiro_vs_fornecedor']."' LIMIT 1 ";
					bancos::sql($sql);
					$valor = 1;
				}else {
					$valor = 2;
				}
			}else {
				$sql = "INSERT INTO `produtos_financeiros_vs_fornecedor` (`id_produto_financeiro_vs_fornecedor`, `id_produto_financeiro`, `id_fornecedor`) VALUES (NULL, '$id_produto_insumo', '$id_fornecedor') ";
				bancos::sql($sql);
				$valor = 1;
			}
		}
	}
?>
	<Script Language = 'JavaScript'>
		window.location = 'juncao.php?valor=<?=$valor;?>'
	</Script>
<?
}else {
//Matriz de fornecedores
	if(!empty($vetor_fornecedores)) {
		$sql = "SELECT id_fornecedor, razaosocial 
				FROM `fornecedores` 
				WHERE `id_fornecedor` IN ($vetor_fornecedores) ORDER BY razaosocial ";
		$campos_fornecedores = bancos::sql($sql);
		$linhas_fornecedores = count($campos_fornecedores);
	}

//Matriz de produtos
	if(!empty($vetor_produtos)) {
		$sql = "SELECT id_produto_financeiro, pf.discriminacao 
				FROM `produtos_financeiros` pf 
				INNER JOIN `grupos` g ON g.id_grupo = pf.id_grupo 
				WHERE pf.`id_produto_financeiro` IN ($vetor_produtos) 
				AND pf.`ativo` = '1' ORDER BY pf.discriminacao ";
		$campos_produtos = bancos::sql($sql);
		$linhas_produtos = count($campos_produtos);
	}
?>
<html>
<head>
<title>.:: Fornecedor vs Produto Financeiro ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href='../../../css/layout.css' type=text/css rel=stylesheet>
<Script Language='JavaScript' Src='../../../js/geral.js'></Script>
<Script Language='JavaScript' Src='../../../js/validar.js'></Script>
<Script Language='JavaScript'>
function retirar_fornecedor() {
//Aqui eu verifico todos os elementos que estão selecionados na combo múltipla
	var flag = 0, fornec_sel = ''
	for(i = 0; i < document.form.elements.length; i++) {
		if(document.form.elements[i].type == 'select-multiple') {
			if(document.form.elements[i].value == '') {
				if(flag == 0) {
					alert('SELECIONE PELO MENOS UM FORNECEDOR !')
				}
				document.form.elements[i].focus()
				return false
			}else {
				for(j = 0; j < document.form.elements[i].length; j ++) {
					if(document.form.elements[i][j].selected == true) {
						fornec_sel = fornec_sel + document.form.elements[i][j].value + ','
					}
				}
			}
			flag++
		}
		i = document.form.elements.length
	}
	fornec_sel = fornec_sel.substr(0, fornec_sel.length - 1)

	document.form.id_fornecedor2.value = fornec_sel
	document.form.acao.value 	= 1
	document.form.passo.value 	= 0
	document.form.submit()
}

function retirar_produto() {
//Aqui eu verifico todos os elementos que estão selecionados na combo múltipla
	var flag = 0, prod_sel = ''
	for(i = 0; i < document.form.elements.length; i++) {
		if(document.form.elements[i].type == 'select-multiple') {
			if(document.form.elements[i + 1].value == '') {
				if(flag == 0) {
					alert('SELECIONE PELO MENOS UM PRODUTO FINANCEIRO !')
				}
				document.form.elements[i + 1].focus()
				return false
			}else {
				for(j = 0; j < document.form.elements[i + 1].length; j ++) {
					if(document.form.elements[i + 1][j].selected == true) {
						prod_sel = prod_sel + document.form.elements[i + 1][j].value + ','
					}
				}
			}
			flag++
		}
	}
	prod_sel = prod_sel.substr(0, prod_sel.length - 1)

	document.form.id_produto2.value = prod_sel
	document.form.acao.value = 1
	document.form.passo.value = 0
	document.form.submit()
}

function selecionar_todos_fornecedores() {
	var i, elementos = document.form.elements
	var selecionados = ''
	for (i = 0; i < elementos.length; i ++) {
		if(document.form.elements[i].type == 'select-multiple') {
			for(j = 1; j < document.form.elements[i].length; j ++) {
				document.form.elements[i][j].selected = true
			}
		}
		i = elementos.length
	}
}

function selecionar_todos_produtos() {
	var elementos = document.form.elements
	for (var i = 0; i < elementos.length; i++) {
		if(document.form.elements[i].type == 'select-multiple') {
			for(var j = 1; j < document.form.elements[i+1].length; j++) {
				document.form.elements[i + 1][j].selected = true
			}
		}
	}
}

function validar() {
	var cont = 0, flag = 0
	var perguntou = document.form.perguntou.value
	if(perguntou == 1) resposta = false
	if(perguntou == 0) {
		var resposta = confirm('DESEJA INCLUIR TODOS OS FORNECEDORES PARA TODOS OS PRODUTOS FINANCEIROS ? ')
	}
	if(resposta == true) {
		selecionar_todos_fornecedores()
		selecionar_todos_produtos()
		document.form.perguntou.value = 1
	}else {
		for(i = 0; i < document.form.elements.length; i++) {
			if(document.form.elements[i].type == 'select-multiple') {
				if(document.form.elements[i].value == '') {
					if(flag == 0) {
						alert('SELECIONE PELO MENOS UM FORNECEDOR !')
					}else {
						alert('SELECIONE PELO MENOS UM PRODUTO INSUMO !')
					}
					document.form.elements[i].focus()
					document.form.perguntou.value = 1
					return false
				}
				flag++
			}
		}
	}
	document.form.passo.value = 1
}
</Script>
</head>
<body>
<form name="form" action='' method="post" onsubmit="return validar()">
<?
	if(!empty($vetor_fornecedores) || !empty($vetor_produtos)) {
?>
<table width='70%' border=0 align='center' cellspacing='1' cellpadding='1'>
	<tr class="linhacabecalho" align="center">
            <td>
                Fornecedor(es)
            </font>
            </td>
            <td>
                Produto(s) Financeiro(s)
            </td>
	</tr>
	<tr class="linhanormal" align="center">
		<td>
			<select name="cmb_fornecedor[]" class="combo" size="5" multiple>
				<option value='' style='color:red'>
				SELECIONE
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				</option>
		<?
			for($i = 0; $i < $linhas_fornecedores; $i++) {
		?>
				<option value='<?=$campos_fornecedores[$i]['id_fornecedor']?>'><?=$campos_fornecedores[$i]['razaosocial']?></option>
		<?
			}
		?>
			</select>
		</td>
		<td>
			<select name="cmb_produto[]" class="combo" size="5" multiple>
				<option value='' style='color:red'>
				SELECIONE
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				</option>
		<?
				for($i = 0; $i < $linhas_produtos; $i++) {
		?>
				<option value='<?=$campos_produtos[$i]['id_produto_financeiro']?>'><?=$campos_produtos[$i]['discriminacao']?></option>
		<?
				}
		?>
			</select>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td>
		<?
			if(!empty($vetor_fornecedores)) {
		?>
			<input type="button" name="cmd_selecionar" value="Selecionar Todos" title="Selecionar Todos" class="botao" onclick="selecionar_todos_fornecedores()">
			<input type="button" name="cmd_retirar" value="Retirar" title="Retirar" class="botao" onclick="retirar_fornecedor()">
		<?
			}
		?>
		</td>
		<td>
		<?
			if(!empty($vetor_produtos)) {
		?>
			<input type="button" name="cmd_selecionar2" value="Selecionar Todos" title="Selecionar Todos" onclick="selecionar_todos_produtos()" class="botao">
			<input type="button" name="cmd_retirar2" value="Retirar" title="Retirar" onclick="retirar_produto()" class="botao">
		<?
			}
		?>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="submit" name="cmd_Incluir" value="Incluir" title="Incluir" class="botao">
		</td>
	</tr>
</table>
<?
	}
?>
<input type="hidden" name="id_fornecedor" value="<?=$vetor_fornecedores;?>">
<input type="hidden" name="id_fornecedor2" value="">
<input type="hidden" name="id_produto" value="<?=$vetor_produtos;?>">
<input type="hidden" name="id_produto2" value="">
<input type="hidden" name="passo">
<input type="hidden" name="perguntou">
<input type="hidden" name="acao">
</form>
</body>
</html>
<?
	if(!empty($valor)) {
?>
		<Script Language = 'JavaScript'>
			alert('<?=$mensagem[$valor];?>')
		</Script>
<?
	}
}
?>