<?
require('../../../lib/segurancas.php');
session_start('funcionarios');

//Aqui eu busco todas as Empresas Divisões cadastradas no sistema ...
$sql = "SELECT `id_empresa_divisao`, `razaosocial` 
        FROM `empresas_divisoes` 
        WHERE `ativo` = '1' ORDER BY `razaosocial` ";
$campos_empresas_divisoes = bancos::sql($sql);
$linhas_empresas_divisoes = count($campos_empresas_divisoes);
?>
<html>
<head>
<title>.:: Volume de Compra(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<table width='100%' cellspacing ='1' cellpadding='1' border='0' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='<?=$linhas_empresas_divisoes + 2;?>'>
            Volume de Compra(s) - Pedidos
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            ANO
        </td>
        <?
            $sql = "SELECT pvi.`id_produto_acabado`, ((pvi.`qtde` - pvi.`qtde_devolvida`) * pvi.`preco_liq_final`) AS total, YEAR(pv.`data_emissao`) AS ano 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
                    INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                    WHERE pv.`id_cliente` = '$id_cliente' 
                    ORDER BY YEAR(pv.`data_emissao`) ";
            $campos_faturamento = bancos::sql($sql);
            $linhas_faturamento = count($campos_faturamento);
            for($i = 0; $i < $linhas_faturamento; $i++) {
                $sql = "SELECT ged.`id_empresa_divisao` 
                        FROM `produtos_acabados` pa 
                        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                        WHERE pa.`id_produto_acabado` = '".$campos_faturamento[$i]['id_produto_acabado']."' LIMIT 1 ";
                $campos_pedido_venda_item = bancos::sql($sql);
                $vetor_faturamento[$campos_faturamento[$i]['ano']][$campos_pedido_venda_item[0]['id_empresa_divisao']]+= $campos_faturamento[$i]['total'];
            }
                        
            //Aqui busca todos os representantes que estão atrelados a esse Cliente ...
            for($i = 0; $i < $linhas_empresas_divisoes; $i++) {
        ?>
        <td>
            <?=$campos_empresas_divisoes[$i]['razaosocial'];?>
        </td>
        <?
            }
        ?>
        <td>
            TOTAL R$
        </td>
    </tr>
<?
	for($ano = (date('Y') - 5); $ano <= date('Y'); $ano++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <a href = "javascript:nova_janela('compras_clientes.php?id_cliente=<?=$id_cliente;?>&ano=<?=$ano;?>', 'COMPRAS_CLIENTES', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <b><?=$ano;?> NOVO (FAT)</b>
            </a>
        </td>
        <?
            $total_por_ano = 0;
            for($i = 0; $i < $linhas_empresas_divisoes; $i++) {
        ?>
        <td>
            <?=number_format($vetor_faturamento[$ano][$campos_empresas_divisoes[$i]['id_empresa_divisao']], 2, ',', '.');?>
        </td>
        <?
                $total_por_ano+= $vetor_faturamento[$ano][$campos_empresas_divisoes[$i]['id_empresa_divisao']];
            }
        ?>
        <td align='right'>
            <?=number_format($total_por_ano, 2, ',', '.');?>
        </td>
    </tr>
<?
            $total_geral+= $total_por_ano;
	}
?>
    <tr class='linhacabecalho' align='right'>
        <td colspan='<?=$linhas_empresas_divisoes + 2;?>'>
            Total Geral => 
            <font color='yellow'>
                <?=number_format($total_geral, 2, ',', '.');?>
            </font>
        </td>
    </tr>
</table>
</body>
</html>