<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/melhores_clientes/melhores_clientes.php', '../../../../');
?>
<html>
<head>
<title>.:: Detalhe(s) por Divisão ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
<?
$sql = "SELECT SUM(nfsi.qtde * nfsi.valor_unitario) total, ged.id_empresa_divisao 
        FROM `nfs` 
        INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf 
        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = nfsi.id_produto_acabado 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
        WHERE nfs.data_emissao BETWEEN '$_GET[data_inicial]' AND '$_GET[data_final]' 
        AND nfs.id_cliente = '$_GET[id_cliente]' 
        GROUP BY ged.id_empresa_divisao ";
$campos_divisao = bancos::sql($sql);
$linhas_divisao = count($campos_divisao);
for($i = 0; $i < $linhas_divisao; $i++) $valor_divisao_do_cliente[$campos_divisao[$i]['id_empresa_divisao']] = $campos_divisao[$i]['total'];
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Detalhe(s) por Divisão
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Cliente
        </td>
        <td>
            Cabri R$
        </td>
        <td>
            Heinz R$
        </td>
        <td>
            Warrior R$
        </td>
        <td>
            Tool Master R$
        </td>
        <td>
            NVO R$
        </td>
        <td>
            Heinz-Pinos R$
        </td>
        <td>
            Total Faturado<font color='red'>**</font>&nbsp;R$
        </td>
    </tr>
    <tr class='linhanormal' align='right'>
        <td align='left'>
        <?
            $sql = "SELECT IF(razaosocial = '', nomefantasia, razaosocial) AS cliente 
                    FROM `clientes` 
                    WHERE `id_cliente` = '$_GET[id_cliente]' LIMIT 1 ";
            $campos_cliente = bancos::sql($sql);
            echo $campos_cliente[0]['cliente'];
        ?>
        </td>
        <td>
            <?if(!empty($valor_divisao_do_cliente[1])) echo number_format($valor_divisao_do_cliente[1], 2, ',', '.');?>
        </td>
        <td>
            <?if(!empty($valor_divisao_do_cliente[2])) echo number_format($valor_divisao_do_cliente[2], 2, ',', '.');?>
        </td>
        <td>
            <?if(!empty($valor_divisao_do_cliente[3])) echo number_format($valor_divisao_do_cliente[3], 2, ',', '.');?>
        </td>
        <td>
            <?if(!empty($valor_divisao_do_cliente[4])) echo number_format($valor_divisao_do_cliente[4], 2, ',', '.');?>
        </td>
        <td>
            <?if(!empty($valor_divisao_do_cliente[5])) echo number_format($valor_divisao_do_cliente[5], 2, ',', '.');?>
        </td>
        <td>
            <?if(!empty($valor_divisao_do_cliente[6])) echo number_format($valor_divisao_do_cliente[6], 2, ',', '.');?>
        </td>
        <td>
            <b><?=number_format($valor_divisao_do_cliente[1] + $valor_divisao_do_cliente[2] + $valor_divisao_do_cliente[3] + $valor_divisao_do_cliente[4] + $valor_divisao_do_cliente[5] + $valor_divisao_do_cliente[6], 2, ',', '.');?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            &nbsp;
        </td>
    </tr>
</table>
</form>
</body>
</html>