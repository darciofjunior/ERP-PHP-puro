<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

//Verifica quantos Custos Industriais existem no Sistema e que o Peso do KG seja maior do que Zero na 2ª Etapa ...
$sql = "SELECT COUNT(id_produto_acabado_custo) AS total_registro 
        FROM `produtos_acabados_custos` 
        WHERE `operacao_custo` = '0' 
        AND `peso_kg` > '0' ";
$campos_total = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'];
echo '<br>';

if($total_registro == $indice) {exit;}

//Trago um Custo Industrial em que o Peso do KG seja maior do que Zero na 2ª Etapa de acordo com o Índice do Loop ...
$sql = "SELECT id_produto_acabado_custo, peso_kg 
        FROM `produtos_acabados_custos` 
        WHERE `operacao_custo` = '0' 
        AND `peso_kg` > '0' ";
$campos = bancos::sql($sql, $indice, 1);

//Atualiza o Peso Aço da Etapa 5 ...
$peso_aco_kg = $campos[0]['peso_kg'] / 1.05;//Essa variável tem q abater 5% a menos nessa etapa ...

$sql = "UPDATE `pacs_vs_pis_trat` SET `peso_aco` = '$peso_aco_kg' WHERE `id_produto_acabado_custo` = '".$campos[0]['id_produto_acabado_custo']."' ";
bancos::sql($sql);
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_corrigir_custo_etapa5.php?indice=<?=++$indice;?>'
</Script>