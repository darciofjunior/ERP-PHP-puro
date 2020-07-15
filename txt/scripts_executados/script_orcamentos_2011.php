<?
require('../../lib/segurancas.php');
require('../../lib/custos.php');
session_start('funcionarios');

function nova_comissao_representante($id_representante, $id_cliente, $desconto_cliente, $desconto_extra, $acrescimo_extra, $id_produto_acabado, $id_orcamento_venda, $preco_liq_final=0) {//Fun��o que calcula a comissao do representante
	$sql = "SELECT porc_comissao_fixa 
                FROM `representantes` 
                WHERE `id_representante` = '$id_representante' LIMIT 1 ";
	$campos_comissao_fixa = bancos::sql($sql);
	if($campos_comissao_fixa[0]['porc_comissao_fixa'] > 0) {//Verifica se o Rep possui Com. Fixa ...
		return $campos_comissao_fixa[0]['porc_comissao_fixa'];
	}else {
/**************************************************************************************/
/************************************Comiss�o Extra************************************/
/**************************************************************************************/
//Se existir or�amento ...
		if($id_orcamento_venda != '') {
			$comissao_extra = 0;//Valor Default ...
			/*Aqui eu pego a Comiss�o Extra do P.A. na Tabela de Divis�es Grupos PA(s) desde que: 
			A Data de Emiss�o do Or�amento seja <= a Data Limite da Tabela 'Estiver dentro do Prazo' ...*/
			$sql = "SELECT ged.comissao_extra, ged.data_limite 
                                FROM `orcamentos_vendas` ov 
                                INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_orcamento_venda = ov.id_orcamento_venda 
                                INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado AND pa.id_produto_acabado = '$id_produto_acabado' 
                                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                                INNER JOIN `estoques_acabados` ea ON ea.id_produto_acabado = pa.id_produto_acabado 
                                WHERE ov.id_orcamento_venda = '$id_orcamento_venda' 
                                AND (ov.data_emissao <= ged.data_limite) LIMIT 1 ";
			$campos_comissao_extra = bancos::sql($sql);
			//Se estiver tudo ok ent�o ...
			if(count($campos_comissao_extra) == 1) $comissao_extra = $campos_comissao_extra[0]['comissao_extra'];
			//Atualizo a Comiss�o Extra em todos os Itens da Tabela de Itens de Or�amento ...
			$sql = "UPDATE `orcamentos_vendas_itens` set comissao_extra = '$comissao_extra' where `id_orcamento_venda` = '$id_orcamento_venda' and id_produto_acabado = '$id_produto_acabado' limit 1 ";
			bancos::sql($sql);
		}
/**************************************************************************************/
/************************************Comiss�o Nova*************************************/
/**************************************************************************************/
		//Pega a UF do cliente ...
		$sql = "SELECT id_uf 
                        FROM `clientes` 
                        WHERE `id_cliente` = '".$id_cliente."' LIMIT 1 ";
		$campos_temp = bancos::sql($sql);
		if(count($campos_temp) > 0) $id_uf_cliente = $campos_temp[0]['id_uf'];
		if(empty($preco_liq_final)) {
			//Pega o Valor Liq Faturado ...
			$sql = "SELECT preco_liq_final 
                                FROM `orcamentos_vendas_itens` 
                                WHERE `id_orcamento_venda` = '$id_orcamento_venda' 
                                AND `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
			$campos_temp = bancos::sql($sql);
			if(count($campos_temp) > 0) $preco_liq_final = $campos_temp[0]['preco_liq_final'];
		}
		$tx_financeira 	= custos::calculo_taxa_financeira($id_orcamento_venda);
		$tx_financeira 	= round($tx_financeira, 2);
		$margem_lucro 	= custos::margem_lucro($id_produto_acabado, $tx_financeira, $id_uf_cliente, $preco_liq_final, $id_orcamento_venda);
		$margem_lucro 	= $margem_lucro[0];// pego o valor da margem de lucro deste produto ...

		$sql = "SELECT base_comissao_supervisor, base_comissao_vendedor_dentro_sp, comissao_fora_estado, comissao_interior, comissao_autonomo 
                        FROM `novas_comissoes_margens_lucros` 
                        WHERE `margem_lucro` <= '$margem_lucro' ORDER BY margem_lucro DESC LIMIT 1 ";
		$campos_margem = bancos::sql($sql);			
		if(count($campos_margem) <= 0) { //se eu nao achar eu pego o ultimo desta tabela	
			$sql = "SELECT base_comissao_supervisor, base_comissao_vendedor_dentro_sp, comissao_fora_estado, comissao_interior, comissao_autonomo 
                                FROM `novas_comissoes_margens_lucros` 
                                ORDER BY margem_lucro LIMIT 1 ";
			$campos_margem = bancos::sql($sql);
			if(count($campos_margem) <= 0) return 0;//N�o encontrei a margem de comissao ...
		}
		/**************************Exce��es**************************/
		if($id_representante == 14) {//Caso for a Mercedes ter� o caminho da comissao Interna, mesmo pq ela � supervisora.
			return ($campos_margem[0]['base_comissao_supervisor'] + $comissao_extra) + 1;
		}else if($id_representante == 71) {//Se for PME ...
			return ($campos_margem[0]['base_comissao_supervisor'] + $comissao_extra);
		}
		/************************************************************/
		//Seleciono nesta tabela para ve se ele � funcionario ou nao ...
		$sql = "SELECT f.id_cargo 
                        FROM `representantes_vs_funcionarios` rf 
                        INNER JOIN `funcionarios` f ON f.id_funcionario = rf.id_funcionario 
                        WHERE rf.id_representante = '$id_representante' LIMIT 1 ";
		$campos = bancos::sql($sql);
		if(count($campos) > 0) {//se achou � pq ele � funcionario
			if($campos[0]['id_cargo'] == 25 || $campos[0]['id_cargo'] == 27) {//representante externo id_cargo=>27 ou id_cargo=>25 => supervisor � para tratar como vend. externo nova l�gica
				//VERIFICO SE O CLIENTE � PERTINENTE A CIDADES DE COMISSAO INTERNAS
				$sql = "SELECT cidade, base_pag_comissao, id_uf 
                                        FROM `clientes` 
                                        WHERE id_cliente = '$id_cliente' LIMIT 1 ";
				$campos = bancos::sql($sql);
				//Verifico se a cidade do cliente consta na tabela de alguma cidades determinadas
				if(count($campos) > 0) {
					$cidade = addslashes($campos[0]['cidade']);
					$id_uf	= $campos[0]['id_uf'];
					$base_pag_comissao = $campos[0]['base_pag_comissao'];
					//PASSO PARA SP PARA O vendedor GANHAR COMISS�O DE SP MESMO FORA DO ESTADO (CASO FRATO)
					if($base_pag_comissao == 1) $id_uf = 1;
				}else {
					return 0;
				}
				if($id_uf == 1) {//Estado = 'SP' ...
					$sql = "SELECT id_comissao_cidade 
                                                FROM `comissoes_cidades` 
                                                WHERE `comissao_cidade` = '$cidade' LIMIT 1 ";
					$campos = bancos::sql($sql);
					if(count($campos) > 0) {//O cliente consta nas cidades q s�o consideradas perto da empresa ent�o ...
						return ($campos_margem[0]['base_comissao_vendedor_dentro_sp'] + $comissao_extra);
					}else {//O cliente deve ser de longe onde o vendedor tera gasto extra ...
						if($base_pag_comissao == 1) {//Mesmo o cliente sendo de fora a comiss�o ser� paga como se fosse de S�o Paulo, marca��o do Cadastro do Cliente ...
							return ($campos_margem[0]['base_comissao_vendedor_dentro_sp'] + $comissao_extra);
						}else {//O cliente � de fora, recebe como se fosse de fora mesmo ...
							return ($campos_margem[0]['comissao_interior'] + $comissao_extra);
						}
					}
				}else {//UF <> 'SP' outros estados, serve para obrigar o vendedor a passar os representantes para o autonomo
					$comi_fora_est = ($campos_margem[0]['comissao_fora_estado'] + $comissao_extra);
					if($comi_fora_est == 0) {
						//Para esses casos, n�s estamos pagando 0,5% de Comiss�o ...
						return '0.5';
					}else {
						return $comi_fora_est;
					}
				}
			}else {// representante interno id_cargo=>47 ou supervisor interno 109
				return ($campos_margem[0]['base_comissao_supervisor'] + $comissao_extra);
			}
		}else {//Como ele � autonomo automaticamente ele pega a comissao externo especial ...
			return ($campos_margem[0]['comissao_autonomo'] + $comissao_extra);
		}
	}
}

if(empty($indice)) $indice = 0;

//Todos os Pedidos de Vendas ...
$sql = "SELECT count(ovi.id_orcamento_venda_item) AS total_registro 
        FROM `orcamentos_vendas` ov 
        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_orcamento_venda = ov.id_orcamento_venda 
        WHERE ov.`data_emissao` >= '2012-03-01' 
        AND ov.`congelar` = 'S' ORDER BY ov.data_emissao DESC ";
$campos_total = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'];
echo '<br>';

//P/ n�o ficar em loop infinito ...
if($total_registro == $indice) exit;

$sql = "SELECT ged.id_empresa_divisao, ov.id_orcamento_venda, ov.id_cliente, ovi.id_orcamento_venda_item, ovi.id_produto_acabado, ovi.id_representante, ovi.desc_extra, ovi.acrescimo_extra, ovi.preco_liq_final, ovi.margem_lucro, pa.referencia 
        FROM `orcamentos_vendas` ov 
        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_orcamento_venda = ov.id_orcamento_venda 
        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
        WHERE ov.`data_emissao` >= '2012-03-01' 
        AND ov.`congelar` = 'S' ORDER BY ov.data_emissao DESC ";
$campos = bancos::sql($sql, $indice, 1);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
	//Aqui pego o representante e o desconto do cliente para o calculo ...
	$sql = "SELECT desconto_cliente 
                FROM `clientes_vs_representantes` 
                WHERE `id_cliente` = '".$campos[$i]['id_cliente']."' 
                AND `id_empresa_divisao` = '".$campos[$i]['id_empresa_divisao']."' LIMIT 1 ";
	$campos_desconto = bancos::sql($sql);
	if(count($campos_desconto) > 0) $desconto_cliente = (strtoupper($campos[$i]['referencia']) == 'ESP') ? 0 : $campos_desconto[0]['desconto_cliente'];
	$comissao_new = nova_comissao_representante($campos[$i]['id_representante'], $campos[$i]['id_cliente'], $desconto_cliente, $campos[$i]['desc_extra'], $campos[$i]['acrescimo_extra'], $campos[$i]['id_produto_acabado'], $campos[$i]['id_orcamento_venda'], $campos[$i]['preco_liq_final']);
	/*****************************************************************************************/
	/************************************Queima de Estoque************************************/
	/*****************************************************************************************/
	/*Se existir algum Item de Or�amento que est� em Queima de Estoque, ent�o eu sobreponho 
	a Comiss�o Extra da Divis�o do Grupo com a Comiss�o Extra de Queima  ... */
	$sql = "SELECT queima_estoque, comissao_extra 
                FROM `orcamentos_vendas_itens` 
                WHERE `id_orcamento_venda_item` = '".$campos[$i]['id_orcamento_venda_item']."' LIMIT 1 ";
	$campos_itens_queima 	= bancos::sql($sql);
	if($campos_itens_queima[0]['queima_estoque'] == 'S') {//Queima de Estoque reflete na Comiss�o Extra ...
            //Desconto da Comiss�o Nova a Comissao Extra do Grupo, pois a que ir� prevalecer ser� a da Queima de Estoque ...
            $comissao_new-= $campos_itens_queima[0]['comissao_extra'];
            $comissao_extra = genericas::variavel(46);//Essa vari�vel reflete + abaixo ...
            //Atualizo a Comiss�o Extra da Queima de Estoque sobre os Itens ...
            $sql = "UPDATE `orcamentos_vendas_itens` SET `comissao_extra` = '$comissao_extra' WHERE `id_orcamento_venda_item` = '".$campos[$i]['id_orcamento_venda_item']."' LIMIT 1 ";
            bancos::sql($sql);
	}else {//N�o � Queima de Estoque, ent�o n�o existe Comiss�o Extra ...
            $comissao_extra = 0;//Essa vari�vel reflete + abaixo ...
	}
	/*****************************************************************************************/
	$sql = "UPDATE `orcamentos_vendas_itens` SET `comissao_new` = '".($comissao_new + $comissao_extra)."' WHERE `id_orcamento_venda_item` = '".$campos[$i]['id_orcamento_venda_item']."' LIMIT 1 ";
	bancos::sql($sql);
	echo $sql.'<br>';
}
//3297-1920 ou 2203-2026 ...
?>
<Script Language = 'JavaScript'>
//Aqui eu j� passo o �ndice do pr�ximo ...
	window.location = 'script_orcamentos_2011.php?indice=<?=++$indice;?>'
</Script>