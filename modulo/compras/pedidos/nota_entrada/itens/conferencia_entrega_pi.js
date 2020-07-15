function cor_clique_celula(cel_atual, backgroundColor) {
	var cor_origem = cel_atual.bgColor
	var cor_atual = cel_atual.style.backgroundColor
	var nova_cor = backgroundColor
	nova_cor = nova_cor.toLowerCase()
//Habilita ou Desabilita o Checkbox e Objeto Radio da Linha quando se dá um clique aonde o cursor do mouse esta posicionado
	if((cor_atual == 'rgb(198,226,255)') || (cor_atual == nova_cor)) {
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
	var linha 		= eval(indice) + 3
	var resultado 	= true
	var cor_origem 	= cor_origem.toLowerCase()
	if(document.getElementById('chkt_nfe_historico'+indice).checked) {
		campo_sel.checked = false
//Desabilita as Caixas ...
		document.getElementById('txt_medida1_mm'+indice).disabled 		= true
		document.getElementById('txt_medida2_mm'+indice).disabled 		= true
		document.getElementById('txt_qtde_metros'+indice).disabled 		= true
		document.getElementById('txt_num_corrida'+indice).disabled 		= true
//Aqui joga o Designer de Desabilitado ...
		document.getElementById('txt_medida1_mm'+indice).className 		= 'textdisabled'
		document.getElementById('txt_medida2_mm'+indice).className 		= 'textdisabled'
		document.getElementById('txt_qtde_metros'+indice).className 	= 'textdisabled'
		document.getElementById('txt_num_corrida'+indice).className 	= 'textdisabled'
		document.getElementById('chkt_nfe_historico'+indice).checked 	= false
	}else {
//Habilita as Caixas ...
		document.getElementById('txt_medida1_mm'+indice).disabled 		= false
		document.getElementById('txt_medida2_mm'+indice).disabled 		= false
		document.getElementById('txt_qtde_metros'+indice).disabled 		= false
		document.getElementById('txt_num_corrida'+indice).disabled 		= false
//Aqui joga o Designer de Habilitado ...
		document.getElementById('txt_medida1_mm'+indice).className 		= 'caixadetexto'
		document.getElementById('txt_medida2_mm'+indice).className 		= 'caixadetexto'
		document.getElementById('txt_qtde_metros'+indice).className 	= 'caixadetexto'
		document.getElementById('txt_num_corrida'+indice).className 	= 'caixadetexto'
		document.getElementById('chkt_nfe_historico'+indice).checked 	= true
		
		for (var i = 0; i < elementos.length; i++) {
			if (elementos[i].type == 'checkbox' && elementos[i].name != campo) {
				if (elementos[i].checked == false) resultado = false
			}
		}
		campo_sel.checked = resultado
	}
	if(document.getElementById('chkt_nfe_historico'+indice).checked) {
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
	var campo_sel 	= eval('document.'+form+'.'+campo+'')
	var elementos 	= eval('document.'+form+'.elements')
	var cor_origem 	= cor_origem.toLowerCase()
	
	if(typeof(elementos['chkt_nfe_historico[]'][0]) == 'undefined') {
		var linhas = 1//Existe apenas 1 único elemento ...
	}else {
		var linhas = (elementos['chkt_nfe_historico[]'].length)
	}
	
	for (i = 0; i < linhas; i++) {
		if (campo_sel.checked) {
			document.getElementById('chkt_nfe_historico'+i).checked 	= true
//Habilita as Caixas				
			document.getElementById('txt_medida1_mm'+i).disabled 		= false
			document.getElementById('txt_medida2_mm'+i).disabled 		= false
			document.getElementById('txt_qtde_metros'+i).disabled 		= false
			document.getElementById('txt_num_corrida'+i).disabled 		= false
//Aqui joga o Designer de Habilitado			
			document.getElementById('txt_medida1_mm'+i).className 		= 'caixadetexto'
			document.getElementById('txt_medida2_mm'+i).className 		= 'caixadetexto'
			document.getElementById('txt_qtde_metros'+i).className 		= 'caixadetexto'
			document.getElementById('txt_num_corrida'+i).className 		= 'caixadetexto'
		}else {
			document.getElementById('chkt_nfe_historico'+i).checked 	= false
			document.getElementById('chkt_nfe_historico'+i).disabled 	= false
//Desabilita as Caixas	
			document.getElementById('txt_medida1_mm'+i).disabled 		= true
			document.getElementById('txt_medida2_mm'+i).disabled 		= true
			document.getElementById('txt_qtde_metros'+i).disabled 		= true
			document.getElementById('txt_num_corrida'+i).disabled 		= true
//Aqui joga o Designer de Desabilitado				
			document.getElementById('txt_medida1_mm'+i).className 		= 'textdisabled'
			document.getElementById('txt_medida2_mm'+i).className 		= 'textdisabled'
			document.getElementById('txt_qtde_metros'+i).className 		= 'textdisabled'
			document.getElementById('txt_num_corrida'+i).className 		= 'textdisabled'
		}
	}
	if (campo_sel.checked) {
		if(navigator.appName == 'Netscape') {
			for (i = 3; i < (totallinhas.rows.length - 1); i++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
		}else {
			for (i = 3; i < (totallinhas.rows.length - 1); i++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
		}
	}else {
		for (i = 3; i < (totallinhas.rows.length - 1); i ++) totallinhas.rows[i].style.backgroundColor = cor_origem
	}
}

function calcular(form, indice, valor_perc, formato_aco) {
	var elements = eval('document.'+form+'.elements')
	var valor_aux, resultado = true
	var qtde, preco, letra = '', cont = 0, cont2 = 0, soma = 0;
	var caracteres = '0123456789,.-'
	
//As variáveis valor_perc, formato_aco, eu pego por parâmetro por serem dados do PA, continuando ...
//Medida 1
	var medida1 = (document.getElementById('txt_medida1_mm'+indice).value != '') ? eval(strtofloat(document.getElementById('txt_medida1_mm'+indice).value)) : 0
//Medida 2
	var medida2 = (document.getElementById('txt_medida2_mm'+indice).value != '') ? eval(strtofloat(document.getElementById('txt_medida2_mm'+indice).value)) : 0
	var fator_densidade = 1 + (valor_perc /100)

	if(formato_aco == 'Q') {
		var calculo = Math.pow(medida1 / 1000, 2) * 7850 * fator_densidade
	}else if(formato_aco == 'R') {
		var calculo = Math.PI / 4 * (Math.pow(medida1 / 1000, 2) * 7850) * fator_densidade
	}else {
		var calculo = ((medida1 * medida2) / 1000) * 7.85 * fator_densidade
	}	
/***********Retorno da Densidade***********/
	document.getElementById('txt_densidade'+indice).value = calculo
	document.getElementById('txt_densidade'+indice).value = arred(document.getElementById('txt_densidade'+indice).value, 3, 1)
//Qtde em Metros
	var qtde_metros = (document.getElementById('txt_qtde_metros'+indice).value != '') ? eval(strtofloat(document.getElementById('txt_qtde_metros'+indice).value)) : 0
/***********Peso Correto***********/
	document.getElementById('txt_peso_correto_kg'+indice).value = qtde_metros * calculo
	document.getElementById('txt_peso_correto_kg'+indice).value = arred(document.getElementById('txt_peso_correto_kg'+indice).value, 3, 1)
/***********Peso + 2***********/
//Peso Correto
	var peso_correto = (document.getElementById('txt_peso_correto_kg'+indice).value != '') ? eval(strtofloat(document.getElementById('txt_peso_correto_kg'+indice).value)) : 0
	document.getElementById('txt_peso_2'+indice).value = peso_correto * 1.02
	document.getElementById('txt_peso_2'+indice).value = arred(document.getElementById('txt_peso_2'+indice).value, 3, 1)
}