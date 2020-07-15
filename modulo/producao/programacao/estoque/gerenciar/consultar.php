<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/custos.php');
require('../../../../../lib/data.php');
require('../../../../../lib/estoque_acabado.php');
require('../../../../../lib/financeiros.php');
require('../../../../../lib/intermodular.php');
require('../../../../../lib/vendas.php');
require('../../../../classes/array_sistema/array_sistema.php');
segurancas::geral($PHP_SELF, '../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    $data_atual             = data::datatodate(date('d/m/Y'), '-');
    $data_atual_mais_um     = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), 1), '-');
//Vou utilizar essa variável no arquivo de consultar_produtos
    $data_atual_menos_sete  = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -7), '-');
//Se estiver marcado 'Estoque Disponível / Comprometido < 0' ou 'Todos PA(s) com Estoque Disponível / Comprometido < 0'
    if(!empty($chkt_est_disp_comp_zero) || !empty($opcao3)) {
        $condicao.= " AND (ea.`qtde_disponivel` - ea.`qtde_pendente`) < '0' ";
        $order_by = " (-(ea.`qtde_disponivel` - ea.`qtde_pendente`) * (pa.`preco_unitario` * (1 - ged.`desc_base_a_nac` / 100) * (1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100)) * ged.`desc_medio_pa`) DESC "; // aqui nao posso passar o apelido por causa da sql de paginacao ela nao intende este apelido e dar erro
    }else {
        $order_by = " pa.`discriminacao` ";
    }
    if($cmb_credito == '') $cmb_credito = '%';
    if($opcao1 == 1) {//Significa que se deseja trazer todos os Pedidos
        $sql_extra = "SELECT COUNT(DISTINCT(pv.`id_pedido_venda`)) AS total_registro 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `transportadoras` t ON t.`id_transportadora` = pv.`id_transportadora` 
                    INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND c.`ativo` = '1' 
                    WHERE pv.`status` < '2' ";

        $sql = "SELECT DISTINCT(pv.`id_pedido_venda`), pv.`id_cliente_contato`, pv.`id_empresa`, 
                pv.`faturar_em`, pv.`condicao_faturamento`, pv.`data_emissao`, c.`id_cliente`, c.`nomefantasia`, 
                c.`razaosocial`, c.`credito`, t.`nome` AS transportadora, pv.`liberado` 
                FROM `pedidos_vendas` pv 
                INNER JOIN `transportadoras` t ON t.`id_transportadora` = pv.`id_transportadora` 
                INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND c.`ativo` = '1' 
                WHERE pv.`status` < '2' 
                ORDER BY c.`razaosocial`, c.`nomefantasia`, pv.`id_empresa` ";
        $arquivo = 1;
    }else if($opcao2 == 1) {//Significa que se deseja trazer todos os Clientes (Programados)
        $data_atual_mais_quatro = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), 4), '-');
        $sql_extra = "SELECT COUNT(DISTINCT(pv.`id_pedido_venda`)) AS total_registro 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `transportadoras` t ON t.`id_transportadora` = pv.`id_transportadora` 
                    INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND c.`ativo` = '1' 
                    WHERE pv.`faturar_em` >= '$data_atual_mais_quatro' 
                    AND pv.`status` < '2' ";

        $sql = "SELECT DISTINCT(pv.`id_pedido_venda`), pv.`id_cliente_contato`, pv.`id_empresa`, 
                pv.`faturar_em`, pv.`condicao_faturamento`, pv.`data_emissao`, c.`id_cliente`, c.`nomefantasia`, 
                c.`razaosocial`, c.`credito`, t.`nome` AS transportadora, pv.`liberado` 
                FROM `pedidos_vendas` pv 
                INNER JOIN `transportadoras` t ON t.`id_transportadora` = pv.`id_transportadora` 
                INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND c.`ativo` = '1' 
                WHERE pv.`faturar_em` >= '$data_atual_mais_quatro' 
                AND pv.`status` < '2' ORDER BY pv.`faturar_em`, c.`razaosocial` ";
        $arquivo = 1;
    }else if($opcao3 == 1) {//Significa que se deseja trazer todos os PA(s) com Estoque Disponível / Comprometido < 0
        $sql_extra = "SELECT COUNT(DISTINCT(pa.`id_produto_acabado`)) AS total_registro 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `estoques_acabados` ea ON ea.`id_produto_acabado` = pa.`id_produto_acabado` 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` <> '23' 
                    WHERE pa.`ativo` = '1' $condicao ";

        $sql = "SELECT pa.`id_produto_acabado`, pa.`operacao_custo`, pa.`referencia`, pa.`discriminacao`, 
                pa.`pecas_por_jogo`, pa.`mmv`, ea.`prazo_entrega`, ea.`racionado`, ed.`razaosocial`, 
                ged.`desc_medio_pa`, gpa.`nome`, (pa.`preco_unitario` * (1 - ged.`desc_base_a_nac` / 100) * (1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100)) AS preco_list_desc, 
                (-(ea.`qtde_disponivel` - ea.`qtde_pendente`) * (pa.`preco_unitario` * (1 - ged.`desc_base_a_nac` / 100) * (1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100)) * ged.`desc_medio_pa`) AS total_rs 
                FROM `produtos_acabados` pa 
                INNER JOIN `estoques_acabados` ea ON ea.`id_produto_acabado` = pa.`id_produto_acabado` 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` <> '23' 
                WHERE pa.`ativo` = '1' $condicao ORDER BY $order_by ";
        $arquivo = 2;
    }else if($opcao4 == 1) {//Significa que se deseja trazer todos os P.A.(s) Racionado(s)
        $sql_extra = "SELECT COUNT(DISTINCT(pa.`id_produto_acabado`)) AS total_registro 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `estoques_acabados` ea ON ea.`id_produto_acabado` = pa.`id_produto_acabado` AND ea.`racionado` = '1' 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` <> '23' 
                    WHERE pa.`ativo` = '1' ";

        $sql = "SELECT pa.`id_produto_acabado`, pa.`operacao_custo`, pa.`referencia`, pa.`discriminacao`, 
                pa.`pecas_por_jogo`, pa.`mmv`, ea.`prazo_entrega`, ea.`racionado`, ed.`razaosocial`, 
                ged.`desc_medio_pa`, gpa.`nome`, (pa.`preco_unitario` *(1 - ged.`desc_base_a_nac` / 100) * (1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100)) AS preco_list_desc, 
                (-(ea.`qtde_disponivel` - ea.`qtde_pendente`) * (pa.`preco_unitario` * (1 - ged.`desc_base_a_nac` / 100) * (1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100)) * ged.`desc_medio_pa`) AS total_rs 
                FROM `produtos_acabados` pa 
                INNER JOIN `estoques_acabados` ea ON ea.`id_produto_acabado` = pa.`id_produto_acabado` AND ea.`racionado` = '1' 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` <> '23' 
                WHERE pa.`ativo` = '1' 
                ORDER BY $order_by ";
        $arquivo = 2;
    }else if($opcao5 == 1) {//Todos os PA(s) Faturáveis c/ ED > 0 e (Pendência - Programado) > 0
        /*Aqui vou pegar a qtde_programada do sistema, para nao produzir produtos para pedido acima de um mes ...
        Exemplo: Se hoje é 22/04/2014, o sistema só irá buscar pedidos a partir de 22/05/2014 ...*/
        $data_atual = date('Y-m-d');
        
        $id_produtos_acabados[] = 0;//Valor Inicial p/ não dar erro de SQL mais abaixo ...
        
        $sql = "SELECT DISTINCT(pvi.`id_produto_acabado`), ea.`qtde_pendente`, 
                SUM(pvi.`qtde_pendente`) AS qtde_programada 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN `estoques_acabados` ea ON ea.`id_produto_acabado` = pvi.`id_produto_acabado` 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
                AND pv.`faturar_em` >= DATE_ADD($data_atual, INTERVAL 1 MONTH) 
                GROUP BY pvi.`id_produto_acabado` 
                HAVING(ea.`qtde_pendente` - SUM(pvi.`qtde_pendente`)) > '0' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) $id_produtos_acabados[] = $campos[$i]['id_produto_acabado'];
        
        $sql = "SELECT pa.`id_produto_acabado`, pa.`operacao_custo`, pa.`referencia`, pa.`discriminacao`, 
                pa.`pecas_por_jogo`, pa.`mmv`, ea.`prazo_entrega`, ea.`racionado`, ed.`razaosocial`, 
                ged.`desc_medio_pa`, gpa.`nome`, (pa.`preco_unitario` *(1 - ged.`desc_base_a_nac` / 100) * (1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100)) AS preco_list_desc, 
                (-(ea.`qtde_disponivel` - ea.`qtde_pendente`) * (pa.`preco_unitario` * (1 - ged.`desc_base_a_nac` / 100) * (1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100)) * ged.`desc_medio_pa`) AS total_rs 
                FROM `produtos_acabados` pa 
                INNER JOIN `estoques_acabados` ea ON ea.`id_produto_acabado` = pa.`id_produto_acabado` AND ea.`qtde_disponivel` > '0' AND ea.`qtde_pendente` > '0' 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` <> '23' 
                WHERE pa.`id_produto_acabado` NOT IN (".implode(',', $id_produtos_acabados).") ";
        $arquivo = 2;
    }else if($opcao6 == 1) {//Entrada Antecipada > 0 ...
        $sql_extra = "SELECT COUNT(DISTINCT(pa.`id_produto_acabado`)) AS total_registro 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `estoques_acabados` ea ON ea.`id_produto_acabado` = pa.`id_produto_acabado` AND ea.`entrada_antecipada` > '0' 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` <> '23' 
                    WHERE pa.`ativo` = '1' ";

        $sql = "SELECT pa.`id_produto_acabado`, pa.`operacao_custo`, pa.`referencia`, pa.`discriminacao`, 
                pa.`pecas_por_jogo`, pa.`mmv`, ea.`prazo_entrega`, ea.`racionado`, ed.`razaosocial`, 
                ged.`desc_medio_pa`, gpa.`nome`, (pa.`preco_unitario` *(1 - ged.`desc_base_a_nac` / 100) * (1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100)) AS preco_list_desc, 
                (-(ea.`qtde_disponivel` - ea.`qtde_pendente`) * (pa.`preco_unitario` * (1 - ged.`desc_base_a_nac` / 100) * (1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100)) * ged.`desc_medio_pa`) AS total_rs 
                FROM `produtos_acabados` pa 
                INNER JOIN `estoques_acabados` ea ON ea.`id_produto_acabado` = pa.`id_produto_acabado` AND ea.`entrada_antecipada` > '0' 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` <> '23' 
                WHERE pa.`ativo` = '1' 
                ORDER BY $order_by ";
        $arquivo = 2;
    }else if($opcao7 == 1) {//Significa que se deseja trazer todos os Clientes (Pendentes) e Faturáveis
        //Dias à mais à partir de hoje para considerar item como faturável ...
        $dias_a_mais            = genericas::variavel(85);
        $data_atual_mais_dias   = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), $dias_a_mais), '-');
        
        $sql_extra = "SELECT COUNT(DISTINCT(c.`id_cliente`)) AS total_registro 
                FROM `pedidos_vendas` pv 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND c.`ativo` = '1' AND c.`credito` LIKE '$cmb_credito' 
                WHERE pv.`status` < '2' 
                AND pv.`faturar_em` <= '$data_atual_mais_dias' 
                AND pv.`condicao_faturamento` = '1' 
                GROUP BY c.`id_cliente` ";

        $sql = "SELECT DISTINCT(c.`id_cliente`), 
                IF(c.`razaosocial` = '', c.`nomefantasia`, CONCAT(c.`razaosocial`, ' - ', c.`nomefantasia`)) AS cliente, 
                c.`credito`, c.`limite_credito`, c.`tipo_faturamento`, pv.`id_empresa`, 
                SUM(pvi.`preco_liq_final` * (pvi.`qtde` - pvi.`qtde_faturada`)) AS total_empresa 
                FROM `pedidos_vendas` pv 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND c.`ativo` = '1' AND c.`credito` LIKE '$cmb_credito' 
                WHERE pv.`status` < '2' 
                AND pv.`faturar_em` <= '$data_atual_mais_dias' 
                AND pv.`condicao_faturamento` = '1' 
                GROUP BY c.`id_cliente` 
                ORDER BY total_empresa DESC ";
        $arquivo = 3;
    }else if($opcao8 == 1) {//Significa que se deseja trazer todos os Clientes (Pendentes)
        $sql_extra = "SELECT COUNT(DISTINCT(c.`id_cliente`)) AS total_registro 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                    INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND c.`credito` LIKE '$cmb_credito' AND c.`ativo` = '1' 
                    WHERE pv.`status` < '2' GROUP BY c.`id_cliente` ";
        
        $sql = "SELECT DISTINCT(c.`id_cliente`), 
                IF(c.`razaosocial` = '', c.`nomefantasia`, CONCAT(c.`razaosocial`, ' - ', c.`nomefantasia`)) AS cliente, 
                c.`credito`, c.`limite_credito`, c.`tipo_faturamento`, pv.`id_empresa`, 
                SUM(pvi.`preco_liq_final` * (pvi.`qtde` - pvi.`qtde_faturada`)) AS total_empresa 
                FROM `pedidos_vendas` pv 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND c.`credito` LIKE '$cmb_credito' AND c.`ativo` = '1' 
                WHERE pv.`status` < '2' 
                GROUP BY c.`id_cliente` 
                ORDER BY total_empresa DESC ";
        $arquivo = 3;
    }else {//Significa que se deseja trazer todos os PA(s) com Nova Entrada em Estoque
        $condicao_pedidos_vendas = '';//Estado Inicial ...
                
        if(!empty($txt_observacao_pedido)) {
            //Aqui eu trago o Orçamento através das Observações que foram Registradas em Follow-Ups ...
            $sql = "SELECT `identificacao` 
                    FROM `follow_ups` 
                    WHERE `origem` = '2' 
                    AND `observacao` LIKE '%$txt_observacao_pedido%' ";
            $campos_follow_ups = bancos::sql($sql);
            $linhas_follow_ups = count($campos_follow_ups);
            if($linhas_follow_ups > 0) {
                for($i = 0; $i < $linhas_follow_ups; $i++) $vetor_pedido_vendas[] = $campos_follow_ups[$i]['identificacao'];
                $condicao_pedidos_vendas = " AND pv.`id_pedido_venda` IN (".implode($vetor_pedido_vendas, ',').") ";
            }else {//Não encontrou nenhum Item ...
                /*Se essa variável não foi abastecida mais acima, então faço esse tratamento p/ 
                não furar a Query mais abaixo ...*/
                if(empty($condicao_pedidos_vendas)) $condicao_pedidos_vendas = " AND pv.`id_pedido_venda` = '0' ";
            }
        }
        
        if($cmb_familia == '')          $cmb_familia = '%';
        if($cmb_grupo_pa == '')         $cmb_grupo_pa = '%';
        if($cmb_empresa_divisao == '') 	$cmb_empresa_divisao = '%';

        if(!empty($txt_numero_pedido) || !empty($txt_observacao_pedido)) {
            $sql_extra = "SELECT COUNT(DISTINCT(pv.`id_pedido_venda`)) AS total_registro 
                        FROM `pedidos_vendas` pv 
                        INNER JOIN `transportadoras` t ON t.`id_transportadora` = pv.`id_transportadora` 
                        INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND c.`ativo` = '1' 
                        WHERE pv.`id_pedido_venda` LIKE '%$txt_numero_pedido%' 
                        AND pv.`status` < '2' 
                        $condicao_pedidos_vendas ";
            
            $sql = "SELECT DISTINCT(pv.`id_pedido_venda`), pv.`id_cliente_contato`, pv.`id_empresa`, 
                    pv.`faturar_em`, pv.`condicao_faturamento`, pv.`data_emissao`, pv.`liberado`, 
                    c.`id_cliente`, c.`nomefantasia`, c.`razaosocial`, c.`credito`, t.`nome` AS transportadora 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `transportadoras` t ON t.`id_transportadora` = pv.`id_transportadora` 
                    INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND c.`ativo` = '1' 
                    WHERE pv.`id_pedido_venda` LIKE '%$txt_numero_pedido%' 
                    AND pv.`status` < '2' 
                    $condicao_pedidos_vendas 
                    ORDER BY c.`razaosocial`, c.`nomefantasia`, pv.`id_empresa` ";
            $arquivo = 1;
        }else if(!empty($txt_referencia) || !empty($txt_discriminacao) || !empty($txt_observacao_produto) || $cmb_familia != '%' || $cmb_grupo_pa != '%' || $cmb_empresa_divisao != '%') {
            if(!empty($txt_observacao_produto)) $condicao_observacao = " and pa.observacao like '%$txt_observacao_produto%' ";
            $sql = "SELECT pa.`id_produto_acabado`, pa.`operacao_custo`, pa.`referencia`, pa.`discriminacao`, 
                    pa.`pecas_por_jogo`, pa.`mmv`, ea.`prazo_entrega`, ea.`racionado`, ed.`razaosocial`, 
                    ged.`desc_medio_pa`, gpa.`nome`, (pa.`preco_unitario` * (1 - ged.`desc_base_a_nac` / 100) * (1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100)) AS preco_list_desc, 
                    (-(ea.`qtde_disponivel` - ea.`qtde_pendente`) * (pa.`preco_unitario` * (1 - ged.`desc_base_a_nac` / 100) * (1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100)) * ged.`desc_medio_pa`) AS total_rs 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `estoques_acabados` ea ON ea.`id_produto_acabado` = pa.`id_produto_acabado` 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` AND ed.`id_empresa_divisao` LIKE '$cmb_empresa_divisao' 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` <> '23' AND gpa.`id_grupo_pa` LIKE '$cmb_grupo_pa' AND gpa.`id_familia` LIKE '$cmb_familia' 
                    WHERE pa.`referencia` LIKE '%$txt_referencia%' 
                    AND pa.`discriminacao` LIKE '%$txt_discriminacao%' 
                    $condicao_observacao 
                    AND pa.`ativo` = '1' 
                    $condicao ORDER BY $order_by ";
            $arquivo = 2;
        }else if(!empty($txt_cliente) || $cmb_credito != '%') {
            $sql_extra = "SELECT COUNT(DISTINCT(c.`id_cliente`)) AS total_registro 
                        FROM `clientes` c 
                        INNER JOIN `pedidos_vendas` pv ON pv.`id_cliente` = c.`id_cliente` AND pv.`id_pedido_venda` LIKE '%$txt_numero_pedido%' AND pv.`status` < '2' $condicao_pedidos_vendas 
                        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                        WHERE (c.`nomefantasia` LIKE '%$txt_cliente%' OR c.`razaosocial` LIKE '%$txt_cliente%') 
                        AND c.`ativo` = '1' 
                        AND c.`credito` LIKE '$cmb_credito' 
                        GROUP BY c.`id_cliente` ";
            
            $sql = "SELECT DISTINCT(c.`id_cliente`), 
                    IF(c.`razaosocial` = '', c.`nomefantasia`, CONCAT(c.`razaosocial`, ' - ', c.`nomefantasia`)) AS cliente, 
                    c.`credito`, c.`limite_credito`, pv.`id_empresa`, 
                    SUM(pvi.`preco_liq_final` * (pvi.`qtde` - pvi.`qtde_faturada`)) AS total_empresa 
                    FROM `clientes` c 
                    INNER JOIN `pedidos_vendas` pv ON pv.`id_cliente` = c.`id_cliente` AND pv.`id_pedido_venda` LIKE '%$txt_numero_pedido%' AND pv.`status` < '2' $condicao_pedidos_vendas 
                    INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                    WHERE (c.`nomefantasia` LIKE '%$txt_cliente%' OR c.`razaosocial` LIKE '%$txt_cliente%') 
                    AND c.`ativo` = '1' 
                    AND c.`credito` LIKE '$cmb_credito' 
                    GROUP BY c.`id_cliente` ORDER BY cliente ";
            $arquivo = 3;
        }
    }
//Significa que foi solicitado o relatório
    if($arquivo == 1) {
        require('consultar_pedidos.php');
        exit;
    }
//Significa que foi solicitado o relatório
    if($arquivo == 2) {
        $veio_do_gerenciar = 'S';
        require('../../../../vendas/estoque_acabado/consultar.php');//Otimização de Tela feita em 10/06/2016 ...
        exit;
    }
//Significa que foi solicitado o relatório
    if($arquivo == 3) {
        require('consultar_clientes.php');
        exit;
    }
}else {
?>
<html>
<head>
<title>.:: Gerenciar Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function desabilita_caixas() {
    var elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'text' || elementos[i].type == 'select-one') {
            elementos[i].disabled   = true
            elementos[i].value      = ''
            elementos[i].className  = 'textdisabled'
        }
    }
//Controle com os Checkbox ...
    document.form.opcao1.checked = false//Todos os Pedidos ...
    document.form.opcao2.checked = false//Todos os Clientes (Programados) ...
    document.form.opcao3.checked = false// Todos PA(s) com Estoque Disponível / Comprometido < 0 ...
    document.form.opcao4.checked = false//Todos os PA(s) Racionado(s) ...
    document.form.opcao5.checked = false//Todos os PA(s) Faturáveis c/ ED > 0 e (Pendência - Programado) > 0 ...
    document.form.opcao6.checked = false//Entrada Antecipada > 0 ...
    document.form.opcao7.checked = false//Todos os Clientes (Pendentes) e Faturáveis ...
    document.form.opcao8.checked = false//Todos os Clientes (Pendentes) ...
}

function habilita_caixas() {
    var elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'text' || elementos[i].type == 'select-one') {
            elementos[i].disabled  = false
            elementos[i].className = 'caixadetexto'
        }
    }
    document.form.txt_cliente.focus()
}

function limpar(objeto) {
    if(objeto.checked == true) {
        desabilita_caixas()
        objeto.checked = true
    }else {
        habilita_caixas()
    }
}

function controlar_foco() {
//Controle especial com o do Checkbox "Estoque Disponível / Comprometido < 0"
    var numero_pedido = document.form.txt_numero_pedido.value
    var cliente = document.form.txt_cliente.value
    var observacao_pedido = document.form.txt_observacao_pedido.value
    if(numero_pedido != '' || cliente != '' || observacao_pedido != '') {
        document.form.chkt_est_disp_comp_zero.checked = false
    }
}

function validar() {
    var opcao1 = document.form.opcao1.checked
    var opcao2 = document.form.opcao2.checked
    var opcao3 = document.form.opcao3.checked
    var opcao4 = document.form.opcao4.checked
    var opcao5 = document.form.opcao5.checked
    var opcao6 = document.form.opcao6.checked
    var opcao7 = document.form.opcao7.checked
    var opcao8 = document.form.opcao8.checked

    var numero_pedido       = document.form.txt_numero_pedido.value
    var cliente             = document.form.txt_cliente.value
    var observacao_pedido   = document.form.txt_observacao_pedido.value
    var referencia          = document.form.txt_referencia.value
    var discriminacao       = document.form.txt_discriminacao.value
    var observacao_produto  = document.form.txt_observacao_produto.value
    var familia             = document.form.cmb_familia.value
    var grupo_pa            = document.form.cmb_grupo_pa.value
    var empresa_divisao     = document.form.cmb_empresa_divisao.value
    var credito             = document.form.cmb_credito.value
//Se todas as caixas de texto e combos estiverem vázias ...
    if(numero_pedido == '' && cliente == '' && observacao_pedido == '' && referencia == '' && discriminacao == '' && observacao_produto == '' && familia == '' && grupo_pa == '' && empresa_divisao == '' && credito == '') {
//Aqui eu forço a ser selecionada uma opção ...
        if(opcao1 == false && opcao2 == false && opcao3 == false && opcao4 == false && opcao5 == false && opcao6 == false && opcao7 == false && opcao8 == false) {
            alert('PREENCHA ALGUM CAMPO OU SELECIONE UMA OPÇÃO P/ FILTRO !')
            return false
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_cliente.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='75%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Gerenciar Estoque
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Número do Pedido
        </td>
        <td>
            <input type='text' name='txt_numero_pedido' title='Digite o Número do Pedido' onkeyup='controlar_foco()' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação do Pedido
        </td>
        <td>
            <input type='text' name='txt_observacao_pedido' title='Digite a Observação do Pedido' size='40' onkeyup="controlar_foco()" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao1' value='1' title='Consultar todos os Pedidos' onclick='limpar(this)' id='label1' class='checkbox'>
            <label for='label1'>Todos os Pedidos</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao2' value='1' title='Consultar todos os Clientes (Programados)' onclick='limpar(this)' id='label2' class='checkbox'>
            <label for='label2'>Todos os Clientes (Programados)</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2' bgcolor='#CECECE'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência
        </td>
        <td>
            <input type='text' name='txt_referencia' title='Digite a Referência' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discriminação
        </td>
        <td>
            <input type='text' name='txt_discriminacao' title='Digite a Discriminação' size='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação do Produto
        </td>
        <td>
            <input type='text' name='txt_observacao_produto' title='Digite a Observação do Produto' size='40' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Família
        </td>
        <td>
            <select name='cmb_familia' title='Selecione a Família' class='combo'>
            <?
                $sql = "SELECT `id_familia`, `nome` 
                        FROM `familias` 
                        WHERE `ativo` = '1' ORDER BY `nome` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Grupo PA
        </td>
        <td>
            <select name='cmb_grupo_pa' title='Selecione o Grupo P.A.' class='combo'>
            <?
                $sql = "SELECT `id_grupo_pa`, `nome` 
                        FROM `grupos_pas` 
                        WHERE `ativo` = '1' ORDER BY `nome` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Empresa Divisão
        </td>
        <td>
            <select name='cmb_empresa_divisao' title='Selecione a Empresa Divisão' class='combo'>
            <?
                $sql = "SELECT `id_empresa_divisao`, `razaosocial` 
                        FROM `empresas_divisoes` 
                        WHERE `ativo` = '1' ORDER BY `razaosocial` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='chkt_est_disp_comp_zero' value='1' title='Estoque Disponível / Comprometido < 0' onclick='controlar_foco()' id='label3' class='checkbox'>
            <label for='label3'>Estoque Disponível / Comprometido < 0</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao3' value='1' title='Todos PA(s) com Estoque Disponível / Comprometido < 0' onclick='limpar(this)' id='label4' class='checkbox'>
            <label for='label4'>Todos PA(s) com Estoque Disponível / Comprometido < 0</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao4' value='1' title='Consultar todos os PA(s) Racionado(s)' onclick='limpar(this)' id='label5' class='checkbox'>
            <label for='label5'>Todos os PA(s) Racionado(s)</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao5' value='1' title='Todos os PA(s) Faturáveis c/ ED > 0 e (Pendência - Programado) > 0' onclick='limpar(this)' id='label6' class='checkbox'>
            <label for='label6'>Todos os PA(s) Faturáveis c/ ED > 0 e (Pendência - Programado) > 0</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao6' value='1' title='Entrada Antecipada' onclick='limpar(this)' id='label7' class='checkbox'>
            <label for='label7'>
                Entrada Antecipada > 0
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2' bgcolor='#CECECE'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cliente
        </td>
        <td>
            <input type='text' name='txt_cliente' title='Digite o Cliente' size='35' onkeyup='controlar_foco()' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Crédito
        </td>
        <td>
            <select name='cmb_credito' title='Selecione o Crédito' class='combo'>
                <option value='' selected>SELECIONE</option>
                <option value='A'>A</option>
                <option value='B'>B</option>
                <option value='C'>C</option>
                <option value='D'>D</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao7' value='1' title='Consultar todos os Clientes (Pendentes) e Faturáveis' onclick='limpar(this)' id='label8' class='checkbox'>
            <label for='label8'>Todos os Clientes (Pendentes) e Faturáveis</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao8' value='1' title='Consultar todos os Clientes (Pendentes)' onclick='limpar(this)' id='label9' class='checkbox'>
            <label for='label9'>Todos os Clientes (Pendentes)</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='habilita_caixas()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
            <input type='button' name='cmd_todos_pas_com_nova_entrada_em_estoque' value='Todos PA(s) com Nova Entrada em Estoque' title='Todos PA(s) com Nova Entrada em Estoque' onclick="html5Lightbox.showLightbox(7, 'todos_pas_com_nova_entrada_em_estoque.php')" style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>