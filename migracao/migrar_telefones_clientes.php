<?
return migrar_telefones_clientes('migrar_telefones_clientes.txt');

function migrar_telefones_clientes($arquivo) {
    require('../lib/segurancas.php');
    if (file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);
        for($i = 0; $i < count($linhas); $i++) {
            $vetor_conteudo = explode('|', $linhas[$i]);
            $sql = "UPDATE `clientes` SET `ddi_com` = '$vetor_conteudo[4]', `ddd_com` = '$vetor_conteudo[5]', `telcom` = '$vetor_conteudo[6]', `ddi_fax` = '$vetor_conteudo[7]', `ddd_fax` = '$vetor_conteudo[8]', `telfax` = '$vetor_conteudo[9]' WHERE `id_cliente` = '$vetor_conteudo[0]' LIMIT 1 ";
            echo $sql.';<br/>';
            //bancos::sql($sql);
        }
    }
    echo '<br/>SCRIPT TERMINADO => '.count($linhas).' linhas.';
}
?>