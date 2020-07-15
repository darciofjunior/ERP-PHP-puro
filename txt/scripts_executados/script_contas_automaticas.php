<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

//Busco todas as Contas Automáticas que não são Contratos ...
$sql = "SELECT COUNT(DISTINCT(ca.id_conta_apagar)) AS total_registro 
        FROM `contas_apagares_automaticas` caa 
        INNER JOIN `contas_apagares_automaticas_vs_pffs` caap ON caap.`id_conta_apagar_automatica` = caa.`id_conta_apagar_automatica` AND caap.`num_nota` <> '' 
        INNER JOIN `produtos_financeiros_vs_fornecedor` pff ON pff.id_produto_financeiro_vs_fornecedor = caap.id_produto_financeiro_vs_fornecedor 
        INNER JOIN `contas_apagares` ca ON ca.`numero_conta` = caap.`num_nota` AND ca.`conta_automatica` = 'S' AND ca.`id_empresa` = caa.`id_empresa` 
        WHERE caa.`qtde_parcelas` = '0' ORDER BY ca.id_conta_apagar ";
$campos_total = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'].'<br>';

if($total_registro == $indice) exit('SCRIPT EXECUTADO COM SUCESSO !');

$sql = "SELECT DISTINCT(ca.id_conta_apagar), ca.numero_conta, ca.valor, caa.id_conta_apagar_automatica 
        FROM `contas_apagares_automaticas` caa 
        INNER JOIN `contas_apagares_automaticas_vs_pffs` caap ON caap.`id_conta_apagar_automatica` = caa.`id_conta_apagar_automatica` AND caap.`num_nota` <> '' 
        INNER JOIN `produtos_financeiros_vs_fornecedor` pff ON pff.id_produto_financeiro_vs_fornecedor = caap.id_produto_financeiro_vs_fornecedor 
        INNER JOIN `contas_apagares` ca ON ca.`numero_conta` = caap.`num_nota` AND ca.`conta_automatica` = 'S' AND ca.`id_empresa` = caa.`id_empresa` 
        WHERE caa.`qtde_parcelas` = '0' GROUP BY ca.id_conta_apagar ORDER BY ca.id_conta_apagar ";
$campos = bancos::sql($sql, $indice, 1);

/*Atualizo todas as Contas à Pagar com o "$id_conta_apagar_automatica" que foram geradas através 
desse "$id_conta_apagar_automatica" do Loop ...*/
$sql = "UPDATE `contas_apagares` SET `id_conta_apagar_automatica` = '".$campos[0]['id_conta_apagar_automatica']."' WHERE `id_conta_apagar` = '".$campos[0]['id_conta_apagar']."' LIMIT 1 ";
bancos::sql($sql);
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_contas_automaticas.php?indice=<?=++$indice;?>'
</Script>