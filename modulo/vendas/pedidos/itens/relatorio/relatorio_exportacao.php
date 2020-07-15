<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
require('../../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/vendas/pedidos/itens/consultar.php', '../../../../../');

//PDF normal ...
error_reporting(0);
function rotulo($tipo_moeda) {//Porque chama mais de uma vez por causa da paginacao
    global $pdf, $tipo_moeda;
    $pdf->SetLeftMargin(20);
    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell($GLOBALS['ph'] * 6, 5, 'Cantidad', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 8, 5, 'Referencia', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 48.5, 5, 'Artículo', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 8.5, 5, 'Precio '.$tipo_moeda, 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 10, 5, 'Total '.$tipo_moeda, 1, 1, 'C');
}

function Heade($id_empresa_pedido) {
    global $pdf;
    //Trago dados de acordo com o Tipo de Faturamento que esta no cadastro do Cliente ...
    $sql = "SELECT * 
            FROM `empresas` 
            WHERE `id_empresa` = '$id_empresa_pedido' LIMIT 1 ";
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
    $pdf->Ln(18);
    $pdf->Line(1 * $GLOBALS['ph'], 43, 101.5 * $GLOBALS['ph'], 43);
    $pdf->Ln(6);
}

/////////////////////////////////////// INÍCIO PDF ///////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel     = 'P';  // P=> Retrato L=>Paisagem
$unidade        = 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= 'A4'; // A3, A4, A5, Letter, Legal
$pdf            = new FPDF($tipo_papel, $unidade, $formato_papel);
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
$sql = "SELECT pv.*, c.*, cc.`nome` AS contato 
        FROM `pedidos_vendas` pv 
        INNER JOIN `clientes_contatos` cc ON cc.`id_cliente_contato` = pv.`id_cliente_contato` 
        INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
        WHERE pv.`id_pedido_venda` = '$_GET[id_pedido_venda]' LIMIT 1 ";
$campos = bancos::sql($sql);
if($campos[0]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[0]['vencimento4'];
if($campos[0]['vencimento3'] > 0) $prazo_faturamento = '/'.$campos[0]['vencimento3'].$prazo_faturamento;
if($campos[0]['vencimento2'] > 0) {
    $prazo_faturamento = $campos[0]['vencimento1'].'/'.$campos[0]['vencimento2'].$prazo_faturamento;
}else {
    if($campos[0]['id_cliente'] == 2328) {//Se o Cliente = 'Herramar' ...
        $prazo_faturamento = 'Anticipado';
    }else {
        $prazo_faturamento = ($campos[0]['vencimento1'] == 0) ? 'Al contado' : $campos[0]['vencimento1'].' DD ';
    }
}

$id_contacorrente   = $campos[0]['id_contacorrente'];
$id_pais            = $campos[0]['id_pais'];
$faturar_em         = data::datetodata($campos[0]['faturar_em'], '/');
$data_emissao       = data::datetodata($campos[0]['data_emissao'], '/');
$fecha              = data::datetodata($campos[0]['fecha'], '/');
$bairro             = $campos[0]['bairro'];
$endereco           = $campos[0]['endereco'];
$num_complemento    = $campos[0]['num_complemento'];
$email              = $campos[0]['email'];
$cep                = $campos[0]['cep'];
$cidade             = $campos[0]['cidade'];
$tipo_faturamento   = $campos[0]['tipo_faturamento'];

$sql = "SELECT `pais` 
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

Heade($campos[0]['id_empresa']);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(38, 5, 'Factura Comercial N.º ', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(71, 5, $_GET['id_pedido_venda'], 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(15, 5, 'Fecha: ', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(75, 5, $fecha, 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(22, 5, 'Comprador: ', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(147, 5, $campos[0]['razaosocial'], 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(17, 5, 'Dirección:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 8);

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
$pdf->Cell(100, 5, $telefone_com.' / Fax: '.$telefone_fax, 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(15, 5, 'E-mail: ', 0, 0, 'L');
$pdf->SetFont('Arial', 'U', 10);
$pdf->Cell(40, 5, $email, 0, 1, 'L', '', 'mailto:'.$email.'?subject=E-Mail Albafer (Pedido)&body=Albafer');

//Se tem pelo menos 1 item, trago o N.º do Orçamento que gerou esse Pedido ...
$sql = "SELECT ovi.`id_orcamento_venda` 
        FROM `pedidos_vendas_itens` pvi 
        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
        WHERE pvi.`id_pedido_venda` = '$_GET[id_pedido_venda]' LIMIT 1 ";
$campos_itens_orc   = bancos::sql($sql);
$id_orcamento_venda = $campos_itens_orc[0]['id_orcamento_venda'];

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(26, 5, 'Consignatário: ', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(70, 5, $campos[0]['consignatario'], 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(11, 5, 'Pago: ', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(45, 5, $prazo_faturamento, 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 5, 'Embarque: ', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);

if($campos[0]['embarque'] == 'A') {//Aéreo ...
    $embarque       = 'Aéreo';
    $rotulo_total   = 'TOTAL FCA';
}else if($campos[0]['embarque'] == 'M') {//Marítimo ...
    $embarque       = 'Marítimo';
    $rotulo_total   = 'TOTAL FOB';
}else if($campos[0]['embarque'] == 'R') {//Rodoviário ...
    $embarque       = 'Rodoviário';
    $rotulo_total   = 'TOTAL FCA';
}
$pdf->Cell(50, 5, $embarque, 0, 1, 'L');

//Verifico se temos algum item desse Pedido vinculado à algum Packing List ...
$sql = "SELECT pl.`qtde_caixas`, pl.`peso_bruto`, pl.`peso_liquido`, pl.`volume` 
        FROM `pedidos_vendas_itens` pvi 
        INNER JOIN `packings_lists_itens` pli ON pli.`id_pedido_venda_item` = pvi.`id_pedido_venda_item` 
        INNER JOIN `packings_lists` pl ON pl.`id_packing_list` = pli.`id_packing_list` 
        WHERE pvi.`id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
$campos_packing_list = bancos::sql($sql);
$linhas_packing_list = count($campos_packing_list);
if($linhas_packing_list > 0) {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(14, 5, 'Bultos: ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(58, 5, $campos_packing_list[0]['qtde_caixas'].' caja(s) de madera compensada', 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(22, 5, 'Peso Bruto: ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(22, 5, number_format($campos_packing_list[0]['peso_bruto'], 3, ',', '.').' kg', 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 5, 'Peso Neto: ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(21, 5, number_format($campos_packing_list[0]['peso_liquido'], 3, ',', '.').' kg', 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(17, 5, 'Volumén: ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(29, 5, number_format($campos_packing_list[0]['volume'], 4, ',', '.').' m3', 0, 1, 'L');
}

if(!empty($campos[0]['marcas'])) {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(15, 5, 'Marcas: ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(90, 5, $campos[0]['marcas'], 0, 1, 'L');
}

if(!empty($campos[0]['informe_importacion'])) {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(37, 5, 'Informe Importación: ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(90, 5, $campos[0]['informe_importacion'], 0, 1, 'L');
}

//Só quando o Cliente for da Argentina que mostraremos essa Informação Fecha Prevista de Embarque, do contrário Nunca ...
/*if($campos[0]['id_pais'] == 12) {//País = 'Argentina' ...
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(50, 5, 'Fecha Prevista de Embarque: ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(15, 5, $faturar_em, 0, 1, 'L');
}else {//Outro País ...
    $pdf->Cell(65, 5, '', 0, 1, 'L');
}*/

/*Só quando o Cliente for da Argentina que mostraremos essa Informação Origen del Material, 
do contrário Nunca ... - a partir do dia 16/06/2015 ...*/
if($campos[0]['id_pais'] == 12) {//País = 'Argentina' ...
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(35, 5, 'Origen del Material: ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(30, 5, 'Hecho en Brasil', 0, 1, 'L');
}else {//Outro País ...
    $pdf->Cell(65, 5, '', 0, 1, 'L');
}
rotulo($tipo_moeda);

//Seleção dos Itens do Pedido ...
$sql = "SELECT ovi.`id_produto_acabado_discriminacao`, pvi.`id_produto_acabado`, 
        pvi.`qtde`, pvi.`preco_liq_final` 
        FROM `pedidos_vendas_itens` pvi 
        INNER JOIN `orcamentos_vendas_itens` ovi ON pvi.`id_orcamento_venda_item` = ovi.`id_orcamento_venda_item` 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` /*UTILIZO ESSA TABELA APENAS POR CAUSA DA ORDENAÇÃO*/
        WHERE pvi.`id_pedido_venda` = '$_GET[id_pedido_venda]' ORDER BY pa.`discriminacao` ";
$campos_itens   = bancos::sql($sql);
$linhas_itens   = count($campos_itens);

/******************************************************************************/
//Essas variáveis p/ controles de Quebra de Página, serão utilizadas mais abaixo ...
$indice_impresso            = 0;
$pagina                     = 1;//Página Corrente ...
//Definição da variável "$qtde_registros_por_pagina" para a Primeira Página apenas ...
$qtde_registros_por_pagina  = ($linhas_itens > 37) ? 37 : 32;
/******************************************************************************/

for($i = 0; $i < $linhas_itens; $i++) {
    /*Toda vez que a variável "$indice_impresso" se igualar a variável "$qtde_registros_por_pagina", 
    então gero uma Nova Página ...*/
    if($indice_impresso != 0 && ($indice_impresso % $qtde_registros_por_pagina == 0)) {
        $pdf->AddPage();
        Heade($campos[0]['id_empresa']);
        rotulo($tipo_moeda);
        $indice_impresso = 0;
        $pagina++;
    }
//A partir da Segunda Página posso ter mais Itens ...
    if($pagina > 1) $qtde_registros_por_pagina = 50;//Por não ter o Pré-Cabeçalho posso colocar mais itens ...
//Qtde
    $pdf->Cell($GLOBALS['ph'] * 6, 5, number_format($campos_itens[$i]['qtde'], 0, ',', '.'), 1, 0, 'C');
    
    if($campos_itens[$i]['id_produto_acabado_discriminacao'] > 0) {//Existe um PA Substitutivo "Gato por Lebre" (rs)
        /*Trago a Ref e Discriminação do PA que o Cliente "comprou", "o Cliente tem que enxergar isso " apesar 
        de estar sendo enviado um outro Produto ...*/
        $sql = "SELECT `referencia`, `discriminacao` 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '".$campos_itens[$i]['id_produto_acabado_discriminacao']."' LIMIT 1 ";
        $campos_produto_acabado = bancos::sql($sql);
    }else {//Existe um PA Substitutivo "Gato por Lebre" (rs)
        //Trago a Ref e Discriminação do PA que o Cliente realmente "comprou"...
        $sql = "SELECT `referencia`, `discriminacao` 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '".$campos_itens[$i]['id_produto_acabado']."' LIMIT 1 ";
        $campos_produto_acabado = bancos::sql($sql);
    }
//Referência ...
    $pdf->Cell($GLOBALS['ph'] * 8, 5, $campos_produto_acabado[0]['referencia'], 1, 0, 'L');
//Discriminação ...
    $pdf->Cell($GLOBALS['ph'] * 48.5, 5, $campos_produto_acabado[0]['discriminacao'], 1, 0, 'L');
    $pdf->SetFont('Arial', '', 7);
//Preço Unit. R$
    $pdf->Cell($GLOBALS['ph'] * 8.5, 5, number_format($campos_itens[$i]['preco_liq_final'], 2, ',', '.'), 1, 0, 'R');
//Total R$
    $pdf->Cell($GLOBALS['ph'] * 10, 5, number_format($campos_itens[$i]['preco_liq_final'] * $campos_itens[$i]['qtde'], 2, ',', '.'), 1, 1, 'R');
    $valor_produto_todos_itens+= ($campos_itens[$i]['preco_liq_final'] * $campos_itens[$i]['qtde']);
    
    $indice_impresso++;
}

$pdf->Cell($GLOBALS['ph'] * 62.5, 5, '', 1, 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 8.5, 5, $rotulo_total, 1, 0, 'L');
$pdf->SetFont('Arial', 'B', 8.7);
$pdf->SetFillColor(200, 200, 200);//Cor Cinza
$pdf->Cell($GLOBALS['ph']*10, 5, $tipo_moeda.number_format($valor_produto_todos_itens, 2, ',', '.'), 'TBR', 1, 'R', 1);
$pdf->Ln(4);

if(!empty($campos[0]['incoterm'])) {
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(90, 5, 'Incoterm: '.$campos[0]['incoterm'], 0, 1, 'L');
}

if($campos[0]['fecha_de_entrega'] != '0000-00-00') {
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(90, 5, 'Fecha de Entrega: '.data::datetodata($campos[0]['fecha_de_entrega'], '/'), 0, 1, 'L');
    $pdf->Ln(4);
}

//Aqui eu busco dados da Conta Estrangeira da Albafer p/ o Cliente ...
$sql = "SELECT b.`id_banco`, b.`banco`, cc.*  
        FROM `contas_correntes` cc 
        INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
        INNER JOIN `bancos` b ON b.`id_banco` = a.`id_banco` 
        WHERE cc.`id_contacorrente` = '$id_contacorrente' LIMIT 1 ";
$campos_conta_corrente = bancos::sql($sql);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(50, 5, 'Account with: '.$campos_conta_corrente[0]['banco'], 0, 1, 'L');

if($campos_conta_corrente[0]['id_banco'] == 1) {//Se o Banco = Banco do Brasil, menos campos ...
    $pdf->Cell(80, 5, 'Swift: '.$campos_conta_corrente[0]['swift_code'], 0, 1, 'L');
    $pdf->Cell(80, 5, 'IBAN: '.$campos_conta_corrente[0]['iban'], 0, 1, 'L');
    
    $sql = "SELECT `razaosocial` 
            FROM `empresas` 
            WHERE `id_empresa` = '".$campos[0]['id_empresa']."' LIMIT 1 ";
    $campos_empresa = bancos::sql($sql);

    $pdf->Cell(50, 5, 'For Further Credit to: '.$campos_empresa[0]['razaosocial'], 0, 1, 'L');
}else {//Nos outros Bancos, mostro mais campos ...
    $pdf->Cell(80, 5, 'Swift Code: '.$campos_conta_corrente[0]['swift_code'], 0, 0, 'L');
    $pdf->Cell(50, 5, 'Account nr: '.$campos_conta_corrente[0]['conta_corrente'], 0, 1, 'L');
    
    $pdf->Cell(50, 5, 'In favor of Banco : '.$campos_conta_corrente[0]['banco_correspondente'], 0, 1, 'L');
    
    $pdf->Cell(80, 5, 'Swift Code: '.$campos_conta_corrente[0]['swift_code_correspondente'], 0, 0, 'L');
    $pdf->Cell(50, 5, 'IBAN: '.$campos_conta_corrente[0]['iban'], 0, 1, 'L');
    
    $sql = "SELECT `razaosocial` 
            FROM `empresas` 
            WHERE `id_empresa` = '".$campos[0]['id_empresa']."' LIMIT 1 ";
    $campos_empresa = bancos::sql($sql);

    $pdf->Cell(50, 5, 'For Further Credit to: '.$campos_empresa[0]['razaosocial'], 0, 1, 'L');

    $pdf->Cell(80, 5, 'Branch number: '.$campos_conta_corrente[0]['agencia_correspondente'], 0, 0, 'L');
    $pdf->Cell(50, 5, 'Account number: '.$campos_conta_corrente[0]['conta_corrente_correspondente'], 0, 1, 'L');
}
$pdf->Ln(6);

//Assinatura do Funcionário ...
$pdf->Cell($GLOBALS['ph'] * 85, 5, '________________________________________________', 0, 1, 'C');
$pdf->Cell($GLOBALS['ph'] * 85, 5, 'Wilson Roberto Rodrigues - Diretor Comercial', 0, 1, 'C');

/***************************************************************************/
chdir('../../../../../pdf');
$file='../../../../../pdf/'.basename(tempnam(getcwd()), '').'Pedido_Grupo_Albafer_'.$_GET['id_orcamento_venda'].'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>