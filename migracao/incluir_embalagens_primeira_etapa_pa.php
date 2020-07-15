<?
require('../lib/segurancas.php');

return incluir_embalagens_primeira_etapa_pa('incluir_embalagens_primeira_etapa_pa.txt');

function incluir_embalagens_primeira_etapa_pa($arquivo) {
    if (file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);
        
        for ($i = 0; $i < count($linhas); $i++) {
            $conteudo           = explode('|', trim(AddSlashes($linhas[$i])));//Coloca Barra Invertida nas Aspas Simp
            $referencia         = $conteudo[0];
            $id_produto_insumo  = $conteudo[1];
            
            //Aqui eu busco id_produto_acabado do PA através da Referência ...
            $sql = "SELECT id_produto_acabado 
                    FROM `produtos_acabados` 
                    WHERE `referencia` = '$referencia' LIMIT 1 ";
            $campos_pa = bancos::sql($sql);
            if(count($campos_pa) == 1) {
                //Insiro uma Embalagem como Default p/ o PA do Loop ...
                $sql = "INSERT INTO `pas_vs_pis_embs` (`id_pa_pi_emb`, `id_produto_acabado`, `id_produto_insumo`, `pecas_por_emb`, `embalagem_default`) VALUES (NULL, '".$campos_pa[0]['id_produto_acabado']."', '$id_produto_insumo', '0', '1'); ";
                echo $sql.'<br/>';
                
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