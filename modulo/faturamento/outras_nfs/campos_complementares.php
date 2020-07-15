<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/faturamento/outras_nfs/itens/alterar_imprimir.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>DADO(S) DE NF COMPLEMENTAR ATUALIZADO(S) COM SUCESSO.</font>";

if($passo == 1) {
    $sql = "UPDATE `nfs_outras` SET `data_sys` = '".date('Y-m-d H:i:s')."', `base_calculo_icms_comp` = '$_POST[txt_base_calculo_icms_comp]', `valor_icms_comp` = '$_POST[txt_valor_icms_comp]', `base_calculo_icms_st_comp` = '$_POST[txt_base_calculo_icms_st_comp]', `valor_icms_st_comp` = '$_POST[txt_valor_icms_st_comp]', `valor_total_produtos_comp` = '$_POST[txt_valor_total_produtos_comp]', `valor_frete_comp` = '$_POST[txt_valor_frete_comp]', `valor_seguro_comp` = '$_POST[txt_valor_seguro_comp]', `outras_despesas_acessorias_comp` = '$_POST[txt_outras_despesas_acessorias_comp]', `valor_ipi_comp` = '$_POST[txt_valor_ipi_comp]', `valor_total_nota_comp` = '$_POST[txt_valor_total_nota_comp]' WHERE `id_nf_outra` = '$_POST[id_nf_outra]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        window.location = 'campos_complementares.php?id_nf_outra=<?=$_POST['id_nf_outra'];?>&valor=1'
    </Script>
<?
}else {
    $sql = "SELECT base_calculo_icms_comp, valor_icms_comp, base_calculo_icms_st_comp, valor_icms_st_comp, valor_total_produtos_comp, valor_frete_comp, valor_seguro_comp, outras_despesas_acessorias_comp, valor_ipi_comp, valor_total_nota_comp 
            FROM `nfs_outras` 
            WHERE `id_nf_outra` = '$_GET[id_nf_outra]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Campo(s) Complementar(es) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/jquery.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Base de Cálculo do ICMS ...
    if(document.form.txt_base_calculo_icms_comp.value != '') {
        if(!texto('form', 'txt_base_calculo_icms_comp', '4', '1234567890,.', 'BASE DE CÁLCULO DO ICMS', '1')) {
            return false
        }
    }
//Valor do ICMS ...
    if(document.form.txt_valor_icms_comp.value != '') {
        if(!texto('form', 'txt_valor_icms_comp', '4', '1234567890,.', 'VALOR DO ICMS', '2')) {
            return false
        }
    }
//Base de Cálculo do ICMS ST . ...
    if(document.form.txt_base_calculo_icms_st_comp.value != '') {
        if(!texto('form', 'txt_base_calculo_icms_st_comp', '4', '1234567890,.', 'BASE DE CÁLCULO DO ICMS ST', '1')) {
            return false
        }
    }
//Valor do ICMS ST ...
    if(document.form.txt_valor_icms_st_comp.value != '') {
        if(!texto('form', 'txt_valor_icms_st_comp', '1', '1234567890,.', 'VALOR DO ICMS ST', '2')) {
            return false
        }
    }
//Valor Total dos Produtos ...
    if(document.form.txt_valor_total_produtos_comp.value != '') {
        if(!texto('form', 'txt_valor_total_produtos_comp', '4', '1234567890,.', 'VALOR TOTAL DOS PRODUTOS', '2')) {
            return false
        }
    }
//Valor do Frete ...
    if(document.form.txt_valor_frete_comp.value != '') {
        if(!texto('form', 'txt_valor_frete_comp', '4', '1234567890,.', 'VALOR DO FRETE', '2')) {
            return false
        }
    }
//Valor do Seguro ...
    if(document.form.txt_valor_seguro_comp.value != '') {
        if(!texto('form', 'txt_valor_seguro_comp', '4', '1234567890,.', 'VALOR DO SEGURO', '2')) {
            return false
        }
    }
//Outras Despesas Acessórias ...
    if(document.form.txt_outras_despesas_acessorias_comp.value != '') {
        if(!texto('form', 'txt_outras_despesas_acessorias_comp', '4', '1234567890,.', 'OUTRA(S) DESPESA(S) ACESSÓRIA(S)', '2')) {
            return false
        }
    }
//Valor do IPI ...
    if(document.form.txt_valor_ipi_comp.value != '') {
        if(!texto('form', 'txt_valor_ipi_comp', '4', '1234567890,.', 'VALOR DO IPI', '2')) {
            return false
        }
    }
//Valor Total da Nota ...
    if(document.form.txt_valor_total_nota_comp.value != '') {
        if(!texto('form', 'txt_valor_total_nota_comp', '4', '1234567890,.', 'VALOR TOTAL DA NOTA', '2')) {
            return false
        }
    }
//Antes de Salvar eu verifico se foi preenchido pelo menos algum Campo ...
    var elementos = document.form.elements
    var cont_campos_vazios = 0
    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'text') {
            if(elementos[i].value == '' || elementos[i].value == '0,00') cont_campos_vazios++
        }
    }
//Retorna Mensagem de Erro ...
    if(cont_campos_vazios == 10) {
        alert('PREENCHA ALGUM CAMPO !')
        document.form.txt_base_calculo_icms_comp.focus()
        document.form.txt_base_calculo_icms_comp.select()
        return false
    }
//Aqui é para não atualizar o Cabeçalho abaixo desse Pop-UP ...
    document.form.nao_atualizar.value = 1
    return limpeza_moeda('form', 'txt_base_calculo_icms_comp, txt_valor_icms_comp, txt_base_calculo_icms_st_comp, txt_valor_icms_st_comp, txt_valor_total_produtos_comp, txt_valor_frete_comp, txt_valor_seguro_comp, txt_outras_despesas_acessorias_comp, txt_valor_ipi_comp, txt_valor_total_nota_comp, ')
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.opener.document.form.passo.value = 1
        window.opener.document.form.submit()
    }
}
</Script>
</head>
<body onload="document.form.txt_base_calculo_icms_comp.focus()" onunload="atualizar_abaixo()">
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit="return validar()">
<input type='hidden' name='id_nf_outra' value="<?=$_GET['id_nf_outra'];?>">
<input type='hidden' name='nao_atualizar'>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Campo(s) Complementar(es)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Base de Cálculo do ICMS:
        </td>
        <td>
            <input type='text' name="txt_base_calculo_icms_comp" value="<?=number_format($campos[0]['base_calculo_icms_comp'], 2, ',', '.');?>" title="Digite a Base de Cálculo do ICMS" size="13" maxlength="13" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor do ICMS:
        </td>
        <td>
            <input type='text' name="txt_valor_icms_comp" value="<?=number_format($campos[0]['valor_icms_comp'], 2, ',', '.');?>" title="Digite o Valor do ICMS" size="13" maxlength="13" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Base de Cálculo do ICMS ST:
        </td>
        <td>
            <input type='text' name="txt_base_calculo_icms_st_comp" value="<?=number_format($campos[0]['base_calculo_icms_st_comp'], 2, ',', '.');?>" title="Digite a Base de Cálculo do ICMS Subst" size="13" maxlength="13" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor do ICMS ST:
        </td>
        <td>
            <input type='text' name="txt_valor_icms_st_comp" value="<?=number_format($campos[0]['valor_icms_st_comp'], 2, ',', '.');?>" title="Digite o Valor do ICMS Subst." size="13" maxlength="13" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor Total dos Produtos:
        </td>
        <td>
            <input type='text' name="txt_valor_total_produtos_comp" value="<?=number_format($campos[0]['valor_total_produtos_comp'], 2, ',', '.');?>" title="Digite o Valor Total dos Produtos" size="13" maxlength="13" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor do Frete:
        </td>
        <td>
            <input type='text' name="txt_valor_frete_comp" value="<?=number_format($campos[0]['valor_frete_comp'], 2, ',', '.');?>" title="Digite o Valor do Frete" size="13" maxlength="13" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor do Seguro:
        </td>
        <td>
            <input type='text' name="txt_valor_seguro_comp" value="<?=number_format($campos[0]['valor_seguro_comp'], 2, ',', '.');?>" title="Digite o Valor do Seguro" size="13" maxlength="13" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Outras Despesas Acessórias:
        </td>
        <td>
            <input type='text' name="txt_outras_despesas_acessorias_comp" value="<?=number_format($campos[0]['outras_despesas_acessorias_comp'], 2, ',', '.');?>" title="Digite as Outras Despesas Acessórias" size="13" maxlength="13" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor do IPI:
        </td>
        <td>
            <input type='text' name="txt_valor_ipi_comp" value="<?=number_format($campos[0]['valor_ipi_comp'], 2, ',', '.');?>" title="Digite o Valor Total do IPI" size="13" maxlength="13" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor Total da Nota:
        </td>
        <td>
            <input type='text' name="txt_valor_total_nota_comp" value="<?=number_format($campos[0]['valor_total_nota_comp'], 2, ',', '.');?>" title="Digite o Valor Total da Nota" size="13" maxlength="13" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_base_calculo_icms_comp.focus()" style="color:#ff9900" class='botao'>
            <input type='submit' name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
            <input type='button' name="cmd_fechar" value="Fechar" title="Fechar" onClick="fechar(window)" style="color:red" class='botao'>
        </td>
    </tr>
</table>
<p class='piscar'>
    <font color='red'>
        &nbsp;&nbsp;&nbsp;*** SE FOR PREENCHER APENAS O CAMPO <b>"VALOR TOTAL DA NOTA"</b>, 
        PREENCHA TAMBÉM O CAMPO <b>"VALOR TOTAL DOS PRODUTOS"</b> !!!
    </font>
</p>
&nbsp;
<!--Tenho q colocar a função depois, pq senão não é reconhecida a Tag "P" que foi criada antes usando o atributo Piscar ...-->
<Script Language = 'JavaScript'>
    function blink(selector) {
        $(selector).fadeOut('slow', function() {
            $(this).fadeIn('slow', function() {
                blink(this);
            });
        });
    }
    blink('.piscar');
</Script>
</form>
</body>
</html>
<?}?>