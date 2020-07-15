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
        Qtde de Pedido(s)
    </td>
    <td>
        Prêmio<br/><?=$mes_ref_sg;?>
    </td>
</tr>
<?
if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
    $condicao_data = ($_POST['cmb_tipo_data'] == 'emissao') ? 'pv.`data_emissao`' : 'pv.`faturar_em`';
    if($_POST['chkt_livre_debito'] == 'S')  $condicao_livre_debito  = " AND pv.`livre_debito` = 'S' ";
    if($_POST['chkt_expresso'] == 'S')      $condicao_expresso = " AND pv.`expresso` = 'S' ";
    
    //Busca de Vendas em R$ ...
    $valor_dolar_dia = genericas::moeda_dia('dolar');
    $mes_ref = date('m');
    $ano_ref = date('Y');
    
    $sql = "SELECT `id_representante`, `comissao_meta_atingida`, `comissao_meta_atingida_sup` 
            FROM `comissoes_extras` 
            WHERE MONTH(`data_periodo_fat`) = '$mes_ref' 
            AND YEAR(`data_periodo_fat`) = '$ano_ref' ";
    $campos_perc_extra = bancos::sql($sql);
    $linhas_perc_extra = count($campos_perc_extra);
    for($i = 0; $i < $linhas_perc_extra; $i++) {
        $comissao_meta_atingida_array[$campos_perc_extra[$i]['id_representante']] = $campos_perc_extra[$i]['comissao_meta_atingida'];
        $comissao_meta_atingida_sup_array[$campos_perc_extra[$i]['id_representante']] = $campos_perc_extra[$i]['comissao_meta_atingida_sup'];
    }
    $sql = "SELECT rs.`id_representante`, r.`nome_fantasia` 
            FROM `representantes` r 
            INNER JOIN `representantes_vs_supervisores` rs ON rs.`id_representante_supervisor` = r.`id_representante` ";
    $campos_sup = bancos::sql($sql);
    $linhas_sup = count($campos_sup);
    for($i = 0; $i < $linhas_sup; $i++) $supervisor_rep_array[$campos_sup[$i]['id_representante']] = $campos_sup[$i]['nome_fantasia'];
    
    $sql = "SELECT c.`id_pais`, r.`id_representante`, r.`nome_fantasia`, r.`uf`, r.`ativo`, 
            SUM(IF(c.`id_pais` = '31', ((pvi.`qtde` - pvi.`qtde_devolvida`) * pvi.`preco_liq_final`), ((pvi.`qtde` - pvi.`qtde_devolvida`) * pvi.`preco_liq_final` * $valor_dolar_dia))) AS total_vendas, 
            COUNT(DISTINCT(pv.`id_pedido_venda`)) AS total_pedido 
            FROM `representantes` r 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_representante` = r.`id_representante` 
            INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` $condicao_livre_debito $condicao_expresso 
            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
            WHERE $condicao_data BETWEEN '$data_inicial' AND '$data_final' 
            AND pv.`liberado` = '1' 
            GROUP BY r.`id_representante` ORDER BY r.`nome_fantasia` ";
    $campos_rep                 = bancos::sql($sql);
    $linhas                     = count($campos_rep);
    $difereca_mes               = data::diferenca_data($data_inicial, $data_final);//Retorna em dias ...
    $difereca_mes               = (integer)$difereca_mes[0];
    $id_representante_listados  = 0;
?>
    <tr class='linhacabecalho'>
        <td colspan='9'>
            Grupo Albafer
        </td>
    </tr>
<?
    for ($i = 0; $i < $linhas; $i++) {
        $id_representante_listados.= $campos_rep[$i]['id_representante'].',';
?>
    <tr class='linhanormal'>
        <td colspan='2' align='left'>
            <a href="javascript:nova_janela('rel_cotas_representantes.php?id_representante=<?=$campos_rep[$i]['id_representante'];?>&data_inicial=<?=$data_inicial;?>&data_final=<?=$data_final;?>', 'CONSULTAR', '', '', '', '', '350', '750', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
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
            <?=$supervisor_rep_array[$campos_rep[$i]['id_representante']];?>&nbsp;
        </td>
        <td align='right'>
        <?
            $cota_total_do_periodo = vendas::cota_total_do_representante($campos_rep[$i]['id_representante'], $data_inicial, $data_final);
            echo number_format($cota_total_do_periodo, 2, ',', '.');

            $total_geral_cotas+= $cota_total_do_periodo;
            if($cota_total_do_periodo == 0 || $cota_total_do_periodo == 0.00) $cota_total_do_periodo = 1;
        ?>
        </td>
        <td align='right'>
        <?
            echo number_format($campos_rep[$i]['total_vendas'], 2, ',', '.');
            $total_geral+= $campos_rep[$i]['total_vendas'];
        ?>
        </td>
        <td align='right'>
        <?
            if($cota_total_do_periodo == 1) {
                echo "<font color='blue'>Sem Cota</font>";
            }else {
                $perc_cota = (($campos_rep[$i]['total_vendas'] / $cota_total_do_periodo) * 100);
                echo number_format($perc_cota, 2, ',', '.').'%';
                $tot_perc_cota+= $perc_cota;
            }
        ?>
        </td>
        <td align='center'>
        <?
            echo "<font title='Quantidade Total de Pedido dentro do Período' style='cursor:help'>".$campos_rep[$i]['total_pedido']."</font>";
            //Busca quem é o Funcionário que representa o representante em questão ...
            $sql = "SELECT id_funcionario 
                    FROM `representantes_vs_funcionarios` 
                    WHERE `id_representante` = '".$campos_rep[$i]['id_representante']."' LIMIT 1 ";
            $campos_funcionario = bancos::sql($sql);
            if(count($campos_funcionario) == 1) {
                $sql = "SELECT COUNT(DISTINCT(pvi.id_pedido_venda)) AS pedidos_do_representante 
                        FROM `representantes` r 
                        INNER JOIN pedidos_vendas_itens pvi on pvi.id_representante = r.id_representante AND pvi.id_representante = '".$campos_rep[$i]['id_representante']."' 
                        INNER JOIN pedidos_vendas pv on pv.id_pedido_venda = pvi.id_pedido_venda AND pvi.id_funcionario = '".$campos_funcionario[0]['id_funcionario']."' $condicao_livre_debito $condicao_expresso 
                        WHERE $condicao_data BETWEEN '$data_inicial' AND '$data_final' 
                        AND pv.liberado = '1' ";
                $campos_pedidos_do_representante = bancos::sql($sql);
                echo " / <font title='Qtde de Pedido feito pelo próprio Vendedor' style='cursor:help'>".$campos_pedidos_do_representante[0]['pedidos_do_representante']."</font>";
            }
        ?>
        </td>
        <td align='right'>
            &nbsp;
        <?
            if($comissao_meta_atingida_array[$campos_rep[$i]['id_representante']] != 0) echo segurancas::number_format($comissao_meta_atingida_array[$campos_rep[$i]['id_representante']], ',', '.').'% ';
        ?>
        </td>
    </tr>
<?
    }
    
    if($total_geral_cotas == 0) $total_geral_cotas = 1;
    $id_representante_listados = substr($id_representante_listados, 0, (strlen($id_representante_listados) - 1));
    if(empty($id_representante_listados)) $id_representante_listados = (int)0;
    
    $sql = "SELECT `id_representante`, `nome_fantasia`, `uf`, `ativo` 
            FROM `representantes` 
            WHERE `id_representante` NOT IN ($id_representante_listados, 0) 
            AND `ativo` = '1' 
            ORDER BY nome_fantasia ";
    $campos_rep = bancos::sql($sql);
    $linhas_rep = count($campos_rep);
    if($linhas_rep > 0) echo "<tr class='linhadestaque'><td colspan='9'>Sem Vendas no Período</td></tr>";
    for ($r = 0; $r < $linhas_rep; $r++) {
?>
    <tr class='linhanormal'>
        <td colspan='2' align='left'>
            <a href="javascript:nova_janela('rel_cotas_representantes.php?id_representante=<?=$campos_rep[$r]['id_representante'];?>&data_inicial=<?=$data_inicial;?>&data_final=<?=$data_final;?>', 'CONSULTAR', '', '', '', '', '350', '750', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <?=$campos_rep[$r]['nome_fantasia'];?>
            </a>
        </td>
        <td align='center'>
            <?=$campos_rep[$r]['uf'];?>&nbsp;
        </td>
        <td>
            <?=$supervisor_rep_array[$campos_rep[$r]['id_representante']];?>&nbsp;
        </td>
        <td align='right'>
        <?
            $cota_total_do_periodo = vendas::cota_total_do_representante($campos_rep[$r]['id_representante'], $data_inicial, $data_final);
            echo number_format($cota_total_do_periodo, 2, ',', '.');

            $total_geral_cotas+= $cota_total_do_periodo;
            if($cota_total_do_periodo == 0 || $cota_total_do_periodo == 0.00) $cota_total_do_periodo = 1;
        ?>
        </td>
        <td align='right'>
            0,00 ???
        </td>
        <td align='right'>
        <?
            if($cota_total_do_periodo == 1) {
                echo "<font color='blue'>Sem Cota</font>";
            }else {
                $perc_cota = (($total_parcial / $cota_total_do_periodo) * 100);
                echo number_format($perc_cota, 2, ',', '.').'%';
                $tot_perc_cota+= $perc_cota;
            }
        ?>
        </td>
        <td align='right'>
            &nbsp;
        </td>
        <td align='right'>
            &nbsp;
        </td>
    </tr>
<?
    }
//Agora vou listar os que nao consta na lista e pegar a cta dele cuidado por q o not in tem problema fazer um if antes ...
?>
    <tr class='linhanormal' align='right'>
        <td colspan='4'>
            <font color='red' size='2'>
                <b>Total Geral R$ </b>
            </font>
        </td>
        <td>
            <font color='red' size='2'>
                <?=number_format($total_geral_cotas, 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font color='red' size='2'>
                <?=number_format($total_geral, 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font color='red' size='2'>
                <?=number_format(($total_geral/$total_geral_cotas)*100, 2, ',', '.').'%';?>
            </font>
        </td>
        <td align='right'>
            &nbsp;
        </td>
        <td align='right'>
            &nbsp;
        </td>
    </tr>
<?
/************************Parte do Supervisor************************/
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
            % sobre a Cota
        </td>
        <td>
            Qtde de Pedido(s)
        </td>
        <td>
            Prêmio Sup.<br><?=$mes_ref_sg;?>
        </td>
    </tr>
<?
    $sql = "SELECT r.`id_representante`, r.`nome_fantasia`, r.`ativo` 
            FROM `representantes` r 
            INNER JOIN `representantes_vs_funcionarios` rf ON rf.`id_representante` = r.`id_representante` 
            INNER JOIN `funcionarios` f ON f.`id_funcionario` = rf.`id_funcionario` AND f.`id_cargo` IN (25, 109) 
            ORDER BY r.`nome_fantasia` ";
    $campos_supervisor = bancos::sql($sql);
/*******************************************************************/
    for($i = 0; $i < count($campos_supervisor); $i++) {
        $id_representante_supervisor = $campos_supervisor[$i]['id_representante'];
?>
    <tr class='linhanormal'>
        <td colspan='4' align='left'>
            <a href="javascript:nova_janela('rel_cotas_sup_representantes.php?id_representante=<?=$campos_supervisor[$i]['id_representante'];?>&data_inicial=<?=$data_inicial;?>&data_final=<?=$data_final;?>', 'CONSULTAR', '', '', '', '', '350', '750', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
            <?
                if($campos_supervisor[$i]['ativo'] == 1) {
                    echo $campos_supervisor[$i]['nome_fantasia'];
                }else {
                    echo "<font color='red'>".$campos_supervisor[$i]['nome_fantasia'].' - (Inativo)</font>';
                }
            ?>
            </a>
        </td>
        <td align='right'>
        <?
            $cota_total_do_periodo = vendas::cota_total_do_representante($id_representante_supervisor, $data_inicial, $data_final, 'S');
            echo number_format($cota_total_do_periodo, 2, ',', '.');

            $total_geral_cotas+= $cota_total_do_periodo;
            if($cota_total_do_periodo == 0 || $cota_total_do_periodo == 0.00) $cota_total_do_periodo = 1;
        ?>
        </td>
        <td align='right'>
        <?
            if($cota_total_do_periodo > 1) {//Pego as vendas dos representantes de acordo com o Período ...
                $sql = "SELECT r.`id_representante`, r.`nome_fantasia`, r.`ativo`, 
                        IF(c.`id_pais`= '31', SUM((pvi.`qtde` - pvi.`qtde_devolvida`) * pvi.`preco_liq_final`), SUM((pvi.`qtde` - pvi.`qtde_devolvida`) * pvi.`preco_liq_final`) * $valor_dolar_dia) AS total_vendas 
                        FROM `pedidos_vendas` pv 
                        INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
                        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                        INNER JOIN `representantes` r ON r.`id_representante` = pvi.`id_representante` 
                        INNER JOIN `representantes_vs_supervisores` rs ON rs.`id_representante` = r.`id_representante` AND rs.`id_representante_supervisor` = '$id_representante_supervisor' 
                        WHERE $condicao_data BETWEEN '$data_inicial' and '$data_final' 
                        AND pv.`liberado` = '1' 
                        $condicao_livre_debito $condicao_expresso GROUP BY r.`id_representante`, c.`id_pais` ";
                $campos_vendas = bancos::sql($sql);
                $total_parcial = 0;
                for($j = 0; $j < count($campos_vendas); $j++) $total_parcial+= $campos_vendas[$j]['total_vendas'];
                $total_geral+= $total_parcial;
            }else {
                $total_parcial = 0;
            }
            if($total_parcial == 0) {
                echo "<font color='red'>".number_format($total_parcial, 2, ',', '.')."</font>";
            }else {
                echo number_format($total_parcial, 2, ',', '.');
            }
        ?>
        </td>
        <td align='right'>
        <?
            if($cota_total_do_periodo == 1) {
                echo "<font color='blue'>Sem Cota</font>";
            }else {
                $perc_cota = (($total_parcial / $cota_total_do_periodo) * 100);
                echo number_format($perc_cota, 2, ',', '.').'%';
                $tot_perc_cota+= $perc_cota;
            }
        ?>
        </td>
        <td align='right'>
            &nbsp;
        </td>
        <td align='right'>&nbsp;
        <?
            if($comissao_meta_atingida_sup_array[$id_representante_supervisor] != 0) echo segurancas::number_format($comissao_meta_atingida_sup_array[$id_representante_supervisor], ',', '.').'%';
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='submit' name='cmd_atualizar' value='Atualizar Relatório' title='Atualizar Relatório' class='botao'>
            <?
                /*Essa opção a princípio só é mostrada para alguns Funcionários 62 - Roberto, 98 - Dárcio, 
                136 - Nishimura, 147 - Arnaldo Netto ...*/
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 136 || $_SESSION['id_funcionario'] == 147) {
            ?>
            <input type='button' name='cmd_grafico_vendas_metas' value='Gráfico de Vendas vs Metas' title='Gráfico de Vendas vs Metas' onclick="html5Lightbox.showLightbox(7, 'grafico_de_vendas_vs_meta.php?data_inicial=<?=$data_inicial;?>&data_final=<?=$data_final;?>&cmb_tipo_data=<?=$_POST['cmb_tipo_data'];?>&chkt_expresso=<?=$_POST['chkt_expresso'];?>')" style='color:red' class='botao'>
            <?
                }
            ?>
        </td>
    </tr>
</body>
</html>
<?}?>