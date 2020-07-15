<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/data.php');
require('../../../../../lib/financeiros.php');
require('../../../../../lib/genericas.php');
session_start('funcionarios');

if($id_emp == 1) {//Albafer
	$endereco = '/erp/albafer/modulo/financeiro/pagamento/pago/albafer/index.php';
}else if($id_emp == 2) {//Tool Master
	$endereco = '/erp/albafer/modulo/financeiro/pagamento/pago/tool_master/index.php';
}else if($id_emp == 4) {//Grupo
	$endereco = '/erp/albafer/modulo/financeiro/pagamento/pago/grupo/index.php';
}else if($id_emp == 0) {//Todos
	$endereco = '/erp/albafer/modulo/financeiro/pagamento/pago/todas_empresas/index.php';
}
segurancas::geral($endereco, '../../../../../');

//Busca do último valor do dólar e do euro ...
$valor_dolar        = genericas::moeda_dia('dolar');
$valor_euro         = genericas::moeda_dia('euro');

if(!empty($cmb_uf))                 $condicao_uf            = " AND f.`id_uf` LIKE '$cmb_uf' ";
if(!empty($cmb_importacao))         $condicao_importacao    = " AND ca.`id_importacao` LIKE '$cmb_importacao' ";

if($chkt_pago_pelo_caixa_compras)   $inner_join_nfe = "INNER JOIN `nfe` ON nfe.`id_nfe` = ca.`id_nfe` AND nfe.`pago_pelo_caixa_compras` = 'S' ";

/************************************Tratamentos para não furar o SQL************************************/
if(!empty($chkt_somente_importacao)) {
    $condicao_somente_importacao    = " AND ca.`id_importacao` <> '0' ";
    $condicao_representante         = " AND r.`id_pais` NOT IN (0, 31) ";
}
/********************************************************************************************************/
$condicao_emp                       = ($id_emp == 0) ? '' : "AND ca.id_empresa = '$id_emp' ";

if(!empty($txt_data_emissao_inicial)) {
//Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
    if(substr($txt_data_emissao_final, 4, 1) != '-') {
        $txt_data_emissao_inicial   = data::datatodate($txt_data_emissao_inicial, '-');
        $txt_data_emissao_final     = data::datatodate($txt_data_emissao_final, '-');
    }
    $condicao_emissao = " AND ca.data_emissao BETWEEN '$txt_data_emissao_inicial' AND '$txt_data_emissao_final' ";
}

if(!empty($txt_data_vencimento_inicial)) {
    //Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
    if(substr($txt_data_vencimento_final, 4, 1) != '-') {
        $txt_data_vencimento_inicial    = data::datatodate($txt_data_vencimento_inicial, '-');
        $txt_data_vencimento_final      = data::datatodate($txt_data_vencimento_final, '-');
    }
    $condicao_vencimento = " AND ca.data_vencimento BETWEEN '$txt_data_vencimento_inicial' AND '$txt_data_vencimento_final' ";
}

if(!empty($txt_data_inicial)) {
    //Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente ...
    if(substr($txt_data_final, 4, 1) != '-') {
        $txt_data_inicial   = data::datatodate($txt_data_inicial, '-');
        $txt_data_final     = data::datatodate($txt_data_final, '-');
    }
    $condicao_pagamento = " AND caq.`data` BETWEEN '$txt_data_inicial' AND '$txt_data_final' ";
}

if(!empty($txt_valor))  $condicao_valor = " AND ca.`valor` = '$txt_valor' ";

if(!empty($cmb_conta_caixa)) {
    $inner_join = "INNER JOIN `grupos` g ON g.id_grupo = ca.id_grupo 
                   INNER JOIN `contas_caixas_pagares` ccp ON ccp.id_conta_caixa_pagar = g.id_conta_caixa_pagar AND ccp.id_conta_caixa_pagar LIKE '$cmb_conta_caixa' ";
}

$sql = "(SELECT ca.*, caq.data, f.razaosocial AS fornecedor, tp.`pagamento`, tp.`imagem`, CONCAT(tm.simbolo, '&nbsp;') AS simbolo 
        FROM `contas_apagares` ca 
        $inner_join_nfe 
        INNER JOIN `contas_apagares_quitacoes` caq ON caq.`id_conta_apagar` = ca.`id_conta_apagar` $condicao_pagamento 
        INNER JOIN `fornecedores` f ON f.id_fornecedor = ca.id_fornecedor AND f.razaosocial LIKE '%$txt_fornecedor%' AND f.bairro LIKE '%$txt_bairro%' AND f.cidade LIKE '%$txt_cidade%' $condicao_uf 
        INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = ca.`id_tipo_pagamento_recebimento` 
        INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = ca.id_tipo_moeda 
        $inner_join 
        WHERE ca.`numero_conta` LIKE '%$txt_numero_conta%' 
        AND ca.`ativo` = '1' 
        AND ca.`status` IN (1, 2) 
        AND ca.`semana` LIKE '%$txt_semana%' 
        $condicao_somente_importacao 
        $condicao_importacao 
        $condicao_valor 
        $condicao_emp $condicao_emissao $condicao_vencimento 
        GROUP BY ca.`id_conta_apagar`) 
        UNION ALL 
        (SELECT ca.*, caq.data, r.nome_fantasia AS fornecedor, tp.`pagamento`, tp.`imagem`, CONCAT(tm.simbolo, '&nbsp;') AS simbolo 
        FROM `contas_apagares` ca 
        $inner_join_nfe 
            
        /*
        Filtro deixou de ter esse campo no dia 16/07/2015 devido uma mudança de amarração no BD ...

        AND r.uf LIKE '$cmb_uf' */

        INNER JOIN `contas_apagares_quitacoes` caq ON caq.`id_conta_apagar` = ca.`id_conta_apagar` $condicao_pagamento 
        INNER JOIN `representantes` r ON r.`id_representante` = ca.`id_representante` AND r.`nome_fantasia` LIKE '%$txt_fornecedor%' AND r.`bairro` LIKE '%$txt_bairro%' AND r.`cidade` LIKE '%$txt_cidade%' $condicao_representante 
        INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = ca.`id_tipo_pagamento_recebimento` 
        INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = ca.id_tipo_moeda 
        $inner_join 
        WHERE ca.`numero_conta` LIKE '%$txt_numero_conta%' 
        AND ca.`ativo` = '1' 
        AND ca.`status` IN (1, 2) 
        AND ca.`semana` LIKE '%$txt_semana%' 
        $condicao_somente_importacao 
        $condicao_importacao 
        $condicao_valor 
        $condicao_emp $condicao_emissao $condicao_vencimento 
        GROUP BY ca.`id_conta_apagar`) ORDER BY `data` DESC ";
$campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
$linhas = count($campos);
/*******************************************************************************************/
if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = 'consultar.php?itens=1&valor=1&id_emp2=<?=$id_emp;?>'
    </Script>
<?
    exit;
}

if($linhas > 0) {
    $dia        = date('d');
    $mes        = date('m');
    $ano        = date('Y');
    $data_hoje  = $ano.$mes.$dia;
?>
<html>
<head>
<title>.:: Itens de Contas Pagas ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function detalhes() {
    var option  = 0
    var elemento = document.form
    for (var i = 0; i < elemento.length; i++) {
        if (elemento[i].checked == true && elemento[i].type == 'radio') option ++
    }
    if (option == 0) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        for (var i = 0; i < elemento.length; i++) {
            if (elemento[i].checked == true && elemento[i].type == 'radio') {
                var id_conta_apagar = elemento[i].value
                break;
            }
        }
        nova_janela('../../alterar.php?id_conta_apagar='+id_conta_apagar+'&pop_up=1', 'POP', '', '', '', '', 550, 950, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}
</Script>
</head>
<body>
<form name='form'>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='17'>
            Contas Paga(s) 
            <font color='yellow'>
            <?
                if($id_emp != 0) {//Diferente de Todas Empresas
                    echo genericas::nome_empresa($id_emp);
                }else {
                    echo 'TODAS EMPRESAS';
                }
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Item
        </td>
        <td>
            <font title='Semana' style='cursor:help'>
                Sm.
            </font>
        </td>
        <td>
            N.º / Conta
        </td>
        <td>
            Fornecedor / Descrição da Conta
        </td>
        <td>
            <font title='Empresa' style='cursor:help'>
                E
            </font>
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            Data de Venc. Inicial
        </td>
        <td>
            Data de Venc. Alterada
        </td>
        <td>
            Data de Pgto
        </td>
        <td>
            <font title='Tipo de Pagamento' style='cursor:help'>
                Tipo de Pgto.
            </font>
        </td>
        <td>
            Valor <br/>Nac / Est
        </td>
        <td>
            Valores Extras
        </td>
        <td>
            Valor Reaj
        </td>        
        <td>
            Valor Pago
        </td>
        <td>
            Saldo à Pagar
        </td>
        <td>
            Obs. Conta à Pagar
        </td>
        <td>
            Obs. Conta Paga
        </td>
    </tr>
<?
//Aqui eu busco todas observações Follow-UPs dos ids_conta_apagares encontrados acima ...
        for($i = 0; $i < $linhas; $i++) $vetor_contas_apagares[] = $campos[$i]['id_conta_apagar'];
        
        $sql = "SELECT `identificacao`, `observacao` 
                FROM `follow_ups` 
                WHERE `origem` = '18' 
                AND `identificacao` IN (".implode(',', $vetor_contas_apagares).") ";
        $campos_follow_ups = bancos::sql($sql);
        $linhas_follow_ups = count($campos_follow_ups);
        for($i = 0; $i < $linhas_follow_ups; $i++) $vetor_observacao_follow_up[$campos_follow_ups[$i]['identificacao']] = $campos_follow_ups[$i]['observacao'];

	$total_contas       = 0;
        $valor_total_pago   = 0;

	for ($i = 0; $i < $linhas; $i++) {
            //Essa variável iguala o Tipo de Moeda da Conta à Pagar ...
            $moeda                  = $campos[$i]['simbolo'];
            $calculos_conta_pagar   = financeiros::calculos_conta_pagar($campos[$i]['id_conta_apagar']);
            
            //Aqui faz esse cálculo só para ver o quanto resta à pagar da Conta ...
            if($campos[$i]['id_tipo_moeda'] == 1) {//Reais
                /**********************************Observações Cruciais**********************************
                $campos[$i]['valor_reajustado'] -> Campo que sempre guarda o valor em R$ c/ juros ...
                *****************************************************************************************/
                //Sempre o Valor Reajustado terá prioridade sobre o Valor de Origem da Conta ...
                $valor_pagar = ($calculos_conta_pagar['valor_reajustado'] != 0) ? $calculos_conta_pagar['valor_reajustado'] : $campos[$i]['valor'];
                $valor_pagar-= $campos[$i]['valor_pago'];
                $valor_pagar_real   = $valor_pagar;
            }else if($campos[0]['id_tipo_moeda'] == 2) {//Dólar
                //O campo $campos[$i]['valor_pago'], guarda o valor pago do Tp de moeda da Conta à Pagar ...
                $valor_pagar        = $campos[$i]['valor'] - $campos[$i]['valor_pago'];
                $valor_pagar_real   = $valor_pagar * $valor_dolar;
            }else if($campos[0]['id_tipo_moeda'] == 3) {//Euro
                //O campo $campos[$i]['valor_pago'], guarda o valor pago do Tp de moeda da Conta à Pagar ...
                $valor_pagar        = $campos[$i]['valor'] - $campos[$i]['valor_pago'];
                $valor_pagar_real   = $valor_pagar * $valor_euro;
            }
?>
    <tr class='linhanormal' onclick="options('form', 'opt_conta_apagar', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='radio' name='opt_conta_apagar' value="<?=$campos[$i]['id_conta_apagar'];?>" onclick="options('form', 'opt_conta_apagar', '<?=$i;?>', '#E8E8E8')">
        </td>
        <td>
            <?=$campos[$i]['semana'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['numero_conta'];?>
            <?
                if($campos[$i]['id_conta_apagar_automatica'] > 0) {
            ?>
            <img src = '../../../../../imagem/letra_a.png' width='17' height='20' title='Conta Gerada de Forma Automática' onclick="nova_janela('../../../cadastro/contas_automaticas/classes/cadastrar_conta/alterar.php?id_conta_apagar_automatica=<?=$campos[$i]['id_conta_apagar_automatica'];?>&id_empresa_menu=<?=$campos[$i]['id_empresa'];?>&pop_up=1', 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" style='cursor:help' border='0'>
            <?
                }
            ?>
        </td>
        <td align='left'>
            <?=$campos[$i]['fornecedor'];?>
        </td>
        <td>
        <?
            $empresa_conta = genericas::nome_empresa($campos[$i]['id_empresa']);
            if($empresa_conta == 'ALBAFER') {
                echo '<font title="ALBAFER" style="cursor:help"><b>A</b></font>';
            }else if($empresa_conta == 'TOOL MASTER') {
                echo '<font title="TOOL MASTER" style="cursor:help"><b>T</b></font>';
            }else if($empresa_conta == 'GRUPO') {
                echo '<font title="GRUPO" style="cursor:help"><b>G</b></font>';
            }
        ?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_vencimento'], '/');?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_vencimento_alterada'], '/');?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data'], '/');?>
        </td>
        <td>
            <?$url = '../../../../../imagem/financeiro/tipos_pag_rec/'.$campos[$i]['imagem'];?>
            <img src="<?=$url;?>" width="33" height="20" border="0" title="<?=$campos[$i]['pagamento'];?>">
        </td>
        <td align='right'>
        <?
            if($campos[$i]['valor'] == '0.00') {
                echo '&nbsp;';
            }else {
                echo $moeda.number_format($campos[$i]['valor'], 2, ',', '.');
            }
        ?>
        </td>
        <td align='right'>
            <?=$moeda.number_format($calculos_conta_pagar['valores_extra'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?='R$ '.number_format($calculos_conta_pagar['valor_reajustado'], 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
            if($campos[$i]['valor_pago'] == '0.00') {
                echo '&nbsp;';
            }else {
                echo $moeda.number_format($campos[$i]['valor_pago'], 2, ',', '.');
            }
            /*Aqui eu verifico se temos uma Conta que foi paga e que ficou sem Tipo de Pagamento ...
            Isso aconteceu com algumas contas por falha de Sistema */
            $sql = "SELECT id_conta_apagar_quitacao 
                    FROM `contas_apagares_quitacoes` 
                    WHERE `id_conta_apagar` = '".$campos[$i]['id_conta_apagar']."' 
                    AND `id_tipo_pagamento_recebimento` = '0' LIMIT 1 ";
            $campos_tipo_pagamento = bancos::sql($sql);
            if(count($campos_tipo_pagamento) == 1) {
        ?>
                <img src='../../../../../imagem/bloco_vermelho.gif' title='Conta Paga que está sem Tipo de Pagamento' width='8' height='8' border='0' style='cursor:help'>
        <?
            }
        ?>
        </td>
        <td align='right'>
        <?
            echo 'R$ '.number_format($valor_pagar_real, 2, ',', '.');
            //Esse só iremos exibir quando for moeda Estrangeira ...
            if($campos[$i]['id_tipo_moeda'] > 1) echo '<br/><font color="brown"><b> / '.$campos[$i]['simbolo'].' '.number_format($valor_pagar, 2, ',', '.').'</b></font>';
        ?>
        </td>
        <td align='left'>
            <?=$vetor_observacao_follow_up[$campos[$i]['id_conta_apagar']];?>
        </td>
        <td align='left'>
        <?
            /*Aqui eu trago todas as "Observações" referente a todos os Pagamentos que foram realizados 
            em cima dessa Conta à Pagar do Loop ...*/
            $sql = "SELECT observacao 
                    FROM `contas_apagares_quitacoes` 
                    WHERE `id_conta_apagar` = '".$campos[$i]['id_conta_apagar']."' ";
            $campos_quitacao = bancos::sql($sql);
            $linhas_quitacao = count($campos_quitacao);
            for($j = 0; $j < $linhas_quitacao; $j++) {
                if($j == 0) {//O primeiro Registro é o único que não tem Quebra de Linha ...
                    if(!empty($campos_quitacao[$j]['observacao'])) echo '* '.$campos_quitacao[$j]['observacao'];
                }else {//A partir dos Demais ...
                    if(!empty($campos_quitacao[$j]['observacao'])) echo '<br/>* '.$campos_quitacao[$j]['observacao'];
                }
            }
        ?>
        </td>
    </tr>
<?
            $valor_total_pago+= $campos[$i]['valor_pago'];
            $total_contas++;
	}
?>
    <tr>
        <td class='linhadestaque' colspan='4'>
            Total de Contas: 
            <font color='yellow'>
                <?=$total_contas;?>
            </font>
        </td>
        <td class='linhadestaque' colspan='5'>
        <?
            $semana = data::numero_semana(date('d'),date('m'),date('Y'));
            $ano = date('Y');
            $sql = "SELECT dia_inicio, dia_fim 
                    FROM `semanas` 
                    WHERE `semana` = '$semana' 
                    AND SUBSTRING(`dia_inicio`, 1, 4) = '$ano' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) != 0) {
                $dia_inicio = data::datetodata($campos[0]['dia_inicio'], '/');
                $dia_inicio = substr($dia_inicio, 0, 6).substr($dia_inicio, 8, 2);
                $dia_fim    = data::datetodata($campos[0]['dia_fim'], '/');
                $dia_fim    = substr($dia_fim, 0, 6).substr($dia_fim, 8, 2);
        ?>							
            Semana: 
            <font color='yellow'>
                <?=$semana;?>
            </font>
            - Período: 
            <font color='yellow'>
                <?=$dia_inicio.' à '.$dia_fim;?>
            </font>
        <?
            }else {
        ?>
            Semana: 
            <font color='yellow'>
                <?=$semana;?>
            </font>
        <?
            }
        ?>
        </td>
        <td class='linhadestaque' colspan='4'>
            &nbsp;
        </td>
        <td class='linhadestaque' align='right'>
            R$ <?=number_format($valor_total_pago, 2, ',', '.');?>
        </td>
        <td class='linhadestaque' colspan='3'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='17'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="location = 'consultar.php?itens=1&id_emp2=<?=$id_emp;?>'" class='botao'>
            <input type='button' name='cmd_detalhes' value='Detalhes' title='Detalhes' onclick="return detalhes()" class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_emp' value='<?=$id_emp;?>'>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>