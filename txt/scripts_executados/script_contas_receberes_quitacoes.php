<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

$sql = "SELECT COUNT(DISTINCT(id_conta_receber_quitacao)) AS total_registro 
        FROM `contas_receberes_vs_cheques` 
        WHERE `id_conta_receber_quitacao` > '0' ";
$campos_total = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'].'<br>';

if($total_registro == $indice) exit('SCRIPT EXECUTADO COM SUCESSO !');

$sql = "SELECT id_conta_receber_quitacao, id_cheque 
        FROM `contas_receberes_vs_cheques` 
        WHERE `id_conta_receber_quitacao` > '0' ";
$campos = bancos::sql($sql, $indice, 1);

$sql = "UPDATE `contas_receberes_quitacoes` SET `id_cheque` = '".$campos[0]['id_cheque']."' WHERE `id_conta_receber_quitacao` = '".$campos[0]['id_conta_receber_quitacao']."' LIMIT 1 ";
bancos::sql($sql);
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_contas_receberes_quitacoes.php?indice=<?=++$indice;?>'
</Script>