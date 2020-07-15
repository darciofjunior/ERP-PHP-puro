<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
session_start('funcionarios');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/consultar.php', '../../../../');

$mensagem[1] = "<font class='atencao'>NÃO HÁ ENTRADA(S) PARA ESSE PEDIDO.</font>";

//Visualização de Entradas por Pedido de Compras, independente da Qtde de Item(ns)
if($passo == 1) {
//Select para saber o número total de entradas
    $sql = "SELECT ip.id_item_pedido, nfe.*,  f.razaosocial, nh.qtde_entregue, p.id_pedido, g.referencia, prod.discriminacao 
            from nfe_historicos nh, itens_pedidos ip, pedidos p, nfe , fornecedores f, produtos_insumos prod, grupos g 
            where p.id_pedido = '$id_pedido' 
            and ip.id_pedido = p.id_pedido 
            and ip.id_item_pedido = nh.id_item_pedido 
            and nh.id_nfe = nfe.id_nfe 
            and nfe.id_fornecedor = f.id_fornecedor 
            and prod.id_produto_insumo = ip.id_produto_insumo 
            and g.id_grupo = prod.id_grupo ORDER BY data_entrega ";
    $campos = bancos::sql($sql);
    $linhas  = count($campos);
    if($linhas == 0) {//Não achou Item(ns)
?>
<html>
<head>
<title>.:: Visualizar Entrada(s) por Pedido ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
</table>
</body>
</html>
<?
        exit;
    }
//Existem entradas p/ esse Pedido, busco todas as Entradas de todo(s) os Item(ns) de Pedido em toda(s) as Nota(s) Fiscal(is)
    $sql = "SELECT nfe.data_entrega 
            FROM `nfe_historicos` nfeh 
            INNER JOIN `nfe` ON nfe.`id_nfe` = nfeh.`id_nfe` 
            INNER JOIN `itens_pedidos` ip ON ip.`id_item_pedido` = nfeh.`id_item_pedido` 
            INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` 
            WHERE p.`id_pedido` = '$id_pedido' 
            GROUP BY nfe.data_entrega DESC ";
    $campos_data_entrega    = bancos::sql($sql);
    $numero_entradas        = count($campos_data_entrega);
?>
<html>
<head>
<title>.:: Visualizar Entrada(s) por Pedido ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' src = '../../../../js/geral.js'></Script>
<Script Language = 'Javascript' src = '../../../../js/sessao.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan="<?=$numero_entradas + 2;?>">
            Visualizar Entrada(s) por Pedido
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
<?
	$numero_colunas = $numero_entradas;
	for($i = 0; $i < $numero_entradas; $i++) {
?>
        <td>
            <?=$numero_colunas;?>&deg; <?=data::datetodata($campos_data_entrega[$i]['data_entrega'], '/');?>
        </td>
<?
            $numero_colunas--;
	}
?>
        <td>
            Refêrencia
        </td>
        <td>
            Discriminação
        </td>
    </tr>
<?
	$cont = 1;
	$saida = 0;
	$saida2 = 0;
	$registro = 0;
	$desviar = 0;
	for($i = 0; $i < $linhas; $i++) {
            $qtde_entregue = $campos[$i]['qtde_entregue'];
            $qtde_entregue = number_format($qtde_entregue, 2, ',', '.');
            $tipo_nota = $campos[$i]['tipo'];
            if($tipo_nota == 1) {
                $tipo_nota = 'NF';
            }else {
                $tipo_nota = 'SGD';
            }
            $id_item_pedido = $campos[$i]['id_item_pedido'];
//Busca da qtde entregue e + outros dados do Item de Pedido de Compra(s) Corrente
            $sql = "SELECT nfe.*, nh.qtde_entregue, g.referencia, prod.id_produto_insumo, prod.discriminacao 
                    from nfe_historicos nh, itens_pedidos ip, nfe, produtos_insumos prod, grupos g 
                    where ip.id_item_pedido = '".$id_item_pedido."' 
                    and ip.id_item_pedido = nh.id_item_pedido 
                    and nh.id_nfe = nfe.id_nfe 
                    and ip.id_produto_insumo = prod.id_produto_insumo 
                    and g.id_grupo = prod.id_grupo 
                    order by data_entrega desc ";
            $campos3 = bancos::sql($sql);
            $linhas3 = count($campos3);
            if($linhas3 > 1 && $registro == 0) {
                    $repetido[$registro] = $id_item_pedido;
                    $registro++;
                    $desviar = 0;
            }else if ($linhas3 > 1 && $registro > 0) {
                    $numeros_repetidos = count($repetido);
                    for($y = 0; $y < $numeros_repetidos; $y++) {
                            if($repetido[$y] == $id_item_pedido) {
                                    $desviar = 1;
                            }
                    }
                    if($desviar == 0) {
                            $repetido[$registro] = $id_item_pedido;
                            $registro++;
                    }
            }
            if($desviar == 0) {
?>
	<tr class="linhanormal">
<?
			if($linhas3 > 1) {
				$entrada = $numero_entradas;
				$a = 0;
				for($z = 0; $z < $linhas3; $z++) {
					$qtde_entregue = $campos3[$z]['qtde_entregue'];
					$qtde_entregue = number_format($qtde_entregue, 2, ',', '.');
					$data_item = $campos3[$z]['data_entrega'];
					$saida2 = 0;
					while($saida2 == 0) {
						$data_corrente = $campos_data_entrega[$a]['data_entrega'];
						if($data_item == $data_corrente) {
?>
		<td align='center'>
			<?=$qtde_entregue;?>
		</td>
<?
							$saida2 = 1;
							$a++;
							$entrada--;
						}else {
?>
		<td>
			&nbsp;
		</td>
<?
							$a++;
							$entrada--;
						}
					}
				}
			}else {
				$a = 0;
				$entrada = $numero_entradas;
				for($z = 0;$z < $linhas3; $z++) {
					$qtde_entregue = $campos3[$z]['qtde_entregue'];
					$qtde_entregue = number_format($qtde_entregue, 2, ',', '.');
					$data_item = $campos3[$z]['data_entrega'];
					$saida2 = 0;
					while($saida2 == 0) {
						$data_corrente = $campos_data_entrega[$a]['data_entrega'];
						if($data_item == $data_corrente) {
?>
		<td align='center'>
			<?=$qtde_entregue;?>
		</td>
<?
							$saida2 = 1;
							$entrada--;
							$a++;
						}else {
?>
		<td>
			&nbsp;
		</td>
<?
							$a++;
							$entrada--;
						}
					}
				}
			}
			if($entrada > 0) {
				for($j = $entrada; $j > 0; $j--) {
?>
		<td>
			&nbsp;
		</td>
<?
				}
			}
?>
		<td>
                    <?=$campos3[0]['referencia'];?>
		</td>
		<td>
                    <?=$campos3[0]['discriminacao'];?>
		</td>
	</tr>
<?
		}
		$cont++;
		$desviar = 0;
	}
?>
	<tr class='linhacabecalho' align='center'>
            <td colspan="<?=$numero_entradas + 2;?>">
                &nbsp;
            </td>
	</tr>
</table>
</body>
</html>
<?
//Visualização de Entradas somente pelo Item Pedido de Compras
}else {
//Seleciona somente dados específicos daquele Item de Pedido de Compras que foi importado em Nota Fiscal
	$sql = "SELECT nfe.*, nh.qtde_entregue, p.id_pedido 
                FROM `itens_pedidos` ip 
                INNER JOIN `pedidos` p ON p.id_pedido = ip.id_pedido 
                INNER JOIN `nfe_historicos` nh ON nh.id_item_pedido = ip.id_item_pedido 
                INNER JOIN `nfe` ON nfe.id_nfe = nh.id_nfe 
                WHERE ip.id_item_pedido = '".$id_item_pedido."' ORDER BY nfe.data_entrega ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	$id_pedido = $campos[0]['id_pedido'];
//Busca essa data para fazer alguma comparação + abaixo
	$sql = "SELECT nfe.data_entrega 
                FROM `pedidos` p 
                INNER JOIN `itens_pedidos` ip ON ip.id_pedido = p.id_pedido 
                INNER JOIN `nfe_historicos` nh ON ip.id_item_pedido = nh.id_item_pedido 
                INNER JOIN `nfe` ON nfe.id_nfe = nh.id_nfe 
                WHERE p.`id_pedido` = '$id_pedido' GROUP BY nfe.data_entrega ";
	$campos_data_entrega = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Visualizar Entrada(s) por Item de Pedido ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../../css/layout.css'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<table width="90%" cellspacing="1" cellpadding="1" align='center'>
	<tr class='linhacabecalho' align='center'>
		<td colspan="5">
			Visualizar Entrada(s) por Item de Pedido
		</td>
	</tr>
	<tr class='linhadestaque' align='center'>
		<td>Entrada</td>
		<td>Qtde Entregue</b></td>
		<td>N.º Nota</b></td>
		<td>Tipo da Nota</b></td>
		<td>Data de Entrega</td>
	</tr>
<?
	for($i = 0; $i < $linhas; $i++) {
		$qtde_entregue = segurancas::number_format($campos[$i]['qtde_entregue'], 2, '.');
		$tipo_nota = $campos[$i]['tipo'];
?>
	<tr class="linhanormal" align='center'>
<?
		$entrada = 1;
		$y = 0;
		$saida = 0;
		while ($saida == 0) {
			if($campos[$i]['data_entrega'] == $campos_data_entrega[$y]['data_entrega']) {
?>
		<td>
			<?=$entrada;?>&deg;
		</td>
<?
				$saida = 1;
			}else {
				$entrada++;
				$y++;
			}
		}
?>
		<td>
			<?=$qtde_entregue;?>
		</td>
		<td>
			<a href="#" onclick="javascript:nova_janela('../nota_entrada/itens/itens.php?id_nfe=<?=$campos[$i]['id_nfe'];?>&pop_up=1', 'ITENS', 'F', '', '', '', '', '', '', '', '', '', 's', 's', '', '', '')" title="Detalhes da Nota Fiscal" class="link">
				<?=$campos[$i]['num_nota'];?>
			</a>
		</td>
		<td>
		<?
//Aqui determino os valores possíveis para a Nota Fiscal
			$vetor_tipo_nota[1] = 'NF';
			$vetor_tipo_nota[2] = 'SGD';
			echo $vetor_tipo_nota[$tipo_nota];
		?>
		</td>
		<td>
			<?=data::datetodata($campos[$i]['data_entrega'], '/');?>
		</td>
	</tr>
<?
	}
?>
	<tr class='linhacabecalho' align='center'>
		<td colspan="5">
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="window.close()" style="color:red" class="botao">
		</td>
	</tr>
</table>
</body>
</html>
<?}?>