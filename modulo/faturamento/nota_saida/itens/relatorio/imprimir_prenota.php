<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
require('../../../../../lib/calculos.php');
require('../../../../../lib/data.php');
require('../../../../../lib/faturamentos.php');
require('../../../../../lib/genericas.php');//Essa biblioteca é requerida dentro da Intermodular ...
require('../../../../../lib/intermodular.php');

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

/////////////////////////////////////// INCIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel		= 'P';  // P=> Retrato L=>Paisagem
$unidade		= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel          = 'A4'; // A3, A4, A5, Letter, Legal
$pdf			= new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->SetLeftMargin(1);
$pdf->SetTopMargin(3);
$pdf->Open();
$pdf->AddPage();
global $pv,$ph; //valor baseado em mm do A4

if(!empty($formato_papel)) {
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

//Busca de alguns dados da NF ...
$sql = "SELECT c.`id_cliente`, c.`cod_cliente`, c.`id_pais`, 
        IF(c.`nomefantasia` = '', c.`razaosocial`, CONCAT(c.`nomefantasia`, ' (', c.`razaosocial`, ')')) AS cliente, 
        c.`cnpj_cpf`, c.`insc_estadual`, CONCAT(c.`endereco`, ', ', c.`num_complemento`) AS endereco, 
        c.`bairro`, c.`cep`, c.`cidade`, c.`ddd_com`, c.`telcom`, c.`ddd_fax`, c.`telfax`, c.`email`, 
        c.`tributar_ipi_rev`, e.`nomefantasia`, nfs.`id_empresa`, nfs.`finalidade`, nfs.`data_emissao`, 
        nfs.`vencimento1`, nfs.`vencimento2`, nfs.`vencimento3`, nfs.`vencimento4`, nfs.`suframa`, nfs.`status`, 
        nfs.`livre_debito`, p.`pais`, t.`nome` AS transportadora, t.`endereco` AS endereco_transportadora, 
        t.`cidade` AS cidade_transportadora, t.`uf` AS uf_transportadora, u.`sigla` AS uf 
        FROM `nfs` 
        INNER JOIN `transportadoras` t ON t.`id_transportadora` = nfs.`id_transportadora` 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
        INNER JOIN `empresas` e ON e.`id_empresa` = nfs.`id_empresa` 
        INNER JOIN `paises` p ON p.`id_pais` = c.`id_pais` 
        LEFT JOIN `ufs` u ON u.`id_uf` = c.`id_uf` 
        WHERE nfs.`id_nf` = '$_GET[id_nf]' LIMIT 1 ";
$campos         = bancos::sql($sql);
$nota_sgd       = ($campos[0]['id_empresa'] <= 2) ? 'N' : 'S';
$rotulo_prenota = ($campos[0]['id_empresa'] <= 2) ? 'NF' : 'SGD';
$status         = $campos[0]['status'];

if($status == 6) $devolucao = ' [DEVOLUÇÃO]'; 

if($campos[0]['livre_debito'] == 'S') $rotulo_livre_debito = ' / "LIVRE DE DÉBITO" ';

//Somente quando a nota é do Tipo NF q existe existe, consequentemente verifico a Finalidade ...
if($campos[0]['finalidade'] == 'C') {
    $finalidade = 'CONSUMO';
}else if($campos[0]['finalidade'] == 'I') {
    $finalidade = 'INDUSTRIALIZAÇÃO';
}else {
    $finalidade = 'REVENDA';
}

$id_pais = $campos[0]['id_pais'];

if($campos[0]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[0]['vencimento4'];
if($campos[0]['vencimento3'] > 0) $prazo_faturamento = '/'.$campos[0]['vencimento3'].$prazo_faturamento;
if($campos[0]['vencimento2'] > 0) {
    $prazo_faturamento= $campos[0]['vencimento1'].'/'.$campos[0]['vencimento2'].$prazo_faturamento;
}else {
    $prazo_faturamento = ($campos[0]['vencimento1'] == 0) ? 'À vista' : $campos[0]['vencimento1'];
}

if(!empty($campos[0]['cnpj_cpf'])) {
    if(strlen($campos[0]['cnpj_cpf']) == 11) {//CPF ...
        $cnpj_cpf = substr($campos[0]['cnpj_cpf'], 0, 3).'.'.substr($campos[0]['cnpj_cpf'], 3, 3).'.'.substr($campos[0]['cnpj_cpf'], 6, 3).'-'.substr($campos[0]['cnpj_cpf'], 9, 2);
    }else {//CNPJ ...
        $cnpj_cpf = substr($campos[0]['cnpj_cpf'], 0, 2).'.'.substr($campos[0]['cnpj_cpf'], 2, 3).'.'.substr($campos[0]['cnpj_cpf'], 5, 3).'/'.substr($campos[0]['cnpj_cpf'], 8, 4).'-'.substr($campos[0]['cnpj_cpf'], 12, 2);
    }
}else {
    $cnpj_cpf = '';
}
    
$data_emissao		= data::datetodata($campos[0]['data_emissao'], '/');
$tributar_ipi_rev	= $campos[0]['tributar_ipi_rev'];

/**********************************************************************************************************/
/*******************************************Cabec da Nota Fiscal*******************************************/
/**********************************************************************************************************/
$pdf->SetFont('Arial', '', 18);
$pdf->Cell($GLOBALS['ph'] * 103.5, 5, 'PRÉ-NOTA N.º '.faturamentos::buscar_numero_nf($_GET['id_nf'], 'S').' ('.$campos[0]['nomefantasia'].' - '.$rotulo_prenota.')'.$rotulo_livre_debito.' - '.$devolucao, 1, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(22, 5, 'CLIENTE: ', 0, 0, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(145, 5, $campos[0]['cod_cliente'].' - '.$campos[0]['cliente'], 0, 1, 'L');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(27, 5, 'ENDEREÇO:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 11);

/******************/
$pdf->Cell(92, 5, $campos[0]['endereco'].' - '.$campos[0]['bairro'].' - '.$campos[0]['cidade'].' - '.$campos[0]['uf'].' - '.$campos[0]['pais'].' - CEP: '.$campos[0]['cep'], 0, 1, 'L');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(26, 5, 'CNPJ / CPF:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(101, 5, $cnpj_cpf, 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(24, 5, 'INSC. EST.:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(51, 5, $campos[0]['insc_estadual'], 0, 1, 'L');

if($campos[0]['telcom'] != '' && $campos[0]['telfax'] == '') {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(21, 5, 'FONE(S): ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(106, 5, '('.$campos[0]['ddd_com'].') '.$campos[0]['telcom'], 0, 0, 'L');
}else {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(21, 5, 'FONE(S): ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(106, 5, '('.$campos[0]['ddd_com'].') '.$campos[0]['telcom'].' / FAX: '.'('.$campos[0]['ddd_fax'].') '.$campos[0]['telfax'], 0, 0, 'L');
}

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(17, 5, 'E-MAIL: ', 0, 0, 'L');
$pdf->SetFont('Arial', 'U', 10);
$pdf->Cell(38, 5, $campos[0]['email'], 0, 1, 'L', '', 'mailto:'.$campos[0]['email'].'?subject=E-Mail Albafer (Pedido)&body=Albafer');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(36, 5, 'DATA EMISSÃO:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(91, 5, $data_emissao, 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 12);
//Significa que o Cliente é do Tipo Internacional
if($id_pais != 31) {
    $pdf->Cell(20, 5, 'PAGO:', 0, 0, 'L');
//Significa que o Cliente é do Tipo Nacional
}else {
    $pdf->Cell(40, 5, 'FORMA DE VENDA:', 0, 0, 'L');
}
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(40, 5, $prazo_faturamento.' DDL - '.$finalidade, 0, 1, 'L');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(191, 5, 'FRETE - FOB (P/ CONTA DO CLIENTE)', 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(44, 5, 'TRANSPORTADORA:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(83, 5, $campos[0]['transportadora'], 0, 1, 'L');

/**************************************************************************************/
/*****************Função que retorna todos os Valores referentes a NF******************/
/**************************************************************************************/
//Essa variável é utilizada lá em baixo ...
$calculo_total_impostos = calculos::calculo_impostos(0, $_GET['id_nf'], 'NF');

/**********************************************************************************************************/
/*************************************************Impostos*************************************************/
/**********************************************************************************************************/
$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($GLOBALS['ph']*103.5, 5, 'CÁLCULO DO IMPOSTO', 1, 1, 'L');

$pdf->SetFont('Arial', '', 8);
$pdf->Cell($GLOBALS['ph']*20.3, 5, 'BASE DE CÁLCULO DO ICMS:', 1, 0, 'L');
$pdf->Cell($GLOBALS['ph']*20.8, 5, 'VALOR DO ICMS:', 1, 0, 'L');
$pdf->Cell($GLOBALS['ph']*20.8, 5, 'BASE CÁLC. ICMS SUBST.:', 1, 0, 'L');
$pdf->Cell($GLOBALS['ph']*20.8, 5, 'VALOR ICMS SUBSTITUIÇÃO:', 1, 0, 'L');
$pdf->Cell($GLOBALS['ph']*20.8, 5, 'VALOR TOTAL DOS PROD.:', 1, 1, 'L');
$pdf->Cell($GLOBALS['ph']*20.3, 5, 'R$ '.number_format($calculo_total_impostos['base_calculo_icms'], 2, ',', '.'), 1, 0, 'L');

//Valor do ICMS ...
if(strpos($calculo_total_impostos['valor_icms'], '.') > 0 || is_numeric($calculo_total_impostos['valor_icms'])) {//Nem sempre esse valor é numérico ...
    $total_icms_itens_rs = 'R$ '.number_format($calculo_total_impostos['valor_icms'], 2, ',', '.');
}else {//Às vezes ele pode ser Isento também ...
    $total_icms_itens_rs = $calculo_total_impostos['valor_icms'];
}

$pdf->Cell($GLOBALS['ph']*20.8, 5, $total_icms_itens_rs, 1, 0, 'L');

$pdf->Cell($GLOBALS['ph']*20.8, 5, 'R$ '.number_format($calculo_total_impostos['base_calculo_icms_st'], 2, ',', '.'), 1, 0, 'L');
$pdf->Cell($GLOBALS['ph']*20.8, 5, 'R$ '.number_format($calculo_total_impostos['valor_icms_st'], 2, ',', '.'), 1, 0, 'L');

$pdf->Cell($GLOBALS['ph']*20.8, 5, 'R$ '.number_format($calculo_total_impostos['valor_total_produtos'], 2, ',', '.'), 1, 1, 'L');

$pdf->Cell($GLOBALS['ph']*20.3, 5, 'VALOR DO FRETE:', 1, 0, 'L');
$pdf->Cell($GLOBALS['ph']*20.8, 5, 'VALOR DO SEGURO:', 1, 0, 'L');
$pdf->Cell($GLOBALS['ph']*20.8, 5, 'OUTRAS DESP. ACESSÓRIAS:', 1, 0, 'L');
$pdf->Cell($GLOBALS['ph']*20.8, 5, 'VALOR TOTAL DO IPI:', 1, 0, 'L');
$pdf->Cell($GLOBALS['ph']*20.8, 5, 'VALOR TOTAL DA NOTA:', 1, 1, 'L');
$pdf->Cell($GLOBALS['ph']*20.3, 5, 'R$ '.number_format($calculo_total_impostos['valor_frete'], 2, ',', '.'), 1, 0, 'L');
$pdf->Cell($GLOBALS['ph']*20.8, 5, 'R$ 0,00', 1, 0, 'L');
$pdf->Cell($GLOBALS['ph']*20.8, 5, 'R$ '.number_format($calculo_total_impostos['outras_despesas_acessorias'], 2, ',', '.'), 1, 0, 'L');
$pdf->Cell($GLOBALS['ph']*20.8, 5, 'R$ '.number_format($calculo_total_impostos['valor_ipi'], 2, ',', '.'), 1, 0, 'L');

//Se o País for Estrangeiro então mostro o Valor da Nota Fiscal em U$ também ...
if($id_pais != 31) $valor_total_nota_us = 'U$ '.number_format($calculo_total_impostos['valor_total_nota_us'], 2, ',', '.').' | ';

//Quando o País é estrangeiro, eu também apresento o Valor em Dólar ...
$pdf->Cell($GLOBALS['ph']*20.8, 5, $valor_total_nota_us.'R$ '.number_format($calculo_total_impostos['valor_total_nota'], 2, ',', '.'), 1, 1, 'L');

/**********************************************************************/
/*************************Observação do Cliente************************/
/**********************************************************************/
/*É o único caso em que eu sempre apresento a Observação do Cliente porque, isso sempre fica internamente, então não importa que um de nossos 
atendentes faça algum comentário que não seja legal referente ao mesmo ...*/
$sql = "SELECT `observacao` 
        FROM `follow_ups` 
        WHERE `id_cliente` = '".$campos[0]['id_cliente']."' 
        AND `origem` = '15' LIMIT 1 ";
$campos_follow_up = bancos::sql($sql);
if(count($campos_follow_up) == 1) {
    $pdf->Ln(2);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell($GLOBALS['ph'] * 103.5, 5, 'Obs. do Cliente: '.$campos_follow_up[0]['observacao'], 0, 1, 'C');
}
/**********************************************************************/

/**********************************************************************************************************/
/*******************************************Itens da Nota Fiscal*******************************************/
/**********************************************************************************************************/
$pdf->Ln(4);
$pdf->SetFont('Arial', '', 8);

$rotulo_qtde = ($status == 6) ? 'Dev' : 'Fat';

$pdf->Cell($GLOBALS['ph']*7, 5, 'N.º Ped', 1, 0, 'C');
$pdf->Cell($GLOBALS['ph']*4, 5, $rotulo_qtde, 1, 0, 'C');
$pdf->Cell($GLOBALS['ph']*4, 5, 'Vale', 1, 0, 'C');
$pdf->Cell($GLOBALS['ph']*4, 5, 'Pac.', 1, 0, 'C');
$pdf->Cell($GLOBALS['ph']*39.5, 5, 'Produto', 1, 0, 'C');
$pdf->Cell($GLOBALS['ph']*10, 5, 'Pço L. Final R$', 1, 0, 'C');
$pdf->Cell($GLOBALS['ph']*10, 5, 'Total Lote R$', 1, 0, 'C');
$pdf->Cell($GLOBALS['ph']*8, 5, 'IPI R$', 1, 0, 'C');
$pdf->Cell($GLOBALS['ph']*10, 5, 'ICMS ST R$', 1, 0, 'C');
$pdf->Cell($GLOBALS['ph']*7, 5, 'ICMS', 1, 1, 'C');

//Seleção dos Itens da Nota Fiscal
$sql = "SELECT nfsi.`id_nfs_item`, nfsi.`qtde`, nfsi.`qtde_devolvida`, nfsi.`vale`, nfsi.`ipi`, 
        nfsi.`valor_unitario`, nfsi.`valor_unitario_exp`, nfsi.`icms`, ovi.`id_produto_acabado_discriminacao`, 
        pa.`observacao` AS observacao_produto, pvi.`id_pedido_venda_item`, pvi.`id_pedido_venda`, 
        pvi.`id_produto_acabado` 
        FROM `nfs_itens` nfsi 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda_item = nfsi.id_pedido_venda_item 
        INNER JOIN `pedidos_vendas` pv ON pv.id_pedido_venda = pvi.id_pedido_venda 
        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_orcamento_venda_item = pvi.id_orcamento_venda_item 
        INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda 
        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado 
        WHERE nfsi.id_nf = '$_GET[id_nf]' ORDER BY pvi.id_pedido_venda, pa.discriminacao ";
$campos_itens           = bancos::sql($sql);
$linhas_itens           = count($campos_itens);

for($i = 0; $i < $linhas_itens; $i++) {//Listando Somente os Itens da Nota Fiscal ...
    //Se for NF de Devolução, usará o campo qtde_devolvida p/ calculo ...
    $qtde_nota          = ($status == 6) ? $campos_itens[$i]['qtde_devolvida'] : $campos_itens[$i]['qtde'];
    $casas_decimais 	= (strstr($qtde_nota, '.00') == '.00') ? 0 : 2;
    $preco_total_lote 	= ($id_pais != 31) ? $campos_itens[$i]['valor_unitario_exp'] * $qtde_nota : $campos_itens[$i]['valor_unitario'] * $qtde_nota;

    $pdf->Cell($GLOBALS['ph']*7, 5, $campos_itens[$i]['id_pedido_venda'], 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*4, 5, number_format($qtde_nota, $casas_decimais, ',', '.'), 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*4, 5, number_format($campos_itens[$i]['vale'], 0, '.', ''), 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*4, 5, number_format($qtde_nota - $campos_itens[$i]['vale'], 0, '.', ''), 1, 0, 'C');
    $pdf->SetFont('Arial', '', 7);

    if($campos_itens[$i]['id_produto_acabado_discriminacao'] > 0) {//Aqui é quando for Gato por Lebre ...
        $pdf->Cell($GLOBALS['ph']*39.5, 5, intermodular::pa_discriminacao($campos_itens[$i]['id_produto_acabado'], 0, '', '', $campos_itens[$i]['id_produto_acabado_discriminacao'], 1), 1, 0, 'L');
    }else {//Produto Normal ...
        $pdf->Cell($GLOBALS['ph']*39.5, 5, intermodular::pa_discriminacao($campos_itens[$i]['id_produto_acabado'], 0, 0, 0, 0, 1), 1, 0, 'L');
    }

    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell($GLOBALS['ph']*10, 5, number_format($campos_itens[$i]['valor_unitario'], 2, ',', '.'), 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*10, 5, number_format($campos_itens[$i]['valor_unitario'] * $qtde_nota, 2, ',', '.'), 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*8, 5, number_format($preco_total_lote * ($campos_itens[$i]['ipi'] / 100), 2, ',', '.'), 1, 0, 'C');	
    if($campos_itens[$i]['iva'] > 0) {//Aqui eu verifico se existe IVA ...
        $calculo_impostos_item = calculos::calculo_impostos($campos_itens[$i]['id_nfs_item'], $_GET['id_nf'], 'NF');
    }
    $pdf->Cell($GLOBALS['ph']*10, 5, number_format(round($calculo_impostos_item['valor_icms_st'], 2), 2, ',', '.'), 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*7, 5, number_format($campos_itens[$i]['icms'], 2, ',', '.').' %', 1, 1, 'C');
}

/**************************************************************************************/
/*************************************Observações**************************************/
/**************************************************************************************/
$pdf->SetFont('Arial', '', 12);
$pdf->Ln(5);

//Aqui eu busco a observação do Follow-UP deste Pedido que possui a marcação Exibir no PDF ...
$sql = "SELECT `observacao` 
        FROM `follow_ups` 
        WHERE `identificacao` = '$_GET[id_nf]' 
        AND `origem` = '5' 
        AND `exibir_no_pdf` = 'S' LIMIT 1 ";
$campos_follow_up   = bancos::sql($sql);

if(count($campos_follow_up) == 1) $pdf->MultiCell($GLOBALS['ph'] * 104, 5, 'OBS. NF: '.$campos_follow_up[0]['observacao'], 1, 1, 'L');

//Aqui eu verifico todos os Pedidos q estão atrelados a essa NF e que possuem observação preenchida ...
$sql = "SELECT DISTINCT(pvi.`id_pedido_venda`) AS id_pedido_venda 
        FROM `nfs_itens` nfsi 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = nfsi.`id_pedido_venda_item` 
        INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
        WHERE nfsi.`id_nf` = '$_GET[id_nf]' ";
$campos_pedidos = bancos::sql($sql);
$linhas_pedidos = count($campos_pedidos);
for($i = 0; $i < $linhas_pedidos; $i++) {
    $sql = "SELECT `observacao` 
            FROM `follow_ups` 
            WHERE `identificacao` = '".$campos_pedidos[$i]['id_pedido_venda']."' 
            AND `origem` = '2' 
            AND `exibir_no_pdf` = 'S' LIMIT 1 ";
    $campos_follow_up = bancos::sql($sql);
    if(count($campos_follow_up) == 1) $pdf->MultiCell($GLOBALS['ph'] * 104, 5, 'OBS. PED ('.$campos_pedidos[$i]['id_pedido_venda'].'): '.$campos_follow_up[0]['observacao'], 1, 1, 'L');
}

$pdf->Ln(5);
$pdf->SetFont('Arial', '', 10);
$peso_nf = faturamentos::calculo_peso_nf($_GET['id_nf']);
$pdf->Cell($GLOBALS['ph']*103.5, 5, 'PESO LÍQUIDO TOTAL: '.number_format($peso_nf['peso_liq_total_nf'], 4, ',', '.').' Kgs', 1, 1, 'L');
$pdf->Cell($GLOBALS['ph']*20.3, 5, 'EMPACOTADO POR: ', 1, 0, 'L');
$pdf->Cell($GLOBALS['ph']*41.6, 5, '', 1, 0, 'L');
$pdf->Cell($GLOBALS['ph']*20.8, 5, 'DATA:', 1, 0, 'L');
$pdf->Cell($GLOBALS['ph']*20.8, 5, 'HORA:', 1, 1, 'L');

chdir('../../../../../pdf');
$file='../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>