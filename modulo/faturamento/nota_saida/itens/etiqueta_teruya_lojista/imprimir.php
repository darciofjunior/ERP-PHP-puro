<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/intermodular.php');
require('../../../../../lib/data.php');
error_reporting(1);

/////////////////////////////////////// INÍCIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel		= 'P';  // P=> Retrato L=>Paisagem
$unidade		= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= 'etiqueta_clientes'; //PIMACO 8923 ...
$pdf = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->SetTopMargin(7);
$pdf->SetLeftMargin(13);
$pdf->Open();
$pdf->AddPage();
global $pv,$ph; //valor baseado em mm do A4
if($formato_papel == 'etiqueta_clientes') {
    if($tipo_papel == 'P') {
        $pv = 295/100;
        $ph = 205/100;
    }else {
        $pv = 205/100;
        $ph = 295/100;
    }
}else {
    echo 'Formato não definido.';
}
$pdf->SetFont('Arial', '', 8);

$linha_atual 	= 1;//Valor Default ...
$linhas         = count($_POST['txt_qtde_total']);

for($i = 0; $i < $linhas; $i+=2) {
    //Posso ter apenas 12 linhas em uma página, quando chegar na 13ª eu mando inserir uma nova página ...
    if($linha_atual == 13) {
        $linha_atual = 1;
        $pdf->SetTopMargin(7);
        $pdf->AddPage();
        $espacador_entre_linhas = 0;
    }
    //Aqui representa a Coluna da Esquerda ...
    $pdf->Cell(100, 5, substr($_POST['txt_qtde_total'][$i].' - '.intermodular::pa_discriminacao($_POST['hdd_produto_acabado'][$i], 0, 0, 0, 0, 1), 0, 60), 0, 0, 'L');
    //Aqui representa a Coluna da Direita ...
    $pdf->Cell(100, 5, substr($_POST['txt_qtde_total'][$i + 1].' - '.intermodular::pa_discriminacao($_POST['hdd_produto_acabado'][$i + 1], 0, 0, 0, 0, 1), 0, 60), 0, 1, 'L');
    
    $pdf->Ln(5);
    $linha_atual++;//A cada linha de Etiqueta que vai sendo printada, eu vou somando nessa variável ...
    if($linha_atual <= 2) {
        $espacador_entre_linhas = 15;
    }else if($linha_atual <= 4) {
        $espacador_entre_linhas = 17;
    }else if($linha_atual <= 6) {
        $espacador_entre_linhas = 19;
    }else if($linha_atual <= 8) {
        $espacador_entre_linhas = 18;
    }else {
        $espacador_entre_linhas = 17.5;
    }
    $pdf->Ln($espacador_entre_linhas);
}
chdir('../../../../../pdf');
//$file='../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>