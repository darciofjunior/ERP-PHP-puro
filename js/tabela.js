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

function selecionar(form, campo, totallinhas, cor_origem) {
        var campo_sel = eval('document.'+form+'.'+campo+'')
        var elements = eval('document.'+form+'.elements')
	cor_origem = cor_origem.toLowerCase()
	for (i = 0; i < elements.length; i ++) {
		if (elements[i].type == 'checkbox') {
			if (campo_sel.checked) {
				elements[i].checked = true
			}else {
				elements[i].checked = false
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
                                totallinhas.rows[i].style.backgroundColor = '#c6e2ff';
		        }
                }
        }else {
                for (i = 3; i < (totallinhas.rows.length - 1); i ++) {
                        totallinhas.rows[i].style.backgroundColor = cor_origem;
                }
	}
}

function checkbox(form, campo, valor, cor_origem) {
	var campo_sel = eval('document.'+form+'.'+campo+'')
	var elements = eval('document.'+form+'.elements')
	var i, resultado = true
	valor++;
	linha = valor + 2
	cor_origem = cor_origem.toLowerCase()
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
		if(navigator.appName == 'Netscape') {
			totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)';
		}else {
			totallinhas.rows[linha].style.backgroundColor = '#c6e2ff';
		}
	}else {
		totallinhas.rows[linha].style.backgroundColor = cor_origem;
	}
}

function options(form, campo, valor, cor_origem) {
	var campo_sel = eval('document.'+form+'.'+campo+'')
	var elements = eval('document.'+form+'.elements')
	cor_origem = cor_origem.toLowerCase()
	linha = eval(valor) + 3
        if (!(elements[valor].checked)) {
		elements[valor].checked = true
	}else {
		elements[valor].checked = false
	}
        for (i = 3; i < (totallinhas.rows.length - 1); i ++) {
		totallinhas.rows[i].style.backgroundColor = cor_origem;
	}
	if (elements[valor].checked) {
		if(navigator.appName == 'Netscape') {
			totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)';
		}else {
			totallinhas.rows[linha].style.backgroundColor = '#c6e2ff';
		}
	}else {
		totallinhas.rows[linha].style.backgroundColor = cor_origem;
	}
}

function aba(cel_atual, qtde_abas, tam_total) {
	for(var a = 0; a < qtde_abas; a++) {
		var abas = document.getElementById(('aba'+a))
		abas.className = 'aba_inativa'
	}
	cel_atual.className = 'aba_ativa'
}