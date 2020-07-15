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

function checkbox(form, campo, indice, cor_origem) {
    var campo_sel   = eval('document.'+form+'.'+campo+'')
    var elementos   = eval('document.'+form+'.elements')
    cor_origem      = cor_origem.toLowerCase()
    linha = eval(indice) + 3
    
    if(!document.getElementById('chkt_produto_insumo'+indice).checked) {
        document.getElementById('chkt_produto_insumo'+indice).checked   = true
        document.getElementById('txt_quantidade'+indice).disabled       = false
        //Muda a cor para habilitado ...
        document.getElementById('txt_quantidade'+indice).className      = 'caixadetexto'
        document.getElementById('txt_quantidade'+indice).focus()
    }else {
        document.getElementById('chkt_produto_insumo'+indice).checked   = false
        document.getElementById('txt_quantidade'+indice).disabled       = true
        //Muda a cor para desabilitado ...
        document.getElementById('txt_quantidade'+indice).className      = 'textdisabled'
        //Limpo todas as caixas daquela linha ...
        document.getElementById('txt_quantidade'+indice).value              = ''
        document.getElementById('txt_peso_total'+indice).value              = ''
        document.getElementById('txt_volume_total'+indice).value            = ''
    }
    
    if(document.getElementById('chkt_produto_insumo'+indice).checked) {
        if(navigator.appName == 'Netscape') {
            totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            totallinhas.rows[linha].style.backgroundColor = '#c6e2ff'
        }
    }else {
        totallinhas.rows[linha].style.backgroundColor = cor_origem
    }
	
//Aqui é um controle para poder selecionar o checkbox principal
    var total_itens_produtos = 0
    var total_itens_produtos_marcados = 0
	
    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'chkt_produto_insumo[]') {
            //Somente para produtos q estão checados
            if(elementos[i].checked == true) total_itens_produtos_marcados++
            total_itens_produtos++
        }
    }
//Significa que todos os itens de Produtos estão marcados
    if(total_itens_produtos == total_itens_produtos_marcados) {
        document.form.chkt_tudo.checked = true
    }else {
        document.form.chkt_tudo.checked = false
    }
}

function selecionar_especial(form, campo, totallinhas, cor_origem) {
    var campo_sel   = eval('document.'+form+'.'+campo+'')
    var elementos   = eval('document.'+form+'.elements')
    cor_origem      = cor_origem.toLowerCase()
    var linhas      = (typeof(elementos['chkt_produto_insumo[]'][0]) == 'undefined') ? 1 : (elementos['chkt_produto_insumo[]'].length)

    for (i = 0; i < linhas; i ++) {
        if (!document.getElementById('chkt_produto_insumo'+i).checked) {//Se estiver checado ...
            document.getElementById('chkt_produto_insumo'+i).checked    = true
            document.getElementById('txt_quantidade'+i).disabled        = false
            //Muda a cor para habilitado
            document.getElementById('txt_quantidade'+i).className       = 'caixadetexto'
        }else {
            document.getElementById('chkt_produto_insumo'+i).checked    = false
            document.getElementById('txt_quantidade'+i).disabled        = true
            //Muda a cor para desabilitado
            document.getElementById('txt_quantidade'+i).className       = 'textdisabled'
            //Limpo todas as caixas daquela linha ...
            document.getElementById('txt_quantidade'+i).value           = ''
            document.getElementById('txt_peso_total'+i).value           = ''
            document.getElementById('txt_volume_total'+i).value         = ''
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