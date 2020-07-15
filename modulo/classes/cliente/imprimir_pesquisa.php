<?
require('../../../lib/pdf/fpdf.php');
require('../../../lib/segurancas.php');
require('../../../lib/data.php');//Não posso retirar pq a biblioteca financeiros, faz requisição da Biblioteca Data ...
require('../../../lib/financeiros.php');

function rotulos() {
    global $pdf;
    $pdf->SetLeftMargin(1);
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell($GLOBALS['ph']*23, 5, 'Cliente', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*28, 5, 'Endereço', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*17, 5, 'Cidade', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*7, 5, 'Tp Cliente', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*10, 5, 'Tel Comercial', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*3, 5, 'Cr', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*13, 5, 'CNPJ / CPF', 1, 1, 'C');
}

//INÍCIO PDF ...
define('FPDF_FONTPATH', 'font/');
$tipo_papel		= 'P';  // P=> Retrato L=>Paisagem
$unidade		= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel          = 'A4'; // A3, A4, A5, Letter, Legal
$pdf                    = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
$pdf->AddPage();
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
    echo 'Formato não Definido';
}

//Seleção dos Clientes
//O sql não está transcrito aki, porque veio como parâmetro do formulário de baixo
//Aqui é um tratamento para funcionar o sql
$sql    = StripSlashes($sql);
$campos = bancos::sql($sql);
$linhas = count($campos);

for($i = 0; $i < $linhas; $i++) {
    if($GLOBALS['nova_pagina'] == 'sim') {
        $GLOBALS['nova_pagina'] = 'nao';
        rotulos();
    }
    $pdf->SetLeftMargin(1);
    $pdf->SetFont('Arial', '', 7);
//Cliente
    if(!empty($campos[$i]['nomefantasia'])) {
        $pdf->Cell($GLOBALS['ph']*23, 5, $campos[$i]['nomefantasia'], 1, 0, 'L');
    }else {
        $pdf->Cell($GLOBALS['ph']*23, 5, $campos[$i]['razaosocial'], 1, 0, 'L');
    }
//Endereço
    if(!empty($campos[$i]['endereco'])) {//Daí sim printa o complemento
        $pdf->Cell($GLOBALS['ph']*28, 5, $campos[$i]['endereco'].', '.$campos[$i]['num_complemento'], 1, 0, 'L');
    }else {
        $pdf->Cell($GLOBALS['ph']*28, 5, '', 1, 0, 'L');
    }
//Cidade
    if(!empty($campos[$i]['cidade'])) {//Daí sim printa o complemento
        $pdf->Cell($GLOBALS['ph']*17, 5, $campos[$i]['cidade'], 1, 0, 'L');
    }else {
        $pdf->Cell($GLOBALS['ph']*17, 5, '', 1, 0, 'L');
    }
//Preço Unit. R$
    if($campos[$i]['tipo_cliente'] == 0) {
        $pdf->Cell($GLOBALS['ph']*7, 5, 'RA', 1, 0, 'C');
    }else if($campos[$i]['tipo_cliente'] == 1) {
        $pdf->Cell($GLOBALS['ph']*7, 5, 'RI', 1, 0, 'C');
    }else if($campos[$i]['tipo_cliente'] == 2) {
        $pdf->Cell($GLOBALS['ph']*7, 5, 'CO', 1, 0, 'C');
    }else if($campos[$i]['tipo_cliente'] == 3) {
        $pdf->Cell($GLOBALS['ph']*7, 5, 'ID', 1, 0, 'C');
    }else if($campos[$i]['tipo_cliente'] == 4) {
        $pdf->Cell($GLOBALS['ph']*7, 5, 'AT', 1, 0, 'C');
    }else if($campos[$i]['tipo_cliente'] == 5) {
        $pdf->Cell($GLOBALS['ph']*7, 5, 'DT', 1, 0, 'C');
    }else if($campos[$i]['tipo_cliente'] == 6) {
        $pdf->Cell($GLOBALS['ph']*7, 5, 'IT', 1, 0, 'C');
    }else if($campos[$i]['tipo_cliente'] == 7) {
        $pdf->Cell($GLOBALS['ph']*7, 5, 'FN', 1, 0, 'C');
    }
//Telefone Comercial
    if(!empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com'])) {
        $pdf->Cell($GLOBALS['ph']*10, 5, $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'], 1, 0, 'C');
    }
    if(!empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com'])) {
        $pdf->Cell($GLOBALS['ph']*10, 5, $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].$campos[$i]['telcom'], 1, 0, 'C');
    }
    if(empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com'])) {
        $pdf->Cell($GLOBALS['ph']*10, 5, $campos[$i]['ddi_com'].$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'], 1, 0, 'C');
    }
    if(empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com'])) {
        $pdf->Cell($GLOBALS['ph']*10, 5, $campos[$i]['telcom'], 1, 0, 'C');
    }
//Crédito
    $credito = financeiros::controle_credito($campos[$i]['id_cliente']);
    $pdf->Cell($GLOBALS['ph']*3, 5, $credito, 1, 0, 'C');
    
    //CPF / CNPJ
    if(!empty($campos[$i]['cnpj_cpf'])) {
        if(strlen($campos[$i]['cnpj_cpf']) == 11) {//CPF ...
            $cnpj_cpf = substr($campos[$i]['cnpj_cpf'], 0, 3).'.'.substr($campos[$i]['cnpj_cpf'], 3, 3).'.'.substr($campos[$i]['cnpj_cpf'], 6, 3).'-'.substr($campos[$i]['cnpj_cpf'], 9, 2);
        }else {//CNPJ ...
            $cnpj_cpf = substr($campos[$i]['cnpj_cpf'], 0, 2).'.'.substr($campos[$i]['cnpj_cpf'], 2, 3).'.'.substr($campos[$i]['cnpj_cpf'], 5, 3).'/'.substr($campos[$i]['cnpj_cpf'], 8, 4).'-'.substr($campos[$i]['cnpj_cpf'], 12, 2);
        }
    }else {
        $cnpj_cpf = '';
    }
    $pdf->Cell($GLOBALS['ph']*13, 5, $cnpj_cpf, 1, 1, 'C');
}

chdir('../../../pdf');
$file='../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>