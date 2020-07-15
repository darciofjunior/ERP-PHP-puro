<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

//Busco todos os Fornecedores cadastrados no Sistema ...
$sql = "SELECT COUNT(`id_fornecedor`) AS total_registro 
        FROM `fornecedores` ";
$campos_total   = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'].'<br/>';

if($total_registro == $indice) exit('SCRIPT EXECUTADO COM SUCESSO !');

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

$sql = "SELECT `id_fornecedor`, `cep`, `ddd_fone1` 
        FROM `fornecedores` ";
$campos = bancos::sql($sql, $indice, 1);

//Busco dados do CEP que foi localizado acima ...
$sql = "SELECT * 
        FROM `ceps` 
        WHERE `cep` = '".$campos[0]['cep']."' LIMIT 1 ";
$campos_cep = bancos::sql($sql);
if(count($campos_cep[0]['cep']) == 1) {//Se encontrou o CEP ...
    /*Verifico se o DDD do Fornecedor está diferente do DDD do CEP cadastrado, 
    desde que exista algum DDD cadastrado também claro ...*/
    if($campos[0]['ddd_fone1'] != $campos_cep[0]['ddd'] && $campos[0]['ddd_fone1'] > 0) {
        //Se estiver diferente, então eu concateno o DDD errado junto ao telefone ...
        echo $sql = "UPDATE `fornecedores` SET `fone1` = CONCAT('(', ".$campos[0]['ddd_fone1'].",') ', `fone1`) WHERE `id_fornecedor` = '".$campos[0]['id_fornecedor']."' LIMIT 1 ";
        bancos::sql($sql);
    }
    //Aqui eu atribuo o DDD correto do CEP no cadastro do Fornecedor ...
    echo $sql = "UPDATE `fornecedores` SET `ddd_fone1` = '".$campos_cep[0]['ddd']."' WHERE `id_fornecedor` = '".$campos[0]['id_fornecedor']."' LIMIT 1 ";
    bancos::sql($sql);
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_consertar_ddds_fornecedores.php?indice=<?=++$indice;?>'
</Script>