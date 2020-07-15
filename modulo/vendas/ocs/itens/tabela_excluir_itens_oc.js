function cor_clique_celula(cel_atual, backgroundColor) {
    var cor_atual   = cel_atual.style.backgroundColor
    var nova_cor    = backgroundColor
    nova_cor        = nova_cor.toLowerCase()
//Habilita ou Desabilita o Checkbox e Objeto Radio da Linha quando se dá um clique aonde o cursor do mouse esta posicionado
    if((cor_atual == 'rgb(198,226,255)') || (cor_atual == nova_cor)) {
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
    if((cor_atual == 'rgb(198,226,255)') || (cor_atual == '#c6e2ff')) {
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

function selecionar(form, campo, totallinhas, cor_origem) {
    var campo_sel   = eval('document.'+form+'.'+campo+'')
    var elementos   = eval('document.'+form+'.elements')
    cor_origem      = cor_origem.toLowerCase()
    var checado     = (campo_sel.checked) ? true : false
    
    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            //Esse controle só será feito em cima dos checkbox que estejam Habilitados ...
            if(elementos[i].disabled == false) elementos[i].checked = checado
        }
    }

    if(campo_sel.checked) {
        if(navigator.appName == 'Netscape') {
            for (i = 3; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            for (i = 3; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = '#c6e2ff'
        }
    }else {
        for (i = 3; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = cor_origem
    }
}

function checkbox(indice, cor_origem) {
    var linha   = eval(indice) + 3
    cor_origem  = cor_origem.toLowerCase()
    
    //Somente se o Checkbox da Linha estiver habilitado que farei o Controle ...
    if(document.getElementById('chkt_oc_item'+indice).disabled == false) {
        if(document.getElementById('chkt_oc_item'+indice).checked == true) {//Está marcado, irá Desmarcar ...
            document.getElementById('chkt_oc_item'+indice).checked  = false
            totallinhas.rows[linha].style.backgroundColor           = cor_origem
        }else {//Está desmarcado, irá Marcar ...
            document.getElementById('chkt_oc_item'+indice).checked  = true
            if(navigator.appName == 'Netscape') {
                totallinhas.rows[linha].style.backgroundColor       = 'rgb(198,226,255)'
            }else {
                totallinhas.rows[linha].style.backgroundColor       = '#c6e2ff'
            }
        }
    }
//Aqui é um controle para poder selecionar o checkbox principal
    var total_itens_habilitados             = 0
    var total_itens_habilitados_marcados    = 0
    var elementos                           = document.form.elements

    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'chkt_oc_item[]' && elementos[i].disabled == false) {
            if(elementos[i].checked == true) {//Somente para orçamentos q estão checados
                total_itens_habilitados_marcados++
            }
            total_itens_habilitados++
        }
    }
/*Significa que todos os itens de OC habilitados estão marcados, o maior que Zero é porque tenho 
que ter pelo menos 1 Item de OC habilitado ...*/
    if(total_itens_habilitados == total_itens_habilitados_marcados && total_itens_habilitados > 0) {
        document.form.chkt_tudo.checked = true
    }else {
        document.form.chkt_tudo.checked = false
    }
}