<?
require('../../lib/segurancas.php');
require('../../lib/data.php');
require('../../lib/financeiros.php');

if(empty($indice)) $indice = 0;

//Verifico o Total de Contas à Pagar que estão em aberto ...
$sql = "SELECT COUNT(`id_conta_apagar`) AS total_registro 
        FROM `contas_apagares` 
        WHERE `status` < '2' ";
$campos_total = bancos::sql($sql);
$total_registro = $campos_total[0]['total_registro'];

echo $total_registro.'/'.$indice;

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

$sql = "SELECT `id_conta_apagar` 
        FROM `contas_apagares` 
        WHERE `status` < '2' ";
$campos = bancos::sql($sql, $indice, 1);

financeiros::atualizar_data_alterada($campos[0]['id_conta_apagar'], 'A');

?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_data_alterada_a_pagar.php?indice=<?=++$indice;?>'
</Script>