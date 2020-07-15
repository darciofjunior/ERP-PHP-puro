<?
require('../../lib/segurancas.php');
require('../../lib/estoque_acabado.php');

if(empty($indice)) $indice = 0;

//Todos os Produtos ...
$sql = "SELECT COUNT(id_produto_acabado) AS total_registro 
        FROM produtos_acabados 
        WHERE ativo =1 ";
$campos_total   = bancos::sql($sql);
$total_registro = $campos_total[0]['total_registro'];

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

//Somente as Notas Fiscais que são Estrangeiras ...
$sql = "SELECT id_produto_acabado 
        FROM produtos_acabados 
        WHERE ativo = '1' ";
$campos = bancos::sql($sql, $indice, 1);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) estoque_acabado::atualizar_producao($campos[$i]['id_produto_acabado']);
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_corrigir_estoque_pa.php?indice=<?=++$indice;?>'
</Script>