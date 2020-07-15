<?
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/programacao/estoque/gerenciar/consultar.php', '../../../../../');
?>
<html>
<head>
<title>.:: Gerenciar Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function manipular_estoque() {
//A variável checkbox -> Serve para verificar quantos checkbox eu tenho selecionado no frame de cima
    var checkbox    = 0
    var elementos   = parent.itens.document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox' && elementos[i].name != 'chkt_tudo') {
            if(elementos[i].checked == true) {
                checkbox ++
                break;//P/ sair fora do Loop ...
            }
        }
    }
    if(checkbox == 0) {
        alert('SELECIONE UM ITEM !')
        return false
    }else {
        nova_janela('manipular.php?id_pedido_venda=<?=$id_pedido_venda;?>&id_cliente=<?=$id_cliente;?>&tela=1&pop_up=1&posicao='+parent.itens.document.form.posicao.value, 'MANIPULAR', '', '', '', '', 500, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

function liberar_estoque() {
//A variável checkbox -> Serve para verificar quantos checkbox eu tenho selecionado no frame de cima
    var checkbox    = 0
    var elementos   = parent.itens.document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox' && elementos[i].name != 'chkt_tudo') {
            if(elementos[i].checked == true) {
                checkbox ++
                break;//P/ sair fora do Loop ...
            }
        }
    }
    if(checkbox == 0) {
        alert('SELECIONE UM ITEM !')
        return false
    }else {
        parent.itens.document.form.passo.value = 1
        parent.itens.document.form.submit()
    }
}

function mover_para_pendencia() {
    //A variável "indice" -> serve para verificar quantos checkbox foram selecionados no Frame acima ...
    var indice                  = 0
    var id_pedido_venda_item    = ''
    var elementos               = parent.itens.document.form.elements
    
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'chkt_pedido_venda_item[]') {
            if(elementos[i].checked == true) {//Checkbox de Pedido de Venda Item selecionado ...
                id_pedido_venda_item = id_pedido_venda_item + elementos[i].value + ', '
                
                //Verifico se o "Pedido de Venda Item" que foi selecionado está em Packing List ...
                if(parent.itens.document.getElementById('hdd_esta_em_packing_list'+indice).value == 'S') {
                    alert('EXISTE(M) ITEM(NS) DE PEDIDO QUE NÃO PODE(M) SER MOVIDO PARA PENDÊNCIA, PORQUE O(S) MESMO(S) SE ENCONTRA(M) EM PACKING LIST !')
                    elementos[i].checked = false
                    return false
                }
            }
            indice++
        }
    }
    id_pedido_venda_item = id_pedido_venda_item.substr(0, id_pedido_venda_item.length - 2)

    
    if(indice == 0) {
        alert('SELECIONE UM ITEM !')
        return false
    }else {
        var resposta1 = confirm('DESEJA SEPARAR O(S) ITEM(NS) SELECIONADO(S) ?')
        if(resposta1 == true) {
            var resposta2 = confirm('DESEJA GERAR RELATÓRIO DE NOVA SEPARAÇÃO ?')
            gerar_relatorio = (resposta2 == false) ? 0 : 1
            parent.itens.location = 'itens.php?passo=1&id_pedido_venda=<?=$id_pedido_venda;?>&id_cliente=<?=$id_cliente;?>&mover_para_pendencia=1&gerar_relatorio='+gerar_relatorio+'&id_pedido_venda_item='+id_pedido_venda_item
        }
    }
}

function separar() {
//A variável checkbox -> Serve para verificar quantos checkbox eu tenho selecionado no frame de cima
    var checkbox                = 0
    var id_pedido_venda_item    = ''
    var elementos               = parent.itens.document.form.elements
    
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox' && elementos[i].name != 'chkt_tudo') {
            if(elementos[i].checked == true) {
                id_pedido_venda_item = id_pedido_venda_item + elementos[i].value + ', '
                checkbox ++
            }
        }
    }

    id_pedido_venda_item = id_pedido_venda_item.substr(0, id_pedido_venda_item.length - 2)
    if(checkbox == 0) {
        alert('SELECIONE UM ITEM !')
        return false
    }else {
        var resposta = confirm('DESEJA SEPARAR O(S) ITEM(NS) SELECIONADO(S) ?')
        if(resposta == true) {
            parent.itens.location = 'itens.php?passo=1&id_pedido_venda=<?=$id_pedido_venda;?>&id_cliente=<?=$id_cliente;?>&separar=1&id_pedido_venda_item='+id_pedido_venda_item
        }
    }
}

function mandar_vale() {
//A variável checkbox -> Serve para verificar quantos checkbox eu tenho selecionado no frame de cima
    var checkbox                = 0
/*A princípio eu mantenho essa variável de sinalização "pedidos_liberados", como se todos os Pedidos 
estivessem Liberados ...*/
    var pedidos_liberados       = 'S'
    var id_pedido_venda_item    = ''
    var elementos               = parent.itens.document.form.elements
    
    //Significa que está tela foi carregada com apenas 1 linha ...
    if(typeof(elementos['chkt_pedido_venda_item[]'][0]) == 'undefined') {
        var linhas = 1
    }else {//Mais de 1 linha ...
        var linhas = elementos['chkt_pedido_venda_item[]'].length
    }
    
    for(var i = 0; i < linhas; i++) {
        if(parent.itens.document.getElementById('chkt_pedido_venda_item'+i).checked == true) {
            id_pedido_venda_item = id_pedido_venda_item + parent.itens.document.getElementById('chkt_pedido_venda_item'+i).value + ', '
            checkbox ++

            //Aqui eu verifico se o Pedido da Linha em Questão esta Liberado ...
            if(parent.itens.document.getElementById('hdd_pedido_liberado'+i).value == 'N') {
                pedidos_liberados = 'N'
                break;//P/ sair fora do Loop ...
            }
        }
    }

    id_pedido_venda_item = id_pedido_venda_item.substr(0, id_pedido_venda_item.length - 2)
    if(checkbox == 0) {
        alert('SELECIONE UM ITEM !')
        return false
    }else {
        if(pedidos_liberados == 'N') {
            alert('NÃO É POSSÍVEL MANDAR NO VALE !!!\n\nEXISTE(M) ITEM(NS) EM QUE O PEDIDO NÃO ESTA LIBERADO !')
        }else {
            nova_janela('mandar_vale.php?id_pedido_venda=<?=$id_pedido_venda;?>&id_cliente=<?=$id_cliente;?>&id_pedido_venda_item='+id_pedido_venda_item, 'MANDAR', '', '', '', '', 500, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
        }
    }
}

function packing_list() {
//A variável checkbox -> Serve para verificar quantos checkbox eu tenho selecionado no frame de cima
    var checkbox                = 0
    var id_pedido_venda_item    = ''
    var elementos               = parent.itens.document.form.elements
    
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox' && elementos[i].name != 'chkt_tudo') {
            if(elementos[i].checked == true) {
                id_pedido_venda_item = id_pedido_venda_item + elementos[i].value + ', '
                checkbox ++
            }
        }
    }
    
    id_pedido_venda_item = id_pedido_venda_item.substr(0, id_pedido_venda_item.length - 2)
    if(checkbox == 0) {
        alert('SELECIONE UM ITEM !')
        return false
    }else {
        nova_janela('packing_list/packing_list.php?id_cliente=<?=$id_cliente;?>&id_pedido_venda_item='+id_pedido_venda_item, 'MANDAR', '', '', '', '', 580, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}
</Script>
</head>
<body>
<form name='form'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <td align='center'>
        <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="parent.location = 'consultar.php<?=$parametro;?>'" class='botao'>
        <input type='button' name='cmd_manipular_estoque' value='Manipular Estoque' title='Manipular Estoque' onclick='manipular_estoque()' class='botao'>
        <input type='button' name='cmd_mover_para_pendencia' value='Mover p/ Pend&ecirc;ncia' title='Mover p/ Pend&ecirc;ncia' onclick='mover_para_pendencia()' class='botao'>
        <input type='button' name='cmd_separar' value='Separar' title='Separar' onclick='separar()' class='botao'>
        <input type='button' name='cmd_mandar_vale' value='Mandar no Vale' title='Mandar no Vale' onclick='mandar_vale()' class='botao'>
        <?
            if($id_pais != 31) {//Essa opção só é exibida p/ os Clientes Estrangeiros ...
        ?>
            <input type='button' name='cmd_packing_list' value='Packing List' title='Packing List' onclick='packing_list()' style='color:green' class='botao'>
        <?
            }
        ?>
    </td>
</table>
<input type='hidden' name='id_pedido_venda' value='<?=$id_pedido_venda?>'>
<input type='hidden' name='id_cliente' value='<?=$id_cliente?>'>
</form>
</body>
</html>