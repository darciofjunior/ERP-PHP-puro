<?
require('../../lib/segurancas.php');

return migrar_precos_vendas_tdc('migrar_precos_venda_tdc3.txt');

function migrar_precos_vendas_tdc($arquivo) {
    if (file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);
        for($i = 0; $i < count($linhas); $i++) {
            $vetor_conteudo = explode('|', $linhas[$i]);
            $sql = "UPDATE `produtos_acabados` SET `preco_unitario` = '$vetor_conteudo[1]' WHERE `codigo_fornecedor` = '$vetor_conteudo[0]' LIMIT 1 ";
            bancos::sql($sql);
            echo '<br/>'.$sql;
        }
    }
    echo '<br/>SCRIPT TERMINADO => '.count($linhas).' linhas.';
}
?>