<?
require('../../../../../../lib/pdf/fpdf.php');
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/depto_pessoal.php');
require('../../../../../../lib/genericas.php');
require('../../../../../../lib/num_por_extenso_em_rs.php');
error_reporting(0);
session_start('funcionarios');

/////////////////////////////////////// INÍCIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel	= 'P';  // P=> Retrato L=>Paisagem
$unidade	= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= 'A4'; // A3, A4, A5, Letter, Legal
$pdf = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
$pdf->SetTopMargin(2);//2.5 deixar este valor
$pdf->SetLeftMargin(8);
$pdf->SetAutoPageBreak('false', 0);
$pdf->SetFont('Arial', '', 10);

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
    echo 'Formato não definido';
}

$separador_colunas = 4;
$altura = 5;
$bordas = 1;

//Busca de Vale(s) do Funcionário ...
$sql = "SELECT vd.*, e.`nomefantasia`, f.`nome` 
        FROM `vales_dps` vd 
        INNER JOIN `funcionarios` f ON f.`id_funcionario` = vd.`id_funcionario` 
        INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
        WHERE vd.`id_vale_dp` IN ($id_vales_dps) ORDER BY f.`nome`, vd.`data_debito` ";
$campos = bancos::sql($sql);
$linhas = count($campos);

$vetor_tipos_vale = depto_pessoal::tipos_vale();

for($i = 0; $i < $linhas; $i+=2) {//Aqui vou acrescentando o i de 2 em 2 por causa da outra coluna ao lado
//Significa que já foram impressos 10 registros daquela página, e sendo assim solicito uma nova página ...
    if($i % 10 == 0) $pdf->AddPage();
    $pdf->Ln(5);
/*Estou separando de 2 em 2 porque a 2 linha seria como se fosse a outra coluna
Segunda Coluna - Verifico se exite a etiqueta ao lado*/
//Nome da Empresa
    if(($i + 1) < $linhas) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 30, $altura, $campos[$i]['nomefantasia'], 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 18, $altura, 'Valor R$ '.number_format($campos[$i]['valor'], 2, ',', '.'), 1, 0, 'R');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 30, $altura, $campos[$i + 1]['nomefantasia'], 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 18, $altura, 'Valor R$ '.number_format($campos[$i + 1]['valor'], 2, ',', '.'), 1, 1, 'R');
    }else {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 30, $altura, $campos[$i]['nomefantasia'], 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 18, $altura, 'Valor R$ '.number_format($campos[$i]['valor'], 2, ',', '.'), 1, 1, 'R');
    }
//Nome do Funcionário ...
    if(($i + 1) < $linhas) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 6, 5, 'Nome: ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 42, 5, $campos[$i]['nome'], 'R', 0, 'L');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 6, 5, 'Nome: ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 42, 5, $campos[$i + 1]['nome'], 'R', 1, 'L');
    }else {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 6, 5, 'Nome: ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 42, 5, $campos[$i]['nome'], 'R', 1, 'L');
    }
    
    //Declarando as variáveis ...
    $observacao1_linha1     = '';
    $observacao1_linha2     = '';
    $observacao2_linha1     = '';
    $observacao2_linha2     = '';
    $qtde_caracteres_linha1 = 30;
    
//Tipo de Vale e Observação 1 que está sendo impressa ...
    if(($i + 1) < $linhas) {
        //Só irá exibir essa variável de parcelamento se o Vale for Avulso, Tipo Consórcio, Empréstimo ou Crédito Consignado ...
        //Parcelamento 1
        if($campos[$i]['tipo_vale'] == 2 || $campos[$i]['tipo_vale'] == 4 || $campos[$i]['tipo_vale'] == 8 || $campos[$i]['tipo_vale'] == 14) {
            if(!empty($campos[$i]['financeira'])) $financeira1 = ' - '.$campos[$i]['financeira'];
            $parcelamento1 = ' - '.$campos[$i]['parcelamento'];
        }else {
            $parcelamento1 = '';
        }
        
        //Tratando a Observação 1 ...
        if(strlen($campos[$i]['observacao']) > $qtde_caracteres_linha1) {
            $vetor_palavras = explode(' ', $campos[$i]['observacao']);
            for($j = 0; $j < count($vetor_palavras); $j++) {
                if(strlen($observacao1_linha1) <= $qtde_caracteres_linha1) {
                    $observacao1_linha1.= $vetor_palavras[$j].' ';
                }else {
                    $observacao1_linha2.= $vetor_palavras[$j].' ';
                }
            }
        }else {
            $observacao1_linha1 = $campos[$i]['observacao'];
        }
        
        //Parcelamento 2
        if($campos[$i + 1]['tipo_vale'] == 2 || $campos[$i + 1]['tipo_vale'] == 4 || $campos[$i + 1]['tipo_vale'] == 8 || $campos[$i + 1]['tipo_vale'] == 14) {
            if(!empty($campos[$i + 1]['financeira'])) $financeira2 = ' - '.$campos[$i + 1]['financeira'];
            $parcelamento2 = ' - '.$campos[$i + 1]['parcelamento'];
        }else {
            $parcelamento2 = '';
        }
        
        //Tratando a Observação 2 ...
        if(strlen($campos[$i + 1]['observacao']) > $qtde_caracteres_linha1) {
            $vetor_palavras = explode(' ', $campos[$i + 1]['observacao']);
            for($j = 0; $j < count($vetor_palavras); $j++) {
                if(strlen($observacao2_linha1) <= $qtde_caracteres_linha1) {
                    $observacao2_linha1.= $vetor_palavras[$j].' ';
                }else {
                    $observacao2_linha2.= $vetor_palavras[$j].' ';
                }
            }
        }else {
            $observacao2_linha1 = $campos[$i + 1]['observacao'];
        }

        //Dados de Impressão ...
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*11.5, 5, 'Tipo do Vale: ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($GLOBALS['ph'] * 36.5, 5, $vetor_tipos_vale[$campos[$i]['tipo_vale']].$financeira1.$parcelamento1.'. '.$observacao1_linha1, 'R', 0, 'L');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*11.5, 5, 'Tipo do Vale: ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($GLOBALS['ph'] * 36.5, 5, $vetor_tipos_vale[$campos[$i + 1]['tipo_vale']].$financeira2.$parcelamento2.'. '.$observacao2_linha1, 'R', 1, 'L');
    }else {//Parcelamento 1 ...
        if($campos[$i]['tipo_vale'] == 2 || $campos[$i]['tipo_vale'] == 4 || $campos[$i]['tipo_vale'] == 8 || $campos[$i]['tipo_vale'] == 14) {
            if(!empty($campos[$i]['financeira'])) $financeira1 = ' - '.$campos[$i]['financeira'];
            $parcelamento1 = ' - '.$campos[$i]['parcelamento'];
        }else {
            $parcelamento1 = '';
        }
        
        //Tratando a Observação 1 ...
        if(strlen($campos[$i]['observacao']) > $qtde_caracteres_linha1) {
            $vetor_palavras = explode(' ', $campos[$i]['observacao']);
            for($j = 0; $j < count($vetor_palavras); $j++) {
                if(strlen($observacao1_linha1) <= $qtde_caracteres_linha1) {
                    $observacao1_linha1.= $vetor_palavras[$j].' ';
                }else {
                    $observacao1_linha2.= $vetor_palavras[$j].' ';
                }
            }
        }else {
            $observacao1_linha1 = $campos[$i]['observacao'];
        }
        
        //Dados de Impressão ...
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 11.5, 5, 'Tipo do Vale: ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($GLOBALS['ph'] * 36.5, 5, $vetor_tipos_vale[$campos[$i]['tipo_vale']].$financeira1.$parcelamento1.'. '.$observacao1_linha1, 'R', 1, 'L');
    }
//Observação Parte 2 ...
    if(($i + 1) < $linhas) {
        $pdf->Cell($GLOBALS['ph'] * 48, 5, $observacao1_linha2, 'LR', 0, 'L');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($GLOBALS['ph'] * 48, 5, $observacao2_linha2, 'LR', 1, 'L');
    }else {
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($GLOBALS['ph'] * 48, 5, $observacao1_linha2, 'LR', 1, 'L');
    }
//Valor por Extenso ...
    if(($i + 1) < $linhas) {
        $pdf->SetFont('Arial', '', 7.3);
        $pdf->Cell($GLOBALS['ph'] * 48, 5, extenso($campos[$i]['valor'], 1), 'LR', 0, 'L');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 7.3);
        $pdf->Cell($GLOBALS['ph'] * 48, 5, extenso($campos[$i + 1]['valor'], 1), 'LR', 1, 'L');
    }else {
        $pdf->SetFont('Arial', '', 7.3);
        $pdf->Cell($GLOBALS['ph'] * 48, 5, extenso($campos[$i]['valor'], 1), 'LR', 1, 'L');
    }
//Linha Vázia ...
    if(($i + 1) < $linhas) {
        $pdf->Cell($GLOBALS['ph'] * 48, 5, '', 'LR', 0, 'L');
        $pdf->Cell($separador_colunas, $altura, '', '', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 48, 5, '', 'LR', 1, 'L');
    }else {
        $pdf->Cell($GLOBALS['ph'] * 48, 5, '', 'LR', 1, 'L');
    }
//Data ...
    if(($i + 1) < $linhas) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 11.5, 5, 'Data de Em.: ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 10, 5, data::datetodata($campos[$i]['data_emissao'], '/'), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 13, 5, ' -  Data de Hol.: ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 13.5, 5, data::datetodata($campos[$i]['data_debito'], '/'), 'R', 0, 'L');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 11.5, 5, 'Data de Em.: ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 10, 5, data::datetodata($campos[$i + 1]['data_emissao'], '/'), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 13, 5, ' -  Data de Hol.: ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 13.5, 5, data::datetodata($campos[$i + 1]['data_debito'], '/'), 'R', 1, 'L');
    }else {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 11.5, 5, 'Data de Em.: ', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 10, 5, data::datetodata($campos[$i]['data_emissao'], '/'), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 13, 5, ' -  Data de Hol.: ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 13.5, 5, data::datetodata($campos[$i]['data_debito'], '/'), 'R', 1, 'L');
    }
//Linha Vázia ...
    if(($i + 1) < $linhas) {
        $pdf->Cell($GLOBALS['ph'] * 48, 5, '', 'LR', 0, 'C');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 48, 5, '', 'LR', 1, 'C');
    }else {
        $pdf->Cell($GLOBALS['ph'] * 48, 5, '', 'LR', 1, 'C');
    }
//Assinatura do Funcionário ...
    if(($i + 1) < $linhas) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*12, 5, 'ASSINATURA', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 36, 5, '____________________________________', 'R', 0, 'L');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*12, 5, 'ASSINATURA', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 36, 5, '____________________________________', 'R', 1, 'L');
    }else {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*12, 5, 'ASSINATURA', 'L', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 36, 5, '____________________________________', 'R', 1, 'L');
    }
//Linha Vázia ...
    if(($i + 1) < $linhas) {
        $pdf->Cell($GLOBALS['ph'] * 48, 5, '', 'LRB', 0, 'C');
        $pdf->Cell($separador_colunas, $altura, '', 0, 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 48, 5, '', 'LRB', 1, 'C');
    }else {
        $pdf->Cell($GLOBALS['ph'] * 48, 5, '', 'LRB', 1, 'C');
    }
}

chdir('../../../../../../pdf');
$file='../../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>