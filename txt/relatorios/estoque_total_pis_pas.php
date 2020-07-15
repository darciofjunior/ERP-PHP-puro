<?
require('../../lib/segurancas.php');

//Aqui eu busco o Peso de Todos os PA(s) ...
$sql = "SELECT ea.qtde, pa.discriminacao, pa.peso_unitario, (ea.qtde * pa.peso_unitario) AS total_item 
        FROM `produtos_acabados` pa 
        INNER JOIN `estoques_acabados` ea ON ea.id_produto_acabado = pa.id_produto_acabado AND ea.`qtde` > '0' 
        WHERE pa.`ativo` = '1' 
        AND pa.`peso_unitario` > '0' ORDER BY pa.discriminacao ";
$campos_pas = bancos::sql($sql);
$linhas_pas = count($campos_pas);
?>
<html>
<head>
<title>.:: Estoque de PA(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../js/sessao.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Estoque de PA(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Discriminação
        </td>
        <td>
            Qtde
        </td>
        <td>
            Peso
        </td>
        <td>
            Total Item
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas_pas; $i++) {
?>
    <tr class='linhanormal'>
        <td>
            <?=$campos_pas[$i]['discriminacao'];?>
        </td>
        <td align='right'>
            <?=number_format($campos_pas[$i]['qtde'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($campos_pas[$i]['peso_unitario'], 4, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($campos_pas[$i]['total_item'], 2, ',', '.');?>
        </td>
    </tr>
<?
        $peso_total_geral+= $campos_pas[$i]['total_item'];
    }
?>
    <tr class='linhacabecalho' align='right'>
        <td colspan='3'>
            <font color='yellow'>
                Peso Total Geral =>
            </font>
        </td>
        <td>
            <?=number_format($peso_total_geral, 2, ',', '.');?>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>