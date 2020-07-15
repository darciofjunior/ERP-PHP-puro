<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
require('../../../../../lib/genericas.php');
require('../../../../../lib/data.php');

function Heade($id_empresa_pedido, $tipo_cabecalho) {
    global $pdf;

    if($id_empresa_pedido == 4) {//se id_empresa == 4 então Grupo Albafer
        if($tipo_cabecalho == 1) {//O usuário escolheu o Cabeçalho da Albafer ...
            $id_empresa_pedido = 1;
        }else {//O usuário escolheu o Cabeçalho da Tool Master ...
            $id_empresa_pedido = 2;
        }
    }
//Busca de Dados da Empresa ...
    $sql = "SELECT * 
            FROM `empresas` 
            WHERE `id_empresa` = '$id_empresa_pedido' LIMIT 1 ";
    $campos_empresa    = bancos::sql($sql);
    $razao_social   = $campos_empresa[0]['razaosocial'];
    $cnpj           = $campos_empresa[0]['cnpj'];
    $cnpj           = substr($cnpj, 0, 2).'.'.substr($cnpj, 2, 3).'.'.substr($cnpj, 5, 3).'/'.substr($cnpj, 8, 4).'-'.substr($cnpj, 12, 2);
    $ie             = $campos_empresa[0]['ie'];
    $ie             = substr($ie, 0, 3).'.'.substr($ie, 3, 3).'.'.substr($ie, 6, 3).'.'.substr($ie, 9, 3);
    $endereco       = $campos_empresa[0]['endereco'];
    $numero         = $campos_empresa[0]['numero'];
    $bairro         = strtoupper($campos_empresa[0]['bairro']);
    $cidade         = strtoupper($campos_empresa[0]['cidade']);
    $telefone_comercial = $campos_empresa[0]['telefone_comercial'];
    $cep            = $campos_empresa[0]['cep'];
    $ddd_comercial  = $campos_empresa[0]['ddd_comercial'];

    $pdf->SetLeftMargin(1);
    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell(200, 5, $razao_social.'  ## FONE/FAX: (55-'.$ddd_comercial.') '.$telefone_comercial.' # E-MAIL: compras@grupoalbafer.com.br', 0, 1, 'L');
    $pdf->Cell(200, 5, $endereco.', N.º '.$numero.' - '.$bairro.' - '.$cidade.' - CEP: '.$cep.'  ## CNPJ: '.$cnpj.' # INSC. EST. - '.$ie, 0, 1, 'L');
}

define('FPDF_FONTPATH', 'font/');
$pdf=new FPDF();
$pdf->Open();
$pdf->SetLeftMargin(1);
$pdf->AddPage();

//Aki Verifica se alguma antecipação do pedido, está atrelada a parte de contas bancárias
$sql = "SELECT fp.`banco`, fp.`agencia`, fp.`num_cc`, fp.`correntista` 
        FROM `antecipacoes` a 
        INNER JOIN `fornecedores_propriedades` fp ON fp.`id_fornecedor_propriedade` = a.`id_fornecedor_propriedade` 
        WHERE a.`id_pedido` = '$id_pedido' 
        AND a.`id_fornecedor_propriedade` > '0' ORDER BY a.`id_antecipacao` DESC LIMIT 1 ";
$campos = bancos::sql($sql);
if(count($campos) == 1) {
    $bank 		= (!empty($campos[0]['banco'])) ? $campos[0]['banco'] : '';
    $agencia 		= (!empty($campos[0]['agencia'])) ? '*AG:'.$campos[0]['agencia'] : '';
    $conta_corrente     = (!empty($campos[0]['num_cc'])) ? '*C/C:'.$campos[0]['num_cc'] : '';
    $correntista        = (!empty($campos[0]['correntista'])) ? '*'.$campos[0]['correntista'] : '';
}//Fim dos dados bancários ...

//Aqui transforma em vetor para poder realizar o loop + abaixo do código
$vetor_chkt_item_pedido = explode(',', $chkt_item_pedido);
$vetor_qtde             = explode(',', $txt_qtde);

/*******************************Seleção de alguns do Pedido*******************************/
$sql = "SELECT p.*, f.*, ufs.sigla 
        FROM `pedidos` p 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = p.`id_fornecedor` 
        LEFT JOIN `ufs` ON ufs.`id_uf` = ufs.`id_uf` 
        WHERE p.`id_pedido` = '$id_pedido' 
        AND p.`ativo` = '1' LIMIT 1 ";
$campos = bancos::sql($sql);
//Tem que renomear essa variável para que não dê conflito com a variável id_empresa que já está na Sessão ...
$id_empresa_pedido  = $campos[0]['id_empresa'];
$tipo_nota          = $campos[0]['tipo_nota'];
$tipo_nota_porc     = $campos[0]['tipo_nota_porc'];
$data               = $campos[0]['data_emissao'];
$data               = substr($data, 8, 2).'/'.substr($data, 5, 2).'/'.substr($data, 0, 4);
$fornecedor         = $campos[0]['razaosocial'];
$endereco           = $campos[0]['endereco'];
$num_complemento    = $campos[0]['num_complemento'];
$bairro             = $campos[0]['bairro'];
$cidade             = $campos[0]['cidade'];
$uf                 = $campos[0]['sigla'];
$vendedor           = $campos[0]['vendedor'];
$ddd_fone1          = $campos[0]['ddd_fone1'];
$fone1              = $campos[0]['fone1'];
if(empty($fone1))   $fone1 = '';
$ddd_fone2          = $campos[0]['ddd_fone2'];
$fone2              = $campos[0]['fone2'];
if(empty($fone2))   $fone2 = '';
$ddd_fax            = $campos[0]['ddd_fax'];
$fax                = $campos[0]['fax'];
if(empty($fax))     $fax = '';
$condicao_ddl       = $campos[0]['desc_ddl'];
$qtde_caracter      = strlen($condicao_ddl);
for($i = 0; $i < $qtde_caracter; $i++) {
    if(substr($condicao_ddl, $i, 1) != '-') {
        $condicao_ddl.= substr($condicao_ddl, $i, 1);
    }else {
        $i = $qtde_caracter;
    }
}
$condicao_ddl = trim($condicao_ddl);
/*****************************************************************************************/

//MATRIZ PARA PEGAR O NOME DAS VIAS E O NÚMERO
$vias[0] = '1º VIA - DEPTO. DE COMPRAS';
$vias[1] = '2º VIA - TRANSPORTE';

for($x = 0; $x < 2; $x++) {
    $reg = 0;
    Heade($id_empresa_pedido, $tipo_cabecalho);
//Não me lembro ao certo o que era isso, daí resolvi simplificar
    $cont_linhas+= count($vetor_chkt_item_pedido);
    $linhas = $cont_linhas;
//MONTAGEM PDF
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell(16, 5, 'PEDIDO:', 'TLB', 0, 'L');

    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell(10, 5, $id_pedido, 'TB', 0, 'L');

    $pdf->Cell(60, 5, 'REQUISIÇÃO DE MATERIAL', 'TB', 0, 'C');
    $pdf->Cell(60, 5, $data, 'TB', 0, 'C');

    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell(65, 5, $vias[$x], 'TBR', 1, 'C');

    $pdf->Cell(23, 5, 'FORNECEDOR - ', 'LT', 0, 'L');

    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell(85, 5, $fornecedor, 'T', 0, 'L');

    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell(20, 5, 'FONE(S) - ', 'T', 0, 'L');

    $pdf->SetFont('Arial', '', 7);
    if($fone2 == '' && $fax == ''){
        $fone = '('.$ddd_fone1.') '.$fone1;
    }else if($fone2 != '' && $fax == '') {
        $fone = '('.$ddd_fone1.') '.$fone1.' / ('.$ddd_fone2.') '.$fone2;
    }else {
        $fone = '('.$ddd_fone1.') '.$fone1.' / ('.$ddd_fone2.') '.$fone2.' / FAX ('.$ddd_fax.') '.$fax;
    }
    $pdf->Cell(83, 5, $fone, 'TR', 1, 'L');
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell(10, 5, 'END - ', 'L', 0, 'L');
    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell(201, 5, $endereco.', '.$num_complemento.' - '.$bairro.' - '.$cidade.' - '.$uf, 'R', 1, 'L');

    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell(12, 5, 'VEND - ', 'L', 0, 'L');

    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell(91, 5, $vendedor, 'B', 0, 'L');

    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell(18, 5, 'CONDIÇÃO - ', 'B', 0, 'L');

    $pdf->SetFont('Arial', '', 7);
/****************************************************************************************************/
/*************************************** Financiamento  *********************************************/
/****************************************************************************************************/
//Aqui eu busco todas as Parcelas do Financiamento que foi feitas p/ o Pedido ...
    $sql = "SELECT pf.*, tm.`simbolo` 
            FROM `pedidos_financiamentos` pf 
            INNER JOIN `pedidos` p ON p.`id_pedido` = pf.`id_pedido` 
            INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = p.`id_tipo_moeda` 
            WHERE pf.`id_pedido` = '$id_pedido' ORDER BY pf.`dias` ";
    $campos_financiamento = bancos::sql($sql);
    $linhas_financiamento = count($campos_financiamento);
    if($linhas_financiamento > 0) {//Se foi encontrado pelo menos 1 Financiamento ...
        for($i = 0; $i < $linhas_financiamento; $i++) {
            if($i == 0) {//Se eu estiver na Primeira parcela
                $primeira_parcela = $campos_financiamento[$i]['dias'];
            }else if($i + 1 == $linhas_financiamento) {//Última Parcela
                $ultima_parcela = $campos_financiamento[$i]['dias'];
            }
        }
        if($tipo_nota == 1) {//NF
            $exibir_nota = 'NF';
        }else {//SGD
            $exibir_nota = 'SGD';
        }
        $condicao_ddl = $linhas_financiamento.' parc. ('.$primeira_parcela.' à '.$ultima_parcela.' DDL) '.$exibir_nota.' '.$tipo_nota_porc.' %';
    }

    $pdf->Cell(90, 5, $condicao_ddl, 'BR', 1, 'L');

    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell(12, 5, 'QTD', 1, 0, 'L');
    $pdf->Cell(10, 5, 'UNID.', 1, 0, 'C');
    $pdf->Cell(91, 5, 'DISCRIMINAÇÃO', 1, 0, 'C');
    $pdf->Cell(14, 5, 'P. UNIT.', 1, 0, 'C');
    $pdf->Cell(17, 5, 'VLR TOT', 1, 0, 'C');
    $pdf->Cell(10, 5, 'IPI %', 1, 0, 'L');
    $pdf->Cell(15, 5, 'VLR. IPI', 1, 0, 'L');
    $pdf->Cell(42, 5, 'MARCA / OBS', 1, 1, 'C');
        
    $pdf->SetFont('Arial', '', 7);
//Não me lembro (rsrs)
    if($x == 0) $cont_registros = 0;
    $total_valor_ipi = 0;
    $valor_total = 0;
//Seleção somente dos Itens em que o usuário escolheu para a Requisição de Material
    for($i = 0; $i < count($vetor_chkt_item_pedido); $i++) {
        $sql = "SELECT ip.*, g.`referencia`, pi.`discriminacao`, u.`sigla` 
                FROM `itens_pedidos` ip 
                INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ip.`id_produto_insumo` 
                INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                WHERE ip.`id_item_pedido` = '$vetor_chkt_item_pedido[$i]' LIMIT 1 ";
        $campos         = bancos::sql($sql);
        $unidade        = $campos[0]['sigla'];
        $referencia     = genericas::buscar_referencia($campos[0]['id_produto_insumo'], $campos[0]['referencia'], 0);
        $discriminacao  = $campos[0]['discriminacao'];
        $preco_unitario = $campos[0]['preco_unitario'];
//Aqui eu não puxo o valor total direto do Banco de Dados, pq pode a qtde nesse caso é a requirida
//Qtde Dig. lá na Requisição de Material
        $valor_item     = round(round($preco_unitario, 3), 2) * $vetor_qtde[$i];
        $valor_total+= $valor_item;
        if(($campos[0]['ipi'] == 0) || ($tipo_nota == 2)) {
            $ipi = 0;
        }else {
            $ipi = $campos[0]['ipi'];
        }
        $valor_com_ipi  = ($valor_item * $ipi) / 100;
        $total_valor_ipi += $valor_com_ipi;
        $marca_prod     = $campos[0]['marca'];

        $pdf->Cell(12, 5, number_format($vetor_qtde[$i], 2, ',', '.'), 1, 0, 'L');
        $pdf->Cell(10, 5, $unidade, 1, 0, 'C');

        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(91, 5, $referencia.' * '.$discriminacao, 1, 0, 'L');

        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(14, 5, number_format($preco_unitario, 2, ',', '.'), 1, 0, 'R');
/*Aqui eu não puxo o valor total direto do Banco de Dados, pq pode variar a qtde
requirida*/
        $pdf->Cell(17, 5, number_format($valor_item, 2, ',', '.'), 1, 0, 'R');
        $pdf->Cell(10, 5, $ipi, 1, 0, 'R');
        $pdf->Cell(15, 5, number_format($valor_com_ipi, 2, ',', '.'), 1, 0, 'R');
        
        if(strlen($marca_prod) >= 30) $marca_prod = substr($marca_prod, 0, 30).' ...';
        $pdf->Cell(42, 5, $marca_prod, 1, 1, 'L');
        
        if($x == 0) $cont_registros++;
    }
//Verifica se o numero de registros &eacute; maior do que 7
    if($cont_registros < 7) {
        $registros_brancos = 7 - $cont_registros  ;
        for($z = 0; $z < $registros_brancos; $z++) {
            $pdf->Cell(12, 5, '', 1, 0, 'L');
            $pdf->Cell(10, 5, '', 1, 0, 'L');
            $pdf->Cell(91, 5, '', 1, 0, 'L');
            $pdf->Cell(14, 5, '', 1, 0, 'L');
            $pdf->Cell(17, 5, '', 1, 0, 'L');
            $pdf->Cell(10, 5, '', 1, 0, 'L');
            $pdf->Cell(15, 5, '', 1, 0, 'L');
            $pdf->Cell(42, 5, '', 1, 1, 'L');
        }
    }
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell(30, 5, 'DADOS BANCARIOS:', 'TBL', 0, 'L');
    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell(100, 5, $bank.$agencia.$conta_corrente.$correntista, 'TB', 0, 'L');
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell(21, 5, 'TOTAL C/ IPI = ', 'BT', 0, 'L');
    if($tipo_nota == 2) $total_valor_ipi = 0;
    if($x < 2) $total_valor_ipi += $valor_total;
    $pdf->Cell(60, 5, number_format($total_valor_ipi, 2, ',', '.'), 'RTB', 1, 'L');
    $valor_total = 0;
/*************Aqui é o Tratamento com a Observação da Requisição de Materiais**************/
    $pdf->Cell(25, 5, 'OBSERVAÇÃO:', 'TL', 0, 'L');
    $pdf->SetFont('Arial', '', 7);
    $contador_letra = strlen($obs_requisicao);
    if($contador_letra > 130) {
        $obs_requisicao1 = substr($obs_requisicao, 0, 130);
        $obs_requisicao2 = substr($obs_requisicao, 130, $contador_letra);
    }else {
        $obs_requisicao1 = $obs_requisicao;
    }
    $pdf->Cell(186, 5, $obs_requisicao1, 'TR', 1, 'L');
    $pdf->Cell(211, 5, $obs_requisicao2, 'LBR', 1, 'L');
/******************************************************************************************/
//Só zera essas variáveis quando for da Primeira para a Segunda via somente
    if($x == 0) {
        $valor_total = 0;
        $total_valor_ipi = 0;
    }
    $pdf->Ln(8);
    if($linhas >= 9 && $x < 1) $pdf->AddPage();

    $pdf->SetFont('Arial', '', 7);
}
chdir('../../../../../pdf');
$file='../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><SCRIPT>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>