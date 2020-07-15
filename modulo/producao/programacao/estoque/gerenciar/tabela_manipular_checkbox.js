/*Esse JS foi feito exclusivo para esse caso, devido eu ter uma caixa de texto para cada linha, sendo assim
fica um pouco diferente o controle de clicks e cores da linha*/
function cor_clique_celula(cel_atual, backgroundColor) {
    var cor_atual   = cel_atual.style.backgroundColor
    var nova_cor    = backgroundColor
    nova_cor        = nova_cor.toLowerCase()
//Habilita ou Desabilita o Checkbox e Objeto Radio da Linha quando se dá um clique aonde o cursor do mouse esta posicionado
    if((cor_atual == 'rgb(198,226,255)') || (cor_atual == nova_cor)) {
        if(navigator.appName == 'Netscape') {
            cel_atual.style.backgroundColor = 'rgb(204,255,204)'
        }else {
            cel_atual.style.backgroundColor = '#ccffcc'
        }
    }else {
        cel_atual.style.backgroundColor = nova_cor
    }
}

function sobre_celula(cel_atual, backgroundColor) {
    var cor_atual = cel_atual.style.backgroundColor
    var nova_cor = backgroundColor
    if((cor_atual == 'rgb(198,226,255)')||(cor_atual == '#c6e2ff')) {
        if(navigator.appName == 'Netscape') {
            cel_atual.style.backgroundColor = 'rgb(198,226,255)'
        }else {
            cel_atual.style.backgroundColor = '#c6e2ff'
        }
    }else {
        cel_atual.style.backgroundColor = nova_cor
    }
    cel_atual.style.cursor='hand'
}

function fora_celula(cel_atual, backgroundColor) {
    var cor_atual = cel_atual.style.backgroundColor
    var nova_cor = backgroundColor
    if((cor_atual == 'rgb(198,226,255)') || (cor_atual == '#c6e2ff')){
        if(navigator.appName == 'Netscape') {
            cel_atual.style.backgroundColor = 'rgb(198,226,255)'
        }else {
            cel_atual.style.backgroundColor = '#c6e2ff'
        }
    }else {
        cel_atual.style.backgroundColor = nova_cor
    }
}

function total_linhas(objeto) {
    totallinhas = objeto
}

function focos(objeto) {
    objeto.disabled = false
    objeto.focus()
    return false
}

function checkbox_habilita(indice, cor_origem) {
    cor_origem      = cor_origem.toLowerCase()
    var elementos   = document.form.elements
    linha           = eval(indice) + 3
    
    //Significa que está tela foi carregada com apenas 1 linha ...
    if(typeof(elementos['chkt_pedido_venda_item[]'][0]) == 'undefined') {
        var linhas = 1
    }else {//Mais de 1 linha ...
        var linhas = elementos['chkt_pedido_venda_item[]'].length
    }
    
    if(!(document.getElementById('chkt_pedido_venda_item'+indice).checked)) {
        document.getElementById('chkt_pedido_venda_item'+indice).checked = true
        //Habilito a caixa p/ digitar ...
        document.getElementById('txt_qtde_separada'+indice).disabled    = false
        //Aqui joga o Designer de Habilitado ...
        document.getElementById('txt_qtde_separada'+indice).className   = 'caixadetexto'
    }else {
        document.getElementById('chkt_pedido_venda_item'+indice).checked = false
        //Aqui foi uma puta gambiarra para recalcular (rsrsrs) ...
        document.getElementById('txt_antigo_vale'+indice).ondblclick()
        //Desabilito a caixa p/ digitar ...
        document.getElementById('txt_qtde_separada'+indice).disabled    = true
        //Aqui joga o Designer de Desabilitado ...
        document.getElementById('txt_qtde_separada'+indice).className   = 'textdisabled'
    }

    if(document.getElementById('chkt_pedido_venda_item'+indice).checked) {
        if(navigator.appName == 'Netscape') {
            totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            totallinhas.rows[linha].style.backgroundColor = '#c6e2ff'
        }
    }else {
        totallinhas.rows[linha].style.backgroundColor = cor_origem
    }

//Aqui é um controle para poder selecionar o checkbox principal
    var total_pedidos = 0
    var total_pedidos_marcados = 0

    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_pedido_venda_item'+i).checked == true) {//Somente para pedidos q estão checados
            total_pedidos_marcados++
        }
        total_pedidos++
    }
    
//Significa que todos os pedidos estão marcados
    if(total_pedidos == total_pedidos_marcados) {
        document.form.chkt_tudo.checked = true
    }else {
        document.form.chkt_tudo.checked = false
    }
}

function selecionar_especial(form, campo, totallinhas, cor_origem) {
    var campo_sel   = eval('document.'+form+'.'+campo+'')
    var elementos   = eval('document.'+form+'.elements')
    cor_origem      = cor_origem.toLowerCase()
    
    //Significa que está tela foi carregada com apenas 1 linha ...
    if(typeof(elementos['chkt_pedido_venda_item[]'][0]) == 'undefined') {
        var linhas = 1
    }else {//Mais de 1 linha ...
        var linhas = elementos['chkt_pedido_venda_item[]'].length
    }
    
    if(campo_sel.checked == true) {//Checkbox Principal está marcado ...
        for (var i = 0; i < linhas; i++) {
            document.getElementById('chkt_pedido_venda_item'+i).checked = true
            //Habilita as Caixas ...
            document.getElementById('txt_qtde_separada'+i).disabled     = false
            //Aqui joga o Designer de Habilitado ...
            document.getElementById('txt_qtde_separada'+i).className    = 'caixadetexto'
        }
        //Cor das Linhas ...
        if(navigator.appName == 'Netscape') {
            for (var i = 3; i < (totallinhas.rows.length - 3); i++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            for (var i = 3; i < (totallinhas.rows.length - 3); i++) totallinhas.rows[i].style.backgroundColor = '#c6e2ff'
        }
    }else {//Checkbox Principal está desmarcado ...
        for (var i = 0; i < linhas; i++) {
            document.getElementById('chkt_pedido_venda_item'+i).checked = false
            //Desabilita as Caixas ...
            document.getElementById('txt_qtde_separada'+i).disabled     = true
            //Aqui joga o Designer de Desabilitado ...
            document.getElementById('txt_qtde_separada'+i).className    = 'textdisabled'
        }
        //Cor das Linhas ...
        for(var i = 3; i < (totallinhas.rows.length - 3); i++) totallinhas.rows[i].style.backgroundColor = cor_origem
    }
}