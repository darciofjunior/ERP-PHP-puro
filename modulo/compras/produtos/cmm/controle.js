function validar_checkbox(formulario, mensagem) {
    var i, cont = 0 , valor = false, elementos = eval('document.'+formulario+'.elements')
    var caracteres = '0123456789'
    var caracteres2 = '0123456789,.'
    for (i = 0; i < elementos.length; i ++)   {
        if (elementos[i].type == 'checkbox')  {
            if (elementos[i].checked == true) valor = true
        }
    }
    if (valor == false) {
        alert(mensagem)
        return false
    }else {
        var objetos_linha = 2
        var objetos_fim = 2
        for (i = 1; i < (elementos.length - objetos_fim); i+=objetos_linha) {
            if (elementos[i].type == 'text') {
                if (elementos[i].disabled == false) {
                    if(elementos[i].value == '') {
                        alert('DIGITE A QUANTIDADE !')
                        elementos[i].focus()
                        return false
                    }
                    if(elementos[i].value <= 0) {
                        alert('QUANTIDADE INVÁLIDA !')
                        elementos[i].focus()
                        elementos[i].select()
                        return false
                    }
                    for (j = 0; j < elementos[i].value.length; j ++) {
                        if (caracteres2.indexOf(elementos[i].value.charAt(j), 0) == -1) {
                            alert('QUANTIDADE INVÁLIDA !')
                            elementos[i].focus()
                            elementos[i].select()
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
    var elements = eval('document.'+form+'.elements')
    cor_origem = cor_origem.toLowerCase()
    for (i = 1; i < elements.length; i ++) {
        if (elements[i].type == 'checkbox') {
            if (campo_sel.checked) {
                elements[i].checked = true
                elements[i + 1].disabled = false
//Designer de Habilitado
                elements[i + 1].className = 'caixadetexto'
                elements[i + 1].disabled = false
            }else {
                elements[i].checked = false
                elements[i].disabled = false
//Designer de Desabilitado
                elements[i + 1].disabled = true
                elements[i + 1].className = 'textdisabled'
            }
        }
    }
    if (campo_sel.checked) {
        if(navigator.appName == 'Netscape') {
            for (i = 3; i < (totallinhas.rows.length - 1); i ++) {
                totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)';
            }
        }else {
            for (i = 3; i < (totallinhas.rows.length - 1); i ++) {
                totallinhas.rows[i].style.backgroundColor = '#c6e2ff';
            }
        }
        elements[2].focus()
    }else {
        for (i = 3; i < (totallinhas.rows.length - 1); i ++) {
            totallinhas.rows[i].style.backgroundColor = cor_origem;
        }
    }
}

function checkbox_habilita(form, campo, valor, cor_origem) {
    var campo_sel = eval('document.'+form+'.'+campo+'')
    var elements = eval('document.'+form+'.elements')
    var resultado = true, objetos_linha = 2
    var valor = eval(valor), linha = valor + 3

    if(valor == 0) {
        valor++
    }else if(valor == 1) {
        valor+= objetos_linha
    }else {
        valor*= objetos_linha
        valor++
    }
    cor_origem = cor_origem.toLowerCase()
    
    if (elements[valor].type == 'checkbox') {
        if(elements[valor].checked) {
            campo_sel.checked       = false
            elements[valor].checked = false
            //Designer de Desabilitado
            elements[valor + 1].disabled    = true
            elements[valor + 1].className   = 'textdisabled'
        }else {
            elements[valor].checked = true
            //Designer de Habilitado
            elements[valor + 1].className   = 'caixadetexto'
            elements[valor + 1].disabled    = false
            elements[valor + 1].focus()
            for (var i = 0; i < elements.length; i++) {
                if (elements[i].type == 'checkbox' && elements[i].name != campo) {
                    if (elements[i].checked == false) resultado = false
                }
            }
            campo_sel.checked = resultado
        }
    }
    if(elements[valor].checked) {
        if(navigator.appName == 'Netscape') {
            totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)';
        }else {
            totallinhas.rows[linha].style.backgroundColor = '#c6e2ff';
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