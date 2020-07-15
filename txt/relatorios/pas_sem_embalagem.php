<?
require('../../lib/segurancas.php');

$sql = "SELECT pa.id_produto_acabado 
        FROM `produtos_acabados` pa 
        INNER JOIN `pas_vs_pis_embs` ppe ON ppe.id_produto_acabado = pa.id_produto_acabado 
        WHERE pa.`ativo` = '1' ";
$campos_pas_embalagens = bancos::sql($sql);
$linhas_pas_embalagens = count($campos_pas_embalagens);

if($linhas_pas_embalagens > 0) {
    for($i = 0; $i < $linhas_pas_embalagens; $i++) $id_produto_acabados.= $campos_pas_embalagens[$i]['id_produto_acabado'].', ';
    $id_produto_acabados = substr($id_produto_acabados, 0, strlen($id_produto_acabados) - 2);
//Aqui eu busco todos os PA(s) que são ESP e que estão sem Embalagem ...
    $sql = "SELECT referencia, discriminacao 
            FROM `produtos_acabados` 
            WHERE `ativo` = '1' 
            AND `referencia` NOT IN ('ESP', '') 
            AND `id_produto_acabado` NOT IN ($id_produto_acabados) 
            ORDER BY discriminacao ";
    $campos_pas = bancos::sql($sql);
    $linhas_pas = count($campos_pas);
?>
<html>
<head>
<title>.:: PA(s) sem Embalagem ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<body>
<table width='60%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            PA(s) sem Embalagem
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas_pas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos_pas[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos_pas[$i]['discriminacao'];?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
</table>
<font face='verdana, arial, helvetica, sans-serif' class='atencao'>
    <center>Total de Registro(s): <?=$linhas_pas;?></center>
</font>
</body>
</html>
<?}?>