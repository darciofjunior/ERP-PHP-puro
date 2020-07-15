<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$sql = "SELECT * 
        FROM `cargos` 
        WHERE `ativo` = '1' 
        ORDER BY cargo ";
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
<title>.:: Consultar Cargo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='60%' border="0" cellspacing="1" cellpadding="1" onmouseover="total_linhas(this)" align='center'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Consultar Cargo(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Cargo
        </td>
    </tr>
 <?
	for ($i = 0; $i < $linhas; $i++) {
 ?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td align="left">
            <?=$campos[$i]['cargo'];?>
        </td>
    </tr>
<?
	}
?>
    <tr class="linhacabecalho" align="center">
        <td colspan='4'>
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