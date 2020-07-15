<?
require('../../../../lib/segurancas.php');
require('../../../../lib/compras_new.php');
require('../../../../lib/comunicacao.php');
require('../../../../lib/data.php');
require('../../../../lib/variaveis/intermodular.php');
//segurancas::geral('/erp/albafer/modulo/compras/pedidos/follow_up/follow_up.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>DADO(S) DE IMPORTAÇÃO ALTERADO COM SUCESSO.</font>";

$id_pedido = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_pedido'] : $_GET['id_pedido'];

if(!empty($_POST['txt_prazo_viagem_navio'])) {
//Tem esse flag aqui para o caso dos pedidos em q o fornecedor é do id_país = 31
    if(empty($_POST['txt_prazo_viagem_navio'])) $txt_prazo_viagem_navio = 0;
    $infl_data_ret_porto = (!empty($_POST['chkt_infl_data_ret_porto'])) ? 'S' : 'N';
    if($_POST['opt_data'] == 2) {//Resolveu alterar a Data de Entrega
        $data_entrega_para_utilizar = data::datatodate($_POST['txt_nova_data_entrega'], '-');//Esse prazo é Digitável ...
        //Igualo a Data de Armazém com o Campo Nova Data de Chegada Atual do Porto ...
        $txt_data_entrada_armazem = data::adicionar_data_hora(data::datetodata($data_entrega_para_utilizar, '/'), $txt_prazo_viagem_navio);
    }else {
        $data_entrega_para_utilizar = $_POST['hdd_prazo_entrega'];//Essa variável está guardada em um Hidden ...
    }
    //Preparação p/ gravar no BD ...
    $txt_data_entrada_armazem 	= data::datatodate($txt_data_entrada_armazem, '-');
    $txt_data_pagto_numerario 	= data::datatodate($txt_data_pagto_numerario, '-');
    $txt_data_retirada_porto 	= data::datatodate($txt_data_retirada_porto, '-');
//Atualizando dados de Cabeçalho ...
    $sql = "UPDATE `pedidos` SET `prazo_entrega` = '$data_entrega_para_utilizar', `prazo_navio` = '$txt_prazo_viagem_navio', `data_entrada_armazem` = '$txt_data_entrada_armazem', `influenciar_data_armazenagem` = '$infl_data_ret_porto', `periodo_armazenagem` = '$txt_periodo_armazenagem', `data_pagto_numerario` = '$txt_data_pagto_numerario', `data_retirada_porto` = '$txt_data_retirada_porto' WHERE `id_pedido` = '$_POST[id_pedido]' LIMIT 1 ";
    bancos::sql($sql);
//Só para os que possuem importação
    if(!empty($txt_prazo_viagem_navio)) {
        //Retorna a Diferença em dias da data de emissão até a data de entrega
        $diferenca_dias = data::diferenca_data($data_emissao, $data_entrega_para_utilizar);
        $prazo_entrega 	= $diferenca_dias[0];
        if($prazo_entrega < 0) $prazo_entrega = 0;
        $soma_prazo 	= 3 + (integer)$txt_prazo_viagem_navio + (integer)$prazo_entrega;
        //Aqui soma a data de emissão mais a somatória de prazos, serve para o financeiro
        compras_new::atualizar_importacao($id_pedido, $soma_prazo);
    }
    $valor = 1;
}

//Busca de Dados para Mostrar os Follow-up(s) cadastrados no Pedido
$sql = "SELECT f.nome as funcionario 
        FROM `funcionarios` f 
        INNER JOIN `logins` l ON l.id_funcionario = f.id_funcionario AND l.id_login = '$_SESSION[id_login]' LIMIT 1 ";
$campos             = bancos::sql($sql);
$funcionario        = $campos[0]['funcionario'];

$sql = "SELECT p.*, f.id_pais 
        FROM `pedidos` p 
        INNER JOIN `fornecedores` f ON f.id_fornecedor = p.id_fornecedor 
        WHERE p.`id_pedido` = '$id_pedido' LIMIT 1 ";
$campos             = bancos::sql($sql);
$id_pais            = $campos[0]['id_pais'];
$prazo_viagem_navio = $campos[0]['prazo_navio'];
$data_emissao       = substr($campos[0]['data_emissao'], 0, 10);
$data_entrega       = $campos[0]['prazo_entrega'];
?>
<html>
<title>.:: Follow-up(s) do Pedido ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    if(!texto('form', 'txt_prazo_viagem_navio', '1', '0123456789', 'PRAZO DE VIAGEM DO NAVIO', '2')) {
        return false
    }
//Nova Data de Entrega
    if(!data('form', 'txt_nova_data_entrega', '4000', 'FOLLOW-UP')) {
        return false
    }
//Data de Entrada no Armazém
    if(document.form.txt_data_entrada_armazem.value == '') {
        alert('DIGITE A DATA DE ENTRADA NO ARMAZÉM !')
        document.form.txt_data_entrada_armazem.focus()
        document.form.txt_data_entrada_armazem.select()
        return false
    }
//Controle mais rigoroso em cima da Data de Entrada no Armazém ...
    if(document.form.txt_data_entrada_armazem.value.length < 10) {
        alert('DATA DE ENTRADA NO ARMAZÉM ATUAL INVÁLIDA !!!')
        document.form.txt_data_entrada_armazem.focus()
        document.form.txt_data_entrada_armazem.select()
        return false
    }
//Período de Armazenagem
    if(!texto('form', 'txt_periodo_armazenagem', '1', '0123456789', 'PERÍODO DE ARMAZENAGEM', '2')) {
        return false
    }
//Data de Pagto. do Numerário
    if(document.form.txt_data_pagto_numerario.value != '') {
        if(!data('form', 'txt_data_pagto_numerario', '4000', 'PAGTO. DO NUMERÁRIO')) {
            return false
        }
    }
//Previsão Data de Retirada no Porto
    if(!data('form', 'txt_data_retirada_porto', '4000', 'PREVISÃO RETIRADA NO PORTO')) {
        return false
    }
    document.form.txt_nova_data_entrega.disabled    = false
    document.form.txt_data_retirada_porto.disabled  = false
}

function recalcular_datas() {
    var id_pais = eval('<?=$id_pais;?>')
    if(document.form.opt_data[0].checked == true) {//Manter Data de Entrega
        if(id_pais != 31) {//Internacional ...
//Calcula a Data de Chegada no Porto ...
            nova_data('document.form.txt_data_entrega_atual', 'document.form.txt_data_chegada_atual', 'document.form.txt_prazo_viagem_navio') 
            nova_data('document.form.txt_data_entrega_atual', 'document.form.txt_data_entrada_armazem', 'document.form.txt_prazo_viagem_navio')
//Se esse Checkbox estiver habilitado então o mesmo influencia no campo de "Previsão Data de Retirada no Porto" ...
            if(document.form.chkt_infl_data_ret_porto.checked == true) {//Fará o cálculo ...
//Calcula a Previsão Data de Retirada no Porto ...
                nova_data('document.form.txt_data_entrada_armazem', 'document.form.txt_data_retirada_porto', 'document.form.txt_periodo_armazenagem')
            }
        }
    }else {//Alterar Data de Entrega
//Só irá fazer esse controle quando for um Fornecedor do Tipo Internacional ...
        if(typeof(document.form.txt_prazo_viagem_navio) == 'object') {
            nova_data('document.form.txt_nova_data_entrega', 'document.form.txt_data_chegada', 'document.form.txt_prazo_viagem_navio')
        }
    }
}

function data_embarque_atual() {
    document.form.txt_nova_data_entrega.disabled            = true
    document.form.txt_nova_data_entrega.style.className     = 'textdisabled'
    recalcular_datas()
}

function nova_data_entrega() {
    document.form.txt_nova_data_entrega.disabled    = false
    document.form.txt_nova_data_entrega.className   = 'caixadetexto'
    document.form.txt_nova_data_entrega.focus()
    recalcular_datas()
}

function travar_data_retirada_porto() {
    if(document.form.chkt_infl_data_ret_porto.checked == true) {//Desabilita o Campo ...
        document.form.txt_data_retirada_porto.disabled  = true
//Layout de Desabilitado ...
        document.form.txt_data_retirada_porto.className = 'textdisabled'
        recalcular_datas()
    }else {//Habilita o Campo ...
        document.form.txt_data_retirada_porto.disabled  = false
//Layout de Habilitado ...
        document.form.txt_data_retirada_porto.className = 'caixadetexto'
    }
}
</Script>
<body onload='document.form.txt_prazo_viagem_navio.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--***************************Controles de Tela***************************-->
<input type='hidden' name='id_pedido' value='<?=$id_pedido;?>'>
<input type='hidden' name='hdd_prazo_entrega' value='<?=$data_entrega;?>'>
<input type='hidden' name='data_emissao' value='<?=$data_emissao;?>' onclick='recalcular_datas()'>
<!--***********************************************************************-->
<table width='100%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Dados de Importação => 
            <font color='yellow' size='2'>
            <?
                //Busca do nome da Importação ...
                $sql = "SELECT i.nome 
                        FROM `importacoes` i 
                        INNER JOIN `pedidos` p ON p.`id_importacao` = i.`id_importacao` AND p.`id_pedido` = '$id_pedido' LIMIT 1 ";
                $campos_importacao = bancos::sql($sql);
                echo $campos_importacao[0]['nome'].' - '.$id_pedido;
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkgreen'>
                <b>Data de Embarque Atual:</b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_data_entrega_atual' value='<?=data::datetodata($data_entrega, '/');?>' size='12' maxlength='10' class='textdisabled' disabled>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type='radio' name='opt_data' value='1' onclick='data_embarque_atual()' id='opt1' checked>
            <label for='opt1'>Manter Data de Entrega</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Prazo de Viagem do Navio:</b>
        </td>
        <td>
            <input type='text' name='txt_prazo_viagem_navio' value='<?=$prazo_viagem_navio;?>' title='Digite o Prazo de Viagem do Navio' onkeyup="verifica(this, 'aceita', 'numeros', '', event);recalcular_datas()" size='8' maxlength='3' class='caixadetexto'>&nbsp;DIAS
        </td>
    </tr>
    <?
//Data de Chegada Atual do Porto: é a soma da Data de Entrega + o prazo do navio
        $data_chegada_atual = data::adicionar_data_hora(data::datetodata($data_entrega, '/'), $prazo_viagem_navio);
    ?>
    <tr class='linhanormal'>
        <td>
            Data de Chegada Atual do Porto:
        </td>
        <td>
            <input type='text' name='txt_data_chegada_atual' value='<?=$data_chegada_atual;?>' title='Data de Chegada Atual do Porto' maxlength='10' size='12' class='textdisabled' disabled>
        </td>
    </tr>
    <?
//Data de Entrega do Armazém ...
        if($campos[0]['data_entrada_armazem'] != '0000-00-00') $data_entrada_armazem = data::datetodata($campos[0]['data_entrada_armazem'], '/');
//Data de Pagto do Numerário ...
        if($campos[0]['data_pagto_numerario'] != '0000-00-00') $data_pagto_numerario = data::datetodata($campos[0]['data_pagto_numerario'], '/');
//Data de Retirada do Porto ...
        if($campos[0]['data_retirada_porto'] != '0000-00-00') $data_retirada_porto = data::datetodata($campos[0]['data_retirada_porto'], '/');
    ?>
    <tr class='linhanormal'>
        <td>
            <b>Data de Entrada no armazém:</b>
        </td>
        <td>
            <input type='text' name='txt_data_entrada_armazem' value='<?=$data_entrada_armazem;?>' title='Data de Entrada no armazém' size='12' maxlength='10' onfocus='document.form.txt_periodo_armazenagem.focus()' class='textdisabled'>
            &nbsp;<img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="if(document.form.txt_data_entrada_armazem.disabled == false) {nova_janela('../../../../calendario/calendario.php?campo=txt_data_entrada_armazem&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')}">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Período de armazenagem:</b>
        </td>
        <td>
            <input type='text' name="txt_periodo_armazenagem" value="<?=$campos[0]['periodo_armazenagem'];?>" title="Digite o Período de armazenagem" size='8' maxlength='7' onkeyup="verifica(this, 'aceita', 'numeros', '', event);recalcular_datas()" onblur='recalcular_datas()' class='caixadetexto'> dias
            &nbsp;-&nbsp;
            <?
                if($campos[0]['influenciar_data_armazenagem'] == 'S') { 
                    $checked    = 'checked';
                    $disabled   = 'disabled';
                    $class      = 'textdisabled'; 
                }else {
                    $checked    = '';
                    $disabled   = '';
                    $class      = 'caixadetexto';
                }
            ?>
            <input type='checkbox' name='chkt_infl_data_ret_porto' value='1' title='Influenciar na Data de Retirada do Porto' onclick='travar_data_retirada_porto()' id='influenciar' class='checkbox' <?=$checked;?>>
            <label for='influenciar'>
                Influenciar na Data de Retirada do Porto
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Pagto. do Numerário: 
        </td>
        <td>
            <input type='text' name='txt_data_pagto_numerario' value='<?=$data_pagto_numerario;?>' title='Digite a Data de Pagto. do Numerário' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            &nbsp;<img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="if(document.form.txt_data_pagto_numerario.disabled == false) {nova_janela('../../../../calendario/calendario.php?campo=txt_data_pagto_numerario&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')}">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Previsão Data de Retirada no Porto:</b> 
        </td>
        <td>
            <input type='text' name='txt_data_retirada_porto' value='<?=$data_retirada_porto;?>' title='Digite a Previsão Data de Retirada no Porto' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" onblur="if(this.value.length == 10) {recalcular_datas()}" class='<?=$class;?>' <?=$disabled;?>>
            &nbsp;<img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="if(document.form.txt_data_retirada_porto.disabled == false) {nova_janela('../../../../calendario/calendario.php?campo=txt_data_retirada_porto&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')}">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            <font size='2' color='yellow'>
                <b>CASO ALTERE PARA A NOVA DATA DE EMBARQUE</b>
            </font>
        </td>
    </tr>
    <?
//Nova Data de Chegada do Porto: é a Data Atual + o prazo do navio
        $nova_data_chegada_atual = data::adicionar_data_hora(date('d/m/Y'), $prazo_viagem_navio);
    ?>
    <tr class='linhanormal'>
        <td>
            <b>Nova Data de Chegada ao Porto:</b>
        </td>
        <td>
            <input type='text' name='txt_data_chegada' value='<?=$nova_data_chegada_atual;?>' title='Data de Chegada' maxlength='10' size='12' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Nova Data de Embarque:
        </td>
        <td>
            <input type='text' name='txt_nova_data_entrega' value='<?=date('d/m/Y');?>' title='Digite a Data' onkeyup="verifica(this, 'data', '', '', event)" onblur="if(this.value.length == 10) {recalcular_datas()}" size='12' maxlength='10' class='textdisabled' disabled>
            <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="if(document.form.opt_data[1].checked == true) {nova_janela('../../../../calendario/calendario.php?campo=txt_nova_data_entrega&tipo_retorno=1&caixa_auxiliar=data_emissao', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')}">
            <input type='radio' name='opt_data' value='2' onclick='nova_data_entrega()' id='opt2'>
            <label for='opt2'>Alterar Data de Entrega</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>