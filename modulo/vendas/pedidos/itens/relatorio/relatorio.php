<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
require('../../../../../lib/calculos.php');
require('../../../../../lib/data.php');
require('../../../../../lib/genericas.php');
require('../../../../../lib/intermodular.php');
require('../../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/pedidos/itens/consultar.php', '../../../../../');

$vetor_vias = array('(***  VIA CLIENTE ***)', '(*** VIA ESTOQUE ***)', '(*** VIA CLIENTE / VIA ESTOQUE ***)');
//PDF normal ...
error_reporting(0);
function rotulo($tipo_moeda) {//Porque chama mais de uma vez por causa da paginacao
    global $pdf, $tipo_moeda;
    $pdf->SetLeftMargin(1);
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell($GLOBALS['ph']*4, 5, 'QTDE', 'RLT', 0, 'C');
    $pdf->Cell($GLOBALS['ph']*51, 5, 'PRODUTO', 'RLT', 0, 'C');
    $pdf->Cell($GLOBALS['ph']*11.4, 5, 'Desconto', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*6, 5, 'Pço ', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*6.5, 5, 'Valor ', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*4, 5, 'IPI %', 'RLT', 0, 'C');
    $pdf->Cell($GLOBALS['ph']*8.5, 5, 'Represen-', 'RLT', 0, 'C');
    $pdf->Cell($GLOBALS['ph']*6, 5, 'Pzo Entr', 'RLT', 0, 'C');
    $pdf->Cell($GLOBALS['ph']*3, 5, '', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*3, 5, 'Sep', 1, 1, 'C');
    
    $pdf->Cell($GLOBALS['ph']*4, 5, '', 'RLB', 0, 'C');
    $pdf->Cell($GLOBALS['ph']*51, 5, '', 'RLB', 0, 'C');
    $pdf->Cell($GLOBALS['ph']*11.4, 5, 'Cl./Ext./SGD/Acr.', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*6, 5, 'Unit. '.$tipo_moeda, 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*6.5, 5, 'Total '.$tipo_moeda, 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*4, 5, '', 'RLB', 0, 'C');
    $pdf->Cell($GLOBALS['ph']*8.5, 5, 'tante', 'RLB', 0, 'C');
    $pdf->Cell($GLOBALS['ph']*6, 5, 'Dias', 'RLB', 0, 'C');
    $pdf->Cell($GLOBALS['ph']*3, 5, '', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*3, 5, 'Sep', 1, 1, 'C');
}

function Heade($id_empresa_pedido, $id_pais, $ajuste) {
    $GLOBALS['ph']+=$ajuste;
    global $pdf, $id_pedido_venda;
    if($id_empresa_pedido == 4) {//Fica como se o usuário escolheu o Cabeçalho da Albafer ...
        $aditivo_razao = 'GRUPO ';
        $id_empresa_pedido = 1;
    }
//Busca de Dados da Empresa ...
    $sql = "SELECT * 
            FROM `empresas` 
            WHERE `id_empresa` = '$id_empresa_pedido' LIMIT 1 ";
    $campos_fone    = bancos::sql($sql);
    $razao_social   = $campos_fone[0]['razaosocial'];
    $cnpj           = $campos_fone[0]['cnpj'];
    $cnpj           = substr($cnpj, 0, 2).'.'.substr($cnpj, 2, 3).'.'.substr($cnpj, 5, 3).'/'.substr($cnpj, 8, 4).'-'.substr($cnpj, 12, 2);
    $ie             = $campos_fone[0]['ie'];
    $ie             = substr($ie, 0, 3).'.'.substr($ie, 3, 3).'.'.substr($ie, 6, 3).'.'.substr($ie, 9, 3);
    $endereco       = $campos_fone[0]['endereco'];
    $numero         = $campos_fone[0]['numero'];
    $bairro         = strtoupper($campos_fone[0]['bairro']);
    $cidade         = strtoupper($campos_fone[0]['cidade']);
    $telefone_comercial = $campos_fone[0]['telefone_comercial'];
    $cep            = $campos_fone[0]['cep'];
    $ddd_comercial  = $campos_fone[0]['ddd_comercial'];

    $pdf->Image('../../../../../imagem/logo_transparente.jpg', 7, 5, 34, 36, 'JPG');
    $pdf->Cell(15 * $GLOBALS['ph'], 10, '', 0, 0, 'L');
    $pdf->SetFont('Arial', 'BI', 12);
    $pdf->Cell(175, 10, '* '.$aditivo_razao.$razao_social, 0, 0, 'L');
    $pdf->SetFont('Arial', 'BI', 9);
    $pdf->Ln(5);

    $pdf->Cell(15 * $GLOBALS['ph'], 10, '', 0, 0, 'L');
    $pdf->Cell(124, 10, $endereco.', N.º '.$numero.' - '.$bairro.' - '.$cidade.' - CEP: '.$cep, 0, 0, 'L');
    $pdf->SetFont('Arial', 'BIU', 13);
    $pdf->Cell(40, 10, 'Pedido N.º: '.$_GET['id_pedido_venda'], 0, 0, 'L');
    $pdf->Ln(4);
    $pdf->SetFont('Arial', 'BI', 9);
    $pdf->Cell(15*$GLOBALS['ph'], 10, '', 0, 0, 'L');
//Significa que o Cliente é do Tipo Internacional
    if($id_pais != 31) {
        $email = 'mercedes@grupoalbafer.com.br';
//Significa que o Cliente é do Tipo Nacional
    }else {
        $email = 'vendas@grupoalbafer.com.br';
    }
    $pdf->Cell(120, 10, 'FONE/FAX: (55-'.$ddd_comercial.') '.$telefone_comercial.' # E-MAIL: '.$email, 0, 0, 'L');
    $pdf->Ln(4);

    $pdf->Cell(15 * $GLOBALS['ph'], 10, '', 0, 0, 'L');
    $pdf->Cell(127, 10, 'CNPJ: '.$cnpj.' # INSC. EST. - '.$ie, 0, 0, 'L');
    $pdf->SetFont('Arial', 'I', 13);
    $pdf->Cell(30, 10, 'Página: '.$GLOBALS['num_pagina'], 0, 0, 'L');
    $pdf->Ln(8);

//Aqui é Padrão para todas as Empresas
    $pdf->SetFont('Arial', 'BI', 9);
    $pdf->Cell(15*$GLOBALS['ph'], 10, '', 0, 0, 'L');
    $pdf->Cell(30, 10, 'Impressão: '.date('d/m/Y').' - '.date('H:i:s'), 0, 0, 'L');
    $pdf->Ln(4);

//Aqui verifica quem é o funcionário responsável pelo Pedido, Data e Hora
    $sql = "SELECT id_funcionario, data_sys 
            FROM `pedidos_vendas` 
            WHERE `id_pedido_venda` = '$_GET[id_pedido_venda]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $data   = data::datetodata(substr($campos[0]['data_sys'], 0, 10), '/');
    $hora   = substr($campos[0]['data_sys'], 11, 8);
    $sql = "SELECT l.login 
            FROM `funcionarios` f 
            INNER JOIN `logins` l ON l.id_funcionario = f.id_funcionario 
            WHERE f.`id_funcionario` = '".$campos[0]['id_funcionario']."' LIMIT 1 ";
    $campos_login = bancos::sql($sql);
    $pdf->Cell(15*$GLOBALS['ph'], 10, '', 0, 0, 'L');
    $pdf->Cell(97, 10, 'Última alteração: '.ucfirst($campos_login[0]['login']).' - '.$data.' - '.$hora, 0, 1, 'L');
    //$pdf->Image('../../../../../imagem/marcas/cabri.jpg', 60, 39, 10, 10, 'JPG');
    //$pdf->Image('../../../../../imagem/marcas/heinz.jpg', 80, 39, 20, 10, 'JPG');
    //$pdf->Image('../../../../../imagem/marcas/warrior.jpg', 108, 39, 30, 10, 'JPG');
    $pdf->Line(1*$GLOBALS['ph'],43,101.5*$GLOBALS['ph'],43);
    $pdf->Ln(2);
    $GLOBALS['ph']-=$ajuste;
}

/////////////////////////////////////// INÍCIO PDF ///////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel		= 'P';  // P=> Retrato L=>Paisagem
$unidade		= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel          = 'A4'; // A3, A4, A5, Letter, Legal
$pdf                    = new FPDF($tipo_papel, $unidade, $formato_papel);
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

//Esta parte está fora do head só é utilizada na primeira página
//Busca dos dados do Cliente
$sql = "SELECT c.`cod_cliente`, c.`id_pais`, c.`id_uf`, 
        IF(c.`nomefantasia` = '', c.`razaosocial`, CONCAT(c.`nomefantasia`, ' (', c.`razaosocial`, ')')) AS cliente, 
        c.`insc_estadual`, c.`cnpj_cpf`, c.`credito`, 
        c.`endereco`, c.`num_complemento`, c.`bairro`, c.`email`, c.`cep`, c.`cidade`, c.`tipo_faturamento`, 
        c.`tipo_suframa`, c.`suframa_ativo`, c.`ddi_com`, c.`ddd_com`, c.`telcom`, c.`ddi_fax`, c.`ddd_fax`, 
        c.`telfax`, pv.*, cc.`nome` AS contato, t.`nome` 
        FROM `pedidos_vendas` pv 
        INNER JOIN `transportadoras` t ON t.`id_transportadora` = pv.`id_transportadora` 
        INNER JOIN `clientes_contatos` cc ON cc.`id_cliente_contato` = pv.`id_cliente_contato` 
        INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
        WHERE pv.`id_pedido_venda` = '$_GET[id_pedido_venda]' LIMIT 1 ";
$campos = bancos::sql($sql);
if($campos[0]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[0]['vencimento4'];
if($campos[0]['vencimento3'] > 0) $prazo_faturamento = '/'.$campos[0]['vencimento3'].$prazo_faturamento;
if($campos[0]['vencimento2'] > 0) {
    $prazo_faturamento = $campos[0]['vencimento1'].'/'.$campos[0]['vencimento2'].$prazo_faturamento;
}else {
    $prazo_faturamento = ($campos[0]['vencimento1'] == 0) ? 'À vista' : $campos[0]['vencimento1'];
}
//Aqui é a verificação do Tipo de Nota
if($campos[0]['id_empresa'] == 4) {
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
$prazo_faturamento.=$rotulo_sgd;

//Para os cálculos
$tipo_suframa	 	= $campos[0]['tipo_suframa'];
$suframa_ativo		= $campos[0]['suframa_ativo'];
//Dados dos Clientes para Cabeçalho
$id_cliente             = $campos[0]['id_cliente'];
$id_pais                = $campos[0]['id_pais'];
$data_emissao	  	= data::datetodata(substr($campos[0]['data_emissao'], 0, 10), '/');
if($campos[0]['faturar_em'] != '0000-00-00') {//Coloca no formato de Data
    $faturar_em         = data::datetodata($campos[0]['faturar_em'], '/');
}else {
    $faturar_em         = '';
}
$bairro                 = $campos[0]['bairro'];
$endereco               = $campos[0]['endereco'];
$num_complemento  	= $campos[0]['num_complemento'];
$email                  = $campos[0]['email'];
$cep                    = $campos[0]['cep'];
$cidade                 = $campos[0]['cidade'];
$transportadora    	= $campos[0]['nome'];

if($campos[0]['id_uf'] > 0) {
    $sql = "SELECT `sigla` 
            FROM `ufs` 
            WHERE `id_uf` = '".$campos[0]['id_uf']."' LIMIT 1 ";
    $campos_uf  = bancos::sql($sql);
    $estado     = $campos_uf[0]['sigla'];
}else {
    $estado     = 'EX';
}

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

$insc_estadual = $campos[0]['insc_estadual'];
$insc_estadual = substr($insc_estadual, 0, 3).'.'.substr($insc_estadual, 3, 3).'.'.substr($insc_estadual, 6, 3).'.'.substr($insc_estadual, 9, 3);

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

Heade($campos[0]['id_empresa'], $id_pais);
$pdf->SetFont('Arial', '', 10);
$pdf->SetLeftMargin(1);

$pdf->Ln(8);
$pdf->SetLeftMargin(1);
$pdf->SetFont('Arial', 'B', 10);

if($campos[0]['livre_debito'] == 'S') $msn_libre_debito = ' - LIVRE DE DÉBITO';

$pdf->Cell(167, 5, $vetor_vias[$_GET['via']].$msn_libre_debito, 0, 1, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 5, 'CLIENTE: ', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(147, 5, $campos[0]['cod_cliente'].' - '.$campos[0]['cliente'], 0, 1, 'L');
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(20, 5, 'ENDEREÇO:', 0, 0, 'L');
$pdf->SetFont('Arial','',8);

//Caso estiver vázio
$cep = (empty($cep)) ? '' : ' CEP-'.$cep;
if(empty($bairro))  $bairro = '-';
if(empty($cidade))  $cidade = '-';
if(empty($estado))  $estado = '-';
if(empty($pais))    $pais = '-';
/******************/
$pdf->Cell(99, 5, $endereco.', '.$num_complemento.' - '.$bairro.' - '.$cidade.' - '.$estado.' - '.$pais.' - '.$cep, 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(22, 5, 'CNPJ / CPF:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(105, 5, $cnpj_cpf, 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(22, 5, 'INSC. EST.:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(53, 5, $insc_estadual, 0, 1, 'L');

if($telefone_com != '' && $telefone_fax == '') {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(17, 5, 'FONE(S): ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(110, 5, $telefone_com, 0, 0, 'L');
}else {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(17, 5, 'FONE(S): ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(110, 5, $telefone_com.' / FAX: '.$telefone_fax, 0, 0, 'L');
}

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(15, 5, 'E-MAIL: ', 0, 0, 'L');
$pdf->SetFont('Arial', 'U', 10);
$pdf->Cell(40, 5, $email, 0, 1, 'L', '', 'mailto:'.$email.'?subject=E-Mail Albafer (Pedido)&body=Albafer');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(19, 5, 'EMISSÃO:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(21, 5, $data_emissao.' -  ', 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(26, 5, 'FATURAR EM:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(61, 5, $faturar_em, 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
if($id_pais != 31) {//Significa que o Cliente é do Tipo Internacional
    $pdf->Cell(15, 5, 'PAGO:', 0, 0, 'L');
}else {//Significa que o Cliente é do Tipo Nacional
    $pdf->Cell(35, 5, 'FORMA DE VENDA:', 0, 0, 'L');
}
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(45, 5, $prazo_faturamento, 0, 1, 'L');

/******************************************************************************/
//Busco Dados de Frete que estão no Orçamento que gerou este Pedido de Venda ...
$sql = "SELECT ov.`tipo_frete` 
        FROM `pedidos_vendas_itens` pvi 
        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
        INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda 
        WHERE pvi.`id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
$campos_orcamento = bancos::sql($sql);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(29, 5, 'TIPO DE FRETE: ', 0, 0, 'L');
if($campos_orcamento[0]['tipo_frete'] == 'F') {
    $pdf->Cell(98, 5, 'FOB (P/ CONTA DO CLIENTE)', 0, 0, 'L');
}else {
    $pdf->Cell(98, 5, 'CIF (POR NOSSA CONTA)', 0, 0, 'L');
}
$pdf->Cell(19, 5, 'CRÉDITO:', 0, 0, 'L');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(45, 5, $campos[0]['credito'], 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(38, 5, 'TRANSPORTADORA:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(89, 5, $transportadora, 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(30, 5, 'CTT - S/ PED N.º:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
if(!empty($campos[0]['contato'])) {
    $pdf->Cell(45, 5, $campos[0]['contato'].' - '.$campos[0]['num_seu_pedido'], 0, 1, 'L');
}else {
    $pdf->Cell(45, 5, $campos[0]['num_seu_pedido'], 0, 1, 'L');
}
/*************************************************************************************/
/***************************************Suframa***************************************/
/*************************************************************************************/
if($tipo_suframa > 0) {
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
//Aki eu busco o Artigo Isenção do Orçamento ...
$sql = "SELECT ov.artigo_isencao 
        FROM `pedidos_vendas_itens` pvi 
        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_orcamento_venda_item = pvi.id_orcamento_venda_item 
        INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda 
        WHERE pvi.`id_pedido_venda` = '$_GET[id_pedido_venda]' LIMIT 1 ";    
$campos_orcamento = bancos::sql($sql);
if($campos_orcamento[0]['artigo_isencao'] == 1) {
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell(160, 5, '[*] -> SUSPENSO IPI, CONF.ART.29, PARÁGRAFO 1, ALÍNEA A E B, LEI 10637/02.', 0, 1, 'L');
}
$pdf->Ln(1);

//Aqui eu busco a observação do Follow-UP deste Pedido que possui a marcação Exibir no PDF ...
$sql = "SELECT `observacao` 
        FROM `follow_ups` 
        WHERE `identificacao` = '$_GET[id_pedido_venda]' 
        AND `origem` = '2' 
        AND `exibir_no_pdf` = 'S' LIMIT 1 ";
$campos_follow_up   = bancos::sql($sql);
if(count($campos_follow_up) == 1) {
    $pdf->SetFillColor(0, 0, 0);//Cor Cinza
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetDrawColor(255, 255, 255);
    $pdf->Cell($GLOBALS['ph']*15.5, 5, 'JUSTIFICATIVA: ', 1, 0, 'L', 1);
    $pdf->SetFont('Arial', '', 11);
    //Somente para observações longas
    //Controle para fazer corretamente a divisão de sílabica
    if(strlen($campos_follow_up[0]['observacao']) > 80) {
        $posicao = 85;
//1) Controle
        for($i = $posicao; $i < 100; $i++) {
            //Verifico se é igual espaço, se for significa que terminou já terminou a palavra
            if(substr($campos_follow_up[0]['observacao'], $posicao, 1) == ' ') {
                $i = 100;
            }else {//Ainda não terminou a palavra ...
                $posicao++;
            }
        }
        //Primeira Linha ...
        $pdf->Cell($GLOBALS['ph']*102, 5, substr($campos_follow_up[0]['observacao'], 0, $posicao), 1, 1, 'L', 1);

        if(strlen($campos_follow_up[0]['observacao']) > 180) {
//2) Controle
            $posicao2 = 185;
            for($i = $posicao2; $i < 200; $i++) {
                //Verifico se é igual espaço, se for significa que terminou já terminou a palavra ...
                if(substr($campos_follow_up[0]['observacao'], $posicao2, 1) == ' ') {
                    $i = 200;
                }else {//Ainda não terminou a palavra ...
                    $posicao2++;
                }
            }
            $qtde_letras_a_pegar = $posicao2 - $posicao;
            //Printa até o final que é a segunda e terceira linha ...
            $pdf->Cell($GLOBALS['ph'] * 102, 5, substr($campos_follow_up[0]['observacao'], $posicao, $qtde_letras_a_pegar), 1, 1, 'L', 1);
            $pdf->Cell($GLOBALS['ph'] * 102, 5, substr($campos_follow_up[0]['observacao'], $posicao2, 100), 1, 1, 'L', 1);
        }else {//Printa até o final, só tinha 2 linhas ...
            $pdf->Cell($GLOBALS['ph'] * 102, 5, substr($campos_follow_up[0]['observacao'], $posicao, 100), 1, 1, 'L', 1);
        }
    }else {//Observações Pequenas ...
        $pdf->Cell($GLOBALS['ph']*102, 5, $campos_follow_up[0]['observacao'], 1, 1, 'L', 1);
    }
}

/**********************************************************************/
/*************************Observação do Cliente************************/
/**********************************************************************/
/*Eu me aproveito desse desvio p/ fazer apresentar a Observação do Cliente, mas isso internamente porque, pode ser que um de nossos atendentes 
faça algum comentário que não seja legal referente ao cliente e por isso que não faço com que esse seja exibido sempre e só nesse caso ...*/
if($_GET['via'] == 1) {
    $sql = "SELECT `observacao` 
            FROM `follow_ups` 
            WHERE `id_cliente` = '$id_cliente' 
            AND `origem` = '15' LIMIT 1 ";
    $campos_follow_up = bancos::sql($sql);
    if(count($campos_follow_up) == 1) {
        $pdf->Ln(2);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell($GLOBALS['ph'] * 102, 5, 'Obs. do Cliente: '.$campos_follow_up[0]['observacao'], 0, 1, 'C');
    }
}
/**********************************************************************/

$pdf->SetTextColor(0, 0, 0);
$pdf->SetDrawColor(0, 0, 0);
//Seleção dos Itens do Pedido
$sql = "SELECT ged.`id_empresa_divisao`, ov.`artigo_isencao`, ovi.`id_produto_acabado_discriminacao`, 
        ovi.`id_representante`, ovi.`desc_cliente`, ovi.`desc_extra`, ovi.`acrescimo_extra`, ovi.`desc_sgd_icms`, 
        ovi.`comissao_new`, ovi.`comissao_extra`, ovi.`prazo_entrega`, ovi.`iva`, pvi.`id_produto_acabado`, 
        pvi.`id_funcionario`, pvi.`qtde`, pvi.`vale`, pvi.`qtde_pendente`, pvi.`preco_liq_final`, u.`sigla` 
        FROM `pedidos_vendas_itens` pvi 
        INNER JOIN `orcamentos_vendas_itens` ovi ON pvi.`id_orcamento_venda_item` = ovi.`id_orcamento_venda_item` 
        INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
        INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        WHERE pvi.`id_pedido_venda` = '$_GET[id_pedido_venda]' ORDER BY ovi.`id_orcamento_venda_item`, pa.`discriminacao` ";
$campos_itens = bancos::sql($sql);
$linhas = count($campos_itens);
//$linhas+= 60;
for($i = 0; $i < $linhas; $i++) {
    if($GLOBALS['nova_pagina'] == 'sim') {
        $GLOBALS['nova_pagina'] = 'nao';
        if($i != 0) {
            $pdf->Ln(-5);
            Heade($campos[0]['id_empresa'], '', 0.6);
        }
        rotulo($tipo_moeda);
    }
//Qtde
    /************************************************************/
    /****Tratamento com as Casas Decimais do campo Quantidade****/
    /************************************************************/
    if($campos_itens[$i]['sigla'] == 'KG') {//Essa é a única sigla que permite trabalharmos com Qtde Decimais ...
        $qtde_apresentar    = number_format($campos_itens[$i]['qtde'], 1, ',', '.');
    }else {
        $qtde_apresentar    = (integer)$campos_itens[$i]['qtde'];
    }
    /************************************************************/
    $pdf->Cell($GLOBALS['ph']*4, 5, $qtde_apresentar, 1, 0, 'C');
/*Se existir um PA Substitutivo "Gato por Lebre" (rs) ..., então eu tenho q/ mostrar a sigla (SB) para 
que este está sendo substituido por outro PA ...*/
    if($_GET['via'] == 1) {//Só irá existir esse controle na Via de Estoque ...
        $sql = "SELECT referencia 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '".$campos_itens[$i]['id_produto_acabado']."' LIMIT 1 ";
        $campos_referencia = bancos::sql($sql);
        if($campos_referencia[0]['referencia'] != 'ESP') {//Normal de Linha ...
/*Se é Normal de linha, então eu verifico se este possui o "Gato por Lebre" através do campo 
id_produto_acabado_discriminacao caso o mesmo seje diferente de Zero ...*/
            $sub = ($campos_itens[$i]['id_produto_acabado_discriminacao'] != 0) ? '(SB '.$campos_referencia[0]['referencia'].') ' : '';
        }else {
            $sub = '';
        }
    }else {//Via do Cliente
        $sub = '';
    }
//Se existir tem q fazer uma marcação nesse item com [*]
    if($campos_itens[$i]['artigo_isencao'] == 1) {
//Ref. * Discriminação
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell($GLOBALS['ph']*51, 5, $sub.intermodular::pa_discriminacao($campos_itens[$i]['id_produto_acabado'], 0, 0, 0, $campos_itens[$i]['id_produto_acabado_discriminacao'], 1).' [*] ', 1, 0, 'L');
        $pdf->SetFont('Arial', '', 6);
    }else {
        $pdf->SetFont('Arial', '', 6);
//Ref. * Discriminação
        $pdf->Cell($GLOBALS['ph']*51, 5, $sub.intermodular::pa_discriminacao($campos_itens[$i]['id_produto_acabado'], 0, 0, 0, $campos_itens[$i]['id_produto_acabado_discriminacao'], 1), 1, 0, 'L');
    }
    $pdf->SetFont('Arial', '', 7);

    $pdf->Cell($GLOBALS['ph']*11.4, 5, number_format($campos_itens[$i]['desc_cliente'], 2, ',', '.').'/'.number_format($campos_itens[$i]['desc_extra'], 2, ',', '.').'/'.number_format($campos_itens[$i]['desc_sgd_icms'], 2, ',', '.').'/'.number_format($campos_itens[$i]['acrescimo_extra'], 2, ',', '.'), 1, 0, 'C');
//Preço Unit. R$
    $pdf->Cell($GLOBALS['ph']*6, 5, number_format($campos_itens[$i]['preco_liq_final'], 2, ',', '.'), 1, 0, 'R');
//Total R$
    $pdf->Cell($GLOBALS['ph']*6.5, 5, number_format($campos_itens[$i]['preco_liq_final'] * $campos_itens[$i]['qtde'], 2, ',', '.'), 1, 0, 'R');
//IPI %
    if($campos_itens[$i]['artigo_isencao'] == 1) {//Verifica se o item corrente tem a lei de Artigo de Isenção
        $pdf->Cell($GLOBALS['ph'] * 4, 5, 'S/IPI', 1, 0, 'C');
    }else {
        /*Esse controle é de extrema importância porque em casos de "Gato por Lebre", preciso pegar 
        os impostos do Gato ...

        Ex: o cliente comprou MRH-042 "Gato", mas estamos enviando o MRT-042 "Lebre ou substituto" ...*/
        $id_produto_acabado_utilizar = (!empty($campos_itens[$i]['id_produto_acabado_discriminacao'])) ? $campos_itens[$i]['id_produto_acabado_discriminacao'] : $campos_itens[$i]['id_produto_acabado'];
    
        //Essas variáveis serão utilizadas mais abaixo ...
        $dados_produto      = intermodular::dados_impostos_pa($id_produto_acabado_utilizar, $campos[0]['id_uf'], $campos_itens[$i]['id_cliente'], $campos[0]['id_empresa'], $campos[0]['finalidade']);
        if($dados_produto['ipi'] > 0) {
            $pdf->Cell($GLOBALS['ph'] * 4, 5, number_format($dados_produto['ipi'], 2, ',', '.'), 1, 0, 'C');
        }else {
            $pdf->Cell($GLOBALS['ph'] * 4, 5, 'S/IPI', 1, 0, 'C');
        }
    }
//Representante
    $sql = "SELECT nome_fantasia 
            FROM `representantes` 
            WHERE `id_representante` = '".$campos_itens[$i]['id_representante']."' LIMIT 1 ";
    $campos_rep = bancos::sql($sql);
    if($campos_itens[$i]['id_representante'] == 71 && $campos_itens[$i]['id_funcionario'] > 0) {//Se o Grupo for PME, listo qual o funcionário do Grupo que fez a Venda ... 
        $sql = "SELECT SUBSTRING_INDEX(UPPER(nome), ' ', 1) AS nome 
                FROM `funcionarios` 
                WHERE `id_funcionario` = '".$campos_itens[$i]['id_funcionario']."' LIMIT 1 ";
        $campos_func = bancos::sql($sql);
        $funcionario = ' - '.$campos_func[0]['nome'];
        $pdf->SetFont('Arial', '', 4.2);
    }
    $pdf->Cell($GLOBALS['ph']*8.5, 5, $campos_rep[0]['nome_fantasia'].$funcionario, 1, 0, 'C');
    $pdf->SetFont('Arial', '', 7);
//Prazo de Entrega
    $vetor_prazos_entrega   = vendas::prazos_entrega();

    foreach($vetor_prazos_entrega as $indice => $prazo_entrega) {
//Compara o valor do Banco com o valor do Vetor
        if($campos_itens[$i]['prazo_entrega'] == $indice) $pdf->Cell($GLOBALS['ph'] * 6, 5, $prazo_entrega, 1, 0, 'C');
    }
//Comissão ...
    $pdf->Cell($GLOBALS['ph']*3, 5, $campos_itens[$i]['comissao_new'] + $campos_itens[$i]['comissao_extra'], 1, 0, 'C');
//Qtde Separada ...
    $qtde_separada = $campos_itens[$i]['qtde'] - $campos_itens[$i]['vale'] - $campos_itens[$i]['qtde_pendente'];
    if($qtde_separada == '' || $qtde_separada == 0) $qtde_separada = '0';
    $pdf->Cell($GLOBALS['ph']*3, 5, $qtde_separada, 1, 1, 'C');
}
/***************************************************************************************/
$calculo_total_impostos = calculos::calculo_impostos(0, $_GET['id_pedido_venda'], 'PV');

$pdf->SetFont('Arial', 'B', 7);
$pdf->Cell($GLOBALS['ph']*26, 5, '* VALOR DO ICMS SUBSTITUIÇÃO: '.$tipo_moeda.number_format($calculo_total_impostos['valor_icms_st'], 2, ',', '.'), 'TBL', 0, 'L');
$pdf->Cell($GLOBALS['ph']*21, 5, '* VALOR TOTAL DO IPI: '.$tipo_moeda.number_format($calculo_total_impostos['valor_ipi'], 2, ',', '.'), 'TB', 0, 'L');
$pdf->Cell($GLOBALS['ph']*29.6, 5, '* VALOR TOTAL DOS PRODUTOS: '.$tipo_moeda.number_format($calculo_total_impostos['valor_total_produtos'], 2, ',', '.'), 'TB', 0, 'L');
$pdf->SetFont('Arial', 'B', 8.7);
$pdf->SetFillColor(200, 200, 200);//Cor Cinza
$pdf->Cell($GLOBALS['ph']*26.8, 5, '* TOTAL DO PEDIDO: '.$tipo_moeda.number_format($calculo_total_impostos['valor_icms_st'] + $calculo_total_impostos['valor_ipi'] + $calculo_total_impostos['valor_total_produtos'], 2, ',', '.'), 'TBR', 1, 'L', 1);

$pdf->Ln(3);

$pdf->SetFont('Arial', '', 8);
$pdf->Cell($GLOBALS['ph']*102, 5, 'Em pedidos especiais observar: Quantidade de 10% para mais/menos; Pedido e desenho assinado pelo cliente, pois não aceitamos o cancelamento.', 0, 1, 'L');
$pdf->Ln(4);

//Esses vetores vão me auxiliar mais abaixo ...
$vetor_emp_valor_venda 		= array();
$vetor_emp_valor_a_faturar 	= array();
$vetor_emp_valor_vale 		= array();
$id_empresa_atual = 0;
//Busca todos Itens de Pedidos q estão Pendentes Parcial / Total e que possuem Pendências p/ este Cliente ...
$sql = "SELECT pv.id_empresa, pvi.preco_liq_final, pvi.qtde, pvi.vale, pvi.qtde_pendente, pvi.qtde_faturada 
        FROM pedidos_vendas pv 
        INNER JOIN pedidos_vendas_itens pvi ON pvi.id_pedido_venda = pv.id_pedido_venda AND pvi.status < '2' 
        WHERE pv.id_cliente = '$id_cliente' 
        ORDER BY pv.id_empresa ";
$campos_pedido = bancos::sql($sql);
$linhas_pedido = count($campos_pedido);
for ($i = 0;  $i < $linhas_pedido; $i++) {
//Verifico se a Empresa Corrente que está sendo listada, é diferente da Atual do Loop ...
    if($id_empresa_atual != $campos_pedido[$i]['id_empresa']) {
        if($i != 0) {//Irá incrementar essa variável a partir da 2ª Empresa ...
            $vetor_emp_valor_venda[$id_empresa_atual] 		= $total_emp_valor_venda;
            $vetor_emp_valor_a_faturar[$id_empresa_atual] 	= $total_emp_valor_a_faturar;
            $vetor_emp_valor_vale[$id_empresa_atual] 		= $total_emp_valor_vale;
        }
//Zera os valores p/ não misturar com o Valor da outra Empresa ...
        $id_empresa_atual               = $campos_pedido[$i]['id_empresa'];//Recebe a Empresa Atual ...
        $total_emp_valor_venda 		= 0;
        $total_emp_valor_a_faturar 	= 0;
        $total_emp_valor_vale 		= 0;
    }
    //Só irá entrar nesse cálculo, quando existir pendência feita pelo próprio Rivaldo ...
    if($campos_pedido[$i]['qtde_pendente'] > 0) {
        $total_emp_valor_venda+= $campos_pedido[$i]['preco_liq_final'] * ($campos_pedido[$i]['qtde'] - $campos_pedido[$i]['qtde_faturada']);
    }else {
        $total_emp_valor_a_faturar+= $campos_pedido[$i]['preco_liq_final'] * ($campos_pedido[$i]['qtde'] - $campos_pedido[$i]['qtde_faturada']);
    }
    //Só irá entrar nesse cálculo, quando existir vale ...
    if($campos_pedido[$i]['vale'] > 0) {
        $total_emp_valor_vale+= $campos_pedido[$i]['preco_liq_final'] * ($campos_pedido[$i]['vale'] - $campos_pedido[$i]['qtde_faturada']);
    }
}
//Aqui eu guardo na variável o valor Total da última Empresa ...
$vetor_emp_valor_venda[$id_empresa_atual]       = $total_emp_valor_venda;
$vetor_emp_valor_a_faturar[$id_empresa_atual] 	= $total_emp_valor_a_faturar;
$vetor_emp_valor_vale[$id_empresa_atual]        = $total_emp_valor_vale;
$pdf->SetLeftMargin(16);

//Situação do Cliente ...
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell($GLOBALS['ph'] * 17, 4, '', 1, 0, 'C', 1);
$pdf->Cell($GLOBALS['ph'] * 17, 4, 'ALBA', 1, 0, 'C', 1);
$pdf->Cell($GLOBALS['ph'] * 17, 4, 'TOOL', 1, 0, 'C', 1);
$pdf->Cell($GLOBALS['ph'] * 17, 4, 'GRUPO', 1, 0, 'C', 1);
$pdf->Cell($GLOBALS['ph'] * 17, 4, 'TOTAL', 1, 1, 'C', 1);

$pdf->Cell($GLOBALS['ph'] * 17, 4, 'PENDÊNCIA', 1, 0, 'l');
$pdf->SetFont('Arial', '', 7);
$pdf->Cell($GLOBALS['ph'] * 17, 4, $tipo_moeda.number_format($vetor_emp_valor_venda[1], 2, ',', '.'), 1, 0, 'R');
$pdf->Cell($GLOBALS['ph'] * 17, 4, $tipo_moeda.number_format($vetor_emp_valor_venda[2], 2, ',', '.'), 1, 0, 'R');
$pdf->Cell($GLOBALS['ph'] * 17, 4, $tipo_moeda.number_format($vetor_emp_valor_venda[4], 2, ',', '.'), 1, 0, 'R');
$pdf->Cell($GLOBALS['ph'] * 17, 4, $tipo_moeda.number_format($vetor_emp_valor_venda[1] + $vetor_emp_valor_venda[2] + $vetor_emp_valor_venda[4], 2, ',', '.'), 1, 1, 'R');

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell($GLOBALS['ph'] * 17, 4, 'À FATURAR', 1, 0, 'l');
$pdf->SetFont('Arial', '', 7);
$pdf->Cell($GLOBALS['ph'] * 17, 4, $tipo_moeda.number_format($vetor_emp_valor_a_faturar[1], 2, ',', '.'), 1, 0, 'R');
$pdf->Cell($GLOBALS['ph'] * 17, 4, $tipo_moeda.number_format($vetor_emp_valor_a_faturar[2], 2, ',', '.'), 1, 0, 'R');
$pdf->Cell($GLOBALS['ph'] * 17, 4, $tipo_moeda.number_format($vetor_emp_valor_a_faturar[4], 2, ',', '.'), 1, 0, 'R');
$pdf->Cell($GLOBALS['ph'] * 17, 4, $tipo_moeda.number_format($vetor_emp_valor_a_faturar[1] + $vetor_emp_valor_a_faturar[2] + $vetor_emp_valor_a_faturar[4], 2, ',', '.'), 1, 1, 'R');

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell($GLOBALS['ph'] * 17, 4, 'VALE', 1, 0, 'l');
$pdf->SetFont('Arial', '', 7);
$pdf->Cell($GLOBALS['ph'] * 17, 4, $tipo_moeda.number_format($vetor_emp_valor_vale[1], 2, ',', '.'), 1, 0, 'R');
$pdf->Cell($GLOBALS['ph'] * 17, 4, $tipo_moeda.number_format($vetor_emp_valor_vale[2], 2, ',', '.'), 1, 0, 'R');
$pdf->Cell($GLOBALS['ph'] * 17, 4, $tipo_moeda.number_format($vetor_emp_valor_vale[4], 2, ',', '.'), 1, 0, 'R');
$pdf->Cell($GLOBALS['ph'] * 17, 4, $tipo_moeda.number_format($vetor_emp_valor_vale[1] + $vetor_emp_valor_vale[2] + $vetor_emp_valor_vale[4], 2, ',', '.'), 1, 1, 'R');

//Esses vetor vai me auxiliar mais abaixo ...
$vetor_emp_faturamento = array();
//Zero a variável p/ não dar conflito com a variável de Cima ...
$id_empresa_atual = 0;
//Busca tudo o que foi vendido p/ o Cliente no decorrer do ano Corrente por empresa ...
$sql = "SELECT nfs.id_empresa, nfsi.qtde, nfsi.valor_unitario 
        FROM `nfs` 
        INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf 
        WHERE nfs.`id_cliente` = '$id_cliente' 
        AND SUBSTRING(nfs.`data_emissao`, 1, 4) = '".date('Y')."' ORDER BY nfs.id_empresa ";
$campos_faturamento = bancos::sql($sql);
$linhas_faturamento = count($campos_faturamento);
for ($i = 0;  $i < $linhas_faturamento; $i++) {
//Verifico se a Empresa Corrente que está sendo listada, é diferente da Atual do Loop ...
    if($id_empresa_atual != $campos_faturamento[$i]['id_empresa']) {
        if($i != 0) {//Irá incrementar essa variável a partir da 2ª Empresa ...
            $vetor_emp_faturamento[$id_empresa_atual] = $total_valor_faturamento;
        }
//Zera os valores p/ não misturar com o Valor da outra Empresa ...
        $id_empresa_atual = $campos_faturamento[$i]['id_empresa'];//Recebe a Empresa Atual ...
        $total_valor_faturamento = 0;
    }
    $total_valor_faturamento+= $campos_faturamento[$i]['qtde'] * $campos_faturamento[$i]['valor_unitario'];
}
//Aqui eu guardo na variável o valor Total da última Empresa ...
$vetor_emp_faturamento[$id_empresa_atual] = $total_valor_faturamento;

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell($GLOBALS['ph'] * 17, 4, 'VENDA '.date('Y'), 1, 0, 'l');
$pdf->SetFont('Arial', '', 7);
$pdf->Cell($GLOBALS['ph'] * 17, 4, $tipo_moeda.number_format($vetor_emp_faturamento[1], 2, ',', '.'), 1, 0, 'R');
$pdf->Cell($GLOBALS['ph'] * 17, 4, $tipo_moeda.number_format($vetor_emp_faturamento[2], 2, ',', '.'), 1, 0, 'R');
$pdf->Cell($GLOBALS['ph'] * 17, 4, $tipo_moeda.number_format($vetor_emp_faturamento[4], 2, ',', '.'), 1, 0, 'R');
$pdf->Cell($GLOBALS['ph'] * 17, 4, $tipo_moeda.number_format($vetor_emp_faturamento[1] + $vetor_emp_faturamento[2] + $vetor_emp_faturamento[4], 2, ',', '.'), 1, 1, 'R');
$pdf->SetLeftMargin(1);
$pdf->Ln(4);

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell($GLOBALS['ph']*22, 4, '', 0, 0, 'R');
$pdf->Cell($GLOBALS['ph']*3, 4, '', 1, 0, 'l');
$pdf->Cell($GLOBALS['ph']*0.5, 4, '', 0, 0, 'R');
$pdf->Cell($GLOBALS['ph']*20, 4, 'Atendimento Interno', 1, 0, 'l');
$pdf->Cell($GLOBALS['ph']*5, 4, '', 0, 0, 'R');
$pdf->Cell($GLOBALS['ph']*3, 4, '', 1, 0, 'l');
$pdf->Cell($GLOBALS['ph']*0.5, 4, '', 0, 0, 'R');
$pdf->Cell($GLOBALS['ph']*20, 4, 'Vendedor', 1, 1, 'l');
$pdf->SetLeftMargin(1);
$pdf->Ln(4);


$pdf->SetFont('Arial', '', 9);
$pdf->Cell($GLOBALS['ph']*20, 5, 'GERÊNCIA:', 1, 0, 'L');
$pdf->Cell($GLOBALS['ph']*20, 5, 'CUSTO:', 1, 0, 'L');
$pdf->Cell($GLOBALS['ph']*20, 5, 'ESTOQUISTA:', 1, 0, 'L');
$pdf->Cell($GLOBALS['ph']*20, 5, 'PRODUÇÃO ESP:', 1, 0, 'L');
$pdf->Cell($GLOBALS['ph']*21, 5, 'VENDAS:', 1, 1, 'L');

/******************************Pedido Expresso******************************/	
if($campos[0]['expresso'] == 'S') {
    $pdf->Ln(10);
    $pdf->SetLeftMargin(25);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell($GLOBALS['ph']*90, 5, 'COMANDA (ETAPAS DO PEDIDO EXPRESSO)', 1, 1, 'C', 1);
    $pdf->Cell($GLOBALS['ph']*15, 5, 'ETAPAS', 1, 0, 'C', 1);
    $pdf->Cell($GLOBALS['ph']*12.5, 5, 'HORA', 1, 0, 'C', 1);
    $pdf->Cell($GLOBALS['ph']*12.5, 5, 'VISTO', 1, 0, 'C', 1);
    $pdf->Cell($GLOBALS['ph']*50, 5, 'OBSERVAÇÃO', 1, 1, 'C', 1);

    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell($GLOBALS['ph']*15, 8, 'Nishimura', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*12.5, 8, '', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*12.5, 8, '', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*50, 8, '', 1, 1, 'L');

    $pdf->Cell($GLOBALS['ph']*15, 8, 'Wilson', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*12.5, 8, '', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*12.5, 8, '', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*50, 8, '', 1, 1, 'L');

    $pdf->Cell($GLOBALS['ph']*15, 8, 'Agueda', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*12.5, 8, '', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*12.5, 8, '', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*50, 8, '', 1, 1, 'L');

    $pdf->Cell($GLOBALS['ph']*15, 8, 'Giacosa', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*12.5, 8, '', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*12.5, 8, '', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*50, 8, '', 1, 1, 'L');

    $pdf->Cell($GLOBALS['ph']*15, 8, 'Sandra (GNRE)', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*12.5, 8, '', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*12.5, 8, '', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*50, 8, '', 1, 1, 'L');

    $pdf->Cell($GLOBALS['ph']*15, 8, 'Agueda', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*12.5, 8, '', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*12.5, 8, '', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*50, 8, '', 1, 1, 'L');

    $pdf->Cell($GLOBALS['ph']*15, 8, 'Yamaoka', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*12.5, 8, '', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*12.5, 8, '', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*50, 8, '', 1, 1, 'L');

    $pdf->Cell($GLOBALS['ph']*15, 8, 'Vendedor', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*12.5, 8, '', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*12.5, 8, '', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*50, 8, '', 1, 1, 'L');
}
/***************************************************************************/
chdir('../../../../../pdf');
$file='../../../../../pdf/'.basename(tempnam(getcwd()), '').'Pedido de Venda Grupo Albafer_'.$_GET[id_pedido_venda].'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>