<?
require('../../lib/segurancas.php');

$sql = "SELECT id_nf, id_cliente_contato 
        FROM nfs 
        WHERE `id_cliente` = '0' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
//Busca o id_cliente através do id_contato_cliente do Pedido ...
    $sql = "SELECT id_cliente 
            FROM `clientes_contatos` 
            WHERE `id_cliente_contato` = '".$campos[$i]['id_cliente_contato']."' LIMIT 1 ";
    $campos_cliente = bancos::sql($sql);
//Atualizo o id_cliente na NF para agilizar a busca ...
    $sql = "UPDATE `nfs` SET `id_cliente` = '".$campos_cliente[0]['id_cliente']."' WHERE `id_nf` = '".$campos[$i]['id_nf']."' LIMIT 1 ";
    bancos::sql($sql);
}
echo 'FIM';
?>