<?
require('../../../lib/pdf/fpdf.php');
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../classes/array_sistema/array_sistema.php');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');

/////////////////////////////////////// INÍCIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel		= 'P';  // P=> Retrato L=>Paisagem
$unidade		= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel          = 'A4'; // A3, A4, A5, Letter, Legal
$pdf                    = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
global $pv, $ph; //valor baseado em mm do A4
if($formato_papel == 'A4') {
    if($tipo_papel == 'P') {
        $pv = 295 / 100;
        $ph = 205 / 100;
    }else {
        $pv = 205 / 100;
        $ph=  295 / 100;
    }
}else {
    echo 'Formato não definido';
}
$pdf->AddPage();
$data_impressao = date('d/m/Y').' - '.date('H:i:s');
//Marcador de Impressão
$pdf->SetLeftMargin(1);
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell($ph * 100, $pv * 5, 'IMPRESSÃO: '.$data_impressao, 0, 0, 'L');
$pdf->Ln(10);

//Aqui busca todas as Empresas Divisões, vou utilizar + abaixo ...
$sql = "SELECT id_empresa_divisao, razaosocial 
        FROM `empresas_divisoes` 
        WHERE `ativo` = '1' ORDER BY razaosocial ";
$campos_emp_div = bancos::sql($sql);
$linhas_emp_div = count($campos_emp_div);

//Só exibe Clientes que contém Representante
if(empty($cmb_tipo_cliente))    $cmb_tipo_cliente = '%';
if(empty($cmb_representante))   $cmb_representante = '%';
if(empty($cmb_uf))              $cmb_uf = '%';

$sql = "SELECT DISTINCT(c.`id_cliente`), IF(c.`nomefantasia` = '', c.`razaosocial`, c.`nomefantasia`) AS cliente 
        FROM `clientes` c 
        INNER JOIN `clientes_vs_representantes` cr ON c.`id_cliente` = cr.`id_cliente` AND cr.`id_representante` LIKE '$cmb_representante' 
        INNER JOIN `representantes` r ON r.`id_representante` = cr.`id_representante` 
        WHERE c.`nomefantasia` LIKE '%$txt_nome_fantasia%' 
        AND c.`razaosocial` LIKE '%$txt_razao_social%' 
        AND c.`ativo` = '1' 
        AND c.`bairro` LIKE '%$txt_bairro%' 
        AND c.`cidade` LIKE '%$txt_cidade%' 
        AND c.`tipo_cliente` LIKE '$cmb_tipo_cliente' 
        AND c.`id_uf` LIKE '$cmb_uf' 
        GROUP BY c.`id_cliente` ORDER BY `cliente` ";
$campos_clientes = bancos::sql($sql);
$linhas_clientes = count($campos_clientes);
for($i = 0; $i < $linhas_clientes; $i++) {
/****************************Volume de Compra**************************/
    $sql = "SELECT id_empresa_divisao, razaosocial 
            FROM `empresas_divisoes` 
            WHERE `ativo` = '1' ORDER BY razaosocial ";
    $campos_empresas_divisoes = bancos::sql($sql);
    $linhas_empresas_divisoes = count($campos_empresas_divisoes);
	
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(215, 5, 'VOLUME DE COMPRA - '.$campos_clientes[$i]['cliente'], 1, 1, 'C');
	
    $sql = "SELECT ged.`id_empresa_divisao`, YEAR(nfs.`data_emissao`) AS ano, SUM((nfsi.`qtde` - nfsi.`qtde_devolvida`) * nfsi.`valor_unitario`) AS valor_total 
            FROM `clientes` c 
            INNER JOIN `nfs` ON nfs.`id_cliente` = c.`id_cliente` 
            INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = nfsi.`id_produto_acabado` 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            WHERE nfs.`id_cliente` = '".$campos_clientes[$i]['id_cliente']."' 
            GROUP BY YEAR(nfs.`data_emissao`), ged.`id_empresa_divisao` ";
    $campos_faturamento = bancos::sql($sql);
    $linhas_faturamento = count($campos_faturamento);
    for($j = 0; $j < $linhas_faturamento; $j++) $vetor_faturamento[$campos_faturamento[$j]['ano']][$campos_faturamento[$j]['id_empresa_divisao']] = $campos_faturamento[$j]['valor_total'];
    //Aqui busca todos os representantes que estão atrelados a esse Cliente ...
    $largura_coluna_divisao = (215 - 40) / $linhas_empresas_divisoes;
	

    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell(25, 5, 'ANO', 1, 0, 'C');
    for($j = 0; $j < $linhas_empresas_divisoes; $j++) $pdf->Cell($largura_coluna_divisao, 5, $campos_empresas_divisoes[$j]['razaosocial'], 1, 0, 'C');
    $pdf->Cell(15, 5, 'TOTAL R$', 1, 1, 'C');
	
    for($ano = 2006; $ano <= date('Y'); $ano++) {
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(25, 5, $ano.' NOVO (FAT)', 1, 0, 'C');
        $total_por_ano = 0;
        for($j = 0; $j < $linhas_empresas_divisoes; $j++) {
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell($largura_coluna_divisao, 5, number_format($vetor_faturamento[$ano][$campos_empresas_divisoes[$j]['id_empresa_divisao']], 2, ',', '.'), 1, 0, 'C');
            $total_por_ano+= $vetor_faturamento[$ano][$campos_empresas_divisoes[$j]['id_empresa_divisao']];
        }
        $pdf->Cell(15, 5, number_format($total_por_ano, 2, ',', '.'), 1, 1, 'R');
    }
    $pdf->Ln(4);
    //Destruo essa variável p/ não herdar valores do Loop Anterior ...
    unset($vetor_faturamento);
}

chdir('../../../pdf');
$file = '../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>