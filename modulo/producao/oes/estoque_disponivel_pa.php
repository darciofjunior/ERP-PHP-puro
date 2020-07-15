<?
require('../../../lib/segurancas.php');
require('../../../lib/estoque_acabado.php');
segurancas::geral('/erp/albafer/modulo/producao/oes/incluir.php', '../../../');

if($_GET['id_produto_acabado'] > 0) {
    $estoque = estoque_acabado::qtde_estoque($_GET['id_produto_acabado']);
    echo number_format($estoque[3], 2, ',', '.');
}else {
    echo '0,00';
}
?>