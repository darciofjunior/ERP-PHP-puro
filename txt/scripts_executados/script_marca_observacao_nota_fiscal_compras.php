<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

$sql = "SELECT COUNT(DISTINCT(id_nfe_historico)) AS total_registro 
        FROM `nfe_historicos` 
        WHERE `marca` = '' ";
$campos_total = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'].'<br>';

if($total_registro == $indice) exit('SCRIPT EXECUTADO COM SUCESSO !');

$sql = "SELECT id_nfe_historico, id_item_pedido 
        FROM `nfe_historicos` 
        WHERE `marca` = '' ";
$campos = bancos::sql($sql, $indice, 1);

//Aqui eu trago a marca do Item Pedido que gerou o Item de Nota Fiscal, se é que existir claro ...
$sql = "SELECT marca 
        FROM `itens_pedidos` 
        WHERE `id_item_pedido` = '".$campos[0]['id_item_pedido']."' 
        AND `marca` <> '' LIMIT 1 ";
$campos_itens = bancos::sql($sql);
if(count($campos_itens[0]['marca']) == 1) {//Nesse caso existe uma marca Observação p/ o Pedido ...
    //Atualizo o Item de Nota Fiscal com a "Marca / Observação" que foi encontrada na tabela de Item de Pedido ...
    $sql = "UPDATE `nfe_historicos` SET `marca` = '".$campos_itens[0]['marca']."' WHERE `id_nfe_historico` = '".$campos[0]['id_nfe_historico']."' LIMIT 1 ";
    bancos::sql($sql);
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_marca_observacao_nota_fiscal_compras.php?indice=<?=++$indice;?>'
</Script>