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
$mensagem[2] = '<font class="confirmacao">EMBALAGEM(NS) INCLUÍDA(S) COM SUCESSO PARA P.A.</font>';
$mensagem[3] = '<font class="erro">EMBALAGEM(NS) JÁ EXISTENTE(S) ESTE PARA P.A.</font>';

//Inserção das Embalagens vs Produtos Acabados
if($passo == 1) {
	if($inserir == 1) {
/*Aqui eu busco o id_produto_acabado através do id_produto_acabado_custo
porque preciso deste para poder gravar na tabela relacional pas_vs_pis_embs*/
		$sql = "Select id_produto_acabado from produtos_acabados_custos where id_produto_acabado_custo = '$id_produto_acabado_custo' limit 1";
		$campos = bancos::sql($sql);
		$id_produto_acabado = $campos[0]['id_produto_acabado'];

//Verifica se já existe pelo menos alguma embalagem atrelada p/ o produto acabado
		$sql = "Select id_pa_pi_emb from pas_vs_pis_embs where id_produto_acabado = '$id_produto_acabado' limit 1";
		$campos = bancos::sql($sql);
		$linhas = count($campos);
/*Significa q tem pelo menos uma embalagem atrelada sendo assim, com certeza essa
deve ser a embalagem principal*/
		if($linhas == 1) {
			$count = 1;
		}else {
			$count = 0;
		}
		foreach($cmb_embalagem as $id_embalagem) {
			$sql = "Select id_pa_pi_emb from pas_vs_pis_embs where id_produto_acabado = '$id_produto_acabado' and id_produto_insumo = '$id_embalagem' limit 1";
			$campos = bancos::sql($sql);
			if(count($campos) == 0) {
				if($count == 0) {//Primeira embalagem a ser inserida
					$embalagem_default = 1;
					$count++;
				}else {//Demais embalagens q estão sendo inseridas
					$embalagem_default = 0;
				}
				$sql = "Insert into pas_vs_pis_embs (`id_pa_pi_emb`, `id_produto_acabado`, `id_produto_insumo`, `embalagem_default`) values ('', '$id_produto_acabado', '$id_embalagem', '$embalagem_default')";
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
				window.location = 'alterar_etapa1.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>'
			}
		</Script>
<?
	}
}
//Fim da Inserção

if($passo == 1) {
//Só traz os produtos insumos em que o grupo é do tipo "embalagem"
	switch($opt_opcao) {
		case 1:
			$sql= "select pi.id_produto_insumo, pi.discriminacao ";
			$sql.="from produtos_insumos pi, grupos g ";
			$sql.="where g.referencia like '%$txt_consultar%' and pi.ativo = '1' and g.id_grupo = pi.id_grupo and pi.id_grupo = '8' order by pi.discriminacao ";
		break;
		case 2:
			$sql= "select pi.id_produto_insumo, pi.discriminacao ";
			$sql.="from produtos_insumos pi, grupos g ";
			$sql.="where pi.discriminacao like '%$txt_consultar%' and pi.ativo = '1' and g.id_grupo = pi.id_grupo and pi.id_grupo = '8' order by pi.discriminacao ";
		break;
		case 3:
			$sql= "select pi.id_produto_insumo, pi.discriminacao ";
			$sql.="from produtos_insumos pi, grupos g ";
			$sql.="where pi.observacao like '%$txt_consultar%' and pi.ativo = 1 and pi.id_grupo = g.id_grupo and pi.id_grupo = '8' order by pi.discriminacao ";
		break;
		default:
			$sql= "select pi.id_produto_insumo, pi.discriminacao ";
			$sql.="from produtos_insumos pi, grupos g ";
			$sql.="where pi.ativo = '1' and pi.id_grupo = g.id_grupo and pi.id_grupo = '8' order by pi.discriminacao ";
		break;
	}
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	if($linhas < 1) {
?>
		<Script Language = 'JavaScript'>
			window.location = 'incluir_embalagem.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>&valor=1'
		</Script>
<?
		exit;
	}
}
?>
<html>
<head>
<title>.:: Consultar Embalagem(ns) ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href='../../../../css/layout.css' type=text/css rel=stylesheet>
<Script Language = 'JavaScript'>
function enviar() {
	var elementos = document.form.elements
	var selecionados = 0, id_embalagem = ''
	for (i = 0; i < elementos.length; i++) {
		if(document.form.elements[i].type == 'select-multiple') {
			for(j = 1; j < document.form.elements[i].length; j++) {
				if(document.form.elements[i][j].selected == true) {
					selecionados ++
					id_embalagem = id_embalagem + document.form.elements[i][j].value + ',';
				}
			}
		}
	}

	if(selecionados == 0) {
		alert('SELECIONE UMA EMBALAGEM !')
		return false
	}else if(selecionados > 10) {
		alert('EXCEDIDO O NÚMERO DE EMBALAGEM(NS) SELECIONADA(S) !\n\nPERMITIDO NO MÁXIMO 10 REGISTROS POR VEZ !')
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
				Consultar Embalagem(ns)
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
			<input type="radio" name="opt_opcao" value="1" onclick="return iniciar()"; title="Consultar Produtos Insumos por: Referência" id='label'>
			<label for='label'>Referência</label>
		</td>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="2" onClick="return iniciar()"; checked  title="Consultar Produtos Insumos por: Referência" id='label2'>
			<label for='label2'>Discrimina&ccedil;&atilde;o</label>
		</td>
	</tr>
	<tr>
		<td width="20%" class="linhanormal">
			<input type="radio" name="opt_opcao" value="3" onClick="return iniciar()"; title="Consultar Produtos Insumos por: Observação" id='label3'>
			<label for='label3'>Observação</label>
		</td>
		<td width="20%" class="linhanormal">
			<input type='checkbox' name='opcao' onClick='limpar()' value='4' tabindex='4' title="Consultar todos os Produtos Insumos" class="checkbox" id='label4'>
			<label for='label4'>Todos os registros</label>
		</td>
	</tr>
<?
	if($passo == 1) {
?>
	<tr class="linhanormal" align="center">
		<td colspan="2">
			<select name="cmb_embalagem[]" class="combo" size="5" multiple>
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
			<input type="button" name="cmd_selecionar" value="Selecionar Todos" title="Selecionar Todos" onclick="selecionar_todos()" class="botao">
			<input type="button" name="cmd_adicionar" value="Adicionar" title="Adicionar" onclick="enviar()" class="botao">
<?
	}
?>
			<input type="submit" name="cmdconsultar" value="Consultar" tabindex="5" class="botao" title="Consultar">
			<input type="button" name="cmd_fechar" style="color:red" value="Fechar" title="Fechar" onclick="window.close()" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
<Script Language = 'JavaScript'>
/*Funções referentes a primeira tela antes de fazer a consulta*/
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
</Script>
</html>
