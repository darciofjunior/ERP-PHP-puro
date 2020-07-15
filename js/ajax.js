/********************************************************************************/
//Start Ajax ...
/********************************************************************************/
function start_ajax() {
//darcio
    var xmlhttp = false//Verificar se estamos usando IE
    try {//Se a versão JavaScript é maior que 5
        xmlhttp = new ActiveXObject('Msxml2.XMLHTTP');
        //alert('NOVO IE !')
    }catch(e) {//Se não, então usar o objeto active x mais antigo
        try {//Se estivermos usando Internet Explorer
            xmlhttp = new ActiveXObject('Microsoft.XMLHTTP')
            //alert('IE ANTIGO !')
        }catch (e) {//Ou devemos estar usando um navegador que não é IE.
            xmlhttp = new XMLHttpRequest()
            //alert('OUTROS NAVEGADORES !')
        }
    }
    return xmlhttp
}

/*Essa função tem por objetivo pegar a URL e os objetos da página tratando todos eles como
sendo parâmetros de acordo com o método solicitado pelo usuário*/
function catch_params(url_that_will_access, method) {
//1) Aqui eu busco todos os elementos de formulário e trato todos esses como sendo parâmetros ...
    var elements_form = eval('document.form')
    var params_form = ''
    if(typeof(elements_form) != 'undefined') {//Se existir formulário na Página ...
        for(var i = 0; i < elements_form.length; i++) {
            params_form+= elements_form[i].name + '=' + elements_form[i].value + '&'
        }
        params_form = params_form.substr(0, params_form.length - 1)
    }else {//Se não existir form na página, faço a busca dos objetos pela Tag ...
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
/*2) Aqui eu trato os parâmetros que foram enviados via URL pelo usuário ?, fazendo
a separação com o Split ...*/
    var url_dividida = url_that_will_access.split('?')
    var parametros_url = url_dividida[1]
    if(parametros_url != undefined) {//Se existir algum parâmetro de URL ...
/*Se foi solicitado que os parâmetros de formulário fossem passados via GET então transformo esses
no modo solicitado e concateno junto dos parâmetros que foram passados na URL ... Gambiarra (rs)*/
        params_url = (method == 'GET') ? parametros_url + '&' + params_form : parametros_url;
    }else {//Não foi passado nenhum parâmetro via URL ...
/*Se foi solicitado que os parâmetros de formulário fossem passados via GET então transformo esses
no modo solicitado ... Gambiarra (rs) */
        if(method == 'GET') {
/*Aqui eu "tapeio" o Ajax rsrsrs, pois concateno a URL que foi passada sem parâmetros c/ os
parâmetros de formulário para que fique como se o usuário tivesse passado o parâmetro
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
//Função Geral ...
/********************************************************************************/
function ajax(url_that_will_access, object_receive_result, id_selected, exibir_loading, method) {
    var xmlhttp = start_ajax()// Aqui herda da função anterior q faz toda a instanciação ...
/*Se o usuário passou algum método na URL, então será assumido o método passado por parâmetro
do contrário este será post devido ser mais seguro ...*/
    method  = ((method != '' && method != undefined) ? method.toUpperCase() : 'POST');
    var obj     = document.getElementById(object_receive_result)//Instância o Elemento Div
    /*Se o objeto que irá receber o resultado for uma DIV, então enquanto não carrega na página o conteúdo requisitado, 
    mostramos esse Loading se desejado, p/ interter o usuário se demorar muito o processamento ...*/
    if((obj == '[object HTMLDivElement]' || obj == '[object]') && exibir_loading == 'SIM') {
        obj.innerHTML    = '<img src="/erp/albafer/css/little_loading.gif"> <font size="2" color="brown"><b>LOADING ...</b></font>'
    }
    if(obj == null) {//Se o usuário esqueceu de por um ID no objeto, então busco este pelo nome ...
        var obj = eval('document.form.'+object_receive_result)//Instância o Elemento Html
    }
    var all_params = catch_params(url_that_will_access, method)
//Se conseguiu apontar p/ algum objeto, então ...
    if(obj != null) {
        xmlhttp.open(method, url_that_will_access, true)//Busca de Dados na URL de acordo com o método solicitado
/***********************************************************************************************/
//Tratamento especial p/ tratar os parâmetros como POST - serve para objetos de formulário e URL ...
        if(method == 'POST') {
            xmlhttp.setRequestHeader('encoding','ISO-8859-1');
            xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');
            xmlhttp.setRequestHeader('Content-length', all_params.length);
            xmlhttp.setRequestHeader('Connection', 'close');
        }
/***********************************************************************************************/
        xmlhttp.onreadystatechange = function() {//
            if(xmlhttp.readyState == 4 && xmlhttp.status == 200) {
//Combo Simples - Select One, Combo Múltiplo - Select Multiple ou Options da Combo ...
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
//Forma de envio dos parâmetros ...
        if(method == 'POST') {//Envia como Post ...
            xmlhttp.send(all_params)
        }else {//Envia como Get
            xmlhttp.send(null)
        }
    }else {
        alert('NÃO É UM OBJETO VÁLIDO !')
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
//Tenho que limpar a combo p/ que ela não fique com os valores antigos armazenados ...
    var valores_xml = result_xml.getElementsByTagName('xml')//pega a tag XML
    if(valores_xml.length > 0) {//total de elementos contidos na tag XML
        for(var i = 0; i < valores_xml.length; i++) {//percorre o arquivo XML para extrair os dados
            var itens = valores_xml[i];
            //Se realmente retornar algum valor ...
            if(itens.getElementsByTagName('rotulo_xml')[0].firstChild != null) {
                //Contéudo dos campos no arquivo XML
                var id_xml = itens.getElementsByTagName('id_xml')[0].firstChild.nodeValue;
                var rotulo_xml = itens.getElementsByTagName('rotulo_xml')[0].firstChild.nodeValue;
                var option = document.createElement('option');//cria um novo option dinamicamente
                option.setAttribute('id', 'opcoes');//atribui um ID a esse elemento
                option.value = id_xml;//atribui um valor
                option.text = rotulo_xml;//atribui um texto
                //Aqui seleciona na Combo, o valor passado via parâmetro pelo Usuário ...
                if(id_xml == id_selected) option.setAttribute('selected', 'selected')
                combo.options.add(option)//finalmente adiciona o novo elemento
            }
        }
    }
}

/********************************************************************************/
//Auto Complete ...
/********************************************************************************/

//Variável Global ...
index_selected = 0

function auto_complete(url_that_will_access, caixa_texto_dig, top, left, event, method) {
/*Se o usuário passou algum método na URL, então será assumido o método passado por parâmetro
do contrário este será post devido ser mais seguro ...*/
    var method = ((method != '' && method != undefined) ? method.toUpperCase() : 'POST');
    if(document.getElementById(caixa_texto_dig).value == '') {
        document.getElementById('div_options').innerHTML = ''
        document.getElementById('div_options').style.visibility = 'hidden'
        limparDestino(div_options)
    }else {
        var xmlhttp = start_ajax()// Aqui herda da função anterior q faz toda a instanciação ...
        var all_params = catch_params(url_that_will_access, method)
        xmlhttp.open(method, url_that_will_access, true)//Busca de Dados na URL de acordo com o método solicitado
/***********************************************************************************************/
//Tratamento especial p/ tratar os parâmetros como POST - serve para objetos de formulário e URL ...
        if(method == 'POST') {
            xmlhttp.setRequestHeader('encoding','ISO-8859-1');
            xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded')
            xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
            xmlhttp.setRequestHeader('charset', 'UTF-8')
            xmlhttp.setRequestHeader('Encoding', 'UTF-8')
            xmlhttp.setRequestHeader('Content-length', all_params.length);
            xmlhttp.setRequestHeader('Connection', 'close');
        }
//Aqui verifico a Tecla que está sendo utilizada ...		
        var tecla_pressionada = (navigator.appName == 'Microsoft Internet Explorer') ? event.keyCode : event.which
        xmlhttp.onreadystatechange = function() {//
            if(xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                json = eval('('+xmlhttp.responseText+')')
/**********************************************************************************************/
//Através da Linguagem DOM, crio uma Div dinâmica que irá listar as opções de auto completar ...
/**********************************************************************************************/
                //Propriedades da DIV que será criada quando entrar na Tela a 1ª Vez ...
                if(document.getElementById('div_options') == null) {//Ainda não existe a DIV ...
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
//Coloco [0], pq sempre só teremos apenas um body em cada página rsrs ...
                    body = document.getElementsByTagName('body')[0]
                    body.appendChild(div_options)//Adiciona a Div no body da Página
                }else {//Aqui significa que a Div já existe, então só estou apontando p/ ela ...
                    div_options = document.getElementById('div_options')
                }
/**********************************************************************************************/
                limparDestino(div_options)//Sempre limpa as opções da DIV ...
                div_options.style.visibility = 'visible'
//Só irá disparar o loop se a variável retornar algum valor ...
                if(typeof(json.array_palavras) != 'undefined') {
                    for(var x = 0; x < json.array_palavras.length; x++) {
                            json.array_palavras[x] = json.array_palavras[x].toUpperCase()
/**********************************************************************************************/
//Através da Linguagem DOM, crio as opções dentro Div dinâmica do auto completar ...
/**********************************************************************************************/
                            option_div = document.createElement('p')//P de parágrafo ...
                            option_div.setAttribute('value', x)
                            option_div.setAttribute('text', json.array_palavras[x])
    //Propriedades das Opções que estão sendo criadas ...
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
                        index_selected = 0//Zero o Índice Novamente ...
                    }else if(tecla_pressionada == 27) {//ESC ...
                        div_options.style.visibility = 'hidden'
                        index_selected = 0//Zero o Índice Novamente ...
                    }else if(tecla_pressionada == 40) {//Seta para baixo ...
                        div_options.childNodes[index_selected].style.background = '#E5E5E5'//Opção atual selecionada ...
                        index_selected++//Nova opção Selecionada ...
                        if(index_selected >= json.array_palavras.length) index_selected = 0
                        div_options.childNodes[index_selected].style.background = '#CDCDCD'
                    }else if(tecla_pressionada == 38) {//Seta para cima ...
                        div_options.childNodes[index_selected].style.background = '#E5E5E5'//Opção atual selecionada ...
                        index_selected--//Nova opção Selecionada ...
                        if(index_selected < 0) index_selected = json.array_palavras.length - 1
                        div_options.childNodes[index_selected].style.background = '#CDCDCD'
                    }else if(tecla_pressionada == 8) {//BackSpace ...
                        index_selected = 0
                    }
/**********************************************************************************************/
                    //Códigos interessantes por isso que não apaguei ...
/**********************************************************************************************/
                    //div_options.childNodes.length
                    //div_options.childNodes[i].getAttribute('text')
                }else {
                    div_options.style.visibility = 'hidden'
                    index_selected = 0//Zero o Índice Novamente ...
                }
            }
        }
//Forma de envio dos parâmetros ...
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