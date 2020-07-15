<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

//Aqui eu busco todos os Bancos cadastrados no sistema ...
$sql = "SELECT * 
	FROM `bancos` 
	WHERE `ativo` = '1' ORDER BY banco ";
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = '../../../html/index.php?valor=2'
    </Script>
<?
    exit;
}
?>
<html>
<head>
<title>.:: Consultar Banco(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='60%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr class="linhacabecalho" align='center'>
        <td colspan='2'>
            Consultar Banco(s)
        </td>
    </tr>
    <tr class="linhadestaque" align='center'>
        <td>
            Banco
        </td>
        <td>
            Página Web
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['banco'];?>
        </td>
        <td>
            <?=$campos[$i]['pagweb'];?>
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