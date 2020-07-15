<?
require('../../../../../lib/segurancas.php');

if($id_emp2 == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/albafer/index.php';
}else if($id_emp2 == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/tool_master/index.php';
}else if($id_emp2 == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/grupo/index.php';
}else if($id_emp2 == 0) {//Todos
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/todas_empresas/index.php';
}
segurancas::geral($endereco, '../../../../../');

session_start('funcionarios');
session_unregister('id_emp');
$GLOBALS['id_emp']=$id_emp2;
session_start('funcionarios');
session_register('id_emp');
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv = 'Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv = 'Cache-Control' content='no-store'>
<meta http-equiv = 'Pragma' content='no-cache'>
</head>
<frameset rows='89%, 11%' frameborder='no' border='0' framespacing='0'>
    <frame name='itens' src='../classes/itens.php?txt_fornecedor=<?=$txt_fornecedor;?>&txt_numero_conta=<?=$txt_numero_conta;?>&txt_data_emissao_inicial=<?=$txt_data_emissao_inicial;?>&txt_data_emissao_final=<?=$txt_data_emissao_final;?>&txt_data_vencimento_alterada_inicial=<?=$txt_data_vencimento_alterada_inicial;?>&txt_data_vencimento_alterada_final=<?=$txt_data_vencimento_alterada_final;?>&txt_data_inicial=<?=$txt_data_inicial;?>&txt_data_final=<?=$txt_data_final;?>&txt_semana=<?=$txt_semana;?>&txt_valor=<?=$txt_valor;?>&txt_bairro=<?=$txt_bairro;?>&txt_cidade=<?=$txt_cidade;?>&cmb_uf=<?=$cmb_uf;?>&cmb_conta_caixa=<?=$cmb_conta_caixa;?>&cmb_tipo_pagamento=<?=$cmb_tipo_pagamento;?>&cmb_importacao=<?=$cmb_importacao;?>&cmb_contas_vencidas=<?=$cmb_contas_vencidas;?>&chkt_mostrar=<?=$chkt_mostrar;?>&chkt_pago_pelo_caixa_compras=<?=$chkt_pago_pelo_caixa_compras;?>&chkt_somente_importacao=<?=$chkt_somente_importacao;?>'>
    <frame name='rodape' src='../classes/rodape.php?id_emp2=<?=$id_emp2;?>'>
</frameset>
<noframes>
    <body>
    </body>
</noframes>
</html>