<?
if(!class_exists('bancos')) require 'bancos.php';//CASO EXISTA EU DESVIO A CLASSE ...
class faturamentos {
/*
 * Caso eu não tenho id_nf, então o default dos parâmetros é id_nf = 0, 

 * cmb_status_nota_fiscal = 1 p/ forçar entrar no if da linha 33 abaixo 
 * historico_cliente_em_js = 1 p/ mostrar o retorno de histórico dentro de um "alert, confirm" de JavaScript 
ao invés de printar na tela em HTML ...*/
    function analise_credito_cliente($id_cliente, $id_nf = 0, $cmb_status_nota_fiscal = 1) {
        $data_atual = date('Y-m-d h:i:s');
        if(empty($id_nf)) {//Foi acessada de algum outro lugar, exemplo Gerenciar, Mandar Vale
            $sql = "SELECT `credito`, `limite_credito`, `id_pais` 
                    FROM `clientes` 
                    WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
            $campos             = bancos::sql($sql);
            $limite_credito     = $campos[0]['limite_credito'];
            $credito 		= $campos[0]['credito'];
            $id_pais 		= $campos[0]['id_pais'];
        }else {//Significa que eu acessei essa Função através de uma Nota Fiscal ...
            $sql = "SELECT c.`credito`, c.`limite_credito`, c.`id_pais`, nfs.`status` 
                    FROM `nfs` 
                    INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
                    WHERE nfs.`id_nf` = '$id_nf' LIMIT 1 ";
            $campos  		= bancos::sql($sql);
            $credito 		= $campos[0]['credito'];
            $limite_credito     = $campos[0]['limite_credito'];
            $id_pais 		= $campos[0]['id_pais'];
            $status_cliente     = $campos[0]['status'];
        }

        if((int)$cmb_status_nota_fiscal > (int)$status_cliente) {
            if($credito == 'B') {//Se o credito do cliente for B eu analizo o limite dele
                if(!class_exists('financeiros'))    require 'financeiros.php';//CASO EXISTA EU DESVIO A CLASSE ...
                if(!class_exists('genericas'))      require 'genericas.php';//CASO EXISTA EU DESVIO A CLASSE ...
                $dolar_dia = genericas::moeda_dia('dolar');
                ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                //Aqui busca as contas que são importadas diretamente do faturamento, são as duplicatas não pagas
                $sql = "SELECT DISTINCT(id_conta_receber) 
                        FROM `contas_receberes` 
                        WHERE `id_cliente` = '$id_cliente' 
                        AND `status` < '2' 
                        AND `ativo` = '1' ORDER BY id_conta_receber ";
                $campos = bancos::sql($sql);
                $linhas = count($campos);
                for($l = 0; $l < $linhas; $l++) $id_contas[] = $campos[$l]['id_conta_receber'];
                //Arranjo Ténico
                if(count($id_contas) == 0) $id_contas[] = '0';
                for($i = 0; $i < count($id_contas); $i++) {
                    $calculos_conta_receber = financeiros::calculos_conta_receber($id_contas[$i]);
                    $contas_a_receber+= $calculos_conta_receber['valor_reajustado'];
                }
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                //Aqui busca todas as NF(s) Faturando ou em Aberto, desde que não tenha sido importada p/ o Financ
                $sql = "SELECT id_nf, suframa, id_empresa id_empresa_nota, status 
                        FROM `nfs` 
                        WHERE `id_cliente` = '$id_cliente' 
                        AND `importado_financeiro` = 'N' 
                        AND `status` < '5' 
                        AND (data_emissao >= '2006-05-01' OR data_emissao = '0000-00-00') ";
                $campos_nfs = bancos::sql($sql);
                $linhas_nfs = count($campos_nfs);
                for($i = 0; $i < $linhas_nfs; $i++) {
                    /*Se foi passado um $id_nf por parâmetro, eu somo todas as NF´s do Cliente exceto essa que foi passada, porque 
                    só me interessa contabilizar tudo o que está sendo faturada exceto essa NF*/
                    if(empty($id_nf) || (!empty($id_nf) && $id_nf != $campos_nfs[$i]['id_nf'])) {
                        $calculo_total_impostos = calculos::calculo_impostos(0, $campos_nfs[$i]['id_nf'], 'NF');
                        $total_faturando+= $calculo_total_impostos['valor_total_nota'];
                    }
                    //$total_faturando+= $calculo_total_impostos['valor_total_nota'];//Retorna todas as NF´s do Cliente inclusive a que foi passada por parâmetro ...
                }
                $icms_st_ipi_perc = 20;//Aqui estamos estimando que esses impostos em uma NF dariam aí no máximo 20% ...
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                //Todos os Vales que ainda não estão em NFs em Aberto que ainda não estão no financeiro ...
                $sql = "SELECT IF($id_pais = '31', SUM(pvi.`preco_liq_final` * pvi.`vale`), SUM(pvi.`preco_liq_final` * pvi.`vale`) * $dolar_dia) AS total_vale 
                        FROM `pedidos_vendas` pv 
                        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`vale` > '0' 
                        WHERE pv.`id_cliente` = '$id_cliente' 
                        AND pv.`status` < '2' ";
                $campos_pedidos = bancos::sql($sql);
                $total_vale     = round($campos_pedidos[0]['total_vale'] * (1 + $icms_st_ipi_perc / 100), 2);
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                //Todos os Pedidos à Faturar em Aberto / Parcial que ainda não estão no financeiro, independente de ter vale ...
                $sql = "SELECT IF($id_pais = '31', SUM(pvi.`preco_liq_final` * (pvi.`qtde` - pvi.`qtde_faturada`)), SUM(pvi.`preco_liq_final` * (pvi.`qtde` - pvi.`qtde_faturada`)) * $dolar_dia) AS total_pedidos_abertos 
                        FROM `pedidos_vendas` pv 
                        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`status` < '2' 
                        WHERE pv.`id_cliente` = '$id_cliente' 
                        AND pv.`status` < '2' ";
                $campos_pedidos                 = bancos::sql($sql);
                $total_pedidos_abertos          = round($campos_pedidos[0]['total_pedidos_abertos'] * (1 + $icms_st_ipi_perc / 100), 2);
/*O Crédito Comprometido não incorpora o Total de Pedido, mas é Feito esse SQL a parte p/ que o Usuário que for 
faturar tome ciência de quanto que o Cliente tem a Faturar ...*/
                $credito_comprometido           = $contas_a_receber + $total_faturando + $total_vale;
                $tolerancia_cliente	  	= $limite_credito * 1.1;
                $credito_disponivel		= $tolerancia_cliente - $credito_comprometido;
                $font = ($credito_disponivel < 0) ? '<font color="red">' : '<font color="darkgreen">';
                $historico_cliente = 'A) LIMITE DE CRÉDITO = R$ '.number_format($limite_credito, 2, ',', '.').'<br>B) TOLERÂNCIA DO CLIENTE (A + 10%) = R$ '.number_format($tolerancia_cliente, 2, ',', '.').'<br>C) CONTAS À RECEBER = R$ '.number_format($contas_a_receber, 2, ',', '.').'<br>D) FATURANDO (EXCLUSA ESTA NF) = R$ '.number_format($total_faturando, 2, ',', '.').'<br>E) TOTAL DE VALE + ICMS ST + IPI (+ 20%) = R$ '.number_format($total_vale, 2, ',', '.').'<br>F) CRÉDITO COMPROMETIDO (C + D + E) = R$ '.number_format($credito_comprometido, 2, ',', '.').$font.'</font><br>I) TOTAL DE PEDIDO(S) EM ABERTO + VALES + ICMS ST + IPI (+ 20%) = R$ '.number_format($total_pedidos_abertos, 2, ',', '.');
                $historico_cliente_em_js = 'A) LIMITE DE CRÉDITO = R$ '.number_format($limite_credito, 2, ',', '.').'\nB) TOLERÂNCIA DO CLIENTE (A + 10%) = R$ '.number_format($tolerancia_cliente, 2, ',', '.').'\nC) CONTAS À RECEBER = R$ '.number_format($contas_a_receber, 2, ',', '.').'\nD) FATURANDO (EXCLUSA ESTA NF) = R$ '.number_format($total_faturando, 2, ',', '.').'\nE) TOTAL DE VALE + ICMS ST + IPI (+ 20%) = R$ '.number_format($total_vale, 2, ',', '.').'\nF) CRÉDITO COMPROMETIDO (C + D + E) = R$ '.number_format($credito_comprometido, 2, ',', '.');

                //Crédito Comprometido - É o valor retornado referente a todos as pendências Financeira do Cliente ...
                //Limite de Crédito é o valor de Crédito normal que Cliente tem p/ Comprar com a Albafer ...
                //Tolerância de Crédito é o valor que o Cliente tem de Crédito + os 10% do Financeiro ...
                //Crédito Disponível é o valor Saldo que o Cliente ainda tem p/ Gastar ou Deve p/ a Albafer ...
                //Total de Pedidos em Aberto - É o total de Pendências do Cliente ... 
                //Contas à Receber - É o que o Cliente ainda deve para nós ...
                //Faturando - NF´s não importadas ainda pelo Financeiro ...

                return array('credito' => $credito, 'credito_comprometido' => $credito_comprometido, 'limite_credito' => $limite_credito, 'tolerancia_cliente' => $tolerancia_cliente, 'credito_disponivel' => $credito_disponivel, 'total_pedidos_abertos'=> $total_pedidos_abertos, 'contas_a_receber' => $contas_a_receber, 'faturando' => $faturando, 'total_vale' => $total_vale, 'historico_cliente' => $historico_cliente, 'historico_cliente_em_js' => $historico_cliente_em_js);
            }else if ($credito == 'C') {//mando uma msg que nao poderar mudar status da nf para faturada e nem liberada para faturar
                return array('credito' => $credito, 'credito_comprometido' => '0', 'limite_credito' => '0', 'tolerancia_cliente' => '0', 'credito_disponivel' => '0', 'total_pedidos_abertos'=> '0', 'contas_a_receber' => '0', 'faturando' => '0', 'total_vale' => '0', 'historico_cliente' => 'CRÉDITO BLOQUEADO PELO FINANCEIRO !', 'historico_cliente_em_js' => 'CRÉDITO BLOQUEADO PELO FINANCEIRO !');
            }else if ($credito == 'D') {
                return array('credito' => $credito, 'credito_comprometido' => '0', 'limite_credito' => '0', 'tolerancia_cliente' => '0', 'credito_disponivel' => '0', 'total_pedidos_abertos'=> '0', 'contas_a_receber' => '0', 'faturando' => '0', 'total_vale' => '0', 'historico_cliente' => 'CRÉDITO BLOQUEADO PELO ERP - MOTIVO: NUNCA COMPROU OU ÚLTIMA COMPRA SUPERIOR A UM ANO !', 'historico_cliente_em_js' => 'CRÉDITO BLOQUEADO PELO ERP - MOTIVO: NUNCA COMPROU OU ÚLTIMA COMPRA SUPERIOR A UM ANO !');
            }else {
                return array('credito' => $credito, 'credito_comprometido' => '0', 'limite_credito' => '0', 'tolerancia_cliente' => '0', 'credito_disponivel' => '0', 'contas_a_receber' => '0', 'total_pedidos_abertos'=> '0', 'faturando' => '0', 'total_vale' => '0', 'historico_cliente' => 'OK', 'historico_cliente_em_js' => 'OK');// siginifica q está OK
            }
        }else {
            return array('credito' => $credito, 'credito_comprometido' => '0', 'tolerancia_cliente' => '0', 'credito_disponivel' => '0', 'total_pedidos_abertos'=> '0', 'contas_a_receber' => '0', 'faturando' => '0', 'total_vale' => '0', 'historico_cliente' => 'OK', 'historico_cliente_em_js' => 'OK');// siginifica q está OK
        }
    }

/******************************************************************************************************/
/****Métodos que controlam o Talonário de NF(s) de Venda ou NF(s) Outra(s) independente da Empresa*****/
/******************************************************************************************************/	
//Tipo_nf = 'S', 'D' então busca na Tab. de Notas de Saída, Devolução - Tipo_nf = 'O' então busca na Tab. Outras ...
    function buscar_numero_nf($id_nf_parametro, $tipo_nf = 'S') {//Como a maioria é NF de Saída joguei padrão S ...
        if($tipo_nf == 'S' || $tipo_nf == 'D') {
            $sql = "SELECT id_nf_num_nota, snf_devolvida, status 
                    FROM `nfs` 
                    WHERE `id_nf` = '$id_nf_parametro' LIMIT 1 ";
            $campos_nf_saida_dev = bancos::sql($sql);
            //Busca Natural do Número da NF independente de a NF for de Saída ou de devolução ...
            $sql = "SELECT numero_nf 
                    FROM `nfs_num_notas` 
                    WHERE `id_nf_num_nota` = '".$campos_nf_saida_dev[0]['id_nf_num_nota']."' LIMIT 1 ";
            $campos_numero_talonario = bancos::sql($sql);
            if($campos_nf_saida_dev[0]['status'] == 6) {//Se for uma NF de Devolução então ...
                $numero_nf = ($campos_numero_talonario[0]['numero_nf'] > 0) ? $campos_numero_talonario[0]['numero_nf'] : $campos_nf_saida_dev[0]['snf_devolvida'];
            }else {//Se for uma NF de Saída então ...
                $numero_nf = $campos_numero_talonario[0]['numero_nf'];
            }
//Provavelmente então, este esse Número foi utilizado por uma NF Outra ...
        }else if($tipo_nf == 'O') {
//Verifico se esse o id_nf_parametro está relacionado com uma NF de Saída ou Devolução ...
            $sql = "SELECT id_nf_num_nota 
                    FROM `nfs_outras` 
                    WHERE `id_nf_outra` = '$id_nf_parametro' LIMIT 1 ";
            $campos_nf_outra = bancos::sql($sql);
//Busca Natural do Número da NF independente de a NF for de Saída ou de devolução ...
            $sql = "SELECT numero_nf 
                    FROM `nfs_num_notas` 
                    WHERE `id_nf_num_nota` = '".$campos_nf_outra[0]['id_nf_num_nota']."' LIMIT 1 ";
            $campos_numero_talonario = bancos::sql($sql);			
            $numero_nf = $campos_numero_talonario[0]['numero_nf'];
        }
        return $numero_nf;
    }

    function gerar_numero_nf($id_empresa, $id_nf_num_nota = 0, $prestacao_servico = 'N') {
/*O Talonário tanto da Empresa Albafer quanto o da Empresa Tool Master já foram reiniciados há tempos 
atrás por causa que mudou o Formulário de Impressão ...*/
        $fase = 1;//Campo fase = 1 significa que a contagem foi reiniciada
/*O Talonário da Empresa 
* Tool Master foi reiniciado por causa da NFe no dia 01/04/2010 ...
* Albafer foi reiniciado por causa da NFe no dia 01/07/2010*/
        if($id_empresa == 1 || $id_empresa == 2) $fase = 2;
/*A empresa "K2" teve a sua numeração inicial como sendo N.º 386 porque as outras 385 NFs anteriores foram 
feitas manualmente, por fora de nosso ERP ...*/
        if(!empty($id_nf_num_nota)) {//O usuário está mudando o N.º da NF no cabeçalho da NF
            $sql = "UPDATE `nfs_num_notas` SET nota_usado = '1' WHERE `id_nf_num_nota` = '$id_nf_num_nota' LIMIT 1 ";
            bancos::sql($sql);
            return $id_nf_num_nota;
        }else {
            /***********************Prestação de Serviço***********************/
            if($prestacao_servico == 'S') {//Significa que o Sys está trabalhando com uma NF de Prestação de Serviço ...
                //Busca o 1º N.º de Prestação de Serviço que está livre ...
                $sql = "SELECT id_nf_num_nota, numero_nf 
                        FROM `nfs_num_notas` 
                        WHERE `id_empresa` = '$id_empresa' 
                        AND `nota_usado` = '0' 
                        AND `prestacao_servico` = 'S' 
                        AND `fase` = '$fase' ORDER BY numero_nf LIMIT 1 ";
                $campos_nf_livre = bancos::sql($sql);
                if(count($campos_nf_livre) == 0) {//Não existe nenhum N.º disponível, sendo assim vou add + 10 N.ºs ...
                    //Busco o último N.º que foi gerado e que foi utilizado pelo Usuário ...
                    $sql = "SELECT numero_nf 
                            FROM `nfs_num_notas` 
                            WHERE `id_empresa` = '$id_empresa' 
                            AND `nota_usado` = '1' 
                            AND `prestacao_servico` = 'S' 
                            AND `fase` = '$fase' ORDER BY numero_nf DESC LIMIT 1 ";
                    $campos     = bancos::sql($sql);
                    //Se ainda não existe nenhum N.º crio o 1º ...
                    $numero_nf  = (count($campos) == 0) ? 1 : ((integer)$campos[0]['numero_nf']) + 1;
                    for ($i = 0; $i < 10; $i++) {//Gero uma sequência de mais 10 NFs ...
                        //Para ñ gerar um N.º de NF disponível existente na Empresa do parâmetro, faço essa segurança ...
                        $sql = "SELECT id_nf_num_nota 
                                FROM `nfs_num_notas` 
                                WHERE `id_empresa` = '$id_empresa' 
                                AND `numero_nf` = '$numero_nf' 
                                AND `nota_usado` = '0' 
                                AND `prestacao_servico` = 'S' 
                                AND `fase` = '$fase' LIMIT 1 ";
                        $campos_numero_nf  = bancos::sql($sql);
                        if(count($campos_numero_nf) == 0) {//Esse N.º ainda não foi gerado, posso gerá-lo com segurança ...
                            $sql = "INSERT INTO `nfs_num_notas` (`id_nf_num_nota`, `id_empresa`, `numero_nf`, `nota_usado`, `prestacao_servico`, `fase`) VALUES (NULL, '$id_empresa', '$numero_nf', 0, 'S', '$fase') ";
                            bancos::sql($sql);
                            //Aqui eu guardo nessa variável o 1º novo N.º gerado disponível p/ retornar para o usuário ...
                            if($i == 0) $id_nf_num_nota = bancos::id_registro();
                            $numero_nf++;
                        }
                    }
                    return $id_nf_num_nota;
                }else {
                    return $campos_nf_livre[0]['id_nf_num_nota'];
                }
            /*******************NF Saída, Devolução, Outras********************/
            }else {//NF Saída, Devolução, Outras ...
                //Busca o 1º N.º de NF que está livre ...
                $sql = "SELECT id_nf_num_nota, numero_nf 
                        FROM `nfs_num_notas` 
                        WHERE `id_empresa` = '$id_empresa' 
                        AND `nota_usado` = '0' 
                        AND `prestacao_servico` = 'N' 
                        AND `fase` = '$fase' ORDER BY numero_nf LIMIT 1 ";
                $campos_nf_livre = bancos::sql($sql);
                if(count($campos_nf_livre) == 0) {//Não existe nenhum N.º disponível, sendo assim vou add + 10 N.ºs ...
                    //Busco o último N.º que foi gerado e que foi utilizado pelo Usuário ...
                    $sql = "SELECT numero_nf 
                            FROM `nfs_num_notas` 
                            WHERE `id_empresa` = '$id_empresa' 
                            AND `nota_usado` = '1' 
                            AND `prestacao_servico` = 'N' 
                            AND `fase` = '$fase' ORDER BY numero_nf DESC LIMIT 1 ";
                    $campos     = bancos::sql($sql);
                    //Se ainda não existe nenhum N.º crio o 1º ...
                    $numero_nf  = (count($campos) == 0) ? 1 : ((integer)$campos[0]['numero_nf']) + 1;
                    for ($i = 0; $i < 10; $i++) {//Gero uma sequência de mais 10 NFs ...
                        //Para ñ gerar um N.º de NF disponível existente na Empresa do parâmetro, faço essa segurança ...
                        $sql = "SELECT id_nf_num_nota 
                                FROM `nfs_num_notas` 
                                WHERE `id_empresa` = '$id_empresa' 
                                AND `numero_nf` = '$numero_nf' 
                                AND `nota_usado` = '0' 
                                AND `prestacao_servico` = 'N' 
                                AND `fase` = '$fase' LIMIT 1 ";
                        $campos_numero_nf  = bancos::sql($sql);
                        if(count($campos_numero_nf) == 0) {//Esse N.º ainda não foi gerado, posso gerá-lo com segurança ...
                            $sql = "INSERT INTO `nfs_num_notas` (`id_nf_num_nota`, `id_empresa`, `numero_nf`, `nota_usado`, `fase`) VALUES (NULL, '$id_empresa', '$numero_nf', 0, '$fase') ";
                            bancos::sql($sql);
                            //Aqui eu guardo nessa variável o 1º novo N.º gerado disponível p/ retornar para o usuário ...
                            if($i == 0) $id_nf_num_nota = bancos::id_registro();
                            $numero_nf++;
                        }
                    }
                    return $id_nf_num_nota;
                }else {
                    return $campos_nf_livre[0]['id_nf_num_nota'];
                }
            }
        }
    }
    
    function verificar_numero_disponivel($id_empresa_pedido) {
        $verificar_numero_sgd = 'S';
        while($verificar_numero_sgd == 'S') {
            //O sistema irá me retornar o primeiro N.º SGD que está disponível ...
            $id_nf_num_nota = faturamentos::gerar_numero_nf($id_empresa_pedido);

            /*Por segurança eu verifico se esse N.º que o sistema disse que está disponível, 
            realmente já não foi utilizado por uma Outra Nota Fiscal ...*/
            $sql = "SELECT `id_nf` 
                    FROM `nfs` 
                    WHERE `id_nf_num_nota` = '$id_nf_num_nota' LIMIT 1 ";
            $campos_nf_ja_usando = bancos::sql($sql);
            /*Significa que este N.º do Talonário já está em uso por alguma Nota Fiscal SGD, 
            consequentemente o sistema retornou um N.º errado ...*/
            if(count($campos_nf_ja_usando) == 1) {
                //Aqui eu seto o N.º da Nota Fiscal do Talonário como já sendo usado ...
                faturamentos::gerar_numero_nf($id_empresa_pedido, $id_nf_num_nota, 'N');
            }else {//Realmente este N.º do Talonário se encontra disponível, posso trabalhar com ele ...
                $verificar_numero_sgd   = 'N';
            }
        }
        return $id_nf_num_nota;
    }

/*Essa função tem por objetivo buscar o N.º Anterior e Posterior ao do N.º que foi selecionado 
no Talonário pelo usuário no cabeçalho de NF - ela serve tanto p/ as NFs de Venda quanto p/ as NFs Outras ...*/
    function buscar_numero_ant_post_talonario($id_num_nota_fiscal) {
//Aqui eu busco a Empresa e o N.º de NF do "$id_num_nota_fiscal", passado por parâmetro ...
        $sql = "SELECT id_empresa AS id_empresa_nota, numero_nf, prestacao_servico 
                FROM `nfs_num_notas` 
                WHERE `id_nf_num_nota` = '$id_num_nota_fiscal' LIMIT 1 ";
        $campos 		= bancos::sql($sql);
        $id_empresa_nota 	= $campos[0]['id_empresa_nota'];
        $numero_nf 		= $campos[0]['numero_nf'];
        $prestacao_servico      = $campos[0]['prestacao_servico'];
/*O Talonário tanto da Empresa Albafer quanto o da Empresa Tool Master já foram reiniciados há tempos 
atrás por causa que mudou o Formulário de Impressão ...*/
        $fase = 1;//Campo fase = 1 significa que a contagem foi reiniciada
/*O Talonário da Empresa 
* Tool Master foi reiniciado por causa da NFe no dia 01/04/2010 ...
* Albafer foi reiniciado por causa da NFe no dia 01/07/2010*/
        if($id_empresa_nota == 1 || $id_empresa_nota == 2) $fase = 2;
//Busca do N.º Anterior em relação ao N.º de NF que foi selecionado pelo usuário na Combo ...
        $sql = "SELECT id_nf_num_nota AS id_nf_num_nota_anterior, numero_nf AS numero_nf_anterior 
                FROM `nfs_num_notas` 
                WHERE `numero_nf` < '$numero_nf' 
                AND `id_empresa` = '$id_empresa_nota' 
                AND `nota_usado` = '1' 
                AND `prestacao_servico` = '$prestacao_servico' 
                AND `fase` = '$fase' ORDER BY numero_nf DESC LIMIT 1 ";
        $campos_anterior = bancos::sql($sql);
//Caso não encontre nada, então eu igualo a variável Data de Emissão = "Data Atual" p/ não dar erro de JS ...
        if(count($campos_anterior) == 0) {
            $numero_nf_anterior = 0;
            $data_emissao_anterior = date('Y-m-d');
        }else {//Se eu encontrar um N.º de NF anterior ao do selecionado, busco a Data em que utilizei esse N.º
            $id_nf_num_nota_anterior = $campos_anterior[0]['id_nf_num_nota_anterior'];
//Verifico se esse N.º Anterior foi utilizado em uma NF de Vendas comum ...
            $sql = "SELECT data_emissao AS data_emissao_anterior 
                    FROM `nfs` 
                    WHERE `id_nf_num_nota` = '$id_nf_num_nota_anterior' LIMIT 1 ";
            $campos_nf = bancos::sql($sql);
            if(count($campos_nf) == 0) {
//Se não foi usado em uma NF de Venda, então com certeza foi utilizado nas Outras NFs ...
                $sql = "SELECT data_emissao AS data_emissao_anterior 
                        FROM `nfs_outras` 
                        WHERE `id_nf_num_nota` = '$id_nf_num_nota_anterior' LIMIT 1 ";
                $campos_nf = bancos::sql($sql);
            }
            $data_emissao_anterior  = $campos_nf[0]['data_emissao_anterior'];
            $numero_nf_anterior     = $campos_anterior[0]['numero_nf_anterior'];
        }
//Busca do N.º Posterior em relação ao N.º de NF que foi selecionado pelo usuário na Combo ...
        $sql = "SELECT id_nf_num_nota AS id_nf_num_nota_posterior, numero_nf AS numero_nf_posterior 
                FROM `nfs_num_notas` 
                WHERE `numero_nf` > '$numero_nf' 
                AND `id_empresa` = '$id_empresa_nota' 
                AND `nota_usado` = '1' 
                AND `prestacao_servico` = '$prestacao_servico' 
                AND `fase` = '$fase' ORDER BY numero_nf LIMIT 1 ";
        $campos_posterior = bancos::sql($sql);
//Caso não encontre nada, então eu igualo a variável Data de Emissão = '' p/ não dar erro de JS ...
        if(count($campos_posterior) == 0) {
            $numero_nf_posterior = '';
            $data_emissao_posterior = '';
        }else {//Se eu encontrar um N.º de NF posterior ao do selecionado, busco a Data em que utilizei esse N.º
            $id_nf_num_nota_posterior = $campos_posterior[0]['id_nf_num_nota_posterior'];
//Verifico se esse N.º Posterior foi utilizado em uma NF de Vendas comum ...
            $sql = "SELECT data_emissao AS data_emissao_posterior 
                    FROM `nfs` 
                    WHERE `id_nf_num_nota` = '$id_nf_num_nota_posterior' LIMIT 1 ";
            $campos_nf = bancos::sql($sql);
            if(count($campos_nf) == 0) {
//Se não foi usado em uma NF de Venda, então com certeza foi utilizado nas Outras NFs ...
                $sql = "SELECT data_emissao AS data_emissao_posterior 
                        FROM `nfs_outras` 
                        WHERE `id_nf_num_nota` = '$id_nf_num_nota_posterior' LIMIT 1 ";
                $campos_nf = bancos::sql($sql);
            }
            $data_emissao_posterior = $campos_nf[0]['data_emissao_posterior'];
            $numero_nf_posterior    = $campos_posterior[0]['numero_nf_posterior'];
        }
        return array('data_emissao_anterior' => $data_emissao_anterior, 'numero_nf_anterior' => $numero_nf_anterior, 'data_emissao_posterior' => $data_emissao_posterior, 'numero_nf_posterior' => $numero_nf_posterior);
    }
/******************************************************************************************************/
/******************************************************************************************************/
/******************************************************************************************************/

    function calculo_peso_nf($id_nfs, $peso_liq_total_nf = 0) {//Calcula o peso bruto e liq  da NFS.   *****   cuidado função recursiva
        //Aqui eu busco alguns dados da Nota Fiscal passada por parâmetro ...
        $sql = "SELECT c.`id_pais`, nfs.`id_cliente`, nfs.`id_packing_list`, nfs.`status` 
                FROM `nfs` 
                INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
                WHERE `id_nf` = '$id_nfs' LIMIT 1 ";
        $campos_nf = bancos::sql($sql);

        if($campos_nf[0]['id_pais'] != 31) {//País Estrangeiro / Internacional ...
            //Nesse caso, eu só leio dados do Packing List ...
            $sql = "SELECT `qtde_caixas`, `peso_bruto`, `peso_liquido`, `volume` 
                    FROM `packings_lists` 
                    WHERE `id_packing_list` = '".$campos_nf[0]['id_packing_list']."' LIMIT 1 ";
            $campos_packing_list = bancos::sql($sql);
            
            $qtde_caixas                = $campos_packing_list[0]['qtde_caixas'];
            $peso_bruto_total           = $campos_packing_list[0]['peso_bruto'];
            $peso_total_nf_current      = 0;
            $peso_total_emb_nf_current  = 0;
            $peso_liq_total_nf          = $campos_packing_list[0]['peso_liquido'];
            $especie                    = 'CAIXA DE MADEIRA';
        }else {//Nota Fiscal Daqui do Brasil ...
            $sql = "SELECT 
                    IF(nfs.`status` = '6', SUM(nfsi.`qtde_devolvida` * pa.`peso_unitario`), SUM((nfsi.`qtde` - nfsi.`vale`) * pa.`peso_unitario`)) AS total_peso_nf 
                    FROM `nfs` 
                    INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = nfsi.`id_produto_acabado` 
                    WHERE nfs.`id_nf` = '$id_nfs' GROUP BY nfs.`id_nf` ";
            $campos_nfs_itens 	= bancos::sql($sql);
            $linhas_nfs_itens 	= count($campos_nfs_itens);
            if($linhas_nfs_itens > 0) {//Para garantir que tem q ter a marcação de uso de c/c no faturamento ...
                $peso_total_nf_current = $campos_nfs_itens[0]['total_peso_nf'];
                $peso_liq_total_nf+= $peso_total_nf_current;
            }else {
                $peso_total_nf_current	= 0;
            }
            //Agora eu pego as somas da NF com a Embalagem ...
            $sql = "SELECT SUM(nfspi.`qtde` * pi.`peso`) AS total, SUM(nfspi.`qtde`) AS qtde_caixas 
                    FROM `nfs` 
                    INNER JOIN `nfs_vs_pi_embalagens` nfspi ON nfspi.`id_nf` = nfs.`id_nf` 
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = nfspi.`id_produto_insumo` 
                    WHERE nfs.`id_nf` = '$id_nfs' ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
            if($linhas > 0) {//Pego o Peso Total das Embalagens ...
                $peso_total_emb_nf_current  = $campos[0]['total'];
                $qtde_caixas                = $campos[0]['qtde_caixas'];
            }else {
                $peso_total_emb_nf_current  = 0;
                $qtde_caixas                = 0;
            }
            $sql = "SELECT `id_nf` 
                    FROM `nfs` 
                    WHERE `id_cliente` = '".$campos_nf[0]['id_cliente']."' 
                    AND `id_nf_vide_nota` = '$id_nfs' ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
            for($i = 0; $i < $linhas; $i++) {
                $peso_nf            = faturamentos::calculo_peso_nf($campos[$i]['id_nf'], $peso_liq_total_nf);
                $peso_liq_total_nf  = $peso_nf['peso_liq_total_nf'];
            }
            $peso_bruto_total   = $peso_liq_total_nf + $peso_total_emb_nf_current;
            $especie            = '';
            //Aqui o Sistema busca todos os Tipos de Embalagens Utilizadas ...
            $sql = "SELECT DISTINCT(pi.`discriminacao`) AS embalagem 
                    FROM `nfs_vs_pi_embalagens` npe 
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = npe.`id_produto_insumo` 
                    WHERE npe.`id_nf` = '$id_nfs' ";
            $campos_pi = bancos::sql($sql);
            $linhas_pi = count($campos_pi);
            for($j = 0; $j < $linhas_pi; $j++) {
                if(strpos(strtoupper($campos_pi[$j]['embalagem']), 'VOLUME') > 0) {
                    $especie = 'VOLUMES - ';
                    break;//Para sair do Loop
                }else if(strpos(strtoupper($campos_pi[$j]['embalagem']), 'MADEIRA') > 0 && strpos($especie, 'MADEIRA') == 0) {
                    $especie.= 'CAIXA DE MADEIRA - ';
                }else if(strpos(strtoupper($campos_pi[$j]['embalagem']), 'PAPELAO') > 0 && strpos($especie, 'PAPELÃO') == 0) {
                    $especie.= 'CAIXA DE PAPELÃO - ';
                }
            }
            $especie = substr($especie, 0, strlen($especie) - 3);
        }
        return array('qtde_caixas'=>$qtde_caixas, 'peso_bruto_total'=>$peso_bruto_total, 'peso_total_nf_current'=>$peso_total_nf_current, 'peso_total_emb_nf_current'=>$peso_total_emb_nf_current, 'peso_liq_total_nf'=>$peso_liq_total_nf, 'especie'=>$especie);
    }

    function equilibrio_bancario() {//Equilíbra o faturamento da empresa mantendo 50% para cada empresa.
            return 2;//Enquanto não me derem aval para trocar os Bancos coloquei bradesco - 20/04/2011 ... - aniversário da simone ...
            $data_inicial 	= date('Y-m-01');
            $data_final		= date('Y-m-t');
            $sql = "SELECT b.id_banco 
                    FROM `bancos` b 
                    INNER JOIN `agencias` a ON a.id_banco = b.id_banco 
                    INNER JOIN `contas_correntes` cc ON cc.id_agencia = a.id_agencia AND cc.id_empresa = '4' AND cc.status_faturamento_sgd = '1' 
                    WHERE b.ativo = '1' ";
            $campos_bancos = bancos::sql($sql);
            $linhas_bancos = count($campos_bancos);
            if($linhas_bancos == 0) {//Se não existe nenhum Banco com essa Marcação ...
                    exit('O SISTEMA NÃO POSSUI MARCAÇÃO EM CONTA CORRENTE DE NENHUM BANCO DE EMPRESA GRUPO PARA FATURAR SGD !');
            }else if($linhas_bancos == 1) {//Se existir apenas um banco, retorna aquele Banco ...
                    return $campos_bancos[0]['id_banco'];
            }else {//Se existir mais de um banco marcado, o Sys tem de pegar o Banco menos utilizado do mês.
                    $sql = "SELECT nfs.id_banco, SUM(nfs.valor1 + nfs.valor2 + nfs.valor3 + nfs.valor4) total, b.banco 
                            FROM `nfs` 
                            INNER JOIN `bancos` b ON b.id_banco = nfs.id_banco AND b.ativo = '1' 
                            INNER JOIN `agencias` a ON a.id_banco = b.id_banco 
                            INNER JOIN `contas_correntes` cc ON cc.id_agencia = a.id_agencia AND cc.id_empresa = '4' AND cc.status_faturamento_sgd = '1' 
                            WHERE (nfs.data_emissao BETWEEN '$data_inicial' AND '$data_final' OR nfs.data_emissao = '0000-00-00') 
                            AND nfs.id_empresa = '4' GROUP BY b.id_banco ORDER BY total ";
                    $campos 	= bancos::sql($sql);
                    $linhas		= count($campos);
                    if($linhas == $linhas_bancos) {//Significa que todos estão na tabela relacional ...
                            return $campos[0]['id_banco'];//Retorna o BD com menor saldo para ser utilizado no faturamento ...
                    }else {
                            if($linhas == 1) {//Só tem um Banco ...
                                    $condicao = " AND b.id_banco <> ".$campos[0]['id_banco'];
                            }else {//Existe mais de 1 Banco ...
                                    if($linhas > 0) {
                                            for($i = 0; $i < $linhas;$i++) $id_banco.=$campos[$i]['id_banco'].',';
                                            $condicao = " AND b.id_banco NOT IN (".substr($id_banco, 0, (strlen($id_banco) - 1)).")";
                                    }
                            }
                            //Retorna o banco com menor saldo para ser utilizado no faturamento ...
                            $sql = "SELECT b.id_banco 
                                    FROM `bancos` b 
                                    INNER JOIN `agencias` a ON a.id_banco = b.id_banco 
                                    INNER JOIN `contas_correntes` cc ON cc.id_agencia = a.id_agencia AND cc.id_empresa = '4' AND cc.status_faturamento_sgd = '1' 
                                    WHERE b.ativo = '1' $condicao LIMIT 1 ";
                            $campos = bancos::sql($sql);
                            return $campos[0]['id_banco']; 
                    }
            }
    }
	
    //Função q verifica se a Nota está faturada, empacotada, despachada, cancelada
    //caso sim, então o usuário não pode + incluir, alterar ou excluir nenhum item
    function situacao_nota_fiscal($id_nf) {
        $sql = "SELECT status 
                FROM `nfs` 
                WHERE id_nf = '$id_nf' LIMIT 1 ";
        $campos = bancos::sql($sql);
        return $campos[0]['status'];
    }

    //Função q verifica se a Nota já foi importada p/ o Financeiro ...
    //caso sim, então o usuário não pode + incluir, alterar ou excluir nenhum item
    function importado_financeiro($id_nf) {
        $sql = "SELECT `importado_financeiro` 
                FROM `nfs` 
                WHERE `id_nf` = '$id_nf' LIMIT 1 ";
        $campos = bancos::sql($sql);
        return $campos[0]['importado_financeiro'];
    }

    /*Função que define o valor de cada Vencimento do $id_nf passado por parâmetro e que facilitará p/ o Depto. Financeiro gerar as Duplicatas ...
    
    Observação: 
        
    Este quinto parâmetro $recalcular_duplicatas só é utilizado no Frame de Frete - arquivo que fica dentro do Cabeçalho da Nota Fiscal ...*/
    function valor_duplicata($id_nf, $suframa_nf, $nota_sgd, $id_pais, $recalcular_duplicatas = 'N') {
        $calculo_total_impostos = calculos::calculo_impostos(0, $id_nf, 'NF');
        
        //Se o país for Internacional, retorno o Valor Total da Nota em U$ senão em Dólar ...
        $valor_total_nota = ($id_pais != 31) ? $calculo_total_impostos['valor_total_nota_us'] : $calculo_total_impostos['valor_total_nota'];
        
        $sql = "SELECT c.`id_uf`, nfs.`data_emissao`, nfs.`valor1`, nfs.`vencimento1`, nfs.`valor2`, nfs.`vencimento2`, 
                nfs.`valor3`, nfs.`vencimento3`, nfs.`valor4`, nfs.`vencimento4`, nfs.`status` 
                FROM `nfs` 
                INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
                WHERE nfs.`id_nf` = '$id_nf' LIMIT 1 ";
        $campos = bancos::sql($sql);
        //"Em Aberto" 0 ou "NF de Devolução" 6, o sistema recalcula os valores de forma automática e grava na NF ...
        if($campos[0]['status'] == 0 || $campos[0]['status'] == 6 || $recalcular_duplicatas == 'S') {
            $id_uf_cliente      = $campos[0]['id_uf'];
    /****************************************************************************/
    /****************************Substituição Tributária*************************/
    /****************************************************************************/
    //Se existir Substituição Tributária então eu desconto esse imposto do valor Total da NF ...
            if($calculo_total_impostos['valor_icms_st'] > 0) {
                if($id_uf_cliente != 1) {//Se for de um Estado diferente de São Paulo ...
                    //Verifico se no Estado do Cliente existe algum Convênio ...
                    $sql = "SELECT convenio 
                            FROM `ufs` 
                            WHERE `id_uf` = '$id_uf_cliente' LIMIT 1 ";
                    $campos_convenio = bancos::sql($sql);
                    //Existe convênio, então significa que o Cliente não irá pagar a GNRE se existir ST e sim nós "Empresa" ...
                    //Do valor Total da NF eu desconto o Valor de Substituição Tributária e o FECP se existir, porque estes serão cobrados na 1ª Parcela ...
                    if($campos_convenio[0]['convenio'] != '') $valor_total_nota-= ($calculo_total_impostos['valor_icms_st'] + $calculo_total_impostos['valor_fecp']);
                }
            }
            $valor_total_nota = round($valor_total_nota, 2);

            if(!empty($campos[0]['vencimento4'])) {
                $valor_duplicata    = round(round(($valor_total_nota / 4), 3), 2);
                $valor1             = number_format($valor_duplicata, 2, '.', '');
                $valor2             = number_format($valor_duplicata, 2, '.', '');
                $valor3             = number_format($valor_duplicata, 2, '.', '');
                $valor4             = number_format($valor_total_nota - ($valor1 + $valor2 + $valor3), 2, '.', '');
            }else if(!empty($campos[0]['vencimento3'])) {
                $valor_duplicata    = round(round(($valor_total_nota / 3), 3), 2);
                $valor1             = number_format($valor_duplicata, 2, '.', '');
                $valor2             = number_format($valor_duplicata, 2, '.', '');
                $valor3             = number_format($valor_total_nota - ($valor1 + $valor2), 2, '.', '');
                $valor4             = 0;
            }else if(!empty($campos[0]['vencimento2'])) {
                $valor_duplicata    = round(round(($valor_total_nota / 2), 3), 2);
                $valor1             = number_format($valor_duplicata, 2, '.', '');
                $valor2             = number_format($valor_total_nota - $valor_duplicata, 2, '.', '');
                $valor3             = 0;
                $valor4             = 0;
            }else {//então só existe um prazo o valor da duplicata é total
                $valor1             = number_format($valor_total_nota, 2, '.', '');
                $valor2             = 0;
                $valor3             = 0;
                $valor4             = 0;
            }
            //Se existir Substituição Tributária então eu acrescento esse imposto na 1º vencimento da NF ...
            if($calculo_total_impostos['valor_icms_st'] > 0) {
                if($id_uf_cliente != 1) {//Se for de um Estado diferente de São Paulo ...
                    /*Ñ existe convênio, então significa que o Cliente irá pagar a GNRE se existir ST e o FECP se existir, 
                    se o Cliente não pagou essa Guia lá no seu Estado de origem então, nós aqui em SP somos 
                    legais e pagamos para ele antecipado e agora precisamos ser reembolsados 
                    na 1ª parcela, por isso desse controle ...*/
                    $valor1+= ($calculo_total_impostos['valor_icms_st'] + $calculo_total_impostos['valor_fecp']);
                }
            }
            //Antes de atualizar a NF, verifico se a mesma já foi Importada no Financeiro ...
            $sql = "SELECT id_conta_receber 
                    FROM `contas_receberes` 
                    WHERE `id_nf` = '$id_nf' LIMIT 1 ";
            $campos_receberes = bancos::sql($sql);
            if(count($campos_receberes) == 0) {//Essa NF ainda não foi importada, sendo assim posso atualizar os Vencimentos da NF ...
                $sql = "UPDATE `nfs` SET `valor1` = '$valor1', `valor2` = '$valor2', `valor3` = '$valor3', `valor4` = '$valor4' WHERE `id_nf` = '$id_nf' LIMIT 1 ";
                bancos::sql($sql);
            }
        }else {
            /*Nos outros status à partir do "Liberada p/ Faturar", só são lidos esses campos de valores que foram gravado em NF ...
            
            Observação esse só tem a chance de ser alterado de forma manual no arquivo "alterar_valores_duplicatas" que fica dentro do 
            frame "destinatario_remetente_fatura" que fica dentro do Cabeçalho da Nota Fiscal, sempre à partir do status 
            "Liberada p/ Faturar" ...*/
            $valor1 = $campos[0]['valor1'];
            $valor2 = $campos[0]['valor2'];
            $valor3 = $campos[0]['valor3'];
            $valor4 = $campos[0]['valor4'];
        }
        return array($valor1, $valor2, $valor3, $valor4);
    }
	
/*Função que será utilizada somente quando o Cliente for estrangeiro, pois nesse caso, 
nós gravamos o valor da Duplicata em U$ na Base de Dados*/
    function valor_duplicata_rs($valor_itens_rs, $qtde_duplicatas) {
        if($qtde_duplicatas == 4) {
            $valor_duplicata = round(round(($valor_itens_rs / 4), 3), 2);
            $duplicata_rs1 = $valor_duplicata;
            $duplicata_rs2 = $valor_duplicata;
            $duplicata_rs3 = $valor_duplicata;
            $duplicata_rs4 = $valor_itens_rs - ($duplicata_rs1 + $duplicata_rs2 + $duplicata_rs3);
        }else if($qtde_duplicatas == 3) {
            $valor_duplicata = round(round(($valor_itens_rs / 3), 3), 2);
            $duplicata_rs1 = $valor_duplicata;
            $duplicata_rs2 = $valor_duplicata;
            $duplicata_rs3 = $valor_itens_rs - ($duplicata_rs1 + $duplicata_rs2);
            $duplicata_rs4 = 0.00;
        }else if($qtde_duplicatas == 2) {
            $valor_duplicata = round(round(($valor_itens_rs / 2), 3), 2);
            $duplicata_rs1 = $valor_duplicata;
            $duplicata_rs2 = $valor_itens_rs - $duplicata_rs1;
            $duplicata_rs3 = 0.00;
            $duplicata_rs4 = 0.00;
        }else {//então só existe um prazo o valor da duplicata é total
            $duplicata_rs1 = $valor_itens_rs;
            $duplicata_rs2 = 0.00;
            $duplicata_rs3 = 0.00;
            $duplicata_rs4 = 0.00;
        }
        return array($duplicata_rs1, $duplicata_rs2, $duplicata_rs3, $duplicata_rs4);
    }

    function pedidos_vendas_status($id_pedido_venda_item) {
        /*Pega o Pedido e Verifica a qtde que foi solicitada no item do pedido*/
        $sql = "SELECT `qtde`, `id_pedido_venda` 
                FROM `pedidos_vendas_itens` 
                WHERE `id_pedido_venda_item` = '$id_pedido_venda_item' LIMIT 1 ";
        $campos 		= bancos::sql($sql);
        $qtde_solicitada	= $campos[0]['qtde'];
        $id_pedido_venda	= $campos[0]['id_pedido_venda'];

        $sql = "SELECT SUM(`qtde` - `qtde_devolvida`) AS total_entregue 
                FROM `nfs_itens` 
                WHERE `id_pedido_venda_item` = '$id_pedido_venda_item' ";
        $campos         = bancos::sql($sql);
        $total_entregue = $campos[0]['total_entregue'];

        if($total_entregue <= 0) {//nada entregue
            $status = 0;
        }else if($total_entregue < $qtde_solicitada) {//entrega parcial
            $status = 1;
        }else if($total_entregue >= $qtde_solicitada) {//entrega total
            $status = 2;
        }

        $sql = "UPDATE `pedidos_vendas_itens` SET `qtde_faturada` = '$total_entregue', `status` = '$status' WHERE `id_pedido_venda_item` = '$id_pedido_venda_item' LIMIT 1 ";
        bancos::sql($sql);

        //Situacao do pedido para a nota
        $sql = "SELECT `id_pedido_venda_item` 
                FROM `pedidos_vendas_itens` 
                WHERE `id_pedido_venda` = '$id_pedido_venda' 
                AND `status` < '2' LIMIT 1 ";
        $campos	= bancos::sql($sql);
        $status = (count($campos) == 0) ? 2 : 1;

        $sql = "UPDATE `pedidos_vendas` SET `status` = '$status' WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
        bancos::sql($sql);
        //Aqui atualiza o valor de Pendência do Pedido ...
        if(!class_exists('vendas')) require 'vendas.php';//CASO EXISTA EU DESVIO A CLASSE ...
        vendas::valor_pendencia($id_pedido_venda);
    }

    function controle_estoque($id_nf = 0, $id_pedido_venda_item, $qtde_faturar, $qtde_nfe, $preco_nfe, $acao = 0) {
        if(!class_exists('estoque_acabado')) require 'estoque_acabado.php';//CASO EXISTA EU DESVIO A CLASSE ...
//Agora antes de qualquer coisa 
        if($id_nf != 0) {
            $sql = "SELECT `id_empresa`, `finalidade`, `natureza_operacao`, `trading`, `suframa`, `suframa_ativo` 
                    FROM `nfs` 
                    WHERE `id_nf` = '$id_nf' LIMIT 1 ";
            $campos_nf      = bancos::sql($sql);
//Renomeio essa variável p/ não dar problema com a id_empresa da sessão ...
            $id_empresa_nf      = $campos_nf[0]['id_empresa'];
            $finalidade         = $campos_nf[0]['finalidade'];
            $natureza_operacao  = $campos_nf[0]['natureza_operacao'];
            $trading            = $campos_nf[0]['trading'];
            $suframa_nf         = $campos_nf[0]['suframa'];
            $suframa_ativo      = $campos_nf[0]['suframa_ativo'];
        }
//Controle com os Itens de Pedido, p/ saber se está separado, etc ...
        $sql = "SELECT ovi.`id_produto_acabado_discriminacao`, pvi.`id_produto_acabado`, pvi.`id_representante`, 
                (pvi.`qtde` - pvi.`qtde_pendente` - pvi.`vale`) AS faturando, pvi.`vale`, 
                pvi.`qtde_faturada`, pvi.`comissao_new`, pvi.`comissao_extra`, c.`id_cliente`, 
                c.`id_pais`, c.`id_uf`, c.`artigo_isencao`, c.`insc_estadual`, c.`tributar_ipi_rev` 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
                INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
                WHERE pvi.`id_pedido_venda_item` = '$id_pedido_venda_item' LIMIT 1 ";
        $campos                             = bancos::sql($sql);
        $id_produto_acabado                 = $campos[0]['id_produto_acabado'];
        $id_produto_acabado_discriminacao   = $campos[0]['id_produto_acabado_discriminacao'];
        $id_representante                   = $campos[0]['id_representante'];
        $faturando                          = $campos[0]['faturando'];//SEPARADO CONTANDO COM O VALE
        $vale                               = $campos[0]['vale'];//qtde de vale do pedido
        $retorno                            = estoque_acabado::qtde_estoque($id_produto_acabado);//qtde de estoque
        $estoque_real                       = $retorno[0];
        $faturando-=                        $campos[0]['qtde_faturada'];
        $comissao_new                       = $campos[0]['comissao_new'];
        $comissao_extra                     = $campos[0]['comissao_extra'];
        $id_cliente                         = $campos[0]['id_cliente'];
        $id_pais                            = $campos[0]['id_pais'];
        $id_uf                              = $campos[0]['id_uf'];
        $artigo_isencao                     = $campos[0]['artigo_isencao'];
        $insc_estadual                      = $campos[0]['insc_estadual'];

/*Esse artigo foi comentado em 08/02/2018 onde já era pra ter sido comentado bem antes, mas enfim paciência ..., tudo por causa da Difal 
que é um novo acordo entre Estados ...

Esse controle da Inscrição Estadual tem a ver com o Novo Artigo Art. 56 do Decreto 45.490/2000 - RICMS-SP 
que se o Cliente não tiver Inscrição Estadual, então eu faço a Busca dos Tributos de ICMS como se fosse 
daqui do Estado de São Paulo, afinal ele não é contribuinte de Imposto ...
        if(empty($insc_estadual) || $insc_estadual == 0) $id_uf = 1;*/
        
        /*Esse controle é de extrema importância porque em casos de "Gato por Lebre", preciso pegar 
        os impostos do Gato ...

        Ex: o cliente comprou MRH-042 "Gato", mas estamos enviando o MRT-042 "Lebre ou substituto" ...*/
        $id_produto_acabado_utilizar = (!empty($id_produto_acabado_discriminacao)) ? $id_produto_acabado_discriminacao : $id_produto_acabado;

        switch($acao) {
            case 0://excluir o item normal
                faturamentos::pedidos_vendas_status($id_pedido_venda_item);
                estoque_acabado::manipular($id_produto_acabado, $qtde_faturar, 0, 3, "Exclusão de Item da NF id_pedido_venda_item=$id_pedido_venda_item", ($qtde_faturar + $vale)); //função de controle do estoque real
            break;
            case 1://incluir o item
                if($qtde_faturar <= ($faturando + $vale)) {//qtde q quero faturar é menor do q eu tenho separado juntado com o vale
                    if($estoque_real >= $faturando) {// aqui eu desconto o vale pois ele ja foi descontado do estoque entao nao posso comprar o vale
/**********************************************************/
/*Agora está existindo casos de sair uma NF de Exportação como sendo SGD, e lembrando que quando a NF é SGD, 
não existe CFOP, então eu só consigo fazer essa distinção pela Unidade Federal, que é diferente de 31 p/ 
nós como sendo do Brasil*/
/**********************************************************/
/*Não existe IPI, ICMS, Redução quando as CFOP(s) forem: 
'7.101' -> Exportação
'6.501' -> Trading
5.912 / 6.912 - Remessa para Demonstração ...*/
                        if($id_pais <> 31 || $trading == 1) {
                            $sql = "SELECT f.`id_classific_fiscal`, ov.`artigo_isencao`, pa.`operacao`, pa.`operacao_custo`, pa.`peso_unitario`, 
                                    pvi.`preco_liq_final` 
                                    FROM `pedidos_vendas_itens` pvi 
                                    INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
                                    INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
                                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
                                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                                    INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
                                    WHERE pvi.`id_pedido_venda_item` = '$id_pedido_venda_item' ";
                            $campos                 = bancos::sql($sql);
                            $id_classific_fiscal    = $campos[0]['id_classific_fiscal'];
                            $artigo_isencao         = $campos[0]['artigo_isencao'];
                            $operacao               = $campos[0]['operacao'];
                            $operacao_custo         = $campos[0]['operacao_custo'];
                            $peso_unitario          = $campos[0]['peso_unitario'];
                            $valor_unitario         = $campos[0]['preco_liq_final'];
                            $valor_unitario_exp     = $campos[0]['preco_liq_final'];
                            
                            $ipi                    = 0;
                            $icms                   = 0;
                            $reducao                = 0;
                            $icms_intraestadual     = 0;
                            $iva                    = 0;
                        }else {//Daqui
//A operação de Fat. do PA sempre será Industrial quando o Cliente possuir a marcação de Tributar IPI REV e for daqui do Brasil ...
//O IVA a partir de agora, está sendo lido direto da Tabela de Classificação Fiscal ...
                            $sql = "SELECT f.`id_classific_fiscal`, ov.`artigo_isencao`, pa.`operacao`, pa.`operacao_custo`, pa.`peso_unitario`, 
                                    pvi.`id_pedido_venda`, pvi.`preco_liq_final` 
                                    FROM `pedidos_vendas_itens` pvi 
                                    INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
                                    INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
                                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
                                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                                    INNER JOIN `grupos_pas` gps ON gps.`id_grupo_pa` = ged.`id_grupo_pa` 
                                    INNER JOIN `familias` f ON f.`id_familia` = gps.`id_familia` 
                                    INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
                                    WHERE pvi.`id_pedido_venda_item` = '$id_pedido_venda_item' ";
                            $campos                 = bancos::sql($sql);
                            $id_classific_fiscal    = $campos[0]['id_classific_fiscal'];
                            $artigo_isencao         = $campos[0]['artigo_isencao'];
                            $operacao               = $campos[0]['operacao'];
                            $operacao_custo         = $campos[0]['operacao_custo'];
                            $peso_unitario          = $campos[0]['peso_unitario'];
                            $id_pedido_venda        = $campos[0]['id_pedido_venda'];
                            $valor_unitario         = $campos[0]['preco_liq_final'];
                            $valor_unitario_exp     = $campos[0]['preco_liq_final'];
                            
                            $dados_produto          = intermodular::dados_impostos_pa($id_produto_acabado_utilizar, $id_uf, $id_cliente, $id_empresa_nf, $finalidade);
                            $ipi                    = $dados_produto['ipi'];
                            $icms                   = $dados_produto['icms'];
                            $reducao                = $dados_produto['reducao'];
                            $icms_intraestadual     = $dados_produto['icms_intraestadual'];
                            $iva                    = $dados_produto['iva'];
                            /**********************************************************************/
                            /***Nota Fiscal de Venda Originada de Encomenda para Entrega Futura****/
                            /**********************************************************************/
                            if($natureza_operacao == 'VOF') {
                                //Nesse único caso em específico, não existe Impostos ...
                                $ipi                    = 0;
                                $icms                   = 0;
                                $reducao                = 0;
                                $icms_intraestadual     = 0;
                                $iva                    = 0;
                                
                                //Mas estes incidem sobre o valor Total do Produto ...
                                $calculo_impostos_item  = calculos::calculo_impostos($id_pedido_venda_item, $id_pedido_venda, 'PV');
                                $valor_ipi              = round($calculo_impostos_item['valor_ipi'] / $qtde_faturar, 2);
                                $valor_icms_st          = round($calculo_impostos_item['valor_icms_st'] / $qtde_faturar, 2);
                                
                                $valor_unitario+=       ($valor_ipi + $valor_icms_st);
                                $valor_unitario_exp+=   ($valor_ipi + $valor_icms_st);
                            }
                            /*Existe essa nova Regra que entrou em vigor em 01/01/2011 ...
                            Decisões Normativas CAT ns. 6 e 8 de 2010 - Alíquota de ICMS e Redução da Base de Cálculo do ICMS 
                            Todas as vendas de produtos de Classificação Fiscal 84.66.93.30 ou 84.66.93.40 que são “REVENDA”, 
                            devem ser retiradas a redução de Base de Cálculo de 26,67 para qualquer estado e 
                            no estado de São Paulo apenas, aquelas alíquotas que são de 12% viram 18%...
                            if($finalidade == 'R' && ($id_classific_fiscal == 1 || $id_classific_fiscal == 2)) {
                                    if($id_uf == 1) $icms = 18;//Se a UF = 'SP', então o ICMS vai para 18% ...
                                    $reducao = 0;
                            }*/
                        }
/*Essa nova variável é o que vai dar autonomia p/ incluir os Itens na NF, criei esta porque 
a partir de agora irá existir muitos controles por CFOP que irão impedir esse procedimento*/
                        $incluir_itens = 1;//Default ...
//Verificações somente p/ a(s) Empresa(s) Albafer e Tool Master ...
                        if($id_empresa_nf == 1 || $id_empresa_nf == 2) {
/*Se a Empresa for Albafér ou Tool Master, "Classificação Fiscal = 14 então significa que 
esse Produto é uma Mercadoria ou Prestação de Serviço e Venda de Sucata Metal", sendo assim, 
só posso incluir o mesmo se for nas Classificações de 5.949 ou 6.949, que é destinada 
justamente para esse fim ...*/
                            if($id_classific_fiscal == 14 && ($cfop != '5.949' && $cfop != '6.949')) {
                                $incluir_itens = 3;//Está incoerente
?>
                            <Script Language = 'JavaScript'>
                                alert('O ITEM NÃO PODE SER INCLUÍDO PORQUE A CLASSIFICAÇÃO FISCAL É 99.99.99.99 "MÃO OBRA" !\n\nDEVE SER FEITO UMA NOTA FISCAL DE SERVIÇO P/ ESTE TIPO DE ITEM EM ESPECÍFICO !!!')
                            </Script>
<?
/*Supondo que estou incluindo um Item de Venda em que a Clas. Fiscal é <> de 14 "Conserto", 

então verifico se a CFOP está de acordo com este Tipo de Item, afinal eu não posso incluir 
Itens de Conserto e Venda na mesma NF ...*/
                            }else if($id_classific_fiscal != 14 && ($cfop == '5.949' || $cfop == '6.949')) {
                                $incluir_itens = 3;//Está incoerente
                            }
/********************************************************************************************/
/***************************Controle com os IVAs fora de São Paulo***************************/
/********************************************************************************************/
                            /*Abrimos uma brecha na lei a partir de agora - 06/06/2012 - Dárcio ...
                            if($id_uf > 1) {
                                //Aqui se foi inserido em NF algum Item com IVA ...
                                $sql = "SELECT `id_nfs_item` 
                                        FROM `nfs_itens` 
                                        WHERE `id_nf` = '$id_nf' 
                                        AND `iva` > '0' LIMIT 1 ";
                                $campos_itens_com_iva = bancos::sql($sql);
                                $itens_com_iva = count($campos_itens_com_iva);
                                /*Se a Qtde de Itens com IVA for > 0 então verifico se esse 
                                Item que está sendo Incluso também possui Iva, do Contrário 
                                o mesmo não pode ser Incluso ...
                                if($itens_com_iva > 0 && $iva == 0) $incluir_itens = 2;//Está incoerente
                                //Aqui se foi inserido em NF algum Item sem IVA ...
                                $sql = "SELECT `id_nfs_item` 
                                        FROM `nfs_itens` 
                                        WHERE `id_nf` = '$id_nf' 
                                        AND `iva` = '0' LIMIT 1 ";
                                $campos_itens_sem_iva = bancos::sql($sql);
                                $itens_sem_iva = count($campos_itens_sem_iva);
                                /*Se a Qtde de Itens sem IVA for > 0 então verifico se esse 
                                Item que está sendo Incluso também não possui Iva, do Contrário 
                                o mesmo não pode ser Incluso ...
                                if($itens_sem_iva > 0 && $iva > 0) $incluir_itens = 2;//Está incoerente
                            }*/
/********************************************************************************************/
                        }
//Procedimento p/ incluir Itens na NF ...
                        if($incluir_itens == 1) {
                            $sql = "SELECT `id_empresa`, `valor_dolar_dia`, `data_emissao` 
                                    FROM `nfs` 
                                    WHERE `id_nf` = '$id_nf' LIMIT 1 ";
                            $campos = bancos::sql($sql);
                            $id_empresa_nota	= $campos[0]['id_empresa'];// id_empresa = 4 <=> Grupo 
                            $valor_dolar_dia	= $campos[0]['valor_dolar_dia'];
                            $data_emissao		= $campos[0]['data_emissao'];
                            if($id_pais != 31) {//somente para pegar o preço do dolar para nota de exportacao
                                $valor_unitario = round(round($valor_unitario * $valor_dolar_dia, 2), 3);
                            }
/*Quando a NF for do Grupo - SGD, então não existe IPI (que já é tratado na função acima), 
ICMS e Redução B.C. por mais que exista no cadastro ...*/
                            /*if($id_empresa_nota == 4) {//Nota feita para Empresa Grupo ...
                                $icms = 0;
                                $reducao = 0;
                                $icms_intraestadual = 0;
                                $iva = 0;
                            }*/
/************************************************************************/
/***************Novos Controle com IVA - Dárcio 18/06/2012***************/
/************************************************************************/
//Nunca existirá IVA para NF(s) que são CONSUMO e os Itens da NF são Industriais ...
                            //if($finalidade == 'C' && $operacao == 0) $iva = 0;
/************************************************************************/

/*ssa linha abaixo foi comentada no dia 28/01/2014 e adaptada dentro da função "dados_impostos_pa" 
da biblioteca intermodular ...*/
//Novas Regras - Se existir IVA, a Operação de Faturamento do PA = 'REV' e o Estado = 'SP', então eu zero a alíquota de ICMS ...
                            //if($iva > 0 && $operacao == 1 && $id_uf == 1) $icms = 0;
                            $data_sys = date('Y-m-d');
                            //Nunca existirá IPI se o Suframa do Cliente estiver Habilitado ...
                            if($ipi == 'S/IPI' || ($suframa_nf > 0 && $suframa_ativo = 'S')) $ipi = 0;
                            
                            //Os impostos que são gravados aqui nessa tabela são sempre do Substituto ...
                            $sql = "INSERT INTO `nfs_itens` (`id_nfs_item`, `id_nf`, `id_pedido_venda_item`, `id_produto_acabado`, `id_representante`, `id_classific_fiscal`, `peso_unitario`, `qtde`, `qtde_nfe`, `vale`, `valor_unitario`, `valor_unitario_exp`, `preco_nfe`, `comissao_new`, `comissao_extra`, `ipi`, `icms`, `reducao`, `icms_intraestadual`, `iva`, `data_sys`) 
                                    VALUES (NULL, '$id_nf', '$id_pedido_venda_item', '$id_produto_acabado', '$id_representante', '$id_classific_fiscal', '$peso_unitario', '$qtde_faturar', '$qtde_nfe', '$vale', '$valor_unitario', '$valor_unitario_exp', '$preco_nfe', '$comissao_new', '$comissao_extra', '$ipi', '$icms', '$reducao', '$icms_intraestadual', '$iva', '$data_sys') ";
                            bancos::sql($sql);
                            $GLOBALS['cont_itens_aceitos']++;
                            faturamentos::pedidos_vendas_status($id_pedido_venda_item);

                            if($vale > 0) {//verifico se o vale ja foi faturado
                                $qtde_faturar-=$vale;
                                $sql = "UPDATE `pedidos_vendas_itens` SET `vale` = '0' WHERE `id_pedido_venda_item` = '$id_pedido_venda_item' LIMIT 1 ";
                                bancos::sql($sql);
                            }
                            estoque_acabado::manipular($id_produto_acabado, -$qtde_faturar, 0, 3, "Incluir Item na NF id_nf=$id_nf", -($qtde_faturar + $vale)); //função de controle do estoque real
                            estoque_acabado::controle_estoque_pa($id_produto_acabado);
                            return 1;//Serve p/ saber qual msn mostrar na Tela de Incluir Itens ...
                        }else {
                            return $incluir_itens;//Serve p/ saber qual msn mostrar na Tela de Incluir Itens ...
                        }
                    }else {
?>
                        <Script Language = 'JavaScript'>
                            alert('O ITEM NÃO PODE SER INCLUÍDO PORQUE NÃO POSSUI ESTOQUE REAL SUFICIENTE !!!')
                        </Script>
<?
                    }
                }else {
?>
                    <Script Language = 'JavaScript'>
                        alert('O ITEM NÃO PODE SER INCLUÍDO, POIS NÃO HÁ QTDE FOI EXCEDIDA !!!')
                    </Script>
<?
                }
            break;
            case 2://alterar o item
                    //O alterar não foi feito devido a complexidade do controle de vales ...
            break;
            case 3://incluir devolucao do item da NF de devoluções
                    estoque_acabado::manipular($id_produto_acabado, $qtde_faturar, 0, 3, "Devolucao do Item da NF id_pedido_venda_item=$id_pedido_venda_item", 0); //função de controle do estoque real
            break;
            case 4://excluir devolucao do item da NF de devoluções
                    // No arquivo direto o o sys verifica se tem estoque real e disponivel para descontar
                    estoque_acabado::manipular($id_produto_acabado, -$qtde_faturar, 0, 3, "Estorno de Devolucao do Item da NF id_pedido_venda_item=$id_pedido_venda_item", 0); //função de controle do estoque real
            break;
        }
        estoque_acabado::qtde_estoque($id_produto_acabado, 1);//depois dos calculos preciso atualizar a tabela de estoque PA n~  tirar esta linha
    }
/*Verifico se já foi paga a Comissão do Representante na NF e se mesmo depois de paga, tem a autorização 
p/ poder estar mudando o Status da NF p/ cancelada*/
    function pago_comissao_pode_excluir($id_nf) {
        $sql = "SELECT status_comissao_pg 
                FROM `nfs` 
                WHERE `id_nf` = '$id_nf' LIMIT 1 ";
        $campos = bancos::sql($sql);
//A comissão já foi Paga, sendo assim verifica se existe autorização p/ estar excluíndo a comissão ...
        if($campos[0]['status_comissao_pg'] == 'S') {
            $sql = "SELECT id_comissao_estorno 
                    FROM `comissoes_estornos` 
                    WHERE `id_nf` = '$id_nf' 
                    AND `tipo_lancamento` = '0' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 1) {//Tem autorização p/ estar excluindo normalmente ...
                return 1;
            }else {//Não tem autorização ...
                return 0;
            }
        }else {//Não foi paga, posso cancelar a NF normalmente ...
            return 1;
        }
    }
/*Função que será utilizada quando existir algum P.A. que não possui Peso Unitário no qual 
retorna erro de Divisão por Zero nos cálculos ...*/
	function itens_nf_peso_unitario_zerado($id_nf) {
		$sql = "Select pa.referencia, pa.discriminacao, pa.peso_unitario 
                        from nfs_itens nfsi 
                        inner join produtos_acabados pa on pa.id_produto_acabado = nfsi.id_produto_acabado and pa.peso_unitario = '0.00000000' 
                        where nfsi.id_nf = '$id_nf' ";
		$campos_pa = bancos::sql($sql);
		$linhas_pa = count($campos_pa);
		if($linhas_pa > 0) {
                    $texto_exibir = '<br><br><font color="red"><b>O(s) Item(ns) da Nota Fiscal: </b></font><br>';
                    for($i = 0; $i < $linhas_pa; $i++) {
                            $texto_exibir.= '<font color="black"><b>'.$campos_pa[$i]['referencia'].'-'.$campos_pa[$i]['discriminacao'].'</b></font><br>';
                    }
                    return $texto_exibir.= '<font color="red">Estão com o Peso Unitário = 0. É necessário o preenchimento com algum valor, p/ que não resulte em erro de Divisão por Zero.</font><br><br>';
		}
	}
/**********************************************************************************************************/
/************************************************Outras NFS************************************************/
/**********************************************************************************************************/
/*Dessa linha em diante, são as Novas Funções que se referem aos outros Tipos de Notas Fiscais que não tem nada 
a ver com as Notas Fiscais de Vendas ...*/
    function situacao_outras_nfs($id_nf_outra) {
        $sql = "SELECT `status` 
                FROM `nfs_outras` 
                WHERE `id_nf_outra` = '$id_nf_outra' LIMIT 1 ";
        $campos = bancos::sql($sql);
        return $campos[0]['status'];
    }
	
	//Função q verifica se a Nota já foi importada p/ o Financeiro ...
	//caso sim, então o usuário não pode + incluir, alterar ou excluir nenhum item
	function importado_financeiro_outras_nfs($id_nf_outra) {
            $sql = "SELECT `importado_financeiro` 
                    FROM `nfs_outras` 
                    WHERE `id_nf_outra` = '$id_nf_outra' LIMIT 1 ";
            $campos = bancos::sql($sql);
            return $campos[0]['importado_financeiro'];
	}
	
	function calculo_peso_outras_nfs($id_nf_outra) { // Calcula o peso bruto e liq  da NFS.   *****   cuidado função recursiva
		$sql = "Select sum(qtde * peso_unitario) as total_peso_nf 
                        from nfs_outras_itens 
                        where id_nf_outra = '$id_nf_outra' 
                        group by id_nf_outra ";
		$campos = bancos::sql($sql);
		return array('total_peso_nf' => $campos[0]['total_peso_nf']);
	}
	
    function cfop_combo_outras_nfs($id_nf_outra) {
        $sql = "SELECT c.`id_pais`, c.`id_uf`, nfso.`id_empresa`, nfso.`tipo_nfe_nfs` 
                FROM `nfs_outras` nfso 
                INNER JOIN `clientes` c ON c.`id_cliente` = nfso.`id_cliente` 
                WHERE nfso.`id_nf_outra` = '$id_nf_outra' LIMIT 1 ";
        $campos         = bancos::sql($sql);
        $id_pais        = $campos[0]['id_pais'];
        $id_uf          = $campos[0]['id_uf'];
        $tipo_nfe_nfs 	= strtoupper($campos[0]['tipo_nfe_nfs']);
//Tratamento em relação as CFOPs
        if($id_pais <> 31) {//Quando for fora do Brasil
//Carrega as de Saída
            $sql_saida = "SELECT CONCAT(`id_cfop`) AS id_cfops, CONCAT(`cfop`, '.', `num_cfop`, ' - ', `natureza_operacao_resumida`) AS cfop 
                            FROM `cfops` 
                            WHERE `cfop` = '7' 
                            AND `ativo` = '1' 
                            AND `cfop_nf_venda` = 'N' ORDER BY `cfop` ";
//Carrega as de Entrada
            $sql_entrada = "SELECT CONCAT(`id_cfop`) AS id_cfops, CONCAT(`cfop`, '.', `num_cfop`, ' - ', `natureza_operacao_resumida`) AS cfop 
                            FROM `cfops` 
                            WHERE `cfop` = '3' 
                            AND `ativo` = '1' 
                            AND `cfop_nf_venda` = 'N' ORDER BY `cfop` ";
//Tratamento para quando carregar a combo de primeiro e não existir nenhuma CFOP atrelada anteriormente
            if($tipo_nfe_nfs == 'S') {
                $id_cfop_apresentar = 156;
            }else {
                $id_cfop_apresentar = 181;
            }
        }else {//Quando dentro do Brasil	
            if($id_uf == 1) {//Verifica se é da Capital -> Sampa
//Carrega as de Saída
                $sql_saida = "SELECT CONCAT(`id_cfop`) AS id_cfops, CONCAT(`cfop`, '.', `num_cfop`, ' - ', `natureza_operacao_resumida`) AS cfop 
                                FROM `cfops` 
                                WHERE `cfop` = '5' 
                                AND `ativo` = '1' 
                                AND `cfop_nf_venda` = 'N' ORDER BY `cfop` ";
//Carrega as de Entrada
                $sql_entrada = "SELECT CONCAT(`id_cfop`) AS id_cfops, CONCAT(`cfop`, '.', `num_cfop`, ' - ', `natureza_operacao_resumida`) AS cfop 
                                FROM `cfops` 
                                WHERE `cfop` = '1' 
                                AND `ativo` = '1' 
                                AND `cfop_nf_venda` = 'N' ORDER BY `cfop` ";
//Tratamento para quando carregar a combo de primeiro e não existir nenhuma CFOP atrelada anteriormente
                if($tipo_nfe_nfs == 'S') {
                    $id_cfop_apresentar = 3;
                }else {
                    $id_cfop_apresentar = 139;
                }
            }else {//No Brasil, mas está em outro Estado
//Carrega as de Saída
                $sql_saida = "SELECT CONCAT(`id_cfop`) AS id_cfops, CONCAT(`cfop`, '.', `num_cfop`, ' - ', `natureza_operacao_resumida`) AS cfop 
                                FROM `cfops` 
                                WHERE `cfop` = '6' 
                                AND `ativo` = '1' 
                                AND `cfop_nf_venda` = 'N' ORDER BY `cfop` ";
//Carrega as de Entrada
                $sql_entrada = "SELECT CONCAT(`id_cfop`) AS id_cfops, CONCAT(`cfop`, '.', `num_cfop`, ' - ', `natureza_operacao_resumida`) AS cfop 
                                FROM `cfops` 
                                WHERE `cfop` = '2' 
                                AND `ativo` = '1' 
                                AND `cfop_nf_venda` = 'N' ORDER BY `cfop` ";
//Tratamento para quando carregar a combo de primeiro e não existir nenhuma CFOP atrelada anteriormente
                if($tipo_nfe_nfs == 'S') {
                    $id_cfop_apresentar = 143;
                }else {
                    $id_cfop_apresentar = 147;
                }
            }
        }
        return array('sql_saida'=>$sql_saida,'sql_entrada'=>$sql_entrada,'id_cfop_apresentar'=>$id_cfop_apresentar);
    }

    function valor_duplicata_outras_nfs($id_nf_outra, $nota_sgd, $id_pais) { // Aqui define o valor exato de cada duplicada
        $calculo_total_impostos = calculos::calculo_impostos(0, $id_nf_outra, 'NFO');

        $sql = "SELECT data_emissao data_emissao_nf, vencimento1 prazo1, vencimento2 prazo2, vencimento3 prazo3, vencimento4 prazo4 
                FROM `nfs_outras` 
                WHERE `id_nf_outra` = '$id_nf_outra' LIMIT 1 ";
        $campos             = bancos::sql($sql);
        $valor_total_nota   = $calculo_total_impostos['valor_total_nota'];
        
/****************************************************************************/
        if(!empty($campos[0]['prazo4'])) {
            $valor_duplicata= round(round(($valor_total_nota / 4), 3), 2);
            $valor1         = number_format($valor_duplicata, 2, '.', '');
            $valor2         = number_format($valor_duplicata, 2, '.', '');
            $valor3         = number_format($valor_duplicata, 2, '.', '');
            $valor4         = number_format($valor_total_nota - ($valor1 + $valor2 + $valor3), 2, '.', '');
        }else if(!empty($campos[0]['prazo3'])) {
            $valor_duplicata= round(round(($valor_total_nota / 3), 3), 2);
            $valor1         = number_format($valor_duplicata, 2, '.', '');
            $valor2         = number_format($valor_duplicata, 2, '.', '');
            $valor3         = number_format($valor_total_nota - ($valor1 + $valor2), 2, '.', '');
            $valor4         = 0;
        }else if(!empty($campos[0]['prazo2'])) {
            $valor_duplicata= round(round(($valor_total_nota / 2), 3), 2);
            $valor1         = number_format($valor_duplicata, 2, '.', '');
            $valor2         = number_format($valor_total_nota - $valor_duplicata, 2, '.', '');
            $valor3         = 0;
            $valor4         = 0;
        }else {//então só existe um prazo o valor da duplicata é total
            $valor1         = number_format($valor_total_nota, 2, '.', '');
            $valor2         = 0;
            $valor3         = 0;
            $valor4         = 0;
        }
        $sql = "UPDATE `nfs_outras` SET valor1='$valor1', valor2='$valor2', valor3='$valor3', valor4='$valor4' WHERE `id_nf_outra` = '$id_nf_outra' LIMIT 1 ";
        bancos::sql($sql);
        return array($valor1, $valor2, $valor3, $valor4);
    }
        
    function comissao_ultimos3meses($id_representante = '%') {
        if(!class_exists('genericas'))  require 'genericas.php';//CASO EXISTA EU DESVIO A CLASSE ...
        /*******************************************************************************************************************/
        //Período de busca em cima das NF(s) p/ guardar na tabela de funcionários a média de comissão dos últimos 3 meses ...
        $datas_3meses_anterior      = genericas::retornar_data_relatorio(3);
        $data_inicial_3meses_anterior = data::datatodate($datas_3meses_anterior['data_inicial'], '-');

        $datas_mes_anterior         = genericas::retornar_data_relatorio(1);
        $data_final_mes_anterior    = data::datatodate($datas_mes_anterior['data_final'], '-');
        /*******************************************************************************************************************/
        //Busca a próxima Data do Holerith, maior do que a Data Final digitada pelo usuário no Filtro ...
        $sql = "SELECT qtde_dias_uteis_mes, qtde_dias_inuteis_mes, total_faturamento 
                FROM `vales_datas` 
                WHERE `data` > '$data_final_mes_anterior' LIMIT 1 ";
        $campos_data = bancos::sql($sql);
        if(count($campos_data) == 1) {
            $qtde_dias_uteis_mes    = $campos_data[0]['qtde_dias_uteis_mes'];
            $qtde_dias_inuteis_mes  = $campos_data[0]['qtde_dias_inuteis_mes'];
            $total_faturamento      = $campos_data[0]['total_faturamento'];
        }
        //Aqui eu só trago os representante que são funcionários ...
        $sql = "SELECT f.id_funcionario, f.id_cargo, f.id_empresa, f.id_pais, r.id_representante, r.porc_comissao_sob_fat, r.descontar_ir, r.tipo_pessoa 
                FROM `representantes` r 
                INNER JOIN `representantes_vs_funcionarios` rf ON rf.id_representante = r.id_representante 
                INNER JOIN `funcionarios` f ON f.id_funcionario = rf.id_funcionario 
                WHERE r.`ativo` = '1' 
                AND r.`id_representante` LIKE '$id_representante' ORDER BY nome_fantasia ";
        $campos_representante = bancos::sql($sql);//traz todos representantes ...
        $total_representantes = count($campos_representante);
        if($total_representantes > 0) {
            $vetor_empresas     = array(1, 2, 4);//São as empresas que temos cadastradas Albafer 1, Tool Master 2, Grupo 4 ...
            $linhas_empresas    = count($vetor_empresas);
            for($i = 0; $i < $total_representantes; $i++) {
                //Sempre zero esses valores p/ não herdar valores do Representante Anterior do Loop Anterior ...
                $total_comissao_pd      = 0;
                $total_comissao_pf      = 0;
                $total_geral_desconto_pd= 0;
                $total_geral_desconto_pf= 0;
                $sub_total_supervisor_pf= 0;
                $desconto_dev_super_pf  = 0;
                $campo_valor            = ($campos_representante[$i]['id_pais'] == 31) ? ' nfsi.valor_unitario ' : ' nfsi.valor_unitario_exp ';                    
                //Será feita uma verificação de tudo que foi negociado "Faturado" do Representante atual do Loop por Empresa ...
                for($j = 0; $j < $linhas_empresas; $j++) {//Início do For das Empresas ...
                    /*Aqui eu trago todas as NF´s do Representante que é funcionário no período dos últimos 3 meses 
                    de Folha da Empresa atual do Loop ...*/
                    $sql = "SELECT nfs.id_nf, nfs.id_empresa, nfs.data_emissao, nfs.suframa, nfs.status, nfs.snf_devolvida, 
                            IF(c.nomefantasia='', c.razaosocial, c.nomefantasia) cliente, c.id_pais, 
                            IF(nfs.status = '6', (SUM(ROUND((nfsi.qtde_devolvida * $campo_valor), 2)) * (-1)), SUM(ROUND((nfsi.qtde * $campo_valor),2))) AS tot_mercadoria, 
                            IF(nfs.status = '6', (SUM(ROUND((((nfsi.qtde_devolvida * $campo_valor) * nfsi.`comissao_new`) / 100), 2)) * (-1)), SUM(ROUND((((nfsi.qtde * $campo_valor) * nfsi.`comissao_new`) / 100), 2))) AS valor_comissao 
                            FROM `nfs_itens` nfsi 
                            INNER JOIN `nfs` ON nfs.id_nf = nfsi.id_nf AND nfs.`id_empresa` = '".$vetor_empresas[$j]."' 
                            INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
                            WHERE nfs.`data_emissao` BETWEEN '$data_inicial_3meses_anterior' AND '$data_final_mes_anterior' AND nfsi.id_representante = '".$campos_representante[$i]['id_representante']."' 
                            GROUP BY nfsi.id_nf ORDER BY nfs.id_empresa, nfs.data_emissao ";
                    $campos_nfs = bancos::sql($sql);
                    $linhas_nfs = count($campos_nfs);
                    for($k = 0; $k < $linhas_nfs; $k++) {
                        /*Se o Representante tiver registro pela Albafer ou Tool Master, então eu guardo o Total de Comissão 
                        nessa variável específica $total_coissao_pd ...*/
                        if($campos_representante[$i]['id_empresa'] != 4) {//Representante da Empresa Alba ou Tool Master ...
                            if($vetor_empresas[$j] == $campos_representante[$i]['id_empresa']) {//Empresa que o Rep está registrado ...
                                $total_comissao_pd+= $campos_nfs[$k]['valor_comissao'];
                            }else {//Não está registrado ...
                                $total_comissao_pf+= $campos_nfs[$k]['valor_comissao'];
                            }
                        }else {//Representante da Empresa Grupo não existe PD somente PF ...
                            $total_comissao_pf+= $campos_nfs[$k]['valor_comissao'];
                        }
                    }
                    /*Parte de Estorno Comissão do Representante que é funcionário no período dos últimos 3 meses 
                    de Folha da Empresa atual do Loop ...*/
                    $sql = "SELECT ce.tipo_lancamento, ce.porc_devolucao, ce.valor_duplicata 
                            FROM `comissoes_estornos` ce 
                            INNER JOIN `nfs` ON nfs.id_nf = ce.id_nf AND nfs.`id_empresa` = '".$vetor_empresas[$j]."' 
                            WHERE SUBSTRING(ce.data_lancamento, 1,10) BETWEEN '$data_inicial_3meses_anterior' AND '$data_final_mes_anterior' AND ce.id_representante = '".$campos_representante[$i]['id_representante']."' 
                            ORDER BY ce.data_lancamento ";
                    $campos_devolucao = bancos::sql($sql);
                    $linhas_devolucao = count($campos_devolucao);
                    if($linhas_devolucao > 0) {
                        for($k = 0; $k < $linhas_devolucao; $k++) {
                            $comissao = ($campos_devolucao[$k]['valor_duplicata'] * $campos_devolucao[$k]['porc_devolucao']) / 100;
                            /*Se o Representante tiver registro pela Albafer ou Tool Master, então eu desconto o Total de Comissão 
                            nessa variável específica $total_coissao_pd ...*/
                            if($campos_representante[$i]['id_empresa'] != 4) {//Representante da Empresa Alba ou Tool Master ...
                                if($vetor_empresas[$j] == $campos_representante[$i]['id_empresa']) {//Empresa que o Rep está registrado ...
                                    if($campos_devolucao[$j]['tipo_lancamento'] == 3) {//REEMBOLSO
                                        $total_geral_desconto_pd+= $comissao;
                                    }else {//DEVOLUÇÃO DE CANCELAMENTO, ATRASO DE PAGAMENTO, ABATIMENTO
                                        $total_geral_desconto_pd-= $comissao;
                                    }
                                }else {//Não está registrado ...
                                    if($campos_devolucao[$j]['tipo_lancamento'] == 3) {//REEMBOLSO
                                        $total_geral_desconto_pf+= $comissao;
                                    }else {//DEVOLUÇÃO DE CANCELAMENTO, ATRASO DE PAGAMENTO, ABATIMENTO
                                        $total_geral_desconto_pf-= $comissao;
                                    }
                                }
                            }else {//Representante da Empresa Grupo não existe PD somente PF ...
                                if($campos_devolucao[$j]['tipo_lancamento'] == 3) {//REEMBOLSO
                                    $total_geral_desconto_pf+= $comissao;
                                }else {//DEVOLUÇÃO DE CANCELAMENTO, ATRASO DE PAGAMENTO, ABATIMENTO
                                    $total_geral_desconto_pf-= $comissao;
                                }
                            }
                        }
                    }
                    /*****************************************************************************************************/
                    /*************************************Cálculo do Imposto de Renda*************************************/
                    /*****************************************************************************************************/
                    if($campos_representante[$i]['id_empresa'] != 4) {//IR somente p/ as Empresas Alba e Tool Master ...
                        if(strtoupper($campos_representante[$i]['descontar_ir']) == 'S') {
                            if($campos_representante[$i]['id_pais'] == 31) {//Brasil ...
                                if($campos_representante[$i]['tipo_pessoa'] == 'J') {//Pessoa Jurídica tem cálculo de I.R.
                                    $ir=- (round(($total_comissao_pd * 0.015), 2));
                                    if(abs($ir) > 10.00) {
                                        $ir = round($ir, 2);
                                    }else {
                                        $ir = 0;//Se o Valor for muito baixo ...
                                    }
                                }else {//Ignora o Imposto de Renda por ser Pessoa Física ...
                                    $ir = 0;
                                }
                            }else {//Internacional ...
                                $ir = 0;
                            }
                        }else {//Ignora o IR por causa do Cadastro ...
                            $ir = 0;
                        }
                    }else {//Empresa Grupo não tem IR ...
                        $ir = 0;
                    }
                    /*****************************************************************************************************/
                    /***********************************************Exceção***********************************************/
                    /*****************************************************************************************************/
                    if($campos_representante[$i]['id_representante'] == 14) {//Caso for a Mercedes, essa será a única que terá 1,5% em cima das Supervisões ...
                        $comissao_supervisao = 1.5;
                    }else {//Os demais terão 1% como sempre foi ...
                        $comissao_supervisao = 1;
                    }
                    /*****************************************************************************************************/
                    /*****************************************Parte de Supervisão*****************************************/
                    /*****************************************************************************************************/
                    //Se o Representante for Supervisor, então só serão realizados cálculos de Comissão de Supervisão p/ a Empresa Grupo ...
                    if(($campos_representante[$i]['id_cargo'] == 25 || $campos_representante[$i]['id_cargo'] == 109) && $vetor_empresas[$j] == 4) {
                        //Busco todos os Representantes subordinados do Representante atual do Loop ...
                        $sql = "SELECT rs.id_representante, IF(r.nome_fantasia = '', r.nome_representante, r.nome_fantasia) AS representante 
                                FROM `representantes` r 
                                INNER JOIN `representantes_vs_supervisores` rs ON rs.id_representante = r.id_representante 
                                WHERE rs.id_representante_supervisor = '".$campos_representante[$i]['id_representante']."' ORDER BY representante ";
                        $campos_sub = bancos::sql($sql);
                        $linhas_sub = count($campos_sub);
                        for($k = 0; $k < $linhas_sub; $k++) {
                            //Aqui eu trago todas as NF´s do Subordinado do Representante atual no período dos últimos 3 meses de Folha ...
                            $sql = "SELECT nfs.data_emissao, nfs.id_empresa, (SUM(ROUND(((nfsi.qtde * $campo_valor)),2)) - SUM(ROUND((nfsi.qtde_devolvida * $campo_valor), 2))) AS valor_nota 
                                    FROM `nfs_itens` nfsi 
                                    INNER JOIN `nfs` ON nfs.id_nf = nfsi.id_nf 
                                    INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
                                    WHERE nfs.`data_emissao` BETWEEN '$data_inicial_3meses_anterior' AND '$data_final_mes_anterior' AND nfsi.`id_representante` = '".$campos_sub[$k]['id_representante']."' 
                                    GROUP BY nfsi.id_representante, nfs.id_empresa ";
                            $campos_nfs = bancos::sql($sql);
                            $linhas_nfs = count($campos_nfs);
                            for($l = 0; $l < $linhas_nfs; $l++) $sub_total_supervisor_pf+= $campos_nfs[$l]['valor_nota'];//Somente PF ...
                            //Parte de Estorno de Comissão do Representante Subordinado do Representante atual do Loop ...
                            $sql = "SELECT IF(ce.tipo_lancamento = '3', ce.valor_duplicata, ce.valor_duplicata * (-1)) AS valor_descontar 
                                    FROM `comissoes_estornos` ce 
                                    INNER JOIN `nfs` ON nfs.id_nf = ce.id_nf 
                                    WHERE SUBSTRING(ce.`data_lancamento`, 1, 10) BETWEEN '$data_inicial_3meses_anterior' AND '$data_final_mes_anterior' AND ce.`id_representante` = '".$campos_sub[$k]['id_representante']."' 
                                    ORDER BY ce.data_lancamento ";
                            $campos_dev_super = bancos::sql($sql);
                            $linhas_dev_super = count($campos_dev_super);
                            for($l = 0; $l < $linhas_dev_super; $l++) $desconto_dev_super_pf+= $campos_dev_super[$l]['valor_descontar'];
                            $sub_total_supervisor_pf+= $desconto_dev_super_pf;
                        }
                    }
                    /*****************************************************************************************************/
                }//Fim do For das Empresas ...
                $subtotal_sobre_total_faturamento   = round($total_faturamento * $campos_representante[$i]['porc_comissao_sob_fat'] / 100, 2);

                /*Se o Representante tiver registro pela Albafer ou Tool Master, então em cima do $total_global_pd que será 
                calculado anteriormente eu somo as variáveis abaixo ...*/
                if($campos_representante[$i]['id_empresa'] != 4) {//$total_global_pd somente p/ as Empresas Alba e Tool Master ...
                    $total_global_pd                    = $total_comissao_pd + $ir + $total_geral_desconto_pd;
                }else {//Se o Representante for Grupo a variável é zerada, não existe PD, afinal não tem Registro ...
                    $total_global_pd                    = 0;
                }
                //Esse $subtotal_sobre_total_faturamento só pode ser atribuído em cima do PF ...
                $total_global_pf                    = $total_comissao_pf + ($sub_total_supervisor_pf * $comissao_supervisao / 100) + $total_geral_desconto_pf + $subtotal_sobre_total_faturamento;
                //Se a Qtde de Dias Úteis ou Qtde de Dias Inúteis = 0 ou o Representante for a Mercedes, então não existe cálculo p/ o DSR ...
                if($qtde_dias_uteis_mes == 0 || $qtde_dias_inuteis_mes == 0 || $campos_representante[$i]['id_representante'] == 14) {
                    $dsr_pd = 0;
                    $dsr_pf = 0;
                }else {
                    //Se o Representante tiver registro pela Albafer ou Tool Master, então existe o cálculo $dsr_pd ...
                    if($campos_representante[$i]['id_empresa'] != 4) {//$dsr_pd somente p/ as Empresas Alba e Tool Master ...
                        $dsr_pd = $total_global_pd / $qtde_dias_uteis_mes * $qtde_dias_inuteis_mes;
                    }else {//Se o Representante for Grupo a variável é zerada, não existe PD, afinal não tem Registro ...
                        $dsr_pd = 0;
                    }
                    $dsr_pf = $total_global_pf / $qtde_dias_uteis_mes * $qtde_dias_inuteis_mes;

                    if($dsr_pd < 0) $dsr_pd = 0;
                    if($dsr_pf < 0) $dsr_pf = 0;
                }
                $comissao_ultimos_3meses_pd = round(round($total_global_pd + $dsr_pd, 2) / 3, 2);
                $comissao_ultimos_3meses_pf = round(round($total_global_pf + $dsr_pf, 2) / 3, 2);
                //Aqui eu guardo na Tabela de Funcionários a média dos últimos 3 meses de vendas do Representante somado aos seus DSR ...
                $sql = "UPDATE `funcionarios` SET `comissao_ultimos3meses_pd` = '$comissao_ultimos_3meses_pd', `comissao_ultimos3meses_pf` = '$comissao_ultimos_3meses_pf' WHERE `id_funcionario` = '".$campos_representante[$i]['id_funcionario']."' LIMIT 1 ";
                bancos::sql($sql);
            }
        }
    }
    
/*Função que traz todos os textos de "CFOP / Natureza de Operação" de acordo com a CFOP de cada item 
da Nota Fiscal passada por parâmetro ...*/
    function texto_dados_adicionais($id_nf) {
        if(!class_exists('genericas'))  require 'genericas.php';//CASO EXISTA EU DESVIO A CLASSE ...
        
        //Aqui eu busco alguns dados do $id_nf passado por parâmetro
        
        /*Obs: Se a Nota Fiscal for uma Devolução coloco essa Letra E que equivale a Entrada, senão 
        S que equivale a Saída ...*/
        $sql = "SELECT c.`id_cliente`, c.`id_pais`, c.`id_uf`, nfs.`id_cliente`, nfs.`id_empresa`, nfs.`finalidade`, 
                IF(nfs.`status` = '6', 'E', 'S') AS tipo_negociacao, `texto_da_nota` 
                FROM `nfs` 
                INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
                WHERE nfs.`id_nf` = '$id_nf' LIMIT 1 ";
        $campos_nfs = bancos::sql($sql);
        
        if($campos_nfs[0]['id_pais'] == 31) {//Se o Cliente é do Tipo Nacional, então busco o Convênio da UF ...
            $sql = "SELECT `convenio` 
                    FROM `ufs` 
                    WHERE `id_uf` = '".$campos_nfs[0]['id_uf']."' LIMIT 1 ";
            $campos_uf = bancos::sql($sql);
            $convenio  = $campos_uf[0]['convenio'];
        }
        
        $perc_calculo_icms_st_recolhido_compra = (1 + (genericas::variavel(93) / 100));
        
        /*Busco todos os itens do $id_nf passado por parâmetro e alguns desses campos serão utilizados mais abaixo ...
        Essa é uma Margem de Lucro Mínima Razoável 40% ...

        Ex: está vendendo por R$ 140,00, que significa que comprou R$ 100,00 ...*/
        $sql = "SELECT `id_produto_acabado`, `qtde`, (`valor_unitario` / $perc_calculo_icms_st_recolhido_compra) AS valor_unit_compra 
                FROM `nfs_itens` 
                WHERE `id_nf` = '$id_nf' ";
        $campos_itens = bancos::sql($sql);
        $linhas_itens = count($campos_itens);
        
        //Esse vetor será utilizado mais abaixo ...
        $vetor_cfops_itens = array();
        
        for($i = 0;  $i < $linhas_itens; $i++) {
            //Busco a CFOP do item da Nota Fiscal ...
            $dados_produto = intermodular::dados_impostos_pa($campos_itens[$i]['id_produto_acabado'], $campos_nfs[0]['id_uf'], $campos_nfs[0]['id_cliente'], $campos_nfs[0]['id_empresa'], $campos_nfs[0]['finalidade'], $campos_nfs[0]['tipo_negociacao'], $id_nf);
            
            if(!in_array($dados_produto['cfop'], $vetor_cfops_itens)) array_push($vetor_cfops_itens, $dados_produto['cfop']);
        }
       
        //Disparo um Loop de todas as CFOPs que foram encontradas acima ...
        for($i = 0; $i < count($vetor_cfops_itens); $i++) {
            //À partir daqui, busco todos os Textos de CFOP de acordo com a respectiva CFOP do Loop ...
            $sql = "SELECT `descricao` 
                    FROM `cfops` 
                    WHERE `cfop` = '".substr($vetor_cfops_itens[$i], 0, 1)."' 
                    AND `num_cfop` = '".substr($vetor_cfops_itens[$i], 2, 3)."' 
                    AND `ativo` = '1' ";
            $campos_texto = bancos::sql($sql);
            
            //Encontrou a respectiva CFOP cadastrada ...
            if(count($campos_texto) == 1) {
                if(!empty($campos_texto[0]['descricao'])) {
                    /*******************************************************************************************/
                    /********************CFOP. 5405 - Textos adicionais p/ todos os Clientes********************/
                    /*******************************************************************************************/
                    if($vetor_cfops_itens[$i] == '5.405') {//Nessa CFOP, a situação é um pouquinho diferente ...
                        for($j = 0; $j < $linhas_itens; $j++) {
                            $valor_total        = ($campos_itens[$j]['qtde'] * $campos_itens[$j]['valor_unit_compra']);

                            $dados_produto      = intermodular::dados_impostos_pa($campos_itens[$i]['id_produto_acabado'], $campos_nfs[0]['id_uf'], $campos_nfs[0]['id_cliente'], $campos_nfs[0]['id_empresa'], $campos_nfs[0]['finalidade'], $campos_nfs[0]['tipo_negociacao'], $id_nf);
                            $ipi                = $dados_produto['ipi'];
                            $icms_cadastrado    = $dados_produto['icms_cadastrado'];
                            $iva_cadastrado     = $dados_produto['iva_cadastrado'];
                            
                            $valor_icms         = ($icms_cadastrado / 100) * $valor_total;
                            
                            //Cálculo do Valor de ICMS ST - por ser de compra, por isso que passo 2 vezes a mesma variável $icms_cadastrado ...
                            $vetor_dados_substituicao_tributaria = calculos::calculos_substituicao_tributaria($ipi, $icms_cadastrado, $icms_cadastrado, $iva_cadastrado, $valor_total, $valor_icms);
                
                            //Acumula o Total de Todas as variáveis referentes ao ST ...
                            $base_calculo_icms_st+= $vetor_dados_substituicao_tributaria['base_calculo_icms_st_item_current_rs'];
                            
                            $vetor_dados_substituicao_tributaria['base_calculo_icms_st_item_current_rs'];
                            
                            $valor_icms_st+=        $vetor_dados_substituicao_tributaria['valor_icms_st_item_current_rs'];
                        }
                        $texto_nf.= $vetor_cfops_itens[$i].' - '.str_replace('XXXX', number_format($base_calculo_icms_st, 2, ',', '.'), $campos_texto[0]['descricao']);
                        $texto_nf = str_replace('YYYY', number_format($valor_icms_st, 2, ',', '.'), $texto_nf);
                    /*******************************************************************************************/
                    }else {//Outras CFOP(s), aí já é normal ...
                        $texto_nf.= $vetor_cfops_itens[$i].' - '.str_replace('xxxxxx', $convenio, $campos_texto[0]['descricao']);
                    }
                    //Enquanto não chegar no último registro, o sistema vai adicionando Quebra de Linha ...
                    if(($i + 1) < count($vetor_cfops_itens)) $texto_nf.= chr(13);
                }
            }
        }
        
        /************************************************************************************************/
        /*Tratamento somente p/ Clientes do Estado de Mato Grosso conforme:

        § 2º do Art. 50 do Anexo V do Novo RICMS/MT - 20/08/2014 ...*/
        /************************************************************************************************/
        if($campos_nfs[0]['id_uf'] == 34) {
            $calculo_total_impostos = calculos::calculo_impostos(0, $id_nf, 'NF');
            $base_calculo_icms_mt   = round($calculo_total_impostos['valor_total_nota'] * 1.45, 2);//Esses 45% equivalem a Margem de Lucro do Estado ...

            $texto_nf.= chr(13).chr(13).'Base de Cálculo de ICMS/MT = R$ '.number_format($base_calculo_icms_mt, 2, ',', '.');
            $texto_nf.= chr(13).'Valor do ICMS/MT = R$ '.number_format($base_calculo_icms_mt * 0.07, 2, ',', '.');//Esses 7% é a Alíquota de ICMS do Estado ...
            $texto_nf.= chr(13).'Conforme § 2º do Art. 50 do Anexo V do Novo RICMS/MT';
        }
        /************************************************************************************************/
        
        //Sempre que o campo "Valor do ICMS ST" > 0, mostro esse texto complementar abaixo ...
        if($calculo_total_impostos['valor_icms_st'] > 0) $texto_nf.= chr(13).'Imposto Recolhido antecipadamente conf. artigo 313 - Z3 do RICMS/SP. ';
        
        //Somente se o Cliente for "Nortel de Campinas" que terá que vir o dizer abaixo em específico ...
        if($campos_nfs[0]['id_cliente'] == 38985) $texto_nf.= ' - "(Sujeito passivo por Substituição Tributaria, conf. Regime Especial - Processo UA 80949-987237/2012). Em razão dessa condição, não deve haver destaque ou cobrança do ICMS/ST."';
        
        //Se o campo "texto_da_nota" estiver preenchido no Cadastro de Cliente, então esse vem como sugestão p/ o texto aqui da Nota Fiscal ...
        if(!empty($campos_nfs[0]['texto_da_nota'])) $texto_nf.= chr(13).$campos_nfs[0]['texto_da_nota'];
       
        return $texto_nf;
    }
    
/*Apesar de ser uma lógica simples, resolvi criar uma função para apresentação do número de Remessa 
por causa dos links dos sites e sendo assim a manutenção fica muito mais fácil p/ administrar ...*/
    function numero_remessa($id_nf) {
        //Aqui eu busco o nome da Transportadora, através do $id_nf passado por parâmetro ...
        $sql = "SELECT nfs.`numero_remessa`, t.`nome` 
                FROM `nfs` 
                INNER JOIN `transportadoras` t ON t.`id_transportadora` = nfs.`id_transportadora` 
                WHERE nfs.`id_nf` = '$id_nf' LIMIT 1 ";
        $campos_nfs = bancos::sql($sql);
        
        if(strpos($campos_nfs[0]['nome'], 'CORREIO') !== false) {//Transportadora do Tipo "CORREIO" ...
            return "<a href='https://www2.correios.com.br/sistemas/rastreamento/' class='html5lightbox'>".$campos_nfs[0]['numero_remessa'].'</a>';
        }else if(strpos($campos_nfs[0]['nome'], 'TAM') !== false) {//Transportadora do Tipo "TAM" ...
            return "<a href='http://www.tamcargo.com.br' class='html5lightbox'>".$campos_nfs[0]['numero_remessa'].'</a>';
        }else {
            return $campos_nfs[0]['numero_remessa'];
        }
    }

/************************************************************************************************/
/****Método que controla a Chave de NF(s) de Venda ou NF(s) Outra(s) independente da Empresa*****/
/************************************************************************************************/	
//Tipo_nf = 'S', 'D' então busca na Tab. de Notas de Saída, Devolução - Tipo_nf = 'O' então busca na Tab. Outras ...
    function gerar_chave_acesso_nfe($id_nf_parametro, $tipo_nf = 'S') {//Como a maioria é NF de Saída joguei padrão S ...
        if($tipo_nf == 'S' || $tipo_nf == 'D') {
            $numero_nf 	= faturamentos::buscar_numero_nf($id_nf_parametro, 'S');
            //Busca de alguns dados de NF para Gerar a Chave de acesso ...
            $sql = "SELECT e.cnpj, concat(substring(nfs.data_emissao, 3, 2), substring(nfs.data_emissao, 6, 2)) as ano_mes, ufs.codigo 
                    FROM `nfs` 
                    INNER JOIN `empresas` e on e.id_empresa = nfs.id_empresa 
                    INNER JOIN `ufs` on ufs.id_uf = e.id_uf 
                    WHERE nfs.id_nf = '$id_nf_parametro' limit 1 ";
//Provavelmente então, este esse Número foi utilizado por uma NF Outra ...
        }else if($tipo_nf == 'O') {
            $numero_nf 	= faturamentos::buscar_numero_nf($id_nf_parametro, 'O');
            $sql = "SELECT e.cnpj, concat(substring(nfso.data_emissao, 3, 2), substring(nfso.data_emissao, 6, 2)) as ano_mes, ufs.codigo 
                    FROM `nfs_outras` nfso 
                    INNER JOIN `empresas` e on e.id_empresa = nfso.id_empresa 
                    INNER JOIN `ufs` on ufs.id_uf = e.id_uf 
                    WHERE nfso.id_nf_outra = '$id_nf_parametro' limit 1 ";
        }
        $campos = bancos::sql($sql);
        //02 - cUF  - código da UF do emitente do Documento Fiscal
        $chave = $campos[0]['codigo'];
        //04 - AAMM - Ano e Mes de emissão da NF-e
        $chave.= $campos[0]['ano_mes'];
        //14 - CNPJ - CNPJ do emitente
        $chave.= $campos[0]['cnpj'];
        //02 - mod  - Modelo do Documento Fiscal
        $chave.= 55;
        //03 - serie - Série do Documento Fiscal
        $chave.= '001';
        //09 - nNF  - Número do Documento Fiscal
        $tamanho_disp_numero_nf = 9;
        $tamanho_ocup_numero_nf = strlen($numero_nf);
        $tamanho_free_numero_nf = $tamanho_disp_numero_nf - $tamanho_ocup_numero_nf;
        for($i = 0; $i < $tamanho_free_numero_nf; $i++) $zeros_numero_nf.= '0';
        $chave.= $zeros_numero_nf.$numero_nf;
        //09 - cNF  - Código Numérico que compõe a Chave de Acesso, sempre gera um Número Randômico ...
        $chave.= mt_rand(100000000, 999999999);
        //01 - cDV  - Dígito Verificador da Chave de Acesso
        $multiplicadores = array(2, 3, 4, 5, 6, 7, 8, 9);
        $i = 42;
        while ($i >= 0) {
            for ($m = 0; $m < count($multiplicadores) && $i >= 0; $m++) {
                $soma_ponderada+= $chave[$i] * $multiplicadores[$m];
                $i--;
            }
        }
        $resto = $soma_ponderada % 11;
        $chave.= ($resto == 0 || $resto == 1) ? 0 : 11 - $resto;
        for($i = 0; $i < strlen($chave); $i++) {
            $chave_espaco.= ($i != 0 && $i % 4 == 0) ? ' '.$chave[$i] : $chave[$i];
        }
        return $chave_espaco;
    }
    
    //Essa função retorna um array com Tipos de Despacho que será utilizada em toda parte de Faturamento ...
    function tipos_despacho() {
        return array(
            '1' => 'PORTARIA', 
            '2' => 'SAIU P/ ENTREGA', 
            '3' => 'COLETADO / ENTREGUE' 
        );
    }
}
?>