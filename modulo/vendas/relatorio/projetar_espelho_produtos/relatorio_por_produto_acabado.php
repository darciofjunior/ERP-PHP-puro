<?
//Busca todos os PAS cadastradas no ERP ...
$sql = "SELECT pa.id_produto_acabado, pa.referencia, pa.discriminacao 
        FROM `produtos_acabados` pa 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
        INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.id_familia NOT IN (23, 24, 61) 
        WHERE pa.ativo = '1' 
        AND pa.status_nao_produzir = '0' ORDER BY pa.referencia, pa.discriminacao ";
$campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
$linhas = count($campos);
//Se retornar pelo menos 1 registro ...
if($linhas > 0) {
?>
    <tr class="linhadestaque" align="center">
        <td rowspan='2'>
            Referência
        </td>
        <td rowspan='2'>
            Discriminação
        </td>
<?
    for($ano = (date('Y') - 5); $ano <= date('Y'); $ano++) {
?>
        <td colspan='2'>
            <font color='blue'>
                <?=$ano;?>
            </font>
        </td>
<?
    }
?>
        <td colspan='2'>
            <?=date('Y');?> Projeção
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
<?
    for($ano = (date('Y') - 5); $ano <= date('Y'); $ano++) {
?>
        <td>
            Qtde
        </td>
        <td>
            R$
        </td>
<?
    }
?>
        <td>
            Qtde
        </td>
        <td>
            R$ 
        </td>
    </tr>
<?
	//Essa variável será utilizada na última coluna ...
        $qtde_dias_ate_hoje         = data::diferenca_data(date('Y').'-01-01', date('Y-m-d'));
        $fator_correcao_projecao 	= (365 / $qtde_dias_ate_hoje[0]);

	for($i = 0; $i < $linhas; $i++) {
            //Aqui eu busco o Total Vendido da Família nos últimos 5 anos ...
            $sql = "SELECT YEAR(pv.data_emissao) AS ano, SUM(pvi.preco_liq_final * pvi.qtde) AS total_venda_ano, SUM(pvi.qtde) AS total_qtde_ano 
                    FROM `pedidos_vendas_itens` pvi 
                    INNER JOIN `pedidos_vendas` pv ON pv.id_pedido_venda = pvi.id_pedido_venda 
                    INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pvi.id_produto_acabado 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                    AND gpa.id_familia = '".$campos[$i]['id_familia']."' 
                    WHERE YEAR(pv.data_emissao) >= '".(date('Y') - 5)."' 
                    GROUP BY YEAR(pv.data_emissao) ";
            $campos_familia = bancos::sql($sql);
            $linhas_familia = count($campos_familia);
            for($j = 0; $j < $linhas_familia; $j++) {
                $vetor_total_venda_ano[$campos[$i]['id_familia']][$campos_familia[$j]['ano']] 	= $campos_familia[$j]['total_venda_ano'];
                $vetor_total_qtde_ano[$campos[$i]['id_familia']][$campos_familia[$j]['ano']] 	= $campos_familia[$j]['total_qtde_ano'];
            }
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='right'>
        <td align='left'>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
<?
            for($ano = (date('Y') - 5); $ano <= date('Y'); $ano++) {
                $sql = "SELECT SUM(pvi.preco_liq_final * pvi.qtde) AS total_venda_ano, SUM(pvi.qtde) AS total_qtde_ano 
                        FROM `pedidos_vendas_itens` pvi
                        INNER JOIN `pedidos_vendas` pv ON pv.id_pedido_venda = pvi.id_pedido_venda AND YEAR(pv.data_emissao) = '$ano' 
                        WHERE pvi.id_produto_acabado = '".$campos[$i]['id_produto_acabado']."'";
                $campos_dados_produto = bancos::sql($sql);
?>
        <td>
                <?	
                        if($campos_dados_produto[0]['total_qtde_ano'] == 0) {
                                echo '&nbsp';
                        }else {
                                echo number_format($campos_dados_produto[0]['total_qtde_ano']);
                        }
                ?>
        </td>
        <td>
                <?	
                        if($campos_dados_produto[0]['total_venda_ano'] == 0) {
                                echo '&nbsp';
                        }else {
                                echo number_format($campos_dados_produto[0]['total_venda_ano'], 0, ',', '.');
                        }
                ?>			
        </td>
<?
            }
?>
        <td>
        <?	
            echo number_format($fator_correcao_projecao * $campos_dados_produto[0]['total_qtde_ano'], 0, ',', '.');
        ?>
        </td>
        <td>
        <?	
                echo number_format($fator_correcao_projecao * $campos_dados_produto[0]['total_venda_ano'], 0, ',', '.');
        ?>			
        </td>
    </tr>
<?
	}
?>
	<tr class="linhacabecalho" align="center">
		<td colspan='16'>
			<input type='button' name="cmd_imprimir" value="Imprimir" title="Imprimir" onclick="window.print()" style="color:purple" class="botao">
		</td>
	</tr>
</table>
<center>
	<?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?	
}else {
?>
<table width='980' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
	<tr align='center'>
		<td colspan='16'>
			<b><?=$mensagem[1];?></b>
		</td>
	</tr>
</table>
<?}?>