<?
require('../../../../lib/pdf/fpdf.php');
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
require('../../../../lib/num_por_extenso_em_rs.php');
segurancas::geral('/erp/albafer/modulo/rh/funcionario/licensa_funcionario.php', '../../../');
error_reporting(1);

/////////////////////////////////////// INÍCIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel	= "P";  // P=> Retrato L=>Paisagem
$unidade	= "mm"; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= "A4"; // A3, A4, A5, Letter, Legal
$pdf=new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
$pdf->SetTopMargin(4);
$pdf->SetLeftMargin(10);
$pdf->SetAutoPageBreak('false', 0);
$pdf->SetFont('Arial', '', 10);

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

//Aqui eu garanto que foi passado o parâmetro de Datas ...
if(!empty($datas)) {
//Aqui eu transformo as Datas selecionadas pelo usuário na Tela abaixo em Array ...
	$array_datas = explode(',', $datas);
	$qtde_datas = count($array_datas); 
	if(count($array_datas) == 1) {//Significa que o array está com apenas 1 elemento ...
            $datas_apresentar = $array_datas[0]; 
	}else {//O array está com mais de 1 elemento ...
            for($i = 0; $i < $qtde_datas; $i++) $datas_apresentar.= $array_datas[$i].' - ';
            $datas_apresentar = substr($datas_apresentar, 0, strlen($datas_apresentar) - 3);
	}
}

//Criei esse vetor p/ facilitar na apresentação do mês abaixo ...
$vetor_meses = array('', 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro');

$sql = "SELECT e.nomefantasia, f.nome 
        FROM `funcionarios` f 
        INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa 
        WHERE f.`id_funcionario` IN ($_POST[hdd_funcs_selecionados]) ORDER BY e.id_empresa, f.nome ";
$campos = bancos::sql($sql);
$linhas = count($campos);

for($i = 0; $i < $linhas; $i++) {
//Só imprime 3 funcionários por folha ...
    if($i % 3 == 0) $pdf->AddPage();
//Empresa ...
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell($GLOBALS['ph']*93, 4, '', 'LRT', 1, 'L');
    $pdf->Cell($GLOBALS['ph']*93, 5, 'Para: '.$campos[$i]['nomefantasia'], 'LR', 1, 'L');
    $pdf->Cell($GLOBALS['ph']*93, 6, '', 'LR', 1, 'L');
//Data Atual ...
    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell($GLOBALS['ph']*93, 5, 'São Paulo, '.date('d').' de '.$vetor_meses[(int)date('m')].' de '.date('Y').'.', 'LR', 1, 'L');
    $pdf->Cell($GLOBALS['ph']*93, 6, '', 'LR', 1, 'L');
//Texto ...
    $pdf->SetFont('Arial', 'BU', 14);
//Se estiver selecionada essa opção, então printo na parte de licensas esse texto a mais ...
    if(!empty($chkt_meio_dia)) {//Controle p/ apenas meio dia ...
        $apresentar_numero = '½';
        $apresentar_extenso = '½ (meio)';
    }else {//Mais de um dia ...
        $apresentar_numero = $qtde_datas;
        $apresentar_extenso = $qtde_datas.' '.extenso($qtde_datas, '', 0);
    }
    $pdf->MultiCell($GLOBALS['ph']*93, 5, 'Licença Referente: Falta de '.$apresentar_numero.' dia(s) Ref: '.$datas_apresentar, 'LR', 1, 'C');
    $pdf->Cell($GLOBALS['ph']*93, 6, '', 'LR', 1, 'L');
    $pdf->Cell($GLOBALS['ph']*93, 4, 'Motivo: '.$_POST['txt_motivo'], 'LR', 1, 'L');
    $pdf->Cell($GLOBALS['ph']*93, 6, '', 'LR', 1, 'L');
//Texto 2 ...
    $pdf->SetFont('Arial', '', 14);
    $pdf->MultiCell($GLOBALS['ph']*93, 5, 'Venho por meio desta, solicitar licença de '.$apresentar_extenso.' dia(s) ref.: as faltas citadas a cima, licença esta a ser descontada nas próximas férias, banco de horas ou na eventual rescisão do contrato de trabalho.', 'LR', 1, 'L');
    $pdf->Cell($GLOBALS['ph']*93, 6, '', 'LR', 1, 'L');
//Texto 3 ...
    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell($GLOBALS['ph']*93, 5, 'Sem mais, ', 'LR', 1, 'L');
    $pdf->Cell($GLOBALS['ph']*93, 5, 'Agradeço Antecipadamente.', 'LR', 1, 'L');
    $pdf->Cell($GLOBALS['ph']*93, 4, '', 'LR', 1, 'L');
//Assinatura do Funcionário ...
    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell($GLOBALS['ph']*93, 5, '________________________________________________', 'LR', 1, 'L');
    $pdf->Cell($GLOBALS['ph']*93, 5, strtoupper($campos[$i]['nome']), 'LR', 1, 'L');
    $pdf->Cell($GLOBALS['ph']*93, 4, '', 'LRB', 1, 'L');
    $pdf->Ln(4);
}

chdir('../../../../pdf');
$file='../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>