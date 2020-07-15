<?
///=========>>>>>>>>>  Procurar por $taxa_financeira_vendas para verificar a / 3  por exportacao 
if(!class_exists('bancos')) 	require 'bancos.php';//CASO EXISTA EU DESVIO A CLASSE ...
if(!class_exists('genericas')) 		require('genericas.php');//CASO EXISTA EU DESVIO A CLASSE ...
if(!class_exists('calculos')) 		require 'calculos.php';//CASO EXISTA EU DESVIO A CLASSE ...
if(!class_exists('comunicacao')) 	require 'comunicacao.php';//CASO EXISTA EU DESVIO A CLASSE ...
require 'variaveis/intermodular.php';
class custos {
	/*************************************************************************************************************************************/
	/********************************************************CUSTO INDUSTRIAL*************************************************************/
	/*************************************************************************************************************************************/
	function etapa1($id_pa_componente, $fator_custo_etapa_1_3_7=1) {//etapa que calcula o custo da embalagem, com base na lista de preco do ultimo pedido
		$sql = "SELECT ppe.id_produto_insumo 
				FROM `pas_vs_pis_embs` ppe 
				INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ppe.id_produto_acabado 
				WHERE ppe.`id_produto_acabado` = '$id_pa_componente' ";
		$campos = bancos::sql($sql);
		$linhas = count($campos);
		for($i = 0; $i < $linhas; $i++) {
			$dados_embalagem 	 	= custos::custo_embalagem($campos[$i]['id_produto_insumo'], $id_pa_componente);
			$dados_pi 				= custos::preco_custo_pi($campos[$i]['id_produto_insumo']);
			$icms 					= $dados_pi['icms'];	
			//$etapa1_com_impostos+= $dados_embalagem[0] * $fator_custo_etapa_1_3_7;
			$etapa1_sem_impostos_sem_fator+= $dados_embalagem[0] * (100 - $icms) / 100;
		}
		$etapa1_com_impostos = 0;//Não utilizamos mais ...
		return array($etapa1_com_impostos, $etapa1_sem_impostos_sem_fator);
	}

	function etapa2($id_produto_insumo, $peso_kg, $peca_corte=1, $comprimento_1=0, $comprimento_2=0, $fator_custo_etapa_2) {
		global $peso_aco_kg; // uso esta variavel em outras etapas
		$sql = "SELECT densidade_aco 
				FROM `produtos_insumos_vs_acos` 
				WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
		$campos = bancos::sql($sql);
		//Nao achou o custo deste Produto ...
		$densidade 			= (count($campos) == 0) ? 0 : $campos[0]['densidade_aco'];
		$comprimento_total 	= ($comprimento_1 + $comprimento_2) / 1000;
		$dados_pi 			= custos::preco_custo_pi($id_produto_insumo);
		$preco_pi			= $dados_pi['preco_comum'];
		$icms 				= $dados_pi['icms'];
		if($peca_corte == 0) $peca_corte = 1;		
		$peso_aco_kg 					= $densidade * $comprimento_total * 1.05 / $peca_corte;//O 1.05 é um fator por conta de perda
		//$etapa2_com_impostos 			= ($peso_aco_kg * $preco_pi) * $fator_custo_etapa_2;
		$etapa2_sem_impostos_sem_fator 	= ($peso_aco_kg * $preco_pi) * (100 - $icms) / 100;
		$etapa2_com_impostos = 0;//Não utilizamos mais ...
		return array($etapa2_com_impostos, $etapa2_sem_impostos_sem_fator);
	}

	function etapa3($id_pa_componente, $fator_custo_etapa_1_3_7, $operacao_custo) { // etapa q atrela as demais materia prima e outros Produto Insumo
		$sql = "SELECT DISTINCT(pp.id_produto_insumo), pp.qtde 
				FROM `produtos_acabados_custos` pac 
				INNER JOIN `pacs_vs_pis` pp ON pp.id_produto_acabado_custo = pac.id_produto_acabado_custo 
				WHERE pac.`operacao_custo` = '$operacao_custo' 
				AND pac.`id_produto_acabado` = '$id_pa_componente' ";
		$campos = bancos::sql($sql);
		$linhas = count($campos);
		if(count($campos) > 0) {//Se achou Custo deste produto ...
			for($i = 0; $i < $linhas; $i++) {
				$dados_pi 				= custos::preco_custo_pi($campos[$i]['id_produto_insumo']);
				$preco_pi				= $dados_pi['preco_comum'];
				$icms 					= $dados_pi['icms'];
				
				//$etapa3_com_impostos+= 			 ($preco_pi * $campos[$i]['qtde']) * $fator_custo_etapa_1_3_7;
		 		$etapa3_sem_impostos_sem_fator+= ($preco_pi * $campos[$i]['qtde']) * (100 - $icms) / 100;
			}
			$etapa3_com_impostos = 0;//Não utilizamos mais ...
			return array($etapa3_com_impostos, $etapa3_sem_impostos_sem_fator);
		}else {
			return array(0, 0);
		}
	}

	function etapa4($id_pa_componente, $fator_custo_etapa_4, $operacao_custo) {
		$sql = "SELECT (SUM(pm.tempo_hs * m.custo_h_maquina)) / pac.qtde_lote AS custo_maquina 
				FROM `produtos_acabados_custos` pac 
				INNER JOIN `pacs_vs_maquinas` pm ON pm.id_produto_acabado_custo = pac.id_produto_acabado_custo 
				INNER JOIN `maquinas` m ON m.id_maquina = pm.id_maquina 
				WHERE pac.`operacao_custo` = '$operacao_custo' 
				AND pac.`id_produto_acabado` = '$id_pa_componente' ";
		$campos = bancos::sql($sql);
		$linhas = count($campos);
		if(count($campos) > 0) {
			if(empty($campos[0]['custo_maquina']) || is_null($campos[0]['custo_maquina'])) {
				return 0;
			}else {
				return $campos[0]['custo_maquina'];
			}
		}else {
			return 0;
		}
	}

	function etapa5($id_pa_componente, $peso_aco_kg, $fator_custo_etapa_5_6, $operacao_custo, $qtde_orcamento = 0) {//Cálcula o custo do tratamento termico ...
		$sql = "SELECT ppt.fator AS fator_tt, ppt.id_produto_insumo, ppt.peso_aco_manual, ppt.lote_minimo_fornecedor, ppt.peso_aco, pac.qtde_lote 
				FROM `produtos_acabados_custos` pac 
				INNER JOIN `pacs_vs_pis_trat` ppt ON ppt.id_produto_acabado_custo = pac.id_produto_acabado_custo 
				WHERE pac.`operacao_custo` = '$operacao_custo' 
				AND pac.`id_produto_acabado` = '$id_pa_componente' ";
		$campos = bancos::sql($sql);
		$linhas = count($campos);
		if($linhas > 0) {
			for($i = 0; $i < $linhas; $i++) {
				/**************Dados de Custo do PI**************/
				$dados_pi 			= custos::preco_custo_pi($campos[$i]['id_produto_insumo']);
				$preco_pi			= $dados_pi['preco_comum'];
				$icms 				= $dados_pi['icms'];
				/************************************************/			
				if($campos[$i]['peso_aco_manual'] == 1) {//Marcado no Checkbox que estamos usando o Peso Real da Peça ...
					$peso_aco_kg_temp 		= round($campos[$i]['peso_aco'], 3);
					$custo_normal_etapa5	= ($peso_aco_kg_temp * $preco_pi);//O ICMS é abatido + abaixo ...
				}else {//Desmarcado no Checkbox, ou seja é um peso Teórico da Peça baseado na Qtde de Aço utilizado * Fator TT ...
					$peso_aco_kg_temp 		= round($peso_aco_kg / 1.05, 3);
					$custo_normal_etapa5	= ($peso_aco_kg_temp * $preco_pi * $campos[$i]['fator_tt']);//O ICMS é abatido + abaixo ...
				}
			
				if($campos[$i]['lote_minimo_fornecedor'] == 1) {//Marcado no Checkbox que acionamos o calculo por Lote Mínimo do Fornecedor ...
					$id_fornecedor_default = custos::preco_custo_pi($campos[0]['id_produto_insumo'], 0, 1);
					//Aqui eu pego o Lote Mínimo do Fornecedor Default encontrado através do PI na lista de Preço de Compras ...
                                	$sql = "SELECT f.razaosocial, fpi.lote_minimo_reais 
                                        	FROM `fornecedores` f 
                                        	INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.id_fornecedor = f.id_fornecedor AND fpi.`id_produto_insumo` = '".$campos[0]['id_produto_insumo']."' 
                                        	WHERE f.`id_fornecedor` = '$id_fornecedor_default' LIMIT 1 ";
					$campos_fornec = bancos::sql($sql);
					$lote_minimo_reais = $campos_fornec[0]['lote_minimo_reais']; //lote minimo do fornecedor default
					//Se qtde_orcamento 		= 0, usamos a Qtde do Lote do Custo senão a Qtde do Lote do Orçamento ...
					$lote_custo 			= ($qtde_orcamento == 0) ? $campos[$i]['qtde_lote'] : $qtde_orcamento;
					$custo_lote_minimo_etapa5 	= $lote_minimo_reais / $lote_custo;
					if($custo_normal_etapa5 < $custo_lote_minimo_etapa5) $custo_normal_etapa5 = $custo_lote_minimo_etapa5;
				}
				//$etapa5_com_impostos+= $custo_normal_etapa5 * $fator_custo_etapa_5_6;
		 		$etapa5_sem_impostos_sem_fator+= $custo_normal_etapa5 * (100 - $icms) / 100;
			}
			$etapa5_com_impostos = 0;//Não utilizamos mais ...
			return array($etapa5_com_impostos, $etapa5_sem_impostos_sem_fator);
		}else {
			return array(0, 0);
		}
	}

	function etapa6($id_pa_componente, $fator_custo_etapa_5_6, $operacao_custo) {//Calcula o Custo da Usinagem ...
		$sql = "SELECT ppu.qtde, ppu.id_produto_insumo 
				FROM `produtos_acabados_custos` pac 
				INNER JOIN `pacs_vs_pis_usis` ppu ON ppu.id_produto_acabado_custo = pac.id_produto_acabado_custo 
				WHERE pac.`operacao_custo` = '$operacao_custo' 
				AND pac.`id_produto_acabado` = '$id_pa_componente' ";
		$campos = bancos::sql($sql);
		$linhas = count($campos);
		if(count($campos) > 0) {
			for($i = 0; $i < $linhas; $i++) {
				$dados_pi 				= custos::preco_custo_pi($campos[$i]['id_produto_insumo']);
				$preco_pi				= $dados_pi['preco_comum'];
				$icms 					= $dados_pi['icms'];
				
				//$etapa6_com_impostos+= 				($preco_pi * $campos[$i]['qtde']) * $fator_custo_etapa_5_6;
		 		$etapa6_sem_impostos_sem_fator+= 	($preco_pi * $campos[$i]['qtde']) * (100 - $icms) / 100;
			}
			$etapa6_com_impostos = 0;//Não utilizamos mais ...
			return array($etapa6_com_impostos, $etapa6_sem_impostos_sem_fator);
		}else {
			return array(0, 0);
		}
	}
	
	function etapa7($id_produto_acabado_custo, $fator_custo_etapa_1_3_7=1, $qtde_orcamento) { // calcula o custo do PA/Componente pode ser industrializado
		$sql = "SELECT pp.qtde, pp.id_produto_acabado, pa.operacao_custo, pa.discriminacao 
				FROM `pacs_vs_pas` pp 
				INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pp.id_produto_acabado 
				WHERE pp.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ";
		$campos = bancos::sql($sql);//Tipo Indutrialização ...
		$linhas = count($campos);
		if(count($campos) > 0) {
			for($i = 0; $i < $linhas; $i++) {
				if(!class_exists('intermodular')) {require 'intermodular.php';}//CASO EXISTA EU DESVIO A CLASSE ...
				if($campos[$i]['operacao_custo'] == 0) {//PA Industrial ... 
					//Essa parte da fórmula é igual independente - passo 0 pq o oc é do tipo industrializado ...
					//$etapa7_com_impostos+= 				($campos[$i]['qtde'] * (custos::todas_etapas($campos[$i]['id_produto_acabado'], 0, 0, $qtde_orcamento)));
					$etapa7_sem_impostos_sem_fator+= 	($campos[$i]['qtde'] * (custos::todas_etapas($campos[$i]['id_produto_acabado'], 0, 0, $qtde_orcamento)));				
				}else {//PA Revenda ...
					$valor_revenda 			= custos::pipa_revenda($campos[$i]['id_produto_acabado'], 0);//0 é para nao somar embalagem
					$taxa_financeira_vendas         = genericas::variaveis('taxa_financeira_vendas') / 100 + 1;
					$valor_revenda/= $taxa_financeira_vendas;
					$fator_margem_lucro 	= genericas::variavel(22);//Margem de Lucro PA Industrial
					//macete para n~ da err division by zero ...
					if($GLOBALS['fator_margem_lucro_pa'] == 0) $GLOBALS['fator_margem_lucro_pa'] = 0.01;
					$valor_revenda 			= ($valor_revenda / $GLOBALS['fator_margem_lucro_pa']) * $fator_margem_lucro; //este calculo é pq tem um PA(Rev) atrelado na 7ª etapa de um PA(Ind)
					
					$etapa7_com_impostos+= ($campos[$i]['qtde'] * $valor_revenda);
					
					//Como esse PA é de Revenda, com certeza, ele é um PI e sendo assim eu busco qual é o seu correspondente ...
					/*$sql = "SELECT id_produto_insumo 
                                                FROM `produtos_acabados` 
                                                WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                                                AND `id_produto_insumo` > '0' 
                                                AND `ativo` = '1' LIMIT 1 ";
					$campos_pi 			= bancos::sql($sql);
					$id_fornecedor 		= custos::preco_custo_pi($campos_pi[0]['id_produto_insumo'], 0, 1);
					//Aqui eu busco o ICMS da própria lista do Fornecedor Default desse PI ...
					$sql = "SELECT `icms` 
							FROM `fornecedores_x_prod_insumos` 
							WHERE `id_fornecedor` = '$id_fornecedor' 
							AND `id_produto_insumo` = '".$campos_pi[0]['id_produto_insumo']."' LIMIT 1 ";
					$campos_lista 		= bancos::sql($sql);
					$icms_com_red 		= $campos_lista[0]['icms'];//O próprio ICMS já é abastecido com a Red pelo pessoal de Compras ...
					
					//$etapa7_sem_impostos_sem_fator+= ($campos[$i]['qtde'] * $valor_revenda) * (1 - $icms_com_red / 100);*/
					$etapa7_sem_impostos_sem_fator+= ($campos[$i]['qtde'] * $valor_revenda);
				}
			}		
			return array($etapa7_com_impostos, $etapa7_sem_impostos_sem_fator);
		}else {
			return array(0, 0);
		}
	}
	
	function todas_etapas($id_pa_componente, $operacao_custo, $somar_etapa1 = 1, $qtde_orcamento = 0) { //=> $operacao_custo 0-> Industrializado, 1-> revenda
		$sql = "SELECT pac.*, icms.icms, icms.reducao 
				FROM `produtos_acabados_custos` pac 
				INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pac.id_produto_acabado 
				INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
				INNER JOIN `grupos_pas` gp ON gp.id_grupo_pa = ged.id_grupo_pa 
				INNER JOIN `familias` f ON f.id_familia = gp.id_familia 
				INNER JOIN `classific_fiscais` cf ON cf.id_classific_fiscal = f.id_classific_fiscal 
				INNER JOIN `icms` ON icms.id_classific_fiscal = cf.id_classific_fiscal 
				WHERE pac.`operacao_custo` = '$operacao_custo' 
				AND pac.`id_produto_acabado` = '$id_pa_componente' LIMIT 1 ";
		$campos 	= bancos::sql($sql);
		$icms 		= $campos[0]['icms'];
		$reducao 	= $campos[0]['reducao'];
		$icms_st 	= 7;
		$outros_impostos_federais = genericas::variavel(34);

		if(count($campos) == 0) {//Não achou o custo deste produto ...
			return 0;
		}else {
			$id_produto_acabado_custo = $campos[0]['id_produto_acabado_custo'];
			//Se for maior q 1000 pegar outro indece de variavel para o calculo ...
			$fator_custo_etapa_2 = ($campos[0]['qtde_lote'] > 1000) ? genericas::variavel(18) : genericas::variavel(11);
			
			$fator_custo_etapa_1_3_7 	= genericas::variavel(12);
			$fator_custo_etapa_4 		= genericas::variavel(9);
			$fator_custo_etapa_5_6 		= genericas::variavel(10);

			/*Cuidado ao mexer nestas variavel abaixo eu declarei com constante até a etapa 6 em uma variavel 
			semelhante, pois nao posso perder o 1º valor de cada etapa, já no caso da etapa 7 tem que ser cumulativa, 
			pois ela é recursiva ...*/
			$etapa11 = custos::etapa1($id_pa_componente, $fator_custo_etapa_1_3_7);
			//define('etapa11', $etapa11[0]);
			define('etapa111', $etapa11[1]);
			//$GLOBALS['etapa1_com_impostos'] = constant('etapa11');
			$GLOBALS['etapa1_sem_impostos_sem_fator'] = constant('etapa111');
			 
			$etapa22 = custos::etapa2($campos[0]['id_produto_insumo'], $campos[0]['peso_kg'], $campos[0]['peca_corte'], $campos[0]['comprimento_1'], $campos[0]['comprimento_2'], $fator_custo_etapa_2);
			//define('etapa22', $etapa22[0]);
			define('etapa222', $etapa22[1]);
			//$GLOBALS['etapa2_com_impostos'] = constant('etapa22');
			$GLOBALS['etapa2_sem_impostos_sem_fator'] = constant('etapa222');
			 
			$etapa33 = custos::etapa3($id_pa_componente, $fator_custo_etapa_1_3_7, $operacao_custo);
			//define('etapa33', $etapa33[0]);
			define('etapa333', $etapa33[1]);
			//$GLOBALS['etapa3_com_impostos'] = constant('etapa33');
			$GLOBALS['etapa3_sem_impostos_sem_fator'] = constant('etapa333');
			 
			$etapa44 = custos::etapa4($id_pa_componente, $fator_custo_etapa_4, $operacao_custo);
			$GLOBALS['etapa4_especial'] = $etapa44;//nunca tirar esta linha, ela é usada pela itens de orçamentos
			//$GLOBALS['etapa4_especial'] =custos::etapa4($id_pa_componente, $fator_custo_etapa_4, $operacao_custo);//nunca tirar esta linha, ela é usada pela itens de orçamentos
			define('etapa44', $etapa44);
			$GLOBALS['etapa4'] = constant('etapa44');
						 
			$etapa55 = custos::etapa5($id_pa_componente, $GLOBALS['peso_aco_kg'], $fator_custo_etapa_5_6, $operacao_custo, $qtde_orcamento);
			//define('etapa55', $etapa55[0]);
			define('etapa555', $etapa55[1]);
			//$GLOBALS['etapa5_com_impostos'] = constant('etapa55');
			$GLOBALS['etapa5_sem_impostos_sem_fator'] = constant('etapa555');
			 
			$etapa66 = custos::etapa6($id_pa_componente, $fator_custo_etapa_5_6, $operacao_custo);
			//define('etapa66', $etapa66[0]);
			define('etapa666', $etapa66[1]);
			//$GLOBALS['etapa6_com_impostos'] = constant('etapa66');
			$GLOBALS['etapa6_sem_impostos_sem_fator'] = constant('etapa666');
		
			$etapa77 = custos::etapa7($id_produto_acabado_custo, '', $qtde_orcamento);
			//$GLOBALS['etapa7_com_impostos'] = $etapa77[0];
			$GLOBALS['etapa7_sem_impostos_sem_fator'] = $etapa77[1];

			//De prache já somo da Etapa 2 em diante ...
			//$GLOBALS['total_com_impostos'] = $etapa22[0] + $etapa33[0] + $etapa44 + $etapa55[0] + $etapa66[0] + $etapa77[0];
			if($somar_etapa1 == 1) $GLOBALS['total_com_impostos']+= $etapa11[0];//Somar a etapa 1 para o custo ...
			$GLOBALS['total_sem_impostos_sem_fator'] = $etapa22[1] + $etapa33[1] + $etapa44 + $etapa55[1] + $etapa66[1] + $etapa77[1];
			if($somar_etapa1 == 1) $GLOBALS['total_sem_impostos_sem_fator']+= $etapa11[1];//Somar a etapa 1 para o custo ...
			$icms_com_red = $icms * (1 - $reducao / 100);
			//$GLOBALS['total_com_impostos_novo'] = $GLOBALS['total_sem_impostos_sem_fator'] / (1 - ($icms_com_red + $outros_impostos_federais) / 100);
			return $GLOBALS['total_sem_impostos_sem_fator'];
		}
	}
	/*************************************************************************************************************************************/	
	function pas_atrelados($id_produto_acabado) {
        	//PA Filho = PA Principal do Custo ...
        	//PA Pai = PA atrelado na 7ª Etapa do Custo ...
        	//Aqui eu verifico a Operação de Custo do PA que entrou no Loop ...
        	$sql = "SELECT operacao_custo 
                	FROM `produtos_acabados` 
                	WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        	$campos         = bancos::sql($sql);
        	$operacao_custo = $campos[0]['operacao_custo'];
/*Pegamos o PA Pai passado por parâmetro e localizamos todos os id_custos que tenham este PA na Sétima Etapa, 
 com estes id_custos, verificamos todos os id_PA Filhos destes id_custos cuja OC de Custo seja = OC do PA Filho*/
        	$sql = "SELECT pac.id_produto_acabado 
                	FROM `pacs_vs_pas` pp 
                	INNER JOIN `produtos_acabados_custos` pac ON pac.id_produto_acabado_custo = pp.id_produto_acabado_custo 
                	INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pac.id_produto_acabado AND pa.operacao_custo = pac.operacao_custo 
                	WHERE pp.`id_produto_acabado` = '$id_produto_acabado' ";
        	$campos_pa = bancos::sql($sql);//pego todos os filho do PA ou seja 7ª etapa
        	$linhas_pa = count($campos_pa);
        	for($i = 0; $i < $linhas_pa; $i++) {
            	//Aqui eu verifico se o PA tem detalhes para Explodir Visualização e se o mesmo também não pertence a Família de Componentes ...
            		$sql = "SELECT pa.explodir_view_estoque 
                    		FROM `produtos_acabados` pa 
                    		INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    		INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.`id_familia` NOT IN (23, 24, 25) 
                    		WHERE pa.`id_produto_acabado` = '".$campos_pa[$i]['id_produto_acabado']."' LIMIT 1 ";
            		$campos_view_pa = bancos::sql($sql);
            		//Se sim posso pegar todos os PA relacionados ...
            		if(strtoupper($campos_view_pa[0]['explodir_view_estoque']) == 'S') $id_pas_atrelados_view[] = $campos_pa[$i]['id_produto_acabado'];
        	}
/*Pegamos todos os Custos deste PA Filho passado por parâmetro cuja a OC do Custo seja = a OC do PA. Com estes 
 id_custos verificamos id_PA(s) Pais que estão na 7ª Etapa destes id_custo.*/
        	$sql = "SELECT pp.id_produto_acabado 
                	FROM `produtos_acabados_custos` pac 
                	INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pac.id_produto_acabado AND pa.`operacao_custo` = pac.`operacao_custo` AND pa.`ativo` = '1' 
                	INNER JOIN `pacs_vs_pas` pp ON pp.id_produto_acabado_custo = pac.id_produto_acabado_custo 
                	WHERE pac.`id_produto_acabado` = '$id_produto_acabado' ";
        	$campos_pa = bancos::sql($sql);//pego todos os Pais do PA ou seja ele é 7ª etapa de outro PA
        	$linhas_pa = count($campos_pa);
        	for($i = 0; $i < $linhas_pa; $i++) {
            		//Aqui eu verifico se o PA tem detalhes para Explodir Visualização e se o mesmo também não pertence a Família de Componentes ...
            		$sql = "SELECT pa.explodir_view_estoque 
                    		FROM `produtos_acabados` pa 
                    		INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    		INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.`id_familia` NOT IN (23, 24, 25) 
                    		WHERE pa.`id_produto_acabado` = '".$campos_pa[$i]['id_produto_acabado']."' LIMIT 1 ";
            		$campos_view_pa = bancos::sql($sql);
            		//Se sim posso pegar todos os PA relacionados ...
            		if(strtoupper($campos_view_pa[0]['explodir_view_estoque']) == 'S') $id_pas_atrelados_view[] = $campos_pa[$i]['id_produto_acabado'];
        	}
        	$linhas_pa_array = count($id_pas_atrelados_view);
        	//Aqui é p/ não dar erro de Array ...
        	if(!isset($GLOBALS['id_pa_atrelados'])) $GLOBALS['id_pa_atrelados'][] = 0;
        	for($x = 0; $x < $linhas_pa_array; $x++) {
            		if(!in_array($id_pas_atrelados_view[$x], $GLOBALS['id_pa_atrelados'])) {
                		$GLOBALS['id_pa_atrelados'][] = $id_pas_atrelados_view[$x];
                		custos::pas_atrelados($id_pas_atrelados_view[$x]);
            		}
        	}
        	return $GLOBALS['id_pa_atrelados'];
    	}

	function custos_hora_maquina($id_maquina) {//Calcula o custo de cada maquina
		//Sal_medio=(aumento_provisorio/100+1)*soma($sal_por_hora/qtde_func_atrelado) => $sal_por_hora =PD+PF+premio
		//custo_h_maq= rount((valor_maq/(duracao*12*dias_trab_mes*horas_trab_dia)*1+porc_ferramental/100))+sal_medio*2) para 2 casas decimais
		$sql = "Select valor, duracao, porc_ferramental, qtde_maq_vs_func 
				from maquinas 
				where id_maquina = '$id_maquina' limit 1 ";
		$campos_maquina = bancos::sql($sql);
		if(count($campos_maquina) == 1) {//Se achou a maquina
			$valor_maquina 		= $campos_maquina[0]['valor'];
			$anos_amortizacao 	= $campos_maquina[0]['duracao'];
			$porc_ferramental 	= $campos_maquina[0]['porc_ferramental'];
			$qtde_maq_vs_func 	= $campos_maquina[0]['qtde_maq_vs_func'];
			//Trago os fatores contidos nas variaveis ...
		 	$dolar_custo			= genericas::variavel(7);
			$euro_custo				= genericas::variavel(8);
			$dias_trab_mes			= genericas::variavel(13);
			$horas_trab_dia			= genericas::variavel(14);
			$aumento_sal_provisorio	= genericas::variavel(15);
		 	//Trago a média dos salarios deste funcionario quando ele é ativo ou diferente de 3 e tambem é horista ...
			$sql = "Select avg(f.salario_pd + f.salario_pf + f.salario_premio) media 
					from funcionarios f 
					inner join maquinas_vs_funcionarios mf on mf.id_funcionario = f.id_funcionario 
					inner join maquinas m on m.id_maquina = mf.id_maquina 
					where tipo_salario = '1' 
					and f.ativo = '1' 
					and f.status <> '3' 
					and m.id_maquina = '$id_maquina' ";
			$campos = bancos::sql($sql);
			$salario_medio_fun = $campos[0]['media'];//Media horista ...
//Trago a média dos salarios deste funcionario quando ele é ativo ou diferente de 3 e tambem é mensalista
			$sql = "Select avg((f.salario_pd + f.salario_pf + f.salario_premio) / 192) media 
					from funcionarios f 
					inner join maquinas_vs_funcionarios mf on mf.id_funcionario = f.id_funcionario 
					inner join maquinas m on m.id_maquina = mf.id_maquina 
					where tipo_salario = '2' 
					and f.ativo = '1' 
					and f.status <> '3' 
					and m.id_maquina = '$id_maquina' ";
			$campos = bancos::sql($sql);
			$salario_medio_fun+= $campos[0]['media'];//media mensalista
			$sal_media_maq = ($aumento_sal_provisorio / 100 + 1) * $salario_medio_fun / $qtde_maq_vs_func; //exibir na tela esta média
			$divisao = $anos_amortizacao * 12 * $dias_trab_mes * $horas_trab_dia;//Separei esta parte pois estava dando erro por divisao por zero ...
			if($divisao == 0) $divisao = 1;
			$custo_hora_maq = ($valor_maquina / ($divisao) * (1 + $porc_ferramental / 100)) + $sal_media_maq * 2;
			$custo_hora_maq = round(round($custo_hora_maq,2),3);
			$sql = "Update maquinas set salario_medio = '$sal_media_maq', custo_h_maquina = '$custo_hora_maq' where id_maquina = '$id_maquina' LIMIT 1 ";
			bancos::sql($sql);
		}
	}

	function localizar_maquina($id_funcionario='todos') { //esta funcao esta sendo chamada pelo cadastro de funcionario e o variaveis
		if($id_funcionario <> 'todos') { //localizar as maquinas q este func opera
			$sql = "Select distinct(m.id_maquina) 
					from funcionarios f 
					inner join maquinas_vs_funcionarios mf on mf.id_funcionario = f.id_funcionario 
					inner join maquinas m on m.id_maquina = mf.id_maquina 
					where f.id_funcionario = '$id_funcionario' ";
			$campos_maquina = bancos::sql($sql);
			$linhas = count($campos_maquina);
			for($i = 0; $i < $linhas; $i++) {
				custos::custos_hora_maquina($campos_maquina[$i]['id_maquina']);
			}
		}else {//Caso contrario localizo todas as maquinas ...
			$sql = "Select id_maquina 
					from maquinas 
					where ativo = '1' ";
			$campos_maquina = bancos::sql($sql);
			$linhas = count($campos_maquina);
			for($i = 0; $i < $linhas; $i++) {
				custos::custos_hora_maquina($campos_maquina[$i]['id_maquina']);
			}
		}
	}

	function custo_auto_pipa_revenda($id_fornecedor, $id_produto_insumo, $somar_etapa1=1, $cliente='') {	
		$sql = "SELECT f.id_fornecedor, f.id_pais, f.razaosocial, fpi.tp_moeda, fpi.preco_faturado, fpi.icms, fpi.preco, fpi.preco_faturado_export, fpi.preco_exportacao, fpi.valor_moeda_compra, fpi.fator_margem_lucro_pa 
				FROM `fornecedores_x_prod_insumos` fpi 
				INNER JOIN `fornecedores` f ON fpi.id_fornecedor = f.id_fornecedor 
				WHERE fpi.id_fornecedor = '$id_fornecedor' 
				AND fpi.id_produto_insumo = '$id_produto_insumo' ";
		$campos = bancos::sql($sql);
		$linhas = count($campos);
		if($linhas > 0) {
			$id_fornecedor				= $campos[0]['id_fornecedor'];
			$id_pais					= $campos[0]['id_pais'];
			$fator_importacao			= genericas::variaveis('fator_importacao'); ///falta trazer este valor
			$taxa_financeira_vendas		= genericas::variaveis('taxa_financeira_vendas');
			$outros_impostos_federais 	= genericas::variavel(34);
				
			$sql = "SELECT id_produto_acabado 
                                FROM `produtos_acabados` 
                                WHERE `id_produto_insumo` = '$id_produto_insumo' 
                                AND `id_produto_insumo` > '0' 
                                AND `ativo` = '1' LIMIT 1 ";
			$campos_pa      = bancos::sql($sql);
			$valor_impostos = calculos::calculo_impostos($campos_pa[0]['id_produto_acabado'], '', '', $id_fornecedor);
			
			//Sempre busco do Estado de SP, pois é de onde vendemos ...
			$dados_produto 		= intermodular::dados_impostos_pa($campos_pa[0]['id_produto_acabado'], 1);
			$icms_com_red_venda = $dados_produto['icms'] * (1 - $dados_produto['reducao'] / 100);
			
			if($id_pais == 31) {//Fornecedor do Brasil "Nacional" ...
				//$valor_custo[0] = $campos[0]['fator_margem_lucro_pa'] * $campos[0]['preco_faturado'] * $taxa_financeira_vendas;
				
				//Esse ICMS é da Lista de Preço do Fornecedor que já foi gravado diretamente com a Redução ...
				$custo_de_compra 		= $campos[0]['preco_faturado'] * (1 - $campos[0]['icms'] / 100) + $valor_impostos['valor_iva_item'];				
				$custo_ml_min_sem_ind	= $custo_de_compra * $campos[0]['fator_margem_lucro_pa'] * (1 + $taxa_financeira_vendas / 100) / (1 - ($icms_com_red_venda + $outros_impostos_federais) / 100);
				
				$valor_custo[0]	= $custo_ml_min_sem_ind;
				//preco_venda_ml_min_forn_nacional = $valor_custo[0]...
				
				//Somamos a parte Industrial no final dessa tela em cima da variável -> $custo_ml_min_sem_ind que por enquanto é valor_custo[0]	...
				if(($valor_custo[0] == 0 || empty($valor_custo[0])) || !empty($cliente)) {
					if($campos[0]['tp_moeda'] == 1 || $campos[0]['tp_moeda'] == 2) {//Dólar ou Euro ... 			
			 			//$valor_custo[1] = $campos[0]['fator_margem_lucro_pa'] * $campos[0]['preco_faturado_export'] * (1 + $taxa_financeira_vendas / 100) * $campos[0]['valor_moeda_compra'];
			 			
			 			$custo_de_compra 		= ($campos[0]['preco_faturado_export'] * $campos[0]['valor_moeda_compra']) * (1 - $campos[0]['icms'] / 100) + $valor_impostos['valor_iva_item'];				
						$custo_ml_min_sem_ind	= $custo_de_compra * $campos[0]['fator_margem_lucro_pa'] * (1 + $taxa_financeira_vendas / 100) / (1 - ($icms_com_red_venda + $outros_impostos_federais) / 100);
						
						$valor_custo[1] = $custo_ml_min_sem_ind;
			 			//preco_fat_venda_ml_min_forn_nac_compramos_em_moeda_estr = $valor_custo[1]...		 			
					}else {//Reais ...
						$valor_custo[1] = 0;
					}	
					/***********************HISPANIA***********************/
					/**Preparar Custo Compra Export para Venda no Mercado Nacional**/
					if($id_fornecedor == 146) {//Hispania ...
						if(empty($cliente)) {//Não é está buscando de uma área comercial, talvez é apenas uma informação de custo ...
							$valor_custo[3] = $valor_custo[1];
							
							//preco_fat_ml_min_hispania = $valor_custo[3] ...
						}else if (strtoupper($cliente) == 'N') {//Nacional
							$valor_custo[3] = $valor_custo[0];
						}else {//Internacional ...
							$valor_custo[3] = (empty($valor_custo[1]) || $valor_custo[1] == 0) ? $valor_custo[0] : $valor_custo[1];
						}
					}else {
						$valor_custo[3] = $valor_custo[0];
					}
					/******************************************************/
				}else {
					$valor_custo[3] = $valor_custo[0];
				}
			}else {//Fornecedor Fora do Brasil "Internacional" ...
				/**Verificar no Orçamento quando Cliente é Nacional, Internacional e Trading **/
				/*
				if($campos[0]['tp_moeda'] == 1) {//Dólar ...
					$valor_custo[1] = $campos[0]['fator_margem_lucro_pa'] * $campos[0]['preco_faturado_export'] * (1 + $taxa_financeira_vendas / 100) * $moeda_custo['dolar_custo'] * $fator_importacao;
				}else if($campos[0]['tp_moeda'] == 2) {//Euro
					$valor_custo[1] = $campos[0]['fator_margem_lucro_pa'] * $campos[0]['preco_faturado_export'] * (1 + $taxa_financeira_vendas / 100) * $moeda_custo['euro_custo'] * $fator_importacao;
				}
				$valor_custo[3] = $valor_custo[1];*/
				
				
				
				$fator_importacao 	= genericas::variaveis('fator_importacao');
				$moeda_custo 		= genericas::variaveis('moeda_custo');
		 		
		 		
		 		if($campos[0]['tp_moeda'] == 1) {//Dólar ...
					$custo_de_compra 	= ($campos[0]['preco_faturado_export'] * $moeda_custo['dolar_custo']) * (1 - $campos[0]['icms'] / 100) + $valor_impostos['valor_iva_item'];
				}else if($campos[0]['tp_moeda'] == 2) {//Euro
					$custo_de_compra 	= ($campos[0]['preco_faturado_export'] * $moeda_custo['euro_custo']) * (1 - $campos[0]['icms'] / 100) + $valor_impostos['valor_iva_item'];
				}
				$custo_ml_min_sem_ind	= $custo_de_compra * $fator_importacao * $campos[0]['fator_margem_lucro_pa'] * (1 + $taxa_financeira_vendas / 100) / (1 - ($icms_com_red_venda + $outros_impostos_federais) / 100) ;
				
				$valor_custo[3] = $custo_ml_min_sem_ind;
		 			
		 		//preco_fat_venda_ml_min_forn_estr = $valor_custo[3]...		 			
			}
			if($campos[0]['fator_margem_lucro_pa'] == 0) {
				$assunto = 'Módulo de Custo - '.date('d-m-Y H:i:s');
				$sql =	"Select pi.discriminacao, pa.id_produto_acabado 
						from produtos_insumos pi 
						inner join produtos_acabados pa on pa.id_produto_insumo = pi.id_produto_insumo 
						where pi.id_produto_insumo = '$id_produto_insumo' ";
				$campos_prod = banocs::sql($sql);
				$sql = "Update produtos_acabados set status_custo = '0' where `id_produto_acabado` = ".$campos_prod[0]['id_produto_acabado']." LIMIT 1 ";
				bancos::sql($sql);
				$mensagem = "O sistema detectou que o produto (".$campos_prod[0]['discriminacao'].") do fornecedor (".$campos[0]['razaosocial'].") está com a margem de lucro zerada e foi bloqueado automaticamente.";
				comunicacao::email('ERP - GRUPO ALBAFER', $margem_de_lucro_zerada_produto_bloqueado, '', $assunto, $mensagem);
				$GLOBALS['fator_margem_lucro_pa'] = 0;
			}else {
				$GLOBALS['fator_margem_lucro_pa'] = $campos[0]['fator_margem_lucro_pa'];
			}

			$sql = "SELECT id_produto_acabado 
                                FROM `produtos_acabados` 
                                WHERE `id_produto_insumo` = '$id_produto_insumo' 
                                AND `id_produto_insumo` > '0' 
                                AND `ativo` = '1' LIMIT 1 ";
			$campos = bancos::sql($sql);
			if(count($campos) > 0) {
				if($somar_etapa1 == 1) {
					$valor_pac_indust = custos::todas_etapas($campos[0]['id_produto_acabado'], 1, 1) * (1 + $taxa_financeira_vendas / 100);//Passo o 1 por causa do insdustri revenda
				}else {
					$valor_pac_indust = custos::todas_etapas($campos[0]['id_produto_acabado'], 1, 0) * (1 + $taxa_financeira_vendas / 100);//Passo o 1 por causa do insdustri revenda
				}
				$valor_custo[0]+= $valor_pac_indust;
				$valor_custo[1]+= $valor_pac_indust;					
				$valor_custo[3]+= $valor_pac_indust;			
				//Na 7 etapa esta somando a tx finaneira por isto coloquei o divisor aqui ...
				return $valor_custo;
			}else {
				return 'ERRO - PRODUTO NÃO LOCALIZADO';
			}
		}
	}
	
	//Quase igual o preco_custo_pi, a <> é q retorna o calculo direto do banco ...
	function custo_embalagem($id_produto_insumo, $id_produto_acabado) {
		$sql = "SELECT pecas_por_emb 
				FROM `pas_vs_pis_embs` 
				WHERE `id_produto_insumo` = '$id_produto_insumo' 
				AND `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
		$campos = bancos::sql($sql);
		if(count($campos) == 0) {//Não achou o custo deste produto ...
			return 0;
		}else {
			$sql = "SELECT unidade_conversao 
					FROM `produtos_insumos` 
					WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
			$campos_pi = bancos::sql($sql);
			if(count($campos_pi) == 0) {//Não achou o custo deste produto ...
				return 0;
			}else {
				$unidade_conversao = ($campos_pi[0]['unidade_conversao'] == 0 || $campos_pi[0]['unidade_conversao'] == 0.00) ? 1 : $campos_pi[0]['unidade_conversao'];
			}
			$pecas_por_emb  = ($campos[0]['pecas_por_emb'] == 0 || $campos[0]['pecas_por_emb'] == NULL) ? 1 : $campos[0]['pecas_por_emb'];
			$pecas_emb_conv = ((1 / $unidade_conversao) / $pecas_por_emb);
		}
		if($pecas_emb_conv == 0.00 || $pecas_emb_conv == 0) $pecas_emb_conv = 1;

		$dados_pi 				= custos::preco_custo_pi($id_produto_insumo, 0);
		$preco_pi				= $dados_pi['preco_comum'];
		$icms 					= $dados_pi['icms'];

		$custo_embalagem_com_impostos+= ($preco_pi * $pecas_emb_conv);
		return array($custo_embalagem_com_impostos);
	}

	function preco_produto_sem_pedido($id_produto_insumo, $desvio=0) {
		//Pega o Primeiro Valor Nacional, a idéia é retornar o menor preço > do que Zero ...
		$sql = "SELECT min( fpi.preco_faturado + fpi.preco_faturado_adicional) AS preco_faturado_total, f.id_fornecedor 
				FROM fornecedores_x_prod_insumos fpi 
				INNER JOIN fornecedores f on f.id_fornecedor = fpi.id_fornecedor AND f.ativo = '1' 
				WHERE fpi.id_produto_insumo = '$id_produto_insumo' 
				AND fpi.ativo = '1' 
				AND (fpi.preco_faturado + fpi.preco_faturado_adicional) > '0' 
				GROUP BY (fpi.preco_faturado + fpi.preco_faturado_adicional) limit 1 ";
		$campos_fat = bancos::sql($sql);
		$valor1 = (count($campos_fat) > 0) ? $campos_fat[0]['preco_faturado_total'] : 0;
		//Pega o Dólar Export ...
		$moeda_custo 		= genericas::variaveis('moeda_custo');
		$fator_importacao 	=	genericas::variaveis('fator_importacao');
		$sql = "Select (fpi.preco_faturado_export + fpi.preco_faturado_export_adicional) as preco_faturado_export_total, fpi.valor_moeda_compra, f.id_pais, f.id_fornecedor 
				from fornecedores_x_prod_insumos fpi 
				inner join fornecedores f on f.id_fornecedor = fpi.id_fornecedor 
				where fpi.tp_moeda = '1' 
				and fpi.id_produto_insumo = '$id_produto_insumo' order by preco_faturado_export_total desc limit 1 ";
		$campos_dolar = bancos::sql($sql);
		if(count($campos_dolar) > 0) {//Não tem pedido mas tem na lista de Preço ...
			if($campos_dolar[0]['id_pais'] == 31) {//Brasil
				$valor2 = $campos_dolar[0]['preco_faturado_export_total'] * $campos_dolar[0]['valor_moeda_compra'];
			}else {//País internacional ...
				$valor2 = $campos_dolar[0]['preco_faturado_export_total'] * $moeda_custo['dolar_custo'] * $fator_importacao;
			}
		}else {
			$valor2 = 0;
		}
		//Pega o Euro Export ...
		$sql = "Select (fpi.preco_faturado_export + fpi.preco_faturado_export_adicional) as preco_faturado_export_total, fpi.valor_moeda_compra, f.id_pais, f.id_fornecedor 
				from fornecedores_x_prod_insumos fpi 
				inner join fornecedores f on f.id_fornecedor = fpi.id_fornecedor 
				where fpi.tp_moeda = '2' 
				and fpi.id_produto_insumo = '$id_produto_insumo' order by preco_faturado_export_total desc limit 1 ";
		$campos_euro = bancos::sql($sql);
		if(count($campos_euro) > 0) { //nao tem pedido mas tem na lista de preco
			if($campos_euro[0]['id_pais'] == 31) {//Brasil
				$valor3 = $campos_euro[0]['preco_faturado_export_total'] * $campos_euro[0]['valor_moeda_compra'];
			}else {//País internacional ...
				$valor3 = $campos_euro[0]['preco_faturado_export_total'] * $moeda_custo['euro_custo'] * $fator_importacao;
			}
		}else {
			$valor3 = 0;
		}
		if($desvio == 0) { //retorna os valores em array
			$valor_custo[0] = $valor1;
			if($valor2 >= $valor3) {
				$valor_custo[1] = $valor2;
			}else {
				$valor_custo[1]= $valor3;
			}
			return $valor_custo;
		}else { //retorna o id fornecedor default
			if($valor1 >= $valor2 && $valor1 >= $valor3) {
				return $campos_fat[0]['id_fornecedor'];
			}else if($valor2 >= $valor1 && $valor2 >= $valor3) {
				return $campos_dolar[0]['id_fornecedor'];
			}else {
				return $campos_euro[0]['id_fornecedor'];
			}
		}
	}
/***********************************Aqui eu Pego o Fornecedor Defatul do PI***********************************/
	function preco_custo_pi($id_produto_insumo, $desvio = 0, $get_id_fonecedor = 0) {//Traz o valor do custo real do PI com base do pedido na lista de preço se n~ tiver pega o maior da lista
		//Rotina normal de como era antigamente ...
		$sql = "SELECT (fpi.preco_faturado + fpi.preco_faturado_adicional) AS preco_faturado_total, 
                        (fpi.preco_faturado_export+fpi.preco_faturado_export_adicional) AS preco_faturado_export_total, 
                        fpi.tp_moeda, fpi.valor_moeda_compra, fpi.icms, f.id_pais, f.id_fornecedor 
                        FROM `produtos_insumos` pi 
                        INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_fornecedor` = pi.`id_fornecedor_default` 
                        INNER JOIN `fornecedores` f ON f.id_fornecedor = fpi.id_fornecedor 
                        WHERE pi.`id_produto_insumo` = '$id_produto_insumo' 
                        AND pi.`id_fornecedor_default` > '0' 
                        AND pi.`ativo` = '1' LIMIT 1 ";
		$campos_lista = bancos::sql($sql);//Essa parte iria ser a minha nova adaptação -Dárcio ...
		if(count($campos_lista) > 0) {//Possui um pedido e um fornecedore default ...
			$icms = $campos_lista[0]['icms'];
			//Este parametro é q só quero saber quem é o id_fornecedor default, ou seja o ultimo pedido deste PI ...
			if($get_id_fonecedor == 1) return $campos_lista[0]['id_fornecedor'];//id_fornecedor_default ...
			if($campos_lista[0]['id_pais'] == 31) {//Brasil ...
				$valor_custo[0] = $campos_lista[0]['preco_faturado_total'];
				if($campos_lista[0]['tp_moeda'] == 1) {//Dolar ...
					 $valor_custo[1] = $campos_lista[0]['preco_faturado_export_total'] * $campos_lista[0]['valor_moeda_compra'];
		 		}else if($campos_lista[0]['tp_moeda'] == 2) {//Euro ...
		 			$valor_custo[1] = $campos_lista[0]['preco_faturado_export_total'] * $campos_lista[0]['valor_moeda_compra'];
		 		}else {
		 			$valor_custo[1] = 0;
				}
			}else {//País internacional ...
				$moeda_custo 		= genericas::variaveis('moeda_custo');
				$fator_importacao 	= genericas::variaveis('fator_importacao');
				$valor_custo[0] 	= $campos_lista[0]['preco_faturado_total'];
				if($campos_lista[0]['tp_moeda'] == 1) {//Dolar ...
					$valor_custo[1] = $campos_lista[0]['preco_faturado_export_total'] * $moeda_custo['dolar_custo'] * $fator_importacao;
				}else if($campos_lista[0]['tp_moeda'] == 2) {//Euro ...
					$valor_custo[1] = $campos_lista[0]['preco_faturado_export_total'] * $moeda_custo['euro_custo'] * $fator_importacao;
				}else {
					$valor_custo[1] = 0;
				}
			}	
			if($desvio == 0) {
				if($valor_custo[0] > $valor_custo[1]) {
					return array('icms'=>$icms, 'preco_comum'=>$valor_custo[0]);//Retona o maior valor para calcular o valor do custo do PA desembutido o icms por causa do IVA
				}else {
					return array('icms'=>$icms, 'preco_comum'=>$valor_custo[1]);//Retona o maior valor para calcular o valor do custo do PA
				}
			}else {//Desvio é >= 1 significa q temos q trazer os dois valor para formacao da base de calculo do PI			
				return array('icms'=>$icms, 'preco_comum'=>$valor_custo[0], 'preco_estrangeiro'=>$valor_custo[1]);//retorna em array para a formacao do custo do PI
			}
		}else {//Aqui significa q nao possui pedido para este produto insumo então pego o maior preco da lista para ele dos fornec atrelado
			//Este parametro é q só quero saber quem é o id_fornecedor default, ou seja o ultimo pedido deste PI ...			
			if($get_id_fonecedor == 1) return custos::preco_produto_sem_pedido($id_produto_insumo, $get_id_fonecedor);//Busco o id_fornecedor_default do maior
			$valor_custo = custos::preco_produto_sem_pedido($id_produto_insumo);
			$icms 		 = 0;
			if($desvio == 0) {
				if($valor_custo[0] > $valor_custo[1]) {
					return array('icms'=>$icms, 'preco_comum'=>$valor_custo[0]);//Retona o maior valor para calcular o valor do custo do PA
				}else {
					return array('icms'=>$icms, 'preco_comum'=>$valor_custo[1]);//Retona o maior valor para calcular o valor do custo do PA
				}
			}else {//Desvio é >= 1 significa q temos q trazer os dois valor para formacao da base de calculo do PI
				return array('icms'=>$icms, 'preco_comum'=>$valor_custo[0], 'preco_estrangeiro'=>$valor_custo[1]);
			}
		}
	}

	
//Esta funcao calcula somente os produtos do tipo revenda q estão atrelados ao Industrialização ...
	function pipa_revenda($id_pa_componente, $somar_embalagem=1) {
		return custos::procurar_fornecedor_default_revenda($id_pa_componente, '', $somar_embalagem);
	}

	/*Regulariza o BD, dos produtos sem referencia para o custo, ela verifica se o produto tem compra 
	em pedidos se tem atualiza o custo e o fornecedor default.*/
	function procurar_fornecedor_default($id_produto_insumo) {
		$sql = "SELECT p.id_fornecedor, p.id_pedido 
				FROM `itens_pedidos` ip 
				INNER JOIN `pedidos` p ON p.id_pedido = ip.id_pedido 
				WHERE ip.`id_produto_insumo` = '$id_produto_insumo' ORDER BY p.id_pedido DESC LIMIT 1 ";
		$campos = bancos::sql($sql);//pego o id para deixar este fornecedor como defalt para este produto
		if(count($campos) == 1) custos::setar_fornecedor_default($id_produto_insumo, $campos[0]['id_fornecedor']);
	}

/***********************************Aqui eu Pego o Fornecedor Defatul do PA***********************************/
	function procurar_fornecedor_default_revenda($id_produto_acabado, $desvio=0, $somar_embalagem=1, $get_id_fornecedor_default=0, $cliente="") { //Esta rotina pega o fornecedor padrao e depois procura o valor do PA revenda
		//Pego id_fornecedor e o PI do último `Pedido` do fornecedor setado como Default ...
                $sql = "SELECT fpi.id_fornecedor, fpi.id_produto_insumo, pa.operacao 
                        FROM `produtos_acabados` pa 
                        INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = pa.`id_produto_insumo` AND pi.`tem_pedido` = 'S' 
                        INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_fornecedor` = pi.`id_fornecedor_default` AND pi.`id_fornecedor_default` > '0' AND fpi.`ativo` = '1' 
                        WHERE pa.`id_produto_acabado` = '$id_produto_acabado' 
                        AND pa.`id_produto_insumo` > '0' 
                        AND pa.`operacao_custo` = '1' 
                        AND pa.`ativo` = '1' ";
		$campos = bancos::sql($sql);	
		if(count($campos)>0) {
			//Significa q quero q retorne somente o id do fornec padrão de revenda ...
			if($get_id_fornecedor_default == 1) {
				return $campos[0]['id_fornecedor'];
			}else {
				$valor = custos::custo_auto_pipa_revenda($campos[0]['id_fornecedor'], $campos[0]['id_produto_insumo'], $somar_embalagem, $cliente);
			}
		}else {
			//Pega o id_fornecedor e o id_produto da ultima atualizacao da lista de preço
			$sql = "Select fp.id_fornecedor, fp.id_produto_insumo 
					from produtos_acabados pa 
					inner join fornecedores_x_prod_insumos fp on fp.id_produto_insumo = pa.id_produto_insumo and fp.ativo = '1' 
					where pa.operacao_custo = '1' 
					and pa.id_produto_acabado = '$id_produto_acabado' order by fp.data_sys desc limit 1 ";
			$campos = bancos::sql($sql);
			if(count($campos) > 0) {
				custos::setar_fornecedor_default($campos[0]['id_produto_insumo'], $campos[0]['id_fornecedor'], 'N');
				if($get_id_fornecedor_default == 1) {//Significa q quero q retorne somente o id do fornec padrão de revenda ...
					return $campos[0]['id_fornecedor'];
				}else {
					$valor = custos::custo_auto_pipa_revenda($campos[0]['id_fornecedor'],$campos[0]['id_produto_insumo'], $somar_embalagem, $cliente);
				}
			}else {
				return 0; //se nao achar um fornecedor com a última compra(pedido) ele retorna com o custo zero mas tenho q v pois posso te apensa na lista de preço
			}
		}
		if($desvio == 0) {
			return $valor[3];
		}else {
			return $valor;
		}
	}

	function setar_fornecedor_default($id_produto_insumo, $id_fornecedor, $tem_pedido='N') {//está funcao tem q está somente no incluir itens.
            if($id_produto_insumo <> 0 && $id_fornecedor <> 0) {
                //Atualização do Fornecedor Default e mais alguns dados na tabela de PI ...
                $sql = "UPDATE `produtos_insumos` SET `id_fornecedor_default` = '$id_fornecedor',  `id_funcionario_fornecedor_default` = '$_SESSION[id_funcionario]', `tem_pedido` = '$tem_pedido' WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
                bancos::sql($sql);

                //Verifico se esse Fornecedor possui esse PI na Lista de Preços ...
                $sql = "SELECT id_fornecedor_prod_insumo 
                        FROM `fornecedores_x_prod_insumos` 
                        WHERE `id_produto_insumo` = '$id_produto_insumo' 
                        AND `id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
                //pego o id para deixar este fornecedor como defalt para este produto
                $campos = bancos::sql($sql); 

                //Atualizo o item da Lista de Preço retirando os Preços adicionais ...
                $sql = "UPDATE `fornecedores_x_prod_insumos` SET `preco_faturado_export_adicional` = '0', `preco_faturado_adicional` = '0' WHERE `id_fornecedor_prod_insumo` = '".$campos[0]['id_fornecedor_prod_insumo']."' LIMIT 1 ";
                bancos::sql($sql);
            }
	}

	function lista_preco_vendas($id_produto_acabado) {
		$sql = "Select pa.preco_unitario, pa.preco_export, ged.desc_base_a_nac, ged.desc_base_b_nac, ged.acrescimo_base_nac, ged.desc_base_exp, ged.acrescimo_base_exp 
				from produtos_acabados pa 
				inner join gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
		 		where pa.id_produto_acabado = '$id_produto_acabado' LIMIT 1 ";
		$campos = bancos::sql($sql); //verifico se ele consta na tabela se nao cadastro ele
		if(count($campos) > 0) {
			$fator_desc_max_vendas 	= genericas::variavel(19);
			$dolar_dia				= genericas::moeda_dia("dolar");
			$preco_liq_fat_reais[] 	= $campos[0]['preco_unitario'] * (1 - $campos[0]['desc_base_a_nac'] / 100) * (1 - $campos[0]['desc_base_b_nac'] / 100) * (1 + $campos[0]['acrescimo_base_nac'] / 100);
			$preco_custo_pa			= custos::preco_custo_pa($id_produto_acabado);//Retorna o valor nacional ...
			$preco_max_fat_custo[] 	= $preco_custo_pa / $fator_desc_max_vendas;
			//Export ...
			$preco_custo_pa_exp 	= custos::preco_custo_pa($id_produto_acabado, 1);//Retorna o valor export ...
			$preco_liq_fat_reais[] 	= $campos[0]['preco_export'] * (1 - $campos[0]['desc_base_exp'] / 100) * (1 + $campos[0]['acrescimo_base_exp'] / 100);
			$preco_max_fat_custo[]	= $preco_custo_pa_exp / $fator_desc_max_vendas / $dolar_dia;	
			return $preco_liq_fat_reais;//indice 0 <=> nacional - 1 <=> export
		}else {
			return 0;
		}
	}

	function preco_custo_pa($id_produto_acabado, $nacional=0) {//Retorna o valor do custo indiferente de ser revenda ou Ind. ele mesmo identifica
		$sql = "Select operacao_custo 
				from `produtos_acabados` 
				where id_produto_acabado = '$id_produto_acabado' limit 1 ";
		$campos = bancos::sql($sql);
		if(count($campos) > 0) {
			if($campos[0]['operacao_custo'] == 0) {//Tipo de Industrializacao ...
				$taxa_financeira_vendas = genericas::variavel(16) / 100 + 1;
				return custos::todas_etapas($id_produto_acabado, 0) * $taxa_financeira_vendas;
			}else if ($campos[0]['operacao_custo'] == 1) {//Tipo de Revenda ...
				$valor = custos::procurar_fornecedor_default_revenda($id_produto_acabado, 1);//1 para hretornar em array com indice 0 nac, 1 export
				return $valor[3]; //caso ele tenho 2 preço de custo pegar o maior
			}else {
				return 0;
			}
		}else {
			return 0;
		}
	}

	function liberar_desliberar_custo_auto($id_produto_acabado, $id_produto_acabado_custo) {// verifica quando atrela o itens na 7º etapa
		$sql = "Select status_custo, operacao_custo 
				from produtos_acabados 
				where `id_produto_acabado` = '$id_produto_acabado' limit 1 ";
		$campos = bancos::sql($sql);
		if(count($campos) > 0) {
			if($campos[0]['status_custo'] == 0) {//Se o produto em q passei estiver bloqueado, bloqueia todos os seu relacionados
				custos::liberar_desliberar_custo($id_produto_acabado_custo, 'nao');
			}
		}else {
			exit('ERRO NO SISTEMA, CONTATE O ADMINISTRADOR !');
		}
	}

	function liberar_desliberar_custo($id_produto_acabado_custo, $liberar) {
		//Antes de liberar o Custo, eu verifico se foi zerado a Qtde do Lote ...
		$sql = "SELECT qtde_lote 
				FROM `produtos_acabados_custos` 
				WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
		$campos_qtde_lote = bancos::sql($sql);
		//Se estiver zerado, tem de bloquear o Custo. Não posso liberar o Custo de jeito maneira ...
		if($campos_qtde_lote[0]['qtde_lote'] == 0) $liberar = 0;
		/*********************************************************************
		Problema ele pega o pai e verifica o filho e vice versa, pois são recursiva da mesma funcao ...
		**********************************************************************/
		if(strtoupper($liberar) == 'SIM') {//Deseja liberar o custo
			//Verifico quem é o id_produto_acabado_custo através do id_produto_acabado
			//Busco todos os produtos da etapa 7 através do id_produto_acabado_custo e se tiver algum bloqueado ele tras registro(s)
			$sql = "Select pac.id_produto_acabado 
					from pacs_vs_pas pp 
					inner join produtos_acabados pa on pa.id_produto_acabado = pp.id_produto_acabado and pa.status_custo = '0' 
					inner join produtos_acabados_custos pac on pac.id_produto_acabado_custo = pp.id_produto_acabado_custo 
					where pp.id_produto_acabado_custo = '$id_produto_acabado_custo' limit 1 ";
			$campos = bancos::sql($sql);
			if(count($campos) == 0) {//Se não tiver nenhum bloqueado ele pode liberar ...
				$sql = "Select pac.id_produto_acabado, pa.referencia 
						from produtos_acabados_custos pac 
						inner join produtos_acabados pa on pa.id_produto_acabado = pac.id_produto_acabado 
						where pac.id_produto_acabado_custo = '$id_produto_acabado_custo' ";
				$campos_custo = bancos::sql($sql);
				if(count($campos_custo) > 0) { //Aqui muda o status_custo do produto acabado p/ liberado ou não
					if($campos_custo[0]['referencia'] == 'ESP') {// atualiza o preço na "lista de preço" para saber se compensa da prioridade na fabricação
						//Esta Funcão tem a mesma semelhança a do relatório do estoque ESP ...
						if(!class_exists('genericas')) require 'genericas.php';
						$fator_desc_max_vendas 	= genericas::variavel(19);
						$id_produto_acabado 	= $campos_custo[0]['id_produto_acabado'];
						$preco_maximo_custo_fat_rs = custos::preco_custo_pa($id_produto_acabado) / $fator_desc_max_vendas;
						$sql = "Select ged.desc_base_a_nac, ged.desc_base_b_nac, ged.acrescimo_base_nac 
								from produtos_acabados pa 
								inner join gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
								where pa.referencia = 'ESP' 
								and pa.ativo = '1' 
								and pa.id_produto_acabado = '$id_produto_acabado' limit 1 ";
						$campos_pa_esp = bancos::sql($sql);
						$linhas_pa_esp=count($campos_pa_esp);
/*************Tratamento para não dar erro de Divisão por Zero*************/
//Desconto A <> 100 e B <> 100 ... 
						if((double)$campos_pa_esp[0]['desc_base_a_nac'] != 100 && (double)$campos_pa_esp[0]['desc_base_b_nac'] != 100) {
							$txt_preco_bruto_fat_rs = $preco_maximo_custo_fat_rs / (1 - $campos_pa_esp[0]['desc_base_a_nac'] / 100) / (1 - $campos_pa_esp[0]['desc_base_b_nac'] / 100) * (1 + $campos_pa_esp[0]['acrescimo_base_nac'] / 100);
//Somente A <> 100 ...
						}else if((double)$campos_pa_esp[0]['desc_base_a_nac'] != 100 && (double)$campos_pa_esp[0]['desc_base_b_nac'] == 100) {
							$txt_preco_bruto_fat_rs = $preco_maximo_custo_fat_rs / (1 - $campos_pa_esp[0]['desc_base_a_nac'] / 100) * (1 + $campos_pa_esp[0]['acrescimo_base_nac'] / 100);
//Somente B <> 100 ...
						}else if((double)$campos_pa_esp[0]['desc_base_a_nac'] == 100 && (double)$campos_pa_esp[0]['desc_base_b_nac'] != 100) {
							$txt_preco_bruto_fat_rs = $preco_maximo_custo_fat_rs / (1 - $campos_pa_esp[0]['desc_base_b_nac'] / 100) * (1 + $campos_pa_esp[0]['acrescimo_base_nac'] / 100);
//Desconto A = 100 e B = 100 ...
						}else if((double)$campos_pa_esp[0]['desc_base_a_nac'] == 100 && (double)$campos_pa_esp[0]['desc_base_b_nac'] == 100) {
							$txt_preco_bruto_fat_rs = $preco_maximo_custo_fat_rs * (1 + $campos_pa_esp[0]['acrescimo_base_nac'] / 100);
						}
						$sql = "Update produtos_acabados set `preco_unitario` = '$txt_preco_bruto_fat_rs' where `id_produto_acabado` = '$id_produto_acabado' ";
						bancos::sql($sql);
					}
/**************************************************************************/
					$sql = "Update `produtos_acabados` set `status_custo` = '1' where `id_produto_acabado` = '".$campos_custo[0]['id_produto_acabado']."' limit 1 ";
					bancos::sql($sql);
/**************************************************************************************************/
/************************************Verifição de Estoque do PA************************************/
/**************************************************************************************************/
/*Aqui eu verifico se esse PA já tem algum Registro na Tabela Estoque, pois se eu não tiver esse Registro 
acaba complicando em outros locais do Sistema em que eu só listo o PA desde que esse tenha Estoque ...*/
					$sql = "Select id_estoque_acabado 
							from `estoques_acabados` 
							where `id_produto_acabado`= ".$campos_custo[0]['id_produto_acabado']." limit 1 ";
					$campos_estoque = bancos::sql($sql);
//Caso não exista registro desse, então eu insiro o mesmo na Tabela de Estoque ...
					if(count($campos_estoque) == 0) {
						$sql = "Insert into estoques_acabados (`id_produto_acabado`, `qtde`) values ('".$campos_custo[0]['id_produto_acabado']."', '0') ";
						bancos::sql($sql);
					}
/**************************************************************************************************/
					$sql = "UPDATE mensagens_esps me
							inner join orcamentos_vendas_itens ovi on ovi.id_orcamento_venda_item = me.id_orcamento_venda_item 
							inner join produtos_acabados pa on pa.id_produto_acabado = ovi.id_produto_acabado 
							SET me.status = '1' WHERE pa.id_produto_acabado = '".$campos_custo[0]['id_produto_acabado']."' ";
					bancos::sql($sql);//Seta a msg para exibir no mural do representante que o custo do esp foi liberado
				}else { 
					echo 'ERRO FALTA, CONTATE O ADMINISTRADOR DE INFORMÁTICA !';
				}
			}
		}else {//Bloquear custo do PA - busco todos os atrelados da 7º etapa ...
			$sql = "Select pac.id_produto_acabado 
					from produtos_acabados_custos pac 
					inner join produtos_acabados pa on pa.id_produto_acabado = pac.id_produto_acabado 
					where pac.id_produto_acabado_custo = '$id_produto_acabado_custo' ";
			$campos = bancos::sql($sql);
			if(count($campos) > 0) {//Pego ele e bloqueio o PA dele, passa o Status p/ Bloqueado ...
				$id_produto_acabado = $campos[0]['id_produto_acabado'];
				$sql = "Update `produtos_acabados` set `status_custo` = '0' where `id_produto_acabado` = '$id_produto_acabado' limit 1 ";
				bancos::sql($sql);
				$sql = "UPDATE mensagens_esps me 
						inner join orcamentos_vendas_itens ovi on ovi.id_orcamento_venda_item = me.id_orcamento_venda_item 
						inner join produtos_acabados pa on pa.id_produto_acabado = ovi.id_produto_acabado 
						SET me.status = '0' WHERE pa.id_produto_acabado = '$id_produto_acabado' ";
				bancos::sql($sql);//Seta a msg para exibir no mural do representante que o custo do esp foi liberado
				//Agora pego todos os pais dele ou quem depende dele ...
				$sql = "Select pp.id_produto_acabado_custo 
						from `produtos_acabados` pa 
						inner join pacs_vs_pas pp on pp.id_produto_acabado = pa.id_produto_acabado 
						where pa.id_produto_acabado = '$id_produto_acabado' ";
				$campos = bancos::sql($sql);
				$linhas = count($campos);
				if($linhas > 0) {//Segue este caminho quando ele é P.A. PAI, bloqueio ele e os filhos dele ...
					for($i = 0; $i < $linhas; $i++) {//Listo os PA da 7º etapa e vasculho novamente cada um dele
						return custos::liberar_desliberar_custo($campos[$i]['id_produto_acabado_custo'], 'nao');
					}
				}
			}else {
				echo 'ERRO FATAL';
			}
		}
	}

	function calculo_taxa_financeira($id_orcamento_venda, $id_pedido_venda=0) {
		if($id_pedido_venda == 0) {//Faz o calculo com base no orcamento ...
			$sql = "Select prazo_a, prazo_b, prazo_c, prazo_d 
					from `orcamentos_vendas` 
					where id_orcamento_venda = '$id_orcamento_venda' ";
		}else {//Faz o calculo com base no pedido ...
			$sql = "Select vencimento1 prazo_a, vencimento2 prazo_b, vencimento3 prazo_c, vencimento4 prazo_d 
					from `pedidos_vendas` 
 	 				where `id_pedido_venda` = '$id_pedido_venda' limit 1 ";
		}
		$campos = bancos::sql($sql);
		if(count($campos) > 0) {
                        $prazo_medio = intermodular::prazo_medio($campos[0]['prazo_a'], $campos[0]['prazo_b'], $campos[0]['prazo_c'], $campos[0]['prazo_d']);
                    
			$sql = "Update `orcamentos_vendas` set `prazo_medio` = '$prazo_medio' where `id_orcamento_venda` = '$id_orcamento_venda' limit 1 ";
			bancos::sql($sql);//Atualiza no BD o prazo médio do orcamento/pedidos de vendas ...
			$tx_financ_vendas			= genericas::variaveis('taxa_financeira_vendas');
			$fator_tx_financ_diaria		= pow((1 + $tx_financ_vendas / 100), (1 / 30));
			$fator_tx_financ_prazo_medio= pow(($fator_tx_financ_diaria), ($prazo_medio));
			$tx_financeira				= (($fator_tx_financ_prazo_medio / (1 + $tx_financ_vendas / 100)) - 1) * 100;
			return $tx_financeira;
		}else {
			return 0;
		}
	}

	function preco_custo_esp_indust($id_produto_acabado, $qtde_item_orc, $id_pais=31, $ref = 'ESP') {//Calcula o preco do custo, tanto pra margem como para o orcamento
		//Pega a qtde do lote custo ...
		$sql = "Select pac.qtde_lote, pac.id_produto_acabado_custo 
                        from `produtos_acabados_custos` pac 
                        inner join produtos_acabados pa on pa.id_produto_acabado = pac.id_produto_acabado 
                        where pac.operacao_custo = '0' 
                        and pac.id_produto_acabado = '".$id_produto_acabado."' limit 1 ";
		$campos_lote = bancos::sql($sql);
		$linhas_lote = count($campos_lote);
		if($linhas_lote > 0) {//Existe custo para este PA ...
                    $qtde_lote_custo            = $campos_lote[0]['qtde_lote'];
                    $id_produto_acabado_custo	= $campos_lote[0]['id_produto_acabado_custo'];
		}else {//Não existe custo para este PA ...
                    exit('COLOQUEI ESTE DESVIO PARA CERTIFICAR DE QUE NÃO EXISTE CUSTO PARA ESTE PA !');
                    $qtde_lote_custo = 1;
		}
		//Caso o id_pais = Estrangeiro e normal de Linha, entao eu calculo a qtde pelo do custo para nao variar ...
		if($id_pais != 31 && $ref != 'ESP') $qtde_item_orc = $qtde_lote_custo;
		//Se o custo não tiver nada atrelado a 4ª etapa ...
		$sql = "select count(id_pac_maquina) total 
                        from pacs_vs_maquinas 
                        where id_produto_acabado_custo = '".$id_produto_acabado_custo."' ";
		$campos_4_etapa = bancos::sql($sql);
		$qtde_4_etapa 	= $campos_4_etapa[0]['total'];
		//Se o custo não tiver itens no lote minimo na 5ª etapa como lote minimo = '1'
		$sql = "Select count(id_pac_pi_trat) total 
                        from pacs_vs_pis_trat 
                        where lote_minimo_fornecedor = '1' 
                        and id_produto_acabado_custo = '".$id_produto_acabado_custo."' ";
		$campos_5_etapa = bancos::sql($sql);
		$qtde_5_etapa	= $campos_5_etapa[0]['total'];
		
		$taxa_financeira_vendas	= genericas::variaveis('taxa_financeira_vendas') / 100 + 1;
		$fator_desc_max_vendas	= genericas::variavel(19); //Aqui é a Busca da Variável de Vendas= genericas::variavel(19); //Aqui é a Busca da Variável de Vendas
		/*************************Valores Herdados*************************/
		$total_indust			= custos::todas_etapas($id_produto_acabado, 0, 1, $qtde_item_orc);
                
                
                echo 'jjjjjjjjjjjjjjjjjjjjjjjjjjjj'.$total_indust.'kkkkkkkkkkkkkkkkkkkkkkkkkkkkk';
		$etapa4                         = $GLOBALS['etapa4_especial'];
		$custos_fixos			= $total_indust - $etapa4;
		/******************************************************************/
		//Busca do Lote Mínimo de Produção em R$ ...
		if($qtde_4_etapa == 0 && $qtde_5_etapa == 0) {//Se nao tiver itens na 4ª etapa e a 5ª não tiver nada setado no lote minimo eu zero lote_mim para deviar a lógica seguintes
			$lote_min_producao_reais = 0;
		}else {
			$sql = "Select gpa.lote_min_producao_reais 
					from produtos_acabados pa 
					inner join gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
					inner join grupos_pas gpa on gpa.id_grupo_pa = ged.id_grupo_pa 
					where pa.id_produto_acabado = '".$id_produto_acabado."' limit 1 ";
			$campos_lote_min = bancos::sql($sql);
			$linhas_lote_min = count($campos_lote_min);
			if($linhas_lote_min > 0) {//Achou ...
				$lote_min_producao_reais = $campos_lote_min[0]['lote_min_producao_reais'];
				//Caso o id_pais for estrangeiro e Normal de Linha, calculo a qtde pelo do custo para nao variar
				if($id_pais != 31) $lote_min_producao_reais/= genericas::moeda_dia('dolar');
			}else {//Não Achou ...
				$lote_min_producao_reais = 0;
			}
		}
		/*1) Se a Qtde ORC > 5 * Lote do Custo e o Total do Item em R$ < (5x do Lote Mínimo do Grupo)
		  2) Se o Lote do Custo for 5x maior que a Qtde do Orçamento ou 20% acima ou abaixo ... */
		if((($qtde_item_orc > 5 * $qtde_lote_custo) || ($qtde_lote_custo > 5 * $qtde_item_orc)) && ($qtde_4_etapa > 0 || $qtde_5_etapa > 0)) {
			return array(0, 0, 0);//DEPTO TÉCNICO
		}
		$preco_liq_fat_rs 			= ($total_indust * $taxa_financeira_vendas) / $fator_desc_max_vendas;
		//Variáveis para simplificar a vida ...
		$total_item_rs 				= $qtde_item_orc * $preco_liq_fat_rs;
		
		$fator_correcao_qtde 	= $qtde_item_orc / $qtde_lote_custo;
		$fator_correcao_4etapa 	= $qtde_lote_custo * $etapa4 / $qtde_item_orc;
		
		//$qtde_item_orc_corrigido 	= 5 * $lote_min_producao_reais / $preco_liq_fat_rs;
		/*Quando o Lote R$ do Orc for menor do q 5x o Lote Mínimo do Grupo R$, o Custo será interpolado pelo 
		Lote do Orçamento, caso contrário orçaremos para uma Qtde equivalente a 5x o Lote Mínimo do Grupo, 
		fixando esse Custo como sendo o mínimo para qtdes maiores ou iguais a Esta ...*/
		/*if($total_item_rs > 5 * $lote_min_producao_reais) {//Existirá a Qtde de Item Corrigida ...
			if((($qtde_item_orc_corrigido < 5 * $qtde_lote_custo) && ($qtde_item_orc > $qtde_item_orc_corrigido))) {
				$fator_correcao_qtde 	= $qtde_item_orc_corrigido / $qtde_lote_custo;
				$fator_correcao_4etapa 	= ($qtde_item_orc_corrigido == 0) ? 1 : $qtde_lote_custo * $etapa4 / $qtde_item_orc_corrigido;
			}else {
				$fator_correcao_qtde 	= $qtde_item_orc / $qtde_lote_custo;
				$fator_correcao_4etapa 	= $qtde_lote_custo * $etapa4 / $qtde_item_orc;
			}
		}else {//Não passou, então não existe a Qtde de Item Corrigida ...
			$fator_correcao_qtde 	= $qtde_item_orc / $qtde_lote_custo;
			$fator_correcao_4etapa 	= $qtde_lote_custo * $etapa4 / $qtde_item_orc;
		}*/
		
		//Cálculo do Fator de Correção da Qtde ...
		if($fator_correcao_qtde < 0.5) {
			$custo_m_o = ($fator_correcao_qtde * 0.6 + 0.5) * $fator_correcao_4etapa;
		}else {
			$custo_m_o = ($fator_correcao_qtde * 0.5 + 0.5) * $fator_correcao_4etapa;
		}
		$preco_custo = ($custos_fixos + $custo_m_o) * $taxa_financeira_vendas;
		/*O Lote mínimo Ideal deveria ser "Lote Mínimo em R$" / "Pço Líq. Faturado em R$", desde que esse Lote
		 * fosse próximo do Lote do Custo. O Ideal seria que o Custo já fosse feito baseado nesse Lote Mínimo 
		 * Ideal através de mensagem na hora da confecção do Custo.
		 * */
		return array($preco_custo, '', $lote_min_producao_reais);
	}

    function margem_lucro($id_produto_acabado, $tx_financeira, $id_classific_fiscal, $id_uf_cliente, $preco_liq_final, $id_orcamento_venda) {
        $sql = "SELECT ov.nota_sgd, ov.prazo_a, ov.prazo_b, ov.prazo_c, ov.prazo_d, ov.desc_icms_sqd_auto, ovi.*, pa.id_produto_acabado, pa.referencia, pa.discriminacao, pa.operacao_custo, pa.peso_unitario, pa.status_custo, pa.observacao, c.id_cliente, c.id_pais, c.trading, ged.id_empresa_divisao 
                FROM `orcamentos_vendas_itens` ovi 
                INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado AND pa.id_produto_acabado = '$id_produto_acabado' 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda 
                INNER JOIN `clientes` c ON c.id_cliente = ov.id_cliente 
                WHERE ovi.`id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas > 0) {//Verifica se tem pelo menos um item no orçamento ...
            $trading		= $campos[0]['trading'];
            $id_pais		= $campos[0]['id_pais'];
            $cliente 		= ($id_pais == 31) ? 'N' : 'I';

            if($campos[0]['operacao_custo'] == 0) {//Industrialização ...
                $fator_margem_lucro = genericas::variavel(22);//margem de Lucro PA Industrial
                if($campos[0]['referencia'] != 'ESP') {//Normais de linha ...
                    if($campos[0]['status_custo'] == 0) return array(0, 'Orçar');//Desvio somente na ML
                    $taxa_financeira_vendas = genericas::variavel(16);//Tx financeira de vendas
                    $preco_liq_fat_custo    = custos::todas_etapas($id_produto_acabado, 0) * (1 + $taxa_financeira_vendas / 100);
                }else {//Especial, é a mesma lógica do orçamento exceto a divisão pelo fator_margem_lucro_vendas no $preco_custo ...
		if($campos[0]['status_custo'] == 0) return array(0, 'Orçar');//Desvio somente na ML
                    $preco_custo = custos::preco_custo_esp_indust($id_produto_acabado , $campos[0]['qtde'], $id_pais, $campos[0]['referencia']);//retorna array
                    if($preco_custo[0] == 0 && $preco_custo[1] == 0) return array(0, 'DEPTO TÉCNICO');
                    $lote_min_producao_reais = $preco_custo[2];
                    //Significa que está indo pelo LM e o usuário realmente quer q siga esse caminho caso o Sys caia aqui ...
                    if(($campos[0]['qtde'] * $preco_custo[0]) < $lote_min_producao_reais && $campos[0]['ignorar_lote_minimo_do_grupo_faixa_orcavel'] == 'N') {
                        $fator_desc_maximo_venda = genericas::variavel(19);//Aqui é a Busca da Variável de Vendas
                        $preco_liq_fat_custo = $lote_min_producao_reais / $campos[0]['qtde'] * $fator_desc_maximo_venda;
                    }else {
                        $preco_liq_fat_custo = $preco_custo[0];
                    }
                }
            }else {//Revenda ...		
                //Pega o fornecedor padrao, se PA = revenda ...
                //========>>>>> Este sql abaixo é a mesma lógica da duncao procurar_fornecedor_default_revenda()
                $id_fornecedor = custos::procurar_fornecedor_default_revenda($id_produto_acabado, '', '', 1);
                $sql = "SELECT fpi.fator_margem_lucro_pa 
                        FROM `fornecedores_x_prod_insumos` fpi 
                        INNER JOIN `produtos_acabados` pa ON pa.id_produto_insumo = fpi.id_produto_insumo AND pa.id_produto_acabado = '$id_produto_acabado' AND pa.`id_produto_insumo` > '0' AND pa.ativo = '1' AND pa.operacao_custo = '1' 
                        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                        INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                        WHERE fpi.`id_fornecedor` = '$id_fornecedor' 
                        AND fpi.`ativo` = '1' LIMIT 1 ";
                $campos_fator = bancos::sql($sql);
                if(count($campos_fator) > 0) $fator_margem_lucro = $campos_fator[0]['fator_margem_lucro_pa'];
                $preco_liq_fat_custo = custos::procurar_fornecedor_default_revenda($id_produto_acabado, '', 1, '', $cliente);
            }
            //Busco o ICMS e a Redução da Classificação Fiscal do Produto p/ o Estado de SP ...
            $sql = "SELECT icms, reducao 
                    FROM `icms` 
                    WHERE `id_uf` = '1' 
                    AND `ativo` = '1'
                    AND `id_classific_fiscal` = '$id_classific_fiscal' LIMIT 1 ";
            $campos_temp = bancos::sql($sql);
            if(count($campos_temp) > 0) {
                $icms_cf_uf_sp              = $campos_temp[0]['icms'];
                $reducao_base_calc_uf_sp    = $campos_temp[0]['reducao'];
            }else {
                $icms_cf_uf_sp              = 0;
                $reducao_base_calc_uf_sp    = 0;
            }
            $ICMS_SP = ($icms_cf_uf_sp) * (100 - $reducao_base_calc_uf_sp) / 100;
            if($trading == 1) {
                $desc_sgd_icms = $ICMS_SP;
            }else {
                if(strtoupper($campos[0]['nota_sgd']) == 'S') {//SGD ...
                    $desc_sgd_icms = $ICMS_SP;//Ele é igual pq terá desconto integral ...
                }else {
                    $sql = "SELECT icms, reducao 
                            FROM `icms` 
                            WHERE `id_uf` = '$id_uf_cliente' 
                            AND `ativo` = '1' 
                            AND `id_classific_fiscal` = '$id_classific_fiscal' LIMIT 1 ";
                    $campos_temp = bancos::sql($sql);
                    if(count($campos_temp) > 0) {
                        $icms_cf_uf_cliente             = $campos_temp[0]['icms'];
                        $reducao_base_calc_uf_cliente	= $campos_temp[0]['reducao'];
                    }else {
                        $icms_cf_uf_cliente             = 0;
                        $reducao_base_calc_uf_cliente	= 0;
                    }
                    $desc_sgd_icms = $ICMS_SP - ($icms_cf_uf_cliente) * (100 - $reducao_base_calc_uf_cliente) / 100;
                }
            }
            //Caso o Cliente for Estrangeiro, Então divido pelo Dólar Exportação ...
            if($id_pais != 31) $preco_liq_fat_custo/= genericas::moeda_dia('dolar');
            if(strtoupper($campos[0]['nota_sgd']) == 'S' || $trading == 1 || $id_pais != 31) {//SGD ...
                $outros_impostos_federais = genericas::variavel(34);//Pis + cofins + csll + ir + refiz
            }else {
                $outros_impostos_federais = 0;//Pis + cofins + csll + ir + refiz
            }
            /*Este Preço Custo ML Padrão, é o Custo descontado a diferença do ICMS e SGD/UF, impostos federais 
            e Diferença de Comissão, e acrescido da Taxa Financeira do Prazo Médio do ORC ...*/
            $custo_margem_lucro_padrao = $preco_liq_fat_custo * (1 + $tx_financeira / 100) * (1 - $desc_sgd_icms / 100) * (1 - $outros_impostos_federais / 100);
            if($fator_margem_lucro == 0 || empty($fator_margem_lucro)) $fator_margem_lucro = 1;

            $custo_margem_lucro_zero_provisorio = $custo_margem_lucro_padrao / $fator_margem_lucro;
            if($custo_margem_lucro_zero_provisorio == 0) $custo_margem_lucro_zero = 1;
            $margem_lucro_provisoria = round(($preco_liq_final / $custo_margem_lucro_zero_provisorio - 1) * 100, 1);
            
            if($campos[0]['queima_estoque'] == 'S') {//Se esse item do ORC estiver em Queima ... 
                $comissao_extra = genericas::variavel(46);//Taxa Fixa de Comissão Extra ...
            }else {//Se esse item de ORC não está em Queima ...
                //Aqui eu busco a Comissão Extra do Grupo do PA, se não tiver expirado a Data de Emissão do ORC ... ...
                $sql = "SELECT ged.margem_lucro_minima, ged.comissao_extra 
                        FROM `orcamentos_vendas` ov 
                        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_orcamento_venda = ov.id_orcamento_venda 
                        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado AND pa.id_produto_acabado = '$id_produto_acabado' 
                        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                        WHERE ov.id_orcamento_venda = '$id_orcamento_venda' 
                        AND (ov.data_emissao <= ged.data_limite) LIMIT 1 ";
                $campos_comissao_extra = bancos::sql($sql);
                if(count($campos_comissao_extra) == 1) {//Se estiver tudo ok então ...
                    /*Tem agora uma nova Regra: Se a ML Provisória for abaixo que até 10% 
                    da ML Mínima é concedido a Comissão Extra - limitado ao mínimo de 45% 
                    p/ pagar a Comissão Extra ...*/
                    $margem_lucro_minima = (0.90 * $campos_comissao_extra[0]['margem_lucro_minima']);
                    if($margem_lucro_minima < 45)   $margem_lucro_minima = 45;
                    if($margem_lucro_provisoria > $margem_lucro_minima) {
                        $comissao_extra = $campos_comissao_extra[0]['comissao_extra'];
                    }else {//Do contrário não concedo nada, porque o vendedor está fazendo donativo da Mercadoria ...
                        $comissao_extra = 0;
                    }
                }
            }
            //Sempre pego essa Coluna pq nosso Custo aqui já inclui os 3% de encargos ...
            $sql = "SELECT `base_comis_dentro_sp` 
                    FROM `novas_comissoes_margens_lucros` 
                    WHERE `margem_lucro` < '$margem_lucro_provisoria' 
                    ORDER BY `base_comis_dentro_sp` DESC LIMIT 1 ";
            $campos_margem = bancos::sql($sql);
            if(count($campos_margem) > 0) $comissao_provisao  = $campos_margem[0]['base_comis_dentro_sp'] + $comissao_extra;
            /*Essa diferença de Comissão, é a Percentagem da Comissão que Ultrapassa os 3% que é a percentagem que 
            consideramos inclusa nos nossos custos, acrescida de 50% que são os encargos dos funcionários ou valor 
            a maior dos autônomos ... */
            $diferenca_comissao         = ($comissao_provisao - 3) * 1.5;
            $margem_lucro               = $margem_lucro_provisoria - $diferenca_comissao;
            $custo_margem_lucro_zero    = $preco_liq_final / (1 + $margem_lucro / 100);
            
            $margem_lucro       = number_format($margem_lucro, 1, ',', '.');
            $margem_lucro_desc  = $margem_lucro;
            return array($margem_lucro, $margem_lucro_desc, $custo_margem_lucro_zero);
        }
    }

	/* Função cujo objetivo é verificar se o componente com o qual está sendo inserido já não está selecionado 
	como produto acabado ...*/
	function vasculhar_pa($id_produto_acabado_principal, $id_produto_acabado_corrent) {//$id_produto_acabado_corrent=>quero incluir
		//Aqui eu verifico de cara se o produto da combo é igual ao q está embaixo
		if($id_produto_acabado_corrent == $id_produto_acabado_principal) {
			return 1;//significa q nao pode ser atrelado
		}else { //Não é igual
			/* Busco aqui o id_produto_acabado_custo referente ao produto q está no loop,
			para poder saber se neste existem outros produtos acabados atrelados*/
			$sql = "Select pac.id_produto_acabado_custo 
					from produtos_acabados_custos pac 
					inner join produtos_acabados pa on pa.operacao_custo = pac.operacao_custo 
					where pac.id_produto_acabado = '$id_produto_acabado_corrent' limit 1 ";
			$campos = bancos::sql($sql);
			$linhas = count($campos);
			if($linhas > 0) {//Entra somente se for maior q 0
				$id_produto_acabado_custo_loop = $campos[0]['id_produto_acabado_custo'];
				//Verifica se o produto_acabado_custo possui produtos acabados atrelados
				$sql = "Select id_produto_acabado 
						from pacs_vs_pas 
						where `id_produto_acabado_custo` = '$id_produto_acabado_custo_loop' ";
				$campos_atrelados = bancos::sql($sql);
				$linhas_atrelados = count($campos_atrelados); //Entra somente se for maior q 0
				for($i = 0; $i < $linhas_atrelados; $i++) {//Disparo do Loop e acumula os PA no array ...
					echo "<br>".$campos_atrelados[$i]['id_produto_acabado']."==".$id_produto_acabado_principal;
					if($campos_atrelados[$i]['id_produto_acabado'] == $id_produto_acabado_principal) {
						return 1;//significa q nao pode ser atrelado
					}else { //Não é igual
						$retorno = custos::vasculhar_pa($id_produto_acabado_principal, $campos_atrelados[$i]['id_produto_acabado']);
						if($retorno == 1) {
							return 1;
							/*Existe este macete pois a funcao retonava NULL da subfuncao e parava o código sozinho 
								dai ele não lia até o fim da função. Nunca tirar este macete desta funcao.
							*/
						}
					}
				}
			}
		}
	}
}
?>