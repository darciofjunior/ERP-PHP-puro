<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

$sql = "SELECT DISTINCT(`id_nfe`) AS id_nfe 
        FROM `nfe` 
        WHERE `observacao` <> '' ";
$campos_total   = bancos::sql($sql);
echo $total_registro = count($campos_total);

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

$sql = "SELECT * 
        FROM `nfe` 
        WHERE `observacao` <> '' ";
$campos = bancos::sql($sql, $indice, 1);
$linhas = count($campos);

for($i = 0; $i < $linhas; $i++) {
    $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_fornecedor`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '".$campos[$i]['id_fornecedor']."', '14', '".$campos[$i]['id_nfe']."', '17', '".addslashes($campos[$i]['observacao'])."', '".$campos[$i]['data_emissao']."'); ";
    echo '<br/><br/>'.$sql;
    bancos::sql($sql);
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_follow_ups.php?indice=<?=++$indice;?>'
</Script>