<?
require('../../../../../../../lib/pdf/fpdf.php');
require('../../../../../../../lib/segurancas.php');
require('../../../../../../../lib/data.php');
require('../../../../../../../lib/genericas.php');

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/cheque_cliente/classes/manipular/opcoes.php?id_emp2=1';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/cheque_cliente/classes/manipular/opcoes.php?id_emp2=2';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/cheque_cliente/classes/manipular/opcoes.php?id_emp2=4';
}
segurancas::geral($endereco, '../../../../../../../');

function tabela($nome_fantasia) {
    global $pdf;
    $pdf->SetLeftMargin(10);
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(0, 15, 155);
    $pdf->Cell(280, 5, 'Relatório de Cheques à Compensar => '.$nome_fantasia, 1, 1, 'C');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(25, 5, 'N.º Cheque', 1, 0, 'C');
    $pdf->Cell(35, 5, 'Banco', 1, 0, 'C');
    $pdf->Cell(90, 5, 'Cliente', 1, 0, 'C');
    $pdf->Cell(80, 5, 'Correntista', 1, 0, 'C');
    $pdf->Cell(25, 5, 'Data de Venc.', 1, 0, 'C');
    $pdf->Cell(25, 5, 'Valor', 1, 1, 'C');
}

define('FPDF_FONTPATH','font/');

$pdf = new FPDF();
$pdf->FPDF('L');
$pdf->Open();
$pdf->SetLeftMargin(1);
$pdf->AddPage();

//Busca o Nome Fantasia da Empresa do Menu ...
$nome_fantasia  = genericas::nome_empresa($id_emp);

$sql = "SELECT DISTINCT(cc.id_cheque_cliente), cc.*, IF(c.nomefantasia = '', c.razaosocial, c.nomefantasia) AS cliente 
        FROM `cheques_clientes` cc 
        INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
        INNER JOIN `contas_receberes_quitacoes` crq ON crq.`id_cheque_cliente` = cc.`id_cheque_cliente` 
        INNER JOIN `contas_receberes` cr ON cr.`id_conta_receber` = crq.`id_conta_receber` 
        WHERE cc.`num_cheque` LIKE '%$_GET[txt_consultar]%' 
        AND cc.`status` = '1' 
        AND cc.`ativo` = '1' 
        AND cc.`valor_disponivel` = '0' 
        AND cr.`id_empresa` = '$id_emp' ORDER BY cc.data_vencimento ";
$campos = bancos::sql($sql);
$linhas = count($campos);

//Geração do corpo do PDF ...
tabela($nome_fantasia);
$contador_registro = 0;
$pagina = 1;

for($i = 0;$i < $linhas; $i++) {
    if($contador_registro > 29) {
        $pdf->Cell(292, 5, $pagina, 0, 1, 'C');
        $pagina++;
        $pdf->AddPage();
        $contador_registro = 0;
        tabela($nome_fantasia);
    }
    $pdf->Cell(25, 5, $campos[$i]['num_cheque'], 1, 0, 'C');
    $pdf->Cell(35, 5, $campos[$i]['banco'], 1, 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(90, 5, $campos[$i]['cliente'], 1, 0, 'L');

    $pdf->SetFont('Arial', '', 9);
    if(strlen($campos[$i]['correntista']) > 37) {
        $pdf->Cell(80, 5, substr($campos[$i]['correntista'], 0, 37).'...', 1, 0, 'L');
    }else {
        $pdf->Cell(80, 5, $campos[$i]['correntista'], 1, 0, 'L');
    }
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(25, 5, data::datetodata($campos[$i]['data_vencimento'], '/'), 1, 0, 'C');
    $pdf->Cell(25, 5, 'R$ '.number_format($campos[$i]['valor'], 2, ',', '.'), 1, 1, 'R');
    $contador_registro ++;

    $total_compensar+= $campos[$i]['valor'];
}

$pdf->SetTextColor(35, 0, 155);
$pdf->Cell(250, 5, 'Valor Total: ', 'TBL', 0, 'R');
$pdf->Cell(30, 5, 'R$ '.number_format($total_compensar, 2, ',', '.'), 'TBR', 1, 'R');
$pdf->SetTextColor(0, 0, 0);

$paginacao = 28 - $contador_registro;

for($i = 0;$i < $paginacao; $i++) $pdf->Cell(1, 5, '', 0, 1);

$pdf->Cell(280, 5, $pagina, 0, 1, 'C');
//Geração do Relatório
chdir('../../../../../../../pdf');
$file='../../../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>