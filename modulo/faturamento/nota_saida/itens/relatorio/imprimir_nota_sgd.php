<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
require('../../../../../lib/faturamentos.php');
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

function rotulo() { // porq chama mais de uma vez por causa da paginacao
    global $pdf;
    $pdf->SetLeftMargin(5);
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell($GLOBALS['ph']*9, 5, 'QTD', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*64, 5, 'PRODUTO', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*12, 5, 'PREÇO UNIT R$', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*12, 5, 'PREÇO TOTAL R$', 1, 1, 'C');
}

/////////////////////////////////////// INCIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel		= 'P';  // P=> Retrato L=>Paisagem
$unidade		= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel		= 'A4'; // A3, A4, A5, Letter, Legal, Ofício ...
$pdf                    = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->SetLeftMargin(1);
$pdf->Open();
$pdf->AddPage();
global $pv, $ph; //valor baseado em mm do A4

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
$sql = "SELECT c.id_pais, SUBSTRING_INDEX(LOWER(c.razaosocial), ' ', 2) AS cliente_conferencia, c.razaosocial, c.credito, c.ddi_com, c.ddd_com, c.telcom, c.ddi_fax, c.ddd_fax, c.telfax, nfs.* 
        FROM `nfs` 
        INNER JOIN clientes c ON c.id_cliente = nfs.id_cliente 
        WHERE nfs.id_nf = '$_GET[id_nf]' LIMIT 1 ";
$campos = bancos::sql($sql);
//Dados dos Clientes para Cabeçalho
$id_pais                = $campos[0]['id_pais'];
$cliente_conferencia    = ucfirst($campos[0]['cliente_conferencia']);
$razao_social           = $campos[0]['razaosocial'];
$tipo_nfe_nfs           = $campos[0]['tipo_nfe_nfs'];
$despesas_acessorias    = $campos[0]['despesas_acessorias'];
$valor_frete            = $campos[0]['valor_frete'];
$data_emissao           = data::datetodata($campos[0]['data_emissao'], '/');
$credito                = $campos[0]['credito'];

$ddi_com                = $campos[0]['ddi_com'];
$ddd_com                = $campos[0]['ddd_com'];
$telcom                 = $campos[0]['telcom'];
$telefone_com           = '('.$ddi_com.'-'.$ddd_com.') '.$telcom;

$ddi_fax                = $campos[0]['ddi_fax'];
$ddd_fax                = $campos[0]['ddd_fax'];
$telfax                 = $campos[0]['telfax'];
$telefone_fax           = '('.$ddi_fax.'-'.$ddd_fax.') '.$telfax;

$vencimento1            = $campos[0]['vencimento1'];
$data_vencimento1       = data::adicionar_data_hora($data_emissao, $campos[0]['vencimento1']);
$valor1                 = number_format($campos[0]['valor1'], 2, ',', '.');

$vencimento2            = $campos[0]['vencimento2'];
$data_vencimento2       = data::adicionar_data_hora($data_emissao, $campos[0]['vencimento2']);
$valor2                 = number_format($campos[0]['valor2'], 2, ',', '.');

$vencimento3            = $campos[0]['vencimento3'];
$data_vencimento3       = data::adicionar_data_hora($data_emissao, $campos[0]['vencimento3']);
$valor3                 = number_format($campos[0]['valor3'], 2, ',', '.');

$vencimento4            = $campos[0]['vencimento4'];
$data_vencimento4       = data::adicionar_data_hora($data_emissao, $campos[0]['vencimento4']);
$valor4                 = number_format($campos[0]['valor4'], 2, ',', '.');

//Tratamento com o Campo Data de Saída / Entrada
if($campos[0]['data_saida_entrada'] != '0000-00-00') {
    $data_saida_entrada = data::datetodata($campos[0]['data_saida_entrada'], '/');
}else {
    $data_saida_entrada = '';
}

$observacao 		= $campos[0]['observacao'];
$tipo_moeda 		= ($id_pais != 31) ? 'U$ ' : 'R$ ';

//Aqui eu busco todos os Pedidos que foram atrelados a essa Nota ...
$sql = "SELECT DISTINCT(pvi.`id_pedido_venda`) 
        FROM `nfs_itens` nfsi 
        INNER JOIN `pedidos_vendas_itens` pvi ON nfsi.`id_pedido_venda_item` = pvi.`id_pedido_venda_item` 
        WHERE nfsi.`id_nf` = '$_GET[id_nf]' ";
$campos_pedidos = bancos::sql($sql);
$linhas_pedidos = count($campos_pedidos);
//Lista dos Pedidos ...
for($i = 0; $i < $linhas_pedidos; $i++) $id_pedido_vendas.= $campos_pedidos[$i]['id_pedido_venda'].', ';
$id_pedido_vendas = substr($id_pedido_vendas, 0, strlen($id_pedido_vendas) - 2);

//Aqui eu busco todos os N.º de Pedidos que foram atrelados a essa Nota ...
$sql = "SELECT DISTINCT(pv.`num_seu_pedido`) 
        FROM `nfs_itens` nfsi 
        INNER JOIN `pedidos_vendas_itens` pvi ON nfsi.`id_pedido_venda_item` = pvi.`id_pedido_venda_item` 
        INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
        WHERE nfsi.`id_nf` = '$_GET[id_nf]' ";
$campos_pedidos = bancos::sql($sql);
$linhas_pedidos = count($campos_pedidos);
//Lista dos N.º de Pedidos ...
for($i = 0; $i < $linhas_pedidos; $i++) $num_seu_pedidos.= $campos_pedidos[$i]['num_seu_pedido'].', ';
$num_seu_pedidos = substr($num_seu_pedidos, 0, strlen($num_seu_pedidos) - 2);

//Aqui eu busco todos os Representantes referentes a esses Itens ...
$sql = "SELECT DISTINCT(r.nome_fantasia) 
        FROM `nfs_itens` nfsi 
        INNER JOIN `pedidos_vendas_itens` pvi ON nfsi.`id_pedido_venda_item` = pvi.`id_pedido_venda_item` 
        INNER JOIN `representantes` r ON r.`id_representante` = pvi.`id_representante` 
        WHERE nfsi.`id_nf` = '$_GET[id_nf]' ";
$campos_representante = bancos::sql($sql);
$linhas_representante = count($campos_representante);
//Lista dos Representantes ...
for($i = 0; $i < $linhas_representante; $i++) $representantes.= $campos_representante[$i]['nome_fantasia'].', ';
$representantes = substr($representantes, 0, strlen($representantes) - 2);

if($tipo_nfe_nfs == 'S') {//Se for uma NF de Saída de Vendas, gera 3 vias ...
    $vias = array('FINANCEIRO', 'CLIENTE', 'CONTROLE INTERNO');
}else {//Se for uma NF de Entrada, de Devolução, gera apenas 1 única via ...
    $vias = array('FINANCEIRO');
    $complemento_numero_nf = ' (DEVOLUÇÃO)';
}

for($v = 0; $v < count($vias); $v++) {//Qtde de Vias a Serem Impressas ...
//Aqui com esse comando eu mando fazer a impressão no começo da Folha ...
    $pdf->SetY(6);
//Impressão da Via ...
    $pdf->SetLeftMargin(4);
    $pdf->SetFont('Arial', '', 6);
    $pdf->Cell(165, 3, 'RECEBEMOS OS PRODUTOS/SERVIÇOS ABAIXO RELACIONADOS', 'TRL', 0, 'L');
    $pdf->Cell(37, 3, 'DATA DE RECEBIMENTO', 'TRL', 1, 'L');
    $pdf->Cell(165, 7, ' ', 'BRL', 0, 'L');
    $pdf->Cell(37, 7, ' ', 'BRL', 1, 'L');
    $pdf->Cell(165, 3, 'IDENTIFICAÇÃO E ASSINATURA DO RECEBEDOR', 'TRL', 0, 'L');
    $pdf->Cell(37, 3, 'CONFERÊNCIA N.º ', 'TRL', 1, 'L');
    $pdf->Cell(165, 7, '', 'BRL', 0, 'L');
    $pdf->Cell(37, 7, faturamentos::buscar_numero_nf($id_nf, 'S').' ('.$cliente_conferencia.')', 'BRL', 1, 'C');
    $pdf->Ln(0.3);
	
    for($i = 0; $i < 162; $i++) $tracos.= '- ';
    $pdf->Cell(205, 7, $tracos, 0, 1, 'C');
    $pdf->Ln(0.3);
//Mini Cabeçalho ...
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(147, 5, '', 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(12, 5, 'VIA - ', 0, 0, 'L');
    $pdf->SetFont('Arial', 'BU', 14);
    $pdf->Cell(43, 5, $vias[$v], 0, 1, 'L');

//Crédito ...
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 5, 'CRÉDITO - ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(20, 5, $credito, 0, 0, 'L');

    if($telefone_com != '(-) ' && $telefone_fax == '(-) ') {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(17, 5, 'FONE(S) - ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(90, 5, $telefone_com, 0, 0, 'L');
    }else {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(17, 5, 'FONE(S) - ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(27, 5, $telefone_com, 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(13, 5, ' / FAX - ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(50, 5, $telefone_fax, 0, 0, 'L');
    }

//Conferência N.º ...
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(35, 5, 'CONFERÊNCIA N.º - ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(30, 5, faturamentos::buscar_numero_nf($id_nf, 'S').$complemento_numero_nf, 0, 1, 'L');

//Cliente ...
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 5, 'CLIENTE - ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(127, 5, $razao_social, 0, 0, 'L');

//Data de Saída ...
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(33, 5, 'DATA DE SAÍDA - ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(27, 5, '', 'B', 1, 'L');

//Representante ...
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(34, 5, 'REPRESENTANTE - ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(113, 5, $representantes, 0, 0, 'L');

//Qtde de Caixas ...
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(35, 5, 'QTDE DE CAIXAS - ', 0, 0, 'L');
    $pdf->SetFont('Arial', 'U', 10);
    $pdf->Cell(25, 5, '', 'B', 1, 'L');

//N/Nº Pedido(s) ...
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(30, 5, 'N/Nº PEDIDO(S) - ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(117, 5, $id_pedido_vendas, 0, 0, 'L');

//Peso Bruto ...
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(28, 5, 'PESO BRUTO - ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(32, 5, 'KGS', 'B', 1, 'R');

//S/Nº Pedido(s) ...
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(30, 5, 'S/Nº PEDIDO(S) - ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(117, 5, $num_seu_pedidos, 0, 0, 'L');

//Data de Emissão ...
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(36, 5, 'DATA DE EMISSÃO - ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(30, 5, $data_emissao, 0, 1, 'L');
    $pdf->SetLeftMargin(5);
/**************************************************Prazos**************************************************/
//Prazo A ...
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(27, 5, 'PRAZO A DLL - ', 'BTL', 0, 'L');
    $pdf->SetFont('Arial', '', 10);

    if($vencimento1 == 0) {
        $pdf->Cell(45, 5, 'À vista', 'BTR', 0, 'L');
    }else {
        $pdf->Cell(45, 5, $vencimento1, 'BTR', 0, 'L');
    }

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(31, 5, 'VENCIMENTO A - ', 'BTL', 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(44, 5, $data_vencimento1, 'BTR', 0, 'L');

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 5, 'VALOR A - ', 'BTL', 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(32, 5, $tipo_moeda.$valor1, 'BTR', 1, 'L');

//Prazo B ...
    if($vencimento2 != 0) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(27, 5, 'PRAZO B DLL - ', 'BTL', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(45, 5, $vencimento2, 'BTR', 0, 'L');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(31, 5, 'VENCIMENTO B - ', 'BTL', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(44, 5, $data_vencimento2, 'BTR', 0, 'L');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 5, 'VALOR B - ', 'BTL', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(32, 5, $tipo_moeda.$valor2, 'BTR', 1, 'L');
    }

//Prazo C ...
    if($vencimento3 != 0) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(27, 5, 'PRAZO C DLL - ', 'BTL', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(45, 5, $vencimento3, 'BTR', 0, 'L');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(31, 5, 'VENCIMENTO C - ', 'BTL', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(44, 5, $data_vencimento3, 'BTR', 0, 'L');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 5, 'VALOR C - ', 'BTL', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(32, 5, $tipo_moeda.$valor3, 'BTR', 1, 'L');
    }

//Prazo D ...
    if($vencimento4 != 0) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(27, 5, 'PRAZO D DLL - ', 'BTL', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(45, 5, $vencimento4, 'BTR', 0, 'L');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(31, 5, 'VENCIMENTO D - ', 'BTL', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(44, 5, $data_vencimento4, 'BTR', 0, 'L');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 5, 'VALOR D - ', 'BTL', 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(32, 5, $tipo_moeda.$valor4, 'BTR', 1, 'L');
    }
/**********************************************************************************************************/
//Seleção dos Itens da Nota Fiscal
    $sql = "SELECT ged.id_empresa_divisao, IF(nfs.`status` = '6', nfsi.qtde_devolvida, nfsi.qtde) AS qtde_nota, nfsi.valor_unitario, nfsi.valor_unitario_exp, ovi.id_produto_acabado, ovi.id_produto_acabado_discriminacao, pa.referencia, pa.discriminacao 
            FROM `nfs_itens` nfsi 
            INNER JOIN `nfs` ON nfs.id_nf = nfsi.id_nf 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda_item = nfsi.id_pedido_venda_item 
            INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_orcamento_venda_item = pvi.id_orcamento_venda_item 
            INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pvi.id_produto_acabado 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
            WHERE nfsi.`id_nf` = '$_GET[id_nf]' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
//Aqui eu zero o Valor da Nota p/ não continuar mantêndo o Valor do Loop anterior ...
    $total_rs   = 0;
    $alba       = 0;
    $warrior    = 0;
    $tool       = 0;
    $nvo        = 0;
    
    for($i = 0; $i < $linhas; $i++) {
        if($GLOBALS['nova_pagina'] == 'sim') {
            $GLOBALS['nova_pagina'] = 'nao';
            rotulo();
        }
        $pdf->SetLeftMargin(5);
        $pdf->SetFont('Arial', '', 8);
//Qtde
        $pdf->Cell($GLOBALS['ph']*9, 5, number_format($campos[$i]['qtde_nota'], 0, ',', '.'), 1, 0, 'R');
//Produto
        $pdf->Cell($GLOBALS['ph']*64, 5, intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0, '', '', $campos[$i]['id_produto_acabado_discriminacao'], 1), 1, 0, 'L');
//Preço Unit. R$
        $pdf->Cell($GLOBALS['ph']*12, 5, number_format($campos[$i]['valor_unitario'], 2, ',', '.'), 1, 0, 'R');
//Total R$
        $pdf->Cell($GLOBALS['ph']*12, 5, number_format($campos[$i]['valor_unitario'] * $campos[$i]['qtde_nota'], 2, ',', '.'), 1, 1, 'R');
        $total_rs+= ($campos[$i]['valor_unitario'] * $campos[$i]['qtde_nota']);
//Aqui eu tenho o somatório por Divisão ...
        switch($campos[$i]['id_empresa_divisao']) {
            case 1://Alba - Cabri
            case 2://Heinz
                $alba+= $campos[$i]['valor_unitario'] * $campos[$i]['qtde_nota'];
            break;
            case 3://Warrior
                $warrior+= $campos[$i]['valor_unitario'] * $campos[$i]['qtde_nota'];
            break;
            case 4://Tool Master
                $tool+= $campos[$i]['valor_unitario'] * $campos[$i]['qtde_nota'];
            break;
            case 5://NVO
                $nvo+= $campos[$i]['valor_unitario'] * $campos[$i]['qtde_nota'];
            break;
        }
    }
    $pdf->Ln(3);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell($GLOBALS['ph']*85, 5, 'Total do(s) Item(ns): ', 0, 0, 'R');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell($GLOBALS['ph']*12, 5, 'R$ '.number_format($total_rs, 2, ',', '.'), 0, 1, 'R');
/*Caso exista o Valor de Despesas Acessórias, então eu apresento este p/ o usuário e também 
acrescento no valor da NF ...*/
    if($despesas_acessorias != '0.00') {
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell($GLOBALS['ph']*85, 5, 'Despesas Acessórias: ', 0, 0, 'R');//No Lotus o Rótulo é Sedex ...
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell($GLOBALS['ph']*12, 5, $tipo_moeda.number_format($despesas_acessorias, 2, ',', '.'), 0, 1, 'R');
//Acrescento junto do Valor da NF o valor de Despesas Acessórias também ...
        $total_rs+= $despesas_acessorias;
    }
/*Caso exista o Valor do Frete na NF, então eu apresento este p/ o usuário e também acrescento 
no valor da NF ...*/
    if($valor_frete != '0.00') {
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell($GLOBALS['ph']*85, 5, 'Sedex: ', 0, 0, 'R');//No Lotus o Rótulo é Sedex ...
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell($GLOBALS['ph']*12, 5, $tipo_moeda.number_format($valor_frete, 2, ',', '.'), 0, 1, 'R');
//Acrescento junto do Valor da NF o valor do Sedex também ...
        $total_rs+= $valor_frete;
    }
    $pdf->SetLeftMargin(1);
//Aqui com esse comando eu mando fazer a impressão no fim da Folha ...
    $pdf->SetY(275);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($GLOBALS['ph']*85, 5, 'TOTAL CONFERÊNCIA: ', 0, 0, 'R');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell($GLOBALS['ph']*12, 5, 'R$ '.number_format($total_rs, 2, ',', '.'), 0, 1, 'R');
    $pdf->Ln(3);
//Linha de Separação
    $pdf->Line(2, 282, 212, 282);
//Observação ...
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell($GLOBALS['ph']*4, 5, 'OBS - ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell($GLOBALS['ph']*13, 5, strtoupper($observacao), 0, 0, 'L');
    $pdf->Ln(4);
//Somatórios ...
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell($GLOBALS['ph']*7, 5, 'ALBA - ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell($GLOBALS['ph']*13, 5, 'R$ '.number_format($alba, 2, ',', '.'), 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell($GLOBALS['ph']*10, 5, 'WARRIOR - ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell($GLOBALS['ph']*13, 5, 'R$ '.number_format($warrior, 2, ',', '.'), 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell($GLOBALS['ph']*8, 5, 'TOOL - ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell($GLOBALS['ph']*13, 5, 'R$ '.number_format($tool, 2, ',', '.'), 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell($GLOBALS['ph']*8, 5, 'NVO - ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell($GLOBALS['ph']*13, 5, 'R$ '.number_format($nvo, 2, ',', '.'), 0, 0, 'L');
    $pdf->Ln(6);
/******************************************************************************************/
/***********************************Nota Fiscal de Saída***********************************/
/******************************************************************************************/
    if($tipo_nfe_nfs == 'S') {//Só gero 3 vias ...
        $quebrar_linha = ($v == 2) ? 0 : 1;
/******************************************************************************************/
/**********************************Nota Fiscal de Entrada**********************************/
/******************************************************************************************/
    }else {//Se for uma NF de Entrada, de Devolução, gera apenas 1 única via ...
//Eu faço um desvio para não quebrar linha e acabar gerando uma segunda folha
        $quebrar_linha = 0;
    }
/******************************************************************************************/
//Frase ...
    $pdf->Cell($GLOBALS['ph']*85, 5, '**************** A LIBERAÇÃO DE SEU CRÉDITO EM NOSSO SISTEMA, ESTÁ CONDICIONADO AO PAGAMENTO DESTES BOLETOS ****************     -     Pág. '.($v + 1), 0, $quebrar_linha, 'L');
}

chdir('../../../../../pdf');
$file='../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>