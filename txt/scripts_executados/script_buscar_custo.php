<?
require('../../lib/segurancas.php');
require('../../lib/custos.php');

$fator_desc_max_vendas 	= genericas::variavel(19);//Fator Desc Máx. de Vendas

$produtos = array('AB-219', 'AB-220', 'AB-225', 'AB-226', 'AB-500', 'AB-500D', 'AM-501', 'C-202', 'C-203', 'C-204', 'CS-254', 'H-391', 'H-392', 
'H-411', 'H-412', 'H-413', 'H-414', 'H-442', 'H-444', 'H-446', 'H-501', 'H-502', 'H-503', 'H-504', 'HE-556', 'HE-557', 'HE-561', 'HE-562', 'HE-563', 
'HE-566', 'HEST-561', 'HEST-562', 'HEST-563', 'LA-304SNL', 'LA-306SNL', 'LA-308SNL', 'LA-310SNL', 'LA-312SNL', 'LA-322SNL', 'LA-608SNL', 'LA-610SNL', 
'LB-406SNL', 'LB-408SNL', 'LB-410SNL', 'LB-412SNL', 'LB-506SNL', 'LB-508SNL', 'LB-510SNL', 'LB-512S', 'LB-512SNL', 'LB-606SNL', 'LB-608SNL', 
'LB-610SNL', 'LB-612SNL', 'LB-716SNL', 'LB-718SNL', 'LB-720SNL', 'LB-816SNL', 'LB-818SNL', 'LB-820SNL', 'LE-301NL', 'LE-301SNL', 'LM-416SNL', 
'LM-418SNL', 'LM-420SNL', 'LM-516SNL', 'LM-518SNL', 'LM-520SNL', 'LM-616SNL', 'LM-618SNL', 'LM-620SNL', 'LM-622SNL', 'LM-706SNL', 'LM-708SNL', 
'LM-710SNL', 'LM-806SNL', 'LM-808SNL', 'ML-001', 'ML-002', 'ML-003', 'ML-005', 'ML-006', 'ML-007', 'ML-009', 'ML-011', 'ML-012', 'ML-024', 'ML-045', 
'ML-047', 'ML-048', 'ML-049', 'ML-051', 'ML-052', 'ML-053', 'ML-054', 'ML-062', 'ML-063', 'ML-065', 'ML-066', 'ML-067', 'ML-068', 'MMH-100', 
'MMH-101', 'MMH-102', 'MMH-103', 'MMH-106', 'MMH-107', 'MMH-108', 'MMH-109', 'MMH-120', 'MMH-121', 'MMH-122', 'MMH-123', 'MMR-100', 'MMR-101', 
'MMR-102', 'MMR-103', 'MMR-120', 'MMR-121', 'MMR-122', 'MR-001', 'MR-002', 'MR-003', 'MR-005', 'MR-006', 'MR-007', 'MR-009', 'MR-011', 'MR-012', 
'MR-045', 'MR-047', 'MR-048', 'MR-049', 'MR-051', 'MR-052', 'MR-053', 'MR-054', 'MR-062', 'MR-063', 'MR-065', 'MR-066', 'MR-067', 'MR-068', 'PB-802', 
'PB-803', 'PB-805', 'PB-850', 'PB-851', 'PB-852', 'PBS-802', 'PBS-803', 'PBS-805', 'PBS-850', 'PBS-851', 'PBS-852', 'SU-407', 'SU-409', 'SU-413', 
'SU-419', 'SU-421', 'SU-427', 'SU-429', 'SU-431', 'SU-504', 'SU-508', 'SU-515', 'SU-516', 'SU-605', 'SU-606', 'SU-608', 'SU-610', 'SU-613', 'TM-008', 
'TM-014', 'TM-020', 'TM-022', 'TM-028', 'TM-030', 'TM-104', 'TM-108', 'TM-116', 'TM-203', 'TM-206', 'TM-208', 'TM-210', 'TM-213');

for($i = 0; $i < count($produtos); $i++) {
    $sql = "SELECT ged.margem_lucro_minima, pa.id_produto_acabado 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
            WHERE pa.`referencia` = '$produtos[$i]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    //Fórmula do Preço Máximo Custo Fat. R$ - esse campo está aqui, mais ele é printado + abaixo
    $preco_maximo_custo_fat_rs = custos::preco_custo_pa($campos[0]['id_produto_acabado']) / $fator_desc_max_vendas;
//Forço o arred. para 2 casas para não dar erro na fórmula por causa do JavaScript -> Dárcio
    $preco_maximo_custo_fat_rs = round($preco_maximo_custo_fat_rs, 2);
    $preco_custo_ml_min_rs = ($preco_maximo_custo_fat_rs / 2) * (1 + $campos[0]['margem_lucro_minima'] / 100);
    echo round($preco_custo_ml_min_rs, 2).'<br>';
}
?>