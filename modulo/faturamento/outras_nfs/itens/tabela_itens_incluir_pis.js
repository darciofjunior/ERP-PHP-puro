/*Esse JS foi feito exclusivo para esse caso, devido eu ter uma caixa de texto para cada linha, sendo assim
fica um pouco diferente o controle de clicks e cores da linha*/
function cor_clique_celula(cel_atual, backgroundColor) {
    var cor_origem = cel_atual.bgColor
    var cor_atual = cel_atual.style.backgroundColor
    var nova_cor = backgroundColor
    nova_cor = nova_cor.toLowerCase()
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
            cel_atual.style.backgroundColor = '#c6e2ff';
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

function checkbox(form, campo, indice, cor_origem) {
    var campo_sel = eval('document.'+form+'.'+campo+'')
    var elementos = eval('document.'+form+'.elements')
    var resultado = true
    linha = eval(indice) + 3
/*************************************************************/
//Habilita as Caixas
    document.getElementById('txt_qtde'+indice).disabled              = false
    document.getElementById('txt_valor_unitario'+indice).disabled    = false
    document.getElementById('txt_peso_unitario'+indice).disabled     = false
//Aqui joga o Designer de Habilitado
    document.getElementById('txt_qtde'+indice).className             = 'caixadetexto'
    document.getElementById('txt_valor_unitario'+indice).className   = 'caixadetexto'
    document.getElementById('txt_peso_unitario'+indice).className    = 'caixadetexto'
/*************************************************************/
    cor_origem = cor_origem.toLowerCase()
    if (document.getElementById('chkt_produto_insumo'+indice).checked) {
        campo_sel.checked = false
//Desabilita as Caixas
        document.getElementById('txt_qtde'+indice).disabled              = true
        document.getElementById('txt_valor_unitario'+indice).disabled    = true
        document.getElementById('txt_peso_unitario'+indice).disabled     = true
//Aqui joga o Designer de Desabilitado
        document.getElementById('txt_qtde'+indice).className             = 'textdisabled'
        document.getElementById('txt_valor_unitario'+indice).className   = 'textdisabled'
        document.getElementById('txt_peso_unitario'+indice).className    = 'textdisabled'
        document.getElementById('chkt_produto_insumo'+indice).checked    = false
    }else {
        document.getElementById('chkt_produto_insumo'+indice).checked    = true
        for (var i = 0; i < elementos.length; i++) {
            if (elementos[i].type == 'checkbox' && elementos[i].name != campo) {
                if (elementos[i].checked == false) resultado = false
            }
        }
        campo_sel.checked = resultado
    }
    if (document.getElementById('chkt_produto_insumo'+indice).checked) {
        if(navigator.appName == 'Netscape') {
            totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            totallinhas.rows[linha].style.backgroundColor = '#c6e2ff'
        }
    }else {
        totallinhas.rows[linha].style.backgroundColor = cor_origem
    }
}

function selecionar(form, campo, totallinhas, cor_origem) {
    var campo_sel = eval('document.'+form+'.'+campo+'')
    var elementos = eval('document.'+form+'.elements')
    cor_origem = cor_origem.toLowerCase()
    
    if(typeof(elementos['chkt_produto_insumo[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_produto_insumo[]'].length)
    }
    
    for (i = 0; i < linhas; i++) {
        if (campo_sel.checked) {
            document.getElementById('chkt_produto_insumo'+i).checked    = true
//Habilita as Caixas
            document.getElementById('txt_qtde'+i).disabled              = false
            document.getElementById('txt_valor_unitario'+i).disabled    = false
            document.getElementById('txt_peso_unitario'+i).disabled     = false
//Aqui joga o Designer de Habilitado
            document.getElementById('txt_qtde'+i).className             = 'caixadetexto'
            document.getElementById('txt_valor_unitario'+i).className   = 'caixadetexto'
            document.getElementById('txt_peso_unitario'+i).className    = 'caixadetexto'
        }else {
            document.getElementById('chkt_produto_insumo'+i).checked    = false
            document.getElementById('chkt_produto_insumo'+i).disabled   = false
//Desabilita as Caixas
            document.getElementById('txt_qtde'+i).disabled              = true
            document.getElementById('txt_valor_unitario'+i).disabled    = true
            document.getElementById('txt_peso_unitario'+i).disabled     = true
//Aqui joga o Designer de Desabilitado
            document.getElementById('txt_qtde'+i).className             = 'textdisabled'
            document.getElementById('txt_valor_unitario'+i).className   = 'textdisabled'
            document.getElementById('txt_peso_unitario'+i).className    = 'textdisabled'
//Limpa as Caixas
            document.getElementById('txt_qtde'+i).value                 = ''
            document.getElementById('txt_valor_unitario'+i).value       = ''
            document.getElementById('txt_peso_unitario'+i).value        = ''
        }
    }

    if (campo_sel.checked) {
        if(navigator.appName == 'Netscape') {
            for (i = 3; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            for (i = 3; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }
    }else {
        for (i = 3; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = cor_origem;
    }
}