<?
require('../../lib/segurancas.php');
?>
<html>
<head>
<title>.:: Diferença de ICMS entre Estados ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<body>
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Diferença de ICMS entre Estados - SP comparado com outros Estados
        </td>
    </tr>
<?
//todos os de SP
$sql = "SELECT cf.classific_fiscal, i.`icms`, i.reducao, i.`id_classific_fiscal` 
        FROM `icms` i 
        INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = i.`id_classific_fiscal` AND cf.`id_classific_fiscal` <> '14' 
        WHERE i.`id_uf` = '1' ORDER BY cf.classific_fiscal ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $sql = "SELECT u.sigla, i.`icms`, i.reducao 
            FROM `icms` i 
            INNER JOIN `ufs` u ON u.id_uf = i.id_uf 
            WHERE  i.`id_classific_fiscal` = '".$campos[$i]['id_classific_fiscal']."' 
            AND i.`id_uf` <> '1' ORDER BY u.sigla ";
    $campos_nao_sp = bancos::sql($sql);
    $linhas_nao_sp = count($campos_nao_sp);
    for($j = 0; $j < $linhas_nao_sp; $j++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos[$i]['classific_fiscal'];?>
        </td>
        <td>
            <?=$campos_nao_sp[$j]['sigla'];?>
        </td>
        <td>
        <?
            $icms_sp 		= round($campos[$i]['icms'] - ($campos[$i]['icms'] * $campos[$i]['reducao'] / 100), 2);
            $icms_nao_sp 	= round($campos_nao_sp[$j]['icms'] - ($campos_nao_sp[$j]['icms'] * $campos_nao_sp[$j]['reducao'] / 100), 2);
            echo number_format($icms_sp - $icms_nao_sp, 2, ',', '.');
        ?>
        </td>
    </tr>
<?
    }
    if($linhas_nao_sp > 0) {
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>&nbsp;</td>
    </tr>
<?
    }
}
?>