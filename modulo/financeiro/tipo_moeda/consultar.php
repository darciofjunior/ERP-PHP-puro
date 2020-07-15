<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

//Busca todos os Tipos de Moedas que estão cadastradas no Sistema ...
$sql = "SELECT * 
	FROM `tipos_moedas` 
	WHERE `ativo` = '1' ORDER BY moeda ";
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = '../../../html/index.php?valor=2'
    </Script>
<?
    exit;
}
?>
<html>
<head>
<head>
<title>.:: Consultar Tipo de Moeda ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr class="linhacabecalho" align="center">
        <td colspan='3'>
            Consultar Tipo de Moeda
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            Moeda
        </td>
        <td>
            Símbolo
        </td>
        <td>
            Origem
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
        <td>
            <?=$campos[$i]['moeda'];?>
        </td>
        <td>
            <?=$campos[$i]['simbolo'];?>
        </td>
        <td>
            <?=$campos[$i]['origem'];?>
        </td>
    </tr>
<?
    }
?>
    <tr class="linhacabecalho">
        <td colspan="3">&nbsp;</td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>