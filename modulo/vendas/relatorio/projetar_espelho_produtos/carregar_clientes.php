<?
require('../../../../lib/segurancas.php');
require('../../../../lib/ajax.php');

//Listo todos os melhores Clientes do Representante selecionado de acordo com a sua colocaηγo no Ranking ...
$sql = "SELECT DISTINCT(cr.id_cliente), CONCAT(c.ranking, ' - ', REPLACE(c.razaosocial, '&', 'E')) AS rotulo 
        FROM `clientes_vs_representantes` cr 
        INNER JOIN `clientes` c ON c.id_cliente = cr.id_cliente AND c.ativo = '1' 
        WHERE cr.`id_representante` = '$_POST[cmb_representante]' 
        ORDER BY razaosocial ";
$campos = bancos::sql($sql);
$combo 	= ajax::combo($campos, 'id_cliente', 'rotulo');
?>