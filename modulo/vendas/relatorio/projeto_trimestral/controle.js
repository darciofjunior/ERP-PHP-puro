/*Esse JS foi feito exclusivo para esse caso, devido eu ter uma caixa de texto para cada linha, sendo assim
fica um pouco diferente o controle de clicks e cores da linha*/
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

function checkbox_incluir(indice, cor_origem) {
	cor_origem = cor_origem.toLowerCase()
	linha = eval(indice) + 2
/**********************************************/
//Se n�o estiver checado, ent�o fica marcado ...
	if(document.getElementById('chkt_projecao_trimestral'+indice).checked == false) {
		document.getElementById('chkt_projecao_trimestral'+indice).checked = true
	}else {//Se marcado, ent�o fica desmarcado ...
		document.getElementById('chkt_projecao_trimestral'+indice).checked = false
	}
/**********************************************/
	if (document.getElementById('chkt_projecao_trimestral'+indice).checked == true) {
		document.getElementById('txt_justificativa'+indice).disabled = false
//Aqui joga o Designer de Habilitado
		document.getElementById('txt_justificativa'+indice).style.color = 'Brown'
		document.getElementById('txt_justificativa'+indice).style.background = '#FFFFFF'
		document.getElementById('txt_justificativa'+indice).focus()
		document.getElementById('txt_justificativa'+indice).select()
	}else {
		document.getElementById('txt_justificativa'+indice).disabled = true
//Aqui joga o Designer de Desabilitado
		document.getElementById('txt_justificativa'+indice).style.color = 'gray'
		document.getElementById('txt_justificativa'+indice).style.background = '#FFFFE1'
	}
//Colore de azul a linha corrente ...
	if (document.getElementById('chkt_projecao_trimestral'+indice).checked) {
		if(navigator.appName == 'Netscape') {
			totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)';
		}else {
			totallinhas.rows[linha].style.backgroundColor = '#c6e2ff';
		}
	}else {
		totallinhas.rows[linha].style.backgroundColor = cor_origem;
	}
}

function selecionar_tudo_incluir(totallinhas, cor_origem) {
	var elements = document.form.elements
	var linhas = (typeof(elements['chkt_projecao_trimestral[]'][0]) == 'undefined') ? 1 : elements['chkt_projecao_trimestral[]'].length
	if(document.getElementById('chkt_tudo').checked == true) {//Checkbox Principal est� marcado ...
		for(var i = 0; i < linhas; i++) {
			document.getElementById('chkt_projecao_trimestral'+i).checked = true
			document.getElementById('txt_justificativa'+i).disabled = false
//Aqui joga o Designer de Habilitado
			document.getElementById('txt_justificativa'+i).style.color = 'Brown'
			document.getElementById('txt_justificativa'+i).style.background = '#FFFFFF'
		}
	}else {//Checkbox Principal est� desmarcado ...
		for(var i = 0; i < linhas; i++) {
			document.getElementById('chkt_projecao_trimestral'+i).checked = false
			document.getElementById('txt_justificativa'+i).disabled = true
//Aqui joga o Designer de Desabilitado
			document.getElementById('txt_justificativa'+i).style.color = 'gray'
			document.getElementById('txt_justificativa'+i).style.background = '#FFFFE1'
		}
	}

	if(document.getElementById('chkt_tudo').checked == true) {//Checkbox Principal est� marcado ...
		if(navigator.appName == 'Netscape') {
	        for (i = 2; i < (totallinhas.rows.length - 1); i++) {
				totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
	        }
		}else {
			for (i = 2; i < (totallinhas.rows.length - 1); i++) {
				totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
			}
		}
	}else {
		for (i = 2; i < (totallinhas.rows.length - 1); i++) {
			totallinhas.rows[i].style.backgroundColor = cor_origem
		}
	}
}