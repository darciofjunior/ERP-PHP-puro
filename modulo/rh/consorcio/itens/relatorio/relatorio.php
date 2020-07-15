<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
require('../../../../../lib/num_por_extenso_em_rs.php');
segurancas::geral('/erp/albafer/modulo/rh/consorcio/itens/consultar.php', '../../../../../');

error_reporting(0);
/////////////////////////////////////// IN�CIO PDF /////////////////////////////////////////////////////////
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
    echo 'Formato n�o definido';
}

$pdf->SetTopMargin(2);//2.5 deixar este valor
$pdf->SetLeftMargin(12);
$pdf->SetAutoPageBreak('false', 0);

//Busca de Dados do Cons�rcio ...
$sql = "SELECT * 
	FROM `consorcios` 
	WHERE `id_consorcio` = '$_GET[id_consorcio]' LIMIT 1 ";
$campos         = bancos::sql($sql);
$nome_grupo     = $campos[0]['nome_grupo'];
$valor          = $campos[0]['valor'];
$juros          = number_format($campos[0]['juros'], 2, ',', '.');
$meses          = $campos[0]['meses'];

//Aqui eu j� deixo pr� carregado todas as Datas de D�bito a partir da Data Atual ...
$data_atual = date('Y-m-d');
//Aqui eu verifico a qtde de Datas de D�bito que existem cadastradas no Sistema a partir da Data Atual ...
$sql = "SELECT data 
	FROM `vales_datas` 
	WHERE `data` > '$data_atual' LIMIT $meses ";
$campos_data_debito = bancos::sql($sql);
$data_holerith      = data::datetodata($campos_data_debito[0]['data'], '/');
$mes_inicio         = intval(substr($campos_data_debito[0]['data'], 5, 2));
$ano_inicio         = substr($campos_data_debito[0]['data'], 0, 4);
$mes_final          = intval(substr($campos_data_debito[$meses - 1]['data'], 5, 2));
$ano_final          = substr($campos_data_debito[$meses - 1]['data'], 0, 4);

//Vetor de Mes�s, vai estar sendo utilizado mais abaixo ...
$vetor_meses = array('', 'JANEIRO', 'FEVEREIRO', 'MAR�O', 'ABRIL', 'MAIO', 'JUNHO', 'JULHO', 'AGOSTO', 'SETEMBRO', 'OUTUBRO', 'NOVEMBRO', 'DEZEMBRO');

//Busca do(s) Funcion�rio(s) do Cons�rcio ...
$sql = "SELECT f.nome 
	FROM `consorcios_vs_funcionarios` cf 
	INNER JOIN `funcionarios` f ON f.`id_funcionario` = cf.`id_funcionario` 
	WHERE cf.`id_consorcio` = '$_GET[id_consorcio]' ORDER BY f.nome ";
$campos = bancos::sql($sql);
$linhas = count($campos);

$pdf->SetFont('Arial', 'BU', 10);
//Cons�rcio ...
$pdf->Cell($GLOBALS['ph']*90, 5, 'CONS�RCIO '.$nome_grupo, 0, 1, 'C');
$pdf->Ln(2);

//Per�odo ...
$periodo = $vetor_meses[$mes_inicio].'/'.$ano_inicio.' � '.$vetor_meses[$mes_final].'/'.$ano_final;
$pdf->Cell($GLOBALS['ph']*90, 5, 'IN�CIO '.$periodo, 0, 1, 'C');
$pdf->Ln(2);

//Participantes ...
$pdf->Cell($GLOBALS['ph']*90, 5, 'PARTICIPANTES', 0, 1, 'C');
$pdf->Ln(6);

$pdf->SetFont('Arial', 'B', 10);

for($i = 0; $i < $linhas; $i++) {
    $total_nome         = strlen($campos[$i]['nome']);
    $total_caracteres   = 100;
//Controle com os Tra�os p/ a Linha n�o ficar torta ...
    for($j = 0; $j < ($total_caracteres - $total_nome); $j++) $traco.= '_';
    $pdf->Cell($GLOBALS['ph']*90, 5, ($i + 1).' - '.strtoupper($campos[$i]['nome']).$traco, 0, 1, 'L');
    $traco = '';//Aqui eu limpo os tra�os p/ n�o acumular com o do loop anterior ...
    $pdf->Ln(6);
}

$pdf->Ln(5);
$pdf->SetFont('Arial', 'BU', 10);
//Regras
$pdf->Cell($GLOBALS['ph']*90, 5, 'REGRAS:', 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 10);

$pdf->Ln(2);
$pdf->Cell($GLOBALS['ph']*90, 5, '- O VALOR INICIAL SER� DE R$ '.number_format($valor, 2, ',', '.').' ('.strtoupper(extenso($valor, 1)).'), ', 0, 1, 'L');
$pdf->Cell($GLOBALS['ph']*90, 5, 'CORRIGIDO MENSALMENTE EM '.$juros.'% AO M�S.', 0, 1, 'L');
$pdf->Ln(5);

//Disparo do Loop em Rela��o a cada M�s do Cons�rcio ...
for($i = 0; $i < $meses; $i++) {
/****************************Preparando a vari�vel p/ exibir na Tela****************************/
    $data_debito = data::datetodata($campos_data_debito[$i]['data'], '/');
/***********************************************************************************************/
    $pdf->Cell($GLOBALS['ph']*90, 5, ($i + 1).'� M�S - '.$data_debito.' - R$ '.number_format($valor, 2, ',', '.'), 0, 1, 'L');
//C�lculo de Valor p/ o pr�ximo M�s do Cons�rcio ...
    $valor*= (1 + $juros / 100);//Aqui eu j� c�lculo o juros em cima do valor p/ o pr�x. m�s ...
}

$pdf->Ln(5);

$pdf->Cell($GLOBALS['ph']*90, 5, '- O VALOR SER� ABATIDO NO PAGAMENTO. O SORTEIO SER� NO DIA DO PAGAMENTO. O PRIMEIRO ', 0, 1, 'L');
$pdf->Cell($GLOBALS['ph']*90, 5, 'DESCONTO SER� NO PAGAMENTO REF. AO M�S DE '.$vetor_meses[$mes_inicio].' E O PAGAMENTO DO PR�MIO SER� EFETUADO ', 0, 1, 'L');
$pdf->Cell($GLOBALS['ph']*90, 5, 'NO PR�XIMO DIA �TIL AP�S O SORTEIO, E ASSIM SUCESSIVAMENTE.', 0, 1, 'L');

$pdf->Ln(2);

$pdf->Cell($GLOBALS['ph']*90, 5, '- CASO A PESSOA VENHA A SAIR DA EMPRESA E J� TENHA SIDO SORTEADA, O SALDO (CORRIGIDO) ', 0, 1, 'L');
$pdf->Cell($GLOBALS['ph']*90, 5, 'SER� DESCONTADO NA RESCIS�O. SE A PESSOA AINDA N�O TIVER SIDO SORTEADA, A EMPRESA FICA ', 0, 1, 'L');
$pdf->Cell($GLOBALS['ph']*90, 5, 'COM A COTA DA MESMA DEVOLVENDO O VALOR J� PAGO PELA PESSOA.', 0, 1, 'L');

chdir('../../../../../pdf');
$file='../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>