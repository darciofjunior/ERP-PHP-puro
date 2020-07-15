<?
require('../../lib/segurancas.php');
require('../../lib/calculos.php');
require('../../lib/faturamentos.php');
require('../../lib/vendas.php');

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

//Verifico a Qtde de NFs no Sistema com essa condicao, NF's que possuem pelo menos 1 item ...
$sql = "SELECT COUNT(DISTINCT(nfsi.id_nf)) AS total_registro 
        FROM `nfs` 
        INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente AND c.id_pais = '31' 
        INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf AND nfsi.valor_unitario > '0' AND nfsi.comissao_new > '0' ";
$campos_total = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'].'<br>';

//Busca todas as NFs nessa condicao ...
$sql = "SELECT DISTINCT(nfs.id_nf), c.id_pais, nfs.id_empresa, nfs.suframa 
        FROM `nfs` 
        INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente AND c.id_pais = '31' 
        INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf AND nfsi.valor_unitario > '0' AND nfsi.comissao_new > '0' 
        GROUP BY nfs.id_nf ORDER BY id_nf DESC ";
$campos = bancos::sql($sql, $indice, 1);

//Busco os itens da NF encontrada no SQL acima ...
$sql = "SELECT valor_unitario, qtde, comissao_new 
        FROM `nfs_itens` 
        WHERE `id_nf` = '".$campos[0]['id_nf']."' ";
$campos_itens = bancos::sql($sql);
$linhas_itens = count($campos_itens);
for($i = 0; $i < $linhas_itens; $i++) {
    $preco_total_lote = $campos_itens[$i]['valor_unitario'] * $campos_itens[$i]['qtde'];
    $comissao_item = vendas::comissao_representante_reais($preco_total_lote, $campos_itens[$i]['comissao_new']);
    $comissao_itens_total+= $comissao_item;
}
$calculo_total_impostos = calculos::calculo_impostos(0, $campos[0]['id_nf'], 'NF');
//Aqui nessa parte do cálculo eu pego a comissão média e divido pela qtde de Itens da Nota Fiscal
//Esse desvio para não dar erro de divisão por Zero, Dárcio
if($calculo_total_impostos['valor_total_nota'] > 0) {
    if($campos[0]['id_pais'] != 31) {
        $comissao_media = round(($comissao_itens_total / $calculo_total_impostos['valor_total_produtos']) * 100, 1);
    }else {
        $comissao_media = round(($comissao_itens_total / $calculo_total_impostos['valor_total_produtos']) * 100, 1);
    }
}else {
    $comissao_media = round($comissao_itens_total * 100, 1);
}
/*****************************************************************************************/
//Guardo a MG Media na NF ...
echo $sql = "UPDATE `nfs` SET `comissao_media` = '$comissao_media' WHERE `id_nf` = '".$campos[0]['id_nf']."' LIMIT 1 ";
bancos::sql($sql);
/*****************************************************************************************/
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_atualizar_comissao_media_nfs.php?indice=<?=++$indice;?>'
</Script>