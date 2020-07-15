<?
require('../../../lib/pdf/fpdf.php');
require('../../../lib/segurancas.php');
require('../../../lib/faturamentos.php');
require('../../../lib/intermodular.php');
require('../../../lib/data.php');
require('../../classes/array_sistema/array_sistema.php');
segurancas::geral('/erp/albafer/modulo/classes/desconto_semest_cliente/desconto_semestral.php', '../../../');

/////////////////////////////////////// INÍCIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel	= 'P';  // P=> Retrato L=>Paisagem
$unidade        = 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel  = 'A4'; // A3, A4, A5, Letter, Legal
$pdf = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
global $pv,$ph; //valor baseado em mm do A4
if($formato_papel == 'A4') {
    if($tipo_papel == 'P') {
        $pv = 295/100;
        $ph = 205/100;
    }else {
        $pv = 205/100;
        $ph = 295/100;
    }
}else {
    echo 'Formato não definido';
}
$pdf->AddPage();
$data_impressao = date('d/m/Y').' - '.date('H:i:s');
//Marcador de Alinhamento ...
$pdf->SetLeftMargin(8);
$pdf->Ln(2);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($GLOBALS['ph']*55, 5, 'IMPRESSÃO: '.$data_impressao, 0, 0, 1);
$pdf->Ln(7);

/*Devido esse arquivo fazer uma requisição meio pesada no Banco de Dados, então só pra
esse caso eu amento o espaço de armazenamento no Servidor ...*/
ini_set('memory_limit', '250M');

//Esse vetor vai me auxiliar mais abaixo ... 
$vetor_empresa_divisao[] = '';

//Busca das Empresas Divisões cadastradas no Sistema ...
$sql = "SELECT razaosocial 
        FROM `empresas_divisoes` 
        WHERE `ativo` = '1' ";
$campos_empresa_divisao = bancos::sql($sql);
$linhas_empresa_divisao = count($campos_empresa_divisao);

for($i = 0; $i < $linhas_empresa_divisao; $i++) $vetor_empresa_divisao[] = $campos_empresa_divisao[$i]['razaosocial'];

//Busca de todos os Clientes que estão ativos no Sistema e que não pertencem ao Grupo de Telemarketing ...
$sql = "SELECT c.id_cliente, r.nome_fantasia, r.id_representante, cr.id_empresa_divisao, 
        IF(c.nomefantasia = '', c.razaosocial, nomefantasia) AS cliente, 
        cr.desconto_cliente, cr.desconto_cliente_old 
        FROM `clientes` c 
        INNER JOIN `clientes_vs_representantes` cr ON cr.`id_cliente` = c.`id_cliente` AND cr.`desconto_cliente` <> cr.`desconto_cliente_old` 
        INNER JOIN `representantes` r ON r.`id_representante` = cr.`id_representante` AND r.`id_representante` <> '71' 
        WHERE c.`ativo` = '1' ORDER BY cliente, cr.id_empresa_divisao ";
$campos_cliente_desconto    = bancos::sql($sql);
$linhas_cliente             = count($campos_cliente_desconto);
$id_cliente_anterior        = 0;//Controle com o Cliente atual ...

//Listando os Clientes ...
for($i = 0; $i < $linhas_cliente; $i++) {
//Só posso imprimir a 1ª linha p/ cada Cliente, p/ não ficar repetitivo ...
    if($id_cliente_anterior != $campos_cliente_desconto[$i]['id_cliente']) {
        if($i != 0) $pdf->Ln(1);//Só não quebra o Registro na 1ª Linha ...
        $pdf->SetFillColor(200, 200, 200);//Cor Cinza
//Listagem do Cliente Corrente ...
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph']*9, 5, 'CLIENTE: ', 'TBL', 0, 'L', 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph']*55, 5, $campos_cliente_desconto[$i]['cliente'], 'TBR', 0, 1, 1);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell($GLOBALS['ph']*15, 5, 'DESCONTO ATUAL', 1, 0, 'C', 1);
        $pdf->Cell($GLOBALS['ph']*15, 5, 'DESCONTO ANTIGO', 1, 1, 'C', 1);
        $id_cliente_anterior = $campos_cliente_desconto[$i]['id_cliente'];
    }
//Listagem do Representante junto com a Empresa Divisão ...
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell($GLOBALS['ph']*30, 5, $campos_cliente_desconto[$i]['nome_fantasia'], 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*34, 5, $vetor_empresa_divisao[$campos_cliente_desconto[$i]['id_empresa_divisao']], 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*15, 5, number_format($campos_cliente_desconto[$i]['desconto_cliente'],2,',','.'), 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*15, 5, number_format($campos_cliente_desconto[$i]['desconto_cliente_old'],2,',','.'), 1, 1, 'C');
}

chdir('../../../pdf');
//$file='../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>