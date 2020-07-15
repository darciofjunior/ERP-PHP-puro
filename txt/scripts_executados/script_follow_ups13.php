<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

$sql = "SELECT DISTINCT(`id_conta_receber`) AS id_conta_receber 
        FROM `contas_receberes_quitacoes` 
        WHERE `observacao` <> '' ";
$campos_total   = bancos::sql($sql);
echo $total_registro = count($campos_total);

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

$sql = "SELECT * 
        FROM `contas_receberes_quitacoes` 
        WHERE `observacao` <> '' ";
$campos = bancos::sql($sql, $indice, 1);
$linhas = count($campos);

for($i = 0; $i < $linhas; $i++) {
    //Significa que esta conta foi importada do Faturamento para o Financeiro ...
    $sql = "SELECT cr.`id_funcionario`, cr.`id_cliente`, cr.`id_representante`, nfs.`id_cliente_contato` 
            FROM `contas_receberes` cr 
            LEFT JOIN `nfs` ON nfs.`id_nf` = cr.`id_nf` 
            WHERE cr.`id_conta_receber` = '".$campos[$i]['id_conta_receber']."' LIMIT 1 ";
    $campos_conta           = bancos::sql($sql);
    $id_cliente_contato     = ($campos_conta[0]['id_cliente_contato'] > 0)  ? $campos_conta[0]['id_cliente_contato'] : 'NULL';
    $id_representante       = ($campos_conta[0]['id_representante'] > 0)    ? $campos_conta[0]['id_representante'] : 'NULL';
    
    //$sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_cliente_contato`, `id_representante`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '".$campos_conta[0]['id_cliente']."', $id_cliente_contato, $id_representante, $id_funcionario, '".$campos[$i]['id_conta_receber']."', '4', '".addslashes($campos[$i]['observacao'])."', '".date('Y-m-d H:i:s')."') ";
    
    $sql = "UPDATE `follow_ups` SET `data_sys` = '".$campos[$i]['data_sys']."' WHERE `identificacao` = '".$campos[$i]['id_conta_receber']."' AND `origem` = '4' AND `observacao` = '".addslashes($campos[$i]['observacao'])."' ";
    echo '<br/><br/>'.$sql;
    bancos::sql($sql);
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_follow_ups13.php?indice=<?=++$indice;?>'
</Script>