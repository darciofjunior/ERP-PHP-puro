<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
require('../../../../../lib/financeiros.php');
session_start('funcionarios');

if($id_emp == 1) {//Albafer
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/albafer/index.php';
}else if($id_emp == 2) {//Tool Master
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/tool_master/index.php';
}else if($id_emp == 4) {//Grupo
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/grupo/index.php';
}else if($id_emp == 0) {//Todos
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/todas_empresas/index.php';
}
segurancas::geral($endereco, '../../../../../');
financeiros::cadastrar_contas_automaticas();

echo '<b>ATUALIZANDO ...</b>';
?>
<Script Language = 'JavaScript'>
function finalizar() {
    opener.window.location = 'itens.php<?=$parametro;?>'
    window.close()
}
//Coloco um timer p/ garantir que rodou todo o Script em PHP do "cadastrar_contas_automaticas()", evitando ...
setTimeout('finalizar()', 2500)
</Script>