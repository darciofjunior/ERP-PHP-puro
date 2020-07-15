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

function checkbox(form, campo, valor, cor_origem) {
	var campo_sel = eval('document.'+form+'.'+campo+'')
	var elements = eval('document.'+form+'.elements')
	var i, valor_aux, resultado = true
	valor_aux = valor
	linha = eval(valor) + 3
	var objetos_linha = 7
	if(valor_aux == 0) {
		valor++;
//Habilita as Caixas
		elements[valor + 2].disabled = false
		elements[valor + 3].disabled = false
		elements[valor + 4].disabled = false
//Muda a Cor das Caixas para Habilitado
		elements[valor + 2].style.color = '#000000'
		elements[valor + 2].style.background = '#FFFFFF'
		elements[valor + 3].style.color = '#000000'
		elements[valor + 3].style.background = '#FFFFFF'
		elements[valor + 4].style.color = '#000000'
		elements[valor + 4].style.background = '#FFFFFF'
	}
	if(valor_aux == 1) {
		valor = eval(valor) + objetos_linha
//Habilita as Caixas
		elements[valor + 2].disabled = false
		elements[valor + 3].disabled = false
		elements[valor + 4].disabled = false
//Muda a Cor das Caixas para Habilitado
		elements[valor + 2].style.color = '#000000'
		elements[valor + 2].style.background = '#FFFFFF'
		elements[valor + 3].style.color = '#000000'
		elements[valor + 3].style.background = '#FFFFFF'
		elements[valor + 4].style.color = '#000000'
		elements[valor + 4].style.background = '#FFFFFF'
	}
	if(valor_aux > 1) {
		valor = valor * objetos_linha
		valor ++
//Habilita as Caixas
		elements[valor + 2].disabled = false
		elements[valor + 3].disabled = false
		elements[valor + 4].disabled = false
//Muda a Cor das Caixas para Habilitado
		elements[valor + 2].style.color = '#000000'
		elements[valor + 2].style.background = '#FFFFFF'
		elements[valor + 3].style.color = '#000000'
		elements[valor + 3].style.background = '#FFFFFF'
		elements[valor + 4].style.color = '#000000'
		elements[valor + 4].style.background = '#FFFFFF'
	}
	cor_origem = cor_origem.toLowerCase()
	if (elements[valor].type == 'checkbox') {
		if(elements[valor].checked) {
			campo_sel.checked = false
//Desabilita as Caixas
			elements[valor + 2].disabled = true
			elements[valor + 3].disabled = true
			elements[valor + 4].disabled = true
//Muda a Cor das Caixas para Habilitado
			elements[valor + 2].style.color = 'gray'
			elements[valor + 2].style.background = '#FFFFE1'
			elements[valor + 3].style.color = 'gray'
			elements[valor + 3].style.background = '#FFFFE1'
			elements[valor + 4].style.color = 'gray'
			elements[valor + 4].style.background = '#FFFFE1'
			elements[valor].checked = false
		}else {
			elements[valor].checked = true
			for (i = 0; i < elements.length;i++) {
				if (elements[i].type == 'checkbox' && elements[i].name != campo) {
					if (elements[i].checked == false) {
						resultado = false
					}
				}
			}
			campo_sel.checked = resultado
		}
	}
	if (elements[valor].checked) {
		if(navigator.appName == 'Netscape') {
			totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)';
		}else {
			totallinhas.rows[linha].style.backgroundColor = '#c6e2ff';
		}
	}else {
		totallinhas.rows[linha].style.backgroundColor = cor_origem;
	}
}

function selecionar(form, campo, totallinhas, cor_origem) {
	var campo_sel = eval('document.'+form+'.'+campo+'')
	var elements = eval('document.'+form+'.elements')
	cor_origem = cor_origem.toLowerCase()
	for (i = 1; i < elements.length; i ++) {
		if (elements[i].type == 'checkbox') {
			if (campo_sel.checked) {//Aqui são os checkbox
				elements[i].checked = true
				elements[i + 2].disabled = false//Aqui são os texts
				elements[i + 3].disabled = false//Combo
				elements[i + 4].disabled = false//Aqui são os texts
//Muda a Cor das Caixas para Habilitado
				elements[i + 2].style.color = '#000000'
				elements[i + 2].style.background = '#FFFFFF'
				elements[i + 3].style.color = '#000000'
				elements[i + 3].style.background = '#FFFFFF'
				elements[i + 4].style.color = '#000000'
				elements[i + 4].style.background = '#FFFFFF'
			}else {
				elements[i].checked = false//Aqui são os checkbox
				elements[i + 2].disabled = true//Aqui são os texts
				elements[i + 3].disabled = true//Combo
				elements[i + 4].disabled = true//Aqui são os texts
//Muda a Cor das Caixas para Desabilitado
				elements[i + 2].style.color = 'gray'
				elements[i + 2].style.background = '#FFFFE1'
				elements[i + 3].style.color = 'gray'
				elements[i + 3].style.background = '#FFFFE1'
				elements[i + 4].style.color = 'gray'
				elements[i + 4].style.background = '#FFFFE1'
			}
		}
	}

	if (campo_sel.checked) {
		if(navigator.appName == 'Netscape') {
			for (i = 3; i < (totallinhas.rows.length - 1); i ++) {
				totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)';
			}
		}else {
			for (i = 3; i < (totallinhas.rows.length - 1); i ++) {
				totallinhas.rows[i].style.backgroundColor = '#c6e2ff';
			}
		}
		elements[2].focus()
	}else {
		for (i = 3; i < (totallinhas.rows.length - 1); i ++) {
			totallinhas.rows[i].style.backgroundColor = cor_origem;
		}
	}
}

function focos(objeto) {
	objeto.disabled = false
	objeto.focus()
	return false
}