<?
require('../../../../lib/segurancas.php');
session_start('funcionarios');

$sql = "SELECT * 
        FROM `rel_estoques` 
        WHERE `status` = '0' LIMIT 5 ";
$campos = bancos::sql($sql);//Apago todos os registro que eram de base para o Faturamento ...
if(count($campos) > 4) {
    $sql = "DELETE FROM `rel_estoques` WHERE `status` = '1' ";
    bancos::sql($sql);//apago todos os registro que eram de base para o Faturamento ...
    $sql = "UPDATE `rel_estoques` SET `status` = '1' WHERE `status` = '0' ";
    bancos::sql($sql);//atualiza todos os registros para servirem de base para o Faturamento ...
}
?>
<Script Language = 'JavaScript'>
    alert('RELATÓRIO SALVO COM SUCESSO !')
    window.close()
</Script>