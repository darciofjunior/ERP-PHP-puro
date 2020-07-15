<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$sql = "SELECT * 
	FROM `vales_transportes` 
	WHERE `ativo` = '1' ORDER BY tipo_vt ";
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = '../../../html/index.php?valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Vale(s) Transporte(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border="0" cellspacing="1" cellpadding="1" onmouseover="total_linhas(this)" align='center'>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            Consultar Vale(s) Transporte(s)
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            Tipo de VT
        </td>
        <td>
            Valor Unitário
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
        <td align="left">
            <?=$campos[$i]['tipo_vt'];?>
        </td>
        <td align="right">
            <?='R$ '.number_format($campos[$i]['valor_unitario'], 2, ',', '.');?>
        </td>
    </tr>
<?
	}
?>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>