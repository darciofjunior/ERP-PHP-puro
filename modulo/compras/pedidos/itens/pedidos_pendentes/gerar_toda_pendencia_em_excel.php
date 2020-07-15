<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
require('../../../../../lib/estoque_acabado.php');
require('../../../../../lib/genericas.php');
require('../../../../../lib/arquivos/gerar_arquivo.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/pedidos_pendentes/pedidos_pendentes.php', '../../../../../');

$sql = "SELECT LOWER(SUBSTRING_INDEX(razaosocial, ' ', 1)) AS fornecedor 
        FROM `fornecedores` 
        WHERE `id_fornecedor` = '$_GET[id_fornecedor]' LIMIT 1 ";
$campos_fornecedor = bancos::sql($sql);

gerar_arquivo::arquivo('toda_pendencia_fornecedor_'.$campos_fornecedor[0]['fornecedor'], 'xls');

//Aqui começamos a posicionar os campos da nossa tbela
//Veja que para mudar para a celular do lado coloca uma tabulacao (\t) w para ir para a linha de baixo (\n)
$tabulacao      = "\t";//Para mudar para a célula ao lado ...
$quebra_linha   = "\n";//Para mudar de linha ...

//Rótulos ...
$rotulos = 'Qtde Solicitada'.$tabulacao;
$rotulos.= 'Qtde Recebido'.$tabulacao;
$rotulos.= 'Qtde Restante'.$tabulacao;
$rotulos.= 'E Forn'.$tabulacao;
$rotulos.= 'E Porto'.$tabulacao;
$rotulos.= 'Un'.$tabulacao;
$rotulos.= 'Produto'.$tabulacao;
$rotulos.= 'Preço Unitário'.$tabulacao;
$rotulos.= 'Valor Pendente'.$tabulacao;
$rotulos.= 'N.º Ped'.$tabulacao;
$rotulos.= 'Data Emissão'.$tabulacao;
$rotulos.= 'Pzo Entr / Emb'.$tabulacao;
$rotulos.= 'Marca / Obs'.$tabulacao;
$rotulos.= 'Embalagem Principal'.$quebra_linha;

//Imprime os Rótulos na Planilha do Excel ...
echo $rotulos;

$_GET['sql_principal'] = str_replace('|', ' ', $_GET['sql_principal']);
$_GET['sql_principal'] = str_replace('!', "'", $_GET['sql_principal']);
$_GET['sql_principal'] = str_replace(':', '%', $_GET['sql_principal']);

$campos = bancos::sql($_GET['sql_principal']);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
//Verifico em Nota Fiscal, a Qtde Entregue do Item de Pedido Corrente ...
    $sql = "SELECT SUM(nfeh.qtde_entregue) AS total_entregue 
            FROM `itens_pedidos` ip 
            INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` 
            INNER JOIN `nfe_historicos` nfeh ON nfeh.`id_item_pedido` = ip.`id_item_pedido` 
            WHERE ip.`id_item_pedido` = '".$campos[$i]['id_item_pedido']."' ";
    $campos_qtde_entregue   = bancos::sql($sql);
    $total_entregue         = $campos_qtde_entregue[0]['total_entregue'];
//Nessa variável eu verifico o quanto que ainda resta para Entregar daquele Item ...
    $total_restante         = $campos[$i]['qtde'] - $total_entregue;
    
    $linhas_itens = number_format($campos[$i]['qtde'], 2, ',', '.').$tabulacao;
    $linhas_itens.= number_format($total_entregue, 2, ',', '.').$tabulacao;
    $linhas_itens.= number_format($total_restante, 2, ',', '.').$tabulacao;
    
    //Busco o PA do PI se esse for "PIPA" ...
    $sql = "SELECT `id_produto_acabado` 
            FROM `produtos_acabados` 
            WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
    $campos_pa = bancos::sql($sql);
    if(count($campos_pa) == 1) {
        $estoque_produto            = estoque_acabado::qtde_estoque($campos_pa[0]['id_produto_acabado'], 0);
        $est_fornecedor             = $estoque_produto[12];
        $est_porto                  = $estoque_produto[13];
    }else {
        $est_fornecedor             = 0;
        $est_porto                  = 0;
    }
    
    $linhas_itens.= number_format($est_fornecedor, 2, ',', '.').$tabulacao;
    $linhas_itens.= number_format($est_porto, 2, ',', '.').$tabulacao;
    $linhas_itens.= $campos[$i]['sigla'].$tabulacao;
    $linhas_itens.= genericas::buscar_referencia($campos[$i]['id_produto_insumo'], $campos[$i]['referencia']).' * '.$campos[$i]['discriminacao'].$tabulacao;
    
    $tipo_moeda = $campos[$i]['tp_moeda'];
    if($tipo_moeda == 1) {
        $tipo_moeda = 'R$ ';
    }else if($tipo_moeda == 2) {
        $tipo_moeda = 'U$ ';
    }else {
        $tipo_moeda = '&euro; ';
    }

    $linhas_itens.= $tipo_moeda.number_format($campos[$i]['preco_unitario'], 2, ',', '.').$tabulacao;
    $linhas_itens.= $tipo_moeda.number_format($total_restante * $campos[$i]['preco_unitario'], 2, ',', '.').$tabulacao;
    $linhas_itens.= $campos[$i]['id_pedido'].$tabulacao;
    $linhas_itens.= data::datetodata($campos[$i]['data_emissao'], '/').$tabulacao;
    $linhas_itens.= data::datetodata($campos[$i]['prazo_entrega'], '/').$tabulacao;
    $linhas_itens.= $campos[$i]['marca'].$tabulacao;
    
    if(count($campos_pa) == 1) {//Se esse PI for "PIPA" ...
        //Busco a Embalagem Principal 
        $sql = "SELECT pi.`discriminacao` 
                FROM `pas_vs_pis_embs` ppe 
                INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppe.`id_produto_insumo` 
                WHERE ppe.`id_produto_acabado` = '".$campos_pa[0]['id_produto_acabado']."' LIMIT 1 ";
        $campos_embalagem = bancos::sql($sql);
        $linhas_itens.= $campos_embalagem[0]['discriminacao'].$quebra_linha;
    }else {
        $linhas_itens.= ''.$quebra_linha;
    }
//Imprime os Dados de cada Item da Lista na Planilha do Excel ...
    echo $linhas_itens;
}
?>