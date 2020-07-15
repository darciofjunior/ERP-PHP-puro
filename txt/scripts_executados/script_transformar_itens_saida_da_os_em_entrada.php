<?
require('../../lib/segurancas.php');
session_start('funcionarios');

if(empty($indice)) $indice = 0;

//Busco todos os itens de OS ...
$sql = "SELECT COUNT(`id_os_item`) AS total_registro 
        FROM `oss_itens` 
        WHERE `qtde_saida` > '0' 
        AND `qtde_entrada` > '0' ";
$campos_total   = bancos::sql($sql);
$total_registro = $campos_total[0]['total_registro'];

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

$sql = "SELECT *  
        FROM `oss_itens` 
        WHERE `qtde_saida` > '0' 
        AND `qtde_entrada` > '0' ";
$campos = bancos::sql($sql, 0, 1);

echo $total_registro.' / '.$indice.' <br/><br/>OS '.$campos[0]['id_os'].' / Item OS '.$campos[0]['id_os_item'];

$id_nfe                 = (empty($campos[0]['id_nfe'])) ? 'NULL' : $campos[0]['id_nfe'];
$id_funcionario_entrada = (empty($campos[0]['id_funcionario_entrada'])) ? 'NULL' : $campos[0]['id_funcionario_entrada'];

/*Gero um Registro de Entrada para o id_os_item "Saída que foi gerado anteriormente" ...*/
$sql = "INSERT INTO `oss_itens` (`id_os_item`, `id_os`, `id_op`, `id_os_item_saida`, `id_produto_insumo_ctt`, 
        `id_nfe`, `id_funcionario_entrada`, `qtde_entrada`, `dureza_fornecedor`, `peso_total_entrada`, `data_entrada`) 
        VALUES (NULL, '".$campos[0]['id_os']."', '".$campos[0]['id_op']."', '".$campos[0]['id_os_item']."', 
        '".$campos[0]['id_produto_insumo_ctt']."', $id_nfe, $id_funcionario_entrada, '".$campos[0]['qtde_entrada']."', 
        '".$campos[0]['dureza_fornecedor']."', '".$campos[0]['peso_total_entrada']."', '".$campos[0]['data_entrada']."') ";
bancos::sql($sql);
echo '<br/><br/>'.$sql;

//Zero do Registro id_os_item "Saída" os campos de Entrada que estavam gravados nesse ...
$sql = "UPDATE `oss_itens` SET `id_nfe` = NULL, `id_funcionario_entrada` = NULL, `qtde_entrada` = '0', `dureza_fornecedor` = '', 
        `peso_total_entrada` = '', `data_entrada` = '0000-00-00' WHERE `id_os_item` = '".$campos[0]['id_os_item']."' LIMIT 1 ";
bancos::sql($sql);
echo '<br/><br/>'.$sql;
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_transformar_itens_saida_da_os_em_entrada.php?indice=<?=++$indice;?>'
</Script>