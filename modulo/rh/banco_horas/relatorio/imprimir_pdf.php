<?
require('../../../../lib/pdf/fpdf.php');
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/rh/banco_horas/relatorio.php', '../../../../');

function rotulo() {
    global $pdf;
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell($GLOBALS['ph'] * 70, 5, 'Nome', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 20, 5, 'Qtde Hora(s)', 1, 1, 'C');
}

/////////////////////////////////////// INÍCIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel	= 'P';  // P=> Retrato L=>Paisagem
$unidade	= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= 'A4'; // A3, A4, A5, Letter, Legal
$pdf            = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
$pdf->SetTopMargin(4);
$pdf->SetLeftMargin(15);
$pdf->SetFont('Arial', '', 10);
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

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell($GLOBALS['ph'] * 90, 5, 'Relatório de Banco de Hora(s)', 1, 1, 'C');

if(!empty($_POST['cmb_chefe'])) $condicao_chefes = ($_POST['cmb_chefe'][0] > 0) ? " AND f.`id_funcionario_superior` IN (".implode(',', $_POST['cmb_chefe']).") " : '';

//Aqui eu só listo os funcionários que possuem Banco de Horas e que ainda estejam trabalhando na Empresa ...
$sql = "SELECT TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(bh.`qtde_horas`))), '%H:%i') AS qtde_horas, f.`nome` 
        FROM `bancos_horas` bh 
        INNER JOIN `funcionarios` f ON f.`id_funcionario` = bh.`id_funcionario` AND f.`nome` LIKE '%$_POST[txt_nome]%' AND f.`status` < '3' $condicao_chefes 
        GROUP BY f.`id_funcionario` ORDER BY f.`nome` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    if($GLOBALS['nova_pagina'] == 'sim') {
        $GLOBALS['nova_pagina'] = 'nao';
        rotulo();
    }
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell($GLOBALS['ph'] * 70, 5, $campos[$i]['nome'], 1, 0, 'L');  
    $pdf->Cell($GLOBALS['ph'] * 20, 5, $campos[$i]['qtde_horas'], 1, 1, 'C');
}

chdir('../../../../pdf');
$file='../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>