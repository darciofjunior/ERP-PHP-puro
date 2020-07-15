<?
require('../../lib/segurancas.php');

$sql = "SELECT fpi.id_fornecedor, fpi.id_produto_insumo, fpi.preco, fpi.preco_exportacao, fpi.preco_faturado, fpi.preco_faturado_adicional, fpi.prazo_pgto_ddl, fpi.desc_vista, fpi.desc_sgd, fpi.ipi, fpi.icms, fpi.reducao, fpi.iva, fpi.lote_minimo_reais, fpi.forma_compra, fpi.tp_moeda, fpi.preco_faturado_export, fpi.preco_faturado_export_adicional, fpi.valor_moeda_compra, fpi.condicao_padrao, fpi.fator_margem_lucro_pa, fpi.valor_moeda_custo, fpi.data_sys, fpi.lote_minimo_pa_rev, fpi.ativo 
        FROM `fornecedores_x_prod_insumos` fpi
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = fpi.`id_produto_insumo`
        AND pa.referencia <> 'ESP'
        WHERE fpi.`id_fornecedor` = '697'
        AND fpi.`ativo` = '1' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    echo $sql = "INSERT INTO `fornecedores_x_prod_insumos` (`id_fornecedor`, `id_produto_insumo`, `preco`, `preco_exportacao`, `preco_faturado`, `preco_faturado_adicional`, `prazo_pgto_ddl`, `desc_vista`, `desc_sgd`, `ipi`, `icms`, `reducao`, `iva`, `lote_minimo_reais`, `forma_compra`, `tp_moeda`, `preco_faturado_export`, `preco_faturado_export_adicional`, `valor_moeda_compra`, `condicao_padrao`, `fator_margem_lucro_pa`, `valor_moeda_custo`, `data_sys`, `lote_minimo_pa_rev`, `ativo`) VALUES ('2196', '".$campos[$i]['id_produto_insumo']."', '".$campos[$i]['preco']."', '".$campos[$i]['preco_exportacao']."', '".$campos[$i]['preco_faturado']."', '".$campos[$i]['preco_faturado_adicional']."', '".$campos[$i]['prazo_pgto_ddl']."', '".$campos[$i]['desc_vista']."', '".$campos[$i]['desc_sgd']."', '".$campos[$i]['ipi']."', '".$campos[$i]['icms']."', '".$campos[$i]['reducao']."', '".$campos[$i]['iva']."', '".$campos[$i]['lote_minimo_reais']."', '".$campos[$i]['forma_compra']."', '".$campos[$i]['tp_moeda']."', '".$campos[$i]['preco_faturado_export']."', '".$campos[$i]['preco_faturado_export_adicional']."', '".$campos[$i]['valor_moeda_compra']."', '".$campos[$i]['condicao_padrao']."', '".$campos[$i]['fator_margem_lucro_pa']."', '".$campos[$i]['valor_moeda_custo']."', '".date('Y-m-d H:i:s')."', '".$campos[$i]['lote_minimo_pa_rev']."', '1'); ";
    echo '<br/>';
}
?>