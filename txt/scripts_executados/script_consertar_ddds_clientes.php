<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

//Busco todos os Clientes cadastrados no Sistema ...
$sql = "SELECT COUNT(`id_cliente`) AS total_registro 
        FROM `clientes` ";
$campos_total   = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'].'<br/>';

if($total_registro == $indice) exit('SCRIPT EXECUTADO COM SUCESSO !');

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

$sql = "SELECT `id_cliente`, `cep`, `ddd_com` 
        FROM `clientes` ";
$campos = bancos::sql($sql, $indice, 1);

//Busco dados do CEP que foi localizado acima ...
$sql = "SELECT * 
        FROM `ceps` 
        WHERE `cep` = '".$campos[0]['cep']."' LIMIT 1 ";
$campos_cep = bancos::sql($sql);
if(count($campos_cep[0]['cep']) == 1) {//Se encontrou o CEP ...
    /*Verifico se o DDD do Cliente está diferente do DDD do CEP cadastrado, 
    desde que exista algum DDD cadastrado também claro ...*/
    if($campos[0]['ddd_com'] != $campos_cep[0]['ddd'] && $campos[0]['ddd_com'] > 0) {
        //Se estiver diferente, então eu concateno o DDD errado junto ao telefone ...
        echo $sql = "UPDATE `clientes` SET `telcom` = CONCAT('(', ".$campos[0]['ddd_com'].",') ', `telcom`) WHERE `id_cliente` = '".$campos[0]['id_cliente']."' LIMIT 1 ";
        bancos::sql($sql);
    }
    //Aqui eu atribuo o DDD correto do CEP no cadastro do Cliente ...
    echo $sql = "UPDATE `clientes` SET `ddd_com` = '".$campos_cep[0]['ddd']."' WHERE `id_cliente` = '".$campos[0]['id_cliente']."' LIMIT 1 ";
    bancos::sql($sql);
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_consertar_ddds_clientes.php?indice=<?=++$indice;?>'
</Script>