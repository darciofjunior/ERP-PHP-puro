<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
require('../../../../lib/arquivos/gerar_arquivo.php');
segurancas::geral('/erp/albafer/modulo/compras/produtos_fornecedores/lista_preco/lista_precos.php', '../../../../');

$sql = "SELECT LOWER(SUBSTRING_INDEX(razaosocial, ' ', 1)) AS fornecedor 
        FROM `fornecedores` 
        WHERE `id_fornecedor` = '$_GET[id_fornecedor]' LIMIT 1 ";
$campos_fornecedor = bancos::sql($sql);


gerar_arquivo::arquivo('lista_de_fornecedor_'.$campos_fornecedor[0]['fornecedor'], 'xls');

//Aqui começamos a posicionar os campos da nossa tbela
//Veja que para mudar para a celular do lado coloca uma tabulacao (\t) w para ir para a linha de baixo (\n)
$tabulacao      = "\t";//Para mudar para a célula ao lado ...
$quebra_linha   = "\n";//Para mudar de linha ...

//Rótulos ...
if($_GET[excel] == 1) {//Lista de Preço Nacional ...
    $rotulos = 'Referência'.$tabulacao;
    $rotulos.= 'Discriminação'.$tabulacao;
    $rotulos.= 'Preço Fat. Nac.'.$tabulacao;
    $rotulos.= 'Forma de Compra'.$tabulacao;
    $rotulos.= 'Preço de Compra Nac.'.$quebra_linha;
}else if($_GET[excel] == 2) {//Lista de Preço Import / Export ...
    $rotulos = 'Referência'.$tabulacao;
    $rotulos.= 'Discriminação'.$tabulacao;
    $rotulos.= 'Preço Fat. Inter.'.$tabulacao;
    $rotulos.= 'Forma de Compra'.$tabulacao;
    $rotulos.= 'Preço de Compra Inter.'.$quebra_linha;
}

//Imprime os Rótulos na Planilha do Excel ...
echo $rotulos;

//Busco todos os Itens de Lista de Preço do "$_GET[id_fornecedor]" passado por parâmetro ...
$sql = "SELECT g.`referencia`, pi.`id_produto_insumo`, pi.`discriminacao`, fpi.`preco_faturado`, 
        fpi.`forma_compra`, fpi.`preco`, fpi.`preco_faturado_export`, fpi.`preco_exportacao`, fpi.`tp_moeda` 
        FROM `fornecedores_x_prod_insumos` fpi 
        INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = fpi.id_produto_insumo AND pi.`ativo` = '1' 
        INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
        WHERE fpi.`id_fornecedor` = '$_GET[id_fornecedor]' 
        AND fpi.`ativo` = '1' ORDER BY pi.discriminacao ";
$campos = bancos::sql($sql);
$linhas = count($campos);

for($i = 0; $i < $linhas; $i++) {
    
    if($campos[$i]['forma_compra'] == 1) {
        $forma_compra = 'FAT/NF';
    }else if($campos[$i]['forma_compra'] == 2) {
        $forma_compra = 'FAT/SGD';
    }else if($campos[$i]['forma_compra'] == 3) {
        $forma_compra = 'AV/NF';
    }else if($campos[$i]['forma_compra'] == 4) {
        $forma_compra = 'AV/SGD';
    }
    
    if($_GET[excel] == 1) {//Lista de Preço Nacional ...
        if($campos[$i]['tp_moeda'] == 1) {
            $moeda = 'U$ ';
        }else if($campos[$i]['tp_moeda'] == 2) {
            $moeda = '&euro; ';
        }else {
            $moeda = 'R$ ';
        }
    }else if($_GET[excel] == 2) {//Lista de Preço Import / Export ...
        if($campos[$i]['tp_moeda'] == 1) {
            $moeda = 'U$ ';
        }else if ($campos[$i]['tp_moeda'] == 2) {
            $moeda = '&euro; ';
        }else {
            $moeda = '? ';
        }
    }
    
    $referencia = genericas::buscar_referencia($campos[$i]['id_produto_insumo'], $campos[$i]['referencia']);
    
    if($_GET[excel] == 1) {//Lista de Preço Nacional ...
        $linhas_itens = $referencia.$tabulacao;
        $linhas_itens.= $campos[$i]['discriminacao'].$tabulacao;
        $linhas_itens.= $moeda.number_format($campos[$i]['preco_faturado'], 2, ',', '.').$tabulacao;
        $linhas_itens.= $forma_compra.$tabulacao;
        $linhas_itens.= $moeda.number_format($campos[$i]['preco'], 2, ',', '.').$quebra_linha;
    }else if($_GET[excel] == 2) {//Lista de Preço Import / Export ...
        $linhas_itens = $referencia.$tabulacao;
        $linhas_itens.= $campos[$i]['discriminacao'].$tabulacao;
        $linhas_itens.= $moeda.number_format($campos[$i]['preco_faturado_export'], 2, ',', '.').$tabulacao;
        $linhas_itens.= $forma_compra.$tabulacao;
        $linhas_itens.= $moeda.number_format($campos[$i]['preco_exportacao'], 2, ',', '.').$quebra_linha;
    }
//Imprime os Dados de cada Item da Lista na Planilha do Excel ...
    echo $linhas_itens;
}
?>