<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/custos.php');
segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/lista_preco/lista_preco.php', '../../../../../');

$fator_desc_max_vendas = genericas::variavel(19);//Fator Desc Máx. de Vendas

//Essa já prepara as variáveis para o cálculo das etapas do custo
$taxa_financeira_vendas = genericas::variaveis('taxa_financeira_vendas') / 100 + 1;

//Tratamento com as variáveis que vem por parâmetro ...
$cmb_opcao_filtro = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['cmb_opcao_filtro'] : $_GET['cmb_opcao_filtro'];

if($cmb_opcao_filtro == 0) {//Todos PAs ...
    $selected0      = 'selected';
    $condicao_sql   = '';
}else if($cmb_opcao_filtro == 1) {//Somente Pinos ...
    $selected1      = 'selected';
    $condicao_sql   = ' AND gpa.id_familia = 2';
}else if($cmb_opcao_filtro == 2) {//Exceto Pinos ...
    $selected2      = 'selected';
    $condicao_sql   = ' AND gpa.id_familia <> 2';
}
	
//Aqui traz todos os PAS que possuem Preço Promocional B, com exceção dos pertencentes a Família de Componentes ...
$sql = "SELECT pa.id_produto_acabado, pa.operacao_custo, pa.referencia, pa.discriminacao, pa.preco_promocional_b 
        FROM `produtos_acabados` pa 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` $condicao_sql 
        WHERE pa.`preco_promocional_b` <> '0' 
        AND pa.`referencia` <> 'ESP' 
        AND pa.`ativo` = '1' ORDER BY pa.referencia ";
$campos = bancos::sql($sql, $inicio, 200, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Relatório de Oferta Promocional vs Custo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' src = '../../../../../js/tabela.js'></Script>
</head>
<body>
<form name='form' aciont=''>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Relatório de Oferta Promocional vs Custo
            &nbsp;-&nbsp;
            <select name='cmb_opcao_filtro' title='Selecione uma Opção de Filtro' onchange='document.form.submit()' class='combo'>
                <option value='0' <?=$selected0;?>>TODOS PRODUTOS</option>
                <option value='1' <?=$selected1;?>>SOMENTE PINO(S)</option>
                <option value='2' <?=$selected2;?>>EXCETO PINO(S)</option>
            </select>
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
            Qtde Lote <br/>do Custo
        </td>
        <td>
            Preço <br/>Prom. B
        </td>
        <td>
            <b>P. Máx. Custo <br/>Fat. R$</b>
        </td>
        <td>
            <b>Pço com <br/>60% M.L.</b>
        </td>
        <td>
            Custo R$
        </td>
        <td>
            % ML Preço B
        </td>
    </tr>
<?
    for ($i = 0; $i < $linhas; $i++) {
//Fórmula do Preço Máximo Custo Fat. R$ - esse campo está aqui, mais ele é printado + abaixo
        $preco_maximo_custo_fat_rs = custos::preco_custo_pa($campos[$i]['id_produto_acabado']) / $fator_desc_max_vendas;
//Forço o arred. para 2 casas para não dar erro na fórmula por causa do JavaScript -> Dárcio
        $preco_maximo_custo_fat_rs = round($preco_maximo_custo_fat_rs, 2);
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td align='center'>
        <?
            $sql = "SELECT qtde_lote 
                    FROM `produtos_acabados_custos` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    AND `operacao_custo` = '".$campos[$i]['operacao_custo']."' LIMIT 1 ";
            $campos_custo = bancos::sql($sql);
            echo $campos_custo[0]['qtde_lote'];
        ?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['preco_promocional_b'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($preco_maximo_custo_fat_rs, 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
            $pco_com_60_ml = round(custos::todas_etapas($campos[$i]['id_produto_acabado'], $campos[$i]['operacao_custo']) * $taxa_financeira_vendas, 2);
            echo number_format($pco_com_60_ml, 2, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
            $custo_pa = round(($pco_com_60_ml / 1.6), 2);
            echo number_format($custo_pa, 2, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
            $perc_ml_preco_b = round((($campos[$i]['preco_promocional_b'] / $custo_pa) - 1) * 100, 2);
            echo number_format($perc_ml_preco_b, 2, ',', '.');
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            &nbsp;
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>