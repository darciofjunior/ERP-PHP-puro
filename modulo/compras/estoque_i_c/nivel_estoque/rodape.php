<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/estoque_i_c/nivel_estoque/index.php', '../../../../');
?>
<html>
<head>
<title>.:: Nível de Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' rel = 'stylesheet' type = 'text/css'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function gerar_cotacao() {
    if(document.form.cmb_tipo_compra.value == '') {
        alert('SELECIONE UM TIPO DE COMPRA !')
        document.form.cmb_tipo_compra.focus()
        return false
    }
    var elementos       = parent.itens.document.form.elements
    var selecionados    = 0
    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].name != 'chkt_tudo') {
                if(elementos[i].checked == true) {
                    selecionados ++
                    break;
                }
            }
        }
    }
    if(selecionados == 0) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        //Prepara a Tela p/ poder gravar no BD ...
        if(typeof(elementos['chkt_produto_insumo[]'][0]) == 'undefined') {
            var linhas = 1//Existe apenas 1 único elemento ...
        }else {
            var linhas = (elementos['chkt_produto_insumo[]'].length)
        }
/*************************************************************************************************/
//Se existir pelo menos 1 Item em que a Qtde de Compra está negativa, não posso Gerar a Cotação ...
        var itens_com_qtde_compra_negativa = 0
        for(var i = 0; i < linhas; i++) {
            if(parent.itens.document.getElementById('chkt_produto_insumo'+i).checked == true) {
                if(strtofloat(parent.itens.document.getElementById('txt_qtde_compra'+i).value) < 0) {
                    itens_com_qtde_compra_negativa++//Apenas um único Item nessa Situação já é o Suficiente ...
                    break
                }
            }
        }
        if(itens_com_qtde_compra_negativa > 0) {
            alert('NÃO É POSSÍVEL GERAR COTAÇÃO !!!\n\nEXISTE(M) ITEM(NS) COM QUANTIDADE DE COMPRA NEGATIVA !')
            return false
        }
/*************************************************************************************************/
//Tratamento com os objetos para poder gravar no BD ...
        for(var i = 0; i < linhas; i++) {
            if(parent.itens.document.getElementById('chkt_produto_insumo'+i).checked == true) {
                parent.itens.document.getElementById('txt_qtde_metros'+i).value  = strtofloat(parent.itens.document.getElementById('txt_qtde_metros'+i).value)
                parent.itens.document.getElementById('txt_qtde_kg'+i).value      = strtofloat(parent.itens.document.getElementById('txt_qtde_kg'+i).value)
            }
        }
        if(document.form.hdd_insert.value == 1) {//Significa que não foi feita nenhuma alteração na Tela ...
            var id_cotacao = document.form.id_cotacao.value
            nova_janela('../../../classes/cotacao/imprimir.php?id_cotacao='+id_cotacao, 'COTACAO', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')
        }else {
            parent.itens.document.form.action = '../../../classes/cotacao/gerar_cotacao.php?compras=1&cmb_tipo_compra='+document.form.cmb_tipo_compra.value
            nova_janela('', 'COTACAO', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')
            parent.itens.document.form.submit()
        }
    }
}
</Script>
</head>
<body>
<form name='form' method='post' target=''>
<table width='90%' align='center'>
    <tr class='confirmacao' align='center'>
        <td>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="parent.location = 'index.php'" class='botao'>
            <input type='button' name='cmd_gerar_cotacao' value='Gerar Cotação' title='Gerar Cotação' onclick='gerar_cotacao()' class='botao'>
            &nbsp;-&nbsp;
            <font size='-1'>
                <b>Tipo de Compra:</b>
            </font>
            &nbsp;
            <select name='cmb_tipo_compra' title='Selecione o Tipo de Compra' onchange='document.form.hdd_insert.value=0' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='N'>Nacional</option>
                <option value='E'>Export</option>
            </select>
        </td>
    </tr>
</table>
<!--Essa caixa é para poder dar um único insert, na hora em que gerar uma Cotação-->
<input type='hidden' name='hdd_insert'>
<input type='hidden' name='id_cotacao'>
</form>
</body>
</html>