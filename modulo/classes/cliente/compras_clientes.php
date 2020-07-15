<?
require('../../../lib/segurancas.php');
require('../../classes/array_sistema/array_sistema.php');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');

/********************************Produtos Faturados para o Cliente******************************/
//Listagem de Todos os Produtos Faturados para o Cliente ...
$sql = "SELECT SUM((pvi.`qtde` - pvi.`qtde_devolvida`) * pvi.`preco_liq_final`) AS total, ed.`id_empresa_divisao`, c.`razaosocial` AS cliente, 
        ed.`razaosocial`, CONCAT(ed.`razaosocial`, ' - ', f.`nome`) AS vendas, ged.`id_gpa_vs_emp_div`, f.`id_familia` 
        FROM `clientes` c 
        INNER JOIN `pedidos_vendas` pv ON pv.`id_cliente` = c.`id_cliente` 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
        INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
        WHERE YEAR(pv.`data_emissao`) = '$_GET[ano]' 
        AND pv.`id_cliente` = '$_GET[id_cliente]' 
        GROUP BY f.`id_familia`, ed.`id_empresa_divisao` ORDER BY ed.`id_empresa_divisao`, f.`nome` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        alert('NÃO EXISTE(M) PRODUTO(S) COMPRADO(S) NESSE ANO DE "<?=$_GET[ano];?>" PARA ESSE CLIENTE !')
        window.close()
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Produto(s) Comprado(s) em <?=$ano;?> ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Produto(s) Comprado(s) em 
            <font color='yellow'>
                <?=$ano;?> 
            </font>
            pelo 
            <font color='yellow'>
                <?=$campos[0]['cliente'];?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='3'>
            Divisão / Família
        </td>
        <td>
            Valor R$
        </td>
    </tr>
<?
    $id_empresa_divisao_current = $campos[0]['id_empresa_divisao'];
        
    for($i = 0; $i < $linhas; $i++) {
        $id_familia         = $campos[$i]['id_familia'];
        $id_empresa_divisao = $campos[$i]['id_empresa_divisao'];
            
        $sql = "SELECT SUM((pvi.`qtde` - pvi.`qtde_devolvida`) * pvi.`preco_liq_final`) AS total, SUM(pvi.`qtde` - pvi.`qtde_devolvida`) AS qtde_total, 
                ed.`id_empresa_divisao`, c.`razaosocial` AS cliente, ed.`razaosocial`, CONCAT(ed.`razaosocial`, ' - ', f.`nome`) AS vendas, 
                pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao` 
                FROM `clientes` c 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_cliente` = c.`id_cliente` 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` AND ged.`id_empresa_divisao` = '$id_empresa_divisao' 
                INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` AND f.`id_familia` = '$id_familia' 
                WHERE YEAR(pv.`data_emissao`) = '$ano' 
                AND pv.`id_cliente` = '$id_cliente' GROUP BY pa.`id_produto_acabado` ORDER BY pa.`discriminacao` ";
        $campos_produtos = bancos::sql($sql);
        $linhas_produtos = count($campos_produtos);
?>
    <tr class='linhanormal'>
        <td colspan='7'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <font color='blue'>
                <b><?=$campos[$i]['vendas'];?></b>
            </font>
        </td>
        <td align='right'>
            <font color='blue'>
                <b><?=segurancas::number_format($campos[$i]['total'], 2, '.');?></b>
            </font>
        </td>
    </tr>
<?
        for($j = 0; $j < $linhas_produtos; $j++) {
?>
    <tr class='linhanormal'>
        <td align='right'>
            <?=number_format($campos_produtos[$j]['qtde_total'], 2, ',', '.');?>
        </td>
        <td>
            <?=$campos_produtos[$j]['referencia'];?>
        </td>
        <td>
            <?=$campos_produtos[$j]['discriminacao'];?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($campos_produtos[$j]['total'], 2, '.');?>
        </td>
    </tr>
<?
        }

        $sub_total_divisao+= $campos[$i]['total'];
        $total_geral+= $campos[$i]['total'];
        
        if($id_empresa_divisao_current != $campos[$i + 1]['id_empresa_divisao']) {//Trocou a Divisão
?>
    <tr class='linhanormal' align='right'>
        <td align='right' colspan='3'>
            <font size='2' color='green'>
                <b><?='TOTAL DA '.$campos[$i]['razaosocial'];?></b>
            </font>
        </td>
        <td align='right'>
            <font size='1' color='green'>
                <b>R$ <?=number_format($sub_total_divisao, 2, ',', '.');?></b>
            </font>
        </td>
    </tr>
<?
            $sub_total_divisao = 0;
            $id_empresa_divisao_current = $campos[$i + 1]['id_empresa_divisao'];
        }
    }
?>
    <tr class='linhanormaldestaque' align='right'>
        <td colspan='7'>
            <b><?="<font size='2' color='blue'>TOTAL R$ ".number_format($total_geral, 2, ',', '.');?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            &nbsp;
        </td>
    </tr>
</table>
</body>
</html>
<?}?>