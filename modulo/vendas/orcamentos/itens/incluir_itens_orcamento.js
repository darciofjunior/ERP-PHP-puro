/*Esse JS foi feito exclusivo para esse caso, devido eu ter uma caixa de texto para cada linha, sendo assim
fica um pouco diferente o controle de clicks e cores da linha*/
function sobre_celula(cel_atual, backgroundColor) {
    var cor_atual = cel_atual.style.backgroundColor
    var nova_cor = backgroundColor
    if((cor_atual == 'rgb(198,226,255)')||(cor_atual == '#c6e2ff')) {
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

function checkbox_incluir(indice, cor_origem) {
    cor_origem = cor_origem.toLowerCase()
    linha = eval(indice) + 1
/**********************************************/
//Se não estiver checado, então fica marcado ...
    if(document.getElementById('chkt_produto_acabado'+indice).checked == false) {
        document.getElementById('chkt_produto_acabado'+indice).checked = true
    }else {//Se marcado, então fica desmarcado ...
        document.getElementById('chkt_produto_acabado'+indice).checked = false
    }
/**********************************************/
    if (document.getElementById('chkt_produto_acabado'+indice).checked == true) {
        document.getElementById('txt_quantidade'+indice).disabled = false
//Aqui joga o Designer de Habilitado
        document.getElementById('txt_quantidade'+indice).className = 'caixadetexto'
        document.getElementById('txt_quantidade'+indice).focus()
        document.getElementById('txt_quantidade'+indice).select()
    }else {
        document.getElementById('txt_quantidade'+indice).disabled = true
//Aqui joga o Designer de Desabilitado
        document.getElementById('txt_quantidade'+indice).className = 'textdisabled'
    }
//Colore de azul a linha corrente ...
    if (document.getElementById('chkt_produto_acabado'+indice).checked) {
        if(navigator.appName == 'Netscape') {
            totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            totallinhas.rows[linha].style.backgroundColor = '#c6e2ff'
        }
    }else {
        totallinhas.rows[linha].style.backgroundColor = cor_origem
    }
}

function selecionar_tudo_incluir(totallinhas, cor_origem) {
    var tamanho_vetor = 0
/*Esse objeto vetor_lote_minimo_fornecedor é um hidden que eu tenho no fim do formulário, que ele armazena 
todos os índices das linhas que contém o Lote do Custo habilitados na Etapa 5*/
    var qtde_vetor_lote_minimo_fornecedor = document.form.vetor_lote_minimo_fornecedor.value
//Disparo esse loop para poder definir o tamanho do vetor
    for(i = 0; i < qtde_vetor_lote_minimo_fornecedor.length; i++) {
        if(qtde_vetor_lote_minimo_fornecedor.charAt(i) != ',') tamanho_vetor++
    }
//Aqui grava no vetor todos os índices das linhas que contém o Lote do Custo habilitados na Etapa 5
    var cont = 0
    var vetor_lote_minimo_fornecedor = new Array(tamanho_vetor)
    for(i = 0; i < qtde_vetor_lote_minimo_fornecedor.length; i++) {
        if(qtde_vetor_lote_minimo_fornecedor.charAt(i) != ',') {
            vetor_lote_minimo_fornecedor[cont] = qtde_vetor_lote_minimo_fornecedor.charAt(i)
            cont++
        }
    }
//Aqui zera o contador para poder comparar com o valor do vetor
    var cont = 0
    var elements = document.form.elements
    var linhas = (typeof(elements['chkt_produto_acabado[]'][0]) == 'undefined') ? 1 : elements['chkt_produto_acabado[]'].length
    cor_origem = cor_origem.toLowerCase()
    var string_vetor_lote_minimo_fornecedor = vetor_lote_minimo_fornecedor.toString()

    if(document.getElementById('chkt_tudo').checked == true) {//Checkbox Principal está marcado ...
/*Significa que esse produto contém o Lote do Custo habilitado na Etapa 5, sendo assim não pode 
desabilitar essa linha*/
        if(string_vetor_lote_minimo_fornecedor.indexOf(cont, 0) == 0) {//Produtos ESP ...
            for(var i = 0; i < linhas; i++) {
                document.getElementById('chkt_produto_acabado'+i).checked   = true
                document.getElementById('txt_quantidade'+i).disabled        = true
//Aqui joga o Designer de Desabilitado
                document.getElementById('txt_quantidade'+i).className       = 'textdisabled'
                cont++
            }
        }else {//Produtos do Tipo Normal
            for(var i = 0; i < linhas; i++) {
                document.getElementById('chkt_produto_acabado'+i).checked   = true
                document.getElementById('txt_quantidade'+i).disabled        = false
//Aqui joga o Designer de Habilitado
                document.getElementById('txt_quantidade'+i).className       = 'caixadetexto'
            }
        }
    }else {//Checkbox Principal está desmarcado ...
        for(var i = 0; i < linhas; i++) {
            document.getElementById('chkt_produto_acabado'+i).checked   = false
            document.getElementById('txt_quantidade'+i).disabled        = true
//Aqui joga o Designer de Desabilitado
            document.getElementById('txt_quantidade'+i).className       = 'textdisabled'
        }
    }

    if(document.getElementById('chkt_tudo').checked == true) {//Checkbox Principal está marcado ...
        if(navigator.appName == 'Netscape') {
            for (i = 1; i < (totallinhas.rows.length - 2); i++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            for (i = 1; i < (totallinhas.rows.length - 2); i++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }
    }else {
        for(i = 1; i < (totallinhas.rows.length - 2); i++) totallinhas.rows[i].style.backgroundColor = cor_origem
    }
}

function checkbox_gerar_pedido(indice, cor_origem) {
    cor_origem = cor_origem.toLowerCase()
    linha = eval(indice) + 3
/**********************************************/
//Se não estiver checado e o checkbox estiver habilitado, então fica marcado ...
    if(document.getElementById('chkt_orcamento_venda_item'+indice).checked == false && document.getElementById('chkt_orcamento_venda_item'+indice).disabled == false) {
        document.getElementById('chkt_orcamento_venda_item'+indice).checked = true
    }else {//Se marcado, então fica desmarcado ...
        document.getElementById('chkt_orcamento_venda_item'+indice).checked = false
    }
/**********************************************/
    if (document.getElementById('chkt_orcamento_venda_item'+indice).checked == true) {
        document.getElementById('txt_quantidade'+indice).disabled = false
//Aqui joga o Designer de Habilitado
        document.getElementById('txt_quantidade'+indice).className = 'caixadetexto'
        document.getElementById('txt_quantidade'+indice).focus()
        document.getElementById('txt_quantidade'+indice).select()
    }else {
        document.getElementById('txt_quantidade'+indice).disabled = true
//Aqui joga o Designer de Desabilitado
        document.getElementById('txt_quantidade'+indice).className = 'textdisabled'
    }
//Colore de azul a linha corrente ...
    if (document.getElementById('chkt_orcamento_venda_item'+indice).checked) {
        if(navigator.appName == 'Netscape') {
            totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            totallinhas.rows[linha].style.backgroundColor = '#c6e2ff'
        }
    }else {
        totallinhas.rows[linha].style.backgroundColor = cor_origem
    }
//Aqui é um controle para poder selecionar o checkbox principal
    var total_itens_orcamentos          = 0
    var total_itens_orcamentos_marcados = 0
    var elementos                       = document.form.elements

    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'chkt_orcamento_venda_item[]') {
            if(elementos[i].checked == true) {//Somente para orçamentos q estão checados
                total_itens_orcamentos_marcados++
            }
            total_itens_orcamentos++
        }
    }
//Significa que todos os itens de Orçamento estão marcados
    if(total_itens_orcamentos == total_itens_orcamentos_marcados) {
        document.form.chkt_tudo.checked = true
    }else {
        document.form.chkt_tudo.checked = false
    }
}

function selecionar_tudo_gerar_pedido(totallinhas, cor_origem) {
    var elementos   = document.form.elements
    var linhas      = (typeof(elementos['chkt_orcamento_venda_item[]'][0]) == 'undefined') ? 1 : elementos['chkt_orcamento_venda_item[]'].length
    
    if(document.getElementById('chkt_tudo').checked == true) {//Checkbox Principal está marcado ...
/*Significa que esse produto contém o Lote do Custo habilitado na Etapa 5, sendo assim não pode 
desabilitar essa linha*/
        for(var i = 0; i < linhas; i++) {
            //Se não estiver checado e o checkbox estiver habilitado, então fica marcado ...
            if(document.getElementById('chkt_orcamento_venda_item'+i).disabled == false) {
                document.getElementById('chkt_orcamento_venda_item'+i).checked  = true
                document.getElementById('txt_quantidade'+i).disabled            = false
                //Aqui joga o Designer de Habilitado
                document.getElementById('txt_quantidade'+i).className           = 'caixadetexto'
            }
        }
    }else {//Checkbox Principal está desmarcado ...
        for(var i = 0; i < linhas; i++) {
            document.getElementById('chkt_orcamento_venda_item'+i).checked = false
            document.getElementById('txt_quantidade'+i).disabled            = true
//Aqui joga o Designer de Desabilitado
            document.getElementById('txt_quantidade'+i).className           = 'textdisabled'
        }
    }
    if(document.getElementById('chkt_tudo').checked == true) {//Checkbox Principal está marcado ...
        if(navigator.appName == 'Netscape') {
            for(i = 3; i < (totallinhas.rows.length - 1); i++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            for(i = 3; i < (totallinhas.rows.length - 1); i++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }
    }else {
        for(i = 3; i < (totallinhas.rows.length - 1); i++) totallinhas.rows[i].style.backgroundColor = cor_origem
    }
}

//Aqui recebe o índice da linha e o Valor Original do Estoque ...
function calcular_estoque_real(indice, estoque_disponivel) {
    var qtde = (document.getElementById('txt_quantidade'+indice).value != '') ? eval(document.getElementById('txt_quantidade'+indice).value) : 0
    document.getElementById('txt_estoque_disponivel'+indice).value = (estoque_disponivel > qtde) ? estoque_disponivel - qtde : 0
}