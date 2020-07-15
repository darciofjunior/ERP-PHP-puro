<?
require('../../../lib/segurancas.php');
require('../../../lib/ajax.php');
session_start('funcionarios');

//Aqui eu listo todos os Clientes de acordo com o que foi digitado pelo Usuário ...
$sql = "SELECT CONCAT(razaosocial, ' (', nomefantasia, ') | ', id_cliente) AS nome 
        FROM `clientes` 
        WHERE (`nomefantasia` LIKE '$_POST[txt_cliente]%' OR `razaosocial` LIKE '$_POST[txt_cliente]%') 
        AND `ativo` = '1' ORDER BY razaosocial LIMIT 20 ";
$campos         = bancos::sql($sql);
$auto_complete 	= ajax::auto_complete($campos, 'nome');
?>