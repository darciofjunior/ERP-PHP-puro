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

//Verifico se esse Fornecedor do Pedido existe na Tabela de Fornecedores ...
$sql = "SELECT p.id_fornecedor 
        FROM `pedidos` p 
        INNER JOIN `fornecedores` f ON f.id_fornecedor = p.id_fornecedor 
        WHERE `id_pedido` = '".$campos[0]['id_pedido']."' LIMIT 1 ";
$campos_fornecedor = bancos::sql($sql);
if(count($campos_fornecedor) == 0) {//Se Fornecedor não foi Encontrado, mudo o mesmo pelo '859' e acrescento uma Observação ...
    $sql = "UPDATE `pedidos` SET `id_fornecedor` = '859', `observacao` = CONCAT('Fornecedor foi excluído do banco de dados, por isso alteramos para OUTROS / COMPRAS. ', `observacao`) WHERE `id_pedido` = '".$campos[0]['id_pedido']."' LIMIT 1 ";
    echo $sql.'<br/>';
    bancos::sql($sql);
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_consertar_fornecedor_pedidos_compras.php?indice=<?=++$indice;?>'
</Script>