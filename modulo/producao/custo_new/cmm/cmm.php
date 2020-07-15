<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/compras_new.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = '<font class="atencao">SÃO CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>';
$mensagem[2] = '<font class="confirmacao">CONSUMO MÉDIO MENSAL ATUALIZADO COM SUCESSO.</font>';

if($passo == 1) {
	switch($opt_opcao) {
		case 1:
			$sql = "Select pi.id_produto_insumo, pi.discriminacao, pi.estoque_mensal, g.nome, g.referencia 
					from produtos_insumos pi 
					inner join grupos g on g.id_grupo = pi.id_grupo and g.referencia like '%$txt_consultar%' 
					where pi.ativo = '1' order by pi.discriminacao ";
		break;
		case 2:
			$sql = "Select pi.id_produto_insumo, pi.discriminacao, pi.estoque_mensal, g.nome, g.referencia 
					from produtos_insumos pi 
					inner join grupos g on g.id_grupo = pi.id_grupo 
					where pi.discriminacao like '%$txt_consultar%' 
					and pi.ativo = '1' order by pi.discriminacao ";
		break;
		case 3:
			$sql = "Select pi.id_produto_insumo, pi.discriminacao, pi.estoque_mensal, g.nome, g.referencia 
					from produtos_insumos pi 
					inner join grupos g on g.id_grupo = pi.id_grupo 
					where pi.observacao like '%$txt_consultar%' 
					and pi.ativo = '1' order by pi.discriminacao ";
		break;
		default:
			$sql = "Select pi.id_produto_insumo, pi.discriminacao, pi.estoque_mensal, g.nome, g.referencia 
					from produtos_insumos pi 
					inner join grupos g on g.id_grupo = pi.id_grupo 
					where pi.ativo = '1' order by pi.discriminacao ";
		break;
	}
	$campos = bancos::sql($sql, $inicio, 10, 'sim', $pagina);
	$linhas = count($campos);

	if($linhas < 1) { ?>
		<Script Language = 'Javascript'>
			window.location = 'cmm.php?valor=1'
		</Script>
<?	}else { ?>
<html>
<head>
<title>.:: Consumo Mensal Médio ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = 'controle.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
	if(!validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')) {
		return false
	}
	elementos = elementos = document.form.elements
	for(x = 1; x < (elementos.length - 2); x+=3) {
		if(elementos[x].checked == true) {
			document.form.formatar_moeda.value = elementos[x + 2].value
			limpeza_moeda('form', 'formatar_moeda, ')
			elementos[x + 2].value = document.form.formatar_moeda.value
		}
	}
	return true
}
</script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<form name='form' method='post' onsubmit="return validar()" action='<?=$PHP_SELF.'?passo=2';?>'>
<table width='700' border=0 align='center' cellspacing=1 cellpadding=1 onmouseover="total_linhas(this)";>
	<tr align='center'>
		<td colspan="6">
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="6">
			<font color='#FFFFFF' size='-1'>
				Consultar Produto(s) Insumo(s) - Consumo Mensal Médio
			</font>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td width='80'>
			<font color='#FFFFFF' size='-1'>
				<label for='todos'>Todos </label><input type='checkbox' name='chkt' onClick="return selecionar('form', 'chkt', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox" id='todos'>
			</font>
		</td>
		<td width='190'>
			<font color='#FFFFFF' size='-1'>
				Grupo
			</font>
		</td>
		<td width='100'>
			<font color='#FFFFFF' size='-1'>
				Referência
			</font>
		</td>
		<td width='300'>
			<font color='#FFFFFF' size='-1'>
				Discriminação
			</font>
		</td>
		<td width='80'>
			<font color='#FFFFFF' size='-1'>
				CMM
			</font>
		</td>
		<td width='80'>
			<font color='#FFFFFF' size='-1'>
				CMM (Auto)
			</font>
		</td>
	</tr>
<?
		for ($i = 0; $i < $linhas; $i++) {
?>
	<tr class="linhanormal" onclick="checkbox_habilita('form', 'chkt','<?=$i;?>', '#E8E8E8')" onmouseover="return sobre_celula(this, '#CCFFCC')" onmouseout="return fora_celula(this, '#E8E8E8')" align="center">
		<td>
			<input type='checkbox' name='chkt_produto_insumo[]' value="<?=$campos[$i]['id_produto_insumo'];?>" onclick="checkbox_habilita('form', 'chkt','<?=$i;?>', '#E8E8E8')" class='checkbox'>
		</td>
		<td>
			<?=$campos[$i]['nome'];?>
		</td>
		<td align="left">
			<?=$campos[$i]['referencia'];?>
		</td>
		<td align="left">
			<?=$campos[$i]['discriminacao'];?>
		</td>
		<td>
			<input type='text' name='txt_cmm[]' value="<?=number_format($campos[$i]['estoque_mensal'], 2, ',', '.');?>" maxlength="10" size="10" class="caixadetexto" disabled>
		</td>
		<td>
			<input type='text' name='txt_cmm_auto[]' value="<?=compras_new::consumo_medio_mensal($campos[$i]['id_produto_insumo']);?>" maxlength="10" size="10" class="caixadetexto" onclick="checkbox_habilita('form', 'chkt','<?echo $i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this,'moeda_especial', '2','',event)" disabled>
		</td>
	</tr>
<?
		}
?>
	<tr class="linhacabecalho" align="center">
		<td colspan='6'>
			<input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'cmm.php'" class="botao">
			<input type='submit' name='cmd_atualizar' value='Atualizar' title='Atualizar' class='botao'>
		</td>
	</tr>
</table>
<input type="hidden" name="formatar_moeda">
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
	}
}else if ($passo == 2) {
    for($i = 0; $i < count($chkt_produto_insumo); $i++) {
        $sql = "Update produtos_insumos set estoque_mensal = '$txt_cmm_auto[$i]' where id_produto_insumo = '$chkt_produto_insumo[$i]' limit 1 ";
        bancos::sql($sql);
    }
?>
	<Script Language = 'Javascript'>
		window.location = 'cmm.php?valor=2'
	</Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consumo Mensal Médio ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
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
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
	<tr align='center'>
		<td colspan='2'>
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class='linhacabecalho' align='center'>
		<td colspan="2">
			<font color='#FFFFFF' size='-1'>
				Consultar Produto Insumo - Consumo Mensal Médio
			</font>
		</td>
	</tr>
	<tr class='linhanormal' align='center'>
		<td colspan='2'>
			Consultar <input type="text" name="txt_consultar" size='45' maxlength='45' class='caixadetexto'>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="1" title="Consultar Produtos Insumos por: Referência" onclick="return iniciar()" id='label'>
			<label for='label'>Referência</label>
		</td>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="2" title="Consultar Produtos Insumos por: Discriminação" onClick="return iniciar()" checked id='label2'>
			<label for='label2'>Discrimina&ccedil;&atilde;o</label>
		</td>
	</tr>
	<tr class="linhanormal">
		<td width="20%">
			<input type="radio" name="opt_opcao" value="3" title="Consultar Produtos Insumos por: Observação" onClick="return iniciar()" id='label3'>
			<label for='label3'>Observação</label>
		</td>
		<td width="20%">
			<input type='checkbox' name='opcao' value='4' title="Consultar todos os Produtos Insumos" onClick='limpar()' class="checkbox" id='label4'>
			<label for='label4'>Todos os registros</label>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="reset" name="cmd_limpar" value="Limpar" title='Limpar' onclick="document.form.opcao.checked = false;limpar();" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>