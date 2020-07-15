<?
require('../../../lib/pdf/fpdf.php');
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
session_start('funcionarios');
error_reporting(0);

function Heade() {
    global $pdf, $id_empresa;
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell($GLOBALS['ph']*97, 5, 'RELATÓRIO DE UNIFORMES', 1, 1, 'C');
}

function Cabecalho() {
    global $pdf;
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($GLOBALS['ph']*20, 5, 'Funcionário', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*6, 5, 'Empresa', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*11, 5, 'Data / Hora', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*6, 5, 'N.º Calç', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*6, 5, 'Camisa', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*6, 5, 'Calça', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*6, 5, 'Avental', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*26, 5, 'Observação', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*10, 5, 'Assinatura', 1, 1, 'C');
}

/////////////////////////////////////// INÍCIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel	= 'L';  // P=> Retrato L=>Paisagem
$unidade	= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= 'A4'; // A3, A4, A5, Letter, Legal
$pdf = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->SetLeftMargin(5);
$pdf->Open();
$pdf->AddPage();

global $pv,$ph; //valor baseado em mm do A4
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

//Listagem somente dos Funcionários que ainda estão trabalhando na Empresa ...
$sql = "SELECT u.*, f.nome, substring(e.nomefantasia, 1, 4) as empresa 
	FROM `uniformes` u 
	INNER JOIN `funcionarios` f ON f.id_funcionario = u.id_funcionario AND f.`status` < '3' 
	INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa ORDER BY f.nome ";
$campos = bancos::sql($sql);
$linhas = count($campos);
Heade();
Cabecalho();

$cont_registro = 1;
$total_registros = $linhas;

for($i = 0; $i < $linhas; $i++) {
    if($i % 2 == 0) {
        $pdf->SetFillColor(255, 255, 255);//Cor Branca
    }else {
        $pdf->SetFillColor(200, 200, 200);//Cor Cinza
    }

    $pdf->SetFont('Arial', '', 8);
//Funcionário
    $pdf->Cell($GLOBALS['ph']*20, 5, $campos[$i]['nome'], 1, 0, 'L', 1);
//Empresa
    $pdf->Cell($GLOBALS['ph']*6, 5, $campos[$i]['empresa'], 1, 0, 'C', 1);
//Data / Hora
    $pdf->Cell($GLOBALS['ph']*11, 5, data::datetodata(substr($campos[$i]['data_sys'], 0, 10), '/').' '.substr($campos[$i]['data_sys'], 11, 8), 1, 0, 'C', 1);
//Calçado
    $calcado = ($campos[$i]['calcado'] == 0) ? '-' : $campos[$i]['calcado'];
    $pdf->Cell($GLOBALS['ph']*6, 5, $calcado, 1, 0, 'C', 1);
    $pdf->Cell($GLOBALS['ph']*6, 5, $campos[$i]['camisa'], 1, 0, 'C', 1);
    $pdf->Cell($GLOBALS['ph']*6, 5, $campos[$i]['calca'], 1, 0, 'C', 1);
    $pdf->Cell($GLOBALS['ph']*6, 5, $campos[$i]['avental'], 1, 0, 'C', 1);
    $pdf->Cell($GLOBALS['ph']*26, 5, $campos[$i]['observacao'], 1, 0, 'L', 1);
    $pdf->Cell($GLOBALS['ph']*10, 5, '', 1, 1, 'C', 1);
    if($cont_registro == 33) {
        $total_registros-= $cont_registro;
//Significa q eu ainda tenho mais registros ainda a serem apresentados na página
        if($total_registros > 0) {
            $cont_registro = 1;
            $pagina++;
            $pdf->AddPage();
            Heade();
            Cabecalho();
        }
    }
    $cont_registro++;
}
$pdf->AddPage();

//Aqui nessa parte é como se fosse um resumo geral do quanto que eu preciso p/ cada Peça de Uniforme ...
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell($GLOBALS['ph']*97, 5, 'RESUMO', 'B', 1, 'C');
$pdf->Ln(5);

$pdf->SetLeftMargin(75);

//N.º Calçado
$sql = "SELECT COUNT(u.id_uniforme) AS total_calcado, u.calcado 
	FROM `uniformes` u 
	INNER JOIN `funcionarios` f ON f.id_funcionario = u.id_funcionario AND f.`status` < '3' 
	GROUP BY u.calcado ORDER BY u.calcado ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas > 0) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell($GLOBALS['ph']*20, 5, 'N.º Calçado', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*30, 5, 'Qtde', 1, 1, 'C');
//Disparando o Loop
    for($i = 0; $i < $linhas; $i++) {
        if($i % 2 == 0) {
            $pdf->SetFillColor(255, 255, 255);//Cor Branca
        }else {
            $pdf->SetFillColor(200, 200, 200);//Cor Cinza
        }
        $pdf->SetFont('Arial', '', 12);
        $calcado = ($campos[$i]['calcado'] == 0) ? 'Sem N.º de Calçado' : $campos[$i]['calcado'];
        $pdf->Cell($GLOBALS['ph']*20, 5, $calcado, 1, 0, 'C', 1);
        $pdf->Cell($GLOBALS['ph']*30, 5, $campos[$i]['total_calcado'], 1, 1, 'C', 1);
    }
}
$pdf->Ln(3);

//Camisa
$sql = "SELECT COUNT(u.id_uniforme) AS total_camisa, u.camisa 
	FROM `uniformes` u 
	INNER JOIN `funcionarios` f ON f.id_funcionario = u.id_funcionario AND f.`status` < '3' 
	GROUP BY u.camisa ORDER BY u.camisa ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas > 0) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell($GLOBALS['ph']*20, 5, 'Camisa', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*30, 5, 'Qtde', 1, 1, 'C');
//Disparando o Loop
    for($i = 0; $i < $linhas; $i++) {
        if($i % 2 == 0) {
            $pdf->SetFillColor(255, 255, 255);//Cor Branca
        }else {
            $pdf->SetFillColor(200, 200, 200);//Cor Cinza
        }
        $pdf->SetFont('Arial', '', 12);
        $camisa = ($campos[$i]['camisa'] == '') ? 'Sem Camisa' : $campos[$i]['camisa'];
        $pdf->Cell($GLOBALS['ph']*20, 5, $camisa, 1, 0, 'C', 1);
        $pdf->Cell($GLOBALS['ph']*30, 5, $campos[$i]['total_camisa'], 1, 1, 'C', 1);
    }
}
$pdf->Ln(3);

//Calça
$sql = "SELECT COUNT(u.id_uniforme) AS total_calca, u.calca 
	FROM `uniformes` u 
	INNER JOIN `funcionarios` f ON f.id_funcionario = u.id_funcionario AND f.`status` < '3' 
	GROUP BY u.calca ORDER BY u.calca ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas > 0) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell($GLOBALS['ph']*20, 5, 'Calça', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*30, 5, 'Qtde', 1, 1, 'C');
//Disparando o Loop
    for($i = 0; $i < $linhas; $i++) {
        if($i % 2 == 0) {
            $pdf->SetFillColor(255, 255, 255);//Cor Branca
        }else {
            $pdf->SetFillColor(200, 200, 200);//Cor Cinza
        }
        $pdf->SetFont('Arial', '', 12);
        $calca = ($campos[$i]['calca'] == '') ? 'Sem Calça' : $campos[$i]['calca'];
        $pdf->Cell($GLOBALS['ph']*20, 5, $calca, 1, 0, 'C', 1);
        $pdf->Cell($GLOBALS['ph']*30, 5, $campos[$i]['total_calca'], 1, 1, 'C', 1);
    }
}
$pdf->Ln(3);

//Avental
$sql = "SELECT COUNT(u.id_uniforme) AS total_avental, u.avental 
	FROM `uniformes` u 
	INNER JOIN `funcionarios` f ON f.id_funcionario = u.id_funcionario AND f.`status` < '3' 
	GROUP BY u.avental ORDER BY u.avental ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas > 0) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell($GLOBALS['ph']*20, 5, 'Avental', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*30, 5, 'Qtde', 1, 1, 'C');
//Disparando o Loop
    for($i = 0; $i < $linhas; $i++) {
        if($i % 2 == 0) {
            $pdf->SetFillColor(255, 255, 255);//Cor Branca
        }else {
            $pdf->SetFillColor(200, 200, 200);//Cor Cinza
        }
        $pdf->SetFont('Arial', '', 12);
        $avental = ($campos[$i]['avental'] == '') ? 'Sem Avental' : $campos[$i]['avental'];
        $pdf->Cell($GLOBALS['ph']*20, 5, $avental, 1, 0, 'C', 1);
        $pdf->Cell($GLOBALS['ph']*30, 5, $campos[$i]['total_avental'], 1, 1, 'C', 1);
    }
}
chdir('../../../pdf');
$file = '../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><SCRIPT>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>