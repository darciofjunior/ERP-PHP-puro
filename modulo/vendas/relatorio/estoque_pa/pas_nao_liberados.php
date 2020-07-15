<?
require('../../../../lib/segurancas.php');
require('../../../../lib/intermodular.php');

/*Listagem de Todos os PA(s) em que o Estoque é > 0, que não são componentes
e o Custo desse P.A. ainda não foi liberado*/
$sql = "SELECT pa.id_produto_acabado, pa.referencia, ea.qtde 
        FROM `produtos_acabados` pa 
        INNER JOIN `estoques_acabados` ea ON ea.`id_produto_acabado` = pa.`id_produto_acabado` AND ea.`qtde` > '0' 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
        INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.`id_familia` <> '23' 
        WHERE pa.`ativo` = '1' 
        AND pa.`status_custo` = '0' ORDER BY pa.discriminacao ";
$campos = bancos::sql($sql, $inicio, 25, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Listagem de PA(s) com Estoque > 0 e Custos não liberados ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Listagem de PA(s) com Estoque > 0 e Custos não liberados
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Qtde Estoque
        </td>
    </tr>
<?
    for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
        </td>
        <td>
            <?=segurancas::number_format($campos[$i]['qtde'], 2, '.');?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>