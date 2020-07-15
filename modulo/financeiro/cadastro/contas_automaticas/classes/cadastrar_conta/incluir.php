<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/genericas.php');

if($id_empresa_menu == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/cadastro/contas_automaticas/albafer/index.php';
}else if($id_empresa_menu == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/cadastro/contas_automaticas/tool_master/index.php';
}else if($id_empresa_menu == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/cadastro/contas_automaticas/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../../');

//Procedimento normal de quando se carrega a Tela ...
$id_fornecedor = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_fornecedor'] : $_GET['id_fornecedor'];

$mensagem[1] = "<font class='confirmacao'>CONTA À PAGAR AUTOMÁTICA INCLUIDA COM SUCESSO.</font>";

if(!empty($_POST[txt_numero_conta])) {
    $data_vencimento = data::datatodate($_POST['txt_data_vencimento'], '-');
    if(empty($chkt_previsao))   $chkt_previsao = 0;
//Essa variável será utilizada mais abaixo ...
    $id_produto_financeiro_vs_fornecedor = $_POST[cmb_produto_financeiro];
    
/*Seleção dos dados bancários do fornecedor p/ gravar na tabela de "contas_apagares_automaticas" 
e ficar mais fácil no Futuro a busca de dados ...*/
    $sql = "SELECT banco, agencia, num_cc, correntista, cnpj_cpf 
            FROM `fornecedores_propriedades` 
            WHERE `id_fornecedor_propriedade` = '$_POST[cmb_conta_corrente]' LIMIT 1 ";
    $campos = bancos::sql($sql);

//Criando uma Conta Automática ...
    $sql = "INSERT INTO `contas_apagares_automaticas` (`id_conta_apagar_automatica`, `id_empresa`, `id_tipo_pagamento_recebimento`, `id_funcionario`, `id_tipo_moeda`, `id_produto_financeiro_vs_fornecedor`, `numero_conta`, `banco`, `agencia`, `num_cc`, `correntista`, `cnpj_cpf`, `dia_exibicao`, `tipo_data`, `intervalo`, `previsao`, `data_vencimento`, `qtde_parcelas`, `valor`, `valor_reajustado`, `observacao`, `status`, `data_sys`) VALUES (NULL, '$id_empresa_menu', '$id_tipo_pagamento', '$_SESSION[id_funcionario]', '$_POST[cmb_tipo_moeda]', '$id_produto_financeiro_vs_fornecedor', '$_POST[txt_numero_conta]', '".$campos[0]['banco']."', '".$campos[0]['agencia']."', '".$campos[0]['num_cc']."', '".$campos[0]['correntista']."', '".$campos[0]['cnpj_cpf']."', '$_POST[txt_dia_exibicao]', '$_POST[cmb_tipo_data]', '$_POST[txt_intervalo]', '$chkt_previsao', '$data_vencimento', '$_POST[txt_qtde_parcelas]', '$_POST[txt_valor]', '$_POST[txt_valor_reajustado]', '".strtolower($_POST[txt_observacao])."', '$_POST[cmb_tipo_automacao]', '".date('Y-m-d H:i:s')."') ";
    bancos::sql($sql);
    $valor = 1;
}

//Aqui eu puxo o último valor do dólar e do euro cadastrado ...
$valor_dolar            = genericas::moeda_dia('dolar');
$valor_euro             = genericas::moeda_dia('euro');
    
//Essa data será utilizada como tolerância máxima p/ cadastrar Datas de Emissão Retroativas ...
$data_atual_menos_365    = data::adicionar_data_hora(date('d/m/Y'), -365);
$data_atual_menos_365    = data::datatodate($data_atual_menos_365, '');
?>
<html>
<head>
<title>.:: Incluir Conta à Pagar Automática ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../../../js/arred.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function desabilitar() {
    if(document.form.cmb_tipo_data.value == 0) {
        document.form.txt_intervalo.disabled = true
        document.form.txt_intervalo.value = ''
    }else if(document.form.cmb_tipo_data.value == 1) {
        document.form.txt_intervalo.disabled = false
        document.form.txt_intervalo.focus()
    }
}

function tipo_automacao() {
    if(document.form.cmb_tipo_automacao.value == 1) {
        document.form.txt_dia_exibicao.disabled = true
        document.form.txt_dia_exibicao.value = ''
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
    if(tipo_moeda == 2) {//Calculando Dólar ...
        document.form.txt_valor_reajustado.value = valor * eval('<?=$valor_dolar;?>')
    }else if(tipo_moeda == 3) {//Calculando Euro ...
        document.form.txt_valor_reajustado.value = valor * eval('<?=$valor_euro;?>')
    }else {//Reais
        document.form.txt_valor_reajustado.value = valor
    }
    document.form.txt_valor_reajustado.value = arred(document.form.txt_valor_reajustado.value, 2, 1)
}

function validar() {
//Produto Financeiro
    if(!combo('form', 'cmb_produto_financeiro', '', 'SELECIONE UM PRODUTO FINANCEIRO !')) {
        return false
    }
//Qtde de Parcelas ...
    if(document.form.txt_qtde_parcelas.value == 1) {
        alert('QTDE DE PARCELA(S) INVÁLIDA(S) !!!\n\nA QTDE DE PARCELA(S) NUNCA PODE SER IGUAL A "UM" !')
        document.form.txt_qtde_parcelas.focus()
        document.form.txt_qtde_parcelas.select()
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
//Tipo de Automação
    if(!combo('form', 'cmb_tipo_automacao', '', 'SELECIONE UM TIPO DE AUTOMAÇÃO !')) {
        return false
    }
//Dia de Exibição
    if(document.form.txt_dia_exibicao.disabled == false) {
        if(!texto('form', 'txt_dia_exibicao', '1', '1234567890', 'DIA DE EXIBIÇÃO', '2')) {
            return false
        }
    }
//Data de Vencimento
    if(!data('form', 'txt_data_vencimento', '4000', 'VENCIMENTO')) {
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
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_fornecedor' value='<?=$id_fornecedor;?>'>
<input type='hidden' name='id_empresa_menu' value='<?=$id_empresa_menu;?>'>
<!--Aqui precisa por causa da função do JavaScript-->
<input type='hidden' name='id_tipo_pagamento'>
<input type='hidden' name='status_db'>
<!--**********************************************-->
<table width='80%' border='0' cellspacing ='1' cellpadding='1'align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr> 
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Conta à Pagar Automática
            <font color='yellow'>
                <?=genericas::nome_empresa($id_empresa_menu);?>
            </font>
        </td>
    </tr>
<?
	$sql = "SELECT razaosocial 
                FROM `fornecedores` 
                WHERE `id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
	$campos = bancos::sql($sql);
?>
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
            <input type='text' name='txt_numero_conta' title='Digite o N.º da Conta / Nota' size='42' maxlength='40' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Produto(s) Financeiro(s):</b> &nbsp;
            <input type='checkbox' name='chkt_previsao' value='1' id='label' class='checkbox'>
            <label for='label'>Previsão</label>
        </td>
        <td colspan='2'>
            Qtde de Parcelas:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name="cmb_produto_financeiro" title="Selecione um Produto Financeiro" class='combo'>
            <?
                $sql = "SELECT pfv.`id_produto_financeiro_vs_fornecedor`, CONCAT(g.referencia, ' - ', pf.discriminacao) AS produto_financeiro 
                        FROM `produtos_financeiros_vs_fornecedor` pfv 
                        INNER JOIN `produtos_financeiros` pf ON pf.id_produto_financeiro = pfv.id_produto_financeiro 
                        INNER JOIN `grupos` g ON g.id_grupo = pf.id_grupo 
                        WHERE pfv.`id_fornecedor` = '$id_fornecedor' ORDER BY produto_financeiro ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
        <td colspan='2'>
            <input type='text' name='txt_qtde_parcelas' onKeyUp="verifica(this, 'aceita', 'numeros', '', event)" class='caixadetexto'>
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
            <select name="cmb_tipo_pagamento" title="Selecione o Tipo de Pagamento" onchange="separar()" class='combo'>
            <?
                $sql = "SELECT CONCAT(`id_tipo_pagamento`, '|', status_db) AS tipo_pagamento_status, pagamento 
                        FROM `tipos_pagamentos` 
                        WHERE `ativo` = '1' ORDER BY pagamento ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
        <td>
            <select name="cmb_conta_corrente" title="Selecione a Conta Corrente" class='textdisabled' disabled>
            <?
                $sql = "SELECT id_fornecedor_propriedade, concat(num_cc, '|', agencia, '|', banco) AS conta_corrente 
                        FROM `fornecedores_propriedades` 
                        WHERE `id_fornecedor` = '$id_fornecedor' ";
                echo combos::combo($sql);
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
            <select name="cmb_tipo_moeda" title="Selecione o Tipo da Moeda" onchange='calcular()' class='combo'>
            <?
                $sql = "SELECT id_tipo_moeda, concat(simbolo,' - ',moeda) AS simbolo_moeda 
                        FROM `tipos_moedas` 
                        WHERE `ativo` = '1' ";
                echo combos::combo($sql, 1);
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
            <input type='text' name="txt_valor" title="Digite o Valor Nacional / Estrangeiro" size="20" maxlength="15" onKeyUp="verifica(this, 'moeda_especial', '2', '', event);calcular()" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name="txt_valor_reajustado" title="Digite o Valor Reajustado" size="20" maxlength="15" onKeyUp="verifica(this, 'moeda_especial', '2', '', event)" class='textdisabled' disabled> em Reais
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
            <select name='cmb_tipo_data' title='Selecione o Tipo de Data' onchange='return desabilitar()' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='0'>Fixa</option>
                <option value='1'>Intervalo</option>
            </select>
        </td>
        <td>
            <input type='text' name="txt_intervalo" title="Digite o Intervalo" size="5" maxlength="15" onKeyUp="verifica(this, 'aceita', 'numeros', '', event)" class='caixadetexto' disabled>
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
                <!--Comentado no dia 26/06/2015 porque segundo a Dona Sandra as opções 1 e 2 não 
                são utilizadas ...
                <option value='0'>POR DATA</option>
                <option value='1'>PAGO A CONTA ANTERIOR</option>-->
                <option value='2' selected>AMBAS ACIMA</option>
            </select>
        </td>
        <td>
            <input type='text' name='txt_dia_exibicao' title='Digite o Dia Exibição' size='5' maxlength='5' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Data de Vencimento:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='text' name='txt_data_vencimento' title='Digite a Data de Vencimento' onkeyup="verifica(this, 'data', '', '', event)" size='20' maxlength='10' class='caixadetexto'>
            &nbsp;<img src = '../../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../../calendario/calendario.php?campo=txt_data_vencimento&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            Observação:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <textarea name='txt_observacao' title='Digite a Observação' rows='3' cols='85' maxlength='255' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'consultar_fornecedor.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_limpar' value="Limpar" title="Limpar" onclick="redefinir('document.form','LIMPAR')" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value="Salvar" title="Salvar" style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value="Fechar" title="Fechar" onclick="fechar(window)" style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>