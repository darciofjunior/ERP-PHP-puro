<?
/*Migração da Tabela Cliente*/
return migrar_500('500_mais_vendidos.txt');

function migrar_500($arquivo) {
    require('../lib/segurancas.php');
    if (file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);
        echo '<table border="1">';

        for($i = 0; $i < count($linhas); $i++) {
            //Busca o id_produto_acabado ...
            $sql = "SELECT id_produto_acabado, referencia 
                    FROM `produtos_acabados` 
                    WHERE `referencia` = '".trim($linhas[$i])."' LIMIT 1 ";
            $campos = bancos::sql($sql);

            $sql = "SELECT date_format(substring(data_sys, 1, 10), '%d/%m/%Y') AS data_inventario 
                    FROM `baixas_manipulacoes_pas` 
                    WHERE `id_produto_acabado` = ".$campos[0]['id_produto_acabado']." 
                    AND `acao` = 'I' ORDER BY id_baixa_manipulacao_pa DESC LIMIT 1 ";
            $campos_inventario = bancos::sql($sql);
            if(count($campos_inventario) == 1) {
                echo '<tr><td>'.$campos[0]['referencia'].'</td>';
                echo '<td>'.$campos_inventario[0]['data_inventario'].'</td></tr>';
            }else {
                echo '<tr><td>-</td></tr>';
            }
        }
        echo '</table>';
    }
}
?>