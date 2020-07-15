<?
require('../../lib/segurancas.php');
require('../../lib/vendas.php');
if(empty($indice)) $indice = 0;

//Todos os Pedidos de Vendas que estão sem Comissão Média ...
$sql = "SELECT COUNT(pv.id_pedido_venda) AS total_registro 
        FROM `pedidos_vendas` pv 
        WHERE `comissao_media` = '0' ";
$campos_total   = bancos::sql($sql);
$total_registro = $campos_total[0]['total_registro'];

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

//Busca o último Pedido que está cadastrado no Sistema sem Comissão Média ...
$sql = "SELECT id_pedido_venda 
        FROM `pedidos_vendas` 
        WHERE `comissao_media` = '0' ORDER BY data_emissao DESC ";
$campos = bancos::sql($sql, $indice, 1);

//Aqui eu busco todos os Itens do Pedido corrente ...
$sql = "SELECT id_pedido_venda_item, qtde, comissao_new, preco_liq_final 
        FROM `pedidos_vendas_itens` 
        WHERE `id_pedido_venda` = '".$campos[0]['id_pedido_venda']."' ";
$campos_itens = bancos::sql($sql);
$linhas_itens = count($campos_itens);
for($i = 0; $i < $linhas_itens; $i++) {
    $preco_total_lote = $campos_itens[$i]['preco_liq_final'] * $campos_itens[$i]['qtde'];
    $valor_produto_todos_itens+= round($preco_total_lote, 2);
    
    $comissao_item = vendas::comissao_representante_reais($preco_total_lote, $campos_itens[$i]['comissao_new']);
    $comissao_itens_total+= $comissao_item;
}

if($valor_produto_todos_itens > 0) {
    $comissao_media = round(($comissao_itens_total / $valor_produto_todos_itens) * 100, 1);
}else {
    $comissao_media = round($comissao_itens_total * 100, 1);
}

//Atualizo o Pedido com a Comissão Média que foi calculada acima ...
echo $sql = "UPDATE `pedidos_vendas` SET `comissao_media` = '".round($comissao_media, 2)."' WHERE `id_pedido_venda` = '".$campos[0]['id_pedido_venda']."' LIMIT 1 ";
bancos::sql($sql);

echo '<br/><br/>Total de Registro(s): '.($indice.'/'.$total_registro);
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_comissao_media_pedido_de_vendas.php?indice=<?=++$indice;?>'
</Script>