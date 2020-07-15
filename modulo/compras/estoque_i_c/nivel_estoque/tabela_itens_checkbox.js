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
    var elementos   = document.form.elements
    cor_origem      = cor_origem.toLowerCase()
    var linha       = eval(indice) + 3

    //Aqui se adiciona mais um para o cont para pular o primeiro checkbox principal
    if(!(document.getElementById('chkt_produto_insumo'+indice).checked)) {
        document.getElementById('chkt_produto_insumo'+indice).checked   = true
        //Habilita os objetos
        document.getElementById('txt_qtde_compra'+indice).disabled      = false
        document.getElementById('txt_qtde_metros'+indice).disabled      = false
        //Muda a cor para habilitado
        document.getElementById('txt_qtde_metros'+indice).className     = 'caixadetexto'
    }else {
        document.getElementById('chkt_produto_insumo'+indice).checked   = false
        //Desabilita os objetos
        document.getElementById('txt_qtde_compra'+indice).disabled      = true
        document.getElementById('txt_qtde_metros'+indice).disabled      = true
        document.getElementById('txt_qtde_metros'+indice).value         = ''
        //Muda a cor para desabilitado
        document.getElementById('txt_qtde_metros'+indice).className     = 'textdisabled'
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
    var total_produtos_insumos = 0
    var total_produtos_insumos_marcados = 0
    
    if(typeof(elementos['chkt_produto_insumo[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_produto_insumo[]'].length)
    }
    for(i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_produto_insumo'+i).checked == true) total_produtos_insumos_marcados++
        total_produtos_insumos++
    }
//Significa que todos os itens de Cotação estão marcados ...
    document.form.chkt_tudo.checked = (total_produtos_insumos == total_produtos_insumos_marcados) ? true : false
}

function selecionar(form, campo, totallinhas, cor_origem) {
    var campo_sel 	= eval('document.'+form+'.'+campo+'')
    var elementos 	= eval('document.'+form+'.elements')
    cor_origem 		= cor_origem.toLowerCase()

    if(typeof(elementos['chkt_produto_insumo[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_produto_insumo[]'].length)
    }

    for(var i = 0; i < linhas; i++) {
        if(campo_sel.checked == true) {//Aqui verifico se o Checkbox Principal está marcado ...
            document.getElementById('chkt_produto_insumo'+i).checked    = true
            //Habilita os objetos
            document.getElementById('txt_qtde_compra'+i).disabled       = false
            document.getElementById('txt_qtde_metros'+i).disabled       = false
            //Muda a cor para habilitado
            document.getElementById('txt_qtde_metros'+i).className      = 'caixadetexto'
        }else {
            document.getElementById('chkt_produto_insumo'+i).checked    = false
            //Desabilita os objetos
            document.getElementById('txt_qtde_compra'+i).disabled       = true
            document.getElementById('txt_qtde_metros'+i).disabled       = true
            document.getElementById('txt_qtde_metros'+i).value          = ''
            //Muda a cor para desabilitado
            document.getElementById('txt_qtde_metros'+i).className      = 'textdisabled'
	}
    }
    if(campo_sel.checked) {//Checkbox Principal ...
        if(navigator.appName == 'Netscape') {
            for (i = 3; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            for (i = 3; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }
    }else {
        for (i = 3; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = cor_origem
    }
}