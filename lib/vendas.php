<?
if(!class_exists('bancos')) require 'bancos.php';//CASO EXISTA EU DESVIO A CLASSE ...
class vendas {
    function comissao_extra_meta_atingida($id_pedido_venda) {
        $datas                  = genericas::retornar_data_relatorio();
        $data_inicial_fat_mes   = data::datatodate($datas['data_inicial'], '-');
        $data_final_fat_mes     = data::datatodate($datas['data_final'], '-');

        if(substr($data_final_fat_mes, 5, 2) != date('m')) {//FAÇO ISSO SE NÃO ELE ZERA A COMISSÃO EXTRA NO DIA 26 A 31 DE CADA MÊS.
            return false;
        }
        
        $extra_90_porc          = genericas::variavel(41);
        $extra_100_porc         = genericas::variavel(42);
        if($extra_100_porc == 0 && $extra_90_porc == 0) {//Se for zerada não pagará comissão extra, então não calcular nada ...
            return false;
        }
        
        $sql = "SELECT `id_representante` 
                FROM `pedidos_vendas_itens` 
                WHERE `id_pedido_venda` = '$id_pedido_venda' ";
        $campos_representante   = bancos::sql($sql);//pode ser mais de um representante
        $linhas_rep             = count($campos_representante);
        for($i = 0; $i < $linhas_rep; $i++) {
            $id_representante   = $campos_representante[$i]['id_representante'];
            $geral_comissoes    = pdt::funcao_cotas_metas($id_representante, $data_inicial_fat_mes, $data_final_fat_mes, "pv.data_emissao");
            $total_cotas        = $geral_comissoes['total_cotas'];
            $total_vendas       = $geral_comissoes['total_vendas'];
            if($total_cotas != 0) {
                $perc_cota  = (($total_vendas / $total_cotas) * 100);
                $data_sys   = date('Y-m-d H:i:s');
                if($perc_cota >= 100) {
                    $comissao_extra_rep = $extra_100_porc;
                }else if($perc_cota >= 90) {
                    $comissao_extra_rep = $extra_90_porc;
                }else {
                    $comissao_extra_rep = 0;
                }
                $data_periodo_fat = date('Y-m').'-01';
                $sql = "SELECT `id_comissao_extra` 
                        FROM `comissoes_extras` 
                        WHERE `id_representante` = '$id_representante' 
                        AND `data_periodo_fat` = '$data_periodo_fat' LIMIT 1 ";
                $campos_com_extra = bancos::sql($sql);
                if(count($campos_com_extra) == 1) {
                    $id_comissao_extra = $campos_com_extra[0]['id_comissao_extra'];
                    $sql = "UPDATE `comissoes_extras` SET `comissao_meta_atingida` = '$comissao_extra_rep', `data_sys` = '$data_sys' WHERE `id_comissao_extra` = '$id_comissao_extra' ";
                }else {
                    $sql = "INSERT INTO `comissoes_extras` (`id_comissao_extra`, `id_representante`, `comissao_meta_atingida`, `comissao_meta_atingida_sup`, `data_periodo_fat`, `data_sys`) 
                            VALUES (NULL , '$id_representante', '$comissao_extra_rep', '0.00', '$data_periodo_fat', '$data_sys')";
                }
                bancos::sql($sql);
            }
            $sql = "SELECT `id_representante_supervisor` 
                    FROM `representantes_vs_supervisores` 
                    WHERE `id_representante` = '$id_representante' ";
            $campos_rep_sup = bancos::sql($sql);
            if(count($campos_rep_sup) > 0) {
                $id_representante_sup = $campos_rep_sup[0]['id_representante_supervisor'];
                //Cota dos Supervisores ...
                $sql = "SELECT SUM(rc.`cota_mensal`) AS total_cotas_sup 
                        FROM `representantes_vs_cotas` rc 
                        INNER JOIN `representantes` r ON r.`id_representante` = rc.`id_representante` 
                        INNER JOIN `representantes_vs_supervisores` rs ON rs.`id_representante` = r.`id_representante` AND rs.`id_representante_supervisor` = '$id_representante_sup' 
                        WHERE r.`ativo` = '1' GROUP BY rs.`id_representante_supervisor` ";
                $campos_sup = bancos::sql($sql);//Pode ser mais de um representante
                if(count($campos_sup) > 0) {
                    $cota_sup   = $campos_sup[0]['total_cotas_sup'];
                    $dolar_dia  = genericas::moeda_dia('dolar');

                    //Tem que testar o da Mercedes ... Luis
                    $sql = "SELECT IF(c.`id_pais` = '31', SUM(pvi.`qtde` * pvi.`preco_liq_final`), SUM(pvi.`qtde` * pvi.`preco_liq_final`) * $dolar_dia) AS total_vendas 
                            FROM `pedidos_vendas` pv 
                            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
                            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
                            INNER JOIN `representantes` r ON r.`id_representante` = pvi.`id_representante` 
                            INNER JOIN `representantes_vs_supervisores` rs ON rs.`id_representante` = r.`id_representante` 
                            AND rs.`id_representante_supervisor` = '$id_representante_sup' 
                            WHERE pv.`data_emissao` BETWEEN '$data_inicial_fat_mes' AND '$data_final_fat_mes' 
                            AND pv.`liberado` = '1' 
                            GROUP BY rs.`id_representante_supervisor` ";
                    $campos_vendas_sup  = bancos::sql($sql);
                    $total_vendas_sup   = (count($campos_vendas_sup) > 0) ? $campos_vendas_sup[0]['total_vendas'] : 0;
                    if($cota_sup != 0) {
                        $perc_cota = (($total_vendas_sup / $cota_sup) * 100);
                        $data_sys = date('Y-m-d H:i:s');
                        if($perc_cota >= 100) {
                            $comissao_extra_rep_sup = $extra_100_porc;
                        }else if($perc_cota >= 90) {
                            $comissao_extra_rep_sup = $extra_90_porc;
                        }else {
                            $comissao_extra_rep_sup = 0;
                        }
                    }
                }
                $sql = "SELECT `id_comissao_extra` 
                        FROM `comissoes_extras` 
                        WHERE `id_representante` = '$id_representante_sup' 
                        AND `data_periodo_fat` = '$data_periodo_fat' ";
                $campos_com_extra = bancos::sql($sql);
                if(count($campos_com_extra) == 1) {
                    $id_comissao_extra = $campos_com_extra[0]['id_comissao_extra'];
                    $sql = "UPDATE `comissoes_extras` SET `comissao_meta_atingida_sup` = '$comissao_extra_rep_sup', `data_sys` = '$data_sys' WHERE `id_comissao_extra` = '$id_comissao_extra' ";
                }else {
                    $sql = "INSERT INTO `comissoes_extras` (`id_comissao_extra`, `id_representante`, `comissao_meta_atingida`,`comissao_meta_atingida_sup`, `data_periodo_fat`, `data_sys`)
                            VALUES (NULL , '$id_representante_sup', '0.00', '$comissao_extra_rep_sup', '$data_periodo_fat', '$data_sys')";
                }
                bancos::sql($sql);
            }
        }
    }

    function verificar_ml_baixa($id_pedido_venda) {
        if(!class_exists('intermodular'))   require 'variaveis/intermodular.php';//Caso exista eu desvio a Classe ...
        if(!class_exists('comunicacao'))    require 'comunicacao.php';//Caso exista eu desvio a classe
        /*Até a presente data de 18/03/2015 o e-mail que era disparado estava no seguinte molde: 
         
        OC = R < 35
        OC = II < 45
        OC = IR < 40

        À partir das 11:00 horas, Roberto alterou em 18/03/15 por conta da Inclusão do custo Bancário p/ 
        os seguintes valores:
        
        OC = R < 30
        OC = II < 40
        OC = IR < 35 ...*/
        
        /*Verifica todos os PA(s) de O.C. Revenda e Margem de Lucro < 30 % sem Promoção 
        no Pedido passado por parâmetro ...*/
        $sql = "SELECT f.id_login_gerente, pa.id_produto_acabado, ovi.queima_estoque, pvi.qtde, pvi.preco_liq_final, pvi.margem_lucro, pvi.margem_lucro_estimada 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN orcamentos_vendas_itens ovi ON ovi.id_orcamento_venda_item = pvi.id_orcamento_venda_item AND ovi.promocao NOT IN ('A', 'B') 
                INNER JOIN produtos_acabados pa ON pa.id_produto_acabado = ovi.id_produto_acabado AND pa.operacao_custo = '1' 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                INNER JOIN `familias` f ON f.id_familia = gpa.id_familia 
                WHERE GREATEST(pvi.`margem_lucro`, pvi.`margem_lucro_estimada`) < '30' AND pvi.id_pedido_venda = '$id_pedido_venda' ";
        $campos_revenda = bancos::sql($sql);
        $linhas_revenda = count($campos_revenda);
        /*Verifica todos os PA(s) de O.C. Industrial e Sub Operação Industrial e Margem de Lucro < 40 % sem Promoção 
        no Pedido passado por parâmetro ...*/
        $sql = "SELECT f.id_login_gerente, pa.id_produto_acabado, ovi.queima_estoque, pvi.qtde, pvi.preco_liq_final, pvi.margem_lucro, pvi.margem_lucro_estimada 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN orcamentos_vendas_itens ovi ON ovi.id_orcamento_venda_item = pvi.id_orcamento_venda_item AND ovi.promocao NOT IN ('A', 'B') 
                INNER JOIN produtos_acabados pa ON pa.id_produto_acabado = ovi.id_produto_acabado AND pa.operacao_custo = '0' AND pa.operacao_custo_sub = '0' 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                INNER JOIN `familias` f ON f.id_familia = gpa.id_familia 
                WHERE GREATEST(pvi.`margem_lucro`, pvi.`margem_lucro_estimada`) < '40' AND pvi.id_pedido_venda = '$id_pedido_venda' ";
        $campos_ind_ind = bancos::sql($sql);
        $linhas_ind_ind = count($campos_ind_ind);
        /*Verifica todos os PA(s) de O.C. Industrial / Sub Operação Revenda e Margem de Lucro < 35 % no Pedido passado por parâmetro ...
        no Pedido passado por parâmetro ...*/
        $sql = "SELECT f.id_login_gerente, pa.id_produto_acabado, ovi.queima_estoque, pvi.qtde, pvi.preco_liq_final, pvi.margem_lucro, pvi.margem_lucro_estimada 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN orcamentos_vendas_itens ovi ON ovi.id_orcamento_venda_item = pvi.id_orcamento_venda_item AND ovi.promocao NOT IN ('A', 'B') 
                INNER JOIN produtos_acabados pa ON pa.id_produto_acabado = ovi.id_produto_acabado AND pa.operacao_custo = '0' AND pa.operacao_custo_sub = '1' 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                INNER JOIN `familias` f ON f.id_familia = gpa.id_familia 
                WHERE GREATEST(pvi.`margem_lucro`, pvi.`margem_lucro_estimada`) < '35' and pvi.id_pedido_venda = '$id_pedido_venda' ";
        $campos_ind_rev = bancos::sql($sql);
        $linhas_ind_rev = count($campos_ind_rev);
        //Mensagens a exibir ...
        if($linhas_revenda > 0 || $linhas_ind_ind > 0 || $linhas_ind_rev > 0) {
            //Busca dados do Pedido Atual e do Cliente ...
            $sql = "SELECT IF(c.`nomefantasia` = '', c.`razaosocial`, c.`nomefantasia`) AS nome_cliente, 
                    c.`cidade`, pv.`id_funcionario`, pv.`id_empresa`, pv.`finalidade`, 
                    pv.`vencimento1`, pv.`vencimento2`, pv.`vencimento3`, pv.`vencimento4`, 
                    pv.`mg_l_m_g`, pv.`ml_est_m`, pv.`liberado`, pv.`data_sys` 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
                    WHERE pv.`id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
            $campos 	= bancos::sql($sql);
            if($campos[0]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[0]['vencimento4'];
            if($campos[0]['vencimento3'] > 0) $prazo_faturamento= '/'.$campos[0]['vencimento3'].$prazo_faturamento;
            if($campos[0]['vencimento2'] > 0) {
                $prazo_faturamento= $campos[0]['vencimento1'].'/'.$campos[0]['vencimento2'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos[0]['vencimento1'] == 0) ? 'À vista' : $campos[0]['vencimento1'];
            }
            //Aqui é a verificação do Tipo de Nota
            if($campos[0]['id_empresa'] == 4) {//Empresa Grupo ...
                $rotulo_sgd = ' - SGD';
            }else {
                $rotulo_sgd = ' - NF';
//Somente quando a nota é do Tipo NF q existe existe, consequentemente verifico a Finalidade ...
                if($campos[0]['finalidade'] == 'C') {
                    $finalidade = 'CONSUMO';
                }else if($campos[0]['finalidade'] == 'I') {
                    $finalidade = 'INDUSTRIALIZAÇÃO';
                }else {
                    $finalidade = 'REVENDA';
                }
                $rotulo_sgd.= '/'.$finalidade;
            }
            $prazo_faturamento.=$rotulo_sgd;
            
            /**Busca do IP Externo que está cadastrado em alguma Empresa aqui do Sistema ...**/
            $sql = "SELECT ip_externo 
                    FROM `empresas` 
                    WHERE `ip_externo` <> '' LIMIT 1 ";
            $campos_empresa = bancos::sql($sql);
            /*Se encontrar um IP Externo cadastrado, o conteúdo do e-mail apontará p/ esse IP "que é a preferência", 
            do contrário o IP será da onde o usuário está acessando o ERP $_SERVER['HTTP_HOST'] ...*/
            $ip_externo     = (count($campos_empresa) == 1) ? $campos_empresa[0]['ip_externo'] : $_SERVER['HTTP_HOST'];
            /************Compondo a Mensagem para Enviar por e-mail************/
            $mensagem = "O Pedido N.º <a href='http://192.168.1.253/erp/albafer/modulo/vendas/pedidos/itens/itens.php?id_pedido_venda=$id_pedido_venda'>".$id_pedido_venda."</a> / 
                        <a href='http://".$ip_externo."/erp/albafer/modulo/vendas/pedidos/itens/itens.php?id_pedido_venda=$id_pedido_venda'>".$id_pedido_venda." Ext</a> 
                        - Forma de Venda: <font color='blue'>".$prazo_faturamento."</font>";
            
            //Aqui eu busco a Última observação do Follow-UP deste Pedido ...
            $sql = "SELECT `observacao` 
                    FROM `follow_ups` 
                    WHERE `identificacao` = '$id_pedido_venda' 
                    AND `origem` = '2' ORDER BY `id_follow_up` DESC LIMIT 1 ";
            $campos_follow_up   = bancos::sql($sql);
            
            $mensagem.= " do Cliente: <font color='blue'>".$campos[0]['nome_cliente']."</font> da Cidade: <font color='blue'>".$campos[0]['cidade']."</font><br><b> - Observação do Pedido: </b><font color='blue'>".$campos_follow_up[0]['observacao'].".</font>";
            $mensagem.= "<br><br><b>Encontra-se com o(s) seguinte(s) problema(s): </b><br>";

            if($linhas_revenda > 0) {//O.C. Revenda
                for($i = 0; $i < $linhas_revenda; $i++) {
                    //Essa variável é importante p/ enviar e-mail p/ os Gerentes de Linha também ...
                    $vetor_login_gerente[] = $campos_revenda[$i]['id_login_gerente'];
                    $mensagem_queima_estoque = ($campos_revenda[$i]['queima_estoque'] == 'S') ? " - <b>(ESTOQUE EXCEDENTE)</b>" : '';
                    $mensagem.= "<br><font color='red'>".number_format($campos_revenda[$i]['qtde'], 2 , ',', '.').' - '.intermodular::pa_discriminacao($campos_revenda[$i]['id_produto_acabado'])." - OC = R foi liberado com R$ ".number_format($campos_revenda[$i]['preco_liq_final'], 2 , ',', '.')." e ML = ".number_format($campos_revenda[$i]['margem_lucro'], 2 , ',', '.')." % e ML Est ".number_format($campos_revenda[$i]['margem_lucro_estimada'], 2 , ',', '.')." % ".$mensagem_queima_estoque."</font>";
                }
            }
            if($linhas_ind_ind > 0) {//O.C. Industrial e Sub O.C. Industrial
                for($i = 0; $i < $linhas_ind_ind; $i++) {
                    //Essa variável é importante p/ enviar e-mail p/ os Gerentes de Linha também ...
                    $vetor_login_gerente[] = $campos_ind_ind[$i]['id_login_gerente'];
                    $mensagem_queima_estoque = ($campos_ind_ind[$i]['queima_estoque'] == 'S') ? " - <b>(ESTOQUE EXCEDENTE)</b>" : '';
                    $mensagem.= "<br><font color='red'>".number_format($campos_ind_ind[$i]['qtde'], 2 , ',', '.').' - '.intermodular::pa_discriminacao($campos_ind_ind[$i]['id_produto_acabado'])." - OC = I/I foi liberado com R$ ".number_format($campos_ind_ind[$i]['preco_liq_final'], 2 , ',', '.')." e ML = ".number_format($campos_ind_ind[$i]['margem_lucro'], 2 , ',', '.')." % e ML Est ".number_format($campos_ind_ind[$i]['margem_lucro_estimada'], 2 , ',', '.')." % ".$mensagem_queima_estoque."</font>";
                }
            }
            if($linhas_ind_rev > 0) {//O.C. Industrial e Sub O.C. Revenda
                for($i = 0; $i < $linhas_ind_rev; $i++) {					
                    //Essa variável é importante p/ enviar e-mail p/ os Gerentes de Linha também ...
                    $vetor_login_gerente[] = $campos_ind_rev[$i]['id_login_gerente'];
                    $mensagem_queima_estoque = ($campos_ind_rev[$i]['queima_estoque'] == 'S') ? " - <b>(ESTOQUE EXCEDENTE)</b>" : '';
                    $mensagem.= "<br><font color='red'>".number_format($campos_ind_rev[$i]['qtde'], 2 , ',', '.').' - '.intermodular::pa_discriminacao($campos_ind_rev[$i]['id_produto_acabado'])." - OC = I/R foi liberado com R$ ".number_format($campos_ind_rev[$i]['preco_liq_final'], 2 , ',', '.')." e ML = ".number_format($campos_ind_rev[$i]['margem_lucro'], 2 , ',', '.')." % e ML Est ".number_format($campos_ind_rev[$i]['margem_lucro_estimada'], 2 , ',', '.')." % ".$mensagem_queima_estoque."</font>";
                }
            }
            $mensagem.= "<br><br><font color='red'><b>ML Geral do Pedido: </b>".number_format($campos[0]['mg_l_m_g'], 2 , ',', '.')." %, <b>MLEst Geral do Pedido: </b>".number_format($campos[0]['ml_est_m'], 2 , ',', '.')." %</font>";
            $mensagem.= "<br><br><b>Situação do Pedido: </b>";
            $mensagem.= ($campos[0]['liberado'] == 1) ? "<font color='darkblue'><b>LIBERADO</b></font>" : "<font color='red'><b>Ñ LIBERADO</b></font>";
            //Busca o nome do funcionário que fez a última alteração no Pedido ...
            $sql = "SELECT nome 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = ".$campos[0]['id_funcionario']." LIMIT 1 ";
            $campos_funcionario = bancos::sql($sql);
            $mensagem.= ' - <b>Liberado por '.$campos_funcionario[0]['nome'].'</b><font color="darkblue"><b> - Data: </b></font>'.data::datetodata(substr($campos[0]['data_sys'], 0, 10), '/').' e <font color="darkblue"><b>Hora: </b></font>'.substr($campos[0]['data_sys'], 11, 8);
            $mensagem.= "<br><br>Data e Hora de Envio: ".date('d-m-Y H:i:s');
            /**********************Controle com o Gerenciamento de Linha**********************/
            $logins_gerentes = array_unique($vetor_login_gerente);//Removo os valores duplicados ...
            sort($logins_gerentes);//Ordena o vetor ...
            foreach($logins_gerentes as $id_login_gerente) {
                //Através do id_login eu busco qual é o e-mail do funcionário "Gerente de Linha" p/ mantê-lo informado ...
                $sql = "SELECT f.`email_externo` 
                        FROM `logins` l 
                        INNER JOIN `funcionarios` f ON f.`id_funcionario` = l.`id_funcionario` 
                        WHERE `id_login` = '$id_login_gerente' LIMIT 1 ";
                $campos_email           = bancos::sql($sql);
                $emails_gerentes_linhas.= $campos_email[0]['email_externo'].', ';
            }
            //O sistema também envia uma cópia dos Problemas referentes aos Produtos p/ os Gerentes de Linhas ...
            $emails_gerentes_linhas = substr($emails_gerentes_linhas, 0, strlen($emails_gerentes_linhas) - 2);
            /*********************************************************************************/
            $destino = $pedidos_em_nao_conformidade;
            $assunto = 'Pedidos em Não Conformidade '.$campos[0]['nome_cliente'];
            comunicacao::email('ERP - GRUPO ALBAFER', $destino, $emails_gerentes_linhas, $assunto, $mensagem);
        }
    }

    function media_mensal_venda($id_produto_acabado) {
        $data_inicial_ultimos_6meses    = data::datatodate(data::adicionar_data_hora(date('d-m-Y'), -180), '-');
        $data_inicial_ultimos_12meses   = data::datatodate(data::adicionar_data_hora(date('d-m-Y'), -365), '-');

        //Aqui eu pego a Data de Inclusão desse PA ...
        $sql = "SELECT SUBSTRING(`data_sys`, 1, 10) AS data_inclusao_pa 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        $campos                 = bancos::sql($sql);
        $data_inclusao_pa       = $campos[0]['data_inclusao_pa'];
        
        //Verifico quantos dias se passaram da Data de Inclusão do PA até a Data Atual "Hoje" ...
        $vetor_diferenca_data   = data::diferenca_data($data_inclusao_pa, date('Y-m-d'));
        $qtde_dias              = $vetor_diferenca_data[0];
        $qtde_meses             = $qtde_dias / 30;
        if($qtde_meses < 1)     $qtde_meses = 1;
        
        if($qtde_meses >= 12) {//Produto já existe há pelo menos 1 Ano, mais de 12 meses ...
            //Busco tudo o que foi Vendido do PA passado por parâmetro nos últimos 6 meses ...
            $sql = "SELECT SUM(pvi.`qtde`) AS qtde_vendida_ultimos_6meses 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                    WHERE pv.`data_emissao` > '$data_inicial_ultimos_6meses' 
                    AND pvi.`id_produto_acabado` = '$id_produto_acabado' ";
            $campos_pedidos_ultimos_6meses  = bancos::sql($sql);
            $qtde_vendida_ultimos_6meses    = $campos_pedidos_ultimos_6meses[0]['qtde_vendida_ultimos_6meses'];
            $mmv_ultimos_6meses             = ($campos_pedidos_ultimos_6meses[0]['qtde_vendida_ultimos_6meses'] / 6);
            /*Busco tudo o que foi Vendido do PA passado por parâmetro nos últimos 12 meses "1ª Parte - 6 meses" ...
         
            Exemplo: Hoje é 10/03/2015

            * A primeira Query acima me traz dados de 10/09/2014 à 10/03/2015 

            * A segunda Query apesar de ser 12 meses, só me traz a Diferença que é 10/03/2014 à 09/09/2014 porque o 
            segundo período de 10/09/2014 à 10/03/2015 já tenho na query acima, de modo a aliviar o BD nesse caso ...*/

            //Busco tudo o que foi Vendido do PA passado por parâmetro nos últimos 12 meses ...
            $sql = "SELECT SUM(pvi.`qtde`) AS qtde_vendida_6meses_antes_dos_ultimos_6meses 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                    WHERE pv.`data_emissao` BETWEEN '$data_inicial_ultimos_12meses' AND '$data_inicial_ultimos_6meses' 
                    AND pvi.`id_produto_acabado` = '$id_produto_acabado' ";
            $campos_pedidos_6meses_antes_dos_ultimos_6meses = bancos::sql($sql);
            $qtde_vendida_6meses_antes_dos_ultimos_6meses   = $campos_pedidos_6meses_antes_dos_ultimos_6meses[0]['qtde_vendida_6meses_antes_dos_ultimos_6meses'];
            $qtde_vendida_ultimos_12meses                   = $qtde_vendida_ultimos_6meses + $qtde_vendida_6meses_antes_dos_ultimos_6meses;
            $mmv_ultimos_12meses                            = ($campos_pedidos_ultimos_6meses[0]['qtde_vendida_ultimos_6meses'] + $campos_pedidos_6meses_antes_dos_ultimos_6meses[0]['qtde_vendida_6meses_antes_dos_ultimos_6meses']) / 12;
            
            $mmv = max($mmv_ultimos_6meses, $mmv_ultimos_12meses);

            return array('qtde_vendida' => $qtde_vendida_ultimos_12meses, 'mmv' => $mmv);
        }else if($qtde_meses >= 6 && $qtde_meses < 12) {//Produto já existe há alguns meses, menos de 1 Ano ...
            //Busco tudo o que foi Vendido do PA passado por parâmetro nos últimos 6 meses ...
            $sql = "SELECT SUM(pvi.`qtde`) AS qtde_vendida_ultimos_6meses 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                    WHERE pv.`data_emissao` > '$data_inicial_ultimos_6meses' 
                    AND pvi.`id_produto_acabado` = '$id_produto_acabado' ";
            $campos_pedidos_ultimos_6meses  = bancos::sql($sql);
            $qtde_vendida_ultimos_6meses    = $campos_pedidos_ultimos_6meses[0]['qtde_vendida_ultimos_6meses'];
            $mmv_ultimos_6meses             = ($campos_pedidos_ultimos_6meses[0]['qtde_vendida_ultimos_6meses'] / 6);
            
            //Verifico quantos dias se passaram da Data de Inclusão do PA até a Data Inicial dos Últimos 6 meses ...
            $vetor_diferenca_data               = data::diferenca_data($data_inclusao_pa, $data_inicial_ultimos_6meses);
            $qtde_dias_antes_dos_ultimos_6meses = $vetor_diferenca_data[0];
            $qtde_meses_antes_dos_ultimos_6meses= $qtde_dias_antes_dos_ultimos_6meses / 30;
            
            if($qtde_meses_antes_dos_ultimos_6meses < 1) $qtde_meses_antes_dos_ultimos_6meses = 1;
            /*"Supondo que sejam 10 meses por exemplo" 
            
            Busco tudo o que foi Vendido do PA passado por parâmetro nos últimos x meses "1ª Parte - x meses" ...
         
            Exemplo: Hoje é 10/03/2015

            * A primeira Query acima me traz dados de 10/09/2014 à 10/03/2015

            * A segunda Query apesar de ser x meses, só me traz a Diferença que é 10/05/2014 à 09/09/2014 
            porque o segundo período de 10/09/2014 à 10/03/2015 
            já tenho na query acima, de modo a aliviar o BD nesse caso ...*/

            //Busco tudo o que foi Vendido do PA passado por parâmetro nos últimos x meses ...
            $sql = "SELECT SUM(pvi.`qtde`) AS qtde_vendida_xmeses_antes_dos_ultimos_6meses 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                    WHERE pv.`data_emissao` BETWEEN '$data_inclusao_pa' AND '$data_inicial_ultimos_6meses' 
                    AND pvi.`id_produto_acabado` = '$id_produto_acabado' ";
            $campos_pedidos_xmeses_antes_dos_ultimos_6meses = bancos::sql($sql);
            $qtde_vendida_xmeses_antes_dos_ultimos_6meses   = $campos_pedidos_xmeses_antes_dos_ultimos_6meses[0]['qtde_vendida_xmeses_antes_dos_ultimos_6meses'];
            $qtde_vendida_ultimos_xmeses                    = $qtde_vendida_ultimos_6meses + $qtde_vendida_xmeses_antes_dos_ultimos_6meses;
            $mmv_ultimos_xmeses                             = ($campos_pedidos_ultimos_6meses[0]['qtde_vendida_ultimos_6meses'] + $campos_pedidos_xmeses_antes_dos_ultimos_6meses[0]['qtde_vendida_xmeses_antes_dos_ultimos_6meses']) / (6 + $qtde_meses_antes_dos_ultimos_6meses);
            
            $mmv = max($mmv_ultimos_6meses, $mmv_ultimos_xmeses);
            
            return array('qtde_vendida' => $qtde_vendida_ultimos_12meses, 'mmv' => $mmv);
        }else {//Menos de 6 meses ...
            $sql = "SELECT SUM(pvi.`qtde`) AS qtde_vendida_ultimos_xmeses 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                    WHERE pv.`data_emissao` >= '$data_inclusao_pa' 
                    AND pvi.`id_produto_acabado` = '$id_produto_acabado' ";
            $campos_pedidos_ultimos_xmeses  = bancos::sql($sql);
            $qtde_vendida_ultimos_xmeses    = $campos_pedidos_ultimos_xmeses[0]['qtde_vendida_ultimos_xmeses'];
            $mmv_ultimos_xmeses             = ($campos_pedidos_ultimos_xmeses[0]['qtde_vendida_ultimos_xmeses'] / $qtde_meses);
            
            return array('qtde_vendida' => $qtde_vendida_ultimos_xmeses, 'mmv' => $mmv_ultimos_xmeses);
        }
    }

    function verificar_pa_custo($id_produto_acabado) { //Função que volta o custo para não liberado, caso tenha passado de X dias ...
        $sql = "SELECT `referencia`, `operacao_custo` 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        $campos = bancos::sql($sql);//Busca a operação de custo do através do id_produto_acabado
        $referencia     = $campos[0]['referencia'];
        $operacao_custo = $campos[0]['operacao_custo'];
        if($referencia == 'ESP') {//Somente se for "ESP" ...
            $sql = "SELECT SUBSTRING(`data_sys`, 1, 10) AS ultima_data_atualizacao, id_produto_acabado_custo 
                    FROM `produtos_acabados_custos` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' 
                    AND `operacao_custo` = '$operacao_custo' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) > 0) {//Tem Custo então segue em frente ...
                $ultima_data_atualizacao    = $campos[0]['ultima_data_atualizacao'];
                $prazo_dias_validade_custo  = genericas::variavel(43);
                $data_retrocedente          = data::datatodate(data::adicionar_data_hora(date('d-m-Y'), -$prazo_dias_validade_custo), '-');
                if($ultima_data_atualizacao <= $data_retrocedente) {//Significa q o Custo esta ultrapassado ...
                    custos::liberar_desliberar_custo($campos[0]['id_produto_acabado_custo'], 'NAO');//se tem item na 7ª etapa bloqueado 
                }
            }
        }else {//Então pego os PAs filhos ou seja os da 7ª etapa ao qual ele depende ...
            $sql = "SELECT pp.id_produto_acabado 
                    FROM `pacs_vs_pas` pp 
                    INNER JOIN `produtos_acabados_custos` pac ON pac.`id_produto_acabado_custo` = pp.`id_produto_acabado_custo` 
                    WHERE pac.`id_produto_acabado` = '$id_produto_acabado' 
                    AND pac.`operacao_custo` = '$operacao_custo' ";
            $campos = bancos::sql($sql);// seleciono todos os PAs da 7ª etapa do PA current para verificar se ele tem custo rev > q X dias de atualizacao dp fornec
            $linhas = count($campos);
            for($i = 0; $i < $linhas; $i++) vendas::verificar_pa_custo($campos[$i]['id_produto_acabado']);
        }
    }

    function recalcular_item_orcamento($id_produto_acabado_custo) {
//Busca o id_produto_acabado através do id_produto_acabado_custo
        $sql = "SELECT id_produto_acabado 
                FROM `produtos_acabados_custos` 
                WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
        $campos             = bancos::sql($sql);
        $id_produto_acabado = $campos[0]['id_produto_acabado'];

//Aqui traz todos os orçamentos q contém o id_produto_acabado, q não estejam congelados e q tenham o retorno = 'DEPTO TÉCNICO'
        $sql = "SELECT ov.id_orcamento_venda, ovi.id_orcamento_venda_item 
                FROM `orcamentos_vendas_itens` ovi 
                INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` AND ov.`congelar` = 'N' 
                WHERE ovi.`id_produto_acabado` = '$id_produto_acabado' 
                AND ovi.`preco_liq_fat_disc` = 'DEPTO TÉCNICO' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        //Aki chama a função para recalcular o preço do item do Orçamento
        for($i = 0; $i < $linhas; $i++) vendas::calculo_preco_liq_final_item_orc($campos[$i]['id_orcamento_venda_item'], 'S');
    }
    
    //Função que retorna o Preço Líquido e Preço de Venda Médio do Produto Acabado ...
    function preco_venda($id_produto_acabado) {
        //Busca de alguns campos do PA p/ fazer o cálculo ...
        $sql = "SELECT ged.desc_base_a_nac, ged.desc_base_b_nac, ged.acrescimo_base_nac, ged.desc_medio_pa, pa.preco_unitario 
                FROM `produtos_acabados` pa 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                WHERE pa.`id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        $campos                 = bancos::sql($sql);
        $preco_lista_liquido_rs = $campos[0]['preco_unitario'] * (1 - $campos[0]['desc_base_a_nac'] / 100) * (1 - $campos[0]['desc_base_b_nac'] / 100) * (1 + $campos[0]['acrescimo_base_nac'] / 100);
        $preco_venda_medio_rs   = ($campos[0]['desc_medio_pa'] > 0) ? ($preco_lista_liquido_rs * $campos[0]['desc_medio_pa']) : $preco_lista_liquido_rs;
        return array('preco_lista_liquido_rs'=>$preco_lista_liquido_rs, 'preco_venda_medio_rs'=>$preco_venda_medio_rs);
    }

//Função q atualiza a 'Data e Hora' e 'Data de Emissão' que o usuário realizou a alteração nesse orçamento ...	
    function atualizar_orcamento_vendas($id_orcamento_venda) {
        $data_sys   = date('Y-m-d H:i:s');
        $data_atual = date('Y-m-d');
        //Aqui eu verifico se o Orçamento tem pelo menos 1 item em Queima ...
        $sql = "SELECT COUNT(`id_orcamento_venda_item`) AS qtde_itens_em_queima_estoque 
                FROM `orcamentos_vendas_itens` 
                WHERE `id_orcamento_venda` = '$id_orcamento_venda' 
                AND `queima_estoque` = 'S' ";
        $campos_orcamentos              = bancos::sql($sql);
        $qtde_itens_em_queima_estoque   = $campos_orcamentos[0]['qtde_itens_em_queima_estoque'];

        //Aqui eu verifico se o Orçamento está congelado ...
        $sql = "SELECT `congelar` 
                FROM `orcamentos_vendas` 
                WHERE `id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
        $campos = bancos::sql($sql);

        /*Só é possível mudar a Data de Emissão do ORC se não tivermos nenhum item em Queima, 
        pois os itens de Queima tem validade em relação a Data de Emissão ou se o mesmo 
        estiver Descongelado ...*/
        if($qtde_itens_em_queima_estoque == 0 && $campos[0]['congelar'] == 'N') $condicao_data_emissao = " `data_emissao` = '$data_atual', ";
        
        $condicao_logado = (!empty($_SESSION[id_funcionario])) ? " `id_funcionario` = '$_SESSION[id_funcionario]' " : " `id_login` = '$_SESSION[id_login]' ";
        
        $sql = "UPDATE `orcamentos_vendas` SET $condicao_logado, $condicao_data_emissao `data_sys` = '$data_sys' WHERE `id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
        bancos::sql($sql);
    }

    function nova_comissao_representante($id_orcamento_venda_item, $preco_liq_final) {//Função que calcula a Comissão do Representante ...
        //Busca de alguns dados necessários ...
        $sql = "SELECT ov.id_cliente, ovi.id_orcamento_venda, ovi.id_produto_acabado, ovi.id_representante, ovi.margem_lucro_estimada 
                FROM `orcamentos_vendas_itens` ovi 
                INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda 
                WHERE ovi.`id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
        $campos_orcamentos_itens    = bancos::sql($sql);

        //Essas váriaveis serão utilizadas mais abaixo no decorrer desse Método ...
        $id_cliente             = $campos_orcamentos_itens[0]['id_cliente'];
        $id_orcamento_venda     = $campos_orcamentos_itens[0]['id_orcamento_venda'];
        $id_produto_acabado     = $campos_orcamentos_itens[0]['id_produto_acabado'];
        $id_representante       = $campos_orcamentos_itens[0]['id_representante'];
        $margem_lucro_estimada  = $campos_orcamentos_itens[0]['margem_lucro_estimada'];
        
        /*Se o Representante for "Direto", não existe + Comissão porque Direto não Recebe Comissão, corrigimos esse 
        erro a partir do dia 06/06/2014 ...*/
        if($id_representante == 1 || $id_representante == 120) {
            return 0;
        }else {//Se for outro Representante ...
            $sql = "SELECT `porc_comissao_fixa` 
                    FROM `representantes` 
                    WHERE `id_representante` = '$id_representante' LIMIT 1 ";
            $campos_comissao_fixa = bancos::sql($sql);
            if($campos_comissao_fixa[0]['porc_comissao_fixa'] > 0) {//Verifica se o Rep possui Com. Fixa ...
                return $campos_comissao_fixa[0]['porc_comissao_fixa'];
            }else {
/**************************************************************************************/
/******************************Comissão Nova - Parte 1 ********************************/
                //Pega a UF do cliente ...
                $sql = "SELECT `id_uf` 
                        FROM `clientes` 
                        WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
                $campos_temp = bancos::sql($sql);
                if(count($campos_temp) > 0) $id_uf_cliente = $campos_temp[0]['id_uf'];
                $tx_financeira              = custos::calculo_taxa_financeira($id_orcamento_venda);
                $tx_financeira              = round($tx_financeira, 2);
                $margem_lucro               = custos::margem_lucro($id_orcamento_venda_item, $tx_financeira, $id_uf_cliente, $preco_liq_final);
                $margem_lucro_instantanea   = $margem_lucro[0];// pego o valor da margem de lucro deste produto ...
                //Para não dar erro de programação referente a qual comissão pagar, trato esse campo ..
                $margem_lucro_instantanea   = str_replace('.', '', $margem_lucro_instantanea);
                $margem_lucro_instantanea   = str_replace(',', '.', $margem_lucro_instantanea);
                
                //Verificamos qual é a maior Margem p/ confrontar com a Margem de Lucro Mínima ...
                $maior_margem_utilizar  = max($margem_lucro_instantanea, $margem_lucro_estimada);
                $comissao_extra         = 0;//Valor Default ...
/*****************************************************************************************/
/************************************Queima de Estoque************************************/
/*****************************************************************************************/
                /*Se existir algum Item de Orçamento que está em Queima de Estoque, então eu sobreponho 
                a Comissão Extra da Divisão do Grupo com a Comissão Extra de Queima  ... */
                $sql = "SELECT `queima_estoque` 
                        FROM `orcamentos_vendas_itens` 
                        WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
                $campos_itens_queima 	= bancos::sql($sql);
                if($campos_itens_queima[0]['queima_estoque'] == 'S') {//Queima de Estoque reflete na Comissão Extra ...
                    $comissao_extra = genericas::variavel(46);
                }else {//Não é Queima de Estoque, então ...
    /**************************************************************************************/
    /************************************Comissão Extra************************************/
    /**************************************************************************************/
                    /*Aqui eu pego a Comissão Extra do P.A. na Tabela de Divisões Grupos PA(s) desde que: 
                    A Data de Emissão do Orçamento seja <= a Data Limite da Tabela 'Estiver dentro do Prazo' ...*/
                    $sql = "SELECT ged.`comissao_extra`, ged.`data_limite` 
                            FROM `orcamentos_vendas` ov 
                            INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda` = ov.`id_orcamento_venda` 
                            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
                            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                            WHERE ovi.`id_orcamento_venda_item` = '$id_orcamento_venda_item' 
                            AND (ov.`data_emissao` <= ged.`data_limite`) LIMIT 1 ";
                    $campos_comissao_extra = bancos::sql($sql);
                    if(count($campos_comissao_extra) == 1) {//Se estiver tudo ok então ...
                        $valores                            = self::calcular_ml_min_pa_vs_cliente($id_produto_acabado, $id_cliente);
                        $margem_lucro_minima                = $valores['margem_lucro_minima'];
                        $fator_reducao_comissao_extra_x_ml  = genericas::variavel(84);
                        
                        /*Tem agora uma nova Regra: Se a ML Instântanea for abaixo que até X% da ML Mínima 
                        é concedido a Comissão Extra Integral, senão pagamos apenas 50% da Comissão Extra */
                        if($maior_margem_utilizar >= ($fator_reducao_comissao_extra_x_ml * $margem_lucro_minima)) {
                            $comissao_extra = $campos_comissao_extra[0]['comissao_extra'];
                        }else {//Do contrário concedemos apenas 50% da Comissão Extra ...
                            $comissao_extra = $campos_comissao_extra[0]['comissao_extra'] * 0.5;
                        }
                    }
                }
                $ml_min_para_pagar_comissão_extra_ou_queima_perc = genericas::variavel(83);
                
                /*Se a "Maior Margem à utilizar" for maior do que 45, então não precisa mais pagar 
                a Comissão Extra porque significa que foi uma boa venda ...*/
                if($maior_margem_utilizar >= $ml_min_para_pagar_comissão_extra_ou_queima_perc) $comissao_extra = 0;
                
                /*Atualizo a Comissão Extra encontrada no Item Específico do Orçamento independente 
                do caminho em que esta veio ...*/
                $sql = "UPDATE `orcamentos_vendas_itens` SET `comissao_extra` = '$comissao_extra' WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
                bancos::sql($sql);
/**************************************************************************************/
/******************************Comissão Nova - Parte 2 ********************************/
/**************************************************************************************/                        
                $sql = "SELECT `base_comis_dentro_sp`, `comis_vend_fora_sp`, `comis_vend_interior_sp`, `comis_autonomo`, 
                        `comis_vend_sup_interno`, `comis_export` 
                        FROM `novas_comissoes_margens_lucros` 
                        WHERE `margem_lucro` <= '$maior_margem_utilizar' ORDER BY `margem_lucro` DESC LIMIT 1 ";
                $campos_margem = bancos::sql($sql);
                if(count($campos_margem) <= 0) {//Se eu não achar nada, eu pego a 1ª que estiver cadastrada na Tabela ...
                    $sql = "SELECT `base_comis_dentro_sp`, `comis_vend_fora_sp`, `comis_vend_interior_sp`, `comis_autonomo`, 
                            `comis_vend_sup_interno`, `comis_export` 
                            FROM `novas_comissoes_margens_lucros` 
                            ORDER BY `margem_lucro` LIMIT 1 ";
                    $campos_margem = bancos::sql($sql);
                    if(count($campos_margem) <= 0) return 0;//Não encontrei a margem de comissão ...
                }
                /**************************Exceção**************************/
                //Caso for a Mercedes terá o caminho da comissao Interna, mesmo pq ela é supervisora.
                if($id_representante == 14) return $campos_margem[0]['comis_export'];
                /***********************************************************/
                //Seleciono nesta tabela para ve se ele é funcionario ou nao ...
                $sql = "SELECT f.`id_cargo` 
                        FROM `representantes_vs_funcionarios` rf 
                        INNER JOIN `funcionarios` f ON f.`id_funcionario` = rf.`id_funcionario` 
                        WHERE rf.`id_representante` = '$id_representante' LIMIT 1 ";
                $campos = bancos::sql($sql);
                if(count($campos) > 0) {//se achou é pq ele é funcionario
                    if($campos[0]['id_cargo'] == 25 || $campos[0]['id_cargo'] == 27) {//representante externo id_cargo=>27 ou id_cargo=>25 => supervisor é para tratar como vend. externo nova lógica
                        //VERIFICO SE O CLIENTE É PERTINENTE A CIDADES DE COMISSAO INTERNAS
                        $sql = "SELECT `cidade`, `base_pag_comissao`, `id_uf` 
                                FROM `clientes` 
                                WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
                        $campos = bancos::sql($sql);
                        //Verifico se a cidade do cliente consta na tabela de alguma cidades determinadas
                        if(count($campos) > 0) {
                            $cidade             = addslashes($campos[0]['cidade']);
                            $id_uf              = $campos[0]['id_uf'];
                            $base_pag_comissao  = $campos[0]['base_pag_comissao'];
                            //PASSO PARA SP PARA O vendedor GANHAR COMISSÃO DE SP MESMO FORA DO ESTADO (CASO FRATO)
                            if($base_pag_comissao == 1) $id_uf = 1;
                        }else {
                            return 0;
                        }
                        if($id_uf == 1) {//Estado = 'SP' ...
                            $sql = "SELECT `id_comissao_cidade` 
                                    FROM `comissoes_cidades` 
                                    WHERE `comissao_cidade` = '$cidade' LIMIT 1 ";
                            $campos = bancos::sql($sql);
                            if(count($campos) > 0) {//O cliente consta nas cidades q são consideradas perto da empresa então ...
                                return $campos_margem[0]['base_comis_dentro_sp'];
                            }else {//O cliente deve ser de longe onde o vendedor tera gasto extra ...
                                if($base_pag_comissao == 1) {//Mesmo o cliente sendo de fora a comissão será paga como se fosse de São Paulo, marcação do Cadastro do Cliente ...
                                    return $campos_margem[0]['base_comis_dentro_sp'];
                                }else {//O cliente é de fora, recebe como se fosse de fora mesmo ...
                                    //A comissão extra esta sendo corrigida pelo mesmo % do que a comissão normal ...
                                    $comissao_extra*= genericas::variavel(55);
                                    //A Comissão extra sofreu alterações, sendo assim atualizo a mesma ...
                                    $sql = "UPDATE `orcamentos_vendas_itens` SET `comissao_extra` = '$comissao_extra' WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
                                    bancos::sql($sql);
                                    return $campos_margem[0]['comis_vend_interior_sp'];
                                }
                            }
                        }else {//UF <> 'SP' outros estados, serve para obrigar o vendedor a passar os representantes para o autonomo
                            //A comissão extra esta sendo corrigida pelo mesmo % do que a comissão normal ...
                            $comissao_extra*= genericas::variavel(54);
                            //A Comissão extra sofreu alterações, sendo assim atualizo a mesma ...
                            $sql = "UPDATE `orcamentos_vendas_itens` SET `comissao_extra` = '$comissao_extra' WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
                            bancos::sql($sql);

                            $comi_fora_est = $campos_margem[0]['comis_vend_fora_sp'];
                            if($comi_fora_est == 0) {//Para esses casos, nós estamos pagando 0,5% de Comissão ...
                                return '0.5';
                            }else {
                                return $comi_fora_est;
                            }
                        }
                    }else {//Representante Interno id_cargo=>47 ou supervisor interno 109 ...
                        //A comissão extra esta sendo corrigida pelo mesmo % do que a comissão normal ...
                        $comissao_extra*= genericas::variavel(57);
                        //A Comissão extra sofreu alterações, sendo assim atualizo a mesma ...
                        $sql = "UPDATE `orcamentos_vendas_itens` SET `comissao_extra` = '$comissao_extra' WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
                        bancos::sql($sql);
                        return $campos_margem[0]['comis_vend_sup_interno'];
                    }
                }else {//Como ele é autonomo automaticamente ele pega a comissao externo especial ...
                    //A comissão do autonomo é sempre 40% a mais da Base Comissão Vendedor dentro de SP ...
                    $comissao_extra*= genericas::variavel(56);
                    //A Comissão extra sofreu alterações, sendo assim atualizo a mesma ...
                    $sql = "UPDATE `orcamentos_vendas_itens` SET `comissao_extra` = '$comissao_extra' WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
                    bancos::sql($sql);
                    return $campos_margem[0]['comis_autonomo'];
                }
            }
        }
    }

    function comissao_representante_reais($valor_total, $perc_comissao) {//Função que calcula a comissao do representante em R$
        return ($valor_total * $perc_comissao) / 100;
    }

    //Função q verifica se o orçamento está congelado, caso sim, então o usuário não pode + incluir, alterar ou excluir nenhum item
    function situacao_orcamento($id_orcamento_venda) {
        $sql = "SELECT congelar 
                FROM `orcamentos_vendas` 
                WHERE `id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
        $campos = bancos::sql($sql);
        return $campos[0]['congelar'];
    }

    //Função q verifica se o pedido está liberado, caso sim, então o usuário não pode + incluir, alterar ou excluir nenhum item
    function situacao_pedido($id_pedido_venda) {
        $sql = "SELECT liberado 
                FROM `pedidos_vendas` 
                WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
        $campos = bancos::sql($sql);
        return $campos[0]['liberado'];
    }
    /*Esse 2º parâmetro $alterou_lote_custo_esp é utilizado no alterar Itens do Orçamento 
    ou dentro do próprio Custo ...

    Esse 3º parâmetro $mudou_tipo_cliente foi criado por causa que o Desconto p/ Clientes do Tipo 
    Indústria é diferenciado ...*/
    function calculo_preco_liq_final_item_orc($id_orcamento_venda_item, $alterou_lote_custo_esp = 'N', $mudou_tipo_cliente = 'N') {
        $fator_desc_maximo_venda    = genericas::variavel(19);//Aqui é a Busca da Variável de Vendas
        $impostos_federais          = genericas::variavel(34);
        $valor_dolar_dia            = genericas::moeda_dia('dolar');
        
        $sql = "SELECT c.`id_pais`, c.`id_uf`, c.`id_cliente_tipo`, c.`isento_st`, ov.`id_orcamento_venda`, 
                ov.`id_cliente`, ov.`finalidade`, ov.`nota_sgd`, ov.`desc_icms_sqd_auto`, 
                ovi.`id_produto_acabado`, ovi.`qtde`, ovi.`preco_liq_fat`, ovi.`desc_cliente`, 
                ovi.`desc_extra`, ovi.`acrescimo_extra`, ovi.`desc_sgd_icms`, 
                ovi.`ignorar_lote_minimo_do_grupo_faixa_orcavel`, 
                ovi.`preco_liq_final`, pa.`referencia`, pa.`operacao`, pa.`operacao_custo`, 
                pa.`origem_mercadoria`, pa.`preco_export`, pa.`status_custo`, pa.`status_nao_produzir`, 
                ged.`id_empresa_divisao` 
                FROM `orcamentos_vendas` ov 
                INNER JOIN `clientes` c ON c.id_cliente = ov.id_cliente 
                INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda` = ov.`id_orcamento_venda` 
                INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                WHERE ovi.`id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
        $campos             = bancos::sql($sql);
        $id_orcamento_venda = $campos[0]['id_orcamento_venda'];
        $id_cliente         = $campos[0]['id_cliente'];
        $finalidade         = $campos[0]['finalidade'];
        $nota_sgd           = $campos[0]['nota_sgd'];
        $desc_icms_sqd_auto = $campos[0]['desc_icms_sqd_auto'];
        $id_pais            = $campos[0]['id_pais'];
        $id_uf_cliente      = $campos[0]['id_uf'];
        $id_cliente_tipo    = $campos[0]['id_cliente_tipo'];
        $cliente            = ($id_pais == 31) ? 'N' : 'I';//Nacional ou Internacional ...
        $preco_liq_fat      = $campos[0]['preco_liq_fat'];
        $desc_extra         = $campos[0]['desc_extra'];
        $acrescimo_extra    = $campos[0]['acrescimo_extra'];
        
        $vetor_dados_gerais = self::dados_gerais_orcamento($id_orcamento_venda);
        $data_validade_orc  = $vetor_dados_gerais['data_validade_orc'];
        /********************Busca de Dados de ICMS e Redução para SP / Cliente********************/
        /*******************************Esse cálculos são genéricos********************************/
        /******************************************************************************************/
        /*Dados de ICMS e Redução da Classificação p/ São Paulo que serão utilizados mais abaixo ...

        Observação: Poderia até ter utilizado a função de Impostos aqui p/ São Paulo, mas nesse caso 
        não me serve de nada devido essa função me retornar os valores tratados o que não é a minha 
        necessidade p/ este momento ...*/
        $sql = "SELECT icms.`icms`, icms.`reducao` 
                FROM `produtos_acabados` pa 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
                INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
                INNER JOIN `icms` ON icms.id_classific_fiscal = cf.id_classific_fiscal AND icms.`id_uf` = '1' 
                WHERE pa.`id_produto_acabado` = '".$campos[0]['id_produto_acabado']."' LIMIT 1 ";
        $campos_dados       = bancos::sql($sql);
        $icms_cf_uf_sp      = $campos_dados[0]['icms'];
        $reducao_uf_sp      = $campos_dados[0]['reducao'];
        
        $ICMS_SP            = ($icms_cf_uf_sp) * (100 - $reducao_uf_sp) / 100;
        
        /*Se a Empresa Divisão = NVO / TDC, não podemos dar Desconto de ICMS p/ Venda SGD porque 
        compramos com Nota Fiscal já pagando a ST na Compra e não tendo direito ao Crédito 
        de ICMS ...*/
        if($campos[0]['id_empresa_divisao'] == 9) {
            $desc_sgd       = 0;
        }else {
            $desc_sgd       = (int)(0.57 * $ICMS_SP);//Conforme cartilha 10/2008 do Wilson
        }
        
        /*Se o país do Cliente é 'Estrangeiro' a fórmula p/ cálculo do Desconto de ICMS é diferente porque nós descontaremos
        do Custo o Total de ICMS p/ UF = 'SP' e não apenas 70% ...*/
        if($id_pais != 31) {
            $desc_icms      = $ICMS_SP;
        }else {
            //Isso aqui é uma adaptação, já que não existe id_empresa em Orçamento ...
            $id_empresa_nf  = ($nota_sgd == 'N') ? 1 : 4;
            
            //Dados de ICMS e Reducao da Classificao p/ a UF do Cliente ...
            $dados_produto      = intermodular::dados_impostos_pa($campos[0]['id_produto_acabado'], $id_uf_cliente, $id_cliente, $id_empresa_nf, $finalidade);
            $icms_cf_uf_cliente = $dados_produto['icms_cadastrado'];
            $reducao_uf_cliente = $dados_produto['reducao_cadastrado'];
            $iva_uf_cliente     = $dados_produto['iva'];//Essa variável aqui acima evita de chamar uma função desnecessária ...
            $ICMS_uf_cliente    = ($icms_cf_uf_cliente) * (100 - $reducao_uf_cliente) / 100;
            $desc_icms          = $ICMS_SP - $ICMS_uf_cliente;
        }
        
        /*Igualo a zero se for negativo p/ os casos em que o ICMS de São Paulo for igual a zero e não acrescermos 
        este valor aos Preços ...*/
        //if($desc_icms < 0) $desc_icms = 0;
        /**************************************************************************************/
        /********************************Preço Líquido Faturado********************************/
        /**************************************************************************************/
        /*Somente quando Incluir itens no ORC ou o Orçamento estiver com a sua Data de Validade fora DO Prazo ou quando o user 
        mudar a Qtde do Lote no alterar itens de Orçamento de PA(s) ESP q o sistema vai rodar esse trecho de código pesado ...*/
        if($preco_liq_fat == 0 || ($data_validade_orc < date('Y-m-d')) || ($campos[0]['referencia'] == 'ESP' && $alterou_lote_custo_esp == 'S') || $mudou_tipo_cliente == 'S') {
            $qtde_utilizar = $campos[0]['qtde'];//Essa Qtde veio gravada do Banco de Dados ...
            /******************************************************************************************/
            /**************************Corrige Quantidade do Item do Orçamento*************************/
            /******************************************************************************************/
            if($campos[0]['referencia'] == 'ESP' && $alterou_lote_custo_esp == 'S') {
                //Busco a Qtde de peças / corte porque essa será utilizada na Continha mais abaixo ...
                $sql = "SELECT peca_corte 
                        FROM `produtos_acabados_custos` 
                        WHERE `id_produto_acabado` = '".$campos[0]['id_produto_acabado']."' 
                        AND `operacao_custo` = '".$campos[0]['operacao_custo']."' LIMIT 1 ";
                $campos_custo   = bancos::sql($sql);
                $peca_corte     = (count($campos_custo) == 1) ? $campos_custo[0]['peca_corte'] : 1;//1 P/ não dar erro de Divisão por Zero ...
                if($campos[0]['qtde'] % $peca_corte != 0) $qtde_utilizar = (intval($campos[0]['qtde'] / $peca_corte) + 1) * $peca_corte;
                if($qtde_utilizar != $campos[0]['qtde']) {
                    $sql = "UPDATE `orcamentos_vendas_itens` SET `qtde` = '$qtde_utilizar' WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
                    bancos::sql($sql);
        ?>
                    <Script Language = 'JavaScript'>
                        alert('ALGUM(NS) ORÇAMENTO(S) TIVERAM SUA(S) QTDE(S) ALTERADA(S) !!!\n\n A QTDE NÃO ESTAVA COMPATÍVEL COM A QTDE DE "PÇS/CORTE" QUE TEM DE SER MÚLTIPLA DE <?=$peca_corte;?> !')
                    </Script>
        <?
                }
            }
            /**********************************************************************/
            /*Esse 1º IF foi comentado no dia 03/08/2015 porque à partir dessa data estamos usando 
            essa marcação somente para peças normais também, antes era somente p/ especiais ...*/
            
            /*if($campos[0]['status_nao_produzir'] == 1) {//1-> Não Produzir este material ...
                $preco_liq_fat          = 0;
                $preco_liq_fat_disc     = 'Não Produz';// este é a discriminacao
            }else if($campos[0]['referencia'] != 'ESP' && $id_pais == 31) {//Normais de Linha no Brasil ...*/

            //Busca do Preço Líq Fat ...
            if($campos[0]['referencia'] != 'ESP' && $id_pais == 31) {//Normais de Linha no Brasil ...
                $preco_custo_lista      = custos::lista_preco_vendas($campos[0]['id_produto_acabado'], $id_uf_cliente);
                $preco_liq_fat          = $preco_custo_lista[0];
                $preco_liq_fat_disc     = '';
                /*Normais de Linha fora do Brasil "Estrangeiro" - Neste preço de Lista colocamos o ICMS porque este vai 
                ser descontado automaticamente pelo Sistema p/ calcular o Preço Líq Final ...*/
            }else if($campos[0]['referencia'] != 'ESP' && $id_pais != 31) {
                $preco_liq_fat          = $campos[0]['preco_export'] / (1 - $desc_icms / 100);
                $preco_liq_fat_disc     = '';
            }else {//Especiais ou preço normal p/ exportação ...
                /******************************************************************************************************/
                /* A margem de Lucro Mínima é um preço Mìnimo que nós gostariamos de vender o PA ...
                baseado na Concorrência nós fazemos esse recalculo porque os PA(s) TOp(s) que são 
                os que mais vendem temos de trabalhar com uma ML menor por causa da Concorrência e 
                analogamente os TOp(s) e não TOP(s) e Clientes Atacadistas usamos fatores coerentes com cada situação ...*/
                $valores                = self::calcular_ml_min_pa_vs_cliente($campos[0]['id_produto_acabado'], $id_cliente);
                //Essa será utilizada + abaixo nos cálculos independente da OC do PA ser Industrial ou Revenda ...
                $margem_lucro_minima    = $valores['margem_lucro_minima'];
                /******************************************************************************************************/
                //Verifica se existe Lote Mínimo de Produção em R$ ...
                $sql = "SELECT gpa.`lote_min_producao_reais` 
                        FROM `produtos_acabados` pa 
                        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                        WHERE pa.`id_produto_acabado` = '".$campos[0]['id_produto_acabado']."' LIMIT 1 ";
                $campos_lote_min = bancos::sql($sql);
                $linhas_lote_min = count($campos_lote_min);
                if($linhas_lote_min > 0) {//Sim ...
                    $lote_min_producao_reais = $campos_lote_min[0]['lote_min_producao_reais'];
                }else {//Não ...
                    $lote_min_producao_reais = 0;
                }
                if($campos[0]['status_custo'] == 0) {//0 - Bloqueado para Orçamento
                    $preco_liq_fat      = 0;
                    $preco_liq_fat_disc = 'Orçar';
                }else {//1 - Liberado o custo para o orçamento ...
                    if($campos[0]['operacao_custo'] == 0) {//Indutrial ...
                        $preco_custo = custos::preco_custo_esp_indust($campos[0]['id_produto_acabado'], $qtde_utilizar, $id_pais, $campos[0]['referencia'], '', $campos[0]['ignorar_lote_minimo_do_grupo_faixa_orcavel']);//Chamará a função do ESP ...
                        if($preco_custo[0] == 0 && $preco_custo[1] == 0) {
                            $preco_liq_fat      = 0;
                            $preco_liq_fat_disc = 'DEPTO TÉCNICO';//Este é a discriminação ...
                        }else {
                            /*Esse valor de 1,6 é fixo porque o Preço que vem do Custo, vem com Margem de Lucro de 60% 
                            e multiplicamos pela Margem de Lucro Mínimo do Grupo para que ao dar 20% de Desconto Extra, 
                            o Preço não seja inferior ao Preço da Margem de Lucro Mínima do Grupo ...*/
                            $preco_custo_max    = $preco_custo[0] / $fator_desc_maximo_venda / 1.6 * (1 + $margem_lucro_minima / 100);
                            //Significa que está indo pelo LM e o usuário realmente quer q siga esse caminho caso o Sys caia aqui ...
                            if(($qtde_utilizar * $preco_custo_max) < $lote_min_producao_reais && $campos[0]['ignorar_lote_minimo_do_grupo_faixa_orcavel'] == 'N') {
                                if($campos[0]['referencia'] != 'ESP') {//Normal de Linha ...
                                    $preco_liq_fat = $preco_custo_max;
                                }else {//ESP ...
                                    $preco_liq_fat = ($lote_min_producao_reais / $qtde_utilizar);
                                    $preco_liq_fat_disc = '';
                                }
                            }else {//Caminho Normal ...						
                                $preco_liq_fat      = $preco_custo_max;
                                $preco_liq_fat_disc = '';//aqui é para limpar este campo caso caia nesta condição ...
                            }
                        }
                    }else {//Revenda ...
                        if($campos[0]['referencia'] == 'ESP') {
                            //Verifico se o PA é um PI ...
                            $sql = "SELECT `id_produto_insumo` 
                                    FROM `produtos_acabados` 
                                    WHERE `id_produto_acabado` = '".$campos[0]['id_produto_acabado']."' 
                                    AND `ativo` = '1' LIMIT 1 ";
                            $campos_pipa            = bancos::sql($sql);
                            $id_produto_insumo      = $campos_pipa[0]['id_produto_insumo']; //Em segundo verifico qual é o id_fornecedor_setado, já tenho mesmo o $id_produto_acabado
                            $id_fornecedor_setado   = custos::procurar_fornecedor_default_revenda($campos[0]['id_produto_acabado'], '', 1); //busco somente o id_forncedor default para saber de qual Fornecedor 
                            if($id_fornecedor_setado == 0) {//Se não existir Fornecedor Default para esse P.I. então ñ retorno nada ...
                                $lote_minimo_fornec_default = 0;
                            }else {//Como encontrei um Fornecedor Default p/ o P.I., então eu busco o `lote_minimo_pa_rev` deles
                                $sql = "SELECT lote_minimo_pa_rev 
                                        FROM `fornecedores_x_prod_insumos` 
                                        WHERE `id_fornecedor` = '$id_fornecedor_setado' 
                                        AND `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
                                $campos_lote_min = bancos::sql($sql);
                                //Fiz essa adaptação porque mais abaixo eu só preciso desse valor p/ programação - Dárcio ...
                                $lote_minimo_fornec_default = $campos_lote_min[0]['lote_minimo_pa_rev'];
                            }
                        }
                        if($campos[0]['referencia'] == 'ESP' && ($qtde_utilizar < $lote_minimo_fornec_default || $lote_minimo_fornec_default == 0)) {//Se a qtde for menor q o lote minimo do fornecedor defaul, então aprsentar D. T.
                            $preco_liq_fat      = 0;
                            $preco_liq_fat_disc	= 'DEPTO TÉCNICO';//Este é a discriminacao ...
                        }else {
                            //Colocou o calculo de soma de embalagem para os orçamento 1402 => ESP * PORTA COSSINETE 55x22mm, mas serve para todos e não só para este exemplo ...
                            $preco_liq_fat          = custos::procurar_fornecedor_default_revenda($campos[0]['id_produto_acabado'], 1, '', $cliente) / $fator_desc_maximo_venda / 1.6 * (1 + $margem_lucro_minima / 100);
                            if($campos[0]['referencia'] == 'ESP') {
                                //Significa que está indo pelo LM e o usuário realmente quer q siga esse caminho caso o Sys caia aqui ...
                                if(($qtde_utilizar * $preco_liq_fat) < $lote_min_producao_reais && $campos[0]['ignorar_lote_minimo_do_grupo_faixa_orcavel'] == 'N') $preco_liq_fat = ($lote_min_producao_reais / $qtde_utilizar);
                            }
                            $preco_liq_fat_disc = '';//Aqui é para limpar este campo caso caia nesta condição
                        }
                    }
                }
                /**************************************************************************************/
                /*********************************Desconto e Acréscimo*********************************/
                /**************************************************************************************/
                //Se O País do Cliente é 'Estrangeiro' e o Produto Acabado é 'ESP' ...
                if($id_pais != 31 && $campos[0]['referencia'] == 'ESP') {
                    //Como o Preço que aqui vem do Custo está em R$, este precisa ser transformado em Moeda Estrangeira ...
                    $preco_liq_fat/= $valor_dolar_dia;
                    $preco_liq_fat*= (1 - $impostos_federais / 100);
                    $preco_liq_fat = round($preco_liq_fat, 2);
                }
                /**************************************************************************************/
            }
            $sql = "UPDATE `orcamentos_vendas_itens` SET `preco_liq_fat` = '$preco_liq_fat', `preco_liq_fat_disc` = '$preco_liq_fat_disc', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
            bancos::sql($sql);
            /*****************************************************************************/
            /*****************************Desconto do Cliente*****************************/
            /*****************************************************************************/
            //Aqui pego o id_representante e o Desconto do Cliente para futuros cálculos ...
            $sql = "SELECT `desconto_cliente` 
                    FROM `clientes_vs_representantes` 
                    WHERE `id_cliente` = '$id_cliente' 
                    AND `id_empresa_divisao` = '".$campos[0]['id_empresa_divisao']."' LIMIT 1 ";
            $campos_representante = bancos::sql($sql);
            if(count($campos_representante) > 0) {//Verifica se tem pelo menos um item no orçamento
                /***************Algumas regras para conceder do Desconto do Cliente***************/
                //Se o Cliente é Indústria, na realidade este tem ainda um acréscimo de 12% pois este é o Consumidor Final ...
                if($id_cliente_tipo == 4) {
                    $desconto_cliente = -12;
                }else {//Se for outro Tipo de Cliente então ...
                    //Produtos Especiais não possuem desconto porque saem fora do Catálogo ...
                    $desconto_cliente = ($campos[0]['referencia'] == 'ESP') ? 0 : $campos_representante[0]['desconto_cliente'];
                }
                $sql = "UPDATE `orcamentos_vendas_itens` SET `desc_cliente` = '$desconto_cliente' WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
                bancos::sql($sql);
            }else {
                exit('REPRESENTANTE NÃO ENCONTRADO !!! VERIFIQUE O REPRESENTANTE DESTE CLIENTE PARA ESTA DIVISÃO !');
            }
            /*****************************************************************************/
        }else {
            //Nesse caso só leio esse campo gravado anteriormente do BD, pq não foi feito nenhum recálculo ...
            $desconto_cliente   = $campos[0]['desc_cliente'];
        }
        if($desc_icms_sqd_auto == 0) {//Vendedor optou por não dá este desconto no Cab. do Orc.
            $calculo_sgd_icms = 0;//Então zero este desconto
        }else {//Significa q o vendedor optou por dá o desconto ICMS/SGD ... 
            $calculo_sgd_icms = (strtoupper($nota_sgd) == 'S') ? $desc_sgd : round($desc_icms, 1);
        }
        //Aqui faço todo um cálculo de "$preco_liq_final" pq quando acabo de Incluir um Item não existe nenhum Preço, nem com Desconto, nem com Acréscimo ...
        $preco_liq_final = round(round($preco_liq_fat, 2) * (100 - $desconto_cliente) / 100 * (100 - $desc_extra) / 100 * (100 + $acrescimo_extra) / 100 * (100 - $calculo_sgd_icms) / 100, 2);
        /*****************************************************************/
        /*Devido as novas leis de ST, então eu só terei as Bases de Cálculo 

        1) Se o Cliente realmente quiser que seja tributado o IVA, pq hoje em dia muitos possuem 
        uma "credencial" que isentam o Cliente de pagar esse Imposto ...
        2) NFs de Revenda c/ negociação do Tipo NF, pois não temos as Empresas Alba ou Tool ...
        3) Quando possuir iva em São Paulo + somente com o PA de Op. Fat = 'Ind' ou Op. 'Rev' desde que sejam nas Origens de Mercadoria 1, 3, 5, 6 e 8 ...
        4) Quando possuir iva em qualquer outro Estado não importa a OF do PA ...*/
        //if($isento_st == 'N' && $nota_sgd == 'N' && $finalidade == 'R' && ($campos[0]['operacao'] == 0 && $id_uf_cliente == 1 || $id_uf_cliente > 1)) {
        /*if($isento_st == 'N' && $nota_sgd == 'N' && $finalidade == 'R' && ($id_uf_cliente == 1 && ($campos[0]['operacao'] == 0 || $campos[0]['operacao'] == 1 && $campos[0]['origem_mercadoria'] == 1 || $campos[0]['origem_mercadoria'] == 3 || $campos[0]['origem_mercadoria'] == 5 || $campos[0]['origem_mercadoria'] == 6 || $campos[0]['origem_mercadoria'] == 8) || $id_uf_cliente > 1)) {
            $iva = $iva_uf_cliente;
        }else {//Não existe p/ outras condições ...
            $iva    = 0;
        }*/
        /*****************************************************************/
        $sql = "UPDATE `orcamentos_vendas_itens` SET `desc_sgd_icms` = '$calculo_sgd_icms', `preco_liq_final` = '$preco_liq_final', `iva` = '$iva_uf_cliente', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
        bancos::sql($sql);
        /*Caso o Cliente for externo existirá divisão pelo Dólar Exportação, mas mesmo o País sendo do Brasil igualo o Dólar 
        a 1 para facilitar o calculo do orçamento dai nao preciso buscar o id_pais do cliente nos relatórios ...*/
        if($id_pais == 31) $valor_dolar_dia = 1;
        $sql = "UPDATE `orcamentos_vendas` SET `valor_dolar` = '$valor_dolar_dia' WHERE `id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
        bancos::sql($sql);
        self::atualizar_orcamento_vendas($id_orcamento_venda);
    }

    /*Esse 3º parâmetro $trazer_comissao só será = 'S' quando o usuário estiver dentro da tela de alterar item 
    de Orçamento recalculando a Comissão quando estiver mudando Preço ou Acréscimo ou Desconto ...*/
    function calculo_ml_comissao_item_orc($id_orcamento_venda, $id_orcamento_venda_item = 0, $trazer_comissao = 'N') {//Só passo $id_orcamento_venda_item quando é para atualizar somente um item ...
        $fator_desc_maximo_venda	= genericas::variavel(19);//Aqui é a Busca da Variável de Vendas
        $outros_impostos_federais 	= genericas::variavel(34);
/***********************************Parte que calcula e exibe os Itens***********************************/
        //Pega somente um item, especificado pelo parametro $id_orcamento_venda_item
        if($id_orcamento_venda_item > 0) $condicao = " AND ovi.`id_orcamento_venda_item` = '$id_orcamento_venda_item' ";

        $sql = "SELECT c.id_uf, ov.nota_sgd 
                FROM `orcamentos_vendas` ov 
                INNER JOIN `clientes` c ON c.id_cliente = ov.id_cliente 
                WHERE ov.id_orcamento_venda = '$id_orcamento_venda' LIMIT 1 ";
        $campos             = bancos::sql($sql);
        $id_cliente         = $campos[0]['id_cliente'];
        $nota_sgd           = $campos[0]['nota_sgd'];
        $id_uf_cliente      = $campos[0]['id_uf'];

        $sql = "SELECT gpa.lote_min_producao_reais, ovi.id_orcamento_venda_item, ovi.id_produto_acabado, ovi.qtde, 
                ovi.desc_extra, ovi.acrescimo_extra, ovi.preco_liq_final, pa.operacao_custo 
                FROM `orcamentos_vendas_itens` ovi 
                INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                WHERE ovi.id_orcamento_venda = '$id_orcamento_venda' $condicao ORDER BY ovi.id_orcamento_venda_item ";
        $campos_itens   = bancos::sql($sql);
        $linhas_itens   = count($campos_itens);
        if($linhas_itens > 0) {//Verifica se tem pelo menos um item no orçamento
            for($i = 0; $i < $linhas_itens; $i++) {
                $lote_min_producao_reais    = $campos_itens[$i]['lote_min_producao_reais'];
                /*Aqui eu pego essa variável no loop, porque teremos alguns casos em que a
                mesma não virá por parametro e eu preciso dessa + abaixo de qualquer jeito, 
                p/ entrar como parâmetro na função de calcular a comissão ... faço esse 
                controle para garantir que não teremos erro ...*/
                $id_orcamento_venda_item    = $campos_itens[$i]['id_orcamento_venda_item'];
                $desc_cliente               = $campos_itens[$i]['desc_cliente'];
                $desc_extra                 = $campos_itens[$i]['desc_extra'];
                $preco_liq_final            = $campos_itens[$i]['preco_liq_final'];
                
                /*Infelizmente esse é o único trecho de código que está em redundância na biblioteca => 
                "$preco_liq_final_orcamento_item", ambas bibliotecas utilizam essa mesma idéia ...*/
                /********************Busca de Dados de ICMS e Redução para SP / Cliente********************/
                /*******************************Esse cálculos são genéricos********************************/
                /******************************************************************************************/
                //Dados de ICMS e Reducao da Classificao p/ São Paulo ...
                $dados_produto          = intermodular::dados_impostos_pa($campos_itens[$i]['id_produto_acabado'], 1);
                $icms_cf_uf_sp          = $dados_produto['icms'];
                $reducao_uf_sp          = $dados_produto['reducao'];
                
                //Dados de ICMS e Reducao da Classificao p/ a UF do Cliente ...
                $dados_produto      = intermodular::dados_impostos_pa($campos_itens[$i]['id_produto_acabado'], $id_uf_cliente);
                $icms_cf_uf_cliente = $dados_produto['icms'];
                $reducao_uf_cliente = $dados_produto['reducao'];
                
                $ICMS_SP 	= ($icms_cf_uf_sp) * (100 - $reducao_uf_sp) / 100;
                $desc_icms	= $ICMS_SP - ($icms_cf_uf_cliente) * (100 - $reducao_uf_cliente) / 100;
                /******************************************************************************************/
                $tx_financeira = custos::calculo_taxa_financeira($id_orcamento_venda);
                /*Esse campo comissao_perc, representa a Comissão Velha que já não é mais necessário, 
                pois guardamos a comissão nova desde Janeiro de 2012 ...*/
                $comissao_new 	= vendas::nova_comissao_representante($id_orcamento_venda_item, $preco_liq_final);
                /*************************Margem de Lucro dos Itens do Orçamento***************************/
                //Na realidade aqui é um recálculo "2ª Vez", pq já calculamos a 1ª Vez da ML quando calculamos a Comissão ...
                $vetor_margem   = custos::margem_lucro($id_orcamento_venda_item, $tx_financeira, $id_uf_cliente, $preco_liq_final);
                $margem_lucro   = str_replace(',', '.', str_replace('.', '',str_replace('%', '', $vetor_margem[1])));
                /*****************************Calculo do Lote Minimo Corrigido*****************************/
                $taxa_financeira_vendas = genericas::variavel(16) / 100 + 1;
                $total_indust           = custos::todas_etapas($campos_itens[$i]['id_produto_acabado'], $campos_itens[$i]['operacao_custo'], 1, $campos_itens[$i]['qtde']);
                $preco_custo_max        = $total_indust * $taxa_financeira_vendas / $fator_desc_maximo_venda;
                if($preco_custo_max > 0) {
                    $lote_minimo_corrigido = $lote_min_producao_reais / $preco_custo_max;
                    if(strtoupper($nota_sgd) == 'S') {//Se o Tipo de Orçamento = 'SGD' ...
                        $lote_minimo_corrigido*= (1 - $ICMS_SP / 100) * (1 - $outros_impostos_federais / 100);
                    }else {//Significa que é com NF ...
                        //Se a UF do Cliente for Diferente de Sampa ...
                        if($id_uf_cliente != 1) $lote_minimo_corrigido*= (1 - $desc_icms / 100);
                    }
                    //Adaptar o Lote Mínimo do Grupo pra forma de Venda (SGD, UF <> 'SP', Prazo Pgto vs Tx Financ), substitui o Lote do Grupo em R$ ...
                    $lote_minimo_corrigido = round($lote_minimo_corrigido, 2);
                }else {
                    $lote_minimo_corrigido = 0;
                }
                $sql = "UPDATE `orcamentos_vendas_itens` SET `lote_minimo_corrigido` = '$lote_minimo_corrigido', `margem_lucro` = '$margem_lucro', `comissao_new` = '".$comissao_new."', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_orcamento_venda_item` = '".$campos_itens[$i]['id_orcamento_venda_item']."' LIMIT 1 ";
                bancos::sql($sql);
            }
        }
        if($trazer_comissao == 'S') return $comissao_new;
    }

    /*A idéia dessa fórmula "AlT-C" que está abaixo, serve p/ definirmos o Preço Líq. Final e p/ isso calculamos 
    o Desc/Acrésc. Extra ...*/
    function alt_c($preco_liq_fat, $desconto_cliente, $preco_liq_final) {
        //A idéia dessa fórmula "AlT-C" que está abaixo, serve p/ definirmos o Preço Líq. Final e p/ isso calculamos o Desc/Acrésc. Extra ...
        $desc_promocional = (1 - $preco_liq_final / $preco_liq_fat) * 100;
        //Parte 2 da Fórmula
        $desconto_extra   = (1 - (1 - $desc_promocional / 100) / (1 - $desconto_cliente / 100)) * 100;
        $desconto_extra   = round($desconto_extra, 2);
        if($desconto_extra < 0) {
            $acrescimo_extra    = abs($desconto_extra);
            $desconto_extra     = 0;
        }else {
            $acrescimo_extra    = 0;
        }
        return array('acrescimo_extra'=> $acrescimo_extra, 'desconto_extra'=> $desconto_extra);
    }

/*Função que serve p/ verificar se o Cliente que está cadastrado ou foi cadastrado recente, 
possui as 3 transportadoras Padrões 795 - N/Carro (Baldez), 796 - Retira, 1098 - N/Carro ...*/
    function transportadoras_padroes($id_cliente) {
//Aqui verifica se o cliente tem a transportadora "N/Carro (Baldez)" cadastrada
            $sql = "Select id_cliente_transportadora 
                            from clientes_vs_transportadoras 
                            where id_cliente = '$id_cliente' 
                            and id_transportadora = 795 limit 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 0) {//Não achou, então cadastra
                    $sql = "Insert into clientes_vs_transportadoras (`id_cliente_transportadora`, `id_cliente`, `id_transportadora`) values ('', '$id_cliente', '795')";
                    bancos::sql($sql);
            }
//Aqui verifica se o cliente tem a transportadora "Retira" cadastrada
            $sql = "Select id_cliente_transportadora 
                            from clientes_vs_transportadoras 
                            where id_cliente = '$id_cliente' 
                            and id_transportadora = '796' limit 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 0) {//Não achou, então cadastra
                    $sql = "Insert into clientes_vs_transportadoras (`id_cliente_transportadora`, `id_cliente`, `id_transportadora`) values ('', '$id_cliente', '796')";
                    bancos::sql($sql);
            }
//Aqui verifica se o cliente tem a transportadora "N/Carro (Vendedor)" cadastrada
            $sql = "Select id_cliente_transportadora 
                            from clientes_vs_transportadoras 
                            where id_cliente = '$id_cliente' 
                            and id_transportadora = '1098' limit 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 0) {//Não achou, então cadastra
                    $sql = "Insert into clientes_vs_transportadoras (`id_cliente_transportadora`, `id_cliente`, `id_transportadora`) values ('', '$id_cliente', '1098')";
                    bancos::sql($sql);
            }
    }

    function pas_precos_export($id_produto_acabado) {
        if(!class_exists('custos'))         require 'custos.php';
        if(!class_exists('genericas'))      require 'genericas.php';
        if(!class_exists('intermodular'))   require 'intermodular.php';
        
        //Busca do Dólar Atual - o último dólar ...
        $dolar_dia              = genericas::moeda_dia('dolar');
        $fator_desc_max_vendas  = genericas::variavel(19);
        $impostos_federais      = genericas::variavel(34);
        
        //Mesmo que seja um Cliente de outro País, eu busco os Impostos do Estado de São Paulo que onde nós estamos ...
        $dados_produto          = intermodular::dados_impostos_pa($id_produto_acabado, 1);

        //Busca do Dólar Atual - o último dólar ...
        $sql = "SELECT valor_dolar_dia 
                FROM `cambios` 
                ORDER BY `id_cambio` DESC LIMIT 1 ";
        $campos_dolar = bancos::sql($sql);

        $sql = "SELECT ged.margem_lucro_exp, icms.icms, icms.reducao 
                FROM `produtos_acabados` pa 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
                INNER JOIN `icms` ON icms.`id_classific_fiscal` = f.`id_classific_fiscal` 
                AND icms.`id_uf` = '1' /*Busca do Estado de SP*/ 
                WHERE pa.`id_produto_acabado` = '$id_produto_acabado' 
                AND pa.`ativo` = '1' LIMIT 1 ";
        $campos                     = bancos::sql($sql);
        
        //Traz o Preço do Custo ...
        $preco_venda_fat_orc_min_rs = custos::preco_custo_pa($id_produto_acabado);

        $preco_maximo_custo_fat_rs = $preco_venda_fat_orc_min_rs / $fator_desc_max_vendas;
        $preco_maximo_custo_fat_us = $preco_maximo_custo_fat_rs * (100 - $dados_produto['icms'] * (100 - $dados_produto['reducao']) / 100) / 100 * (1 - $impostos_federais / 100) / $dolar_dia;

        /*Divido esse valor por 2 porque este $preco_maximo_custo_fat_us está com ML de 100% ou seja ML 60% que vem do 
        Custo / 0,8 que é o Fator Desconto Máximo de Vendas ...*/
        $margem_lucro_zero  = $preco_maximo_custo_fat_us / 2;
        $preco_liq_fat_us   = $margem_lucro_zero * (1 + $dados_produto['margem_lucro_exp'] / 100);
        $preco_bruto_fat_us = $preco_liq_fat_us / $fator_desc_max_vendas;
        return array('preco_maximo_custo_fat_us' => $preco_maximo_custo_fat_us, 'margem_lucro_zero' => $margem_lucro_zero, 'preco_liq_fat_us' => $preco_liq_fat_us, 'preco_bruto_fat_us' => $preco_bruto_fat_us);
    }
    /*********************************Controle de Lotes Orçáveis*****************************************/
    function buscar_qtde($id_produto_acabado) {
        //Busca de alguns dados do PA para saber qual Qtde que deve ser sugerida para o usuário incluir ...
        $sql = "SELECT referencia, discriminacao, operacao_custo 
                FROM `produtos_acabados` pa 
                WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if($campos[0]['referencia'] == 'ESP') {//Caminho do ESP ...
            if($campos[0]['operacao_custo'] == 0) {//Operação de Custo => Industrial
                $sql = "SELECT qtde_lote 
                        FROM `produtos_acabados_custos` 
                        WHERE `id_produto_acabado` = '$id_produto_acabado' 
                        AND `operacao_custo` = ".$campos[0]['operacao_custo']." LIMIT 1 ";
                $campos_qtde    = bancos::sql($sql);
                $qtde           = $campos_qtde[0]['qtde_lote'];
                if($qtde == '' || $qtde == 0) {
                    $qtde = 1;
                    $title = 'Digite a Quantidade';
                }else {
                    $qtde_minima = $qtde / genericas::variavel(60);
                    $qtde_maxima = $qtde * genericas::variavel(60);
                    $title = 'Lotes Or&ccedil;&aacute;veis de '.segurancas::number_format($qtde_minima, 2, '.').' &agrave; '.segurancas::number_format($qtde_maxima, 2, '.');
                }
            }else {//Operação de Custo => Revenda
//O primeiro passo a fazer é verificar qual o Fornecedor Default do PA Revenda ...
                $id_fornecedor_setado = custos::procurar_fornecedor_default_revenda($id_produto_acabado, '', '', 1);
//O segundo passo a fazer é verificar qual o PI do PA ...
                $sql = "SELECT id_produto_insumo 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` = '$id_produto_acabado' 
                        AND `ativo` = '1' LIMIT 1 ";
                $campos_pi          = bancos::sql($sql);
                $id_produto_insumo  = $campos_pi[0]['id_produto_insumo'];
//O terceiro passo a fazer é verificar qual o PI do PA ...
                $sql = "SELECT lote_minimo_pa_rev 
                        FROM `fornecedores_x_prod_insumos` 
                        WHERE `id_fornecedor`= '$id_fornecedor_setado' 
                        AND `id_produto_insumo`= '$id_produto_insumo' LIMIT 1 ";
                $campos_lote_min = bancos::sql($sql);
                $qtde 	= $campos_lote_min[0]['lote_minimo_pa_rev'];
                $title 	= 'Lote M&iacute;nimo';
            }
        }else {//PA(s) normais de Linha ...
            $pegar_pecas_por_emb = 'S';
            if(strripos($campos[0]['referencia'], 'PBS-') !== false) {//Existe PBS na Referência do PA ...
                $qtde   = (int)genericas::variavel(64);//É um PA que nós fizemos com Custo Baixo que tem de ser vendido em grande Qtde p/ Compensar ...
                /*O Roberto pediu p/ colocarmos junto desse Critério de Discriminação o id_familia, mas como não
                trago no momento, vamos aguardar p/ ver se na prática precisa mesmo ...*/
            }else if(strripos($campos[0]['discriminacao'], 'PRIMEIRO') !== false || strripos($campos[0]['discriminacao'], 'SEGUNDO') !== false || strripos($campos[0]['discriminacao'], 'TERCEIRO') !== false) {
                $qtde   = (int)genericas::variavel(65);//Mínimo de 10 peças porque o Fornecedor já não quer mais Produzir em Quantidades Pequenas ...
            }else {
                //Sugere p/ o usuário a Qtde de Peças por Embalagem nos PA(s) normais de Linha ...
                $sql = "SELECT pecas_por_emb 
                        FROM `pas_vs_pis_embs` 
                        WHERE `id_produto_acabado` = '$id_produto_acabado' 
                        AND `embalagem_default` = '1' LIMIT 1 ";
                $campos_pecas_emb   = bancos::sql($sql);
                $qtde               = (count($campos_pecas_emb) == 1) ? intval($campos_pecas_emb[0]['pecas_por_emb']) : 1;
            }
            $title  = 'Digite a Quantidade';
        }
        return array('qtde'=>$qtde, 'title'=>$title);
    }

    function vendedores_pendencias($id_orcamento_venda = 0, $id_pedido_venda = 0) {
        $data_sys = date('Y-m-d H:i:s');
        session_start('funcionarios');
        if(!class_exists('intermodular')) require 'variaveis/intermodular.php';//Caso exista eu desvio a Classe ...
        if(empty($pme_array)) $pme_array = array('0');

        if(in_array($_SESSION['id_funcionario'], $pme_array)) {
            $id_rep_logado = 1;//Equivale ao Representante Direto ...
        }else {
            $id_funcionario_sessao = $_SESSION['id_funcionario'];
            $sql = "SELECT `id_representante` 
                    FROM `representantes_vs_funcionarios` 
                    WHERE `id_funcionario` = '$id_funcionario_sessao' LIMIT 1 ";
            $campos_rep = bancos::sql($sql);
            $id_rep_logado = (count($campos_rep) > 0) ? $campos_rep[0]['id_representante'] : 0;
        }
        if($id_orcamento_venda > 0) {//Orçamentos ...
            /********************Somente com a Data a partir de 26/09/2009********************/
            /*Aqui eu busco os Representantes que estão nos Itens dos Orçamentos 
            que não estão Congelados ...*/
            $sql = "SELECT DISTINCT(ovi.id_representante) 
                    FROM `orcamentos_vendas_itens` ovi 
                    INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` AND ov.`congelar` = 'N' AND ov.`data_emissao` >= '2009-09-26' 
                    WHERE ovi.`id_orcamento_venda` = '$id_orcamento_venda' ";
            $campos = bancos::sql($sql);
        }else {//Pedidos ...
            $sql = "SELECT DISTINCT(pvi.id_representante) 
                    FROM `pedidos_vendas_itens` pvi 
                    INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`liberado` = '0' AND pv.`data_emissao` >= '2009-09-26' 
                    WHERE pvi.`id_pedido_venda` = '$id_pedido_venda' ";
            $campos = bancos::sql($sql);
        }
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
            //Verifico se o Representante do Orc ou Ped é um Funcionário ...
            $sql = "SELECT id_funcionario 
                    FROM `representantes_vs_funcionarios` 
                    WHERE `id_representante` = '".$campos[$i]['id_representante']."' LIMIT 1 ";
            $campos_rep_func = bancos::sql($sql);
            if(count($campos_rep_func) == 1) {//Significa que o Representante é um Funcionário ...
                $id_rep_destino = $campos[$i]['id_representante'];
            }else {//Se não eu busco o Representante Superior do Funcionário ...
                $sql = "SELECT id_representante_supervisor 
                        FROM `representantes_vs_supervisores` 
                        WHERE `id_representante` = '".$campos[$i]['id_representante']."' LIMIT 1 ";
                $campos_rep_func    = bancos::sql($sql);
                if(count($campos_rep_func) == 1) {//Se existir Superior então ...
                    $id_rep_destino = $campos_rep_func[0]['id_representante_supervisor'];
                }else {//Senão existe Superior, isso significa que esse Representante seja o "Direto" então ...
                    $id_rep_destino = $campos[$i]['id_representante'];
                }
            }
            /*Se o Funcionário "Representante" Atual que está logado no Sistema for diferente do(s) 
            Representante(s) dos Itens do Orçamento então eu gero um registro de Pendência ...*/
            if($id_rep_logado <> $id_rep_destino) {
                if($id_orcamento_venda > 0) {//Aqui eu verifico se já não foi gerado anteriormente algum registro p/ esse Representante ...
                    $sql = "SELECT id_vendedor_pendencia 
                            FROM `vendedores_pendencias` 
                            WHERE `id_orcamento_venda` = '$id_orcamento_venda' 
                            AND `id_representante` = '$id_rep_destino' limit 1 ";
                    $campos_registro = bancos::sql($sql);
                    if(count($campos_registro) == 0) {
                        if(!empty($_SESSION['id_funcionario'])) {//99% dos casos, serão os funcionários da Albafer que irão acessar nosso sistema ...
                            $sql = "INSERT INTO `vendedores_pendencias` (`id_vendedor_pendencia`, `id_orcamento_venda`, `id_representante`, `id_funcionario_originador`, `data_sys`) 
                                    VALUES (NULL, '$id_orcamento_venda', '$id_rep_destino', '$_SESSION[id_funcionario]', '$data_sys') ";
                            bancos::sql($sql);
                        }
                    }
                }else {//Aqui eu verifico se já não foi gerado anteriormente algum registro p/ esse Representante ...
                    $sql = "SELECT id_vendedor_pendencia 
                            FROM `vendedores_pendencias` 
                            WHERE `id_pedido_venda` = '$id_pedido_venda' 
                            AND `id_representante` = '$id_rep_destino' LIMIT 1 ";
                    $campos_registro = bancos::sql($sql);
                    if(count($campos_registro) == 0) {
                        $sql = "INSERT INTO `vendedores_pendencias` (`id_vendedor_pendencia`, `id_pedido_venda`, `id_representante`, `id_funcionario_originador`, `data_sys`) 
                                VALUES (NULL, '$id_pedido_venda', '$id_rep_destino', '$_SESSION[id_funcionario]', '$data_sys') ";
                        bancos::sql($sql);
                    }
                }
            }
        }
    }

    function valor_pendencia($id_pedido_venda) {
        //Verifico tudo o que está em Pendência do Pedido passador por parâmetro ...
        $sql = "SELECT SUM((qtde - qtde_faturada) * preco_liq_final) AS valor_pendencia 
                FROM `pedidos_vendas_itens` 
                WHERE `id_pedido_venda` = '$id_pedido_venda' ";
        $campos_valor_pendencia = bancos::sql($sql);
        //Atualiza o Pedido passador por parâmetro com o Valor de Pendência ...
        $sql = "UPDATE `pedidos_vendas` SET valor_pendencia = '".$campos_valor_pendencia[0]['valor_pendencia']."' WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
        bancos::sql($sql);
    }
        
    //Função que retorna o Preço de Queima de um PA baseado na forma de negociação do Orçamento ...
    function calcular_preco_de_queima_pa($id_produto_acabado, $id_orcamento_venda = 0) {
        if($id_orcamento_venda == 0) {//Quando nao temos orcamento, temos entao alguns dados padroes ...
            $prazo_medio    = 60;
            $id_pais        = 31;//Brasil ...
            $id_uf          = 1;//Sao Paulo ...
            $nota_sgd       = 'N';//Nota Fiscal ...
        }else {//Busco alguns dados do Orçamento ...
            $sql = "SELECT c.id_cliente, c.id_pais, c.id_uf, ov.nota_sgd, ov.prazo_a, ov.prazo_b, ov.prazo_c, ov.prazo_d  
                    FROM `orcamentos_vendas` ov 
                    INNER JOIN `clientes` c ON c.id_cliente = ov.id_cliente 
                    WHERE ov.`id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
            $campos     = bancos::sql($sql);
            $id_cliente = $campos[0]['id_cliente'];
            $id_pais    = $campos[0]['id_pais'];
            $id_uf      = $campos[0]['id_uf'];
            $nota_sgd   = $campos[0]['nota_sgd'];
            /*****************************************************************************/
            //O tipo_moeda p/ parte de cálculo do Orc ...
            if($id_pais != 31) {//Significa que o Cliente é do Tipo Internacional
                $dolar_dia = genericas::moeda_dia('dolar');
            }else {//Significa que o Cliente é do Tipo Nacional
                $dolar_dia = 1;
            }
            /*****************************************************************************/
            $prazo_medio = intermodular::prazo_medio($campos[0]['prazo_a'], $campos[0]['prazo_b'], $campos[0]['prazo_c'], $campos[0]['prazo_d']);
        }
        //Dados da UF do Cliente ...
        $dados_produto              = intermodular::dados_impostos_pa($id_produto_acabado, $id_uf);
        $classific_fiscal           = $dados_produto['classific_fiscal'];
        $icms                       = $dados_produto['icms'];
        $icms_intraestadual         = $dados_produto['icms_intraestadual'];
        $reducao                    = $dados_produto['reducao'];

        //Dados da UF de São Paulo ...
        $dados_produto              = intermodular::dados_impostos_pa($id_produto_acabado, 1);
        $classific_fiscal_sp        = $dados_produto['classific_fiscal'];
        $icms_sp                    = $dados_produto['icms'];
        $icms_intraestadual_sp      = $dados_produto['icms_intraestadual'];
        $reducao_sp                 = $dados_produto['reducao'];

        //Busco alguns dados do PA que serão utilizados mais abaixo ...
        $sql = "SELECT gpa.id_grupo_pa, gpa.id_familia, pa.referencia 
                FROM `produtos_acabados` pa 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                WHERE pa.`id_produto_acabado` = '$id_produto_acabado' 
                AND pa.`ativo` = '1' LIMIT 1 ";
        $campos             = bancos::sql($sql);
        $id_grupo_pa        = $campos[0]['id_grupo_pa'];
        $id_familia         = $campos[0]['id_familia'];
        $referencia         = $campos[0]['referencia'];
        //Aqui eu verifico o Estoque Comprometido desse ...
        $estoque_produto 	= estoque_acabado::qtde_estoque($id_produto_acabado);
        $est_comprometido 	= $estoque_produto[8];
        /************************************************************************************/
        /******************************Controle de Grupos PA(s)******************************/
        /************************************************************************************/
        //Lima Agulha WS - pode ser vendida avulsa, mas normalmente é utilizada p/ montar jogos ...
        //Lima Agulha Diamantada - pode ser vendida avulsa, mas normalmente é utilizada p/ montar jogos ...
        //Cabo de Lima, não calculo a Queima a função é muy pesada ...
        //Referências começadas por Si-4 ñ podem pq são Bits Sinterizados q temos produzidos bem acima da média p/ forçar venda ...
        if($id_grupo_pa == 11 || $id_grupo_pa == 78 || $id_grupo_pa == 81 || strpos($referencia, 'SI-4') !== false) {
            $estoque_queima = 0;
            $ec_tot		= $est_comprometido;
        }else {
            $valores        = intermodular::calculo_estoque_queima_pas_atrelados($id_produto_acabado);
            $estoque_queima = $valores['total_eq_pas_atrelados'];
            //Se for componente, não existe queima ...
            if($id_familia == 23 || $id_familia == 24) $estoque_queima = 0;
            $ec_tot         = $valores['total_ec_pas_atrelados'];
        }
        /************************************************************************************/
        $preco_custo                            = custos::preco_custo_pa($id_produto_acabado);
        $qtde_meses_estoque                     = round($ec_tot / $valores['total_mmv_pas_atrelados'], 1);
        $taxa_financeira_de_queima              = 2;
        $fator_taxa_financeira_queima_30ddl     = $taxa_financeira_de_queima / 100 + 1;
        /*Se o Fator Taxa de Queima > 1.3 significa que teríamos de dar mais que 23% de desconto sobre 
        o Preço Ideal de Queima, por isso limitamos este fator a 1.3 p/ que o Desconto Máximo seja de 23% ...*/
        $fator_taxa_financeira_de_queima        = 1.3;

        if(pow($fator_taxa_financeira_queima_30ddl, $qtde_meses_estoque / 2) <= $fator_taxa_financeira_de_queima) {
            $fator_taxa_financeira_de_queima 	= round(pow($fator_taxa_financeira_queima_30ddl, $qtde_meses_estoque / 2), 2);
        }
        //Estamos tirando 1 dessa Taxa p/ sermos mais coerentes c/ a Taxa Financeira que pagamos ao Banco ...
        $taxa_financeira_vendas     = genericas::variavel(16);
        $preco_custo_av_ml_zero_nf  = ($preco_custo / genericas::variavel(22)) / (1 + $taxa_financeira_vendas / 100);

        $valores                = self::calcular_ml_min_pa_vs_cliente($id_produto_acabado, $id_cliente);
        $margem_lucro_minima    = $valores['margem_lucro_minima'];
        //P/ fazermos um incentivo na Venda pela parte de Queima, faremos uma MLMIn 10% abaixo da ML Min normal ...
        $margem_lucro_minima*= 0.9;
        /*Estamos limitando a Margem de Lucro Mínima em 35% por Conta dos Impostos Federais que agora estão 
        inclusos no Custo para quando fizermos o cálculo do Preço de Queima 30 dias NF ...*/
        $margem_lucro_minima_queima = 35;

        if($margem_lucro_minima < $margem_lucro_minima_queima) $margem_lucro_minima = $margem_lucro_minima_queima;
        $preco_de_queima_av_nf  = $preco_custo_av_ml_zero_nf * (1 + $margem_lucro_minima / 100) / $fator_taxa_financeira_de_queima;

        $fator_taxa_financeira_diaria   = pow(1 + $taxa_financeira_vendas / 100, 1 / 30);
        $fator_taxa_financeira_orc_pz_medio = round(pow($fator_taxa_financeira_diaria, $prazo_medio), 4);

        $fator_correcao_icms_sgd 	= (1 - $icms_sp * (1 - $reducao_sp / 100) / 100);
        $fator_correcao_icms_nf 	= $fator_correcao_icms_sgd / (1 - $icms * (1 - $reducao / 100) / 100);
        //Só se for com Nota Fiscal e do Brasil é que utiliza o Fator de Correção do ICMS NF ...
        $fator_correcao_icms_orc 	= ($nota_sgd == 'N' && $id_pais == 31) ? $fator_correcao_icms_nf : $fator_correcao_icms_sgd;
        /*Este Preço é o Preço de Queima AV NF acrescido
        da Taxa Financeira do Prazo Médio e da Diferença de
        ICMS (SGD / NF) ...*/
        $preco_venda_para_queima_cond_orc = round($preco_de_queima_av_nf * $fator_taxa_financeira_orc_pz_medio * $fator_correcao_icms_orc, 2);
        /*Se for País Estrangeiro, então divido esse valor pelo Dólar do Dia p/ 
        transformar esse valor de Queima em Dólar ...*/
        if($id_pais <> 31) {
            $preco_venda_para_queima_cond_orc/= $dolar_dia;
            $preco_venda_para_queima_cond_orc = round($preco_venda_para_queima_cond_orc, 2);
        }
        return $preco_venda_para_queima_cond_orc;
    }
    
    function dados_gerais_orcamento($id_orcamento_venda) {
        if(!class_exists('data'))   require 'data.php';//Caso exista eu desvio a classe
        $dias_validade = (int)genericas::variavel(38);//Prazo Inicial que Hoje está em 10 dias ...

        $sql = "SELECT DATE_FORMAT(`data_emissao`, '%d/%m/%Y') AS data_emissao 
                FROM `orcamentos_vendas` 
                WHERE `id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
        $campos         = bancos::sql($sql);
        $data_emissao   = $campos[0]['data_emissao'];
        $congelar       = $campos[0]['congelar'];

        //Aqui eu verifico se existe algum Item que está em Queima de Estoque ...
        $sql = "SELECT COUNT(id_orcamento_venda_item) AS qtde_item_queima_estoque 
                FROM `orcamentos_vendas_itens` 
                WHERE `id_orcamento_venda` = '$id_orcamento_venda' 
                AND `queima_estoque` = 'S' ";
        $campos_queima_estoque  = bancos::sql($sql);
        $possui_queima_estoque  = ($campos_queima_estoque[0]['qtde_item_queima_estoque'] > 0) ? 'S' : 'N';
        
        //Se existir algum Item que está em Queima de Estoque muda-se o Prazo p/ menor que Hoje está em 3 dias ...
        if($possui_queima_estoque == 'S') $dias_validade = genericas::variavel(48);
        
        /*Aqui eu verifico se existe algum Item que está em Promoção ...
        Observação => campo Promoção = S, significa "Sem Promoção", N significa "Não Tem" ...*/
        $sql = "SELECT COUNT(id_orcamento_venda_item) AS qtde_item_promocao 
                FROM `orcamentos_vendas_itens` 
                WHERE `id_orcamento_venda` = '$id_orcamento_venda' 
                AND `promocao` NOT IN ('S', 'N') ";
        $campos_promocao    = bancos::sql($sql);
        $possui_promocao    = ($campos_promocao[0]['qtde_item_promocao'] > 0) ? 'S' : 'N';
        
        $data_validade_orc = data::datatodate(data::adicionar_data_hora($data_emissao, $dias_validade), '-');
        return array('data_validade_orc' => $data_validade_orc, 'dias_validade' => intval($dias_validade), 'possui_queima_estoque' => $possui_queima_estoque, 'possui_promocao' => $possui_promocao);
    }
    
    function verificar_orcamento_item_fora_custo($id_orcamento_venda_item) {
        $sql = "SELECT ov.`id_orcamento_venda`, ov.`id_cliente`, ovi.`id_produto_acabado`, ovi.`queima_estoque`, 
                ovi.`desc_extra`, ovi.`preco_liq_final`, ovi.`margem_lucro`, ovi.`margem_lucro_estimada` 
                FROM `orcamentos_vendas_itens` ovi 
                INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
                WHERE ovi.`id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
        $campos                 = bancos::sql($sql);
        $valores                = self::calcular_ml_min_pa_vs_cliente($campos[0]['id_produto_acabado'], $campos[0]['id_cliente']);
        $margem_lucro_minima    = $valores['margem_lucro_minima'];
        //Se o item estiver em Queima "Excesso" de Estoque nunca estará fora do Custo, mesmo com a ML estando baixa ...
        if($campos[0]['queima_estoque'] == 'S') {
            $item_fora_custo = 'N';
        }else {
            /*Por conta da Crise estamos ignorando esse caminho desde 18/06/2015 ...
            $vetor_valores      = self::preco_minimo_venda($id_orcamento_venda_item);
            $preco_minimo_venda = $vetor_valores['preco_minimo_venda'];
            
            
            
            //Nunca o Preço Líquido Final poderá ser menor que o Preço Mínimo de Venda ...
            if($campos[0]['preco_liq_final'] < $preco_minimo_venda) {//Este IF tem prioridade sobre a lógica de Margem Lucro ...
                $item_fora_custo = 'S';
            }else {*/
                //Verificamos qual é a maior Margem p/ confrontar com a Margem de Lucro Mínima ...
                $maior_margem_utilizar = max($campos[0]['margem_lucro'], $campos[0]['margem_lucro_estimada']);
                /*Se a Maior Margem à Utilizar do Item do ORC < 90% da ML do Grupo vs Divisão e Desconto 
                Extra do Item > 20 em 09/06/2015, por conta da crise tiramos o Desconto Extra > 20 da 
                verificação e mudamos de 90% da Margem de Lucro p/ 80% da Margem de Lucro. Quando 
                terminar a crise voltaremos esses parâmetros ao normal ...*/
                //$item_fora_custo = (($maior_margem_utilizar < $margem_lucro_minima) && $campos[0]['desc_extra'] > 20) ? 'S' : 'N';
                $item_fora_custo = ($maior_margem_utilizar < 0.8 * $margem_lucro_minima) ? 'S' : 'N';
            //}
        }
        return $item_fora_custo;
    }
        
    function calcular_ml_min_pa_vs_cliente($id_produto_acabado, $id_cliente) {
        //Aqui eu busco o Tipo de Cliente do $id_cliente passado por parâmetro p/ saber qual Fator utilizar ...
        $sql = "SELECT id_pais, id_cliente_tipo 
                FROM `clientes` 
                WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
        $campos_cliente = bancos::sql($sql);

        //Aqui eu busco o Status Top do $id_produto_acabado passado por parâmetro p/ saber qual Fator utilizar ...
        $sql = "SELECT ged.margem_lucro_exp, ged.margem_lucro_minima, pa.status_top 
                FROM `produtos_acabados` pa 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        $campos_produto_acabado = bancos::sql($sql);
        $margem_lucro_utilizar  = ($campos_cliente[0]['id_pais'] == 31) ? $campos_produto_acabado[0]['margem_lucro_minima'] : $campos_produto_acabado[0]['margem_lucro_exp'];

        //Se o Cliente for Atacadista, então o procedimento para com o mesmo é diferente concedendo alguns descontos a + ...
        if($campos_cliente[0]['id_cliente_tipo'] == 5) {/************Atacadista************/
            //Nós entendemos que os Atacadistas compram em grande Quantidade e por isso tratamos como fossem TOP A ...
            $fator_ml_min           = 0.95;
            $margem_lucro_minima    = $margem_lucro_utilizar * $fator_ml_min;
            $rotulo_ml_min          = 'MLAtac=';
            $rotulo_preco           = 'Pre&ccedil;o Atac=';
        }else {//Qualquer outra Tipo ...
            if($campos_produto_acabado[0]['status_top'] == 1) {//Top A ...
                $fator_ml_min           = 0.95;
            }else if($campos_produto_acabado[0]['status_top'] == 2) {//Top B ...
                $fator_ml_min           = 1;
            }else if($campos_produto_acabado[0]['status_top'] == 0) {//Não é TOP ...
                //Produto que vende muito pouco, tentamos cobrar um pouquinho a mais ...
                $fator_ml_min           = 1.1;
            }
            $margem_lucro_minima    = $margem_lucro_utilizar * $fator_ml_min;
            $rotulo_ml_min          = 'MLMin=';
            $rotulo_preco           = 'Pre&ccedil;o Ideal=';
        }
        /*Estamos baixando de 45 p/ 35 por causa q no nosso Custo agora estamos levando em Conta 
        os Impostos Federais - 18/09/2013 ...*/
        if($margem_lucro_minima < genericas::variavel(59)) $margem_lucro_minima = genericas::variavel(59);
        return array('rotulo_ml_min'=> $rotulo_ml_min, 'rotulo_preco'=> $rotulo_preco, 'margem_lucro_minima'=> $margem_lucro_minima, 'fator_ml_min'=> $fator_ml_min);
    }
    
    function cota_total_do_representante($id_representante, $data_inicial_relatorio, $data_final_relatorio, $pegar_cota_como_supervisor = 'N') {
        if($pegar_cota_como_supervisor == 'S') {//Lógica de Supervisor que é muito mais simples ...
            //Nesse caso não preciso das Datas de Vigência da Cota e passo somente as Datas do Relatório ...
            $vetor      = data::diferenca_data($data_inicial_relatorio, $data_final_relatorio);
            $qtde_meses = intval($vetor[0] / 30);//Transformo em Meses ...
            if($qtde_meses == 0) $qtde_meses = 1;//Mesmo que a dif. de dias não chegue a 30, isso representa 1 mês ...
            
            /*Nesse caso as Datas de Início e de Fim não são utilizadas no SQL e trazemos aí somente a última Cota 
            que está em vigência para o determinado Representante passado por parâmetro na sua determinada 
            Empresa Divisão ...*/
            $sql = "SELECT SUM(rc.cota_mensal) AS cota_todos_subordinados 
                    FROM `representantes_vs_supervisores` rs 
                    INNER JOIN `representantes` r ON r.`id_representante` = rs.`id_representante` AND r.`ativo` = '1' 
                    INNER JOIN `representantes_vs_cotas` rc ON rc.`id_representante` = r.`id_representante` AND rc.data_final_vigencia = '0000-00-00' 
                    WHERE rs.`id_representante_supervisor` = '$id_representante' 
                    GROUP BY rs.`id_representante_supervisor` ";
            $campos = bancos::sql($sql);
            return ($campos[0]['cota_todos_subordinados'] * $qtde_meses);
        }else {//Lógica de Representante, bem mais complexa ...
            $executar_while         = 'S';
            $data_inicial           = $data_inicial_relatorio;//A princípio é a própria data Inicial do Rel digitada pelo Usuário ...
            $cota_total_do_periodo  = 0;
            $qtde_meses             = 0;
            
            while($executar_while == 'S') {
                /*Busca o Período de Vigência de Cotas mais próximo da variável $data_inicial ...
                 
                Exemplo verídico: Relatório de 26/07/2014 à 19/08/2014
                 
                O Yamaoka mudou a sua cota no dia 21/08/2014 então dentro desse período, só poderá pegar essa Nova Cota 
                se o Filtro digitado pelo usuário fosse de 26/07/2014 à 25/08/2014 que é o período normal da Folha ...*/
                $sql = "SELECT SUM(cota_mensal) AS cota_mensal, data_inicial_vigencia, data_final_vigencia 
                        FROM `representantes_vs_cotas` 
                        WHERE `id_representante` = '$id_representante' 
                        AND `data_inicial_vigencia` >= '$data_inicial' AND `data_inicial_vigencia` <= '$data_final_relatorio' 
                        GROUP BY `data_inicial_vigencia` ";
                $campos = bancos::sql($sql);
                /*Se não encontrou registro, representa que a Data do Relatório foi maior do que a última 
                variável $data_inicial de Cotas cadastradas ...*/
                if(count($campos) == 0) {
                    //Busco a 1ª Cota Antecedente a variável $data_inicial ...
                    $sql = "SELECT SUM(cota_mensal) AS cota_mensal 
                            FROM `representantes_vs_cotas` 
                            WHERE `id_representante` = '$id_representante' 
                            AND `data_inicial_vigencia` < '$data_inicial' 
                            GROUP BY `data_inicial_vigencia` ORDER BY data_inicial_vigencia DESC ";
                    $campos             = bancos::sql($sql);
                    //Nesse caso não preciso das Datas de Vigência da Cota e passo somente as Datas do Relatório ...
                    $vetor              = data::diferenca_data($data_inicial, $data_final_relatorio);
                    $executar_while     = 'N';
                }else {//Encontrou pelo menos 1 registro ...
                    /*Se a Data Inicial do Relatório for menor que a 1ª Data Inicial de Vigência encontrada, 
                    significa que ainda existe diferença de Dias e que podemos ter valor de Cota nesse período ...*/
                    if($data_inicial < $campos[0]['data_inicial_vigencia']) {
                        //Verifico quantos meses se passaram da Data Inicial do Relatório até a 1ª Data Inicial de Vigência Encontrada ...
                        $vetor      = data::diferenca_data($data_inicial, $campos[0]['data_inicial_vigencia']);
                        $qtde_meses = intval($vetor[0] / 30);//Transformo em Meses ...
                        if($qtde_meses == 0) $qtde_meses = 1;//Mesmo que a dif. de dias não chegue a 30, isso representa 1 mês ...

                        //Verifico se existem Cotas antecedentes da 1ª Data Inicial de Vigência Encontrada ...
                        $sql = "SELECT SUM(cota_mensal) AS cota_mensal, data_inicial_vigencia, data_final_vigencia 
                                FROM `representantes_vs_cotas` 
                                WHERE `id_representante` = '$id_representante' 
                                AND `data_inicial_vigencia` < '$data_inicial' 
                                GROUP BY `data_inicial_vigencia` ORDER BY data_inicial_vigencia DESC ";
                        $campos_anterior_data_inicial = bancos::sql($sql);
                        if(count($campos_anterior_data_inicial) > 0) {//Se sim, trabalho c/ a cota que antecipa a 1ª Data Inicial de Vigência Encontrada no SQL acima ...
                            //Data Inicial do Relatório até última antecedente Data Final de Vigência ...
                            $vetor      = data::diferenca_data($data_inicial, $campos_anterior_data_inicial[0]['data_final_vigencia']);
                            $qtde_meses = intval($vetor[0] / 30);//Transformo em Meses ...
                            if($qtde_meses == 0) $qtde_meses = 1;//Mesmo que a dif. de dias não chegue a 30, isso representa 1 mês ...
                            
                            $cota_total_do_periodo+= ($qtde_meses * $campos_anterior_data_inicial[0]['cota_mensal']);
                        }else {//Não existem períodos anteriores, então essa é a 1ª Cota cadastrada p/ o Representante ...
                            $cota_total_do_periodo+= ($qtde_meses * $campos[0]['cota_mensal']);
                        }
                    }
                    
                    if($campos[0]['data_final_vigencia'] != '0000-00-00') {//Significa que esse Representante ainda possui + Cotas ...
                        //Verifico quantos meses se passaram da Data Inicial de Vigência até a Data Final de Vigência do Período ...
                        $vetor                  = data::diferenca_data($campos[0]['data_inicial_vigencia'], $campos[0]['data_final_vigencia']);
                        $data_final_vigencia    = data::datetodata($campos[0]['data_final_vigencia'], '/');

                        //Somo + 1 pq essa Data Inicial será executada no SQL acima, buscando o novo Período de Cotas ...
                        $data_inicial           = data::adicionar_data_hora($data_final_vigencia, 1);
                        $data_inicial           = data::datatodate($data_inicial, '-');
                        
                        /*Porém eu só posso buscar essas outras Cotas e somar na variável "$cota_total_do_periodo" se a 
                        $data_final_relatorio que foi digitada pelo usuário for maior que a próxima "data_inicial_vigencia" ...

                        /*****************************************************************************************************/
                        /*Observação Importantíssima: essa variável $data_inicial à partir desse momento já está fazendo menção ao próximo 
                        período de Cotas porque "já foi somado + 1 em cima da variável $data_final_vigencia" na linha 1651, agora o do porque 
                        que eu faço esse controle é ... O gerente de Vendas às vezes cria novas cotas p/ um determinado representante mesmo 
                        sem ter sido concluído o período atual de vendas que vai de 26 à 25 ...*/
                        if($data_final_relatorio < $data_inicial) $executar_while = 'N';
                        /*****************************************************************************************************/
                    }else {//Significa que chegamos na última Cota Vigente que está sendo utilizada no momento ...
                        $vetor      = data::diferenca_data($campos[0]['data_inicial_vigencia'], $data_final_relatorio);
                        $executar_while = 'N';
                    }
                }
                $qtde_meses = intval($vetor[0] / 30);//Transformo em Meses ...
                //Esse tratamento evita erro quando o usuário fizer Filtro de Datas com menos de 30 dias de diferença ...
                if($qtde_meses == 0) $qtde_meses = 1;//Mesmo que a dif. de dias não chegue a 30, isso representa 1 mês ...
                $cota_total_do_periodo+= ($qtde_meses * $campos[0]['cota_mensal']);
            }
            return $cota_total_do_periodo;
        }
    }
    
    /*Essa função é utilizada em todo o "Orçamento de Vendas" criando aí um Limite de Desconto para o Vendedor 
    numa possível Venda ...*/
    function preco_minimo_venda($id_orcamento_venda_item = 0, $id_orcamento_venda = 0) {
        $sql = "SELECT ov.`id_cliente`, ov.`nota_sgd`, ovi.`id_produto_acabado`, ovi.`preco_liq_fat` 
                FROM `orcamentos_vendas_itens` ovi 
                INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` ";
        if($id_orcamento_venda_item > 0) {//Se o usuário passou o $id_orcamento_venda_item como parâmetro ...
            $sql.= "WHERE ovi.`id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
        }else {
            $sql.= "AND ov.`id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
        }
        $campos                         = bancos::sql($sql);
        
        //Com o "id_cliente" do Orçamento Venda eu busco qual é o seu Desconto ...
        if($id_orcamento_venda_item > 0) {
            //Através do "id_produto_acabado" do item do Orçamento eu verifico qual é a sua Empresa Divisão ...
            $sql = "SELECT ged.`id_empresa_divisao` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    WHERE pa.`id_produto_acabado` = '".$campos[0]['id_produto_acabado']."' LIMIT 1 ";
            $campos_empresa_divisao = bancos::sql($sql);
            //Aqui nesse caso em específico, eu busco o Desconto do Cliente na sua respectiva Empresa Divisão ...
            $sql = "SELECT desconto_cliente 
                    FROM `clientes_vs_representantes` 
                    WHERE `id_cliente` = '".$campos[0]['id_cliente']."' 
                    AND `id_empresa_divisao` = '".$campos_empresa_divisao[0]['id_empresa_divisao']."' LIMIT 1 ";
        }else {
            /*Nesse caso, através do $id_cliente eu busco o primeiro Desconto do Cliente, devido não se ter um 
            item em específico ...*/
            $sql = "SELECT `desconto_cliente` 
                    FROM `clientes_vs_representantes` 
                    WHERE `id_cliente` = '".$campos[0]['id_cliente']."' LIMIT 1 ";
        }
        $campos_desconto_cliente        = bancos::sql($sql);
        
        /*Se o Desconto do Cliente for menor do que a variável Genérica "Fator Desconto Máximo de Vendas",
        usamos o Desconto do Cliente ...*/
        $fator_desconto_maximo_vendas   = min(genericas::variavel(19), (1 - $campos_desconto_cliente[0]['desconto_cliente'] / 100));
        
        //Somente quando a Negociação = 'SGD' que existirá "Outros Impostos Federais" ...
        $outros_impostos_federais       = ($campos[0]['nota_sgd'] == 'S') ? genericas::variavel(34) : 0;
        $fator_desconto_maximo_vendas   = $fator_desconto_maximo_vendas * 0.9 * (1 - $outros_impostos_federais / 100);
        $desconto_maximo_venda          = (1 - $fator_desconto_maximo_vendas) * 100;
        
        //Somente se o "$id_orcamento_venda_item" for passado por parâmetro que serão realizados os cálculos abaixo ...
        if($id_orcamento_venda_item > 0) {
            $desconto_icms_sgd  = intermodular::desconto_icms_sgd($campos[0]['nota_sgd'], $campos[0]['id_cliente'], $campos[0]['id_produto_acabado']);
            $preco_minimo_venda = $campos[0]['preco_liq_fat'] * (1 - $desconto_icms_sgd / 100) * $fator_desconto_maximo_vendas;
        }else {
            $preco_minimo_venda = 0;
        }
        return array('desconto_maximo_venda' => $desconto_maximo_venda, 'preco_minimo_venda' => round($preco_minimo_venda, 2));
    }
    
    /*Esse 3º parâmetro "$forma_listagem" possui 2 valores: 
     
    T => Todos os Itens ou vazio, quando não se passa nenhum parâmetro ...
    A => Apenas Itens com Pendência ...*/
    function calculo_preco_venda_medio_nf_sp_30ddl_rs($id, $tipo_parametro, $forma_listagem) {
        $valor_dolar_dia        = genericas::moeda_dia('dolar');
        $impostos_federais      = genericas::variavel(34);
        $qtde_maxima_Xmeses     = 36;
        
        $data_inicial_3meses    = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -90), '-');
        $data_inicial_6meses    = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -180), '-');
        $data_inicial_12meses   = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -365), '-');
        $data_inicial_Xmeses    = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), (-365 * $qtde_maxima_Xmeses / 12)), '-');

        $total_qtde_inicial_3meses      = 0;
        $total_qtde_inicial_6meses      = 0;
        $total_qtde_inicial_12meses     = 0;
        $total_qtde_inicial_Xmeses      = 0;

        $total_qtde_pedido_venda_3meses = 0;
        $total_qtde_pedido_venda_6meses = 0;
        $total_qtde_pedido_venda_12meses= 0;
        
        $condicao_periodo   = " AND pv.`data_emissao` >= '$data_inicial_Xmeses' ";
        
        /******************************************Produto Acabado*****************************************/
        if($tipo_parametro == 'PA') {//Produto Acabado ...
            $vetor_pa_atrelados = custos::pas_atrelados($id);
            $id_pas_atrelados   = implode(',', $vetor_pa_atrelados);
            $where              = " pvi.`id_produto_acabado` IN ($id_pas_atrelados) ";
        /**********************************************Pedido**********************************************/
        }else if($tipo_parametro == 'PVI') {//Item de Pedido de Venda ...
            $where              = " pvi.`id_pedido_venda_item` = '$id' ";
        }
        
        /*Se a soma dos Itens Pendentes + Separados > '0' que equivale aos campos -> 
        "pvi.`qtde` - pvi.`qtde_pendente` - pvi.`vale` - pvi.`qtde_faturada`" ...*/
        if($forma_listagem == 'A') $condicao_forma_listagem = " AND (pvi.`qtde_pendente` + (pvi.`qtde` - pvi.`qtde_pendente` - pvi.`vale` - pvi.`qtde_faturada`) > '0') ";
        
        //Aqui eu busco a Qtde de Pedidos que estão atrelados a este Produto e aos seus atrelados de forma Total ...
        $sql = "SELECT c.`id_pais`, c.`id_uf`, ovi.`id_orcamento_venda`, pv.`id_pedido_venda`, pv.`id_cliente`, 
                pv.`id_empresa`, pv.`finalidade`, pv.`data_emissao`, pvi.`id_produto_acabado`, pvi.`qtde`, 
                pvi.`qtde_pendente`, pvi.`preco_liq_final`, (pvi.`qtde` * pvi.`preco_liq_final`) AS total_item, 
                (pvi.`qtde` * pvi.`preco_liq_final` / (1 + pvi.`margem_lucro` / 100)) AS total_item_ml_zero 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` AND ovi.`status` > '0' 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` $condicao_periodo 
                INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
                WHERE $where 
                $condicao_forma_listagem ORDER BY pv.`data_emissao` DESC ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {//Dentro desse Total, eu vou fatiando por Período ...
            $tx_financeira      = custos::calculo_taxa_financeira($campos[$i]['id_orcamento_venda']);
            
            $dados_produto      = intermodular::dados_impostos_pa($campos[$i]['id_produto_acabado'], 1);
            $ICMS_SP            = ($dados_produto['icms_cadastrado']) * (100 - $dados_produto['reducao_cadastrado']) / 100;

            $dados_produto      = intermodular::dados_impostos_pa($campos[$i]['id_produto_acabado'], $campos[$i]['id_uf'], $campos[$i]['id_cliente'], $campos[$i]['id_empresa'], $campos[$i]['finalidade']);
            $ICMS_uf_cliente    = ($dados_produto['icms']) * (100 - $dados_produto['reducao']) / 100;
            
            $desc_icms          = $ICMS_SP - $ICMS_uf_cliente;
        
            if(($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2) && $campos[$i]['id_pais'] == 31) {//Alba ou Tool Master - NF e Cliente de País Nacional "Brasil" ...
                $preco_NF_SP_30_ddl = $campos[$i]['preco_liq_final'] / (1 + $tx_financeira / 100) / (1 - $desc_icms / 100);
            }else {//Grupo - SGD ou País do Cliente for Estrangeiro ...
                $preco_NF_SP_30_ddl = $campos[$i]['preco_liq_final'] / (1 + $tx_financeira / 100) / (1 - ($desc_icms + $impostos_federais) / 100);
                //Se o País do Cliente for Estrangeiro ...
                if($campos[$i]['id_pais'] != 31) $preco_NF_SP_30_ddl*= $valor_dolar_dia;
            }
            $preco_NF_SP_30_ddl = round($preco_NF_SP_30_ddl, 2);
            
            $total_lote_NF_SP_30_ddl = $campos[$i]['qtde'] * $preco_NF_SP_30_ddl;
            
            if($campos[$i]['data_emissao'] >= $data_inicial_3meses) {
                $total_qtde_pedido_venda_3meses++;
                $total_qtde_inicial_3meses+= $campos[$i]['qtde'];
                
                if($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2) {//NF ...
                    $total_venda_nf_rs_3meses+= $campos[$i]['total_item'];
                }else {//SGD ...
                    $total_venda_sgd_rs_3meses+= $campos[$i]['total_item'];
                }
                
                $total_qtde_pendencia_3meses+= $campos[$i]['qtde_pendente'];
                $total_pendencia_rs_3meses+= $campos[$i]['qtde_pendente'] * $campos[$i]['preco_liq_final'];
                $total_item_ml_zero_3meses+= $campos[$i]['total_item_ml_zero'];
                $total_lote_NF_SP_30_ddl_3meses+= $total_lote_NF_SP_30_ddl;
                $total_item_rs_3meses+= $campos[$i]['total_item'];
            }
            
            if($campos[$i]['data_emissao'] >= $data_inicial_6meses) {
                $total_qtde_pedido_venda_6meses++;
                $total_qtde_inicial_6meses+= $campos[$i]['qtde'];
                
                if($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2) {//NF ...
                    $total_venda_nf_rs_6meses+= $campos[$i]['total_item'];
                }else {//SGD ...
                    $total_venda_sgd_rs_6meses+= $campos[$i]['total_item'];
                }
                
                $total_qtde_pendencia_6meses+= $campos[$i]['qtde_pendente'];
                $total_pendencia_rs_6meses+= $campos[$i]['qtde_pendente'] * $campos[$i]['preco_liq_final'];
                $total_item_ml_zero_6meses+= $campos[$i]['total_item_ml_zero'];
                $total_lote_NF_SP_30_ddl_6meses+= $total_lote_NF_SP_30_ddl;
                $total_item_rs_6meses+= $campos[$i]['total_item'];
            }
            if($campos[$i]['data_emissao'] >= $data_inicial_12meses) {
                $total_qtde_pedido_venda_12meses++;
                $total_qtde_inicial_12meses+= $campos[$i]['qtde'];
                
                if($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2) {//NF ...
                    $total_venda_nf_rs_12meses+= $campos[$i]['total_item'];
                }else {//SGD ...
                    $total_venda_sgd_rs_12meses+= $campos[$i]['total_item'];
                }
                
                $total_qtde_pendencia_12meses+= $campos[$i]['qtde_pendente'];
                $total_pendencia_rs_12meses+= $campos[$i]['qtde_pendente'] * $campos[$i]['preco_liq_final'];
                $total_item_ml_zero_12meses+= $campos[$i]['total_item_ml_zero'];
                $total_lote_NF_SP_30_ddl_12meses+= $total_lote_NF_SP_30_ddl;
                $total_item_12_rs_meses+= $campos[$i]['total_item'];
            }
            $total_qtde_pedido_venda_Xmeses++;
            $total_qtde_inicial_Xmeses+=       $campos[$i]['qtde'];
            
            if($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2) {//NF ...
                $total_venda_nf_rs_Xmeses+= $campos[$i]['total_item'];
            }else {//SGD ...
                $total_venda_sgd_rs_Xmeses+= $campos[$i]['total_item'];
            }
            
            $total_qtde_pendencia_Xmeses+= $campos[$i]['qtde_pendente'];
            $total_pendencia_rs_Xmeses+= $campos[$i]['qtde_pendente'] * $campos[$i]['preco_liq_final'];
            $total_item_ml_zero_Xmeses+= $campos[$i]['total_item_ml_zero'];
            $total_lote_NF_SP_30_ddl_Xmeses+=  $total_lote_NF_SP_30_ddl;
            $total_item_X_rs_meses+= $campos[$i]['total_item'];
        }
        $total_mmv_3meses       = ($total_qtde_inicial_3meses / 3);
        $total_mmv_6meses       = ($total_qtde_inicial_6meses / 6);
        $total_mmv_12meses      = ($total_qtde_inicial_12meses / 12);
        $total_mmv_Xmeses       = ($total_qtde_inicial_Xmeses / $qtde_maxima_Xmeses);
        
        $pecas_por_pedido_3meses    = ($total_qtde_inicial_3meses / $total_qtde_pedido_venda_3meses);
        $pecas_por_pedido_6meses    = ($total_qtde_inicial_6meses / $total_qtde_pedido_venda_6meses);
        $pecas_por_pedido_12meses   = ($total_qtde_inicial_12meses / $total_qtde_pedido_venda_12meses);
        $pecas_por_pedido_Xmeses    = ($total_qtde_inicial_Xmeses / $total_qtde_pedido_venda_Xmeses);
        
        $total_mlm_3meses       = ($total_item_rs_3meses / $total_item_ml_zero_3meses - 1) * 100;
        $total_mlm_6meses       = ($total_item_rs_6meses / $total_item_ml_zero_6meses - 1) * 100;
        $total_mlm_12meses      = ($total_item_12_rs_meses / $total_item_ml_zero_12meses - 1) * 100;
        $total_mlm_Xmeses       = ($total_item_X_rs_meses / $total_item_ml_zero_Xmeses - 1) * 100;
        
        $preco_medio_NF_SP_30_ddl_3meses        = $total_lote_NF_SP_30_ddl_3meses / $total_qtde_inicial_3meses;
        $preco_medio_NF_SP_30_ddl_6meses        = $total_lote_NF_SP_30_ddl_6meses / $total_qtde_inicial_6meses;
        $preco_medio_NF_SP_30_ddl_12meses       = $total_lote_NF_SP_30_ddl_12meses / $total_qtde_inicial_12meses;
        $preco_medio_NF_SP_30_ddl_Xmeses        = $total_lote_NF_SP_30_ddl_Xmeses / $total_qtde_inicial_Xmeses;
        
        return array('preco_NF_SP_30_ddl' => $preco_NF_SP_30_ddl, 'total_lote_NF_SP_30_ddl' => $total_lote_NF_SP_30_ddl, 'total_qtde_inicial_3meses' => $total_qtde_inicial_3meses, 'total_qtde_inicial_6meses' => $total_qtde_inicial_6meses, 'total_qtde_inicial_12meses' => $total_qtde_inicial_12meses, 'total_qtde_inicial_Xmeses' => $total_qtde_inicial_Xmeses, 'total_venda_nf_rs_3meses' => $total_venda_nf_rs_3meses, 'total_venda_nf_rs_6meses' => $total_venda_nf_rs_6meses, 'total_venda_nf_rs_12meses' => $total_venda_nf_rs_12meses, 'total_venda_nf_rs_Xmeses' => $total_venda_nf_rs_Xmeses, 'total_venda_sgd_rs_3meses' => $total_venda_sgd_rs_3meses, 'total_venda_sgd_rs_6meses' => $total_venda_sgd_rs_6meses, 'total_venda_sgd_rs_12meses' => $total_venda_sgd_rs_12meses, 'total_venda_sgd_rs_Xmeses' => $total_venda_sgd_rs_Xmeses, 'total_qtde_pendencia_3meses' => $total_qtde_pendencia_3meses, 'total_qtde_pendencia_6meses' => $total_qtde_pendencia_6meses, 'total_qtde_pendencia_12meses' => $total_qtde_pendencia_12meses, 'total_qtde_pendencia_Xmeses' => $total_qtde_pendencia_Xmeses, 'total_pendencia_rs_3meses' => $total_pendencia_rs_3meses, 'total_pendencia_rs_6meses' => $total_pendencia_rs_6meses, 'total_pendencia_rs_12meses' => $total_pendencia_rs_12meses, 'total_pendencia_rs_Xmeses' => $total_pendencia_rs_Xmeses, 'total_mmv_3meses' => $total_mmv_3meses, 'total_mmv_6meses' => $total_mmv_6meses, 'total_mmv_12meses' => $total_mmv_12meses, 'total_mmv_Xmeses' => $total_mmv_Xmeses, 'pecas_por_pedido_3meses' => $pecas_por_pedido_3meses, 'pecas_por_pedido_6meses' => $pecas_por_pedido_6meses, 'pecas_por_pedido_12meses' => $pecas_por_pedido_12meses, 'pecas_por_pedido_Xmeses' => $pecas_por_pedido_Xmeses, 'total_mlm_3meses' => $total_mlm_3meses, 'total_mlm_6meses' => $total_mlm_6meses, 'total_mlm_12meses' => $total_mlm_12meses, 'total_mlm_Xmeses' => $total_mlm_Xmeses, 'preco_medio_NF_SP_30_ddl_3meses' => $preco_medio_NF_SP_30_ddl_3meses, 'preco_medio_NF_SP_30_ddl_6meses' => $preco_medio_NF_SP_30_ddl_6meses, 'preco_medio_NF_SP_30_ddl_12meses' => $preco_medio_NF_SP_30_ddl_12meses, 'preco_medio_NF_SP_30_ddl_Xmeses' => $preco_medio_NF_SP_30_ddl_Xmeses);
    }
    
    /*Essa função calcula todos os ranges de Comissão da tabela novas_comissoes_margens_lucros ...

    Quando este parâmetro $base_comis_dentro_sp vier preenchido, significa que está sendo um único intervalo da escala de acordo com o que 
    o usuário digitou, do contrário está sendo alterada uma variável genérica que conseqüentemente terá de alterar todos os valores 
    da escala ...*/
    function calcular_comissoes($base_comis_dentro_sp) {
        if(!class_exists('genericas')) require 'genericas.php';
        
        if($base_comis_dentro_sp > 0) {//Altera um único registro na tabela `novas_comissoes_margens_lucros` ...
            $comis_vend_fora_sp     = ($base_comis_dentro_sp * genericas::variavel(54) < 1) ? 1 : ($base_comis_dentro_sp * genericas::variavel(54));
            $comis_vend_interior_sp = ($base_comis_dentro_sp * genericas::variavel(55) < 1.5) ? 1.5 : ($base_comis_dentro_sp * genericas::variavel(55));
            $comis_autonomo         = ($base_comis_dentro_sp * genericas::variavel(56) < 2) ? 2 : ($base_comis_dentro_sp * genericas::variavel(56));
            $comis_vend_sup_interno = ($base_comis_dentro_sp * genericas::variavel(57) < 1) ? 1 : ($base_comis_dentro_sp * genericas::variavel(57));
            $comis_export           = ($base_comis_dentro_sp * genericas::variavel(58) < 1) ? 1 : ($base_comis_dentro_sp * genericas::variavel(58));
            $comis_sup_outras_ufs   = ($base_comis_dentro_sp * genericas::variavel(88) < 1.25) ? 1.25 : ($base_comis_dentro_sp * genericas::variavel(88));
            $comis_sup_autonomo     = ($base_comis_dentro_sp * genericas::variavel(89) < 1.5) ? 1.5 : ($base_comis_dentro_sp * genericas::variavel(89));
            //Arredonda todos valores para gravar no Banco de Dados ...
            $comis_vend_fora_sp     = round($comis_vend_fora_sp, 2);
            $comis_vend_interior_sp = round($comis_vend_interior_sp, 2);
            $comis_autonomo         = round($comis_autonomo, 2);
            $comis_vend_sup_interno = round($comis_vend_sup_interno, 2);
            $comis_export           = round($comis_export, 2);
            $comis_sup_outras_ufs   = round($comis_sup_outras_ufs, 2);
            $comis_sup_autonomo     = round($comis_sup_autonomo, 2);
        
            return array('comis_vend_fora_sp' => $comis_vend_fora_sp, 'comis_vend_interior_sp' => $comis_vend_interior_sp, 'comis_autonomo' => $comis_autonomo, 'comis_vend_sup_interno' => $comis_vend_sup_interno, 'comis_export' => $comis_export, 'comis_sup_outras_ufs' => $comis_sup_outras_ufs, 'comis_sup_autonomo' => $comis_sup_autonomo);
        }else {//Altera todos os registros da tabela `novas_comissoes_margens_lucros` ...
            //Busco todos os registros da tabela `novas_comissoes_margens_lucros` para recálculo ...
            $sql = "SELECT `id_nova_comissao_margem_lucro`, `base_comis_dentro_sp` 
                    FROM `novas_comissoes_margens_lucros` 
                    ORDER BY `id_nova_comissao_margem_lucro` ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
            for($i = 0; $i < $linhas; $i++) {
                $comis_vend_fora_sp     = ($campos[$i]['base_comis_dentro_sp'] * genericas::variavel(54) < 1) ? 1 : ($campos[$i]['base_comis_dentro_sp'] * genericas::variavel(54));
                $comis_vend_interior_sp = ($campos[$i]['base_comis_dentro_sp'] * genericas::variavel(55) < 1.5) ? 1.5 : ($campos[$i]['base_comis_dentro_sp'] * genericas::variavel(55));
                $comis_autonomo         = ($campos[$i]['base_comis_dentro_sp'] * genericas::variavel(56) < 2) ? 2 : ($campos[$i]['base_comis_dentro_sp'] * genericas::variavel(56));
                $comis_vend_sup_interno = ($campos[$i]['base_comis_dentro_sp'] * genericas::variavel(57) < 1) ? 1 : ($campos[$i]['base_comis_dentro_sp'] * genericas::variavel(57));
                $comis_export           = ($campos[$i]['base_comis_dentro_sp'] * genericas::variavel(58) < 1) ? 1 : ($campos[$i]['base_comis_dentro_sp'] * genericas::variavel(58));
                $comis_sup_outras_ufs   = ($campos[$i]['base_comis_dentro_sp'] * genericas::variavel(88) < 1.25) ? 1.25 : ($campos[$i]['base_comis_dentro_sp'] * genericas::variavel(88));
                $comis_sup_autonomo     = ($campos[$i]['base_comis_dentro_sp'] * genericas::variavel(89) < 1.5) ? 1.5 : ($campos[$i]['base_comis_dentro_sp'] * genericas::variavel(89));
                //Arredonda todos valores para gravar no Banco de Dados ...
                $comis_vend_fora_sp     = round($comis_vend_fora_sp, 2);
                $comis_vend_interior_sp = round($comis_vend_interior_sp, 2);
                $comis_autonomo         = round($comis_autonomo, 2);
                $comis_vend_sup_interno = round($comis_vend_sup_interno, 2);
                $comis_export           = round($comis_export, 2);
                $comis_sup_outras_ufs   = round($comis_sup_outras_ufs, 2);
                $comis_sup_autonomo     = round($comis_sup_autonomo, 2);
                
                $sql = "UPDATE `novas_comissoes_margens_lucros` SET `comis_vend_fora_sp` = '$comis_vend_fora_sp', `comis_vend_interior_sp` = '$comis_vend_interior_sp', `comis_autonomo` = '$comis_autonomo', `comis_vend_sup_interno` = '$comis_vend_sup_interno', `comis_export` = '$comis_export', `comis_sup_outras_ufs` = '$comis_sup_outras_ufs', `comis_sup_autonomo` = '$comis_sup_autonomo', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_nova_comissao_margem_lucro` = '".$campos[$i]['id_nova_comissao_margem_lucro']."' LIMIT 1 ";
                bancos::sql($sql);
            }
        }
    }
    
    //Essa função retorna um array com Prazos de Entrega que será visualizado em toda parte de Orc / Ped e NF ...
    function prazos_entrega() {
        return array(
            'I' => 'IMEDIATO', 
            'P' => 'PARCIAL', 
            'S' => 'SOB-CONSULTA', 
            'P3' => 'PARCIAL 3', 
            'P45' => 'PARCIAL 45', 
            '1' =>  '1 dia', 
            '2' =>  '2 dias', 
            '3' =>  '3 dias', 
            '4' =>  '4 dias', 
            '5' =>  '5 dias', 
            '7' =>  '7 dias', 
            '14' =>  '14 dias', 
            '21' =>  '21 dias', 
            '30' =>  '30 dias', 
            '45' =>  '45 dias', 
            '60' =>  '60 dias', 
            '75' =>  '75 dias', 
            '90' =>  '90 dias', 
            '120' =>  '120 dias'
        );
    }
    
    function email_automatico_vales_para_clientes() {
        if(!class_exists('data'))   require 'data.php';//Caso exista eu desvio a classe
        
        $data_atual_menos_10dias    = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -10), '-');
        
        //Aqui eu listo todos os Pedidos que foram entregues em Vales e não foram faturados ...
        $sql = "SELECT DISTINCT(pvi.`id_pedido_venda`) AS id_pedido_venda, pv.`data_emissao`, c.`email` 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`data_emissao` >= '$data_atual_menos_10dias' 
                INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
                WHERE pvi.`vale` > '0' 
                AND pvi.`status` = '0' 
                ORDER BY pv.`data_emissao` DESC ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
            //Verifico a Qtde de Dias transcorridos da "Data de Emissão do Pedido" até a "Data de Hoje" ...
            $vetor_datas    = data::diferenca_data($campos[$i]['data_emissao'], date('Y-m-d'));
            $qtde_dias      = $vetor_datas[0];
            
            //Primeira Remessa, Segunda Remessa e Terceira Remessa p/ Envio de E-mails ...
            if($qtde_dias == 3 || $qtde_dias == 6 || $qtde_dias == 10) {
                //echo $campos[$i]['email'].'<br/>';
            }
        }
    }
    
    /*Essa função retorna um array com os ids_logins que podem enxergar as Margens de Lucro em diferentes 
    pontos do Sistema ...

    Trabalho com o id_login nesse caso porque tenho alguns logins "Representantes" que são não Funcionários 
    que acessam o Sistema e precisam enxergar essa informação ...*/
    function logins_com_acesso_margens_lucro() {
        return array(
            '22', //Roberto Diretor ...
            '25', //Fábio Petroni ...
            '27', //Rivaldo ...
            '32', //Wilson Chefe ...
            '35', //Rodrigo Soares ...
            '52', //Agueda ...
            '53', //Solange ...
            '89', //Nishimura ...
            '92', //Dárcio "porque programa" ...
            '95', //Ubirajara ...
            '99', //Bispo ...
            '132'//Mariza ...
        );
    }
}
?>