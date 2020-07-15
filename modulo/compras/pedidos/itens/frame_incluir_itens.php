<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../../');

$sql = "SELECT `tp_moeda` 
        FROM `pedidos` 
        WHERE `id_pedido` = '$_GET[id_pedido]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<title>.:: Incluir Itens de Pedido ::.</title>
<frameset framespacing='0' rows='72, *' frameborder='0'>
    <frame name='fornecedor_produto' src="superior.php?id_pedido=<?=$_GET['id_pedido'];?>&tipo_moeda=<?=$campos[0]['tp_moeda'];?>" marginheight='0' marginwidth='0' noresize='NO' scrolling='NO'>
    <frame name='inferior_produto' src="inferior.php?id_pedido=<?=$_GET['id_pedido'];?>&tipo_moeda=<?=$campos[0]['tp_moeda'];?>" marginheight='0' marginwidth='0' noresize='NO' scrolling='NO'>
</frameset>
</html>