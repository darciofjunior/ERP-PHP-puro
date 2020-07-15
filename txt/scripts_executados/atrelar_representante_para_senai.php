<?
require('../../lib/segurancas.php');

for($i = 40331; $i <= 40807; $i++) {
    //Aqui eu busco todas as Empresas Divisões que estão cadastradas no Sistema ...
    $sql = "SELECT id_empresa_divisao 
            FROM `empresas_divisoes` 
            WHERE `ativo` = '1' ";
    $campos_empresas_divisoes = bancos::sql($sql);
    $linhas_empresas_divisoes = count($campos_empresas_divisoes);
    //Insere o Representante "DIRETO em todas as Divisões existentes" para o Cliente gerado ...
    for($j = 0; $j < $linhas_empresas_divisoes; $j++) {
        $sql = "INSERT INTO `clientes_vs_representantes` (`id_cliente_representante`, `id_cliente`, `id_representante`, `id_empresa_divisao`, `desconto_cliente`) VALUES (NULL, '$i', '129', '".$campos_empresas_divisoes[$j]['id_empresa_divisao']."', '0'); ";
        echo $sql.'<br/>';
    }
}
?>