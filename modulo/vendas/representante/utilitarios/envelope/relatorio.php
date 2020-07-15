<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/vendas/representante/utilitarios/envelope/envelopes.php', '../../../../../');

define('FPDF_FONTPATH','font/');
$pdf    = new FPDF();
$pdf->FPDF('P', 'mm', 'envelope');
$pdf->Open();
$pdf->SetTopMargin(0);
$pdf->SetAutoPageBreak('false', 0);
$pdf->AddPage();

//Aqui eu trago alguns dados do Representante passado por parâmetro p/ apresentar no Envelope ...
$sql = "SELECT nome_representante, endereco, num_comp, cep, cidade, uf 
        FROM `representantes` 
        WHERE `id_representante` = '$_POST[id_representante]' 
        AND `ativo` = '1' LIMIT 1 ";
$campos = bancos::sql($sql);
if(strlen($campos[0]['cep']) == 7) {
    $cep = '0'.$campos[0]['cep'];
    $cep = substr($campos[0]['cep'], 0, 5).'-'.substr($campos[0]['cep'], 5, 3);
}else {
    $cep = $campos[0]['cep'];
}
 
$pdf->SetFont('Arial', '', 11);
$pdf->SetLeftMargin(70);
$pdf->Ln(42);

$pdf->Cell(100, 5, $campos[0]['nome_representante'], 0, 1, 'L');
$pdf->Cell(100, 5, $campos[0]['endereco'].', '.$campos[0]['num_comp'], 0, 1, 'L');
$pdf->Cell(100, 5, $cep.' - '.strtoupper($campos[0]['cidade']).' - '.strtoupper($campos[0]['uf']), 0, 1, 'L');
$pdf->Cell(100, 5, 'ATT.: '.strtoupper($_POST['txt_contato']).' - DEPTO. '.$_POST['cmb_depto'], 0, 1, 'L');

chdir('../../../../../pdf');
$file='../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>