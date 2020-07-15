<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos_new.php');

echo  "<font color='red' size=6>Comparação com a biblioteca de custo<br></font>";
$sql="SELECT id_produto_acabado, operacao_custo as operacao_custo_prac ";
$sql.="from produtos_acabados_custos ";
$sql.="where id_produto_acabado_custo = '$id_produto_acabado_custo' limit 1";
$campos = bancos::sql($sql);
$id_produto_acabado = $campos[0]['id_produto_acabado'];
$operacao_custo_prac = $campos[0]['operacao_custo_prac'];
/*

$custos->custo_auto_pi_industrializado();
todas_etapas($id_produto_acabado);
echo "vixi".$custos->etapa1;
*/
$taxa_financeira_vendas=genericas::variaveis('taxa_financeira_vendas')/100+1;
//custos::custo_auto_pi_industrializado();
$total_indust=custos::todas_etapas($id_produto_acabado, $operacao_custo_prac);
echo "Etapa1="."R$ ".number_format($GLOBALS['etapa1'], 2, ",", ".");
echo "<br>Etapa2="."R$ ".number_format($GLOBALS['etapa2'],2,",",".");
echo "<br>Etapa3="."R$ ".number_format($GLOBALS['etapa3'],2,",",".");
echo "<br>Etapa4="."R$ ".number_format($GLOBALS['etapa4'],2,",",".");
echo "<br>Etapa5="."R$ ".number_format($GLOBALS['etapa5'],2,",",".");
echo "<br>Etapa6="."R$ ".number_format($GLOBALS['etapa6'],2,",",".");
echo "<br>Etapa7="."R$ ".number_format($GLOBALS['etapa7'],2,",",".");
echo "<br>Total sem a taxa="."R$ ".number_format($GLOBALS['tot_sem_tx_financ'],2,",",".");;
echo "<br>Total com a taxa="."R$ ".number_format($total_indust*$taxa_financeira_vendas,2,",",".");
/*
echo "<br>";
$total_indust=todas_etapas(45);
echo "<br>Etapa4="."R$ ".number_format($GLOBALS['etapa4_especial'],2,",",".");
*/
?>