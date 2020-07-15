<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
session_start('funcionarios');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../../');

//Sugestão pra quando acabar de carregar a Tela ...
$ano = (empty($_GET['cmb_ano'])) ? date('Y') : $_GET['cmb_ano'];

if(empty($_GET['cmb_trimestre'])) {
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
	$trimestre = $_GET['cmb_trimestre'];
}

if($trimestre == 1) {//Significa que são meses pertinentes ao 1º Trimestre ...
	$mes_inicial = '01';
	$mes_final = '03';
	$mes_rotulo1 = 'Janeiro';
	$mes_rotulo2 = 'Fevereiro';
	$mes_rotulo3 = 'Março';
	$selected1	 = 'selected';
	$periodo = '01/01/'.$ano.' à 31/03/'.$ano;
	$condicao_periodo_trimestre = " AND pv.`faturar_em` BETWEEN '".$ano."-01-01' AND '".$ano."-03-31' ";
}else if($trimestre == 2) {//Significa que são meses pertinentes ao 2º Trimestre ...
	$mes_inicial = '04';
	$mes_final = '06';
	$mes_rotulo1 = 'Abril';
	$mes_rotulo2 = 'Maio';
	$mes_rotulo3 = 'Junho';
	$selected2	 = 'selected';
	$periodo = '01/04/'.$ano.' à 30/06/'.$ano;
	$condicao_periodo_trimestre = " AND pv.`faturar_em` BETWEEN '".$ano."-04-01' AND '".$ano."-06-30' ";
}else if($trimestre == 3) {//Significa que são meses pertinentes ao 3º Trimestre ...
	$mes_inicial = '07';
	$mes_final = '09';
	$mes_rotulo1 = 'Julho';
	$mes_rotulo2 = 'Agosto';
	$mes_rotulo3 = 'Setembro';
	$selected3	 = 'selected';
	$periodo = '01/07/'.$ano.' à 30/09/'.$ano;
	$condicao_periodo_trimestre = " AND pv.`faturar_em` BETWEEN '".$ano."-07-01' AND '".$ano."-09-30' ";
}else {//Significa que são meses pertinentes ao 4º Trimestre ...
	$mes_inicial = 10;
	$mes_final = 12;
	$mes_rotulo1 = 'Outubro';
	$mes_rotulo2 = 'Novembro';
	$mes_rotulo3 = 'Dezembro';
	$selected4	 = 'selected';
	$periodo = '01/10/'.$ano.' à 31/12/'.$ano;
	$condicao_periodo_trimestre = " AND pv.`faturar_em` BETWEEN '".$ano."-10-01' AND '".$ano."-12-31' ";
}

//Busca de alguns dados do Cliente ...
$sql = "SELECT IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, ct.`tipo` 
        FROM `clientes` c 
        INNER JOIN `clientes_tipos` ct ON ct.`id_cliente_tipo` = c.`id_cliente_tipo` 
        WHERE c.`id_cliente` = '$_GET[id_cliente]' LIMIT 1 ";
$campos_clientes = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Detalhes das Projeções Realizadas ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href='../../../../css/layout.css' type='text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<form name="form" action="<?=$PHP_SELF;?>" method="POST">
<table border='1' width='960' cellspacing ='1' cellpadding='1'  align='center'>
	<tr class='linhacabecalho' align='center'>
		<td colspan='8'>
			Detalhes das Projeções Realizadas para o 
			<font color='yellow' size='-1'>
				Cliente: 
			</font>
			<?=$campos_clientes[0]['cliente'];?>
			<font color='yellow' size='-1'>
				Tipo: 
			</font>
			<?=$campos_clientes[0]['tipo'];?>
		</td>
	</tr>
<?
//Aqui eu busco todas as Projeções do Cliente passado por parâmetro ...
	$sql = "SELECT f.nome, pt.* 
			FROM projecoes_trimestrais pt 
			INNER JOIN funcionarios f ON f.id_funcionario = pt.id_funcionario 
			WHERE pt.id_cliente = '$_GET[id_cliente]' 
			AND SUBSTRING(data_sys, 6, 2) BETWEEN '$mes_inicial' AND '$mes_final' 
			AND SUBSTRING(data_sys, 1, 4) = '$ano' 
			ORDER BY pt.id_projecao_trimestral DESC ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
?>
	<tr class="linhadestaque" align="center">
		<td>
			<font color='#FFFFFF' size='-1'>
				Funcionário
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Tipo de Projeção
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Tipo de Produto
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Qtde de Produtos
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Qtde de Meses
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Percentagem
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Valor da Projeção
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Data / Hora
			</font>
		</td>
	</tr>
<?
	for ($i = 0; $i < $linhas; $i++) {
?>
	<tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
		<td align="left">
			<?=$campos[$i]['nome'];?>
		</td>
		<td>
		<?
			if($campos[$i]['tipo_projecao'] == 'C') {
				echo 'Cliente Compra';
			}else {
				echo 'Cliente não Compra';
			}
		?>
		</td>
		<td>
			<?=$campos[$i]['tipo_produto'];?>
		</td>
		<td>
			<?=$campos[$i]['qtde_produtos'];?>
		</td>
		<td>
			<?=$campos[$i]['qtde_meses'];?>
		</td>
		<td>
			<?=$campos[$i]['percentagem'];?>
		</td>
		<td align="right">
			<?=number_format($campos[$i]['valor_projecao'], 2, ',', '.');?>
		</td>
		<td>
			<?=data::datetodata(substr($campos[$i]['data_sys'], 0, 10), '/').' '.substr($campos[$i]['data_sys'], 11, 8);?>
		</td>
	</tr>
<?
	}
?>
	<tr class="linhacabecalho" align="center">
		<td colspan='8'>
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="window.close()" style="color:red" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>