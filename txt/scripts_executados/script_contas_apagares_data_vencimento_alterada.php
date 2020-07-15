<?
require('../../lib/segurancas.php');
require('../../lib/data.php');

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

$sql = "SELECT COUNT(DISTINCT(`id_conta_apagar`)) AS id_conta_apagar 
        FROM `contas_apagares` ca 
        INNER JOIN `nfe` ON nfe.`id_nfe` = ca.`id_nfe` 
        INNER JOIN `nfe_financiamentos` nfef ON nfef.`id_nfe` = nfe.`id_nfe` 
        WHERE ca.`id_nfe` > '0' 
        AND ca.`data_emissao` >= '2014-04-01' 
        GROUP BY ca.`id_nfe` ";
$campos_total   = bancos::sql($sql);
echo $total_registro = count($campos_total).'<br>';

if($total_registro == $indice) exit('SCRIPT EXECUTADO COM SUCESSO !');

$sql = "SELECT ca.`id_conta_apagar`, ca.`id_nfe` 
        FROM `contas_apagares` ca 
        INNER JOIN `nfe` ON nfe.`id_nfe` = ca.`id_nfe` 
        INNER JOIN `nfe_financiamentos` nfef ON nfef.`id_nfe` = nfe.`id_nfe` 
        WHERE ca.`id_nfe` > '0' 
        AND ca.`data_emissao` >= '2014-04-01' 
        GROUP BY ca.`id_nfe` ";
$campos = bancos::sql($sql, $indice, 1);

//Desse 'id_nfe' que foi encontrado acima da Conta à Pagar, eu busco todos os seus vencimentos ...
$sql = "SELECT `data`, `valor_parcela_nf` 
        FROM `nfe_financiamentos` 
        WHERE `id_nfe` = '".$campos[0]['id_nfe']."' ORDER BY data ";
$campos_nfe_financiamento = bancos::sql($sql);
$linhas_nfe_financiamento = count($campos_nfe_financiamento);

if($linhas_nfe_financiamento > 0) {
    for($i = 0; $i < $linhas_nfe_financiamento; $i++) {
        $vetor_data_vencimento[]    = $campos_nfe_financiamento[$i]['data'];
        $vetor_valor_inicial[]      = $campos_nfe_financiamento[$i]['valor_parcela_nf'];
    }
}

//Desse 'id_nfe' que foi encontrado acima da Conta à pagar, eu busco todas as Contas à Pagar vinculadas a esta ...
$sql = "SELECT `id_conta_apagar`, `data_vencimento`, `data_vencimento_alterada`, `valor_reajustado` 
        FROM `contas_apagares` 
        WHERE `id_nfe` = '".$campos[0]['id_nfe']."' ";
$campos_contas_apagar = bancos::sql($sql);
$linhas_contas_apagar = count($campos_contas_apagar);
for($i = 0; $i < $linhas_contas_apagar; $i++) {
    if($campos_contas_apagar[$i]['valor_reajustado'] != $vetor_valor_inicial[$i] && $campos_contas_apagar[$i]['data_vencimento'] != $vetor_data_vencimento[$i]) {
        //A variável dias equivale a data atual até a data de vecimento ...
        $dias   = data::diferenca_data($vetor_data_vencimento[$i], $campos_contas_apagar[$i]['data_vencimento']);
        if($dias[0] < 0) $dias[0] = 0;
        
        $valor_juros    = $campos_contas_apagar[$i]['valor_reajustado'] - $vetor_valor_inicial[$i];
        //$taxa_juros     = round($valor_juros * 100 / ($vetor_valor_inicial[$i] * $dias[0] / 30), 2);
        $taxa_juros     = (pow(pow($campos_contas_apagar[$i]['valor_reajustado'] / $vetor_valor_inicial[$i], 1 / $dias[0]), 30) - 1) * 100;
        
        $sql = "UPDATE `contas_apagares` SET `data_vencimento` = '$vetor_data_vencimento[$i]', `valor` = '$vetor_valor_inicial[$i]', `taxa_juros` = '$taxa_juros', `valor_juros` = '$valor_juros' WHERE `id_conta_apagar` = '".$campos_contas_apagar[$i]['id_conta_apagar']."' LIMIT 1 ";
        bancos::sql($sql);
    }
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_contas_apagares_data_vencimento_alterada.php?indice=<?=++$indice;?>'
</Script>