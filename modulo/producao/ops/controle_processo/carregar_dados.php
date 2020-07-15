<?
require('../../../../lib/segurancas.php');
require('../../../../lib/ajax.php');

if($_POST['valor'] == 1) {//Trago todos os Funcionários vinculados a Máquina passada por parâmetro ...
    $sql = "SELECT f.`id_funcionario`, f.`nome` AS rotulo 
            FROM `maquinas_vs_funcionarios` mf 
            INNER JOIN `funcionarios` f ON f.`id_funcionario` = mf.`id_funcionario` AND mf.`id_maquina` = '$_POST[cmb_maquina]' 
            ORDER BY f.`nome`  ";
    $campos = bancos::sql($sql);
    $combo  = ajax::combo($campos, 'id_funcionario', 'rotulo');
}

if($_POST['valor'] == 2) {//Trago todas as Operações vinculados a Máquina passada por parâmetro ...
    $sql = "SELECT DISTINCT(`id_maquina_operacao`), `operacao` AS rotulo 
            FROM `maquinas_vs_operacoes` 
            WHERE `id_maquina` = '$_POST[cmb_maquina]' 
            ORDER BY `operacao` ";
    $campos = bancos::sql($sql);
    $combo  = ajax::combo($campos, 'id_maquina_operacao', 'rotulo');
}
?>