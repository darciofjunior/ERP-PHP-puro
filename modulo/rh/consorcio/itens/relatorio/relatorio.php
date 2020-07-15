<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
require('../../../../../lib/num_por_extenso_em_rs.php');
segurancas::geral('/erp/albafer/modulo/rh/consorcio/itens/consultar.php', '../../../../../');

error_reporting(0);
/////////////////////////////////////// INÍCIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel	= 'P';// P=> Retrato L=>Paisagem
$unidade	= 'mm';// pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= 'A4';// A3, A4, A5, Letter, Legal
$pdf = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
$pdf->AddPage();
global $pv, $ph; //valor baseado em mm do A4

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

$pdf->SetTopMargin(2);//2.5 deixar este valor
$pdf->SetLeftMargin(12);
$pdf->SetAutoPageBreak('false', 0);

//Busca de Dados do Consórcio ...
$sql = "SELECT * 
	FROM `consorcios` 
	WHERE `id_consorcio` = '$_GET[id_consorcio]' LIMIT 1 ";
$campos         = bancos::sql($sql);
$nome_grupo     = $campos[0]['nome_grupo'];
$valor          = $campos[0]['valor'];
$juros          = number_format($campos[0]['juros'], 2, ',', '.');
$meses          = $campos[0]['meses'];

//Aqui eu já deixo pré carregado todas as Datas de Débito a partir da Data Atual ...
$data_atual = date('Y-m-d');
//Aqui eu verifico a qtde de Datas de Débito que existem cadastradas no Sistema a partir da Data Atual ...
$sql = "SELECT data 
	FROM `vales_datas` 
	WHERE `data` > '$data_atual' LIMIT $meses ";
$campos_data_debito = bancos::sql($sql);
$data_holerith      = data::datetodata($campos_data_debito[0]['data'], '/');
$mes_inicio         = intval(substr($campos_data_debito[0]['data'], 5, 2));
$ano_inicio         = substr($campos_data_debito[0]['data'], 0, 4);
$mes_final          = intval(substr($campos_data_debito[$meses - 1]['data'], 5, 2));
$ano_final          = substr($campos_data_debito[$meses - 1]['data'], 0, 4);

//Vetor de Mesês, vai estar sendo utilizado mais abaixo ...
$vetor_meses = array('', 'JANEIRO', 'FEVEREIRO', 'MARÇO', 'ABRIL', 'MAIO', 'JUNHO', 'JULHO', 'AGOSTO', 'SETEMBRO', 'OUTUBRO', 'NOVEMBRO', 'DEZEMBRO');

//Busca do(s) Funcionário(s) do Consórcio ...
$sql = "SELECT f.nome 
	FROM `consorcios_vs_funcionarios` cf 
	INNER JOIN `funcionarios` f ON f.`id_funcionario` = cf.`id_funcionario` 
	WHERE cf.`id_consorcio` = '$_GET[id_consorcio]' ORDER BY f.nome ";
$campos = bancos::sql($sql);
$linhas = count($campos);

$pdf->SetFont('Arial', 'BU', 10);
//Consórcio ...
$pdf->Cell($GLOBALS['ph']*90, 5, 'CONSÓRCIO '.$nome_grupo, 0, 1, 'C');
$pdf->Ln(2);

//Período ...
$periodo = $vetor_meses[$mes_inicio].'/'.$ano_inicio.' À '.$vetor_meses[$mes_final].'/'.$ano_final;
$pdf->Cell($GLOBALS['ph']*90, 5, 'INÍCIO '.$periodo, 0, 1, 'C');
$pdf->Ln(2);

//Participantes ...
$pdf->Cell($GLOBALS['ph']*90, 5, 'PARTICIPANTES', 0, 1, 'C');
$pdf->Ln(6);

$pdf->SetFont('Arial', 'B', 10);

for($i = 0; $i < $linhas; $i++) {
    $total_nome         = strlen($campos[$i]['nome']);
    $total_caracteres   = 100;
//Controle com os Traços p/ a Linha não ficar torta ...
    for($j = 0; $j < ($total_caracteres - $total_nome); $j++) $traco.= '_';
    $pdf->Cell($GLOBALS['ph']*90, 5, ($i + 1).' - '.strtoupper($campos[$i]['nome']).$traco, 0, 1, 'L');
    $traco = '';//Aqui eu limpo os traços p/ não acumular com o do loop anterior ...
    $pdf->Ln(6);
}

$pdf->Ln(5);
$pdf->SetFont('Arial', 'BU', 10);
//Regras
$pdf->Cell($GLOBALS['ph']*90, 5, 'REGRAS:', 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 10);

$pdf->Ln(2);
$pdf->Cell($GLOBALS['ph']*90, 5, '- O VALOR INICIAL SERÁ DE R$ '.number_format($valor, 2, ',', '.').' ('.strtoupper(extenso($valor, 1)).'), ', 0, 1, 'L');
$pdf->Cell($GLOBALS['ph']*90, 5, 'CORRIGIDO MENSALMENTE EM '.$juros.'% AO MÊS.', 0, 1, 'L');
$pdf->Ln(5);

//Disparo do Loop em Relação a cada Mês do Consórcio ...
for($i = 0; $i < $meses; $i++) {
/****************************Preparando a variável p/ exibir na Tela****************************/
    $data_debito = data::datetodata($campos_data_debito[$i]['data'], '/');
/***********************************************************************************************/
    $pdf->Cell($GLOBALS['ph']*90, 5, ($i + 1).'º MÊS - '.$data_debito.' - R$ '.number_format($valor, 2, ',', '.'), 0, 1, 'L');
//Cálculo de Valor p/ o próximo Mês do Consórcio ...
    $valor*= (1 + $juros / 100);//Aqui eu já cálculo o juros em cima do valor p/ o próx. mês ...
}

$pdf->Ln(5);

$pdf->Cell($GLOBALS['ph']*90, 5, '- O VALOR SERÁ ABATIDO NO PAGAMENTO. O SORTEIO SERÁ NO DIA DO PAGAMENTO. O PRIMEIRO ', 0, 1, 'L');
$pdf->Cell($GLOBALS['ph']*90, 5, 'DESCONTO SERÁ NO PAGAMENTO REF. AO MÊS DE '.$vetor_meses[$mes_inicio].' E O PAGAMENTO DO PRÊMIO SERÁ EFETUADO ', 0, 1, 'L');
$pdf->Cell($GLOBALS['ph']*90, 5, 'NO PRÓXIMO DIA ÚTIL APÓS O SORTEIO, E ASSIM SUCESSIVAMENTE.', 0, 1, 'L');

$pdf->Ln(2);

$pdf->Cell($GLOBALS['ph']*90, 5, '- CASO A PESSOA VENHA A SAIR DA EMPRESA E JÁ TENHA SIDO SORTEADA, O SALDO (CORRIGIDO) ', 0, 1, 'L');
$pdf->Cell($GLOBALS['ph']*90, 5, 'SERÁ DESCONTADO NA RESCISÃO. SE A PESSOA AINDA NÃO TIVER SIDO SORTEADA, A EMPRESA FICA ', 0, 1, 'L');
$pdf->Cell($GLOBALS['ph']*90, 5, 'COM A COTA DA MESMA DEVOLVENDO O VALOR JÁ PAGO PELA PESSOA.', 0, 1, 'L');

chdir('../../../../../pdf');
$file='../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>