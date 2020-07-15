<?
require('../../lib/segurancas.php');

//Busco todos os Clientes que estao com credito A e que possuem compras nos ultimos 6 meses ...
$sql = "SELECT id_cliente 
        FROM `clientes` 
        WHERE `credito` = 'A' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $sql = "SELECT nfs.id_cliente, (ROUND((SUM(nfsi.qtde * nfsi.valor_unitario) / 1000), 0) * 1000 / 2) AS valor_total 
            FROM `nfs` 
            INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf 
            WHERE nfs.id_cliente = '".$campos[$i]['id_cliente']."' 
            AND nfs.data_emissao BETWEEN '2012-10-22' AND '2013-04-22' 
            GROUP BY nfs.id_cliente ";
    $campos_nfs     = bancos::sql($sql);
    $limite_credito = (count($campos_nfs) == 0) ? 0 : $campos_nfs[0]['valor_total'];

    $sql = "UPDATE `clientes` SET `credito` = 'B', `limite_credito` = '$limite_credito' WHERE `id_cliente` = '".$campos[$i]['id_cliente']."' LIMIT 1 ";
    bancos::sql($sql);
}
echo 'TOTAL DE REGISTRO(S): '.$linhas;
?>