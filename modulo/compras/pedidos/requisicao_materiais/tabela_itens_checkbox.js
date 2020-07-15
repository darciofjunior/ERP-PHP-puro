/*Esse JS foi feito exclusivo para esse caso, devido eu ter uma caixa de texto para cada linha, sendo assim
fica um pouco diferente o controle de clicks e cores da linha*/
function cor_clique_celula(cel_atual, backgroundColor) {
    var cor_origem = cel_atual.bgColor
    var cor_atual = cel_atual.style.backgroundColor
    var nova_cor = backgroundColor
    nova_cor = nova_cor.toLowerCase()
//Habilita ou Desabilita o Checkbox e Objeto Radio da Linha quando se dá um clique aonde o cursor do mouse esta posicionado
    if((cor_atual == 'rgb(198,226,255)') || (cor_atual == nova_cor)){
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

function checkbox_habilita(form, campo, indice, cor_origem) {
    var campo_sel   = eval('document.'+form+'.'+campo+'')
    var elementos   = eval('document.'+form+'.elements')
    var resultado   = true
    var linha       = eval(indice) + 4
    cor_origem = cor_origem.toLowerCase()
    
    if(document.getElementById('chkt_item_pedido'+indice).checked) {//Se estiver checado, desmarca ...
        document.getElementById('chkt_item_pedido'+indice).checked  = false
        //Desabilita ...
        document.getElementById('txt_qtde'+indice).disabled         = true
        document.getElementById('txt_qtde'+indice).className        = 'textdisabled'
    }else {//Se estiver desmarcado, checa ...
        document.getElementById('chkt_item_pedido'+indice).checked = true
        //Habilita ...
        document.getElementById('txt_qtde'+indice).disabled         = false
        document.getElementById('txt_qtde'+indice).className        = 'caixadetexto'
    }
    
    //Controle com o Checkbox Principal ...
    for (i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox' && elementos[i].name != campo) {
            if(!elementos[i].checked) resultado = false
        }
    }
    campo_sel.checked = resultado
    
    if(document.getElementById('chkt_item_pedido'+indice).checked) {//Se estiver checado, desmarca ...
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
    var campo_sel   = eval('document.'+form+'.'+campo+'')
    var elementos   = eval('document.'+form+'.elements')
    cor_origem      = cor_origem.toLowerCase()
    
    if(typeof(elementos['chkt_item_pedido[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_item_pedido[]'].length)
    }
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_item_pedido'+i).checked) {//Se estiver checado, desmarca ...
            document.getElementById('chkt_item_pedido'+i).checked  = false
            //Desabilita ...
            document.getElementById('txt_qtde'+i).disabled         = true
            document.getElementById('txt_qtde'+i).className        = 'textdisabled'
        }else {//Se estiver desmarcado, checa ...
            document.getElementById('chkt_item_pedido'+i).checked  = true
            //Habilita ...
            document.getElementById('txt_qtde'+i).disabled         = false
            document.getElementById('txt_qtde'+i).className        = 'caixadetexto'
        }
    }

    if (campo_sel.checked) {
        if(navigator.appName == 'Netscape') {
            for (i = 4; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            for (i = 4; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = '#c6e2ff'
        }
        elementos[2].focus()
    }else {
        for (i = 4; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = cor_origem
    }
}

function validar_checkbox(formulario, mensagem) {
    var caracteres  = '0123456789,.-'
    var valor       = false
    var elementos   = eval('document.'+formulario+'.elements')
    
    for (var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox' && elementos[i].name != 'chkt') {
            if (elementos[i].checked == true) valor = true
        }
    }
    
    if (valor == false) {
        alert(mensagem)
        return false
    }else {
	if(typeof(elementos['chkt_item_pedido[]'][0]) == 'undefined') {
            var linhas = 1//Existe apenas 1 único elemento ...
        }else {
            var linhas = (elementos['chkt_item_pedido[]'].length)
        }	
        for(var i = 0; i < linhas; i++) {
            if(document.getElementById('chkt_item_pedido'+i).checked) {//Se estiver checado, desmarca ...
                if(document.getElementById('txt_qtde'+i).value == '') {
                    alert('DIGITE A QUANTIDADE !')
                    document.getElementById('txt_qtde'+i).focus()
                    return false
                }
                if(document.getElementById('txt_qtde'+i).value <= 0) {
                    alert('QUANTIDADE INVÁLIDA !')
                    document.getElementById('txt_qtde'+i).focus()
                    document.getElementById('txt_qtde'+i).select()
                    return false
                }
                for (var y = 0; y < document.getElementById('txt_qtde'+i).value.length; y ++) {
                    if (caracteres.indexOf(document.getElementById('txt_qtde'+i).value.charAt(y), 0) == -1) {
                        alert('QUANTIDADE INVÁLIDA !')
                        document.getElementById('txt_qtde'+i).focus()
                        document.getElementById('txt_qtde'+i).select()
                        return false
                    }
                }
            }
        }
        return true
    }
}