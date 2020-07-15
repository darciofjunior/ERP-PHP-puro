<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/pedidos_emitidos/pedidos_emitidos.php', '../../../../');

$valor_dolar_dia=genericas::moeda_dia('dolar');

//Busca de todas as Empresas Divisões ...
$sql = "SELECT id_empresa_divisao, razaosocial 
        FROM `empresas_divisoes` 
        WHERE `ativo` = '1' ";
$campos_ed = bancos::sql($sql);
$linhas_ed = count($campos_ed);

//Busca de dados do Representante ...
$sql = "SELECT nome_fantasia, zona_atuacao 
        FROM `representantes` 
        WHERE `id_representante` = '$_GET[id_representante]' LIMIT 1 ";
$campos_rep = bancos::sql($sql);

//pego o total de pedido emitidos por representante
$sql = "SELECT c.id_pais, SUM(pvi.qtde * pvi.preco_liq_final) AS total 
        FROM `pedidos_vendas` pv 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda AND pvi.id_representante = '$_GET[id_representante]' 
        INNER JOIN `clientes` c ON c.id_cliente = pv.id_cliente 
        WHERE pv.`data_emissao` BETWEEN '$_GET[data_inicial]' AND '$_GET[data_final]' 
        AND pv.`liberado` = '1' GROUP BY pvi.id_representante, c.id_pais ";
$campos_total_ped_rep= bancos::sql($sql);
$linhas_total_ped_rep = count($campos_total_ped_rep);
for($i = 0; $i < $linhas_total_ped_rep; $i++) {
    if($campos_total_ped_rep[$i]['id_pais'] == 31) {
        $tot_nac+= $campos_total_ped_rep[$i]['total'];
    }else {
        $tot_exp+= ($campos_total_ped_rep[$i]['total'] * $valor_dolar_dia);
    }
}
$total_pedidos_emitidos_rep = $tot_nac + $tot_exp;
?>
<html>
<head>
<title>.:: Detalhes de Venda do Representante por Divisão ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<table border="0" width='95%' align='center' cellspacing ='1' cellpadding='1'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Detalhes de Venda do Representante por Divisão => 
            <font color='yellow'>
                (<?=$campos_rep[0]['nome_fantasia'];?>) <br>                
                <?=$campos_rep[0]['zona_atuacao'];?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>Divis&atilde;o</td>
        <td>Cotas R$ </td>
        <td>Vendas R$ </td>
        <td>Cotas % </td>
        <td>Total(is) % </td>
    </tr>
<?
    $difereca_mes = data::diferenca_data($_GET[data_inicial], $_GET[data_final]);//$difereca_mes[0];//dias
    $difereca_mes = (integer)$difereca_mes[0];
    
    for($i = 0; $i < $linhas_ed; $i++) {
        /*Aqui eu busco a última Cota que está em vigência para o determinado Representante passado 
        por parâmetro na sua respectiva Empresa Divisão ...*/
        $sql = "SELECT rc.cota_mensal 
                FROM `representantes` r 
                INNER JOIN `representantes_vs_cotas` rc ON rc.id_representante = r.id_representante AND rc.id_empresa_divisao = '".$campos_ed[$i]['id_empresa_divisao']."' AND rc.`data_final_vigencia` = '0000-00-00' 
                WHERE r.`id_representante` = '$_GET[id_representante]' ";
        $campos_cota = bancos::sql($sql);

        //Busco o Total de Vendas do Vendedor no período de Datas filtrados e no "id_empresa_divisao" do Loop ...
        $sql = "SELECT c.id_pais, SUM(pvi.qtde * pvi.preco_liq_final) AS total 
                FROM `pedidos_vendas` pv 
                INNER JOIN `clientes` c ON c.id_cliente = pv.id_cliente 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda 
                INNER JOIN `representantes` r ON r.id_representante = pvi.id_representante AND r.`id_representante` = '$_GET[id_representante]' 
                INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pvi.id_produto_acabado 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao AND ed.`id_empresa_divisao` = '".$campos_ed[$i]['id_empresa_divisao']."' 
                WHERE pv.`data_emissao` BETWEEN '$_GET[data_inicial]' AND '$_GET[data_final]' 
                AND pv.`liberado` = '1' GROUP BY ed.id_empresa_divisao, c.id_pais ";
        $campos_rep = bancos::sql($sql);
        $linhas_rep = count($campos_rep);
        $tot_nac = 0;// zero para nao acumular valores indevido logo abaixo
        $tot_exp = 0;// zero para nao acumular valores indevido logo abaixo
        for($j = 0; $j < count($campos_rep); $j++) {
            if($campos_rep[$j]['id_pais'] == 31) {
                $tot_nac+= $campos_rep[$j]['total'];
            }else {
                $tot_exp+= ($campos_rep[$j]['total'] * $valor_dolar_dia);
            }
        }
        $total_divisao = $tot_nac+$tot_exp;
        $total_geral_div+= $total_divisao;
?>

    <tr class='linhanormal'>
        <td>
            <a href="#" onclick="nova_janela('rel_venda_best_cliente_divisao.php?id_representante=<?=$_GET['id_representante'];?>&id_empresa_divisao=<?=$campos_ed[$i]['id_empresa_divisao'];?>&data_inicial=<?=$_GET['data_inicial'];?>&data_final=<?=$_GET['data_final'];?>', 'Relatorio', '', '', '', '', '600', '900', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <?=$campos_ed[$i]['razaosocial'];?>
            </a>
        </td>
        <td align='right'>
        <?
            if($difereca_mes <= 31) {// ou seja se for a diferenca de um mes ele faz a conta pela cota mensal
                $total_cota_diaria = $campos_cota[0]['cota_mensal'];
            }else { //se nao eu calculo a cota diaria e multiplico pela diferença de dias entre a data solicitada
                $total_cota_diaria = ($campos_cota[0]['cota_mensal'] / 30) * ($difereca_mes + 1);
            }
            echo number_format($total_cota_diaria, 2, ',', '.');
            $total_geral_cota_diaria += $total_cota_diaria;

            if($total_cota_diaria == 0 || empty($total_cota_diaria)) {$total_cota_diaria = 1;}
        ?>
        </td>
        <td align='right'>
            <?=number_format($total_divisao, 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
            $perc_cota = (($total_divisao / $total_cota_diaria) * 100);
            echo number_format($perc_cota, 2, ',', '.').'%';
            $tot_perc_cota+= $perc_cota;
        ?>
        </td>
        <td align='right'>
        <?
            if($total_pedidos_emitidos_rep == 0 || empty($total_pedidos_emitidos_rep)) $total_pedidos_emitidos_rep = 1;

            $total_perc = ($total_divisao / $total_pedidos_emitidos_rep) * 100;
            $total_geral_perc+= $total_perc;
            echo number_format($total_perc, 2, ',', '.').' %';
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhanormal' align='right'>
        <td align="left">
            <a href="#" onclick="nova_janela('rel_venda_best_cliente_divisao.php?id_representante=<?=$_GET['id_representante'];?>&id_empresa_divisao=0&data_inicial=<?=$_GET['data_inicial'];?>&data_final=<?=$_GET['data_final'];?>', 'Relatorio', '', '', '', '', '600', '900', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <font color="green" size="2">
                    Total
                </font>
            </a>
        </td>
        <td>
            <font color="green" size="2">
                <?=number_format($total_geral_cota_diaria, 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font color="green" size="2">
                <?=number_format($total_geral_div, 2, ',', '.');?>
            </font>
        </td>
        <td colspan='2'>
            <font color='green' size='2'>
                <?=number_format($total_geral_perc, 2, ',', '.');?> %
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Modo de Venda
        </td>
        <td>
            Qtde de Ped(s)
        </td>
        <td>
            Vendas R$
        </td>
        <td colspan='2'>
            %(s) Venda
        </td>
    </tr>
<?
/******************************Selects referentes ao Modo de Venda******************************/
    //Busco o Total de Vendas do Vendedor e período passado por parâmetro por parâmetro ...
    $sql = "SELECT c.id_pais, pv.modo_venda, SUM(pvi.qtde * pvi.preco_liq_final) AS total, COUNT(DISTINCT(pv.id_pedido_venda)) AS total_pedidos 
            FROM `pedidos_vendas` pv 
            INNER JOIN `clientes` c ON c.id_cliente = pv.id_cliente 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda 
            INNER JOIN `representantes` r ON r.id_representante = pvi.id_representante AND r.`id_representante` = '$_GET[id_representante]' 
            INNER JOIN `representantes_vs_cotas` rc ON rc.id_representante = r.id_representante 
            INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pvi.id_produto_acabado 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
            INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao AND ed.id_empresa_divisao = rc.id_empresa_divisao 
            WHERE pv.`data_emissao` BETWEEN '$_GET[data_inicial]' AND '$_GET[data_final]' 
            AND pv.`liberado` = '1' GROUP BY c.id_pais, pv.modo_venda ";
    $campos_rep = bancos::sql($sql);
    $linhas_rep = count($campos_rep);
    //Zero p/ não acumular valores indevido logo abaixo ...
    $modo_venda_indefinido  = 0;
    $modo_venda_fone        = 0;
    $modo_venda_vendedor    = 0;
    for($i = 0; $i < $linhas_rep; $i++) {
        if($campos_rep[$i]['id_pais']==31) {
            switch ($campos_rep[$i]['modo_venda']) {
                case 0:
                    $total_pedidos_indefinidos+= $campos_rep[$i]['total_pedidos'];
                    $modo_venda_indefinido+= $campos_rep[$i]['total'];
                break;
                case 1:
                    $total_pedidos_fone+= $campos_rep[$i]['total_pedidos'];
                    $modo_venda_fone+= $campos_rep[$i]['total'];
                break;
                case 2:
                    $total_pedidos_vendedor+= $campos_rep[$i]['total_pedidos'];
                    $modo_venda_vendedor+= $campos_rep[$i]['total'];
                break;
            }
        }else {
            switch ($campos_rep[$i]['modo_venda']) {
                case 0:
                    $total_pedidos_indefinidos+= $campos_rep[$i]['total_pedidos'];
                    $modo_venda_indefinido+= ($campos_rep[$i]['total']*$valor_dolar_dia);
                break;
                case 1:
                    $total_pedidos_fone+= $campos_rep[$i]['total_pedidos'];
                    $modo_venda_fone+= ($campos_rep[$i]['total']*$valor_dolar_dia);
                break;
                case 2:
                    $total_pedidos_vendedor+= $campos_rep[$i]['total_pedidos'];
                    $modo_venda_vendedor+= ($campos_rep[$i]['total']*$valor_dolar_dia);
                break;
            }
        }
    }
    $total_modo_venda_nac = $modo_venda_indefinido + $modo_venda_fone + $modo_venda_vendedor;
//Se existir essa situação, então mostra
    if(!empty($modo_venda_indefinido)) {//Existe esse if, devido a pedidos antigos, que não utilizavam esse método de modo de venda
?>
    <tr class='linhanormal' align='right'>
        <td align="left">
            INDEFINIDO
        </td>
        <td>
        <?
            if(empty($total_pedidos_indefinidos)) {
                echo 0;
            }else {
                echo $total_pedidos_indefinidos;
            }
        ?>
        </td>
        <td>
            <?=number_format($modo_venda_indefinido, 2, ',', '.');?>
        </td>
        <td colspan='2'>
        <?
            if($total_modo_venda_nac == 0) {
                echo number_format(0, 2, ',', '.').' %';
            }else {
                echo number_format(($modo_venda_indefinido / $total_modo_venda_nac) * 100, 2, ',', '.').' %';
            }
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhanormal' align='right'>
        <td align='left'>
            ATENDIMENTO INTERNO
        </td>
        <td>
        <?
            if(empty($total_pedidos_fone)) {
                echo 0;
            }else {
                echo $total_pedidos_fone;
            }
        ?>
        </td>
        <td>
            <?=number_format($modo_venda_fone, 2, ',', '.');?>
        </td>
        <td colspan='2'>
        <?
            if($total_modo_venda_nac == 0) {//Aki é para não dar erro de Divisão ...
                echo number_format(0, 2, ',', '.').' %';
            }else {
                echo number_format(($modo_venda_fone / $total_modo_venda_nac) * 100, 2, ',', '.').' %';
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal' align='right'>
        <td align='left'>
            VENDEDOR
        </td>
        <td>
        <?
            if(empty($total_pedidos_vendedor)) {
                echo 0;
            }else {
                echo $total_pedidos_vendedor;
            }
        ?>
        </td>
        <td>
            <?=number_format($modo_venda_vendedor, 2, ',', '.');?>
        </td>
        <td colspan='2'>
        <?
            if($total_modo_venda_nac == 0) {//Aki é para não dar erro de Divisão ...
                echo number_format(0, 2, ',', '.').' %';
            }else {
                echo number_format(($modo_venda_vendedor / $total_modo_venda_nac) * 100, 2, ',', '.').' %';
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal' align='right'>
        <td align='left'>
            <font color='green' size='2'>
                Total
            </font>
        </td>
        <td colspan='2'>
            <font color='green' size='2'>
                <?=number_format($total_geral_div, 2, ',', '.');?>
            </font>
        </td>
        <td colspan='2'>
            <font color='green' size='2'>
                <?=number_format($total_geral_perc, 2, ',', '.');?> %
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='5'>
            <font color='blue'><b>
                Valor Dólar do Dia R$: 
            </b></font>
            <?=number_format($valor_dolar_dia, 4, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            &nbsp;
        </td>
    </tr>
</table>
</body>
</html>