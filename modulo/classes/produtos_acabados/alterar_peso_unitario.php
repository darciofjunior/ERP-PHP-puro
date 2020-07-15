<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
session_start('funcionarios');

/*Eu tenho esse desvio aki para n�o verificar a sess�o desse arkivo, fa�o isso pq esse arquivo aki � um 
pop-up em outras partes do sistema e se eu n�o fizer esse desvio d� erro de permiss�o*/
if($nao_verificar_sessao != 1) {
//Significa que veio do Menu Abertas / Liberadas
    if($seguranca == 1) {
        segurancas::geral('/erp/albafer/modulo/faturamento/nota_saida/itens/abertas_liberadas.php', '../../../');
//Significa que veio do Menu de Liberadas / Faturadas
    }else if($seguranca == 2) {
        segurancas::geral('/erp/albafer/modulo/faturamento/nota_saida/itens/liberadas_faturadas.php', '../../../');
//Significa que veio do Menu de Faturadas / Empacotadas / Despachadas
    }else if($seguranca == 3) {
        segurancas::geral('/erp/albafer/modulo/faturamento/nota_saida/itens/fat_empac_despachada.php', '../../../');
    }
}
$mensagem[1] = "<font class='confirmacao'>PESO UNIT�RIO ALTERADO COM SUCESSO.</font>";

if(!empty($_POST['txt_peso_unitario'])) {
    //Altero dados do $id_produto_acabado passado por par�metro ...
    $sql = "UPDATE `produtos_acabados` SET `qtde_pecas` = '$_POST[txt_qtde_pecas]', `peso_unitario` = '$_POST[txt_peso_unitario]', `peso_total` = '$_POST[txt_peso_total]', `data_alteracao_peso` = '".date('Y-m-d H:i:s')."', peso_atualizado = 'S' WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
    bancos::sql($sql);
    
    if(!empty($_POST['id_nfs_item'])) {//Se essa tela foi acessada atrav�s de um item de NF de Sa�da, altero o peso deste item tamb�m ...
        $sql = "UPDATE `nfs_itens` SET `peso_unitario` = '$_POST[txt_peso_unitario]' WHERE `id_nfs_item` = '$_POST[id_nfs_item]' LIMIT 1 ";
        bancos::sql($sql);
    }
    $valor = 1;
}

//Procedimento normal de quando se carrega a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_produto_acabado = $_POST['id_produto_acabado'];
    $id_nfs_item        = $_POST['id_nfs_item'];
}else {
    $id_produto_acabado = $_GET['id_produto_acabado'];
    $id_nfs_item        = $_GET['id_nfs_item'];
}

//Busca de alguns dados do PA passado por par�metro ...
$sql = "SELECT `referencia`, `discriminacao`, `qtde_pecas`, `peso_unitario`, `peso_total`, `data_alteracao_peso` 
        FROM `produtos_acabados` 
        WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<title>.:: Alterar Peso Unit�rio ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Quantidade
    if(!texto('form', 'txt_qtde_pecas', '1', '0123456789', 'QUANTIDADE DE PE�AS', '1')) {
        return false
    }
//Peso Total
    if(!texto('form', 'txt_peso_total', '4', '0123456789,.', 'PESO TOTAL', '2')) {
        return false
    }
//Aqui desabilita para poder gravar no BD
    document.form.txt_peso_unitario.disabled = false
//Aqui � para n�o atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    return limpeza_moeda('form', 'txt_peso_total, txt_peso_unitario, ')
}

function calcular_peso_unitario() {
    var quantidade_pecas = eval(document.form.txt_qtde_pecas.value)
    var peso_total = eval(strtofloat(document.form.txt_peso_total.value))
    if(quantidade_pecas > 0 && peso_total > 0) {
        document.form.txt_peso_unitario.value = peso_total / quantidade_pecas
        document.form.txt_peso_unitario.value = arred(document.form.txt_peso_unitario.value, 8, 1)
//C�lculo para achar % de Diferen�a de Peso 
        var peso_unitario = eval(strtofloat(document.form.txt_peso_unitario.value))
        var peso_unitario_antigo = eval(strtofloat(document.form.txt_peso_unitario_antigo.value))
//Aki � para n�o dar erro de divis�o por Zero
        if(peso_unitario == 0) peso_unitario = 1
        document.form.txt_diferenca_peso.value = (1 - peso_unitario_antigo / peso_unitario) * 100
        document.form.txt_diferenca_peso.value = arred(document.form.txt_diferenca_peso.value, 1, 1)
    }else {
        document.form.txt_peso_unitario.value = ''
        document.form.txt_diferenca_peso.value = ''
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que s� atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        var tela1 = eval(document.form.tela1.value)//Referente aos frames da Tela da parte de baixo
        var tela2 = eval(document.form.tela2.value)//Referente aos frames da Tela da parte de baixo
//Atualiza a parte de Itens se existir
        if(typeof(tela1) == 'object') tela1.document.form.submit()
//Atualiza a parte de Rodap� se existir
        if(typeof(tela2) == 'object') tela2.document.form.submit()
    }
}
</Script>
</head>
<body onload='document.form.txt_qtde_pecas.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--****************************Controles de Tela****************************-->
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<input type='hidden' name='id_nfs_item' value='<?=$id_nfs_item;?>'>
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='tela1' value='<?=$tela1;?>'>
<input type='hidden' name='tela2' value='<?=$tela2;?>'>
<!--*************************************************************************-->
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Alterar Peso Unit�rio
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td>
            <font color='yellow'>
                <b>Ref: </b>
            </font>
            <?=$campos[0]['referencia'];?>
        </td>
        <td colspan='3'>
            <font color='yellow'>
                <b>Discrimina��o: </b>
            </font>
            <?=$campos[0]['discriminacao'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qtde de Pe�as (Atual):</b>
        </td>
        <td>
            <input type='text' name='txt_qtde_pecas' title='Digite a Quantidade de Pe�as' size='12' maxlength='10' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''};calcular_peso_unitario()" class='caixadetexto'>
        </td>
        <td>
            <b>Qtde de Pe�as (Antigo):</b>
        </td>
        <td>
            <input type='text' name='txt_qtde_pecas_antigo' value='<?=$campos[0]['qtde_pecas'];?>' title='Quantidade de Pe�as' size='12' maxlength='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Peso Total (Atual):</b>
        </td>
        <td>
            <input type='text' name='txt_peso_total' title='Digite o Peso Total' onkeyup="verifica(this, 'moeda_especial', '4', '1', event);calcular_peso_unitario()" size='16' maxlength='15' class='caixadetexto'>
        </td>
        <td>
            <b>Peso Total (Antigo):</b>
        </td>
        <td>
            <input type='text' name='txt_peso_total_antigo' value='<?=number_format($campos[0]['peso_total'], 8, ',', '.');?>' title='Peso Total' size='16' maxlength='15' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Peso Unit�rio:</b>
        </td>
        <td>
            <input type='text' name='txt_peso_unitario' title='Peso Unit�rio' size='16' maxlength='15' class='textdisabled' disabled>
        </td>
        <td>
            <b>Peso Unit�rio (Antigo):</b>
        </td>
        <td>
            <input type='text' name='txt_peso_unitario_antigo' value='<?=number_format($campos[0]['peso_unitario'], 8, ',', '.');?>' title='Peso Unit�rio' size='16' maxlength='15' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td>
            Diferen�a de Peso:
        </td>
        <td colspan='3'>
            <input type='text' name='txt_diferenca_peso' size='10' class='textdisabled' disabled> %
            &nbsp;-&nbsp;Data e Hora da �lt. Alter.: 
            <font color='yellow'>
                <?=data::datetodata(substr($campos[0]['data_alteracao_peso'], 0, 10), '/').' - '.substr($campos[0]['data_alteracao_peso'], 11, 8);?>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_peso_unitario.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>