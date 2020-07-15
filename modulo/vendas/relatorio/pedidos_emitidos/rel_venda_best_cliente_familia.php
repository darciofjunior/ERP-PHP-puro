<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/pedidos_emitidos/pedidos_emitidos.php', '../../../../');

$valor_dolar_dia    = genericas::moeda_dia('dolar');
$ano4               = date('Y') - 4;

//Tratamento com as variáveis que vem por parâmetro ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cmb_empresa_divisao    = $_POST['cmb_empresa_divisao'];
    $id_familia             = $_POST['id_familia'];
    $data_inicial           = $_POST['data_inicial'];
    $data_final             = $_POST['data_final'];
}else {
    $cmb_empresa_divisao    = $_GET['cmb_empresa_divisao'];
    $id_familia             = $_GET['id_familia'];
    $data_inicial           = $_GET['data_inicial'];
    $data_final             = $_GET['data_final'];
}
?>
<html>
<head>
<title>.:: Relat&oacute;rio dos Maiores Compradores da Familia ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
</head>
<body>
<form name='form' method='post' action=''>
<input type='hidden' name='id_familia' value='<?=$id_familia;?>'>
<input type='hidden' name='data_inicial' value='<?=$data_inicial;?>'>
<input type='hidden' name='data_final' value='<?=$data_final;?>'>
<table width='95%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
        <?
//Busca do nome da família ...
            $sql = "SELECT `nome` 
                    FROM `familias` 
                    WHERE `id_familia` = '$id_familia' LIMIT 1 ";
            $campos_familia = bancos::sql($sql);
        ?>
            Relat&oacute;rio dos Maiores Compradores da Familia: 
            <font color='yellow'>
                <b>(<?=$campos_familia[0]['nome'];?>)</b>
            </font>
            -&nbsp;Empresa Divisão:&nbsp;
            <select name='cmb_empresa_divisao' title='Selecione a Empresa Divisão' onchange='document.form.submit()' class='combo'>
            <?
                $sql = "SELECT `id_empresa_divisao`, `razaosocial` 
                        FROM `empresas_divisoes`  
                        WHERE `ativo` = '1' ORDER BY `razaosocial` ";
                echo combos::combo($sql, $cmb_empresa_divisao);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Família
        </td>
        <td>
            Divisão
        </td>
        <td>
            Cliente
        </td>
        <td>
            E-mail
        </td>
        <td>
            <font title='Representante' style='cursor:help'>
                Repres.
            </font>
        </td>
        <td bgcolor='#C8C8C8'>
            Total<br><?=date('Y')-4;?>
        </td>
        <td bgcolor='#C8C8C8'>
            Total<br><?=date('Y')-3;?>
        </td>
        <td bgcolor='#C8C8C8'>
            Total<br><?=date('Y')-2;?>
        </td>
        <td bgcolor='#C8C8C8'>
            Total<br><?=date('Y')-1;?>
        </td>
        <td>
            <font title='Total do Período entre a Data Inicial e a Final solicitada em R$' style='cursor:help'>
                Total do Per. R$
            </font>
        </td>
        <td>
            %(s)
        </td>
        <td title='Margem de Lucro Média Gravada' style='cursor:help'>
            M.L.M.G.
        </td>
    </tr>
<?
    if($cmb_representante == '')    $cmb_representante      = '%';
    if($cmb_empresa_divisao == '')  $cmb_empresa_divisao    = '%';
    
/*Aqui eu busco todos os Produtos Acabados da Família passado por parâmetro e da Empresa Divisão selecionada 
pelo Usuário ...*/
    $sql = "SELECT pa.`id_produto_acabado` 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` AND ged.`id_empresa_divisao` LIKE '$cmb_empresa_divisao' 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` = '$id_familia' 
            WHERE pa.`ativo` = '1' ";
    $campos_produtos_acabados = bancos::sql($sql);
    $linhas_produtos_acabados = count($campos_produtos_acabados);
    if($linhas_produtos_acabados == 0) {
        $id_produtos_acabados = 0;
    }else {
        for($i = 0; $i < $linhas_produtos_acabados; $i++) $id_produtos_acabados.= $campos_produtos_acabados[$i]['id_produto_acabado'].', ';
        $id_produtos_acabados = substr($id_produtos_acabados, 0, strlen($id_produtos_acabados) - 2);
    }
//Busca o Total de Pedidos Emitidos do Período selecionado anteriomente na Tela abaixo pelo usuário do Rep ...
    $sql = "SELECT IF(c.`id_pais` = '31', SUM(pvi.`qtde` * pvi.`preco_liq_final`), SUM(pvi.`qtde` * pvi.`preco_liq_final` * $valor_dolar_dia)) AS total_pedidos_emitidos 
            FROM `pedidos_vendas_itens` pvi 
            INNER JOIN `pedidos_vendas` pv ON pv.id_pedido_venda = pvi.id_pedido_venda AND pv.`liberado` = '1' AND pv.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
            WHERE pvi.`id_representante` LIKE '$cmb_representante' 
            AND pvi.`id_produto_acabado` IN ($id_produtos_acabados) GROUP BY c.`id_cliente` ORDER BY total_pedidos_emitidos DESC ";
    $campos_total_pedido = bancos::sql($sql);
    for($i = 0; $i < count($campos_total_pedido); $i++) $total_pedidos_emitidos+= $campos_total_pedido[$i]['total_pedidos_emitidos'];

    $total_pedidos_emitidos = round($total_pedidos_emitidos, 2);
/**************************************************************************************************************/
/*Observação: A Margem de Lucro Gravada, só começou a vigorar em meados do fim de Junho de 2007, sendo assim 
não podemos utilizá-la para relatórios anteriores a 2008 devido retornar erro no cálculo ...*/
/**************************************************************************************************************/
    $sql = "SELECT IF(c.`id_pais` = 31, SUM(pvi.`qtde` * pvi.`preco_liq_final`), SUM(pvi.`qtde` * pvi.`preco_liq_final` * $valor_dolar_dia)) AS total, 
            pv.`id_cliente`, YEAR(pv.`data_emissao`) AS ano 
            FROM `pedidos_vendas_itens` pvi 
            INNER JOIN `pedidos_vendas` pv ON pv.id_pedido_venda = pvi.id_pedido_venda AND pv.`liberado` = '1' AND YEAR(pv.`data_emissao`) >= '$ano4' 
            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
            WHERE pvi.`id_representante` LIKE '$cmb_representante' 
            AND pvi.`id_produto_acabado` IN ($id_produtos_acabados) 
            GROUP BY YEAR(pv.`data_emissao`), pv.`id_cliente` ORDER BY pv.`id_cliente` ";
    $campos_vendas = bancos::sql($sql);
    $linhas_vendas = count($campos_vendas);
    for($i = 0; $i < $linhas_vendas; $i++) $vendas_anual_array[$campos_vendas[$i]['id_cliente']][$campos_vendas[$i]['ano']] = $campos_vendas[$i]['total'];

//Busca o Total de Pedidos Emitidos do Período selecionado anteriomente na Tela abaixo pelo usuário do Representante ...
    $sql = "SELECT c.`id_cliente`, c.`razaosocial`, c.`email`, pvi.`id_produto_acabado`, r.`nome_fantasia`, 
            IF(c.`id_pais` = '31', SUM(pvi.`qtde` * pvi.`preco_liq_final`), SUM(pvi.`qtde` * pvi.`preco_liq_final` * $valor_dolar_dia)) AS total, 
            ((IF(c.`id_pais` = '31', SUM(pvi.`qtde` * pvi.`preco_liq_final`), SUM(pvi.`qtde` * pvi.`preco_liq_final` * $valor_dolar_dia)) / SUM((pvi.`qtde` * pvi.`preco_liq_final`) / (1 + pvi.`margem_lucro` / 100)) - 1) * 100) AS mlmg 
            FROM `pedidos_vendas_itens` pvi 
            INNER JOIN `representantes` r ON r.`id_representante` = pvi.`id_representante` 
            INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`liberado` = '1' AND pv.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
            WHERE pvi.`id_representante` LIKE '$cmb_representante' 
            AND pvi.`id_produto_acabado` IN ($id_produtos_acabados) 
            GROUP BY c.`id_cliente` ORDER BY total DESC ";

    $sql_extra = "SELECT COUNT(pv.id_cliente) AS total_registro 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN `representantes` r ON r.`id_representante` = pvi.`id_representante` 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`liberado` = '1' AND pv.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
                INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
                WHERE pvi.`id_representante` LIKE '$cmb_representante' 
                AND pvi.`id_produto_acabado` IN ($id_produtos_acabados) 
                GROUP BY c.`id_cliente` ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal'>
        <td>
            <?=$campos_familia[0]['nome'];?>
        </td>
        <td align='left'>
        <?
            $sql = "SELECT ed.`razaosocial` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                    WHERE pa.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
            $campos_empresa_divisao = bancos::sql($sql);
            echo $campos_empresa_divisao[0]['razaosocial'];
        ?>
        </td>
        <td>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['email'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['nome_fantasia'];?>
        </td>
        <td align='right' bgcolor='#C8C8C8'>
        <?
            $tot_ano1 = $vendas_anual_array[$campos[$i]['id_cliente']][(date('Y') - 4)];
            echo number_format($tot_ano1, 2, ',', '.');
        ?>
        </td>
        <td align='right' bgcolor='#C8C8C8'>
        <?
            $tot_ano2 = $vendas_anual_array[$campos[$i]['id_cliente']][(date('Y') - 3)];
            echo number_format($tot_ano2, 2, ',', '.');
        ?>
        </td>
        <td align='right' bgcolor='#C8C8C8'>
        <?
            $tot_ano3 = $vendas_anual_array[$campos[$i]['id_cliente']][(date('Y') - 2)];
            echo number_format($tot_ano3, 2, ',', '.');
        ?>
        </td>
        <td align='right' bgcolor='#C8C8C8'>
        <?
            $tot_ano4 = $vendas_anual_array[$campos[$i]['id_cliente']][(date('Y') - 1)];
            echo number_format($tot_ano4, 2, ',', '.');
        ?>
        </td>
        <td align='right'>
            <font color='blue'>
                <?=number_format($campos[$i]['total'], 2, ',', '.');?>
            </font>
        </td>
        <td align='right'>
        <?
            $porc_parcial = ($campos[$i]['total'] / $total_pedidos_emitidos) * 100;
            echo number_format($porc_parcial, 2, ',', '.').' %';
            $porc_total+= $porc_parcial;
        ?>
        </td>
        <td align='right'>
        <?
            echo number_format($campos[$i]['mlmg'], 2, ',', '.').' %';
            $total_mlmg+= $campos[$i]['mlmg'];
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhanormal'>
        <td colspan='10' align='right'>
            <font color='red' size='2'>
                <b>Total Geral:</b> 
                <?=number_format($total_pedidos_emitidos, 2, ',', '.');?>
            </font>
        </td>
        <td align='right'>
            <font color='red' size='2'>
                <?=number_format($porc_total, 2, ',', '.');?> %
            </font>
        </td>
        <td align='right'>
            <font color='red' size='2'>
                <?=number_format($total_mlmg, 2, ',', '.');?> %
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='12'>
            Valor Dolar dia R$: <?=number_format($valor_dolar_dia, 4, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
<pre>
<b><font color='red'>Observação:</font></b>
<pre>
* A coluna Total R$ equivale ao Total de Compra do Cliente no Período de <b><?=data::datetodata($data_inicial, '/');?></b> à <b><?=data::datetodata($data_final, '/');?></b>.
</pre>
<iframe name='ifr_relatorio_semanal_visita' src='rel_grupos.php?id_familia=<?=$id_familia;?>&data_inicial=<?=$data_inicial;?>&data_final=<?=$data_final;?>&cmb_empresa_divisao=<?=$cmb_empresa_divisao;?>' width='100%' height='450' frameborder='0'></iframe>
</html>