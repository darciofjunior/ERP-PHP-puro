<?
if(!class_exists('bancos')) require 'bancos.php';//CASO EXISTA EU DESVIO A CLASSE ...
class financeiros {
    function controle_credito($id_cliente) {
        $data_atual     = date('d/m/Y');
        $ultimos_45dias = data::datatodate(data::adicionar_data_hora($data_atual, -45), '-');
        $ultimos_6meses = data::datatodate(data::adicionar_data_hora($data_atual, -180), '-');
        //Busco o Cr�dito atual do Cliente ...
        $sql = "SELECT `credito` 
                FROM `clientes` 
                WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
        $campos = bancos::sql($sql);
        //Se o Cr�dito for "C" ou "D" o sistema simplesmente retorna esse Cr�dito, s/ atualizar nada ...
        if($campos[0]['credito'] == 'C' || $campos[0]['credito'] == 'D') return $campos[0]['credito'];
        /************************************************************************************************/
        /*****************************Controle Especial somente p/ Cr�dito B*****************************/
        /************************************************************************************************/
        //Verifico se este cliente est� com seu cr�dito a mais de 45 dias - 1 m�s e meio sem modifica��es ...
        $sql = "SELECT `id_cliente` 
                FROM `clientes` 
                WHERE (SUBSTRING(`credito_data`, 1, 10) = '0000-00-00' OR SUBSTRING(`credito_data`, 1, 10) < '$ultimos_45dias') 
                AND `id_cliente` = '$id_cliente' LIMIT 1 ";
        $campos_cliente = bancos::sql($sql);
        if(count($campos_cliente) == 1) {//Est� a mais de 45 dias ...
            //Vejo se este Cliente possui pelo menos 1 Compra "Faturamento" dentro dos �ltimos 6 meses - 180 dias ...
            $sql = "SELECT `id_nf` 
                    FROM `nfs` 
                    WHERE `data_emissao` > '$ultimos_6meses' 
                    AND `id_cliente` = '$id_cliente' LIMIT 1 ";
            $campos_nfs = bancos::sql($sql);
            if(count($campos_nfs) == 0) {//N�o tem compra nos �ltimos 6 meses ...
                //Por falta de movimenta��o e por seguran�a modificamos o cr�dito p/ D "como se esse fosse um novo Cliente" ...
                $sql = "UPDATE `clientes` SET `credito` = 'D', `credito_data` = '".date('Y-m-d H:i:s')."' WHERE `id_cliente` = '$id_cliente' AND `credito` <> 'D' LIMIT 1 ";
                bancos::sql($sql);
                return 'D';
            }
        }
        /************************************************************************************************/
        /*Est� a menos de 45 dias sem modifica��es de Cr�dito ou possui compra nos �ltimos 6 meses, 
        sendo assim o sistema s� retorna o Cr�dito provavelmente "B" ...*/
        return $campos[0]['credito'];
    }

    function cadastrar_contas_automaticas() {
        //Busco somente as Contas Autom�ticas que est�o com a Marca��o conta_ativa = 'S' no "Sistema" ...
        $sql = "SELECT * 
                FROM `contas_apagares_automaticas` 
                WHERE `conta_ativa` = 'S' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
            $nova                           = 'nao';//N�O pode retirar esta linha, porque serve de controle l� embaixo ...
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
            /*Tratamento com vari�vel "$data_emissao" que ser� adicionada no Campo Data de Emiss�o 
            da Conta � Pagar ... Nunca que a Data de Emiss�o pode ser Maior do que a Data de Vencimento ...

            Se a Data de Emiss�o for maior do que a Data de Vencimento, a Data de Emiss�o assume a 
            Data de Vencimento ...*/
            $data_emissao_conta_apagar      = (date('Y-m-d') > $campos[$i]['data_vencimento']) ? $campos[$i]['data_vencimento'] : date('Y-m-d');
            
            /*Comentado no dia 26/06/2015 porque segundo a Dona Sandra as op��es 1 e 2 n�o 
            s�o utilizadas ...*/
            
            /*switch($status) {
                case 0://Tipo de Automa��o "POR DATA" ...
                    /**********************Esquema Provis�rio**********************/
                    //Verifico se existe alguma conta � Pagar Automatica que esteja em aberto, que n�o foi paga ...
                    /*$sql = "SELECT `id_conta_apagar` 
                            FROM `contas_apagares` 
                            WHERE `id_conta_apagar_automatica` = '$id_conta_apagar_automatica' 
                            AND `status` < '2' LIMIT 1 ";
                    $campos_conta_apagar = bancos::sql($sql);
                    /*Seguran�a p/ n�o gerar Conta a Pagar Autom�tica desnecess�ria ...
                    
                    S� gera se a Conta Autom�tica n�o tiver nenhuma parcela ou se a mesma tiver mais de 
                    uma parcela que ocorre quando � contrato e cuja Conta � utilizada apenas uma �nica vez
                    na vida ...
                    if(($qtde_parcelas <= 1 && count($campos_conta_apagar) == 0) || $qtde_parcelas > 1) {
                        $data_atual                     = date('Ymd');
                        $data_criacao_proximo_registro  = data::datatodate(data::adicionar_data_hora(data::datetodata($data_proximo_vencimento, '/'), -$dia_exibicao), '');
                        if($data_criacao_proximo_registro <= $data_atual) $nova = 'sim';
                    }
                break;
                case 1://Tipo de Automa��o "PAGO A CONTA ANTERIOR" ...
                    //Verifico a �ltima "Conta � Pagar" que foi gerada atrav�s desse $id_conta_apagar_automatica ...
                    $sql = "SELECT `status` 
                            FROM `contas_apagares` 
                            WHERE `id_conta_apagar_automatica` = '$id_conta_apagar_automatica' ORDER BY `id_conta_apagar` DESC LIMIT 1 ";
                    $campos_conta_apagar = bancos::sql($sql);
                    if($campos_conta_apagar[0]['status'] == 2) $nova = 'sim';//Significa que essa j� foi quitada ...
                break;
                default://Tipo de Automa��o "AMBAS ACIMA" ...
                    /**********************Esquema Provis�rio**********************/
                    //Verifico se existe alguma conta � Pagar Automatica que esteja em aberto, que n�o foi paga ...
                    /*$sql = "SELECT `id_conta_apagar` 
                            FROM `contas_apagares` 
                            WHERE `id_conta_apagar_automatica` = '$id_conta_apagar_automatica' 
                            AND `status` < '2' LIMIT 1 ";
                    $campos_conta_apagar = bancos::sql($sql);
                    /*Seguran�a p/ n�o gerar Conta a Pagar Autom�tica desnecess�ria ...
                    
                    S� gera se a Conta Autom�tica n�o tiver nenhuma parcela ou se a mesma tiver mais de 
                    uma parcela que ocorre quando � contrato e cuja Conta � utilizada apenas uma �nica vez
                    na vida ...
                    if(($qtde_parcelas <= 1 && count($campos_conta_apagar) == 0) || $qtde_parcelas > 1) {
                        $data_atual                     = date('Ymd');
                        $data_criacao_proximo_registro  = data::datatodate(data::adicionar_data_hora(data::datetodata($data_proximo_vencimento, '/'), -$dia_exibicao), '');
                        if($data_criacao_proximo_registro <= $data_atual) $nova = 'sim';
                        //Verifico a �ltima "Conta � Pagar" que foi gerada atrav�s desse $id_conta_apagar_automatica ...
                        $sql = "SELECT `status` 
                                FROM `contas_apagares` 
                                WHERE `id_conta_apagar_automatica` = '$id_conta_apagar_automatica' ORDER BY `id_conta_apagar` DESC LIMIT 1 ";
                        $campos_conta_apagar = bancos::sql($sql);
                        if($campos_conta_apagar[0]['status'] == 2) $nova = 'sim';//Significa que essa j� foi quitada ...
                    }
                break;
            }*/
            
            /**************************************************************/
            /**************************Ambas Acima*************************/
            /**************************************************************/
            
            /**********************Esquema Provis�rio**********************/
            //Verifico se existe alguma conta � Pagar Automatica que esteja em aberto, que n�o foi paga ...
            /*$sql = "SELECT `id_conta_apagar` 
                    FROM `contas_apagares` 
                    WHERE `id_conta_apagar_automatica` = '$id_conta_apagar_automatica' 
                    AND `status` < '2' LIMIT 1 ";
            $campos_conta_apagar = bancos::sql($sql);*/
            /*Seguran�a p/ n�o gerar Conta a Pagar Autom�tica desnecess�ria ...
                    
            S� gera se a Conta Autom�tica n�o tiver nenhuma parcela ou se a mesma tiver mais de 
            uma parcela que ocorre quando � contrato e cuja Conta � utilizada apenas uma �nica vez
            na vida ...*/
            //if(($qtde_parcelas == 0 && count($campos_conta_apagar) == 0) || $qtde_parcelas > 1) {
            
            /*Se a conta Autom�tica for ativa = 'S' ou seja conta utiliz�vel ...
            if($conta_ativa == 'S') {*/
                $data_atual                     = date('Ymd');
                $data_criacao_proximo_registro  = data::datatodate(data::adicionar_data_hora(data::datetodata($data_proximo_vencimento, '/'), -$dia_exibicao), '');
                if($data_criacao_proximo_registro <= $data_atual) $nova = 'sim';
                //Verifico a �ltima "Conta � Pagar" que foi gerada atrav�s desse $id_conta_apagar_automatica ...
                /*$sql = "SELECT `status` 
                        FROM `contas_apagares` 
                        WHERE `id_conta_apagar_automatica` = '$id_conta_apagar_automatica' ORDER BY `id_conta_apagar` DESC LIMIT 1 ";
                $campos_conta_apagar = bancos::sql($sql);
                if($campos_conta_apagar[0]['status'] == 2) $nova = 'sim';//Significa que essa j� foi quitada ...*/
            //}
            /**************************************************************/

            if($nova == 'sim') {//Significa que pode ser gerado uma Nova Conta � Pagar atrav�s da conta autom�tica ...
                //Aqui verifico os dados bancarios da conta � pagar ...
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
//Verifico se a Data de Vencimento da Conta ir� cair em um Fim de Semana ...
                $dia_vencimento         = substr($data_proximo_vencimento, 8, 2);
                $mes_vencimento         = substr($data_proximo_vencimento, 5, 2);
                $ano_vencimento         = substr($data_proximo_vencimento, 0, 4);
                $dia_semana_q_ira_vencer= date('w', mktime(0, 0, 0, $mes_vencimento, $dia_vencimento, $ano_vencimento));

                if($qtde_parcelas > 1) {//Conta Autom�tica do "Tipo Contrato" ...
                    //Se usar �ndice $i aqui, d� conflito pq utilizo essa function dentro de outro for ...
                    for($parcela = 0; $parcela < $qtde_parcelas; $parcela++) {
/*Posso gerar Contas Autom�ticas p/ qualquer Tipo de Conta, mas se o Tipo de Conta for "Desp. Banc�rias", 
eu s� posso gerar estas com vencimentos em que o Dia da Semana caiam de 2� at� 6� somente ...*/
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
                                $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_fornecedor`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_fornecedor', '$_SESSION[id_funcionario]', '$id_conta_apagar_new', '18', '".$observacao." - Parcela N.� ".($parcela + 1)." de ".$qtde_parcelas."', '".date('Y-m-d H:i:s')."') ";
                                bancos::sql($sql);
                            }
                        }
                        if($tipo_data == 0) {//Quando � Fixa, � sempre no mesmo dia do m�s ...
                            /*****************************************************************/
                            //Gerando a Pr�xima Data de Vencimento ...
                            $data_vencimento_dia    = substr($data_proximo_vencimento, 8, 2);
                            $data_vencimento_mes    = substr($data_proximo_vencimento, 5, 2) + 1;//Preparo o Novo M�s ...
                            $data_vencimento_ano    = substr($data_proximo_vencimento, 0, 4);

                            if($data_vencimento_mes > 12) {//Virou o Ano ...
                                $data_vencimento_mes = 1;//Volta p/ o m�s de Janeiro ...
                                $data_vencimento_ano+= 1;//Adiciona um novo Ano ...
                            }
                            if($data_vencimento_mes < 10) $data_vencimento_mes = '0'.$data_vencimento_mes;
                            $resposta_data_valida   =  checkdate($data_vencimento_mes, $dia_vencimento, $data_vencimento_ano);

                            if($resposta_data_valida == 1) {//Data V�lida ...
                               $data_proximo_vencimento = $dia_vencimento.'/'.$data_vencimento_mes.'/'.$data_vencimento_ano;
                            }else {//Data Inv�lida, por exemplo: 30 de fevereiro ... rs
                                if($data_vencimento_mes == '02') {//Somente em Fevereiro, farei essa verifica��o ...
                                    /*Se o ANO da qual vai vencer a Conta for 'Bissexto' ent�o o sistema coloca 
                                    o dia como sendo 29, do contr�rio 28 que � o normal ...*/
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
                    //Neutralizo a Conta para que n�o seja gerada outras parcelas ...
                    $sql = "UPDATE `contas_apagares_automaticas` SET `conta_ativa` = 'N' WHERE `id_conta_apagar_automatica` = '$id_conta_apagar_automatica' LIMIT 1 ";
                    bancos::sql($sql);
                }else {//Conta Autom�tica do "Tipo N�O Contrato" ...
/*Se o Grupo da Conta � Pagar for Imposto e cair na no fim de Semana, antecipo essa p/ que caia 
na Sexta-Feira ...*/
                    if($id_grupo == 33 && ($dia_semana_q_ira_vencer == 6 || $dia_semana_q_ira_vencer == 0)) {
                        //Se a Data de Vencimento da Nova Conta � Pagar que ser� gerada caiu no ...
                        $qtde_dias_para_retroceder              = ($dia_semana_q_ira_vencer == 6) ? 1 : 2;//S�bado ou Domingo ...
                        $data_proximo_vencimento_retrocedida    = data::adicionar_data_hora(data::datetodata($data_proximo_vencimento, '/'), -$qtde_dias_para_retroceder);
                        $data_proximo_vencimento_retrocedida    = data::datatodate($data_proximo_vencimento_retrocedida, '-');
                    }
                    $data_proximo_vencimento_gravar = (!empty($data_proximo_vencimento_retrocedida)) ? $data_proximo_vencimento_retrocedida : $data_proximo_vencimento;
/*Posso gerar Contas Autom�ticas p/ qualquer Tipo de Conta, mas se o Tipo de Conta for "Desp. Banc�rias", 
eu s� posso gerar estas com vencimentos em que o Dia da Semana caiam de 2� at� 6� somente ...*/
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
                    if($tipo_data == 0) {//Quando � Fixa, � sempre no mesmo dia do m�s ...
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

    //N�o me lembro o porque dessa fun��o - D�rcio 06/08/2014 ...
    function atualiza_valores_contas_automaticas($id_conta_apagar_automatica, $valor, $valor_reajustado) {//quando pagar a conta atualizar a mesma no valor da contas_automaticas
        $sql = "UPDATE `contas_apagares_automaticas` SET `valor` = '$valor', `valor_reajustado` = '$valor_reajustado' WHERE `id_conta_apagar_automatica` = '$id_conta_apagar_automatica' LIMIT 1 ";
        bancos::sql($sql);
    }

    function cheque_devolvido($id_cheque_cliente) {//Isto ocorre quando o cheque � devolvido ...
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

            //Zero o valor do registro na tabela de "Recebimentos" porque n�o recebemos esse Valor ...
            $sql = "UPDATE `contas_receberes_quitacoes` SET `valor` = '0' WHERE `id_conta_receber_quitacao` = '".$campos[$i]['id_conta_receber_quitacao']."' LIMIT 1 ";
            bancos::sql($sql);

            /*Em cima das Duplicatas encontradas, eu abato esse Valor de cheque e mudo o seu status 
            p/ parcialmente recebida ...*/
            $sql = "UPDATE `contas_receberes` SET `valor_pago` = `valor_pago` - '$valor_cheque_conta', `status` = '1', `descricao_conta` = 'CHEQUE DEVOLVIDO' WHERE `id_conta_receber` = '".$campos[$i]['id_conta_receber']."' LIMIT 1 ";
            bancos::sql($sql);
        }
    }

    function estorno_conta_recebida($id_conta_receber_quitacao) { //Isto ocorre quando o cheque � devolvido
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
        $valor_pago         = $campos[0]['valor'];//Esse valor pago est� em R$, pois sempre guardamos em R$ o Valor Pago ...
        $valor_moeda_dia    = $campos[0]['valor_moeda_dia'];
        /*********************************************************************/
        /*************************Controle com Cheque*************************/
        /*********************************************************************/
        if($id_cheque > 0) {//Se a Quita��o que est� sendo estornada, foi paga mediante a cheque ent�o ...
            //Verifico aqui quantas Contas que foram pagas com o mesmo Cheque ...
            $sql = "SELECT id_conta_apagar_quitacao  
                    FROM `contas_apagares_quitacoes` 
                    WHERE `id_cheque` = '$id_cheque' ";
            $campos_cheques = bancos::sql($sql);
            if(count($campos_cheques) > 1) {//2 ou + Quita��es Contas, representa q este cheque tb � de outras contas ...
                $sql = "UPDATE `cheques` SET `valor` = `valor` - $valor_pago WHERE `id_cheque` = '$id_cheque' LIMIT 1 ";
                bancos::sql($sql);
            }else if(count($campos_cheques) == 1) {//Posso zerar o cheque, pois ele � unico p/ uma s� Conta ...
                $sql = "UPDATE `cheques` SET `status` = '0', valor = '0.00', historico = '' WHERE `id_cheque` = '$id_cheque' LIMIT 1 ";
                bancos::sql($sql);
            }
        }
        /*********************************************************************/
        //Apago o Registro de Quita��o ...
        $sql = "DELETE FROM `contas_apagares_quitacoes` WHERE `id_conta_apagar_quitacao` = '$id_conta_apagar_quitacao' LIMIT 1 ";
        bancos::sql($sql);

        $sql = "SELECT COUNT(id_conta_apagar_quitacao) AS qtde 
                FROM `contas_apagares_quitacoes` 
                WHERE `id_conta_apagar` = '$id_conta_apagar' ";
        $campos_qtde    = bancos::sql($sql);
        $status         = ($campos_qtde[0]['qtde'] > 0) ? 1 : 0;
        //Verifico se a Conta � de Importa��o e o Quanto que foi pago desta Conta ...
        $sql = "SELECT id_tipo_moeda, valor_pago 
                FROM `contas_apagares` 
                WHERE `id_conta_apagar` = '$id_conta_apagar' LIMIT 1 ";
        $campos_pago = bancos::sql($sql);
        if($campos_pago[0]['id_tipo_moeda'] > 1) {//Significa que � de Importa��o ...
            if($campos_pago[0]['valor_pago'] - $valor_pago < 0) {
                $sql = "UPDATE `contas_apagares` SET `valor_pago` = '0', `predatado` = '0', `status` = '$status' WHERE `id_conta_apagar` = '$id_conta_apagar' LIMIT 1 ";
            }else {
                /*Esse controle s� ser� feito quando a Conta for do Tipo moeda Estrangeira, pq o campo valor_pago 
                da Conta � Pagar, sempre guardo o que foi pago no Tipo da Moeda da Conta ...*/
                if($id_tipo_moeda > 1) $valor_pago/= $valor_moeda_dia;
                $sql = "UPDATE `contas_apagares` SET `valor_pago` = `valor_pago` - $valor_pago, `predatado` = '0', `status` = '$status' WHERE `id_conta_apagar` = '$id_conta_apagar' LIMIT 1 ";
            }
        }else {
            $sql = "UPDATE `contas_apagares` SET `valor_pago` = `valor_pago` - $valor_pago, `predatado` = '0', `status` = '$status' WHERE `id_conta_apagar` = '$id_conta_apagar' LIMIT 1 ";
        }
        bancos::sql($sql);
        return array('status' => $status, 'id_conta_apagar' => $id_conta_apagar);
    }

/*********Fun��o que retorna a qtde e ids das contas � pagar ou � receber conforme o par�metro**********/
//De qual empresa que eu quero ver as contas daquele Cliente -> Albafer, Grupo ou Tool Master

/*
Descri��o de alguns par�metros abaixo:

$onde_buscar_dados = 1 -> Tabela de Clientes
$onde_buscar_dados = 2 -> Tabela de Fornecedores
$onde_buscar_dados = 3 -> Tabela de Representantes

$tipo_retorno = 1 -> Contas � Pagar
$tipo_retorno = 2 -> Contas � Receber

Esse 6� par�metro s� ser� $trazer_livre_debito = 'S' quando vier pelo Controle de Pagamento que fica no Contas � Pagar do Financeiro ...*/
    function contas_em_aberto($id, $onde_buscar_dados, $id_empresa, $tipo_retorno = 1, $id_conta_apagar_automatica = 0, $trazer_livre_debito = 'N') {
        if($id_conta_apagar_automatica == 0) {//Aqui significa que desejo verificar a situa��o geral das Contas do Financeiro ...
            //Busca de alguns campos que ser�o utilizados mais abaixo ...
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
            //Tratamento com a vari�vel p/ n�o furar o SQL ...
            if(empty($id_empresa)) $id_empresa = '%';

            if($id_pais == 31) {//Pa�s Nacional "Brasil" ...
                if(strlen($cnpj_cpf) == 14 || strlen($cnpj_cpf) == 11) {//CNPJ ou CPF ...
                    $condicao_contas_apagares 	= " AND f.`cnpj_cpf` = '$cnpj_cpf' ";
                    $condicao_contas_receberes 	= " AND c.`cnpj_cpf` = '$cnpj_cpf' ";
                }else {
                    $condicao_contas_apagares 	= " AND f.`razaosocial` = '$razaosocial' ";
                    $condicao_contas_receberes 	= " AND c.`razaosocial` = '$razaosocial' ";
                }
            }else {//Pa�s Estrangeiro ...
                $condicao_contas_apagares 	= " AND f.`razaosocial` = '$razaosocial' ";
                $condicao_contas_receberes 	= " AND c.`razaosocial` = '$razaosocial' ";
            }
            
            if($tipo_retorno == 1) {//Caminho de Contas � Pagar ...
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
            }else if($tipo_retorno == 2) {//Caminho de Contas � Receber ...
                /*Esse geralmente � o Padr�o do sistema inteiro, nunca trazer as NFs que s�o livre de D�bito, somente quando vier pelo 
                Controle de Pagamento que fica no Contas � Pagar do Financeiro que ter� de trazer estas tamb�m al�m das normais ...*/
                if($trazer_livre_debito == 'N') $condicao_trazer_livre_debito = " AND nfs.`livre_debito` = 'N' ";
///////////////////////////////////Estrutura dos SQL(s) do Union///////////////////////////////////
//1) Lista todas as Contas que est�o com o Status de em Aberto para receber do Cliente - Modo Antigo ...
//2) Aqui busca as contas que s�o importadas diretamente do faturamento e que n�o sejam "Livre de D�bito" ...
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
        }else {//S� me interessa verificar as Contas Apagares que s�o Autom�ticas ...
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
    
    //Nessa fun��o eu c�lculo tanto os valores Extras como os Valores Reajustados das Contas � Pagar ...
    function calculos_conta_pagar($id_conta_apagar) {
        if(!class_exists('genericas'))    require 'genericas.php';//Caso exista eu desvio a classe 
        //Busca do �ltimo valor do d�lar e do euro ...
        $valor_dolar    = genericas::moeda_dia('dolar');
        $valor_euro     = genericas::moeda_dia('euro');
        //Busca os dados da Conta � Receber ...
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
        if($campos[0]['id_tipo_moeda'] == 2) {//Conta em D�lar ...
            $valor_em_reais = $valor_conta * $valor_dolar;
        }else if($campos[0]['id_tipo_moeda'] == 3) {//Conta em Euro ...
            $valor_em_reais = $valor_conta * $valor_euro;
        }else {//Conta em R$ ...
            $valor_em_reais = $valor_conta;//Nesse caso � o Valor da Pr�pria Conta ...
        }
        /********************************************************************************************/
        $taxa_juros         = $campos[0]['taxa_juros'];
        $data_vencimento    = $campos[0]['data_vencimento'];
        $data_atual         = date('Y-m-d');
        
        /***************
        
        1) UPDATE no campo Data Vencimento Alterada = Data Vencimento Inicial ...
         
         1.a) UPDATE `contas_apagares` SET data_vencimento_alterada = data_vencimento ... ok, rodar no ar agora ...
         
         1.b) Uma Conta � Pagar tem uma NFe, uma NFe pode ter N vencimentos "Financiamentos" ...
         
         Data de Vencimento, Valor Inicial da Parcela ...
         
        IF(DAta Inicial = DAta Alterada) {
            calculo igual ao que est� hoje ...
        }else {
            Data Alterada - Data Inicial p/ calcular os juros ...
        }

        3) Data de Vencimento Alterada = Data de Vencimento - Inclus�o INSERT nos 2 campos ... ok

        4) Data Prorrogada n�o pode ser menor que a Data de Vencimento ... ok
         
        5) Pagamento -> Colocar Checkbox Zerar Juros -> Zera Multa + Juros, resumindo Valor Inicial ... ok
        
        6) Renomear os Campos VAlores Reajustos que pela teoria hoje n�o tem sentido ... ok
         
        */
        
        //A vari�vel dias equivale a data atual at� a data de vecimento ...
        $dias = data::diferenca_data($data_vencimento, $data_atual);
        if($dias[0] < 0) $dias[0] = 0;
        
        if($taxa_juros > 0) {
            if($campos[0]['tipo_juros'] == 'S') {//Juros Simples ...
                //A vari�vel dias equivale a data atual at� a data de vecimento ...
                $fator_taxa_juros_dias_venc = ($taxa_juros / 30 * $dias[0] / 100) + 1;
            }else {//Juros Composto ...
                $fator_taxa_juros_diaria    = pow(1 + $taxa_juros / 100, (1 / 30));
                $fator_taxa_juros_dias_venc = pow($fator_taxa_juros_diaria, $dias[0]);
            }
            //Aqui eu s� pego exatamente 2 casas do valor da conta, simplesmente trunco, n�o me interessa arredondar ...
            $valor_juros                    = round($valor_em_reais * ($fator_taxa_juros_dias_venc - 1), 2);
            /*Essa vari�vel se comporta exatamente no Tipo da Moeda da Conta "R$", "U$", Euro ... 
            irei utiliz�-la apenas na Hora do Recebimento da mesma ...*/
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

//Nessa fun��o eu c�lculo tanto os valores Extras como os Valores Reajustados das Contas � Receber ...
    function calculos_conta_receber($id_conta_receber) {
        if(!class_exists('genericas'))    require 'genericas.php';//Caso exista eu desvio a classe 
        //Busca do �ltimo valor do d�lar e do euro ...
        $valor_dolar    = genericas::moeda_dia('dolar');
        $valor_euro     = genericas::moeda_dia('euro');
        //Busca os dados da Conta � Receber ...
        $sql = "SELECT cr.*, CONCAT(tm.simbolo, '&nbsp;') AS simbolo 
                FROM `contas_receberes` cr 
                INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = cr.id_tipo_moeda 
                WHERE cr.`id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
        $campos             = bancos::sql($sql);
        $manual             = $campos[0]['manual'];
        //Aqui essas vari�veis s�o para o c�lculo da f�rmula do Roberto ...
        $valor_conta        = $campos[0]['valor'];
        $valor_desconto     = $campos[0]['valor_desconto'];
        /********************************************************************************************/
        //Verifico se tenho alguma NF de Devolu��o importada p/ essa Duplicata da Fun��o ...
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
       //Essa vari�vel ser� utilizada + abaixo em alguns c�lculos, principalmente p/ o valor do Juros ...
        $valor_liq_conta    = ($valor_conta - $valor_desconto - $valor_abatimento);
/*Significa que aqui n�o c�lcula a taxa de juros, e sim s� puxa o valor direto da base com a qual foi o que o usu�rio 
digitou anteriormente ...*/
        if($manual == 1) {
            $valor_reajustado = ($valor_liq_conta + $valor_despesas) + ($valor_juros);
        }else {
            if($taxa_juros > 0) {
                //A vari�vel dias equivale a data atual at� a data de vecimento ...
                $dias   = data::diferenca_data($data_vencimento_alterada, $data_atual);
                if($dias[0] < 0) $dias[0] = 0;
                $taxa_juros_dias_venc = ($taxa_juros / 100) / 30 * $dias[0];
            }else {
                $taxa_juros_dias_venc = 0;
            }
            $valor_juros        =   $valor_liq_conta * $taxa_juros_dias_venc;
            //As despesas � o �nico campo que n�o pode levar em conta os Juros ...
            $valor_reajustado   =   $valor_liq_conta * ($taxa_juros_dias_venc + 1) + $valor_despesas;
        }
        
        /*Nunca o Juros pode ser negativo porque significa que n�s daqui da Albafer ainda 
        estamos devendo juros p/ o Cliente ...*/
        if($valor_juros < 0) $valor_juros = 0;
        
        //Aqui eu s� pego exatamente 2 casas do valor da conta, simplesmente trunco, n�o me interessa arredondar ...
        $valor_juros        = round(round($valor_juros, 3), 2);
        $valor_reajustado   = round(round($valor_reajustado, 3), 2);
        $valores_extra      = $valor_juros + $valor_despesas - $valor_desconto - $valor_abatimento;
        
        //Aqui eu s� pego exatamente 2 casas do valor da conta, simplesmente trunco, n�o me interessa arredondar ...
        $valores_extra      = round(round($valores_extra, 3), 2);
        $valor_reajustado-= $campos[0]['valor_pago'];

        if($campos[0]['id_tipo_moeda'] == 2) {//D�lar
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
    
    /*Fun��o que guarda os dados banc�rios do Fornecedor da Negocia��o na tabela relacional 
    do "$id_conta_apagar" passado por par�metro ...*/
    function inserir_dados_bancarios($id, $acao, $id_conta_apagar) {
        //Sele��o dos dados banc�rios do fornecedor para gravar na tabela de contas_apagares_vs_pffs para ficar + f�cil a busca dos dados ...
        if($acao == 1) {//Significa que essa fun��o foi chamada pelo Caminho de NFe ...
            $sql = "SELECT fp.`banco`, fp.`agencia`, fp.`num_cc`, fp.`correntista`, fp.`cnpj_cpf` 
                    FROM `nfe` 
                    INNER JOIN `fornecedores_propriedades` fp ON fp.`id_fornecedor_propriedade` = nfe.`id_fornecedor_propriedade` 
                    WHERE nfe.`id_nfe` = '$id' LIMIT 1 ";
        }else if($acao == 2) {//Significa que essa fun��o foi chamada pelo Caminho de Antecipa��o ...
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
    Par�metros ...

    Tipo Conta = 'A' => 'Contas � Pagar' ...
    Tipo Conta = 'R' => 'Contas � Receber' ...*/
    function atualizar_data_alterada($id, $tipo_conta) {
        if($tipo_conta == 'A') {//Conta � Pagar ...
            $sql = "SELECT `id_fornecedor`, `numero_conta`, `data_vencimento_alterada` 
                    FROM `contas_apagares` 
                    WHERE `id_conta_apagar` = '$id' LIMIT 1 ";
            $campos = bancos::sql($sql);
            
            /*Fornecedor "SECRETARIA DA RECEITA FEDERAL - 899" ou "CAIXA ECONOMICA FEDERAL 942 - com a Conta FGTS" tem que pagar 
            no dia �til anterior "Se ??? ent�o ???" ...*/
            if($campos[0]['id_fornecedor'] == 899 || ($campos[0]['id_fornecedor'] == 942 && $campos[0]['numero_conta'] == 'FGTS')) {
                $fornecedores_clientes_especiais = 'S';
            }else {//Adiciono mais 1 dia ... Porque esta s� ser� paga no pr�ximo dia �til "???" ...
                $fornecedores_clientes_especiais = 'N';
            }
            //Na primeira vez que eu chamo essa fun��o, eu passo o 4� par�metro �ndice como sendo 0 ...
            self::nova_data_vencimento_alterada($campos[0]['data_vencimento_alterada'], $fornecedores_clientes_especiais, 0);
            
            //Atualizo a tabela de Contas � Pagar com a nova $data_vencimento_alterada ...
            $sql = "UPDATE `contas_apagares` SET `data_vencimento_alterada` = '$GLOBALS[data_vencimento_alterada]' WHERE `id_conta_apagar` = '$id' LIMIT 1 ";
            
        }else if($tipo_conta == 'R') {//Conta � Receber ...
            $sql = "SELECT `id_tipo_recebimento`, `data_vencimento_alterada` 
                    FROM `contas_receberes` 
                    WHERE `id_conta_receber` = '$id' LIMIT 1 ";
            $campos = bancos::sql($sql);
            
            //Na primeira vez que eu chamo essa fun��o, eu passo o 4� par�metro �ndice como sendo 0 ...
            self::nova_data_recebimento($campos[0]['data_vencimento_alterada'], $campos[0]['id_tipo_recebimento'], 0);
            
            //Atualizo a tabela de Contas � Receber com a nova $data_recebimento ...
            $sql = "UPDATE `contas_receberes` SET `data_recebimento` = '$GLOBALS[data_recebimento]' WHERE `id_conta_receber` = '$id' LIMIT 1 ";
        }
        bancos::sql($sql);
    }
    
    /**********************************************************************/
    /*****************************Contas � Pagar***************************/
    /**********************************************************************/
    /*Esse par�metro $data_pagamento, n�o � nada mais nada menos do que a $data_vencimento_alterada da Conta � Pagar; 
    data em que n�s empresa tem que pagar o Fornecedor ...*/
    function nova_data_vencimento_alterada($data_pagamento, $fornecedores_clientes_especiais, $indice) {
        $data_pagamento_foi_alterada = 'N�O';//A princ�pio = N�O porque acabamos de entrar na fun��o ...
        
        //1) Feriado ...
        //A primeira coisa que eu fa�o � verificar se essa "data_vencimento_alterada" cai em um feriado ...
        $sql = "SELECT `id_feriado` 
                FROM `feriados` 
                WHERE `data_feriado` = '$data_pagamento' LIMIT 1 ";
        $campos_feriado = bancos::sql($sql);
        if(count($campos_feriado) == 1) {//� feriado ...
            if($fornecedores_clientes_especiais == 'S') {
                $incremento_feriado = -1;
            }else {//Adiciono mais 1 dia ... Porque esta s� ser� paga no pr�ximo dia �til "???" ...
                $incremento_feriado = 1;
            }
            $data_pagamento = data::adicionar_data_hora(data::datetodata($data_pagamento, '/'),  $incremento_feriado);
            $data_pagamento = data::datatodate($data_pagamento, '-');

            $data_pagamento_foi_alterada = 'SIM';
        }
        //2) Fim de Semana ...
        //Agora, independente de acima ser feriado ou n�o, verifico se essa "data_vencimento_alterada" da Conta cai em um s�bado ou domingo ...
        $dia_vencimento             = substr($data_pagamento, 8, 2);
        $mes_vencimento             = substr($data_pagamento, 5, 2);
        $ano_vencimento             = substr($data_pagamento, 0, 4);
        $dia_semana_q_ira_vencer    = date('w', mktime(0, 0, 0, $mes_vencimento, $dia_vencimento, $ano_vencimento));

        if($dia_semana_q_ira_vencer == 6 || $dia_semana_q_ira_vencer == 0) {//S�bado ou Domingo ...
            if($dia_semana_q_ira_vencer == 6) {//S�bado ...
                /*Fornecedor "SECRETARIA DA RECEITA FEDERAL - 899" ou "CAIXA ECONOMICA FEDERAL 942 - com a Conta FGTS" tem que pagar 
                no dia �til anterior "Se s�bado ent�o Sexta-Feira" ...*/
                if($fornecedores_clientes_especiais == 'S') {
                    $incremento_fim_de_semana = -1;
                }else {//Adiciono mais 2 dias ... Porque esta s� ser� paga no pr�ximo dia �til "Segunda-Feira" ...
                    $incremento_fim_de_semana = 2;
                }
            }else if($dia_semana_q_ira_vencer == 0) {//Domingo ...
                /*Fornecedor "SECRETARIA DA RECEITA FEDERAL - 899" ou "CAIXA ECONOMICA FEDERAL 942 - com a Conta FGTS" tem que pagar 
                no dia �til anterior "Se domingo ent�o Sexta-Feira" ...*/
                if($fornecedores_clientes_especiais == 'S') {
                    $incremento_fim_de_semana = -2;
                }else {//Adiciono mais 2 dias ... Porque esta s� ser� paga no pr�ximo dia �til "Segunda-Feira" ...
                    $incremento_fim_de_semana = 1;
                }
            }
            $data_pagamento = data::adicionar_data_hora(data::datetodata($data_pagamento, '/'), $incremento_fim_de_semana);
            $data_pagamento = data::datatodate($data_pagamento, '-');

            $data_pagamento_foi_alterada = 'SIM';
        }

        if($data_pagamento_foi_alterada == 'SIM') {//Enquanto a vari�vel $data_pagamento_foi_alterada for alterada continua chamando a fun��o ...
            $indice++;
            self::nova_data_vencimento_alterada($data_pagamento, $fornecedores_clientes_especiais, $indice);
        }else {//Mais do que garantido que a $data_pagamento est� correta por n�o cair em feriado ou fim de semana, ent�o posso retorn�-la ...
            /*Tive que colocar essa vari�vel data_vencimento_alterada como $GLOBALS para que a fun��o atualizar_data_alterada que chamou essa 
            fun��o conseguisse enxergar o seu retorno, isso acontece devido eu trabalhar com o lance da recursividade ...*/
            $GLOBALS['data_vencimento_alterada'] = $data_pagamento;
            //J� posso retornar a $data_vencimento_alterada porque n�o � feriado e tamb�m n�o � fim de semana ...
            return $GLOBALS['data_vencimento_alterada'];
        }
    }
    
    /**********************************************************************/
    /****************************Contas � Receber**************************/
    /**********************************************************************/
    /*Esse par�metro $data_pagamento, n�o � nada mais nada menos do que a $data_vencimento_alterada da Conta � Receber; 
    data em que que o Cliente vai nos pagar ...*/
    function nova_data_recebimento($data_pagamento, $id_tipo_recebimento, $indice) {
        $data_pagamento_foi_alterada = 'N�O';//A princ�pio = N�O porque acabamos de entrar na fun��o ...
        
        /*Esses tipos de recebimentos do vetor abaixo, prorrogam a $data_recebimento da Conta � Receber fazendo com essa data n�o seja identifica 
        a Data de Vencimento Alterada ...

        3 - Cobran�a Simples, 7 Protestado, 9 Cart�rio, 11 Cobran�a Caucionada, 12 Desconto ...*/
        $vetor_tipos_recebimentos = array(3, 7, 9, 11, 12);
        
        //1) Feriado ...
        //A primeira coisa que eu fa�o � verificar se essa "$data_pagamento" cai em um feriado ...
        $sql = "SELECT `id_feriado` 
                FROM `feriados` 
                WHERE `data_feriado` = '$data_pagamento' LIMIT 1 ";
        $campos_feriado = bancos::sql($sql);
        if(count($campos_feriado) == 1) {//� feriado ...
            //Adiciono mais 1 dia ... Porque na teoria o cliente s� vai pagar a Empresa no pr�ximo dia �til "Ter�a-Feira" por exemplo ...
            $data_pagamento     = data::adicionar_data_hora(data::datetodata($data_pagamento, '/'),  1);
            $data_pagamento     = data::datatodate($data_pagamento, '-');

            $data_pagamento_foi_alterada = 'SIM';

            if(in_array($id_tipo_recebimento, $vetor_tipos_recebimentos)) {
                //Automaticamente a $data_recebimento � a pr�pria $data_pagamento + 1 porque o cliente paga em um dia e cai em conta no outro ...
                $data_recebimento   = data::adicionar_data_hora(data::datetodata($data_pagamento, '/'),  1);
                $data_recebimento   = data::datatodate($data_recebimento, '-');
            }else {//Nesses casos a $data_recebimento � a pr�pria $data_pagamento ...
                $data_recebimento   = $data_pagamento;
            }
        }
        /*****************************Fim de Semana****************************/
        //Agora, independente de acima ser feriado ou n�o, verifico se essa "$data_recebimento" da Conta cai em um s�bado ou domingo ...
        $dia_vencimento             = substr($data_pagamento, 8, 2);
        $mes_vencimento             = substr($data_pagamento, 5, 2);
        $ano_vencimento             = substr($data_pagamento, 0, 4);
        $dia_semana_q_ira_vencer    = date('w', mktime(0, 0, 0, $mes_vencimento, $dia_vencimento, $ano_vencimento));

        if($dia_semana_q_ira_vencer == 6 || $dia_semana_q_ira_vencer == 0) {//S�bado ou Domingo ...
            if($dia_semana_q_ira_vencer == 6) {//S�bado ...
                $incremento_fim_de_semana = 2;
            }else if($dia_semana_q_ira_vencer == 0) {//Domingo ...
                $incremento_fim_de_semana = 1;
            }
            $data_pagamento = data::adicionar_data_hora(data::datetodata($data_pagamento, '/'), $incremento_fim_de_semana);
            $data_pagamento = data::datatodate($data_pagamento, '-');

            $data_pagamento_foi_alterada = 'SIM';

            if(in_array($id_tipo_recebimento, $vetor_tipos_recebimentos)) {
                //Automaticamente a $data_recebimento � a pr�pria $data_pagamento + 1 porque o cliente paga em um dia e cai em conta no outro ...
                $data_recebimento   = data::adicionar_data_hora(data::datetodata($data_pagamento, '/'),  1);
                $data_recebimento   = data::datatodate($data_recebimento, '-');
            }else {//Nesses casos a $data_recebimento � a pr�pria $data_pagamento ...
                $data_recebimento   = $data_pagamento;
            }
        }

        //Foi feita toda uma verifica��o em cima da $data_pagamento que esta realmente ok, preciso verificar agora a $data_recebimento ...
        if($indice == 0) {
            /*Se aconteceu de n�o ter a $data_recebimento ent�o foi porque a $data_pagamento mais acima n�o caiu nem em feriado 
            e nem em fim de semana, mais sempre temos que ter uma $data_recebimento ...*/
            if($data_pagamento_foi_alterada == 'N�O') {
                if(in_array($id_tipo_recebimento, $vetor_tipos_recebimentos)) {
                    //Automaticamente a $data_recebimento � a pr�pria $data_pagamento + 1 porque o cliente paga em um dia e cai em conta no outro ...
                    $data_recebimento   = data::adicionar_data_hora(data::datetodata($data_pagamento, '/'),  1);
                    $data_recebimento   = data::datatodate($data_recebimento, '-');
                }else {
                    /*Automaticamente a $data_recebimento � a pr�pria $data_pagamento, mas nesse caso eu N�O preciso somar + 1, porque o Tipo de 
                    Recebimento dessa conta representa que o Cliente est� pagando a empresa no mesmo dia ...*/
                    $data_recebimento   = $data_pagamento;
                }
            }
            $indice++;
            self::nova_data_recebimento($data_recebimento, $id_tipo_recebimento, $indice);
        }else {//Verificando a $data_recebimento ...
            $data_recebimento = $data_pagamento;

            if($data_pagamento_foi_alterada == 'SIM') {//Enquanto a vari�vel $data_pagamento_foi_alterada for alterada continua chamando a fun��o ...
                $indice++;
                self::nova_data_recebimento($data_recebimento, $id_tipo_recebimento, $indice);
            }else {//Mais do que garantido que a $data_recebimento est� correta por n�o cair em feriado ou fim de semana, ent�o posso retorn�-la ...
                /*Tive que colocar essa vari�vel data_recebimento como $GLOBALS para que a fun��o atualizar_data_alterada que chamou essa 
                fun��o conseguisse enxergar o seu retorno, isso acontece devido eu trabalhar com o lance da recursividade ...*/
                $GLOBALS['data_recebimento'] = $data_recebimento;
                //J� posso retornar a $data_vencimento_alterada porque n�o � feriado e tamb�m n�o � fim de semana ...
                return $GLOBALS['data_recebimento'];
            }
        }
    }
}
?>