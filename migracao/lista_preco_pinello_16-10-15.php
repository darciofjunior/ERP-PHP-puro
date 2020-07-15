<?
require('../lib/segurancas.php');

return migrar_lista_preco_macho_avulso_warrior_atual_novo('lista_preco_pinello_16-10-15.txt');

function migrar_lista_preco_macho_avulso_warrior_atual_novo($arquivo) {
    if (file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);
        for ($i = 0; $i < count($linhas); $i++) {
            $conteudo       = explode('|', trim(AddSlashes($linhas[$i])));//Coloca Barra Invertida nas Aspas Simp
            $referencia     = $conteudo[0];
            $preco_bruto    = $conteudo[1];
            
            //Aqui eu busco id_produto_acabado do PA através da Referência ...
            $sql = "SELECT `id_produto_acabado` 
                    FROM `produtos_acabados` 
                    WHERE `referencia` = '$referencia' LIMIT 1 ";
            $campos_pa = bancos::sql($sql);
            if(count($campos_pa) == 1) {//Encontrou o PA cadastrado no Sistema ...
                $preco_liquido = $preco_bruto * (1 - 50 / 100) * (1 - 10 / 100) * (1 - 10 / 100);
                
                //Verifico se esse PA existe nesse cadastro de Concorrente ...
                $sql = "SELECT `id_concorrente_prod_acabado` 
                        FROM `concorrentes_vs_prod_acabados` 
                        WHERE `id_concorrente` = '19' 
                        AND `id_produto_acabado` = '".$campos_pa[0]['id_produto_acabado']."' LIMIT 1 ";
                $campos_concorrente = bancos::sql($sql);
                if(count($campos_concorrente) == 1) {
                    //Atualizo o PA do Loop com o Novo Preço / Descontos do TXT ...
                    $sql = "UPDATE `concorrentes_vs_prod_acabados` 
                            SET `preco_unitario` = '$preco_bruto', `desc_a` = '50', `desc_b` = '10', 
                            `desc_c` = '10', `desc_d` = '0', `desc_e` = '0', 
                            `preco_liquido` = '$preco_liquido', `data_sys_ult_alt` = '".date('Y-m-d H:i:s')."' 
                            WHERE `id_concorrente` = '19' 
                            AND `id_produto_acabado` = '".$campos_pa[0]['id_produto_acabado']."' LIMIT 1 ";
                }else {
                    //Atualizo o PA do Loop com o Novo Preço / Descontos do TXT ...
                    $sql = "INSERT `concorrentes_vs_prod_acabados` (`id_concorrente_prod_acabado`, `id_concorrente`, `id_produto_acabado`, `preco_bruto`, `preco_liquido`, `desc_a`, `desc_b`, `desc_c`, `desc_d`, `desc_e`, `data_sys_ult_alt`) 
                            VALUES (NULL, '19', '".$campos_pa[0]['id_produto_acabado']."', '$preco_bruto', '$preco_liquido', '50', '10', '10', '0', '0', '".date('Y-m-d H:i:s')."') ";
                }
                echo $sql.'<br/>';
                bancos::sql($sql);
            }else {//Não encontrou o PA cadastrado no Sistema ...
                //echo '<br/>'.$referencia;
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