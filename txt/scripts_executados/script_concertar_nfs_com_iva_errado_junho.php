<?
require('../../lib/segurancas.php');
/*Busca das NFs que estejam no Período digitado pelo Usuário, que não estejam Canceladas e com Itens em que 
a Operação de Faturamento sejam igual a Industrial ...*/
$sql = "SELECT nfsi.id_nfs_item, id_classific_fiscal 
        FROM `nfs` 
        INNER JOIN `clientes` c ON c.id_cliente = cc.id_cliente AND c.id_uf = '1' 
        INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf AND nfsi.iva = '37.00' 
        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = nfsi.id_produto_acabado 
        WHERE nfs.`data_emissao` >= '2009-06-01' ORDER BY nfsi.id_nfs_item ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
//Através da Classificação Fiscal que é gravada no Item da NF, eu busco o IVA atual ...
    $sql = "SELECT iva 
            FROM `classific_fiscais` 
            WHERE `id_classific_fiscal` = ".$campos[$i]['id_classific_fiscal']." LIMIT 1 ";
    $campos_iva = bancos::sql($sql);
    $sql = "UPDATE `nfs_itens` SET `iva` = '".$campos_iva[0]['iva']."' WHERE `id_nfs_item` = '".$campos[$i]['id_nfs_item']."' LIMIT 1 ";
    bancos::sql($sql);
}
?>