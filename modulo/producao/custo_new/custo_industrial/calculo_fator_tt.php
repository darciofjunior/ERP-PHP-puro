<?
require('../../../../lib/segurancas.php');
session_start('funcionarios');
if($tela == 1) {//Veio da tela de Todos os P.A.
    segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_todos.php', '../../../../');
}else if($tela == 2) {//Veio da tela dos P.A. do Tipo Esp.
    segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_esp.php', '../../../../');
}
?>
<html>
<title>.:: Cálculo Fator T.T. ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript'>
//Cálculo para Fator de Tratamento Térmico Bits Quad / Retang
function calculo1() {
//Variáveis
	var diam_materia_prima1 = document.form.txt_diam_materia_prima1.value
	var ladoa_materia_prima = document.form.txt_ladoa_materia_prima.value
	var ladoa_peca_acabada = document.form.txt_ladoa_peca_acabada.value
	var ladob_materia_prima = document.form.txt_ladob_materia_prima.value
	var ladob_peca_acabada = document.form.txt_ladob_peca_acabada.value
	var qtde_pcs_corte = document.form.txt_qtde_pcs_corte.value

	if(diam_materia_prima1 != '') {
		diam_materia_prima1 = eval(strtofloat(diam_materia_prima1))
	}else {
		diam_materia_prima1 = 0
	}

	if(ladoa_materia_prima != '') {
		ladoa_materia_prima = eval(strtofloat(ladoa_materia_prima))
	}else {
		ladoa_materia_prima = 1
	}

	if(ladoa_peca_acabada != '') {
		ladoa_peca_acabada = eval(strtofloat(ladoa_peca_acabada))
	}else {
		ladoa_peca_acabada = 0
	}

	if(ladob_materia_prima != '') {
		ladob_materia_prima = eval(strtofloat(ladob_materia_prima))
	}else {
		ladob_materia_prima = 1
	}

	if(ladob_peca_acabada != '') {
		ladob_peca_acabada = eval(strtofloat(ladob_peca_acabada))
	}else {
		ladob_peca_acabada = 0
	}

	if(qtde_pcs_corte != '') {
		qtde_pcs_corte = eval(strtofloat(qtde_pcs_corte))
	}else {
		qtde_pcs_corte = 0
	}
	
//Resultado Final
	if(diam_materia_prima1 == 0) {
		fator_conversao_tt1 = (ladoa_peca_acabada + 0.4) / ladoa_materia_prima * (ladob_peca_acabada + 0.4) / ladob_materia_prima * qtde_pcs_corte
	}else {
		fator_conversao_tt1 = ((ladoa_peca_acabada + 0.4) * (ladob_peca_acabada + 0.4)) / (Math.PI * Math.pow(diam_materia_prima1, 2) / 4) * qtde_pcs_corte
	}
	
	
	if(fator_conversao_tt1 == 'Infinity') {
		fator_conversao_tt1 = 0
	}
	
	document.form.txt_fator_conversao_tt1.value = fator_conversao_tt1
	document.form.txt_fator_conversao_tt1.value = arred(document.form.txt_fator_conversao_tt1.value, 2, 1)
}

//Cálculo para Fator de Tratamento Térmico Bits Redondo
function calculo2() {
	var diam_materia_prima2 = document.form.txt_diam_materia_prima2.value
	var diam_peca_acabada = document.form.txt_diam_peca_acabada.value

	if(diam_materia_prima2 != '') {
		diam_materia_prima2 = eval(strtofloat(diam_materia_prima2))
	}else {
		diam_materia_prima2 = 0
	}

	if(diam_peca_acabada != '') {
		diam_peca_acabada = eval(strtofloat(diam_peca_acabada))
	}else {
		diam_peca_acabada = 0
	}

	if((diam_peca_acabada + 0.4) > diam_materia_prima2) {
		fator_conversao_tt2 = 1
	}else {
		fator_conversao_tt2 = Math.pow(diam_peca_acabada + 0.4, 2) / Math.pow(diam_materia_prima2, 2)
	}
	
	if(fator_conversao_tt2 == 'Infinity') {
		fator_conversao_tt2 = 0
	}
	
	document.form.txt_fator_conversao_tt2.value = fator_conversao_tt2
	document.form.txt_fator_conversao_tt2.value = arred(document.form.txt_fator_conversao_tt2.value, 2, 1)
}

//Cálculo para M.P. Redonda p/ Fabricação de Bits Quad / Retang
function calculo3() {
	var l1 = document.form.txt_l1.value
	var l2 = document.form.txt_l2.value
	var diam_minimo = document.form.txt_diam_minimo.value
	var diam_minimo2 = document.form.txt_diam_minimo2.value

	if(l1 != '') {
		l1 = eval(strtofloat(l1))
	}else {
		l1 = 0
	}

	if(l2 != '') {
		l2 = eval(strtofloat(l2))
	}else {
		l2 = 0
	}

	if(diam_minimo != '') {
		diam_minimo = eval(strtofloat(diam_minimo))
	}else {
		diam_minimo = 0
	}

	if(diam_minimo2 != '') {
		diam_minimo2 = eval(strtofloat(diam_minimo2))
	}else {
		diam_minimo2 = 0
	}

	if(l1 == 0) {
		diam_minimo = 0
	}else {
		diam_minimo = Math.sqrt(Math.pow(l1 + 1, 2) + Math.pow(l2 + 1, 2))
	}

	if(diam_minimo == 'Infinity') {
		diam_minimo = 0
	}
	document.form.txt_diam_minimo.value = diam_minimo
	document.form.txt_diam_minimo.value = arred(document.form.txt_diam_minimo.value, 2, 1)

	if(l1 == 0) {
		diam_minimo2 = 0
	}else {
		diam_minimo2 = Math.sqrt(Math.pow(l1 + 0.5, 2) + Math.pow(l2 + 0.5, 2))
	}

	if(diam_minimo2 == 'Infinity') {
		diam_minimo2 = 0
	}
	document.form.txt_diam_minimo2.value = diam_minimo2
	document.form.txt_diam_minimo2.value = arred(document.form.txt_diam_minimo2.value, 2, 1)
}

function verificar() {
//Cálculo p/ Fator de Tratamento Térmico Bits QUAD / RETANG
	if(document.form.opt_opcao[0].checked == true ) {
//Parte 1
		document.form.txt_diam_materia_prima1.disabled = false
		document.form.txt_ladoa_materia_prima.disabled = false
		document.form.txt_ladoa_peca_acabada.disabled = false
		document.form.txt_ladob_materia_prima.disabled = false
		document.form.txt_ladob_peca_acabada.disabled = false
		document.form.txt_qtde_pcs_corte.disabled = false
//Parte 2
		document.form.txt_diam_materia_prima2.disabled = true
		document.form.txt_diam_peca_acabada.disabled = true
		document.form.txt_diam_materia_prima2.value = ''
		document.form.txt_diam_peca_acabada.value = ''
		document.form.txt_fator_conversao_tt2.value = ''
//Parte 3
		document.form.txt_l1.disabled = true
		document.form.txt_l2.disabled = true
		document.form.txt_l1.value = ''
		document.form.txt_l2.value = ''
		document.form.txt_diam_minimo.value = ''
		document.form.txt_diam_minimo2.value = ''
		
		document.form.opcao[0].checked = true
		habilitar_parte1()
//Cálculo p/ Fator de Tratamento Térmico Bits REDONDO
	}else if(document.form.opt_opcao[1].checked == true ) {
//Parte 1
		document.form.txt_diam_materia_prima1.disabled = true
		document.form.txt_ladoa_materia_prima.disabled = true
		document.form.txt_ladoa_peca_acabada.disabled = true
		document.form.txt_ladob_materia_prima.disabled = true
		document.form.txt_ladob_peca_acabada.disabled = true
		document.form.txt_qtde_pcs_corte.disabled = true
		document.form.txt_diam_materia_prima2.disabled = true
		document.form.txt_diam_peca_acabada.disabled = true
		document.form.txt_diam_materia_prima1.value = ''
		document.form.txt_ladoa_materia_prima.value = ''
		document.form.txt_ladoa_peca_acabada.value = ''
		document.form.txt_ladob_materia_prima.value = ''
		document.form.txt_ladob_peca_acabada.value = ''
		document.form.txt_qtde_pcs_corte.value = ''
		document.form.txt_diam_materia_prima2.value = ''
		document.form.txt_diam_peca_acabada.value = ''
		document.form.txt_fator_conversao_tt1.value = ''
//Parte 2
		document.form.txt_diam_materia_prima2.disabled = false
		document.form.txt_diam_peca_acabada.disabled = false
//Parte 3
		document.form.txt_l1.disabled = true
		document.form.txt_l2.disabled = true
		document.form.txt_l1.value = ''
		document.form.txt_l2.value = ''
		document.form.txt_diam_minimo.value = ''
		document.form.txt_diam_minimo2.value = ''
//Cálculo p/ Tratamento Térmico Bits QUAD / RETANG
	}else if(document.form.opt_opcao[2].checked == true ) {
//Parte 1
		document.form.txt_diam_materia_prima1.disabled = true
		document.form.txt_ladoa_materia_prima.disabled = true
		document.form.txt_ladoa_peca_acabada.disabled = true
		document.form.txt_ladob_materia_prima.disabled = true
		document.form.txt_ladob_peca_acabada.disabled = true
		document.form.txt_qtde_pcs_corte.disabled = true
		document.form.txt_diam_materia_prima2.disabled = true
		document.form.txt_diam_peca_acabada.disabled = true
		document.form.txt_diam_materia_prima1.value = ''
		document.form.txt_ladoa_materia_prima.value = ''
		document.form.txt_ladoa_peca_acabada.value = ''
		document.form.txt_ladob_materia_prima.value = ''
		document.form.txt_ladob_peca_acabada.value = ''
		document.form.txt_qtde_pcs_corte.value = ''
		document.form.txt_diam_materia_prima2.value = ''
		document.form.txt_diam_peca_acabada.value = ''
		document.form.txt_fator_conversao_tt1.value = ''
//Parte 2
		document.form.txt_diam_materia_prima2.disabled = true
		document.form.txt_diam_peca_acabada.disabled = true
		document.form.txt_diam_materia_prima2.value = ''
		document.form.txt_diam_peca_acabada.value = ''
		document.form.txt_fator_conversao_tt2.value = ''
//Parte 3
		document.form.txt_l1.disabled = false
		document.form.txt_l2.disabled = false
	}
}

function habilitar_parte1() {
//Só é válido se a primeira opção estiver checada
	if(document.form.opt_opcao[0].checked == true) {
		if(document.form.opcao[0].checked == true) {
//Desabilita aqui os campos que tem q estar habilitado para a parte B
			document.form.txt_diam_materia_prima1.disabled = true
//Habilita aqui os campos que tem q estar habilitado para a parte A
			document.form.txt_ladoa_materia_prima.disabled = false
			document.form.txt_ladob_materia_prima.disabled = false
		}else {
//Habilita aqui os campos que tem q estar habilitado para a parte B
			document.form.txt_diam_materia_prima1.disabled = false
//Desabilita aqui os campos que tem q estar habilitado para a parte A
			document.form.txt_ladoa_materia_prima.disabled = true
			document.form.txt_ladob_materia_prima.disabled = true
		}
//Limpa os Campos
		document.form.txt_diam_materia_prima1.value = ''
		document.form.txt_ladoa_materia_prima.value = ''
		document.form.txt_ladob_materia_prima.value = ''
		document.form.txt_qtde_pcs_corte.value = ''
		document.form.txt_ladoa_peca_acabada.value = ''
		document.form.txt_ladob_peca_acabada.value = ''
		document.form.txt_fator_conversao_tt1.value = ''
	}
}

function atualizar() {
//Cálculo p/ Fator de Tratamento Térmico Bits QUAD / RETANG
	if(document.form.opt_opcao[0].checked == true ) {
		if(document.form.txt_fator_conversao_tt1.value != '') {
			var fator_conversao_tt1 = eval(strtofloat(document.form.txt_fator_conversao_tt1.value))
//Aqui eu verifico se o valor é > 1, caso sim o valor = 1, o valor nunca pode ser > q 1
			if(fator_conversao_tt1 > 1) {
				top.document.form.txt_fator_tt5.value = '1,00'
			}else {
				top.document.form.txt_fator_tt5.value = document.form.txt_fator_conversao_tt1.value
			}
		}
//Cálculo p/ Fator de Tratamento Térmico Bits REDONDO
	}else if(document.form.opt_opcao[1].checked == true ) {
		if(document.form.txt_fator_conversao_tt2.value != '') {
			top.document.form.txt_fator_tt5.value = document.form.txt_fator_conversao_tt2.value
		}
	}
}
</Script>
<body onload="verificar()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>">
  <table border="0" width="645" align="center" cellspacing ='1' cellpadding='1'>
    <tr class='linhadestaque'>
      <td colspan="3" align='left'> <input type="radio" name="opt_opcao" onclick="verificar()" id="verificar1" checked>
        <label for="verificar1"> <font color='#FFFFFF' size='-1'>Cálculo p/ Fator
        de Trat. Térmico Bits QUADRADO / RETANGULAR</font> </label> <font color='#FFFFFF' size='-1'>&nbsp;
        </font></td>
    </tr>
    <tr class='linhacabecalho'>
      <td colspan="3" align='left'>
        <input type="radio" name="opcao" onclick="habilitar_parte1()" id="opcao1" checked>
        <label for="opcao1">
	  <font color='#FFFFFF' size='-1'>
	    Matéria Prima QUAD / RETANG
	  </font>
	</label>
	&nbsp;&nbsp;&nbsp;
	<input type="radio" name="opcao" onclick="habilitar_parte1()" id="opcao2">
        <label for="opcao2">
	  <font color='#FFFFFF' size='-1'>
	    Matéria Prima REDONDA
	  </font>
	</label>
      </td>
    </tr>
    <tr class="linhanormal">
      <td>&Oslash; Da Matéria Prima:
      <td>Lado A Matéria Prima: </td>
      <td>Lado B Matéria Prima: </td>
      
    </tr>
    <tr class="linhanormal">
      <td><input type="text" name="txt_diam_materia_prima1" onKeyUp="verifica(this, 'moeda_especial', 2, '', event);calculo1()" class="caixadetexto">
      <td><input type="text" name="txt_ladoa_materia_prima" onKeyUp="verifica(this, 'moeda_especial', 2, '', event);calculo1()" class="caixadetexto"></td>     
      <td width="189"><input type="text" name="txt_ladob_materia_prima" onKeyUp="verifica(this, 'moeda_especial', 2, '', event);calculo1()" class="caixadetexto"></td>      
    </tr>
    <tr class="linhanormal">
      <td>Qtd Pçs / Corte: </td>
      <td>Lado A Peça Acabada: </td>
      <td>Lado B Peça Acabada: </td>
    </tr>
    <tr class="linhanormal">
      <td width="159"><input type="text" name="txt_qtde_pcs_corte" onKeyUp="verifica(this, 'aceita', 'numeros', '', event);if(this.value != '') {this.value = Math.round(this.value)};calculo1()" class="caixadetexto"></td>
      <td><input type="text" name="txt_ladoa_peca_acabada" onKeyUp="verifica(this, 'moeda_especial', 2, '', event);calculo1()" class="caixadetexto"></td>
      <td width="167"><input type="text" name="txt_ladob_peca_acabada" onKeyUp="verifica(this, 'moeda_especial', 2, '', event);calculo1()" class="caixadetexto"></td>
    </tr>
    <tr class="linhanormal">
      <td width="189">Fator de Conversão T.T.:
      <td colspan="2"><input type="text" name="txt_fator_conversao_tt1" class="caixadetexto" disabled>
      </td>
    </tr>
    <tr class='linhadestaque'> 
      <td colspan="3" align='left'> <input type="radio" name="opt_opcao" onclick="verificar()" id="verificar2">
       <label for="verificar2"> <font color='#FFFFFF' size='-1'>Cálculo p/ Fator de Tratamento Térmico 
        Bits REDONDO </font> <label for="verificar2"> </label> <font color='#FFFFFF' size='-1'>&nbsp;
        </font></label></td>
    </tr>
    <tr class="linhanormal">
      <td>&Oslash; Da Matéria Prima:
      <td>&Oslash; Da Peça Acabada: </td>
      <td>Fator de Conversão T.T.: </td>
    </tr>
    <tr class="linhanormal">
      <td width="189"><input type="text" name="txt_diam_materia_prima2" onKeyUp="verifica(this, 'moeda_especial', 2, '', event);calculo2()" class="caixadetexto" disabled>
      <td width="167"><input type="text" name="txt_diam_peca_acabada" onKeyUp="verifica(this, 'moeda_especial', 2, '', event);calculo2()" class="caixadetexto" disabled>
      </td>
      <td width="159"><input type="text" name="txt_fator_conversao_tt2" class="caixadetexto" disabled></td>
    </tr>
    <tr class='linhadestaque'>
      <td colspan="3" align='left'> <input type="radio" name="opt_opcao" onclick="verificar()" id="verificar3">
       <label for="verificar3"> <font color='#FFFFFF' size='-1'>Cálculo de &Oslash; de Aço Redondo p/ Bits QUADRADO / RETANGULAR</font> <label for="verificar3"> </label> <font color='#FFFFFF' size='-1'>&nbsp;
        </font></label></td>
    </tr>
    <tr class="linhanormal">
      <td>L1 : &nbsp;&nbsp;&nbsp; <input type="text" name="txt_l1" onKeyUp="verifica(this, 'moeda_especial', 2, '', event);calculo3()" class="caixadetexto">
      <td colspan="2">L2:
        <input type="text" name="txt_l2" onKeyUp="verifica(this, 'moeda_especial', 2, '', event);calculo3()" class="caixadetexto"></td>
    </tr>
    <tr class="linhanormal">
      <td width="189">&Oslash; Min:
        <input type="text" name="txt_diam_minimo" class="caixadetexto" disabled>
      <td colspan="2"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="text" name="txt_diam_minimo2" class="caixadetexto" disabled>
      </td>
    </tr>
    <tr class="linhacabecalho">
      <td colspan="3" align="center"> <input type="button" name="cmd_limpar" value="Limpar" title="Limpar" class="botao" onclick="redefinir('document.form', 'LIMPAR');verificar()" style="color:#ff9900;">
        <input type="button" name="cmd_atualizar" value="Atualizar" title="Atualizar" class="botao" style="color:green" onclick="atualizar()">
      </td>
    </tr>
  </table>
</form>
</body>
</html>
