<?
if(!class_exists('bancos')) require 'bancos.php';//CASO EXISTA EU DESVIO A CLASSE ...
class compras_new {
    function atualizar_importacao($id_pedido, $soma_prazo=0) {
        if(!class_exists('genericas')) require 'genericas.php';// CASO EXISTA EU DESVIO A CLASSE
//Aqui eu verifico se o pedido j� foi importado antes pelo financeiro ...
        $sql = "SELECT id_conta_apagar 
                FROM `contas_apagares` 
                WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) $id_conta_apagar = $campos[0]['id_conta_apagar'];
//Aqui verifica se o pedido possui alguma importa��o ...
        $sql = "SELECT id_importacao 
                FROM `pedidos` 
                WHERE `id_pedido` = '$id_pedido' 
                AND `id_importacao` > '0' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) $id_importacao = $campos[0]['id_importacao'];
        /*Aqui verifica o valor total do pedido, que � a somat�ria do valor total do item
        que j� � a multiplica��o da quantidade vs o pre�o unit�rio pedido*/
        $sql = "SELECT preco_unitario, qtde, ipi 
                FROM `itens_pedidos` 
                WHERE `id_pedido` = '$id_pedido' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas > 0) {
            for($i = 0; $i < $linhas; $i++) {
                $valor	= ($campos[$i]['qtde'] * $campos[$i]['preco_unitario']);
                $ipi	= $campos[$i]['ipi'];
                $total	= $valor;
                $total+= (($total * $ipi) / 100);
                $valor_total+= $total;
            }
        }
//Aqui eu busco o �ltimo valor de Fator Custo ...
        $fator_custo_importacao = genericas::variavel(1);
//Aqui � o c�lculo do valor com o valor do fator ...
        $valor_total*= ($fator_custo_importacao - 1);
//Selecao do tipo de moeda e da data de emissao
        $sql = "Select date_format(data_emissao, '%d/%m/%Y') as data_emissao, id_tipo_moeda 
                from pedidos 
                where id_pedido = $id_pedido limit 1 ";
        $campos = bancos::sql($sql);
        $id_tipo_moeda	= $campos[0]['id_tipo_moeda'];
        $data_emissao	= $campos[0]['data_emissao'];
        $data_vencimento= data::adicionar_data_hora($data_emissao, $soma_prazo); //Aqui atualiza a conta � pagar
        $data_vencimento = data::datatodate($data_vencimento, '-');
        //Se existe uma conta que est� atrelada ao pedido ent�o
        if(isset($id_conta_apagar)) {
                $sql = "Update contas_apagares set `id_tipo_moeda` = '$id_tipo_moeda', `valor` = '$valor_total', `data_vencimento` = '$data_vencimento' where id_conta_apagar = '$id_conta_apagar' and status < 2 LIMIT 1 ";
                bancos::sql($sql);
                if(isset($id_importacao)) {//Se existe uma importa��o daquela conta ent�o
                        $sql = "SELECT id_importacao 
                                FROM `contas_apagares` 
                                WHERE `id_conta_apagar` = '$id_conta_apagar' 
                                AND `id_importacao` > 0 ";
                        $campos = bancos::sql($sql);
                        if(count($campos) == 1) {//Busca o nome da Importa��o ...
                                $sql = "SELECT nome 
                                        FROM `importacoes` 
                                        WHERE `id_importacao` = '$id_importacao' LIMIT 1 ";
                                $campos = bancos::sql($sql);
                                $nome	= $campos[0]['nome'];
                                $conta	= $nome.' - '.$id_pedido;
                                $sql = "UPDATE `contas_apagares` SET `numero_conta` = '$conta' WHERE `id_conta_apagar` = '$id_conta_apagar' AND `id_pedido` = '$id_pedido' LIMIT 1 ";
                                bancos::sql($sql);
                        }else {//Atualiza a importa��o no Contas � Pagar ...
                                $sql = "UPDATE `contas_apagares` SET `id_importacao` = '$id_importacao' WHERE `id_conta_apagar` = '$id_conta_apagar' LIMIT 1 ";
                                bancos::sql($sql);
                        }
                }else {//N�o existe nenhuma importa��o ...
                        $conta = 'Numer�rio - '.$id_pedido;
                        $sql = "UPDATE `contas_apagares` SET `numero_conta` = '$conta' WHERE `id_conta_apagar` = '$id_conta_apagar' AND `id_pedido` = '$id_pedido' LIMIT 1 ";
                        bancos::sql($sql);
                        $sql = "UPDATE `contas_apagares` SET `id_importacao` = '0' WHERE id_conta_apagar = '$id_conta_apagar' LIMIT 1 ";
                        bancos::sql($sql);
                }
        }
    }

    function pedido_status($id_item_pedido) {
//Pega o Pedido e verifica a qtde que foi solicitada no item do pedido ...
        $sql = "SELECT `qtde`, `id_pedido` 
                FROM `itens_pedidos` 
                WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
        $campos             = bancos::sql($sql);
        $qtde_solicitada    = $campos[0]['qtde'];
        $id_pedido          = $campos[0]['id_pedido'];
//Verifica a qtde total que chegou do produto em todas as notas fiscais ...
        $sql = "SELECT SUM(`qtde_entregue`) AS total_entregue 
                FROM `nfe_historicos` 
                WHERE `id_item_pedido` = '$id_item_pedido' ";
        $campos         = bancos::sql($sql);
        $total_entregue = $campos[0]['total_entregue'];
        if($total_entregue < 0) {//significa que o controle � negativo
            if($total_entregue == $qtde_solicitada) {//Entrega Total ...
                $status = 2;
            }else {//Entrega Parcial ...
                $status = 1;
            }
        }else if($total_entregue == 0) {//Nada Entregue ...
            $status = 0;
        }else if($total_entregue < $qtde_solicitada) {//Entrega Parcial ...
            $status = 1;
        }else if($total_entregue >= $qtde_solicitada) {//Entrega Total ...
            $status = 2;
        }
        //Atualizando o id_item_pedido ...
        $sql = "UPDATE `itens_pedidos` SET `status` = '$status' WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
        bancos::sql($sql);
        
        //Aqui eu atualizo o "status" do id_os_item se � que este id_item_pedido esteja vinculado a este ...
        $sql = "UPDATE `oss_itens` SET `status` = '$status' WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
        bancos::sql($sql);
        
//Situacao do pedido
        $sql = "SELECT `id_item_pedido` 
                FROM `itens_pedidos` 
                WHERE `id_pedido` = '$id_pedido' 
                AND `status` < '2' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 0) {
            //Verifica se o Pedido possui Antecipa��es em Pend�ncia ou Liberada - que � quando n�o foi importada p/ NF ...
            $sql = "SELECT `id_antecipacao` 
                    FROM `antecipacoes` 
                    WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
            $campos_antecipacao = bancos::sql($sql);
/*Atualizando o status do Pedido para parcial, porque mesmo tendo fechado todos os Itens, 
existem Antecipa��es em Pend�ncia ...*/
            if(count($campos_antecipacao) == 1) {
                $sql = "UPDATE `pedidos` SET `status` = '1' WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
//Atualizando o status do Pedido para concluido, significa que todos os Itens de Pedido est�o concluidos ...
            }else {
                $sql = "UPDATE `pedidos` SET `status` = '2' WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
            }
        }else {
            $sql = "UPDATE `pedidos` SET `status` = '1' WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
        }
        bancos::sql($sql);
    }
    
    function valor_pendencia($id_pedido) {
        $valor_pendencia = 0;
        //Verifico tudo o que est� em Pend�ncia do Pedido passador por par�metro ...
        $sql = "SELECT `id_item_pedido`, `preco_unitario`, `qtde` 
                FROM `itens_pedidos` 
                WHERE `id_pedido` = '$id_pedido' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
            //Verifico se o Item de Pedido corrente j� est� na NF de Entrada ...
            $sql = "SELECT SUM(`qtde_entregue`) AS total_entregue 
                    FROM `nfe_historicos` 
                    WHERE `id_item_pedido` = '".$campos[$i]['id_item_pedido']."' ";
            $campos_nfe = bancos::sql($sql);
            if($campos_nfe[0]['total_entregue'] != 0) {//Significa que algo ou tudo j� est� em NF ...
                $valor_pendencia+= (($campos[$i]['qtde'] - $campos_nfe[0]['total_entregue']) * $campos[$i]['preco_unitario']);
            }else {
                $valor_pendencia+= $campos[$i]['qtde'] * $campos[$i]['preco_unitario'];//Representa uma Pend�ncia Total do Item, pq ainda n�o est� em NF ...
            }
        }
        //Atualiza o Pedido passador por par�metro com o Valor de Pend�ncia ...
        $sql = "UPDATE `pedidos` SET `valor_pendencia` = '$valor_pendencia' WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
        bancos::sql($sql);
    }

    function pedido_status_excluir($id_pedido) {
        /* verifica se tem mais de um registro para nao fechar a nota nova*/
        $sql = "Select id_item_pedido 
                from itens_pedidos 
                where id_pedido='$id_pedido' limit 1 ";
        $campos_nova = bancos::sql($sql);
        /* verifica se tem registro com status em aberto*/
        $sql = "Select id_item_pedido 
                from itens_pedidos 
                where id_pedido = '$id_pedido' 
                and status < 2 limit 1 ";
        $campos = bancos::sql($sql);
        if((count($campos)==0)&&(!count($campos_nova) == 0)) {
            $sql = "Update pedidos set status = 2 where id_pedido = '$id_pedido' limit 1";
        }else {
            $sql = "Update pedidos set status = 1 where id_pedido = '$id_pedido' limit 1";
        }
        bancos::sql($sql);
    }

    function atualizar_status_item_cotacao($id_cotacao_item) {
        //Busco a qtde Pedida do Item da Cota��o para fazer o Controle de Status ...
        $sql = "SELECT `qtde_pedida` 
                FROM `cotacoes_itens` 
                WHERE `id_cotacao_item` = '$id_cotacao_item' LIMIT 1 ";
        $campos_cotacao = bancos::sql($sql);
        $qtde_cotacao 	= $campos_cotacao[0]['qtde_pedida'];
        
        /*Busco o total que j� foi Importado do Item da Cota��o na Tabela de Itens de Pedido 
        para fazer a Compara��o ...*/
        $sql = "SELECT SUM(`qtde`) AS total_importado_cotacao 
                FROM `itens_pedidos` 
                WHERE `id_cotacao_item` = '$id_cotacao_item' ";
        $campos_pedido  = bancos::sql($sql);
        $qtde_importado_cotacao	= $campos_pedido[0]['total_importado_cotacao'];
        //Aqui eu tamb�m fa�o controle de Status do Item da Cota��o ...
        if($qtde_cotacao == $qtde_importado_cotacao) {
            $status_item = 2;//Item de Cota��o conclu�do
        }else {//Se n�o foi importado nada ou foi importado alguma coisa ...
            $status_item = ($qtde_importado_cotacao == 0) ? 0 : 1;
        }
        //Atualizando o Status do Item da Cota��o ...
        $sql = "UPDATE `cotacoes_itens` SET `status` = '$status_item' WHERE `id_cotacao_item` = '$id_cotacao_item' LIMIT 1 ";
        bancos::sql($sql);
    }

    function atualizar_status_cotacao($id_cotacao) {
//1) Aqui eu verifico o Total de Itens que existe nessa Cota��o
        $sql = "SELECT COUNT(`id_cotacao_item`) AS qtde_itens_cotacao 
                FROM `cotacoes_itens` 
                WHERE `id_cotacao` = '$id_cotacao' ";
        $campos_cotacao = bancos::sql($sql);
        $qtde_itens_cotacao = $campos_cotacao[0]['qtde_itens_cotacao'];
//2) Aqui eu verifico se existe algum Item de Cota��o que ficou pendente ...
        $sql = "SELECT COUNT(`qtde_pedida`) AS total_itens_aberto 
                FROM `cotacoes_itens` 
                WHERE `id_cotacao` = '$id_cotacao' 
                AND `status` < '2' ";
        $campos_cotacao = bancos::sql($sql);
        $total_itens_aberto = $campos_cotacao[0]['total_itens_aberto'];
//Significa que n�o existe + nenhum item pendente, sendo assim eu posso concluir essa Cota��o ...
        if($total_itens_aberto == 0) {
            $status = 2;
        }else {//Ainda existem itens em abertos ... ent�o continou com a Cota��o em aberto ainda ...
            //Cota��o em aberto de forma Total ou Parcial ...
            $status = ($total_itens_aberto == $qtde_itens_cotacao) ? 0 : 1;
        }
//Atualizo o Status da Cota��o ...
        $sql = "UPDATE `cotacoes` SET `status` = '$status' WHERE `id_cotacao` = '$id_cotacao' LIMIT 1 ";
        bancos::sql($sql);
    }

    function consumo_medio_mensal($id_produto_insumo) {
        if(!class_exists('genericas')) require 'genericas.php';// CASO EXISTA EU DESVIO A CLASSE
        
        $meses                  = genericas::variavel(71);
        $data_atual             = date('Y-m-d');
        $data_x_meses_atras     = data::datatodate(data::adicionar_data_hora(date('d-m-Y'), -($meses * 30)), '-');//X meses atr�s ...
/*Verifico o Total Baixado ou Manipulado da mercadoria no �ltimo ano e que n�o tenha tido 
troca de Material ...*/
        $sql_consumo = "SELECT SUM(`qtde`) AS total_baixado_manipulado 
                        FROM `baixas_manipulacoes` 
                        WHERE `id_produto_insumo` = '$id_produto_insumo' 
                        AND `acao` IN ('B', 'M') 
                        AND `data_sys` >= '$data_x_meses_atras' 
                        AND `troca` = 'N' ";
/*Verifico se existe Baixa ou Manipula��o da mercadoria a + de 1 ano e que n�o tenha tido 
troca de Material ...*/
        $sql = "SELECT data_sys 
                FROM `baixas_manipulacoes` 
                WHERE `id_produto_insumo` = '$id_produto_insumo' 
                AND `acao` IN ('B', 'M') 
                AND `data_sys` <= '$data_x_meses_atras' 
                AND `troca` = 'N' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {//Existe Baixa ou Manipula��o a + de 1 ano ...
            $campos_consumo = bancos::sql($sql_consumo);
//Aqui eu inverto o Sinal, porque na hora de se gravar as baixas, se grava com o sinal invertido ...
            $cmm = ($campos_consumo[0]['total_baixado_manipulado'] / $meses) * -1;
            return number_format($cmm, 2, ',', '.');
        }else {//Pode ser que existe Baixa ou Manipula��o anterior a 1 ano ...
//Busco a Primeira Data anterior a 1 ano e que n�o tenha tido troca de Material ...
            $sql = "SELECT SUBSTRING(`data_sys`, 1, 10) AS data_sys 
                    FROM `baixas_manipulacoes` 
                    WHERE `id_produto_insumo` = '$id_produto_insumo' 
                    AND `acao` IN ('B', 'M') 
                    AND `troca` = 'N' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 1) {//Significa que existe Baixa ou Manipula��o anterior a 1 ano ...
                $diff_dias = data::diferenca_data($campos[0]['data_sys'], $data_atual);
/*Se a qtde de meses retornada for > q 12, ent�o pra mim j� nem me interessa mais at� 
pq eu s� preciso do CMM dos �ltimos 12 meses apenas*/
                $meses = (intval($diff_dias[0]) / 30);
                if($meses > 0) {//Posso calcular normalmente, pq n�o d� erro de divis�o por Zero ...
                    $campos_consumo = bancos::sql($sql_consumo);
/*Aqui eu inverto o Sinal, porque na hora de se gravar as baixas, se grava com o sinal 
invertido ...*/
                    $cmm = ($campos_consumo[0]['total_baixado_manipulado'] / $meses) * -1;
                    return number_format($cmm, 2, ',', '.');
                }else {
                    return 0;
                }
            }else {//N�o existe sendo assim Zero ...
                return 0;
            }
        }
    }

    function verificar_irregularidades_nfe($id_nfe) { //verificar_irregularidades de importacao
        $sql = "SELECT f.id_pais 
                FROM `nfe` 
                INNER JOIN `fornecedores` f ON f.`id_fornecedor` = nfe.`id_fornecedor` 
                WHERE nfe.`id_nfe` = '$id_nfe' ";
        $campos     = bancos::sql($sql);
        $id_pais    = $campos[0]['id_pais'];
        if($id_pais != 31) {
            $sql = "SELECT id_importacao 
                    FROM `nfe` 
                    WHERE `id_nfe` = '$id_nfe' 
                    AND `id_importacao` > '0' ";
            $campos = bancos::sql($sql);
            if(count($campos) == 0) {
?>
        <Script Language = 'Javascript'>
            alert('ATEN��O !!! ESTA NOTA N�O POSSUI UMA IMPORTA��O !')
            document.form.aberto.disabled = true
        </Script>
<? 
            }
        }
    }

//Retorna o Valor Total do Pedido de Compras com IPI
    function valor_total_ped_com_ipi($id_pedido) {
//Busca o Tipo de Nota do Pedido de Compras
        $sql = "SELECT tipo_nota 
                FROM `pedidos` 
                WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
        $campos = bancos::sql($sql);
        $tipo_nota = $campos[0]['tipo_nota'];
        //Busca todos os Itens do Pedido de Compras
        $sql = "SELECT preco_unitario, qtde, ipi 
                FROM `itens_pedidos` 
                WHERE `id_pedido` = '$id_pedido' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
            //Somat�rio do Valor dos Itens ...
            $valor_total_sem_ipi+= ($campos[$i]['qtde'] * $campos[$i]['preco_unitario']);
            //Quando � SGD, n�o existe IPI
            $ipi    = ($tipo_nota == 2) ? 0 : $campos[$i]['ipi'];
//Somat�rio somente dos IPI(s) de Cada Item
            $valor_com_ipi = (($campos[$i]['qtde'] * $campos[$i]['preco_unitario']) * $ipi) / 100;
            $total_valor_com_ipi+= $valor_com_ipi;
        }
        return $valor_total_sem_ipi + $total_valor_com_ipi;
    }

    function calculo_valor_antecipacao($id_nfe) {
        $sql = "SELECT COUNT(a.id_antecipacao) AS qtde_antecipacao, SUM(a.valor) AS valor_total_antecipacoes 
                FROM `nfe_antecipacoes` nfea 
                INNER JOIN `antecipacoes` a ON a.`id_antecipacao` = nfea.`id_antecipacao` 
                WHERE nfea.`id_nfe` = '$id_nfe' ";
        $campos = bancos::sql($sql);
//N�o encontrou nenhuma antecipa��o p/ esta Nota Fiscal ...
        if($campos[0]['qtde_antecipacao'] == 0) {
            return array('qtde_antecipacao' => 0, 'valor_total_antecipacoes' => 0);
//Encontrou pelo menos 1 antecipa��o ...
        }else {
            return array('qtde_antecipacao' => $campos[0]['qtde_antecipacao'], 'valor_total_antecipacoes' => $campos[0]['valor_total_antecipacoes']);
        }
    }

//C�lculo para fazer a divis�o das parcelas pelo jeito de Vencimento ...
    function calculo_valor_financiamento($id_nfe) {
//1) Busca dos Dados de Cabe�alho desta Nota Fiscal p/ saber o Valor Total da NFE ...
        $sql = "SELECT `tipo`, DATE_FORMAT(SUBSTRING(`data_emissao`, 1, 10), '%d/%m/%Y') AS data_emissao 
                FROM `nfe` 
                WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
        $campos                 = bancos::sql($sql);
        $calculo_total_impostos = calculos::calculo_impostos(0, $id_nfe, 'NFC');
        $data_emissao           = $campos[0]['data_emissao'];
/**********************Valor Total da NFE**********************/
/*Busca do Valor e os Dias das Parcelas da NF para poder montar uma Nova Data de Vencimento 
com a Data de Entrega digitada pelo usu�rio ...*/
        $sql = "SELECT `id_nfe_financiamento`, `dias` 
                FROM `nfe_financiamentos` 
                WHERE `id_nfe` = '$id_nfe' ORDER BY `dias` ";
        $campos_financiamento = bancos::sql($sql);
        $linhas_financiamento = count($campos_financiamento);
/*Sempre que eu carrego essa fun��o, eu pego o valor Total da NF e divido pela qtde de Parcelas de um modo 
exato p/ que n�o d� erro de diferen�a de Valores caso tenha sido exclu�da alguma antecipa��o anteriormente*/
//Gero o Valor p/ cada parcela ...
        if($linhas_financiamento > 0) {//Tem que ter pelo menos 1 parcela gerada ...
            $valor_parcela_nf = round(round(((float)($calculo_total_impostos['valor_total_nota'] / $linhas_financiamento)), 3), 2);
            $valor_total_financiamento = 0;
//Aqui eu gero as Parcelas de Financiamento da NF mediante ao Pedido selecionado pelo usu�rio ...
            for($i = 0; $i < $linhas_financiamento; $i++) {
/*Quando eu estiver na �ltima parcela ent�o o Sistema verifica se est� coerente o somat�rio das Parcelas 
com o Valor total da NFE, caso isso n�o aconte�a eu jogo nessa parcela a diferen�a p/ que venha
resultar no valor Total da NFE ...*/
                if(($i + 1) == $linhas_financiamento) $valor_parcela_nf = $calculo_total_impostos['valor_total_nota'] - $valor_total_financiamento;
//Aqui eu atualizo a Data de Venc. das Parcelas somando da Data de Ent. digitada pelo usu�rio ...
                $data_gravar = data::datatodate(data::adicionar_data_hora($data_emissao, $campos_financiamento[$i]['dias']), '-');
//Atualizando o Valor de cada parcela e a Data de Vencimento de cada uma dessas ...
                $sql = "UPDATE `nfe_financiamentos` SET `data` = '$data_gravar', `valor_parcela_nf` = '$valor_parcela_nf' WHERE `id_nfe_financiamento` = '".$campos_financiamento[$i]['id_nfe_financiamento']."' LIMIT 1 ";
                bancos::sql($sql);
                $valor_total_financiamento+= $valor_parcela_nf;
            }
//Atualiza as Parcelas de Financiamento na NF p/ seja debatida a parte de Antecipa��es ...
            $sql = "SELECT `id_nfe_financiamento`, `valor_parcela_nf` 
                    FROM `nfe_financiamentos` 
                    WHERE `id_nfe` = '$id_nfe' ORDER BY `dias` ";
            $campos_financiamento = bancos::sql($sql);
            $linhas_financiamento = count($campos_financiamento);
//2) Valor Total das Antecipa��es da NFE ...
            $retorno_antecipacoes       = compras_new::calculo_valor_antecipacao($id_nfe);
            $valor_total_antecipacoes   = $retorno_antecipacoes['valor_total_antecipacoes'];
//Se existir antecipa��es ent�o ...
            if($valor_total_antecipacoes > 0) {
//Disparo do Loop ...
                for($i = 0; $i < $linhas_financiamento; $i++) {
//Enquanto o Valor da Antecipa��o for diferente de Zero, ent�o eu vou fazendo a verifica��o nesse loop ...
                    if($valor_total_antecipacoes != 0) {
//Verifica se o Valor da Antecipa��o � maior ou igual ao Valor da Parcela ...
                        if($valor_total_antecipacoes >= $campos_financiamento[$i]['valor_parcela_nf']) {
//Sendo assim eu 0 essa parcela os valores da Nota Fiscal p/ Zero, afinal a antecipa��o cubriu a despesa ...
                            $sql = "UPDATE `nfe_financiamentos` SET `valor_parcela_nf` = '0' WHERE `id_nfe_financiamento` = '".$campos_financiamento[$i]['id_nfe_financiamento']."' LIMIT 1 ";
                            bancos::sql($sql);
//Desconto do Valor Total da Antecipa��o o valor da Parcela Corrente da NF ...
                            $valor_total_antecipacoes-= $campos_financiamento[$i]['valor_parcela_nf'];
                        }else {
//Desconto dessa Parcela somente o Valor da Antecipa��o e Zero a Antecipa��o, afinal j� abateu toda antec ...
                            $sql = "UPDATE `nfe_financiamentos` SET `valor_parcela_nf` = `valor_parcela_nf` - $valor_total_antecipacoes WHERE `id_nfe_financiamento` = '".$campos_financiamento[$i]['id_nfe_financiamento']."' LIMIT 1 ";
                            bancos::sql($sql);
//Zero a Antecipa��o ...
                            $valor_total_antecipacoes = 0;
                        }
                    }else {//J� n�o h� mais nada a ser descontado da NF, pois a Antecipa��o = 0 ...
                        $i = $linhas_financiamento;//P/ sair fora do loop ...
                    }
                }
/*Se o Valor da Antecipa��o ainda for maior do que o Valor da Nota Fiscal mesmo depois de todos os 
abatimentos, ent�o eu pego esse valor e debato da Primeira Parcela ...*/
                if($valor_total_antecipacoes > 0) {
                    $sql = "UPDATE `nfe_financiamentos` SET valor_parcela_nf = valor_parcela_nf - $valor_total_antecipacoes WHERE `id_nfe_financiamento` = '".$campos_financiamento[0]['id_nfe_financiamento']."' LIMIT 1 ";
                    bancos::sql($sql);
                }
            }
        }
    }

    function calcular_densidade($id_produto_insumo = 0, $id_item_conversoes_temps = 0) {
        if($id_item_conversoes_temps > 0) {//Significa que a fun��o foi chamada de dentro das Convers�es de A�o ...
                $sql = "SELECT ict.id_geometria_aco, ict.medida1 AS medida1_mm, ict.medida2 AS medida2_mm, qa.valor_perc 
                                FROM `itens_conversoes_temps` ict 
                                INNER JOIN `produtos_insumos_vs_acos` pia ON pia.id_produto_insumo = ict.id_produto_insumo 
                                INNER JOIN `qualidades_acos` qa ON qa.id_qualidade_aco = pia.id_qualidade_aco 
                                WHERE ict.`id_item_conversoes_temps` = '$id_item_conversoes_temps' LIMIT 1 ";
        }else {//Significa que a Tela foi chamada de Dentro de algum cadastro de PI ...
                $sql = "SELECT pia.id_geometria_aco, pia.bitola1_aco AS medida1_mm, pia.bitola2_aco AS medida2_mm, qa.valor_perc 
                                FROM `produtos_insumos_vs_acos` pia 
                                INNER JOIN `qualidades_acos` qa ON qa.id_qualidade_aco = pia.id_qualidade_aco 
                                WHERE pia.`id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
        }
        $campos_geral 		= bancos::sql($sql);
        $fator_densidade 	= 1 + ($campos_geral[0]['valor_perc'] /100);
        /*********************C�lculos feitos de acordo com o Formato do a�o*********************/
        if($campos_geral[0]['id_geometria_aco'] == 1) {//Quadrado
                $densidade = pow($campos_geral[0]['medida1_mm'] / 1000, 2) * 7850 * $fator_densidade;
        }else if($campos_geral[0]['id_geometria_aco'] == 2) {//Redondo
                $densidade = pi() / 4 * (pow($campos_geral[0]['medida1_mm'] / 1000, 2) * 7850) * $fator_densidade;
        }else if($campos_geral[0]['id_geometria_aco'] == 3) {//Chato
                $densidade = (($campos_geral[0]['medida1_mm'] * $campos_geral[0]['medida2_mm']) / 1000) * 7.85 * $fator_densidade;
        }else if($campos_geral[0]['id_geometria_aco'] == 4) {//Tubo
                $densidade = (((pow($campos_geral[0]['medida1_mm'] / 2, 2) * PI()) - (pow($campos_geral[0]['medida2_mm'] / 2, 2) * PI())) / 1000) * 7.85 * $fator_densidade;
        }else if($campos_geral[0]['id_geometria_aco'] == 5) {//Sextavado
                $densidade = pow($campos_geral[0]['medida1_mm'] / 2, 2)  * 0.68 / 100;
        }
        /****************************************************************************************/
        return round($densidade, 3);
    }
    
    //Essa fun��o retorna um array com N�veis que ser� visualizado em N�vel de Estoque eu acho ??? ...
    function niveis() {
        return array(
            '1' => 'Baix�ssimo', 
            '2' => 'Baixo', 
            '3' => 'M�dio', 
            '4' => 'Alto', 
            '5' => 'Alt�ssimo'
        );
    }
}
?>