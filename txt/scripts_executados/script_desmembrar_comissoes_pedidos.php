<?
require('../../lib/segurancas.php');
require('../../lib/calculos.php');
require('../../lib/custos.php');
require('../../lib/faturamentos.php');
require('../../lib/intermodular.php');
require('../../lib/vendas.php');
session_start('funcionarios');

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

//Busco todas as Contas Automáticas que não são Contratos ...
$sql = "SELECT COUNT(`id_pedido_venda`) AS total_registro 
        FROM `pedidos_vendas` ";
$campos_total = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'].'<br>';

if($total_registro == $indice) exit('SCRIPT EXECUTADO COM SUCESSO !');

//Trago todas as Notas Fiscais dentro do Período ...
$sql = "SELECT `id_pedido_venda` 
        FROM `pedidos_vendas` 
        ORDER BY `id_pedido_venda` DESC ";
$campos = bancos::sql($sql, $indice, 1);

$sql = "SELECT `qtde`, `preco_liq_final`, `comissao_new`, `comissao_extra` 
        FROM `pedidos_vendas_itens` 
        WHERE `id_pedido_venda` = '".$campos[0]['id_pedido_venda']."' ";
$campos_itens   = bancos::sql($sql);
$linhas_itens   = count($campos_itens);
for($i = 0; $i < $linhas_itens; $i++) {
    $preco_total_lote = $campos_itens[$i]['preco_liq_final'] * $campos_itens[$i]['qtde'];

    $comissao_por_item_rs = vendas::comissao_representante_reais($preco_total_lote, $campos_itens[$i]['comissao_new']);
    $total_comissoes_dos_itens_rs+= $comissao_por_item_rs;

    $comissao_extra_por_item_rs = vendas::comissao_representante_reais($preco_total_lote, $campos_itens[$i]['comissao_extra']);
    $total_comissoes_extras_dos_itens_rs+= $comissao_extra_por_item_rs;
}

$calculo_total_impostos = calculos::calculo_impostos(0, $campos[0]['id_pedido_venda'], 'PV');

if($calculo_total_impostos['valor_total_produtos'] > 0) {
    $comissao_media         = round(($total_comissoes_dos_itens_rs / $calculo_total_impostos['valor_total_produtos']) * 100, 2);
    $comissao_media_extra   = round(($total_comissoes_extras_dos_itens_rs / $calculo_total_impostos['valor_total_produtos']) * 100, 2);
}else {
    $comissao_media         = round($total_comissoes_dos_itens_rs * 100, 2);
    $comissao_media_extra   = round($total_comissoes_extras_dos_itens_rs * 100, 2);
}

$sql = "UPDATE `pedidos_vendas` SET `comissao_media` = '$comissao_media', `comissao_media_extra` = '$comissao_media_extra' WHERE `id_pedido_venda` = '".$campos[0]['id_pedido_venda']."' LIMIT 1 ";
echo $sql;
bancos::sql($sql);
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_desmembrar_comissoes_pedidos.php?indice=<?=++$indice;?>'
</Script>