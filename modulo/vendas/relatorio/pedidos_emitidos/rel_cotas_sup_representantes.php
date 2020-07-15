<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/pedidos_emitidos/pedidos_emitidos.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

$valor_dolar_dia                = genericas::moeda_dia('dolar');
$id_representante_supervisor    = $id_representante;
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
<form name='form' method='post' action=''>
<input type='hidden' name='id_representante' value='<?=$id_representante_supervisor;?>'>
<input type='hidden' name='data_inicial' value='<?=$data_inicial;?>'>
<input type='hidden' name='data_final' value='<?=$data_final;?>'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='9'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Relat&oacute;rio de Supervisor Vs Cota do Representante<br/>
            <font color='yellow'>
            <?
                $sql = "SELECT nome_fantasia 
                        FROM `representantes` 
                        WHERE id_representante = '$id_representante_supervisor' LIMIT 1 ";
                $campos_supervisor = bancos::sql($sql);
                echo '('.$campos_supervisor[0]['nome_fantasia'].')';
            ?>
            </font>
        </td>
    </tr>
<?
        /*Nesse trazemos aí somente a última Cota que está em vigência para o determinado Representante passado 
        por parâmetro na sua determinada Empresa Divisão ...*/
        $sql = "SELECT SUM(rc.cota_mensal) AS total_cota_mensal, r.id_representante, r.nome_fantasia, r.ativo 
                FROM `representantes_vs_supervisores` rs 
                INNER JOIN `representantes` r ON r.`id_representante` = rs.`id_representante` AND r.`ativo` = '1' 
                INNER JOIN `representantes_vs_cotas` rc ON rc.`id_representante` = r.`id_representante` AND rc.`data_final_vigencia` = '0000-00-00' 
                WHERE rs.`id_representante_supervisor` = '$id_representante_supervisor' 
                GROUP BY rs.`id_representante` ORDER BY r.nome_fantasia ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        
        //Busca o total de pedido emitidos 
        $sql = "SELECT c.id_pais, SUM(pvi.qtde * pvi.preco_liq_final) AS total 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN `pedidos_vendas` pv ON pv.id_pedido_venda = pvi.id_pedido_venda 
                INNER JOIN `clientes` c ON c.id_cliente = pv.id_cliente 
                WHERE pv.data_emissao BETWEEN '$data_inicial' AND '$data_final' 
                AND pv.liberado = '1' 
                GROUP BY c.id_pais ";
	$campos_perc = bancos::sql($sql);
        $linhas_perc = count($campos_perc);
	for($i = 0; $i < $linhas_perc; $i++) {
            if($campos_perc[$i]['id_pais'] == 31) {
                $tot_nac+= $campos_perc[$i]['total'];
            }else {
                $tot_exp+= ($campos_perc[$i]['total'] * $valor_dolar_dia);
            }
	}
        $total_pedidos_emitidos = $tot_nac + $tot_exp;
?>
        <font color='white'>
            Valor total dos pedidos emitidos por representantes => <?=$total_pedidos_emitidos;?>
        </font>
	<tr class='linhadestaque' align='center'>
            <td colspan='2'>Representante(s)</td>
            <td>Cotas R$</td>
            <td>Vendas R$</td>
            <td>% Sobre a Cota(s)</td>
            <td>% Sobre o Total(is)</td>
            <td>Média Diária R$</td>
            <td>Valor Devido R$</td>      
            <td>Falta P/ Média R$</td>
	</tr>
<?
	$difereca_mes   = data::diferenca_data($data_inicial, $data_final);//$difereca_mes[0];//dias
	$difereca_mes   = (integer)$difereca_mes[0];
	for ($i = 0; $i < $linhas; $i++) {
?>

    <tr class='linhanormal'>
        <td colspan='2' align='left'>
            <a href="javascript:nova_janela('rel_cotas_representantes.php?id_representante=<?=$campos[$i]['id_representante'];?>&data_inicial=<?=$data_inicial;?>&data_final=<?=$data_final;?>', 'CONSULTAR', '', '', '', '', '350', '750', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
            <?
		if($campos[$i]['ativo'] == 1) {
                    echo $campos[$i]['nome_fantasia'];
		}else {
                    echo "<font color='red'>".$campos[$i]['nome_fantasia']." - (Inativo)</font>";
		}
	  ?>
            </a>
        </td>
        <td align='right'>
        <?
            if($difereca_mes <= 31) {// ou seja se for a diferenca de um mes ele faz a conta pela cota mensal
                $total_cota_diaria = $campos[$i]['total_cota_mensal'];
            }else { //se nao eu calculo a cota diaria e multiplico pela diferença de dias entre a data solicitada
                $total_cota_diaria = ($campos[$i]['total_cota_mensal'] / 30) * ($difereca_mes + 1);
            }
            echo number_format($total_cota_diaria, 2, ',', '.');
            $total_geral_cotas+= $total_cota_diaria;
            if($total_cota_diaria == 0 || $total_cota_diaria == 0.00) $total_cota_diaria = 1;
        ?>
        </td>
        <td align='right'>
        <?
            //Pego as vendas dos representantes ...
            $sql = "SELECT rep.id_representante, rep.nome_fantasia, rep.ativo, c.id_pais, SUM( pvi.qtde * pvi.preco_liq_final ) AS total 
                    FROM `pedidos_vendas_itens` pvi 
                    INNER JOIN `representantes` rep ON pvi.id_representante = rep.id_representante 
                    INNER JOIN `representantes_vs_supervisores` rs ON rs.id_representante = rep.id_representante 
                    AND rs.id_representante = '".$campos[$i]['id_representante']."' 
                    INNER JOIN `pedidos_vendas` pv ON pv.id_pedido_venda = pvi.id_pedido_venda 
                    INNER JOIN `clientes` c ON c.id_cliente = pv.id_cliente 
                    WHERE pv.data_emissao BETWEEN '$data_inicial' AND '$data_final' 
                    AND pv.liberado = '1'
                    GROUP BY rep.id_representante, c.id_pais ";
            $campos_vendas = bancos::sql($sql);
            $total_parcial = 0;
            for($x = 0; $x < count($campos_vendas); $x++) {
                if($campos_vendas[$x]['id_pais'] == 31) {
                    $total_parcial+= $campos_vendas[$x]['total'];
                }else {
                    $total_parcial+= ($campos_vendas[$x]['total'] * $valor_dolar_dia);
                }
            }
            echo number_format($total_parcial, 2, ',', '.');
            $total_geral+= $total_parcial;
        ?>
        </td>
        <td align='right'>
        <?
            if($total_cota_diaria == 1) {
                echo "<font color='blue'>Sem Cota</font>";
            }else {
                $perc_cota = (($total_parcial/$total_cota_diaria) * 100);
                echo number_format($perc_cota, 2, ',', '.').'%';
                $tot_perc_cota+=$perc_cota;
            }
        ?>
	</td>
        <td align='right'>
        <?
            if($total_pedidos_emitidos == 0) {//Aqui eu tenho esse tratamento p/ q não dê erro de Divisão por 0
                $total_perc = 0;
            }else {//Posso dividir por Zero normalmente ...
                $total_perc = ($total_parcial / $total_pedidos_emitidos) * 100;
            }
            $total_geral_perc+= $total_perc;
            echo number_format($total_perc, 2, ',', '.');
        ?> %
        </td>
        <td align='right'>
            <?$media_diaria = $total_cota_diaria / $_GET['dias_uteis_mes']; echo number_format($media_diaria, 2, ',', '.');?>
        </td>
        <td align='right'>
            <?$valor_devido = $media_diaria * $_GET['dias_uteis_ate_hoje']; echo number_format($valor_devido, 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
            $falta_media = $total_parcial - $valor_devido;
            if($falta_media < 0) {
                echo "<font color='red'>".number_format($falta_media, 2, ',', '.')."</font>";
            }else {
                echo '0,00';
            }
        ?>
        </td>
    </tr>
<?
            $total_geral_cotas_perc = ($total_geral / $total_geral_cotas) * 100;
        }
?>
    <tr class='linhanormal' align='right'>
        <td colspan='2'>
            <font color='green'>
                <b>Totais:</b>
            </font>
        </td>
        <td>
            <font color='green'>
                <?=number_format($total_geral_cotas, 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font color='green'>
                <?=number_format($total_geral, 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font color='green'>
                <?=number_format($total_geral_cotas_perc, 2, ',', '.');?> %
            </font>
        </td>
        <td>
            <font color='green'>
                <?=number_format($total_geral_perc, 2, ',', '.');?> %
            </font>
        </td>
        <td colspan='3'>&nbsp;</td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='9'>
            Valor Dolar dia R$: <?=number_format($valor_dolar_dia, 4, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='submit' name='cmd_atualizar' value='Atualizar Relatório' title='Atualizar Relatório' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>