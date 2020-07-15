<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

//Todos os Pedidos de Vendas ...
$sql = "SELECT COUNT(nfeh.id_nfe_historico) AS total_registro 
        FROM `nfe_historicos` nfeh 
        WHERE nfeh.`id_produto_insumo` = '0' ";
$campos_total = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'];

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

$sql = "SELECT nfeh.*, nfe.id_fornecedor 
        FROM `nfe_historicos` nfeh 
        INNER JOIN `nfe` ON nfe.id_nfe = nfeh.id_nfe 
        WHERE nfeh.`id_produto_insumo` = '0' ";

echo $sql;
exit;

$campos = bancos::sql($sql, $indice, 1);

//Busca do 1º Pedido do Fornecedor da NF ...
$sql = "SELECT id_pedido 
        FROM `pedidos` 
        WHERE `id_fornecedor` = '".$campos[0]['id_fornecedor']."' LIMIT 1 ";
$campos_pedido = bancos::sql($sql);

echo '<br/><br/>';

echo $sql = "INSERT INTO `itens_pedidos` (`id_item_pedido`, `id_pedido`, `id_produto_insumo`, `id_fornecedor`, `id_fornecedor_terceiro`, `preco_unitario`, `qtde`, `valor_total`, `ipi`, `marca`, `status`) VALUES (NULL, '".$campos_pedido[0]['id_pedido']."', '1340', '".$campos[0]['id_fornecedor']."', '0', '".$campos[0]['valor_entregue']."', '".$campos[0]['qtde_entregue']."', '".($campos[0]['qtde_entregue'] * $campos[0]['valor_entregue'])."', '".$campos[0]['ipi_entregue']."', '".$campos[0]['marca']."', '2') ";
bancos::sql($sql);
$id_item_pedido = bancos::id_registro();


echo '<br/><br/>';
//Aqui eu atualizo o item de Nota Fiscal com dados do item de Pedido que acabou de ser incluso ...
echo $sql = "UPDATE `nfe_historicos` SET `id_item_pedido` = '$id_item_pedido', `id_produto_insumo` = '1340', `id_pedido` = '".$campos_pedido[0]['id_pedido']."' WHERE `id_nfe_historico` = '".$campos[0]['id_nfe_historico']."' LIMIT 1 ";
bancos::sql($sql);
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_itens_notas_fiscais.php?indice=<?=++$indice;?>'
</Script>