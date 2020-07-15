<?
if(!class_exists('bancos')) require 'bancos.php';//CASO EXISTA EU DESVIO A CLASSE ...
class estoque_acabado {
    function baixas_pas_para_ops($id_produto_acabado_etapa7, $id_produto_acabado) {
/*Aqui eu seleciono todas as OP(s) que não foram finalizadas ainda e q não foram excluídas, 
de PA(s) que contém este PA no Custo ...*/ 
        $sql = "SELECT DISTINCT(bop.`id_op`), bop.`qtde_baixa`, bop.`status` 
                FROM `ops` 
                INNER JOIN `baixas_ops_vs_pas` bop ON bop.`id_op` = ops.`id_op` 
                WHERE bop.`id_produto_acabado` = '$id_produto_acabado_etapa7' AND ops.`id_produto_acabado` = '$id_produto_acabado' 
                AND ops.`status_finalizar` = '0' 
                AND ops.`ativo` = '1' ";
/*Se a última situação de PA = "baixa", então significa que eu posso estar contabilizando 
essa OP no processo de confecção, pois saiu PA(s) Componente(s) do Almoxarifado p/ a Produção de PA...*/
        $campos_op = bancos::sql($sql);
        $linhas_op = count($campos_op);
        for($i = 0; $i < $linhas_op; $i++) {
            if($linhas_op == 1) {//Aqui é para otimizar a função evitando mais Query ...
                /*Como esse valor "$campos_op[$i]['qtde_baixa']" sempre é gravado de maneira negativa no 
                Banco de Dados, tenho que transformá-lo em positivo para não furar a Fórmula mais abaixo ...*/
                if($campos_op[$i]['status'] == 2) $total_qtde_baixa+= abs($campos_op[$i]['qtde_baixa']);
            }else {//Se tiver mais de uma Baixa ...
                //Busca do último Status de Baixa referente a OP ...
                $sql = "SELECT `status` 
                        FROM `baixas_ops_vs_pas` 
                        WHERE `id_op` = '".$campos_op[$i]['id_op']."' ORDER BY `id_baixa_op_vs_pa` DESC LIMIT 1 ";
                $campos_status_baixa_op = bancos::sql($sql);
/*Se a última situação de PA = "baixa", então significa que eu posso estar contabilizando 
essa OP no processo de confecção, pois saiu PA(s) do Almoxarifado p/ a Produção de PA...*/
                if($campos_status_baixa_op[0]['status'] == 2) $total_qtde_baixa+= $campos_op[$i]['qtde_baixa'];
            }
            //Aqui eu verifico todas as Entradas da OP e do PA Filho q foi passado no 2º Parâmetro ...
            $sql = "SELECT SUM(bmp.`qtde`) AS qtde_total_entrada 
                    FROM `baixas_manipulacoes_pas` bmp 
                    INNER JOIN `baixas_ops_vs_pas` bop ON bop.`id_baixa_manipulacao_pa` = bmp.`id_baixa_manipulacao_pa` AND bop.`id_op` = '".$campos_op[$i]['id_op']."' 
                    WHERE bmp.`id_produto_acabado` = '$id_produto_acabado' 
                    AND bmp.`acao` = 'E' ";
            $campos_qtde_total_entrada = bancos::sql($sql);          
            $total_qtde_baixa-= $campos_qtde_total_entrada[0]['qtde_total_entrada'];
        }
        return $total_qtde_baixa;
    }

        //Essa função serve para marcar no PA a ser consultado no Gerenciar Estoque na opção 'Todos os PA(s) com Nova Entrada em Estoque'
	function seta_nova_entrada_pa_op_compras($id_produto_acabado) {//Serve para marcar o PA que está dando entrada e possui est_comprometido < 0 ...
            if(!class_exists('custos')) require('custos.php');// CASO EXISTA EU DESVIO A CLASSE
            $GLOBALS['id_pa_atrelados'][]=$id_produto_acabado;//essa variavel esta como global por que tenho que pegar o id PA principal depois vejo os atrelados assim ficará ordenado
            $id_pa_atrelados = custos::pas_atrelados($id_produto_acabado);
            $linhas_tela = count($id_pa_atrelados);
            for($tela = 0; $tela < $linhas_tela; $tela++) {//Loop 
                $id_produto_acabado = $id_pa_atrelados[$tela];
                $retorno            = estoque_acabado::qtde_estoque($id_produto_acabado);
                $est_comprometido   = $retorno[8];
                //Se o Estoque Comprometido for Menor do que Zero, eu tenho que atualizar esse Campo ...
                if($est_comprometido < 0) {
                    $sql = "UPDATE `produtos_acabados` SET `status_material_novo` = 1 WHERE `id_produto_acabado`= '$id_produto_acabado' LIMIT 1 ";
                    bancos::sql($sql);
                }
            }
	}
/***********************************Corrige a Produção***********************************/
	function atualizar_producao($id_produto_acabado) {//aqui eu atualizo a table de estoque, para otmizar a funçao de estoque
//Aqui eu verifico tudo o que existe para produzir das OPs em aberto ...
            $sql = "SELECT `id_op`, `qtde_produzir` 
                    FROM `ops` 
                    WHERE `status_finalizar` = '0' 
                    AND `id_produto_acabado` = '$id_produto_acabado' 
                    AND `ativo` = '1' ";
            $campos = bancos::sql($sql);//pego tudo que está para produzir até agora deste produto
            $linhas = count($campos);
            for($i = 0; $i < $linhas; $i++) {
                $sql = "SELECT SUM(bop.`qtde_baixa`) AS `qtde_produzido` 
                        FROM `ops` 
                        INNER JOIN `baixas_ops_vs_pas` bop ON bop.`id_op` = ops.`id_op` AND bop.`id_produto_acabado` = ops.`id_produto_acabado` 
                        WHERE ops.`status_finalizar` = '0' 
                        AND ops.id_op = '".$campos[$i]['id_op']."' ";
                $campos_produzido = bancos::sql($sql);
                $qtde_produzir+= ($campos_produzido[0]['qtde_produzido'] >= $campos[$i]['qtde_produzir']) ? 0 : $campos[$i]['qtde_produzir'] - $campos_produzido[0]['qtde_produzido'];
            }
/*Aqui verifico todas as OEs q ainda não foram finalizadas do PA passado por parâmetro, onde subtraio 
do Total da Qtde à Retornar o Total da Qtde de Entrada ...*/
            $sql = "SELECT (SUM(`qtde_a_retornar`) - SUM(`qtde_e`)) AS restante 
                    FROM `oes` 
                    WHERE `id_produto_acabado_e` = '$id_produto_acabado' 
                    AND `status_finalizar` = '0' ";
            $campos_oes_em_aberto = bancos::sql($sql);
            $qtde_produzir+=        ($campos_oes_em_aberto[0]['restante'] > 0) ? $campos_oes_em_aberto[0]['restante'] : 0;//Soma todas as OEs também ...
            if($qtde_produzir < 0)  $qtde_produzir = 0;//Para não mostra a produção negativa pois não existe ...

            $sql = "UPDATE `estoques_acabados` SET `qtde_oe_em_aberto` = '".$campos_oes_em_aberto[0]['restante']."', `qtde_producao` = '$qtde_produzir' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            bancos::sql($sql);
	}
/***********************************Corrige o Estoque Disponível, Separado***********************************/
	function controle_estoque_pa($id_produto_acabado) {
            //Busca a Qtde em Estoque do PA ...
            $sql = "SELECT `qtde` 
                    FROM `estoques_acabados` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 1) {//Se existe Estoque para este PA ...
                $qtde_estoque = $campos[0]['qtde'];
            }else {//Se não existe, inclui-se um Estoque para este PA ...
                $sql = "INSERT INTO `estoques_acabados` (`id_produto_acabado`) VALUES ('$id_produto_acabado') ";
                bancos::sql($sql);
                $qtde_estoque = 0;
            }
            /******************************************************************************/
            /********************Modificado em 21/07/2010 - Darcio / Roberto***************/
            /******************************************************************************/
            //Aqui eu pego a qtde_faturada do "PA" corrente mas somente dos Itens de Pedidos Parciais ...
            $sql = "SELECT SUM(`qtde_faturada`) AS qtde_faturada, SUM(`qtde` - `qtde_pendente` - `vale`) AS separada 
                    FROM `pedidos_vendas_itens` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' 
                    AND `status` < '2' ";
            $campos_itens_pedido            = bancos::sql($sql);
            $qtde_separada                  = (count($campos_itens_pedido) == 1) ? $campos_itens_pedido[0]['separada'] - $campos_itens_pedido[0]['qtde_faturada'] : 0;
            $qtde_estoque_disponivel        = $qtde_estoque - $qtde_separada;
            if($qtde_estoque_disponivel < 0) $qtde_estoque_disponivel = 0;
            /******************************************************************************/
            //Atualiza a tabela de Estoques Acabados ...
            $sql = "UPDATE `estoques_acabados` SET `qtde_disponivel` = '$qtde_estoque_disponivel', `qtde_separada` = '$qtde_separada' 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            bancos::sql($sql);
	}
/***********************************Corrige o Estoque Pendente***********************************/
        function atualiza_qtde_pendente($id_produto_acabado) {
            //Verifico o quanto que tenho de pendência desse item em Pedidos de Vendas, status 0 "Pendência Total", status 1 "Pendência Parcial" ...
            $sql = "SELECT SUM(`qtde_pendente`) AS total_pendente 
                    FROM `pedidos_vendas_itens` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' 
                    AND `status` < '2' ";
            $campos_pendente    = bancos::sql($sql);
            $qtde_pendente      = (is_null($campos_pendente[0]['total_pendente'])) ? 0 : $campos_pendente[0]['total_pendente'];
            
            $sql = "UPDATE `estoques_acabados` SET `qtde_pendente` = '$qtde_pendente' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            bancos::sql($sql);
	}
/***********************************Retorna todas as Qtdes de Estoque***********************************/
	function qtde_estoque($id_produto_acabado, $atualizar_banco = 0) {// o certo é $atualizar_banco=0 por default ...
            //Este desvio é temporario pois preciso atualizar o data base inteira ...
            if($atualizar_banco == 1) estoque_acabado::controle_estoque_pa($id_produto_acabado);
            //Busca de todas as Qtdes do PA na Tabela de Estoque ...
            $sql = "SELECT `qtde`, `entrada_antecipada`, `qtde_disponivel`, `qtde_separada`, `qtde_faturada`, `qtde_pendente`, 
                    `status`, `qtde_oe_em_aberto`, `qtde_producao`, `racionado`, 
                    (`qtde_disponivel` - `qtde_pendente`) AS est_comprometido, 
                    `qtde_fornecedor`, `qtde_porto` 
                    FROM `estoques_acabados` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) > 0) {
                $qtde_estoque       = $campos[0]['qtde'];
                $entrada_antecipada = $campos[0]['entrada_antecipada'];
                $qtde_disponivel    = $campos[0]['qtde_disponivel'];
                $qtde_separada      = $campos[0]['qtde_separada'];
                $qtde_faturada      = $campos[0]['qtde_faturada'];
                $qtde_pendente      = $campos[0]['qtde_pendente'];
                $status_estoque     = $campos[0]['status'];
                $racionado          = $campos[0]['racionado'];
                $qtde_oe_em_aberto  = $campos[0]['qtde_oe_em_aberto'];
                $qtde_producao      = $campos[0]['qtde_producao'];
                $est_comprometido   = $campos[0]['est_comprometido'];
                $qtde_fornecedor    = $campos[0]['qtde_fornecedor'];
                $qtde_porto         = $campos[0]['qtde_porto'];
            }else {
                $sql = "INSERT INTO `estoques_acabados` (`id_estoque_acabado`, `id_produto_acabado`) VALUES (NULL, '$id_produto_acabado') ";
                bancos::sql($sql);
                $qtde_estoque       = 0;
                $entrada_antecipada = 0;
                $qtde_disponivel    = 0;
                $qtde_separada      = 0;
                $qtde_pendente      = 0;
                $qtde_faturada      = 0;
                $status_estoque     = 0;
                $racionado          = 0;
                $qtde_oe_em_aberto  = 0;
                $qtde_producao      = 0;
                $est_comprometido   = 0;
                $qtde_fornecedor    = 0;
                $qtde_porto         = 0;
            }
            
            /*Verifico se o Item possui Estoque Excedente, mas somente do que está "Em aberto" 
            para completar o jogo, ex: 1º, 2º, 3º Macho ...*/
            $sql = "SELECT SUM(`qtde`) AS qtde_pa_possui_item_faltante 
                    FROM `estoques_excedentes` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' 
                    AND `id_produto_acabado_faltante` > '0' 
                    AND `status` = '0' ";
            $campos_itens_faltantes         = bancos::sql($sql);
            $qtde_pa_possui_item_faltante   = $campos_itens_faltantes[0]['qtde_pa_possui_item_faltante'];
            $qtde_disponivel-= $qtde_pa_possui_item_faltante;

            /*Verifico se o Item possui Estoque Excedente, mas somente do que está "Em aberto" 
            para completar outro Jogo, ex: 3º ...*/
            $sql = "SELECT SUM(`qtde`) AS qtde_pa_e_item_faltante 
                    FROM `estoques_excedentes` 
                    WHERE `id_produto_acabado_faltante` = '$id_produto_acabado' 
                    AND `status` = '0' ";
            $campos_itens_faltantes     = bancos::sql($sql);
            $qtde_pa_e_item_faltante    = $campos_itens_faltantes[0]['qtde_pa_e_item_faltante'];
            
            //Verifico se o Item possui Estoque Excedente, mas somente do que está "Em aberto" ...
            $sql = "SELECT SUM(`qtde`) AS qtde_excedente 
                    FROM `estoques_excedentes` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' 
                    AND `status` = '0' ";
            $campos_excedente   = bancos::sql($sql);
            $qtde_excedente     = $campos_excedente[0]['qtde_excedente'];
            
            /* Números de Retorno ...

            * 0 - $qtde_estoque 
            * 1 - $status_estoque, 
            * 2 - $qtde_producao, 
            * 3 - $qtde_disponivel, 
            * 4 - $qtde_separada, 
            * 5 - $racionado, 
            * 6 - $qtde_faturada, 
            * 7 - $qtde_pendente, 
            * 8 - $est_comprometido, 
            * 9 - $qtde_pa_possui_item_faltante, 
            * 10 - $qtde_pa_e_item_faltante, 
            * 11 - $qtde_oe_em_aberto, 
            * 12 - $qtde_fornecedor, 
            * 13 - $qtde_porto, 
            * 14 - $qtde_excedente, 
            * 15 - $entrada_antecipada ...*/
            return array($qtde_estoque, $status_estoque, $qtde_producao, $qtde_disponivel, $qtde_separada, $racionado, $qtde_faturada, $qtde_pendente, $est_comprometido, $qtde_pa_possui_item_faltante, $qtde_pa_e_item_faltante, $qtde_oe_em_aberto, $qtde_fornecedor, $qtde_porto, $qtde_excedente, $entrada_antecipada);
	}
        
        //Pega a qtde comprometida programada do sistema, para não produzir PA(s) p/ Pedidos acima de um mês ...
        function qtde_programada($id_produto_acabado) {
            $sql = "SELECT (SUM(`qtde_pendente`)) AS qtde_programada 
                    FROM `pedidos_vendas_itens` pvi 
                    INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
                    WHERE pvi.`id_produto_acabado` = '$id_produto_acabado' 
                    AND pv.`faturar_em` >= DATE_ADD('".date('Y-m-d')."', INTERVAL 1 MONTH) ";//Só até próximos 30 dias ...
            $campos_programado  = bancos::sql($sql);
            return $campos_programado[0]['qtde_programada'];
        }

	function mandar_vale($id_pedido_venda_item, $qtde_vale) {//verifico se tenho separado e depois mando tudo para vale; isto se tiver em estoque	
            //Trago alguns campos do $id_pedido_venda_item que serão passados mais abaixo via e-mail + abaixo ...
            $sql = "SELECT `id_produto_acabado`, `qtde`, `qtde_pendente`, `vale` 
                    FROM `pedidos_vendas_itens` 
                    WHERE `id_pedido_venda_item` = '$id_pedido_venda_item' LIMIT 1 ";
            $campos             = bancos::sql($sql);
            $id_produto_acabado = $campos[0]['id_produto_acabado'];
            $qtde_pedido_item   = $campos[0]['qtde'];
            $qtde_pendente      = $campos[0]['qtde_pendente'];
            $vale               = $campos[0]['vale'];//Qtde anterior que já foi retirada pelo cliente ...
            
            $separada           = $qtde_pedido_item - $qtde_pendente - $vale;
            
            /* Explicação dessa parte da Fórmula => ($qtde_vale < 0 && abs($qtde_vale) <= $vale), 
            utilizada somente quando for fazer um Estorno de Vale ...
             
            Exemplo: $qtde_vale < 0 por exemplo "-5", então transformo em Positivo com o absoluto = "5" 
             
            $qtde_vale = -5; digitado pelo Usuário ...
            $vale      = 5; gravado na tabela Pedidos ...
             
            Ou seja  abs($qtde_vale) = 5 <= 5, então pode passar ...*/

            if((($qtde_vale <= $separada) && ($qtde_vale != 0)) || ($qtde_vale < 0 && abs($qtde_vale) <= $vale)) {
                estoque_acabado::manipular($id_produto_acabado, -$qtde_vale, 0, 4, "Envio de Vale. id_pedido_venda_item = $id_pedido_venda_item");

                $sql = "UPDATE `pedidos_vendas_itens` SET `status_estoque` = '1', `vale` = `vale` + $qtde_vale WHERE `id_pedido_venda_item` = '$id_pedido_venda_item' LIMIT 1 ";
                bancos::sql($sql);
                estoque_acabado::qtde_estoque($id_produto_acabado, 1);//depois dos calculos preciso atualizar a tabela de estoque PA n~  tirar esta linha
            }
            return 1;
	}

        function mover_para_pendencia($id_pedido_venda_item, $gerar_relatorio = 0) {
            //Aqui trago dados vasculho os itens desse pedido que estão em Aberto ...
            $sql = "SELECT `id_pedido_venda_item`, `id_pedido_venda`, `id_produto_acabado`, 
                    `qtde`, `qtde_pendente`, `vale`, `qtde_faturada` 
                    FROM `pedidos_vendas_itens` 
                    WHERE `id_pedido_venda_item` = '$id_pedido_venda_item' 
                    AND `status` < '2' ";
            $campos = bancos::sql($sql);

            //Provalmente eu guardo este campo abaixo do faturado por item depois analisar com calma ...
            $faturado       = (integer)$campos[0]['qtde_faturada'];
            $nova_separada  = ($campos[0]['qtde_pendente'] + $campos[0]['vale']) - $campos[0]['qtde'] - $campos[0]['qtde_faturada'];
            //Aqui guarda na tabela relacional para poder gerar um relatório de separação do Estoque
            if($gerar_relatorio == 1) {
                if($nova_separada != '0.00') {
                    $sql = "INSERT INTO `pedidos_vendas_separacoes` (`id_pedido_venda_separacao`, `id_pedido_venda`, `id_produto_acabado`, `id_funcionario`, `qtde_separado`, `qtde_vale`, `data_sys`) VALUES (NULL, '".$campos[0]['id_pedido_venda']."', '".$campos[0]['id_produto_acabado']."', '$GLOBALS[id_funcionario]', '$nova_separada', '0.00', '".date('Y-m-d H:i:s')."') ";
                    bancos::sql($sql);
                }
            }
            //Muda o status_estoque p/ 1, para saber que o estoquista mexeu no produto ...
            $sql = "UPDATE `pedidos_vendas_itens` SET `qtde_pendente` = (`qtde` - `vale` - $faturado), `status_estoque` = '1' WHERE `id_pedido_venda_item` = '".$campos[0]['id_pedido_venda_item']."' LIMIT 1 ";
            bancos::sql($sql);

            estoque_acabado::qtde_estoque($campos[0]['id_produto_acabado'], 1);//depois dos calculos preciso atualizar a tabela de estoque PA n~  tirar esta linha
            estoque_acabado::atualiza_qtde_pendente($campos[0]['id_produto_acabado']);//so atualizo o banco de dados
	}

	function separar_tudo($id_pedido_venda_item) {
            $data_sys = date('Y-m-d H:i:s');
            //Aqui eu vasculho os itens em Aberto desse pedido ...
            $sql = "SELECT id_pedido_venda, qtde_pendente, id_produto_acabado 
                    FROM `pedidos_vendas_itens` 
                    WHERE `id_pedido_venda_item` = '$id_pedido_venda_item' 
                    AND status < '2' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) > 0) {
                $retorno            = estoque_acabado::qtde_estoque($campos[0]['id_produto_acabado']);//busco a qtde do estoque do PA corrente
                $status_estoque     = $retorno[1]; //status do estoque para saber se ele está bloqueado
                $racionado          = $retorno[5]; //status do estoque para saber se ele está racionado
                $qtde_pendente      = $campos[0]['qtde_pendente'] - $retorno[3]; //cuidado aqui é um truque direto p/ saber se pode mesmo separa este item
                //Preciso deste macete para quando eu incluir uma qtde de item menos q a est. disp. p/ ele nao dá erro
                if($qtde_pendente < 0) $qtde_pendente = 0;
                $nova_separada 		= $campos[0]['qtde_pendente'] - $qtde_pendente;
                //Aqui guarda na tabela relacional para poder gerar um relatório de separação do Estoque
                if($nova_separada != '0.00') {
                    $sql = "INSERT INTO `pedidos_vendas_separacoes` (`id_pedido_venda_separacao`, `id_pedido_venda`, `id_produto_acabado`, `id_funcionario`, `qtde_separado`, `qtde_vale`, `data_sys`) VALUES (NULL, '".$campos[0]['id_pedido_venda']."', '".$campos[0]['id_produto_acabado']."', '$_SESSION[id_funcionario]', '$nova_separada', '0.00', '$data_sys') ";
                    bancos::sql($sql);
                }
                //Muda o status_estoque p/ 1, para saber que o estoquista mexeu no produto
                $sql= "UPDATE `pedidos_vendas_itens` SET `qtde_pendente` = '$qtde_pendente', `status_estoque` = '1' WHERE `id_pedido_venda_item` = '$id_pedido_venda_item' LIMIT 1 ";
                bancos::sql($sql);
                estoque_acabado::qtde_estoque($campos[0]['id_produto_acabado'], 1);//depois dos calculos preciso atualizar a tabela de estoque PA n~  tirar esta linha
                estoque_acabado::atualiza_qtde_pendente($campos[0]['id_produto_acabado']);//so atualizo o banco de dados
            }
	}

	function status_estoque($id_produto_acabado, $status) {
            $sql = "SELECT status 
                    FROM `estoques_acabados` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if($status == 1) {//Quero travar ...
                if($campos[0]['status'] == 1) {
                    return 0; //significa q tem outra pessoa usando a tela q contem este produto então eu bloqueio ele
                }else {
                    $sql = "UPDATE `estoques_acabados` SET `status` = '$status' WHERE `id_produto_acabado` = '$id_produto_acabado' AND `status` = '0' LIMIT 1 ";
                }
            }else {//quero destravar
                if($campos[0]['status'] == 1) {
                    $sql = "UPDATE `estoques_acabados` SET `status` = '$status' WHERE `id_produto_acabado` = '$id_produto_acabado' AND `status` = '1' LIMIT 1 ";
                }
            }
            bancos::sql($sql);
            return 1;
	}
/***********************************Corrige o Estoque Real e o Estoque Faturado***********************************/
	/*Tenho q fazer uma funcao q controle a alteracao da pendencia do estoquista q e parecida esta debaixo
	fazer tambem a manipulacao do Estoque acabado quando eu compro pelo modulo compras */
	function manipular($id_produto_acabado, $qtde=0, $qtde_producao=0, $acao=0, $obs_acao="", $qtde_faturada=0) { //manipula a qtde total do estoque de cada PA corrente		
            /* acao
                    0-> Indefinido
                    1-> Manipular / OPS / Substituir => tanto para + como para - / tira de um produto e e coloca em outro
                    2-> Compras      => compras de PA REVENDA
                    3-> faturamento=> saida ou entrada de nota fiscal / devolucao
                    4-> vale            => tanto para + como para -

                    <option value="B">BAIXA DO ESTOQUE</option>
                    <option value="E">ENTRADA DE PRODUÇÃO</option>
                    <option value="S">ESTORNO DE BAIXA</option>
                    <option value="I">INVENTÁRIO</option>
                    <option value="M">MANIPULAÇÃO DO ESTOQUE</option>
                    <option value="O">OC</option>
                    <option value="P">OP NOVA</option>
                    <option value="R">REFUGO</option>
                    <option value="U">USO P/ FÁBRICA</option>
            */
            $data_atual = date('Y-m-d H:i:s');
            
            if(strtolower($qtde) == 'zerar') {//Se sim simplesmente eu zero o estoque ...
                $sql = "UPDATE `estoques_acabados` SET `qtde` = '0', `qtde_faturada` = qtde_faturada - $qtde_faturada, `data_atualizacao` = '$data_atual' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            }else {
                $sql = "UPDATE `estoques_acabados` SET `qtde` = qtde + $qtde, `qtde_faturada` = qtde_faturada - $qtde_faturada, `data_atualizacao` = '$data_atual' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            }
            bancos::sql($sql);
            
            $sql = "SELECT `qtde` AS qtde_atual_estoque 
                    FROM `estoques_acabados` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            $campos         = bancos::sql($sql);
            $saldo_estoque  = $campos[0]['qtde_atual_estoque'];
            
            $sql = "INSERT INTO `rel_saldos_estoques` (`id_rel_saldo_estoque`, `id_produto_acabado`, `id_funcionario`, `qtde_manipulada`, `saldo_est_real`, `data_acao`, `acao`, `obs_acao`) 
                    VALUES (NULL, '$id_produto_acabado', '$_SESSION[id_funcionario]', '$qtde', '$saldo_estoque', '$data_atual', '$acao', '$obs_acao') ";
            bancos::sql($sql);
	}

	//É a qtde total do orcamento q já consta nos pedidos de mesmo origem ...
	function qtde_total_pedido($id_orcamento_venda_item, $id_pedido_venda_item) {
            /*Aqui eu verifico o quanto que eu tenho já importado desse item de orçamento
            em todos os pedidos com exceção do pedido corrente*/
            $sql = "SELECT SUM(qtde) AS qtde_total_em_pedido 
                    FROM `pedidos_vendas_itens` 
                    WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' 
                    AND `id_pedido_venda_item` <> '$id_pedido_venda_item' ";
            $campos = bancos::sql($sql);
            return $campos[0]['qtde_total_em_pedido'];
	}

	function controle_pedidos_vendas_itens($id_pedido_venda_item, $acao, $nova_qtde_pedido_item = 0) {
            //Pega o Pedido e Verifica a qtde que foi solicitada no item do pedido
            $sql = "SELECT ovi.`id_produto_acabado`, ovi.`id_orcamento_venda_item`, ovi.`id_orcamento_venda`, 
                    ovi.`qtde` AS qtde_item_orcamento, pa.`referencia`, pvi.`qtde`, pvi.`qtde_pendente`, 
                    pvi.`id_pedido_venda`, pvi.`vale`, pvi.`status_estoque` 
                    FROM `pedidos_vendas_itens` pvi 
                    INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
                    WHERE pvi.`id_pedido_venda_item` = '$id_pedido_venda_item' LIMIT 1 ";
            $campos                 = bancos::sql($sql);
            $referencia             = $campos[0]['referencia'];
            $qtde_pedido_item       = $campos[0]['qtde'];
            $qtde_item_orcamento    = $campos[0]['qtde_item_orcamento'];
            $id_pedido_venda        = $campos[0]['id_pedido_venda'];
            $qtde_pendente          = $campos[0]['qtde_pendente'];
            $vale                   = $campos[0]['vale'];// qtde que já foi retirado pelo cliente 
            $id_produto_acabado     = $campos[0]['id_produto_acabado'];
            $id_orcamento_venda_item = $campos[0]['id_orcamento_venda_item'];
            $id_orcamento_venda     = $campos[0]['id_orcamento_venda'];
            
            /*Busco a Qtde_Est do PA corrente e atualizo o estoque desse também, devido o 2º parâmetro ser 1, então chamo 
            de forma embutida a função estoque_acabado::controle_estoque_pa($id_produto_acabado) p/ recalculo ...*/
            $retorno                = estoque_acabado::qtde_estoque($id_produto_acabado, 1);           
            $qtde_estoque           = $retorno[3]; //qtde disponivel
            $status_estoque         = $retorno[1]; //status do estoque para saber se ele está bloqueado
            $racionado              = $retorno[5]; //status do estoque para saber se ele está racionado
            $status_estoque_item    = $campos[0]['status_estoque'];
            $qtde_separada          = ($qtde_pedido_item - $qtde_pendente);//é a qtde q eu pedi - a qtde q ja esta pendente no estoque
            
            switch($acao) {
                case 0: //excluir item		
                    $pode_excluir_item = 'S';//O padrão é que eu posso excluir um Item normalmente ...

                    if($referencia == 'ESP') {
                        /*Se o Item for ESP então verifico se o mesmo já foi importado em mais de um pedido, se sim e existir um dos Pedidos 
                        que já estiver liberado, então ja não posso mais excluir esse item ...*/
                        $sql = "SELECT pv.id_pedido_venda 
                                FROM `pedidos_vendas_itens` pvi 
                                INNER JOIN `pedidos_vendas` pv ON pv.id_pedido_venda = pvi.id_pedido_venda AND pv.liberado = '1' 
                                WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' ";
                        $campos_esp_pedidos_liberados = bancos::sql($sql);
                        $pode_excluir_item = (count($campos_esp_pedidos_liberados) > 1) ? 'N' : 'S';
                    }
                    
                    /*Aqui ainda vai entrar a parte de Estoque Faturado quando tiver uma NFS ...
                    
                    *** Quando não tiver Vale, "$vale == 0" então posso Excluir o $id_pedido_venda_item 
                    normalmente ...
                    
                    Obs: na realidade a variável $vale == 0, apenas garante que foi feito um Estorno de Vale 
                    por parte do Rivaldo no Gerenciar, mas não necessariamente foram deletados os registros 
                    que compõem todo o histórico de tudo o que foi em Vale que ficam na tabela 
                    "vales_vendas_itens" ...*/

                    if(($vale == 0.00 || $vale == 0) && $pode_excluir_item == 'S') {
                        /*Por segurança, deleto todos os Itens de Vale do $id_pedido_venda_item 
                        passado por parâmetro aqui na Função da tabela vales_vendas_itens ...*/
                        $sql = "DELETE FROM `vales_vendas_itens` WHERE `id_pedido_venda_item` = '$id_pedido_venda_item' ";
                        bancos::sql($sql);
                        //Deleto o $id_pedido_venda_item passado por parâmetro aqui na Função ...
                        $sql = "DELETE FROM `pedidos_vendas_itens` WHERE `id_pedido_venda_item` = '$id_pedido_venda_item' LIMIT 1 ";
                        bancos::sql($sql);
                        //Daqui pra baixo eu controlo o status do pedido depois da exclusao de algum item do pedido ...
                        /* verifica se tem mais de um registro para nao fechar a nota nova*/
                        $sql = "SELECT `id_pedido_venda_item` 
                                FROM `pedidos_vendas_itens` 
                                WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
                        $campos_nova = bancos::sql($sql);
                        /* verifica se tem registro com status em aberto*/
                        $sql = "SELECT id_pedido_venda_item 
                                FROM `pedidos_vendas_itens` 
                                WHERE `id_pedido_venda` = '$id_pedido_venda' 
                                AND `status` < '2' LIMIT 1 ";
                        $campos = bancos::sql($sql);
                        if((count($campos) == 0) && (!count($campos_nova) == 0)) {
                            $sql = "UPDATE `pedidos_vendas` SET `status` = '2' WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
                        }else {
                            $sql = "UPDATE `pedidos_vendas` SET `status` = '1' WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
                        }
                        bancos::sql($sql);
                    }else {
                        return 0;
                    }
                    //Chama essa função p/ que alguns campos na tab estoque_acabado se atualizem automaticamente ...
                    estoque_acabado::controle_estoque_pa($id_produto_acabado);
                break;
                case 1://Incluir ...
                    //Chama essa função p/ que alguns campos na tab estoque_acabado se atualizem automaticamente ...
                    estoque_acabado::controle_estoque_pa($id_produto_acabado);
                break;
                case 2: //alterar item
                    if($status_estoque_item == 0) {//se o estoquista mexeu no item ele move o status para 1 e o vendedor nao pode mais mexer.
                        $qtde_total_pedido = estoque_acabado::qtde_total_pedido($id_orcamento_venda_item, $id_pedido_venda_item);/* pego a qtde do orçamento q já contas no pedido mas com exceção dele mesmo para saber a qtde totai disponivel*/
                        //significa e foi orç. 10 e tem um ped. com 9 o outro ped. nao pode ser 2 pois da 11 > 10.
                        if($nova_qtde_pedido_item > ($qtde_item_orcamento - $qtde_total_pedido)) $nova_qtde_pedido_item = $qtde_total_pedido;
                       
                        if($status_estoque == 1 || $racionado == 1) {//então tá bloqueado ou racioado ...
                            //Precisa fazer ...
                        }else {//entao faz o calculo normalmente
                            //preciso saber se esta sendo alterado para mais ou para menos para cntrolar o estoque ...
                            if($nova_qtde_pedido_item >= $qtde_separada) {// se for maior nao preciso mexer no estoque, exceto se existir estoque maior q a nova pendencia
                                //altero o valor do item e mexo na pendencia
                                if($_SESSION['id_funcionario'] <> 136) $alterar_funcionario = " `id_funcionario` = '$_SESSION[id_funcionario]', ";
                                $sql = "UPDATE `pedidos_vendas_itens` SET $alterar_funcionario `qtde` = '$nova_qtde_pedido_item' WHERE `id_pedido_venda_item` = '$id_pedido_venda_item' LIMIT 1 ";
                                bancos::sql($sql);//simplesmente eu altero o valor
                                
                                $nova_pendencia = $nova_qtde_pedido_item - $qtde_separada;
                                
                                /*Mudamos isso no dia 14/11/2016 porque não queremos mais separação automática na hora de 
                                gerar Pedido, devido muitos erros de Estoque com a Entrada dos Machos ...

                                $qtde_pendente = $_POST['txt_quantidade'][$i] - $qtde_estoque;*/

                                /*if($qtde_estoque > $nova_pendencia) {// se for maior retirar tudo se estiver incluido
                                    $sql = "UPDATE `pedidos_vendas_itens` SET $alterar_funcionario `qtde_pendente` = '0' WHERE `id_pedido_venda_item` = '$id_pedido_venda_item' LIMIT 1 ";
                                    bancos::sql($sql);//zero a pendencia
                                    //estoque_acabado::manipular($id_produto_acabado, -$nova_pendencia);
                                }else {
                                    //estoque_acabado::manipular($id_produto_acabado, "zerar");
                                    $nova_pendencia-= $qtde_estoque;
                                    $sql = "UPDATE `pedidos_vendas_itens` SET $alterar_funcionario `qtde_pendente` = '$nova_pendencia' WHERE `id_pedido_venda_item`= '$id_pedido_venda_item' LIMIT 1 ";
                                    bancos::sql($sql);//atualizo a nova pendencia de acordo com o separado
                                }*/
                                
                                $sql = "UPDATE `pedidos_vendas_itens` SET $alterar_funcionario `qtde_pendente` = '$nova_pendencia' WHERE `id_pedido_venda_item`= '$id_pedido_venda_item' LIMIT 1 ";
                                bancos::sql($sql);//atualizo a nova pendencia de acordo com o separado
                                
                            }else { //preciso mexer no estoque
                                $sql = "UPDATE `pedidos_vendas_itens` SET $alterar_funcionario `qtde` = '$nova_qtde_pedido_item' where `id_pedido_venda_item` = '$id_pedido_venda_item' LIMIT 1 ";
                                bancos::sql($sql);//simplesmente eu altero o valor
                                $sql = "Update `pedidos_vendas_itens` set $alterar_funcionario `qtde_pendente` = '0' where `id_pedido_venda_item` = '$id_pedido_venda_item' limit 1 ";
                                bancos::sql($sql);//zero o valor pendente
                                $novo_estoque = $qtde_separada - $nova_qtde_pedido_item;
                                //estoque_acabado::manipular($id_produto_acabado, $novo_estoque);
                            }
                        }
                    }
                    //Chama essa função p/ que alguns campos na tab estoque_acabado se atualizem automaticamente ...
                    estoque_acabado::controle_estoque_pa($id_produto_acabado);
                break;
                case 3: //omitir este case serve para quando eu preciso somente controlar o status do item do orcamento no caso do => orcamentos/itens/alterar_qtde.php
                    //Chama essa função p/ que alguns campos na tab estoque_acabado se atualizem automaticamente ...
                    estoque_acabado::controle_estoque_pa($id_produto_acabado);
                break;
            }
            estoque_acabado::atualiza_qtde_pendente($id_produto_acabado);//busco a qtde do estoque do PA corrente e atualizo o estoque PA
            //Aqui verifico se o PA é um PI "PIPA" para poder executar a função abaixo ...
            $sql = "SELECT `id_produto_insumo` 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' 
                    AND `id_produto_insumo` > '0' 
                    AND `ativo` = '1' LIMIT 1 ";
            $campos_pipa = bancos::sql($sql);
            if(count($campos_pipa) == 1) intermodular::gravar_campos_para_calcular_margem_lucro_estimada($campos_pipa[0]['id_produto_insumo']);
//Controle p/ atualizar o Status do Item do Orçamento ...
            $sql = "SELECT SUM(`qtde`) AS qtde_total 
                    FROM `pedidos_vendas_itens` 
                    WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
            $campos = bancos::sql($sql);
            $qtde_total = $campos[0]['qtde_total'];
            if($qtde_total == 0 || is_null($qtde_total)) {
                $status = 0;//Livre ...
            }else if($qtde_total < $qtde_item_orcamento) {
                $status = 1;//Parcial ...
            }else {
                $status = 2;//Concluído ...
            }
            $sql = "UPDATE `orcamentos_vendas_itens` SET `status` = '$status' WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
            bancos::sql($sql);
            //Verifico se existe pelo menos um Item de Orçamento que esteja com seu status "Parcial" ou "Em aberto" ...
            $sql = "SELECT id_orcamento_venda_item 
                    FROM `orcamentos_vendas_itens` 
                    WHERE `id_orcamento_venda` = '$id_orcamento_venda' 
                    AND `status` < '2' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) > 0) {//Se achar é q tem item sem está concluido então mantenho o orçamento em aberto ...
                $sql = "UPDATE `orcamentos_vendas` SET `status` = '1' WHERE `id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
                bancos::sql($sql);
            }else { //Senão fecho orçamento, ou seja, deixo como concluido o mesmo ...
                $sql = "UPDATE `orcamentos_vendas` SET `status` = '2' WHERE `id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
                bancos::sql($sql);
            }
            return 1;
	}
/****Registra Baixas aonde enxergamos no Relatório de Movimentação e faz um somatório das Qtdes não Contabilizadas 
 lançando no Estoque Real ****/
    function atualizar($id_produto_acabado) {//Vai ter que passar mais parametros
        $qtde 	= 0;//Essa quantidade é passada por parâmetro ...
        $sql = "SELECT `id_estoque_acabado` 
                FROM `estoques_acabados` 
                WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) < 1) {
            $sql = "INSERT INTO `estoques_acabados` (`id_estoque_acabado`, `id_produto_acabado`)  VALUES (NULL, '$id_produto_acabado') ";
            bancos::sql($sql);
        }
        //Verifica a qtde de Baixa do Produto que ainda não foi contabilizada ...
        $sql = "SELECT `id_baixa_manipulacao_pa`, `qtde` 
                FROM `baixas_manipulacoes_pas` 
                WHERE `id_produto_acabado` = '$id_produto_acabado' 
                AND `status` = '0' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas > 0) {
            for($i = 0; $i < $linhas; $i++) {
                $qtde+= $campos[$i]['qtde'];
                $id_baixa_manipulacao_pa.= $campos[$i]['id_baixa_manipulacao_pa'].', ';
                $sql = "UPDATE `baixas_manipulacoes_pas` SET `status` = '1' WHERE `id_baixa_manipulacao_pa` = '".$campos[$i]['id_baixa_manipulacao_pa']."' LIMIT 1 ";
                bancos::sql($sql);
            }
        }
        //O SCRIPT DE ATUALIZACAO DE COMPRAS PIPA=>PA ESTA DENTRO DO ESTOQUE_NEW => INSUMO CONSUMO ...
        estoque_acabado::manipular($id_produto_acabado, $qtde, 0 , 1, "Manipular / OPS / Substituir (baixas_manipulacoes_pas) ids_baixa_manipulacao_pa=$id_baixa_manipulacao_pa ");
    }

    function compra_producao($id_produto_acabado) {
        //Verifica se o PA passado por parâmetro é um PI ...
        $sql = "SELECT `id_produto_insumo` 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '$id_produto_acabado' 
                AND `id_produto_insumo` > '0' 
                AND `ativo` = '1' LIMIT 1 ";
        $campos_pipa = bancos::sql($sql);
        if(count($campos_pipa) == 1) {//Se sim, verifico a Compra Produção do PI nesse caso ...
            if(!class_exists('estoque_ic')) require 'estoque_new.php';
            return estoque_ic::compra_producao($campos_pipa[0]['id_produto_insumo']);
        }else {
            return 0;//Significa que não está atrelado a PI, "PIPA" ...
        }
    }

    function verificar_manipulacao_estoque($id_produto_acabado, $txt_lancamento_estoque = 0) { //Verifica se pode manipular o estoque
        /*
        $estoque_real 	= $vetor[0];
        $status_estoque = $vetor[1];
        $qtde_disponivel= $vetor[3];
        $racionado      = $vetor[5];
        $separado       = $vetor[4] - $vetor[6];
        //if(($estoque_real + $txt_lancamento_estoque) < $qtde_disponivel) {
        */

        $vetor              = estoque_acabado::qtde_estoque($id_produto_acabado);
        $estoque_real       = $vetor[0];
        $status_estoque     = $vetor[1];
        $qtde_disponivel    = $vetor[3];
        $separado           = $vetor[4];
        $racionado          = $vetor[5];

        /*Nunca podemos ter $qtde_disponivel < 0, porque significa que estamos manipulando coisas que estão separadas 
        e isso não pode ser feito, primeiro precisamos ir no Gerenciar separar ...*/
        if(($qtde_disponivel + $txt_lancamento_estoque < 0)) {
            return array ('retorno'=>'nao executar', 'valor_msg' => 4);
        }else {
            /*Aqui eu verifico a quantidade desse item em Estoque e já trago o status do Estoque para saber se este pode ser manipulado pelo Estoquista */
            if($status_estoque == 0 && $racionado == 0) { //$status_estoque => para saber se o estoquista esta manpulando o  produto 0-free  1-locked
                if($status_estoque == 0) { //$status_estoque_item => é para saber se o item poder ser manipulado ou liberado para manipular 0-free 1-lock
                    $executar = 1;//Pode manipular o estoque
                }else {
                    $executar = 1;//Pode manipular o estoque
                }
            }else if($status_estoque == 1) {//tive  tirar a condiçao racionado
                $executar = 0;//Não pode manipular o estoque
            }else {
                $executar = 1;//Pode manipular o estoque
            }
            if($executar == 1) {
                return array ('retorno' => 'executar', 'valor_msg' => 2);
            }else {
                return array ('retorno' => 'nao executar', 'valor_msg' => 3);
            }
        }
    }
        
        /*Essa função calcula a necessidade de um PA Componente p/ todas as OP(s) 
        em Aberto que utilizem esse Componente ...*/
        function necessidade_pa_componentes($id_produto_acabado) {
            $compra             = self::compra_producao($id_produto_acabado);
            $estoque_produto    = self::qtde_estoque($id_produto_acabado, '1');
            $producao           = $estoque_produto[2];
            $est_comprometido   = $estoque_produto[8];

            $necessidade_ops_em_aberto = 0;//Variável utilizada mais abaixo ...
            //Vejo por quais PAs da 7ª Etapa que este PA passado por parâmetro é utilizado ...
            $sql = "SELECT DISTINCT(pa.`id_produto_acabado`), pp.`qtde` 
                    FROM `pacs_vs_pas` pp 
                    INNER JOIN `produtos_acabados_custos` pac ON pac.`id_produto_acabado_custo` = pp.`id_produto_acabado_custo` 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` /*AND pa.`referencia` <> 'ESP' AND pa.`operacao_custo` = pac.`operacao_custo`*/ 
                    WHERE pp.`id_produto_acabado` = '$id_produto_acabado' ORDER BY pp.`id_pac_pa` ASC ";
            $campos_pas7_nivel1 = bancos::sql($sql);
            $linhas_pas7_nivel1 = count($campos_pas7_nivel1);
            for($i = 0; $i < $linhas_pas7_nivel1; $i++) {
                $compra_nivel1              = self::compra_producao($campos_pas7_nivel1[$i]['id_produto_acabado']);
                $estoque_produto_nivel1     = self::qtde_estoque($campos_pas7_nivel1[$i]['id_produto_acabado'], '1');
                $producao_nivel1            = $estoque_produto_nivel1[2];
                $necessidade_ops_em_aberto+= ($compra_nivel1 + $producao_nivel1) * $campos_pas7_nivel1[$i]['qtde'];
            }
            $necessidade_pa_componentes = $necessidade_ops_em_aberto - ($compra + $producao + $est_comprometido);
            return $necessidade_pa_componentes;
        }
}
?>