<?
require('../../lib/segurancas.php');
require('../../lib/intermodular.php');
require('../../lib/calculos.php');
session_start('funcionarios');

echo $_SESSION['id_funcionario'].'<br>';

//Todos os Pedidos do Sistema...
$sql = "SELECT COUNT(DISTINCT(pv.`id_pedido_venda`)) AS total_registro 
        FROM `pedidos_vendas` pv 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`preco_liq_final` > '0' 
        WHERE pv.`valor_ped` = '0' ";
$campos = bancos::sql($sql);
echo 'Total de Pedido(s) p/ Acertar => '.$campos[0]['total_registro'].'<br>';

//Aqui é para reassumir o Tempo Logado ...
$_SESSION['ultimo_acesso'] = date('Y-m-d H:i:s');
echo 'Último Acesso às '.$_SESSION['ultimo_acesso'].'<br>';

//Todos os Pedidos do Sistema...
$sql = "SELECT c.`id_uf`, pv.`id_pedido_venda`, pv.`id_cliente`, pv.`id_empresa`, pv.`finalidade` 
        FROM `pedidos_vendas` pv 
        INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`preco_liq_final` > '0' 
        WHERE pv.`valor_ped` = '0' ORDER BY pv.`id_pedido_venda` DESC LIMIT 1 ";
$campos = bancos::sql($sql);

//TOdos os itens daquele pedido ...
$sql = "SELECT pvi.id_pedido_venda_item, pvi.id_produto_acabado, pvi.qtde, pvi.preco_liq_final, ov.artigo_isencao 
        FROM `pedidos_vendas_itens` pvi 
        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_orcamento_venda_item = pvi.id_orcamento_venda_item 
        INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda 
        WHERE pvi.`id_pedido_venda` = '".$campos[0]['id_pedido_venda']."' ";
$campos_preco_liq = bancos::sql($sql);
$linhas_preco_liq = count($campos_preco_liq);
for($j = 0; $j < $linhas_preco_liq; $j++) {
    $valor_impostos = calculos::calculo_impostos($campos_preco_liq[$j]['id_pedido_venda_item'], $campos[0]['id_pedido_venda'], 'PV');
    $valor_icms_st_todos_itens+= round($valor_impostos['valor_iva_item'], 2);
    
    $dados_produto = intermodular::dados_impostos_pa($campos_preco_liq[$j]['id_produto_acabado'], $campos[0]['id_uf'], $campos[0]['id_cliente'], $campos[0]['id_empresa'], $campos[0]['finalidade']);
    $ipi = $dados_produto['ipi'];
    $valor_ipi_todos_itens+= round(($campos_preco_liq[$j]['qtde'] * $campos_preco_liq[$j]['preco_liq_final']) * ($ipi / 100), 2);

    $valor_produtos_todos_itens+= $campos_preco_liq[$j]['qtde'] * $campos_preco_liq[$j]['preco_liq_final'];
}
echo $sql = "UPDATE pedidos_vendas set `valor_ped` = '".($valor_produtos_todos_itens + $valor_ipi_todos_itens + $valor_icms_st_todos_itens)."' WHERE `id_pedido_venda` = '".$campos[0]['id_pedido_venda']."' LIMIT 1 ";
bancos::sql($sql);
?>
<Script Language = "JavaScript">
    window.location = 'script_atualizar_valor_pedidos.php'
</Script>