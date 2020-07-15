<?
require('../../../../lib/segurancas.php');
session_start('funcionarios');
segurancas::geral($PHP_SELF, '../../../../');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

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
	$campos = bancos::sql($sql, $inicio, 10, 'sim', $pagina);
	$linhas = count($campos);
	if($linhas == 0) {
?>
		<Script Language = 'JavaScript'>
			window.location = 'consultar.php?valor=1'
		</Script>
<?
	}else {
?>
<html>
<head>
<title>.:: Consultar Máquinas vs Funcionários ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href='../../../../css/layout.css' type=text/css rel=stylesheet>
<Script Language='JavaScript' Src='../../../../js/tabela.js'></Script>
<Script Language='JavaScript' Src='../../../../js/validar.js'></Script>
<Script Language='JavaScript' Src='../../../../js/geral.js'></Script>
<Script Language='JavaScript' Src='../../../../js/nova_janela.js'></Script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<table width='600' border=0 align='center' cellspacing=1 cellpadding=1 onmouseover="total_linhas(this)";>
	<tr class="atencao">
		<td colspan='2'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-1'>
				<div align='center'>
					<?=$mensagem[$valor];?>
				</div>
			</font>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<font color='#FFFFFF' size='-1'>
				Consultar Máquina(s) vs Funcionário(s)
			</font>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td colspan='2'>
			<font color='#FFFFFF' size='-1'>
				Máquina
			</font>
		</td>
	</tr>
<?
		for ($i=0;  $i < $linhas; $i++) {
?>
	<tr class="linhanormal" onclick="return cor_clique_celula(this, '#C6E2FF')" onmouseover="return sobre_celula(this, '#CCFFCC')" onmouseout="return fora_celula(this, '#E8E8E8')">
		<td width='20' onclick="javascript:window.location=('consultar.php?passo=2&id_maquina=<?=$campos[$i]['id_maquina'];?>&txt_consultar=<?=$txt_consultar;?>&opt_opcao=<?=$opt_opcao;?>')" width="20">
                    <a href = "javascript:window.location=('consultar.php?passo=2&id_maquina=<?=$campos[$i]['id_maquina'];?>&txt_consultar=<?=$txt_consultar;?>&opt_opcao=<?=$opt_opcao;?>')";>
                        <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                    </a>
		</td>
		<td width='580' align='center' onclick="javascript:window.location=('consultar.php?passo=2&id_maquina=<?=$campos[$i]['id_maquina'];?>&txt_consultar=<?=$txt_consultar;?>&opt_opcao=<?=$opt_opcao;?>')">
			<a href="javascript:window.location=('consultar.php?passo=2&id_maquina=<?=$campos[$i]['id_maquina'];?>&txt_consultar=<?=$txt_consultar;?>&opt_opcao=<?=$opt_opcao;?>')";>
				<?=$campos[$i]['nome'];?>
			</a>
		</td>
	</tr>
<?
		}
?>
	<tr class="linhacabecalho" align="center">
		<td colspan='3'>
			<input type="button" name="cmdConsultarNovamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'consultar.php'" class="botao">
		</td>
	</tr>
</table>
<center>
	<?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
	}
}else if($passo == 2) {
	$sql = "select nome from maquinas where id_maquina = '$id_maquina' and ativo = 1";
	$campos = bancos::sql($sql);
	$nome = $campos[0]['nome'];
	$sql = "select f.nome, f.id_funcionario, mf.id_maquina_vs_funcionario from funcionarios f, maquinas_vs_funcionarios mf where mf.id_funcionario = f.id_funcionario and mf.id_maquina = '$id_maquina' ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	if($linhas == 0) {
?>
		<Script Language = 'JavaScript'>
			window.location = 'consultar.php?passo=1&opt_opcao=<?=$opt_opcao;?>&txt_consultar=<?=$txt_consultar;?>&valor=1'
		</Script>
<?
		exit;
	}
?>
<html>
<head>
<title>.:: Consultar Máquinas vs Funcionários ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
</head>
<body>
<table width="600" cellspacing="1" cellpadding="1" border="0" align="center" onmouseover="total_linhas(this)">
	<tr class="linhacabecalho">
		<td colspan="2" align="center">
			Consultar Máquinas Vs Funcionários
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td>
			Funcionários da Máquina: <?=$nome;?>
		</td>
	</tr>
<?
	for($i = 0; $i < $linhas; $i++) {
		$id_func = $campos[$i]['id_funcionario'];
		$nome = $campos[$i]['nome'];
?>
	<tr class="linhanormal" onclick="return cor_clique_celula(this, '#C6E2FF')" onmouseover="return sobre_celula(this, '#CCFFCC')" onmouseout="return fora_celula(this, '#E8E8E8')" align="center">
		<td colspan="2">
			<?=$nome;?>
		</td>
	</tr>
<?
	}
?>
	<tr class="linhacabecalho">
		<td colspan="2" align="center">
			<input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" class="botao" title="Consultar Novamente" onclick="javascript:window.location='consultar.php?passo=1&txt_consultar=<?=$txt_consultar;?>&opt_opcao=<?=$opt_opcao;?>'">
		</td>
	</tr>
</table>
</body>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Produtos Acabados Vs Componentes ::.</title>
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
	<tr class='linhacabecalho' align='center'>
		<td colspan="2">
			<font color='#FFFFFF' size='-1'>
				Consultar Máquinas Vs Funcionários
			</font>
		</td>
	</tr>
	<tr class='linhanormal' align='center'>
		<td colspan='2'>
			Consultar <input type="text" name="txt_consultar" size="45" maxlength="45" class="caixadetexto">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="20%"><input type="radio" name="opt_opcao" value="1" onclick="document.form.txt_consultar.focus()" title="Consultar máquinas por: Máquina" id='label' checked>
			<label for='label'>Máquina</label>
		</td>
		<td width="20%">
			<input type='checkbox' name='opcao' onclick='limpar()' value='2' title="Consultar todos as máquinas" class="checkbox" id='label2'>
			<label for='label2'>Todos os registros</label>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="reset" name="cmd_limpar" value="Limpar" title='Limpar' onclick="document.form.opcao.checked = false;limpar()" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>