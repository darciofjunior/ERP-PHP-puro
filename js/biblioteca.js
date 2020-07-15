function caracteres_especiais() {
	var ns4=document.layers?1:0
	var ie4=document.all?1:0
	var ns6=document.getElementById&&!document.all?1:0

	if (ns4)
		document.captureEvents(Event.KEYPRESS)

	function menuengine(e) {
		if (ns4 || ns6) {
			if (!(e.which == 0) && !(e.which == 8) && !(e.which == 45) && !(e.which > 47 && e.which < 58)) {
				return false
			}
		}else if (ie4) {
			if (!(event.keyCode == 8) && !(event.keyCode == 45) && !(event.keyCode > 47 && event.keyCode < 58)) {
				return false
			}
		}
	}
	document.onkeypress=menuengine
}

function detecta() {
	var navegadores = Array('Gailleon','Microsoft Internet Explorer', 'Konqueror', 'Mozilla', 'MsnExplorer', 'NeoPlanet', 'Netscape Comunicator', 'Netscape', 'Opera', 'Safari')
	var versoes = Array('1.0', '4.0', '1.0', '1.0', '1.0', '1.0', '1.0', '1.0', '1.0', '1.0')
	var total_navegadores = navegadores.length
	var pto_virgula = 0, versao = ''
	var validos = '0123456789.'
//Verifica se consta o Java
	if(!navigator.javaEnabled()) {
		alert('NÃO TEM COMO ENTRAR NO SISTEMA PORQUE NÃO CONSTA O PLUGIN DO JAVA !')
		//return false
	}
//Verifica se consta o Flash
	/*hasFlash = false
	for(i = 0; i < navigator.plugins.length; i++) {
		if(navigator.plugins[i].name.indexOf("Flash") >= 0) {
			hasFlash = true
		}
	}*/
//Verifica o tipo de Navegador
	if(navigator.appName == 'Microsoft Internet Explorer') {
		for(i = 0; i < navigator.appVersion.length; i++) {
			if(navigator.appVersion.charAt(i) == ';') {
				pto_virgula ++
			}else {
				if(pto_virgula == 1) {
					if(validos.indexOf(navigator.appVersion.charAt(i), 0) != -1) {
						versao = versao + navigator.appVersion.charAt(i)
					}
				}		
				if(pto_virgula == 2) {
					i = navigator.appVersion.length
				}
			}
		}
		alert('NAVEGADOR: '+navigator.appName+'\nVERSÃO: '+versao)
	}else {
		alert('NAVEGADOR: '+navigator.appName+'\nVERSÃO: '+navigator.appVersion)
	}
	for(i = 0; i < total_navegadores; i++) {
		if(navigator.appName == navegadores[i]) {
			if(versao < versoes[i]) {
				alert('NÃO TEM COMO ENTRAR NO SISTEMA DEVIDO A VERSÃO DO NAVEGADOR ESTAR DESATUALIZADA !')
				return false
			}
			i = total_navegadores
		}
	}
}

//Validação na hora de perder o foco
//Função Data

function verifica_data(objeto) {
	if(objeto == '[object]' || objeto == '[object HTMLInputElement]') {
		var elemento = objeto
	}else {
		var elemento = eval(objeto)
	}
	var expressao = /^\d{2}\/{1}\d{2}\/{1}\d{4}$/
	if (expressao.test(elemento.value) == false) {
		alert('DATA INVÁLIDA !')
		elemento.focus()
		elemento.select()
		return false
	}
	dia = elemento.value.substring(0, 2)
	mes = elemento.value.substring(3, 5) - 1
	ano = elemento.value.substring(6, 10)
	data = new Date(ano, mes, dia)	
	if (data.getDate() != dia) 	{
		window.alert('DATA INVÁLIDA !')
		elemento.focus()
		elemento.select()
		return false
	}else if (data.getMonth() != mes) {
		window.alert('DATA INVÁLIDA !')
		elemento.focus()
		elemento.select()
		return false
	}
	return true
}

//Função Cep

function verifica_cep(objeto) {
	if(objeto == '[object]' || objeto == '[object HTMLInputElement]') {
		var elemento = objeto
	}else {
		var elemento = eval(objeto)
	}
	var expressao = /^\d{5}-\d{3}$/ 
  	if (expressao.test(elemento.value) == false) {
		alert('CEP INVÁLIDO !')
		elemento.focus()
		return false
	}
	return true
}

function verifica_telefone(objeto) {
	if(objeto == '[object]' || objeto == '[object HTMLInputElement]') {
		var elemento = objeto
	}else {
		var elemento = eval(objeto)
	}
	var expressao = /^\d{3}-\d{4}$/
	var expressao2 = /^\d{4}-\d{4}$/
  	if (expressao.test(elemento.value) == false && expressao2.test(elemento.value) == false) {
		alert('TELEFONE INVÁLIDO !')
		elemento.focus()
		elemento.select()
		return false
	}
	return true
}

//Validação na hora de digitar
//Telefone

function telefone_digito(elemento, event) {
	var x, y, tamanho1 = elemento.value.length, tamanho2, auxiliar1, auxiliar2
	var cont_traco = 0, cont = 0
	var ultimo_caracter, tres_primeiros, quatro_primeiros, quatro_ultimos
	var validos = '0123456789-'
	
	caracteres_especiais()
	
	if (navigator.appName == 'Microsoft Internet Explorer') {
		if(event.keyCode == 8 || event.keyCode == 16 || (event.keyCode > 34 && event.keyCode < 41) || event.keyCode == 46) {
			return false
		}
	}else {
		if(event.which == 8 || event.which == 16 || (event.which > 34 && event.which < 41) || event.which == 46) {
			return false
		}
	}
	
	for (x = 0; x < tamanho1; x ++)
		break
		auxiliar1 = ''
		for (; x < tamanho1; x ++)
			if (validos.indexOf(elemento.value.charAt(x)) != -1) 
				auxiliar1 += elemento.value.charAt(x) + ''
				tamanho1   = auxiliar1.length
		if (tamanho1 >= 0) {
			auxiliar2 = ''
			for (y = 0, x = tamanho1 - 3; x >= 0; x --) {
			    auxiliar2 += auxiliar1.charAt(x)
			    y ++
	    }
       	elemento.value = ''
		tamanho2 = auxiliar2.length
		for (x   = tamanho2 - 1; x >= 0; x --)
			elemento.value += auxiliar2.charAt(x)
			elemento.value += auxiliar1.substr(tamanho1 - 2, tamanho1)
		}
	
	for (x = 0; x < elemento.value.length; x ++) {
		if(elemento.value.charAt(x) == '-' && (x != 3 && x != 4)) {
			elemento.value = elemento.value.substr(0, x)
		}
	}
	if(elemento.value.length == 3) {
		elemento.value = elemento.value + '-'
	}
	if(elemento.value.length == 4) {
		if(elemento.value.charAt(3) != '-') {
			ultimo_caracter = elemento.value.substr(3, 1)
			elemento.value = elemento.value.substr(0, 3) + '-' + ultimo_caracter
		}
	}
	if(elemento.value.length == 5) {
		if(elemento.value.charAt(3) == '-' && elemento.value.charAt(4) == '-') {
			elemento.value = elemento.value.substr(0, 4)
		}
	}
	if(elemento.value.length == 7) {
		if(elemento.value.charAt(3) != '-') {
      		elemento.value = elemento.value.substr(0, 3) + '-' + elemento.value.substr(3, 4)
		}
	}
	if(elemento.value.length == 8) {
		if(elemento.value.charAt(3) != '-') {
      		elemento.value = elemento.value.substr(0, 3) + '-' + elemento.value.substr(4, 4)
		}
	}
	if(elemento.value.length == 8) {
		if(elemento.value.substr(3, 1) == '-') {
			tres_primeiros = elemento.value.substr(0, 3)
			quatro_ultimos = elemento.value.substr(4, 4)
		}else {
			tres_primeiros = elemento.value.substr(0, 3)
			quatro_ultimos = elemento.value.substr(3, 1) + elemento.value.substr(5, 3)
		}
		elemento.value = tres_primeiros + '-' + quatro_ultimos
	}
	if(elemento.value.length == 9) {
		if(elemento.value.substr(3, 1) == '-') {
			quatro_primeiros = elemento.value.substr(0, 3) + elemento.value.substr(4,1)
		}else {
			quatro_primeiros = elemento.value.substr(0, 4)
		}
		quatro_ultimos = elemento.value.substr(5,4)
		elemento.value = quatro_primeiros + '-' + quatro_ultimos
	}
	if(elemento.value.length == 9) {
		if(elemento.value.charAt(4) != '-') {
			elemento.value = elemento.value.substr(0, 4) + '-' + elemento.value.substr(5, 4)
		}
	}
	for(i = 0; i < elemento.value.length; i++) {
		if(elemento.value.charAt(i) == '-') {
			cont_traco++
		}
	}
	if(cont_traco == 2) {
		elemento.value = elemento.value.replace('-','')
	}
	
	if(elemento.value.length > 9) {
		elemento.value = elemento.value.substr(0, 9)
	}
}

//Cep

function cep_digito(elemento, event) {
	var x, y, tamanho1 = elemento.value.length, tamanho2, auxiliar1, auxiliar2
	var ultimo_caracter
	var validos = '0123456789-'
	
	caracteres_especiais()
	
	if (navigator.appName == 'Microsoft Internet Explorer') {
		if(event.keyCode == 8 || event.keyCode == 16 || (event.keyCode > 34 && event.keyCode < 41) || event.keyCode == 46) {
			return false
		}
	}else {
		if(event.which == 8 || event.which == 16 || (event.which > 34 && event.which < 41) || event.which == 46) {
			return false
		}
	}
	
	for (x = 0; x < tamanho1; x ++)
		break
		auxiliar1 = ''
		for (; x < tamanho1; x ++)
			if (validos.indexOf(elemento.value.charAt(x)) != -1) 
				auxiliar1 += elemento.value.charAt(x) + ''
				tamanho1   = auxiliar1.length
		if (tamanho1 >= 0) {
			auxiliar2 = ''
			for (y = 0, x = tamanho1 - 3; x >= 0; x --) {
			    auxiliar2 += auxiliar1.charAt(x)
			    y ++
	    }
       	elemento.value = ''
		tamanho2 = auxiliar2.length
		for (x   = tamanho2 - 1; x >= 0; x --)
			elemento.value += auxiliar2.charAt(x)
			elemento.value += auxiliar1.substr(tamanho1 - 2, tamanho1)
		}
	
	for (x = 0; x < elemento.value.length; x ++) {
		if(elemento.value.charAt(x) == '-' && x != 5) {
			elemento.value = elemento.value.substr(0, x)
		}
	}
	if(elemento.value.length == 5) {
		elemento.value = elemento.value + '-'
	}
	if(elemento.value.length == 6) {
		if(elemento.value.charAt(5) != '-') {
			ultimo_caracter = elemento.value.substr(5, 1)
			elemento.value = elemento.value.substr(0, 5) + '-' + ultimo_caracter
		}
	}
	if(elemento.value.length == 7) {
		if(elemento.value.charAt(5) != '-') {
			ultimo_caracter = elemento.value.substr(6, 1)
			elemento.value = elemento.value.substr(0, 5) + '-' + ultimo_caracter
		}
	}
	if(elemento.value.length == 8) {
		if(elemento.value.charAt(5) != '-') {
			ultimo_caracter = elemento.value.substr(6, 2)
			elemento.value = elemento.value.substr(0, 5) + '-' + ultimo_caracter
		}
	}
	if(elemento.value.length == 9) {
		if(elemento.value.charAt(5) != '-') {
			elemento.value = elemento.value.substr(0, 5) + '-' + elemento.value.substr(6, 3)
		}
	}
	if(elemento.value.length > 9) {
		elemento.value = elemento.value.substr(0, 9)
	}
}

//CPF

function cpf_digito(elemento, event) {
	var x, y, tamanho1 = elemento.value.length, tamanho2, auxiliar1, auxiliar2
	var resto = 0, soma = 0
	var ultimo_caracter
	var validos = '0123456789.-'
	
	caracteres_especiais()
	
	if (navigator.appName == 'Microsoft Internet Explorer') {
		if(event.keyCode == 8 || event.keyCode == 16 || (event.keyCode > 34 && event.keyCode < 41) || event.keyCode == 46) {
			return false
		}
	}else {
		if(event.which == 8 || event.which == 16 || (event.which > 34 && event.which < 41) || event.which == 46) {
			return false
		}
	}
	
	for (x = 0; x < tamanho1; x ++)
		break
		auxiliar1 = ''
		for (; x < tamanho1; x ++)
			if (validos.indexOf(elemento.value.charAt(x)) != -1) 
				auxiliar1 += elemento.value.charAt(x) + ''
				tamanho1   = auxiliar1.length
		if (tamanho1 >= 0) {
			auxiliar2 = ''
			for (y = 0, x = tamanho1 - 3; x >= 0; x --) {
			    auxiliar2 += auxiliar1.charAt(x)
			    y ++
	    }
       	elemento.value = ''
		tamanho2 = auxiliar2.length
		for (x   = tamanho2 - 1; x >= 0; x --)
			elemento.value += auxiliar2.charAt(x)
			elemento.value += auxiliar1.substr(tamanho1 - 2, tamanho1)
		}
	
	for (x = 0; x < elemento.value.length; x ++) {
		if(elemento.value.charAt(x) == '.' && (x != 3 && x != 7)) {
			elemento.value = elemento.value.substr(0, x)
		}
	}
	for (x = 0; x < elemento.value.length; x ++) {
		if(elemento.value.charAt(x) == '-' && x != 11) {
			elemento.value = elemento.value.substr(0, x)
		}
	}
	
	if(elemento.value.length == 3 || elemento.value.length == 7 || elemento.value.length == 11) {
		if(elemento.value.length == 3 || elemento.value.length == 7) {
			elemento.value = elemento.value + '.'
		}else {
			elemento.value = elemento.value + '-'
		}
	}
	if(elemento.value.length == 4) {
		if(elemento.value.charAt(3) != '.') {
			ultimo_caracter = elemento.value.substr(3, 1)
			elemento.value = elemento.value.substr(0, 3) + '.' + ultimo_caracter
		}
	}
	if(elemento.value.length == 5) {
		if(elemento.value.charAt(3) != '.') {
			ultimo_caracter = elemento.value.substr(4,1)
			elemento.value = elemento.value.substr(0, 3) + '.' + ultimo_caracter
		}
	}
	if(elemento.value.length == 6) {
		if(elemento.value.charAt(3) != '.') {
			ultimo_caracter = elemento.value.substr(4,2)
			elemento.value = elemento.value.substr(0, 3) + '.' + ultimo_caracter
		}
	}
	if(elemento.value.length == 8) {
		if(elemento.value.charAt(3) != '.') {
			ultimo_caracter = elemento.value.substr(4,3)
			elemento.value = elemento.value.substr(0, 3) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(7) != '.') {
			ultimo_caracter = elemento.value.substr(7, 1)
			elemento.value = elemento.value.substr(0, 7) + '.' + ultimo_caracter
		}
	}
	if(elemento.value.length == 9) {
		if(elemento.value.charAt(3) != '.') {
			ultimo_caracter = elemento.value.substr(4,5)
			elemento.value = elemento.value.substr(0, 3) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(7) != '.') {
			ultimo_caracter = elemento.value.substr(8, 1)
			elemento.value = elemento.value.substr(0, 7) + '.' + ultimo_caracter
		}
	}
	if(elemento.value.length == 10) {
		if(elemento.value.charAt(3) != '.') {
			ultimo_caracter = elemento.value.substr(4,6)
			elemento.value = elemento.value.substr(0, 3) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(7) != '.') {
			ultimo_caracter = elemento.value.substr(8, 2)
			elemento.value = elemento.value.substr(0, 7) + '.' + ultimo_caracter
		}
	}
	if(elemento.value.length == 12) {
		if(elemento.value.charAt(3) != '.') {
			ultimo_caracter = elemento.value.substr(4,8)
			elemento.value = elemento.value.substr(0, 3) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(7) != '.') {
			ultimo_caracter = elemento.value.substr(8, 4)
			elemento.value = elemento.value.substr(0, 7) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(11) != '-') {
			ultimo_caracter = elemento.value.substr(11, 1)
			elemento.value = elemento.value.substr(0, 11) + '-' + ultimo_caracter
		}
	}
	if(elemento.value.length == 13) {
		if(elemento.value.charAt(3) != '.') {
			ultimo_caracter = elemento.value.substr(4, 9)
			elemento.value = elemento.value.substr(0, 3) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(7) != '.') {
			ultimo_caracter = elemento.value.substr(8, 5)
			elemento.value = elemento.value.substr(0, 7) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(11) != '-') {
			ultimo_caracter = elemento.value.substr(12, 1)
			elemento.value = elemento.value.substr(0, 11) + '-' + ultimo_caracter
		}
	}
	if(elemento.value.length > 14) {
		elemento.value = elemento.value.substr(0, 14)
	}
	if(elemento.value.length == 14) {
		if(elemento.value.charAt(3) != '.') {
			ultimo_caracter = elemento.value.substr(4, 10)
			elemento.value = elemento.value.substr(0, 3) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(7) != '.') {
			ultimo_caracter = elemento.value.substr(8, 6)
			elemento.value = elemento.value.substr(0, 7) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(11) != '-') {
			ultimo_caracter = elemento.value.substr(12, 2)
			elemento.value = elemento.value.substr(0, 11) + '-' + ultimo_caracter
		}
		var valor = elemento.value
		valor = valor.replace('.', '')
		valor = valor.replace('.', '')
		valor = valor.replace('-', '')
		for (x = 0; x < 9; x ++)  {
			soma += parseInt(valor.charAt(x)) * (10 - x)
			resto = 11 - (soma % 11)
		}
		if (resto == 10 || resto == 11) {
			resto = 0	
		}
		if (resto != parseInt(valor.charAt(9))) {
			window.alert('CPF INVÁLIDO !')
			elemento.focus()
			return false
		}
		soma = 0
		for (x = 0; x < 10; x ++) {
			soma += parseInt(valor.charAt(x)) * (11 - x)
			resto = 11 - (soma % 11)
			if (resto == 10 || resto == 11) {
				resto = 0
			}
		}
		if (resto != parseInt(valor.charAt(10))) {
			window.alert('CPF INVÁLIDO !')
			elemento.focus()
			return false	
		}
	}
	return true
}

//CNPJ

function cnpj_digito(elemento, event) {
	var x, y, tamanho1 = elemento.value.length, tamanho2, auxiliar1, auxiliar2
	var conta, divisao_vertical, divisao_1ateral
	var ultimo_caracter
	var validos = '0123456789.-/'
	
	caracteres_especiais()
	
	if (navigator.appName == 'Microsoft Internet Explorer') {
		if(event.keyCode == 8 || event.keyCode == 16 || (event.keyCode > 34 && event.keyCode < 41) || event.keyCode == 46) {
			return false
		}
	}else {
		if(event.which == 8 || event.which == 16 || (event.which > 34 && event.which < 41) || event.which == 46) {
			return false
		}
	}
	
	for (x = 0; x < tamanho1; x ++)
		break
		auxiliar1 = ''
		for (; x < tamanho1; x ++)
			if (validos.indexOf(elemento.value.charAt(x)) != -1) 
				auxiliar1 += elemento.value.charAt(x) + ''
				tamanho1   = auxiliar1.length
		if (tamanho1 >= 0) {
			auxiliar2 = ''
			for (y = 0, x = tamanho1 - 3; x >= 0; x --) {
			    auxiliar2 += auxiliar1.charAt(x)
			    y ++
	    }
       	elemento.value = ''
		tamanho2 = auxiliar2.length
		for (x   = tamanho2 - 1; x >= 0; x --)
			elemento.value += auxiliar2.charAt(x)
			elemento.value += auxiliar1.substr(tamanho1 - 2, tamanho1)
		}
	
	for (x = 0; x < elemento.value.length; x ++) {
		if(elemento.value.charAt(x) == '.' && (x != 2 && x != 6)) {
			elemento.value = elemento.value.substr(0, x)
		}
	}
	for (x = 0; x < elemento.value.length; x ++) {
		if(elemento.value.charAt(x) == '/' && (x != 10)) {
			elemento.value = elemento.value.substr(0, x)
		}
	}
	for (x = 0; x < elemento.value.length; x ++) {
		if(elemento.value.charAt(x) == '-' && x != 15) {
			elemento.value = elemento.value.substr(0, x)
		}
	}
	
	if(elemento.value.length == 2 || elemento.value.length == 6 || elemento.value.length == 10 || elemento.value.length == 15) {
		if(elemento.value.length == 2 || elemento.value.length == 6) {
			elemento.value = elemento.value + '.'
		}else if(elemento.value.length == 10) {
			elemento.value = elemento.value + '/'		
		}else {
			elemento.value = elemento.value + '-'
		}
	}
	if(elemento.value.length == 3) {
		if(elemento.value.charAt(2) != '.') {
			ultimo_caracter = elemento.value.substr(2, 1)
			elemento.value = elemento.value.substr(0, 2) + '.' + ultimo_caracter
		}
	}
	if(elemento.value.length == 4) {
		if(elemento.value.charAt(2) != '.') {
			ultimo_caracter = elemento.value.substr(3, 1)
			elemento.value = elemento.value.substr(0, 2) + '.' + ultimo_caracter
		}
	}
	if(elemento.value.length == 5) {
		if(elemento.value.charAt(2) != '.') {
			ultimo_caracter = elemento.value.substr(3, 2)
			elemento.value = elemento.value.substr(0, 2) + '.' + ultimo_caracter
		}
	}
	if(elemento.value.length == 7) {
		if(elemento.value.charAt(2) != '.') {
			ultimo_caracter = elemento.value.substr(3, 4)
			elemento.value = elemento.value.substr(0, 2) + '.' + ultimo_caracter
		}
	}
	if(elemento.value.length == 8) {
		if(elemento.value.charAt(2) != '.') {
			ultimo_caracter = elemento.value.substr(3, 5)
			elemento.value = elemento.value.substr(0, 2) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(6) != '.') {
			ultimo_caracter = elemento.value.substr(7, 1)
			elemento.value = elemento.value.substr(0, 6) + '.' + ultimo_caracter
		}
	}
	if(elemento.value.length == 9) {
		if(elemento.value.charAt(2) != '.') {
			ultimo_caracter = elemento.value.substr(3, 6)
			elemento.value = elemento.value.substr(0, 2) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(6) != '.') {
			ultimo_caracter = elemento.value.substr(7, 2)
			elemento.value = elemento.value.substr(0, 6) + '.' + ultimo_caracter
		}
	}
	if(elemento.value.length == 11) {
		if(elemento.value.charAt(2) != '.') {
			ultimo_caracter = elemento.value.substr(3, 8)
			elemento.value = elemento.value.substr(0, 2) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(6) != '.') {
			ultimo_caracter = elemento.value.substr(7, 4)
			elemento.value = elemento.value.substr(0, 6) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(10) != '/') {
			ultimo_caracter = elemento.value.substr(10, 1)
			elemento.value = elemento.value.substr(0, 10) + '/' + ultimo_caracter
		}
	}
	if(elemento.value.length == 12) {
		if(elemento.value.charAt(2) != '.') {
			ultimo_caracter = elemento.value.substr(3, 9)
			elemento.value = elemento.value.substr(0, 2) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(6) != '.') {
			ultimo_caracter = elemento.value.substr(7, 5)
			elemento.value = elemento.value.substr(0, 6) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(10) != '/') {
			ultimo_caracter = elemento.value.substr(11, 1)
			elemento.value = elemento.value.substr(0, 10) + '/' + ultimo_caracter
		}
	}
	if(elemento.value.length == 12) {
		if(elemento.value.charAt(2) != '.') {
			ultimo_caracter = elemento.value.substr(3, 10)
			elemento.value = elemento.value.substr(0, 2) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(6) != '.') {
			ultimo_caracter = elemento.value.substr(7, 6)
			elemento.value = elemento.value.substr(0, 6) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(10) != '/') {
			ultimo_caracter = elemento.value.substr(11, 2)
			elemento.value = elemento.value.substr(0, 10) + '/' + ultimo_caracter
		}
	}
	if(elemento.value.length == 13) {
		if(elemento.value.charAt(2) != '.') {
			ultimo_caracter = elemento.value.substr(3, 11)
			elemento.value = elemento.value.substr(0, 2) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(6) != '.') {
			ultimo_caracter = elemento.value.substr(7, 7)
			elemento.value = elemento.value.substr(0, 6) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(10) != '/') {
			ultimo_caracter = elemento.value.substr(11, 3)
			elemento.value = elemento.value.substr(0, 10) + '/' + ultimo_caracter
		}
	}
	if(elemento.value.length == 14) {
		if(elemento.value.charAt(2) != '.') {
			ultimo_caracter = elemento.value.substr(3, 12)
			elemento.value = elemento.value.substr(0, 2) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(6) != '.') {
			ultimo_caracter = elemento.value.substr(7, 8)
			elemento.value = elemento.value.substr(0, 6) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(10) != '/') {
			ultimo_caracter = elemento.value.substr(11, 4)
			elemento.value = elemento.value.substr(0, 10) + '/' + ultimo_caracter
		}
	}
	if(elemento.value.length == 16) {
		if(elemento.value.charAt(2) != '.') {
			ultimo_caracter = elemento.value.substr(3, 14)
			elemento.value = elemento.value.substr(0, 2) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(6) != '.') {
			ultimo_caracter = elemento.value.substr(7, 10)
			elemento.value = elemento.value.substr(0, 6) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(10) != '/') {
			ultimo_caracter = elemento.value.substr(11, 6)
			elemento.value = elemento.value.substr(0, 10) + '/' + ultimo_caracter
		}
		if(elemento.value.charAt(15) != '-') {
			ultimo_caracter = elemento.value.substr(15, 1)
			elemento.value = elemento.value.substr(0, 15) + '-' + ultimo_caracter
		}
	}
	if(elemento.value.length == 17) {
		if(elemento.value.charAt(2) != '.') {
			ultimo_caracter = elemento.value.substr(3, 15)
			elemento.value = elemento.value.substr(0, 2) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(6) != '.') {
			ultimo_caracter = elemento.value.substr(7, 11)
			elemento.value = elemento.value.substr(0, 6) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(10) != '/') {
			ultimo_caracter = elemento.value.substr(11, 7)
			elemento.value = elemento.value.substr(0, 10) + '/' + ultimo_caracter
		}
		if(elemento.value.charAt(15) != '-') {
			ultimo_caracter = elemento.value.substr(16, 1)
			elemento.value = elemento.value.substr(0, 15) + '-' + ultimo_caracter
		}
	}
	if(elemento.value.length > 18) {
		elemento.value = elemento.value.substr(0, 18)
	}
	if(elemento.value.length == 18) {
		if(elemento.value.charAt(2) != '.') {
			ultimo_caracter = elemento.value.substr(3, 16)
			elemento.value = elemento.value.substr(0, 2) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(6) != '.') {
			ultimo_caracter = elemento.value.substr(7, 12)
			elemento.value = elemento.value.substr(0, 6) + '.' + ultimo_caracter
		}
		if(elemento.value.charAt(10) != '/') {
			ultimo_caracter = elemento.value.substr(11, 8)
			elemento.value = elemento.value.substr(0, 10) + '/' + ultimo_caracter
		}
		if(elemento.value.charAt(15) != '-') {
			ultimo_caracter = elemento.value.substr(16, 2)
			elemento.value = elemento.value.substr(0, 15) + '-' + ultimo_caracter
		}
		var valor = elemento.value
		valor     = valor.replace('.', '')
		valor     = valor.replace('.', '')
		valor     = valor.replace('/', '')
		valor     = valor.replace('-', '')	
		
		conta  			 =  valor.substr(0, 12)
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
			return false
		}
	}
	return true	
}

function digito_valido(elemento, validos, event, funcao) {
	var x, y, tamanho1 = elemento.value.length, tamanho2, auxiliar1, auxiliar2	
	for (x = 0; x < tamanho1; x ++)
		break
		auxiliar1 = ''
		for (; x < tamanho1; x ++)
			if (validos.indexOf(elemento.value.charAt(x)) != -1) 
				auxiliar1 += elemento.value.charAt(x) + ''
				tamanho1   = auxiliar1.length
		if (tamanho1 >= 0) {
			auxiliar2 = ''
			for (y = 0, x = tamanho1 - 3; x >= 0; x --) {
			    auxiliar2 += auxiliar1.charAt(x)
			    y ++
	    	}
       		elemento.value = ''
			tamanho2 = auxiliar2.length
			for (x   = tamanho2 - 1; x >= 0; x --)
				elemento.value += auxiliar2.charAt(x)
				elemento.value += auxiliar1.substr(tamanho1 - 2, tamanho1)
		}
	chamar_funcao = eval(funcao)
	return chamar_funcao(elemento, event)
}