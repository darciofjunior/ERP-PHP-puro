<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/vendas/cliente/utilitarios/envelope/envelopes.php', '../../../../../');

define('FPDF_FONTPATH','font/');
$pdf    = new FPDF();
$pdf->FPDF('P', 'mm', 'envelope');
$pdf->Open();
$pdf->SetTopMargin(0);
$pdf->SetAutoPageBreak('false', 0);
$pdf->AddPage();

$sql = "SELECT `nomefantasia`, `razaosocial`, `endereco`, `num_complemento`, `bairro`, `cep`, `cidade`, `id_uf` 
        FROM `clientes` 
        WHERE `id_cliente` = '$_POST[id_cliente]' 
        AND `ativo` = '1' LIMIT 1 ";
$campos         = bancos::sql($sql);
$nome_fantasia  = $campos[0]['nomefantasia'];
$razao_social   = $campos[0]['razaosocial'];

//Endereço Normal
$endereco       = $campos[0]['endereco'];
$num_complemento = $campos[0]['num_complemento'];
$bairro         = $campos[0]['bairro'];
$cep            = $campos[0]['cep'];

if(strlen($cep) == 7) {
    $cep = '0'.$cep;
    $cep = substr($cep, 0, 5).'-'.substr($cep, 5, 3);
}
$cidade         = $campos[0]['cidade'];

$sql = "SELECT `estado`, `sigla` 
        FROM `ufs` 
        WHERE `id_uf` = '".$campos[0]['id_uf']."' LIMIT 1 ";
$campos_uf  = bancos::sql($sql);
$estado     = strtoupper($campos_uf[0]['estado']);
$sigla      = $campos_uf[0]['sigla'];

$pdf->SetFont('Arial', '', 11);
$pdf->SetLeftMargin(70);
$pdf->Ln(45);

$pdf->Cell(100, 5, $razao_social, 0, 1, 'L');
$pdf->Cell(100, 5, $endereco.', '.$num_complemento.' - '.$bairro, 0, 1, 'L');
$pdf->Cell(100, 5, $cep.' - '.$cidade.' - '.$sigla, 0, 1, 'L');

$pdf->Cell(100, 5, 'ATT.: '.strtoupper($_POST['txt_contato']).' - DEPTO. '.$_POST['cmb_departamento'], 0, 1, 'L');

chdir('../../../../../pdf');
$file='../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>