<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
require('../../../../../lib/calculos.php');
require('../../../../../lib/custos.php');
require('../../../../../lib/data.php');
require('../../../../../lib/estoque_acabado.php');
require('../../../../../lib/intermodular.php');
require('../../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../../../');
error_reporting(0);

//Como esse processamento pode ser muito pesado, deixo o servidor operar excepcionalmente em até 5 minutos para essa tela ...
set_time_limit(300);

function rotulo($tipo_moeda) { // porq chama mais de uma vez por causa da paginacao
    global $pdf, $tipo_moeda;
    $pdf->SetLeftMargin(2);
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 7.5);
    $pdf->Cell($GLOBALS['ph'] * 4, 5, 'QTDE', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 38, 5, 'REF. * DISCRIM.', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 5, 5, 'NCM', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 9, 5, 'Pço Unit. '.$tipo_moeda, 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 10, 5, 'Total '.$tipo_moeda, 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 3.5, 5, 'IPI %', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 3.5, 5, 'ICMS%', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 4.5, 5, 'IVA / %', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 4.5, 5, 'P.Un.(Kg)', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 5.5, 5, 'P.Total(Kg)', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 6.5, 5, 'Represent.', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 7, 5, 'Pz.Ent.', 1, 1, 'C');
}

function Heade($id_pais, $tipo_faturamento) {
    global $pdf, $id_orcamento_venda;
    
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
    $campos_fone        = bancos::sql($sql);
    $razao_social       = $campos_fone[0]['razaosocial'];
    $cnpj               = $campos_fone[0]['cnpj'];
    $cnpj               = substr($cnpj, 0, 2).'.'.substr($cnpj, 2, 3).'.'.substr($cnpj, 5, 3).'/'.substr($cnpj, 8, 4).'-'.substr($cnpj, 12, 2);
    $ie                 = $campos_fone[0]['ie'];
    $ie                 = substr($ie, 0, 3).'.'.substr($ie, 3, 3).'.'.substr($ie, 6, 3).'.'.substr($ie, 9, 3);
    $endereco           = $campos_fone[0]['endereco'];
    $numero             = $campos_fone[0]['numero'];
    $bairro             = strtoupper($campos_fone[0]['bairro']);
    $cidade             = strtoupper($campos_fone[0]['cidade']);
    $telefone_comercial = $campos_fone[0]['telefone_comercial'];
    $cep                = $campos_fone[0]['cep'];
    $ddd_comercial      = $campos_fone[0]['ddd_comercial'];

    $pdf->Image('../../../../../imagem/logo_transparente.jpg', 7, 5, 34, 36, 'JPG');

    $pdf->Cell(15*$GLOBALS['ph'], 10, '', 0, 0, 'L');
    $pdf->SetFont('Arial', 'BI', 13);
    $pdf->Cell(175, 10, '* '.$razao_social, 0, 0, 'L');
    //Significa que o Cliente é do Tipo Internacional
    if($id_pais != 31) {
        $pdf->Cell(50, 10, 'Factura Proforma N.º: '.$id_orcamento_venda, 0, 0, 'L');
            //Significa que o Cliente é do Tipo Nacional
    }else {
        $pdf->Cell(50, 10, 'Orçamento N.º: '.$id_orcamento_venda, 0, 0, 'L');
    }
    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'BI', 9);
    $pdf->Cell(15 * $GLOBALS['ph'], 10, '', 0, 0, 'L');
    $pdf->Cell(130, 10, $endereco.', N.º '.$numero.' - '.$bairro.' - '.$cidade.' - CEP: '.$cep, 0, 0, 'L');

    $pdf->Ln(4);

    $pdf->Cell(15*$GLOBALS['ph'], 10, '', 0, 0, 'L');
    
    if($id_pais != 31) {//Significa que o Cliente é do Tipo Internacional
        $email = 'mercedes@grupoalbafer.com.br';
    }else {//Significa que o Cliente é do Tipo Nacional
        $email = 'vendas@grupoalbafer.com.br';
    }

    $pdf->Cell(120, 10, 'FONE/FAX: (55-'.$ddd_comercial.') '.$telefone_comercial.' # E-MAIL: '.$email, 0, 0, 'L');
    $pdf->Ln(4);

    $pdf->Cell(15*$GLOBALS['ph'], 10, '', 0, 0, 'L');
    $pdf->Cell(185, 10, 'CNPJ: '.$cnpj.' # INSC. EST. - '.$ie, 0, 0, 'L');
    $pdf->SetFont('Arial', 'BI', 13);
    $pdf->Cell(30, 10, 'Página: '.$GLOBALS['num_pagina'], 0, 0, 'L');
    $pdf->Ln(8);

    $pdf->SetFont('Arial', 'BI', 9);
    $pdf->Cell(15*$GLOBALS['ph'], 10, '', 0, 0, 'L');
    $pdf->Cell(30, 10, 'Impressão: '.date('d/m/Y').' - '.date('H:i:s'), 0, 0, 'L');
    $pdf->Ln(4);

//Aqui verifica quem é o funcionário responsável pelo Orçamento, Data e Hora
    $sql = "SELECT `id_funcionario`, `id_login`, `data_sys` 
            FROM `orcamentos_vendas` 
            WHERE `id_orcamento_venda` = '$_GET[id_orcamento_venda]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $data   = data::datetodata(substr($campos[0]['data_sys'], 0, 10), '/');
    $hora   = substr($campos[0]['data_sys'], 11, 8);

    if($campos[0]['id_funcionario'] > 0) {//99% dos casos, serão os funcionários da Albafer que irão acessar nosso sistema ...
        $sql = "SELECT l.`login` 
                FROM `funcionarios` f 
                INNER JOIN `logins` l ON l.id_funcionario = f.`id_funcionario` 
                WHERE f.`id_funcionario` = '".$campos[0]['id_funcionario']."' LIMIT 1 ";
    }else {//No demais representantes ...
        $sql = "SELECT `login` 
                FROM `logins` 
                WHERE `id_login` = '".$campos[0]['id_login']."' LIMIT 1 ";
    }
    $campos_login = bancos::sql($sql);
    
    $pdf->Cell(15 * $GLOBALS['ph'], 10, '', 0, 0, 'L');
    $pdf->Cell(30, 10, 'Última alteração: '.ucfirst($campos_login[0]['login']).' - '.$data.' - '.$hora, 0, 1, 'L');

    //$pdf->Image('../../../../../imagem/marcas/cabri.jpg', 60, 39, 10, 10, 'JPG');
    //$pdf->Image('../../../../../imagem/marcas/heinz.jpg', 80, 39, 20, 10, 'JPG');
    //$pdf->Image('../../../../../imagem/marcas/warrior.jpg', 108, 39, 30, 10, 'JPG');
    $pdf->Line(1 * $GLOBALS['ph'],43, 100 * $GLOBALS['ph'], 43);
    $pdf->Ln(2);
}
/////////////////////////////////////// INICIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');

$tipo_papel		= 'L';  // P=> Retrato L=>Paisagem
$unidade		= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel          = 'A4'; // A3, A4, A5, Letter, Legal
$pdf                    = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
$pdf->SetLeftMargin(4);
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

//Esta parte está fora do head só é utilizada na primeira página
//Busca dos dados do Cliente
$sql = "SELECT c.`id_pais`, c.`id_uf`, 
        IF(c.`nomefantasia` = '', c.`razaosocial`, CONCAT(c.`nomefantasia`, '(', c.`razaosocial`, ')')) AS cliente, 
        c.`insc_estadual`, c.`cnpj_cpf`, c.`endereco`, c.`num_complemento`, c.`bairro`, c.`email`, c.`cep`, c.`cidade`, 
        c.`tipo_faturamento`, c.`tipo_suframa`, c.`suframa_ativo`, c.`ddi_com`, c.`ddd_com`, c.`telcom`, c.`ddi_fax`, c.`ddd_fax`, c.`telfax`, 
        ov.*, ov.`artigo_isencao` AS artigo_isencao_orcamento, t.`nome` 
        FROM `orcamentos_vendas` ov 
        LEFT JOIN `transportadoras` t ON t.`id_transportadora` = ov.`id_transportadora` AND t.`ativo` = '1' 
        INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` AND c.`ativo` = '1' 
        WHERE ov.`id_orcamento_venda` = '$_GET[id_orcamento_venda]' LIMIT 1 ";
$campos                 = bancos::sql($sql);
$tipo_frete             = $campos[0]['tipo_frete'];

if($campos[0]['prazo_d'] > 0) $prazo_faturamento = '/'.$campos[0]['prazo_d'];
if($campos[0]['prazo_c'] > 0) $prazo_faturamento = '/'.$campos[0]['prazo_c'].$prazo_faturamento;
if($campos[0]['prazo_b'] > 0) {
    $prazo_faturamento = $campos[0]['prazo_a'].'/'.$campos[0]['prazo_b'].$prazo_faturamento;
}else {
    $prazo_faturamento = ($campos[0]['prazo_a'] == 0) ? 'À vista' : $campos[0]['prazo_a'];
}

if($campos[0]['nota_sgd'] == 'S') {
    $rotulo_sgd = ' - SGD';
}else {
    $rotulo_sgd = ' - NF';
//Somente quando a nota é do Tipo NF q existe existe, consequentemente verifico a Finalidade ...
    if($campos[0]['finalidade'] == 'C') {
        $finalidade = 'CONSUMO';
    }else if($campos[0]['finalidade'] == 'I') {
        $finalidade = 'INDUSTRIALIZAÇÃO';
    }else {
        $finalidade = 'REVENDA';
    }
    $rotulo_sgd.= '/'.$finalidade;
}

$prazo_faturamento.=    $rotulo_sgd;
$conceder_pis_cofins    =  $campos[0]['conceder_pis_cofins'];
$mg_l_m_g               = $campos[0]['mg_l_m_g'];

//Para os cálculos
$tipo_suframa		= $campos[0]['tipo_suframa'];
$suframa_ativo		= $campos[0]['suframa_ativo'];
$id_uf_cliente		= $campos[0]['id_uf'];

//Dados dos Clientes para Cabeçalho
$id_pais		= $campos[0]['id_pais'];
$cliente                = $campos[0]['cliente'];
$data_emissao           = data::datetodata(substr($campos[0]['data_emissao'], 0, 10), '/');
$bairro			= $campos[0]['bairro'];
$endereco		= $campos[0]['endereco'];
$num_complemento        = $campos[0]['num_complemento'];
$email			= $campos[0]['email'];
$cep			= $campos[0]['cep'];
$cidade			= $campos[0]['cidade'];
$tipo_faturamento       = $campos[0]['tipo_faturamento'];

$sql = "SELECT `sigla` 
        FROM `ufs` 
        WHERE `id_uf` = '$id_uf_cliente' LIMIT 1 ";
$campos_ufs = bancos::sql($sql);
$estado     = $campos_ufs[0]['sigla'];

$sql = "SELECT `pais` 
        FROM `paises` 
        WHERE `id_pais` = ".$campos[0]['id_pais']." LIMIT 1 ";
$campos_pais    = bancos::sql($sql);
$pais           = $campos_pais[0]['pais'];

if(!empty($campos[0]['cnpj_cpf'])) {
    if(strlen($campos[0]['cnpj_cpf']) == 11) {//CPF ...
        $cnpj_cpf = substr($campos[0]['cnpj_cpf'], 0, 3).'.'.substr($campos[0]['cnpj_cpf'], 3, 3).'.'.substr($campos[0]['cnpj_cpf'], 6, 3).'-'.substr($campos[0]['cnpj_cpf'], 9, 2);
    }else {//CNPJ ...
        $cnpj_cpf = substr($campos[0]['cnpj_cpf'], 0, 2).'.'.substr($campos[0]['cnpj_cpf'], 2, 3).'.'.substr($campos[0]['cnpj_cpf'], 5, 3).'/'.substr($campos[0]['cnpj_cpf'], 8, 4).'-'.substr($campos[0]['cnpj_cpf'], 12, 2);
    }
}else {
    $cnpj_cpf = '';
}

$insc_estadual  = $campos[0]['insc_estadual'];
$insc_estadual  = substr($insc_estadual, 0, 3).'.'.substr($insc_estadual, 3, 3).'.'.substr($insc_estadual, 6, 3).'.'.substr($insc_estadual, 9, 3);

$ddi_com        = $campos[0]['ddi_com'];
$ddd_com        = $campos[0]['ddd_com'];
$telcom         = $campos[0]['telcom'];
$telefone_com   = '('.$ddi_com.'-'.$ddd_com.') '.$telcom;

$ddi_fax        = $campos[0]['ddi_fax'];
$ddd_fax        = $campos[0]['ddd_fax'];
$telfax         = $campos[0]['telfax'];
$telefone_fax   = '('.$ddi_fax.'-'.$ddd_fax.') '.$telfax;

$transportadora = $campos[0]['nome'];

//Verifico se o Cliente é Nacional ou Internacional ...
$tipo_moeda 	= ($id_pais != 31) ? 'U$' : 'R$';

Heade($id_pais, $tipo_faturamento);
$pdf->SetFont('Arial', '', 10);
$pdf->Ln(8);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 5, 'CLIENTE: ', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(147, 5, $cliente, 0, 1, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(85, 5, 'ENDEREÇO / BAIRRO / CIDADE / UF / PAÍS / CEP:', 0, 0, 'L');
$pdf->SetFont('Arial','',10);

//Caso estiver vázio
if(empty($cep)) $cep = '-';
if(empty($bairro)) $bairro = '-';
if(empty($cidade)) $cidade = '-';
if(empty($estado)) $estado = '-';
if(empty($pais)) $pais = '-';
/******************/

$pdf->Cell(109, 5, $endereco.', '.$num_complemento.' / '.$bairro.' / '.$cidade.' / '.$estado.' / '.$pais.' / '.$cep, 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(22, 5, 'CNPJ / CPF:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(125, 5, $cnpj_cpf, 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(22, 5, 'INSC. EST.:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(53, 5, $insc_estadual, 0, 1, 'L');

if($telefone_com != '' && $telefone_fax == '') {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(17, 5, 'FONE(S): ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(130, 5, $telefone_com, 0, 0, 'L');
}else {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(17, 5, 'FONE(S): ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(130, 5, $telefone_com.' / FAX: '.$telefone_fax, 0, 0, 'L');
}

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(15, 5, 'E-MAIL: ', 0, 0, 'L');
$pdf->SetFont('Arial', 'U', 10);
$pdf->Cell(40, 5, $email, 0, 1, 'L', '', 'mailto:'.$email.'?subject=E-Mail Albafer (Pedido)&body=Albafer');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(19, 5, 'EMISSÃO:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(128, 5, $data_emissao, 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
//Significa que o Cliente é do Tipo Internacional
if($id_pais != 31) {
    $pdf->Cell(15, 5, 'PAGO: ', 0, 0, 'L');
//Significa que o Cliente é do Tipo Nacional
}else {
    $pdf->Cell(49, 5, 'FORMA DE VENDA: ', 0, 0, 'L');
}
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(45, 5, $prazo_faturamento, 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(48, 5, 'VALIDADE DA PROPOSTA: ', 0, 0, 'L');

$pdf->SetFont('Arial', '', 10);
$vetor_dados_gerais = vendas::dados_gerais_orcamento($_GET['id_orcamento_venda']);
$pdf->Cell(99, 5, data::datetodata($vetor_dados_gerais['data_validade_orc'], '/'), 0, 0, 'L');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(29, 5, 'TIPO DE FRETE: ', 0, 0, 'L');
if($tipo_frete == 'F') {
    $pdf->Cell(71, 5, 'FOB (P/ CONTA DO CLIENTE)', 0, 1, 'L');
}else {
    $pdf->Cell(71, 5, 'CIF (POR NOSSA CONTA)', 0, 1, 'L');
}

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(38, 5, 'TRANSPORTADORA:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(89, 5, $transportadora, 0, 1, 'L');

/*************************************************************************************/
/***************************************Suframa***************************************/
/*************************************************************************************/ 
/*Esse campo $conceder_pis_cofins só serve simplesmente como histórico, pra saber se o Orçamento foi negociado 
com base de Suframa caso o Cliente passe a ter o Suframa como sendo Inativo ...*/
if($conceder_pis_cofins == 'S') {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 5, 'SUFRAMA:', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);

//Vetor
    $textos_suframa[1] = 'Desconto de ICMS = '.number_format(genericas::variavel(40), 2, ',', '.').'% ';
    $textos_suframa[2] = 'Desconto de PIS + Cofins = '.number_format((genericas::variavel(20)+genericas::variavel(21)), 2, ',', '.').' % e ICMS = '.number_format(genericas::variavel(40), 2, ',', '.').' % ';
    $texto_fixo = '(A ser concedido na Emissão da Nota Fiscal)';
	
    if($tipo_suframa == 1 && $suframa_ativo == 'S') {//Área de Livre e o Cliente possui o Suframa Ativo ...
        $pdf->Cell(160, 5, $textos_suframa[1].$texto_fixo, 0, 1, 'L');
    }else if($tipo_suframa == 2 && $suframa_ativo == 'S') {//Zona Franca de Man...
        $pdf->Cell(160, 5, $textos_suframa[2].$texto_fixo, 0, 1, 'L');
    }
}
/*************************************************************************************/

if(!empty($campos[0]['artigo_isencao_orcamento'])) {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(160, 5, 'SUSPENSO IPI, CONF.ART.29, PARÁGRAFO 1, ALÍNEA A E B, LEI 10637/02.', 0, 1, 'L');
}

/**********************************************************************/
/*************************Observação do Cliente************************/
/**********************************************************************/
/*Eu me aproveito desse desvio p/ fazer apresentar a Observação do Cliente, mas isso internamente porque, pode ser que um de nossos atendentes 
faça algum comentário que não seja legal referente ao cliente e por isso que não faço com que esse seja exibido sempre e só nesse caso ...*/
if($_GET['exibir_margem_lucro'] == 1) {
    $sql = "SELECT `observacao` 
            FROM `follow_ups` 
            WHERE `id_cliente` = '".$campos[0]['id_cliente']."' 
            AND `origem` = '15' LIMIT 1 ";
    $campos_follow_up = bancos::sql($sql);
    if(count($campos_follow_up) == 1) {
        $pdf->Ln(2);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell($GLOBALS['ph'] * 100, 5, 'Obs. do Cliente: '.$campos_follow_up[0]['observacao'], 0, 1, 'C');
    }
}
/**********************************************************************/

//Seleção dos Itens do Orçamento
$sql = "SELECT ovi.*, pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, pa.`preco_unitario`, 
        pa.`operacao_custo`, pa.`operacao`, pa.`peso_unitario`, pa.`status_custo`, 
        pa.`preco_promocional_b`, u.`sigla` 
        FROM `orcamentos_vendas_itens` ovi 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
        INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
        INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
        WHERE ovi.`id_orcamento_venda` = '$_GET[id_orcamento_venda]' ORDER BY ovi.`id_orcamento_venda_item` ";
$campos_itens 	= bancos::sql($sql);
$linhas_itens	= count($campos_itens);

$tx_financeira = custos::calculo_taxa_financeira($_GET['id_orcamento_venda']);

for($i = 0; $i < $linhas_itens; $i++) $total_orcamento+= $campos_itens[$i]['preco_liq_final'] * $campos_itens[$i]['qtde'];

//Isso aqui é uma adaptação, já que não existe id_empresa em Orçamento ...
$id_empresa_nf  = ($campos[0]['nota_sgd'] == 'N') ? 1 : 4;

for($i = 0; $i < $linhas_itens; $i++) {
    $calculo_impostos_item  = calculos::calculo_impostos($campos_itens[$i]['id_orcamento_venda_item'], $_GET['id_orcamento_venda'], 'OV');
    
    if($GLOBALS['nova_pagina'] == 'sim') {
        $GLOBALS['nova_pagina'] = 'nao';
        if($i != 0) {
            $pdf->Ln(-4);
            Heade($id_pais, $tipo_faturamento);
        }
        rotulo($tipo_moeda);
    }
    $pdf->SetFont('Arial', '', 7);
//Qtde ...
    $pdf->Cell($GLOBALS['ph'] * 4, 5, number_format($campos_itens[$i]['qtde'], 0, ',', '.'), 1, 0, 'C');
/*Se existir um PA Substitutivo "Gato por Lebre" (rs) ..., então eu tenho q/ mostrar a sigla (SB) para 
que este está sendo substituido por outro PA ...*/
    $sub = ($campos_itens[$i]['id_produto_acabado_discriminacao'] != 0) ? '(SB) ' : '';
//Ref. * Discriminação ...
    $pdf->Cell($GLOBALS['ph'] * 38, 5, $sub.intermodular::pa_discriminacao($campos_itens[$i]['id_produto_acabado'], 0, 0, 0, $campos_itens[$i]['id_produto_acabado_discriminacao'], 1), 1, 0, 'L');
    
    /*Esse controle é de extrema importância porque em casos de "Gato por Lebre", preciso pegar 
    os impostos do Gato ...

    Ex: o cliente comprou MRH-042 "Gato", mas estamos enviando o MRT-042 "Lebre ou substituto" ...*/
    $id_produto_acabado_utilizar = (!empty($campos_itens[$i]['id_produto_acabado_discriminacao'])) ? $campos_itens[$i]['id_produto_acabado_discriminacao'] : $campos_itens[$i]['id_produto_acabado'];
    
    //Essas variáveis serão utilizadas mais abaixo ...
    $dados_produto      = intermodular::dados_impostos_pa($id_produto_acabado_utilizar, $id_uf_cliente, $campos[0]['id_cliente'], $id_empresa_nf, $campos[0]['finalidade']);
    $classific_fiscal 	= $dados_produto['classific_fiscal'];
    $ipi                = $dados_produto['ipi'];
    $reducao            = $dados_produto['reducao'];
    //Se não existe Redução o ICMS é o próprio valor retornado da função, se não desconto a Redução do ICMS ...
    $icms               = ($reducao == 0) ? $dados_produto['icms'] : ($dados_produto['icms'] - ($dados_produto['icms'] * $dados_produto['reducao'] / 100));
//NCM ...
    $pdf->Cell($GLOBALS['ph'] * 5, 5, $classific_fiscal, 1, 0, 'C');
//Preço Unit. R$ ...
    if($_GET['exibir_margem_lucro'] == 1) {//Nesse caso eu também o Preço B para que o Wilson possa fazer o comparativo de Vendas ...
        $preco_unitario = number_format($campos_itens[$i]['preco_liq_final'], 2, ',', '.').' | PçB='.number_format($campos_itens[$i]['preco_promocional_b'], 2, ',', '.');
    }else {
        $preco_unitario = number_format($campos_itens[$i]['preco_liq_final'], 2, ',', '.');
    }
    $pdf->Cell($GLOBALS['ph'] * 9, 5, $preco_unitario, 1, 0, 'C');
    
    /************************************Margem de Lucro************************************/
    if($_GET['exibir_margem_lucro'] == 1) {
        //Aqui faço um Comparativo de Margens p/ saber se estamos lucrando ou tomando na Cabeça dos Pços atuais com os Antigos Preços B ...
        /**********Preço Atual**********/
        $margem                     = custos::margem_lucro($campos_itens[$i]['id_orcamento_venda_item'], $tx_financeira, $id_uf_cliente, $campos_itens[$i]['preco_liq_final']);
        $custo_margem_lucro_zero    = $margem[2];//preco_custo_zero
        $soma_margem+= $custo_margem_lucro_zero * $campos_itens[$i]['qtde'];

        $mlm_por_item 	= ' >> M.L. '.$margem[1];
        $mlmg_por_item 	= ' / G. '.$campos_itens[$i]['margem_lucro'];

        /************Preço B************/	
        /*Se existir o Preço B do Produto será esse que irei utilizar para comparação de ML, senão vou utilizar o 
        Preço de Lista Cheio do PA e desse eu retiro 60% de desconto + 10% de desconto ...*/
        $preco_b_a_ser_utilizado	= ($campos_itens[$i]['preco_promocional_b'] != 0) ? $campos_itens[$i]['preco_promocional_b'] : round($campos_itens[$i]['preco_unitario'] * 0.40 * 0.90, 2);
        $margem_preco_b                 = custos::margem_lucro($campos_itens[$i]['id_orcamento_venda_item'], $tx_financeira, $id_uf_cliente, $preco_b_a_ser_utilizado);
        $soma_margem_preco_b+= 		str_replace(',', '.', $margem_preco_b[1]);
    }
    /***************************************************************************************/
//Total R$
    $preco_total_lote = $campos_itens[$i]['preco_liq_final'] * $campos_itens[$i]['qtde'];
    
    $pdf->Cell($GLOBALS['ph'] * 10, 5, number_format($preco_total_lote, 2, ',', '.').$mlm_por_item.$mlmg_por_item, 1, 0, 'C');
    if($ipi > 0) {
        $pdf->Cell($GLOBALS['ph'] * 3.5, 5, number_format($ipi, 2, ',', '.'), 1, 0, 'C');
    }else {
        $pdf->Cell($GLOBALS['ph'] * 3.5, 5, 'S/IPI', 1, 0, 'C');
    }
//ICMS %
    if($icms > 0) {
        $pdf->Cell($GLOBALS['ph'] * 3.5, 5, number_format($icms, 2, ',', '.'), 1, 0, 'C');
    }else {
        $pdf->Cell($GLOBALS['ph'] * 3.5, 5, 'S/ICMS', 1, 0, 'C');
    }
//IVA % ...
    if($campos_itens[$i]['iva'] > 0) {
        $msn_iva            = number_format($campos_itens[$i]['iva'], 1, ',', '.').' / '.number_format(($calculo_impostos_item['valor_icms_st'] * 100) / $preco_total_lote, 1, ',', '.');
        $exibir_msn_iva++;
    }else {
        $msn_iva            = 'S/IVA';
    }
    $pdf->Cell($GLOBALS['ph'] * 4.5, 5, $msn_iva, 1, 0, 'C');
//Peso / Pç (Kg)
    $pdf->Cell($GLOBALS['ph'] * 4.5, 5, number_format($campos_itens[$i]['peso_unitario'], 3, ',', '.'), 1, 0, 'C');
//Peso Total (Kg)
    $pdf->Cell($GLOBALS['ph'] * 5.5, 5, number_format($campos_itens[$i]['peso_unitario'] * $campos_itens[$i]['qtde'], 3, ',', '.'), 1, 0, 'C');
    $total_kgs+= ($campos_itens[$i]['peso_unitario'] * $campos_itens[$i]['qtde']);
//Representante
    $sql = "SELECT nome_fantasia 
            FROM `representantes` 
            WHERE `id_representante` = '".$campos_itens[$i]['id_representante']."' LIMIT 1 ";
    $campos_rep = bancos::sql($sql);
    $pdf->Cell($GLOBALS['ph'] * 6.5, 5, $campos_rep[0]['nome_fantasia'], 1, 0, 'C');
	
    $vetor_prazos_entrega   = vendas::prazos_entrega();
//Essa variável aqui é um flag de Impressão p/ saber se a linha de Prazo foi impressa ou não ...
    $printou                = 0;

    foreach($vetor_prazos_entrega as $indice => $prazo_entrega) {
        //Compara o valor do Banco com o valor do Vetor
        if($campos_itens[$i]['prazo_entrega'] == $indice) {//Se igual 
            $estoque_produto 	= estoque_acabado::qtde_estoque($campos_itens[$i]['id_produto_acabado']);
            $qtde_estoque       = $estoque_produto[3];
            $complemento        = ($campos_itens[$i]['prazo_entrega'] == 'P') ? ' / '.number_format($qtde_estoque, 2, ',', '.').' '.$campos_itens[$i]['sigla'] : '';
            $pdf->Cell($GLOBALS['ph'] * 7, 5, $prazo_entrega.$complemento, 1, 1, 'C');
            $printou = 1;
        }
    }
/*Caso não foi printada a linha de Prazo(s) + acima, então eu tenho que garantir q/ essa 
linha vai ser printada ...*/
    if($printou == 0) $pdf->Cell($GLOBALS['ph'] * 7, 5, $campos_itens[$i]['prazo_entrega'].' Dias', 1, 1, 'C');
}
/************************************Margem de Lucro************************************/
if($_GET['exibir_margem_lucro'] == 1) {
    //Faço esse tratamento p/ não dar erro de divisão por 0
    if($soma_margem == 0 || empty($soma_margem) || (integer)($soma_margem) == 0) $soma_margem = 1;
    $mlm_geral 	= 'M.L. '.number_format(((($total_orcamento / round($soma_margem, 1)) - 1) * 100), 1, ',', '.').'% ';
    if($mg_l_m_g != '0.00') $mlmg_geral	= ' / G. '.number_format(((($total_orcamento / $mg_l_m_g) - 1) * 100), 1, ',', '.').'% ';
    $mlm_geral_preco_b = ' | M.L.PçB='.number_format($soma_margem_preco_b / $linhas, 1, ',', '.').'% ';
}
/***************************************************************************************/
$calculo_total_impostos = calculos::calculo_impostos(0, $_GET['id_orcamento_venda'], 'OV');

$pdf->SetFont('Arial', 'B', 9);

$pdf->Cell($GLOBALS['ph']*22, 5, '* VALOR DO ICMS ST: '.$tipo_moeda.' '.number_format($calculo_total_impostos['valor_icms_st'], 2, ',', '.'), 'TBL', 0, 'L');
$pdf->Cell($GLOBALS['ph']*18, 5, '* VALOR TOTAL DO IPI: '.$tipo_moeda.' '.number_format($calculo_total_impostos['valor_ipi'], 2, ',', '.'), 'TB', 0, 'L');
$pdf->Cell($GLOBALS['ph']*24, 5, '* VALOR TOTAL DOS PRODUTOS: '.$tipo_moeda.' '.number_format($calculo_total_impostos['valor_total_produtos'], 2, ',', '.'), 'TB', 0, 'L');

$total_orcamento = $calculo_total_impostos['valor_icms_st'] + $calculo_total_impostos['valor_ipi'] + $calculo_total_impostos['valor_total_produtos'];;

$pdf->Cell($GLOBALS['ph']*25, 5, '* TOTAL DO ORÇAMENTO: '.$tipo_moeda.' '.number_format($total_orcamento, 2, ',', '.'), 'TB', 0, 'L');
$pdf->Cell($GLOBALS['ph']*12, 5, '* TOTAL KGs: '.number_format($total_kgs, 3, ',', '.'), 'TBR', 1, 'L');
$pdf->Cell($GLOBALS['ph']*101, 5, $mlm_geral.$mlmg_geral.$mlm_geral_preco_b, 1, 1, 'L');

$pdf->Ln(8);
$pdf->SetFont('Arial', 'B', 10);
if($exibir_msn_iva > 0) $pdf->Cell($GLOBALS['ph']*10, 5, 'ATENÇÃO: EXISTE(M) ITEM(NS) COM SUBSTITUIÇÃO TRIBUTÁRIA.', 0, 1, 'L');

/*Esse parâmetro "$_GET['mostrar_observacao']" só irá existir quando esse arquivo for chamado 
por dentro do SCAN ERP ...*/
if($_GET['mostrar_observacao'] != 'N') {
    //Aqui eu busco a observação do Follow-UP deste Orçamento que possui a marcação Exibir no PDF ...
    $sql = "SELECT `observacao` 
            FROM `follow_ups` 
            WHERE `identificacao` = '$_GET[id_orcamento_venda]' 
            AND `origem` = '1' 
            AND `exibir_no_pdf` = 'S' LIMIT 1 ";
    $campos_follow_up   = bancos::sql($sql);
    if(count($campos_follow_up) == 1) {
        $pdf->Ln(2);
        $pdf->Cell($GLOBALS['ph'] * 10, 5, 'OBSERVAÇÃO:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 9);

        //Somente para observações longas
        //Controle para fazer corretamente a divisão de sílabica
        if(strlen($campos_follow_up[0]['observacao']) > 140) {
            $posicao = 145;
        //1) Controle
            for($i = $posicao; $i < 160; $i++) {
        //Verifico se é igual espaço, se for significa que terminou já terminou a palavra
                if(substr($campos_follow_up[0]['observacao'], $posicao, 1) == ' ') {
                    $i = 160;
        //Ainda não terminou a palavra
                }else {
                    $posicao++;
                }
            }
        //Primeira Linha
            $pdf->Cell($GLOBALS['ph']*87, 5, substr($campos_follow_up[0]['observacao'], 0, $posicao), 0, 1, 'L');
        //Printa até o final
            $pdf->Cell($GLOBALS['ph']*87, 5, substr($campos_follow_up[0]['observacao'], $posicao, 120), 0, 1, 'L');
        //Observações Pequenas
        }else {
            $pdf->Cell($GLOBALS['ph']*87, 5, $campos_follow_up[0]['observacao'], 0, 1, 'L');
        }
    }
}

$pdf->Cell($GLOBALS['ph']*85, 5, '', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($GLOBALS['ph']*85, 5, 'Conforme sua solicitação passamos preço, prazo, e demais condições de fornecimento para os seguintes itens, lembrando que para peças especiais é admissível na produção, ', 0, 1, 'L');
$pdf->Cell($GLOBALS['ph']*85, 5, '10% a mais ou a menos na quantidade.', 0, 1, 'L');

chdir('../../../../../pdf');
$file='../../../../../pdf/'.basename(tempnam(getcwd()), '').'Orcamento_Grupo_Albafer_'.$_GET['id_orcamento_venda'].'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>