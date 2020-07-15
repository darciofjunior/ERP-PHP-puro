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

function checkbox(form, campo, indice, cor_origem, id_produto_acabado, peso_unitario) {
    var campo_sel = eval('document.'+form+'.'+campo+'')
    var elementos = eval('document.'+form+'.elements')
    var resultado = true
    var linha = eval(indice) + 3//Mais 3 porque pulo as 3 primeiras linhas da Tabela ...
	
/*Agora, caso o Peso Unitário do PA esteje zerado, então eu preciso atualizar com o Peso 
correto antes de dar Entrada p/ não dar Erro na Nota Fiscal ...*/
    if(peso_unitario == '0,0000' && id_produto_acabado > 0) {
        alert('NÃO EXISTE PESO UNITÁRIO P/ ESTE PRODUTO !!!\nCOLOQUE UM PESO UNITÁRIO P/ O MESMO ! ')
        nova_janela('../../../../classes/produtos_acabados/alterar_peso_unitario.php?id_produto_acabado='+id_produto_acabado+'&tela1=window.opener', 'PESO_UNITARIO', '', '', '', '', 300, 800, 'c', 'c', '', '', 's', 's', '', '', '')
        return false
    }
    cor_origem = cor_origem.toLowerCase()
    if(document.getElementById('chkt_nfe_historico'+indice).checked) {
        document.getElementById('chkt_nfe_historico'+indice).checked   = false
        //Desabilita as Caixas ...
        document.getElementById('txt_qtde_prac_conf'+indice).disabled  = true
        //Aqui joga o Designer de Desabilitado ...
        document.getElementById('txt_qtde_prac_conf'+indice).className = 'textdisabled'    
        
        campo_sel.checked = false
    }else {
        document.getElementById('chkt_nfe_historico'+indice).checked   = true
        //Habilita as Caixas ...
        document.getElementById('txt_qtde_prac_conf'+indice).disabled  = false
        //Aqui joga o Designer de Habilitado ...
        document.getElementById('txt_qtde_prac_conf'+indice).className = 'caixadetexto'
        
        for (i = 0; i < elementos.length; i++) {
            if (elementos[i].type == 'checkbox' && elementos[i].name != campo) {
                if (elementos[i].checked == false) resultado = false
            }
        }
        campo_sel.checked = resultado
    }
    if(document.getElementById('chkt_nfe_historico'+indice).checked) {
        if(navigator.appName == 'Netscape') {
            totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            totallinhas.rows[linha].style.backgroundColor = '#c6e2ff'
        }
    }else {
        totallinhas.rows[linha].style.backgroundColor = cor_origem
    }
}

function selecionar(form, campo, totallinhas, cor_origem) {
    var campo_sel   = eval('document.'+form+'.'+campo+'')
    var elementos   = eval('document.'+form+'.elements')
    cor_origem      = cor_origem.toLowerCase()
    
    if(typeof(elementos['chkt_nfe_historico[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_nfe_historico[]'].length)
    }
    
    if (campo_sel.checked) {//Se o checkbox principal está selecionado ...
        var checkbox_linha  = true//Habilita o checkbox da Linha
        var checado         = false//Habilita as Caixas
        var layout          = 'caixadetexto'//Aqui joga o Designer de Habilitado
    }else {
        var checkbox_linha  = false//Desabilita o checkbox da Linha
        var checado         = true//Desabilita as Caixas
        var layout          = 'textdisabled'//Aqui joga o Designer de Desabilitado
    }
    
    for (var i = 0; i < linhas; i++) {
        document.getElementById('chkt_nfe_historico'+i).checked     = checkbox_linha
        document.getElementById('txt_qtde_prac_conf'+i).disabled    = checado
        document.getElementById('txt_qtde_prac_conf'+i).className   = layout
    }

    if (campo_sel.checked) {
        if(navigator.appName == 'Netscape') {
            for (i = 3; i < (totallinhas.rows.length - 1); i++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            for (i = 3; i < (totallinhas.rows.length - 1); i++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }
    }else {
        for (i = 3; i < (totallinhas.rows.length - 1); i++) totallinhas.rows[i].style.backgroundColor = cor_origem;
    }
}