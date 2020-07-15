<?
if(!class_exists(segurancas)) {
    require('../../../../lib/segurancas.php');
    require('../../../../lib/menu/menu.php');
    require('../../../../lib/calculos.php');
    require('../../../../lib/faturamentos.php');
    require('../../../../lib/genericas.php');
    require('../../../../lib/data.php');
}
segurancas::geral($PHP_SELF, '../../../../');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$valor_dolar_dia = genericas::moeda_dia('dolar');

//Atualiza na Tabela de Variáveis o Valor dessa Caixa de Dias_Úteis_Mês ...
if(!empty($txt_dias_uteis_mes)) {//Variável 24 ->  referente aos Dias úteis do Mês ...
    $sql = "UPDATE `variaveis` SET valor = '$txt_dias_uteis_mes' WHERE `id_variavel` = '24' LIMIT 1 ";
    bancos::sql($sql);
}

if(empty($txt_data_faturado_mes)) {//Se vazio, faz ajuste da Data ...
    $datas                      = genericas::retornar_data_relatorio();
    $txt_data_faturado_mes 	= $datas['data_inicial'];
    $txt_data_faturavel_mes     = $datas['data_final'];
}

$data_faturado_mes  = data::datatodate($txt_data_faturado_mes, '-');
$data_faturavel_mes = data::datatodate($txt_data_faturavel_mes, '-');

$data_emissao_vazia = "OR `data_emissao` = '0000-00-00' ";

//Busca tudo o que foi faturado dentro do Período - 'NOTAS FISCAIS' ...
$sql = "SELECT `id_nf` 
        FROM `nfs` 
        WHERE (`data_emissao` BETWEEN '$data_faturado_mes' AND '$data_faturavel_mes' $data_emissao_vazia) ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas == 0) {//Não encontrou nenhum Pedido Programado ...
    $vetor_nfs[] = 0;
}else {//Encontrou pelo menos 1 Pedido Programado ...
    for($i = 0; $i < $linhas; $i++) $vetor_nfs[] = $campos[$i]['id_nf'];
}

//Verifico tudo o que foi faturado por Empresa Divisão ...
$sql = "SELECT ged.`id_empresa_divisao`, SUM(nfsi.`qtde` * nfsi.`valor_unitario`) AS total 
        FROM `nfs_itens` nfsi 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = nfsi.`id_produto_acabado` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        WHERE nfsi.`id_nf` IN (".implode(',', $vetor_nfs).") 
        GROUP BY ged.`id_empresa_divisao` ORDER BY ged.`id_empresa_divisao` ";
$campos_faturado_nac_emp_div = bancos::sql($sql);
$linhas_faturado_nac_emp_div = count($campos_faturado_nac_emp_div);
for($i = 0; $i < $linhas_faturado_nac_emp_div; $i++) $vetor_faturado_nac_emp_div[$campos_faturado_nac_emp_div[$i]['id_empresa_divisao']] = $campos_faturado_nac_emp_div[$i]['total'];

/********************************************************************************************/
/*Observação: p/ essas Querys abaixo, é interessante estar fazendo 2 Querys separadas, com outra dentro 
do for porque se ajunto tudas em uma Query só o Sistema demora muito p/ responder por causa
das amarrações ... */
/********************************************************************************************/
//Busca os pedidos pendentes Faturáveis dos Clientes Nacionais e Internacionais - Exportações ...
$sql = "SELECT c.`id_pais`, pv.`id_empresa`, pvi.`id_produto_acabado`, pvi.`qtde`, pvi.`qtde_faturada`, pvi.`preco_liq_final`, pvi.`status` 
        FROM `pedidos_vendas` pv 
        INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND c.`credito` IN ('A', 'B') 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`status` < '2' 
        WHERE pv.`liberado` = '1' 
        AND pv.`condicao_faturamento` IN (1, 2) 
        AND pv.`faturar_em` <= '$data_faturavel_mes' ";
$campos_faturavel = bancos::sql($sql);
$linhas_faturavel = count($campos_faturavel);
for($i = 0; $i < $linhas_faturavel; $i++) {
    if($campos_faturavel[$i]['status'] == 0) {//Em Aberto ...
        $valor_total_em_aberto  = $campos_faturavel[$i]['qtde'] * $campos_faturavel[$i]['preco_liq_final'];
    }else if($campos_faturavel[$i]['status'] == 1) {//Parcial ...
        $valor_total_parcial    = ($campos_faturavel[$i]['qtde'] - $campos_faturavel[$i]['qtde_faturada']) * $campos_faturavel[$i]['preco_liq_final'];
    }
    $sql = "SELECT ged.`id_empresa_divisao` 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            WHERE pa.`id_produto_acabado` = '".$campos_faturavel[$i]['id_produto_acabado']."' LIMIT 1 ";
    $campos_item_pedido = bancos::sql($sql);
    if($campos_faturavel[$i]['id_pais'] == 31) {//Brasil ...
        $vetor_faturavel_nac_emp_div[$campos_item_pedido[0]['id_empresa_divisao']]+= $valor_total_em_aberto;
        $vetor_p_faturavel_nac_emp_div[$campos_item_pedido[0]['id_empresa_divisao']]+= $valor_total_parcial;
        
        $vetor_faturavel_nac_empresa[$campos_faturavel[$i]['id_empresa']]+= $valor_total_em_aberto;
        $vetor_p_faturavel_nac_empresa[$campos_faturavel[$i]['id_empresa']]+= $valor_total_em_aberto;
    }else {//Outro País ...
        //Se o País desse Cliente do Pedido do Loop, for Estrangeiro multiplico pelo Dólar p/ transformar em R$ ...
        $vetor_faturavel_exp_emp_div[$campos_item_pedido[0]['id_empresa_divisao']]+= ($valor_total_em_aberto * $valor_dolar_dia);
        $vetor_p_faturavel_exp_emp_div[$campos_item_pedido[0]['id_empresa_divisao']]+= ($valor_total_parcial * $valor_dolar_dia);
        
        $vetor_faturavel_exp_empresa[$campos_faturavel[$i]['id_empresa']]+= $valor_total_em_aberto;
        $vetor_p_faturavel_exp_empresa[$campos_faturavel[$i]['id_empresa']]+= ($valor_total_parcial * $valor_dolar_dia);
    }
}
/********************************************************************************************/
//Busca os pedidos pendentes não Faturáveis dos Clientes Nacionais e Internacionais - Exportação ...
$sql = "SELECT c.`id_pais`, pv.`id_empresa`, pvi.`id_produto_acabado`, pvi.`qtde`, pvi.`qtde_faturada`, pvi.`preco_liq_final`, pvi.`status` 
        FROM `pedidos_vendas` pv 
        INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`status` < '2' 
        WHERE pv.`liberado` = '1' 
        AND (pv.`condicao_faturamento` IN (3, 4) OR c.`credito` IN ('C', 'D') OR pv.`faturar_em` > '$data_faturavel_mes') ";
$campos_nao_faturar = bancos::sql($sql);
$linhas_nao_faturar = count($campos_nao_faturar);
for($i = 0; $i < $linhas_nao_faturar; $i++) {
    if($campos_nao_faturar[$i]['status'] == 0) {//Em Aberto ...
        $valor_total_em_aberto  = $campos_nao_faturar[$i]['qtde'] * $campos_nao_faturar[$i]['preco_liq_final'];
    }else if($campos_nao_faturar[$i]['status'] == 1) {//Parcial ...
        $valor_total_parcial    = ($campos_nao_faturar[$i]['qtde'] - $campos_nao_faturar[$i]['qtde_faturada']) * $campos_nao_faturar[$i]['preco_liq_final'];
    }
    $sql = "SELECT ged.`id_empresa_divisao` 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            WHERE pa.`id_produto_acabado` = '".$campos_nao_faturar[$i]['id_produto_acabado']."' LIMIT 1 ";
    $campos_item_pedido = bancos::sql($sql);
    if($campos_nao_faturar[$i]['id_pais'] == 31) {//Brasil ...
        $vetor_nao_faturar_nac_emp_div[$campos_item_pedido[0]['id_empresa_divisao']]+= $valor_total_em_aberto;
        $vetor_p_nao_faturar_nac_emp_div[$campos_item_pedido[0]['id_empresa_divisao']]+= $valor_total_parcial;
        
        $vetor_nao_faturar_nac_empresa[$campos_nao_faturar[$i]['id_empresa']]+= $valor_total_em_aberto;
        $vetor_p_nao_faturar_nac_empresa[$campos_nao_faturar[$i]['id_empresa']]+= $valor_total_parcial;
    }else {//Outro País
        $vetor_nao_faturar_exp_emp_div[$campos_item_pedido[0]['id_empresa_divisao']]+= ($valor_total_em_aberto * $valor_dolar_dia);
        $vetor_p_nao_faturar_exp_emp_div[$campos_item_pedido[0]['id_empresa_divisao']]+= ($valor_total_parcial * $valor_dolar_dia);
        
        $vetor_nao_faturar_exp_empresa[$campos_nao_faturar[$i]['id_empresa']]+= ($valor_total_em_aberto * $valor_dolar_dia);
        $vetor_p_nao_faturar_exp_empresa[$campos_nao_faturar[$i]['id_empresa']]+= ($valor_total_parcial * $valor_dolar_dia);
    }
}
/********************************************************************************************/
?>
<html>
<head>
<title>.:: Relat&oacute;rio de Pedidos Pendentes &agrave; Faturar Vs Divis&atilde;o ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
</head>
<body>
<form name='form' method='post' action=''>
<input type='hidden' name='passo' value='1'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='9'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <font color='red'>
                ***
            </font>
            Relat&oacute;rio de Pedidos Pendentes &agrave; Faturar Vs Divis&atilde;o do M&ecirc;s:
            <font color="yellow">
                <?=$txt_data_faturado_mes.' à '.$txt_data_faturavel_mes;?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            Empresa Divis&atilde;o
        </td>
        <td rowspan='2'>
            Faturado
            <font color='red'>**</font>
            R$ <br/>(Incluso NFs s/ Dt Emissão)
        </td>
        <td colspan='2'>
            Total em R$
        </td>
        <td colspan='2'>
            Total U$
        </td>
        <td colspan='2'>
            Total Geral R$
        </td>
        <td rowspan='2'>
            Previs&atilde;o do <br/>Faturamento R$
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Fatur&aacute;vel
        </td>
        <td>
            N&atilde;o Fatur&aacute;vel
        </td>
        <td>
            Fatur&aacute;vel
        </td>
        <td>
            N&atilde;o Fatur&aacute;vel
        </td>
        <td>
            Fatur&aacute;vel
        </td>
        <td>
            N&atilde;o Fatur&aacute;vel
        </td>
    </tr>
<?
//Essa query é muito utilizada + abaixo ...
$sql = "SELECT ed.`id_empresa`, ed.`id_empresa_divisao`, ed.`razaosocial`, e.`nomefantasia` 
        FROM `empresas_divisoes` ed 
        INNER JOIN `empresas` e ON e.`id_empresa` = ed.`id_empresa` 
        WHERE ed.`ativo` = '1' ORDER BY e.`razaosocial` ";
$campos_empresas_divisoes = bancos::sql($sql);
$linhas_empresas_divisoes = count($campos_empresas_divisoes);
for($i = 0; $i < $linhas_empresas_divisoes; $i++) {
?>
    <tr class='linhanormal' align='right'>
        <td align='left'>
            <!--$GLOBALS['nivel'] -> vem do rel faturamento-->
            <a href = '<?=$GLOBALS['nivel'];?>rel_familias.php?id_empresa_divisao=<?=$campos_empresas_divisoes[$i]['id_empresa_divisao'];?>' class='html5lightbox'>
                <?=$campos_empresas_divisoes[$i]['razaosocial'].' ('.$campos_empresas_divisoes[$i]['nomefantasia'].')';?>
            </a>
        </td>
        <td>
            <?=number_format($vetor_faturado_nac_emp_div[$campos_empresas_divisoes[$i]['id_empresa_divisao']], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($vetor_faturavel_nac_emp_div[$campos_empresas_divisoes[$i]['id_empresa_divisao']], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($vetor_nao_faturar_nac_emp_div[$campos_empresas_divisoes[$i]['id_empresa_divisao']], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($vetor_faturavel_exp_emp_div[$campos_empresas_divisoes[$i]['id_empresa_divisao']], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($vetor_nao_faturar_exp_emp_div[$campos_empresas_divisoes[$i]['id_empresa_divisao']], 2, ',', '.');?>
        </td>
        <td>
        <?
            $previsao_faturar_emp_div = $vetor_faturavel_nac_emp_div[$campos_empresas_divisoes[$i]['id_empresa_divisao']] + $vetor_faturavel_exp_emp_div[$campos_empresas_divisoes[$i]['id_empresa_divisao']];
            echo number_format($previsao_faturar_emp_div, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            $previsao_nao_faturar_emp_div = $vetor_nao_faturar_nac_emp_div[$campos_empresas_divisoes[$i]['id_empresa_divisao']] + $vetor_nao_faturar_exp_emp_div[$campos_empresas_divisoes[$i]['id_empresa_divisao']];
            echo number_format($previsao_nao_faturar_emp_div, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            $previsao_faturamento_emp_div = $vetor_faturado_nac_emp_div[$campos_empresas_divisoes[$i]['id_empresa_divisao']] + $previsao_faturar_emp_div;
            echo number_format($previsao_faturamento_emp_div, 2, ',', '.');
        ?>
        </td>
    </tr>
<?
    $total_faturado_nac+=           $vetor_faturado_nac_emp_div[$campos_empresas_divisoes[$i]['id_empresa_divisao']];
    $total_faturavel_nac+=          $vetor_faturavel_nac_emp_div[$campos_empresas_divisoes[$i]['id_empresa_divisao']];
    $total_nao_faturar_nac+=        $vetor_nao_faturar_nac_emp_div[$campos_empresas_divisoes[$i]['id_empresa_divisao']];
    $total_faturavel_exp+=          $vetor_faturavel_exp_emp_div[$campos_empresas_divisoes[$i]['id_empresa_divisao']];
    $total_nao_faturar_exp+=        $vetor_nao_faturar_exp_emp_div[$campos_empresas_divisoes[$i]['id_empresa_divisao']];
    $total_previsao_faturar+=       $previsao_faturar_emp_div;
    $total_previsao_nao_faturar+=   $previsao_nao_faturar_emp_div;
    $total_previsao_faturamento+=   $previsao_faturamento_emp_div;
    
    if($campos_empresas_divisoes[$i]['id_empresa'] == 1) {//Albafer ...
        $total_albafer_faturado+=                   $vetor_faturado_nac_emp_div[$campos_empresas_divisoes[$i]['id_empresa_divisao']];
        $total_albafer_previsao_faturar+=           $previsao_faturar_emp_div;
        $total_albafer_previsao_nao_faturar+=       $previsao_nao_faturar_emp_div;
    }else {//Tool Master ...
        $total_tool_master_faturado+=               $vetor_faturado_nac_emp_div[$campos_empresas_divisoes[$i]['id_empresa_divisao']];
        $total_tool_master_previsao_faturar+=       $previsao_faturar_emp_div;
        $total_tool_master_previsao_nao_faturar+=   $previsao_nao_faturar_emp_div;
    }
}
?>
    <tr class='linhadestaque' align='right'>
        <td>
            TOTAIS =>
        </td>
        <td>
            <?=number_format($total_faturado_nac, 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($total_faturavel_nac, 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($total_nao_faturar_nac, 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($total_faturavel_exp, 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($total_nao_faturar_exp, 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($total_previsao_faturar, 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($total_previsao_nao_faturar, 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($total_previsao_faturamento, 2, ',', '.');?>
        </td>
    </tr>
<?
    //Verifico tudo o que foi faturado por Empresa ...
    $sql = "SELECT nfs.`id_empresa`, SUM(nfsi.`qtde` * nfsi.`valor_unitario`) AS total 
            FROM `nfs_itens` nfsi 
            INNER JOIN `nfs` ON nfs.`id_nf` = nfsi.`id_nf` 
            WHERE nfsi.`id_nf` IN (".implode(',', $vetor_nfs).") 
            GROUP BY nfs.`id_empresa` ORDER BY nfs.`id_empresa` ";
    $campos_faturado_nac_empresa = bancos::sql($sql);
    $linhas_faturado_nac_empresa = count($campos_faturado_nac_empresa);
    for($i = 0; $i < $linhas_faturado_nac_empresa; $i++) $vetor_faturado_nac_empresa[$campos_faturado_nac_empresa[$i]['id_empresa']] = $campos_faturado_nac_empresa[$i]['total'];
?>
    <tr class='linhanormal' align='right'>
        <td>
            <font color='blue' size='2'>
                <b>Albafer R$ </b>
            </font>
        </td>
        <td>
            <?=number_format($vetor_faturado_nac_empresa[1], 2, ',', '.');?>
        </td>
        <td colspan='4'>
            &nbsp;
        </td>
        <td>
        <?
            $previsao_faturar_empresa_alba = $vetor_faturavel_nac_empresa[1] + $vetor_faturavel_exp_empresa[1];
            echo number_format($previsao_faturar_empresa_alba, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            $previsao_nao_faturar_empresa_alba = $vetor_nao_faturar_nac_empresa[1] + $vetor_nao_faturar_exp_empresa[1];
            echo number_format($previsao_nao_faturar_empresa_alba, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            $previsao_faturamento_empresa_alba = $vetor_faturado_nac_empresa[1] + $previsao_faturar_empresa_alba;
            echo number_format($previsao_faturamento_empresa_alba, 2, ',', '.');
        ?>
        </td>
    </tr>
    <tr class='linhanormal' align='right'>
        <td>
            <font color='blue' size='2'>
                <b>Tool Master R$ </b>
            </font>
        </td>
        <td>
            <?=number_format($vetor_faturado_nac_empresa[2], 2, ',', '.');?>
        </td>
        <td colspan='4'>
            &nbsp;
        </td>
        <td>
        <?
            $previsao_faturar_empresa_tool = $vetor_faturavel_nac_empresa[2] + $vetor_faturavel_exp_empresa[2];
            echo number_format($previsao_faturar_empresa_tool, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            $previsao_nao_faturar_empresa_tool = $vetor_nao_faturar_nac_empresa[2] + $vetor_nao_faturar_exp_empresa[2];
            echo number_format($previsao_nao_faturar_empresa_tool, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            $previsao_faturamento_empresa_tool = $vetor_faturado_nac_empresa[2] + $previsao_faturar_empresa_tool;
            echo number_format($previsao_faturamento_empresa_tool, 2, ',', '.');
        ?>
        </td>
    </tr>
    <tr class='linhanormal' align='right'>
        <td>
            <font color='blue' size='2'>
                <b>Grupo R$ </b>
            </font>
        </td>
        <td>
            <?=number_format($vetor_faturado_nac_empresa[4], 2, ',', '.');?>
        </td>
        <td colspan='4'>
            &nbsp;
        </td>
        <td>
        <?
            $previsao_faturar_empresa_grupo = $vetor_faturavel_nac_empresa[4] + $vetor_faturavel_exp_empresa[4];
            echo number_format($previsao_faturar_empresa_grupo, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            $previsao_nao_faturar_empresa_grupo = $vetor_nao_faturar_nac_empresa[4] + $vetor_nao_faturar_exp_empresa[4];
            echo number_format($previsao_nao_faturar_empresa_grupo, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            $previsao_faturamento_empresa_grupo = $vetor_faturado_nac_empresa[4] + $previsao_faturar_empresa_grupo;
            echo number_format($previsao_faturamento_empresa_grupo, 2, ',', '.');
        ?>
        </td>
    </tr>
    <tr class='linhadestaque' align='right'>
        <td>
            <font color='yellow' size='2'>
                <b>Total Geral R$ </b>
            </font>
        </td>
        <td>
        <?
            $faturamento_total_liberado = $total_albafer_faturado + $total_tool_master_faturado;
            echo number_format($faturamento_total_liberado, 2, ',', '.');
        ?>
        </td>
        <td colspan='4'>
            &nbsp;
        </td>
        <td>
        <?
            $calculo1 = $total_albafer_previsao_faturar + $total_tool_master_previsao_faturar;
            echo number_format($calculo1, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            $calculo2 = $total_albafer_previsao_nao_faturar + $total_tool_master_previsao_nao_faturar;
            echo number_format($calculo2, 2, ',', '.');
        ?>
        </td>
        <td>
            <?=number_format($faturamento_total_liberado + $calculo1, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal' align='right'>
        <td colspan='6'>
            <font color='green' size='2'>
                <b>Pedidos em Carteira R$ </b>
            </font>
        </td>
        <td colspan='2'>
            <font color='green' size='2'>
                <b><?=number_format($calculo1 + $calculo2, 2, ',', '.');?></b>
            </font>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
<?
unset($vetor_nfs);//Deleto essa variável p/ não herdar valores do Loop anterior lá em cima ...

/******************************************************************************/
/*********************************Faturamento**********************************/
/******************************************************************************/
/*Aqui eu busco tudo o que foi faturado nas respectivas Datas 

Nesse bolo de Notas, estão inclusas as Devoluções também ...*/
$sql = "SELECT `id_nf` 
        FROM `nfs` 
        WHERE `data_emissao` BETWEEN '$data_faturado_mes' AND '$data_faturavel_mes' ";
$campos_nfs = bancos::sql($sql);
$linhas_nfs = count($campos_nfs);
if($linhas_nfs == 0) {//Não encontrou nenhuma Nota Fiscal ...
    $vetor_nfs[] = 0;
}else {//Encontrou pelo menos 1 Nota Fiscal ...
    for($i = 0; $i < $linhas_nfs; $i++) $vetor_nfs[] = $campos_nfs[$i]['id_nf'];
}

/*******************************Sem Impostos*******************************/
$sql = "SELECT nfs.`id_nf`, nfs.`id_empresa`, 
        nfs.`status`, nfsi.`qtde`, nfsi.`qtde_devolvida`, nfsi.`valor_unitario` 
        FROM `nfs` 
        INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
        WHERE nfs.`id_nf` IN (".implode(',', $vetor_nfs).") 
        ORDER BY nfs.`id_nf` ";
$campos_nfs = bancos::sql($sql);
$linhas_nfs = count($campos_nfs);
for($i = 0; $i < $linhas_nfs; $i++) {
    if($campos_nfs[$i]['status'] == 6) {//Somente NF de Devolução ...
        $valor_devolvido = ($campos_nfs[$i]['qtde_devolvida'] * $campos_nfs[$i]['valor_unitario']) * (-1);
        
        if($campos_nfs[$i]['id_empresa'] == 1) {//Albafer ...
            $total_nfs_devolucao_s_imp_alba+= $valor_devolvido;
        }else if($campos_nfs[$i]['id_empresa'] == 2) {//Tool Master ...
            $total_nfs_devolucao_s_imp_tool+= $valor_devolvido;
        }else if($campos_nfs[$i]['id_empresa'] == 4) {//Grupo ...
            $total_nfs_devolucao_s_imp_grupo+= $valor_devolvido;
        }
    }
    /*A partir daqui estão inclusos todos os Tipos de NF, tudo o que foi faturado "Devoluções também" totalizando um Valor Bruto Faturado, 
    ao lado já em sequência será abatido o $valor_devolvido calculado acima em cima desse Valor Bruto Faturado ...*/
    $valor_faturado = ($campos_nfs[$i]['qtde'] * $campos_nfs[$i]['valor_unitario']);
    if($campos_nfs[$i]['id_empresa'] == 1) {//Albafer
        $total_nfs_faturado_s_imp_alba+= $valor_faturado;
    }else if($campos_nfs[$i]['id_empresa'] == 2) {//Tool Master
        $total_nfs_faturado_s_imp_tool+= $valor_faturado;
    }else if($campos_nfs[$i]['id_empresa'] == 4) {//Grupo
        $total_nfs_faturado_s_imp_grupo+= $valor_faturado;
    }
}

/*******************************Com Impostos*******************************/
//Faço busca de dados nos campos de duplicatas da Tabela nfs porque essas já possuem o Imposto facilitando muito ...
$sql = "SELECT `id_nf`, `id_empresa`, nfs.`status` 
        FROM `nfs` 
        WHERE `id_nf` IN (".implode(',', $vetor_nfs).") 
        ORDER BY `id_nf` ";
$campos_nfs = bancos::sql($sql);
$linhas_nfs = count($campos_nfs);
for($i = 0; $i < $linhas_nfs; $i++) {
    $calculo_total_impostos = calculos::calculo_impostos(0, $campos_nfs[$i]['id_nf'], 'NF');
    
    if($campos_nfs[$i]['status'] == 6) {//Somente NF de Devolução ...
        if($campos_nfs[$i]['id_empresa'] == 1) {//Albafer ...
            $total_nfs_devolucao_c_imp_alba+= ($calculo_total_impostos['valor_total_nota']) * (-1);
        }else if($campos_nfs[$i]['id_empresa'] == 2) {//Tool Master ...
            $total_nfs_devolucao_c_imp_tool+= ($calculo_total_impostos['valor_total_nota']) * (-1);
        }else if($campos_nfs[$i]['id_empresa'] == 4) {//Grupo ...
            $total_nfs_devolucao_c_imp_grupo+= ($calculo_total_impostos['valor_total_nota']) * (-1);
        }
    }
    
    /*A partir daqui estão inclusos todos os Tipos de NF, tudo o que foi faturado "Devoluções também" totalizando um Valor Bruto Faturado, 
    ao lado já em sequência será abatido o $valor_devolvido calculado acima em cima desse Valor Bruto Faturado ...*/
    if($campos_nfs[$i]['id_empresa'] == 1) {//Albafer
        $total_nfs_faturado_c_imp_alba+= $calculo_total_impostos['valor_total_nota'];
    }else if($campos_nfs[$i]['id_empresa'] == 2) {//Tool Master
        $total_nfs_faturado_c_imp_tool+= $calculo_total_impostos['valor_total_nota'];
    }else if($campos_nfs[$i]['id_empresa'] == 4) {//Grupo
        $total_nfs_faturado_c_imp_grupo+= $calculo_total_impostos['valor_total_nota'];
    }
}

/******************************************************************************/
/**********************************Comissões***********************************/
/******************************************************************************/
//Estorno de Comissões ...
$sql = "SELECT IF(ce.`tipo_lancamento` = '3', SUM(ce.`valor_duplicata`), (SUM(ce.`valor_duplicata`) * (-1))) AS total, nfs.`id_empresa` 
        FROM `comissoes_estornos` ce 
        INNER JOIN `nfs` ON nfs.`id_nf` = ce.`id_nf` 
        WHERE SUBSTRING(ce.`data_lancamento`, 1, 10) BETWEEN '$data_faturado_mes' AND '$data_faturavel_mes' 
        GROUP BY ce.`tipo_lancamento`, nfs.`id_empresa` ORDER BY nfs.`id_empresa` ";
$campos_atraso_reembolso    = bancos::sql($sql);
$linhas_atraso_reembolso    = count($campos_atraso_reembolso);
for($i = 0; $i < $linhas_atraso_reembolso; $i++) {//Atraso + reembolso
    if($campos_atraso_reembolso[$i]['id_empresa'] == 1) {//Albafer
        $atraso_reembolso_alba+= $campos_atraso_reembolso[$i]['total'];
    }else if($campos_atraso_reembolso[$i]['id_empresa'] == 2) {//Tool Master
        $atraso_reembolso_tool+= $campos_atraso_reembolso[$i]['total'];
    }else if($campos_atraso_reembolso[$i]['id_empresa'] == 4) {//Grupo
        $atraso_reembolso_grupo+= $campos_atraso_reembolso[$i]['total'];
    }
}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <font color='red'>
                ****
            </font>
            Faturado do Mês vs Empresa
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2' colspan='2'>
            Empresa
        </td>
        <td colspan='2'>
            Faturado R$ s/ Devoluções
        </td>
        <td colspan='2'>
            Devolu&ccedil;&atilde;o R$
        </td>
        <td rowspan='2'>
            Atrasos / Reembolsos R$
        </td>
        <td colspan='2'>
            Total R$
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            (s/ Impostos)
        </td>
        <td>
            (c/ Impostos)
        </td>
        <td>
            (s/ Impostos)
        </td>
        <td>
            (c/ Impostos)
        </td>
        <td>
            (s/ Impostos)
        </td>
        <td>
            (c/ Impostos)
        </td>
    </tr>
    <tr class='linhanormal' align='right'>
        <td colspan='2' align='left'>
            <font color='blue' size='2'>
                <b>Albafer</b>
            </font>
        </td>
        <td>
            <a href = '../faturamento/rel_fat_empresa.php?id_empresa_parametro=1&data_inicial=<?=$data_faturado_mes;?>&data_final=<?=$data_faturavel_mes;?>' class='html5lightbox'>
                <?=number_format($total_nfs_faturado_s_imp_alba, 2, ',', '.');?>
            </a>
        </td>
        <td>
            <b><?=number_format($total_nfs_faturado_c_imp_alba, 2, ',', '.');?></b>
        </td>
        <td>
            <?=number_format($total_nfs_devolucao_s_imp_alba, 2, ',', '.');?>
        </td>
        <td>
            <b><?=number_format($total_nfs_devolucao_c_imp_alba, 2, ',', '.');?></b>
        </td>
        <td>
            <a href = '../faturamento/rel_dev_empresa.php?id_empresa_parametro=1&data_inicial=<?=$data_faturado_mes;?>&data_final=<?=$data_faturavel_mes;?>' class='html5lightbox'>
                <?=number_format($atraso_reembolso_alba, 2, ',', '.');?>
            </a>
        </td>
        <td>
            <?=number_format($total_nfs_faturado_s_imp_alba + $total_nfs_devolucao_s_imp_alba + $atraso_reembolso_alba, 2, ',', '.');?>
        </td>
        <td>
            <b><?=number_format($total_nfs_faturado_c_imp_alba + $total_nfs_devolucao_c_imp_alba + $atraso_reembolso_alba, 2, ',', '.');?></b>
        </td>
    </tr>
    <tr align='right' class='linhanormal'>
        <td colspan='2' align='left'>
            <font color='blue' size='2'>
                <b>Tool Master</b>
            </font>
        </td>
        <td>
            <a href = '../faturamento/rel_fat_empresa.php?id_empresa_parametro=2&data_inicial=<?=$data_faturado_mes;?>&data_final=<?=$data_faturavel_mes;?>' class='html5lightbox'>
                <?=number_format($total_nfs_faturado_s_imp_tool, 2, ',', '.');?>
            </a>
        </td>
        <td>
            <b><?=number_format($total_nfs_faturado_c_imp_tool, 2, ',', '.');?></b>
        </td>
        <td>
            <?=number_format($total_nfs_devolucao_s_imp_tool, 2, ',', '.');?>
        </td>
        <td>
            <b><?=number_format($total_nfs_devolucao_c_imp_tool, 2, ',', '.');?></b>
        </td>
        <td>
            <a href = '../faturamento/rel_dev_empresa.php?id_empresa_parametro=2&data_inicial=<?=$data_faturado_mes;?>&data_final=<?=$data_faturavel_mes;?>' class='html5lightbox'>
                <?=number_format($atraso_reembolso_tool, 2, ',', '.');?>
            </a>
        </td>
        <td>
            <?=number_format($total_nfs_faturado_s_imp_tool + $total_nfs_devolucao_s_imp_tool + $atraso_reembolso_tool, 2, ',', '.');?>
        </td>
        <td>
            <b><?=number_format($total_nfs_faturado_c_imp_tool + $total_nfs_devolucao_c_imp_tool + $atraso_reembolso_tool, 2, ',', '.');?></b>
        </td>
    </tr>
    <tr align='right' class='linhanormal'>
        <td colspan='2' align='left'>
            <font color='blue' size='2'>
                <b>Grupo</b>
            </font>
        </td>
        <td>
            <a href = '../faturamento/rel_fat_empresa.php?id_empresa_parametro=4&data_inicial=<?=$data_faturado_mes;?>&data_final=<?=$data_faturavel_mes;?>' class='html5lightbox'>
                <?=number_format($total_nfs_faturado_s_imp_grupo, 2, ',', '.');?>
            </a>
        </td>
        <td>
            <b><?=number_format($total_nfs_faturado_c_imp_grupo, 2, ',', '.');?></b>
        </td>
        <td>
            <?=number_format($total_nfs_devolucao_s_imp_grupo, 2, ',', '.');?>
        </td>
        <td>
            <b><?=number_format($total_nfs_devolucao_c_imp_grupo, 2, ',', '.');?></b>
        </td>
        <td>
            <a href = '../faturamento/rel_dev_empresa.php?id_empresa_parametro=4&data_inicial=<?=$data_faturado_mes;?>&data_final=<?=$data_faturavel_mes;?>' class='html5lightbox'>
                <?=number_format($atraso_reembolso_grupo, 2, ',', '.');?>
            </a>
        </td>
        <td>
            <?=number_format($total_nfs_faturado_s_imp_grupo + $total_nfs_devolucao_s_imp_grupo + $atraso_reembolso_grupo, 2, ',', '.');?>
        </td>
        <td>
            <b><?=number_format($total_nfs_faturado_c_imp_grupo + $total_nfs_devolucao_c_imp_grupo + $atraso_reembolso_grupo, 2, ',', '.');?></b>
        </td>
    </tr>
    <tr align='right' class='linhanormal'>
        <td colspan='2' align='left'>
            <font color='red' size='2'>
                <b>Total Geral</b>
            </font>
        </td>
        <td>
            <font color='red' size='2'>
            <?
                $total_nfs_faturado_s_imp = number_format($total_nfs_faturado_s_imp_alba + $total_nfs_faturado_s_imp_tool + $total_nfs_faturado_s_imp_grupo, 2, ',', '.');
                echo $total_nfs_faturado_s_imp;
            ?>
            </font>
        </td>
        <td>
            <font color='red' size='2'>
            <?
                $total_nfs_faturado_c_imp = number_format($total_nfs_faturado_c_imp_alba + $total_nfs_faturado_c_imp_tool + $total_nfs_faturado_c_imp_grupo, 2, ',', '.');
                echo $total_nfs_faturado_c_imp;
            ?>
            </font>
        </td>
        <td>
            <font color='red' size='2'>
            <?
                $total_nfs_devolucoes_s_imp = number_format($total_nfs_devolucao_s_imp_alba + $total_nfs_devolucao_s_imp_tool + $total_nfs_devolucao_s_imp_grupo, 2, ',', '.');
                echo $total_nfs_devolucoes_s_imp;
            ?>
            </font>
        </td>
        <td>
            <font color='red' size='2'>
            <?
                $total_nfs_devolucoes_c_imp = number_format($total_nfs_devolucao_c_imp_alba + $total_nfs_devolucao_c_imp_tool + $total_nfs_devolucao_c_imp_grupo, 2, ',', '.');
                echo $total_nfs_devolucoes_c_imp;
            ?>
            </font>
        </td>
        <td>
            <font color='red' size='2'>
            <?
                $atrasos_reembolsos = number_format($atraso_reembolso_alba + $atraso_reembolso_tool + $atraso_reembolso_grupo, 2, ',', '.');
                echo $atrasos_reembolsos;
            ?>
            </font>
        </td>
        <td>
            <font color='red' size='2'>
                <?=number_format($total_nfs_faturado_s_imp_alba + $total_nfs_faturado_s_imp_tool + $total_nfs_faturado_s_imp_grupo + ($total_nfs_devolucao_s_imp_alba + $total_nfs_devolucao_s_imp_tool + $total_nfs_devolucao_s_imp_grupo) + ($atraso_reembolso_alba + $atraso_reembolso_tool + $atraso_reembolso_grupo), 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font color='red' size='2'>
                <?=number_format($total_nfs_faturado_c_imp_alba + $total_nfs_faturado_c_imp_tool + $total_nfs_faturado_c_imp_grupo + ($total_nfs_devolucao_c_imp_alba + $total_nfs_devolucao_c_imp_tool + $total_nfs_devolucao_c_imp_grupo) + ($atraso_reembolso_alba + $atraso_reembolso_tool + $atraso_reembolso_grupo), 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='9'>
            Previsão de Faturamento
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Dias úteis do Mês:
        </td>
        <td>
            <input type='text' name='txt_dias_uteis_mes' value='<?=number_format(genericas::variavel(24), 0);?>' title='Digite os Dias úteis do Mês' maxlength='12' size='13' onKeyUp="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''};previsao_faturamento()" class='caixadetexto'>
        </td>
        <td>
            Dias úteis até Hoje:
        </td>
        <td>
            <input type='text' name='txt_dias_uteis_ate_hoje' value='1,0' title='Digite os Dias úteis até Hoje' maxlength='5' size='13' onKeyUp="verifica(this, 'moeda_especial', '1', '', event);if(this.value == '0,0') {this.value = ''};previsao_faturamento()" class='caixadetexto'>
        </td>
        <td>
            Meta de Faturamento R$:
        </td>
        <td colspan="4">
            <input type='text' name='txt_meta_faturamento_rs' value='<?=number_format(genericas::variavel(25), 2, ',', '.');?>' title='Digite a Meta de Faturamento R$' maxlength='12' size='13' onkeyup="verifica(this, 'moeda_especial', '2', '', event);if(this.value == 0) {this.value = ''};previsao_faturamento()" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Faturamento Atual R$:
        </td>
        <td>
            <input type='text' name='txt_faturamento_atual_rs' value='<?=number_format($faturamento_total_liberado, 2, ',', '.');?>' title='Faturamento Atual R$' maxlength='12' size='13' class='textdisabled' disabled>
        </td>
        <td>
            Devoluções / Atrasos R$:
        </td>
        <td>
            <?$devolucoes_abatimentos_rs = ($total_nfs_devolucao_s_imp_alba + $total_nfs_devolucao_s_imp_tool + $total_nfs_devolucao_s_imp_grupo + $atraso_reembolso_alba + $atraso_reembolso_tool + $atraso_reembolso_grupo);?>
            <input type='text' name='txt_devolucoes_abatimentos_rs' value='<?=number_format($devolucoes_abatimentos_rs, 2, ',', '.');?>' title='Devoluções / Atrasos R$' maxlength='12' size='13' class='textdisabled' disabled>
        </td>
        <td>
            Previsão de Fat:
        </td>
        <td>
            <input type='text' name='txt_previsao_faturamento' maxlength='12' size='13' class='textdisabled' disabled>
        </td>
        <td colspan='2'>
            Faturamento necessário / dia:
        </td>
        <td>
            <input type='text' name='txt_faturamento_necessario_por_dia' title="Faturamento Necessário por Dia" maxlength='12' size='13' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='9'>
            Valor Dolar dia R$: <?=number_format($valor_dolar_dia, 4, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='submit' name='cmd_atualizar' value='Atualizar Relatório' title='Atualizar Relatório' class='botao'>
            <input type='button' name='cmd_imprimir' value='Imprimir Relatório' title='Imprimir Relatório' onclick='window:print()' class='botao'>
        </td>
    </tr>
</table>
<?
$vetor_empresas = array('', 'ALBAFER', 'TOOL MASTER', '', 'GRUPO');

//Busca os pedidos pendentes não Faturáveis dos Clientes Nacionais ...
$sql = "SELECT `id_pedido_venda_item` 
        FROM `pedidos_vendas_itens` 
        WHERE `status` < '2' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas == 0) {//Não encontrou nenhum Pedido Programado ...
    $vetor_pedido_venda_item[] = 0;
}else {//Encontrou pelo menos 1 Pedido Programado ...
    for($i = 0; $i < $linhas; $i++) $vetor_pedido_venda_item[] = $campos[$i]['id_pedido_venda_item'];
}

$sql = "SELECT pvi.`id_produto_acabado`, (pvi.`qtde` * pvi.`preco_liq_final`) AS total 
        FROM `pedidos_vendas` pv 
        INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND c.`id_pais` = '31' 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`id_pedido_venda_item` IN (".implode(',', $vetor_pedido_venda_item).") 
        WHERE pv.`liberado` = '1' 
        AND pv.`condicao_faturamento` = '3' ";
$campos_nao_faturar_nac = bancos::sql($sql);
$linhas_nao_faturar_nac = count($campos_nao_faturar_nac);
for($i = 0; $i < $linhas_nao_faturar_nac; $i++) {
    $sql = "SELECT ged.id_empresa_divisao 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
            WHERE pa.`id_produto_acabado` = '".$campos_nao_faturar_nac[$i]['id_produto_acabado']."' LIMIT 1 ";
    $campos_item_pedido = bancos::sql($sql);
    $vetor_sem_valor_nf_em_estoque[$campos_item_pedido[0]['id_empresa_divisao']]+= $campos_nao_faturar_nac[$i]['total'];
}
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            SEM VALOR P/ NF + EM ESTOQUE
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Empresa
        </td>
        <td>
            Empresa Divisão
        </td>
        <td>
            Valor Total
        </td>
    </tr>
<?
$total_sql  = 0;
//A query que equivale a essa variável foi feita mais acima na linha 200 +/- ...
for($i = 0; $i < $linhas_empresas_divisoes; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$vetor_empresas[$campos_empresas_divisoes[$i]['id_empresa']];?>
        </td>
        <td>
            <?=$campos_empresas_divisoes[$i]['razaosocial'];?>
        </td>
        <td>
            <?=number_format($vetor_sem_valor_nf_em_estoque[$campos_empresas_divisoes[$i]['id_empresa_divisao']], 2, ',', '.');?>
        </td>
    </tr>
<?
    $total_sql+=    $vetor_sem_valor_nf_em_estoque[$campos_empresas_divisoes[$i]['id_empresa_divisao']];
    $total_geral+=  $vetor_sem_valor_nf_em_estoque[$campos_empresas_divisoes[$i]['id_empresa_divisao']];
}
?>
    <tr class='linhacabecalho' align='right'>
        <td colspan='3'>
            Total R$ 
            <a href = '../pedidos_pendentes/detalhes_pedidos_pendentes.php?tipo_filtro=1' class='html5lightbox'>
                <font color='yellow' size='1'>
                    <?=number_format($total_sql, 2, ',', '.');?>
                </font>
            </a>
        </td>
    </tr>
</table>
<?
//Busca os pedidos pendentes não Faturáveis dos Clientes Nacionais ...
$sql = "SELECT pvi.id_produto_acabado, (pvi.qtde * pvi.preco_liq_final) AS total 
        FROM `pedidos_vendas` pv 
        INNER JOIN `clientes` c ON c.id_cliente = pv.id_cliente AND c.id_pais = '31' 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda AND pvi.`id_pedido_venda_item` IN (".implode(',', $vetor_pedido_venda_item).") 
        WHERE pv.liberado = '1' 
        AND pv.condicao_faturamento = '4' ";
$campos_nao_faturar_nac = bancos::sql($sql);
$linhas_nao_faturar_nac = count($campos_nao_faturar_nac);
for($i = 0; $i < $linhas_nao_faturar_nac; $i++) {
    $sql = "SELECT ged.id_empresa_divisao 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
            WHERE pa.`id_produto_acabado` = '".$campos_nao_faturar_nac[$i]['id_produto_acabado']."' LIMIT 1 ";
    $campos_item_pedido = bancos::sql($sql);
    $vetor_sem_valor_nf_em_producao_compra[$campos_item_pedido[0]['id_empresa_divisao']]+= $campos_nao_faturar_nac[$i]['total'];
}
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            SEM VALOR P/ NF + EM PRODUÇÃO / COMPRA
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Empresa
        </td>
        <td>
            Empresa Divisão
        </td>
        <td>
            Valor Total
        </td>
    </tr>
<?
$total_sql  = 0;
//A query que equivale a essa variável foi feita mais acima na linha 200 +/- ...
for($i = 0; $i < $linhas_empresas_divisoes; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$vetor_empresas[$campos_empresas_divisoes[$i]['id_empresa']];?>
        </td>
        <td>
            <?=$campos_empresas_divisoes[$i]['razaosocial'];?>
        </td>
        <td>
            <?=number_format($vetor_sem_valor_nf_em_producao_compra[$campos_empresas_divisoes[$i]['id_empresa_divisao']], 2, ',', '.');?>
        </td>
    </tr>
<?
    $total_sql+=    $vetor_sem_valor_nf_em_producao_compra[$campos_empresas_divisoes[$i]['id_empresa_divisao']];
    $total_geral+=  $vetor_sem_valor_nf_em_producao_compra[$campos_empresas_divisoes[$i]['id_empresa_divisao']];
}
?>
    <tr class='linhacabecalho' align='right'>
        <td colspan='3'>
            Total R$ 
            <a href = '../pedidos_pendentes/detalhes_pedidos_pendentes.php?tipo_filtro=2' class='html5lightbox'>
                <font color='yellow' size='1'>
                    <?=number_format($total_sql, 2, ',', '.');?>
                </font>
            </a>
        </td>
    </tr>
</table>
<?
//Busca os pedidos pendentes não Faturáveis dos Clientes Nacionais ...
$sql = "SELECT pvi.id_produto_acabado, (pvi.qtde * pvi.preco_liq_final) AS total 
        FROM `pedidos_vendas` pv 
        INNER JOIN `clientes` c ON c.id_cliente = pv.id_cliente AND c.id_pais = '31' AND c.credito = 'C' 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda AND pvi.`id_pedido_venda_item` IN (".implode(',', $vetor_pedido_venda_item).") 
        WHERE pv.liberado = '1' ";
$campos_nao_faturar_nac = bancos::sql($sql);
$linhas_nao_faturar_nac = count($campos_nao_faturar_nac);
for($i = 0; $i < $linhas_nao_faturar_nac; $i++) {
    $sql = "SELECT ged.id_empresa_divisao 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
            WHERE pa.`id_produto_acabado` = '".$campos_nao_faturar_nac[$i]['id_produto_acabado']."' LIMIT 1 ";
    $campos_item_pedido = bancos::sql($sql);
    $vetor_credito_c[$campos_item_pedido[0]['id_empresa_divisao']]+= $campos_nao_faturar_nac[$i]['total'];
}
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            CRÉDITO C
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Empresa
        </td>
        <td>
            Empresa Divisão
        </td>
        <td>
            Valor Total
        </td>
    </tr>
<?
$total_sql  = 0;
//A query que equivale a essa variável foi feita mais acima na linha 200 +/- ...
for($i = 0; $i < $linhas_empresas_divisoes; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$vetor_empresas[$campos_empresas_divisoes[$i]['id_empresa']];?>
        </td>
        <td>
            <?=$campos_empresas_divisoes[$i]['razaosocial'];?>
        </td>
        <td>
            <?=number_format($vetor_credito_c[$campos_empresas_divisoes[$i]['id_empresa_divisao']], 2, ',', '.');?>
        </td>
    </tr>
<?
    $total_sql+=    $vetor_credito_c[$campos_empresas_divisoes[$i]['id_empresa_divisao']];
    $total_geral+=  $vetor_credito_c[$campos_empresas_divisoes[$i]['id_empresa_divisao']];
}
?>
    <tr class='linhacabecalho' align='right'>
        <td colspan='3'>
            Total R$
            <a href = '../pedidos_pendentes/detalhes_pedidos_pendentes.php?tipo_filtro=3' class='html5lightbox'>
                <font color='yellow' size='1'>
                    <?=number_format($total_sql, 2, ',', '.');?>
                </font>
            </a>
        </td>
    </tr>
</table>
<?
//Busca os pedidos pendentes não Faturáveis dos Clientes Nacionais ...
$sql = "SELECT pvi.id_produto_acabado, (pvi.qtde * pvi.preco_liq_final) AS total 
        FROM `pedidos_vendas` pv 
        INNER JOIN `clientes` c ON c.id_cliente = pv.id_cliente AND c.id_pais = '31' AND c.credito = 'D' 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda AND pvi.`id_pedido_venda_item` IN (".implode(',', $vetor_pedido_venda_item).") 
        WHERE pv.liberado = '1' ";
$campos_nao_faturar_nac = bancos::sql($sql);
$linhas_nao_faturar_nac = count($campos_nao_faturar_nac);
for($i = 0; $i < $linhas_nao_faturar_nac; $i++) {
    $sql = "SELECT ged.id_empresa_divisao 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
            WHERE pa.`id_produto_acabado` = '".$campos_nao_faturar_nac[$i]['id_produto_acabado']."' LIMIT 1 ";
    $campos_item_pedido = bancos::sql($sql);
    $vetor_credito_d[$campos_item_pedido[0]['id_empresa_divisao']]+= $campos_nao_faturar_nac[$i]['total'];
}
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            CRÉDITO D
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Empresa
        </td>
        <td>
            Empresa Divisão
        </td>
        <td>
            Valor Total
        </td>
    </tr>
<?
$total_sql  = 0;
//A query que equivale a essa variável foi feita mais acima na linha 200 +/- ...
for($i = 0; $i < $linhas_empresas_divisoes; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$vetor_empresas[$campos_empresas_divisoes[$i]['id_empresa']];?>
        </td>
        <td>
            <?=$campos_empresas_divisoes[$i]['razaosocial'];?>
        </td>
        <td>
            <?=number_format($vetor_credito_d[$campos_empresas_divisoes[$i]['id_empresa_divisao']], 2, ',', '.');?>
        </td>
    </tr>
<?
    $total_sql+=    $vetor_credito_d[$campos_empresas_divisoes[$i]['id_empresa_divisao']];
    $total_geral+=  $vetor_credito_d[$campos_empresas_divisoes[$i]['id_empresa_divisao']];
}
?>
    <tr class='linhacabecalho' align='right'>
        <td colspan='3'>
            Total R$ 
            <a href = '../pedidos_pendentes/detalhes_pedidos_pendentes.php?tipo_filtro=4' class='html5lightbox'>
                <font color='yellow' size='1'>
                    <?=number_format($total_sql, 2, ',', '.');?>
                </font>
            </a>
        </td>
    </tr>
</table>
<?
//Busca os pedidos pendentes não Faturáveis dos Clientes Nacionais ...
$sql = "SELECT pvi.`id_produto_acabado`, (pvi.`qtde` * pvi.`preco_liq_final`) AS total 
        FROM `pedidos_vendas` pv 
        INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND c.`id_pais` = '31' 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`id_pedido_venda_item` IN (".implode(',', $vetor_pedido_venda_item).") 
        WHERE pv.`liberado` = '1' 
        AND pv.`faturar_em` > '$data_faturavel_mes' ";
$campos_nao_faturar_nac = bancos::sql($sql);
$linhas_nao_faturar_nac = count($campos_nao_faturar_nac);
for($i = 0; $i < $linhas_nao_faturar_nac; $i++) {
    $sql = "SELECT ged.`id_empresa_divisao` 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            WHERE pa.`id_produto_acabado` = '".$campos_nao_faturar_nac[$i]['id_produto_acabado']."' LIMIT 1 ";
    $campos_item_pedido = bancos::sql($sql);
    $vetor_programado_data_final_relatorio[$campos_item_pedido[0]['id_empresa_divisao']]+= $campos_nao_faturar_nac[$i]['total'];
}
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            PROGRAMADO > DATA FINAL DO RELATÓRIO
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Empresa
        </td>
        <td>
            Empresa Divisão
        </td>
        <td>
            Valor Total
        </td>
    </tr>
<?
$total_sql  = 0;
//A query que equivale a essa variável foi feita mais acima na linha 200 +/- ...
for($i = 0; $i < $linhas_empresas_divisoes; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$vetor_empresas[$campos_empresas_divisoes[$i]['id_empresa']];?>
        </td>
        <td>
            <?=$campos_empresas_divisoes[$i]['razaosocial'];?>
        </td>
        <td>
            <?=number_format($vetor_programado_data_final_relatorio[$campos_empresas_divisoes[$i]['id_empresa_divisao']], 2, ',', '.');?>
        </td>    
    </tr>
<?
    $total_sql+=    $vetor_programado_data_final_relatorio[$campos_empresas_divisoes[$i]['id_empresa_divisao']];
    $total_geral+=  $vetor_programado_data_final_relatorio[$campos_empresas_divisoes[$i]['id_empresa_divisao']];
}
?>
    <tr class='linhacabecalho' align='right'>
        <td colspan='3'>
            Total R$
            <a href = '../pedidos_pendentes/detalhes_pedidos_pendentes.php?tipo_filtro=5' class='html5lightbox'>
                <font color='yellow' size='1'>
                    <?=number_format($total_sql, 2, ',', '.');?>
                </font>
            </a>
        </td>
    </tr>
    <tr class='linhacabecalho' align='right'>
        <td colspan='3'>
            Total Geral R$ <?=number_format($total_geral, 2, ',', '.');?>
        </td>
    </tr>
</table>
<!--*************************************************************************************************-->
<!--Jogo essa função aki em baixo, por causa das variáveis faturamento_total_liberado, 
faturamento_total_adquirido e devolucoes que eu só vou carregando no interim da página-->
<Script Language = 'JavaScript'>
function previsao_faturamento() {
    var faturamento_total_liberado  = eval(strtofloat('<?=number_format($faturamento_total_liberado, 2, ',', '.');?>'))
    var devolucoes                  = eval(strtofloat('<?=$total_nfs_devolucoes_s_imp;?>'))
    var atrasos_reembolsos          = eval(strtofloat('<?=$atrasos_reembolsos;?>'))
//Dias Úteis do Mês
    var dias_uteis_mes              = (document.form.txt_dias_uteis_mes.value != '') ? eval(document.form.txt_dias_uteis_mes.value) : 0
//Dias Úteis até Hoje
    var dias_uteis_ate_hoje         = (document.form.txt_dias_uteis_ate_hoje.value != '') ? eval(strtofloat(document.form.txt_dias_uteis_ate_hoje.value)) : 1//Aqui é para não dar erro de divisão
    if(document.form.txt_dias_uteis_mes.value == '' && document.form.txt_dias_uteis_ate_hoje.value == '') {
        document.form.txt_previsao_faturamento.value = ''
    }else {	
        document.form.txt_previsao_faturamento.value = ((faturamento_total_liberado + (devolucoes + atrasos_reembolsos)) / dias_uteis_ate_hoje) * dias_uteis_mes
        document.form.txt_previsao_faturamento.value = arred(document.form.txt_previsao_faturamento.value, 2, 1)
    }
//Vendas necessária por dia ...
    //Meta de Faturamento
    var meta_faturamento_rs         = (document.form.txt_meta_faturamento_rs.value != '') ?	eval(strtofloat(document.form.txt_meta_faturamento_rs.value)) : 0
    var faturamento_atual_rs        = (document.form.txt_faturamento_atual_rs.value != '') ? eval(strtofloat(document.form.txt_faturamento_atual_rs.value)) : 0
    var devolucoes_abatimentos_rs   = (document.form.txt_devolucoes_abatimentos_rs.value != '') ? eval(strtofloat(document.form.txt_devolucoes_abatimentos_rs.value)) : 0
//Faturamento necessário por dia ...
    document.form.txt_faturamento_necessario_por_dia.value = (meta_faturamento_rs - (faturamento_atual_rs + devolucoes_abatimentos_rs)) / (dias_uteis_mes - dias_uteis_ate_hoje)
    document.form.txt_faturamento_necessario_por_dia.value = arred(document.form.txt_faturamento_necessario_por_dia.value, 2, 1)
}
//Eu chamo a função aqui em baixo, para que ele já executa essa função logo de cara, intenção de onload
previsao_faturamento()
</Script>
<!--*************************************************************************************************-->
</form>
<pre>
<font color='red'>  ** Neste Relatório não consta os seguintes cálculos: </font><font color='blue'>Frete/IPI, Despesas Acessórias, Desconto de PIS+Cofins e ICMS=7%
<font color='red'> *** Soma as notas com a data de emissão vazia, a menos que este seja puxado pelo relatório geral.</font>
<font color='red'>**** Não soma notas com a data de emissão vazia.</font>
</pre>
</body>
</html>