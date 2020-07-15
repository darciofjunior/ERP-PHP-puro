<?
require('../../../lib/segurancas.php');

//Verifico se o PA1 já estava atrelado com o PA2 ...
$sql = "SELECT `id_pa_substituir` 
        FROM `pas_substituires` 
        WHERE (`id_produto_acabado_1`= '$_GET[id_pa_a_ser_desatrelado]' AND `id_produto_acabado_2` = '$_GET[id_produto_acabado]') 
        OR (`id_produto_acabado_2`= '$_GET[id_pa_a_ser_desatrelado]' AND `id_produto_acabado_1` = '$_GET[id_produto_acabado]') LIMIT 1 ";
$campos = bancos::sql($sql);
if(count($campos) == 1) {//O PA1 já estava atrelado com o PA2 ...
    //Desatrelando o P.A. Enviado da combo do P.A. Principal na Tabela relacional `pas_substituires` ...
    $sql = "DELETE FROM `pas_substituires` WHERE `id_pa_substituir` = '".$campos[0]['id_pa_substituir']."' LIMIT 1 ";
    bancos::sql($sql);
}
?>
<Script Language = 'JavaScript'>
    alert('PRODUTO ACABADO DESATRELADO COM SUCESSO !')
    opener.document.location = opener.document.location.href
    window.close()
</Script>