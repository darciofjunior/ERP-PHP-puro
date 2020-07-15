<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../../');

//Aqui eu busco dados do $id_pedido passado por parâmetro p/ gerar a NF de Entrada mais abaixo ...
$sql = "SELECT * 
        FROM `pedidos` 
        WHERE `id_pedido` = '$_GET[id_pedido]' LIMIT 1 ";
$campos     = bancos::sql($sql);
$tipo_nota  = ($campos[0]['id_empresa'] == 1 || $campos[0]['id_empresa'] == 2) ? 1 : 2;

/*Agora já com os dados de Pedido na mão, a partir daqui eu gero a NF de Entrada ...

Observação: o campo "id_tipo_pagamento_recebimento" -> Vem sugerido como Dinheiro porque este não pode 
ficar "NULL", senão o sistema não gera um registro para uma Nota Fiscal de Entrada ...*/
$sql = "INSERT INTO `nfe` (`id_nfe`, `id_empresa`, `id_fornecedor`, `id_tipo_pagamento_recebimento`, `id_tipo_moeda`, `tipo`) VALUES (NULL, '".$campos[0]['id_empresa']."', '".$campos[0]['id_fornecedor']."', '1', '".$campos[0]['id_tipo_moeda']."', '$tipo_nota') ";
bancos::sql($sql);
$id_nfe = bancos::id_registro();

/*Aqui eu busco dados de financimento do $id_pedido passado por parâmetro, para gerar a Nota Fiscal já 
com a Qtde de Parcelas e Vencimentos corretos ...

Observação: Gero o valor para cada parcela como sendo R$ 0,00 porque o usuário ainda fará a inclusão de itens 
de Nota Fiscal ...
$sql = "SELECT `dias` 
        FROM `pedidos_financiamentos` 
        WHERE `id_pedido` = '$_GET[id_pedido]' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $data_financiamento = data::adicionar_data_hora(date('d/m/Y'), $campos[$i]['dias']);
    $data_financiamento = data::datatodate($data_financiamento, '-');
    $insert_extendido.= " (NULL, '$id_nfe', '".$campos[$i]['dias']."', '$data_financiamento', '0'), ";
}
$insert_extendido = substr($insert_extendido, 0, strlen($insert_extendido) - 2);

//Gravando os Vencimentos de Nota Fiscal ...
$sql = "INSERT INTO `nfe_financiamentos` (`id_nfe_financiamento`, `id_nfe`, `dias`, `data`, `valor_parcela_nf`) VALUES 
        $insert_extendido ";
bancos::sql($sql);*/
?>
<Script Language = 'JavaScript'>
    alert('NOTA FISCAL DE ENTRADA GERADA COM SUCESSO !')
    opener.parent.location = '../nota_entrada/itens/index.php?id_nfe=<?=$id_nfe;?>&clique_automatico_incluir_itens=S'
    window.close()
</Script>