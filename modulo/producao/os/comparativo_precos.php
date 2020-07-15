<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

//Aqui eu só trago o(s) PI(s) que são do Grupo 'TRAT' ...
$sql = "SELECT pi.`id_produto_insumo`, pi.`discriminacao` 
        FROM `produtos_insumos` pi 
        INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` AND g.`ativo` = '1' AND g.`referencia` = 'TRAT' 
        WHERE pi.`ativo` = '1' ORDER BY pi.discriminacao ";
$campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Comparativo de Preço(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Comparativo de Preço(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Discriminação
        </td>
        <td>
            Traternit
        </td>
        <td>
            Nova Chama
        </td>
        <td>
            Ultraterm
        </td>
        <td>
            Tenaz
        </td>
    </tr>
<?
    for ($i = 0;  $i < $linhas; $i++) {
//Aqui eu trago o Preço de Lista do PI corrente e dos 4 fornecedores em questão ...
/**
 * 1452 - Traternit ...
 * 969 - Nova Chama ...
 * 1216 - Ultraterm ...
 * 413 - Tenaz
 **/
?>
    <tr class='linhanormal'>
        <td>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td align='right'>
        <?
            $sql = "SELECT preco_faturado 
                    FROM `fornecedores_x_prod_insumos` 
                    WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' 
                    AND `id_fornecedor` = '1452' LIMIT 1 ";
            $campos_preco = bancos::sql($sql);
            if(count($campos_preco) == 1 && $campos_preco[0]['preco_faturado'] > 0) {
                echo number_format($campos_preco[0]['preco_faturado'], 2, ',', '.');
            }
        ?>
        </td>
        <td align='right'>
        <?
            $sql = "SELECT preco_faturado 
                    FROM `fornecedores_x_prod_insumos` 
                    WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' 
                    AND `id_fornecedor` = '969' LIMIT 1 ";
            $campos_preco = bancos::sql($sql);
            if(count($campos_preco) == 1 && $campos_preco[0]['preco_faturado'] > 0) {
                echo number_format($campos_preco[0]['preco_faturado'], 2, ',', '.');
            }
        ?>
        </td>
        <td align='right'>
        <?
            $sql = "SELECT preco_faturado 
                    FROM `fornecedores_x_prod_insumos` 
                    WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' 
                    AND `id_fornecedor` = '1216' LIMIT 1 ";
            $campos_preco = bancos::sql($sql);
            if(count($campos_preco) == 1 && $campos_preco[0]['preco_faturado'] > 0) {
                echo number_format($campos_preco[0]['preco_faturado'], 2, ',', '.');
            }
        ?>
        </td>
        <td align='right'>
        <?
            $sql = "SELECT preco_faturado 
                    FROM `fornecedores_x_prod_insumos` 
                    WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' 
                    AND `id_fornecedor` = '413' LIMIT 1 ";
            $campos_preco = bancos::sql($sql);
            if(count($campos_preco) == 1 && $campos_preco[0]['preco_faturado'] > 0) {
                echo number_format($campos_preco[0]['preco_faturado'], 2, ',', '.');
            }
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>