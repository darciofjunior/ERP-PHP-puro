<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/custos.php');
require('../../../../../lib/estoque_acabado.php');
require('../../../../../lib/intermodular.php');
require('../../../../../lib/vendas.php');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

$fator_desc_max_vendas 	= genericas::variavel(19);//Fator Desc Máx. de Vendas
$fator_margem_lucro 	= genericas::variavel(22);//margem de Lucro PA

//Essa combo tem de funcionar sozinha apenas, sem concatenar com nenhuma outra opção ...
if(!empty($cmb_grupo_pa_vs_empresa_divisao) && $cmb_grupo_pa_vs_empresa_divisao != '%') {
    $txt_referencia                     = '%';
    $txt_discriminacao                  = '%';
    $cmb_empresa_divisao                = '%';
    $cmb_grupo_pa                       = '%';
    $cmb_familia                        = '%';
    $cmb_order_by                       = '1';
}else {//Filtro normal ...
    if(!empty($chkt_novo_preco_promocional)) 	$condicao_novo_preco_promocional    = " AND (pa.preco_promocional_simulativa <> '0' OR pa.preco_promocional_simulativa_b <> '0') ";
    if(!empty($chkt_preco_promocional_atual))	$condicao_preco_promocional_atual   = " AND (pa.preco_promocional <> '0' OR pa.preco_promocional_b <> '0') ";
    if(!empty($chkt_todos_produtos_zerados)) 	$condicao_produtos_zerados          = " AND pa.preco_unitario_simulativa = '0.00' ";
    if(empty($cmb_grupo_pa_vs_empresa_divisao))     $cmb_grupo_pa_vs_empresa_divisao    = '%';
    if(empty($cmb_empresa_divisao)) 		$cmb_empresa_divisao                = '%';
    if(empty($cmb_grupo_pa)) 			$cmb_grupo_pa                       = '%';
    if(empty($cmb_familia)) 			$cmb_familia                        = '%';
    if(empty($cmb_order_by)) 			$cmb_order_by                       = '1';
}

//Aqui traz todos os grupos com exceção dos que são pertencentes a Família de Componentes
$sql = "SELECT pa.id_produto_acabado, pa.operacao_custo, pa.referencia, pa.discriminacao, pa.preco_unitario, pa.preco_unitario_simulativa, pa.perc_estimativa_custo, 
        pa.preco_promocional, pa.preco_promocional_b, pa.qtde_promocional_simulativa, pa.preco_promocional_simulativa, pa.qtde_promocional_simulativa_b, 
        pa.preco_promocional_simulativa_b, pa.status_top, pa.status_custo, pa.qtde_queima_estoque, 
        ed.razaosocial, cf.ipi, gpa.nome, ged.desc_base_a_nac, ged.desc_base_b_nac, ged.acrescimo_base_nac, 
        ged.margem_lucro_minima, ged.desc_a_lista_nova, ged.desc_b_lista_nova, ged.acrescimo_lista_nova 
        FROM `produtos_acabados` pa 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div AND ged.`id_gpa_vs_emp_div` LIKE '$cmb_grupo_pa_vs_empresa_divisao' 
        INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.id_grupo_pa LIKE '$cmb_grupo_pa' AND gpa.id_familia LIKE '$cmb_familia' AND gpa.id_familia <> '23' 
        INNER JOIN `familias` f ON f.id_familia = gpa.id_familia 
        INNER JOIN `classific_fiscais` cf ON cf.id_classific_fiscal = f.id_classific_fiscal 
        INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao AND ed.id_empresa_divisao LIKE '$cmb_empresa_divisao' 
        WHERE pa.referencia LIKE '%$txt_referencia%' 
        AND pa.discriminacao LIKE '%$txt_discriminacao%' 
        AND pa.referencia <> 'ESP' 
        AND pa.ativo = '1' 
        $condicao_novo_preco_promocional $condicao_preco_promocional_atual $condicao_produtos_zerados ORDER BY $cmb_order_by ";
$campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Relatório Nova Lista de Preço Nacional ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='95%' border='0' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)' align='center'>
<?
    if($linhas == 0) {
?>
    <tr align='center'>
        <td colspan='23'>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='23'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='23'>
            Relatório Nova Lista de Preço Nacional
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>Ref.</b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>Discrimina&ccedil;&atilde;o&nbsp;</b>
        </td>
        <td colspan='2' bgcolor='#CECECE'>
            <b>EE</b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font bgcolor='#CECECE'>
                <b>Nova<br>Qtde A</b>
            </font>
        </td>
        <td colspan='2' bgcolor='#CECECE'>
            <font title="Preço Promocional">
                <b>Preço <br>Promocional A R$</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font bgcolor='#CECECE'>
                <b>Nova<br>Qtde B</b>
            </font>
        </td>
        <td colspan='2' bgcolor='#CECECE'>
            <font title="Preço Promocional">
                <b>Preço <br>Promocional B R$</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font size='-2'>
                <b>Preço R$<br>Concor.A</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font size='-2'>
                <b>Preço R$<br>Concor.B</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font size='-2'>
                <b>Preço R$<br>Concor.C</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font size='-2'>
                <b>ML.Min.%
                <br>Gr.x Div. Nova</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font size='-2'>
                <b>Pr.Custo R$<br>M.L.100%</b>				
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'> 
            <font size='-2'>
                <b>Preço Custo <br>M.L. Mín Nova R$</b>
            </font>
        </td>
        <td colspan="3" bgcolor='#CECECE'>
            <b>Preço Líquido + 20% <br>Faturado R$</b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title="Novo Preço Bruto Faturado R$" bgcolor='#CECECE'>
                <b>Novo Preço <br>Bruto Fat. R$</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>IPI %</b>
        </td>
        <td colspan='2' bgcolor='#CECECE'>
            <b>Desc.20+10% (Atacadista)</b>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE'>
            <b>Qtde</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Preço</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Novo</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Atual</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Novo</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Atual</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Novo / ML %</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Atual</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Dif.%</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>P.Líq.R$</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>ML%</b>
        </td>
    </tr>
<?
        for ($i = 0; $i < $linhas; $i++) {
//Fórmula do Preço Máximo Custo Fat. R$ - esse campo está aqui, mais ele é printado + abaixo
/*A percentagem extra, eu já aplico desde o início no "Preço Máximo Custo Fat. R$", pq essa variável é
utilizada praticamente em todos o cálculo dessa Lista ...*/
            $preco_maximo_custo_fat_rs = custos::preco_custo_pa($campos[$i]['id_produto_acabado']) / $fator_desc_max_vendas * ($campos[$i]['perc_estimativa_custo'] / 100 + 1);
//Forço o arred. para 2 casas para não dar erro na fórmula por causa do JavaScript -> Dárcio
            $preco_maximo_custo_fat_rs = round($preco_maximo_custo_fat_rs, 2);
//Utilizo + abaixo
            if($campos[$i]['operacao_custo'] == 0) {//Operação de Custo = Industrial
//Atribui de acordo com o Fator Margem de Lucro declarado no início do código
                $fator_margem_lucro_loop = $fator_margem_lucro;
                $printar = segurancas::number_format(($fator_margem_lucro_loop - 1) * 100, 2, '.');
            }else {//Operação de Custo = Revenda
//Busco somente o id_fornecedor default para saber qual fornecedor q estou pegando para calcular o custo PA
                $id_fornecedor_setado = custos::procurar_fornecedor_default_revenda($campos[$i]['id_produto_acabado'], '', 1);
//Busco desse Fornecedor e PA corrente o Fator Margem de Lucro na Lista de Preços
                $sql = "SELECT fpi.fator_margem_lucro_pa 
                        FROM `fornecedores_x_prod_insumos` fpi 
                        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = fpi.`id_produto_insumo` AND pa.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' AND pa.`ativo` = '1' 
                        WHERE fpi.id_fornecedor = '$id_fornecedor_setado' LIMIT 1 ";
                $campos_margem_lucro 		= bancos::sql($sql);
                $fator_margem_lucro_loop 	= $campos_margem_lucro[0]['fator_margem_lucro_pa'];
                if(empty($fator_margem_lucro_loop)) $fator_margem_lucro_loop = 1;
                $printar = segurancas::number_format(($fator_margem_lucro_loop - 1) * 100, 2, '.');
            }
            
            //Busca dos Preços dos Concorrentes atrelados ao Produto Acabado ...
            $sql = "SELECT cpa.com_ipi, cpa.com_st, cpa.preco_liquido, cc.nome 
                    FROM `concorrentes_vs_prod_acabados` cpa 
                    INNER JOIN `concorrentes` cc ON cc.id_concorrente = cpa.id_concorrente 
                    WHERE cpa.id_produto_acabado = '".$campos[$i]['id_produto_acabado']."' ";
            $campos_concorrentes = bancos::sql($sql);
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <font title="Grupo P.A. (E. D.): <?=$campos[$i]['nome'].' / '.$campos[$i]['razaosocial'];?>">
                <?=$campos[$i]['referencia'];?>
            </font>
        </td>
        <td align='left'>
            <font title="Grupo P.A. (E. D.): <?=$campos[$i]['nome'].' / '.$campos[$i]['razaosocial'];?>">
                <?=$campos[$i]['discriminacao'];?>
            </font>
            <font color='red' size='-2'>
            <?
                if($campos[$i]['status_top'] == 1) {
                    echo  "<font color='red' style='cursor:help;' title='1º 50% dos PA´s TOP'> (TopA)</font>";
                }else if($campos[$i]['status_top'] == 2) {
                    echo  "<font color='red' style='cursor:help;' title='2º 50% dos PA´s TOP'> (TopB)</font>";
                }
            ?>
            </font>
        </td>
        <td>
        <?
            //Se existir Excesso de Estoque, mostro a figura abaixo ...
            if($campos[$i]['qtde_queima_estoque'] > 0) echo number_format($campos[$i]['qtde_queima_estoque'], 0, ',', '.');
        ?>
        </td>
        <td>
        <?
            //Foi comentado no dia 18/04/2017, precisa ser reavaliado ...
            if($campos[$i]['qtde_queima_estoque'] > 0) {//Se existir Excesso de Estoque, mostro a figura abaixo ...
                //$preco_excesso_estoque = vendas::calcular_preco_de_queima_pa($campos[$i]['id_produto_acabado']);
                echo '<font title="Preço Excedente p/ 60 ddl" style="cursor:help">R$ '.number_format($preco_excesso_estoque, 2, ',', '.');
            }
        ?>
        </td>
        <td>
            <?=segurancas::number_format($campos[$i]['qtde_promocional_simulativa'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($campos[$i]['preco_promocional_simulativa'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($campos[$i]['preco_promocional'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($campos[$i]['qtde_promocional_simulativa_b'], 2, '.');?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($campos[$i]['preco_promocional_simulativa_b'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($campos[$i]['preco_promocional_b'], 2, '.');?>
        </td>
        <td>
            <font title='<?=$campos_concorrentes[0]['nome'];?>' style='cursor:help'>
            <?
                if($campos_concorrentes[0]['preco_liquido'] != 0) {
                    echo number_format($campos_concorrentes[0]['preco_liquido'], 2, ',', '.').' <b>'.substr(strtoupper($campos_concorrentes[0]['nome']), 0, 4).'</b>';
                    if($campos_concorrentes[0]['com_ipi'] == 'S' && $campos_concorrentes[0]['com_st'] == 'N') {
                        $rotulo_impostos1 = '<font color="red"><b> (c/IPI) </b></font>';
                    }else if($campos_concorrentes[0]['com_ipi'] == 'N' && $campos_concorrentes[0]['com_st'] == 'S') {
                        $rotulo_impostos1 = '<font color="red"><b> (c/ST) </b></font>';
                    }else if($campos_concorrentes[0]['com_ipi'] == 'S' && $campos_concorrentes[0]['com_st'] == 'S') {
                        $rotulo_impostos1 = '<font color="red"><b> (c/IPI + ST) </b></font>';
                    }else {
                        $rotulo_impostos1 = '';
                    }
                    echo $rotulo_impostos1;
                }
            ?>
            </font>
        </td>
        <td>
            <font title='<?=$campos_concorrentes[1]['nome'];?>' style='cursor:help'>
            <?
                if($campos_concorrentes[1]['preco_liquido'] != 0) {
                    echo number_format($campos_concorrentes[1]['preco_liquido'], 2, ',', '.').' <b>'.substr(strtoupper($campos_concorrentes[1]['nome']), 0, 4).'</b>';
                    if($campos_concorrentes[1]['com_ipi'] == 'S' && $campos_concorrentes[1]['com_st'] == 'N') {
                        $rotulo_impostos2 = '<font color="red"><b> (c/IPI) </b></font>';
                    }else if($campos_concorrentes[1]['com_ipi'] == 'N' && $campos_concorrentes[1]['com_st'] == 'S') {
                        $rotulo_impostos2 = '<font color="red"><b> (c/ST) </b></font>';
                    }else if($campos_concorrentes[1]['com_ipi'] == 'S' && $campos_concorrentes[1]['com_st'] == 'S') {
                        $rotulo_impostos2 = '<font color="red"><b> (c/IPI + ST) </b></font>';
                    }else {
                        $rotulo_impostos2 = '';
                    }
                    echo $rotulo_impostos2;
                }
            ?>
            </font>
        </td>
        <td>
            <font title='<?=$campos_concorrentes[2]['nome'];?>' style='cursor:help'>
            <?
                if($campos_concorrentes[2]['preco_liquido'] != 0) {
                    echo number_format($campos_concorrentes[2]['preco_liquido'], 2, ',', '.').' <b>'.substr(strtoupper($campos_concorrentes[2]['nome']), 0, 4).'</b>';
                    if($campos_concorrentes[2]['com_ipi'] == 'S' && $campos_concorrentes[2]['com_st'] == 'N') {
                        $rotulo_impostos3 = '<font color="red"><b> (c/IPI) </b></font>';
                    }else if($campos_concorrentes[2]['com_ipi'] == 'N' && $campos_concorrentes[2]['com_st'] == 'S') {
                        $rotulo_impostos3 = '<font color="red"><b> (c/ST) </b></font>';
                    }else if($campos_concorrentes[2]['com_ipi'] == 'S' && $campos_concorrentes[2]['com_st'] == 'S') {
                        $rotulo_impostos3 = '<font color="red"><b> (c/IPI + ST) </b></font>';
                    }else {
                        $rotulo_impostos3 = '';
                    }
                    echo $rotulo_impostos3;
                }
            ?>
            </font>
        </td>
        <td>
        <?
            //Se a Margem de Lucro < 70%, não aplicamos a redução de 5% pois a ML ficaria abaixo de 45% para Atacadista.
            if($campos[$i]['status_top'] == 1) {
                $margem_lucro_minima_corr = ($campos[$i]['margem_lucro_minima'] <= 70) ? $campos[$i]['margem_lucro_minima'] : $campos[$i]['margem_lucro_minima'] * 0.95;
            }else if($campos[$i]['status_top'] == 2) {
                $margem_lucro_minima_corr = $campos[$i]['margem_lucro_minima'];
            }else if($campos[$i]['status_top'] == 0) {
                $margem_lucro_minima_corr = $campos[$i]['margem_lucro_minima'] * 1.1;
            }
            echo number_format($margem_lucro_minima_corr, 2, ',', '.');
        ?>
        </td>
        <?
            $preco_liq_fat_novo_rs = round($campos[$i]['preco_unitario_simulativa'] * (100 - $campos[$i]['desc_a_lista_nova']) / 100 * (100 - $campos[$i]['desc_b_lista_nova']) / 100 * (100 + $campos[$i]['acrescimo_lista_nova']) / 100 * $fator_desc_max_vendas, 2);
            if($campos[$i]['status_custo'] == 1) {//Custo Liberado
//Comparação
//Preço Máx. Custo Fat. Novo R$ - maior do q P. Líq. Fat. R$
//Eu tive que arredondar aqui em baixo p/ 2 casas, para poder fazer a comparação de forma exata
                if(round($preco_liq_fat_novo_rs, 2) >= round($preco_maximo_custo_fat_rs, 2)) {
//Preço Máx. Custo Fat. Novo R$ - menor do q P. Líq. Fat. R$
                    $color = 'background:#FFFFE1;color:gray';
                }else {
                    $color = 'background:red;color:white';
                }
                $printar = segurancas::number_format($preco_maximo_custo_fat_rs, 2, '.');
            }else {//Custo não Liberado
                $color = 'background:#FFFFE1;color:gray';
                $printar = 'Orçar';
            }
        ?>
        <td align="right">
            <?=$printar;?></td>
        </td>
        <td>
        <?
            $preco_custo_ml_zero    = ($preco_maximo_custo_fat_rs / 2);
            $preco_custo_ml_min_rs  = $preco_custo_ml_zero * (1 + $margem_lucro_minima_corr / 100);
            echo number_format($preco_custo_ml_min_rs, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            $ml_preco_liq_mais_20 = (($preco_liq_fat_novo_rs / $preco_custo_ml_zero - 1) * 100);
            echo segurancas::number_format($preco_liq_fat_novo_rs, 2, '.').' / '.segurancas::number_format($ml_preco_liq_mais_20, 1, '.');
        ?>
        </td>
        <td>
        <?
            //Fórmula do Preço Líquido Fat. Atual R$ ...
            $preco_liq_fat_atual_rs = $campos[$i]['preco_unitario'] * (1 - $campos[$i]['desc_base_a_nac'] / 100) * (1 - $campos[$i]['desc_base_b_nac'] / 100) * (1 + $campos[$i]['acrescimo_base_nac'] / 100) * $fator_desc_max_vendas;
            echo segurancas::number_format($preco_liq_fat_atual_rs, 2, '.');
        ?>
        </td>
        <td>
        <?
            //Fórmula da Diferença em Percentagem, tem essa verificação para não dar erro de divisão por Zero
            if($preco_liq_fat_atual_rs != 0) $preco_liq_dif_perc = ($preco_liq_fat_novo_rs / $preco_liq_fat_atual_rs - 1) * 100;
            if($preco_liq_dif_perc < 0) {
                echo '<font color="red">'.segurancas::number_format($preco_liq_dif_perc, 1, '.').'</font>';
            }else {
                echo segurancas::number_format($preco_liq_dif_perc, 1, '.');
            }
        ?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($campos[$i]['preco_unitario_simulativa'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($campos[$i]['ipi'], 2, '.');?>
        </td>
        <td>
        <?
            $preco_liq_fat_novo_rs_mais_10 = $preco_liq_fat_novo_rs * 0.9;
            echo segurancas::number_format($preco_liq_fat_novo_rs_mais_10, 2, '.');
        ?>
        </td>
        <td>
        <?
            $ml_preco_liq_mais_20_mais_10 = (($preco_liq_fat_novo_rs_mais_10 / $preco_custo_ml_zero - 1) * 100);
            echo segurancas::number_format($ml_preco_liq_mais_20_mais_10, 1, '.');
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='23'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
    }
?>
</body>
</html>