<?
/************************************************************************************/
/*Objetivo: Calcular MMV dos últimos 12 meses por família dos principais Clientes ***/

/*25% dos Clientes compram 75% das Vendas, estamos imaginando que temos 1.500 clientes 
ativos, 25% desses 1.500 = 375 (adotaremos 100) seriam os maiores clientes, entao o nosso 
cálculo será 

***** em R$ => 75% da Somatória(Qtde * Preco Unitario) por família é o valor Total que 
os 25% melhores clientes compram ... Posteriormente p/ achar a média anual por Cliente 
dividimos por 100 e divido por 2 pois utilizei 24 meses nesse Filtro abaixo ...*/

//Busca o Total Faturado "NF(s)" por Família no último 1 ano ...
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
    $sql = "SELECT gpa.id_familia 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            WHERE pa.`id_produto_acabado` = '".$campos_nfs_itens[$i]['id_produto_acabado']."' LIMIT 1 ";
    $campos_familia = bancos::sql($sql);
    $vetor_fat_familia_1ano_qtde[$campos_familia[0]['id_familia']]+= ($campos_nfs_itens[$i]['qtde'] * 0.75 / 100);
    $vetor_fat_familia_1ano_rs[$campos_familia[0]['id_familia']]+= ($campos_nfs_itens[$i]['valor_total'] * 0.75 / 100);
}
/***************************************************************************************/

//Aqui eu busco o País do Cliente, p/ saber com qual moeda será feita a Projeção ...
$sql = "SELECT id_pais 
        FROM `clientes` 
        WHERE `id_cliente` = '$_POST[cmb_cliente]' LIMIT 1 ";
$campos_pais    = bancos::sql($sql);
//Se o Cliente for do Brasil então R$, se estrangeiro é em Dólar ...
$moeda          = ($campos_pais[0]['id_pais'] == 31) ? 'R$ ' : 'U$ ';

//Busca todas as Famílias cadastradas no ERP, com exceção dos Componentes e Mão de Obras ...
$sql = "SELECT id_familia, nome 
        FROM `familias` 
        WHERE `ativo` = '1' 
        AND `id_familia` NOT IN (23, 24) ORDER BY nome ";
$campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
$linhas = count($campos);
//Se retornar pelo menos 1 registro ...
if($linhas > 0) {
?>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            Família
        </td>
        <td colspan='2'>
            Média dos <br/>Melhores Clientes
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
            <?=date('Y');?> Projeção<br/>
            M&eacute;dia &Uacute;lt. 5 anos
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
    $qtde_total_por_familia_ultimos_5anos   = 0;

    for($i = 0; $i < $linhas; $i++) {
        //Aqui eu busco o Total Vendido da Família nos últimos 6 anos ...
        $sql = "SELECT YEAR(pv.data_emissao) AS ano, SUM(pvi.preco_liq_final * pvi.qtde) AS total_venda_ano, SUM(pvi.qtde) AS total_qtde_ano 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN `pedidos_vendas` pv ON pv.id_pedido_venda = pvi.id_pedido_venda AND pv.id_cliente = '$_POST[cmb_cliente]' 
                INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pvi.id_produto_acabado 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.id_familia = '".$campos[$i]['id_familia']."' 
                WHERE YEAR(pv.data_emissao) >= '".(date('Y') - 5)."' 
                GROUP BY YEAR(pv.data_emissao) ";
        $campos_familia = bancos::sql($sql);
        $linhas_familia = count($campos_familia);
        for($j = 0; $j < $linhas_familia; $j++) {
            $vetor_familia_total_venda_ano[$campos[$i]['id_familia']][$campos_familia[$j]['ano']] 	= $campos_familia[$j]['total_venda_ano'];
            $vetor_familia_total_qtde_ano[$campos[$i]['id_familia']][$campos_familia[$j]['ano']] 	= $campos_familia[$j]['total_qtde_ano'];
        }
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='right'>
        <td width='30%' align='left'>
            <a href="javascript:visualizar_detalhes_por_grupo('<?=$i;?>', '<?=$_POST['cmb_cliente'];?>', '<?=$campos[$i]['id_familia'];?>')" title='Visualizar Detalhes por Grupo' class='link'>
                <?=$campos[$i]['nome'];?>
                <img src='../../../../imagem/seta_abaixo.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td width='7%'>
            <?=number_format($vetor_fat_familia_1ano_qtde[$campos[$i]['id_familia']], 0, ',', '.');?>
        </td>
        <td width='7%'>
            <?=number_format($vetor_fat_familia_1ano_rs[$campos[$i]['id_familia']], 2, ',', '.');?>
        </td>
<?
        for($ano = (date('Y') - 5); $ano <= date('Y'); $ano++) {
?>
        <td width='7%'>
        <?	
            if($vetor_familia_total_qtde_ano[$campos[$i]['id_familia']][$ano] == 0) {
                echo '&nbsp';
            }else {
                echo number_format($vetor_familia_total_qtde_ano[$campos[$i]['id_familia']][$ano], 0, ',', '.');
                
                //Só não contabilizo o último ano nessa variável pq a intenção é tirarmos uma média exata ...
                if($ano < date('Y')) $qtde_de_anos_para_dividir++;
            }
        ?>
        </td>
        <!--<td width='7%'>
        <?	
            /*if($vetor_familia_total_venda_ano[$campos[$i]['id_familia']][$ano] == 0) {
                echo '&nbsp';
            }else {
                echo number_format($vetor_familia_total_venda_ano[$campos[$i]['id_familia']][$ano], 0, ',', '.');
            }*/
        ?>			
        </td>-->
<?
            //Só não contabilizo o último ano nesse somatório pq a intenção é tirarmos uma média exata ...
            if($ano < date('Y')) $qtde_total_por_familia_ultimos_5anos+= $vetor_familia_total_qtde_ano[$campos[$i]['id_familia']][$ano];
        }
?>
        <td width='7%'>
        <?
            if($qtde_de_anos_para_dividir == 0) {
                $qtde_projetada_ano_atual = 0;
            }else {
                $qtde_projetada_ano_atual = ($qtde_total_por_familia_ultimos_5anos / $qtde_de_anos_para_dividir);
            }
            echo number_format($qtde_projetada_ano_atual, 0, ',', '.');
        ?>
        </td>
        <td width='7%'>
            <?
                //Cálculo o Pço Unitário por Família, baseado na Qtde Total Vendida e Valor Total Vendido do Ano Atual ...
                $preco_unitario_vendido             = $vetor_familia_total_venda_ano[$campos[$i]['id_familia']][date('Y')] / $vetor_familia_total_qtde_ano[$campos[$i]['id_familia']][date('Y')];
                $projecao_para_vender_no_atual_atual+= $qtde_projetada_ano_atual * $preco_unitario_vendido;
                echo number_format($qtde_projetada_ano_atual * $preco_unitario_vendido, 2, ',', '.');
            ?>
        </td>
    </tr>
    <!--*******************Visualização por Grupo*******************-->
    <tr id='linha_detalhes_por_grupo<?=$i;?>' style='visibility: hidden'>
        <td colspan='17'>
            <div id='div_detalhes_por_grupo<?=$i;?>'></div>
        </td>
    </tr>
    <!--************************************************************-->
<?
        //Limpo essas variáveis p/ não herdar valores e atrapalhar nos Próximos Loops ...
        $qtde_de_anos_para_dividir              = 0 ;
	$qtde_total_por_familia_ultimos_5anos   = 0;
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name="cmd_imprimir" value="Imprimir" title="Imprimir" onclick="window.print()" style="color:purple" class="botao">
        </td>
        <td colspan='2' align='right'>
            <font color='yellow'>
                Valor Total => 
            </font>
        </td>
        <td colspan='2' align='right'>
            <?=$moeda.number_format($projecao_para_vender_no_atual_atual, 2, ',', '.');?>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    /*Atualizo no cadastro de Clientes a Data em que foi gerada a última Análise dos PA(s) Vendidos pelo Vendedor 
    p/ o determinado Cliente selecionado , o Wilson diretor que batizou isso como Espelho ??? rsrs ...*/
    $sql = "UPDATE `clientes` SET `data_ultimo_espelho_produtos` = '".date('Y-m-d H:i:s')."' WHERE `id_cliente` = '$_POST[cmb_cliente]' LIMIT 1 ";
    bancos::sql($sql);
}else {
?>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='17'>
            <?=$mensagem[1];?>
        </td>
    </tr>
</table>
<?}?>