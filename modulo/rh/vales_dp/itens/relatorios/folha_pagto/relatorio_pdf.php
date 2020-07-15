<?
require('../../../../../../lib/pdf/fpdf.php');
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/variaveis/dp.php');
error_reporting(0);
session_start('funcionarios');

/////////////////////////////////////// INÍCIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel	= "P";  // P=> Retrato L=>Paisagem
$unidade	= "mm"; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= "A4"; // A3, A4, A5, Letter, Legal
$pdf=new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
$pdf->AddPage();
$pdf->SetTopMargin(2);//2.5 deixar este valor
$pdf->SetLeftMargin(12);
$pdf->SetAutoPageBreak('false', 0);
global $pv,$ph; //valor baseado em mm do A4
if($formato_papel=="A4") {
    if($tipo_papel=="P") {
        $pv = 295/100;
        $ph = 205/100;
    }else {
        $pv = 205/100;
        $ph = 295/100;
    }
}else {
    echo 'Formato não definido';
}

$data_atual = date('Y-m-d');

//Busca a próxima Data do Holerith, maior do que a Data Atual digitada pelo usuário no Filtro ...
$sql = "SELECT id_vale_data, data, qtde_dias_uteis_mes, qtde_dias_inuteis_mes 
        FROM `vales_datas` 
        WHERE `data` > '$data_atual' LIMIT 1 ";
$campos_data = bancos::sql($sql);
if(count($campos_data) == 1) {
    $id_vale_data   = $campos_data[0]['id_vale_data'];
    $data_holerith  = data::datetodata($campos_data[0]['data'], '/');
}

//1) Busca somente das Contas Correntes utilizadas p/ a Folha de Pagto ...
$sql = "SELECT b.banco, a.cod_agencia, cc.id_empresa, cc.conta_corrente, e.nomefantasia 
        FROM `contas_correntes` cc 
        INNER JOIN `agencias` a ON a.id_agencia = cc.id_agencia 
        INNER JOIN `bancos` b ON b.id_banco = a.id_banco 
        INNER JOIN `empresas` e ON e.id_empresa = cc.id_empresa 
        WHERE cc.`id_contacorrente` IN (3, 4, 5, 7, 8) ORDER BY e.nomefantasia ";
$campos = bancos::sql($sql);
$linhas = count($campos);
//Disparando o Loop das Contas Correntes 
for($i = 0; $i < $linhas; $i++) {
//Controle p/ saber quais Funcionários buscar referente ao banco no loop ...
    if(strtoupper($campos[$i]['banco']) == 'BRADESCO') {
        $cod_banco = 1;
    }else if(strtoupper($campos[$i]['banco']) == 'UNIBANCO') {
        $cod_banco = 3;
    }
/*2)
Listagem de Funcionários que ainda estão trabalhando com o id_empresa e do Banco Específico ...
Só não exibo os funcionários Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes não são funcionários, simplesmente só possuem cadastrado 
no Sistema p/ poder acessar algumas telas ...*/
    $sql = "SELECT id_funcionario, nome, agencia, conta_corrente 
            FROM `funcionarios` 
            WHERE `id_empresa` = '".$campos[$i]['id_empresa']."' 
            AND `status` < '3' 
            AND `cod_banco` = '$cod_banco' 
            AND `id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) ORDER BY nome ";
    $campos_funcionario = bancos::sql($sql);
    $linhas_funcionario = count($campos_funcionario);
/*Se existir pelo menos 1 funcionário nesse banco, então eu exibo essa linha com os dados 
da Empresa e do Banco ...*/
    if($linhas_funcionario > 0) {
        $pdf->SetTextColor(0, 0, 0);//Cor Preta como sendo Padrão
/*****************************************Dados da Empresa e Banco*****************************************/
//Imprimindo os Dados da Empresa ...
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($GLOBALS['ph']*8, 5, 'EMPRESA: ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($GLOBALS['ph']*10, 5, strtoupper($campos[$i]['nomefantasia']), 0, 0, 'L');
//Imprimindo os Dados Bancários ...
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($GLOBALS['ph']*6, 5, 'CONTA: ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($GLOBALS['ph']*22, 5, strtoupper($campos[$i]['banco']).' / '.strtoupper($campos[$i]['cod_agencia']).' / '.strtoupper($campos[$i]['conta_corrente']), 0, 0, 'L');
//Data de Holerith
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($GLOBALS['ph']*12, 5, 'Data de Holerith: ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($GLOBALS['ph']*10, 5, $data_holerith, 0, 0, 'L');
//Data de Impressão
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($GLOBALS['ph']*13, 5, 'Data de Impressão: ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($GLOBALS['ph']*10, 5, date('d/m/Y H:i:s'), 0, 1, 'L');
        $pdf->Ln(2);
/*****************************************Dados do Funcionário*****************************************/
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*40, 5, 'FUNCIONÁRIO', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph']*20, 5, 'AGÊNCIA / CONTA', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph']*25, 5, 'VALOR À DEPOSITAR', 1, 1, 'C');
    }
//Disparando o Loop dos Funcionários ...
    $total_geral = 0;//Aqui eu sempre tenho que limpar essa variável p/ acumular o valor do loop anterior
    for($j = 0; $j < $linhas_funcionario; $j++) {
        if($j % 2 == 0) {
            $pdf->SetFillColor(255, 255, 255);//Cor Branca
        }else {
            $pdf->SetFillColor(200, 200, 200);//Cor Cinza
        }
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph']*40, 5, strtoupper($campos_funcionario[$j]['nome']), 1, 0, 'L', 1);
        $pdf->Cell($GLOBALS['ph']*20, 5, strtoupper($campos_funcionario[$j]['agencia'].' / '.$campos_funcionario[$j]['conta_corrente']), 1, 0, 'C', 1);
//Busca do salário do Funcionário depositado na parte do Crédito de Holerith ...
        $sql = "SELECT valor_total_receber 
                FROM `funcionarios_vs_holeriths` 
                WHERE `id_funcionario` = ".$campos_funcionario[$j]['id_funcionario']." 
                AND `id_vale_data` = '$id_vale_data' LIMIT 1 ";        
        $campos_holerith = bancos::sql($sql);
//Quando o Valor for negativo, então eu printo este como sendo Zero ...
        if($campos_holerith[0]['valor_total_receber'] < 0) {
            $pdf->Cell($GLOBALS['ph']*25, 5, 'R$ '.number_format(0, 2, ',', '.'), 1, 1, 'R', 1);
        }else {
            $pdf->Cell($GLOBALS['ph']*25, 5, 'R$ '.number_format($campos_holerith[0]['valor_total_receber'], 2, ',', '.'), 1, 1, 'R', 1);
            $total_geral+= $campos_holerith[0]['valor_total_receber'];
        }
    }
//Imprimindo o Total Geral da Folha de ...
    if($linhas_funcionario > 0) {//Se existir pelo menos 1 funcionário, então eu exibo esse rodapé ...
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*60, 5, 'TOTAL GERAL: ', 1, 0, 'L');
        $pdf->Cell($GLOBALS['ph']*25, 5, 'R$ '.number_format($total_geral, 2, ',', '.'), 1, 1, 'R');
        $pdf->AddPage();
    }
}

/*************************************************************************************************/
/*Listagem de Funcionários que não possuem Conta em Banco por Empresa ...
Só não exibo os funcionários Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes não são funcionários, simplesmente só possuem cadastrado 
no Sistema p/ poder acessar algumas telas ...*/
/*************************************************************************************************/

//Formas de Pagamento ...
$forma_pagamento[0] = 'N';//Nenhum ...
$forma_pagamento[1] = 'C';//Cheque ...
$forma_pagamento[2] = 'D';//Dinheiro ...

for($i = 0; $i < count($forma_pagamento); $i++) {
    $sql = "SELECT e.nomefantasia, f.id_funcionario, f.nome 
            FROM `funcionarios` f 
            INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa 
            WHERE f.`status` < '3' 
            AND f.`cod_banco` = '0' 
            AND f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) 
            AND f.`cheque_dinheiro` = '$forma_pagamento[$i]' ORDER BY f.nome, f.cheque_dinheiro ";
    $campos_funcionario = bancos::sql($sql);
    $linhas_funcionario = count($linhas_funcionario);
    if($linhas_funcionario > 0) {//Se existir pelo menos 1 funcionário, então eu exibo essa linha de cabeçalho ...
/***************************Cabeçalho por Empresa***************************/
        $pdf->SetTextColor(0, 0, 0);//Cor Preta como sendo Padrão
//Imprimindo os Dados da Empresa ...
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($GLOBALS['ph']*42, 5, 'FUNCIONÁRIOS QUE NÃO POSSUEM CONTA BANCÁRIA ', 0, 0, 'L');
//Data de Holerith
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($GLOBALS['ph']*12, 5, 'Data de Holerith: ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($GLOBALS['ph']*12, 5, $data_holerith, 0, 0, 'L');
//Data de Impressão
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($GLOBALS['ph']*13, 5, 'Data de Impressão: ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($GLOBALS['ph']*10, 5, date('d/m/Y H:i:s'), 0, 1, 'L');
//Forma de Pagamento ...
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($GLOBALS['ph']*15, 5, 'Forma de Pagamento: ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8);
//Tramentos com a forma de pagamento ...
        if($forma_pagamento[$i] == 'N') {
            $apresentar = 'NENHUM';
        }else if($forma_pagamento[$i] == 'C') {
            $apresentar = 'CHEQUE';
        }else {
            $apresentar = 'DINHEIRO';
        }
        $pdf->Cell($GLOBALS['ph']*10, 5, $apresentar, 0, 1, 'L');
        $pdf->Ln(3);
/***************************************************************************/
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*40, 5, 'FUNCIONÁRIO', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph']*25, 5, 'EMPRESA', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph']*20, 5, 'VALOR À RECEBER', 1, 1, 'C');
//Disparando o Loop dos Funcionários ...
        $total_geral = 0;//Aqui eu sempre tenho que limpar essa variável p/ acumular o valor do loop anterior
        for($j = 0; $j < $linhas_funcionario; $j++) {
            if($j % 2 == 0) {
                $pdf->SetFillColor(255, 255, 255);//Cor Branca
            }else {
                $pdf->SetFillColor(200, 200, 200);//Cor Cinza
            }
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell($GLOBALS['ph']*40, 5, strtoupper($campos_funcionario[$j]['nome']), 1, 0, 'L', 1);
            $pdf->Cell($GLOBALS['ph']*25, 5, strtoupper($campos_funcionario[$j]['nomefantasia']), 1, 0, 'L', 1);
//Busca do salário do Funcionário depositado na parte do Crédito de Holerith ...
            $sql = "SELECT valor_total_receber 
                    FROM `funcionarios_vs_holeriths` 
                    WHERE `id_funcionario` = '".$campos_funcionario[$j]['id_funcionario']."' 
                    AND `id_vale_data` = '$id_vale_data' LIMIT 1 ";
            $campos_holerith = bancos::sql($sql);
//Quando o Valor for negativo, então eu printo este como sendo Zero ...
            if($campos_holerith[0]['valor_total_receber'] < 0) {
                $pdf->Cell($GLOBALS['ph']*20, 5, 'R$ '.number_format(0, 2, ',', '.'), 1, 1, 'R', 1);
            }else {
                $pdf->Cell($GLOBALS['ph']*20, 5, 'R$ '.number_format($campos_holerith[0]['valor_total_receber'], 2, ',', '.'), 1, 1, 'R', 1);
                $total_geral+= $campos_holerith[0]['valor_total_receber'];
            }
        }
//Imprimindo o Total Geral da Folha de ...
        if($linhas_funcionario > 0) {//Se existir pelo menos 1 funcionário, então eu exibo esse rodapé ...
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell($GLOBALS['ph']*65, 5, 'TOTAL GERAL: ', 1, 0, 'L');
            $pdf->Cell($GLOBALS['ph']*20, 5, 'R$ '.number_format($total_geral, 2, ',', '.'), 1, 1, 'R');
            if(($i + 1) < count($forma_pagamento)) {//Enquanto não chegar no último registro, eu posso quebrar a  Pág.
                $pdf->AddPage();
            }
        }
    }
}

chdir('../../../../../../pdf');
$file = '../../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<html><body></body><Script Language='JavaScript'>document.location='$file';</Script></html>";//JavaScript redirection
?>