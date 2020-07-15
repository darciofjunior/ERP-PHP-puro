<?
require('../../lib/segurancas.php');

//Busca dos PA(s) somente Normais de Linha ...
$sql = "SELECT ea.qtde, pa.id_produto_acabado, pa.discriminacao, pa.peso_unitario 
        FROM `produtos_acabados` pa 
        INNER JOIN `estoques_acabados` ea ON ea.id_produto_acabado = pa.id_produto_acabado 
        WHERE pa.`id_produto_acabado` 
        AND pa.`ativo` = '1' 
        AND pa.`referencia` <> 'ESP' ORDER BY pa.discriminacao ";
$campos_pas = bancos::sql($sql);
$linhas_pas = count($campos_pas);
?>
<html>
<head>
<title>.:: Peso Total dos PA(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../js/tabela.js'></Script>
</head>
<body>
<form name='form'>
<table width='80%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Peso Total dos PA(s)
        </td>
    </tr>
    <tr class="linhadestaque" align="center"> 
        <td>
            Descrição Produto
        </td>
        <td>
            Estoque
        </td>
        <td>
            Peso Unitário
        </td>
        <td>
            Total Kg (PA)
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas_pas; $i++) {
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
        <td align='left'>
            <?=$campos_pas[$i]['discriminacao'];?>
        </td>
        <td>
            <?=number_format($campos_pas[$i]['qtde'], 2, ',', '');?>
        </td>
        <td>
            <?=number_format($campos_pas[$i]['peso_unitario'], 8, ',', '');?>
        </td>
        <td>
            <?=number_format($campos_pas[$i]['peso_unitario'] * $campos_pas[$i]['qtde'], 4, ',', '');?>
        </td>
    </tr>
<?
	$peso_total+= $campos_pas[$i]['peso_unitario'] * $campos_pas[$i]['qtde'];
    }
?>
    <tr class='linhacabecalho'>
        <td colspan='4'>
            Peso Total: <?=number_format($peso_total, 4, ',', '.');?>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>