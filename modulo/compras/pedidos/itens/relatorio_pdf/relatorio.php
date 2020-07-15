<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
require('../../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../../../');
error_reporting(0);

function rotulo($tipo_moeda, $txt_entrada) {//Porque chama + de uma vez por causa da paginacao
    global $pdf, $tipo_moeda;
    $pdf->SetLeftMargin(1);
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 8);
    if($txt_entrada != '') {
        $pdf->SetFont('Arial', '', 7);
        $entr = $txt_entrada + 1;
        for($y = 1;$y >= 0 ;$y--) {
            $pdf->Cell($GLOBALS['ph'] * 4, 5, $entr.' ENT', 1, 0, 'C');
            $entr--;
        }
    }else {
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell($GLOBALS['ph'] * 4, 5, '2 ENT', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 4, 5, '1 ENT', 1, 0, 'C');
    }
    $pdf->Cell($GLOBALS['ph'] * 6, 5, 'PEND', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 3.5, 5, 'UN.', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 43, 5, 'DISCRIMINAÇÃO', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 7.1, 5, 'P. UNIT. '.$tipo_moeda, 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 7.8, 5, 'VLR TOT '.$tipo_moeda, 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 3.8, 5, 'IPI%', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 24, 5, 'MARCA / OBS', 1, 1, 'C');
}

function Heade($id_empresa_pedido, $tipo_cabecalho, $ajuste) {
    $GLOBALS['ph']+=$ajuste;
    global $pdf, $id_pedido;

    if($id_empresa_pedido == 4) {//se id_empresa == 4 então Grupo Albafer
            //O usuário escolheu o Cabeçalho da Albafer ou Tool Master ...
            $id_empresa_pedido = ($tipo_cabecalho == 1) ? 1 : 2;
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
    $cep            = $campos_fone[0]['cep'];

    $pdf->Image('../../../../../imagem/logo_transparente.jpg',7,5,34,36,'JPG');

    $pdf->Cell(15*$GLOBALS['ph'], 10, '', 0, 0, 'L');
    $pdf->SetFont('Arial', 'BI', 12);
    $pdf->Cell(175, 10, '* '.$razao_social, 0, 0, 'L');
    $pdf->SetFont('Arial', 'BI', 9);
    $pdf->Ln(5);

    $pdf->Cell(15*$GLOBALS['ph'], 10, '', 0, 0, 'L');
    $pdf->Cell(115, 10, $endereco.', N.º '.$numero.' - '.$bairro.' - '.$cidade.' - CEP: '.$cep, 0, 0, 'L');

    //Foi criado esse parâmetro p/ termos 1 único arquivo apenas e facilitar na hora de Impressão.
    if($_GET['relatorio_pendencia'] == 1) {//Relatório de Pendência ...
        $pdf->SetFont('Arial', 'BIU', 11);
        $pdf->Cell(10, 10, 'Pendências Pedido N.º: '.$id_pedido, 0, 0, 'L');
    }else {//Relatório de Pedidos ...
        $pdf->SetFont('Arial', 'BIU', 13);
        $pdf->Cell(40, 10, 'Pedido N.º: '.$id_pedido, 0, 0, 'L');
    }
    $pdf->Ln(4);
    $pdf->SetFont('Arial', 'BI', 9);
    $pdf->Cell(15*$GLOBALS['ph'], 10, '', 0, 0, 'L');
    $pdf->Cell(120, 10, 'FONE/FAX: (55-'.$campos_fone[0]['ddd_comercial'].') '.$campos_fone[0]['telefone_comercial'].' # E-MAIL: compras@grupoalbafer.com.br', 0, 0, 'L');
    $pdf->Ln(4);

    $pdf->Cell(15*$GLOBALS['ph'], 10, '', 0, 0, 'L');
    $pdf->Cell(127, 10, 'CNPJ: '.$cnpj.' # INSC. EST. - '.$ie, 0, 0, 'L');
    $pdf->SetFont('Arial', 'I', 13);
    $pdf->Cell(30, 10, 'Página: '.$GLOBALS['num_pagina'], 0, 0, 'L');
    $pdf->Ln(4);

    if($id_empresa_pedido == 1) {//Se o usuário escolheu Albafér ...
        $pdf->Image('../../../../../imagem/marcas/cabri.jpg', 60, 32, 10, 10, 'JPG');
        $pdf->Image('../../../../../imagem/marcas/heinz.jpg', 80, 32, 20, 10, 'JPG');
        $pdf->Ln(4);
    }else {//Se o usuário escolheu Tool Master ...
        $pdf->Image('../../../../../imagem/marcas/tool.jpg',60,32,42,10,'JPG');
        $pdf->Image('../../../../../imagem/marcas/nvo.jpg',108,32,20,10,'JPG');
        $pdf->Ln(4);
    }
    $pdf->Ln(4);
    $pdf->Cell(100 * $GLOBALS['ph'], 10, '', 0, 1, 'L');
    $pdf->Line(1 * $GLOBALS['ph'], 43, 101.5 * $GLOBALS['ph'], 43);
    $pdf->Ln(2);
    $GLOBALS['ph']-=$ajuste;
}

/////////////////////////////////////// INÍCIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel     = 'P';  // P=> Retrato L=>Paisagem
$unidade        = 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel  = 'A4'; // A3, A4, A5, Letter, Legal, Ofício ...
$pdf            = new FPDF($tipo_papel, $unidade, $formato_papel);
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

//Aqui já deixa carregado o valor do desconto especial porc do Pedido
$sql = "SELECT `desconto_especial_porc` 
        FROM `pedidos` 
        WHERE `id_pedido` = '$_GET[id_pedido]' LIMIT 1 ";
$campos                 = bancos::sql($sql);
$desconto_especial_porc = $campos[0]['desconto_especial_porc'];

//Esta parte está fora do head só é utilizada na primeira página
//Busca dos dados do Cliente
$sql = "SELECT p.*, f.*, paises.`pais`, ufs.`sigla` 
        FROM `pedidos` p 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = p.`id_fornecedor` 
        INNER JOIN `paises` ON paises.`id_pais` = f.`id_pais` 
        LEFT JOIN `ufs` ON ufs.`id_uf` = f.`id_uf` 
        WHERE p.`id_pedido` = '$_GET[id_pedido]' LIMIT 1 ";
$campos             = bancos::sql($sql);
$id_empresa_pedido  = $campos[0]['id_empresa'];
$id_fornecedor      = $campos[0]['id_fornecedor'];
$data_emissao       = data::datetodata(substr($campos[0]['data_emissao'], 0, 10), '/').' - '.substr($campos[0]['data_emissao'], 11, 8);
$tipo_nota          = $campos[0]['tipo_nota'];
$exibir_nota        = ($tipo_nota == 1) ? 'NF' : 'SGD';
$tipo_export        = $campos[0]['tipo_export'];
$tipo_nota_porc     = $campos[0]['tipo_nota_porc'];

if(!empty($campos[0]['cnpj_cpf'])) {//Campo está preenchido ...
    if(strlen($campos[0]['cnpj_cpf']) == 11) {//CPF ...
        $cnpj_cpf = substr($campos[0]['cnpj_cpf'], 0, 3).'.'.substr($campos[0]['cnpj_cpf'], 3, 3).'.'.substr($campos[0]['cnpj_cpf'], 6, 3).'-'.substr($campos[0]['cnpj_cpf'], 9, 2);
    }else {//CNPJ ...
        $cnpj_cpf = substr($campos[0]['cnpj_cpf'], 0, 2).'.'.substr($campos[0]['cnpj_cpf'], 2, 3).'.'.substr($campos[0]['cnpj_cpf'], 5, 3).'/'.substr($campos[0]['cnpj_cpf'], 8, 4).'-'.substr($campos[0]['cnpj_cpf'], 12, 2);
    }
}

$id_pais            = $campos[0]['id_pais'];
$tp_moeda           = $campos[0]['tp_moeda'];

if($tp_moeda == 1) {
    $tipo_moeda = 'R$ ';
}else if($tp_moeda == 2) {
    $tipo_moeda = 'U$ ';
}else {
    $tipo_moeda = '€ ';
}

$sql = "SELECT `nome` 
        FROM `funcionarios` 
        WHERE `id_funcionario` = '".$campos[0]['id_funcionario_cotado']."' LIMIT 1 ";
$campos_nome = bancos::sql($sql);
if(count($campos_nome) == 1) $cotador = $campos_nome[0]['nome'];

Heade($id_empresa_pedido, $tipo_cabecalho);
$pdf->SetFont('Arial', '', 10);
$pdf->SetLeftMargin(1);

$pdf->Ln(8);
$pdf->SetLeftMargin(1);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(22, 5, 'FORNECEDOR: ', 0, 0, 'L');
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(104, 5, $campos[0]['razaosocial'], 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(15, 5, 'EMISSÃO:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(40, 5, $data_emissao, 0, 1, 'L');
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(18, 5, 'ENDEREÇO: ', 0, 0, 'L');
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(108, 5, $campos[0]['endereco'].', '.$campos[0]['num_complemento'], 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(13, 5, 'BAIRRO:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(40, 5, $campos[0]['bairro'], 0, 1, 'L');
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(31, 5, 'CEP/CIDADE/UF/PAÍS:', 0, 0, 'L');
$pdf->SetFont('Arial','',8);
$pdf->Cell(95, 5, $campos[0]['cep'].' / '.$campos[0]['cidade'].' - '.$campos[0]['sigla'].' / '.$campos[0]['pais'], 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(18, 5, 'CONDIÇÃO:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 12);

/****************************************************************************************************/
/*************************************** Financiamento  *********************************************/
/****************************************************************************************************/
//Aqui eu busco todas as Parcelas do Financiamento que foi feitas p/ o Pedido ...
$sql = "SELECT pf.*, tm.simbolo 
        FROM `pedidos_financiamentos` pf 
        INNER JOIN `pedidos` p ON p.id_pedido = pf.id_pedido 
        INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = p.id_tipo_moeda 
        WHERE pf.`id_pedido` = '$_GET[id_pedido]' ORDER BY pf.dias asc ";
$campos_financiamento = bancos::sql($sql);
$linhas_financiamento = count($campos_financiamento);
if($linhas_financiamento > 0) {//Se foi encontrado pelo menos 1 Financiamento ...
    if($linhas_financiamento <= 6) {
        for($i = 0; $i < $linhas_financiamento; $i++) $condicao_ddl.= $campos_financiamento[$i]['dias'].'/';
        $condicao_ddl = '('.substr($condicao_ddl, 0, strlen($condicao_ddl) - 1).' DDL) ';
        $condicao_ddl.= ' '.$exibir_nota.' '.$tipo_nota_porc.' %';
    }else {//Se eu tiver mais que 6 parcelas então eu só exibo a 1ª e a última Parcela ...
        for($i = 0; $i < $linhas_financiamento; $i++) {
            if($i == 0) {//Se eu estiver na Primeira parcela
                $primeira_parcela = $campos_financiamento[$i]['dias'];
            }else if($i + 1 == $linhas_financiamento) {//Última Parcela
                $ultima_parcela = $campos_financiamento[$i]['dias'];
            }
        }
        $condicao_ddl = $linhas_financiamento.' parc. ('.$primeira_parcela.' à '.$ultima_parcela.' DDL) '.$exibir_nota.' '.$tipo_nota_porc.' %';
    }
}

if($tipo_export == 'E') {
    $condicao_ddl.=' - EXP';
}else if($tipo_export == 'I') {
    $condicao_ddl.= ' - IMP';
}else if($tipo_export == 'N') {
    $condicao_ddl.= ' - NAC';
}

$pdf->SetFont('Arial', '', 8);
$pdf->Cell(40, 5, $condicao_ddl, 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(15.5, 5, 'CNPJ / CPF:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(110.8, 5, $cnpj_cpf, 0, 0, 'L');

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(17, 5, 'INSC. EST.:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(40, 5, $campos[0]['insc_est'], 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(22, 5, 'VENDEDOR(A):', 0, 0, 'L');
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(104, 5, $campos[0]['vendedor'], 0, 0, 'L');

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(25, 5, 'COMPRADOR(A): ', 0, 0, 'L');
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(40, 5, $cotador, 0, 1, 'L');

if($campos[0]['fone2'] == '' && $campos[0]['fax'] == '') {
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(14, 5, 'FONE(S): ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(112.2, 5, '('.$campos[0]['ddd_fone1'].') '.$campos[0]['fone1'], 0, 0, 'L');
}else if($campos[0]['fone2'] == '' && $campos[0]['fax'] != '') {
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(14, 5, 'FONE(S): ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(112.2, 5, '('.$campos[0]['fone1'].') / FAX: ('.$campos[0]['ddd_fax'].') '.$campos[0]['fax'], 0, 0, 'L');
}else if($campos[0]['fax'] == '' && $campos[0]['fone2'] != '') {
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(14, 5, 'FONE(S): ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(112.2, 5, '('.$campos[0]['ddd_fone1'].') '.$campos[0]['fone1'].' / ('.$campos[0]['ddd_fone2'].') '.$campos[0]['fone2'], 0, 0, 'L');
}else {
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(14, 5, 'FONE(S): ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(112.2, 5, '('.$campos[0]['ddd_fone1'].') '.$campos[0]['fone1'].' / ('.$campos[0]['ddd_fone2'].') '.$campos[0]['fone2'].'/ FAX: ('.$campos[0]['ddd_fax'].') '.$campos[0]['fax'], 0, 0, 'L');
}

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(13, 5, 'E-MAIL: ', 0, 0, 'L');
$pdf->SetFont('Arial', 'U', 8);
$pdf->Cell(40, 5, $campos[0]['email'], 0, 1, 'L', '', 'mailto:'.$campos[0]['email'].'?subject=E-Mail Albafer (Pedido)&body=Albafer');
$pdf->SetFont('Arial', '', 8);

$mensagem = ($id_pais == 31) ? 'ENTREGA' : 'EMBARQUE';

if($id_pais == 31) {//País Nacional - Brasil
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(31, 5, 'PRAZO DE '.$mensagem.': ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(95.2, 5, data::datetodata($campos[0]['prazo_entrega'], '/'), 0, 1, 'L');
}else {//País Internacional
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(34, 5, 'PRAZO DE '.$mensagem.': ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(92.2, 5, data::datetodata($campos[0]['prazo_entrega'], '/'), 0, 0, 'L');

//Busca do nome da importação
    $sql = "SELECT i.nome 
            FROM `importacoes` i 
            INNER JOIN `pedidos` p ON p.id_importacao = i.id_importacao 
            WHERE p.id_pedido = '$_GET[id_pedido]' ";
    $campos = bancos::sql($sql);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(22, 5, 'IMPORTAÇÃO:', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(40, 5, $campos[0]['nome'], 0, 1, 'L');
}

//Seleção dos dados bancários do Fornecedor
$sql = "SELECT fp.banco, fp.agencia, fp.num_cc, fp.correntista 
        FROM `pedidos` p 
        INNER JOIN `fornecedores_propriedades` fp ON fp.id_fornecedor = p.id_fornecedor 
        WHERE p.`id_pedido` = '$_GET[id_pedido]' 
        AND p.`ativo` = '1' LIMIT 1 ";
$campos = bancos::sql($sql);
if(count($campos) == 1) {
    $bank           = (empty($campos[0]['banco'])) ? '' : $campos[0]['banco'];
    $agencia        = (empty($campos[0]['agencia'])) ? '' : '*AG:'.$campos[0]['agencia'];
    $num_cc         = (empty($campos[0]['num_cc'])) ? '' : '*C/C:'.$campos[0]['num_cc'];
    $correntista    = (empty($campos[0]['correntista'])) ? '' : '*'.$campos[0]['correntista'];
}
//Fim dos dados bancários

//Se existir desconto especial no Pedido, então eu printo essa linha no Relatório ...
if($desconto_especial_porc != 0) {
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(70, 5, 'DESCONTO ESPECIAL P/ ESTE PEDIDO:', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(50, 5, number_format($desconto_especial_porc, 2, ',', '.').' %', 0, 1, 'L');
}

//Foi criado esse parâmetro p/ termos 1 único arquivo apenas e facilitar na hora de Impressão ... //Relatório de Pendência ...
if($_GET['relatorio_pendencia'] == 1) $condicao_em_aberto = " AND ip.`status` < '2' ";//Busca Somente dos Item(ns) do Pedido q estão em aberto ...

//Busca dos Item(ns) do Pedido
$sql = "SELECT ip.*, g.`referencia`, pi.`id_produto_insumo`, pi.`discriminacao`, u.`sigla` 
        FROM `itens_pedidos` ip 
        INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ip.`id_produto_insumo` 
        INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
        INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
        WHERE ip.`id_pedido` = '$id_pedido' 
        $condicao_em_aberto ORDER BY ip.`id_item_pedido` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
$aco_trefilado = 0;

if($linhas > 0) {//Se existir pelo menos 1 Item que ainda está em aberto deste Pedido, disparo o loop ...
    for($i = 0; $i < $linhas; $i++) {
        if($GLOBALS['nova_pagina'] == 'sim') {
            $GLOBALS['nova_pagina'] = 'nao';
            if($i != 0) {
                $pdf->Ln(-5);
                Heade($id_empresa_pedido, $tipo_cabecalho, 0.6);
            }
            rotulo($tipo_moeda, $txt_entrada);
        }
        $pdf->SetFont('Arial', '', 7);
//Foi criado esse parâmetro p/ termos 1 único arquivo apenas e facilitar na hora de Impressão.
        if($_GET['relatorio_pendencia'] == 1) {//Relatório de Pendência ...
            //Verifico em Nota Fiscal, a Qtde Entregue do Item de Pedido Corrente ...
            $sql = "SELECT SUM(nh.qtde_entregue) AS total_entregue 
                    FROM `itens_pedidos` ip 
                    INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` 
                    INNER JOIN `nfe_historicos` nh ON nh.`id_item_pedido` = ip.`id_item_pedido` 
                    WHERE ip.`id_item_pedido` = '".$campos[$i]['id_item_pedido']."' ";
            $campos_entregue    = bancos::sql($sql);
            $total_entregue     = $campos_entregue[0]['total_entregue'];
//Nessa variável eu verifico o quanto que ainda resta para Entregar daquele Item ...
            $total_restante     = $campos[$i]['qtde'] - $total_entregue;
//Aqui eu tenho que limpar a variável p/ não printar dados de registro anterior do loop
            $apresentar         = '';
/*************************************************************************************/
            $total_qtde+= $total_restante;
            $unidade_insumo = $campos[$i]['sigla'];
            $referencia = $campos[$i]['referencia'];
            $discriminacao =  $campos[$i]['discriminacao'];
            $id_produto_insumo = $campos[$i]['id_produto_insumo'];
            $contador = strlen($discriminacao);
//Essa variável equivale a multiplicação da qtde pelo preço
            $valor_linha = $total_restante * $campos[$i]['preco_unitario'];
            $valor_total+= $valor_linha;
            $marca_prod = $campos[$i]['marca'];
//Fim do calculo Restante
            if($campos[$i]['status'] == 2) $total_restante = '';
            if($achou_aco != 1) {
                $sql = "SELECT `id_produto_insumo_vs_aco` 
                        FROM `produtos_insumos_vs_acos` 
                        WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
                $campos_aco = bancos::sql($sql);
                if(count($campos_aco) == 1) $achou_aco = 1;
            }
            
            if($campos[$i]['ipi_incluso'] == 'S') {
                $ipi_exibir = '(Incl)';
            }else {
                if(($campos[$i]['ipi'] == 0) || ($tipo_nota == 2)) {//SGD
                    $ipi_exibir = '';
                }else {//NF
                    $ipi_exibir 	= ($campos[$i]['ipi'] == 0) ? '' : $campos[$i]['ipi'];
                    $valor_com_ipi 	= ($valor_linha * $campos[$i]['ipi']) / 100;
                    $total_valor_com_ipi+= $valor_com_ipi;
                }
            }
            
            $total_restante = ($total_restante == 0 || $total_restante == 0.00) ? '' : number_format($total_restante, 2, ',', '.');
            $pdf->Cell($GLOBALS['ph'] * 4, 5, '', 1, 0, 'L');
            $pdf->Cell($GLOBALS['ph'] * 4, 5, '', 1, 0, 'L');
            $pdf->Cell($GLOBALS['ph'] * 5, 5, $total_restante, 1, 0, 'R');
        }else {
//Aqui eu tenho que limpar a variável p/ não printar dados de registro anterior do loop
            $apresentar = '';
/*************************************************************************************/
            $total_qtde+= $campos[$i]['qtde'];
            $unidade_insumo 	= $campos[$i]['sigla'];
            $referencia         = $campos[$i]['referencia'];
            $discriminacao      =  $campos[$i]['discriminacao'];
            $id_produto_insumo 	= $campos[$i]['id_produto_insumo'];
            $contador           = strlen($discriminacao);
            //Essa variável equivale a multiplicação da qtde pelo preço
            $valor_linha        = $campos[$i]['qtde'] * $campos[$i]['preco_unitario'];
            $valor_total+= $valor_linha;
            $marca_prod         = $campos[$i]['marca'];
            if($achou_aco != 1) {
                $sql = "SELECT `id_produto_insumo_vs_aco` 
                        FROM `produtos_insumos_vs_acos` 
                        WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
                $campos_aco = bancos::sql($sql);
                if(count($campos_aco) == 1) $achou_aco = 1;
            }
            
            if($campos[$i]['ipi_incluso'] == 'S') {
                $ipi_exibir = '(Incl)';
            }else {
                if(($campos[$i]['ipi'] == 0) || ($tipo_nota == 2)) {//SGD
                    $ipi_exibir = '';
                }else {//NF
                    $ipi_exibir 	= ($campos[$i]['ipi'] == 0) ? '' : $campos[$i]['ipi'];
                    $valor_com_ipi 	= ($valor_linha * $campos[$i]['ipi']) / 100;
                    $total_valor_com_ipi+= $valor_com_ipi;
                }
            }
            
            $pdf->Cell($GLOBALS['ph'] * 4, 5, '', 1, 0, 'L');
            $pdf->Cell($GLOBALS['ph'] * 4, 5, '', 1, 0, 'L');
            $pdf->Cell($GLOBALS['ph'] * 6, 5, number_format($campos[$i]['qtde'], 2, ',', '.'), 1, 0, 'R');
        }
/****************************************************************************************/
/****Aqui é igual independente do Tipo de Relatório de Pedido que está sendo impresso****/
/****************************************************************************************/
        $pdf->Cell($GLOBALS['ph'] * 3.5, 5, $unidade_insumo, 1, 0, 'L');
//Significa que é um Produto do Tipo não Estocável
        if($campos[$i]['estocar'] == 0) $apresentar = ' (N.E) ';
//Significa que esse Produto tem débito com Fornecedor
        if($campos[$i]['id_fornecedor'] != 0) $apresentar.= '(DEB) ';
        $referencia = genericas::buscar_referencia($id_produto_insumo, $referencia);
/*Aqui eu verifico qual é o Tipo de Produto que estou utilizando, isso porque se for aço 1020 ou 1045
tenho que estar apresentando uma mensagem a mais no fim do Pedido*/
        $discriminacao_corrent = strtok(strchr($campos[$i]['discriminacao'], 'TREFILADO'), 'I');
        if($referencia == 'ACO' && $discriminacao_corrent == 'TREF') $aco_trefilado++;
        
        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell($GLOBALS['ph'] * 43, 5, $referencia.' * '.$campos[$i]['discriminacao'].$apresentar, 1, 0, 'L');
        
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell($GLOBALS['ph'] * 7.1, 5, number_format($campos[$i]['preco_unitario'], 2, ',', '.'), 1, 0, 'R');
        $pdf->Cell($GLOBALS['ph'] * 7.8, 5, number_format($valor_linha, 2, ',', '.'), 1, 0, 'R');
        $pdf->Cell($GLOBALS['ph'] * 3.8, 5, $ipi_exibir, 1, 0, 'R');
        if(strlen($marca_prod) >= 42) $marca_prod = substr($marca_prod, 0, 42).' ...';
        $pdf->Cell($GLOBALS['ph'] * 24, 5, $marca_prod, 1, 1, 'L');
    }
//Alguns linhas em branco ...
    for($i = 0; $i < 3; $i++) $pdf->Cell($GLOBALS['ph'] * 103.2, 5, '', 'RL', 1, 'C');
    $pdf->Cell(211.6, 5, ' ', 1, 1, 'C');

/*Significa que achou algum aço 1020 ou 1045 no Pedido, sendo assim tenho que exibir essa mensagem de 
alerta para que não seje cortada errada as barras ...*/
    if($aco_trefilado > 0) {
        $pdf->SetFillColor(0, 0, 0);//Fundo Preto
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetTextColor(255, 255, 255);//Cor Branca ...
        $pdf->MultiCell(211.6, 5, ' * NÃO RECEBEMOS BARRAS FORA DO COMPRIMENTO ESPECIFICADO (2,80 À 3,10 MTS) * ', 1, 'C', 1);
        $pdf->SetTextColor(0, 0, 0);//Cor Preta ...
    }

    if($achou_aco == 1) {
        $pdf->SetFillColor(0, 0, 0);//Fundo Preto
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetTextColor(255, 255, 255);//Cor Branca ...
        $pdf->MultiCell(211.6, 5, ' * NÃO RECEBEMOS BARRAS C/ DIAM > 69,85 mm E PESO > 150 KGs. NESTES CASOS, CORTAR AO MEIO. ACEITAMOS ATÉ +/- 5% DA QTDE PEDIDA. * ', 1, 'C', 1);
        $pdf->SetTextColor(0, 0, 0);//Cor Preta ...
    }

    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(120, 5, 'QTD KGS P/ CALCULO TOTAL/FRETE ->', 'TBL', 0, 'R');
    $pdf->Cell(20, 5, number_format($total_qtde, 2, ',', '.'), 'TBR', 0, 'R');
    $pdf->Cell(71.6, 5, '/kG - TOT', 'TBR', 1, 'C');
    $pdf->Cell(110, 5, 'DADOS BANCÁRIOS  '.$bank.' '.$agencia.' '.$num_cc, 1, 0, 'L');
    $pdf->Cell(20, 5, 'TOTAL S/ IPI', 1, 0, 'L');
    $pdf->Cell(30, 5, $tipo_moeda.number_format($valor_total, '2', ',', '.'), 'TLB', 0, 'L');
    $pdf->Cell(10, 5, 'C/ IPI', 1, 0, 'L');
    if($tipo_nota == 2) {
        $total_valor_com_ipi = '';
    }else {
        $total_valor_com_ipi+= $valor_total;
        $total_valor_com_ipi = $tipo_moeda.number_format($total_valor_com_ipi, '2', ',', '.');
    }
    $pdf->Cell(41.6, 5, $total_valor_com_ipi, 1, 1, 'L');
    $pdf->SetFont('Arial', 'B', 13);
    $pdf->Cell(35, 5, 'OBSERVAÇÃO: ', 'TL', 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    
    //Aqui eu busco a observação do Follow-UP deste Pedido que possui a marcação Exibir no PDF ...
    $sql = "SELECT `observacao` 
            FROM `follow_ups` 
            WHERE `identificacao` = '$_GET[id_pedido]' 
            AND `origem` = '16' 
            AND `exibir_no_pdf` = 'S' LIMIT 1 ";
    $campos_follow_up   = bancos::sql($sql);
    if(count($campos_follow_up) == 1) {
        $pdf->Cell(176.6, 5, substr($campos_follow_up[0]['observacao'], 0, 100), 'TR', 1, 'L');
        $pdf->Cell(211.6, 5, substr($campos_follow_up[0]['observacao'], 100, 110), 'LR', 1, 'L');
        $pdf->Cell(211.6, 5, substr($campos_follow_up[0]['observacao'], 210, 110),'BLR', 1, 'L');
    }
///////////////////////////////////////////////////////////////////////////////
    $pdf->SetFont('Arial', '', 8);
    if($txt_entrada == '') {
        $pdf->Cell(15, 5, '1 ENT', 'TLR', 0, 'C');
        $pdf->Cell(20.3, 5, 'DATA', 1, 0, 'C');
        $pdf->Cell(25.4, 5, '', 'BLR', 0, 'L');
        $pdf->Cell(16, 5, 'VISTO', 1, 0, 'C');
        $pdf->Cell(34.5, 5, '', 'BLR', 0, 'L');
        $pdf->Cell(15, 5, '2 ENT', 'TLR', 0, 'C');
        $pdf->Cell(20.3, 5, 'DATA', 1, 0, 'C');
        $pdf->Cell(27.4, 5, '', 'BLR', 0, 'L');
        $pdf->Cell(16, 5, 'VISTO', 1, 0, 'C');
    }else {
        $pdf->Cell(15, 5, $txt_entrada.' ENT', 'TLR', 0, 'C');
        $pdf->Cell(20.3, 5, 'DATA', 1, 0, 'C');
        $pdf->Cell(27.4, 5, '', 'BLR', 0, 'L');
        $pdf->Cell(16, 5, 'VISTO', 1, 0, 'C');
        $pdf->Cell(27.1, 5, '', 'BLR', 0, 'L');
        $txt_entrada++;
        $pdf->Cell(15, 5, $txt_entrada.' ENT', 'TLR', 0, 'C');
        $pdf->Cell(20.3, 5, 'DATA', 1, 0, 'C');
        $pdf->Cell(27.4, 5, '', 'BLR', 0, 'L');
        $pdf->Cell(16, 5, 'VISTO', 1, 0, 'C');
    }
    $pdf->Cell(21.7, 5, '', 1, 1, 'C');
    $pdf->Cell(15, 5, 'VISTOS', 'TLR', 0, 'L');
    $pdf->Cell(45.7, 5, 'EMISSOR', 'TLR', 0, 'L');
    $pdf->Cell(50.4, 5, 'CONFERENTE', 'TLR', 0, 'L');
    $pdf->Cell(49.5, 5, 'GERÊNCIA', 'TLR', 0, 'L');
    $pdf->Cell(51, 5, 'DIRETORIA', 'TLR', 1, 'L');
    $pdf->Cell(15, 5, '', 'BLR', 0, 'L');
    $pdf->Cell(45.7, 5, '', 'BLR', 0, 'L');
    $pdf->Cell(50.4, 5, '', 'BLR', 0, 'L');
    $pdf->Cell(49.5, 5, '', 'BLR', 0, 'L');
    $pdf->Cell(51, 5, '', 'BLR', 1, 'L');
}
chdir('../../../../../pdf');
$file='../../../../../pdf/'.basename(tempnam(getcwd()), '').'Pedido_de_Compras_Grupo_Albafer_'.$id_pedido.'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>