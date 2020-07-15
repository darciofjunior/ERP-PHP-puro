<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
require('../../../../../lib/faturamentos.php');
require('../../../../classes/nf_carta_correcao/class_carta_correcao.php');
session_start('funcionarios');//É necessário termos a Sessão porque o $_SESSION['id_modulo'] está dentro da mesma ...

if($_SESSION['id_modulo'] == 3) {//Se foi acessado do Módulo de Compras ou Principal ...
    segurancas::geral('/erp/albafer/modulo/compras/nf_carta_correcao/itens/consultar.php', '../../../../../');
}else {//Se foi acessado do Módulo de Faturamento ...
    segurancas::geral('/erp/albafer/modulo/faturamento/nf_carta_correcao/itens/consultar.php', '../../../../../');
}
/////////////////////////////////////// INÍCIO PDF /////////////////////////////////////////////////////////

define('FPDF_FONTPATH', 'font/');
$tipo_papel		= 'P';  // P=> Retrato L=>Paisagem
$unidade		= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel          = 'A4'; // A3, A4, A5, Letter, Legal, Ofício ...
$pdf = new FPDF($tipo_papel, $unidade, $formato_papel);
global $pv,$ph; //valor baseado em mm do A4
if($formato_papel == 'A4') {
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

$pdf->Open();
$pdf->SetLeftMargin(15);
$pdf->SetTopMargin(20);
$pdf->AddPage();
$pdf->SetFont('Arial', '', 11);

//Busca da Empresa da NF ...
$dados          = carta_correcao::dados_nfs($_GET['id_carta_correcao']);
$id_empresa_nf 	= $dados['id_empresa_nf'];
$id_nota        = $dados['id_nota'];
$tipo_nota      = $dados['tipo_nota'];
if($tipo_nota == 'NFE') {//Se for uma NF de Entrada ...
    $email = 'compras@grupoalbafer.com.br';
    $mensagem_nota = 'Vossa';
}else {//Se for uma NF de Saída ...
    $email = 'vendas@grupoalbafer.com.br';
    $mensagem_nota = 'Nossa';
}
$id_negociador	= $dados['id_negociador'];
$numero_nf      = $dados['numero_nf'];
$data_emissao	= $dados['data_emissao'];

//Busca dos Dados da Empresa ...
$sql = "SELECT e.*, ufs.sigla 
        FROM `empresas` e 
        INNER JOIN `ufs` on ufs.id_uf = e.id_uf 
        WHERE e.`id_empresa` = '$id_empresa_nf' LIMIT 1 ";
$campos_empresa = bancos::sql($sql);
$cnpj 	= substr($campos_empresa[0]['cnpj'], 0, 2).'.'.substr($campos_empresa[0]['cnpj'], 2, 3).'.'.substr($campos_empresa[0]['cnpj'], 5, 3).'/'.substr($campos_empresa[0]['cnpj'], 8, 4).'-'.substr($campos_empresa[0]['cnpj'], 12, 2);
$ie 	= substr($campos_empresa[0]['ie'], 0, 3).'.'.substr($campos_empresa[0]['ie'], 3, 3).'.'.substr($campos_empresa[0]['ie'], 6, 3).'.'.substr($campos_empresa[0]['ie'], 9, 3);

$pdf->setTextColor(192, 192, 192);
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell($GLOBALS['ph']*90, 5, $campos_empresa[0]['razaosocial'], 0, 1, 'C');
$pdf->Ln(3);
$pdf->Cell($GLOBALS['ph']*90, 5, 'CNPJ: '.$cnpj.' - INSC. EST.: '.$ie, 0, 1, 'C');
$pdf->Ln(12);

//Dados do Cliente, NF ...
if($tipo_nota == 'NFE') {
    $sql = "SELECT * 
            FROM `fornecedores` 
            WHERE `id_fornecedor` = '$id_negociador' LIMIT 1 ";
    $campos_negociador      = bancos::sql($sql);
    $negociador             = $campos_negociador[0]['razaosocial'];
    $endereco               = $campos_negociador[0]['endereco'];
    $cep                    = $campos_negociador[0]['cep'];
    $bairro                 = $campos_negociador[0]['bairro'];
    $uf                     = $campos_negociador[0]['uf'];
    $ie_negociador          = substr($campos_negociador[0]['insc_est'], 0, 3).'.'.substr($campos_negociador[0]['insc_est'], 3, 3).'.'.substr($campos_negociador[0]['insc_est'], 6, 3).'.'.substr($campos_negociador[0]['insc_est'], 9, 3);
    $cnpj_cpf_negociador    = substr($campos_negociador[0]['cnpj_cpf'], 0, 2).'.'.substr($campos_negociador[0]['cnpj_cpf'], 2, 3).'.'.substr($campos_negociador[0]['cnpj_cpf'], 5, 3).'/'.substr($campos_negociador[0]['cnpj_cpf'], 8, 4).'-'.substr($campos_negociador[0]['cnpj_cpf'], 12, 2);
}else {
    $sql = "SELECT c.*, ufs.sigla 
            FROM `clientes` c 
            LEFT JOIN `ufs` ON ufs.id_uf = c.id_uf 
            WHERE c.`id_cliente` = '$id_negociador' LIMIT 1 ";
    $campos_negociador      = bancos::sql($sql);
    $negociador             = $campos_negociador[0]['razaosocial'];
    $endereco               = $campos_negociador[0]['endereco'].', '.$campos_negociador[0]['num_complemento'];
    $cep                    = $campos_negociador[0]['cep'];
    $bairro                 = $campos_negociador[0]['bairro'];
    $uf                     = $campos_negociador[0]['sigla'];
    $ie_negociador          = substr($campos_negociador[0]['insc_estadual'], 0, 3).'.'.substr($campos_negociador[0]['insc_estadual'], 3, 3).'.'.substr($campos_negociador[0]['insc_estadual'], 6, 3).'.'.substr($campos_negociador[0]['insc_estadual'], 9, 3);
    $cnpj_cpf_negociador    = substr($campos_negociador[0]['cnpj_cpf'], 0, 2).'.'.substr($campos_negociador[0]['cnpj_cpf'], 2, 3).'.'.substr($campos_negociador[0]['cnpj_cpf'], 5, 3).'/'.substr($campos_negociador[0]['cnpj_cpf'], 8, 4).'-'.substr($campos_negociador[0]['cnpj_cpf'], 12, 2);
}

$pdf->setTextColor(0, 0, 0);
//Empresa que está recebendo o Documento ...
$pdf->SetFont('Arial', '', 11);
$pdf->Cell($GLOBALS['ph']*90, 5, 'À '.$negociador, 0, 1, 'L');
$pdf->Cell($GLOBALS['ph']*90, 5, 'CNPJ / CPF: '.cnpj_cpf_negociador.' - Inscrição Estadual: '.$ie_negociador, 0, 1, 'L');
$pdf->Cell($GLOBALS['ph']*90, 5, $endereco.' - '.$bairro.' - '.$uf.' - CEP: '.$cep, 0, 1, 'L');
$pdf->Ln(15);

//Comunicado ...
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell($GLOBALS['ph']*90, 5, 'COMUNICADO DE NÃO APROPRIAÇÃO DE IMPOSTO DESTACADO A MAIOR', 0, 1, 'C');
$pdf->Ln(15);
	
$sql = "SELECT valor_ipi, valor_icms, valor_icms_st 
        FROM `cartas_correcoes` 
        WHERE `id_carta_correcao` = '$_GET[id_carta_correcao]' LIMIT 1 ";
$campos_cartas_correcoes = bancos::sql($sql);
if($campos_cartas_correcoes[0]['valor_ipi'] > 0) {
    $rotulo1 = ' (IPI)';
    $rotulo2 = ' IPI no Valor de R$ '.number_format($campos_cartas_correcoes[0]['valor_ipi'], 2, ',', '.');
}

if($campos_cartas_correcoes[0]['valor_icms'] > 0) {
    $rotulo1.= ' (ICMS)';
    $rotulo2.= ' ICMS no Valor de R$ '.number_format($campos_cartas_correcoes[0]['valor_icms'], 2, ',', '.'); 
}

if($campos_cartas_correcoes[0]['valor_icms_st'] > 0) {
    $rotulo1.= ' (ICMS ST)'; 
    $rotulo2.= ' ICMS ST no Valor de R$ '.number_format($campos_cartas_correcoes[0]['valor_icms_st'], 2, ',', '.'); 
}

//A Empresa xxx ...
$pdf->SetFont('Arial', '', 11);
$pdf->MultiCell($GLOBALS['ph']*90, 5, 'A '.$campos_empresa[0]['razaosocial'].', inscrita no CNPJ / CPF sob o n.º '.$cnpj_cpf_negociador.' e com Inscrição Estadual n.º '.$ie.', estabelecida à '.$campos_empresa[0]['endereco'].' n.º '.$campos_empresa[0]['numero'].' - '.$campos_empresa[0]['bairro'].' - '.$campos_empresa[0]['cidade'].' - '.$campos_empresa[0]['sigla'].' - CEP: '.$campos_empresa[0]['cep'].' vem por meio desta, Declarar a não apropriação do Crédito '.$rotulo1.' relativo ao destaque a maior em vossa Nota Fiscal de Devolução n.º '.$numero_nf.' emitida em '.$data_emissao.', ficando assim Vsa. autorizada a se creditar do '.$rotulo2.', destacado a maior conforme a determinação constante na Legislação vigente.', 0, 1, 'L');
$pdf->Ln(15);

//Para que surtam ...
$pdf->MultiCell($GLOBALS['ph']*90, 5, 'Para que surtam os devidos efeitos e evitarmos sanções fiscais, solicitamos a devolução da 2ª via deste comunicado com vossa ciência mediante a oposição de assinatura e carimbo da empresa, devendo a 1ª via deste ficar arquivada juntamente com a Nota Fiscal em questão pelo prazo prescricional da Legislação vigente.', 0, 1, 'L');
$pdf->Ln(26);
	
$vetor_meses = array('', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');

$pdf->Cell($GLOBALS['ph']*90, 5, 'São Paulo, '.date('d').' de '.$vetor_meses[intval(date('m'))].' de '.date('Y'), 0, 1, 'L');
$pdf->Ln(40);

if($id_empresa_nf == 1) {//Se for Alba
    $linha = '_____________________________________________________';
}else {//Tool ...
    $linha = '__________________________________________';
}

$pdf->Cell($GLOBALS['ph']*90, 5, $linha.'         ________________________________', 0, 1, 'L');
$pdf->Cell($GLOBALS['ph']*90, 5, $campos_empresa[0]['razaosocial'].'          Ciente:', 0, 1, 'L');
$pdf->Ln(15);

$pdf->setTextColor(192, 192, 192);
$pdf->Cell($GLOBALS['ph']*90, 5, $campos_empresa[0]['endereco'].' n.º '.$campos_empresa[0]['numero'].' - '.$campos_empresa[0]['bairro'].' - '.$campos_empresa[0]['cidade'].' - '.$campos_empresa[0]['sigla'].' - CEP: '.$campos_empresa[0]['cep'], 0, 1, 'C');
$pdf->Cell($GLOBALS['ph']*90, 5, 'Fone: (011) 2972-5655 - Email: '.$email, 0, 1, 'C');

chdir('../../../../../pdf');
$file='../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>