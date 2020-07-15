<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

$sql = "SELECT COUNT(DISTINCT(id_pedido)) AS total_registro 
        FROM `pedidos` ";
$campos_total = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'].'<br>';

if($total_registro == $indice) exit('SCRIPT EXECUTADO COM SUCESSO !');

$sql = "SELECT id_pedido 
        FROM `pedidos` ";
$campos = bancos::sql($sql, $indice, 1);

$sql = "SELECT id_item_pedido, status 
        FROM `itens_pedidos` 
        WHERE `id_pedido` = '".$campos[0]['id_pedido']."' ";
$campos_itens = bancos::sql($sql);
$linhas_itens = count($campos_itens);
if($linhas_itens == 0) {
    $status_pedido = 1;//Em Aberto ...
}else {
    //Valor Default de Status do Pedido, como se estivesse fechado ...
    $status_pedido = 2;//Fechado ...

    for($i = 0; $i < $linhas_itens; $i++) {
        if($campos_itens[$i]['status'] == 0 || $campos_itens[$i]['status'] == 1) {//Se encontrou um item Aberto ou Parcial ...
            $status_pedido = 1;//Em Aberto ...
            break;
        }
    }
}
$sql = "UPDATE `pedidos` SET `status` = '$status_pedido' WHERE `id_pedido` = '".$campos[0]['id_pedido']."' LIMIT 1 ";
echo $sql.'<br/>';
bancos::sql($sql);
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_consertar_status_pedidos_compras.php?indice=<?=++$indice;?>'
</Script>