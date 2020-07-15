<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
require('../../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../../../');

//PDF normal ...
error_reporting(0);
function rotulo($tipo_moeda) {//Porque chama mais de uma vez por causa da paginacao
    global $pdf, $tipo_moeda;
    $pdf->SetLeftMargin(20);
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell($GLOBALS['ph'] * 6, 5, 'Cantidad', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 8, 5, 'Referencia', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 48.5, 5, 'Artículo', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 8.5, 5, 'Precio '.$tipo_moeda, 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 10, 5, 'Total '.$tipo_moeda, 1, 1, 'C');
}

function Heade($tipo_faturamento) {
    global $pdf;
    
    if($tipo_faturamento == 1) {
        $id_empresa_buscar  = 1;
    }else if($tipo_faturamento == 2) {
        $id_empresa_buscar  = 2;
    }else if($tipo_faturamento == 'Q' || $tipo_faturamento == 'S') {
        $id_empresa_buscar  = genericas::variavel(47);
    }
    //Trago dados de acordo com o Tipo de Faturamento que esta no cadastro do Cliente ...
    $sql = "SELECT * 
            FROM `empresas` 
            WHERE `id_empresa` = '$id_empresa_buscar' LIMIT 1 ";
    $campos_empresa = bancos::sql($sql);
    $razao_social   = $campos_empresa[0]['razaosocial'];
    $cnpj           = $campos_empresa[0]['cnpj'];
    $cnpj           = substr($cnpj, 0, 2).'.'.substr($cnpj, 2, 3).'.'.substr($cnpj, 5, 3).'/'.substr($cnpj, 8, 4).'-'.substr($cnpj, 12, 2);
    $ie             = $campos_empresa[0]['ie'];
    $ie             = substr($ie, 0, 3).'.'.substr($ie, 3, 3).'.'.substr($ie, 6, 3).'.'.substr($ie, 9, 3);
    $endereco       = $campos_empresa[0]['endereco'];
    $numero         = $campos_empresa[0]['numero'];
    $bairro         = $campos_empresa[0]['bairro'];
    $cidade         = $campos_empresa[0]['cidade'];
    $telefone_comercial = $campos_empresa[0]['telefone_comercial'];
    $cep            = $campos_empresa[0]['cep'];
    $ddd_comercial  = $campos_empresa[0]['ddd_comercial'];

    $pdf->Image('../../../../../imagem/logo_transparente.jpg', 7, 5, 34, 36, 'JPG');

    $pdf->SetFont('Arial', 'BI', 12);
    $pdf->Cell(15 * $GLOBALS['ph'], 10, '', 0, 0, 'L');
    $pdf->Cell(175, 10, '* '.$razao_social, 0, 0, 'L');
    $pdf->Ln(4);
    
    $pdf->SetFont('Arial', 'BI', 9);
    $pdf->Cell(15*$GLOBALS['ph'], 10, '', 0, 0, 'L');
    $pdf->Cell(124, 10, $endereco.', N.º '.$numero.' - '.$bairro.' - '.$cidade.' - CEP: '.$cep, 0, 0, 'L');
    $pdf->Ln(4);
    
    $pdf->SetFont('Arial', 'BI', 9);
    $pdf->Cell(15*$GLOBALS['ph'], 10, '', 0, 0, 'L');
    $pdf->Cell(120, 10, 'FONE/FAX: (55-'.$ddd_comercial.') '.$telefone_comercial.' # E-MAIL: mercedes@grupoalbafer.com.br', 0, 0, 'L');
    $pdf->Ln(4);

    $pdf->Cell(15 * $GLOBALS['ph'], 10, '', 0, 0, 'L');
    $pdf->Cell(127, 10, 'CNPJ: '.$cnpj.' # INSC. EST. - '.$ie, 0, 0, 'L');
    
    $pdf->SetFont('Arial', 'I', 13);
    $pdf->Cell(30, 10, 'Página: '.$GLOBALS['num_pagina'], 0, 0, 'L');
    $pdf->Ln(8);
    
    $pdf->SetFont('Arial', 'BI', 9);
    $pdf->Cell(30 * $GLOBALS['ph'], 10, '', 0, 0, 'L');
    $pdf->Cell(30, 10, 'Fecha: '.date('d/m/Y').' - '.date('H:i:s').' hs', 0, 0, 'L');
    $pdf->Ln(4);
    
    $pdf->Line(1 * $GLOBALS['ph'], 43, 101.5 * $GLOBALS['ph'], 43);
    
    $pdf->Ln(12);
}

/////////////////////////////////////// INÍCIO PDF ///////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel     = 'P';  // P=> Retrato L=>Paisagem
$unidade        = 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= 'A4'; // A3, A4, A5, Letter, Legal
$pdf = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
$pdf->AddPage();
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

//Esta parte está fora do head só é utilizada na primeira página
//Busca dos dados do Cliente
$sql = "SELECT ov.*, c.*, cc.nome AS contato 
        FROM `orcamentos_vendas` ov 
        INNER JOIN `clientes_contatos` cc ON cc.id_cliente_contato = ov.id_cliente_contato 
        INNER JOIN `clientes` c ON c.id_cliente = cc.id_cliente 
        WHERE ov.`id_orcamento_venda` = '$_GET[id_orcamento_venda]' LIMIT 1 ";
$campos = bancos::sql($sql);
if($campos[0]['prazo_d'] > 0) $prazo_faturamento = '/'.$campos[0]['prazo_d'];
if($campos[0]['prazo_c'] > 0) $prazo_faturamento = '/'.$campos[0]['prazo_c'].$prazo_faturamento;
if($campos[0]['prazo_b'] > 0) {
    $prazo_faturamento = $campos[0]['prazo_a'].'/'.$campos[0]['prazo_b'].$prazo_faturamento;
}else {
    $prazo_faturamento = ($campos[0]['prazo_a'] == 0) ? 'Al contado' : $campos[0]['prazo_a'].' dias ';
}

//Dados dos Clientes para Cabeçalho
$id_pais                = $campos[0]['id_pais'];
$data_emissao	  	= data::datetodata(substr($campos[0]['data_emissao'], 0, 10), '/');
$bairro                 = $campos[0]['bairro'];
$endereco               = $campos[0]['endereco'];
$num_complemento  	= $campos[0]['num_complemento'];
$email                  = $campos[0]['email'];
$cep                    = $campos[0]['cep'];
$cidade                 = $campos[0]['cidade'];
$tipo_faturamento       = $campos[0]['tipo_faturamento'];

$sql = "SELECT pais 
        FROM `paises` 
        WHERE `id_pais` = ".$campos[0]['id_pais']." LIMIT 1 ";
$campos_pais    = bancos::sql($sql);
$pais           = $campos_pais[0]['pais'];

$ddi_com = $campos[0]['ddi_com'];
$ddd_com = $campos[0]['ddd_com'];
$telcom = $campos[0]['telcom'];
$telefone_com = '('.$ddi_com.'-'.$ddd_com.') '.$telcom;

$ddi_fax = $campos[0]['ddi_fax'];
$ddd_fax = $campos[0]['ddd_fax'];
$telfax = $campos[0]['telfax'];
$telefone_fax = '('.$ddi_fax.'-'.$ddd_fax.') '.$telfax;

//Significa que o Cliente é do Tipo Internacional ...
$tipo_moeda = ($id_pais != 31) ? 'U$ ' : 'R$ ';

Heade($tipo_faturamento);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(37, 5, 'Factura Proforma N.º ', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(72, 5, number_format($_GET['id_orcamento_venda'], 0, ',', '.'), 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 5, 'Fecha de la Cotización: ', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(50, 5, $data_emissao, 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(22, 5, 'Comprador: ', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(147, 5, $campos[0]['razaosocial'], 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(17, 5, 'Dirección:', 0, 0, 'L');
$pdf->SetFont('Arial','',8);

//Caso estiver vázio
$cep = (empty($cep)) ? '' : ' Cod. Postal -'.$cep;
if(empty($bairro))  $bairro = '-';
if(empty($cidade))  $cidade = '-';
if(empty($pais))    $pais = '-';
/******************/
$pdf->Cell(99, 5, $endereco.', '.$num_complemento.' - '.$bairro.' - '.$cidade.' - '.$pais.' - '.$cep, 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(17, 5, 'Fone(s): ', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(92, 5, $telefone_com.' / Fax: '.$telefone_fax, 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(15, 5, 'E-mail: ', 0, 0, 'L');
$pdf->SetFont('Arial', 'U', 10);
$pdf->Cell(40, 5, $email, 0, 1, 'L', '', 'mailto:'.$email.'?subject=E-Mail Albafer (Pedido)&body=Albafer');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(11, 5, 'Pago: ', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(138, 5, $prazo_faturamento, 0, 1, 'L');

//Seleção dos Itens do Orçamento ...
$sql = "SELECT ovi.`id_produto_acabado_discriminacao`, ovi.`qtde`, ovi.`preco_liq_final`, 
        pa.`referencia`, pa.`discriminacao` 
        FROM `orcamentos_vendas_itens` ovi 
        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
        INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
        WHERE ovi.`id_orcamento_venda` = '$_GET[id_orcamento_venda]' ORDER BY ovi.id_orcamento_venda_item, pa.discriminacao ";
$campos_itens = bancos::sql($sql);
$linhas = count($campos_itens);
for($i = 0; $i < $linhas; $i++) {
    if($GLOBALS['nova_pagina'] == 'sim') {
        $GLOBALS['nova_pagina'] = 'nao';
        if($i != 0) {
            $pdf->Ln(-5);
            Heade($tipo_faturamento);
        }
        rotulo($tipo_moeda);
    }
//Qtde
    $pdf->Cell($GLOBALS['ph'] * 6, 5, number_format($campos_itens[$i]['qtde'], 0, ',', '.'), 1, 0, 'C');
    
    if($campos_itens[$i]['id_produto_acabado_discriminacao'] > 0) {//Nessa situação existe gato por Lebre ...
        //Busco dados da Lebre ... kkk
        $sql = "SELECT `referencia`, `discriminacao` 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '".$campos_itens[$i]['id_produto_acabado_discriminacao']."' LIMIT 1 ";
        $campos_pa_substituto   = bancos::sql($sql);
        $referencia             = $campos_pa_substituto[0]['referencia'];
        $discriminacao          = $campos_pa_substituto[0]['discriminacao'];
    }else {
        $referencia             = $campos_itens[$i]['referencia'];
        $discriminacao          = $campos_itens[$i]['discriminacao'];
    }
//Referência ...
    $pdf->Cell($GLOBALS['ph'] * 8, 5, $referencia, 1, 0, 'L');
//Discriminação ...
    $pdf->Cell($GLOBALS['ph'] * 48.5, 5, $discriminacao, 1, 0, 'L');
    $pdf->SetFont('Arial', '', 7);
//Preço Unit. R$
    $pdf->Cell($GLOBALS['ph'] * 8.5, 5, number_format($campos_itens[$i]['preco_liq_final'], 2, ',', '.'), 1, 0, 'R');
//Total R$
    $pdf->Cell($GLOBALS['ph'] * 10, 5, number_format($campos_itens[$i]['preco_liq_final'] * $campos_itens[$i]['qtde'], 2, ',', '.'), 1, 1, 'R');
    $valor_produto_todos_itens+= ($campos_itens[$i]['preco_liq_final'] * $campos_itens[$i]['qtde']);
}

$pdf->SetFont('Arial', 'B', 7);

//Busco os Itens de Orçamento p/ calcular o Peso Neto "Peso Líquido" ...
$sql = "SELECT SUM(ovi.qtde * pa.peso_unitario) AS total_peso_liquido 
        FROM `orcamentos_vendas_itens` ovi 
        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado 
        WHERE ovi.`id_orcamento_venda` = '$_GET[id_orcamento_venda]' ";
$campos_peso_neto = bancos::sql($sql);

$pdf->Cell($GLOBALS['ph'] * 62.5, 5, 'PESO NETO '.number_format($campos_peso_neto[0]['total_peso_liquido'], 3, ',', '.').' kg', 1, 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 8.5, 5, 'TOTAL FOB', 1, 0, 'L');
$pdf->SetFont('Arial', 'B', 8.7);
$pdf->SetFillColor(200, 200, 200);//Cor Cinza
$pdf->Cell($GLOBALS['ph']*10, 5, $tipo_moeda.number_format($valor_produto_todos_itens, 2, ',', '.'), 'TBR', 1, 'R', 1);
$pdf->Ln(4);

/***************************************************************************/
chdir('../../../../../pdf');
//$file='../../../../../pdf/'.basename(tempnam(getcwd()), '').'Pedido de Venda Grupo Albafer_'.$_GET[id_pedido_venda].'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>