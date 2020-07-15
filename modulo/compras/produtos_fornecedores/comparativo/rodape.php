<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/produtos_fornecedores/comparativo/index.php', '../../../../');
?>
<html>
<head>
<title>.:: Rodapé de Comparativo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function excluir_grupo() {
    var selecionado = 0
    elementos = parent.itens.document.form.elements
    for (var i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'chkt_produto_insumo' && elementos[i].checked == true) selecionado = 1
    }
    if(selecionado == 0) {
        alert('SELECIONE UM GRUPO !')
        return false
    }else {
        var numero = '', id_prods_insumos = '', vetor = '<?=$id_prods_insumos;?>'
        for (var i = 0; i < elementos.length; i++) {
            if(elementos[i].name == 'chkt_produto_insumo' && elementos[i].checked == true) {
                numero_selecionado = elementos[i].value
                id_prods_insumos = ''
                for(var j = 0; j < vetor.length; j++) {
                    if(vetor.charAt(j) == ',') {
                        numero = eval(numero)
                        numero_selecionado = eval(numero_selecionado)
                        if(numero != numero_selecionado) id_prods_insumos+= numero + ','
                        numero = ''
                    }else {
                        numero+= vetor.charAt(j)
                    }
                }
                vetor = id_prods_insumos
            }
        }
        parent.itens.document.location = 'itens.php?id_prods_insumos='+id_prods_insumos
        parent.rodape.document.location = 'rodape.php?id_prods_insumos='+id_prods_insumos
    }
}
    
function imprimir() {
    if(typeof(parent.itens.document.form.chkt_produto_insumo) == 'object') {
        parent.itens.print()
    }else {
        alert('ADICIONE UM GRUPO P/ IMPRIMIR !')
        return false
    }
}
</Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align="center">
    <td align='center'>
        <input type='button' name='cmd_incluir' value='Incluir Grupo' title='Incluir Grupo' onclick="nova_janela('incluir_grupo.php?id_prods_insumos=<?=$id_prods_insumos;?>', 'POP', '', '', '', '', 450, 750, 'c', 'c')" class='botao'>
        <input type='button' name='cmd_excluir' value='Excluir Grupo' title='Excluir Grupo' onclick='excluir_grupo()' class='botao'>
        <input type='button' name="cmd_calculo_desconto" value="Calculo de Desconto" title="Calculo de Desconto" onclick="nova_janela('../lista_preco/calc_desconto.php', 'POP', '', '', '', '', 140, 700, 'c', 'c')" class="botao">
        <input type='button' name="cmd_imprimir" value='Imprimir' title='Imprimir' onclick='return imprimir()' class='botao'>
    </td>
</table>
</body>
</html>