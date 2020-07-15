<?
require('../../lib/segurancas.php');

//Foi quando ele começou a trabalhar conosco como Representante +/- 01/07/2014 ...

/*
//Parte de Orçamento ...
$sql = "SELECT id_orcamento_venda_item, GREATEST(margem_lucro, margem_lucro_estimada) AS margem_lucro_utilizar 
        FROM `orcamentos_vendas_itens` 
        WHERE `id_orcamento_venda` IN (130856, 169606, 166159, 170095, 170474, 170488, 170507, 170520, 170523, 
                                        170938, 171024, 171328, 171332, 171696, 959, 23744, 60320, 111461, 
                                        112606, 112629, 168655, 169097, 169663, 170146, 170284, 170560, 171141, 
                                        171616, 171977, 165356, 168860, 169682, 170322, 170331, 170404, 170776, 
                                        170843, 171182, 171724) ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    //Busco a Comissão do Autonomo de acordo com a Margem de Lucro, entrando na Tabela ...
    $sql = "SELECT comissao_autonomo 
            FROM `novas_comissoes_margens_lucros` 
            WHERE `margem_lucro` <= '".$campos[$i]['margem_lucro_utilizar']."' ORDER BY `comissao_autonomo` DESC LIMIT 1 ";
    $campos_comissao = bancos::sql($sql);
    if(count($campos_comissao) <= 0) {//Se eu não achar nada, eu pego a 1ª que estiver cadastrada na Tabela ...
        $sql = "SELECT comissao_autonomo 
                FROM `novas_comissoes_margens_lucros` 
                ORDER BY `margem_lucro` LIMIT 1 ";
        $campos_comissao = bancos::sql($sql);
        if(count($campos_comissao) <= 0) return 0;//Não encontrei a margem de comissão ...
    }
    $sql = "UPDATE `orcamentos_vendas_itens` SET `comissao_new` = '".$campos_comissao[0]['comissao_autonomo']."' WHERE `id_orcamento_venda_item` = '".$campos[$i]['id_orcamento_venda_item']."' LIMIT 1 ";
    bancos::sql($sql);
}*/

//Parte de Pedido ...
/*$sql = "SELECT id_pedido_venda_item, GREATEST(margem_lucro, margem_lucro_estimada) AS margem_lucro_utilizar 
        FROM `pedidos_vendas_itens` 
        WHERE `id_pedido_venda` IN (100354, 100476, 100632, 100884, 100897, 93812, 101129, 101204, 101217, 
                                    101230, 101258, 101304, 101306, 101601, 101340, 101481, 101487, 101310, 
                                    101296, 101668, 101696, 101797, 101899, 101939, 101972, 102056) ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    //Busco a Comissão do Autonomo de acordo com a Margem de Lucro, entrando na Tabela ...
    $sql = "SELECT comissao_autonomo 
            FROM `novas_comissoes_margens_lucros` 
            WHERE `margem_lucro` <= '".$campos[$i]['margem_lucro_utilizar']."' ORDER BY `comissao_autonomo` DESC LIMIT 1 ";
    $campos_comissao = bancos::sql($sql);
    if(count($campos_comissao) <= 0) {//Se eu não achar nada, eu pego a 1ª que estiver cadastrada na Tabela ...
        $sql = "SELECT comissao_autonomo 
                FROM `novas_comissoes_margens_lucros` 
                ORDER BY `margem_lucro` LIMIT 1 ";
        $campos_comissao = bancos::sql($sql);
        if(count($campos_comissao) <= 0) return 0;//Não encontrei a margem de comissão ...
    }
    $sql = "UPDATE `pedidos_vendas_itens` SET `comissao_new` = '".$campos_comissao[0]['comissao_autonomo']."' WHERE `id_pedido_venda_item` = '".$campos[$i]['id_pedido_venda_item']."' LIMIT 1 ";
    bancos::sql($sql);
}*/

//Parte de Nota Fiscal ...
$sql = "SELECT nfsi.`id_nfs_item`, pvi.`comissao_new` 
        FROM `nfs_itens` nfsi 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = nfsi.`id_pedido_venda_item` 
        WHERE nfsi.`id_representante` = '128' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $sql = "UPDATE `nfs_itens` SET `comissao_new` = '".$campos[$i]['comissao_new']."' WHERE `id_nfs_item` = '".$campos[$i]['id_nfs_item']."' LIMIT 1 ";
    echo $sql.'<br/>';
    bancos::sql($sql);
}
?>