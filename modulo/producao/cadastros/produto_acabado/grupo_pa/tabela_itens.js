/*Esse JS foi feito exclusivo para esse caso, devido eu ter uma caixa de texto para cada linha, sendo assim
fica um pouco diferente o controle de clicks e cores da linha*/
function cor_clique_celula(cel_atual, backgroundColor) {
    var cor_origem  = cel_atual.bgColor
    var cor_atual   = cel_atual.style.backgroundColor
    var nova_cor    = backgroundColor
    nova_cor        = nova_cor.toLowerCase()
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
    linha           = eval(indice) + 3

    if(!document.getElementById('chkt_empresa_divisao'+indice).checked) {
//Habilita as caixas ...
        document.getElementById('chkt_empresa_divisao'+indice).checked      = true
        document.getElementById('txt_desc_base_nac_a'+indice).disabled      = false
        document.getElementById('txt_desc_base_nac_b'+indice).disabled      = false
        document.getElementById('txt_acrescimo_base_nac'+indice).disabled   = false
        document.getElementById('txt_ml_min_exp'+indice).disabled           = false
        document.getElementById('txt_ml_min_nac'+indice).disabled           = false
        document.getElementById('txt_comissao_extra'+indice).disabled       = false
        document.getElementById('txt_data_limite'+indice).disabled          = false
        document.getElementById('txt_caminho_pdf_site'+indice).disabled     = false
//Layout de Habilitado ...
        document.getElementById('txt_desc_base_nac_a'+indice).className      = 'caixadetexto'
        document.getElementById('txt_desc_base_nac_b'+indice).className      = 'caixadetexto'
        document.getElementById('txt_acrescimo_base_nac'+indice).className   = 'caixadetexto'
        document.getElementById('txt_ml_min_exp'+indice).className           = 'caixadetexto'
        document.getElementById('txt_ml_min_nac'+indice).className           = 'caixadetexto'
        document.getElementById('txt_comissao_extra'+indice).className       = 'caixadetexto'
        document.getElementById('txt_data_limite'+indice).className          = 'caixadetexto'
        document.getElementById('txt_caminho_pdf_site'+indice).className     = 'caixadetexto'
    }else {
//Desabilita as caixas ...
        document.getElementById('chkt_empresa_divisao'+indice).checked      = false
        document.getElementById('txt_desc_base_nac_a'+indice).disabled      = true
        document.getElementById('txt_desc_base_nac_b'+indice).disabled      = true
        document.getElementById('txt_acrescimo_base_nac'+indice).disabled   = true
        document.getElementById('txt_ml_min_exp'+indice).disabled           = true
        document.getElementById('txt_ml_min_nac'+indice).disabled           = true
        document.getElementById('txt_comissao_extra'+indice).disabled       = true
        document.getElementById('txt_data_limite'+indice).disabled          = true
        document.getElementById('txt_caminho_pdf_site'+indice).disabled     = true
//Layout de Desabilitado ...
        document.getElementById('txt_desc_base_nac_a'+indice).className      = 'textdisabled'
        document.getElementById('txt_desc_base_nac_b'+indice).className      = 'textdisabled'
        document.getElementById('txt_acrescimo_base_nac'+indice).className   = 'textdisabled'
        document.getElementById('txt_ml_min_exp'+indice).className           = 'textdisabled'
        document.getElementById('txt_ml_min_nac'+indice).className           = 'textdisabled'
        document.getElementById('txt_comissao_extra'+indice).className       = 'textdisabled'
        document.getElementById('txt_data_limite'+indice).className          = 'textdisabled'
        document.getElementById('txt_caminho_pdf_site'+indice).className     = 'textdisabled'
    }
    
    if(document.getElementById('chkt_empresa_divisao'+indice).checked) {
        if(navigator.appName == 'Netscape') {
            totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            totallinhas.rows[linha].style.backgroundColor = '#c6e2ff'
        }
    }else {
        totallinhas.rows[linha].style.backgroundColor = cor_origem
    }
}