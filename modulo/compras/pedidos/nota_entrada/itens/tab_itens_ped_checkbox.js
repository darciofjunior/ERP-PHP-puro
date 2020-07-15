function validar_checkbox(form, mensagem) {
    var elementos   = eval('document.'+form+'.elements')
    var caracteres  = '0123456789,.-'
    var valor       = false 
    
    //Significa que está tela foi carregada com apenas 1 linha ...
    if(typeof(elementos['chkt_item_pedido[]'][0]) == 'undefined') {
        var linhas = 1
    }else {//Mais de 1 linha ...
        var linhas = elementos['chkt_item_pedido[]'].length
    }

    for (var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_item_pedido'+i).checked == true) valor = true
    }

    if(valor == false) {
        alert(mensagem)
        return false
    }else {
        for (var i = 0; i < linhas; i++) {
            if(document.getElementById('chkt_item_pedido'+i).checked == true) {
                //Controle com a Qtde ...
                if(document.getElementById('txt_qtde'+i).disabled == false) {
                    if(document.getElementById('txt_qtde'+i).value == '') {
                        alert('DIGITE A QUANTIDADE !')
                        document.getElementById('txt_qtde'+i).focus()
                        return false
                    }
                    if(document.getElementById('txt_qtde'+i).value <= 0) {
                        alert('QUANTIDADE INVÁLIDA !')
                        document.getElementById('txt_qtde'+i).focus()
                        document.getElementById('txt_qtde'+i).select()
                        return false
                    }
                    for(var y = 0; y < document.getElementById('txt_qtde'+i).value.length; y++) {
                        if(caracteres.indexOf(document.getElementById('txt_qtde'+i).value.charAt(y), 0) == -1) {
                            alert('QUANTIDADE INVÁLIDA !')
                            document.getElementById('txt_qtde'+i).focus()
                            document.getElementById('txt_qtde'+i).select()
                            return false
                        }
                    }
                }
//Controle com o Preço ...
                if(document.getElementById('txt_preco'+i).disabled == false) {
                    if(document.getElementById('txt_preco'+i).value == '') {
                        alert('DIGITE O PREÇO !')
                        document.getElementById('txt_preco'+i).focus()
                        return false
                    }
                    for(var y = 0; y < document.getElementById('txt_preco'+i).value.length; y++) {
                        if(caracteres.indexOf(document.getElementById('txt_preco'+i).value.charAt(y), 0) == -1) {
                            alert('PREÇO INVÁLIDO !')
                            document.getElementById('txt_preco'+i).focus()
                            document.getElementById('txt_preco'+i).select()
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
    var elementos   = eval('document.'+form+'.elements')
    var campo_sel   = eval('document.'+form+'.'+campo+'')

    //Significa que está tela foi carregada com apenas 1 linha ...
    if(typeof(elementos['chkt_item_pedido[]'][0]) == 'undefined') {
        var linhas = 1
    }else {//Mais de 1 linha ...
        var linhas = elementos['chkt_item_pedido[]'].length
    }
    
    if(campo_sel.checked == true) {//Checkbox Principal está selecionado ...
        for (var i = 0; i < linhas; i++) {
            document.getElementById('chkt_item_pedido'+i).checked       = true
            //Habilita as Caixas ...
            document.getElementById('txt_qtde'+i).disabled              = false
            document.getElementById('txt_preco'+i).disabled             = false
            document.getElementById('txt_marca_obs'+i).disabled         = false
            document.getElementById('txt_num_corrida'+i).disabled       = false
            document.getElementById('hdd_pedido'+i).disabled            = false
            document.getElementById('hdd_produto_insumo'+i).disabled    = false
            //Aqui joga o Designer de Habilitado ...
            document.getElementById('txt_qtde'+i).className             = 'caixadetexto'
            document.getElementById('txt_preco'+i).className            = 'caixadetexto'
            document.getElementById('txt_marca_obs'+i).className        = 'caixadetexto'
            document.getElementById('txt_num_corrida'+i).className      = 'caixadetexto'
        }
//Cor das Linhas ...
        if(navigator.appName == 'Netscape') {
            for (var i = 3; i < (totallinhas.rows.length - 4); i++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            for (var i = 3; i < (totallinhas.rows.length - 4); i++) totallinhas.rows[i].style.backgroundColor = '#c6e2ff'
        }
    }else {//Checkbox Principal está desmarcado ...
        for (var i = 0; i < linhas; i++) {
            document.getElementById('chkt_item_pedido'+i).checked       = false
            //Desabilita as Caixas ...
            document.getElementById('txt_qtde'+i).disabled              = true
            document.getElementById('txt_preco'+i).disabled             = true
            document.getElementById('txt_marca_obs'+i).disabled         = true
            document.getElementById('txt_num_corrida'+i).disabled       = true
            document.getElementById('hdd_pedido'+i).disabled            = true
            document.getElementById('hdd_produto_insumo'+i).disabled    = true
            //Aqui joga o Designer de Desabilitado ...
            document.getElementById('txt_qtde'+i).className             = 'textdisabled'
            document.getElementById('txt_preco'+i).className            = 'textdisabled'
            document.getElementById('txt_marca_obs'+i).className        = 'textdisabled'
            document.getElementById('txt_num_corrida'+i).className      = 'textdisabled'
        }
        //Cor das Linhas ...
        for(var i = 3; i < (totallinhas.rows.length - 4); i ++) totallinhas.rows[i].style.backgroundColor = cor_origem;
    }
    calcular_totais()
}

/*Esse quarto parâmetro achei interessante comentar, é o preço da linha
corrente aonde o usuário clicou, sempre vai trazer o preço do banco de dados
da hora em que carregou a tela, independente de o usuário ter alterado
o seu valor, e a sua função principal é jogar o seu valor
no hidden de valor_corrente, para depois poder fazer a comparação
se o preço não passou dos 10%*/

function checkbox_habilita(form, campo, indice, preco_linha, cor_origem) {
    var elementos = eval('document.'+form+'.elements')
    var campo_sel = eval('document.'+form+'.'+campo+'')
    var i, resultado = true
    linha = eval(indice) + 3

//Habilita as Caixas
    document.getElementById('txt_qtde'+indice).disabled             = false
    document.getElementById('txt_preco'+indice).disabled            = false
    document.getElementById('txt_marca_obs'+indice).disabled        = false
    document.getElementById('txt_num_corrida'+indice).disabled      = false
    document.getElementById('hdd_pedido'+indice).disabled           = false
    document.getElementById('hdd_produto_insumo'+indice).disabled   = false
//Aqui joga o Designer de Habilitado
    document.getElementById('txt_qtde'+indice).className            = 'caixadetexto'
    document.getElementById('txt_preco'+indice).className           = 'caixadetexto'
    document.getElementById('txt_marca_obs'+indice).className       = 'caixadetexto'
    document.getElementById('txt_num_corrida'+indice).className     = 'caixadetexto'

    cor_origem  = cor_origem.toLowerCase()

    if(document.getElementById('chkt_item_pedido'+indice).checked == true) {//Desabilita a Linha
        campo_sel.checked                                               = false
        //Desabita as caixas ...
        document.getElementById('txt_qtde'+indice).disabled             = true
        document.getElementById('txt_preco'+indice).disabled            = true
        document.getElementById('txt_marca_obs'+indice).disabled        = true
        document.getElementById('txt_num_corrida'+indice).disabled      = true
        document.getElementById('hdd_pedido'+indice).disabled           = true
        document.getElementById('hdd_produto_insumo'+indice).disabled   = true
//Aqui joga o Designer de Desabilitado
        document.getElementById('txt_qtde'+indice).className            = 'textdisabled'
        document.getElementById('txt_preco'+indice).className           = 'textdisabled'
        document.getElementById('txt_marca_obs'+indice).className       = 'textdisabled'
        document.getElementById('txt_num_corrida'+indice).className     = 'textdisabled'

        document.getElementById('chkt_item_pedido'+indice).checked      = false
        document.form.valor_corrente.value = ''
    }else {//Habilita a Linha
        document.getElementById('chkt_item_pedido'+indice).checked      = true
    }
    
    //Significa que está tela foi carregada com apenas 1 linha ...
    if(typeof(elementos['chkt_item_pedido[]'][0]) == 'undefined') {
        var linhas = 1
    }else {//Mais de 1 linha ...
        var linhas = elementos['chkt_item_pedido[]'].length
    }

    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_item_pedido'+i).checked == false) resultado = false
    }
    campo_sel.checked = resultado

    if(document.getElementById('chkt_item_pedido'+indice).checked == true) {
        if(navigator.appName == 'Netscape') {
            totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            totallinhas.rows[linha].style.backgroundColor = '#c6e2ff'
        }
    }else {
        totallinhas.rows[linha].style.backgroundColor = cor_origem
    }
    calcular_totais()
}

function focos(objeto) {
    objeto.disabled = false
    objeto.focus()
    return false
}

function calcular(indice, preco_linha) {
    var preco_linha = eval(strtofloat(preco_linha))
    var letra = '', cont = 0, cont2 = 0, soma = 0
    var caracteres = '0123456789,.-'
    linha = eval(indice) + 3
    
    for (var y = 0; y < document.getElementById('txt_qtde'+indice).value.length; y ++) {
        if (caracteres.indexOf(document.getElementById('txt_qtde'+indice).value.charAt(y), 0) == -1) {
            soma ++
        }
    }
    document.getElementById('txt_qtde'+indice).value = document.getElementById('txt_qtde'+indice).value.substr(0, (document.getElementById('txt_qtde'+indice).value.length - soma))
    for(var y = 0; y < document.getElementById('txt_qtde'+indice).value.length; y++) {
        if(document.getElementById('txt_qtde'+indice).value.charAt(y) == ',') cont ++
    }

    if(cont > 1) {
        for(var y = 0; y < document.getElementById('txt_qtde'+indice).value.length; y++) {
            if(document.getElementById('txt_qtde'+indice).value.charAt(y) == ',') cont2 ++
        }
        cont2 = cont2 - 1
        document.getElementById('txt_qtde'+indice).value = document.getElementById('txt_qtde'+indice).value.substr(0, (document.getElementById('txt_qtde'+indice).value.length - cont2))
    }

    cont = 0
    cont2 = 0

    for (var y = 0; y < document.getElementById('txt_preco'+indice).value.length; y++) {
        if(caracteres.indexOf(document.getElementById('txt_preco'+indice).value.charAt(y), 0) == -1) {
            document.getElementById('txt_preco'+indice).value = document.getElementById('txt_preco'+indice).value.replace(document.getElementById('txt_preco'+indice).value.charAt(y), '')
            return false
        }
    }

    for(var y = 0; y < document.getElementById('txt_preco'+indice).value.length; y++) {
        if(document.getElementById('txt_preco'+indice).value.charAt(y) == ',') cont ++
    }

    if(cont > 1) {
        for(var y = 0; y < document.getElementById('txt_preco'+indice).value.length; y++) {
            if(document.getElementById('txt_preco'+indice).value.charAt(y) == ',') {
                document.getElementById('txt_preco'+indice).value = document.getElementById('txt_preco'+indice).value.replace(document.getElementById('txt_preco'+indice).value.charAt(y), '')
            }
        }

        for(var y = (document.getElementById('txt_preco'+indice).value.length - 1); y >= 0; y --) {
            letra = document.getElementById('txt_preco'+indice).value.charAt(y) + letra
            cont2 ++
            if(cont2 == 2) letra = ',' + letra
        }
        document.getElementById('txt_preco'+indice).value = letra
    }

    if(document.getElementById('txt_qtde'+indice).value != '') {//O campo "Qtde" está preenchido ...
        if(document.getElementById('txt_preco'+indice).value != '') {//O campo "Preço" está preenchido ...
            preco_reajustado = (preco_linha) * 1.1
            if(strtofloat(document.getElementById('txt_preco'+indice).value) > preco_reajustado) {
                alert('PREÇO UNITÁRIO INVÁLIDO !\n EXCEDIDO OS 10% PERMITIDO !')
                document.getElementById('txt_preco'+indice).value = preco_linha
                document.getElementById('txt_preco'+indice).value = arred(document.getElementById('txt_preco'+indice).value, 2, 1)
                document.getElementById('txt_preco'+indice).focus()
            }
            document.getElementById('txt_valor_total'+indice).value = strtofloat(document.getElementById('txt_qtde'+indice).value) * strtofloat(document.getElementById('txt_preco'+indice).value)
            document.getElementById('txt_valor_total'+indice).value = 'R$ ' + arred(document.getElementById('txt_valor_total'+indice).value, 2, 1)
        }else {//O campo "Preço" não está preenchido ...
            document.getElementById('txt_valor_total'+indice).value = ''
        }
    }else {
        document.getElementById('txt_valor_total'+indice).value = ''
    }
    calcular_totais()
}

function calcular_totais() {
    var elementos   = document.form.elements
    var total_nf    = 0
    var qtde_total  = 0
//Significa que está tela foi carregada com apenas 1 linha ...
    if(typeof(elementos['txt_qtde[]'][0]) == 'undefined') {
        var linhas = 1
//Mais de 1 linha ...
    }else {
        var linhas = elementos['chkt_item_pedido[]'].length
    }
    for (var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_item_pedido'+i).checked == true) {
            qtde_total+=    eval(strtofloat(document.getElementById('txt_qtde'+i).value))
            total_nf+=      eval(strtofloat(document.getElementById('txt_qtde'+i).value) * strtofloat(document.getElementById('txt_preco'+i).value))
        }
    }
    document.form.txt_qtde_total.value  = qtde_total
    document.form.txt_qtde_total.value  = arred(document.form.txt_qtde_total.value, 2, 1)
    document.form.txt_total_nf.value    = total_nf
    document.form.txt_total_nf.value    = arred(document.form.txt_total_nf.value, 2, 1)
}