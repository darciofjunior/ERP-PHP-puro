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

function checkbox(indice, cor_origem) {
    cor_origem  = cor_origem.toLowerCase()
    linha       = eval(indice) + 2
/*************************************************************/
    //Habilita as Caixas
    document.getElementById('txt_qtde_devolver'+indice).disabled            = false
    document.getElementById('txt_preco_liq_devolver'+indice).disabled       = false
    document.getElementById('txt_comissao_devolver'+indice).disabled        = false
    //Aqui joga o Designer de Habilitado
    document.getElementById('txt_qtde_devolver'+indice).className           = 'caixadetexto'
    document.getElementById('txt_preco_liq_devolver'+indice).className      = 'caixadetexto'
    document.getElementById('txt_comissao_devolver'+indice).className       = 'caixadetexto'
/*************************************************************/
    if(document.getElementById('chkt_nfs_item'+indice).checked) {
        //Desabilita as Caixas
        document.getElementById('txt_qtde_devolver'+indice).disabled        = true
        document.getElementById('txt_preco_liq_devolver'+indice).disabled   = true
        document.getElementById('txt_comissao_devolver'+indice).disabled    = true
//Aqui joga o Designer de Desabilitado
        document.getElementById('txt_qtde_devolver'+indice).className       = 'textdisabled'
        document.getElementById('txt_preco_liq_devolver'+indice).className  = 'textdisabled'
        document.getElementById('txt_comissao_devolver'+indice).className   = 'textdisabled'
        document.getElementById('chkt_nfs_item'+indice).checked             = false
    }else {
        document.getElementById('chkt_nfs_item'+indice).checked             = true
    }
    if(document.getElementById('chkt_nfs_item'+indice).checked) {
        if(navigator.appName == 'Netscape') {
            totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            totallinhas.rows[linha].style.backgroundColor = '#c6e2ff'
        }
    }else {
        totallinhas.rows[linha].style.backgroundColor = cor_origem
    }
//Aqui é um controle para poder selecionar o checkbox principal
    var total_itens_nfs             = 0
    var total_itens_nfs_marcados    = 0
    var elementos                   = document.form.elements

    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'chkt_nfs_item[]') {
            if(elementos[i].checked == true) {//Somente para orçamentos q estão checados
                total_itens_nfs_marcados++
            }
            total_itens_nfs++
        }
    }
//Significa que todos os itens de Orçamento estão marcados
    if(total_itens_nfs == total_itens_nfs_marcados) {
        document.form.chkt_tudo.checked = true
    }else {
        document.form.chkt_tudo.checked = false
    }
}

function selecionar(form, campo, totallinhas, cor_origem) {
    var campo_sel   = eval('document.'+form+'.'+campo+'')
    var elementos   = eval('document.'+form+'.elements')
    cor_origem = cor_origem.toLowerCase()
    
    if(typeof(elementos['chkt_nfs_item[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_nfs_item[]'].length)
    }
    
    for (i = 0; i < linhas; i++) {
        if (campo_sel.checked) {
            document.getElementById('chkt_nfs_item'+i).checked              = true
            //Habilita as Caixas
            document.getElementById('txt_qtde_devolver'+i).disabled         = false
            document.getElementById('txt_preco_liq_devolver'+i).disabled    = false
            document.getElementById('txt_comissao_devolver'+i).disabled     = false
            //Aqui joga o Designer de Habilitado
            document.getElementById('txt_qtde_devolver'+i).className        = 'caixadetexto'
            document.getElementById('txt_preco_liq_devolver'+i).className   = 'caixadetexto'
            document.getElementById('txt_comissao_devolver'+i).className    = 'caixadetexto'
        }else {
            document.getElementById('chkt_nfs_item'+i).checked              = false
            //Desabilita as Caixas
            document.getElementById('txt_qtde_devolver'+i).disabled         = true
            document.getElementById('txt_preco_liq_devolver'+i).disabled    = true
            document.getElementById('txt_comissao_devolver'+i).disabled     = true
            //Aqui joga o Designer de Desabilitado
            document.getElementById('txt_qtde_devolver'+i).className        = 'textdisabled'
            document.getElementById('txt_preco_liq_devolver'+i).className   = 'textdisabled'
            document.getElementById('txt_comissao_devolver'+i).className    = 'textdisabled'
        }
    }

    if (campo_sel.checked) {
        if(navigator.appName == 'Netscape') {
            for (i = 2; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            for (i = 2; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }
    }else {
        for (i = 2; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = cor_origem
    }
}