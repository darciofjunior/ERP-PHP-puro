<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
session_start('funcionarios');

$mensagem[1] = "<font class='atencao'>NÃO HÁ ITEM(NS) PENDENTE(S) NESSE MÊS.</font>";
$mensagem[2] = "<font class='atencao'>NÃO HÁ PEDIDO(S) PENDENTE(S) NESSE MÊS.</font>";

//segurancas::geral('/erp/albafer/modulo/compras/relatorios/pedidos/pedidos_pendentes/pedidos_pendentes.php', '../../../../');
if($passo == 1) {
//Parte de Pedidos
    $sql = "SELECT p.*, f.razaosocial, e.nomefantasia 
            FROM `pedidos` p 
            INNER JOIN `fornecedores` f ON f.id_fornecedor = p.id_fornecedor 
            INNER JOIN `empresas` e ON e.id_empresa = p.id_empresa 
            WHERE p.`status` = '1' 
            AND p.`ativo` = '1' 
            AND SUBSTRING(p.`data_emissao`, 6, 2) = '$mes' ORDER BY p.data_emissao DESC ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = 'consultar_pedidos_itens.php?valor=2'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Pedidos Pendentes ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
</head>
<body>
<form name='form' action='<?=$PHP_SELF.'?passo=2';?>' method='post' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr class="atencao" align='center'>
        <td colspan='8'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Pedido(s) Pendente(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N. &ordm; Pedido
        </td>
        <td>
            Emiss&atilde;o
        </td>
        <td colspan='2'>
            Fornecedor
        </td>
        <td>
            Pend&ecirc;ncia
        </td>
        <td>
            Empresa
        </td>
    </tr>
<?
        for($i = 0;  $i < $linhas; $i++) {
            $url = '../../../pedidos/itens/itens.php?id_pedido='.$campos[$i]['id_pedido'].'&pop_up=1';
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href='<?=$url?>' class='html5lightbox'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href='<?=$url?>' class='html5lightbox'>
                <?=$campos[$i]['id_pedido'];?>
            </a>
        </td>
        <td>
            <?=data::datetodata(substr($campos[$i]['data_emissao'], 0, 10), '/');?>
        </td>
        <td colspan='2' align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
        <?
            $sql = "SELECT id_item_pedido 
                    FROM `itens_pedidos` 
                    WHERE `id_pedido` = ".$campos[$i]['id_pedido']." 
                    AND `status` > '0' ";
            $campos2 = bancos::sql($sql);
            if(count($campos2) == 0) {
                echo '<font color="FF0000">Total</font>';
            }else {
                echo '<font color="0000FF">Parcial</font>';
            }
        ?>
        </td>
        <td align="left">
            <?=$campos[$i]['nomefantasia'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick="window.close()" style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {
//Parte de Itens Pendentes
	$sql = "SELECT ui.sigla, g.referencia, pi.discriminacao, ip.*, f.razaosocial, e.nomefantasia, p.* 
                FROM pedidos p, itens_pedidos ip, produtos_insumos pi, fornecedores f, empresas e, unidades ui, grupos g 
                WHERE ip.status < 2 and pi.id_unidade = ui.id_unidade and pi.id_produto_insumo = ip.id_produto_insumo and ip.id_pedido = p.id_pedido and (p.status = '1' or p.status = '2') and substring(p.data_emissao,6,2) = '$mes' and p.ativo = 1 and p.id_empresa = e.id_empresa and p.id_fornecedor = f.id_fornecedor and g.id_grupo = pi.id_grupo order by p.data_emissao desc";
	$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
	$linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
            $totalqtde  = 0;
            $totalvalor = 0;
            $totalvalorcomipi = 0;
            $totalqtde+= $campos2[$i]['qtde'];
            
            $sql = "SELECT SUM(nh.qtde_entregue) AS total_entregue 
                    FROM `nfe_historicos` nh, itens_pedidos ip, pedidos p 
                    WHERE ip.`id_item_pedido` = '".$campos2[$i]['id_item_pedido']."' 
                    AND ip.`id_pedido` = p.id_pedido 
                    AND ip.`id_item_pedido` = nh.`id_item_pedido` ";
            $campos3 = bancos::sql($sql);
            $total_entregue = $campos3[0]['total_entregue'];

            $preco_unitario =   $campos2[$i]['preco_unitario'];
            $total_rest     =   $totalqtde - $total_entregue;
            $valor_total+=      $campos2[$i]['valor_total'];
        }

	if($linhas == 0) {
?>
	<Script Language = 'JavaScript'>
            window.location = 'consultar_pedidos_itens.php?valor=1'
	</Script>
<?
        }else {
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
</head>
<body>
<form name='form' action='<?=$PHP_SELF.'?passo=3';?>' method='post' onsubmit='return validar(1)'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Itens de Pedido
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Qtde Solicitado
        </td>
        <td>
            Qtde Recebido
        </td>
        <td>
            Qtde Restante
        </td>
        <td>
            Un.
        </td>
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Preço Unitário
        </td>
        <td>
            Valor Total
        </td>
        <td>
            N.º Pedido
        </td>
    </tr>
<?
			$pular = 0;
			for ($i = 0;  $i < $linhas; $i++) {
                                $url = '../../../pedidos/itens/itens.php?id_pedido='.$campos[$i]['id_pedido'].'&pop_up=1';
				$totalqtde = 0;
				$totalvalorcomipi = 0;

				$totalqtde = $totalqtde + $campos[$i]['qtde'];
				$totalqtde2 = str_replace('.', ',', $campos[$i]['qtde']);

				$sql = "SELECT sum(nh.qtde_entregue) as total_entregue 
                                        from nfe_historicos nh, itens_pedidos ip, pedidos p 
                                        where ip.id_item_pedido = '".$campos[$i]['id_item_pedido']."' 
                                        and ip.id_pedido = p.id_pedido 
                                        and ip.id_item_pedido = nh.id_item_pedido";
				$campos2 = bancos::sql($sql);
				$total_entregue = $campos2[0]['total_entregue'];

				$total_restante =  $totalqtde - $total_entregue;
				if($total_restante > 0) {//echo $total_restante;
?>
	<tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
		<td>
                    <?=$totalqtde2;?>
                </td>
		<td>
                    <?=number_format($total_entregue, 2, ',', '.');?>
                </td>
		<td>
                    <?=str_replace('.', ',', $total_restante);?>
                </td>
		<td align='left'>
                    <?=$campos[$i]['sigla'];?>
		</td>
		<td align='left'>
                    <?=$campos[$i]['referencia'];?>
		</td>
		<td align='left'>
                    <?=$campos[$i]['discriminacao'];?>
		</td>
		<td align='right'>
                <?
                    if($campos[$i]['tp_moeda'] == 1) {
                        $tipo_moeda = 'R$ ';
                    }else if($campos[$i]['tp_moeda'] == 2) {
                        $tipo_moeda = 'U$ ';
                    }else {
                        $tipo_moeda = '&euro;';
                    }
                    echo $tipo_moeda.str_replace('.', ',', $campos[$i]['preco_unitario']);
                ?>
		</td>
		<td align='right'>
                    <?=$tipo_moeda.str_replace('.', ',', $campos[$i]['valor_total']);?>
		</td>
		<td align='center'>
                    <a href='<?=$url;?>' class='html5lightbox'>
                        <?=$campos[$i]['id_pedido'];?>
                    </a>
		</td>
	</tr>
<?							$pular ++;
						}
					}
?>
	<tr class='linhacabecalho' align='center'>
            <td colspan="11">
                <input type="button" name="cmd_fechar" value="Fechar" style="color:red" onclick="window.close()" class="botao">
            </td>
	</tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
</html>
<?
	}
}else {
?>
<html>
<head>
<title>.:: Consultar Pedidos / Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function relatorio() {
    if(document.form.opt_opcao[0].checked == true) {
        window.location = 'consultar_pedidos_itens.php?passo=1&mes=<?=$mes;?>'
    }else if(document.form.opt_opcao[1].checked == true) {
        window.location = 'consultar_pedidos_itens.php?passo=2&mes=<?=$mes;?>'
    }
}
</Script>
</head>
<body>
<form name="form" method="POST" action="">
<table width="60%" align='center' cellpadding="1" cellspacing="1">
    <tr class='linhacabecalho'>
        <td align='center'>
            Consultar Pedidos / Itens
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type="radio" name="opt_opcao" value="1" id="opt1" checked>
            <label for="opt1">Visualizar Pedidos</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type="radio" name="opt_opcao" value="3" id="opt2">
            <label for="opt2">Visualizar Itens</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            <input type="button" name="cmd_avancar" value="&gt;&gt; Avançar &gt;&gt;" title="Avançar" onclick="relatorio()" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>