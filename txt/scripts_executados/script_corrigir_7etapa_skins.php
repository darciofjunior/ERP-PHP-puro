<?
require('../../lib/segurancas.php');

//Busco todos os Custos que são Industriais e que possuem Produtos Industriais ...
$sql = "SELECT gpa.`id_familia`, pac.`id_produto_acabado_custo`, pa.`discriminacao` 
        FROM `produtos_acabados_custos` pac 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` AND pa.`operacao_custo` = '0' AND pa.`discriminacao` LIKE '%SKIN%' 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
        WHERE pac.`operacao_custo` = '0' ";
$campos_custo = bancos::sql($sql);
$linhas_custo = count($campos_custo);
for($i = 0; $i < $linhas_custo; $i++) {
    //Verifico se esse Custo possui algum item atrelado na sua 7ª Etapa desde que seja da mesma família do PA do Custo ...
    $sql = "SELECT pp.`id_pac_pa`, pa.`discriminacao` 
            FROM `pacs_vs_pas` pp 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pp.`id_produto_acabado` 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` = '".$campos_custo[$i]['id_familia']."' 
            WHERE pp.`id_produto_acabado_custo` = '".$campos_custo[$i]['id_produto_acabado_custo']."' LIMIT 1 ";
    $campos_etapa7 = bancos::sql($sql);
    if(count($campos_etapa7) == 1) {
        echo $campos_custo[$i]['discriminacao'].'|'.$campos_etapa7[0]['discriminacao'].'<br/>';
        $sql = "UPDATE `pacs_vs_pas` SET `usar_este_lote_para_orc` = 'S' WHERE `id_pac_pa` = '".$campos_etapa7[0]['id_pac_pa']."' LIMIT 1 ";
        bancos::sql($sql);
        echo $sql.'<br/><br/>';
    }
}
?>