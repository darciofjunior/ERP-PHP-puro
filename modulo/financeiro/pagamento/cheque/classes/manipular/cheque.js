function selecionar(form, campo, totallinhas, cor_origem) {
    var campo_sel   = eval('document.'+form+'.'+campo+'')
    var elementos   = eval('document.'+form+'.elements')
    cor_origem      = cor_origem.toLowerCase()
    
    //Verifica o N.º de Linhas ...
    if(typeof(elementos['chkt_cheque[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_cheque[]'].length)
    }
    //Se o checkbox Principal estiver checado ...
    if(campo_sel.checked) {
        var checar      = true
        var travar      = false
        var layout      = 'caixadetexto'
    }else {//Se o checkbox Principal estiver deschecado ...
        var checar      = false
        var travar      = true
        var layout      = 'textdisabled'
    }
    
    for(var i = 0; i < linhas; i++) {
        document.getElementById('chkt_cheque'+i).checked        = checar
        //Habilita ou Desabilita os objetos ...
        document.getElementById('txt_historico'+i).disabled     = travar
        document.getElementById('txt_data'+i).disabled          = travar
        //Layout de Habilitado / Desabilitado dos Objetos ...
        document.getElementById('txt_historico'+i).className    = layout
        document.getElementById('txt_data'+i).className         = layout
    }

    if (campo_sel.checked) {
        if(navigator.appName == 'Netscape') {
            for (i = 3; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            for (i = 3; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = '#c6e2ff'
        }
    }else {
        for (i = 3; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = cor_origem
    }
}

function checkbox(form, campo, indice, cor_origem) {
    var campo_sel   = eval('document.'+form+'.'+campo+'')
    var elementos   = eval('document.'+form+'.elements')
    var linha       = eval(indice) + 3
    cor_origem      = cor_origem.toLowerCase()
    
    if(document.getElementById('chkt_cheque'+indice).checked) {//Se estiver habilitado, desabilita ...
        document.getElementById('chkt_cheque'+indice).checked       = false
        //Desabilita os Objetos ...
        document.getElementById('txt_historico'+indice).disabled    = true
        document.getElementById('txt_data'+indice).disabled         = true
        //Limpa os Objetos ...
        document.getElementById('txt_historico'+indice).value       = ''
        document.getElementById('txt_data'+indice).value            = ''
        //Layout de Desabilitado ...
        document.getElementById('txt_historico'+indice).className   = 'textdisabled'
        document.getElementById('txt_data'+indice).className        = 'textdisabled'
    }else {
        document.getElementById('chkt_cheque'+indice).checked       = true
        //Habilita os Objetos ...
        document.getElementById('txt_historico'+indice).disabled    = false
        document.getElementById('txt_data'+indice).disabled         = false
        //Layout de Habilitado ...
        document.getElementById('txt_historico'+indice).className   = 'caixadetexto'
        document.getElementById('txt_data'+indice).className        = 'caixadetexto'
    }
//Aqui é um controle para poder selecionar o checkbox principal
    var total_itens_cheques             = 0
    var total_itens_cheques_marcados    = 0

    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'chkt_cheque[]') {
            if(elementos[i].checked == true) {//Somente para Cheques q estão checados ...
                total_itens_cheques_marcados++
            }
            total_itens_cheques++
        }
    }
    
    //Significa que todos os itens de Cheques estão marcados
    if(total_itens_cheques == total_itens_cheques_marcados) {
        document.form.chkt_tudo.checked = true
    }else {
        document.form.chkt_tudo.checked = false
    }

    if(document.getElementById('chkt_cheque'+indice).checked) {//Se estiver habilitado, desabilita ...
        if(navigator.appName == 'Netscape') {
            totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            totallinhas.rows[linha].style.backgroundColor = '#c6e2ff'
        }
    }else {
        totallinhas.rows[linha].style.backgroundColor = cor_origem;
    }
}

function focos(objeto) {
    objeto.disabled = false
    objeto.focus()
    return false
}