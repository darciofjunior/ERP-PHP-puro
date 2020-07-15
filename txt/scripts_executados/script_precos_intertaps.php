<?
require('../../lib/segurancas.php');
session_start('funcionarios');

echo 'APÓS RODAR SCRIPT, ENTRAR NAS PÁGINAS DA LISTA E SALVAR, PARA QUE O SISTEMA ASSUMA O PREÇO NA SUA FORMA DE COMPRA';

$fp = fopen("precos_intertaps.txt", "r");
while(!feof($fp)) {
    $linha = fgets($fp);
    $vetor = explode('|', $linha);

    $sql = "SELECT id_produto_insumo 
            FROM `produtos_acabados` 
            WHERE `referencia` = '$vetor[0]' 
            AND `id_produto_insumo` > '0' 
            AND `ativo` = '1' ";
    $campos_pi          = bancos::sql($sql);
    $id_produto_insumo  = $campos_pi[0]['id_produto_insumo'];

    $update = "UPDATE `fornecedores_x_prod_insumos` 
                SET `preco_faturado` = '$vetor[1]', `data_sys` = '".date('Y-m-d H:i:s')."' 
                WHERE `id_fornecedor` = '657'
                AND `id_produto_insumo` = '$id_produto_insumo' ";
    echo mysql_query($update) or die(mysql_error());
}
fclose($fp);
?>