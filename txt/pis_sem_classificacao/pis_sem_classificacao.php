<?
require('../../lib/segurancas.php');

//Lista Todos os PI(s) no Sistema que estão sem Classificação Fiscal ...
$sql = "SELECT g.referencia, pi.discriminacao 
        FROM `produtos_insumos` pi 
        INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
        WHERE pi.`id_classific_fiscal` = '0' 
        AND pi.`ativo` = '1' ORDER BY g.referencia, pi.discriminacao ";
$campos_pis = bancos::sql($sql, $inicio, 200, 'sim', $pagina);
$linhas_pis = count($campos_pis);
?>
<html>
<head>
<title>.:: PI(s) Sem Classificação Fiscal ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../js/sessao.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            PI(s) Sem Classificação Fiscal
        </td>
    </tr>
    <tr class='linhadestaque' align="center">
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas_pis; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos_pis[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos_pis[$i]['discriminacao'];?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
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