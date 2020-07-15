<?
//Busca todos os Grupos do PA vs Empresas Divisões cadastrados no ERP, com exceção dos Componentes e Mão de Obras ...
$sql = "SELECT ged.id_gpa_vs_emp_div, CONCAT(gpa.nome, ' (',  ed.razaosocial, ')') AS grupo_empresa_divisao 
        FROM `gpas_vs_emps_divs` ged 
        INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
        INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.id_familia NOT IN (23, 24, 61) 
        WHERE gpa.`ativo` = '1' ORDER BY gpa.nome, ed.razaosocial ";
$campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
$linhas = count($campos);
//Se retornar pelo menos 1 registro ...
if($linhas > 0) {
?>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            Grupo PA vs Empresa Divisão
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
            //Aqui eu busco o Total Vendido do Grupo vs Empresa Divisão nos últimos 5 anos ...
            $sql = "SELECT YEAR(pv.data_emissao) AS ano, SUM(pvi.preco_liq_final * pvi.qtde) AS total_venda_ano, SUM(pvi.qtde) AS total_qtde_ano 
                    FROM `pedidos_vendas_itens` pvi 
                    INNER JOIN `pedidos_vendas` pv ON pv.id_pedido_venda = pvi.id_pedido_venda 
                    INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pvi.id_produto_acabado 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div AND ged.id_gpa_vs_emp_div = '".$campos[$i]['id_gpa_vs_emp_div']."' 
                    WHERE YEAR(pv.data_emissao) >= '".(date('Y') - 5)."' 
                    GROUP BY YEAR(pv.data_emissao) ";
            $campos_grupo_pa = bancos::sql($sql);
            $linhas_grupo_pa = count($campos_grupo_pa);
            for($j = 0; $j < $linhas_grupo_pa; $j++) {
                $vetor_total_venda_ano[$campos[$i]['id_gpa_vs_emp_div']][$campos_grupo_pa[$j]['ano']] 	= $campos_grupo_pa[$j]['total_venda_ano'];
                $vetor_total_qtde_ano[$campos[$i]['id_gpa_vs_emp_div']][$campos_grupo_pa[$j]['ano']] 	= $campos_grupo_pa[$j]['total_qtde_ano'];
            }
?>
	<tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='right'>
		<td align='left'>
			<?=$campos[$i]['grupo_empresa_divisao'];?>
		</td>
<?
		for($ano = (date('Y') - 5); $ano <= date('Y'); $ano++) {
?>
		<td>
		<?	
			if($vetor_total_qtde_ano[$campos[$i]['id_gpa_vs_emp_div']][$ano] == 0) {
				echo '&nbsp';
			}else {
				echo number_format($vetor_total_qtde_ano[$campos[$i]['id_gpa_vs_emp_div']][$ano], 0, ',', '.');
			}
		?>
		</td>
		<td>
		<?	
			if($vetor_total_venda_ano[$campos[$i]['id_gpa_vs_emp_div']][$ano] == 0) {
				echo '&nbsp';
			}else {
				echo number_format($vetor_total_venda_ano[$campos[$i]['id_gpa_vs_emp_div']][$ano], 0, ',', '.');
			}
		?>			
		</td>					
<?
		}
?>
		<td>
		<?	
			echo number_format($fator_correcao_projecao * $vetor_total_qtde_ano[$campos[$i]['id_gpa_vs_emp_div']][date('Y')], 0, ',', '.');
		?>
		</td>
		<td>
		<?	
			echo number_format($fator_correcao_projecao * $vetor_total_venda_ano[$campos[$i]['id_gpa_vs_emp_div']][date('Y')], 0, ',', '.');
		?>			
		</td>
	</tr>
<?
	}
?>
	<tr class="linhacabecalho" align="center">
		<td colspan='15'>
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
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='17'>
            <?=$mensagem[1];?>
        </td>
    </tr>
</table>
<?}?>