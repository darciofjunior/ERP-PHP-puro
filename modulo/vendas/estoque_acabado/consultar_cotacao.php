<?
require('../../../lib/segurancas.php');
//Aqui eu busco a �ltima Cota��o gerada no Sistema e somo em cima dessa + 1 que ser� a pr�xima gerada ...
$sql = "SELECT id_cotacao 
        FROM `cotacoes` 
        ORDER BY id_cotacao DESC ";
$campos = bancos::sql($sql);
echo 'Cota&ccedil;&atilde;o Gerada N.&deg; '.($campos[0]['id_cotacao'] + 1);
?>