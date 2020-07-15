<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/data.php');
require('../../../../../lib/genericas.php');
session_start('funcionarios');

if($id_empresa_menu == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/cadastro/contas_automaticas/albafer/index.php';
}else if($id_empresa_menu == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/cadastro/contas_automaticas/tool_master/index.php';
}else if($id_empresa_menu == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/cadastro/contas_automaticas/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../');

//Busca do último valor do dólar e do euro
$sql = "SELECT valor_dolar_dia, valor_euro_dia 
        FROM `cambios` 
        ORDER BY id_cambio DESC LIMIT 1 ";
$campos         = bancos::sql($sql);
$valor_dolar 	= $campos[0]['valor_dolar_dia'];
$valor_euro 	= $campos[0]['valor_euro_dia'];

if(!empty($txt_data_vencimento_inicial)) {
//Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
    if(substr($txt_data_vencimento_final, 4, 1) != '-') {
        $txt_data_vencimento_inicial = data::datatodate($txt_data_vencimento_inicial, '-');
        $txt_data_vencimento_final = data::datatodate($txt_data_vencimento_final, '-');
    }
//Aqui é para não dar erro de SQL
    $condicao_data_vencimento = " AND caa.`data_vencimento` BETWEEN '$txt_data_vencimento_inicial' AND '$txt_data_vencimento_final' ";
}
//Se tiver preenchido o Fornecedor ou Número da Conta, ...
if(!empty($txt_fornecedor) || !empty($txt_numero_conta)) {
//Seleção da razão social do fornecedor na tabela relacional de conta à pagar automática
    $sql = "SELECT caa.id_conta_apagar_automatica 
            FROM `contas_apagares_automaticas` caa 
            INNER JOIN `produtos_financeiros_vs_fornecedor` pff ON pff.`id_produto_financeiro_vs_fornecedor` = caa.`id_produto_financeiro_vs_fornecedor` AND caa.`numero_conta` LIKE '%$txt_numero_conta%' 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = pff.`id_fornecedor` AND f.`razaosocial` LIKE '%$txt_fornecedor%' 
            WHERE caa.`id_empresa` = '$id_empresa_menu' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($l = 0; $l < $linhas; $l++) $id_contas[] = $campos[$l]['id_conta_apagar_automatica'];
}
//Arranjo Ténico
if(count($id_contas) > 0) {
    $vetor_contas = implode(',', $id_contas);
    if(count($id_contas) == 1) {
        $condicao_conta_automatica = " AND caa.`id_conta_apagar_automatica` = '$vetor_contas' ";
    }else {
        $condicao_conta_automatica = " AND caa.`id_conta_apagar_automatica` IN ($vetor_contas) ";
    }
}

//Aqui é para não retornar todos os Registros
//Se tiver preenchido o Fornecedor ou Número da Conta, ...
if(!empty($txt_fornecedor) || !empty($txt_numero_conta)) {
    if(count($id_contas) == 0) $condicao2 = " AND caa.`id_conta_apagar_automatica` = '0' ";
}

//Se essa opção está desmarcada, só mostro as "Contas Ativas" ...
if(empty($chkt_mostrar_contas_inativas)) $condicao_contas_inativas = " AND caa.`conta_ativa` = 'S' ";

$sql = "SELECT caa.*, f.razaosocial, tp.`pagamento`, tp.`imagem`, CONCAT(tm.`simbolo`, '&nbsp;') AS simbolo 
        FROM `contas_apagares_automaticas` caa 
        INNER JOIN `produtos_financeiros_vs_fornecedor` pff ON pff.`id_produto_financeiro_vs_fornecedor` = caa.`id_produto_financeiro_vs_fornecedor` 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = pff.`id_fornecedor` 
        INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = caa.`id_tipo_pagamento_recebimento` 
        INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = caa.`id_tipo_moeda` 
        WHERE caa.`id_empresa` = '$id_empresa_menu' 
        $condicao_data_vencimento 
        $condicao_conta_automatica 
        $condicao_contas_inativas 
        ORDER BY caa.data_vencimento ";
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
/*******************************************************************************************/
if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'consultar_contas.php?itens=1&valor=1&id_empresa_menu=<?=$id_empresa_menu;?>'
        </Script>
<?
        exit;
}

if($linhas > 0) {
    $dia = date('d');
    $mes = date('m');
    $ano = date('Y');

    $data_hoje = $ano.$mes.$dia;
?>
<html>
<head>
<title>.:: Itens de Contas à Pagar Automática ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
</head>
<body>
<form name='form'>
<table width='90%' cellspacing='0' cellpadding='0' border='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Contas à Pagar Automática 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_empresa_menu);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>Opções</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>N.º Nota /<br> N.º Conta</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>Fornecedor</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>Tipo de Data <br/>/ Gerar a cada X dias</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>Tipo de Automação <br/>/ Gerar Y dias antes do Vencimento</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>Data do Próximo Vencimento</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>Tipo de Pagamento</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>Valor</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>Valor Reajustado</b>
            </font>
        </td>
    </tr>
<?
    $cont_vencer = 0;
    $cont_vencidas = 0;
    for ($i = 0;  $i < $linhas; $i++) {
        //Essa variável iguala o tipo de moeda da conta à pagar automática
        $moeda = $campos[$i]['simbolo'];
/***************************************************************************/
        $data_vencimento = substr($campos[$i]['data_vencimento'], 0, 4).substr($campos[$i]['data_vencimento'], 5, 2).substr($campos[$i]['data_vencimento'], 8, 2);

        if($data_vencimento < $data_hoje) {
            $color = "color='#FF0000'";
            $cont_vencidas ++;
        }else {
            $color = ($campos[$i]['previsao'] == 1) ? "color='blue'" : '';
            $cont_vencer ++;
        }
?>
    <tr class='linhanormal' onmouseover="solbre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <img src = '../../../../../imagem/menu/alterar.png' border='0' title='Alterar Conta Automática' alt='Alterar Conta Automática' onclick="nova_janela('cadastrar_conta/alterar.php?id_empresa_menu=<?=$id_empresa_menu;?>&id_conta_apagar_automatica=<?=$campos[$i]['id_conta_apagar_automatica'];?>', 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')">
            <img src = '../../../../../imagem/propriedades.png' border='0' title='Detalhes de Conta Automática' alt='Detalhes de Conta Automática' onclick="nova_janela('detalhes.php?id_empresa_menu=<?=$id_empresa_menu;?>&id_conta_apagar_automatica=<?=$campos[$i]['id_conta_apagar_automatica'];?>', 'POP', '', '', '', '', 550, 950, 'c', 'c', '', '', 's', 's', '', '', '')" style='cursor:pointer' border='0'>
        </td>
        <td align='left'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
            <?
                echo $campos[$i]['numero_conta'];
                if($campos[$i]['qtde_parcelas'] > 0) echo ' <font color="darkgreen"><b>('.$campos[$i]['qtde_parcelas'].' parc)</b></font>';
                
                if($campos[$i]['previsao'] == 1) echo ' <font color="red" title="Previsão" style="cursor:help"><b>(Prev)</b></font>';
            ?>
            </font>
        </td>
        <td align='left'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                <?=$campos[$i]['razaosocial'];?>
            </font>
        </td>
        <td align='center'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
            <?
                if($campos[$i]['tipo_data'] == 0) {
                    echo 'Fixa - Todo dia '.substr(data::datetodata($campos[$i]['data_vencimento'], '/'), 0, 2);
                }else {
                    echo 'Intervalo / '.$campos[$i]['intervalo'];
                }
            ?>
            </font>
        </td>
        <td align='center'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
            <?
                if($campos[$i]['status'] == 0) {
                    echo 'POR DATA / '.$campos[$i]['dia_exibicao'];
                }else if($campos[$i]['status'] == 1) {
                    echo 'PAGO A CONTA ANTERIOR';
                }else {
                    echo 'AMBAS ACIMA / '.$campos[$i]['dia_exibicao'];
                }
            ?>
            </font>
        </td>					
        <td align='center'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
            <?
                if($campos[$i]['conta_ativa'] == 'S') {
                    echo data::datetodata($campos[$i]['data_vencimento'], '/');
                }else {
                    echo '<b>INATIVA</b>';
                }
            ?>
            </font>
        </td>
        <td align='center'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                <?$url = "../../../../../imagem/financeiro/tipos_pag_rec/".$campos[$i]['imagem'];?>
                <img src="<?=$url;?>" width='33' height='20' border='0' title='<?=$campos[$i]['pagamento'];?>'>
            </font>
        </td>
        <td align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
            <?
                if($campos[$i]['valor'] == '0.00') {
                    echo '&nbsp;';
                }else {
                    echo $moeda.str_replace('.', ',', $campos[$i]['valor']);
                }
                $valor_pagar_total = $valor_pagar_total + $campos[$i]['valor'];
            ?>
            </font>
        </td>
        <td align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
            <?
                if($campos[$i]['valor_reajustado'] == '0.00') {
                    echo '&nbsp;';
                }else {
                    echo 'R$ '.str_replace('.',',',$campos[$i]['valor_reajustado']);
                }
            ?>
            </font>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhanormal'>
        <td colspan='3'>
            <font size='0'>
                <b>Contas Vencidas: </b><?=$cont_vencidas;?>
                &nbsp;&nbsp;
                <b>Contas à Vencer: </b><?=$cont_vencer;?>
                &nbsp;&nbsp;
                <b>Total: </b><?=$cont_vencer + $cont_vencidas;?>
            </font>
        </td>
        <td colspan='3'>
        <?
            $semana = data::numero_semana(date('d'),date('m'),date('Y'));
            $ano = date('Y');
            $sql = "SELECT dia_inicio, dia_fim 
                    FROM `semanas` 
                    WHERE `semana` = '$semana' and substring(dia_inicio, 1, 4) = '$ano' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) != 0) {
                    $dia_inicio = data::datetodata($campos[0]['dia_inicio'], '/');
                    $dia_inicio = substr($dia_inicio, 0, 6).substr($dia_inicio, 8, 2);
                    $dia_fim = data::datetodata($campos[0]['dia_fim'], '/');
                    $dia_fim = substr($dia_fim, 0, 6).substr($dia_fim, 8, 2);
        ?>
            <b>Semana: </b><?echo $semana;?>
            <b> - Período: </b><?echo $dia_inicio.' a '.$dia_fim;?>
        <?
            }else {
        ?>
            <b>Semana: </b><?echo $semana;?>
        <?
            }
        ?>
        </td>
        <td colspan='3' align='right'>
            <font size='0' color='#FF0000'>
                <b>Total: </b><?='R$ '.number_format($valor_pagar_total, 2, ',', '.');?>
            </font>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
    <p/>
    <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar_contas.php?id_empresa_menu=<?=$id_empresa_menu;?>'" class='botao'>
</center>    
</form>
</body>
</html>
<?
}else {
?>
<html>
<body>
<form name='form'>
<table border="0" width='90%' align='center' cellspacing ='1' cellpadding='1'>
    <tr class="atencao">
        <td align='center'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-1' color="#FF0000">
                <b>NENHUMA CONTA AUTOMÁTICA FOI CADASTRADA.</b>
            </font>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>