//Importa o Arquivo de Sessão ...
document.write("<Script Language = 'JavaScript' src='/erp/albafer/js/sessao.js'></Script>")

function texto(formulario, campo, tamanho, caracteres, mensagem, opcao) {
        var x, valor, auxiliar1, auxiliar2, elemento = eval('document.'+formulario+'.'+campo+'')
        var genero = new  Array(2)
        var valor  = elemento.value
        genero[1]  = 'A'
        genero[2]  = 'O'
	if (elemento.value == '') {
		window.alert('DIGITE '+genero[opcao]+' '+mensagem+' !')
		elemento.focus()
		return false
	}
	for (x = 0; x < elemento.value.length; x ++) {
		if (valor.charCodeAt(x) != 10) {
			if (caracteres.indexOf(elemento.value.charAt(x), 0) == -1) {
				window.alert(mensagem+' INVÁLID'+genero[opcao]+' !')
				elemento.focus()
				elemento.select()
				return false
			}
		}

	}
		auxiliar1 = elemento.value
		auxiliar2 = auxiliar1
		valor  = elemento.value.length
	for (x = 0; x < valor; x ++) {
		auxiliar1 = auxiliar2.replace(' ', '')
		auxiliar2 = auxiliar1
	}
	if (auxiliar2.length < tamanho) {
		window.alert(mensagem+' INCOMPLET'+genero[opcao]+' !')
		elemento.focus()
		elemento.select()
		return false
	}
return true
}

function parametros(formulario, campo, caracteres) {
var x, valor, auxiliar1, auxiliar2, elemento = eval('document.'+formulario+'.'+campo+'')
var interrogacao = elemento.value.indexOf('?', 0)
var sinal		 = elemento.value.indexOf('=', interrogacao)
	for (x = 0; x < elemento.value.length; x ++) {
		if (caracteres.indexOf(elemento.value.charAt(x), 0) == -1) {
			window.alert('PARAMETRO INVÁLIDO !')
			elemento.focus()
			elemento.select()
			return false
		}
	}
	if (interrogacao != 0) {
		window.alert('PARAMETRO INVÁLIDO !')
		elemento.focus()
		elemento.select()
		return false
	}
	if (elemento.value.indexOf('?', interrogacao + 1) != - 1) {
		window.alert('PARAMETRO INVÁLIDO !')
		elemeto.focus()
		elemento.select()
		return false
	}
	
	if (sinal == -1 || sinal == 1) {
		window.alert('PARAMETRO INVÁLIDO !')
		elemento.focus()
		elemento.select()
		return false
	}		
		auxiliar1 = elemento.value
		auxiliar2 = auxiliar1
		valor  = elemento.value.length
	for (x = 0; x < valor; x ++) {
		auxiliar1 = auxiliar2.replace(' ', '')
		auxiliar2 = auxiliar1
	}
	if (auxiliar2.length < 4) {
		window.alert('PARAMETRO INCOMPLETO !')
		elemento.focus()
		elemento.select()
		return false
	}	
return true
}


function email(formulario, campo, tamanho, caracteres, mensagem) {
var x, valor, auxiliar1, auxiliar2, auxiliar3, elemento = eval('document.'+formulario+'.'+campo+'')
var arroba      = elemento.value.indexOf('@', 1)
var ponto		= elemento.value.indexOf('.', arroba)

	if (elemento.value == '') {
		window.alert('DIGITE '+mensagem+' !')
		elemento.focus()
		return false
	}	
	for (x = 0; x < elemento.value.length; x ++) {
		if (caracteres.indexOf(elemento.value.charAt(x), 0) == -1) {
			window.alert(mensagem+' INVÁLIDO !')
			elemento.focus()
			elemento.select()
			return false
		}
	}				
	if (arroba == -1) {
		window.alert(mensagem+' INVÁLIDO !')
		elemento.focus()
		elemento.select()
		return false
	}
	if (elemento.value.indexOf('@', arroba + 1) != - 1) {
		window.alert(mensagem+' INVÁLIDO !')
		elemeto.focus()
		elemento.select()
		return false
	}
	if (ponto == -1) {
		window.alert(mensagem+' INVÁLIDO !')
		elemento.focus()
		elemento.select()
		return false
	}
		auxiliar1 = elemento.value
		auxiliar2 = auxiliar1
		valor  = elemento.value.length
	for (x = 0; x < valor; x ++) {
		auxiliar1 = auxiliar2.replace(' ', '')
		auxiliar2 = auxiliar1
	}
	if ( auxiliar2.length < tamanho) {
		window.alert(mensagem+' INCOMPLETO !')
		elemento.focus()
		elemento.select()
		return false
	}
return true
}

function data(formulario, campo, valor, mensagem) {
	var separacao, data, dia, mes, ano, elemento = eval('document.'+formulario+'.'+campo+'')
	if (elemento.value == '') {
		window.alert('DIGITE A DATA DE '+mensagem+' !')
		elemento.focus()
		elemento.select()
		return false
	}
//A data sempre terá que ter 10 dígitos, pois segue o Formato de DD/MM/YYYY ...
	if (elemento.value.length < 10) {
		window.alert('DATA DE '+mensagem+' INVÁLIDA DIGITE NO FORMATO \nEXEMPLO: DD/MM/YYYY !')
		elemento.focus()
		elemento.select()
		return false
	}
	dia = elemento.value.substring(0, 2)
//Aqui eu subtrai 1 do mês, por causa do GetMonth()
	mes = elemento.value.substring(3, 5) - 1
	ano = elemento.value.substring(6, 10)
	data = new Date(ano, mes, dia)

	if (data.getUTCDate() != dia) {
       	alert('DATA DE '+mensagem+' INVÁLIDA !')
		elemento.focus()
		elemento.select()
		return false
	}else if (data.getMonth() != mes) {
 		window.alert('DATA DE '+mensagem+ '  INVÁLIDA !')
		elemento.focus()
		elemento.select()
		return false
	}

/*Aqui eu somo + 1, para voltar o valor do mês original conforme passado por
parâmetro*/
	mes++
	bissexto = ano % 4

	if((bissexto != "0") && (dia == "29") && (mes == "02")) {
		window.alert('DATA DE '+mensagem+ '  INVÁLIDA !')
		return false
	}

	ano = eval(ano)
	valor = eval(valor)

	if (ano > valor) {
		window.alert('DATA INVÁLIDA !')
		elemento.focus()
		elemento.select()
		return false
	}
	return true
}

function formatar_data(formulario, campo) {
var x, elemento = eval('document.'+formulario+'.'+campo+'')
	if (navigator.appName == 'Microsoft Internet Explorer') {
		if (elemento.value.length == 2) {
			elemento.value = elemento.value + '/'
		}else if (elemento.value.length == 5) {
			elemento.value = elemento.value + '/'
		}
	}
}

function cpf(formulario, campo) {
var elemento = eval('document.'+formulario+'.'+campo+'')
var x, resto = 0, soma = 0
	if (elemento.value == '') {
		window.alert('DIGITE O CPF !')
		elemento.focus()
		elemento.select()
		return false
	}
	if (elemento.value == '00000000000' || elemento.value == '11111111111' || elemento.value == '22222222222' || elemento.value == '33333333333' || elemento.value == '44444444444' || elemento.value == '55555555555' || elemento.value == '66666666666' || elemento.value == '77777777777' || elemento.value == '88888888888' || elemento.value == '99999999999') {
		window.alert('CPF INVÁLIDO !')
		elemento.focus()
		elemento.select()
		return false
	}
	for (x = 0; x < 9; x ++)  {
		soma += parseInt(elemento.value.charAt(x)) * (10 - x)
		resto = 11 - (soma % 11)
	}
	if (resto == 10 || resto == 11) {
		resto = 0
	}
	if (resto != parseInt(elemento.value.charAt(9))) {
		window.alert('CPF INVÁLIDO !')
		elemento.focus()
		elemento.select()
		return false
	}
	soma = 0
	for (x = 0; x < 10; x ++) {
		soma += parseInt(elemento.value.charAt(x)) * (11 - x)
		resto = 11 - (soma % 11)
		if (resto == 10 || resto == 11) {
			resto = 0
		}
	}
	if (resto != parseInt(elemento.value.charAt(10))) {
		window.alert('CPF INVÁLIDO !')
		elemento.focus()
		elemento.select()
		return false
	}
return true
}

function cnpj(formulario, campo) {
var elemento = eval('document.'+formulario+'.'+campo+'')
var x, valor = elemento.value, conta, divisao_vertical, divisao_1ateral
   if (valor == '') {
       window.alert('DIGITE O CNPJ !')
       elemento.focus()
       elemento.select()
       return false
   }

conta               =  valor.substr(0, 12)
divisao_vertical =  valor.substr(12, 2)
divisao_1ateral  = 0
   for (x = 0; x < 12; x ++) {
       divisao_1ateral += conta.charAt(11 - x) * (2 + (x % 8))
   }
   if (divisao_1ateral == 0)
       return false
       divisao_1ateral = 11 - (divisao_1ateral % 11)
   if (divisao_1ateral > 9)
       divisao_1ateral = 0
   if (divisao_vertical.charAt(0) != divisao_1ateral) {
       window.alert('CNPJ INVÁLIDO !')
       elemento.focus()
       elemento.select()
       return false
   }

   divisao_1ateral *= 2
   for (x = 0; x < 12; x ++) {
       divisao_1ateral += conta.charAt(11 - x) * (2 + ((x + 1) % 8))
   }
   divisao_1ateral = 11 - (divisao_1ateral % 11)
   if (divisao_1ateral > 9)
       divisao_1ateral = 0
   if (divisao_vertical.charAt(1) != divisao_1ateral) {
       window.alert('CNPJ INVÁLIDO !')
       elemento.focus()
       elemento.select()
       return false
   }
   return true
}

function combo(formulario, campo, valor, mensagem) {
var elemento = eval('document.'+formulario+'.'+campo+'')
var index    = eval('document.'+formulario+'.'+campo+'.selectedIndex')
	if (elemento.options[index].value == valor || elemento.options[index].value  == ' ') {
		window.alert(mensagem)
		elemento.focus()
		return false
	}
return true
}

function combo_falso(formulario, numero, valor) {
var elementos = eval('document.'+formulario+'.elements')
var index     = elementos[numero].selectedIndex
	if (elementos[numero].options[index].value == valor) {
		elementos[numero].options[index].selected = false
	}
}

function combo_multiplo(formulario, numero, quantidade, mensagem) {
var x, contador = 0, elementos = eval('document.'+formulario+'.elements')
for (x = 0; x < elementos[numero].length; x ++) {
		if (elementos[numero].options[x].selected == true) {
			contador ++
		}
	}
	if (contador < quantidade) {
		window.alert(mensagem)
		return false
	}
return true
}


function combo_maximo(formulario, numero, quantidade, mensagem) {
var x, contador = 0, elementos = eval('document.'+formulario+'.elements')
var index       = elementos[numero].selectedIndex
for (x = 0; x < elementos[numero].length; x ++) {		
		if (elementos[numero].options[x].selected == true) {
			contador ++
		}
		if (contador > quantidade) {
			elementos[numero].options[x].selected = false
			window.alert(mensagem)
			return false
		}
	}
return true
}

function option(formulario, campo, mensagem) {
var x, option  = 0, elemento  = eval('document.'+formulario+'.'+campo+'')
	if (elemento.checked == true) {
		return true
	}
	if (elemento.checked == false) {
		window.alert(mensagem)
		return false
	}
	for (x = 0; x < elemento.length; x ++) {
		if (elemento[x].checked == true) {
			option ++
		}
	}
	if (option == 0) {
		window.alert(mensagem)
		return false
	}
return true
}

function validar_checkbox(formulario, mensagem) {
    var valor = false, elementos = eval('document.'+formulario+'.elements')
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) valor = true
        }
    }
    
    if(valor == false) {
        alert(mensagem)
        return false
    }else {
        var confirmacao = confirm('CONFIRMA A EXCLUSÃO ?')
        if(confirmacao == true) {
            return true
        }else {
            return false
        }
    }
}

function selecionar_todos_checkbox(formulario, campo) {
	var x, valor   = true, elementos = eval('document.'+formulario+'.elements')
	var selecionar = eval('document.'+formulario+'.'+campo+'')
		if (selecionar.checked == false) {
			valor = false
		}
		for (x = 0; x < elementos.length; x ++)   {
			if (elementos[x].type == 'checkbox')  {
				elementos[x].checked = eval(valor)
			}
		}
}


function selecionar_checkbox(formulario, campo) {
	var x, valor   = true, elementos = eval('document.'+formulario+'.elements')
	var selecionar = eval('document.'+formulario+'.'+campo+'')
		for (x = 0; x < elementos.length; x ++) {
			if (elementos[x].type == 'checkbox' && elementos[x].name != campo) {
				if (elementos[x].checked == false) {
					valor = false
				}
			}
		}
		selecionar.checked = eval(valor)
}

function comparar(formulario, campo1, campo2, mensagem) {
var elemento1 = eval('document.'+formulario+'.'+campo1+'.value')
var elemento2 = eval('document.'+formulario+'.'+campo2+'')
		if (elemento1 != elemento2.value) {
			window.alert(mensagem)
			elemento2.focus()
			elemento2.select()
			return false
		}
	return true
}

function mover_focu(formulario, campo1, campo2, valor) {
var elemento1 = eval('document.'+formulario+'.'+campo1+'.value.length')
var elemento2 = eval('document.'+formulario+'.'+campo2+'')
	if (elemento1 >= valor) {
		elemento2.focus()
	}
}

function confirmar(formulario, mensagem) {
	var elemento = eval('document.'+formulario+'')
		if (confirm(mensagem)) {
			elemento.submit()
		}else {
			return false
		}
	return true
}

function enviar(formulario, campo, valor) {
    var elemento1 = eval('document.'+formulario+'.'+campo+'')
    var elemento2 = eval('document.'+formulario+'')
    elemento1.value = valor
    elemento2.submit()
}

function digito_numero() {
	if (navigator.appName == 'Microsoft Internet Explorer') {
		if (!(event.keyCode > 47 && event.keyCode < 58)) {
			event.keyCode = 0
		}
	}
}

function new_email(formulario, campo) {
	var palavra = eval('document.'+formulario+'.'+campo+'')
//Se maior q 5 caracteres então ele vasculha no loop
	if(palavra.value.length >= 5) {
//Verifica se o e-mail possui arroba
		var achou_arroba = palavra.value.indexOf('@')
		var achou_ponto = palavra.value.indexOf('.')
		var achou_espaco = palavra.value.indexOf(' ')
/*Significa que o e-mail digitado não tem arroba ou ponto, sendo assim está
em formato inválido*/
		if(achou_arroba == -1) {
			alert('DIGITE UM E-MAIL VÁLIDO !')
			palavra.focus()
			palavra.select()
			return false
		}
		if(achou_ponto == -1) {
			alert('DIGITE UM E-MAIL VÁLIDO !')
			palavra.focus()
			palavra.select()
			return false
		}
//Verifica se o arroba ou o ponto estão exatamente na primeira posição do string
		if(achou_arroba == 0 || achou_ponto == 0) {
			return 0;
		}else {
			if(achou_ponto < achou_arroba) {
				for(i = achou_arroba; i < palavra.value.length; i++) {
					if(palavra.value.substr(i, 1) == '.') {
						var posicao_ponto_loop = i
						i = palavra.value.length
					}
				}
			}else {
				var posicao_ponto_loop = achou_ponto
			}
//Verifica se o ponto não está logo após o arroba ex: --> luis_gomes@. <-- inválido
			if((posicao_ponto_loop - achou_arroba) == 1) {
				alert('DIGITE UM E-MAIL VÁLIDO !')
				palavra.focus()
				palavra.select()
				return false
			}else {
/*Significa que achou um espaço no e-mail digitado, sendo assim está
em formato inválido*/
				if(achou_espaco != -1) {
					alert('DIGITE UM E-MAIL VÁLIDO !')
					palavra.focus()
					palavra.select()
					return false
				}
//Verifico se eu tenho pelo menos um caracter depois do .
				if((palavra.value.length - posicao_ponto_loop) == 1) {
					alert('DIGITE UM E-MAIL VÁLIDO !')
					palavra.focus()
					palavra.select()
					return false
				}else {
//Converte o e-mail para em minúsculo que é o padrão correto
					palavra.value = palavra.value.toLowerCase()
					return true
				}
			}
		}
	}else {
		alert('DIGITE UM E-MAIL VÁLIDO !')
		palavra.focus()
		palavra.select()
		return false
	}
}