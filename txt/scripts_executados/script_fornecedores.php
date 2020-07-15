<?
require('../../lib/segurancas.php');

$sql = "SELECT id_fornecedor, razaosocial, endereco, insc_est, cep 
        FROM `fornecedores` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $vetor 		= explode(',', $campos[$i]['endereco']);
    $cep 		= substr($campos[$i]['cep'], 0, 5).'-'.substr($campos[$i]['cep'], 5, 3);
    $insc_est 	= substr($campos[$i]['insc_est'], 0, 3).'.'.substr($campos[$i]['insc_est'], 3, 3).'.'.substr($campos[$i]['insc_est'], 6, 3).'.'.substr($campos[$i]['insc_est'], 9, 3);
    echo $sql = "UPDATE `fornecedores` SET endereco = '$vetor[0]', `num_complemento` = '".str_replace('.', '', trim($vetor[1]))."', `insc_est` = '$insc_est', `cep` = '$cep' WHERE `id_fornecedor` = '".$campos[$i]['id_fornecedor']."' LIMIT 1 ;".'<br>';
}
?>