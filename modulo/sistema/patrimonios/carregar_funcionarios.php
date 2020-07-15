<?
require('../../../lib/segurancas.php');
require('../../../lib/ajax.php');

$cmb_departamento = (!empty($_POST['cmb_departamento'])) ? $_POST['cmb_departamento'] : '%';

//Lista todos os Funcionários do Departamento passado por parâmetro e que ainda trabalham na Empresa ...
$sql = "SELECT `id_funcionario`, UPPER(`nome`) AS rotulo 
        FROM `funcionarios` 
        WHERE `id_departamento` LIKE '$cmb_departamento' 
        AND `status` < '3' ORDER BY `rotulo` ";
$campos = bancos::sql($sql);
$combo 	= ajax::combo($campos, 'id_funcionario', 'rotulo');
?>