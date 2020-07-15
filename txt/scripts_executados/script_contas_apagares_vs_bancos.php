<?
require('../../lib/segurancas.php');
require('../../lib/data.php');

if(empty($indice)) $indice = 0;

//36774242

//76277545

//Todas as NFS ...
$sql = "SELECT COUNT(cacc.id_conta_apagar_conta_corrente) AS total_registro 
        FROM `contas_apagares_vs_contas_correntes` cacc 
        INNER JOIN `contas_correntes` cc ON cc.id_contacorrente = cacc.id_contacorrente 
        INNER JOIN `agencias` a ON a.id_agencia = cc.id_agencia ";
$campos_total = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'];
echo '<br>';

if($total_registro == $indice) {//P/ não ficar em loop infinito ...
    exit;
}

$sql = "SELECT cacc.id_conta_apagar_quitacao, a.id_banco 
        FROM `contas_apagares_vs_contas_correntes` cacc 
        INNER JOIN `contas_correntes` cc ON cc.id_contacorrente = cacc.id_contacorrente 
        INNER JOIN `agencias` a ON a.id_agencia = cc.id_agencia ";
$campos = bancos::sql($sql, $indice, 1);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    echo $sql = "UPDATE `contas_apagares_quitacoes` SET `id_banco` = '".$campos[0]['id_banco']."' WHERE `id_conta_apagar_quitacao` = '".$campos[0]['id_conta_apagar_quitacao']."' LIMIT 1 ";
    echo '<br>';
    bancos::sql($sql);
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_contas_apagares_vs_bancos.php?indice=<?=++$indice;?>'
</Script>