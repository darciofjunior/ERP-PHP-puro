<?
require('../../lib/segurancas.php');

$sql = "SELECT ovi.id_orcamento_venda_item 
		FROM `orcamentos_vendas` ov 
		INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_orcamento_venda = ov.id_orcamento_venda 
		WHERE ov.`comissao_extra_queima_estoque` = 'S' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
	$sql = "UPDATE `orcamentos_vendas_itens` SET `queima_estoque` = 'S' WHERE `id_orcamento_venda_item` = '".$campos[$i]['id_orcamento_venda_item']."' LIMIT 1 ";
	bancos::sql($sql);
}
?>