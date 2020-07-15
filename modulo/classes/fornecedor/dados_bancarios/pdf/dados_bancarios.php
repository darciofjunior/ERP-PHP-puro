<?
require('../../../../../lib/pdf/fpdf.php');
if($itens == 1) {//Parâmetro que vem não sei de onde ??? - Dárcio 22/08/2013 ...
    require('../../../../../lib/segurancas.php');
}
session_start('funcionarios');
define('FPDF_FONTPATH','font/');

//Busca dados do Fornecedor passado por parâmetro ...
$sql = "SELECT f.`razaosocial`, f.`endereco`, f.`num_complemento`, f.`cep`, f.`cidade`, 
        f.`bairro`, f.`fone1`, f.`fone2`, f.`fax`, ufs.`sigla` 
        FROM `fornecedores` f 
        LEFT JOIN `ufs` ON ufs.`id_uf` = f.`id_uf` 
        WHERE f.`id_fornecedor` = '$_GET[id_fornecedor]' 
        AND f.`ativo` = '1' LIMIT 1 ";
$campos_fornecedor = bancos::sql($sql);
$razaosocial        = $campos_fornecedor[0]['razaosocial'];
$endereco           = $campos_fornecedor[0]['endereco'].', '.$campos_fornecedor[0]['num_complemento'];
$cep                = $campos_fornecedor[0]['cep'];
$cidade             = $campos_fornecedor[0]['cidade'];
$estado             = $campos_fornecedor[0]['sigla'];
$bairro             = $campos_fornecedor[0]['bairro'];
$fone1              = $campos_fornecedor[0]['fone1'];
if(empty($fone1))   $fone1 = '';
$fone2  = $campos_fornecedor[0]['fone2'];
if(empty($fone2))   $fone2 = '';
$fax    = $campos_fornecedor[0]['fax'];
if(empty($fax))     $fax = '';

/////////////////////// Geração do Relatório PDF ////////////////////////////////////
$pdf = new FPDF();
$pdf->Open();
$pdf->SetLeftMargin(1);
$pdf->AddPage();
$pdf->SetLeftMargin(1);
$pdf->SetFont('Arial','',12);
$pdf->Cell(208, 5, 'DADOS DE FORNECEDOR', 1, 1, 'C');
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(38, 5, 'FORNECEDOR:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(170, 5, $razaosocial, 0, 1, 'L');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(38, 5, 'ENDEREÇO:', 0, 0, 'R');
$pdf->SetFont('Arial','',12);
$pdf->Cell(170, 5, $endereco, 0, 1, 'L');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(38, 5, 'CEP:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(170, 5, $cep, 0, 1, 'L');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(38, 5, 'CIDADE:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(170, 5, $cidade, 0, 1, 'L');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(38, 5, 'ESTADO:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(170, 5, $estado, 0, 1, 'L');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(38, 5, 'BAIRRO:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(170, 5, $bairro, 0, 1, 'L');
$pdf->SetFont('Arial','B',12);
$pdf->Cell(38, 5, 'FONE 1:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(170, 5, $fone1, 0, 1, 'L');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(38, 5, 'FONE 2:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(170, 5, $fone2, 0, 1, 'L');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(38, 5, 'FAX:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(170, 5, $fax, 0, 1, 'L');
$pdf->Ln(5);
$pdf->SetFont('Arial','',12);
$pdf->Cell(208, 5, 'DADOS BANCÁRIOS', 1, 1, 'C');
$pdf->Ln(5);

//Busco todos os Dados Bancários cadastrados do id_fornecedor passado por parâmetro ...
$sql = "SELECT * 
        FROM `fornecedores_propriedades` 
        WHERE `id_fornecedor` = '$_GET[id_fornecedor]' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $bank       = $campos[$i]['banco'];
    if($bank == '0') $bank = '';
    $agencia    = $campos[$i]['agencia'];
    if($agencia == 0) $agencia = '';
    $conta_corrente = $campos[$i]['num_cc'];
    if(empty($conta_corrente)) $conta_corrente = '';
    $correntista = $campos[$i]['correntista'];
    if(empty($correntista)) $correntista = '';

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(38, 5, 'BANCO:', 0, 0, 'R');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(170, 5, $bank, 0, 1, 'L');
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(38, 5, 'AGÊNCIA:', 0, 0, 'R');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(170, 5, $agencia, 0, 1, 'L');
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(38, 5, 'No. C/C:', 0, 0, 'R');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(170, 5, $conta_corrente, 0, 1, 'L');
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(38, 5, 'CORRENTISTA:', 0, 0, 'R');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(170, 5, $correntista, 0, 1, 'L');
    $pdf->Ln(5);
}
chdir('../../../../../pdf');
$file='../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><SCRIPT>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>