<?
// para fazer no estoque IC quando nao tem nada nao aparece a mensagem mas no acabado aparece
/* no estoque insumo so falta a saida do insumo em producao precisa de uma liberacao da frabrica */
/////////////////* CONTROLES DE PRODUTOS INSUMOS E CONSUMOS *////////////////////
if(!class_exists('bancos')) require 'bancos.php';//CASO EXISTA EU DESVIO A CLASSE ...
class estoque_ic {
    function baixas_pis_para_ops($id_produto_acabado, $id_produto_insumo) {
/*Aqui eu seleciono todas as OP(s) que n�o foram finalizadas ainda e q n�o foram exclu�das, 
de PA(s) que cont�m este PI no Custo ...*/ 
        $sql = "SELECT DISTINCT(bop.`id_op`), ops.`qtde_produzir`, pi.`id_grupo` 
                FROM `ops` 
                INNER JOIN `baixas_ops_vs_pis` bop ON bop.`id_op` = ops.`id_op` 
                INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = bop.`id_produto_insumo` 
                WHERE bop.`id_produto_insumo` = '$id_produto_insumo' AND ops.`id_produto_acabado` = '$id_produto_acabado' 
                AND ops.`status_finalizar` = '0' 
                AND ops.`ativo` = '1' GROUP BY bop.`id_op` ";
        $campos_op = bancos::sql($sql);
        $linhas_op = count($campos_op);
        for($i = 0; $i < $linhas_op; $i++) {
            if($campos_op[$i]['id_grupo'] == 5) {//Se o PI for do Grupo = 'A�O' ...
                /*Busco o Somat�rio de "Baixas / Estornos" da respectiva OP do Loop que foi encontrada 
                na tabela de "baixas_ops_vs_pis" porque �s vezes o Usu�rio pode dar uma Baixa como sendo Zero 
                ou at� o pr�prio sistema pode fazer isso p/ fazer corre��es ... "�s vezes isso acontece porque 
                n�o temos a determinada Mat�ria Prima" ...

                Observa��o: nesse caso � um pouquinho diferente, fizemos assim porque para este Tipo de Caso 
                basta apenas olharmos se j� foi baixado A�O p/ essa OP independente do A�o atual do Custo ...*/
                $sql = "SELECT SUM(bop.`qtde_baixa`) AS total_qtde_baixa 
                        FROM `baixas_ops_vs_pis` bop 
                        INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = bop.`id_produto_insumo` AND pi.`id_grupo` = '5' 
                        WHERE bop.`id_op` = '".$campos_op[$i]['id_op']."' GROUP BY bop.`id_op` ";
            }else {
                /*Busco o Somat�rio de "Baixas / Estornos" da respectiva OP do Loop que foi encontrada 
                na tabela de "baixas_ops_vs_pis" p/ o determinado PI "Mat�ria Prima" que foi passado por 
                par�metro no in�cio dessa fun��o porque �s vezes o Usu�rio pode dar uma Baixa como sendo Zero 
                ou at� o pr�prio sistema pode fazer isso p/ fazer corre��es "�s vezes isso acontece porque 
                n�o temos a determinada Mat�ria Prima" ...*/
                $sql = "SELECT SUM(bop.`qtde_baixa`) AS total_qtde_baixa 
                        FROM `baixas_ops_vs_pis` bop 
                        INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = bop.`id_produto_insumo` AND pi.`id_produto_insumo` = '$id_produto_insumo' 
                        WHERE bop.`id_op` = '".$campos_op[$i]['id_op']."' GROUP BY bop.`id_op` ";
            }
            $campos_baixas_ops_vs_pis = bancos::sql($sql);
            //Se o Total Qtde Baixa for Maior do que Zero ent�o ...
            if($campos_baixas_ops_vs_pis[0]['total_qtde_baixa'] > 0) $total_ops_pis_baixados+= $campos_op[$i]['qtde_produzir'];
        }
        return $total_ops_pis_baixados;
    }

    function necessidade_compras_estoque($id_produto_acabado, $qtde_producao_pa, $compra=0, $id_produto_insumo) {
        $ops_baixadas = (int)estoque_ic::baixas_pis_para_ops($id_produto_acabado, $id_produto_insumo);
        //Aqui eu busco a Necessidade de Compra do PA em todas as suas OP(s) que est�o em Abertas ...
        $sql = "SELECT (SUM(qtde_produzir) - $ops_baixadas) * $qtde_producao_pa AS necessidade_compra 
                FROM `ops` 
                WHERE ops.`status_finalizar` = '0' 
                AND `id_produto_acabado` = '$id_produto_acabado' 
                AND `ativo` = '1' LIMIT 1 ";
        $campos_ops = bancos::sql($sql);
        if(count($campos_ops) > 0) {
            return $campos_ops[0]['necessidade_compra'] + $compra;
        }else {
            return 0;
        }
    }

    function necessidade_compras($id_produto_insumo) { //verifico a necessidade de compras dos componentes com base nas OP's e no componentes_pa da 3� do custo
        $necessidade_total = 0;
        //1� etapa ...
        //Eu busco essa Unidade de Convers�o do PI, porque vou estar utilizando nos c�lculos + abaixo ...
        $sql = "SELECT unidade_conversao 
                FROM `produtos_insumos` 
                WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
        $campos = bancos::sql($sql);
        $unidade_conversao = $campos[0]['unidade_conversao'];
        $sql = "SELECT id_produto_acabado, pecas_por_emb 
                FROM `pas_vs_pis_embs` 
                WHERE `id_produto_insumo` = '$id_produto_insumo' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {//Se existir a Unidade de Convers�o, ent�o eu tamb�m fa�o a divis�o com esta na f�rmula tamb�m ...
            if($unidade_conversao != 0) {
                $qtde = (1 / $campos[$i]['pecas_por_emb']) * (1 / $unidade_conversao);
                /*Caso n�o exista a Unidade de Convers�o, ent�o eu n�o aplico esta na F�rmula p/ que n�o de erro
                de Divis�o por Zero ...*/
            }else {
                $qtde = ($campos[$i]['pecas_por_emb'] > 0) ? (1 / $campos[$i]['pecas_por_emb']) : 0;
            }
            $compra = estoque_acabado::compra_producao($campos[$i]['id_produto_acabado']);
            $necessidade_total+=estoque_ic::necessidade_compras_estoque($campos[$i]['id_produto_acabado'], $qtde, ($compra*$qtde), $id_produto_insumo);
        }
        //2� Etapa ...
        $sql = "SELECT pia.densidade_aco 
                FROM `produtos_insumos` pi 
                INNER JOIN `produtos_insumos_vs_acos` pia ON pi.id_produto_insumo = pia.id_produto_insumo 
                AND pi.`id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
        $campos_densidade_aco   = bancos::sql($sql);//Trazer a densidade do produto insumo para auxiliar no c�lculo de $peso_aco_kg ...
        $densidade              = (count($campos_densidade_aco) == 1) ? $campos_densidade_aco[0]['densidade_aco'] : '';
        //Aqui eu pego o PA do Custo que possui essa Mat�ria Prima ...
        $sql = "SELECT gpa.id_familia, gpa.nome, pa.referencia, pac.id_produto_acabado_custo, 
                pac.id_produto_acabado, pac.qtde_lote, pac.peca_corte, pac.comprimento_1, pac.comprimento_2, 
                pac.qtde_lote AS qtde_producao_pa 
                FROM `produtos_acabados_custos` pac 
                INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pac.id_produto_acabado 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                WHERE pac.`operacao_custo` = '0' 
                AND pac.`id_produto_insumo` = '$id_produto_insumo' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
            ///////////////////////////////////////////////////////////////////////////////////////////
            //A partir daqui j� � a f�rmula ...
            //Tenho que fazer esse Tratamento para que n�o de erro de Divis�o por Zero ...
            $pecas_corte        = ($campos[$i]['peca_corte'] == 0) ? 1 : $campos[$i]['peca_corte'];
            $comprimento_total  = ($campos[$i]['comprimento_1'] + $campos[$i]['comprimento_2']) / 1000;
            //O c�lculo p/ Pinos com PA(s) = 'ESP' � o mesmo com 5% a mais da Quantidade ...
            $fator_perda        = ($campos[$i]['id_familia'] == 2 && $campos[$i]['referencia'] == 'ESP') ? 1.10 : 1.05;
            $peso_aco_kg = $densidade * $comprimento_total * $fator_perda;
            $peso_aco_kg/= $pecas_corte;
            $peso_aco_kg = round($peso_aco_kg, 4);
            ///////////////////////////////////////////////////////////////////////////////////////////
            $necessidade_total+=estoque_ic::necessidade_compras_estoque($campos[$i]['id_produto_acabado'], $peso_aco_kg, 0, $id_produto_insumo);
        }
        //3� Etapa ...
        $sql = "SELECT pac.id_produto_acabado, pp.qtde AS qtde_producao_pa 
                FROM `pacs_vs_pis` pp 
                INNER JOIN `produtos_acabados_custos` pac ON pac.id_produto_acabado_custo = pp.id_produto_acabado_custo 
                WHERE pac.`operacao_custo` = '0' AND pp.id_produto_insumo = '$id_produto_insumo' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) $necessidade_total+= estoque_ic::necessidade_compras_estoque($campos[$i]['id_produto_acabado'], $campos[$i]['qtde_producao_pa'], 0, $id_produto_insumo);
        //6� Etapa ...
        $sql = "SELECT pac.id_produto_acabado, ppu.qtde AS qtde_producao_pa 
                FROM `pacs_vs_pis_usis` ppu 
                INNER JOIN `produtos_acabados_custos` pac ON pac.id_produto_acabado_custo = ppu.id_produto_acabado_custo 
                WHERE pac.`operacao_custo` = '0' AND ppu.id_produto_insumo = '$id_produto_insumo' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) $necessidade_total+= estoque_ic::necessidade_compras_estoque($campos[$i]['id_produto_acabado'], $campos[$i]['qtde_producao_pa'], 0, $id_produto_insumo);
        return $necessidade_total;
    }

    function consumo_mensal($id_produto_insumo, $unidade_conversao=1) {//Verifico o Consumo Mensal dos componentes com base nas OP's e no componentes_pa da 3� do custo
        $mostrar_cmmv = 0; $cmmv = 0;
        ///////////////////////////////// ETAPA 1 ///////////////////////////////////////////////////
        $sql = "Select id_produto_acabado, pecas_por_emb 
                from pas_vs_pis_embs 
                where id_produto_insumo = $id_produto_insumo ";
        $campos2 = bancos::sql($sql);
        $linhas2 = count($campos2);
        for($j = 0; $j < $linhas2; $j++) {
                $mostrar_cmmv=1;
                $qtde= (1 / $campos2[$j]['pecas_por_emb']);
                if($unidade_conversao != 0) {//Caso n�o exista a Unidade de Convers�o, ent�o eu n�o aplico esta na F�rmula p/ que n�o de erro de Divis�o por Zero ...
                        //Se existir a Unidade de Convers�o, ent�o eu tamb�m fa�o a divis�o com esta na f�rmula tamb�m ...
                        $qtde*=(1 / $unidade_conversao);
                }
                $sql = "Select mmv 
                        from produtos_acabados 
                        where `id_produto_acabado` = '".$campos2[$j]['id_produto_acabado']."' LIMIT 1 ";
                $campos3 = bancos::sql($sql);//Busco o M�dia Mensal de Vendas dos PA(s) para auxiliar no c�lculo abaixo ...
                $cmmv+=$qtde*$campos3[0]['mmv'];
        }
        ///////////////////////////////// ETAPA 2 ///////////////////////////////////////////////////
        /*Serve para listar o P.A. que est� atrelado ao custo da Segunda Etapa atrav�s desse Produto Insumo*/
        $sql = "SELECT `id_produto_acabado_custo`, `id_produto_acabado`, `qtde_lote`, `peca_corte`, `comprimento_1`, `comprimento_2` 
                FROM `produtos_acabados_custos` 
                WHERE `id_produto_insumo` = '$id_produto_insumo' 
                AND `operacao_custo` = '0' ";
        $campos2 = bancos::sql($sql);
        $linhas2 = count($campos2);
        //Somente Quando existir Itens ...
        if($linhas2 > 0) {
                $mostrar_cmmv = 1;
                //Trago a densidade do produto insumo, para auxiliar no c�lculo de $peso_aco_kg ...
                $sql = "SELECT pia.densidade_aco 
                        FROM `produtos_insumos` pi 
                        INNER JOIN `produtos_insumos_vs_acos` pia ON pia.id_produto_insumo = pi.id_produto_insumo 
                        WHERE pi.`id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
                $campos3 = bancos::sql($sql);
                if(count($campos3) == 1) {
                        $densidade = $campos3[0]['densidade_aco'];
                }else {
                        $densidade = '';
                }
                for($j=0;$j<$linhas2;$j++) {
                        $pecas_corte 		= ($campos2[$j]['peca_corte'] == 0) ? 1 : $campos2[$j]['peca_corte'];
                        $comprimento_total 	= ($campos2[$j]['comprimento_1'] + $campos2[$j]['comprimento_2']) / 1000;
                        $peso_aco_kg 		= $densidade * $comprimento_total * 1.05;
                        $peso_aco_kg/= $pecas_corte;
                        $peso_aco_kg = round($peso_aco_kg, 3);
                        $sql = "SELECT mmv 
                                FROM `produtos_acabados` 
                                WHERE id_produto_acabado = '".$campos2[$j]['id_produto_acabado']."' LIMIT 1 ";
                        $campos3 = bancos::sql($sql);
                        $cmmv+= $peso_aco_kg * $campos3[0]['mmv'];
                }
        }
        ///////////////////////////////// ETAPA 3 ///////////////////////////////////////////////////
        $sql = "SELECT pac.id_produto_acabado, pp.qtde 
                FROM `pacs_vs_pis` pp 
                INNER JOIN `produtos_acabados_custos` pac ON pac.id_produto_acabado_custo = pp.id_produto_acabado_custo AND pac.operacao_custo = '0' 
                WHERE pp.id_produto_insumo = '$id_produto_insumo' ";
        $campos2 = bancos::sql($sql);
        $linhas2 = count($campos2);
        for($j=0;$j<$linhas2;$j++) {
                $mostrar_cmmv=1;
                $sql = "Select mmv 
                        from produtos_acabados 
                        where id_produto_acabado = '".$campos2[$j]['id_produto_acabado']."' limit 1 ";
                $campos3 = bancos::sql($sql);
                $cmmv+=$campos2[$j]['qtde'] * $campos3[0]['mmv'];
        }
        ///////////////////////////////// ETAPA 6 ///////////////////////////////////////////////////
        $sql = "select pac.id_produto_acabado, ppu.qtde 
                from pacs_vs_pis_usis ppu 
                inner join produtos_acabados_custos pac on pac.id_produto_acabado_custo=ppu.id_produto_acabado_custo AND pac.operacao_custo = '0' 
                where ppu.id_produto_insumo = '$id_produto_insumo' ";
        $campos2 = bancos::sql($sql);
        $linhas2 = count($campos2);
        for($j=0;$j<$linhas2;$j++) {
                $mostrar_cmmv=1;
                $sql = "Select mmv 
                        from produtos_acabados 
                        where id_produto_acabado = '".$campos2[$j]['id_produto_acabado']."' LIMIT 1 ";
                $campos3 = bancos::sql($sql);
                $cmmv+=$campos2[$j]['qtde'] * $campos3[0]['mmv'];
        }
        return array("mostrar_cmmv"=>$mostrar_cmmv, "cmmv"=>$cmmv);
    }

    function atualizar($id_produto_insumo, $id_nfe, $id_nfe_historico = 0) {
        /******************************** Entrada **************************************/
        //////////////////////////////////////////////////////////////////////////////////
        /* localiza a qtde a ser acrescentado da seguintes tabelas         */           //
        //primeiro vejo se o produto esta em grupo aonde pode ser estocado              //
        // Na tabela  estoques insumos erros qtde tipo for 1 e status tb for 0          //
        // Na tabela  nfe_historico para o estoque_insumo qndo status for 0             //
        // depois de pegar este registro passar o status dele para 1 de ambas a tabela  //
        //////////////////////////////////////////////////////////////////////////////////
        // conferencia de estoque insumo  //////
        $data_sys   = date('Y-m-d H:m:s');
        $qtde       = 0;
        
        //SQL padr�o de quando se inicia a Tela, neste eu tamb�m verifico se o PI � um PA ...
        $sql = "SELECT pa.`id_produto_acabado`, nfeh.`qtde_entregue`, nfeh.`id_nfe_historico` 
                FROM `nfe_historicos` nfeh ";

        if($id_nfe_historico > 0) {//Hoje, esse par�metro s� � abastecido quando vem pelo caminho de Liberar Itens de Nota Fiscal ...
            $sql.= "INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = nfeh.`id_produto_insumo` AND pa.`id_produto_insumo` > '0' AND pa.`ativo` = '1' 
                    WHERE nfeh.`id_nfe_historico` = '$id_nfe_historico' ";
        }else {
            $sql.= "INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = nfeh.`id_produto_insumo` AND pa.`id_produto_insumo` = '$id_produto_insumo' AND pa.`id_produto_insumo` > '0' AND pa.`ativo` = '1' 
                    WHERE nfeh.`status` = '0' 
                    AND nfeh.`id_nfe` = '$id_nfe' ";
        }
        $sql.= "ORDER BY nfeh.`qtde_entregue` DESC ";
        $campos = bancos::sql($sql);
        $linhas	= count($campos);
        if($linhas > 0) {//Se for (P.A.) estoque acabado e tem q esta com o ativo 0 para dizer q est� atrelad;
            //Segundo o Roberto sempre estocar� PRAC ou melhor PIPA
            //Desabilitado temporariamente para migra o estoque depois � s� retirar do barra estrela. luis
            if(!class_exists('estoque_acabado')) require('estoque_acabado.php');
            $id_produto_acabado = $campos[0]['id_produto_acabado'];
            estoque_acabado::seta_nova_entrada_pa_op_compras($id_produto_acabado);//Seta o PA que n�o tinha e estoque e acabou de entrar para o estoquista saber a nova entrada
            
            $sql = "SELECT `qtde`, `qtde_disponivel` 
                    FROM `estoques_acabados` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            $campos_verifica = bancos::sql($sql);
            if(count($campos_verifica) == 0) { //se for igual a zero eu inicio o estoque
                bancos::sql("INSERT INTO `estoques_acabados` (`id_produto_acabado`) VALUES ('$id_produto_acabado') ");
                $qtde_real          = 0;
                $qtde_disponivel    = 0;
            }else {
                $qtde_real          = $campos_verifica[0]['qtde'];
                $qtde_disponivel    = $campos_verifica[0]['qtde_disponivel'];
            }
            for($i = 0; $i < $linhas; $i++) {//nfe.id_nfe
                //pego a qtde e depois coloco no estoque acabado
                $qtde_nf		= $campos[$i]['qtde_entregue'];
                $id_nfe_historico	= $campos[$i]['id_nfe_historico'];
                $qtde_real+= $qtde_nf; //fa�o a conta para saber se ele continuar� positivo
/*A partir de agora o Sistema tamb�m levar� em conta o campo qtde_disponivel para fazer 
o controle do Estoque - D�rcio - 10/11/2008...*/
                if($qtde_real >= 0 && $qtde_disponivel >= 0) {
                    
                    /*************Liberando item de Nota Fiscal PA*************/
                    //Mudo o status do Item da Nota Fiscal p/ Liberado, nesse caso � um PA ...
                    $sql = "UPDATE `nfe_historicos` SET `status` = '1' WHERE `id_nfe_historico` = '$id_nfe_historico' LIMIT 1 ";
                    bancos::sql($sql);
                    /**********************************************************/
                    
                    /*Acrescento a qtde do Item de Nota Fiscal que foi liberada p/ o Estoque Real e Dispon�vel, 
                    tamb�m atualizo o campo `data_atualizacao` da tabela "estoques_acabados" com a Data do dia 
                    em que ocorreu a a��o ...*/
                    $sql = "UPDATE `estoques_acabados` SET `qtde` = `qtde` + $qtde_nf, `qtde_disponivel` = `qtde_disponivel` + $qtde_nf, `data_atualizacao` = '$data_sys' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
                    bancos::sql($sql);
                    /*Atualiza o Prazo de Entrega ...
            
                    * Esse WHERE `qtde_disponivel` >= `qtde_pendente`, significa que n�o precisamos 
                    colocar Prazo de Entrega, porque o mesmo j� � imediato ...*/
                    $sql = "UPDATE `estoques_acabados` SET `prazo_entrega` = ' => ERP | $data_sys' WHERE `qtde_disponivel` >= `qtde_pendente` AND `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
                    bancos::sql($sql);
                    /*Criando o Log de Saldo do Estoque, chamo a fun��o de Estoque porque houve mudan�as 
                    na Qtde Real que foi atualizada acima ...*/
                    $vetor_estoque      = estoque_acabado::qtde_estoque($id_produto_acabado);
                    $qtde_real          = $vetor_estoque[0];
                    $sql = "INSERT INTO `rel_saldos_estoques` (`id_rel_saldo_estoque`, `id_produto_acabado`, `id_funcionario`, `qtde_manipulada`, `saldo_est_real`, `data_acao`, `acao`, `obs_acao`) 
                            VALUES (NULL, $id_produto_acabado, $_SESSION[id_funcionario], '$qtde_nf', '$qtde_real', '$data_sys', '2', 'Libera��o de Compras. id_nfe=$id_nfe')";
                    bancos::sql($sql);
                }else {
                    $msg = 1;
                }
            }
            return $msg;
        }else {//Sen�o � Estoque Insumo (P.I.) ...  
            if($id_nfe_historico > 0) {//Hoje, esse par�metro s� � abastecido quando vem pelo caminho de Liberar Itens de Nota Fiscal ...
                //Eu tenho que trazer o PI porque sen�o fura a Query mais abaixo ...
                $sql = "SELECT `id_produto_insumo` 
                        FROM `nfe_historicos` 
                        WHERE `id_nfe_historico` = '$id_nfe_historico' LIMIT 1 ";
                $campos_pi          = bancos::sql($sql);
                $id_produto_insumo  = $campos_pi[0]['id_produto_insumo'];
            }
            //Aqui eu busco todos os Registros de Manipula��o de PI que estejam com Status = '0' q significa n�o Contabilizado ...
            $sql = "SELECT `id_baixa_manipulacao`, `qtde`, `acao` 
                    FROM `baixas_manipulacoes` 
                    WHERE `id_produto_insumo` = '$id_produto_insumo' 
                    AND `status` = '0' ";
            $campos = bancos::sql($sql);
            $linhas	= count($campos);
            for($i = 0; $i < $linhas; $i++) {
                $qtde+= $campos[$i]['qtde'];
                /*Somente se o usu�rio estiver estornando uma Libera��o de Estoque na NF de Compras, 
                que eu mudo no registro dessa tabela de baixas a Qtde p/ Zero, para n�o comprometer o 
                CMM do PI ...*/
                if($campos[$i]['acao'] == 'E') $atualizar_qtde_baixa_manipulacao = " `qtde` = '0', ";
                
                //Uma vez que foi lido o Registro esse passa ser marcado como Contabilizado p/ que n�o seja lido novamente ...
                $sql = "UPDATE `baixas_manipulacoes` SET $atualizar_qtde_baixa_manipulacao `status` = '1' WHERE `id_baixa_manipulacao` = '".$campos[$i]['id_baixa_manipulacao']."' LIMIT 1 ";
                bancos::sql($sql);
            }
            if($qtde != 0) {//Se essa vari�vel foi abastecida e com valor <> de Zero ent�o atualiza o Estoque do PI ...
                $sql = "UPDATE `estoques_insumos` SET `qtde` = `qtde` + '$qtde', `data_atualizacao` = '$data_sys' WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
                bancos::sql($sql);
            }
        }
    }

    function consultar_qtde($id) {
        if(!empty($id)) {
            $campos = bancos::sql("SELECT qtde, data_atualizacao 
                                    FROM `estoques_insumos` 
                                    WHERE `id_produto_insumo` = '$id' ");
            $linhas = count($campos);
            if($linhas > 0) {
                for($i = 0; $i < $linhas; $i++){
                    echo "<br>qtde => ".$campos[$i]["qtde"];
                    echo "<br>data_atualizacao => ".$campos[$i]["data_atualizacao"];
                }
            }else{
                echo "Produto nao encontrado";
            }
        }else {
            echo "Par�metro Inv�lido.";
        }
    }

//Essa func�o s� retorna "compra / producao" dos Pedidos que est�o Contabilizados ...
    function compra_producao($id_produto_insumo) {
        $existe_pendencia_parcial = 0;//Flag que servir� p/ executar o SQL abaixo ...
        //Aqui eu busco as Pend�ncias do PI passado por par�metro de Pedidos que est�o Contabilizados ...
        $sql = "SELECT ip.`qtde`, ip.`status` 
                FROM `itens_pedidos` ip 
                INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` AND p.`programado_descontabilizado` = 'N' 
                WHERE ip.`id_produto_insumo` = '$id_produto_insumo' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
            //S� me interessam os itens em que a sua pend�ncia � Total ...
            if($campos[$i]['status'] == 0) {
                $pendencia_total_pedido+= $campos[$i]['qtde'];
            }else if($campos[$i]['status'] == 1) {//Se existe pend�ncia Parcial ent�o muda a vari�vel Flag ...
                $existe_pendencia_parcial = 1;
            }
        }
        
        if($existe_pendencia_parcial == 1) {
            /*Observa��o:

            Infelizmente n�o consigo simplicar esses 2 SQL abaixo em um s� porque pensando que tenho 2 itens de Pedido com mesma qtde exemplo: 15 cada 
            que somando = 30 e temos 4 entregas em Nota Fiscal por exemplo 2 entregas para cada item de Pedido, o sistema ir� pegar o SUM(dos itens 
            de Pedido e calcular como sendo 60 afinal t�veram 4 entregas em Nota Fiscal, o Mysql favorece nos SUMs "prioriza" a multiplica��o da tabela 
            que cont�m o maior n�mero de registros em cima da outra que tem menos registros e por isso d�o essas diverg�ncias ...*/
            
            //Aqui eu busco o somat�rio da Pend�ncia Parcial do $id_produto_insumo em Pedidos ...
            $sql = "SELECT SUM(`qtde`) AS pendencia_parcial_pedido 
                    FROM `itens_pedidos` 
                    WHERE `id_produto_insumo` = '$id_produto_insumo' 
                    AND `status` = '1' ";
            $campos_pendencia_parcial_pedido    = bancos::sql($sql);
            $pendencia_parcial_pedido           = $campos_pendencia_parcial_pedido[0]['pendencia_parcial_pedido'];
            
            //Aqui eu busco o somat�rio j� entregue em NF da Pend�ncia Parcial do $id_produto_insumo em Pedidos ...
            $sql = "SELECT SUM(nfeh.`qtde_entregue`) AS pendencia_parcial_pedido 
                    FROM `nfe_historicos` nfeh 
                    INNER JOIN `itens_pedidos` ip ON ip.`id_item_pedido` = nfeh.`id_item_pedido` 
                    WHERE ip.`id_produto_insumo` = '$id_produto_insumo' 
                    AND ip.`status` = '1' ";
            $campos_entrega_pendencia_parcial_pedido    = bancos::sql($sql);
            $entrega_pendencia_parcial_pedido           = $campos_entrega_pendencia_parcial_pedido[0]['pendencia_parcial_pedido'];
            
            //Abato da $pendencia_parcial_pedido a $entrega_pendencia_parcial_pedido que consta em NF ...
            $pendencia_parcial_pedido-= $entrega_pendencia_parcial_pedido;
        }
        
        //Aqui eu busca o total que foi recebido do Item na NF mas que n�o foi liberado em Estoque ...
        $sql = "SELECT SUM(`qtde_entregue`) AS total_recebido_n_liberado 
                FROM `nfe_historicos` 
                WHERE `id_produto_insumo` = '$id_produto_insumo' 
                AND `status` = '0' ";
        $campos                        = bancos::sql($sql);
        $total_recebido_n_liberado     = $campos[0]['total_recebido_n_liberado'];
        $restante                      = $pendencia_total_pedido + $total_recebido_n_liberado + $pendencia_parcial_pedido;
        if(is_null($restante) || $restante == '') {
            return 0;
        }else {
            return round($restante, 2);
        }
    }

//Nessa fun��o eu retorno a quantidade baixada do PI que foi passado por par�metro ...
    function baixa_pi($id_produto_insumo, $id_produto_acabado=0) {
        //Se n�o existir Produto Acabado, ent�o eu tento buscar este atrav�s do Produto Insumo ...
        if($id_produto_acabado == 0) {
            //Pega todos os PA�s da tabela custo que faz referencia com este PI, relacao etapa 1, 2 e 3 ...
            $sql = "SELECT id_produto_acabado 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
            $campos 			= bancos::sql($sql);
            $id_produto_acabado = (count($campos) == 1) ? $campos[0]['id_produto_acabado'] : 0;
        }
        if($id_produto_acabado == 0) {//Se n�o encontrou nenhum P.A. anteriomente, ent�o j� retorno 0 logo de cara ...
            $total_qtde_baixa = 0;
        }else {//Se eu encontrar o P.A. ou j� tiver P.A., ent�o eu busco todas as OP(s) atreladas a este ...
            $sql = "SELECT SUM(`qtde_baixa`) qtde_baixa 
                    FROM `ops` 
                    INNER JOIN `baixas_ops_vs_pis` bop ON bop.`id_op` = ops.`id_op` 
                    WHERE bop.`id_produto_insumo` = '$id_produto_insumo' 
                    AND ops.`id_produto_acabado` = '$id_produto_acabado' 
                    AND ops.`status_finalizar` = '0' 
                    AND ops.`ativo` = '1' ";
            $campos = bancos::sql($sql);
            return $campos[0]['qtde_baixa'];
        }
    }
}
?>