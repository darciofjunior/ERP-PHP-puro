<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');
require('../../../../lib/data.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro do Custos ...
require('../../../../lib/intermodular.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Vendas ...
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/pedidos/itens/consultar.php', '../../../../');

//Aqui eu trago todos os itens em Aberto do "id_pedido_venda" passado por parâmetro ...
$sql = "SELECT ovi.`id_orcamento_venda`, pvi.`id_pedido_venda_item`, pvi.`id_orcamento_venda_item` 
        FROM `pedidos_vendas_itens` pvi 
        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
        WHERE pvi.`id_pedido_venda` = '$_GET[id_pedido_venda]' 
        AND pvi.`status` = '0' ";
$campos = bancos::sql($sql);
$linhas = count($campos);

for($i = 0; $i < $linhas; $i++) {
    /*Tiro a Marcação de Queima de todos os Itens do Orçamento que estão em Queima de Estoque - Zero o Desc Extra e Acréscimo 
    porque esses 2 campos interferem no Pço Líquido do Item ...*/
    echo $sql = "UPDATE `orcamentos_vendas_itens` SET `queima_estoque` = 'N', `desc_extra` = '0', `acrescimo_extra` = '0' WHERE `id_orcamento_venda_item` = '".$campos[$i]['id_orcamento_venda_item']."' AND `queima_estoque` = 'S' ";
    bancos::sql($sql);
    //Rodo a função abaixo em cima de cada item ...
    vendas::calculo_preco_liq_final_item_orc($campos[$i]['id_orcamento_venda_item'], 'S');
    //Aqui eu atualizo a ML Est do Iem do Orçamento ...
    custos::margem_lucro_estimada($campos[$i]['id_orcamento_venda_item']);
    /*************Rodo a função de Comissão depois de ter gravado a ML Estimada*************/
    vendas::calculo_ml_comissao_item_orc($campos[$i]['id_orcamento_venda'], $campos[$i]['id_orcamento_venda_item']);
    
    //Aqui eu busco o "Novo Preço Líq Fat." que foi atualizado no Item de Orçamento através das funções acima ...
    $sql = "SELECT preco_liq_fat 
            FROM `orcamentos_vendas_itens` 
            WHERE `id_orcamento_venda_item` = '".$campos[$i]['id_orcamento_venda_item']."' LIMIT 1 ";
    $campos_orcamento_venda_item = bancos::sql($sql);
    
    //Atualizo o Item de Pedido de Venda com o Novo "Preço Líq. Fat." que foi atualizado no Item de Orçamento ...
    echo $sql = "UPDATE `pedidos_vendas_itens` SET `preco_liq_fat` = '".$campos_orcamento_venda_item[0]['preco_liq_fat']."' WHERE id_pedido_venda_item = '".$campos[$i]['id_pedido_venda_item']."' LIMIT 1 ";
    bancos::sql($sql);
}
?>
<Script Language = 'JavaScript'>
    alert('ITEM(NS) EM ABERTO DE PEDIDO ATUALIZADO(S) COM SUCESSO !')
    window.close()
</Script>