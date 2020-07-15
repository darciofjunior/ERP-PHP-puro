<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos_new.php');
session_start('funcionarios');
if($tela == 1) {//Veio da tela de Todos os P.A.
    segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_todos.php', '../../../../');
}else if($tela == 2) {//Veio da tela dos P.A. do Tipo Esp.
    segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_esp.php', '../../../../');
}
$mensagem[1] = '<font class="erro">SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>';
$mensagem[2] = '<font class="confirmacao">PRODUTO INSUMO INCLUÍDO COM SUCESSO PARA P.A.</font>';
$mensagem[3] = '<font class="erro">PRODUTO INSUMO JÁ EXISTENTE(S) ESTE PARA P.A.</font>';

//Inserção dos produtos acabados vs produtos insumos
if($passo == 1) {
	if($inserir == 1) {
		foreach($cmb_produto_insumo as $id_produto_insumo) {
			$sql = "Select id_pac_pi from pacs_vs_pis where id_produto_acabado_custo = '$id_produto_acabado_custo' and id_produto_insumo = '$id_produto_insumo' limit 1";
			$campos = bancos::sql($sql);
			if(count($campos) == 0) {
				$sql = "Insert into pacs_vs_pis (`id_pac_pi`, `id_produto_acabado_custo`, `id_produto_insumo`, `qtde`) values ('', '$id_produto_acabado_custo', '$id_produto_insumo', '1')";
				bancos::sql($sql);
				custos::procurar_fornecedor_default($id_produto_insumo);
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
				window.location = 'alterar_etapa3.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>'
			}
		</Script>
<?
	}
}
//Fim da Inserção

if($passo == 1) {
//Aqui vasculha todos os produtos que são do tipo aço
	$sql= "select pi.id_produto_insumo ";
	$sql.="from produtos_insumos pi, produtos_insumos_vs_acos pia ";
	$sql.="where pi.id_produto_insumo = pia.id_produto_insumo order by pi.id_produto_insumo ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	for($i = 0; $i < $linhas; $i++) {
		$id_produtos_insumos = $id_produtos_insumos.$campos[$i]['id_produto_insumo'].',';
	}
	$id_produtos_insumos = substr($id_produtos_insumos, 0, strlen($id_produtos_insumos) - 1);
//Traz todos os produtos que não sejam embalagem, aço, tratamento térmico, prac e usinagem
	switch($opt_opcao) {
		case 1:
			$sql= "select pi.id_produto_insumo, pi.discriminacao ";
			$sql.="from produtos_insumos pi, grupos g ";
			$sql.="where g.referencia like '%$txt_consultar%' and pi.id_grupo = g.id_grupo and g.ativo = 1 and g.id_grupo not in (8, 11, 9, 17) and pi.id_produto_insumo not in ($id_produtos_insumos) order by pi.discriminacao asc ";
		break;
		case 2:
			$sql= "select pi.id_produto_insumo, pi.discriminacao ";
			$sql.="from produtos_insumos pi, grupos g ";
			$sql.="where pi.discriminacao like '%$txt_consultar%' and pi.id_grupo = g.id_grupo and pi.ativo = 1 and g.id_grupo not in (8, 11, 9, 17) and pi.id_produto_insumo not in ($id_produtos_insumos) order by pi.discriminacao asc ";
		break;
		default:
			$sql= "select pi.id_produto_insumo, pi.discriminacao ";
			$sql.="from produtos_insumos pi, grupos g ";
			$sql.="where pi.id_grupo = g.id_grupo and pi.ativo = 1 and g.id_grupo not in (8, 11, 9, 17) and pi.id_produto_insumo not in ($id_produtos_insumos) order by pi.discriminacao asc ";
		break;
	}
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	if($linhas < 1) {
?>
		<Script Language = 'JavaScript'>
			window.location = 'incluir_produto_insumo.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>&valor=1'
		</Script>
<?
		exit;
	}
}
?>
<html>
<head>
<title>.:: Consultar Produto(s) Insumo(s) ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href='../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
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
		alert('SELECIONE UM PRODUTO INSUMO !')
		return false
	}else if(selecionados > 10) {
		alert('EXCEDIDO O NÚMERO DE PRODUTO(S) INSUMO(S) SELECIONADO(S) !\n\nPERMITIDO NO MÁXIMO 10 REGISTROS POR VEZ !')
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
          <input type="text" name="txt_consultar" size="45" maxlength="45" class="caixadetexto">
        </div>
      </td>
    </tr>
    <tr class='linhanormal'>
      <td width="20%">
        <input type="radio" name="opt_opcao" value="1"  onclick="return iniciar()"; title="Consultar Produtos Acabados por: Referência" id='label'>
		<label for='label'>
        Referência</label></td>
      <td width="20%">
        <input type="radio" name="opt_opcao" checked value="2" onClick="return iniciar()"; title="Consultar Produtos Acabados por: Referência" id='label2'>
		<label for='label2'>
        Discrimina&ccedil;&atilde;o</label></td>
    </tr>
    <tr class="linhanormal">
	  <td colspan="2">
        <input type='checkbox' name='opcao' onClick='limpar()' value='4' tabindex='4' title="Consultar todos os Produtos Acabados" class="checkbox" id='label4'>
		<label for='label4'>
        Todos os registros</label></td>
    </tr>
<?
	if($passo == 1) {
?>
	<tr class="linhanormal" align="center">
		<td colspan="2">
			<select name="cmb_produto_insumo[]" class="combo" size="5" multiple>
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
				<option value="<?=$campos[$i]['id_produto_insumo'];?>"><?=$campos[$i]['discriminacao'];?></option>
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
