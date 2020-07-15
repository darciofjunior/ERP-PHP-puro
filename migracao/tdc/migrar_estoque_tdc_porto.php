<?
require('../../lib/segurancas.php');

return migrar_novos_produtos('migrar_estoque_tdc_porto.txt');

function migrar_novos_produtos($arquivo) {
    if (file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);
        for ($i = 0; $i < count($linhas); $i ++) {
            $conteudo           = trim($linhas[$i]);
            $vetor_conteudo     = explode('|', $conteudo);
            $codigo_fornecedor  = $vetor_conteudo[0];
            $qtde_fornecedor    = $vetor_conteudo[1];
            
            //Aqui eu busco id_produto_acabado através do codigo_fornecedor ...
            $sql = "SELECT `id_produto_acabado` 
                    FROM `produtos_acabados` 
                    WHERE `codigo_fornecedor` = '$codigo_fornecedor' LIMIT 1 ";
            $campos_produto_acabado = bancos::sql($sql);
            if(count($campos_produto_acabado) == 1) {
                $id_produto_acabado = $campos_produto_acabado[0]['id_produto_acabado'];
                //Busco a Referência do Produto Acabado que foi gerado ...
                $sql = "SELECT `id_produto_acabado` 
                        FROM `estoques_acabados` 
                        WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
                $campos = bancos::sql($sql);
                if(count($campos) == 0) {//PA não existente na Tabela de Estoque ...
                    $sql = "INSERT INTO `estoques_acabados` (`id_produto_acabado`, `qtde_porto`) VALUES ('$id_produto_acabado', '$qtde_fornecedor') ";
                }else {//PA já existente na Tabela de Estoque ...
                    //Primeiro Zero o Registro de Estoque p/ gerar futuros erros de apresentação ...
                    $sql = "UPDATE `estoques_acabados` SET `qtde_porto` = '0' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
                    bancos::sql($sql);
                    
                    $sql = "UPDATE `estoques_acabados` SET `qtde_porto` = '$qtde_fornecedor' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
                }
                echo '<br/>'.$sql;
                bancos::sql($sql);
            }
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