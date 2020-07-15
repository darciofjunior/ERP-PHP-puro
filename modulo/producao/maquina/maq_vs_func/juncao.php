<?
require('../../../../lib/segurancas.php');
require('../../../../lib/biblioteca.php');
segurancas::geral('/erp/albafer/modulo/producao/maquina/maq_vs_func/index.php', '../../../../');
session_start('funcionarios');

$mensagem[1] = 'EXCEDIDO O NÚMERO DE REGISTROS SELECIONADOS !';
$mensagem[2] = 'MÁQUINA INCLUIDA PARA O FUNCIONÁRIO COM SUCESSO !';
$mensagem[3] = 'MÁQUINA JÁ EXISTENTE PARA ESTE FUNCIONÁRIO !';

$maquinas		= biblioteca::controle_itens($id_maquina, $id_maquina2, $acao);
$funcionarios	= biblioteca::controle_itens($id_func, $id_func2, $acao);

if($passo==1) {
	$data = date('Y-m-d H:i:s');
	$cont = 0;
	foreach($cmb_maquina as $valor) {
		$cont ++;
	}
	foreach($cmb_funcionario as $valor2) {
		$cont ++;
	}
	if($cont > 100) {
		$val = 1;
	}else {
		foreach($cmb_maquina as $valor) {
			foreach($cmb_funcionario as $valor2) {
				$sql = "Select id_maquina_vs_funcionario from maquinas_vs_funcionarios where id_maquina = '$valor' and id_funcionario = '$valor2' limit 1";
				$campos = bancos::sql($sql);
				$linhas = count($campos);
				if($linhas == 0) {
					$sql = "INSERT INTO `maquinas_vs_funcionarios` (`id_maquina_vs_funcionario`, `id_maquina`, `id_funcionario`) VALUES ('', '$valor', '$valor2')";
					bancos::sql($sql);
					$val = 2;
				} else {
					$val = 3;
				}
			}
		}
	}
?>
	<script language='JavaScript'>
		window.location = 'juncao.php?valor=<?echo $val;?>'
	</script>
<?
}else {
//Matriz de produtos acabados
	if(!empty($maquinas)) {
		$sql = "select id_maquina, nome ";
		$sql.="from maquinas ";
		$sql.="where id_maquina in ($maquinas) and ativo = 1 order by nome";
		$campos = bancos::sql($sql);
		$linhas = count($campos);
	}
//Matriz de funcionários
	if(!empty($funcionarios)) {
		$sql = "select id_funcionario, nome ";
		$sql.="from funcionarios ";
		$sql.="where id_funcionario in ($funcionarios) and ativo = 1 order by nome";
		$campos2 = bancos::sql($sql);
		$linhas2 = count($campos2);
	}
?>
<html>
<head>
<title>.:: Máquina vs Funcionário ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href='../../../../css/layout.css' type=text/css rel=stylesheet>
<Script Language='JavaScript' Src='../../../../js/tabela.js'></Script>
<Script Language='JavaScript' Src='../../../../js/validar.js'></Script>
<Script Language='JavaScript' Src='../../../../js/geral.js'></Script>
<Script Language='JavaScript'>
function retirar_maquina() {
//Aqui eu verifico todos os elementos que estão selecionados na combo múltipla
	var flag = 0, maquina_sel = ''
	for(i = 0; i < document.form.elements.length; i++) {
		if(document.form.elements[i].type == 'select-multiple') {
			if(document.form.elements[i].value == '') {
				if(flag == 0) {
					alert('SELECIONE PELO MENOS UMA MÁQUINA !')
				}
				document.form.elements[i].focus()
				return false
			}else {
				for(j = 0; j < document.form.elements[i].length; j ++) {
					if(document.form.elements[i][j].selected == true) {
						maquina_sel = maquina_sel + document.form.elements[i][j].value + ','
					}
				}
			}
			flag++
		}
		i = document.form.elements.length
	}
	maquina_sel = maquina_sel.substr(0, maquina_sel.length - 1)

	document.form.id_maquina2.value = maquina_sel
	document.form.acao.value = 1
	document.form.passo.value = 0
	document.form.submit()
}

function retirar_funcionario() {
//Aqui eu verifico todos os elementos que estão selecionados na combo múltipla
	var flag = 0, funcionario_sel = ''
	for(i = 0; i < document.form.elements.length; i++) {
		if(document.form.elements[i].type == 'select-multiple') {
			if(document.form.elements[i + 1].value == '') {
				if(flag == 0) {
					alert('SELECIONE PELO MENOS UM FUNCIONÁRIO !')
				}
				document.form.elements[i + 1].focus()
				return false
			}else {
				for(j = 0; j < document.form.elements[i + 1].length; j ++) {
					if(document.form.elements[i + 1][j].selected == true) {
						funcionario_sel = funcionario_sel + document.form.elements[i + 1][j].value + ','
					}
				}
			}
			flag++
		}
		i = document.form.elements.length
	}
	funcionario_sel = funcionario_sel.substr(0, funcionario_sel.length - 1)

	document.form.id_func2.value = funcionario_sel
	document.form.acao.value = 1
	document.form.passo.value = 0
	document.form.submit()
}

function selecionar_todas_maquinas() {
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

function selecionar_todos_funcionarios() {
	var elementos = document.form.elements
	for (var i = 0; i < elementos.length; i++) {
		if(document.form.elements[i].type == 'select-multiple') {
			for(var j=1;j<document.form.elements[i+1].length; j++) {
				document.form.elements[i + 1][j].selected = true
			}
		}
	}
}

function validar() {
	var cont = 0, flag = 0
	var perguntou = document.form.perguntou.value
	if(perguntou == 1) {
		resposta=false
	}
	if(perguntou == 0) {
		var resposta=confirm('DESEJA INCLUIR TODAS AS MÁQUINAS PARA TODOS OS FUNCIONÁRIOS ? ')
	}
	if(resposta==true) {
		selecionar_todas_maquinas()
		selecionar_todos_funcionarios()
		document.form.perguntou.value=1
	} else {
		for(i = 0; i < document.form.elements.length; i++) {
			if(document.form.elements[i].type == 'select-multiple') {
				if(document.form.elements[i].value == '') {
					if(flag == 0) {
						alert('SELECIONE PELO MENOS UMA MÁQUINA !')
					}else {
						alert('SELECIONE PELO MENOS UM FUNCIONÁRIO !')
					}
					document.form.elements[i].focus()
					document.form.perguntou.value = 1
					return false
				}
				flag++
			}
		}
	}
//Aqui eu verifico o número de elementos que eu tenho selecionados nas 2 combos
//alert(document.form.elements[i].length);
	for(i=0;i<document.form.elements.length;i++) {
		if(document.form.elements[i].type == 'select-multiple') {
			for(j=0;j<document.form.elements[i].length;j++) {
				if(document.form.elements[i][j].selected==true) {
					cont ++
				}
			}
		}
	}
//Aqui eu verifico se excedeu o número de elementos selecionados
	if(cont > 200) {
		alert('EXCEDIDO O NÚMERO DE REGISTROS SELECIONADOS !')
		return false
	}
	document.form.passo.value = 1
	return true
}
</Script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<form name="form" action="<?=$PHP_SELF;?>" method="post" onsubmit="return validar()">
<?
	if(!empty($maquinas) || !empty($funcionarios)) {
?>
<table width='700' border=0 align='center' cellspacing=1 cellpadding=1>
	<tr class="linhacabecalho" align="center">
		<td width='350'>
			<font color='#FFFFFF' size='-1'>
				Máquina(s)
			</font>
		</td>
		<td width='350'>
			<font color='#FFFFFF' size='-1'>
				Funcionário(s)
			</font>
		</td>
	</tr>
	<tr class="linhanormal" align="center">
		<td>
			<select name="cmb_maquina[]" class="combo" size="5" multiple>
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
			for($i = 0; $i < $linhas; $i++) {
		?>
				<option value='<?=$campos[$i]['id_maquina']?>'><?=$campos[$i]['nome']?></option>
		<?
			}
		?>
			</select>
		</td>
		<td>
			<select name="cmb_funcionario[]" class="combo" size="5" multiple>
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
			for($i = 0; $i < $linhas2; $i++) {
		?>
				<option value='<?=$campos2[$i]['id_funcionario']?>'><?=$campos2[$i]['nome']?></option>
		<?
			}
		?>
			</select>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td>
		<?
			if(!empty($maquinas)) {
		?>
			<input type="button" name="cmd_selecionar" value="Selecionar Todos" title="Selecionar Todos" class="botao" onclick="selecionar_todas_maquinas()">
			<input type="button" name="cmd_Retirar" value="Retirar" title="Retirar" class="botao" onclick="retirar_maquina()">
		<?
			}
		?>
		</td>
		<td>
		<?
			if(!empty($funcionarios)) {
		?>
			<input type="button" name="cmd_selecionar2" value="Selecionar Todos" title="Selecionar Todos" class="botao" onclick="selecionar_todos_funcionarios()">
			<input type="button" name="cmd_Retirar2" value="Retirar" title="Retirar" class="botao" onclick="retirar_funcionario()">
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
<input type="hidden" name="id_maquina" value="<?=$maquinas;?>">
<input type="hidden" name="id_maquina2" value="">
<input type="hidden" name="id_func" value="<?=$funcionarios;?>">
<input type="hidden" name="id_func2" value="">
<input type="hidden" name="passo">
<input type="hidden" name="perguntou">
<input type="hidden" name="acao">
</form>
</body>
</html>
<?
	if(!empty($valor)) {
?>
		<script language="JavaScript">
			alert('<?=$mensagem[$valor];?>')
		</script>
<?
	}
}
?>