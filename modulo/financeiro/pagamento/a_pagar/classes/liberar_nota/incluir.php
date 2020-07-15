<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/financeiros.php');
require('../../../../../../lib/genericas.php');

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../../');
$mensagem[1] = "<font class='confirmacao'>CONTA À PAGAR INCLUIDA COM SUCESSO.</font>";

function percentagem_itens_nf($id_nfe) {
    //Quantidade de Itens da NF ...
    $sql = "SELECT SUM(`qtde_entregue` * `valor_entregue`) AS valor_total 
            FROM `nfe_historicos` 
            WHERE `id_nfe` = '$id_nfe' ";
    $campos_total       = bancos::sql($sql);
    $valor_total	= $campos_total[0]['valor_total'];

    $sql = "SELECT SUM(nfeh.`qtde_entregue` * nfeh.`valor_entregue`) AS valor_total_por_grupo, g.`id_grupo` 
            FROM `nfe_historicos` nfeh 
            INNER JOIN `itens_pedidos` ip ON ip.`id_item_pedido` =  nfeh.`id_item_pedido` 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ip.`id_produto_insumo` 
            INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
            WHERE nfeh.`id_nfe` = '$id_nfe' 
            GROUP BY g.`id_grupo` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
	
    //Formula para descobrir a percentagem de cada grupo de item ...
    for($i = 0; $i < $linhas; $i++) {
        //Fazer a percentagem de cada grupo ...
        $percentagem_item   = round((($campos[$i]['valor_total_por_grupo'] * 100) / $valor_total), 1);
        $id_grupos.=        $campos[$i]['id_grupo'].', ';
        $percentagens.=     $percentagem_item.', ';
    }
    $id_grupos 		= substr($id_grupos, 0, strlen($id_grupos) - 2);
    $percentagens 	= substr($percentagens, 0, strlen($percentagens) - 2);
    return array('id_grupos' => $id_grupos, 'percentagens' => $percentagens);
}

if(!empty($_POST['hdd_nfe'])) {
    //Preparando a Datas p/ poder gravar no BD ...
    $txt_data_emissao   = data::datatodate($txt_data_emissao, '-');
    //Disparo o Loop p/ as Caixinhas ...
	
    for($i = 0; $i < count($_POST['txt_valor']); $i++) {
        //Desmembro a Data p/ ver qual será o número da semana ...
        $dia 	= substr($txt_data_vencimento[$i], 0, 2);
        $mes 	= substr($txt_data_vencimento[$i], 3, 2);
        $ano 	= substr($txt_data_vencimento[$i], 6, 4);
        $semana = data::numero_semana($dia, $mes, $ano);
        //Preparando as Datas p/ poder gravar no BD ...
        $txt_data_vencimento[$i]    = data::datatodate($txt_data_vencimento[$i], '-');
        $dados_nfe                  = percentagem_itens_nf($_POST['hdd_nfe']);
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
        $cmb_importacao             = (!empty($_POST[cmb_importacao])) ? "'".$_POST[cmb_importacao]."'" : 'NULL';
        
        if($_POST['txt_valor'][$i] < 0) {//Quando o Valor da "Conta à Pagar" for Negativa então significa que é uma "Conta à Receber" ...
            $taxa_juros = 3;//3% de Juros ...
            $tipo_juros = 'C';//Composto ...
        }else {
            $taxa_juros = 0;//0% de Juros ...
            $tipo_juros = 'S';//Simples ...
        }
        $sql = "INSERT INTO `contas_apagares` (`id_conta_apagar`, `id_funcionario`, `id_fornecedor`, `id_nfe`, `id_importacao`, `id_empresa`, `id_tipo_moeda`, `id_grupo`, `perc_uso_produto_financeiro`, `numero_conta`, `semana`, `data_emissao`, `data_vencimento`, `data_vencimento_alterada`, `id_tipo_pagamento_recebimento`, `valor`, `taxa_juros`, `tipo_juros`, `status`, `ativo`) VALUES (NULL, '$_SESSION[id_funcionario]', '$_POST[id_fornecedor]', '$_POST[hdd_nfe]', $cmb_importacao, '$id_emp', '$cmb_tipo_moeda', '$dados_nfe[id_grupos]', '$dados_nfe[percentagens]', '".$_POST['hdd_conta'][$i]."', '$semana', '$txt_data_emissao', '$txt_data_vencimento[$i]', '$txt_data_vencimento[$i]', '$_POST[id_tipo_pagamento]', '".$_POST['txt_valor'][$i]."', '$taxa_juros', '$tipo_juros', '0', '1') ";
        bancos::sql($sql);
        $id_conta_apagar = bancos::id_registro();
        //Registrando Follow-UP(s) ...
        if(!empty($_POST['txt_observacao'])) {
            $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_fornecedor`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_fornecedor', '$_SESSION[id_funcionario]', '$id_conta_apagar', '18', '".strtolower($_POST['txt_observacao'])."', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
        }
        financeiros::inserir_dados_bancarios($_POST['hdd_nfe'], 1, $id_conta_apagar);
        
        financeiros::atualizar_data_alterada($id_conta_apagar, 'A');
    }
    //Mudo o Status da NF de Entrada p/ S, porque todas as vias foram cadastradas ...
    $sql = "UPDATE `nfe` SET `importado_financeiro` = 'S' WHERE `id_nfe` = '$_POST[hdd_nfe]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'Javascript'>
        //window.opener.parent.itens.document.form.recarregar.value = 1
        //window.opener.parent.itens.document.form.submit()
        //window.opener.parent.itens.document.location = '../itens.php<?=$parametro;?>'
        window.location = 'consultar_nf.php?valor=1'
    </Script>
<?
}

//Seleciona os dados do fornecedor com o id da nota fiscal
$sql = "SELECT f.razaosocial, f.id_pais, nfe.*, tp.status_db 
        FROM `nfe` 
        INNER JOIN `fornecedores` f ON f.id_fornecedor = nfe.id_fornecedor 
        INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = nfe.`id_tipo_pagamento_recebimento` 
        WHERE nfe.`id_nfe` = '$_GET[id_nfe]' LIMIT 1 ";
$campos 		= bancos::sql($sql);
$status_db 		= $campos[0]['status_db'];
$pago_pelo_caixa_compras= $campos[0]['pago_pelo_caixa_compras'];
$prazo_a 		= $campos[0]['prazo_a'];
$prazo_b 		= $campos[0]['prazo_b'];
$prazo_c 		= $campos[0]['prazo_c'];
$valor_a 		= $campos[0]['valor_a'];
$valor_b 		= $campos[0]['valor_b'];
$valor_c 		= $campos[0]['valor_c'];
$id_fornecedor          = $campos[0]['id_fornecedor'];
$id_grupo 		= $campos[0]['id_grupo'];
$id_tipo_moeda          = $campos[0]['id_tipo_moeda'];
$tipo_nota 		= $campos[0]['tipo'];
$id_tipo_pagamento      = $campos[0]['id_tipo_pagamento_recebimento'];
$id_tipo_pagamento_status = $campos[0]['id_tipo_pagamento_recebimento'].'|'.$status_db;
$razaosocial            = $campos[0]['razaosocial'];
$id_pais 		= $campos[0]['id_pais'];
$num_nota 		= $campos[0]['num_nota'];
$data_emissao           = data::datetodata($campos[0]['data_emissao'], '/');

//Aqui é somente para fornecedores que são do tipo internacional
if($id_pais != 31) {
    $sql = "SELECT i.nome 
            FROM `nfe` 
            INNER JOIN `importacoes` i ON i.id_importacao = nfe.id_importacao 
            WHERE nfe.id_nfe = '$id_nfe' ";
    $campos_importacao = bancos::sql($sql);
    //Se encontrar Importação, então eu concateno essa junto do N.º da Conta ...
    if(count($campos_importacao) == 1) $num_nota = $campos_importacao[0]['nome'].' - '.$num_nota;
}
//Aqui eu puxo o último valor do dólar e do euro cadastrado
$sql = "SELECT valor_dolar_dia, valor_euro_dia, data 
        FROM `cambios` 
        ORDER BY id_cambio DESC LIMIT 1 ";
$campos_cambios	= bancos::sql($sql);
$valor_dolar 	= $campos_cambios[0]['valor_dolar_dia'];
$valor_euro 	= $campos_cambios[0]['valor_euro_dia'];
$data_cadastro 	= data::datetodata($campos_cambios[0]['data'], '/');
?>
<html>
<head>
<title>.:: Liberar Nota de Compras ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../../../js/arred.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function separar() {
    var tipo_pagamento = document.form.cmb_tipo_pagamento.value
    var achou = 0, id_tipo_pagamento = '', status_db = ''
    for(i = 0; i < tipo_pagamento.length; i++) {
        if(tipo_pagamento.charAt(i) == '|') {
            achou = 1
        }else {
            if(achou == 0) {
                id_tipo_pagamento = id_tipo_pagamento + tipo_pagamento.charAt(i)
            }else {
                status_db = status_db + tipo_pagamento.charAt(i)
            }
        }
    }
    document.form.id_tipo_pagamento.value = id_tipo_pagamento
    document.form.status_db.value = status_db
}

function calcular() {
    var tipo_moeda = document.form.cmb_tipo_moeda.value
    var elementos = document.form.elements
//Significa que está tela foi carregada com apenas 1 linha ...
    if(typeof(elementos['txt_valor[]'][0]) == 'undefined') {
        if(tipo_moeda == 2) {//Dólar ...
            elementos['txt_valor_reajustado[]'].value = eval(strtofloat(elementos['txt_valor[]'].value)) * eval('<?=$valor_dolar;?>')
        }else if(tipo_moeda == 3) {//Euro ...
            elementos['txt_valor_reajustado[]'].value = eval(strtofloat(elementos['txt_valor[]'].value)) * eval('<?=$valor_euro;?>')
        }else {//Real ...
            elementos['txt_valor_reajustado[]'].value = eval(strtofloat(elementos['txt_valor[]'].value)) * 1
        }
        elementos['txt_valor_reajustado[]'].value = arred(elementos['txt_valor_reajustado[]'].value, 2, 1)
    }else {//Carregada com mais de 1 linha ...
        for (var i = 0; i < elementos.length; i++) {
            if(elementos['txt_valor[]'][i] == '[object HTMLInputElement]' || elementos['txt_valor[]'][i] == '[object]') {
                if(tipo_moeda == 2) {//Dólar ...
                    elementos['txt_valor_reajustado[]'][i].value = eval(strtofloat(elementos['txt_valor[]'][i].value)) * eval('<?=$valor_dolar;?>')
                }else if(tipo_moeda == 3) {//Euro ...
                    elementos['txt_valor_reajustado[]'][i].value = eval(strtofloat(elementos['txt_valor[]'][i].value)) * eval('<?=$valor_euro;?>')
                }else {//Real ...
                    elementos['txt_valor_reajustado[]'][i].value = eval(strtofloat(elementos['txt_valor[]'][i].value)) * 1
                }
                elementos['txt_valor_reajustado[]'][i].value = arred(elementos['txt_valor_reajustado[]'][i].value, 2, 1)
            }
        }
    }
}

function validar() {
//Tipo de Pagamento ...
    if(!combo('form', 'cmb_tipo_pagamento', '', 'SELECIONE UM TIPO DE PAGAMENTO !')) {
        return false
    }
//Tipo de Moeda
    if(!combo('form', 'cmb_tipo_moeda', '', 'SELECIONE O TIPO DA MOEDA !')) {
        return false
    }
    var elementos = document.form.elements
//Significa que está tela foi carregada com apenas 1 linha ...
    if(typeof(elementos['txt_valor[]'][0]) == 'undefined') {
        if(elementos['txt_data_vencimento[]'].value == '') {
            alert('DIGITE A DATA DE VENCIMENTO !')
            elementos['txt_data_vencimento[]'].focus()
            return false
        }
    }else {//Carregada com mais de 1 linha ...
        for (var i = 0; i < elementos.length; i++) {
            if(elementos['txt_valor[]'][i] == '[object HTMLInputElement]' || elementos['txt_valor[]'][i] == '[object]') {
                if(elementos['txt_data_vencimento[]'][i].value == '') {
                    alert('DIGITE A DATA DE VENCIMENTO !')
                    elementos['txt_data_vencimento[]'][i].focus()
                    return false
                }
            }
        }
    }
//Desabilito esses campos para poder gravar no BD
    document.form.cmb_tipo_pagamento.disabled   = false
    document.form.cmb_tipo_moeda.disabled       = false
//Importação
    if(typeof(document.form.cmb_importacao) == 'object') document.form.cmb_importacao.disabled = false
//Preparando os Campos do Array p/ poder gravar na Base de Dados ...
//Significa que está tela foi carregada com apenas 1 linha ...
    if(typeof(elementos['txt_valor[]'][0]) == 'undefined') {
//Preparandos os objetos ...
        elementos['txt_valor[]'].value = strtofloat(elementos['txt_valor[]'].value)
        elementos['txt_valor_reajustado[]'].value = strtofloat(elementos['txt_valor_reajustado[]'].value)
//Desabilitando os objetos ...
        elementos['txt_valor[]'].disabled = false
        elementos['txt_valor_reajustado[]'].disabled = false
    }else {//Carregada com mais de 1 linha ...
        for(var i = 0; i < elementos.length; i++) {
            if(elementos['txt_valor[]'][i] == '[object HTMLInputElement]' || elementos['txt_valor[]'][i] == '[object]') {
//Preparandos os objetos ...
                elementos['txt_valor[]'][i].value = strtofloat(elementos['txt_valor[]'][i].value)
                elementos['txt_valor_reajustado[]'][i].value = strtofloat(elementos['txt_valor_reajustado[]'][i].value)
//Desabilitando os objetos ...
                elementos['txt_valor[]'][i].disabled = false
                elementos['txt_valor_reajustado[]'][i].disabled = false
            }
        }
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP, tem um controle um pouquinho diferente
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
//Variável referente ao Frame de Baixo
    var recarregar = window.opener.parent.itens.document.form.recarregar.value
    if(recarregar == 1 && document.form.ignorar.value == 0) {
        if(typeof(window.opener.parent.itens.document.form) == 'object') window.opener.parent.itens.document.location = '../itens.php'+window.opener.parent.itens.document.form.parametro.value
    }
}
</Script>
</head>
<body onload='calcular();separar()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='hdd_nfe' value='<?=$id_nfe;?>'>
<input type='hidden' name='id_fornecedor' value='<?=$id_fornecedor;?>'>
<!--Aqui precisa por causa da função do JavaScript-->
<input type='hidden' name='id_tipo_pagamento' value='<?=$id_tipo_pagamento;?>'>
<input type='hidden' name='status_db' value='<?=$status_db;?>'>
<input type='hidden' name='ignorar' value='0'>
<!--**********************************************-->
<table border="0" width='90%' align='center' cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='5'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Liberar Nota de Compras => 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <b>Fornecedor:</b>
        </td>
        <td>
            <b>N.º da Conta / Nota:</b>
        </td>
        <td>
            <b>Data de Emissão:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3' height='21'>
            <font size='-2'>
                <?=$razaosocial;?>
            </font>
        </td>
        <td>
            <?=$num_nota;?>
        </td>
        <td>
            <?=$data_emissao;?>
            <input type="hidden" name="txt_data_emissao" value="<?=$data_emissao;?>">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'><b>Tipo de Pagamento:</b></td>
        <td colspan='2'><b>Tipo da Moeda:</b></td>
    </tr>
    <tr class='linhanormal'>
        <td colspan="3">
            <select name="cmb_tipo_pagamento" title="Tipo de Pagamento" class='textdisabled' disabled>
            <?
                $sql = "SELECT CONCAT(id_tipo_pagamento, '|', status_db) AS tipo, pagamento 
                        FROM `tipos_pagamentos` 
                        WHERE `ativo` = '1' ";
                echo combos::combo($sql, $id_tipo_pagamento_status);
            ?>
            </select>
            <?
                //Se o usuário marcou essa opção de "Caixa" no Cabeçalho de Nota Fiscal, então apresento esta linha abaixo ...
                if($pago_pelo_caixa_compras == 'S') echo '&nbsp;-&nbsp;<font color="red" size="1"><b>(PAGO PELO CAIXA DE COMPRAS)</b></font>';
            ?>
        </td>
        <td colspan="2">
            <select name="cmb_tipo_moeda" class='textdisabled' disabled>
            <?
                $sql = "SELECT id_tipo_moeda, CONCAT(simbolo, ' - ', moeda) AS moeda 
                        FROM `tipos_moedas` 
                        WHERE `ativo` = '1' ";
                echo combos::combo($sql, $id_tipo_moeda);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan="3">
            <font color="blue">
                Valor Dólar:
            </font>
            <?='R$ '.number_format($valor_dolar, 4, ',', '.');?>
        </td>
        <td colspan="2">
            <font color="blue">
                Valor Euro:
            </font>
            <?='R$ '.number_format($valor_euro, 4, ',', '.');?>
        </td>
    </tr>
<?
	//Seleção dos dados da importação com o id da nota
	$sql = "SELECT id_importacao 
                FROM `nfe` 
                WHERE `id_nfe` = '$id_nfe' 
                AND `id_importacao` > '0' LIMIT 1 ";
	$campos_nfe = bancos::sql($sql);
	if(count($campos_nfe) == 1) {
?>
    <tr class='linhanormal'>
        <td colspan='5'>
            <b>Importação:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan="5">
            <select name="cmb_importacao" title="Importação" class='textdisabled' disabled>
            <?
                    $sql = "SELECT id_importacao, nome 
                            FROM `importacoes` 
                            WHERE `ativo` = '1' ORDER BY nome ";
                    echo combos::combo($sql, $campos_nfe[0]['id_importacao']);
            ?>
            </select>
        </td>
    </tr>
<?
	}
?>
    <tr class="linhadestaque" align='center'>
        <td>
            N.º da Duplicata
        </td>
        <td>
            Valor Nac / Est
        </td>
        <td>
            Valor Reajustado
        </td>
        <td>
            Dias
        </td>
        <td>
            Data de Vencimento
        </td>
    </tr>
<?
/****************************************************************************************************/
/*************************************** Financiamento  *********************************************/
/****************************************************************************************************/
//Aqui eu busco todas as Parcelas do Financiamento da NF que foi através do Pedido ...
	$sql = "SELECT nf.*, tm.simbolo 
                FROM `nfe_financiamentos` nf 
                INNER JOIN `nfe` ON nfe.id_nfe = nf.id_nfe 
                INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = nfe.id_tipo_moeda 
                WHERE nf.`id_nfe` = '$id_nfe' ORDER BY nf.dias ";
	$campos_financiamento = bancos::sql($sql);
	$linhas_financiamento = count($campos_financiamento);
	if($linhas_financiamento > 0) {//Encontrou pelo menos 1 Financiamento ...
            for($i = 0; $i < $linhas_financiamento; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$num_nota.' - '.($i + 1).'/'.$linhas_financiamento;?>
        </td>
        <td>
            <input type='text' name="txt_valor[]" value="<?=number_format($campos_financiamento[$i]['valor_parcela_nf'], '2', ',', '');?>" title="Valor Nacional / Estrangeiro" size="12" maxlength="11" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name="txt_valor_reajustado[]" title="Valor Reajustado" size="12" maxlength="11" class='textdisabled' disabled> em Reais
        </td>
        <td>
            <input type='text' name="txt_dias[]" value="<?=$campos_financiamento[$i]['dias'];?>" title="Dias" size="7" maxlength="6" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name="txt_data_vencimento[]" value="<?=data::datetodata($campos_financiamento[$i]['data'], '/');?>" title="Digite a Data de Vencimento" onkeyup="verifica(this, 'data', '', '', event)" size="11" maxlength="10" class='caixadetexto'>
            &nbsp;<img src="../../../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../../../calendario/calendario.php?campo=txt_data_vencimento&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
            <input type="hidden" name="hdd_conta[]" value="<?=$num_nota.' - '.($i + 1).'/'.$linhas_financiamento;?>">
        </td>
    </tr>
<?
            }
	}else {//Significa que não foi feito nenhum Financiamento p/ esta NF ...
            $data_venc_a = data::adicionar_data_hora(data::datetodata(data::datatodate($data_emissao, '-'), '/'), $prazo_a);
            $a = 1;//Mostra a Duplicata A ...

//Aqui eu verifico se existe o prazo b
            if($prazo_b != '' && $prazo_b > 0) {
                $data_venc_b = data::adicionar_data_hora(data::datetodata(data::datatodate($data_emissao, '-'), '/'), $prazo_b);
                $b = 1;//Mostra a Duplicata B ...
            }

//Aqui eu verifico se existe o prazo c
            if($prazo_c != '' && $prazo_c > 0) {
                $data_venc_c = data::adicionar_data_hora(data::datetodata(data::datatodate($data_emissao, '-'), '/'), $prazo_c);
                $c = 1;//Mostra a Duplicata C ...
            }
            $total_vias = $a + $b + $c;//Essa variável vai servir p/ gerar as letras ...

            if(isset($a)) {
                if($total_vias > 1) {//Significa que existe pelo menos 2 Vias ...
                    $rotulo = ' - 1/'.$total_vias;
                }
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$num_nota.$rotulo;?>
        </td>
        <td>
            <input type='text' name="txt_valor[]" value="<?=number_format($valor_a, '2', ',', '');?>" title="Valor Nacional / Estrangeiro" size="12" maxlength="11" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name="txt_valor_reajustado[]" title="Valor Reajustado" size="12" maxlength="11" class='textdisabled' disabled> em Reais
        </td>
        <td>
            <input type='text' name="txt_dias[]" value="<?=$prazo_a;?>" title="Dias" size="7" maxlength="6" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name="txt_data_vencimento[]" value="<?=$data_venc_a;?>" title="Digite a Data de Vencimento" onkeyup="verifica(this, 'data', '', '', event)" size="11" maxlength="10" class='caixadetexto'>
            &nbsp;<img src="../../../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../../../calendario/calendario.php?campo=txt_data_vencimento&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
            <input type="hidden" name="hdd_conta[]" value="<?=$num_nota.$rotulo;?>">
        </td>
    </tr>
<?
            }

            if(isset($b)) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$num_nota.' - 2/'.$total_vias;?>
        </td>
        <td>
            <input type='text' name="txt_valor[]" value="<?=number_format($valor_b, '2', ',', '');?>" title="Valor Nacional / Estrangeiro" size="12" maxlength="11" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name="txt_valor_reajustado[]" title="Valor Reajustado" size="12" maxlength="11" class='textdisabled' disabled> em Reais
        </td>
        <td>
            <input type='text' name="txt_dias[]" value="<?=$prazo_b;?>" title="Dias" size="7" maxlength="6" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name="txt_data_vencimento[]" value="<?=$data_venc_b;?>" title="Digite a Data de Vencimento" onkeyup="verifica(this, 'data', '', '', event)" size="11" maxlength="10" class='caixadetexto'>
            &nbsp;<img src="../../../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../../../../calendario/calendario.php?campo=txt_data_vencimento&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
            <input type="hidden" name="hdd_conta[]" value="<?=$num_nota.' - 2/'.$total_vias;?>">
        </td>
    </tr>
<?
            }

            if(isset($c)) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$num_nota.' - 3/'.$total_vias;?>
        </td>
        <td>
            <input type='text' name="txt_valor[]" value="<?=number_format($valor_c, '2', ',', '');?>" title="Valor Nacional / Estrangeiro" size="12" maxlength="11" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name="txt_valor_reajustado[]" title="Valor Reajustado" size="12" maxlength="11" class='textdisabled' disabled> em Reais
        </td>
        <td>
            <input type='text' name="txt_dias[]" value="<?=$prazo_c;?>" title="Dias" size="7" maxlength="6" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name="txt_data_vencimento[]" value="<?=$data_venc_c;?>" title="Digite a Data de Vencimento" onkeyup="verifica(this, 'data', '', '', event)" size="11" maxlength="10" class='caixadetexto'>
            &nbsp;<img src="../../../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../../../../calendario/calendario.php?campo=txt_data_vencimento&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
            <input type="hidden" name="hdd_conta[]" value="<?=$num_nota.' - 3/'.$total_vias;?>">
        </td>
    </tr>
<?
            }
	}
?>
    <tr class='linhanormal'>
        <td colspan='5'>
            Observação:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='5'>
            <?
                /*Verifico se existe uma Observação na NFE que está sendo importada com a Marcação 
                "Exibir no PDF" ...*/
                $sql = "SELECT `observacao` 
                        FROM `follow_ups` 
                        WHERE `identificacao` = '$id_nfe' 
                        AND `origem` = '17' 
                        AND `exibir_no_pdf` = 'S' LIMIT 1 ";
                $campos_follow_up = bancos::sql($sql);
            ?>
            <textarea name='txt_observacao' title='Digite a Observação' rows='5' cols='100' maxlength='500' class='caixadetexto'><?=$campos_follow_up[0]['observacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='button' name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'consultar_nf.php<?=$parametro;?>'" class='botao'>
            <input type='button' name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form','REDEFINIR');calcular();separar()" style="color:#ff9900;" class='botao'>
            <input type='submit' name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" <?=$disabled;?> class='botao'>
            <input type='button' name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="fechar(window)" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>