<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
require('../../../../lib/jpgraph/src/jpgraph.php');
require('../../../../lib/jpgraph/src/jpgraph_bar.php');

//Busca de Vendas em R$ ...
$valor_dolar_dia = genericas::moeda_dia('dolar');

$condicao_data = ($_GET['cmb_tipo_data'] == 'emissao') ? 'pv.`data_emissao`' : 'pv.`faturar_em`';
if($_GET['chkt_expresso'] == 'S') $condicao_expresso = " AND pv.`expresso` = 'S' ";
//Busca de todos os Representantes que são Funcionários ...
$sql = "SELECT r.`id_representante`, LOWER(SUBSTRING_INDEX(r.`nome_fantasia`, ' ', 1)) AS nome_fantasia, SUM(rc.`cota_mensal`) AS total_cota_mensal 
        FROM `representantes` r 
        INNER JOIN `representantes_vs_funcionarios` rf ON rf.`id_representante` = r.`id_representante` 
        INNER JOIN `representantes_vs_cotas` rc ON rc.`id_representante` = rf.`id_representante` 
        WHERE r.ativo = '1' GROUP BY rc.`id_representante` HAVING(total_cota_mensal > 0) 
        ORDER BY r.`nome_fantasia` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $cotas[] = $campos[$i]['total_cota_mensal'];

    //Aqui eu busco o total de Vendas de Cada de representante ...
    $sql = "SELECT IF(c.`id_pais` = 31, SUM(pvi.`qtde` * pvi.`preco_liq_final`), SUM(pvi.`qtde` * pvi.`preco_liq_final`) * $valor_dolar_dia) AS total_vendas 
            FROM `pedidos_vendas_itens` pvi 
            INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` $condicao_expresso AND pv.`liberado` = '1' 
            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
            WHERE $condicao_data BETWEEN '$data_inicial' AND '$data_final' 
            AND pvi.`id_representante` = '".$campos[$i]['id_representante']."' ";
    $campos_vendas  = bancos::sql($sql);
    $vendas[]       = $campos_vendas[0]['total_vendas'];	
}

//Create the graph. These two calls are always required
$graph = new Graph(2200, 1100, 'auto');
$graph->SetScale('textlin');
$graph->SetMarginColor('black');

$theme_class = new UniversalTheme;
$graph->SetTheme($theme_class);

$graph->SetBox(false);
$graph->ygrid->SetFill(false);

//Crio um vetor com os Representantes para apresentar no Eixo "x" Horizontal do Gráfico ...
for($i = 0; $i < $linhas; $i++) $representantes[] = $campos[$i]['nome_fantasia'];

$graph->xaxis->SetTickLabels($representantes);
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false, false);

//Cria as colunas do Gráfico ...
$coluna_cotas 	= new BarPlot($cotas);
$coluna_vendas 	= new BarPlot($vendas);

//Create the grouped bar plot
$gbplot = new GroupBarPlot(array($coluna_cotas, $coluna_vendas));
$graph->Add($gbplot);

//Cores da Coluna 1 ...
$coluna_cotas->SetColor('black');
$coluna_cotas->SetFillColor('#0000EE');
$coluna_cotas->SetLegend('Cotas');

//Cores da Coluna 2 ...
$coluna_vendas->SetColor('black');
$coluna_vendas->SetFillColor('#1C86EE');
$coluna_vendas->SetLegend('Vendas');

$graph->img->SetMargin(70, 40, 160, 0);//Margens do Gráfico ...
$graph->title->Set('Gráfico de Vendas vs Metas no Período de '.data::datetodata($data_inicial, '/').' a '.data::datetodata($data_final, '/'));
$graph->title->SetFont(FF_FONT2);
$graph->xaxis->title->Set('Representantes');//Linha Horizontal
$graph->yaxis->title->Set('Metas');//Linha Vertical
$graph->ygrid->SetFill(true, '#EFEFEF@0.5', '#D3D3D3@0.5'); 
$graph->legend->SetFrameWeight(1);
$graph->legend->SetColumns(2);
$graph->legend->SetColor('#000000','#000000');
$graph->Stroke();//Mostrá o Gráfico ...
?>