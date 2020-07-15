<?
///=========>>>>>>>>>  Procurar por $taxa_financeira_vendas para verificar a / 3  por exporta��o 
if(!class_exists('comunicacao'))    require 'comunicacao.php';//CASO EXISTA EU DESVIO A CLASSE ...
if(!class_exists('genericas'))      require 'genericas.php';//CASO EXISTA EU DESVIO A CLASSE ...
require 'variaveis/intermodular.php';

class custos extends bancos {
    //Etapa que calcula o custo da embalagem, com base na lista de preco do ultimo pedido ...
    function etapa1($id_produto_acabado, $fator_custo_etapa_1_3 = 1) {
        $sql = "SELECT ppe.`id_produto_insumo` 
                FROM `pas_vs_pis_embs` ppe 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ppe.`id_produto_acabado` 
                WHERE ppe.`id_produto_acabado` = '$id_produto_acabado' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) $total_embalagem+= self::custo_embalagem($campos[$i]['id_produto_insumo'], $id_produto_acabado) * $fator_custo_etapa_1_3;
        return $total_embalagem;
    }

    function etapa2($id_produto_acabado, $operacao_custo) {
        global $peso_aco_kg;//Essa vari�vel est� definida como Global, porque a mesma � utilizada em outras etapas, exemplo Etapa 5 ...
        //Busca de alguns dados na 2� Etapa do Custo ...
        $sql = "SELECT `id_produto_insumo`, `peca_corte`, `qtde_lote`, `comprimento_1`, `comprimento_2`, 
                `comprimento_barra` 
                FROM `produtos_acabados_custos` 
                WHERE `id_produto_acabado` = '$id_produto_acabado' 
                AND `operacao_custo` = '$operacao_custo' LIMIT 1 ";
        $campos = bancos::sql($sql);
        
        //Se o Lote for maior do q 1000 pegar outro �ndice de vari�vel p/ o c�lculo ...
        $fator_custo_etapa_2 = ($campos[0]['qtde_lote'] > 1000) ? genericas::variavel(18) : genericas::variavel(11);
        
        //Busco dados de Densidade do A�o se existir Produto Insumo "Mat�ria Prima" na 2� Etapa do Custo ...
        $sql = "SELECT `densidade_aco` 
                FROM `produtos_insumos_vs_acos` 
                WHERE `id_produto_insumo` = '".$campos[0]['id_produto_insumo']."' LIMIT 1 ";
        $campos_pi  = bancos::sql($sql);
        $densidade  = (count($campos_pi) == 0) ?  0 : $campos_pi[0]['densidade_aco'];
        $preco      = self::preco_custo_pi($campos[0]['id_produto_insumo']);
        
        $comprimento_total                      = ($campos[0]['comprimento_1'] + $campos[0]['comprimento_2']) / 1000 * 1.05;
        $quantidade_barras                      = intval($campos[0]['qtde_lote'] * (($campos[0]['comprimento_1'] + $campos[0]['comprimento_2']) / $campos[0]['pecas_corte']) / $campos[0]['comprimento_barra']) + 1;//Somo + 1 porque se der 1,3 por exemplo teremos que usar 2 barras ...
        $comprimento_peca_usando_todas_barras   = $campos[0]['comprimento_barra'] * $quantidade_barras / $campos[0]['qtde_lote'];

        if($comprimento_total < ($comprimento_peca_usando_todas_barras / 1000)) {//Essa divis�o por 1000 � p/ convertermos em Milimetros em Metros ...
            $comprimento_total = $comprimento_peca_usando_todas_barras / 1000;
        }

        $peca_corte     = ($campos[0]['peca_corte'] == 0) ? 1 : $campos[0]['peca_corte'];
        $peso_aco_kg    = round($densidade * $comprimento_total / $peca_corte, 4);
        return ($peso_aco_kg * $preco * $fator_custo_etapa_2);
    }

    //Etapa que atrela as demais materia prima e outros Produto Insumo ...
    function etapa3($id_produto_acabado, $fator_custo_etapa_1_3, $operacao_custo) {
        $sql = "SELECT DISTINCT(pp.`id_produto_insumo`), pp.`qtde` 
                FROM `produtos_acabados_custos` pac 
                INNER JOIN `pacs_vs_pis` pp ON pp.`id_produto_acabado_custo` = pac.`id_produto_acabado_custo` 
                WHERE pac.`operacao_custo` = '$operacao_custo' 
                AND pac.`id_produto_acabado` = '$id_produto_acabado' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if(count($linhas) > 0) {////N�o achou Custo deste Produto ...
            for($i = 0; $i < $linhas; $i++) {
                $total+= self::preco_custo_pi($campos[$i]['id_produto_insumo']) * $campos[$i]['qtde'] * $fator_custo_etapa_1_3;
            }
            return $total;
        }else {
            return 0;
        }
    }

    function etapa4($id_produto_acabado, $fator_custo_etapa_4, $operacao_custo, $somente_custo_taxa_estocagem = 'N') {
        //Nesse caso trago somente o Total em R$ dessa M�quina "TX FINANC ESTOCAGEM" p/ essa Etapa ...
        if($somente_custo_taxa_estocagem == 'S') $condicao_custo_taxa_estocagem = " AND pm.`id_maquina` = '40' ";
        //N�o � preciso calcular a maquina pois ela faz isto quando o func ou a maquina sofre altera��es ...
        $sql = "SELECT (SUM(pm.`tempo_hs` * m.`custo_h_maquina`) * $fator_custo_etapa_4) / pac.`qtde_lote` AS custo_maquina 
                FROM `produtos_acabados_custos` pac 
                INNER JOIN `pacs_vs_maquinas` pm ON pm.`id_produto_acabado_custo` = pac.`id_produto_acabado_custo` $condicao_custo_taxa_estocagem 
                INNER JOIN `maquinas` m ON m.id_maquina = pm.id_maquina 
                WHERE pac.`operacao_custo` = '$operacao_custo' 
                AND pac.`id_produto_acabado` = '$id_produto_acabado' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas > 0) {
            if(empty($campos[0]['custo_maquina']) || is_null($campos[0]['custo_maquina'])) {
                return 0;
            }else {
                /*P/ esse caso espec�fico atribu�mos os Impostos Federais devido fazermos uma Conta 
                que utilizar� todas as Etapas e essa j� tem embutida esses Impostos Federais ...*/
                if($somente_custo_taxa_estocagem == 'S') {
                    /*$outros_impostos_federais   = genericas::variavel(34);
                    $custo_bancario             = self::custo_bancario($id_produto_acabado);

                    return ($campos[0]['custo_maquina'] / (1 - $outros_impostos_federais / 100));
                    return ($campos[0]['custo_maquina'] / (1 - $outros_impostos_federais / 100) / (1 - $custo_bancario / 100));*/
                    echo 'CHAMAR D�RCIO OU NETTO !';
                    exit;
                }else {
                    return $campos[0]['custo_maquina'];
                }
            }
        }else {
            return 0;
        }
    }

    //C�lcula o Custo do Tratamento T�rmico ...
    function etapa5($id_produto_acabado, $peso_aco_kg, $fator_custo_etapa_5_6, $operacao_custo, $qtde_orcamento = 0) {
        $sql = "SELECT ppt.`fator`, ppt.`id_produto_insumo`, ppt.`peso_aco_manual`, ppt.`lote_minimo_fornecedor`, 
                ppt.`peso_aco`, pac.`qtde_lote` 
                FROM `produtos_acabados_custos` pac 
                INNER JOIN `pacs_vs_pis_trat` ppt ON ppt.`id_produto_acabado_custo` = pac.`id_produto_acabado_custo` 
                WHERE pac.`operacao_custo` = '$operacao_custo' 
                AND pac.`id_produto_acabado` = '$id_produto_acabado' ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	if($linhas > 0) {
            for($i = 0; $i < $linhas; $i++) {
                if($campos[$i]['peso_aco_manual'] == 1) {//Manual - foi marcado o Checkbox da 5� Etapa ...
                    $peso_aco_kg_temp   = $campos[$i]['peso_aco'];
                    $sub_total          = $peso_aco_kg_temp * self::preco_custo_pi($campos[$i]['id_produto_insumo']) * $fator_custo_etapa_5_6;
                }else {//Est� na forma autom�tica ...
                    $peso_aco_kg_temp   = $peso_aco_kg / 1.05;
                    $sub_total          = $campos[$i]['fator'] * self::preco_custo_pi($campos[$i]['id_produto_insumo']) * $peso_aco_kg_temp * $fator_custo_etapa_5_6;
                }
                if($campos[$i]['lote_minimo_fornecedor'] == 1) {// Se estiver setado ou 1 acionar o calculo abaixo de lote minimo por fornecedor default por pedido
                    $id_fornecedor_default 	= self::preco_custo_pi($campos[0]['id_produto_insumo'], 0, 1);
                    //Aqui eu pego o Lote M�nimo do Fornecedor Default encontrado atrav�s do PI na lista de Pre�o de Compras ...
                    $sql = "SELECT f.`razaosocial`, fpi.`lote_minimo_reais` 
                            FROM `fornecedores` f 
                            INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_fornecedor` = f.`id_fornecedor` AND fpi.`id_produto_insumo` = '".$campos[0]['id_produto_insumo']."' 
                            WHERE f.`id_fornecedor` = '$id_fornecedor_default' LIMIT 1 ";
                    $campos_fornec      = bancos::sql($sql);
                    $lote_minimo_reais  = $campos_fornec[0]['lote_minimo_reais']; //lote minimo do fornecedor default
                    $lote_custo         = ($qtde_orcamento == 0) ? $campos[$i]['qtde_lote'] : $qtde_orcamento;
                    $preco_peca_corte   = $lote_minimo_reais / $lote_custo;
                    $total_pecas_s_fator= $sub_total / $fator_custo_etapa_5_6;
                    if($total_pecas_s_fator < $preco_peca_corte) $sub_total = $preco_peca_corte * $fator_custo_etapa_5_6;
                }
                $total+= $sub_total;
            }
            return $total;
	}else {
            return 0;
	}
    }

    //Calcula o Custo da Usinagem ...
    function etapa6($id_produto_acabado, $fator_custo_etapa_5_6, $operacao_custo) {
        $sql = "SELECT ppu.`qtde`, ppu.`id_produto_insumo` 
                FROM `produtos_acabados_custos` pac 
                INNER JOIN `pacs_vs_pis_usis` ppu ON ppu.`id_produto_acabado_custo` = pac.`id_produto_acabado_custo` 
                WHERE pac.`operacao_custo` = '$operacao_custo' 
                AND pac.`id_produto_acabado` = '$id_produto_acabado' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas > 0) {
            for($i = 0; $i < $linhas; $i++) $total+= $campos[$i]['qtde'] * self::preco_custo_pi($campos[$i]['id_produto_insumo']) * $fator_custo_etapa_5_6;
            return $total;
        }else {
            return 0;
        }
    }
    
    /*Observa��o muito importante: quando o "$id_produto_acabado_item" � diferente de Zero ent�o o sistema faz o 
    c�lculo de Custo da 7� Etapa p/ o Item espec�fico passado por par�metro, do contr�rio se for passado igual 
    a Zero o sistema faz o c�lculo de Custo da 7� Etapa de todos os Itens ...

    *** Sempre passo no $fator_custo_etapa_1_3 valor como '1' pq j� vem valor de fator de Outro Custo desse PA ...

    //Calcula o custo do PA / Componente ...*/
    function etapa7($id_produto_acabado_item, $id_produto_acabado_custo, $fator_custo_etapa_1_3 = 1, $qtde_orcamento, $id_uf_cliente = 0, $nota_sgd) {
        if(!empty($id_produto_acabado_item)) $condicao = " AND pp.`id_produto_acabado` = '$id_produto_acabado_item' LIMIT 1 ";
        
        $sql = "SELECT pp.`id_produto_acabado`, pp.`qtde`, pa.`operacao_custo` 
                FROM `pacs_vs_pas` pp 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pp.`id_produto_acabado` 
                WHERE pp.`id_produto_acabado_custo` = '$id_produto_acabado_custo' $condicao ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas > 0) {
            for($i = 0; $i < $linhas; $i++) {
                if($campos[$i]['operacao_custo'] == 0) {//PA Industrial ...
                    $total+= $campos[$i]['qtde'] * (self::todas_etapas($campos[$i]['id_produto_acabado'], 0, 0, $qtde_orcamento));//passo 0 pq o OC � do tipo industrializado ...
                }else {//PA Revenda ...
                    $valor_revenda              = self::pipa_revenda($campos[$i]['id_produto_acabado'], 0, $id_uf_cliente, $nota_sgd);//0 � para n�o Somar Embalagem ...
                    
                    /*Estamos desembutindo os Impostos Federais porque o Custo Revenda j� vem com esses Impostos 
                    naturalmente e que ser�o reembutidos no no calculo Final de Todas as Etapas - caminho Ind ...*/
                    $outros_impostos_federais   = genericas::variavel(34);
                    $custo_bancario             = self::custo_bancario($campos[$i]['id_produto_acabado']);
                    
                    $valor_revenda              = $valor_revenda * (1 - $outros_impostos_federais / 100) * (1 - $custo_bancario / 100);
                    
                    //Estamos desembutindo a Taxa Financeira de Vendas ...
                    $taxa_financeira_vendas = genericas::variaveis('taxa_financeira_vendas') / 100 + 1;
                    $valor_revenda/=       $taxa_financeira_vendas;

                    $fator_margem_lucro     = genericas::variavel(22);//Margem de Lucro PA Industrial
                    /*Aonde que essa vari�vel "$GLOBALS['fator_margem_lucro_pa']" � criada ou abastecida, n�s sabemos
                    que ela vem da lista de Pre�o de Compras de cada PI ???*/
                    if($GLOBALS['fator_margem_lucro_pa'] == 0) $GLOBALS['fator_margem_lucro_pa'] = 0.01;//macete p/ n�o dar erro de Divis�o por Zero ...
                    //Este c�lculo � pq tem um PA(Rev) atrelado na 7� etapa de um PA(Ind) ...
                    $valor_revenda          = ($valor_revenda / $GLOBALS['fator_margem_lucro_pa']) * $fator_margem_lucro;
                    $total+= $campos[$i]['qtde'] * $valor_revenda;
                }
            }
            return $total;
        }else {
            return 0;
        }
    }
    
    //$operacao_custo 0 -> Industrializado, 1-> revenda ...
    function todas_etapas($id_produto_acabado, $operacao_custo, $somar_etapa1 = 1, $qtde_orcamento = 0, $id_uf_cliente, $nota_sgd) {
        //Aplica id_uf = 1 "S�o Paulo", porque o nosso Custo Base � para esta UF ...
        $sql = "SELECT pa.`referencia`, pa.`operacao_custo_sub`, pac.*, icms.`icms`, icms.`reducao` 
                FROM `produtos_acabados_custos` pac 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
                INNER JOIN `icms` ON icms.`id_classific_fiscal` = f.`id_classific_fiscal` AND icms.`id_uf` = '1' 
                WHERE pac.`operacao_custo` = '$operacao_custo' 
                AND pac.`id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        $campos                     = bancos::sql($sql);
        $icms_c_red                 = $campos[0]['icms'] * (1 - $campos[0]['reducao'] / 100);
        $outros_impostos_federais   = genericas::variavel(34);
        $custo_bancario             = self::custo_bancario($id_produto_acabado);
        
        if(count($campos) == 0) {//N�o achou o Custo do PA ...
            return 0;
        }else {
            $id_produto_acabado_custo   = $campos[0]['id_produto_acabado_custo'];
            $fator_custo_etapa_1_3      = genericas::variavel(12);
            $fator_custo_etapa_4        = genericas::variavel(9);
            $fator_custo_etapa_5_6      = genericas::variavel(10);
            /***********************************Observa��o Crucial***********************************
            Declarei como constante o valor retornado de cada Etapa do Custo at� a Etapa 6, pois n�o posso perder 
            o 1� valor Total retornardo de cada etapa "n�o sei porque o PHP faz isso e n�o retorna o valor da fun��o 
            espec�fica", j� no caso da 7� Etapa tem que ser retornado uma valor cumulativo, pois ela � uma fun��o 
            recursiva ...*/
            /***************************************************************************************/
            $etapa1 = self::etapa1($id_produto_acabado, $fator_custo_etapa_1_3);
            define('etapa1', $etapa1);
            $GLOBALS['etapa1'] = constant('etapa1');
            
            $etapa2 = self::etapa2($id_produto_acabado, $operacao_custo);
            define('etapa2', $etapa2);
            $GLOBALS['etapa2'] = constant('etapa2');
            
            $etapa3 = self::etapa3($id_produto_acabado, $fator_custo_etapa_1_3, $operacao_custo);
            define('etapa3', $etapa3);
            $GLOBALS['etapa3'] = constant('etapa3');
            
            $etapa4 = self::etapa4($id_produto_acabado, $fator_custo_etapa_4, $operacao_custo);
            define('etapa4', $etapa4);
            $GLOBALS['etapa4'] = constant('etapa4');
            
            $etapa5 = self::etapa5($id_produto_acabado, $GLOBALS['peso_aco_kg'], $fator_custo_etapa_5_6, $operacao_custo, $qtde_orcamento);
            define('etapa5', $etapa5);
            $GLOBALS['etapa5'] = constant('etapa5');
            
            $etapa6 = self::etapa6($id_produto_acabado, $fator_custo_etapa_5_6, $operacao_custo);
            define('etapa6', $etapa6);
            $GLOBALS['etapa6'] = constant('etapa6');
            
            $etapa7 = self::etapa7('', $id_produto_acabado_custo, '', $qtde_orcamento, $id_uf_cliente, $nota_sgd);
            $GLOBALS['etapa7'] = $etapa7;
            
            if($somar_etapa1 == 1) {//Acrescento no Total do Custo a Etapa 1 ...
                if(!class_exists('intermodular'))   require 'intermodular.php';//CASO EXISTA EU DESVIO A CLASSE ...
                /*Somente nessa situa��o, verifico se o PA que foi passado por par�metro nessa fun��o possui algum item 
                de sua 7� Etapa que esteja com a Marca��o de "Usar este Lote p/ ORC" e que tenha a sua OC igual ao do PA 
                passado por par�metro ...*/
                $sql = "SELECT `id_produto_acabado` 
                        FROM `pacs_vs_pas` 
                        WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' 
                        AND `usar_este_lote_para_orc` = 'S' LIMIT 1 ";
                $campos_etapa7 = bancos::sql($sql);
                /*Se encontrou 1 item na 7� Etapa com a Marca��o de "Usar este Lote p/ ORC", ent�o eu pego essa Qtde de Lote
                do Custo do PA que foi encontrado, pois a mesma ser� utilizada nos c�lculos abaixo ...*/
                if(count($campos_etapa7) == 1) {
                    $sql = "SELECT `qtde_lote` 
                            FROM `produtos_acabados_custos` 
                            WHERE `id_produto_acabado` = '".$campos_etapa7[0]['id_produto_acabado']."' 
                            AND `operacao_custo` = '$operacao_custo' LIMIT 1 ";
                    $campos_qtde_lote   = bancos::sql($sql);
                    $qtde_lote_custo    = $campos_qtde_lote[0]['qtde_lote'];
                }else {//Se n�o encontrou nada, utiliza o Lote do Custo do PA passado por par�metro nessa fun��o ...
                    $qtde_lote_custo    = $campos[0]['qtde_lote'];
                }
                $valores                            = intermodular::calculo_producao_mmv_estoque_pas_atrelados($id_produto_acabado);
                
                //Estamos estimando que o Maior Lote de Produ��o ser� consumido em no m�ximo X meses ...
                $qtde_meses_mmv_maximo_lote_custo   = genericas::variavel(82);
                $mmv_custo                          = $qtde_lote_custo / $qtde_meses_mmv_maximo_lote_custo;
                $maior_mmv_para_utilizar_no_custo   = ($mmv_custo > $valores['total_mmv_pas_atrelados']) ? $mmv_custo : $valores['total_mmv_pas_atrelados'];
               
                /*Somente se a OC do PA = "Industrial" e a "Sub-OC" tamb�m, que eu c�lculo a vari�vel 
                $qtde_meses_estocagem_lote_custo porque isto representa que este PA � feito 100% 
                internamente ...*/
                if($operacao_custo == 0 && $campos[0]['operacao_custo_sub'] == 0) {
                    $qtde_meses_estocagem_lote_custo    = $qtde_lote_custo / $maior_mmv_para_utilizar_no_custo;
                    /*Quando o PA � Ind Rev, significa que este � um PA adquirido, ent�o o nosso calculo 
                    de Taxa de Estocagem � feito pela ML Estimada, ou seja n�o podemos usar essa vari�vel 
                    $qtde_meses_estocagem_lote_custo, porque sen�o aplicar�amos 2 vezes a Taxa de Estocagem ...*/
                }else {
                    $qtde_meses_estocagem_lote_custo    = 0;
                }
                $taxa_financeira_vendas             = genericas::variavel(16);//Taxa Financeira de Vendas ...
                //Zero essa Taxa Financeira p/ ESPs porque n�s n�o estocamos Produtos Especiais ...
                $taxa_financeira_mensal_estocagem   = ($campos[0]['referencia'] == 'ESP') ? 0 : (0.70 * $taxa_financeira_vendas);
                /*� uma cobran�a feita em cima do PA que foi fabricado e que est� parado no Estoque pois estamos pagando 
                Juros p/ o Banco de Empr�stimo ...
                *Dividimos a $qtde_meses_estocagem_lote por 2, p/ achar o Prazo M�dio de Estocagem e somamos + 1 m�s 
                que � o per�odo m�dio que a Pe�a demora p/ ser Produzida ...*/
                $taxa_estocagem_pa_custo            = (pow(1 + $taxa_financeira_mensal_estocagem / 100, $qtde_meses_estocagem_lote_custo / 2 + 1) - 1) * 100 + $taxa_financeira_mensal_estocagem;

                /*Essa taxa n�o leva em Conta a Taxa de 1 m�s de Produ��o ...

                Se o PA = Componente exclu�mos essa taxa de Estocagem ao atrel�-lo na 
                7� Etapa de outro PA ...*/
                $taxa_financeira_media_estocagem_pa = $taxa_estocagem_pa_custo - $taxa_financeira_mensal_estocagem;
                $fator_correcao_qtde_orc_vs_custo   = $qtde_orcamento / $qtde_lote_custo;
                /*Aqui corrigimos a Taxa de Estocagem usando como base a fra��o do Lote do ORC vs o Lote do Custo, onde a 
                Taxa n�o pode ser menor do que uma Taxa de Estocagem do PA que � a Taxa Financeira referente 1 m�s que � o 
                prazo m�dio de produ��o do PA ...*/
                $taxa_estocagem_pa_orc          = ($qtde_orcamento >= $qtde_lote_custo) ? $taxa_financeira_mensal_estocagem : $taxa_financeira_media_estocagem_pa - $fator_correcao_qtde_orc_vs_custo * $taxa_financeira_media_estocagem_pa + $taxa_financeira_mensal_estocagem;

                $GLOBALS['total_sem_impostos']  = $etapa1 + $etapa2 + $etapa3 + $etapa4 + $etapa5 + $etapa6 + $etapa7;
                
                $GLOBALS['total_sem_impostos']  = $GLOBALS['total_sem_impostos'] / (1 - $outros_impostos_federais / 100) / (1 - $custo_bancario / 100);
                
                if($qtde_orcamento > 0) {
                    $GLOBALS['total_sem_impostos']*= (1 + $taxa_estocagem_pa_orc / 100);
                }else {
                    $GLOBALS['total_sem_impostos']*= (1 + $taxa_estocagem_pa_custo / 100);
                }
                //echo '<br/><br/>MMV ORC => '.$id_produto_acabado.'|'.$qtde_orcamento.'<br/><br/>';
                //echo '<br/><br/>Qtde Lote do Custo => '.$id_produto_acabado.'|'.$qtde_lote_custo.'<br/><br/>';
                //echo '<br/><br/>Fator Correcao Qtde orc vs Custo => '.$id_produto_acabado.'|'.$fator_correcao_qtde_orc_vs_custo.'<br/><br/>';
                //echo '<br/><br/>Qtde Meses Estocagem Lote => '.$id_produto_acabado.'|'.$qtde_meses_estocagem_lote.'<br/><br/>';
                //echo '<br/><br/>Taxa Financeiro Mensal de Estocagem => '.$id_produto_acabado.'|'.$taxa_financeira_mensal_estocagem.'<br/><br/>';
                //echo '<br/><br/>Qtde Or�amento => '.$id_produto_acabado.'|'.$qtde_orcamento.'<br/><br/>';
                //echo '<br/><br/>Taxa Estocagem PA ORC => '.$id_produto_acabado.'|'.$qtde_orcamento.'|'.$taxa_estocagem_pa_orc.'<br/><br/>';
                //echo '<br/><br/>Taxa Estocagem PA CUSTO => '.$id_produto_acabado.'|'.$qtde_orcamento.'|'.$taxa_estocagem_pa_custo.'<br/><br/>';
            }else {//N�o somo a 1� Etapa porque estamos usando esse Custo p/ um PA que est� na 7� Etapa ...
                $GLOBALS['total_sem_impostos'] = $etapa2 + $etapa3 + $etapa4 + $etapa5 + $etapa6 + $etapa7;
            }
            $GLOBALS['total_com_impostos'] = $GLOBALS['total_sem_impostos'] / ((100 - $icms_c_red) / 100);
           
            return $GLOBALS['total_sem_impostos'];//Retorna o valor total sem impostos ...
        }
    }
    
    /*Esse par�metro "$destruir_vetor_pas_atrelados" foi criado somente para evitar transtornos de valores 
    que se acumulam na vari�vel $vetor_pas_atrelados[] somente quando essa fun��o � chamada em Loop ...*/
    function pas_atrelados($id_produto_acabado, $id_unidade) {
        /*Essa variavel esta como global por que tenho que pegar o id PA principal depois vejo os atrelados assim ficar� ordenado ...
        Infelizmente tive que manter essa estrutura do Luis que encontrei no arquivo de Visualizar Estoque, pq sen�o d� erro no Custo*/
        global $vetor_pas_atrelados;
        $vetor_pas_atrelados[]  = $id_produto_acabado;
        $vetor_pas_atrelados    = array_unique($vetor_pas_atrelados);//Retiro os elementos j� existentes no Vetor ...
        
        /*S� retornar� PA(s) atrelados a este PA principal que foi passado por par�metro, desde que sejam 
        do mesmo $id_unidade que foi passado por par�metro ...*/
        if($id_unidade > 0) $condicao_unidade = " AND pa.`id_unidade` = '$id_unidade' ";
        
        /*A partir de agora "08/06/2015", a fun��o tamb�m trar� o PA Principal tamb�m, independente 
        de existir PAs Atrelados a este ...*/
        
        //PA Filho = PA Principal do Custo ...
        //PA Pai = PA atrelado na 7� Etapa do Custo ...
        //Aqui eu verifico a Opera��o de Custo do PA que entrou no Loop ...
        $sql = "SELECT `operacao_custo` 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        $campos         = bancos::sql($sql);
        $operacao_custo = $campos[0]['operacao_custo'];
/*Pegamos o PA Pai passado por par�metro e localizamos todos os id_custos que tenham este PA na S�tima Etapa, 
 com estes id_custos, verificamos todos os id_PA Filhos destes id_custos cuja OC de Custo seja = OC do PA Filho*/
/***********Este PA esta na 7� de Quem - Este PA esta na 7� de Quais PA(s) ???***********/
        $sql = "SELECT pac.`id_produto_acabado` 
                FROM `pacs_vs_pas` pp 
                INNER JOIN `produtos_acabados_custos` pac ON pac.`id_produto_acabado_custo` = pp.`id_produto_acabado_custo` 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` AND pa.`operacao_custo` = pac.`operacao_custo` AND pa.`ativo` = '1' $condicao_unidade 
                WHERE pp.`id_produto_acabado` = '$id_produto_acabado' ";
        $campos_pa = bancos::sql($sql);//pego todos os filho do PA ou seja 7� etapa
        $linhas_pa = count($campos_pa);
        for($i = 0; $i < $linhas_pa; $i++) {
            //Aqui eu verifico se o PA tem detalhes para Explodir Visualiza��o e se o mesmo tamb�m n�o pertence a Fam�lia de Componentes ...
            $sql = "SELECT pa.`explodir_view_estoque` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.`id_familia` NOT IN (23, 24, 25) 
                    WHERE pa.`id_produto_acabado` = '".$campos_pa[$i]['id_produto_acabado']."' LIMIT 1 ";
            $campos_view_pa = bancos::sql($sql);
            //Se sim posso pegar todos os PA relacionados ...
            if(strtoupper($campos_view_pa[0]['explodir_view_estoque']) == 'S') $id_pas_atrelados_view[] = $campos_pa[$i]['id_produto_acabado'];
        }
/*Pegamos todos os Custos deste PA Filho passado por par�metro cuja a OC do Custo seja = a OC do PA. Com estes 
 id_custos verificamos id_PA(s) Pais que est�o na 7� Etapa destes id_custo.*/
/***********Quem tem este PA na 7� Etapa - Quais PA(s) tem este PA em sua 7� Etapa ???***********/
        $sql = "SELECT pp.`id_produto_acabado` 
                FROM `produtos_acabados_custos` pac 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` AND pa.`operacao_custo` = pac.`operacao_custo` AND pa.`ativo` = '1' $condicao_unidade 
                INNER JOIN `pacs_vs_pas` pp ON pp.`id_produto_acabado_custo` = pac.`id_produto_acabado_custo` 
                WHERE pac.`id_produto_acabado` = '$id_produto_acabado' ";
        $campos_pa = bancos::sql($sql);//pego todos os Pais do PA ou seja ele � 7� etapa de outro PA
        $linhas_pa = count($campos_pa);
        for($i = 0; $i < $linhas_pa; $i++) {
            //Aqui eu verifico se o PA tem detalhes para Explodir Visualiza��o e se o mesmo tamb�m n�o pertence a Fam�lia de Componentes ...
            $sql = "SELECT pa.`explodir_view_estoque` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.`id_familia` NOT IN (23, 24, 25) 
                    WHERE pa.`id_produto_acabado` = '".$campos_pa[$i]['id_produto_acabado']."' LIMIT 1 ";
            $campos_view_pa = bancos::sql($sql);
            //Se sim posso pegar todos os PA relacionados ...
            if(strtoupper($campos_view_pa[0]['explodir_view_estoque']) == 'S') $id_pas_atrelados_view[] = $campos_pa[$i]['id_produto_acabado'];
        }
        $linhas_pa_array = count($id_pas_atrelados_view);
        if($linhas_pa_array > 0) {//Existe pelo menos 1 PA atrelado ao que foi passado por par�metro ...
            for($i = 0; $i < $linhas_pa_array; $i++) {
                if(!in_array($id_pas_atrelados_view[$i], $vetor_pas_atrelados)) {
                    $vetor_pas_atrelados[] = $id_pas_atrelados_view[$i];
                    self::pas_atrelados($id_pas_atrelados_view[$i]);
                }
            }
        }
        return $vetor_pas_atrelados;
    }

    //Calcula o custo de cada m�quina ...
    function custos_hora_maquina($id_maquina) {
        //Essas vari�veis ser�o utilizadas mais abaixo ...
        $dias_trab_mes                      = genericas::variavel(13);
        $horas_trab_dia                     = genericas::variavel(14);
        $aumento_sal_provisorio             = genericas::variavel(15);
        $horas_efetivamente_trabalhadas_mes = genericas::variavel(86);
        
        $sql = "SELECT `valor`, `duracao`, `porc_ferramental`, `qtde_maq_vs_func` 
                FROM `maquinas` 
                WHERE `id_maquina` = '$id_maquina' LIMIT 1 ";
	 $campos_maquina = bancos::sql($sql);
	 if(count($campos_maquina) == 1) {//Se achou a m�quina ...
            $valor_maquina      = $campos_maquina[0]['valor'];
            $anos_amortizacao   = $campos_maquina[0]['duracao'];
            $porc_ferramental   = $campos_maquina[0]['porc_ferramental'];
            $qtde_maq_vs_func   = $campos_maquina[0]['qtde_maq_vs_func'];
/*Trago a m�dia Salarial dos Funcion�rios atrelados a m�quina passada por par�metro 
Que trabalh�o atualmente aqui na Empresa ...*/
            $soma_salario_funcionarios = 0;//Essa vari�vel ser� utilizada mais abaixo ...

            $sql = "SELECT f.`tipo_salario`, f.`salario_pd`, f.`salario_pf`, f.`salario_premio` 
                    FROM `maquinas_vs_funcionarios` mf 
                    INNER JOIN `funcionarios` f ON f.`id_funcionario` = mf.`id_funcionario` AND f.`status` < '3' 
                    WHERE mf.`id_maquina` = '$id_maquina' ";
            $campos_funcionarios = bancos::sql($sql);
            $linhas_funcionarios = count($campos_funcionarios);
            for($i = 0; $i < $linhas_funcionarios; $i++) {
                if($campos_funcionarios[$i]['tipo_salario'] == 1) {//Horista ...
                    $soma_salario_funcionarios+= ($campos_funcionarios[$i]['salario_pd'] + $campos_funcionarios[$i]['salario_pf'] + $campos_funcionarios[$i]['salario_premio']) * 220 / $horas_efetivamente_trabalhadas_mes;
                }else {//Mensalista ...
                    $soma_salario_funcionarios+= ($campos_funcionarios[$i]['salario_pd'] + $campos_funcionarios[$i]['salario_pf'] + $campos_funcionarios[$i]['salario_premio']) / $horas_efetivamente_trabalhadas_mes;
                }
            }
            $media_salarial_funcionarios    = ($soma_salario_funcionarios / $linhas_funcionarios);
            $sal_media_maq                  = ($aumento_sal_provisorio / 100 + 1) * $media_salarial_funcionarios / $qtde_maq_vs_func; //exibir na tela esta m�dia
            $divisao                        = $anos_amortizacao * 12 * $dias_trab_mes * $horas_trab_dia;//separei esta parte pois estava dando erro por divisao por zero
            if($divisao == 0)   $divisao = 1;
            $custo_hora_maq                 = ($valor_maquina / ($divisao) * (1 + $porc_ferramental / 100)) + $sal_media_maq * genericas::variavel(63);
            $custo_hora_maq                 = round(round($custo_hora_maq, 2), 3);
            //Atualizo alguns dados da Tabela de M�quinas ...
            $sql = "UPDATE `maquinas` SET `salario_medio` = '$sal_media_maq', `custo_h_maquina` = '$custo_hora_maq' WHERE `id_maquina` = '$id_maquina' LIMIT 1 ";
            bancos::sql($sql);
        }
    }

    //Esta fun��o est� sendo chamada pelo cadastro de funcionario e o variaveis ...
    function localizar_maquina($id_funcionario = 'todos') {
        if($id_funcionario <> 'todos') {//Localizo todas as m�quinas que s�o operadas pelo usu�rio passado por par�metro ...
            $sql = "SELECT DISTINCT(m.id_maquina) 
                    FROM `funcionarios` f 
                    INNER JOIN `maquinas_vs_funcionarios` mf ON mf.id_funcionario = f.id_funcionario 
                    INNER JOIN `maquinas` m ON m.id_maquina = mf.id_maquina 
                    WHERE f.`id_funcionario` = '$id_funcionario' ";
            $campos_maquina = bancos::sql($sql);
            $linhas_maquina = count($campos_maquina);
            for($i = 0; $i < $linhas_maquina; $i++) self::custos_hora_maquina($campos_maquina[$i]['id_maquina']);
	 }else {//Aqui n�o foi passado nenhum Funcion�rio por par�metro, ent�o localizo todas as m�quinas cadastradas ...
            $sql = "SELECT id_maquina 
                    FROM `maquinas` 
                    WHERE `ativo` = '1' ";
            $campos_maquina = bancos::sql($sql);
            $linhas_maquina = count($campos_maquina);
            for($i = 0; $i < $linhas_maquina; $i++) self::custos_hora_maquina($campos_maquina[$i]['id_maquina']);
        }
    }

    /*Essa fun��o traz o Pre�o de Compra "Lista do Fornecedor" e calculamos o Pre�o Custo ML Min Revenda, em cima deste 
    calculado somamos o Custo Industrial e acrescemos os Impostos Federais ...
    
    Esse 4� par�metro $trazer_pco_compra_no_orc -> representa qual pre�o de Lista que queremos que seja retornado dentro 
    do Or�amento e esse ser� Nacional ou Internacional de acordo com o Pa�s do Cliente ou somente Internacional, se marcarmos 
    dentro o Or�amento a op��o de Compra como Export ...*/
    function preco_custo_ml_min_revenda($id_fornecedor, $id_produto_insumo, $somar_etapa1 = 1, $trazer_pco_compra_no_orc = '') {
        //Vari�veis que ser�o utilizadas + abaixo ...
        $outros_impostos_federais   = genericas::variavel(34);
        
        $sql = "SELECT f.id_pais, f.id_fornecedor, f.razaosocial, fpi.tp_moeda, fpi.preco, 
                fpi.preco_exportacao, fpi.preco_faturado, fpi.icms, fpi.reducao, fpi.preco_faturado_export, 
                fpi.valor_moeda_compra, fpi.fator_margem_lucro_pa 
                FROM `fornecedores_x_prod_insumos` fpi 
                INNER JOIN `fornecedores` f ON f.id_fornecedor = fpi.id_fornecedor 
                WHERE fpi.`id_fornecedor` = '$id_fornecedor' 
                AND fpi.`id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	if($linhas > 0) {
            $id_fornecedor          = $campos[0]['id_fornecedor'];//id_fornecedor 146=> hispania ...
            $id_pais                = $campos[0]['id_pais'];//Esta fora por causa que ele � igual no loop ...
            $fator_importacao       = genericas::variaveis('fator_importacao'); ///falta trazer este valor
            $taxa_financeira_vendas = genericas::variaveis('taxa_financeira_vendas') / 100 + 1;
            
            /*******************************************************************************/
            /*****************Procedimento para se calcular e trazer o Pre�o****************/
            /*******************************************************************************/
            if($id_pais == 31) {//Fornecedor do Brasil ...
                //C�lculo do Pre�o Nacional, moeda em R$ ...
                $preco_venda_fat_nac_min_rs     = $campos[0]['fator_margem_lucro_pa'] * $campos[0]['preco_faturado'] * $taxa_financeira_vendas;
                //C�lculo do Pre�o Internacional, moeda Estrangeira => D�lar ou Euro ...
                $preco_venda_fat_inter_min_rs   = ($campos[0]['tp_moeda'] == 1 || $campos[0]['tp_moeda'] == 2) ? ($campos[0]['fator_margem_lucro_pa'] * $campos[0]['preco_faturado_export'] * $taxa_financeira_vendas * $campos[0]['valor_moeda_compra']) : 0;
            }else {//Fornecedor de Pa�s Internacional ...
                $preco_venda_fat_nac_min_rs     = 0;//Nessa situa��o n�o existe Pre�o Nacional em R$ ...
                //C�lculo do Pre�o Internacional, moeda Estrangeira ...
                $fator_importacao   = genericas::variaveis('fator_importacao');
                $moeda_custo        = genericas::variaveis('moeda_custo');

                if($campos[0]['tp_moeda'] == 1) {//D�lar ...
                    $preco_venda_fat_inter_min_rs = $campos[0]['fator_margem_lucro_pa'] * $campos[0]['preco_faturado_export'] * $taxa_financeira_vendas * $moeda_custo['dolar_custo'] * $fator_importacao;
                }else if($campos[0]['tp_moeda'] == 2) {//Euro ...
                    $preco_venda_fat_inter_min_rs = $campos[0]['fator_margem_lucro_pa'] * $campos[0]['preco_faturado_export'] * $taxa_financeira_vendas * $moeda_custo['euro_custo'] * $fator_importacao;
                }
            }
            /*******************************************************************************/
            //Verifico se esse PI � um PA, ou seja PIPA ...
            $sql = "SELECT ged.`id_gpa_vs_emp_div`, ged.`id_grupo_pa`, pi.`discriminacao`, pa.`id_produto_acabado` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = pi.`id_produto_insumo` 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    WHERE pi.`id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
            $campos_pipa    = bancos::sql($sql);
            if(count($campos_pipa) > 0) {
                /***********Rotina simples que bloqueia o Custo, n�o me lembro ao certo agora ??? D�rcio***********/
                if($campos[0]['fator_margem_lucro_pa'] == '0.00') {
                    $assunto = 'M�dulo de Custo - '.date('d-m-Y H:i:s');
                    //Aqui eu Bloqueio o Custo desse PA ...
                    $sql = "UPDATE `produtos_acabados` SET `status_custo` = '0' WHERE `id_produto_acabado` = '".$campos_pipa[0]['id_produto_acabado']."' LIMIT 1 ";
                    bancos::sql($sql);
                    
                    $mensagem = "O sistema detectou que o produto (".$campos_pipa[0]['discriminacao'].") do fornecedor (".$campos[0]['razaosocial'].") est� com a margem de lucro zerada e foi bloqueado automaticamente.";
                    comunicacao::email('ERP - GRUPO ALBAFER', $margem_de_lucro_zerada_produto_bloqueado, '', $assunto, $mensagem);
                    $GLOBALS['fator_margem_lucro_pa'] = 0.00;
                }else {
                    $GLOBALS['fator_margem_lucro_pa'] = $campos[0]['fator_margem_lucro_pa'];
                }
                /**************************************************************************************************/
                $dados_produto      = intermodular::dados_impostos_pa($campos_pipa[0]['id_produto_acabado'], 1);//Sempre trago dados como a UF = 'SP' ...
                
                $icms_cf_uf_sp      = $dados_produto['icms_cadastrado'];
                $reducao_cf_uf_sp   = $dados_produto['reducao_cadastrado'];
                $ICMS_c_red_vendas  = ($icms_cf_uf_sp) * (1 - $reducao_cf_uf_sp / 100);
                
                $ICMS_c_red_compras = $campos[0]['icms'] * (1 - $campos[0]['reducao'] / 100);
                $diferenca_icms     = ($ICMS_c_red_vendas - $ICMS_c_red_compras);
                
                $custo_bancario     = self::custo_bancario($campos_pipa[0]['id_produto_acabado']);
                
                //echo '<br/>OIF '.$outros_impostos_federais;
                //echo '<br/>CB '.$custo_bancario;
                //echo '<br/>DI '.$diferenca_icms;
                
                /*Quando SGD a diferen�a de ICMS deveria ser 0% pra Limas e Outros Impost Fed. teria 
                de ser zerado ...

                 NF deveria de ICMS = 18 e OIF 7,5% ...*/
                
                //Aqui eu acrescento os Impostos Federais em cima das vari�veis calculadas acima ...
                $preco_venda_fat_nac_min_rs     = $preco_venda_fat_nac_min_rs / (1 - $outros_impostos_federais / 100) / (1 - $custo_bancario / 100) / (1 - $diferenca_icms / 100); 
                
                $preco_venda_fat_inter_min_rs   = $preco_venda_fat_inter_min_rs / (1 - $outros_impostos_federais / 100) / (1 - $custo_bancario / 100) / (1 - $diferenca_icms / 100);
                
                if($somar_etapa1 == 1) {
                    $valor_pac_indust = self::todas_etapas($campos_pipa[0]['id_produto_acabado'], 1, 1);
                }else {
                    $valor_pac_indust = self::todas_etapas($campos_pipa[0]['id_produto_acabado'], 1, 0);
                }

                //Somo o Valor de Custo Industrial em cima das vari�veis calculadas acima ...
                if($preco_venda_fat_nac_min_rs > 0) $preco_venda_fat_nac_min_rs+= $valor_pac_indust;
                if($preco_venda_fat_inter_min_rs > 0) $preco_venda_fat_inter_min_rs+= $valor_pac_indust;
                
                /****************************************************************************************************/
                /*************Controle para saber qual vari�vel � a ideal p/ ser utilizada na Negocia��o*************/
                /****************************************************************************************************/
                if($id_pais == 31) {//Fornecedor do Brasil ...
                    if($id_fornecedor == 146) {//Se Fornecedor = 'Hispania', esse � o �nico Fornecedor que possui 2 Pre�os ...
                        if($trazer_pco_compra_no_orc == 'N') {//Traz o Pre�o Nacional calculado acima ...
                            $preco_venda_fat_orc_min_rs = $preco_venda_fat_nac_min_rs;
                        }else if($trazer_pco_compra_no_orc == 'I') {//Traz o Pre�o Internacional calculado acima ...
                            //Somente se o Pre�o Internacional for Maior que Zero ...
                            $preco_venda_fat_orc_min_rs = ($preco_venda_fat_inter_min_rs > 0) ? $preco_venda_fat_inter_min_rs : $preco_venda_fat_nac_min_rs;
                        }else {//N�o par�metro, ent�o o sistema d� prioridade p/ o Caminho Nacional ...
                            /*Estamos interpretando que estamos entrando em uma Tela de Custo p/ confec��o de Lista
                            de Pre�o, ent�o o Cliente � nacional (No custo Novo precisaremos definir que a 
                            UF = 'SP' nesse caso) ...

                            //Somente se o Pre�o Nacional for Maior que Zero ...*/
                            $preco_venda_fat_orc_min_rs = ($preco_venda_fat_nac_min_rs > 0) ? $preco_venda_fat_nac_min_rs : $preco_venda_fat_inter_min_rs;
                        }
                    }else {//Qualquer outro Fornecedor, traz o Pre�o Nacional ...
                        $preco_venda_fat_orc_min_rs = $preco_venda_fat_nac_min_rs;
                    }
                }else {//Fornecedor de Pa�s Internacional, traz o Pre�o Internacional ...
                    $preco_venda_fat_orc_min_rs = $preco_venda_fat_inter_min_rs;
                }
                return array('preco_venda_fat_nac_min_rs' => $preco_venda_fat_nac_min_rs, 'preco_venda_fat_inter_min_rs' => $preco_venda_fat_inter_min_rs, 'preco_venda_fat_orc_min_rs' => $preco_venda_fat_orc_min_rs);
            }else {
                return 'PRODUTO INSUMO N�O ENCONTRADO.';
            }
        }
    }
 
    function custo_embalagem($id_produto_insumo, $id_produto_acabado) {//Quase igual o preco_custo_pi, a <> � q retorna o calculo direto do banco ...
	$sql = "SELECT pecas_por_emb 
                FROM `pas_vs_pis_embs` 
                WHERE `id_produto_insumo` = '$id_produto_insumo' 
                AND `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
	$campos = bancos::sql($sql);
        if(count($campos) == 0) {//N�o achou embalagem no Custo deste PA ...
            return 0;
	}else {
            $sql = "SELECT unidade_conversao 
                    FROM `produtos_insumos` 
                    WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
            $campos_pi = bancos::sql($sql);
            if(count($campos_pi) == 0) {//N�o encontrou o PI passador por par�metro, cadastrado no BD ...
                return 0;
            }else {
                if($campos_pi[0]['unidade_conversao'] == 0 || $campos_pi[0]['unidade_conversao'] == 0.00) {
                    $unidade_conversao = 1;
                }else {
                    $unidade_conversao = $campos_pi[0]['unidade_conversao'];
                }

                if($campos[0]['pecas_por_emb'] == 0 || $campos[0]['pecas_por_emb'] == null) {
                    $pecas_por_emb = 1;
                }else {
                    $pecas_por_emb = $campos[0]['pecas_por_emb'];
                }
                $pecas_emb_conv = ((1 / $unidade_conversao) / $pecas_por_emb);
            }
            if($pecas_emb_conv == 0.00 || $pecas_emb_conv == 0) $pecas_emb_conv = 1;
            return self::preco_custo_pi($id_produto_insumo, 0) * $pecas_emb_conv;
        }
    }

    function preco_produto_sem_pedido($id_produto_insumo, $desvio = 0) {
        //1�) Pego o valor nacional ...
	//Eu tive que modificar porque a id�ia na realidade � retornar o menor pre�o > do que Zero - D�rcio ...
	$sql = "SELECT MIN(fpi.preco_faturado + fpi.preco_faturado_adicional) AS preco_faturado_total, f.id_fornecedor 
                FROM `fornecedores_x_prod_insumos` fpi 
                INNER JOIN `fornecedores` f ON f.`id_fornecedor` = fpi.`id_fornecedor` AND f.`ativo` = '1' 
                WHERE fpi.`id_produto_insumo` = '$id_produto_insumo' 
                AND fpi.`ativo` = '1' 
                AND (fpi.`preco_faturado` + fpi.`preco_faturado_adicional`) > '0' 
                GROUP BY (fpi.preco_faturado + fpi.preco_faturado_adicional) LIMIT 1 ";
	$campos_fat         = bancos::sql($sql);
	$valor1             = (count($campos_fat) > 0) ? $campos_fat[0]['preco_faturado_total'] : 0;
	$moeda_custo        = genericas::variaveis('moeda_custo');
	$fator_importacao   = genericas::variaveis('fator_importacao');
        //2�) Pego o D�lar Export ...
	$sql = "SELECT (fpi.preco_faturado_export + fpi.preco_faturado_export_adicional) AS preco_faturado_export_total, fpi.valor_moeda_compra, f.id_pais, f.id_fornecedor 
                FROM `fornecedores_x_prod_insumos` fpi 
                INNER JOIN `fornecedores` f ON f.`id_fornecedor` = fpi.`id_fornecedor` 
                WHERE fpi.`id_produto_insumo` = '$id_produto_insumo' 
                AND fpi.`tp_moeda` = '1' ORDER BY preco_faturado_export_total DESC LIMIT 1 ";
	$campos_dolar = bancos::sql($sql);
	if(count($campos_dolar) > 0) {//Fornecedor sem pedido, mas que consta na Lista de Pre�o ...
            if($campos_dolar[0]['id_pais'] == 31) {//Fornecedor do Brasil ...
                $valor2 = $campos_dolar[0]['preco_faturado_export_total'] * $campos_dolar[0]['valor_moeda_compra'];
            }else {//Internacional ...
                $valor2 = $campos_dolar[0]['preco_faturado_export_total'] * $moeda_custo['dolar_custo'] * $fator_importacao;
            }
        }else {
            $valor2 = 0;
        }
        //3�) Pego o Euro Export ...
        $sql = "SELECT (fpi.preco_faturado_export + fpi.preco_faturado_export_adicional) AS preco_faturado_export_total, fpi.valor_moeda_compra, f.id_pais, f.id_fornecedor 
                FROM `fornecedores_x_prod_insumos` fpi 
                INNER JOIN `fornecedores` f ON f.`id_fornecedor` = fpi.`id_fornecedor` 
                WHERE fpi.`id_produto_insumo` = '$id_produto_insumo' 
                AND fpi.`tp_moeda` = '2' ORDER BY preco_faturado_export_total DESC LIMIT 1 ";
        $campos_euro = bancos::sql($sql);
        if(count($campos_euro) > 0) {//Fornecedor sem pedido, mas que consta na Lista de Pre�o ...
            if($campos_euro[0]['id_pais'] == 31) {//Fornecedor do Brasil ...
                $valor3 = $campos_euro[0]['preco_faturado_export_total'] * $campos_euro[0]['valor_moeda_compra'];
            }else {//Internacional ...
                $valor3 = $campos_euro[0]['preco_faturado_export_total'] * $moeda_custo['euro_custo'] * $fator_importacao;
            }
        }else {
            $valor3 = 0;
        }
        if($desvio == 0) {//Retorno os valores em array ...
            $valor_custo[0] = $valor1;
            if($valor2 >= $valor3) {//Se o D�lar for maior do que o Euro, Retorno o D�lar ...
                $valor_custo[1] = $valor2;
            }else {//Retorno o Valor Euro ...
                $valor_custo[1] = $valor3;
            }
            return $valor_custo;
	}else {//Retorna o id_fornecedor_default ...
            if($valor1 >= $valor2 && $valor1 >= $valor3) {
                return $campos_fat[0]['id_fornecedor'];
            }else if($valor2 >= $valor1 && $valor2 >= $valor3) {
                return $campos_dolar[0]['id_fornecedor'];
            }else {
                return $campos_euro[0]['id_fornecedor'];
            }
	}
    }
/***********************************Aqui eu Pego o Fornecedor Default do PI***********************************/
    //Fun��o que traz o valor do custo real do PI c/ Base do Pedido na Lista de Pre�o se � tiver pega o maior da Lista ...
    function preco_custo_pi($id_produto_insumo, $desvio = 0, $get_id_fonecedor = 0) {
	//Rotina normal de como era antigamente ...
	$sql = "SELECT (fpi.`preco_faturado` + fpi.`preco_faturado_adicional`) AS preco_faturado_total, 
                (fpi.`preco_faturado_export` + fpi.`preco_faturado_export_adicional`) AS preco_faturado_export_total, 
                fpi.`tp_moeda`, fpi.`valor_moeda_compra`, fpi.`ipi`, fpi.`ipi_incluso`, fpi.`icms`, 
                fpi.`reducao`, f.`id_pais`, f.`id_fornecedor` 
                FROM `produtos_insumos` pi 
                INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_produto_insumo` = pi.`id_produto_insumo` AND fpi.`id_fornecedor` = pi.`id_fornecedor_default` AND fpi.`ativo` = '1' 
                INNER JOIN `fornecedores` f ON f.`id_fornecedor` = pi.`id_fornecedor_default` 
                WHERE pi.`id_produto_insumo` = '$id_produto_insumo' 
                AND pi.`id_fornecedor_default` > '0' 
                AND pi.`ativo` = '1' LIMIT 1 ";
        $campos = bancos::sql($sql);//Essa parte iria ser a minha nova adapta��o -D�rcio ...
	if(count($campos) > 0) {//Possui Hum Pedido e um Fornecedor Default ...
            //Este parametro � q s� quero saber quem � o id_fornecedor default, ou seja o �ltimo pedido deste PI ...
            if($get_id_fonecedor == 1) return $campos[0]['id_fornecedor'];//id_fornecedor_default ...
            if($campos[0]['id_pais'] == 31) {//Fornecedor do Brasil ...
                $valor_custo[0] = $campos[0]['preco_faturado_total'];
                if($campos[0]['tp_moeda'] == 1) {//D�lar ...
                    $valor_custo[1] = $campos[0]['preco_faturado_export_total'] * $campos[0]['valor_moeda_compra'];
                }else if($campos[0]['tp_moeda'] == 2) {//Euro ...
                    $valor_custo[1] = $campos[0]['preco_faturado_export_total'] * $campos[0]['valor_moeda_compra'];
                }else {
                    $valor_custo[1] = 0;
                }
            }else {//Fornecedor de Pa�s Internacional ...
                $moeda_custo        = genericas::variaveis('moeda_custo');
                $fator_importacao   = genericas::variaveis('fator_importacao');
                $valor_custo[0]     = $campos[0]['preco_faturado_total'];
                if($campos[0]['tp_moeda'] == 1) {//D�lar ...
                    $valor_custo[1] = $campos[0]['preco_faturado_export_total'] * $moeda_custo['dolar_custo'] * $fator_importacao;
                }else if($campos[0]['tp_moeda'] == 2) {//Euro ...
                    $valor_custo[1] = $campos[0]['preco_faturado_export_total'] * $moeda_custo['euro_custo'] * $fator_importacao;
                }else {
                    $valor_custo[1] = 0;
                }
            }
            if($desvio == 0) {
                $icms_c_red         = $campos[0]['icms'] * (1 - $campos[0]['reducao'] / 100);

                if($valor_custo[0] > $valor_custo[1]) {//Pre�o Faturado � maior do que o Pre�o de Exporta��o ...
                    $preco_rs_kg_s_icms = $valor_custo[0] * (1 - $icms_c_red / 100);
                }else {
                    $preco_rs_kg_s_icms = $valor_custo[1] * (1 - $icms_c_red / 100);
                }
                if($campos[0]['ipi_incluso'] == 'S') $preco_rs_kg_s_icms*= (1 - $campos[0]['ipi'] / 100 / 2);
                return $preco_rs_kg_s_icms;
            }else {//Desvio � >= 1 significa q temos q trazer os dois valor para formacao da base de calculo do PI ...
                return $valor_custo; //retorna em array para a formacao do custo do PI
            }
	}else {//N�o possui Pedido p/ este PI ent�o pego o maior preco da lista do Fornecedor atrelado ...
            //Este parametro � q s� quero saber quem � o id_fornecedor default, ou seja o �ltimo pedido deste PI ...
            if($get_id_fonecedor == 1) return self::preco_produto_sem_pedido($id_produto_insumo, $get_id_fonecedor);//busco o id_fornecedor_default do maior ...
            $valor_custo = self::preco_produto_sem_pedido($id_produto_insumo);
            if($desvio == 0) {//Aqui � p/ retornar somente um �nico valor ...
                if($valor_custo[0] > $valor_custo[1]) {//Pre�o Faturado � maior do que o Pre�o de Exporta��o 
                    return $valor_custo[0];//Retorna o Pre�o Faturado ...
                }else {
                    return $valor_custo[1];//Retorna o Pre�o de Exporta��o ...
                }
            }else {//Desvio = 1, significa q temos q trazer os dois valor para formacao da base de calculo do PI ...
                return $valor_custo;//retorna em array p/ a forma��o do custo do PI ...
            }
	}
    }

    //Esta fun��o calcula somente os produtos do tipo revenda q est�o atrelados ao Industrializa��o ...
    function pipa_revenda($id_produto_acabado, $somar_embalagem = 1, $id_uf_cliente = 0, $nota_sgd) {
        return self::procurar_fornecedor_default_revenda($id_produto_acabado, $somar_embalagem, '', '', $id_uf_cliente, '', $nota_sgd);
    }

/***********************************Aqui eu Pego o Fornecedor Defatul do PA***********************************/
    //Fun��o pega o fornecedor Default do PA e depois procura o valor Revenda do mesmo ...
    function procurar_fornecedor_default_revenda($id_produto_acabado, $somar_embalagem = 1, $get_id_fornecedor_default = 0, $trazer_pco_compra_no_orc, $id_uf_cliente = 0, $trazer_array_todos_valores_revenda, $nota_sgd = 'N') {
        //A primeira coisa que sempre tenho que fazer ao entrar nessa fun��o � "buscar o Fornecedor Default na tabela do PI" ...
        $sql = "SELECT pi.`id_fornecedor_default`, pi.`id_produto_insumo` 
                FROM `produtos_acabados` pa 
                INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = pa.`id_produto_insumo` 
                WHERE pa.`id_produto_acabado` = '$id_produto_acabado' 
                AND pa.`id_produto_insumo` > '0' 
                AND pa.`ativo` = '1' LIMIT 1 ";
        $campos                 = bancos::sql($sql);
        $id_fornecedor_default  = $campos[0]['id_fornecedor_default'];
        
        if($id_fornecedor_default > 0) {//Existe fornecedor default ...
            $valores                                = self::preco_custo_ml_min_revenda($id_fornecedor_default, $campos[0]['id_produto_insumo'], $somar_embalagem, $trazer_pco_compra_no_orc);
            $preco_venda_fat_nac_min_rs             = $valores['preco_venda_fat_nac_min_rs'];
            $preco_venda_fat_inter_min_rs           = $valores['preco_venda_fat_inter_min_rs'];
            $preco_venda_fat_orc_min_rs             = $valores['preco_venda_fat_orc_min_rs'];
            $preco_venda_fat_orc_min_rs_sem_icms_sp = $valores['preco_venda_fat_orc_min_rs_sem_icms_sp'];
        }else {//N�o temos fornecedor default, ou seja bem prov�vel que seja um PI novo, rec�m cadastrado do PA "PIPA" ...
            //Pego o id_fornecedor_default e o PI do PA "PIPA" em Pedido de Compras ...
            $sql = "SELECT pi.`id_fornecedor_default`, pi.`id_produto_insumo`, pa.`operacao` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = pa.`id_produto_insumo` AND pi.`tem_pedido` = 'S' 
                    WHERE pa.`id_produto_acabado` = '$id_produto_acabado' 
                    AND pa.`id_produto_insumo` > '0' 
                    AND pa.`operacao_custo` = '1' 
                    AND pa.`ativo` = '1' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) > 0) {
                echo 'CHAMAR DARCIO OU ROBERTO - FUN��O CUSTO LINHA 842 "FUN��O (procurar_fornecedor_default_revenda) "';
                exit;
                
                //� partir desse momento, a vari�vel $id_fornecedor_default passa a assumir o id_fornecedor do Pedido de Compras ...
                $id_fornecedor_default                  = $campos[0]['id_fornecedor_default'];
                
                $valores                                = self::preco_custo_ml_min_revenda($id_fornecedor_default, $campos[0]['id_produto_insumo'], $somar_embalagem, $trazer_pco_compra_no_orc);
                $preco_venda_fat_nac_min_rs             = $valores['preco_venda_fat_nac_min_rs'];
                $preco_venda_fat_inter_min_rs           = $valores['preco_venda_fat_inter_min_rs'];
                $preco_venda_fat_orc_min_rs             = $valores['preco_venda_fat_orc_min_rs'];
                $preco_venda_fat_orc_min_rs_sem_icms_sp = $valores['preco_venda_fat_orc_min_rs_sem_icms_sp'];
            }else {
                //Pego o id_fornecedor e o PI do PA na sua �ltima atualiza��o da lista de Pre�o ...
                $sql = "SELECT fpi.`id_fornecedor`, fpi.`id_produto_insumo`, pa.`operacao` 
                        FROM `produtos_acabados` pa 
                        INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_produto_insumo` = pa.`id_produto_insumo` AND fpi.`ativo` = '1' 
                        WHERE pa.`id_produto_acabado` = '$id_produto_acabado' 
                        AND pa.`id_produto_insumo` > '0' 
                        AND pa.`operacao_custo` = '1' 
                        AND pa.`ativo` = '1' 
                        ORDER BY fpi.`data_sys` DESC LIMIT 1 ";
                $campos = bancos::sql($sql);
                if(count($campos) > 0) {//Encontrou 1 valor ...
                    /*Como n�o existia nenhum "Fornecedor Default na tabela do PI", ent�o atribuo este que foi encontrado da Lista de Pre�os 
                    na tabela do PI ...*/
                    if($id_fornecedor_default == 0 || is_null($id_fornecedor_default)) self::setar_fornecedor_default($campos[0]['id_produto_insumo'], $campos[0]['id_fornecedor'], 'N');
                    
                    //� partir desse momento, a vari�vel $id_fornecedor_default passa a assumir o id_fornecedor da Lista de Pre�o ...
                    $id_fornecedor_default                  = $campos[0]['id_fornecedor'];
                    
                    $valores                                = self::preco_custo_ml_min_revenda($id_fornecedor_default, $campos[0]['id_produto_insumo'], $somar_embalagem, $trazer_pco_compra_no_orc);
                    $preco_venda_fat_nac_min_rs             = $valores['preco_venda_fat_nac_min_rs'];
                    $preco_venda_fat_inter_min_rs           = $valores['preco_venda_fat_inter_min_rs'];
                    $preco_venda_fat_orc_min_rs             = $valores['preco_venda_fat_orc_min_rs'];
                    $preco_venda_fat_orc_min_rs_sem_icms_sp = $valores['preco_venda_fat_orc_min_rs_sem_icms_sp'];
                }else {
                    return 0;//N�o achou fornecedor com o �ltima atualiza��o em Lista ...
                }
            }
        }
        
        if($get_id_fornecedor_default == 1) {//Quero apenas o id_fornecedor Default de Revenda conforme par�metro passado ...
            return $id_fornecedor_default;
        }else {//Retorno todos os Valores de Revenda do PI / Fornecedor Default ...
            /*Se essa vari�vel "$trazer_array_todos_valores_revenda" = 'S' trago todas as vari�veis que foram calculadas 
            na fun��o preco_custo_ml_min_revenda(), porque tem telas que n�s n�o queremos enxergar mais de um valor como 
            Pre�o Nacional e Pre�o Internacional ao mesmo tempo, exemplo de uma das Telas -> "Custo de Revenda" ...*/
            if($trazer_array_todos_valores_revenda == 'S') {
                return array('preco_venda_fat_nac_min_rs' => $preco_venda_fat_nac_min_rs, 'preco_venda_fat_inter_min_rs' => $preco_venda_fat_inter_min_rs, 'preco_venda_fat_orc_min_rs' => $preco_venda_fat_orc_min_rs, 'preco_venda_fat_orc_min_rs_sem_icms_sp' => $preco_venda_fat_orc_min_rs_sem_icms_sp);
            }else {
                return $preco_venda_fat_orc_min_rs;
            }
        }
    }

    //Est� fun��o tem q estar somente no incluir itens ???
    function setar_fornecedor_default($id_produto_insumo, $id_fornecedor, $tem_pedido = 'N') {
        if($id_produto_insumo <> 0 && $id_fornecedor <> 0) {
            /*Caso o $_SESSION[id_funcionario] da "Sess�o Funcion�rios" esteja inativado, ent�o eu chamo a 
            session_start('funcionarios') para que essa vari�vel $_SESSION[id_funcionario] volte a ativar e 
            n�o fure abaixo o UPDATE logo abaixo na tabela de `produtos_insumos` ...*/
            if(!isset($_SESSION[id_funcionario])) session_start('funcionarios');
            
            //Atualiza��o do Fornecedor Default e mais alguns dados na tabela de PI ...
            $sql = "UPDATE `produtos_insumos` SET `id_fornecedor_default` = '$id_fornecedor',  `id_funcionario_fornecedor_default` = '$_SESSION[id_funcionario]', `tem_pedido` = '$tem_pedido' WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
            bancos::sql($sql);
            
            //Verifico se esse Fornecedor possui esse PI na Lista de Pre�os ...
            $sql = "SELECT id_fornecedor_prod_insumo 
                    FROM `fornecedores_x_prod_insumos` 
                    WHERE `id_produto_insumo` = '$id_produto_insumo' 
                    AND `id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
            //pego o id para deixar este fornecedor como defalt para este produto
            $campos = bancos::sql($sql); 

            //Atualizo o item da Lista de Pre�o retirando os Pre�os adicionais ...
            $sql = "UPDATE `fornecedores_x_prod_insumos` SET `preco_faturado_export_adicional` = '0', `preco_faturado_adicional` = '0' WHERE `id_fornecedor_prod_insumo` = '".$campos[0]['id_fornecedor_prod_insumo']."' LIMIT 1 ";
            bancos::sql($sql);
        }
    }

    function lista_preco_vendas($id_produto_acabado, $id_uf_cliente) {
        //Busco dados de "Lista de Pre�o do mesmo" do PA passado por par�metro ...
        $sql = "SELECT pa.preco_unitario, pa.preco_export, ged.desc_base_a_nac, ged.desc_base_b_nac, 
                ged.acrescimo_base_nac, ged.desc_base_exp, ged.acrescimo_base_exp 
                FROM `produtos_acabados` pa 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                WHERE pa.`id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        $campos = bancos::sql($sql); //verifico se ele consta na tabela se nao cadastro ele
        if(count($campos) > 0) {
            $fator_desc_max_vendas  = genericas::variavel(19);
            $preco_unit             = $campos[0]['preco_unitario'];
            $preco_unit_exp         = $campos[0]['preco_export'];
            $desc_a_nac             = $campos[0]['desc_base_a_nac'];
            $desc_b_nac             = $campos[0]['desc_base_b_nac'];
            $acresc_nac             = $campos[0]['acrescimo_base_nac'];
            $desc_a_exp             = $campos[0]['desc_base_exp'];
            $acresc_exp             = $campos[0]['acrescimo_base_exp'];
            $dolar_dia              = genericas::moeda_dia('dolar');
            $preco_liq_fat_reais[]  = $preco_unit * (1 - $desc_a_nac / 100) * (1 - $desc_b_nac / 100) * (1 + $acresc_nac / 100);
            $preco_liq_fat_reais[]  = $preco_unit_exp * (1 - $desc_a_exp / 100) * (1 + $acresc_exp / 100);
            
            /* Esse trecho foi comentado no dia 10/10/2014 �s 11:30 ... 
            N�o estava sendo utilizado p/ nada ... D�rcio J�nior

            $preco_custo_pa         = self::preco_custo_pa($id_produto_acabado, $id_uf_cliente);
            $preco_max_fat_custo[]  = $preco_custo_pa / $fator_desc_max_vendas;
            //A princ�pio: "O pre�o Export" � o mesmo que $preco_custo_pa que foi calculado acima ...
            $preco_custo_pa_exp     = $preco_custo_pa;
            $preco_max_fat_custo[]  = $preco_custo_pa_exp / $fator_desc_max_vendas / $dolar_dia;*/
            return $preco_liq_fat_reais;//indice 0 <=> nacional - 1 <=> export ...
        }else {
            return 0;
        }
    }

    /*Retorna o Valor do Custo indiferente do PA ser Industrial ou Revenda, a pr�pria fun��o identifica ...

    Observa��o: Essa vari�vel "$trazer_array_todos_valores_revenda" traz todas as vari�veis que foram calculadas na 
    fun��o preco_custo_ml_min_revenda(), porque tem telas que n�s n�o queremos enxergar mais de um valor como 
    Pre�o Nacional e Pre�o Internacional ao mesmo tempo, exemplo de uma das Telas -> "Custo de Revenda" ...*/
    function preco_custo_pa($id_produto_acabado, $id_uf_cliente, $trazer_array_todos_valores_revenda = 'N') {
        $sql = "SELECT `operacao_custo` 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) > 0) {
            if($campos[0]['operacao_custo'] == 0) {//Industrial ...
                return self::todas_etapas($id_produto_acabado, $campos[0]['operacao_custo']);
            }else if($campos[0]['operacao_custo'] == 1) {//Revenda ...
                if($trazer_array_todos_valores_revenda == 'S') {
                    $valores = self::procurar_fornecedor_default_revenda($id_produto_acabado, 1, '', '', $id_uf_cliente, $trazer_array_todos_valores_revenda);
                    return array('preco_venda_fat_nac_min_rs' => $valores['preco_venda_fat_nac_min_rs'], 'preco_venda_fat_inter_min_rs' => $valores['preco_venda_fat_inter_min_rs'], 'preco_venda_fat_orc_min_rs' => $valores['preco_venda_fat_orc_min_rs'], 'preco_venda_fat_orc_min_rs_sem_icms_sp' => $valores['preco_venda_fat_orc_min_rs_sem_icms_sp']);
                }else {
                    $preco_venda_fat_orc_min_rs = self::procurar_fornecedor_default_revenda($id_produto_acabado, 1, '', '', $id_uf_cliente);
                    return $preco_venda_fat_orc_min_rs;//Sempre retorna o Valor do Pre�o de Venda Nacional ...
                }
            }else {
                return 0;
            }
        }else {
            return 0;
        }
    }

    //Verifica quando atrelo os itens na 7� etapa ...
    function liberar_desliberar_custo_auto($id_produto_acabado, $id_produto_acabado_custo) {
        //Aqui eu busco a opera��o de Custo do PA e o seu status no Custo ...
        $sql = "SELECT operacao_custo, status_custo 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) > 0) {
            //Se o PA q passei por par�metro estiver bloqueado, bloqueia todos os seu relacionados ...
            if($campos[0]['status_custo'] == 0) self::liberar_desliberar_custo($id_produto_acabado_custo, 'nao');
        }else {
            exit('Erro no sistema, contate o administrador !!! ');
        }
    }

    function liberar_desliberar_custo($id_produto_acabado_custo, $liberar) {
        //Busco o PA do Custo "$id_produto_acabado_custo" passado por par�metro ...
        $sql = "SELECT `id_produto_acabado`, `operacao_custo` 
                FROM `produtos_acabados_custos` 
                WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
        $campos_produto_acabado = bancos::sql($sql);
        $id_produto_acabado     = $campos_produto_acabado[0]['id_produto_acabado'];
        $operacao_custo         = $campos_produto_acabado[0]['operacao_custo'];
        
        //Apenas se o usu�rio estiver tentando liberar o Custo que fa�o as verifica��es abaixo ...
        if(strtoupper($liberar) == 'SIM') {//Deseja liberar o custo ...
            //Verifico se existe(m) Embalagem(ns) atrelada(s) nessa 1� Etapa do Custo p/ esse PA ...
            $sql = "SELECT `id_pa_pi_emb`, `embalagem_default` 
                    FROM `pas_vs_pis_embs` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' ";
            $campos_embalagens = bancos::sql($sql);
            $linhas_embalagens = count($campos_embalagens);
            if($linhas_embalagens > 0) {//Existe pelo menos 1 Embalagem atrelada p/ esse PA do Custo ...
                $embalagem_default = 0;//Valor Inicial ...            
                //Verifico se das Embalagens existentes, alguma ainda � a Principal ...
                for($i = 0; $i < $linhas_embalagens; $i++) {
                    if($campos_embalagens[$i]['embalagem_default'] == 1) {
                        $embalagem_default = 1;
                        break;//P/ sair fora do Loop, afinal apenas uma Embalagem Default � o que me interessa ...
                    }
                }
                //Se das Embalagem existentes nenhuma for Default, ent�o n�o posso Liberar o Custo ...
                if($embalagem_default == 0) {
                    $liberar = 'NAO';
    ?>
        <Script Language = 'JavaScript'>
            alert('ESSE CUSTO N�O PODE SER LIBERADO !!!\n\n� NECESS�RIO MARCAR NA 1� ETAPA ALGUMA EMBALAGEM COMO SENDO PRINCIPAL !')
        </Script>
    <?
                }
            }
            /*********************************************************************
            Problema ele pega o pai e verifica o filho e vice versa, pois s�o recursiva da mesma fun��o ...
            **********************************************************************/
            //Antes de liberar o Custo, eu verifico se foi zerado a Qtde do Lote ...
            $sql = "SELECT qtde_lote 
                    FROM `produtos_acabados_custos` 
                    WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
            $campos_qtde_lote = bancos::sql($sql);
            //Se estiver zerado, tem de bloquear o Custo. N�o posso liberar o Custo de jeito maneira ...
            if($campos_qtde_lote[0]['qtde_lote'] == 0) {
                $liberar = 'NAO';
    ?>
        <Script Language = 'JavaScript'>
            alert('ESSE CUSTO N�O PODE SER LIBERADO !\nA QUANTIDADE DO LOTE DESSE CUSTO INDUSTRIAL � = 0,00 !')
        </Script>
    <?
            }
            /*********************************************************************
            Verifico se dos PI�s que ser�o encontrados mais abaixo, algum est� sem atualiza��o 
            de Pre�o por mais de 90 dias em sua Lista de Compras ...
            **********************************************************************/
            /*Em primeiro lugar: busco todos os PI�s desse Custo "$id_produto_acabado_custo" 
            nas Etapas 1, 2, 3, 5 e 6 ...*/
            $sql = "(SELECT ppe.`id_produto_insumo` 
                    FROM `pas_vs_pis_embs` ppe 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ppe.`id_produto_acabado` 
                    WHERE ppe.`id_produto_acabado` = '$id_produto_acabado') 
                    UNION ALL 
                    (SELECT `id_produto_insumo` 
                    FROM `produtos_acabados_custos` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' 
                    AND `operacao_custo` = '$operacao_custo') 
                    UNION ALL 
                    (SELECT DISTINCT(pp.`id_produto_insumo`) 
                    FROM `produtos_acabados_custos` pac 
                    INNER JOIN `pacs_vs_pis` pp ON pp.`id_produto_acabado_custo` = pac.`id_produto_acabado_custo` 
                    WHERE pac.`operacao_custo` = '$operacao_custo' 
                    AND pac.`id_produto_acabado` = '$id_produto_acabado') 
                    UNION ALL 
                    (SELECT ppt.`id_produto_insumo` 
                    FROM `produtos_acabados_custos` pac 
                    INNER JOIN `pacs_vs_pis_trat` ppt ON ppt.`id_produto_acabado_custo` = pac.`id_produto_acabado_custo` 
                    WHERE pac.`operacao_custo` = '$operacao_custo' 
                    AND pac.`id_produto_acabado` = '$id_produto_acabado') 
                    UNION ALL 
                    (SELECT ppu.`id_produto_insumo` 
                    FROM `produtos_acabados_custos` pac 
                    INNER JOIN `pacs_vs_pis_usis` ppu ON ppu.`id_produto_acabado_custo` = pac.`id_produto_acabado_custo` 
                    WHERE pac.`operacao_custo` = '$operacao_custo' 
                    AND pac.`id_produto_acabado` = '$id_produto_acabado') ";
            $campos_etapas_12356 = bancos::sql($sql);
            $linhas_etapas_12356 = count($campos_etapas_12356);
            for($i = 0; $i < $linhas_etapas_12356; $i++) {
                //1) Desse PI do Loop eu busco o Fornecedor Default ...
                $id_fornecedor_default 	= self::preco_custo_pi($campos_etapas_12356[$i]['id_produto_insumo'], 0, 1);
                
                /*2) Com esse PI e esse $id_fornecedor_default entro na Lista de Pre�o e verifico se j� se 
                passaram mais de 90 dias em que esse item de Lista ficou sem atualiza��o de Pre�o, se sim isso 
                impedir� de o Custo ser Liberado ...*/
                $sql = "SELECT fpi.`custo_pi_bloqueado`, pi.`discriminacao` 
                        FROM `fornecedores_x_prod_insumos` fpi 
                        INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = fpi.`id_produto_insumo` 
                        WHERE fpi.`id_fornecedor` = '$id_fornecedor_default' 
                        AND fpi.`id_produto_insumo` = '".$campos_etapas_12356[$i]['id_produto_insumo']."' LIMIT 1 ";
                $campos_lista = bancos::sql($sql);
                //Realmente encontrou 1 item na Lista de Pre�o e este esta em nadipl�ncia ...
                if(count($campos_lista) == 1 && $campos_lista[0]['custo_pi_bloqueado'] == 'S') {
                    $liberar = 'NAO';
                    $itens.= '\n* '.$campos_lista[0]['discriminacao'].'; ';
                }
            }
            if($liberar == 'NAO') {//Houve alguma diverg�ncia ...
    ?>
        <Script Language = 'JavaScript'>
            alert('ESSE CUSTO N�O PODE SER LIBERADO ! O(S) ITEM(NS): \n<?=$itens;?>\n\n EST�(�0) SEM ATUALIZA��O DE PRE�O POR MAIS DE 90 DIAS EM SUA LISTA DE COMPRAS !')
        </Script>
    <?
            }
        }
        /**********************************************************************/
        if(strtoupper($liberar) == 'SIM') {//Deseja liberar o custo ...
            //Verifico quem � o id_produto_acabado_custo atrav�s do id_produto_acabado ...
            //Busco todos os produtos da etapa 7 atrav�s do id_produto_acabado_custo e se tiver algum bloqueado ele tras registro(s)...
            $sql = "SELECT pac.id_produto_acabado 
                    FROM `pacs_vs_pas` pp 
                    INNER JOIN `produtos_acabados_custos` pac ON pac.`id_produto_acabado_custo` = pp.`id_produto_acabado_custo` 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pp.`id_produto_acabado` AND pa.status_custo = '0' 
                    WHERE pp.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ";
            $campos = bancos::sql($sql);
            if(count($campos) == 0) {//Se n�o tiver nenhum PA de 7� Etapa bloqueado, pode liberar o Custo ...
                //Aqui eu busco o custo do PA da 7� Etapa ...
                $sql = "SELECT pac.id_produto_acabado, pa.referencia 
                        FROM `produtos_acabados_custos` pac 
                        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` 
                        WHERE pac.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ";
                $campos_custo = bancos::sql($sql);
                if(count($campos_custo) > 0) {//Aqui muda o status_custo do produto acabado p/ liberado ou n�o ...
                    if($campos_custo[0]['referencia'] == 'ESP') {//Atualiza o pre�o na "lista de pre�o" p/ saber se compensa da prioridade na fabrica��o ...
                        //Esta Func�o tem a mesma semelhan�a a do relat�rio do estoque ESP ...
                        $fator_desc_max_vendas      = genericas::variavel(19);
                        $preco_maximo_custo_fat_rs  = self::preco_custo_pa($campos_custo[0]['id_produto_acabado']) / $fator_desc_max_vendas;
                        //Aqui eu busco os descontos do PA na tabela de Empresa vs Empresa Divis�o ...
                        $sql = "SELECT ged.desc_base_a_nac, ged.desc_base_b_nac, ged.acrescimo_base_nac 
                                FROM `produtos_acabados` pa 
                                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                                WHERE `id_produto_acabado` = '".$campos_custo[0]['id_produto_acabado']."' 
                                AND pa.`referencia` = 'ESP' 
                                AND pa.`ativo` = '1' ";
                        $campos_pa_esp = bancos::sql($sql);
/*************Tratamento para n�o dar erro de Divis�o por Zero*************/
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
                        $sql = "UPDATE `produtos_acabados` SET `preco_unitario` = '$txt_preco_bruto_fat_rs' WHERE `id_produto_acabado` = '".$campos_custo[0]['id_produto_acabado']."' LIMIT 1 ";
                        bancos::sql($sql);
                    }
/**************************************************************************/
                    //Aqui eu libero o Custo do PA ...
                    $sql = "UPDATE `produtos_acabados` SET `status_custo` = '1' WHERE `id_produto_acabado` = '".$campos_custo[0]['id_produto_acabado']."' LIMIT 1 ";
                    bancos::sql($sql);
/**************************************************************************************************/
/************************************Verifi��o de Estoque do PA************************************/
/**************************************************************************************************/
/*Aqui eu verifico se esse PA j� tem algum Registro na Tabela Estoque, pois se eu n�o tiver esse Registro 
acaba complicando em outros locais do Sistema em que eu s� listo o PA desde que esse tenha Estoque ...*/
                    $sql = "SELECT id_estoque_acabado 
                            FROM `estoques_acabados` 
                            WHERE `id_produto_acabado`= ".$campos_custo[0]['id_produto_acabado']." LIMIT 1 ";
                    $campos_estoque = bancos::sql($sql);
                    if(count($campos_estoque) == 0) {//Se n�o existe registro desse PA nessa tabela, ent�o insiro o mesmo hum ...
                        $sql = "INSERT INTO `estoques_acabados` (`id_produto_acabado`, `qtde`) values ('".$campos_custo[0]['id_produto_acabado']."', '0.00') ";
                        bancos::sql($sql);
                    }
/**************************************************************************************************/
                    $sql = "UPDATE `mensagens_esps` me 
                            INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_orcamento_venda_item = me.id_orcamento_venda_item 
                            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` AND pa.`id_produto_acabado` = '".$campos_custo[0]['id_produto_acabado']."' 
                            SET me.`status` = '1' ";
                    bancos::sql($sql);//seta a msg para exibir no mural do representante que o custo do esp foi liberado
                }else {
                    echo 'Erro Fatal !!! Contate o administrador do ERP ! ';
                }
            }
        }else {//Deseja bloquear o custo  ...
            //Passa o status do PA para bloqueado ...
            $sql = "UPDATE `produtos_acabados` SET `status_custo` = '0' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            bancos::sql($sql);

            $sql = "UPDATE `mensagens_esps` me 
                    INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_orcamento_venda_item = me.id_orcamento_venda_item 
                    INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado AND pa.`id_produto_acabado` = '$id_produto_acabado' 
                    SET me.`status` = '0' ";
            bancos::sql($sql);//Seta a msg p/ exibir no mural do representante que o custo do ESP foi liberado ...

            //Aqui eu busco a Fam�lia desse $id_produto_acabado ...
            $sql = "SELECT gpa.`id_familia` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                    WHERE pa.`id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            $campos_familia = bancos::sql($sql);
            if($campos_familia[0]['id_familia'] != 25) {//S� irei fazer o procedimento abaixo, se a Fam�lia desse PA for diferente da Fam�lia "M�o de Obra" ...
                //Aqui eu pego todos os pais dele ou quem depende dele, todos os Custos ...
                $sql = "SELECT pp.`id_produto_acabado_custo` 
                        FROM `produtos_acabados` pa 
                        INNER JOIN `pacs_vs_pas` pp ON pp.`id_produto_acabado` = pa.`id_produto_acabado` 
                        WHERE pa.`id_produto_acabado` = '$id_produto_acabado' ";
                $campos_etapa7 = bancos::sql($sql);
                $linhas_etapa7 = count($campos_etapa7);
                if($linhas_etapa7 > 0) {
                    //Segue este caminho quando ele � P.A. PAI, bloqueio ele e os filhos dele ...
                    for($i = 0; $i < $linhas_etapa7; $i++) {//listo os PA da 7� etapa e vasculho novamente cada um dele ...
                        self::liberar_desliberar_custo($campos_etapa7[$i]['id_produto_acabado_custo'], 'NAO');
                    }
                }else {//N�o encontrou nada ...
                    //echo 'Erro Fatal !';
                }
            }
        }
    }

    function calculo_taxa_financeira($id_orcamento_venda, $id_pedido_venda = 0) {
        /*if($id_pedido_venda == 0) {//Calcula com base no orcamento ...
            $sql = "SELECT `prazo_a`, `prazo_b`, `prazo_c`, `prazo_d` 
                    FROM `orcamentos_vendas` 
                    WHERE `id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
        }else {//Calcula com base no Pedido ...
            $sql = "SELECT `vencimento1` AS prazo_a, `vencimento2` AS prazo_b, `vencimento3` AS prazo_c, `vencimento4` AS prazo_d 
                    FROM `pedidos_vendas` 
                    WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
        }
        $campos = bancos::sql($sql);
        if(count($campos) > 0) {
            $prazo_medio = intermodular::prazo_medio($campos[0]['prazo_a'], $campos[0]['prazo_b'], $campos[0]['prazo_c'], $campos[0]['prazo_d']); 

            $sql = "UPDATE `orcamentos_vendas` SET `prazo_medio` = '$prazo_medio' WHERE `id_orcamento_venda` = '$id_orcamento_venda' ";
            bancos::sql($sql);//atualiza no BD o prazo m�dio do orcamento/pedidos de vendas
            $tx_financ_vendas               = genericas::variaveis('taxa_financeira_vendas');
            $fator_tx_financ_diaria         = pow((1 + $tx_financ_vendas / 100), (1 / 30));
            $fator_tx_financ_prazo_medio    = pow(($fator_tx_financ_diaria), ($prazo_medio));
            $tx_financeira                  = (($fator_tx_financ_prazo_medio / (1 + $tx_financ_vendas / 100)) - 1) * 100;
            return $tx_financeira;
        }else {
            return 0;
        }*/
        
        /*Fun��o que foi modificada �s 17:30 do dia 12/11/2015 porque n�o tinha sentido gravar um prazo 
        m�dio em Or�amento que j� � gravado no pr�prio Cabe�alho, essa fun��o foi feita h� anos atr�s 
        e hoje a situa��o � totalmente outra ...*/
        if($id_pedido_venda == 0) {//Calcula com base no Or�amento ...
            $sql = "SELECT `prazo_medio` 
                    FROM `orcamentos_vendas` 
                    WHERE `id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
        }else {//Calcula com base no Pedido ...
            $sql = "SELECT `prazo_medio` 
                    FROM `pedidos_vendas` 
                    WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
        }
        $campos = bancos::sql($sql);
        if(count($campos) > 0) {
            $tx_financ_vendas               = genericas::variaveis('taxa_financeira_vendas');
            $fator_tx_financ_diaria         = pow((1 + $tx_financ_vendas / 100), (1 / 30));
            $fator_tx_financ_prazo_medio    = pow(($fator_tx_financ_diaria), ($campos[0]['prazo_medio']));
            $tx_financeira                  = (($fator_tx_financ_prazo_medio / (1 + $tx_financ_vendas / 100)) - 1) * 100;
            return $tx_financeira;
        }else {
            return 0;
        }
    }
    
    /*Fun��o que tem por objetivo retornar o Custo Banc�rio, de acordo com as particulariza��es do $id_produto_acabado ou $id_pais 
    passados por par�metro ...*/
    function custo_bancario($id_produto_acabado, $id_pais = 31) {
        /*Hoje "17/04/2018" n�o existe Custo Banc�rio para as seguintes situa��es: 

        Fam�lias: 
         
        Pinos => 2;
        Pontas Fixas => 12;
        Morsas e Mesas => 21;
        Suporte p/ Serra copo => 22;
        Flu�dos => 26;
        Cabo de Lima => 27;
        Roscas Posti�as => 30;
        
        Grupos:
         
        Bedames M2 => 1;
        Bits M2 => 2;
        Buchas de Redu��o => 6;
        Chaves p/ Mandril Rohm => 62;

        Grupos vs Empresas Divis�es:
         
        Cossinete Manual Warrior => 18;

        Ou fora do pa�s "Pa�ses Estrangeiros" ...*/
        
        $vetor_familias                 = array(2, 12, 21, 22, 26, 27, 30);
        $vetor_grupos_pas               = array(1, 2, 6, 62);
        $vetor_grupos_empresas_divisoes = array(18);
        
        //Busco alguns dados do $id_produto_acabado passado por par�metro ...
        $sql = "SELECT gpa.`id_familia`, gpa.`id_grupo_pa`, ged.`id_gpa_vs_emp_div`, pa.`referencia` 
                FROM `produtos_acabados` pa 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                WHERE pa.`id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if($campos[0]['referencia'] == 'ESP') {//Se o PA for 'ESP', n�o Isento a Taxa Banc�ria ...
            $custo_bancario = genericas::variavel(66);
        }else {//Se o PA for normal de linha ...
            if(in_array($campos[0]['id_familia'], $vetor_familias) || 
            in_array($campos[0]['id_grupo_pa'], $vetor_grupos_pas) || 
            in_array($campos[0]['id_gpa_vs_emp_div'], $vetor_grupos_empresas_divisoes) || $id_pais <> 31) {//E se pertencer a alguma dessas fam�lias ou for para Pa�s Estrangeiro Isento a Taxa Banc�ria ...
                $custo_bancario = 0.4 * genericas::variavel(66);//Mudan�a realiza em 30/11/2018 ...
            }else {//Do contr�rio n�o Isento  Taxa Banc�ria ...
                $custo_bancario = genericas::variavel(66);
            }
        }
        return $custo_bancario;
    }

    //Calcula o Pre�o do Custo, tanto margem como Or�amento ...
    function preco_custo_esp_indust($id_produto_acabado, $qtde_item_orc, $id_pais = 31, $ref = 'ESP', $id_uf_cliente = 0, $ignorar_lote_minimo_do_grupo_faixa_orcavel = 'N') {
        //Pega a qtde do lote custo ...
        $sql = "SELECT pac.`qtde_lote`, pac.`id_produto_acabado_custo` 
                FROM `produtos_acabados_custos` pac 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` 
                WHERE pac.`operacao_custo` = '0' 
                AND pac.`id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        $campos_lote = bancos::sql($sql);
        $linhas_lote = count($campos_lote);
        if($linhas_lote > 0) {//Existe custo para este PA ...
            $qtde_lote_custo            = $campos_lote[0]['qtde_lote'];
            $id_produto_acabado_custo	= $campos_lote[0]['id_produto_acabado_custo'];
        }else {//N�o existe custo para este PA ...
            $qtde_lote_custo = 1;
            exit('COLOQUEI ESTE DESVIO PARA CERTIFICAR DE QUE N�O EXISTE CUSTO PARA ESTE PA !');
        }
        //Caso o id_pais = Estrangeiro e normal de Linha, entao eu calculo a qtde pelo do custo para nao variar ...
        if($id_pais != 31 && $ref != 'ESP') $qtde_item_orc = $qtde_lote_custo;
        //Se o custo n�o tiver nada atrelado a 4� etapa ...
        $sql = "SELECT COUNT(`id_pac_maquina`) AS total 
                FROM `pacs_vs_maquinas` 
                WHERE `id_produto_acabado_custo` = '".$id_produto_acabado_custo."' ";
        $campos_4_etapa = bancos::sql($sql);
        $qtde_4_etapa 	= $campos_4_etapa[0]['total'];
        //Se o custo n�o tiver itens no lote minimo na 5� etapa como lote minimo = '1'
        $sql = "SELECT COUNT(`id_pac_pi_trat`) AS total 
                FROM `pacs_vs_pis_trat` 
                WHERE `lote_minimo_fornecedor` = '1' 
                AND `id_produto_acabado_custo` = '".$id_produto_acabado_custo."' ";
        $campos_5_etapa         = bancos::sql($sql);
        $qtde_5_etapa           = $campos_5_etapa[0]['total'];
        $taxa_financeira_vendas	= genericas::variaveis('taxa_financeira_vendas') / 100 + 1;
        /*************************Valores Herdados*************************/
        $total_indust           = self::todas_etapas($id_produto_acabado, 0, 1, $qtde_item_orc, $id_uf_cliente);
        $fator_custo_etapa_4    = genericas::variavel(9);
        $etapa4                 = self::etapa4($id_produto_acabado, $fator_custo_etapa_4, 0);
        /*O Custo Fixo inclui todas as Etapas do Custo exceto a 4� mais os Custos de Taxa Financeira, 
        Custo Banc�rio e Impostos Federais, este nome n�o � o mais correto porque estas �ltimas 3 vari�veis 
        que entraram na Conta n�o eram para entrar como Custo Fixo ...*/
        $custos_fixos           = $total_indust - $etapa4;
        /******************************************************************/
        //Busca do Lote M�nimo de Produ��o em R$ ...
        if($qtde_4_etapa == 0 && $qtde_5_etapa == 0) {//Se n�o tiver itens na 4� etapa e a 5�, n�o existe M�o de Obra ...
            $lote_min_producao_reais = 0;
        }else {
            $sql = "SELECT gpa.`lote_min_producao_reais` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                    WHERE pa.`id_produto_acabado` = '".$id_produto_acabado."' LIMIT 1 ";
            $campos_lote_min = bancos::sql($sql);
            $linhas_lote_min = count($campos_lote_min);
            if($linhas_lote_min > 0) {//Achou ...
                $lote_min_producao_reais = $campos_lote_min[0]['lote_min_producao_reais'];
                //Caso o id_pais for estrangeiro e Normal de Linha, calculo a qtde pelo do custo para nao variar
                if($id_pais != 31) $lote_min_producao_reais/= genericas::moeda_dia('dolar');
            }else {//N�o Achou ...
                $lote_min_producao_reais = 0;
            }
        }
        
        /**********************************************************************************************************/
        /***********************************************Interpola��o***********************************************/
        /**********************************************************************************************************/
        /*Na data do dia 21/11/2013 trabalh�vamos com uma interpola��o +/- 5x a Qtde e mudamos para +/- 2x 
        porque que gerava muito erro de Custo ...*/
        $interpolacao = intval(genericas::variavel(60));
        /**********************************************************************************************************/
        
        //N�o est� marcando Depto. T�cnico no macho especial, porque a 4 e 5 etapa est�o zeradas ...
        //Verificar a segunda parte da f�rmula ...
        if((($qtde_item_orc > $interpolacao * $qtde_lote_custo) || ($qtde_lote_custo > $interpolacao * $qtde_item_orc)) && $ignorar_lote_minimo_do_grupo_faixa_orcavel == 'N' && ($qtde_4_etapa > 0 || $qtde_5_etapa > 0)) {
            return array(0, 0, 0);//DEPTO T�CNICO
        }
        
        //Vari�veis para simplificar a vida ...
        if($ignorar_lote_minimo_do_grupo_faixa_orcavel == 'S') {
            /*Nesse caso � 1 porque estamos ignorando a Interpola��o de Qtde de Lote entendendo que 
            o custo da 4� Etapa � sempre o mesmo independente da Qtde do Or�amento ...*/
            $fator_correcao_qtde    = 1;
            $fator_correcao_4etapa  = 1 * $etapa4;
        }else {
            $fator_correcao_qtde    = $qtde_item_orc / $qtde_lote_custo;
            $fator_correcao_4etapa  = $qtde_lote_custo * $etapa4 / $qtde_item_orc;
        }

        //Interpola��o � uma estimativa do Custo da M�o de obra em rela��o a uma Qtde diferente do Lote do Custo ...
        //C�lculo do Fator de Corre��o da Qtde ...
        if($fator_correcao_qtde < 0.5) {
            //0.6 � uma corre��o, baseado em alguns c�lculos do Roberto, foi a� o "melhor" n�mero retornado ...
            $custo_m_o = ($fator_correcao_qtde * 0.6 + 0.5) * $fator_correcao_4etapa;
        }else {
            $custo_m_o = ($fator_correcao_qtde * 0.5 + 0.5) * $fator_correcao_4etapa;
        }
        
        /*Esse � o Pre�o Faturado Nacional M�nimo do Custo j� com as interpola��es de quantidade 
        do Or�amento vs Lote do Custo, onde se estiver marcado no item do Or�amento Ignorar 
        Lote M�nimo do Grupo Faixa Or��vel, ele � o pr�prio Pre�o Faturado Nacional M�nimo 
        do Custo ...

        Lembrando que esse Pre�o j� tem Incluso: Taxa Financeira, Impostos Federais e Custo Banc�rio ...*/
        $preco_custo = ($custos_fixos + $custo_m_o);
        
        /*O Lote m�nimo Ideal deveria ser "Lote M�nimo em R$" / "P�o L�q. Faturado em R$", desde que esse Lote 
        fosse pr�ximo do Lote do Custo. O Ideal seria que o Custo j� fosse feito baseado nesse Lote M�nimo 
        Ideal atrav�s de mensagem na hora da confec��o do Custo ...*/
        return array($preco_custo, '', $lote_min_producao_reais);
    }
    
    /*Se quiser verificar os Grupos e Grupos vs Empresas Divis�es onde reduzimos o ICMS para 
    $id_uf_cliente == 1 && $campos[0]['operacao'] == 1 && $iva > 0 && $nota_sgd == 'N', v� para a 
    fun��o "procurar_fornecedor_default_revenda" que come�a a partir da Linha 800 ...*/
    function margem_lucro($id_orcamento_venda_item, $tx_financeira, $id_uf_cliente, $preco_liq_final) {
        $sql = "SELECT ov.`id_orcamento_venda`, ov.`tipo_frete`, ov.`nota_sgd`, ov.`prazo_a`, ov.`prazo_b`, 
                ov.`prazo_c`, ov.`prazo_d`, ov.`desc_icms_sqd_auto`, ov.`comprar_como_export`, 
                ovi.`id_produto_acabado`, ovi.`id_representante`, ovi.`qtde`, ovi.`queima_estoque`, 
                ovi.`ignorar_lote_minimo_do_grupo_faixa_orcavel`, pa.`referencia`, pa.`discriminacao`, 
                pa.`operacao`, pa.`operacao_custo`, pa.`peso_unitario`, pa.`status_custo`, pa.`observacao`, 
                c.`id_cliente`, c.`id_pais`, c.`id_uf`, c.`trading`, ged.`id_empresa_divisao`, ged.`id_grupo_pa` 
                FROM `orcamentos_vendas_itens` ovi 
                INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda 
                INNER JOIN `clientes` c ON c.id_cliente = ov.id_cliente 
                INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                WHERE ovi.`id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas > 0) {//Verifica se tem pelo menos um item no or�amento ...
            $trading        = $campos[0]['trading'];
            $id_pais        = $campos[0]['id_pais'];
            $id_uf_cliente  = $campos[0]['id_uf'];
            /******************************************************************/
            //Essa vari�vel -> "$trazer_pco_compra_no_orc" ser� utilizada mais abaixo dentro de uma outra fun��o ...

            /*Se foi marcado o Checkbox de "Comprar Como Export" no Or�amento ou o Cliente � Trading ou Cliente � 
            Internacional "Estrangeiro", sempre utilizaremos o pre�o de Compra "Lista" Internacional ...*/
            if($campos[0]['comprar_como_export'] == 'S' || $trading == 1 || $id_pais <> 31) {
                $trazer_pco_compra_no_orc   = 'I';//S� em or�amentos com itens da Hispania, que ser� marcada essa op��o ...
            }else {//Se n�o, o pre�o de Compra "Lista" que ser� retornado ser� sempre Nacional ...
                $trazer_pco_compra_no_orc   = 'N';
            }
            /******************************************************************/
            if($campos[0]['operacao_custo'] == 0) {//Industrializa��o ...
                $fator_margem_lucro = genericas::variavel(22);//margem de Lucro PA Industrial
                if($campos[0]['referencia'] != 'ESP') {//Normais de linha ...
                    if($campos[0]['status_custo'] == 0) return array(0, 'Or�ar');//Desvio somente na ML, se o Custo est� bloqueado n�o temos Pre�o ...
                    $preco_liq_fat_custo    = self::todas_etapas($campos[0]['id_produto_acabado'], 0, 1, $campos[0]['qtde'], $id_uf_cliente, strtoupper($campos[0]['nota_sgd']));
                }else {//Especial, � a mesma l�gica do or�amento exceto a divis�o pelo fator_margem_lucro_vendas no $preco_custo ...
                    if($campos[0]['status_custo'] == 0) return array(0, 'Or�ar');//Desvio somente na ML
                    /*Esse � o Pre�o Faturado Nacional M�nimo do Custo j� com as interpola��es de quantidade 
                    do Or�amento vs Lote do Custo, onde se estiver marcado no item do Or�amento Ignorar 
                    Lote M�nimo do Grupo Faixa Or��vel, ele � o pr�prio Pre�o Faturado Nacional M�nimo 
                    do Custo ...

                    Lembrando que esse Pre�o j� tem Incluso: Taxa Financeira, Impostos Federais e Custo Banc�rio ...*/
                    $preco_custo = self::preco_custo_esp_indust($campos[0]['id_produto_acabado'], $campos[0]['qtde'], $id_pais, $campos[0]['referencia'], $id_uf_cliente, $campos[0]['ignorar_lote_minimo_do_grupo_faixa_orcavel']);
                    
                    if($preco_custo[0] == 0 && $preco_custo[1] == 0) return array(0, 'DEPTO T�CNICO');
                    $lote_min_producao_reais = $preco_custo[2];
                    
                    /* Explica��o da vari�vel $preco_liq_fat_custo => 

                    Caso a Qtde nao atinja a Qtde de Lote Minimo do Grupo, o sistema calcula da seguinte 
                    maneira: "$lote_min_producao_reais / Qtde item do Or�amento" 
                    que posteriormente auxiliar� no c�lculo da Margem de Lucro ...*/

                    //Significa que est� indo pelo LM e o usu�rio realmente quer q siga esse caminho caso o Sys caia aqui ...
                    if(($campos[0]['qtde'] * $preco_custo[0]) < $lote_min_producao_reais && $campos[0]['ignorar_lote_minimo_do_grupo_faixa_orcavel'] == 'N') {
                        $preco_liq_fat_custo = $lote_min_producao_reais / $campos[0]['qtde'];
                    }else {
                        $preco_liq_fat_custo = $preco_custo[0];
                    }
                }
                
                //Verifico se existe IVA p/ o PA passado por par�metro no Estado de SP ...
                $dados_produto  = intermodular::dados_impostos_pa($campos[0]['id_produto_acabado'], 1);
                $iva_cf_uf_sp   = $dados_produto['iva_cadastrado'];
                
                /*Aqui calculamos o pre�o de compra faturado do PIPA sem os 18% de ICMS cadastrados na lista 
                de pre�o de compras, para UF = SP, pois compramos pelo supersimples pagando 0% de ICMS 
                e +/- 9,5 de % ICMST (base IVA= 55%) e vendemos sem tributar ICMS e ICMS ST para UF = SP.
                Como compramos +/- 80% SGD, vamos dizer que pagamos apenas 20% dos 9,5% = 2,0 % de ST ...

                OBSERVA��O: Essa Regra s� ser� v�lida se for com Nota Fiscal ...

                /* Somente no Grupo Pinos => 39, 40, 41, 42, 43, 44, 45, 80, 89 ...*/
                //$vetor_grupos_pas_comprado_super_simples = array(39, 40, 41, 42, 43, 44, 45, 80, 89);
                $vetor_grupos_pas_comprado_super_simples = array(0);
                
                /*Aqui calculamos o pre�o de Custo  sem os 18% de ICMS para UF = SP, pois apesar de 
                estar como industrial , simulamos que compramos pelo supersimples pagando 0% de ICMS e 
                +/- 8,5  de % ICMST (base IVA= 50%) e vendemos sem tributar ICMS e ICMS ST para UF = SP. 
                Como compramos +/- 80% SGD, vamos dizer que pagamos apenas 20% dos 8,5% =~ 2,0 % de ST ...*/
                if($id_uf_cliente == 1 && $campos[0]['operacao'] == 1 && $iva_cf_uf_sp > 0 && strtoupper($campos[0]['nota_sgd']) == 'N' && in_array($campos[0]['id_grupo_pa'], $vetor_grupos_pas_comprado_super_simples)) {
                    $preco_liq_fat_custo*= (1 - (18 - 2) / 100);
                }
            }else {//Revenda ...
                //Pega o fornecedor padrao, se PA = revenda ...
                //========>>>>> Este sql abaixo � a mesma l�gica da duncao procurar_fornecedor_default_revenda()
                $id_fornecedor = self::procurar_fornecedor_default_revenda($campos[0]['id_produto_acabado'], '', 1);

                $sql = "SELECT fpi.fator_margem_lucro_pa 
                        FROM `fornecedores_x_prod_insumos` fpi 
                        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = fpi.`id_produto_insumo` AND pa.id_produto_acabado = '".$campos[0]['id_produto_acabado']."' AND pa.`id_produto_insumo` > '0' AND pa.`ativo` = '1' AND pa.`operacao_custo` = '1' 
                        WHERE fpi.`id_fornecedor` = '$id_fornecedor' 
                        AND fpi.`ativo` = '1' LIMIT 1 ";
                $campos_fator = bancos::sql($sql);
                if(count($campos_fator) > 0) $fator_margem_lucro = $campos_fator[0]['fator_margem_lucro_pa'];
                $preco_liq_fat_custo = self::procurar_fornecedor_default_revenda($campos[0]['id_produto_acabado'], 1, '', $trazer_pco_compra_no_orc, $id_uf_cliente, '', strtoupper($campos[0]['nota_sgd']));
            }
            //Dados de ICMS e Reducao da Classificao p/ a UF do Cliente ...
            $dados_produto      = intermodular::dados_impostos_pa($campos[0]['id_produto_acabado'], 1);
            $icms_cf_uf_sp      = $dados_produto['icms_cadastrado'];
            $reducao_cf_uf_sp   = $dados_produto['reducao_cadastrado'];
            $iva_cf_uf_sp       = $dados_produto['iva_cadastrado'];
            $ICMS_COM_REDUCAO_SP= ($icms_cf_uf_sp) * (100 - $reducao_cf_uf_sp) / 100;

            if($trading == 1) {
                $desc_sgd_icms = $ICMS_COM_REDUCAO_SP;
            }else {
                /*A partir do dia 04/09/2015 os Produtos da Divis�o NVO/TDC n�o ter�o mais cr�ditos 
                de ICMS para Venda SGD pois compramos com Nota Fiscal j� pagando a ST na Compra 
                e n�o tendo direito ao Cr�dito de ICMS ...*/
                if(strtoupper($campos[0]['nota_sgd']) == 'S' && $campos[0]['id_empresa_divisao'] != 9) {//SGD e Empresa Divis�o diferente de NVO / TDC ...
                    $desc_sgd_icms = $ICMS_COM_REDUCAO_SP;//Ele � igual pq ter� desconto integral ...
                }else if(strtoupper($campos[0]['nota_sgd']) == 'S' && $campos[0]['id_empresa_divisao'] == 9) {//SGD e Empresa Divis�o = 'NVO / TDC' ...
                    $desc_sgd_icms = (0.5 * $ICMS_COM_REDUCAO_SP);//Estamos usando 50% de ICMS pois compramos 50% SGD ...
                }else {
                    $dados_produto                  = intermodular::dados_impostos_pa($campos[0]['id_produto_acabado'], $id_uf_cliente);
                    $icms_cf_uf_cliente             = $dados_produto['icms'];
                    $reducao_base_calc_uf_cliente   = $dados_produto['reducao'];
                    $ICMS_COM_REDUCAO_CLIENTE       = ($icms_cf_uf_cliente) * (100 - $reducao_base_calc_uf_cliente) / 100;
                    $desc_sgd_icms                  = $ICMS_COM_REDUCAO_SP - $ICMS_COM_REDUCAO_CLIENTE;
                }
            }
            /*�ltima modifica��o feita na listagem de Grupos e Grupos vs Empresas Divis�es no dia 17/04/2017 �s 15:53 +/- ...

            Somente nos Grupo Limas => 11, 12, 59, 74, 75, 78, 90;
            Cossinetes => 9, 38;
            Pinos => 39, 40, 41, 42, 43, 44, 45, 80, 89;

            Grupo vs Empresa Divis�o Machos Warrior => 22, 43, 83;
            Fresas de Topo => 140, 141;
            Brocas => 143, 144;
            Alargadores => 145;
            Escareadores => 146;
            Rosca Posti�a e afins => 149, 150, 160, 161 */
            
            $vetor_grupos_pas_comprado_super_simples                = array(9, 11, 12, 38, 59, 74, 75, 78, 90);
            $vetor_grupos_empresas_divisoes_comprado_super_simples  = array(22, 43, 83, 140, 141, 143, 144, 145, 146, 149, 150, 160, 161);
                
            /*

            A vari�vel $desc_sgd_icms -> deixa de assumir o Valor Antigo calculado acima p/ ser (18 - 2) ...

             * Aqui calculamos o pre�o de compra faturado do PIPA sem os 18% de ICMS cadastrados na lista 
            de pre�o de compras, para UF = SP, pois compramos pelo supersimples pagando 0% de ICMS 
            e +/- 9,5 de % ICMST (base IVA= 55%) e vendemos sem tributar ICMS e ICMS ST para UF = SP.
            Como compramos +/- 80% SGD, vamos dizer que pagamos apenas 20% dos 9,5% = 2,0 % de ST ...*/
            if($id_uf_cliente == 1 && $campos[0]['operacao'] == 1 && $iva_cf_uf_sp > 0 && strtoupper($campos[0]['nota_sgd']) == 'N' && (in_array($campos[0]['id_grupo_pa'], $vetor_grupos_pas_comprado_super_simples) || in_array($campos[0]['id_gpa_vs_emp_div'], $vetor_grupos_empresas_divisoes_comprado_super_simples))) {
                $desc_sgd_icms = (18 - 2);
            }

            //Caso o Cliente for Estrangeiro ent�o divido pelo D�lar Exporta��o ...
            if($id_pais != 31) $preco_liq_fat_custo/= genericas::moeda_dia('dolar');
            
            /*Trading / Exporta��o ... Em 05/09/18 tiramos strtoupper($campos[0]['nota_sgd']) == 'S' desta clausula, por conta do grande aumento 
            do risco fiscal e dificuldade em receber vendas SGD ...*/
            if($trading == 1 || $id_pais != 31) {
                $outros_impostos_federais = genericas::variavel(34);//Pis + cofins + csll + ir + refiz
            }else {
                $outros_impostos_federais = 0;//Pis + cofins + csll + ir + refiz
            }
            
            /*Este Pre�o Custo ML Padr�o, � o Custo descontado a diferen�a do ICMS e SGD/UF, impostos federais 
            e Diferen�a de Comiss�o, e acrescido da Taxa Financeira do Prazo M�dio do ORC ...*/
            $custo_margem_lucro_padrao = $preco_liq_fat_custo * (1 + $tx_financeira / 100) * (1 - $desc_sgd_icms / 100) * (1 - $outros_impostos_federais / 100);
            
            if($fator_margem_lucro == 0 || empty($fator_margem_lucro)) $fator_margem_lucro = 1;
                       
            //Se for CIF (POR NOSSA CONTA) a vari�vel $frete_despesas_acessorias, assume o Valor da Fun��o ...
            if($campos[0]['tipo_frete'] == 'C') {//CIF ...
                $calculo_impostos_item      = calculos::calculo_impostos($id_orcamento_venda_item, $campos[0]['id_orcamento_venda'], 'OV');
                $frete_despesas_acessorias  = $calculo_impostos_item['frete_despesas_acessorias'];
            }else {//FOB (POR CONTA DO COMPRADOR), nem chamo a fun��o de modo a agilizar o Processamento ...
                $frete_despesas_acessorias  = 0;//Assumo Valor Zero, afinal quem Paga � o Cliente ...
            }
            $custo_margem_lucro_zero_provisorio = $custo_margem_lucro_padrao / $fator_margem_lucro + ($frete_despesas_acessorias / $campos[0]['qtde']);
            
            if($custo_margem_lucro_zero_provisorio == 0) $custo_margem_lucro_zero_provisorio = 1;
            
            /*Essas vari�veis provis�rias s�o vari�veis antes de recalcularmos levando em Conta a Comiss�o Paga, 
            pois se a Comiss�o for maior que a Comiss�o Padr�o do Custo que � 3%, o Custo subir� e a 
            Margem de Lucro cair� e caso a caso a Comiss�o seja menor do que Custo Padr�o, 
            o Custo cair� e Margem de Lucro subir� ...*/
            $margem_lucro_provisoria = round(($preco_liq_final / $custo_margem_lucro_zero_provisorio - 1) * 100, 1);
           
            /*Se o Representante for "Direto", n�o existe + Comiss�o porque Direto n�o Recebe Comiss�o, corrigimos esse 
            erro a partir do dia 06/06/2014 ...*/
            if($campos[0]['id_representante'] == 1 || $campos[0]['id_representante'] == 120) {
                $comissao_provisao = 0;
            }else {//Se for outro Representante ...
                $sql = "SELECT porc_comissao_fixa 
                        FROM `representantes` 
                        WHERE `id_representante` = '".$campos[0]['id_representante']."' LIMIT 1 ";
                $campos_comissao_fixa = bancos::sql($sql);
                if($campos_comissao_fixa[0]['porc_comissao_fixa'] > 0) {//Verifica se o Rep possui Com. Fixa ...
                    $comissao_provisao = $campos_comissao_fixa[0]['porc_comissao_fixa'];
                }else {
                    if($campos[0]['queima_estoque'] == 'S') {//Se esse item do ORC estiver em Queima ...
                        $comissao_extra = genericas::variavel(46);//Taxa Fixa de Comiss�o Extra ...
                    }else {//Se esse item de ORC n�o est� em Queima ...
                        //Aqui eu busco a Comiss�o Extra do Grupo do PA, se n�o tiver expirado a Data de Emiss�o do ORC ... ...
                        $sql = "SELECT ged.comissao_extra 
                                FROM `orcamentos_vendas` ov 
                                INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_orcamento_venda = ov.id_orcamento_venda AND ovi.id_orcamento_venda_item = '$id_orcamento_venda_item' 
                                INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado 
                                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                                WHERE (ov.data_emissao <= ged.data_limite) LIMIT 1 ";
                        $campos_comissao_extra = bancos::sql($sql);
                        if(count($campos_comissao_extra) == 1) {//Se estiver tudo ok ent�o ...
                            $valores                = vendas::calcular_ml_min_pa_vs_cliente($campos[0]['id_produto_acabado'], $campos[0]['id_cliente']);
                            $margem_lucro_minima    = $valores['margem_lucro_minima'];
                            /*Tem agora uma nova Regra: Se a ML Provis�ria for abaixo que at� 10% da ML M�nima � concedido a 
                            Comiss�o Extra - limitado ao m�nimo de 45% p/ pagar a Comiss�o Extra ...*/
                            $margem_lucro_minima_limite = (0.90 * $margem_lucro_minima);
                            if($margem_lucro_minima_limite < 45) $margem_lucro_minima_limite = 45;
                            if($margem_lucro_provisoria > $margem_lucro_minima_limite) {
                                $comissao_extra = $campos_comissao_extra[0]['comissao_extra'];
                            }else {//Do contr�rio n�o concedo nada, porque o vendedor est� fazendo donativo da Mercadoria ...
                                $comissao_extra = 0;
                            }
                        }
                    }
                }
                //Sempre pego essa Coluna pq nosso Custo aqui j� inclui os 3% de encargos ...
                $sql = "SELECT `base_comis_dentro_sp` 
                        FROM `novas_comissoes_margens_lucros` 
                        WHERE `margem_lucro` < '$margem_lucro_provisoria' 
                        ORDER BY `base_comis_dentro_sp` DESC LIMIT 1 ";
                $campos_margem = bancos::sql($sql);
                if(count($campos_margem) > 0) $comissao_provisao = $campos_margem[0]['base_comis_dentro_sp'] + $comissao_extra;
            }
            /*Essa diferen�a de Comiss�o, � a Percentagem da Comiss�o que Ultrapassa os 3% que � a percentagem que 
            consideramos inclusa nos nossos custos, acrescida de 50% que s�o os encargos dos funcion�rios ou valor 
            a maior dos aut�nomos ... */
            $diferenca_comissao         = ($comissao_provisao - 3) * 1.5;
            $margem_lucro               = $margem_lucro_provisoria - $diferenca_comissao;
            $custo_margem_lucro_zero    = $preco_liq_final / (1 + $margem_lucro / 100);

            $margem_lucro               = number_format($margem_lucro, 1, ',', '.');
            $margem_lucro_desc          = $margem_lucro;
            
            return array($margem_lucro, $margem_lucro_desc, $custo_margem_lucro_zero);
        }
    }
    
    function margem_lucro_estimada($id_orcamento_venda_item) {
        //Busca de alguns dados do Or�amento ...
        $sql = "SELECT c.`id_uf`, c.`trading`, gpa.`id_familia`, ov.`id_orcamento_venda`, ov.`id_cliente`, 
                ov.`comprar_como_export`, ovi.`preco_liq_final`, pa.`id_produto_acabado`, pa.`operacao_custo`, 
                pa.`operacao_custo_sub`, pa.`discriminacao` 
                FROM `orcamentos_vendas_itens` ovi 
                INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
                INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                WHERE ovi.id_orcamento_venda_item = '$id_orcamento_venda_item' LIMIT 1 ";
        $campos = bancos::sql($sql);
        /**********************************************************************************************/
        /*Se o PA for SKIN C/CABO na discrimina��o e o PA for da fam�lia "LIMA", sempre iremos descontar do Pre�o de 
        Compra Ideal em R$ : R$ 0,15 + R$ 0,52 = R$ 0,67 (Estes valores s�o os valores calculados nos 2 itens + abaixo) ...*/
        if(strpos($campos[0]['discriminacao'], 'SKIN C/CABO') !== false && $campos[0]['id_familia'] == 3) {
            $acrescimo_acessorio    = 0.67;
            /*Se o PA tiver SKIN na discrimina��o e o PA for da fam�lia "LIMA", sempre iremos descontar do Pre�o de 
            Compra Ideal em R$ analogamente p.custo ML Zero R$ 0,17 * 0,9 * 0,97 = R$ 0,15 ...*/
        }else if(strpos($campos[0]['discriminacao'], 'SKIN') !== false && $campos[0]['id_familia'] == 3) {
            $acrescimo_acessorio    = 0.15;
            /*Se o PA for Com Cabo na discrimina��o e o PA for da fam�lia "LIMA", sempre iremos descontar do 
            Pre�o de Compra Ideal em R$ analogamente p.custo ML Zero (R$ 0,31 M. Obra Cabo + R$ 0,29 do Pre�o M�dio 
            dos Cabos) * 0,9 * 0,97 = R$ 0,52 ...*/
        }else if(strpos($campos[0]['discriminacao'], 'C/CABO') !== false && $campos[0]['id_familia'] == 3) {
            $acrescimo_acessorio    = 0.52;
        }else {
            $acrescimo_acessorio    = 0;
        }
        
        $tx_financeira  = self::calculo_taxa_financeira($campos[0]['id_orcamento_venda']);
        $margem         = custos::margem_lucro($id_orcamento_venda_item, $tx_financeira, $campos[0]['id_uf'], $campos[0]['preco_liq_final']);
        $margem_lucro   = $margem[1];
        
        if($campos[0]['operacao_custo'] == 0 && $campos[0]['operacao_custo_sub'] == 0) {//OC => Industrial e Sub-OC => Industrial ...
            $margem_lucro_estimada  = str_replace(',', '.', $margem_lucro);
        }else {
            if($campos[0]['operacao_custo'] == 0 && $campos[0]['operacao_custo_sub'] == 1) {//OC => Industrial e Sub-OC => Revenda ...
                self::busca_primeiro_pa_revenda_atrelado_na_7etapa($campos[0]['id_produto_acabado'], $campos[0]['operacao_custo'], $campos[0]['id_familia']);
                $id_produto_acabado = $GLOBALS['id_produto_acabado'];
            }else if($campos[0]['operacao_custo'] == 1) {//Revenda ...
                $id_produto_acabado = $campos[0]['id_produto_acabado'];
            }
            //Verifico se esse PA � um PI ...
            $sql = "SELECT pi.`id_produto_insumo`, pi.`qtde_total_compras_ml_est`, pi.`preco_compra_medio_corr_ml_est`, 
                    pi.`qtde_total_pendencias_ml_est`, pi.`preco_pendencias_medio_corr_ml_est`, 
                    pi.`data_ultima_atualizacao_ml_est` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = pa.`id_produto_insumo` 
                    WHERE pa.`id_produto_acabado` = '$id_produto_acabado' 
                    AND pa.`id_produto_insumo` > '0' 
                    AND pa.`ativo` = '1' LIMIT 1 ";
            $campos_pi              = bancos::sql($sql);
            //N�o fa�o IF para saber se realmente � um PIPA ...
            $id_fornecedor_default  = self::procurar_fornecedor_default_revenda($id_produto_acabado, '', 1);

            //Busca do Pre�o na Lista de Pre�o do PI e do Fornecedor Default ...
            $sql = "SELECT f.`id_pais`, fpi.`preco`, fpi.`preco_exportacao` 
                    FROM `fornecedores_x_prod_insumos` fpi 
                    INNER JOIN `fornecedores` f ON f.`id_fornecedor` = fpi.`id_fornecedor` 
                    WHERE fpi.`id_fornecedor` = '$id_fornecedor_default' 
                    AND fpi.`id_produto_insumo` = '".$campos_pi[0]['id_produto_insumo']."' LIMIT 1 ";
            $campos_lista = bancos::sql($sql);
            if($id_fornecedor_default == 146) {//Se o Fornecedor for Hispania traz o Pre�o Nacional ...
                //Se tiver como Comprar como Export no Or�amento, Cliente Estrangeiro ou cadastro do Cliente = Trading, trazer Export ...
                if($campos[0]['comprar_como_export'] == 'S' || $campos[0]['trading'] == 1) {//Hoje essa marca��o s� serve p/ a Hispania ...
                    $preco_compra_lista = $campos_lista[0]['preco_exportacao'];
                }else {
                    $preco_compra_lista = $campos_lista[0]['preco'];
                }
            }else {//Se o pa�s do Fornecedor for Brasil, ent�o fa�o com que o Sistema interprete a Moeda como sendo em R$ ...
                if($campos_lista[0]['id_pais'] == 31) {
                    $preco_compra_lista = $campos_lista[0]['preco'];
                }else {//Se o Pa�s for Estrangeiro, ir� utilizar o Pre�o Estrangeiro ...
                    $preco_compra_lista = $campos_lista[0]['preco_exportacao'];
                }
            }
            $preco_lista_mais_acessorio = $preco_compra_lista + $acrescimo_acessorio;
            
            /*Essa vari�vel "$fator_taxa_financeira_corr" vai corrigir somente os Pre�os M�dios de Compras do PI 
            na data que foram geradas p/ a Data Atual ...*/
            $vetor_data                     = data::diferenca_data($campos_pi[0]['data_ultima_atualizacao_ml_est'], date('Y-m-d'));
            $dias_decorridos                = $vetor_data[0];
            $taxa_financeira_calculo_ml_est = genericas::variavel(62);
            $taxa_financeira_dias_decorridos= $taxa_financeira_calculo_ml_est / 30 * $dias_decorridos;
            $fator_taxa_financeira_corr     = (1 + $taxa_financeira_dias_decorridos / 100);
            $custo_ml_zero_preco_venda_orc  = $campos[0]['preco_liq_final'] / (1 + str_replace(',', '.', $margem_lucro) / 100);

            $preco_compra_medio_corr_ml_est = $campos_pi[0]['preco_compra_medio_corr_ml_est'] * $fator_taxa_financeira_corr;
            $preco_compra_medio_corr_ml_est_mais_acessorio = $preco_compra_medio_corr_ml_est + $acrescimo_acessorio;
            
            $custo_ml_zero_compras          = $custo_ml_zero_preco_venda_orc / $preco_lista_mais_acessorio * $preco_compra_medio_corr_ml_est_mais_acessorio;
            $ml_est_compras                 = ($custo_ml_zero_compras == 0) ? 0 : round(($campos[0]['preco_liq_final'] / $custo_ml_zero_compras - 1) * 100, 2);
            
            $preco_pendencia_medio_corr_ml_est  = $campos_pi[0]['preco_pendencias_medio_corr_ml_est'];
            $preco_pendencia_medio_corr_ml_est_mais_acessorio = $preco_pendencia_medio_corr_ml_est + $acrescimo_acessorio;
            
            $custo_ml_zero_pendencias       = $custo_ml_zero_preco_venda_orc / $preco_lista_mais_acessorio * $preco_pendencia_medio_corr_ml_est_mais_acessorio;
            $ml_est_pendencias              = ($custo_ml_zero_pendencias == 0) ? 0 : round(($campos[0]['preco_liq_final'] / $custo_ml_zero_pendencias - 1) * 100, 2);
            
            $preco_medio_corr_global        = ($campos_pi[0]['qtde_total_compras_ml_est'] * $preco_compra_medio_corr_ml_est_mais_acessorio + $campos_pi[0]['qtde_total_pendencias_ml_est'] * $preco_pendencia_medio_corr_ml_est_mais_acessorio) / ($campos_pi[0]['qtde_total_compras_ml_est'] + $campos_pi[0]['qtde_total_pendencias_ml_est']);
            $custo_ml_zero_global           = $custo_ml_zero_preco_venda_orc / $preco_lista_mais_acessorio * $preco_medio_corr_global;
            $ml_est_global                  = ($custo_ml_zero_global == 0) ? 0 : round(($campos[0]['preco_liq_final'] / $custo_ml_zero_global - 1) * 100, 2);
            
            /******Vari�veis que nos auxiliar�o para que nos ajudar confrontar os c�lculos desta fun��o com a Tela******/
            /*echo '<br/>Pre�o L�q Final do Orc '.$campos[0]['preco_liq_final'];
            echo '<br/>Margem de Lucro do Orc '.$margem_lucro;
            echo '<br/>Pre�o Compra de Lista '.$preco_compra_lista;
            echo '<br/>Acr�scimo Acess�rio '.$acrescimo_acessorio;
            echo '<br/>Qtde Compras ML Est '.$campos_pi[0]['qtde_total_compras_ml_est'];
            echo '<br/>Pre�o Compras M�dio Corr ML Est '.$campos_pi[0]['preco_compra_medio_corr_ml_est'];
            echo '<br/>Qtde Pend�ncias ML Est '.$campos_pi[0]['qtde_total_pendencias_ml_est'];
            echo '<br/>Pre�o Pend�ncias M�dio Corr ML Est '.$campos_pi[0]['preco_pendencias_medio_corr_ml_est'];*/
            /***********************************************************************************************************/

            //Controle para saber qual vari�vel retornar ...
            $margem_lucro_estimada      = 0;

            if($ml_est_compras != 0 && $ml_est_pendencias != 0) {
                $margem_lucro_estimada  = $ml_est_global;
            }else if($ml_est_compras != 0 && $ml_est_pendencias == 0) {
                $margem_lucro_estimada  = $ml_est_compras;
            }else if($ml_est_compras == 0 && $ml_est_pendencias != 0) {
                $margem_lucro_estimada  = $ml_est_pendencias;
            }else {
                $margem_lucro_estimada  = str_replace(',', '.', $margem_lucro);
            }
            /*Esse truque � para que o usu�rio realmente libere um Pedido em que a sua ML Estimada tenha 
            Valor = '0' no Cabe�alho - 19/08/2014 ...*/
            if($margem_lucro_estimada == 0) $margem_lucro_estimada = 0.1;
        }
        //Enquanto este item � foi importado totalmente p/ Pedido, vou atualizando a ML Estimada ...
        $sql = "UPDATE `orcamentos_vendas_itens` SET `margem_lucro_estimada` = '".round($margem_lucro_estimada, 1)."' WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' AND `status` < '2' LIMIT 1 ";
        bancos::sql($sql);
        return $margem_lucro_estimada;
    }
    
    /*Fun��o que atualiza o Custos de todos os Or�amentos Descongelados que est�o dentro do Prazo de Validade 
    e que cont�m o PA passado por par�metro ...
    ***** Esse par�metro $id_fornecedor_prod_insumo, s� ter� algum valor quando o Usu�rio alterar o Custo Revenda ...*/
    function atualizar_custos_orcs_descongelados($id_produto_acabado, $url_remetente, $valor, $id_fornecedor_prod_insumo = 0) {
        if(!class_exists('vendas')) require 'vendas.php';//CASO EXISTA EU DESVIO A CLASSE ...
        //Busco todos os ORC(s) n�o congelados que est�o vinculados a esse PA passado por par�metro ...
        $sql = "SELECT ov.`id_orcamento_venda`, ovi.`id_orcamento_venda_item` 
                FROM `orcamentos_vendas_itens` ovi 
                INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` AND ov.congelar = 'N' 
                WHERE ovi.`id_produto_acabado` = '$id_produto_acabado' ORDER BY data_emissao DESC ";
        $campos_itens = bancos::sql($sql);
        $linhas_itens = count($campos_itens);
        for($i = 0; $i < $linhas_itens; $i++) {
            $vetor_dados_gerais     = vendas::dados_gerais_orcamento($campos_itens[$i]['id_orcamento_venda']);
            //Se o Or�amento ainda est� dentro do Prazo de Validade, executo essa fun��o abaixo p/ atualizar o Custo e a Comiss�o do Representante ...
            if($vetor_dados_gerais['data_validade_orc'] >= date('Y-m-d')) {
                /*Fun��o pesad�ssima que verifica o Custo do Produto Acabado, Comiss�o do Representante p/ o determinado 
                Item de Or�amento, sendo executada desse jeito por item, a mesma j� fica um pouco mais leve ...*/
                vendas::calculo_preco_liq_final_item_orc($campos_itens[$i]['id_orcamento_venda_item'], 'S');
//Aqui eu atualizo a ML Est do Iem do Or�amento ...
                custos::margem_lucro_estimada($campos_itens[$i]['id_orcamento_venda_item']);
/*************Rodo a fun��o de Comiss�o depois de ter gravado a ML Estimada*************/
                vendas::calculo_ml_comissao_item_orc($campos_itens[$i]['id_orcamento_venda'], $campos_itens[$i]['id_orcamento_venda_item']);
            }
        }
        $preco_fat_nac_nac_min_rs = self::preco_custo_pa($id_produto_acabado);
        //Gravo o "custo_ml60" na tabela do Produto Acabado que foi passado por par�metro ...
        $sql = "UPDATE `produtos_acabados` SET `custo_ml60` = '$preco_fat_nac_nac_min_rs' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        bancos::sql($sql);
?>
        <Script Language = 'JavaScript'>
            window.location = '<?=$url_remetente;?>&valor=<?=$valor;?>'
        </Script>
<?
    }
    
    /*Esta fun��o tem por objetivo verificar se o PA passado por par�metro possui um outro PA em sua 7� Etapa do Custo 
    da mesma fam�lia passada por par�metro, se encontrar traz esse PA e pronto. 

    Exemplo: LE-301s passado por par�metro � o Produto Acabado Principal, por�m ele � derivado do LE-301 e chegando nesse 
    conseguimos visualizar as Compras e Pend�ncias do mesmo ...

    N�o segue a hierarquia como nas outras fun��es que continuam com a sua recursividade, ela � utilizada para c�lculo 
    da ML Estimada ...*/
    function busca_primeiro_pa_revenda_atrelado_na_7etapa($id_produto_acabado, $operacao_custo, $id_familia) {
        //Aqui eu busco o Custo do PA passado por par�metro ...
        $sql = "SELECT id_produto_acabado_custo 
                FROM `produtos_acabados_custos` 
                WHERE `id_produto_acabado` = '$id_produto_acabado' 
                AND `operacao_custo` = '$operacao_custo' ";
        $campos_custo = bancos::sql($sql);
        //Com o id_produto_acabado_custo eu pego o id_pa da 7� Etapa desde que seja Revenda ...
        $sql = "SELECT pp.id_produto_acabado, pa.operacao_custo 
                FROM `pacs_vs_pas` pp 
                INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pp.id_produto_acabado 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` = '$id_familia' 
                WHERE pp.`id_produto_acabado_custo` = '".$campos_custo[0]['id_produto_acabado_custo']."' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {//Se encontrou um PA atrelado a este na 7� Etapa, retorno o mesmo e acabou ...
            if($campos[0]['operacao_custo'] == 0) {
                self::busca_primeiro_pa_revenda_atrelado_na_7etapa($campos[0]['id_produto_acabado'], $campos[0]['operacao_custo'], $id_familia);
            }else {//Se for Revenda, j� retorno o mesmo porque da� j� serve ...
                /*Tive que colocar essa vari�vel id_produto_acabado como $GLOBALS para que esse valor fosse enxergue 
                em outros locais, devido o lance da recursividade ...*/
                $GLOBALS['id_produto_acabado'] = $campos[0]['id_produto_acabado'];
                return $GLOBALS['id_produto_acabado'];
            }
        }else {//Se n�o encontrou nada, significa que este PA � o �ltimo N�vel de toda a cadeia de todas as 7� Etapas ...
            /*Tive que colocar essa vari�vel id_produto_acabado como $GLOBALS para que esse valor fosse enxergue 
            em outros locais, devido o lance da recursividade ...*/
            $GLOBALS['id_produto_acabado'] = $id_produto_acabado;
            return $GLOBALS['id_produto_acabado'];
        }
    }

    /*Fun��o cujo objetivo � verificar se o componente com o qual est� sendo inserido 
    j� n�o est� selecionado como produto acabado ... */
    function vasculhar_pa($id_produto_acabado_principal, $id_produto_acabado_corrent) {//$id_produto_acabado_corrent => quero incluir
        //Aqui eu verifico de cara se o produto da combo � igual ao q est� embaixo ...
        if($id_produto_acabado_corrent == $id_produto_acabado_principal) {
            return 1;//significa q nao pode ser atrelado
        }else { //N�o � igual
            /* Busco aqui o id_produto_acabado_custo referente ao produto q est� no loop,
            para poder saber se neste existem outros produtos acabados atrelados*/
            $sql = "SELECT pac.id_produto_acabado_custo 
                    FROM `produtos_acabados_custos` pac 
                    INNER JOIN `produtos_acabados` pa ON pa.operacao_custo = pac.`operacao_custo` AND pa.`id_produto_acabado` = pac.`id_produto_acabado` 
                    WHERE pac.`id_produto_acabado` = '$id_produto_acabado_corrent' LIMIT 1 ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
            if($linhas > 0) {//Entra somente se for maior q 0
                $id_produto_acabado_custo_loop = $campos[0]['id_produto_acabado_custo'];
                //Verifica se o produto_acabado_custo possui produtos acabados atrelados ...
                $sql = "SELECT id_produto_acabado 
                        FROM `pacs_vs_pas` 
                        WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo_loop' ";
                $campos_etapa7 = bancos::sql($sql);
                $linhas_etapa7 = count($campos_etapa7);
                if($linhas_etapa7 > 0) {//Entra somente se for maior q 0
                    for($i = 0; $i < $linhas_etapa7; $i++) {//Disparo do Loop e acumula os PA no array ...
                        if($campos_etapa7[$i]['id_produto_acabado'] == $id_produto_acabado_principal) {
                            return 1;//significa q nao pode ser atrelado
                        }else {//N�o � igual
                            $retorno = self::vasculhar_pa($id_produto_acabado_principal, $campos_etapa7[$i]['id_produto_acabado']);
                            if($retorno == 1) {
                                /*Existe este macete pois a funcao retornava NULL da subfun��o e parava o c�digo sozinho, 
                                dai ele n�o lia at� o fim da fun��o, nunca tirar este macete desta funcao. ...*/
                                return 1;
                            }
                        }
                    }
                }
            }
        }
    }
    /**************************************************************************/
    /*******************************Custo Padr�o*******************************/
    /**************************************************************************/
    //A partir daqui essas fun��es servem para calcular o "Custo Padr�o" ...
    function dados_pa_para_custo_padrao($id_produto_acabado_custo) {
        /*Dependendo do local onde for acessada essa biblioteca, nem sempre teremos a vari�vel 
        $id_produto_acabado_custo ...*/
        if($id_produto_acabado_custo > 0) {
            //Busca de alguns dados de Custo do $id_produto_acabado_custo passado por par�metro, independente da Etapa ...
            $sql = "SELECT ged.`id_gpa_vs_emp_div`, ged.`id_grupo_pa`, pac.id_produto_insumo, pac.qtde_lote, 
                    pac.comprimento_1 
                    FROM `produtos_acabados_custos` pac 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    WHERE pac.`id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
            $campos = bancos::sql($sql);
            //Se existir Mat�ria Prima atrelada a 2� Etapa do Custo do PA, ent�o busco algumas informa��es a mais ...
            if($campos[0]['id_produto_insumo'] > 0) {
                //Busca da Qualidade do A�o do PI ...
                $sql = "SELECT pia.`geometria_aco`, pia.`bitola1_aco`, pia.`bitola2_aco`, qa.`id_qualidade_aco`, qa.`nome` 
                        FROM `produtos_insumos_vs_acos` pia 
                        INNER JOIN `qualidades_acos` qa ON qa.`id_qualidade_aco` = pia.`id_qualidade_aco` 
                        WHERE pia.`id_produto_insumo` = '".$campos[0]['id_produto_insumo']."' LIMIT 1 ";
                $campos_qualidade_aco = bancos::sql($sql);
            }
        }
        /********************Mudan�a feita no Dia 24/02/2017********************/
        /*Se o Grupo retornado for "Cossinetes TOP" 38, ent�o transformo em Cossinetes Manual "9" p/ o Roberto n�o ter que cadastrar 
        as mesmas m�quinas desse Grupo no outro, � o mesmo racioc�nio */
        if($campos[0]['id_grupo_pa'] == 38) $campos[0]['id_grupo_pa'] = 9;
        if($campos[0]['id_gpa_vs_emp_div'] == 46) $campos[0]['id_gpa_vs_emp_div'] = 17;//Para a divis�o NVO ...
        
        return array('id_gpa_vs_emp_div' => $campos[0]['id_gpa_vs_emp_div'], 'id_grupo_pa' => $campos[0]['id_grupo_pa'], 'qtde_lote' => $campos[0]['qtde_lote'], 'comprimento_peca' => $campos[0]['comprimento_1'], 'id_qualidade_aco' => $campos_qualidade_aco[0]['id_qualidade_aco'], 'qualidade_aco' => $campos_qualidade_aco[0]['nome'], 'geometria_aco' => $campos_qualidade_aco[0]['geometria_aco'], 'bitola1_aco' => $campos_qualidade_aco[0]['bitola1_aco'], 'bitola2_aco' => $campos_qualidade_aco[0]['bitola2_aco']);
    }
    
    //Observa��o: a 3� vari�vel do Escopo $diametro_aco varia muito de acordo com o Grupo do PA ...
    function dados_maquina_para_custo_padrao($id_gpa_vs_emp_div, $id_maquina, $diametro_aco) {
        /*O setup da m�quina sempre � buscado a parte porque nunca dependemos das vari�veis $id_gpa_vs_emp_div 
        e $diametro_aco para busca dessa informa��o ...*/
        $sql = "SELECT setup 
                FROM `maquinas` 
                WHERE `id_maquina` = '$id_maquina' LIMIT 1 ";
        $campos_maquina = bancos::sql($sql);
        //Busco o pecas_hora de acordo com o $id_gpa_vs_emp_div, $id_maquina e $diametro_aco passados por par�metro ...
        $sql = "SELECT pecas_hora 
                FROM `gpas_vs_emps_divs_vs_maquinas` 
                WHERE `id_gpa_vs_emp_div` = '$id_gpa_vs_emp_div' 
                AND `id_maquina` = '$id_maquina' 
                AND `diametro_aco_menor_igual` >= '$diametro_aco' ORDER BY `id_gpa_vs_emp_div_vs_maquina` LIMIT 1 ";
        $campos = bancos::sql($sql);
        return array('pecas_hora' => $campos[0]['pecas_hora'], 'setup' => $campos_maquina[0]['setup']);
    }
    
    function calculo_horas($id_produto_acabado_custo, $id_maquina, $diametro_aco, $diametro_menor = 0) {
        $vetor_valores_pa   = self::dados_pa_para_custo_padrao($id_produto_acabado_custo);
        $id_gpa_vs_emp_div  = $vetor_valores_pa['id_gpa_vs_emp_div'];
        $id_qualidade_aco   = $vetor_valores_pa['id_qualidade_aco'];
        $qualidade_aco      = $vetor_valores_pa['qualidade_aco'];
        $id_grupo_pa        = $vetor_valores_pa['id_grupo_pa'];
        $qtde_lote          = $vetor_valores_pa['qtde_lote'];
        $comprimento_peca   = $vetor_valores_pa['comprimento_peca'];
        $bitola1_aco        = $vetor_valores_pa['bitola1_aco'];
        /**********************************************************************/
        /**************************Grupo de Cossinetes*************************/
        /**********************************************************************/
        if($id_grupo_pa == 9) {//Cossinetes Manual ...
            /*Aqui eu trago a Qtde de Pe�as por Hora de acordo com a vari�vel $bitola1_aco do PA ...
            Observa��o: Nesse Grupo a "Bitola 1" do PI � o pr�prio Di�metro do A�o ...*/
            $vetor_valores_maquina  = self::dados_maquina_para_custo_padrao($id_gpa_vs_emp_div, $id_maquina, $bitola1_aco);
            $pecas_hora             = $vetor_valores_maquina['pecas_hora'];
            $setup                  = $vetor_valores_maquina['setup'];
            $tempo_horas            = $qtde_lote / $pecas_hora + $setup;
        /**********************************************************************/
        /****************************Grupo de Pinos****************************/
        /**********************************************************************/
        }else if($id_grupo_pa == 39 || $id_grupo_pa == 45) {//Pinos DIN 1 ou Pinos 1:50 ou Pinos 1:48 ...
            /*Aqui eu trago a Qtde de Pe�as por Hora de acordo com a vari�vel $bitola1_aco do PA ...
            Observa��o: Nesse Grupo a "Bitola 1" do PI � o pr�prio Di�metro do A�o ...*/
            $vetor_valores_maquina  = self::dados_maquina_para_custo_padrao($id_gpa_vs_emp_div, $id_maquina, $bitola1_aco);
            $pecas_hora             = $vetor_valores_maquina['pecas_hora'];
            $setup                  = $vetor_valores_maquina['setup'];
            
            //Busca de dados p/ calcular a vari�vel "$fator" que ser� utilizada no c�lculo mais abaixo ...
            $sql = "SELECT perc_tempo_a_mais 
                    FROM `custos_qualidades_acos_vs_tempos_usinagens` 
                    WHERE `id_gpa_vs_emp_div` = '$id_gpa_vs_emp_div' 
                    AND `id_qualidade_aco` = '$id_qualidade_aco' LIMIT 1 ";
            $campos     = bancos::sql($sql);
            $fator_aco  = $campos[0]['perc_tempo_a_mais'] / 100 + 1;
            
            //Busca de dados p/ calcular a vari�vel "$fator_parte_conica" que ser� utilizada no c�lculo mais abaixo ...
            $sql = "SELECT perc_tempo_a_mais 
                    FROM `custos_tempos_pinos` 
                    WHERE `id_maquina` = '$id_maquina' 
                    AND `variacao_diametro_pino_conico` <= '".($diametro_aco - $diametro_menor)."' ORDER BY perc_tempo_a_mais DESC LIMIT 1 ";
            $campos             = bancos::sql($sql);
            $fator_parte_conica = $campos[0]['perc_tempo_a_mais'] / 100 + 1;

            if($id_maquina == 2) {//ESMERIL ...
                if($comprimento_peca <= 70 && ($qualidade_aco == 1113 || $qualidade_aco == 1020)) {
                    $tempo_horas = 0;
                }else {
                    $tempo_horas = round($qtde_lote / $pecas_hora, 1);
                    if($qtde_lote >= 5000) $tempo_horas*= 0.90;//Aqui o comprometimento do operador � >, por isso desse desconto de 10% ...
                    $tempo_horas+= $setup;
                }
            }else if($id_maquina == 12) {//RETIFICA CENTERLESS ...
                //Somente nesse caso que eu sobreponho o valor que foi encontrado acima no Banco de Dados ...
                if(($diametro_aco - $diametro_menor) > 2 && $diametro_menor >= 10) $fator_parte_conica = 0.2;
                $tempo_horas    = $fator_parte_conica * $qtde_lote / $pecas_hora;
                //Existe "INOX" na Qualidade do A�o ent�o ...
                if(strpos($qualidade_aco, 'INOX') !== false) $tempo_horas*= 2;
                if($qtde_lote >= 5000) $tempo_horas*= 0.90;//Aqui o comprometimento do operador � >, por isso desse desconto de 10% ...
                $tempo_horas+= $setup;
            }else if($id_maquina == 17) {//TORNO TRAUB ...
                if($comprimento_peca > 120) {
                    $tempo_horas    = 0;
                }else {
                    $tempo_horas    = round($qtde_lote / $pecas_hora  * $fator_aco, 1);
                    if($qtde_lote >= 5000) $tempo_horas*= 0.90;//Aqui o comprometimento do operador � >, por isso desse desconto de 10% ...
                    $tempo_horas+= $setup;
                }
            }else if($id_maquina == 25) {//TORNO CNC NARDINI ...
                if(($diametro_aco - $diametro_menor) <= 2) {
                    $tempo_horas = 0;
                }else {
                    $tempo_horas    = $fator_parte_conica * $qtde_lote / $pecas_hora * $fator_aco;
                }
                if($qtde_lote >= 500) $tempo_horas*= 0.90;//O CNC vai usar apenas 10% do Lote M�nimo Ideal ...
                $tempo_horas+= $setup;
            }
        }
        return round($tempo_horas, 1);//Sempre retorna arredondado p/ 1 casa decimal ...
    }
    /**************************************************************************/
}