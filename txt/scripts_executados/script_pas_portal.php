<?
require('../../lib/segurancas.php');
if(empty($indice)) $indice = 0;

//Busca todos os PA(s) cadastrados no ERP ...
$sql = "SELECT COUNT(pa.id_produto_acabado) AS total_registro 
        FROM `produtos_acabados` pa 
        INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
        INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
        INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` ";
$campos_total = bancos::sql($sql);
$total_registro = $campos_total[0]['total_registro'];

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

$sql = "SELECT cf.classific_fiscal, ed.razaosocial, gpa.nome, pa.id_produto_acabado, pa.operacao, pa.origem_mercadoria, 
        pa.referencia, pa.discriminacao, pa.peso_unitario, pa.altura, pa.largura, pa.comprimento, 
        pa.codigo_barra, pa.ativo, u.sigla 
        FROM `produtos_acabados` pa 
        INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
        INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
        INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
        ORDER BY pa.id_produto_acabado ";
$campos                 = bancos::sql($sql, $indice, 1);
$classific_fiscal       = $campos[0]['classific_fiscal'];
$divisao                = $campos[0]['razaosocial'];
$grupo                  = $campos[0]['nome'];
$operacao_faturamento   = $campos[0]['operacao'];
$origem_mercadoria      = $campos[0]['origem_mercadoria'];
$id_produto_acabado     = $campos[0]['id_produto_acabado'];
$referencia             = $campos[0]['referencia'];
$discriminacao          = $campos[0]['discriminacao'];
$peso_unitario          = $campos[0]['peso_unitario'];
$altura                 = $campos[0]['altura'];
$largura                = $campos[0]['largura'];
$comprimento            = $campos[0]['comprimento'];
$codigo_barra           = $campos[0]['codigo_barra'];
$ativo                  = $campos[0]['ativo'];
$unidade                = $campos[0]['sigla'];

//Busca da Qtde de Peças por Embalagem do PA Corrente ...
$sql = "SELECT pecas_por_emb 
        FROM `pas_vs_pis_embs` 
        WHERE `id_produto_acabado` = '$id_produto_acabado' 
        AND `embalagem_default` = '1' LIMIT 1 ";
$campos_pecas_emb   = bancos::sql($sql);
$pecas_por_emb      = $campos_pecas_emb[0]['pecas_por_emb'];
/******************************************************************/
/***********************Conexão com o Portal***********************/
/******************************************************************/
$host = mysql_connect('187.45.196.216', 'grupoalbafer1', 'd4rc10');
mysql_select_db('grupoalbafer1', $host);
//Adiciona um Produto Acabado ...
$sql = "INSERT INTO `produtos_acabados` (`id_produto_acabado`, `referencia`, `discriminacao`, `codigo_barra`, `classific_fiscal`, `unidade`, `operacao_faturamento`, `origem_mercadoria`, `peso_unitario`, `altura`, `largura`, `comprimento`, `pecas_por_emb`, `divisao`, `grupo`, `ativo`) VALUES ('$id_produto_acabado', '$referencia', '$discriminacao', '$codigo_barra', '$classific_fiscal', '$unidade', '$operacao_faturamento', '$origem_mercadoria', '$peso_unitario', '$altura', '$largura', '$comprimento', '$pecas_por_emb', '$divisao', '$grupo', '$ativo') ";
mysql_query($sql);
echo $sql.'<br>';
/******************************************************************/
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_pas_portal.php?indice=<?=++$indice;?>'
</Script>