<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/calculos.php');
require('../../../../lib/compras_new.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/financeiro/relatorio/import_a_pagar/consultar_contas.php', '../../../../');

//Busca de Algumas Variáveis que serão utilizadas mais abaixo ...
$fator_custo_importacao = genericas::variavel(1);
$valor_dolar_dia        = genericas::moeda_dia('dolar');
$valor_euro_dia         = genericas::moeda_dia('euro');
?>
<html>
<head>
<title>.:: Itens de Importações à Pagar ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function registrar_follow_up(id_pedido) {
    nova_janela('../../../compras/pedidos/follow_up/follow_up_manipular.php?id_pedido='+id_pedido+'&nao_exibir=1', 'POP', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
}
</Script>
</head>
<body>
<form name='form' method='post'>
<table width='90%' border='1' cellspacing='0' cellpadding='0' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='8'>
            Importações à Pagar
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td rowspan="2" width='17%' bgcolor='#CECECE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>N.º / Conta</b>
            </font>
        </td>
        <td rowspan="2" bgcolor='#CECECE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>Fornecedor / Descrição da Conta</b>
            </font>
        </td>
        <td rowspan="2" bgcolor='#CECECE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>Data de<br>Vencimento</b>
            </font>
        </td>
        <td colspan="2" bgcolor='#CECECE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>Numerário</b>
            </font>
        </td>
        <td colspan="2" bgcolor='#CECECE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>Fob</b>
            </font>
        </td>
        <td rowspan="2" bgcolor='#CECECE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>Emp.</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>Valor</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>Valor R$</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>Valor</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>Valor R$</b>
            </font>
        </td>
    </tr>
<?
    $sql = "SELECT nfe.id_nfe 
            FROM `nfe` 
            INNER JOIN `contas_apagares` ca ON ca.id_nfe = nfe.id_nfe 
            WHERE nfe.`id_importacao` > '0' ";
    $campos_nfes_importadas = bancos::sql($sql);
    $linhas_nfes_importadas = count($campos_nfes_importadas);
    for ($i = 0; $i < $linhas_nfes_importadas; $i++) $id_nfes_importadas.= $campos_nfes_importadas[$i]['id_nfe'].', ';
    $id_nfes_importadas = substr($id_nfes_importadas, 0, strlen($id_nfes_importadas) - 2);

//Aqui eu busco todas as NFes de Compras em Aberto que possuem Importação atrelada ...
//nome_importacao, data_vencimento, fornecedor, valor_total_nf, 
$sql = "SELECT e.nomefantasia, f.razaosocial, i.nome, nfe.id_nfe, nfe.id_tipo_moeda, nfe.num_nota, DATE_FORMAT(nfef.data, '%d/%m/%Y') AS data_vencimento, nfef.valor_parcela_nf, CONCAT(tm.simbolo, '&nbsp;') AS simbolo 
        FROM `nfe` 
        INNER JOIN `nfe_financiamentos` nfef on nfef.id_nfe = nfe.id_nfe 
        INNER JOIN `empresas` e on e.id_empresa = nfe.id_empresa 
        INNER JOIN `fornecedores` f on f.id_fornecedor = nfe.id_fornecedor 
        INNER JOIN `importacoes` i ON i.id_importacao = nfe.id_importacao 
        INNER JOIN `tipos_moedas` tm on tm.id_tipo_moeda = nfe.id_tipo_moeda 
        WHERE nfe.id_importacao > '0' 
        AND nfe.id_nfe 
        NOT IN ($id_nfes_importadas) ";
$campos_nfe = bancos::sql($sql);
$linhas_nfe = count($campos_nfe);
for ($i = 0; $i < $linhas_nfe; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                <a href = '../../../compras/pedidos/nota_entrada/itens/itens.php?id_nfe=<?=$campos_nfe[$i]['id_nfe'];?>&pop_up=1' class='html5lightbox'>
                    <?=$campos_nfe[$i]['nome'].' - '.$campos_nfe[$i]['num_nota'];?>
                </a>
            </font>
        </td>
        <td align='left'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                <?=$campos_nfe[$i]['razaosocial'];?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                <?=$campos_nfe[$i]['data_vencimento'];?>
            </font>
        </td>
        <td align='right'>
                &nbsp;
        </td>
        <td align='right'>
                &nbsp;
        </td>
        <td align='right'>
            <?=$campos_nfe[$i]['simbolo'].number_format($campos_nfe[$i]['valor_parcela_nf'], 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
            //Tratamento com o Tipo de Moeda p/ na hora de exibir o Valor à Pagar em Reais ...
            if($campos_nfe[$i]['id_tipo_moeda'] == 2) {//Dólar
                echo number_format($campos_nfe[$i]['valor_parcela_nf'] * $valor_dolar_dia, 2, ',', '.');
            }else if($campos_nfe[$i]['id_tipo_moeda'] == 3) {//Euro
                echo number_format($campos_nfe[$i]['valor_parcela_nf'] * $valor_euro_dia, 2, ',', '.');
            }
        ?>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' style="cursor:help" title="<?=$campos_nfe[$i]['nomefantasia'];?>" <?=$color;?>>
                <?=substr($campos_nfe[$i]['nomefantasia'], 0, 1);?>
            </font>
        </td>
    </tr>
<?
}

/************************************************Estrutura do UNION************************************************/
/*1) FOB ou Contas Apagar geradas no Financeiro e atrelada a uma Importação - "São Despesas 
 Aqui eu trago todas as Importações que foram pagas Parciais ou de Forma Total com Cheque Pré-Datado ...
2) Aqui eu trago todas as Importações que estão vinculadas a Pedidos de Compras que estão em Aberto ainda, mas a Conta à Pagar "Numerário" 
 já foi quitada pelo Financeiro ...*/
$sql = "(SELECT ca.*, e.nomefantasia, f.razaosocial AS fornecedor, f.despachante, tp.`pagamento`, tp.`imagem`, CONCAT(tm.simbolo, '&nbsp;') AS simbolo 
            FROM `contas_apagares` ca 
            INNER JOIN `empresas` e ON e.id_empresa = ca.id_empresa 
            INNER JOIN `fornecedores` f ON f.id_fornecedor = ca.id_fornecedor 
            INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = ca.`id_tipo_pagamento_recebimento` 
            INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = ca.id_tipo_moeda 
            WHERE ca.id_importacao > '0' 
            AND ca.ativo = '1' 
            AND (ca.status < '2' OR (ca.status = '2' AND ca.predatado = '1'))) 
            UNION ALL 
            (SELECT ca.*, e.nomefantasia, f.razaosocial AS fornecedor, f.despachante, tp.`pagamento`, tp.`imagem`, CONCAT(tm.simbolo, '&nbsp;') AS simbolo 
            FROM `contas_apagares` ca 
            INNER JOIN `pedidos` p ON p.id_pedido = ca.id_pedido AND p.status = '1' 
            INNER JOIN `empresas` e ON e.id_empresa = ca.id_empresa 
            INNER JOIN `fornecedores` f ON f.id_fornecedor = ca.id_fornecedor 
            INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = ca.`id_tipo_pagamento_recebimento` 
            INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = ca.id_tipo_moeda 
            WHERE ca.id_importacao > '0' 
            AND ca.ativo = '1' 
            AND ca.status = '2') ORDER BY data_vencimento ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas == 0) {
    exit('NÃO EXISTE FOB/NUMERÁRIO EM ABERTO !');
}else if($linhas > 0) {
    $dia = date('d');
    $mes = date('m');
    $ano = date('Y');
    $data_hoje = $ano.$mes.$dia;

    $cont_vencer_alba = 0;
    $cont_vencer_tool = 0;
    $cont_vencidas_alba = 0;
    $cont_vencidas_tool = 0;

    for ($i = 0; $i < $linhas; $i++) {
            $moeda              = $campos[$i]['simbolo'];//Essa variável iguala o tipo de moeda da conta à pagar ...
            $data_vencimento 	= substr($campos[$i]['data_vencimento'], 0, 4).substr($campos[$i]['data_vencimento'], 5, 2).substr($campos[$i]['data_vencimento'], 8, 2);
            //Aqui verifica se é previsão também para poder chamar a função que bloqueia o pagamento
            if($campos[$i]['previsao'] == 1) {
                $color = "color='blue'";
            }else {
                $color = ($data_vencimento < $data_hoje) ? "color = 'red'" : '';
                //Aqui faz esse cálculo só para verificar se é negativo e mudar a cor da linha
                $valor_pagar_conta = $campos[$i]['valor'] - $campos[$i]['valor_pago'];
                if($campos[$i]['id_tipo_moeda'] == 2) {//Dólar
                    $valor_pagar_conta*= $valor_dolar_dia;
                }else if($campos[$i]['id_tipo_moeda'] == 3) {//Euro
                    $valor_pagar_conta*= $valor_euro_dia;
                }
                if($valor_pagar_conta < 0) $color = "color='#ff33ff'";//Aqui é para contas negativas
            }
            if($campos[$i]['status'] == 2) $color = "color='green'";//Aqui é para colorir a conta que já foi paga com cheque pré-datado
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
            <td align='left'>
                    <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                    <?
                        if(empty($campos[$i]['id_nfe']) && empty($campos[$i]['id_pedido'])) {
                            echo $campos[$i]['numero_conta'];
                        }else {
                            if(!empty($campos[$i]['id_nfe'])) {
                    ?>
                                <a href = '../../../compras/pedidos/nota_entrada/itens/itens.php?id_nfe=<?=$campos[$i]['id_nfe'];?>&pop_up=1' class='html5lightbox'>
                                    <?=$campos[$i]['numero_conta'];?>
                                </a>
                    <?
                            }
                            if(!empty($campos[$i]['id_pedido'])) {
                    ?>
                                <a href = '../../../compras/pedidos/itens/itens.php?id_pedido=<?=$campos[$i]['id_pedido'];?>&pop_up=1' class='html5lightbox'>
                                    <?=$campos[$i]['numero_conta'];?>
                                </a>
                                &nbsp;
                                <img src = "../../../../imagem/menu/alterar.png" border='0' title="Registrar Follow-UP" alt="Registrar Follow-UP" onclick="registrar_follow_up('<?=$campos[$i]['id_pedido'];?>')">
                    <?
                            }
                        }
                    ?>
                            <img src = "../../../../imagem/cifrao.png" border='0' width="20" height="20" title="Visualizar Pagamento(s)" alt="Visualizar Pagamento(s)" onclick="html5Lightbox.showLightbox(7, '../../pagamento/detalhes.php?id_conta_apagar=<?=$campos[$i]['id_conta_apagar'];?>')">
                    </font>
            </td>
            <td align='left'>
                    <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                            <?=$campos[$i]['fornecedor'].'       '.$campos[$i]['id_conta_apagar'];?>
                    </font>
                    <?
//Busca de alguns dados na Tabela de Pedidos referentes a Importação ...
                            if(!empty($campos[$i]['id_pedido'])) {
/********************************************************************************************/
/*Aqui eu verifico se o Pedido foi pelo modo Novo de Financiamento, caso este foi feito
desse modo, eu busco apenas o Primeiro Prazo, porque estarei utilizando esse prazo em alguns 
casos mais abaixo ...*/
                                    $sql = "SELECT dias 
                                            FROM `pedidos_financiamentos` 
                                            WHERE `id_pedido` = '".$campos[$i]['id_pedido']."' ORDER BY dias LIMIT 1 ";
                                    $campos_financiamento = bancos::sql($sql);
                                    $linhas_financiamento = count($campos_financiamento);
                                    if($linhas_financiamento == 1) {
                                            $primeiro_prazo_pedido = $campos_financiamento[0]['dias'];
                                    }else {
                                            $sql = "SELECT prazo_pgto_a 
                                                            FROM `pedidos` 
                                                            WHERE `id_pedido` = '".$campos[$i]['id_pedido']."' LIMIT 1 ";
                                            $campos_prazo_antigo = bancos::sql($sql);
                                            $primeiro_prazo_pedido = $campos_prazo_antigo[0]['prazo_pgto_a'];
                                    }
/********************************************************************************************/
                                    $sql = "SELECT id_tipo_moeda, prazo_entrega, prazo_navio, data_entrada_armazem, periodo_armazenagem, data_pagto_numerario, data_retirada_porto 
                                                    FROM `pedidos` 
                                                    WHERE `id_pedido` = '".$campos[$i]['id_pedido']."' LIMIT 1 ";
                                    $campos_pedido = bancos::sql($sql);
//Data de Embarque ...
                                    if($campos_pedido[0]['prazo_entrega'] != '0000-00-00') {
                                            echo '<br><br>
                                                            <font color="darkblue">
                                                                    <b>Data de Embarque Atual: 
                                                        </font></b>'.data::datetodata($campos_pedido[0]['prazo_entrega'], '/');
                                    }
//Prazo do Navio ...
                                    if($campos_pedido[0]['prazo_navio'] != 0) {
                                            echo '<br>
                                                            <font color="darkblue">
                                                                    <b>Prazo de Viagem do Navio: 
                                                        </font></b>'.$campos_pedido[0]['prazo_navio'];
                                    }
//Data de Chegada Atual do Porto ...
                                    $data_chegada_atual = data::adicionar_data_hora(data::datetodata($campos_pedido[0]['prazo_entrega'], '/'), $campos_pedido[0]['prazo_navio']);
                                    if($data_chegada_atual != '0000-00-00') {
                                            echo '<br>
                                                            <font color="darkblue">
                                                                    <b>Data de Chegada Atual do Porto: 
                                                        </font></b>'.$data_chegada_atual;
                                    }
//Data de Entrada no armazém ...
                                    if($campos_pedido[0]['data_entrada_armazem'] != '0000-00-00') {
                                            echo '<br>
                                                            <font color="darkblue">
                                                                    <b>Data de Entrada no armazém: 
                                                        </font></b>'.data::datetodata($campos_pedido[0]['data_entrada_armazem'], '/');
                                    }
//Período de Armazenagem ...
                                    if($campos_pedido[0]['periodo_armazenagem'] != 0) {
                                            echo '<br><b>
                                                            <font color="darkblue">
                                                                    Período de Armazenagem: 
                                                            </font></b>'.$campos_pedido[0]['periodo_armazenagem'];
//Aqui eu verifico a Qtde de Dias entre a Data Atual e a Data de Entrada no armazém ...
                                            $dias_entre_armazem_atual = data::diferenca_data($campos_pedido[0]['data_entrada_armazem'], date('Y-m-d'));	
                                            echo ' dias <b>('.(intval($dias_entre_armazem_atual[0] / $campos_pedido[0]['periodo_armazenagem']) + 1).'º Período)</b>';
                                    }
//Data de Pagto do Numerário ...
                                    if($campos_pedido[0]['data_pagto_numerario'] != '0000-00-00') {
                                            echo '<br><b>
                                                            <font color="darkblue">
                                                                    Data de Pagto. do Numerário: 
                                                            </font></b>'.data::datetodata($campos_pedido[0]['data_pagto_numerario'], '/');
                                    }
//Previsão Data de Retirada do Porto ...
                                    if($campos_pedido[0]['data_retirada_porto'] != '0000-00-00') {
                                            echo '<br><b>
                                                            <font color="darkblue">
                                                                    Previsão Data de Retirada no Porto: 
                                                            </font></b>'.data::datetodata($campos_pedido[0]['data_retirada_porto'], '/');
                                    }
//Somente p/ as contas na cor azul ...
                                    if($campos[$i]['previsao'] == 1) {
                                            if($campos_pedido[0]['prazo_entrega'] != '0000-00-00') {
                                                    echo '<br>
                                                                    <font color="darkgreen">
                                                                            <b>Data de Venc. FOB: 
                                                                </font></b>'.$primeiro_prazo_pedido.' DDL - '.data::adicionar_data_hora(data::datetodata($campos_pedido[0]['prazo_entrega'], '/'), $primeiro_prazo_pedido);
                                            }
                                    }
//Busca do último Follow-Up(s) Registrado para este Pedido ...
                                    $sql = "SELECT f.`nome`, fu.`observacao`, fu.`data_sys` 
                                            FROM `follow_ups` fu 
                                            INNER JOIN `funcionarios` f ON f.`id_funcionario` = fu.`id_funcionario` 
                                            WHERE fu.`identificacao` = '".$campos[$i]['id_pedido']."' 
                                            AND fu.`origem` = '16' ORDER BY fu.`data_sys` DESC LIMIT 1 ";
                                    $campos_follow = bancos::sql($sql);
                                    if(count($campos_follow_up) == 1) {//Se encontrou um Registro então ...
                                        echo '<br><br><b>
                                                <font color="black">
                                                        Funcionário: 
                                                </font></b>'.$campos_follow_up[0]['nome'].
                                                '<b>
                                                <font color="black">
                                                        - Data da Ocorrência: 
                                                </font></b>'.data::datetodata($campos_follow_up[0]['data_sys'], '/').
                                                '<b>
                                                <font color="black">
                                                        - Observação: 
                                                </font></b>'.$campos_follow_up[0]['observacao'];
                                    }
                            }
                    ?>
            </td>
            <td>
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                    <?=data::datetodata($campos[$i]['data_vencimento'], '/');?>
                </font>
            </td>
            <td align='right'>
                    <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                    <?
/*******************Definição das variáveis de $valor_numerario*******************/			
                        if($campos[$i]['despachante'] == 'S') {//Quando Fornecedor = Despachante vem por esse caminho ...
                            if($campos[$i]['id_pedido'] > 0) {//Se existir Pedido então ...
                                //Aqui ele vasculha todos as importações que possuem numerário de pedido
                                $sql = "SELECT SUM(ip.`qtde` * ip.`preco_unitario`) AS valor_pedido 
                                        FROM `contas_apagares` ca 
                                        INNER JOIN `pedidos` p ON p.id_pedido = ca.id_pedido 
                                        INNER JOIN `itens_pedidos` ip ON ip.id_pedido = p.id_pedido 
                                        WHERE ca.id_conta_apagar = '".$campos[$i]['id_conta_apagar']."' GROUP BY p.id_pedido ";
                                $campos_pedido = bancos::sql($sql);
                                //O Valor do Numerário é o Valor da Conta a ser pago Multiplicado pelo Fator de Custo de Importação ...
                                $valor_numerario = $campos_pedido[0]['valor_pedido'] * ($fator_custo_importacao - 1);
                                echo $moeda.number_format($valor_numerario, 2, ',', '.');
                            }else {//Se não existe Pedido, então o Valor Conta Financeira = Zero ...
                                $valor_numerario = 0;
                                echo '&nbsp;';
                            }
                        }else {//Outro Fornecedor Qualquer é a própria Conta Financeira ...
                            $valor_numerario = $campos[$i]['valor'];
                            echo '&nbsp;';
                        }
                    ?>
                    </font>
            </td>
            <td align='right'>
                    <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                    <?
/*******************Definição das variáveis de $valor_pagar*******************/
                            if($campos[$i]['status'] == 2) {//Se a conta estiver totalmente quitada, busco o seu valor Total
                                //Nesta parte vejo o que foi pago com cheque para descontar no predatado ...
                                $sql = "SELECT SUM(caq.valor) valor 
                                        FROM `contas_apagares` ca 
                                        INNER JOIN `contas_apagares_quitacoes` caq ON caq.id_conta_apagar = ca.id_conta_apagar 
                                        INNER JOIN `cheques` c ON c.id_cheque = caq.id_cheque AND c.status = '2' AND c.predatado = '1' 
                                        WHERE ca.id_conta_apagar = '".$campos[$i]['id_conta_apagar']."' ";
                                $campos_pagamento   = bancos::sql($sql);
                                $valor_pagar        = $campos_pagamento[0]['valor'];
                            }else {//Abato o Valor já pago desta Conta ...
                                if($valor_numerario > 0) {//Se existir numerário ...
                                    $valor_pagar = $valor_numerario - $campos[$i]['valor_pago'];
                                }else {//Se não existe numerário ...
                                    $valor_pagar = $campos[$i]['valor'] - $campos[$i]['valor_pago'];
                                }
                            }
/*******************Definição das variáveis de $valor_pagar_reais*******************/
                            //Tratamento com o Tipo de Moeda p/ na hora de exibir o Valor à Pagar em Reais ...
                            if($campos[$i]['id_tipo_moeda'] == 2) {//Dólar
                                $valor_pagar_reais = $valor_pagar * $valor_dolar_dia;
                            }else if($campos[$i]['id_tipo_moeda'] == 3) {//Euro
                                $valor_pagar_reais = $valor_pagar * $valor_euro_dia;
                            }else {//Reais mesmo ...
                                $valor_pagar_reais = $valor_pagar;
                            }
                            //Aqui armazena o valor total das contas que estão vencidas
                            if($data_vencimento < $data_hoje) {
                                if($campos[$i]['id_empresa'] == 1) {//Se for Albafer ...
                                    $cont_vencidas_alba+= $valor_pagar_reais;
                                }else if($campos[$i]['id_empresa'] == 2) {//Se for Tool ...
                                    $cont_vencidas_tool+= $valor_pagar_reais;
                                }
                            }else {
                                if($campos[$i]['id_empresa'] == 1) {//Se for Albafer ...
                                    $cont_vencer_alba+= $valor_pagar_reais;
                                }else if($campos[$i]['id_empresa'] == 2) {//Se for Tool ...
                                    $cont_vencer_tool+= $valor_pagar_reais;
                                }
                            }

                            if($campos[$i]['despachante'] == 'S') {//Quando Fornecedor = Despachante vem por esse caminho ...
                                if($campos[$i]['id_pedido'] > 0) {
                                    //Utiliza essa variável lá em baixo com o Somatório Total de Todas as Contas à serem Pagas ...
                                    $valor_pagar_total_numerario+= $valor_pagar_reais;
                                    echo number_format($valor_pagar_reais, 2, ',', '.');
                                }else {
                                    echo '&nbsp;';
                                }
                            }else {
                                echo '&nbsp;';
                            }
                    ?>
                    </font>
            </td>
            <td align='right'>
                    <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                    <?
                        if($campos[$i]['despachante'] == 'S') {//Quando Fornecedor = Despachante vem por esse caminho ...
                            if($campos[$i]['id_pedido'] > 0) {//Se existir Pedido então ...
                                //Verifico se esse Pedido possui Antecipações ...
                                $sql = "SELECT SUM(valor) AS total_antecipacoes 
                                        FROM `antecipacoes` 
                                        WHERE `id_pedido` = '".$campos[$i]['id_pedido']."' ";
                                $campos_antecipacoes    = bancos::sql($sql);
                                $total_antecipacoes     = $campos_antecipacoes[0]['total_antecipacoes'];
                                //Busca o Tipo de Moeda do Pedido ...
                                $sql = "SELECT CONCAT(tm.simbolo, '&nbsp;') AS simbolo 
                                        FROM `pedidos` p 
                                        INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = p.id_tipo_moeda 
                                        WHERE p.`id_pedido` = '".$campos[$i]['id_pedido']."' LIMIT 1 ";
                                $campos_moeda = bancos::sql($sql);
                                echo $campos_moeda[0]['simbolo'].number_format($valor_pagar / ($fator_custo_importacao - 1), 2, ',', '.');
                                if($total_antecipacoes > 0) {
                                    echo '<br/>Antecipado: '.$campos_moeda[0]['simbolo'].number_format($total_antecipacoes, 2, ',', '.');
                                    echo '<br/>Saldo: '.$campos_moeda[0]['simbolo'].number_format($valor_pagar / ($fator_custo_importacao - 1) - $total_antecipacoes, 2, ',', '.');
                                }
                            }else {
                                echo $moeda.number_format($valor_pagar, 2, ',', '.');
                            }
                        }else {
/*Aqui as importações não possuem numerário de pedido, portanto já importa da nota direto, e aqui ele traz o 
valor sem ter multiplicado pelo valor dólar ou valor euro ...*/
                            echo $moeda.number_format($valor_pagar, 2, ',', '.');
                        }
                    ?>
                    </font>
            </td>
            <td align='right'>
                    <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                    <?
                        if($campos[$i]['despachante'] == 'S') {//Quando Fornecedor = Despachante vem por esse caminho ...
                            $sql = "SELECT p.id_tipo_moeda, SUM(ip.`qtde` * ip.`preco_unitario`) AS valor_pedido 
                                    FROM `contas_apagares` ca 
                                    INNER JOIN `pedidos` p ON p.id_pedido = ca.id_pedido 
                                    INNER JOIN `itens_pedidos` ip ON ip.id_pedido = p.id_pedido 
                                    WHERE ca.`id_conta_apagar` = '".$campos[$i]['id_conta_apagar']."' GROUP BY p.id_pedido ";
                            $campos_pedido = bancos::sql($sql);
                            if(count($campos_pedido) == 1) {//Busca do Valor Total do pedido ...
                                if($campos_pedido[0]['id_tipo_moeda'] == 2) {//Dólar
                                    $valor_pedido = ($campos_pedido[0]['valor_pedido'] - $total_antecipacoes) * $valor_dolar_dia;
                                }else if($campos_pedido[0]['id_tipo_moeda'] == 3) {//Euro
                                    $valor_pedido = ($campos_pedido[0]['valor_pedido'] - $total_antecipacoes) * $valor_euro_dia;
                                }
                                echo number_format($valor_pedido, 2, ',', '.');
                                $valor_pagar_total_fob+= $valor_pedido;
                            }else {
/*Aqui as importações não possuem numerário de pedido, portanto já importa a NF direto com o valor 
multiplicado pelo valor dólar ou valor euro ...*/
                                echo number_format($valor_pagar_reais, 2, ',', '.');
                                $valor_pagar_total_fob+= $valor_pagar_reais;
                            }
                        }else {//Fornecedor Não é Despachante ...
                            if($campos[$i]['id_pedido'] == 0) {//Se Não existir Pedido Exibe ...
                                if($campos[$i]['id_tipo_moeda'] == 2) {//Dólar
                                    $valor_pagar*= $valor_dolar_dia;
                                }else if($campos[$i]['id_tipo_moeda'] == 3) {//Euro
                                    $valor_pagar*= $valor_euro_dia;
                                }
                                echo number_format($valor_pagar, 2, ',', '.');
                                $valor_pagar_total_fob+= $valor_pagar_reais;
                            }else {
                                echo '&nbsp;';
                            }
                        }
                    ?>
                    </font>
            </td>
            <td>
                    <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' style="cursor:help" title="<?=$campos[$i]['nomefantasia'];?>" <?=$color;?>>
                            <?=substr($campos[$i]['nomefantasia'], 0, 1);?>
                    </font>
            </td>
    </tr>
<?
    }
?>
    <tr class='linhadestaque'>
            <td colspan='2'>
                    <font size='0'>
                            <b>Contas Vencidas: </b>
                            <br>
                            <font color="yellow">
                                    Albafer: 
                            </font>
                            <?='R$ '.number_format($cont_vencidas_alba, 2, ',', '.').' - ';?>
                            <font color="yellow">
                                    Tool Master: 
                            </font>
                            <?='R$ '.number_format($cont_vencidas_tool, 2, ',', '.');?>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <font color="yellow">
                                    Valor do Fator: 
                            </font>
                            <?=number_format($fator_custo_importacao, 2, ',', '.');?>
                            <br><br>
                            <b>Contas à Vencer: </b>
                            <br>
                            <font color="yellow">
                                    Albafer: 
                            </font>
                            <?='R$ '.number_format($cont_vencer_alba, 2, ',', '.').' - ';?>
                            <font color="yellow">
                                    Tool Master: 
                            </font>
                            <?='R$ '.number_format($cont_vencer_tool, 2, ',', '.');?>
                    </font>
            </td>
            <td>
                    <font size='0'>
                            <font color='yellow'><b>Dólar: </b></font>
                                    <?='R$ '.number_format($valor_dolar_dia, 4, ',', '.');?>
                            <br>
                            <font color='yellow'><b>Euro: </b></font>
                                    <?='R$ '.number_format($valor_euro_dia, 4, ',', '.');?>
                    </font>
            </td>
            <td align='right'>
                    <font size='2' color="#FF0000">
                            <b>Total: </b>
                    </font>
            </td>
            <td>
                    <font size='0' color="#FF0000">
                            <?='R$ '.number_format($valor_pagar_total_numerario, 2, ',', '.');?>
                    </font>
            </td>
            <td align='right'>
                    <font size='2' color="#FF0000">
                            <b>Total: </b>
                    </font>
            </td>
        <td align='left'>
            <font size='0' color="#FF0000">
                            <?='R$ '.number_format($valor_pagar_total_fob, 2, ',', '.');?>
                    </font>
            </td>
        <td align='right'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='submit' name='cmd_atualizar' value='Atualizar Relatório' title='Atualizar Relatório' class='botao'>
            <input type='button' name='cmd_consultar_cambio' value='Consultar Câmbio' title='Consultar Câmbio' onclick="html5Lightbox.showLightbox(7, '../../cambio/consultar_mural.php')" style='color:black' class='botao'>
        </td>
    </tr>
</table>
<?}?>
</body>
<div align='left'>
<pre>
<font size='2'><b>Legenda da Cor da Fonte</b></font>
<font color='black'>- Preta  => Conta Normal.</font>
<font color='red'>- Vermelha => Conta Vencida.</font>
<font color='blue'>- Azul    => Conta em Previsão.</font>
<font color='green'>- Verde  => Conta paga com pedido(s) em aberto. "Só desaparece quando o pedido for concluido"</font>
</pre>
</div>
</html>