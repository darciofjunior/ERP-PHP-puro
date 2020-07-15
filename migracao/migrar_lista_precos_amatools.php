<?
require('../lib/segurancas.php');

//Migração dos novos Preços de Produtos da Lista de Preço ...
return migrar_novos_produtos_precos('migrar_lista_precos_amatools.txt');

function migrar_novos_produtos_precos($arquivo) {
    if (file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);
        for ($x = 0; $x < count($linhas); $x ++) {
            $conteudo           = explode('|', trim(AddSlashes($linhas[$x])));//Coloca Barra Invertida nas Aspas Simp
            $id_produto_insumo  = $conteudo[0];
            $preco_faturado     = $conteudo[1];
            $preco_forma_compra = $conteudo[2];
            
            //Com o PI eu entro na Lista de Preço do Fornecedor Amatools "2294" e atualizo o seu preço ...
            $sql = "UPDATE `fornecedores_x_prod_insumos` fpi SET `preco_faturado` = '$preco_faturado', 
                    `preco` = '$preco_forma_compra', `data_sys` = '".date('Y-m-d')."' 
                    WHERE `id_fornecedor` = '2294' 
                    AND `id_produto_insumo` = '$id_produto_insumo' LIMIT 1; ";
            echo $sql.'<br/>';
            //bancos::sql($sql);
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