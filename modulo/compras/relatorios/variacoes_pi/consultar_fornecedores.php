<?
require('../../../../lib/segurancas.php');
require('../../../../lib/ajax.php');
session_start('funcionarios');

//Aqui eu listo todos os Fornecedores de acordo com o que foi digitado pelo Usuário ...
$sql = "SELECT `razaosocial` AS fornecedor 
        FROM `fornecedores` 
        WHERE (`nomefantasia` LIKE '$_POST[txt_consultar]%' OR `razaosocial` LIKE '$_POST[txt_consultar]%') 
        AND `ativo` = '1' ORDER BY razaosocial LIMIT 20 ";
$campos         = bancos::sql($sql);
$auto_complete 	= ajax::auto_complete($campos, 'fornecedor');
?>