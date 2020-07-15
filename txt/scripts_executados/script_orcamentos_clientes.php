<?
require('../../lib/segurancas.php');

$sql = "SELECT id_orcamento_venda, id_cliente_contato 
        FROM orcamentos_vendas 
        WHERE `id_cliente` = '0' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
//Busca o id_cliente através do id_contato_cliente do Orçamento ...
    $sql = "SELECT id_cliente 
            FROM `clientes_contatos` 
            WHERE `id_cliente_contato` = '".$campos[$i]['id_cliente_contato']."' LIMIT 1 ";
    $campos_cliente = bancos::sql($sql);
//Atualizo o id_cliente no Orçamento para agilizar a busca ...
    $sql = "UPDATE `orcamentos_vendas` SET `id_cliente` = '".$campos_cliente[0]['id_cliente']."' WHERE `id_orcamento_venda` = '".$campos[$i]['id_orcamento_venda']."' LIMIT 1 ";
    bancos::sql($sql);
}
echo 'FIM';
?>