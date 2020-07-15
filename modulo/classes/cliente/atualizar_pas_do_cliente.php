<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/vendas/cliente/vs_produtos_acabados.php', '../../../');

//Busca todos os PA(s) que o Cliente já comprou da Albafer ...
$sql = "SELECT DISTINCT(`id_produto_acabado`) 
        FROM `pedidos_vendas_itens` pvi 
        INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
        AND `id_cliente` = '$_GET[id_cliente]' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    //Verifico se esse PA que já foi Comprado de nós possui Código de Cliente ...
    $sql = "SELECT id_pa_cod_cliente 
            FROM `pas_cod_clientes` 
            WHERE `id_produto_acabado` = ".$campos[$i]['id_produto_acabado']." 
            AND `id_cliente` = '$_GET[id_cliente]' LIMIT 1 ";
    $campos_produtos = bancos::sql($sql);
    //Não possui, sendo assim, adiciono o mesmo na nosso BD pq posteriormente possamos colocar um código ...
    if(count($campos_produtos) == 0) {
        $sql = "INSERT INTO `pas_cod_clientes` (`id_pa_cod_cliente`, `id_produto_acabado`, `id_cliente`) VALUES (NULL, '".$campos[$i]['id_produto_acabado']."', '$_GET[id_cliente]') ";
        bancos::sql($sql);
    }
}
?>
<Script Language = 'JavaScript'>
    alert('PAS ATUALIZADOS !')
    parent.document.form.submit()
    parent.html5Lightbox.finish()
</Script>