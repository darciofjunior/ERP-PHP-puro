<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/rh/consorcio/itens/consultar.php', '../../../');

$sql = "SELECT * 
	FROM `consorcios` 
	WHERE `id_consorcio` = '$_GET[id_consorcio]' LIMIT 1 ";
$campos         = bancos::sql($sql);
$observacao     = (empty($campos[0]['observacao'])) ? '&nbsp;' : $campos[0]['observacao'];
?>
<html>
<head>
<title>.:: Dados do Consórcio ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<body>
<table width='100%' border='1' cellpadding='0' cellspacing='0' bordercolor='#FFFFFF' bgcolor='#E8E8E8'>
    <tr class='linhanormal'>
        <td bgcolor='#DEDEDE' width='21%'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='#000000'>
                <b>NOME DO GRUPO:</b>
            </font>
        </td>
        <td width='30%'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1.5'>
                <?=$campos[0]['nome_grupo'];?>
            </font>
        </td>
        <td bgcolor='#DEDEDE' width='28%'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='#000000'>
                <b>VALOR:</b>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1.5'>
                <?=number_format($campos[0]['valor'], 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td bgcolor='#DEDEDE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='#000000'>
                <b>JUROS:</b>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1.5'>
                <?=number_format($campos[0]['juros'], 2, ',', '.').' %';?>
            </font>
        </td>
        <td bgcolor='#DEDEDE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='#000000'>
                <b>DATA DE HOLERITH INICIAL:</b>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1.5'>
                <?=data::datetodata($campos[0]['data_inicial'], '/');?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td bgcolor='#DEDEDE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='#000000'>
                <b>MESES:</b>
            </font>
        </td>
        <td colspan='3'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1.5'>
                <?=$campos[0]['meses'];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td bgcolor='#DEDEDE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='#000000'>
                <b>OBSERVAÇÃO:</b>
            </font>
        </td>
        <td colspan='3'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1.5'>
                <?=$observacao;?>
            </font>
        </td>
    </tr>
</table>
</body>
</html>