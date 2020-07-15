<?
require('../../../../lib/segurancas.php');
require('../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_revenda/pa_componente_revenda_esp.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = '<font class="confirmacao">FORNECEDOR ATRELADO COM SUCESSO PARA P.A.</font>';
$mensagem[3] = '<font class="erro">FORNECEDOR JÁ EXISTENTE PARA ESTE P.A.</font>';

//Inserção dos produtos acabados vs produtos insumos
if($passo == 1) {
	if($inserir == 1) {
		$data_sys = date('Y-m-d H:i:s');
//Verifico se o P.A. já tem relação com o PI
		$sql = "Select id_produto_insumo 
                        from `produtos_insumos` 
                        where id_produto_acabado = '$id_produto_acabado' 
                        AND `id_produto_insumo` > '0' 
                        AND `ativo` = '1' limit 1 ";
		$campos = bancos::sql($sql);
		if(count($campos) == 1) {//Significa que encontrou
			$id_produto_insumo = $campos[0]['id_produto_insumo'];
		}else {//Não encontrou, chama a função de importar P.A. p/ P.I.
			$id_produto_insumo = intermodular::importar_patopi($id_produto_acabado);
		}
//Disparo do Foreach
		foreach($cmb_fornecedor as $id_fornecedor) {
//Aqui atrela o fornecedor para o P.I.
			$retorno = intermodular::incluir_varios_pi_fornecedor($id_fornecedor, $id_produto_insumo);
			if($retorno != 0) {//Fornecedor incluido com sucesso
				$valor = 2;
			}else {//Fornecedor já existe
				$valor = 3;
			}
		}
?>
		<Script Language = 'JavaScript'>
			window.opener.document.form.submit()
			window.location = 'atrelar_fornecedor.php?id_produto_acabado=<?=$id_produto_acabado;?>&valor=<?=$valor;?>'
		</Script>
<?
	}
}
//Fim da Inserção

if($passo == 1) {
	switch($opt_opcao) {
		case 1://Razão Social
			$sql= "select id_fornecedor, razaosocial ";
			$sql.="from fornecedores ";
			if($opt_internacional == 1) {
				$sql.="where razaosocial like '%$txt_consultar%' and ativo = '1' and id_pais <> '31' and razaosocial <> '' order by razaosocial ";
			}else {
				$sql.="where razaosocial like '%$txt_consultar%' and ativo = '1' and id_pais = '31' and razaosocial <> '' order by razaosocial ";
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
			$sql= "select id_fornecedor, razaosocial ";
			$sql.="from fornecedores ";
			if($opt_internacional == 1) {//Internacional
				$sql.="where ativo = '1' and id_pais <> '31' and razaosocial <> '' order by razaosocial asc ";
			}else {//Nacional
				$sql.="where ativo = '1' and id_pais = '31' and razaosocial <> '' order by razaosocial asc ";
			}
		break;
	}
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	if($linhas < 1) {
?>
		<Script Language = 'JavaScript'>
			window.location = 'atrelar_fornecedor.php?passo=0&id_produto_acabado=<?=$id_produto_acabado;?>&valor=1'
		</Script>
<?
	}
}
?>
<html>
<head>
<title>.:: Atrelar Fornecedor(es) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function enviar() {
	var i, elementos = document.form.elements
	var selecionados = 0
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
		alert('SELECIONE UM FORNECEDOR !')
		return false
	}else if(selecionados > 10) {
		alert('EXCEDIDO O NÚMERO DE FORNECEDOR(ES) SELECIONADO(S) !\n\nPERMITIDO NO MÁXIMO 10 REGISTROS POR VEZ !')
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
<body onLoad="return iniciar()";>
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
<input type='hidden' name='id_produto_acabado' value="<?=$id_produto_acabado;?>">
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
	<tr align='center'>
		<td colspan='2' width="750">
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class='linhacabecalho' align='center'>
		<td colspan="2">
			<font color='#FFFFFF' size='-1'>
				Atrelar Fornecedor
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
<?
	if($passo == 1) {
?>
	<tr class="linhanormal" align="center">
		<td colspan="2">
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
				for($i = 0; $i < $linhas; $i ++) {
?>
				<option value="<?=$campos[$i]['id_fornecedor'];?>"><?=$campos[$i]['razaosocial'];?></option>
<?
				}
?>
			</select>
		</td>
	</tr>
<?
	}
?>
	<tr align="center">
		<td colspan="2" class="linhacabecalho">
			<input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = false;limpar();" style="color:#ff9900;" class="botao">
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