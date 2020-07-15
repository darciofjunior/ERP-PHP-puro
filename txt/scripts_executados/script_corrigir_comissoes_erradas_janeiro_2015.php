<?
require('../../lib/segurancas.php');
require('../../lib/custos.php');
require('../../lib/data.php');
require('../../lib/faturamentos.php');
require('../../lib/intermodular.php');
require('../../lib/vendas.php');
session_start('funcionarios');

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

//Busco todas as Contas Automáticas que não são Contratos ...
$sql = "SELECT COUNT(ovi.`id_orcamento_venda_item`) AS total_registro 
        FROM `orcamentos_vendas_itens` ovi 
        WHERE SUBSTRING(ovi.`data_sys`, 1, 10) BETWEEN '2015-01-12' AND '2015-01-28' ";
$campos_total = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'].'<br>';

if($total_registro == $indice) exit('SCRIPT EXECUTADO COM SUCESSO !');

//Trago todas as Comissões dentro do Período 12/01 à 28/01 ...
$sql = "SELECT `id_orcamento_venda`, `id_orcamento_venda_item`, `preco_liq_final`, 
        `margem_lucro`, `margem_lucro_estimada` 
        FROM `orcamentos_vendas_itens` 
        WHERE SUBSTRING(`data_sys`, 1, 10) BETWEEN '2015-01-12' AND '2015-01-28' ORDER BY `data_sys` ";
$campos = bancos::sql($sql, $indice, 1);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $sql = "SELECT c.`id_uf`
            FROM `orcamentos_vendas` ov 
            INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
            WHERE ov.`id_orcamento_venda` = '".$campos[$i]['id_orcamento_venda']."' LIMIT 1 ";
    $campos_orcamento = bancos::sql($sql);

    $tx_financeira  = custos::calculo_taxa_financeira($campos[$i]['id_orcamento_venda']);
    $margem         = custos::margem_lucro($campos[$i]['id_orcamento_venda_item'], $tx_financeira, $campos_orcamento[0]['id_uf'], $campos[$i]['preco_liq_final']);
    $margem_inst    = str_replace(',', '.', $margem[0]);
    
    if($campos[$i]['margem_lucro_estimada'] < $margem_inst) {
        if(($margem_inst - $campos[$i]['margem_lucro']) > 5) {
            $comissao_new   = vendas::nova_comissao_representante($campos[$i]['id_orcamento_venda_item'], $campos[$i]['preco_liq_final']);
            
            $sql = "UPDATE `orcamentos_vendas_itens` SET `comissao_new` = '$comissao_new', `margem_lucro` = '$margem_inst' WHERE `id_orcamento_venda_item` = '".$campos[$i]['id_orcamento_venda_item']."' LIMIT 1 ";
            bancos::sql($sql);
            echo $sql.'<br/>';
            //Verifico se esse item de Orçamento se encontra em Pedido ...
            $sql = "SELECT id_pedido_venda_item 
                    FROM `pedidos_vendas_itens` 
                    WHERE `id_orcamento_venda_item` = '".$campos[$i]['id_orcamento_venda_item']."' ";
            $campos_pedidos_vendas = bancos::sql($sql);
            $linhas_pedidos_vendas = count($campos_pedidos_vendas);
            for($j = 0; $j < $linhas_pedidos_vendas; $j++) {
                $sql = "UPDATE `pedidos_vendas_itens` SET `comissao_new` = '$comissao_new', `margem_lucro` = '$margem_inst' WHERE `id_pedido_venda_item` = '".$campos_pedidos_vendas[$j]['id_pedido_venda_item']."' LIMIT 1 ";
                bancos::sql($sql);
                echo $sql.'<br/>';
                //Verifico se esse item de Pedido se encontra em NF ...
                $sql = "SELECT id_nfs_item 
                        FROM `nfs_itens` 
                        WHERE `id_pedido_venda_item` = '".$campos_pedidos_vendas[$j]['id_pedido_venda_item']."' ";
                $campos_nfs = bancos::sql($sql);
                $linhas_nfs = count($campos_nfs);
                for($k = 0; $k < $linhas_nfs; $k++) {
                    $sql = "UPDATE `nfs_itens` SET `comissao_new` = '$comissao_new' WHERE `id_nfs_item` = '".$campos_nfs[$k]['id_nfs_item']."' LIMIT 1 ";
                    bancos::sql($sql);
                    echo $sql.'<br/>';
                }
            }
        }
    }
    echo '<br/> => '.$campos[$i]['id_orcamento_venda'].'|'.$campos[$i]['id_orcamento_venda_item'].'|Inst='.$margem_inst.'|Est='.$campos[$i]['margem_lucro_estimada'].'|Grav='.$campos[$i]['margem_lucro'];
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_corrigir_comissoes_erradas_janeiro_2015.php?indice=<?=++$indice;?>'
</Script>