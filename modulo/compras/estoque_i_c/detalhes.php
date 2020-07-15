<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/estoque_i_c/inventario.php', '../../../');

$sql = "SELECT g.referencia, pi.discriminacao, pi.observacao 
        FROM `produtos_insumos` pi 
        INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
        WHERE pi.`id_produto_insumo` = '$_GET[id_produto_insumo]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Detalhes ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Detalhes
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td>
            <font color='yellow'>Referência: </font>
            <?=$campos[0]['referencia'];?>
        </td>
        <td>
            <font color='yellow'>Discriminação: </font>
            <?=$campos[0]['discriminacao'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <a href="javascript:window.location = 'detalhes_baixas_manipulacoes.php?id_produto_insumo=<?=$_GET['id_produto_insumo'];?>'" title="Consultar Baixa(s) / Manipulação(ões)" class="link">
                * Consultar Compra(s) / Baixa(s) / Manipulação(ões)
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <a href="javascript:window.location = '../produtos_fornecedores/comparativo/itens.php?id_prods_insumos=<?=$_GET['id_produto_insumo'];?>'" title="Comparativo" class="link">
                * Comparativo
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <a href="javascript:window.location = 'nivel_estoque/pendencias_item.php?id_produto_insumo=<?=$_GET['id_produto_insumo'];?>'" title="Pendência de Pedidos (Compra Produção)" class="link">
                * Pendência de Pedidos (Compra Produção)
            </a>
        </td>
    </tr>
<?
//Se o Produto Insumo for Produto Acabado, então eu exibo esse link a +, para visualizar o Estoque do P.A...
    $sql = "SELECT id_produto_acabado 
            FROM `produtos_acabados` 
            WHERE `id_produto_insumo` = '$_GET[id_produto_insumo]' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos_pipa = bancos::sql($sql);
    if(count($campos_pipa) == 1) {
?>
    <tr class='linhanormal'>
        <td colspan='2'>
            <a href="javascript:nova_janela('../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$campos_pipa[0]['id_produto_acabado'];?>', 'POP', '', '', '', '', 300, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Estoque" class="link">
                <font color="green">
                    * Produto Acabado - Visualizar Estoque
                </font>
            </a>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhanormal'>
        <td>
            <b>Observação:</b>
        </td>
        <td>
            <textarea name='txt_observacao' title="Observação" cols='80' rows='2' class='textdisabled' disabled><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align="center">
        <td colspan='2'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</body>
</html>