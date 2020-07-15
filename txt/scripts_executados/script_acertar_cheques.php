<?
require('../../lib/segurancas.php');

$sql = "SELECT crq.id_cheque_cliente, crq.valor, cr.id_cliente, cr.id_empresa 
        FROM `contas_receberes_quitacoes` crq
        INNER JOIN contas_receberes cr ON cr.id_conta_receber = crq.id_conta_receber
        WHERE crq.id_tipo_recebimento = '5'
        AND crq.id_cheque_cliente <= 3475 ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $sql = "UPDATE `cheques_clientes` SET `id_cliente` = '".$campos[$i]['id_cliente']."', `id_empresa` =  '".$campos[$i]['id_empresa']."', `status_disponivel` = '2' WHERE `id_cheque_cliente` = '".$campos[$i]['id_cheque_cliente']."' LIMIT 1 ";
    bancos::sql($sql);
}
echo 'TOTAL DE REGISTROS: '.$linhas;
?>