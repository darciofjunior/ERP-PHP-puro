<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

$sql = "SELECT COUNT(DISTINCT(id_pedido)) AS total_registro 
        FROM `pedidos` 
        WHERE `status` = '1' ";
$campos_total = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'].'<br>';

if($total_registro == $indice) exit('SCRIPT EXECUTADO COM SUCESSO !');

$sql = "SELECT id_pedido 
        FROM `pedidos` 
        WHERE `status` = '1' ";
$campos = bancos::sql($sql, $indice, 1);

$sql = "SELECT id_item_pedido, qtde 
        FROM `itens_pedidos` 
        WHERE `id_pedido` = '".$campos[0]['id_pedido']."' ";
$campos_itens = bancos::sql($sql);
$linhas_itens = count($campos_itens);
for($i = 0; $i < $linhas_itens; $i++) {
    $sql = "SELECT SUM(qtde_entregue) AS total_entregue 
            FROM `nfe_historicos` 
            WHERE `id_item_pedido` = '".$campos_itens[$i]['id_item_pedido']."' ";
    $campos_nfe     = bancos::sql($sql);
    $total_entregue = $campos_nfe[0]['total_entregue'];
    
    if($campos_itens[$i]['qtde'] == $total_entregue) {
        echo $sql = "UPDATE `itens_pedidos` SET `status` = '2' WHERE `id_item_pedido` = '".$campos_itens[$i]['id_item_pedido']."' LIMIT 1 ";
        echo '<br/>';
        bancos::sql($sql);
    }
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_consertar_status_itens_pedidos_compras.php?indice=<?=++$indice;?>'
</Script>