/*Esse JS foi feito exclusivo para esse caso, devido eu ter uma caixa de texto para cada linha, sendo assim
fica um pouco diferente o controle de clicks e cores da linha*/
function cor_clique_celula(cel_atual, backgroundColor) {
	var cor_origem = cel_atual.bgColor
	var cor_atual = cel_atual.style.backgroundColor
	var nova_cor = backgroundColor
	nova_cor = nova_cor.toLowerCase()
//Habilita ou Desabilita o Checkbox e Objeto Radio da Linha quando se dá um clique aonde o cursor do mouse esta posicionado
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

function focos(objeto) {
	objeto.disabled = false
	objeto.focus()
	return false
}

function checkbox(form, campo, valor, cor_origem) {
	var campo_sel = eval('document.'+form+'.'+campo+'')
	var elements = eval('document.'+form+'.elements')
	cor_origem = cor_origem.toLowerCase()
	linha = eval(valor) + 3
/*Significa que o usuário está clicando da segunda linha em diante, aqui se realiza
esse macete porque se tem 2 objetos por linha contando o checkbox também*/
	if(valor != 0) {
		var cont = valor * 2
	}else {
		var cont = valor
	}
//Aqui se adiciona mais um para o cont para pular o primeiro checkbox principal
	cont ++
	if (!(elements[cont].checked)) {
		elements[cont].checked = true
	}else {
		elements[cont].checked = false
	}
	if (elements[cont].checked) {
		if(navigator.appName == 'Netscape') {
			totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)'
		}else {
			totallinhas.rows[linha].style.backgroundColor = '#c6e2ff'
		}
	}else {
		totallinhas.rows[linha].style.backgroundColor = cor_origem
	}
	
//Aqui é um controle para poder selecionar o checkbox principal
	var total_itens_cheques = 0
	var total_itens_cheques_marcados = 0
	
	for(i = 0; i < elements.length; i++) {
		if(elements[i].name == 'chkt_cheque_cliente[]') {
			if(elements[i].checked == true) {//Somente para cheques q estão checados
				total_itens_cheques_marcados++
			}
			total_itens_cheques++
		}
	}
//Significa que todos os itens de Cheque estão marcados
	if(total_itens_cheques == total_itens_cheques_marcados) {
		document.form.chkt.checked = true
	}else {
		document.form.chkt.checked = false
	}
}

function selecionar(form, campo, totallinhas, cor_origem) {
	var campo_sel = eval('document.'+form+'.'+campo+'')
	var elements = eval('document.'+form+'.elements')
	cor_origem = cor_origem.toLowerCase()
	var objetos_inicio = 1//Ignorando a combo e a caixa de texto de data e os hiddens, antes do loop
	var objetos_fim = 2//ignorando a partir do objeto textarea até o final de elementos

	for (i = objetos_inicio; i < (elements.length) - objetos_fim; i ++) {
		if (elements[i].type == 'checkbox') {
			if (campo_sel.checked) {//Aqui são os checkbox
				elements[i].checked = true
			}else {
				elements[i].checked = false//Aqui são os checkbox
			}
		}
	}
	
	if (campo_sel.checked) {
		if(navigator.appName == 'Netscape') {
		        for (i = 3; i < (totallinhas.rows.length - 1); i ++) {
				totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
		        }
		}else {
			for (i = 3; i < (totallinhas.rows.length - 1); i ++) {
				totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
			}
		}
	}else {
		for (i = 3; i < (totallinhas.rows.length - 1); i ++) {
			totallinhas.rows[i].style.backgroundColor = cor_origem
		}
	}
}