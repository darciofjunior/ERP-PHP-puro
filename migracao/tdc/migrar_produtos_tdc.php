<?
require('../../lib/segurancas.php');
require('../../lib/producao.php');

return migrar_novos_produtos('cadastro_produtos_tdc2.txt', 'produtos_acabados', 'codigo_fornecedor, referencia, discriminacao, id_unidade, operacao, operacao_custo, origem_mercadoria, id_gpa_vs_emp_div, explodir_view_estoque, peso_unitario, preco_unitario', '|');

function migrar_novos_produtos($arquivo, $tabela, $campos, $separacao) {
    if (file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);
        for ($x = 0; $x < count($linhas); $x ++) {
            $conteudo  = trim(AddSlashes($linhas[$x]));//Coloca Barra Invertida nas Aspas Simp
            $conteudo  = str_replace($separacao, "', '", $conteudo);//Troca o Pipe por Virg ...
            $sql = "INSERT INTO $tabela ($campos, `id_funcionario`, `data_sys`) VALUES ('$conteudo', '62', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
            $id_produto_acabado = bancos::id_registro();

            //Busco a Referência do Produto Acabado que foi gerado ...
            $sql = "SELECT referencia 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            $campos_referencia  = bancos::sql($sql);
            if($campos_referencia[0]['referencia'] != 'ESP') {//Somente PA(s) normais de Linha possuem código de Barra ...
                //Nessa parte do Script é gerado o código de barra p/ o PA que acabou de ser gerado no ERP ...
                $codigo_barra = producao::gerador_codigo_barra($id_produto_acabado);
                $sql = "UPDATE `produtos_acabados` SET `codigo_barra` = '$codigo_barra' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
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