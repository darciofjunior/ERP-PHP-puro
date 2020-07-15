<?
require('../../../../lib/segurancas.php');
require('../../../../lib/biblioteca.php');
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');
//Tratamento com a variável que vem por parâmetro ...
$id_oc = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_oc'] : $_GET['id_oc'];
segurancas::geral('/erp/albafer/modulo/vendas/ocs/itens/consultar.php', '../../../../');

$mensagem[1] = '<font class="confirmacao">NOVO PRODUTO ACABADO INCLUIDO COM SUCESSO.</font>';
$mensagem[2] = '<font class="confirmacao">ITEM(NS) INCLUIDO(S) COM SUCESSO.</font>';

/*********************************Procedimento p/ Incluir os Itens*****************************************/
if(isset($_POST['chkt_produto_acabado'])) {
    for($i = 0; $i < count($_POST['chkt_produto_acabado']); $i++) {
        $sql = "INSERT INTO `ocs_itens` (`id_oc_item`, `id_oc`, `id_produto_acabado`, `qtde`, `defeito_alegado`) VALUES (NULL, '$id_oc', '".$_POST['chkt_produto_acabado'][$i]."', '".$_POST['txt_quantidade'][$i]."', '".$_POST['txt_defeito_alegado'][$i]."') ";
        bancos::sql($sql);
    }
    $valor = 2;
}
/**********************************************************************************************************/
?>
<html>
<head>
<title>.:: Consultar Produtos Acabados ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'incluir_itens_oc.js'></Script>
<Script Language = 'JavaScript'>
function verificar_teclas(event) {
    if(navigator.appName == 'Microsoft Internet Explorer') {
        if(event.keyCode == 13 || event.keyCode == 35) {//Se Enter ou End faz a Consulta.
            pesquisar_itens_incluir()
            document.form.txt_referencia.value = ''
            document.form.txt_discriminacao.value = ''
            document.form.txt_referencia.focus()
        }
    }else {
        if(event.which == 13 || event.which == 35) {//Se Enter ou End faz a Consulta.
            pesquisar_itens_incluir()
            document.form.txt_referencia.value = ''
            document.form.txt_discriminacao.value = ''
            document.form.txt_referencia.focus()
        }
    }
}

function validar_itens(event) {
    if(navigator.appName == 'Microsoft Internet Explorer') {
        if(event.keyCode == 13 || event.keyCode == 35) {//Se Enter ou End faz a Consulta.
            /*A maioria dos vendedores, preferem que ao incluir um Item, o Sistema ainda permaneça na mesma tela de 
            Filtro para Incluir Novos Itens ao invés de ir para o Alterar e colocar o Preço ...*/
            return validar()
        }
    }else {
        if(event.which == 13 || event.which == 35) {//Se Enter ou End faz a Consulta.
            /*A maioria dos vendedores, preferem que ao incluir um Item, o Sistema ainda permaneça na mesma tela de 
                    Filtro para Incluir Novos Itens ao invés de ir para o Alterar e colocar o Preço ...*/
            return validar()
        }
    }
}

function pesquisar_itens_incluir() {
    for(var i = 0; i < document.form.txt_discriminacao.value.length; i++) {
        if(document.form.txt_discriminacao.value.charAt(i) == '%') document.form.txt_discriminacao.value = document.form.txt_discriminacao.value.replace('%', '|')
    }
    ajax('pesquisar_itens_incluir.php?id_oc=<?=$id_oc;?>', 'div_pesquisar_itens_incluir')
}

function consultar_especiais(txt_referencia, txt_discriminacao) {
    ajax('pesquisar_itens_incluir.php?id_oc=<?=$id_oc;?>&txt_referencia='+txt_referencia+'&txt_discriminacao='+txt_discriminacao+'&hdd_checkbox_mostrar_esp=1&esp_listados=1', 'div_pesquisar_itens_incluir')
}

function validar() {
    var elementos = document.form.elements
    var cont_checkbox_selecionados = 0, total_linhas = 0
    for (var i = 0; i < elementos.length; i++) {
        if (elementos[i].type == 'checkbox') {
            if(elementos[i].name == 'chkt_produto_acabado[]') {//Só vasculho os checkbox de Produtos ...
                if(elementos[i].checked) cont_checkbox_selecionados++
                total_linhas++
            }
        }
    }
    
    if(cont_checkbox_selecionados == 0) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }

    for(var i = 0; i < total_linhas; i++) {
//Força o Preenchimento do Campo Quantidade ...
        if(document.getElementById('chkt_produto_acabado'+i).checked == true) {
            //Quantidade ...
            if(document.getElementById('txt_quantidade'+i).value == '') {
                alert('DIGITE A QUANTIDADE !')
                document.getElementById('txt_quantidade'+i).focus()
                return false
            }
            if(document.getElementById('txt_quantidade'+i).value == 0) {
                alert('QUANTIDADE INVÁLIDA !')
                document.getElementById('txt_quantidade'+i).focus()
                document.getElementById('txt_quantidade'+i).select()
                return false
            }
            //Defeito Alegado ...
            if(document.getElementById('txt_defeito_alegado'+i).value == '') {
                alert('DIGITE O DEFEITO ALEGADO !')
                document.getElementById('txt_defeito_alegado'+i).focus()
                return false
            }
        }
    }
    //Aqui é para não atualizar a Tela abaixo que chamou esse LightBox ...
    document.form.nao_atualizar.value = 1
    document.form.submit()
}

function controlar_hdd_checkbox_mostrar_esp() {
    if(document.form.chkt_mostrar_especiais.checked == true) {
        document.form.hdd_checkbox_mostrar_esp.value = 1
    }else {
        document.form.hdd_checkbox_mostrar_esp.value = 0
    }
    document.form.txt_referencia.focus()
}

function controlar_hdd_mostrar_componentes() {
    if(document.form.chkt_mostrar_componentes.checked == true) {
        document.form.hdd_mostrar_componentes.value = 1
    }else {
        document.form.hdd_mostrar_componentes.value = 0
    }
    document.form.txt_referencia.focus()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
    //Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.ativar_loading()
}
</Script>
</head>
<body onload='document.form.txt_referencia.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action=''>
<!--************************Controles de Tela************************-->
<!--Controles de Tela-->
<input type='hidden' name='hdd_checkbox_mostrar_esp' value='0'><!--Macete-->
<input type='hidden' name='hdd_mostrar_componentes' value='0'><!--Macete-->
<input type='hidden' name='nao_atualizar'>
<!--*****************************************************************-->
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr>
        <td>
            <fieldset>
                <legend>
                    <span style='cursor: pointer' onclick="listar_dados_clientes()">
                        <font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='#000000'>
                            <b>CONSULTAR PRODUTOS ACABADOS</b>
                        </font>
                    </span>
                </legend>
                <table width='100%' border='0' cellspacing='1' cellpadding='1' align='center'>
                        <tr class='linhanormal'>
                                <td>
                                        Referência
                                </td>
                                <td>
                                        <input type="text" name="txt_referencia" id="txt_referencia" title="Digite a Referência" onkeyup="verificar_teclas(event)" class="caixadetexto">
                                        &nbsp;
                                        <input type='checkbox' name='chkt_mostrar_especiais' value='1' title="Mostrar Especiais" onclick="controlar_hdd_checkbox_mostrar_esp()" id='label1' class="checkbox">
                                        <label for='label1'>
                                                Mostrar Especiais
                                        </label>
                                </td>
                        </tr>
                        <tr class='linhanormal'>
                                <td>
                                        Discriminação
                                </td>
                                <td>
                                        <input type="text" name="txt_discriminacao" id="txt_discriminacao" title="Digite a Discriminação" size="30" onkeyup="verificar_teclas(event)" class="caixadetexto">
                                        &nbsp;
                                        <img src = "../../../../imagem/menu/pesquisar.png" onclick="pesquisar_itens_incluir()" title='Pesquisar' style='cursor:pointer' border="0">
                                </td>
                        </tr>
                        <tr class='linhanormal'>
                                <td>
                                        &nbsp;
                                </td>
                                <td>
                                        <input type='checkbox' name='chkt_mostrar_componentes' value='1' title="Mostrar Componentes" onclick="controlar_hdd_mostrar_componentes()" id='label2' class="checkbox">
                                        <label for='label2'>
                                                Mostrar Componentes
                                        </label>
                                </td>
                        </tr>
                        <tr class='linhacabecalho' align='center'>
                                <td colspan="2">
                                        &nbsp;
                                </td>
                        </tr>
                </table>
            </fieldset>
        </td>
    </tr>
</table>
<br>
<div id="div_pesquisar_itens_incluir"></div>
</form>
</body>
</html>