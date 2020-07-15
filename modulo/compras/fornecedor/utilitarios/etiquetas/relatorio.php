<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/fornecedor/utilitarios/etiquetas/etiquetas.php', '../../../../../');

define('FPDF_FONTPATH', 'font/');
$pdf	= new FPDF();
$pdf->FPDF('P', 'mm', 'etiqueta');
$pdf->Open();
$pdf->SetTopMargin(-1.5);//2.5 deixar este valor
$pdf->SetLeftMargin(10);
$pdf->SetAutoPageBreak('false', 0);
$pdf->SetFont('Arial', '', 8);
$pdf->AddPage();

//Transforma em vetor
$vetor_fornecedores_selecionados = explode(',', $_POST['cmb_fornecedores_selecionados']);

/*Aqui dispara o vetor de clientes selecionados*/
foreach($vetor_fornecedores_selecionados as $id_fornecedor) $id_fornecedores.= $id_fornecedor.', ';
$id_fornecedores = substr($id_fornecedores, 0, strlen($id_fornecedores) - 2);

//Aqui trago alguns dados do(s) Fornecedor(es) selecionados ...
$sql = "SELECT `razaosocial`, `endereco`, `bairro`, `cep`, `num_complemento` 
        FROM `fornecedores` 
        WHERE `id_fornecedor` IN ($id_fornecedores) 
        AND `ativo` = '1' LIMIT 1 ";
$campos = bancos::sql($sql);
$linhas = count($campos);

$flag       = 0;
$largura1   = 90;
$largura2   = 102;
$altura     = 5.8;
$bordas     = '';//TLR

for($i = 0; $i < $linhas; $i+=2) {//Aqui vou acrescentando o i de 2 em 2 por causa da outra coluna ao lado
    //Estou separando de 2 em 2 porque a 2 linha seria como se fosse a outra coluna
    //Segunda Coluna - Verifico se exite a etiqueta ao lado
    if(($i + 1) < $linhas) {
        $pdf->Cell($largura1, $altura, $campos[$i]['razaosocial'], $bordas, 0, 'L');
        $pdf->Cell($largura2, $altura, $campos[$i + 1]['razaosocial'], $bordas, 1, 'L');
    }else {
        $pdf->Cell($largura1, $altura, $campos[$i]['razaosocial'], $bordas, 1, 'L');
    }
    //Segunda Coluna - Verifico se exite a etiqueta ao lado
    if(($i + 1) < $linhas) {
        if(empty($campos[$i]['bairro'])) {
            $pdf->Cell($largura1, $altura, $campos[$i]['endereco'].', '.$campos[$i]['num_complemento'], $bordas, 0, 'L');
            $pdf->Cell($largura2, $altura, $campos[$i + 1]['endereco'].', '.$campos[$i + 1]['num_complemento'],$bordas, 1, 'L');
        }else{
            $pdf->Cell($largura1, $altura, $campos[$i]['endereco'].', '.$campos[$i]['num_complemento']." - ".strtoupper($campos[$i]['bairro']), $bordas, 0, 'L');
            $pdf->Cell($largura2, $altura, $campos[$i + 1]['endereco'].', '.$campos[$i + 1]['num_complemento']. " - ".strtoupper($campos[$i + 1]['bairro']),$bordas, 1, 'L');
        }
    }else {
        if(empty($campos[$i]['bairro'])) {
            $pdf->Cell($largura1, $altura, $campos[$i]['endereco'].', '.$campos[$i]['num_complemento'], $bordas, 1, 'L');
        }else {
            $pdf->Cell($largura1, $altura, $campos[$i]['endereco'].', '.$campos[$i]['num_complemento']. "-" . $campos[$i]['bairro'], $bordas, 1, 'L');
        }
    }
    //Segunda Coluna - Verifico se exite a etiqueta ao lado
    if(($i + 1) < $linhas) {
        $estado     = strtoupper($campos[$i]['uf']);
        $estado1    = strtoupper($campos[$i +1]['uf']);
        $pdf->Cell($largura1, $altura, $campos[$i]['cep'].' - '.$estado.' - A/C DEPTO. '.$cmb_depto[$i], $bordas , 0, 'L');
        $pdf->Cell($largura1, $altura, $campos[$i + 1]['cep'].' - '.$estado1 . ' - A/C DEPTO. '.$cmb_depto[$i], $bordas, 1, 'L');
    }else {
        $estado = strtoupper($campos[$i]['uf']);
        $pdf->Cell($largura1, $altura, $campos[$i]['cep'].' - '.$estado.' - A/C DEPTO. '.$cmb_depto[$i], $bordas , 0, 'L');
    }
    //Segunda Coluna - Verifico se exite a etiqueta ao lado
    if(($i + 1) < $linhas) {
        if(empty($_POST['txt_contato'][$i])) {
            //Se $_POST['txt_contato'] for vazio naum imprime nda na 1 coluna
            $pdf->Cell($largura1, $altura, ' ', $bordas, 0, 'L'); //BLR
        }else {
            //Se $_POST['txt_contato'] for verdadeiro imprime na 1 coluna
            $pdf->Cell($largura1, $altura, 'ATT.: '.strtoupper($_POST['txt_contato'][$i]), $bordas, 0, 'L'); //BLR
        }
        if(empty($_POST['txt_contato'][$i + 1])) {
            //Se $_POST['txt_contato'] for vazio naum imprime nda na 2 coluna
            $pdf->Cell($largura2, $altura, ' ', $bordas, 1, 'L'); //BLR
        }else {
            //Se $_POST['txt_contato'] for verdadeiro imprime na 2 coluna
            $pdf->Cell($largura2, $altura, 'ATT.: '.strtoupper($_POST['txt_contato'][$i + 1]), $bordas, 1, 'L'); //BLR
        }
    }else {
        if(empty($_POST['txt_contato'][$i])){
            $pdf->Cell($largura1, $altura, '' , $bordas, 1, 'L'); //BLR
        }else {
            $pdf->Cell($largura1, $altura, 'ATT.: '.strtoupper($_POST['txt_contato'][$i]), $bordas, 1, 'L'); //BLR
        }
    }
    //Aqui é um macete para poder controlar o espaçamento entre as etiquetas
    if($i != 0) $pdf->Ln(2.3);
    $flag = ($flag == 0) ? 1 : 0;
}

chdir('../../../../../pdf');
$file='../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>