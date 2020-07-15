<?
/*Aqui vou pegar a qtde comprometida programada do sistema, para nao produzir produtos para pedido 
acima de um mês*/
$data_atual = date('Y-m-d');

if($cmb_operacao == '') $cmb_operacao = '%';
/*Aqui eu tive que fazer essa adaptação, porque estava dando erro de parâmetro por causa que a Combo
armazena um dos valores como sendo zero, e devido a isso, eu estava perdendo todo o Filtro*/
if($hidden_operacao_custo == 1) {//Operação de Custo = Industrial
    $cmb_operacao_custo = 0;
}else if($hidden_operacao_custo == 2) {//Operação de Custo = Revenda
    $cmb_operacao_custo = 1;
}else {//Independente da Operação de Custo
    if($cmb_operacao_custo == '') $cmb_operacao_custo = '%';
}

//Segunda adaptação
if($hidden_operacao_custo_sub == 1) {//Sub-Operação de Custo = Industrial
    $cmb_operacao_custo_sub = 0;
}else if($hidden_operacao_custo_sub == 2) {//Sub-Operação de Custo = Revenda
    $cmb_operacao_custo_sub = 1;
}else {//Independente da Sub-Operação de Custo
    if($cmb_operacao_custo_sub == '') $cmb_operacao_custo_sub = '%';
}

//Se não estiver habilitado o checkbox, só não mostra os P.A. q pertecem a família de Componentes
if(empty($chkt_mostrar_componentes)) $condicao_componentes = " AND gpa.id_familia <> '23' ";

//Aqui eu verifico qual o Tipo de opção escolhida pelo usuário p/ ver a ordenação ...
if($cmb_tipo == 1) {//Produto mais Vendido
    $order_by_pedidos 	= ' ORDER BY qtde_vendida DESC ';
}else if($cmb_tipo == 2) {//Maior Margem de Lucro
    $order_by_pedidos 	= ' ORDER BY mg_l_m_g DESC ';
}else if($cmb_tipo == 3) {//Maior Falta de Produto
    $order_by_produtos 	= ' ORDER BY estoque_comprometido ';
}else if($cmb_tipo == 4) {//Maior P.A. Programado
    $condicao_faturar 	= " AND pv.`faturar_em` >= DATE_ADD('$data_atual', INTERVAL 1 MONTH) ";
    $order_by_pedidos 	= ' ORDER BY estoque_programado DESC ';
}else if($cmb_tipo == 5) {//M.M.V.
    $order_by_produtos 	= ' ORDER BY pa.mmv DESC ';
}else if($cmb_tipo == 6) {//Maior Volume de Vendas
    $order_by_pedidos 	= ' ORDER BY volume_rs DESC ';
}else if($cmb_tipo == 7) {//Maior Lucro em R$
    $order_by_pedidos 	= ' ORDER BY maior_lucro_rs DESC ';
}else if($cmb_tipo == 8) {//Maior Estoque Disponível
    $order_by_produtos 	= ' ORDER BY ea.qtde_disponivel DESC ';
} 

if(!empty($cmb_familia)) {
    if($cmb_grupo_pa) {
        $id_grupos_pas = implode(',', $cmb_grupo_pa);
        $condicao_grupos_pas = " AND gpa.id_grupo_pa IN ($id_grupos_pas) ";
    }
    
    $id_familias = implode(',', $cmb_familia);
    $inner_join_familia = "INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado 
                            INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                            INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.id_familia IN ($id_familias) $condicao_grupos_pas ";
}

$sql = "SELECT pvi.id_produto_acabado, (SUM(pvi.qtde_pendente)) AS estoque_programado, 
        SUM(pvi.qtde) AS qtde_vendida, 
        (SUM(pvi.qtde * pvi.preco_liq_final * ov.valor_dolar) / SUM((pvi.qtde * pvi.preco_liq_final * ov.valor_dolar) / (1 + pvi.margem_lucro / 100)) - 1) * 100 AS mg_l_m_g, 
        SUM(pvi.qtde * pvi.preco_liq_final * ov.valor_dolar) as volume_rs, 
        SUM(pvi.qtde * pvi.preco_liq_final * ov.valor_dolar) * (1 - 1 / ((SUM(pvi.qtde * pvi.preco_liq_final * ov.valor_dolar) / SUM((pvi.qtde * pvi.preco_liq_final * ov.valor_dolar) / (1 + pvi.margem_lucro/100)) - 1) * 100 / 100 + 1)) AS maior_lucro_rs 
        FROM `pedidos_vendas` pv 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda 
        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_orcamento_venda_item = pvi.id_orcamento_venda_item 
        $inner_join_estoques_acabados 
        $inner_join_familia 
        INNER JOIN `orcamentos_vendas` ov ON ovi.id_orcamento_venda = ov.id_orcamento_venda 
        WHERE pv.data_emissao BETWEEN '$data_inicial' AND '$data_final' $condicao_faturar 
        GROUP BY pvi.id_produto_acabado $order_by_pedidos ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
	$vetor_estoque_programado[$campos[$i]['id_produto_acabado']] 	= $campos[$i]['estoque_programado'];
	$vetor_qtde_vendida[$campos[$i]['id_produto_acabado']]          = $campos[$i]['qtde_vendida'];
	$vetor_mg_l_m_g[$campos[$i]['id_produto_acabado']]              = $campos[$i]['mg_l_m_g'];
	$vetor_volume_rs[$campos[$i]['id_produto_acabado']]             = $campos[$i]['volume_rs'];
	$vetor_maior_lucro_rs[$campos[$i]['id_produto_acabado']]        = $campos[$i]['maior_lucro_rs'];
	$id_produtos_acabados.= $campos[$i]['id_produto_acabado'].', ';
}
$id_produtos_acabados 	= substr($id_produtos_acabados, 0, strlen($id_produtos_acabados) - 2);
$order_by_field 		= (!empty($order_by_produtos)) ? '' : " ORDER BY FIELD(pa.`id_produto_acabado`,  $id_produtos_acabados) ";

$sql = "SELECT ged.desc_medio_pa, pa.id_produto_acabado, pa.operacao_custo, pa.qtde_queima_estoque, pa.operacao_custo_sub, pa.referencia, pa.mmv, 
        (pa.preco_unitario * (1 - ged.desc_base_a_nac / 100) * (1 - ged.desc_base_b_nac / 100) * (1 + ged.acrescimo_base_nac / 100)) AS preco_list_desc, 
        ((pa.preco_unitario * (1 - ged.desc_base_a_nac / 100) * (1 - ged.desc_base_b_nac / 100) * (1+ged.acrescimo_base_nac/100)) * ged.desc_medio_pa) preco_rs, 
        pa.observacao observacao_pa, pa.status_top, pa.codigo_barra, 
        (ea.qtde_disponivel - ea.qtde_pendente) AS estoque_comprometido 
        FROM `produtos_acabados` pa 
        INNER JOIN `estoques_acabados` ea ON ea.id_produto_acabado = pa.id_produto_acabado 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
        INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa $condicao_componentes 
        WHERE pa.`id_produto_acabado` IN ($id_produtos_acabados) 
        AND pa.`referencia` <> 'ESP' 
        AND pa.`ativo` = '1' 
        AND pa.operacao_custo LIKE '$cmb_operacao_custo' 
        AND pa.operacao_custo_sub like '$cmb_operacao_custo_sub' $order_by_produtos $order_by_field ";
$campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
$linhas = count($campos);

/********************************************************************************************************/
/*************************************Marcação dos Produtos como TOP*************************************/
/********************************************************************************************************/
if($chkt_marcar_top == 'S') {
    $qtde_produtos_tops_serem_atualizados = genericas::variavel(45);
    $sql_update = "UPDATE `produtos_acabados` SET `status_top` = '0' WHERE `status_top` IN (1, 2) ";
    bancos::sql($sql_update);//apaga todos os TOPs

    $sql_temp 			= $sql." LIMIT 0, ".intval($qtde_produtos_tops_serem_atualizados);
    $campos_temp 		= bancos::sql($sql_temp);//busco o PI atrelado ao custo etapa 6
    $linhas_temp 		= count($campos_temp);
    $primeiros_50_perc          = $linhas_temp / 2;
    $segundos_50_perc           = $primeiros_50_perc;

    for($j = 0; $j < $linhas_temp; $j++) {
        if($j < $primeiros_50_perc) {//Aqui equivale aos 1º 50% dos PA´s TOPs ...
                bancos::sql("UPDATE `produtos_acabados` SET `status_top` = '1', `posicao_top` = '".($j + 1)."' WHERE `id_produto_acabado` = '".$campos_temp[$j]['id_produto_acabado']."' LIMIT 1");
        }else {//Aqui equivale aos 2º 50% dos PA´s TOPs ...	
                bancos::sql("UPDATE `produtos_acabados` SET `status_top` = '2', `posicao_top` = '".($j + 1)."' WHERE `id_produto_acabado` = '".$campos_temp[$j]['id_produto_acabado']."' LIMIT 1");
        }
    }
}
/********************************************************************************************************/
?>
    <tr class='linhacabecalho' align='center'>
        <td rowspan='2'>
            Produto
        </td>
        <td rowspan='2'>
            Código de Barra
        </td>
        <td rowspan='2'>
            <font style='cursor:help' size='-2' title='Operação de Custo'>
                O.C.
            </font>
        </td>
        <td rowspan='2'>
            Compra<br/> Produção
        </td>
        <td colspan='5'>
            Quantidade / Estoque
        </td>
        <td rowspan='2'>
            <font title='Média Mensal de Vendas' style='cursor:help'>
                M.M.V.
            </font>
        </td>
        <td rowspan='2'>
            <font title='Quantidade Vendida' style='cursor:help'>
                Qtde <br/>Vendida
            </font>
        </td>
        <td rowspan='2'>
            <font title='Margem de Lucro' style='cursor:help'>
                M.G. <br>Lucro
            </font>
        </td>
        <td rowspan='2'>
            <p>Pre&ccedil;o de<br/>Lista R$
        </td>
        <td rowspan='2' title="((qtde vend))*(preco*(1-desc_a/100)*(1-desc_b/100)*(1+acr./100))*desc_medio_pa)" style='cursor:help'>
            Total R$
        </td>
        <td rowspan='2' title='Qtde pedido * preço liquido final' style='cursor:help'>
            Volume R$
        </td>
        <td rowspan='2' title='Lucro em R$ => Volume R$ * (1 - 1 / (Mg_Lucro / 100 + 1))' style='cursor:help'>
            Lucro em R$
        </td>
        <td rowspan='2' title='Estoque p/ x meses' style='cursor:help'>
            Est p/ <br/>x meses
        </td>
        <td rowspan='2' title='Estoque p/ x meses' style='cursor:help'>
            Excesso de Estoque
        </td>		
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            <font title='Estoque Real' style='cursor:help'>
                Real
            </font>
        </td>
        <td>
            <font title='Estoque Disponível' style='cursor:help'>
                Disp.
            </font>
        </td>
        <td>
            <font title='Pendência' size='-2' style='cursor:help'>
                Pend.
            </font>
        </td>
        <td>
            <font title='Estoque Comprometido' style='cursor:help'>
                Comp.
            </font>
        </td>
        <td>
            <font title='Estoque Comprometido Programado &gt; que 30 dias' style='cursor:help'>
                Prog.
            </font>
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
            $preco_list_desc        = $campos[$i]['preco_list_desc'];
            $total_rs               = $vetor_qtde_vendida[$campos[$i]['id_produto_acabado']] * $campos[$i]['preco_rs'];
            $desc_medio_pa          = $campos[$i]['desc_medio_pa'];
            $retorno                = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado'], 0);
            $quantidade_estoque     = $retorno[0];
            $compra                 = estoque_acabado::compra_producao($campos[$i]['id_produto_acabado']);
            $producao               = $retorno[2];
            $quantidade_disponivel  = $retorno[3];
            $qtde_pendente          = $retorno[7];
            $est_comprometido       = $retorno[8];
?>
    <tr class='linhanormal'>
        <td align='left'>
            <a href="javascript:nova_janela('../../../vendas/estoque_acabado/detalhes.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>', 'pop', '', '', '', '', '500', '850', 'c', 'c', '', '', 's', 's', '', '', '')" title="Detalhes" class="link">
                <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
            </a>
        </td>
        <td align='center'>
            <?=$campos[$i]['codigo_barra'];?>
        </td>
        <td align='left'>
        <?
            if($campos[$i]['status_top'] == 1) {
                echo  "<font color='red' style='cursor:help;' title='1º 50% dos PA´s TOP'>TopA</font> - ";
            }else if($campos[$i]['status_top'] == 2) {
                echo  "<font color='red' style='cursor:help;' title='2º 50% dos PA´s TOP'>TopB</font> - ";
            }
            if($campos[$i]['operacao_custo'] == 0) {
                echo 'I';
                //Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
                if($campos[$i]['operacao_custo_sub'] == 0) {
                    echo '-I';
                }else if($campos[$i]['operacao_custo_sub'] == 1) {
                    echo '-R';
                }else {
                    echo '-';
                }
            }else if($campos[$i]['operacao_custo'] == 1) {
                echo 'R';
            }else {
                echo '-';
            }
        ?>
        </td>
        <td align='right'>
                <?
/*Se a Qtde em Compra ou Produção for < que a do Estoque Comprometido, então exibo essa coluna com a cor 
em Vermelho a Pedido do Betão ...*/
                        if(!empty($compra) && $compra != 0) {//Se existir Compra ...
                                if($compra < - ($est_comprometido)) {
                                        $font = "<font color='red'>";
                                }else {
                                        $font = "<font color='black'>";
                                }
                        }
//Aqui verifica se o PA tem relação com o PI, caso isso não acontece não apresenta o link
                        $sql = "SELECT id_produto_insumo 
                                FROM `produtos_acabados` 
                                WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                                AND `id_produto_insumo` > '0' 
                                AND `ativo` = '1' ";
                        $campos_pipa = bancos::sql($sql);
//Aqui o PI em relação com o PA e a OC. é do Tipo Revenda então mostra o link
                        if(count($campos_pipa) == 1 && $campos[$i]['operacao_custo'] == 1) {
                ?>
                <a href="javascript:nova_janela('../../../classes/estoque/compra_producao.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>', 'pop', '', '', '', '', '580', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Compra Produção" class="link">
                <?
                                echo $font.segurancas::number_format($compra, 2, '.');
                                if(!empty($producao) && $producao != 0) {
                                        if($producao < - ($est_comprometido)) {
                                                $font = "<font color='red'>";
                                        }else {
                                                $font = "<font color='black'>";
                                        }
//Se for link, tenho que exibir em Azul ...
                                        if($font == "<font color='black'>") {
                                                $font = "<font color='#6473D4'>";
                                        }
                                        echo ' / '.$font.segurancas::number_format($producao, 2, '.');
                                }
                        ?>
                </a>
<?
//Aqui o PI em relação com o PA e a OC. é do Tipo Industrial
                        }else if(count($campos_pipa) == 1 && $campos[$i]['operacao_custo'] == 0) {
                ?>
                <a href="#" onclick="nova_janela('visualizar_qtde_vendida.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&data_inicial=<?=$data_inicial;?>&data_final=<?=$data_final;?>', 'pop', '', '', '', '', '580', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Qtde Vendida" class="link">
                <?
                                echo $font.segurancas::number_format($compra, 2, '.');
                                if(!empty($producao) && $producao != 0) {
                                        if($producao < - ($est_comprometido)) {
                                                $font = "<font color='red'>";
                                        }else {
                                                $font = "<font color='#6473D4'>";
                                        }
                                        echo ' / '.$font.segurancas::number_format($producao, 2, '.');
                                }
//Aqui o PA não tem relação com o PI
                        }else {
                                if($producao < - ($est_comprometido)) {
                                        $font = "<font color='red'>";
                                        $title = "Produção menor do que Estoque Comprometido";
                                }else {
                                        $font = "<font color='#6473D4'>";
                                        $title = "Produção maior do que Estoque Comprometido";
                                }
                ?>
                <a href="#" onclick="nova_janela('visualizar_qtde_vendida.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&data_inicial=<?=$data_inicial;?>&data_final=<?=$data_final;?>', 'pop', '', '', '', '', '580', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="<?=$title;?>" class="link">
                <?
                                echo $font.segurancas::number_format($producao, 2, '.');
                        }
                ?>
        </td>
        <td align='right' bgcolor='#FFFFDF'>
                <?=segurancas::number_format($quantidade_estoque, 2, '.');?>
        </td>
        <td align='right' bgcolor='#FFFFDF'>
        <?
                if($quantidade_disponivel < 0) {
                        echo "<font color='red'>".segurancas::number_format($quantidade_disponivel, 2, '.')."</font>";
                }else {
                        echo segurancas::number_format($quantidade_disponivel, 2, '.');
                }
        ?>
        </td>
        <td align='right' bgcolor='#FFFFDF'>
                <?=segurancas::number_format($qtde_pendente, 2, '.');?>
        </td>
        <td align='right' bgcolor='#FFFFDF'>
        <?
                if($est_comprometido < 0) {
                        echo "<font color='red'>".segurancas::number_format($est_comprometido, 2, '.')."</font>";
                }else {
                        echo segurancas::number_format($est_comprometido, 2, '.');
                }
        ?></td>
        <td align='right' bgcolor='#FFFFDF'>
        <?

            if($cmb_tipo != 4) {
                $sql = "SELECT (SUM(`qtde_pendente`)) AS estoque_programado 
                        FROM `pedidos_vendas_itens` pvi 
                        INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
                        WHERE pvi.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                        AND pv.`faturar_em` >= DATE_ADD('$data_atual', INTERVAL 1 MONTH) ";
                $campos_prog = bancos::sql($sql);
                $estoque_programado = $campos_prog[0]['estoque_programado'];
                echo segurancas::number_format($estoque_programado, 2, '.');
            }else {//Maior Tempo de Produção
                echo segurancas::number_format($vetor_estoque_programado[$campos[$i]['id_produto_acabado']], 2, '.');
            }
        ?>
        </td>
        <td align='right'>
                <?=segurancas::number_format($campos[$i]['mmv'], 1, '.');?>
        </td>
        <td align='right'>
                <a href="#" onclick="nova_janela('visualizar_qtde_vendida.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&data_inicial=<?=$data_inicial;?>&data_final=<?=$data_final;?>', 'pop', '', '', '', '', '580', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Qtde Vendida" class="link">
                        <?=segurancas::number_format($vetor_qtde_vendida[$campos[$i]['id_produto_acabado']], 2, '.');?>
                </a>
        </td>
        <td align='right'>
                <a href="#" onclick="nova_janela('visualizar_qtde_vendida.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&data_inicial=<?=$data_inicial;?>&data_final=<?=$data_final;?>', 'pop', '', '', '', '', '580', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="M.G. Lucro" class="link">
                        <?=segurancas::number_format($vetor_mg_l_m_g[$campos[$i]['id_produto_acabado']], 1, '.').' %';?>
                </a>
        </td>
        <td align='right'>
                <?=segurancas::number_format($preco_list_desc, 2, '.');?>
        </td>
        <td align='right' title="Fator de Desconto=><?=$desc_medio_pa;?>" style="cursor:help">
                <?
                        if($total_rs > 0) {
                                $total_geral_rs+= $total_rs;
                                echo segurancas::number_format($total_rs, 0, '.');
                        }else {
                                echo "&nbsp;";
                        }
                ?>
        </td>
        <td align='right'>
                <?=segurancas::number_format($vetor_volume_rs[$campos[$i]['id_produto_acabado']], 0, '.');?>
        </td>
        <td align='right'>
                <?=segurancas::number_format($vetor_maior_lucro_rs[$campos[$i]['id_produto_acabado']], 0, '.');?>
        </td>
        <td align='center'>
        <?
                if($est_comprometido > 0) {
                        $ec_p_meses = $est_comprometido / $campos[$i]['mmv'];
                        $font 		= ($ec_p_meses >= 4) ? '<font color="red"><b>': '';
                        echo $font.number_format($ec_p_meses, 1, ',', '.');
                }else {
                        echo '0,0';
                }
        ?>
        </td>
        <td align='center'>
        <?
                if($campos[$i]['qtde_queima_estoque'] > 0) {
                        echo number_format($campos[$i]['qtde_queima_estoque'], 0, ',', '.');
                }else {
                        echo '&nbsp;';
                }
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='18'>
            <input type="submit" name="cmd_atualizar" value="Atualizar Relatório" title='Atualizar Relatório' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<pre>
<b><font color="red">Observação:</font></b>
<pre>
* Ao setar "Marcar Produtos como TOP" e clicar no botão Consultar, os TOP(s) existente(s) são desmarcado(s) 
e o(s) novo(s) <?=genericas::variavel(45);?> TOP(s) são marcado(s), independente de paginação.
</pre>