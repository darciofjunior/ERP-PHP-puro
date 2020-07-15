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
$sql = "SELECT pi.id_produto_insumo, concat(pi.discriminacao, ' | ', replace(round(ei.qtde / pia.densidade_aco, 2), '.', ','), ' m | ', replace(ei.qtde, '.', ','), ' kg') as rotulo 
        FROM `produtos_insumos` pi 
        INNER JOIN estoques_insumos ei on ei.id_produto_insumo = pi.id_produto_insumo 
        INNER JOIN produtos_insumos_vs_acos pia on pia.id_produto_insumo = pi.id_produto_insumo and pia.id_geometria_aco like '$geometria_aco' and pia.bitola1_aco $bitola1_aco and pia.bitola2_aco $bitola2_aco and pia.id_qualidade_aco like '$qualidade_aco' 
        WHERE pi.ativo = '1' order by pi.discriminacao ";
$campos = bancos::sql($sql);
$combo 	= ajax::combo($campos, 'id_produto_insumo', 'rotulo');
?>