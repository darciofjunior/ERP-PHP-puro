<?
require('../../lib/segurancas.php');

$fp = fopen('script_preco_bruto_nova_lista_precos.txt', 'r');
while(!feof($fp)) {
    $linha = fgets($fp);
    $vetor = explode('|', $linha);

    $sql = "SELECT id_produto_acabado 
            FROM `produtos_acabados` 
            WHERE `referencia` = '$vetor[0]' ";
    $campos_pa  = bancos::sql($sql);

    $sql = "UPDATE `produtos_acabados` SET `preco_unitario_simulativa` = '$vetor[1]' WHERE `id_produto_acabado` = '".$campos_pa[0]['id_produto_acabado']."' LIMIT 1 ";
    echo $sql.'<br/>';
    bancos::sql($sql);
}
fclose($fp);
?>