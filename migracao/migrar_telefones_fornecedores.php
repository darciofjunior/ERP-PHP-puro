<?
return migrar_telefones_fornecedores('migrar_telefones_fornecedores.txt');

function migrar_telefones_fornecedores($arquivo) {
    require('../lib/segurancas.php');
    if (file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);
        for($i = 0; $i < count($linhas); $i++) {
            $vetor_conteudo = explode('|', $linhas[$i]);
            $sql = "UPDATE `fornecedores` SET `uf` = '$vetor_conteudo[1]', `id_uf` = '$vetor_conteudo[2]', `ddd_fone1` = '$vetor_conteudo[5]', `fone1` = '$vetor_conteudo[6]', `ddd_fone2` = '$vetor_conteudo[7]', `fone2` = '$vetor_conteudo[8]', `ddd_fax` = '$vetor_conteudo[9]', `fax` = '$vetor_conteudo[10]' WHERE `id_fornecedor` = '$vetor_conteudo[0]' LIMIT 1 ";
            echo $sql.';<br/>';
            //bancos::sql($sql);
        }
    }
    echo '<br/>SCRIPT TERMINADO => '.count($linhas).' linhas.';
}
?>