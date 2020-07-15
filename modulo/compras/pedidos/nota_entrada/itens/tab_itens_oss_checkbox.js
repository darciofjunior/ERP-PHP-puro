function selecionar(totallinhas, cor_origem) {
    var elementos   = document.form.elements
    var linha       = 3//Começo com 3 p/ ignorar as 3 primeiras linhas da tabela da tela -> mensagem, linhacabecalho e linhadestaque ...
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['chkt_os_item[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_os_item[]'].length)
    }
    //Verifico se o Checkbox Principal está marcado ...
    if(document.form.chkt_tudo.checked) {//Marcado ...
        var checked     = true
        var disabled    = false
        if(navigator.appName == 'Netscape') {
            var cor = 'rgb(198,226,255)'
        }else {
            var cor = '#c6e2ff'
        }
    }else {//Desmarcado ...
        var checked     = false
        var disabled    = true
        var cor         = cor_origem
    }
    for(i = 0; i < linhas; i++) {
        document.getElementById('chkt_os_item'+i).checked                   = checked
        document.getElementById('hdd_peso_qtde_total_utilizar'+i).disabled  = disabled
        document.getElementById('hdd_preco_pi'+i).disabled                  = disabled
        totallinhas.rows[linha].style.backgroundColor                       = cor
        linha++
    }
}

function checkbox_habilita(indice, cor_origem) {
    var cor_origem  = cor_origem.toLowerCase()
    var linha       = eval(indice) + 3//Começo com 3 p/ ignorar as 3 primeiras linhas da tabela da tela -> mensagem, linhacabecalho e linhadestaque ...
    if (document.getElementById('chkt_os_item'+indice).checked == false) {//Aqui irá marcar a linha ...
        document.getElementById('chkt_os_item'+indice).checked                  = true
        //Habilita as Caixas
        document.getElementById('hdd_peso_qtde_total_utilizar'+indice).disabled = false
        document.getElementById('hdd_preco_pi'+indice).disabled                 = false
        //Muda a cor da linha p/ habilitada ...
        if(navigator.appName == 'Netscape') {
            totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            totallinhas.rows[linha].style.backgroundColor = '#c6e2ff';
        }
    }else {
        document.getElementById('chkt_os_item'+indice).checked                  = false
        //Desabilita as Caixas
        document.getElementById('hdd_peso_qtde_total_utilizar'+indice).disabled = true
        document.getElementById('hdd_preco_pi'+indice).disabled                 = true
        //Muda a cor da linha p/ desabilitada ...
        totallinhas.rows[linha].style.backgroundColor = cor_origem
    }
    /*************Controle para marcar o Checkbox Principal*************/
    var elementos   = document.form.elements
    var checkados   = 0
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['chkt_os_item[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_os_item[]'].length)
    }
    for(i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_os_item'+i).checked) checkados++
    }
    document.form.chkt_tudo.checked = (checkados == linhas) ? true : false
    /*******************************************************************/
}