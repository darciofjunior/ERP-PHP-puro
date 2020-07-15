<?
return migrar_500('machos_maquinas.txt');

function migrar_500($arquivo) {
    require('../lib/segurancas.php');
    if(file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);

        for($i = 0; $i < count($linhas); $i++) {
            $conteudo           = explode('|', trim(AddSlashes($linhas[$i])));//Coloca Barra Invertida nas Aspas Simp
            $referencia         = $conteudo[0];
            $preco_promocional  = $conteudo[1];
            
            $sql = "UPDATE `produtos_acabados` SET `qtde_promocional` = '1', `preco_promocional` = '$preco_promocional' WHERE `referencia` = '$referencia' LIMIT 1;";
            echo $sql.'<br/>';
            
            //$campos = bancos::sql($sql);
        }
    }
}
?>