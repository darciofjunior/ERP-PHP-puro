<?
require('../../lib/segurancas.php');

$vetor_oes = array();

$sql = "SELECT gpa.`id_familia`, bmp.`id_produto_acabado`, bmp.`qtde`, pa.`pecas_por_jogo`, 
        oes.`id_oe`, oes.`id_produto_acabado_e` 
        FROM `baixas_manipulacoes_pas` bmp 
        INNER JOIN `oes` ON oes.`id_oe` = bmp.`id_oe` 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = bmp.`id_produto_acabado` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
        WHERE SUBSTRING(bmp.`data_sys`, 1, 10) >= '2016-01-01' ORDER BY oes.`id_oe` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    //Busco o pecas_por_jogo do PA que esta retornando da OE ...
    $sql = "SELECT `pecas_por_jogo` 
            FROM `produtos_acabados` 
            WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado_e']."' LIMIT 1 ";
    $campos_pa = bancos::sql($sql);
    if($campos[$i]['id_familia'] == 9) {//Nesse caso específico, o procedimento será um pouquinho diferenciado ...
        //Se o PA à Retornar for diferente do PA Substituvo ...
        if($campos[$i]['id_produto_acabado'] != $campos[$i]['id_produto_acabado_e']) {
            $qtde_e = ($campos[$i]['qtde'] * $campos[$i]['pecas_por_jogo'] / $campos_pa[0]['pecas_por_jogo']);
        }else {
            $qtde_e = $campos[$i]['qtde'];
        }
    }else {
        $qtde_e = $campos[$i]['qtde'];
    }
    //Nova OE ...
    if(!in_array($campos[$i]['id_oe'], $vetor_oes)) $vetor_oes[] = $campos[$i]['id_oe'];
    $vetor_qtde_e[$campos[$i]['id_oe']]+= $qtde_e;
}

for($i = 0; $i < count($vetor_oes); $i++) {
    $sql = "UPDATE `oes` SET `qtde_e` = '".$vetor_qtde_e[$vetor_oes[$i]]."' WHERE `id_oe` = '".$vetor_oes[$i]."' LIMIT 1;";
    echo $sql.'<br/>';
}
?>