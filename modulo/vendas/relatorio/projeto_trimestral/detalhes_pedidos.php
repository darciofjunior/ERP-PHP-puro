<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
session_start('funcionarios');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../../');

$ano = (empty($_GET['cmb_ano'])) ? date('Y') : $_GET['cmb_ano'];

if(!empty($_GET['cmb_trimestre'])) {
	if($_GET['cmb_trimestre'] == 1) {//Significa que o Mês é pertinente ao 1º Trimestre ...
		$mes_inicial = 1;
	}else if($_GET['cmb_trimestre'] == 2) {//Significa que o Mês é pertinente ao 2º Trimestre ...
		$mes_inicial = 4;
	}else if($_GET['cmb_trimestre'] == 3) {//Significa que o Mês é pertinente ao 3º Trimestre ...
		$mes_inicial = 7;
	}else {//Significa que o Mês é pertinente ao 4º Trimestre ...
		$mes_inicial = 10;
	}
}else {
//Busca do Total de Pedidos Programados do Cliente dentro do Trimestre do Ano Atual ...
	if(date('m') <= 3) {//Significa que o Mês é pertinente ao 1º Trimestre ...
		$mes_inicial = 1;
	}else if(date('m') <= 6) {//Significa que o Mês é pertinente ao 2º Trimestre ...
		$mes_inicial = 4;
	}else if(date('m') <= 9) {//Significa que o Mês é pertinente ao 3º Trimestre ...
		$mes_inicial = 7;
	}else {//Significa que o Mês é pertinente ao 4º Trimestre ...
		$mes_inicial = 10;
	}	
}

if($_GET['buscar_mes'] == 'mes_atual') {//Significa que é o primeiro mês do Trimestre ...
	$mes = $mes_inicial;
	$condicao_mes = " = '$mes' ";
	$rotulo = $vetor_meses[$mes];
}else if($_GET['buscar_mes'] == 'proximo_1mes') {//Significa que é o segundo mês do Trimestre ...
	$mes = $mes_inicial + 1;
	$condicao_mes = " = '$mes' ";
	$rotulo = $vetor_meses[$mes];
}else if($_GET['buscar_mes'] == 'proximo_2meses') {//Significa que é o terceiro mês do Trimestre ...
	$mes = $mes_inicial + 2;
	$condicao_mes = " = '$mes' ";
	$rotulo = $vetor_meses[$mes];
}else {//Significa que é todo mês do Trimestre ...
	$mes = $mes_inicial;
	$condicao_mes = " BETWEEN '$mes' AND '".($mes + 2)."' ";
	$rotulo = 'Trimestre';
}
$vetor_meses = array('', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');
?>
<html>
<head>
<title>.:: Detalhes de Projeto Trimestral ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href='../../../../css/layout.css' type='text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table border="1" width="880" align="center" cellspacing ='1' cellpadding='1'>
	<tr class='linhacabecalho' align='center'>
		<td colspan='8'>
			Detalhes do Projeto Trimestral - 
			<font color='yellow' size='-1'>
				<?=$rotulo;?> de <?=$ano;?>
			</font>
			<br><font color='yellow' size='-1'>
				Representante: 
			</font>
			<?
				$sql = "SELECT nome_fantasia 
						FROM `representantes` 
						WHERE `id_representante` = '$_GET[id_representante]' limit 1 ";
				$campos_representante = bancos::sql($sql);
				echo $campos_representante[0]['nome_fantasia'];
			?>
			<font color='yellow' size='-1'>
				- Cliente: 
			</font>
			<?
				$sql = "SELECT if(razaosocial = '', nomefantasia, razaosocial) as cliente 
						FROM `clientes` 
						WHERE `id_cliente` = '$_GET[id_cliente]' limit 1 ";
				$campos_cliente = bancos::sql($sql);
				echo $campos_cliente[0]['cliente'];
			?>
		</td>
	</tr>
<?
//Significa que se deseja trazer todos os Clientes (Pendentes) e não Faturáveis ...
	$data_atual = date('Y-m-d');
	$data_atual_mais_um = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), 1), '-');

	$sql = "SELECT DISTINCT(pv.`id_pedido_venda`), pv.`id_cliente_contato`, pv.`id_empresa`, 
                pv.`data_emissao`, pv.`condicao_faturamento`, pv.`faturar_em`, pv.`vencimento1`, 
                pv.`vencimento2`, pv.`vencimento3`, pv.`vencimento4`, pv.`status`, 
                SUM(pvi.qtde * pvi.preco_liq_final) AS total_pedido, c.`credito` 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pvi.`id_representante` LIKE '$_GET[id_representante]' 
                INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND c.`id_cliente` = '$_GET[id_cliente]' 
                WHERE pv.`liberado` = '1' 
                AND pv.`projecao_vendas` = 'S' 
                AND MONTH(pv.`faturar_em`) $condicao_mes 
                AND YEAR(pv.`faturar_em`) = '$ano' 
                GROUP BY pv.`id_pedido_venda` ORDER BY pv.`faturar_em` ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
?>
	<tr class="linhadestaque" align="center">
		<td>
			<font color='#FFFFFF' title="N.&ordm; Pedido" size='-1'>
				N.&ordm; Ped
			</font>
		</td>
		<td>
			<font color='#FFFFFF' title="Data de Emissão" size='-1'>
				Data Em.
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Faturar Em
			</font>
		</td>
		<td>
			<font color='#FFFFFF' title="Condição de Faturamento" size='-1'>
				Condição de<br>Faturamento
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Vale
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Contato
			</font>
		</td>
		<td>
			<font color='#FFFFFF' title="Empresa / Tipo de Nota / Prazo de Pagamento" size='-1'>
				Emp / Tp Nota<br> / Prazo Pgto
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Valor Total
			</font>
		</td>
	</tr>
<?
	for ($i = 0; $i < $linhas; $i++) {
?>
	<tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
		<td>
			<a href="javascript:nova_janela('../../../faturamento/nota_saida/itens/detalhes_pedido.php?id_pedido_venda=<?=$campos[$i]['id_pedido_venda'];?>', 'PED', '', '', '', '', 450, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Detalhes de Pedido" class="link">
			<?
				if($campos[$i]['status'] < 2) {//Pedido em Aberto
			?>
				<font title="Pedido em Aberto">
					<?=$campos[$i]['id_pedido_venda'];?>
				</font>
			<?
				}else {//Pedido Concluído
			?>
				<font title="Pedido Concluído" color="red">
					<?=$campos[$i]['id_pedido_venda'];?>
				</font>
			<?
				}
			?>
			</a>
		</td>
		<td>
			<?=data::datetodata($campos[$i]['data_emissao'], '/');?>
		</td>
		<td>
		<?
			if($campos[$i]['faturar_em'] != '0000-00-00') {//Coloca no formato de Data
                            if($campos[$i]['faturar_em'] > $data_atual_mais_um) {
                                echo '<font color="red">'.data::datetodata($campos[$i]['faturar_em'], '/').'</font>';
                            }else {
                                echo '<font color="green">'.data::datetodata($campos[$i]['faturar_em'], '/').'</font>';
                            }
			}else {
                            echo '&nbsp;';
			}
		?>
		</td>
		<td>
			<?
				if($campos[$i]['credito'] == 'C' || $campos[$i]['credito'] == 'D') {
					echo '<font color="red">CRÉDITO '.$campos[$i]['credito'].'</font>';
				}else {
					$condicao_faturamento[1] = '<font color="green">FATURÁVEL</font>';
					$condicao_faturamento[2] = '<font color="red">MATERIAL EM PRODUÇÃO</font>';
					$condicao_faturamento[3] = '<font color="red">SEM VALOR P/ NF</font>';
					$condicao_faturamento[4] = '<font color="red">OUTROS</font>';
					echo $condicao_faturamento[$campos[$i]['condicao_faturamento']];
				}
			?>
		</td>
		<td>
		<?
//Aqui eu verifico se existe pelo menos 1 item desse que Pedido que contém Vale ...
			$sql = "Select id_pedido_venda_item 
					from pedidos_vendas_itens  
					where id_pedido_venda = '".$campos[$i]['id_pedido_venda']."' 
					and vale > 0 limit 1 ";
			$campos2 = bancos::sql($sql);
			if(count($campos2) == 1) {
				echo '<font color="blue"><b>SIM</b></font>';
			}else {
				echo '&nbsp;';
			}
		?>
		</td>
		<td align="left">
		<?
			$sql = "Select nome 
					from `clientes_contatos` 
					where id_cliente_contato = '".$campos[$i]['id_cliente_contato']."' limit 1 ";
			$campos_contato = bancos::sql($sql);
			echo $campos_contato[0]['nome'];
		?>
		</td>
		<td align="left">
		<?
			if($campos[$i]['vencimento4'] > 0) {
				$prazo_faturamento = '/'.$campos[$i]['vencimento4'];
			}
			if($campos[$i]['vencimento3'] > 0) {
				$prazo_faturamento= '/'.$campos[$i]['vencimento3'].$prazo_faturamento;
			}
			if($campos[$i]['vencimento2'] > 0) {
				$prazo_faturamento= $campos[$i]['vencimento1'].'/'.$campos[$i]['vencimento2'].$prazo_faturamento;
			} else {
				if($campos[$i]['vencimento1'] == 0) {
					$prazo_faturamento = 'À vista';
				}else {
					$prazo_faturamento = $campos[$i]['vencimento1'];
				}
			}

			if($campos[$i]['id_empresa']==1) {
				$nomefantasia = 'ALBA - NF';
				
				echo '(A - NF) / '.$prazo_faturamento;
			}else if($campos[$i]['id_empresa']==2) {
				$nomefantasia = 'TOOL - NF';
				
				echo '(T - NF) / '.$prazo_faturamento;
			}else if($campos[$i]['id_empresa']==4) {
				$nomefantasia = 'GRUPO - SGD';
				
				echo '(G - SGD) / '.$prazo_faturamento;
			}else {
				echo 'Erro';
			}
//Aki eu limpo essa variável para não dar problema quando voltar no próximo loop
			$prazo_faturamento = '';
		?>
		</td>
		<td align="right">
			R$ <?=number_format($campos[$i]['total_pedido'], 2, ',', '.');?>		
		</td>
	</tr>
<?
		$total_geral_pedido+= $campos[$i]['total_pedido'];
	}
?>
	<tr class="linhacabecalho" align="right">
		<td colspan='7'>
			<font color='yellow' size='-1'>
				TOTAL GERAL =>
			</font>
		</td>
		<td>
			<font size='-1'>
				R$ <?=number_format($total_geral_pedido, 2, ',', '.');?>
			</font>
		</td>
	</tr>
</table>
</body>
</html>