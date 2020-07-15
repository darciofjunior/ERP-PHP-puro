<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
require('../../../../../lib/genericas.php');
require('../../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/produtos_fornecedores/lista_preco/lista_precos.php', '../../../../../');

define('FPDF_FONTPATH', 'font/');
$pdf = new FPDF();
$pdf->FPDF('L', 'mm', 'Letter');
$pdf->Open();
$pdf->SetLeftMargin(5);
$pdf->AddPage();

if($toda_lista == 'true') {
    $sql = "SELECT f.razaosocial, g.referencia, pi.discriminacao, fpi.* 
            FROM `fornecedores_x_prod_insumos` fpi 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = fpi.`id_fornecedor` AND f.`id_fornecedor` = '$_GET[id_fornecedor]' 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = fpi.`id_produto_insumo` AND pi.`ativo` = '1' 
            INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
            WHERE fpi.`ativo` = '1' ORDER BY pi.discriminacao ";
}else {
    $sql = "SELECT f.razaosocial, g.referencia, pi.discriminacao, fpi.* 
            FROM `fornecedores_x_prod_insumos` fpi 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = fpi.`id_fornecedor` AND f.`id_fornecedor` = '$_GET[id_fornecedor]' 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = fpi.`id_produto_insumo` AND pi.`ativo` = '1' 
            INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
            WHERE fpi.id_produto_insumo IN ($_GET[id_produtos_insumos]) 
            AND fpi.`ativo` = '1' ORDER BY pi.discriminacao ";
}
$campos = bancos::sql($sql);
$linhas = count($campos);

/*********************************Cabeçalhos*********************************/
//Tabela do Relatório Nacional
function tabela_valor0($fornecedor, $pagina) {
    global $pdf;
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(140, 5, 'Fornecedor: '.$fornecedor, 'TBL', 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(100, 5, 'Impresso em: '.date('d/m/Y H:i:s'), 'BT', 0, 'R');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(30, 5, 'Página: '.$pagina, 'TBR', 1, 'R');
    $pdf->SetFont('Arial', '', 8);

    $pdf->Cell(25, 5, 'Referência', 1, 0, 'C');
    $pdf->Cell(71, 5, 'Discriminação', 1, 0, 'C');
    $pdf->Cell(26, 5, 'Preç. Fat. Nac. R$', 1, 0, 'C');
    $pdf->Cell(23, 5, 'Prazo Pgto dias', 1, 0, 'C');
    $pdf->Cell(20, 5, 'Desc. A/V %', 1, 0, 'C');
    $pdf->Cell(20, 5, 'Desc. SGD %', 1, 0, 'C');
    $pdf->Cell(10, 5, 'IPI %', 1, 0, 'C');
    $pdf->Cell(15, 5, 'ICMS %', 1, 0, 'C');
    $pdf->Cell(25, 5, 'Forma de Compra', 1, 0, 'C');
    $pdf->Cell(35, 5, 'Preço de Compra Nac. R$', 1, 1, 'C');
}

//Tabela Relatóio Nacional Exportação
function tabela_valor1($fornecedor, $pagina) {
    global $pdf;
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(132, 5, 'Fornecedor: '.$fornecedor, 'TBL', 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(115, 5, 'Impresso em: '.date('d/m/Y H:i:s'), 'TB', 0, 'R');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(23, 5, 'Página: '.$pagina, 'RTB', 1, 'R');
    $pdf->SetFont('Arial', '', 8);

//Primeira Linha
    $pdf->Cell(25, 5, 'Referência', 'TLR', 0, 'C');
    $pdf->Cell(65, 5, 'Discriminação', 'TLR', 0, 'C');
    $pdf->Cell(18, 5, 'Tipo de ', 'LTR', 0, 'C');
    $pdf->Cell(24, 5, 'Preço Fat. ', 'LTR', 0, 'C');
    $pdf->Cell(18, 5, 'Prazo', 'LTR', 0, 'C');
    $pdf->Cell(15, 5, 'Desc.', 'LTR', 0, 'C');
    $pdf->Cell(15, 5, 'Desc.', 'LTR', 0, 'C');
    $pdf->Cell(10, 5, 'IPI %', 'LTR', 0, 'C');
    $pdf->Cell(15, 5, 'ICMS %', 'LTR', 0, 'C');
    $pdf->Cell(15, 5, 'Forma de', 'LTR', 0, 'C');
    $pdf->Cell(25, 5, 'Preço de Compra', 'LTR', 0, 'C');
    $pdf->Cell(25, 5, 'Valor Moeda', 'LTR', 1, 'C');

//Segunda Linha
    $pdf->Cell(25, 4, '', 'LBR', 0, 'C');
    $pdf->Cell(65, 4, '', 'LBR', 0, 'C');
    $pdf->Cell(18, 4, 'Moeda', 'LBR', 0, 'C'); // Tipo de Moeda
    $pdf->Cell(24, 4, 'Import/Export', 'LBR', 0, 'C'); // Preço Faturado
    $pdf->Cell(18, 4, 'Pgto dias', 'LBR', 0, 'C'); // Prazo Pgto Dias
    $pdf->Cell(15, 4, 'A/V %', 'LBR', 0, 'C'); // Desconto a Vista
    $pdf->Cell(15, 4, 'SGD %', 'LBR', 0, 'C');  // Desconto SGD
    $pdf->Cell(10, 4, '', 'LBR', 0, 'C'); //IPI
    $pdf->Cell(15, 4, '', 'LBR', 0, 'C'); //ICMS
    $pdf->Cell(15, 4, 'Compra', 'LBR', 0, 'C'); //Forma de Compra
    $pdf->Cell(25, 4, 'Internacional R$', 'LBR', 0, 'C'); // Compra Internancional
    $pdf->Cell(25, 4, 'P/ Compra R$', 'LBR', 1, 'C');
}

//Tabela de Importação
function tabela_valor2($fornecedor, $pagina) {
    global $pdf;
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(132, 5, 'Fornecedor: '.$fornecedor, 'TBL', 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(115, 5, 'Impresso em: '.date('d/m/Y H:i:s'), 'TB', 0, 'R');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(23, 5, 'Página: '.$pagina, 'RTB', 1, 'R');
    $pdf->SetFont('Arial', '', 8);

//Primeira Linha
    $pdf->Cell(25, 5, 'Referência', 'TLR', 0, 'C');
    $pdf->Cell(65, 5, 'Discriminação', 'TLR', 0, 'C');
    $pdf->Cell(18, 5, 'Tipo de', 'LTR', 0, 'C');
    $pdf->Cell(24, 5, 'Preço Fat.', 'LTR', 0, 'C');
    $pdf->Cell(18, 5, 'Prazo', 'LTR', 0, 'C');
    $pdf->Cell(15, 5, 'Desc.', 'LTR', 0, 'C');
    $pdf->Cell(15, 5, 'Desc.', 'LTR', 0, 'C');
    $pdf->Cell(10, 5, 'IPI', 'LTR', 0, 'C');
    $pdf->Cell(15, 5, 'ICMS', 'LTR', 0, 'C');
    $pdf->Cell(15, 5, 'Forma de', 'LTR', 0, 'C');
    $pdf->Cell(32, 5, 'Preço de', 'LTR', 1, 'C');

//Segunda Linha
    $pdf->Cell(25, 4, '', 'LBR', 0, 'C');
    $pdf->Cell(65, 4, '', 'LBR', 0, 'C');
    $pdf->Cell(18, 4, 'Moeda', 'LBR', 0, 'C'); // Tipo de Moeda
    $pdf->Cell(24, 4, 'Import/Export', 'LBR', 0, 'C');
    $pdf->Cell(18, 4, 'Pgto dias', 'LBR', 0, 'C'); // Prazo Pgto Dias
    $pdf->Cell(15, 4, 'à Vista', 'LBR', 0, 'C'); // Desconto a Vista
    $pdf->Cell(15, 4, 'SGD', 'LBR', 0, 'C');  // Desconto SGD
    $pdf->Cell(10, 4, '', 'LBR', 0, 'C'); //IPI
    $pdf->Cell(15, 4, '', 'LBR', 0, 'C'); //ICMS
    $pdf->Cell(15, 4, 'Compra', 'LBR', 0, 'C'); //Forma de Compra
    $pdf->Cell(32, 4, 'Compra Inter. R$', 'LBR', 1, 'C'); // Compra Internancional
}

$fornecedor = $campos[0]['razaosocial'];
/*
$valor = 0 -> Imprime o Relatório Nacional
$valor = 1 -> Imprime o Relatório Nacional para exportação
$valor = 2 -> Imprime o Relatório Importação
*/
if($valor == 0) {//Relatório Nacional
    $pagina = 1;
    $registro = 0;
    tabela_valor0($fornecedor, $pagina);
//Dados ...
    for($i = 0; $i < $linhas; $i++) {
        $desc_sgd = ($campos[$i]['desc_sgd'] == '0.0') ? '' : number_format($campos[$i]['desc_sgd'], 1, ',', '.');
        $forma_compra = $campos[$i]['forma_compra'];
        if($forma_compra == 0) {
            $forma_compra = '';
        }else if($forma_compra == 1) {
            $forma_compra = 'FAT/NF';
        }else if($forma_compra == 2) {
            $forma_compra = 'FAT/SGD';
        }else if($forma_compra == 3) {
            $forma_compra = 'AV/NF';
        }else if($forma_compra == 4) {
            $forma_compra = 'AV/SGD';
        }
        $prazo_pgto = ($campos[$i]['prazo_pgto_ddl'] == '0.0') ? '' : number_format($campos[$i]['prazo_pgto_ddl'], 1, ',', '.');
        $icms       = $campos[$i]['icms'];
        if($icms == 0) $icms = '';
        $ipi        = $campos[$i]['ipi'];
        if($ipi == 0) $ipi = '';
        $preco      = ($campos[$i]['preco'] == '0.00') ? '' : number_format($campos[$i]['preco'], 2, ',', '.');
        $desc_avista = ($desc_avista == '0.0') ? '' : number_format($campos[$i]['desc_vista'], 1, ',', '.');
        $preco_faturado = ($campos[$i]['preco_faturado'] == '0.00') ? '' : number_format($campos[$i]['preco_faturado'], 2, ',', '.');
        if($registro == 34) {
            $pdf->AddPage();
            $pagina++;
            $registro = 1;
            tabela_valor0($fornecedor, $pagina);
        }
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(25, 5, $campos[$i]['referencia'].' - '.genericas::buscar_referencia($campos[$i]['id_produto_insumo'], $campos[$i]['referencia']), 1, 0, 'L');
        $pdf->Cell(71, 5, $campos[$i]['discriminacao'], 1, 0, 'L');
        if($preco_faturado == '0,00') $preco_faturado = '';
        if($prazo_pgto == '0,0')    $prazo_pgto = '';
        if($desc_avista == '0,0')   $desc_avista = '';
        if($desc_sgd == '0,0')      $desc_sgd = '';
        if($preco == '0,00')        $preco = '';
        $pdf->Cell(26, 5, $preco_faturado, 1, 0, 'C');
        $pdf->Cell(23, 5, $prazo_pgto, 1, 0, 'C');
        $pdf->Cell(20, 5, $desc_avista, 1, 0, 'C');
        $pdf->Cell(20, 5, $desc_sgd, 1, 0, 'C');
        $pdf->Cell(10, 5, $ipi, 1, 0, 'C');
        $pdf->Cell(15, 5, $icms, 1, 0, 'C');
        $pdf->Cell(25, 5, $forma_compra, 1, 0, 'C');
        $pdf->Cell(35, 5, $preco, 1, 1, 'C');
        $registro++;
    }
}else if($valor == 1) {//Relatório nacional para Exportação
    $pagina = 1;
    tabela_valor1($fornecedor, $pagina);
    $registro = 0;
//Dados ...
    for($i = 0;$i < $linhas; $i++) {
        $desc_sgd = ($campos[$i]['desc_sgd'] == '0.0' || $campos[$i]['desc_sgd'] == '') ? '' : number_format($campos[$i]['desc_sgd'], 1, ',', '.');
        $forma_compra = $campos[$i]['forma_compra'];
        if($forma_compra == 0) {
            $forma_compra = '';
        }else if($forma_compra == 1) {
            $forma_compra = 'FAT/NF';
        }else if($forma_compra == 2) {
            $forma_compra = 'FAT/SGD';
        }else if($forma_compra == 3) {
            $forma_compra = 'AV/NF';
        }else if($forma_compra == 4) {
            $forma_compra = 'AV/SGD';
        }
        $prazo_pgto = ($campos[$i]['prazo_pgto_ddl'] == '0.0') ? '' : number_format($campos[$i]['prazo_pgto_ddl'], 1, ',', '.');
        $icms       = $campos[$i]['icms'];
        if($icms == 0) $icms = '';
        $ipi        = $campos[$i]['ipi'];
        if($ipi == 0) $ipi = '';
        $desc_avista        = ($campos[$i]['desc_vista'] == '0.0' || $campos[$i]['desc_vista'] == '') ? '' : number_format($campos[$i]['desc_vista'], 1, ',', '.');
        if($campos[$i]['tp_moeda'] == 1) {
            $tp_moeda = 'DÓLAR - U$';
        }else if($campos[$i]['tp_moeda'] == 2) {
            $tp_moeda = 'EURO - €';
        }else {
            $tp_moeda = '';
        }
        $preco_exportacao       = ($campos[$i]['preco_exportacao'] == '0.000' || $campos[$i]['preco_exportacao'] == '') ? '' : number_format($campos[$i]['preco_exportacao'], 3, ',', '.');
        $preco_import_export    = ($campos[$i]['preco_faturado_export'] == '0.00' || $campos[$i]['preco_faturado_export'] == '') ? '' : number_format($campos[$i]['preco_faturado_export'], 2, ',', '.');
        $valor_moeda_compra     = ($campos[$i]['valor_moeda_compra'] == '0.000' || $campos[$i]['valor_moeda_compra'] == '') ? '' : number_format($campos[$i]['valor_moeda_compra'], 3, ',', '.');
        if($registro == 34) {
            $pdf->AddPage();
            $pagina++;
            tabela_valor1($fornecedor, $pagina);
            $registro= 1;
        }
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(25, 5, $campos[$i]['referencia'].' - '.genericas::buscar_referencia($campos[$i]['id_produto_insumo'], $campos[$i]['referencia']), 1, 0, 'L');
        $pdf->Cell(65, 5, $campos[$i]['discriminacao'], 1, 0, 'L');
        $pdf->Cell(18, 5, $tp_moeda, 1, 0, 'L');
        $pdf->Cell(24, 5, $preco_import_export, 1, 0, 'C');
        $pdf->Cell(18, 5, $prazo_pgto, 1, 0, 'L');
        $pdf->Cell(15, 5, $desc_avista, 1, 0, 'L');
        $pdf->Cell(15, 5, $desc_sgd, 1, 0, 'L');
        $pdf->Cell(10, 5, $ipi, 1, 0, 'L');
        $pdf->Cell(15, 5, $icms, 1, 0, 'L');
        $pdf->Cell(15, 5, $forma_compra, 1, 0, 'L');
        $pdf->Cell(25, 5, $preco_exportacao, 'LBR', 0, 'L');
        $pdf->Cell(25, 5, $valor_moeda_compra, 1, 1, 'L');
        $registro++;
    }
}else {//Relatório de Importação
    $pagina = 1;
    tabela_valor2($fornecedor, $pagina);
    $registro=0;
//Dados ...
    for($i = 0;$i < $linhas; $i++) {
        $desc_sgd = ($campos[$i]['desc_sgd'] == '0.0' || $campos[$i]['desc_sgd'] == '') ? '' : number_format($campos[$i]['desc_sgd'], 1, ',', '.');
        $forma_compra = $campos[$i]['forma_compra'];
        if($forma_compra == 0) {
            $forma_compra = '';
        }else if($forma_compra == 1) {
            $forma_compra = 'FAT/NF';
        }else if($forma_compra == 2) {
            $forma_compra = 'FAT/SGD';
        }else if($forma_compra == 3) {
            $forma_compra = 'AV/NF';
        }else if($forma_compra == 4) {
            $forma_compra = 'AV/SGD';
        }
        $prazo_pgto = ($campos[$i]['prazo_pgto_ddl'] == '0.0') ? '' : number_format($campos[$i]['prazo_pgto_ddl'], 1, ',', '.');
        $icms       = $campos[$i]['icms'];
        if($icms == 0) $icms = '';
        $ipi        = $campos[$i]['ipi'];
        if($ipi == 0) $ipi = '';
        $desc_avista        = ($campos[$i]['desc_vista'] == '0.0' || $campos[$i]['desc_vista'] == '') ? '' : number_format($campos[$i]['desc_vista'], 1, ',', '.');
        if($campos[$i]['tp_moeda'] == 1) {
            $tp_moeda = 'DÓLAR - U$';
        }else if($campos[$i]['tp_moeda'] == 2) {
            $tp_moeda = 'EURO - €';
        }else {
            $tp_moeda = '';
        }
        $preco_exportacao       = ($campos[$i]['preco_exportacao'] == '0.000' || $campos[$i]['preco_exportacao'] == '') ? '' : number_format($campos[$i]['preco_exportacao'], 3, ',', '.');
        $preco_import_export    = ($campos[$i]['preco_faturado_export'] == '0.00' || $campos[$i]['preco_faturado_export'] == '') ? '' : number_format($campos[$i]['preco_faturado_export'], 2, ',', '.');
        $valor_moeda_compra     = ($campos[$i]['valor_moeda_compra'] == '0.000' || $campos[$i]['valor_moeda_compra'] == '') ? '' : number_format($campos[$i]['valor_moeda_compra'], 3, ',', '.');
        if($registro == 34) {
            $pdf->AddPage();
            $pagina++;
            tabela_valor2($fornecedor);
            $registro= 1;
        }
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(25, 5, $campos[$i]['referencia'].' - '.genericas::buscar_referencia($campos[$i]['id_produto_insumo'], $campos[$i]['referencia']), 1, 0, 'L');
        $pdf->Cell(65, 5, $campos[$i]['discriminacao'], 1, 0, 'L');
        $pdf->Cell(18, 5, $tp_moeda, 1, 0, 'L');
        $pdf->Cell(24, 5, $preco_import_export, 1, 0, 'C');
        $pdf->Cell(18, 5, $prazo_pgto, 1, 0, 'L');
        $pdf->Cell(15, 5, $desc_avista, 1, 0, 'L');
        $pdf->Cell(15, 5, $desc_sgd, 1, 0, 'L');
        $pdf->Cell(10, 5, $ipi, 1, 0, 'L');
        $pdf->Cell(15, 5, $icms, 1, 0, 'L');
        $pdf->Cell(15, 5, $forma_compra, 1, 0, 'L');
        $pdf->Cell(32, 5, $preco_exportacao, 'LBR', 1, 'L');
        $registro++;
    }
}

chdir('../../../../../pdf');
$file='../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><SCRIPT>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>