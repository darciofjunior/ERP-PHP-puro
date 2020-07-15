<?
require('../../lib/segurancas.php');

return migrar_novos_produtos('migrar_pecas_por_embalagem.txt');

function migrar_novos_produtos($arquivo) {
    if (file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);
        for ($i = 0; $i < count($linhas); $i ++) {
            $conteudo           = trim($linhas[$i]);
            $vetor_conteudo     = explode('|', $conteudo);
            $codigo_fornecedor  = $vetor_conteudo[0];
            $pecas_por_embalagem= $vetor_conteudo[1];
            
            //Aqui eu busco id_produto_acabado através do codigo_fornecedor ...
            $sql = "SELECT `id_produto_acabado` 
                    FROM `produtos_acabados` 
                    WHERE `codigo_fornecedor` = '$codigo_fornecedor' LIMIT 1 ";
            $campos_produto_acabado = bancos::sql($sql);
            if(count($campos_produto_acabado) == 1) {
                $id_produto_acabado = $campos_produto_acabado[0]['id_produto_acabado'];
                //Verifico se existe Peças por Embalagem p/ o Produto Acabado do Loop ...
                $sql = "SELECT `id_pa_pi_emb` 
                        FROM `pas_vs_pis_embs` 
                        WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
                $campos = bancos::sql($sql);
                if(count($campos) == 0) {//PA não existente na Tabela de Estoque ...
                    $sql = "INSERT INTO `pas_vs_pis_embs` (`id_pa_pi_emb`, `id_produto_acabado`, `id_produto_insumo`, `pecas_por_emb`, `embalagem_default`) VALUES (NULL, '$id_produto_acabado', '11900', '$pecas_por_embalagem', '1') ";
                }else {//PA já existente na Tabela de Estoque ...
                    $sql = "UPDATE `pas_vs_pis_embs` SET `id_produto_acabado` = '$id_produto_acabado', `id_produto_insumo` = '11900', `pecas_por_emb` = '$pecas_por_embalagem', `embalagem_default` = '1' WHERE `id_pa_pi_emb` = '".$campos[0]['id_pa_pi_emb']."' LIMIT 1 ";
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