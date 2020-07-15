<?
require('../../lib/segurancas.php');

//Aqui eu busco o Peso de Todos os PA(s) ...
$sql = "SELECT id_cliente, CONCAT(razaosocial, ' (', nomefantasia, ')') AS cliente 
        FROM `clientes` 
        WHERE ativo = 1 ORDER BY cliente ";
$campos_clientes = bancos::sql($sql);
$linhas_clientes = count($campos_clientes);
for($i = 0; $i < $linhas_clientes; $i++) {
    $sql = "SELECT COUNT(DISTINCT(id_representante)) AS total_representante 
            FROM `clientes_vs_representantes` 
            WHERE id_cliente = '".$campos_clientes[$i]['id_cliente']."' ";
    $campos_representantes = bancos::sql($sql);
    if($campos_representantes[0]['total_representante'] > 1) echo $campos_clientes[$i]['cliente'].'<br>';
}
?>