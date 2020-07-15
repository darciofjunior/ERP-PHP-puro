<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../../');

//Com o id_pedido, eu busco qual � o id_fornecedor, id_pais
$sql = "SELECT p.desconto_especial_porc, p.tipo_export, p.id_fornecedor, f.id_pais 
        FROM `pedidos` p 
        INNER JOIN `fornecedores` f ON f.id_fornecedor = p.id_fornecedor 
        WHERE p.id_pedido = '$_GET[id_pedido]' LIMIT 1 ";
$campos         = bancos::sql($sql);
$id_fornecedor  = $campos[0]['id_fornecedor'];
$id_pais        = $campos[0]['id_pais'];
//Vou utilizar na l�gica um pouco + abaixo
$desconto_especial_porc = $campos[0]['desconto_especial_porc'];
$tipo_export    = $campos[0]['tipo_export'];

/*Busca os id_item_pedido, id_produtos_insumos da tabela de itens_pedidos do pedido, 
que estejam parciais ou totalmente em aberto e que o pre�o Unit�rio seja diferente de Zero*/
$sql = "SELECT id_item_pedido, id_produto_insumo, qtde 
        FROM `itens_pedidos` 
        WHERE `id_pedido` = '$_GET[id_pedido]' 
        AND `preco_unitario` <> '0.00' 
        AND `status` < '2' ";
$campos = bancos::sql($sql);
$linhas = count($campos);

//Disparo do Loop com os Itens do Pedido
for($i = 0; $i < $linhas; $i++) {
    $atualizar_item = 1;//O default � atualizar o Item ...
//Verifico se o PI do Item de Pedido � um PA, e se este � um ESP ...
    $sql = "SELECT referencia 
            FROM `produtos_acabados` 
            WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' 
            AND `referencia` = 'ESP' 
            AND `ativo` = '1'  LIMIT 1 ";
    $campos_pipa = bancos::sql($sql);
    if(count($campos_pipa) == 1) $atualizar_item = 0;//Se o Item � ESP, n�o posso atualiz�-lo pela Lista de Pre�o ...
//Procedimento p/ atualizar o Item com os dados da Lista de Pre�o ...
    if($atualizar_item == 1) {
//Com o id_fornecedor e com o id_produto_corrente verifico qual � o pre�o desse item na lista de Pre�o
        $sql = "SELECT preco, preco_exportacao, ipi, preco_faturado_export 
                FROM `fornecedores_x_prod_insumos` 
                WHERE `id_fornecedor` = '$id_fornecedor' 
                AND `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' 
                AND `ativo` = '1' LIMIT 1 ";
        $campos_preco_lista = bancos::sql($sql);
        if($id_pais == 31) {//Quando for Nacional (Brasil)
//Se o Tipo de Pedido for Nacional
            if($tipo_export == 'N') {
                $preco_de_lista = $campos_preco_lista[0]['preco'];
    //Se o Tipo de Pedido for Exporta��o
            }else {
                $preco_de_lista = $campos_preco_lista[0]['preco_exportacao'];
            }
        }else {//Quando for Internacional, puxa o pre�o internacional
            $preco_de_lista = $campos_preco_lista[0]['preco_faturado_export'];
        }
//Se existir o Desconto Especial de Pedido, ent�o eu aplico esse desconto em cima do Pre�o da Lista ...
        if($desconto_especial_porc != 0) {
            $preco_de_lista-= $preco_de_lista * ($desconto_especial_porc / 100);
/*Aqui eu arredondo o Rec�lculo do Pre�o com Desconto p/ 2 casas, evitando futuros 
erro de c�lculos ...*/
            $preco_de_lista = round(round($preco_de_lista, 3), 2);
        }
        $sql = "UPDATE `itens_pedidos` SET `preco_unitario` = '$preco_de_lista', `ipi` = '".$campos_preco_lista[0]['ipi']."' WHERE `id_item_pedido` = '".$campos[$i]['id_item_pedido']."' LIMIT 1 ";
        bancos::sql($sql);
    }
}
//Registro o Follow UP p/ saber que este Pedido foi atualizado segundo os novos Pre�os da Lista de Pre�o ...
$observacao = "<font color=\"blue\"><b>Atualiza��o Autom�tica dos Novos Pre�os da Lista Pre�o p/ este Pedido</b></font> (Somente p/ Itens Totalmente em Aberto ou Parciais, em que o pre�o Unit�rio R$ seja diferente de Zero e que a refer�ncia seja diferente de ESP) ";

$sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_fornecedor`, `id_funcionario`, `identificacao`, `origem`, `data_entrega_embarque`, `observacao`, `data_sys`) VALUES (NULL, '$id_fornecedor', '$_SESSION[id_funcionario]', '$_GET[id_pedido]', '16', '".date('Y-m-d')."', '$observacao', '".date('Y-m-d H:i:s')."') ";
bancos::sql($sql);
?>
<Script Language = 'JavaScript'>
    alert('PRE�O(S) ATUALIZADO(S) COM SUCESSO !')
    window.opener.parent.itens.document.form.submit()
    window.close()
</Script>