<?
require('../../lib/segurancas.php');

//Aqui eu zero todos os Descontos de todos os Clientes por Divisão ...
$sql = "UPDATE `clientes_vs_representantes` SET `desconto_cliente` = '0' ";
bancos::sql($sql);//Limpo todos os descontos dos clientes para calc novamente ...

//Busco o total comprado do Cliente por Divisão no último 1 ano ...
$sql = "SELECT SUM(nfsi.qtde * nfsi.valor_unitario) AS volume_compras_ano_atual, ged.id_empresa_divisao, nfs.id_cliente 
        FROM `nfs` 
        INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente AND c.ativo = '1' 
        INNER JOIN `ufs` ON ufs.id_uf = c.id_uf 
        INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda_item = nfsi.id_pedido_venda_item 
        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pvi.id_produto_acabado 
        INNER JOIN gpas_vs_emps_divs ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
        WHERE YEAR(nfs.data_emissao) BETWEEN '2011-01-01' AND '2011-12-31' 
        GROUP BY nfs.id_cliente, ged.id_empresa_divisao ORDER BY nfs.id_cliente, ged.id_empresa_divisao ";
$campos = bancos::sql($sql);
$linhas	= count($campos);
for($i = 0; $i < $linhas; $i++) {//add +1 no loop para o ultimo cliente nao ficar sem passar pela lógica
    $vetor_volume_compras[$campos[$i]['id_cliente']]+= $campos[$i]['volume_compras_ano_atual'];
    $vetor_cliente[] = $campos[$i]['id_cliente'];
    //Busco o Desconto do Cliente na Tabela Faixa de Desconto do Cliente por Divisão (Semestral) ...
    $sql = "SELECT desconto_cliente 
            FROM `descontos_clientes` 
            WHERE `valor_semestral`> '".$campos[$i]['volume_compras_ano_atual']."' 
            AND `tabela_analise` = '1' ORDER BY desconto_cliente LIMIT 1 ";
    $campos_desconto_divisao = bancos::sql($sql);
    $desconto_cliente = (count($campos_desconto_divisao) > 0) ? $campos_desconto_divisao[0]['desconto_cliente'] : 0;
    //Atualiza todos os descontos do Cliente por Divisão ...
    $sql = "UPDATE `clientes_vs_representantes` SET `desconto_cliente_old` = `desconto_cliente`, `desconto_cliente` = '$desconto_cliente' WHERE `id_cliente` = '".$campos[$i]['id_cliente']."' AND `id_empresa_divisao` = ".$campos[$i]['id_empresa_divisao']." ";
    bancos::sql($sql);
}

$vetor_cliente = array_unique($vetor_cliente);//Retira os valores duplicados do vetor ...

foreach($vetor_cliente as $id_cliente) {
    //Busco o Desconto do Cliente na Tabela Faixa de Desconto do Cliente por Grupo (Semestral) ...
    $sql = "SELECT desconto_cliente 
                    FROM `descontos_clientes` 
                    WHERE `valor_semestral`> '".$vetor_volume_compras[$id_cliente]."' 
                    AND `tabela_analise` = '0' ORDER BY desconto_cliente LIMIT 1 ";
    $campos_desconto_grupo = bancos::sql($sql);
    $desconto_cliente = (count($campos_desconto_grupo) > 0) ? $campos_desconto_grupo[0]['desconto_cliente'] : 0;
    //Atualiza todos os descontos do Cliente, desde que seja o novo desconto seja maior do q o atual que ele tem ...
    $sql = "UPDATE `clientes_vs_representantes` SET `desconto_cliente_old` = `desconto_cliente`, `desconto_cliente` = '$desconto_cliente' WHERE `id_cliente` = '$id_cliente' AND `desconto_cliente` < '$desconto_cliente' ";
    bancos::sql($sql);
}
echo 'SCRIPT EXECUTADO COM SUCESSO !';
?>