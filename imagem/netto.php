<?
require('../lib/segurancas.php');
require('../lib/menu/menu.php');
require('../lib/intermodular.php');

$sql_compraram =    "SELECT c.id_cliente
                    FROM  `clientes` c
                    INNER JOIN  `pedidos_vendas` pv ON pv.id_cliente = c.id_cliente
                    INNER JOIN  `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda AND pv.liberado = '1' AND pv.`data_emissao` >= '2013-08-01'
                    INNER JOIN  `produtos_acabados` pa ON pa.id_produto_acabado = pvi.id_produto_acabado
                    INNER JOIN  `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div
                    INNER JOIN  `grupos_pas` gp ON gp.id_grupo_pa = ged.id_grupo_pa
                    AND gp.id_familia =  '3'
                    WHERE c.ativo =  '1'
                    GROUP BY pv.id_cliente";
$campos_compraram = bancos::sql($sql_compraram);
$linhas_compraram = count($campos_compraram);
for($i = 0;$i < $linhas_compraram; $i++) {
    $ids_compraram.= $campos_compraram[$i]['id_cliente'].', ';
}
$size = strlen($ids_compraram);
$ids_compraram = substr($ids_compraram,0, $size - 2);

$sql_nao_compraram = "SELECT c.id_cliente
                    FROM  `clientes` c
                    INNER JOIN  `clientes_vs_representantes` cr ON cr.id_cliente = c.id_cliente
                    INNER JOIN  `representantes` r ON r.id_representante = cr.id_representante
                    INNER JOIN  `ufs` u ON u.id_uf = c.id_uf
                    INNER JOIN  `pedidos_vendas` pv ON pv.id_cliente = c.id_cliente
                    INNER JOIN  `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda AND pv.liberado = '1' 
                    INNER JOIN  `produtos_acabados` pa ON pa.id_produto_acabado = pvi.id_produto_acabado
                    INNER JOIN  `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div
                    INNER JOIN  `grupos_pas` gp ON gp.id_grupo_pa = ged.id_grupo_pa
                    AND gp.id_familia =  '3'
                    WHERE c.ativo =  '1' 
                    AND pv.id_cliente NOT IN($ids_compraram)
                    GROUP BY pv.id_cliente ORDER BY id_cliente";
$campos_nao_compraram = bancos::sql($sql_nao_compraram);
echo $campos_nao_compraram;
exit;
$linhas_nao_compraram = count($campos_nao_compraram);
echo '<br>'.$campos_nao_compraram;;

for($j = 0; $j < $linhas_nao_compraram; $j++) {
    $ids_nao_compraram.= $campos_nao_compraram[$j]['id_cliente'].', ';
}
$size_nao_compram = strlen($ids_nao_compraram);
$ids_nao_compraram = substr($ids_nao_compraram,0, $size_nao_compram - 2);
echo $ids_nao_compraram;
echo '<br>aaaa';
exit;
$sql_12_meses = "SELECT CONCAT(c.nomefantasia, ' - ', c.razaosocial) AS cliente, SUM(pvi.qtde * pvi.preco_liq_final) AS total_comprado
                FROM  `clientes` c
                INNER JOIN  `pedidos_vendas` pv ON pv.id_cliente = c.id_cliente
                INNER JOIN  `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda AND pv.liberado = '1' AND pv.data_emissao BETWEEN '2012-07-31' AND '2013-07-31' 
                INNER JOIN  `produtos_acabados` pa ON pa.id_produto_acabado = pvi.id_produto_acabado
                INNER JOIN  `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div
                INNER JOIN  `grupos_pas` gp ON gp.id_grupo_pa = ged.id_grupo_pa
                AND gp.id_familia =  '3'
                WHERE c.ativo =  '1'
                AND pv.id_cliente IN ($ids_nao_compraram)
                GROUP BY pv.id_cliente ORDER BY id_cliente";
echo $sql_12_meses;
exit;
$campos_12_meses = bancos::sql($sql_12_meses);

?>
<html>
<head>
<title>.:: Consultar OE(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
	<tr class='linhacabecalho' align='center'>
            <td colspan="2">
                Consultar OE
            </td>
	</tr>
	<tr class='linhanormal' align='center'>
            <td colspan='2'>
                Consultar <input type="text" name="txt_consultar" size="45" maxlength='45' class="caixadetexto">
            </td>
	</tr>
	<tr class='linhanormal'>
            <td width="20%">
                <input type="radio" name="opt_opcao" value="1" onclick='document.form.txt_consultar.focus()' title="Consultar O.E(s) por: Referência" id='label' checked>
                <label for='label'>
                    Referência
                </label>
            </td>
            <td width="20%">
                <input type="radio" name="opt_opcao" value="2" onclick='document.form.txt_consultar.focus()' title="Consultar O.E(s) por: Discriminação" id='label2'>
                <label for='label2'>
                    Discrimina&ccedil;&atilde;o
                </label>
            </td>
	</tr>
	<tr class="linhanormal">
            <td width="20%">
                <input type="radio" name="opt_opcao" value="3" onclick='document.form.txt_consultar.focus()' title="Consultar O.E(s) por: Número da O.E." id='label3'>
                <label for='label3'>
                    Número da O.E.
                </label>
            </td>
            <td width="20%">
                <input type='checkbox' name='opcao' value='1' title="Consultar todas as O.E(s)" onClick='limpar()' id='label4' class="checkbox">
                <label for='label4'>
                    Todos os registros
                </label>
            </td>
	</tr>
	<tr class="linhacabecalho" align="center">
            <td colspan="2">
                <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = false;limpar()" style="color:#ff9900;" class="botao">
                <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
            </td>
	</tr>
</table>
</form>
</body>
</html>