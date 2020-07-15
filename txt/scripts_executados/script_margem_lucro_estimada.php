<?
require('../../lib/segurancas.php');
require('../../lib/custos.php');
require('../../lib/data.php');
require('../../lib/estoque_acabado.php');
require('../../lib/intermodular.php');

if(empty($indice)) $indice = 0;

//Busca todos os PAs em que a OC = Revenda ...
$sql = "SELECT COUNT(id_produto_insumo) AS total_registro 
        FROM `produtos_acabados` 
        WHERE `operacao_custo` = '1' 
        AND `ativo` = '1' 
        AND `id_produto_insumo` > '0' 
        AND `referencia` <> 'ESP' 
        ORDER BY referencia ";
$campos_total   = bancos::sql($sql);
echo 'Total de Registro => '.$total_registro = $campos_total[0]['total_registro'];

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit('FIM !');

//Busca todos os PAs em que a OC = Revenda ...
$sql = "SELECT id_produto_insumo 
        FROM `produtos_acabados` 
        WHERE `operacao_custo` = '1' 
        AND `ativo` = '1' 
        AND `id_produto_insumo` > '0' 
        AND `referencia` <> 'ESP' 
        ORDER BY referencia ";
$campos = bancos::sql($sql, $indice, 1);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    intermodular::gravar_campos_para_calcular_margem_lucro_estimada($campos[$i]['id_produto_insumo']);
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_margem_lucro_estimada.php?indice=<?=++$indice;?>'
</Script>