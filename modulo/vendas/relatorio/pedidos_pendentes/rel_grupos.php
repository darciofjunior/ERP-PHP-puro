<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/pedidos_pendentes/pedidos_pendentes.php', '../../../../');
$valor_dolar_dia = genericas::moeda_dia('dolar');

//Pedidos Nacionais com Pendência Total ...
$sql = "SELECT gpa.id_grupo_pa, gpa.nome, f.nome nome_familia, ed.id_empresa_divisao, SUM(pvi.qtde * pvi.preco_liq_final) AS total 
        FROM `pedidos_vendas` pv  
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`status` = '0' 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` AND ed.`id_empresa_divisao` = '$_GET[id_empresa_divisao]' 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` = '$_GET[id_familia]' 
        INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
        INNER JOIN `clientes` c ON pv.`id_cliente` = c.`id_cliente` AND c.`id_pais` = '31' 
        WHERE pv.`liberado` = '1' 
        GROUP BY gpa.id_grupo_pa ORDER BY gpa.nome ";
$campos_nac = bancos::sql($sql);

//Pedidos de Exportação com Pendência Total ...
$sql = "SELECT gpa.id_grupo_pa, gpa.nome, f.nome nome_familia, ed.id_empresa_divisao, SUM(pvi.qtde * pvi.preco_liq_final) AS total 
        FROM `pedidos_vendas` pv  
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`status` = '0' 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` AND ed.`id_empresa_divisao` = '$_GET[id_empresa_divisao]' 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` = '$_GET[id_familia]' 
        INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
        INNER JOIN `clientes` c ON pv.`id_cliente` = c.`id_cliente` AND c.`id_pais` <> '31' 
        WHERE pv.`liberado` = '1' 
        GROUP BY gpa.id_grupo_pa ORDER BY gpa.nome ";
$campos_exp = bancos::sql($sql);

//Pedidos Nacionais com Pendência Parcial ...
$sql = "SELECT gpa.id_grupo_pa, gpa.nome, f.nome nome_familia, ed.id_empresa_divisao, SUM(pvi.qtde * pvi.preco_liq_final) AS total 
        FROM `pedidos_vendas` pv  
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`status` = '1' 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` AND ed.`id_empresa_divisao` = '$_GET[id_empresa_divisao]' 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` = '$_GET[id_familia]' 
        INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
        INNER JOIN `clientes` c ON pv.`id_cliente` = c.`id_cliente` AND c.`id_pais` = '31' 
        WHERE pv.`liberado` = '1' 
        GROUP BY gpa.id_grupo_pa ORDER BY gpa.nome ";
$campos_p_nac = bancos::sql($sql);

//Pedidos Exportação com Pendência Parcial ...
$sql = "SELECT gpa.id_grupo_pa, gpa.nome, f.nome nome_familia, ed.id_empresa_divisao, SUM(pvi.qtde * pvi.preco_liq_final) AS total 
        FROM `pedidos_vendas` pv  
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`status` = '1' 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` AND ed.`id_empresa_divisao` = '$_GET[id_empresa_divisao]' 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` = '$_GET[id_familia]' 
        INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
        INNER JOIN `clientes` c ON pv.`id_cliente` = c.`id_cliente` AND c.`id_pais` <> '31' 
        WHERE pv.`liberado` = '1' 
        GROUP BY gpa.id_grupo_pa ORDER BY gpa.nome ";
$campos_p_exp = bancos::sql($sql);
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
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Relat&oacute;rio de Pedidos Pendentes &agrave; Faturar Vs Fam&iacute;lia<br/>
            <font color='yellow'>
                (<?=$campos_nac[0]['nome_familia'];?>)
            </font>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td>
            Grupos
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
$grupo_nac = array();
$grupo_exp = array();

//Disparando os Laços ...
for($i = 0; $i < count($campos_nac); $i++) {
    if(array_key_exists($campos_nac[$i]['nome'], $grupo_nac)) {//sim o elemento consta no array
        $grupo_nac[$campos_nac[$i]['nome']]+= $campos_nac[$i]['total'];//"O elemento 'tal' está no array!";
    }else {// NAO CONSTA NO ARRAY
        $grupo_nac[$campos_nac[$i]['nome']] = $campos_nac[$i]['total']; //$grupo = array("primeiro" => 1, "segundo" => 4);
    }
}

for($i = 0; $i < count($campos_exp); $i++) {
    if(array_key_exists($campos_exp[$i]['nome'], $grupo_exp)) {//sim o elemento consta no array
        $grupo_exp[$campos_exp[$i]['nome']]+= $campos_exp[$i]['total'];//"O elemento 'tal' está no array!";
    }else {// NAO CONSTA NO ARRAY
        $grupo_exp[$campos_exp[$i]['nome']] = $campos_exp[$i]['total']; //$grupo = array("primeiro" => 1, "segundo" => 4);
    }
}

for($i = 0; $i < count($campos_p_nac); $i++) {
    if(array_key_exists($campos_p_nac[$i]['nome'], $grupo_nac)) {//sim o elemento consta no array
        $grupo_nac[$campos_p_nac[$i]['nome']]+= $campos_p_nac[$i]['total'];//"O elemento 'tal' está no array!";
    }else {// NAO CONSTA NO ARRAY
        $grupo_nac[$campos_p_nac[$i]['nome']] = $campos_p_nac[$i]['total']; //$grupo = array("primeiro" => 1, "segundo" => 4);
    }
}

for($i = 0; $i < count($campos_p_exp); $i++) {
    if(array_key_exists($campos_p_exp[$i]['nome'], $grupo_exp)) {//sim o elemento consta no array
        $grupo_exp[$campos_p_exp[$i]['nome']]+= $campos_p_exp[$i]['total'];//"O elemento 'tal' está no array!";
    }else {// NAO CONSTA NO ARRAY
        $grupo_exp[$campos_p_exp[$i]['nome']] = $campos_p_exp[$i]['total']; //$grupo = array("primeiro" => 1, "segundo" => 4);
    }
}

//Busca todos os Grupos de PA cadastrados ...
$sql = "SELECT id_grupo_pa, nome 
        FROM `grupos_pas` 
        WHERE `ativo` = '1' ORDER BY nome ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    if($grupo_nac[$campos[$i]['nome']] > 0 || $grupo_exp[$campos[$i]['nome']] > 0) {
?>
    <tr class='linhanormal'>
        <td>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=number_format($grupo_nac[$campos[$i]['nome']], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($grupo_exp[$campos[$i]['nome']], 2, ',', '.');?>
        </td>
        <td>
        <?
            $total_parcial = $grupo_nac[$campos[$i]['nome']] + ((double)$grupo_exp[$campos[$i]['nome']] * $valor_dolar_dia);
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
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
<pre>
<font color='red'><b>
Atenção:</font> Esta somatória é baseada na Família vs Empresa Divisão, ou seja,
os totais apresentados pelos grupos demonstram os totais de pendências da Família 
selecionada juntamente com a Empresa Divisão escolhida anteriormente.</b>
</pre>
</html>