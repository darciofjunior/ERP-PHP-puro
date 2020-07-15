<?
class pdt extends bancos {
    function funcao_geral_orcamentos($tipo_retorno, $dias = '', $representante = '', $inicio = '', $pagina = '', $paginacao = 'nao') {
        $data_atual = date('Y-m-d');
        if($tipo_retorno == 1 ? $condicao = " ov.congelar = 'N' " : $condicao = " ov.`congelar` = 'S' AND ov.`status` < '2' ");
        if($dias == 'H') {//Data de Hoje
            $condicao_data = " AND `data_emissao` = '$data_atual' ";
        }else {
            $data_atual_menos_1 = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -1), '-');
            if(is_numeric($dias)) $condicao_data = " AND ov.`data_emissao` BETWEEN (DATE_ADD('$data_atual_menos_1', INTERVAL -$dias DAY)) AND '$data_atual_menos_1' ";
        }
//Quando não passar nenhum representante, então eu mando selecionar tudo, indiferente do rep ...
        if(empty($representante)) {$representante = '%';}
        $condicao_representante = " AND ovi.`id_representante` LIKE '$representante' ";

//Aqui eu busco todos os Orçamentos de acordo com os parâmetros selecionada acima ...
        $sql = "SELECT DISTINCT(ov.id_orcamento_venda), DATE_FORMAT(ov.data_emissao, '%d/%m/%Y') AS data_emissao, IF(c.razaosocial = '', c.nomefantasia, c.razaosocial) AS cliente, c.id_uf, c.cidade, cc.nome, ov.valor_orc 
                FROM `orcamentos_vendas` ov 
                INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_orcamento_venda = ov.id_orcamento_venda $condicao_representante 
                INNER JOIN `clientes_contatos` cc ON cc.id_cliente_contato = ov.id_cliente_contato 
                INNER JOIN `clientes` c ON c.id_cliente = cc.id_cliente 
                WHERE $condicao $condicao_data ORDER BY ov.data_emissao DESC ";
        if(strtoupper($paginacao) == 'SIM') {
            $campos     = bancos::sql($sql, $inicio, 15, 'sim', $pagina);
            return array('campos'=>$campos);
        }else {
            $campos = bancos::sql($sql);
            return array('campos'=>$campos);
        }
    }

    function funcao_geral_pedidos($tipo_retorno, $dias = '', $representante = '', $inicio = '', $pagina = '', $paginacao = 'nao') {
        $data_atual = date('Y-m-d');
        $order_by   = " DESC ";//O order by default será sempre desc ...
        if($tipo_retorno == 1) {//Pedidos não liberados
            $condicao = " pv.liberado = '0' ";
        }else if($tipo_retorno == 2) {//Pedidos liberados
            $condicao = " pv.liberado = '1' ";
        }else if($tipo_retorno == 3) {//Programados
            if($dias == 'H') {
                $condicao = " pv.`faturar_em` = '$data_atual' AND (pv.`data_emissao` > pv.`faturar_em`) AND pv.`status` < '2' ";
            }else {
                if(is_numeric($dias)) {
                    $condicao = " (pv.`faturar_em` > '$data_atual') AND (pv.`faturar_em` < DATE_ADD('$data_atual', INTERVAL $dias DAY)) AND (pv.`data_emissao` > pv.`faturar_em`) AND pv.`status` < '2' ";
                }else {
                    $condicao = " (pv.`data_emissao` > pv.`faturar_em`) AND pv.`status` < '2' ";
                }
            }
        }else if($tipo_retorno == 4) {//Com Vale
            $condicao = " pvi.`vale` > '0' ";
            $order_by = " ASC ";
        }else {
            $condicao = " (pv.`faturar_em` > DATE_ADD('$data_atual', INTERVAL 1 DAY) OR pv.`condicao_faturamento` > '2' OR c.`credito` IN ('C','D')) AND pv.`status` < '2' ";
        }
        if($dias == 'H') {//Data de Hoje
            $condicao_data = " AND `data_emissao` = '$data_atual' ";
        }else {
            $data_atual_menos_1 = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -1), '-');
            if(is_numeric($dias)) $condicao_data = " AND pv.`data_emissao` BETWEEN DATE_ADD('$data_atual_menos_1', INTERVAL -$dias DAY) AND '$data_atual_menos_1' ";
        }
//Quando não passar nenhum representante, então eu mando selecionar tudo, indiferente do rep ...
        if(empty($representante)) $representante = '%';
        $condicao_representante = " AND pvi.`id_representante` LIKE '$representante' ";

//Aqui eu busco todos os Pedidos de acordo com os parâmetros selecionada acima ...
        $sql = "SELECT DISTINCT(pv.`id_pedido_venda`), pv.`id_empresa`, pv.`faturar_em`, pv.`condicao_faturamento`, 
                DATE_FORMAT(pv.`data_emissao`, '%d/%m/%Y') AS data_emissao, pv.`vencimento1`, pv.`vencimento2`, 
                pv.`vencimento3`, pv.`vencimento4`, pv.`status`, c.`id_cliente`, c.`nomefantasia`, c.`razaosocial`, 
                c.`credito`, cc.`nome`, pvi.`id_representante`, pv.`valor_ped` 
                FROM `pedidos_vendas` pv 
                INNER JOIN `clientes_contatos` cc ON cc.`id_cliente_contato` = pv.`id_cliente_contato` 
                INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` $condicao_representante 
                WHERE $condicao $condicao_data ORDER BY pv.`data_emissao` $order_by ";
        if(strtoupper($paginacao) == 'SIM') {
            $campos     = bancos::sql($sql, $inicio, 15, 'sim', $pagina);
            return array('campos'=>$campos);
        }else {
            $campos = bancos::sql($sql);
            return array('campos'=>$campos);
        }
    }
	
    function funcao_geral_faturamentos($tipo_retorno, $dias = '', $representante = '', $inicio = '', $pagina = '', $paginacao = 'nao') {
        $data_atual = date('Y-m-d');
        if($tipo_retorno == 0) {//NF(s) não despachadas
            $condicao = " nfs.`status` < '4' ";
        }else if($tipo_retorno == 1) {//Devolvidas
            $condicao = " nfs.`status` = '6' ";
        }
        if($dias == 'H') {//Data de Hoje
            $condicao_data = " AND nfs.`data_emissao` = '$data_atual' ";
        }else {
            $data_atual_menos_1 = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -1), '-');
            if(is_numeric($dias)) $condicao_data = " AND nfs.`data_emissao` BETWEEN (DATE_ADD('$data_atual_menos_1', INTERVAL -$dias DAY)) AND '$data_atual_menos_1' ";
        }
//Quando não passar nenhum representante, então eu mando selecionar tudo, indiferente do rep ...
        if(empty($representante)) {$representante = '%';}
        $condicao_representante = " AND nfsi.`id_representante` LIKE '$representante' ";

//Aqui eu busco todas as NF(s) de acordo com os parâmetros selecionada acima ...
        $sql = "SELECT DISTINCT(nfs.id_nf) 
                FROM `nfs` 
                INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf $condicao_representante 
                WHERE $condicao $condicao_data ";
        if(strtoupper($paginacao) == 'SIM') {
            $campos     = bancos::sql($sql, $inicio, 15, 'sim', $pagina);
            return array('campos'=>$campos);
        }else {
            $campos = bancos::sql($sql);
            return array('campos'=>$campos);
        }
    }
	
    function funcao_geral_follow_ups($dias = '', $representante = '', $inicio = '', $pagina = '', $paginacao = 'nao') {
        $data_atual = date('Y-m-d');

        if($dias == 'H') {//Data de Hoje
            $condicao_data = " AND SUBSTRING(fu.`data_sys`, 1, 10) = '$data_atual' ";
        }else {//Mais de 1 dia ...
            $data_atual_menos_1 = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -1), '-');
            //Follow-Up ...
            if(is_numeric($dias)) $condicao_data = " AND SUBSTRING(fu.`data_sys`, 1, 10) BETWEEN (DATE_ADD('$data_atual_menos_1', INTERVAL -$dias DAY)) AND '$data_atual_menos_1' ";
        }
        
//Quando não passar nenhum representante, então eu mando selecionar tudo, indiferente do Rep ...
        if(empty($representante)) $representante = '%';
        $condicao_representante = " AND ((fu.`id_representante` LIKE '$representante') OR (fu.`id_funcionario` = '$_SESSION[id_funcionario]')) ";
        
//Aqui eu busco todos os Follow-UPs de acordo com os parâmetros selecionada acima ...
        $sql = "SELECT DISTINCT(fu.`id_follow_up`) 
                FROM `follow_ups` fu 
                WHERE 1 $condicao_data 
                $condicao_representante 
                ORDER BY fu.`data_sys` DESC ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas == 0) {//Não encontrou nenhum Follow-UP ...
            $vetor_follow_up[] = 0;
        }else {//Encontrou pelo menos 1 Follow-UP ...
            for($i = 0; $i < $linhas; $i++) $vetor_follow_up[] = $campos[$i]['id_follow_up'];
        }
        if(strtoupper($paginacao) == 'SIM') {
            $campos     = bancos::sql($sql, $inicio, 15, 'sim', $pagina);
            return array('campos'=>$campos);
        }else {
            $campos = bancos::sql($sql);
            return array('campos'=>$campos);
        }
    }
	
    function funcao_geral_financeiros($tipo_retorno, $dias = '', $representante = '', $inicio = '', $pagina = '', $paginacao = 'nao') {
        $data_atual = date('Y-m-d');

        if($tipo_retorno == 0) {//Créditos Bloqueados ...
            if($dias == 'H') {//Data de Hoje
                $condicao_data = " AND SUBSTRING(c.`credito_data`, 1, 10) = '$data_atual' ";
            }else {
                $data_atual_menos_1 = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -1), '-');
                if(is_numeric($dias)) $condicao_data = " AND SUBSTRING(c.`credito_data`, 1, 10) BETWEEN (DATE_ADD('$data_atual_menos_1', INTERVAL -$dias DAY)) AND '$data_atual_menos_1' ";
            }

//Busco todos os Clientes de acordo com o Representante selecionado pelo Usuário ...
            if(!empty($representante)) {
                $sql = "SELECT DISTINCT(`id_cliente`) 
                        FROM `clientes_vs_representantes` 
                        WHERE `id_representante` = '$representante' 
                        GROUP BY `id_cliente` ";
                $campos = bancos::sql($sql);
                $linhas = count($campos);
                //Encontrou pelo menos 1 Cliente ...
                if($linhas > 0) {
                    for($i = 0; $i < $linhas; $i++) $vetor_cliente[] = $campos[$i]['id_cliente'];
                    $condicao_cliente = " AND `id_cliente` IN (".implode(',', $vetor_cliente).") ";
                }
            }

            $sql = "SELECT DISTINCT(c.`id_cliente`), c.`nomefantasia`, c.`razaosocial`, c.`ddi_com`, c.`ddd_com`, c.`telcom`, c.`cnpj_cpf`, ct.`tipo` 
                    FROM `clientes` c 
                    INNER JOIN `clientes_tipos` ct ON ct.`id_cliente_tipo` = c.`id_cliente_tipo` 
                    WHERE c.`credito` IN ('C', 'D') 
                    $condicao_data 
                    $condicao_cliente ";
        }else {//Contas Atrasadas ...
            if($dias == 'H') {//Data de Hoje
                $condicao_data = " AND cr.`data_vencimento` = '$data_atual' ";
            }else {
                if(is_numeric($dias)) {
                    $data_atual_menos_1 = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -1), '-');
                    $condicao_data = " AND cr.`data_vencimento` BETWEEN (DATE_ADD('$data_atual_menos_1', INTERVAL -$dias DAY)) AND '$data_atual_menos_1' ";
                }else {
                    $visualizar_todas_contas = 1;
                }
            }
//Aqui é quando o usuário clica em cima do link de Contas Atrasadas ...
            if($visualizar_todas_contas == 1) {
                //Aqui eu busco todos os Clientes de acordo com os parâmetros selecionada acima ...
                $sql = "SELECT DISTINCT(`id_conta_receber`) 
                        FROM `contas_receberes` 
                        WHERE `ativo` = '1' 
                        AND `status` < '2' 
                        AND `id_representante` LIKE '$representante' 
                        $condicao_data ";
                $campos = bancos::sql($sql);
                $linhas = count($campos);
                for($l = 0; $l < $linhas; $l++) $id_contas[] = $campos[$l]['id_conta_receber'];
//Arranjo Ténico
                if(count($id_contas) == 0) {$id_contas[]='0';}
                $vetor_contas = implode(',', $id_contas);

                $sql = "SELECT cr.`id_conta_receber`, cr.`id_empresa`, cr.`id_cliente`, cr.`num_conta`, cr.`data_vencimento`, cr.`valor`, 
                        cr.`valor_pago`, tm.`simbolo`, IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente 
                        FROM `contas_receberes` cr 
                        INNER JOIN `tipos_recebimentos` t ON t.`id_tipo_recebimento` = cr.`id_tipo_recebimento` 
                        INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = cr.`id_tipo_moeda` 
                        INNER JOIN `clientes` c ON c.id_cliente = cr.id_cliente 
                        WHERE cr.`id_conta_receber` IN ($vetor_contas) ORDER BY cr.data_vencimento ";
            }else {
//Aqui eu busco todos os Clientes de acordo com os parâmetros selecionada acima ...
                $sql = "SELECT cr.`id_conta_receber`, cr.`id_empresa`, cr.`id_cliente`, cr.`num_conta`, cr.`data_vencimento`, cr.`valor`, 
                        cr.`valor_pago`, tm.`simbolo`, IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente 
                        FROM `contas_receberes` cr 
                        INNER JOIN `tipos_recebimentos` t ON t.id_tipo_recebimento = cr.id_tipo_recebimento 
                        INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = cr.id_tipo_moeda 
                        INNER JOIN `clientes` c ON c.id_cliente = cr.id_cliente 
                        WHERE cr.`ativo` = '1' 
                        AND cr.`status` < '2' 
                        AND cr.`id_representante` LIKE '$representante' 
                        $condicao_data ";
            }
        }
        if(strtoupper($paginacao) == 'SIM') {
            $campos     = bancos::sql($sql, $inicio, 15, 'sim', $pagina);
            return array('campos'=>$campos);
        }else {
            $campos = bancos::sql($sql);
            return array('campos'=>$campos);
        }
    }
	
    function funcao_cotas_metas($representante = '', $id_funcionario_pedido = '', $data_inicial, $data_final, $data_pedido = 'pv.`faturar_em`') {
//Quando não passar nenhum representante, então eu mando selecionar tudo, indiferente do rep ...
        if(!empty($representante)) $condicao_representante = " AND r.`id_representante` = '$representante' ";
        /******************************************************************************************/
        //Busco a última Cota em Vigência ...
        $sql = "SELECT SUM(rc.cota_mensal) AS total_cotas 
                FROM `representantes_vs_cotas` rc 
                INNER JOIN `representantes` r ON r.id_representante = rc.id_representante $condicao_representante AND r.`ativo` = '1' 
                WHERE rc.`data_final_vigencia` = '0000-00-00' GROUP BY r.id_representante ORDER BY r.nome_fantasia ";
        $campos = bancos::sql($sql);
//Se foi passado um representante em específico por parâmetro, então já retorno o seu valor ...
        if(!empty($representante)) {
            $total_cotas = $campos[0]['total_cotas'];
        }else {//Traz de todos os Representantes ...
            $linhas = count($campos);
            for($i = 0; $i < $linhas; $i++) $total_cotas+= $campos[$i]['total_cotas'];
        }
        $difereca_mes = data::diferenca_data($data_inicial, $data_final);
        $qtde_dias = (integer)$difereca_mes[0];
        /*Se a Qtde de Dias for do que 31, então Pego o Total de Cotas e multiplico pela diferença 
        de dias entre a Data Inicial e a Data Final ...*/
        if($qtde_dias > 31) $total_cotas = ($total_cotas / 30) * ($qtde_dias + 1);
        /******************************************************************************************/
        //Busca de Vendas em R$ ...
        $dolar_dia = genericas::moeda_dia('dolar');

        /*Se o Representante for PME e foi passado o parâmetro Funcionário, então a variável herda os itens de Pedido 
        relacionado ao func passado por parâmetro ...*/
        if($representante == 71 && $id_funcionario_pedido > 0) {
            $condicao_representante = " AND pvi.`id_representante` = '$representante' AND pvi.`id_funcionario` = '$id_funcionario_pedido' ";
        }else {
            $condicao_representante = " AND pvi.`id_representante` = '$representante' ";
        }

        $sql = "SELECT IF(c.id_pais = 31, SUM(pvi.qtde * pvi.preco_liq_final), SUM(pvi.qtde * pvi.preco_liq_final) * $dolar_dia) AS total_vendas 
                FROM `pedidos_vendas` pv 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda $condicao_representante 
                INNER JOIN `clientes` c ON c.id_cliente = pv.id_cliente 
                WHERE $data_pedido BETWEEN '$data_inicial' AND '$data_final' 
                AND pv.`liberado` = '1' GROUP BY pvi.id_representante, c.id_pais ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) $total_vendas+= $campos[$i]['total_vendas'];
        return array ('total_cotas'=>$total_cotas, 'total_vendas'=>$total_vendas);
    }
}
?>