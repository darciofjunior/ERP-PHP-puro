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
	linha = eval(indice) + 1
/**********************************************/
//Se não estiver checado, então fica marcado ...
	if(document.getElementById('chkt_produto_acabado'+indice).checked == false) {
		document.getElementById('chkt_produto_acabado'+indice).checked = true
	}else {//Se marcado, então fica desmarcado ...
		document.getElementById('chkt_produto_acabado'+indice).checked = false
	}
/**********************************************/
	if (document.getElementById('chkt_produto_acabado'+indice).checked == true) {
		document.getElementById('cmd_nfs_saida'+indice).disabled 		= false
		document.getElementById('txt_quantidade'+indice).disabled 		= false
		document.getElementById('txt_defeito_alegado'+indice).disabled 	= false
//Aqui joga o Designer de Habilitado
		document.getElementById('cmd_nfs_saida'+indice).className 		= 'botao'
		document.getElementById('txt_quantidade'+indice).className 		= 'caixadetexto'
		document.getElementById('txt_defeito_alegado'+indice).className = 'caixadetexto'
	}else {
		document.getElementById('cmd_nfs_saida'+indice).disabled 		= true
		document.getElementById('txt_quantidade'+indice).disabled 		= true
		document.getElementById('txt_defeito_alegado'+indice).disabled 	= true
//Aqui joga o Designer de Desabilitado
		document.getElementById('cmd_nfs_saida'+indice).className 		= 'textdisabled'
		document.getElementById('txt_quantidade'+indice).className 		= 'textdisabled'
		document.getElementById('txt_defeito_alegado'+indice).className = 'textdisabled'
	}
//Colore de azul a linha corrente ...
	if (document.getElementById('chkt_produto_acabado'+indice).checked) {
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
//Aqui zera o contador para poder comparar com o valor do vetor
	var cont = 0
	var elements = document.form.elements
	var linhas = (typeof(elements['chkt_produto_acabado[]'][0]) == 'undefined') ? 1 : elements['chkt_produto_acabado[]'].length
	cor_origem = cor_origem.toLowerCase()
	
	if(document.getElementById('chkt_tudo').checked == true) {//Checkbox Principal está marcado ...
		for(var i = 0; i < linhas; i++) {
			document.getElementById('chkt_produto_acabado'+i).checked 	= true
			document.getElementById('cmd_nfs_saida'+indice).disabled 	= false
			document.getElementById('txt_quantidade'+i).disabled 		= false
			document.getElementById('txt_defeito_alegado'+i).disabled 	= false
//Aqui joga o Designer de Habilitado
			document.getElementById('cmd_nfs_saida'+indice).className 	= 'botao'
			document.getElementById('txt_quantidade'+i).className 		= 'caixadetexto'
			document.getElementById('txt_defeito_alegado'+i).className 	= 'caixadetexto'
		}
	}else {//Checkbox Principal está desmarcado ...
		for(var i = 0; i < linhas; i++) {
			document.getElementById('chkt_produto_acabado'+i).checked = false
			document.getElementById('cmd_nfs_saida'+indice).disabled 	= true
			document.getElementById('txt_quantidade'+i).disabled 		= true
			document.getElementById('txt_defeito_alegado'+i).disabled 	= true
//Aqui joga o Designer de Desabilitado
			document.getElementById('cmd_nfs_saida'+indice).className 	= 'textdisabled'
			document.getElementById('txt_quantidade'+i).className 		= 'textdisabled'
			document.getElementById('txt_defeito_alegado'+i).className 	= 'textdisabled'
		}
	}

	if(document.getElementById('chkt_tudo').checked == true) {//Checkbox Principal está marcado ...
		if(navigator.appName == 'Netscape') {
	        for (i = 1; i < (totallinhas.rows.length - 2); i++) {
				totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
	        }
		}else {
			for (i = 1; i < (totallinhas.rows.length - 2); i++) {
				totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
			}
		}
	}else {
		for (i = 1; i < (totallinhas.rows.length - 2); i++) {
			totallinhas.rows[i].style.backgroundColor = cor_origem
		}
	}
}