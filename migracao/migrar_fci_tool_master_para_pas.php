<?
require('../lib/segurancas.php');

return migrar_fci_tool_para_pas('migrar_fci_tool_para_pas.txt');

function migrar_fci_tool_para_pas($arquivo, $separacao) {
    if(file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);
        for($i = 0; $i < count($linhas); $i++) {
            $vetor              = explode('|', trim(AddSlashes($linhas[$i])));//Coloca Barra Invertida nas Aspas Simp ...
            $referencia         = $vetor[0];
            $fci_tool_master    = $vetor[1];
            
            $sql = "UPDATE `produtos_acabados` SET `fci_tool_master` = '$fci_tool_master' WHERE `referencia` = '$referencia' LIMIT 1 ";
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