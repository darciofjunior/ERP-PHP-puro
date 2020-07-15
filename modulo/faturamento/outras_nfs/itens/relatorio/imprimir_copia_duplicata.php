<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
require('../../../../../lib/faturamentos.php');
require('../../../../../lib/intermodular.php');
require('../../../../../lib/num_por_extenso_em_rs.php');
error_reporting(0);
segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');

/////////////////////////////////////// INCIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel     = 'P';  // P=> Retrato L=>Paisagem
$unidade        = 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= 'A4'; // A3, A4, A5, Letter, Legal
$pdf            = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->SetLeftMargin(6);
$pdf->SetTopMargin(1);
$pdf->SetAutoPageBreak();//Aqui é para não fazer quebra de página ...
$pdf->Open();
$pdf->AddPage();
global $pv, $ph; //Valor baseado em mm do Ofício ...

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

/**********************************Dados do Cliente**********************************/
$sql = "SELECT c.`cod_cliente`, c.`id_pais`, c.`id_uf`, 
        IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, c.`cnpj_cpf`, 
        CONCAT(c.`endereco`, ', ', c.`num_complemento`) AS endereco, c.`bairro`, c.`cep`, c.`cidade`, 
        c.`insc_estadual`, nfso.* 
        FROM `nfs_outras` nfso 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfso.`id_cliente` 
        WHERE nfso.`id_nf_outra` = '$_GET[id_nf_outra]' LIMIT 1 ";
$campos             = bancos::sql($sql);
//Coloquei esse nome na variável porque na sessão já existe uma variável com o nome de id_empresa
$id_empresa_nota    = $campos[0]['id_empresa'];
$id_cfop            = $campos[0]['id_cfop'];
$id_uf              = $campos[0]['id_uf'];
$cod_cliente        = $campos[0]['cod_cliente'];
$cliente            = $campos[0]['cliente'];

if(!empty($campos[0]['cnpj_cpf'])) {
    if(strlen($campos[0]['cnpj_cpf']) == 11) {//CPF ...
        $cnpj_cpf = substr($campos[0]['cnpj_cpf'], 0, 3).'.'.substr($campos[0]['cnpj_cpf'], 3, 3).'.'.substr($campos[0]['cnpj_cpf'], 6, 3).'-'.substr($campos[0]['cnpj_cpf'], 9, 2);
    }else {//CNPJ ...
        $cnpj_cpf = substr($campos[0]['cnpj_cpf'], 0, 2).'.'.substr($campos[0]['cnpj_cpf'], 2, 3).'.'.substr($campos[0]['cnpj_cpf'], 5, 3).'/'.substr($campos[0]['cnpj_cpf'], 8, 4).'-'.substr($campos[0]['cnpj_cpf'], 12, 2);
    }
}else {
    $cnpj_cpf = '';
}

$data_emissao       = data::datetodata($campos[0]['data_emissao'], '/');
$endereco           = $campos[0]['endereco'];
$bairro             = $campos[0]['bairro'];
$cep                = $campos[0]['cep'];
$cidade             = $campos[0]['cidade'];
$insc_estadual      = substr($campos[0]['insc_estadual'], 0, 3).'.'.substr($campos[0]['insc_estadual'], 3, 3).'.'.substr($campos[0]['insc_estadual'], 6, 3).'.'.substr($campos[0]['insc_estadual'], 9, 3);

//Unidade Federal ...
if(!empty($id_uf)) {
    $sql = "SELECT `sigla` 
            FROM `ufs` 
            WHERE id_uf = '$id_uf' LIMIT 1 ";
    $campos_uf  = bancos::sql($sql);
    $uf         = $campos_uf[0]['sigla'];
}

//Prazos
$vencimento1        = $campos[0]['vencimento1'];
if($campos[0]['data_emissao'] != '0000-00-00') $data_vencimento1 = data::adicionar_data_hora($data_emissao, $vencimento1);
$valor1             = $campos[0]['valor1'];
$qtde_vias_duplic   = 1;//Pelo menos 1 via irá existir na Duplicata ...
	
if($campos[0]['vencimento2'] == 0) {
    $vencimento2        = '';
    $data_vencimento2   = '';
}else {
    $vencimento2    = $campos[0]['vencimento2'];
    if($campos[0]['data_emissao'] != '0000-00-00') $data_vencimento2 = data::adicionar_data_hora($data_emissao, $vencimento2);
    $valor2         = $campos[0]['valor2'];
    $qtde_vias_duplic++;
}

if($campos[0]['vencimento3'] == 0) {
    $vencimento3        = '';
    $data_vencimento3   = '';
}else {
    $vencimento3 = $campos[0]['vencimento3'];
    if($campos[0]['data_emissao'] != '0000-00-00') $data_vencimento3 = data::adicionar_data_hora($data_emissao, $vencimento3);
    $valor3 = $campos[0]['valor3'];
    $qtde_vias_duplic++;
}

if($campos[0]['vencimento4'] == 0) {
    $vencimento4        = '';
    $data_vencimento4   = '';
}else {
    $vencimento4 = $campos[0]['vencimento4'];
    if($campos[0]['data_emissao'] != '0000-00-00') $data_vencimento4 = data::adicionar_data_hora($data_emissao, $vencimento4);
    $valor4 = $campos[0]['valor4'];
    $qtde_vias_duplic++;
}
	
$numero_nf      = faturamentos::buscar_numero_nf($_GET['id_nf_outra'], 'O');
$observacao_nf	= $campos[0]['observacao'];

/**********************************Dados da Empresa**********************************/
$sql = "SELECT e.*, ufs.sigla 
        FROM `empresas` e 
        INNER JOIN `ufs` ON ufs.`id_uf` = e.`id_uf` 
        WHERE e.`id_empresa` = '$id_empresa_nota' LIMIT 1 ";
$campos_empresa     = bancos::sql($sql);
$cnpj               = substr($campos_empresa[0]['cnpj'], 0, 2).'.'.substr($campos_empresa[0]['cnpj'], 2, 3).'.'.substr($campos_empresa[0]['cnpj'], 5, 3).'/'.substr($campos_empresa[0]['cnpj'], 8, 4).'-'.substr($campos_empresa[0]['cnpj'], 12, 2);
$ie                 = substr($campos_empresa[0]['ie'], 0, 3).'.'.substr($campos_empresa[0]['ie'], 3, 3).'.'.substr($campos_empresa[0]['ie'], 6, 3).'.'.substr($campos_empresa[0]['ie'], 9, 3);

//CFOP ...
$sql = "SELECT `id_cfop_revenda`, `cfop`, CONCAT(`cfop`, '.', `num_cfop`) AS cfop_ind 
        FROM `cfops` 
        WHERE `id_cfop` = '$id_cfop' 
        AND `ativo` = '1' LIMIT 1 ";
$campos_cfop    = bancos::sql($sql);
$cfops          = $campos_cfop[0]['cfop_ind'];
//Significa que está atrelada uma CFOP de Revenda p/ a CFOP Industrial ...
if($campos_cfop[0]['id_cfop_revenda'] != 0) {
//Mais antes eu verifico se existe pelo menos um PA em que a Operação é do Tipo Revenda ...
    $sql = "SELECT pa.`id_produto_acabado` 
            FROM `nfs_itens` nfsi 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = nfsi.`id_produto_acabado` AND pa.`operacao` = '1' 
            WHERE nfsi.`id_nf` = '$_GET[id_nf_outra]' LIMIT 1 ";
    $campos_item_revenda = bancos::sql($sql);
    if(count($campos_item_revenda) == 1) {//Existe pelo menos um item sendo Revenda ...
        $sql = "SELECT CONCAT(`cfop`, '.', `num_cfop`) AS cfop_revenda 
                FROM `cfops` 
                WHERE `id_cfop` = '".$campos_cfop[0]['id_cfop_revenda']."' 
                AND `ativo` = '1' LIMIT 1 ";
        $campos_cfop_revenda = bancos::sql($sql);
        $cfops.= ' / '.$campos_cfop_revenda[0]['cfop_revenda'];
    }
}

/**********************************************************************************************************/
/******************************************Impressão da Duplicata******************************************/
/**********************************************************************************************************/
$valor_array = array($valor1, $valor2, $valor3, $valor4);

if($qtde_vias_duplic == 1) {//Uma única Via ...
    $duplicata_array = array($numero_nf);
}else {//Mais de uma via ...
    $duplicata_array = array($numero_nf.'-A', $numero_nf.'-B', $numero_nf.'-C', $numero_nf.'-D');	
}
$data_vencimento_array = array($data_vencimento1, $data_vencimento2, $data_vencimento3, $data_vencimento4);

for($i = 0; $i < $qtde_vias_duplic; $i++) {
    //Quando eu estiver na Terceira Via, então mando Inserir uma Nova Página ...
    if($i == 2) $pdf->AddPage();
//Imagem a ser exibida na Impressão da NF ...
    if($id_empresa_nota == 1) {//Se o usuário escolheu Albafér ...
        $pdf->Image('../../../../../imagem/marcas/cabri_preto.jpg', 112, $pdf->GetY() + 5.4, 13, 13, 'JPG');
    }else {//Se o usuário escolheu Tool Master ...
        $pdf->Image('../../../../../imagem/marcas/tool_preto.jpg', 98, $pdf->GetY() + 7, 45, 10, 'JPG');
    }
    $pdf->SetFont('Arial', 'B', 13.4);	
//Dados de Empresa ...
    $pdf->Cell($GLOBALS['ph']*70, 5, $campos_empresa[0]['razaosocial'], 'TLR', 0, 'L');
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell($GLOBALS['ph']*30, 5, 'BANCO:', 'TLR', 1, 'L');
    $pdf->Cell($GLOBALS['ph']*70, 5, strtoupper($campos_empresa[0]['endereco']).', '.$campos_empresa[0]['numero'].' - BAIRRO: '.strtoupper($campos_empresa[0]['bairro']), 'LR', 0, 'L');
    $pdf->Cell($GLOBALS['ph']*30, 5, '', 'LR', 1, 'L');
    $pdf->Cell($GLOBALS['ph']*70, 5, 'CEP: '.$campos_empresa[0]['cep'].' - MUNICÍPIO: '.strtoupper($campos_empresa[0]['cidade']).' - ESTADO: '.strtoupper($campos_empresa[0]['sigla']), 'LR', 0, 'L');
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell($GLOBALS['ph']*30, 5, '', 'LR', 1, 'C');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell($GLOBALS['ph']*70, 5, 'CNPJ: '.$cnpj.' - I. EST. N.º '.$ie, 'BLR', 0, 'L');
    $pdf->Cell($GLOBALS['ph']*30, 5, '', 'BLR', 1, 'L');
//Natureza da Operação ...
    $pdf->Cell($GLOBALS['ph']*25, 5, 'NATUREZA DA OPERAÇÃO: ', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*25, 5, $cfops, 1, 0, 'C');
//Data de Emissão ...
    $pdf->Cell($GLOBALS['ph']*25, 5, 'DATA DE EMISSÃO: ', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*25, 5, $data_emissao, 1, 1, 'C');
//Espaço vázio do Banco ...
    $pdf->Cell($GLOBALS['ph']*25, 7, '', 'TLR', 0, 'C');
    $pdf->SetFont('Arial', '', 9);
//Juros ...
    $pdf->Cell($GLOBALS['ph']*37.5, 7, 'JUROS: ', 1, 0, 'L');
//Abatimento ...
    $pdf->Cell($GLOBALS['ph']*37.5, 7, 'ABATIMENTO: ', 1, 1, 'L');
    $pdf->SetFont('Arial', 'B', 16);
//Cópia ...
    $pdf->Cell($GLOBALS['ph']*25, 7, 'CÓPIA ', 'LRC', 0, 'C');
    $pdf->SetFont('Arial', '', 9);
//Data Pagto ...
    $pdf->Cell($GLOBALS['ph']*37.5, 7, 'DATA PAGTO: ', 1, 0, 'L');
//Valor Pagto ...
    $pdf->Cell($GLOBALS['ph']*37.5, 7, 'VALOR PAGO: ', 1, 1, 'L');

//Espaço vázio do Banco ...
    $pdf->Cell($GLOBALS['ph']*25, 7, '', 'BLR', 0, 'L');
//Nosso N.º ...
    $pdf->Cell($GLOBALS['ph']*75, 7, 'NOSSO N.º: ', 1, 1, 'L');
	
    $pdf->SetFont('Arial', 'B', 8);
//Cartório - Data Entrada ...
    $pdf->Cell($GLOBALS['ph']*38, 7, $pdf->Rect($pdf->GetX() + 1.2, $pdf->GetY() + 0.8, 3.5, 3.5).'      CARTÓRIO - DATA DE ENTRADA: ', 1, 0, 'L');
//Taxa Despesa Bancária ...
    $pdf->Cell($GLOBALS['ph']*34, 7, 'TAXA DESPESA BANCÁRIA: ', 1, 0, 'L');
//Taxa de Cartório ...
    $pdf->Cell($GLOBALS['ph']*28, 7, 'TAXA DE CARTÓRIO: ', 1, 1, 'L');
//Protestado - Data Protesto ...
    $pdf->Cell($GLOBALS['ph']*100, 7, $pdf->Rect($pdf->GetX() + 1.2, $pdf->GetY() + 0.8, 3.5, 3.5).'      PROTESTADO - DATA DO PROTESTO: ', 1, 1, 'L');
	
    $pdf->SetFont('Arial', '', 9);
//Rótulo Principal ...
    $pdf->Cell($GLOBALS['ph']*100, 7, 'NOTA FISCAL', 1, 1, 'C');
//Nota Fiscal N.º
    $pdf->Cell($GLOBALS['ph']*25, 5, 'FATURA N.º ', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*25, 5, 'DUPLICATA - VALOR R$ ', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*25, 5, 'N.º DE ORDEM ', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph']*25, 5, 'VENCIMENTO ', 1, 1, 'C');

    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell($GLOBALS['ph']*25, 5, $numero_nf, 1, 0, 'C');
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell($GLOBALS['ph']*25, 5, number_format($valor_array[$i], 2, ',', '.'), 1, 0, 'C');

    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell($GLOBALS['ph']*25, 5, $duplicata_array[$i], 1, 0, 'C');
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell($GLOBALS['ph']*25, 5, $data_vencimento_array[$i], 1, 1, 'C');

    $pdf->SetFont('Arial', '', 9);
//Nome do Sacado - Cliente
    $pdf->Cell($GLOBALS['ph']*25, 5, 'NOME DO SACADO: ', 'TL', 0, 'L');
    $pdf->Cell($GLOBALS['ph']*75, 5, $cod_cliente.'-'.$cliente, 'TR', 1, 'L');
    $pdf->Cell($GLOBALS['ph']*100, 5, '', 'LR', 1, 'L');
	
//Endereço
    $pdf->Cell($GLOBALS['ph']*25, 5, 'ENDEREÇO: ', 'L', 0, 'L');
    $pdf->Cell($GLOBALS['ph']*40, 5, $endereco, 0, 0, 'L');
//Bairro
    $pdf->Cell($GLOBALS['ph']*8, 5, 'BAIRRO: ', 0, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*27, 5, $bairro, 'R', 1, 'L');

//CEP / Município - Cidade
    $pdf->Cell($GLOBALS['ph']*25, 5, 'CEP / MUNICÍPIO: ', 'L', 0, 'L');
    $pdf->Cell($GLOBALS['ph']*40, 5, $cep.' / '.$cidade, 0, 0, 'L'); 
    $pdf->Cell($GLOBALS['ph']*8, 5, 'ESTADO: ', 0, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*27, 5, $uf, 'R', 1, 'L');

//CNPJ
    $pdf->Cell($GLOBALS['ph']*25, 5, 'CNPJ / CPF: ', 'LB', 0, 'L');
    $pdf->Cell($GLOBALS['ph']*20, 5, $cnpj_cpf, 'B', 0, 'L');
    $pdf->Cell($GLOBALS['ph']*15, 5, 'INSCR. EST. Nº: ', 'B', 0, 'L');
    $pdf->Cell($GLOBALS['ph']*40, 5, $insc_estadual, 'RB', 1, 'L');

//Vendedores - Representante(s) ...
    $pdf->Cell($GLOBALS['ph']*25, 5, 'VENDEDORES: ', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*25, 5, '', 1, 0, 'L');

//Copiador - Login ...
//Busca do nome do Usuário logado ...
    $sql = "SELECT login 
            FROM `logins` 
            WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
    $campos_login = bancos::sql($sql);
    $pdf->Cell($GLOBALS['ph']*25, 5, 'COPIADOR: ', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*25, 5, $campos_login[0]['login'], 1, 1, 'L');

//Vendedores - Representante(s) ...
    $pdf->Cell($GLOBALS['ph']*25, 7, 'VALOR POR EXTENSO: ', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*75, 7, ucfirst(extenso($valor_array[$i], '', 1)), 1, 1, 'L');

//Observação da NF ...
    $pdf->Cell($GLOBALS['ph']*25, 7, 'OBSERVAÇÃO DA NF: ', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*75, 7, $observacao_nf, 1, 1, 'L');
	
//Observação ...
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell($GLOBALS['ph']*25, 7, 'OBSERVAÇÃO: ', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph']*75, 7, '', 1, 1, 'L');
    $pdf->Cell($GLOBALS['ph']*100, 7, '', 1, 1, 'L');
    $pdf->Cell($GLOBALS['ph']*100, 7, '', 1, 1, 'L');
    $pdf->Cell($GLOBALS['ph']*100, 7, '', 1, 1, 'L');
    $pdf->Cell($GLOBALS['ph']*100, 7, '', 1, 1, 'L');
    $pdf->Ln(1);
    $pdf->Cell($GLOBALS['ph']*100, 5, '_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _', 0, 1, 'L');
    $pdf->Ln(2);
}

chdir('../../../../../pdf');
//$file='../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>