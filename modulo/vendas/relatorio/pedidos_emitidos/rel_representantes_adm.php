<?
	$mes_ref_sg = (string)data::mes((int)date('m'));
	$mes_ref_sg = substr($mes_ref_sg,0,3).date('/Y');
?>
<tr class='linhadestaque' align='center'>
    <td colspan='2'>
        Representante(s)
    </td>
    <td>
        UF
    </td>
    <td>
        Supervisor
    </td>
    <td>
        Cotas R$
    </td>
    <td>
        Vendas R$
    </td>
    <td>
        % S. Cota
    </td>
    <td>
        % S. Total
    </td>
    <td>
        Média Diária R$
    </td>
    <td>
        Valor Devido R$
    </td>
    <td>
        Falta P/ Média R$
    </td>
</tr>
<?
if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
    $condicao_data = ($_POST['cmb_tipo_data'] == 'emissao') ? 'pv.`data_emissao`' : 'pv.`faturar_em`';
    if($_POST['chkt_livre_debito'] == 'S')  $condicao_livre_debito  = " AND pv.`livre_debito` = 'S' ";
    if($_POST['chkt_expresso'] == 'S') $condicao_expresso = " AND pv.`expresso` = 'S' ";
    
    //Busca de Vendas em R$ ...
    $valor_dolar_dia = genericas::moeda_dia('dolar');
    $mes_ref = date('m');
    $ano_ref = date('Y');
    /*******************************************************************************/
    /************************Definição da Quantidade de Meses***********************/
    /*******************************************************************************/
    if($qtde_dias_periodo_filtrado < 30) {
        $qtde_meses     = 1;//Atribuo como sendo 1 mês que seria aí o mês em Vigência ...
    }else {
        $qtde_meses     = intval($qtde_dias_periodo_filtrado / 30);//Verifico qtde de Meses do Período de Datas que foi Filtrado ...
    }
    /*******************************************************************************/
    //Aqui eu busco a Meta do Mês de cada Representante e Supervisor ...
    $sql = "SELECT `id_representante`, `comissao_meta_atingida`, `comissao_meta_atingida_sup` 
            FROM `comissoes_extras` 
            WHERE MONTH(`data_periodo_fat`) = '$mes_ref' 
            AND YEAR(`data_periodo_fat`) = '$ano_ref' ";
    $campos_perc_extra = bancos::sql($sql);
    $linhas_perc_extra = count($campos_perc_extra);
    for($i = 0; $i < $linhas_perc_extra; $i++) {
        $comissao_meta_atingida_array[$campos_perc_extra[$i]['id_representante']]       = $campos_perc_extra[$i]['comissao_meta_atingida'];
        $comissao_meta_atingida_sup_array[$campos_perc_extra[$i]['id_representante']] 	= $campos_perc_extra[$i]['comissao_meta_atingida_sup'];
    }
    //Busca do Total de Pedidos Emitidos dentro do Período ...
    $sql = "SELECT c.`id_pais`, SUM(IF(c.`id_pais` = '31', ((pvi.`qtde` - pvi.`qtde_devolvida`) * pvi.`preco_liq_final`), ((pvi.`qtde` - pvi.`qtde_devolvida`) * pvi.`preco_liq_final` * $valor_dolar_dia))) AS total 
            FROM`pedidos_vendas` pv 
            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
            WHERE $condicao_data BETWEEN '$data_inicial' AND '$data_final' 
            AND pv.`liberado` = '1' $condicao_livre_debito $condicao_expresso GROUP BY c.`id_pais` ";
    $campos_perc = bancos::sql($sql);
    $linhas_perc = count($campos_perc);
    for($i = 0; $i < $linhas_perc; $i++) {
        if($campos_perc[$i]['id_pais'] == 31) {
            $tot_nac+= $campos_perc[$i]['total'];
        }else {
            $tot_exp+= ($campos_perc[$i]['total'] * $valor_dolar_dia);
        }
    }
    $total_pedidos_emitidos = $tot_nac + $tot_exp;
	
//SQL Principal do Filtro feito pelo usuário na combo Representante ...
    for($l = 0; $l < 3; $l++) {//Faço um Loop porque a estrutura da Tela e o SQL são os mesmos independente das 3 condições ...
        //Limpo essas variáveis para não herdar valores do Loop anterior ...
        $total_cota_mensal_com_vendas 	= 0;
        $total_cota_mensal_sem_vendas 	= 0;
        $total_vendas_mensal_todos 	= 0;
        $total_media_diaria_todos       = 0;
        $total_valor_devido_todos       = 0;
        $total_falta_para_media_todos   = 0;
        $total_valor_devido_sem_vendas  = 0;
        $total_media_diaria_sem_vendas  = 0;

        $inner_join = '';
        $condicao = '';
        if($l == 0) {//Todos os que são funcionários Internos / Externos ...
            $inner_join = "INNER JOIN `representantes_vs_funcionarios` rf ON rf.id_representante = r.id_representante ";
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Grupo Albafer (Internos)
        </td>
    </tr>
<?
        }else if($l == 1) {//Autônomos Nacionais - Brasil ...
            $condicao = " AND r.id_representante NOT IN (SELECT id_representante FROM `representantes_vs_funcionarios`) AND r.id_pais = '31' ";
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Representantes Autônomos
        </td>
    </tr>
<?
        }else {//Autônomos Internacionais - Fora do Brasil ...
            $condicao = " AND r.id_representante NOT IN (SELECT id_representante FROM `representantes_vs_funcionarios`) AND r.id_pais <> '31' ";
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Exportação
        </td>
    </tr>
<?
        }
        /******************************************************************************************/
        /********************************Representantes com Vendas*********************************/
        /******************************************************************************************/
        //SQL comum para qualquer uma das 3 condições abaixo - Listando todos os Representantes que tiveram vendas no Período ...
        $sql = "SELECT c.id_pais, r.id_representante, r.nome_fantasia, r.uf, r.ativo, SUM(IF(c.id_pais = '31', ((pvi.`qtde` - pvi.`qtde_devolvida`) * pvi.preco_liq_final), ((pvi.`qtde` - pvi.`qtde_devolvida`) * pvi.preco_liq_final * $valor_dolar_dia))) AS total_vendas 
                FROM `representantes` r 
                $inner_join 
                INNER JOIN pedidos_vendas_itens pvi on pvi.id_representante = r.id_representante 
                INNER JOIN pedidos_vendas pv on pv.id_pedido_venda = pvi.id_pedido_venda $condicao_livre_debito $condicao_expresso 
                INNER JOIN clientes c on c.id_cliente = pv.id_cliente 
                WHERE $condicao_data BETWEEN '$data_inicial' AND '$data_final' 
                AND pv.liberado = '1' $condicao 
                GROUP BY r.id_representante ORDER BY r.nome_fantasia ";
        $campos_rep             = bancos::sql($sql);
        $linhas                 = count($campos_rep);
        $id_representante_listados = 0;
        for ($i = 0; $i < $linhas; $i++) {
            $id_representante_listados.= $campos_rep[$i]['id_representante'].', ';
?>
    <tr class='linhanormal'>
        <td colspan='2' align='left'>
            <a href = 'rel_cotas_representantes.php?id_representante=<?=$campos_rep[$i]['id_representante'];?>&data_inicial=<?=$data_inicial;?>&data_final=<?=$data_final;?>' class='html5lightbox'>
            <?
                if($campos_rep[$i]['ativo'] == 1) {
                    echo $campos_rep[$i]['nome_fantasia'];
                }else {
                    echo "<font color='red'>".$campos_rep[$i]['nome_fantasia']." - (Inativo)</font>";
                }
            ?>
            </a>
        </td>
        <td align='center'>
            <?=$campos_rep[$i]['uf'];?>&nbsp;
        </td>
        <td>
        <?
            $sql = "SELECT r.nome_fantasia 
                    FROM `representantes` r 
                    INNER JOIN `representantes_vs_supervisores` rs ON rs.id_representante_supervisor = r.id_representante AND rs.id_representante = '".$campos_rep[$i]['id_representante']."' LIMIT 1 ";
            $campos_sup = bancos::sql($sql);
            echo $campos_sup[0]['nome_fantasia'].'&nbsp;';
        ?>
        </td>
        <td align='right'>
        <?
            $cota_total_do_periodo = vendas::cota_total_do_representante($campos_rep[$i]['id_representante'], $data_inicial, $data_final);
            echo number_format($cota_total_do_periodo, 2, ',', '.');
        ?>
        </td>
        <td align='right'>
            <?=number_format($campos_rep[$i]['total_vendas'], 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
            if($cota_total_do_periodo == 0) {
                echo "<font color='blue'>Sem Cota</font>";
            }else {
                if($cota_total_do_periodo == 0) $cota_total_do_periodo = 1;//Para não dar erro de Divisão por Zero ...
                $perc_cota = (($campos_rep[$i]['total_vendas'] / $cota_total_do_periodo) * 100);
                echo number_format($perc_cota, 1, ',', '.').'%';
            }
        ?>
        </td>
        <td align='right'>
<?
                $total_perc = ($campos_rep[$i]['total_vendas'] / $total_pedidos_emitidos) * 100;
                echo number_format($total_perc, 1, ',', '.').'%';
?>
        </td>
        <td align='right'>
        <?
            /*Independente do Período Filtrado pelo usuário, aqui sempre terá que retornar o Valor Diário, por isso 
            que divido pela "Qtde de Dias do Período Filtrado" ... */
            if($qtde_dias_periodo_filtrado <= 30) {
                $media_diaria = $cota_total_do_periodo / 30;//Se o período for inferior há um mês então divido por 30 que é a Qtde Mínima de dias em um Mês ...
            }else {
                $media_diaria = $cota_total_do_periodo / $qtde_dias_periodo_filtrado;
            }
            echo number_format($media_diaria, 2, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
            $valor_devido = $media_diaria * $qtde_dias_periodo_filtrado;
            echo number_format($valor_devido, 2, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
            $falta_media = $campos_rep[$i]['total_vendas'] - $valor_devido;
            if($falta_media < 0) {
                echo "<font color='red'>".number_format($falta_media, 2, ',', '.')."</font>";
            }else {
                echo '0,00';
            }
        ?>
        </td>
    </tr>
<?
            $total_cota_mensal_com_vendas+=     $cota_total_do_periodo;
            $total_vendas_mensal_todos+= 	$campos_rep[$i]['total_vendas'];
            $total_media_diaria_todos+= 	$media_diaria;
            $total_valor_devido_todos+= 	$valor_devido;
            $total_falta_para_media_todos =     $total_vendas_mensal_todos - $total_valor_devido_todos; 

            //Variáveis que serão apresentadas na última linha do relatório ...
            $total_cota_geral+=                 $cota_total_do_periodo;
            $total_vendas_geral+=               $campos_rep[$i]['total_vendas'];
            $total_media_diaria_geral+=         $media_diaria;
            $total_valor_devido_geral+=         $valor_devido;
        }
?>
    <tr class='linhanormaldestaque'>
        <td colspan='4'>
            <b>Totais =></b>
        </td>
        <td align='right'>
            <b><?=number_format($total_cota_mensal_com_vendas, 2, ',', '.')?></b>
        </td>
        <td align='right'>
            <b><?=number_format($total_vendas_mensal_todos, 2, ',', '.')?></b>
        </td>
        <td align='right'>
        <?
            if($total_vendas_mensal_todos != '0') {
                echo '<b>'.number_format(($total_vendas_mensal_todos / $total_cota_mensal_com_vendas) * 100, 2, ',', '.').'%</b>';
            }else {
                echo '<b>0,00 %</b>';
            }
        ?>
        </td>
        <td>
            &nbsp;
        </td>                
        <td align='right'>
            <b><?=number_format($total_media_diaria_todos, 2, ',', '.')?></b>
        </td>    
        <td align='right'>
            <b><?=number_format($total_valor_devido_todos, 2, ',', '.')?></b>
        </td>                  
        <td align='right'>
            <b><?=number_format($total_falta_para_media_todos, 2, ',', '.')?></b>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='11'>
            SEM VENDAS NO PERÍODO
        </td>
    </tr>
	
<?	
        /******************************************************************************************/
        /********************************Representantes sem Vendas*********************************/
        /******************************************************************************************/
        $id_representante_listados = substr($id_representante_listados, 0, (strlen($id_representante_listados) - 2));
        if(empty($id_representante_listados)) $id_representante_listados = (int)0;

        //SQL comum para qualquer uma das 3 condições abaixo - Listando todos os Representantes que não tiveram vendas no Período ...
        $sql = "SELECT r.id_representante, r.nome_fantasia, r.uf, r.ativo 
                FROM `representantes` r 
                $inner_join	
                WHERE r.`id_representante` NOT IN ($id_representante_listados, 0) 
                AND r.ativo = '1' $condicao 
                ORDER BY r.nome_fantasia ";
        $campos_rep = bancos::sql($sql);
        $linhas_rep = count($campos_rep);
        for($j = 0; $j < $linhas_rep; $j++) {
?>
    <tr class='linhanormal'>
        <td colspan='2'>
            <a href = 'rel_cotas_representantes.php?id_representante=<?=$campos_rep[$j]['id_representante'];?>&data_inicial=<?=$data_inicial;?>&data_final=<?=$data_final;?>' class='html5lightbox'>
                <?=$campos_rep[$j]['nome_fantasia'];?>
            </a>
        </td>
        <td align='center'>
            <?=$campos_rep[$j]['uf'];?>&nbsp;
        </td>
        <td>
        <?
            $sql = "SELECT r.nome_fantasia 
                    FROM `representantes` r 
                    INNER JOIN `representantes_vs_supervisores` rs ON rs.id_representante_supervisor = r.id_representante AND rs.id_representante = '".$campos_rep[$j]['id_representante']."' LIMIT 1 ";
            $campos_sup = bancos::sql($sql);
            echo $campos_sup[0]['nome_fantasia'].'&nbsp;';
        ?>
        </td>
        <td align='right'>
        <?
            $cota_total_do_periodo = vendas::cota_total_do_representante($campos_rep[$j]['id_representante'], $data_inicial, $data_final);
            echo number_format($cota_total_do_periodo, 2, ',', '.');
        ?>
        </td>
        <td align='right'>
            0,00
        </td>
        <td align='right'>
        <?
            if($cota_total_do_periodo == 0) {
                echo "<font color='blue'>Sem Cota</font>";
            }else {
                echo '-';
            }
        ?>
        </td>
        <td align='right'>
            0,0%
        </td>
        <td align='right'>
        <?
            /*Independente do Período Filtrado pelo usuário, aqui sempre terá que retornar o Valor Diário, por isso 
            que divido pela "Qtde de Dias do Período Filtrado" ... */
            if($qtde_dias_periodo_filtrado <= 30) {
                $media_diaria = $cota_total_do_periodo / 30;//Se o período for inferior há um mês então divido por 30 que é a Qtde Mínima de dias em um Mês ...
            }else {
                $media_diaria = $cota_total_do_periodo / $qtde_dias_periodo_filtrado;
            }
            echo number_format($media_diaria, 2, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
            $valor_devido = $media_diaria * $qtde_dias_periodo_filtrado;
            echo number_format($valor_devido, 2, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
            $falta_media = $campos_rep[$i]['total_vendas'] - $valor_devido;
            if($falta_media < 0) {
                echo "<font color='red'>".number_format($falta_media, 2, ',', '.')."</font>";
            }else {
                echo '0,00';
            }
        ?>
        </td>
    </tr>
<?
            $total_cota_mensal_sem_vendas+=         $cota_total_do_periodo;
            $total_media_diaria_sem_vendas+=        $media_diaria;
            $total_valor_devido_sem_vendas+=        $valor_devido;
            $total_falta_para_media_sem_vendas =    0 - $total_valor_devido_sem_vendas;

            //Variáveis que serão apresentadas na última linha do relatório ...
            $total_cota_geral+=                     $cota_total_do_periodo;
            $total_vendas_geral+=                   $campos_rep[$i]['total_vendas'];
            $total_media_diaria_geral+=             $media_diaria;
            $total_valor_devido_geral+=             $valor_devido;
        }
?>
    <tr class='linhanormaldestaque'>
        <td colspan='4'>
            <b>Totais =></b>
        </td>
        <td align='right'>
            <b><?=number_format($total_cota_mensal_sem_vendas, 2, ',', '.')?></b>
        </td>
        <td align='right'>
            <b>0,00</b>
        </td>
        <td align='right'>
            <b>0,00 %</b>
        </td>
        <td>
            &nbsp;
        </td>                
        <td align='right'>
            <b><?=number_format($total_media_diaria_sem_vendas, 2, ',', '.')?></b>
        </td>    
        <td align='right'>
            <b><?=number_format($total_valor_devido_sem_vendas, 2, ',', '.')?></b>
        </td>                  
        <td align='right'>
            <b><?=number_format($total_falta_para_media_sem_vendas, 2, ',', '.')?></b>
        </td>
    </tr>
<?
    }//Aqui eu fecho o Loop das 3 condições semelhantes ...
/******************************************************************************************/
/***************************************Supervisão*****************************************/
/******************************************************************************************/
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='4'>
            Supervisor(es)
        </td>
        <td>
            Cotas R$
        </td>
        <td>
            Vendas R$
        </td>
        <td>
            % sobre a(s) Cota(s)
        </td>
        <td>
            % sobre o(s) Total(is) de Venda(s)
        </td>
        <td>
            Média Diária R$
        </td>
        <td>
            Valor Devido R$
        </td>
        <td>
            Falta P/ Média R$
        </td>
    </tr>
<?
 	$sql = "SELECT r.id_representante, r.nome_fantasia, r.ativo 
                FROM `representantes` r 
                INNER JOIN `representantes_vs_funcionarios` rf ON rf.id_representante = r.id_representante 
                INNER JOIN `funcionarios` f ON f.id_funcionario = rf.id_funcionario 
                WHERE f.`id_cargo` IN (25, 109) ORDER BY r.nome_fantasia ";
	$campos_supervisor = bancos::sql($sql);
	for($i = 0; $i < count($campos_supervisor); $i++) {
            $vendas_supervisor = 0;
?>
    <tr class='linhanormal'>
        <td colspan='4'>
            <a href = 'rel_cotas_sup_representantes.php?id_representante=<?=$campos_supervisor[$i]['id_representante'];?>&data_inicial=<?=$data_inicial;?>&data_final=<?=$data_final;?>&dias_uteis_mes=<?=$qtde_dias_uteis_mes;?>&dias_uteis_ate_hoje=<?=$qtde_dias_uteis_ate_hoje?>' class='html5lightbox'>
            <?
                if($campos_supervisor[$i]['ativo'] == 1) {
                    echo $campos_supervisor[$i]['nome_fantasia'];
                }else {
                    echo "<font color='red'>".$campos_supervisor[$i]['nome_fantasia']." - (Inativo)</font>";
                }
            ?>
            </a>
        </td>
        <td align='right'>
        <?
            $cota_total_do_periodo = vendas::cota_total_do_representante($campos_supervisor[$i]['id_representante'], $data_inicial, $data_final, 'S');
            echo number_format($cota_total_do_periodo, 2, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
            //Pego as vendas dos representantes de acordo com o Período ...
            $sql = "SELECT r.id_representante, r.nome_fantasia, r.ativo, SUM(IF(c.id_pais = '31', ((pvi.`qtde` - pvi.`qtde_devolvida`) * pvi.preco_liq_final), ((pvi.`qtde` - pvi.`qtde_devolvida`) * pvi.preco_liq_final * $valor_dolar_dia))) AS total_vendas 
                    FROM pedidos_vendas pv 
                    INNER JOIN clientes c ON c.id_cliente = pv.id_cliente 
                    INNER JOIN pedidos_vendas_itens pvi ON pvi.id_pedido_venda = pv.id_pedido_venda 
                    INNER JOIN representantes r ON r.id_representante = pvi.id_representante 
                    INNER JOIN representantes_vs_supervisores rs ON rs.id_representante = r.id_representante and rs.id_representante_supervisor = '".$campos_supervisor[$i]['id_representante']."' 
                    WHERE $condicao_data BETWEEN '$data_inicial' and '$data_final' 
                    AND pv.liberado = '1' 
                    $condicao_livre_debito $condicao_expresso GROUP BY r.id_representante, c.id_pais ";
            $campos_vendas = bancos::sql($sql);
            $linhas_vendas = count($campos_vendas);
            //Disparo do Loop de tudo o que foi vendido do Supervisor ...
            for($j = 0; $j < $linhas_vendas; $j++) $vendas_supervisor+= $campos_vendas[$j]['total_vendas'];

            if($vendas_supervisor == 0) {
                echo "<font color='red'>".number_format($vendas_supervisor, 2, ',', '.')."</font>";
            }else {
                echo number_format($vendas_supervisor, 2, ',', '.');
            }
        ?>
        </td>
        <td align='right'>
        <?
            if($cota_total_do_periodo == 0) {
                echo "<font color='blue'>Sem Cota</font>";
            }else {
                if($cota_total_do_periodo == 0) $cota_total_do_periodo = 1;//Para não dar erro de Divisão por Zero ...
                $perc_cota = (($vendas_supervisor / $cota_total_do_periodo) * 100);
                echo number_format($perc_cota, 1, ',', '.').' %';
            }
        ?>
        </td>
        <td align='right'>
        <?
            $total_perc = ($vendas_supervisor / $total_pedidos_emitidos) * 100;
            echo number_format($total_perc, 1, ',', '.').' %';
        ?>
        </td>
        <td align='right'>
        <?
            /*Independente do Período Filtrado pelo usuário, aqui sempre terá que retornar o Valor Diário, por isso 
            que divido pela "Qtde de Dias do Período Filtrado" ... */
            if($qtde_dias_periodo_filtrado <= 30) {
                $media_diaria = $cota_total_do_periodo / 30;//Se o período for inferior há um mês então divido por 30 que é a Qtde Mínima de dias em um Mês ...
            }else {
                $media_diaria = $cota_total_do_periodo / $qtde_dias_periodo_filtrado;
            }
            echo number_format($media_diaria, 2, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
            $valor_devido = $media_diaria * $qtde_dias_periodo_filtrado;
            echo number_format($valor_devido, 2, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
            $falta_media = $vendas_supervisor - $valor_devido;
            if($falta_media < 0) {
                echo "<font color='red'>".number_format($falta_media, 2, ',', '.')."</font>";
            }else {
                echo '0,00';
            }
        ?>
        </td>
    </tr>
<?
            $total_cota_mensal_supervisor+=         $cota_total_do_periodo;
            $total_vendas_mensal_supervisor+=       $vendas_supervisor;
            $total_media_diaria_supervisor+=        $media_diaria;    
            $total_valor_devido_supervisor+=        $valor_devido;
            $total_falta_para_media_supervisor =    $total_vendas_mensal_supervisor - $total_valor_devido_supervisor;    
	}
?>
    <tr class='linhanormaldestaque'>
        <td colspan='4'>
            <b>Totais =></b>
        </td>
        <td align='right'>
            <b><?=number_format($total_cota_mensal_supervisor, 2, ',', '.')?></b>
        </td>
        <td align='right'>
            <b><?=number_format($total_vendas_mensal_supervisor, 2, ',', '.')?></b>
        </td>
        <td align='right'>
        <?
            if($total_vendas_mensal_supervisor != '0') {
                echo '<b>'.number_format(($total_vendas_mensal_supervisor / $total_cota_mensal_supervisor) * 100, 2, ',', '.').'%</b>';
            }else {
                echo '<b>0,00 %</b>';
            }
        ?>
        </td>
        <td>
            &nbsp;
        </td>                
        <td align='right'>
            <b><?=number_format($total_media_diaria_supervisor, 2, ',', '.')?></b>
        </td>     
        <td align='right'>
            <b><?=number_format($total_valor_devido_supervisor, 2, ',', '.')?></b>
        </td>                  
        <td align='right'>
            <b><?=number_format($total_falta_para_media_supervisor, 2, ',', '.')?></b>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='4'>
            <b>Total Geral =></b>
        </td>
        <td align='right'>
            <b><?=number_format($total_cota_geral, 2, ',', '.')?></b>
        </td>
        <td align='right'>
            <b><?=number_format($total_vendas_geral, 2, ',', '.')?></b>
        </td>
        <td align='right'>
        <?
            if($total_vendas_geral != '0') {
                echo '<b>'.number_format(($total_vendas_geral / $total_cota_geral) * 100, 1, ',', '.').'%</b>';
            }else {
                echo '<b>0,00 %</b>';
            }
        ?>
        </td>
        <td>
            &nbsp;
        </td>                
        <td align='right'>
            <b><?=number_format($total_media_diaria_geral, 2, ',', '.')?></b>
        </td>      
        <td align='right'>
            <b><?=number_format($total_valor_devido_geral, 2, ',', '.')?></b>
        </td>                  
        <td align='right'>
            <b>
            <?
                if($total_vendas_geral - $total_valor_devido_geral < 0) {                                    
                    echo '<font color="red">'.number_format($total_vendas_geral - $total_valor_devido_geral, 2, ',', '.').'</font>';
                }else {
                    echo number_format($total_vendas_geral - $total_valor_devido_geral, 2, ',', '.');
                }
            ?>
            </b>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='5'>
            Previsão =>
        </td>
        <td align='right'>
            <?=number_format($total_vendas_geral / $qtde_dias_uteis_ate_hoje * number_format(genericas::variavel(24), 0), 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($total_vendas_geral / $qtde_dias_uteis_ate_hoje * number_format(genericas::variavel(24), 0) / $total_cota_geral * 100, 1, ',', '.').'%';?>
        </td>
        <td>
            &nbsp;
        </td> 
        <td align='right'>
            <?=number_format($total_vendas_geral / $qtde_dias_uteis_ate_hoje * number_format(genericas::variavel(24), 0) / number_format(genericas::variavel(24), 0), 2, ',', '.');?>
        </td>     
        <td>
        </td>  
        <td align='right'>
            <?=number_format($total_cota_geral - $total_vendas_geral / $qtde_dias_uteis_ate_hoje * number_format(genericas::variavel(24), 0), 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            <input type='submit' name='cmd_atualizar' value='Atualizar Relatório' title='Atualizar Relatório' class='botao'>
        </td>
    </tr>
</body>
</html>
<?}?>