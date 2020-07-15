<?
require('../../../lib/segurancas.php');
require('../../../lib/ajax.php');

if($_POST[opt_outras_opcoes] == 1) {//Gerar NF Complementar ...
    /*
    1) Listo as NFs de Saída que estão com status de Despachada ...
    2) Listo as NF Outras que estão com status pelo menos de Faturada ...
    Ambas tem de possuir pelo menos um CFOP ...*/
    $sql = "(SELECT nfs.`id_nf`, nnn.`numero_nf` 
            FROM `nfs` 
            INNER JOIN `nfs_num_notas` nnn ON nnn.id_nf_num_nota = nfs.id_nf_num_nota 
            WHERE nfs.`id_cliente` = '$_POST[id_cliente]' 
            AND nfs.`id_empresa` = '$_POST[id_empresa_nota]' 
            AND nfs.`status` = '4') 
            UNION ALL 
            (SELECT /*Esse Pipe é um Macete ...*/ CONCAT('777', nfso.`id_nf_outra`), nnn.`numero_nf` 
            FROM `nfs_outras` nfso 
            INNER JOIN `nfs_num_notas` nnn ON nnn.`id_nf_num_nota` = nfso.`id_nf_num_nota` 
            WHERE nfso.`id_cliente` = '$_POST[id_cliente]' 
            AND nfso.`id_empresa` = '$_POST[id_empresa_nota]' 
            AND nfso.`status` >= '2' 
            AND nfso.`id_cfop` > '0') ORDER BY `numero_nf` DESC ";
    $campos = bancos::sql($sql);
    $combo  = ajax::combo($campos, 'id_nf', 'numero_nf');
}else {//Venda para Entrega Futura ...
    $sql = "SELECT `id_pedido_venda`, `id_pedido_venda` 
            FROM `pedidos_vendas` 
            WHERE `id_cliente` = '$_POST[id_cliente]' 
            AND `id_empresa` = '$_POST[id_empresa_nota]' 
            ORDER BY `id_pedido_venda` DESC ";
    $campos = bancos::sql($sql);
    $combo  = ajax::combo($campos, 'id_pedido_venda', 'id_pedido_venda');
}
?>