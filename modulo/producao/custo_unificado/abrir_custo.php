<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/custo_unificado/custo_unificado.php', '../../../../');

$sql = "SELECT `operacao_custo` 
        FROM `produtos_acabados` 
        WHERE `id_produto_acabado` = '$_GET[id_produto_acabado]' LIMIT 1 ";
$campos = bancos::sql($sql);

if($campos[0]['operacao_custo'] == 0) {
    $url = '../custo/industrial/custo_industrial.php?id_produto_acabado='.$_GET['id_produto_acabado'].'&tela=1&pop_up=1';
}else {
    $url = '../custo/revenda/custo_revenda.php?id_produto_acabado='.$_GET['id_produto_acabado'].'&pop_up=1';
}

header('Location: '.$url);
?>