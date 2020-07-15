<?
require('../../lib/segurancas.php');
require('../../lib/estoque_acabado.php');

$sql = "SELECT `id_produto_acabado`, `referencia`, `discriminacao` 
        FROM `produtos_acabados` 
        WHERE `ativo` = '1' ORDER BY `referencia` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Relatório de ED > EE ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../js/sessao.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Relatório de ED > EE
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
    for($i = 0; $i < $linhas; $i++) {
        $estoque_produto    = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado']);
        $qtde_disponivel    = $estoque_produto[3];
        $qtde_excedente     = $estoque_produto[14];
        
        if($qtde_excedente > $qtde_disponivel && $qtde_excedente > 0) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td>
            <?=$campos[$i]['discriminacao'];?>
        </td>
    </tr>
<?        
        }
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
</table>
</body>
</html>