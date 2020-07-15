<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

//Todos os Produtos ...
$sql = "SELECT COUNT(`id_cliente_follow_up`) AS total_registro 
        FROM `clientes_follow_ups` 
        WHERE `origem` = '11' ";
$campos_total   = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'];

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

$sql = "SELECT * 
        FROM `clientes_follow_ups` 
        WHERE `origem` = '11' ";
$campos = bancos::sql($sql, $indice, 1);
    
/*id_cliente_follow_up
id_cliente_contato
id_representante
id_funcionario
id_funcionario_originador
identificacao
origem
observacao
observacao_retorno
data_ocorrencia
data_retorno
tipo_ocorrencia
intermodular
modo_venda
ativo_passivo
status_ocorrencia*/

$sql = "SELECT `id_cliente` 
        FROM `clientes_contatos` 
        WHERE `id_cliente_contato`  = '".$campos[0]['id_cliente_contato']."' LIMIT 1 ";
$campos_cliente = bancos::sql($sql);
    
$sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_cliente_contato`, `id_representante`, `id_funcionario`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '".$campos_cliente[0]['id_cliente']."', '".$campos[0]['id_cliente_contato']."', '".$campos[0]['id_representante']."', '".$campos[0]['id_funcionario']."', '".$campos[0]['origem']."', '".$campos[0]['observacao']."', '".$campos[0]['data_ocorrencia']."') ";
bancos::sql($sql);
echo '<br/><br/>'.$sql;

/*if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

$sql = "SELECT DISTINCT(pv.`id_pedido_venda`) AS id_pedido_venda 
        FROM `pedidos_vendas` pv 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
        WHERE pv.`observacao` <> '' ";
$campos_total   = bancos::sql($sql);
echo $total_registro = count($campos_total);

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

$sql = "SELECT DISTINCT(pv.`id_pedido_venda`), pv.`id_cliente_contato`, pv.`id_cliente`, 
        pv.`id_funcionario`, pv.`observacao` 
        FROM `pedidos_vendas` pv 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
        WHERE pv.`observacao` <> '' ";
$campos = bancos::sql($sql, $indice, 1);
$linhas = count($campos);

for($i = 0; $i < $linhas; $i++) {
    
    $sql = "SELECT `id_representante` 
            FROM `pedidos_vendas_itens` 
            WHERE `id_pedido_venda` = '".$campos[$i]['id_pedido_venda']."' LIMIT 1 ";
    $campos_representante = bancos::sql($sql);
    
    $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_cliente_contato`, `id_representante`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '".$campos[$i]['id_cliente']."', '".$campos[$i]['id_cliente_contato']."', '".$campos_representante[0]['id_representante']."', '".$campos[$i]['id_funcionario']."', '".$campos[$i]['id_pedido_venda']."', '2', '".addslashes($campos[$i]['observacao'])."', '".date('Y-m-d H:i:s')."'); ";
    echo '<br/>'.$sql;
    bancos::sql($sql);
}*/
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_follow_ups.php?indice=<?=++$indice;?>'
</Script>