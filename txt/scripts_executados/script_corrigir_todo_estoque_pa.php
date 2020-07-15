<?
require('../../lib/segurancas.php');
require('../../lib/estoque_acabado.php');

if(empty($indice)) $indice = 0;

//Todos os Produtos ...
$sql = "SELECT count(id_produto_acabado) total_registro 
        FROM produtos_acabados 
        WHERE ativo =1 ";
$campos_total   = bancos::sql($sql);
$total_registro = $campos_total[0]['total_registro'];

//P/ n�o ficar em loop infinito ...
if($total_registro == $indice) exit;

//Somente as Notas Fiscais que s�o Estrangeiras ...
$sql = "SELECT id_produto_acabado 
        FROM produtos_acabados 
        WHERE ativo = 1 ";
$campos = bancos::sql($sql, $indice, 1);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado'], 1);
?>
<Script Language = 'JavaScript'>
//Aqui eu j� passo o �ndice do pr�ximo ...
    window.location = 'script_corrigir_todo_estoque_pa.php?indice=<?=++$indice;?>'
</Script>