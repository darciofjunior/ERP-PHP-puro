<?
require('../../lib/segurancas.php');

//Trago todos os PI's "PIPAS" da Lista de Preço da "JT" que possuem na Discriminação a palavra "Lima Agulha" ...
$sql = "SELECT * 
        FROM `fornecedores_x_prod_insumos` fpi 
        INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = fpi.`id_produto_insumo` 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = pi.`id_produto_insumo` AND pa.`ativo` = '1' 
        AND pa.`id_produto_acabado` NOT IN (646, 647, 651, 681, 682, 664, 691) /*O Fabio incluiu manualmente esses PAs ...*/
        AND pa.`discriminacao` LIKE '%LIMA%AGULHA%' 
        AND pa.referencia <> 'ESP' 
        WHERE `id_fornecedor` = '2196' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $sql = "INSERT INTO `fornecedores_x_prod_insumos` (`id_fornecedor_prod_insumo`, `id_fornecedor`, `id_produto_insumo`, 
            `preco`, `preco_exportacao`, `preco_faturado`, `preco_faturado_adicional`, `prazo_pgto_ddl`, 
            `desc_vista`, `desc_sgd`, `ipi`, `icms`, `reducao`, `iva`, `lote_minimo_reais`, `forma_compra`, `tp_moeda`, 
            `preco_faturado_export`, `preco_faturado_export_adicional`, `valor_moeda_compra`, `condicao_padrao`, 
            `fator_margem_lucro_pa`, `valor_moeda_custo`, `data_sys`, `lote_minimo_pa_rev`, `ativo`) 
            VALUES (NULL, '1320', '".$campos[$i]['id_produto_insumo']."', '".$campos[$i]['preco']."', 
            '".$campos[$i]['preco_exportacao']."', '".$campos[$i]['preco_faturado']."', 
            '".$campos[$i]['preco_faturado_adicional']."', '".$campos[$i]['prazo_pgto_ddl']."', 
            '".$campos[$i]['desc_vista']."', '".$campos[$i]['desc_sgd']."', '".$campos[$i]['ipi']."', 
            '".$campos[$i]['icms']."', '".$campos[$i]['reducao']."', '".$campos[$i]['iva']."', 
            '".$campos[$i]['lote_minimo_reais']."', '".$campos[$i]['forma_compra']."', 
            '".$campos[$i]['tp_moeda']."', '".$campos[$i]['preco_faturado_export']."', 
            '".$campos[$i]['preco_faturado_export_adicional']."', '".$campos[$i]['valor_moeda_compra']."', 
            '".$campos[$i]['condicao_padrao']."', '".$campos[$i]['fator_margem_lucro_pa']."', 
            '".$campos[$i]['valor_moeda_custo']."', '".date('Y-m-d H:i:s')."', 
            '".$campos[$i]['lote_minimo_pa_rev']."', '1') ";
    echo $sql.';<br/>';
}
echo 'TOTAL DE REGISTROS: '.$linhas;
?>