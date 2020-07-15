<?
require('../../lib/segurancas.php');
if(empty($indice)) $indice = 0;

$sql = "SELECT COUNT(id_cliente_representante) AS total_registro 
        FROM `clientes_vs_representantes` 
        WHERE `id_empresa_divisao` = '5' ";
$campos_total   = bancos::sql($sql);
$total_registro = $campos_total[0]['total_registro'];

//P/ n�o ficar em loop infinito ...
if($total_registro == $indice) exit;

//1) Busca o Representante que est� atrelado na Empresa Divis�o "NVO" ...
$sql = "SELECT id_cliente, id_representante, desconto_cliente 
        FROM `clientes_vs_representantes` 
        WHERE `id_empresa_divisao` = '5' ";
$campos = bancos::sql($sql, $indice, 1);

//Verifico se o Cliente do Loop possui a Empresa Divis�o = "INFERPA" ...
$sql = "SELECT id_cliente_representante 
        FROM `clientes_vs_representantes` 
        WHERE `id_cliente` = '".$campos[0]['id_cliente']."' 
        AND `id_empresa_divisao` = '8' LIMIT 1 ";
$campos_empresa_divisao = bancos::sql($sql);
if(count($campos_empresa_divisao) == 0) {//N�o possui a Empresa Divis�o INFERPA, sendo assim vou cri�-la com o Representante da Empresa Divis�o NVO ...
    $sql = "INSERT INTO `clientes_vs_representantes` (`id_cliente_representante`, `id_cliente`, `id_representante`, `id_empresa_divisao`, `desconto_cliente`) VALUES (NULL, '".$campos[0]['id_cliente']."', '".$campos[0]['id_representante']."', '8', '".$campos[0]['desconto_cliente']."') ";
}else {//Possui a Empresa Divis�o Inferpa e eu atribuo nessa Divis�o o Representante da Empresa Divis�o NVO ...
    $sql = "UPDATE `clientes_vs_representantes` SET `id_representante` = '".$campos[0]['id_representante']."', `desconto_cliente` = '".$campos[0]['desconto_cliente']."' WHERE `id_cliente` = '".$campos[0]['id_cliente']."' AND `id_empresa_divisao` = '8' LIMIT 1 ";
}
echo $sql;
bancos::sql($sql);
?>
<Script Language = 'JavaScript'>
//Aqui eu j� passo o �ndice do pr�ximo ...
    window.location = 'copiar_nvo_para_inferpa.php?indice=<?=++$indice;?>'
</Script>