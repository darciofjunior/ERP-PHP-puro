<?
require('../../lib/segurancas.php');

return migrar_ufs_cidades_ddd('ufs_cidades_ddd.txt');

function migrar_ufs_cidades_ddd($arquivo) {
    if (file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);
        for ($i = 0; $i < count($linhas); $i ++) {
            $conteudo           = trim($linhas[$i]);
            $vetor_conteudo     = explode('|', $conteudo);
            $uf                 = $vetor_conteudo[0];
            $cidade             = $vetor_conteudo[1];
            $ddd                = $vetor_conteudo[2];
            
            //Aqui eu busco id_cep através da cidade e UF ...
            $sql = "SELECT `id_cep` 
                    FROM `ceps` 
                    WHERE `cidade_descricao` LIKE '%".addslashes($cidade)."%' 
                    AND `uf` = '$uf' 
                    AND `ddd` = '0' LIMIT 1 ";
            $campos_cep = bancos::sql($sql);
            if(count($campos_cep) == 0) {
                //echo '<br/>Não achou Cidade '.addslashes($cidade).' / UF '.$uf;
            }else {
                //Atualizo 
                $sql = "UPDATE `ceps` SET `ddd` = '$ddd' WHERE `cidade_descricao` LIKE '%".addslashes($cidade)."%' AND `uf` = '$uf' AND `ddd` = '0'; ";
                
                echo '<br/>'.$sql;
                //bancos::sql($sql);
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