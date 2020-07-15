<?
$pop_up = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['pop_up'] : $_GET['pop_up'];

require('../../../lib/segurancas.php');
if(empty($pop_up)) require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');
?>
<html>
<head>
<title>.:: Relatório de Estoque vs Pendência ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<form name='form' action=''>
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<table width='95%' border='0' align='center' cellspacing='1' cellpadding='1'>
	<tr class="linhacabecalho" align="center">
		<td colspan='7'>
			<font color="yellow">
				<b>Vendedor: </b>
			</font>
			<?
//Verifico se o Vendedor foi passado por Parâmetro ...
				if(!empty($representante)) {
					$sql = "Select nome_fantasia 
                                                from representantes 
                                                where id_representante = '$representante' limit 1 ";
					$campos_representante = bancos::sql($sql);
					echo $campos_representante[0]['nome_fantasia'];
//Se não foi passado nenhum Representante por parâmetro, então eu apresento a combo abaixo ...
				}else {
			?>
					<select name="cmb_representante" title="Selecione o Representante" class="combo">
			<?
				$sql = "SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
						FROM `representantes` 
						WHERE `ativo` = '1' ORDER BY nome_fantasia ";
						echo combos::combo($sql, $cmb_representante);
			?>
					</select>
					&nbsp;
					<input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
			<?
				}
			?>
		</td>
	</tr>
<?
	$data_atual_mais_um = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), 1), '-');
/*Se foi passado um parâmetro de Representante ou foi selecionado algum na Combo, então 
eu realizo o SQL abaixo ...*/
	if(!empty($representante) || !empty($cmb_representante)) {
            if(!empty($representante)) {
                $condicao_representante = " pvi.id_representante like '$representante' ";
            }else {
                $condicao_representante = " pvi.id_representante like '$cmb_representante' ";
            }
/*Busco todos os Itens de Pedidos junto do Cliente que estão em Pendências do Representante 
que foi passado por parâmetro ...*/
            $sql = "SELECT c.id_cliente, c.credito, c.limite_credito, IF(c.razaosocial = '', c.nomefantasia, CONCAT(c.razaosocial, ' - ', c.nomefantasia)) AS cliente, pv.id_empresa, SUM(pvi.preco_liq_final * (pvi.qtde - pvi.qtde_faturada)) AS total_empresa 
                    FROM `clientes` c 
                    INNER JOIN `pedidos_vendas` pv ON pv.id_cliente = c.id_cliente 
                    INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda AND pvi.status < '2' 
                    WHERE $condicao_representante 
                    GROUP BY c.id_cliente, pv.id_empresa 
                    ORDER BY cliente, pv.id_empresa ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
//Verifica se tem pelo menos um item no Pedido que está Pendente
            if($linhas > 0) {
	?>
	<tr class="linhacabecalho" align="center">
            <td colspan='7'>
                Relatório de Estoque vs Pendência (Total por Empresa)
            </td>
	</tr>
	<tr class='linhadestaque' align='center'>
            <td>Cliente</td>
            <td>Crédito</td>
            <td>ALBA</td>
            <td>TOOL</td>
            <td>A + T</td>
            <td>GRUPO</td>
            <td>TOT. GERAL</td>
	</tr>
<?
		for($i = 0; $i < $linhas; $i++) {
			$alba = 0; $tool = 0; $grupo = 0; $somar = 0;
			//Aqui significa que o Cliente possui valor de Pendência em todas as Empresas ...
			if($campos[$i]['cliente'] == $campos[$i + 1]['cliente'] && $campos[$i]['cliente'] == $campos[$i + 2]['cliente']) {
				$alba 	= $campos[$i]['total_empresa'];
				$tool 	= $campos[$i + 1]['total_empresa'];
				$grupo 	= $campos[$i + 2]['total_empresa'];
				$somar	= 2;//Valor acrescentado no Índice I do Loop p/ avançar os Registros ...
			//Aqui significa que o Cliente possui valor de Pendência em 2 Empresas ...
			}else if($campos[$i]['cliente'] == $campos[$i + 1]['cliente']) {
				if($campos[$i]['id_empresa'] == 1 && $campos[$i + 1]['id_empresa'] == 2) {//Alba e Tool ..
					$alba 	= $campos[$i]['total_empresa'];
					$tool 	= $campos[$i + 1]['total_empresa'];
				}else if($campos[$i]['id_empresa'] == 1 && $campos[$i + 1]['id_empresa'] == 4) {//Alba e Grupo ..
					$alba 	= $campos[$i]['total_empresa'];
					$grupo 	= $campos[$i + 1]['total_empresa'];
				}else if($campos[$i]['id_empresa'] == 2 && $campos[$i + 1]['id_empresa'] == 4) {//Tool e Grupo ..
					$tool 	= $campos[$i]['total_empresa'];
					$grupo 	= $campos[$i + 1]['total_empresa'];
				}
				$somar	= 1;//Valor acrescentado no Índice I do Loop p/ avançar os Registros ...
			//Aqui significa que o Cliente possui valor de Pendência em 1 única Empresa ...
			}else {
				if($campos[$i]['id_empresa'] == 1) {//Alba apenas ...
					$alba 	= $campos[$i]['total_empresa'];
				}else if($campos[$i]['id_empresa'] == 2) {//Tool Master apenas ...
					$tool 	= $campos[$i]['total_empresa'];
				}else if($campos[$i]['id_empresa'] == 4) {//Grupo apenas ...
					$grupo 	= $campos[$i]['total_empresa'];
				}
			}
?>
	<tr class='linhanormal'>
		<td align='left'>
			<a href="#" onclick="javascript:nova_janela('../../classes/pedido_vendas/relatorio_pendencias.php?id_cliente=<?=$campos[$i]['id_cliente'];?>', 'RELATORIO', '', '', '', '', 450, 900, 'c', 'c', '', '', 's', 's', '', '', '')" title="Relatório de Pendências" class="link">
				<?=$campos[$i]['cliente'];?>
			</a>
		</td>
		<td align='center'>
		<?	
			if($campos[$i]['credito'] == 'C') {
				echo "<font color='red'><b>";
			}else if($campos[$i]['credito'] == 'D') {
				echo "<font color='blue'><b>";
			}
			echo $campos[$i]['credito'];
			if($campos[$i]['credito'] == 'B') echo ' - R$ '.number_format($campos[$i]['limite_credito'] * 1.1, 0, ',', '.');
		?>
		</td>
		<td align='right'>
		<?
			if($alba > 0) {
				echo number_format($alba, 2, ',', '.');
			}else {
				echo '&nbsp;';
			}
		?>
		</td>
		<td align='right'>
		<?
			if($tool > 0) {
				echo number_format($tool, 2, ',', '.');
			}else {
				echo '&nbsp;';
			}
		?>
		</td>
		<td align='right' bgcolor='#CECECE'>
		<?
			if(($alba + $tool) > 0) {
				echo number_format($alba + $tool, 2, ',', '.');
			}else {
				echo '&nbsp;';
			}
		?>
		</td>
		<td align='right'>
		<?
			if($grupo > 0) {
				echo number_format($grupo, 2, ',', '.');
			}else {
				echo '&nbsp;';
			}
		?>
		</td>
		<td align='right' bgcolor='#CECECE'>
		<?
			if(($alba + $tool + $grupo) > 0) {
				echo number_format($alba + $tool + $grupo, 2, ',', '.');
			}else {
				echo '&nbsp;';
			}
		?>
		</td>
	</tr>
<?
			$i+= $somar; 
		}
?>
        <tr class='linhacabecalho' align='center'>
            <td colspan='7'>
                &nbsp;
            </td>
	</tr>        
<?
            }else {
?>
        <tr class='erro' align='center'>
            <td colspan='7'>
                ESSE VENDEDOR NÃO POSSUI NENHUMA PENDÊNCIA DE ESTOQUE.
            </td>
	</tr>        
<?
            }
?>
	<tr class='atencao' align='center'>
            <td colspan='7'>
                <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' style='color:red' onclick="window.location = 'estrategia_vendas.php?representante=<?=$representante;?>&pop_up=<?=$pop_up;?>'" class='botao'>
            </td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>