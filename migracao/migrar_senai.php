<?
require('../lib/segurancas.php');

return migrar_senai('migrar_senai.txt', '|');

function migrar_senai($arquivo, $separacao) {
    if(file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);
        for($i = 0; $i < count($linhas); $i++) {
            $vetor          = explode('|', trim(AddSlashes($linhas[$i])));//Coloca Barra Invertida nas Aspas Simp ...
            $nome_fantasia  = $vetor[0];
            $razao_social   = $vetor[1];
            $endereco       = $vetor[2];
            $numero_comp    = $vetor[3];
            $bairro         = $vetor[4];
            $cidade         = $vetor[5];
            
            //Aqui eu busco o id_uf através da UF ...
            $sql = "SELECT id_uf 
                    FROM `ufs` 
                    WHERE `sigla` = '$vetor[6]' LIMIT 1 ";
            $campos_uf      = bancos::sql($sql);
            
            $cep            = $vetor[7];
            $ddd_com        = $vetor[8];
            $tel_com        = $vetor[9];
            $ddd_fax        = $vetor[10];
            $tel_fax        = $vetor[11];
            
            $sql = "INSERT INTO `clientes` (`id_cliente`, `id_pais`, `id_cliente_tipo`, `nomefantasia`, `razaosocial`, `endereco`, `num_complemento`, `bairro`, `cidade`, `id_uf`, `cep`, `ddd_com`, `telcom`, `ddd_fax`, `telfax`, `data_cadastro`) VALUES (NULL, '31', '15', '$nome_fantasia', '$razao_social', '$endereco', '$numero_comp', '$bairro', '$cidade', '".$campos_uf[0]['id_uf']."', '$cep', '$ddd_com', '$tel_com', '$ddd_fax', '$tel_fax', '".date('Y-m-d')."') ";
            echo $sql.';<br/><br/>';
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