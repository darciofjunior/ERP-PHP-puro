<?
require('../../../../../../lib/pdf/fpdf.php');
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/financeiros.php');
require('../../../../../../lib/genericas.php');
session_start('funcionarios');

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}else if($id_emp == 0) {//Todas Empresas
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/todas_empresas/index.php';
}
segurancas::geral($endereco, '../../../../../../');

function tabela($empresa) {
    global $pdf;
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(0, 15, 155);
    $pdf->Cell($GLOBALS['ph'] * 65, 5, 'Relatório de Contas à Receber: '.$empresa, 'TBL', 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 35, 5, 'Data da Impressão: '.date('d/m/Y H:i:s'), 'TBR', 1, 'L');
    
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell($GLOBALS['ph'] * 3, 5, 'Sem.', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 6, 5, 'Nº / Conta', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 29, 5, 'Cliente', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 11, 5, 'Represent. / Superv.', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 8, 5, 'Data Venc. Alt.', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 7, 5, 'Data de Rec.', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 10, 5, 'Tipo de Rec.', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 8, 5, 'Valor', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 9, 5, 'Valor Recebido', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 9, 5, 'Valor Reaj.', 1, 1, 'C');
}

/////////////////////////////////////// INICIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');

$tipo_papel		= 'L';  // P=> Retrato L=>Paisagem
$unidade		= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel          = 'A4'; // A3, A4, A5, Letter, Legal
$pdf                    = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
$pdf->SetLeftMargin(4);
$pdf->AddPage();
global $pv,$ph; //valor baseado em mm do A4

if(!empty($formato_papel)) {
    if($tipo_papel == 'P') {
        $pv = 295 / 100;
        $ph = 205 / 100;
    }else {
        $pv = 205 / 100;
        $ph = 295 / 100;
    }
}else {
    echo 'Formato não definido';
}

$paginacao = 1;

//Busca do último valor do dólar e do euro
$sql = "SELECT `valor_dolar_dia`, `valor_euro_dia` 
        FROM `cambios` 
        ORDER BY `id_cambio` DESC LIMIT 1 ";
$campos         = bancos::sql($sql);
$valor_dolar    = $campos[0]['valor_dolar_dia'];
$valor_euro     = $campos[0]['valor_euro_dia'];

//Nome Fantasia da empresa do Menu ...
if($id_emp != 0) {//Diferente de Todas Empresas
    $nome_fantasia = genericas::nome_empresa($id_emp);
}else {
    $nome_fantasia = 'TODAS EMPRESAS';
}

//Seleciona os itens ...
$sql = "SELECT cr.*, c.`razaosocial`, t.`recebimento`, CONCAT(tm.`simbolo`, ' ') AS simbolo 
        FROM `contas_receberes` cr 
        INNER JOIN `clientes` c ON c.`id_cliente` = cr.`id_cliente` 
        INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = cr.`id_tipo_moeda` 
        INNER JOIN `tipos_recebimentos` t ON t.`id_tipo_recebimento` = cr.`id_tipo_recebimento` 
        WHERE cr.`id_conta_receber` IN ($_GET[id_conta_receber]) 
        AND cr.`status` < '2' ORDER BY cr.`data_vencimento_alterada` ";
$campos = bancos::sql($sql);
$linhas = count($campos);

//Geração do corpo do PDF ...
tabela($nome_fantasia);

$contador_registro  = 0;
$pagina             = 1;

for($i = 0; $i < $linhas; $i++) {
    //Essa variável iguala o tipo de moeda da conta à receber ...
    $moeda                      = $campos[$i]['simbolo'];
    $num_nota                   = $campos[$i]['num_conta'];
    $descricao_conta            = ($campos[$i]['descricao_conta'] == '') ? '' : $campos[$i]['descricao_conta'];
    $cliente                    = $campos[$i]['razaosocial'];
/*********************************************************************************************/
    $data_vencimento_alterada   = data::datetodata($campos[$i]['data_vencimento_alterada'], '/');
    $data_recebimento           = data::datetodata($campos[$i]['data_recebimento'], '/');
    $calculos_conta_receber     = financeiros::calculos_conta_receber($campos[$i]['id_conta_receber']);

    $tipo_recebimento           = substr($campos[$i]['recebimento'], 0, 16);
    
    if($contador_registro > 29) {
        $pdf->Cell($GLOBALS['ph'] * 109, 5, $pagina, 0, 1, 'C');
        $pagina++;
        $pdf->AddPage();
        $contador_registro = 0;
        tabela($nome_fantasia);
    }

//Verifico se a Conta à Receber tem "Representante" ...
    $sql = "SELECT `nome_fantasia` 
            FROM `representantes` 
            WHERE `id_representante` = '".$campos[$i]['id_representante']."' LIMIT 1 ";
    $campos_representante = bancos::sql($sql);
    if(count($campos_representante) == 1) {//Tem Representante ...
        $representante = $campos_representante[0]['nome_fantasia'];
//Verifico quem é o Supervisor desse Representante ...
        $sql = "SELECT r.`nome_fantasia` AS supervisor 
                FROM `representantes_vs_supervisores` rs 
                INNER JOIN `representantes` r ON r.`id_representante` = rs.`id_representante_supervisor` 
                WHERE rs.`id_representante` = '".$campos[$i]['id_representante']."' LIMIT 1 ";
        $campos_supervisor = bancos::sql($sql);
        if(count($campos_supervisor) == 1) {//Se encontre o Supervisor, apresento este ao lado do Representante ...
            $supervisor = ' ('.$campos_supervisor[0]['supervisor'].') ';
        }else {//Se não só apresenta o Vendedor que no caso seria o próprio representante ...
            $supervisor = '';
        }
    }
    if(!empty($cliente) && $cliente != '&nbsp;')    $cliente_descricao_conta = $cliente.' / ';
    if(!empty($campos[$i]['descricao_conta']))      $cliente_descricao_conta.= $campos[$i]['descricao_conta'];
    if(strlen($cliente_descricao_conta) > 56)       $cliente_descricao_conta = substr($cliente_descricao_conta, 0, 56).'...';

    if(strlen($campos[$i]['pagamento_recebimento']) > 16) {//Registro com 2 linhas ...
//Primeira Linha 
        $pdf->Cell($GLOBALS['ph'] * 3, 5, $campos[$i]['semana'], 'LRT', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 6, 5, $num_nota, 'LRT', 0, 'C');
        
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($GLOBALS['ph'] * 29, 5, $cliente_descricao_conta, 'LRT', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 11, 5, $representante.$supervisor, 'LRT', 0, 'C');
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 8, 5, $data_vencimento_alterada, 'LRT', 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 7, 5, $data_recebimento, 'LRT', 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 10, 5, $tipo_recebimento, 'LRT', 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 8, 5, $moeda.number_format($campos[$i]['valor'], 2, ',', '.'), 'LRT', 0, 'R');
        $pdf->Cell($GLOBALS['ph'] * 9, 5, $moeda.number_format($campos[$i]['valor_pago'], 2, ',', '.'), 'LRT', 0, 'R');
        $pdf->Cell($GLOBALS['ph'] * 9, 5, 'R$ '.number_format($calculos_conta_receber['valor_reajustado'], 2, ',', '.'),'LRT', 1, 'R');
//Segunda Linha
        $tipo_recebimento = substr($campos[$i]['pagamento_recebimento'], 16, 16);
        
        $pdf->Cell($GLOBALS['ph'] * 3, 5, '', 'LRB', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 6, 5, '', 'LRB', 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 29, 5, '', 'LRB', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 11, 5, '', 'LRB', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 8, 5, '', 'LRB', 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 7, 5, '', 'LRB', 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 10, 5, $tipo_recebimento, 'LRB', 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 8, 5, '', 'LRB', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 9, 5, '', 'LRB', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 9, 5, '', 'LRB', 1, 'L');
        $contador_registro+= 2;
    }else {//Somente uma Linha ...
        $pdf->Cell($GLOBALS['ph'] * 3, 5, $campos[$i]['semana'], 1, 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 6, 5, $num_nota, 1, 0, 'C');

        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($GLOBALS['ph'] * 29, 5, $cliente_descricao_conta, 1, 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 11, 5, $representante.$supervisor, 1, 0, 'C');

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 8, 5, $data_vencimento_alterada, 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 7, 5, $data_recebimento, 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 10, 5, $tipo_recebimento, 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 8, 5, $moeda.number_format($campos[$i]['valor'], 2, ',', '.'), 1, 0, 'R');
        $pdf->Cell($GLOBALS['ph'] * 9, 5, $moeda.number_format($campos[$i]['valor_pago'], 2, ',', '.'), 1, 0, 'R');
        $pdf->Cell($GLOBALS['ph'] * 9, 5, 'R$ '.number_format($calculos_conta_receber['valor_reajustado'], 2, ',', '.'), 1, 1, 'R');
        $contador_registro++;
    }
    $valor_total_receber+= $calculos_conta_receber['valor_reajustado'];
}
$pdf->SetTextColor(35, 0, 155);
$pdf->Cell($GLOBALS['ph'] * 90, 5, 'Total:', 'TBL', 0, 'R');
$pdf->Cell($GLOBALS['ph'] * 10, 5, 'R$ '.number_format($valor_total_receber, 2, ',', '.'), 'TBR', 1, 'R');
$pdf->SetTextColor(0, 0, 0);

$paginacao = 28 - $contador_registro;
for($i = 0; $i < $paginacao; $i++) $pdf->Cell($GLOBALS['ph'] * 109, 5, '', 0, 1);
$pdf->Cell($GLOBALS['ph'] * 100, 5, $pagina, 0, 1, 'C');

//Geração do Relatório ...
chdir('../../../../../../pdf');
$file ='../../../../../../pdf/'.basename(tempnam(str_replace(trim('/'), '/', getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><Script Language = 'JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>