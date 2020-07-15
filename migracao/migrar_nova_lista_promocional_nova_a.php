<?
//Esses preços foram migrados na Data do dia 02/07/2013 ...
return migrar_promocao_a('nova_lista_promocional_nova_a.txt');

function migrar_promocao_a($arquivo) {
    require('../lib/segurancas.php');
    if (file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);
        for($i = 0; $i < count($linhas); $i++) {
            $vetor_conteudo = explode('|', $linhas[$i]);
            $sql = "UPDATE `produtos_acabados` SET `qtde_promocional` = '$vetor_conteudo[0]', `preco_promocional` = '$vetor_conteudo[2]' WHERE `referencia` = '$vetor_conteudo[1]' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
    echo '<br/>SCRIPT TERMINADO => '.count($linhas).' linhas.';
}
?>