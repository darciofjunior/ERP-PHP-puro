<?
require('../../lib/segurancas.php');
if(empty($indice)) $indice = 0;

//Todas as NFS ...
$sql = "SELECT COUNT(distinct(id_produto_acabado)) AS total_registro 
        FROM `produtos_acabados` 
        WHERE `ativo` = '1' ";
$campos_total   = bancos::sql($sql);
$total_registro = $campos_total[0]['total_registro'];

if($total_registro == $indice) {//P/ não ficar em loop infinito ...
    exit;
}

$sql = "SELECT DISTINCT(id_produto_acabado) 
        FROM `produtos_acabados` 
        WHERE `ativo` = '1' ";
$campos = bancos::sql($sql, $indice, 1);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $sql = "SELECT SUM(qtde_s) AS qtde_saida 
            FROM `oes` 
            WHERE `id_produto_acabado_e` = '".$campos[0]['id_produto_acabado']."' 
            AND `qtde_e` = '0' 
            AND `status_finalizar` = '0' ";
    $campos_oe_saida = bancos::sql($sql);
    echo $sql = "UPDATE `estoques_acabados` SET `qtde_oe_em_aberto` = '".$campos_oe_saida[0]['qtde_saida']."' WHERE `id_produto_acabado` = '".$campos[0]['id_produto_acabado']."' LIMIT 1 ";
    echo '<br>';
    bancos::sql($sql);
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_oes_em_aberto.php?indice=<?=++$indice;?>'
</Script>