<?
require('../../../lib/segurancas.php');
require('../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/vendas/estoque_acabado/consultar.php', '../../../');
$taxa_financeira = genericas::variavel(16);
?>
<html>
<head>
<title>.:: Cálculo Desconto Ideal p/ Compra ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>    
function calcular() {
    if(document.form.txt_mlm_desejada.value != '') {
        var mlm_total       = eval(strtofloat(document.form.txt_mlm_total.value))
        var taxa_financeira = eval('<?=$taxa_financeira;?>')
        var markup_vendas   = (1 + mlm_total / 100) * (1 + taxa_financeira / 100)
        var mlm_desejada    = eval(strtofloat(document.form.txt_mlm_desejada.value))
        document.form.txt_desconto_ideal_compra.value = (1 - markup_vendas / (1 + mlm_desejada / 100) / (1 + taxa_financeira / 100)) * 100
        document.form.txt_desconto_ideal_compra.value = arred(document.form.txt_desconto_ideal_compra.value, 1, 1)
    }else {
        document.form.txt_desconto_ideal_compra.value = ''
    }
}
</Script>
</head>
<body onload='document.form.txt_mlm_total.focus()'>
<form name='form'>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            Cálculo Desconto Ideal p/ Compra
            <br>Produto: 
            <font color='yellow'>
            <?
                $sql = "SELECT referencia, discriminacao 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` = '$_GET[id_produto_acabado]' LIMIT 1 ";
                $campos = bancos::sql($sql);
                echo $campos[0]['referencia'].' - '.$campos[0]['discriminacao'];
            ?>
            </font>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            MLM Total
        </td>
        <td>
            <input type="text" name="txt_mlm_total" value="<?=$_GET['mlm_total'];?>" size='15' maxlength='12' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            MLM Desejada
        </td>
        <td>
            <input type="text" name="txt_mlm_desejada" onKeyUp="verifica(this, 'moeda_especial', '1', '', event);calcular()" size='15' maxlength='12' class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Desconto Ideal p/ Compra
        </td>
        <td>
            <input type="text" name="txt_desconto_ideal_compra" size='15' maxlength='12' class="textdisabled" disabled>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="window.close()" style="color:red" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>