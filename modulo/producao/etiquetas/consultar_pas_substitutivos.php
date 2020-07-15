<?
require('../../../lib/segurancas.php');
require('../../../lib/ajax.php');
session_start('funcionarios');

//Aqui eu busco qual é o PA da OP ...
$sql = "SELECT `id_produto_acabado` 
        FROM `ops` 
        WHERE `id_op` = '$_POST[id_op]' LIMIT 1 ";
$campos_pa = bancos::sql($sql);

//Aqui eu verifico se existem PAS de Substituição atrelados ao PA Principal da OP ...
$sql = "SELECT 
        DISTINCT(IF(ps.id_produto_acabado_1 = '".$campos_pa[0]['id_produto_acabado']."', ps.id_produto_acabado_2, ps.id_produto_acabado_1)) AS id_pa 
        FROM `pas_substituires` ps 
        WHERE 
        (ps.`id_produto_acabado_1` = '".$campos_pa[0]['id_produto_acabado']."') 
        OR (ps.id_produto_acabado_2 = '".$campos_pa[0]['id_produto_acabado']."') ";
$campos_pas_substituicao = bancos::sql($sql);
$linhas_pas_substituicao = count($campos_pas_substituicao);
if($linhas_pas_substituicao > 0) {
    for($i = 0; $i < $linhas_pas_substituicao; $i++) $id_pas_exibir.= $campos_pas_substituicao[$i]['id_pa'].', ';
    $id_pas_exibir = substr($id_pas_exibir, 0, strlen($id_pas_exibir) - 2);

    $sql = "SELECT id_produto_acabado, CONCAT(referencia, ' - ', discriminacao) AS dados 
            FROM `produtos_acabados` 
            WHERE `id_produto_acabado` IN ($id_pas_exibir) ORDER BY referencia ";
    $campos         = bancos::sql($sql);
    $auto_complete  = ajax::combo($campos, 'id_produto_acabado', 'dados');
}
?>