<?
require('../lib/segurancas.php');

//Migração dos novos Preços de Produtos da Lista de Preço ...
return migrar_novos_produtos_precos('migrar_atualizar_preco_lista_compras_jt.txt');

function migrar_novos_produtos_precos($arquivo) {
    if (file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);
        for ($x = 0; $x < count($linhas); $x++) {
            $conteudo           = explode('|', trim(AddSlashes($linhas[$x])));//Coloca Barra Invertida nas Aspas Simp
            $referencia         = $conteudo[0];
            $preco_faturado     = $conteudo[1];
            $preco_forma_compra = $conteudo[2];
            $forma_compra       = 2;//Faturado SGD ...
            
            //Aqui eu verifico se o PA que está sendo migrado já é um PI no sistema ...
            $sql = "SELECT `id_produto_insumo` 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_insumo` > '0' 
                    AND `referencia` = '$referencia' LIMIT 1 ";
            $campos_pipa = bancos::sql($sql);
            if(count($campos_pipa) == 1) {
                /*Agora com o PI na mão eu consigo entrar na Lista de Preço do Fornecedor ...
                e atualizar o seu preço ...*/
                $sql = "UPDATE `fornecedores_x_prod_insumos` fpi SET `id_funcionario` = '62', `preco_faturado` = '$preco_faturado', 
                        `preco` = '$preco_forma_compra', `forma_compra` = '$forma_compra', `data_sys` = '".date('Y-m-d')."' 
                        WHERE `id_fornecedor` = '2196' 
                        AND `id_produto_insumo` = '".$campos_pipa[0]['id_produto_insumo']."' LIMIT 1 ";
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