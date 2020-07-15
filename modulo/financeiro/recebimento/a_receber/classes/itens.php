<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/intermodular.php');
require('../../../../../lib/data.php');
require('../../../../../lib/genericas.php');
require('../../../../../lib/faturamentos.php');
require('../../../../../lib/financeiros.php');
require('../../../../../lib/variaveis/intermodular.php');
session_start('funcionarios');

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}else if($id_emp == 0) {//Todas Empresas
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/todas_empresas/index.php';
}
segurancas::geral($endereco, '../../../../../../');

//////////////////////// Tratamentos para não furar o SQL ///////////////////////////
if(!empty($cmb_representante))      $condicao_representante = " AND cr.`id_representante` LIKE '$cmb_representante' ";
if(empty($cmb_tipo_recebimento))    $cmb_tipo_recebimento   = '%';
if(!empty($cmb_uf))                 $condicao_uf            = " AND c.`id_uf` LIKE '$cmb_uf' ";
if(empty($cmb_ano))                 $cmb_ano                = '%';
if(!empty($cmb_banco))              $condicao_banco         = " AND cr.`id_banco` LIKE '$cmb_banco' ";
									
$condicao_emp = ($id_emp == 0) ? '' : " AND cr.`id_empresa` = '$id_emp' ";

if(!empty($chkt_somente_exportacao)) $condicao_exportacao = " AND c.id_pais <> '31' ";

//Busca do último valor do dólar e do euro
$sql = "SELECT `valor_dolar_dia`, `valor_euro_dia` 
        FROM `cambios` 
        ORDER BY `id_cambio` DESC LIMIT 1 ";
$campos = bancos::sql($sql);
$valor_dolar 	= $campos[0]['valor_dolar_dia'];
$valor_euro 	= $campos[0]['valor_euro_dia'];

$data_retirada_60 = data::adicionar_data_hora(date('d/m/Y'), -60);
$data_retirada_60 = data::datatodate($data_retirada_60, '-');
if($chkt_mostrar == 1) $condicao = " AND cr.`data_vencimento_alterada` >= '$data_retirada_60' ";

if(!empty($txt_data_emissao_inicial)) {
    //Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
    if(substr($txt_data_emissao_final, 4, 1) != '-') {
        $txt_data_emissao_inicial 	= data::datatodate($txt_data_emissao_inicial, '-');
        $txt_data_emissao_final 	= data::datatodate($txt_data_emissao_final, '-');
    }
    //Aqui é para não dar erro de SQL
    $condicao1 = " AND cr.`data_emissao` BETWEEN '$txt_data_emissao_inicial' AND '$txt_data_emissao_final' ";
}

if(!empty($txt_data_vencimento_inicial)) {
    //Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
    if(substr($txt_data_vencimento_final, 4, 1) != '-') {
        $txt_data_vencimento_inicial = data::datatodate($txt_data_vencimento_inicial, '-');
        $txt_data_vencimento_final = data::datatodate($txt_data_vencimento_final, '-');
    }
    //Aqui é para não dar erro de SQL
    $condicao2 = " AND cr.`data_vencimento_alterada` BETWEEN '$txt_data_vencimento_inicial' AND '$txt_data_vencimento_final' ";
}

if(!empty($txt_data_inicial)) {
    //Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
    if(substr($txt_data_final, 4, 1) != '-') {
        $txt_data_inicial   = data::datatodate($txt_data_inicial, '-');
        $txt_data_final     = data::datatodate($txt_data_final, '-');
    }
}

if(!empty($txt_data_cadastro)) {
    //Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
    if(substr($txt_data_cadastro, 4, 1) != '-') $txt_data_cadastro = data::datatodate($txt_data_cadastro, '-');
}

//Essa adaptação só serve para o 1º SQL que não tem o relacional com a Tabela mesmo que não esteja preenchido o campo descrição da Conta ...
if(empty($txt_descricao_conta) && !empty($txt_cliente)) {
    $txt_descricao_conta_sql1 = $txt_cliente;
}else {
    $txt_descricao_conta_sql1 = $txt_descricao_conta;
}

if(!empty($chkt_somente_livre_debito)) $inner_join_nfs = " INNER JOIN `nfs` ON nfs.`id_nf` = cr.`id_nf` AND nfs.`livre_debito` = 'S' ";
			
$sql = "SELECT cr.*, c.`razaosocial`, c.`credito`, t.`recebimento`, t.`imagem`, CONCAT(tm.`simbolo`, '&nbsp;') AS simbolo 
        FROM `contas_receberes` cr 
        $inner_join_nfs 
        INNER JOIN `clientes` c ON c.`id_cliente` = cr.`id_cliente` AND (c.`nomefantasia` LIKE '%$txt_cliente%' OR c.`razaosocial` LIKE '%$txt_cliente%' OR cr.`descricao_conta` LIKE '%$txt_descricao_conta_sql1%') AND c.`ativo` = '1' AND c.`bairro` LIKE '%$txt_bairro%' AND c.`cidade` LIKE '%$txt_cidade%' $condicao_uf $condicao_exportacao 
        INNER JOIN `tipos_recebimentos` t ON t.`id_tipo_recebimento` = cr.`id_tipo_recebimento` 
        INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = cr.`id_tipo_moeda` 
        WHERE cr.`ativo` = '1' 
        AND cr.`status` < '2' 
        AND cr.`num_conta` LIKE '%$txt_numero_conta%' 
        AND SUBSTRING(cr.`data_vencimento_alterada`, 1, 4) LIKE '$cmb_ano' 
        AND cr.`semana` LIKE '%$txt_semana%' 
        AND SUBSTRING(cr.`data_sys`, 1, 10) LIKE '%$txt_data_cadastro%' 
        AND cr.`id_tipo_recebimento` LIKE '$cmb_tipo_recebimento' 
        $condicao_representante 
        $condicao_banco 
        $condicao_emp 
        $condicao 
        $condicao1 
        $condicao2 ORDER BY cr.`data_vencimento_alterada` ";
$campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
$linhas = count($campos);
/*******************************************************************************************/
if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        parent.location = 'consultar_contas.php?itens=1&valor=1&id_emp2=<?=$id_emp;?>'
    </Script>
<?
    exit;
}
/*Aqui eu envio o parâmetros linhas para o frame debaixo p/ não ter que refazer todo esse sql acima, com o objetivo de 
saber se é para aparecer os demais botões no frame de baixo como alterar, excluir, ...*/
?>
    <Script Language = 'JavaScript'>
        parent.rodape.location = 'rodape.php?linhas=<?=$linhas;?>'
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
<title>.:: Itens de Contas à Receber ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
/*Função que serve para verificar quantas contas que estão em previsão ou que já
foram pagas, se nesta retornar zero, então eu posso estar pagando as contas
apagares, do contrário ele não permite que eu venha pagar nenhuma conta*/
function bloquear(posicao) {
    var elements = document.form.elements
    posicao = eval(posicao) + 1
    if(elements[posicao].checked == true) {
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
        if(elementos[i].name == 'chkt_conta_receber[]') elementos[i].checked = checar
    }
}

//Toda vez que fechar o Pop-UP de Follow-UP, chama essa função p/ não perder o parâmetro de Filtro ...
function recarregar_tela() {
    parent.itens.location = parent.itens.location.href
}

function somente_livre_debito() {
    if(document.form.chkt_somente_livre_debito.checked) {
        parent.itens.location = parent.itens.location.href+'&chkt_somente_livre_debito=S'
    }else {
        parent.itens.location = parent.itens.location.href+'&chkt_somente_livre_debito='//Macete feito p/ não trazer + nada referente a LD ...
    }
}

function carta_cobranca(id_conta_receber) {
    var resposta = confirm('TEM CERTEZA QUE DESEJA GERAR UMA CARTA DE COBRANÇA ?')
    if(resposta == true) nova_janela('carta_cobranca.php?id_conta_receber='+id_conta_receber, 'CARTA_COBRANCA', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
}
</Script>
</head>
<body>
<form name='form'>
<table width='98%' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='17'>
            Contas à Receber
            <font color='yellow'>
            <?
                if($id_emp != 0) {//Diferente de Todas Empresas
                    echo genericas::nome_empresa($id_emp);
                }else {
                    echo 'TODAS EMPRESAS';
                }
                
                if(!empty($chkt_somente_livre_debito)) $checked = 'checked';
            ?>
            </font>
            &nbsp;-
            <input type='checkbox' name='chkt_somente_livre_debito' id='chkt_somente_livre_debito' title='Somente Livre de Débito' onclick='somente_livre_debito()' class='checkbox' <?=$checked;?>>
            <label for='chkt_somente_livre_debito'>
                Somente Livre de Débito
            </label>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' title='Marcar / Desmarcar Tudo' onclick='controlar_checkbox()' style='cursor:help' class='checkbox'>
        </td>
        <td>
            <font title='Semana' style='cursor:help'>
                Sm.
            </font>
        </td>
        <td>
            N.º da<br/>Conta
        </td>
        <td>
            Cliente / Descrição da Conta
        </td>
        <td>
            <font title='Empresa' style='cursor:help'>
                E
            </font>
        </td>
        <td>
            Cr
        </td>
        <td>
            Representante
        </td>
        <td>
            <font title='Data de Vencimento' style='cursor:help'>
                Data de<br/>Venc.
            </font>
        </td>
        <td>
            <font title='Data de Vencimento Alterada' style='cursor:help'>
                Data de<br/>Venc. Alt.
            </font>
        </td>
        <td>
            <font title='Data de Recebimento' style='cursor:help'>
                Data de <br/>Rec.
            </font>
        </td>
        <td>
            <font title='Tipo de Recebimento' style='cursor:help'>
                Tipo<br/>Rec.
            </font>
        </td>
        <td>
            <font title='Praça de Recebimento' style='cursor:help'>
                Praça de<br/>Rec.
            </font>
        </td>
        <td>
            Valor
        </td>
        <td>
            Valor Recebido
        </td>
        <td>
            Valores Extras
        </td>
        <td>
            <font title='Valor Reajustado' style='cursor:help'>
                Valor Reaj.
            </font>
        </td>
        <td>
            Observação
        </td>
    </tr>
<?
    //Aqui eu busco todas observações Follow-UPs dos ids_conta_receberes encontrados acima ...
    for($i = 0; $i < $linhas; $i++) $vetor_contas_receberes[] = $campos[$i]['id_conta_receber'];

    $sql = "SELECT `identificacao`, `observacao` 
            FROM `follow_ups` 
            WHERE `origem` = '4' 
            AND `identificacao` IN (".implode(',', $vetor_contas_receberes).") ";
    $campos_follow_ups = bancos::sql($sql);
    $linhas_follow_ups = count($campos_follow_ups);
    for($i = 0; $i < $linhas_follow_ups; $i++) $vetor_observacao_follow_up[$campos_follow_ups[$i]['identificacao']] = $campos_follow_ups[$i]['observacao'];

    $cont_vencer    = 0;
    $cont_vencidas  = 0;
    for ($i = 0; $i < $linhas; $i++) {
        //Essa variável iguala o tipo de moeda da conta à receber ...
        $moeda          = $campos[$i]['simbolo'];

        //Aqui eu limpo as variáveis para no caso de não encontrar o cliente no if abaixo e não manter os valores antigos
        $virar_link     = 1;//Valor Default - Significa que é p/ virar link ...

        $id_cliente     = $campos[$i]['id_cliente'];
        $cliente 	= $campos[$i]['razaosocial'];
        $credito 	= $campos[$i]['credito'];
        if(empty($credito)) $credito = ' ';
/***************************************************************************/
        $data_vencimento_alterada = substr($campos[$i]['data_vencimento_alterada'], 0, 4).substr($campos[$i]['data_vencimento_alterada'], 5, 2).substr($campos[$i]['data_vencimento_alterada'], 8, 2);
        $calculos_conta_receber = financeiros::calculos_conta_receber($campos[$i]['id_conta_receber']);
//Aqui verifica se é previsão também para poder chamar a função que bloqueia o pagamento
        if($campos[$i]['id_tipo_recebimento'] == 7) {// o 7 É O id DO PROTESTADO
            $color      = "color='blue'";
            $protestado = "PROTESTADO";
            $onclick    = "checkbox('form', 'chkt_tudo','".($i + 1)."', '#E8E8E8');document.form.chkt_tudo.checked = false";
        }else if($campos[$i]['id_tipo_recebimento'] == 9) {// o 9 É O id DO CARTORIO
            $color      = "color='blue'";
            $onclick    = "checkbox('form', 'chkt_tudo','".($i + 1)."', '#E8E8E8');document.form.chkt_tudo.checked = false";
        }else if($campos[$i]['predatado'] == 1) {// este predatado serve para cheque devolvido
            $color      = "color='green'";
            $onclick    = "checkbox('form', 'chkt_tudo','".($i + 1)."', '#E8E8E8');document.form.chkt_tudo.checked = false";
        }else {
            if($data_vencimento_alterada < $data_hoje) {
                $color      = "color='#FF0000'";
                $onclick    = "checkbox('form', 'chkt_tudo','".($i + 1)."', '#E8E8E8');document.form.chkt_tudo.checked = false";
                $cont_vencidas ++;
            }else {
                $color      = '';
                $onclick    = "checkbox('form', 'chkt_tudo','".($i + 1)."', '#E8E8E8');document.form.chkt_tudo.checked = false";
                $cont_vencer ++;
            }
//Se o Valor Reajustado for < do que Zero então eu apresento a linha na cor Rosa ...
            if($calculos_conta_receber['valor_reajustado'] < 0) $color = "color='#ff33ff'";
        }
?>
    <tr class='linhanormal' onclick="<?=$onclick;?>" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_conta_receber[]' value="<?=$campos[$i]['id_conta_receber'];?>" onclick="<?=$onclick;?>" class='checkbox'>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                <?=$campos[$i]['semana'];?>
            </font>
        </td>
        <td align='left'>
        <?
            $livre_debito = 'N';//Para não dar erro quando retornar do Loop ...
            if($virar_link == 1) {
                if($campos[$i]['id_nf'] > 0) {//Exibir Dados de NFs de Saída ...
                    //Verifico se o id_nf é uma NF de Saída que é Livre de Débito ...
                    $sql = "SELECT `livre_debito` 
                            FROM `nfs` 
                            WHERE `id_nf` = '".$campos[$i]['id_nf']."' LIMIT 1 ";
                    $campos_nfs     = bancos::sql($sql);
                    $livre_debito   = $campos_nfs[0]['livre_debito'];
                    //Exibo aqui detalhes da NF de Vendas de Saída ...
                    $a_href = "javascript:nova_janela('../../../../faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=".$campos[$i]['id_nf']."&pop_up=1', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '') ";
                }else if($campos[$i]['id_nf_outra'] > 0) {//Exibir Dados de NFs de Saída Outras ...
                    $a_href = "javascript:nova_janela('../../../../faturamento/outras_nfs/itens/detalhes_nota_fiscal.php?id_nf_outra=".$campos[$i]['id_nf_outra']."&pop_up=1', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '') ";
                }
        ?>
            <a href="<?=$a_href;?>" title='Visualizar Faturamento' class='link'>
        <?
            }
        ?>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
            <?
                if($campos[$i]['num_conta'] == '') {
                    echo '&nbsp;';
                }else {
                    echo $campos[$i]['num_conta'];
                }
                //Se existir a marcação de Livre de Débito ...
                if($livre_debito == 'S') echo '<font color="darkgreen" title="Livre de Débito Propaganda / Marketing" style="cursor:help"><b> (LD)</b></font>';
            ?>
            </font>
        <?
            if($virar_link == 1) {
        ?>
                </a>
        <?
            }
            
            if($campos[$i]['id_cliente'] > 0 && is_null($campos[$i]['id_nf']) && is_null($campos[$i]['id_nf_outra'])) {
        ?>                    
            <img src = '../../../../../imagem/letra_m.png' width='17' height='20' title='Conta Gerada de Forma Manual' style='cursor:help' border='0'>
        <?
            }
        ?>
        </td>
        <td align='left'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                <a href="javascript:nova_janela('../../../../classes/follow_ups/detalhes.php?identificacao=<?=$campos[$i]['id_conta_receber'];?>&origem=4', 'OUTRAS', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title="Registrar Follow-Up do Cliente" class='link'>
                <?
                    if(!empty($cliente) && $cliente != '&nbsp;') echo $cliente.' / ';
                    if($campos[$i]['descricao_conta'] == '') {
                        echo '&nbsp;';
                    }else {
                        echo $campos[$i]['descricao_conta'];
                    }
                ?>
                </a>
            </font>
            &nbsp;
            <?
                //Só irá mostrar carta de cobrança quando existir juros ...
                if($calculos_conta_receber['valores_extra'] > 0) {
                    $exibir_cobranca = 0;//Para não herdar valores do Loop Anterior, eu zero essa variável ...
                    //Aqui eu verifico se já foi feita alguma Cobrança para essa Duplicata ...
                    $sql = "SELECT COUNT(id_carta_cobranca) AS total_cobranca, MAX(CONCAT(SUBSTRING(data_sys, 1, 4), SUBSTRING(data_sys, 6, 2), SUBSTRING(data_sys, 9, 2))) AS data_cobranca 
                            FROM `cartas_cobrancas` 
                            WHERE `id_conta_receber` = '".$campos[$i]['id_conta_receber']."' 
                            GROUP BY id_conta_receber ORDER BY data_sys DESC ";
                    $campos_cobranca = bancos::sql($sql);
                    if($campos_cobranca[0]['total_cobranca'] == 0) {//Não realizou nenhuma cobrança ainda ...
                        $dias = data::diferenca_data($data_vencimento_alterada, $data_hoje);
                        if($dias[0] >= 3) $exibir_cobranca = 1;//Se estiver atrasado a + de 7 dias, aí sim exibir cobrança ...		 
                    }else if($campos_cobranca[0]['total_cobranca'] == 1) {//Já fez 1 cobrança ...
            ?>
                        <img src='../../../../../imagem/cobrou1vez.png' title='Foi feita 1 Cobrança' style='cursor:help' width='20' height='20' border='0'>
            <?
                        $dias = data::diferenca_data($campos_cobranca[0]['data_cobranca'], $data_hoje);
                        if($dias[0] >= 3) $exibir_cobranca = 1;//Se estiver atrasado a + de 10 dias, aí sim exibir nova cobrança ...
                    }else if($campos_cobranca[0]['total_cobranca'] == 2) {//Já fez 2 cobranças ...
            ?>
                        <img src = '../../../../../imagem/cobrou2vezes.png' title='Foram feitas 2 Cobranças' style='cursor:help' width='20' height='20' border='0'>
            <?
                        $dias = data::diferenca_data($campos_cobranca[0]['data_cobranca'], $data_hoje);
                        if($dias[0] >= 3) $exibir_cobranca = 1;//Se estiver atrasado a + de 10 dias, aí sim exibir nova cobrança ...
                    }else {//Já fez mais de 3 cobranças ...
            ?>
                        <img src = '../../../../../imagem/cobrou3vezes.png' title='Foram feitas 3 Cobranças' style='cursor:help' width='20' height='20' border='0'>
            <?
                        $dias = data::diferenca_data($campos_cobranca[0]['data_cobranca'], $data_hoje);
                        if($dias[0] >= 3) $exibir_cobranca = 1;//Se estiver atrasado a + de 10 dias, aí sim exibir nova cobrança ...
                    }
                    if($exibir_cobranca == 1) {
            ?>
            &nbsp;<img src='../../../../../imagem/cobrou0vez.png' title='Realizar Cobrança' onclick="carta_cobranca('<?=$campos[$i]['id_conta_receber'];?>')" style='cursor:help' width='20' height='20' border='0'>
            <?
                    }
                }
            ?>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
            <?
                $empresa_conta = genericas::nome_empresa($campos[$i]['id_empresa']);
                if($empresa_conta == 'ALBAFER') {
                    echo '<font title="ALBAFER" style="cursor:help">A</font>';
                }else if($empresa_conta == 'TOOL MASTER') {
                    echo '<font title="TOOL MASTER" style="cursor:help">T</font>';
                }else if($empresa_conta == 'GRUPO') {
                    echo '<font title="GRUPO" style="cursor:help">G</font>';
                }
            ?>
            </font>
        </td>
<?
        if($credito != ' ') {
?>
        <td onclick="nova_janela('../../../cadastro/credito_cliente/detalhes.php?id_cliente=<?=$id_cliente?>&pop_up=1', 'POP', '', '', '', '', 450, 780, 'c', 'c', '', '', 's', 's', '', '', '')">
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                <a href="#" title='Alterar Crédito do Cliente'>
                    <?=$credito;?>
                </a>
            </font>
        </td>
<?
        }else {
?>
        <td>
            &nbsp;
        </td>
<?
        }
?>
        <td>
            <?
                //Verifica se tem Representante na tabela relacional de conta à receber ...
                $sql = "SELECT r.`id_representante`, r.`nome_fantasia` 
                        FROM `contas_receberes` cr 
                        INNER JOIN `representantes` r ON r.`id_representante` = cr.`id_representante` 
                        WHERE cr.`id_conta_receber` = '".$campos[$i]['id_conta_receber']."' LIMIT 1 ";
                $campos_representante = bancos::sql($sql);
                if(count($campos_representante) > 0) {
            ?>
                <a href="javascript:nova_janela('../../../../vendas/representante/alterar2.php?passo=1&id_representante=<?=$campos_representante[0]['id_representante'];?>&pop_up=1', 'POP', '', '', '', '', 580, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                    <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                        <?=$campos_representante[0]['nome_fantasia'];?>
                    </font>
                </a>
            <?
                }else {
                    echo '&nbsp;';
                }
                //Verifico se existe Atraso de Pagamento p/ a Duplicata ...
                $sql = "SELECT id_comissao_estorno, ((porc_devolucao / 100) * valor_duplicata) AS comissao_estornada 
                        FROM `comissoes_estornos` 
                        WHERE `id_conta_receber` = '".$campos[$i]['id_conta_receber']."' 
                        AND `tipo_lancamento` = '1' LIMIT 1 ";
                $campos_comissao_estorno = bancos::sql($sql);
                if(count($campos_comissao_estorno) > 0) {
            ?>
                <img src='../../../../../imagem/carinha_triste.png' width='24' height='24' title='Comissao Estornada -> R$ <?=number_format($campos_comissao_estorno[0]['comissao_estornada'], 2, ',', '.');?>' style='cursor:help'/>
            <?
                }
            ?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                <?=data::datetodata($campos[$i]['data_vencimento'], '/');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                <?=data::datetodata($campos[$i]['data_vencimento_alterada'], '/');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                <?=data::datetodata($campos[$i]['data_recebimento'], '/');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                <?$url = '../../../../../imagem/financeiro/tipos_pag_rec/'.$campos[$i]['imagem'];?>
                <img src='<?=$url;?>' width="33" height="20" border="0" title='<?=$campos[$i]['recebimento'];?>'>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
            <?
                $sql = "SELECT b.banco 
                        FROM `contas_receberes` cr 
                        INNER JOIN `bancos` b on cr.id_banco = b.id_banco 
                        WHERE cr.id_conta_receber = '".$campos[$i]['id_conta_receber']."' LIMIT 1 ";
                $campos_bancos = bancos::sql($sql);
                if(count($campos_bancos) > 0) {
                        echo $campos_bancos[0]['banco'];
                }else {
                    if($campos[$i]['id_tipo_recebimento'] == 7) {
                        echo $protestado;
                    }else {
                        echo '&nbsp';
                    }
                }
            ?>
            </font>
        </td>
        <td align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
            <?
                if($campos[$i]['valor'] == '0.00') {
                    echo '&nbsp;';
                }else {
                    echo $moeda.number_format($campos[$i]['valor'], 2, ',', '.');
                }
            ?>
            </font>
        </td>
        <td align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
            <?
                if($campos[$i]['valor_pago'] == 0) {
                    echo '&nbsp;';
                }else {
                    echo $moeda.number_format($campos[$i]['valor_pago'], 2, ',', '.');
                }
            ?>
            </font>
        </td>
        <td align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-7' title='Desc. Dupl. R$ <?=number_format($campos[$i]['valor_desconto'], 2, ',', '.');?> | Abat. R$ <?=number_format($campos[$i]['valor_abatimento'], 2, ',', '.');?> | Juros <?=number_format($campos[$i]['taxa_juros'], 2, ',', '.');?> % | Despesas R$ <?=number_format($campos[$i]['valor_despesas'], 2, ',', '.');?>' style='cursor:help' <?=$color;?>>
                <?='R$ '.number_format($calculos_conta_receber['valores_extra'], 2, ',', '.');?>
            </font>
        </td>
        <td align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
            <?
                echo 'R$ '.number_format($calculos_conta_receber['valor_reajustado'], 2, ',', '.');
                $valor_receber_total+= $calculos_conta_receber['valor_reajustado'];
            ?>
            </font>
        </td>
        <td align='left'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                <?=$vetor_observacao_follow_up[$campos[$i]['id_conta_receber']];?>
            </font>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho'>
        <td colspan='7'>
            Contas Vencidas: <?=$cont_vencidas;?>
            &nbsp;&nbsp;
            Contas à Vencer: <?=$cont_vencer;?>
            &nbsp;&nbsp;
            Total: <?=$cont_vencer + $cont_vencidas;?>
        </td>
        <td colspan='6'>
<?
	$semana = data::numero_semana(date('d'), date('m'), date('Y'));
        
	$sql = "SELECT dia_inicio, dia_fim 
                FROM `semanas` 
                WHERE `semana` = '$semana' 
                AND SUBSTRING(`dia_inicio`, 1, 4) = '$ano' LIMIT 1 ";
	$campos = bancos::sql($sql);
	if(count($campos) != 0) {
            $dia_inicio = data::datetodata($campos[0]['dia_inicio'], '/');
            $dia_inicio = substr($dia_inicio, 0, 6).substr($dia_inicio, 8, 2);
            $dia_fim = data::datetodata($campos[0]['dia_fim'], '/');
            $dia_fim = substr($dia_fim, 0, 6).substr($dia_fim, 8, 2);
?>
            Semana: <?=$semana;?>
            Período: <?=$dia_inicio.' a '.$dia_fim;?>
<?
	}else {
?>
            Semana: <?=$semana;?>
<?
	}
?>
        </td>
        <td colspan='3' align='right'>
            Total: <?='R$ '.number_format($valor_receber_total, 2, ',', '.');?>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<input type="hidden" name="bloquear_pagamento" value="0">
<!--Esse Hidden é controlado pelo Pop-UP de Incluir Itens da Nota Automático-->
<input type="hidden" name="recarregar">
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