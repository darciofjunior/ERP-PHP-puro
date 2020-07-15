<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
/************************************************************************************/
/*Objetivo: Calcular MMV dos últimos 12 meses por família dos principais Clientes ***/

/*25% dos Clientes compram 75% das Vendas, estamos imaginando que temos 1.500 clientes 
ativos, 25% desses 1.500 = 375 (adotaremos 100) seriam os maiores clientes, entao o nosso 
cálculo será 

***** em R$ => 75% da Somatória(Qtde * Preco Unitario) por família é o valor Total que 
os 25% melhores clientes compram ... Posteriormente p/ achar a média anual por Cliente 
dividimos por 100 e divido por 2 pois utilizei 24 meses nesse Filtro abaixo ...*/

//Busca o Total Faturado "NF(s)" por Grupo do PA no último 1 ano ...
/* * ***************************************************************************************** */
/* Observação: p/ essas Querys abaixo, é interessante estar fazendo 2 Querys com outra dentro
  do for porque se ajunto tudas em uma Query só o Sistema demora muito p/ responder por causa
  das amarrações ... */
/* * ***************************************************************************************** */
$sql = "SELECT nfsi.id_produto_acabado, nfsi.qtde, (nfsi.qtde * nfsi.valor_unitario) AS valor_total 
        FROM `nfs` 
        INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf 
        WHERE nfs.`data_emissao` BETWEEN '".(date('Y') - 1).date('-m-d')."' AND '".date('Y-m-d')."' ";
$campos_nfs_itens = bancos::sql($sql);
$linhas_nfs_itens = count($campos_nfs_itens);
for($i = 0; $i < $linhas_nfs_itens; $i++) {
    $sql = "SELECT gpa.id_grupo_pa 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            WHERE pa.`id_produto_acabado` = '".$campos_nfs_itens[$i]['id_produto_acabado']."' LIMIT 1 ";
    $campos_grupo_pa = bancos::sql($sql);
    $vetor_fat_grupo_pa_1ano_qtde[$campos_grupo_pa[0]['id_grupo_pa']]+= ($campos_nfs_itens[$i]['qtde'] * 0.75 / 100);
    $vetor_fat_grupo_pa_1ano_rs[$campos_grupo_pa[0]['id_grupo_pa']]+= ($campos_nfs_itens[$i]['valor_total'] * 0.75 / 100);
}
/***************************************************************************************/

//Aqui eu busco o Total Vendido por Grupo do PA do id_familia passada por parâmetro nos últimos 6 anos ...
$sql = "SELECT gpa.id_grupo_pa, YEAR(pv.data_emissao) AS ano, SUM(pvi.preco_liq_final * pvi.qtde) AS total_venda_ano, SUM(pvi.qtde) AS total_qtde_ano 
        FROM `pedidos_vendas_itens` pvi 
        INNER JOIN `pedidos_vendas` pv ON pv.id_pedido_venda = pvi.id_pedido_venda AND pv.id_cliente = '$_POST[id_cliente]' 
        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pvi.id_produto_acabado 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
        INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.`id_familia` = '$_POST[id_familia]' 
        WHERE YEAR(pv.data_emissao) >= '".(date('Y') - 5)."' 
        GROUP BY YEAR(pv.data_emissao), gpa.id_grupo_pa ";
$campos_grupo_pa = bancos::sql($sql);
$linhas_grupo_pa = count($campos_grupo_pa);
for($i = 0; $i < $linhas_grupo_pa; $i++) {
    $vetor_grupo_pa_total_venda_ano[$campos_grupo_pa[$i]['id_grupo_pa']][$campos_grupo_pa[$i]['ano']] 	= $campos_grupo_pa[$i]['total_venda_ano'];
    $vetor_grupo_pa_total_qtde_ano[$campos_grupo_pa[$i]['id_grupo_pa']][$campos_grupo_pa[$i]['ano']] 	= $campos_grupo_pa[$i]['total_qtde_ano'];
}

//Aqui eu busco o País do Cliente, p/ saber com qual moeda será feita a Projeção ...
$sql = "SELECT id_pais 
        FROM `clientes` 
        WHERE `id_cliente` = '$_POST[cmb_cliente]' LIMIT 1 ";
$campos_pais    = bancos::sql($sql);
//Se o Cliente for do Brasil então R$, se estrangeiro é em Dólar ...
$moeda          = ($campos_pais[0]['id_pais'] == 31) ? 'R$ ' : 'U$ ';

//Busca todos os Grupos cadastradas no ERP do id_familia passado por parâmetro ...
$sql = "SELECT id_grupo_pa, nome 
        FROM `grupos_pas` 
        WHERE `id_familia` = '$_POST[id_familia]' 
        AND `ativo` = '1' ORDER BY nome ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<!--*****************************************************************************************************************-->
<table width='100%' border='1' cellspacing='0' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Visualizando Detalhes por Grupo da Fam&iacute;lia => 
            <?
                $sql = "SELECT nome 
                        FROM `familias` 
                        WHERE `id_familia` = '$_POST[id_familia]' LIMIT 1 ";
                $campos_familia = bancos::sql($sql);
            ?>
            <font color='yellow'>
                <?=$campos_familia[0]['nome'];?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            Grupo do PA
        </td>
        <td colspan='2'>
            M&eacute;dia dos <br/>Melhores Clientes
        </td>
<?
    for($ano = (date('Y') - 5); $ano <= date('Y'); $ano++) {
?>
        <td>
            <font color='blue'>
                <?=$ano;?>
            </font>
        </td>
<?
    }
?>
        <td colspan='2'>
            <?=date('Y');?> Proje&ccedil;&atilde;o
            (M&eacute;dia &Uacute;lt. 5 anos)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Qtde
        </td>
        <td>
            <?=$moeda;?>
        </td>
<?
    for($ano = (date('Y') - 5); $ano <= date('Y'); $ano++) {
?>
        <td>
            Qtde
        </td>
        <!--<td>
            <?=$moeda;?>
        </td>-->
<?
    }
?>
        <td>
            Qtde
        </td>
        <td>
            <?=$moeda;?>
        </td>
    </tr>
<?

//Essas variáveis serão utilizadas + abaixo dentro do Loop ...
$qtde_de_anos_para_dividir              = 0;
$qtde_total_por_grupo_pa_ultimos_5anos  = 0;

for($i = 0; $i < $linhas; $i++) {
?>

    <tr class='linhanormaldestaque' align='right'>
        <td width='30%' align='left'>
            <b><?=utf8_encode($campos[$i]['nome']);?></b>
        </td>
        <td width='7%'>
            <?=number_format($vetor_fat_grupo_pa_1ano_qtde[$campos[$i]['id_grupo_pa']], 2, ',', '.');?>
        </td>
        <td width='7%'>
            <?=number_format($vetor_fat_grupo_pa_1ano_rs[$campos[$i]['id_grupo_pa']], 2, ',', '.');?>
        </td>
<?
        for($ano = (date('Y') - 5); $ano <= date('Y'); $ano++) {
?>
        <td width='7%'>
        <?	
            if($vetor_grupo_pa_total_qtde_ano[$campos[$i]['id_grupo_pa']][$ano] == 0) {
                echo '&nbsp';
            }else {
                echo number_format($vetor_grupo_pa_total_qtde_ano[$campos[$i]['id_grupo_pa']][$ano], 0, ',', '.');

                //Só não contabilizo o último ano nessa variável pq a intenção é tirarmos uma média exata ...
                if($ano < date('Y')) $qtde_de_anos_para_dividir++;
            }
        ?>
        </td>
        <!--<td width='7%'>
        <?	
            /*if($vetor_grupo_pa_total_venda_ano[$campos[$i]['id_grupo_pa']][$ano] == 0) {
                echo '&nbsp';
            }else {
                echo number_format($vetor_grupo_pa_total_venda_ano[$campos[$i]['id_grupo_pa']][$ano], 0, ',', '.');
            }*/
        ?>			
        </td>-->
<?
            //Só não contabilizo o último ano nesse somatório pq a intenção é tirarmos uma média exata ...
            if($ano < date('Y')) $qtde_total_por_grupo_pa_ultimos_5anos+= $vetor_grupo_pa_total_qtde_ano[$campos[$i]['id_grupo_pa']][$ano];

        }
?>
        <td width='7%'>
        <?	
            if($qtde_de_anos_para_dividir == 0) {
                $qtde_projetada_ano_atual = 0;
            }else {
                $qtde_projetada_ano_atual = ($qtde_total_por_grupo_pa_ultimos_5anos / $qtde_de_anos_para_dividir);
            }
            echo number_format($qtde_projetada_ano_atual, 0, ',', '.');
        ?>
        </td>
        <td width='7%'>
        <?
            //Cálculo o Pço Unitário por Família, baseado na Qtde Total Vendida e Valor Total Vendido do Ano Atual ...
            $preco_unitario_vendido             = $vetor_grupo_pa_total_venda_ano[$campos[$i]['id_grupo_pa']][date('Y')] / $vetor_grupo_pa_total_qtde_ano[$campos[$i]['id_grupo_pa']][date('Y')];
            $projecao_para_vender_no_atual_atual+= $qtde_projetada_ano_atual * $preco_unitario_vendido;
            echo number_format($qtde_projetada_ano_atual * $preco_unitario_vendido, 2, ',', '.');
        ?>
        </td>
    </tr>
<?
//Limpo essas variáveis p/ não herdar valores e atrapalhar nos Próximos Loops ...
    $qtde_de_anos_para_dividir              = 0 ;
    $qtde_total_por_grupo_pa_ultimos_5anos   = 0;
}
?>
</table>
<!--*****************************************************************************************************************-->