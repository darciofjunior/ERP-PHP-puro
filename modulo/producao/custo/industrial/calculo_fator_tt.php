<?
require('../../../../lib/segurancas.php');
if($tela == 1) {//Veio da tela de Todos os P.A.
    segurancas::geral('/erp/albafer/modulo/producao/custo/industrial/pa_componente_todos.php', '../../../../');
}else if($tela == 2) {//Veio da tela dos P.A. do Tipo Esp.
    segurancas::geral('/erp/albafer/modulo/producao/custo/industrial/pa_componente_esp.php', '../../../../');
}
?>
<html>
<title>.:: Cálculo Fator T.T. ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
//Cálculo para Fator de Tratamento Térmico Bits Quad / Retang
function calculo1() {
//Variáveis
    var diam_materia_prima1 = document.form.txt_diam_materia_prima1.value
    var ladoa_materia_prima = document.form.txt_ladoa_materia_prima.value
    var ladoa_peca_acabada  = document.form.txt_ladoa_peca_acabada.value
    var ladob_materia_prima = document.form.txt_ladob_materia_prima.value
    var ladob_peca_acabada  = document.form.txt_ladob_peca_acabada.value
    var qtde_pcs_corte      = document.form.txt_qtde_pcs_corte.value

    diam_materia_prima1 = (diam_materia_prima1 != '') ? eval(strtofloat(diam_materia_prima1)) : 0
    ladoa_materia_prima = (ladoa_materia_prima != '') ? eval(strtofloat(ladoa_materia_prima)) : 1
    ladoa_peca_acabada  = (ladoa_peca_acabada != '') ? eval(strtofloat(ladoa_peca_acabada)) : 0
    ladob_materia_prima = (ladob_materia_prima != '') ? eval(strtofloat(ladob_materia_prima)) : 1
    ladob_peca_acabada  = (ladob_peca_acabada != '') ? eval(strtofloat(ladob_peca_acabada)) : 0
    qtde_pcs_corte      = (qtde_pcs_corte != '') ? eval(strtofloat(qtde_pcs_corte)) : 0
    //Resultado Final ...
    if(diam_materia_prima1 == 0) {
        fator_conversao_tt1 = (ladoa_peca_acabada + 0.4) / ladoa_materia_prima * (ladob_peca_acabada + 0.4) / ladob_materia_prima * qtde_pcs_corte
    }else {
        fator_conversao_tt1 = ((ladoa_peca_acabada + 0.4) * (ladob_peca_acabada + 0.4)) / (Math.PI * Math.pow(diam_materia_prima1, 2) / 4) * qtde_pcs_corte
    }
    if(fator_conversao_tt1 == 'Infinity') fator_conversao_tt1 = 0
    document.form.txt_fator_conversao_tt1.value = fator_conversao_tt1
    document.form.txt_fator_conversao_tt1.value = arred(document.form.txt_fator_conversao_tt1.value, 2, 1)
}

//Cálculo para Fator de Tratamento Térmico Bits Redondo
function calculo2() {
    var diam_materia_prima2 = document.form.txt_diam_materia_prima2.value
    var diam_peca_acabada   = document.form.txt_diam_peca_acabada.value

    diam_materia_prima2 = (diam_materia_prima2 != '') ? eval(strtofloat(diam_materia_prima2)) : 0
    diam_peca_acabada   = (diam_peca_acabada != '') ? eval(strtofloat(diam_peca_acabada)) : 0

    if((diam_peca_acabada + 0.4) > diam_materia_prima2) {
        fator_conversao_tt2 = 1
    }else {
        fator_conversao_tt2 = Math.pow(diam_peca_acabada + 0.4, 2) / Math.pow(diam_materia_prima2, 2)
    }
    if(fator_conversao_tt2 == 'Infinity') fator_conversao_tt2 = 0
    document.form.txt_fator_conversao_tt2.value = fator_conversao_tt2
    document.form.txt_fator_conversao_tt2.value = arred(document.form.txt_fator_conversao_tt2.value, 2, 1)
}

//Cálculo para M.P. Redonda p/ Fabricação de Bits Quad / Retang
function calculo3() {
    var l1 = document.form.txt_l1.value
    var l2 = document.form.txt_l2.value
    var diam_minimo     = document.form.txt_diam_minimo.value
    var diam_minimo2    = document.form.txt_diam_minimo2.value

    l1 = (l1 != '') ? eval(strtofloat(l1)) : 0
    l2 = (l2 != '') ? eval(strtofloat(l2)) : 0
    diam_minimo     = (diam_minimo != '') ? eval(strtofloat(diam_minimo)) : 0
    diam_minimo2    = (diam_minimo2 != '') ? eval(strtofloat(diam_minimo2)) : 0
    diam_minimo     = (l1 == 0) ? 0 : Math.sqrt(Math.pow(l1 + 1, 2) + Math.pow(l2 + 1, 2))
    if(diam_minimo == 'Infinity') diam_minimo = 0
    document.form.txt_diam_minimo.value = diam_minimo
    document.form.txt_diam_minimo.value = arred(document.form.txt_diam_minimo.value, 2, 1)
    diam_minimo2 = (l1 == 0) ? 0 : Math.sqrt(Math.pow(l1 + 0.5, 2) + Math.pow(l2 + 0.5, 2))
    if(diam_minimo2 == 'Infinity') diam_minimo2 = 0
    document.form.txt_diam_minimo2.value = diam_minimo2
    document.form.txt_diam_minimo2.value = arred(document.form.txt_diam_minimo2.value, 2, 1)
}

function verificar() {
//Cálculo p/ Fator de Tratamento Térmico Bits QUAD / RETANG
    if(document.form.opt_opcao[0].checked == true ) {
        //I)
        //Habilita os Campos ...
        document.form.txt_diam_materia_prima1.disabled  = false
        document.form.txt_ladoa_materia_prima.disabled  = false
        document.form.txt_ladoa_peca_acabada.disabled   = false
        document.form.txt_ladob_materia_prima.disabled  = false
        document.form.txt_ladob_peca_acabada.disabled   = false
        document.form.txt_qtde_pcs_corte.disabled       = false
        //Layout de Habilitado ...
        document.form.txt_diam_materia_prima1.className = 'caixadetexto'
        document.form.txt_ladoa_materia_prima.className = 'caixadetexto'
        document.form.txt_ladoa_peca_acabada.className  = 'caixadetexto'
        document.form.txt_ladob_materia_prima.className = 'caixadetexto'
        document.form.txt_ladob_peca_acabada.className  = 'caixadetexto'
        document.form.txt_qtde_pcs_corte.className      = 'caixadetexto'
        //II)
        //Desabilita os Campos ...
        document.form.txt_diam_materia_prima2.disabled  = true
        document.form.txt_diam_peca_acabada.disabled    = true
        //Layout de Desabilitado ...
        document.form.txt_diam_materia_prima2.className = 'textdisabled'
        document.form.txt_diam_peca_acabada.className   = 'textdisabled'
        //LImpa os Campos ...
        document.form.txt_diam_materia_prima2.value     = ''
        document.form.txt_diam_peca_acabada.value       = ''
        document.form.txt_fator_conversao_tt2.value     = ''
        //III)
        //Desabilita os Campos ...
        document.form.txt_l1.disabled                   = true
        document.form.txt_l2.disabled                   = true
        //Desabilita os Campos ...
        document.form.txt_l1.className                  = 'textdisabled'
        document.form.txt_l2.className                  = 'textdisabled'
        document.form.txt_diam_minimo.className         = 'textdisabled'
        document.form.txt_diam_minimo2.className        = 'textdisabled'
        //Limpa os Campos ...
        document.form.txt_l1.value                      = ''
        document.form.txt_l2.value                      = ''
        document.form.txt_diam_minimo.value             = ''
        document.form.txt_diam_minimo2.value            = ''

        document.form.opcao[0].checked  = true
        habilitar_parte1()
//Cálculo p/ Fator de Tratamento Térmico Bits REDONDO
    }else if(document.form.opt_opcao[1].checked == true ) {
        //I)
        //Desabilita os Campos ...
        document.form.txt_diam_materia_prima1.disabled  = true
        document.form.txt_ladoa_materia_prima.disabled  = true
        document.form.txt_ladoa_peca_acabada.disabled   = true
        document.form.txt_ladob_materia_prima.disabled  = true
        document.form.txt_ladob_peca_acabada.disabled   = true
        document.form.txt_qtde_pcs_corte.disabled       = true
        document.form.txt_diam_materia_prima2.disabled  = true
        document.form.txt_diam_peca_acabada.disabled    = true
        //Layout de Desabilitado ...
        document.form.txt_diam_materia_prima1.className = 'textdisabled'
        document.form.txt_ladoa_materia_prima.className = 'textdisabled'
        document.form.txt_ladoa_peca_acabada.className  = 'textdisabled'
        document.form.txt_ladob_materia_prima.className = 'textdisabled'
        document.form.txt_ladob_peca_acabada.className  = 'textdisabled'
        document.form.txt_qtde_pcs_corte.className      = 'textdisabled'
        document.form.txt_diam_materia_prima2.className = 'textdisabled'
        document.form.txt_diam_peca_acabada.className   = 'textdisabled'
        //Limpa os Campos ...
        document.form.txt_diam_materia_prima1.value     = ''
        document.form.txt_ladoa_materia_prima.value     = ''
        document.form.txt_ladoa_peca_acabada.value      = ''
        document.form.txt_ladob_materia_prima.value     = ''
        document.form.txt_ladob_peca_acabada.value      = ''
        document.form.txt_qtde_pcs_corte.value          = ''
        document.form.txt_diam_materia_prima2.value     = ''
        document.form.txt_diam_peca_acabada.value       = ''
        document.form.txt_fator_conversao_tt1.value     = ''
        //II)
        //Habilita os Campos ...
        document.form.txt_diam_materia_prima2.disabled  = false
        document.form.txt_diam_peca_acabada.disabled    = false
        //Layout de Habilitado ...
        document.form.txt_diam_materia_prima2.className = 'caixadetexto'
        document.form.txt_diam_peca_acabada.className   = 'caixadetexto'
        //III)
        //Desabilita os Campos ...
        document.form.txt_l1.disabled                   = true
        document.form.txt_l2.disabled                   = true
        //Layout de Desabilitado ...
        document.form.txt_l1.className                  = 'textdisabled'
        document.form.txt_l2.className                  = 'textdisabled'
        //Limpa os Campos ...
        document.form.txt_l1.value                      = ''
        document.form.txt_l2.value                      = ''
        document.form.txt_diam_minimo.value             = ''
        document.form.txt_diam_minimo2.value            = ''
//Cálculo p/ Tratamento Térmico Bits QUAD / RETANG
    }else if(document.form.opt_opcao[2].checked == true ) {
        //I)
        //Desabilita os Campos ...
        document.form.txt_diam_materia_prima1.disabled  = true
        document.form.txt_ladoa_materia_prima.disabled  = true
        document.form.txt_ladoa_peca_acabada.disabled   = true
        document.form.txt_ladob_materia_prima.disabled  = true
        document.form.txt_ladob_peca_acabada.disabled   = true
        document.form.txt_qtde_pcs_corte.disabled       = true
        document.form.txt_diam_materia_prima2.disabled  = true
        document.form.txt_diam_peca_acabada.disabled    = true
        //Layout de Desabilitado ...
        document.form.txt_diam_materia_prima1.className = 'textdisabled'
        document.form.txt_ladoa_materia_prima.className = 'textdisabled'
        document.form.txt_ladoa_peca_acabada.className  = 'textdisabled'
        document.form.txt_ladob_materia_prima.className = 'textdisabled'
        document.form.txt_ladob_peca_acabada.className  = 'textdisabled'
        document.form.txt_qtde_pcs_corte.className      = 'textdisabled'
        document.form.txt_diam_materia_prima2.className = 'textdisabled'
        document.form.txt_diam_peca_acabada.className   = 'textdisabled'
        //Limpa os Campos ...
        document.form.txt_diam_materia_prima1.value     = ''
        document.form.txt_ladoa_materia_prima.value     = ''
        document.form.txt_ladoa_peca_acabada.value      = ''
        document.form.txt_ladob_materia_prima.value     = ''
        document.form.txt_ladob_peca_acabada.value      = ''
        document.form.txt_qtde_pcs_corte.value          = ''
        document.form.txt_diam_materia_prima2.value     = ''
        document.form.txt_diam_peca_acabada.value       = ''
        document.form.txt_fator_conversao_tt1.value     = ''
        //II)
        //Desabilita os Campos ...
        document.form.txt_diam_materia_prima2.disabled  = true
        document.form.txt_diam_peca_acabada.disabled    = true
        //Layout de Desabilitado ...
        document.form.txt_diam_materia_prima2.className = 'textdisabled'
        document.form.txt_diam_peca_acabada.className   = 'textdisabled'
        //Limpa os Campos ...
        document.form.txt_diam_materia_prima2.value     = ''
        document.form.txt_diam_peca_acabada.value       = ''
        document.form.txt_fator_conversao_tt2.value     = ''
        //III)
        //Habilita os Campos ...
        document.form.txt_l1.disabled                   = false
        document.form.txt_l2.disabled                   = false
        //Layout de Habilitado ...
        document.form.txt_l1.className                  = 'caixadetexto'
        document.form.txt_l2.className                  = 'caixadetexto'
    }
}

function habilitar_parte1() {
    //Só é válido se a primeira opção estiver checada
    if(document.form.opt_opcao[0].checked == true) {
        if(document.form.opcao[0].checked == true) {
            //Desabilita aqui os campos que tem q estar habilitado para a parte B
            document.form.txt_diam_materia_prima1.disabled  = true
            document.form.txt_diam_materia_prima1.className = 'textdisabled'
            //Habilita aqui os campos que tem q estar habilitado para a parte A
            document.form.txt_ladoa_materia_prima.disabled  = false
            document.form.txt_ladob_materia_prima.disabled  = false
            document.form.txt_ladoa_materia_prima.className = 'caixadetexto'
            document.form.txt_ladob_materia_prima.className = 'caixadetexto'
        }else {
            //Habilita aqui os campos que tem q estar habilitado para a parte B
            document.form.txt_diam_materia_prima1.disabled  = false
            document.form.txt_diam_materia_prima1.className = 'caixadetexto'
            //Desabilita aqui os campos que tem q estar habilitado para a parte A
            document.form.txt_ladoa_materia_prima.disabled  = true
            document.form.txt_ladob_materia_prima.disabled  = true
            document.form.txt_ladoa_materia_prima.className = 'textdisabled'
            document.form.txt_ladob_materia_prima.className = 'textdisabled'
        }
        //Limpa os Campos
        document.form.txt_diam_materia_prima1.value = ''
        document.form.txt_ladoa_materia_prima.value = ''
        document.form.txt_ladob_materia_prima.value = ''
        document.form.txt_qtde_pcs_corte.value      = ''
        document.form.txt_ladoa_peca_acabada.value  = ''
        document.form.txt_ladob_peca_acabada.value  = ''
        document.form.txt_fator_conversao_tt1.value = ''
    }
}

function atualizar() {
    /*Se o Peso aço Manual estiver checado, então significa que o campo Fator T.T está travado e assim 
    não tem o porque atualizar o Iframe da 5ª Etapa se este não será gravado ...*/
    if(parent.document.form.chkt_peso_aco_manual.checked == true) {
        alert('DESMARQUE A OPÇÃO DE PESO/PEÇA PARA ATUALIZAR O FATOR T.T !')
    }else {
        //Cálculo p/ Fator de Tratamento Térmico Bits QUAD / RETANG
        if(document.form.opt_opcao[0].checked == true ) {
            if(document.form.txt_fator_conversao_tt1.value != '') {
                var fator_conversao_tt1 = eval(strtofloat(document.form.txt_fator_conversao_tt1.value))
    //Aqui eu verifico se o valor é > 1, caso sim o valor = 1, o valor nunca pode ser > q 1
                if(fator_conversao_tt1 > 1) {
                    parent.document.form.txt_fator_tt5.value = '1,00'
                }else {
                    parent.document.form.txt_fator_tt5.value = document.form.txt_fator_conversao_tt1.value
                }
            }
    //Cálculo p/ Fator de Tratamento Térmico Bits REDONDO
        }else if(document.form.opt_opcao[1].checked == true ) {
            if(document.form.txt_fator_conversao_tt2.value != '') parent.document.form.txt_fator_tt5.value = document.form.txt_fator_conversao_tt2.value
        }
    }
}
</Script>
<body onload='verificar()'>
<form name='form'>
<table width='100%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhadestaque'>
        <td colspan='3' align='left'>
            <input type='radio' name='opt_opcao' onclick='verificar()' id='verificar1' checked>
            <label for='verificar1'>
                Cálculo p/ Fator de Trat. Térmico Bits QUADRADO / RETANGULAR
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td colspan='3' align='left'>
            <input type='radio' name='opcao' onclick='habilitar_parte1()' id='opcao1' checked>
            <label for='opcao1'>
                Matéria Prima QUAD / RETANG
            </label>
            &nbsp;&nbsp;&nbsp;
            <input type='radio' name='opcao' onclick='habilitar_parte1()' id='opcao2'>
            <label for='opcao2'>
                Matéria Prima REDONDA
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &Oslash; Da Matéria Prima:
        </td>
        <td>
            Lado A Matéria Prima:
        </td>
        <td>
            Lado B Matéria Prima:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_diam_materia_prima1' onKeyUp="verifica(this, 'moeda_especial', 2, '', event);calculo1()" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_ladoa_materia_prima' onKeyUp="verifica(this, 'moeda_especial', 2, '', event);calculo1()" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_ladob_materia_prima' onKeyUp="verifica(this, 'moeda_especial', 2, '', event);calculo1()" class='caixadetexto'>
        </td>      
    </tr>
    <tr class='linhanormal'>
        <td>
            Qtd Pçs / Corte:
        </td>
        <td>
            Lado A Peça Acabada:
        </td>
        <td>
            Lado B Peça Acabada:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_qtde_pcs_corte' onKeyUp="verifica(this, 'aceita', 'numeros', '', event);if(this.value != '') {this.value = Math.round(this.value)};calculo1()" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_ladoa_peca_acabada' onKeyUp="verifica(this, 'moeda_especial', 2, '', event);calculo1()" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name="txt_ladob_peca_acabada" onKeyUp="verifica(this, 'moeda_especial', 2, '', event);calculo1()" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fator de Conversão T.T.:
        </td>
        <td colspan='2'>
            <input type='text' name='txt_fator_conversao_tt1' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhadestaque'> 
        <td colspan='3' align='left'>
            <input type='radio' name='opt_opcao' onclick='verificar()' id='verificar2'>
            <label for='verificar2'>
                Cálculo p/ Fator de Tratamento Térmico Bits REDONDO
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &Oslash; Da Matéria Prima:
        </td>
        <td>
            &Oslash; Da Peça Acabada:
        </td>
        <td>
            Fator de Conversão T.T.:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_diam_materia_prima2' onKeyUp="verifica(this, 'moeda_especial', 2, '', event);calculo2()" class='caixadetexto' disabled>
        </td>
        <td>
            <input type='text' name='txt_diam_peca_acabada' onKeyUp="verifica(this, 'moeda_especial', 2, '', event);calculo2()" class='caixadetexto' disabled>
        </td>
        <td>
            <input type='text' name='txt_fator_conversao_tt2' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='3' align='left'>
            <input type='radio' name='opt_opcao' onclick="verificar()" id='verificar3'>
            <label for='verificar3'>
                Cálculo de &Oslash; de Aço Redondo p/ Bits QUADRADO / RETANGULAR
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>L1 : &nbsp;&nbsp;&nbsp;
            <input type='text' name='txt_l1' onKeyUp="verifica(this, 'moeda_especial', 2, '', event);calculo3()" class='caixadetexto'>
        </td>
        <td colspan='2'>
            L2: <input type='text' name='txt_l2' onKeyUp="verifica(this, 'moeda_especial', 2, '', event);calculo3()" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &Oslash; Min:
            <input type='text' name='txt_diam_minimo' class='textdisabled' disabled>
        </td>
        <td colspan='2'>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type='text' name='txt_diam_minimo2' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar'onclick="redefinir('document.form', 'LIMPAR');verificar()" style='color:#ff9900' class='botao'>
            <input type='button' name='cmd_atualizar' value='Atualizar' title='Atualizar' onclick="atualizar()" style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>