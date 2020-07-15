/*Função Verificar String

Objetivo: Localizar um trecho de String dentro de uma String principal
Data de Criação: 27/01/2005
Observação: Conservar em local fresco

Parâmetros

localizar: Trecho de texto com o qual desejo encontrar dentro de uma string principal

objeto: Objeto em html aonde eu desejo que contém a string principal, ex:
caixa de texto, textarea, botões, ...

mensagem: Mensagem que retornará caso não encontrar a string

sensitive: Faz a procura exata de uma string
Caso seje passado S ou s de sim faz a procura exata dentro de uma String ex: TEstE*/

function verificar_string(localizar, objeto, mensagem, sensitive) {
    var achou   = 0
    objeto      = eval(objeto)
    
    if(sensitive != 'S' && sensitive != 's') {
        localizar   = localizar.toUpperCase()
        objeto_aux  = objeto.value.toUpperCase()
    }

    for(i = 0; i < objeto.value.length; i++) {
        if(sensitive != 'S' && sensitive != 's') {
            if(objeto_aux.substr(i, localizar.length) == localizar) achou = 1
        }else {
            if(objeto.value.substr(i, localizar.length) == localizar) achou = 1
        }
    }

    if(achou == 0) {
        alert(mensagem)
        objeto.focus()
        return false
    }else {
        return true
    }
}
