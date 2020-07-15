<?
require('../../lib/segurancas.php');
require('../../lib/data.php');

function percentagem_itens_nf($id_nfe) {
    //Quantidade de Itens da NF ...
    $sql = "SELECT SUM(qtde_entregue * valor_entregue) AS valor_total 
            FROM `nfe_historicos` 
            WHERE `id_nfe` = '$id_nfe' ";
    $campos_total   = bancos::sql($sql);
    $valor_total    = $campos_total[0]['valor_total'];

    $sql = "SELECT SUM(nfeh.qtde_entregue * nfeh.valor_entregue) AS valor_total_por_grupo, g.id_grupo 
            FROM nfe_historicos nfeh 
            INNER JOIN itens_pedidos ip ON ip.id_item_pedido =  nfeh.id_item_pedido 
            INNER JOIN produtos_insumos pi ON pi.id_produto_insumo = ip.id_produto_insumo 
            INNER JOIN grupos g ON g.id_grupo = pi.id_grupo 
            WHERE nfeh.id_nfe = '$id_nfe' 
            GROUP BY g.id_grupo ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);

    //Formula para descobrir a percentagem de cada grupo de item ...
    for($i = 0; $i < $linhas; $i++) {
        //Fazer a percentagem de cada grupo ...
        $percentagem_item 	= round((($campos[$i]['valor_total_por_grupo'] * 100) / $valor_total), 1);
        $id_grupos.= 		$campos[$i]['id_grupo'].', ';
        $percentagens.= 	$percentagem_item.', ';
    }
    $id_grupos 		= substr($id_grupos, 0, strlen($id_grupos) - 2);
    $percentagens 	= substr($percentagens, 0, strlen($percentagens) - 2);
    return array('id_grupos' => $id_grupos, 'percentagens' => $percentagens);
}

if(empty($indice)) $indice = 0;

$sql = "SELECT COUNT(id_conta_apagar) AS total_registro 
        FROM contas_apagares 
        WHERE id_nfe > '0' 
        AND id_grupo = '' ";
$campos_total = bancos::sql($sql);
$total_registro = $campos_total[0]['total_registro'];

if($total_registro == $indice) {//P/ não ficar em loop infinito ...
    exit;
}

$sql = "SELECT id_conta_apagar, id_nfe 
        FROM contas_apagares 
        WHERE id_nfe > '0' 
        AND id_grupo = '' ";
$campos_apagares = bancos::sql($sql, $indice, 1);
$linhas_apagares = count($campos_apagares);
for($j = 0; $j < $linhas_apagares; $j++) {
    $dados_nfe = percentagem_itens_nf($campos_apagares[$j]['id_nfe']);

    echo $sql = "UPDATE `contas_apagares` SET `id_grupo` = '$dados_nfe[id_grupos]', `perc_uso_produto_financeiro` = '$dados_nfe[percentagens]' WHERE `id_conta_apagar` = '".$campos_apagares[$j]['id_conta_apagar']."' LIMIT 1 ";
    bancos::sql($sql);
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_contas_apagares_nfs.php?indice=<?=++$indice;?>'
</Script>