function validar_checkbox(formulario, mensagem) {
    var cont            = 0 , valor = false, elementos = eval('document.'+formulario+'.elements')
    var caracteres      = '0123456789'
    var caracteres2     = '0123456789,.'
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) valor = true
        }
    }
    if (valor == false) {
        alert(mensagem)
        return false
    }else {
        if(typeof(elementos['chkt_produto_insumo[]'][0]) == 'undefined') {
            var linhas = 1//Existe apenas 1 único elemento ...
	}else {
            var linhas = (elementos['chkt_produto_insumo[]'].length)
	}
        for (var i = 0; i < linhas; i++) {
            if(document.getElementById('chkt_produto_insumo'+i).checked == true) {
                if(document.getElementById('txt_qtde'+i).value == '') {
                    alert('DIGITE A QUANTIDADE !')
                    document.getElementById('txt_qtde'+i).focus()
                    return false
                }
                if(eval(strtofloat(document.getElementById('txt_qtde'+i).value)) <= 0) {
                    alert('QUANTIDADE INVÁLIDA !')
                    document.getElementById('txt_qtde'+i).focus()
                    document.getElementById('txt_qtde'+i).select()
                    return false
                }
                for(var j = 0; j < document.getElementById('txt_qtde'+i).value.length; j++) {
                    if (caracteres2.indexOf(document.getElementById('txt_qtde'+i).value.charAt(j), 0) == -1) {
                        alert('QUANTIDADE INVÁLIDA !')
                        document.getElementById('txt_qtde'+i).focus()
                        document.getElementById('txt_qtde'+i).select()
                        return false
                    }
                }
            }
        }
        //Tratamento com os campos p/ gravar no BD ...
        for (var i = 0; i < linhas; i++) {
            if(document.getElementById('chkt_produto_insumo'+i).checked == true) document.getElementById('txt_qtde'+i).value = strtofloat(document.getElementById('txt_qtde'+i).value)
        }
    }
    return true
}

function selecionar(form, campo, totallinhas, cor_origem) {
    var campo_sel   = eval('document.'+form+'.'+campo+'')
    var elementos   = eval('document.'+form+'.elements')
    cor_origem      = cor_origem.toLowerCase()
    
    if(typeof(elementos['chkt_produto_insumo[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_produto_insumo[]'].length)
    }
    
    if(campo_sel.checked) {
        var checar      = true
        var layout      = 'caixadetexto'
        var habilitado  = true
    }else {
        var checar      = false
        var layout      = 'textdisabled'
        var habilitado  = false
    }
    
    for (var i = 0; i < linhas; i++) {
        document.getElementById('chkt_produto_insumo'+i).checked    = checar
        document.getElementById('txt_qtde'+i).disabled              = habilitado
        document.getElementById('txt_qtde'+i).className             = layout
    }

    if (campo_sel.checked) {
        if(navigator.appName == 'Netscape') {
            for(i = 3; i < (totallinhas.rows.length - 1); i++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            for(i = 3; i < (totallinhas.rows.length - 1); i++) totallinhas.rows[i].style.backgroundColor = '#c6e2ff'
        }
        elementos[2].focus()
    }else {
        for (i = 3; i < (totallinhas.rows.length - 1); i++) totallinhas.rows[i].style.backgroundColor = cor_origem
    }
}

function checkbox_habilita(form, campo, indice, cor_origem) {
    var campo_sel   = eval('document.'+form+'.'+campo+'')
    var elementos   = eval('document.'+form+'.elements')
    var resultado   = true
    var linha       = eval(indice) + 3
    cor_origem      = cor_origem.toLowerCase()
    
    if(document.getElementById('chkt_produto_insumo'+indice).checked == true) {
        document.getElementById('chkt_produto_insumo'+indice).checked   = false
        document.getElementById('txt_qtde'+indice).disabled             = true
        document.getElementById('txt_qtde'+indice).className            = 'textdisabled'
        campo_sel.checked                                               = false
    }else {
        document.getElementById('chkt_produto_insumo'+indice).checked   = true
        document.getElementById('txt_qtde'+indice).disabled             = false
        document.getElementById('txt_qtde'+indice).className            = 'caixadetexto'
        for (var i = 0; i < elementos.length; i++) {
            if(elementos[i].type == 'checkbox' && elementos[i].name != campo) {
                if (elementos[i].checked == false) resultado = false
            }
        }
        campo_sel.checked = resultado
    }
    if(document.getElementById('chkt_produto_insumo'+indice).checked == true) {
        if(navigator.appName == 'Netscape') {
            totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            totallinhas.rows[linha].style.backgroundColor = '#c6e2ff'
        }
    }else {
        totallinhas.rows[linha].style.backgroundColor = cor_origem
    }
}

function focos(objeto) {
    objeto.disabled = false
    objeto.focus()
    return false
}