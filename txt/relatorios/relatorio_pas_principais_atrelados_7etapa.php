<?
require('../../lib/segurancas.php');

$sql = "SELECT pa.`referencia`, pac.`id_produto_acabado_custo`, pp.`id_produto_acabado` 
        FROM `pacs_vs_pas` pp 
        INNER JOIN `produtos_acabados_custos` pac ON pac.id_produto_acabado_custo = pp.id_produto_acabado_custo 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` ORDER BY pa.referencia ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Relatório de PA(s) Principal(is) Atrelado(s) na 7ª Etapa ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../js/sessao.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            Relatório de PA(s) Principal(is) Atrelado(s) na 7ª Etapa
        </td>
    </tr>
<?
    for ($i = 0; $i < $linhas; $i++) {
        //Verifico se esse PA "Principal do Custo" do Loop está atrelado na 7ª Etapa de alguém ...
        $sql = "SELECT pp.id_produto_acabado, pa.referencia, pa.discriminacao 
                FROM `pacs_vs_pas` pp 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pp.`id_produto_acabado` 
                WHERE pp.`id_produto_acabado_custo` = '".$campos[$i]['id_produto_acabado_custo']."' 
                AND pp.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
        $campos_etapa7 = bancos::sql($sql);
?>
    <tr class='linhanormal'>
        <td>
            <?=$campos[$i]['referencia'].' => '.$campos_etapa7[0]['referencia'].' | '.$campos_etapa7[0]['discriminacao'];?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>