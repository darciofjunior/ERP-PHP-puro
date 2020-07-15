<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

//Verifico a Qtde de NFs no Sistema com essa condicao, NF's que possuem pelo menos 1 item ...
$sql = "SELECT COUNT(DISTINCT(id_cliente)) AS total_registro 
        FROM `orcamentos_vendas` ";
$campos_total = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'].'<br>';

if($indice == $total_registro) exit('FIM DE SCRIPT !');

$sql = "SELECT DISTINCT(id_cliente), data_emissao 
        FROM `orcamentos_vendas` 
        GROUP BY id_cliente ";
$campos = bancos::sql($sql, $indice, 1);
    
echo '<br>'.$sql = "UPDATE `clientes` SET `data_cadastro` = '".$campos[0]['data_emissao']."' WHERE `id_cliente` = '".$campos[0]['id_cliente']."' AND `data_cadastro` = '0000-00-00' LIMIT 1 ";
bancos::sql($sql);
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_data_cadastro_cliente.php?indice=<?=++$indice;?>'
</Script>