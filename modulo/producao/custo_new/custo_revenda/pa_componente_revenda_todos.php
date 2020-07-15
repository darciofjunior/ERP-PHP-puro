<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='atencao'>NÃO HÁ PRODUTO(S) ACABADO(S) CADASTRADO(s) PARA ESSE FORNECEDOR.</font>";

if($passo == 1) {
	switch($opt_opcao) {
		case 1://Razão Social
			$sql = "Select id_fornecedor, razaosocial 
					from fornecedores ";
			if($opt_internacional == 1) {
				$sql.= "where razaosocial like '%$txt_consultar%' 
						and ativo = '1' 
						and id_pais <> '31' 
						and razaosocial <> '' order by razaosocial ";
			}else {
				$sql.= "where razaosocial like '%$txt_consultar%' 
						and ativo = '1' 
						and id_pais = '31' 
						and razaosocial <> '' order by razaosocial ";
			}
		break;
		case 2://CNPJ
                        $txt_consultar = str_replace('.', '', $txt_consultar);
                        $txt_consultar = str_replace('.', '', $txt_consultar);
                        $txt_consultar = str_replace('/', '', $txt_consultar);
			$txt_consultar = str_replace('-', '', $txt_consultar);
			
			$sql= "select id_fornecedor, razaosocial ";
			$sql.="from fornecedores ";
			if($opt_internacional == 1) {
				$sql.="where `cnpj_cpf` like '$txt_consultar%' and ativo = '1' and id_pais <> '31' and razaosocial <> '' order by `cnpj_cpf` ";
			}else {
				$sql.="where `cnpj_cpf` like '$txt_consultar%' and ativo = '1' and id_pais = '31' and razaosocial <> '' order by `cnpj_cpf` ";
			}
		break;
		default://Todos
			$sql = "Select id_fornecedor, razaosocial 
					from fornecedores ";
			if($opt_internacional == 1) {//Internacional
				$sql.= "where ativo = '1' 
						and id_pais <> '31' 
						and razaosocial <> '' order by razaosocial asc ";
			}else {//Nacional
				$sql.= "where ativo = '1' 
						and id_pais = '31' 
						and razaosocial <> '' order by razaosocial asc ";
			}
		break;
	}
	$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
	$linhas = count($campos);
	if($linhas < 1) {
?>
		<Script Language = 'JavaScript'>
			window.location = 'pa_componente_revenda_todos.php?valor=1'
		</Script>
<?	} else {  ?>
<html>
<head>
<title>.:: Consultar Fornecedor(es) ::.</title>
<meta http-equiv = 'Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content='no-store'>
<meta http-equiv = 'pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<form name="form" method="POST" action="<?=$PHP_SELF.'?passo=2';?>" onsubmit="validar()">
<table width='600' border=0 align='center' cellspacing=1 cellpadding=1 onmouseover="total_linhas(this)";>
	<tr>
		<td colspan='4'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-1'>
				<div align='center'>
				</div>
			</font>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td>
			<font color='#FFFFFF' size='-1'>
				Consultar Fornecedor(es)
			</font>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td><font color='#FFFFFF' size='-1'>
<?
		if($opt_internacional == 1) {
			echo 'Internacional(s)';
		}else {
			echo 'Nacional(s)';
		}
?>
		</font></td>
	</tr>
<?
		for ($i = 0;  $i < $linhas; $i++) {
			$id_fornecedor = $campos[$i]['id_fornecedor'];
			$razaosocial = $campos[$i]['razaosocial'];
?>
			<tr class="linhanormal">
				<td align="center">
					<a href="consultar_produtos.php?id_fornecedor=<?=$id_fornecedor;?>&razaosocial=<?=$razaosocial;?>" class="link">
						<?=$campos[$i]['razaosocial'];?>
					</a>
				</td>
			</tr>
<?
		}
?>
	<tr class="linhacabecalho">
		<td align="center">
			<input type="button" class="botao" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'pa_componente_revenda_todos.php?passo=0'">
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
}else {
?>
<html>
<head>
<title>.:: Consultar Fornecedor(es) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
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
</head>
<body onLoad="return iniciar()";>
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
				Consultar Fornecedor
			</font>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan='2'>
			<div align='center'>Consultar
				<input type="text" name="txt_consultar" size='45' maxlength='45' class="caixadetexto">
			</div>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="1" checked onclick="return iniciar()" title="Consultar fornecedores por: Razão Social" id='label'>
			<label for="label">Razão Social</label>
		</td>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="2" onclick="return iniciar()" title="Consultar fornecedores por: CNPJ ou CPF" id='label2'>
			<label for="label2">CNPJ / CPF</label>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="20%">
			<input type='checkbox' name='opt_internacional' value='1' tabindex='3' title="Consultar fornecedores internacionais" class="checkbox" id='label3'>
			<label for="label3">Internacionais</label>
		</td>
		<td width="20%">
			<input type='checkbox' name='opcao' onclick='limpar()' value='3' tabindex='4' title="Consultar todos os fornecedores" class="checkbox" id='label4'>
			<label for="label4">Todos os registros</label>
		</td>
	</tr>
	<tr align="center">
		<td colspan="2" class="linhacabecalho">
			<input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = false;limpar();" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>