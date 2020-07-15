<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Vendas ...
require('../../../../lib/intermodular.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Vendas ...
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../../');

//Aqui eu busco todos os Itens do id_orcamento_venda que estão em queima passado por parâmetro ...
$sql = "SELECT id_orcamento_venda_item 
        FROM `orcamentos_vendas_itens` 
        WHERE `id_orcamento_venda` = '$_GET[id_orcamento_venda]' 
        AND `queima_estoque` = 'S' ";
$campos_itens = bancos::sql($sql);
$linhas_itens = count($campos_itens);
for($i = 0; $i < $linhas_itens; $i++) {
    //Tiro a Marcação de Queima de todos os Itens do Orçamento - Zero o Desc Extra e Acréscimo porque esses 2 campos interferem no Pço Líquido do Item
    $sql = "UPDATE `orcamentos_vendas_itens` SET `queima_estoque` = 'N', `desc_extra` = '0', `acrescimo_extra` = '0' WHERE `id_orcamento_venda_item` = '".$campos_itens[$i]['id_orcamento_venda_item']."' ";
    bancos::sql($sql);
/*******************************************************************************************************/
    vendas::calculo_preco_liq_final_item_orc($campos_itens[$i]['id_orcamento_venda_item']);
//Aqui eu atualizo a ML Est do Iem do Orçamento ...
    custos::margem_lucro_estimada($campos_itens[$i]['id_orcamento_venda_item']);
/*************Rodo a função de Comissão depois de ter gravado a ML Estimada*************/
    vendas::calculo_ml_comissao_item_orc($_GET[id_orcamento_venda], $campos_itens[$i]['id_orcamento_venda_item']);
}
?>
<Script Language = 'JavaScript'>
    alert('MARCAÇÃO DE EXCESSO DE ESTOQUE RETIRADA DE TODO(S) O(S) ITEM(NS) COM SUCESSO !')
    window.location = '/erp/albafer/modulo/vendas/orcamentos/itens/itens.php?id_orcamento_venda=<?=$_GET[id_orcamento_venda];?>'
</Script>