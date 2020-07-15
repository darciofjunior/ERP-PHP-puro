<?
require('../../../lib/pdf/fpdf.php');
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');
error_reporting(0);

define('FPDF_FONTPATH', 'font/');

$tipo_papel		= 'L';  // P=> Retrato L=>Paisagem
$unidade		= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel          = 'A4'; // A3, A4, A5, Letter, Legal
$pdf = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);
$pdf->SetLeftMargin(15);
$pdf->Ln(8);

global $pv, $ph; //valor baseado em mm do A4
if($formato_papel == 'A4') {
    if($tipo_papel == 'P') {
        $pv = 295/100;
        $ph = 205/100;
    }else {
        $pv = 205/100;
        $ph = 295/100;
    }
}else {
    echo 'Formato não definido';
}

//Variáveis utilizadas mais abaixo ...
/*
2158 - JRC
2209 - Imporpico
2321 - Hierros Yacare
2423 - Juan Bohm
3746 - Thyssen - Esse clientes possuem uma Exceção, onde não tem compras nos anos atuais, sendo assim eu 
retroajo com estes para o ano atual deles para 2007, p/ que daí possamos fazer as suas estastísticas ...*/
if($_GET['id_cliente'] == 2158 || $_GET['id_cliente'] == 2209 || $_GET['id_cliente'] == 2321 || $_GET['id_cliente'] == 2423 || $_GET['id_cliente'] == 3746) {
    $ano_atual = 2007;
}else {//Outros clientes, assumem o ano Atual ...
    $ano_atual = date('Y');
}

$dois_anos_atras    = $ano_atual - 2;
$meses_ano_atual    = date('m') - 1;
$qtde_meses         = 24 + $meses_ano_atual;//Esses 24, equivale aos 2 primeiros anos ...

/**************************Listagem de Todos os Produtos Vendidos************************/
$vetor_familia = explode(',', $_GET['cmb_familia']);//Transforma em Vetor a String passada por parâmetro ...

for($i = 0; $i < count($vetor_familia); $i++) {
    if($vetor_familia[$i] == 'A') {
        $acessorios             = array('3', '9', '2', '10');//É tudo que não seja Limas, Machos, Pinos, Bits e Bedames ...
        $familias_selecionadas 	= substr($familias_selecionadas, 0, strlen($familias_selecionadas) - 2);
        $vetor_familias         = explode(',' , $familias_selecionadas);
        for($j = 0; $j < count($vetor_familias); $j++) {
            if(in_array($vetor_familias[$j], $acessorios)) {
                $indice_array = array_search($vetor_familias[$j], $acessorios);//Localizo o Índice do Array ...
                unset($acessorios[$indice_array]);//Apago o valor / índice do Array ...
            }
        }
        $produtos.= 'ACESSÓRIOS - ';
    }else {
        if($vetor_familia[$i] == '%') {
            $produtos.= 'PRODUTOS (TODOS) - ';
        }else if($vetor_familia[$i] == '3') {
            $produtos.= 'LIMAS - ';
        }else if($vetor_familia[$i] == '9') {
            $produtos.= 'MACHOS - ';
        }else if($vetor_familia[$i] == '2') {
            $produtos.= 'PINOS - ';
        }else if($vetor_familia[$i] == '10') {
            $produtos.= 'BITS / BEDAMES - ';
        }
        $familias_selecionadas.= $vetor_familia[$i].', ';
    }
}
$produtos = substr($produtos, 0, strlen($produtos) - 2);

if(isset($acessorios)) {//Se existir acessórios, o Sistema irá cair pelo SQL de Acessórios apenas ...
    $condicao_acessorios = " AND gpa.id_familia NOT IN (".implode(',', $acessorios).") ";
}else {//Se não, o Sistema irá cair pelo SQL de Família ...
    $familias_selecionadas 	= substr($familias_selecionadas, 0, strlen($familias_selecionadas) - 2);
    if($familias_selecionadas == '%') {
        $condicao_familia = " AND gpa.id_familia LIKE '$familias_selecionadas' ";
    }else {
        $condicao_familia = " AND gpa.id_familia IN ($familias_selecionadas) ";
    }
}

if(isset($_GET['cmb_divisao_lima'])) {
    if($_GET['cmb_divisao_lima'] == 1) {//Nova Lusa ...
        $condicao_lima = " AND pa.referencia LIKE '%NL' ";
    }else if($_GET['cmb_divisao_lima'] == 2) {//NVO ...
        $condicao_lima = " AND pa.referencia NOT LIKE '%NL' ";
    }
}

//Aqui eu trago os X melhores PAS vendidos nos últimos 3 anos - Normais de Linha ...
$sql = "SELECT DISTINCT(pa.`id_produto_acabado`), SUM(pvi.qtde) AS qtde_total_item, SUM(pvi.`qtde` * ovi.`preco_liq_final` * ov.`valor_dolar`) AS volume_item_rs 
        FROM `produtos_acabados` pa 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.id_grupo_pa $condicao_familia $condicao_acessorios 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_produto_acabado` = pa.`id_produto_acabado` 
        INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND YEAR(pv.`data_emissao`) >= '$dois_anos_atras' 
        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
        INNER JOIN `orcamentos_vendas` ov ON ovi.`id_orcamento_venda` = ov.`id_orcamento_venda` 
        WHERE pa.`ativo` = '1' 
        AND pa.`referencia` <> 'ESP' 
        AND pa.`referencia` NOT LIKE 'HS%' 
        AND pa.`referencia` NOT LIKE '%WURTH%' 
        $condicao_lima 
        AND gpa.`id_familia` <> '23' GROUP BY pa.id_produto_acabado HAVING volume_item_rs > '0' ORDER BY volume_item_rs DESC LIMIT $_GET[cmb_qtde] ";
$campos = bancos::sql($sql);
$linhas	= count($campos);
//Toda essa maracutaia abaixo, é para ordenar os Produtos pela Discriminação ...
for($i = 0; $i < $linhas; $i++) {
    $vetor_qtde_totam_item[$campos[$i]['id_produto_acabado']] 	= $campos[$i]['qtde_total_item'];
    //$vetor_volume_item_rs[$campos[$i]['id_produto_acabado']] 	= $campos[$i]['volume_item_rs'];
    $vetor_produtos_acabados[] = $campos[$i]['id_produto_acabado'];
}
$string_produtos_acabados = implode(',', $vetor_produtos_acabados);

//Aqui eu busco os mesmos PAs pela ordem de Discriminação para que o Cliente visualize todos os Itens por ordem de Disc ...
$sql = "SELECT distinct(id_produto_acabado), referencia, discriminacao, preco_promocional_b 
        FROM `produtos_acabados` 
        WHERE `id_produto_acabado` IN ($string_produtos_acabados) ORDER BY discriminacao ";
$campos = bancos::sql($sql);
$linhas	= count($campos);

$vetor_meses = array('', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');

if($linhas > 0) {//Existe pelo menos uma Compra da Família ou Famílias ...
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell($GLOBALS['ph']*100, 5, 'São Paulo, '.date('d').' de '.$vetor_meses[intval(date('m'))].' de '.$ano_atual.'.', 0, 1, 'L');
    $pdf->Ln(8);

    //Busca do nome do Cliente ...
    $sql = "SELECT razaosocial AS cliente 
            FROM `clientes` 
            WHERE id_cliente = '$_GET[id_cliente]' LIMIT 1 ";
    $campos_cliente = bancos::sql($sql);

    $pdf->Cell($GLOBALS['ph']*100, 5, 'Atenção cliente '.$campos_cliente[0]['cliente'], 0, 1, 'L');
    $pdf->Ln(6);
    $pdf->Cell($GLOBALS['ph']*100, 5, 'Referente: Produtos de Alto Giro', 0, 1, 'L');
    $pdf->Ln(6);
    $pdf->Cell($GLOBALS['ph']*100, 5, 'Prezado senhor:', 0, 1, 'L');
    $pdf->Ln(8);

    $pdf->SetFont('Arial', '', 14);
    $pdf->MultiCell($GLOBALS['ph']*100, 5, 'Baseando-se no que o nosso mercado de Ferramentas está consumindo nos últimos meses da nossa linha de produção, destacamos em nosso Sistema, dos 10.000 itens que produzimos, os '.$_GET['cmb_qtde'].' maiores que são responsáveis por mais de 80% da nossa força produtiva.', 0, 1, 'L');
    $pdf->Ln(2);
    $pdf->MultiCell($GLOBALS['ph']*100, 5, 'Levamos ao seu conhecimento o resultado desse estudo, mostrando que '.$_GET['contador_pas'].' desses principais itens não fazem parte do nosso MIX de comercialização.', 0, 1, 'L');
    $pdf->Ln(2);
    $pdf->MultiCell($GLOBALS['ph']*100, 5, 'Passamos abaixo uma proposta de estudo para a sua apreciação e possível aceitação.', 0, 1, 'L');
    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'B', 13);
    $pdf->SetTextColor(255, 0, 0);
    $pdf->Cell($GLOBALS['ph']*100, 5, 'PRODUTOS '.$produtos, 1, 1, 'C');

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell($GLOBALS['ph']*15, 5, 'Referência', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*40, 5, 'Discriminação', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*15, 5, 'Qtde', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*15, 5, 'Pço Unitário', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*15, 5, 'Total', 1, 1, 'C');
	
    for($i = 0; $i < $linhas; $i++) {
        //Aqui eu verifico se o Cliente, tem pelo menos 1 compra desse Item mais vendido ...
        $sql = "SELECT pvi.id_pedido_venda_item 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN `pedidos_vendas` pv on pv.id_pedido_venda = pvi.id_pedido_venda and pv.id_cliente = '$_GET[id_cliente]' 
                WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' limit 1 ";
        $campos_pedido = bancos::sql($sql);
        if(count($campos_pedido) == 0) {//Se o Cliente nunca comprou esse PA, então eu faço a oferta pra ele ...
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell($GLOBALS['ph']*15, 5, $campos[$i]['referencia'], 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*40, 5, $campos[$i]['discriminacao'], 1, 0, 'L');

            $qtde_mensal = ceil(intval($vetor_qtde_totam_item[$campos[$i]['id_produto_acabado']]) / $qtde_meses);
            $qtde_projetada = ceil($qtde_mensal * $_GET['txt_perc_projetada'] / 100);

            $pdf->Cell($GLOBALS['ph']*15, 5, $qtde_projetada, 1, 0, 'C');

            //Se não tem Preço ...
            if($campos[$i]['preco_promocional_b'] == 0) {
                //Puxo o Preço A do SU - Supercut para o HS - HardSteel ...
                if(substr_count(strtoupper($campos[$i]['referencia']), 'HS')) {
                    $sql = "SELECT preco_promocional 
                            FROM `produtos_acabados` 
                            WHERE referencia = '".str_replace('HS', 'SU', $campos[$i]['referencia'])."' LIMIT 1 ";
                    $campos_preco_a = bancos::sql($sql);
                    $preco_unitario = round($campos_preco_a[0]['preco_promocional'] + ($campos_preco_a[0]['preco_promocional'] * 0.1), 2);//Preço c/ + 10 %
                //Puxo o Preço B da NVO - Nova Lusa para o NVO ...
                }else if(substr_count(strtoupper($campos[$i]['referencia']), 'NL')) {
                    $sql = "SELECT preco_promocional_b 
                            FROM `produtos_acabados` 
                            WHERE referencia = 'L".strtok($campos[$i]['referencia'], 'NL')."' LIMIT 1 ";
                    $campos_preco_b = bancos::sql($sql);
                    $preco_unitario = round($campos_preco_b[0]['preco_promocional_b'] - ($campos_preco_b[0]['preco_promocional_b'] * 0.1), 2);//Preço c/ + 10 %
                }
                $marcacao = '*';
            }else {
                $preco_unitario = $campos[$i]['preco_promocional_b'];
                $marcacao = '';
            }
            $pdf->Cell($GLOBALS['ph']*15, 5, $marcacao.'R$ '.number_format($preco_unitario, 2, ',', '.'), 1, 0, 'R');
            $projetado_item = $qtde_projetada * $preco_unitario;
            $pdf->Cell($GLOBALS['ph']*15, 5, 'R$ '.number_format($projetado_item, 2, ',', '.'), 1, 1, 'R');

            $projetado_item_total_geral+= $projetado_item;
        }
    }
	
    $pdf->SetTextColor(255, 0, 0);
    $pdf->Cell($GLOBALS['ph']*100, 5, 'TOTAL GERAL PROJ R$ '.number_format($projetado_item_total_geral, 2, ',', '.'), 1, 1, 'L');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 13);

    $pdf->MultiCell($GLOBALS['ph']*100, 5, 'Sugerimos: dividir o Total deste planejamento em 3 fornecimentos mensais. Nas seguintes condições:', 0, 1, 'L');
    $pdf->Ln(8);

    $pdf->Cell($GLOBALS['ph']*100, 5, '* Prazo Pgto em até 4 parcelas;', 0, 1, 'L');
    $pdf->Cell($GLOBALS['ph']*100, 5, '* Preço Tabela de Oferta N.º 6 2011 / 2012;', 0, 1, 'L');
    $pdf->Cell($GLOBALS['ph']*100, 5, '* Manteremos o Preço Firme até o final da entrega.', 0, 1, 'L');
    $pdf->Cell($GLOBALS['ph']*100, 5, '* Embalagem: Nas quantidades para efeito de pedido, consideramos embalagem fechada de Estoque.', 0, 1, 'L');
    $pdf->Ln(8);

    $pdf->Cell($GLOBALS['ph']*100, 5, 'Sem mais, ficamos no aguardo.', 0, 1, 'L');
    $pdf->Ln(4);

    $pdf->Cell($GLOBALS['ph']*100, 5, 'Atenciosamente', 0, 1, 'L');
    $pdf->Ln(4);

    $pdf->Cell($GLOBALS['ph']*100, 5, 'Grupo Albafer', 0, 1, 'L');
    $pdf->Ln(4);
}
/**************************Aqui eu Gravo a Projeção Trimestral do Cliente**************************/
//Primeiramente eu verifico se já foi feita alguma projeção para o determinado e respectivo Cliente ...
$sql = "SELECT id_projecao_trimestral 
        FROM `projecoes_trimestrais` 
        WHERE `id_cliente` = '$_GET[id_cliente]' 
        AND `tipo_projecao` = 'N' 
        AND `tipo_produto` = '$produtos' LIMIT 1 ";
$campos = bancos::sql($sql);
if(count($campos) == 0) {//Não tem projeção, então insere no Banco a respectiva Projeção ...
    $sql = "INSERT INTO `projecoes_trimestrais` (`id_projecao_trimestral`, `id_cliente`, `id_funcionario`, `tipo_projecao`, `tipo_produto`, `qtde_produtos`, `percentagem`, `valor_projecao`, `data_sys`) VALUES (NULL, '$_GET[id_cliente]', '$_SESSION[id_funcionario]', 'N', '$produtos', '$_GET[cmb_qtde]', '$_GET[txt_perc_projetada]', '$projetado_item_total_geral', '".date('Y-m-d H:i:s')."') ";
}else {
    $sql = "UPDATE `projecoes_trimestrais` SET `id_funcionario` = '$_SESSION[id_funcionario]', `qtde_produtos` = '$_GET[cmb_qtde]', `percentagem` = '$_GET[txt_perc_projetada]', `valor_projecao` = '$projetado_item_total_geral', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_projecao_trimestral` = ".$campos[0]['id_projecao_trimestral']." LIMIT 1 ";
}
bancos::sql($sql);

//Aqui eu gero um Follow-Up para o Cliente ...
$sql = "SELECT id_cliente_contato 
        FROM `clientes_contatos` 
        WHERE `id_cliente` = '$_GET[id_cliente]' 
        AND `ativo` = '1' LIMIT 1 ";
$campos_contatos    = bancos::sql($sql);
$id_cliente_contato = $campos_contatos[0]['id_cliente_contato'];

$sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_cliente_contato`, `id_funcionario`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$_GET[id_cliente]', '$id_cliente_contato', '$_SESSION[id_funcionario]', '13', '(Projeção Realizada) próximo passo gerar o Pedido.', '".date('Y-m-d H:i:s')."') ";
bancos::sql($sql);
/**************************************************************************************************/

chdir('../../../pdf');
$file='../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>