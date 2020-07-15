<?
require('../../lib/segurancas.php');
require('../../lib/estoque_acabado.php');

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

$sql = "SELECT COUNT(`id_produto_acabado`) AS total_registro 
        FROM `produtos_acabados` ";
$campos_total           = bancos::sql($sql);
echo $total_registro    = $campos_total[0]['total_registro'].'<br>';

if($total_registro == $indice) exit('SCRIPT EXECUTADO COM SUCESSO !');

$sql = "SELECT `id_produto_acabado` 
        FROM `produtos_acabados` ";
$campos = bancos::sql($sql, $indice, 1);

//Aqui eu atualizo o campo de Produção do Estoque
estoque_acabado::atualizar_producao($campos[0]['id_produto_acabado']);
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_acertar_producao.php?indice=<?=++$indice;?>'
</Script>