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
	var campo_sel = eval('document.'+form+'.'+campo+'')
	var elements = eval('document.'+form+'.elements')
	cor_origem = cor_origem.toLowerCase()
	linha = eval(valor) + 3
/*Significa que o usuário está clicando da segunda linha em diante, aqui se realiza
esse macete porque se tem 2 objetos por linha contando o checkbox também*/
	var cont = valor * 2
	if (!(elements[cont].checked)) {
		elements[cont].checked = true
		elements[cont + 1].disabled = false
		elements[cont + 1].style.color = 'Brown'
		elements[cont + 1].style.background = '#FFFFFF'
		elements[cont + 1].focus()
	}else {
		elements[cont].checked = false
		elements[cont + 1].disabled = true
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
	
/*Se um desses 3 abaixo estiver selecionado, então habilito o Botão p/ a Impressão do Comunicado ...
- Valor do IPI R$: 
- Valor do ICMS R$: 
- Valor do ICMS ST R$:*/
	var elementos = document.form.elements, habilitar_botao_comunicado = 0
	for (i = 0; i < elementos.length; i ++) {
		if (elementos[i].checked == true && elementos[i].type == 'checkbox') {
			if(elementos[i].value == 18 || elementos[i].value == 23 || elementos[i].value == 43 && elementos[i + 1].value != '') {
				habilitar_botao_comunicado = 1
			}
		}
	}
//Só habilitará esse botão em um dos 3 casos acima ...
	if(habilitar_botao_comunicado == 1) {
		document.form.cmd_comunicado.disabled = false
		document.form.cmd_comunicado.className = 'botao'
	}else {
		document.form.cmd_comunicado.disabled = true
		document.form.cmd_comunicado.className = 'textdisabled'
	}
}