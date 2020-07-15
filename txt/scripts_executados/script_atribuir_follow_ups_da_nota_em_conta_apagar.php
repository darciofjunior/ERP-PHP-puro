<?
require('../../lib/segurancas.php');
require('../../lib/intermodular.php');
require('../../lib/calculos.php');
session_start('funcionarios');

if(empty($indice)) $indice = 0;

//Todos os Pedidos do Sistema...
$sql = "SELECT COUNT(DISTINCT(`id_conta_apagar`)) AS total_registro 
        FROM `contas_apagares` 
        WHERE `id_nfe` > '0' 
        AND `status` < '2' ";
$campos_total = bancos::sql($sql);
echo $indice.'/'.$total_registro = $campos_total[0]['total_registro'];
echo '<br/>';

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

//Todos os Pedidos do Sistema...
$sql = "SELECT `id_conta_apagar`, `id_fornecedor`, `id_nfe` 
        FROM `contas_apagares` 
        WHERE `id_nfe` > '0' 
        AND `status` < '2' ";
$campos = bancos::sql($sql, $indice, 1);
//Verifico se a NF de Entrada possui algum Follow_UP ...
$sql = "SELECT * 
        FROM `follow_ups` 
        WHERE `identificacao` = '".$campos[0]['id_nfe']."' 
        AND `origem` = '17' 
        AND `exibir_no_pdf` = 'S' ";
$campos_follow_up = bancos::sql($sql);
if(count($campos_follow_up) == 1) {
    //Como foi encontrado um Follow-UP de NF de Entrada, verifico se existem algum Follow-UP na Parte de Contas à Pagar ...
    $sql = "SELECT * 
            FROM `follow_ups` 
            WHERE `identificacao` = '".$campos[0]['id_conta_apagar']."' 
            AND `origem` = '18' LIMIT 1 ";//Nesse caso eu busco o Follow-UP mais antigo ...
    $campos_follow_up_contas_apagar = bancos::sql($sql);
    if(count($campos_follow_up_contas_apagar) == 1) {//Sim existe, então basta fazer uma atualização e mais nada ...
        $sql = "UPDATE `follow_ups` SET `observacao` = CONCAT(`observacao`, '. ".$campos_follow_up[0]['observacao']."') WHERE `id_follow_up` = '".$campos_follow_up_contas_apagar[0]['id_follow_up']."' LIMIT 1 ";
        bancos::sql($sql);
    }else {//Não existe, então preciso fazer uma Inserção de Registro ...
        $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_fornecedor`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '".$campos[0]['id_fornecedor']."', '".$campos_follow_up[0]['id_funcionario']."', '".$campos[0]['id_conta_apagar']."', '18', '".strtolower($campos_follow_up[0]['observacao'])."', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
    }
    echo '<br/>'.$sql;
}
?>
<Script Language = "JavaScript">
    window.location = 'script_atribuir_follow_ups_da_nota_em_conta_apagar.php?indice=<?=++$indice;?>'
</Script>