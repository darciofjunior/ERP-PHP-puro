<?
require('../../../../lib/segurancas.php');
require('../../../../lib/ajax.php');

if($_POST['txt_bitola1_aco'] == '0,00') $_POST['txt_bitola1_aco'] = '';
if($_POST['txt_bitola2_aco'] == '0,00') $_POST['txt_bitola2_aco'] = '';

$geometria_aco 	= (!empty($_POST['cmb_geometria_aco'])) ? $_POST['cmb_geometria_aco'] : '%%';
$qualidade_aco 	= (!empty($_POST['cmb_qualidade_aco'])) ? $_POST['cmb_qualidade_aco'] : '%%';
$bitola1_aco 	= (!empty($_POST['txt_bitola1_aco'])) ? " >= '".(float)str_replace(',', '.', $_POST['txt_bitola1_aco'])."' " : " like '%%'";
$bitola2_aco 	= (!empty($_POST['txt_bitola2_aco'])) ? " >= '".(float)str_replace(',', '.', $_POST['txt_bitola2_aco'])."' " : " like '%%'";

//Listo todos os aços de acordo com a Geometria, Qualidade e Bitolas ...
$sql = "SELECT pi.`id_produto_insumo`, CONCAT(pi.`discriminacao`, ' | ', REPLACE(ROUND(ei.`qtde` / pia.`densidade_aco`, 2), '.', ','), ' m | ', REPLACE(ei.`qtde`, '.', ','), ' kg') AS rotulo 
        FROM `produtos_insumos` pi 
        INNER JOIN `estoques_insumos` ei ON ei.`id_produto_insumo` = pi.`id_produto_insumo` 
        INNER JOIN `produtos_insumos_vs_acos` pia ON pia.`id_produto_insumo` = pi.`id_produto_insumo` AND pia.`id_geometria_aco` LIKE '$geometria_aco' AND pia.`bitola1_aco` $bitola1_aco AND pia.`bitola2_aco` $bitola2_aco AND pia.`id_qualidade_aco` LIKE '$qualidade_aco' 
        WHERE pi.`ativo` = '1' ORDER BY pi.`discriminacao` ";
$campos = bancos::sql($sql);
$combo 	= ajax::combo($campos, 'id_produto_insumo', 'rotulo');
?>