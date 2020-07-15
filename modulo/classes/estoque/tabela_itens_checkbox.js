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

//Controle de Checkbox por Linha ...
function checkbox(indice, cor_origem) {
    var resultado   = true
    var linha       = eval(indice) + 3
//Verifico quantos checkbox existem na Tela ...
    var inputs = document.getElementsByTagName('input')
/*************************************************************/
//Habilita as Caixas ...
    document.getElementById('txt_dias'+indice).disabled             = false
    document.getElementById('txt_situacao'+indice).disabled         = false
//Aqui joga o Designer de Habilitado ...
    document.getElementById('txt_dias'+indice).className            = 'caixadetexto'
    document.getElementById('txt_situacao'+indice).className        = 'caixadetexto'
/*************************************************************/
    cor_origem = cor_origem.toLowerCase()
    if(document.getElementById('chkt_op'+indice).checked) {
//Desabilita as Caixas ...
        document.getElementById('txt_dias'+indice).disabled         = true
        document.getElementById('txt_situacao'+indice).disabled     = true
//Aqui joga o Designer de Habilitado
        document.getElementById('txt_dias'+indice).className        = 'textdisabled'
        document.getElementById('txt_situacao'+indice).className    = 'textdisabled'
        document.getElementById('chkt_op'+indice).checked           = false
//Controle p/ todos os Checkboxs ...
        for(var i = 0; i < inputs.length; i++) if(inputs[i].type == 'checkbox' && inputs[i].name == 'chkt_op[]' && inputs[i].checked == false) resultado = false
//Checkbox Principal ...
        document.getElementById('chkt_tudo').checked = resultado
    }else {
//Habilita as Caixas
        document.getElementById('txt_dias'+indice).disabled         = false
        document.getElementById('txt_situacao'+indice).disabled     = false
//Aqui joga o Designer de Habilitado
        document.getElementById('txt_dias'+indice).className        = 'caixadetexto'
        document.getElementById('txt_situacao'+indice).className    = 'caixadetexto'
        document.getElementById('chkt_op'+indice).checked           = true
//Controle p/ todos os Checkboxs ...
        for(var i = 0; i < inputs.length; i++) {
            if(inputs[i].type == 'checkbox' && inputs[i].name == 'chkt_op[]' && inputs[i].checked == false) resultado = false
        }
        //Checkbox Principal ...
        document.getElementById('chkt_tudo').checked = resultado
    }
//Controla a cor das linhas ...
    if(document.getElementById('chkt_op'+indice).checked) {
        if(navigator.appName == 'Netscape') {
            totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            totallinhas.rows[linha].style.backgroundColor = '#c6e2ff'
        }
    }else {
        totallinhas.rows[linha].style.backgroundColor = cor_origem;
    }
}

//Controle de Seleção p/ Todos os Checkbox ...
function selecionar_tudo(totallinhas, cor_origem) {
    cor_origem = cor_origem.toLowerCase()
    var elementos = 0
//Verifico quantos checkbox existem na Tela ...
    var inputs = document.getElementsByTagName('input')
    for(var i = 0; i < inputs.length; i++) {
        if(inputs[i].type == 'checkbox' && inputs[i].name == 'chkt_op[]') elementos++
    }
//Se o Checkbox Principal estiver marcado, então marco os demais ...
    if(document.getElementById('chkt_tudo').checked) {
        for(var i = 0; i < elementos; i++) {
            document.getElementById('chkt_op'+i).checked        = true
//Habilita as Caixas
            document.getElementById('txt_dias'+i).disabled      = false
            document.getElementById('txt_situacao'+i).disabled  = false
//Aqui joga o Designer de Habilitado
            document.getElementById('txt_dias'+i).className     = 'caixadetexto'
            document.getElementById('txt_situacao'+i).className = 'caixadetexto'
        }
//Se não estiver marcado, então desmarca todos ...
    }else {
        for(var i = 0; i < elementos; i++) {
            document.getElementById('chkt_op'+i).checked        = false
//Desabilita as Caixas
            document.getElementById('txt_dias'+i).disabled      = true
            document.getElementById('txt_situacao'+i).disabled  = true
//Aqui joga o Designer de Desabilitado
            document.getElementById('txt_dias'+i).className     = 'textdisabled'
            document.getElementById('txt_situacao'+i).className = 'textdisabled'
        }
    }
    if(document.getElementById('chkt_tudo').checked) {
        if(navigator.appName == 'Netscape') {
            for(i = 3; i < (totallinhas.rows.length - 2); i++) {
                totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
            }
        }else {
            for(i = 3; i < (totallinhas.rows.length - 2); i++) {
                totallinhas.rows[i].style.backgroundColor = '#c6e2ff'
            }
        }
    }else {
        for(i = 3; i < (totallinhas.rows.length - 2); i ++) {
            totallinhas.rows[i].style.backgroundColor = cor_origem
        }
    }
}