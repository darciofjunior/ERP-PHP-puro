<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
?>
<html>
<head>
<title>.:: Itens de Pedido com ML <= 70% da ML Mínima ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Itens de Pedido com ML <= 70% da ML Mínima
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Qtde Restante
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
            Preço Total
        </td>
        <td>
            M.L.G
        </td>
        <td>
            ML.min Grupo
        </td>
        <td>
            N° Pedido
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            Cliente
        </td>
    </tr>
<?
    $sql = "SELECT IF(c.nomefantasia = '', c.razaosocial, c.nomefantasia) AS cliente, DATE_FORMAT(pv.data_emissao, '%d/%m/%Y') AS data_emissao, pvi.* 
            FROM `pedidos_vendas_itens` pvi 
            INNER JOIN `pedidos_vendas` pv ON pv.id_pedido_venda = pvi.id_pedido_venda 
            INNER JOIN `clientes` c ON c.id_cliente = pv.id_cliente 
            WHERE pvi.`status` IN (0,1) 
            AND pvi.`margem_lucro` <= '70' 
            AND (pvi.qtde - pvi.vale - pvi.qtde_faturada > '0') ORDER BY pv.data_emissao ";
    $campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
    $linhas = count($campos);
		
    for($i = 0; $i < $linhas; $i++) {
        $qtde_restante  = $campos[$i]['qtde'] - $campos[$i]['vale'] - $campos[$i]['qtde_faturada'];
        $preco_total    = $campos[$i]['preco_liq_final'] * $qtde_restante;
        
        //Aqui eu busco mais alguns dados do Item do Pedido de Venda ...
        $sql = "SELECT ged.margem_lucro_minima, pa.referencia, pa.discriminacao 
                FROM `produtos_acabados` pa 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                WHERE pa.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
        $campos_item_pedido = bancos::sql($sql);
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$qtde_restante;?>
        </td>
        <td>
            <?=$campos_item_pedido[0]['referencia'];?>
        </td>		
        <td align='left'>
            <?=$campos_item_pedido[0]['discriminacao'];?>
            <font color='red' size='-2'>
            <?
                if($campos[$i]['status_top'] == 1) {
                    echo  "<font color='red' style='cursor:help;' title='1º 50% dos PA´s TOP'> (TopA)</font>";
                }else if($campos[$i]['status_top'] == 2) {
                    echo  "<font color='red' style='cursor:help;' title='2º 50% dos PA´s TOP'> (TopB)</font>";
                }
            ?>
            </font>
        </td>		
        <td align='right'>
            R$ <?=$campos[$i]['preco_liq_final'];?>
        </td>		
        <td align='right'>
            R$ <?=number_format($preco_total, 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['margem_lucro'], 2, ',', '.');?> %
        </td>		
        <td align='right'>
        <?
            //Se a Margem de Lucro < 70%, não aplicamos a redução de 5% pois a ML ficaria abaixo de 45% para Atacadista.
            if($campos[$i]['status_top'] == 1) {
                $margem_lucro_minima_corr = ($campos_item_pedido[0]['margem_lucro_minima'] <= 70) ? $campos_item_pedido[0]['margem_lucro_minima'] : $campos_item_pedido[0]['margem_lucro_minima'] * 0.95;
            }else if($campos[$i]['status_top'] == 2) {
                $margem_lucro_minima_corr = $campos_item_pedido[0]['margem_lucro_minima'];
            }else if($campos[$i]['status_top'] == 0) {
                $margem_lucro_minima_corr = $campos_item_pedido[0]['margem_lucro_minima'] * 1.1;
            }
            echo number_format($margem_lucro_minima_corr, 2, ',', '.');
        ?> %
        </td>
        <td>
            <a href = '../../../faturamento/nota_saida/itens/detalhes_pedido.php?id_pedido_venda=<?=$campos[$i]['id_pedido_venda'];?>' class='html5lightbox'>
                <?=$campos[$i]['id_pedido_venda'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>	
        <td align='left'>
            <?=$campos[$i]['cliente'];?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>