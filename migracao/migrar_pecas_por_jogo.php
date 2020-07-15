<?
return migrar_telefones_fornecedores('migrar_pecas_por_jogo.txt');

function migrar_telefones_fornecedores($arquivo) {
    require('../lib/segurancas.php');
    if (file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);
        for($i = 0; $i < count($linhas); $i++) {
            $vetor_conteudo = explode('|', $linhas[$i]);

            $sql = "UPDATE `produtos_acabados` SET `pecas_por_jogo` = '$vetor_conteudo[1]' WHERE `referencia` = '$vetor_conteudo[0]' LIMIT 1;";
            echo $sql.'<br/>';
            //bancos::sql($sql);
        }
    }
    echo '<br/>SCRIPT TERMINADO => '.count($linhas).' linhas.';
}
?>