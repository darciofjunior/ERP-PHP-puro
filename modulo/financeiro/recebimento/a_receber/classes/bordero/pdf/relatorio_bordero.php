<?
require('../../../../../../../lib/segurancas.php');
require('../../../../../../../lib/pdf/fpdf.php');
require('../../../../../../../lib/data.php');
define('FPDF_FONTPATH','font/');
session_start('funcionarios');

if($id_emp2 == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp2 == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp2 == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}else if($id_emp2 == 0) {//Todas Empresas
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/todas_empresas/index.php';
}
segurancas::geral($endereco, '../../../../../../');

function cabecalho($data_cabecalho, $tipo_recebimento, $banco) {
    global $pdf;
    $pdf->Cell(190, 5, 'Impresso em '.date('d/m/Y'), 0, 1, 'R');
    $pdf->Ln(3);
    $pdf->SetTextColor(0, 80, 255);
    $pdf->Cell(190, 5, 'Relatório de Bordero', 1, 1, 'C');
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(190, 5, 'Bordero: '.data::datetodata($data_cabecalho, '/').' - '.$tipo_recebimento.' - '.$banco, 1, 1, 'C');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(20, 5, 'Nº da Conta', 1, 0, 'C');
    $pdf->Cell(110, 5, 'Cliente', 1, 0, 'C');
    $pdf->Cell(30, 5, 'Data de Emissão', 1, 0, 'C');
    $pdf->Cell(30, 5, 'Valor Reajustado', 1, 1, 'C');
    $pdf->SetFont('Arial', '', 8);
}

$pdf = new FPDF();
$pdf->Open();
$pdf->SetLeftMargin(4);
$pdf->SetTopMargin(2);
$pdf->AddPage();
$pagina = 1;

//Traz Dados Específicos do Bordero com o id_bordero
$sql = "SELECT bc.banco, SUBSTRING(b.data, 1, 10) AS data, cc.id_contacorrente, tr.id_tipo_recebimento, tr.recebimento 
        FROM `borderos` b 
        INNER JOIN `tipos_recebimentos` tr ON tr.`id_tipo_recebimento` = b.`id_tipo_recebimento` 
        INNER JOIN `contas_correntes` cc ON cc.`id_contacorrente` = b.`id_contacorrente` 
        INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
        INNER JOIN `bancos` bc ON bc.`id_banco` = a.`id_banco` 
        WHERE b.`id_bordero` = '$_GET[id_bordero]' LIMIT 1 ";
$campos = bancos::sql($sql);
//Variáveis que utiliza para fazer o SQL
$data                   = $campos[0]['data'];
$id_conta_corrente      = $campos[0]['id_contacorrente'];
$id_tipo_recebimento    = $campos[0]['id_tipo_recebimento'];
//Variáveis que utiliza para exibição na Tela na Barra de Título
$recebimento            = $campos[0]['recebimento'];
$banco                  = $campos[0]['banco'];

/*Busca todas as contas à Receber com a mesma data do bordero, do mesmo tipo de Conta Corrente e 
do mesmo Tipo de Recebimento*/
$sql = "SELECT c.razaosocial, cr.id_conta_receber, cr.num_conta, cr.data_emissao, cr.valor, tm.simbolo 
        FROM `borderos` b 
        INNER JOIN `contas_receberes` cr ON cr.`id_bordero` = b.`id_bordero` 
        INNER JOIN `clientes` c ON c.`id_cliente` = cr.`id_cliente` 
        INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = cr.`id_tipo_moeda` 
        WHERE b.`data` = '$data' 
        AND b.`id_contacorrente` = '$id_conta_corrente' 
        AND b.`id_tipo_recebimento` = '$id_tipo_recebimento' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
$pdf->SetFont('Arial', '', 8);
cabecalho($data, $recebimento, $banco);

//Variável que contem o número total de registros por folha
$contador_registro = 47;
for($i = 0; $i < $linhas; $i++) {
    //Ele vai decrementando até chegar 0 quando chega a 0 ele coloca o número da página no final da página ...
    if($contador_registro == 0) {
        $pdf->Cell(150, 5, $pagina, 0, 1, 'C');
        $contador_registro = 47;
        $pdf->AddPage();
        cabecalho($data, $recebimento, $banco);
        $pagina++;
    }
    $pdf->Cell(20, 5, $campos[$i]['num_conta'], 1, 0, 'C');
    $pdf->Cell(110, 5, $campos[$i]['razaosocial'], 1, 0, 'L');
    $pdf->Cell(30, 5, data::datetodata($campos[$i]['data_emissao'], '/'), 1, 0, 'C');
    $pdf->Cell(30, 5, $campos[$i]['simbolo'].' '.str_replace('.', ',', $campos[$i]['valor']), 1, 1, 'R');
    $valor_total+= $campos[$i]['valor'];
    $contador_registro--;
}

$pdf->SetTextColor(255, 0, 0);
$pdf->Cell(160, 5, 'Valor Total:', 1, 0, 'R');
$pdf->Cell(30, 5, 'R$ '.number_format($valor_total, 2, ',', '.'), 1, 1, 'R');
$pdf->SetTextColor(0, 0, 0);

/*Se os registros acabarem no meio da folha ou no começo com esse for
ele consegue ir até o fim dessa página para colocar o número da página*/
for($i = $contador_registro; $i > 0; $i--) $pdf->Cell(40, 5, '', 0, 1, 'L');

$pdf->Cell(190, 5, $pagina, 0, 1, 'C');
//Geração do Relatório PDF
chdir('../../../../../../../pdf');
$file='../../../../../../../pdf/'.basename(tempnam(str_replace(trim('/'), '/', getcwd()), 'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<html><body></body><Script Language = 'JavaScript'>document.location='$file';</Script></html>";//JavaScript redirection
?>