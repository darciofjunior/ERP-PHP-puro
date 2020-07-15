<?
require('../../../../../../lib/pdf/fpdf.php');
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/calculos.php');
require('../../../../../../lib/compras_new.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/genericas.php');
require('../../../../../../lib/variaveis/compras.php');

error_reporting(1);

function Heade($condicao) {
    global $pdf, $id_nfe, $moeda;
//Busca de Dados do Cabeçalho da Nota Fiscal de Entrada
    $sql = "SELECT nfe.*, f.razaosocial, f.fone1, f.fax, p.vendedor 
            FROM `nfe` 
            INNER JOIN `fornecedores` f ON f.id_fornecedor = nfe.id_fornecedor 
            INNER JOIN `nfe_historicos` nfeh ON nfeh.id_nfe = nfe.id_nfe 
            INNER JOIN `pedidos` p ON p.id_pedido = nfeh.id_pedido 
            WHERE nfe.`id_nfe` = '$id_nfe' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $num_nota       = $campos[0]['num_nota'];
    $vendedor       = $campos[0]['vendedor'];
    $telefone       = $campos[0]['fone1'];
    $fax            = $campos[0]['fax'];
    $razao_social   = $campos[0]['razaosocial'];

//Tratamento para o Tipo de Nota
    $tipo = ($campos[0]['tipo'] == 1) ?  'NF' : 'SGD';
//Tratamento para a Empresa
    if($campos[0]['id_empresa'] == 1) {
        $empresa = 'ALBAFER';
    }else if($campos[0]['id_empresa'] == 2) {
        $empresa = 'TOOL MASTER';
    }else if($campos[0]['id_empresa'] == 4) {
        $empresa = 'GRUPO';
    }
    $data_emissao = data::datetodata(substr($campos[0]['data_emissao'], 0, 10), '/');
/***************/
    $pdf->SetLeftMargin(10);
    $pdf->Image('../../../../../../imagem/logosistema.jpg', 15, 10, 40, 40, 'JPG');
    $pdf->SetFont('Arial', 'B',8);
    $pdf->Cell(45, 5,'', 0, 0, 'L');

    $pdf->Cell(110, 5, 'CONTROLE DE COMPRA: '.$empresa.' ('.$tipo.')', 0, 0);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(20, 5, 'VIA FINANCEIRO', 0, 1);
    $pdf->Rect(161, 10, 3, 3);
//Fornecedor
    $pdf->Cell(45, 5, '', 0, 0, 'L');
    $pdf->Cell(23, 5, 'FORNECEDOR:', 0);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(87, 5, $razao_social, 0, 0);
    $pdf->Rect(161, 15, 3, 3);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(20, 5, 'VIA COMPRAS', 0, 1);

//Data da Entrega
    $pdf->Cell(45, 5, '', 0, 0, 'L');

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(10, 5, 'FONE:', 0);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(35, 5, $telefone, 0, 0);

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(10, 5, 'FAX:', 0);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(30, 5, $fax, 0, 0);

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(20, 5, 'VENDEDOR:', 0);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(30, 5, $vendedor, 0, 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 5, 'Página: '.$GLOBALS['num_pagina'], 0, 1);
    $pdf->SetFont('Arial', '', 8);

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(45, 5, '', 0, 0, 'L');
    $pdf->Cell(35, 5, 'DATA DA EMISSÃO:', 0);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(80, 5, $data_emissao, 0, 0);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(30, 5, 'CONFERÊNCIA N.º '. $num_nota, 0, 1);

    if($condicao == true) {
/****************************************************************************************************/
/*************************************** Financiamento  *********************************************/
/****************************************************************************************************/
//Aqui eu busco todas as Parcelas do Financiamento que foi feitas p/ a NF ...
        $sql = "SELECT nf.*, tm.simbolo 
                FROM `nfe_financiamentos` nf 
                INNER JOIN `nfe` ON nfe.id_nfe = nf.id_nfe 
                INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = nfe.id_tipo_moeda 
                WHERE nf.`id_nfe` = '$id_nfe' ORDER BY nf.dias ";
        $campos_financiamento = bancos::sql($sql);
        $linhas_financiamento = count($campos_financiamento);
        if($linhas_financiamento > 0) {//Foi feito pelo modo financiamento ...
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(225, 5, 'PRAZO(S) DE FATURAMENTO ', 0, 1, 'C');
//Disparo do Loop ...
            for($i = 0; $i < $linhas_financiamento; $i++) {
                $pdf->Cell(45, 5, '', 0, 0, 'L');
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell(40, 5, 'Parcela N.º '.($i + 1).' -> ', 0);
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(40, 5, 'Dias: '.$campos_financiamento[$i]['dias'], 0, 0);
                $pdf->Cell(40, 5, 'Data: '.data::datetodata($campos_financiamento[$i]['data'], '/'), 0, 0);
                $pdf->Cell(40, 5, 'Valor: '.$campos_financiamento[$i]['simbolo'].': '.number_format($campos_financiamento[$i]['valor_parcela_nf'], 2, ',', '.'), 0, 1);
            }
//Busca das Antecipações que estão atreladas a essa Nota Fiscal
            $sql = "SELECT a.*, p.desc_ddl 
                    FROM `nfe_antecipacoes` nfea 
                    INNER JOIN `antecipacoes` a ON a.id_antecipacao = nfea.id_antecipacao 
                    INNER JOIN `pedidos` p ON p.id_pedido = a.id_pedido 
                    WHERE nfea.`id_nfe` = '$id_nfe' ";
            $campos_antecipacao = bancos::sql($sql);
            for($j = 0; $j < count($campos_antecipacao); $j++) {
                $pdf->Cell(45, 5, '', 0, 0, 'L');
                $pdf->Cell(80, 5, 'ANTECIPAÇÃO N.º '.($j + 1), 0, 0, 'L');
                $pdf->Cell(40, 5, 'DATA: '.data::datetodata($campos_antecipacao[$j]['data'], '/'), 0, 0, 'L');
                $pdf->Cell(45, 5, 'VALOR: '.$moeda.number_format($campos_antecipacao[$j]['valor'], 2, ',', '.'), 0, 1, 'L');
            }
        }else {//Modo Normal, modo antigo ...
//Prazos da Nota
//Parte A
            $prazo_a = $campos[0]['prazo_a'];
            if($prazo_a == 0) {
                $prazo_a = 'À Vista';
                $retorno_a = $data_emissao;
            }else {
                $retorno_a = data::adicionar_data_hora(data::datetodata(substr($campos[0]['data_emissao'], 0, 10), '/'), $prazo_a);
                $prazo_a.= ' - DDL';
            }
            $valor_a = $moeda.number_format($campos[0]['valor_a'], 2, ',', '.');
//Parte B
            $prazo_b = $campos[0]['prazo_b'];
            if($prazo_b == 0) {
                    $prazo_b = '';
            }else {
                $retorno_b = data::adicionar_data_hora(data::datetodata(substr($campos[0]['data_emissao'], 0, 10), '/'), $prazo_b);
                $prazo_b.= ' - DDL';
            }
            $valor_b = ($campos[0]['valor_b'] == '0.00') ? '' : $moeda.number_format($campos[0]['valor_b'], 2, ',', '.');
//Parte C
            $prazo_c = $campos[0]['prazo_c'];
            if($prazo_c == 0) {
                $prazo_c = '';
            }else {
                $retorno_c = data::adicionar_data_hora(data::datetodata(substr($campos[0]['data_emissao'], 0, 10), '/'), $prazo_c);
                $prazo_c = $prazo_c.' - DDL';
            }
            $valor_c = ($campos[0]['valor_c'] == '0.00') ? '' : $moeda.number_format($campos[0]['valor_c'], 2, ',', '.');
            $pdf->SetFont('Arial', '', 8);
//*********************** Prazo Faturamento ***********************
            $pdf->Cell(45, 5, '', 0, 0, 'L');
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(35, 5, 'PRAZO FATURAMENTO:', 0, 0);
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(30, 5, '', 0, 0);
//*********************** Vencimento ***********************
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(40, 5, 'VENCIMENTO:', 0, 0);
//*********************** Valor ***********************
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(15, 5, 'VALOR:', 0, 1);
//Parte A
            $pdf->Cell(45, 5, '', 0, 0, 'L');
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(5, 5, "'A' ", 0);
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(60, 5, $prazo_a, 0, 0);
            $pdf->Cell(40, 5, $retorno_a, 0, 0);
            $pdf->Cell(30, 5, $valor_a, 0, 0);
            $pdf->Cell(40, 5, '', 0, 1);
//Parte B
            $pdf->Cell(45, 5, '', 0, 0, 'L');
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(5, 5, "'B' ", 0);
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(60, 5, $prazo_b, 0, 0);
            $pdf->Cell(40, 5, $retorno_b, 0, 0);
            $pdf->Cell(30, 5, $valor_b, 0, 0);
            $pdf->Cell(40, 5, '', 0, 1);
//Parte C
            $pdf->Cell(45, 5, '', 0, 0, 'L');
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(5, 5, "'C'", 0);
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(60, 5, $prazo_c, 0, 0);
            $pdf->Cell(40, 5, $retorno_c, 0, 0);
            $pdf->Cell(30, 5, $valor_c, 0, 0);
            $pdf->Cell(40, 5, '', 0, 1);
//Busca das Antecipações que estão atreladas a essa Nota Fiscal
            $sql = "SELECT a.*, p.desc_ddl 
                    FROM `nfe_antecipacoes` nfea 
                    INNER JOIN `antecipacoes` a ON a.id_antecipacao = nfea.id_antecipacao 
                    INNER JOIN `pedidos` p ON p.id_pedido = a.id_pedido 
                    WHERE nfea.`id_nfe` = '$id_nfe' ";
            $campos_antecipacao = bancos::sql($sql);
            $linhas_antecipacao = count($campos_antecipacao);
            for($j = 0; $j < $linhas_antecipacao; $j++) {
                $pdf->Cell(45, 5, '', 0, 0, 'L');
                $pdf->Cell(65, 5, 'ANTECIPAÇÃO N.º '.($j + 1), 0, 0, 'L');
                $pdf->Cell(40, 5, 'DATA: '.data::datetodata($campos_antecipacao[$j]['data'], '/'), 0, 0, 'L');
                $pdf->Cell(45, 5, 'VALOR: '.$moeda.number_format($campos_antecipacao[$j]['valor'], 2, ',', '.'), 0, 1, 'L');
            }
        }
        $pdf->SetLeftMargin(1);
        $condicao = false;
    }else {
        $pdf->Cell(1, 5, '', 0, 1);
        $pdf->Cell(1, 5, '', 0, 1);
        $pdf->Cell(1, 5, '', 0, 1);
        $pdf->Cell(1, 5, '', 0, 1);
        $pdf->SetLeftMargin(1);
    }
}

function tabela() {
    global $pdf;
    $pdf->Cell(12, 5, 'QTD', 1, 0, 'C');
    $pdf->Cell(6, 5, 'UN', 1, 0, 'C');
    $pdf->Cell(108, 5, 'PRODUTO', 1, 0, 'C');
    $pdf->Cell(15, 5, 'PÇO UNIT', 1, 0, 'C');
    $pdf->Cell(18, 5, 'VAL TOTAL', 1, 0, 'C');
    $pdf->Cell(28, 5, 'MARCA / OBS', 1, 0, 'C');
    $pdf->Cell(18, 5, 'N.º PED / OS', 1, 1, 'C');
}

function rodape() {
    global $pdf, $id_nfe, $moeda;
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(150, 5, 'TOTAL', 'TBL', 0, 'R');
    $pdf->Cell(20, 5, $moeda, 'TB', 0, 'R');
    
    $calculo_total_impostos = calculos::calculo_impostos(0, $id_nfe, 'NFC');
    $pdf->Cell(35, 5, number_format($calculo_total_impostos['valor_total_nota'], 2, ',', '.'), 'BTR', 1, 'L');

    $pdf->Cell(205, 5, 'DADOS PARA DEPÓSITO', 1, 1, 'L');
    $sql = "SELECT `id_fornecedor_propriedade` 
            FROM `nfe` 
            WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
    $campos                     = bancos::sql($sql);
    $id_fornecedor_propriedade 	= $campos[0]['id_fornecedor_propriedade'];

    if($id_fornecedor_propriedade > 0) {
        $sql = "SELECT banco, agencia, num_cc, correntista 
                FROM `fornecedores_propriedades` 
                WHERE `id_fornecedor_propriedade` = '$id_fornecedor_propriedade' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {
            $bank               = (!empty($campos[0]['banco'])) ? $campos[0]['banco'] : '';
            $agencia 		= (!empty($campos[0]['agencia'])) ? $campos[0]['agencia'] : '';
            $conta_corrente     = (!empty($campos[0]['num_cc'])) ? $campos[0]['num_cc'] : '';
            $correntista 	= (!empty($campos[0]['correntista'])) ? $campos[0]['correntista'] : '';
        }
    }
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(15, 5, 'BANCO - ', 'LTB', 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(50, 5, $bank, 'TB', 0, 'L');

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(17, 5, 'AGÊNCIA - ', 'LTB', 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(30, 5, $agencia, 'TB', 0, 'L');

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(15, 5, ' N C/C -', 'LTB', 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(78, 5, $conta_corrente, 'TBR', 1, 'L');

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(25, 5, 'CORRENTISTA', 'LTB', 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(180, 5, $correntista, 'TBR', 1, 'L');
    
    //Aqui eu busco a observação do Follow-UP desta NFe que possui a marcação Exibir no PDF ...
    $sql = "SELECT `observacao` 
            FROM `follow_ups` 
            WHERE `identificacao` = '$_GET[id_nfe]' 
            AND `origem` = '17' 
            AND `exibir_no_pdf` = 'S' LIMIT 1 ";
    $campos_follow_up   = bancos::sql($sql);
    if(count($campos_follow_up) == 1) {
        $pdf->Cell(205, 5, 'OBS: '.substr($campos_follow_up[0]['observacao'], 0, 140), 'TLR', 1, 'L');
        $pdf->Cell(205, 5, substr($campos_follow_up[0]['observacao'], 140, 160), 'LR', 1, 'L');
        $pdf->Cell(205, 5, substr($campos_follow_up[0]['observacao'], 300, 200), 'BLR', 1, 'L');
    }

    $pdf->Cell(14, 5, 'VISTOS', 'TLR', 0, 'L');
    $pdf->Cell(46, 5, 'EMISSOR', 'TLR', 0, 'L');
    $pdf->Cell(48, 5, 'CONFERENTE', 'TLR', 0, 'L');
    $pdf->Cell(48, 5, 'GERÊNCIA', 'TLR', 0, 'L');
    $pdf->Cell(49, 5, 'DIRETORIA', 'TLR', 1, 'L');
    $pdf->Cell(14, 5, '', 'BLR', 0, 'L');
    $pdf->Cell(46, 5, '', 'BLR', 0, 'L');
    $pdf->Cell(48, 5, '', 'BLR', 0, 'L');
    $pdf->Cell(48, 5, '', 'BLR', 0, 'L');
    $pdf->Cell(49, 5, '', 'BLR', 1, 'L');
}

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

//Busca o id_pais do fornecedor
$sql = "SELECT IF(f.razaosocial = '', UPPER(f.nomefantasia), UPPER(f.razaosocial)) AS fornecedor, f.id_pais, 
        nfe.num_nota, nfe.tipo, nfe.pago_pelo_caixa_compras, CONCAT(tm.simbolo, ' ') AS moeda 
        FROM `nfe` 
        INNER JOIN `fornecedores` f ON f.id_fornecedor = nfe.id_fornecedor 
        INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = nfe.id_tipo_moeda 
        WHERE nfe.`id_nfe` = '$_GET[id_nfe]' LIMIT 1 ";
$campos                 = bancos::sql($sql);
$moeda                  = $campos[0]['moeda'];
$fornecedor             = $campos[0]['fornecedor'];
$id_pais                = $campos[0]['id_pais'];
$num_nota               = $campos[0]['num_nota'];
$tipo                   = ($campos[0]['tipo'] == 1) ? 'NF' : 'SGD';
$pago_pelo_caixa_compras= $campos[0]['pago_pelo_caixa_compras'];

$condicao = true;
Heade($condicao);
$pdf->SetLeftMargin(1);

//Busca dos Item(ns) da Nota Fiscal de Entrada de Compras
$sql = "SELECT nfeh.* 
        FROM `nfe` 
        INNER JOIN `nfe_historicos` nfeh ON nfeh.id_nfe = nfe.id_nfe 
        WHERE nfeh.`id_nfe` = '$_GET[id_nfe]' ";
$campos = bancos::sql($sql);
$linhas = count($campos);

if($linhas > 0) {
    for($i = 0; $i < $linhas; $i++) {
//Aqui eu tenho que limpar as variáveis p/ não printar dados de registro anterior do loop
        $apresentar = '';
        $apresentar_os = '';
        $apresentar_op = '';
/*************************************************************************************/
        if($GLOBALS['nova_pagina'] == 'sim') {
            $GLOBALS['nova_pagina'] = 'nao';
            if($i != 0) {
                $pdf->Ln(-4);
                $condicao = false;
                Heade($condicao);
            }
            $pdf->Ln(15);
            $pdf->SetFont('Arial', '', 6.5);
            tabela();
        }
        $pdf->Cell(12, 3.8, number_format($campos[$i]['qtde_entregue'], 2, ',', '.'), 1, 0, 'R');

        $id_item_pedido = $campos[$i]['id_item_pedido'];
//Busca Dados de Referência, Discriminação, Unidade do Produto ...
        $sql = "SELECT g.referencia, ip.*, pi.id_produto_insumo, pi.discriminacao, u.sigla 
                FROM `itens_pedidos` ip 
                INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = ip.id_produto_insumo 
                INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
                INNER JOIN `unidades` u ON u.id_unidade = pi.id_unidade 
                WHERE ip.`id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
        $campos_item_pedido = bancos::sql($sql);
        $pdf->Cell(6, 3.8, $campos_item_pedido[0]['sigla'], 1, 0, 'R');       
        $referencia         = $campos_item_pedido[0]['referencia'];
        $id_produto_insumo  = $campos_item_pedido[0]['id_produto_insumo'];
        $discriminacao      = $campos_item_pedido[0]['discriminacao'];
//Impressão do Tipo de Ajuste na Tela ...
        if(!empty($campos[$i]['cod_tipo_ajuste'])) {//Certifico que ele não foi deletado, Luis ..
            $codigo_ajuste = ' - '.$tipos_ajustes[$campos[$i]['cod_tipo_ajuste']][1];
//Se o Tipo de Ajuste = 'Abatimento de NF' então eu exibo o N.º da NF ...
            if($campos[$i]['cod_tipo_ajuste'] == 4) $codigo_ajuste.= ' => '.$campos[$i]['nf_obs_abatimento'];
//Se não existir ajuste, então eu limpo essa variável p/ que não fique com o valor do Loop Anterior ...
        }else {
            $codigo_ajuste = '';
        }
//Significa que é um Produto do Tipo não Estocável
        if($campos_item_pedido[0]['estocar'] == 0)          $apresentar = ' (N.E) ';
//Significa que esse Produto tem débito com Fornecedor
        if($campos_item_pedido[0]['id_fornecedor'] != 0)    $apresentar.= '(DEB) ';
/**********************OP**********************/
//Verifico se esse Item está atrelado a alguma OP ...
        $sql = "SELECT id_op 
                FROM `oss_itens` 
                WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
        $campos_op = bancos::sql($sql);
        if(count($campos_op) == 1) $apresentar_op = ' / OP N.º '.$campos_op[0]['id_op'];
/**********************************************/
        $pdf->Cell(108, 3.8, genericas::buscar_referencia($id_produto_insumo, $referencia).' * '.$discriminacao.$codigo_ajuste.$apresentar_op.$apresentar, 1, 0, 'L');
        $pdf->Cell(15, 3.8, $moeda.number_format($campos[$i]['valor_entregue'], 2, ',', '.'), 1, 0, 'R');
        $total_item = round(round($campos[$i]['qtde_entregue'] * $campos[$i]['valor_entregue'], 3), 2);
        $pdf->Cell(18, 3.8, $moeda.number_format($total_item, '2', ',', '.'), 1, 0, 'R');
//Verifico se esta OS está atrelada em algum Pedido ...
        $sql = "SELECT id_os 
                FROM `oss` 
                WHERE `id_pedido` = '".$campos[$i]['id_pedido']."' LIMIT 1 ";
        $campos_os = bancos::sql($sql);
        //Encontrou a OS em um Pedido, então eu printo o N. da OS e Ped
        if(count($campos_os) == 1) $apresentar_os = ' / '.$campos_os[0]['id_os'];
        if(!empty($campos[$i]['marca'])) {
            $pdf->Cell(28, 3.8, strip_tags($campos[$i]['marca']), 1, 0, 'L');
        }else {
            $pdf->Cell(28, 3.8, strip_tags($campos_item_pedido[0]['marca']), 1, 0, 'L');
        }
        $pdf->Cell(18, 3.8, $campos[$i]['id_pedido'].$apresentar_os, 1, 1, 'C');
    }
    //Se o usuário marcou essa opção de "Caixa" no Cabeçalho de Nota Fiscal, então apresento esta linha abaixo ...
    if($pago_pelo_caixa_compras == 'S') {
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetTextColor(255, 0, 0);//Cor Vermelha ...
        $pdf->Cell(205, 3.8, '', 'LR', 1, 'C');
        $pdf->Cell(205, 3.8, '(PAGO PELO CAIXA DE COMPRAS)', 'LR', 1, 'C');
        $pdf->SetTextColor(0, 0, 0);//Cor Preta ...
    }
//Enquanto não chegar nessa posição da página eu vou imprimindo essas linhas em branco ...
    while($pdf->GetY() < 230) $pdf->Cell(205, 5, '', 'LR', 1, 'L');
    rodape();//Área do Rodapé
}
$caracteres_invalidos 	= "ÁÉÍÓÚÃÕÂÊÎÔÛÇ°ª/'";
$caracteres_validos 	= "AEIOUAOAEIOUC____";
chdir('../../../../../../pdf');
$file='../../../../../../pdf/'.basename(tempnam(getcwd()), '').'NF_('.strtr($fornecedor, $caracteres_invalidos, $caracteres_validos).')_'.$num_nota.'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><SCRIPT>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>