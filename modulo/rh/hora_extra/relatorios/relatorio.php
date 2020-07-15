<?
require('../../../../lib/pdf/fpdf.php');
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/depto_pessoal.php');
require('../../../../lib/genericas.php');
require('../../../../lib/num_por_extenso_em_rs.php');
segurancas::geral('/erp/albafer/modulo/rh/hora_extra/opcoes_gerenciar_hora_extra.php', '../../../../');

//Utilizo essas variáveis mais abaixo na hora de fazer os cálculos de Hora Extra do funcionário ...
$valor_vale_refeicao_hora_extra    = genericas::variavel(35);

/////////////////////////////////////// INÍCIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel	= 'P';  // P=> Retrato L=>Paisagem
$unidade	= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= 'A4'; // A3, A4, A5, Letter, Legal
$pdf = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
$pdf->SetTopMargin(2);//2.5 deixar este valor
$pdf->SetLeftMargin(12);
$pdf->SetAutoPageBreak('false', 0);
$pdf->SetFont('Arial', '', 10);

global $pv,$ph; //valor baseado em mm do A4
if($formato_papel == 'A4') {
    if($tipo_papel == 'P') {
        $pv=295/100;
        $ph=205/100;
    }else {
        $pv=205/100;
        $ph=295/100;
    }
}else {
    echo 'Formato não definido';
}

$separador_colunas = 5;
$altura = 5;
$bordas = 1;

/*Lista todas as horas extras lançadas no Período passado por parâmetro dos Funcionários da Empresa
passada por parâmetro e que possuem Valor Extra à Receber ...*/
$sql = "SELECT e.`nomefantasia`, f.`nome`, f.`tipo_salario`, f.`salario_pd`, f.`salario_pf`, fhr.* 
        FROM `funcionarios_hes_rel` fhr 
        INNER JOIN `funcionarios` f ON f.`id_funcionario` = fhr.`id_funcionario` 
        INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
        WHERE (fhr.`data_inicial` BETWEEN '$_GET[txt_data_inicial]' AND '$_GET[txt_data_final]') ORDER BY f.`nome` ";
$campos = bancos::sql($sql);
$linhas = count($campos);

for($i = 0; $i < $linhas; $i+=2) {//Aqui vou acrescentando o i de 2 em 2 por causa da outra coluna ao lado
//Significa que já foram impressos 8 registros daquela página, e sendo assim solicito uma nova página ...
    if($i % 8 == 0) $pdf->AddPage();
    $pdf->Ln(5);
/*Estou separando de 2 em 2 porque a 2 linha seria como se fosse a outra coluna Segunda Coluna*/
//Nome da Empresa
    if(($i + 1) < $linhas) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 44, $altura, $campos[$i]['nomefantasia'], 1, 0, 'C');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 44, $altura, $campos[$i + 1]['nomefantasia'], 1, 1, 'C');
    }else {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 44, $altura, $campos[$i]['nomefantasia'], 1, 1, 'C');
    }
//Nome do Funcionário ...
    if(($i + 1) < $linhas) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 6.5, 5, 'Nome - ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 37.5, 5, $campos[$i]['nome'], 'R', 0, 'L');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 6.5, 5, 'Nome - ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 37.5, 5, $campos[$i + 1]['nome'], 'R', 1, 'L');
    }else {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 6.5, 5, 'Nome - ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 37.5, 5, $campos[$i]['nome'], 'R', 1, 'L');
    }
//Tipo ...
    if(($i + 1) < $linhas) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 6, 5, 'Tipo - ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
//Rótulo do Tipo de Salário ...
        if($campos[$i]['tipo_salario'] == 1) {//Horista
            $rotulo = 'Horista';
        }else {//Mensalista
            $rotulo = 'Mensalista';
        }
        $pdf->Cell($GLOBALS['ph'] * 17.5, 5, $rotulo, 0, 0, 'L');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 9.7, 5, ' Data Pag. - ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 10.8, 5, data::datetodata($campos[$i]['data_pagamento'], '/'), 'R', 0, 'L');

        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 6, 5, 'Tipo - ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
//Rótulo do Tipo de Salário ...
        if($campos[$i + 1]['tipo_salario'] == 1) {//Horista
            $rotulo = 'Horista';
        }else {//Mensalista
            $rotulo = 'Mensalista';
        }
        $pdf->Cell($GLOBALS['ph'] * 17.5, 5, $rotulo, 0, 0, 'L');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 9.7, 5, ' Data Pag. - ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 10.8, 5, data::datetodata($campos[$i]['data_pagamento'], '/'), 'R', 1, 'L');
    }else {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 6, 5, 'Tipo - ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
//Rótulo do Tipo de Salário ...
        if($campos[$i]['tipo_salario'] == 1) {//Horista
            $rotulo = 'Horista';
        }else {//Mensalista
            $rotulo = 'Mensalista';
        }
        $pdf->Cell($GLOBALS['ph'] * 17.5, 5, $rotulo, 0, 0, 'L');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 9.7, 5, ' Data Pag. - ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 10.8, 5, data::datetodata($campos[$i]['data_pagamento'], '/'), 'R', 1, 'L');
    }
//Qtde Horas, Vlr à Receber H. Extra, Vl VR, Vl VT ...
    if(($i + 1) < $linhas) {
//Linha Vazia
        $pdf->Cell($GLOBALS['ph'] * 44, 2, '', 'LR', 0, 'L');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
//Linha Vazia
        $pdf->Cell($GLOBALS['ph'] * 44, 2, '', 'LR', 1, 'L');
//Qtde de Horas ...
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 10.5, 5, 'Qtd. Horas - ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 13, 5, number_format($campos[$i]['qtde_horas'], 2, ':', ''), 0, 0, 'L');
//Vlr Hora ...
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 9.7, 5, ' Vlr Hora. - ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $valor_hora_salario = $campos[$i]['salario_pd'] + $campos[$i]['salario_pf'];
//Salário, se for Mensalista, transformo em Hora ...
        if($campos[$i]['tipo_salario'] == 2) $valor_hora_salario/= 220;
        $salario_mensal = round($valor_hora_salario * 220, 2);
        /******************************************Controle com o Add de Hora Extra******************************************/
        $vetor_valores          = depto_pessoal::valores_hora_extra($data_pagamento, $salario_mensal);
        $adicional_hora_extra   = $vetor_valores['adicional_hora_extra'];
        $valor_hora_extra_min   = $vetor_valores['valor_hora_extra_min'];
        
        //Valor da Hora Extra ...
        $valor_hora_extra = $valor_hora_salario * (1 + $adicional_hora_extra / 100);
        $valor_hora_extra = round(max($valor_hora_extra, $valor_hora_extra_min), 2);
        /********************************************************************************************************************/
        $pdf->Cell($GLOBALS['ph'] * 10.8, 5, 'R$ '.number_format($valor_hora_extra, 2, ',', '.'), 'R', 0, 'L');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        
//Qtde de Horas ...
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 10.5, 5, 'Qtd. Horas - ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 13, 5, number_format($campos[$i + 1]['qtde_horas'], 2, ':', ''), 0, 0, 'L');
//Vlr Hora ...
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 9.7, 5, ' Vlr Hora. - ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $valor_hora_salario = $campos[$i + 1]['salario_pd'] + $campos[$i + 1]['salario_pf'];
//Salário, se for Mensalista, transformo em Hora ...
        if($campos[$i + 1]['tipo_salario'] == 2) $valor_hora_salario/= 220;
        $salario_mensal = round($valor_hora_salario * 220, 2);
        /******************************************Controle com o Add de Hora Extra******************************************/
        $vetor_valores          = depto_pessoal::valores_hora_extra($data_pagamento, $salario_mensal);
        $adicional_hora_extra   = $vetor_valores['adicional_hora_extra'];
        $valor_hora_extra_min   = $vetor_valores['valor_hora_extra_min'];
            
        //Valor da Hora Extra ...
        $valor_hora_extra = $valor_hora_salario * (1 + $adicional_hora_extra / 100);
        $valor_hora_extra = round(max($valor_hora_extra, $valor_hora_extra_min), 2);
        
        $pdf->Cell($GLOBALS['ph'] * 10.8, 5, 'R$ '.number_format($valor_hora_extra, 2, ',', '.'), 'R', 1, 'L');
//Vlr à Receber H. Extra ...
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 20, 5, 'Vlr à Receber H. Extra - ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $valor_extra_receber1 = $campos[$i]['extra_receber'];
        if($valor_extra_receber1 != 0) {
            $pdf->Cell($GLOBALS['ph'] * 24, 5, 'R$ '.number_format($valor_extra_receber1, 2, ',', '.'), 'R', 0, 'L');
        }else {
            $pdf->Cell($GLOBALS['ph'] * 24, 5, '', 'R', 0, 'L');
        }
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 20, 5, 'Vlr à Receber H. Extra - ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $valor_extra_receber2 = $campos[$i + 1]['extra_receber'];
        if($valor_extra_receber2 != 0) {
            $pdf->Cell($GLOBALS['ph'] * 24, 5, 'R$ '.number_format($valor_extra_receber2, 2, ',', '.'), 'R', 1, 'L');
        }else {
            $pdf->Cell($GLOBALS['ph'] * 24, 5, '', 'R', 1, 'L');
        }
//Vl VT ...
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 7, 5, 'Vlr VT - ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        //Aqui eu busco o Valor Diário de VT consumido por um Funcionário ...
        $sql = "SELECT SUM(fvt.`qtde_vale` * vt.`valor_unitario`) AS vlr_diario_vt 
                FROM `funcionarios_vs_vales_transportes` fvt 
                INNER JOIN `vales_transportes` vt ON vt.`id_vale_transporte` = fvt.`id_vale_transporte` AND vt.`ativo` = '1' 
                WHERE fvt.`id_funcionario` = '".$campos[$i]['id_funcionario']."' ";
        $campos_valor_diario_vt = bancos::sql($sql);
        $total_pagar_vt1        = $campos_valor_diario_vt[0]['vlr_diario_vt'] * $campos[$i]['qtde_vt_para_pagar'];
        if($total_pagar_vt1 != 0) {
            $pdf->Cell($GLOBALS['ph'] * 37, 5, 'R$ '.number_format($total_pagar_vt1, 2, ',', '.'), 'R', 0, 'L');
        }else {
            $pdf->Cell($GLOBALS['ph'] * 37, 5, '', 'R', 0, 'L');
        }
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 7, 5, 'Vlr VT - ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        //Aqui eu busco o Valor Diário de VT consumido por um Funcionário ...
        $sql = "SELECT SUM(fvt.`qtde_vale` * vt.`valor_unitario`) AS vlr_diario_vt 
                FROM `funcionarios_vs_vales_transportes` fvt 
                INNER JOIN `vales_transportes` vt ON vt.id_vale_transporte = fvt.id_vale_transporte AND vt.`ativo` = '1' 
                WHERE fvt.`id_funcionario` = '".$campos[$i + 1]['id_funcionario']."' ";
        $campos_valor_diario_vt = bancos::sql($sql);
        $total_pagar_vt2        = $campos_valor_diario_vt[0]['vlr_diario_vt'] * $campos[$i + 1]['qtde_vt_para_pagar'];
        if($total_pagar_vt2 != 0) {
            $pdf->Cell($GLOBALS['ph'] * 37, 5, 'R$ '.number_format($total_pagar_vt2, 2, ',', '.'), 'R', 1, 'L');
        }else {
            $pdf->Cell($GLOBALS['ph'] * 37, 5, '', 'R', 1, 'L');
        }
//Vl VR ...
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 7, 5, 'Vlr VR - ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        
        $total_pagar_vr1 = $valor_vale_refeicao_hora_extra * $campos[$i]['qtde_vr_para_pagar'];
        if($total_pagar_vr1 != 0) {
            $pdf->Cell($GLOBALS['ph'] * 37, 5, 'R$ '.number_format($total_pagar_vr1, 2, ',', '.'), 'R', 0, 'L');
        }else {
            $pdf->Cell($GLOBALS['ph'] * 37, 5, '', 'R', 0, 'L');
        }
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 7, 5, 'Vlr VR - ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        
        $total_pagar_vr2 = $valor_vale_refeicao_hora_extra * $campos[$i + 1]['qtde_vr_para_pagar'];
        if($total_pagar_vr2 != 0) {
            $pdf->Cell($GLOBALS['ph'] * 37, 5, 'R$ '.number_format($total_pagar_vr2, 2, ',', '.'), 'R', 1, 'L');
        }else {
            $pdf->Cell($GLOBALS['ph'] * 37, 5, '', 'R', 1, 'L');
        }
    }else {
//Linha Vazia
        $pdf->Cell($GLOBALS['ph'] * 44, 2, '', 'LR', 1, 'L');
//Qtde de Horas ...
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 10.5, 5, 'Qtd. Horas - ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 13, 5, number_format($campos[$i]['qtde_horas'], 2, ':', ''), 'R', 1, 'L');
//Vlr Hora ...
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 9.7, 5, ' Vlr Hora. - ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $valor_hora_salario = $campos[$i]['salario_pd'] + $campos[$i]['salario_pf'];
        //Salário, se for Mensalista, transformo em Hora ...
        if($campos[$i]['tipo_salario'] == 2) $valor_hora_salario/= 220;
        $salario_mensal = round($valor_hora_salario * 220, 2);
        /******************************************Controle com o Add de Hora Extra******************************************/
        $vetor_valores          = depto_pessoal::valores_hora_extra($data_pagamento, $salario_mensal);
        $adicional_hora_extra   = $vetor_valores['adicional_hora_extra'];
        $valor_hora_extra_min   = $vetor_valores['valor_hora_extra_min'];
        
        //Valor da Hora Extra ...
        $valor_hora_extra1 = $valor_hora_salario * (1 + $adicional_hora_extra / 100);
        $valor_hora_extra = round(max($valor_hora_extra, $valor_hora_extra_min), 2);
        $pdf->Cell($GLOBALS['ph'] * 10.8, 5, 'R$ '.number_format($valor_hora_extra1, 2, ',', '.'), 'R', 1, 'L');
//Vlr à Receber H. Extra ...
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 20, 5, 'Vlr à Receber H. Extra - ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $valor_extra_receber1 = $campos[$i]['extra_receber'];
        if($valor_extra_receber1 != 0) {
            $pdf->Cell($GLOBALS['ph'] * 24, 5, 'R$ '.number_format($valor_extra_receber1, 2, ',', '.'), 'R', 1, 'L');
        }else {
            $pdf->Cell($GLOBALS['ph'] * 24, 5, '', 'R', 1, 'L');
        }
//Vl VT ...
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 7, 5, 'Vlr VT - ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        //Aqui eu busco o Valor Diário de VT consumido por um Funcionário ...
        $sql = "SELECT SUM(fvt.`qtde_vale` * vt.`valor_unitario`) AS vlr_diario_vt 
                FROM `funcionarios_vs_vales_transportes` fvt 
                INNER JOIN `vales_transportes` vt ON vt.id_vale_transporte = fvt.id_vale_transporte AND vt.`ativo` = '1' 
                WHERE fvt.`id_funcionario` = '".$campos[$i]['id_funcionario']."' ";
        $campos_valor_diario_vt = bancos::sql($sql);
        $total_pagar_vt1        = $campos_valor_diario_vt[0]['vlr_diario_vt'] * $campos[$i]['qtde_vt_para_pagar'];
        if($total_pagar_vt1 != 0) {
            $pdf->Cell($GLOBALS['ph'] * 37, 5, 'R$ '.number_format($total_pagar_vt1, 2, ',', '.'), 'R', 1, 'L');
        }else {
            $pdf->Cell($GLOBALS['ph'] * 37, 5, '', 'R', 1, 'L');
        }
//Vl VR ...
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 7, 5, 'Vlr VT - ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $total_pagar_vr1 = $valor_vale_refeicao_hora_extra * $campos[$i]['qtde_vr_para_pagar'];
        if($total_pagar_vr1 != 0) {
            $pdf->Cell($GLOBALS['ph'] * 37, 5, 'R$ '.number_format($total_pagar_vr1, 2, ',', '.'), 'R', 1, 'L');
        }else {
            $pdf->Cell($GLOBALS['ph'] * 37, 5, '', 'R', 1, 'L');
        }
    }
//Vlr Total à Receber ...
    if(($i + 1) < $linhas) {
//Linha Vazia
        $pdf->Cell($GLOBALS['ph'] * 44, 2, '', 'LR', 0, 'L');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
//Linha Vazia
        $pdf->Cell($GLOBALS['ph'] * 44, 2, '', 'LR', 1, 'L');

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell($GLOBALS['ph'] * 21, 5, 'Vlr Total à Receber - ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 12);
        $valor_total_extra_receber1 = $valor_extra_receber1 + $total_pagar_vt1 + $total_pagar_vr1;
        /*************************Financeiro*************************/
        //Arredondamento para o Financeiro, não ter que pagar com tantas moedas de centavo ...
        $reais 		= intval($valor_total_extra_receber1);
        $centavos 	= round($valor_total_extra_receber1 - intval($valor_total_extra_receber1), 2);
        $valor_total_extra_receber1 = ($centavos <= 0.50) ? $reais.'.50' : $reais + 1;
        /************************************************************/
        $pdf->Cell($GLOBALS['ph'] * 23, 5, 'R$ '.number_format($valor_total_extra_receber1, 2, ',', '.'), 'R', 0, 'L');
        $pdf->Cell($separador_colunas, 5, '', 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell($GLOBALS['ph'] * 21, 5, 'Vlr Total à Receber - ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 12);
        $valor_total_extra_receber2 = $valor_extra_receber2 + $total_pagar_vt2 + $total_pagar_vr2;
        /*************************Financeiro*************************/
        //Arredondamento para o Financeiro, não ter que pagar com tantas moedas de centavo ...
        $reais 		= intval($valor_total_extra_receber2);
        $centavos 	= round($valor_total_extra_receber2 - intval($valor_total_extra_receber2), 2);
        $valor_total_extra_receber2 = ($centavos <= 0.50) ? $reais.'.50' : $reais + 1;
        /************************************************************/
        $pdf->Cell($GLOBALS['ph'] * 23, 5, 'R$ '.number_format($valor_total_extra_receber2, 2, ',', '.'), 'R', 1, 'L');
    }else {
//Linha Vazia
        $pdf->Cell($GLOBALS['ph'] * 44, 2, '', 'LR', 1, 'L');
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell($GLOBALS['ph'] * 21, 5, 'Vlr Total à Receber - ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 12);
        $valor_total_extra_receber1 = $valor_extra_receber1 + $total_pagar_vt1 + $total_pagar_vr1;
        /*************************Financeiro*************************/
        //Arredondamento para o Financeiro, não ter que pagar com tantas moedas de centavo ...
        $reais 		= intval($valor_total_extra_receber1);
        $centavos 	= round($valor_total_extra_receber1 - intval($valor_total_extra_receber1), 2);
        $valor_total_extra_receber1 = ($centavos <= 0.50) ? $reais.'.50' : $reais + 1;
        /************************************************************/
        $pdf->Cell($GLOBALS['ph'] * 23, 5, 'R$ '.number_format($valor_total_extra_receber1, 2, ',', '.'), 'R', 1, 'L');
    }
//Período ...
    if(($i + 1) < $linhas) {
//Linha Vazia
        $pdf->Cell($GLOBALS['ph'] * 44, 2, '', 'LR', 0, 'L');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
//Linha Vazia
        $pdf->Cell($GLOBALS['ph'] * 44, 2, '', 'LR', 1, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 7.5, 5, 'Período: ', 'L', 0, 'L');
        $pdf->SetFont('Arial', 'U', 10);
        $pdf->Cell($GLOBALS['ph'] * 36.5, 5, data::datetodata($campos[$i]['data_inicial'], '/').' à '.data::datetodata($campos[$i]['data_final'], '/'), 'R', 0, 'L');

        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 7.5, 5, 'Período: ', 'L', 0, 'L');
        $pdf->SetFont('Arial', 'U', 10);
        $pdf->Cell($GLOBALS['ph'] * 36.5, 5, data::datetodata($campos[$i + 1]['data_inicial'], '/').' à '.data::datetodata($campos[$i + 1]['data_final'], '/'), 'R', 1, 'L');
    }else {
//Linha Vazia
        $pdf->Cell($GLOBALS['ph'] * 44, 2, '', 'LR', 1, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 7.5, 5, 'Período: ', 'L', 0, 'L');
        $pdf->SetFont('Arial', 'U', 10);
        $pdf->Cell($GLOBALS['ph'] * 36.5, 5, data::datetodata($campos[$i]['data_inicial'], '/').' à '.data::datetodata($campos[$i]['data_final'], '/'), 'R', 1, 'L');
    }
//Assinatura do Funcionário ...
    if(($i + 1) < $linhas) {
//Linha Vazia
        $pdf->Cell($GLOBALS['ph'] * 44, 2, '', 'LR', 0, 'L');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
//Linha Vazia
        $pdf->Cell($GLOBALS['ph'] * 44, 2, '', 'LR', 1, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 12, 5, 'ASSINATURA', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 32, 5, '________________________________', 'R', 0, 'L');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 12, 5, 'ASSINATURA', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 32, 5, '________________________________', 'R', 1, 'L');
    }else {
//Linha Vazia
        $pdf->Cell($GLOBALS['ph'] * 44, 2, '', 'LR', 1, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 12, 5, 'ASSINATURA', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 32, 5, '________________________________', 'R', 1, 'L');
    }
//Linha Final ...
    if(($i + 1) < $linhas) {
        $pdf->Cell($GLOBALS['ph'] * 44, 5, '', 'LRB', 0, 'C');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 44, 5, '', 'LRB', 1, 'C');
    }else {
        $pdf->Cell($GLOBALS['ph'] * 44, 5, '', 'LRB', 1, 'C');
    }
}

chdir('../../../../pdf');
$file = '../../../../pdf/'.basename(tempnam(str_replace(trim('/'), '/', getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>