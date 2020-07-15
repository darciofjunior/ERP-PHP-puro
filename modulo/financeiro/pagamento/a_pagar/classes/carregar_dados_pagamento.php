<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/ajax.php');

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

//Só fará essa busca específica por empresa, quando essa realmente existir ...
if($id_emp > 0) $condicao_empresa = " AND cc.`id_empresa` = '$id_emp' ";

if($_POST['valor'] == 1) {
    $sql = "SELECT DISTINCT(cc.`id_contacorrente`) AS id_contacorrente, CONCAT(b.banco, ' | ', conta_corrente) AS rotulo 
            FROM `bancos` b 
            INNER JOIN `agencias` a ON a.`id_banco` = b.`id_banco` AND a.`ativo` = '1' 
            INNER JOIN `contas_correntes` cc ON cc.`id_agencia` = a.`id_agencia` $condicao_empresa 
            WHERE b.`ativo` = '1' ORDER BY `banco` ";
    $campos = bancos::sql($sql);
    $combo  = ajax::combo($campos, 'id_contacorrente', 'rotulo');
}

if($_POST['valor'] == 2) {
    /*Aqui eu listo Somente os cheques "Em Abertos" das Contas Correntes Ativas, Talões Ativos, 
    do Banco que foi selecionado ...*/
    $sql = "SELECT DISTINCT(`id_cheque`) AS id_cheque, CONCAT(`conta_corrente`,' | ', `num_cheque`) AS rotulo 
            FROM `cheques` c 
            INNER JOIN `taloes` t ON t.`id_talao` = c.`id_talao` AND t.`ativo` = '1' 
            INNER JOIN `contas_correntes` cc ON cc.`id_contacorrente` = t.`id_contacorrente` AND cc.`id_contacorrente` = '$_POST[cmb_conta_corrente]' $condicao_empresa AND cc.`ativo` = '1' 
            WHERE (c.`status` = '0' 
            AND c.`ativo` = '1') ORDER BY rotulo ";//Cheques Emitidos ...
    $campos = bancos::sql($sql);
    $combo  = ajax::combo($campos, 'id_cheque', 'rotulo');
}
?>