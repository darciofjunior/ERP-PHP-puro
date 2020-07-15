<?
require('../../../lib/pdf/fpdf.php');
require('../../../lib/segurancas.php');
require('../../../lib/faturamentos.php');

switch($opcao) {
    case 1://Significa que veio do Menu Abertas / Liberadas ...
    case 2://Significa que veio do Menu de Liberadas / Faturadas ...
    case 3://Significa que veio do Menu de Faturadas / Empacotadas / Despachadas ...
        segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
    break;
    case 4://Significa que veio do Menu de Devolução 
        segurancas::geral('/erp/albafer/modulo/faturamento/nota_saida/itens/devolucao.php', '../../../../');
    break;
    default://Significa que veio do Menu de Devolução ...
        segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
    break;
}

/////////////////////////////////////// INÍCIO PDF ///////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');

$tipo_papel     = 'P';  // P=> Retrato L=>Paisagem
$unidade        = 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= 'A4'; // A3, A4, A5, Letter, Legal
$pdf            = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
$pdf->SetTopMargin(0);
$pdf->SetAutoPageBreak('false', 0);
$pdf->AddPage();

//Esse vetor de Meses eu vou estar utilizando mais abaixo ...
$vetor_meses = array('', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');

//Aqui traz alguns dados da Nota Fiscal
$sql = "SELECT nfs.*, c.id_pais, c.razaosocial, c.trading as trading_cliente, c.cod_suframa 
        FROM `nfs` 
        INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
        WHERE nfs.`id_nf` = '$_GET[id_nf]' LIMIT 1 ";
$campos                 = bancos::sql($sql);
//Coloquei esse nome na variável porque na sessão já existe uma variável com o nome de id_empresa
$id_cliente             = $campos[0]['id_cliente'];
$id_transportadora	= $campos[0]['id_transportadora'];
$id_nf_vide_nota	= $campos[0]['id_nf_vide_nota'];
$peso_bruto_balanca     = number_format($campos[0]['peso_bruto_balanca'], 2, ',', '.');

//Faço um Loop de 2, porque são 2 vias
for($i = 1; $i <= 2; $i++) {
/***********************************************************************************************/
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetLeftMargin(5);
    $pdf->Ln(15);
    $pdf->Cell(200, 5, 'COMPROVANTE DE ENTREGA DE MATERIAL', 1, 1, 'C');
    $pdf->Ln(3);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(100, 5, 'São Paulo, '.date('d').' de '.$vetor_meses[intval(date('m'))].' de '.date('Y'), 0, 1, 'L');
    $pdf->Ln(3);
    $pdf->Cell(200, 5, '', 'T', 1, 'L');

/**************************Transportadora**************************/
//Busca de Dados da Transportadora ...
    $sql = "SELECT nome, endereco, num_complemento, bairro, cidade, uf, fone, fone2, cep 
            FROM `transportadoras` 
            WHERE `id_transportadora` = '$id_transportadora' LIMIT 1 ";
    $campos_transp      = bancos::sql($sql);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 5, 'TRANSPORT. ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(175, 5, ' -  '.$campos_transp[0]['nome'], 0, 1, 'L');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 5, 'ENDEREÇO ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(175, 5, ' -  '.$campos_transp[0]['endereco'].', '.$campos_transp[0]['num_complemento'].' - '.$campos_transp[0]['bairro'].' - '.$campos_transp[0]['cidade'].' - '.$campos_transp[0]['uf'].' - CEP: '.$campos_transp[0]['cep'], 0, 1, 'L');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 5, 'TELEFONE ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(175, 5, ' -  '.$campos_transp[0]['fone'].' / '.$campos_transp[0]['fone2'], 0, 1, 'L');
    $pdf->Ln(3);
    $pdf->Cell(200, 5, '', 'T', 1, 'L');

/**************************Cliente**************************/
//Busca de Dados do Cliente ...
    $sql = "SELECT c.razaosocial, c.endereco, c.num_complemento, c.bairro, c.cep, c.cidade, ufs.sigla, UPPER(ufs.estado) AS estado 
            FROM `clientes` c 
            INNER JOIN `ufs` ON ufs.id_uf = c.id_uf 
            WHERE c.`id_cliente` = '$id_cliente' LIMIT 1 ";
    $campos_cliente = bancos::sql($sql);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 5, 'CLIENTE ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(175, 5, ' -  '.$campos_cliente[0]['razaosocial'], 0, 1, 'L');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 5, 'ENDEREÇO ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(175, 5, ' -  '.$campos_cliente[0]['endereco'].', '.$campos_cliente[0]['num_complemento'].' - '.$campos_cliente[0]['bairro'].' - '.$campos_cliente[0]['cidade'].' - '.$campos_cliente[0]['sigla'], 0, 1, 'L');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(29, 5, '', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(175, 5, $campos_cliente[0]['estado'].' - CEP: '.$campos_cliente[0]['cep'], 0, 1, 'L');
    $pdf->Ln(3);
    $pdf->Cell(200, 5, '', 'T', 1, 'L');

/**************************Dados da NF**************************/
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 5, 'SGD N.º ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(175, 5, ' -  '.faturamentos::buscar_numero_nf($_GET['id_nf'], 'S'), 0, 1, 'L');

    $calculo_peso_nf = faturamentos::calculo_peso_nf($_GET['id_nf']);
//1) 
    $qtde_volume = $calculo_peso_nf['qtde_caixas'];
//2)
    $peso_liquido_vol = $calculo_peso_nf['peso_liq_total_nf'];

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 5, 'QUANTIDADE ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(175, 5, ' -  '.$qtde_volume, 0, 1, 'L');

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 5, 'ESPÉCIE ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(175, 5, ' -  CAIXA', 0, 1, 'L');

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 5, 'PESO BRUTO ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(175, 5, ' -  '.$peso_bruto_balanca, 0, 1, 'L');

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 5, 'PESO LÍQUIDO ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(175, 5, ' -  '.number_format($peso_liquido_vol, 4, ',', '.'), 0, 1, 'L');
/**************************Observação p/ Entrega**************************/
//Se o usuário digitou uma Observação p/ Entrega, então eu apresento esta linha ...
    if(!empty($_GET['observacao_entrega'])) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(25, 5, 'OBS. P/ ENTREGA ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(175, 5, ' -  '.strtoupper($_GET['observacao_entrega']), 0, 1, 'L');
    }

/**************************Via**************************/
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(120, 5, '', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);

    if($i == 1) {//Se for a 1ª Via
        $via = 'TRANSPORTADORA';
    }else {//Se for a 2ª Via
        $via = 'EMPRESA';
    }
    $pdf->Cell(60, 5, $i.' ª VIA - '.$via, 1, 1, 'C');
    $pdf->Ln(15);
    $pdf->Cell(200, 5, '', 'T', 1, 'L');
    $pdf->Ln(15);
}

chdir('../../../pdf');
//$file='../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>