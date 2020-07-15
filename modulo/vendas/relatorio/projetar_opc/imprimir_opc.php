<?
require('../../../../lib/pdf/fpdf.php');
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/vendas/apv/apv.php', '../../../../');

error_reporting(0);
define('FPDF_FONTPATH', 'font/');

$tipo_papel		= "P";  // P=> Retrato L=>Paisagem
$unidade		= "mm"; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel          = "A4"; // A3, A4, A5, Letter, Legal
$pdf = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
$pdf->AddPage();
$pdf->Ln(8);

global $pv, $ph; //valor baseado em mm do A4
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
$vetor_meses            = array('', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');
$vetor_meses_espanhol   = array('', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

//Aqui eu faço a busca do Nome do Cliente com o qual estou montando a OPC - Atendimento Planejado de Vendas ...
$sql = "SELECT c.id_pais, IF(c.id_pais = 31, 'R$ ', 'U$ ') AS tipo_moeda, CONCAT(c.razaosocial, ' - ', c.nomefantasia) AS cliente, opcs.tipo_opc, opcs.prazo_a, opcs.prazo_b, opcs.prazo_c, opcs.prazo_d 
        FROM `opcs` 
        INNER JOIN `clientes` c ON c.id_cliente = opcs.id_cliente 
        WHERE opcs.`id_opc` = '$_GET[id_opc]' LIMIT 1 ";
$campos_cliente = bancos::sql($sql);
$tipo_moeda     = $campos_cliente[0]['tipo_moeda'];
$id_pais        = $campos_cliente[0]['id_pais'];
$tipo_opc       = $campos_cliente[0]['tipo_opc'];

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetLeftMargin(10);

if($id_pais == 31) {//No Brasil ...
    $pdf->Cell($GLOBALS['ph']*100, 5, 'São Paulo, '.date('d').' de '.$vetor_meses[intval(date('m'))].' de '.date('Y').'.', 0, 1, 'L');
    $pdf->Ln(8);
    $pdf->Cell($GLOBALS['ph']*100, 5, 'À '.$campos_cliente[0]['cliente'], 0, 1, 'L');
    $pdf->Ln(6);
    
    if($tipo_opc == 'C') {//Produtos Adquiridos ...
        $pdf->Cell($GLOBALS['ph']*100, 5, 'Ref.: Oferta Planejada de Compras (OPC) - N.º '.$_GET['id_opc'], 0, 1, 'L');
    }else {//Produtos Top(s) não Adquiridos (Curva ABC) ...
        $pdf->Cell($GLOBALS['ph']*100, 5, 'Ref.: Oferta Produtos TOP (Curva ABC) - Produtos não adquiridos - N.º '.$_GET['id_opc'], 0, 1, 'L');
    }
    $pdf->Ln(6);
    if($tipo_opc == 'C') {//Produtos Adquiridos ...
        $pdf->Cell($GLOBALS['ph']*100, 5, 'Prezado DISTRIBUIDOR:', 0, 1, 'L');
        $pdf->Ln(8);
        $pdf->MultiCell($GLOBALS['ph']*100, 5, 'Com base numa avaliação de resultados das suas compras efetuadas conosco nos últimos anos, passamos abaixo o resultado desse estudo emitido pelo nosso sistema, para sua avaliação.', 0, 1, 'L');
    }else {//Produtos Top(s) não Adquiridos (Curva ABC) ...
        $pdf->Cell($GLOBALS['ph']*100, 5, 'Prezado CLIENTE:', 0, 1, 'L');
        $pdf->Ln(8);
        $pdf->MultiCell($GLOBALS['ph']*100, 5, 'Com base numa avaliação de resultados das suas compras nos últimos anos, passamos abaixo uma oferta dos Produtos Top(s) não adquiridos para sua avaliação.', 0, 1, 'L');
    }
}else {//Fora do Brasil ...
    $pdf->Cell($GLOBALS['ph']*100, 5, 'São Paulo, '.date('d').' de '.$vetor_meses_espanhol[intval(date('m'))].' de '.date('Y').'.', 0, 1, 'L');
    $pdf->Ln(8);
    $pdf->Cell($GLOBALS['ph']*100, 5, 'Srs '.$campos_cliente[0]['cliente'].';', 0, 1, 'L');
    $pdf->Ln(6);
    $pdf->Cell($GLOBALS['ph']*100, 5, 'Ref.: Oferta Planeada de Compras (OPC)', 0, 1, 'L');
    $pdf->Ln(6);
    $pdf->Cell($GLOBALS['ph']*100, 5, 'Estimado '.$campos_cliente[0]['contato'].':', 0, 1, 'L');
    $pdf->Ln(8);
    $pdf->MultiCell($GLOBALS['ph']*100, 5, 'Con base en una avaliación de resultados de las compras efectuadas a nosotros en los ultimos años, les enviamos a seguir el resultado de este estudio emitido por nuestro sistema, para su estudio.', 0, 1, 'L');
}
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetTextColor(0, 0, 0);

if($id_pais == 31) {//No Brasil ...
    $pdf->Cell($GLOBALS['ph'] * 10, 5, 'Referência', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 55, 5, 'Discriminação', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 11, 5, 'Qtde Proposta', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 12, 5, 'Pço Unit '.$tipo_moeda.' Prop', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 8, 5, 'Total '.$tipo_moeda, 1, 1, 'C');
}else {//Fora do Brasil ...
    $pdf->Cell($GLOBALS['ph'] * 10, 5, 'Referencia', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 55, 5, 'Discriminación', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 11, 5, 'Cant. Propuesta', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 12, 5, 'Precio '.$tipo_moeda.' Prop', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 8, 5, 'Total '.$tipo_moeda, 1, 1, 'C');
}

$pdf->SetFont('Arial', '', 8);

//Aqui eu busco todos os PA(s) da ...
$sql = "SELECT oi.*, pa.referencia, pa.discriminacao 
        FROM `opcs_itens` oi 
        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = oi.id_produto_acabado 
        WHERE oi.`id_opc` = '$_GET[id_opc]' ORDER BY pa.discriminacao ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $pdf->Cell($GLOBALS['ph'] * 10, 5, $campos[$i]['referencia'], 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 55, 5, $campos[$i]['discriminacao'], 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph'] * 11, 5, $campos[$i]['qtde_proposta'], 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 12, 5, number_format($campos[$i]['preco_proposto'], 2, ',', '.'), 1, 0, 'R');
    $pdf->Cell($GLOBALS['ph'] * 8, 5, number_format($campos[$i]['qtde_proposta'] * $campos[$i]['preco_proposto'], 2, ',', '.'), 1, 1, 'R');

    $total_geral_proposto+= $campos[$i]['qtde_proposta'] * $campos[$i]['preco_proposto'];
}
$pdf->SetTextColor(255, 0, 0);
$pdf->Cell($GLOBALS['ph'] * 96, 5, 'TOTAL GERAL '.$tipo_moeda.number_format($total_geral_proposto, 2, ',', '.'), 1, 1, 'R');
$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 10);

if($id_pais == 31) {//No Brasil ...
    $pdf->MultiCell($GLOBALS['ph']*100, 5, 'Condições de Venda:', 0, 1, 'L');
    $pdf->Ln(8);
    
    if($campos_cliente[0]['prazo_d'] > 0) {
        $prazos_pgto = $campos_cliente[0]['prazo_a'].'/'.$campos_cliente[0]['prazo_b'].'/'.$campos_cliente[0]['prazo_c'].'/'.$campos_cliente[0]['prazo_d'];
    }else if($campos_cliente[0]['prazo_c'] > 0) {
        $prazos_pgto = $campos_cliente[0]['prazo_a'].'/'.$campos_cliente[0]['prazo_b'].'/'.$campos_cliente[0]['prazo_c'];
    }else if($campos_cliente[0]['prazo_b'] > 0) {
        $prazos_pgto = $campos_cliente[0]['prazo_a'].'/'.$campos_cliente[0]['prazo_b'];
    }else {
        $prazos_pgto = $campos_cliente[0]['prazo_a'];
    }

    $pdf->Cell($GLOBALS['ph']*100, 5, '* Prazo Pgto: '.$prazos_pgto.' DDL', 0, 1, 'L');
    $pdf->Cell($GLOBALS['ph']*100, 5, '* Preços Líquidos', 0, 1, 'L');
    $pdf->Cell($GLOBALS['ph']*100, 5, '* Manteremos o Preço Firme até o final da entrega.', 0, 1, 'L');
    $pdf->Cell($GLOBALS['ph']*100, 5, '* Embalagem: Quantidades conforme nossa lista de preço / catálogo.', 0, 1, 'L');
    $pdf->Ln(8);
    $pdf->Cell($GLOBALS['ph']*100, 5, '* Obs: ______________________________________________________________________________________________', 0, 1, 'L');
    $pdf->Ln(8);
    $pdf->Cell($GLOBALS['ph']*100, 5, '* Condições de Entrega: _____________________________________________________________________________', 0, 1, 'L');
    $pdf->Ln(8);
   
    if($tipo_opc == 'C') {//Produtos Adquiridos ...
        $pdf->MultiCell($GLOBALS['ph']*100, 5, '* O objetivo desta oferta ( OPC ) é trazer vantagens à você nosso DISTRIBUIDOR, considerando sua fidelidade às nossas marcas , propondo um equilíbrio em suas compras , através de quantidades , preços e condições de pagamento e entrega vantajosas , para que possua preços competitivos e pronta entrega de nossos produtos.', 0, 1, 'L');
    }else {//Produtos Top(s) não Adquiridos (Curva ABC) ...
        $pdf->MultiCell($GLOBALS['ph']*100, 5, '* O objetivo desta oferta ( Produtos Top ) é trazer vantagens à você CLIENTE, propondo um equilíbrio em suas compras, através de quantidades , preços e condições de pagamento e entrega vantajosas , para que possua preços competitivos e pronta entrega de nossos produtos.', 0, 1, 'L');
    }
    $pdf->Ln(8);

    $pdf->Cell($GLOBALS['ph']*100, 5, 'Sem mais, esperando atendê-lo da melhor maneira possível ,', 0, 1, 'L');
    $pdf->Ln(4);
    $pdf->Cell($GLOBALS['ph']*100, 5, 'atenciosamente', 0, 1, 'L');
    $pdf->Ln(4);
}else {//Fora do Brasil ...
    $pdf->MultiCell($GLOBALS['ph']*100, 5, 'Condiciones de Venta:', 0, 1, 'L');
    $pdf->Ln(8);

    $pdf->Cell($GLOBALS['ph']*100, 5, '* Plazo de Pago', 0, 1, 'L');
    $pdf->Cell($GLOBALS['ph']*100, 5, '* Precios Netos', 0, 1, 'L');
    $pdf->Cell($GLOBALS['ph']*100, 5, '* Mantendremos este precio durante 15 días.', 0, 1, 'L');
    $pdf->Cell($GLOBALS['ph']*100, 5, '* Embalaje: Cantidades conforme nuestra lista de precios / catálogo.', 0, 1, 'L');
    $pdf->Ln(8);
    $pdf->Cell($GLOBALS['ph']*100, 5, '* Obs: ______________________________________________________________________________________________', 0, 1, 'L');
    $pdf->Ln(8);
    $pdf->Cell($GLOBALS['ph']*100, 5, '* Condiciones de Entrega: _____________________________________________________________________________', 0, 1, 'L');
    $pdf->Ln(8);

    $pdf->MultiCell($GLOBALS['ph']*100, 5, '* El objetivo de esta oferta ( OPC ) es traerle ventajas, considerando su fidelidad con nuestras marcas, proponiéndole un equilibrio en sus compras, por medio de cantidades , precios, condiciones de pago y ventajosas entregas, para que tenga precios competitivos y la entrega inmediata.', 0, 1, 'L');
    $pdf->Ln(8);

    $pdf->Cell($GLOBALS['ph']*100, 5, 'Sin más por el momento, esperando poder atenderlo de la mejor manera posible, nos despedimos', 0, 1, 'L');
    $pdf->Ln(4);
    $pdf->Cell($GLOBALS['ph']*100, 5, 'atentamente', 0, 1, 'L');
    $pdf->Ln(4);
}
$pdf->Cell($GLOBALS['ph']*100, 5, 'Grupo Albafer', 0, 1, 'L');
$pdf->Ln(4);
/**************************************************************************************************/
$caracteres_invalidos 	= "àáéíóúãõâêîôûçÀÁÉÍÓÚÃÕÂÊÎÔÛÇª°º'§/";
$caracteres_validos 	= 'AAEIOUAOAEIOUCAAEIOUAOAEIOUC      ';

$file='../../../../pdf/'.basename(tempnam(getcwd()), '').'OPC_'.strtr($campos_cliente[0]['cliente'], $caracteres_invalidos, $caracteres_validos).'_'.date("d-m-Y").'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>