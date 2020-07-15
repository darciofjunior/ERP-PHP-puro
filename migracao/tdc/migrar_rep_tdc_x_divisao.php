<?
require('../../lib/segurancas.php');

return migrar_novos_produtos('migrar_rep_tdc_x_divisao.txt');

function migrar_novos_produtos($arquivo) {
    if(file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);
        for($i = 0; $i < count($linhas); $i++) {
            $conteudo           = trim($linhas[$i]);
            $vetor_conteudo     = explode('|', $conteudo);
            $id_cliente         = $vetor_conteudo[0];
            $id_representante   = $vetor_conteudo[1];
            $id_empresa_divisao = $vetor_conteudo[2];

            //Verifico se esse Registro já existe na Base de Dados ...
            $sql = "SELECT `id_cliente_representante` 
                    FROM `clientes_vs_representantes` 
                    WHERE `id_cliente` = '$id_cliente' 
                    AND `id_empresa_divisao` = '$id_empresa_divisao'  LIMIT 1 ";
            $campos_cliente_representante  = bancos::sql($sql);
            if(count($campos_cliente_representante) == 1) {//Registro já existente ...
                $sql = "UPDATE `clientes_vs_representantes` SET `id_representante` = '$id_representante' WHERE `id_cliente` = '$id_cliente' AND `id_empresa_divisao` = '$id_empresa_divisao' LIMIT 1 ";
            }else {//Ainda não existe ...
                $sql = "INSERT INTO `clientes_vs_representantes` (`id_cliente_representante`, `id_cliente`, `id_representante`, `id_empresa_divisao`) VALUES (NULL, '$id_cliente', '$id_representante', '$id_empresa_divisao') ";
            }
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
        echo '<font class="atencao">ARQUIVO MIGRADO COM SUCESSO '.basename($arquivo).' TAMANHO '.$tamanho.' TOTAL DE REGISTRO '.$i.'</font><br>';
    }else {
        echo '<font class="atencao">ERROR AO TENTAR ABRIR O ARQUIVO '.basename($arquivo).'</font>';
    }
}
?>