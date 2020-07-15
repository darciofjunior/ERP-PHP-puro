function selecionar_todos(form, campo, totallinhas, cor_origem) {
	var checkbox_principal 	= eval('document.'+form+'.'+campo+'')
	var elementos 			= document.form.elements
	//Prepara a Tela p/ poder gravar no BD ...
	if(typeof(elementos['chkt_fornecedor_prod_insumo[]'][0]) == 'undefined') {
		var linhas = 1//Existe apenas 1 único elemento ...
	}else {
		var linhas = (elementos['chkt_fornecedor_prod_insumo[]'].length)
	}
	//Verifico se o elemento principal está checado ou não ...
	var checar_objetos 	= (checkbox_principal.checked) ? true : false
	var css_objetos		= (checkbox_principal.checked) ? 'caixadetexto' : 'textdisabled'
	var disabled_objetos= (checkbox_principal.checked) ? '' : 'disabled'
	
	//Aqui eu trato os objetos de cada linha ...
	for(var i = 0; i < linhas; i++) {
		document.getElementById('chkt_fornecedor_prod_insumo'+i).checked = checar_objetos
		//Muda a cor dos objetos da linha ...
		document.getElementById('txt_preco_fat_nac_real'+i).className 	= css_objetos
		document.getElementById('txt_preco_fat_inter_est'+i).className 	= css_objetos
		document.getElementById('txt_preco_fat_inter_real'+i).className	= css_objetos
		//Habilita ou Desabilita objetos da linha ...
		document.getElementById('txt_preco_fat_nac_real'+i).disabled 	= disabled_objetos
		document.getElementById('txt_preco_fat_inter_est'+i).disabled 	= disabled_objetos
		document.getElementById('txt_preco_fat_inter_real'+i).disabled	= disabled_objetos
	}
	
	//Aqui eu trato as cores da linha ...
	for(var i = 0; i < linhas; i++) {
		if(document.getElementById('chkt_fornecedor_prod_insumo'+i).checked == true) {
			if(navigator.appName == 'Netscape') {
				totallinhas.rows[i+3].style.backgroundColor = 'rgb(198,226,255)';
			}else {
				totallinhas.rows[i+3].style.backgroundColor = '#c6e2ff';
			}
		}else {
			totallinhas.rows[i+3].style.backgroundColor = cor_origem;
		}
	}
}

function checkbox_habilita(form, campo, indice, cor_origem) {
	var campo_sel 	= eval('document.'+form+'.'+campo+'')
	var resultado	= true
	var linha 		= eval(indice) + 3
	var elementos 	= document.form.elements

	if(document.getElementById('chkt_fornecedor_prod_insumo'+indice).checked == false) {
		document.getElementById('chkt_fornecedor_prod_insumo'+indice).checked		= true
		//Habilita os objetos ...
		document.getElementById('txt_preco_fat_nac_real'+indice).disabled 	= false
		document.getElementById('txt_preco_fat_inter_est'+indice).disabled 	= false
		document.getElementById('txt_preco_fat_inter_real'+indice).disabled = false
		//Muda o Layout para Habilitado ...
		document.getElementById('txt_preco_fat_nac_real'+indice).className 	= 'caixadetexto'
		document.getElementById('txt_preco_fat_inter_est'+indice).className = 'caixadetexto'
		document.getElementById('txt_preco_fat_inter_real'+indice).className= 'caixadetexto'
	}else {
		document.getElementById('chkt_fornecedor_prod_insumo'+indice).checked		= false
		//Desabilita os objetos ...
		document.getElementById('txt_preco_fat_nac_real'+indice).disabled 	= true
		document.getElementById('txt_preco_fat_inter_est'+indice).disabled 	= true
		document.getElementById('txt_preco_fat_inter_real'+indice).disabled = true
		//Muda o Layout para Desabilitado ...
		document.getElementById('txt_preco_fat_nac_real'+indice).className 	= 'textdisabled'
		document.getElementById('txt_preco_fat_inter_est'+indice).className = 'textdisabled'
		document.getElementById('txt_preco_fat_inter_real'+indice).className= 'textdisabled'
	}
	//Aqui eu verifico se todas as linhas estão checadas ...
	for (i = 0; i < elementos.length; i++) {
		if (elementos[i].type == 'checkbox') {
			if (elementos[i].name == 'chkt_fornecedor_prod_insumo[]' && elementos[i].checked == false) {
				resultado = false
				break;
			}
		}
	}
	campo_sel.checked 	= resultado
	cor_origem 			= cor_origem.toLowerCase()

	if(document.getElementById('chkt_fornecedor_prod_insumo'+indice).checked == true) {
		if(navigator.appName == 'Netscape') {
			totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)';
		}else {
			totallinhas.rows[linha].style.backgroundColor = '#c6e2ff';
		}
	}else {
		totallinhas.rows[linha].style.backgroundColor = cor_origem;
	}
}

function focos(objeto) {
	objeto.disabled = false
	objeto.focus()
	return false
}