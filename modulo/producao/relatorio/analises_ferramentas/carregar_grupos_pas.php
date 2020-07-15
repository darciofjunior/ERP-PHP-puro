<?
require('../../../../lib/segurancas.php');
require('../../../../lib/ajax.php');

//Listo todos os aços de acordo com a Geometria, Qualidade e Bitolas ...
$sql = "SELECT id_grupo_pa, nome AS rotulo 
        FROM `grupos_pas` 
        WHERE `id_familia` IN ($_POST[id_familia]) 
        AND `ativo`  = '1' ORDER BY nome ";
$campos = bancos::sql($sql);
$combo 	= ajax::combo($campos, 'id_grupo_pa', 'rotulo');
?>