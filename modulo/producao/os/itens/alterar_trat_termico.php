<?
echo 'ESSE ARQUIVO É UTILIZADO ?';
echo '<br>'.$PHP_SELF;
exit;

require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');
segurancas::geral('/erp/albafer/modulo/producao/os/incluir.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>CUSTO ATUALIZADO COM SUCESSO.</font>";

if($passo == 1) {
	$chkt_peso_aco_manual = 1;
	$sql = "Update pacs_vs_pis_trat set `peso_aco` = '$txt_peso_unit_saida', peso_aco_manual = '$chkt_peso_aco_manual' where id_pac_pi_trat = '$id_pac_pi_trat' limit 1";
	bancos::sql($sql);
//Atualização do Funcionário que alterou os dados no custo
	$data_sys = date('Y-m-d H:i:s');
	$sql = "Update produtos_acabados_custos set id_funcionario = '$id_funcionario', data_sys = '$data_sys' where id_produto_acabado_custo = '$id_produto_acabado_custo' limit 1";
	bancos::sql($sql);
	$valor = 1;
}

/*****************************************************************************************************/
/*                                             Dúvida                                                */
/*****************************************************************************************************/
/*Por enquanto eu busco esse id_produto_acabado_custo, porque eu ainda preciso por causa dos cálculos,
e também porque está indefinido ainda, não sabe se o Roberto vai manter essa parte ou vai tirar ????*/
$sql = "Select id_produto_acabado_custo from `pacs_vs_pis_trat` where `id_pac_pi_trat` = '$id_pac_pi_trat' limit 1";
$campos = bancos::sql($sql);
$id_produto_acabado_custo = $campos[0]['id_produto_acabado_custo'];

/*Nessa parte o sistema já deixa preparado o valor do peso aço da etapa 2, caso o
usuário venha tirar do modo manual e desejar colocar do modo automático*/
$sql = "Select id_produto_insumo, comprimento_1, comprimento_2, qtde_lote from produtos_acabados_custos where id_produto_acabado_custo = '$id_produto_acabado_custo' limit 1";
$campos2 		= bancos::sql($sql);
$id_produto_insumo	= $campos2[0]['id_produto_insumo'];
$comprimento_a		= $campos2[0]['comprimento_1'];
$qtde_lote		= $campos2[0]['qtde_lote'];
$comprimento_b		= $campos2[0]['comprimento_2'];
$comprimento_total	= ($comprimento_a + $comprimento_b) / 1000;

$sql = "Select pia.densidade_aco ";
$sql.= "from produtos_insumos pi, produtos_insumos_vs_acos pia ";
$sql.= "where pi.id_produto_insumo = '$id_produto_insumo' ";
$sql.= "and pi.id_produto_insumo = pia.id_produto_insumo limit 1";
$campos2 = bancos::sql($sql);
if(count($campos2) == 1) {
	$densidade = $campos2[0]['densidade_aco'];
}else {
	$densidade = 1;
}
$peso_aco_kg = $densidade * $comprimento_total;
/*****************************************************************************************************/

//Busca de um valor para fator custo para etapa 5
$sql = "Select valor from variaveis where id_variavel = '10' limit 1";
$campos = bancos::sql($sql);
$fator_custo5 = $campos[0]['valor'];

//Aqui trago todos os dados do Produto com o $id_pac_pi_trat
$sql = "Select u.sigla, pi.id_produto_insumo, pi.discriminacao, ppt.fator, ppt.peso_aco, ppt.peso_aco_manual, ppt.lote_minimo_fornecedor 
        from produtos_insumos pi, pacs_vs_pis_trat ppt, unidades u 
        where ppt.id_pac_pi_trat = '$id_pac_pi_trat' 
        and ppt.id_produto_insumo = pi.id_produto_insumo 
        and pi.id_unidade = u.id_unidade order by ppt.id_pac_pi_trat asc limit 1";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Custo de Trat. Térmico / Galvanoplastia ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript'>
function calculo_etapa5() {
	var fator_custo = eval('<?=$fator_custo5;?>')
	var preco_kg_rs = eval(strtofloat(document.form.txt_preco_unitario_kg_rs5.value))
	
	if(document.form.txt_peso_unit_saida.value != '') {//Diferente de Vázio
		var peso_aco = eval(strtofloat(document.form.txt_peso_unit_saida.value))
	}else {
		var peso_aco = 0
	}
	if(document.form.txt_fator_tt5.value != '') {//Diferente de Vázio
		var fator_tt = eval(strtofloat(document.form.txt_fator_tt5.value))
	}else {//Vazio
		var fator_tt = 0
	}
	/*
//Ignora a multiplicação pelo fator_tt
	if(document.form.chkt_peso_aco_manual.checked == true) {
		document.form.txt_total5.value = (preco_kg_rs * peso_aco * fator_custo)
	}else {
		document.form.txt_total5.value = (fator_tt * preco_kg_rs * peso_aco * fator_custo)
	}
	*/
	document.form.txt_total5.value = (preco_kg_rs * peso_aco * fator_custo)
//////////////////////////////////////////////////////////
	if(document.form.chkt_lote_minimo.checked == true) {// Se estiver setado ou checked acionar o calculo abaixo de lote minimo por fornecedor default por pedido
		var lote_minimo_fornecedor = eval(strtofloat(document.form.txt_lote_minimo.value));
		var lote_custo=eval(<?=$qtde_lote;?>);
		var preco_peca_corte=lote_minimo_fornecedor/lote_custo;
		total_pecas_s_fator=eval(document.form.txt_total5.value)/fator_custo
		if(total_pecas_s_fator < preco_peca_corte) { // preco_peca_corte=>5
			document.form.txt_total5.value = preco_peca_corte*fator_custo;
		}else { //segue o mesmo calculo
		}
	}

/********* Parte Nova de Código de Adaptação do Roberto **********/
	/*
	if(document.form.chkt_peso_aco_manual.checked == true) {
		document.form.txt_peso_peca_corrigo.value = peso_aco
	}else {//Aqui só iguala as caixas
//Aqui é o cálculo para o novo campo que apareceu peso de peça corrigido
		document.form.txt_peso_peca_corrigo.value = fator_tt * peso_aco
	}
	*/
	document.form.txt_peso_peca_corrigo.value = peso_aco
	document.form.txt_peso_peca_corrigo.value = arred(document.form.txt_peso_peca_corrigo.value, 3, 1)
/*****************************************************************/

	if(isNaN(document.form.txt_total5.value)) {
		document.form.txt_total5.value = ''
	}else {
		document.form.txt_total5.value = arred(document.form.txt_total5.value, 2, 1)
	}
}

function desabilitar_peso_aco() {
/********* Parte Nova de Código de Adaptação do Roberto **********/
//Verifico qual é a unidade do Produto Insumo
	var unidade_insumo = '<?=$campos[0]["sigla"];?>'
//Quando for Diferente de Unidade Realiza o Cálculo Normalmente
	if(unidade_insumo != 'UN') {//Checkbox Habilitado
		/*
		if(document.form.chkt_peso_aco_manual.checked == true) {
			//document.form.txt_fator_tt5.disabled = true
			//document.form.txt_fator_tt5.value = '0,00'
			//document.form.txt_fator_tt5.style.color = 'gray'
			//document.form.txt_fator_tt5.style.background = '#FFFFE1'
			//document.form.txt_peso_unit_saida.disabled = false
			document.form.txt_peso_unit_saida.value = '<?=number_format($campos[0]["peso_aco"], 3, ",", ".");?>'
			//document.form.txt_peso_unit_saida.style.color = '#000000'
			//document.form.txt_peso_unit_saida.style.background = '#FFFFFF'
			//document.form.txt_peso_unit_saida.focus()
		}else {//Checkbox Desabilitado
			//document.form.txt_peso_unit_saida.disabled = true
			//document.form.txt_peso_unit_saida.value = '<?=number_format($peso_aco_kg, 3, ",", ".");?>'
			//document.form.txt_peso_unit_saida.style.color = 'gray'
			//document.form.txt_peso_unit_saida.style.background = '#FFFFE1'
			//calculo_etapa5()
			//document.form.txt_fator_tt5.disabled = false
			//document.form.txt_fator_tt5.style.color = '#000000'
			//document.form.txt_fator_tt5.style.background = '#FFFFFF'
			//document.form.txt_fator_tt5.focus()
		}*/
		document.form.txt_peso_unit_saida.value = '<?=number_format($campos[0]["peso_aco"], 3, ",", ".");?>'
//Quando for unidade, tem essa particularização para alguns produtos
	}else {//Aqui o sistema sugere 1 no Peso do Aço
		/*
		if(document.form.chkt_peso_aco_manual.checked == true) {//Checkbox Habilitado
			//document.form.txt_fator_tt5.disabled = true
			//document.form.txt_fator_tt5.value = '0,00'
			//document.form.txt_fator_tt5.style.color = 'gray'
			//document.form.txt_fator_tt5.style.background = '#FFFFE1'
			//document.form.txt_peso_unit_saida.disabled = false
			
			var peso_aco = eval('<?=$campos[0]["peso_aco"];?>')
			if(peso_aco != 1) {
				document.form.txt_peso_unit_saida.value = peso_aco
			}else {
				document.form.txt_peso_unit_saida.value = 1
			}
			document.form.txt_peso_unit_saida.value = arred(document.form.txt_peso_unit_saida.value, 3, 1)
			//document.form.txt_peso_unit_saida.style.color = '#000000'
			//document.form.txt_peso_unit_saida.style.background = '#FFFFFF'
			//document.form.txt_peso_unit_saida.focus()
		}else {//Checkbox Desabilitado
			//document.form.txt_peso_unit_saida.disabled = true
			//document.form.txt_peso_unit_saida.value = '<?=number_format($peso_aco_kg, 3, ",", ".");?>'
			//document.form.txt_peso_unit_saida.style.color = 'gray'
			//document.form.txt_peso_unit_saida.style.background = '#FFFFE1'
			//calculo_etapa5()
			//document.form.txt_fator_tt5.disabled = false
			//document.form.txt_fator_tt5.style.color = '#000000'
			//document.form.txt_fator_tt5.style.background = '#FFFFFF'
			//document.form.txt_fator_tt5.focus()
		}*/
		var peso_aco = eval('<?=$campos[0]["peso_aco"];?>')
		if(peso_aco != 1) {
			document.form.txt_peso_unit_saida.value = peso_aco
		}else {
			document.form.txt_peso_unit_saida.value = 1
		}
	}
}

function validar() {
//Peso Total de Saída
	if(!texto('form', 'txt_peso_total_saida', '1', '1234567890,.', 'PESO TOTAL DE SAÍDA', '2')) {
		return false
	}
//Verificação do Fator_TT5
	if(document.form.txt_fator_tt5.disabled == false) {
		var fator_tt = eval(strtofloat(document.form.txt_fator_tt5.value))
		if(fator_tt == 0 || typeof(fator_tt) == 'undefined') {
			alert('FATOR T.T. INVÁLIDO ! \nVALOR IGUAL A ZERO OU ESTÁ VÁZIO !')
			document.form.txt_fator_tt5.focus()
			document.form.txt_fator_tt5.select()
			return false
		}
	}
	document.form.txt_fator_tt5.disabled = false
	document.form.txt_peso_unit_saida.disabled = false
	limpeza_moeda('form', 'txt_fator_tt5, txt_peso_unit_saida, ')
//Aqui é para não atualizar o frames abaixo desse Pop-UP
	document.form.nao_atualizar.value = 1
	atualizar_abaixo()
//Submetendo o Formulário
	document.form.submit()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
	if(document.form.nao_atualizar.value == 0) {
		window.opener.document.form.submit()
	}
}

//Para não dar problema no início da tela, na hora em q carregar o body
function retornar_foco() {
	if(document.form.txt_fator_tt5.disabled == false) {
		document.form.txt_fator_tt5.focus()
	}
}

function calcular_peso_unit_saida() {
	var qtde_saida = document.form.txt_qtde_saida.value
	var peso_total_saida = eval(strtofloat(document.form.txt_peso_total_saida.value))
//Para não dar erro de divisão por zero
	if(qtde_saida == '' || qtde_saida == 0 || typeof(peso_total_saida) == 'undefined' || peso_total_saida == 0) {
		peso_unitario_saida = 0
		
	}else {
		peso_unitario_saida = peso_total_saida / qtde_saida
	}
	document.form.txt_peso_unit_saida.value = peso_unitario_saida
	document.form.txt_peso_unit_saida.value = arred(document.form.txt_peso_unit_saida.value, 3, 1)
}

//Função que herda a qtde de saída corrente do Checkbox selecionada do Pop-Up abaixo dessa Tela
function herdar_qtde_saida_pop_abaixo() {
//Essa variável índice é p/ qual linha do Pop-Up de baixo que invocou esse outro Pop-up q é essa Tela
	var indice = eval('<?=$indice;?>')
	var objetos_linha = 7
	var objetos_fim = 6
//Esses elementos com a qual eu me refiro, são objetos do Pop-Up de baixo
	var elementos = opener.document.form.elements
//O parâmetro índice - é o Índice da linha
	if(indice == 0) {
		posicao = 1
	}else {
		posicao = indice * objetos_linha
		posicao++
	}
//A variável posição é p/ saber com qual objeto eu vou trabalhar na tela
	if(elementos[posicao].type == 'checkbox' && elementos[posicao].checked == true) {
		document.form.txt_qtde_saida.value = elementos[posicao + 2].value
	}
}

function desabilitado() {
	alert('DESABILITADO TEMPORARIAMENTE !')
	return false
}
</Script>
</head>
<body onload="herdar_qtde_saida_pop_abaixo();desabilitar_peso_aco();calculo_etapa5();document.form.txt_peso_total_saida.focus()" onunload="atualizar_abaixo()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onsubmit="return validar()">
<input type='hidden' name='id_pac_pi_trat' value="<?=$id_pac_pi_trat;?>">
<!--Essa variável índice é p/ qual linha do Pop-Up de baixo que invocou esse outro Pop-up q é essa Tela-->
<input type='hidden' name='indice' value="<?=$indice;?>">
<input type='hidden' name='nao_atualizar'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr onClick="showHide('calculo_fator_tt'); return false">
		<td height="18" align="center">
			<font color="yellow" size="2">
				&nbsp;
			</font>
		</td>
		<td align="right">
			&nbsp;
			<span id="statuscalculo_fator_tt"></span>
			<span id="statuscalculo_fator_tt"></span>
		</td>
	</tr>
	<tr onClick="showHide('calculo_fator_tt'); return false">
		<td colspan="2">
			<iframe src="calculo_fator_tt.php?tela=<?=$tela;?>" name="calculo_fator_tt" id="calculo_fator_tt" marginwidth="0" marginheight="0" style="display: none;" frameborder="0" height="260" width="100%" scrolling="auto"></iframe>
		</td>
	</tr>
</table>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr align='center'>
		<td colspan='2'>
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class='linhacabecalho' align='center'>
		<td colspan="2">
			Alterar Custo de Trat. Térmico / Galvanoplastia
		</td>
	</tr>
	<tr class='linhadestaque'>
		<td colspan="2"><font color='#FFFFFF' size='-1'> <font color="#FFFF00">Ref.:</font>
		Trat - <font color="#FFFF00">Und.:</font>
		<?=$campos[0]['sigla'];?>
		- <font color="#FFFF00">Discrim.:</font>
		<?=$campos[0]['discriminacao'];?>
			</font> <font color='#FFFFFF' size='-1'>&nbsp; </font>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="153"> Fator T.T.: </td>
		<td width="390">
			<input type="text" name="txt_fator_tt5" value="<?=number_format($campos[0]['fator'], 2, ',', '.');?>" id="txt_fator_tt5" onKeyUp="verifica(this, 'moeda_especial', '2', '', event)" size="8" class="textdisabled" disabled>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>Fornecedor Default:</td>
		<td>
		<?
                    $id_fornecedor_default 	= custos::preco_custo_pi($campos[0]['id_produto_insumo'], 0, 1);

                    //Busco na Lista de Preços o Lote Mínimo em R$ do Fornecedor e do PI na Lista de Preços ...
                    $sql = "SELECT f.razaosocial, fpi.lote_minimo_reais 
                            FROM `fornecedores_x_prod_insumos` fpi 
                            INNER JOIN `fornecedores` f ON f.id_fornecedor = fpi.id_fornecedor 
                            WHERE fpi.`id_fornecedor` = '$id_fornecedor_default' 
                            AND fpi.`id_produto_insumo` = '".$campos[0]['id_produto_insumo']."' LIMIT 1 ";
                    $campos_fornec = bancos::sql($sql);
                    echo $campos_fornec[0]['razaosocial'];
		?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>Valor do Lote M&iacute;nimo R$:</td>
		<td>
			<input type="text" name="txt_lote_minimo" id="txt_lote_minimo" value="<?=number_format($campos_fornec[0]['lote_minimo_reais'], 2, ',', '.');?>" size="12" class="disabled" disabled>
			<?
				if($campos[0]['lote_minimo_fornecedor'] == 1) {
					$checked_lote_minimo = 'checked';
				}else {
					$checked_lote_minimo = '';
				}
			?>
			<input type="checkbox" name="chkt_lote_minimo" value="1" id="chkt_lote_minimo" onClick="desabilitado();calculo_etapa5()" <?=$checked_lote_minimo;?> class="checkbox" disabled>
			<label for="chkt_lote_minimo">C&aacute;lculo por Lote Mínimo</label>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>P. Unit&aacute;rio / Kg R$: </td>
		<td>
			<?
				$dados_pi 	= custos::preco_custo_pi($campos[0]['id_produto_insumo']);
				$preco_pi 	= $dados_pi['valor_comum'];
			?>
			<input type="text" name="txt_preco_unitario_kg_rs5" value="<?=number_format($preco_pi, 2, ',', '.');?>" size="12" class="disabled" disabled>
			&nbsp;
			<?=$campos[0]['sigla'];?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>Qtde de Saída:</td>
		<td>
			<input type="text" name="txt_qtde_saida" title="Digite a Quantidade de Saída" onKeyUp="verifica(this, 'aceita', 'numeros', '', event);calcular_peso_unit_saida()" size="12" class="textdisabled" disabled>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td><b>Peso Total de Saída:</b></td>
		<td>
			<input type="text" name="txt_peso_total_saida" title="Digite o Peso Total de Saída" onKeyUp="verifica(this, 'moeda_especial', '3', '', event);calcular_peso_unit_saida()" size="12" class="caixadetexto">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>Peso Unitário de Saída:</td>
		<td>
			<?
				/*if($campos[0]['sigla'] == 'UN') {//Quando for = a Unidade
					$checked = 'checked';
				}else {//Quando for != Unidade
					$peso_aco = $campos[0]['peso_aco'];
					if($campos[0]['peso_aco_manual'] == 1) {
						$checked = 'checked';
					}else {
						$checked = '';
					}
				}*/
				$checked = 'checked';
			?>
			<input type="text" name="txt_peso_unit_saida" value="<?=number_format($peso_aco, 3, ',', '.');?>" id="txt_peso_unit_saida" size="7" class="textdisabled" disabled>
			&nbsp; 
			<input type="checkbox" name="chkt_peso_aco_manual" value="1" id="alterar" onclick="desabilitar_peso_aco();calculo_etapa5()" <?=$checked;?> class="checkbox" disabled>
			<label for="alterar">Alterar</label>
			&nbsp;-&nbsp;Peso / Peça corrigido pelo Fator
			<input type="text" name="txt_peso_peca_corrigo" id="txt_peso_peca_corrigo" size="7" class="disabled" disabled>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td> Total R$: </td>
		<?
//Ignora a multiplicação pelo fator_tt
			if($campos[0]['peso_aco_manual'] == 1) {
				$total = $preco_custo * $campos[0]['peso_aco'] * $fator_custo5;
			}else {
				$total = $campos[0]['fator'] * $preco_custo * $campos[0]['peso_aco'] * $fator_custo5;
			}
		?>
		<td>
			<input type="text" name="txt_total5" value="<?=number_format($total, 2, ',', '.');?>" id="txt_total5" size="12" class="disabled" disabled>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');herdar_qtde_saida_pop_abaixo();desabilitar_peso_aco();calculo_etapa5();document.form.txt_peso_total_saida.focus()" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="fechar(window)" class="botao"> 
		</td>
	</tr>
</table>
</form>
</body>
</html>