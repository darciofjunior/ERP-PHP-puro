<?
require('../../lib/segurancas.php');

//Busca todos os PIs que sуo PAs e que estуo sem a Classificaчуo Fiscal ...
$sql = "SELECT pa.id_produto_acabado, pi.id_produto_insumo 
        FROM `produtos_insumos` pi 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = pi.`id_produto_acabado` AND pi.`ativo` = '1' 
        WHERE pi.`id_classific_fiscal` = '0' 
        AND pi.`ativo` = '1' ORDER BY pi.discriminacao ";
$campos_pipa = bancos::sql($sql);
$linhas_pipa = count($campos_pipa);

for($i = 0; $i < $linhas_pipa; $i++) {
//Busca da Classificaчуo Fiscal do PA ...
    $sql = "SELECT f.id_classific_fiscal 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
            INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
            INNER JOIN `familias` f ON f.id_familia = gpa.id_familia 
            WHERE pa.`id_produto_acabado` = '".$campos_pipa[$i]['id_produto_acabado']."' LIMIT 1 ";
    $campos_familia	= bancos::sql($sql);
    $sql = "UPDATE `produtos_insumos` SET `id_classific_fiscal` = '".$campos_familia[0]['id_classific_fiscal']."' WHERE `id_produto_insumo` = '".$campos_pipa[$i]['id_produto_insumo']."' LIMIT 1 ";
    bancos::sql($sql);
}
echo 'TOTAL DE REGISTRO(S) => '.$linhas_pipa;
?>