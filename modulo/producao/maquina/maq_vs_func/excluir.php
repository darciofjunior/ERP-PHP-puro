<?
require('../../../../lib/segurancas.php');
segurancas::geral($PHP_SELF, '../../../../');
session_start('funcionarios');

$mensagem[1] = 'Sua consulta não retornou nenhum resultado.';
$mensagem[2] = 'Algun(s) registro(s) não podem ser apagados pois consta em uso por outro cadastro.';
$mensagem[3] = 'Máquina - Funcionário excluido com sucesso.';

if($passo == 1) {
	switch($opt_opcao) {
		case 1:
			$sql = "SELECT * 
					FROM `maquinas` 
					WHERE `nome` LIKE '%$txt_consultar%' 
					AND `ativo` = '1' ORDER BY nome ";
		break;
		default:
			$sql = "SELECT * 
					FROM `maquinas` 
					WHERE `ativo` = '1' ORDER BY nome ";
		break;
	}
	$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
	$linhas = count($campos);
	if($linhas == 0) {
?>
		<Script Language = 'JavaScript'>
			window.location = 'excluir.php?valor=1'
		</Script>
<?
	}else {
?>
<html>
<head>
<title>.:: Excluir Máquinas vs Funcionários ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href='../../../../css/layout.css' type=text/css rel=stylesheet>
<Script Language='JavaScript' Src='../../../../js/tabela.js'></Script>
<Script Language='JavaScript' Src='../../../../js/validar.js'></Script>
<Script Language='JavaScript' Src='../../../../js/geral.js'></Script>
<script language="javascript">
</script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<form name="form" method="POST" action="<? echo $PHP_SELF.'?passo=2';?>" onsubmit="validar()">
<table width='600' border=0 align='center' cellspacing=1 cellpadding=1 onmouseover="total_linhas(this)";>
	<tr>
		<td colspan='2'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-1'>
				<div align='center'>
					<b><?=$mensagem[$valor];?></b>
				</div>
			</font>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center" colspan="2">
		<td colspan="2">
			<font color='#FFFFFF' size='-1'>
				Excluir Máquina(s) Vs Funcionário(s)
			</font>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td>
			<font color='#FFFFFF' size='-1'>
				Máquina
			</font>
		</td>
	</tr>
<?
		for ($i = 0;  $i < $linhas; $i++) {
?>
			<tr class="linhanormal" align="center">
				<td>
					<a href="excluir.php?passo=2&id_maquina=<?=$campos[$i]['id_maquina'];?>&nome=<?=$campos[$i]['nome'];?>&txt_consultar=<?=$txt_consultar;?>&opt_opcao=<?=$opt_opcao;?>" class="link">
						<?=$campos[$i]['nome'];?>
					</a>
				</td>
			</tr>
<?
		}
?>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'excluir.php'" class="botao">
		</td>
	</tr>
</table>
</form>
<center>
	<?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
	}
}elseif ($passo == 2) {
?>
<html>
<head>
<title>.:: Excluir Funcionários da Máquina <?echo $nome;?> ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href='../../../../css/layout.css' type=text/css rel=stylesheet>
<Script Language='JavaScript' Src='../../../../js/tabela.js'></Script>
<Script Language='JavaScript' Src='../../../../js/validar.js'></Script>
<Script Language='JavaScript' Src='../../../../js/geral.js'></Script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<form name="form" method="POST" action="<?=$PHP_SELF.'?passo=3';?>" onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')">
<table width='600' border=0 align='center' cellspacing=1 cellpadding=1 onmouseover="total_linhas(this)";>
<?
	$sql = "select nome from maquinas where id_maquina = '$id_maquina' and ativo = 1";
	$campos = bancos::sql($sql);
	$nome = $campos[0]['nome'];
	$sql = "select f.nome, f.id_funcionario, mf.id_maquina_vs_funcionario from funcionarios f, maquinas_vs_funcionarios mf where mf.id_funcionario = f.id_funcionario and mf.id_maquina = '$id_maquina' ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	if($linhas == 0) {
?>
		<Script Language = 'JavaScript'>
			window.location = 'excluir.php?valor=1'
		</Script>
<?
	}else {
?>
	<tr>
		<td colspan="3">
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-1'>
				<div align='center'>
					<b><?=$mensagem[$valor];?></b>
				</div>
			</font>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">Excluir Máquina(s) Vs Funcionário(s)</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td>
			Funcionários da Máquina: &nbsp;<?=$nome;?>
		</td>
		<td>
			<label for='todos'>Todos </label>
			<input type="checkbox" name="chkt" title="Selecionar Todos" onClick="selecionar('form', 'chkt', totallinhas, '#E8E8E8')" class="checkbox" id='todos'>
		</td>
	</tr>
<?
		for($i = 0; $i < $linhas; $i++) {
?>
		<tr class="linhanormal" onclick="checkbox('form', 'chkt','<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
			<td><b>Funcionário:</b>&nbsp;<?=$campos[$i]['nome'];?></td>
			<td align="center">
				<input type="checkbox" name="chkt_opcao[]" value="<?=$campos[$i]['id_maquina_vs_funcionario'];?>" onclick="checkbox('form', 'chkt','<?=$i;?>', '#E8E8E8')" class="checkbox">
			</td>
		</tr>
<?
		}
?>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'excluir.php?passo=1&id_maquina=<?=$id_maquina;?>&nome=<?=$nome;?>&txt_consultar=<?=$txt_consultar;?>&opt_opcao=<?=$opt_opcao;?>'" class="botao">
			<input type="submit" name="cmd_excluir" value="Excluir" title="Excluir" class="botao">
		</td>
	</tr>
</table>
</form>
<center>
	<?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
	}
}else if($passo == 3) {
	foreach($chkt_opcao as $id_maquina_vs_funcionario) {
		$sql = "DELETE FROM `maquinas_vs_funcionarios` WHERE `id_maquina_vs_funcionario` = '$id_maquina_vs_funcionario' LIMIT 1 ";
		bancos::sql($sql);
	}
?>
	<Script Language = 'JavaScript'>
		window.location = 'excluir.php?valor=3'
	</Script>
<?
}else {
?>
<html>
<head>
<title>.:: Excluir Produtos Acabados Vs Componentes ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href='../../../../css/layout.css' type=text/css rel=stylesheet>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
	if(document.form.opcao.checked == true) {
		document.form.opt_opcao.disabled = true
		document.form.txt_consultar.disabled=true
		document.form.txt_consultar.value=''
	}else {
		document.form.opt_opcao.disabled = false
		document.form.txt_consultar.disabled=false
		document.form.txt_consultar.value=''
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
</Script>
</head>
<body onLoad="document.form.txt_consultar.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
	<tr class="atencao" align='center'>
		<td colspan='4'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-1'>
				<b><?=$mensagem[$valor];?></b>
			</font>
		</td>
	</tr>
	<tr class='linhacabecalho'>
		<td colspan="2" align='center'><font color='#FFFFFF' size='-1'>Excluir Máquinas Vs Funcionários</font></td>
	</tr>
	<tr class='linhanormal' align='center'>
		<td colspan='2'>
			Consultar <input type="text" name="txt_consultar" size="45" maxlength="45" class="caixadetexto">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="20%"><input type="radio" name="opt_opcao" value="1" onclick="document.form.txt_consultar.focus()" title="Consultar máquinas por: Máquina" id='label' checked ><label for='label'>Máquina</label></td>
		<td width="20%">
			<input type='checkbox' name='opcao' onclick='limpar()' value='1' title="Consultar todos as máquinas" class="checkbox" id='label2'>
			<label for='label2'>Todos os registros</label>
		</td>
	</tr>
	<tr align="center">
		<td colspan="2" class="linhacabecalho">
			<input type="reset" style="color:#ff9900;" name="cmdLimpar" value="Limpar" onclick="document.form.opcao.checked = false;limpar();" class="botao" title='Limpar'>
			<input type="submit" name="cmdconsultar" value="Consultar" class="botao" title='Consultar'>
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>