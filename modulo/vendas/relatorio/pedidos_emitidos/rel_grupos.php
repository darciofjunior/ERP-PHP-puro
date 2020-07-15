<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/pedidos_emitidos/pedidos_emitidos.php', '../../../../');

$id_empresa_divisao = (!empty($_GET['cmb_empresa_divisao'])) ? $_GET['cmb_empresa_divisao'] : '%';

//Busca a Venda Total de todos os PAs da determinada Empresa por Divisão no Período passado por parâmetro ...
$sql = "SELECT c.`id_cliente`, c.`id_pais`, CONCAT(gpa.`nome`, ' (', ed.razaosocial, ')') AS nome, ged.`id_gpa_vs_emp_div`, 
        IF(c.`id_pais` = '31', (pvi.`qtde` * pvi.`preco_liq_final`), (pvi.`qtde` * pvi.`preco_liq_final` * ov.`valor_dolar`)) AS total_pedido_vendas, 
        pvi.`margem_lucro` 
        FROM `produtos_acabados` pa 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` AND ged.`id_empresa_divisao` LIKE '$id_empresa_divisao' 
        INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` = '$_GET[id_familia]' 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_produto_acabado` = pa.`id_produto_acabado` 
        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
        INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
        INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`data_emissao` BETWEEN '$_GET[data_inicial]' AND '$_GET[data_final]' AND pv.`liberado` = '1' 
        INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` ORDER BY gpa.`nome` ";
$campos	= bancos::sql($sql);
$linhas	= count($campos);
$indice     = 0;

for($i = 0; $i < $linhas; $i++) {
    if(!in_array($campos[$i]['nome'], $vetor_grupo_pa)) {//NAO CONSTA NO ARRAY
        $vetor_grupo_pa[]       = $campos[$i]['nome'];
        $vetor_gpa_vs_emp_div[] = $campos[$i]['id_gpa_vs_emp_div'];
        $indice++;
    }
    $vetor_total[$campos[$i]['nome']]+= $campos[$i]['total_pedido_vendas'];
    $vetor_mlmg[$campos[$i]['nome']]+=  $campos[$i]['margem_lucro'];

    if($campos[$i]['margem_lucro'] != '-100.00') {
        $vetor_custo_ml_zero[$campos[$i]['nome']]+= $campos[$i]['total_pedido_vendas'] / (1 + $campos[$i]['margem_lucro'] / 100);
        $total_custo_ml_zero+=                      $campos[$i]['total_pedido_vendas'] / (1 + $campos[$i]['margem_lucro'] / 100);
    }
    $total_pedidos_emitidos+= $campos[$i]['total_pedido_vendas'];
}
?>
<html>
<head>
<title>.:: Relat&oacute;rio de Pedidos Emitidos por Família - Grupo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Relat&oacute;rio de Pedidos Emitidos por Família - Grupo
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Produto
        </td>
        <td>
            Total em R$
        </td>
        <td>
            Porcentagem(ns)
        </td>
        <td>
            <font title='Margem de Lucro Média Gravada' style='cursor:help'>
                M.L.M.G.
            </font>
        </td>
        <td>
            Lucro em <br/> Reais
        </td>
    </tr>
<?
    $total_grupo_pas = count($vetor_grupo_pa);
    for($i = 0; $i < $total_grupo_pas; $i++) {
?>
    <tr class='linhanormal' align='right'>
        <td align='left'>
            <a href="javascript:nova_janela('rel_venda_produto.php?id_gpa_vs_emp_div=<?=$vetor_gpa_vs_emp_div[$i];?>&data_inicial=<?=$data_inicial;?>&data_final=<?=$data_final;?>', 'Relatorio', '', '', '', '', '600', '900', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <?=$vetor_grupo_pa[$i];?>
            </a>
        </td>
        <td>
            <?=number_format($vetor_total[$vetor_grupo_pa[$i]], 2, ',', '.');?>
        </td>               
        <td>
        <?
            $porc_parcial = ($vetor_total[$vetor_grupo_pa[$i]] / $total_pedidos_emitidos) * 100;
            $porc_total+= $porc_parcial;
            echo number_format($porc_parcial, 2, ',', '.');
        ?>
        %
        </td>
        <td>
        <?
            $mlmg_pa = ($vetor_total[$vetor_grupo_pa[$i]] / $vetor_custo_ml_zero[$vetor_grupo_pa[$i]] - 1) * 100;
            echo number_format($mlmg_pa, 2, ',', '.');
        ?> %
        </td>
        <td>
        <?
            $custo_ml_zero = round($vetor_total[$vetor_grupo_pa[$i]], 2) / (1 + round($mlmg_pa, 2) / 100);
            $lucro_rs      = round($vetor_total[$vetor_grupo_pa[$i]], 2) - $custo_ml_zero;
            echo number_format($lucro_rs, 2, ',', '.');                                                                                                                                                                                                                                                                      
            $lucro_total_reais+= $lucro_rs;
        ?>
        </td>                 
    </tr>
<?
    }
?>
    <tr class='linhanormal' align='right'>
        <td colspan='2'>
            <font color='red' size='2'>
                <b>Total Geral: </b><?=number_format($total_pedidos_emitidos, 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font color='red' size='2'>
                <?=number_format($porc_total, 2, ',', '.');?> %
            </font>
        </td>
        <td>
            <font color='red' size='2'>
                <?=number_format(($total_pedidos_emitidos / $total_custo_ml_zero - 1) * 100, 2, ',', '.');?> %
            </font>
        </td>
        <td>
            <font color='red' size='2'>
                <?=number_format($lucro_total_reais, 2, ',', '.');?>
            </font>
        </td>          
    </tr>
    <tr class='linhanormal'>
        <td colspan='5'>
            <font size='2' color='red'>
                <b>PEDIDOS EXPORT USAM O U$ DO ORÇAMENTO.</b>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='parent.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</body>
</html>
<pre>
<font color='red'><b>
* Existe discrepância entre o Relatório da Tela abaixo com este aqui que é por Grupo no campo 
Lucro em R$, pois a Tela de Baixo é feita sobre o Total da Família e está Tela é feito pelo Somatório
Total por Grupos.
</b></font>
</pre>