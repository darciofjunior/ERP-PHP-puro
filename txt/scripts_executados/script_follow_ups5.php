<?
require('../../lib/segurancas.php');

/*if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

$sql = "SELECT DISTINCT(p.`id_pedido`) AS id_pedido 
        FROM `pedidos` p 
        INNER JOIN `itens_pedidos` ip ON ip.`id_pedido` = p.`id_pedido` 
        WHERE p.`observacao` <> '' ";
$campos_total   = bancos::sql($sql);
echo $total_registro = count($campos_total);

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

$sql = "SELECT DISTINCT(p.`id_pedido`) AS id_pedido, p.`id_fornecedor`, p.`id_funcionario_cotado`, 
        p.`observacao`, p.`data_emissao` 
        FROM `pedidos` p 
        INNER JOIN `itens_pedidos` ip ON ip.`id_pedido` = p.`id_pedido` 
        WHERE p.`observacao` <> '' ";
$campos = bancos::sql($sql, $indice, 1);
$linhas = count($campos);

for($i = 0; $i < $linhas; $i++) {
    $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_fornecedor`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '".$campos[$i]['id_fornecedor']."', '".$campos[$i]['id_funcionario_cotado']."', '".$campos[$i]['id_pedido']."', '16', '".addslashes($campos[$i]['observacao'])."', '".$campos[$i]['data_emissao']."'); ";
    echo '<br/>'.$sql;
    bancos::sql($sql);
}*/

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

$sql = "SELECT DISTINCT(`id_pedido_parecer`) AS id_pedido_parecer 
        FROM `pedidos_pareceres` 
        WHERE `observacao` <> '' ";
$campos_total   = bancos::sql($sql);
echo $total_registro = count($campos_total);

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

$sql = "SELECT * 
        FROM `pedidos_pareceres` 
        WHERE `observacao` <> '' ";
$campos = bancos::sql($sql, $indice, 1);
$linhas = count($campos);

for($i = 0; $i < $linhas; $i++) {
    $sql = "SELECT id_fornecedor 
            FROM `pedidos` 
            WHERE `id_pedido` = '".$campos[$i]['id_pedido']."' LIMIT 1 ";
    $campos_fornecedor = bancos::sql($sql);
    
    $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_fornecedor`, `id_funcionario`, `identificacao`, `origem`, `data_entrega_embarque`, `observacao`, `data_sys`) VALUES (NULL, '".$campos_fornecedor[0]['id_fornecedor']."', '".$campos[$i]['id_funcionario']."', '".$campos[$i]['id_pedido']."', '16', '".$campos[$i]['data']."', '".addslashes($campos[$i]['observacao'])."', '".$campos[$i]['data_sys']."'); ";
    echo '<br/><br/>'.$sql;
    bancos::sql($sql);
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_follow_ups.php?indice=<?=++$indice;?>'
</Script>