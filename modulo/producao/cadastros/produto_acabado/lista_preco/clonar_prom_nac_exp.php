<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
session_start('funcionarios');
segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/lista_preco/lista_preco.php', '../../../../../');

$mensagem[1] = "<font class='confirmacao'>PROMOÇÃO CLONADA COM SUCESSO.</font>";

//Retira a Promoção dos Produtos p/ as Empresas Divisões selecionadas
if(!empty($_POST['chkt_empresa_divisao'])) {
	foreach($_POST['chkt_empresa_divisao'] as $id_empresa_divisao) {
/*Traz todos os grupos_empresas_divisão p/ poder achar os produtos através
da id_empresa_divisao selecionada*/
		$sql = "Select pa.id_produto_acabado 
				from `gpas_vs_emps_divs` ged 
				inner join produtos_acabados pa on pa.id_gpa_vs_emp_div = ged.id_gpa_vs_emp_div and pa.preco_promocional <> 0 
				where ged.id_empresa_divisao = '$id_empresa_divisao' ";
		$campos = bancos::sql($sql);
		$linhas = count($campos);
		for($i = 0; $i < $linhas; $i++) {
			$id_produto_acabado = $campos[$i]['id_produto_acabado'];
//Aqui já retira promoção de todos os P.A. através do id_grupo_empresa_divisao
			$sql = "Update produtos_acabados set `promocao_export` = 'S' where `id_produto_acabado` = '$id_produto_acabado' ";
			bancos::sql($sql);
		}
		$valor = 1;
	}
}
?>
<html>
<head>
<title>.:: Clonar Item(ns) da Promoção Nacional p/ Exportação ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
	var x, mensagem = '', valor = false, elementos = document.form.elements
	for (x = 0; x < elementos.length; x ++)   {
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
		resposta = confirm('VOCÊ TEM CERTEZA QUE DESEJA CLONAR ESSA PROMOÇÃO ?')
		if(resposta == true) {
			return true
		}else {
			return false
		}
	}
}
</Script>
</head>
<body>
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onsubmit="return validar()">
<table border="0" width="700" align="center" cellspacing ='1' cellpadding='1' onmouseover='total_linhas(this)'>
	<tr align='center'>
		<td colspan='2' width="750">
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class='linhacabecalho'>
		<td colspan="2" align='center'>
			<font color='#FFFFFF' size='-1'>
				Clonar Item(ns) da Promoção Nacional p/ Exportação
			</font>
		</td>
	</tr>
	<tr class='linhadestaque' align='center'>
		<td>
			<font color='#FFFFFF' size='-1'>
				Empresa Divisão / Item(ns) c/ Promoção
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				<label for='todos'>Todos </label><input type="checkbox" name="chkt" onClick="return selecionar('form', 'chkt', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox" id='todos'>
			</font>
		</td>
	</tr>
<?
//Listagem das Empresas Divisões
	$sql = "Select id_empresa_divisao, razaosocial 
			from empresas_divisoes 
			where ativo = 1 order by razaosocial asc ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	for($i = 0; $i < $linhas; $i++) {
?>
	<tr class="linhanormal" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" onmouseover="return sobre_celula(this, '#CCFFCC')" onmouseout="return fora_celula(this, '#E8E8E8')" align="center">
		<td>
		<?
			echo $campos[$i]['razaosocial'];
//Aqui eu verifico o Total de Promoção por Divisão ...
			$sql = "Select count(pa.id_produto_acabado) as total_promocao_divisao 
					from `empresas_divisoes` ed 
					inner join gpas_vs_emps_divs ged on ged.id_empresa_divisao = ed.id_empresa_divisao 
					inner join produtos_acabados pa on pa.id_gpa_vs_emp_div = ged.id_gpa_vs_emp_div and pa.preco_promocional <> 0 
					where ed.id_empresa_divisao = '".$campos[$i]['id_empresa_divisao']."' ";
			$campos_total_promocao_divisao = bancos::sql($sql);
			echo ' <b>('.$campos_total_promocao_divisao[0]['total_promocao_divisao'].')</b>';
		?>
		</td>
		<td>
			<input type="checkbox" name="chkt_empresa_divisao[]" value="<?=$campos[$i]['id_empresa_divisao']?>" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" class="checkbox">
		</td>
	</tr>
<?
	}
?>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'lista_preco.php'" class='botao'>
			<input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' style="color:#ff9900;" class='botao'>
			<input type='submit' name='cmd_clonar' value='Clonar' title='Clonar' class='botao'>
		</td>
	</tr>
</table>
</form>
</body>
</html>