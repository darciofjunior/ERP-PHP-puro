<?

function Heade()
{
global $pdf;
//echo $luis;
$pdf->SetFont('Arial','B',15);
$pdf->Cell(80);
$pdf->Cell(30,10,'Dárcio',1,0,'C');
$pdf->Ln(20);
}

$luis="darcio";
require('../fpdf.php');
//require('lib/lib1.php');
define('FPDF_FONTPATH','font/');
//$banco = new conectar;
//$banco->conexcao();
//if($passo != 1){exit;}
$pdf=new FPDF();
$pdf->Open();
$pdf->AddPage();

//Heade();
//$pdf->SetFont('Arial','',10);
//$pdf->SetTextColor(0,0,100);
//$this->SetFont('Arial','B',15);
//$this->Cell(80);
//$this->Cell(30,10,'Title',1,0,'C');
//$this->Ln(20);
//$pdf->Cell(80,10,'Ficha de Custos de Obras',1,1,'',0,'');
//$sql = "Select Empresa from Empresas where Codigo='$cmbempresa' and status='1' limit 1";
//$result = mysql_query($sql);
//$empresa = mysql_result($result, 0, 'Empresa');
//$pdf->Cell(80,10,$empresa,1,1,'',0,'');
$pdf->Output();
?>