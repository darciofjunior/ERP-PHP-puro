<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/genericas.php');
require('../../../../../../lib/comunicacao.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/financeiros.php');
require('../../../../../../lib/variaveis/intermodular.php');

session_start('funcionarios');
if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../../');
$mensagem[1] = "<font class='confirmacao'>CONTA À PAGAR INCLUIDA COM SUCESSO.</font>";

if(!empty($_POST['txt_numero_conta']) || !empty($_POST['txt_valor'])) {
//Só não irá enviar esse e-mail quando for a própria da Dona Sandra que estiver incluindo a Conta ...
    if($_SESSION['id_funcionario'] != 66) {
//1)
/************************Busca de Dados************************/
//Aqui eu trago alguns dados de Conta à Pagar p/ passar por e-mail via parâmetro ...
        $sql = "SELECT `razaosocial` 
                FROM `fornecedores` 
                WHERE `id_fornecedor` = '$_POST[id_fornecedor]' LIMIT 1 ";
        $campos_conta = bancos::sql($sql);
//Coloquei esse nome na variável porque na sessão já existe uma variável com o nome de id_empresa ...
        $empresa        = genericas::nome_empresa($id_emp);
        $fornecedor 	= $campos_conta[0]['razaosocial'];
        $num_nota       = $_POST['txt_numero_conta'];
//2)
/************************E-mail************************/
/*
//-Se o Usuário estiver incluindo uma Conta Financeiro, então o Sistema dispara um e-mail informando 
qual a Conta à Pagar que está sendo incluida ...
//-Aqui eu trago alguns dados de Conta à Pagar p/ passar por e-mail via parâmetro ...
//-Aqui eu busco o login de quem está incluindo a Conta à Pagar ...*/
        $sql = "SELECT `login` 
                FROM `logins` 
                WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
        $campos_login       = bancos::sql($sql);
        $login_incluindo    = $campos_login[0]['login'];
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
        $complemento_justificativa  = '<br><b>Empresa: </b>'.$empresa.' <br><b>Fornecedor: </b>'.$fornecedor.' <br><b>N.º da Conta / Nota: </b>'.$num_nota.' <br><b>Login: </b>'.$login_incluindo;
        $justificativa              = $complemento_justificativa.'<br><b>Data de Emissão: </b>'.$_POST['txt_data_emissao'].'<br><b>Data de Vencimento: </b>'.$_POST['txt_data_vencimento'].'<br><b>Valor Reajustado: </b>'.number_format($_POST['txt_valor_reajustado'], 2, ',', '.').'<br>'.date('d/m/Y H:i:s').'<br>'.$PHP_SELF;
//Os e-mails estão especificados dentro da biblioteca intermodular na pasta variáveis ...
        $destino    = $incluir_contas_apagar;
        $copia      = $incluir_contas_apagar_copia;
        comunicacao::email('ERP - GRUPO ALBAFER', $destino, $copia, 'Incluindo Conta Financeiro', $justificativa);
    }
//3)
/************************Inclusão************************/
    $dia    = substr($_POST['txt_data_vencimento'], 0, 2);
    $mes    = substr($_POST['txt_data_vencimento'], 3, 2);
    $ano    = substr($_POST['txt_data_vencimento'], 6, 4);
    $semana = data::numero_semana($dia, $mes, $ano);

    $data_emissao       = data::datatodate($_POST['txt_data_emissao'], '-');
    $data_vencimento 	= data::datatodate($_POST['txt_data_vencimento'], '-');

    if(empty($chkt_previsao))   $chkt_previsao = 0;

    $vetor                  = explode('|', $_POST['cmb_produto_financeiro']);
    $id_grupo               = $vetor[0];
    $id_produto_financeiro  = $vetor[1];
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
    $cmb_importacao = (!empty($_POST[cmb_importacao])) ? "'".$_POST[cmb_importacao]."'" : 'NULL';

    $sql = "INSERT INTO `contas_apagares` (`id_conta_apagar`, `id_funcionario`, `id_fornecedor`, `id_empresa`, `id_importacao`, `id_tipo_moeda`, `semana`, `previsao`, `data_emissao`, `data_vencimento`, `data_vencimento_alterada`, `id_tipo_pagamento_recebimento`, `id_grupo`, `id_produto_financeiro`, `perc_uso_produto_financeiro`, `numero_conta`, `valor`, `valor_icms`, `status` , `ativo`) VALUES (NULL, '$_SESSION[id_funcionario]', '$_POST[id_fornecedor]', '$id_emp', $cmb_importacao, '$_POST[cmb_tipo_moeda]' ,'$semana', '$chkt_previsao', '$data_emissao', '$data_vencimento', '$data_vencimento', '$_POST[id_tipo_pagamento]', '$id_grupo', '$id_produto_financeiro', '100', '$_POST[txt_numero_conta]', '$_POST[txt_valor]', '$_POST[txt_icms_creditar]', '0', '1') ";
    bancos::sql($sql);
    $id_conta_apagar = bancos::id_registro();
    //Registrando Follow-UP(s) ...
    if(!empty($_POST['txt_observacao'])) {
        $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_fornecedor`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_fornecedor', '$_SESSION[id_funcionario]', '$id_conta_apagar', '18', '".strtolower($_POST['txt_observacao'])."', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
    }
	
/*Seleção dos dados bancários do fornecedor para gravar na tabela
de contas_apagares_vs_pffs para ficar mais fácil a busca dos dados*/
    $sql = "SELECT `banco`, `agencia`, `num_cc`, `correntista`, `cnpj_cpf` 
            FROM `fornecedores_propriedades` 
            WHERE `id_fornecedor_propriedade` = '$_POST[cmb_conta_corrente]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) {
        $bancos_fornecedor  = $campos[0]['banco'];
        $agencia            = $campos[0]['agencia'];
        $num_cc             = $campos[0]['num_cc'];
        $correntista        = $campos[0]['correntista'];
        $cnpj_cpf           = $campos[0]['cnpj_cpf'];
    }
//Adiciona a conta apagar, mais a relação do produto financeiro do fornecedor
    $sql = "INSERT INTO `contas_apagares_vs_pffs` (`id_conta_apagar_vs_pff`, `id_conta_apagar`, `banco`, `agencia`, `num_cc`, `correntista`, `cnpj_cpf`, `ativo`) VALUES (NULL, '$id_conta_apagar', '$bancos_fornecedor', '$agencia', '$num_cc', '$correntista', '$cnpj_cpf', '1') ";
    bancos::sql($sql);
    
    financeiros::atualizar_data_alterada($id_conta_apagar, 'A');
    $valor = 1;
}
//Procedimento quando carrega a Tela ...
$id_fornecedor = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_fornecedor'] : $_GET['id_fornecedor'];

//Aqui verifica se já foi inserido a conta à pagar antes para poder desabilitar o botão de submit lá em baixo ...
if($valor == 1) $disabled = 'disabled';
//Essa variável aqui irá me servir de controle p/ o JavaScript na função + abaixo ...
$sql = "SELECT pf.forcar_icms 
        FROM `produtos_financeiros_vs_fornecedor` pfv 
        INNER JOIN `produtos_financeiros` pf ON pf.id_produto_financeiro = pfv.id_produto_financeiro 
        WHERE pfv.id_fornecedor = '$id_fornecedor' ";
$campos         = bancos::sql($sql);
$forcar_icms    = $campos[0]['forcar_icms'];
//Busca do último valor do dólar e do euro ...
$valor_dolar    = genericas::moeda_dia('dolar');
$valor_euro     = genericas::moeda_dia('euro');
?>
<html>
<head>
<title>.:: Incluir Conta à Pagar ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
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
    if(tipo_moeda == 2) {
        document.form.txt_valor_reajustado.value = valor * eval('<?=$valor_dolar;?>')
    }else if(tipo_moeda == 3) {
        document.form.txt_valor_reajustado.value = valor * eval('<?=$valor_euro;?>')
    }else {
        document.form.txt_valor_reajustado.value = valor
    }
    document.form.txt_valor_reajustado.value = arred(document.form.txt_valor_reajustado.value, 2, 1)
}

function validar() {
    var forcar_icms = '<?=$forcar_icms;?>'
//Produto Financeiro ...
    if(!combo('form', 'cmb_produto_financeiro', '', 'SELECIONE UM PRODUTO FINANCEIRO !')) {
        return false
    }
//N.º da Conta ...
    if(document.form.txt_numero_conta.value != '') {
        if(!texto('form', 'txt_numero_conta', '1', '1234567890QWERTYUIOPÇLKJHGFDSAZXCVBNM zaqwsxcderfvbgtyhnmjuiklopç,.-/º°ª', 'CONTA / NOTA', '1')) {
            return false
        }
    }
//Tipo de Pagamento ...
    if(!combo('form', 'cmb_tipo_pagamento', '', 'SELECIONE UM TIPO DE PAGAMENTO !')) {
        return false
    }
//Tipo da Moeda 
    if(!combo('form', 'cmb_tipo_moeda', '', 'SELECIONE O TIPO DA MOEDA !')) {
        return false
    }
//ICMS à Creditar ...
    if(forcar_icms == 'S') {//Verifico se no cadastro consta essa Marcação p/ forçar o Preenchimento ...
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
//Valor Nacional / Estrangeiro
    if(!texto('form', 'txt_valor', '1', '1234567890,.-','VALOR', '2')) {
        return false
    }
//Data de Emissão (Conta)
    if(!data('form', 'txt_data_emissao', '4000', 'CONTA')) {
        return false
    }
//Data de Vencimento ...
    if(!data('form', 'txt_data_vencimento', '4000', 'VENCIMENTO')) {
        return false
    }
//Comparação entre o ICMS à Creditar e o Valor Nacional / Estrangeiro ...
    if(forcar_icms == 'S') {//Verifico se no cadastro consta essa Marcação p/ forçar o Preenchimento ...
        var valor_nac_est = eval(strtofloat(document.form.txt_valor.value))
        var icms_creditar = eval(strtofloat(document.form.txt_icms_creditar.value))
//O valor do ICMS pode ser no máximo de até 30% do Valor Nacional / Estrangeiro ...
        if(icms_creditar > (valor_nac_est * 0.3)) {
            alert('ICMS À CREDITAR À CREDITAR INVÁLIDO !!!\nVALOR DE ICMS À CREDITAR ACIMA DE 30% DO VALOR NACIONAL / ESTRANGEIRO !')
            document.form.txt_icms_creditar.focus()
            document.form.txt_icms_creditar.select()
            return false
        }
    }
//Aki desabilita os campos para poder gravar no BD ...
    document.form.txt_valor_reajustado.disabled = false
//Desabilito o Botão para o usuário não ficar incluindo várias vezes a mesma Conta no BD ...
    document.form.cmd_salvar.disabled   = true
    document.form.cmd_salvar.className  = 'textdisabled'
//Aqui é para não atualizar o frame de Itens abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
    return limpeza_moeda('form', 'txt_icms_creditar, txt_valor, txt_valor_reajustado, ')
}

function visualizar_todas_importacoes() {
    var checado = (document.form.chkt_importacao.checked == true) ? 1 : 0
    ajax('consultar_importacao.php?checado='+checado, 'cmb_importacao')
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) window.opener.parent.itens.document.location = '../itens.php'+window.opener.parent.itens.document.form.parametro.value
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_fornecedor' value='<?=$id_fornecedor;?>'>
<!--Aqui precisa por causa da função do JavaScript-->
<input type='hidden' name='id_tipo_pagamento'>
<input type='hidden' name='status_db'>
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<!--**********************************************-->
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Conta à Pagar
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp);?>
            </font>
        </td>
    </tr>
<?
    $sql = "SELECT razaosocial, id_pais 
            FROM `fornecedores` 
            WHERE `id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
    $campos     = bancos::sql($sql);
    $id_pais    = $campos[0]['id_pais'];
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
            <input type='text' name="txt_numero_conta" title="Digite a Conta / Nota" size="45" maxlength="50" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Produto(s) Financeiro(s):</b>
            &nbsp;<input type='checkbox' name='chkt_previsao' value='1' class='checkbox' id='label'>
            <label for='label'>Previsão</label>
        </td>
        <td>
            Importação:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_produto_financeiro' title='Selecione um Produto Financeiro' class='combo'>
            <?
                $sql = "SELECT CONCAT(g.id_grupo, '|', pf.id_produto_financeiro), CONCAT(g.referencia, ' - ', pf.discriminacao) AS produto 
                        FROM `produtos_financeiros_vs_fornecedor` pfv 
                        INNER JOIN `produtos_financeiros` pf ON pf.id_produto_financeiro = pfv.id_produto_financeiro 
                        INNER JOIN `grupos` g ON g.id_grupo = pf.id_grupo 
                        WHERE pfv.id_fornecedor = '$id_fornecedor' ORDER BY produto ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
        <td>
            <select name='cmb_importacao' title='Selecione uma Importação' class='combo'>
            <?
                $sql = "SELECT id_importacao, nome 
                        FROM  `importacoes` 
                        WHERE `ativo` = '1' ORDER BY nome ";
                echo combos::combo($sql);
            ?>
            </select>
            &nbsp;
            <input type='checkbox' name='chkt_importacao' id='label2' value='1' onclick='visualizar_todas_importacoes()' class='checkbox' checked>
            <label for='label2'>
                Visualizar Todas Importações
            </label>
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
                $sql = "SELECT CONCAT(id_tipo_pagamento, '|', status_db) AS tipo_pagamento_status, pagamento 
                        FROM `tipos_pagamentos` 
                        WHERE `ativo` = '1' ORDER BY pagamento ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
        <td>
            <select name='cmb_conta_corrente' title='Selecione a Conta Corrente' class='combo' disabled>
            <?
                $sql = "SELECT id_fornecedor_propriedade, CONCAT(num_cc, '|', agencia, '|', banco) AS conta_corrente 
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
            ICMS à Creditar:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_tipo_moeda' onchange='calcular()' class='combo'>
            <?
                $sql = "SELECT id_tipo_moeda, CONCAT(simbolo, ' - ', moeda) AS simbolo_moeda 
                        FROM `tipos_moedas` 
                        WHERE `ativo` = '1' ";
                echo combos::combo($sql, 1);
            ?>
            </select>
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
            <input type='text' name='txt_icms_creditar' title='Digite o ICMS à Creditar' onkeyup="verifica(this, 'moeda_especial', '2', '1', event)" size='20' maxlength='15' class="<?=$class;?>" <?=$disabled_icms;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='blue'>
                Valor Dólar:
            </font>
            <?='R$ '.number_format($valor_dolar, 4, ',', '.');?>
        </td>
        <td>
            <font color='blue'>
                Valor Euro:
            </font>
            <?='R$ '.number_format($valor_euro, 4, ',', '.');?>
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
            <input type='text' name='txt_valor' title='Digite o Valor' onkeyup="verifica(this, 'moeda_especial', '2', '1', event);calcular()" size='20' maxlength='15' class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_valor_reajustado' title='Valor Reajustado' size='20' maxlength='15' class='textdisabled' disabled> em Reais
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data da Conta:</b>
        </td>
        <td>
            <b>Data de Vencimento:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_data_emissao' value="<?=date('d/m/Y');?>" onkeyup="verifica(this, 'data', '', '', event)" size='20' maxlength='10' class='caixadetexto'>
            &nbsp; <img src = '../../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../../calendario/calendario.php?campo=txt_data_emissao&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;Calend&aacute;rio
        </td>
        <td>
            <input type='text' name='txt_data_vencimento' title='Data de Vencimento' onkeyup="verifica(this, 'data', '', '', event)" size='20' maxlength='10' class='caixadetexto'>
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
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="document.form.nao_atualizar.value=1;window.location = 'consultar_fornecedor.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR')" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao' <?=$disabled;?>>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>