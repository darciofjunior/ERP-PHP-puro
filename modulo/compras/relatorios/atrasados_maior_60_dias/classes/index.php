<?
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/relatorios/atrasados_maior_60_dias/classes/consultar_contas.php', '../../../../../');
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Cache-Control" content="no-store">
<meta http-equiv="Pragma" content="no-cache">
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
</head>
<frameset rows="80, 10" frameborder="NO" border="0" framespacing="0">
  <frame name='itens' src = '../classes/itens.php?txt_cliente=<?=$_POST['txt_cliente'];?>&txt_descricao_conta=<?=$_POST['txt_descricao_conta'];?>&cmb_representante=<?=$_POST['cmb_representante'];?>&txt_numero_conta=<?=$_POST['txt_numero_conta'];?>&txt_data_emissao_inicial=<?=$_POST['txt_data_emissao_inicial'];?>&txt_data_emissao_final=<?=$_POST['txt_data_emissao_final'];?>&txt_data_vencimento_inicial=<?=$_POST['txt_data_vencimento_inicial'];?>&txt_data_vencimento_final=<?=$_POST['txt_data_vencimento_final'];?>&txt_data_inicial=<?=$_POST['txt_data_inicial'];?>&txt_data_final=<?=$_POST['txt_data_final'];?>&cmb_ano=<?=$_POST['cmb_ano'];?>&txt_semana=<?=$_POST['txt_semana'];?>&txt_data_cadastro=<?=$_POST['txt_data_cadastro'];?>&cmb_banco=<?=$_POST['cmb_banco'];?>&cmb_tipo_recebimento=<?=$_POST['cmb_tipo_recebimento'];?>&chkt_mostrar=<?=$_POST['chkt_mostrar'];?>&chkt_somente_exportacao=<?=$_POST['chkt_somente_exportacao'];?>&txt_bairro=<?=$_POST['txt_bairro'];?>&txt_cidade=<?=$_POST['txt_cidade'];?>&cmb_uf=<?=$_POST['cmb_uf'];?>'>
  <frame name='rodape' src = '../classes/rodape.php'>
</frameset>
<noframes>
    <body>
    </body>
</noframes>
</html>