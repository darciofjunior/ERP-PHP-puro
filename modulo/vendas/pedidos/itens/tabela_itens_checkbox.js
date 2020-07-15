/*Esse JS foi feito exclusivo para esse caso, devido eu ter uma caixa de texto para cada linha, sendo assim
fica um pouco diferente o controle de clicks e cores da linha*/
function cor_clique_celula(cel_atual, backgroundColor) {
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

/*****************************Funções de Inclusão*****************************/
function selecionar_especial(form, campo, totallinhas, cor_origem) {
    var tamanho_vetor = 0
/*Esse objeto vetor_esp é um hidden que eu tenho no fim do formulário, que ele armazena todas os índices
das linhas que contém os produtos do Tipo Especial*/
    var qtde_vetor_esp = document.form.vetor_esp.value
//Disparo esse loop para poder definir o tamanho do vetor
    for(i = 0; i < qtde_vetor_esp.length; i++) {
        if(qtde_vetor_esp.charAt(i) == ',') tamanho_vetor++
    }
//Aqui eu incremento mais um no vetor pq o último digíto da String qtde_vetor_esp, não termina com , ...
    tamanho_vetor+= 1
//Aqui grava no vetor todos os índices das linhas q contém produtos do Tipo Especial
    var cont = 0
    var caracter_corrente = ''
    var vetor_esp = new Array(tamanho_vetor)
    for(i = 0; i < qtde_vetor_esp.length; i++) {
        if(qtde_vetor_esp.charAt(i) != ',') {
                caracter_corrente+= qtde_vetor_esp.charAt(i)
/*Aki é um controlezinho p/ armazenar o último dígito do vetor, lembrando que nesse último 
eu não termino com vírgula*/
            if((i + 1) == qtde_vetor_esp.length) vetor_esp[cont] = caracter_corrente
        }else {
            vetor_esp[cont] = caracter_corrente
            caracter_corrente = ''
            cont++
        }
    }
//Aqui zera o contador para poder comparar com o valor do vetor
    var cont            = 0
    var campo_sel       = eval('document.'+form+'.'+campo+'')
    var elements        = eval('document.'+form+'.elements')
    cor_origem          = cor_origem.toLowerCase()
    var string_vetor_esp = vetor_esp.toString()
    var objetos_inicio 	= 1//Qtde de Objetos antes de Loop ...
    var objetos_linha 	= 7//Qtde de Objetos por linha ...
    var objetos_fim 	= 6//Qtde de Objetos depois de Loop ...

    for (var i = objetos_inicio; i < (elements.length) - objetos_fim; i +=objetos_linha) {
        if (elements[i].type == 'checkbox') {
//Significa que esse produto contém o Lote do Custo habilitado na Etapa 5, sendo assim não pode desabilitar essa linha
            if (string_vetor_esp.indexOf(cont, 0) != -1) {
                if (campo_sel.checked) {//Aqui são os checkbox
                    elements[i].checked         = true//Aqui são os checkbox
                    elements[i + 1].disabled    = true//Aqui são os texts
//Aqui joga o Designer de Desabilitado
                    elements[i + 1].className   = 'textdisabled'
                }else {
                    elements[i].checked         = false//Aqui são os checkbox
                    elements[i + 1].disabled    = true//Aqui são os texts
//Aqui joga o Designer de Desabilitado
                    elements[i + 1].className   = 'textdisabled'
                }
//Produtos do Tipo Normal
            }else {
                if (campo_sel.checked) {//Aqui são os checkbox
                    elements[i].checked         = true
                    elements[i + 1].disabled    = false//Aqui são os texts
//Aqui joga o Designer de Habilitado
                    elements[i + 1].className = 'caixadetexto'
                }else {
                    elements[i].checked         = false//Aqui são os checkbox
                    elements[i + 1].disabled    = true//Aqui são os texts
//Aqui joga o Designer de Desabilitado
                    elements[i + 1].className   = 'textdisabled'
                }
            }
            cont ++
        }
    }

    if (campo_sel.checked) {
        if(navigator.appName == 'Netscape') {
            for (i = 3; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            for (i = 3; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)';
        }
    }else {
        for (i = 3; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = cor_origem;
    }
}

function checkbox(form, indice, cor_origem, tipo) {
    var elementos   = eval('document.'+form+'.elements')
    cor_origem      = cor_origem.toLowerCase()
    linha           = eval(indice) + 3

    if(!(document.getElementById('chkt_orcamento_venda_item'+indice).checked)) {
        document.getElementById('chkt_orcamento_venda_item'+indice).checked = true       
//Só habilita a caixa para digitar a qtde em produtos normais
        if(tipo != 'ESP') {
            //Aqui joga o Designer de Habilitado ...
            document.getElementById('txt_quantidade'+indice).className  = 'caixadetexto'
            document.getElementById('txt_quantidade'+indice).disabled   = false
            document.getElementById('txt_quantidade'+indice).focus()
        }
    }else {
        document.getElementById('chkt_orcamento_venda_item'+indice).checked = false
        //Aqui joga o Designer de Desabilitado ...
        document.getElementById('txt_quantidade'+indice).className      = 'textdisabled'
        document.getElementById('txt_quantidade'+indice).disabled       = true
    }
    if(document.getElementById('chkt_orcamento_venda_item'+indice).checked) {
        if(navigator.appName == 'Netscape') {
            totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            totallinhas.rows[linha].style.backgroundColor = '#c6e2ff'
        }
    }else {
        totallinhas.rows[linha].style.backgroundColor = cor_origem
    }

//Aqui é um controle para poder selecionar o checkbox principal
    var total_itens_orcamentos = 0
    var total_itens_orcamentos_marcados = 0

    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'chkt_orcamento_venda_item[]') {
            //Somente para pedidos q estão checados
            if(elementos[i].checked == true) total_itens_orcamentos_marcados++
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
/*****************************Funções de Exclusão*****************************/
function selecionar_excluir(form, cor_origem) {
    var elementos   = eval('document.'+form+'.elements')
    cor_origem = cor_origem.toLowerCase()
    
    for(var i = 0; i < elementos.length; i++) {
        if(!(document.getElementById('chkt_pedido_venda_item'+i).checked) && document.getElementById('chkt_pedido_venda_item'+i).disabled == false) {
            document.getElementById('chkt_pedido_venda_item'+i).checked    = true
            //Cor da Linha ...
            if(navigator.appName == 'Netscape') {
                totallinhas.rows[i + 3].style.backgroundColor = 'rgb(198,226,255)'
            }else {
                totallinhas.rows[i + 3].style.backgroundColor = '#c6e2ff'
            }
        }else {
            document.getElementById('chkt_pedido_venda_item'+i).checked    = false
            //Cor da Linha ...
            totallinhas.rows[i + 3].style.backgroundColor = cor_origem
        }
    }
}

function checkbox_excluir(form, indice, cor_origem) {
    var elementos   = eval('document.'+form+'.elements')
    cor_origem      = cor_origem.toLowerCase()
    linha           = eval(indice) + 3
    
    if(!(document.getElementById('chkt_pedido_venda_item'+indice).checked) && document.getElementById('chkt_pedido_venda_item'+indice).disabled == false) {
        document.getElementById('chkt_pedido_venda_item'+indice).checked    = true
    }else {
        document.getElementById('chkt_pedido_venda_item'+indice).checked    = false
    }
    
    if(document.getElementById('chkt_pedido_venda_item'+indice).checked) {
        if(navigator.appName == 'Netscape') {
            totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            totallinhas.rows[linha].style.backgroundColor = '#c6e2ff'
        }
    }else {
        totallinhas.rows[linha].style.backgroundColor = cor_origem
    }

//Aqui é um controle para poder selecionar o checkbox principal
    var total_itens_pedidos = 0
    var total_itens_pedidos_marcados = 0

    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'chkt_pedido_venda_item[]' && elementos[i].disabled == false) {
            //Somente para pedidos q estão checados
            if(elementos[i].checked == true) total_itens_pedidos_marcados++
            total_itens_pedidos++
        }
    }
//Significa que todos os itens de Orçamento estão marcados
    if(total_itens_pedidos == total_itens_pedidos_marcados) {
        document.form.chkt_tudo.checked = true
    }else {
        document.form.chkt_tudo.checked = false
    }
}
/*****************************************************************************/