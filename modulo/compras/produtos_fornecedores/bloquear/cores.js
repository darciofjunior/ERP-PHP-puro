function sobre_celula_2(cel_atual, backgroundColor) {
    var cor_atual = cel_atual.style.backgroundColor
    if(cor_atual == '') cor_atual = '#c6e2ff'
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
    cel_atual.style.cursor='hand'
}

function checkbox_2(form, campo, valor, cor_click, cor_nclick) {
    var campo_sel   = eval('document.'+form+'.'+campo+'')
    var elementos   = eval('document.'+form+'.elements')
    var resultado = true
    valor++;
    linha = valor + 2
    cor_click   = cor_click.toLowerCase()
    cor_nclick  = cor_nclick.toLowerCase()
    if (elementos[valor].type == 'checkbox') {
        if (!(elementos[valor].checked)) {
            elementos[valor].checked = true
            for (var i = 0; i < elementos.length; i++) {
                if(elementos[i].type == 'checkbox' && elementos[i].name != campo) {
                    if (elementos[i].checked == false) resultado = false
                }
            }
            campo_sel.checked       = resultado
        }else {
            campo_sel.checked           = false
            elementos[valor].checked    = false
        }
    }
    if(elementos[valor].checked) {
        totallinhas.rows[linha].style.backgroundColor = cor_click
    }else {
        totallinhas.rows[linha].style.backgroundColor = cor_nclick
    }
}