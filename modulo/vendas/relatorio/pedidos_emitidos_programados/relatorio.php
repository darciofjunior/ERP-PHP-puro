<?
if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
    $vetor_rep_emitidos             = array();
    $vetor_rep_pendentes            = array();
    $vetor_rep_programados          = array();
    $vetor_rep_programados_antigos  = array();

    //Pego o Total de Pedidos Emitidos ...
    $sql = "SELECT c.`id_pais`, pvi.`id_representante`, SUM(pvi.`qtde` * pvi.`preco_liq_final`) AS total 
            FROM `pedidos_vendas` pv 
            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
            WHERE pv.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
            AND pv.`faturar_em` <= '$data_final' 
            AND pv.`liberado` = '1' GROUP BY pvi.`id_representante` ";
    $campos_perc = bancos::sql($sql);
    $linhas_perc = count($campos_perc);
    for($i = 0; $i < $linhas_perc; $i++) {
        if($campos_perc[$i]['id_pais'] == 31) {//Do Brasil ...
            $vetor_rep_emitidos[$campos_perc[$i]['id_representante']] = $campos_perc[$i]['total'];
        }else {//Estrangeiro ...
            $vetor_rep_emitidos[$campos_perc[$i]['id_representante']] = ($campos_perc[$i]['total'] * $valor_dolar_dia);
        }
    }

    $sql = "SELECT c.`id_pais`, pvi.`id_representante`, SUM(pvi.`qtde` * pvi.`preco_liq_final`) AS total 
            FROM `pedidos_vendas` pv 
            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`status` < '2' 
            WHERE pv.`data_emissao` < '$data_inicial' 
            AND pv.`liberado` = '1' GROUP BY pvi.id_representante ";
    $campos_perc = bancos::sql($sql);
    $linhas_perc = count($campos_perc);
    for($i = 0; $i < $linhas_perc; $i++) {
        if($campos_perc[$i]['id_pais'] == 31) {//Do Brasil ...
            $vetor_rep_pendentes[$campos_perc[$i]['id_representante']] = $campos_perc[$i]['total'];
        }else {//Estrangeiro ...
            $vetor_rep_pendentes[$campos_perc[$i]['id_representante']] = ($campos_perc[$i]['total'] * $valor_dolar_dia);
        }
    }

    //Pego o Total de Pedidos Programados ...
    $sql = "SELECT c.`id_pais`, pvi.`id_representante`, SUM(pvi.`qtde` * pvi.`preco_liq_final`) AS total 
            FROM `pedidos_vendas` pv 
            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`status` < '2' 
            WHERE pv.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
            AND pv.`faturar_em` > '$data_final' 
            AND pv.`liberado` = '1' GROUP BY pvi.`id_representante` ";
    $campos_perc = bancos::sql($sql);
    $linhas_perc = count($campos_perc);
    for($i = 0; $i < $linhas_perc; $i++) {
        if($campos_perc[$i]['id_pais'] == 31) {//Do Brasil ...
            $vetor_rep_programados[$campos_perc[$i]['id_representante']] = $campos_perc[$i]['total'];
        }else {//Estrangeiro ...
            $vetor_rep_programados[$campos_perc[$i]['id_representante']] = ($campos_perc[$i]['total'] * $valor_dolar_dia);
        }
    }

    //Pego o Total de Pedidos Programados ...
    $sql = "SELECT c.`id_pais`, pvi.`id_representante`, SUM(pvi.`qtde` * pvi.`preco_liq_final`) AS total 
            FROM `pedidos_vendas` pv 
            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`status` < '2' 
            WHERE pv.`data_emissao` < '$data_inicial' 
            AND pv.`faturar_em` > '$data_final' 
            AND pv.`liberado` = '1' GROUP BY pvi.`id_representante` ";
    $campos_perc = bancos::sql($sql);
    $linhas_perc = count($campos_perc);
    for($i = 0; $i < $linhas_perc; $i++) {
        if($campos_perc[$i]['id_pais'] == 31) {//Do Brasil ...
            $vetor_rep_programados_antigos[$campos_perc[$i]['id_representante']] = $campos_perc[$i]['total'];
        }else {//Estrangeiro ...
            $vetor_rep_programados_antigos[$campos_perc[$i]['id_representante']] = ($campos_perc[$i]['total'] * $valor_dolar_dia);
        }
    }

    //Busca todos os Representantes cadastrados no Sistema ...
    $sql = "SELECT `id_representante`, `nome_fantasia` 
            FROM `representantes` 
            WHERE `ativo` = '1' ORDER BY `nome_fantasia` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Representante(s)
        </td>
        <td>
            Cota Mensal R$
        </td>
        <td>
            Pedido(s) Liberado(s)<br/> no Período
        </td>
        <td>
            % Cota do Mês
        </td>
        <td>
            Pedido(s) Programado(s)<br>após <?=data::datetodata($data_final, '/');?>
        </td>
        <td>
            Total de Pedidos<br/>Emitidos no Período
            <font color='yellow'>
                <br/>A
            </font>
        </td>
        <td>
            % Cota Acumulada do Mês
        </td>
        <td>
            Pedido(s) Pendente(s)
            <font color='yellow'>
                <br/><br/>B
            </font>
        </td>
        <td>
            Pedido(s) Emitido(s) <br>anterior à <?=data::datetodata($data_inicial, '/');?> e <br>Programado(s) após <?=data::datetodata($data_final, '/');?>
            <font color='yellow'>
                <br/>C
            </font>
        </td>
        <td>
            Total
            <font color='yellow'>
                <br/>A + B + C
            </font>
        </td>
        <td>
            % Cota Acumulada Total
        </td>
    </tr>
<?
        for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='right'>
        <td align='left'>
            <?=$campos[$i]['nome_fantasia'];?>
        </td>
        <td>
        <?
            $cota_total_do_periodo = vendas::cota_total_do_representante($campos[$i]['id_representante'], $data_inicial, $data_final);
            if($difereca_mes <= 31) {// ou seja se for a diferenca de um mes ele faz a conta pela cota mensal
                $total_cota_diaria = $cota_total_do_periodo;
            }else { //se nao eu calculo a cota diaria e multiplico pela diferença de dias entre a data solicitada
                $total_cota_diaria = ($cota_total_do_periodo / 30) * ($difereca_mes + 1);
            }
            echo number_format($cota_total_do_periodo, 2, ',', '.');
            if($total_cota_diaria == 0) $total_cota_diaria = 1;
            $total_cotas+= $cota_total_do_periodo;
        ?>
        </td>
        <td>
        <?
            $valor_emitido = $vetor_rep_emitidos[$campos[$i]['id_representante']];
            if($valor_emitido == 0) {
                echo number_format(0, 2, ',', '.');
            }else {
                echo number_format($valor_emitido, 2, ',', '.');
            }
            $total_emitidos+= $valor_emitido;
        ?>
        </td>
        <td>
        <?
            if($total_cota_diaria == 1) {
                echo '<font color="blue">Sem Cota</font>';
            }else {
                $perc_cota = (($valor_emitido / $total_cota_diaria) * 100);
                echo number_format($perc_cota, 2, ',', '.').' %';
            }
        ?>
        </td>
        <td>
        <?
            $valor_programado = $vetor_rep_programados[$campos[$i]['id_representante']];
            if($valor_programado == 0) {
                echo number_format(0, 2, ',', '.');
            }else {
                echo number_format($valor_programado, 2, ',', '.');
            }
            $total_programados+= $valor_programado;
        ?>
        </td>
        <td>
        <?
            echo number_format($valor_emitido + $valor_programado, 2, ',', '.');
            $total_emitidos_periodo+= $valor_emitido + $valor_programado;
        ?>
        </td>
        <td>
        <?
            if($total_cota_diaria == 1) {
                echo '<font color="blue">Sem Cota</font>';
            }else {
                $perc_cota = ((($valor_emitido + $valor_programado) / $total_cota_diaria) * 100);
                echo number_format($perc_cota, 2, ',', '.').' %';
            }
        ?>
        </td>
        <td>
        <?
            $valor_pendente = $vetor_rep_pendentes[$campos[$i]['id_representante']];
            if($valor_pendente == 0) {
                echo number_format(0, 2, ',', '.');
            }else {
                echo number_format($valor_pendente, 2, ',', '.');
            }
            $total_pendentes+= $valor_pendente;
        ?>
        </td>
        <td>
        <?
            $valor_programado_antigo = $vetor_rep_programados_antigos[$campos[$i]['id_representante']];
            if($valor_programado_antigo == 0) {
                echo number_format(0, 2, ',', '.');
            }else {
                echo number_format($valor_programado_antigo, 2, ',', '.');
            }
            $total_programados_antigos+= $valor_programado_antigo;
        ?>
        </td>
        <td>
        <?
            echo number_format($valor_emitido + $valor_programado + $valor_pendente + $valor_programado_antigo, 2, ',', '.');
            $total_abc+= $valor_emitido + $valor_programado + $valor_pendente + $valor_programado_antigo;
        ?>
        </td>
        <td>
        <?
            if($total_cota_diaria == 1) {
                echo '<font color="blue">Sem Cota</font>';
            }else {
                $perc_cota = ((($valor_emitido + $valor_programado + $valor_pendente + $valor_programado_antigo) / $total_cota_diaria) * 100);
                echo number_format($perc_cota, 2, ',', '.').' %';
            }
        ?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhadestaque' align='right'>
        <td>
            <font color='yellow'>
                TOTAIS =>
            </font>
        </td>
        <td>
            R$ <?=number_format($total_cotas, 2, ',', '.');?>
        </td>
        <td>
            R$ <?=number_format($total_emitidos, 2, ',', '.');?>
        </td>
        <td align='right'>
            <font color='yellow'>
                <?=number_format(($total_emitidos / $total_cotas) * 100, 2, ',', '.');?> %
            </font>
        </td>
        <td>
            R$ <?=number_format($total_programados, 2, ',', '.');?>
        </td>
        <td>
            R$ <?=number_format($total_emitidos_periodo, 2, ',', '.');?>
        </td>
        <td align='right'>
            <font color='yellow'>
                <?=number_format(($total_emitidos_periodo / $total_cotas) * 100, 2, ',', '.');?> %
            </font>
        </td>
        <td>
            R$ <?=number_format($total_pendentes, 2, ',', '.');?>
        </td>
        <td>
            R$ <?=number_format($total_programados_antigos, 2, ',', '.');?>
        </td>
        <td>
            R$ <?=number_format($total_abc, 2, ',', '.');?>
        </td>
        <td>
            <font color='yellow'>
                <?=number_format(($total_abc / $total_cotas) * 100, 2, ',', '.');?> %
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            &nbsp;
        </td>
    </tr>
</body>
</html>
<?}?>