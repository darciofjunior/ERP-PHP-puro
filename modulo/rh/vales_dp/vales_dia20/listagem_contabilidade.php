<?
require('../../../../lib/pdf/fpdf.php');
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../../');
//error_reporting(0);

/////////////////////////////////////// INÍCIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel	= 'P';  // P=> Retrato L=>Paisagem
$unidade	= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= 'A4'; // A3, A4, A5, Letter, Legal
$pdf            = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->SetLeftMargin(20);
$pdf->SetTopMargin(12);
$pdf->Open();

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

/*****Se o usuário não selecionou nenhuma Empresa ou selecionou a empresa Grupo, então nesse relatório só irá aparecer
funcionários das empresas Albafer e Tool Master. Como essa listagem vai p/ o Claudinei, ele nunca pode enxergar Funcs da Empresa Grupo*****/
if(empty($_GET['cmb_empresa']) || $_GET['cmb_empresa'] == 4) {
    $condicao_empresa = " IN (1, 2) ";
}else if($_GET['cmb_empresa'] == 1) {
    $condicao_empresa = " = '1' ";
}else if($_GET['cmb_empresa'] == 2) {
    $condicao_empresa = " = '2' ";
}

/*Aqui eu busco todos os Funcionários que não foram Demitidos e que possuem vales na Data de Holerith passada por parâmetro, 
mas somente das Empresas Albafer e Tool Master p/ enviar p/ o Claudinei ...*/
$sql = "SELECT e.`nomefantasia`, f.`nome`, vd.`valor` AS salario_pd 
        FROM `funcionarios` f 
        INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
        INNER JOIN `vales_dps` vd ON vd.`id_funcionario` = f.`id_funcionario` AND vd.`data_debito` = '$_GET[cmb_data_holerith]' AND vd.`tipo_vale` = '1' AND vd.`descontar_pd_pf` = 'PD' 
        WHERE f.`id_empresa` $condicao_empresa 
        AND f.`status` <= '2' 
        ORDER BY e.`nomefantasia`, f.`nome` ";
$campos         = bancos::sql($sql);
$linhas         = count($campos);
$empresa_atual  = '';

for($i = 0; $i < $linhas; $i++) {
    //Sempre que o sistema trocar o nome da Empresa, esse nome só será exibido apenas 1 única vez ...
    if($empresa_atual != $campos[$i]['nomefantasia']) {
        $pdf->AddPage();//Abre uma Nova Página ...
        $pdf->SetFont('Arial', 'B', 12);
        
        $pdf->Cell($GLOBALS['ph']*90, 6, $campos[$i]['nomefantasia'], 1, 1, 'C');
        $pdf->Cell($GLOBALS['ph']*65, 6, 'Funcionário', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph']*25, 6, 'Adiantamento 40%', 1, 1, 'C');

        $empresa_atual = $campos[$i]['nomefantasia'];
    }
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell($GLOBALS['ph']*65, 6, $campos[$i]['nome'], 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*25, 6, 'R$ '.number_format($campos[$i]['salario_pd'], 2, ',', '.'), 1, 1, 'R');
}

chdir('../../../../pdf');
$file='../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>