<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

$sql = "SELECT COUNT(id_cliente_follow_up) AS total_registro 
        FROM `clientes_follow_ups` 
        WHERE `id_representante` = '0' ";
$campos_total   = bancos::sql($sql);
$total_registro = $campos_total[0]['total_registro'];

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

$sql = "SELECT id_cliente_follow_up, id_cliente_contato 
        FROM `clientes_follow_ups` 
        WHERE `id_representante` = '0' ";
$campos = bancos::sql($sql, $indice, 1);

//Aqui eu guardo o id_representante no Registro de Follow-UP p/ agilizar o processamento da tela de PDT ...
$sql = "SELECT cr.`id_representante` 
        FROM `clientes_contatos` cc 
        INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
        INNER JOIN `clientes_vs_representantes` cr ON cr.`id_cliente` = c.`id_cliente` 
        WHERE cc.`id_cliente_contato` = '".$campos[0]['id_cliente_contato']."' LIMIT 1 ";
$campos_representante = bancos::sql($sql);

//Atualizo o Pedido com a Comissão Média que foi calculada acima ...
echo $sql = "UPDATE `clientes_follow_ups` SET `id_representante` = '".$campos_representante[0]['id_representante']."' WHERE `id_cliente_follow_up` = '".$campos[0]['id_cliente_follow_up']."' LIMIT 1 ";
bancos::sql($sql);

echo '<br/><br/>Total de Registro(s): '.($indice.'/'.$total_registro);
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_clientes_follow_ups.php?indice=<?=++$indice;?>'
</Script>