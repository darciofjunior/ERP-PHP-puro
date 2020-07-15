<?
require('../../../../lib/pdf/fpdf.php');
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/financeiro/livro_caixa/relatorios/relatorio.php', '../../../../');

function rotulo() {
    global $pdf;
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell($GLOBALS['ph'] * 8, 5, 'DATA', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 80, 5, 'HISTÓRICO', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 8, 5, 'ENTRADAS', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 8, 5, 'SAÍDAS', 1, 1, 'C');
}

/////////////////////////////////////// INÍCIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel	= 'P';  // P=> Retrato L=>Paisagem
$unidade	= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= 'A4'; // A3, A4, A5, Letter, Legal
$pdf            = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
$pdf->SetTopMargin(5);
$pdf->SetLeftMargin(2);
$pdf->AddPage();
global $pv, $ph; //Valor baseado em mm do Ofício ...

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

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell($GLOBALS['ph'] * 104, 5, 'LIVRO CAIXA   -   PERÍODO DE '.data::datetodata($_GET[txt_data_inicial], '/').' À '.data::datetodata($_GET[txt_data_final], '/'), 1, 1, 'C');

//Aqui eu busco a Razão Social da Empresa passada por parâmetro ...
$sql = "SELECT razaosocial 
        FROM `empresas` 
        WHERE `id_empresa` = '$_GET[cmb_empresa]' LIMIT 1 ";
$campos_empresa = bancos::sql($sql);

$pdf->Cell($GLOBALS['ph'] * 104, 5, 'EMPRESA: '.$campos_empresa[0]['razaosocial'], 1, 1, 'C');
rotulo();

$total_entrada  = 0;
$total_saida    = 0;
/******************************************************************************************/
/**************************************Contas à Pagar**************************************/
/******************************************************************************************/
$sql = "SELECT CONCAT(tp.`pagamento`, ' | ', b.banco, ' | ', pf.discriminacao, ' | ', ca.numero_conta, ' | ', f.razaosocial) AS historico, 
        caq.valor, DATE_FORMAT(caq.data, '%d/%m/%Y') AS data_quitacao 
        FROM `contas_apagares_quitacoes` caq 
        LEFT JOIN `bancos` b ON b.`id_banco` = caq.`id_banco` 
        INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = caq.`id_tipo_pagamento_recebimento` 
        INNER JOIN `contas_apagares` ca ON ca.`id_conta_apagar` = caq.`id_conta_apagar` AND ca.`id_empresa` = '$_GET[cmb_empresa]' 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = ca.`id_fornecedor` 
        LEFT JOIN `produtos_financeiros` pf ON pf.`id_produto_financeiro` = ca.`id_produto_financeiro` 
        WHERE caq.`data` BETWEEN '$_GET[txt_data_inicial]' AND '$_GET[txt_data_final]' ORDER BY caq.`data` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas > 0) {//Se encontrou pelo menos 1 Registro ...
    for($i = 0; $i < $linhas; $i++) {
        if($GLOBALS['nova_pagina'] == 'sim') {
            $GLOBALS['nova_pagina'] = 'nao';
            rotulo();
        }
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($GLOBALS['ph'] * 8, 5, $campos[$i]['data_quitacao'], 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 80, 5, ucwords(strtolower($campos[$i]['historico'])), 1, 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 8, 5, '', 1, 0, 'R');
        $pdf->Cell($GLOBALS['ph'] * 8, 5, number_format($campos[$i]['valor'], 2, ',', '.'), 1, 1, 'R');
        
        $total_saida+= $campos[$i]['valor'];
    }
}
/******************************************************************************************/
/*************************************Contas à Receber*************************************/
/******************************************************************************************/
$sql = "SELECT CONCAT(tr.recebimento, ' | ', b.banco, ' | ', cr.num_conta, ' | ', c.razaosocial) AS historico, 
        crq.valor, DATE_FORMAT(crq.data, '%d/%m/%Y') AS data_quitacao 
        FROM `contas_receberes_quitacoes` crq 
        LEFT JOIN `contas_correntes` cc ON cc.`id_contacorrente` = crq.`id_contacorrente` 
        INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
        INNER JOIN `bancos` b ON b.`id_banco` = a.`id_banco` 
        INNER JOIN `tipos_recebimentos` tr ON tr.`id_tipo_recebimento` = crq.`id_tipo_recebimento` 
        INNER JOIN `contas_receberes` cr ON cr.`id_conta_receber` = crq.`id_conta_receber` AND cr.`id_empresa` = '$_GET[cmb_empresa]' 
        INNER JOIN `clientes` c ON c.`id_cliente` = cr.`id_cliente` 
        WHERE crq.`data` BETWEEN '$_GET[txt_data_inicial]' AND '$_GET[txt_data_final]' ORDER BY crq.`data` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas > 0) {//Se encontrou pelo menos 1 Registro ...
    for($i = 0; $i < $linhas; $i++) {
        if($GLOBALS['nova_pagina'] == 'sim') {
            $GLOBALS['nova_pagina'] = 'nao';
            rotulo();
        }
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($GLOBALS['ph'] * 8, 5, $campos[$i]['data_quitacao'], 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 80, 5, ucwords(strtolower($campos[$i]['historico'])), 1, 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 8, 5, number_format($campos[$i]['valor'], 2, ',', '.'), 1, 0, 'R');
        $pdf->Cell($GLOBALS['ph'] * 8, 5, '', 1, 1, 'R');
        
        $total_entrada+= $campos[$i]['valor'];
    }
}
/******************************************************************************************/
/********************************Transferência(s) de Caixa*********************************/
/******************************************************************************************/
$sql = "SELECT valor_transferencia, DATE_FORMAT(data_transferencia, '%d/%m/%Y') AS data_transferencia 
        FROM `transferencias_caixas` 
        WHERE `id_empresa` = '$_GET[cmb_empresa]' 
        AND `data_transferencia` BETWEEN '$_GET[txt_data_inicial]' AND '$_GET[txt_data_final]' ORDER BY `data_transferencia` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas > 0) {//Se encontrou pelo menos 1 Registro ...
    for($i = 0; $i < $linhas; $i++) {
        if($GLOBALS['nova_pagina'] == 'sim') {
            $GLOBALS['nova_pagina'] = 'nao';
            rotulo();
        }
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($GLOBALS['ph'] * 8, 5, $campos[$i]['data_transferencia'], 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 80, 5, '???', 1, 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 8, 5, number_format($campos[$i]['valor_transferencia'], 2, ',', '.'), 1, 0, 'R');
        $pdf->Cell($GLOBALS['ph'] * 8, 5, '', 1, 1, 'R');
        
        $total_entrada+= $campos[$i]['valor_transferencia'];
    }
}
/******************************************************************************************/    
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell($GLOBALS['ph'] * 88, 5, 'A TRANSPORTAR TOTAIS DO DIA R$ ', 1, 0, 'R');
$pdf->SetFont('Arial', '', 8);
$pdf->Cell($GLOBALS['ph'] * 8, 5, number_format($total_entrada, 2, ',', '.'), 1, 0, 'R');
$pdf->Cell($GLOBALS['ph'] * 8, 5, number_format($total_saida, 2, ',', '.'), 1, 1, 'R');

//Aqui eu busco o último Saldo atual de Caixa cadastrado no sistema ...
$sql = "SELECT saldo_atual_caixa, DATE_FORMAT(data_lancamento, '%d/%m/%Y') AS data_lancamento 
        FROM `saldos_atuais_caixas` 
        ORDER BY id_saldo_atual_caixa DESC LIMIT 1 ";
$campos_saldo_atual_caixa = bancos::sql($sql);

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell($GLOBALS['ph'] * 88, 5, 'SALDO ANTERIOR EM '.$campos_saldo_atual_caixa[0]['data_lancamento'].' R$ ', 1, 0, 'R');
$pdf->SetFont('Arial', '', 8);
$pdf->Cell($GLOBALS['ph'] * 8, 5, number_format($campos_saldo_atual_caixa[0]['saldo_atual_caixa'], 2, ',', '.'), 1, 0, 'R');
$pdf->Cell($GLOBALS['ph'] * 8, 5, '', 1, 1, 'C');

//Fórmulas ...
$somas_conferencia_entrada  = $total_entrada + $campos_saldo_atual_caixa[0]['saldo_atual_caixa'];
$saldo_atual                = $somas_conferencia_entrada - $total_saida;
$somas_conferencia_saida    = $total_saida + $saldo_atual;

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell($GLOBALS['ph'] * 88, 5, 'SALDO ATUAL R$ ', 1, 0, 'R');
$pdf->SetFont('Arial', '', 8);
$pdf->Cell($GLOBALS['ph'] * 8, 5, '', 1, 0, 'R');
$pdf->Cell($GLOBALS['ph'] * 8, 5, number_format($saldo_atual, 2, ',', '.'), 1, 1, 'R');

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell($GLOBALS['ph'] * 88, 5, '(SOMAS PARA CONFERÊNCIA) R$ ', 1, 0, 'R');
$pdf->SetFont('Arial', '', 8);
$pdf->Cell($GLOBALS['ph'] * 8, 5, number_format($somas_conferencia_entrada, 2, ',', '.'), 1, 0, 'R');
$pdf->Cell($GLOBALS['ph'] * 8, 5, number_format($somas_conferencia_saida, 2, ',', '.'), 1, 1, 'R');

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell($GLOBALS['ph'] * 88, 5, '', 'TLR', 0, 'R');
$pdf->Cell($GLOBALS['ph'] * 16, 5, 'VISTO', 'TLR', 1, 'C');

$pdf->Cell($GLOBALS['ph'] * 88, 5, '', 'BLR', 0, 'R');
$pdf->Cell($GLOBALS['ph'] * 16, 5, '', 'BLR', 1, 'C');

//Aqui o sistema guarda o último Saldo atual de Caixa cadastrado no sistema ...
$sql = "INSERT INTO `saldos_atuais_caixas` (`id_saldo_atual_caixa`, `id_funcionario`, `id_empresa`, `saldo_atual_caixa`, `data_lancamento`) VALUES (NULL, '$_SESSION[id_funcionario]', '$_GET[cmb_empresa]', '$saldo_atual', '$_GET[txt_data_final]') ";
//bancos::sql($sql);

chdir('../../../../pdf');
//$file='../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>