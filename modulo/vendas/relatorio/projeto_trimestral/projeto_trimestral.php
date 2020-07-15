<?
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pop_up             = $_POST['pop_up'];
    $representante      = $_POST['representante'];
    $cmb_representante 	= $_POST['cmb_representante'];
    $cmb_ano            = $_POST['cmb_ano'];
    $cmb_trimestre      = $_POST['cmb_trimestre'];
}else {
    $pop_up             = $_GET['pop_up'];
    $representante      = $_GET['representante'];
    $cmb_representante 	= $_GET['cmb_representante'];
    $cmb_ano            = $_GET['cmb_ano'];
    $cmb_trimestre      = $_GET['cmb_trimestre'];
}

require('../../../../lib/segurancas.php');
if(empty($pop_up)) require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../../');

//Sugestão pra quando acabar de carregar a Tela ...
$ano = (empty($cmb_ano)) ? date('Y') : $cmb_ano;

if(empty($cmb_trimestre)) {
    if(date('m') <= 3) {//Significa que são meses pertinentes ao 1º Trimestre ...
        $trimestre = 1;
    }else if(date('m') <= 6) {//Significa que são meses pertinentes ao 2º Trimestre ...
        $trimestre = 2;
    }else if(date('m') <= 9) {//Significa que são meses pertinentes ao 3º Trimestre ...
        $trimestre = 3;
    }else {
        $trimestre = 4;
    }
}else {
    $trimestre = $cmb_trimestre;
}

if($trimestre == 1) {//Significa que são meses pertinentes ao 1º Trimestre ...
    $mes_inicial = '01';
    $mes_final = '03';
    $mes_rotulo1 = 'Janeiro';
    $mes_rotulo2 = 'Fevereiro';
    $mes_rotulo3 = 'Março';
    $selected1	 = 'selected';
    $periodo = '01/01/'.$ano.' à 31/03/'.$ano;
    $condicao_periodo_trimestre = " AND pv.data_emissao BETWEEN '".$ano."-01-01' AND '".$ano."-03-31' ";
}else if($trimestre == 2) {//Significa que são meses pertinentes ao 2º Trimestre ...
    $mes_inicial = '04';
    $mes_final = '06';
    $mes_rotulo1 = 'Abril';
    $mes_rotulo2 = 'Maio';
    $mes_rotulo3 = 'Junho';
    $selected2	 = 'selected';
    $periodo = '01/04/'.$ano.' à 30/06/'.$ano;
    $condicao_periodo_trimestre = " AND pv.data_emissao BETWEEN '".$ano."-04-01' AND '".$ano."-06-30' ";
}else if($trimestre == 3) {//Significa que são meses pertinentes ao 3º Trimestre ...
    $mes_inicial = '07';
    $mes_final = '09';
    $mes_rotulo1 = 'Julho';
    $mes_rotulo2 = 'Agosto';
    $mes_rotulo3 = 'Setembro';
    $selected3	 = 'selected';
    $periodo = '01/07/'.$ano.' à 30/09/'.$ano;
    $condicao_periodo_trimestre = " AND pv.data_emissao BETWEEN '".$ano."-07-01' AND '".$ano."-09-30' ";
}else {//Significa que são meses pertinentes ao 4º Trimestre ...
    $mes_inicial = 10;
    $mes_final = 12;
    $mes_rotulo1 = 'Outubro';
    $mes_rotulo2 = 'Novembro';
    $mes_rotulo3 = 'Dezembro';
    $selected4	 = 'selected';
    $periodo = '01/10/'.$ano.' à 31/12/'.$ano;
    $condicao_periodo_trimestre = " AND pv.data_emissao BETWEEN '".$ano."-10-01' AND '".$ano."-12-31' ";
}
?>
<html>
<head>
<title>.:: Projeções Realizadas ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href='../../../../css/layout.css' type='text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(typeof(document.form.cmb_representante) == 'object') {
        if(document.form.cmb_representante.value == '') {
            alert('SELECIONE UM REPRESENTANTE !')
            document.form.reset()
            return false
        }else {
            document.form.submit()
        }
    }else {
        document.form.submit()
    }
}
</Script>
</head>
<body>
<form name='form' action='' method='post'>
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<table border="1" width='95%' align='center' cellspacing ='1' cellpadding='1'>
	<tr class='linhacabecalho' align='center'>
		<td colspan='10'>
			Projeções Realizadas - 
			<font color='yellow' size='-1'>
				Representante: 
			</font>
			<?
//Verifico se o Vendedor foi passado por Parâmetro ...
				if(!empty($representante)) {
					$sql = "Select nome_fantasia 
							from representantes 
							where id_representante = '$representante' limit 1 ";
					$campos_representante = bancos::sql($sql);
					echo $campos_representante[0]['nome_fantasia'];
			?>
				<input type="hidden" name="representante" value="<?=$representante;?>">
			<?
//Se não foi passado nenhum Representante por parâmetro, então eu apresento a combo abaixo ...
				}else {
			?>
					<select name="cmb_representante" title="Selecione o Representante" onchange="return validar()" class="combo">
			<?
				$sql = "SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
						FROM `representantes` 
						WHERE `ativo` = '1' ORDER BY nome_fantasia ";
						echo combos::combo($sql, $cmb_representante);
			?>
					</select>
			<?
				}
			?>
			&nbsp;
			<font color='yellow' size='-1'>
				Ano: 
			</font>
			<select name="cmb_ano" title="Selecione o Ano" onchange="return validar()" class="combo">
			<?
				for($i = date('Y') - 2; $i < date('Y') + 2; $i++) {
					$selected = ($i == $ano) ? 'selected' : '';
			?>
			<option value='<?=$i;?>' <?=$selected;?>><?=$i;?></option>
			<?
				}
			?>
			</select>
			&nbsp;
			<font color='yellow' size='-1'>
				Trimestre: 
			</font>
			<select name="cmb_trimestre" title="Selecione o Trimestre" onchange="return validar()" class="combo">
				<option value='1' <?=$selected1;?>>1º </option>
				<option value='2' <?=$selected2;?>>2º </option>
				<option value='3' <?=$selected3;?>>3º </option>
				<option value='4' <?=$selected4;?>>4º </option>
			</select>
		</td>
	</tr>
<?
	if(!empty($representante) || !empty($cmb_representante)) {
		$representante = (!empty($representante)) ? $representante : $cmb_representante;
		//Se o Representante for PME, então só irá exibir os Clientes que são do Tipo Revenda Ativa ...
		if($representante == 71) $condicao_tipo = " AND ct.id_cliente_tipo in (1, 4) ";
	}else {//O usuário é obrigado a selecionar um Representante ...
		exit;
	}

//Busca o Total de Faturamento no Ano por Cliente ...
	$sql = "SELECT pv.id_cliente, sum(pvi.qtde * pvi.preco_liq_final) total_vendido 
	 		FROM `pedidos_vendas_itens` pvi 
			INNER JOIN `pedidos_vendas` pv ON pv.id_pedido_venda = pvi.id_pedido_venda AND YEAR(pv.data_emissao) = '$ano' 
			WHERE pvi.id_representante like '$representante' GROUP BY pv.id_cliente ";
	$campos_total_vendido = bancos::sql($sql);
	$linhas_total_vendido = count($campos_total_vendido);
	for($i = 0; $i < $linhas_total_vendido; $i++) $vetor_total_vendido[$campos_total_vendido[$i]['id_cliente']] = $campos_total_vendido[$i]['total_vendido'];

//Busca o Total de Pedidos projetado dentro do Trimestre ...
	$sql = "SELECT id_cliente, sum(pvi.qtde * pvi.preco_liq_final) total_pedido 
                FROM pedidos_vendas_itens pvi 
                INNER JOIN pedidos_vendas pv on pv.id_pedido_venda = pvi.id_pedido_venda and pvi.id_representante like '$representante' 
                WHERE pv.liberado = '1' 
                AND pv.projecao_vendas = 'S' 
                $condicao_periodo_trimestre 
                GROUP BY pv.id_cliente ";
	$campos_total_pedido = bancos::sql($sql);
	$linhas_total_pedido = count($campos_total_pedido);
	for($i = 0; $i < $linhas_total_pedido; $i++) $vetor_total_pedido[$campos_total_pedido[$i]['id_cliente']] = $campos_total_pedido[$i]['total_pedido'];

//Aqui eu listo todos os Clientes do Representante selecionado ...
	$sql = "SELECT distinct(c.id_cliente), if(c.razaosocial = '', c.nomefantasia, c.razaosocial) cliente 
                FROM clientes c 
                INNER JOIN clientes_tipos ct ON ct.id_cliente_tipo = c.id_cliente_tipo $condicao_tipo 
                INNER JOIN clientes_vs_representantes cr ON c.id_cliente = cr.id_cliente AND cr.id_representante LIKE '$representante' 
                WHERE c.ativo = '1' 
                GROUP BY c.id_cliente ORDER BY c.razaosocial ";
	$campos = bancos::sql($sql, $inicio, 1000, 'sim', $pagina);
	$linhas = count($campos);
?>
    <tr class="linhadestaque" align='center'>
        <td rowspan="2">
            Cliente
        </td>
        <td rowspan="2">
            Volume Fat. <?=$ano;?>
        </td>
        <td rowspan="2">
            Projeções <br>Trimestre Anterior 
        </td>
        <td colspan="3">
            Simulação do que Compra
        </td>
        <td colspan="3">
            Simulação do que Não Compra
        </td>
        <td rowspan="2">
            Resultado
        </td>
    </tr>
    <tr class="linhadestaque" align='center'>
        <td>
            <?=$mes_rotulo1;?>
        </td>
        <td>
            <?=$mes_rotulo2;?>
        </td>
        <td>
            <?=$mes_rotulo3;?>
        </td>
        <td>
            <?=$mes_rotulo1;?>
        </td>
        <td>
            <?=$mes_rotulo2;?>
        </td>
        <td>
            <?=$mes_rotulo3;?>
        </td>
    </tr>
<?
	for ($i = 0; $i < $linhas; $i++) {
//Limpo as variáveis p/ não herdar valores do Próximo Anterior ...
		$simulacao_compra_mes1 = '&nbsp;';
		$simulacao_compra_mes2 = '&nbsp;';
		$simulacao_compra_mes3 = '&nbsp;';
		$simulacao_nao_compra_mes1 = '&nbsp;';
		$simulacao_nao_compra_mes2 = '&nbsp;';
		$simulacao_nao_compra_mes3 = '&nbsp;';
//Aqui eu busco a última Projeção Realizada dentro do Trimestre para o Determinado Cliente ...
		$sql = "SELECT SUM(valor_projecao) as total_projetado, SUBSTRING(data_sys, 6, 2) mes, tipo_projecao, justificativa 
                        FROM `projecoes_trimestrais` 
                        WHERE `id_cliente` = '".$campos[$i]['id_cliente']."' 
                        AND SUBSTRING(data_sys, 6, 2) BETWEEN '$mes_inicial' AND '$mes_final' 
                        AND SUBSTRING(data_sys, 1, 4) = '$ano' 
                        GROUP BY mes, tipo_projecao ORDER BY mes, tipo_projecao ";
		$campos_projecoes = bancos::sql($sql);
		if(count($campos_projecoes) == 3) {//Existe Projeção p/ todo o Trimestre ...
			if($campos_projecoes[0]['tipo_projecao'] == 'C') {//Projeção para o que o Cliente Compra ...
				$simulacao_compra_mes1 = number_format($campos_projecoes[0]['total_projetado'], 2, ',', '.');
				$simulacao_compra_mes2 = number_format($campos_projecoes[1]['total_projetado'], 2, ',', '.');
				$simulacao_compra_mes3 = number_format($campos_projecoes[2]['total_projetado'], 2, ',', '.');
			}else {//Projeção para o que o Cliente Não Compra ...
				$simulacao_nao_compra_mes1 = number_format($campos_projecoes[0]['total_projetado'], 2, ',', '.');
				$simulacao_nao_compra_mes2 = number_format($campos_projecoes[1]['total_projetado'], 2, ',', '.');
				$simulacao_nao_compra_mes3 = number_format($campos_projecoes[2]['total_projetado'], 2, ',', '.');
			}
		}else if(count($campos_projecoes) == 2) {//Existe Projeção p/ 2 Meses do Trimestre ...
			if($campos_projecoes[0]['tipo_projecao'] == 'C') {//Projeção para o que o Cliente Compra ...
				if($campos_projecoes[0]['mes'] == str_pad(($mes_final - 2), 2, '0', STR_PAD_LEFT) && $campos_projecoes[1]['mes'] == str_pad(($mes_final - 1), 2, '0', STR_PAD_LEFT)) {
					$simulacao_compra_mes1 = number_format($campos_projecoes[0]['total_projetado'], 2, ',', '.');
					$simulacao_compra_mes2 = number_format($campos_projecoes[1]['total_projetado'], 2, ',', '.');
					$simulacao_compra_mes3 = '&nbsp;';
				}else if($campos_projecoes[0]['mes'] == str_pad(($mes_final - 2), 2, '0', STR_PAD_LEFT) && $campos_projecoes[1]['mes'] == str_pad($mes_final, 2, '0', STR_PAD_LEFT)) {
					$simulacao_compra_mes1 = number_format($campos_projecoes[0]['total_projetado'], 2, ',', '.');
					$simulacao_compra_mes2 = '&nbsp;';
					$simulacao_compra_mes3 = number_format($campos_projecoes[1]['total_projetado'], 2, ',', '.');
				}else if($campos_projecoes[0]['mes'] == str_pad(($mes_final - 1), 2, '0', STR_PAD_LEFT) && $campos_projecoes[1]['mes'] == str_pad($mes_final, 2, '0', STR_PAD_LEFT)) {
					$simulacao_compra_mes1 = '&nbsp;';
					$simulacao_compra_mes2 = number_format($campos_projecoes[0]['total_projetado'], 2, ',', '.');
					$simulacao_compra_mes3 = number_format($campos_projecoes[1]['total_projetado'], 2, ',', '.');
				}
			}else {//Projeção para o que o Cliente Não Compra ...
				if($campos_projecoes[0]['mes'] == str_pad(($mes_final - 2), 2, '0', STR_PAD_LEFT) && $campos_projecoes[1]['mes'] == str_pad(($mes_final - 1), 2, '0', STR_PAD_LEFT)) {
					$simulacao_nao_compra_mes1 = number_format($campos_projecoes[0]['total_projetado'], 2, ',', '.');
					$simulacao_nao_compra_mes2 = number_format($campos_projecoes[1]['total_projetado'], 2, ',', '.');
					$simulacao_nao_compra_mes3 = '&nbsp;';
				}else if($campos_projecoes[0]['mes'] == str_pad(($mes_final - 2), 2, '0', STR_PAD_LEFT) && $campos_projecoes[1]['mes'] == str_pad($mes_final, 2, '0', STR_PAD_LEFT)) {
					$simulacao_nao_compra_mes1 = number_format($campos_projecoes[0]['total_projetado'], 2, ',', '.');
					$simulacao_nao_compra_mes2 = '&nbsp;';
					$simulacao_nao_compra_mes3 = number_format($campos_projecoes[1]['total_projetado'], 2, ',', '.');
				}else if($campos_projecoes[0]['mes'] == str_pad(($mes_final - 1), 2, '0', STR_PAD_LEFT) && $campos_projecoes[1]['mes'] == str_pad($mes_final, 2, '0', STR_PAD_LEFT)) {
					$simulacao_nao_compra_mes1 = '&nbsp;';
					$simulacao_nao_compra_mes2 = number_format($campos_projecoes[0]['total_projetado'], 2, ',', '.');
					$simulacao_nao_compra_mes3 = number_format($campos_projecoes[1]['total_projetado'], 2, ',', '.');
				}
			}
		}else if(count($campos_projecoes) == 1) {//Existe Projeção p/ 1 Mês do Trimestre ...
			if($campos_projecoes[0]['tipo_projecao'] == 'C') {//Projeção para o que o Cliente Compra ...
				if($campos_projecoes[0]['mes'] == str_pad(($mes_final - 2), 2, '0', STR_PAD_LEFT)) {
					$simulacao_compra_mes1 = number_format($campos_projecoes[0]['total_projetado'], 2, ',', '.');
					$simulacao_compra_mes2 = '&nbsp;';
					$simulacao_compra_mes3 = '&nbsp;';
				}else if($campos_projecoes[0]['mes'] == str_pad(($mes_final - 1), 2, '0', STR_PAD_LEFT)) {
					$simulacao_compra_mes1 = '&nbsp;';
					$simulacao_compra_mes2 = number_format($campos_projecoes[0]['total_projetado'], 2, ',', '.');
					$simulacao_compra_mes3 = '&nbsp;';
				}else {
					$simulacao_compra_mes1 = '&nbsp;';
					$simulacao_compra_mes2 = '&nbsp;';
					$simulacao_compra_mes3 = number_format($campos_projecoes[0]['total_projetado'], 2, ',', '.');
				}
			}else {//Projeção para o que o Cliente Não Compra ...
				if($campos_projecoes[0]['mes'] == str_pad(($mes_final - 2), 2, '0', STR_PAD_LEFT)) {
					$simulacao_nao_compra_mes1 = number_format($campos_projecoes[0]['total_projetado'], 2, ',', '.');
					$simulacao_nao_compra_mes2 = '&nbsp;';
					$simulacao_nao_compra_mes3 = '&nbsp;';
				}else if($campos_projecoes[0]['mes'] == str_pad(($mes_final - 1), 2, '0', STR_PAD_LEFT)) {
					$simulacao_nao_compra_mes1 = '&nbsp;';
					$simulacao_nao_compra_mes2 = number_format($campos_projecoes[0]['total_projetado'], 2, ',', '.');
					$simulacao_nao_compra_mes3 = '&nbsp;';
				}else {
					$simulacao_nao_compra_mes1 = '&nbsp;';
					$simulacao_nao_compra_mes2 = '&nbsp;';
					$simulacao_nao_compra_mes3 = number_format($campos_projecoes[0]['total_projetado'], 2, ',', '.');
				}
			}
		}
?>
	<tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="right">
		<td align="left">
			<?=$campos[$i]['cliente'];?>
		</td>
		<td>
		<?
			if($vetor_total_vendido[$campos[$i]['id_cliente']] > 0) {
				echo 'R$ '.number_format($vetor_total_vendido[$campos[$i]['id_cliente']], 2, ',', '.');
			}else {
				echo '&nbsp;';
			}
		?>
		</td>
		<td bgcolor='#CECECE'>
		<?
			/***********************************Projeções do Trimestre Anterior***********************************/
			$mes_inicial_trimestre_anterior = ($mes_inicial - 3);
			$mes_final_trimestre_anterior 	= ($mes_final 	- 3);
			if($mes_inicial_trimestre_anterior <= 0) 	$mes_inicial_trimestre_anterior = 12;
			if($mes_final_trimestre_anterior <= 0) 		$mes_final_trimestre_anterior 	= 12;
			if($mes_inicial_trimestre_anterior < 10) 	$mes_inicial_trimestre_anterior = '0'.$mes_inicial_trimestre_anterior;
			if($mes_final_trimestre_anterior < 10) 		$mes_final_trimestre_anterior 	= '0'.$mes_final_trimestre_anterior;
			//Aqui eu busco todas as Projeção Realizadas no Trimestre Anterior p/ o Determinado Cliente ...
			$sql = "SELECT SUM(valor_projecao) as total_projetado_trimestre_anterior 
                                FROM `projecoes_trimestrais` 
                                WHERE `id_cliente` = '".$campos[$i]['id_cliente']."' 
                                AND SUBSTRING(data_sys, 6, 2) BETWEEN '$mes_inicial_trimestre_anterior' AND '$mes_final_trimestre_anterior' 
                                AND SUBSTRING(data_sys, 1, 4) = '$ano' 
                                GROUP BY id_cliente ";
			$campos_projecoes_trimestre_anterior = bancos::sql($sql);
			if($campos_projecoes_trimestre_anterior[0]['total_projetado_trimestre_anterior'] > 0) {
				echo 'R$ '.number_format($campos_projecoes_trimestre_anterior[0]['total_projetado_trimestre_anterior'], 2, ',', '.');
			}else {
				echo '&nbsp;';
			}
			/*****************************************************************************************************/
		?>
		</td>
		<td>
                    <a href = 'detalhes_projecoes_realizadas.php?id_cliente=<?=$campos[$i]['id_cliente'];?>&cmb_trimestre=<?=$trimestre;?>&ano=<?=$ano;?>' class='html5lightbox'>
                        <?=$simulacao_compra_mes1;?>
                    </a>
		</td>
		<td>
                    <a href = 'detalhes_projecoes_realizadas.php?id_cliente=<?=$campos[$i]['id_cliente'];?>&cmb_trimestre=<?=$trimestre;?>&ano=<?=$ano;?>' class='html5lightbox'>
                        <?=$simulacao_compra_mes2;?>
                    </a>
		</td>
		<td>
                    <a href = 'detalhes_projecoes_realizadas.php?id_cliente=<?=$campos[$i]['id_cliente'];?>&cmb_trimestre=<?=$trimestre;?>&ano=<?=$ano;?>' class='html5lightbox'>
                        <?=$simulacao_compra_mes3;?>
                    </a>
		</td>
		<td>
                    <a href = 'detalhes_projecoes_realizadas.php?id_cliente=<?=$campos[$i]['id_cliente'];?>&cmb_trimestre=<?=$trimestre;?>&ano=<?=$ano;?>' class='html5lightbox'>
                        <?=$simulacao_nao_compra_mes1;?>
                    </a>
		</td>
		<td>
                    <a href = 'detalhes_projecoes_realizadas.php?id_cliente=<?=$campos[$i]['id_cliente'];?>&trimestre=<?=$trimestre;?>&ano=<?=$ano;?>' class='html5lightbox'>
                        <?=$simulacao_nao_compra_mes2;?>
                    </a>
		</td>
		<td>
                    <a href = 'detalhes_projecoes_realizadas.php?id_cliente=<?=$campos[$i]['id_cliente'];?>&representante=<?=$representante;?>&buscar_mes=todo_trimestre&trimestre=<?=$trimestre;?>&ano=<?=$ano;?>' class='html5lightbox'>
                        <?=$simulacao_nao_compra_mes3;?>
                    </a>
		</td>
		<td bgcolor='#CECECE'>
		<?
                    if($vetor_total_pedido[$campos[$i]['id_cliente']] > 0) {
		?>
			<a href = 'detalhes_pedidos.php?id_cliente=<?=$campos[$i]['id_cliente'];?>&id_representante=<?=$representante;?>&buscar_mes=todo_trimestre&trimestre=<?=$trimestre;?>&ano=<?=$ano;?>' class='html5lightbox'>
		<?
                        echo 'R$ '.number_format($vetor_total_pedido[$campos[$i]['id_cliente']], 2, ',', '.');
                    }else {//Não existe Projeção p/ esse Cliente dentro do Trimestre ...
                        if(!empty($campos_projecoes[0]['justificativa'])) {
                            echo $campos_projecoes[0]['justificativa'];
                        }else {
                            echo '&nbsp;';
                        }
                    }
		?>
		</td>
	</tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            <input type='button' name='cmd_pedidos_realizados' value='Pedidos Realizados' title='Pedidos Realizados' onclick="html5Lightbox.showLightbox(7, 'pedidos_realizados.php?representante=<?=$representante;?>')" style='color:purple' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
</html>