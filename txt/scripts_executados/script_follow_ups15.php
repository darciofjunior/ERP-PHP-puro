<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

$sql = "SELECT DISTINCT(`id_conta_apagar`) AS id_conta_apagar 
        FROM `contas_apagares_quitacoes` 
        WHERE `observacao` <> '' ";
$campos_total   = bancos::sql($sql);
echo $total_registro = count($campos_total);

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

$sql = "SELECT * 
        FROM `contas_apagares_quitacoes` 
        WHERE `observacao` <> '' ";
$campos = bancos::sql($sql, $indice, 1);
$linhas = count($campos);

for($i = 0; $i < $linhas; $i++) {
    //Significa que esta conta foi importada do Faturamento para o Financeiro ...
    $sql = "SELECT `id_funcionario`, `id_fornecedor` 
            FROM `contas_apagares` 
            WHERE `id_conta_apagar` = '".$campos[$i]['id_conta_apagar']."' LIMIT 1 ";
    $campos_conta   = bancos::sql($sql);
    
    $id_fornecedor  = ($campos_conta[0]['id_fornecedor'] > 0) ? $campos_conta[0]['id_fornecedor'] : 'NULL';
    
    $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_fornecedor`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, $id_fornecedor, '".$campos_conta[0]['id_funcionario']."', '".$campos[$i]['id_conta_apagar']."', '18', '".addslashes($campos[$i]['observacao'])."', '".$campos[$i]['data_sys']."') ";
    //$sql = "UPDATE `follow_ups` SET `data_sys` = '".$campos[$i]['data_sys']."' WHERE `identificacao` = '".$campos[$i]['id_conta_receber']."' AND `origem` = '4' AND `observacao` = '".addslashes($campos[$i]['observacao'])."' ";
    echo '<br/><br/>'.$sql;
    bancos::sql($sql);
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_follow_ups15.php?indice=<?=++$indice;?>'
</Script>