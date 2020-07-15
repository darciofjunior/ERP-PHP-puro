<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');

echo "<font color='red' size='5'>COMPARAÇÃO COM A BIBLIOTECA DE CUSTO !<br></font>";

$sql = "SELECT id_produto_acabado, operacao_custo as operacao_custo_prac 
        FROM `produtos_acabados_custos` 
        WHERE `id_produto_acabado_custo` = '$_GET[id_produto_acabado_custo]' LIMIT 1 ";
$campos = bancos::sql($sql);

$taxa_financeira_vendas = genericas::variaveis('taxa_financeira_vendas') / 100 + 1;
$total_indust           = custos::todas_etapas($campos[0]['id_produto_acabado'], $campos[0]['operacao_custo_prac']);

echo '<br>Etapa1 = R$ '.number_format($GLOBALS['etapa1'], 2, ',', '.');
echo '<br>Etapa2 = R$ '.number_format($GLOBALS['etapa2'], 2, ',', '.');
echo '<br>Etapa3 = R$ '.number_format($GLOBALS['etapa3'], 2, ',', '.');
echo '<br>Etapa4 = R$ '.number_format($GLOBALS['etapa4'], 2, ',', '.');
echo '<br>Etapa5 = R$ '.number_format($GLOBALS['etapa5'], 2, ',', '.');
echo '<br>Etapa6 = R$ '.number_format($GLOBALS['etapa6'], 2, ',', '.');
echo '<br>Etapa7 = R$ '.number_format($GLOBALS['etapa7'], 2, ',', '.');
echo '<br>Total s/ a taxa = R$ '.number_format($GLOBALS['tot_sem_tx_financ'], 2, ',', '.');
echo '<br>Total c/ a taxa = R$ '.number_format($total_indust * $taxa_financeira_vendas, 2, ',', '.');
?>