<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/pedidos_emitidos/pedidos_emitidos.php', '../../../../');

$valor_dolar_dia    = genericas::moeda_dia('dolar');

//Significa que o usuário escolheu alguma divisão ...
if(!empty($_GET['id_empresa_divisao'])) {
    $condicao1 = ' AND ed.id_empresa_divisao ='.$_GET['id_empresa_divisao'];
    $condicao2 = ' AND ged.id_empresa_divisao = '.$_GET['id_empresa_divisao'];
}
	
$sql = "SELECT c.`id_pais`, SUM(IF(c.`id_pais` = '31', ((pvi.`qtde` - pvi.`qtde_devolvida`) * pvi.`preco_liq_final`), ((pvi.`qtde` - pvi.`qtde_devolvida`) * pvi.`preco_liq_final` * $valor_dolar_dia))) AS total 
        FROM `empresas_divisoes` ed 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_empresa_divisao` = ed.`id_empresa_divisao` $condicao2 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
        INNER JOIN `produtos_acabados` pa ON pa.`id_gpa_vs_emp_div` = ged.`id_gpa_vs_emp_div` 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_produto_acabado` = pa.`id_produto_acabado` AND pvi.`id_representante` = '$_GET[id_representante]' 
        INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
        INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
        INNER JOIN `clientes_vs_representantes` crp ON crp.`id_cliente` = c.`id_cliente` AND crp.`id_empresa_divisao` = ed.`id_empresa_divisao` 
        WHERE pv.`data_emissao` BETWEEN '$_GET[data_inicial]' AND '$_GET[data_final]' 
        AND pv.`liberado` = '1' GROUP BY c.`id_pais` ";
$campos_total = bancos::sql($sql);
$linhas_total = count($campos_total);
for($i = 0; $i < $linhas_total; $i++) {
    $total_pedidos_emitidos+= ($campos_total[$i]['id_pais'] == 31) ? $campos_total[$i]['total'] : ($campos_total[$i]['total'] * $valor_dolar_dia);
}
              
//Aqui eu trago os melhores Clientes do Período ...
$sql = "SELECT DISTINCT(c.id_cliente), c.razaosocial, ed.razaosocial divisao, f.nome nome_familia, crp.desconto_cliente, 
        IF(c.id_pais = 31, SUM((pvi.`qtde` - pvi.`qtde_devolvida`) * pvi.preco_liq_final), SUM((pvi.`qtde` - pvi.`qtde_devolvida`) * pvi.preco_liq_final * $valor_dolar_dia)) AS total 
        FROM `empresas_divisoes` ed 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_empresa_divisao = ed.id_empresa_divisao 
        INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
        INNER JOIN `familias` f ON f.id_familia = gpa.id_familia 
        INNER JOIN `produtos_acabados` pa ON pa.id_gpa_vs_emp_div = ged.id_gpa_vs_emp_div 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_produto_acabado = pa.id_produto_acabado AND pvi.id_representante = '$_GET[id_representante]' 
        INNER JOIN `pedidos_vendas` pv ON pv.id_pedido_venda = pvi.id_pedido_venda 
        INNER JOIN `clientes` c ON c.id_cliente = pv.id_cliente 
        INNER JOIN `clientes_vs_representantes` crp ON crp.id_cliente = c.id_cliente AND crp.id_empresa_divisao = ed.id_empresa_divisao $condicao1 
        inner join representantes rep on rep.id_representante=crp.id_representante
        WHERE pv.`data_emissao` BETWEEN '$_GET[data_inicial]' AND '$_GET[data_final]' 
        AND pv.`liberado` = '1' GROUP BY c.id_cliente ORDER BY total DESC ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Relat&oacute;rio dos Maior(es) Comprador(es) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<table border="0" width='95%' align="center" cellspacing ='1' cellpadding='1'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Relat&oacute;rio dos Maior(es) Comprador(es)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='4'>
            <font color="Yellow">Desc. Cliente: </font>
            <?
//Significa que o usuário escolheu alguma divisão ...
                if(!empty($id_empresa_divisao)) {
                    echo $campos[0]['divisao'];
//O usuário não escolheu nenhuma divisão, significa que ele deseje enxergar o Valor de Tudo ...
                }else {
                    echo 'TOTAL';
                }
            ?>
            <font color="Yellow">Representante</font>
            <?
//Busca do nome do Representante ...
                $sql = "SELECT nome_fantasia 
                        FROM `representantes` 
                        WHERE `id_representante` = '$id_representante' LIMIT 1 ";
                $campos_rep = bancos::sql($sql);
                echo $campos_rep[0]['nome_fantasia'];
            ?>
            <font color="Yellow">
                Data de
            </font>
            <?=data::datetodata($data_inicial,'/');?>
            <font color="Yellow"> à </font>
            <?=data::datetodata($data_final,'/');?>
        </td>
    </tr>
    <tr class='linhadestaque' align="center">
        <td>Cliente</td>
        <td>Desconto</td>
        <td>Total em R$ </td>
        <td>Porcentagem(ns)</td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal'>
        <td>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($campos[$i]['desconto_cliente'], 2, '.');?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($campos[$i]['total'], 2, '.');?>
        </td>
        <td align='right'>
        <?
            $porc_parcial = ($campos[$i]['total'] / $total_pedidos_emitidos) * 100;
            $porc_total+= $porc_parcial;
            echo number_format($porc_parcial, 2, ',', '.').' %';
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhanormal'>
        <td colspan='3' align="right">
            <font color='red' size='2'>
                <b>Total Geral:</b> <?=number_format($total_pedidos_emitidos, 2, ',', '.');?>
            </font>
        </td>
        <td align='right'>
            <font color='red' size='2'>
                <?=number_format($porc_total, 2, ',', '.');?>%
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            Valor Dolar dia R$: <?=number_format($valor_dolar_dia, 4, ',', '.');?>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='4'>
            <input type="button" name="cmd_imprimir" id="cmd_imprimir" title='Imprimir' value="Imprimir" onclick="print()" class="botao">
            <input type="button" name="cmd_fechar" id="cmd_fechar" title='Fechar' value="Fechar" onclick="window.close()" style="color:red" class="botao">
        </td>
    </tr>
</table>
</body>
</html>