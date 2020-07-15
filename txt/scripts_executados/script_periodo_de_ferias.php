<?
require('../../lib/segurancas.php');
require('../../lib/data.php');

$sql = "SELECT id_funcionario, DATE_FORMAT(periodo_anual_data_final, '%d/%m/%Y')AS periodo_anual_data_final, data_prox_ferias 
        FROM `funcionarios` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
/*for($i = 0; $i < $linhas; $i++) {
    $ano_prox_ferias    = substr($campos[$i]['data_prox_ferias'], 0, 4);
    $mes_prox_ferias    = substr($campos[$i]['data_prox_ferias'], 5, 2);
    $dia_prox_ferias    = substr($campos[$i]['data_prox_ferias'], 8, 2);
    
    $periodo_anual_data_inicial = ($ano_prox_ferias - 2).'-'.$mes_prox_ferias.'-'.$dia_prox_ferias;
    $periodo_anual_data_final   = data::datatodate(data::adicionar_data_hora(data::datetodata($periodo_anual_data_inicial, '/'), 364), '-');
    
    echo $sql = "UPDATE `funcionarios` SET `periodo_anual_data_inicial` = '$periodo_anual_data_inicial', `periodo_anual_data_final` = '$periodo_anual_data_final' WHERE `id_funcionario` = '".$campos[$i]['id_funcionario']."' LIMIT 1 ";
    echo '<br>';
    bancos::sql($sql);
}*/

for($i = 0; $i < $linhas; $i++) {
    $data_prox_ferias   = data::datatodate(data::adicionar_data_hora($campos[$i]['periodo_anual_data_final'], 1), '-');
    echo $sql = "UPDATE `funcionarios` SET `data_prox_ferias` = '$data_prox_ferias' WHERE `id_funcionario` = '".$campos[$i]['id_funcionario']."' LIMIT 1 ";
    echo '<br>';
    bancos::sql($sql);
}
?>