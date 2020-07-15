<?
session_start('funcionarios');
require('../../../../lib/pdf/fpdf.php');
require('../../../../lib/segurancas.php');

/////////////////////////////////////// INÍCIO PDF ///////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');

$tipo_papel     = 'P';  // P=> Retrato L=>Paisagem
$unidade        = 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= 'A4'; // A3, A4, A5, Letter, Legal
$pdf            = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
$pdf->SetTopMargin(0);
$pdf->SetAutoPageBreak('false', 0);
$pdf->AddPage();

/****************************************Dados do Destinatário****************************************/
if(!empty($_GET['id_nf'])) {
    $sql = "SELECT nfs.`id_cliente`, nfs.`id_transportadora`, nfsi.`id_representante`, t.`nome` 
            FROM `nfs` 
            INNER JOIN `transportadoras` t ON t.`id_transportadora` = nfs.`id_transportadora` 
            INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
            WHERE nfs.`id_nf` = '$_GET[id_nf]' LIMIT 1 ";
    $campos_nfs         = bancos::sql($sql);
    $id_cliente         = $campos_nfs[0]['id_cliente'];
    $id_transportadora 	= $campos_nfs[0]['id_transportadora'];
    $id_representante 	= $campos_nfs[0]['id_representante'];
    $transportadora 	= $campos_nfs[0]['nome'];
}

$sql = "SELECT `nomefantasia`, `razaosocial`, `endereco`, `num_complemento`, `bairro`, `cep`, `cidade`, `id_uf` 
        FROM `clientes` 
        WHERE `id_cliente` = '$id_cliente' 
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
$id_uf          = $campos[0]['id_uf'];

$sql = "SELECT `estado`, `sigla` 
        FROM `ufs` 
        WHERE `id_uf` = '$id_uf' LIMIT 1 ";
$campos_uf      = bancos::sql($sql);
$estado         = strtoupper($campos_uf[0]['estado']);
$sigla          = $campos_uf[0]['sigla'];
/***********************************************************************************************/
$pdf->SetLeftMargin(1);
$pdf->Ln(5);

if(isset($id_transportadora)) {
//797 - Sedex, 849 - Correio Encomenda Simples, 1050 - Correio Encomenda P.A.C., 1092 - Correio Sedex 10 ...
    if($id_transportadora == 797 || $id_transportadora == 849 || $id_transportadora == 1050 || $id_transportadora == 1092) {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(100, 5, $transportadora, 0, 1, 'L');
    }
}

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(100, 5, 'DESTINATÁRIO', 0, 1, 'L');
$pdf->Cell(100, 5, $razao_social, 0, 1, 'L');

$pdf->Cell(100, 5, $endereco.', '.$num_complemento.' - '.$bairro, 0, 1, 'L');
$pdf->Cell(100, 5, $cep.' - '.$cidade.' - '.$sigla, 0, 1, 'L');

$pdf->Ln(10);
$pdf->Cell(100, 5, '----------------------------------------------------------------------------------------------------------------------------------------------------------------------', 0, 1, 'L');
$pdf->Ln(10);

/****************************************Dados do Remetente****************************************/
//Se o Remetente for Albafer ou Tool Master, então eu busco os dados da Empresa selecionada no Remetente ...
if($remetente_emp == 1 || $remetente_emp == 2) {
    $sql = "SELECT e.`razaosocial`, e.`endereco`, e.`numero`, e.`complemento`, e.`bairro`, e.`cep`, 
            e.`cidade`, u.`sigla` 
            FROM `empresas` e 
            INNER JOIN `ufs` u ON u.`id_uf` = e.`id_uf` 
            WHERE e.`id_empresa` = '$remetente_emp' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $razao_social   = strtoupper($campos[0]['razaosocial']);
    $endereco       = strtoupper($campos[0]['endereco']);
    $numero         = $campos[0]['numero'];
    if(!empty($campos[0]['complemento'])) $complemento = ' - '.strtoupper($campos[0]['complemento']);
    $bairro         = strtoupper($campos[0]['bairro']);
    $cep            = $campos[0]['cep'];
    $cidade         = strtoupper($campos[0]['cidade']);
    $sigla          = strtoupper($campos[0]['sigla']);

    $pdf->Cell(100, 5, 'REMETENTE', 0, 1, 'L');
    $pdf->Cell(100, 5, $razao_social, 0, 1, 'L');
    $pdf->Cell(100, 5, $endereco.', '.$numero.$complemento.' - '.$bairro, 0, 1, 'L');
    $pdf->Cell(100, 5, $cep.' - '.$cidade.' - '.$sigla, 0, 1, 'L');
//Se o Remetente for Grupo, então o endereço vai ser um outro especificado ...
}else {
    $sql = "SELECT UPPER(`nome_representante`) AS representante 
            FROM `representantes` 
            WHERE `id_representante` = '$id_representante' LIMIT 1 ";
    $campos_rep = bancos::sql($sql);
    $pdf->Cell(100, 5, 'REMETENTE', 0, 1, 'L');
    $pdf->Cell(100, 5, $campos_rep[0]['representante'], 0, 1, 'L');
    $pdf->Cell(100, 5, 'R. DIAS DA SILVA. Nº 1183', 0, 1, 'L');
    $pdf->Cell(100, 5, 'CEP: 02114-002 - VILA MARIA - SP', 0, 1, 'L');
}

chdir('../../../../pdf');
$file='../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>