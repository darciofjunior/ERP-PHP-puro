<?
require('../../lib/segurancas.php');

//Trago todos os Custos do PA que est� sendo localizado dentro da refer�ncia ...
$sql = "SELECT pa.`referencia`, pa.`discriminacao`, pac.`id_produto_acabado_custo` 
        FROM `produtos_acabados_custos` pac 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` AND pa.`operacao_custo` = '0' AND pa.`referencia` LIKE 'HL-50%' 
        WHERE pac.`operacao_custo` = '0' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    //Verifico se o PA do Loop possui algum item em sua 7� Etapa, desde que sua refer�ncia n�o seja M�O DE OBRA ...
    $sql = "SELECT pp.`id_pac_pa` 
            FROM `pacs_vs_pas` pp 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pp.`id_produto_acabado` AND (pa.`referencia` <> 'MO-SKIN' AND pa.`referencia` NOT LIKE 'CL-%' AND pa.`referencia` NOT LIKE 'CLM-%' AND pa.referencia <> 'ESP') 
            WHERE pp.`id_produto_acabado_custo` = '".$campos[$i]['id_produto_acabado_custo']."' ";
    $campos_etapa7  = bancos::sql($sql);
    //Se encontrar pelo menos 1 item da 7� Etapa marca "Usar este Lote p/ Orc" ...
    if(count($campos_etapa7) == 1) {
        $sql = "UPDATE `pacs_vs_pas` SET `usar_este_lote_para_orc` = 'S' WHERE `id_pac_pa` = '".$campos_etapa7[0]['id_pac_pa']."' ";
        bancos::sql($sql);
        echo $sql.'<br/>';
    }else if(count($campos_etapa7) > 1) {
        echo 'REFER�NCIA COM + DE 1 ITEM NA 7� ETAPA => '.$campos[$i]['referencia'].' - '.$campos[$i]['discriminacao'].'<br/><br/>';
    }
}
echo '<br/>TOTAL DE LINHAS => '.$linhas;
//SU-, HS-, UL-, AL-, CM-, H-41, H-44, HJ-, HE-, BR-, D7979A, ML-, MR-, LM-, LB-, LA-, LE-, H-50, HL-50
?>