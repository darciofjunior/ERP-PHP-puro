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

function checkbox(form, indice, cor_origem, tipo, prazo_medio) {
    var elementos   = eval('document.'+form+'.elements')
    cor_origem      = cor_origem.toLowerCase()
    linha           = eval(indice) + 3
//Quando acontecer isso eu nunca posso selecionar a opção ...
    if(prazo_medio == 'IRREGULAR') {
//Essa lógica mudou um pouco e por enquanto eu não tenho certeza de excluir a antiga ainda ...
        //alert('ESSE PEDIDO NÃO PODE SER INCLUSO NESTA NOTA FISCAL !\n A DIFERENÇA ENTRE O PRAZO MÉDIO DO PEDIDO E O PRAZO MÉDIO DA NOTA FISCAL É SUPERIOR A 15 DIAS !')
        //elements[cont].checked = false

        var resposta = confirm('ESSE ITEM CONTÉM O PRAZO MÉDIO IRREGULAR E PODERÁ AFETAR NO FATURAMENTO DESSA NOTA !\nTEM CERTEZA DE QUE DESEJA CONTINUAR ?')
        if(resposta == true) {
//Eu coloco invertido aqui no código de propósito mesmo por causa da lógica um pouco + abaixo
            document.getElementById('chkt_pedido_venda_item'+indice).checked = false
        }else {
//Eu coloco invertido aqui no código de propósito mesmo por causa da lógica um pouco + abaixo
            document.getElementById('chkt_pedido_venda_item'+indice).checked = true
        }
    }
    
    //Vou habilitar a linha somente SE: o item estiver desmarcado e o checkbox estiver habilitado ...
    if (!(document.getElementById('chkt_pedido_venda_item'+indice).checked) && document.getElementById('chkt_pedido_venda_item'+indice).disabled == false) {
        document.getElementById('chkt_pedido_venda_item'+indice).checked = true
        //Só habilita a caixa para digitar a qtde em produtos normais ...
        if(tipo != 'ESP') {
            document.getElementById('txt_qtde'+indice).disabled         = false
            document.getElementById('txt_qtde'+indice).className        = 'caixadetexto'
        }
        //Essas outras caixas, sempre habilito ...
        document.getElementById('txt_qtde_nfe'+indice).disabled     = false
        document.getElementById('txt_preco_nfe'+indice).disabled    = false
        //Aqui joga o Designer de Habilitado ...
        document.getElementById('txt_qtde_nfe'+indice).className    = 'caixadetexto'
        document.getElementById('txt_preco_nfe'+indice).className   = 'caixadetexto'
    }else {
        document.getElementById('chkt_pedido_venda_item'+indice).checked = false
//Desabilita toda a linha ...
        document.getElementById('txt_qtde'+indice).disabled         = true
        document.getElementById('txt_qtde_nfe'+indice).disabled     = true
        document.getElementById('txt_preco_nfe'+indice).disabled    = true
//Aqui joga o Designer de Desabilitado ...
        document.getElementById('txt_qtde'+indice).className        = 'textdisabled'
        document.getElementById('txt_qtde_nfe'+indice).className    = 'textdisabled'
        document.getElementById('txt_preco_nfe'+indice).className   = 'textdisabled'
    }

    if(document.getElementById('chkt_pedido_venda_item'+indice).checked) {
        if(navigator.appName == 'Netscape') {
            totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)';
        }else {
            totallinhas.rows[linha].style.backgroundColor = '#c6e2ff';
        }
    }else {
        totallinhas.rows[linha].style.backgroundColor = cor_origem;
    }

//Aqui é um controle para poder selecionar o checkbox principal
    var total_itens_pedidos = 0
    var total_itens_pedidos_marcados = 0

    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'chkt_pedido_venda_item[]') {
            //Somente para pedidos q estão checados
            if(elementos[i].checked == true) total_itens_pedidos_marcados++
            total_itens_pedidos++
        }
    }
//Significa que todos os itens de Pedido estão marcados
    if(total_itens_pedidos == total_itens_pedidos_marcados) {
        document.form.chkt_tudo.checked = true
    }else {
        document.form.chkt_tudo.checked = false
    }
}

//linhas_selecionar -> Número de linhas permitido para seleção
function selecionar_especial(form, campo, totallinhas, cor_origem) {
    if(typeof(document.form.vetor_esp) == 'object') {
        var tamanho_vetor 	= 0
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
        var indice = 0
        var caracter_corrente = ''
        var vetor_esp = new Array(tamanho_vetor)
        for(i = 0; i < qtde_vetor_esp.length; i++) {
            if(qtde_vetor_esp.charAt(i) != ',') {
                caracter_corrente+= qtde_vetor_esp.charAt(i)
/*Aki é um controlezinho p/ armazenar o último dígito do vetor, lembrando que nesse último 
eu não termino com vírgula*/
                if((i + 1) == qtde_vetor_esp.length) {
                    vetor_esp[indice] = caracter_corrente
                }
            }else {
                vetor_esp[indice] = caracter_corrente
                caracter_corrente = ''
                indice++
            }
        }
        var achou_esp   = 0
        var campo_sel   = eval('document.'+form+'.'+campo+'')
        var elementos   = eval('document.'+form+'.elements')
        cor_origem      = cor_origem.toLowerCase()
        var linhas      = (typeof(elementos['chkt_pedido_venda_item[]'][0]) == 'undefined') ? 1 : (elementos['chkt_pedido_venda_item[]'].length)

        for (var i = 0; i < linhas; i++) {
            //Rastreio todos os índices ESPs armazenados no vetor com a posição atual do Item do Loop ...
            for(j = 0; j < vetor_esp.length; j++) {
                if(vetor_esp[j] == i) {
                    achou_esp = 1
                    break;
                }
            }
//Significa que esse produto contém o Lote do Custo habilitado na Etapa 5, sendo assim não pode desabilitar essa linha
            if(achou_esp == 1) {
                if(campo_sel.checked) {//Checkbox Principal está marcado ...
                    document.getElementById('chkt_pedido_venda_item'+i).checked = true
                    document.getElementById('txt_qtde'+i).disabled              = true
                    document.getElementById('txt_qtde_nfe'+i).disabled          = false
                    document.getElementById('txt_preco_nfe'+i).disabled         = false
//Aqui joga o Designer de Desabilitado
                    document.getElementById('txt_qtde'+i).className             = 'textdisabled'
                    document.getElementById('txt_qtde_nfe'+i).className         = 'caixadetexto'
                    document.getElementById('txt_preco_nfe'+i).className        = 'caixadetexto'
                }else {//Checkbox Principal está desmarcado ...
                    document.getElementById('chkt_pedido_venda_item'+i).checked = false
                    document.getElementById('txt_qtde'+i).disabled              = true
                    document.getElementById('txt_qtde_nfe'+i).disabled          = true
                    document.getElementById('txt_preco_nfe'+i).disabled         = true
//Aqui joga o Designer de Habilitado
                    document.getElementById('txt_qtde'+i).className             = 'textdisabled'
                    document.getElementById('txt_qtde_nfe'+i).className         = 'textdisabled'
                    document.getElementById('txt_preco_nfe'+i).className        = 'textdisabled'
                }
                achou_esp = 0//P/ não herdar valor dessa variável em Loops Futuros ...
//Produtos do Tipo Normal
            }else {
                if(campo_sel.checked) {//Checkbox Principal está marcado ...
                    //Vou habilitar a linha somente SE: o item estiver desmarcado e o checkbox estiver habilitado ...
                    if(document.getElementById('chkt_pedido_venda_item'+i).disabled == false) {
                        document.getElementById('chkt_pedido_venda_item'+i).checked = true
                        document.getElementById('txt_qtde'+i).disabled              = false
                        document.getElementById('txt_qtde_nfe'+i).disabled          = false
                        document.getElementById('txt_preco_nfe'+i).disabled         = false
//Aqui joga o Designer de Habilitado
                        document.getElementById('txt_qtde'+i).className             = 'caixadetexto'
                        document.getElementById('txt_qtde_nfe'+i).className         = 'caixadetexto'
                        document.getElementById('txt_preco_nfe'+i).className        = 'caixadetexto'
                    }
                }else {//Checkbox Principal está desmarcado ...
                    document.getElementById('chkt_pedido_venda_item'+i).checked = false
                    document.getElementById('txt_qtde'+i).disabled              = true
                    document.getElementById('txt_qtde_nfe'+i).disabled          = true
                    document.getElementById('txt_preco_nfe'+i).disabled         = true
//Aqui joga o Designer de Desabilitado
                    document.getElementById('txt_qtde'+i).className             = 'textdisabled'
                    document.getElementById('txt_qtde_nfe'+i).className         = 'textdisabled'
                    document.getElementById('txt_preco_nfe'+i).className        = 'textdisabled'
                }
            }
        }
        if(campo_sel.checked) {
            if(navigator.appName == 'Netscape') {
                for (i = 3; i < (totallinhas.rows.length - 3); i ++) {
                    totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)';
                }
            }else {
                for (i = 3; i < (totallinhas.rows.length - 3); i ++) {
                    totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)';
                }
            }
        }else {
            for (i = 3; i < (totallinhas.rows.length - 3); i ++) totallinhas.rows[i].style.backgroundColor = cor_origem;
        }
    }
}