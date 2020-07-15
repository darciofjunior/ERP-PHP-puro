<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

$sql = "SELECT DISTINCT(`id_cliente`) AS id_cliente 
        FROM `clientes` 
        WHERE `observacao` <> '' ";
$campos_total   = bancos::sql($sql);
echo $total_registro = count($campos_total);

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

$sql = "SELECT * 
        FROM `clientes` 
        WHERE `observacao` <> '' ";
$campos = bancos::sql($sql, $indice, 1);
$linhas = count($campos);

for($i = 0; $i < $linhas; $i++) {
    $id_funcionario = (!empty($campos[0]['id_funcionario'])) ? $campos[0]['id_funcionario'] : 'NULL';
    
    
    $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_funcionario`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '".$campos[$i]['id_cliente']."', $id_funcionario, '15', '".addslashes($campos[$i]['observacao'])."', '".$campos[$i]['data_cadastro']."'); ";
    echo '<br/><br/>'.$sql;
    bancos::sql($sql);
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_follow_ups.php?indice=<?=++$indice;?>'
</Script>