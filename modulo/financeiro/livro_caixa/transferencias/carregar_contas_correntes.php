<?
require('../../../../lib/segurancas.php');
require('../../../../lib/ajax.php');

//Listo todos os aços de acordo com a Geometria, Qualidade e Bitolas ...
$sql = "SELECT cc.id_contacorrente, CONCAT(b.banco, ' | ', a.cod_agencia, ' | ', cc.conta_corrente) AS rotulo 
        FROM `contas_correntes` cc 
        INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
        INNER JOIN `bancos` b ON b.`id_banco` = a.`id_banco` 
        WHERE cc.`ativo` = '1' 
        AND cc.`id_empresa` = '$_POST[cmb_empresa]' ORDER BY cc.conta_corrente ";
$campos = bancos::sql($sql);
$combo 	= ajax::combo($campos, 'id_contacorrente', 'rotulo');
?>