<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/maquina/maq_vs_func/index.php', '../../../../');
session_start('funcionarios');
$mensagem[1] = 'SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO !';

if($passo == 1) {
	switch($opt_opcao) {
		case 1:
			$sql= "select id_maquina, nome ";
			$sql.="from maquinas ";
			$sql.="where nome like '%$txt_consultar%' and ativo = '1' order by nome ";
		break;
		default:
			$sql= "select id_maquina, nome ";
			$sql.="from maquinas ";
			$sql.="where ativo = '1' order by nome ";
		break;
	}
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	if($linhas == 0) {
?>
		<Script Language = 'JavaScript'>
			window.location = 'consultar_maquina.php?valor=1'
		</Script>
<?
		exit;
	}
}
?>
<html>
<head>
<title>.:: Consultar Máquinas ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type=text/css rel=stylesheet>
<Script Language='JavaScript' Src='../../../../js/validar.js'></Script>
<Script Language='JavaScript'>
function enviar() {
	var elementos=document.form.elements
	var id_maquina=''
	for (i=0;i<elementos.length;i++) {
		if(document.form.elements[i].type=='select-multiple') {
			for(j=1;j<document.form.elements[i].length;j++) {
				if(document.form.elements[i][j].selected==true) {
					id_maquina=id_maquina+document.form.elements[i][j].value+',';
				}
			}
		}
	}
	parent.juncao.document.form.id_maquina2.value=id_maquina.substr(0,id_maquina.length-1);
    parent.juncao.document.form.submit();
}

function selecionar_todos() {
	var i, elementos = document.form.elements
	var selecionados = ''
	for (i = 0; i < elementos.length; i ++) {
		if(document.form.elements[i].type == 'select-multiple') {
			for(j = 1; j < document.form.elements[i].length; j ++) {
				document.form.elements[i][j].selected = true
			}
		}
	}
}
</Script>
</head>
<body onLoad="document.form.txt_consultar.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border="0" width="95%" align="center" cellspacing ='1' cellpadding='1'>
	<tr class='linhacabecalho'>
		<td colspan="2" align='center'>
			<font color='#FFFFFF' size='-1'>
				Consultar Máquina
			</font>
		</td>
    </tr>
	<tr class='linhanormal' align='center'>
		<td colspan='2'>
			Consultar <input type="text" name="txt_consultar" size="45" maxlength="45" class="caixadetexto">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="1" onclick="document.form.txt_consultar.focus()" title="Consultar máquinas por: Máquina" id='label' checked>
			<label for='label'>Máquina</label>
		</td>
		<td width="20%">
			<input type='checkbox' name='opcao' onclick='limpar()' value='2' tabindex='3' title="Consultar todos as máquinas" class="checkbox" id='label2'>
				<label for='label2'>Todos os registros</label>
		</td>
	</tr>
<?
	if($passo == 1) {
?>
	<tr class="linhanormal" align="center">
		<td colspan="2">
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
				for($i = 0; $i < $linhas; $i ++) {
			?>
				<option value="<?=$campos[$i]['id_maquina'];?>"><?=$campos[$i]['nome'];?></option>
			<?
				}
			?>
			</select>
		</td>
	</tr>
<?
	}
?>
	<tr>
		<td colspan="2" class="linhacabecalho" align="center">
<?
	if($passo == 1) {
?>
			<input type="button" name="cmd_selecionar" value="Selecionar Todos" title="Selecionar Todos" class="botao" onclick="selecionar_todos()">
			<input type="button" name="cmd_adicionar" value="Adicionar" title="Adicionar" class="botao" onclick="enviar()">
<?
	}
?>
			<input type="submit" name="cmdconsultar" value="Consultar" tabindex="5" class="botao" title="Consultar">
		</td>
	</tr>
</table>
</form>
</body>
<?
	if(!empty($valor)) {
?>
		<script language="JavaScript">
			alert('<?=$mensagem[$valor];?>')
		</script>
<?
	}
?>
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
</html>