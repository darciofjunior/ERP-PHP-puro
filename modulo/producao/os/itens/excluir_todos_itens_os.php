<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/os/itens/consultar.php', '../../../../');

/****************************************Controle**********************************************/
//Verifico se essa OS j� est� importada p/ Pedido ...
$sql = "SELECT id_pedido 
        FROM `oss` 
        WHERE `id_os` = '$_GET[id_os]' LIMIT 1 ";
$campos_os = bancos::sql($sql);
if($campos_os[0]['id_pedido'] != 0) {//Essa O.S. j� foi importada para Pedido, sendo assim n�o possu excluir nenhum Item
    $mensagem = 'NENHUM ITEM DE O.S. PODE SER EXCLU�DO !\nEST� O.S. J� FOI IMPORTADA P/ PEDIDO !';
}else {//Essa O.S. ainda est� em aberto, ent�o posso excluir os Itens dela normalmente ...
/**********************************************************************************************/
//Aqui eu mudo o Status da OP para 0 novamente, p/ que est� possa ser Importada futuramente em alguma OS
    $sql = "UPDATE `ops` 
            INNER JOIN `oss_itens` oi ON oi.id_op = ops.id_op 
            SET ops.`status_import` = '0' WHERE oi.`id_os` = '$_GET[id_os]' ";
    bancos::sql($sql);

//Aki eu Deleto todos os Itens Os(s) da Os selecionada
    $sql = "DELETE FROM `oss_itens` WHERE `id_os` = '$_GET[id_os]' ";
    bancos::sql($sql);

    $mensagem = 'TODO(S) O(S) ITEM(NS) DE OS FORAM EXCLU�DO(S) COM SUCESSO !';
}
?>
<Script Language = 'JavaScript'>
    alert('<?=$mensagem;?>')
    window.opener.parent.itens.document.form.submit()
    window.opener.parent.rodape.document.form.submit()
    window.close()
</Script>