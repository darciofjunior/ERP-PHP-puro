function etapa1(posicao_inicial, posicao) {
/*Passo a qtde de elementos como sendo 4, porque eu tenho 4 elementos por linha,
contando o hidden também*/
	var qtde_elem_etapa1 = 4
	var total1 = 0
	var elementos = document.form.elements
	var fator_custo = eval(strtofloat(document.form.txt_fator_custo1.value))
	posicao = eval(posicao)
	posicao_inicial = eval(posicao_inicial)
//Cálculo na linha corrente
	var pecas_emb = eval(strtofloat(elementos[posicao].value))
	var preco_unitario_rs = eval(strtofloat(elementos[posicao + 1].value))

	var numero_linhas = (elementos['txt_total1[]'].length)
	if(typeof(numero_linhas) == 'undefined') {
		numero_linhas = 1
	}

	if(pecas_emb > 0) {
		elementos[posicao + 2].value = (preco_unitario_rs / pecas_emb) * fator_custo
	}

	if(isNaN(elementos[posicao + 2].value)) {
		elementos[posicao + 2].value = ''
	}else {
		elementos[posicao + 2].value = arred(elementos[posicao + 2].value, 2, 1)
	}

//Somatório de todos os totais de todas as linhas
	for(i = 0; i < numero_linhas; i++) {
		if(elementos[posicao_inicial + 2].value == '') {
			total1+=0
		}else {
			total1+=eval(strtofloat(elementos[posicao_inicial + 2].value))
		}
		posicao_inicial += qtde_elem_etapa1
	}
	document.form.txt_custo1.value = total1
	document.form.txt_custo1.value = arred(document.form.txt_custo1.value, 2, 1)
}

function etapa2() {
	var comprimento_a = strtofloat(document.form.txt_comprimento_a.value)
	if(comprimento_a == '') {
		comprimento_a = 0
	}

	var comprimento_b = strtofloat(document.form.txt_comprimento_b.value)

	if(comprimento_b == '') {
		comprimento_b = 0
	}

	document.form.txt_comprimento_total.value = (eval(comprimento_a) + eval(comprimento_b)) / 1000

	if(document.form.txt_comprimento_total.value == 0) {
		document.form.txt_comprimento_total.value = ''
	}else {
		document.form.txt_comprimento_total.value = arred(document.form.txt_comprimento_total.value, 3, 1)
	}

	if(document.form.txt_comprimento_total.value != '' && document.form.txt_densidade_kg_m.value) {
		var comprimento_total = strtofloat(document.form.txt_comprimento_total.value)
		if(comprimento_total == '') {
			comprimento_total = 0
		}
		var pecas_corte = strtofloat(document.form.txt_pecas_corte.value)
		if(pecas_corte == 0 || pecas_corte == '' || pecas_corte == '0.00') {
			pecas_corte = 1
		}
		var densidade_kg_m = strtofloat(document.form.txt_densidade_kg_m.value)
		document.form.txt_peso_aco_kg.value = eval(densidade_kg_m) * eval(comprimento_total) * 1.05
		document.form.txt_peso_aco_kg.value = document.form.txt_peso_aco_kg.value / pecas_corte
		var peso_aco_kg =document.form.txt_peso_aco_kg.value; //passo para esta var pq tenhoi um calculo embaibo com o mesmo
		document.form.txt_peso_aco_kg.value = arred(document.form.txt_peso_aco_kg.value, 3, 1)
	}
	if(document.form.txt_peso_aco_kg.value != '' && document.form.txt_preco_rs_kg.value != '' && document.form.txt_fator_custo2.value != '') {
		var peso_aco_kg = strtofloat(document.form.txt_peso_aco_kg.value)
		var preco_rs_kg = strtofloat(document.form.txt_preco_rs_kg.value)
		var fator_custo2 = strtofloat(document.form.txt_fator_custo2.value)
		document.form.txt_custo2.value = eval(peso_aco_kg) * eval(preco_rs_kg) * eval(fator_custo2)
		document.form.txt_custo2.value = arred(document.form.txt_custo2.value, 2, 1)
	}
}

function etapa3(posicao_inicial, posicao) {
/*Passo a qtde de elementos como sendo 5, porque eu tenho 5 elementos por linha,
contando o hidden também*/
	var qtde_elem_etapa3 = 5
	var total3 = 0
	elementos = document.form.elements
	posicao = eval(posicao)
	posicao_inicial = eval(posicao_inicial)

//Cálculo na linha corrente
	var fator_custo = eval(strtofloat(document.form.txt_fator_custo3.value))
	var qtde = eval(strtofloat(elementos[posicao + 1].value))
	var preco_unitario = eval(strtofloat(elementos[posicao + 2].value))
	var numero_linhas = (elementos['txt_total3[]'].length)

	if(typeof(numero_linhas) == 'undefined') {
		numero_linhas = 1
	}

	elementos[posicao + 3].value = qtde * preco_unitario * fator_custo

	if(isNaN(elementos[posicao + 3].value)) {
		elementos[posicao + 3].value = ''
	}else {
		elementos[posicao + 3].value = arred(elementos[posicao + 3].value, 2, 1)
	}

//Somatório de todos os totais de todas as linhas
	for(i = 0; i < numero_linhas; i++) {
		if(elementos[posicao_inicial + 3].value == '') {
			total3+=0
		}else {
			total3+=eval(strtofloat(elementos[posicao_inicial + 3].value))
		}
		posicao_inicial += qtde_elem_etapa3
	}
	document.form.txt_custo3.value = total3
	document.form.txt_custo3.value = arred(document.form.txt_custo3.value, 2, 1)
}

function etapa4(posicao_inicial, posicao) {
/*Passo a qtde de elementos como sendo 4, porque eu tenho 4 elementos por linha
contando o hidden também*/
	var qtde_elem_etapa4 = 4
	var total4 = 0
	elementos = document.form.elements
	posicao = eval(posicao)
	posicao_inicial = eval(posicao_inicial)
//Cálculo na linha corrente
	var fator_custo = strtofloat(document.form.txt_fator_custo4.value)
	var qtde_lote = eval(strtofloat(document.form.txt_qtde_lote.value))
//Aqui é para evitar erro de divisão por zero
	if(typeof(qtde_lote) == 'undefined' || qtde_lote == 0) {
		qtde_lote = 1
	}
	var tempo_hs = eval(strtofloat(elementos[posicao].value))
	var real_h = eval(strtofloat(elementos[posicao + 1].value))
	var numero_linhas = (elementos['txt_total4[]'].length)

	if(typeof(numero_linhas) == 'undefined') {
		numero_linhas = 1
	}

	elementos[posicao + 2].value = tempo_hs * real_h * fator_custo

	if(isNaN(elementos[posicao + 2].value)) {
		elementos[posicao + 2].value = ''
	}else {
		elementos[posicao + 2].value = arred(elementos[posicao + 2].value, 2, 1)
	}
//Somatório de todos os totais de todas as linhas
	for(i = 0; i < numero_linhas; i++) {
		if(elementos[posicao_inicial + 2].value == '') {
			total4+=0
		}else {
			total4+=eval(strtofloat(elementos[posicao_inicial + 2].value))
		}
		posicao_inicial += qtde_elem_etapa4
	}
	document.form.txt_custo4.value = (total4 / qtde_lote)
	document.form.txt_custo4.value = arred(document.form.txt_custo4.value, 2, 1)
}

//Recalcula a Qtde do Lote para a etapa 4
function extra_etapa4() {
//Aqui eu busco o primeiro elemento da etapa 4
	var elementos = document.form.elements
	var	posicao_inicial = 0
	for(i = 0; i < elementos.length; i++) {
		if(elementos[i].name == 'txt_custo4') {
//Somo 1 para pegar o primeiro elemento do array, que está sendo disparado no for
			posicao_inicial = i + 1
			i = elementos.length
		}
	}
	var qtde_elem_etapa4 = 4
	var total4 = 0
	var fator_custo = eval(strtofloat(document.form.txt_fator_custo4.value))
	var qtde_lote = eval(strtofloat(document.form.txt_qtde_lote.value))
//Aqui é para evitar erro de divisão por zero
	if(typeof(qtde_lote) == 'undefined' || qtde_lote == 0) {
		qtde_lote = 1
	}
	var numero_linhas = (elementos['txt_total4[]'].length)

	if(typeof(numero_linhas) == 'undefined') {
		numero_linhas = 1
	}

	for(y = 0; y < numero_linhas; y++) {
		var tempo_hs = eval(strtofloat(elementos[posicao_inicial].value))
		var real_h = eval(strtofloat(elementos[posicao_inicial + 1].value))
		elementos[posicao_inicial + 2].value = tempo_hs * real_h * fator_custo
		elementos[posicao_inicial + 2].value = arred(elementos[posicao_inicial + 2].value, 2, 1)
		total4+=eval(strtofloat(elementos[posicao_inicial + 2].value))
//qtde * preco_unitario * fator_custo
		posicao_inicial += qtde_elem_etapa4
	}
	document.form.txt_custo4.value = (total4 / qtde_lote)
	document.form.txt_custo4.value = arred(document.form.txt_custo4.value, 2, 1)
}

function etapa5(posicao_inicial, posicao) {
/*Passo a qtde de elementos como sendo 5, porque eu tenho 5 elementos por linha
contando o hidden também*/
	var qtde_elem_etapa5 = 5
	var total5 = 0
	elementos = document.form.elements
	posicao = eval(posicao)
	posicao_inicial = eval(posicao_inicial)
//Cálculo na linha corrente
	var fator_custo = eval(strtofloat(document.form.txt_fator_custo5.value))
	var fator_tt = eval(strtofloat(elementos[posicao].value))
	var preco_kg_rs = eval(strtofloat(elementos[posicao + 1].value))
	var peso_aco = eval(strtofloat(elementos[posicao + 2].value))
	if(typeof(peso_aco) == 'undefined') {
		peso_aco = 0
	}
	//var peso_aco = eval(strtofloat(document.form.txt_peso_aco_kg.value))
	var numero_linhas = (elementos['txt_total5[]'].length)

	if(typeof(numero_linhas) == 'undefined') {
		numero_linhas = 1
	}
	elementos[posicao + 3].value = (fator_tt * preco_kg_rs * peso_aco * fator_custo)

	if(isNaN(elementos[posicao + 3].value)) {
		elementos[posicao + 3].value = ''
	}else {
		elementos[posicao + 3].value = arred(elementos[posicao + 3].value, 2, 1)
	}
//Somatório de todos os totais de todas as linhas
	for(i = 0; i < numero_linhas; i++) {
		if(elementos[posicao_inicial + 3].value == '') {
			total5+=0
		}else {
			total5+=eval(strtofloat(elementos[posicao_inicial + 3].value))
		}
		posicao_inicial += qtde_elem_etapa5
	}
	document.form.txt_custo5.value = total5
	document.form.txt_custo5.value = arred(document.form.txt_custo5.value, 2, 1)
}

//Atribui os valores da etapa 2: Peso Aço Kg - para etapa 5 no array de peso aço
function extra_etapa5() {
//Aqui eu busco o primeiro elemento da etapa 5
	var elementos = document.form.elements
	var	posicao_inicial = 0
	for(i = 0; i < elementos.length; i++) {
		if(elementos[i].name == 'txt_custo5') {
//Somo 1 para pegar o primeiro elemento do array, que está sendo disparado no for
			posicao_inicial = i + 1
			i = elementos.length
		}
	}
	var qtde_elem_etapa5 = 5
	var total5 = 0
	var fator_custo = eval(strtofloat(document.form.txt_fator_custo5.value))
	var peso_aco_kg = eval(strtofloat(document.form.txt_peso_aco_kg.value))
	var numero_linhas = (elementos['txt_peso_aco5[]'].length)

	if(typeof(numero_linhas) == 'undefined') {
		numero_linhas = 1
	}

	for(y = 0; y < numero_linhas; y++) {
		var fator_tt = eval(strtofloat(elementos[posicao_inicial].value))
		var preco_kg_rs = eval(strtofloat(elementos[posicao_inicial + 1].value))
		elementos[posicao_inicial + 2].value = eval(strtofloat(document.form.txt_peso_aco_kg.value)) / 1.05
		elementos[posicao_inicial + 2].value = arred(elementos[posicao_inicial + 2].value, 3, 1)
		elementos[posicao_inicial + 3].value = fator_tt * preco_kg_rs * peso_aco_kg * fator_custo
		elementos[posicao_inicial + 3].value = arred(elementos[posicao_inicial + 3].value, 2, 1)
		total5+=eval(strtofloat(elementos[posicao_inicial + 3].value))
//qtde * preco_unitario * fator_custo
		posicao_inicial += qtde_elem_etapa5
	}
	document.form.txt_custo5.value = total5
	document.form.txt_custo5.value = arred(document.form.txt_custo5.value, 2, 1)
}

function etapa6(posicao_inicial, posicao) {
/*Passo a qtde de elementos como sendo 4, porque eu tenho 4 elementos por linha
contando o hidden também*/
	var qtde_elem_etapa6 = 4
	var total6 = 0
	elementos = document.form.elements
	posicao = eval(posicao)
	posicao_inicial = eval(posicao_inicial)
//Cálculo na linha corrente
	var fator_custo = strtofloat(document.form.txt_fator_custo6.value)
	var qtde = eval(strtofloat(elementos[posicao].value))
	var preco_unitario_rs = eval(strtofloat(elementos[posicao + 1].value))
	var numero_linhas = (elementos['txt_total6[]'].length)

	if(typeof(numero_linhas) == 'undefined') {
		numero_linhas = 1
	}

	elementos[posicao + 2].value = qtde * preco_unitario_rs * fator_custo

	if(isNaN(elementos[posicao + 2].value)) {
		elementos[posicao + 2].value = ''
	}else {
		elementos[posicao + 2].value = arred(elementos[posicao + 2].value, 2, 1)
	}
//Somatório de todos os totais de todas as linhas
	for(i = 0; i < numero_linhas; i++) {
		if(elementos[posicao_inicial + 2].value == '') {
			total6+=0
		}else {
			total6+=eval(strtofloat(elementos[posicao_inicial + 2].value))
		}
		posicao_inicial += qtde_elem_etapa6
	}
	document.form.txt_custo6.value = total6
	document.form.txt_custo6.value = arred(document.form.txt_custo6.value, 2, 1)
}

function etapa7(posicao_inicial, posicao) {
/*Passo a qtde de elementos como sendo 5, porque eu tenho 5 elementos por linha
contando o hidden também*/
	var qtde_elem_etapa7 = 5
	var total7 = 0
	elementos = document.form.elements
	posicao = eval(posicao)
	posicao_inicial = eval(posicao_inicial)
//Cálculo na linha corrente
	var fator_custo = eval(strtofloat(document.form.txt_fator_custo7.value))
	var qtde = eval(strtofloat(elementos[posicao + 1].value))
	var preco_unitario = eval(strtofloat(elementos[posicao + 2].value))
	var numero_linhas = (elementos['txt_total7[]'].length)

	if(typeof(numero_linhas) == 'undefined') {
		numero_linhas = 1
	}

	elementos[posicao + 3].value = qtde * preco_unitario * fator_custo

	if(isNaN(elementos[posicao + 3].value)) {
		elementos[posicao + 3].value = ''
	}else {
		elementos[posicao + 3].value = arred(elementos[posicao + 3].value, 2, 1)
	}
//Somatório de todos os totais de todas as linhas
	for(i = 0; i < numero_linhas; i++) {
		if(elementos[posicao_inicial + 3].value == '') {
			total7+=0
		}else {
			total7+=eval(strtofloat(elementos[posicao_inicial + 3].value))
		}
		posicao_inicial += qtde_elem_etapa7
	}
	document.form.txt_custo7.value = total7
	document.form.txt_custo7.value = arred(document.form.txt_custo7.value, 2, 1)
}

function todas_etapas(taxa_financeira_vendas) {
	var taxa_financeira_vendas = eval(taxa_financeira_vendas)
	taxa_financeira_vendas = ((taxa_financeira_vendas / 100) + 1)
	var custo_total = 0
	for(i = 1; i < 8; i++) {
		custo = eval('document.form.txt_custo'+i+'.value')
		custo = eval(strtofloat(custo))
		custo_total+=custo
	}
	document.form.txt_custo_total.value = custo_total * taxa_financeira_vendas
	document.form.txt_custo_total.value = arred(document.form.txt_custo_total.value, 2, 1)
}
