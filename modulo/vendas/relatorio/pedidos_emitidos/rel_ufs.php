<?
//Atualiza na Tabela de Variáveis o Valor dessa Caixa de Dias_Úteis_Mês
if(!empty($txt_dias_uteis_mes)) {//Variável 24 ->  referente aos Dias úteis do Mês
    $sql = "Update variaveis set valor = '$txt_dias_uteis_mes' where id_variavel = '24' limit 1 ";
    bancos::sql($sql);
}

//Atualiza na Tabela de Variáveis o Valor dessa Caixa de Dias_Úteis_Mês
if(!empty($txt_meta_faturamento_rs)) {//Variável 25 ->  referente aos Dias úteis do Mês
/*****************Aki faz um tratamento na Caixinha para gravar no Banco*****************/
    $txt_meta_faturamento_rs = str_replace('.', '', $txt_meta_faturamento_rs);
    $txt_meta_faturamento_rs = str_replace(',', '.', $txt_meta_faturamento_rs);
/****************************************************************************************/
    $sql = "UPDATE `variaveis` SET `valor` = '$txt_meta_faturamento_rs' WHERE `id_variavel` = '25' LIMIT 1 ";
    bancos::sql($sql);
}

if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
    $condicao_data = ($_POST['cmb_tipo_data'] == 'emissao') ? 'pv.`data_emissao`' : 'pv.`faturar_em`';
    if($_POST['chkt_livre_debito'] == 'S')  $condicao_livre_debito  = " AND pv.`livre_debito` = 'S' ";
    if($_POST['chkt_expresso'] == 'S') $condicao_expresso = " AND pv.`expresso` = 'S' ";
//Tenho q verificar aqui pois nao esta pegando o do valor em dolar
    $sql = "SELECT ufs.`id_uf`, ufs.`estado`, ufs.`cota_mensal`, SUM(pvi.`qtde` * pvi.`preco_liq_final`) AS total 
            FROM `pedidos_vendas` pv 
            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND c.`id_pais` = '31' 
            INNER JOIN `ufs` ON ufs.`id_uf` = c.`id_uf` 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
            WHERE $condicao_data BETWEEN '$data_inicial' AND '$data_final' 
            AND pv.`liberado` = '1' 
            $condicao_livre_debito $condicao_expresso GROUP BY c.`id_uf` ORDER BY ufs.`estado` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    $difereca_mes = data::diferenca_data($data_inicial, $data_final);//Retorna em dias ...
    $difereca_mes = (integer)$difereca_mes[0];
}
?>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhadestaque' align='center'>
        <td>
            Estado(s)
        </td>
        <td>
            Cota Mensal R$
        </td>
        <td>
            Total(is) R$
        </td>
        <td>
            Porcentagem(ns)
        </td>
    </tr>
<?
    $estados_listados = '';//Valor Default ...
    for ($i = 0; $i < $linhas; $i++) {
        $estados_listados.= 'id_uf <> '.$campos[$i]['id_uf'].' AND ';
?>
    <tr class='linhanormal'>
        <td>
            <a href = 'detalhes_uf_pais.php?data_inicial=<?=$data_inicial;?>&data_final=<?=$data_final;?>&id_uf=<?=$campos[$i]['id_uf'];?>' style='cursor:help' class='html5lightbox'>
                <?=$campos[$i]['estado'];?>
            </a>
        </td>
        <td align='right'>
        <?
            if($difereca_mes <= 31) {// ou seja se for a diferenca de um mes ele faz a conta pela cota mensal
                $total_cota_diaria = $campos[$i]['cota_mensal'];
            }else { //se nao eu calculo a cota diaria e multiplico pela diferença de dias entre a data solicitada
                $total_cota_diaria = ($campos[$i]['cota_mensal'] / 30) * ($difereca_mes + 1);
            }
            echo number_format($total_cota_diaria, 2, ',', '.');
        ?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['total'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['total'] / $total_cota_diaria * 100, 2, ',', '.').' %';?>
        </td>
    </tr>
<?
        $total_cota_mensal_vendido+=    $total_cota_diaria;
        $total_geral_vendido+=          $campos[$i]['total'];
    }
?>
    <tr class='linhacabecalho' align='right'>
        <td colspan='2'>
            <font color='yellow'>
                R$ <?=number_format($total_cota_mensal_vendido, 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font color='yellow'>
                R$ <?=number_format($total_geral_vendido, 2, ',', '.');?>
            </font>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
<?
    if(!empty($estados_listados)) {
        $estados_listados.= " `ativo` = '1' ";

        $sql = "SELECT id_uf, estado, cota_mensal 
                FROM `ufs` 
                WHERE ".$estados_listados." ORDER BY estado ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal'>
        <td>
            <a href = 'detalhes_uf_pais.php?data_inicial=<?=$data_inicial;?>&data_final=<?=$data_final;?>&id_uf=<?=$campos[$i]['id_uf'];?>' style='cursor:help' class='html5lightbox'>
                <font color='red'>
                    <?=$campos[$i]['estado'];?>
                </font>
            </a>
        </td>
        <td align='right'>
            <font color='red'>
                <?=number_format($campos[$i]['cota_mensal'], 2, ',', '.');?>
            </font>
        </td>
        <td align='right'>
            <font color='red'>
                0,00
            </font>
        </td>
        <td align='right'>
            <font color='red'>
                0,00 %
            </font>
        </td>
    </tr>
<?
            $total_cota_mensal_nao_vendido+=    $campos[$i]['cota_mensal'];
        }
?>
    <tr class='linhacabecalho' align='right'>
        <td colspan='2'>
            <font color='yellow'>
                R$ <?=number_format($total_cota_mensal_nao_vendido, 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font color='yellow'>
                R$ 0,00
            </font>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
<?
    }
/*************Exportação *************/
    if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
        $sql = "SELECT p.id_pais, p.pais, p.cota_mensal, SUM(pvi.qtde * pvi.preco_liq_final) AS total 
                FROM `pedidos_vendas` pv 
                INNER JOIN `clientes` c ON c.id_cliente = pv.id_cliente AND c.id_pais <> '31' 
                INNER JOIN `paises` p ON p.id_pais = c.id_pais 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda 
                WHERE $condicao_data BETWEEN '$data_inicial' AND '$data_final' 
                AND pv.`liberado` = '1' 
                $condicao_livre_debito $condicao_expresso GROUP BY c.id_pais ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
    }
?>
    <tr class='linhadestaque' align='center'>
        <td>
            País(es)
        </td>
        <td>
            Cota Mensal
        </td>
        <td>
            Total(is) U$&nbsp;/ &nbsp;R$ 
            <font color='darkblue'>
                <b>(U$ = R$ <?=number_format($valor_dolar_dia, 4, ',', '.');?>)</b>
            </font>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal'>
        <td>
            <a href = 'detalhes_uf_pais.php?data_inicial=<?=$data_inicial;?>&data_final=<?=$data_final;?>&id_pais=<?=$campos[$i]['id_pais'];?>' style='cursor:help' class='html5lightbox'>
                <?=$campos[$i]['pais'];?>
            </a>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['cota_mensal'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['total'], 2, ',', '.').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.number_format($campos[$i]['total'] * $valor_dolar_dia, 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['total'] / $campos[$i]['cota_mensal'] * 100, 2, ',', '.').' %';?>
        </td>
    </tr>
<?
        $total_geral_exp+= $campos[$i]['total'] * $valor_dolar_dia;
    }
?>
    <tr class='linhacabecalho' align='right'>
        <td colspan='3'>
            <font color='yellow'>
                R$ <?=number_format($total_geral_exp, 2, ',', '.');?>
            </font>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormalescura'>
        <td>
            <font color='red' size='2'>
                <b>Total Geral: </b>
            </font>
        </td>
        <td align='right'>
            <?
                $total_cota_geral = $total_cota_mensal_vendido + $total_cota_mensal_nao_vendido;
            ?>
            <font color='red' size='2'>
                <b>R$ <?=number_format($total_cota_geral, 2, ',', '.');?></b>
            </font>
        </td>
        <td align='right'>
        <?
            //Aqui eu igualo a soma dessas variáveis nessa variável $total_geral_vendas para facilitar nas functions + abaixo de JavaScript ...
            $total_geral_vendas = $total_geral_vendido + $total_geral_exp;
        ?>
            <font color='red' size='2'>
                <b>R$ <?=number_format($total_geral_vendas, 2, ',', '.');?></b>
            </font>
        </td>
        <td align='right'>
            <font color='red' size='2'>    
                <b><?=number_format($total_geral_vendas / $total_cota_geral * 100, 2, ',', '.').' %';?></b>
            </font>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='4'>
            Previsão de Vendas
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Dias úteis do Mês:
        </td>
        <td>
            <input type='text' name='txt_dias_uteis_mes' value='<?=number_format(genericas::variavel(24), 0);?>' title='Digite os Dias úteis do Mês' maxlength='12' size='13' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''};previsao_faturamento()" class='caixadetexto'>
        </td>
        <td>
            Meta de Faturamento R$:
        </td>
        <td>
            <input type='text' name='txt_meta_faturamento_rs' value='<?=number_format(genericas::variavel(25), 2, ',', '.');?>' title='Digite a Meta de Faturamento R$' maxlength='12' size='13' onkeyup="verifica(this, 'moeda_especial', '2', '', event);if(this.value == 0) {this.value = ''};previsao_faturamento()" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Dias úteis até Hoje:
        </td>
        <td>
            <input type='text' name='txt_dias_uteis_ate_hoje' value='<?=number_format($qtde_dias_uteis_ate_hoje, 1, ',', '.');?>' title='Digite os Dias úteis até Hoje' maxlength='4' size='13' onkeyup="if(this.value == 0) {this.value = ''};previsao_faturamento()" class='caixadetexto'>
        </td>
        <td>
            Falta p/ Meta R$:
        </td>
        <td>
            <input type='text' name='txt_falta_para_meta' title="Falta para Meta" maxlength='12' size='13' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Média Diária:
        </td>
        <td>
            <input type='text' name='txt_media_diaria' maxlength='12' size='13' class='textdisabled' disabled>
        </td>
        <td>
            Necessidade Diária p/ Meta R$:
        </td>
        <td>
            <input type='text' name='txt_neces_diaria_para_meta' title="Necessidade para Meta" maxlength='12' size='13' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Previsão de Vendas:
        </td>
        <td>
            <input type='text' name='txt_previsao_vendas' maxlength='12' size='13' class='textdisabled' disabled>
        </td>
        <td>
            Vendas necessária / dia:
        </td>
        <td>
            <input type='text' name='txt_vendas_necessaria_por_dia' title="Vendas Necessária por Dia" maxlength='12' size='13' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='4'>
            <input type="submit" name="cmd_atualizar" value="Atualizar Relatório" title='Atualizar Relatório' class="botao">
        </td>
    </tr>
</body>
</html>
<Script Language = 'JavaScript'>
/*Jogo essa função aki em baixo, por causa da variável que eu carreguei + acima $total_geral que servirá
para os cálculos dessa função*/
function previsao_faturamento() {
    var total_geral_vendas = eval(strtofloat('<?=number_format($total_geral_vendas, 2, ',', '.');?>'))
    var dias_uteis_ate_hoje = eval(strtofloat(document.form.txt_dias_uteis_ate_hoje.value))
//Dias Úteis do Mês
    var dias_uteis_mes = (document.form.txt_dias_uteis_mes.value != '') ? eval(document.form.txt_dias_uteis_mes.value) : 0
//Dias Úteis até Hoje
    var dias_uteis_ate_hoje = (dias_uteis_ate_hoje != '') ? eval(dias_uteis_ate_hoje) : 1//Aqui é para não dar erro de divisão
/******************************Tratamento do Campo Falta p/ Meta******************************/
//Meta de Faturamento
    var meta_faturamento_rs = (document.form.txt_meta_faturamento_rs.value != '') ?	eval(strtofloat(document.form.txt_meta_faturamento_rs.value)) : 0
//Falta para Meta
    document.form.txt_falta_para_meta.value = meta_faturamento_rs - total_geral_vendas
    document.form.txt_falta_para_meta.value = arred(document.form.txt_falta_para_meta.value, 2, 1)
    document.form.txt_falta_para_meta.value = number_format(document.form.txt_falta_para_meta.value)
/************************************************Fórmulas************************************************/
//Previsão de Vendas
    if(document.form.txt_dias_uteis_mes.value == '' && dias_uteis_ate_hoje == '') {
            document.form.txt_previsao_vendas.value = ''
            var previsao_vendas = 0
    }else {
            document.form.txt_previsao_vendas.value = total_geral_vendas * dias_uteis_mes / dias_uteis_ate_hoje
            document.form.txt_previsao_vendas.value = arred(document.form.txt_previsao_vendas.value, 2, 1)
            document.form.txt_previsao_vendas.value = number_format(document.form.txt_previsao_vendas.value)
            var previsao_vendas = eval(strtofloat(document.form.txt_previsao_vendas.value))
    }
//Vendas necessária por dia ...
    document.form.txt_vendas_necessaria_por_dia.value = (meta_faturamento_rs - total_geral_vendas) / (dias_uteis_mes - dias_uteis_ate_hoje)
    document.form.txt_vendas_necessaria_por_dia.value = arred(document.form.txt_vendas_necessaria_por_dia.value, 2, 1)
//Média Diária
    document.form.txt_media_diaria.value = total_geral_vendas / dias_uteis_ate_hoje
    document.form.txt_media_diaria.value = arred(document.form.txt_media_diaria.value, 2, 1)
    document.form.txt_media_diaria.value = number_format(document.form.txt_media_diaria.value)
/******************************Tratamento do Campo Falta p/ Meta******************************/
//Falta p/ Meta
    var falta_para_meta = (document.form.txt_falta_para_meta.value != '') ?	eval(strtofloat(document.form.txt_falta_para_meta.value)) : 0
    var substracao = (dias_uteis_mes - dias_uteis_ate_hoje)
    //Aqui eu forço a variável para 1, para não dar erro de divisão
    if(substracao == 0) substracao = 1
//Necessidade Diária p/ Meta R$
    document.form.txt_neces_diaria_para_meta.value = falta_para_meta / substracao
    document.form.txt_neces_diaria_para_meta.value = arred(document.form.txt_neces_diaria_para_meta.value, 2, 1)
    document.form.txt_neces_diaria_para_meta.value = number_format(document.form.txt_neces_diaria_para_meta.value)
}
//Eu chamo a função aqui em baixo, para que ele já executa essa função logo de cara, intenção de onload
previsao_faturamento()
</Script>