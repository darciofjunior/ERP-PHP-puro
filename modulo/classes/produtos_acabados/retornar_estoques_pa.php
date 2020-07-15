<?
require('../../../lib/segurancas.php');
require('../../../lib/estoque_acabado.php');

$estoque_produto    = estoque_acabado::qtde_estoque($_GET['id_produto_acabado']);
$qtde_disponivel    = $estoque_produto[3];
$qtde_excedente     = $estoque_produto[14];
?>
<Script Language = 'JavaScript'>
    parent.document.form.txt_qtde_disponivel_inicial.value  = '<?=$qtde_disponivel;?>'
    //parent.document.form.txt_total_vale.value   = '<?=$qtde_excedente;?>'
</Script>