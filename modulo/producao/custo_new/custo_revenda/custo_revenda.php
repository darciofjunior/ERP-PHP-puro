<?
require('../../../../lib/segurancas.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/calculos.php');
require('../../../../lib/custos_new.php');
require('../../../../lib/data.php');
session_start('funcionarios');

if($tela == 1) {//Veio da tela de Todos os P.A.
    segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_revenda_esp.php', '../../../../');
}else if($tela == 2) {//Veio da tela dos P.A. do Tipo Esp.
    segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_revenda/pa_componente_revenda_esp.php', '../../../../');
}
$mensagem[1] = "<font class='confirmacao'>LISTA DE PREÇO ATUALIZADA COM SUCESSO.</font>";

//Aqui são as variáveis para o cálculo do custo
$taxa_financeira_vendas 		= genericas::variaveis('taxa_financeira_vendas');
$fator_importacao 				= genericas::variaveis('fator_importacao');
$taxa_financeiro 				= genericas::variavel(4);
$desconto_snf 					= genericas::variavel(5);
$valor_moeda_dolar_custo 		= genericas::variavel(7);
$valor_moeda_euro_custo 		= genericas::variavel(8);
$fator_desconto_maximo_vendas 	= genericas::variavel(19);

/***************Atualização da Lista de Preço e do Custo de Revenda***************/
if($passo == 1) {
//Significa que o usuário desejou marcar o Fornecedor corrente da Tela como Default
	if(!empty($chkt_marcar_default)) {
//Todo esse procedimento é para buscar o novo id_relacional id_fornecedor_prod_insumo p/ marcar st c/ default
            $sql = "SELECT id_produto_insumo 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' 
                    AND `id_produto_insumo` > '0' 
                    AND `ativo` = '1' ";
            $campos             = bancos::sql($sql);
            $id_produto_insumo  = $campos[0]['id_produto_insumo'];
//Atualização do Novo Fornecedor Default na tabela de PI ...
            $sql = "UPDATE `produtos_insumos` SET `id_fornecedor_default` = '$id_fornecedor' WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
            bancos::sql($sql);
	}
//Aqui eu busco a operação de custo do P.A.
	$sql = "Select referencia, operacao_custo, status_custo 
			from `produtos_acabados` 
			where `id_produto_acabado` = '$id_produto_acabado' limit 1";
	$campos = bancos::sql($sql);
//Agora com o P.A. e a operação de custo, busco quem é o PAC
	$sql = "Select id_produto_acabado_custo 
			from `produtos_acabados_custos` 
			where `id_produto_acabado` = '$id_produto_acabado' 
			and `operacao_custo` = '".$campos[0]['operacao_custo']."' limit 1 ";
	$campos2 = bancos::sql($sql);
//Só entra se encontrar o produto acabado na tela relacional de produtos_acabados_custos
	if(count($campos2) == 1) {
		$id_produto_acabado_custo = $campos2[0]['id_produto_acabado_custo'];
		$data_sys = date('Y-m-d H:i:s');
//Aqui é a Data de Atualização do Custo p/ que o sistema comece fazer a contagem a partir dos 90 dias
		$sql = "Update produtos_acabados_custos 
				set id_funcionario = '$id_funcionario', data_sys = '$data_sys' 
				where id_produto_acabado_custo = '$id_produto_acabado_custo' limit 1";
		bancos::sql($sql);
//Atualização do custo liberado para o produto acabado
		if(!empty($chkt_custo_liberado)) {//Selecionado
			$acao = 'SIM';
/***************************Tratamento de Margem de Lucro**************************/
//Antes de cair na função que já faz tudo automático, tem uma condição antes só para o caso o PA ser 'ESP'
			if($campos[0]['referencia'] == 'ESP') {
/*Listagem de Todos os Orçamento(s) que estão em Aberto, q não estão congelados, que contém esse Item
em que o prazo de Entrega seja igual a Imediato*/
				$sql = "Select ovi.id_orcamento_venda_item 
						from orcamentos_vendas_itens ovi 
						inner join orcamentos_vendas ov on ov.id_orcamento_venda = ovi.id_orcamento_venda and ov.status < 2 and ov.congelar = 'N' 
						where ovi.id_produto_acabado = '$id_produto_acabado' 
						and ovi.prazo_entrega_tecnico = '0.0' limit 1 ";
				$campos2 = bancos::sql($sql);
//Se encontrar algum item que tenha o prazo de entrega técnico como zerado, então não pode liberar o custo
				if(count($campos2) == 1) $acao = 'NAO';
			}
		}else {//Não selecionado
			$acao = 'NAO';
		}
		custos::liberar_desliberar_custo($id_produto_acabado_custo, $acao);
//Aqui eu mudo o status desse P.A. q foi migrado, p/ 0, p/ dizer q este já foi atualizado
		$sql = "UPDATE `produtos_acabados` SET `pa_migrado` = '0' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
		bancos::sql($sql);
	}
	$data_sys = date('Y-m-d H:i:s');
//Aqui existe esse tratamento com as variáveis para não furar o sql abaixo
	if(empty($txt_lote_minimo_compra)) 		$txt_lote_minimo_compra = '0';
	if(empty($txt_preco_fat_compra_nac_rs)) $txt_preco_fat_compra_nac_rs = '0.00';
	if(empty($txt_prazo_pgto_ddl)) 			$txt_prazo_pgto_ddl = '0.0';
	if(empty($txt_desc_vista)) 				$txt_desc_vista = '0.0';
	if(empty($txt_desc_sgd)) 				$txt_desc_sgd = '0.0';
	if(empty($txt_ipi)) 					$txt_ipi = 0;
	if(empty($txt_icms_forn)) 				$txt_icms_forn = '0.00';
	if(empty($txt_iva_forn)) 				$txt_iva_forn = '0.00';
	if(empty($cmb_forma_compra)) 			$cmb_forma_compra = 0;
	if(empty($txt_preco_forma_compra_nac_rs)) 	$txt_preco_forma_compra_nac_rs = '0.00';
	if(empty($txt_preco_forma_compra_export_hispania_rs)) 	$txt_preco_forma_compra_export_hispania_rs = '0.00';
//Aqui eu verifico se o usuário não alterou algum dado da lista de preço
	$sql = "SELECT id_fornecedor_prod_insumo 
			FROM `fornecedores_x_prod_insumos` 
			WHERE `preco_faturado` = '$txt_preco_fat_compra_nac_rs' 
			AND `prazo_pgto_ddl` = '$txt_prazo_pgto_ddl' 
			AND `desc_vista` = '$txt_desc_vista' 
			AND `desc_sgd` = '$txt_desc_sgd' 
			AND `ipi` = '$txt_ipi' 
			AND `icms` = '$txt_icms_forn' 
			AND `iva` = '$txt_iva_forn' 
			AND `forma_compra` = '$cmb_forma_compra' 
			AND `preco` = '$txt_preco_forma_compra_nac_rs' 
			AND `preco_exportacao` = '$txt_preco_forma_compra_export_hispania_rs' 
			AND `lote_minimo_pa_rev` = '$txt_lote_minimo_compra' 
			AND `id_fornecedor_prod_insumo` = '$id_fornecedor_prod_insumo' LIMIT 1 ";
	$campos = bancos::sql($sql);
	/*Parte de script que serve p/ atualizar o "custo do PI" - Se a Query acima não retornar nenhum registro, significa 
	q o usuário realizou alguma alteração em algum campo da Lista de Preço, então mexo no "Custo do PI" ...*/
	if(count($campos) == 0) {
		//Aqui eu zero os adicionais do produto na lista de preço ...
		$sql = "UPDATE `fornecedores_x_prod_insumos` SET `preco_faturado_adicional` = '0', `preco_faturado_export_adicional` = '0' WHERE `id_fornecedor_prod_insumo` = '$id_fornecedor_prod_insumo' LIMIT 1 ";
		bancos::sql($sql);
	}
	//Aqui atualiza normalmente a lista de preço ...
	$sql = "UPDATE `fornecedores_x_prod_insumos` SET `preco_faturado` = '$txt_preco_fat_compra_nac_rs', `prazo_pgto_ddl` = '$txt_prazo_pgto_ddl', `desc_vista` = '$txt_desc_vista', `desc_sgd` = '$txt_desc_sgd', `ipi` = '$txt_ipi', `icms` = '$txt_icms_forn', `iva` = '$txt_iva_forn', `forma_compra` = '$cmb_forma_compra', `preco` = '$txt_preco_forma_compra_nac_rs', `preco_exportacao` = '$txt_preco_forma_compra_export_hispania_rs', `data_sys` = '$data_sys', `lote_minimo_pa_rev` = '$txt_lote_minimo_compra' WHERE `id_fornecedor_prod_insumo` = '$id_fornecedor_prod_insumo' LIMIT 1 ";
	bancos::sql($sql);
	$valor = 1;
}
/*********************************************************************************/
/***************************Procedimento Normal da Tela***************************/
/***************************************ORÇAMENTOS*****************************************/
//Se for vazio, significa que esta tela está sendo acessado da Tela de Orçamentos
if(empty($id_fornecedor_prod_insumo)) {
	$sql = "SELECT id_produto_insumo 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
	$campos2 = bancos::sql($sql);
//Em primeiro verifico quem é o id_produto_insumo
	$id_produto_insumo = $campos2[0]['id_produto_insumo'];
//Em segundo verifico qual é o id_fornecedor_setado, já tenho mesmo o $id_produto_acabado
	$id_fornecedor_setado = custos::procurar_fornecedor_default_revenda($id_produto_acabado, "", "", 1); //busco somente o id_forncedor default para saber de qual forncedor q estou pegando para calcular o custo do PA revenda
//Em terceiro qual é o id_fornecedor_prod_insumo, agora já tenho $id_produto_insumo e o $id_fornecedor_setado
	$sql = "Select id_fornecedor_prod_insumo 
			from fornecedores_x_prod_insumos 
			where id_produto_insumo = '$id_produto_insumo' 
			and id_fornecedor = '$id_fornecedor_setado' limit 1 ";
	$campos2 = bancos::sql($sql);
	$id_fornecedor_prod_insumo = $campos2[0]['id_fornecedor_prod_insumo'];
/****************************************PRODUÇÃO******************************************/
//Veio da Tela de Custos do PA Revenda ...
}else {
	$sql = "SELECT pa.id_produto_acabado 
                FROM `fornecedores_x_prod_insumos` fpi 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = fpi.`id_produto_insumo` AND pa.ativo = 1 and pa.operacao_custo = 1 
                WHERE fpi.`id_fornecedor_prod_insumo` = '$id_fornecedor_prod_insumo' LIMIT 1 ";
	$campos = bancos::sql($sql);
//Em primeiro verifico quem é o id_produto_acabado
	$id_produto_acabado = $campos[0]['id_produto_acabado'];
//Em segundo verifico qual é o id_fornecedor_setado, já tenho mesmo o $id_produto_acabado
	$id_fornecedor_setado = custos::procurar_fornecedor_default_revenda($id_produto_acabado, "", "", 1); //busco somente o id_forncedor default para saber de qual forncedor q estou pegando para calcular o custo do PA revenda
}

//Criar uma Função ...
/******************************************************************************************************/
/*****************************Dados Preço de Compra pra Pedido*****************************************/
/******************************************************************************************************/
//Aqui eu busco dados da Lista de Compras do Fornecedor vs PI ...
$sql = "Select pa.id_produto_insumo, pa.id_gpa_vs_emp_div, pa.referencia, pa.operacao_custo, pa.status_custo, pa.observacao, gpa.nome, gpa.lote_min_producao_reais, fpi.* 
        FROM `fornecedores_x_prod_insumos` fpi 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = fpi.`id_produto_insumo` AND pa.`ativo` = '1' AND pa.`operacao_custo` = '1' 
        inner join gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
        inner join grupos_pas gpa on gpa.id_grupo_pa = ged.id_grupo_pa 
        where fpi.id_fornecedor_prod_insumo = '$id_fornecedor_prod_insumo' order by pa.referencia limit 1 ";
$campos_lista 	= bancos::sql($sql);

//Aqui traz o id do fornecedor para poder verificar qual que é o preço de produto padrão que ele tem
$id_fornecedor 				= $campos_lista[0]['id_fornecedor'];

$sql = "SELECT uf, id_pais, razaosocial 
		FROM `fornecedores` 
		WHERE `id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
$campos_fornecedor			= bancos::sql($sql);
$uf 						= $campos_fornecedor[0]['uf'];
$id_pais 					= $campos_fornecedor[0]['id_pais'];
$razaosocial 				= $campos_fornecedor[0]['razaosocial'];

$sql = "SELECT id_uf 
		FROM `ufs` 
		WHERE `sigla` = '$uf' LIMIT 1 ";
$campos_uf 	= bancos::sql($sql);
$id_uf		= $campos_uf[0]['id_uf'];

$operacao_custo 			= $campos_lista[0]['operacao_custo'];
//Já coloco essa variável aki, pq vou utilizar ela p/ alguns cálculos em JavaScript ...
$lote_min_producao_reais = $campos_lista[0]['lote_min_producao_reais'];

$preco_compra_faturado_rs 	= ($campos_lista[0]['preco_faturado'] == '0.00') ? '' : number_format($campos_lista[0]['preco_faturado'], 2, ',', '.');
$prazo_compra_ddl 			= ($campos_lista[0]['prazo_pgto_ddl'] == '0.0') ? '' : number_format($campos_lista[0]['prazo_pgto_ddl'], 1, ',', '.');
/********************Utilizo essas variáveis para cálculo -> valor_custo()************************/


if($id_pais == 31) {//Se for do Brasil o Preço de Lista ira para Hispania ...
	$preco_fat_compra_moeda_est 			= 0;
	$tp_moeda 								= 0;//Não existe Dólar e nem Euro ...
	$preco_fat_compra_export_hispania_rs 	= $campos_lista[0]['preco_faturado_export'];
}else {//Se for Internacional irá para Estrangeiros ...
	$preco_fat_compra_moeda_est 			= $campos_lista[0]['preco_faturado_export'];
	$tp_moeda 								= $campos_lista[0]['tp_moeda'];
	$preco_fat_compra_export_hispania_rs 	= 0;
}

$valor_moeda_compra 		= $campos_lista[0]['valor_moeda_compra'];
$preco_exportacao 			= number_format($campos_lista[0]['preco_exportacao'], 2, ',', '.');
if($id_pais != 31) {//Se o País do Fornecedor for <> de Brasil
	//Múltiplico por 1 p/ facilitar p/ o Roberto, mas somente quando for País Estrangeiro, por causa do Dólar, Euro
	if($valor_moeda_compra == '' || $valor_moeda_compra == '0.0000') $preco_exportacao = $preco_fat_compra_moeda_est * 1;
}
$desc_compra_avista 		= ($campos_lista[0]['desc_vista'] == '0.0') ? '' : number_format($campos_lista[0]['desc_vista'], 1, ',', '.');
$desc_compra_sgd 			= ($campos_lista[0]['desc_sgd'] == '0.0') ? '' : number_format($campos_lista[0]['desc_sgd'], 1, ',', '.');
$ipi_compra 				= $campos_lista[0]['ipi'];
$icms_c_red_compra 			= ($campos_lista[0]['icms'] == '0.00') ? '' : number_format($campos_lista[0]['icms'], 2, ',', '.');
$iva_compra 				= ($campos_lista[0]['iva'] == '0.00') ? '' : number_format($campos_lista[0]['iva'], 2, ',', '.');
$forma_compra 				= $campos_lista[0]['forma_compra'];
$preco_compra_nac 			= ($campos_lista[0]['preco'] == '0.00' || $campos_lista[0]['preco'] == '') ? '' : number_format($campos_lista[0]['preco'], 2, ',', '.');
$lote_min_compra 			= $campos_lista[0]['lote_minimo_pa_rev'];
$status_custo 				= $campos_lista[0]['status_custo'];

//Aqui eu busco dados da Lista de Compras do PI Padrão deste Fornecedor - Só existe um PI Padrão por fornecedor ...
$sql = "SELECT * 
		FROM `fornecedores_x_prod_insumos` 
		WHERE `id_fornecedor` = '$id_fornecedor' 
		AND condicao_padrao = 1 limit 1 ";
$campos_padrao = bancos::sql($sql);
if(count($campos_padrao) == 0) {//Não Achou
	$prazo_compra_ddl_padrao 	= '0,0';
	$desc_compra_avista_padrao 	= '0,0';
	$desc_compra_sgd_padrao 	= '0,0';
	$ipi_compra_padrao 			= 0;
	$icms_c_red_compra_padrao 	= '0,00';
	$iva_compra_padrao 			= '0,00';
}else {//Achou
	$prazo_compra_ddl_padrao 	= ($campos_padrao[0]['prazo_pgto_ddl'] == '0.0') ? '0,0' : number_format($campos_padrao[0]['prazo_pgto_ddl'], 1, ',', '.');
	$desc_compra_avista_padrao 	= ($campos_padrao[0]['desc_vista'] == '0.0') ? '0,0' : number_format($campos_padrao[0]['desc_vista'], 1, ',', '.');
	$desc_compra_sgd_padrao 	= ($campos_padrao[0]['desc_sgd'] == '0.0') ? '0,0' : number_format($campos_padrao[0]['desc_sgd'], 1, ',', '.');
	$ipi_compra_padrao 			= $campos_padrao[0]['ipi'];
	$icms_c_red_compra_padrao 	= ($campos_padrao[0]['icms'] == '0.00') ? '0,00' : number_format($campos_padrao[0]['icms'], 2, ',', '.');
	$iva_compra_padrao 			= ($campos_padrao[0]['iva'] == '0.00') ? '0,00' : number_format($campos_padrao[0]['iva'], 2, ',', '.');
	$forma_compra_padrao 		= $campos_padrao[0]['forma_compra'];
}
?>
<html>
<head>
<title>.:: Custo Revenda ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Lote Mínimo p/ Compra
	if(!texto('form', 'txt_lote_minimo_compra', '1', '0123456789', 'LOTE MÍNIMO P/ COMPRA', '2')) {
		return false
	}
//Se o Valor de Lote Mínimo for = Zero, então eu considero este como sendo inválido ...
	if(document.form.txt_lote_minimo_compra.value == 0) {
		alert('VALOR DE LOTE MÍNIMO INVÁLIDO !')
		document.form.txt_lote_minimo_compra.focus()
		document.form.txt_lote_minimo_compra.select()
		return false
	}
/*Caso exista o Preço Fat. de Moeda Estrangeira, então não preciso forçar o preenchimento do campo 
Preço Faturado...*/
	var preco_fat_moeda_estrangeira = eval(strtofloat(document.form.txt_preco_fat_compra_moeda_est.value))
	if(preco_fat_moeda_estrangeira == 0) {
//Preço Faturado
		if(!texto('form', 'txt_preco_fat_compra_nac_rs', '3', '0123456789,.', 'PREÇO FATURADO', '2')) {
			return false
		}
	}
//Prazo Pagto DLL
	if(!texto('form', 'txt_prazo_pgto_ddl', '3', '0123456789,.', 'PRAZO DE PAGTO DDL', '2')) {
		return false
	}
//Desconto à Vista
	if(!texto('form', 'txt_desc_vista', '3', '0123456789,.', 'DESCONTO À VISTA', '2')) {
		return false
	}
//Desconto SGD
	if(!texto('form', 'txt_desc_sgd', '3', '0123456789,.', 'DESCONTO SGD', '2')) {
		return false
	}
//IPI
	if(!texto('form', 'txt_ipi', '1', '0123456789', 'IPI', '2')) {
		return false
	}
//ICMS
	/*if(!texto('form', 'txt_icms_forn', '3', '0123456789,.', 'ICMS', '2')) {
		return false
	}
//Reforço (rs) ...
	if(document.form.txt_icms_forn.value == '0,00') {
		alert('DIGITE O ICMS % !')
		document.form.txt_icms_forn.focus()
		document.form.txt_icms_forn.select()
		return false
	}*/
//Se existir Valor de Moeda Estrangeira e estiver sem Tipo de Moeda preenchido então forço o preenchimento ...
	if(eval(strtofloat(document.form.txt_preco_fat_moeda_estrangeira.value)) > 0 && document.form.cmb_tipo_moeda.value == '') {
		alert('ESTE CUSTO NÃO PODE SER SALVO, PORQUE ESTÁ SEM O TIPO DE MOEDA PREENCHIDO !!!\n\nCONTATE O DEPARTAMENTO DE COMPRAS PARA FAZER A CORREÇÃO !')
		return false
	}
//IVA
	if(document.form.txt_iva_forn.value != '') {
		if(!texto('form', 'txt_iva_forn', '3', '0123456789,.', 'IVA', '2')) {
			return false
		}
	}
//Forma de Compra
	if(!combo('form', 'cmb_forma_compra', '', 'SELECIONE A FORMA DE COMPRA !')) {
		return false
	}
	var indice_forma_compra = document.form.cmb_forma_compra.value
	var forma_compra = document.form.cmb_forma_compra[indice_forma_compra].text
	var preco_compra_nacional = document.form.txt_preco_forma_compra_nac_rs.value
	
	var resposta = confirm('VOCÊ TEM CERTEZA DE QUE O PREÇO DE COMPRA NAC. R$ '+preco_compra_nacional+' E A FORMA DE COMPRA '+forma_compra+' ESTÃO CORRETAS ?')
	if(resposta == false) {
		return false
	}else {
//Lote Mínimo P. Venda R$
		var lote_minimo_preco_venda_rs = calculo_lote_minimo_preco_venda_rs()
//Lote Mínimo Grupo R$
		var lote_minimo_grupo_rs = eval(strtofloat(document.form.txt_lote_minimo_grupo_rs.value))
		if(typeof(lote_minimo_grupo_rs) == 'undefined') {
			lote_minimo_grupo_rs = 0
		}
//Essas variáveis são para exibir no Alert ...
		var lote_minimo_preco_venda_rs_apresent = arred(String(calculo_lote_minimo_preco_venda_rs()), 2, 1)
		var lote_minimo_grupo_rs_apresent = document.form.txt_lote_minimo_grupo_rs.value
//Comparação entre o Lote Mínimo P. Venda R$ com o Lote Mínimo do Grupo em R$
		if(lote_minimo_preco_venda_rs < lote_minimo_grupo_rs) {
			var resposta2 = confirm('O LOTE MÍNIMO P/ VENDA DE R$ '+lote_minimo_preco_venda_rs_apresent+' É MENOR DO QUE O LOTE MÍNIMO GRUPO R$ '+lote_minimo_grupo_rs_apresent+' ! DESEJA CONTINUAR ???')
			if(resposta2 == false) return false
		}
		document.form.passo.value = 1
		document.form.nao_atualizar.value = 1
//Desabilito esse(s) campo(s) p/ poder gravar na Base de Dados ...
		document.form.txt_preco_forma_compra_nac_rs.disabled 		= false
		document.form.txt_preco_forma_compra_export_hispania_rs.disabled 	= false
		return limpeza_moeda('form', 'txt_preco_fat_compra_nac_rs, txt_prazo_pgto_ddl, txt_desc_vista, txt_desc_sgd, txt_icms_forn, txt_iva_forn, txt_preco_forma_compra_nac_rs, txt_preco_forma_compra_export_hispania_rs, ')
	}
}

function calcular_icms_custo_rs() {
	var fator_importacao					= '<?=$fator_importacao;?>'
	var valor_moeda_dolar_custo				= '<?=$valor_moeda_dolar_custo;?>'
	var valor_moeda_euro_custo				= '<?=$valor_moeda_euro_custo;?>'
	var icms_forn							= (document.form.txt_icms_forn.value != '') 							? eval(strtofloat(document.form.txt_icms_forn.value)) : 0
	var icms_c_red_cf_uf_forn				= (document.form.txt_icms_c_red_cf_uf_forn.value != '') 				? eval(strtofloat(document.form.txt_icms_c_red_cf_uf_forn.value)) : 0
	var preco_fat_compra_nac_rs				= (document.form.txt_preco_fat_compra_nac_rs.value != '') 				? eval(strtofloat(document.form.txt_preco_fat_compra_nac_rs.value)) : 0
	var preco_fat_compra_moeda_est			= (document.form.txt_preco_fat_compra_moeda_est.value != '') 			? eval(strtofloat(document.form.txt_preco_fat_compra_moeda_est.value)) : 0
	var preco_fat_compra_export_hispania_rs	= (document.form.txt_preco_fat_compra_export_hispania_rs.value != '') 	? eval(strtofloat(document.form.txt_preco_fat_compra_export_hispania_rs.value)) : 0

	document.form.txt_icms_nac_rs.value 	= preco_fat_compra_nac_rs * icms_forn / 100
	if(document.form.cmb_tipo_moeda.value == 1) {//Se a Moeda Estrangeira selecionada = 'Dólar' ...
		document.form.txt_icms_moeda_est.value 	= preco_fat_compra_moeda_est * fator_importacao * valor_moeda_dolar_custo * icms_c_red_cf_uf_forn / 100
	}else if(document.form.cmb_tipo_moeda.value == 2) {//Se a Moeda Estrangeira selecionada = 'Euro' ...
		document.form.txt_icms_moeda_est.value 	= preco_fat_compra_moeda_est * fator_importacao * valor_moeda_euro_custo * icms_c_red_cf_uf_forn / 100
	}
	document.form.txt_icms_export_hispania_rs.value = preco_fat_compra_export_hispania_rs * icms_forn / 100
	
	document.form.txt_icms_nac_rs.value 	= arred(document.form.txt_icms_nac_rs.value, 2, 1)
	document.form.txt_icms_moeda_est.value 	= arred(document.form.txt_icms_moeda_est.value, 2, 1)
	document.form.txt_icms_export_hispania_rs.value = arred(document.form.txt_icms_export_hispania_rs.value, 2, 1)
	
	calcular_icms_st_custo_rs()//Chamo a Função que Calcula o ICMS ST do Custo em R$ ...
}

function calcular_icms_st_custo_rs() {
	var iva_forn							= (document.form.txt_iva_forn.value != '') ? eval(strtofloat(document.form.txt_iva_forn.value)) : 0
	var icms_c_red_cf_uf_forn				= eval(strtofloat(document.form.txt_icms_c_red_cf_uf_forn.value))
	
	var preco_fat_compra_nac_rs				= (document.form.txt_preco_fat_compra_nac_rs.value != '') 				? eval(strtofloat(document.form.txt_preco_fat_compra_nac_rs.value)) : 0
	var preco_fat_compra_moeda_est			= (document.form.txt_preco_fat_compra_moeda_est.value != '') 			? eval(strtofloat(document.form.txt_preco_fat_compra_moeda_est.value)) : 0
	var preco_fat_compra_export_hispania_rs	= (document.form.txt_preco_fat_compra_export_hispania_rs.value != '') 	? eval(strtofloat(document.form.txt_preco_fat_compra_export_hispania_rs.value)) : 0
		
	var base_calculo_icms_st_nac_rs			= preco_fat_compra_nac_rs * (1 + iva_forn / 100)
	var icms_st_rs							= base_calculo_icms_st_nac_rs * icms_c_red_cf_uf_forn / 100 - preco_fat_compra_nac_rs * icms_c_red_cf_uf_forn / 100
	document.form.txt_icms_st_nac_rs.value 	= icms_st_rs
	document.form.txt_icms_st_nac_rs.value 	= arred(document.form.txt_icms_st_nac_rs.value, 2, 1)
	
	var base_calculo_icms_st_moeda_est			= 0//Essa é Zerada, porque não existe ST na Importação ...
	var icms_st_moeda_est						= 0//Essa é Zerada, porque não existe ST na Importação ...
	document.form.txt_icms_st_moeda_est.value 	= icms_st_moeda_est
	document.form.txt_icms_st_moeda_est.value 	= arred(document.form.txt_icms_st_moeda_est.value, 2, 1)
	
	var base_calculo_icms_st_export_hispania_rs			= preco_fat_compra_export_hispania_rs * (1 + iva_forn / 100)
	var icms_st_export_hispania_rs						= base_calculo_icms_st_export_hispania_rs * icms_c_red_cf_uf_forn / 100 - preco_fat_compra_export_hispania_rs * icms_c_red_cf_uf_forn / 100
	document.form.txt_icms_st_export_hispania_rs.value 	= icms_st_export_hispania_rs
	document.form.txt_icms_st_export_hispania_rs.value 	= arred(document.form.txt_icms_st_export_hispania_rs.value, 2, 1)
	
	calcular_preco_de_compra_para_custo()//Chamo a Função que Calcula o Preço de Compra p/ Custo R$ ...
}

function calcular_preco_de_compra_para_custo() {
	var fator_importacao		= '<?=$fator_importacao;?>'
	var valor_moeda_dolar_custo	= '<?=$valor_moeda_dolar_custo;?>'
	var valor_moeda_euro_custo	= '<?=$valor_moeda_euro_custo;?>'
	var id_tipo_moeda 			= (document.form.cmb_tipo_moeda.value) ? eval(strtofloat(document.form.cmb_tipo_moeda.value)) : 0
	
	var preco_fat_compra_nac_rs				= (document.form.txt_preco_fat_compra_nac_rs.value != '') 				? eval(strtofloat(document.form.txt_preco_fat_compra_nac_rs.value)) : 0
	var preco_fat_compra_moeda_est			= (document.form.txt_preco_fat_compra_moeda_est.value != '') 			? eval(strtofloat(document.form.txt_preco_fat_compra_moeda_est.value)) : 0
	var preco_fat_compra_export_hispania_rs	= (document.form.txt_preco_fat_compra_export_hispania_rs.value != '') 	? eval(strtofloat(document.form.txt_preco_fat_compra_export_hispania_rs.value)) : 0
	
	var custo_pa_indust						= eval(strtofloat(document.form.txt_custo_pa_indust.value))
	
	var icms_nac_rs 						= (document.form.txt_icms_nac_rs.value != '') 				? eval(strtofloat(document.form.txt_icms_nac_rs.value)) : 0
	var icms_moeda_est						= (document.form.txt_icms_moeda_est.value != '') 			? eval(strtofloat(document.form.txt_icms_moeda_est.value)) : 0
	var icms_export_hispania_rs 			= (document.form.txt_icms_export_hispania_rs.value != '') 	? eval(strtofloat(document.form.txt_icms_export_hispania_rs.value)) : 0
	
	var icms_st_nac_rs						= (document.form.txt_icms_st_nac_rs.value != '')				? eval(strtofloat(document.form.txt_icms_st_nac_rs.value)) : 0
	var icms_st_moeda_est					= (document.form.txt_icms_st_moeda_est.value != '')				? eval(strtofloat(document.form.txt_icms_st_moeda_est.value)) : 0
	var icms_st_export_hispania_rs 			= (document.form.txt_icms_st_export_hispania_rs.value != '') 	? eval(strtofloat(document.form.txt_icms_st_export_hispania_rs.value)) : 0
	
	if(preco_fat_compra_nac_rs != '') {
		document.form.txt_preco_de_compra_para_custo_nac_rs.value = preco_fat_compra_nac_rs + custo_pa_indust - icms_nac_rs + icms_st_nac_rs
		document.form.txt_preco_de_compra_para_custo_nac_rs.value = arred(document.form.txt_preco_de_compra_para_custo_nac_rs.value, 2, 1)
	}else {
		document.form.txt_preco_de_compra_para_custo_nac_rs.value = '0,00'
	}
	
	if(preco_fat_compra_moeda_est != '') {
		if(id_tipo_moeda == 1) {//Valor Dólar
			document.form.txt_preco_de_compra_para_custo_moeda_est.value = preco_fat_compra_moeda_est * fator_importacao * valor_moeda_dolar_custo + custo_pa_indust - icms_moeda_est + icms_st_moeda_est
		}else if(id_tipo_moeda == 2) {//Valor Euro
			document.form.txt_preco_de_compra_para_custo_moeda_est.value = preco_fat_compra_moeda_est * fator_importacao * valor_moeda_euro_custo + custo_pa_indust - icms_moeda_est + icms_st_moeda_est
		}
		document.form.txt_preco_de_compra_para_custo_moeda_est.value = arred(document.form.txt_preco_de_compra_para_custo_moeda_est.value, 2, 1)
	}else {
		document.form.txt_preco_de_compra_para_custo_moeda_est.value = '0,00'
	}
	
	if(preco_fat_compra_export_hispania_rs != '') {
		document.form.txt_preco_de_compra_para_custo_export_hispania_rs.value 	= preco_fat_compra_export_hispania_rs + custo_pa_indust - icms_export_hispania_rs + icms_st_export_hispania_rs
		document.form.txt_preco_de_compra_para_custo_export_hispania_rs.value 	= arred(document.form.txt_preco_de_compra_para_custo_export_hispania_rs.value, 2, 1)
	}else {
		document.form.txt_preco_de_compra_para_custo_export_hispania_rs.value 	= '0,00'
	}
}

function calcular_preco_pela_forma_compra() {
	var financeiro 		= '<?=$taxa_financeiro;?>'
	var desconto_snf 	= '<?=$desconto_snf;?>'
	var id_pais 		= '<?=$id_pais;?>'
	
	var preco_fat_compra_nac_rs				= (document.form.txt_preco_fat_compra_nac_rs.value != '') 				? eval(strtofloat(document.form.txt_preco_fat_compra_nac_rs.value)) : 0
	var preco_fat_compra_moeda_est			= (document.form.txt_preco_fat_compra_moeda_est.value != '') 			? eval(strtofloat(document.form.txt_preco_fat_compra_moeda_est.value)) : 0
	var preco_fat_compra_export_hispania_rs	= (document.form.txt_preco_fat_compra_export_hispania_rs.value != '') 	? eval(strtofloat(document.form.txt_preco_fat_compra_export_hispania_rs.value)) : 0
	
	var prazo_pgto_dias		= (document.form.txt_prazo_pgto_ddl.value != '') 	? eval(strtofloat(document.form.txt_prazo_pgto_ddl.value)) : 0
	var desconto_vista		= (document.form.txt_desc_vista.value != '') 		? eval(strtofloat(document.form.txt_desc_vista.value)) : 0
	var desconto_sgd		= (document.form.txt_desc_sgd.value != '') 			? eval(strtofloat(document.form.txt_desc_sgd.value)) : 0
	var icms_forn			= (document.form.txt_icms_forn.value != '') 		? eval(strtofloat(document.form.txt_icms_forn.value)) : 0
//Verifica se a combo forma de compra está selecionada
	if(document.form.cmb_forma_compra.value == '') {
		document.form.txt_preco_forma_compra_nac_rs.value 				= ''//Nacional ...
		document.form.txt_preco_forma_compra_moeda_est.value 			= ''//Estrangeiro ...
		document.form.txt_preco_forma_compra_export_hispania_rs.value 	= ''//Hispania ...
	}else {
		/**************************Preço Forma Compra Nacional R$ ***************************/
		var preco_av_nf 	= (preco_fat_compra_nac_rs * (100 - desconto_vista) / 100)
		var preco_fat_sgd 	= (preco_fat_compra_nac_rs * (100 - desconto_sgd) / 100)
		var preco_av_sgd 	= (preco_fat_sgd * (100 - desconto_vista) / 100)
			
		var resposta1 		= (Math.round((preco_av_nf * 100))) / 100
		var resposta2 		= (Math.round((preco_fat_sgd * 100))) / 100
		var resposta3 		= (Math.round((preco_av_sgd * 100))) / 100
		/**************************Preço Forma Compra Moeda Est ***************************/
		var preco_av_nf_est 	= (preco_fat_compra_moeda_est * (100 - desconto_vista) / 100)
		var preco_fat_sgd_est 	= (preco_fat_compra_moeda_est * (100 - desconto_sgd) / 100)
		var preco_av_sgd_est 	= (preco_fat_sgd_est * (100 - desconto_vista) / 100)
			
		var resposta1_est 		= (Math.round((preco_av_nf_est * 100))) / 100
		var resposta2_est 		= (Math.round((preco_fat_sgd_est * 100))) / 100
		var resposta3_est 		= (Math.round((preco_av_sgd_est * 100))) / 100
		/**************************Preço Forma Compra Export Hispania ***************************/
		var preco_av_nf_exp_hisp	= (preco_fat_compra_export_hispania_rs * (100 - desconto_vista) / 100)
		var preco_fat_sgd_exp_hisp	= (preco_fat_compra_export_hispania_rs * (100 - desconto_sgd) / 100)
		var preco_av_sgd_exp_hisp 	= (preco_fat_sgd_exp_hisp * (100 - desconto_vista) / 100)
			
		var resposta1_exp_hisp 		= (Math.round((preco_av_nf_exp_hisp * 100))) / 100
		var resposta2_exp_hisp 		= (Math.round((preco_fat_sgd_exp_hisp * 100))) / 100
		var resposta3_exp_hisp 		= (Math.round((preco_av_sgd_exp_hisp * 100))) / 100
		/***********************************************************************************/
		if(document.form.cmb_forma_compra.value == 1) {//FAT/NF ...
			document.form.txt_preco_forma_compra_nac_rs.value = document.form.txt_preco_fat_compra_nac_rs.value//Nacional ...
			document.form.txt_preco_forma_compra_moeda_est.value = document.form.txt_preco_fat_compra_moeda_est.value//Estrangeiro ...
			document.form.txt_preco_forma_compra_export_hispania_rs.value = document.form.txt_preco_fat_compra_export_hispania_rs.value//Hispania ...
		}
		if(document.form.cmb_forma_compra.value == 2) {//FAT/SGD ...
			document.form.txt_preco_forma_compra_nac_rs.value = resposta2//Nacional ...
			document.form.txt_preco_forma_compra_moeda_est.value = resposta2_est//Estrangeiro ...
			document.form.txt_preco_forma_compra_export_hispania_rs.value = resposta2_exp_hisp//Hispania ...
		}
		if(document.form.cmb_forma_compra.value == 3) {//AV/NF ...
			document.form.txt_preco_forma_compra_nac_rs.value = resposta1//Nacional ...
			document.form.txt_preco_forma_compra_moeda_est.value = resposta1_est//Estrangeiro ...
			document.form.txt_preco_forma_compra_export_hispania_rs.value = resposta1_exp_hisp//Hispania ...
		}
		if(document.form.cmb_forma_compra.value == 4) {//AV/SGD ...
			document.form.txt_preco_forma_compra_nac_rs.value = resposta3//Nacional ...
			document.form.txt_preco_forma_compra_moeda_est.value = resposta3_est//Estrangeiro ...
			document.form.txt_preco_forma_compra_export_hispania_rs.value = resposta3_exp_hisp//Hispania ...
		}
		document.form.txt_preco_forma_compra_nac_rs.value 		= arred(document.form.txt_preco_forma_compra_nac_rs.value, 2, 1)
		document.form.txt_preco_forma_compra_moeda_est.value 	= arred(document.form.txt_preco_forma_compra_moeda_est.value, 2, 1)
		document.form.txt_preco_forma_compra_export_hispania_rs.value = arred(document.form.txt_preco_forma_compra_export_hispania_rs.value, 2, 1)
	}
/*Lote Mínimo P. Venda R$
	var lote_minimo_preco_venda_rs = calculo_lote_minimo_preco_venda_rs()
//Lote Mínimo Grupo R$
	var lote_minimo_grupo_rs		= (document.form.txt_lote_minimo_grupo_rs.value != '') ? eval(strtofloat(document.form.txt_lote_minimo_grupo_rs.value)) : 0
//Comparação entre o Lote Mínimo P. Venda R$ com o Lote Mínimo do Grupo em R$
Se o Lote Mínimo P. Venda R$ for < Lote Mínimo Grupo R$, então printo a caixa em Vermelho, dizendo 
que a caixa de Lote Mínimo de Compra está em Irregular
	if(lote_minimo_preco_venda_rs < lote_minimo_grupo_rs) {
		document.form.txt_lote_minimo_compra.style.background = 'red'
Se o Lote Mínimo P. Venda R$ for < Lote Mínimo Grupo R$, então printo a caixa em Branca, dizendo 
que a caixa de Lote Mínimo de Compra está Normal
	}else {
		document.form.txt_lote_minimo_compra.className = 'caixadetexto'
	}*/
}

function atualizar_abaixo() {
	if(typeof(window.opener.document.form.resposta) == 'object') {
//Aqui é para não abrir o Pop-up do Custo
		window.opener.document.form.resposta.value = false
//Aqui é para não dar Update na tela de baixo e gravar o valor das caixas abaixo
		window.opener.document.form.ignorar_update.value = 1
		window.opener.document.form.submit()
	}
	window.close()
}

function copiar_condicao_padrao() {
	document.form.txt_prazo_pgto_ddl.value = document.form.txt_prazo_pgto_ddl2.value
	document.form.txt_desc_vista.value = document.form.txt_desc_vista2.value
	document.form.txt_desc_sgd.value = document.form.txt_desc_sgd2.value
	document.form.txt_ipi.value = document.form.txt_ipi2.value
	document.form.txt_icms_forn.value = document.form.txt_icms_forn2.value
	document.form.txt_iva_forn.value = document.form.txt_iva2_forn.value
	document.form.cmb_forma_compra.value = document.form.cmb_forma_compra2.value
//Aqui calcula novamente
	calcular_preco_pela_forma_compra()
}

function calcular_custo_revenda() {
	var taxa_financeira_vendas	= '<?=$taxa_financeira_vendas;?>'
	var fator_importacao		= '<?=$fator_importacao;?>'
	var valor_moeda_dolar_custo	= '<?=$valor_moeda_dolar_custo;?>'
	var valor_moeda_euro_custo	= '<?=$valor_moeda_euro_custo;?>'
	var outros_impostos_federais= eval('<?=genericas::variavel(34);?>')
	var id_pais					= '<?=$id_pais;?>'
	
	var preco_faturado 			= (document.form.txt_preco_fat_compra_nac_rs.value != '') ? eval(strtofloat(document.form.txt_preco_fat_compra_nac_rs.value)) : 0
	var prazo_pgto_dias 		= (document.form.txt_prazo_pgto_ddl.value != '') ? eval(strtofloat(document.form.txt_prazo_pgto_ddl.value)) : 0
	var custo_pa_indust 		= (document.form.txt_custo_pa_indust.value != '') ? eval(strtofloat(document.form.txt_custo_pa_indust.value)) : 0
	var preco_faturado_estrang 	= (document.form.txt_preco_fat_compra_moeda_est.value != '') ? eval(strtofloat(document.form.txt_preco_fat_compra_moeda_est.value)) : 0
	var icms 					= (document.form.txt_icms_forn.value != '') ? eval(strtofloat(document.form.txt_icms_forn.value)) : 0	
	//var icms_st_rs 			= (document.form.txt_icms_st_rs.value != '') ? eval(strtofloat(document.form.txt_icms_st_rs.value)) : 0
	var icms_st_rs				= 0
	var icms_com_red_venda		= (document.form.txt_icms_c_red_cf_uf_forn.value != '') ? eval(strtofloat(document.form.txt_icms_c_red_cf_uf_forn.value)) : 0
//Valor Moeda p/ Compra
	if(id_pais != 31) {//Se o País do Fornecedor for <> de Brasil
		//Igualo a 1 p/ facilitar pro Roberto, mas somente quando for País Estrangeiro, por causa do Dólar, Euro ...
		var valor_moeda_compra = (document.form.txt_valor_moeda_compra.value == '' || document.form.txt_valor_moeda_compra.value == '0,0000') ? 1 : eval(strtofloat(document.form.txt_valor_moeda_compra.value))
	}else {//Se o País for nacional, segue o procedimento normal ...
		var valor_moeda_compra = (document.form.txt_valor_moeda_compra.value != '') ? eval(strtofloat(document.form.txt_valor_moeda_compra.value)) : 0
	}
	var id_tipo_moeda 			= (document.form.cmb_tipo_moeda.value) ? eval(strtofloat(document.form.cmb_tipo_moeda.value)) : 0
	if(id_pais == 31) {//nacional multiplicar pelo moeda compra no caso q compra em moda estrangeira
		custo_de_compra 		= preco_faturado * (1 - icms / 100) + icms_st_rs
		custo_ml_min_sem_ind 	= custo_de_compra * (1 + taxa_financeira_vendas / 100) / (1 - (icms_com_red_venda + outros_impostos_federais) / 100)
	
		//fator_margem_lucro_pa * 
		//taxa_financeira_vendas * 
		if(id_tipo_moeda == 1) { //Dólar
			custo_ml_min = preco_faturado_estrang * taxa_financeira_vendas * valor_moeda_compra
		}else if(id_tipo_moeda == 2) { //Euro
			custo_ml_min = preco_faturado_estrang * taxa_financeira_vendas * valor_moeda_compra
		}else {
			custo_ml_min = 0
		}
	}else { //se for estrangeiro multiplicar pelo moeda custo
		custo_de_compra = 0
		if(id_tipo_moeda == 1) { //Valor Dólar
			custo_ml_min = preco_faturado_estrang * taxa_financeira_vendas * valor_moeda_dolar_custo * fator_importacao
		}else if(id_tipo_moeda == 2) { //Valor Euro
			custo_ml_min = preco_faturado_estrang * taxa_financeira_vendas * valor_moeda_euro_custo * fator_importacao
		}
	}
	document.form.txt_valor_custo_nac_min_rs.value = custo_ml_min_sem_ind + custo_pa_indust//Cálculo Final do Custo ...
	document.form.txt_valor_custo_nac_min_rs.value = arred(document.form.txt_valor_custo_nac_min_rs.value, 2, 1)
}

function calculo_lote_minimo_preco_venda_rs() {
	var taxa_financeira_vendas = '<?=$taxa_financeira_vendas;?>'
	var taxa_financeira_vendas = ((taxa_financeira_vendas / 100) + 1)
	var fator_desconto_maximo_vendas = '<?=$fator_desconto_maximo_vendas;?>'
	var fator_importacao = '<?=$fator_importacao;?>'
	var id_pais = '<?=$id_pais;?>'
/*Essas 3 variáveis aki abaixo, vai ser um caso bem + raro de eu utilizar, + já deixo carregado 
para facilitar a vida (hehehe) ...*/
	var tp_moeda = eval('<?=$tp_moeda;?>')
	var valor_moeda_custo_dolar = eval('<?=$valor_moeda_dolar_custo;?>')
	var valor_moeda_custo_euro = eval('<?=$valor_moeda_euro_custo;?>')
//Lote Mínimo de Compra
	var lote_minimo_compra = eval(strtofloat(document.form.txt_lote_minimo_compra.value))
	if(typeof(lote_minimo_compra) == 'undefined') lote_minimo_compra = 0
//Preço Faturado
	var preco_faturado = eval(strtofloat(document.form.txt_preco_fat_compra_nac_rs.value))
	if(typeof(preco_faturado) == 'undefined') preco_faturado = 0
//Custo P.A. Industrial
	var custo_pa_indust = eval(strtofloat(document.form.txt_custo_pa_indust.value))
	if(typeof(custo_pa_indust) == 'undefined') custo_pa_indust = 0
//Preço Fat. de Moeda Estrangeira
	var preco_fat_moeda_estrangeira = eval(strtofloat(document.form.txt_preco_fat_compra_moeda_est.value))
	var valor_custo_nac_min_rs 		= eval(strtofloat(document.form.txt_valor_custo_nac_min_rs.value))
	if(typeof(valor_custo_nac_min_rs) == 'undefined') valor_custo_nac_min_rs = 0
//Significa que não existe moeda estrangeira e daí, utilizo o Preço Faturado normal ...
	if(typeof(preco_fat_moeda_estrangeira) == 'undefined' || preco_fat_moeda_estrangeira == 0) preco_fat_moeda_estrangeira = 0
//Aqui tem algumas verificações para ver qual preço que eu vou utilizar ...
	if(preco_fat_moeda_estrangeira != 0 && preco_faturado == 0) {
		var calculo_internacional = 1//Vai me servir + abaixo
	}else if(preco_fat_moeda_estrangeira != 0 && preco_faturado != 0) {
		var calculo_internacional = 0//Vai me servir + abaixo
	}else if(preco_fat_moeda_estrangeira == 0 && preco_faturado != 0) {
		var calculo_internacional = 0//Vai me servir + abaixo
	}
//Vlr Moeda p/ Compra - Só vou utilizar essa variável quando o País = 'Brasil'
	var valor_moeda_compra = eval(strtofloat(document.form.txt_valor_moeda_compra.value))
	if(typeof(valor_moeda_compra) == 'undefined') valor_moeda_compra = 0
//Significa que a base de cálculo do Preço de Venda Máximo vai ser em cima do Preço Fat. Moeda Estrangeira ...
	if(calculo_internacional == 1) {
		if(tp_moeda == 1) {//Dólar ...
			if(id_pais == 31) {//Se for Brasil, utilizo o Valor Moeda Compra da Lista de Preço
				var preco_venda_maximo = (preco_fat_moeda_estrangeira * valor_moeda_compra) / fator_desconto_maximo_vendas
			}else {//Se for Estrangeiro, utilizo o Valor Moeda Compra do Custo
				var preco_venda_maximo = ((preco_fat_moeda_estrangeira * valor_moeda_custo_dolar * fator_importacao * taxa_financeira_vendas) + custo_pa_indust) / fator_desconto_maximo_vendas
			}
		}else if(tp_moeda == 2) {//Euro ...
			if(id_pais == 31) {//Se for Brasil, utilizo o Valor Moeda Compra da Lista de Preço
				var preco_venda_maximo = (preco_fat_moeda_estrangeira * valor_moeda_compra) / fator_desconto_maximo_vendas
			}else {//Se for Estrangeiro, utilizo o Valor Moeda Compra do Custo
				var preco_venda_maximo = ((preco_fat_moeda_estrangeira * valor_moeda_custo_euro * fator_importacao * taxa_financeira_vendas) + custo_pa_indust) / fator_desconto_maximo_vendas
			}
		}else {//Não tem moeda nenhuma ...
			var preco_venda_maximo = (preco_fat_moeda_estrangeira) / fator_desconto_maximo_vendas
		}
//Significa que a base de cálculo do Preço de Venda Máximo vai ser em cima do Preço Fat. Nac. R$ ...
//Ou seja vai seguir o critério normal
	}else {
		/*var preco_venda_maximo = ((preco_faturado) + custo_pa_indust) / fator_desconto_maximo_vendas
		preco_venda_maximo*= taxa_financeira_vendas*/
		
		
		var preco_venda_maximo = valor_custo_nac_min_rs / fator_desconto_maximo_vendas
	}
/*Comentado por causa que as fórmulas estão sendo redefinidas ...
//Já deixo embutida a Variável de Taxa Financeira de Vendas na Variável Preço de Venda Máximo ...
preco_venda_maximo*= taxa_financeira_vendas*/
	
	document.form.txt_preco_venda_maximo.value = preco_venda_maximo
	document.form.txt_preco_venda_maximo.value = arred(document.form.txt_preco_venda_maximo.value, 2, 1)

	var lote_minimo_preco_venda_rs = lote_minimo_compra * preco_venda_maximo
	document.form.txt_lote_minimo_preco_venda_rs.value = lote_minimo_preco_venda_rs
	document.form.txt_lote_minimo_preco_venda_rs.value = arred(document.form.txt_lote_minimo_preco_venda_rs.value, 2, 1)
	return lote_minimo_preco_venda_rs
}

function alterar_produto_acabado(id_produto_acabado) {
	nova_janela('../../cadastros/produto_acabado/alterar.php?passo=1&id_produto_acabado='+id_produto_acabado+'&pop_up=1', 'CONSULTAR', '', '', '', '', '450', '780', 'c', 'c', '', '', 's', 's', '', '', '')
}

function marcar_fornecedor_default() {
	var mensagem = confirm('DESEJA MARCAR ESTE FORNECEDOR COMO DEFAULT ?')
	if(mensagem == true) {
		document.form.passo.value = 1
		document.form.nao_atualizar.value = 1
		//Desabilito esse(s) campo(s) p/ poder gravar na Base de Dados ...
		document.form.txt_preco_forma_compra_nac_rs.disabled 		= false
		document.form.txt_preco_forma_compra_export_hispania_rs.disabled 	= false
		limpeza_moeda('form', 'txt_preco_fat_compra_nac_rs, txt_preco_forma_compra_moeda_est, txt_prazo_pgto_ddl, txt_desc_vista, txt_desc_sgd, txt_icms_forn, txt_preco_forma_compra_nac_rs, ')
		document.form.submit()
	}else {
		document.form.chkt_marcar_default.checked = false
	}
}
</Script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4' onload="calcular_preco_pela_forma_compra();calcular_icms_custo_rs();document.form.txt_lote_minimo_compra.focus()">
<form name="form" method="post" action="<?=$PHP_SELF;?>" onsubmit="return validar()">
<!--Aqui eu já guardo essas variável pq utilizo elas no Update-->
<input type='hidden' name="id_fornecedor" value="<?=$id_fornecedor;?>">
<input type='hidden' name="id_produto_acabado" value="<?=$id_produto_acabado;?>">
<!--Controle de Tela-->
<input type='hidden' name="id_fornecedor_prod_insumo" value="<?=$id_fornecedor_prod_insumo;?>">
<input type='hidden' name="nao_atualizar" value="0">
<input type='hidden' name="passo">
<!--********************************************************-->
<table width='920' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr align='center'>
		<td colspan='6'>
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class='linhacabecalho' align='center'>
		<td colspan='6'>
			<font color="#00FF00" size="2">
				<b>CUSTO REVENDA</b>
			</font>
			<?$checked = ($status_custo == 1) ? 'checked' : '';?>
			<input type="checkbox" name="chkt_custo_liberado" value="1" id="checar" title="Custo Liberado" onclick="confirmar_liberacao()" <?=$checked;?> class="checkbox">
			<label for="checar">
				<b>LIBERADO</b>
			</label>
		</td>
	</tr>
	<tr class='linhadestaque'>
		<td colspan='6'>
			<font color="yellow"><b>Produto:</b></font>
			<?=intermodular::pa_discriminacao($id_produto_acabado);?>
			&nbsp;&nbsp;<img src = "../../../../imagem/menu/alterar.png" border='0' title="Alterar Produto Acabado" alt="Alterar Produto Acabado" onClick="alterar_produto_acabado('<?=$id_produto_acabado;?>')">&nbsp;
			&nbsp;
			<a href="javascript:nova_janela('../../../vendas/relatorio/pedidos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$id_produto_acabado;?>&sumir_botao=1', 'VISUALIZAR_PEDIDOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Pedidos - Últimos 6 meses" class="link">
				<img src="../../../../imagem/visualizar_detalhes.png" title="Visualizar Pedidos - Últimos 6 meses" alt="Visualizar Pedidos - Últimos 6 meses" border="0">
			</a>
			&nbsp;
			<a href="javascript:nova_janela('../../../vendas/relatorio/orcamentos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$id_produto_acabado;?>&sumir_botao=1', 'VISUALIZAR_ORCAMENTOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Orçamentos - Últimos 6 meses" class="link">
				<img src="../../../../imagem/propriedades.png" title="Visualizar Orçamentos - Últimos 6 meses" alt="Visualizar Orçamentos - Últimos 6 meses" border="0">
			</a>
			&nbsp;
			<font color='black' size='-2' title='Data da Última Atualização' style='cursor:help'>
				<b>(<?=data::datetodata(substr($campos_lista[0]['data_sys'], 0, 10), '/');?>)</b>
			</font>
		</td>
	</tr>
	<tr class='linhadestaque'>
		<td colspan='6'>
			<font color="yellow"><b>Fornecedor:</b></font>
			<?
				echo $razaosocial;
				if($id_fornecedor == $id_fornecedor_setado) {
					echo '<b> <= DEFAULT</b>';
				}else {
			?>
				&nbsp;-&nbsp;
				<input type="checkbox" name="chkt_marcar_default" value="1" onclick="marcar_fornecedor_default()" id="marcar_default" class="checkbox">
				<label for="marcar_default">
					Marcar como Default
				</label>
			<?
				}
			?>
		</td>
	</tr>
	<tr class='linhacabecalho' align='center'>
		<td colspan='3'>
			Cálculo de Preço de Compra p/ Pedido
		</td>
		<td colspan='3'>
			Cálculo de Preço de Compra p/ Custo
		</td>
	</tr>
	<tr class='linhacabecalho' align='center'>
		<td>
			Nacional R$ 
		</td>
		<td>
			Moeda Estrangeira
		</td>
		<td>
			Exp. (HISPANIA) R$
		</td>
		<td class='linhanormal'>
			<b>Valor do U$ p/ Custo:</b> <?=number_format($valor_moeda_dolar_custo, 4, ',', '.');?>  -
		</td>
		<td class='linhanormal'>
			<b>Valor do &euro; p/ Custo:</b> <?=number_format($valor_moeda_euro_custo, 4, ',', '.');?>
		</td>
		<td class='linhanormal'>
			Fator Importação <?=number_format($fator_importacao, 2, ',', '.');?>
		</td>
	</tr>
	<tr class='linhacabecalho'>
		<td colspan='3' align='center'>
			Preço de Compra Faturado
		</td>
		<td class='linhanormal'>
			Custo P.A. Indust. R$
		</td>
		<td class='linhanormal' colspan='2'>
			<?$todas_etapas = custos::todas_etapas($id_produto_acabado, 1) * ($taxa_financeira_vendas / 100 + 1);?>
			<input type="text" name="txt_custo_pa_indust" value="<?=number_format($todas_etapas, 2, ',', '.');?>" title="Custo P.A. Indust" size="12" class="textdisabled" disabled>
		</td>
	</tr>
	<tr class='linhanormal' align='center'>
		<td>
			<input type="text" name="txt_preco_fat_compra_nac_rs" value="<?=$preco_compra_faturado_rs;?>" title="Digite o Preço Faturado Nacional R$" onKeyUp="verifica(this, 'moeda_especial', '2', '', event);calcular_preco_pela_forma_compra()" size="12" class="caixadetexto">
		</td>
		<td>
			<input type="text" name="txt_preco_fat_compra_moeda_est" value="<?=number_format($preco_fat_compra_moeda_est, 2, ',', '.');?>" size="12" class="textdisabled" disabled>
			&nbsp;
			<select name="cmb_tipo_moeda" class="textdisabled" disabled>
				<?
					if($tp_moeda == 1) {
						$selectedd = 'selected';
					}else if($tp_moeda == 2) {
						$selectede = 'selected';
					}
				?>
				<option value="" style="color:red">SELECIONE</option>
				<option value="1" <?=$selectedd;?>>DÓLAR - U$</option>
				<option value="2" <?=$selectede;?>>EURO - &euro;</option>
			</select>
		</td>
		<td>
			<input type="text" name="txt_preco_fat_compra_export_hispania_rs" value="<?=number_format($preco_fat_compra_export_hispania_rs, 2, ',', '.');?>" title="Digite o Preço Faturado Export Hispania R$" size="12" class="textdisabled" disabled>
		</td>
		<td class='linhacabecalho'>
			Nacional R$ 
		</td>
		<td class='linhacabecalho'>
			Moeda Estrangeira
		</td>
		<td class='linhacabecalho'>
			Exp. (HISPANIA) R$
		</td>
	</tr>
	<tr class='linhacabecalho'>
		<td colspan='3' align='center'>
			Preço de Compra Faturado
		</td>
		<td class='linhanormal' colspan='3' align='center'>
			ICMS R$
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>Lote Mínimo p/ Compra:</b>
		</td>
		<td>
			<input type="text" name="txt_lote_minimo_compra" value="<?=$lote_min_compra;?>" title="Digite o Lote Mínimo p/ Compra" onKeyUp="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''};calcular_preco_pela_forma_compra()" size="12" class="caixadetexto">
		</td>
		<td>
			<input type="button" name="cmd_condicao_padrao" value="Cond. Padr&atilde;o" title="Condi&ccedil;&otilde;es Padr&atilde;o" onclick="copiar_condicao_padrao()" style="color:green" class="botao">
		</td>
		<td align='center'>
			<input type="text" name="txt_icms_nac_rs" title="ICMS Nacional R$" size="12" class="textdisabled" disabled>
		</td>
		<td align='center'>
			<input type="text" name="txt_icms_moeda_est" title="ICMS Moeda Estrangeira R$" size="12" class="textdisabled" disabled>
		</td>
		<td align='center'>
			<input type="text" name="txt_icms_export_hispania_rs" title="ICMS Export Hispania R$" size="12" class="textdisabled" disabled>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>Prazo Pgto Dias:</b>
		</td>
		<td>
			<input type="text" name="txt_prazo_pgto_ddl" value="<?=$prazo_compra_ddl;?>" title="Digite o Prazo Pgto Dias" onKeyUp="verifica(this, 'moeda_especial', '1', '', event);calcular_preco_pela_forma_compra()" size="12" class="caixadetexto">
		</td>
		<td>
			<= 
			<input type="text" name="txt_prazo_pgto_ddl2" value="<?=$prazo_compra_ddl_padrao;?>" disabled size="12" class="textdisabled">
		</td>
		<td colspan='3' align='center'>
			<b>ICMS ST R$</b>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>Desc. A/V %:</b>
		</td>
		<td>
			<input type="text" name="txt_desc_vista" value="<?=$desc_compra_avista;?>" title="Digite o Desc. A/V %" onKeyUp="verifica(this, 'moeda_especial', '1', '', event);calcular_preco_pela_forma_compra()" size="12" class="caixadetexto">
		</td>
		<td>
			<= 
			<input type="text" name="txt_desc_vista2" value="<?=$desc_compra_avista_padrao;?>" disabled  size="12" class="textdisabled">
		</td>
		<td align='center'>
			<input type="text" name="txt_icms_st_nac_rs" value="<?=number_format($icms_st_rs_compra, 2, ',', '.');?>" title="ICMS ST Nacional R$" size="12" class="textdisabled" disabled>
		</td>
		<td align='center'>
			<input type="text" name="txt_icms_st_moeda_est" title="ICMS ST Moeda Estrangeira R$" size="12" class="textdisabled" disabled>
		</td>
		<td align='center'>
			<input type="text" name="txt_icms_st_export_hispania_rs" title="ICMS ST Export Hispania R$" size="12" class="textdisabled" disabled>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>Desc. SGD %:</b>
		</td>
		<td>
			<input type="text" name="txt_desc_sgd" value="<?=$desc_compra_sgd;?>" title="Digite o Desc. SGD %" onKeyUp="verifica(this, 'moeda_especial', '1', '', event);calcular_preco_pela_forma_compra()" size="12" class="caixadetexto">
		</td>
		<td>
			<= 
			<input type="text" name="txt_desc_sgd2" value="<?=$desc_compra_sgd_padrao;?>" size="12" class="textdisabled" disabled>
		</td>
		<td class='linhacabecalho' colspan='3' align='center'>
			Preço de Compra de Custo
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>IPI % Lista do Fornecedor:</b>
		</td>
		<td>
			<input type="text" name="txt_ipi" value="<?=$ipi_compra;?>" title="Digite o IPI %" onKeyUp="verifica(this, 'aceita', 'numeros', '', event)" size="12" class="caixadetexto">
		</td>
		<td>
			<= 
			<input type="text" name="txt_ipi2" value="<?=$ipi_compra_padrao;?>" size="12" class="textdisabled" disabled>
		</td>
		<td align='center'>
			<input type="text" name="txt_preco_de_compra_para_custo_nac_rs" size="12" class="textdisabled" disabled>
		</td>
		<td align='center'>
			<input type="text" name="txt_preco_de_compra_para_custo_moeda_est" size="12" class="textdisabled" disabled>
		</td>
		<td align='center'>
			<input type="text" name="txt_preco_de_compra_para_custo_export_hispania_rs" size="12" class="textdisabled" disabled>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>ICMS % Lista do Fornecedor:</b>
		</td>
		<td>
			<input type="text" name="txt_icms_forn" value="<?=$icms_c_red_compra;?>" title="Digite o ICMS %" onKeyUp="verifica(this, 'moeda_especial', '2', '', event);calcular_preco_pela_forma_compra()" size="12" class="caixadetexto">
		</td>
		<td>
			<= 
			<input type="text" name="txt_icms2_forn" value="<?=$icms_c_red_compra_padrao;?>" disabled size="12" class="textdisabled">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			IVA Lista do Fornecedor:
		</td>
		<td>
			<input type="text" name="txt_iva_forn" value="<?=$iva_compra;?>" title="Digite o IVA" onKeyUp="verifica(this, 'moeda_especial', '2', '', event);calcular_preco_pela_forma_compra()" size="12" class="caixadetexto">
		</td>
		<td>
			<= 
			<input type="text" name="txt_iva2_forn" value="<?=$iva_compra_padrao;?>" disabled size="12" class="textdisabled">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			ICMS c/ Red de CF x UF Forn %
		</td>
		<td colspan='2'>
			<?
				if($id_pais == 31) {//Quando Brasil, busco O ICMS c/ Red da Clas. vs UF do Fornecedor ...
					$dados_produto = intermodular::dados_impostos_pa($id_produto_acabado, $id_uf);
				}else {//Quando Estrangeiro, busco O ICMS c/ Red da Clas. vs UF SP, porque é Importação ...
					$dados_produto = intermodular::dados_impostos_pa($id_produto_acabado, 1);
				}
			?>
			<input type="text" name="txt_icms_c_red_cf_uf_forn" value="<?=number_format(($dados_produto['icms'] * (100 - $dados_produto['reducao']) / 100), 2, ',', '.');?>" title="ICMS c/ Red de Venda %" size="12" class="textdisabled" disabled>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td class='linhacabecalho' colspan='3' align='center'>
			Preço na Forma de Compra
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>Forma de Compra:</b>
		</td>
		<td>
			<select name='cmb_forma_compra' title="Selecione a Forma de Compra" onChange="calcular_preco_pela_forma_compra()" class='combo'>
			<?
				if($forma_compra == 1) {
					$selected1 = 'selected';
				}else if($forma_compra == 2) {
					$selected2 = 'selected';
				}else if($forma_compra == 3) {
					$selected3 = 'selected';
				}else if($forma_compra == 4) {
					$selected4 = 'selected';
				}
			?>
				<option value="" style="color:red">SELECIONE</option>
				<option value="1" <?=$selected1;?>>FAT/NF</option>
				<option value="2" <?=$selected2;?>>FAT/SGD</option>
				<option value="3" <?=$selected3;?>>AV/NF</option>
				<option value="4" <?=$selected4;?>>AV/SGD</option>
			</select>
		</td>
		<td>
			<?
				if($forma_compra_padrao == 1) {
					$texto = 'FAT/NF';
				}else if($forma_compra_padrao == 2) {
					$texto = 'FAT/SGD';
				}else if($forma_compra_padrao == 3) {
					$texto = 'AV/NF';
				}else if($forma_compra_padrao == 4) {
					$texto = 'AV/SGD';
				}
			?>
			<input type="text" name="txt_forma_compra" value="<?=$texto;?>" disabled size="12" class="textdisabled">
			<input type="hidden" name="cmb_forma_compra2" value="<?=$forma_compra_padrao;?>">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<input type="text" name="txt_preco_forma_compra_nac_rs" value="<?=$preco_compra_nac;?>" size="12" class="textdisabled" disabled>
		</td>
		<td>
			<?
				//Se o Fornecedor for do Brasil não existe valor em Moeda Estrangeira ...
				$preco_fat_compra_est_rs = ($id_pais == 31) ? 0 : $preco_fat_compra_moeda_est * $valor_moeda_compra;
			?>
			<input type="text" name="txt_preco_forma_compra_moeda_est" value="<?=number_format($preco_fat_compra_est_rs, 2, ',', '.');?>" title="Preço Faturado Estrangeiro R$" size="12" class="textdisabled" disabled>
		</td>
		<td>
			<input type="text" name="txt_preco_forma_compra_export_hispania_rs" value="<?=$preco_fat_compra_moeda_est;?>" size="12" class="textdisabled" disabled>
			<!--Essa caixa será utilizada para fazer os Cálculos-->
			<input type="hidden" name="txt_valor_moeda_compra" value="<?=number_format($valor_moeda_compra, 4, ',', '.');?>" size="12" class="textdisabled" disabled>
		</td>
	</tr>
	<tr class='linhacabecalho' align='center'>
		<td colspan='6'>
			Dados Adicionais
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>Observação do Produto:</b>
		</td>
		<td colspan='5'>
			<?=$campos_lista[0]['observacao'];?>
		</td>
	</tr>
	<tr class='linhanormal' align="left">
		<td>
			<b>Follow-Up do Produto Acabado <br>(Vendedores e Depto. Técnico): </b>
		</td>
		<td colspan='2'>
			<font face="Verdana, Geneva, Arial, Helvetica, sans-serif;" size="2">
				<a href="javascript:nova_janela('../../cadastros/produto_acabado/follow_up.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'CONSULTAR', '', '', '', '', '500', '780', 'c', 'c', '', '', 's', 's', '', '', '')" alt="Registrar Follow_up(s) do Produto" title="Registrar Follow_up(s) do Produto" class="link">
				<?
					$sql = "Select count(id_produto_acabado_follow_up) as total_follow_ups 
							from produtos_acabados_follow_ups 
							where id_produto_acabado = '$id_produto_acabado' ";
					$campos2 = bancos::sql($sql);
					$total_follow_ups = $campos2[0]['total_follow_ups'];
					if($total_follow_ups == 0) {
						echo 'NÃO HÁ FOLLOW-UP(S) REGISTRADO(S)';
					}else {
						echo '<font color="red"><marquee width="560">'.$total_follow_ups.' FOLLOW-UP(S) REGISTRADO(S)</marquee></font>';
					}
				?>
				</a>
			</font>
		</td>
		<td colspan='3' align='center'>
			<a href="javascript:nova_janela('../../../vendas/estoque_acabado/detalhes.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'COMPRA', '', '', '', '', '600', '850', 'c', 'c', '', '', 's', 's', '', '', '')" title="Detalhes" class="link">
				Detalhes do Produto
			</a>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="javascript:nova_janela('../../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'VISUALIZAR_ESTOQUE', '', '', '', '', 300, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Estoque" class="link">
				Visualizar Estoque
			</a>
		</td>
	</tr>
	<?
//Agora com o P.A. e a operação de custo, busco quem é o PAC
		$sql = "SELECT id_produto_acabado_custo 
				FROM `produtos_acabados_custos` 
				WHERE `id_produto_acabado` = '$id_produto_acabado' 
				AND `operacao_custo` = '$operacao_custo' LIMIT 1 ";
		$campos_prac = bancos::sql($sql);
/*Como não encontrou o produto acabado na tabela relacional de produtos_acabados_custos então ele primeiro 
vai gerar o PA para poder liberar o custo, isso caso o usuário desejar -> invenção do Roberto */
		if(count($campos_prac) == 0) {
			$data_sys = date('Y-m-d H:i:s');
			$sql = "INSERT INTO `produtos_acabados_custos` (`id_produto_acabado`, `qtde_lote`, `comprimento_2`, `operacao_custo`, `data_sys`) values ('$id_produto_acabado', '1', '6.0', '$operacao_custo', '$data_sys') ";
			bancos::sql($sql);
		}
	?>
	<tr class="linhacabecalho" align="center">
		<td colspan='6'>
			<input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" style="color:#ff9900;" onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_lote_minimo_compra.focus();calcular_preco_pela_forma_compra()" class="botao">
			<input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
			<input type="button" name="cmd_fechar_atualizar" value="Fechar e Atualizar" title="Fechar e Atualizar" onClick="atualizar_abaixo()" style="color:red" class="botao">
<!--Aqui eu também passo o id_pais, porque se o País for nacional "31", eu não posso ficar bloqueando 
e desbloqueando o Custo-->
			<input type="button" name="cmd_custo_industrial" value="Custo Industrial" title="Custo Industrial" style="color:black" onclick="nova_janela('../prod_acabado_componente/prod_acabado_componente2.php?id_produto_acabado=<?=$id_produto_acabado;?>&id_pais=<?=$id_pais;?>', 'DETALHES_CUSTO', '', '', '', '', '550', '950', 'c', 'c', '', '', 's', 's', '', '', '')" class="botao">
		</td>
	</tr>
	<tr>
		<td colspan='6'>
			<font face="Verdana, Geneva, Arial, Helvetica, sans-serif;" size="-1" color="blue">
				<b>* Após alterar o Prazo de Entrega clique no botão Salvar e Libere o Custo Posteriormente.</b>
			</font>
		</td>
	</tr>
	<?
//Aqui verifico a qual família que pertence esse PA
	$sql = "SELECT gp.id_familia 
			FROM `gpas_vs_emps_divs` ged 
			INNER JOIN `grupos_pas` gp ON gp.id_grupo_pa = ged.id_grupo_pa 
			WHERE ged.`id_gpa_vs_emp_div` = '".$campos_lista[0]['id_gpa_vs_emp_div']."' ";
	$campos2 = bancos::sql($sql);
//Se a família desse PA, for pertencente a família de Componentes, então não mostra link para esse PA
	if($campos2[0]['id_familia'] == 13) {
		$url = '../../../classes/producao/alterar_prazo_entrega_normal.php?';
	}else {
		if($campos_lista[0]['referencia'] == 'ESP') {//Se for Especial
			$url = '../../../classes/producao/alterar_prazo_entrega_esp.php?';
		}else {
			$url = '../../../classes/producao/alterar_prazo_entrega_normal.php?';
		}
	}

	if($campos_lista[0]['referencia'] == 'ESP') {//Se for Especial
?>
	<tr><td></td></tr>
	<tr class="iframe" onClick="showHide('alterar_prazo_entrega_tecnico'); return false" style="cursor:pointer;">
		<td colspan="8" height="22" align="left">
			Alterar Prazo de Entrega Sugerido pelo Depto. Técnico
			<span id="statusalterar_prazo_entrega_tecnico">&nbsp;</span>
			<span id="statusalterar_prazo_entrega_tecnico">&nbsp;</span>
		</td>
	</tr>
	<tr>
		<td colspan="8">
<!--Eu passo a origem por parâmetro também para não dar erro de URL na parte de detalhes da conta e de cheque-->
			<iframe src="<?=$url.'id_produto_acabado='.$id_produto_acabado;?>" name="alterar_prazo_entrega_tecnico" id="alterar_prazo_entrega_tecnico" marginwidth="0" marginheight="0" style="display: none;" frameborder="0" height="185" width="100%" scrolling="auto"></iframe>
		</td>
	</tr>
<?
	}
?>
</table>

<table width='920' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr>
		<td>
			Entulho
		</td>
	</tr>
	<tr>
		<td>
			Preço Fat. Min. R$ c/ ICMS+Imp.Fed.: (AJAXXXXXXX)
		</td>
		<td bgcolor='green'>
			<input type="text" name="txt_valor_custo_nac_min_rs" size="12" class="textdisabled" disabled>
		</td>
		<td>
			Preço Fat. Min. c/ ICMS+Imp.Fed.:
		</td>
		<td>
			<?
				if($tp_moeda == 1) {
					$moeda_estrangeiro = 'DÓLAR - U$ ';
				}else if($tp_moeda == 2) {
					$moeda_estrangeiro = 'EURO - &euro; ';
				}
			?>
			R$ <input type="text" name="txt_valor_custo_est_min_rs" size="12" class="textdisabled" disabled>
			<?=$moeda_estrangeiro;?> <input type="text" name="txt_valor_custo_est_min_est" size="12" class="textdisabled" disabled>
		</td>
		<td>
			Preço de Venda Máximo:
		</td>
		<td>
			R$ <input type="text" name="txt_preco_venda_maximo_rs" size="12" class="textdisabled" disabled>
			<?=$moeda_estrangeiro;?> <input type="text" name="txt_preco_venda_maximo_est" size="12" class="textdisabled" disabled>
		</td>
		<td>
			Preço de Venda Máximo:
		</td>
		<td>
			<input type="text" name="txt_preco_venda_maximo" size="12" class="textdisabled" disabled>
		</td>
		<td>
			Lote Mínimo do Grupo R$:
		</td>
		<td>
			<input type="text" name="txt_lote_minimo_grupo_rs" value="<?=number_format($lote_min_producao_reais, 2, ',', '.');?>" title="Lote Mínimo do Grupo R$" size="12" class="textdisabled" disabled>
		</td>
		<td>
			Lote Mínimo p/ Venda R$:
		</td>
		<td>
			<input type="text" name="txt_lote_minimo_preco_venda_rs" title="Lote Mínimo p/ Venda R$" size="12" class="textdisabled" disabled>
		</td>
	</tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color="red">Observação:</font></b>
<pre>
* No cálculo do P. Venda Máximo, não estamos levando em conta o Custo P.A. Industrial deste P.A. Revenda.
<font color="blue">
<b>Fator Desconto Máximo de Vendas -> </b><font color="black"><?=number_format($fator_desconto_maximo_vendas, 4, ',', '.');?></font>
</font>
</pre>
<?
//Controle que serve para essa função de JavaScript -> confirmar_liberacao(), mais abaixo
if($campos_lista[0]['referencia'] == 'ESP') {
/*Listagem de Todos os Orçamento(s) que estão em Aberto, q não estão congelados, que contém esse Item
em que o prazo de Entrega seja igual a Imediato*/
	$sql = "Select ovi.id_orcamento_venda_item 
			from orcamentos_vendas_itens ovi 
			inner join orcamentos_vendas ov on ov.id_orcamento_venda = ovi.id_orcamento_venda and ov.status < 2 and ov.congelar = 'N' 
			where ovi.id_produto_acabado = '$id_produto_acabado' 
			and ovi.prazo_entrega_tecnico = '0.0' limit 1 ";
	$campos2 = bancos::sql($sql);
//Se encontrar algum item que tenha o prazo de entrega técnico como zerado, então não pode liberar o custo
	if(count($campos2) == 1) {
		$custo_nao_pode_liberar = 1;
	}else {
		$custo_nao_pode_liberar = 0;
	}
}else {//Se for Industrial
	$custo_nao_pode_liberar = 0;
}
?>
<!--Joguei essa function aki em baixo, devido a variável $custo_nao_pode_liberar-->
<Script Language = 'JavaScript'>
function confirmar_liberacao() {
	if(document.form.chkt_custo_liberado.checked == true) {//Vai liberar o custo
/**********************************************************************************/
/*Na hora de liberar o custo, o Sistema verifica se o Depto. Técnico já deu o prazo para este Item
do Orçamento do PA atrelado, se isso ainda não aconteceu, não posso liberar o custo*/
		var custo_nao_pode_liberar = eval('<?=$custo_nao_pode_liberar;?>')
		if(custo_nao_pode_liberar == 1) {
			alert('ESSE CUSTO NÃO PODE SER LIBERADO, PREENCHA O PRAZO DE ENTREGA DO P.A. !')
			document.form.chkt_custo_liberado.checked = false
			showHide('alterar_prazo_entrega_tecnico')
			return false
		}
		var mensagem = confirm('DESEJA LIBERAR O CUSTO ?')
		if(mensagem == false) {
			document.form.chkt_custo_liberado.checked = false
			return false
		}else {
			document.form.passo.value = 1
			document.form.nao_atualizar.value = 1
//Desabilito esse(s) campo(s) p/ poder gravar na Base de Dados ...
			document.form.txt_preco_forma_compra_nac_rs.disabled = false
			limpeza_moeda('form', 'txt_preco_fat_compra_nac_rs, txt_prazo_pgto_ddl, txt_desc_vista, txt_desc_sgd, txt_icms_forn, txt_preco_forma_compra_nac_rs, ')
			document.form.submit()
		}
	}else {//Vai bloquear o custo
		var mensagem = confirm('DESEJA BLOQUEAR O CUSTO ?')
		if(mensagem == false) {
			document.form.chkt_custo_liberado.checked = true
			return false
		}else {
			document.form.passo.value = 1
			document.form.nao_atualizar.value = 1
//Desabilito esse(s) campo(s) p/ poder gravar na Base de Dados ...
			document.form.txt_preco_forma_compra_nac_rs.disabled = false
			limpeza_moeda('form', 'txt_preco_fat_compra_nac_rs, txt_prazo_pgto_ddl, txt_desc_vista, txt_desc_sgd, txt_icms_forn, txt_preco_forma_compra_nac_rs, ')
			document.form.submit()
		}
	}
}
/*Para não dar erro com as variáveis do PHP, enquanto está carregando a tela, 
coloquei essa função aqui em baixo*/
//calcular_custo_revenda()
</Script>