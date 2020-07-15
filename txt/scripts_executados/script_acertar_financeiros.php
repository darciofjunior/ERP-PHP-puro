<?
require('../../lib/segurancas.php');

/*$sql = "SELECT id_conta_receber, id_bordero 
            FROM `contas_receberes_vs_borderos` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
	$sql = "UPDATE `contas_receberes` SET `id_bordero` = '".$campos[$i]['id_bordero']."' WHERE `id_conta_receber` = '".$campos[$i]['id_conta_receber']."' LIMIT 1 ";
	bancos::sql($sql);
}
echo 'TOTAL DE REGISTROS: '.$linhas;

$sql = "SELECT id_conta_receber, id_representante 
		FROM `contas_receberes_vs_representantes` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
	$sql = "UPDATE `contas_receberes` SET `id_representante` = '".$campos[$i]['id_representante']."' WHERE `id_conta_receber` = '".$campos[$i]['id_conta_receber']."' LIMIT 1 ";
	bancos::sql($sql);
}
echo 'TOTAL DE REGISTROS: '.$linhas;

$sql = "SELECT id_conta_receber, id_nf 
		FROM `contas_receberes_vs_nfs_dev` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
	$sql = "UPDATE `contas_receberes` SET `id_nf_devolucao` = '".$campos[$i]['id_nf']."' WHERE `id_conta_receber` = '".$campos[$i]['id_conta_receber']."' LIMIT 1 ";
	bancos::sql($sql);
}
echo 'TOTAL DE REGISTROS: '.$linhas;

$sql = "SELECT id_conta_receber, id_banco 
		FROM `contas_receberes_vs_bancos` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
	$sql = "UPDATE `contas_receberes` SET `id_banco` = '".$campos[$i]['id_banco']."' WHERE `id_conta_receber` = '".$campos[$i]['id_conta_receber']."' LIMIT 1 ";
	bancos::sql($sql);
}
echo 'TOTAL DE REGISTROS: '.$linhas;

$sql = "SELECT crno.id_conta_receber, crno.id_nf_outra, nfso.id_cliente 
		FROM `contas_receberes_vs_nfs_outras` crno 
		INNER JOIN `nfs_outras` nfso ON nfso.id_nf_outra = crno.id_nf_outra ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
	$sql = "UPDATE `contas_receberes` SET `id_cliente` = '".$campos[$i]['id_cliente']."', `id_nf_outra` = '".$campos[$i]['id_nf_outra']."' WHERE `id_conta_receber` = '".$campos[$i]['id_conta_receber']."' LIMIT 1 ";
	bancos::sql($sql);
}
echo 'TOTAL DE REGISTROS: '.$linhas;

$sql = "SELECT id_conta_receber, id_cliente 
		FROM `contas_receberes_vs_clientes` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
	$sql = "UPDATE `contas_receberes` SET `id_cliente` = '".$campos[$i]['id_cliente']."' WHERE `id_conta_receber` = '".$campos[$i]['id_conta_receber']."' LIMIT 1 ";
	bancos::sql($sql);
}
echo 'TOTAL DE REGISTROS: '.$linhas;*/

$sql = "SELECT crn.id_conta_receber, crn.id_nf, nfs.id_cliente 
        FROM `contas_receberes_vs_nfs` crn 
        INNER JOIN `nfs` ON nfs.id_nf = crn.id_nf ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
	$sql = "UPDATE `contas_receberes` SET `id_cliente` = '".$campos[$i]['id_cliente']."', `id_nf` = '".$campos[$i]['id_nf']."' WHERE `id_conta_receber` = '".$campos[$i]['id_conta_receber']."' LIMIT 1 ";
	bancos::sql($sql);
}
echo 'TOTAL DE REGISTROS: '.$linhas;
?>