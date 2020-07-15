<?
require('../../../../../../lib/pdf/fpdf.php');
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/financeiros.php');
require('../../../../../../lib/genericas.php');
session_start('funcionarios');

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../');

//Busca do último valor do dólar e do euro ...
$valor_dolar        = genericas::moeda_dia('dolar');
$valor_euro         = genericas::moeda_dia('euro');

function tabela($empresa) {
    global $pdf;
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(0, 15, 155);
    $pdf->Cell(234, 5, 'Relatório de Conta(s) à Pagar '.$empresa, 'LTB', 0, 'C');
    $pdf->Cell(85, 5, 'Data da Impressão: '.date('d/m/Y H:i:s'), 'TBR', 1, 'L');

    $pdf->SetTextColor(0, 0, 0);

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(8, 5, 'Sem.', 1, 0, 'C');
    $pdf->Cell(45, 5, 'Nº Conta', 1, 0, 'C');
    $pdf->Cell(75, 5, 'Fornecedor / Descrição da Conta', 1, 0, 'C');
    $pdf->Cell(18, 5, 'Data Em.', 1, 0, 'C');
    $pdf->Cell(20, 5, 'Dt Venc. In.', 1, 0, 'C');
    $pdf->Cell(20, 5, 'Dt Venc. Alt.', 1, 0, 'C');
    $pdf->Cell(28, 5, 'Tipo Pgto.', 1, 0, 'C');
    $pdf->Cell(20, 5, 'Valor N/E', 1, 0, 'C');
    $pdf->Cell(18, 5, 'Val Extras', 1, 0, 'C');
    $pdf->Cell(20, 5, 'Valor Reaj', 1, 0, 'C');
    $pdf->Cell(20, 5, 'Valor Pago', 1, 0, 'C');
    $pdf->Cell(27, 5, 'Saldo à Pagar', 1, 1, 'C');
}

define('FPDF_FONTPATH','font/');

$pdf    = new FPDF();
$pdf->FPDF('L');
$pdf->Open();
$pdf->SetLeftMargin(5);
$pdf->AddPage();

//Busca do nome_fantasia da Empresa logada ...
$sql = "SELECT nomefantasia 
        FROM `empresas` 
        WHERE `id_empresa` = '$id_emp' LIMIT 1 ";
$campos         = bancos::sql($sql);
$nome_fantasia  = $campos[0]['nomefantasia'];

//Busca dos Itens selecionados ...
$sql = "(SELECT ca.*, f.`razaosocial` AS fornecedor, tp.`pagamento`, tp.`imagem`, CONCAT(tm.`simbolo`, ' ') AS simbolo 
        FROM `contas_apagares` ca 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = ca.`id_fornecedor` 
        INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = ca.`id_tipo_pagamento_recebimento` 
        INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = ca.`id_tipo_moeda` 
        WHERE ca.`id_conta_apagar` IN ($_GET[id_contas_apagares]) 
        AND ca.`ativo` = '1' 
        AND ca.`status` < 2 
        GROUP BY ca.`id_conta_apagar`) 
        UNION ALL 
        (SELECT ca.*, r.`nome_fantasia` AS fornecedor, tp.`pagamento`, tp.`imagem`, CONCAT(tm.`simbolo`, ' ') AS simbolo 
        FROM `contas_apagares` ca 
        INNER JOIN `representantes` r ON r.`id_representante` = ca.`id_representante` 
        INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = ca.`id_tipo_pagamento_recebimento` 
        INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = ca.`id_tipo_moeda` 
        WHERE ca.`id_conta_apagar` IN ($_GET[id_contas_apagares]) 
        AND ca.`ativo` = '1' 
        AND ca.`status` < 2 GROUP BY ca.`id_conta_apagar`) ORDER BY `data_vencimento_alterada` ";
$campos = bancos::sql($sql);
$linhas = count($campos);

//Geração do corpo do PDF ...
tabela($nome_fantasia);
$contador_registro = 0;
$pagina = 1;

for($i = 0; $i < $linhas; $i++) {
    if($contador_registro > 29) {
        $pdf->Cell(292, 5, $pagina, 0, 1, 'C');
        $pagina++;
        $pdf->AddPage();
        $contador_registro = 0;
        tabela($nome_fantasia);
    }
    
    $calculos_conta_pagar = financeiros::calculos_conta_pagar($campos[$i]['id_conta_apagar']);
	
    //Aqui faz esse cálculo só para ver o quanto resta à pagar da Conta ...
    if($campos[$i]['id_tipo_moeda'] == 1) {//Reais
        /**********************************Observações Cruciais**********************************
        $campos[$i]['valor_reajustado'] -> Campo que sempre guarda o valor em R$ c/ juros ...
        *****************************************************************************************/
        //Sempre o Valor Reajustado terá prioridade sobre o Valor de Origem da Conta ...
        $valor_pagar = ($calculos_conta_pagar['valor_reajustado'] != 0) ? $calculos_conta_pagar['valor_reajustado'] : $campos[$i]['valor'];
        $valor_pagar-= $campos[$i]['valor_pago'];
        $valor_pagar_real   = $valor_pagar;
    }else if($campos[$i]['id_tipo_moeda'] == 2) {//Dólar
        //O campo $campos[$i]['valor_pago'], guarda o valor pago do Tp de moeda da Conta à Pagar ...
        $valor_pagar        = $campos[$i]['valor'] - $campos[$i]['valor_pago'];
        $valor_pagar_real   = $valor_pagar * $valor_dolar;
    }else if($campos[$i]['id_tipo_moeda'] == 3) {//Euro
        //O campo $campos[$i]['valor_pago'], guarda o valor pago do Tp de moeda da Conta à Pagar ...
        $valor_pagar        = $campos[$i]['valor'] - $campos[$i]['valor_pago'];
        $valor_pagar_real   = $valor_pagar * $valor_euro;
    }
    
    //Essa variável iguala o tipo de moeda da conta à pagar ...
    $moeda = $campos[$i]['simbolo'];
    
    $valor_ne   = ($campos[$i]['valor'] == '0.00') ? '' : $campos[$i]['valor'];
    $valor_pago = ($campos[$i]['valor_pago'] == '0.00') ? '' : $campos[$i]['valor_pago'];

    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(8, 5, $campos[$i]['semana'], 1, 0, 'C');
    
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(45, 5, ucfirst(strtolower($campos[$i]['numero_conta'])), 1, 0, 'L');
    
    if(strlen($campos[$i]['fornecedor']) > 55) {
        $pdf->Cell(75, 5, substr(ucfirst(strtolower($campos[$i]['fornecedor'])), 0, 50).' ...', 1, 0, 'L');
    }else {
        $pdf->Cell(75, 5, ucfirst(strtolower($campos[$i]['fornecedor'])), 1, 0, 'L');
    }
    
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(18, 5, data::datetodata($campos[$i]['data_emissao'], '/'), 1, 0, 'C');
    $pdf->Cell(20, 5, data::datetodata($campos[$i]['data_vencimento'], '/'), 1, 0, 'C');
    $pdf->Cell(20, 5, data::datetodata($campos[$i]['data_vencimento_alterada'], '/'), 1, 0, 'C');
    
    $pdf->SetFont('Arial', '', 8);
    
    if(strlen($campos[$i]['pagamento']) > 16) {
        $pdf->Cell(28, 5, substr($campos[$i]['pagamento'], 0, 16).' ...', 1, 0, 'L');
    }else {
        $pdf->Cell(28, 5, $campos[$i]['pagamento'], 1, 0, 'L');
    }
    
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(20, 5, $moeda.$valor_ne, 1, 0, 'R');
    $pdf->Cell(18, 5, $moeda.number_format($calculos_conta_pagar['valores_extra'], 2, ',', '.'), 1, 0, 'R');
    $pdf->Cell(20, 5, $moeda.number_format($calculos_conta_pagar['valor_reajustado'], 2, ',', '.'), 1, 0, 'R');
    $pdf->Cell(20, 5, $moeda.number_format($valor_pago, 2, ',', '.'), 1, 0, 'R');
    $pdf->Cell(27, 5, 'R$ '.number_format($valor_pagar_real, 2, ',', '.'), 1, 1, 'R');
    $contador_registro++;
    
    if($campos[$i]['id_tipo_moeda'] == 1) {//Reais
        $total_valor_ne_rs+= $valor_ne;
        $total_valores_extra_rs+= $calculos_conta_pagar['valores_extra'];
        $total_valor_reajustado_rs+= $calculos_conta_pagar['valor_reajustado'];
        $total_valor_pago_rs+= $valor_pago;
        $total_valor_pagar_rs+= $valor_pagar_real;
    }else if($campos[$i]['id_tipo_moeda'] == 2) {//Dólar
        $total_valor_ne_us+= $valor_ne;
        $total_valores_extra_us+= $calculos_conta_pagar['valores_extra'];
        $total_valor_reajustado_us+= $calculos_conta_pagar['valor_reajustado'];
        $total_valor_pago_us+= $valor_pago;
        $total_valor_pagar_us+= $valor_pagar_real;
    }else if($campos[$i]['id_tipo_moeda'] == 3) {//Euro
        $total_valor_ne_euro+= $valor_ne;
        $total_valores_extra_euro+= $calculos_conta_pagar['valores_extra'];
        $total_valor_reajustado_euro+= $calculos_conta_pagar['valor_reajustado'];
        $total_valor_pago_euro+= $valor_pago;
        $total_valor_pagar_euro+= $valor_pagar_real;
    }
}

$pdf->Ln(5);

$total_valor_pagar_geral = ($total_valor_pagar_rs + $total_valor_pagar_us + $total_valor_pagar_euro);

$pdf->Cell(214, 5, 'Total em R$ ', 1, 0, 'R');
$pdf->Cell(20, 5, number_format($total_valor_ne_rs, 2, ',', '.'), 1, 0, 'R');
$pdf->Cell(18, 5, number_format($total_valores_extra_rs, 2, ',', '.'), 1, 0, 'R');
$pdf->Cell(20, 5, number_format($total_valor_reajustado_rs, 2, ',', '.'), 1, 0, 'R');
$pdf->Cell(20, 5, number_format($total_valor_pago_rs, 2, ',', '.'), 1, 0, 'R');
$pdf->Cell(27, 5, number_format($total_valor_pagar_rs, 2, ',', '.'), 1, 1, 'R');

$pdf->Cell(214, 5, 'Total em U$ ', 1, 0, 'R');
$pdf->Cell(20, 5, number_format($total_valor_ne_us, 2, ',', '.'), 1, 0, 'R');
$pdf->Cell(18, 5, number_format($total_valores_extra_us, 2, ',', '.'), 1, 0, 'R');
$pdf->Cell(20, 5, number_format($total_valor_reajustado_us, 2, ',', '.'), 1, 0, 'R');
$pdf->Cell(20, 5, number_format($total_valor_pago_us, 2, ',', '.'), 1, 0, 'R');
$pdf->Cell(27, 5, number_format($total_valor_pagar_us, 2, ',', '.'), 1, 1, 'R');

$pdf->Cell(214, 5, 'Total em E$ ', 1, 0, 'R');
$pdf->Cell(20, 5, number_format($total_valor_euro, 2, ',', '.'), 1, 0, 'R');
$pdf->Cell(18, 5, number_format($total_valores_extra_euro, 2, ',', '.'), 1, 0, 'R');
$pdf->Cell(20, 5, number_format($total_valor_reajustado_euro, 2, ',', '.'), 1, 0, 'R');
$pdf->Cell(20, 5, number_format($total_valor_pago_euro, 2, ',', '.'), 1, 0, 'R');
$pdf->Cell(27, 5, number_format($total_valor_pagar_euro, 2, ',', '.'), 1, 1, 'R');

$pdf->Cell(292, 5, 'Total Geral R$ ', 1, 0, 'R');
$pdf->Cell(27, 5, number_format($total_valor_pagar_geral, 2, ',', '.'), 1, 1, 'R');

$pdf->SetFont('Arial', '', 9);
$paginacao = 28 - $contador_registro;
for($i = 0; $i < $paginacao; $i++) $pdf->Cell(1, 5, '', 0, 1);
$pdf->Cell(319, 5, $pagina, 0, 1, 'C');

//Geração do Relatório ...
chdir('../../../../../../pdf');
$file = '../../../../../../pdf/'.basename(tempnam(str_replace(trim('/'),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>