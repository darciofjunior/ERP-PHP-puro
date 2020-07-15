<?
if(!class_exists('bancos')) require 'bancos.php';//CASO EXISTA EU DESVIO A CLASSE ...
class financeiros {
    function controle_credito($id_cliente) {
        $data_atual     = date('d/m/Y');
        $ultimos_45dias = data::datatodate(data::adicionar_data_hora($data_atual, -45), '-');
        $ultimos_6meses = data::datatodate(data::adicionar_data_hora($data_atual, -180), '-');
        //Busco o Crédito atual do Cliente ...
        $sql = "SELECT `credito` 
                FROM `clientes` 
                WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
        $campos = bancos::sql($sql);
        //Se o Crédito for "C" ou "D" o sistema simplesmente retorna esse Crédito, s/ atualizar nada ...
        if($campos[0]['credito'] == 'C' || $campos[0]['credito'] == 'D') return $campos[0]['credito'];
        /************************************************************************************************/
        /*****************************Controle Especial somente p/ Crédito B*****************************/
        /************************************************************************************************/
        //Verifico se este cliente está com seu crédito a mais de 45 dias - 1 mês e meio sem modificações ...
        $sql = "SELECT `id_cliente` 
                FROM `clientes` 
                WHERE (SUBSTRING(`credito_data`, 1, 10) = '0000-00-00' OR SUBSTRING(`credito_data`, 1, 10) < '$ultimos_45dias') 
                AND `id_cliente` = '$id_cliente' LIMIT 1 ";
        $campos_cliente = bancos::sql($sql);
        if(count($campos_cliente) == 1) {//Está a mais de 45 dias ...
            //Vejo se este Cliente possui pelo menos 1 Compra "Faturamento" dentro dos últimos 6 meses - 180 dias ...
            $sql = "SELECT `id_nf` 
                    FROM `nfs` 
                    WHERE `data_emissao` > '$ultimos_6meses' 
                    AND `id_cliente` = '$id_cliente' LIMIT 1 ";
            $campos_nfs = bancos::sql($sql);
            if(count($campos_nfs) == 0) {//Não tem compra nos últimos 6 meses ...
                //Por falta de movimentação e por segurança modificamos o crédito p/ D "como se esse fosse um novo Cliente" ...
                $sql = "UPDATE `clientes` SET `credito` = 'D', `credito_data` = '".date('Y-m-d H:i:s')."' WHERE `id_cliente` = '$id_cliente' AND `credito` <> 'D' LIMIT 1 ";
                bancos::sql($sql);
                return 'D';
            }
        }
        /************************************************************************************************/
        /*Está a menos de 45 dias sem modificações de Crédito ou possui compra nos últimos 6 meses, 
        sendo assim o sistema só retorna o Crédito provavelmente "B" ...*/
        return $campos[0]['credito'];
    }

    function cadastrar_contas_automaticas() {
        //Busco somente as Contas Automáticas que estão com a Marcação conta_ativa = 'S' no "Sistema" ...
        $sql = "SELECT * 
                FROM `contas_apagares_automaticas` 
                WHERE `conta_ativa` = 'S' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
            $nova                           = 'nao';//NÃO pode retirar esta linha, porque serve de controle lá embaixo ...
            $id_conta_apagar_automatica     = $campos[$i]['id_conta_apagar_automatica'];
            $id_empresa                     = $campos[$i]['id_empresa'];
            $id_tipo_pagamento_recebimento  = $campos[$i]['id_tipo_pagamento_recebimento'];
            $id_tipo_moeda                  = $campos[$i]['id_tipo_moeda'];
            $dia_exibicao                   = $campos[$i]['dia_exibicao'];
            $intervalo                      = $campos[$i]['intervalo'];
            $tipo_data                      = $campos[$i]['tipo_data'];
            $previsao                       = $campos[$i]['previsao'];
            $data_proximo_vencimento        = $campos[$i]['data_vencimento'];
            $qtde_parcelas                  = $campos[$i]['qtde_parcelas'];
            $valor                          = $campos[$i]['valor'];
            $observacao                     = $campos[$i]['observacao'];

            $dia                            = substr($campos[$i]['data_vencimento'], 8, 2);
            $mes                            = substr($campos[$i]['data_vencimento'], 5, 2);
            $ano                            = substr($campos[$i]['data_vencimento'], 0, 4);
            $semana                         = data::numero_semana($dia, $mes, $ano);
            /*Tratamento com variável "$data_emissao" que será adicionada no Campo Data de Emissão 
            da Conta à Pagar ... Nunca que a Data de Emissão pode ser Maior do que a Data de Vencimento ...

            Se a Data de Emissão for maior do que a Data de Vencimento, a Data de Emissão assume a 
            Data de Vencimento ...*/
            $data_emissao_conta_apagar      = (date('Y-m-d') > $campos[$i]['data_vencimento']) ? $campos[$i]['data_vencimento'] : date('Y-m-d');
            
            /*Comentado no dia 26/06/2015 porque segundo a Dona Sandra as opções 1 e 2 não 
            são utilizadas ...*/
            
            /*switch($status) {
                case 0://Tipo de Automação "POR DATA" ...
                    /**********************Esquema Provisório**********************/
                    //Verifico se existe alguma conta à Pagar Automatica que esteja em aberto, que não foi paga ...
                    /*$sql = "SELECT `id_conta_apagar` 
                            FROM `contas_apagares` 
                            WHERE `id_conta_apagar_automatica` = '$id_conta_apagar_automatica' 
                            AND `status` < '2' LIMIT 1 ";
                    $campos_conta_apagar = bancos::sql($sql);
                    /*Segurança p/ não gerar Conta a Pagar Automática desnecessária ...
                    
                    Só gera se a Conta Automática não tiver nenhuma parcela ou se a mesma tiver mais de 
                    uma parcela que ocorre quando é contrato e cuja Conta é utilizada apenas uma única vez
                    na vida ...
                    if(($qtde_parcelas <= 1 && count($campos_conta_apagar) == 0) || $qtde_parcelas > 1) {
                        $data_atual                     = date('Ymd');
                        $data_criacao_proximo_registro  = data::datatodate(data::adicionar_data_hora(data::datetodata($data_proximo_vencimento, '/'), -$dia_exibicao), '');
                        if($data_criacao_proximo_registro <= $data_atual) $nova = 'sim';
                    }
                break;
                case 1://Tipo de Automação "PAGO A CONTA ANTERIOR" ...
                    //Verifico a última "Conta à Pagar" que foi gerada através desse $id_conta_apagar_automatica ...
                    $sql = "SELECT `status` 
                            FROM `contas_apagares` 
                            WHERE `id_conta_apagar_automatica` = '$id_conta_apagar_automatica' ORDER BY `id_conta_apagar` DESC LIMIT 1 ";
                    $campos_conta_apagar = bancos::sql($sql);
                    if($campos_conta_apagar[0]['status'] == 2) $nova = 'sim';//Significa que essa já foi quitada ...
                break;
                default://Tipo de Automação "AMBAS ACIMA" ...
                    /**********************Esquema Provisório**********************/
                    //Verifico se existe alguma conta à Pagar Automatica que esteja em aberto, que não foi paga ...
                    /*$sql = "SELECT `id_conta_apagar` 
                            FROM `contas_apagares` 
                            WHERE `id_conta_apagar_automatica` = '$id_conta_apagar_automatica' 
                            AND `status` < '2' LIMIT 1 ";
                    $campos_conta_apagar = bancos::sql($sql);
                    /*Segurança p/ não gerar Conta a Pagar Automática desnecessária ...
                    
                    Só gera se a Conta Automática não tiver nenhuma parcela ou se a mesma tiver mais de 
                    uma parcela que ocorre quando é contrato e cuja Conta é utilizada apenas uma única vez
                    na vida ...
                    if(($qtde_parcelas <= 1 && count($campos_conta_apagar) == 0) || $qtde_parcelas > 1) {
                        $data_atual                     = date('Ymd');
                        $data_criacao_proximo_registro  = data::datatodate(data::adicionar_data_hora(data::datetodata($data_proximo_vencimento, '/'), -$dia_exibicao), '');
                        if($data_criacao_proximo_registro <= $data_atual) $nova = 'sim';
                        //Verifico a última "Conta à Pagar" que foi gerada através desse $id_conta_apagar_automatica ...
                        $sql = "SELECT `status` 
                                FROM `contas_apagares` 
                                WHERE `id_conta_apagar_automatica` = '$id_conta_apagar_automatica' ORDER BY `id_conta_apagar` DESC LIMIT 1 ";
                        $campos_conta_apagar = bancos::sql($sql);
                        if($campos_conta_apagar[0]['status'] == 2) $nova = 'sim';//Significa que essa já foi quitada ...
                    }
                break;
            }*/
            
            /**************************************************************/
            /**************************Ambas Acima*************************/
            /**************************************************************/
            
            /**********************Esquema Provisório**********************/
            //Verifico se existe alguma conta à Pagar Automatica que esteja em aberto, que não foi paga ...
            /*$sql = "SELECT `id_conta_apagar` 
                    FROM `contas_apagares` 
                    WHERE `id_conta_apagar_automatica` = '$id_conta_apagar_automatica' 
                    AND `status` < '2' LIMIT 1 ";
            $campos_conta_apagar = bancos::sql($sql);*/
            /*Segurança p/ não gerar Conta a Pagar Automática desnecessária ...
                    
            Só gera se a Conta Automática não tiver nenhuma parcela ou se a mesma tiver mais de 
            uma parcela que ocorre quando é contrato e cuja Conta é utilizada apenas uma única vez
            na vida ...*/
            //if(($qtde_parcelas == 0 && count($campos_conta_apagar) == 0) || $qtde_parcelas > 1) {
            
            /*Se a conta Automática for ativa = 'S' ou seja conta utilizável ...
            if($conta_ativa == 'S') {*/
                $data_atual                     = date('Ymd');
                $data_criacao_proximo_registro  = data::datatodate(data::adicionar_data_hora(data::datetodata($data_proximo_vencimento, '/'), -$dia_exibicao), '');
                if($data_criacao_proximo_registro <= $data_atual) $nova = 'sim';
                //Verifico a última "Conta à Pagar" que foi gerada através desse $id_conta_apagar_automatica ...
                /*$sql = "SELECT `status` 
                        FROM `contas_apagares` 
                        WHERE `id_conta_apagar_automatica` = '$id_conta_apagar_automatica' ORDER BY `id_conta_apagar` DESC LIMIT 1 ";
                $campos_conta_apagar = bancos::sql($sql);
                if($campos_conta_apagar[0]['status'] == 2) $nova = 'sim';//Significa que essa já foi quitada ...*/
            //}
            /**************************************************************/

            if($nova == 'sim') {//Significa que pode ser gerado uma Nova Conta à Pagar através da conta automática ...
                //Aqui verifico os dados bancarios da conta à pagar ...
                $sql = "SELECT caa.`numero_conta`, caa.`banco`, caa.`agencia`, caa.`num_cc`, caa.`correntista`, 
                        pff.`id_fornecedor`, pff.`id_produto_financeiro`, pf.`id_grupo` 
                        FROM `contas_apagares_automaticas` caa 
                        INNER JOIN `produtos_financeiros_vs_fornecedor` pff ON pff.`id_produto_financeiro_vs_fornecedor` = caa.`id_produto_financeiro_vs_fornecedor` 
                        INNER JOIN `produtos_financeiros` pf ON pf.`id_produto_financeiro` = pff.`id_produto_financeiro` 
                        WHERE caa.`id_conta_apagar_automatica` = '$id_conta_apagar_automatica' LIMIT 1 ";
                $campos_pffs            = bancos::sql($sql);
                $id_fornecedor          = $campos_pffs[0]['id_fornecedor'];
                $id_produto_financeiro  = $campos_pffs[0]['id_produto_financeiro'];
                $id_grupo               = $campos_pffs[0]['id_grupo'];
                $numero_conta           = $campos_pffs[0]['numero_conta'];
                $banco                  = $campos_pffs[0]['banco'];
                $agencia                = $campos_pffs[0]['agencia'];
                $num_cc                 = $campos_pffs[0]['num_cc'];
                $correntista            = $campos_pffs[0]['correntista'];
//Verifico se a Data de Vencimento da Conta irá cair em um Fim de Semana ...
                $dia_vencimento         = substr($data_proximo_vencimento, 8, 2);
                $mes_vencimento         = substr($data_proximo_vencimento, 5, 2);
                $ano_vencimento         = substr($data_proximo_vencimento, 0, 4);
                $dia_semana_q_ira_vencer= date('w', mktime(0, 0, 0, $mes_vencimento, $dia_vencimento, $ano_vencimento));

                if($qtde_parcelas > 1) {//Conta Automática do "Tipo Contrato" ...
                    //Se usar índice $i aqui, dá conflito pq utilizo essa function dentro de outro for ...
                    for($parcela = 0; $parcela < $qtde_parcelas; $parcela++) {
/*Posso gerar Contas Automáticas p/ qualquer Tipo de Conta, mas se o Tipo de Conta for "Desp. Bancárias", 
eu só posso gerar estas com vencimentos em que o Dia da Semana caiam de 2ª até 6ª somente ...*/
                        if(strpos($numero_conta, 'BANCARIA') == '' || (strpos($numero_conta, 'BANCARIA') > 0 && ($dia_semana_q_ira_vencer <> 6 && $dia_semana_q_ira_vencer <> 0))) {
                            $sql = "INSERT INTO `contas_apagares` (`id_conta_apagar`, `id_conta_apagar_automatica`, `id_funcionario`, `id_fornecedor`, `id_tipo_moeda`, `id_empresa`, `id_tipo_pagamento_recebimento`, `id_grupo`, `id_produto_financeiro`, `perc_uso_produto_financeiro`, `numero_conta`, `semana`, `previsao`, `data_emissao`, `data_vencimento`, `data_vencimento_alterada`, `valor`, `valor_pago`, `status`, `ativo`) 
                                    VALUES (NULL, '$id_conta_apagar_automatica', '144', '$id_fornecedor', '$id_tipo_moeda', '$id_empresa', '$id_tipo_pagamento_recebimento', '$id_grupo', '$id_produto_financeiro', '100', '".$numero_conta." ".($parcela + 1)."/".$qtde_parcelas."', '$semana', '$previsao', '$data_emissao_conta_apagar', '$data_proximo_vencimento', '$data_proximo_vencimento', '$valor', '0.00', '0', '1') ";
                            bancos::sql($sql);
                            $id_conta_apagar_new = bancos::id_registro();

                            $sql = "INSERT INTO `contas_apagares_vs_pffs` (`id_conta_apagar_vs_pff`, `id_conta_apagar`, `banco`, `agencia`, `num_cc`, `correntista`, `ativo`) 
                                    VALUES (NULL, '$id_conta_apagar_new', '$banco', '$agencia', '$num_cc', '$correntista', '1') ";
                            bancos::sql($sql);
                            
                            //Registrando Follow-UP(s) ...
                            if(!empty($observacao)) {
                                $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_fornecedor`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_fornecedor', '$_SESSION[id_funcionario]', '$id_conta_apagar_new', '18', '".$observacao." - Parcela N.º ".($parcela + 1)." de ".$qtde_parcelas."', '".date('Y-m-d H:i:s')."') ";
                                bancos::sql($sql);
                            }
                        }
                        if($tipo_data == 0) {//Quando é Fixa, é sempre no mesmo dia do mês ...
                            /*****************************************************************/
                            //Gerando a Próxima Data de Vencimento ...
                            $data_vencimento_dia    = substr($data_proximo_vencimento, 8, 2);
                            $data_vencimento_mes    = substr($data_proximo_vencimento, 5, 2) + 1;//Preparo o Novo Mês ...
                            $data_vencimento_ano    = substr($data_proximo_vencimento, 0, 4);

                            if($data_vencimento_mes > 12) {//Virou o Ano ...
                                $data_vencimento_mes = 1;//Volta p/ o mês de Janeiro ...
                                $data_vencimento_ano+= 1;//Adiciona um novo Ano ...
                            }
                            if($data_vencimento_mes < 10) $data_vencimento_mes = '0'.$data_vencimento_mes;
                            $resposta_data_valida   =  checkdate($data_vencimento_mes, $dia_vencimento, $data_vencimento_ano);

                            if($resposta_data_valida == 1) {//Data Válida ...
                               $data_proximo_vencimento = $dia_vencimento.'/'.$data_vencimento_mes.'/'.$data_vencimento_ano;
                            }else {//Data Inválida, por exemplo: 30 de fevereiro ... rs
                                if($data_vencimento_mes == '02') {//Somente em Fevereiro, farei essa verificação ...
                                    /*Se o ANO da qual vai vencer a Conta for 'Bissexto' então o sistema coloca 
                                    o dia como sendo 29, do contrário 28 que é o normal ...*/
                                    $dia_vencimento_fevereiro   = ($data_vencimento_ano % 4 == 0) ? 29 : 28;
                                    $data_proximo_vencimento    = $dia_vencimento_fevereiro.'/'.$data_vencimento_mes.'/'.$data_vencimento_ano;
                                }else {
                                    $data_proximo_vencimento    = $dia_vencimento.'/'.$data_vencimento_mes.'/'.$data_vencimento_ano;
                                }
                            }
                            /*****************************************************************/
                        }else {//Quando tera o intervalo
                            $data_proximo_vencimento    = data::datetodata($data_proximo_vencimento, '/');
                            $data_proximo_vencimento    = data::adicionar_data_hora($data_proximo_vencimento, $intervalo);
                        }
                        $data_proximo_vencimento        = data::datatodate($data_proximo_vencimento, '-');
                    }
                    //Neutralizo a Conta para que não seja gerada outras parcelas ...
                    $sql = "UPDATE `contas_apagares_automaticas` SET `conta_ativa` = 'N' WHERE `id_conta_apagar_automatica` = '$id_conta_apagar_automatica' LIMIT 1 ";
                    bancos::sql($sql);
                }else {//Conta Automática do "Tipo NÃO Contrato" ...
/*Se o Grupo da Conta à Pagar for Imposto e cair na no fim de Semana, antecipo essa p/ que caia 
na Sexta-Feira ...*/
                    if($id_grupo == 33 && ($dia_semana_q_ira_vencer == 6 || $dia_semana_q_ira_vencer == 0)) {
                        //Se a Data de Vencimento da Nova Conta à Pagar que será gerada caiu no ...
                        $qtde_dias_para_retroceder              = ($dia_semana_q_ira_vencer == 6) ? 1 : 2;//Sábado ou Domingo ...
                        $data_proximo_vencimento_retrocedida    = data::adicionar_data_hora(data::datetodata($data_proximo_vencimento, '/'), -$qtde_dias_para_retroceder);
                        $data_proximo_vencimento_retrocedida    = data::datatodate($data_proximo_vencimento_retrocedida, '-');
                    }
                    $data_proximo_vencimento_gravar = (!empty($data_proximo_vencimento_retrocedida)) ? $data_proximo_vencimento_retrocedida : $data_proximo_vencimento;
/*Posso gerar Contas Automáticas p/ qualquer Tipo de Conta, mas se o Tipo de Conta for "Desp. Bancárias", 
eu só posso gerar estas com vencimentos em que o Dia da Semana caiam de 2ª até 6ª somente ...*/
                    if(strpos($numero_conta, 'BANCARIA') == '' || (strpos($numero_conta, 'BANCARIA') > 0 && ($dia_semana_q_ira_vencer <> 6 && $dia_semana_q_ira_vencer <> 0))) {
                        $sql = "INSERT INTO `contas_apagares` (`id_conta_apagar`, `id_conta_apagar_automatica`, `id_funcionario`, `id_fornecedor`, `id_tipo_moeda`, `id_empresa`, `id_tipo_pagamento_recebimento`, `id_grupo`, `id_produto_financeiro`, `perc_uso_produto_financeiro`, `numero_conta`, `semana`, `previsao`, `data_emissao`, `data_vencimento`, `data_vencimento_alterada`, `valor`, `valor_pago`, `status`, `ativo`) 
                                VALUES (NULL, '$id_conta_apagar_automatica', '$_SESSION[id_funcionario]', '$id_fornecedor', '$id_tipo_moeda', '$id_empresa', '$id_tipo_pagamento_recebimento', '$id_grupo', '$id_produto_financeiro', '100', '$numero_conta', '$semana', '$previsao', '$data_emissao_conta_apagar', '$data_proximo_vencimento_gravar', '$data_proximo_vencimento_gravar', '$valor', '0.00', '0', '1') ";
                        bancos::sql($sql);
                        $id_conta_apagar_new = bancos::id_registro();

                        $sql = "INSERT INTO `contas_apagares_vs_pffs` (`id_conta_apagar_vs_pff`, `id_conta_apagar`, `banco`, `agencia`, `num_cc`, `correntista`, `ativo`) 
                                VALUES (NULL, '$id_conta_apagar_new', '$banco', '$agencia', '$num_cc', '$correntista', '1') ";
                        bancos::sql($sql);
                        
                        //Registrando Follow-UP(s) ...
                        if(!empty($observacao)) {
                            $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_fornecedor`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_fornecedor', '$_SESSION[id_funcionario]', '$id_conta_apagar_new', '18', '$observacao', '".date('Y-m-d H:i:s')."') ";
                            bancos::sql($sql);
                        }
                    }
                    if($tipo_data == 0) {//Quando é Fixa, é sempre no mesmo dia do mês ...
                        $data_vencimento_dia	= substr($data_proximo_vencimento, 8, 2);
                        $data_vencimento_mes	= substr($data_proximo_vencimento, 5, 2);
                        $data_vencimento_ano	= substr($data_proximo_vencimento, 0, 4);
                        $data_proximo_vencimento    = date('d/m/Y', mktime (0,0,0, $data_vencimento_mes + 1, $data_vencimento_dia, $data_vencimento_ano));
                    }else {//Quando tem Intervalo ...
                        $data_proximo_vencimento    = data::datetodata($data_proximo_vencimento, '-');
                        $data_proximo_vencimento    = data::adicionar_data_hora($data_proximo_vencimento, $intervalo);
                    }
                    $data_proximo_vencimento    = data::datatodate($data_proximo_vencimento, '-');

                    //Simplesmente altero os campos de Data p/ as "Datas Novas" ...
                    $sql = "UPDATE `contas_apagares_automaticas` SET `id_funcionario` = '144', `data_vencimento` = '$data_proximo_vencimento', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_conta_apagar_automatica` = '$id_conta_apagar_automatica' LIMIT 1 ";
                    bancos::sql($sql);
                }
            }
        }
    }

    //Não me lembro o porque dessa função - Dárcio 06/08/2014 ...
    function atualiza_valores_contas_automaticas($id_conta_apagar_automatica, $valor, $valor_reajustado) {//quando pagar a conta atualizar a mesma no valor da contas_automaticas
        $sql = "UPDATE `contas_apagares_automaticas` SET `valor` = '$valor', `valor_reajustado` = '$valor_reajustado' WHERE `id_conta_apagar_automatica` = '$id_conta_apagar_automatica' LIMIT 1 ";
        bancos::sql($sql);
    }

    function cheque_devolvido($id_cheque_cliente) {//Isto ocorre quando o cheque é devolvido ...
        //Muda o Status do Cheque do Cliente p/ devolvido ...
        $sql = "UPDATE `cheques_clientes` SET `status` = '3' WHERE `id_cheque_cliente` = '$id_cheque_cliente' LIMIT 1 ";
        bancos::sql($sql);
        //Localizo todas as Duplicatas que foram recebidas com o Tal Cheque do Cliente que foi devolvido ...
        $sql = "SELECT cr.`id_conta_receber`, crq.`id_conta_receber_quitacao`, crq.`valor` 
                FROM `cheques_clientes` cc 
                INNER JOIN `contas_receberes_quitacoes` crq ON crq.`id_cheque_cliente` = cc.`id_cheque_cliente` 
                INNER JOIN `contas_receberes` cr ON cr.`id_conta_receber` = crq.`id_conta_receber` 
                WHERE cc.`id_cheque_cliente` = '$id_cheque_cliente' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
            $valor_cheque_conta = $campos[$i]['valor'];//Valor do cheque p/ cada conta ...

            //Zero o valor do registro na tabela de "Recebimentos" porque não recebemos esse Valor ...
            $sql = "UPDATE `contas_receberes_quitacoes` SET `valor` = '0' WHERE `id_conta_receber_quitacao` = '".$campos[$i]['id_conta_receber_quitacao']."' LIMIT 1 ";
            bancos::sql($sql);

            /*Em cima das Duplicatas encontradas, eu abato esse Valor de cheque e mudo o seu status 
            p/ parcialmente recebida ...*/
            $sql = "UPDATE `contas_receberes` SET `valor_pago` = `valor_pago` - '$valor_cheque_conta', `status` = '1', `descricao_conta` = 'CHEQUE DEVOLVIDO' WHERE `id_conta_receber` = '".$campos[$i]['id_conta_receber']."' LIMIT 1 ";
            bancos::sql($sql);
        }
    }

    function estorno_conta_recebida($id_conta_receber_quitacao) { //Isto ocorre quando o cheque é devolvido
        $sql = "SELECT id_conta_receber, id_cheque_cliente, valor 
                FROM `contas_receberes_quitacoes` 
                WHERE `id_conta_receber_quitacao` = '$id_conta_receber_quitacao' LIMIT 1 ";
        $campos                 = bancos::sql($sql);
        $id_conta_receber       = $campos[0]['id_conta_receber'];
        $id_cheque_cliente      = $campos[0]['id_cheque_cliente'];
        $valor_conta_receber    = $campos[0]['valor'];
        $valor_conta            = $valor_conta_receber;

        if($id_cheque_cliente > 0) {//Se o recebimento foi feito com cheque ...
            $sql = "UPDATE `cheques_clientes` SET `valor_disponivel` = `valor_disponivel` + $valor_conta, `status_disponivel` = '1' WHERE `id_cheque_cliente` = '$id_cheque_cliente' LIMIT 1 ";
            bancos::sql($sql);
        }
        $sql = "DELETE FROM `contas_receberes_quitacoes` WHERE `id_conta_receber_quitacao` = '$id_conta_receber_quitacao' ";
        bancos::sql($sql);
        $sql = "Select count(id_conta_receber_quitacao) as qtde 
                from contas_receberes_quitacoes 
                where id_conta_receber='$id_conta_receber' ";
        $campos_qtde = bancos::sql($sql);
        $status = ($campos_qtde[0]['qtde'] > 0) ? 1 : 0;
        $sql = "UPDATE `contas_receberes` SET valor_pago = valor_pago - $valor_conta, status = '$status' where id_conta_receber = '$id_conta_receber' LIMIT 1 ";
        bancos::sql($sql);
        return array('status' => $status, 'id_conta_receber' => $id_conta_receber);
    }

    function estorno_conta_paga($id_conta_apagar_quitacao) {
        $sql = "SELECT ca.`id_tipo_moeda`, caq.`id_conta_apagar`, caq.`id_cheque`, caq.`valor`, caq.`valor_moeda_dia` 
                FROM `contas_apagares_quitacoes` caq 
                INNER JOIN `contas_apagares` ca ON ca.`id_conta_apagar` = caq.`id_conta_apagar` 
                WHERE caq.`id_conta_apagar_quitacao` = '$id_conta_apagar_quitacao' LIMIT 1 ";
        $campos             = bancos::sql($sql);
        $id_tipo_moeda      = $campos[0]['id_tipo_moeda'];
        $id_conta_apagar    = $campos[0]['id_conta_apagar'];
        $id_cheque          = $campos[0]['id_cheque'];
        $valor_pago         = $campos[0]['valor'];//Esse valor pago está em R$, pois sempre guardamos em R$ o Valor Pago ...
        $valor_moeda_dia    = $campos[0]['valor_moeda_dia'];
        /*********************************************************************/
        /*************************Controle com Cheque*************************/
        /*********************************************************************/
        if($id_cheque > 0) {//Se a Quitação que está sendo estornada, foi paga mediante a cheque então ...
            //Verifico aqui quantas Contas que foram pagas com o mesmo Cheque ...
            $sql = "SELECT id_conta_apagar_quitacao  
                    FROM `contas_apagares_quitacoes` 
                    WHERE `id_cheque` = '$id_cheque' ";
            $campos_cheques = bancos::sql($sql);
            if(count($campos_cheques) > 1) {//2 ou + Quitações Contas, representa q este cheque tb é de outras contas ...
                $sql = "UPDATE `cheques` SET `valor` = `valor` - $valor_pago WHERE `id_cheque` = '$id_cheque' LIMIT 1 ";
                bancos::sql($sql);
            }else if(count($campos_cheques) == 1) {//Posso zerar o cheque, pois ele é unico p/ uma só Conta ...
                $sql = "UPDATE `cheques` SET `status` = '0', valor = '0.00', historico = '' WHERE `id_cheque` = '$id_cheque' LIMIT 1 ";
                bancos::sql($sql);
            }
        }
        /*********************************************************************/
        //Apago o Registro de Quitação ...
        $sql = "DELETE FROM `contas_apagares_quitacoes` WHERE `id_conta_apagar_quitacao` = '$id_conta_apagar_quitacao' LIMIT 1 ";
        bancos::sql($sql);

        $sql = "SELECT COUNT(id_conta_apagar_quitacao) AS qtde 
                FROM `contas_apagares_quitacoes` 
                WHERE `id_conta_apagar` = '$id_conta_apagar' ";
        $campos_qtde    = bancos::sql($sql);
        $status         = ($campos_qtde[0]['qtde'] > 0) ? 1 : 0;
        //Verifico se a Conta é de Importação e o Quanto que foi pago desta Conta ...
        $sql = "SELECT id_tipo_moeda, valor_pago 
                FROM `contas_apagares` 
                WHERE `id_conta_apagar` = '$id_conta_apagar' LIMIT 1 ";
        $campos_pago = bancos::sql($sql);
        if($campos_pago[0]['id_tipo_moeda'] > 1) {//Significa que é de Importação ...
            if($campos_pago[0]['valor_pago'] - $valor_pago < 0) {
                $sql = "UPDATE `contas_apagares` SET `valor_pago` = '0', `predatado` = '0', `status` = '$status' WHERE `id_conta_apagar` = '$id_conta_apagar' LIMIT 1 ";
            }else {
                /*Esse controle só será feito quando a Conta for do Tipo moeda Estrangeira, pq o campo valor_pago 
                da Conta à Pagar, sempre guardo o que foi pago no Tipo da Moeda da Conta ...*/
                if($id_tipo_moeda > 1) $valor_pago/= $valor_moeda_dia;
                $sql = "UPDATE `contas_apagares` SET `valor_pago` = `valor_pago` - $valor_pago, `predatado` = '0', `status` = '$status' WHERE `id_conta_apagar` = '$id_conta_apagar' LIMIT 1 ";
            }
        }else {
            $sql = "UPDATE `contas_apagares` SET `valor_pago` = `valor_pago` - $valor_pago, `predatado` = '0', `status` = '$status' WHERE `id_conta_apagar` = '$id_conta_apagar' LIMIT 1 ";
        }
        bancos::sql($sql);
        return array('status' => $status, 'id_conta_apagar' => $id_conta_apagar);
    }

/*********Função que retorna a qtde e ids das contas à pagar ou à receber conforme o parâmetro**********/
//De qual empresa que eu quero ver as contas daquele Cliente -> Albafer, Grupo ou Tool Master

/*
Descrição de alguns parâmetros abaixo:

$onde_buscar_dados = 1 -> Tabela de Clientes
$onde_buscar_dados = 2 -> Tabela de Fornecedores
$onde_buscar_dados = 3 -> Tabela de Representantes

$tipo_retorno = 1 -> Contas à Pagar
$tipo_retorno = 2 -> Contas à Receber

Esse 6º parâmetro só será $trazer_livre_debito = 'S' quando vier pelo Controle de Pagamento que fica no Contas à Pagar do Financeiro ...*/
    function contas_em_aberto($id, $onde_buscar_dados, $id_empresa, $tipo_retorno = 1, $id_conta_apagar_automatica = 0, $trazer_livre_debito = 'N') {
        if($id_conta_apagar_automatica == 0) {//Aqui significa que desejo verificar a situação geral das Contas do Financeiro ...
            //Busca de alguns campos que serão utilizados mais abaixo ...
            if($onde_buscar_dados == 1) {//Busca dados na tabela de Clientes ...
                $sql = "SELECT `id_pais`, `razaosocial`, `cnpj_cpf` 
                        FROM `clientes` 
                        WHERE `id_cliente` = '$id' LIMIT 1 ";
                $campos_cliente = bancos::sql($sql);
                $id_pais        = $campos_cliente[0]['id_pais'];
                $razaosocial    = $campos_cliente[0]['razaosocial'];
                $cnpj_cpf       = $campos_cliente[0]['cnpj_cpf'];
            }else if($onde_buscar_dados == 2) {//Busca dados na tabela de Fornecedores ...
                $sql = "SELECT `id_pais`, `razaosocial`, `cnpj_cpf` 
                        FROM `fornecedores` 
                        WHERE `id_fornecedor` = '$id' LIMIT 1 ";
                $campos_fornecedor  = bancos::sql($sql);
                $id_pais            = $campos_fornecedor[0]['id_pais'];
                $razaosocial        = $campos_fornecedor[0]['razaosocial'];
                $cnpj_cpf           = $campos_fornecedor[0]['cnpj_cpf'];
            }else if($onde_buscar_dados == 3) {//Busca dados na tabela de Representantes ...
                $sql = "SELECT `id_pais`, `nome_fantasia`, `cnpj_cpf` 
                        FROM `representantes` 
                        WHERE `id_representante` = '$id' LIMIT 1 ";
                $campos_representante   = bancos::sql($sql);
                $id_pais                = $campos_representante[0]['id_pais'];
                $razaosocial            = $campos_representante[0]['nome_fantasia'];
                $cnpj_cpf               = $campos_representante[0]['cnpj_cpf'];
            }
            //Tratamento com a variável p/ não furar o SQL ...
            if(empty($id_empresa)) $id_empresa = '%';

            if($id_pais == 31) {//País Nacional "Brasil" ...
                if(strlen($cnpj_cpf) == 14 || strlen($cnpj_cpf) == 11) {//CNPJ ou CPF ...
                    $condicao_contas_apagares 	= " AND f.`cnpj_cpf` = '$cnpj_cpf' ";
                    $condicao_contas_receberes 	= " AND c.`cnpj_cpf` = '$cnpj_cpf' ";
                }else {
                    $condicao_contas_apagares 	= " AND f.`razaosocial` = '$razaosocial' ";
                    $condicao_contas_receberes 	= " AND c.`razaosocial` = '$razaosocial' ";
                }
            }else {//País Estrangeiro ...
                $condicao_contas_apagares 	= " AND f.`razaosocial` = '$razaosocial' ";
                $condicao_contas_receberes 	= " AND c.`razaosocial` = '$razaosocial' ";
            }
            
            if($tipo_retorno == 1) {//Caminho de Contas à Pagar ...
                $sql = "SELECT ca.`id_conta_apagar` 
                        FROM `contas_apagares` ca 
                        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = ca.`id_fornecedor` $condicao_contas_apagares 
                        WHERE ca.`id_empresa` LIKE '$id_empresa' 
                        AND ca.`ativo` = '1' 
                        AND ca.`status` < '2' ORDER BY `data_vencimento` ";
                $campos = bancos::sql($sql);
                $linhas = count($campos);
                for($i = 0; $i < $linhas; $i++) $id_contas_apagar.= $campos[$i]['id_conta_apagar'].',';
                if($linhas == 0) {
                    return 0;
                }else {
                    $id_contas_apagar 	= substr($id_contas_apagar, 0, strlen($id_contas_apagar) - 1);
                    $vetor_contas       = explode(',', $id_contas_apagar);
                    return array('id_contas' => $vetor_contas);
                }
            }else if($tipo_retorno == 2) {//Caminho de Contas à Receber ...
                /*Esse geralmente é o Padrão do sistema inteiro, nunca trazer as NFs que são livre de Débito, somente quando vier pelo 
                Controle de Pagamento que fica no Contas à Pagar do Financeiro que terá de trazer estas também além das normais ...*/
                if($trazer_livre_debito == 'N') $condicao_trazer_livre_debito = " AND nfs.`livre_debito` = 'N' ";
///////////////////////////////////Estrutura dos SQL(s) do Union///////////////////////////////////
//1) Lista todas as Contas que estão com o Status de em Aberto para receber do Cliente - Modo Antigo ...
//2) Aqui busca as contas que são importadas diretamente do faturamento e que não sejam "Livre de Débito" ...
                $sql = "(SELECT DISTINCT(cr.`id_conta_receber`), `data_vencimento_alterada` /*Tenho que trazer esse campo para fazer o ORDER BY ...*/
                        FROM `contas_receberes` cr 
                        INNER JOIN `clientes` c ON c.`id_cliente` = cr.`id_cliente` $condicao_contas_receberes 
                        WHERE cr.`id_empresa` LIKE '$id_empresa' 
                        AND cr.`id_nf` IS NULL 
                        AND cr.`status` < '2' 
                        AND cr.`ativo` = '1') 
                        UNION 
                        (SELECT DISTINCT(cr.`id_conta_receber`), `data_vencimento_alterada` /*Tenho que trazer esse campo para fazer o ORDER BY ...*/
                        FROM `contas_receberes` cr 
                        INNER JOIN `nfs` ON nfs.`id_nf` = cr.`id_nf` $condicao_trazer_livre_debito 
                        INNER JOIN `clientes` c ON c.`id_cliente` = cr.`id_cliente` $condicao_contas_receberes 
                        WHERE cr.`id_empresa` LIKE '$id_empresa' 
                        AND cr.`status` < '2' 
                        AND cr.`ativo` = '1') ORDER BY `data_vencimento_alterada` ";
                $campos = bancos::sql($sql);
                $linhas = count($campos);
                for($i = 0; $i < $linhas; $i++) $id_contas_receber.= $campos[$i]['id_conta_receber'].',';
                if($linhas == 0) {
                    return 0;
                }else {
                    $id_contas_receber  = substr($id_contas_receber, 0, strlen($id_contas_receber) - 1);
                    $vetor_contas       = explode(',', $id_contas_receber);
                    return array('id_contas' => $vetor_contas);
                }
            }
        }else {//Só me interessa verificar as Contas Apagares que são Automáticas ...
            $sql = "SELECT ca.`id_conta_apagar` 
                    FROM `contas_apagares` ca 
                    WHERE ca.`id_conta_apagar_automatica` = '$id_conta_apagar_automatica' 
                    AND ca.`ativo` = '1' ORDER BY `data_vencimento` ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
            for($i = 0; $i < $linhas; $i++) $id_contas_apagar.= $campos[$i]['id_conta_apagar'].',';
            if($linhas == 0) {
                return 0;
            }else {
                $id_contas_apagar 	= substr($id_contas_apagar, 0, strlen($id_contas_apagar) - 1);
                $vetor_contas 		= explode(',', $id_contas_apagar);
                return array('id_contas' => $vetor_contas);
            }
        }
    }
    
    //Nessa função eu cálculo tanto os valores Extras como os Valores Reajustados das Contas à Pagar ...
    function calculos_conta_pagar($id_conta_apagar) {
        if(!class_exists('genericas'))    require 'genericas.php';//Caso exista eu desvio a classe 
        //Busca do último valor do dólar e do euro ...
        $valor_dolar    = genericas::moeda_dia('dolar');
        $valor_euro     = genericas::moeda_dia('euro');
        //Busca os dados da Conta à Receber ...
        $sql = "SELECT ca.`id_tipo_moeda`, ca.`data_vencimento`, ca.`valor`, ca.`multa`, ca.`taxa_juros`, 
                ca.`tipo_juros`, ca.`valor_pago`, CONCAT(tm.`simbolo`, '&nbsp;') AS simbolo 
                FROM `contas_apagares` ca 
                INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = ca.id_tipo_moeda 
                WHERE ca.`id_conta_apagar` = '$id_conta_apagar' LIMIT 1 ";
        $campos             = bancos::sql($sql);
        $valor_conta        = $campos[0]['valor'];
        $multa              = $campos[0]['multa'];
        $valor_pago         = $campos[0]['valor_pago'];
        
        //Independente do caso, descubro o Valor da Conta em Reais ...
        if($campos[0]['id_tipo_moeda'] == 2) {//Conta em Dólar ...
            $valor_em_reais = $valor_conta * $valor_dolar;
        }else if($campos[0]['id_tipo_moeda'] == 3) {//Conta em Euro ...
            $valor_em_reais = $valor_conta * $valor_euro;
        }else {//Conta em R$ ...
            $valor_em_reais = $valor_conta;//Nesse caso é o Valor da Própria Conta ...
        }
        /********************************************************************************************/
        $taxa_juros         = $campos[0]['taxa_juros'];
        $data_vencimento    = $campos[0]['data_vencimento'];
        $data_atual         = date('Y-m-d');
        
        /***************
        
        1) UPDATE no campo Data Vencimento Alterada = Data Vencimento Inicial ...
         
         1.a) UPDATE `contas_apagares` SET data_vencimento_alterada = data_vencimento ... ok, rodar no ar agora ...
         
         1.b) Uma Conta à Pagar tem uma NFe, uma NFe pode ter N vencimentos "Financiamentos" ...
         
         Data de Vencimento, Valor Inicial da Parcela ...
         
        IF(DAta Inicial = DAta Alterada) {
            calculo igual ao que está hoje ...
        }else {
            Data Alterada - Data Inicial p/ calcular os juros ...
        }

        3) Data de Vencimento Alterada = Data de Vencimento - Inclusão INSERT nos 2 campos ... ok

        4) Data Prorrogada não pode ser menor que a Data de Vencimento ... ok
         
        5) Pagamento -> Colocar Checkbox Zerar Juros -> Zera Multa + Juros, resumindo Valor Inicial ... ok
        
        6) Renomear os Campos VAlores Reajustos que pela teoria hoje não tem sentido ... ok
         
        */
        
        //A variável dias equivale a data atual até a data de vecimento ...
        $dias = data::diferenca_data($data_vencimento, $data_atual);
        if($dias[0] < 0) $dias[0] = 0;
        
        if($taxa_juros > 0) {
            if($campos[0]['tipo_juros'] == 'S') {//Juros Simples ...
                //A variável dias equivale a data atual até a data de vecimento ...
                $fator_taxa_juros_dias_venc = ($taxa_juros / 30 * $dias[0] / 100) + 1;
            }else {//Juros Composto ...
                $fator_taxa_juros_diaria    = pow(1 + $taxa_juros / 100, (1 / 30));
                $fator_taxa_juros_dias_venc = pow($fator_taxa_juros_diaria, $dias[0]);
            }
            //Aqui eu só pego exatamente 2 casas do valor da conta, simplesmente trunco, não me interessa arredondar ...
            $valor_juros                    = round($valor_em_reais * ($fator_taxa_juros_dias_venc - 1), 2);
            /*Essa variável se comporta exatamente no Tipo da Moeda da Conta "R$", "U$", Euro ... 
            irei utilizá-la apenas na Hora do Recebimento da mesma ...*/
            $valor_juros_moeda_conta        = round($valor_conta * ($fator_taxa_juros_dias_venc - 1), 2);
        }else {
            $valor_juros                    = 0;
            $valor_juros_moeda_conta        = 0;
        }
        
        $valor_reajustado               = round($valor_em_reais + $multa + $valor_juros, 2);//Sempre em R$ ...
        $valor_reajustado_moeda_conta   = round($valor_conta + $multa + $valor_juros_moeda_conta, 2);

        $valores_extra                  = round($valor_juros + $multa, 2);

        return array('valor' => $valor_conta, 'valor_pago' => $valor_pago, 'valores_extra'=> $valores_extra, 'valor_reajustado'=> $valor_reajustado, 'valor_reajustado_moeda_conta' => $valor_reajustado_moeda_conta, 'valor_juros'=> $valor_juros);
    }

//Nessa função eu cálculo tanto os valores Extras como os Valores Reajustados das Contas à Receber ...
    function calculos_conta_receber($id_conta_receber) {
        if(!class_exists('genericas'))    require 'genericas.php';//Caso exista eu desvio a classe 
        //Busca do último valor do dólar e do euro ...
        $valor_dolar    = genericas::moeda_dia('dolar');
        $valor_euro     = genericas::moeda_dia('euro');
        //Busca os dados da Conta à Receber ...
        $sql = "SELECT cr.*, CONCAT(tm.simbolo, '&nbsp;') AS simbolo 
                FROM `contas_receberes` cr 
                INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = cr.id_tipo_moeda 
                WHERE cr.`id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
        $campos             = bancos::sql($sql);
        $manual             = $campos[0]['manual'];
        //Aqui essas variáveis são para o cálculo da fórmula do Roberto ...
        $valor_conta        = $campos[0]['valor'];
        $valor_desconto     = $campos[0]['valor_desconto'];
        /********************************************************************************************/
        //Verifico se tenho alguma NF de Devolução importada p/ essa Duplicata da Função ...
        $sql = "SELECT SUM(valor_devolucao) AS total_devolucao_importada 
                FROM `contas_receberes_vs_nfs_devolucoes` 
                WHERE `id_conta_receber` = '$id_conta_receber' ";
        $campos_devolucao_importada = bancos::sql($sql);
        /********************************************************************************************/
        $valor_abatimento           = $campos[0]['valor_abatimento'] + $campos_devolucao_importada[0]['total_devolucao_importada'];
        $valor_despesas             = $campos[0]['valor_despesas'];
        $taxa_juros                 = $campos[0]['taxa_juros'];
        $valor_juros                = $campos[0]['valor_juros'];
        $data_vencimento_alterada   = $campos[0]['data_vencimento_alterada'];
        $data_atual                 = date('Y-m-d');
       //Essa variável será utilizada + abaixo em alguns cálculos, principalmente p/ o valor do Juros ...
        $valor_liq_conta    = ($valor_conta - $valor_desconto - $valor_abatimento);
/*Significa que aqui não cálcula a taxa de juros, e sim só puxa o valor direto da base com a qual foi o que o usuário 
digitou anteriormente ...*/
        if($manual == 1) {
            $valor_reajustado = ($valor_liq_conta + $valor_despesas) + ($valor_juros);
        }else {
            if($taxa_juros > 0) {
                //A variável dias equivale a data atual até a data de vecimento ...
                $dias   = data::diferenca_data($data_vencimento_alterada, $data_atual);
                if($dias[0] < 0) $dias[0] = 0;
                $taxa_juros_dias_venc = ($taxa_juros / 100) / 30 * $dias[0];
            }else {
                $taxa_juros_dias_venc = 0;
            }
            $valor_juros        =   $valor_liq_conta * $taxa_juros_dias_venc;
            //As despesas é o único campo que não pode levar em conta os Juros ...
            $valor_reajustado   =   $valor_liq_conta * ($taxa_juros_dias_venc + 1) + $valor_despesas;
        }
        
        /*Nunca o Juros pode ser negativo porque significa que nós daqui da Albafer ainda 
        estamos devendo juros p/ o Cliente ...*/
        if($valor_juros < 0) $valor_juros = 0;
        
        //Aqui eu só pego exatamente 2 casas do valor da conta, simplesmente trunco, não me interessa arredondar ...
        $valor_juros        = round(round($valor_juros, 3), 2);
        $valor_reajustado   = round(round($valor_reajustado, 3), 2);
        $valores_extra      = $valor_juros + $valor_despesas - $valor_desconto - $valor_abatimento;
        
        //Aqui eu só pego exatamente 2 casas do valor da conta, simplesmente trunco, não me interessa arredondar ...
        $valores_extra      = round(round($valores_extra, 3), 2);
        $valor_reajustado-= $campos[0]['valor_pago'];

        if($campos[0]['id_tipo_moeda'] == 2) {//Dólar
            $valor_juros*= $valor_dolar;
            $valor_reajustado*= $valor_dolar;
            $valores_extra*= $valor_dolar;
        }else if($campos[0]['id_tipo_moeda'] == 3) {//Euro
            $valor_juros*= $valor_euro;
            $valor_reajustado*= $valor_euro;
            $valores_extra*= $valor_euro;
        }
        $valor_juros        = round(round($valor_juros, 3), 2);
        $valor_reajustado   = round(round($valor_reajustado, 3), 2);
        $valores_extra      = round(round($valores_extra, 3), 2);
        return array('valores_extra' => $valores_extra, 'valor_reajustado' => $valor_reajustado, 'valor_juros' => $valor_juros);
    }
	
    function nome_cliente_conta_receber($id_conta_receber) {
        $sql = "SELECT c.`id_cliente`, c.`razaosocial`, c.`credito` 
                FROM `contas_receberes` cr 
                LEFT JOIN `nfs` ON nfs.`id_nf` = cr.`id_nf` 
                INNER JOIN `clientes` c on c.`id_cliente` = cr.`id_cliente` 
                WHERE cr.`id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
        $campos_nf_geral = bancos::sql($sql);
        return array('id_cliente' => $campos_nf_geral[0]['id_cliente'], 'cliente' => $campos_nf_geral[0]['razaosocial'], 'credito' => $campos_nf_geral[0]['credito']);
    }
    
    /*Função que guarda os dados bancários do Fornecedor da Negociação na tabela relacional 
    do "$id_conta_apagar" passado por parâmetro ...*/
    function inserir_dados_bancarios($id, $acao, $id_conta_apagar) {
        //Seleção dos dados bancários do fornecedor para gravar na tabela de contas_apagares_vs_pffs para ficar + fácil a busca dos dados ...
        if($acao == 1) {//Significa que essa função foi chamada pelo Caminho de NFe ...
            $sql = "SELECT fp.`banco`, fp.`agencia`, fp.`num_cc`, fp.`correntista`, fp.`cnpj_cpf` 
                    FROM `nfe` 
                    INNER JOIN `fornecedores_propriedades` fp ON fp.`id_fornecedor_propriedade` = nfe.`id_fornecedor_propriedade` 
                    WHERE nfe.`id_nfe` = '$id' LIMIT 1 ";
        }else if($acao == 2) {//Significa que essa função foi chamada pelo Caminho de Antecipação ...
            $sql = "SELECT fp.`banco`, fp.`agencia`, fp.`num_cc`, fp.`correntista`, fp.`cnpj_cpf` 
                    FROM `antecipacoes` a 
                    INNER JOIN `fornecedores_propriedades` fp ON fp.`id_fornecedor_propriedade` = a.`id_fornecedor_propriedade` 
                    WHERE a.`id_antecipacao` = '$id' LIMIT 1 ";
        }
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {
            $sql = "INSERT INTO `contas_apagares_vs_pffs` (`id_conta_apagar_vs_pff`, `id_conta_apagar`, `banco`, `agencia`, `num_cc`, `correntista`) VALUES (NULL, '$id_conta_apagar', '".$campos[0]['banco']."', '".$campos[0]['agencia']."', '".$campos[0]['num_cc']."', '".$campos[0]['correntista']."') ";
            bancos::sql($sql);
        }
    }
    
    /***************************************************************************
    Parâmetros ...

    Tipo Conta = 'A' => 'Contas à Pagar' ...
    Tipo Conta = 'R' => 'Contas à Receber' ...*/
    function atualizar_data_alterada($id, $tipo_conta) {
        if($tipo_conta == 'A') {//Conta à Pagar ...
            $sql = "SELECT `id_fornecedor`, `numero_conta`, `data_vencimento_alterada` 
                    FROM `contas_apagares` 
                    WHERE `id_conta_apagar` = '$id' LIMIT 1 ";
            $campos = bancos::sql($sql);
            
            /*Fornecedor "SECRETARIA DA RECEITA FEDERAL - 899" ou "CAIXA ECONOMICA FEDERAL 942 - com a Conta FGTS" tem que pagar 
            no dia útil anterior "Se ??? então ???" ...*/
            if($campos[0]['id_fornecedor'] == 899 || ($campos[0]['id_fornecedor'] == 942 && $campos[0]['numero_conta'] == 'FGTS')) {
                $fornecedores_clientes_especiais = 'S';
            }else {//Adiciono mais 1 dia ... Porque esta só será paga no próximo dia útil "???" ...
                $fornecedores_clientes_especiais = 'N';
            }
            //Na primeira vez que eu chamo essa função, eu passo o 4º parâmetro índice como sendo 0 ...
            self::nova_data_vencimento_alterada($campos[0]['data_vencimento_alterada'], $fornecedores_clientes_especiais, 0);
            
            //Atualizo a tabela de Contas à Pagar com a nova $data_vencimento_alterada ...
            $sql = "UPDATE `contas_apagares` SET `data_vencimento_alterada` = '$GLOBALS[data_vencimento_alterada]' WHERE `id_conta_apagar` = '$id' LIMIT 1 ";
            
        }else if($tipo_conta == 'R') {//Conta à Receber ...
            $sql = "SELECT `id_tipo_recebimento`, `data_vencimento_alterada` 
                    FROM `contas_receberes` 
                    WHERE `id_conta_receber` = '$id' LIMIT 1 ";
            $campos = bancos::sql($sql);
            
            //Na primeira vez que eu chamo essa função, eu passo o 4º parâmetro índice como sendo 0 ...
            self::nova_data_recebimento($campos[0]['data_vencimento_alterada'], $campos[0]['id_tipo_recebimento'], 0);
            
            //Atualizo a tabela de Contas à Receber com a nova $data_recebimento ...
            $sql = "UPDATE `contas_receberes` SET `data_recebimento` = '$GLOBALS[data_recebimento]' WHERE `id_conta_receber` = '$id' LIMIT 1 ";
        }
        bancos::sql($sql);
    }
    
    /**********************************************************************/
    /*****************************Contas à Pagar***************************/
    /**********************************************************************/
    /*Esse parâmetro $data_pagamento, não é nada mais nada menos do que a $data_vencimento_alterada da Conta à Pagar; 
    data em que nós empresa tem que pagar o Fornecedor ...*/
    function nova_data_vencimento_alterada($data_pagamento, $fornecedores_clientes_especiais, $indice) {
        $data_pagamento_foi_alterada = 'NÃO';//A princípio = NÃO porque acabamos de entrar na função ...
        
        //1) Feriado ...
        //A primeira coisa que eu faço é verificar se essa "data_vencimento_alterada" cai em um feriado ...
        $sql = "SELECT `id_feriado` 
                FROM `feriados` 
                WHERE `data_feriado` = '$data_pagamento' LIMIT 1 ";
        $campos_feriado = bancos::sql($sql);
        if(count($campos_feriado) == 1) {//É feriado ...
            if($fornecedores_clientes_especiais == 'S') {
                $incremento_feriado = -1;
            }else {//Adiciono mais 1 dia ... Porque esta só será paga no próximo dia útil "???" ...
                $incremento_feriado = 1;
            }
            $data_pagamento = data::adicionar_data_hora(data::datetodata($data_pagamento, '/'),  $incremento_feriado);
            $data_pagamento = data::datatodate($data_pagamento, '-');

            $data_pagamento_foi_alterada = 'SIM';
        }
        //2) Fim de Semana ...
        //Agora, independente de acima ser feriado ou não, verifico se essa "data_vencimento_alterada" da Conta cai em um sábado ou domingo ...
        $dia_vencimento             = substr($data_pagamento, 8, 2);
        $mes_vencimento             = substr($data_pagamento, 5, 2);
        $ano_vencimento             = substr($data_pagamento, 0, 4);
        $dia_semana_q_ira_vencer    = date('w', mktime(0, 0, 0, $mes_vencimento, $dia_vencimento, $ano_vencimento));

        if($dia_semana_q_ira_vencer == 6 || $dia_semana_q_ira_vencer == 0) {//Sábado ou Domingo ...
            if($dia_semana_q_ira_vencer == 6) {//Sábado ...
                /*Fornecedor "SECRETARIA DA RECEITA FEDERAL - 899" ou "CAIXA ECONOMICA FEDERAL 942 - com a Conta FGTS" tem que pagar 
                no dia útil anterior "Se sábado então Sexta-Feira" ...*/
                if($fornecedores_clientes_especiais == 'S') {
                    $incremento_fim_de_semana = -1;
                }else {//Adiciono mais 2 dias ... Porque esta só será paga no próximo dia útil "Segunda-Feira" ...
                    $incremento_fim_de_semana = 2;
                }
            }else if($dia_semana_q_ira_vencer == 0) {//Domingo ...
                /*Fornecedor "SECRETARIA DA RECEITA FEDERAL - 899" ou "CAIXA ECONOMICA FEDERAL 942 - com a Conta FGTS" tem que pagar 
                no dia útil anterior "Se domingo então Sexta-Feira" ...*/
                if($fornecedores_clientes_especiais == 'S') {
                    $incremento_fim_de_semana = -2;
                }else {//Adiciono mais 2 dias ... Porque esta só será paga no próximo dia útil "Segunda-Feira" ...
                    $incremento_fim_de_semana = 1;
                }
            }
            $data_pagamento = data::adicionar_data_hora(data::datetodata($data_pagamento, '/'), $incremento_fim_de_semana);
            $data_pagamento = data::datatodate($data_pagamento, '-');

            $data_pagamento_foi_alterada = 'SIM';
        }

        if($data_pagamento_foi_alterada == 'SIM') {//Enquanto a variável $data_pagamento_foi_alterada for alterada continua chamando a função ...
            $indice++;
            self::nova_data_vencimento_alterada($data_pagamento, $fornecedores_clientes_especiais, $indice);
        }else {//Mais do que garantido que a $data_pagamento está correta por não cair em feriado ou fim de semana, então posso retorná-la ...
            /*Tive que colocar essa variável data_vencimento_alterada como $GLOBALS para que a função atualizar_data_alterada que chamou essa 
            função conseguisse enxergar o seu retorno, isso acontece devido eu trabalhar com o lance da recursividade ...*/
            $GLOBALS['data_vencimento_alterada'] = $data_pagamento;
            //Já posso retornar a $data_vencimento_alterada porque não é feriado e também não é fim de semana ...
            return $GLOBALS['data_vencimento_alterada'];
        }
    }
    
    /**********************************************************************/
    /****************************Contas à Receber**************************/
    /**********************************************************************/
    /*Esse parâmetro $data_pagamento, não é nada mais nada menos do que a $data_vencimento_alterada da Conta à Receber; 
    data em que que o Cliente vai nos pagar ...*/
    function nova_data_recebimento($data_pagamento, $id_tipo_recebimento, $indice) {
        $data_pagamento_foi_alterada = 'NÃO';//A princípio = NÃO porque acabamos de entrar na função ...
        
        /*Esses tipos de recebimentos do vetor abaixo, prorrogam a $data_recebimento da Conta à Receber fazendo com essa data não seja identifica 
        a Data de Vencimento Alterada ...

        3 - Cobrança Simples, 7 Protestado, 9 Cartório, 11 Cobrança Caucionada, 12 Desconto ...*/
        $vetor_tipos_recebimentos = array(3, 7, 9, 11, 12);
        
        //1) Feriado ...
        //A primeira coisa que eu faço é verificar se essa "$data_pagamento" cai em um feriado ...
        $sql = "SELECT `id_feriado` 
                FROM `feriados` 
                WHERE `data_feriado` = '$data_pagamento' LIMIT 1 ";
        $campos_feriado = bancos::sql($sql);
        if(count($campos_feriado) == 1) {//É feriado ...
            //Adiciono mais 1 dia ... Porque na teoria o cliente só vai pagar a Empresa no próximo dia útil "Terça-Feira" por exemplo ...
            $data_pagamento     = data::adicionar_data_hora(data::datetodata($data_pagamento, '/'),  1);
            $data_pagamento     = data::datatodate($data_pagamento, '-');

            $data_pagamento_foi_alterada = 'SIM';

            if(in_array($id_tipo_recebimento, $vetor_tipos_recebimentos)) {
                //Automaticamente a $data_recebimento é a própria $data_pagamento + 1 porque o cliente paga em um dia e cai em conta no outro ...
                $data_recebimento   = data::adicionar_data_hora(data::datetodata($data_pagamento, '/'),  1);
                $data_recebimento   = data::datatodate($data_recebimento, '-');
            }else {//Nesses casos a $data_recebimento é a própria $data_pagamento ...
                $data_recebimento   = $data_pagamento;
            }
        }
        /*****************************Fim de Semana****************************/
        //Agora, independente de acima ser feriado ou não, verifico se essa "$data_recebimento" da Conta cai em um sábado ou domingo ...
        $dia_vencimento             = substr($data_pagamento, 8, 2);
        $mes_vencimento             = substr($data_pagamento, 5, 2);
        $ano_vencimento             = substr($data_pagamento, 0, 4);
        $dia_semana_q_ira_vencer    = date('w', mktime(0, 0, 0, $mes_vencimento, $dia_vencimento, $ano_vencimento));

        if($dia_semana_q_ira_vencer == 6 || $dia_semana_q_ira_vencer == 0) {//Sábado ou Domingo ...
            if($dia_semana_q_ira_vencer == 6) {//Sábado ...
                $incremento_fim_de_semana = 2;
            }else if($dia_semana_q_ira_vencer == 0) {//Domingo ...
                $incremento_fim_de_semana = 1;
            }
            $data_pagamento = data::adicionar_data_hora(data::datetodata($data_pagamento, '/'), $incremento_fim_de_semana);
            $data_pagamento = data::datatodate($data_pagamento, '-');

            $data_pagamento_foi_alterada = 'SIM';

            if(in_array($id_tipo_recebimento, $vetor_tipos_recebimentos)) {
                //Automaticamente a $data_recebimento é a própria $data_pagamento + 1 porque o cliente paga em um dia e cai em conta no outro ...
                $data_recebimento   = data::adicionar_data_hora(data::datetodata($data_pagamento, '/'),  1);
                $data_recebimento   = data::datatodate($data_recebimento, '-');
            }else {//Nesses casos a $data_recebimento é a própria $data_pagamento ...
                $data_recebimento   = $data_pagamento;
            }
        }

        //Foi feita toda uma verificação em cima da $data_pagamento que esta realmente ok, preciso verificar agora a $data_recebimento ...
        if($indice == 0) {
            /*Se aconteceu de não ter a $data_recebimento então foi porque a $data_pagamento mais acima não caiu nem em feriado 
            e nem em fim de semana, mais sempre temos que ter uma $data_recebimento ...*/
            if($data_pagamento_foi_alterada == 'NÃO') {
                if(in_array($id_tipo_recebimento, $vetor_tipos_recebimentos)) {
                    //Automaticamente a $data_recebimento é a própria $data_pagamento + 1 porque o cliente paga em um dia e cai em conta no outro ...
                    $data_recebimento   = data::adicionar_data_hora(data::datetodata($data_pagamento, '/'),  1);
                    $data_recebimento   = data::datatodate($data_recebimento, '-');
                }else {
                    /*Automaticamente a $data_recebimento é a própria $data_pagamento, mas nesse caso eu NÃO preciso somar + 1, porque o Tipo de 
                    Recebimento dessa conta representa que o Cliente está pagando a empresa no mesmo dia ...*/
                    $data_recebimento   = $data_pagamento;
                }
            }
            $indice++;
            self::nova_data_recebimento($data_recebimento, $id_tipo_recebimento, $indice);
        }else {//Verificando a $data_recebimento ...
            $data_recebimento = $data_pagamento;

            if($data_pagamento_foi_alterada == 'SIM') {//Enquanto a variável $data_pagamento_foi_alterada for alterada continua chamando a função ...
                $indice++;
                self::nova_data_recebimento($data_recebimento, $id_tipo_recebimento, $indice);
            }else {//Mais do que garantido que a $data_recebimento está correta por não cair em feriado ou fim de semana, então posso retorná-la ...
                /*Tive que colocar essa variável data_recebimento como $GLOBALS para que a função atualizar_data_alterada que chamou essa 
                função conseguisse enxergar o seu retorno, isso acontece devido eu trabalhar com o lance da recursividade ...*/
                $GLOBALS['data_recebimento'] = $data_recebimento;
                //Já posso retornar a $data_vencimento_alterada porque não é feriado e também não é fim de semana ...
                return $GLOBALS['data_recebimento'];
            }
        }
    }
}
?>