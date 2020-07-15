<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');//Essa biblioteca � chamada aqui porque a mesma � utilizada dentro da Vendas ...
require('../../../../lib/intermodular.php');//Essa biblioteca � chamada aqui porque a mesma � utilizada dentro da Vendas ...
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../../');

//Se o usu�rio preencheu o Acr�scimo Extra ...
$acrescimo_extra = (!empty($_GET['acrescimo_extra'])) ? str_replace(',', '.', $_GET['acrescimo_extra']) : 0;

//Aqui eu busco todos os itens do Or�amento que foi passado por par�metro, desde que sejam do Tipo PIPA ...
$sql = "SELECT ovi.id_orcamento_venda_item, ovi.preco_liq_fat, ovi.desc_cliente, pa.id_produto_insumo 
        FROM `orcamentos_vendas_itens` ovi 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` AND pa.`id_produto_insumo` > '0' 
        WHERE ovi.`id_orcamento_venda` = '$_GET[id_orcamento_venda]' ";
$campos_itens = bancos::sql($sql);
$linhas_itens = count($campos_itens);
for($i = 0; $i < $linhas_itens; $i++) {
    //Aqui eu busco o Pre�o do Item do Loop na Lista de Compras da JT ...
    $sql = "SELECT preco 
            FROM `fornecedores_x_prod_insumos` 
            WHERE `id_fornecedor` = '2196' 
            AND `id_produto_insumo` = '".$campos_itens[$i]['id_produto_insumo']."' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos_lista   = bancos::sql($sql);
    if(count($campos_lista) == 1) {//Somente se achar o Item na Lista de Pre�o da JT que ir� desenrolar todo o roteiro abaixo ...
        $vetor_valores  = vendas::alt_c($campos_itens[$i]['preco_liq_fat'], $campos_itens[$i]['desc_cliente'], $campos_lista[0]['preco']);
        /*******************************************************************************************/
        //Atualiza o Item de Or�amento com esse Pre�o da Lista de Compras da JT ...
        $sql = "UPDATE `orcamentos_vendas_itens` SET `acrescimo_extra` = '$acrescimo_extra', `desc_extra` = '".$vetor_valores['desconto_extra']."' WHERE `id_orcamento_venda_item` = '".$campos_itens[$i]['id_orcamento_venda_item']."' LIMIT 1 ";
        bancos::sql($sql);
        /*******************************************************************************************************/
        vendas::calculo_preco_liq_final_item_orc($campos_itens[$i]['id_orcamento_venda_item']);
        //Aqui eu atualizo a ML Est do Iem do Or�amento ...
        custos::margem_lucro_estimada($campos_itens[$i]['id_orcamento_venda_item']);
        /*************Rodo a fun��o de Comiss�o depois de ter gravado a ML Estimada*************/
        vendas::calculo_ml_comissao_item_orc($_GET[id_orcamento_venda], $campos_itens[$i]['id_orcamento_venda_item']);
    }
}
?>
<Script Language = 'JavaScript'>
    alert('PRE�OS PELA LISTA DE COMPRAS JT ADAPTADOS COM SUCESSO !')
    parent.window.location = '/erp/albafer/modulo/vendas/orcamentos/itens/itens.php?id_orcamento_venda=<?=$_GET[id_orcamento_venda];?>'
</Script>