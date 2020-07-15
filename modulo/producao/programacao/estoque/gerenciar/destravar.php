<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/estoque_acabado.php');
segurancas::geral('/erp/albafer/modulo/producao/programacao/estoque/gerenciar/consultar.php', '../../../../../');
estoque_acabado::status_estoque($_GET['id_produto_acabado'], 0);//Destravar o PA ...
?>
<Script Language = 'JavaScript'>
    window.close()
</Script>