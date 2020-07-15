<?
require('../../../../lib/segurancas.php');
require('../../../../lib/biblioteca.php');
session_start('funcionarios');
if($tela == 1) {//Veio da tela de Todos os P.A.
    segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_todos.php', '../../../../');
}else if($tela == 2) {//Veio da tela dos P.A. do Tipo Esp.
    segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_esp.php', '../../../../');
}
$mensagem[1] = '<font class="erro">SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>';
$mensagem[2] = '<font class="confirmacao">MÁQUINA(S) INCLUÍDA(S) COM SUCESSO PARA P.A.</font>';
$mensagem[3] = '<font class="erro">MÁQUINA(S) JÁ EXISTENTE(S) ESTE PARA P.A.</font>';

//Inserção dos produtos acabados vs máquinas
if($passo == 1) {
	if($inserir == 1) {
		foreach($cmb_maquina as $id_maquina) {
			$sql = "Select id_pac_maquina from pacs_vs_maquinas where id_produto_acabado_custo = '$id_produto_acabado_custo' and id_maquina = '$id_maquina' limit 1";
			$campos = bancos::sql($sql);
			if(count($campos) == 0) {
				$sql = "Insert into pacs_vs_maquinas (`id_pac_maquina`, `id_produto_acabado_custo`, `id_maquina`) values ('', '$id_produto_acabado_custo', '$id_maquina')";
				bancos::sql($sql);
				$valor = 2;
			}else {
				$valor = 3;
			}
		}
?>
		<Script Language = 'JavaScript'>
			window.opener.document.form.submit()
			var valor = eval('<?=$valor;?>')
			if(valor == 2) {
				window.location = 'alterar_etapa4.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>'
			}
		</Script>
<?
	}
}
//Fim da Inserção

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
	if($linhas < 1) {
?>
		<Script Language = 'JavaScript'>
			window.location = 'incluir_maquina.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>&valor=1'
		</script>
<?
		exit;
	}
}
?>
<html>
<head>
<title>.:: Consultar Máquina(s) ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href='../../../../css/layout.css' type='text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
/*************************************************************************/
/*Funções referentes a segunda tela depois da consulta - Passo = 1*/
function enviar() {
	var elementos = document.form.elements
	var selecionados = 0, id_maquina = ''
	for (i = 0; i < elementos.length; i++) {
		if(document.form.elements[i].type == 'select-multiple') {
			for(j = 1; j < document.form.elements[i].length; j++) {
				if(document.form.elements[i][j].selected == true) {
					selecionados ++
				}
			}
		}
	}

	if(selecionados == 0) {
		alert('SELECIONE UMA MÁQUINA !')
		return false
	}else if(selecionados > 10) {
		alert('EXCEDIDO O NÚMERO DE MÁQUINA(S) SELECIONADA(S) !\n\nPERMITIDO NO MÁXIMO 10 REGISTROS POR VEZ !')
		return false
	}

	document.form.inserir.value = 1
	document.form.submit();
}

function selecionar_todos() {
	var i, elementos = document.form.elements
	for (i = 0; i < elementos.length; i++) {
		if(document.form.elements[i].type == 'select-multiple') {
			for(j = 1; j < document.form.elements[i].length; j++) {
				document.form.elements[i][j].selected = true
			}
		}
	}
}
</Script>
</head>
<body onLoad="return iniciar()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='inserir'>
<?
	if(!empty($txt_consultar)) {
		$consultar = $txt_consultar;
	}else {
		$consultar = $txt_consultar2;
	}
?>
<input type='hidden' name='txt_consultar2' value="<?=$consultar;?>">
<?
	if(!empty($opt_opcao)) {
		$opcao = $opt_opcao;
	}else {
		$opcao = $opt_opcao2;
	}
?>
<input type='hidden' name='opt_opcao2' value="<?=$opcao;?>">
<input type='hidden' name='id_produto_acabado_custo' value="<?=$id_produto_acabado_custo;?>">
<table border="0" width="70%" align="center" cellspacing='1' cellpadding='1'>
	<tr align='center'>
		<td colspan='2' width="750">
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class='linhacabecalho'>
		<td colspan="2" align='center'>
			<font color='#FFFFFF' size='-1'>
				Consultar Máquina(s)
			</font>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan='2'>
			<div align='center'>Consultar
				<input type="text" name="txt_consultar" size="45" maxlength="45" class="caixadetexto">
			</div>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="20%"><input type="radio" name="opt_opcao" value="1" checked onclick="return iniciar()"; title="Consultar máquinas por: Máquina" id='label'><label for='label'>Máquina</label></td>
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
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="reset" name="cmd_limpar" style="color:#ff9900;" value="Limpar" onclick="document.form.opcao.checked = false;limpar();" title='Limpar' class="botao">
<?
	if($passo == 1) {
?>
			<input type="button" name="cmd_selecionar" value="Selecionar Todos" title="Selecionar Todos" class="botao" onclick="selecionar_todos()">
			<input type="button" name="cmd_adicionar" value="Adicionar" title="Adicionar" class="botao" onclick="enviar()">
<?
	}
?>
			<input type="submit" name="cmd_consultar" value="Consultar" tabindex="5" class="botao" title="Consultar">
			<input type="button" name="cmd_fechar" style="color:red" value="Fechar" title="Fechar" onclick="window.close()" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
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
</script>
</html>
