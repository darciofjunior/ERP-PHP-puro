<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/consultar.php', '../../../../');

$sql = "SELECT gpa.nome, pa.id_produto_acabado, pa.referencia, pa.discriminacao, pa.peso_unitario 
        FROM `produtos_acabados` pa 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_grupo_pa` IN (11, 12, 59, 74, 75, 78, 81) 
        WHERE pa.`referencia` <> 'ESP' 
        AND pa.`ativo` = '1' ORDER BY gpa.nome, pa.referencia, pa.discriminacao ";
$campos = bancos::sql($sql, $inicio, 2000, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Packing List de Limas ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Packing List de
            <font color='yellow'>
                Limas
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Grupo P.A.
        </td>
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Peso Unitário
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['peso_unitario'], 4, ',', '.');?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>