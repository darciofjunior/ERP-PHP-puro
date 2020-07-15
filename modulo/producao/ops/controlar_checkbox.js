//Arquivo particularizado somente p/ esta tela ...
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

function checkbox(form, campo, linha, indice, cor_origem) {
    var campo_sel = eval('document.'+form+'.'+campo+'')
    var elementos = eval('document.'+form+'.elements')
    var resultado = true
    cor_origem = cor_origem.toLowerCase()
    indice++//Somo mais hum no índice recebido por pârametro, p/ que seja ignorado o checkbox principal ...
    linha = eval(linha) + 2//Somo mais 2 na linha recebida por pârametro, p/ que sejam ignoradas as linhas cabeçalho e destaque ...
    if (elementos[indice].type == 'checkbox') {
        if (!(elementos[indice].checked)) {
            elementos[indice].checked = true
            for(var i = 0; i < elementos.length; i++) {
                if(elementos[i].type == 'checkbox' && elementos[i].name != campo) {
                    if(elementos[i].checked == false) resultado = false
                }
            }
            campo_sel.checked = resultado
        }else {
            campo_sel.checked = false
            elementos[indice].checked = false
        }
    }
    if(elementos[indice].checked) {
        if(navigator.appName == 'Netscape') {
            totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)';
        }else {
            totallinhas.rows[linha].style.backgroundColor = '#c6e2ff';
        }
    }else {
        totallinhas.rows[linha].style.backgroundColor = cor_origem;
    }
}