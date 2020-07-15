<?
require('../../../../lib/segurancas.php');
session_start('funcionarios');
if($tela == 1) {//Veio da tela de Todos os P.A.
    segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_todos.php', '../../../../');
}else if($tela == 2) {//Veio da tela dos P.A. do Tipo Esp.
    segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_esp.php', '../../../../');
}
$mensagem[1] = '<font class="erro">SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>';
$mensagem[2] = '<font class="confirmacao">CUSTO ATUALIZADO COM SUCESSO.</font>';

if($passo == 1) {
	switch($opt_opcao) {
		case 1:
			$sql= "select g.referencia, pi.id_produto_insumo, pi.discriminacao ";
			$sql.="from produtos_insumos pi, grupos g, produtos_insumos_vs_acos pia ";
			$sql.="where g.referencia like '%$txt_consultar%' and pi.id_grupo = g.id_grupo and pi.id_produto_insumo = pia.id_produto_insumo and pi.ativo = 1 order by pi.discriminacao ";
		break;
		case 2:
			$sql= "select g.referencia, pi.id_produto_insumo, pi.discriminacao ";
			$sql.="from produtos_insumos pi, grupos g, produtos_insumos_vs_acos pia ";
			$sql.="where pi.discriminacao like '%$txt_consultar%' and pi.id_grupo = g.id_grupo and pi.ativo = 1 and pi.id_produto_insumo = pia.id_produto_insumo order by pi.discriminacao ";
		break;
		default:
			$sql= "select g.referencia, pi.id_produto_insumo, pi.discriminacao ";
			$sql.="from produtos_insumos pi, grupos g, produtos_insumos_vs_acos pia ";
			$sql.="where pi.id_grupo = g.id_grupo and pi.ativo = 1 and pi.id_produto_insumo = pia.id_produto_insumo order by pi.discriminacao ";
		break;
	}
	$campos = bancos::sql($sql, $inicio, 10, 'sim', $pagina);
	$linhas = count($campos);
	if($linhas < 1) {
?>
		<Script Language = 'JavaScript'>
			window.location = 'consultar_produto_insumo.php?passo=0&id_produto_acabado_custo=<?=$id_produto_acabado_custo?>&valor=1'
		</Script>
<?
	}else {
?>
<html>
<head>
<title>.:: Consultar Produto Insumo ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<table width='600' border='0' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)" align='center'>
	<tr>
		<td colspan='3'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-1'>
				<div align='center'>
				</div>
			</font>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan='3'>
			<font color='#FFFFFF' size='-1'>
				Consultar Produto(s) Insumo(s)
			</font>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td width='150' colspan='2'>
			<font color='#FFFFFF' size='-1'>
				Referência
			</font>
		</td>
		<td width='450'>
			<font size="-1">
				Discriminação
			</font>
		</td>
	</tr>
<?
		for ($i = 0;  $i < $linhas; $i++) {
?>
	<tr class="linhanormal" onclick="return cor_clique_celula(this, '#C6E2FF')" onmouseover="return sobre_celula(this, '#CCFFCC')" onmouseout="return fora_celula(this, '#E8E8E8')">
		<td width='10' onclick="window.location = 'consultar_produto_insumo.php?passo=2&id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>&id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>'">
                    <a href='#' onclick="window.location = 'consultar_produto_insumo.php?passo=2&id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>&id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>'">
                        <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                    </a>
		</td>
		<td width='140' onclick="window.location = 'consultar_produto_insumo.php?passo=2&id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>&id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>'" align="center">
			<a href='#' onclick="window.location = 'consultar_produto_insumo.php?passo=2&id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>&id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>'">
				<?=$campos[$i]['referencia'];?>
			</a>
		</td>
		<td>
			<?=$campos[$i]['discriminacao'];?>
		</td>
	</tr>
<?
		}
?>
	<tr class="linhacabecalho" align="center">
		<td colspan='3'>
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="window.close()" style="color:red" class="botao">
			<input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" onclick="window.location = 'consultar_produto_insumo.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>'" class="botao">
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
	$data_sys = date('Y-m-d H:i:s');
	$sql = "Update produtos_acabados_custos set id_produto_insumo = '$id_produto_insumo', id_funcionario = '$id_funcionario', data_sys = '$data_sys' where id_produto_acabado_custo = '$id_produto_acabado_custo' limit 1";
	bancos::sql($sql);
?>
	<Script Language = 'JavaScript'>
		window.opener.document.form.submit()
		window.location = 'alterar_etapa2.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>'
	</Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Produto Insumo ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href='../../../../css/layout.css' type=text/css rel=stylesheet>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
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
</html>
</head>
<body onLoad="return iniciar()";>
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1'; ?>" onSubmit="return validar()">
<input type='hidden' name='id_produto_acabado_custo' value='<?=$id_produto_acabado_custo;?>'>
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
	<tr align='center'>
		<td colspan='2' width="750">
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class='linhacabecalho'>
		<td colspan="2" align='center'>
			<font color='#FFFFFF' size='-1'>
				Consultar Produto(s) Insumo(s)
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
			<input type="radio" name="opt_opcao" value="1" onclick="return iniciar()"; title="Consultar Produtos Insumos por: Referência" id='label'>
			<label for="label">Referência</label>
		</td>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="2" checked onClick="return iniciar()"; title="Consultar Produtos Insumos por: Referência" id='label2'>
			<label for="label2">Discrimina&ccedil;&atilde;o</label>
		</td>
    </tr>
	<tr>
		<td colspan="2" class="linhanormal">
			<input type='checkbox' name='opcao' onClick='limpar()' value='3' tabindex='3' title="Consultar todos os Produtos Insumos" class="checkbox" id='label3'>
			<label for="label3">Todos os registros</label>
		</td>
	</tr>
	<tr align="center">
		<td colspan="2" class="linhacabecalho">
			<input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = false;limpar();" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="fechar(window)" style="color:red" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
<?}?>