<?
//Executa o sql passado por parâmetro
$campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = 'consultar.php?valor=1'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Gerenciar Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='80%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='8'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Gerenciar Estoque - Consultar Cliente(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Cliente
        </td>
        <td>
            Tipo de Faturamento
        </td>
        <td>
            Crédito
        </td>
        <td>
            ALBA
        </td>
        <td>
            TOOL
        </td>
        <td>
            A + T
        </td>
        <td>
            GRUPO
        </td>
        <td>
            TOT. GERAL
        </td>
    </tr>
<?
    $dias_a_mais            = genericas::variavel(85);
    $data_atual_mais_dias   = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), $dias_a_mais), '-');

    for($i = 0; $i < $linhas; $i++) {
        //Limpo essas variáveis p/ não herdar valores do Loop anterior ...
        $alba               = 0;
        $tool               = 0;
        $grupo              = 0;
        $alba_faturar_em    = 0;
        $tool_faturar_em    = 0;
        $grupo_faturar_em   = 0;
        
        //Aqui eu busco o Total de Pedido por Empresa do id_cliente do Loop ...
        $sql = "SELECT IF(c.`id_pais` <> '31', SUM(pvi.`preco_liq_final` * (pvi.`qtde` - pvi.`qtde_faturada`) * pv.`valor_dolar`), 
                SUM(pvi.`preco_liq_final` * (pvi.`qtde` - pvi.`qtde_faturada`))) AS total_empresa, 
                pv.`id_empresa` 
                FROM `clientes` c 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_cliente` = c.`id_cliente` AND pv.`status` < '2' 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`status` < '2' 
                WHERE c.`id_cliente` = '".$campos[$i]['id_cliente']."' 
                GROUP BY pv.`id_cliente`, pv.`id_empresa` ";
        $campos_pedidos_venda = bancos::sql($sql);
        $linhas_pedidos_venda = count($campos_pedidos_venda);
        for($j = 0; $j < $linhas_pedidos_venda; $j++) {
            if($campos_pedidos_venda[$j]['id_empresa'] == 1) {
                $alba = $campos_pedidos_venda[$j]['total_empresa'];
            }else if($campos_pedidos_venda[$j]['id_empresa'] == 2) {
                $tool = $campos_pedidos_venda[$j]['total_empresa'];
            }else if($campos_pedidos_venda[$j]['id_empresa'] == 4) {
                $grupo = $campos_pedidos_venda[$j]['total_empresa'];
            }
        }
        
        //Aqui eu busco o Total de Pedido Programados por Empresa do id_cliente do Loop ...
        $sql = "SELECT IF(c.`id_pais` <> '31', SUM(pvi.`preco_liq_final` * (pvi.`qtde` - pvi.`qtde_faturada`) * pv.`valor_dolar`), 
                SUM(pvi.`preco_liq_final` * (pvi.`qtde` - pvi.`qtde_faturada`))) AS total_empresa, 
                pv.`id_empresa` 
                FROM `clientes` c 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_cliente` = c.`id_cliente` AND pv.`status` < '2' AND pv.`faturar_em` >= '$data_atual_mais_dias' 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`status` < '2' 
                WHERE c.`id_cliente` = '".$campos[$i]['id_cliente']."' 
                GROUP BY pv.`id_cliente`, pv.`id_empresa` ";
        $campos_pedidos_venda = bancos::sql($sql);
        $linhas_pedidos_venda = count($campos_pedidos_venda);
        for($j = 0; $j < $linhas_pedidos_venda; $j++) {
            if($campos_pedidos_venda[$j]['id_empresa'] == 1) {
                $alba_faturar_em = $campos_pedidos_venda[$j]['total_empresa'];
            }else if($campos_pedidos_venda[$j]['id_empresa'] == 2) {
                $tool_faturar_em = $campos_pedidos_venda[$j]['total_empresa'];
            }else if($campos_pedidos_venda[$j]['id_empresa'] == 4) {
                $grupo_faturar_em = $campos_pedidos_venda[$j]['total_empresa'];
            }
        }
        
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <a href='index.php?id_cliente=<?=$campos[$i]['id_cliente'];?>' class="link">
                <?=$campos[$i]['cliente'];?>
            </a>
        <?
            //Verifico se o Cliente possui pelo menos um Pedido não Liberado, com Itens e que ainda não esteja fechado ...
            $sql = "SELECT pv.`id_pedido_venda` 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                    WHERE pv.`id_cliente` = '".$campos[$i]['id_cliente']."' 
                    AND pv.`liberado` = '0' 
                    AND pv.`status` < '2' LIMIT 1 ";
            $campos_nao_liberado = bancos::sql($sql);
            if(count($campos_nao_liberado) == 1) echo " <font color='red' title='Não Liberado' style='cursor:help'><b>Ñ LIB</b></font>";
        ?>
        </td>
        <td align='center'>
        <?
            if($campos[$i]['tipo_faturamento'] == 1) {
                echo 'TUDO PELA ALBAFER';
            }else if($campos[$i]['tipo_faturamento'] == 2) {
                echo 'TUDO PELA TOOL MASTER';
            }else if($campos[$i]['tipo_faturamento'] == 'Q') {
                echo 'QUALQUER EMPRESA';
            }else if($campos[$i]['tipo_faturamento'] == 'S') {
                echo 'SEPARADAMENTE';
            }
        ?>
        </td>
        <td align='center'>
        <?	
            if($campos[$i]['credito'] == 'C') {
                echo "<font color='red'><b>";
            }else if($campos[$i]['credito'] == 'D') {
                echo "<font color='blue'><b>";
            }
            echo $campos[$i]['credito'];
            if($campos[$i]['credito'] == 'B') echo ' - R$ '.number_format($campos[$i]['limite_credito'] * 1.1, 0, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
            if($alba > 0) echo number_format($alba, 2, ',', '.');
            
            if($alba_faturar_em > 0) echo ' + <font color="red"><b>'.number_format($alba_faturar_em, 2, ',', '.').' prg</b></font>';
        ?>
        </td>
        <td align='right'>
        <?
            if($tool > 0) echo number_format($tool, 2, ',', '.');

            if($tool_faturar_em > 0) echo ' + <font color="red"><b>'.number_format($tool_faturar_em, 2, ',', '.').' prg</b></font>';
        ?>
        </td>
        <td align='right' bgcolor='#CECECE'>
        <?
            if(($alba + $tool) > 0) echo number_format($alba + $tool, 2, ',', '.');
            
            if($alba_faturar_em + $tool_faturar_em > 0) echo ' + <font color="red"><b>'.number_format($alba_faturar_em + $tool_faturar_em, 2, ',', '.').' prg</b></font>';
        ?>
        </td>
        <td align='right'>
        <?
            if($grupo > 0) echo number_format($grupo, 2, ',', '.');
            
            if($grupo_faturar_em > 0) echo ' + <font color="red"><b>'.number_format($grupo_faturar_em, 2, ',', '.').' prg</b></font>';
        ?>
        </td>
        <td align='right' bgcolor='#CECECE'>
        <?
            if(($alba + $tool + $grupo) > 0) echo number_format($alba + $tool + $grupo, 2, ',', '.');
            
            if($alba_faturar_em + $tool_faturar_em + $grupo_faturar_em > 0) echo ' + <font color="red"><b>'.number_format($alba_faturar_em + $tool_faturar_em + $grupo_faturar_em, 2, ',', '.').' prg</b></font>';
        ?>
        </td>
    </tr>
<?
            $i+= $somar;
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>