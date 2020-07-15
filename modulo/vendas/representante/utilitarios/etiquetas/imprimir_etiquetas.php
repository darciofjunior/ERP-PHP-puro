<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/data.php');
require('../../../../../lib/segurancas.php');
error_reporting(1);

//Busca do Nome do Departamento da Própria Combo de Departamento selecionada ...
$sql = "SELECT departamento 
		FROM `departamentos` 
		WHERE `id_departamento` = '$_POST[cmb_departamento]' 
		AND `ativo` = '1' LIMIT 1 ";
$campos_departamento 	= bancos::sql($sql);
$departamento 			= $campos_departamento[0]['departamento'];

/*Aqui dispara o vetor de representantes selecionados*/
foreach($_POST['cmb_representante_selecionado'] as $id_representante_selecionado) $representantes.= $id_representante_selecionado.', ';
$representantes = substr($representantes, 0, strlen($representantes) - 2);

$sql = "SELECT UPPER(nome_representante) AS representante, endereco, num_comp, bairro, cep, cidade, uf 
		FROM `representantes` 
		WHERE `id_representante` IN ($representantes) 
		AND LENGTH(cep) = '9' 
		AND `ativo` = '1' ORDER BY representante ";
$campos 	= bancos::sql($sql);
$registros 	= count($campos);

/////////////////////////////////////// INÍCIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel		= 'P';  // P=> Retrato L=>Paisagem
$unidade		= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= 'etiqueta_clientes'; // A3, A4, A5, Letter, Legal
$pdf = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->SetTopMargin(1);
$pdf->SetLeftMargin(15);
$pdf->Open();
$pdf->AddPage();
global $pv,$ph; //valor baseado em mm do A4
if($formato_papel == 'etiqueta_clientes') {
	if($tipo_papel == 'P') {
		$pv = 295/100;
		$ph = 205/100;
	}else {
		$pv = 205/100;
		$ph = 295/100;
	}
}else {
	echo 'Formato não definido.';
}
$pdf->SetFont('Arial', '', 10);
$linha 					= 1;//Valor Default ...

for($i = 0; $i < $registros; $i+=2) {
	//Posso ter apenas 12 linhas em uma página, quando chegar na 13ª eu mando inserir uma nova página ...
	if($linha == 13) {
		$linha = 1;
		$pdf->SetTopMargin(1);
		$pdf->AddPage(); 
		$espacador_entre_linhas = 0;
	}
//1ª Linha da Etiquetagem
	//Aqui é a Primeira Coluna de Etiquetas, Coluna Esquerda ...
	$pdf->Cell(100, 5, $campos[$i]['representante'], 0, 0, 'L');
	//Aqui é a Segunda Coluna de Etiquetas, Coluna Direita ...
	$pdf->Cell(100, 5, $campos[$i + 1]['representante'], 0, 1, 'L');
	
//2ª Linha da Etiquetagem
	//Aqui é a Primeira Coluna de Etiquetas, Coluna Esquerda ...
	$end_sem_bairro = $campos[$i]['endereco'].', '.$campos[$i]['num_comp'];
	if(!empty($campos[$i]['bairro'])) $bairro.=' - '.strtoupper($campos[$i]['bairro']);
	$end_com_bairro = $end_sem_bairro.$bairro;

	if(strlen($end_com_bairro) > 40) {//Se o endereco junto c/ o bairro > 40, imprime o ender. s/ bairro
		$endereco = $end_sem_bairro;
	}else {//Imprime o endereço completo
		$endereco = $end_com_bairro;
	}
	$pdf->Cell(100, 5, $endereco, 0, 0, 'L');
	//Aqui é a Segunda Coluna de Etiquetas, Coluna Direita ...
	$end_sem_bairro = $campos[$i + 1]['endereco'].', '.$campos[$i + 1]['num_comp'];
	if(!empty($campos[$i + 1]['bairro'])) $bairro.=' - '.strtoupper($campos[$i + 1]['bairro']);
	$end_com_bairro = $end_sem_bairro.$bairro;

	if(strlen($end_com_bairro) > 40) {//Se o endereco junto c/ o bairro > 40, imprime o ender. s/ bairro
		$endereco = $end_sem_bairro;
	}else {//Imprime o endereço completo
		$endereco = $end_com_bairro;
	}
	$pdf->Cell(100, 5, $endereco, 0, 1, 'L');
//3ª Linha da Etiquetagem	
	//Aqui é a Primeira Coluna de Etiquetas, Coluna Esquerda ...
	$pdf->Cell(100, 5, $campos[$i]['cep'].' - '.$campos[$i]['cidade'].' - '.$campos[$i]['uf'], 0, 0, 'L');
	//Aqui é a Segunda Coluna de Etiquetas, Coluna Direita ...
	$pdf->Cell(100, 5, $campos[$i + 1]['cep'].' - '.$campos[$i + 1]['cidade'].' - '.$campos[$i + 1]['uf'], 0, 1, 'L');
//4ª Linha da Etiquetagem			
	$pdf->Cell(100, 5, 'A/C DEPTO. '.$departamento, 0, 0, 'L');
	//Aqui é a Segunda Coluna de Etiquetas, Coluna Direita ...
	$pdf->Cell(100, 5, 'A/C DEPTO. '.$departamento, 0, 1, 'L');
	$pdf->Ln(5);
	$linha++;//A cada linha de Etiqueta que vai sendo printada, eu vou somando nessa variável ...
	if($linha <= 2) {
		$espacador_entre_linhas = 1;
	}else if($linha <= 4) {
		$espacador_entre_linhas = 2;
	}else if($linha <= 6) {
		$espacador_entre_linhas = 3;
	}else if($linha <= 8) {
		$espacador_entre_linhas = 2;
	}else {
		$espacador_entre_linhas = 2.5;
	}
	$pdf->Ln($espacador_entre_linhas);
}
chdir('../../../../../pdf');
$file='../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>