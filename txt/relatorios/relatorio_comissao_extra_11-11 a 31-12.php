<?
require('../../lib/segurancas.php');

$sql = "SELECT gpa.nome, DATE_FORMAT(ov.data_emissao, '%d/%m/%Y') AS data_emissao, ovi.id_orcamento_venda, ovi.comissao_extra, (ovi.qtde * ovi.preco_liq_final) valor_do_item, pa.referencia, pa.discriminacao 
        FROM `orcamentos_vendas_itens` ovi 
        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
        INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.id_familia = '3' 
        INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda AND ov.data_emissao BETWEEN '2010-11-11' AND '2010-12-31' 
        WHERE ovi.`comissao_extra` > '0' ORDER BY ov.data_emissao, ovi.id_orcamento_venda, pa.referencia ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Comissão Extra paga em 11/11 à 31/12 - Grupo de Limas ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<body>
<form name='form'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Comissão Extra paga em 11/11 à 31/12 - Grupo de Limas
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.º Orc
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            Grupo
        </td>
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Valor do Item
        </td>
        <td>
            % Comissão Extra
        </td>
        <td>
            Pago Extra
        </td>
    </tr>
<?
    for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos[$i]['id_orcamento_venda'];?>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td align='right'>
            R$ <?=number_format($campos[$i]['valor_do_item'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['comissao_extra'], 1, ',', '.');?>
        </td>
        <td align='right'>
            R$ <?=number_format($campos[$i]['valor_do_item'] * ($campos[$i]['comissao_extra'] / 100), 2, ',', '.');?>
        </td>
    </tr>
<?
        $total_itens+= $campos[$i]['valor_do_item'];
        $total_comissoes+= $campos[$i]['valor_do_item'] * ($campos[$i]['comissao_extra'] / 100);
    }
?>
    <tr class='linhacabecalho' align='right'>
        <td colspan='5'>
            <font color='yellow'>
                Total de Vendas: 
            </font>
        </td>
        <td>
            R$ <?=number_format($total_itens, 2, ',', '.');?>
        </td>
        <td>
            <font color='yellow'>
                Total Comissão: 
            </font>
        </td>
        <td>
            R$ <?=number_format($total_comissoes, 2, ',', '.');?>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
</html>