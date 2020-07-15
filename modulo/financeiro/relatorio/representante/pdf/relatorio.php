<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
require('../../../../../lib/financeiros.php');
require('../../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/financeiro/relatorio/representante/consultar.php', '../../../../');

function tabela($representante) {
    global $pdf;
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(244, 5, 'Contas do Representante '.$representante, 'LTB', 0, 'C');
    $pdf->Cell(48, 5, 'Data de Impressão '.date('d/m/Y'), 'BTR', 1, 'R');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(10, 5, 'Sem', 1, 0, 'C');
    $pdf->Cell(80, 5, 'Cliente', 1, 0, 'C');
    $pdf->Cell(10, 5, 'CR', 1, 0, 'C');
    $pdf->Cell(22, 5, 'Data Venc.', 1, 0, 'C');
    $pdf->Cell(40, 5, 'Tipo Recebimento', 1, 0, 'C');
    $pdf->Cell(40, 5, 'Praça Recebimento', 1, 0, 'C');
    $pdf->Cell(30, 5, 'Valor', 1, 0, 'C');
    $pdf->Cell(30, 5, 'Valor Recebido', 1, 0, 'C');
    $pdf->Cell(30, 5, 'Valor Extra', 1, 1, 'C');
}

define('FPDF_FONTPATH', 'font/');
$tipo_papel     = 'L';  // P=> Retrato L=>Paisagem
$unidade        = 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel  = 'A4'; // A3, A4, A5, Letter, Legal
$pdf            = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
$pdf->SetLeftMargin(20);
$pdf->AddPage();

if(!empty($_POST['txt_data_inicial'])) {
    $data_inicial   = data::datatodate($_POST['txt_data_inicial'], '-');
    $data_final     = data::datatodate($_POST['txt_data_final'], '-');

    if($_POST['opt_data'] == 1) {//Data de Emissão ...
        $condicao = " AND cr.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' ";
    }else if($_POST['opt_data'] == 2) {//Data de Vencimento ...
        $condicao = " AND cr.`data_vencimento` BETWEEN '$data_inicial' AND '$data_final' ";
    }
}

//Busca o Dólar e Euro do Dia p/ o caso das Contas Estrangeiras ...
$valor_dolar_dia = genericas::moeda_dia('dolar'); 
$valor_euro_dia = genericas::moeda_dia('euro'); 

$sql = "SELECT r.nome_representante, cr.id_conta_receber, cr.semana, cr.data_vencimento, cr.valor, cr.valor_pago, c.razaosocial, c.credito, tr.recebimento, 
        CONCAT(tm.simbolo, ' ') AS simbolo, tm.id_tipo_moeda 
        FROM `representantes` r 
        INNER JOIN `contas_receberes` cr ON cr.id_representante = r.id_representante AND r.`id_representante` = '$_POST[cmb_representante]' $condicao 
        INNER JOIN `clientes` c ON c.id_cliente = cr.id_cliente 
        INNER JOIN `tipos_recebimentos` tr ON tr.id_tipo_recebimento = cr.id_tipo_recebimento 
        INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = cr.id_tipo_moeda ORDER BY cr.data_vencimento DESC ";
$campos = bancos::sql($sql);
$linhas = count($campos);

tabela($campos[0]['nome_representante']);

$contador_registro = 0;
$pagina = 1;
$soma_valores_extras = 0;
$somatoria_valor = 0;
$somatoria_valor_pago = 0;

for($i = 0; $i < $linhas; $i++) {
    if($contador_registro == 31) {
        $pdf->Cell(280, 5, $pagina, 0, 1, 'C');
        $pagina++;
        $pdf->AddPage();
        tabela($campos[0]['nome_representante']);
        $contador_registro = 0;
    }
    $pdf->Cell(10, 5, $campos[$i]['semana'], 1, 0, 'C');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(80, 5, $campos[$i]['razaosocial'], 1, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(10, 5, $campos[$i]['credito'], 1, 0, 'C');
    $pdf->Cell(22, 5, data::datetodata($campos[$i]['data_vencimento'], '/'), 1, 0, 'C');
    $pdf->Cell(40, 5, $campos[$i]['recebimento'], 1, 0, 'C');
    
    //Verifica se Existe Banco p/ a Conta à Receber ...
    $sql = "SELECT b.banco 
            FROM `contas_receberes` cr 
            INNER JOIN `bancos` b ON b.id_banco = cr.id_banco 
            WHERE cr.`id_conta_receber` = '".$campos[$i]['id_conta_receber']."' LIMIT 1 ";
    $campos_bancos = bancos::sql($sql);
    if(count($campos_bancos) > 0) {
        $banco = $campos_bancos[0]['banco'];
    }else {
        $banco = ($campos[$i]['id_tipo_recebimento'] == 7) ? 'Protestado' : '';
    }
    $pdf->Cell(40, 5, $banco, 1, 0, 'C');
//Valores à Receber ...
    if($campos[$i]['valor'] == '0.00') {
        $valor = 0;
    }else {
        if($campos[$i]['id_tipo_moeda'] == 2) {
            $valor = $campos[$i]['valor'] * $valor_dolar_dia;
        }else if($campos[$i]['id_tipo_moeda'] == 3) {
            $valor = $campos[$i]['valor'] * $valor_euro_dia;
        }else {
            $valor = $campos[$i]['valor'];
        }
        $somatoria_valor+= $valor;
    }
    $pdf->Cell(30, 5, $campos[$i]['simbolo'].' '.number_format($valor, 2, ',', '.'), 1, 0, 'R');   
//Valores Recebidos ...
    if($campos[$i]['id_tipo_moeda'] == 2) {
        $valor_pago = $campos[$i]['valor_pago'] * $valor_dolar_dia;
    }else if($campos[$i]['id_tipo_moeda'] == 3) {
        $valor_pago = $campos[$i]['valor_pago'] * $valor_euro_dia;
    }else {
        $valor_pago = $campos[$i]['valor_pago'];
    }
    $somatoria_valor_pago+= $valor_pago;
    $pdf->Cell(30, 5, $campos[$i]['simbolo'].' '.number_format($campos[$i]['valor_pago'], 2, ',', '.'), 1, 0, 'R');
//Valores Extras ...
    $calculos_conta_receber = financeiros::calculos_conta_receber($campos[$i]['id_conta_receber']);
    $pdf->Cell(30, 5, $campos[$i]['simbolo'].' '.number_format($calculos_conta_receber['valores_extra'], 2, ',', '.'),1,1,"R");
    $soma_valores_extras+= $calculos_conta_receber['valores_extra'];
    
    $contador_registro++;
}

$pdf->Cell(202, 5, '', 1, 0, 'R');
$pdf->Cell(30, 5, 'R$ '.number_format($somatoria_valor, 2, ',', '.'), 1, 0, 'R');
$pdf->Cell(30, 5, 'R$ '.number_format($somatoria_valor_pago, 2, ',', '.'), 1, 0, 'R');

$calculo_extra = $somatoria_valor - $somatoria_valor_pago + $soma_valores_extras;
$pdf->Cell(30, 5, 'R$ '.number_format($calculo_extra, 2, ',', '.'), 1, 0, 'R');

if($contador_registro < 31) {
    for($i = $contador_registro; $i < 31; $i++) $pdf->Cell(280, 5, '', 0, 1, 'C');
    $pdf->Cell(280, 5, $pagina, 0, 1, 'C');
}
chdir('../../../../../pdf');
//$file='../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><SCRIPT>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>