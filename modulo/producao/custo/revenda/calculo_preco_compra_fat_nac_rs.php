<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/custo_unificado/custo_unificado.php', '../../../../');

$sql = "SELECT pa.status_custo 
        FROM `fornecedores_x_prod_insumos` fpi 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = fpi.`id_produto_insumo` AND pa.`ativo` = '1' AND pa.`operacao_custo` = '1' 
        WHERE fpi.`id_fornecedor_prod_insumo` = '$_GET[id_fornecedor_prod_insumo]' LIMIT 1 ";
$campos_lista   = bancos::sql($sql);

//Se o Custo está liberado, não é possível ser alterado mais nada ...
if($campos_lista[0]['status_custo'] == 1) {
    $disabled   = 'disabled';
    $class      = 'textdisabled';
}else {//Só é possível estar alterando algum campo quando o Custo estiver Bloqueado ...
    $disabled   = '';
    $class      = 'botao';
}
?>
<html>
<head>
<title>.:: Calcular Preço Compra Fat Nac R$ ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content= 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function atualizar() {
//Preço de Compra Nac. R$ na forma de Compra desejada ...
    if(!texto('form', 'txt_preco_compra_nac_forma_compra_desejada', '3', '0123456789,.', 'PREÇO DE COMPRA NACIONAL R$ NA FORMA DE COMPRA DESEJADA', '2')) {
        return false
    }
//Atualiza o campo da Tela abaixo ...
    opener.document.form.txt_preco_compra_fat_nac.value = document.form.txt_preco_compra_fat_nac.value
    opener.calculos_gerais_custo_revenda()
    window.close()
}
    
function calcular_preco_compra_faturado_nacional_rs() {   
    if(document.form.txt_preco_compra_nac_forma_compra_desejada.value == '') {//Se a caixa estiver Vazia ...
        document.form.txt_preco_compra_fat_nac.value = ''
    }else {
        var desconto_vista          = eval(strtofloat(opener.document.form.txt_desc_vista.value))
        var desconto_sgd            = eval(strtofloat(opener.document.form.txt_desc_sgd.value))
        var forma_compra            = opener.document.form.cmb_forma_compra.value
        var preco_compra_fat_nac_des= eval(strtofloat(document.form.txt_preco_compra_nac_forma_compra_desejada.value))

        if(forma_compra == '') {//SELECIONE ...
            var preco_compra_fat_nac_reajustado = ''
        }else if(forma_compra == 1) {//FAT/NF ...
            var preco_compra_fat_nac_reajustado = preco_compra_fat_nac_des
        }else if(forma_compra == 2) {//FAT/SGD ...
            var preco_compra_fat_nac_reajustado = preco_compra_fat_nac_des / ((100 - desconto_sgd) / 100)
        }else if(forma_compra == 3) {//AV/NF ...
            var preco_compra_fat_nac_reajustado = preco_compra_fat_nac_des  / ((100 - desconto_vista) / 100)
        }else if(forma_compra == 4) {//AV/SGD ...
            var preco_compra_fat_nac_reajustado = preco_compra_fat_nac_des / ((100 - desconto_vista) / 100) / ((100 - desconto_sgd) / 100)
        }
        document.form.txt_preco_compra_fat_nac.value = preco_compra_fat_nac_reajustado
        document.form.txt_preco_compra_fat_nac.value = arred(document.form.txt_preco_compra_fat_nac.value, 2, 1)
    }
}
</Script>
</head>
<body onload='document.form.txt_preco_compra_nac_forma_compra_desejada.focus()'>
<form name='form' method='post'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Calcular Preço Compra Fat Nac R$
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Preço de Compra Nac. R$ na forma de Compra desejada:</b>
        </td>
        <td>
            <input type='text' name='txt_preco_compra_nac_forma_compra_desejada' title='Digite o Preço de Compra Nac. R$ na forma de Compra desejada' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_preco_compra_faturado_nacional_rs()" size='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Preço Compra Fat. Nac. R$:
        </td>
        <td>
            <input type='text' name='txt_preco_compra_fat_nac' title='Digite o Preço de Compra Nac. R$ na forma de Compra desejada' size='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_atualizar' value='Atualizar' title='Atualizar' onclick="return atualizar()" style='color:green' class='<?=$class;?>' <?=$disabled;?>>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>