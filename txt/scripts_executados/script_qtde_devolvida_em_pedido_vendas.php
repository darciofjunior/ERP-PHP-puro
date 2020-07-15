<?
require('../../lib/segurancas.php');
session_start('funcionarios');

if(empty($indice)) $indice = 0;

//Busco todos os itens de Nota Fiscal que tiveram Devolução ...
$sql = "SELECT COUNT(id_nfs_item) AS total_registro 
        FROM `nfs_itens` 
        WHERE id_nf_item_devolvida <> '0' ";
$campos_total = bancos::sql($sql);
$total_registro = $campos_total[0]['total_registro'];

echo $total_registro.' / '.$indice;

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

$sql = "SELECT id_pedido_venda_item, qtde_devolvida 
        FROM `nfs_itens` 
        WHERE id_nf_item_devolvida <> '0' ";
$campos = bancos::sql($sql, $indice, 1);

/*Atualizo o item de Pedido de Venda com a Qtde que foi Devolvida em Nota Fiscal, na intenção de corrigir e 
agilizar alguns Relatórios de Pedidos ...*/
$sql = "UPDATE `pedidos_vendas_itens` SET `qtde_devolvida` = `qtde_devolvida` + '".$campos[0]['qtde_devolvida']."' WHERE `id_pedido_venda_item` = '".$campos[0]['id_pedido_venda_item']."' LIMIT 1 ";
bancos::sql($sql);
echo '<br/><br/>'.$sql;
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_qtde_devolvida_em_pedido_vendas.php?indice=<?=++$indice;?>'
</Script>