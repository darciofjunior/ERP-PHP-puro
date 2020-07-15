<?
require('../../../lib/segurancas.php');
require('../../../lib/ajax.php');

//Aqui eu trago toda a Carteira de Cliente do Representante passado por parâmetro ...
$sql = "SELECT `id_cliente` 
        FROM `clientes_vs_representantes` 
        WHERE `id_representante` = '$_POST[cmb_representante]' 
        GROUP BY `id_cliente` ";
$campos_clientes = bancos::sql($sql);
$linhas_clientes = count($campos_clientes);
if($linhas_clientes == 0) {//Não encontrou nenhum Cliente p/ este Representante ...
    $vetor_clientes[] = 0;
}else {//Encontrou pelo menos 1 Cliente p/ o Representante que foi passado por parâmetro ...
    for($i = 0; $i < $linhas_clientes; $i++) $vetor_clientes[] = $campos_clientes[$i]['id_cliente'];
}

//Trago mais dados dos Clientes que foram encontrados acima ...
$sql = "SELECT `id_cliente`, IF(`razaosocial` = '', REPLACE(`nomefantasia`, '&', 'E'), REPLACE(`razaosocial`, '&', 'E')) AS rotulo 
        FROM `clientes` 
        WHERE `id_cliente` IN (".implode(',', $vetor_clientes).") ORDER BY rotulo ";
$campos = bancos::sql($sql);
$combo 	= ajax::combo($campos, 'id_cliente', 'rotulo');
?>