<?
require('../../lib/segurancas.php');
require('../../lib/calculos.php');
require('../../lib/faturamentos.php');

$sql = "SELECT c.`id_pais`, nfs.`id_nf`, nfs.`id_empresa`, DATE_FORMAT(nfs.`data_emissao`, '%d/%m/%Y') AS data_emissao, 
        nfs.`valor1`, nfs.`valor2`, nfs.`valor3`, nfs.`valor4`, nfs.`suframa` 
        FROM `nfs` 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
        WHERE nfs.`data_emissao` >= '2013-01-01' ORDER BY nfs.`id_empresa` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $calculo_total_impostos = calculos::calculo_impostos(0, $campos[$i]['id_nf'], 'NF');
    $valor_total_nota       = round($calculo_total_impostos['valor_total_nota'], 2);
    $total_duplicatas       = round($campos[$i]['valor1'] + $campos[$i]['valor2'] + $campos[$i]['valor3'] + $campos[$i]['valor4'], 2);
}
?>