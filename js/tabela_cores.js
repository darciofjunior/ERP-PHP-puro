var redefinir_tabela = '', controle_checkbox, controle_option, celula_option, objeto
var cor_original1 = '#e8e8e8', cor_original2 = '#f1f1f1', cor_troca = '#c3d6e6', cor_mouse = '#c4d6e6'

function sobre_celula(celula) {
celula.style.cursor = 'hand'
	if (celula.style.backgroundColor == 'rgb(232, 232, 232)' || celula.style.backgroundColor == 'rgb(241, 241, 241)' || celula.style.backgroundColor == cor_original1 || celula.style.backgroundColor == cor_original2 || celula.style.backgroundColor == '') { 
		celula.style.backgroundColor = cor_mouse
	}
}

function fora_celula(celula) {
celula.style.cursor = 'hand'
	if ((celula.style.backgroundColor == 'rgb(196, 214, 230)') || celula.style.backgroundColor == cor_mouse) {
		celula.style.backgroundColor = ''
	}
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

function tabela_option(tabela, formulario, campo, inicio) {
var x, y = 0, selecionar = eval('document.'+formulario+'.'+campo+'')  
	if (controle_option == 0) {
		for (x = inicio; x < tabela.rows.length - 1; x ++) {					
			if (tabela.rows[x].bgColor == cor_troca || tabela.rows[x].bgColor == cor_original1 || tabela.rows[x].bgColor == cor_original2 || tabela.rows[x].bgColor == '') {
				if (y % 2 == 0) {
					tabela.rows[x].style.backgroundColor = cor_original1
					tabela.rows[x].bgColor = cor_original1
				}else {
					tabela.rows[x].style.backgroundColor = cor_original2
					tabela.rows[x].bgColor = cor_original2
				}
				y ++
			}
		}	
		celula_option.style.backgroundColor = cor_troca
		celula_option.bgColor = cor_troca 
		if (selecionar.length >= 1) {
			selecionar[objeto].checked = true			
		}else {
			selecionar.checked = true
		}
	}
	redefinir_tabela = tabela		
}

function option_cor(celula, numero) {
	celula_option   = celula
	objeto          = numero
	controle_option = 0	
}

function validar_checkbox(formulario, mensagem) {
var x, valor = false, elementos = eval('document.'+formulario+'.elements')
	for (x = 0; x < elementos.length; x ++)   {
		if (elementos[x].type == 'checkbox')  {
			if (elementos[x].checked == true) {
					valor = true
			}		
		}
	}
	if (valor == false) {
		window.alert(mensagem)		
		return false
	}
return true
}

function selecionar_todos_checkbox(formulario, campo) {
var x, valor      = true, elementos = eval('document.'+formulario+'.elements')  
var selecionar    = eval('document.'+formulario+'.'+campo+'')  
controle_checkbox = 0
		if (selecionar.checked == false) {
			valor = false			
		}
		for (x = 0; x < elementos.length; x ++)   {		
			if (elementos[x].type == 'checkbox')  {
				elementos[x].checked = eval(valor)													
			}
		}	
}

function tabela_checkbox(tabela, formulario, campo, inicio) {
var x, y = 0, auxiliar1, auxiliar2, selecionar  = eval('document.'+formulario+'.'+campo+'')  
	if (controle_checkbox == 0) {
		if (selecionar.checked == true) {		
			for (x = inicio; x < tabela.rows.length - 1; x ++) {			
				if (tabela.rows[x].bgColor == cor_original1 || tabela.rows[x].bgColor == cor_original2 || tabela.rows[x].bgColor == '') {
					tabela.rows[x].style.backgroundColor = cor_troca
					tabela.rows[x].bgColor 				 = cor_troca
				}
			}
		}else {
			for (x = inicio; x < tabela.rows.length - 1; x ++) {			
				if (tabela.rows[x].bgColor == cor_troca || tabela.rows[x].bgColor == '') {		
					if (y %  2 == 0) {
						tabela.rows[x].style.backgroundColor = cor_original1
						tabela.rows[x].bgColor = cor_original1
					}else {
						tabela.rows[x].style.backgroundColor = cor_original2
						tabela.rows[x].bgColor = cor_original2
					}
					y ++
				}
			}			
		}
	}
	redefinir_tabela = tabela
}

function selecionar_checkbox_cor(celula, numero_celula, formulario, campo, numero_objeto) {
	var x, valor   = true, elementos = eval('document.'+formulario+'.elements')  
	var selecionar = eval('document.'+formulario+'.'+campo+'')  
		for (x = 0; x < elementos.length; x ++) {
			if (elementos[x].type == 'checkbox' && elementos[x].name != campo) {
				if (elementos[x].checked == false) {														
					valor = false
				}
			}			
		}		
		if (celula.bgColor == cor_original1 || celula.bgColor == cor_original2 || celula.bgColor == '') {
			celula.style.backgroundColor = cor_troca
			celula.bgColor			     = cor_troca
			elementos[numero_objeto].checked    = true
		}else {
			if (numero_celula % 2 == 0) {
				celula.style.backgroundColor = cor_original1
				celula.bgColor 				 = cor_original1
			}else {
				celula.style.backgroundColor = cor_original2
				celula.bgColor 				 = cor_original2
			}		
			elementos[numero_objeto].checked = false
		}	
		
		if (valor == false) {
			controle_checkbox = 1
		}else {			
			controle_checkbox = 0
		}						
		selecionar.checked = eval(valor)
		return auxiliar_checkbox(formulario, campo)
}

function auxiliar_checkbox(formulario, campo) {
var x, valor   = true, elementos = eval('document.'+formulario+'.elements')  
var selecionar = eval('document.'+formulario+'.'+campo+'')  
		for (x = 0; x < elementos.length; x ++) {
			if (elementos[x].type == 'checkbox' && elementos[x].name != campo) {
				if (elementos[x].checked == false) {														
					valor = false
				}
			}
		}			
		if (valor == false) {
			controle_checkbox = 1
		}else {			
			controle_checkbox = 0
		}	
selecionar.checked = eval(valor)
}

function redefinir_tabela_formulario(inicio) {
var x, y = 0
	if (redefinir_tabela != '') {
		for (x = inicio; x < redefinir_tabela.rows.length - 1; x ++) {		
			if (redefinir_tabela.rows[x].bgColor == cor_troca || redefinir_tabela.rows[x].bgColor == cor_original1 || redefinir_tabela.rows[x].bgColor == cor_original2 || redefinir_tabela.rows[x].bgColor == '') {
				if (y % 2 == 0) {
					redefinir_tabela.rows[x].style.backgroundColor = cor_original1
					redefinir_tabela.rows[x].bgColor 			   = cor_original1
				}else {
					redefinir_tabela.rows[x].style.backgroundColor = cor_original2
					redefinir_tabela.rows[x].bgColor 			   = cor_original2
				}
				y ++
			}
		}		
	}
	controle_checkbox = 1
	controle_option   = 1
}

/*
document.oncontextmenu =
function () {
	return false
}
if (document.layers) {
	window.captureEvents(event.mousedown)
	window.onmousedown =	
function (e){
	if (e.target == document)
		return false
	}}else {
    	document.onmousedown =
function (){
	return false
	}
}*/