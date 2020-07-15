<?
if(!class_exists('bancos')) require 'bancos.php';//CASO EXISTA EU DESVIO A CLASSE ...
//Fico indignado com a minha técnica em orientação a objetos, sem palavras
class intermodular {
    function importar_patopi($id_produto_acabado) {
//Aqui eu verifico se o PA já foi importado alguma vez p/ PI ...
        $sql = "SELECT `id_produto_insumo` 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '$id_produto_acabado' 
                AND `id_produto_insumo` > '0' 
                AND `ativo` = '1' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 0) {//Nunca foi importado ...
            $sql = "SELECT `id_unidade`, `referencia`, `discriminacao`, `observacao` 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            $campos_pa      = bancos::sql($sql);
            $id_unidade     = $campos_pa[0]['id_unidade'];
            $discriminacao  = AddSlashes($campos_pa[0]['discriminacao']);
            $observacao     = AddSlashes($campos_pa[0]['observacao']);
            $data_sys       = date('Y-m-d H:i:s');
            //Gera um novo PI através do PA ...
            $sql = "INSERT INTO `produtos_insumos` (`id_produto_insumo`, `id_unidade`, `estocagem`, `discriminacao`, `id_grupo`, `data_sys`, `observacao`, `ativo`) VALUES (NULL, '$id_unidade', 'S', '$discriminacao', '9', '$data_sys', '$observacao', 1) ";
            bancos::sql($sql);
            $id_produto_insumo = bancos::id_registro();
            //Atualizo a Tabela de Produtos Acabados com o 'id_produto_insumo' equivalente "PIPA" ...
            $sql = "UPDATE `produtos_acabados` SET `id_produto_insumo` = '$id_produto_insumo' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            bancos::sql($sql);
        }
        return $id_produto_insumo;
    }

    function incluir_varios_pi_fornecedor($id_fornecedor, $id_produto_insumo) {
        $sql = "SELECT `id_fornecedor_prod_insumo`, `ativo` 
                FROM `fornecedores_x_prod_insumos` 
                WHERE `id_fornecedor` = '$id_fornecedor' 
                AND `id_produto_insumo` = '$id_produto_insumo' 
                AND `ativo` = '1' LIMIT 1 ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas == 1) $id_fornecedor_prod_insumo = $campos[0]['id_fornecedor_prod_insumo'];
        if($linhas == 0) {//Aqui eu atrelo o $id_fornecedor passado ao $id_produto_insumo por parâmetro na Lista de Preço com a Data Atual ...
            $sql = "INSERT INTO `fornecedores_x_prod_insumos` (`id_fornecedor_prod_insumo`, `id_produto_insumo`, `id_fornecedor`, `fator_margem_lucro_pa`, `data_sys`) VALUES (NULL, '$id_produto_insumo', '$id_fornecedor', '".genericas::variavel(22)."', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
            $id_fornecedor_prod_insumo = bancos::id_registro();
            return $id_fornecedor_prod_insumo;
        }else {
            $ativo = $campos[0]['ativo'];
            if($ativo == 0) {//Já exista como inativo, e voltou a reativar esse item com a Data Atual ...
                $sql = "UPDATE `fornecedores_x_prod_insumos` SET `data_sys` = '".date('Y-m-d H:i:s')."', `ativo` = '1' WHERE `id_fornecedor_prod_insumo` = '$id_fornecedor_prod_insumo' LIMIT 1 ";
                bancos::sql($sql);
                return $id_fornecedor_prod_insumo;
            }else {//Já existe o produto
                return 0;
            }
        }
    }

    function excluir_varios_pi_fornecedor($id_fornecedor_prod_insumo) {
        //Além de eu desatrelar o Fornecedor do PI, eu também já zero os preços deste Fornec na lista de Preço ...
        $sql = "UPDATE `fornecedores_x_prod_insumos` SET `preco_faturado` = '0.00', `preco_faturado_export` = '0.00', `ativo` = 0 where id_fornecedor_prod_insumo = '$id_fornecedor_prod_insumo' LIMIT 1 ";
        bancos::sql($sql);
    }

    function pa_discriminacao($id_produto_acabado=0, $mostrar=1, $mostrar_status=1, $mostra_status_nao_produzir=1, $id_produto_acabado_discriminacao=0, $pdf=0) {
/********************************Busca de Dados do P.A. Principal********************************/
        $sql = "SELECT pa.`operacao_custo`, pa.`codigo_fornecedor`, pa.`referencia`, pa.`discriminacao`, 
                pa.`status_custo`, pa.`status_nao_produzir`, pa.`ativo`, u.`sigla` 
                FROM `produtos_acabados` pa 
                INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
                WHERE pa.`id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        $campos                 = bancos::sql($sql);
//Dados Referente ao Produto Acabado Principal ...
        $operacao_custo         = $campos[0]['operacao_custo'];
        $codigo_fornecedor      = $campos[0]['codigo_fornecedor'];
        $referencia             = $campos[0]['referencia'];
        $discriminacao          = $campos[0]['discriminacao'];
        $discriminacao          = str_replace('% ', '%', $discriminacao);//Aqui eu retiro o espaço q está entre a % e a Sigla de Co
        $status_custo           = $campos[0]['status_custo'];
        $status_nao_produzir    = $campos[0]['status_nao_produzir'];
        $unidade                = $campos[0]['sigla'];
        $ativo                  = $campos[0]['ativo'];
/******************************Busca de Dados do Custo do P.A. Principal*****************************/
        $sql = "SELECT REPLACE(f.`nome`, ' ', '_') AS funcionario_alterou_custo, 
                REPLACE(CONCAT(DATE_FORMAT(SUBSTRING(pac.`data_sys`, 1, 10), '%d/%m/%Y'), ' às', SUBSTRING(pac.`data_sys`, 11, 9)), ' ', '_') AS data_atualizacao 
                FROM `produtos_acabados_custos` pac 
                LEFT JOIN `funcionarios` f ON f.`id_funcionario` = pac.`id_funcionario` 
                WHERE pac.`id_produto_acabado` = '$id_produto_acabado' 
                AND pac.`operacao_custo` = '$operacao_custo' LIMIT 1 ";
        $campos_custo               = bancos::sql($sql);
        $funcionario_alterou_custo1 = $campos_custo[0]['funcionario_alterou_custo'];
        $data_atualiazacao1         = $campos_custo[0]['data_atualizacao'];
/********************************Busca de Dados do P.A. Discriminação********************************/
        $sql = "SELECT pa.`operacao_custo`, pa.`codigo_fornecedor`, pa.`referencia`, pa.`discriminacao`, 
                pa.`status_custo`, pa.`status_nao_produzir`, pa.`ativo`, u.`sigla` 
                FROM `produtos_acabados` pa 
                INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
                WHERE pa.`id_produto_acabado` = '$id_produto_acabado_discriminacao' LIMIT 1 ";
        $campos                 = bancos::sql($sql);
//Dados Referente ao Produto Acabado Discriminação ...
        $operacao_custo2        = $campos[0]['operacao_custo'];
        $codigo_fornecedor2     = $campos[0]['codigo_fornecedor'];
        $referencia2            = $campos[0]['referencia'];
        $discriminacao2         = $campos[0]['discriminacao'];
        $discriminacao2         = str_replace('% ', '%', $discriminacao2);//Aqui eu retiro o espaço q está entre a % e a Sigla de Co
        $status_custo2          = $campos[0]['status_custo'];
        $status_nao_produzir2   = $campos[0]['status_nao_produzir'];
        $unidade2               = $campos[0]['sigla'];
/******************************Busca de Dados do Custo do P.A. Principal*****************************/
        $sql = "SELECT REPLACE(f.`nome`, ' ', '_') AS funcionario_alterou_custo, 
                REPLACE(CONCAT(DATE_FORMAT(SUBSTRING(pac.`data_sys`, 1, 10), '%d/%m/%Y'), ' às', SUBSTRING(pac.`data_sys`, 11, 9)), ' ', '_') AS data_atualizacao 
                FROM `produtos_acabados_custos` pac 
                LEFT JOIN `funcionarios` f ON f.`id_funcionario` = pac.`id_funcionario` 
                WHERE pac.`id_produto_acabado` = '$id_produto_acabado_discriminacao' 
                AND pac.`operacao_custo` = '$operacao_custo2' LIMIT 1 ";
        $campos_custo               = bancos::sql($sql);
        $funcionario_alterou_custo2 = $campos_custo[0]['funcionario_alterou_custo'];
        $data_atualiazacao2         = $campos_custo[0]['data_atualizacao'];
/********************************Busca da Qualidade de Aço do P.A. Principal********************************/
        //O sistema irá pegar a Qualidade do aço do PA desde de que sua OC seja igual a OC do Custo ...
        $sql = "SELECT qa.`nome` 
                FROM `produtos_acabados_custos` pac 
                INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = pac.`id_produto_insumo` 
                INNER JOIN `produtos_insumos_vs_acos` pia ON pia.`id_produto_insumo` = pi.`id_produto_insumo` 
                INNER JOIN `qualidades_acos` qa ON qa.`id_qualidade_aco` = pia.`id_qualidade_aco` 
                WHERE pac.`id_produto_acabado` = '$id_produto_acabado' 
                AND pac.`operacao_custo` = '$operacao_custo' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) > 0) {
//Qualidade do Aço Referente ao Produto Acabado Principal ...
            $qualidade_aco = "<font color='blue'>".' ('.$campos[0]['nome'].')'.'</font>';
//Aqui eu verifico com é a qualidade do Co
            $qualidade_cobalto = strtok($campos[0]['nome'], '%');
            if($qualidade_cobalto == 5) {
                $discriminacao = str_replace('%Co', '%co', $discriminacao);
                $discriminacao = str_replace('%CO', '%co', $discriminacao);
                $discriminacao = str_replace('%cO', '%co', $discriminacao);
            }else if($qualidade_cobalto == 8) {
                $discriminacao = str_replace('%co', '%Co', $discriminacao);
                $discriminacao = str_replace('%CO', '%Co', $discriminacao);
                $discriminacao = str_replace('%cO', '%Co', $discriminacao);
            }else {
                $discriminacao = str_replace('%Co', '%CO', $discriminacao);
                $discriminacao = str_replace('%co', '%CO', $discriminacao);
                $discriminacao = str_replace('%cO', '%CO', $discriminacao);
            }
//Significa que tem q aparecer a qualidade do Aço Principal para o Usuário
            if($mostrar == 1) $discriminacao.= $qualidade_aco;
        }
//Significa que esse PA já foi excluído do Sistema ...
        if($ativo == 0) $discriminacao.= ' <font color="red" title="P.A. Excluído"> (EXCL) </font>';
/********************************Busca da Qualidade de Aço do P.A. Discriminação********************************/
        $sql = "SELECT qa.`nome` 
                FROM `produtos_acabados_custos` pac 
                INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = pac.`id_produto_insumo` 
                INNER JOIN `produtos_insumos_vs_acos` pia ON pia.`id_produto_insumo` = pi.`id_produto_insumo` 
                INNER JOIN `qualidades_acos` qa ON qa.`id_qualidade_aco` = pia.`id_qualidade_aco` 
                WHERE pac.`id_produto_acabado` = '$id_produto_acabado_discriminacao' 
                AND pac.`operacao_custo` = '$operacao_custo2' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) > 0) {
//Qualidade do Aço Referente ao Produto Acabado Discriminação ...
            $qualidade_aco2 = "<font color='blue'>".' ('.$campos[0]['nome'].')'.'</font>';
//Aqui eu verifico com é a qualidade do Co
            $qualidade_cobalto2 = strtok($campos[0]['nome'], '%');
            if($qualidade_cobalto2 == 5) {
                $discriminacao2 = str_replace('%Co', '%co', $discriminacao2);
                $discriminacao2 = str_replace('%CO', '%co', $discriminacao2);
                $discriminacao2 = str_replace('%cO', '%co', $discriminacao2);
            }else if($qualidade_cobalto == 8) {
                $discriminacao2 = str_replace('%co', '%Co', $discriminacao2);
                $discriminacao2 = str_replace('%CO', '%Co', $discriminacao2);
                $discriminacao2 = str_replace('%cO', '%Co', $discriminacao2);
            }else {
                $discriminacao2 = str_replace('%Co', '%CO', $discriminacao2);
                $discriminacao2 = str_replace('%co', '%CO', $discriminacao2);
                $discriminacao2 = str_replace('%cO', '%CO', $discriminacao2);
            }
//Significa que tem q aparecer a qualidade do Aço Discrminação para o Usuário
            if($mostrar == 1) $discriminacao2.= $qualidade_aco2;
        }
//Dados Referente ao Produto Acabado Principal ...
        $apresentar = $unidade.' * '.$referencia.' * ';
        if(!empty($codigo_fornecedor)) $apresentar.= $codigo_fornecedor.' * ';
        
//Dados Referente ao Produto Acabado Discriminação ...
        $apresentar2 = $unidade2.' * '.$referencia2.' * ';
        if(!empty($codigo_fornecedor2)) $apresentar2.= $codigo_fornecedor2.' * ';
/******************************************************************************/
/*************************************HTML*************************************/
/******************************************************************************/
        if($pdf == 0) {//Html
            //Dados Referente ao Produto Acabado Principal ...
            $apresentar.= $discriminacao;
            //Dados Referente ao Produto Acabado Discriminação ...
            $apresentar2.= $discriminacao2;
            //Aqui verifica se o status do custo está liberado ou bloqueado ...
            if($mostrar_status == 1) {
                //Dados Referente ao Produto Acabado Principal ...
                if($status_custo == 1) {//Está Liberado
                    $title1 = 'Custo_Liberado_em_'.$data_atualiazacao1.'_por_'.$funcionario_alterou_custo1;
                    //A cor Roxa só será utilizada quando existir Gato por Lebre ...
                    if($id_produto_acabado_discriminacao <> 0) {
                        $color1 = 'purple';
                    }else {
                        //Se a Referência do PA = 'ESP', sempre exibiremos na cor Azul ...
                        $color1 = ($referencia == 'ESP') ? '#20B2AA' : 'black';
                    }
                }else {//Está Bloqueado
                    $title1 = 'Custo_não_Liberado';
                    $color1  = 'red';
                }
                //Dados Referente ao Produto Acabado Discriminação ...
                if($status_custo2 == 1) {//Está Liberado
                    $title2 = 'Custo_Liberado_em_'.$data_atualiazacao2.'_por_'.$funcionario_alterou_custo2;
                    //A cor Roxa só será utilizada quando existir Gato por Lebre ...
                    if($id_produto_acabado_discriminacao <> 0) {
                        $color2 = 'purple';
                    }else {
                        //Se a Referência do PA = 'ESP', sempre exibiremos na cor Verde ...
                        $color2 = ($referencia2 == 'ESP') ? '#20B2AA' : 'black';
                    }
                }else {//Está Bloqueado
                    $title2 = 'Custo_não_Liberado';
                    $color2 = 'red';
                }
            }

            if($mostra_status_nao_produzir == 1) {
                //Dados Referente ao Produto Acabado Principal ...
                if($status_nao_produzir == 1) {//Significa que este PA, está sem Produzir temporariamente
                    if(!empty($title1)) $title1.= '_/_Não_Produzido_Temporariamente';
                    $apresentar.= ' / (ÑP) ';
                }
                //Dados Referente ao Produto Acabado Discriminação ...
                if($status_nao_produzir2 == 1) {//Significa que este PA, está sem Produzir temporariamente
                    if(!empty($title2)) $title2.= '_/_Não_Produzido_Temporariamente';
                    $apresentar2.= ' / (ÑP)';
                }
            }
            /**********Tratamento p/ apresentar as Discriminações**********/
            if($id_produto_acabado <> 0 && $id_produto_acabado_discriminacao == 0) {
                return '<font color="'.$color1.'" title="'.$title1.'" style="cursor:help">'.$apresentar.'</font>';
            }else if($id_produto_acabado == 0 && $id_produto_acabado_discriminacao <> 0) {
                return '<font color="'.$color1.'" title="'.$title2.'" style="cursor:help">'.$apresentar2.'</font>';
            }else if($id_produto_acabado <> 0 && $id_produto_acabado_discriminacao <> 0) {
                if(!empty($title1)) $title1.= '_/_(SB):_'.str_replace(' ', '_', $apresentar);
                return '<font color="'.$color1.'" title="'.$title1.'" style="cursor:help"><b>(SB '.$referencia.') '.$apresentar2.'</b></font>';
            }
/******************************************************************************/
/*************************************PDF**************************************/
/******************************************************************************/
        }else {//Não precisará de Tags ...
            //Dados Referente ao Produto Acabado Principal ...
            $apresentar.= $discriminacao;
            //Dados Referente ao Produto Acabado Discriminação ...
            $apresentar2.= $discriminacao2;
            
            if($id_produto_acabado <> 0 && $id_produto_acabado_discriminacao == 0) {
                return $apresentar;
            }else if($id_produto_acabado == 0 && $id_produto_acabado_discriminacao <> 0) {
                return $apresentar2;
            }else if($id_produto_acabado <> 0 && $id_produto_acabado_discriminacao <> 0) {
                return $apresentar2;
            }
        }
    }
    
    function dados_op($id_op) {
        $total_entradas_op_para_pa          = 0;
        $exibir_rotulo_pa_baixado_para_pa   = 'N';
        
        //Aqui eu busco o Total de Baixa(s) de PA realizada(s) para a OP ...
        $sql = "SELECT bop.`qtde_baixa`, bmp.`acao` 
                FROM `baixas_ops_vs_pas` bop 
                INNER JOIN `baixas_manipulacoes_pas` bmp ON bmp.`id_baixa_manipulacao_pa` = bop.`id_baixa_manipulacao_pa` 
                WHERE bop.`id_op` = '$id_op' ";
        $campos_baixa_op_para_pa = bancos::sql($sql);
        $linhas_baixa_op_para_pa = count($campos_baixa_op_para_pa);
        for($i = 0; $i < $linhas_baixa_op_para_pa; $i++) {
            if($campos_baixa_op_para_pa[$i]['acao'] == 'E') {//Ação = 'E' Entrada p/ saber o quanto que ainda resta p/ produzir ...
                $total_entradas_op_para_pa+= $campos_baixa_op_para_pa[$i]['qtde_baixa'];
            }else if($campos_baixa_op_para_pa[$i]['acao'] == 'B') {//Somente na ação = 'B' Baixa ...
                $exibir_rotulo_pa_baixado_para_pa = 'S';
            }
        }
        
        /*Aqui eu busco o Total de Baixa(s) de PI da Família "AÇO e METAIS 5" - "BLANKS 22" realizada(s) para a OP, p/ saber o quanto que 
        ainda resta p/ produzir ...*/
        $sql = "SELECT SUM(bop.`qtde_baixa`) AS total_baixa_op_para_pi 
                FROM `baixas_ops_vs_pis` bop 
                INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = bop.`id_produto_insumo` AND pi.`id_grupo` IN (5, 22) 
                WHERE bop.`id_op` = '$id_op' ";
        $campos_baixa_op_para_pi = bancos::sql($sql);
        
        //Aqui eu trago o status - "Situação" da OP ...
        $sql = "SELECT `qtde_produzir`, DATE_FORMAT(`data_emissao`, '%d/%m/%Y') AS data_emissao, 
                DATE_FORMAT(`prazo_entrega`, '%d/%m/%Y') AS prazo_entrega, `situacao`, `data_ocorrencia`, `status_finalizar` 
                FROM `ops` 
                WHERE `id_op` = '$id_op' LIMIT 1 ";
        $campos_op = bancos::sql($sql);
        if($campos_op[0]['status_finalizar'] == 1) {
            $posicao_op = '<font color="red"><b>(Finalizada)</b></font>';
        }else {
            //Verifico se a OP está importada em alguma O.S ...
            $sql = "SELECT oi.`qtde_entrada` 
                    FROM `oss` 
                    INNER JOIN `oss_itens` oi ON oi.`id_os` = oss.`id_os` 
                    WHERE oss.`ativo` = '1' 
                    AND oi.`id_op` = '$id_op' ORDER BY oi.`id_os_item` DESC LIMIT 1 ";
            $campos_os = bancos::sql($sql);
            if(count($campos_os) == 1) {//Sim, realmente a OP está importada em alguma OS ...
                //Se esse item de OS possui alguma entrada, então significa que este está fechado "Concluído" ...
                if($campos_os[0]['qtde_entrada'] > 0) {
                    $posicao_op.= '<font color="darkblue" style="cursor:help" title="Item de OS Concluído"><b> | OS</b></font>';
                }else {//Significa que este item de OSS não possui entrada ou seja está "Em Aberto" ...
                    $posicao_op.= '<font color="red" style="cursor:help" title="Item de OS em Aberto"><b> | OS</b></font>';
                }
            }
            if($exibir_rotulo_pa_baixado_para_pa == 'S')                    $posicao_op.= '<font color="red" title="PA Baixado" style="cursor:help"><b> | PA</b></font>';
            if($campos_baixa_op_para_pi[0]['total_baixa_op_para_pi'] != 0)  $posicao_op.= '<font color="red" title="PI Baixado" style="cursor:help"><b> | PI</b></font>';
        }
        $qtde_saldo = $campos_op[0]['qtde_produzir'] - $total_entradas_op_para_pa;

        if($qtde_saldo < 0) $qtde_saldo = 0;
        
        return array('qtde_produzir' => intval($campos_op[0]['qtde_produzir']), 'total_baixa_op_para_pa' => intval($total_entradas_op_para_pa), 'qtde_saldo' => $qtde_saldo, 'data_emissao' => $campos_op[0]['data_emissao'], 'prazo_entrega' => $campos_op[0]['prazo_entrega'], 'situacao' => $campos_op[0]['situacao'], 'data_ocorrencia' => $campos_op[0]['data_ocorrencia'], 'posicao_op' => $posicao_op);
    }

    /*Função que busca os Detalhes e Impostos do PA ...
    Esse 4º parâmetro $id_empresa_nf é necessário por causa das Notas Fiscais que emitimos pela K2 ...
    Esse 5º parâmetro $finalidade, se refere a uma Marcação de cadastro do Cliente, 
    "Artigo Isenção" -> SUSPENSO IPI, CONF.ART.29, PARÁGRAFO 1, ALÍNEA A E B, LEI 10637/02 e se 
    a Negociação for do Tipo INDUSTRIALIZAÇÃO, zero o IVA pois entende-se que este PA tem uma OF 
    industrializada e não Revenda ...

    Hoje esse 5º parâmetro só é utilizado pelo Incluir Itens de Orçamento e Itens Nota Fiscal ...

    Esse 6º parâmetro esta relacionado ao Tipo de Nota Fiscal = 'S' => Saída ou 'E' => Entrada ...

    Esse 7º parâmetro só é utilizado na parte de Nota Fiscal de Saída ...

    Esse 8º parâmetro só é utilizado na parte de Nota Fiscal Outra ...*/
    function dados_impostos_pa($id_produto_acabado, $id_uf = 1, $id_cliente = 0, $id_empresa_nf = 0, $finalidade = 'R', $tipo_negociacao = 'S', $id_nf = 0, $id_nf_outra = 0) {
        if(!class_exists('genericas')) require 'genericas.php';//CASO EXISTA EU DESVIO A CLASSE ...
        
        $pis                    = genericas::variavel(20);
        $cofins                 = genericas::variavel(21);
        
        //Valores Padrões ...
        $id_pais                = 31;//Representa "Brasil" ...
        $tributar_ipi_rev       = 'N';
        $pegar_iva              = 1;//A princípio a idéia é pegar o IVA ...

        //Se existir esse parâmetro $id_cliente, então eu pego alguns dados que serão de extrema importância no SQL abaixo ...
        if($id_cliente > 0) {
            $sql = "SELECT `id_pais`, `artigo_isencao`, `insc_estadual`, `trading`, `tipo_suframa`, 
                    `suframa_ativo`, `tributar_ipi_rev`, `optante_simples_nacional`, `isento_st`, `isento_st_em_pinos` 
                    FROM `clientes` 
                    WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
            $campos_cliente     = bancos::sql($sql);
            $id_pais            = $campos_cliente[0]['id_pais'];
            
            /*Se o País for Estrangeiro, não existe UF e sendo assim faço esta assumir 1, porque nossa 
            Empresa está situada no Estado de São Paulo ...*/
            if($id_pais != 31) $id_uf = 1;
            
            $artigo_isencao     = $campos_cliente[0]['artigo_isencao'];
            $insc_estadual      = $campos_cliente[0]['insc_estadual'];
            $trading            = $campos_cliente[0]['trading'];
            $tipo_suframa       = $campos_cliente[0]['tipo_suframa'];
            $suframa_ativo      = $campos_cliente[0]['suframa_ativo'];

            if($id_empresa_nf > 0) {
                /*Se a Empresa da Nota Fiscal for 'K2', então eu sempre assumo que esse campo 
                "$tributar_ipi_rev" do Cliente está marcado, p/ que as OF(s) do PA sempre saiam 
                como Industrial ...*/
                $tributar_ipi_rev       = ($id_empresa_nf == 3) ? 'S' : $campos_cliente[0]['tributar_ipi_rev'];
            }else {
                $tributar_ipi_rev       = $campos_cliente[0]['tributar_ipi_rev'];
            }

            $optante_simples_nacional   = $campos_cliente[0]['optante_simples_nacional'];
            $isento_st                  = $campos_cliente[0]['isento_st'];
            $isento_st_em_pinos         = $campos_cliente[0]['isento_st_em_pinos'];
            
/*Esse controle da Inscrição Estadual tem a ver com o § 2º do art. 155 da Constituição Federal e no art. 99 do 
Ato das Disposições Constitucionais Transitórias - ADCT da Constituição Federal, bem como nos arts. 102 e 199 
do Código Tributário Nacional (Lei nº 5.172, de 25 de outubro de 1966), resolve celebrar o seguinte ...*/
            if(intval($insc_estadual) == 0 || empty($insc_estadual) || $isento_st == 'S') {
                $pegar_iva      = 0;//Essa variável servirá de controle mais abaixo na hora de se pegar o Iva ...

                /*Se o Cliente tiver marcado no seu Cadastro "Artigo Isenção" -> SUSPENSO IPI, 
                CONF.ART.29, PARÁGRAFO 1, ALÍNEA A E B, LEI 10637/02. ou a Nota Fiscal tiver a sua Finalidade 
                como "CONSUMO" ou "INDUSTRIALIZAÇÃO", zero o IVA pois entende-se que este PA tem uma 
                OF industrializada e não Revenda ...*/
            }else if($artigo_isencao == 1 || $finalidade == 'C' || $finalidade == 'I') {
                $pegar_iva = 0;//Essa variável servirá de controle mais abaixo na hora de se pegar o Iva ...
            }
            
            if($id_pais == 31) {//Cliente do Brasil ...
                if(!empty($id_uf_original)) {//Esta variável tem prioridade sobre o $id_uf passado por parâmetro ...
                    if($id_uf_original == 1) {//Estado de São Paulo ...
                        $inicio_cfop    = ($tipo_negociacao == 'S') ? 5 : 1;
                    }else {//Fora do Estado de São Paulo ...
                        $inicio_cfop    = ($tipo_negociacao == 'S') ? 6 : 2;
                    }
                }else {
                    if($id_uf == 1) {//Estado de São Paulo ...
                        $inicio_cfop    = ($tipo_negociacao == 'S') ? 5 : 1;
                    }else {//Fora do Estado de São Paulo ...
                        $inicio_cfop    = ($tipo_negociacao == 'S') ? 6 : 2;
                    }
                }
            }else {//Cliente fora do Brasil "Internacional" ...
                $inicio_cfop    = 7;
            }
        }
        
        /*******************************************************************************************/
        /*Adaptação exclusiva somente p/ os Clientes => Bandeirantes / Lemos 115 e 
        Lemos e Gonçalves 1034 que são do mesmo dono - 04/02/2016 ...*/
        /*******************************************************************************************/
        if($id_cliente == 115 || $id_cliente == 1034) {
            //Aqui eu busco o "id_familia" desse $id_produto_acabado que foi passado por parâmetro ...
            $sql = "SELECT gpa.`id_familia` 
                    FROM `produtos_acabados` pa  
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                    WHERE pa.`id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            $campos_familia = bancos::sql($sql);
            if($campos_familia[0]['id_familia'] == 30) {//Se a Família do PA = 'Rosca Postiça' ...
                $tributar_ipi_rev = 'S';//Adaptação exclusiva só p/ esse caso, pois o cliente exige se creditar de ICMS na compra dessa linha de Produtos ...
            }
        }
        
        /*******************************************************************************************/
        /*No caminho de Exportação, sempre trataremos todos os PA(s) como se fossem Industrial "PA(s) 
        produzidos por nós mesmos" - Produção Nacional, p/ que as OF(s) do PA sempre saiam como Industrial ...*/
        /*******************************************************************************************/
        if($id_pais != 31) $tributar_ipi_rev = 'S';
        
        /*******************************************************************************************/
        /*********************Montagem de Situação Tributária de forma Dinâmica*********************/
        /*******************************************************************************************/
        //Busco os impostos do PA na Unidade Federal passada por parâmetro ...
        $sql = "SELECT cf.`id_classific_fiscal`, cf.`classific_fiscal`, cf.`ipi`, 
                ged.`margem_lucro_exp`, icms.`icms`, icms.`reducao`, icms.`icms_intraestadual`, icms.`fecp`, 
                IF('$pegar_iva' = '0', 0, icms.`iva`) AS iva, pa.`id_gpa_vs_emp_div`, 
                IF('$tributar_ipi_rev' = 'S', 0, pa.`operacao`) AS operacao, pa.`origem_mercadoria` 
                FROM `produtos_acabados` pa 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
                INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
                INNER JOIN `icms` ON icms.`id_classific_fiscal` = cf.`id_classific_fiscal` AND icms.`id_uf` = '$id_uf' 
                WHERE pa.`id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        $campos_pa_na_uf = bancos::sql($sql);
        /*************************************************************************************/
        /********************************Atribuições Iniciais*********************************/
        /*************************************************************************************/
        //A princípio essas variáveis são os valores que acabaram de serem lidos do BD ...
        $margem_lucro_exp   = $campos_pa_na_uf[0]['margem_lucro_exp'];
        $id_classific_fiscal= $campos_pa_na_uf[0]['id_classific_fiscal'];
        $classific_fiscal   = $campos_pa_na_uf[0]['classific_fiscal'];
        $ipi                = $campos_pa_na_uf[0]['ipi'];
        
        $icms               = $campos_pa_na_uf[0]['icms'].'|';
        $icms_cadastrado    = $campos_pa_na_uf[0]['icms'];//Essa variável será utilizada em poucos lugares do sistema ...
        
        $reducao            = $campos_pa_na_uf[0]['reducao'];
        $reducao_cadastrado = $campos_pa_na_uf[0]['reducao'];//Essa variável será utilizada em poucos lugares do sistema ...

        $icms_intraestadual = $campos_pa_na_uf[0]['icms_intraestadual'];
        $fecp               = $campos_pa_na_uf[0]['fecp'];
        
        //Se este PA pertencer a Classificação Fiscal de PINOS e tiver com essa marcação $isento_st_em_pinos então, eu não tributo o ST nessa linha ...
        if($id_classific_fiscal == 3 && $isento_st_em_pinos == 'S') {
            /*Estou ignorando o Valor de IVA que com certeza foi pego mais acima do SQL dependo da UF e valores que foram cadastrados, 
            acima deste SQL nesse Script eu não pegava o $id_classificao_fiscal, então a variável $pegar_iva ficou como sendo 1 e 
            consequentemente retornou sim o IVA do Banco de Dados p/ Pinos porque existe, mais o cliente não quer pagar de jeito nenhum ...*/
            $iva            = 0;
        }else {
            $iva            = $campos_pa_na_uf[0]['iva'];
        }
        
        $iva_cadastrado     = $campos_pa_na_uf[0]['iva'];//Essa variável será utilizada em poucos lugares do sistema ...

        $operacao           = $campos_pa_na_uf[0]['operacao'];
        $origem_mercadoria  = $campos_pa_na_uf[0]['origem_mercadoria'];
        
        //Grupo vs Empresa Divisão = '75' representa "Mão de Obra" ou Empresa = "Grupo" ou País diferente de Brasil "Exportação" ...
        if($campos_pa_na_uf[0]['id_gpa_vs_emp_div'] == 75 || $id_empresa_nf == 4 || $id_pais != 31) {
            $situacao_tributaria    = 41;//Não Tributada ...
            $icms                   = 0;
            $reducao                = 0;
            $icms_intraestadual     = 0;
            $fecp                   = 0;
            $iva                    = 0;
            $ipi                    = 0;
            $origem_mercadoria      = 0;//Como exportamos basicamente p/ o Mercosul obrigamos a Origem ser = 0 Nacional, p/ que os Clientes possam usufluir de todos os benefícios de Redução de Impostos em seus países ...
            /*************Preparei o Sistema p/ uma situação muito absurda*************/
            if($tipo_suframa > 0 && $suframa_ativo == 'S') {//Cliente possui Suframa e está ativo ...
                $fim_cfop   = ($tipo_negociacao == 'S') ? 109 : 203;
            }else if($trading == 1) {//Cliente possui Trading ...
                $fim_cfop   = ($tipo_negociacao == 'S') ? 501 : 503;
            }else {
                //Esse é o caminho comum ...
                if($campos_pa_na_uf[0]['operacao'] == 0) {
                    $fim_cfop = ($tipo_negociacao == 'S') ? 101 : 201;
                }else {
                    $fim_cfop = ($tipo_negociacao == 'S') ? 102 : 202;
                }
            }
        }else {
            /*Aqui estamos ignorando a lei que obriga a usar o ICMS de SP para Clientes 
            s/ Inscrição Estadual, ou seja estamos abrindo uma brecha na lei ... rsrs */
            if($id_uf > 1 || $id_uf_original > 1) {//UF ou UF original diferente do Estado de SP ...
                //Tratamento com o ICMS ...
                /********************************************************************************************/
                /*Adequação p/ Produtos Importados e UF diferente de São Paulo, que começou à vigorar em 01/01/2013*/
                /********************************************************************************************/
                /*Essa lei consiste em abaixar o valor de ICMS p/ 4% em cima dos Produtos Importados ...
                País Brasil e fora do Estado de SP, Origem = 1, 2, 3, 6, 7, 8, 
                ST = 00, 10, 20, 70, 90 ou 

                Em cima dos Produtos Importados e Índice de Importação maior do que 40% por isso (origem 5 <=40% e 6 sem similar nacional não entram) 
                e Data de Emissão >= 01/01/2013 ...*/
                if($id_pais == 31 && ($origem_mercadoria == 1 || $origem_mercadoria == 2 || $origem_mercadoria == 3 || $origem_mercadoria == 6 || $origem_mercadoria == 7 || $origem_mercadoria == 8) && date('Y-m-d') >= '2013-01-01') {
                    if($icms * (1 - $reducao / 100) > 4) {
                        $icms       = 4;
                        $reducao    = 0;
                        //Por enquanto é o único caso em que o ICMS cadastrado passa a assumir esse ICMS Instantâneo ...
                        $icms_cadastrado    = 4;
                        $reducao_cadastrado = 0;
                    }
                }
            }
            
            /*************************************************************************************/
            /*****************************Optante pelo Simples em SC******************************/
            /*************************************************************************************/
            /*Se o Cliente é Optante Simples Nacional e esta no Estado de "SC", devido ao 
            Decreto 3.467/10 § 3º de 19.08.10, existe uma redução de 70% no IVA ...*/
            if($optante_simples_nacional == 'S' && $id_uf == 7) {
                $iva*= 0.3;
                $iva = round($iva, 2);
            }
            /*************************************************************************************/
            /***************************************SUFRAMA***************************************/
            /*************************************************************************************/
            $desconto_pis_cofins_icms = 0;//Valor Inicial ...
            
            /*1) Área de Livre Comércio IPI e (ICMS de 7% somente p/ algumas cidades se o Cliente estiver com Suframa Ativo) 
            - Macapá e Santana (Amapá)
            - Bonfim e Pacaraima (Roraima)
            - Guajaramirim (Rondônia)
            - Tabatinga (Amazonas)
            - Cruzeiro do Sul, Basiléia e Epitaciolandia (Acre)
            - Boa Vista (Roraima) ...

            *2) Zona Franca de Manaus IPI, ICMS 7 % e (PIS+COFINS de 3,65% somente p/ algumas cidades) 
                - Manaus
                - Rio Preto da Eva
                - Presidente Figueiredo ...*/

            if(($tipo_suframa == 1 || $tipo_suframa == 2) && $suframa_ativo == 'S') {//Cliente possui Suframa do Tipo 1 ou 2, "Área de Livre ou Zona Franca de Manaus" e está ativo ...
                if($campos_pa_na_uf[0]['operacao'] == 0) {
                    $fim_cfop = ($tipo_negociacao == 'S') ? 109 : 203;
                }else {
                    $fim_cfop = ($tipo_negociacao == 'S') ? 110 : 204;
                }
                $desconto_pis_cofins        = ($tipo_suframa == 2) ? ($pis + $cofins) : 0;

                /*Propositalmente fiz a Conta nessa Linha p/ não dar erro concernente ao Desconto 
                pois 5 linhas mais abaixo eu Zero o ICMS e a Redução ...*/
                $desconto_pis_cofins_icms   = $desconto_pis_cofins + $icms * (1 - $reducao / 100);
                $desconto_pis_cofins_icms   = round($desconto_pis_cofins_icms, 2);
                
                $ipi                    = 0;
                $icms                   = 0;
                $reducao                = 0;
                $icms_intraestadual     = 0;
                $fecp                   = 0;
                $iva                    = 0;
                $situacao_tributaria    = 40;//Isento, porque é um benefício ...
            /*************************************************************************************/
            /***************************************TRADING***************************************/
            /*************************************************************************************/
            }else if($trading == 1) {//Cliente possui Trading ...
                $situacao_tributaria    = 41;//Porque segue a mesma idéia de Exportação ...
                if($campos_pa_na_uf[0]['operacao'] == 0) {
                    $fim_cfop = ($tipo_negociacao == 'S') ? 501 : 503;
                }else {
                    $fim_cfop = ($tipo_negociacao == 'S') ? 502 : 504;
                }
                $icms                   = 0;
                $reducao                = 0;
                $icms_intraestadual     = 0;
                $fecp                   = 0;
                $iva                    = 0;
                $ipi                    = 0;
            /*************************************************************************************/
            /*********************************PROCEDIMENTO NORMAL*********************************/
            /*************************************************************************************/
            }else {
                //Se não tem IVA ou a compra é como "INDUSTRIALIZAÇÃO" ou o Cliente possui a Credencial de Isenção de ST então ...
                if($iva == 0 || $finalidade == 'I' || $isento_st == 'S') {
                    //$iva = 0;//Independente do País, se for Estrangeiro só estou reafirmando o que já foi feito + acima ...
                    if($id_pais == 31) {//Cliente do Brasil ...
                        if($campos_pa_na_uf[0]['operacao'] == 0 || $tributar_ipi_rev == 'S') {
                            $fim_cfop = ($tipo_negociacao == 'S') ? 101 : 201;
                        }else {
                            $fim_cfop   = ($tipo_negociacao == 'S') ? 102 : 202;
                            $ipi        = 0;//Sempre em que o PA = 'Revenda', nunca existirá IPI ...
                        }
                        $situacao_tributaria    = ($campos_pa_na_uf[0]['reducao'] == 0) ? '00' : 20;
                    }else {//Cliente fora do Brasil "Internacional" ...
                        $fim_cfop = ($tipo_negociacao == 'S') ? 101 : 201;
                    }
                }else {//Iva > '0' ou finalidade = 'R' ou isento_st = 'N' ...
                    /***************Procedimento com Convênio se existir ST***************/
                    /*Verifico se no Estado do Cliente existe algum Convênio, se sim a variável 
                    "$situacao_tributaria" irá sobrepor o valor que foi atribuído anteriormente ...*/
                    $sql = "SELECT `convenio` 
                            FROM `ufs` 
                            WHERE `id_uf` = '$id_uf' LIMIT 1 ";
                    $campos_convenio = bancos::sql($sql);
                    if($campos_convenio[0]['convenio'] != '') {//Existe Convênio ...
                        //Existe convênio, então significa que o Cliente não irá pagar a GNRE se existir ST e sim nós "Empresa" ...
                        if($campos_pa_na_uf[0]['operacao'] == 0 || $tributar_ipi_rev == 'S') {
                            $fim_cfop = ($tipo_negociacao == 'S') ? 401 : 410;
                        }else {
                            $fim_cfop   = ($tipo_negociacao == 'S') ? 403 : 411;
                            $ipi        = 0;//Sempre em que o PA = 'Revenda', nunca existirá IPI ...
                        }
                        $situacao_tributaria    = ($campos_pa_na_uf[0]['reducao'] == 0) ? 10 : 70;//Somos os Substitutos Tributários ...
                    }else {//Não existe Convênio ...
                        if($id_uf == 1) {//Estado de São Paulo ...
                            if($campos_pa_na_uf[0]['operacao'] == 0 || $tributar_ipi_rev == 'S') {
                                $situacao_tributaria    = 10;//Somos os Substitutos Tributários ...
                                $fim_cfop               = ($tipo_negociacao == 'S') ? 401 : 410;
                            }else {
                                $icms                   = 0;
                                $reducao                = 0;
                                $icms_intraestadual     = 0;
                                $fecp                   = 0;
                                $iva                    = 0;
                                $ipi                    = 0;
                                $situacao_tributaria    = 60;//Somos os Substituídos Tributários ...
                                $fim_cfop               = ($tipo_negociacao == 'S') ? 405 : 411;
                            }
                        }
                    }
                }
            }
            
            //Quando o Cliente não tiver Inscrição Estadual e a sua UF for fora do Estado de SP ...
            if(empty($insc_estadual) && $id_uf > 1) {
                if($id_nf > 0) {//Se existir NF ...
                    $sql = "SELECT `status` 
                            FROM `nfs` 
                            WHERE `id_nf` = '$id_nf' LIMIT 1 ";
                    $campos_nfs         = bancos::sql($sql);
                    $fim_cfop           = ($campos_nfs[0]['status'] == 6) ? 202 : 108;//NF de Devolução 102, NF de Saída 108 ...
                }else {
                    $fim_cfop = 108;
                }
            }
/*Se estiver marcado no Cadastro do Cliente a Opção de SUSPENSO IPI, CONF.ART.29, PARÁGRAFO 1, 
ALÍNEA A E B, LEI 10637/02 ou Cliente possui Suframa do Tipo 3 "Amazônia Ocidental" e está ativo, 
então NÃO EXISTE IPI ...*/
            if($artigo_isencao == 1 || ($tipo_suframa == 3 && $suframa_ativo == 'S')) $ipi = 0;
        }
        /**********************************************************************/
        /*****************************NF de Saída******************************/
        /**********************************************************************/
        if($id_nf > 0) {
            /**********************************************************************/
            /**********************Nota Fiscal de Bonificação**********************/
            /**********************************************************************/
            /*De um modo paleativo para liberar uma Nota Fiscal de Bonificação, fiz essa adaptação 
            aqui no fim dessa função - 07/12/2015 ...*/
            $sql = "SELECT `natureza_operacao` 
                    FROM `nfs` 
                    WHERE `id_nf` = '$id_nf' LIMIT 1 ";
            $campos_nfs = bancos::sql($sql);
            if($campos_nfs[0]['natureza_operacao'] == 'BON') {
                //Dentro do país coloco 910, fora do país não existe esse código então coloco 949 ...
                $fim_cfop = ($id_pais == 31) ? 910 : 949;
            /**********************************************************************/
            /***Nota Fiscal de Venda Originada de Encomenda para Entrega Futura****/
            /**********************************************************************/
            }else if($campos_nfs[0]['natureza_operacao'] == 'VOF') {
                if($campos_pa_na_uf[0]['operacao'] == 0) {
                    $fim_cfop = 116;
                }else {
                    $fim_cfop = 117;
                }
            /**********************************************************************/
            /*****************Nota Fiscal Remessa de Amostra Grátis****************/
            /**********************************************************************/
            }else if($campos_nfs[0]['natureza_operacao'] == 'RAG') {
                $fim_cfop = 911;
            }
        }
        /**********************************************************************/
        /******************************NF Outras*******************************/
        /**********************************************************************/
        /*"Notas Fiscais Outras" é a única Situação da qual as Regras p/ CFOP são totalmente diferentes do procedimento normal, mas 
        costumam ser as próprias CFOP(s) selecionadas pelo usuário no Cabeçalho ...*/
        if($id_nf_outra > 0) {
            //Busca a CFOP da NF Outra e verifico se existe NF Complementar que irá influenciar nesta parte de CFOP(s) ...
            $sql = "SELECT `id_cfop`, `id_nf_comp`, `id_nf_outra_comp` 
                    FROM `nfs_outras` 
                    WHERE `id_nf_outra` = '$id_nf_outra' LIMIT 1 ";
            $campos             = bancos::sql($sql);
            $id_cfop            = $campos[0]['id_cfop'];
            $id_nf_comp         = $campos[0]['id_nf_comp'];
            $id_nf_outra_comp 	= $campos[0]['id_nf_outra_comp'];
            
            if($id_nf_comp > 0) {
                /*Busco alguns dados da NF de Saída que serão passados por parâmetro na função "dados_impostos_pa" 
                abaixo, 1 só item de NF já me satisfaz, porque hoje em dia a CFOP é por item de Nota Fiscal ...*/
                $sql = "SELECT c.`id_cliente`, c.`id_uf`, nfs.`id_empresa`, nfs.`status`, nfsi.`id_produto_acabado` 
                        FROM `nfs_itens` nfsi 
                        INNER JOIN `nfs` ON nfs.`id_nf` = nfsi.`id_nf` 
                        INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
                        WHERE nfs.`id_nf` = '$id_nf_comp' LIMIT 1 ";
                $campos_nfs_item    = bancos::sql($sql);

                /*Se a Nota Fiscal for uma Devolução coloco essa Letra E que equivale a Entrada, senão 
                S que equivale a Saída ...*/
                $tipo_negociacao    = ($campos_nfs_item[0]['status'] == 6) ? 'E' : 'S';
                $dados_produto      = self::dados_impostos_pa($campos_nfs_item[0]['id_produto_acabado'], $campos_nfs_item[0]['id_uf'], $campos_nfs_item[0]['id_cliente'], $campos_nfs_item[0]['id_empresa'], $campos_nfs_item[0]['finalidade'], $tipo_negociacao, $id_nf_comp);

                //Busco o id_cfop através do N.º de CFOP que foi encontrado acima ...
                $sql = "SELECT `id_cfop` 
                        FROM `cfops` 
                        WHERE `cfop` = '".substr($dados_produto['cfop'], 0, 1)."' 
                        AND `num_cfop` = '".substr($dados_produto['cfop'], 2, 3)."' 
                        AND `ativo` = '1' LIMIT 1 ";
                $campos_cfop    = bancos::sql($sql);
                $id_cfop        = $campos_cfop[0]['id_cfop'];
            }else if($id_nf_outra_comp > 0) {
                $sql = "SELECT `id_cfop` 
                        FROM `nfs_outras` 
                        WHERE `id_nf_outra` = '$id_nf_outra_comp' LIMIT 1 ";
                $campos     = bancos::sql($sql);
                $id_cfop    = $campos[0]['id_cfop'];
            }
            /*Busco a CFOP equivalente ao id_cfop que foi selecionado no Cabeçalho da Nota Fiscal ou do que foi encontrado encontrado aí 
            pelo caminho desse Script se esta CFOP for pertinente a uma Nota Fiscal Complementar ...*/
            $sql = "SELECT `cfop`, `num_cfop` 
                    FROM `cfops` 
                    WHERE `id_cfop` = '$id_cfop' 
                    AND `ativo` = '1' LIMIT 1 ";
            $campos_cfop    = bancos::sql($sql);
            $inicio_cfop    = $campos_cfop[0]['cfop'];
            $fim_cfop       = $campos_cfop[0]['num_cfop'];
            /*Se a CFOP que estiver no Cabeçalho dessa NF for 5.116 "Venda originada de encomenda p/ entrega futura", então nesse caso em 
            específico eu preciso separar os itens que são Industrial dos itens que são Revenda com outra CFOP = '117' ...*/
            if($inicio_cfop == 5 && $fim_cfop == 116 && $campos_pa_na_uf[0]['operacao'] == 1) $fim_cfop = 117;
        }
        /**********************************************************************/
        $cfop   = $inicio_cfop.'.'.$fim_cfop;
        $cst    = $origem_mercadoria.$situacao_tributaria;
        /*******************************************************************************************/
        return array('margem_lucro_exp' => $margem_lucro_exp, 'id_classific_fiscal' => $id_classific_fiscal, 'classific_fiscal' => $classific_fiscal, 'ipi' => $ipi, 'icms' => $icms, 'icms_cadastrado' => $icms_cadastrado, 'reducao' => $reducao, 'reducao_cadastrado' => $reducao_cadastrado, 'icms_intraestadual' => $icms_intraestadual, 'fecp' => $fecp, 'iva' => $iva, 'iva_cadastrado' => $iva_cadastrado, 'operacao' => $operacao, 'situacao_tributaria' => $situacao_tributaria, 'cfop' => $cfop, 'cst' => $cst, 'desconto_pis_cofins_icms' => $desconto_pis_cofins_icms, 'pis' => $pis, 'cofins' => $cofins);
    }
    
    /*Essa Margem de Lucro Estimada é utilizada em Vendas no Orçamento que tem como objetivo auxilío no cálculo 
    da Comissão do Vendedor ...*/
    function gravar_campos_para_calcular_margem_lucro_estimada($id_produto_insumo) {
        if(!class_exists('custos')) require 'custos.php';//CASO EXISTA EU DESVIO A CLASSE ...
        /***************************************************************************/
        /**********************************Compras**********************************/
        /***************************************************************************/
        //Aqui eu busco o PA do PI que é "Matéria Prima" - PIPA ...
        $sql = "SELECT pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, u.`sigla` 
                FROM `produtos_acabados` pa 
                INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
                WHERE pa.`id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
        $campos_pa  = bancos::sql($sql);
        if(count($campos_pa) == 1) {//Se esse PI for realmente um PA então ...
            $id_fornecedor_default  = custos::procurar_fornecedor_default_revenda($campos_pa[0]['id_produto_acabado'], '',  1);
            $qtde_estoque           = estoque_acabado::qtde_estoque($campos_pa[0]['id_produto_acabado']);
            $ec_pa                  = $qtde_estoque[8];
            $total_qtde_entregue    = 0;

            /*Aqui eu busco todas as NF´s de Entrada desse PI que esteja liberado em Estoque até que a Qtde Recebida 
            seja < que o EC do PA ...*/
            $sql = "SELECT nfe.`id_nfe`, nfeh.`qtde_entregue` 
                    FROM `nfe_historicos` nfeh 
                    INNER JOIN `nfe` ON nfe.`id_nfe` = nfeh.`id_nfe` 
                    WHERE nfeh.`id_produto_insumo` = '$id_produto_insumo' 
                    AND nfeh.`status` = '1' 
                    ORDER BY nfe.`data_entrega` DESC ";
            $campos_nfe = bancos::sql($sql);
            $linhas_nfe = count($campos_nfe);
            for($i = 0; $i < $linhas_nfe; $i++) {
                //Enquanto o Somatório Total da Qtde Entregue for menor que o EC do PA, vou acumulando nessa variável $total_qtde_entregue ...
                if($total_qtde_entregue < $ec_pa) {
                    $total_qtde_entregue+= $campos_nfe[$i]['qtde_entregue'];
                    $vetor_nfe[]    = $campos_nfe[$i]['id_nfe'];
                }
            }
        }
        if(!isset($vetor_nfe)) $vetor_nfe[] = 0;//Trato essa variável p/ não dar erro na query mais abaixo ...
        $condicao_nfes = " AND nfe.`id_nfe` IN (".implode(',', $vetor_nfe).") ";
        
        $sql = "SELECT `qtde_total_compras_ml_est`, `preco_compra_medio_corr_ml_est`, `qtde_total_pendencias_ml_est`, 
                `preco_pendencias_medio_corr_ml_est`, `data_ultima_atualizacao_ml_est` 
                FROM `produtos_insumos` 
                WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
        $campos_pi = bancos::sql($sql);

        //Trago somente itens que estão na Nota Fiscal de Entrada e que estejam liberados em Estoque ...
        $sql = "SELECT nfe.`data_emissao`, nfeh.`qtde_entregue`, nfeh.`valor_entregue` 
                FROM `nfe` 
                INNER JOIN `nfe_historicos` nfeh ON nfeh.`id_nfe` = nfe.`id_nfe` AND nfeh.`status` = '1' AND nfeh.`id_produto_insumo` = '$id_produto_insumo' 
                WHERE 1 
                $condicao_nfes 
                ORDER BY nfe.data_entrega DESC ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas == 0) {//Se não encontrou nenhuma Compra ...
            //Guardando campos p/ auxiliar a ML Estimada que é utilizada em Vendas ...
            $sql = "UPDATE `produtos_insumos` SET `qtde_total_compras_ml_est` = '0', `preco_compra_medio_corr_ml_est` = '0' WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
            bancos::sql($sql);
        }else {//Existe pela menos 1 Compra ...
            $qtde_total = 0;
            for($i = 0; $i < $linhas; $i++) {
                //Só irá contabilizar a Quantidade quando existir Preço p/ o Item de NF de Entrada ...
                if($campos[$i]['valor_entregue'] != '0.00') $qtde_total+= $campos[$i]['qtde_entregue'];
                
                /*Verifico se existem Compras acima desse período capitalizaremos uma Taxa de 0,5% 
                porque a empresa nessa época não capitava dinheiro nos Bancos ...*/
                if($campos[$i]['data_emissao'] < '2009-01-01') {
                    //Aqui é anterior a 2009, com meio % apenas ao mês de Taxas ...
                    $taxa_financeira_compras    = 0.5;
                    $fator_taxa_financeira      = pow(($taxa_financeira_compras / 100 + 1), (1 / 30));

                    $retorno_data               = data::diferenca_data($campos[$i]['data_emissao'], '2008-12-31');
                    $dias                       = $retorno_data[0];
                    $fator_taxa_final_periodo   = pow($fator_taxa_financeira, $dias);
                    $preco_corrigido_atual      = $campos[$i]['valor_entregue'] * $fator_taxa_final_periodo;

                    /*Aqui já é a partir de 01 de janeiro de 2009 com Taxas a partir de 2% ...
                    para esse caso será cobrado taxa em cima de taxa ...*/
                    $taxa_financeira_compras    = genericas::variavel(4) - 0.5;
                    $fator_taxa_financeira      = pow(($taxa_financeira_compras / 100 + 1), (1 / 30));

                    $retorno_data               = data::diferenca_data('2009-01-01', date('Y-m-d'));
                    $dias                       = $retorno_data[0];
                    $fator_taxa_final_periodo   = pow($fator_taxa_financeira, $dias);

                    $preco_corrigido_atual      = $preco_corrigido_atual * $fator_taxa_final_periodo;
                }else {//Sempre a partir de 1 de Janeiro de 2009 ...
                    /*Até o dia 23/07/2013 às 16:38 era desse modo => "genericas::variavel(4) - 0.5" ..., 
                    a partir daí fixamos 2% porque o Roberto acha que esse é o Valor Máximo p/ essa Taxa de 
                    Estocagem, como os Juros subiram teríamos que fazer uma interpolação o que seria complicado 
                    e fizemos isso p/ simplicarmos os cálculos e ganharmos tempo ...*/
                    $taxa_financeira_compras    = 2;
                    $fator_taxa_financeira      = pow(($taxa_financeira_compras / 100 + 1), (1 / 30));

                    $retorno_data               = data::diferenca_data($campos[$i]['data_emissao'], date('Y-m-d'));
                    $dias                       = $retorno_data[0];
                    $fator_taxa_final_periodo   = pow($fator_taxa_financeira, $dias);

                    $preco_corrigido_atual      = $campos[$i]['valor_entregue'] * $fator_taxa_final_periodo;
                }
                $valor_total_corrigido          = round($preco_corrigido_atual * $campos[$i]['qtde_entregue'], 2);
                $valor_total_corrigido_geral+=  $valor_total_corrigido;
            }
            $preco_medio_corr_atual = ($valor_total_corrigido_geral / $qtde_total);
            //Guardando campos p/ auxiliar a ML Estimada que é utilizada em Vendas ...
            $sql = "UPDATE `produtos_insumos` SET `qtde_total_compras_ml_est` = '$qtde_total', `preco_compra_medio_corr_ml_est` = '$preco_medio_corr_atual' WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
            bancos::sql($sql);
        }
        /***************************************************************************/
        /*********************************Pendências********************************/
        /***************************************************************************/
        //Zero essas variáveis abaixo p/ não herdarmos os valores que foram calculadas acima na parte de Compras ...
        $qtde_total                     = 0;
        $valor_total_corrigido_geral    = 0;
        $preco_medio_corr_atual         = 0;
        
        /*Explicação das duas querys abaixo:

1) Aqui eu busco todos os Itens de Pedido que estejam Totalmente em Aberto ou importados Parcialmente em Nota Fiscal 
e não liberados em Estoque. Pedidos não Contabilizados aparecem nesse Relatório com a Marcação ÑC ...

2) Aqui eu busco todos os Itens de Pedido que estejam Totalmente importados em Nota Fiscal e ñ liberados em Estoque ...*/
        $sql = "SELECT ip.id_item_pedido 
                FROM `itens_pedidos` ip 
                INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` AND p.`status` = '1' AND ((p.`programado_descontabilizado` = 'S' AND p.`ativo` = '0') OR (p.`programado_descontabilizado` = 'N' AND p.`ativo` = '1')) 
                WHERE ip.`id_produto_insumo` = '$id_produto_insumo' 
                AND ip.`status` < '2' 
                UNION 
                SELECT ip.id_item_pedido 
                FROM `itens_pedidos` ip 
                INNER JOIN `nfe_historicos` nfeh ON nfeh.`id_item_pedido` = ip.`id_item_pedido` AND nfeh.`status` = '0' 
                WHERE ip.`id_produto_insumo` = '$id_produto_insumo' 
                AND ip.`status` = '2' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas == 0) {//Não existe nenhum item nas situações cima ...
            $id_itens_pedidos = 0;//Controle p/ não furar o SQL abaixo ...
        }else {//Existe pelo menos um item na situação cima ...
            for($i = 0; $i < $linhas; $i++) $vetor_item_pedido[] = $campos[$i]['id_item_pedido'];
            $id_itens_pedidos = implode(',', $vetor_item_pedido);
        }
        $sql = "SELECT `id_item_pedido`, `preco_unitario`, `qtde` 
                FROM `itens_pedidos` 
                WHERE `id_item_pedido` IN ($id_itens_pedidos) ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas == 0) {//Se não encontrou nenhuma Pendência ...
            //Guardando campos p/ auxiliar a ML Estimada que é utilizada em Vendas ...
            $sql = "UPDATE `produtos_insumos` SET `qtde_total_pendencias_ml_est` = '0', `preco_pendencias_medio_corr_ml_est` = '0', `data_ultima_atualizacao_ml_est` = '".date('Y-m-d')."' WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
            bancos::sql($sql);
        }else {//Existe pela menos 1 Pendência ...
            for($i = 0; $i < $linhas; $i++) {
                //Busca o Total entregue do Item do Pedido em diversas NF(s) ...
		$sql = "SELECT SUM(`qtde_entregue`) AS total_entregue 
                        FROM `nfe_historicos` 
                        WHERE `id_item_pedido` = '".$campos[$i]['id_item_pedido']."' ";
		$campos_entregue    = bancos::sql($sql);
		$total_entregue     = $campos_entregue[0]['total_entregue'];

                //Busca o Total entregue do Item do Pedido em diversas NF(s) que já não foi liberado ...
		$sql = "SELECT SUM(`qtde_entregue`) AS total_entregue 
                        FROM `nfe_historicos` 
                        WHERE `id_item_pedido` = '".$campos[$i]['id_item_pedido']."' 
                        AND `status` = '0' ";
		$campos_entregue                = bancos::sql($sql);
		$total_entregue_nao_liberado    = $campos_entregue[0]['total_entregue'];
                $total_restante                 = $campos[$i]['qtde'] - $total_entregue + $total_entregue_nao_liberado;
                
                //Só irá contabilizar a Quantidade Restante quando existir Preço p/ o Item de Pedido ...
                if($campos[$i]['preco_unitario'] != '0.00') $qtde_total+= $total_restante;//Nesse caso a Qtde Total sempre será em cima do Restante ...
                $preco_total+= $total_restante * $campos[$i]['preco_unitario'];
                
                $compra_producao_total+= $total_restante;
            }
            //Nesse caso o Valor Corrigido já é o Próprio Preço Total ...
            $valor_total_corrigido_geral    = $preco_total;
            $preco_medio_corr_atual         = ($valor_total_corrigido_geral / $qtde_total);
            //Guardando campos p/ auxiliar a ML Estimada que é utilizada em Vendas ...
            $sql = "UPDATE `produtos_insumos` SET `qtde_total_pendencias_ml_est` = '$compra_producao_total', `preco_pendencias_medio_corr_ml_est` = '$preco_medio_corr_atual', `data_ultima_atualizacao_ml_est` = '".date('Y-m-d')."' WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
	
    /*Essa função traz o somatório de MMV do PA passado por parâmetro e de todos os PA´s em que ele 
    atrelados à 7ª Etapa ou que esses PA´s estão atrelados a 7ª Etapa dele ...
     
    Esse parâmetro $id_unidade restrigirá dados, trazendo somente os pas_atrelados desse PA que entrou 
    no escopo dessa função, agora da mesma Unidade deste ...*/
    function calculo_producao_mmv_estoque_pas_atrelados($id_produto_acabado, $id_unidade) {
        if(!class_exists('custos'))             require 'custos.php';//CASO EXISTA EU DESVIO A CLASSE ...
        if(!class_exists('estoque_acabado'))    require 'estoque_acabado.php';//CASO EXISTA EU DESVIO A CLASSE ...
        if(!class_exists('genericas'))          require 'genericas.php';//CASO EXISTA EU DESVIO A CLASSE ...

        /*Sempre deleto essa variável pq se essa função for chamada p/ ser rodada, por ser uma variável
        global acaba acumulando id de outros PAs dos Loops anteriores ...*/
        if(isset($id_pa_atrelados))     unset($id_pa_atrelados);

        //Aqui eu busco a Unidade do PA principal que foi passado por parâmetro ...
        $sql = "SELECT gpa.`id_grupo_pa`, gpa.`id_familia`, pa.`id_unidade`, pa.`referencia` 
                FROM `produtos_acabados` pa 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                WHERE pa.`id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        $campos_unidade_principal   = bancos::sql($sql);
        $id_grupo_pa_principal      = $campos_unidade_principal[0]['id_grupo_pa'];
        $id_familia_principal       = $campos_unidade_principal[0]['id_familia'];
        $id_unidade_principal       = $campos_unidade_principal[0]['id_unidade'];
        $referencia_principal       = $campos_unidade_principal[0]['referencia'];

        /*Essa variavel esta como global por que tenho que pegar o id PA principal depois vejo os atrelados assim ficará ordenado ...
        Infelizmente tive que manter essa estrutura do Luis que encontrei no arquivo de Visualizar Estoque, pq senão dá erro no Custo*/
        global $id_pa_atrelados;
        $id_pa_atrelados[] = $id_produto_acabado;
        
        /*Aqui eu verifico se o PA que foi passado por parâmetro tem a marcação de visualização
        ou seja se ele for componente de um outro, esse não pode ser exibido ...*/
        $sql = "SELECT explodir_view_estoque 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        $campos_view_explodir = bancos::sql($sql);
        if($campos_view_explodir[0]['explodir_view_estoque'] == 'S') {//Esse PA tem ramificação ...
            $vetor_pas_atrelados = custos::pas_atrelados($id_produto_acabado, $id_unidade);//Aqui eu também retorno o próprio PA que foi passado por parâmetro ...
        }else {//Esse PA não tem ramificação então eu retorno ele próprio apenas ...
            $vetor_pas_atrelados[] = $id_produto_acabado;
        }

        for($i = 0; $i < count($vetor_pas_atrelados); $i++) {
            //Aqui eu busco a Unidade e o mmv de cada PA ...
            $sql = "SELECT `id_unidade`, `referencia`, `pecas_por_jogo`, `mmv` 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '$vetor_pas_atrelados[$i]' 
                    AND `ativo` = '1' LIMIT 1 ";
            $campos_pa                  = bancos::sql($sql);
            $retorno                    = estoque_acabado::qtde_estoque($vetor_pas_atrelados[$i]);
            $estoque_comprometido       = $retorno[8];
            $compra                     = estoque_acabado::compra_producao($vetor_pas_atrelados[$i]);
            $producao                   = $retorno[2];
            
            /*Suponho que todos os PA(s) do Loop também sejam Machos, senão não teria lógica por isso só analiso 
            a Família do PA Principal ...*/
            if($id_familia_principal == 9) {//Nesse caso específico, o procedimento será um pouquinho diferenciado ...
                $total_mmv_pas_atrelados+= ($campos_pa[0]['pecas_por_jogo'] * $campos_pa[0]['mmv']);
                $total_compra_producao_pas_atrelados+= ($campos_pa[0]['pecas_por_jogo'] * ($producao + $compra));
                //Aqui eu também já calculo o Estoque de Queima de todos os PAs ...
                $total_ec_pas_atrelados+= ($campos_pa[0]['pecas_por_jogo'] * $estoque_comprometido);
            }else {//Outras Famílias ...
                //Se a UN Principal do PA for = a UN do PA que está em evidência do Looping, acumulo o MMV ...
                if($id_unidade_principal == $campos_pa[0]['id_unidade']) {
                    $total_mmv_pas_atrelados+= $campos_pa[0]['mmv'];
                    $total_compra_producao_pas_atrelados+= $producao + $compra;
                    //Aqui eu também já calculo o Estoque de Queima de todos os PAs ...
                    $total_ec_pas_atrelados+= $estoque_comprometido;
                }
            }
            /*Nunca podemos somar o Estoque Disponível de PA(s) que sejam Sub-Produtos de um Produto Principal que é o que acontece na regra 
            do IF abaixo:
                
            Exemplo: MR-053 - É o PA Principal "Kit com 3 PA(s) que são o U, D, T" ...
                MR-053T - É o Terceiro Macho do PA Principal ...
                MR-053D - É o Segundo Macho do PA Principal ...
                MR-053U - É o Primeiro Macho do PA Principal ...
                MR-053A - É um Jogo que contém o Primeiro Macho e Terceiro Macho
            */
            if(($campos_pa[0]['referencia'] != $referencia_principal.'U') && 
                ($campos_pa[0]['referencia'] != $referencia_principal.'D') && 
                ($campos_pa[0]['referencia'] != $referencia_principal.'T') && 
                ($campos_pa[0]['referencia'] != $referencia_principal.'A')) {
                    $total_ed_pas_atrelados+= $retorno[3];//Total dos Estoques Disponíveis atrelados ...
            }
            $total_er_pas_atrelados+= $retorno[0];//Total dos Estoques Reais atrelados ...
        }
        /************************************************************************************/
        /******************************Controle de Grupos PA(s)******************************/
        /************************************************************************************/
        //Lima Agulha WS - pode ser vendida avulsa, mas normalmente é utilizada p/ montar jogos ...
        //Lima Agulha Diamantada - pode ser vendida avulsa, mas normalmente é utilizada p/ montar jogos ...
        //Cabo de Lima, não calculo a Queima a função é muy pesada ...
        //Referências começadas por Si-4 ñ podem pq são Bits Sinterizados q temos produzidos bem acima da média p/ forçar venda ...
        if($id_grupo_pa_principal == 11 || $id_grupo_pa_principal == 78 || $id_grupo_pa_principal == 81 || strpos($referencia_principal, 'SI-4') !== false) {
            $retorno                = estoque_acabado::qtde_estoque($id_produto_acabado);//Pego o Estoque Comprometido do PA principal ...
            $total_ec_pas_atrelados = $retorno[8];
        }
        /************************************************************************************/
        if($total_mmv_pas_atrelados == 0) $total_mmv_pas_atrelados = 0.01;//Para não dar erro de Divisão por Zero ...
        
        //Sendo assim eu faço um arredondamento dessa Qtde de Excesso p/ Baixo ...
        $estoque_para_x_meses_pas_atrelados = round($total_ec_pas_atrelados / $total_mmv_pas_atrelados, 1);
        
        return array('total_mmv_pas_atrelados' => $total_mmv_pas_atrelados, 'total_compra_producao_pas_atrelados' => $total_compra_producao_pas_atrelados, 'total_er_pas_atrelados' => $total_er_pas_atrelados, 'total_ed_pas_atrelados' => $total_ed_pas_atrelados, 'total_ec_pas_atrelados' => $total_ec_pas_atrelados, 'estoque_para_x_meses_pas_atrelados' => $estoque_para_x_meses_pas_atrelados);
    }
    
    /*Essa função traz o somatório de Queima "$total_eq_pas_atrelados" do PA passado por parâmetro e de todos 
    os PA´s em que ele atrelados à 7ª Etapa ou que esses PA´s estão atrelados a 7ª Etapa dele ...*/
    function calculo_estoque_queima_pas_atrelados($id_produto_acabado) {
        if(!class_exists('custos'))             require 'custos.php';//CASO EXISTA EU DESVIO A CLASSE ...
        if(!class_exists('estoque_acabado'))    require 'estoque_acabado.php';//CASO EXISTA EU DESVIO A CLASSE ...
        if(!class_exists('genericas'))          require 'genericas.php';//CASO EXISTA EU DESVIO A CLASSE ...
        
        $dias_validade          = (int)genericas::variavel(48);//Essa variável será utilizada no SQL + abaixo nos itens de Queima ...
        $qtde_meses             = (int)genericas::variavel(73);
        
        $total_eq_pas_atrelados = 0;
        
        /*Sempre deleto essa variável pq se essa função for chamada p/ ser rodada, por ser uma variável
        global acaba acumulando id de outros PAs dos Loops anteriores ...*/
        if(isset($id_pa_atrelados)) unset($id_pa_atrelados);
        
        //Aqui eu busco a "Unidade do PA principal" e alguns atributos deste que foi passado por parâmetro ...
        $sql = "SELECT ged.`id_gpa_vs_emp_div`, gpa.`id_familia`, pa.`id_unidade` 
                FROM `produtos_acabados` pa 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                WHERE pa.`id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        $campos_unidade_principal       = bancos::sql($sql);
        $id_gpa_vs_emp_div_principal    = $campos_unidade_principal[0]['id_gpa_vs_emp_div'];
        $id_familia_principal           = $campos_unidade_principal[0]['id_familia'];
        $id_unidade_principal           = $campos_unidade_principal[0]['id_unidade'];
        
        /*À partir do dia "24/06/2016" a queima só esta sendo feita em cima dos respectivos 
        "Grupos vs Empresas Divisões" -> 22 Machos Manuais WS Jogos, 43 Machos Máquina, 
        83 Machos Manuais HSS Jogos - Por conta de uma Promoção de Machos Warrior WARRIOR ...*/
        $vetor_produtos_em_promocao     = array(22, 43, 83);
        
        if(in_array($id_gpa_vs_emp_div_principal, $vetor_produtos_em_promocao)) {
            /*Acumulo nessa variável "$retorno_pas_atrelados" valores dos PA(s) atrelados a este PA principal 
            desde que sejam da mesma UN ...*/
            $retorno_pas_atrelados  = intermodular::calculo_producao_mmv_estoque_pas_atrelados($id_produto_acabado, $id_unidade_principal);
            $total_eq_pas_atrelados = $retorno_pas_atrelados['total_ec_pas_atrelados'] + $retorno_pas_atrelados['total_compra_producao_pas_atrelados'] - $qtde_meses * $retorno_pas_atrelados['total_mmv_pas_atrelados'];
        }
        
        /*Verifico tudo o que tenho atrelado desse PA passado por parâmetro, mas desde que seja da 
        mesma Unidade deste ...*/
        $vetor_pas_atrelados = custos::pas_atrelados($id_produto_acabado, $id_unidade_principal);//Aqui eu também retorno o próprio PA que foi passado por parâmetro ...
        
        /*Se a variável "$vetor_pas_atrelados" retornar vazia, então faço esse controle para não 
        dar erro mais abaixo para esse array ...*/
        if(empty($vetor_pas_atrelados)) $vetor_pas_atrelados[] = $id_produto_acabado;
        
        /*Embora a função de Custo "pas_atrelados" tenha trago também os PA´s que são do Tipo ESP, 
        nesse caso ignoro os mesmos porque não existe queima para este Tipo de Produto ...*/
        $sql = "SELECT `id_produto_acabado`, `referencia` 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` IN (".implode(',', $vetor_pas_atrelados).") ";
        $campos_produto_acabado = bancos::sql($sql);
        $linhas_produto_acabado = count($campos_produto_acabado);
        for($i = 0; $i < $linhas_produto_acabado; $i++) {
            if($campos_produto_acabado[$i]['referencia'] == 'ESP') unset($vetor_pas_atrelados[$i]);//Removo o índice de array que é do Tipo ESP ...
        }
        $vetor_pas_atrelados = array_values($vetor_pas_atrelados);//Reindexa os índices do Array ...
        
        /*Se não encontrou nenhum PA, ou até tinha encontrado como por exemplo um "ESP", mas esse foi removido 
        pelo trecho de código acima, então faço esse macete p/ não furar o SQL mais abaixo ...*/
        if(count($vetor_pas_atrelados) == 0) $vetor_pas_atrelados[] = 0;
        
        /*****************************************************************************************************************************/
        /******* Observação: Eu não fiz essa Query com SUM porque tinha horas que não retorna registro porque o resultado não era 
         positivo e devido esse ocorrido retornava NULL em alguns casos o q furava nos cálculos, preferi uma estrutura + manual ******/
        /*****************************************************************************************************************************/
        /*Aqui eu verifico todos os itens de Orcs que possuem esse PA do Loop marcados 
        como Queima de Estoque que estejam em Aberto ou Parcial ...*/
        $sql = "SELECT ovi.`id_orcamento_venda_item`, ovi.`qtde` 
                FROM `orcamentos_vendas_itens` ovi 
                INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` AND ov.`data_emissao` >= DATE_ADD('".date('Y-m-d')."', INTERVAL -$dias_validade DAY) 
                WHERE ovi.`id_produto_acabado` IN (".implode(',', $vetor_pas_atrelados).") 
                AND ovi.`queima_estoque` = 'S' 
                AND ovi.`status` <= '1' ";
        $campos_orcamentos = bancos::sql($sql);
        $linhas_orcamentos = count($campos_orcamentos);
        for($j = 0; $j < $linhas_orcamentos; $j++) {
            $total_queima_orcado+= $campos_orcamentos[$j]['qtde'];
            //Aqui eu verifico todos os Pedidos que foram gerados através desse Item de Orçamento ...
            $sql = "SELECT `qtde` 
                    FROM `pedidos_vendas_itens` 
                    WHERE `id_orcamento_venda_item` = '".$campos_orcamentos[$j]['id_orcamento_venda_item']."' ";
            $campos_pedidos = bancos::sql($sql);
            $linhas_pedidos = count($campos_pedidos);
            for($k = 0; $k < $linhas_pedidos; $k++) $total_queima_pedido+= $campos_pedidos[$k]['qtde'];
        }
        /*****************************************************************************************************************************/  
        /*Do total de Queima encontrado pela fórmula acima, eu desconto o Total de Queima 
        encontrado nos ORCs do Produto Acabado ...*/
        $total_eq_pas_atrelados-= ($total_queima_orcado - $total_queima_pedido);
        /************************************************************************************/
        /******************************Controle de Grupos PA(s)******************************/
        /************************************************************************************/
        //Se for componente, não existe queima ...
        if($id_familia_principal == 23 || $id_familia_principal == 24)  $total_eq_pas_atrelados = 0;
        /************************************************************************************/
        
        //Aqui eu verifico se existe Qtde de Peças por Embalagem do PA ...
        $sql = "SELECT `pecas_por_emb` 
                FROM `pas_vs_pis_embs` 
                WHERE `id_produto_acabado` = '$id_produto_acabado' ";
        $campos_pecas_por_emb   = bancos::sql($sql);
        //Não encontrou registro algum ou até tem registro mas está com valor Zero = 1 ...
        $pecas_por_emb          = (count($campos_pecas_por_emb) == 0 || $campos_pecas_por_emb[0]['pecas_por_emb'] == 0) ? 1 : $campos_pecas_por_emb[0]['pecas_por_emb'];
        
        //Sendo assim eu faço um arredondamento dessa Qtde de Excesso p/ Baixo ...
        $total_eq_pas_atrelados = intval($total_eq_pas_atrelados / $pecas_por_emb) * $pecas_por_emb;
        if($total_eq_pas_atrelados < 0) $total_eq_pas_atrelados = 0;

        //Guardo o mesmo valor de "Qtde de Queima p/ Estoque" p/ todos os PA(s) encontrados do Custo de forma a facilitar relatórios ...
        for($i = 0; $i < count($vetor_pas_atrelados); $i++) {
            $sql = "UPDATE `produtos_acabados` SET `qtde_queima_estoque` = '$total_eq_pas_atrelados' WHERE `id_produto_acabado` = '$vetor_pas_atrelados[$i]' LIMIT 1 ";
            bancos::sql($sql);
        }
        
        return array('total_eq_pas_atrelados' => $total_eq_pas_atrelados);
    }
    
    /*Essa função traz o somatório de Programado "$total_eq_pas_atrelados" do PA passado por parâmetro e de 
    todos os PA´s em que ele atrelados à 7ª Etapa ou que esses PA´s estão atrelados a 7ª Etapa dele ...*/
    function calculo_programado_pas_atrelados($id_produto_acabado) {
        if(!class_exists('custos')) require 'custos.php';//CASO EXISTA EU DESVIO A CLASSE ...

        /*Sempre deleto essa variável pq se essa função for chamada p/ ser rodada, por ser uma variável
        global acaba acumulando id de outros PAs dos Loops anteriores ...*/
        if(isset($id_pa_atrelados)) unset($id_pa_atrelados);
        
        /*Aqui eu verifico se o PA que foi passado por parâmetro tem a marcação de visualização
        ou seja se ele for componente de um outro, esse não pode ser exibido ...*/
        $sql = "SELECT explodir_view_estoque 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        $campos_view_explodir = bancos::sql($sql);
        if($campos_view_explodir[0]['explodir_view_estoque'] == 'S') $vetor_pas_atrelados = custos::pas_atrelados($id_produto_acabado);//Aqui eu também retorno o próprio PA que foi passado por parâmetro ...
        
        /*Nessa parte calcula o somatório de programado do PA passado por parâmetro e de todos os PA´s em que ele 
        atrelados à 7ª Etapa ou que esses PA´s estão atrelados a 7ª Etapa dele ...*/
        $id_pas_atrelados = (count($vetor_pas_atrelados) > 0) ? implode(',', $vetor_pas_atrelados) : 0;//Controle p/ não furar o SQL abaixo ...

        /*SQL que pega a qtde comprometida programada do sistema, para não produzir PA(s) p/ Pedidos 
        acima de um mês ...

        Exemplo: Hoje é dia 17/10/2014, então o sistema só irá trazer Pedidos que sejam acima de 17/11/2014.*/
        $sql = "SELECT (SUM(`qtde_pendente`)) AS total_programado_pas_atrelados 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
                WHERE pvi.`id_produto_acabado` IN ($id_pas_atrelados) 
                AND pv.`faturar_em` >= DATE_ADD('".date('Y-m-d')."', INTERVAL 1 MONTH) ";//Só até próximos 30 dias ...
        $campos_programado              = bancos::sql($sql);
        $total_programado_pas_atrelados = $campos_programado[0]['total_programado_pas_atrelados'];
        
        return array('total_programado_pas_atrelados' => $total_programado_pas_atrelados);
    }
    
    /*Essa função traz o somatório de OE(s) "$total_oe_pas_atrelados" do PA passado por parâmetro e de 
    todos os PA´s em que ele atrelados à 7ª Etapa ou que esses PA´s estão atrelados a 7ª Etapa dele ...*/
    function calculo_oes_pas_atrelados($id_produto_acabado) {
        if(!class_exists('custos')) require 'custos.php';//CASO EXISTA EU DESVIO A CLASSE ...

        /*Sempre deleto essa variável pq se essa função for chamada p/ ser rodada, por ser uma variável
        global acaba acumulando id de outros PAs dos Loops anteriores ...*/
        if(isset($id_pa_atrelados)) unset($id_pa_atrelados);
        
        /*Aqui eu verifico se o PA que foi passado por parâmetro tem a marcação de visualização
        ou seja se ele for componente de um outro, esse não pode ser exibido ...*/
        $sql = "SELECT `explodir_view_estoque` 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        $campos_view_explodir = bancos::sql($sql);
        if($campos_view_explodir[0]['explodir_view_estoque'] == 'S') $vetor_pas_atrelados = custos::pas_atrelados($id_produto_acabado);//Aqui eu também retorno o próprio PA que foi passado por parâmetro ...
        
        for($i = 0; $i < count($vetor_pas_atrelados); $i++) {
            $vetor_estoque_acabado  = estoque_acabado::qtde_estoque($vetor_pas_atrelados[$i]);
            $total_oe_pas_atrelados+= $vetor_estoque_acabado[11];
        }
        return array('total_oe_pas_atrelados' => $total_oe_pas_atrelados);
    }

//Função que bloqueia a Emissão de Pedido e de Nota Fiscal, caso esteja incompleto o Cadastro de Cliente
    function cadastro_cliente_incompleto($id_cliente) {
        $sql = "SELECT id_pais, id_uf, endereco 
                FROM `clientes` 
                WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
        $campos = bancos::sql($sql);
        $id_pais = $campos[0]['id_pais'];
        $id_uf = $campos[0]['id_uf'];
        $endereco = $campos[0]['endereco'];
//Se não estiver preenchida a Unidade Federal então ...
        if($id_pais == '' || $id_pais == 0) {//Tem que força o preenchimento do País
            $valor = 1;
        }else {//Se o país já estiver preenchido, legal ...
            if($id_pais == 31) {//Verificação para países que são do Brasil
//Se não estiver preenchida a Un. Federal e o Endereço
                if($id_uf == 0 || $endereco == '') {
                    $valor = 1;
                }else {
                    $valor = 0;
                }
            }else {
                $valor = 0;
            }
        }
        return $valor;
    }
    
    function desconto_icms_sgd($forma_venda, $id_cliente, $id_produto_acabado) {
        //Busco alguns dados do Cliente que serão utilizados mais abaixo ...
        $sql = "SELECT `id_pais`, `id_uf`, `trading` 
                FROM `clientes` 
                WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
        $campos = bancos::sql($sql);

        //Dados de ICMS e Reducao da Classificao p/ São Paulo ...
        $dados_produto      = self::dados_impostos_pa($id_produto_acabado, 1);
        $icms_cf_uf_sp      = $dados_produto['icms'];
        $reducao_uf_sp      = $dados_produto['reducao'];
        
        //Dados de ICMS e Reducao da Classificao p/ a UF do Cliente ...
        $dados_produto      = self::dados_impostos_pa($id_produto_acabado, $campos[0]['id_uf']);
        $icms_cf_uf_cliente = $dados_produto['icms'];
        $reducao_uf_cliente = $dados_produto['reducao'];
        
        $ICMS_SP            = ($icms_cf_uf_sp) * (100 - $reducao_uf_sp) / 100;
        //SGD ou Cliente Estrangeiro ou Trading ...
        if($forma_venda == 'S' || $campos[0]['id_pais'] != 31 || $campos[0]['trading'] == 1) {
            $desconto_icms_sgd  = (int)(0.57 * $ICMS_SP);//Conforme cartilha 10/2008 do Wilson ...
        }else {//Nota Fiscal ...
            $desconto_icms_sgd  = $ICMS_SP - ($icms_cf_uf_cliente) * (100 - $reducao_uf_cliente) / 100;
        }
        return $desconto_icms_sgd;
    }
    
    /*Essa função é utilizada em vários pontos do sistema, mas principalmente na parte Comercial 
    "Vendas" e "Faturamento" ...*/
    function prazo_medio($a = 0, $b = 0, $c = 0, $d = 0, $e = 0, $f = 0, $g = 0, $h = 0, $i = 0, $j = 0) {
        /**********************Prazo Médio**********************/
        if($j > 0) {
            $prazo_medio = ($a + $b + $c + $d + $e + $f + $g + $h + $i + $j) / 10;
        }else if($i > 0) {
            $prazo_medio = ($a + $b + $c + $d + $e + $f + $g + $h + $i) / 9;
        }else if($h > 0) {
            $prazo_medio = ($a + $b + $c + $d + $e + $f + $g + $h) / 8;
        }else if($g > 0) {
            $prazo_medio = ($a + $b + $c + $d + $e + $f + $g) / 7;
        }else if($f > 0) {
            $prazo_medio = ($a + $b + $c + $d + $e + $f) / 6;
        }else if($e > 0) {
            $prazo_medio = ($a + $b + $c + $d + $e) / 5;
        }else if($d > 0) {
            $prazo_medio = ($a + $b + $c + $d) / 4;
        }else if($c > 0) {
            $prazo_medio = ($a + $b + $c) / 3;
        }else if($b > 0) {
            $prazo_medio = ($a + $b) / 2;
        }else {
            $prazo_medio = $a;
        }
        /*******************************************************/
        return $prazo_medio;
    }
}
?>