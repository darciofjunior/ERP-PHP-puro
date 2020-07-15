<?
if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
    $condicao_data                              = ($_POST['cmb_tipo_data'] == 'emissao') ? 'pv.`data_emissao`' : 'pv.`faturar_em`';
    
    if($_POST['chkt_livre_debito'] == 'S')      $condicao_livre_debito  = " AND pv.`livre_debito` = 'S' ";
    if($_POST['chkt_expresso'] == 'S')          $condicao_expresso      = " AND pv.`expresso` = 'S' ";
    
    $cmb_id_representante                       = (empty($_POST['cmb_id_representante'])) ? '%' : $_POST['cmb_id_representante'];
    $busca_uf                                   = (!empty($_POST['cmb_id_uf'])) ? " INNER JOIN `ufs` ON ufs.`id_uf` = c.`id_uf` AND ufs.`id_uf` LIKE '$cmb_id_uf'" : '';
    
    if(!empty($_POST['chkt_melhores_clientes'])) {
        $group_by                               = " pv.`id_cliente`, ed.`id_empresa_divisao` ";
        $order_by                               = " ed.`id_empresa_divisao`, total_do_pedido_por_divisao DESC ";
    }else {
        $group_by                               = " pv.`id_pedido_venda`, ed.`id_empresa_divisao` ";
        $order_by                               = (!empty($_POST['chkt_ordenar_cliente'])) ? ' c.`razaosocial`, pv.`id_pedido_venda` DESC ' : ' ed.`razaosocial`, pv.`id_pedido_venda` DESC ';
    }
    
    $sql = "SELECT DISTINCT(pv.`id_pedido_venda`), ed.`razaosocial` AS razaosocial_divisao, 
            ed.`id_empresa_divisao`, pv.`id_pedido_venda`, pv.`id_empresa`, pv.`finalidade`, 
            pv.`data_emissao`, pv.`faturar_em`, c.`id_pais`, c.`id_cliente`, 
            CONCAT(c.`razaosocial`, ' (', c.`nomefantasia`,')') AS cliente, 
            pv.`vencimento1`, pv.`vencimento2`, pv.`vencimento3`, pv.`vencimento4`, 
            pv.`comissao_media`,  pv.`valor_dolar`, pv.`expresso`, 
            SUM((pvi.`qtde` - pvi.`qtde_devolvida`) * pvi.`preco_liq_final`) AS total_do_pedido_por_divisao, 
            (SUM((pvi.`qtde` - pvi.`qtde_devolvida`) * pvi.preco_liq_final) / SUM(((pvi.`qtde` - pvi.`qtde_devolvida`) * pvi.`preco_liq_final`) / (1 + pvi.`margem_lucro` / 100)) - 1) * 100 AS mg_l_m_g 
            FROM `produtos_acabados` pa 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_produto_acabado` = pa.`id_produto_acabado` 
            INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pvi.`id_representante` LIKE '$cmb_id_representante' $condicao_livre_debito $condicao_expresso 
            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
            $busca_uf 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
            WHERE $condicao_data BETWEEN '$data_inicial' AND '$data_final' AND pv.`liberado` = '1' 
            GROUP BY $group_by ORDER BY $order_by ";
    $campos_nac = bancos::sql($sql);
    $linhas_nac = count($campos_nac);
}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <b>Representante:</b>
            <select name='cmb_id_representante' title='Selecione um Estado' class='combo'>
            <?
                $sql = "SELECT `id_representante`, `nome_fantasia` 
                        FROM `representantes` 
                        WHERE `ativo` = '1' ORDER BY `nome_fantasia` ";
                echo combos::combo($sql, $cmb_id_representante);
            ?>
            </select>
        </td>
        <td>
            <b>Estado:</b>
            <select name='cmb_id_uf' title='Selecione um Estado' class='combo'>
            <?
                $sql = "SELECT `id_uf`, `sigla` 
                        FROM `ufs` 
                        WHERE `ativo` = '1' ORDER BY `sigla` ";
                echo combos::combo($sql, $cmb_id_uf);
            ?>
            </select>
        </td>
        <td colspan='2'>
            <?
                $checked = (!empty($_POST['chkt_melhores_clientes'])) ? 'checked' : '';
            ?>
            <input type='checkbox' name='chkt_melhores_clientes' id='chkt_melhores_clientes' value='S' onclick='document.form.submit()' class='checkbox' <?=$checked;?>>
            <label for='chkt_melhores_clientes'>
                Melhores Clientes
            </label>
        </td>
        <td colspan='3'>
            <?
                $checked = (!empty($_POST['chkt_ordenar_cliente'])) ? 'checked' : '';
            ?>
            <input type='checkbox' name='chkt_ordenar_cliente' id='chkt_ordenar_cliente' value='S' onclick='document.form.submit()' class='checkbox' <?=$checked;?>>
            <label for='chkt_ordenar_cliente'>
                Ordenar por Cliente
            </label>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Divis&atilde;o
        </td>
        <td>
            N&ordm; do Pedido
        </td>
        <td>
            Data de Emiss&atilde;o
        </td>
        <td>
            Faturar Em
        </td>
        <td>
            Cliente
        </td>
        <td>
            Representante
        </td>
        <td>
            Forma de Pagamento
        </td>
        <td>
            Total R$
        </td>
        <td title='Margem de Lucro Média Gravada' style='cursor:help'>
            M.L.M.G.
        </td>
        <td title='Comissão Média Gravada' style='cursor:help'>
            Com. M.G.
        </td>
    </tr>
<?
    for($j = 0; $j < $linhas_nac; $j++) {
        $total_do_pedido_por_divisao  = ($campos_nac[$j]['id_pais'] != 31) ? $campos_nac[$j]['total_do_pedido_por_divisao'] * $campos_nac[$j]['valor_dolar'] : $campos_nac[$j]['total_do_pedido_por_divisao'];
        
        //Total por Empresa Divisão ...
        $vetor_emp_div_mlmg[$campos_nac[$j]['id_empresa_divisao']]+= $total_do_pedido_por_divisao;
        $vetor_emp_div_com_mg[$campos_nac[$j]['id_empresa_divisao']]+= $total_do_pedido_por_divisao;
        
        //Total por Empresa ...
        if($campos_nac[$j]['id_empresa'] != 4) {//Albafer ou Tool Master ...
            $vetor_tipo_nf['NF']+= $total_do_pedido_por_divisao;
        }else {
            $vetor_tipo_nf['SGD']+= $total_do_pedido_por_divisao;
        }
        
        //Independente de Empresa ou Empresa Divisão ...
        $total_geral+= $total_do_pedido_por_divisao;
        /*******************************************************************************************/
        /***********************************Ordenação por Cliente***********************************/
        /*******************************************************************************************/
        //Somente quando essa opção estiver "DESMARCADA", então eu mostro o Total de Venda por Divisão ...
        if(empty($_POST['chkt_ordenar_cliente'])) {
            //Se a Empresa Divisão atual é diferente da Empresa Divisão anterior mostra uma linha com o valor Total da Anterior ...
            if(($campos_nac[$j]['id_empresa_divisao'] != $campos_nac[$j - 1]['id_empresa_divisao']) && $j != 0) {//Só não mostro a 1ª linha ...
                $mlmg_total_por_emp_div     = (round($vetor_emp_div_mlmg[$campos_nac[$j - 1]['id_empresa_divisao']], 2) / round($custo_mlmg_zero[$campos_nac[$j - 1]['id_empresa_divisao']], 2) - 1) * 100;
                $com_mg_total_por_emp_div   = (round($vetor_emp_div_com_mg[$campos_nac[$j - 1]['id_empresa_divisao']], 2) / round($com_mg_zero[$campos_nac[$j - 1]['id_empresa_divisao']], 2) - 1) * 100;
?>
    <tr class='linhadestaque' align='right'>
        <td colspan='8'>
            Total da <?=$campos_nac[$j - 1]['razaosocial_divisao'];?> : <?=number_format($vetor_emp_div_mlmg[$campos_nac[$j - 1]['id_empresa_divisao']], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($mlmg_total_por_emp_div, 2, ',', '.');?> %
        </td>
        <td align='right'>
            <?=number_format($com_mg_total_por_emp_div, 2, ',', '.');?> %
        </td>
    </tr>
<?
            }
        }
        /*******************************************************************************************/
?>
    <tr class='linhanormal'>
        <td>
            <?=$campos_nac[$j]['razaosocial_divisao'];?>
        </td>
        <td>
        <?
            echo $campos_nac[$j]['id_pedido_venda'];
            if($campos_nac[$j]['expresso'] == 'S') echo '<font color="brown"><b>(EXPRESSO)</b></font>';
        ?>
        </td>
        <td align='center'>
            <?=data::datetodata($campos_nac[$j]['data_emissao'], '/');?>
        </td>
        <td align='center'>
            <?=data::datetodata($campos_nac[$j]['faturar_em'], '/');?>
        </td>
        <td>
            <a href='rel_pedido_cliente.php?id_cliente=<?=$campos_nac[$j]['id_cliente'];?>&data_inicial=<?=$data_inicial;?>&data_final=<?=$data_final;?>' class='html5lightbox'>
                <?=$campos_nac[$j]['cliente'];?>
            </a>
        </td>
        <td align='center'>
        <?
            //Aqui eu busco o Representante do Cliente e Empresa Divisão do Loop atual ...
            $sql = "SELECT r.`nome_fantasia` 
                    FROM `clientes_vs_representantes` cr 
                    INNER JOIN `representantes` r ON r.`id_representante` = cr.`id_representante` 
                    WHERE cr.`id_cliente` = '".$campos_nac[$j]['id_cliente']."' 
                    AND cr.`id_empresa_divisao` = '".$campos_nac[$j]['id_empresa_divisao']."' LIMIT 1 ";
            $campos_representante = bancos::sql($sql);
            echo $campos_representante[0]['nome_fantasia'];
        ?>
        </td>
        <td>
        <?
            $prazo_faturamento = '';
            if($campos_nac[$j]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos_nac[$j]['vencimento4'];
            if($campos_nac[$j]['vencimento3'] > 0) $prazo_faturamento= '/'.$campos_nac[$j]['vencimento3'].$prazo_faturamento;
            if($campos_nac[$j]['vencimento2'] > 0) {
                $prazo_faturamento= $campos_nac[$j]['vencimento1'].'/'.$campos_nac[$j]['vencimento2'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos_nac[$j]['vencimento1'] == 0) ? 'À vista' : $campos_nac[$j]['vencimento1'];
            }
            if($campos_nac[$j]['id_empresa'] == 4) {//Empresa Grupo ...
                $rotulo_sgd = ' - SGD';
            }else {//Empresa NF ...
                $rotulo_sgd = ' - NF';
//Somente quando a nota é do Tipo NF q existe existe, consequentemente verifico a Finalidade ...
                if($campos_nac[$j]['finalidade'] == 'C') {
                    $finalidade = 'CONSUMO';
                }else if($campos_nac[$j]['finalidade'] == 'I') {
                    $finalidade = 'INDUSTRIALIZAÇÃO';
                }else {
                    $finalidade = 'REVENDA';
                }
                $rotulo_sgd.= '/'.$finalidade;
            }
            $prazo_faturamento.= $rotulo_sgd;
            echo $prazo_faturamento;
        ?>
        </td>
        <td align='right'>
            <?=number_format($total_do_pedido_por_divisao, 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($campos_nac[$j]['mg_l_m_g'], 1, ',', '.');?> %
        </td>
        <td align='right'>
            <?=number_format($campos_nac[$j]['comissao_media'], 1, ',', '.');?> %
        </td>
    </tr>
<?
        /***************************************************************************/
        /**************Nascimento das Variáveis M.L.M.G e Comissão M.G**************/
        /***************************************************************************/
        //Aqui nessa linha é que se criam as variáveis $custo_mlmg_zero e $comissao_zero ...
        if($total_do_pedido_por_divisao != 0 && $campos_nac[$j]['mg_l_m_g'] != 0) {
            $custo_mlmg_zero[$campos_nac[$j]['id_empresa_divisao']]+= ($total_do_pedido_por_divisao / (1 + $campos_nac[$j]['mg_l_m_g'] / 100));
            
            //Total por Empresa ...
            if($campos_nac[$j]['id_empresa'] != 4) {//Albafer ou Tool Master ...
                $custo_mlmg_zero_tipo_nf['NF']+= ($total_do_pedido_por_divisao / (1 + $campos_nac[$j]['mg_l_m_g'] / 100));
            }else {
                $custo_mlmg_zero_tipo_nf['SGD']+= ($total_do_pedido_por_divisao / (1 + $campos_nac[$j]['mg_l_m_g'] / 100));
            }
        }
        if($total_do_pedido_por_divisao != 0 && $campos_nac[$j]['comissao_media'] != 0) $com_mg_zero[$campos_nac[$j]['id_empresa_divisao']]+= ($total_do_pedido_por_divisao / (1 + $campos_nac[$j]['comissao_media'] / 100));
        /****************************************************************************/
    }
    /*******************************************************************************************/
    /***********************************Ordenação por Cliente***********************************/
    /*******************************************************************************************/
    //Somente quando essa opção estiver "DESMARCADA", então eu mostro o Total de Venda por Divisão ...
    if(empty($_POST['chkt_ordenar_cliente'])) {
        //Aqui são valores que serão apresentandos para a última linha exclusivamente da última Divisão ...
        if($custo_mlmg_zero[$campos_nac[$j - 1]['id_empresa_divisao']] != 0) $mlmg_total_por_emp_div = (round($vetor_emp_div_mlmg[$campos_nac[$j - 1]['id_empresa_divisao']], 2) / round($custo_mlmg_zero[$campos_nac[$j - 1]['id_empresa_divisao']], 2) -1) * 100;
        if($com_mg_zero[$campos_nac[$j - 1]['id_empresa_divisao']] != 0) $com_mg_total_por_emp_div = (round($vetor_emp_div_com_mg[$campos_nac[$j - 1]['id_empresa_divisao']], 2) / round($com_mg_zero[$campos_nac[$j - 1]['id_empresa_divisao']], 2) -1) * 100;
?>
    <tr class='linhadestaque' align='right'>
        <td colspan='8'>
            Total da <?=$campos_nac[$j - 1]['razaosocial_divisao'];?> : <?=number_format($vetor_emp_div_mlmg[$campos_nac[$j - 1]['id_empresa_divisao']], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($mlmg_total_por_emp_div, 2, ',', '.');?> %
        </td>
        <td align='right'>
            <?=number_format($com_mg_total_por_emp_div, 2, ',', '.');?> %
        </td>
    </tr>
<?
    }
        /**********************************************************************/
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='6'>
            Divisões
        </td>
        <td>
            % Sobre o Total
        </td>
        <td>
            Totais R$
        </td>
        <td title='Margem de Lucro Média Gravada' style='cursor:help'>
            M.L.M.G.
        </td>
        <td title='Lucro Reais'>
            Lucro Reais
        </td>
    </tr>
<?
    $sql = "SELECT ed.`id_empresa`, ed.`id_empresa_divisao`, ed.`razaosocial`, e.`nomefantasia` 
            FROM `empresas_divisoes` ed 
            INNER JOIN `empresas` e ON e.`id_empresa` = ed.`id_empresa` 
            WHERE ed.`ativo` = '1' ORDER BY ed.`razaosocial` ";
    $campos_empresas_divisoes = bancos::sql($sql);
    $linhas_empresas_divisoes = count($campos_empresas_divisoes);
    for($i = 0; $i < $linhas_empresas_divisoes; $i++) {
?>
    <tr class='linhanormal'>
        <td colspan='6'>
            <font color='green'>
                <?=$campos_empresas_divisoes[$i]['razaosocial'];?>
            </font>
        </td>
        <td align='right'>
            <?=number_format(($vetor_emp_div_mlmg[$campos_empresas_divisoes[$i]['id_empresa_divisao']] / $total_geral * 100), 2, ',', '.');?> %
        </td>
        <td align='right'>
            <?=number_format($vetor_emp_div_mlmg[$campos_empresas_divisoes[$i]['id_empresa_divisao']], 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
            if($custo_mlmg_zero[$campos_empresas_divisoes[$i]['id_empresa_divisao']] != 0) {
                $mlmg_tot_empresa_divisao = (round($vetor_emp_div_mlmg[$campos_empresas_divisoes[$i]['id_empresa_divisao']], 2) / round($custo_mlmg_zero[$campos_empresas_divisoes[$i]['id_empresa_divisao']], 2) - 1) * 100;
                echo number_format($mlmg_tot_empresa_divisao,2, ',', '.').' %';
            }
        ?>
        </td>
        <td align='right'>
        <?
            $lucro_reais = round($vetor_emp_div_mlmg[$campos_empresas_divisoes[$i]['id_empresa_divisao']], 2) - $custo_mlmg_zero[$campos_empresas_divisoes[$i]['id_empresa_divisao']];
            echo number_format($lucro_reais, 2, ',', '.');
        ?>
        </td>
    </tr>
<?
        if($campos_empresas_divisoes[$i]['id_empresa'] == 1) {//Albafer ...
            $total_albafer+=            $vetor_emp_div_mlmg[$campos_empresas_divisoes[$i]['id_empresa_divisao']];
            $custo_mlmg_zero_albafer+=  $custo_mlmg_zero[$campos_empresas_divisoes[$i]['id_empresa_divisao']];
            $lucro_reais_albafer+=      $lucro_reais;
        }else {//Tool Master ...
            $total_tool_master+=            $vetor_emp_div_mlmg[$campos_empresas_divisoes[$i]['id_empresa_divisao']];
            $custo_mlmg_zero_tool_master+=  $custo_mlmg_zero[$campos_empresas_divisoes[$i]['id_empresa_divisao']];
            $lucro_reais_tool_master+=  $lucro_reais;
        }
    }
    $mlmg_total_zero_albafer        = ($total_albafer / $custo_mlmg_zero_albafer - 1) * 100;
    $mlmg_total_zero_tool_master    = ($total_tool_master / $custo_mlmg_zero_tool_master - 1) * 100;
    
    /*Esse "1º) Total" e "2º) Total" que estou apresentando utilizam a mesma fórmula das variáveis que foram abastecidas no loop acima, 
    porque ambos vêm da mesma idéia que é por Empresa Divisão ...*/
    
    //1º) Total ...
?>
    <tr class='linhanormal' align='right'>
        <td colspan='8'>
            <font color='red' size='2'>
                <b>Total Geral R$ </b><?=number_format($total_geral, 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font color='red' size='2'>
            <?
                $mlmg_tot_zero_geral = $total_albafer / (1 + $mlmg_total_zero_albafer / 100) + $total_tool_master / (1 + $mlmg_total_zero_tool_master / 100);
                if($mlmg_tot_zero_geral != 0) {
                    $mlmg_tot_geral =(round($total_geral, 2) / $mlmg_tot_zero_geral - 1) * 100;
                    echo number_format($mlmg_tot_geral, 2, ',', '.').'%';
                }
            ?>
            </font>
        </td>
        <td>
            <font color='red' size='2'>
                <?=number_format($lucro_reais_albafer + $lucro_reais_tool_master, 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <?
        /**********************************************************************/
    ?>
    <tr class='linhadestaque' align='center'>
        <td colspan='6'>
            Empresas
        </td>
        <td>
            Porcentagens Sobre o Total
        </td>
        <td>
            Totais R$
        </td>
        <td title='Margem de Lucro Média Gravada' style='cursor:help'>
            M.L.M.G.
        </td>
        <td>
            Lucro Reais
        </td>
    </tr>
    <tr class='linhanormal' align='right'>
        <td colspan='6' align='left'>
            <font color='green'>ALBAFER</font>
        </td>
        <td>
            <?=number_format($total_albafer / $total_geral * 100, 2, ',', '.');?> %
        </td>
        <td>
            <?=number_format($total_albafer, 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($mlmg_total_zero_albafer, 2, ',', '.').'%';?>
        </td>
        <td>
            <?=number_format($lucro_reais_albafer, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal' align='right'>
        <td colspan='6' align='left'>
            <font color='green'>
                TOOL MASTER
            </font>
        </td>
        <td>
            <?=number_format($total_tool_master / $total_geral * 100, 2, ',', '.');?> %
        </td>
        <td>
            <?=number_format($total_tool_master, 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($mlmg_total_zero_tool_master, 2, ',', '.').'%';?>
        </td>
        <td>
            <?=number_format($lucro_reais_tool_master, 2, ',', '.');?>
        </td>
    </tr>
<?
    //2º) Total ...
?>
    <tr class='linhanormal' align='right'>
        <td colspan='8'>
            <font color='red' size='2'>
                <b>Total Geral R$ </b><?=number_format($total_geral, 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font color='red' size='2'>
            <?
                $mlmg_tot_zero_geral = $total_albafer / (1 + $mlmg_total_zero_albafer / 100) + $total_tool_master / (1 + $mlmg_total_zero_tool_master / 100);
                if($mlmg_tot_zero_geral != 0) {
                    $mlmg_tot_geral =(round($total_geral, 2) / $mlmg_tot_zero_geral - 1) * 100;
                    echo number_format($mlmg_tot_geral, 2, ',', '.').'%';
                }
            ?>
            </font>
        </td>
        <td>
            <font color='red' size='2'>
                <?=number_format($lucro_reais_albafer + $lucro_reais_tool_master, 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <?
        /**********************************************************************/
        $mlmg_tot_tipo_nf['NF']         = (round($vetor_tipo_nf['NF'], 2) / round($custo_mlmg_zero_tipo_nf['NF'], 2) - 1) * 100;
        $lucro_reais_tipo_nf['NF']      = round($vetor_tipo_nf['NF'], 2) - $custo_mlmg_zero_tipo_nf['NF'];

        $mlmg_tot_tipo_nf['SGD']        = (round($vetor_tipo_nf['SGD'], 2) / round($custo_mlmg_zero_tipo_nf['SGD'], 2) - 1) * 100;
        $lucro_reais_tipo_nf['SGD']     = round($vetor_tipo_nf['SGD'], 2) - $custo_mlmg_zero_tipo_nf['SGD'];
    ?>
    <tr class='linhadestaque' align='center'>
        <td colspan='6'>
            Tipo NF
        </td>
        <td>
            Porcentagens Sobre o Total
        </td>
        <td>
            Totais R$
        </td>
        <td title='Margem de Lucro Média Gravada' style='cursor:help'>
            M.L.M.G.
        </td>
        <td>
            Lucro Reais
        </td>
    </tr>
    <tr class='linhanormal' align='right'>
        <td colspan='6' align='left'>
            <font color='green'>NF</font>
        </td>
        <td>
            <?=number_format($vetor_tipo_nf['NF'] / $total_geral * 100, 2, ',', '.');?> %
        </td>
        <td>
            <?=number_format($vetor_tipo_nf['NF'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($mlmg_tot_tipo_nf['NF'], 2, ',', '.').'%';?>
        </td>
        <td>
            <?=number_format($lucro_reais_tipo_nf['NF'], 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal' align='right'>
        <td colspan='6' align='left'>
            <font color='green'>
                SGD
            </font>
        </td>
        <td>
            <?=number_format($vetor_tipo_nf['SGD'] / $total_geral * 100, 2, ',', '.');?> %
        </td>
        <td>
            <?=number_format($vetor_tipo_nf['SGD'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($mlmg_tot_tipo_nf['SGD'], 2, ',', '.').'%';?>
        </td>
        <td>
            <?=number_format($lucro_reais_tipo_nf['SGD'], 2, ',', '.');?>
        </td>
    </tr>
<?
    //3º) Total ...
?>
    <tr class='linhanormal' align='right'>
        <td colspan='8'>
            <font color='red' size='2'>
                <b>Total Geral R$ </b><?=number_format($total_geral, 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font color='red' size='2'>
            <?
                $mlmg_tot_zero_geral = $vetor_tipo_nf['NF'] / (1 + $mlmg_tot_tipo_nf['NF'] / 100) + $vetor_tipo_nf['SGD'] / (1 + $mlmg_tot_tipo_nf['SGD'] / 100);
                if($mlmg_tot_zero_geral != 0) {
                    $mlmg_tot_geral =(round($total_geral, 2) / $mlmg_tot_zero_geral - 1) * 100;
                    echo number_format($mlmg_tot_geral, 2, ',', '.').'%';
                }
            ?>
            </font>
        </td>
        <td>
            <font color='red' size='2'>
                <?=number_format($lucro_reais_tipo_nf['NF'] + $lucro_reais_tipo_nf['SGD'], 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='10'>
            <font color='yellow'>
                Valor Dólar dia R$: 
            </font>
            <?=number_format($valor_dolar_dia, 4, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            <input type='submit' name='cmd_atualizar' title='Atualizar Relatório' value='Atualizar Relatório' class='botao'>
        </td>
    </tr>
</table>
</body>
</html>