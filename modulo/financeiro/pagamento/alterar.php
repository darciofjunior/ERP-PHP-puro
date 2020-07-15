<?
require('../../../lib/segurancas.php');
require('../../../lib/comunicacao.php');
require('../../../lib/data.php');
require('../../../lib/financeiros.php');
require('../../../lib/genericas.php');
require('../../../lib/variaveis/intermodular.php');
session_start('funcionarios');

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/grupo/index.php';
}
segurancas::geral($endereco, '../../../');

$mensagem[1] = "<font class='atencao'>SOMENTE ALGUNS CAMPOS FOI(RAM) ALTERADO(S) DEVIDO EXISTIR(EM) PAGAMENTO(S).</font>";
$mensagem[2] = "<font class='confirmacao'>CONTA À PAGAR ALTERADA COM SUCESSO.</font>";

if(!empty($_POST['id_conta_apagar'])) {
//1)
/************************Busca de Dados************************/
//Aqui eu trago alguns dados de Conta à Pagar p/ passar por e-mail via parâmetro ...
    $sql = "SELECT DATE_FORMAT(ca.`data_vencimento_alterada`, '%d/%m/%Y') AS data_venc_antiga, nfe.`id_empresa`, nfe.`num_nota`, nfe.`tipo`, 
            f.`razaosocial` 
            FROM `contas_apagares` ca 
            INNER JOIN `nfe` ON nfe.`id_nfe` = ca.`id_nfe` 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = nfe.`id_fornecedor` 
            WHERE ca.`id_conta_apagar` = '$_POST[id_conta_apagar]' LIMIT 1 ";
    $campos_nf                  = bancos::sql($sql);
    $data_venc_antiga           = $campos_nf[0]['data_venc_antiga'];
    //Coloquei esse nome na variável porque na sessão já existe uma variável com o nome de id_empresa ...
    $id_empresa_nf              = $campos_nf[0]['id_empresa'];
    $empresa                    = genericas::nome_empresa($id_empresa_nf);
    $num_nota                   = $campos_nf[0]['num_nota'];
    $tipo_nf                    = ($campos_nf[0]['tipo'] == 1) ? 'NF' : 'SGD';//Verifica o Tipo de Nota ...
    $fornecedor                 = $campos_nf[0]['razaosocial'];
/*Se tiver a Data de Vencimento for alterada, então precisa ser modificada a Justificativa ou 
for modificado o Valor Reajustado da Conta à Pagar ...*/
    if(!empty($_POST['hdd_justificativa'])) {
/************************E-mail************************/
/*
//-Se o Usuário estiver alterando a Conta à Pagar de Compras, então o Sistema dispara um e-mail informando 
qual a Conta à Pagar que está sendo alterada ...
//-Aqui eu trago alguns dados de Conta à Pagar p/ passar por e-mail via parâmetro ...
//-Aqui eu busco o login de quem está alterando a Conta à Pagar ...*/
        $sql = "SELECT login 
                FROM `logins` 
                WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
        $campos_login 		= bancos::sql($sql);
        $login_alterando 	= $campos_login[0]['login'];
        //Verifica se a data de Vencimento foi alterada ...
        if($data_venc_antiga != $_POST['txt_data_vencimento']) {
            if($_SESSION['id_login'] != 29) {//Só não irá enviar esse e-mail quando for a própria da Dona Sandra q estiver fazendo essa ação ...
//2)
/************************Enviando E-mail************************/
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
                $complemento_justificativa  = '<br><b>Empresa: </b>'.$empresa.' ('.$tipo_nf.') / <br><b>Fornecedor: </b>'.$fornecedor.' / <br><b>N.º da Nota Fiscal: </b>'.$num_nota.' / <br><b>Login: </b>'.$login_alterando;
                $txt_justificativa          = $complemento_justificativa.'<br><b>Alterado da Data: </b>'.$data_venc_antiga.' <b>para </b>'.$txt_data_vencimento.'<br>'.date('d/m/Y H:i:s').'<br><b>Justificativa: </b>'.$hdd_justificativa.'<br>'.$PHP_SELF;
//Os e-mails estão especificados dentro da biblioteca intermodular na pasta variáveis ...
                $destino                    = $alterar_contas_apagar;
                $copia                      = $alterar_contas_apagar_copia;
                comunicacao::email('ERP - GRUPO ALBAFER', $destino, $copia, 'Alteração da Data de Vencimento da Nota Fiscal', $txt_justificativa);
            }
        }
//Valor Reajustado modificado ...
        /*if(!empty($_POST['chkt_corrigir_valor'])) {
            $calculos_conta_pagar   = financeiros::calculos_conta_pagar($campos[$i]['id_conta_apagar']);
            $perc_variacao          = (($_POST['txt_valor_reajustado'] / $calculos_conta_pagar['valor_reajustado']) - 1) * 100;
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
            $complemento_justificativa  = '<br><b>Empresa: </b>'.$empresa.' ('.$tipo_nf.') / <br><b>Fornecedor: </b>'.$fornecedor.' / <br><b>N.º da Nota Fiscal: </b>'.$num_nota.' / <br><b>Login: </b>'.$login_alterando;
            $justificativa              = $complemento_justificativa.'<br><b>Valor Reajustado Antigo: </b>R$ '.number_format($calculos_conta_pagar['valor_reajustado'], 2, ',', '.').' - <b>Valor Reajustado Atual: </b>R$ '.number_format($_POST['txt_valor_reajustado'], 2, ',', '.').' - <b>% da Variação: </b>'.number_format($perc_variacao, 2, ',', '.').'<br>'.date('d/m/Y H:i:s').'<br><b>Justificativa: </b>'.$hdd_justificativa.'<br>'.$PHP_SELF;
//Os e-mails estão especificados dentro da biblioteca intermodular na pasta variáveis ...
            $destino                    = $correcao_parc_financ_contas_apagar;
            comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', 'Correção Parcela Financiada - Contas à Pagar', $justificativa);
        }*/
    }
//3)
/************************Alteração************************/
    $dia    = substr($_POST['txt_data_vencimento_alterada'], 0, 2);
    $mes    = substr($_POST['txt_data_vencimento_alterada'], 3, 2);
    $ano    = substr($_POST['txt_data_vencimento_alterada'], 6, 4);
    $semana = data::numero_semana($dia, $mes, $ano);
    
    if(empty($_POST['chkt_previsao'])) $_POST['chkt_previsao'] = 0;

    $data_emissao               = data::datatodate($_POST['txt_data_emissao'], '-');
    $data_vencimento            = data::datatodate($_POST['txt_data_vencimento'], '-');
    $data_vencimento_alterada   = data::datatodate($_POST['txt_data_vencimento_alterada'], '-');
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
    $cmb_importacao             = (!empty($_POST[cmb_importacao])) ? "'".$_POST[cmb_importacao]."'" : 'NULL';
    
//Verifica o status da conta para saber quais os campos que pode alterar
    $sql = "SELECT status 
            FROM `contas_apagares` 
            WHERE `id_conta_apagar` = '$_POST[id_conta_apagar]' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $status_conta   = $campos[0]['status'];
    if($status_conta == 1) {//Pode alterar só alguns campos, pois é, tem uma ou mais parcelas pagas ...
        $sql = "UPDATE `contas_apagares` SET `id_funcionario` = '$_SESSION[id_login]', `id_importacao` = $cmb_importacao, `semana` =  '$semana' WHERE `id_conta_apagar` = '$_POST[id_conta_apagar]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Pode alterar todos os campos, porque não foi paga nenhuma parcela daquela conta ...
        $pos = strripos($_POST['cmb_produto_financeiro'], '|');
        
        if($pos === false) {//Não achou o caracter |, então significa que essa Combo só abastecia 1 Valor ...
            $id_produto_financeiro  = 'NULL';
            $id_grupo               = $_POST['cmb_produto_financeiro'];
        }else {//Achou o caracter |, então representa que essa Combo armazena 2 Valores ...
            $vetor                  = explode('|', $_POST['cmb_produto_financeiro']);
            $id_produto_financeiro  = $vetor[0];
            $id_grupo               = $vetor[1];
        }

        $sql = "UPDATE `contas_apagares` SET `id_funcionario` = '$_SESSION[id_funcionario]', `id_importacao` = $cmb_importacao, `id_tipo_moeda` = '$_POST[cmb_tipo_moeda]', `id_grupo` = '$id_grupo', `id_produto_financeiro` = $id_produto_financeiro, `semana` =  '$semana', `previsao` = '$_POST[chkt_previsao]', `data_emissao` = '$data_emissao', `data_vencimento` = '$data_vencimento', `data_vencimento_alterada` = '$data_vencimento_alterada', `id_tipo_pagamento_recebimento` = '$_POST[id_tipo_pagamento]', `valor` = '$_POST[txt_valor]', `multa` = '$_POST[txt_multa]', `taxa_juros` = '$_POST[txt_taxa_juros]', `tipo_juros` = '$_POST[opt_tipo_juros]', `valor_juros` = '$_POST[txt_valor_juros]', `valor_icms` = '$_POST[txt_icms_creditar]' WHERE `id_conta_apagar` = '$_POST[id_conta_apagar]' LIMIT 1 ";
        bancos::sql($sql);
        
        //Seleção dos dados bancários do fornecedor para gravar na tabela de contas_apagares_vs_pffs para ficar + fácil a busca dos dados ...
        $sql = "SELECT banco, agencia, num_cc, correntista 
                FROM `fornecedores_propriedades` 
                WHERE `id_fornecedor_propriedade` = '$_POST[cmb_conta_corrente]' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {
            $banco_fornecedor   = $campos[0]['banco'];
            $agencia            = $campos[0]['agencia'];
            $num_cc             = $campos[0]['num_cc'];
            $correntista        = $campos[0]['correntista'];
        }
        //Verifico se existe esse "id_conta_apagar" na Tabela relacional `contas_apagares_vs_pffs` ...
        $sql = "SELECT `id_conta_apagar_vs_pff` 
                FROM `contas_apagares_vs_pffs` 
                WHERE `id_conta_apagar` = '$_POST[id_conta_apagar]' LIMIT 1 ";
        $campos_conta_apagar_vs_pffs = bancos::sql($sql);
        if(count($campos_conta_apagar_vs_pffs) == 0) {//Não existe, então insiro um Registro nessa Tabela ...
            $sql = "INSERT INTO `contas_apagares_vs_pffs` (`id_conta_apagar_vs_pff`, `id_conta_apagar`, `banco`, `agencia`, `num_cc`, `correntista`) VALUES (NULL, '$_POST[id_conta_apagar]', '$banco_fornecedor', '$agencia', '$num_cc', '$correntista') ";
        }else {//Já existe, então só atualizo essa Tabela ...
            $sql = "UPDATE `contas_apagares_vs_pffs` SET `banco` = '$banco_fornecedor', `agencia` = '$agencia', `num_cc` = '$num_cc', `correntista` = '$correntista' WHERE `id_conta_apagar` = '$_POST[id_conta_apagar]' LIMIT 1 ";
        }
        bancos::sql($sql);
        
        financeiros::atualizar_data_alterada($_POST['id_conta_apagar'], 'A');
        $valor = 2;
    }
}

$id_conta_apagar = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_conta_apagar'] : $_GET['id_conta_apagar'];

//Seleção dos dados de contas à pagar
$sql = "SELECT ca.*, f.razaosocial, tp.`status_db` 
        FROM `contas_apagares` ca 
        INNER JOIN `fornecedores` f ON f.id_fornecedor = ca.id_fornecedor 
        INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = ca.`id_tipo_pagamento_recebimento` 
        WHERE ca.`id_conta_apagar` = '$id_conta_apagar' LIMIT 1 ";
$campos                         = bancos::sql($sql);
$id_conta_apagar_automatica     = $campos[0]['id_conta_apagar_automatica'];
$id_fornecedor                  = $campos[0]['id_fornecedor'];
$id_pedido                      = $campos[0]['id_pedido'];
$id_antecipacao                 = $campos[0]['id_antecipacao'];
$id_nfe                         = $campos[0]['id_nfe'];
$id_nf                          = $campos[0]['id_nf'];
$id_representante               = $campos[0]['id_representante'];
$id_importacao                  = $campos[0]['id_importacao'];
$id_tipo_moeda                  = $campos[0]['id_tipo_moeda'];
$id_tipo_pagamento              = $campos[0]['id_tipo_pagamento_recebimento'];
$status_db                      = $campos[0]['status_db'];
$id_tipo_pagamento_status       = $campos[0]['id_tipo_pagamento_recebimento'].'|'.$status_db;
$id_grupo                       = $campos[0]['id_grupo'];
$id_produto_financeiro          = $campos[0]['id_produto_financeiro'];
$perc_uso_produto_financeiro    = $campos[0]['perc_uso_produto_financeiro'];
$valor_conta                    = $campos[0]['valor'];
$multa                          = ($campos[0]['multa'] != '0.00' && $campos[0]['multa'] != '') ? number_format($campos[0]['multa'], '2', ',', '') : '';
$taxa_juros 			= ($campos[0]['taxa_juros'] != '0.00' && $campos[0]['taxa_juros'] != '') ? number_format($campos[0]['taxa_juros'], '2', ',', '') : '';
$tipo_juros                     = $campos[0]['tipo_juros'];
$valor_juros 			= ($campos[0]['valor_juros'] != '0.00' && $campos[0]['valor_juros'] != '') ? number_format($campos[0]['valor_juros'], '2', ',', '') : '';
$valor_icms 			= ($campos[0]['valor_icms'] != '0.00' && $campos[0]['valor_icms'] != '') ? number_format($campos[0]['valor_icms'], '2', ',', '') : '';
$semana                         = $campos[0]['semana'];
$previsao                       = $campos[0]['previsao'];
$data_emissao                   = data::datetodata($campos[0]['data_emissao'], '/');
$data_vencimento                = data::datetodata($campos[0]['data_vencimento'], '/');
$data_vencimento_alterada       = ($campos[0]['data_vencimento_alterada'] != '0000-00-00') ? data::datetodata($campos[0]['data_vencimento_alterada'], '/') : '';
$status_conta                   = $campos[0]['status'];

if(!empty($id_produto_financeiro)) {
    $sql = "SELECT `forcar_icms` 
            FROM `produtos_financeiros` 
            WHERE `id_produto_financeiro` = '$id_produto_financeiro' LIMIT 1 ";
    $campos_produto_financeiro  = bancos::sql($sql);
    $forcar_icms                = $campos_produto_financeiro[0]['forcar_icms'];
}

//Verifica o status da conta para verificar os campos que podem ser alterados
if($status_conta == 1) $disabled = 'disabled';

//Seleção do produto financeiro do fornecedor, da nota e dos dados bancários para poder pegar o id dos dados bancários do fornecedor ...
$sql = "SELECT `banco`, `agencia`, `num_cc` 
        FROM `contas_apagares_vs_pffs` 
        WHERE `id_conta_apagar` = '$id_conta_apagar' LIMIT 1 ";
$campos_pffs        = bancos::sql($sql);
$banco_fornecedor   = $campos_pffs[0]['banco'];
$agencia            = $campos_pffs[0]['agencia'];
$num_cc             = $campos_pffs[0]['num_cc'];

//Aqui eu verifico qual que é o id do dado bancário que foi utilizado pelo fornecedor na conta a pagar e trazer selecionado na combo ...
$sql = "SELECT `id_fornecedor_propriedade` 
        FROM `fornecedores_propriedades` 
        WHERE `id_fornecedor` = '$id_fornecedor' 
        AND `banco` = '$banco_fornecedor' 
        AND `agencia` = '$agencia' 
        AND `num_cc` = '$num_cc' LIMIT 1 ";
$campos_fp                  = bancos::sql($sql);
$id_fornecedor_propriedade  = $campos_fp[0]['id_fornecedor_propriedade'];

//Aqui eu puxo o último valor do dólar e do euro cadastrado
$sql = "SELECT `valor_dolar_dia`, `valor_euro_dia` 
        FROM `cambios` 
        ORDER BY `id_cambio` DESC LIMIT 1 ";
$campos_cambios	= bancos::sql($sql);
$valor_dolar 	= $campos_cambios[0]['valor_dolar_dia'];
$valor_euro 	= $campos_cambios[0]['valor_euro_dia'];

$calculos_conta_pagar   = financeiros::calculos_conta_pagar($campos[$i]['id_conta_apagar']);
$valor_reajustado       = $calculos_conta_pagar['valor_reajustado'];
?>
<html>
<head>
<title>.:: Alterar Conta à Pagar ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/ajax.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/arred.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/data.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    var forcar_icms         = '<?=$forcar_icms;?>'
    var previsao_inicial    = '<?=$previsao;?>'//Variável do jeito que veio do Banco de Dados ...
    var id_funcionario      = eval('<?=$_SESSION['id_funcionario'];?>')
//Tipo de Pagamento ...
    if(!combo('form', 'cmb_tipo_pagamento', '', 'SELECIONE UM TIPO DE PAGAMENTO !')) {
        return false
    }
//Tipo de Moeda ...
    if(!combo('form', 'cmb_tipo_moeda', '', 'SELECIONE O TIPO DA MOEDA !')) {
        return false
    }
//ICMS à Creditar ...
    if(forcar_icms == 'S') {//Se no cadastro consta essa Marcação, forço o Preenchimento desse campo ...
        if(!texto('form', 'txt_icms_creditar', '4', '1234567890,.', 'ICMS À CREDITAR', '2')) {
            return false
        }
//Se o Valor ICMS Creditar = 0, então tem que obrigar a colocar outro valor ...
        if(document.form.txt_icms_creditar.value == '0,00') {
            alert('ICMS À CREDITAR INVÁLIDO !')
            document.form.txt_icms_creditar.focus()
            document.form.txt_icms_creditar.select()
            return false
        }
    }
//Valor Reajustado ...
    if(document.form.txt_valor_reajustado.disabled == false) {//Se essa caixa estiver habilitada ...
        if(!texto('form', 'txt_valor_reajustado', '1', '1234567890,.-', 'VALOR REAJUSTADO', '2')) {
            return false
        }
    }
//Data de Emissão ...
    if(!data('form', 'txt_data_emissao', '4000', 'EMISSÃO')) {
        return false
    }
//Data de Vencimento ...
    if(!data('form', 'txt_data_vencimento', '4000', 'VENCIMENTO')) {
        return false
    }
//Data de Vencimento Alterada ...
    if(document.form.txt_data_vencimento_alterada.value != '') {
        if(!data('form', 'txt_data_vencimento_alterada', '4000', 'VENCIMENTO ALTERADA')) {
            return false
        }
        var data_vencimento             = document.form.txt_data_vencimento.value
        var data_vencimento_alterada    = document.form.txt_data_vencimento_alterada.value
        data_vencimento                 = data_vencimento.substr(6, 4) + data_vencimento.substr(3, 2) + data_vencimento.substr(0, 2)
        data_vencimento_alterada        = data_vencimento_alterada.substr(6, 4) + data_vencimento_alterada.substr(3, 2) + data_vencimento_alterada.substr(0, 2)
        data_vencimento                 = eval(data_vencimento)
        data_vencimento_alterada        = eval(data_vencimento_alterada)

        /*Essa marcação inicial significa que essa Conta não é "Previsão", sendo assim faço a comparação 
        para Bloqueio nas Datas de Vencimento abaixo ...*/
        if(previsao_inicial == 0) {
            /*Os funcionários Roberto 62 e Dona Sandra 66 são os únicos que podem retroagir a 
            Data de uma Conta à Pagar ...*/
            if(id_funcionario != 62 && id_funcionario != 66) {
                if(data_vencimento_alterada < data_vencimento) {
                    var resposta = confirm('DATA DE VENCIMENTO ALTERADA INVÁLIDA !!! DATA DE VENCIMENTO ALTERADA MENOR DO QUE A DATA DE VENCIMENTO !\n\nDESEJA CONTINUAR ASSIM MESMO ???')
                    if(resposta == true) {//OK ...
                        document.form.txt_data_vencimento.value = document.form.txt_data_vencimento_alterada.value
                    }else {//Cancelar ...
                        document.form.txt_data_vencimento_alterada.focus()
                        document.form.txt_data_vencimento_alterada.select()
                        return false
                    }
                }
            }
        }
    }
//Representa que a Previsão veio marcada do Banco de Dados e que a mesma foi desmarcada pelo Usuário ...
    if(previsao_inicial == 1 && !document.form.chkt_previsao.checked) {
        var resposta = confirm('VOCÊ TIROU DA PREVISÃO !!!\n\nCONFIRMA O NOVO VALOR NACIONAL E DATA DE VENCIMENTO ALTERADA ?')
        if(resposta == true) {
            document.form.txt_data_vencimento.value = document.form.txt_data_vencimento_alterada.value
        }else {
            return false
        }
    }
/******************Controle com a Data de Vencimento e Valor Reajustado******************/
//Aqui eu verifico se a Data de Vencimento foi alterada pelo usuário ...
    var data_vencimento_alterada_bd = '<?=$data_vencimento_alterada;?>'//Q carrega do BD diretamente ...
    var data_vencimento_alterada_dg = document.form.txt_data_vencimento_alterada.value//Digitada pelo usuário ...
//Verifico se a Data de Vencimento e o Valor Reajustado foi alterado pelo usuário ...
    if(data_vencimento_alterada_bd != data_vencimento_alterada_dg) {
        var justificativa = prompt('DIGITE UMA JUSTIFICATIVA P/ MUDAR A DATA DE VENCIMENTO ALTERADA: ')
        document.form.hdd_justificativa.value = justificativa
//Controle com a Justificativa ...
        if(document.form.hdd_justificativa.value == '' || document.form.hdd_justificativa.value == 'null' || document.form.hdd_justificativa.value == 'undefined') {
            alert('JUSTIFICATIVA INVÁLIDA !!!\nDIGITE UMA JUSTIFICATIVA P/ MUDAR A DATA DE VENCIMENTO ALTERADA !')
            return false
        }
        //Preparar segurança p/ Multa e Taxa de Juros ...
    }
/*********************************************************************/
//Desabilito esses campos para poder gravar no BD
    document.form.cmb_tipo_pagamento.disabled   = false
    document.form.chkt_previsao.disabled        = false
    document.form.cmb_tipo_moeda.disabled       = false
    document.form.txt_valor.disabled            = false
    document.form.txt_valor_juros.disabled      = false
    document.form.txt_valor_reajustado.disabled = false
    document.form.txt_data_emissao.disabled     = false
    document.form.txt_data_vencimento.disabled  = false
//Aqui é para não atualizar o frame de Itens abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    return limpeza_moeda('form', 'txt_icms_creditar, txt_valor, txt_multa, txt_taxa_juros, txt_valor_juros, txt_valor_reajustado, ')
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
}

function calcular() {
    var valor_conta = eval('<?=$valor_conta;?>')
    var tipo_moeda  = document.form.cmb_tipo_moeda.value
    
//Independente do caso, descubro o Valor da Conta em Reais ...
    if(tipo_moeda == 2) {//Conta em Dólar ...
        var valor_em_reais  = valor_conta * eval('<?=$valor_dolar;?>')
    }else if(tipo_moeda == 3) {//Conta em Euro ...
        var valor_em_reais  = valor_conta * eval('<?=$valor_euro;?>')
    }else {//Conta em R$ ...
        var valor_em_reais  = valor_conta//Nesse caso é o Valor da Própria Conta ...
    }
    var multa = (document.form.txt_multa.value != '') ? eval(strtofloat(document.form.txt_multa.value)) : 0
    
//Taxa Juros ...
    if(document.form.txt_taxa_juros.value != '') {//Se esse valor estiver preenchido faço o Cálculo abaixo ...
        var dias        = diferenca_datas('document.form.txt_data_vencimento', 'document.form.txt_data_atual')    
        var taxa_juros  = eval(strtofloat(document.form.txt_taxa_juros.value))
        
        if(document.form.opt_tipo_juros[0].checked == true) {//Juros Simples ...
            //A variável dias equivale a data atual até a data de vecimento ...
            var fator_taxa_juros_dias_venc  = (taxa_juros / 30 * dias / 100) + 1
        }else {//Juros Composto ...
            var fator_taxa_juros_diaria     = Math.pow(1 + taxa_juros / 100, (1/30))
            var fator_taxa_juros_dias_venc  = Math.pow(fator_taxa_juros_diaria, dias)
        }
        document.form.txt_valor_juros.value = valor_em_reais * (fator_taxa_juros_dias_venc - 1)
        document.form.txt_valor_juros.value = arred(document.form.txt_valor_juros.value, 2, 1)
    }else {//Valor não Preenchido ...
        document.form.txt_valor_juros.value = ''
    }
    calcular_valor_reajustado()
}

function habilitar_valor_juros() {
    if(document.form.chkt_calcular_taxa_juros.checked == true) {
        document.form.txt_valor_juros.className = 'caixadetexto'
        document.form.txt_valor_juros.disabled  = false
        document.form.txt_valor_juros.value     = ''
        document.form.txt_valor_juros.focus()
    }else {
        document.form.txt_valor_juros.className = 'textdisabled'
        document.form.txt_valor_juros.disabled  = true
        calcular()
    }
}

function calcular_taxa_juros() {
    var valor_conta = eval('<?=$valor_conta;?>')
    var tipo_moeda  = document.form.cmb_tipo_moeda.value
    
//Independente do caso, descubro o Valor da Conta em Reais ...
    if(tipo_moeda == 2) {//Conta em Dólar ...
        var valor_em_reais  = valor_conta * eval('<?=$valor_dolar;?>')
    }else if(tipo_moeda == 3) {//Conta em Euro ...
        var valor_em_reais  = valor_conta * eval('<?=$valor_euro;?>')
    }else {//Conta em R$ ...
        var valor_em_reais  = valor_conta//Nesse caso é o Valor da Própria Conta ...
    }
    
    var dias        = diferenca_datas('document.form.txt_data_vencimento', 'document.form.txt_data_atual')    
    var valor_juros = (document.form.txt_valor_juros.value != '') ? eval(strtofloat(document.form.txt_valor_juros.value)) : 0
    
    if(document.form.opt_tipo_juros[0].checked == true) {//Juros Simples ...
        document.form.txt_taxa_juros.value = (((valor_juros / valor_em_reais) * 100) / dias) * 30
    }else {//Juros Composto ...
        //document.form.txt_taxa_juros.value = (Math.pow((Math.pow(valor_juros / valor_em_reais, 1 / dias) + 1), 30) - 1) * 100
        document.form.txt_taxa_juros.value = (Math.pow((Math.pow(valor_juros / valor_em_reais + 1, 1 / dias)), 30) - 1) * 100
    }
    document.form.txt_taxa_juros.value = arred(document.form.txt_taxa_juros.value, 2, 1)
    
    calcular_valor_reajustado()
}

function calcular_valor_reajustado() {
    var valor_conta = eval('<?=$valor_conta;?>')
    var tipo_moeda  = document.form.cmb_tipo_moeda.value
    
//Independente do caso, descubro o Valor da Conta em Reais ...
    if(tipo_moeda == 2) {//Conta em Dólar ...
        var valor_em_reais  = valor_conta * eval('<?=$valor_dolar;?>')
    }else if(tipo_moeda == 3) {//Conta em Euro ...
        var valor_em_reais  = valor_conta * eval('<?=$valor_euro;?>')
    }else {//Conta em R$ ...
        var valor_em_reais  = valor_conta//Nesse caso é o Valor da Própria Conta ...
    }
    var multa       = (document.form.txt_multa.value != '') ? eval(strtofloat(document.form.txt_multa.value)) : 0
    var valor_juros = (document.form.txt_valor_juros.value != '') ? eval(strtofloat(document.form.txt_valor_juros.value)) : 0
    
    document.form.txt_valor_reajustado.value = valor_em_reais + multa + valor_juros
    document.form.txt_valor_reajustado.value = arred(document.form.txt_valor_reajustado.value, 2, 1)
}

function corrigir_valor() {
    if(document.form.chkt_corrigir_valor.checked == true) {//Opção Marcada, habilita a Caixa de Valor Reajustado ...
        //Habilita Caixa de Valor Reajustado ...
        document.form.txt_valor_reajustado.disabled     = false
        //Designer de Habilitado de Valor Reajustado ...
        document.form.txt_valor_reajustado.className    = 'caixadetexto'

        //Desabilita Caixas de Juros ...
        document.form.txt_taxa_juros.disabled           = true
        document.form.txt_valor_juros.disabled          = true
        //Designer de Desabilitado de Juros ...
        document.form.txt_taxa_juros.className          = 'textdisabled'
        document.form.txt_valor_juros.className         = 'textdisabled'
        //Limpa as Caixas de Juros ...
        document.form.txt_taxa_juros.value              = ''
        document.form.txt_valor_juros.value             = ''
    }else {//Opção Desmarcada, desabilita a Caixa de Valor Reajustado ...
        //Desabilita Caixa de Valor Reajustado ...
        document.form.txt_valor_reajustado.disabled     = true
        //Designer de Desabilitado de Valor Reajustado ...
        document.form.txt_valor_reajustado.className    = 'textdisabled'
        
        //Habilita Caixas de Juros ...
        document.form.txt_taxa_juros.disabled           = false
        document.form.txt_valor_juros.disabled          = false
        //Designer de Habilitado de Juros ...
        document.form.txt_taxa_juros.className          = 'caixadetexto'
        document.form.txt_valor_juros.className         = 'caixadetexto'
        //Restaura os Valores das Caixas de Juros ...
        document.form.txt_taxa_juros.value              = '<?=$taxa_juros;?>'
        document.form.txt_valor_juros.value             = '<?=$valor_juros;?>'
    }
}

function visualizar_todas_importacoes() {
    var checado = (document.form.chkt_importacao.checked == true) ? 1 : 0
    ajax('consultar_importacao.php?checado='+checado, 'cmb_importacao')
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) opener.parent.itens.document.location = opener.parent.itens.document.location.href
}
</Script>
</head>
<body onload='separar();calcular()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onSubmit='return validar()'>
<!--**********************************************-->
<input type='hidden' name='id_tipo_pagamento' value='<?=$id_tipo_pagamento;?>'>
<input type='hidden' name='status_db' value='<?=$status_db;?>'>
<input type='hidden' name='id_conta_apagar' value='<?=$id_conta_apagar;?>'>
<input type='hidden' name='txt_data_atual' value='<?=date('d/m/Y');?>'>
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='hdd_justificativa'>
<!--**********************************************-->
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Conta à Pagar
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
    <tr class='linhanormal'>
        <td width='50%'>
            <b>Fornecedor:</b>
        </td>
        <td width='50%'>
            <b>N.º da Conta:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?
                if($id_pedido > 0 && $id_antecipacao == 0) {//Somente quando for Numerário que Mostrará essa Opção ...
            ?>
            <select name='cmb_despachante' title='Selecione o Despachante' class='combo'>
            <?
                $sql = "SELECT id_fornecedor, razaosocial 
                        FROM `fornecedores` 
                        WHERE `despachante` = 'S' ORDER BY razaosocial ";
                echo combos::combo($sql, $id_fornecedor);
            ?>
            </select>
            <?
                }else {
            ?>
            <font size='-2'>
                <?=$campos[0]['razaosocial'];?>
            </font>
            <?
                }
            ?>
        </td>
        <td>
            <?=$campos[0]['numero_conta'];?>
        </td>
    </tr>
<?
    /**************************************************************************/
    if($id_antecipacao > 0) {//Só quando existir Antecipação que mostrarei essa Linha ...
?>
    <tr class='linhanormal'>
        <td>
            <b>N.º Pedido: </b>
        </td>
        <td>
            <b>N.º Antecipa&ccedil;&atilde;o: </b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?=$id_pedido;?>
        </td>
        <td>
            <?=$id_antecipacao;?>
        </td>
    </tr>
<?
    }
    /**************************************************************************/
?>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Pagamento:</b>
        </td>
        <td>
            <b>Conta Corrente:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_tipo_pagamento' title='Tipo de Pagamento' onchange='separar()' class='textdisabled' disabled>
            <?
                $sql = "SELECT CONCAT(`id_tipo_pagamento`, '|', `status_db`) AS tipo, `pagamento` 
                        FROM `tipos_pagamentos` 
                        WHERE `ativo` = '1' ORDER BY `pagamento` ";
                echo combos::combo($sql, $id_tipo_pagamento_status);
            ?>
            </select>
            <?
                //Se essa Conta à Pagar for vinculada a uma NF de Entrada, então executo a Query abaixo ...
                if($id_nfe > 0) {//Conta à Pagar com NF de Entrada ...
                    //Verifico se essa NF de Entrada foi paga pelo Caixa de Compras ...
                    $sql = "SELECT pago_pelo_caixa_compras 
                            FROM `nfe` 
                            WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
                    $campos_nfe = bancos::sql($sql);
                    //Se o usuário marcou essa opção de "Caixa" no Cabeçalho de Nota Fiscal, então apresento esta linha abaixo ...
                    if($campos_nfe[0]['pago_pelo_caixa_compras'] == 'S') echo '&nbsp;-&nbsp;<font color="red" size="1"><b>(PAGO PELO CAIXA DE COMPRAS)</b></font>';
                        
                }
            ?>
        </td>
        <td>
            <select name='cmb_conta_corrente' title='Selecione a Conta Corrente' class='combo' <?=$disabled;?>>
            <?
                $sql = "SELECT `id_fornecedor_propriedade`, CONCAT(`num_cc`, '|', `agencia`, '|', `banco`) AS conta_corrente 
                        FROM `fornecedores_propriedades` 
                        WHERE `id_fornecedor` = '$id_fornecedor' ORDER BY conta_corrente ";
                echo combos::combo($sql, $id_fornecedor_propriedade);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?
                //Se existir id_pedido "Numerário", id_antecipacao ou id_nfe "NF de Entrada" só trago o Grupo ...
                if($id_pedido > 0 || $id_antecipacao > 0 || $id_nfe > 0) {
            ?>
            <b>Grupo:</b>
            <?
                }else {//Conta Avulso ...
            ?>
            <b>Produto(s) Financeiro(s) || Grupo:</b>
            <?
                }
            ?>
        </td>
        <td>
            <b>Importação:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='red'><b>
            <?
                //Se existir id_pedido "Numerário", id_antecipacao ou id_nfe "NF de Entrada" só trago o Grupo ...
                if($id_pedido > 0 || $id_antecipacao > 0 || $id_nfe > 0) {
                    if($id_grupo > 0) {
                        $vetor_perc_uso_produto_financeiro = explode(',', $perc_uso_produto_financeiro);
                        //Busca do nome dos Grupos ...
                        $sql = "SELECT `referencia` 
                                FROM `grupos` 
                                WHERE `id_grupo` IN ($id_grupo) ";
                        $campos_grupos = bancos::sql($sql);
                        $linhas_grupos = count($campos_grupos);
                        for($i = 0; $i < $linhas_grupos; $i++) $grupos_exibir.= $vetor_perc_uso_produto_financeiro[$i].'% '.$campos_grupos[$i]['referencia'].', ';
                        $grupos_exibir = substr($grupos_exibir, 0, strlen($grupos_exibir) - 2);
                        echo $grupos_exibir;
                ?>
                <!--Hidden p/ controle de Programação, de modo a não perder o $id_grupo ...-->
                <input type='hidden' name='cmb_produto_financeiro' value='<?=$id_grupo;?>'>
                <?
                    }else {
                ?>
                <select name='cmb_produto_financeiro' title='Selecione um Produto Financeiro' class='combo'>
                <?
                    $sql = "SELECT `id_grupo`, `referencia` 
                            FROM `grupos` 
                            WHERE `ativo` = '1' ORDER BY `referencia` ";
                    echo combos::combo($sql, $id_grupo);
                ?>
                </select>
                <?                        
                    }
                ?>
            </b></font>
            <?
                }else {//Conta Avulso ...
            ?>
                <select name='cmb_produto_financeiro' title='Selecione um Produto Financeiro' class='combo' <?=$disabled;?>>
                <?
                    $sql = "SELECT CONCAT(pf.`id_produto_financeiro`, '|', g.`id_grupo`), CONCAT(pf.`discriminacao`, ' || ', g.`referencia`) AS produto 
                            FROM `produtos_financeiros_vs_fornecedor` pfv 
                            INNER JOIN `produtos_financeiros` pf ON pf.`id_produto_financeiro` = pfv.`id_produto_financeiro` 
                            INNER JOIN `grupos` g ON g.`id_grupo` = pf.`id_grupo` 
                            WHERE pfv.`id_fornecedor` = '$id_fornecedor' ORDER BY produto ";
                    echo combos::combo($sql, $id_produto_financeiro.'|'.$id_grupo);
                ?>
                </select>
            <?
                }
            ?>
            &nbsp;
            <?
                //Se essa Conta à Pagar for vinculada a uma NF de Entrada, então executo a Query abaixo ...
                if($id_nfe > 0) {//Conta à Pagar com NF de Entrada ...
                    //Verifico se essa NF de Entrada foi paga pelo Caixa de Compras ...
                    $sql = "SELECT pago_pelo_caixa_compras 
                            FROM `nfe` 
                            WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
                    $campos_nfe = bancos::sql($sql);
                    //Se o usuário marcou essa opção de "Caixa" no Cabeçalho de Nota Fiscal, então apresento esta linha abaixo ...
                    if($campos_nfe[0]['pago_pelo_caixa_compras'] == 'S') echo '&nbsp;-&nbsp;<font color="red" size="1"><b>(PAGO PELO CAIXA DE COMPRAS)</b></font>';
                }
        ?>
        </td>
        <td>
            <select name='cmb_importacao' id='cmb_importacao' title='Selecione uma Importação' class='combo'>
            <?
                //Se o checkbox estiver marcado, significa que o usuário deseja visualizar todas as Importações ...
                $condicao_ultimos_6meses = " AND SUBSTRING(nfe.`data_emissao`, 1, 10) >= DATE_ADD('".date('Y-m-d')."', INTERVAL -6 MONTH) ";
            
                $sql = "SELECT i.`id_importacao`, i.`nome` 
                        FROM `importacoes` i 
                        INNER JOIN `nfe` ON nfe.`id_importacao` = i.`id_importacao` $condicao_ultimos_6meses 
                        WHERE i.`ativo` = '1' ORDER BY i.`nome` ";
                echo combos::combo($sql, $id_importacao);
            ?>
            </select>
            &nbsp;
            <input type='checkbox' name='chkt_importacao' id='label2' value='1' onclick='visualizar_todas_importacoes()' class='checkbox'>
            <label for='label2'>
                Visualizar Todas Importações
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo da Moeda:</b>
        </td>
        <td>
            ICMS à Creditar:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_tipo_moeda' title='Tipo de Moeda' onchange='return calcular()' class='textdisabled' disabled <?=$disabled;?>>
            <?
                $sql = "SELECT id_tipo_moeda, CONCAT(simbolo, ' - ', moeda) AS moeda 
                        FROM `tipos_moedas` 
                        WHERE `ativo` = '1' ";
                echo combos::combo($sql, $id_tipo_moeda);
            ?>
            </select>
            &nbsp;-&nbsp;
            <font color='blue'>
                Dólar U$ = R$ <?=number_format($valor_dolar, 4, ',', '.');?>
            </font>
            ||
            <font color='blue'>
                Euro &euro; = R$ <?=number_format($valor_euro, 4, ',', '.');?>
            </font>
        </td>
        <td>
        <?
            if($forcar_icms == 'S') {//Se tiver q forçar o ICMS, então a caixa vem habilitada ...
                $class          = 'caixadetexto';
                $disabled_icms  = '';
            }else {//Sempre virá desabilitada ...
                $class          = 'textdisabled';
                $disabled_icms  = 'disabled';
            }
        ?>
            <input type='text' name='txt_icms_creditar' value='<?=$valor_icms;?>' title='Digite o ICMS à Creditar' onkeyup="verifica(this, 'moeda_especial', '2', '1', event)" size='20' maxlength='15' class='<?=$class;?>' <?=$disabled_icms;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor Nacional / Estrangeiro:</b>
        </td>
        <td>
            Multa / Ajuste R$:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?
                /*
                1) Sempre que a Conta à Pagar que o usuário estiver alterando vier de uma Conta Automática; 
                2) ou a Conta à Pagar for do Tipo Manual.

                Esse Campo "Valor Nacional / Estrangeiro" sempre estará disponível p/ ser alterado ...*/
                if($id_conta_apagar_automatica > 0 || (is_null($id_pedido) && is_null($id_antecipacao) && is_null($id_nfe) && is_null($id_nf) && is_null($id_representante))) {
                    $class_valor    = 'caixadetexto';
                    $disabled_valor = '';
                }else {
                    $class_valor    = 'textdisabled';
                    $disabled_valor = 'disabled';
                }
            ?>
            <input type='text' name='txt_valor' value="<?=number_format($valor_conta, '2', ',', '');?>" title="Valor Nacional / Estrangeiro" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" size='14' maxlength='13' class='<?=$class_valor;?>' <?=$disabled_valor;?>>
            <?
                if($previsao == 1) $checked = 'checked';
                
                /*A previsão só estará habilitada quando a Conta à Pagar for do Tipo Manual e vier 
                habilitada do BD, isso normalmente só acontece quando a Conta à Pagar nasceu de 
                Contas Automáticas ...*/
                $disabled_previsao = ($id_pedido > 0 || $id_antecipacao > 0 || $id_nfe > 0 || $previsao == 0) ? 'disabled' : '';
            ?>
            &nbsp;<input type='checkbox' name='chkt_previsao' value='1' id='label' class='checkbox' <?=$checked;?> <?=$disabled_previsao;?>>
            <label for='label'>Previsão</label>
        </td>
        <td>
            <input type='text' name='txt_multa' value="<?=$multa;?>" title='Digite a Multa' size='20' maxlength='15' onkeyup="verifica(this, 'moeda_especial', '2', 1, event);calcular()" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Taxa Juros:
        </td>
        <td>
            Valor Juros R$:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_taxa_juros' value="<?=$taxa_juros;?>" title='Digite a Taxa Juros' size='20' maxlength='15' onkeyup="verifica(this, 'moeda_especial', '2', 1, event);calcular()" class='caixadetexto'>
            <?
                if($tipo_juros == 'S') {
                    $checkeds = 'checked';
                }else {
                    $checkedc = 'checked';
                }
            ?>
            <input type='radio' name='opt_tipo_juros' value='S' id='opt_tipo_jurosS' title='Selecione o Tipo de Juros' onclick='calcular()' <?=$checkeds;?>>
            <label for='opt_tipo_jurosS'>
                Simples
            </label>
            <input type='radio' name='opt_tipo_juros' value='C' id='opt_tipo_jurosC' title='Selecione o Tipo de Juros' onclick='calcular()' <?=$checkedc;?>>
            <label for='opt_tipo_jurosC'>
                Composto
            </label>
        </td>
        <td>
            <input type='text' name='txt_valor_juros' value='<?=$valor_juros;?>' title='Valor Juros' size='20' maxlength='15' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_taxa_juros()" class='textdisabled' disabled>
            &nbsp;
            <?
                //Enquanto a Data de Vencimento for >= Data Atual então travo esse campo "Calcular Taxa de Juros" ...
                if(data::datatodate($data_vencimento, '-') >= date('Y-m-d')) $disabled_calcular_taxa_juros = 'disabled';
            ?>
            <input type='checkbox' name='chkt_calcular_taxa_juros' value='1' title='Calcular Taxa de Juros' onclick='habilitar_valor_juros()' id='id_calcular_taxa_juros' class='checkbox' <?=$disabled_calcular_taxa_juros;?>>
            <label for='id_calcular_taxa_juros'>Calcular Taxa de Juros</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor Reajustado:</b>
        </td>
        <td>
            <b>Data de Emissão:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_valor_reajustado' value='<?=number_format($valor_reajustado, '2', ',', '');?>' title='Valor Reajustado' onkeyup="verifica(this, 'moeda_especial', '2', '1', event)" size='14' maxlength='13' class='textdisabled' disabled> em Reais
        <?
            //Verifico se essa Conta foi feita pelo modo Financiamento ...
            $sql = "SELECT DISTINCT(ca.numero_conta) 
                    FROM `contas_apagares` ca 
                    INNER JOIN `nfe` ON nfe.id_nfe = ca.id_nfe 
                    INNER JOIN `nfe_financiamentos` nf on nf.id_nfe = nfe.id_nfe 
                    WHERE ca.`id_conta_apagar` = '$id_conta_apagar' 
                    AND ca.`ativo` = '1' LIMIT 1 ";
            $campos_financiamento = bancos::sql($sql);
            if(count($campos_financiamento) == 1) {
        ?>
            <input type='checkbox' name='chkt_corrigir_valor' value='1' title='Corrigir Valor' onclick='corrigir_valor();calcular()' id='id_corrigir_valor' class='checkbox' disabled>
                <label for='id_corrigir_valor'>Corrigir Valor</label>
                <font color='red'>
                    <b>(Fora de Uso)</b>
                </font>
        <?
            }
            
            if($id_pedido > 0) {//Somente quando for Numerário que Mostrará essa Opção ...
        ?>
            <marquee width='150'>
                <font color='darkgreen'>
                    <b>VALOR CORRIGIDO PELO FATOR DE IMPORTAÇÃO.</b>
                </font>
            </marquee>
        <?
            }
        ?>
        </td>
        <td>
            <input type='text' name='txt_data_emissao' value='<?=$data_emissao;?>' title='Data de Emissão' onkeyup="verifica(this, 'data', '', '', event)" size='14' maxlength='11' class='textdisabled' disabled <?=$disabled;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Vencimento Inicial:</b>
        </td>
        <td>
            <b>Data de Vencimento Alterada:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_data_vencimento' value='<?=$data_vencimento;?>' title='Data de Vencimento' size='14' maxlength='11' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_data_vencimento_alterada' value='<?=$data_vencimento_alterada;?>' title='Digite a Data de Vencimento Alterada' onkeyup="verifica(this, 'data', '', '', event)" size='14' maxlength='11' class='caixadetexto'>
            &nbsp;<img src='../../../imagem/calendario.gif' width='12' height='12' border='0' alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_vencimento_alterada&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Semana:</b>
        </td>
        <td>
            <b>Grupo:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?=$semana;?>
        </td>
        <td>
            <?
                $vetor_perc_uso_produto_financeiro = explode(',', $perc_uso_produto_financeiro);	
                if(empty($id_grupo)) $id_grupo = 0;//Para não dar erro de SQL ..
                //Busca do nome dos Grupos ...
                $sql = "SELECT `referencia` 
                        FROM `grupos` 
                        WHERE `id_grupo` IN ($id_grupo) ";
                $campos_grupos = bancos::sql($sql);
                $linhas_grupos = count($campos_grupos);
                for($i = 0; $i < $linhas_grupos; $i++) $grupos_exibir.= $vetor_perc_uso_produto_financeiro[$i].'% '.$campos_grupos[$i]['referencia'].', ';
                $grupos_exibir = substr($grupos_exibir, 0, strlen($grupos_exibir) - 2);
                echo $grupos_exibir;
            ?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
        <?
            /******************************************************************/
            /*Significa que essa Tela foi aberta de modo normal e sendo assim exibo normalmente 
            os botões abaixo p/ manipulação de Dados do Formulário ...*/
            if($_GET['pop_up'] != 1) {
        ?>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');calcular();separar()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        <?
            }
            /******************************************************************/
        ?>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
    <!--****************************Follow-UPs***************************-->
    <tr align='center'>
        <td colspan='2'>
            <br/>
            <iframe name='detalhes' id='detalhes' src = '/erp/albafer/modulo/classes/follow_ups/detalhes.php?identificacao=<?=$id_conta_apagar;?>&origem=18' marginwidth='0' marginheight='0' frameborder='0' height='150' width='100%'></iframe>
        </td>
    </tr>
    <!--*****************************************************************-->
</table>
<?
//Aqui retorna todas as parcelas que foram quitadas
    $sql = "SELECT * 
            FROM `contas_apagares_quitacoes` 
            WHERE `id_conta_apagar` = '$_GET[id_conta_apagar]' ORDER BY id_conta_apagar_quitacao ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Detalhes de Conta à Pagar Quitada
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            Tipo de Pagamento
        </td>
        <td rowspan='2'>
            Banco / CC / Cheque
        </td>
        <td colspan='2'>
            Valor R$
        </td>
        <td rowspan='2'>
            Data
        </td>
        <td rowspan='2'>
            Obs.
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Parcela</td>
        <td>
            Total Pago
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
//Aqui eu limpo essa variável "$exibir" para não acumular dados do Loop Anterior ...
        $exibir = '';

        $sql = "SELECT CONCAT(b.`banco`, ' / ', cc.`conta_corrente`) AS dados_bancarios 
                FROM `contas_correntes` cc 
                INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
                INNER JOIN `bancos` b ON b.`id_banco` = a.`id_banco` 
                WHERE `id_contacorrente` = '".$campos[$i]['id_contacorrente']."' LIMIT 1 ";
        $campos_dados_bancarios = bancos::sql($sql);
        if(count($campos_dados_bancarios) == 1) $exibir = $campos_dados_bancarios[0]['dados_bancarios'];

//Aqui eu verifico se tenho cheque nas tabelas relacionais de quitações à pagares ...
        $sql = "SELECT num_cheque, status 
                FROM `cheques` 
                WHERE `id_cheque` = '".$campos[$i]['id_cheque']."' LIMIT 1 ";
        $campos_cheque = bancos::sql($sql);
        if(count($campos_cheque) == 1) {
            if($campos_cheque[0]['status'] == 0) {
                $situacao = 'Aberto';
            }else if($campos_cheque[0]['status'] == 1) {
                $situacao = 'Travado';
            }else if($campos_cheque[0]['status'] == 2) {
                $situacao = 'Emitido';
            }else if($campos_cheque[0]['status'] == 3) {
                $situacao = 'Compensado';
            }else if($campos_cheque[0]['status'] == 4) {
                $situacao = 'Cancelado';
            }
            $exibir.= ' / <a href="cheque/classes/manipular/detalhes.php?id_cheque='.$campos[$i]['id_cheque'].'" title="Detalhes de Cheque" style="cursor:help" class="html5lightbox">'.$campos_cheque[0]['num_cheque'].'</a> <b>('.$situacao.')</b>';
        }
/***********************************************************************/
        $valor_pago         = $campos[$i]['valor'];
        $valor_total_pago+= $campos[$i]['valor'];
?>
    <tr class='linhanormal'>
        <td>
        <?
            $sql = "SELECT pagamento, status_db 
                    FROM `tipos_pagamentos` 
                    WHERE `id_tipo_pagamento` = '".$campos[$i]['id_tipo_pagamento_recebimento']."' LIMIT 1 ";
            $campos_tipo_pagamento = bancos::sql($sql);
            if(count($campos_tipo_pagamento) == 1) {//Encontrou um Tipo de Pagamento ...
                echo $campos_tipo_pagamento[0]['pagamento'];
            }else {//Não encontrou sendo assim, mostro essa Combo p/ que o usuário grave o Tipo de Pagamento ...
        ?>
            <input type='button' name='cmd_atualizar_pagamento' value='Atualizar Pagamento' title='Atualizar Pagamento' onclick="nova_janela('atualizar_pagamento.php?id_conta_apagar_quitacao=<?=$campos[$i]['id_conta_apagar_quitacao'];?>', 'ATUALIZAR_PAGAMENTO', '', '', '', '', 280, 780, 'c', 'c', '', '', 's', 's', '', '', '')" style='color:black' class='botao'>
        <?
            }
        ?>
        </td>
        <td>
            <?=$exibir;?>
        </td>
        <td align='right'>
        <?
            if($id_tipo_moeda == 1) {//Valor em R$ ...
                echo number_format($valor_pago, '2', ',', '.');
            }else {//Valor em Moeda Estrangeira ...
                echo number_format($valor_pago * $campos[$i]['valor_moeda_dia'], '2', ',', '.');
                //Essa MSN só iremos exibir quando for moeda Estrangeira ...
                echo '<br/><font color="brown"><b>Pago '.$campos_moeda[0]['simbolo'].' '.number_format($valor_pago, 2, ',', '.').' -> '.$campos_moeda[0]['simbolo'].'=R$ '.number_format($campos[$i]['valor_moeda_dia'], 4, ',', '.').'</font>';
            }
        ?>
        </td>
        <td align='right'>
            <?=number_format($valor_total_pago, '2', ',', '.');?>
        </td>
        <td align='center'>
            <?=data::datetodata($campos[$i]['data'], '/');?>
        </td>
        <td>
        <?
            if(!empty($campos[$i]['observacao'])) echo $campos[$i]['observacao'];
        ?>
        </td>
    </tr>
<?
        }
    }
?>
</table>
<?
    if($id_representante > 0) {
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_detalhes_representante' value='Detalhes de Representante' title='Detalhes de Representante' style='color:blue' onclick="nova_janela('../../vendas/representante/alterar2.php?passo=1&pop_up=1&id_representante=<?=$id_representante;?>', 'DETALHES', '', '', '', '', 580, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
        </td>
    </tr>
</table>
<?
    }
/************************Visualização das Contas à Pagar************************/
//Aqui eu zero a variável para não dar conflito com a variável lá de cima
    $valor_pagar = 0;
//Essa variável é um controle de outra Tela, quando essa variável for igual a 1, ele não exibe o iframe abaixo
    if($nao_exibir_iframe == 0) {
//Visualizando as Contas à Pagar
        $retorno = financeiros::contas_em_aberto($id_fornecedor, 2, '', 1, $id_conta_apagar_automatica);
        $linhas = count($retorno['id_contas']);
//Se encontrou uma Conta à Pagar pelo menos
        if($linhas > 0) {
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr>
        <td></td>
    </tr>
    <tr class='iframe' onclick="showHide('detalhes1'); return false">
        <td height='22' align='left' colspan='2'>
            <font color='yellow' size='2'>
                (<?=$linhas;?>)
            </font>
            Contas à Pagar do Fornecedor:
            <font color='#FFFFFF' size='2'>
                <?=$fornecedor;?>
            </font>
            <font color='yellow' size='2'>
                - Valor Total:
            </font>
<?
            for($i = 0; $i < $linhas; $i++) {
                $sql = "SELECT ca.*, CONCAT(tm.`simbolo`, '&nbsp;') AS simbolo 
                        FROM `contas_apagares` ca 
                        INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = ca.`id_tipo_moeda` 
                        WHERE ca.`id_conta_apagar` = ".$retorno['id_contas'][$i]." LIMIT 1 ";
                $campos     = bancos::sql($sql);
//Essa variável iguala o tipo de moeda da conta à pagar
                $moeda      = $campos[0]['simbolo'];
                $valor_pagar= $campos[0]['valor'] - $campos[0]['valor_pago'];
                if($campos[0]['predatado'] == 1) {
//Está parte é o script q exibirá o valor da conta quando o cheque for pré-datado ...
                    $sql = "SELECT SUM(caq.`valor`) AS valor 
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
            <font color='#FFFFFF' size='2'>
                <?=number_format($valor_pagar_total, 2, ',', '.');?>
            </font>
            &nbsp;
            <span id='statusdados_fornecedor'>&nbsp;</span>
            <span id='statusdados_fornecedor'>&nbsp;</span>
        </td>
    </tr>
    <tr>
        <td colspan='2'>
<!--Passo o id_fornecedor por parâmetro porque utilizo dentro da Função de Apagar-->
            <iframe src = '../../classes/cliente/debitos_pagar.php?id_fornecedor=<?=$id_fornecedor;?>&id_emp=<?=$id_emp;?>&id_conta_apagar_automatica=<?=$id_conta_apagar_automatica;?>' name='detalhes1' id='detalhes1' marginwidth='0' marginheight='0' style='display:none' frameborder='0' height='126' width='100%' scrolling='auto'></iframe>
        </td>
    </tr>
</table>
<?
        }
    }
/*******************************************************************************/
/************************Visualização das Contas à Receber************************/
//Essa variável é um controle de outra Tela, quando essa variável for igual a 1, ele não exibe o iframe abaixo
    if($nao_exibir_iframe == 0) {
        $sql = "SELECT `id_cliente` 
                FROM `clientes` 
                WHERE `cnpj_cpf` = '$cnpj_cpf' LIMIT 1 ";
        $campos_cliente = bancos::sql($sql);
        //Visualizando as Contas à Receber
        $retorno        = financeiros::contas_em_aberto($campos_cliente[0]['id_cliente'], 1, '', 2);
        $linhas         = count($retorno['id_contas']);
        if($linhas > 0) {
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr>
        <td></td>
    </tr>
    <tr class='iframe' onclick="showHide('detalhes2'); return false">
        <td colspan='2'>
            <font color='yellow' size='2'>
                &nbsp;Débito(s) à Receber: 
            </font>
            <font color='#FFFFFF' size='2'>
                <?=$linhas;?>
            </font>
        </td>
    </tr>
    <tr>
        <td colspan='2'>
<!--Passo o id_cliente por parâmetro porque utilizo dentro da Função de Receber-->
            <iframe src = '../../classes/cliente/debitos_receber.php?id_cliente=<?=$id_cliente;?>&id_emp=<?=$id_emp;?>&ignorar_sessao=1' name='detalhes2' id='detalhes2' marginwidth='0' marginheight='0' style='display: none' frameborder='0' height='126' width='100%' scrolling='auto'></iframe>
        </td>
    </tr>
</table>
<?
        }
/*********************************************************************************/
    }
?>
</form>
</body>
</html>