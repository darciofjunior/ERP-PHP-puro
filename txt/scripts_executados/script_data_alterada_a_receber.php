<?
require('../../lib/segurancas.php');
require('../../lib/data.php');
require('../../lib/financeiros.php');

if(empty($indice)) $indice = 0;

//Verifico o Total de Contas � Receber que est�o em aberto ...
$sql = "SELECT COUNT(`id_conta_receber`) AS total_registro 
        FROM `contas_receberes` 
        WHERE `status` < '2' ";
$campos_total = bancos::sql($sql);
$total_registro = $campos_total[0]['total_registro'];

echo $total_registro.'/'.$indice;

//P/ n�o ficar em loop infinito ...
if($total_registro == $indice) exit;

$sql = "SELECT `id_conta_receber` 
        FROM `contas_receberes` 
        WHERE `status` < '2' ";
$campos = bancos::sql($sql, $indice, 1);

financeiros::atualizar_data_alterada($campos[0]['id_conta_receber'], 'R');
?>
<Script Language = 'JavaScript'>
//Aqui eu j� passo o �ndice do pr�ximo ...
    window.location = 'script_data_alterada_a_receber.php?indice=<?=++$indice;?>'
</Script>