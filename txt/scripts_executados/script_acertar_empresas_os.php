<?
require('../../lib/segurancas.php');

//Busca das OSs importadas para Pedido ...
$sql = "SELECT id_os, id_pedido 
        FROM oss 
        WHERE id_pedido <> '0' order by oss.id_os ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
//Agora com o id_pedido busco a Empresa do Pedido ...
    $sql = "SELECT id_empresa 
            FROM `pedidos` 
            WHERE `id_pedido` = '".$campos[$i]['id_pedido']."' LIMIT 1 ";
    $campos_pedido 	= bancos::sql($sql);
//Atualizando o Item de Nota Fiscal com o ICMS e a Redução correta ...
    $sql = "Update `oss` set `id_empresa` = '".$campos_pedido[0]['id_empresa']."' where `id_os` = '".$campos[$i]['id_os']."' limit 1 ";
    bancos::sql($sql);
}
?>