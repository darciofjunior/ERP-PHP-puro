<?
require('../../lib/segurancas.php');
require('../../lib/data.php');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

$sql = "SELECT pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao` 
       FROM `produtos_acabados` pa 
       INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
       INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` NOT IN (23, 24) 
       WHERE pa.referencia <> 'ESP'
       AND pa.ativo = '1' ORDER BY pa.referencia, pa.discriminacao ";
$campos = bancos::sql($sql);
$linhas = count($campos);
        
?>
<html>
<head>
<title>.:: Produtos Albafer ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../js/data.js'></Script>
</Script>
</head>
<body>
<form name='form' action='' method='post'>
<table width='80%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Produtos Albafer            
        </td>
    </tr>
<?
/*****************Se já submeteu então*****************/
    if($linhas == 0) {//Se não trazer nenhum registro então ...
?>
    <tr class='atencao' align='center'>
        <td colspan='2'>
            <?=$mensagem[1];?>
        <td>
    </tr>
<?
    }else {
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Referência
        </td>
        <td>
            Discriminaçao
        </td> 
        <td>
            Ultimo preço - Data
        </td>
    </tr>
<?        
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td>
            <?=$campos[$i]['discriminacao'];?>
        </td>   
        <td>
            <?
                $sql = "SELECT pvi.preco_liq_final, pv.data_emissao
                        FROM `pedidos_vendas_itens` pvi
                        INNER JOIN `pedidos_vendas` pv ON pv.id_pedido_venda = pvi.id_pedido_venda AND pv.id_cliente = 118
                        WHERE pvi.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                        ORDER BY pvi.id_pedido_venda_item DESC LIMIT 1";
                $campos_item = bancos::sql($sql);
                if(count($campos_item) == 1) echo $campos_item[0]['preco_liq_final'].' - '.data::datetodata($campos_item[0]['data_emissao'], '/');
            ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho'>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
<?
    }
?>
</table>
</form>
</body>
</html>