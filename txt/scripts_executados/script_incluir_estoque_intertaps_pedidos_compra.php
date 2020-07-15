<?
require('../../lib/segurancas.php');

//Migração dos novos Preços de Produtos da Lista de Preço ...
return incluir_estoque_intertaps_pedidos_compra('script_incluir_estoque_intertaps_pedidos_compra.txt');

function incluir_estoque_intertaps_pedidos_compra($arquivo) {
    if (file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);
        for($i = 0; $i < count($linhas); $i++) {
            $conteudo           = explode('|', trim(AddSlashes($linhas[$i])));//Coloca Barra Invertida nas Aspas Simp
            $id_produto_insumo  = $conteudo[0];
            $preco_unitario     = $conteudo[1];
            $qtde               = $conteudo[2];
            $id_pedido          = $conteudo[3];
            $marca              = $conteudo[4];

            $sql = "INSERT INTO `itens_pedidos` (`id_item_pedido`, `id_pedido`, `id_produto_insumo`, `preco_unitario`, `qtde`, `marca`) VALUES (NULL, '$id_pedido', '$id_produto_insumo', '$preco_unitario', '$qtde', '$marca'); ";
            echo '<br/>'.$sql;
        }
        flush();
        $tamanho = filesize($arquivo);
        if ($tamanho >= '1073741824') {
            $tamanho = round($tamanho / 1073741824 * 100) / 100 . ' GB';
        }elseif ($tamanho >= '1048576') {
            $tamanho = round($tamanho / 1048576 * 100) / 100 . ' MB';
        }elseif ($tamanho >= '1024') {
            $tamanho = round($tamanho / 1024 * 100) / 100 . ' KB';
        }else {
            $tamanho = $tamanho . ' B';
        }
        echo '<font class="atencao">ARQUIVO MIGRADO COM SUCESSO '.basename($arquivo).' TAMANHO '.$tamanho.' TOTAL DE REGISTRO '.$x.'</font><br>';
    }else {
        echo '<font class="atencao">ERROR AO TENTAR ABRIR O ARQUIVO '.basename($arquivo).'</font>';
    }
}
?>