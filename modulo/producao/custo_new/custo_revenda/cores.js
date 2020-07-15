function sobre_celula_2(cel_atual, backgroundColor) {
	var cor_atual = cel_atual.style.backgroundColor
	if (cor_atual == '') {
		cor_atual = '#c6e2ff'
	}
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

function checkbox_2(form, campo, valor, cor_click, cor_nclick) {
	var campo_sel = eval('document.'+form+'.'+campo+'')
	var elements = eval('document.'+form+'.elements')
	var i, resultado = true
	valor++;
	linha = valor + 2
	cor_click = cor_click.toLowerCase()
        cor_nclick = cor_nclick.toLowerCase()
	if (elements[valor].type == 'checkbox') {
		if (!(elements[valor].checked)) {
			elements[valor].checked = true
			for (i=0;i<elements.length;i++){
				if (elements[i].type == 'checkbox' && elements[i].name != campo) {
					if (elements[i].checked == false) {
						resultado = false
					}
				}
			}
			campo_sel.checked = resultado
		}else {
			campo_sel.checked = false
			elements[valor].checked = false
		}
	}
	if (elements[valor].checked) {
		totallinhas.rows[linha].style.backgroundColor = cor_click
	}else {
		totallinhas.rows[linha].style.backgroundColor = cor_nclick
	}
}
