<?
require('../../lib/segurancas.php');
if(empty($indice)) $indice = 0;

//Todos os Pedidos de Vendas ...
$sql = "SELECT count(id_pedido_venda_item) total_registro 
        FROM `pedidos_vendas_itens` 
        WHERE `id_funcionario` = '0' and id_representante <> '71' ";
$campos_total = bancos::sql($sql);
$total_registro = $campos_total[0]['total_registro'];

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

//Busca todos os Pedidos que tem o Funcionário como sendo Rivaldo ...
$sql = "SELECT id_pedido_venda_item, id_representante 
        FROM `pedidos_vendas_itens` 
        WHERE `id_funcionario` = '0' 
        AND `id_representante` <> '71' ORDER BY id_pedido_venda_item ";
$campos = bancos::sql($sql, $indice, 1);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $sql = "SELECT id_funcionario 
            FROM `representantes_vs_funcionarios` 
            WHERE `id_representante` = '".$campos[$i]['id_representante']."' LIMIT 1 ";
    $campos_funcionario = bancos::sql($sql);
    if(count($campos_funcionario) == 1) {//Se encontrou funcionário legal ...
        echo $sql = "UPDATE `pedidos_vendas_itens` SET `id_funcionario` = '".$campos_funcionario[0]['id_funcionario']."' WHERE `id_pedido_venda_item` = '".$campos[$i]['id_pedido_venda_item']."' LIMIT 1 ";
        bancos::sql($sql);
    }else {//Busca qual é o Supervisor deste Representante ...
        $sql = "SELECT rf.id_funcionario 
                FROM `representantes_vs_supervisores` rs 
                INNER JOIN `representantes_vs_funcionarios` rf on rf.id_representante = rs.id_representante_supervisor 
                WHERE rs.id_representante = '".$campos[$i]['id_representante']."' LIMIT 1 ";
        $campos_funcionario = bancos::sql($sql);
        echo $sql = "UPDATE `pedidos_vendas_itens` SET `id_funcionario` = '".$campos_funcionario[0]['id_funcionario']."' WHERE `id_pedido_venda_item` = '".$campos[$i]['id_pedido_venda_item']."' LIMIT 1 ";
        bancos::sql($sql);
    }
    echo '<br>';
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_representantes_funcionarios.php?indice=<?=++$indice;?>'
</Script>