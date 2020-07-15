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

function checkbox_habilita(indice, cor_origem) {
    var elementos   = document.form.elements
    cor_origem      = cor_origem.toLowerCase()
    var linha       = eval(indice) + 3

    if(!(document.getElementById('chkt_cotacao_item'+indice).checked)) {
        document.getElementById('chkt_cotacao_item'+indice).checked     = true
        //Habilita os objetos ...
        document.getElementById('txt_qtde_pedida'+indice).disabled      = false
        document.getElementById('txt_preco_unitario'+indice).disabled   = false
        document.getElementById('txt_marca_observacao'+indice).disabled = false
        document.getElementById('hdd_ipi'+indice).disabled              = false
        document.getElementById('hdd_ipi_incluso'+indice).disabled      = false
        //Muda a cor para habilitado ...
        document.getElementById('txt_qtde_pedida'+indice).className     = 'caixadetexto'
        document.getElementById('txt_preco_unitario'+indice).className  = 'caixadetexto'
        document.getElementById('txt_marca_observacao'+indice).className= 'caixadetexto'
    }else {
        document.getElementById('chkt_cotacao_item'+indice).checked     = false
        //Desabilita os objetos ...
        document.getElementById('txt_qtde_pedida'+indice).disabled      = true
        document.getElementById('txt_preco_unitario'+indice).disabled   = true
        document.getElementById('txt_marca_observacao'+indice).disabled = true
        document.getElementById('hdd_ipi'+indice).disabled              = true
        document.getElementById('hdd_ipi_incluso'+indice).disabled      = true
        //Muda a cor para desabilitado ...
        document.getElementById('txt_qtde_pedida'+indice).className     = 'textdisabled'
        document.getElementById('txt_preco_unitario'+indice).className  = 'textdisabled'
        document.getElementById('txt_marca_observacao'+indice).className= 'textdisabled'
    }
    
    if(document.getElementById('chkt_cotacao_item'+indice).checked) {
        if(navigator.appName == 'Netscape') {
            totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            totallinhas.rows[linha].style.backgroundColor = '#c6e2ff'
        }
    }else {
        totallinhas.rows[linha].style.backgroundColor = cor_origem
    }

//Aqui é um controle para poder selecionar o checkbox principal
    var total_itens_cotacoes = 0
    var total_itens_cotacoes_marcados = 0
    
    if(typeof(elementos['chkt_cotacao_item[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_cotacao_item[]'].length)
    }
    for(i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_cotacao_item'+i).checked == true) total_itens_cotacoes_marcados++
        total_itens_cotacoes++
    }
//Significa que todos os itens de Cotação estão marcados ...
    document.form.chkt_tudo.checked = (total_itens_cotacoes == total_itens_cotacoes_marcados) ? true : false
    calcular_total_geral(linhas)
}

function selecionar(form, campo, totallinhas, cor_origem) {
    var campo_sel 	= eval('document.'+form+'.'+campo+'')
    var elementos 	= eval('document.'+form+'.elements')
    cor_origem 		= cor_origem.toLowerCase()

    if(typeof(elementos['chkt_cotacao_item[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_cotacao_item[]'].length)
    }

    for(var i = 0; i < linhas; i++) {
        if(campo_sel.checked == true) {//Aqui verifico se o Checkbox Principal está marcado ...
            if(document.getElementById('chkt_cotacao_item'+i).type == 'checkbox') {//Só irá habilitar a linha quando for Checkbox mesmo ...
                document.getElementById('chkt_cotacao_item'+i).checked      = true
                //Habilita os objetos ...
                document.getElementById('txt_qtde_pedida'+i).disabled       = false
                document.getElementById('txt_preco_unitario'+i).disabled    = false
                document.getElementById('txt_marca_observacao'+i).disabled  = false
                document.getElementById('hdd_ipi'+i).disabled               = false
                document.getElementById('hdd_ipi_incluso'+i).disabled       = false
                //Aqui joga o Designer de Habilitado ...
                document.getElementById('txt_qtde_pedida'+i).className      = 'caixadetexto'
                document.getElementById('txt_preco_unitario'+i).className   = 'caixadetexto'
                document.getElementById('txt_marca_observacao'+i).className = 'caixadetexto'
            }
        }else {
            document.getElementById('chkt_cotacao_item'+i).checked      = false
            //Desabilita os objetos ...
            document.getElementById('txt_qtde_pedida'+i).disabled       = true
            document.getElementById('txt_preco_unitario'+i).disabled    = true
            document.getElementById('txt_marca_observacao'+i).disabled  = true
            document.getElementById('hdd_ipi'+i).disabled               = true
            document.getElementById('hdd_ipi_incluso'+i).disabled       = true
            //Aqui joga o Designer de Desabilitado ...
            document.getElementById('txt_qtde_pedida'+i).className      = 'textdisabled'
            document.getElementById('txt_preco_unitario'+i).className         = 'textdisabled'
            document.getElementById('txt_marca_observacao'+i).className = 'textdisabled'
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
    calcular_total_geral(linhas)
}

function calcular_total_geral(linhas) {
    var elementos   = document.form.elements
    var	valor_total = 0
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_cotacao_item'+i).checked == true) {//Verifica somente os Checkbox selecionados ...
            document.getElementById('txt_valor_total'+i).value = strtofloat(document.getElementById('txt_qtde_pedida'+i).value) * strtofloat(document.getElementById('txt_preco_unitario'+i).value)
            document.getElementById('txt_valor_total'+i).value = arred(document.getElementById('txt_valor_total'+i).value, 2, 1)
            valor_total+= eval(strtofloat(document.getElementById('txt_valor_total'+i).value))
        }
    }
    document.form.txt_total_geral.value = valor_total
    document.form.txt_total_geral.value = arred(document.form.txt_total_geral.value, 2, 1)
}