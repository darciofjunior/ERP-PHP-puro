<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos_new.php');
require('../../../../lib/producao.php');
session_start('funcionarios');
if($tela == 1) {//Veio da tela de Todos os P.A.
    segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_todos.php', '../../../../');
}else if($tela == 2) {//Veio da tela dos P.A. do Tipo Esp.
    segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_esp.php', '../../../../');
}
$mensagem[1] = "<font class='confirmacao'>CUSTO ATUALIZADO COM SUCESSO.</font>";

if($passo == 1) {
//Busca do Produto Acabado e Produto Insumo atual antes da Alteração ...
	$sql = "Select id_produto_acabado, id_produto_insumo 
			from `produtos_acabados_custos` 
			where id_produto_acabado_custo = '$id_produto_acabado_custo' limit 1 ";
	$campos_custo = bancos::sql($sql);
	$id_produto_acabado = $campos_custo[0]['id_produto_acabado'];
	$id_produto_insumo_custo = $campos_custo[0]['id_produto_insumo'];//Produto Insumo Atual ...
//Verifico se houve alguma alteração do Produto Insumo "Aço" ...
	if($id_produto_insumo_custo != $_POST['cmb_produto_insumo']) {//Se houve alteração então chamo a Função ...
		producao::verificar_ops_com_baixa_nao_finalizadas($id_produto_acabado, $id_produto_insumo_custo, $_POST['cmb_produto_insumo'], 2);
	}
//Etapa 2 - Atualização na tabela Produtos Acabados Custos + o Funcionário
	$data_sys = date('Y-m-d H:i:s');
	$sql = "Update `produtos_acabados_custos` set `qtde_lote` = '$_POST[txt_lote_custo]', `id_produto_insumo` = '$_POST[cmb_produto_insumo]', `id_funcionario` = '$_SESSION[id_funcionario]', `peso_kg` = '$_POST[txt_peso_aco_kg]', `peca_corte` = '$_POST[txt_pecas_corte]', `comprimento_1` = '$_POST[txt_comprimento_a]', `comprimento_2` = '$_POST[txt_comprimento_b]', `data_sys` = '$data_sys' where `id_produto_acabado_custo` = '$id_produto_acabado_custo' limit 1 ";
	bancos::sql($sql);

//Atualiza o Peso Aço da Etapa 5, mas somente quando o peso estiver no modo manual = 0
	$txt_peso_aco_kg = $txt_peso_aco_kg / 1.05;//Essa variável tem q abater 5% a - nessa etapa
	$sql = "Update `pacs_vs_pis_trat` set `peso_aco` = '$txt_peso_aco_kg' where `id_produto_acabado_custo` = '$id_produto_acabado_custo' and `peso_aco_manual` = '0' ";
	bancos::sql($sql);
	$valor = 1;
}

//Busca de um valor para fator custo para etapa 2
$fator_custo_2 = genericas::variavel(11);

/*Essa variável vai estar sendo acionada para o caso de o usuário digitar na qtde
um valor maior do que 1000*/
$fator_custo_2_new = genericas::variavel(18);

/*Aqui traz todos os produtos insumos que estão relacionados ao produto acabado
passado por parâmetro*/
$sql = "Select id_produto_insumo, qtde_lote, peso_kg, peca_corte, comprimento_1, comprimento_2 
		from produtos_acabados_custos 
		where id_produto_acabado_custo = '$id_produto_acabado_custo' limit 1 ";
$campos = bancos::sql($sql);

//Somente na primeira em que carregar a tela
/*Tem esse controle por que às vezes não se quer exatamente trocar a matéria prima dessa etapa, mas sim 
fazer algumas simulações com outros produtos*/
//Iguala o Produto ao retorno da consulta do Banco de Dados
if(empty($_POST['cmb_produto_insumo'])) $_POST['cmb_produto_insumo'] = $campos[0]['id_produto_insumo'];

$qtde_lote = $campos[0]['qtde_lote'];
/*Aqui verifica se a quantidade do lote é > 1000, porque caso isso aconteça então sofrerá alterações no 
valor do fator de custo da Etapa 2 ...*/
if($qtde_lote > 1000) $fator_custo_2 = $fator_custo_2_new;
$pecas_corte 	= ($campos[0]['peca_corte'] == 0) ? 1 : $campos[0]['peca_corte'];
$comprimento_a 	= (!empty($_POST['txt_comprimento_a'])) ? $_POST['txt_comprimento_a'] : $campos[0]['comprimento_1'];
$comprimento_b 	= (!empty($_POST['txt_comprimento_b'])) ? $_POST['txt_comprimento_b'] : $campos[0]['comprimento_2'];

/*Aqui eu trago o produto acabado do produto acabado custo que está
armazenado em um hidden*/
$sql = "Select id_produto_acabado, operacao_custo as operacao_custo_prac 
		from produtos_acabados_custos 
		where id_produto_acabado_custo = '$id_produto_acabado_custo' limit 1 ";
$campos2 = bancos::sql($sql);
$id_produto_acabado = $campos2[0]['id_produto_acabado'];
$operacao_custo_prac = $campos2[0]['operacao_custo_prac'];

//Aqui eu pego a referencia do produto acabado
$sql = "Select referencia 
		from produtos_acabados 
		where id_produto_acabado = '$id_produto_acabado' limit 1 ";
$campos2 = bancos::sql($sql);
$referencia = $campos2[0]['referencia'];

//Busco a família a qual pertence esse produto acabado
$sql = "Select f.id_familia 
		from produtos_acabados pa 
		inner join gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
		inner join grupos_pas gpa on gpa.id_grupo_pa = ged.id_grupo_pa 
		inner join familias f on f.id_familia = gpa.id_familia 
		where pa.id_produto_acabado = '$id_produto_acabado' limit 1 ";
$campos2 = bancos::sql($sql);
$id_familia 	= $campos2[0]['id_familia'];
$total_indust 	= custos::todas_etapas($id_produto_acabado, $operacao_custo_prac);

$id_produto_insumo_selected = (!empty($_POST['cmb_produto_insumo'])) ? $_POST['cmb_produto_insumo'] : $campos[0]['id_produto_insumo'];
?>
<html>
<head>
<title>.:: Alterar Custo A&ccedil;o / Outros Metais ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function calculo_etapa2() {
	var fator_custo = eval('<?=$fator_custo_2;?>')
	var qtde_lote = '<?=$qtde_lote;?>'
	var comprimento_a = strtofloat(document.form.txt_comprimento_a.value)
	if(comprimento_a == '') {
		comprimento_a = 0
	}
	var comprimento_b = strtofloat(document.form.txt_comprimento_b.value)
	if(comprimento_b == '') {
		comprimento_b = 0
	}
	document.form.txt_comprimento_total.value = (eval(comprimento_a) + eval(comprimento_b)) / 1000
	if(document.form.txt_comprimento_total.value == 0) {
		document.form.txt_comprimento_total.value = ''
	}else {
		document.form.txt_comprimento_total.value = arred(document.form.txt_comprimento_total.value, 3, 1)
	}
	if(document.form.txt_comprimento_total.value != '' && document.form.txt_densidade_kg_m.value) {
		var comprimento_total = strtofloat(document.form.txt_comprimento_total.value)
		if(comprimento_total == '') {
			comprimento_total = 0
		}
		var pecas_corte = document.form.txt_pecas_corte.value
		if(pecas_corte == 0 || pecas_corte == '' || pecas_corte == '0.00') {
			pecas_corte = 1
		}
		var densidade_kg_m = strtofloat(document.form.txt_densidade_kg_m.value)
		
		document.form.txt_peso_aco_kg.value = eval(densidade_kg_m) * eval(comprimento_total) * 1.05
		document.form.txt_peso_aco_kg.value = document.form.txt_peso_aco_kg.value / pecas_corte
		var peso_aco_kg = document.form.txt_peso_aco_kg.value; //passo para esta var pq tenhoi um calculo embaixo com o mesmo
		document.form.txt_peso_aco_kg.value = arred(document.form.txt_peso_aco_kg.value, 3, 1)

//Cálculo das Qtde necessária para o Lote Kg
		document.form.txt_lote_custo_calculo1.value = (peso_aco_kg * qtde_lote)
//Cálculo das Qtde necessária para o Lote Metros
		document.form.txt_lote_custo_calculo2.value = document.form.txt_lote_custo_calculo1.value / densidade_kg_m
//Arredondamentos
		document.form.txt_lote_custo_calculo1.value = arred(document.form.txt_lote_custo_calculo1.value, 2, 1)
		document.form.txt_lote_custo_calculo2.value = arred(document.form.txt_lote_custo_calculo2.value, 2, 1)

		
	}
	if(document.form.txt_peso_aco_kg.value != '' && document.form.txt_preco_rs_kg.value != '' && fator_custo != '') {
		var peso_aco_kg = strtofloat(document.form.txt_peso_aco_kg.value)
		var preco_rs_kg = strtofloat(document.form.txt_preco_rs_kg.value)
		document.form.txt_custo2.value = eval(peso_aco_kg) * eval(preco_rs_kg) * eval(fator_custo)
		document.form.txt_custo2.value = arred(document.form.txt_custo2.value, 2, 1)
	}
	var referencia = '<?=$referencia;?>'
	var operacao_custo = '<?=$operacao_custo_prac;?>'
//Só pode fazer a comparação se o Produto for do tipo Esp e a Operação de Custo for do Tipo Industrial
	if(referencia == 'ESP' && operacao_custo == 0) {
		var lote_custo	= <?=$qtde_lote;?>;
		if(pecas_corte>lote_custo) {
			document.form.txt_lote_custo.value = document.form.txt_pecas_corte.value
		} else {
			for(var temp=lote_custo; temp > 1; temp--) {
				if(temp % pecas_corte == 0) {
					document.form.txt_lote_custo.value = temp
					temp = 0
				}
			}
		}
	}
}

function validar() {
	var referencia = '<?=$referencia;?>'
	var operacao_custo = '<?=$operacao_custo_prac;?>'
	var id_familia = '<?=$id_familia;?>'
	var qtde_lote_original = '<?=$qtde_lote;?>'
	
//Aço - PI ...	
	if(!combo('form', 'cmb_produto_insumo', '', 'SELECIONE UM AÇO !')) {
		return false
	}
//Só pode fazer a comparação se o Produto for do tipo Esp e a Operação de Custo for do Tipo Industrial
	if(referencia == 'ESP' && operacao_custo == 0) {
		if(eval(document.form.txt_lote_custo.value) % eval(document.form.txt_pecas_corte.value) != 0) {
			alert('QUANTIDADE DE PEÇAS/CORTE INVÁLIDA ! QUANTIDADE DE PEÇAS/CORTE NÃO ESTÁ COMPATÍVEL COM A QUANTIDADE DO LOTE !')
			return false
		}
	}
//Aqui compara a quantidade do lote custo inicial 'PHP' com a quantidade do lote que foi recalculada 'JavaScript'
	if(qtde_lote_original != document.form.txt_lote_custo.value) {
		alert('A QUANTIDADE DE LOTE DO CUSTO FOI ALTERADA PARA '+document.form.txt_lote_custo.value+' PÇS !')
	}
	
/*Verifica se a família é do Tipo Bits e Bedames id => 10 ou do Tipo Pinos id => 2, caso entrar essa rotina 
então o sistema apenas da um aviso*/
	if((id_familia == 10 || id_familia == 2) && document.form.txt_comprimento_a.value >= 150) {
		alert('CUIDADO COMPRIMENTO >= 150 mm ! MATERIAL SUJEITO A EMPENAMENTO !!!')
		window.focus()
	}
	
	document.form.txt_lote_custo.disabled = false
	document.form.txt_peso_aco_kg.disabled = false
	limpeza_moeda('form', 'txt_peso_aco_kg, ')
//Aqui é para não atualizar o frames abaixo desse Pop-UP
	document.form.nao_atualizar.value = 1
	document.form.passo.value = 1
	atualizar_abaixo()
}

function alterar_produto_insumo() {
//Aqui é para não atualizar o frames abaixo desse Pop-UP
	document.form.nao_atualizar.value = 1
	document.form.submit()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
	if(document.form.nao_atualizar.value == 0) {
		window.opener.document.form.submit()
		//parent.ativar_loading()
	}
}

var contador = 0

function carregar_acos() {
	var geometria_aco = document.form.cmb_geometria_aco.value
	var qualidade_aco = document.form.cmb_qualidade_aco.value
	var bitola1_aco   = document.form.txt_bitola1_aco.value
	var bitola2_aco	  = document.form.txt_bitola2_aco.value

	if(contador == 0) { 
		var id_produto_insumo_selected = eval('<?=$id_produto_insumo_selected;?>')
		ajax('carregar_acos.php', 'cmb_produto_insumo', id_produto_insumo_selected)
	}else {
		ajax('carregar_acos.php', 'cmb_produto_insumo')
	}
	//Só irá desabilitar o Botão Salvar, quando carregar pelo menos um Aço dentro da Combo ...
	if(document.form.cmb_produto_insumo.length > 0) {
		document.form.cmd_salvar.disabled = false
		document.form.cmd_salvar.className = 'botao'
	}
	contador++
}
</Script>
<style type="text/css">
<!--
.style1 {font-size: 10px}
-->
</style>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4' onload="carregar_acos();document.form.txt_comprimento_a.focus()" onunload="atualizar_abaixo()">
<form name="form" method="post" action="<?=$PHP_SELF;?>" onsubmit="return validar()">
<input type='hidden' name='id_produto_acabado_custo' value="<?=$id_produto_acabado_custo;?>">
<!--***************Controle de Tela***************-->
<input type='hidden' name='passo'>
<input type='hidden' name='nao_atualizar'>
<!--**********************************************-->
<table width='780' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr align='center'>
		<td colspan='4'>
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class='linhacabecalho' align='center'>
		<td colspan="4">
			2&ordf; Etapa: Alterar Custo A&ccedil;o / Outros Metais
			&nbsp;&nbsp;&nbsp;&nbsp;<font color="yellow">&nbsp;Tot.R$ s/ICMS:</font>
			<input type="text" name="txt_custo2" value="<?=number_format($GLOBALS['etapa2_sem_impostos_sem_fator'], 2, ',', '.');?>" id="txt_custo2" size="15" class="textdisabled" disabled>
		</td>
	</tr>
	<?
		$sql = "Select u.sigla 
				from produtos_insumos pi 
				inner join unidades u on u.id_unidade = pi.id_unidade 
				where pi.id_produto_insumo = '$_POST[cmb_produto_insumo]' 
				and pi.ativo = 1 order by pi.discriminacao ";
		$campos_unidade	= bancos::sql($sql);
		$sigla 			= $campos_unidade[0]['sigla'];
	?>
	<tr class='linhadestaque' align='center'>
		<td colspan="4">
			Filtro para AÇO
		</td>
	</tr>
	<tr class="linhanormal">
		<td>
			<font color="darkblue">
				<b><i>Geometria: </i></b>
			</font>
			<select name="cmb_geometria_aco" title="Selecione a Geometria do Aço" onchange="carregar_acos()" class="combo">
			<?
				$sql = "Select id_geometria_aco, nome 
						from geometrias_acos 
						where ativo = '1' order by nome ";
				echo combos::combo($sql, $_POST['cmb_geometria_aco']);
			?>
			</select>
		</td>
		<td>
			<font color="darkblue">
				<b><i>Qualidade: </i></b>
			</font>
			<select name="cmb_qualidade_aco" title="Selecione a Qualidade do Aço" onchange="carregar_acos()" class="combo">
			<?
				$sql = "Select id_qualidade_aco, nome 
						from qualidades_acos 
						where ativo = '1' order by nome ";
				echo combos::combo($sql, $_POST['cmb_qualidade_aco']);
			?>
			</select>
		</td>
		<td>
			<font color="darkblue">
				<b><i>Bitola 1: </i></b>
			</font>
			<input type="text" name="txt_bitola1_aco" value="<?if(!empty($_POST['txt_bitola1_aco'])) {echo number_format($_POST['txt_bitola1_aco'], 2, ',', '.');}?>" title="Digite a Bitola 1 Aço" onkeyup="verifica(this, 'moeda_especial', '2', '', event);carregar_acos()" size="10" maxlength="20" class="caixadetexto"> mm
		</td>
		<td>
			<font color="darkblue">
				<b><i>Bitola 2: </i></b>
			</font>
			<input type="text" name="txt_bitola2_aco" value="<?if(!empty($_POST['txt_bitola2_aco'])) {echo number_format($_POST['txt_bitola2_aco'], 2, ',', '.');}?>" title="Digite a Bitola 2 Aço" onkeyup="verifica(this, 'moeda_especial', '2', '', event);carregar_acos()" size="10" maxlength="20" class="caixadetexto"> mm
		</td>
	</tr>
	<tr class="linhanormal">
		<td>
			<font color="darkblue">
				<b><i>Discriminação: </i></b>
			</font>
		</td>
		<td colspan="3">
			<select name="cmb_produto_insumo" id="cmb_produto_insumo" onChange="alterar_produto_insumo()" class="combo">
				<option value="">LOADING ...</option>
			</select>
			<?=$sigla;?>
		</td>
	</tr>
	<tr class='linhadestaque' align='center'>
		<td colspan="4">
			&nbsp;
		</td>
	</tr>
	<?
//Traz o preço custo e a densidade do produto insumo que está selecionado na combo
		$sql = "Select pia.densidade_aco 
				from produtos_insumos pi 
				inner join produtos_insumos_vs_acos pia on pia.id_produto_insumo = pi.id_produto_insumo 
				where pi.id_produto_insumo = '$_POST[cmb_produto_insumo]' limit 1 ";
		$campos2 = bancos::sql($sql);
		if(count($campos2) == 1) {
			$dados_pi 		= custos::preco_custo_pi($_POST['cmb_produto_insumo']);
			$preco_custo	= number_format($dados_pi['preco_comum'], 2, ',', '.');
			$icms 			= $dados_pi['icms'];
			$densidade 		= $campos2[0]['densidade_aco'];
		}else {
			$preco_custo 	= '';
			$icms			= '';
			$densidade 		= '';
		}
?>
	<tr class="linhanormal">
		<td width="200">P.Unit.R$ - ICMS c/Red</b></font></td>
		<td width="203"><i>Comprimento</i> </td>
		<td width="192"><i>Corte</i></td>
		<td width="200"><i>Comprimento Total:</i></td>
	</tr>
	<tr class="linhanormal">
		<td>
			<input type="text" name="txt_preco_rs_kg" value="<?=$preco_custo;?>" title="Preço R$ / Kg" size="15" class="textdisabled" disabled>&nbsp;/&nbsp;<?=$sigla;?>
			<font color="red"><b> - <?=number_format($icms, 2, ',', '.');?> %</b></font>
		</td>
		<td>
			<input type="text" name="txt_comprimento_a" value="<?=$comprimento_a;?>" title="Digite o Comprimento" onKeyUp="verifica(this, 'aceita', 'numeros_inteiros', '', event);calculo_etapa2()" size="8" class="caixadetexto"> MM&nbsp;&nbsp;
		</td>
		<td>
			<input type="text" name="txt_comprimento_b" value="<?=$comprimento_b;?>" title="Digite o Corte" onKeyUp="verifica(this, 'aceita', 'numeros', '', event);calculo_etapa2()" size="5" class="caixadetexto"> MM&nbsp;
		</td>
		<td>
		<?
			$comprimento_total = ($comprimento_a + $comprimento_b) / 1000;
		?>
			<input type="text" name="txt_comprimento_total" value="<?=number_format($comprimento_total, 3, ',', '.')?>" title="Comprimento Total" size="12" class="textdisabled" disabled>&nbsp;M
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Peças / Corte:</td>
		<td>Densidade Kg / M :</td>
		<td>Peso / KG + 5%: </td>
		<td>Qtde de lote do Custo:</td>
	</tr>
	<tr class="linhanormal">
		<td><input type="text" name="txt_pecas_corte" value="<?=$pecas_corte;?>" title="Digite as Peças / Corte" onKeyUp="verifica(this, 'aceita', 'numeros', '', event);if(this.value != '') {this.value = Math.round(this.value)};calculo_etapa2()" size="15" class="caixadetexto"></td>
		<td><input type="text" name="txt_densidade_kg_m" value="<?=number_format($densidade, 3, ',', '.');?>" title="Densidade de Kg / M" size="15" class="textdisabled" disabled></td>
		<td>
		<?
			$peso_aco_kg = $densidade * $comprimento_total * 1.05;
			$peso_aco_kg/= $pecas_corte;
		?>
			<input type="text" name="txt_peso_aco_kg" value="<?=number_format($peso_aco_kg, 3, ',', '.');?>" title="Peso KG" size="15" class="textdisabled" disabled>
		</td>
		<td>
			<input type="text" name="txt_lote_custo" value="<?=$qtde_lote;?>" title="Peso KG" id="txt_lote_custo" size="15" class="textdisabled" disabled>
		</td>
	</tr>
<?
//Aqui são os cálculos para q Qtde do Lote do Custo
	$lote_custo_calculo1 = $peso_aco_kg * $qtde_lote;
	$lote_custo_calculo2 = $lote_custo_calculo1 / $densidade;
?>
	<tr class="linhanormal">
		<td class="style1">
			<font color="#FFFF00"><em><font color="green">
				<strong>Qtde necessária p/ o Lote:</strong>
			</font></em></font>
		</td>
		<td>
			<font color="#FFFF00"><em>
				<input type="text" name="txt_lote_custo_calculo1" value="<?=number_format($lote_custo_calculo1, 3, ',', '.');?>" title="Qtde necessária p/ o Lote" id="txt_lote_custo_calculo1" size="15" class="textdisabled" disabled>
			</em><font color="#000000"> Kg</font></font>
		</td>
		<td>
			<font color="#FFFF00">
				<em><span class="style1"><font color="green">
					<strong>Qtde necessária p/ o Lote:</strong></font>
					<font color="#00FF00"> </font>
					<font color="#00FF00">
				</font></span></em>
			</font>
		</td>
		<td>
			<font color="#FFFF00"><em>
				<input type="text" name="txt_lote_custo_calculo2" value="<?=number_format($lote_custo_calculo2, 3, ',', '.');?>" title="Qtde necessária p/ o Lote" id="txt_lote_custo_calculo2" size="15" class="textdisabled" disabled>
				<font color="#000000">Metros</font>
			</em></font>
		</td>
	</tr>
<?
//Traz a quantidade em estoque do produto insumo
		$sql = "Select qtde as qtde_estoque 
				from estoques_insumos 
				where id_produto_insumo = '$_POST[cmb_produto_insumo]' limit 1 ";
		$campos2 = bancos::sql($sql);
		if(count($campos2) == 1) {
			$qtde_estoque = number_format($campos2[0]['qtde_estoque'], 2, ',', '.');
			$qtde_estoque2 = number_format(($campos2[0]['qtde_estoque']/$densidade), 2, ',', '.');
		}else {
			$qtde_estoque = '0,00';
			$qtde_estoque2 = '0,00';
		}
?>
	<tr class="linhanormal">
		<td class="style1">
			<font color="#FFFF00"><em><font color="green"><strong>Estoque do Produto Insumo:</strong></font> </em></font>
		</td>
		<td>
			<font color="#FFFF00"><em>
				<input type="text" name="txt_estoque" value="<?=$qtde_estoque;?>" title="Digite o Custor Fator" id="txt_estoque" size="15" class="textdisabled" disabled>
			</em> <font color="#000000"> Kg</font></font>
		</td>
		<td>
			<font color="#FFFF00"><em><span class="style1">
			<font color="green"><strong>Estoque do Produto Insumo:</strong></font><font color="#00FF00"> </font><font color="#00FF00"></font>
			</span></em></font>
		</td>
		<td>
			<font color="#FFFF00"><em>
				<input type="text" name="txt_estoque2" value="<?=$qtde_estoque2;?>" title="Digite o Custor Fator" id="txt_estoque2" size="15" class="textdisabled" disabled>
				<font color="#000000">Metros</font>
			</em></font>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="4">
			<input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');carregar_acos();calculo_etapa2();document.form.txt_comprimento_a.focus()" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="textdisabled" disabled>
			<input type="button" name="cmd_alterar_fornecedores" value="Alterar Fornecedores" title="Alterar Fornecedores" onClick="showHide('alterar_fornecedores'); return false" style="color:black" class="botao">
		</td>
	</tr>
</table>
<!--Agora sempre irá mostrar esse Iframe-->
<table width='870' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr>
		<td height="18" align="center">
			<font color="yellow" size="2">&nbsp;
				
			</font>
		</td>
		<td align="right">
			&nbsp;
			<span id="statusalterar_fornecedores"></span>
			<span id="statusalterar_fornecedores"></span>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<iframe src='../../../classes/produtos_insumos/marcar_fornecedor_default.php?id_produto_insumo=<?=$_POST['cmb_produto_insumo'];?>' name="alterar_fornecedores" id="alterar_fornecedores" marginwidth="0" marginheight="0" style="display: none;" frameborder="0" height="260" width="100%" scrolling="auto"></iframe>
		</td>
	</tr>
</table>
<!--Controle para saber se vai estar mostrando este Iframe para o Usuário-->
<?
//Verifico se esse PI corrente está em algum Pedido de Compras ...
	$sql = "Select id_item_pedido 
			from itens_pedidos 
			where id_produto_insumo = '$_POST[cmb_produto_insumo]' limit 1 ";
	$campos_pedido = bancos::sql($sql);
	if(count($campos_pedido) == 0) {//Como não está, exibo essa Tela com Todos os Fornecedores desse PI ...
?>
<Script Language = 'JavaScript'>
/*Idéia de Onload

Na primeira vez em que carregar essa Tela, caso venha existir algum Pedido de Compras para esse PI, então 
eu disparo por meio do JavaScript essa função para que já venha mostrar esse iframe ...*/
	showHide('alterar_fornecedores')
</Script>
<?
	}
?>
</form>
</body>
</html>