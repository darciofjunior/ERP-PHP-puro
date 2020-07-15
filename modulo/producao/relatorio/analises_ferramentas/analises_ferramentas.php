<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/genericas.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";
$valor_dolar_dia = genericas::moeda_dia('dolar');
?>
<html>
<head>
<title>.:: Relat�rio de An�lises de Ferramentas ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function verificar_top() {
    //A op��o de TOP s� ser� v�lida apenas p/ Per�odo Semestral e Filtro por Maior Lucro em R$ ...
    if(document.getElementById('cmb_periodo').value != 4 || document.getElementById('cmb_tipo').value != 7) {
        if(document.getElementById('chkt_marcar_top').checked == true) {
            alert('A MARCA��O DO TOP SER� DESATIVADA !!!\nMARQUE O PER�ODO COMO SEMESTRAL E RELAT�RIO POR MAIOR LUCRO EM R$ PARA QUE ESSA MARCA��O SEJA FIXADA !')
            document.getElementById('chkt_marcar_top').checked = false
            document.getElementById('cmb_periodo').focus()
        }
    }
}

function habilitar_familia() {
    if(document.getElementById('cmb_tipo').value == 1) {
        document.form.cmb_familia.className     = 'combo'
        document.form.cmb_familia.disabled 	= false
        document.form.cmb_familia.size 		= 4
    }else {
        document.form.cmb_familia.className     = 'textdisabled'
        document.form.cmb_familia.disabled 	= true
        document.form.cmb_familia.size 		= 1
    }
}

function validar() {
    if(!data('form', 'txt_data_inicial', '4000', 'IN�CIO')) {
        return false
    }

    if(!data('form', 'txt_data_final', '4000', 'FIM')) {
        return false
    }

    var data_inicial    = document.form.txt_data_inicial.value
    var data_final      = document.form.txt_data_final.value
    data_inicial        = data_inicial.substr(6, 4) + data_inicial.substr(3, 2) + data_inicial.substr(0, 2)
    data_final          = data_final.substr(6, 4) + data_final.substr(3, 2) + data_final.substr(0, 2)
    data_inicial        = eval(data_inicial)
    data_final          = eval(data_final)

    if(data_final < data_inicial) {
        alert('DATA FINAL INV�LIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
/**Verifico se o intervalo entre Datas � > do que 10 anos. Fa�o essa verifica��o porque se o usu�rio 
colocar um intervalo de datas muito distantes, ent�o acaba sobrecarregando o Banco de Dados**/
    var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
    if(dias > 3700) {
        alert('INTERVALO DE DATAS INV�LIDO !!!\n INTERVALO DE DATAS SUPERIOR A DEZ ANOS !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
//Aqui � para n�o perder os valores da combo, caso o usu�rio clicar 2 vezes ou + no bot�o consultar ...
    controle_hidden_operacao_custo()
    controle_hidden_operacao_custo_sub()
}

function periodo_datas() {
    var periodo = eval(document.form.cmb_periodo.value)
    var data_atual = '<?=date("d/m/Y");?>'
//Se n�o tiver nenhum per�odo selecionado ...
    if(periodo == '') {
        document.form.txt_data_inicial.value = ''
        document.form.txt_data_final.value = ''
    }else if(periodo == 1) {//Mensal
        nova_data(data_atual, 'document.form.txt_data_inicial', -30)
        document.form.txt_data_final.value = data_atual
    }else if(periodo == 2) {//Bimestral
        nova_data(data_atual, 'document.form.txt_data_inicial', -60)
        document.form.txt_data_final.value = data_atual
    }else if(periodo == 3) {//Trimestral
        nova_data(data_atual, 'document.form.txt_data_inicial', -90)
        document.form.txt_data_final.value = data_atual
    }else if(periodo == 4) {//Semestral
        nova_data(data_atual, 'document.form.txt_data_inicial', -180)
        document.form.txt_data_final.value = data_atual
    }else if(periodo == 5) {//Anual
        nova_data(data_atual, 'document.form.txt_data_inicial', -365)
        document.form.txt_data_final.value = data_atual
    }
}

//Controle com a Opera��o de Custo
function controle_hidden_operacao_custo() {
    var operacao_custo = document.form.cmb_operacao_custo[document.form.cmb_operacao_custo.selectedIndex].text
//Se n�o estiver selecionada nenhuma Opera��o de Custo
    if(operacao_custo == 'SELECIONE') {
        document.form.hidden_operacao_custo.value = ''
    }else if(operacao_custo == 'Industrializa��o') {
        document.form.hidden_operacao_custo.value = 1
    }else if(operacao_custo == 'Revenda') {
        document.form.hidden_operacao_custo.value = 2
    }
}

//Controle com a Sub-Opera��o de Custo
function controle_hidden_operacao_custo_sub() {
    var operacao_custo_sub = document.form.cmb_operacao_custo_sub[document.form.cmb_operacao_custo_sub.selectedIndex].text
//Se n�o estiver selecionada nenhuma Sub-Opera��o de Custo
    if(operacao_custo_sub == 'SELECIONE') {
        document.form.hidden_operacao_custo_sub.value = ''
    }else if(operacao_custo_sub == 'Industrializa��o') {
        document.form.hidden_operacao_custo_sub.value = 1
    }else if(operacao_custo_sub == 'Revenda') {
        document.form.hidden_operacao_custo_sub.value = 2
    }
}

function controle_operacao_custo() {
    var operacao_custo = eval(document.form.cmb_operacao_custo.value)
    if(operacao_custo == 0) {//Quando a Opera��o de Custo = Industrial, eu habilito a Sub-Opera��o de Custo ...
//Layout de Habilitado
        document.form.cmb_operacao_custo_sub.className  = 'caixadetexto'
//Habilita a Combo de Empresa
        document.form.cmb_operacao_custo_sub.value      = ''
        document.form.cmb_operacao_custo_sub.disabled   = false
//Quando a Opera��o de Custo = Revenda, eu desabilito a Sub-Opera��o de Custo ...
    }else {
//Layout de Desabilitado
        document.form.cmb_operacao_custo_sub.className  = 'textdisabled'
//Desabilita a Combo de Empresa
        document.form.cmb_operacao_custo_sub.value      = ''
        document.form.cmb_operacao_custo_sub.disabled   = true
    }
}

function atualizar_grupos_pas(combo) {
    var id_familias = ''
    //Aqui eu verifico quais s�o as fam�lias que est�o selecionadas ...
    for(i = 0; i < combo.length; i++) {
        if(combo[i].selected) {
            id_familias+= combo[i].value + ', '
        }
    }
    id_familias = id_familias.substr(0, id_familias.length - 2) 
    ajax('carregar_grupos_pas.php?id_familia='+id_familias, 'cmb_grupo_pa')
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<!--**********************Gambiarra**********************
/*Aqui eu tive que fazer essa adapta��o, porque estava dando erro de par�metro por causa que a Combo
armazena um dos valores como sendo zero, e devido a isso, eu estava perdendo todo o Filtro l� no outro
passo da consulta*/
-->
<input type='hidden' name='hidden_operacao_custo'>
<input type='hidden' name='hidden_operacao_custo_sub'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='18'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='18'>
            Relat&oacute;rio de An�lises de Ferramentas
        </td>
    </tr>
    <tr class='linhadestaque' valign='center' align='center'>
        <td colspan='18'>
            Per�odo: 
            <select name="cmb_periodo" id="cmb_periodo" title="Selecione o Per�odo" onchange="periodo_datas();verificar_top();" class="combo">
                    <option value="" style="color:red">SELECIONE</option>
                    <? if($cmb_periodo==1) { $selected="selected"; } else { $selected=""; }?>
                    <option value="1" <?=$selected;?>>Mensal</option>
                    <? if($cmb_periodo==2) { $selected="selected"; } else { $selected=""; }?>
                    <option value="2" <?=$selected;?>>Bimestral</option>
                    <? if($cmb_periodo==3) { $selected="selected"; } else { $selected=""; }?>
                    <option value="3" <?=$selected;?>>Trimestal</option>
                    <? if($cmb_periodo==4) { $selected="selected"; } else { $selected=""; }?>
                    <option value="4" <?=$selected;?>>Semestral</option>
                    <? 
                            if(empty($txt_data_inicial)) {
                                    $selected = 'selected';
                            }else {
                                    if($cmb_periodo==5) { $selected="selected"; } else { $selected=""; }
                            }
                    ?>
                    <option value="5" <?=$selected;?>>Anual</option>
            </select>
            &nbsp;Data Inicial: 
            <?
                    if(empty($txt_data_inicial)) {
                            $txt_data_inicial 	= data::adicionar_data_hora(date('d/m/Y'), -365);
                            $txt_data_final 	= date('d/m/Y');
                    }
                    $data_inicial 	= data::datatodate($txt_data_inicial, '-');
                    $data_final 	= data::datatodate($txt_data_final, '-');
            ?>
            <input type="text" name="txt_data_inicial" value="<?=$txt_data_inicial;?>" onkeyup="verifica(this, 'data', '', '', event)" size="11" maxlength="10" class="caixadetexto">
             <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;Data Final:
            <input type="text" name="txt_data_final" value="<?=$txt_data_final;?>" onkeyup="verifica(this, 'data', '', '', event)" size="11" maxlength="10" class="caixadetexto">
             <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;
            <?
                    $checked = ($chkt_marcar_top == 'S') ? 'checked' : '';
            ?>
            <input type='checkbox' name='chkt_marcar_top' id='chkt_marcar_top' value='S' title="Marcar Ferramentas como TOP" onclick="verificar_top()" class="checkbox" <?=$checked;?>>
            <label for="chkt_marcar_top">
                    Marcar <?=intval(genericas::variavel(45));?> Ferramentas como <font color="red">TOP</font>
            </label>
            &nbsp;
            Opera��o de Custo: 
            <select name="cmb_operacao_custo" title="Selecione a Opera��o de Custo" onchange="controle_operacao_custo();controle_hidden_operacao_custo()" class="combo">
                    <option value="" style="color:red">SELECIONE</option>
                    <? if($hidden_operacao_custo==1) { $selected="selected"; } else { $selected=""; }?>
                    <option value="0" <?=$selected;?>>Industrializa��o</option>
                    <? if($hidden_operacao_custo==2) { $selected="selected"; } else { $selected=""; }?>
                    <option value="1" <?=$selected;?>>Revenda</option>
            </select>
            &nbsp;
            <?
                    if($hidden_operacao_custo == 1) {//O.C. = Industrial, destrava a combo ...
                            $disabled = '';
                            $class = 'combo';
                    }else {//O.C. = Revenda, trava a combo ...
                            $disabled = 'disabled';
                            $class = 'textdisabled';
                    }
            ?>
            <select name="cmb_operacao_custo_sub" title="Selecione a Sub-Opera��o" onchange="controle_hidden_operacao_custo_sub()" class="<?=$class;?>" <?=$disabled;?>>
                    <option value="" style="color:red" selected>SELECIONE</option>
                    <? if($hidden_operacao_custo_sub==1) { $selected="selected"; } else { $selected=""; }?>
                    <option value="0" <?=$selected;?>>Industrializa��o</option>
                    <? if($hidden_operacao_custo_sub==2) { $selected="selected"; } else { $selected=""; }?>
                    <option value="1" <?=$selected;?>>Revenda</option>
            </select>
            <br/>
            Relat�rio por: 
            <select name="cmb_tipo" id="cmb_tipo" title="Selecione o tipo de relat�rio" onchange="habilitar_familia()" class="combo">
                    <?
                            if($cmb_tipo == 1) {
                                    $selected1 = 'selected';
                            }else if($cmb_tipo == 2) {
                                    $selected2 = 'selected';
                            }else if($cmb_tipo == 3) {
                                    $selected3 = 'selected';
                            }else if($cmb_tipo == 4) {
                                    $selected4 = 'selected';
                            }else if($cmb_tipo == 5) {
                                    $selected5 = 'selected';
                            }else if($cmb_tipo == 6) {
                                    $selected6 = 'selected';
                            }else if($cmb_tipo == 7) {
                                    $selected7 = 'selected';
                            }else if($cmb_tipo == 8) {
                                    $selected8 = 'selected';
                            }
                    ?>				
                    <option value="1" <?=$selected1;?>>Produto mais Vendido</option>
                    <option value="2" <?=$selected2;?>>Maior Margem de Lucro</option>
                    <option value="3" <?=$selected3;?>>Maior Falta de Produto</option>
                    <option value="4" <?=$selected4;?>>Maior P.A. Programado</option>
                    <option value="5" <?=$selected5;?>>Maior M.M.V.</option>
                    <option value="6" <?=$selected6;?>>Maior Volume R$</option>
                    <option value="7" <?=$selected7;?>>Maior Lucro em R$</option>
                    <option value="8" <?=$selected8;?>>Maior Estoque Dispon�vel</option>
            </select>
            <br/>
            Fam�lia: 
            <select name="cmb_familia[]" id='cmb_familia' title="Selecione a Familia" size="4" onclick="atualizar_grupos_pas(this)" class="combo" multiple>
            <?
                $sql = "SELECT id_familia, nome 
                        FROM `familias` 
                        WHERE ativo = '1' ORDER BY nome ";
                echo combos::combo($sql);
            ?>
            </select>
            &nbsp;
            Grupo PA: 
            <select name="cmb_grupo_pa[]" id='cmb_grupo_pa' title="Selecione o Grupo do PA" size="4" class="combo" multiple>
                <option value='' style='color:red'>SELECIONE</option>
            </select>
            &nbsp;
            <?$checked = (!empty($chkt_mostrar_componentes)) ? 'checked': '';?>
            <input type='checkbox' name='chkt_mostrar_componentes' value='1' title="Mostrar Componentes" id="mostrar_componentes" class="checkbox" <?=$checked;?>>
            <label for="mostrar_componentes">
                    Mostrar Componentes
            </label>
            &nbsp;
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
<table width='90%' border='1' cellspacing='0' cellpadding='0' align='center'>
<? 
    switch($cmb_tipo) {
        case 1://Produto mais Vendido
        case 2://Maior Margem de Lucro
        case 3://Maior Falta de Produto
        case 4://Maior P.A. Programado
        case 5://Maior M.M.V.
        case 6://Maior Volume em R$
        case 7://Maior Lucro em R$
        case 8://Maior Estoque Dispon�vel
                require('relatorio_analise_ferramentas.php');
        break;
    }
?>
</table>
</form>
</body>
</html>