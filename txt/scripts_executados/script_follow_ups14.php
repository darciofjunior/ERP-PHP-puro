<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

$sql = "SELECT DISTINCT(`id_conta_apagar`) AS id_conta_apagar 
        FROM `contas_apagares` 
        WHERE `observacao` <> '' ";
$campos_total   = bancos::sql($sql);
echo $total_registro = count($campos_total);

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

$sql = "SELECT * 
        FROM `contas_apagares` 
        WHERE `observacao` <> '' ";
$campos = bancos::sql($sql, $indice, 1);
$linhas = count($campos);

for($i = 0; $i < $linhas; $i++) {
    $id_fornecedor      = ($campos[$i]['id_fornecedor'] > 0) ? $campos[$i]['id_fornecedor'] : 'NULL';
    $id_representante   = ($campos[$i]['id_representante'] > 0) ? $campos[$i]['id_representante'] : 'NULL';
    $id_funcionario     = ($campos[$i]['id_funcionario'] > 0) ? $campos[$i]['id_funcionario'] : 'NULL';
    
    $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_fornecedor`, `id_representante`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, $id_fornecedor, $id_representante, $id_funcionario, '".$campos[$i]['id_conta_apagar']."', '18', '".addslashes($campos[$i]['observacao'])."', '".date('Y-m-d H:i:s')."') ";
    
    //$sql = "UPDATE `follow_ups` SET `data_sys` = '".$campos[$i]['data_sys']."' WHERE `identificacao` = '".$campos[$i]['id_conta_receber']."' AND `origem` = '4' AND `observacao` = '".addslashes($campos[$i]['observacao'])."' ";
    echo '<br/><br/>'.$sql;
    bancos::sql($sql);
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_follow_ups14.php?indice=<?=++$indice;?>'
</Script>