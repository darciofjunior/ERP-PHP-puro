<?
require('../lib/segurancas.php');

return migrar_lista_preco_macho_avulso_warrior_atual_novo('lista_preco_macho_avulso_warrior_atual_novo.txt');

function migrar_lista_preco_macho_avulso_warrior_atual_novo($arquivo) {
    if (file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);
        for ($i = 0; $i < count($linhas); $i++) {
            $conteudo           = explode('|', trim(AddSlashes($linhas[$i])));//Coloca Barra Invertida nas Aspas Simp
            $referencia         = $conteudo[0];
            $preco_bruto_atual  = $conteudo[1];
            $novo_preco_bruto   = $conteudo[2];
            
            //Aqui eu busco id_produto_acabado do PA através da Referência ...
            $sql = "SELECT id_produto_acabado 
                    FROM `produtos_acabados` 
                    WHERE `referencia` = '$referencia' LIMIT 1 ";
            $campos_pa = bancos::sql($sql);
            if(count($campos_pa) == 1) {
                //Faço Backup do Preço Atual p/ o campo de Backup, antes de aderirmos os novos Preços ...
//Atualizo o PA do Loop com os Novos preços do txt ...
                $sql = "UPDATE `produtos_acabados` SET `preco_unitario_bkp` = `preco_unitario` 
                        WHERE `id_produto_acabado` = '".$campos_pa[0]['id_produto_acabado']."' LIMIT 1 ";
                bancos::sql($sql);

                //Atualizo o PA do Loop com os Novos preços do txt ...
                $sql = "UPDATE `produtos_acabados` SET `preco_unitario` = '$preco_bruto_atual',  
                        `preco_unitario_simulativa` = '$novo_preco_bruto' 
                        WHERE `id_produto_acabado` = '".$campos_pa[0]['id_produto_acabado']."' LIMIT 1 ";
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