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
    alert('NOVO PA ESP PROVISIONADO COM SUCESSO !!!\n\nEM BREVE O DEPTO. TÉCNICO IRÁ INCLUIR ESSE NOVO PA(S) ESP NESTE ORÇAMENTO !\n\nAPÓS PROVISIONAR TODOS OS ITENS INEXISTENTES VÁ ATÉ OUTRAS OPÇÕES PARA ENVIAR O E-MAIL DE SOLICITAÇÃO AO DEPTO. TÉCNICO !')
    parent.ativar_loading()
    window.location = 'incluir_lote.php?id_orcamento_venda=<?=$_POST['id_orcamento_venda'];?>'
</Script>