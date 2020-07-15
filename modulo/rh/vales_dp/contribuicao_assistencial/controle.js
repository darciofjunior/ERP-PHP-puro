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

function checkbox_habilita(indice, cor_origem) {
	cor_origem = cor_origem.toLowerCase()
	linha = eval(indice) + 4
/**********************************************/
//Se não estiver checado, então fica marcado ...
	if(document.getElementById('chkt_funcionario'+indice).checked == false) {
		document.getElementById('chkt_funcionario'+indice).checked = true
	}else {//Se marcado, então fica desmarcado ...
		document.getElementById('chkt_funcionario'+indice).checked = false
	}
/**********************************************/
	if (document.getElementById('chkt_funcionario'+indice).checked == true) {
		document.getElementById('txt_comissao_dsr'+indice).disabled = false
//Aqui joga o Designer de Habilitado
		document.getElementById('txt_comissao_dsr'+indice).className = 'caixadetexto'
		document.getElementById('txt_comissao_dsr'+indice).focus()
		document.getElementById('txt_comissao_dsr'+indice).select()
	}else {
		document.getElementById('txt_comissao_dsr'+indice).disabled = true
//Aqui joga o Designer de Desabilitado
		document.getElementById('txt_comissao_dsr'+indice).className = 'textdisabled'
	}
//Colore de azul a linha corrente ...
	if (document.getElementById('chkt_funcionario'+indice).checked) {
		if(navigator.appName == 'Netscape') {
			totallinhas.rows[linha].style.backgroundColor = 'rgb(198,226,255)';
		}else {
			totallinhas.rows[linha].style.backgroundColor = '#c6e2ff';
		}
	}else {
		totallinhas.rows[linha].style.backgroundColor = cor_origem;
	}
//Aqui é um controle para poder selecionar o checkbox principal
	var total_itens_orcamentos          = 0
	var total_itens_orcamentos_marcados = 0
        var elementos                       = document.form.elements
	
	for(i = 0; i < elementos.length; i++) {
		if(elementos[i].name == 'chkt_funcionario[]') {
                    if(elementos[i].checked == true) {//Somente para orçamentos q estão checados
                        total_itens_orcamentos_marcados++
                    }
                    total_itens_orcamentos++
		}
	}
//Significa que todos os itens de Orçamento estão marcados
	if(total_itens_orcamentos == total_itens_orcamentos_marcados) {
            document.form.chkt_tudo.checked = true
	}else {
            document.form.chkt_tudo.checked = false
	}
}

function selecionar_tudo(totallinhas, cor_origem) {
    var elements = document.form.elements
    var linhas = (typeof(elements['chkt_funcionario[]'][0]) == 'undefined') ? 1 : elements['chkt_funcionario[]'].length
    if(document.getElementById('chkt_tudo').checked == true) {//Checkbox Principal está marcado ...
/*Significa que esse produto contém o Lote do Custo habilitado na Etapa 5, sendo assim não pode 
desabilitar essa linha*/
        for(var i = 0; i < linhas; i++) {
            document.getElementById('chkt_funcionario'+i).checked = true
            document.getElementById('txt_comissao_dsr'+i).disabled = true
//Aqui joga o Designer de Habilitado
            document.getElementById('txt_comissao_dsr'+i).className = 'caixadetexto'
        }
    }else {//Checkbox Principal está desmarcado ...
        for(var i = 0; i < linhas; i++) {
            document.getElementById('chkt_funcionario'+i).checked = false
            document.getElementById('txt_comissao_dsr'+i).disabled = true
//Aqui joga o Designer de Desabilitado
            document.getElementById('txt_comissao_dsr'+i).className = 'textdisabled'
        }
    }
    if(document.getElementById('chkt_tudo').checked == true) {//Checkbox Principal está marcado ...
        if(navigator.appName == 'Netscape') {
            for (i = 4; i < (totallinhas.rows.length - 2); i++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }else {
            for (i = 4; i < (totallinhas.rows.length - 2); i++) totallinhas.rows[i].style.backgroundColor = 'rgb(198,226,255)'
        }
    }else {
        for (i = 4; i < (totallinhas.rows.length - 2); i++)     totallinhas.rows[i].style.backgroundColor = cor_origem
    }
}