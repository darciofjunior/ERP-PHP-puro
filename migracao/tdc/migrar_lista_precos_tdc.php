<?
require('../../lib/segurancas.php');

/*Migração da Tabela Transporte*/
return migrar_lista_precos_tdc('migrar_lista_precos_tdc.txt');

function migrar_lista_precos_tdc($arquivo) {
    if(file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);
        for($i = 0; $i < count($linhas); $i++) {
            $conteudo                   = explode('|', trim(AddSlashes($linhas[$i])));//Coloca Barra Invertida nas Aspas Simp
            $id_fornecedor_prod_insumo  = $conteudo[0];
            $preco_fat_nac              = $conteudo[1];
            $preco_compra_nac           = $conteudo[2];
            $iva                        = $conteudo[3];
            
            //Atualizo os dados na Lista de Preço do Fornecedor ...
            $sql = "UPDATE `fornecedores_x_prod_insumos` SET 
                    `preco_faturado` = '$preco_fat_nac', 
                    `preco` = '$preco_compra_nac', 
                    `prazo_pgto_ddl` = '60', 
                    `desc_vista` = '3', 
                    `desc_sgd` = '18', 
                    `ipi` = '8', 
                    `icms` = '18', 
                    `iva` = '$iva', 
                    `forma_compra` = '1', 
                    `fator_margem_lucro_pa` = '1.6' 
                    WHERE `id_fornecedor_prod_insumo` = '$id_fornecedor_prod_insumo' LIMIT 1 ";
            echo '<br/>'.$sql;
            bancos::sql($sql);
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