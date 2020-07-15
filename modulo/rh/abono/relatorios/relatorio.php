<?
require('../../../../lib/pdf/fpdf.php');
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/num_por_extenso_em_rs.php');
error_reporting(0);
session_start('funcionarios');

/////////////////////////////////////// INÍCIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel	= 'P';  // P=> Retrato L=>Paisagem
$unidade	= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= 'A4'; // A3, A4, A5, Letter, Legal
$pdf = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
$pdf->SetTopMargin(2);//2.5 deixar este valor
$pdf->SetLeftMargin(12);
$pdf->SetAutoPageBreak('false', 0);
$pdf->SetFont('Arial', '', 10);

global $pv,$ph; //valor baseado em mm do A4
if($formato_papel == 'A4') {
    if($tipo_papel == 'P') {
        $pv = 295 / 100;
        $ph = 205 / 100;
    }else {
        $pv = 205 / 100;
        $ph = 295 / 100;
    }
}else {
    echo 'Formato não definido';
}

$separador_colunas = 5;
$altura = 5;
$bordas = 1;

//Busca de Vale(s) do Funcionário ...
$sql = "SELECT a.*, e.nomefantasia, f.nome, vd.data 
	FROM `abonos` a 
	INNER JOIN `vales_datas` vd ON vd.id_vale_data = a.id_vale_data 
	INNER JOIN `funcionarios` f ON f.id_funcionario = a.id_funcionario 
	INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa 
	WHERE a.`id_abono` IN ($_GET[id_abonos]) ORDER BY f.nome ";
$campos = bancos::sql($sql);
$linhas = count($campos);

for($i = 0; $i < $linhas; $i+=2) {//Aqui vou acrescentando o i de 2 em 2 por causa da outra coluna ao lado
//Significa que já foram impressos 10 registros daquela página, e sendo assim solicito uma nova página ...
    if($i % 10 == 0) $pdf->AddPage();
    $pdf->Ln(5);
/*Estou separando de 2 em 2 porque a 2 linha seria como se fosse a outra coluna
Segunda Coluna - Verifico se exite a etiqueta ao lado*/
//Nome da Empresa
    if(($i + 1) < $linhas) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*44, $altura, $campos[$i]['nomefantasia'], 1, 0, 'C');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*44, $altura, $campos[$i + 1]['nomefantasia'], 1, 1, 'C');
    }else {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*44, $altura, $campos[$i]['nomefantasia'], 1, 1, 'C');
    }
//Tipo de Impressão ...
    if(($i + 1) < $linhas) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*31, $altura, 'Valor: ', 'L', 0, 'R');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph']*13, $altura, 'R$ '.number_format($campos[$i]['valor'], 2, ',', '.'), 'R', 0, 'L');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*31, 5, 'Valor: ', 'L', 0, 'R');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph']*13, 5, 'R$ '.number_format($campos[$i + 1]['valor'], 2, ',', '.'), 'R', 1, 'L');
    }else {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*31, 5, 'Valor: ', 'L', 0, 'R');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph']*13, 5, 'R$ '.number_format($campos[$i]['valor'], 2, ',', '.'), 'R', 1, 'L');
    }
//Nome do Funcionário ...
    if(($i + 1) < $linhas) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*6, 5, 'Nome: ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph']*38, 5, $campos[$i]['nome'], 'R', 0, 'L');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*6, 5, 'Nome: ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph']*38, 5, $campos[$i + 1]['nome'], 'R', 1, 'L');
    }else {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*6, 5, 'Nome: ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph']*38, 5, $campos[$i]['nome'], 'R', 1, 'L');
    }
//Valor por Extenso ...
    if(($i + 1) < $linhas) {
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($GLOBALS['ph']*44, 5, extenso($campos[$i]['valor'], 1), 'LR', 0, 'L');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($GLOBALS['ph']*44, 5, extenso($campos[$i + 1]['valor'], 1), 'LR', 1, 'L');
    }else {
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($GLOBALS['ph']*44, 5, extenso($campos[$i]['valor'], 1), 'LR', 1, 'L');
    }
//Nome do Funcionário ...
    if(($i + 1) < $linhas) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*7, 5, 'Abono: ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph']*37, 5, $campos[$i]['descontar_pd_pf'], 'R', 0, 'L');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*7, 5, 'Abono: ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph']*37, 5, $campos[$i + 1]['descontar_pd_pf'], 'R', 1, 'L');
    }else {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*7, 5, 'Abono: ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph']*37, 5, $campos[$i]['descontar_pd_pf'], 'R', 1, 'L');
    }
//Linha Vázia ...
    if(($i + 1) < $linhas) {
        $pdf->Cell($GLOBALS['ph']*44, 5, '', 'LR', 0, 'L');
        $pdf->Cell($separador_colunas, $altura, '', '', 0, 'L');
        $pdf->Cell($GLOBALS['ph']*44, 5, '', 'LR', 1, 'L');
    }else {
        $pdf->Cell($GLOBALS['ph']*44, 5, '', 'LR', 1, 'L');
    }
//Data ...
    if(($i + 1) < $linhas) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*10.5, 5, 'Data de Em.: ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph']*9, 5, data::datetodata($campos[$i]['data_emissao'], '/'), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*12.7, 5, ' - Data de Hol.: ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph']*11.8, 5, data::datetodata($campos[$i]['data'], '/'), 'R', 0, 'L');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*10.5, 5, 'Data de Em.: ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph']*9, 5, data::datetodata($campos[$i + 1]['data_emissao'], '/'), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*12.7, 5, ' - Data de Hol.: ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph']*11.8, 5, data::datetodata($campos[$i + 1]['data'], '/'), 'R', 1, 'L');
    }else {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*10.5, 5, 'Data de Em.: ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph']*9, 5, data::datetodata($campos[$i]['data_emissao'], '/'), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*12.7, 5, ' - Data de Hol.: ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph']*11.8, 5, data::datetodata($campos[$i]['data'], '/'), 'R', 1, 'L');
    }
//Linha Vázia ...
    if(($i + 1) < $linhas) {
        $pdf->Cell($GLOBALS['ph']*44, 5, '', 'LR', 0, 'C');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->Cell($GLOBALS['ph']*44, 5, '', 'LR', 1, 'C');
    }else {
        $pdf->Cell($GLOBALS['ph']*44, 5, '', 'LR', 1, 'C');
    }
//Assinatura do Funcionário ...
    if(($i + 1) < $linhas) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*12, 5, 'ASSINATURA', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph']*32, 5, '________________________________', 'R', 0, 'L');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*12, 5, 'ASSINATURA', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph']*32, 5, '________________________________', 'R', 1, 'L');
    }else {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*12, 5, 'ASSINATURA', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph']*32, 5, '________________________________', 'R', 1, 'L');
    }
//Linha Vázia ...
    if(($i + 1) < $linhas) {
        $pdf->Cell($GLOBALS['ph']*44, 5, '', 'LRB', 0, 'C');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->Cell($GLOBALS['ph']*44, 5, '', 'LRB', 1, 'C');
    }else {
        $pdf->Cell($GLOBALS['ph']*44, 5, '', 'LRB', 1, 'C');
    }
}

chdir('../../../../pdf');
$file='../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>