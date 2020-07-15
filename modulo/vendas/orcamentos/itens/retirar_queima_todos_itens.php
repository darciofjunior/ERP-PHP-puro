<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');//Essa biblioteca � chamada aqui porque a mesma � utilizada dentro da Vendas ...
require('../../../../lib/intermodular.php');//Essa biblioteca � chamada aqui porque a mesma � utilizada dentro da Vendas ...
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../../');

//Aqui eu busco todos os Itens do id_orcamento_venda que est�o em queima passado por par�metro ...
$sql = "SELECT id_orcamento_venda_item 
        FROM `orcamentos_vendas_itens` 
        WHERE `id_orcamento_venda` = '$_GET[id_orcamento_venda]' 
        AND `queima_estoque` = 'S' ";
$campos_itens = bancos::sql($sql);
$linhas_itens = count($campos_itens);
for($i = 0; $i < $linhas_itens; $i++) {
    //Tiro a Marca��o de Queima de todos os Itens do Or�amento - Zero o Desc Extra e Acr�scimo porque esses 2 campos interferem no P�o L�quido do Item
    $sql = "UPDATE `orcamentos_vendas_itens` SET `queima_estoque` = 'N', `desc_extra` = '0', `acrescimo_extra` = '0' WHERE `id_orcamento_venda_item` = '".$campos_itens[$i]['id_orcamento_venda_item']."' ";
    bancos::sql($sql);
/*******************************************************************************************************/
    vendas::calculo_preco_liq_final_item_orc($campos_itens[$i]['id_orcamento_venda_item']);
//Aqui eu atualizo a ML Est do Iem do Or�amento ...
    custos::margem_lucro_estimada($campos_itens[$i]['id_orcamento_venda_item']);
/*************Rodo a fun��o de Comiss�o depois de ter gravado a ML Estimada*************/
    vendas::calculo_ml_comissao_item_orc($_GET[id_orcamento_venda], $campos_itens[$i]['id_orcamento_venda_item']);
}
?>
<Script Language = 'JavaScript'>
    alert('MARCA��O DE EXCESSO DE ESTOQUE RETIRADA DE TODO(S) O(S) ITEM(NS) COM SUCESSO !')
    window.location = '/erp/albafer/modulo/vendas/orcamentos/itens/itens.php?id_orcamento_venda=<?=$_GET[id_orcamento_venda];?>'
</Script>