<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

$sql = "SELECT COUNT(DISTINCT(id_conta_apagar_quitacao)) AS total_registro 
        FROM `contas_apagares_vs_contas_correntes` 
        WHERE `id_conta_apagar_quitacao` > '0' ";
$campos_total = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'].'<br>';

if($total_registro == $indice) exit('SCRIPT EXECUTADO COM SUCESSO !');

$sql = "SELECT id_conta_apagar_quitacao, id_contacorrente 
        FROM `contas_apagares_vs_contas_correntes` 
        WHERE `id_conta_apagar_quitacao` > '0' ";
$campos = bancos::sql($sql, $indice, 1);

$sql = "UPDATE `contas_apagares_quitacoes` SET `id_contacorrente` = '".$campos[0]['id_contacorrente']."' WHERE `id_conta_apagar_quitacao` = '".$campos[0]['id_conta_apagar_quitacao']."' LIMIT 1 ";
bancos::sql($sql);
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_contas_apagares_quitacoes.php?indice=<?=++$indice;?>'
</Script>