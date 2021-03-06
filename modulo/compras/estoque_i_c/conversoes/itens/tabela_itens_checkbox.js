/*Esse JS foi feito exclusivo para esse caso, devido eu ter uma caixa de texto para cada linha, sendo assim
fica um pouco diferente o controle de clicks e cores da linha*/
function cor_clique_celula(cel_atual, backgroundColor) {
	var cor_origem = cel_atual.bgColor
	var cor_atual = cel_atual.style.backgroundColor
	var nova_cor = backgroundColor
	nova_cor = nova_cor.toLowerCase()
//Habilita ou Desabilita o Checkbox e Objeto Radio da Linha quando se d� um clique aonde o cursor do mouse esta posicionado
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

function focos(objeto) {
	objeto.disabled = false
	objeto.focus()
	return false
}

function checkbox(form, campo, valor, cor_origem) {
	var objetos_inicio = 1
	var objetos_linha = 2
	var objetos_fim = 2
	var campo_sel = eval('document.'+form+'.'+campo+'')
	var elements = eval('document.'+form+'.elements')
	cor_origem = cor_origem.toLowerCase()
	linha = eval(valor) + 3
/*Significa que o usu�rio est� clicando da segunda linha em diante, aqui se realiza
esse macete porque se tem 2 objetos por linha contando o checkbox tamb�m*/
	if(valor != 0) {
		var cont = valor * objetos_linha
	}else {
		var cont = valor
	}
//Aqui se adiciona mais um para o cont para pular o primeiro checkbox principal
	cont ++
	if (!(elements[cont].checked)) {
		elements[cont].checked = true
//Habilita os objetos
		elements[cont + 1].disabled = false
//Muda a cor para habilitado
		elements[cont + 1].style.color = 'Brown'
		elements[cont + 1].style.background = '#FFFFFF'
		elements[cont + 1].focus()
	}else {
		elements[cont].checked = false
//Desabilita os objetos
		elements[cont + 1].disabled = true
		elements[cont + 1].value = ''
//Muda a cor para desabilitado
		elements[cont + 1].style.color = 'gray'
		elements[cont + 1].style.background = '#FFFFE1'
	}
	if (elements[cont].checked) {
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
	var objetos_inicio = 1
	var objetos_fim = 2
	var campo_sel = eval('document.'+form+'.'+campo+'')
	var elements = eval('document.'+form+'.elements')
	cor_origem = cor_origem.toLowerCase()

	for (i = objetos_inicio; i < (elements.length) - objetos_fim; i++) {
		if (elements[i].type == 'checkbox') {
			if (campo_sel.checked) {//Aqui s�o os checkbox
				elements[i].checked = true
//Habilita os objetos
				elements[i + 1].disabled = false//Aqui s�o os texts
//Muda a cor para habilitado
				elements[i + 1].style.color = 'Brown'
				elements[i + 1].style.background = '#FFFFFF'
			}else {
				elements[i].checked = false//Aqui s�o os checkbox
//Desabilita os objetos
				elements[i + 1].disabled = true//Aqui s�o os texts
				elements[i + 1].value = ''
//Muda a cor para desabilitado
				elements[i + 1].style.color = 'gray'
				elements[i + 1].style.background = '#FFFFE1'
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
				totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)';
			}
		}
	}else {
		for (i = 3; i < (totallinhas.rows.length - 1); i ++) {
			totallinhas.rows[i].style.backgroundColor = cor_origem;
		}
	}
}