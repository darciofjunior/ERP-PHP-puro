/*Esse JS foi feito exclusivo para esse caso, devido eu ter uma caixa de texto para cada linha, sendo assim
fica um pouco diferente o controle de clicks e cores da linha*/
function cor_clique_celula(cel_atual, backgroundColor) {
    var cor_atual = cel_atual.style.backgroundColor
    var nova_cor = backgroundColor
    nova_cor = nova_cor.toLowerCase()
//Habilita ou Desabilita o Checkbox e Objeto Radio da Linha quando se d� um clique aonde o cursor do mouse esta posicionado
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

function selecionar_tudo(form, campo, totallinhas, cor_origem) {
    var elementos   = eval('document.'+form+'.elements')
    var campo_sel   = eval('document.'+form+'.'+campo+'')
    //Significa que est� tela foi carregada com apenas 1 linha ...
    var linhas      = (typeof(elementos['chkt_nfe_item[]'][0]) == 'undefined') ? 1 : elementos['chkt_nfe_item[]'].length

    if(campo_sel.checked == true) {//Checkbox Principal est� selecionado ...
        for(var i = 0; i < linhas; i++) {
            document.getElementById('chkt_nfe_item'+i).checked              = true
            //Se o Produto for um PA ...
            if(document.getElementById('hdd_tipo_de_produto'+i).value == 'PA') {
                //Habilita a Caixa ...
                document.getElementById('txt_entrada_antecipada'+i).disabled   = false
                //Aqui joga o Designer de Habilitado ...
                document.getElementById('txt_entrada_antecipada'+i).className  = 'caixadetexto'
            }
        }
//Cor das Linhas ...
        if(navigator.appName == 'Netscape') {
            for (var i = 3; i < (totallinhas.rows.length - 3); i++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            for (var i = 3; i < (totallinhas.rows.length - 3); i++) totallinhas.rows[i].style.backgroundColor = '#c6e2ff'
        }
    }else {//Checkbox Principal est� desmarcado ...
        for(var i = 0; i < linhas; i++) {
            document.getElementById('chkt_nfe_item'+i).checked              = false
            //Se o Produto for um PA ...
            if(document.getElementById('hdd_tipo_de_produto'+i).value == 'PA') {
                //Desabilita a Caixa ...
                document.getElementById('txt_entrada_antecipada'+i).disabled    = true
                document.getElementById('txt_entrada_antecipada'+i).value       = ''
                //Aqui joga o Designer de Desabilitado ...
                document.getElementById('txt_entrada_antecipada'+i).className   = 'textdisabled'
            }
        }
        //Cor das Linhas ...
        for(var i = 3; i < (totallinhas.rows.length - 3); i ++) totallinhas.rows[i].style.backgroundColor = cor_origem
    }
}

function checkbox(form, indice_linha, indice, cor_origem) {
    var elementos   = eval('document.'+form+'.elements')
    cor_origem      = cor_origem.toLowerCase()
    linha           = eval(indice_linha) + 3
    
    if(!document.getElementById('chkt_nfe_item'+indice).checked) {//Linha est� marcada ...
        document.getElementById('chkt_nfe_item'+indice).checked             = true
        //Se o Produto for um PA ...
        if(document.getElementById('hdd_tipo_de_produto'+indice).value == 'PA') {
            //Habilita a Caixa ...
            document.getElementById('txt_entrada_antecipada'+indice).disabled   = false
            //Aqui joga o Designer de Habilitado ...
            document.getElementById('txt_entrada_antecipada'+indice).className  = 'caixadetexto'
        }
    }else {//Linha est� desmarcada ...
        document.getElementById('chkt_nfe_item'+indice).checked             = false
        //Se o Produto for um PA ...
        if(document.getElementById('hdd_tipo_de_produto'+indice).value == 'PA') {
            //Desabilita a Caixa ...
            document.getElementById('txt_entrada_antecipada'+indice).disabled   = true
            document.getElementById('txt_entrada_antecipada'+indice).value      = ''
            //Aqui joga o Designer de Desabilitado ...
            document.getElementById('txt_entrada_antecipada'+indice).className  = 'textdisabled'
        }
    }

    if(document.getElementById('chkt_nfe_item'+indice).checked) {
        if(navigator.appName == 'Netscape') {
            totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            totallinhas.rows[linha].style.backgroundColor = '#c6e2ff'
        }
    }else {
        totallinhas.rows[linha].style.backgroundColor = cor_origem
    }

//Aqui � um controle para poder selecionar o checkbox principal ...
    var total_itens_nfe             = 0
    var total_itens_nfe_marcados    = 0

    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'chkt_nfe_item[]') {
            //Somente para pedidos q est�o checados
            if(elementos[i].checked == true) total_itens_nfe_marcados++
            total_itens_nfe++
        }
    }
//Significa que todos os itens de Nota Fiscal est�o marcados ...
    if(total_itens_nfe == total_itens_nfe_marcados) {
        document.form.chkt_tudo.checked = true
    }else {
        document.form.chkt_tudo.checked = false
    }
}