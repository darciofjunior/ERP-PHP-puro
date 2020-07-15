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

function checkbox(form, campo, indice, cor_origem) {
	var campo_sel 	= eval('document.'+form+'.'+campo+'')
	var elementos 	= eval('document.'+form+'.elements')
	cor_origem 		= cor_origem.toLowerCase()
	linha 			= eval(indice) + 3

	if (!(document.getElementById('chkt_pedido_venda_item'+indice).checked)) {
		document.getElementById('chkt_pedido_venda_item'+indice).checked = true
		//Aqui joga o Designer de Habilitado ...
		document.getElementById('txt_num_corrida'+indice).className	= 'caixadetexto'
		document.getElementById('txt_tolerancia'+indice).className	= 'caixadetexto'
		//Habilita os objetos ...
		document.getElementById('txt_num_corrida'+indice).disabled 	= false
		document.getElementById('txt_tolerancia'+indice).disabled 	= false
	}else {
		document.getElementById('chkt_pedido_venda_item'+indice).checked = false
		//Aqui joga o Designer de Desabilitado ...
		document.getElementById('txt_num_corrida'+indice).className	= 'textdisabled'
		document.getElementById('txt_tolerancia'+indice).className	= 'textdisabled'
		//Habilita os objetos ...
		document.getElementById('txt_num_corrida'+indice).disabled 	= true
		document.getElementById('txt_tolerancia'+indice).disabled 	= true
	}
	
	if (document.getElementById('chkt_pedido_venda_item'+indice).checked) {
		if(navigator.appName == 'Netscape') {
			totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)';
		}else {
			totallinhas.rows[linha].style.backgroundColor = '#c6e2ff';
		}
	}else {
		totallinhas.rows[linha].style.backgroundColor = cor_origem;
	}
	
//Aqui é um controle para poder selecionar o checkbox principal
	var total_itens_certificados = 0
	var total_itens_certificados_marcados = 0
	
	for(i = 0; i < elementos.length; i++) {
		if(elementos[i].name == 'chkt_pedido_venda_item[]') {
			if(elementos[i].checked == true) {//Somente para certificados q estão checados
				total_itens_certificados_marcados++
			}
			total_itens_certificados++
		}
	}
//Significa que todos os itens de Certificados estão marcados ...
	document.form.chkt.checked = (total_itens_certificados == total_itens_certificados_marcados) ? true : false
}

function selecionar(form, campo, totallinhas, cor_origem) {
	var campo_sel 	= eval('document.'+form+'.'+campo+'')
	var elementos 	= eval('document.'+form+'.elements')
	cor_origem 		= cor_origem.toLowerCase()
	//Prepara a Tela p/ poder gravar no BD ...
	if(typeof(elementos['chkt_pedido_venda_item[]'][0]) == 'undefined') {
		var linhas = 1//Existe apenas 1 único elemento ...
	}else {
		var linhas = (elementos['chkt_pedido_venda_item[]'].length)
	}

	for (var i = 0; i < linhas; i++) {
		if (!(document.getElementById('chkt_pedido_venda_item'+i).checked)) {
			document.getElementById('chkt_pedido_venda_item'+i).checked = true
			//Aqui joga o Designer de Habilitado ...
			document.getElementById('txt_num_corrida'+i).className	= 'caixadetexto'
			document.getElementById('txt_tolerancia'+i).className	= 'caixadetexto'
			//Habilita os objetos ...
			document.getElementById('txt_num_corrida'+i).disabled 	= false
			document.getElementById('txt_tolerancia'+i).disabled 	= false
		}else {
			document.getElementById('chkt_pedido_venda_item'+i).checked = false
			//Aqui joga o Designer de Desabilitado ...
			document.getElementById('txt_num_corrida'+i).className	= 'textdisabled'
			document.getElementById('txt_tolerancia'+i).className	= 'textdisabled'
			//Habilita os objetos ...
			document.getElementById('txt_num_corrida'+i).disabled 	= true
			document.getElementById('txt_tolerancia'+i).disabled 	= true
		}
	}
	if (campo_sel.checked) {
		if(navigator.appName == 'Netscape') {
			for (i = 3; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)';
		}else {
			for (i = 3; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
		}
	}else {
		for (i = 3; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = cor_origem;
	}
}