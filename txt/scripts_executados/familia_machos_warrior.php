<?
require('../../lib/segurancas.php');

$sql = "SELECT id_produto_acabado, discriminacao 
        FROM `produtos_acabados` 
        WHERE `id_gpa_vs_emp_div` IN (22, 43, 83, 98) 
        AND ativo = '1'
        ORDER BY discriminacao ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $pos_aux	= strpos($campos[$i]['discriminacao'], 'MAQUINA');
    if($pos_aux > 0) {
        $discriminacao = str_replace('MAQUINA', 'MAQUINA WARRIOR', $campos[$i]['discriminacao']);
        echo $sql = "UPDATE `produtos_acabados` SET `discriminacao` = '$discriminacao' WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ;<br>";
        //bancos::sql($sql);
    }
}
?>