/*Esse JS foi feito exclusivo para esse caso, devido eu ter uma caixa de texto para cada linha, sendo assim
fica um pouco diferente o controle de clicks e cores da linha*/
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


function selecionar(totallinhas, cor_origem) {
    var elementos   = document.form.elements
    var linhas      = (typeof(elementos['chkt_produto_acabado[]'][0]) == 'undefined') ? 1 : elementos['chkt_produto_acabado[]'].length
    cor_origem      = cor_origem.toLowerCase()
    
    if(document.getElementById('chkt_tudo').checked == true) {//Checkbox Principal está marcado ...
        for(var i = 0; i < linhas; i++) {
            if(document.getElementById('chkt_produto_acabado'+i).disabled == false) {
                document.getElementById('chkt_produto_acabado'+i).checked = true
                //Designer de Habilitado ...
                document.getElementById('txt_ajuste_mmv'+i).className       = 'caixadetexto'
                document.getElementById('txt_mmv_inst_corr'+i).className    = 'caixadetexto'
                //Habilita as Caixinhas ...
                document.getElementById('txt_ajuste_mmv'+i).disabled        = false
                document.getElementById('txt_mmv_inst_corr'+i).disabled     = false
            }
        }
    }else {//Checkbox Principal está desmarcado ...
        for(var i = 0; i < linhas; i++) {
            if(document.getElementById('chkt_produto_acabado'+i).disabled == false) {
                document.getElementById('chkt_produto_acabado'+i).checked   = false
                //Designer de Desabilitado ...
                document.getElementById('txt_ajuste_mmv'+i).className       = 'textdisabled'
                document.getElementById('txt_mmv_inst_corr'+i).className    = 'textdisabled'
                //Desabilita as Caixinhas ...
                document.getElementById('txt_ajuste_mmv'+i).disabled        = true
                document.getElementById('txt_mmv_inst_corr'+i).disabled     = true
            }
        }
    }

    if(document.getElementById('chkt_tudo').checked == true) {//Checkbox Principal está marcado ...
        if(navigator.appName == 'Netscape') {
            for (i = 3; i < (totallinhas.rows.length - 2); i++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            for (i = 3; i < (totallinhas.rows.length - 2); i++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }
    }else {
        for (i = 3; i < (totallinhas.rows.length - 2); i++) totallinhas.rows[i].style.backgroundColor = cor_origem
    }
}

function checkbox_habilita(form, campo, indice, cor_origem) {
    var campo_sel 	= eval('document.'+form+'.'+campo+'')
    var elements 	= eval('document.'+form+'.elements')
    var resultado 	= true, objetos_linha = 2
    var linha 		= eval(indice) + 3
    cor_origem 		= cor_origem.toLowerCase()

    //Linha estava com o checkbox marcado e passará a ser desmarcado ...
    if(document.getElementById('chkt_produto_acabado'+indice).checked == true && document.getElementById('chkt_produto_acabado'+indice).disabled == false) {
        document.getElementById('chkt_produto_acabado'+indice).checked  = false
        //Designer de Desabilitado ...
        document.getElementById('txt_ajuste_mmv'+indice).className      = 'textdisabled'
        document.getElementById('txt_mmv_inst_corr'+indice).className   = 'textdisabled'
        //Desabilita as Caixinhas ...
        document.getElementById('txt_ajuste_mmv'+indice).disabled       = true
        document.getElementById('txt_mmv_inst_corr'+indice).disabled    = true
        campo_sel.checked = false
        //cor da Linha ...
        totallinhas.rows[linha].style.backgroundColor = cor_origem
    //Linha estava com o checkbox desmarcado e passará a ser marcado ...
    }else if(document.getElementById('chkt_produto_acabado'+indice).checked == false && document.getElementById('chkt_produto_acabado'+indice).disabled == false) {
        document.getElementById('chkt_produto_acabado'+indice).checked  = true
        //Designer de Habilitado ...
        document.getElementById('txt_ajuste_mmv'+indice).className      = 'caixadetexto'
        document.getElementById('txt_mmv_inst_corr'+indice).className   = 'caixadetexto'
        //Habilita as Caixinhas ...
        document.getElementById('txt_ajuste_mmv'+indice).disabled       = false
        document.getElementById('txt_mmv_inst_corr'+indice).disabled    = false
        for (var i = 0; i < elements.length; i++) {
            if (elements[i].type == 'checkbox' && elements[i].disabled == false && elements[i].name != campo) {
                if (elements[i].checked == false) resultado = false
            }
        }
        campo_sel.checked = resultado
        //Cor da Linha ...
        if(navigator.appName == 'Netscape') {
            totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            totallinhas.rows[linha].style.backgroundColor = '#c6e2ff'
        }
    }
}