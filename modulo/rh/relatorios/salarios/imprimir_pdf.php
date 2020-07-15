<?
require('../../../../lib/pdf/fpdf.php');
require('../../../../lib/segurancas.php');

segurancas::geral('/erp/albafer/modulo/rh/vales/itens/consultar.php', '../../../../');
//error_reporting(0);

/////////////////////////////////////// INÍCIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel	= 'L';  // P=> Retrato L=>Paisagem
$unidade	= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= 'A4'; // A3, A4, A5, Letter, Legal
$pdf            = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->SetLeftMargin(6);
$pdf->SetTopMargin(4);
$pdf->Open();
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

//Listagem de Funcionários independente da Empresa em "Férias ou Ativo" ...

/*Só não exibo os funcionários Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes não são funcionários, simplesmente só possuem cadastrado 
no Sistema p/ poder acessar algumas telas ...*/
$sql = "SELECT c.cargo, e.nomefantasia, f.nome, DATE_FORMAT(f.data_admissao, '%d/%m/%Y') AS data_admissao, f.tipo_salario, f.salario_pd, f.salario_pf, f.salario_premio 
        FROM `funcionarios` f 
        INNER JOIN `cargos` c ON c.id_cargo = f.id_cargo 
        INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa 
        WHERE f.`status` < '2' 
        AND f.`id_departamento` LIKE '$_GET[cmb_departamento]' 
        AND f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) ORDER BY e.id_empresa, f.nome ";
$campos                     = bancos::sql($sql);
$linhas                     = count($campos);
$empresa_atual              = '';
$total_funcs_por_empresa    = 0;
$total_salarial_por_empresa = 0;

for($i = 0; $i < $linhas; $i++) {
    //Sempre que o sistema trocar o nome da Empresa, esse nome só será exibido apenas 1 única vez ...
    if($empresa_atual != $campos[$i]['nomefantasia']) {
        $pdf->SetFont('Arial', 'B', 12);
        
        $pdf->Cell($GLOBALS['ph']*108, 6, 'Empresa: '.$campos[$i]['nomefantasia'], 1, 1, 'L');
        $pdf->Cell($GLOBALS['ph']*28, 6, 'Funcionário', 'LTR', 0, 'C');
        $pdf->Cell($GLOBALS['ph']*25, 6, 'Cargo', 'LTR', 0, 'C');
        $pdf->Cell($GLOBALS['ph']*10, 6, 'Data Adm', 'LTR', 0, 'C');
        $pdf->Cell($GLOBALS['ph']*5, 6, 'Tipo', 'LTR', 0, 'C');
        $pdf->Cell($GLOBALS['ph']*40, 6, 'Salário R$', 1, 1, 'C');
        
        $pdf->Cell($GLOBALS['ph']*28, 6, '', 'LBR', 0, 'C');
        $pdf->Cell($GLOBALS['ph']*25, 6, '', 'LBR', 0, 'C');
        $pdf->Cell($GLOBALS['ph']*10, 6, '', 'LBR', 0, 'C');
        $pdf->Cell($GLOBALS['ph']*5, 6, '', 'LBR', 0, 'C');
        $pdf->Cell($GLOBALS['ph']*8, 6, 'PD', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph']*8, 6, 'PF', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph']*8, 6, 'Prêmio', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph']*8, 6, 'Total', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph']*8, 6, 'Mensal', 1, 1, 'C');

        $empresa_atual              = $campos[$i]['nomefantasia'];
        $total_funcs_por_empresa    = 0;
        $total_salarial_por_empresa = 0;
    }
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell($GLOBALS['ph']*28, 6, $campos[$i]['nome'], 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*25, 6, ucfirst(strtolower($campos[$i]['cargo'])), 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*10, 6, $campos[$i]['data_admissao'], 1, 0, 'C');
    
    $tipo_salario = ($campos[$i]['tipo_salario'] == 1) ? 'Hs' : 'M';
    
    $pdf->Cell($GLOBALS['ph']*5, 6, $tipo_salario, 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*8, 6, number_format($campos[$i]['salario_pd'], 2, ',', '.'), 1, 0, 'R');
    $pdf->Cell($GLOBALS['ph']*8, 6, number_format($campos[$i]['salario_pf'], 2, ',', '.'), 1, 0, 'R');
    $pdf->Cell($GLOBALS['ph']*8, 6, number_format($campos[$i]['salario_premio'], 2, ',', '.'), 1, 0, 'R');
    $pdf->Cell($GLOBALS['ph']*8, 6, number_format($campos[$i]['salario_pd'] + $campos[$i]['salario_pf'] + $campos[$i]['salario_premio'], 2, ',', '.'), 1, 0, 'R');
    
    if($campos[$i]['tipo_salario'] == 1) {//Horista ...
        $salario = ($campos[$i]['salario_pd'] + $campos[$i]['salario_pf'] + $campos[$i]['salario_premio']) * 220;
    }else {//Mensalista ...
        $salario = ($campos[$i]['salario_pd'] + $campos[$i]['salario_pf'] + $campos[$i]['salario_premio']);
    }
    $pdf->Cell($GLOBALS['ph']*8, 6, number_format($salario, 2, ',', '.'), 1, 1, 'R');
    
    $total_funcs_por_empresa++;
    $total_salarial_por_empresa+= $salario;
    $total_funcs_geral++;
    $total_salarial_geral+= $salario;
    
    //Aqui eu verifico se é o último registro da Empresa atual ...
    if($campos[$i]['nomefantasia'] != $campos[$i + 1]['nomefantasia']) {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell($GLOBALS['ph']*68, 6, 'Total de Funcionário(s) da Empresa: '.$total_funcs_por_empresa, 1, 0, 'L');
        $pdf->Cell($GLOBALS['ph']*40, 6, 'Total Salarial da Empresa: '.number_format($total_salarial_por_empresa, 2, ',', '.'), 1, 1, 'R');
    }
}
$pdf->Cell($GLOBALS['ph']*68, 6, 'Total de Funcionário(s) Geral: '.$total_funcs_geral, 1, 0, 'L');
$pdf->Cell($GLOBALS['ph']*40, 6, 'Total Salarial Geral: '.number_format($total_salarial_geral, 2, ',', '.'), 1, 1, 'R');

chdir('../../../../pdf');
$file='../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>