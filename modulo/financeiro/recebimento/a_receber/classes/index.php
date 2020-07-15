<?
require('../../../../../lib/segurancas.php');

if($id_emp2 == 1) {//Albafer
	$endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp2 == 2) {//Tool Master
	$endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp2 == 4) {//Grupo
	$endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}else if($id_emp == 0) {//Todas Empresas
	$endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/todas_empresas/index.php';
}
segurancas::geral($endereco, '../../../../../');

session_start('funcionarios');
session_unregister('id_emp');
$GLOBALS['id_emp'] = $id_emp2;
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
    <frame name='itens' src='../classes/itens.php?txt_cliente=<?=$txt_cliente;?>&txt_descricao_conta=<?=$txt_descricao_conta;?>&cmb_representante=<?=$cmb_representante;?>&txt_numero_conta=<?=$txt_numero_conta;?>&txt_data_emissao_inicial=<?=$txt_data_emissao_inicial;?>&txt_data_emissao_final=<?=$txt_data_emissao_final;?>&txt_data_vencimento_inicial=<?=$txt_data_vencimento_inicial;?>&txt_data_vencimento_final=<?=$txt_data_vencimento_final;?>&txt_data_inicial=<?=$txt_data_inicial;?>&txt_data_final=<?=$txt_data_final;?>&cmb_ano=<?=$cmb_ano;?>&txt_semana=<?=$txt_semana;?>&txt_data_cadastro=<?=$txt_data_cadastro;?>&cmb_banco=<?=$cmb_banco;?>&cmb_tipo_recebimento=<?=$cmb_tipo_recebimento;?>&chkt_mostrar=<?=$chkt_mostrar;?>&chkt_somente_exportacao=<?=$chkt_somente_exportacao;?>&txt_bairro=<?=$txt_bairro;?>&txt_cidade=<?=$txt_cidade;?>&cmb_uf=<?=$cmb_uf;?>'>
    <frame name='rodape' src='../classes/rodape.php?id_emp2=<?=$id_emp2;?>'>
</frameset>
<noframes>
    <body>
    </body>
</noframes>
</html>