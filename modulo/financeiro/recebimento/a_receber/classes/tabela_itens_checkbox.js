/*Esse JS foi feito exclusivo para esse caso, devido eu ter uma caixa de texto para cada linha, sendo assim
fica um pouco diferente o controle de clicks e cores da linha*/
function cor_clique_celula(cel_atual, backgroundColor) {
    var cor_origem  = cel_atual.bgColor
    var cor_atual   = cel_atual.style.backgroundColor
    var nova_cor    = backgroundColor
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
    var cor_atual   = cel_atual.style.backgroundColor
    var nova_cor    = backgroundColor
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
    var cor_atual   = cel_atual.style.backgroundColor
    var nova_cor    = backgroundColor
    if((cor_atual == 'rgb(198,226,255)') || (cor_atual == '#c6e2ff')) {
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
    var campo_sel   = eval('document.'+form+'.'+campo+'')
    var elementos   = eval('document.'+form+'.elements')
    cor_origem      = cor_origem.toLowerCase()
    linha           = eval(indice) + 3
    if(!document.getElementById('chkt_cheque_cliente'+indice).checked) {
        document.getElementById('chkt_cheque_cliente'+indice).checked = true
    }else {
        document.getElementById('chkt_cheque_cliente'+indice).checked = false
    }
    if(document.getElementById('chkt_cheque_cliente'+indice).checked == true) {
        if(navigator.appName == 'Netscape') {
            totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            totallinhas.rows[linha].style.backgroundColor = '#c6e2ff'
        }
    }else {
        totallinhas.rows[linha].style.backgroundColor = cor_origem
    }

//Aqui é um controle para poder selecionar o checkbox principal
    var total_itens_cheques = 0
    var total_itens_cheques_marcados = 0

    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'chkt_cheque_cliente[]') {
            //Somente para cheques q estão checados
            if(elementos[i].checked == true) total_itens_cheques_marcados++
            total_itens_cheques++
        }
    }
//Significa que todos os itens de Cheque estão marcados
    if(total_itens_cheques == total_itens_cheques_marcados) {
        document.form.chkt_tudo.checked = true
    }else {
        document.form.chkt_tudo.checked = false
    }
}

function selecionar(form, campo, totallinhas, cor_origem) {
    var campo_sel = eval('document.'+form+'.'+campo+'')
    var elementos = eval('document.'+form+'.elements')
    cor_origem = cor_origem.toLowerCase()
    
    if(typeof(elementos['chkt_cheque_cliente[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_cheque_cliente[]'].length)
    }
    
    //O checkbox Principal será quem irá marcar / desmacar os cheques "Itens" nas demais Linhas ...
    var checar = (campo_sel.checked) ? true : false

    for (i = 0; i < linhas; i++) document.getElementById('chkt_cheque_cliente'+i).checked = checar
	
    if (campo_sel.checked) {
        if(navigator.appName == 'Netscape') {
            for (i = 3; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            for (i = 3; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }
    }else {
        for (i = 3; i < (totallinhas.rows.length - 1); i++) totallinhas.rows[i].style.backgroundColor = cor_origem
    }
}