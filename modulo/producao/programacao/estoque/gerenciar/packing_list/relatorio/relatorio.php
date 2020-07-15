<?
require('../../../../../../../lib/pdf/fpdf.php');
require('../../../../../../../lib/segurancas.php');
require('../../../../../../../lib/genericas.php');
require('../../../../../../../lib/intermodular.php');
//segurancas::geral('/erp/albafer/modulo/producao/programacao/estoque/gerenciar/consultar.php', '../../../../../../../');
error_reporting(0);

function Heade($id_empresa_buscar) {
    global $pdf;

    //Trago dados de acordo com o id_empresa passado por parâmetro ...
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

    $pdf->Image('../../../../../../../imagem/logo_transparente.jpg', 7, 5, 30, 32, 'JPG');
    
    $pdf->SetFont('Arial', 'BI', 12);
    $pdf->Cell(20 * $GLOBALS['ph'], 10, '', 0, 0, 'L');
    $pdf->Cell(175, 10, '* '.$razao_social, 0, 0, 'L');
    $pdf->Ln(4);
    
    $pdf->SetFont('Arial', 'BI', 9);
    $pdf->Cell(20 * $GLOBALS['ph'], 10, '', 0, 0, 'L');
    $pdf->Cell(130, 10, $endereco.', N.º '.$numero.' - '.$bairro.' - '.$cidade.' - CEP: '.$cep, 0, 0, 'L');
    $pdf->Ln(4);

    $pdf->Cell(20 * $GLOBALS['ph'], 10, '', 0, 0, 'L');
    $pdf->Cell(120, 10, 'FONE/FAX: (55-'.$ddd_comercial.') '.$telefone_comercial.' # E-MAIL: mercedes@grupoalbafer.com.br', 0, 0, 'L');
    $pdf->Ln(4);

    $pdf->Cell(20 * $GLOBALS['ph'], 10, '', 0, 0, 'L');
    $pdf->Cell(185, 10, 'CNPJ: '.$cnpj.' # INSC. EST. - '.$ie, 0, 0, 'L');
    $pdf->SetFont('Arial', 'BI', 13);
    
    $pdf->Ln(11);

    $pdf->Line(1 * $GLOBALS['ph'], 40, 100 * $GLOBALS['ph'], 40);
    $pdf->Ln(15);
}
/////////////////////////////////////// INICIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');

$tipo_papel     = 'P';  // P=> Retrato L=>Paisagem
$unidade        = 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel  = 'A4'; // A3, A4, A5, Letter, Legal
$pdf            = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
$pdf->SetLeftMargin(4);
$pdf->SetTopMargin(5);
$pdf->AddPage();
$pdf->SetAutoPageBreak(auto, 30);

global $pv,$ph; //valor baseado em mm do A4
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

//Busca do N.º da Factura Comercial, Razão Social do Cliente e Empresa do Pedido p/ saber qual Cabeçalho Imprimir ...
$sql = "SELECT DISTINCT(pvi.`id_pedido_venda`) AS id_pedido_venda, c.`razaosocial`, pv.`id_empresa` 
        FROM `packings_lists_itens` pli 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = pli.`id_pedido_venda_item` 
        INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
        INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
        WHERE pli.`id_packing_list` = '$_GET[id_packing_list]' LIMIT 1 ";
$campos = bancos::sql($sql);
Heade($campos[0]['id_empresa']);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($GLOBALS['ph'] * 103, 5, 'PACKING LIST N.º '.$_GET[id_packing_list], 0, 1, 'C');

$pdf->Cell($GLOBALS['ph'] * 20, 5, 'Comprador: ', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($GLOBALS['ph'] * 83, 5, $campos[0]['razaosocial'], 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($GLOBALS['ph'] * 20, 5, 'Factura Comercial: ', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($GLOBALS['ph'] * 83, 5, $campos[0]['id_pedido_venda'], 0, 1, 'L');

$pdf->Ln(3);
$pdf->SetFont('Arial', '', 9.5);

//Aqui eu busco todos os Itens que compõem o Packing List ...
$sql = "SELECT ovi.`id_produto_acabado_discriminacao`, pa.`peso_unitario`, pli.`id_packing_list_item`, pli.`id_produto_acabado`, 
        pli.`id_produto_insumo_master`, pli.`id_produto_insumo_secundario`, pli.`caixa_master_numero`, 
        pli.`caixa_secundario_numero`, pli.`qtde` 
        FROM `packings_lists_itens` pli 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = pli.`id_pedido_venda_item` 
        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pli.`id_produto_acabado` 
        INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = pli.`id_produto_insumo_secundario` 
        WHERE pli.`id_packing_list` = '$_GET[id_packing_list]' ORDER BY pli.`caixa_master_numero`, pli.`caixa_secundario_numero`, pa.`referencia`, pa.`discriminacao` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
/*****************************Organização por Caixa Master*****************************/
    if($caixa_master_numero != $campos[$i]['caixa_master_numero']) {//Organização por N.º de Caixa Master ...
        $caixa_master_numero = $campos[$i]['caixa_master_numero'];
        
        $pdf->Cell($GLOBALS['ph'] * 16, 4.5, 'Caixa Master N.º '.$campos[$i]['caixa_master_numero'], 1, 0, 'L');
        $pdf->SetFont('Arial', '', 9);//Como a Discriminação é grande em alguns casos, a fonte é menorzinha aqui ...
        
        if($campos[$i]['id_produto_insumo_master'] > 0) {
            /*Busco as medidas externas, porque é o espaço real que será utilizado 
            no Container "Navio" ...*/
            $sql = "SELECT `discriminacao`, `peso`, `altura_externo`, `largura_externo`, `comprimento_externo` 
                    FROM `produtos_insumos` 
                    WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo_master']."' LIMIT 1 ";
            $campos_caixa_master = bancos::sql($sql);
            $volume_caixa_master = ($campos_caixa_master[0]['altura_externo'] * $campos_caixa_master[0]['largura_externo'] * $campos_caixa_master[0]['comprimento_externo'] / pow(10, 9));
            
            $pdf->Cell($GLOBALS['ph'] * 53, 4.5, ucfirst(strtolower($campos_caixa_master[0]['discriminacao'])).' ('.$campos_caixa_master[0]['altura_externo'].' alt. x '.$campos_caixa_master[0]['largura_externo'].' larg x '.$campos_caixa_master[0]['comprimento_externo'].' comp.)', 1, 0, 'L');
        }
        
        $pdf->SetFont('Arial', '', 9.5);
        $pdf->Cell($GLOBALS['ph'] * 17, 4.5, '(Peso '.number_format($campos_caixa_master[0]['peso'], 3, ',', '.').' Kgs)', 1, 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 14, 4.5, '(Vol. '.number_format($volume_caixa_master, 3, ',', '.').' m³)', 1, 1, 'L');
    }
    /**************************************************************************************/
    /***************************Organização por Caixa Secundária***************************/
    if($caixa_secundario_numero != $campos[$i]['caixa_secundario_numero']) {//Organização por N.º de Caixa Secundária ...
        $caixa_secundario_numero = $campos[$i]['caixa_secundario_numero'];

        $pdf->Cell($GLOBALS['ph'] * 20, 4.5, 'Caixa Secundária N.º '.$campos[$i]['caixa_secundario_numero'], 1, 0, 'L');
        $pdf->SetFont('Arial', '', 9);//Como a Discriminação é grande em alguns casos, a fonte é menorzinha aqui ...
        
        $sql = "SELECT `discriminacao`, `peso` 
                FROM `produtos_insumos` 
                WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo_secundario']."' LIMIT 1 ";
        $campos_caixa_secundario = bancos::sql($sql);
        
        $pdf->Cell($GLOBALS['ph'] * 64, 4.5, ucfirst(strtolower($campos_caixa_secundario[0]['discriminacao'])), 1, 0, 'L');
        
        $pdf->SetFont('Arial', '', 9.5);
        $pdf->Cell($GLOBALS['ph'] * 16, 4.5, '(Peso '.number_format($campos_caixa_secundario[0]['peso'], 3, ',', '.').' Kgs)', 1, 1, 'L');
        
        $pdf->Cell($GLOBALS['ph'] * 15, 4.5, 'Qtde Packing List', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 56, 4.5, 'Produto', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 13, 4.5, 'Peso Unitário', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 16, 4.5, 'Peso Total', 1, 1, 'C');
    }
    /**************************************************************************************/
    
    $pdf->Cell($GLOBALS['ph'] * 15, 4.5, $campos[$i]['qtde'], 1, 0, 'C');
    
    if($campos[$i]['id_produto_acabado_discriminacao'] > 0) {//Existe um PA Substitutivo "Gato por Lebre" (rs)
        /*Trago a Ref e Discriminação do PA que o Cliente "comprou", "o Cliente tem que enxergar isso " apesar 
        de estar sendo enviado um outro Produto ...*/
        $sql = "SELECT pa.`referencia`, pa.`discriminacao`, u.`sigla` 
                FROM `produtos_acabados` pa 
                INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
                WHERE pa.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado_discriminacao']."' LIMIT 1 ";
        $campos_produto_acabado = bancos::sql($sql);
        $produto_acabado        = $campos_produto_acabado[0]['sigla'].' * '.$campos_produto_acabado[0]['referencia'].' - '.$campos_produto_acabado[0]['discriminacao'];
    }else {//Existe um PA Substitutivo "Gato por Lebre" (rs)
        //Trago a Ref e Discriminação do PA que o Cliente realmente "comprou"...
        $produto_acabado        = intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0, 0, 0, 0, 1);
    }

    $pdf->Cell($GLOBALS['ph'] * 56, 4.5, $produto_acabado, 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph'] * 13, 4.5, number_format($campos[$i]['peso_unitario'], 3, ',', '.'), 1, 0, 'R');
    $pdf->Cell($GLOBALS['ph'] * 16, 4.5, number_format($campos[$i]['qtde'] * $campos[$i]['peso_unitario'], 3, ',', '.'), 1, 1, 'R');
    $peso_liquido_caixa_secundaria+=    $campos[$i]['qtde'] * $campos[$i]['peso_unitario'];
/**************************************************************************************/
/*******************************Pesos da Caixa Secundária******************************/
//Se a caixa Secundária atual for diferente da próxima Caixa Secundária, então já apresento o Peso Total da Caixa ...
    if($caixa_secundario_numero != $campos[$i + 1]['caixa_secundario_numero']) {
        $pdf->Cell($GLOBALS['ph'] * 84, 4.5, 'Peso Líquido da Caixa Secundária => ', 1, 0, 'L');
        /*Só apresento o Total de Peso da Mercadoria que está dentro da caixa, 
        nesse caso não levo em conta o peso do própria Caixa ...*/
        $pdf->Cell($GLOBALS['ph'] * 16, 4.5, number_format($peso_liquido_caixa_secundaria, 3, ',', '.'), 1, 1, 'R');
        
        $peso_liquido_todas_caixa_master+=  $peso_liquido_caixa_secundaria;//Que na realidade é o peso do próprio PA sem as caixas de Papelão ...
        
        /*Além do Total de Peso da Mercadoria que está dentro da caixa, 
        levo em conta também o peso do própria Caixa ...*/
        $pdf->Cell($GLOBALS['ph'] * 84, 4.5, 'Peso Bruto da Caixa Secundária => ', 1, 0, 'L');
        //Sempre somo ao Total da Caixa "Todos os PAs" o Valor da Caixa Secundária ...
        $pdf->Cell($GLOBALS['ph'] * 16, 4.5, number_format($peso_liquido_caixa_secundaria + $campos_caixa_secundario[0]['peso'], 3, ',', '.'), 1, 1, 'R');
        
        //Essas variáveis serão utilizadas mais abaixo ...

        //1) Tratamento com as Caixas Secundárias do Lote ...
        $vetor_peso_bruto_caixa_secundaria[$caixa_secundario_numero]        = $peso_liquido_caixa_secundaria + $campos_caixa_secundario[0]['peso'];

        //2) Tratamento com as Caixas Master do Lote ...
        $vetor_peso_liquido_caixa_master[$caixa_master_numero]+=            $vetor_peso_bruto_caixa_secundaria[$caixa_secundario_numero];
        $vetor_peso_bruto_caixa_master[$caixa_master_numero]                = $vetor_peso_liquido_caixa_master[$caixa_master_numero] + $campos_caixa_master[0]['peso'];

        $peso_liquido_caixa_secundaria      = 0;//Aqui eu zero o Total da caixa Secundária para não acumular o valor dessa nas próximas Caixas ...
    }
    /**************************************************************************************/
    /*******************************Pesos da Caixa Master******************************/
    //Se a caixa Master atual for diferente da próxima Caixa Master, então já apresento o Peso Total da Caixa ...
    if($caixa_master_numero != $campos[$i + 1]['caixa_master_numero']) {
        $pdf->Cell($GLOBALS['ph'] * 84, 4.5, 'Peso Líquido da Caixa Master => ', 1, 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 16, 4.5, number_format($vetor_peso_liquido_caixa_master[$caixa_master_numero], 3, ',', '.'), 1, 1, 'R');
        
        $pdf->Cell($GLOBALS['ph'] * 84, 4.5, 'Peso Bruto da Caixa Master => ', 1, 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 16, 4.5, number_format($vetor_peso_bruto_caixa_master[$caixa_master_numero], 3, ',', '.'), 1, 1, 'R');
        
        //3) Tratamento com todas as Caixas Master do Packing List Inteiro ...
        $peso_bruto_todas_caixa_master+=    $vetor_peso_bruto_caixa_master[$caixa_master_numero];
        $volume_todas_caixa_master+=        $volume_caixa_master;
    }
    /**************************************************************************************/
}
/******************************************Total do Packing List******************************************/
$pdf->Ln(5);

//Aqui eu verifico se existe pelo menos uma Caixa Master p/ Fazer o relatório de demonstração ...
$sql = "SELECT COUNT(DISTINCT(caixa_master_numero)) AS qtde_caixa_master 
        FROM `packings_lists_itens` 
        WHERE `id_packing_list` = '$_GET[id_packing_list]' 
        AND `id_produto_insumo_master` > '0' ";
$campos = bancos::sql($sql);
if($campos[0]['qtde_caixa_master'] > 0) {
    $pdf->Cell($GLOBALS['ph'] * 100, 4.5, 'Total do Packing List', 1, 1, 'C');
    
    $pdf->Cell($GLOBALS['ph'] * 25, 4.5, 'Qtde Caixas Master', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 25, 4.5, 'Peso Bruto (Kg)', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 25, 4.5, 'Peso Líquido (Kg)', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 25, 4.5, 'Volume (m³)', 1, 1, 'C');
    
    $pdf->Cell($GLOBALS['ph'] * 25, 4.5, $campos[0]['qtde_caixa_master'], 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 25, 4.5, number_format($peso_bruto_todas_caixa_master, 3, ',', '.'), 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 25, 4.5, number_format($peso_liquido_todas_caixa_master, 3, ',', '.'), 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 25, 4.5, number_format($volume_todas_caixa_master, 3, ',', '.').' (m³)', 1, 1, 'C');
    
    //Gravo na Tabela de Packing List esse "Resumo" de Qtde de Caixas, Peso Bruto, Peso Líquido e Volume ...
    $sql = "UPDATE `packings_lists` SET `qtde_caixas` = '".$campos[0]['qtde_caixa_master']."', `peso_bruto` = '$peso_bruto_todas_caixa_master', `peso_liquido` = '$peso_liquido_todas_caixa_master', `volume` = '$volume_todas_caixa_master' WHERE `id_packing_list` = '$_GET[id_packing_list]' LIMIT 1 ";
    bancos::sql($sql);
}

$pdf->Ln(10);

//Assinatura do Funcionário ...
$pdf->Cell($GLOBALS['ph'] * 100, 4.5, '________________________________________________', 0, 1, 'C');
$pdf->Cell($GLOBALS['ph'] * 100, 4.5, 'Wilson Roberto Rodrigues - Diretor Comercial', 0, 1, 'C');

chdir('../../../../../../../pdf');
$file = '../../../../../../../pdf/'.basename(tempnam(getcwd()), '').'Packing_List_Grupo_Albafer_'.$_GET[id_packing_list].'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>