<?
require('../../lib/segurancas.php');
session_start('funcionarios');

echo $_SESSION['id_funcionario'].'<br>';

if(empty($indice)) $indice = 0;

echo 'Registro Atual '.$indice.' / ';

//Todos os Pedidos do Sistema...
$sql = "SELECT COUNT(DISTINCT(p.id_pedido)) AS total_registro 
        FROM `itens_pedidos` ip 
        INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` ";
$campos_total = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'].'<br>';

if($total_registro == $indice) exit('SCRIPT EXECUTADO COM SUCESSO !');

//Aqui é para reassumir o Tempo Logado ...
$_SESSION['ultimo_acesso'] = date('Y-m-d H:i:s');
echo 'Último Acesso às '.$_SESSION['ultimo_acesso'].'<br>';

//TOdos os itens daquele pedido ...
$sql = "SELECT p.id_pedido, SUM(ip.`qtde` * ip.`preco_unitario`) AS valor_total_pedido 
        FROM `itens_pedidos` ip 
        INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` 
        GROUP BY p.`id_pedido` ";
$campos = bancos::sql($sql, $indice, 1);

echo $sql = "UPDATE `pedidos`  set `valor_ped` = '".$campos[0]['valor_total_pedido']."' WHERE `id_pedido` = '".$campos[0]['id_pedido']."' LIMIT 1 ";
bancos::sql($sql);
?>
<Script Language = "JavaScript">
    window.location = 'script_atualizar_valor_pedidos_em_compras.php?indice=<?=++$indice;?>'
</Script>