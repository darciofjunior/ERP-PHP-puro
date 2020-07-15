<?
require('../../../lib/segurancas.php');
require('../../../lib/calculos.php');
require('../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/faturamento/outras_nfs/itens/alterar_imprimir.php', '../../../');

//Busca de alguns dados do $id_nf_outra passado por parâmetro ...
$sql = "SELECT nfso.`id_cliente`, nfso.`id_empresa`, nfso.`finalidade`, ufs.`id_uf` 
        FROM `nfs_outras` nfso 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfso.`id_cliente` 
        LEFT JOIN `ufs` ON ufs.`id_uf` = c.`id_uf` 
        WHERE nfso.`id_nf_outra` = '$_GET[id_nf_outra]' LIMIT 1 ";
$campos_dados   = bancos::sql($sql);

//Busco todos os itens do id_pedido_venda "Pedido de Venda escolhido" passada por parâmetro ...
$sql = "SELECT `id_pedido_venda_item`, `id_produto_acabado`, `qtde`, `preco_liq_final` 
        FROM `pedidos_vendas_itens` 
        WHERE `id_pedido_venda` = '$_GET[id_pedido_venda]' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    //Busco alguns dados de cadastro do PA do 'id_nfs_item' ...
    $sql = "SELECT `id_unidade`, `origem_mercadoria`, `peso_unitario`, `observacao` 
            FROM `produtos_acabados` 
            WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
    $campos_pas             = bancos::sql($sql);
    $id_unidade             = $campos_pas[0]['id_unidade'];
    $origem_mercadoria      = $campos_pas[0]['origem_mercadoria'];
    $peso_unitario          = $campos_pas[0]['peso_unitario'];
    $observacao             = $campos_pas[0]['observacao'];
    
    //Busco alguns dados de imposto do PA do 'id_nfs_item' ...
    $dados_produto          = intermodular::dados_impostos_pa($campos[$i]['id_produto_acabado'], $campos_dados[0]['id_uf'], $campos_dados[0]['id_cliente'], $campos_dados[0]['id_empresa'], $campos_dados[0]['finalidade']);
    $id_classific_fiscal    = $dados_produto['id_classific_fiscal'];
    $ipi                    = $dados_produto['ipi'];
    $icms                   = $dados_produto['icms'];
    $reducao                = $dados_produto['reducao'];
    $icms_intraestadual     = $dados_produto['icms_intraestadual'];
    $iva                    = $dados_produto['iva'];
    $situacao_tributaria    = $dados_produto['situacao_tributaria'];

    //Inserindo os itens na NF Outra ...
    $sql = "INSERT INTO `nfs_outras_itens` (`id_nf_outra_item`, `id_nf_outra`, `id_produto_acabado`, `id_unidade`, `id_classific_fiscal`, `origem_mercadoria`, `situacao_tributaria`, `qtde`, `valor_unitario`, `peso_unitario`, `ipi`, `icms`, `reducao`, `icms_intraestadual`, `iva`) VALUES (NULL, '$_GET[id_nf_outra]', '".$campos[$i]['id_produto_acabado']."', '$id_unidade', '$id_classific_fiscal', '$origem_mercadoria', '$situacao_tributaria', '".$campos[$i]['qtde']."', '".$campos[$i]['preco_liq_final']."', '$peso_unitario', '$ipi', '$icms', '$reducao', '$icms_intraestadual', '$iva') ";
    bancos::sql($sql);
}

/*Se a Empresa do $id_nf_outra for com Nota = "Albafer" ou "Tool Master" então eu também atualizo a CFOP desta também, colocando uma
CFOP de "Venda para Entrega Futura" ...*/
if($campos_dados[0]['id_empresa'] != 4) $campo_cfop = " `id_cfop` = '237', ";

$sql = "UPDATE `nfs_outras` SET `id_funcionario` = '$_SESSION[id_funcionario]', $campo_cfop `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_nf_outra` = '$_GET[id_nf_outra]' LIMIT 1 ";
bancos::sql($sql);
?>
<Script Language = 'JavaScript'>
    alert('NOTA FISCAL "VENDA PARA ENTREGA FUTURA" GERADA COM SUCESSO !')
    parent.location = parent.location.href
</Script>