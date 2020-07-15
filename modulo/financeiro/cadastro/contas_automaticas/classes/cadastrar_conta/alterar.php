<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/financeiros.php');
require('../../../../../../lib/genericas.php');

//Significa que essa Tela foi aberta do modo Normal, não como "Pop-UP" ...
if(empty($_GET['pop_up']))  {
    if($id_empresa_menu == 1) {
        $endereco = '/erp/albafer/modulo/financeiro/cadastro/contas_automaticas/albafer/index.php';
    }else if($id_empresa_menu == 2) {
        $endereco = '/erp/albafer/modulo/financeiro/cadastro/contas_automaticas/tool_master/index.php';
    }else if($id_empresa_menu == 4) {
        $endereco = '/erp/albafer/modulo/financeiro/cadastro/contas_automaticas/grupo/index.php';
    }
    segurancas::geral($endereco, '../../../../../../');
}else {
    session_start('funcionarios');
}

$mensagem[1] = "<font class='confirmacao'>CONTA À PAGAR AUTOMÁTICA ALTERADA COM SUCESSO.</font>";

if(!empty($_POST['id_conta_apagar_automatica'])) {
    $data_proximo_vencimento    = data::datatodate($_POST['txt_data_proximo_vencimento'], '-');
    $previsao                   = (!empty($_POST['chkt_previsao'])) ? 1 : 0;  
//Nesse caso a programação é ao contrário mesmo porque esse checkbox está relacionado ao Inativar Conta ...
    $conta_ativa                = (!empty($_POST['chkt_conta_ativa'])) ? 'N' : 'S';

//Essa variável será utilizada mais abaixo ...
    $id_produto_financeiro_vs_fornecedor = $_POST[cmb_produto_financeiro];
    
/*Seleção dos dados bancários do fornecedor p/ gravar na tabela de "contas_apagares_automaticas" 
e ficar mais fácil no Futuro a busca de dados ...*/
    $sql = "SELECT banco, agencia, num_cc, correntista, cnpj_cpf 
            FROM `fornecedores_propriedades` 
            WHERE `id_fornecedor_propriedade` = '$_POST[cmb_conta_corrente]' LIMIT 1 ";
    $campos = bancos::sql($sql);

//Atualizo a Contas Automática passada por parâmetro ...
    $sql = "UPDATE `contas_apagares_automaticas` SET `id_tipo_pagamento_recebimento` = '$_POST[id_tipo_pagamento]', `id_funcionario` = '$_SESSION[id_funcionario]', `id_tipo_moeda` = '$_POST[cmb_tipo_moeda]', `id_produto_financeiro_vs_fornecedor` = '$id_produto_financeiro_vs_fornecedor', `numero_conta` = '$_POST[txt_numero_conta]', `banco` = '".$campos[0]['banco']."', `agencia` = '".$campos[0]['agencia']."', `num_cc` = '".$campos[0]['num_cc']."', `correntista` = '".$campos[0]['correntista']."', cnpj_cpf = '".$campos[0]['cnpj_cpf']."', `dia_exibicao` = '$_POST[txt_dia_exibicao]', `tipo_data` = '$_POST[cmb_tipo_data]', `intervalo` = '$_POST[txt_intervalo]', `previsao` = '$chkt_previsao', `data_vencimento` = '$data_proximo_vencimento', `valor` = '$_POST[txt_valor]', `valor_reajustado` = '$_POST[txt_valor_reajustado]', `observacao` = '".strtolower($txt_observacao)."', `conta_ativa` = '$conta_ativa', `status` = '$_POST[cmb_tipo_automacao]', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_conta_apagar_automatica` = '$_POST[id_conta_apagar_automatica]' LIMIT 1 ";
    bancos::sql($sql);

//Esse id_produto_financeiro, será utilizado no UPDATE mais abaixo ...
    $sql = "SELECT id_produto_financeiro 
            FROM `produtos_financeiros_vs_fornecedor` 
            WHERE `id_produto_financeiro_vs_fornecedor` = '$id_produto_financeiro_vs_fornecedor' LIMIT 1 ";
    $campos_produto_financeiro = bancos::sql($sql);

//Atualizo todas as Contas a Pagar "Em Aberto" que foram geradas através desse "$_POST[id_conta_apagar_automatica]" ...
    $sql = "UPDATE `contas_apagares` SET `id_produto_financeiro` = '".$campos_produto_financeiro[0]['id_produto_financeiro']."', `numero_conta` = '$_POST[txt_numero_conta]', `valor` = '$_POST[txt_valor]' WHERE `id_conta_apagar_automatica` = '$_POST[id_conta_apagar_automatica]' AND `status` = '0' ";
    bancos::sql($sql);
    $valor = 1;
    
//Atualizo a Observação das Contas à Pagar "Em Aberto" que foram geradas através desse "$_POST[id_conta_apagar_automatica]" ...
    if(!empty($_POST['txt_observacao'])) {
        $sql = "SELECT `id_conta_apagar`, `id_fornecedor` 
                FROM `contas_apagares` 
                WHERE `id_conta_apagar_automatica` = '$_POST[id_conta_apagar_automatica]' 
                AND `status` = '0' ";
        $campos_contas_apagar = bancos::sql($sql);
        $linhas_contas_apagar = count($campos_contas_apagar);
        for($i = 0; $i < $linhas_contas_apagar; $i++) {
            $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_fornecedor`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '".$campos_contas_apagar[$i]['id_fornecedor']."', '$_SESSION[id_funcionario]', '".$campos_contas_apagar[$i]['id_conta_apagar']."', '18', '$_POST[txt_observacao]', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
        }
    }
?>
    <Script Language = 'JavaScript'>
        window.top.opener.parent.itens.document.location    = window.top.opener.parent.itens.document.location.href
        window.top.opener.parent.rodape.document.location   = '../rodape.php?id_empresa_menu=<?=$id_empresa_menu;?>'
    </Script>
<?
}

//Seleção dos dados de contas à pagar automática
$sql = "SELECT caa.*, f.`id_fornecedor`, f.`razaosocial`, func.`nome`, tp.`status_db` 
        FROM `contas_apagares_automaticas` caa 
        INNER JOIN `funcionarios` func ON func.`id_funcionario` = caa.`id_funcionario` 
        INNER JOIN `produtos_financeiros_vs_fornecedor` pff ON pff.`id_produto_financeiro_vs_fornecedor` = caa.`id_produto_financeiro_vs_fornecedor` 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = pff.`id_fornecedor` 
        INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = caa.`id_tipo_pagamento_recebimento` 
        WHERE caa.`id_conta_apagar_automatica` = '$id_conta_apagar_automatica' LIMIT 1 ";
$campos                                 = bancos::sql($sql);
$status_db                              = $campos[0]['status_db'];
$id_tipo_pagamento                      = $campos[0]['id_tipo_pagamento_recebimento'];
$id_tipo_pagamento_status               = $campos[0]['id_tipo_pagamento_recebimento'].'|'.$status_db;
$id_tipo_moeda                          = $campos[0]['id_tipo_moeda'];
$id_produto_financeiro_vs_fornecedor    = $campos[0]['id_produto_financeiro_vs_fornecedor'];
$id_fornecedor                          = $campos[0]['id_fornecedor'];
$dia_exibicao                           = $campos[0]['dia_exibicao'];
$intervalo                              = $campos[0]['intervalo'];
if($intervalo == 0) {
    $intervalo      = '';
    $desabilitar    = 'disabled';
}
$tipo_automacao                         = $campos[0]['status'];
$data_proximo_vencimento                = data::datetodata($campos[0]['data_vencimento'], '/');
$data_sys_funcionario                   = data::datetodata(substr($campos[0]['data_sys'], 0, 10), '/').' '.substr($campos[0]['data_sys'], 11, 8).' ('.$campos[0]['nome'].')';
$tipo_data                              = $campos[0]['tipo_data'];

/*Aqui eu verifico qual que é o id do dado bancário que foi utilizado pelo fornecedor na conta a pagar e 
trazer selecionado na combo ...*/
$sql = "SELECT id_fornecedor_propriedade 
        FROM `fornecedores_propriedades` 
        WHERE `id_fornecedor` = '".$campos[0]['id_fornecedor']."' 
        AND `banco` = '".$campos[0]['banco']."' 
        AND `agencia` = '".$campos[0]['agencia']."' 
        AND `num_cc` = '".$campos[0]['num_cc']."' LIMIT 1 ";
$campos_fornecedore_propriedade = bancos::sql($sql);
$id_fornecedor_propriedade      = $campos[0]['id_fornecedor_propriedade'];

//Aqui eu puxo o último valor do dólar e do euro cadastrado ...
$valor_dolar            = genericas::moeda_dia('dolar');
$valor_euro             = genericas::moeda_dia('euro');

//Essa data será utilizada como tolerância máxima p/ cadastrar Datas de Emissão Retroativas ...
$data_atual_menos_365   = data::adicionar_data_hora(date('d/m/Y'), -365);
$data_atual_menos_365   = data::datatodate($data_atual_menos_365, '');
?>
<html>
<head>
<title>.:: Alterar Conta à Pagar Automática ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../../../js/arred.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../../js/tabela.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function desabilitar() {
    if(document.form.cmb_tipo_data.value == 0) {
        document.form.txt_intervalo.disabled    = true
        document.form.txt_intervalo.value       = ''
    }else if(document.form.cmb_tipo_data.value == 1) {
        document.form.txt_intervalo.disabled    = false
        document.form.txt_intervalo.focus()
    }
}

function tipo_automacao() {
    if(document.form.cmb_tipo_automacao.value == 1) {
        document.form.txt_dia_exibicao.disabled = true
        document.form.txt_dia_exibicao.value    = ''
    }else {
        document.form.txt_dia_exibicao.disabled = false
        document.form.txt_dia_exibicao.focus()
    }
}

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
    if(document.form.status_db.value == 1) {
        document.form.cmb_conta_corrente.disabled = false
    }else {
        document.form.cmb_conta_corrente.disabled = true
    }
}

function calcular() {
    var tipo_moeda  = document.form.cmb_tipo_moeda.value
    var valor       = eval(strtofloat(document.form.txt_valor.value))
    if(tipo_moeda == 2) {//Calculando o Dólar ...
        document.form.txt_valor_reajustado.value = valor * eval('<?=$valor_dolar;?>')
    }else if(tipo_moeda == 3) {//Calculando o Euro ...
        document.form.txt_valor_reajustado.value = valor * eval('<?=$valor_euro;?>')
    }else {
        document.form.txt_valor_reajustado.value = valor
    }
    document.form.txt_valor_reajustado.value = arred(document.form.txt_valor_reajustado.value, 2, 1)
}

function validar() {
//Produto Financeiro
    if(!combo('form', 'cmb_produto_financeiro', '', 'SELECIONE UM PRODUTO FINANCEIRO !')) {
        return false
    }
//Conta
    if(document.form.txt_numero_conta.value != '') {
        if(!texto('form', 'txt_numero_conta', '1', '1234567890QWERTYUIOPÇLKJHGFDSAZXCVBNM zaqwsxcderfvbgtyhnmjuiklopç,.-/()', 'CONTA / NOTA', '1')) {
            return false
        }
    }
//Tipo de Pagamento
    if(!combo('form', 'cmb_tipo_pagamento', '', 'SELECIONE UM TIPO DE PAGAMENTO !')) {
        return false
    }
//Conta Corrente
    if(document.form.cmb_conta_corrente.disabled == false) {
        if(document.form.cmb_conta_corrente.value == '') {
            alert('SELECIONE A CONTA CORRENTE !')
            document.form.cmb_conta_corrente.focus()
            return false
        }
    }
//Tipo de Moeda
    if(!combo('form', 'cmb_tipo_moeda', '', 'SELECIONE O TIPO DA MOEDA !')) {
        return false
    }
//Valor
    if(!texto('form', 'txt_valor', '1', '1234567890,.', 'VALOR', '2')) {
        return false
    }
//Tipo de Data
    if(!combo('form', 'cmb_tipo_data', '', 'SELECIONE UM TIPO DE DATA !')) {
        return false
    }
//Intervalo
    if(document.form.txt_intervalo.disabled == false) {
        if(!texto('form', 'txt_intervalo', '1', '1234567890', 'INTERVALO', '2')) {
            return false
        }
    }
//Dia de Exibição
    if(document.form.txt_dia_exibicao.disabled == false) {
        if(!texto('form', 'txt_dia_exibicao', '1', '1234567890', 'DIA DE EXIBIÇÃO', '2')) {
            return false
        }
    }
//Data de Vencimento
    if(!data('form', 'txt_data_proximo_vencimento', '4000', 'PRÓXIMO VENCIMENTO')) {
        return false
    }
//Desabilito as caixas para poder gravar no Bd ...
    document.form.txt_valor_reajustado.disabled = false
    document.form.txt_dia_exibicao.disabled     = false
//Tratamento com os campos de Moeda
    return limpeza_moeda('form', 'txt_valor, txt_valor_reajustado, ')
}
</Script>
</head>
<body onload='calcular();separar();tipo_automacao();desabilitar()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_conta_apagar_automatica' value='<?=$id_conta_apagar_automatica;?>'>
<input type='hidden' name='id_empresa_menu' value='<?=$id_empresa_menu;?>'>
<!--Aqui precisa por causa da função do JavaScript-->
<input type='hidden' name='id_tipo_pagamento' value='<?=$id_tipo_pagamento;?>'>
<input type='hidden' name='status_db' value='<?=$status_db;?>'>
<!--**********************************************-->
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr> 
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Conta à Pagar Automática
            <font color='yellow'>
                <?=genericas::nome_empresa($id_empresa_menu);?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td width='50%'>
            <?
                $checked = ($campos[0]['previsao'] == 1) ? 'checked' : '';
            ?>
            <input type='checkbox' name='chkt_previsao' value='1' id='label1' <?=$checked;?> class='checkbox'>
            <label for='label1'>Previsão</label>
        </td>
        <td width='50%'>
            <?
                /*Nesse caso a programação é ao contrário mesmo porque esse checkbox está relacionado 
                ao Inativar Conta, Conta Ativa = 'N', significa que esta conta está inativa ...*/
                $checked = ($campos[0]['conta_ativa'] == 'N') ? 'checked' : '';
            ?>
            <input type='checkbox' name='chkt_conta_ativa' value='N' id='label2' <?=$checked;?> class='checkbox'>
            <label for='label2'>Inativar Conta</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Fornecedor:</b>
        </td>
        <td>
            N.º da Conta / Nota:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font size='-2'>
                <?=$campos[0]['razaosocial'];?>
            </font>
        </td>
        <td>
            <input type='text' name='txt_numero_conta' value='<?=$campos[0]['numero_conta'];?>' title='Digite o N.º da Conta / Nota' size='42' maxlength='40' class='caixadetexto'>
            <img src = '../../../../../../imagem/bloco_negro.gif' width='8' height='8' border='0'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Produto(s) Financeiro(s):</b>
        </td>
        <td colspan='2'>
            Qtde de Parcelas:
        </td>	
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_produto_financeiro' title='Selecione um Produto Financeiro' class='combo'>
            <?
                $sql = "SELECT pfv.id_produto_financeiro_vs_fornecedor, CONCAT(g.referencia, ' - ', pf.discriminacao) AS produto_financeiro 
                        FROM `produtos_financeiros_vs_fornecedor` pfv 
                        INNER JOIN `produtos_financeiros` pf ON pf.`id_produto_financeiro` = pfv.`id_produto_financeiro` 
                        INNER JOIN `grupos` g ON g.`id_grupo` = pf.`id_grupo` 
                        WHERE pfv.id_fornecedor = '$id_fornecedor' ORDER BY produto_financeiro ";
                echo combos::combo($sql, $id_produto_financeiro_vs_fornecedor);
            ?>
            </select>
            <img src = '../../../../../../imagem/bloco_negro.gif' width='8' height='8' border='0'>
        </td>
        <td colspan='2'>
            <input type='text' name='txt_qtde_parcelas' value='<?=$campos[0]['qtde_parcelas']?>' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo Pagamento:</b>
        </td>
        <td>
            Conta Corrente:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_tipo_pagamento' title='Selecione o Tipo de Pagamento' onchange='separar()' class='combo'>
            <?
                $sql = "SELECT CONCAT(`id_tipo_pagamento`, '|', status_db) AS tipo_pagamento_status, pagamento 
                        FROM `tipos_pagamentos` 
                        WHERE `ativo` = '1' ORDER BY pagamento ";
                echo combos::combo($sql, $id_tipo_pagamento_status);
            ?>
            </select>
        </td>
        <td>
            <select name='cmb_conta_corrente' title='Selecione a Conta Corrente' class='combo'>
            <?
                $sql = "SELECT id_fornecedor_propriedade, CONCAT(num_cc, '|', agencia, '|', banco) AS conta_corrente 
                        FROM `fornecedores_propriedades` 
                        WHERE `id_fornecedor` = '$id_fornecedor' ";
                echo combos::combo($sql, $id_fornecedor_propriedade);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo da Moeda:</b>
        </td>
        <td>
            <font color='blue'>
                Valor Dólar - Valor Euro:
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_tipo_moeda' title='Selecione o Tipo da Moeda' onchange='return calcular()' class='combo'>
            <?
                $sql = "SELECT id_tipo_moeda, CONCAT(simbolo, ' - ', moeda) AS simbolo_moeda 
                        FROM `tipos_moedas` 
                        WHERE `ativo` = '1' ";
                echo combos::combo($sql, $id_tipo_moeda);
            ?>
            </select>
        </td>
        <td>
            <?='U$ '.number_format($valor_dolar, 4, ',', '.').''.'&nbsp;&nbsp;&nbsp;'.'&euro; '.number_format($valor_euro, 4, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor Nacional / Estrangeiro:</b>
        </td>
        <td>
            Valor Reajustado:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_valor' value='<?=number_format($campos[0]['valor'], '2', ',', '');?>' title='Digite o Valor Nacional / Estrangeiro' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" size='20' maxlength='15' class='caixadetexto'>
            <img src = '../../../../../../imagem/bloco_negro.gif' width='8' height='8' border='0'>
        </td>
        <td>
            <input type='text' name='txt_valor_reajustado' title='Digite o Valor Reajustado' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='20' maxlength='15' class='textdisabled' disabled> em Reais
            <img src = '../../../../../../imagem/bloco_negro.gif' width='8' height='8' border='0'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Data:</b>
        </td>
        <td>
            <b>Gerar a cada X dias (Intervalo):</b>
            <!--No "Gerar a cada X dias", este campo é usado p/ calcular a Data do Próximo Vencimento após 
            gerar se uma Nova Conta à Pagar.
            
            Exemplo: 22/10/2014 (Data Próximo Vencimento) + 7 (Gerar a cada X dias) = 29/10/2014 
            (Nova Data Próximo Vencimento) ...-->
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_tipo_data' title='Selecione o Tipo de Data' onchange='desabilitar()' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    if($tipo_data == 0) {
                        $selected_data0 = 'selected';
                    }else if($tipo_data == 1) {
                        $selected_data1 = 'selected';
                    }
                ?>
                <option value='0' <?=$selected_data0;?>>Fixa</option>
                <option value='1' <?=$selected_data1;?>>Intervalo</option>
            </select>
        </td>
        <td>
            <input type='text' name="txt_intervalo" value="<?=$intervalo;?>" title="Digite o Intervalo" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size="5" maxlength="15" <?=$desabilitar;?> class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Automação:</b>
        </td>
        <td>
            <b>Gerar Y dias antes do Vencimento (Dias de Exibição):</b>
            <!--No "Gerar Y dias antes do Vencimento", este campo é usado na função de "Contas Automáticas" que 
            está dentro da Biblioteca do Financeiro, onde Nova Conta à Pagar será gerada no dia 
            (Data Próximo Vencimento - Gerar X dias antes do Vencimento.

            Exemplo: 22/10/2014 (Data Próximo Vencimento) - 7 (Gerar Y dias antes do Vencimento) = 15/10/2014 
            (Nova Data Próximo Vencimento) ...-->
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_tipo_automacao' title='Selecione o Tipo de Automação' onchange='tipo_automacao()' class='combo'>
            <option value='' style='color:red'>SELECIONE</option>
            <?
                if($tipo_automacao == 0) {
                    $selected_automacao0 = 'selected';
                }else if($tipo_automacao == 1) {
                    $selected_automacao1 = 'selected';
                }else if($tipo_automacao == 2) {
                    $selected_automacao2 = 'selected';
                }
            ?>
                <!--<option value='0' <?=$selected_automacao0;?>>POR DATA</option>
                <option value='1' <?=$selected_automacao1;?>>PAGO A CONTA ANTERIOR</option>-->
                <option value='2' <?=$selected_automacao2;?>>AMBAS ACIMA</option>
            </select>
        </td>
        <td>
            <input type='text' name='txt_dia_exibicao' value='<?=$dia_exibicao;?>' title='Digite o Dia Exibição' size='5' maxlength='5' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data do Próximo Vencimento:</b>
        </td>
        <td>
            <b>Data Sys / Funcionário:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_data_proximo_vencimento' value="<?=$data_proximo_vencimento;?>" title='Digite a Data do Próximo Vencimento' onkeyup="verifica(this, 'data', '', '', event)" size='20' maxlength='10' class='caixadetexto'>
            &nbsp;<img src = '../../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../../calendario/calendario.php?campo=txt_data_proximo_vencimento&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
        <td>
            <?=$data_sys_funcionario;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            Observação:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <textarea name='txt_observacao' title='Digite a Observação' rows='3' cols='85' maxlength='255' class='caixadetexto'><?=$campos[0]['observacao'];?></textarea>
            <img src = '../../../../../../imagem/bloco_negro.gif' width='8' height='8' border='0'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form','REDEFINIR');calcular();separar()" style='color:#ff9900' class='botao'>
            <?
                /*Somente os usuários Roberto 62, Sandra 66 e Dárcio 98 porque programa, podem estar 
                alterando os dados de cadastro dessa Conta Automática desde que a mesma não seja Contrato ...*/
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 66 || $_SESSION['id_funcionario'] == 98 && $campos[0]['qtde_parcelas'] == 0) {
            ?>
            <input type='submit' name="cmd_salvar" value="Salvar" title="Salvar" style='color:green' class='botao'>
            <?
                }
            ?>
            <input type='button' name="cmd_fechar" value="Fechar" title="Fechar" onclick="fechar(window)" style='color:red' class='botao'>
        </td>
    </tr>
<?
    if($campos[0]['conta_ativa'] == 'N') {//Informativo p/ lembrar o usuário se essa Conta Automática for contrato ...
?>
    <tr class='erro' align='center'>
        <td colspan='2'>
            <p><p>ESSA CONTA AUTOMÁTICA É UM CONTRATO !!!
        </td>
    </tr>
<?
    }
?>
</table>
</form>
<?
/************************Visualização das Contas à Pagar************************/
//Aqui eu zero a variável para não dar conflito com a variável lá de cima
    $valor_pagar = 0;
//Visualizando as Contas à Pagar
    $retorno    = financeiros::contas_em_aberto('', '', '', '', $id_conta_apagar_automatica);
    $linhas     = count($retorno['id_contas']);
//Se encontrou uma Conta à Pagar pelo menos
    if($linhas > 0) {
?>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr>
        <td></td>
    </tr>
    <tr class='iframe' onclick="showHide('detalhes1'); return false">
        <td height="22" align="left" colspan="2">
            <font color="yellow" size="2">(<?=$linhas;?>) </font>
            Contas à Pagar do Fornecedor:
            <font color="#FFFFFF" size="2"><?=$fornecedor;?></font>
            <font color="yellow" size="2"> - Valor Total:</font>
            <?
                for($i = 0; $i < $linhas; $i++) {
                    $sql = "SELECT ca.*, concat(tm.simbolo, '&nbsp;') as simbolo 
                            FROM `contas_apagares` ca 
                            INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = ca.`id_tipo_moeda` 
                            WHERE ca.`id_conta_apagar` = ".$retorno['id_contas'][$i]." LIMIT 1 ";
                    $campos         = bancos::sql($sql);
//Essa variável iguala o tipo de moeda da conta à pagar
                    $moeda          = $campos[0]['simbolo'];
                    $valor_pagar    = $campos[0]['valor'] - $campos[0]['valor_pago'];
                    if($campos[0]['predatado'] == 1) {
//Está parte é o script q exibirá o valor da conta quando o cheque for pré-datado ...
                        $sql = "SELECT SUM(caq.valor) valor 
                                FROM `contas_apagares_quitacoes` caq 
                                INNER JOIN `cheques` c ON c.`id_cheque` = caq.`id_cheque` AND c.`status` IN (1, 2) AND c.`predatado` = '1' 
                                WHERE caq.`id_conta_apagar` = '".$retorno['id_contas'][$i]."' ";
                        $campos_pagamento   = bancos::sql($sql);
                        $valor_conta        = $campos_pagamento[0]['valor'];
                        $valor_pagar+= $valor_conta;
                    }
                    if($campos[0]['id_tipo_moeda'] == 2) {//Dólar
                        $valor_pagar*= $valor_dolar;
                    }else if($campos[0]['id_tipo_moeda'] == 3) {//Euro
                        $valor_pagar*= $valor_euro;
                    }
                    $valor_pagar_total+= $valor_pagar;
                }
            ?>
            <font color="#FFFFFF" size="2"><?=number_format($valor_pagar_total, 2, ',', '.');?></font>
            &nbsp;
            <span id='statusdados_fornecedor'>&nbsp;</span>
            <span id='statusdados_fornecedor'>&nbsp;</span>
        </td>
    </tr>
    <tr>
        <td colspan='2'>
<!--Passo o id_fornecedor por parâmetro porque utilizo dentro da Função de Apagar-->
            <iframe src = '../../../../../classes/cliente/debitos_pagar.php?id_conta_apagar_automatica=<?=$id_conta_apagar_automatica;?>&ignorar_sessao=1' name='detalhes1' id='detalhes1' marginwidth='0' marginheight='0' style='display:none' frameborder='0' height='126' width='100%' scrolling='auto'></iframe>
        </td>
    </tr>
</table>
<?
    }
?>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>

<img src = '../../../../../../imagem/bloco_negro.gif' width='8' height='8' border='0'> Esses campos se alterados, também modifica(m) a(s) Conta à Pagar "Em Aberto" que fora(m) gerada(s) através 
dessa Conta Automática.
</pre>