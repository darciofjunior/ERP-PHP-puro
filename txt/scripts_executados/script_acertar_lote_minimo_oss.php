<?
require('../../lib/segurancas.php');

if(!isset($_GET['id_os'])) $_GET['id_os'] = 1;

//Todos os Itens de NF ...
$sql = "SELECT COUNT(oi.id_os) AS total_registro 
        FROM `oss_itens` oi 
        INNER JOIN `oss` ON oss.id_os = oi.id_os 
        WHERE oi.`lote_minimo_custo_tt` = '0.00' ";
$campos_total = bancos::sql($sql);
echo $total_registro = $campos_total[0]['total_registro'];
echo '<br>';

//Busco o último N.º de OS feito no Sistema ...
$sql = "SELECT id_os 
        FROM `oss` 
        ORDER BY id_os DESC LIMIT 1 ";
$campos_os              = bancos::sql($sql);
$id_ultimo_numero_os    = $campos_os[0]['id_os'];

//Busca de todas as OSS que possuem os seus itens de OS zerados ...
$sql = "SELECT oss.id_os, oss.lote_minimo_custo_tt 
        FROM `oss` 
        WHERE `id_os` = '$_GET[id_os]' LIMIT 1 ";
$campos_oss = bancos::sql($sql);
$linhas_oss = count($campos_oss);
if($linhas_oss == 1) {//Ainda existe mais esse N.º de OS no Sistema ...
    for($i = 0; $i < $linhas_oss; $i++) {
        echo $sql = "UPDATE `oss_itens` SET `lote_minimo_custo_tt` = '".$campos_oss[$i]['lote_minimo_custo_tt']."' WHERE `id_os` = '".$campos_oss[$i]['id_os']."' ";
        bancos::sql($sql);
        echo '<br>';
    }
}else {//Já não existe mais esse N.º de OS no Sistema ...
    if($_GET['id_os'] > $id_ultimo_numero_os) exit('FIM DE SCRIPT !!!');
}

//Será o id da próxima OS ...
$_GET['id_os']++;
?>
<Script Language = 'JavaScript'>
    window.location = 'script_acertar_lote_minimo_oss.php?id_os=<?=$_GET['id_os'];?>'
</Script>