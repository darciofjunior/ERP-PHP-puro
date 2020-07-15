<?
if(!empty($_POST['cmb_tipo_data'])) {
    $condicao_data = ($_POST['cmb_tipo_data'] == 'emissao') ? 'pv.`data_emissao`' : 'pv.`faturar_em`';
}else {
    $condicao_data = 'pv.`data_emissao`';
}
if($_POST['chkt_livre_debito'] == 'S')  $condicao_livre_debito  = " AND pv.`livre_debito` = 'S' ";
if($_POST['chkt_expresso'] == 'S')      $condicao_expresso = " AND pv.`expresso` = 'S' ";

$vetor_familia 	= array();
		
//Aqui eu busco todas as Vendas no determinado período preenchido pelo usuário nos campos de Data ...
$sql = "SELECT IF(c.`id_pais` = 31, (pvi.`qtde` * pvi.`preco_liq_final`), (pvi.`qtde` * pvi.`preco_liq_final` * ov.`valor_dolar`)) AS total, 
        pvi.`id_produto_acabado`, pvi.`margem_lucro`, pa.`referencia` 
        FROM `pedidos_vendas_itens` pvi 
        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
        INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
        INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`liberado` = '1' AND $condicao_data BETWEEN '$data_inicial' AND '$data_final' $condicao_livre_debito $condicao_expresso 
        INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` ";
$campos	= bancos::sql($sql);
$linhas	= count($campos);
for($i = 0; $i < $linhas; $i++) {
    //Aqui eu faço uma organização de tudo o que foi vendido por família ...
    $sql = "SELECT f.`id_familia`, f.`nome`, f.`meta_mensal_vendas`, l.`login` 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
            LEFT JOIN `logins` l ON l.`id_login` = f.`id_login_gerente` 
            WHERE pa.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
    $campos_detalhes = bancos::sql($sql);
    if (!in_array($campos_detalhes[0]['nome'], $vetor_familia)) {//NAO CONSTA NO ARRAY
            $vetor_id_familia[] 	= $campos_detalhes[0]['id_familia'];
            $vetor_familia[] 	= $campos_detalhes[0]['nome'];
            $vetor_meta_mensal[] 	= $campos_detalhes[0]['meta_mensal_vendas'];
            $vetor_login[]          = $campos_detalhes[0]['login'];
    }
    if($campos[$i]['referencia'] == 'ESP') {
        $vetor_total_esp[$campos_detalhes[0]['nome']]+= 	$campos[$i]['total'];
        $total_pedidos_emitidos_esp+=                       $campos[$i]['total'];
    }else {
        $vetor_total[$campos_detalhes[0]['nome']]+= 	$campos[$i]['total'];
        $total_pedidos_emitidos_normais_linha+=             $campos[$i]['total'];
    }
    //Aqui vai armazenando o Total em R$ da Família do Loop ...
    $vetor_total_familia[$campos_detalhes[0]['nome']]+=       $campos[$i]['total'];

    $total_pedidos_emitidos_geral                  +=       $campos[$i]['total'];

    $vetor_mlmg[$campos_detalhes[0]['nome']]+= 	$campos[$i]['margem_lucro'];
    if($campos[$i]['margem_lucro'] != '-100.00') {
        $vetor_custo_ml_zero[$campos_detalhes[0]['nome']]+= $campos[$i]['total'] / (1 + $campos[$i]['margem_lucro'] / 100);
        $total_custo_ml_zero+=    							$campos[$i]['total'] / (1 + $campos[$i]['margem_lucro'] / 100);
    }
}
$total_familias = count($vetor_id_familia);
?> 
	<tr class='linhadestaque' align="center">
		<td>Fam&iacute;lia</td>
		<td>Total <br>em R$ - <font color="yellow">ESP</font></td>                
		<td>Total em R$ <br> com <font color="yellow">ESP</font></td>
		<td>%(s) Sobre <br>o Total</td>
		<td title="Margem de Lucro Média Gravada" style="cursor:help">M.L.M.G.</td>
                <td>Lucro em <br> Reais</td>
		<td>Gerente <br>da Linha</td>
		<td>Meta Mensal <br>de Vendas</td>
                <td>Previsão do <br>Período</td>
	</tr>
<?
	
	for ($i = 0; $i < $total_familias; $i++) {
?>
	<tr class='linhanormal' align="right">
		<td align="left">
			<a href="javascript:nova_janela('<?=$GLOBALS['nivel_emitidos'];/*vem do rel faturamento;*/?>rel_grupos.php?id_familia=<?=$vetor_id_familia[$i];?>&data_inicial=<?=$data_inicial;?>&data_final=<?=$data_final;?>', 'CONSULTAR', '', '', '', '', '700', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" class="link">
				<?=$vetor_familia[$i];?>
			</a>
			&nbsp;-&nbsp;
			<a href="javascript:nova_janela('rel_venda_best_cliente_familia.php?id_familia=<?=$vetor_id_familia[$i];?>&data_inicial=<?=$data_inicial;?>&data_final=<?=$data_final;?>', 'Relatorio', '', '', '', '', '600', '900', 'c', 'c', '', '', 's', 's', '', '', '')" style="color:yellow">
				<img src="../../../../imagem/propriedades.png" title="Relatório de Maiores Compradores de Cliente vs Família" alt="Relatório de Maiores Compradores de Cliente vs Família" border="0">
			</a>
		</td>
		<td>
			<?=number_format($vetor_total_esp[$vetor_familia[$i]], 2, ',', '.');?>
		</td>                  
		<td>
			<?=number_format($vetor_total_familia[$vetor_familia[$i]], 2, ',', '.');?>
                        <input type="hidden" id="hdd_total_reais<?=$i?>" value="<?=number_format($vetor_total_familia[$vetor_familia[$i]], 2, ',', '.');?>">
		</td>             
		<td>
		<?
			$porc_parcial= ($vetor_total_familia[$vetor_familia[$i]] / $total_pedidos_emitidos_geral) * 100;
			$porc_total+= $porc_parcial;
			echo number_format($porc_parcial, 2, ',', '.').'%';
		?>
		</td>
		<td>
		<?
			$mlmg_pa = ($vetor_total_familia[$vetor_familia[$i]] / $vetor_custo_ml_zero[$vetor_familia[$i]] - 1) * 100;
			echo number_format($mlmg_pa, 2, ',', '.');
		?> %
		</td>
                <td>
		<?
                    $custo_ml_zero = round($vetor_total_familia[$vetor_familia[$i]], 2) / (1 + round($mlmg_pa, 2) / 100);
                    $lucro_rs      = round($vetor_total_familia[$vetor_familia[$i]], 2) - $custo_ml_zero;
                    echo number_format($lucro_rs, 2, ',', '.');                                                                                                                                                                                                                                                                      
                    $lucro_total_reais+= $lucro_rs;
		?>
		</td>
		<td align="center">
			<?if(!empty($vetor_login[$i])) {echo $vetor_login[$i];}else {echo '&nbsp;';}?>
		</td>
		<td>
			<?=number_format($vetor_meta_mensal[$i], 2, ',', '.');?>
		</td>
                <td align="center">
                    <input type="text" id="txt_previsao_periodo<?=$i;?>" style="text-align:right" class="textdisabled" disabled>
		</td>
	</tr>
<?
	}
?>
	<tr class='linhanormal' align="right">
            <td>
                <font color='red' size='2'>
                    <b>Totais => </b>
                </font>
            </td>
            <td>
                <font color='red' size='2'>
                    <b><?=number_format($total_pedidos_emitidos_esp, 2, ',', '.');?></b>
                </font>
            </td>
            <td>
                <font color='red' size='2'>
                    <b><?=number_format($total_pedidos_emitidos_geral, 2, ',', '.');?></b>
                </font>
            </td>
            <td>
                <font color='red' size='2'>
                    <?=number_format($porc_total, 2, ',', '.').'%';?>
                </font>
            </td>
            <td>
                <font color='red' size='2'>
                    <?=number_format(($total_pedidos_emitidos_geral / $total_custo_ml_zero - 1) * 100, 2, ',', '.').'%';?>
                </font>
            </td>
            <td>
                <font color='red' size='2'>
                    <?=number_format($lucro_total_reais,2 , ',', '.');?>
                </font>
            </td>
            <td colspan='2'>
                &nbsp;
            </td>
            <td align="center">
                <input type="text" id="txt_total_previsao_periodo" style="text-align:right" class="textdisabled" disabled>
            </td>
	</tr>
	<tr class="linhanormal">
		<td colspan="9">
			<font size="2" color="red">
				<b>PEDIDOS EXPORT USAM O U$ DO ORÇAMENTO.</b>
			</font>
			&nbsp;-&nbsp;
			Valor Dólar do dia R$: <?=number_format($valor_dolar_dia, 4, ',', '.');?>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="9">
			<input type="submit" name="cmd_atualizar" value="Atualizar Relatório" title='Atualizar Relatório' id="cmd_atualizar" class="botao">
		</td>
	</tr>
	<tr align="left">
		<td colspan="9">
			<pre align="left">
Caso dê alguma Diferença na MLMG Total analisar os seguintes critérios.
	- Se a data é do mesmo período
	- Se foi desativado recentemente alguma família.
			</pre>
		</td>
	</tr>
</table>
<?
//Aqui eu busco todos os Gerentes de Linha das Famílias ...
	$sql = "SELECT DISTINCT(f.id_login_gerente) AS id_login_gerente, l.login 
                FROM `familias` f 
                INNER JOIN `logins` l ON l.id_login = f.id_login_gerente 
                WHERE f.`ativo` = '1' 
                AND f.`id_login_gerente` > '0' ORDER BY l.login ";
	$campos_gerentes_familia = bancos::sql($sql);
	$linhas_gerentes_familia = count($campos_gerentes_familia);
	if($linhas_gerentes_familia > 0) {
?>
<table width='90%' border='1' cellspacing ='1' cellpadding='1' align='center'>
	<tr class="linhacabecalho" align='center'>
		<td colspan="7">
			Resumo por Gerente
		</td>
	</tr>
	<tr class='linhadestaque' align="center">
		<td>Supervisor</td>
		<td>Família</td>
		<td>Meta</td>
		<td>Total</td>
		<td>Vendas R$</td>
		<td>% Vendido</td>
		<td>Prêmio</td>
	</tr>
<?
		for($i = 0; $i < $linhas_gerentes_familia; $i++) {
			$total_meta_gerente_linha = 0;//Zero p/ não herdar valores ...
			$total_vendido_gerente_linha = 0;//Zero p/ não herdar valores ...
			//Aqui eu busco todas as Famílias do Representante Corrente ...
			$sql = "SELECT meta_mensal_vendas, nome 
					FROM `familias` 
					WHERE `id_login_gerente` = '".$campos_gerentes_familia[$i]['id_login_gerente']."' ORDER BY nome ";
			$campos_familia = bancos::sql($sql);
			$linhas_familia = count($campos_familia);
			for($j = 0; $j < $linhas_familia; $j++) {
?>
	<tr class='linhanormal' align="right">
		<td align="center">
			<?if($j ==0) {echo strtoupper($campos_gerentes_familia[$i]['login']);}else{echo '&nbsp;';}?>
		</td>
		<td align="left">
			<?=$campos_familia[$j]['nome'];?>
		</td>
		<td>
		<?
			echo number_format($campos_familia[$j]['meta_mensal_vendas'], 2, ',', '.');
			$total_meta_gerente_linha+= $campos_familia[$j]['meta_mensal_vendas'];
		?>
		</td>
		<td>
                    ???
		</td>
		<td>
		<?
			$total_vendido_familia = $vetor_total_familia[$campos_familia[$j]['nome']];
			echo number_format($total_vendido_familia, 2, ',', '.');
			$total_vendido_gerente_linha+= $total_vendido_familia;
		?>
		</td>
		<td>
		<?
			if($campos_familia[$j]['meta_mensal_vendas'] > 0) {
				$porc_vendida_familia = ($total_vendido_familia / $campos_familia[$j]['meta_mensal_vendas']) * 100;
				//$porc_total+= $porc_parcial;
				echo number_format($porc_vendida_familia, 2, ',', '.').'%';
			}else {
				echo '&nbsp;';
			}
		?>
		</td>
		<td>&nbsp;</td>
	</tr>
<?
			}
?>
	<tr class="linhacabecalho" align="right">
		<td colspan="2">
			&nbsp;
		</td>
		<td>
			<?=number_format($total_meta_gerente_linha, 2, ',', '.');?>
		</td>
		<td>
			&nbsp;
		</td>
		<td>
			<?=number_format($total_vendido_gerente_linha, 2, ',', '.');?>
		</td>
		<td>
		<?
			$total_porc_vendido_gerente_linha = ($total_vendido_gerente_linha / $total_meta_gerente_linha) * 100;
			echo number_format($total_porc_vendido_gerente_linha, 2, ',', '.');
		?>
		</td>
		<td>
		<?
			if($total_porc_vendido_gerente_linha >= 90) {
				if($total_porc_vendido_gerente_linha >= 100) {
					echo "<font color='red' size='2'>100%</font>";
				}else {
					echo "<font color='red' size='2'>50%</font>";
				}
			}else {
				echo "<font color='red' size='2'>0</font>";
			}
		?>
		</td>
	<tr>
<?
		}
?>
</table>
<?
	}
?>
</body>
</html>
<Script Language = 'JavaScript'>
//Coloquei essa função aqui em baixo porque não tenho Tag <head> ...
function calcular_periodo() {
    var linhas              = '<?=$total_familias;?>'
    var previsao_periodo    = 0
    var dias_uteis_ate_hoje = '<?=$qtde_dias_uteis_ate_hoje;?>'
    if(dias_uteis_ate_hoje != '') {
        var dias_uteis_mes = '<?=number_format(genericas::variavel(24), 0);?>'   
        for(var i = 0; i < linhas; i++) {
            document.getElementById('txt_previsao_periodo'+i).value = eval(strtofloat(document.getElementById('hdd_total_reais'+i).value)) / eval(dias_uteis_ate_hoje) * dias_uteis_mes
            document.getElementById('txt_previsao_periodo'+i).value = arred(document.getElementById('txt_previsao_periodo'+i).value, 2, 1)
            
            previsao_periodo+= eval(strtofloat(document.getElementById('txt_previsao_periodo'+i).value))
        }
    }else {
        for(var i = 0; i < linhas; i++) {
            document.getElementById('txt_previsao_periodo'+i).value = ''
        }
    }
    document.getElementById('txt_total_previsao_periodo').value = previsao_periodo
    document.getElementById('txt_total_previsao_periodo').value = arred(document.getElementById('txt_total_previsao_periodo').value, 2, 1)
}
calcular_periodo()//Aqui faço papel de Onload ...
</Script>