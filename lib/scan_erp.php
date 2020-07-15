<?
class scan_erp extends bancos {
    var $id_produtos_insumos;
    function __construct() {
        $data = date('Y-m-d');
        $hora = date('H:i:s');

        if(!class_exists('comunicacao'))    require 'comunicacao.php';//CASO EXISTA EU DESVIO A CLASSE ...
        if(!class_exists('financeiros'))    require 'financeiros.php';//CASO EXISTA EU DESVIO A CLASSE ...
        if(!class_exists('genericas'))      require 'genericas.php';//CASO EXISTA EU DESVIO A CLASSE ...
        if(!class_exists('vendas'))         require 'vendas.php';//CASO EXISTA EU DESVIO A CLASSE ...
        if(!class_exists('variaveis/intermodular.php')) require 'variaveis/intermodular.php';
		
        ////////////////////////////PI atrelado ao custo////////////////////////////
        //Busca de PI's da 1ª Etapa ...
        $sql = "SELECT DISTINCT(`id_produto_insumo`) 
                FROM `pas_vs_pis_embs` ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($j = 0; $j < $linhas; $j++) $id_produtos_insumos.= $campos[$j]['id_produto_insumo'].',';
        //Busca de PI's da 2ª Etapa ...
        $sql = "SELECT DISTINCT(`id_produto_insumo`) 
                FROM `produtos_acabados_custos` 
                WHERE `id_produto_insumo` IS NOT NULL ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($j = 0; $j < $linhas; $j++) $id_produtos_insumos.= $campos[$j]['id_produto_insumo'].',';
        //Busca de PI's da 3ª Etapa ...
        $sql = "SELECT DISTINCT(`id_produto_insumo`) 
                FROM `pacs_vs_pis` ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($j = 0; $j < $linhas; $j++) $id_produtos_insumos.= $campos[$j]['id_produto_insumo'].',';
        //Busca de PI's da 5ª Etapa ...
        $sql = "SELECT DISTINCT(`id_produto_insumo`) 
                FROM `pacs_vs_pis_trat` ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($j = 0; $j < $linhas; $j++) $id_produtos_insumos.= $campos[$j]['id_produto_insumo'].',';
        //Busca de PI's da 6ª Etapa ...
        $sql = "SELECT DISTINCT(`id_produto_insumo`) 
                FROM `pacs_vs_pis_usis` ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($j = 0; $j < $linhas; $j++) $id_produtos_insumos.= $campos[$j]['id_produto_insumo'].',';
        $id_produtos_insumos        = substr($id_produtos_insumos, 0, (strlen($id_produtos_insumos) - 1));
        $id_produtos_insumos        = implode(',',array_unique(explode(',', $id_produtos_insumos)));
        $this->id_produtos_insumos  = " pi.`id_produto_insumo` IN (".$id_produtos_insumos.") ";
        ////////////////////////////////////////////////////////////////////////////
        $sql = "SELECT `id_scan_erp`, `funcao`, `intervalo_dias` 
                FROM `scans_erps` 
                WHERE `data` <= '$data' 
                AND `hora` <= '$hora' ";
        $campos_scan    = bancos::sql($sql);//Verifico todas as máquinas existentes no Sistema
        $linhas         = count($campos_scan);
        for($i = 0; $i < $linhas; $i++) {//quantos agendamento tiver eu executo olhando o modulo e seu intervalo de tempo
            $intervalor = "INTERVAL ".intval($campos_scan[$i]['intervalo_dias'])." DAY";
            $sql = "UPDATE `scans_erps` SET `data` = DATE_ADD('$data', $intervalor) WHERE `id_scan_erp` = ".$campos_scan[$i]['id_scan_erp'];
            bancos::sql($sql);//Altero a data para ele executar amanhã novamente nos mesmo horarios do agendamento
            switch($campos_scan[$i]['funcao']) {
                case 'custo_pi_x_dias_desatualizado':
                    $mensagem = self::custo_pi_x_dias_desatualizado();//quando p PI já tem mais de X dias de atualização para o forncecedor
                    if(!empty($mensagem)) {
                        $destino    = $scan_custo_pi_x_dias_desat;
                        $assunto    = 'SCAN Custo com P.I. Desatualizado - '.intval(genericas::variavel(43)).' dias - '.date('d-m-Y H:i:s');
                        comunicacao::email('erp@grupoalbafer.com.br', $destino, '', $assunto, $mensagem);
                    }
                break;
                case 'fornec_default_sem_preco_pi':
                    $mensagem = self::fornec_default_sem_preco_pi();
                    if(!empty($mensagem)) {
//Por enquanto não passar e-mails no nome do Roberto - Dárcio
                        $destino        = $scan_fornec_default_sem_preco_pi;
                        $copia          = $scan_fornec_default_sem_preco_pi_copia;
                        $copia_oculta   = 'darcio@grupoalbafer.com.br';
                        $assunto        = 'SCAN FORNECEDOR DEFAULT COM PROD. SEM PREÇO - '.date('d-m-Y H:i:s');
                        comunicacao::email('erp@grupoalbafer.com.br', $destino, $copia, $assunto, $mensagem, $copia_oculta);
                    }
                break;
                case 'scan_maquinas':
                    self::scan_maquinas();
                break;
                case 'reg_clear':
                    self::reg_clear();
                break;
                case 'scan_nfs_duplicidade':
                    self::scan_nfs_duplicidade();//verifico se possui numero de nota fiscal duplicado
                break;
                case 'abatimento_comissao_por_atraso_pagamento':
                    self::abatimento_comissao_por_atraso_pagamento();
                break;
                case 'scan_health_erp':
                    self::scan_health_erp();
                break;
                case 'zerar_comissoes_extras_orcamento':
                    self::zerar_comissoes_extras_orcamento();
                break;
                case 'atualizar_contas_receber_atraso':
                    self::atualizar_contas_receber_atraso();
                break;
                case 'clientes_em_atraso_sem_credito_c':
                    self::clientes_em_atraso_sem_credito_c();
                break;
                case 'cadastrar_contas_automaticas':
                    financeiros::cadastrar_contas_automaticas();
                break;
                case 'bloquear_pedidos_vendas_antigos_fora_custo':
                    //self::bloquear_pedidos_vendas_antigos_fora_custo();
                break;
                case 'email_aniversariantes':
                    self::email_aniversariantes();
                break;
                case 'email_automatico_orcs_para_clientes':
                    self::email_automatico_orcs_para_clientes();
                break;
                case 'email_automatico_vales_para_clientes':
                    vendas::email_automatico_vales_para_clientes();
                break;
                default:
                break;
            }
        }
    }
	
    /*Essa função tem por objetivo estornar a comissão do Representante que foi paga, caso o Cliente efetuou 
    uma compra a mais de 120 dias e ainda não pagou de forma total a Duplicata ...*/
    function abatimento_comissao_por_atraso_pagamento() {
        $data_atual = date('Y-m-d');
        /*Aqui eu verifico se tem alguma Duplicata que está com atraso maior do que 60 dias e que já foi 
        importada, indiferente da Empresa e que não tenham algum Lançamento como sendo "Atraso de Pagamento" ...*/
        $sql = "SELECT cr.id_conta_receber, cr.id_nf, DATE_FORMAT(cr.data_emissao, '%d/%m/%Y') as data_emissao, 
                DATE_FORMAT(cr.data_vencimento, '%d/%m/%Y') AS data_vencimento, cr.id_empresa, 
                cr.num_conta, (cr.valor - cr.valor_pago) AS valor_restante, cr.id_representante, 
                IF(c.razaosocial = '', c.nomefantasia, c.razaosocial) AS cliente, nfs.comissao_media, r.nome_fantasia 
                FROM `contas_receberes` cr 
                INNER JOIN `nfs` ON nfs.id_nf = cr.id_nf 
                INNER JOIN `representantes` r ON r.id_representante = cr.id_representante 
                INNER JOIN `tipos_recebimentos` tp ON tp.id_tipo_recebimento = cr.id_tipo_recebimento 
                INNER JOIN `clientes` c ON c.id_cliente = cr.id_cliente AND c.id_pais = '31' 
                WHERE (DATEDIFF('$data_atual', cr.data_vencimento) > 60) 
                AND cr.status < '2' AND (cr.valor - cr.valor_pago) > '0' 
                AND cr.`id_conta_receber` NOT IN 
                /*A query abaixo verifica todas as NF(s) de Saída ou duplicatas que já foram importadas anteriormente 
                na tabela de abatimentos p/ não geramos abatimentos de comissão novamente.
                O controle de abatimento somente por duplicata passou a ser realizado no ERP a partir do dia 21/03/2013 ...*/
                (SELECT DISTINCT(cr.id_conta_receber) 
                FROM `contas_receberes` cr 
                INNER JOIN `comissoes_estornos` ce ON ((ce.id_conta_receber = cr.id_conta_receber) 
                OR (ce.id_nf = cr.id_nf AND ce.id_conta_receber IS NULL)) 
                AND ce.`tipo_lancamento` = '1') ORDER BY cr.data_vencimento ";
        $campos_atraso = bancos::sql($sql);
        $linhas_atraso = count($campos_atraso);
        //Disparo de Loop das Contas à Receber, NFS ...
        for($i = 0; $i < $linhas_atraso; $i++) {
            /*Verifico se esse Registro que será gerado, já foi gerado anteriormente p/ não gerar o 
            mesmo em Dobro ...*/
            $sql = "SELECT id_comissao_estorno 
                    FROM `comissoes_estornos` 
                    WHERE `id_nf` = '".$campos_atraso[$i]['id_nf']."' 
                    AND `id_conta_receber` = '".$campos_atraso[$i]['id_conta_receber']."' 
                    AND `id_representante` = '".$campos_atraso[$i]['id_representante']."' 
                    AND `tipo_lancamento` = '1' 
                    AND `porc_devolucao` = '".$campos_atraso[$i]['comissao_media']."' 
                    AND `valor_duplicata` = '".$campos_atraso[$i]['valor_restante']."' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 0) {//Registro ainda não gerado, sendo assim posso gerar um Novo ...
                //Esse registro que eu gero na tabela de Abatimento é utilizado no relatorio de Estorno de Comissoes ...
                $sql = "INSERT INTO `comissoes_estornos` (`id_comissao_estorno`, `id_nf`, `id_conta_receber`, `id_representante`, `num_nf_devolvida`, `data_lancamento`, `tipo_lancamento`, `porc_devolucao`, `valor_duplicata`) 
                        VALUES (NULL, '".$campos_atraso[$i]['id_nf']."', '".$campos_atraso[$i]['id_conta_receber']."', '".$campos_atraso[$i]['id_representante']."', '', '$data_atual', '1', '".$campos_atraso[$i]['comissao_media']."', '".$campos_atraso[$i]['valor_restante']."') ";
                bancos::sql($sql);
                //Essa variavel sera passada por e-mail, coloquei esse nome na variável porque na sessão já existe uma variável com o nome de id_empresa ...
                $empresa = genericas::nome_empresa($campos_atraso[$i]['id_empresa']);
                //Dados p/ enviar por e-mail ...
                $complemento_justificativa.= ' <br><b>Cliente: </b>'.$campos_atraso[$i]['cliente'].' <br><b>N.º da Conta: </b>'.$campos_atraso[$i]['num_conta'].'<br><b>Empresa: </b>'.$empresa.' <br><b>Data de Emissão: </b>'.$campos_atraso[$i]['data_emissao'].' <br><b>Data de Vencimento: </b>'.$campos_atraso[$i]['data_vencimento'].' <br><b>Valor a Descontar do Representante: </b>'.number_format($campos_atraso[$i]['valor_restante'], 2, ',', '.').' <br><b>Representante: </b>'.$campos_atraso[$i]['nome_fantasia'].' <br><b>% de Desconto: </b>'.number_format($campos_atraso[$i]['comissao_media'], 2, ',', '.').'<br/>';
            }
        }
/************************E-mail************************/
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
        $observacao = "O sistema gerou para a(s) conta(s) acima um Abatimento de Comissão do Tipo \"Atraso de Pagamento\".";
//Os e-mails estão especificados dentro da biblioteca intermodular na pasta variáveis ...
        $destino = $atraso_pagamento_automatico;
        $mensagem = $complemento_justificativa.'<br><font color="blue"><b>Data e Hora de Inclusão: </b></font> '.date('d/m/Y H:i:s').'<br><font color="blue"><b>Observação: </b></font>'.$observacao.'<br>'.$GLOBALS['PHP_SELF'];
        comunicacao::email('erp@grupoalbafer.com.br', $destino, '', 'Atraso de Pagamento gerado sobre as Contas à Receber com o Vencimento acima de 120 dias - Financeiro', $mensagem);
    }

    function scan_nfs_duplicidade() {//verifico se possui numero de nota fiscal duplicado
        $sql = "SELECT nfs.id_nf 
                FROM `nfs` 
                INNER JOIN `nfs_num_notas` nnn ON nnn.id_nf_num_nota = nfs.id_nf_num_nota 
                WHERE nfs.`ativo` = '1' 
                GROUP BY nfs.`id_nf_num_nota` 
                HAVING COUNT(nfs.`id_nf_num_nota`) > '1' ";//Se for maior q um é pq tem duplicidade ...
        $campos = bancos::sql($sql);
        if(count($campos) > 3) {// existem 3 que ja tinha ocorrido o problema então deixamos, pq ja estava no cliente
            $mensagem = 'Urgente===>Verificar duplicidade de número de Nota Fiscal => <br>sql abaixo:<br>';
            $mensagem.= $sql;
            ///////////////////////////// Envia email //////////////////////////////
            $destino = 'darcio@grupoalbafer.com.br';
            $assunto = 'ERRO NO SISTEMA - '.date('d-m-Y H:i:s');
            comunicacao::email('erp@grupoalbafer.com.br', $destino, '', $assunto, $mensagem);
        }
    }
	
    //Função que deleta Orçamentos e Itens de Orçamento ...
    function reg_clear() {
        $data_atual = date('Y-m-d');

        //1) Deleta os Orçamentos sem Itens com mais de 365 dias existentes ou zerados ...
        $sql = "SELECT id_orcamento_venda 
                FROM `orcamentos_vendas` 
                WHERE `id_orcamento_venda` NOT IN 
                (SELECT `id_orcamento_venda` 
                FROM `orcamentos_vendas_itens`) 
                AND (SUBSTRING(`data_sys`, 1, 10) < DATE_ADD('$data_atual', INTERVAL -365 DAY) OR SUBSTRING(`data_sys`, 1, 10) = '0000-00-00') ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
            //Deleto todos os "Prazo de Revenda ESP" do id_orcamento_venda que está no Loop ...
            $sql = "DELETE FROM `prazos_revendas_esps` WHERE `id_orcamento_venda` = '".$campos[$i]['id_orcamento_venda']."' ";
            bancos::sql($sql);
            //Deleto o "Orçamento" do id_orcamento_venda que está no Loop ...
            $sql = "DELETE FROM `orcamentos_vendas` WHERE `id_orcamento_venda` = '".$campos[$i]['id_orcamento_venda']."' LIMIT 1 ";
            bancos::sql($sql);
        }
        
        //2) Deleta os Orçamentos com Itens com mais de 1095 dias "3 anos" com status de que nunca foi gerado pedido ...
        $sql = "SELECT `id_orcamento_venda` 
                FROM `orcamentos_vendas` 
                WHERE `id_orcamento_venda` IN 
                (SELECT `id_orcamento_venda` 
                FROM `orcamentos_vendas_itens`) 
                AND `status` = '0' 
                AND (SUBSTRING(`data_sys`, 1, 10) < DATE_ADD('$data_atual', INTERVAL -365 DAY) OR SUBSTRING(`data_sys`, 1, 10) = '0000-00-00') ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
            //Deleto os "Itens de Orçamento" do id_orcamento_venda que está no Loop ...
            $sql = "DELETE FROM `orcamentos_vendas_itens` WHERE `id_orcamento_venda` = '".$campos[$i]['id_orcamento_venda']."' ";
            bancos::sql($sql);
            //Deleto todos os "Prazo de Revenda ESP" do id_orcamento_venda que está no Loop ...
            $sql = "DELETE FROM `prazos_revendas_esps` WHERE `id_orcamento_venda` = '".$campos[$i]['id_orcamento_venda']."' ";
            bancos::sql($sql);
            //Deleto o "Orçamento" do id_orcamento_venda que está no Loop ...
            $sql = "DELETE FROM `orcamentos_vendas` WHERE `id_orcamento_venda` = '".$campos[$i]['id_orcamento_venda']."' LIMIT 1 ";
            bancos::sql($sql);
        }
    }

    function custo_pi_x_dias_desatualizado() {
        if(!class_exists('genericas')) require('genericas.php');//CASO EXISTA EU DESVIO A CLASSE ...
        $prazo_dias_validade_custo	= genericas::variavel(43);
        $data_atual 			= date('Y-m-d');
        /********************Irá exibir somente os PI`s que estão atrelados ao Custo********************/
        $sql = "SELECT DISTINCT(`id_produto_insumo`) 
                FROM `pas_vs_pis_embs` ";
        $campos = bancos::sql($sql);
        $linhas	= count($campos);
        for($i = 0; $i < $linhas; $i++) $id_produtos_insumos.= ($campos[$i]['id_produto_insumo'].', ');
        
        $sql = "SELECT DISTINCT(`id_produto_insumo`) 
                FROM `produtos_acabados_custos` 
                WHERE id_produto_insumo IS NOT NULL ";
        $campos = bancos::sql($sql);
        $linhas	= count($campos);
        for($i = 0; $i < $linhas; $i++) $id_produtos_insumos.= ($campos[$i]['id_produto_insumo'].', ');
        
        $sql = "SELECT DISTINCT(`id_produto_insumo`) 
                FROM `pacs_vs_pis` ";
        $campos = bancos::sql($sql);
        $linhas	= count($campos);
        for($i = 0; $i < $linhas; $i++) $id_produtos_insumos.= ($campos[$i]['id_produto_insumo'].', ');
        
        $sql = "SELECT DISTINCT(`id_produto_insumo`) 
                FROM `pacs_vs_pis_trat` ";
        $campos = bancos::sql($sql);
        $linhas	= count($campos);
        for($i = 0; $i < $linhas; $i++) $id_produtos_insumos.= ($campos[$i]['id_produto_insumo'].', ');
        
        $sql = "SELECT DISTINCT(`id_produto_insumo`) 
                FROM `pacs_vs_pis_usis` ";
        $campos = bancos::sql($sql);
        $linhas	= count($campos);
        for($i = 0; $i < $linhas; $i++) $id_produtos_insumos.= ($campos[$i]['id_produto_insumo'].', ');

        $id_produtos_insumos = substr($id_produtos_insumos, 0, (strlen($id_produtos_insumos) - 2));
        $id_produtos_insumos = implode(',', array_unique(explode(',', $id_produtos_insumos)));
        $id_produtos_insumos = " AND pi.`id_produto_insumo` IN (".$id_produtos_insumos.") ";        
        /***********************************************************************************************/
        /*SQL, faço a listagem de Todos os PI(s) com exceção do Grupo PRAC, em que a Lista de Preço tenha 
        PI(s) com data de atualização a mais de X dias - Ex: + de 90 dias, 45 dias, etc - apenas no 
        Fornecedor Default ... - exibindo apenas os PI`s atrelados ao Custo ...*/
        $sql = "SELECT fpi.`id_fornecedor_prod_insumo`, g.`referencia`, pi.`discriminacao` 
                FROM `produtos_insumos` pi 
                INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_fornecedor` = pi.`id_fornecedor_default` AND fpi.`id_produto_insumo` = pi.`id_produto_insumo` AND fpi.`ativo` = '1' 
                INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` AND g.`id_grupo` <> '9' 
                WHERE SUBSTRING(fpi.`data_sys`, 1, 10) <= DATE_ADD('$data_atual', INTERVAL -$prazo_dias_validade_custo DAY) 
                AND pi.`id_fornecedor_default` > '0' 
                AND pi.ativo = '1' 
                $id_produtos_insumos ORDER BY pi.`discriminacao` ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
            /*Bloqueio este PI "Matéria Prima" porque já se passaram mais de 90 dias em que esse item de Lista 
            ficou sem atualização de Preço, e esse novo Controle pelo campo `custo_pi_bloqueado` -> impedirá 
            de o Custo ser Liberado caso ocorra isso nas "Etapas 1, 2, 3, 5 e 6" ...*/
            $sql = "UPDATE `fornecedores_x_prod_insumos` SET `custo_pi_bloqueado` = 'S' WHERE `id_fornecedor_prod_insumo` = '".$campos[$i]['id_fornecedor_prod_insumo']."' LIMIT 1 ";
            bancos::sql($sql);
            
            $produtos.= '<br/>'.$campos[$i]['referencia'].' - '.$campos[$i]['discriminacao'];
        }
        return substr($produtos, 0, strlen($produtos) - 2);
    }
	
    function fornec_default_sem_preco_pi() {
        $id_produtos_insumos    = $this->id_produtos_insumos;//PI atrelado ao custo
        $data_atual             = date('d/m/Y');
        
        //Primeira Regra - estou ignorando o Fornecedor NSK 198 pq é do Brasil e tem preço em Dólar ...
        $sql = "SELECT f.`id_pais`, f.`razaosocial`, pi.`discriminacao` 
                FROM `fornecedores_x_prod_insumos` fpi 
                INNER JOIN `fornecedores` f ON f.`id_fornecedor` = fpi.`id_fornecedor` AND f.`id_fornecedor` <> '198' 
                INNER JOIN `produtos_insumos` pi ON pi.`id_fornecedor_default` = fpi.`id_fornecedor` AND pi.`id_produto_insumo` = fpi.`id_produto_insumo` AND pi.`ativo` = '1' AND pi.`id_fornecedor_default` > '0' 
                INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` AND g.`id_grupo` <> '9' 
                WHERE $id_produtos_insumos 
                AND fpi.`ativo` = '1' 
                AND (fpi.`preco_faturado` = '0.00' AND fpi.`preco_faturado_export` = '0.00') 
                ORDER BY f.`razaosocial` ";
        $campos = bancos::sql($sql);//busco todos os fornecedores default que tem produto sem preço cadastrado
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
            if($campos[$i]['id_pais'] == 31) {//Fornecedor Nacional ...
                $mensagem.= "<br/><font color='red'>FORNEC NACIONAL => </font>".$campos[$i]['razaosocial'].". <font color='red'>PRODUTO INSUMO => </font>".$campos[$i]['discriminacao'];
            }else {//Fornecedor Internacional ...
                $mensagem.= "<br/><font color='red'>FORNEC INTERNACIONAL => </font>".$campos[$i]['razaosocial'].". <font color='red'>PRODUTO INSUMO => </font>".$campos[$i]['discriminacao'];
            }
        }
        return $mensagem;
    }

    function scan_maquinas() {
        $sql = "SELECT id_maquina, nome 
                FROM `maquinas` 
                WHERE `ativo` = '1' ";
        $campos = bancos::sql($sql);//Verifico todas as máquinas existentes no Sistema
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) { //Verifico se a máquina tem pelo menos 1 funcionário atrelado
            $sql = "SELECT id_maquina_vs_funcionario 
                    FROM `maquinas_vs_funcionarios` 
                    WHERE `id_maquina` = '".$campos[$i]['id_maquina']."' LIMIT 1 ";
            $campos_maquina = bancos::sql($sql);
            if(count($campos_maquina) == 0) $maquinas.= $campos[$i]['nome'].', ';
        }
        if(!empty($maquinas)) {//para mandar email só se tiver problemas
            $destino            = $scan_maquinas;
            $copia		= $scan_maquinas_copia;
            $assunto		= 'SCAN MAQUINAS - '.date('d-m-Y H:i:s');
            $mensagem           = 'Existem máquinas ao qual não possuem funcionarios Atrelados. \n';
            $mensagem.=         substr($maquinas, 0, strlen($maquinas) - 2);
            comunicacao::email('erp@grupoalbafer.com.br', $destino, $copia, $assunto, $mensagem);
        }
    }

    function scan_health_erp() {
        $dia                = date('d');
        $dias_array         = array(1=>"a",2=>"b",3=>"c",4=>"d",5=>"e",6=>"f",7=>"g",8=>"h",9=>"i",10=>"j",11=>"k",12=>"l",13=>"m",14=>"n",15=>"o",16=>"p",17=>"q",18=>"r",19=>"s",20=>"t",21=>"u",22=>"v",23=>"x",24=>"z",25=>"w",26=>"y",27=>"",28=>"",29=>"",30=>"",31=>"");
        $campos_registros   = bancos::getDb()->query('SHOW TABLES');
        while ($row = $campos_registros->fetch(PDO::FETCH_NUM)) {
        if(substr($row[0], 0, 1) == $dias_array[(int)$dia]) $tabelas_loop.= $row[0].',';
        }
        $table_all = substr($tabelas_loop, 0, strlen($tabelas_loop) - 1);//tabela retirada
        $table_all = str_replace(',ceps', '', $table_all);//tabela retirada
        $table_all = str_replace(',logs_apvs ', '', $table_all);//tabela retirada
        if(empty($table_all)) return true;

        $sql="OPTIMIZE TABLE $table_all";
        bancos::getDb()->query($sql);// se retornar OK é pq otmizou se não ele ignora pq ja está otimizado
        $sql="ANALYZE TABLE $table_all";
        bancos::getDb()->query($sql);// se retornar OK é pq a table foi analisada se não ele ignora pq ja está analisada
        $sql="CHECK TABLE $table_all";
        bancos::getDb()->query($sql);// No manual do my sql manda 1º executar ele simples depois no modo completo
        $sql="CHECK TABLE $table_all EXTENDED";//modo completo de varredura na tabela
        $campos = bancos::getDb()->query($sql);
        while ($row = $campos->fetch(PDO::FETCH_NUM)) {
            $table = $row[0];
            $mensagem.="<br><br>CHK Tabela => ".$table;
            if($table != 'check' || $table != 'OK') {// ENTÃO EXCUTAR A REPARAÇÃO
                //Reparando a Tabela, só pode reparar a tabela se realmente achar erros
                $sql = "REPAIR TABLE $table EXTENDED";
                $campos_repair = bancos::getDb()->query($sql);
                while ($row_repair = $campos_repair->fetch(PDO::FETCH_NUM)) {
                    $sql = "REPAIR TABLE $table USE_FRM";//recria o arquivo myi com base no frm
                    bancos::getDb()->query($sql);
                }
            }
        }
        $assunto    = 'SCAN HEALTH ERP - '.date('d-m-Y H:i:s');
        comunicacao::email('erp@grupoalbafer.com.br', 'darcio@grupoalbafer.com.br', '', $assunto, $mensagem);
    }
	
    function zerar_comissoes_extras_orcamento() {
        /*Função que ficou inutilizada a partir do dia 03/02/2015 devido não existir + Comissão Extra, 
        pode ser que a mesma volte a ser utilizada no futuro ??? - Dárcio ...*/
        return 0;
        
        $data_atual = date('Y-m-d');
/*Aqui eu verifico todos os Itens de Orçamento que estão em 'Aberto' ou 'Parcialmente em Abertos' com Comissão Extra 
em que a 'Data Atual' seja > do que a Data de Validade do Orçamento ("que já é a Emissão + 10 + 3") 
o + 3 se refere a tolerância máxima que está sendo dada em cima do que o usuário está liberando ...*/
        $sql = "SELECT ovi.id_orcamento_venda_item 
                FROM `orcamentos_vendas_itens` ovi 
                INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda 
                WHERE ovi.`status` < '2' 
                AND ovi.`comissao_extra` > '0' 
                AND ('$data_atual' > DATE_ADD(ov.data_emissao, INTERVAL 3 DAY)) ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
//Aqui eu zero a Comissão Extra no caso de já ter expirado a Data de Validade do Orçamento com relação a Data Atual
            $sql = "UPDATE `orcamentos_vendas_itens` SET `comissao_extra` = '0' WHERE `id_orcamento_venda_item` = '".$campos[$i]['id_orcamento_venda_item']."' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
	
    function atualizar_contas_receber_atraso() {//Função cujo objetivo é atualizar o valor de juros das contas que estão em atraso
        $data_atual = date('Y-m-d');
        /*Seleciono as Contas à Receber que estão em Aberto < 2, que estão Vencidas e que o Juros é calculado de 
        maneira automática pelo campo "Taxa de Juros > 0", consequentemente o checkbox "Valor Juros Manual" 
        que equivale ao campo "manual" do Banco de Dados está desmarcado e o campo "Valor Juros R$" desabilitado ...*/
        $sql = "SELECT id_conta_receber, valor, valor_desconto, 
                valor_abatimento, valor_despesas, taxa_juros, data_vencimento 
                FROM `contas_receberes` 
                WHERE `data_vencimento` < '$data_atual' 
                AND `manual` = '0' 
                AND `status` < '2' ORDER BY data_vencimento ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
            //Aqui essas variáveis são para o cálculo da fórmula do Roberto ...
            $id_conta_receber   = $campos[$i]['id_conta_receber'];
            $valor              = $campos[$i]['valor'];
            $valor_desconto     = $campos[$i]['valor_desconto'];
            /********************************************************************************************/
            //Verifico se tenho alguma NF de Devolução importada p/ essa Duplicata em Questão ...
            $sql = "SELECT SUM(valor_devolucao) AS total_devolucao_importada 
                    FROM `contas_receberes_vs_nfs_devolucoes` 
                    WHERE `id_conta_receber` = '$id_conta_receber' ";
            $campos_devolucao_importada = bancos::sql($sql);
            /********************************************************************************************/
            $valor_abatimento   = $campos[$i]['valor_abatimento'] + $campos_devolucao_importada[0]['total_devolucao_importada'];
            $valor_despesas     = $campos[$i]['valor_despesas'];
            $taxa_juros         = $campos[$i]['taxa_juros'];
            $data_vencimento    = $campos[$i]['data_vencimento'];
            if($taxa_juros > 0) {
                //A variável dias equivale a data atual até a data de vecimento
                $dias = data::diferenca_data($data_vencimento, $data_atual);
                if($dias[0] < 0) $dias[0] = 0;
                $taxa_juros_dias_venc = ($taxa_juros / 30/ 100) * $dias[0];
            }else {
                $taxa_juros_dias_venc = 0;
            }
            $valor_juros = ($valor - $valor_desconto - $valor_abatimento + $valor_despesas) * $taxa_juros_dias_venc;
            //Atualizo a Conta à Receber do Loop com o novo juros calculado ...
            $sql = "UPDATE `contas_receberes` SET `valor_juros` = '$valor_juros' WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
    
    //Função cujo objetivo é mudar p/ Crédito "C" clientes que estão com Duplicatas em atraso a mais de uma semana ...
    function clientes_em_atraso_sem_credito_c() {
        $data_atual = date('Y-m-d');
        /*Essa Query traz todos os Clientes que possuem Crédito B ou D e que estão com Duplicatas em Aberto 
        a mais de uma semana, mesmo que o Cliente tenha pago algo dessa Duplicata "status = '1'" ...*/
        $sql = "SELECT cr.`id_conta_receber`, cr.`id_cliente` 
                FROM `contas_receberes` cr 
                INNER JOIN `clientes` c ON c.`id_cliente` = cr.`id_cliente` AND c.`credito` <> 'C' 
                WHERE cr.`data_vencimento` <= DATE_ADD('$data_atual', INTERVAL -7 DAY) 
                AND cr.`status` < '2' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
            /*Aqui eu verifico se está Duplicata do Loop realmente possui Pendência "Dívida Conosco" ...

            Obs: Existem Duplicatas que estão no Valor Negativo o que representa que o Cliente possui Crédito 
            conosco e não pendência e essas não podem ser Contabilizadas p/ mudar o Crédito do Cliente p/ C ...*/
            $calculos_conta_receber = financeiros::calculos_conta_receber($campos[$i]['id_conta_receber']);
            
            /*Se esse "Valor Reajustado" for maior do que Zero, então isso representa "Dívida" e sendo assim tenho 
            que contabilizar o "id_cliente" dessa Duplicata p/ poder mudar o Crédito do Cliente p/ "C" ...*/
            if($calculos_conta_receber['valor_reajustado'] > 0) $id_clientes[] = $campos[$i]['id_cliente'];
        }
        //Retiro os Clientes que estão em Duplicidade ...
        $id_clientes = array_unique($id_clientes);

        //Atualizando todos os Clientes encontrados acima p/ Crédito C ...
        $sql = "UPDATE `clientes` SET `credito` = 'C', `credito_data` = '".date('Y-m-d H:i:s')."', `credito_observacao` = CONCAT(`credito_observacao`, ' * Crédito alterado para C automaticamente pelo ERP em ".date('d/m/Y')." às ".date('H:i:s')." por atraso de pagamento superior à 7 dias.') WHERE `id_cliente` = '".implode(',', $id_clientes)."' ";
        bancos::sql($sql);
    }
    
    /*Aqui eu busco todos os Pedidos que estejam Liberados e que possuem pelo menos 1 item em "Aberto / Parcial" 
    "Ainda não foi faturado" p/ bloqueá-lo, muito provável que o Preço desse item na na época 
    esteja muito defazado em relação ao Preço de Hoje devido inflações, então isso é feito p/ que seja feita 
    uma reanálise ...*/
    /*function bloquear_pedidos_vendas_antigos_fora_custo() {
        if(!class_exists('genericas')) require('genericas.php');//CASO EXISTA EU DESVIO A CLASSE ...
        $prazo_validade_pedido  = intval(genericas::variavel(68));
        $data_atual             = date('Y-m-d');
        
        $sql = "UPDATE `pedidos_vendas` 
                SET `liberado` = '0' 
                WHERE `data_emissao` <= DATE_ADD('$data_atual', INTERVAL -$prazo_validade_pedido DAY) 
                AND `status` < '2' 
                AND `liberado` = '1' ";
        bancos::sql($sql);
    }*/
    
    /*Função que dispara um e-mail p/ o responsável do RH p/ que este esteja ciente dos funcionários 
    que fazem aniversário na data atual ...*/
    function email_aniversariantes() {
        if(!class_exists('variaveis/intermodular.php')) require 'variaveis/intermodular.php';
        $data_atual = date('m-d');

        //Trago nessa Query todos os funcionários que fazem aniversário na Data Atual ...
        $sql = "SELECT `nome` 
                FROM `funcionarios` 
                WHERE SUBSTRING(`data_nascimento`, 6, 5) = '$data_atual' 
                AND `status` < '2' ORDER BY `nome` ";
        $campos_funcionarios = bancos::sql($sql);
        $linhas_funcionarios = count($campos_funcionarios);
        
        if($linhas_funcionarios > 0) {//Se encontrou pelo menos 1 funcionário que faz aniversário, aí sim disparo o e-mail ...
            for($i = 0; $i < $linhas_funcionarios; $i++) $funcionarios.= '<b>'.$campos_funcionarios[$i]['nome'].'</b><br/>';
            /************************E-mail************************/
            //Os e-mails estão especificados dentro da biblioteca intermodular na pasta variáveis ...
            $destino = $scan_aniversariantes;
            $assunto = 'Aniversariantes do dia '.date('d/m/Y');
            $mensagem = 'Segue abaixo a relação do(s) funcionário(s) que faz(em) aniversário nesta data: <p/>'.$funcionarios;
            comunicacao::email('erp@grupoalbafer.com.br', $destino, '', $assunto, $mensagem);
        }
    }
    
    function email_automatico_orcs_para_clientes() {
        $data_atual_menos_9dias     = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -9), '-');
        
        /*Busco todos os Orçamentos que ainda não tiveram um só item que viraram Pedido, que 
        estejam dentro do prazo de Validade, que estejam Congelado e que a sua Negociação ainda não tenha 
        sido Finalizada - Somente do Brasil porque em casos de Exportação a Mercedes faz muita 
        simulações de Preços ...*/
        $sql = "SELECT ov.`id_orcamento_venda`, ov.`id_cliente_contato`, ov.`id_cliente`, ov.`data_emissao` 
                FROM `orcamentos_vendas` ov 
                INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` AND c.`id_pais` = '31' 
                WHERE ov.`data_emissao` >= '$data_atual_menos_9dias' 
                AND ov.`congelar` = 'S' 
                AND ov.`negociacao_finalizada` = 'N' 
                AND ov.`id_orcamento_venda` NOT IN 
                (SELECT DISTINCT(ov.`id_orcamento_venda`) AS id_orcamento_venda 
                FROM `orcamentos_vendas` ov 
                INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda` = ov.`id_orcamento_venda` AND ovi.`status` > '0' 
                WHERE ov.`data_emissao` >= '$data_atual_menos_9dias') 
                ORDER BY ov.`data_emissao` DESC ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
            //Verifico a Qtde de Dias transcorridos da "Data de Emissão do Orçamento" até a "Data de Hoje" ...
            $vetor_datas    = data::diferenca_data($campos[$i]['data_emissao'], date('Y-m-d'));
            $qtde_dias      = $vetor_datas[0];
            
            //Primeira Remessa, Segunda Remessa e Terceira Remessa p/ Envio de E-mails ...
            if($qtde_dias == 3 || $qtde_dias == 6 || $qtde_dias == 9) {
                //Registrando Follow-UP(s) ...
                $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_cliente_contato`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`)                       
                             VALUES (NULL, '".$campos[$i]['id_cliente']."', '".$campos[$i]['id_cliente_contato']."', '$_SESSION[id_funcionario]', '".$campos[$i]['id_orcamento_venda']."', '1', 'Reenvio automático via email do Orçamento em ".date('d/m/Y').' às '.date('H:i:s')."', '".date('Y-m-d H:i:s')."') ";
                bancos::sql($sql);
                
                if($qtde_dias == 3) {//1º Email de Mala Direta sendo Enviado ...
                    $sql = "UPDATE `orcamentos_vendas` SET `mala` = '1' WHERE `id_orcamento_venda` = '".$campos[$i]['id_orcamento_venda']."' LIMIT 1 ";
                }else if($qtde_dias == 6) {//2º Email de Mala Direta sendo Enviado ...
                    $sql = "UPDATE `orcamentos_vendas` SET `mala` = '2' WHERE `id_orcamento_venda` = '".$campos[$i]['id_orcamento_venda']."' LIMIT 1 ";
                }else if($qtde_dias == 9) {//3º Email de Mala Direta sendo Enviado ...
                    $sql = "UPDATE `orcamentos_vendas` SET `mala` = '3' WHERE `id_orcamento_venda` = '".$campos[$i]['id_orcamento_venda']."' LIMIT 1 ";
                }
                bancos::sql($sql);
                
                /*Antes como forma de garantia, deleto o arquivo ".pdf" se é que se encontra na pasta PDF, faço 
                isso porque vou gerar um novo arquivo desse logo mais abaixo e sem a Observação dessa vez; 
                porque: nossos Vendedores em alguns momentos tem o costume de escrever algumas besteiras nesse 
                campo e não é interessante que nossos Clientes vejam o que foi escrito, que fique somente 
                entre nós mesmo, como controle interno ...*/
                unlink('../pdf/Orcamento_Grupo_Albafer_'.$campos[$i]['id_orcamento_venda'].'.pdf');
?>
            <Script Language = 'JavaScript' Src = '../js/nova_janela.js'></Script>
            <Script Language = 'JavaScript'>
                //Gerando os arquivos PDF´s p/ cada Orçamento de Venda ...
                nova_janela('../modulo/vendas/orcamentos/itens/relatorio/relatorio.php?id_orcamento_venda=<?=$campos[$i]['id_orcamento_venda'];?>&mostrar_observacao=N', 'GERAR_ORCAMENTO', '', '', '', '', 1, 1, 'l', 'u')
            </Script>
<?
                $id_orcamentos_para_enviar_email.= $campos[$i]['id_orcamento_venda'].', ';
            }
        }
        $id_orcamentos_para_enviar_email = substr($id_orcamentos_para_enviar_email, 0, strlen($id_orcamentos_para_enviar_email) - 2);

        if(strlen($id_orcamentos_para_enviar_email) > 0) {//Significa que pelo menos um ORC foi encontrado na relação acima ...
?>
        <Script Language = 'JavaScript'>
            /*Esse comando setTimeout para abrir esse outro Pop-UP que só servirá para disparar e-mails, 
            foi um truque que eu fiz, para garantir que o Sistema conseguisse gerar o último ORC em PDF 
            na sua respectiva pasta para um futuro anexo sem dar erro ...*/
            setTimeout("nova_janela('/erp/albafer/lib/email_automatico_orcs_para_clientes.php?id_orcamentos_para_enviar_email=<?=$id_orcamentos_para_enviar_email;?>', 'GERAR_ORCAMENTO', '', '', '', '', 1, 1, 'l', 'u')", 5000)
        </Script>
<?
        }
    }
}
?>