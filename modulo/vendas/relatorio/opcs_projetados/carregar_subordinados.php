<?
require('../../../../lib/segurancas.php');
require('../../../../lib/ajax.php');

//Trago todos os representantes subordinados do Funcionário "Representante" logado ou selecionado ...
$sql = "SELECT r.id_representante, CONCAT(r.nome_fantasia, ' / ', r.zona_atuacao) AS rotulo 
        FROM `representantes_vs_supervisores` rs 
        INNER JOIN `representantes` r ON r.id_representante = rs.id_representante AND r.ativo = '1' 
        WHERE rs.`id_representante_supervisor` = '$_POST[cmb_representante]' ORDER BY rotulo ";
$campos = bancos::sql($sql);
$combo 	= ajax::combo($campos, 'id_representante', 'rotulo');
?>