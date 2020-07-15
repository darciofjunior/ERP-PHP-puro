<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/pedidos_pendentes/pedidos_pendentes.php', '../../../../');
$valor_dolar_dia = genericas::moeda_dia('dolar');

/********************************************************************************************/
//Busca os pedidos pendentes Faturáveis dos Clientes Nacionais por Divisão ...
$sql = "SELECT f.id_familia, f.nome, ed.razaosocial emp_divisao, SUM(pvi.qtde * pvi.preco_liq_final) AS total 
        FROM `pedidos_vendas` pv 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`status` = '0' 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
        INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
        INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` AND ed.`id_empresa_divisao` = '$_GET[id_empresa_divisao]' 
        INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND c.id_pais = '31'
        WHERE pv.liberado = '1' 
        GROUP BY f.id_familia ORDER BY f.nome ";
$campos_nac = bancos::sql($sql);
//Busca os pedidos pendentes Faturáveis dos Clientes Internacionais - Exportações por Divisão ...
$sql = "SELECT f.id_familia, f.nome, ed.razaosocial emp_divisao, SUM(pvi.qtde * pvi.preco_liq_final) AS total 
        FROM `pedidos_vendas` pv 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`status` = '0' 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
        INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
        INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` AND ed.`id_empresa_divisao` = '$_GET[id_empresa_divisao]' 
        INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND c.id_pais <> '31'
        WHERE pv.liberado = '1' 
        GROUP BY f.id_familia ORDER BY f.nome ";
$campos_exp = bancos::sql($sql);
/********************************************************************************************/
//Busca os pedidos pendentes não Faturáveis dos Clientes Nacionais por Divisão ...
$sql = "SELECT f.id_familia, f.nome, ed.razaosocial emp_divisao, SUM(pvi.qtde * pvi.preco_liq_final) AS total 
        FROM `pedidos_vendas` pv 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`status` = '1' 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
        INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
        INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` AND ed.`id_empresa_divisao` = '$_GET[id_empresa_divisao]' 
        INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND c.id_pais = '31'
        WHERE pv.liberado = '1' 
        GROUP BY f.id_familia ORDER BY f.nome ";
$campos_p_nac = bancos::sql($sql);
//Busca os pedidos pendentes não Faturáveis dos Clientes Internacionais - Exportações por Divisão ...
$sql = "SELECT f.id_familia, f.nome, ed.razaosocial emp_divisao, SUM(pvi.qtde * pvi.preco_liq_final) AS total 
        FROM `pedidos_vendas` pv 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`status` = '1' 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
        INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
        INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` AND ed.`id_empresa_divisao` = '$_GET[id_empresa_divisao]' 
        INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND c.id_pais <> '31'
        WHERE pv.liberado = '1' 
        GROUP BY f.id_familia ORDER BY f.nome ";
$campos_p_exp = bancos::sql($sql);
/********************************************************************************************/
?>
<html>
<head>
<title>.:: Consultar ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<table width='90%' border=0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Relat&oacute;rio de Pedidos Pendentes &agrave; Faturar Vs Fam&iacute;lia (<?=$campos_nac[0]['emp_divisao'];?>)
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td>
            Fam&iacute;lia
        </td>
        <td>
            Total em R$
        </td>
        <td>
            Total U$
        </td>
        <td>
            Total Geral R$
        </td>
    </tr>
<?
$familia_nac = array();
$familia_exp = array();

//Disparando os Laços ...
for($i = 0; $i < count($campos_nac); $i++) {
    if(array_key_exists($campos_nac[$i]['nome'], $familia_nac)) {//Sim o elemento consta no array
        $familia_nac[$campos_nac[$i]['nome']]+= $campos_nac[$i]['total'];//"O elemento 'tal' está no array!";
    }else {// NAO CONSTA NO ARRAY
        $familia_nac[$campos_nac[$i]['nome']] = $campos_nac[$i]['total']; //$familia = array("primeiro" => 1, "segundo" => 4);
    }
}

for($i = 0; $i < count($campos_exp); $i++) {
    if(array_key_exists($campos_exp[$i]['nome'], $familia_exp)) {//sim o elemento consta no array
        $familia_exp[$campos_exp[$i]['nome']]+= $campos_exp[$i]['total'];//"O elemento 'tal' está no array!";
    }else {// NAO CONSTA NO ARRAY
        $familia_exp[$campos_exp[$i]['nome']] = $campos_exp[$i]['total']; //$familia = array("primeiro" => 1, "segundo" => 4);
    }
}

for($i = 0; $i < count($campos_p_nac); $i++) {
    if(array_key_exists($campos_p_nac[$i]['nome'], $familia_nac)) {//sim o elemento consta no array
        $familia_nac[$campos_p_nac[$i]['nome']]+= $campos_p_nac[$i]['total'];//"O elemento 'tal' está no array!";
    }else {// NAO CONSTA NO ARRAY
        $familia_nac[$campos_p_nac[$i]['nome']] = $campos_p_nac[$i]['total']; //$familia = array("primeiro" => 1, "segundo" => 4);
    }
}

for($i = 0; $i < count($campos_p_exp); $i++) {
    if(array_key_exists($campos_p_exp[$i]['nome'], $familia_exp)) {//sim o elemento consta no array
       $familia_exp[$campos_p_exp[$i]['nome']]+= $campos_p_exp[$i]['total'];//"O elemento 'tal' está no array!";
    }else {// NAO CONSTA NO ARRAY
       $familia_exp[$campos_p_exp[$i]['nome']] = $campos_p_exp[$i]['total']; //$familia = array("primeiro" => 1, "segundo" => 4);
    }
}
	
//Busca todas as Famílias cadastradas ...
$sql = "SELECT id_familia, nome 
        FROM familias 
        WHERE ativo = '1' ORDER BY nome ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    if($familia_nac[$campos[$i]['nome']] > 0 || $familia_exp[$campos[$i]['nome']] > 0) {
?>
    <tr class='linhanormal'>
        <td>
            <a href="javascript:nova_janela('rel_grupos.php?id_familia=<?=$campos[$i]['id_familia'];?>&id_empresa_divisao=<?=$_GET['id_empresa_divisao'];?>', 'CONSULTAR', '', '', '', '', '350', '750', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td>
            <?=number_format($familia_nac[$campos[$i]['nome']], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($familia_exp[$campos[$i]['nome']], 2, ',', '.');?>
        </td>
        <td>
        <?
            $total_parcial = $familia_nac[$campos[$i]['nome']] + ((float)$familia_exp[$campos[$i]['nome']] * $valor_dolar_dia);
            $total_geral+= $total_parcial;
            echo number_format($total_parcial, 2, ',', '.');
        ?>
        </td>
    </tr>
<?
    }
}
?>
    <tr class='linhanormal'>
        <td colspan='4'>
            <font color='red' size='2'>
                <b>Total Geral:</b> <?=number_format($total_geral, 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            Valor Dolar dia R$: <?=number_format($valor_dolar_dia, 4, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            &nbsp;
        </td>
    </tr>
</table>
</body>
</html>