function validar_checkbox(formulario, mensagem) {
    var cont = 0 , valor = false, elementos = eval('document.'+formulario+'.elements')
    var caracteres2 = '0123456789/'
    for (var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if (elementos[i].checked == true) valor = true
        }
    }
    if (valor == false) {
        alert(mensagem)
        return false
    }else {
        for (var i = 1; i < (elementos.length - 2); i+=3) {
//Campo do Valor de Custo
            if (elementos[i + 2].type == 'text') {
                if (elementos[i + 2].disabled == false) {
                    if(elementos[i + 2].value == '') {
                        alert('DIGITE A DATA DE COMPENSAÇÃO !')
                        elementos[i + 2].focus()
                        return false
                    }
                    for (j = 0; j < elementos[i + 2].value.length; j++) {
                        if (caracteres2.indexOf(elementos[i + 2].value.charAt(j), 0) == -1) {
                            alert('DATA DE COMPENSAÇÃO INVÁLIDA !')
                            elementos[i + 2].focus()
                            elementos[i + 2].select()
                            return false
                        }
                    }
                }
            }
        }
    }
    return true
}

function selecionar(form, campo, totallinhas, cor_origem) {
    var campo_sel = eval('document.'+form+'.'+campo+'')
    var elementos = eval('document.'+form+'.elements')
    cor_origem = cor_origem.toLowerCase()
    for (i = 1; i < elementos.length; i ++) {
        if (elementos[i].type == 'checkbox') {
            if (campo_sel.checked) {
                //Habilita os Elementos ...
                elementos[i].checked        = true
                elementos[i + 1].disabled   = false
                elementos[i + 2].disabled   = false
                //Muda o Layout p/ Habilitado ...
                elementos[i + 1].className  = 'caixadetexto'
                elementos[i + 2].className  = 'caixadetexto'
            }else {
                //Desabilita os Elementos ...
                elementos[i].checked        = false
                elementos[i].disabled       = false
                elementos[i + 1].disabled   = true
                elementos[i + 2].disabled   = true
                //Muda o Layout p/ Desabilitado ...
                elementos[i + 1].className  = 'textdisabled'
                elementos[i + 2].className  = 'textdisabled'
            }
        }
    }

    if(campo_sel.checked) {
        if(navigator.appName == 'Netscape') {
            for (i = 3; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            for (i = 3; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = '#c6e2ff'
        }
        elementos[2].focus()
    }else {
        for (i = 3; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = cor_origem
    }
}

function checkbox_habilita(form, campo, valor, cor_origem) {
    var campo_sel   = eval('document.'+form+'.'+campo+'')
    var elementos   = eval('document.'+form+'.elements')
    var i, valor_aux, resultado = true
    valor_aux = valor
    linha = eval(valor) + 3
    if(valor_aux == 0) {
        valor++;
//Habilita as Caixas
        elementos[valor + 1].disabled = false
        elementos[valor + 2].disabled = false
//Muda a Cor das Caixas para Habilitado
        elementos[valor + 1].className = 'caixadetexto'
        elementos[valor + 2].className = 'caixadetexto'
    }
    if(valor_aux == 1) {
        valor = eval(valor) + 3
//Habilita as Caixas
        elementos[valor + 1].disabled = false
        elementos[valor + 2].disabled = false
//Muda a Cor das Caixas para Habilitado
        elementos[valor + 1].className = 'caixadetexto'
        elementos[valor + 2].className = 'caixadetexto'
    }
    if(valor_aux > 1) {
        valor = valor * 3
        valor ++;
//Habilita as Caixas
        elementos[valor + 1].disabled = false
        elementos[valor + 2].disabled = false
//Muda a Cor das Caixas para Habilitado
        elementos[valor + 1].className = 'caixadetexto'
        elementos[valor + 2].className = 'caixadetexto'
    }
    cor_origem = cor_origem.toLowerCase()
    if (elementos[valor].type == 'checkbox') {
        if(elementos[valor].checked) {
            campo_sel.checked = false
//Desabilita as Caixas
            elementos[valor + 1].disabled = true
            elementos[valor + 2].disabled = true
//Muda a Cor das Caixas para Desabilitado ...
            elementos[valor + 1].className = 'textdisabled'
            elementos[valor + 2].className = 'textdisabled'
            elementos[valor].checked = false
        }else {
            elementos[valor].checked = true
            for(i = 0; i <elementos.length; i++) {
                if(elementos[i].type == 'checkbox' && elementos[i].name != campo) {
                    if (elementos[i].checked == false) resultado = false
                }
            }
            campo_sel.checked = resultado
        }
    }
    if (elementos[valor].checked) {
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