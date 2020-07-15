<?
require('../../../../../lib/segurancas.php');

session_start('funcionarios');//Não posso retirar esse código de Sessão porque aqui está registrado o $id_emp ...

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/grupo/index.php';
}else if($id_emp == 0) {//Todos
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/todas_empresas/index.php';
}
segurancas::geral($endereco, '../../../../../');

//Aqui atualiza o status do cheque p/ 0 "Liberado", antes estava bloqueado e nunca foi usado ...
$sql = "UPDATE `cheques` SET `status` = '0' WHERE `status` = '1' AND `id_funcionario` = '$_SESSION[id_funcionario]' AND `valor` = '0.00' ";
bancos::sql($sql);
 
//Aqui atualiza o status do cheque p/ 2, pq ele esta  bloqueado e já foi usado
$sql = "UPDATE `cheques` SET `status` = '2' WHERE `status` = '1' AND `id_funcionario` = '$_SESSION[id_funcionario]' AND `valor` <> '0.00' ";
bancos::sql($sql);

//Aqui mudo o status do cheque para '1', p/ Travar o cheque que foi escolhido ...
if(!empty($_POST['cmb_cheque'])) {
    $sql = "UPDATE `cheques` SET `status` = '1', `id_funcionario` = '$_SESSION[id_funcionario]' WHERE `id_cheque` = '$_POST[cmb_cheque]' LIMIT 1 ";
    bancos::sql($sql);
}
?>