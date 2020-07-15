<?
require('../../../lib/segurancas.php');
require('../../../lib/ajax.php');

//Se o checkbox estiver marcado, significa que o usuário deseja visualizar todas as Importações ...
$inner_join_nfe = ($_POST['checado'] == 1) ? '' : " INNER JOIN `nfe` ON nfe.`id_importacao` = i.`id_importacao` AND SUBSTRING(nfe.`data_emissao`, 1, 10) >= DATE_ADD('".date('Y-m-d')."', INTERVAL -6 MONTH) ";

//Listo as importações de acordo com o Filtro passado por parâmetro ...
$sql = "SELECT i.id_importacao AS id_importacao, i.nome AS rotulo 
        FROM `importacoes` i 
        $inner_join_nfe 
        WHERE i.`ativo` = '1' ORDER BY i.nome ";
$campos = bancos::sql($sql);
$combo 	= ajax::combo($campos, 'id_importacao', 'rotulo');
?>