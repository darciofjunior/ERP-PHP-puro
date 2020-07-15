/********************************************************************************/
//Start Ajax ...
/********************************************************************************/
function start_ajax() {
//darcio
    var xmlhttp = false//Verificar se estamos usando IE
    try {//Se a vers�o JavaScript � maior que 5
        xmlhttp = new ActiveXObject('Msxml2.XMLHTTP');
        //alert('NOVO IE !')
    }catch(e) {//Se n�o, ent�o usar o objeto active x mais antigo
        try {//Se estivermos usando Internet Explorer
            xmlhttp = new ActiveXObject('Microsoft.XMLHTTP')
            //alert('IE ANTIGO !')
        }catch (e) {//Ou devemos estar usando um navegador que n�o � IE.
            xmlhttp = new XMLHttpRequest()
            //alert('OUTROS NAVEGADORES !')
        }
    }
    return xmlhttp
}

/*Essa fun��o tem por objetivo pegar a URL e os objetos da p�gina tratando todos eles como
sendo par�metros de acordo com o m�todo solicitado pelo usu�rio*/
function catch_params(url_that_will_access, method) {
//1) Aqui eu busco todos os elementos de formul�rio e trato todos esses como sendo par�metros ...
    var elements_form = eval('document.form')
    var params_form = ''
    if(typeof(elements_form) != 'undefined') {//Se existir formul�rio na P�gina ...
        for(var i = 0; i < elements_form.length; i++) {
            params_form+= elements_form[i].name + '=' + elements_form[i].value + '&'
        }
        params_form = params_form.substr(0, params_form.length - 1)
    }else {//Se n�o existir form na p�gina, fa�o a busca dos objetos pela Tag ...
        elementos_input = document.getElementsByTagName('INPUT')
        for(var i = 0; i < elementos_input.length; i++) {
            params_form+= elementos_input[i].name + '=' + elementos_input[i].value + '&'
        }
        elementos_combo = document.getElementsByTagName('SELECT')
        for(var i = 0; i < elementos_combo.length; i++) {
            params_form+= elementos_combo[i].name + '=' + elementos_combo[i].value + '&'
        }
        elementos_textarea = document.getElementsByTagName('TEXTAREA')
        for(var i = 0; i < elementos_textarea.length; i++) {
            params_form+= elementos_textarea[i].name + '=' + elementos_textarea[i].value + '&'
        }
        params_form = params_form.substr(0, params_form.length - 1)
    }
/*2) Aqui eu trato os par�metros que foram enviados via URL pelo usu�rio ?, fazendo
a separa��o com o Split ...*/
    var url_dividida = url_that_will_access.split('?')
    var parametros_url = url_dividida[1]
    if(parametros_url != undefined) {//Se existir algum par�metro de URL ...
/*Se foi solicitado que os par�metros de formul�rio fossem passados via GET ent�o transformo esses
no modo solicitado e concateno junto dos par�metros que foram passados na URL ... Gambiarra (rs)*/
        params_url = (method == 'GET') ? parametros_url + '&' + params_form : parametros_url;
    }else {//N�o foi passado nenhum par�metro via URL ...
/*Se foi solicitado que os par�metros de formul�rio fossem passados via GET ent�o transformo esses
no modo solicitado ... Gambiarra (rs) */
        if(method == 'GET') {
/*Aqui eu "tapeio" o Ajax rsrsrs, pois concateno a URL que foi passada sem par�metros c/ os
par�metros de formul�rio para que fique como se o usu�rio tivesse passado o par�metro
de forma manual junto da URL => ?txt_a=1&txt_b=2*/
            url_that_will_access+= '?' + params_form
            var url_dividida = url_that_will_access.split('?')
            var params_url = url_dividida[1]
        }else {
            params_url = ''
        }
    }
    var all_params = (params_form != '' && params_url != '' && (params_form != params_url)) ? params_form + '&' + params_url : (params_form != '' && params_url == '') ? params_form : params_url;
    return all_params
}

/********************************************************************************/
//Fun��o Geral ...
/********************************************************************************/
function ajax(url_that_will_access, object_receive_result, id_selected, exibir_loading, method) {
    var xmlhttp = start_ajax()// Aqui herda da fun��o anterior q faz toda a instancia��o ...
/*Se o usu�rio passou algum m�todo na URL, ent�o ser� assumido o m�todo passado por par�metro
do contr�rio este ser� post devido ser mais seguro ...*/
    method  = ((method != '' && method != undefined) ? method.toUpperCase() : 'POST');
    var obj     = document.getElementById(object_receive_result)//Inst�ncia o Elemento Div
    /*Se o objeto que ir� receber o resultado for uma DIV, ent�o enquanto n�o carrega na p�gina o conte�do requisitado, 
    mostramos esse Loading se desejado, p/ interter o usu�rio se demorar muito o processamento ...*/
    if((obj == '[object HTMLDivElement]' || obj == '[object]') && exibir_loading == 'SIM') {
        obj.innerHTML    = '<img src="/erp/albafer/css/little_loading.gif"> <font size="2" color="brown"><b>LOADING ...</b></font>'
    }
    if(obj == null) {//Se o usu�rio esqueceu de por um ID no objeto, ent�o busco este pelo nome ...
        var obj = eval('document.form.'+object_receive_result)//Inst�ncia o Elemento Html
    }
    var all_params = catch_params(url_that_will_access, method)
//Se conseguiu apontar p/ algum objeto, ent�o ...
    if(obj != null) {
        xmlhttp.open(method, url_that_will_access, true)//Busca de Dados na URL de acordo com o m�todo solicitado
/***********************************************************************************************/
//Tratamento especial p/ tratar os par�metros como POST - serve para objetos de formul�rio e URL ...
        if(method == 'POST') {
            xmlhttp.setRequestHeader('encoding','ISO-8859-1');
            xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');
            xmlhttp.setRequestHeader('Content-length', all_params.length);
            xmlhttp.setRequestHeader('Connection', 'close');
        }
/***********************************************************************************************/
        xmlhttp.onreadystatechange = function() {//
            if(xmlhttp.readyState == 4 && xmlhttp.status == 200) {
//Combo Simples - Select One, Combo M�ltiplo - Select Multiple ou Options da Combo ...
                if(obj.type == 'select-one' || obj.type == 'select-multiple') {
                    if(xmlhttp.responseXML) {//Se conseguir retornar o resultado em XML
                        carregando_combo_processXML(xmlhttp.responseXML, object_receive_result, id_selected)
                    }else {
                        obj.length = 1//Redefino o Tamanho da Combo
                    }
                //Caixa de Texto, Hidden
                }else if(obj.type == 'text' || obj.type == 'hidden') {
                    obj.value = xmlhttp.responseText
                //Div [object HTMLDivElement], etc ...
                }else {
                    obj.innerHTML = xmlhttp.responseText
                }
            }
        }
//Forma de envio dos par�metros ...
        if(method == 'POST') {//Envia como Post ...
            xmlhttp.send(all_params)
        }else {//Envia como Get
            xmlhttp.send(null)
        }
    }else {
        alert('N�O � UM OBJETO V�LIDO !')
    }
}

/********************************************************************************/
//Combo ...
/********************************************************************************/
function carregando_combo_processXML(result_xml, object_receive_result, id_selected) {
    var combo = eval('document.form.'+object_receive_result)
    combo.length = 0//Redefino o Tamanho da Combo
    /********************************************************************************/
//Aqui eu crio o Primeiro Elemento na Combo como sendo Selecione ...
    var option = document.createElement('option');//cria um novo option dinamicamente
    option.setAttribute('id', 'opcoes');//atribui um ID a esse elemento
    option.value = '';//atribui um valor
    option.text = 'SELECIONE';//atribui um texto
    option.style.color = 'red'
    combo.options.add(option)//finalmente adiciona o novo elemento
/********************************************************************************/
//Tenho que limpar a combo p/ que ela n�o fique com os valores antigos armazenados ...
    var valores_xml = result_xml.getElementsByTagName('xml')//pega a tag XML
    if(valores_xml.length > 0) {//total de elementos contidos na tag XML
        for(var i = 0; i < valores_xml.length; i++) {//percorre o arquivo XML para extrair os dados
            var itens = valores_xml[i];
            //Se realmente retornar algum valor ...
            if(itens.getElementsByTagName('rotulo_xml')[0].firstChild != null) {
                //Cont�udo dos campos no arquivo XML
                var id_xml = itens.getElementsByTagName('id_xml')[0].firstChild.nodeValue;
                var rotulo_xml = itens.getElementsByTagName('rotulo_xml')[0].firstChild.nodeValue;
                var option = document.createElement('option');//cria um novo option dinamicamente
                option.setAttribute('id', 'opcoes');//atribui um ID a esse elemento
                option.value = id_xml;//atribui um valor
                option.text = rotulo_xml;//atribui um texto
                //Aqui seleciona na Combo, o valor passado via par�metro pelo Usu�rio ...
                if(id_xml == id_selected) option.setAttribute('selected', 'selected')
                combo.options.add(option)//finalmente adiciona o novo elemento
            }
        }
    }
}

/********************************************************************************/
//Auto Complete ...
/********************************************************************************/

//Vari�vel Global ...
index_selected = 0

function auto_complete(url_that_will_access, caixa_texto_dig, top, left, event, method) {
/*Se o usu�rio passou algum m�todo na URL, ent�o ser� assumido o m�todo passado por par�metro
do contr�rio este ser� post devido ser mais seguro ...*/
    var method = ((method != '' && method != undefined) ? method.toUpperCase() : 'POST');
    if(document.getElementById(caixa_texto_dig).value == '') {
        document.getElementById('div_options').innerHTML = ''
        document.getElementById('div_options').style.visibility = 'hidden'
        limparDestino(div_options)
    }else {
        var xmlhttp = start_ajax()// Aqui herda da fun��o anterior q faz toda a instancia��o ...
        var all_params = catch_params(url_that_will_access, method)
        xmlhttp.open(method, url_that_will_access, true)//Busca de Dados na URL de acordo com o m�todo solicitado
/***********************************************************************************************/
//Tratamento especial p/ tratar os par�metros como POST - serve para objetos de formul�rio e URL ...
        if(method == 'POST') {
            xmlhttp.setRequestHeader('encoding','ISO-8859-1');
            xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded')
            xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
            xmlhttp.setRequestHeader('charset', 'UTF-8')
            xmlhttp.setRequestHeader('Encoding', 'UTF-8')
            xmlhttp.setRequestHeader('Content-length', all_params.length);
            xmlhttp.setRequestHeader('Connection', 'close');
        }
//Aqui verifico a Tecla que est� sendo utilizada ...		
        var tecla_pressionada = (navigator.appName == 'Microsoft Internet Explorer') ? event.keyCode : event.which
        xmlhttp.onreadystatechange = function() {//
            if(xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                json = eval('('+xmlhttp.responseText+')')
/**********************************************************************************************/
//Atrav�s da Linguagem DOM, crio uma Div din�mica que ir� listar as op��es de auto completar ...
/**********************************************************************************************/
                //Propriedades da DIV que ser� criada quando entrar na Tela a 1� Vez ...
                if(document.getElementById('div_options') == null) {//Ainda n�o existe a DIV ...
                    var div_options = document.createElement('div')
                    div_options.setAttribute('id', 'div_options')
                    div_options.style.background = '#E5E5E5'
                    div_options.style.border = '1px solid #000'
                    div_options.style.width = '450px'
                    div_options.style.padding = '0px'
                    div_options.style.position = 'relative'
                    div_options.style.top = top+'px'
                    div_options.style.left = left+'%'
                    div_options.style.margin = '0px'
//Coloco [0], pq sempre s� teremos apenas um body em cada p�gina rsrs ...
                    body = document.getElementsByTagName('body')[0]
                    body.appendChild(div_options)//Adiciona a Div no body da P�gina
                }else {//Aqui significa que a Div j� existe, ent�o s� estou apontando p/ ela ...
                    div_options = document.getElementById('div_options')
                }
/**********************************************************************************************/
                limparDestino(div_options)//Sempre limpa as op��es da DIV ...
                div_options.style.visibility = 'visible'
//S� ir� disparar o loop se a vari�vel retornar algum valor ...
                if(typeof(json.array_palavras) != 'undefined') {
                    for(var x = 0; x < json.array_palavras.length; x++) {
                            json.array_palavras[x] = json.array_palavras[x].toUpperCase()
/**********************************************************************************************/
//Atrav�s da Linguagem DOM, crio as op��es dentro Div din�mica do auto completar ...
/**********************************************************************************************/
                            option_div = document.createElement('p')//P de par�grafo ...
                            option_div.setAttribute('value', x)
                            option_div.setAttribute('text', json.array_palavras[x])
    //Propriedades das Op��es que est�o sendo criadas ...
                            option_div.style.lineHeight = '15px'
                            option_div.style.margin = '0px'
                            option_div.style.font = '12px "Arial", sans-serif'
                            option_div.style.fontWeight = 'bold'
                            option_div.style.padding = '3px'
                            option_div.appendChild(document.createTextNode(json.array_palavras[x]))
                            option_div.style.background = (x == 0 && index_selected == 0) ? '#CDCDCD' : '#E5E5E5'
                            document.getElementById('div_options').appendChild(option_div)
                            /**********************************************************/
                            /*************************Eventos**************************/
                            /**********************************************************/
                            option_div.onclick = new Function("copiar_resultado('"+caixa_texto_dig+"', '"+json.array_palavras[x]+"', '"+div_options+"')")
                            option_div.onmouseover = function() {
                                    this.style.background = '#CDCDCD'
                                    this.style.cursor = 'pointer'
                            }
                            option_div.onmouseout = function() {
                                    this.style.background = '#E5E5E5'
                            }
                            /**********************************************************/
/**********************************************************************************************/
                    }
//Controle para selecionar a Lista via teclado ...
                    if(tecla_pressionada == 13) {//Enter ...
                        copiar_resultado(caixa_texto_dig, json.array_palavras[index_selected], div_options)
                        div_options.style.visibility = 'hidden'
                        index_selected = 0//Zero o �ndice Novamente ...
                    }else if(tecla_pressionada == 27) {//ESC ...
                        div_options.style.visibility = 'hidden'
                        index_selected = 0//Zero o �ndice Novamente ...
                    }else if(tecla_pressionada == 40) {//Seta para baixo ...
                        div_options.childNodes[index_selected].style.background = '#E5E5E5'//Op��o atual selecionada ...
                        index_selected++//Nova op��o Selecionada ...
                        if(index_selected >= json.array_palavras.length) index_selected = 0
                        div_options.childNodes[index_selected].style.background = '#CDCDCD'
                    }else if(tecla_pressionada == 38) {//Seta para cima ...
                        div_options.childNodes[index_selected].style.background = '#E5E5E5'//Op��o atual selecionada ...
                        index_selected--//Nova op��o Selecionada ...
                        if(index_selected < 0) index_selected = json.array_palavras.length - 1
                        div_options.childNodes[index_selected].style.background = '#CDCDCD'
                    }else if(tecla_pressionada == 8) {//BackSpace ...
                        index_selected = 0
                    }
/**********************************************************************************************/
                    //C�digos interessantes por isso que n�o apaguei ...
/**********************************************************************************************/
                    //div_options.childNodes.length
                    //div_options.childNodes[i].getAttribute('text')
                }else {
                    div_options.style.visibility = 'hidden'
                    index_selected = 0//Zero o �ndice Novamente ...
                }
            }
        }
//Forma de envio dos par�metros ...
        if(method == 'POST') {//Envia como Post ...
            xmlhttp.send(all_params)
        }else {//Envia como Get
            xmlhttp.send(null)
        }
    }
}

function copiar_resultado(caixa_texto_dig, resultado_selecionado) {
    document.getElementById(caixa_texto_dig).style.font = '13px "Arial" , sans-serif;'
    document.getElementById(caixa_texto_dig).value = resultado_selecionado
    document.getElementById('div_options').style.visibility ='hidden'
}

//Remove todos os elementos filhos de um elemento
function limparDestino(div_options) {
    while(div_options.firstChild)
    div_options.removeChild(div_options.firstChild)
}