<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/data.php');
require('../../../../../lib/financeiros.php');
require('../../../../../lib/genericas.php');
session_start('funcionarios');

if($id_emp == 1) {//Albafer
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/albafer/index.php';
}else if($id_emp == 2) {//Tool Master
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/tool_master/index.php';
}else if($id_emp == 4) {//Grupo
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/grupo/index.php';
}else if($id_emp == 0) {//Todos
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/todas_empresas/index.php';
}
segurancas::geral($endereco, '../../../../../');

$mensagem[1] = 'CONTA À PAGAR EXCLUIDA COM SUCESSO !';
$mensagem[2] = 'ESSA CONTA NÃO PODE SER EXCLUÍDA !';

/*****************************************************************************************/
/************Controle p/ marcar as Contas à Pagar c/ Urgentes ou Não Urgentes*************/
/*****************************************************************************************/
if(!empty($_GET['id_conta_apagar'])) {
    $sql = "UPDATE `contas_apagares` SET `urgente` = '$_GET[urgente]' WHERE `id_conta_apagar` = '$_GET[id_conta_apagar]' LIMIT 1 ";
    bancos::sql($sql);
}
/*****************************************************************************************/
/*****************************************************************************************/
/*****************************************************************************************/

//Busca do último valor do dólar e do euro ...
$valor_dolar        = genericas::moeda_dia('dolar');
$valor_euro         = genericas::moeda_dia('euro');

$data_retirada_60   = data::adicionar_data_hora(date('d/m/Y'), -60);
$data_retirada_60   = data::datatodate($data_retirada_60, '-');

if(!empty($cmb_uf))                     $condicao_uf                = " AND f.`id_uf` LIKE '$cmb_uf' ";
if(!empty($cmb_importacao))             $condicao_importacao        = " AND ca.`id_importacao` LIKE '$cmb_importacao' ";
if(!empty($cmb_tipo_pagamento))         $condicao_tipo_pagamento    = " AND ca.`id_tipo_pagamento_recebimento` LIKE '$cmb_tipo_pagamento' ";

if($chkt_mostrar == 1)                  $condicao_vencimento_60dias = " AND ca.`data_vencimento_alterada` >= '$data_retirada_60' ";
if($chkt_pago_pelo_caixa_compras)       $inner_join_nfe = "INNER JOIN `nfe` ON nfe.`id_nfe` = ca.`id_nfe` AND nfe.`pago_pelo_caixa_compras` = 'S' ";

/************************************Tratamentos para não furar o SQL************************************/
if(!empty($chkt_somente_importacao)) {
    $condicao_somente_importacao    = " AND ca.`id_importacao` <> '0' ";
    $condicao_representante         = " AND r.`id_pais` NOT IN (0, 31) ";
}
/********************************************************************************************************/
$condicao_emp                       = ($id_emp == 0) ? '' : "AND ca.`id_empresa` = '$id_emp' ";

if(!empty($txt_data_emissao_inicial)) {
//Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
    if(substr($txt_data_emissao_final, 4, 1) != '-') {
        $txt_data_emissao_inicial   = data::datatodate($txt_data_emissao_inicial, '-');
        $txt_data_emissao_final     = data::datatodate($txt_data_emissao_final, '-');
    }
    $condicao_emissao = " AND ca.`data_emissao` BETWEEN '$txt_data_emissao_inicial' AND '$txt_data_emissao_final' ";
}

if(!empty($txt_data_vencimento_alterada_inicial)) {
    //Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
    if(substr($txt_data_vencimento_alterada_final, 4, 1) != '-') {
        $txt_data_vencimento_alterada_inicial   = data::datatodate($txt_data_vencimento_alterada_inicial, '-');
        $txt_data_vencimento_alterada_final     = data::datatodate($txt_data_vencimento_alterada_final, '-');
    }
    $condicao_vencimento = " AND ca.`data_vencimento_alterada` BETWEEN '$txt_data_vencimento_alterada_inicial' AND '$txt_data_vencimento_alterada_final' ";
}
	
if(!empty($txt_data_inicial)) {//Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente ...
    if(substr($txt_data_final, 4, 1) != '-') {
        $txt_data_inicial   = data::datatodate($txt_data_inicial, '-');
        $txt_data_final     = data::datatodate($txt_data_final, '-');
    }
}

if(!empty($txt_valor))  $condicao_valor = " AND ca.`valor` = '$txt_valor' ";

if(!empty($cmb_conta_caixa)) {
    $inner_join = "INNER JOIN `grupos` g ON g.id_grupo = ca.id_grupo 
                   INNER JOIN `contas_caixas_pagares` ccp ON ccp.id_conta_caixa_pagar = g.id_conta_caixa_pagar AND ccp.id_conta_caixa_pagar LIKE '$cmb_conta_caixa' ";
}

$sql = "(SELECT ca.*, f.razaosocial AS fornecedor, tp.pagamento, tp.imagem, CONCAT(tm.simbolo, '&nbsp;') AS simbolo 
        FROM `contas_apagares` ca 
        $inner_join_nfe 
        INNER JOIN `fornecedores` f ON f.id_fornecedor = ca.id_fornecedor AND f.razaosocial LIKE '%$txt_fornecedor%' AND f.bairro LIKE '%$txt_bairro%' AND f.`cidade` LIKE '%$txt_cidade%' $condicao_uf 
        INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = ca.`id_tipo_pagamento_recebimento` 
        INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = ca.id_tipo_moeda 
        $inner_join 
        WHERE ca.numero_conta LIKE '%$txt_numero_conta%' 
        AND ca.`ativo` = '1' 
        AND (ca.`status` < 2 OR (ca.`status` = '2' AND ca.`predatado` = '1')) 
        AND ca.`semana` LIKE '%$txt_semana%' 
        $condicao_somente_importacao 
        $condicao_importacao 
        $condicao_tipo_pagamento 
        $condicao_valor 
        AND ca.`urgente` LIKE '$cmb_contas_vencidas' 
        $condicao_emp $condicao_vencimento_60dias $condicao_emissao $condicao_vencimento 
        GROUP BY ca.`id_conta_apagar`) 
        UNION ALL 
        (SELECT ca.*, r.nome_fantasia AS fornecedor, tp.`pagamento`, tp.`imagem`, CONCAT(tm.simbolo, '&nbsp;') AS simbolo 
        FROM `contas_apagares` ca 
        $inner_join_nfe 
            
        /*
        Filtro deixou de ter esse campo no dia 16/07/2015 devido uma mudança de amarração no BD ...

        AND r.uf LIKE '$cmb_uf' */

        INNER JOIN `representantes` r ON r.`id_representante` = ca.`id_representante` AND r.`nome_fantasia` LIKE '%$txt_fornecedor%' AND r.`bairro` LIKE '%$txt_bairro%' AND r.`cidade` LIKE '%$txt_cidade%' $condicao_representante 
        INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = ca.`id_tipo_pagamento_recebimento` 
        INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = ca.id_tipo_moeda 
        $inner_join 
        WHERE ca.numero_conta LIKE '%$txt_numero_conta%' 
        AND ca.`ativo` = '1' 
        AND (ca.`status` < 2 OR (ca.`status` = '2' AND ca.`predatado` = '1')) 
        AND ca.`semana` LIKE '%$txt_semana%' 
        $condicao_somente_importacao 
        $condicao_importacao 
        $condicao_tipo_pagamento 
        $condicao_valor 
        AND ca.`urgente` LIKE '$cmb_contas_vencidas' 
        $condicao_emp $condicao_vencimento_60dias $condicao_emissao $condicao_vencimento 
        GROUP BY ca.`id_conta_apagar`) ORDER BY `data_vencimento_alterada` ";
$campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
$linhas = count($campos);
/*******************************************************************************************/
if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.parent.location = 'consultar_contas.php?itens=1&valor=1&id_emp2=<?=$id_emp;?>'
    </Script>
<?
    exit;
}
/*Aqui eu envio o parâmetro "linhas" para o frame debaixo p/ não ter que refazer todo esse sql acima,
com o objetivo de saber se é para aparecer os demais botões no frame de baixo como alterar, excluir, ...*/
?>
    <Script Language = 'JavaScript'>
        window.parent.rodape.location = 'rodape.php?linhas=<?=$linhas;?>'
    </Script>

<?
if($linhas > 0) {
    $dia = date('d');
    $mes = date('m');
    $ano = date('Y');
    $data_hoje = $ano.$mes.$dia;
?>
<html>
<head>
<title>.:: Itens de Contas à Pagar ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
/*Função que serve para verificar quantas contas que estão em previsão ou que já
foram pagas, se nesta retornar zero, então eu posso estar pagando as contas
apagares, do contrário ele não permite que eu venha pagar nenhuma conta*/
function bloquear(posicao) {
    var elemento = document.form.elements
    posicao = eval(posicao) + 1
    if(elemento[posicao].checked == true) {
        document.form.bloquear_pagamento.value = eval(document.form.bloquear_pagamento.value) + 1
    }else {
        document.form.bloquear_pagamento.value = eval(document.form.bloquear_pagamento.value) - 1
    }
}

//Função que Desmarca todos os checkboxs ...
function controlar_checkbox() {
    var elementos   = document.form.elements
    var checar      = (document.form.chkt_tudo.checked == false) ? false : true       
    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'chkt_conta_apagar[]') elementos[i].checked = checar
    }
}

function alterar_urgente(id_conta_apagar, urgente_situacao_atual) {
    if(urgente_situacao_atual == 'S') {//A situação atual dessa Conta é Urgente, o Sistema pergunta se o usuário deseja colocar essa como "Não Urgente" ...
        var resposta = confirm('QUER MUDAR ESTA CONTA P/ "NÃO URGENTE" ?')
    }else {//A situação atual dessa Conta é Não Urgente, o Sistema pergunta se o usuário deseja colocar essa como "Urgente" ...
        var resposta = confirm('QUER MUDAR ESTA CONTA P/ "URGENTE" ?')
    }
    if(resposta == true) {//Se o Usuário desejou mudar a Situação da Conta ...
        //Nessa variável abaixo defino qual será a situação da Conta à Pagar no que tange ao "urgente" ...
        urgente = (urgente_situacao_atual == 'S') ? 'N' : 'S'
        window.location = 'itens.php<?=$parametro;?>&id_conta_apagar='+id_conta_apagar+'&urgente='+urgente
    }
}

function recalcular_contas_automaticas() {
    if(document.form.chkt_recalcular_contas_automaticas.checked) {
        nova_janela('recalcular_contas_automaticas.php', 'RECALCULAR_CONTAS_AUTOMATICAS', '', '', '', '', 20, 160, 'l', 'u', '', '', 's', 's', '', '', '')
    }
}
</Script>
</head>
<body>
<form name='form'>
<table width='95%' cellspacing='1' cellpadding='1' border='0' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='15'>
            Contas à Pagar 
            <font color='yellow'>
            <?
                if($id_emp != 0) {//Diferente de Todas Empresas
                    echo genericas::nome_empresa($id_emp);
                }else {
                    echo 'TODAS EMPRESAS';
                }
            ?>
            </font>
            &nbsp;-
            <input type='checkbox' name='chkt_recalcular_contas_automaticas' id='chkt_recalcular_contas_automaticas' title='Recalcular Contas Automáticas' onclick='recalcular_contas_automaticas()' class='checkbox'>
            <label for='chkt_recalcular_contas_automaticas'>
                Recalcular Contas Automáticas
            </label>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' title='Marcar / Desmarcar Tudo' onclick='controlar_checkbox()' class='checkbox'>
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
            Observação
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

	$cont_vencer    = 0;
	$cont_vencidas  = 0;
	for ($i = 0; $i < $linhas; $i++) {
            //Essa variável iguala o Tipo de Moeda da Conta à Pagar ...
            $moeda                      = $campos[$i]['simbolo'];
            $data_vencimento_alterada   = substr($campos[$i]['data_vencimento_alterada'], 0, 4).substr($campos[$i]['data_vencimento_alterada'], 5, 2).substr($campos[$i]['data_vencimento_alterada'], 8, 2);
            
            $calculos_conta_pagar = financeiros::calculos_conta_pagar($campos[$i]['id_conta_apagar']);
            //Aqui faz esse cálculo só para ver o quanto resta à pagar da Conta ...
            if($campos[$i]['id_tipo_moeda'] == 1) {//Reais
                /**********************************Observações Cruciais**********************************
                $campos[$i]['valor_reajustado'] -> Campo que sempre guarda o valor em R$ c/ juros ...
                *****************************************************************************************/
                //Sempre o Valor Reajustado terá prioridade sobre o Valor de Origem da Conta ...
                $valor_pagar = ($calculos_conta_pagar['valor_reajustado'] != 0) ? $calculos_conta_pagar['valor_reajustado'] : $campos[$i]['valor'];
                $valor_pagar-= $campos[$i]['valor_pago'];
                $valor_pagar_real   = $valor_pagar;
            }else if($campos[$i]['id_tipo_moeda'] == 2) {//Dólar
                //O campo $campos[$i]['valor_pago'], guarda o valor pago do Tp de moeda da Conta à Pagar ...
                $valor_pagar        = $campos[$i]['valor'] - $campos[$i]['valor_pago'];
                $valor_pagar_real   = $valor_pagar * $valor_dolar;
            }else if($campos[$i]['id_tipo_moeda'] == 3) {//Euro
                //O campo $campos[$i]['valor_pago'], guarda o valor pago do Tp de moeda da Conta à Pagar ...
                $valor_pagar        = $campos[$i]['valor'] - $campos[$i]['valor_pago'];
                $valor_pagar_real   = $valor_pagar * $valor_euro;
            }
//Aqui verifica se é previsão também para poder chamar a função que bloqueia o pagamento ...
            if($campos[$i]['previsao'] == 1) {
                $color = "color='blue'";
                $onclick = "checkbox('form', 'chkt_tudo', '".($i + 1)."', '#E8E8E8');bloquear('".($i + 1)."');document.form.chkt_tudo.checked = false";
            }else {
                if($data_vencimento_alterada < $data_hoje) {
                    $color = "color='#FF0000'";
                    $onclick = "checkbox('form', 'chkt_tudo','".($i + 1)."', '#E8E8E8');document.form.chkt_tudo.checked = false";
                    $cont_vencidas ++;
                }else {
                    $color = '';
                    $onclick = "checkbox('form', 'chkt_tudo','".($i + 1)."', '#E8E8E8');document.form.chkt_tudo.checked = false";
                    $cont_vencer ++;
                }
                if($valor_pagar_real < 0) $color = "color='#ff33ff'";//Se a conta for negativa, muda a cor da linha ...
            }
            if($campos[$i]['predatado'] == 1) $color = "color='green'";//Aki é p/ colorir a conta que já foi paga com cheque pré-datado ...
?>
    <tr class='linhanormal' onclick="<?=$onclick;?>" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' class='checkbox' name='chkt_conta_apagar[]' value="<?=$campos[$i]['id_conta_apagar'];?>" onclick="<?=$onclick;?>">
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-7' <?=$color;?>>
                <?=$campos[$i]['semana'];?>
            </font>
        </td>        
        <td align='left'>
            <?
                if($campos[$i]['id_nfe'] > 0) {//Só mostrarei esse link caso exista NF ...
            ?>
            <a href="javascript:nova_janela('../../../../compras/pedidos/nota_entrada/itens/itens.php?id_nfe=<?=$campos[$i]['id_nfe'];?>&pop_up=1', 'NOTA_FISCAL', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title="Detalhes de Nota Fiscal de Entrada" class="link">
            <?
                }
            ?>
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-7' <?=$color;?>>
                    <?=$campos[$i]['numero_conta'];?>
                </font>
            </a>
            <?
                if($campos[$i]['id_conta_apagar_automatica'] > 0) {
            ?>
            <img src = '../../../../../imagem/letra_a.png' width='17' height='20' title='Conta Gerada de Forma Automática' onclick="nova_janela('../../../cadastro/contas_automaticas/classes/cadastrar_conta/alterar.php?id_conta_apagar_automatica=<?=$campos[$i]['id_conta_apagar_automatica'];?>&id_empresa_menu=<?=$campos[$i]['id_empresa'];?>&pop_up=1', 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" style='cursor:help' border='0'>
            <?
                }
                
                $livre_debito = 'N';//Para não dar erro quando retornar do Loop ...
                
                if($campos[$i]['id_nfe'] > 0) {//Exibir Dados de NFs de Entrada ...
                    //Verifico se o id_nfe é uma NF de Entrada que é Livre de Débito ...
                    $sql = "SELECT `livre_debito` 
                            FROM `nfe` 
                            WHERE `id_nfe` = '".$campos[$i]['id_nfe']."' LIMIT 1 ";
                    $campos_nfs     = bancos::sql($sql);
                    $livre_debito   = $campos_nfs[0]['livre_debito'];
                }
                
                //Se existir a marcação de Livre de Débito ...
                if($livre_debito == 'S') echo '<font color="darkgreen" title="Livre de Débito Propaganda / Marketing" style="cursor:help"><b> (LD)</b></font>';
            ?>
        </td>
        <td align='left'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-7' <?=$color;?>>
                <?=$campos[$i]['fornecedor'];?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
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
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-7' <?=$color;?>>
                <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-7' <?=$color;?>>
                <?=data::datetodata($campos[$i]['data_vencimento'], '/');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-7' <?=$color;?>>
                <?=data::datetodata($campos[$i]['data_vencimento_alterada'], '/');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-7' <?=$color;?>>
                <?$url = '../../../../../imagem/financeiro/tipos_pag_rec/'.$campos[$i]['imagem'];?>
                <img src="<?=$url;?>" width='33' height='20' border='0' title='<?=$campos[$i]['pagamento'];?>'>
            </font>
            <?
                if($campos[$i]['predatado'] == 1) {//Se a Conta Estiver como Pré-Datada ...
                    /*Com certeza existe Cheque e eu busco esse N.º do Cheque que foi utilizado p/ a 
                    Conta à Pagar em Questão ...*/
                    $sql = "SELECT c.`id_cheque`, c.`num_cheque` 
                            FROM `contas_apagares_quitacoes` caq 
                            INNER JOIN `cheques` c ON c.`id_cheque` = caq.`id_cheque` 
                            WHERE caq.`id_conta_apagar` = '".$campos[$i]['id_conta_apagar']."' ";
                    $campos_cheque = bancos::sql($sql);
                    $linhas_cheque = count($campos_cheque);
                    for($j = 0; $j < $linhas_cheque; $j++) {
                    
            ?>
                    <a href = "javascript:nova_janela('../../cheque/classes/manipular/detalhes.php?id_cheque=<?=$campos_cheque[$j]['id_cheque'];?>', 'NOTA_FISCAL', '', '', '', '', 300, 980, 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes de Cheque Pré-Datado' style='cursor:help' class='link'>
                        <br/><?=$campos_cheque[$j]['num_cheque'];?>
                    </a>
            <?
                    }
                }
            ?>
        </td>
        <td align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-7' <?=$color;?>>
            <?
                if($campos[$i]['valor'] == '0.00') {
                    echo '&nbsp;';
                }else {
                    echo $moeda.number_format($campos[$i]['valor'], 2, ',', '.');
                }
            ?>
            </font>
        </td>
        <td>
            <?$tipo_juros = ($campos[$i]['taxa_juros'] == 'S') ? 'Simples' : 'Composto';?>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-7' title='Multa R$ <?=number_format($campos[$i]['multa'], 2, ',', '.');?> + Juros <?=number_format($campos[$i]['taxa_juros'], 2, ',', '.').' % '.$tipo_juros;?>' style='cursor:help' <?=$color;?>>
                <?=$moeda.number_format($calculos_conta_pagar['valores_extra'], 2, ',', '.');?>
            </font>
        </td>
        <td align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-7' <?=$color;?>>
                <?='R$ '.number_format($calculos_conta_pagar['valor_reajustado'], 2, ',', '.');?>
            </font>
        </td>
        <td align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-7' <?=$color;?>>
            <?
                if($campos[$i]['valor_pago'] == '0.00') {
                    echo '&nbsp;';
                }else {
                    echo $moeda.number_format($campos[$i]['valor_pago'], 2, ',', '.');
                }
            ?>
            </font>
        </td>
        <td align='right'>
            <?
                //Esse link só é mostrado p/ Roberto 62, Dona Sandra 66, Dárcio 98 e Netto porque programam 147 ...
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 66 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 147) {
            ?>
            <a href="javascript:alterar_urgente('<?=$campos[$i]['id_conta_apagar'];?>', '<?=$campos[$i]['urgente'];?>')" title='Urgente ou Não Urgente' class='link'>
            <?
                }
            ?>
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-7' <?=$color;?>>
                <?
                    //Se essa Conta à Pagar for vinculada a uma NF de Entrada, então executo a Query abaixo ...
                    if($campos[$i]['id_nfe'] > 0) {//Conta à Pagar com NF de Entrada ...
                        //Verifico se essa NF de Entrada foi paga pelo Caixa de Compras ...
                        $sql = "SELECT pago_pelo_caixa_compras 
                                FROM `nfe` 
                                WHERE `id_nfe` = '".$campos[$i]['id_nfe']."' LIMIT 1 ";
                        $campos_nfe = bancos::sql($sql);
                        if($campos_nfe[0]['pago_pelo_caixa_compras'] == 'S') {//Paga pelo Cx de Compras ...
                            echo 'PG CX COMPRAS';
                        }else {//Não foi paga ...
                            //Só contabiliza no Total da Página, as Contas que estão marcadas como Urgente ...
                            if($campos[$i]['urgente'] == 'S') $valor_pagar_total+= $valor_pagar_real;
                            echo 'R$ '.number_format($valor_pagar_real, 2, ',', '.');
                        }
                    }else {//Conta à Pagar em outra Situação ...
                        //Só contabiliza no Total da Página, as Contas que estão com Marcação de Urgentes ...
                        if($campos[$i]['urgente'] == 'S') $valor_pagar_total+= $valor_pagar_real;
                        echo 'R$ '.number_format($valor_pagar_real, 2, ',', '.');
                    }
                    if($campos[$i]['urgente'] == 'N') echo '<font color="#4F4F4F"><b> / (ÑU)</b></font>';
                ?>
                </font>
            </a>
            <?
                //Esse só iremos exibir quando for moeda Estrangeira ...
                if($campos[$i]['id_tipo_moeda'] > 1) echo '<br/><font color="brown"><b> / '.$campos[$i]['simbolo'].' '.number_format($valor_pagar, 2, ',', '.').'</b></font>';
            ?>
        </td>
        <td align='left'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                <?=$vetor_observacao_follow_up[$campos[$i]['id_conta_apagar']];?>
            </font>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho'>
        <td colspan='7'>
            Contas Vencidas: 
            <font color='yellow'>
                <?=$cont_vencidas;?>
            </font>
            &nbsp;&nbsp;
            Contas à Vencer: 
            <font color='yellow'>
                <?=$cont_vencer;?>
            </font>
            &nbsp;&nbsp;
            Total: 
            <font color='yellow'>
                <?=$cont_vencer + $cont_vencidas;?>
            </font>
        </td>
        <td colspan='5'>
<?
        $semana = data::numero_semana(date('d'),date('m'),date('Y'));
        $ano = date('Y');
        $sql = "SELECT dia_inicio, dia_fim 
                FROM `semanas` 
                WHERE `semana` = '$semana' 
                AND SUBSTRING(`dia_inicio`, 1, 4) = '$ano' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) != 0) {
            $dia_inicio = data::datetodata($campos[0]['dia_inicio'],'/');
            $dia_inicio = substr($dia_inicio, 0, 6).substr($dia_inicio, 8, 2);
            $dia_fim    = data::datetodata($campos[0]['dia_fim'],'/');
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
                <font color='yellow'>
                    Semana: 
                </font>
                <?=$semana;?>
<?
        }
?>
        </td>
        <td colspan='2' align='right'>
            Total: 
            <font color='yellow'>
                <?='R$ '.number_format($valor_pagar_total, 2, ',', '.');?>
            </font>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<input type='hidden' name='bloquear_pagamento' value='0'>
<!--Esse Hidden é controlado pelo Pop-UP de Incluir Itens da Nota Automático-->
<input type='hidden' name='recarregar'>
<!--Essa variável parâmetro é referente a paginação da Tela de Itens, que é a Tela Principal, ela
é controlada pelos pop_ups de Inclusão de Contas-->
<input type='hidden' name='parametro' value='<?=$parametro;?>'>
</form>
</body>
</html>
<?
}

if(!empty($valor)) {
?>
    <Script Language = 'JavaScript'>
        alert('<?=$mensagem[$valor];?>')
    </Script>
<?}?>