<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

$sql = "SELECT COUNT(id_conta_apagar_automatica_vs_pff) AS total_registro 
        FROM `contas_apagares_automaticas_vs_pffs` ";
$campos_total = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'];

if($total_registro == $indice) exit('SCRIPT EXECUTADO COM SUCESSO !');

$sql = "SELECT id_conta_apagar_automatica, id_produto_financeiro_vs_fornecedor, num_nota, 
        banco, agencia, num_cc, correntista, cnpj_cpf 
        FROM `contas_apagares_automaticas_vs_pffs` ";
$campos = bancos::sql($sql, $indice, 1);

echo '<br>'.$sql = "UPDATE `contas_apagares_automaticas` SET `id_produto_financeiro_vs_fornecedor` = '".$campos[0]['id_produto_financeiro_vs_fornecedor']."', 
        `numero_conta` = '".$campos[0]['num_nota']."', `agencia` = '".$campos[0]['agencia']."',  
        `num_cc` = '".$campos[0]['num_cc']."', `correntista` = '".$campos[0]['correntista']."',  
        `cnpj_cpf` = '".$campos[0]['cnpj_cpf']."' 
        WHERE `id_conta_apagar_automatica` = '".$campos[0]['id_conta_apagar_automatica']."' LIMIT 1 ";
bancos::sql($sql);
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_contas_automaticas_vs_pffs.php?indice=<?=++$indice;?>'
</Script>