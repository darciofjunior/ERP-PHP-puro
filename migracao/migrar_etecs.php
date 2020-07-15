<?
require('../lib/segurancas.php');

return migrar_etecs('migrar_etecs.txt');

function migrar_etecs($arquivo) {
    if (file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);
        for ($i = 0; $i < count($linhas); $i++) {
            $vetor          = explode('|', trim(AddSlashes($linhas[$i])));//Coloca Barra Invertida nas Aspas Simp ...
            $razao_social   = $vetor[0];
            $nome_fantasia  = $vetor[1];
            $endereco       = $vetor[2];
            $num_complemento= $vetor[3];
            $bairro         = $vetor[4];
            $cep            = $vetor[5];
            $cidade         = $vetor[6];
            $ddd_com        = $vetor[7];
            $telcom         = trim($vetor[8]);
            $email          = $vetor[9];
            $pagweb         = $vetor[10];
            
            $sql = "INSERT INTO `clientes` (`id_cliente`, `id_pais`, `id_uf`, `id_cliente_tipo`, `razaosocial`, `nomefantasia`, `endereco`, `num_complemento`, `bairro`, `cep`, `cidade`, `ddd_com`, `telcom`, `email`, `pagweb`) VALUES (NULL, '31', '1', '15', '$razao_social', '$nome_fantasia', '$endereco', '$num_complemento', '$bairro', '$cep', '$cidade', '$ddd_com', '$telcom', '$email', '$pagweb') ";
            bancos::sql($sql);
            
            echo $sql.'<br/>';
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