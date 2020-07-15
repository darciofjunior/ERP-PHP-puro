<?
require('../../lib/segurancas.php');

$sql = "SELECT pa.referencia, fpi.* 
        FROM `fornecedores_x_prod_insumos` fpi 
        INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = fpi.id_produto_insumo AND pi.ativo = '1' 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = fpi.`id_produto_insumo` AND pa.ativo = '1' 
        WHERE fpi.id_fornecedor = '697' 
        AND fpi.ativo = '1' ORDER BY pi.discriminacao ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $pos_aux	= strpos($campos[$i]['referencia'], 'NL');
    if($pos_aux > 0 || !empty($pos_aux)) {//Significa que é um Produto Nova Lusa ...
        $id_produto_insumo_nova_lusa 	= $campos[$i]['id_produto_insumo'];
        $referencia_nvo                 = substr($campos[$i]['referencia'], 0, strlen($campos[$i]['referencia']) - 2);
    }else {//Produto NVO ou qualquer outra coisa ???
        if($id_produto_insumo_nova_lusa > 0) {
            if($campos[$i]['referencia'] == $referencia_nvo) {
                echo $sql = "UPDATE `fornecedores_x_prod_insumos` SET `preco_faturado` = '".$campos[$i]['preco_faturado']."', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_fornecedor` = '697' AND `id_produto_insumo` = '$id_produto_insumo_nova_lusa' LIMIT 1 ;<br>";
            }else {
                //echo 'Diferente '.$campos[$i]['referencia'].'<br>';
            }
        }
    }
}
?>