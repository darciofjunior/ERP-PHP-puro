<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/ajax.php');

//Se o checkbox estiver marcado, significa que o usuário deseja visualizar todas as Importações ...
$condicao_ultimos_6meses = ($_POST['checado'] == 1) ? '' : " AND SUBSTRING(n.`data_emissao`, 1, 10) >= DATE_ADD('".date('Y-m-d')."', INTERVAL -6 MONTH) ";

//Listo as importações de acordo com o Filtro passado por parâmetro ...
$sql = "SELECT i.id_importacao AS id_importacao, i.nome AS rotulo 
        FROM `importacoes` i 
        INNER JOIN `nfe` n ON n.id_importacao = i.id_importacao $condicao_ultimos_6meses 
        WHERE i.`ativo` = '1' ORDER BY i.nome ";
$campos = bancos::sql($sql);
$combo 	= ajax::combo($campos, 'id_importacao', 'rotulo');
?>