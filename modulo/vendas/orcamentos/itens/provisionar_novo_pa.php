<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../../');

$incluir_novos_pas = $_POST['hdd_quantidade'].' - '.$_POST['hdd_referencia'];
if(!empty($_POST['hdd_discriminacao'])) $incluir_novos_pas.= ' ('.str_replace('|', '%', $_POST['hdd_discriminacao']).')';
$incluir_novos_pas.= '<br>';
 
$sql = "UPDATE `orcamentos_vendas` SET `id_login_novos_pas` = '$_SESSION[id_login]', `incluir_novos_pas` = CONCAT(`incluir_novos_pas`, '$incluir_novos_pas') WHERE `id_orcamento_venda` = '$_POST[id_orcamento_venda]' LIMIT 1 ";
bancos::sql($sql);
?>
<Script Language = 'JavaScript'>
    alert('NOVO PA ESP PROVISIONADO COM SUCESSO !!!\n\nEM BREVE O DEPTO. T�CNICO IR� INCLUIR ESSE NOVO PA(S) ESP NESTE OR�AMENTO !\n\nAP�S PROVISIONAR TODOS OS ITENS INEXISTENTES V� AT� OUTRAS OP��ES PARA ENVIAR O E-MAIL DE SOLICITA��O AO DEPTO. T�CNICO !')
    parent.ativar_loading()
    window.location = 'incluir_lote.php?id_orcamento_venda=<?=$_POST['id_orcamento_venda'];?>'
</Script>