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
    cel_atual.style.cursor = 'hand'
}

function fora_celula(cel_atual, backgroundColor) {
    var cor_atual = cel_atual.style.backgroundColor
    var nova_cor = backgroundColor
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

function checkbox_habilita(indice, cor_origem) {
    var cor_origem      = cor_origem.toLowerCase()
    var linha           = eval(indice) + 3

    if (!(document.getElementById('chkt_conta_receber'+indice).checked)) {
//Marca o Checkbox
        document.getElementById('chkt_conta_receber'+indice).checked        = true
//Habilita a caixa ...
        document.getElementById('txt_valor_recebendo_rs'+indice).disabled   = false
//Muda a cor da Caixa p/ Habilitado ...
        document.getElementById('txt_valor_recebendo_rs'+indice).className  = 'caixadetexto'
        document.getElementById('txt_valor_recebendo_rs'+indice).focus()
    }else {
//Desmarcar o Checkbox
        document.getElementById('chkt_conta_receber'+indice).checked        = false
//Desabilita a caixa
        document.getElementById('txt_valor_recebendo_rs'+indice).disabled   = true
//Muda a cor da Caixa p/ Desabilitado
        document.getElementById('txt_valor_recebendo_rs'+indice).className  = 'textdisabled'
    }
/************Controle de Cor da Linha************/
    if(document.getElementById('chkt_conta_receber'+indice).checked) {
        if(navigator.appName == 'Netscape') {
            totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            totallinhas.rows[linha].style.backgroundColor = '#c6e2ff'
        }
    }else {
        totallinhas.rows[linha].style.backgroundColor = cor_origem
    }
    travar_formulario_contas_a_pagar()
}

function travar_formulario_contas_a_pagar() {
    //Verifico se existe pelo menos 1 Conta à Receber selecionada ...
    var contas_receber_selecionadas = 0
    var elementos_contas_receber    = document.form_contas_receber.elements
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos_contas_receber['chkt_conta_receber[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos_contas_receber['chkt_conta_receber[]'].length)
    }
    //Aqui faz toda essa preparação para poder gravar no BD ...
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_conta_receber'+i).checked == true) {
            contas_receber_selecionadas++//Verifico se foi selecionada pelo menos 1 Conta à Receber ...
            break;
        }
    }
    if(contas_receber_selecionadas > 0) {//Temos pelo menos uma Conta à Receber selecionada ...
        var className   = 'textdisabled'
        var disabled    = 'disabled'
    }else {//Não existe nenhuma Conta à Receber selecionada ...
        var className   = 'caixadetexto'
        var disabled    = ''
    }
/*Tratamento com os campos do Primeiro Formulário de Contas à Pagar, caso o usuário tenha marcado ou 
desmarcado alguma Conta à Receber ...*/    
    var id_contas_apagar        = String(document.form.id_conta_apagar.value)
    var linhas_contas_apagar    = id_contas_apagar.split(',')
    
    for(i = 0; i < linhas_contas_apagar.length; i++) {//Destravo a parte de Contas à Pagar ...
        document.getElementById('txt_valor_pagando'+i).className    = className
        document.getElementById('txt_valor_pagando'+i).disabled     = disabled
    }
}

function selecionar(form, campo, totallinhas, cor_origem) {
    var campo_sel   = eval('document.'+form+'.'+campo+'')
    var elementos   = eval('document.'+form+'.elements')
    cor_origem = cor_origem.toLowerCase()
    var objetos_inicio = 1//Objetos antes do loop
    var objetos_linha = 6//Qtde de Objetos por Linha
    var objetos_fim = 8//Objetos depois do loop

    for (i = objetos_inicio; i < (elementos.length) - objetos_fim; i+=objetos_linha) {
        if (elementos[i].type == 'checkbox') {
            if (campo_sel.checked) {//Aqui são os checkbox
                elementos[i].checked = true
//Habilita a caixa
                elementos[i + 5].disabled = false
//Muda a cor da Caixa p/ Habilitado
                elementos[i + 5].className = 'caixadetexto'
            }else {
                elementos[i].checked = false//Aqui são os checkbox
//Desabilita a caixa
                elementos[i + 5].disabled = true
//Muda a cor da Caixa p/ Desabilitado
                elementos[i + 5].className = 'textdisabled'
            }
        }
    }

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