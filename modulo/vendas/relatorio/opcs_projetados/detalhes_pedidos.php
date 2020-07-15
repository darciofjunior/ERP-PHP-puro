<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
require('../../../classes/array_sistema/array_sistema.php');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/opcs_projetados/opcs_projetados.php', '../../../../');

$data_inicial   = data::datetodata($_GET['data_inicial'], '/');
$data_final     = data::datetodata($_GET['data_final'], '/');
?>
<html>
<head>
<title>.:: Detalhes de OPC(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href='../../../../css/layout.css' type='text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table border="1" width="880" align="center" cellspacing ='1' cellpadding='1'>
    <tr class='linhacabecalho' align='center'>
            <td colspan='8'>
                Detalhes de Pedido(s) realizados com base em OPC(s) Projetado(s)
                <font color='yellow' size='-1'>
                    Período: <?=$data_inicial.' à '.$data_final;?>
                </font>
                <br>
                <font color='yellow' size='-1'>
                    Cliente:
                </font>
                <?
                    $sql = "SELECT if(razaosocial = '', nomefantasia, razaosocial) as cliente 
                            FROM `clientes` 
                            WHERE `id_cliente` = '$_GET[id_cliente]' LIMIT 1 ";
                    $campos_cliente = bancos::sql($sql);
                    echo $campos_cliente[0]['cliente'];
                ?>
            </td>
    </tr>
<?
//Aqui eu busco todos os Pedidos que foram feitos para o Cliente e que foram marcados como sendo OPC Projetado ...
	$sql = "SELECT DISTINCT(pvi.`id_pedido_venda`), c.`credito`, pv.`id_cliente_contato`, pv.`id_empresa`, 
                pv.`data_emissao`, pv.`condicao_faturamento`, pv.`faturar_em`, 
                pv.`vencimento1`, pv.`vencimento2`, pv.`vencimento3`, pv.`vencimento4`, pv.`status`, 
                SUM(pvi.`qtde` * pvi.`preco_liq_final`) AS total_pedido 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN `pedidos_vendas` pv on pv.id_pedido_venda = pvi.id_pedido_venda 
                INNER JOIN clientes c on c.id_cliente = pv.id_cliente AND c.id_cliente = '$_GET[id_cliente]' 
                WHERE pv.projecao_apv = 'S' 
                AND pv.data_emissao BETWEEN '$_GET[data_inicial]' AND '$_GET[data_final]'  
                GROUP BY pv.id_pedido_venda ORDER BY pv.`faturar_em` ";
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
                                    $condicao_faturamento = array_sistema::condicao_faturamento();
                                    echo $condicao_faturamento[$campos[$i]['condicao_faturamento']];
				}
			?>
		</td>
		<td>
		<?
//Aqui eu verifico se existe pelo menos 1 item desse que Pedido que contém Vale ...
                    $sql = "SELECT id_pedido_venda_item 
                            FROM `pedidos_vendas_itens` 
                            WHERE `id_pedido_venda` = '".$campos[$i]['id_pedido_venda']."' 
                            AND `vale` > 0 LIMIT 1 ";
                    $campos_vale = bancos::sql($sql);
                    if(count($campos_vale) == 1) {
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