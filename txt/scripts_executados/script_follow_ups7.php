<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

$sql = "SELECT DISTINCT(`id_cliente_follow_up`) AS id_cliente_follow_up 
        FROM `clientes_follow_ups` 
        WHERE `origem` = '7' ";
$campos_total   = bancos::sql($sql);
echo $total_registro = count($campos_total);

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

$sql = "SELECT * 
        FROM `clientes_follow_ups` 
        WHERE `origem` = '7' ";
$campos = bancos::sql($sql, $indice, 1);
$linhas = count($campos);

for($i = 0; $i < $linhas; $i++) {
    //Aqui eu trago o "id_cliente" através do "id_cliente_contato" ...
    $sql = "SELECT `id_cliente` 
            FROM `clientes_contatos` 
            WHERE `id_cliente_contato`  = '".$campos[$i]['id_cliente_contato']."' LIMIT 1 ";
    $campos_cliente = bancos::sql($sql);
    
    $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_cliente_contato`, `id_funcionario`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '".$campos_cliente[0]['id_cliente']."', '".$campos[$i]['id_cliente_contato']."', '".$campos[$i]['id_funcionario']."', '7', '".addslashes($campos[$i]['observacao'])."', '".$campos[$i]['data_ocorrencia']."'); ";
    echo '<br/><br/>'.$sql;
    bancos::sql($sql);
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_follow_ups.php?indice=<?=++$indice;?>'
</Script>