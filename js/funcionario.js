// função que manda o focus para o telefone comercial
function ddd1_focus() {
	if (document.form.txtddd1.value.length == '3') {
		document.form.txttelefone1.focus()
	}
}
// função que manda o focus para o ddd comercial
function telefone1_focus() {
	if (document.form.txttelefone1.value.length == '8') {
				document.form.txtddd2.focus()
	}
}
// função que manda o focus para o telefone celular
function ddd2_focus() {
	if (document.form.txtddd2.value.length == '3') {
		document.form.txttelefone2.focus()
	}
}
// função que manda o focus para a home page
function telefone2_focus() {
	if (document.form.txttelefone2.value.length == '8') {
		document.form.txtemail.focus()
	}
}
// função que abilita a unidade federal
function pais_abilita() {
	if (document.form.cmbpais.value == '31') {
		document.form.cmbfederal.disabled = false
		document.form.cmbfederal.focus()
	}else {
		document.form.cmbfederal.disabled = true
	}
}
function validar() {
// validação do nome
	var caractxtnome = "@/1:4,;5&#®$6!?7¨%89*-+{2}3[0]\)(><|=|ºÜÖÄ"
	if (document.form.txtnome.value == '') {
		window.alert('DIGITE O NOME !')
		document.form.txtnome.focus()
		return false
	}
	for (y = 0; y < caractxtnome.length; y ++) {
		asctxtnome =  caractxtnome.charAt(y)
		if (document.form.txtnome.value.indexOf(asctxtnome, 0) > - 1) {
			window.alert('NOME INVÁLIDO !')
			document.form.txtnome.focus()
			document.form.txtnome.select()
			return false
		}
	}
	var txtnome2 = document.form.txtnome.value
	var txtnome3 = txtnome2
	var ttxtnome = document.form.txtnome.value.length
	for (x = 0; x < ttxtnome; x ++) {
		txtnome2 = txtnome3.replace(' ', '')
		txtnome3 = txtnome2
	}
	var ttxtnome2 = txtnome3.length
	if (ttxtnome2 < '7') {
		window.alert('NOME INCOMPLETO !')
		document.form.txtnome.focus()
		document.form.txtnome.select()
		return false
	}
// validação do sexo
	var option  = - 1
	for (i = 0; i <document.form.radsexo.length; i ++) {
		if (document.form.radsexo[i].checked) {
			option = i
		}
	}
	if (option == -1) {
		window.alert('SELECIONE O SEXO !')
		return false
	}
// validação da nacionalidade
	var veri = document.form.cmbnacionalidade.selectedIndex
	if (document.form.cmbnacionalidade.options[veri].value == '') {
		window.alert('SELECIONE A NACIONALIDADE !')
		document.form.cmbnacionalidade.focus()
		return false
	}
// validação do estado civil
	var veri = document.form.cmbestado_civil.selectedIndex
	if (document.form.cmbestado_civil.options[veri].value == '') {
		window.alert('SELECIONE O ESTADO CIVIL !')
		document.form.cmbestado_civil.focus()
		return false
	}
// validação da naturalidade
	var caractxtnaturalidade = "@/1:4,;5&#®$6!?7¨%89*+{2}3[0]\)(><|=|ºÜÖÄ"
	if (document.form.txtnaturalidade.value == '') {
		window.alert('DIGITE A NATURALIDADE !')
		document.form.txtnaturalidade.focus()
		return false
	}
	for (y = 0; y < caractxtnaturalidade.length; y ++) {
		asctxtnaturalidade =  caractxtnaturalidade.charAt(y)
		if (document.form.txtnaturalidade.value.indexOf(asctxtnaturalidade, 0) > - 1) {
			window.alert('NATURALIDADE INVÁLIDA !')
			document.form.txtnaturalidade.focus()
			document.form.txtnaturalidade.select()
			return false
		}
	}
	var txtnaturalidade2 = document.form.txtnaturalidade.value
	var txtnaturalidade3 = txtnaturalidade2
	var ttxtnaturalidade = document.form.txtnaturalidade.value.length
	for (x = 0; x < ttxtnaturalidade; x ++) {
		txtnaturalidade2 = txtnaturalidade3.replace(' ', '')
		txtnaturalidade3 = txtnaturalidade2
	}
	var ttxtnaturalidade2 = txtnaturalidade3.length
	if (ttxtnaturalidade2 < '5') {
		window.alert('NATURALIDADE INCOMPLETA !')
		document.form.txtnaturalidade.focus()
		document.form.txtnaturalidade.select()
		return false
	}
// validação do nível academico
	var veri = document.form.cmbacademico.selectedIndex
	if (document.form.cmbacademico.options[veri].value == '') {
		window.alert('SELECIONE O NÍVEL ACADEMICO !')
		document.form.cmbacademico.focus()
		return false
	}
// validação da txtnascimento de nascimento
	if (document.form.txtnascimento.value == '') {
		window.alert('DIGITE A DATA DE NASCIMENTO !')
		document.form.txtnascimento.focus()
		document.form.txtnascimento.select()
		return false
	}
	var vdia = document.form.txtnascimento.value.indexOf('/', 0)
	if (vdia < 0 || vdia> 2) {
	 	window.alert('DATA DE NASCIMENTO INVÁLIDA DIGITE NO FORMATO \N   EXEMPLO: DD/MM/YYYY !')
		document.form.txtnascimento.focus()
		document.form.txtnascimento.select()
		return false
	}
	if (vdia < 2) {
		document.form.txtnascimento.value = '0' + document.form.txtnascimento.value
	}
	vdia = document.form.txtnascimento.value.indexOf('/', 3)
	if (vdia < 3 ||  vdia > 5) {
		window.alert('DATA DE NASCIMENTO INVÁLIDA DIGITE NO FORMATO \N  EXEMPLO: DD/MM/YYYY !')
		document.form.txtnascimento.focus()
		document.form.txtnascimento.select()
		return false
	}
	if (vdia < 5) {
		document.form.txtnascimento.value = document.form.txtnascimento.value.substring(0, 3) + "0" + document.form.txtnascimento.value.substring(3, document.form.txtnascimento.value.length)
	}
	vdia = document.form.txtnascimento.value.length
	if (vdia < 7) {
		window.alert('DATA DE NASCIMENTO INVÁLIDA DIGITE NO FORMATO \N  EXEMPLO: DD/MM/YYYY !')
		document.form.txtnascimento.focus()
		document.form.txtnascimento.select()
		return false
	}
	var nDia = document.form.txtnascimento.value.substring(0, 2)
	var nMes = document.form.txtnascimento.value.substring(3, 5) - 1
	var nAno = document.form.txtnascimento.value.substring(6, 10)
	var newDate = new Date(nAno, nMes, nDia)
	if (newDate.getDate() != nDia) 	{
		window.alert('DATA DE NASCIMENTO INVÁLIDA !')
		document.form.txtnascimento.focus()
		document.form.txtnascimento.select()
		return false
	}else if (newDate.getMonth() != nMes) {
		window.alert('DATA DE NASCIMENTO INVÁLIDA !')
		document.form.txtnascimento.focus()
		document.form.txtnascimento.select()
		return false
	}
        var maior = document.form.txtnascimento.value.substring(6, 10);
	if (maior > '1987' ) {
		window.alert('DATA INVÁLIDA PARA CADASTRAMENTO !')
		document.form.txtnascimento.focus()
		document.form.txtnascimento.select()
		return false
	}
//Validacao do RG
	if (document.form.txtrg.value==''){
        	window.alert('DIGITE O RG !')
        	document.form.txtrg.focus()
        	document.form.txtrg.select()
        	return false
	}
 // validação da data txtemissao
	if (document.form.txtemissao.value != '') {
		var vdia = document.form.txtemissao.value.indexOf('/', 0)
		if (vdia < 0 || vdia> 2) {
	 		window.alert('DATA DE EMISSÃO INVÁLIDA DIGITE NO FORMATO \N   EXEMPLO: DD/MM/YYYY !')
			document.form.txtemissao.focus()
			document.form.txtemissao.select()
			return false
		}
		if (vdia < 2) {
			document.form.txtemissao.value = '0' + document.form.txtemissao.value
		}
		vdia = document.form.txtemissao.value.indexOf('/', 3)
		if (vdia < 3 ||  vdia > 5) {
			window.alert('DATA DE EMISSÃO INVÁLIDA DIGITE NO FORMATO \N  EXEMPLO: DD/MM/YYYY !')
			document.form.txtemissao.focus()
			document.form.txtemissao.select()
			return false
		}
		if (vdia < 5) {
			document.form.txtemissao.value = document.form.txtemissao.value.substring(0, 3) + "0" + document.form.txtemissao.value.substring(3, document.form.txtemissao.value.length)
		}
		vdia = document.form.txtemissao.value.length
		if (vdia < 7) {
			window.alert('DATA DE EMISSÃO INVÁLIDA DIGITE NO FORMATO \N  EXEMPLO: DD/MM/YYYY !')
			document.form.txtemissao.focus()
			document.form.txtemissao.select()
			return false
		}
		var nDia = document.form.txtemissao.value.substring(0, 2)
		var nMes = document.form.txtemissao.value.substring(3, 5) - 1
		var nAno = document.form.txtemissao.value.substring(6, 10)
		var newDate = new Date(nAno, nMes, nDia)
		if (newDate.getDate() != nDia) 	{
			window.alert('DATA DE EMISSÃO INVÁLIDA !')
			document.form.txtemissao.focus()
			document.form.txtemissao.select()
			return false
		}else if (newDate.getMonth() != nMes){
			window.alert('DATA DE EMISSÃO INVÁLIDA !')
			document.form.txtemissao.focus()
			document.form.txtemissao.select()
			return false
		}
	}
 // validação do cnpf
	txtcnpf = document.form.txtcnpf.value
	switch (txtcnpf.length) {
		case 0 :
			window.alert('DIGITE O CPF !')
			document.form.txtcnpf.focus()
			return false
                        break;
		case 11 :
			if (!valida_cpf(txtcnpf)) {
				window.alert ('CPF INVÁLIDO !')
				document.form.txtcnpf.focus()
				document.form.txtcnpf.select()
				return false
			}
                         break;
		default:
			window.alert('CPF INVÁLIDO !')
			document.form.txtcnpf.focus()
			document.form.txtcnpf.select()
			return false
	}
//validação da empresa
	var veri = document.form.cmbempresa.selectedIndex
	if (document.form.cmbempresa.options[veri].value == '') {
		window.alert('SELECIONE A EMPRESA !')
		document.form.cmbempresa.focus()
		return false
	}
// validação do endereço
	var caractxtendereco = "'@/®;&#$!?%*-+{}[]\)(><|=|ºÜÖÄ"
	if (document.form.txtendereco.value == '') {
		window.alert('DIGITE O ENDEREÇO !')
		document.form.txtendereco.focus()
		return false
	}
	for (i = 0; i <caractxtendereco.length; i ++) {
		asctxtendereco =  caractxtendereco.charAt(i)
		if (document.form.txtendereco.value.indexOf(asctxtendereco, 0) > - 1) {
			window.alert('ENDEREÇO INVÁLIDO !')
			document.form.txtendereco.focus()
			document.form.txtendereco.select()
			return false
		}
	}
	var txtendereco2 = document.form.txtendereco.value
	var txtendereco3 = txtendereco2
	var ttxtendereco = document.form.txtendereco.value.length
	for (x = 0; x <ttxtendereco; x ++) {
		txtendereco2 = txtendereco3.replace(' ', '')
		txtendereco3 = txtendereco2
	}
	var ttxtendereco2 = txtendereco3.length
	if (ttxtendereco2 < 2) {
		window.alert('ENDEREÇO INCOMPLETO !')
		document.form.txtendereco.focus()
		document.form.txtendereco.select()
		return false
	}
//Validacao do numero
	var caractxtnumero = "'/®&#$!?%*-+{}[]\)(><|=|ºÜÖÄ''"
	if (document.form.txtnumero.value == '') {
		window.alert('DIGITE O NÚMERO !')
		document.form.txtnumero.focus()
		return false
	}
	for (i = 0; i <caractxtnumero.length; i ++) {
		asctxtnumero =  caractxtnumero.charAt(i)
		if (document.form.txtnumero.value.indexOf(asctxtnumero, 0) > - 1) {
			window.alert('NÚMERO INVÁLIDO !')
			document.form.txtnumero.focus()
			document.form.txtnumero.select()
			return false
		}
	}
	var num2    = document.form.txtnumero.value
	var num3    = num2
	var tnum    = document.form.txtnumero.value.length
	for (x = 0; x <tnum; x ++) {
		num2 = num3.replace(' ', '')
		num3 = num2
	}
	var tnum2 = num3.length
	if (tnum2 < '1'){
		window.alert('NÚMERO INCOMPLETO !')
		document.form.txtnumero.focus()
		document.form.txtnumero.select()
		return false
	}
// validação do bairro
	var caratxtbairro = "@/®&#$!?%*-+{}[]\)(><|=|ºÜÖÄ"
	if (document.form.txtbairro.value == '') {
		window.alert('DIGITE O BAIRRO !')
		document.form.txtbairro.focus()
		return false
	}
	for (i = 0; i <caratxtbairro.length; i ++) {
		asctxtbairro =  caratxtbairro.charAt(i)
		if (document.form.txtbairro.value.indexOf(asctxtbairro, 0) > - 1) {
			window.alert('BAIRRO INVÁLIDO !')
			document.form.txtbairro.focus()
			document.form.txtbairro.select()
			return false
		}
	}
	var txtbairro2 = document.form.txtbairro.value
	var txtbairro3 = txtbairro2
	var ttxtbairro = document.form.txtbairro.value.length
	for (x = 0; x <ttxtbairro; x ++) {
		txtbairro2 = txtbairro3.replace(' ', '')
		txtbairro3 = txtbairro2
	}
	var ttxtbairro2 = txtbairro3.length
	if (ttxtbairro2 < '2'){
		window.alert('BAIRRO INCOMPLETO !')
		document.form.txtbairro.focus()
		document.form.txtbairro.select()
		return false
	}
// validação da cidade
	var caracid = "@/®&#$!?%*-+{}[]\)(><|=|ºÜÖÄ'"
	if (document.form.txtcidade.value == '') {
		window.alert('DIGITE A CIDADE !')
		document.form.txtcidade.focus()
		return false
	}
	for (i = 0; i <caracid.length; i ++) {
		asccid =  caracid.charAt(i)
		if (document.form.txtcidade.value.indexOf(asccid, 0) > - 1) {
			window.alert('CIDADE INVÁLIDA !')
			document.form.txtcidade.focus()
			document.form.txtcidade.select()
			return false
		}
	}
	var cid2 = document.form.txtcidade.value
	var cid3 = cid2
	var tcid = document.form.txtcidade.value.length
	for (x= 0; x <tcid; x ++) {
		cid2 = cid3.replace(' ', '')
		cid3 = cid2
	}
	var tcid2 = cid3.length
	if (tcid2 < '3'){
		window.alert('CIDADE INCOMPLETA !')
		document.form.txtcidade.focus()
		document.form.txtcidade.select()
		return false
	}
// validação do cep
	var caratxtcep = ",;#!?ç~%^*+{}[]\)(><|=QWERTYUIOPASDFGHJKLZXCVBNM|qwertyuiopasdfghjkl;zxcvbnm'´"
	if (document.form.txtcep.value == '') {
		window.alert('DIGITE O CEP !')
		document.form.txtcep.focus()
		return false
	}
	for (i = 0; i <caratxtcep.length; i ++) {
		astxtcep = caratxtcep.charAt(i)
		if (document.form.txtcep.value.indexOf(astxtcep, 0) > -1) {
			window.alert('CEP INVÁLIDO !')
			document.form.txtcep.focus()
			document.form.txtcep.select()
			return false
		}
        }
	var txtcep2 = document.form.txtcep.value
	var txtcep3 = txtcep2
	var ttxtcep = document.form.txtcep.value.length
	for(x = 0; x <ttxtcep; x ++) {
		txtcep2 = txtcep3.replace(' ', '')
		txtcep3 = txtcep2
	}
	var ttxtcep2 = txtcep3.length
	if (ttxtcep2 < '8'){
		window.alert('CEP INCOMPLETO !')
		document.form.txtcep.focus()
		document.form.txtcep.select()
		return false
	}
// validação da unidade federal
	if (document.form.cmbfederal.disabled == false) {
		var veri = document.form.cmbfederal.selectedIndex
		if (document.form.cmbfederal.options[veri].value == '') {
			window.alert('SELECIONE A UNIDADE FEDERAL !')
			document.form.cmbfederal.focus()
			return false
		}
	}
// validação do pais
	var veri = document.form.cmbpais.selectedIndex
	if (document.form.cmbpais.options[veri].value == '') {
		window.alert('SELECIONE O PAÍS !')
		document.form.cmbpais.focus()
		return false
	}
// validação do ddd residencial
	var caractxtddd1 = ":,;!?ç~%^+{}[]\)(><|=|QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjkl;zxcvbnm'´"
	if (document.form.txtddd1.value != '') {
		for (i = 0 ; i <caractxtddd1.length; i ++) {
			asctxtddd1 = caractxtddd1.charAt(i)
			if (document.form.txtddd1.value.indexOf(asctxtddd1, 0) > -1) {
				window.alert('DDD RESIDENCIAL INVÁLIDO !')
				document.form.txtddd1.focus()
				document.form.txtddd1.select()
				return false
			}
		}
		var txtddd12   = document.form.txtddd1.value
		var txtddd13   = txtddd12
		var ttxtddd1   = document.form.txtddd1.value.length
		for (x = 0; x <ttxtddd1; x ++) {
			txtddd12 = txtddd13.replace(' ', '')
			txtddd13 = txtddd12
		}
		var ttxtddd12 = txtddd13.length
		if (ttxtddd12 < '2') {
			window.alert('DDD RESIDENCIAL INCOMPLETO !')
			document.form.txtddd1.focus()
			document.form.txtddd1.select()
			return false
		}
	}
// validação do txttelefone residencial
	if (document.form.txttelefone1.value == '' && document.form.txttelefone2.value == '') {
		alert('DIGITE O TELEFONE RESIDENCIAL OU TELEFONE CELULAR !')
		document.form.txttelefone1.focus()
		document.form.txttelefone1.select()
		return false
	}
	var caractxttelefone1 = "-:,;&#$®!?ç~%^*+{}[]\)(><|=|QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjkl;zxcvbnmÜÖÄ'´"
	if (document.form.txttelefone1.value != '') {
		for (i = 0; i <caractxttelefone1.length; i ++) {
			asctxttelefone1 = caractxttelefone1.charAt(i)
			if (document.form.txttelefone1.value.indexOf(asctxttelefone1, 0) > -1) {
				window.alert('TELEFONE RESIDENCIAL INVÁLIDO !')
				document.form.txttelefone1.focus()
				document.form.txttelefone1.select()
				return false
			}
		}
		var txttelefone12 = document.form.txttelefone1.value
		var txttelefone13 = txttelefone12
		var ttxttelefone1 = document.form.txttelefone1.value.length
		for (x = 0; x <ttxttelefone1; x ++) {
			txttelefone12 = txttelefone13.replace(' ', '')
			txttelefone13 = txttelefone12
		}
		ttxttelefone12 = txttelefone13.length
		if (ttxttelefone12 < '7') {
			window.alert('TELEFONE RESIDENCIAL INCOMPLETO !')
			document.form.txttelefone1.focus()
			document.form.txttelefone1.select()
			return false
		}
	}
//Validacao Celular
	var caractxttelefone2 = "-:,;&#$®!?ç~%^*+{}[]\)(><|=|QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjkl;zxcvbnmÜÖÄ'´"
	if (document.form.txttelefone2.value != '') {
		for (i = 0; i <caractxttelefone2.length; i ++) {
			asctxttelefone2 = caractxttelefone2.charAt(i)
			if (document.form.txttelefone2.value.indexOf(asctxttelefone2, 0) > -1) {
				window.alert('TELEFONE CELULAR INVÁLIDO !')
				document.form.txttelefone2.focus()
				document.form.txttelefone2.select()
				return false
			}
		}
		var txttelefone22 = document.form.txttelefone2.value
		var txttelefone23 = txttelefone22
		var ttxttelefone2 = document.form.txttelefone2.value.length
		for (x = 0; x <ttxttelefone2; x ++) {
			txttelefone22 = txttelefone23.replace(' ', '')
			txttelefone23 = txttelefone22
		}
		ttxttelefone22 = txttelefone23.length
		if (ttxttelefone22 < '7') {
			window.alert('TELEFONE CELULAR INCOMPLETO !')
			document.form.txttelefone2.focus()
			document.form.txttelefone2.select()
			return false
		}
	}
// validação do ddd fax
	var caractxtddd2 = "-:,;!?ç~%^+{}[]\)(><|=|QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjkl;zxcvbnm'´"
	if (document.form.txtddd2.value != '') {
		for (i = 0 ; i <caractxtddd2.length; i ++) {
			asctxtddd2 = caractxtddd2.charAt(i)
			if (document.form.txtddd2.value.indexOf(asctxtddd2, 0) > -1) {
				window.alert('DDD CELULAR INVÁLIDO !')
				document.form.txtddd2.focus()
				document.form.txtddd2.select()
				return false
			}
		}
		var txtddd22   = document.form.txtddd2.value
		var txtddd23   = txtddd22
		var ttxtddd2   = document.form.txtddd2.value.length
		for (x = 0; x <ttxtddd2; x ++) {
			txtddd22 = txtddd23.replace(' ', '')
			txtddd23 = txtddd22
		}
		var ttxtddd22 = txtddd23.length
		if (ttxtddd22 < '2') {
			window.alert('DDD CELULAR INCOMPLETO !')
			document.form.txtddd2.focus()
			document.form.txtddd2.select()
			return false
		}
	}
// validação do pais
	var veri = document.form.cmbpais.selectedIndex
	if (document.form.cmbpais.options[veri].value == '') {
		window.alert('SELECIONE O PAÍS !')
		document.form.cmbpais.focus()
		return false
	}
// validação do departamento
	var veri = document.form.cmbdepartamento.selectedIndex
	if (document.form.cmbdepartamento.options[veri].value == '') {
		window.alert('SELECIONE O DEPARTAMENTO !')
		document.form.cmbdepartamento.focus()
		return false
        }
// validação do Cargo
	var veri = document.form.cmbcargo.selectedIndex
	if (document.form.cmbcargo.options[veri].value == '') {
		window.alert('SELECIONE O CARGO !')
		document.form.cmbcargo.focus()
		return false
	}
// validação do Salário PD
	var caractxt_salario_pd2 = ":;!?ç~%^+{}[]\)(><|=|QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjkl;zxcvbnm'´"
	if (document.form.txt_salario_pd.value != '') {
		for (i = 0; i <caractxt_salario_pd2.length; i ++) {
			asctxt_salario_pd2 = caractxt_salario_pd2.charAt(i)
			if (document.form.txt_salario_pd.value.indexOf(asctxt_salario_pd2, 0) > -1) {
				window.alert('SALÁRIO PD INVÁLIDO !')
				document.form.txt_salario_pd.focus()
				document.form.txt_salario_pd.select()
				return false
			}
		}

		if (asctxt_salario_pd2 < '4') {
			window.alert('SALÁRIO PD INCOMPLETO !')
			document.form.txt_salario_pd.focus()
			document.form.txt_salario_pd.select()
			return false
		}
	}
// validação do Salário PF
	var caractxt_salario_pf2 = ":;!?ç~%^+{}[]\)(><|=|QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjkl;zxcvbnm'´"
	if (document.form.txt_salario_pf.value != '') {
		for (i = 0; i <caractxt_salario_pf2.length; i ++) {
			asctxt_salario_pf2 = caractxt_salario_pf2.charAt(i)
			if (document.form.txt_salario_pf.value.indexOf(asctxt_salario_pf2, 0) > -1) {
				window.alert('SALÁRIO PF INVÁLIDO !')
				document.form.txt_salario_pf.focus()
				document.form.txt_salario_pf.select()
				return false
			}
		}

		if (asctxt_salario_pf2 < '4') {
			window.alert('SALÁRIO PF INCOMPLETO !')
			document.form.txt_salario_pf.focus()
			document.form.txt_salario_pf.select()
			return false
		}
	}
// validação do Prêmio
	var caractxt_premio2 = ":;!?ç~%^+{}[]\)(><|=|QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjkl;zxcvbnm'´"
	if (document.form.txt_premio.value != '') {
		for (i = 0; i <caractxt_premio2.length; i ++) {
			asctxt_premio2 = caractxt_premio2.charAt(i)
			if (document.form.txt_premio.value.indexOf(asctxt_premio2, 0) > -1) {
				window.alert('PRÊMIO INVÁLIDO !')
				document.form.txt_premio.focus()
				document.form.txt_premio.select()
				return false
			}
		}

		if (asctxt_premio2 < '4') {
			window.alert('PRÊMIO INCOMPLETO !')
			document.form.txt_premio.focus()
			document.form.txt_premio.select()
			return false
		}
	}
// validação da unidade federal
	if (document.form.cmbfederal.disabled == false) {
		var veri = document.form.cmbfederal.selectedIndex
		if (document.form.cmbfederal.options[veri].value == '') {
			window.alert('SELECIONE A UNIDADE FEDERAL !')
			document.form.cmbfederal.focus()
			return false
		}
	}
//Validação da data de Admissão
	if(document.form.txtdataadmissao.value == ''){
		alert("DIGITE A DATA DE ADMISSÃO !")
		document.form.txtdataadmissao.focus()
		return false
	}
	invalidChars="abcdefghijklmnoprstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ~!@#$%^&*_+=`[]}{':;?.>,<\|-'"+'"'
	for(i = 0 ; i< invalidChars.length; i++) {
		BadChars = invalidChars.charAt(i)
		if(document.form.txtdataadmissao.value.indexOf(BadChars,0)>-1){
			alert("DATA DE ADMISSÃO INVÁLIDA !")
			document.form.txtdataadmissao.focus()
			document.form.txtdataadmissao.select()
			return false
		}
	}
	if(document.form.txtdataadmissao.value.length < '10' || document.form.txtdataadmissao.value.substr(2,1) != '/' || document.form.txtdataadmissao.value.substr(5,1) != '/') {
		alert('DATA DE ADMISSÃO INCOMPLETA !')
		document.form.txtdataadmissao.select()
		document.form.txtdataadmissao.focus()
		return false
	}
	var cont = 0
	for(i = 0; i < document.form.txtdataadmissao.value.length ; i++){
		if (document.form.txtdataadmissao.value.charAt(i) == '/'){
			cont = cont + 1
		}
	}
	if(cont > 2){
		alert('DATA DE ADMISSÃO INCOMPLETA !')
		document.form.txtdataadmissao.focus()
		document.form.txtdataadmissao.select()
		return false
	}
	var nDia = document.form.txtdataadmissao.value.substring(0, 2)
	var nMes = document.form.txtdataadmissao.value.substring(3, 5) - 1
	var nAno = document.form.txtdataadmissao.value.substring(6, 10)
	var newDate = new Date(nAno, nMes, nDia)

	if (newDate.getDate() != nDia){
		alert('DATA INVÁLIDA !')
		document.form.txtdataadmissao.focus()
		document.form.txtdataadmissao.select()
		return false
	}else if (newDate.getMonth() != nMes){
		window.alert('DATA INVÁLIDA !')
		document.form.txtdataadmissao.focus()
		document.form.txtdataadmissao.select()
		return false
	}
// Validação da data de Demissão
	if(typeof(document.form.cmbstatus == 'object') && (document.form.cmbstatus.value == 3)) {
//Validação da data de Demissão
		if(document.form.txtdatademissao.value == '') {
			alert('DIGITE A DATA DE DEMISSÃO !')
			document.form.txtdatademissao.focus()
			document.form.txtdatademissao.select()
			return false
		}
		invalidChars = "abcdefghijklmnoprstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ~!@#$%^&*_+=`[]}{':;?.>,<\|-'"+'"'
		for(i = 0 ; i< invalidChars.length; i++) {
			BadChars = invalidChars.charAt(i)
			if(document.form.txtdatademissao.value.indexOf(BadChars,0)>-1){
				alert('DATA DE DEMISSÃO INVÁLIDA !')
				document.form.txtdatademissao.focus()
				document.form.txtdatademissao.select()
				return false
			}
		}
		if(document.form.txtdatademissao.value.length < '10' || document.form.txtdatademissao.value.substr(2, 1) != '/' || document.form.txtdatademissao.value.substr(5, 1) != '/'){
			alert('DATA DE DEMISSÃO INCOMPLETA !')
			document.form.txtdatademissao.select()
			document.form.txtdatademissao.focus()
			return false
		}
		var cont = 0
		for(i=0 ; i < document.form.txtdatademissao.value.length ; i++){
			if (document.form.txtdatademissao.value.charAt(i) == '/'){
				cont = cont + 1
			}
		}
		if(cont > 2){
			alert('DATA DE DEMISSÃO INCOMPLETA !')
			document.form.txtdatademissao.focus()
			document.form.txtdatademissao.select()
			return false
		}
		var nDia = document.form.txtdatademissao.value.substring(0, 2)
		var nMes = document.form.txtdatademissao.value.substring(3, 5) - 1
		var nAno = document.form.txtdatademissao.value.substring(6, 10)
		var newDate = new Date(nAno, nMes, nDia)

		if (newDate.getDate() != nDia){
			alert('DATA INVÁLIDA !')
			document.form.txtdatademissao.focus()
			document.form.txtdatademissao.select()
			return false
		}else if (newDate.getMonth() != nMes){
			window.alert('DATA INVÁLIDA !')
			document.form.txtdatademissao.focus()
			document.form.txtdatademissao.select()
			return false
		}
	}
	return limpeza_moeda('form', 'txt_salario_pd, txt_salario_pf, txt_premio, ')
}

function valida_cpf(nro) {
	var soma1 = resto = dig1 = dig2 = 0
	for (var i = 10; i > 1; i --) {
		soma1 += parseInt(nro.substr(nro.length-i-1, 1)) * i
	}
	resto = soma1 % 11
	switch (resto) {
		case 0:
		case 1:
			dig1 = 0
			break
		default:
			dig1 = 11 - resto
			break
	}
	soma1 = 0
	for (i = 11; i > 2; i --) {
		soma1 += parseInt(nro.substr(nro.length-i,1)) * i
	}
	soma1 = soma1 + dig1 * 2
	resto = soma1 % 11
	switch (resto) {
		case 0:
		case 1:
			dig2 = 0
			break
		default:
			dig2 = 11 - resto
			break
	}
	var controle = String(dig1) + String(dig2)
	if (controle == nro.substr(9))
		return true
	else
		return false
}

function limpeza_moeda(formulario, campos) {
var x, y, elemento, objeto1, objeto2, indice = 0, auxiliar = 0, posicao = 0, matriz = new Array()
for (x = 0; x < campos.length; x ++) {
    if (campos.substr(x, 1) == ',') {
			if (auxiliar == 1) {
					matriz[indice] = campos.substr(posicao, auxiliar)
					auxiliar = 0
					indice   ++
			}else if (auxiliar > 1) {
					matriz[indice] = campos.substr(posicao - auxiliar + 1, auxiliar)
					auxiliar = 0
					indice   ++
			}
		}else {
			auxiliar ++
		}
		posicao = x
	}
	for (x = 0; x < indice; x ++) {
		elemento = eval('document.'+formulario+'.'+matriz[x]+'')
		objeto1  = eval('document.'+formulario+'.'+matriz[x]+'.value')
		objeto2  =  objeto1
			for (y = 0; y < objeto1.length; y ++) {
				objeto1  = objeto2.replace('.', '')
				objeto2  = objeto1
			}
			objeto1  = objeto2.replace(',', '.')
			objeto2  = objeto1
	elemento.value = objeto1
	}
}
