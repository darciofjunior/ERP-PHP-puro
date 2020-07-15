<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/calculos.php');//Essa biblioteca щ chamada aqui porque a mesma щ utilizada dentro da Compras New ...
require('../../../../../lib/compras_new.php');
require('../../../../../lib/data.php');
require('../../../../../lib/genericas.php');
require('../../../../../lib/intermodular.php');
require('../../../../../lib/producao.php');
require('../../../../../lib/variaveis/compras.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/nota_entrada/itens/consultar.php', '../../../../../');

if (empty($posicao)) $posicao = 1;
//1) Verifico quem щ id_item_pedido desta NF, p/ ver para qual tela de alteraчуo que eu vou prosseguir ...
$sql = "SELECT id_item_pedido 
        FROM `nfe_historicos` 
        WHERE `id_nfe` = '$_GET[id_nfe]' ";
$campos 	= bancos::sql($sql, ($posicao - 1), $posicao);
$id_item_pedido = $campos[0]['id_item_pedido'];

//2) Verifico se este id_item_pedido estс atrelada a um item de OS ...
$sql = "SELECT id_os_item 
        FROM `oss_itens` 
        WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
$campos = bancos::sql($sql);
if(count($campos) == 1) {//Significa que este id_item_pedido estс atrelado a uma OS ...
    require('alterar_itens_nf_os.php');
}else {//Щ apenas um simples item de pedido ...
    require('alterar_itens_nf.php');
}
?>