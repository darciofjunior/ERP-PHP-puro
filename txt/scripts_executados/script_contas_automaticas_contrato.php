<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

//Busco todas as Contas Autom�ticas que s�o Contratos ...
$sql = "SELECT COUNT(DISTINCT(caa.id_conta_apagar_automatica)) AS total_registro 
        FROM `contas_apagares_automaticas` caa 
        INNER JOIN `contas_apagares_automaticas_vs_pffs` caap ON caap.`id_conta_apagar_automatica` = caa.`id_conta_apagar_automatica` ";
$campos_total = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'].'<br>';

if($total_registro == $indice) exit('SCRIPT EXECUTADO COM SUCESSO !');

$sql = "SELECT caa.id_conta_apagar_automatica, caa.id_empresa, caa.valor, caap.num_nota 
        FROM `contas_apagares_automaticas` caa 
        INNER JOIN `contas_apagares_automaticas_vs_pffs` caap ON caap.`id_conta_apagar_automatica` = caa.`id_conta_apagar_automatica` ";
$campos = bancos::sql($sql, $indice, 1);

/*Rastreio todas as Contas � Pagar que foram geradas atrav�s das caracter�sticas encontradas acima na
Conta Autom�tica de Contrato ...*/
$sql = "UPDATE `contas_apagares` SET `id_conta_apagar_automatica` = '".$campos[0]['id_conta_apagar_automatica']."' WHERE `id_empresa` = '".$campos[0]['id_empresa']."' AND `numero_conta` LIKE '".$campos[0]['num_nota']."%' AND `valor` = '".$campos[0]['valor']."' AND `conta_automatica` = 'S' ";
bancos::sql($sql);
?>
<Script Language = 'JavaScript'>
//Aqui eu j� passo o �ndice do pr�ximo ...
    window.location = 'script_contas_automaticas_contrato.php?indice=<?=++$indice;?>'
</Script>