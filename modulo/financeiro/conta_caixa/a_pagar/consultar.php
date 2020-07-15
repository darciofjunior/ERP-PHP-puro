<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');

$sql = "SELECT ccp.id_conta_caixa_pagar, ccp.conta_caixa, ccp.descricao, m.modulo 
        FROM `contas_caixas_pagares` ccp 
        INNER JOIN `modulos` m ON m.id_modulo = ccp.id_modulo 
        WHERE ccp.`ativo` = '1' ";
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
            window.location = '../../../../html/index.php?valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Conta(s) Caixa(s) à Pagar(es) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border="0" cellspacing="1" cellpadding="1" onmouseover="total_linhas(this)" align="center">
    <tr></tr>
    <tr class="linhacabecalho" align="center">
            <td colspan='3'>
                Consultar Conta(s) Caixa(s) à Pagar(es)
            </td>
    </tr>
    <tr class="linhadestaque" align='center'>
        <td>
            Conta Caixa
        </td>
        <td>
            Módulo
        </td>
        <td>
            Descrição
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align="left"> 
            <?=$campos[$i]['conta_caixa'];?></div>
        </td>
        <td>
            <?=$campos[$i]['modulo'];?>
        </td>
        <td align="left">
            <?=$campos[$i]['descricao'];?>
        </td>
    </tr>
<?
	}
?>
	<tr class="linhacabecalho">
            <td colspan="3">
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