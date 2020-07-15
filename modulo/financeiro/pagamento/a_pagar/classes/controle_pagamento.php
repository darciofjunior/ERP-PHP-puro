<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
require('../../../../../lib/financeiros.php');
require('../../../../../lib/genericas.php');
session_start('funcionarios');//Não posso retirar esse código de Sessão porque aqui está registrado o $id_emp ...

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/grupo/index.php';
}else if($id_emp == 0) {//Todos
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/todas_empresas/index.php';
}
segurancas::geral($endereco, '../../../../../');

$mensagem[1] = "<font class='confirmacao'>PAGAMENTO EFETUADO COM SUCESSO.</font>";

if($passo == 1) {
    $data_sys                       = date('Y-m-d H:i:s');
    $data_pagamento                 = data::datatodate($_POST['txt_data_pagamento'], '-');
    //Coloco o Tipo de Recebimento como 15, porque equivale ao Tipo de Pagto. -> acerto a pagar vs receber (tro
    if($_POST['chkt_conta_receber'] > 0) {
        $id_tipo_pagamento_recebimento  = 15;
        $total_pagamento                = $_POST['txt_total_recebimento'];//Contas à Receber irão abater as Apagar ...
    }else {
        $id_tipo_pagamento_recebimento  = $_POST['hdd_tipo_pagamento'];
        $total_pagamento                = $_POST['txt_total_pagamento'];
    }
    
    if(count($_POST['chkt_conta_receber']) > 0) {//Significa que eu estou efetuando recebimentos e pagamentos ao mesmo tempo ...
/**************************************Contas à Receber********************************************/
        foreach($_POST['chkt_conta_receber'] as $i => $id_conta_receber) {
//Busca do Valor da Conta na sua moeda Original e o Tipo de Moeda da Conta à Receber ...
            $sql = "SELECT `id_tipo_moeda` 
                    FROM `contas_receberes` 
                    WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
            $campos         = bancos::sql($sql);
            $id_tipo_moeda  = $campos[0]['id_tipo_moeda'];
//É para gravar no Banco o valor do Dólar ou valor do Euro diário
            if($id_tipo_moeda == 1) {//Real
                $valor_moeda_dia    = '1.0000';
                $valor_recebendo    = $_POST['txt_valor_recebendo_rs'][$i];//O valor já veio em R$, então eu não preciso fazer nada ...
            }else if($id_tipo_moeda == 2) {//Dólar
                $valor_moeda_dia    = $_POST['txt_valor_dolar'];
                $valor_recebendo    = ($_POST['txt_valor_recebendo_rs'][$i] / $_POST['txt_valor_dolar']);//O Valor recebendo digitado pelo Usuário estava em R$ e por isso transformo em Dólar ...
            }else if($id_tipo_moeda == 3) {//Euro
                $valor_moeda_dia    = $_POST['txt_valor_euro'];
                $valor_recebendo    = ($_POST['txt_valor_recebendo_rs'][$i] / $_POST['txt_valor_euro']);//O Valor recebendo digitado pelo Usuário estava em R$ e por isso transformo em Euro ...
            }
            //Arredondo p/ ficar com o valor mais preciso ...
            $valor_recebendo = round(round($valor_recebendo, 3), 2);
            /*Se foi habilitado o checkbox de zerar juros, então atualiza a conta receber, como manual 
            e o valor de juros como 0,00 ...*/
            if($_POST['chkt_zerar_juros'] == 1) {
                $sql = "UPDATE `contas_receberes` SET manual = '1', valor_juros = '0.00' WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
                bancos::sql($sql);
            }
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
            $cmb_conta_corrente = (!empty($_POST[cmb_conta_corrente])) ? "'".$_POST[cmb_conta_corrente]."'" : 'NULL';
            
            //Nessa Tabela de Quitações, sempre guardo o Valor Recebido no Tipo da Moeda da Conta R$, Dólar ou Euro ...
            $sql = "INSERT INTO `contas_receberes_quitacoes` (`id_conta_receber_quitacao`, `id_conta_receber`, `id_tipo_recebimento`, `id_contacorrente`, `valor`, `valor_moeda_dia`, `data`, `data_sys`) VALUES (NULL, '$id_conta_receber', '$id_tipo_pagamento_recebimento', $cmb_conta_corrente, '$valor_recebendo', '$valor_moeda_dia', '$data_pagamento', '$data_sys') ";
            bancos::sql($sql);
            
            $dados_cliente      = financeiros::nome_cliente_conta_receber($id_conta_receber);
            $id_cliente_loop    = $dados_cliente['id_cliente'];
            $id_cliente_contato = $dados_cliente['id_cliente_contato'];
            
            //Registrando Follow-UP(s) ...
            $id_representante   = genericas::buscar_id_representante($id_cliente_contato);
            
            /*Tenho essa verificação porque nem todas as Contas à Receber terão Cliente e Representante, devido terem sido 
            inclusas de forma Manual pela opção "Incluir Crédito(s) / Débito(s) Financeiro(s)" ...*/
            if(!empty($id_cliente_contato) && !empty($id_representante)) {
                $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_cliente_contato`, `id_representante`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_cliente_loop', '$id_cliente_contato', '$id_representante', '$_SESSION[id_funcionario]', '$id_conta_receber', '4', 'Acerto de Conta à Receber.', '$data_sys') ";
                bancos::sql($sql);
            }
            
            //Aqui eu somo o valor da última parcela recebida recente da Conta à Receber no Tipo da Moeda da Conta ...
            $sql = "UPDATE `contas_receberes` SET valor_pago = valor_pago + '$valor_recebendo' WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
            bancos::sql($sql);
/*Aqui eu instâncio novamente na função p/ saber o Quanto que ainda resta a Receber da Conta depois 
do último recebimento ...*/
            $calculos_conta_receber = financeiros::calculos_conta_receber($id_conta_receber);
//O restante a Receber, sempre será igual o Valor Reajustado em R$ indepedente de a Conta ser em Dólar ou Euro ...
            $restante_receber       = $calculos_conta_receber['valor_reajustado'];
/*Verifico se ainda resta alguma coisa à receber da conta, caso não falte nada, então significa que a conta 
foi recebido de modo exato, sem um centavo a + ou a -*/
            $status = ($restante_receber == '0.00' || $restante_receber == 0 || $restante_receber == -0) ? 2 : 1;
            $sql    = "UPDATE `contas_receberes` SET status = '$status' WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
/**************************************Contas à Pagar**********************************************/
    $vetor_conta_apagar     = explode(',', $_POST['id_conta_apagar']);//Transforma em Vetor
    
    for($i = 0; $i < count($vetor_conta_apagar); $i++) {
        $calculos_conta_pagar = financeiros::calculos_conta_pagar($vetor_conta_apagar[$i]);

        //Busco Tipo de Moeda e o Número da Conta à Pagar ...
        $sql = "SELECT `id_fornecedor`, `id_tipo_moeda`, `numero_conta`, 
                DATE_FORMAT(`data_vencimento_alterada`, '%d/%m/%Y') AS data_vencimento_alterada 
                FROM `contas_apagares` 
                WHERE `id_conta_apagar` = '$vetor_conta_apagar[$i]' LIMIT 1 ";
        $campos         = bancos::sql($sql);
        $id_fornecedor  = $campos[0]['id_fornecedor'];
        $id_tipo_moeda  = $campos[0]['id_tipo_moeda'];
        /**************************************** Observação Crucial => ********************************************/
        /*****O campo $campos[0]['valor_pago'], guarda o valor pago do Tp de moeda da Conta à Pagar*****************/
        /***********************************************************************************************************/
        if($_POST['chkt_conta_receber'] > 0) {//Significa que houve Acerto de Contas - "à Receber com à Pagar" ...
            if(!empty($_POST['chkt_zerar_valores_extras'])) {
                //Essa atribuiçao é feita de maneira específica p/ a Conta do Loop ...
                $valor_pagando          = $calculos_conta_pagar['valor'];
                $valor_pagando_em_reais = $calculos_conta_pagar['valor'];
            }else {
                //Essa atribuiçao é feita de maneira específica p/ a Conta do Loop ...
                $valor_pagando          = $calculos_conta_pagar['valor_reajustado'];
                $valor_pagando_em_reais = $calculos_conta_pagar['valor_reajustado'];
            }
        }else {//Só houve Contas à Pagar ...
            $valor_pagando          = $_POST['txt_valor_pagando_rs'][$i];
            $valor_pagando_em_reais = $_POST['txt_valor_pagando_rs'][$i];
        }
        //Aqui faz esse cálculo só para ver o quanto resta à pagar da Conta ...
        if($id_tipo_moeda == 1) {//Reais
            $valor_moeda_dia    = '1.0000';
        }else if($id_tipo_moeda == 2) {//Dólar
            $valor_moeda_dia    = $_POST['txt_valor_dolar'];
            $valor_pagando      = ($valor_pagando_em_reais / $_POST['txt_valor_dolar']);//O Valor pagando digitado pelo Usuário estava em R$ e por isso transformo em Dólar ...
        }else if($id_tipo_moeda == 3) {//Euro
            $valor_moeda_dia    = $_POST['txt_valor_euro'];
            $valor_pagando      = ($valor_pagando_em_reais / $_POST['txt_valor_euro']);//O Valor pagando digitado pelo Usuário estava em R$ e por isso transformo em Euro ...
        }
        //Arredondo p/ ficar com o valor mais preciso ...
        $valor_pagando  = round(round($valor_pagando, 3), 2);
        $observacao     = (count($_POST['chkt_conta_receber']) > 0) ? ' com a Conta à Pagar N.º '.$campos[0]['numero_conta'] : strtolower($_POST['txt_observacao']);
        
        if(!empty($_POST['chkt_zerar_valores_extras'])) $observacao.= ' <b>(Valores Extras foram Zerados)</b>';
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
        $cmb_conta_corrente = (!empty($_POST[cmb_conta_corrente])) ? "'".$_POST[cmb_conta_corrente]."'" : 'NULL';
        $cmb_cheque         = (!empty($_POST[cmb_cheque])) ? "'".$_POST[cmb_cheque]."'" : 'NULL';

        //Nessa Tabela de Quitações, sempre guardo o Valor Pago em R$, independente da Moeda ser Dólar ou Euro ...
        $sql = "INSERT INTO `contas_apagares_quitacoes` (`id_conta_apagar_quitacao`, `id_conta_apagar`, `id_tipo_pagamento_recebimento`, `id_contacorrente`, `id_cheque`, `valor`, `valor_moeda_dia`, `data`, `data_sys`) VALUES (NULL, '$vetor_conta_apagar[$i]', '$id_tipo_pagamento_recebimento', $cmb_conta_corrente, $cmb_cheque, '$valor_pagando', '$valor_moeda_dia', '$data_pagamento', '$data_sys') ";
        bancos::sql($sql);
        
        //Registrando Follow-UP(s) ...
        if(!empty($observacao)) {
            if(empty($id_fornecedor)) $id_fornecedor = 'NULL';
            
            $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_fornecedor`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, $id_fornecedor, '$_SESSION[id_funcionario]', '$id_conta_apagar', '18', '$observacao', '$data_sys') ";
            bancos::sql($sql);
        }
        
        //Aqui eu somo o valor da última parcela paga recente da Conta à Pagar no Tipo da Moeda da Conta ...
        $sql = "UPDATE `contas_apagares` SET `valor_pago` = `valor_pago` + '$valor_pagando' WHERE `id_conta_apagar` = '$vetor_conta_apagar[$i]' LIMIT 1 ";
        bancos::sql($sql);
        /***********************************************************************************************************/
        /****************************************Controle do Status da Conta****************************************/
        /***********************************************************************************************************/
        /*Busca o Valor da Conta e o Valor Pago da Conta p/ comparar se já foi paga a conta totalmente ...
        - Obs: No Valor Pago da Conta, já está levando em conta o último pagamento que acabou de ser feito recente*/
        $sql = "SELECT `id_conta_apagar_automatica` 
                FROM `contas_apagares` 
                WHERE `id_conta_apagar` = '$vetor_conta_apagar[$i]' LIMIT 1 ";
        $campos_conta_apagar_automatica = bancos::sql($sql);
        
        //Chamo novamente essa função porque houve um pagamento recentemente, sendo assim diminui a nossa Dívida ...
        $calculos_conta_pagar   = financeiros::calculos_conta_pagar($vetor_conta_apagar[$i]);
        
        /*Significa que o usuário p/ esse respectivo pagamento, mandou ignorar tudo o que é Multa e Juros do Valor 
        Inicial da Conta à Pagar ...*/
        if(!empty($_POST['chkt_zerar_valores_extras'])) {
            $valor_pagar    = $calculos_conta_pagar['valor'] - $calculos_conta_pagar['valor_pago'];
        }else {
            $valor_pagar    = $calculos_conta_pagar['valor_reajustado_moeda_conta'] - $calculos_conta_pagar['valor_pago'];
        }
        
        //Aqui faz esse cálculo só para ver o quanto resta à pagar da Conta ...
        if($id_tipo_moeda == 1) {//Reais
            $valor_pagar_real   = $valor_pagar;//Passo na função financeiros::contas_pagas(...
        }else if($id_tipo_moeda == 2) {//Dólar
            $valor_pagar_real   = $valor_pagar * $_POST['txt_valor_dolar'];//Passo na função financeiros::contas_pagas(...
        }else if($id_tipo_moeda == 3) {//Euro
            $valor_pagar_real   = $valor_pagar * $_POST['txt_valor_euro'];//Passo na função financeiros::contas_pagas(...
        }
        if($campos_conta_apagar_automatica[0]['id_conta_apagar_automatica'] > 0) financeiros::atualiza_valores_contas_automaticas($campos_conta_apagar_automatica[0]['id_conta_apagar_automatica'], $calculos_conta_pagar['valor'], $valor_pagar_real);//Tenho que manter a ordem deste Script ...
        
        if($valor_pagar == 0 || $valor_pagar == '0.00') {//Aqui significa que a conta foi paga de modo exato, sem um centavo a + ou a -
            $status = 2;//Paga de forma Total ...
        }else {
            $status = 1;//Paga de forma Parcial ...
        }
        //Atualizo o status da Conta que acabou de ser paga ...
        $sql = "UPDATE `contas_apagares` SET `status` = '$status' WHERE `id_conta_apagar` = '$vetor_conta_apagar[$i]' LIMIT 1 ";
        bancos::sql($sql);
        /***********************************************************************************************************/
//Aqui adiciona dados na tabela relacional de contas_apagares com cheques
        if(!empty($_POST['cmb_cheque'])) {
/*Só entra aqui se o cheque for pré-datado, muda o campo predatado da conta à pagar para 1, significando que aquela 
conta foi paga com cheque mas predatado ...*/
            if(!empty($_POST['chkt_predatado'])) {
                $sql = "UPDATE `contas_apagares` SET `predatado` = '1', `data_vencimento_alterada` = '$data_pagamento' WHERE `id_conta_apagar` = '$vetor_conta_apagar[$i]' LIMIT 1 ";
                bancos::sql($sql);
                
                //Registrando Follow-UP(s) ...
                $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_fornecedor`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_fornecedor', '$_SESSION[id_funcionario]', '$id_conta_apagar', '18', '<br/>A \"Data de Vencimento Alterada\" anterior era ".$campos[0]['data_vencimento_alterada']."', '$data_sys') ";
                bancos::sql($sql);
            }
        }
    }
/*A atualização do cheque é fora do laço de contas à pagar, primeiro porque é um único cheque para pagar várias contas 
e outra porque senão ele vai incrementando no valor do cheque conforme o número de contas ...*/
//Atualiza o cheque com o valor e histórico
    if(!empty($_POST['cmb_cheque'])) {
        $sql = "UPDATE `cheques` SET `status` = '2', `valor` = valor + '$total_pagamento', `historico` = CONCAT(historico,' => ', '$observacao') WHERE `id_cheque` = '$_POST[cmb_cheque]' LIMIT 1 ";
        bancos::sql($sql);
/*Só entra aqui se o cheque for pré-datado, muda o campo predatado do cheque para 1, significando que aquela conta 
foi paga com cheque mas predatado ...*/
        if(!empty($_POST['chkt_predatado'])) {
            $sql = "UPDATE `cheques` SET `predatado` = '1' WHERE `id_cheque` = '$_POST[cmb_cheque]' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
/**************************************************************************************************/
?>
    <Script Language = 'JavaScript'>
        window.location = 'controle_pagamento.php?id_conta_apagar=<?=$_POST['id_conta_apagar'];?>&valor=1'
    </Script>
<?
}else {
    //Aqui eu puxo o último valor do dólar e do euro cadastrado
    $sql = "SELECT valor_dolar_dia, valor_euro_dia, data  
            FROM `cambios` 
            ORDER BY id_cambio DESC LIMIT 1 ";
    $campos_cambios	= bancos::sql($sql);
    $valor_dolar 	= $campos_cambios[0]['valor_dolar_dia'];
    $valor_euro 	= $campos_cambios[0]['valor_euro_dia'];
    $data_cadastro 	= data::datetodata($campos_cambios[0]['data'], '/');
/*******************************************************************************************************/
/*Eu faço isso porque durante todo o processo vão sumindo as contas devido ao usuário ir pagando uma conta
com outra que está recebendo, etc ...*/
//Retorno desse string somente as contas em que o Status é < 2
    $sql = "SELECT id_conta_apagar 
            FROM `contas_apagares` 
            WHERE `id_conta_apagar` IN ($id_conta_apagar) 
            AND `status` < '2' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {//Se encontrar pelo menos 1 conta, então ...
        for($i = 0; $i < $linhas; $i++) $id_conta_apagar_em_aberto.= $campos[$i]['id_conta_apagar'].', ';
        $id_conta_apagar_em_aberto = substr($id_conta_apagar_em_aberto, 0, strlen($id_conta_apagar_em_aberto) - 2);
/*******************************************************************************************************/
//Transformo o String em Vetor
        $vetor_conta_apagar = explode(',', $id_conta_apagar_em_aberto);
    }else {//Não há + contas p/ pagar, então posso fechar o Pop-UP ...
?>
    <Script Language = 'JavaScript'>
        window.opener.parent.itens.document.location = 'itens.php<?=$parametro;?>'
        window.close()
    </Script>
<?
        exit;
    }
?>
<html>
<head>
<title>.:: Quitar Conta à Pagar ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'tabela_itens_checkbox.js'></Script>
<Script Language = 'JavaScript'>
//Aqui eu trato os objetos do Formulário de Contas à Pagar antes de submeter p/ o Banco de Dados ...
function tratar_formulario_contas_a_pagar() {
    var elementos   = document.form.elements
    var linhas      = (typeof(elementos['txt_valor_pagar_restante[]'][0]) == 'undefined') ? 1 : (elementos['txt_valor_pagar_restante[]'].length)

    //Aqui faz toda essa preparação para poder gravar no BD
    for(var i = 0; i < linhas; i++) document.getElementById('txt_valor_pagando_rs'+i).value = strtofloat(document.getElementById('txt_valor_pagando_rs'+i).value)
    
    //Travo o botão p/ que o usuário não fique clicando no mesmo várias vezes, após ter submetido ...
    document.form.cmd_salvar.className          = 'textdisabled'
    document.form.cmd_salvar.disabled           = true
    document.form.txt_total_pagamento.disabled  = false
    //Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    limpeza_moeda('form', 'txt_valor_dolar, txt_valor_euro, txt_total_pagamento, ')
}
    
//Essa função calcula o valor da conta apenas em todas as linhas no tipo da moeda especificado ...
function calcular_todas_contas() {
    var elementos           = document.form.elements
    var linhas              = (typeof(elementos['txt_valor_pagar_restante[]'][0]) == 'undefined') ? 1 : (elementos['txt_valor_pagar_restante[]'].length)
    
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('hdd_tipo_moeda'+i).value == 2) {//Conta em U$ ...
            //Se a Conta à Pagar está em U$, então só me baseio na Caixa em Dólar ...
            var moeda_estrangeira   = (document.form.txt_valor_dolar.value != '') ? eval(strtofloat(document.form.txt_valor_dolar.value)) : 0
        }else if(document.getElementById('hdd_tipo_moeda'+i).value == 3) {//Conta em Euro 
            //Se a Conta à Pagar está em U$, então só me baseio na Caixa em Euro ...
            var moeda_estrangeira = (document.form.txt_valor_euro.value != '') ? eval(strtofloat(document.form.txt_valor_euro.value)) : 0
        }else {//Conta em R$ ...
            var moeda_estrangeira = 1
        }
        
        var valor_pagando = eval(strtofloat(document.getElementById('txt_valor_pagando'+i).value))
        //Calculo o Valor Pagando na Moeda Estrangeira da Conta ...
        if(document.getElementById('txt_valor_pagando'+i).value == '') {
            document.getElementById('txt_valor_pagando'+i).value        = ''
            document.getElementById('txt_valor_pagando_rs'+i).value     = ''
        }else {
            document.getElementById('txt_valor_pagando_rs'+i).value     = valor_pagando * moeda_estrangeira
            document.getElementById('txt_valor_pagando_rs'+i).value     = arred(document.getElementById('txt_valor_pagando_rs'+i).value, 2, 1)
        }
    }
}

//Essa função calcula o valor da conta apenas na linha corrente
function calcular_conta_pagar_por_linha(indice) {
//Essa variável "valor_pagar" serve para me retornar o quanta que falta da conta para pagar na moeda R$, U$ ou Euro ...
    var valor_pagar     = eval(strtofloat(document.getElementById('txt_saldo_a_pagar'+indice).value))
    var valor_pagando   = eval(strtofloat(document.getElementById('txt_valor_pagando'+indice).value))

    //Calculo o Restante do Valor à Pagar ...
    if(document.getElementById('txt_valor_pagando'+indice).value == '') {
        document.getElementById('txt_valor_pagar_restante'+indice).value    = valor_pagar
        document.getElementById('txt_valor_pagando'+indice).value           = ''
        document.getElementById('txt_valor_pagando_rs'+indice).value        = ''
    }else {
        document.getElementById('txt_valor_pagar_restante'+indice).value    = valor_pagar - valor_pagando

        //Calculando o Valor que está sendo pago, mais sempre em R$, isso terá + vantagens em Contas Estrangeiras ...
        if(document.getElementById('hdd_tipo_moeda'+indice).value == 2) {//Dólar ...
            valor_dolar = (document.form.txt_valor_dolar.value != '') ? eval(strtofloat(document.form.txt_valor_dolar.value)) : 0
            document.getElementById('txt_valor_pagando_rs'+indice).value = valor_pagando * valor_dolar
        }else if(document.getElementById('hdd_tipo_moeda'+indice).value == 3) {//Euro ...
            valor_euro = (document.form.txt_valor_euro.value != '') ? eval(strtofloat(document.form.txt_valor_euro.value)) : 0
            document.getElementById('txt_valor_pagando_rs'+indice).value = valor_pagando * valor_euro
        }else {//Reais ...
            document.getElementById('txt_valor_pagando_rs'+indice).value = valor_pagando
        }
        document.getElementById('txt_valor_pagando_rs'+indice).value    = arred(document.getElementById('txt_valor_pagando_rs'+indice).value, 2, 1)
    }
    document.getElementById('txt_valor_pagar_restante'+indice).value    = arred(document.getElementById('txt_valor_pagar_restante'+indice).value, 2, 1)
}

function valor_total_contas_pagar() {
    var elementos       = document.form.elements
    var linhas          = (typeof(elementos['txt_valor_pagar_restante[]'][0]) == 'undefined') ? 1 : (elementos['txt_valor_pagar_restante[]'].length)
    var valor_total_rs  = 0

    for(var i = 0; i < linhas; i++) {
        valor_total_rs+= eval(strtofloat(document.getElementById('txt_valor_pagando_rs'+i).value))
        /*Transformo a variável "valor_total_rs" em String p/ poder arredondar p/ 2 casas, infelizmente 
        o JavaScript da erro de cálculo, proporcionando um arredondamento incorreto com várias casas decimais 
        sem necessidade -> Exemplo: 9610,630000000000000001 ...*/
        valor_total_rs = String(valor_total_rs)
        valor_total_rs = arred(valor_total_rs, 2, 1)
        /*Aqui transformo a variável "valor_total_rs" em número novamente p/ que essa continue acumulando 
        os "valores pagando" dos próximos loops ...*/
        valor_total_rs = eval(strtofloat(valor_total_rs))
    }
    document.form.txt_total_pagamento.value = valor_total_rs
    document.form.txt_total_pagamento.value = arred(document.form.txt_total_pagamento.value, 2, 1)
}

function separar() {
    var tipo_pagamento = document.form.cmb_tipo_pagamento.value
    var achou = 0, id_tipo_pagamento = '', status_ch = ''
    for(i = 0; i < tipo_pagamento.length; i++) {
        if(tipo_pagamento.charAt(i) == '|') {
            achou = 1
        }else {
            if(achou == 0) {
                id_tipo_pagamento = id_tipo_pagamento + tipo_pagamento.charAt(i)
            }else {
                status_ch = status_ch + tipo_pagamento.charAt(i)
            }
        }
    }
    document.form.hdd_tipo_pagamento.value  = id_tipo_pagamento
    document.form.hdd_status_ch.value       = status_ch
}

function zerar_valores_extras() {
    var elementos       = document.form.elements
    var linhas          = (typeof(elementos['txt_valor_pagar_restante[]'][0]) == 'undefined') ? 1 : (elementos['txt_valor_pagar_restante[]'].length)

    for(var i = 0; i < linhas; i++) {
        if(document.form.chkt_zerar_valores_extras.checked == true) {//Significa que o usuário Zerou os Valores Extras ...
            document.getElementById('txt_valores_extras'+i).value   = '0,00'
        }else {//Significa que o usuário "Não Zerou" os Valores Extras ...
            document.getElementById('txt_valores_extras'+i).value   = document.getElementById('hdd_valores_extras'+i).value
        }
        var valor_nac_est   = eval(strtofloat(document.getElementById('txt_valor_nac_est'+i).value))
        var valores_extras  = eval(strtofloat(document.getElementById('txt_valores_extras'+i).value))
        var valor_pago      = eval(strtofloat(document.getElementById('txt_valor_pago'+i).value))

        document.getElementById('txt_saldo_a_pagar'+i).value    = (valor_nac_est + valores_extras) - valor_pago
        document.getElementById('txt_valor_pagando'+i).value    = document.getElementById('txt_saldo_a_pagar'+i).value
        
        document.getElementById('txt_saldo_a_pagar'+i).value    = arred(document.getElementById('txt_saldo_a_pagar'+i).value, 2, 1)
        document.getElementById('txt_valor_pagando'+i).value    = arred(document.getElementById('txt_valor_pagando'+i).value, 2, 1)
        
        calcular_conta_pagar_por_linha(i)
    }
}

function carregar_dados_pagamento(valor) {
    /*Nesse caso, sempre estou deixando todos os Cheques Liberados, independente da opção 
    que o Usuário venha escolher mais abaixo ...*/
    reservar_cheques(0)
    
    if(valor == 1) {//Significa que o usuário selecionou a combo "Tipo de Pagamento" ...
        if(document.form.hdd_status_ch.value >= 1) {//Significa que existem Dados Bancários ou Cheques ...
            document.getElementById('lbl_conta_corrente').style.visibility = 'visible'//Aparecendo ...
            document.getElementById('lbl_cheque').style.visibility = 'hidden'//Ocultando ...
            ajax('carregar_dados_pagamento.php?valor=1', 'cmb_conta_corrente')
        }else {//Não existem Contas Correntes nesse nível "0" ...
            document.getElementById('lbl_conta_corrente').style.visibility = 'hidden'//Ocultando ...
            document.getElementById('lbl_cheque').style.visibility = 'hidden'//Ocultando ...
        }
    }else if(valor == 2) {//Significa que o usuário selecionou a combo "Conta Corrente" ...
        if(document.form.hdd_status_ch.value == 2) {//Só carrego os Cheques se o Nível máximo de Preenchimento = 2 ...
            if(document.form.cmb_conta_corrente.value > 0) {//Selecionou alguma Conta Corrente ...
                //Verifico se existe(m) Cheques p/ a Conta Corrente selecionada ...
                ajax('carregar_dados_pagamento.php?cmb_conta_corrente='+document.form.cmb_conta_corrente.value+'&valor=2', 'cmb_cheque')
                /*Eu preciso colocar um "Timeout" porque para o JavaScript entender os dados que foram 
                retornados do Ajax aqui nesta tela se gasta um tempo de milésimos de segundos ??? ...*/
                setTimeout("visualizar_cheques()", 500)
            }else {//Não selecionou nenhuma Conta Corrente ...
                document.getElementById('lbl_cheque').style.visibility = 'hidden'//Ocultando ...
                ajax('carregar_dados_pagamento.php?cmb_conta_corrente=0&valor=2', 'cmb_cheque')
            }
        }else {//Não existem Cheques nesse nível "0 ou 1" ...
            document.getElementById('lbl_cheque').style.visibility = 'hidden'//Ocultando ...
        }
    }
}

function visualizar_cheques() {
    //Se existe pelo menos 1 Cheque, então eu apresento os mesmos na Combo de Cheques ...
    if(document.form.cmb_cheque.length > 1) {//Coloco length > 1, porque "1" representa o próprio Selecione ...
        document.getElementById('lbl_cheque').style.visibility = 'visible'//Aparecendo ...
    }else {
        document.getElementById('lbl_cheque').style.visibility = 'hidden'//Ocultando ...
    }
}

function reservar_cheques(id_cheque) {
    ajax('reservar_cheques.php?cmb_cheque='+id_cheque, 'div_reservar_cheques')
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.opener.parent.itens.document.location = 'itens.php<?=$parametro;?>'
    }
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>'>
<!--************************Controles de Tela************************-->
<input type='hidden' name='hdd_tipo_pagamento'>
<!--Esse campo de "status_ch", representa o Nível "Forçar Preenchimento" de Dados referente ao Tipo de Pagto ...-->
<input type='hidden' name='hdd_status_ch'>
<input type='hidden' name='id_conta_apagar' value='<?=$id_conta_apagar_em_aberto;?>'>
<input type='hidden' name='nao_atualizar'>
<!--*****************************************************************-->
<table width='98%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='13'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
            Quitar Conta à Pagar
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
    <tr class='linhadestaque'>
        <td colspan='13'>
            Pagto:
            <select name='cmb_tipo_pagamento' title='Selecione o Tipo de Pagamento' onchange='separar();carregar_dados_pagamento(1)' class='combo'>
            <?
                $sql = "SELECT CONCAT(`id_tipo_pagamento`, '|', `status_ch`) AS tipo_pagamento_status, `pagamento` 
                        FROM `tipos_pagamentos` 
                        WHERE `ativo` = '1' ORDER BY `pagamento` ";
                echo combos::combo($sql);
            ?>
            </select>
            <label id='lbl_conta_corrente' style='visibility: hidden'>
                &nbsp;
                Conta Corrente: 
                <select name='cmb_conta_corrente' id='cmb_conta_corrente' title='Selecione a Conta Corrente' onchange='carregar_dados_pagamento(2)' class='combo'>
                </select>
            </label>
            <label id='lbl_cheque' style='visibility: hidden'>
                &nbsp;
                Cheque:
                <select name='cmb_cheque' id='cmb_cheque' title='Selecione o Cheque' onchange='reservar_cheques(this.value)' class='combo'>
                </select>
                <input type='checkbox' name='chkt_predatado' value='1' class='checkbox' id='cheque'>
                <label for='cheque'>Pré-Datado</label>
                <div id='div_reservar_cheques'>
                </div>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            <b>Data de Pagamento:</b>
            <input type='text' name='txt_data_pagamento' value='<?=date('d/m/Y');?>' title='Digite a Data de Pagamento' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
            &nbsp;<img src='../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_pagamento&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
        <td colspan='4'>
            <font color='blue'>
                <b>Valor Dólar: <?='('.$data_cadastro.')';?></b>
            </font>
            
            <input type='text' name='txt_valor_dolar' value='<?=number_format($valor_dolar, 4, ',', '.');?>' title='Valor Dólar' onkeyup="verifica(this, 'moeda_especial', '4', '', event);calcular_todas_contas();valor_total_contas_pagar()" maxlength='7' size='6' class='caixadetexto'>
        </td>
        <td colspan='7'>
            <font color='blue'>
                <b>Valor Euro: <?='('.$data_cadastro.')';?></b>
            </font>
            <input type='text' name='txt_valor_euro' value='<?=number_format($valor_euro, 4, ',', '.');?>' title='Valor Euro' onkeyup="verifica(this, 'moeda_especial', '4', '', event);calcular_todas_contas();valor_total_contas_pagar()" maxlength='7' size='6' class='caixadetexto'>
            &nbsp;
            <input type='checkbox' name='chkt_zerar_valores_extras' id='chkt_zerar_valores_extras' value='S' title='Selecione o Zerar Valores Extras' onclick='zerar_valores_extras();valor_total_contas_pagar()' class='checkbox'>
            <label for='chkt_zerar_valores_extras'>
                Zerar Valores Extras
            </label>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.º / Conta
        </td>
        <td>
            Data Venc. <br/>Inicial
        </td>
        <td>
            Data Venc. <br/>Alterada
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
            Tx Juros
        </td>
        <td>
            Vlr Nac / Est
        </td>
        <td>
            Vlr Extras
        </td>
        <td>
            Vlr Pago
        </td>
        <td>
            Saldo à Pagar
        </td>
        <td>
            Vlr Pagando Moeda
        </td>
        <td>
            Vlr Pagando R$
        </td>
        <td>
            Valor Restante
        </td>
    </tr>
<?
//Essa variável vai me auxiliar para controlar o botão de Salvar lá da Parte de Contas à Receber, através do JavaScript ...
        $contas_valor_negativo      = 0;

        //Variáveis q vão auxiliar para exibir ou não os Recebimentos ...
        $id_fornecedor_antigo       = '';
        $id_reprentante_antigo      = '';

        $total_fornecedores         = 0;//Acumula nessa variável o Total de Fornecedores Selecionados
        $total_representantes       = 0;//Acumula nessa variável o Total de Representantes Selecionados
        for($i = 0; $i < count($vetor_conta_apagar); $i++) {//Disparo do loop
/****************************************************************************************************/
            //Essa verificação consiste em verificar se foi preenchido o ICMS quando é forçado no Produto ...
            $sql = "SELECT pf.`id_produto_financeiro` 
                    FROM `contas_apagares` ca 
                    INNER JOIN `produtos_financeiros` pf ON pf.`id_produto_financeiro` = ca.`id_produto_financeiro` AND pf.`forcar_icms` = 'S' 
                    WHERE ca.`id_conta_apagar` = '$vetor_conta_apagar[$i]' 
                    AND ca.`valor_icms` = '0.00' ";
            $campos_verificar_forcar = bancos::sql($sql);
            if(count($campos_verificar_forcar) == 1) {//Significa que nessa conta precisa estar sendo preenchido o ICMS
?>
            <Script Language = 'JavaScript'>
                alert('EXISTE(M) CONTA(S) À PAGAR EM QUE NÃO FOI DIGITADO O ICMS À CREDITAR !!!\nPREENCHA ESSE CAMPO PRIMEIRO ANTES DE EFETUAR A QUITAÇÃO DESSA CONTA !')
                window.close()
//Abrindo o Pop-Up de Alterar Conta p/ que o usuário venha preencher o ICMS ...
                nova_janela('../../alterar.php?id_conta_apagar=<?=$vetor_conta_apagar[$i];?>', 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')
            </Script>
<?
                exit;
            }
/****************************************************************************************************/
            $calculos_conta_pagar = financeiros::calculos_conta_pagar($vetor_conta_apagar[$i]);

/*Aqui eu seleciono as Datas da Conta porque não posso pagar uma conta em que a Data de Emissão é maior
do que a Data de Vencimento, o Valor a ser pago daquela conta à apagar corrent e o Tipo de Moeda*/
            $sql = "SELECT ca.`id_fornecedor`, ca.`id_nfe`, ca.`id_representante`, ca.`id_tipo_moeda`, 
                    ca.`id_empresa`, ca.`numero_conta`, ca.`data_emissao`, ca.`data_vencimento`, 
                    ca.`data_vencimento_alterada`, ca.`valor`, ca.`taxa_juros`, ca.`valor_pago`, tm.`simbolo` 
                    FROM `contas_apagares` ca 
                    INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = ca.`id_tipo_moeda` 
                    WHERE ca.`id_conta_apagar` = '$vetor_conta_apagar[$i]' LIMIT 1 ";
            $campos = bancos::sql($sql);
            $id_fornecedor              = $campos[0]['id_fornecedor'];
            $id_nfe                     = $campos[0]['id_nfe'];
            $id_representante           = $campos[0]['id_representante'];
            $id_tipo_moeda              = $campos[0]['id_tipo_moeda'];
            /*Renomeio essa variável p/ "$id_empresa_loop" não dar conflito com a id_empresa 
            que existe na Sessão ...*/
            $id_empresa_loop            = $campos[0]['id_empresa'];
            $numero_conta               = $campos[0]['numero_conta'];
            $data_emissao               = $campos[0]['data_emissao'];
            $data_vencimento            = $campos[0]['data_vencimento'];
            $data_vencimento_alterada   = $campos[0]['data_vencimento_alterada'];
            //Se a Data de Emissão é maior do que a Data de Vencimento Alterada, eu não posso pagar a Conta ...
            if($data_emissao > $data_vencimento_alterada) $id_contas_inadiplentes[] = $vetor_conta_apagar[$i];
            
            $valor_pagar    = $campos[0]['valor'];
            $taxa_juros     = $campos[0]['taxa_juros'];
            
            $valor_pagar+= $calculos_conta_pagar['valores_extra'];
            $valor_pagar-= $campos[0]['valor_pago'];
            
            //Aqui faz esse cálculo só para ver o quanto resta à pagar da Conta ...
            if($campos[0]['id_tipo_moeda'] == 1) {//Reais
                /**********************************Observações Cruciais**********************************
                $campos[0]['valor_reajustado'] -> Campo que sempre guarda o valor em R$ c/ juros ...
                *****************************************************************************************/
                //Sempre o Valor Reajustado terá prioridade sobre o Valor de Origem da Conta ...
                //$valor_pagar = ($campos[0]['valor_reajustado'] > 0) ? $campos[0]['valor_reajustado'] : $campos[0]['valor'];

                $valor_pagar_real   = $valor_pagar;
            }else if($campos[0]['id_tipo_moeda'] == 2) {//Dólar
                //O campo $campos[0]['valor_pago'], guarda o valor pago do Tp de moeda da Conta à Pagar ...
                
                $valor_pagar_real   = $valor_pagar * $valor_dolar;
            }else if($campos[0]['id_tipo_moeda'] == 3) {//Euro
                //O campo $campos[0]['valor_pago'], guarda o valor pago do Tp de moeda da Conta à Pagar ...
                
                $valor_pagar_real   = $valor_pagar * $valor_euro;
            }
            //Arredondo o Valor, p/ garantir mais precisão ...
            $valor_pagar_real = round(round($valor_pagar_real, 3), 2);
            //Aqui eu tenho o total da soma de várias parcelas ...
            $valor_total_pagar_real+= $valor_pagar_real;

            $simbolo            = $campos[0]['simbolo'];
            if(strlen($simbolo) == 1) $simbolo.= "&nbsp;&nbsp;";
?>
    <tr class='linhanormal' align='center'>
        <td>
            <a href="javascript:nova_janela('../../alterar.php?id_conta_apagar=<?=$vetor_conta_apagar[$i];?>&pop_up=1', 'DETALHES', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '', '')" title='Detalhes de Contas à Pagar / Pagas' class='link'>
                <?=$numero_conta;?>
            </a>
        </td>
        <td>
            <?=data::datetodata($data_vencimento, '/');?>
        </td>
        <td>
            <?=data::datetodata($data_vencimento_alterada, '/');?>
        </td>
        <td align='left'>
        <?
            if($id_fornecedor > 0) {//Se existir Fornecedor então ...
                /***************************Controle para exibir ou não os Recebimentos***************************/
                if($id_fornecedor_antigo != $id_fornecedor) {
                    $id_fornecedor_antigo = $id_fornecedor;
                    $total_fornecedores++;//Aki significa que já mudou para outro Fornecedor
                }
                /**************************************************************************************************/
                $sql = "SELECT `razaosocial` 
                        FROM `fornecedores` 
                        WHERE `id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
                $campos_fornecedor = bancos::sql($sql);
                echo $campos_fornecedor[0]['razaosocial'];

                $retorno = financeiros::contas_em_aberto($id_fornecedor, 2, $id_emp, 2, 0, 'S');
                $qtde_contas = count($retorno['id_contas']);
//Significa que existem Contas à Receber desse Cliente
                if($qtde_contas > 0) {
            ?>
                    &nbsp;<img src = '../../../../../imagem/icones/outros.gif' width='33' height='20' border='0' title='Exite(m) <?=$qtde_contas;?> Conta(s) à Receber desse Cliente'>
            <?
                }
            }else {//Significa então que existe representante ...
                /***************************Controle para exibir ou não os Recebimentos***************************/
                if($id_representante_antigo != $id_representante) {
                    $id_representante_antigo = $id_representante;
                    $total_representantes++;//Aki significa que já mudou para outro Representante
                }
                /**************************************************************************************************/
                $sql = "SELECT `nome_fantasia` 
                        FROM `representantes` 
                        WHERE `id_representante` = '$id_representante' LIMIT 1 ";
                $campos_representante = bancos::sql($sql);
                echo $campos_representante[0]['nome_fantasia'];
                
                $retorno = financeiros::contas_em_aberto($id_representante, 3, $id_emp, 2, 0, 'S');
                $qtde_contas = count($retorno['id_contas']);
//Significa que existem Contas à Receber desse Cliente
                if($qtde_contas > 0) {
            ?>
                    &nbsp;<img src = '../../../../../imagem/icones/outros.gif' width='33' height='20' border='0' title='Exite(m) <?=$qtde_contas;?> Conta(s) à Receber desse Cliente'>
            <?
                }
            }

            //Se essa Conta à Pagar for vinculada a uma NF de Entrada, então executo a Query abaixo ...
            if($id_nfe > 0) {//Conta à Pagar com NF de Entrada ...
                //Verifico se essa NF de Entrada foi paga pelo Caixa de Compras ...
                $sql = "SELECT `pago_pelo_caixa_compras` 
                        FROM `nfe` 
                        WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
                $campos_nfe = bancos::sql($sql);
                //Paga pelo Cx de Compras ...
                if($campos_nfe[0]['pago_pelo_caixa_compras'] == 'S') echo '&nbsp;-&nbsp;<font color="red"><b>(PG CX COMPRAS)</b></font>';
            }
        ?>
        </td>
        <td align='center'>
        <?
            $empresa_conta = genericas::nome_empresa($id_empresa_loop);
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
            <?=number_format($taxa_juros, 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=$simbolo;?><input type='text' name='txt_valor_nac_est[]' id='txt_valor_nac_est<?=$i;?>' value="<?=number_format($campos[0]['valor'], 2, ',', '.');?>" title='Valor Nac / Est' size='8' maxlength='9' class='textdisabled' disabled>
        </td>
        <td align='right'>
            <?=$simbolo;?><input type='text' name='txt_valores_extras[]' id='txt_valores_extras<?=$i;?>' value="<?=number_format($calculos_conta_pagar['valores_extra'], 2, ',', '.');?>" title='Valores Extras' size='8' class='textdisabled' disabled>
            <input type='hidden' name='hdd_valores_extras[]' id='hdd_valores_extras<?=$i;?>' value="<?=number_format($calculos_conta_pagar['valores_extra'], 2, ',', '.');?>">
        </td>
        <td align='right'>
            <?=$simbolo;?><input type='text' name='txt_valor_pago[]' id='txt_valor_pago<?=$i;?>' value="<?=number_format($campos[0]['valor_pago'], 2, ',', '.');?>" title='Valor Pago' size='8' class='textdisabled' disabled>
        </td>
        <td align='right'>
            <input type='hidden' name='hdd_tipo_moeda[]' id='hdd_tipo_moeda<?=$i;?>' value='<?=$id_tipo_moeda;?>'>
            <?=$simbolo;?><input type='text' name='txt_saldo_a_pagar[]' id='txt_saldo_a_pagar<?=$i;?>' value='<?=number_format($valor_pagar, 2, ',', '.');?>' title='Valor Total' size='8' class='textdisabled' disabled>
        </td>
        <td align='right'>
            <input type='text' name='txt_valor_pagando[]' id='txt_valor_pagando<?=$i;?>' value='<?=number_format($valor_pagar, 2, ',', '.');?>' size='8' maxlength='11' title='Digite o Valor' onkeyup="verifica(this, 'moeda_especial', '2', '1', event);calcular_conta_pagar_por_linha('<?=$i;?>');valor_total_contas_pagar()" class='caixadetexto'>
        </td>
        <td align='right'>
<?
//Essa variável vai me auxiliar para controlar o botão de Salvar lá da Parte de Contas à Receber, através do JavaScript ...
            if($valor_pagar_real < 0) $contas_valor_negativo++;
?>
            <input type='text' name='txt_valor_pagando_rs[]' id='txt_valor_pagando_rs<?=$i;?>' value='<?=number_format($valor_pagar_real, 2, ',', '.');?>' size='8' title='Valor Pagando em R$' onfocus="document.getElementById('txt_valor_pagando<?=$i?>').focus()" class='textdisabled'>
        </td>
        <td align='right'>
            <input type='text' name='txt_valor_pagar_restante[]' id='txt_valor_pagar_restante<?=$i;?>' value='0,00' title='Valor Restante' size='8' class='textdisabled' disabled>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='right'>
        <td colspan='13'>
            Total de Contas:
            <font color='yellow'>
                <?=count($vetor_conta_apagar);?>
            </font>
            &nbsp;-&nbsp;
            Valor Total R$ :
            <input type='text' name='txt_total_pagamento' value="<?=number_format($valor_total_pagar_real, 2, ',', '.');?>" size='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação:
        </td>
        <td colspan='12'>
            <textarea name='txt_observacao' title='Digite a Observação' maxlength='600' rows='5' cols='120' class='caixadetexto'></textarea>
        </td>
    </tr>
<?
/*Se eu tiver pelo menos 1 conta em que a Data de Emissão é maior do que a Data de Vencimento, eu não posso 
estar pagando as demais Conta ...*/
        if(count($id_contas_inadiplentes) > 0) {
//Alert informando ao usuário que existem contas com inadiplência ...
?>
        <Script Language = 'JavaScript'>
            alert('NÃO É POSSÍVEL EFETUAR O PAGAMENTO DA(S) CONTA(S) !\nEXISTEM CONTA(S) EM QUE A DATA DE EMISSÃO É MAIOR DO QUE A DATA DE VENCIMENTO !!!')
        </Script>
<?
            $disabled = 'disabled';//Serve para travar o Botão return validar_contas_pagar_receber() de Pagar à Conta ...
?>
    <tr class='linhanormal'>
        <td colspan='13'>
            <font color='red'>
                <b>CONTA(S) QUE ESTÃO COM A DATA DE EMISSÃO INCOMPATÍVEL(IS):</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='3'>
            <font color='blue'>
                <b>N.º / CONTA</b>
            </font>
        </td>
        <td colspan='3'>
            <font color='blue'>
                <b>FORNECEDOR</b>
            </font>
        </td>
        <td colspan='3'>
            <font color='blue'>
                <b>DATA DE EMISSÃO</b>
            </font>
        </td>
        <td colspan='2'>
            <font color='blue'>
                <b>DATA DE VENCIMENTO</b>
            </font>
        </td>
        <td colspan='2'>
            <font color='blue'>
                <b>DATA DE VENCIMENTO ALTERADA</b>
            </font>
        </td>
    </tr>
<?
//Listagem das contas que não podem ser pagas devido a Data de Emissão estar incoerente ...
            for($i = 0; $i < count($id_contas_inadiplentes); $i++) {
                $sql = "SELECT ca.`id_fornecedor`, ca.`id_representante`, ca.`numero_conta`, ca.`data_emissao`, ca.`data_vencimento` 
                        FROM `contas_apagares` ca 
                        WHERE ca.`id_conta_apagar` = '$vetor_conta_apagar[$i]' LIMIT 1 ";
                $campos_contas_inadiplentes = bancos::sql($sql);
?>
    <tr class='linhanormal'>
        <td colspan='3'>
            <?=$campos_contas_inadiplentes[0]['numero_conta'];?>
        </td>
        <td colspan='3'>
        <?
            if($campos_contas_inadiplentes[0]['id_fornecedor'] > 0) {//Se existir Fornecedor então ...
                $sql = "SELECT `razaosocial` 
                        FROM `fornecedores` 
                        WHERE `id_fornecedor` = '".$campos_contas_inadiplentes[0]['id_fornecedor']."' LIMIT 1 ";
                $campos_fornecedor = bancos::sql($sql);
                echo $campos_fornecedor[0]['razaosocial'];
            }else {//Significa então que existe representante ...
                $sql = "SELECT `nome_fantasia` 
                        FROM `representantes` 
                        WHERE `id_representante` = '".$campos_contas_inadiplentes[0]['id_representante']."' LIMIT 1 ";
                $campos_representante = bancos::sql($sql);
                echo $campos_representante[0]['nome_fantasia'];
            }
        ?>
        </td>
        <td colspan='3'>
            <?=data::datetodata($campos_contas_inadiplentes[0]['data_emissao'], '/');?>
        </td>
        <td colspan='2'>
            <?=data::datetodata($campos_contas_inadiplentes[0]['data_vencimento'], '/');?>
        </td>
        <td colspan='2'>
            <?=data::datetodata($campos_contas_inadiplentes[0]['data_vencimento'], '/');?>
        </td>
    </tr>
<?

            }
        }
        $id_contas_receberes = (count($retorno['id_contas']) > 0) ? implode($retorno['id_contas'], ',') : 0;
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style='color:#ff9900' class='botao'>
            <input type='button' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' onclick='return validar_contas_pagar_receber()' class='botao' <?=$disabled;?>>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(parent)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<Script Language = 'JavaScript'>
/*Eu tive que colocar essa função mais abaixo p/ evitar alguns problemas de verificação de formulário, outra 
que o SQL que verifica as Contas à Receber estão na parte de baixo do formulário ...*/
function validar_contas_pagar_receber() {
//Tipo de Pagamento ...
    if(document.form.cmb_tipo_pagamento.value == '') {
        alert('SELECIONE O TIPO DE PAGAMENTO !')
        document.form.cmb_tipo_pagamento.focus()
        return false
    }
//Conta Corrente ...
    if(document.form.hdd_status_ch.value >= 1) {
        if(document.form.cmb_conta_corrente.value == '') {
            alert('SELECIONE A CONTA CORRENTE !')
            document.form.cmb_conta_corrente.focus()
            return false
        }
    }
//Cheque ...
    if(document.form.hdd_status_ch.value == 2) {
        if(document.form.cmb_cheque.value == '') {
            alert('SELECIONE O CHEQUE !')
            document.form.cmb_cheque.focus()
            return false
        }
    }
/******************************************************************************/
/*************************Formulário de Contas à Pagar*************************/
/******************************************************************************/
    var elementos   = document.form.elements
    var linhas      = (typeof(elementos['txt_valor_pagar_restante[]'][0]) == 'undefined') ? 1 : (elementos['txt_valor_pagar_restante[]'].length)
//Data de Emissão
    if(!data('form', 'txt_data_pagamento', '4000', 'PAGAMENTO')) {
        return false
    }
//Valor Pagando ...
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('txt_valor_pagando'+i).value == '') {
            alert('DIGITE O VALOR PAGANDO !')
            document.getElementById('txt_valor_pagando'+i).select()
            return false
        }
    }
/******************************************************************************/
/************************Formulário de Contas à Receber************************/
/******************************************************************************/
    var contas_receber_selecionadas = 0
    if(typeof(document.form_contas_receber) == 'object') {//Se existir Contas à Receber ...
        var elementos_contas_receber    = document.form_contas_receber.elements
        //Prepara a Tela p/ poder gravar no BD ...
        if(typeof(elementos_contas_receber['chkt_conta_receber[]'][0]) == 'undefined') {
            var linhas = 1//Existe apenas 1 único elemento ...
        }else {
            var linhas = (elementos_contas_receber['chkt_conta_receber[]'].length)
        }
        //Aqui faz toda essa preparação para poder gravar no BD ...
        for(var i = 0; i < linhas; i++) {
            if(document.getElementById('chkt_conta_receber'+i).checked == true) {
                contas_receber_selecionadas++//Verifico se foi selecionada pelo menos 1 Conta à Receber ...
                break;
            }
        }
    }

    if(contas_receber_selecionadas > 0) {//Tivemos pelo menos uma Conta à Receber selecionada ...
        /***************************************************************************************/
        //Verifico se o "Total das Contas à Receber" selecionados é maior que o Total à Pagar ...
        var total_recebimento   = eval(strtofloat(document.form_contas_receber.txt_total_recebimento.value))
        var total_pagamento     = eval(strtofloat(document.form.txt_total_pagamento.value))
        
        if(total_recebimento != total_pagamento) {
            alert('NÃO É POSSÍVEL EFETUAR O ACERTO DE CONTA(S) !!! \n\nTOTAL À RECEBER TEM QUE SER EXATAMENTE IGUAL AO TOTAL À PAGAR !')
            return false
        }
        
/*Observação: 

A partir do dia 31/10/2014 o sistema passou a permitir que se fizesse acerto de Contas independente 
de termos Contas Livres de Débito ...*/
/*****************************************************************************************/
        var contas_valor_negativo = eval('<?=$contas_valor_negativo;?>')
        if(contas_valor_negativo > 0) {
            //alert('EXISTEM CONTA(S) À PAGAR COM VALOR NEGATIVO !\nDEVIDO A ISTO ESSA FUNÇÃO ESTÁ INDISPONÍVEL !')
            //return false

            //Mudança feita no dia 19/09/2014 ...
            var resposta = confirm('EXISTEM CONTA(S) À PAGAR COM VALOR NEGATIVO !\n\nDESEJA CONTINUAR ?')
            if(resposta == false) return false
        }
        
        /***************************************************************************************/
        //Trato todos os dados do Formulário "Contas à Pagar" através dessa função abaixo ...
        tratar_formulario_contas_a_pagar()

        //Trato todos os dados do Formulário "Contas à Receber" através das linhas abaixo ...
        for(var i = 0; i < linhas; i++) {
            if(document.getElementById('chkt_conta_receber'+i).checked == true) document.getElementById('txt_valor_recebendo_rs'+i).value = strtofloat(document.getElementById('txt_valor_recebendo_rs'+i).value)
        }
        //Igualo os campos do form de baixo com os dados do form de cima
        document.form_contas_receber.txt_valor_dolar.value              = document.form.txt_valor_dolar.value
        document.form_contas_receber.txt_valor_euro.value               = document.form.txt_valor_euro.value
        document.form_contas_receber.txt_data_pagamento.value           = document.form.txt_data_pagamento.value
        document.form_contas_receber.chkt_zerar_valores_extras.value    = document.form.chkt_zerar_valores_extras.value
        document.form_contas_receber.txt_total_recebimento.disabled     = false
        
        //Travo o botão p/ que o usuário não fique clicando no mesmo várias vezes, após ter submetido ...
        document.form.cmd_salvar.className  = 'textdisabled'
        document.form.cmd_salvar.disabled   = true
        limpeza_moeda('form_contas_receber', 'txt_total_recebimento, ')
        document.form_contas_receber.submit()
/******************************************************************************/
    }else {//Somente Contas à Pagar ...
/*Observação: 

A partir do dia 31/10/2014 o sistema passou a permitir que se fizesse acerto de Contas independente 
de termos Contas Livres de Débito ...*/
/*****************************************************************************************/
        var contas_valor_negativo = eval('<?=$contas_valor_negativo;?>')
        if(contas_valor_negativo > 0) {
            //alert('EXISTEM CONTA(S) À PAGAR COM VALOR NEGATIVO !\nDEVIDO A ISTO ESSA FUNÇÃO ESTÁ INDISPONÍVEL !')
            //return false

            //Mudança feita no dia 19/09/2014 ...
            var resposta = confirm('EXISTEM CONTA(S) À PAGAR COM VALOR NEGATIVO !\n\nDESEJA CONTINUAR ?')
            if(resposta == false) return false
        }
        
        //Trato todos os dados do Formulário "Contas à Pagar" através dessa função abaixo ...
        tratar_formulario_contas_a_pagar()
        document.form.submit()
    }
}
</Script>
</form>
<!--
/*******************************************Contas à Receber**********************************************/
Aki é no caso de o Fornecedor ser Cliente também-->
<?
//E só exibirá quando eu tiver apenas "um Fornecedor" ou "um Representante" selecionado ...
    if($qtde_contas > 0 && ($total_fornecedores == 1 || $total_representantes == 1)) {
//Tenho que trampar com 2 forms p/ não dar problema nas funções e objetos do outro form da parte de pagto.
?>
<Script language = 'JavaScript'>
//Essa função calcula o valor da conta apenas na linha corrente
function calcular_conta_receber_por_linha(indice, valor_receber, tipo_moeda) {
/*Essa variável valor à receber serve para me retornar o quanta que falta da
conta para receber na moeda R$, U$ ou Euro*/
    valor_receber                   = eval(strtofloat(valor_receber))//Vem na Moeda Corrente da Conta mesmo R$, U$, Euro
    var valor_recebendo_aux         = new Array('<?=$qtde_contas;?>')
    var data_atual                  = "<?=date('d/m/Y');?>"
//Rotina para o cálculo de Juros em cima do valor da Conta à Receber, caso o usuário venha colocar ou tirar os juros
<?
    for($i = 0; $i < $qtde_contas; $i++) {
        $sql = "SELECT * 
                FROM `contas_receberes` 
                WHERE `id_conta_receber` = '".$retorno['id_contas'][$i]."' LIMIT 1 ";
        $campos_conta_receber = bancos::sql($sql);
?>
        manual                      = eval('<?=$campos_conta_receber[0]["manual"];?>')
        valor_recebido              = eval('<?=$campos_conta_receber[0]["valor_pago"];?>')//Vem na Moeda Corrente mesmo R$, U$, Euro
/*Aqui essas variáveis são para o cálculo da fórmula do Roberto*/
        valor                       = eval('<?=$campos_conta_receber[0]["valor"];?>')
        valor_desconto              = eval('<?=$campos_conta_receber[0]["valor_desconto"];?>')
        valor_abatimento            = eval('<?=$campos_conta_receber[0]["valor_abatimento"];?>')
        valor_despesas              = eval('<?=$campos_conta_receber[0]["valor_despesas"];?>')
        taxa_juros                  = eval('<?=$campos_conta_receber[0]["taxa_juros"];?>')
        valor_juros                 = eval('<?=$campos_conta_receber[0]["valor_juros"];?>')
        data_vencimento             = '<?=data::datetodata($campos_conta_receber[0]["data_vencimento"], "/");?>'
        id_tipo_moeda               = eval('<?=$campos_conta_receber[0]["id_tipo_moeda"];?>')
        
        if(document.form_contas_receber.chkt_zerar_juros.checked == true) {
//Aqui é o valor a receber na moeda da conta R$, U$
            valor_receber = (valor - valor_desconto - valor_abatimento + valor_despesas)
        }else {
            if(manual == 1) {
//Aqui é o valor a receber na moeda da conta R$, U$
                valor_receber = (valor - valor_desconto - valor_abatimento + valor_despesas) + eval(valor_juros)
            }else {
                if(taxa_juros > 0) {
//A variável dias equivale a data atual até a data de vecimento
                    dias = diferenca_datas(data_vencimento, data_atual)
                    taxa_juros_dias_venc = (taxa_juros / 30/ 100) * dias
                }else {
                    taxa_juros_dias_venc = 0
                }
//Aqui é o valor a receber na moeda da conta R$, U$
                valor_receber = (valor - valor_desconto - valor_abatimento + valor_despesas) * (taxa_juros_dias_venc + 1)
            }
        }
        valor_receber                   = valor_receber - valor_recebido
        valor_receber_real              = valor_receber
        valor_recebendo_aux['<?=$i?>']  = valor_receber_real
<?
    }
?>
//Esse valor a Receber Corrente é sempre em R$, daí o Sys é que tem que transformar para Dólar ou Euro, caso necessário
    valor_a_receber_corrente            = eval(strtofloat(document.getElementById('txt_valor_recebendo_rs'+indice).value))
//Aqui é quando a caixa valor recebendo está vázia
    if(typeof(valor_a_receber_corrente) == 'undefined' || valor_a_receber_corrente == '') valor_a_receber_corrente = 0
//Verifica o tipo da moeda da Conta Dólar ou Euro
    if(tipo_moeda == 1) {
        valor_moeda = 1
    }else if(tipo_moeda == 2) {//Dólar
        valor_moeda = eval(strtofloat(document.form.txt_valor_dolar.value))
    }else if(tipo_moeda == 3) {//Euro
        valor_moeda = eval(strtofloat(document.form.txt_valor_euro.value))
    }
/*Aqui é o quanto falta para receber da conta na moeda da conta R$, U$

/Esse variável valor_recebendo_aux[linha] é o valor que estou devendo da conta na moeda correnta daquela conta
e nela também já está embutido todas as taxas de juros, acréscimos, etc ..
Na outra parte -> (valor_a_receber_corrente / valor_moeda), eu transformo o valor em R$ para moeda da 
conta R$, U$ ...*/
    document.getElementById('txt_valor_receber'+indice).value = (valor_recebendo_aux[indice]) - (valor_a_receber_corrente / valor_moeda)
    if(document.getElementById('txt_valor_receber'+indice).value == '-Infinity' || document.getElementById('txt_valor_receber'+indice).value == 'NaN' || document.getElementById('txt_valor_receber'+indice).value == '') {
        document.getElementById('txt_valor_receber'+indice).value   = valor_receber
        document.getElementById('txt_valor_recebendo'+indice).value = '0,00'
    }else {
        document.getElementById('txt_valor_receber'+indice).value = arred(document.getElementById('txt_valor_receber'+indice).value, 2, 1)
    }
    valor_total_contas_receber()
}

function zerar_juros() {
    var elementos_contas_receber    = document.form_contas_receber.elements
    
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos_contas_receber['chkt_conta_receber[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos_contas_receber['chkt_conta_receber[]'].length)
    }
    
    var valor_receber_real_vetor    = new Array('<?=$qtde_contas;?>')
    var y                           = 0
    var data_atual                  = "<?=date('d/m/Y');?>"
//Rotina para o cálculo de Juros em cima do valor da Conta à Receber, caso o usuário venha colocar ou tirar os juros
<?
    for($i = 0; $i < $qtde_contas; $i++) {
//Aqui busco todos os campos
        $sql = "SELECT * 
                FROM `contas_receberes` 
                WHERE `id_conta_receber` = '".$retorno['id_contas'][$i]."' LIMIT 1 ";
        $campos_conta_receber = bancos::sql($sql);
?>
        manual          = eval('<?=$campos_conta_receber[0]["manual"];?>')
        valor_recebido  = eval('<?=$campos_conta_receber[0]["valor_pago"];?>')
/*Aqui essas variáveis são para o cálculo da fórmula do Roberto*/
        valor           = eval('<?=$campos_conta_receber[0]["valor"];?>')
        valor_desconto  = eval('<?=$campos_conta_receber[0]["valor_desconto"];?>')
        valor_abatimento = eval('<?=$campos_conta_receber[0]["valor_abatimento"];?>')
        valor_despesas  = eval('<?=$campos_conta_receber[0]["valor_despesas"];?>')
        taxa_juros      = eval('<?=$campos_conta_receber[0]["taxa_juros"];?>')
        valor_juros     = eval('<?=$campos_conta_receber[0]["valor_juros"];?>')
        data_vencimento = '<?=data::datetodata($campos_conta_receber[0]["data_vencimento"], "/");?>'
        id_tipo_moeda   = eval('<?=$campos_conta_receber[0]["id_tipo_moeda"];?>')

        if(document.form_contas_receber.chkt_zerar_juros.checked == true) {
//Aqui é o valor a receber na moeda da conta R$, U$
            valor_receber = (valor - valor_desconto - valor_abatimento + valor_despesas)
        }else {
            if(manual == 1) {
//Aqui é o valor a receber na moeda da conta R$, U$
                valor_receber = (valor - valor_desconto - valor_abatimento + valor_despesas) + eval(valor_juros)
            }else {
                if(taxa_juros > 0) {
//A variável dias equivale a data atual até a data de vecimento
                    dias = diferenca_datas(data_vencimento, data_atual)
                    taxa_juros_dias_venc = (taxa_juros / 30/ 100) * dias
                }else {
                    taxa_juros_dias_venc = 0
                }
//Aqui é o valor a receber na moeda da conta R$, U$
                valor_receber = (valor - valor_desconto - valor_abatimento + valor_despesas) * (taxa_juros_dias_venc + 1)
            }
        }
        valor_receber = valor_receber - valor_recebido
        valor_receber_real = valor_receber
//Essa variável é o que o usuário tem pra receber da Conta com todos os juros, acréscimos, ...
        valor_receber_real_vetor['<?=$i?>'] = valor_receber_real
<?
    }
?>
//Valor em R$
    valor_moeda_real = 1
//Valor para Dólar
    valor_moeda_dolar   = (document.form.txt_valor_dolar.value == '') ? 1 : eval(strtofloat(document.form.txt_valor_dolar.value))
//Valor para Euro
    valor_moeda_euro    = (document.form.txt_valor_euro.value == '') ? 1 : eval(strtofloat(document.form.txt_valor_euro.value))

    for(var i = 0; i < linhas; i++) {
        document.getElementById('txt_valor_receber'+i).value = '0,00'
//Verifica o Tipo de moeda da Conta Corrente
        if(document.getElementById('hdd_tipo_moeda'+i).value == 1) {//R$
//Aqui é o Cálculo para o "Valor Recebendo"
            document.getElementById('txt_valor_recebendo_rs'+i).value   = valor_receber_real_vetor[y] * valor_moeda_real
        }else if(document.getElementById('hdd_tipo_moeda'+i).value == 2) {//U$
//Aqui é o Cálculo para o "Valor Recebendo"
            document.getElementById('txt_valor_recebendo_rs'+i).value   = valor_receber_real_vetor[y] * valor_moeda_dolar
        }else if(document.getElementById('hdd_tipo_moeda'+i).value == 3) {//Euro
//Aqui é o Cálculo para o "Valor Recebendo"
            document.getElementById('txt_valor_recebendo_rs'+i).value   = valor_receber_real_vetor[y] * valor_moeda_euro
        }
        document.getElementById('txt_valor_recebendo_rs'+i).value = arred(document.getElementById('txt_valor_recebendo_rs'+i).value, 2, 1)
        valor_total_contas_receber()
        y++
    }
}

//Faz um somatório do Valor de todas as contas em que está selecionado o checkbox, bem simplesinho
function valor_total_contas_receber() {
    var elementos_contas_receber        = document.form_contas_receber.elements
    var valor_total_a_receber_corrente  = 0
    
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos_contas_receber['chkt_conta_receber[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos_contas_receber['chkt_conta_receber[]'].length)
    }
    
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_conta_receber'+i).checked == true) {
            valor_a_receber_corrente            = eval(strtofloat(document.getElementById('txt_valor_recebendo_rs'+i).value))

            if(typeof(valor_a_receber_corrente) == 'undefined') valor_a_receber_corrente = 0
            valor_total_a_receber_corrente = valor_total_a_receber_corrente + eval(valor_a_receber_corrente)
        }
    }
    document.form_contas_receber.txt_total_recebimento.value = valor_total_a_receber_corrente
    document.form_contas_receber.txt_total_recebimento.value = arred(document.form_contas_receber.txt_total_recebimento.value, 2, 1)
}

function redefinir2() {
    var resposta = confirm('DESEJA REDEFINIR ?')
    if(resposta == true) {
        document.form_contas_receber.chkt_tudo.checked = false
        selecionar('form_contas_receber', 'chkt_tudo', totallinhas, '#E8E8E8')
        document.form_contas_receber.reset()
    }else {
        return false
    }
}
</Script>
<form name='form_contas_receber' method='post' action='<?=$PHP_SELF.'?passo=1';?>'>
<table width='98%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Conta(s) à Receber
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form_contas_receber', 'chkt_tudo', totallinhas, '#E8E8E8');valor_total_contas_receber()" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td>
            N.º / <br/>Conta
        </td>
        <td>
            Cliente / <br/>Descrição da Conta
        </td>
        <td>
            <font title='Empresa' style='cursor:help'>
                E
            </font>
        </td>
        <td>
            Representante
        </td>
        <td>
            Data Venc
        </td>
        <td>
            Tx Juros
        </td>
        <td>
            Valor <br/>Total
        </td>
        <td>
            Valor <br/>Recebido
        </td>
        <td>
            Valor <br/>à Receber
        </td>
        <td>
            Valor <br/>Recebendo
        </td>
    </tr>
<?
        for($i = 0; $i < $qtde_contas; $i++) {
            //Busca de Alguns Dados da Conta à Receber para verificar se está foi recebida parcialmente ...
            $sql = "SELECT c.`razaosocial`, cr.`id_empresa`, cr.`id_tipo_moeda`, cr.`id_nf`, cr.`num_conta`, cr.`data_vencimento`, 
                    cr.`valor`, cr.`taxa_juros`, cr.`valor_pago`, r.`nome_fantasia`, tm.`simbolo` 
                    FROM `contas_receberes` cr 
                    LEFT JOIN `representantes` r ON r.`id_representante` = cr.`id_representante` 
                    INNER JOIN `clientes` c ON c.`id_cliente` = cr.`id_cliente` 
                    INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = cr.`id_tipo_moeda` 
                    WHERE cr.`id_conta_receber` = ".$retorno['id_contas'][$i]." LIMIT 1 ";
            $campos_contas_receber      = bancos::sql($sql);
            $id_tipo_moeda              = $campos_contas_receber[0]['id_tipo_moeda'];
            $valor_conta                = $campos_contas_receber[0]['valor'];
            $taxa_juros                 = $campos_contas_receber[0]['taxa_juros'];
            $valor_recebido             = $campos_contas_receber[0]['valor_pago'];
            $moeda                      = $campos_contas_receber[0]['simbolo'];
            if(strlen($moeda) == 1)     $moeda.= '&nbsp;&nbsp;';
            $calculos_conta_receber     = financeiros::calculos_conta_receber($retorno['id_contas'][$i]);
            $valor_receber_real         = $calculos_conta_receber['valor_reajustado'];
            if($valor_receber_real == '-0.00') $valor_receber_real = 0;
?>
    <tr class='linhanormal' onclick="checkbox_habilita('<?=$i;?>', '#E8E8E8');valor_total_contas_receber()" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_conta_receber[]' id='chkt_conta_receber<?=$i;?>' value="<?=$retorno['id_contas'][$i];?>" onclick="checkbox_habilita('<?=$i;?>', '#E8E8E8');valor_total_contas_receber()" class='checkbox'>
        </td>
        <td>
            <a href="javascript:nova_janela('../../../../faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=<?=$campos_contas_receber[0]['id_nf'];?>&pop_up=1', 'DETALHES', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <?=$campos_contas_receber[0]['num_conta'];?>
            </a>
        </td>
        <td align='left'>
            <a href="javascript:nova_janela('../../../recebimento/alterar.php?pop_up=1&id_conta_receber=<?=$retorno['id_contas'][$i];?>', 'DETALHES', '', '', '', '', 520, 950, 'c', 'c', '', '', 's', 's', '', '', '', '')" title='Parcelas Recebidas' class='link'>
            <?
                if(!empty($campos_contas_receber[0]['razaosocial']) && $campos_contas_receber[0]['razaosocial'] != '&nbsp;') {
                    echo $campos_contas_receber[0]['razaosocial'];
                    $sql = "SELECT livre_debito 
                            FROM `nfs` 
                            WHERE `id_nf` = '".$campos_contas_receber[0]['id_nf']."' LIMIT 1 ";
                    $campos_nf = bancos::sql($sql);
                    if($campos_nf[0]['livre_debito'] == 'S') echo '<font color="darkgreen" title="Livre de Débito Propaganda / Marketing" style="cursor:help"><b> (LD)</b></font>';
                }

                if($campos_contas_receber[0]['descricao_conta'] == '') {
                    echo '&nbsp;';
                }else {
                    echo $campos_contas_receber[0]['descricao_conta'];
                }
            ?>
            </a>
        </td>
        <td align='center'>
        <?
            $empresa_conta = genericas::nome_empresa($campos_contas_receber[0]['id_empresa']);
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
            <?=$campos_contas_receber[0]['nome_fantasia'];?>
        </td>
        <td>
            <?=data::datetodata($campos_contas_receber[0]['data_vencimento'], '/');?>
        </td>
        <td>
            <?=number_format($taxa_juros, 2, ',', '.');?>
        </td>
        <td align='right'>
            <input type='hidden' name='hdd_tipo_moeda[]' id='hdd_tipo_moeda<?=$i;?>' value='<?=$id_tipo_moeda;?>'>
            <?=$moeda;?><input type='text' name='txt_saldo_a_pagar[]' value="<?=str_replace('.', ',', $valor_conta);?>" title="Valor Total" size="12" maxlength="15" class='textdisabled' disabled>
        </td>
        <td align='right'>
            <?=$moeda;?><input type='text' name='txt_valor_recebido[]' value="<?=number_format($valor_recebido, 2, ',', '.');?>" title="Valor Recebido" size="12" maxlength="15" class='textdisabled' disabled>
        </td>
        <td align='right'>
            <?=$moeda;?><input type='text' name='txt_valor_receber[]' id='txt_valor_receber<?=$i;?>' value='0,00' title='Valor à Receber' size='12' maxlength='15' class='textdisabled' disabled>
        </td>
        <td align='right'>
            R$ <input type='text' name='txt_valor_recebendo_rs[]' id='txt_valor_recebendo_rs<?=$i;?>' value="<?=number_format($valor_receber_real, '2', ',', '.');?>" title="Digite o Valor" size="12" maxlength="15" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_conta_receber_por_linha('<?=$i;?>', '<?=$valor_receber_real;?>', '<?=$id_tipo_moeda;?>')" onclick="checkbox_habilita('<?=$i;?>', '#E8E8E8');return focos(this)" class='textdisabled' disabled>
        </td>
    </tr>
<?
        }
?>
    <tr>
        <td class='linhadestaque' colspan='9'>
            Total de Contas: 
            <font color='yellow'>
                <?=$qtde_contas;?>
            </font>
            &nbsp;-
            <input type='checkbox' name='chkt_zerar_juros' value='1' onclick='zerar_juros()' title='Zerar Juros' id='zerar' class='checkbox'>
            <label for='zerar'>
                Zerar Juros
            </label>
        </td>
        <td class='linhadestaque' align='right'>
            Valor Total R$:
        </td>
        <td class='linhadestaque' align='right'>
            <input type='text' name='txt_total_recebimento' value='0,00' size='12' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick='redefinir2()' style='color:#ff9900' class='botao'>
        </td>
    </tr>
</table>
<!--******************Aqui eu puxo os dados do form Número 1******************-->
<!--Porque faço isso ??? Porque só submeto o Formulário 2, perdendo referência dos dados do formulário 1-->
<input type='hidden' name='id_conta_apagar' value='<?=$id_conta_apagar;?>'>
<input type='hidden' name='txt_valor_dolar'>
<input type='hidden' name='txt_valor_euro'>
<input type='hidden' name='txt_data_pagamento'>
<input type='hidden' name='chkt_zerar_valores_extras'>
<!--**************************************************************************-->
<?
    }
?>
<!--/*************************************Fim de Contas à Receber****************************************/-->
</form>
</body>
</html>
<?}?>