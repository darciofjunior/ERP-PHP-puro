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

function checkbox(indice, cor_origem) {
	cor_origem = cor_origem.toLowerCase()
	if(document.getElementById('chkt_item_pedido'+indice).checked == true) {
		document.getElementById('chkt_item_pedido'+indice).checked = false
		document.getElementById('txt_quantidade'+indice).disabled = true//Desabilita ...
		document.getElementById('txt_quantidade'+indice).className = 'textdisabled'//Joga o Designer de Desabilitado ...
	}else {
		document.getElementById('chkt_item_pedido'+indice).checked = true
		document.getElementById('txt_quantidade'+indice).disabled = false//Habilita ...
		document.getElementById('txt_quantidade'+indice).className = 'caixadetexto'//Joga o Designer de Habilitado ...
	}
	var indice_linha = eval(indice) + 3
	if(document.getElementById('chkt_item_pedido'+indice).checked == true) {
		if(navigator.appName == 'Netscape') {
			totallinhas.rows[indice_linha].style.backgroundColor = 'rgb(198,226,255)'
		}else {
			totallinhas.rows[indice_linha].style.backgroundColor = '#c6e2ff'
		}
	}else {
		totallinhas.rows[indice_linha].style.backgroundColor = cor_origem;
	}
//Aqui é um controle para poder selecionar o checkbox principal
	var total_itens_pedidos = 0
	var total_itens_pedidos_marcados = 0
	var elementos = document.form.elements
	
	for(i = 0; i < elementos.length; i++) {
		if(elementos[i].name == 'chkt_item_pedido[]' && elementos[i].type == 'checkbox') {
			//Somente para pedidos q estão checados
			if(elementos[i].checked == true) total_itens_pedidos_marcados++
			total_itens_pedidos++
		}
	}
//Significa que todos os itens de Cotação estão marcados ...
	document.form.chkt_tudo.checked = (total_itens_pedidos == total_itens_pedidos_marcados) ? true : false
	calcular_total_geral(indice)
}

function selecionar(totallinhas, cor_origem) {
	var elementos = document.form.elements
	var linhas = (typeof(elementos['chkt_item_pedido[]'][0]) == 'undefined') ? 1 : elementos['chkt_item_pedido[]'].length
	cor_origem = cor_origem.toLowerCase()
	
	if(document.getElementById('chkt_tudo').checked == true) {//Checkbox Principal está marcado ...
		for(var i = 0; i < linhas; i++) {
			document.getElementById('chkt_item_pedido'+i).checked = true
			document.getElementById('txt_quantidade'+i).disabled = false
			//Aqui joga o Designer de Habilitado
			document.getElementById('txt_quantidade'+i).className = 'caixadetexto'
		}
	}else {//Checkbox Principal está desmarcado ...
		for(var i = 0; i < linhas; i++) {
			document.getElementById('chkt_item_pedido'+i).checked = false
			document.getElementById('txt_quantidade'+i).disabled = true
			//Aqui joga o Designer de Desabilitado
			document.getElementById('txt_quantidade'+i).className = 'textdisabled'
		}
	}

	if(document.getElementById('chkt_tudo').checked == true) {//Checkbox Principal está marcado ...
		if(navigator.appName == 'Netscape') {
	        for (i = 3; i < (totallinhas.rows.length - 1); i++) {
				totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
	        }
		}else {
			for (i = 3; i < (totallinhas.rows.length - 1); i++) {
				totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
			}
		}
	}else {
		for (i = 3; i < (totallinhas.rows.length - 1); i++) {
			totallinhas.rows[i].style.backgroundColor = cor_origem
		}
	}
}

function calcular_total_geral(indice) {
	var quantidade 		= (document.getElementById('txt_quantidade'+indice).value != '') ? eval(strtofloat(document.getElementById('txt_quantidade'+indice).value)) : 0
	var preco_unitario 	= eval(strtofloat(document.getElementById('txt_preco_unitario'+indice).value))
	document.getElementById('txt_valor_total'+indice).value = quantidade * preco_unitario
	document.getElementById('txt_valor_total'+indice).value = arred(document.getElementById('txt_valor_total'+indice).value, 2, 1)
	
	//document.form.txt_total_geral.value = valor_total
	//document.form.txt_total_geral.value = arred(document.form.txt_total_geral.value, 2, 1)
}