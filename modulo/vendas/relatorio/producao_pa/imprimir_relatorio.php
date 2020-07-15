<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/producao_pa/producao_pa.php', '../../../../');

$valor_dolar_dia = genericas::moeda_dia('dolar');
?>
<html>
<head>
<title>.:: Relatório de Produção P.A. ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<table width='90%' border='1' cellspacing='0' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Relat&oacute;rio de Produção P.A. - 
            <font color='black'>
                Impressão em: 
            </font>
            <?=date('d/m/Y H:i:s');?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            Empresa Divis&atilde;o / Família / Grupo P.A.
        </td>
        <td colspan='3'>
            OE
        </td>
        <td colspan='3'>
            OP
        </td>
        <td colspan='3'>
            NF de Entrada
        </td>	
        <td rowspan='2'>
            Total
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.º
        </td>
        <td>
            Data <br/>Entrada
        </td>
        <td>
            Qtde / Preço <br/>de Entrada
        </td>
        <td>
            N.º
        </td>
        <td>
            Data <br/>Entrada
        </td>
        <td>
            Qtde / Preço <br/>de Entrada
        </td>
        <td>
            N.º
        </td>
        <td>
            Data <br/>Entrada
        </td>
        <td>
            Qtde / Preço <br/>de Entrada
        </td>
    </tr>
<?
$data_inicial   = data::datatodate($_POST['txt_data_inicial'], '-');
$data_final     = data::datatodate($_POST['txt_data_final'], '-');

//Faço a busca de todas as Empresas Divisões que estão cadastradas no Sistema ...
$sql = "SELECT `id_empresa_divisao`, `razaosocial` 
        FROM `empresas_divisoes` 
        WHERE `ativo` = '1' 
        ORDER BY `razaosocial` ";
$campos_empresa_divisao = bancos::sql($sql);
$linhas_empresa_divisao = count($campos_empresa_divisao);
for($i = 0; $i < $linhas_empresa_divisao; $i++) {
?>
<!---------------------------------------Empresa Divisão-------------------------------------------------->
    <tr class='linhanormal'>
        <td colspan='11'>
            <font color='blue'>
                <b><?=$campos_empresa_divisao[$i]['razaosocial'];?></b>
            </font>
        </td>
    </tr>
<!-----------------------------------------Família--------------------------------------------------------->
<?
    $sql = "SELECT f.`id_familia`, f.`nome` 
            FROM `gpas_vs_emps_divs` ged 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`ativo` = '1' 
            INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` AND f.`ativo` = '1' 
            WHERE ged.`id_empresa_divisao` = '".$campos_empresa_divisao[$i]['id_empresa_divisao']."' 
            GROUP BY f.`id_familia` 
            ORDER BY f.`nome` ";
    $campos_familia = bancos::sql($sql);
    $linhas_familia = count($campos_familia);
    for($j = 0; $j < $linhas_familia; $j++) {
?>
    <tr class='linhanormal'>
        <td colspan='11'>
            <font color='#990099'>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;<?=$campos_familia[$j]['nome'];?></b>
            </font>
        </td>
    </tr>
<!------------------------------------------Grupo---------------------------------------------------------->
<?
        $sql = "SELECT ged.`id_gpa_vs_emp_div`, gpa.`id_grupo_pa`, gpa.`nome` 
                FROM `grupos_pas` gpa 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_grupo_pa` = gpa.`id_grupo_pa` AND ged.`id_empresa_divisao` = '".$campos_empresa_divisao[$i]['id_empresa_divisao']."' 
                INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` AND f.`ativo` = '1' 
                WHERE gpa.`id_familia` = '".$campos_familia[$j]['id_familia']."' 
                AND gpa.`ativo` = '1' 
                GROUP BY gpa.`id_grupo_pa` 
                ORDER BY gpa.`nome` ";
        $campos_grupo = bancos::sql($sql);
        $linhas_grupo = count($campos_grupo);
        for($k = 0; $k < $linhas_grupo; $k++) {
            //Limpa as variáveis p/ não herdar valores do Loop Anterior ...
            $total_do_grupo_rs = '';
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <font color='#ff9900'>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <a href = 'detalhes_producao_pa.php?id_grupo_pa=<?=$campos_grupo[$k]['id_grupo_pa'];?>&id_empresa_divisao=<?=$campos_empresa_divisao[$i]['id_empresa_divisao'];?>&txt_data_inicial=<?=$txt_data_inicial;?>&txt_data_final=<?=$txt_data_final;?>' style='color:#ff9900' class='html5lightbox'>
                    <?=$campos_grupo[$k]['nome'];?>
                </a>
            </font>
        </td>
        <!--******************************OE(s)******************************-->
        <td>
        <?
            /*Verifico o Total de Entrada(s) Registrada(s) p/ esse PA no caminho de OE cuja ação seja:

            M -> "Manipulação" e Tipo seja: 

            1) -> Manipulação p/ Substituição;
            2) -> Manipulação p/ Substituição com Ordem de Embalagem;
            3) -> Manipulação p/ Montagem de Jogos;

            /* Aqui controlamos tanto as Entradas como PA(s) enviados da OE ... Sinal Positivo representa entrada, 
            negativo PA(s) enviados ou correção de Entrada ...*/
            $sql = "SELECT bmp.`id_oe`, bmp.`qtde` AS qtde_entrada_saida, DATE_FORMAT(SUBSTRING(bmp.`data_sys`, 1, 10), '%d/%m/%Y') AS data_entrada_saida, 
                    ged.`desc_medio_pa`, pa.`operacao_custo`, pa.`operacao_custo_sub`, 
                    ROUND(pa.`preco_unitario` * (1 - ged.`desc_base_a_nac` / 100) * (1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100), 2) AS preco_de_lista_c_desconto 
                    FROM `baixas_manipulacoes_pas` bmp 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = bmp.`id_produto_acabado` 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` AND ged.`id_gpa_vs_emp_div` = '".$campos_grupo[$k]['id_gpa_vs_emp_div']."' 
                    WHERE SUBSTRING(bmp.`data_sys`, 1, 10) BETWEEN '$data_inicial' AND '$data_final' 
                    AND bmp.`acao` = 'M' 
                    AND bmp.`tipo_manipulacao` IN (1, 2, 3) ";
            $campos_entrada_por_oe = bancos::sql($sql);
            $linhas_entrada_por_oe = count($campos_entrada_por_oe);
            for($l = 0; $l < $linhas_entrada_por_oe; $l++) {
        ?>
            <a href = '../../../producao/oes/alterar.php?passo=1&id_oe=<?=$campos_entrada_por_oe[$l]['id_oe'];?>&pop_up=1' class='html5lightbox'>
                <?=$campos_entrada_por_oe[$l]['id_oe'];?>
            </a>
            <br/>
        <?
                $preco_item_rs      = ($campos_entrada_por_oe[$l]['desc_medio_pa'] > 0) ? $campos_entrada_por_oe[$l]['preco_de_lista_c_desconto'] * $campos_entrada_por_oe[$l]['desc_medio_pa'] : $campos_entrada_por_oe[$l]['preco_de_lista_c_desconto'];
                $total_do_item_rs   = $campos_entrada_por_oe[$l]['qtde_entrada_saida'] * $preco_item_rs;
                $total_do_grupo_rs+= $total_do_item_rs;
                
                //Acumulo nessa variável o Total da Divisão vs OC ...
                $vetor_total_por_divisao_vs_oc[$campos_empresa_divisao[$i]['id_empresa_divisao']][$campos_entrada_por_oe[$l]['operacao_custo']][$campos_entrada_por_oe[$l]['operacao_custo_sub']]+= $total_do_item_rs;
            }
        ?>
        </td>
        <td>
        <?
            for($l = 0; $l < $linhas_entrada_por_oe; $l++) echo $campos_entrada_por_oe[$l]['data_entrada_saida'].'<br/>';
        ?>
        </td>
        <td>
        <?
            for($l = 0; $l < $linhas_entrada_por_oe; $l++) {
                echo number_format($campos_entrada_por_oe[$l]['qtde_entrada_saida'], 2, ',', '.').' à R$ '.number_format($campos_entrada_por_oe[$l]['preco_de_lista_c_desconto'], 2, ',', '.').'<br/>';
                $qtde_entrada+= $campos_entrada_por_oe[$l]['qtde_entrada_saida'];
            }
        ?>
        </td>
        <!--******************************OP(s)******************************-->
        <td>
        <?
            /*Aqui eu localizo todas as Baixas Manipulações de PA dentro do Período digitado pelo Usuário 

            Obs: Ação E -> Entrada de OP ...*/
            $sql = "SELECT bmp.`id_baixa_manipulacao_pa`, bmp.`qtde` AS qtde_entrada, DATE_FORMAT(SUBSTRING(bmp.`data_sys`, 1, 10), '%d/%m/%Y') AS data_entrada, 
                    ged.`desc_medio_pa`, pa.`operacao_custo`, pa.`operacao_custo_sub`, 
                    ROUND(pa.`preco_unitario` * (1 - ged.`desc_base_a_nac` / 100) * (1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100), 2) AS preco_de_lista_c_desconto 
                    FROM `baixas_manipulacoes_pas` bmp 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = bmp.`id_produto_acabado` 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` AND ged.`id_gpa_vs_emp_div` = '".$campos_grupo[$k]['id_gpa_vs_emp_div']."' 
                    WHERE SUBSTRING(bmp.`data_sys`, 1, 10) BETWEEN '$data_inicial' AND '$data_final' AND bmp.`acao` = 'E' ";
            $campos_entrada_por_op = bancos::sql($sql);
            $linhas_entrada_por_op = count($campos_entrada_por_op);
            for($l = 0; $l < $linhas_entrada_por_op; $l++) {
                //Busco o N.º da OP através do id_baixa_manipulação do PA ...
                $sql = "SELECT `id_op` 
                        FROM `baixas_ops_vs_pas` 
                        WHERE `id_baixa_manipulacao_pa` = '".$campos_entrada_por_op[$l]['id_baixa_manipulacao_pa']."' ";
                $campos_ops = bancos::sql($sql);
                $linhas_ops = count($campos_ops);
                for($m = 0; $m < $linhas_ops; $m++) {
            ?>
                    <a href = '../../../producao/ops/alterar.php?passo=2&id_op=<?=$campos_ops[$m]['id_op'];?>&pop_up=1' class='html5lightbox'>
                        <?=$campos_ops[$m]['id_op'];?>
                    </a>
                    <br/>
            <?
                }
                $preco_item_rs      = ($campos_entrada_por_op[$l]['desc_medio_pa'] > 0) ? $campos_entrada_por_op[$l]['preco_de_lista_c_desconto'] * $campos_entrada_por_op[$l]['desc_medio_pa'] : $campos_entrada_por_op[$l]['preco_de_lista_c_desconto'];
                $total_do_item_rs   = $campos_entrada_por_op[$l]['qtde_entrada'] * $preco_item_rs;
                $total_do_grupo_rs+= $total_do_item_rs;
                
                //Acumulo nessa variável o Total da Divisão vs OC ...
                $vetor_total_por_divisao_vs_oc[$campos_empresa_divisao[$i]['id_empresa_divisao']][$campos_entrada_por_op[$l]['operacao_custo']][$campos_entrada_por_op[$l]['operacao_custo_sub']]+= $total_do_item_rs;
            }
        ?>
        </td>
        <td>
        <?
            for($l = 0; $l < $linhas_entrada_por_op; $l++) echo $campos_entrada_por_op[$l]['data_entrada'].'<br/>';
        ?>
        </td>
        <td>
        <?
            for($l = 0; $l < $linhas_entrada_por_op; $l++) {
                echo number_format($campos_entrada_por_op[$l]['qtde_entrada'], 2, ',', '.').' à R$ '.number_format($campos_entrada_por_op[$l]['preco_de_lista_c_desconto'], 2, ',', '.').'<br/>';
                $qtde_entrada+= $campos_entrada_por_op[$l]['qtde_entrada'];
            }
        ?>
        </td>
        <!--******************************NF(e)******************************-->
        <td>
        <?
            //Aqui eu localizo todas as NF de Entrada do PA dentro do Período digitado pelo Usuário ...
            $sql = "SELECT nfe.`id_nfe`, nfe.`num_nota`,  SUM(nfeh.`qtde_entregue`) AS qtde_entrada, 
                    DATE_FORMAT(SUBSTRING(nfe.`data_entrega`, 1, 10), '%d/%m/%Y') AS data_entrada, 
                    ged.`desc_medio_pa`, pa.`operacao_custo`, pa.`operacao_custo_sub`, 
                    ROUND(pa.`preco_unitario` * (1 - ged.`desc_base_a_nac` / 100) * (1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100), 2) AS preco_de_lista_c_desconto 
                    FROM `nfe_historicos` nfeh 
                    INNER JOIN `nfe` ON nfe.`id_nfe` = nfeh.`id_nfe` 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = nfeh.`id_produto_insumo` AND pa.`ativo` = '1' 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` AND ged.`id_gpa_vs_emp_div` = '".$campos_grupo[$k]['id_gpa_vs_emp_div']."' 
                    WHERE SUBSTRING(nfeh.`data_sys`, 1, 10) BETWEEN '$data_inicial' AND '$data_final' 
                    GROUP BY nfe.`id_nfe`, pa.`id_produto_acabado` ";
            $campos_entrada_por_nfe = bancos::sql($sql);
            $linhas_entrada_por_nfe = count($campos_entrada_por_nfe);
            for($l = 0; $l < $linhas_entrada_por_nfe; $l++) {
        ?>
                <a href = '../../../compras/pedidos/nota_entrada/itens/index.php?id_nfe=<?=$campos_entrada_por_nfe[$l]['id_nfe'];?>&pop_up=1' class='html5lightbox'>
                    <?=$campos_entrada_por_nfe[$l]['num_nota'];?>
                </a>
                <br/>
        <?
                $preco_item_rs      = ($campos_entrada_por_nfe[$l]['desc_medio_pa'] > 0) ? $campos_entrada_por_nfe[$l]['preco_de_lista_c_desconto'] * $campos_entrada_por_nfe[$l]['desc_medio_pa'] : $campos_entrada_por_nfe[$l]['preco_de_lista_c_desconto'];
                $total_do_item_rs   = $campos_entrada_por_nfe[$l]['qtde_entrada'] * $preco_item_rs;
                $total_do_grupo_rs+= $total_do_item_rs;
                
                //Acumulo nessa variável o Total da Divisão vs OC ...
                $vetor_total_por_divisao_vs_oc[$campos_empresa_divisao[$i]['id_empresa_divisao']][$campos_entrada_por_nfe[$l]['operacao_custo']][$campos_entrada_por_nfe[$l]['operacao_custo_sub']]+= $total_do_item_rs;
            }
        ?>
        </td>
        <td>
        <?
            for($l = 0; $l < $linhas_entrada_por_nfe; $l++) echo $campos_entrada_por_nfe[$l]['data_entrada'].'<br/>';
        ?>
        </td>
        <td>
        <?
            for($l = 0; $l < $linhas_entrada_por_nfe; $l++) {
                echo number_format($campos_entrada_por_nfe[$l]['qtde_entrada'], 2, ',', '.').' à R$ '.number_format($campos_entrada_por_nfe[$l]['preco_de_lista_c_desconto'], 2, ',', '.').'<br/>';
                $qtde_entrada+= $campos_entrada_por_nfe[$l]['qtde_entrada'];
            }
        ?>
        </td>
        <td>
            <?=segurancas::number_format($total_do_grupo_rs, 2, '.');?>
        </td>
    </tr>
<?
            //$vetor_total_por_divisao[$campos_empresa_divisao[$i]['id_empresa_divisao']]+= $total_do_grupo_rs;
            $total_de_todos_grupos_rs+= $total_do_grupo_rs;
        }//Fim do For do Grupo ...
    }//Fim do For da Família ...
}//Fim do For da Divisão ...
?>
</table>
<table width='90%' border='1' cellspacing='0' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Total por Empresa Divisão
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Empresa Divisão
        </td>
        <td>
            Industrial R$
        </td>
        <td>
            Industrial Revenda R$
        </td>
        <td>
            Revenda R$
        </td>
        <td>
            Total R$
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas_empresa_divisao; $i++) {
?>
    <tr class='linhanormal'>
        <td>
            <?=$campos_empresa_divisao[$i]['razaosocial'];?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($vetor_total_por_divisao_vs_oc[$campos_empresa_divisao[$i]['id_empresa_divisao']][0][0], 2, '.');?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($vetor_total_por_divisao_vs_oc[$campos_empresa_divisao[$i]['id_empresa_divisao']][0][1], 2, '.');?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($vetor_total_por_divisao_vs_oc[$campos_empresa_divisao[$i]['id_empresa_divisao']][1][''], 2, '.');?>
        </td>
        <td align='right'>
        <?
            $vetor_total_por_divisao = $vetor_total_por_divisao_vs_oc[$campos_empresa_divisao[$i]['id_empresa_divisao']][0][0] + $vetor_total_por_divisao_vs_oc[$campos_empresa_divisao[$i]['id_empresa_divisao']][0][1] + $vetor_total_por_divisao_vs_oc[$campos_empresa_divisao[$i]['id_empresa_divisao']][1][''];
            echo segurancas::number_format($vetor_total_por_divisao, 2, '.');
        ?>
        </td>
    </tr>
<?
        $total_industrial_rs+=          $vetor_total_por_divisao_vs_oc[$campos_empresa_divisao[$i]['id_empresa_divisao']][0][0];
        $total_industrial_revenda_rs+=  $vetor_total_por_divisao_vs_oc[$campos_empresa_divisao[$i]['id_empresa_divisao']][0][1];
        $total_revenda_rs+=             $vetor_total_por_divisao_vs_oc[$campos_empresa_divisao[$i]['id_empresa_divisao']][1][''];
    }
?>
    <tr class='linhacabecalho' align='right'>
        <td align='left'>
            Total(is) => 
        </td>
        <td>
            R$ <?=segurancas::number_format($total_industrial_rs, 2, '.');?>
        </td>
        <td>
            R$ <?=segurancas::number_format($total_industrial_revenda_rs, 2, '.');?>
        </td>
        <td>
            R$ <?=segurancas::number_format($total_revenda_rs, 2, '.');?>
        </td>
        <td>
            <font color='yellow'>
                R$ <?=segurancas::number_format($total_de_todos_grupos_rs, 2, '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='5'>
            <font color='yellow'>
                Total Revenda = (Total Revenda + 90% Industrial Revenda) => 
            </font>
            R$ <?=segurancas::number_format($total_revenda_rs + ($total_industrial_revenda_rs * 0.90), 2, '.');?>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='5'>
            <font color='yellow'>
                Total Industrial = (Total Industrial + 10% Industrial Revenda) => 
            </font>
            R$ <?=segurancas::number_format($total_industrial_rs + ($total_industrial_revenda_rs * 0.10), 2, '.');?>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='5'>
            <font color='yellow'>
                Valor OP(s) em Aberto => 
            </font>
            <?
                //Aqui eu busco todas as OP(s) que estão em Aberto ...
                $sql = "SELECT `id_op`, `id_produto_acabado`, `qtde_produzir` 
                        FROM `ops` 
                        WHERE `ativo` = 1 
                        AND `status_finalizar` = '0' ORDER BY `id_op` DESC ";
                $campos = bancos::sql($sql);
                $linhas = count($campos);
                for($i = 0; $i < $linhas; $i++) {
                    /*Aqui eu busco o somatório de todas as Entradas que foram dadas para a OP do Loop, 
                    ou seja de tudo que foi Produzido para aquela OP ...*/
                    $sql = "SELECT SUM(bop.`qtde_baixa`) AS qtde_produzido 
                            FROM `ops` 
                            INNER JOIN `baixas_ops_vs_pas` bop ON bop.`id_op` = ops.`id_op` AND bop.`id_produto_acabado` = ops.`id_produto_acabado` 
                            INNER JOIN `baixas_manipulacoes_pas` bmp ON bmp.`id_baixa_manipulacao_pa` = bop.`id_baixa_manipulacao_pa` AND bmp.`acao` = 'E' 
                            WHERE ops.`status_finalizar` = '0' 
                            AND ops.`id_op` = '".$campos[$i]['id_op']."' ";
                    $campos_produzido 	= bancos::sql($sql);
                    $qtde_restante      = $campos[$i]['qtde_produzir'] - $campos_produzido[0]['qtde_produzido'];

                    $sql = "SELECT ged.`desc_medio_pa`, 
                            ROUND(pa.`preco_unitario` * (1 - ged.`desc_base_a_nac` / 100) * (1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100), 2) AS preco_de_lista_c_desconto 
                            FROM `produtos_acabados` pa 
                            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                            WHERE pa.id_produto_acabado = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
                    $campos_preco_unit 	= bancos::sql($sql);
                    $preco_lista 	= ($campos_preco_unit[0]['desc_medio_pa'] > 0) ? $campos_preco_unit[0]['preco_de_lista_c_desconto'] * $campos_preco_unit[0]['desc_medio_pa'] : $campos_preco_unit[0]['preco_de_lista_c_desconto'];

                    if($qtde_restante > 0 && $preco_lista > 0) $valor_total_rs+= $qtde_restante * $preco_lista;
                }
                echo 'R$ '.segurancas::number_format($valor_total_rs, 2, '.');
            ?>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='5'>
            <font color='yellow'>
                Semi-Acabado (50% do Valor OP(s) em Aberto) => 
            </font>
            R$ <?=segurancas::number_format($valor_total_rs * 0.5, 2, '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='5'>
            <font color='red' size='2'>
                <b>Valor Dólar do dia R$: <?=number_format($valor_dolar_dia, 4, ',', '.');?></b>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <?
                //Essa query é feita aqui p/ que o Pop-UP acima não faça essa Query toda vez em que a tela for recarregada ...
                $sql = "SELECT COUNT(DISTINCT(pa.`id_produto_acabado`)) AS total_registro 
                        FROM `produtos_acabados` pa 
                        WHERE pa.`ativo` = '1' 
                        AND pa.`referencia` = 'ESP' 
                        AND pa.`status_custo` = '1' 
                        UNION ALL 
                        SELECT COUNT(DISTINCT(pa.`id_produto_acabado`)) AS total_registro 
                        FROM `produtos_acabados` pa 
                        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                        INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.`id_familia` IN ( 23, 24 ) 
                        WHERE pa.`ativo` = '1' 
                        AND pa.`status_custo` = '1' ";
                $campos_total_registro  = bancos::sql($sql);
                $total_registro         = $campos_total_registro[0]['total_registro'] + $campos_total_registro[1]['total_registro'];
            ?>
            <input type='button' name='cmd_atualizar_lista_PA_ESP' value='Atualizar à Lista dos P.A.s ESPs' title='Atualizar à Lista dos P.A.s ESPs' onclick="html5Lightbox.showLightbox(7, '../estoque_pa/atualizar_lista_pas_esp.php?total_registro=<?=$total_registro;?>')" class='botao'>
            <input type='button' name='cmd_atualizar_grupos' title='Atualizar Grupo(s)' value='Atualizar Grupo(s)' onclick="html5Lightbox.showLightbox(7, '../estoque_pa/atualizar_grupos.php')" class='botao'>
        </td>
    </tr>
</table>
</body>
<br>
- As Quantidades <b>Revenda</b> são das NF´s de entrada em compras dos PRAC´s (PI vs PA).
<br>
- A Quantidade <b>Industrializados</b> são das <i>Entradas de Materiais e Estoque</i> do Manipular estoque.
<br>
- Não Levamos em conta a <b> Operação de Custo</b>!!!
</html>
<!--Apresento o Botão de Imprimir p/ que o Usuário Imprima a Listagem caso desejar ...-->
<Script Language = 'JavaScript'>
    parent.document.getElementById('linha_imprimir').style.visibility = 'visible'
</Script>