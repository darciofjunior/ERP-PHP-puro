<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/fornecedor/utilitarios/envelope/envelopes.php', '../../../../../');

define('FPDF_FONTPATH','font/');
$pdf = new FPDF();
$pdf->FPDF('P', 'mm', 'envelope');
$pdf->Open();
$pdf->SetTopMargin(0);
$pdf->SetAutoPageBreak('false', 0);
$pdf->AddPage();

$sql = "SELECT f.`razaosocial`, f.`endereco`, f.`bairro`, f.`cep`, f.`num_complemento`, f.`cidade`, ufs.`sigla` 
        FROM `fornecedores` f 
        LEFT JOIN `ufs` ON ufs.`id_uf` = f.`id_uf` 
        WHERE f.`id_fornecedor` = '$_POST[id_fornecedor]' 
        AND f.`ativo` = '1' LIMIT 1 ";
$campos         = bancos::sql($sql);
$razao_social   = $campos[0]['razaosocial'];
$endereco       = $campos[0]['endereco'].', '.$campos[0]['num_complemento'];
$cep            = $campos[0]['cep'];

if(strlen($cep) == 7) {
    $cep = '0'.$cep;
    $cep = substr($cep, 0, 5).'-'.substr($cep, 5, 3);
}

$pdf->SetFont('Arial', '', 11);
$pdf->SetLeftMargin(100);
$pdf->Ln(48);//Comentei porque estava dando erro na Impressora da Gladys ...

$pdf->Cell(100, 5, $razao_social, 0, 1, 'L');
$pdf->Cell(100, 5, $endereco, 0, 1, 'L');
$pdf->Cell(100, 5, 'CEP: '.$cep.' - '.$campos[0]['bairro'].' - '.$campos[0]['cidade']. ' - '  .$campos[0]['sigla'], 0, 1, 'L');
$pdf->Cell(100, 5, 'ATT.: '.strtoupper($_POST['txt_contato']).' - DEPTO. '.$_POST['cmb_depto'], 0, 1, 'L');

chdir('../../../../../pdf');
$file='../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>