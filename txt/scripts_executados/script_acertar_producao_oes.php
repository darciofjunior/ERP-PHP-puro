<?
require('../../lib/segurancas.php');
require('../../lib/estoque_acabado.php');

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

//Somente as Notas Fiscais que s�o de Devolu��o ...
$sql = "SELECT COUNT(`id_oe`) AS total_registro 
        FROM `oes` ";
$campos_total = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'].'<br>';

if($total_registro == $indice) {//P/ n�o ficar em loop infinito ...
    exit;
}

$sql = "SELECT `id_oe`, `id_produto_acabado_e` 
        FROM `oes` ";
$campos = bancos::sql($sql, $indice, 1);
estoque_acabado::atualizar_producao($campos[0]['id_produto_acabado_e']);
?>
<Script Language = 'JavaScript'>
//Aqui eu j� passo o �ndice do pr�ximo ...
    window.location = 'script_acertar_producao_oes.php?indice=<?=++$indice;?>'
</Script>