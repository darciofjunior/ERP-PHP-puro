<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
require('../../../../../lib/calculos.php');
require('../../../../../lib/data.php');
require('../../../../../lib/faturamentos.php');
require('../../../../../lib/genericas.php');//Essa biblioteca È requerida dentro da Intermodular ...
require('../../../../../lib/intermodular.php');
require('../../../../../lib/num_por_extenso_em_rs.php');
error_reporting(0);

switch($opcao) {
    case 1://Significa que veio do Menu Abertas / Liberadas ...
    case 2://Significa que veio do Menu de Liberadas / Faturadas ...
    case 3://Significa que veio do Menu de Faturadas / Empacotadas / Despachadas ...
        segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
    break;
    case 4://Significa que veio do Menu de DevoluÁ„o 
        segurancas::geral('/erp/albafer/modulo/faturamento/nota_saida/itens/devolucao.php', '../../../../');
    break;
    default://Significa que veio do Menu de DevoluÁ„o ...
        segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
    break;
}

/////////////////////////////////////// INÕCIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel     = 'P';  // P=> Retrato L=>Paisagem
$unidade        = 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= 'A4'; // A3, A4, A5, Letter, Legal
$pdf            = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->SetLeftMargin(6);
$pdf->SetTopMargin(5);
$pdf->SetAutoPageBreak();//Aqui È para n„o fazer quebra de p·gina ...
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
    echo 'Formato n„o definido';
}

/**********************************Dados do Cliente**********************************/
$sql = "SELECT c.`id_cliente`, c.`cod_cliente`, c.`id_pais`, c.`id_uf`, c.`id_pais_cobranca`, c.`id_uf_cobranca`, 
        IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, `cnpj_cpf`, `rg`, `orgao`, 
        c.`endereco`, c.`num_complemento`, c.`bairro`, c.`cep`, c.`cidade`, 
        c.`endereco_cobranca`, c.`num_complemento_cobranca`, c.`bairro_cobranca`, c.`cep_cobranca`, c.`cidade_cobranca`, c.`insc_estadual`, 
        c.`email_financeiro`, nfs.* 
        FROM `nfs` 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
        WHERE nfs.`id_nf` = '$_GET[id_nf]' LIMIT 1 ";
$campos                 = bancos::sql($sql);
$id_cliente	    	= $campos[0]['id_cliente'];
//Coloquei esse nome na vari·vel porque na sess„o j· existe uma vari·vel com o nome de id_empresa
$id_empresa_nota 	= $campos[0]['id_empresa'];
$id_cfop                = $campos[0]['id_cfop'];
$suframa_nf             = $campos[0]['suframa'];
$cod_cliente	   	= $campos[0]['cod_cliente'];
$cliente                = $campos[0]['cliente'];

if(!empty($campos[0]['cnpj_cpf'])) {
    if(strlen($campos[0]['cnpj_cpf']) == 11) {//CPF ...
        $cnpj_cpf = substr($campos[0]['cnpj_cpf'], 0, 3).'.'.substr($campos[0]['cnpj_cpf'], 3, 3).'.'.substr($campos[0]['cnpj_cpf'], 6, 3).'-'.substr($campos[0]['cnpj_cpf'], 9, 2);
    }else {//CNPJ ...
        $cnpj_cpf = substr($campos[0]['cnpj_cpf'], 0, 2).'.'.substr($campos[0]['cnpj_cpf'], 2, 3).'.'.substr($campos[0]['cnpj_cpf'], 5, 3).'/'.substr($campos[0]['cnpj_cpf'], 8, 4).'-'.substr($campos[0]['cnpj_cpf'], 12, 2);
    }
}else {
    $cnpj_cpf = '';
}

$rg			= $campos[0]['rg'];
if(!empty($campos[0]['orgao'])) $rg.= ' - '.$campos[0]['orgao'];

$data_emissao		= data::datetodata($campos[0]['data_emissao'], '/');
$data_bl		= data::datetodata($campos[0]['data_bl'], '/');

//O endereÁo de CobranÁa sempre ter· prioridade sobre o endereÁo Normal ...
$id_pais                = (!empty($campos[0]['id_pais_cobranca'])) ? $campos[0]['id_pais_cobranca'] : $campos[0]['id_pais'];
$id_uf                  = (!empty($campos[0]['id_uf_cobranca'])) ? $campos[0]['id_uf_cobranca'] : $campos[0]['id_uf'];
$endereco               = (!empty($campos[0]['endereco_cobranca'])) ? $campos[0]['endereco_cobranca'].', '.$campos[0]['num_complemento_cobranca'] : $campos[0]['endereco'].', '.$campos[0]['num_complemento'];

$bairro                 = (!empty($campos[0]['bairro_cobranca'])) ? $campos[0]['bairro_cobranca'] : $campos[0]['bairro'];
$cep                    = (!empty($campos[0]['cep_cobranca'])) ? $campos[0]['cep_cobranca'] : $campos[0]['cep'];
$cidade                 = (!empty($campos[0]['cidade_cobranca'])) ? $campos[0]['cidade_cobranca'] : $campos[0]['cidade'];

$insc_estadual 		= substr($campos[0]['insc_estadual'], 0, 3).'.'.substr($campos[0]['insc_estadual'], 3, 3).'.'.substr($campos[0]['insc_estadual'], 6, 3).'.'.substr($campos[0]['insc_estadual'], 9, 3);
$email_financeiro	= $campos[0]['email_financeiro'];

//N„o ser· permitido a Impress„o da Duplicata enquanto n„o existir um e-mail Financeiro cadastrado no sistema ...
/***************************Controle com o E-mail Financeiro***************************/
if(empty($email_financeiro)) {
?>
<Script Language = 'JavaScript'>
    alert('ESSE CLIENTE N√O POSSUI E-MAIL FINANCEIRO CADASTRADO !!!\n\n… NECESS¡RIO TER O E-MAIL FINANCEIRO CADASTRADO P/ QUE SE IMPRIMA A(S) DUPLICATA(S) E O BANCO ENVIE OS BORDERO(S) AO E-MAIL CORRETO DO CLIENTE !')
    window.close()
</Script>
<?
}
/**************************************************************************************/
/**************************************************************************************************************/
/*********************************Controle com a Parte de EndereÁo do Cliente**********************************/
/**************************************************************************************************************/
$enderecos_vazios = 0;

if($id_pais == 31) {//Se for do Brasil ir· forÁar o Cliente a ter todos esses dados preenchidos ...
    if($endereco == '' || $bairro == '' || $cidade == '' || $id_uf == '') $enderecos_vazios++;
}else {//Se for Estrangeiro ... o Cliente tem que ter pelo menos o EndereÁo e a Cidade ...
    if($endereco == '' || $cidade == '') $enderecos_vazios++;
}

if($enderecos_vazios == 1) {//Significa que n„o existe nenhum Tipo de EndereÁo ...
?>
    <Script Language = 'JavaScript'>
        alert('ATUALIZE CORRETAMENTE OS DADOS DE ENDERE«O E ENDERE«O DE COBRAN«A DO CLIENTE !')
        window.close()
    </Script>
<?
    exit;
}
/**************************************************************************************************************/

//Unidade Federal ...
if(!empty($id_uf)) {
    $sql = "SELECT `sigla` 
            FROM `ufs` 
            WHERE `id_uf` = '$id_uf' LIMIT 1 ";
    $campos_uf  = bancos::sql($sql);
    $uf         = $campos_uf[0]['sigla'];
}

//Aqui verifica o Tipo de Nota
if($id_empresa_nota == 1 || $id_empresa_nota == 2) {
    $nota_sgd = 'N';//var surti efeito l· embaixo
}else {
    $nota_sgd = 'S'; //var surti efeito l· embaixo
}
$tipo_nfe_nfs   = $campos[0]['tipo_nfe_nfs'];

//Prazos
$vencimento1 = $campos[0]['vencimento1'];
/*SÛ existe este campo, p/ clientes Internacionais, caso esteja preenchido, os vencimentos
ser„o feitos em cima deste ...*/
if($campos[0]['data_bl'] != '0000-00-00') {
    $data_vencimento1 = data::adicionar_data_hora($data_bl, $vencimento1);
}else {
    if($campos[0]['data_emissao'] != '0000-00-00') $data_vencimento1 = data::adicionar_data_hora($data_emissao, $vencimento1);
}
$valor1 = $campos[0]['valor1'];
$qtde_duplicatas = 1;//Pelo menos 1 via ir· existir na Duplicata ...
	
if($campos[0]['vencimento2'] == 0) {
    $vencimento2        = '';
    $data_vencimento2   = '';
}else {
    $vencimento2        = $campos[0]['vencimento2'];
/*SÛ existe este campo, p/ clientes Internacionais, caso esteja preenchido, os vencimentos
ser„o feitos em cima deste ...*/
    if($campos[0]['data_bl'] != '0000-00-00') {
        $data_vencimento2 = data::adicionar_data_hora($data_bl, $vencimento2);
    }else {
        if($campos[0]['data_emissao'] != '0000-00-00') $data_vencimento2 = data::adicionar_data_hora($data_emissao, $vencimento2);
    }
    $valor2 = $campos[0]['valor2'];
    $qtde_duplicatas++;
}

if($campos[0]['vencimento3'] == 0) {
    $vencimento3 = '';
    $data_vencimento3 = '';
}else {
    $vencimento3 = $campos[0]['vencimento3'];
/*SÛ existe este campo, p/ clientes Internacionais, caso esteja preenchido, os vencimentos
ser„o feitos em cima deste ...*/
    if($campos[0]['data_bl'] != '0000-00-00') {
        $data_vencimento3 = data::adicionar_data_hora($data_bl, $vencimento3);
    }else {
        if($campos[0]['data_emissao'] != '0000-00-00') $data_vencimento3 = data::adicionar_data_hora($data_emissao, $vencimento3);
    }
    $valor3 = $campos[0]['valor3'];
    $qtde_duplicatas++;
}

if($campos[0]['vencimento4'] == 0) {
    $vencimento4 = '';
    $data_vencimento4 = '';
}else {
    $vencimento4 = $campos[0]['vencimento4'];
/*SÛ existe este campo, p/ clientes Internacionais, caso esteja preenchido, os vencimentos
ser„o feitos em cima deste ...*/
    if($campos[0]['data_bl'] != '0000-00-00') {
        $data_vencimento4 = data::adicionar_data_hora($data_bl, $vencimento4);
    }else {
        if($campos[0]['data_emissao'] != '0000-00-00') $data_vencimento4 = data::adicionar_data_hora($data_emissao, $vencimento4);
    }
    $valor4 = $campos[0]['valor4'];
    $qtde_duplicatas++;
}
	
$numero_nf      = faturamentos::buscar_numero_nf($_GET[id_nf], 'S');
$observacao_nf	= $campos[0]['observacao'];

//Aqui eu busco todos os Representantes referentes a esses Itens ...
$sql = "SELECT DISTINCT(r.`nome_fantasia`) AS nome_fantasia 
        FROM `nfs_itens` nfsi 
        INNER JOIN `representantes` r ON r.`id_representante` = nfsi.`id_representante` 
        WHERE nfsi.`id_nf` = '$_GET[id_nf]' ";
$campos_rep = bancos::sql($sql);
$linhas_rep = count($campos_rep);
//Lista dos Representantes ...
for($i = 0; $i < $linhas_rep; $i++) $representantes.= $campos_rep[$i]['nome_fantasia'].', ';
$representantes = substr($representantes, 0, strlen($representantes) - 2);

/**********************************Dados da Empresa**********************************/
$sql = "SELECT e.*, ufs.`sigla` 
        FROM `empresas` e 
        INNER JOIN `ufs` ON ufs.`id_uf` = e.`id_uf` 
        WHERE e.`id_empresa` = '$id_empresa_nota' LIMIT 1 ";
$campos_empresa = bancos::sql($sql);
$cnpj           = substr($campos_empresa[0]['cnpj'], 0, 2).'.'.substr($campos_empresa[0]['cnpj'], 2, 3).'.'.substr($campos_empresa[0]['cnpj'], 5, 3).'/'.substr($campos_empresa[0]['cnpj'], 8, 4).'-'.substr($campos_empresa[0]['cnpj'], 12, 2);
$ie             = substr($campos_empresa[0]['ie'], 0, 3).'.'.substr($campos_empresa[0]['ie'], 3, 3).'.'.substr($campos_empresa[0]['ie'], 6, 3).'.'.substr($campos_empresa[0]['ie'], 9, 3);
$telefone_comercial = $campos_empresa[0]['telefone_comercial'];
$ddd_comercial  = $campos_empresa[0]['ddd_comercial'];

//CFOP ...
$sql = "SELECT `id_cfop_revenda`, `cfop`, CONCAT(`cfop`, '.', `num_cfop`) AS cfop_ind 
        FROM `cfops` 
        WHERE `id_cfop` = '$id_cfop' 
        AND `ativo` = '1' LIMIT 1 ";
$campos_cfop    = bancos::sql($sql);
$cfops          = $campos_cfop[0]['cfop_ind'];
//Significa que est· atrelada uma CFOP de Revenda p/ a CFOP Industrial ...
if($campos_cfop[0]['id_cfop_revenda'] != 0) {
//Mais antes eu verifico se existe pelo menos um PA em que a OperaÁ„o È do Tipo Revenda ...
    $sql = "SELECT pa.`id_produto_acabado` 
            FROM `nfs_itens` nfsi 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = nfsi.`id_produto_acabado` AND pa.`operacao` = '1' 
            WHERE nfsi.`id_nf` = '$_GET[id_nf]' LIMIT 1 ";
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
/******************************************Impress„o da Duplicata******************************************/
/**********************************************************************************************************/
$calculo_total_impostos = calculos::calculo_impostos(0, $_GET['id_nf'], 'NF');
$valor_total_nota       = $calculo_total_impostos['valor_total_nota'];
$valor_duplicata	= faturamentos::valor_duplicata($_GET['id_nf'], $suframa_nf, $nota_sgd, $id_pais);

//Verifica qual È o PaÌs para poder imprimir os sÌmbolos corretos de R$
if($id_pais != 31) {
    $tipo_moeda = 'U$';
/*FunÁ„o que ser· utilizada somente quando o Cliente for estrangeiro, pois nesse caso, 
nÛs gravamos o valor da Duplicata em U$ na Base de Dados, daÌ passo o Valor da NF em
reais e l· dentro da FunÁ„o ele divide pelo n˙mero de Vencimentos ...*/
    $valor_duplicata_rs = faturamentos::valor_duplicata_rs($valor_total_nota, $qtde_duplicatas);
}else {
    $tipo_moeda = 'R$';
}

if($qtde_duplicatas == 1) {//Uma ˙nica Via ...
    $duplicata_array = array($numero_nf);
}else {//Mais de uma via ...
    $duplicata_array = array($numero_nf.'-A', $numero_nf.'-B', $numero_nf.'-C', $numero_nf.'-D');
}

$data_vencimento_array = array($data_vencimento1, $data_vencimento2, $data_vencimento3, $data_vencimento4);

for($i = 0; $i < $qtde_duplicatas; $i++) {
    //Quando eu estiver na Terceira Via, ent„o mando Inserir uma Nova P·gina ...
    if($i == 2) $pdf->AddPage();
//Imagem a ser exibida na Impress„o da NF ...
    if($id_empresa_nota == 1) {//Se o usu·rio escolheu AlbafÈr ...
        $pdf->Image('../../../../../imagem/marcas/cabri_preto.jpg', 112, $pdf->GetY() + 5.4, 13, 13, 'JPG');
    }else {//Se o usu·rio escolheu Tool Master ...
        $pdf->Image('../../../../../imagem/marcas/tool_preto.jpg', 98, $pdf->GetY() + 7, 45, 10, 'JPG');
    }
    $pdf->SetFont('Arial', 'B', 13.4);	
//Dados de Empresa ...
    $pdf->Cell($GLOBALS['ph'] * 70, 5, $campos_empresa[0]['razaosocial'], 'TLR', 0, 'L');
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell($GLOBALS['ph'] * 30, 5, 'BANCO:', 'TLR', 1, 'L');
    $pdf->Cell($GLOBALS['ph'] * 70, 5, strtoupper($campos_empresa[0]['endereco']).', '.$campos_empresa[0]['numero'].' - BAIRRO: '.strtoupper($campos_empresa[0]['bairro']), 'LR', 0, 'L');
    $pdf->Cell($GLOBALS['ph'] * 30, 5, '', 'LR', 1, 'L');
    $pdf->Cell($GLOBALS['ph'] * 70, 5, 'CEP: '.$campos_empresa[0]['cep'].' - MUNICÕPIO: '.strtoupper($campos_empresa[0]['cidade']).' - ESTADO: '.strtoupper($campos_empresa[0]['sigla']), 'LR', 0, 'L');
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell($GLOBALS['ph'] * 30, 5, '', 'LR', 1, 'C');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell($GLOBALS['ph'] * 70, 5, 'CNPJ: '.$cnpj.' - I. EST. N.∫ '.$ie, 'BLR', 0, 'L');
    $pdf->Cell($GLOBALS['ph'] * 30, 5, '', 'BLR', 1, 'L');
	
//Natureza da OperaÁ„o ...
    $pdf->Cell($GLOBALS['ph'] * 25, 5, 'NATUREZA DA OPERA«√O: ', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph'] * 25, 5, $cfops, 1, 0, 'C');

//Data de Emiss„o ...
    $pdf->Cell($GLOBALS['ph'] * 25, 5, 'DATA DE EMISS√O: ', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph'] * 25, 5, $data_emissao, 1, 1, 'C');

//EspaÁo v·zio do Banco ...
    $pdf->Cell($GLOBALS['ph'] * 25, 7, '', 'TLR', 0, 'C');
    $pdf->SetFont('Arial', '', 9);
//Juros ...
    $pdf->Cell($GLOBALS['ph'] * 37.5, 7, 'JUROS: ', 1, 0, 'L');
//Abatimento ...
    $pdf->Cell($GLOBALS['ph'] * 37.5, 7, 'ABATIMENTO: ', 1, 1, 'L');

    $pdf->SetFont('Arial', 'B', 16);
//CÛpia ...
    $pdf->Cell($GLOBALS['ph'] * 25, 7, 'C”PIA ', 'LRC', 0, 'C');
    $pdf->SetFont('Arial', '', 9);
//Data Pagto ...
    $pdf->Cell($GLOBALS['ph'] * 37.5, 7, 'DATA PAGTO: ', 1, 0, 'L');
//Valor Pagto ...
    $pdf->Cell($GLOBALS['ph'] * 37.5, 7, 'VALOR PAGO: ', 1, 1, 'L');

//EspaÁo v·zio do Banco ...
    $pdf->Cell($GLOBALS['ph'] * 25, 7, '', 'BLR', 0, 'L');
//Nosso N.∫ ...
    $pdf->Cell($GLOBALS['ph'] * 75, 7, 'NOSSO N.∫: ', 1, 1, 'L');
	
    $pdf->SetFont('Arial', 'B', 8);
//CartÛrio - Data Entrada ...
    $pdf->Cell($GLOBALS['ph'] * 38, 7, $pdf->Rect($pdf->GetX() + 1.2, $pdf->GetY() + 0.8, 3.5, 3.5).'      CART”RIO - DATA DE ENTRADA: ', 1, 0, 'L');
//Taxa Despesa Banc·ria ...
    $pdf->Cell($GLOBALS['ph'] * 34, 7, 'TAXA DESPESA BANC¡RIA: ', 1, 0, 'L');
//Taxa de CartÛrio ...
    $pdf->Cell($GLOBALS['ph'] * 28, 7, 'TAXA DE CART”RIO: ', 1, 1, 'L');
//Protestado - Data Protesto ...
    $pdf->Cell($GLOBALS['ph'] * 100, 7, $pdf->Rect($pdf->GetX() + 1.2, $pdf->GetY() + 0.8, 3.5, 3.5).'      PROTESTADO - DATA DO PROTESTO: ', 1, 1, 'L');
	
    $pdf->SetFont('Arial', '', 9);
//RÛtulo Principal ...
    $pdf->Cell($GLOBALS['ph'] * 100, 7, 'NOTA FISCAL', 1, 1, 'C');
//Nota Fiscal N.∫
    $pdf->Cell($GLOBALS['ph'] * 25, 5, 'FATURA N.∫ ', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 25, 5, 'DUPLICATA - VALOR R$ ', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 25, 5, 'N.∫ DE ORDEM ', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 25, 5, 'VENCIMENTO ', 1, 1, 'C');

    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell($GLOBALS['ph'] * 25, 5, $numero_nf, 1, 0, 'C');
    $pdf->SetFont('Arial', 'B', 11);
//Se for de fora do Brasil ...
    if($id_pais != 31) {
        $pdf->Cell($GLOBALS['ph'] * 25, 5, number_format($valor_duplicata_rs[$i], 2, ',', '.').' / U$ '.number_format($valor_duplicata[$i], 2, ',', '.'), 1, 0, 'C');
    }else {
        $pdf->Cell($GLOBALS['ph'] * 25, 5, number_format($valor_duplicata[$i], 2, ',', '.'), 1, 0, 'C');
    }
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell($GLOBALS['ph'] * 25, 5, $duplicata_array[$i], 1, 0, 'C');
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell($GLOBALS['ph'] * 25, 5, $data_vencimento_array[$i], 1, 1, 'C');
	
    $pdf->SetFont('Arial', '', 9);
//Nome do Sacado - Cliente
    $pdf->Cell($GLOBALS['ph'] * 25, 5, 'NOME DO SACADO: ', 'TL', 0, 'L');
    $pdf->Cell($GLOBALS['ph'] * 75, 5, $cod_cliente.'-'.$cliente, 'TR', 1, 'L');
    $pdf->Cell($GLOBALS['ph'] * 25, 5, 'E-MAIL FINANCEIRO: ', 'TL', 0, 'L');
    $pdf->Cell($GLOBALS['ph'] * 75, 5, $email_financeiro, 'TR', 1, 'L');
	
    if($id_pais == 31) {//Se for do Brasil ...
        $pdf->Cell($GLOBALS['ph'] * 25, 5, 'ENDERE«O: ', 'L', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 52, 5, $endereco, 0, 0, 'L');
//Bairro
        $pdf->Cell($GLOBALS['ph'] * 7, 5, 'BAIRRO: ', 0, 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 16, 5, $bairro, 'R', 1, 'L');

//CEP / MunicÌpio - Cidade
        $pdf->Cell($GLOBALS['ph'] * 25, 5, 'CEP / MUNICÕPIO: ', 'L', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 52, 5, $cep.' / '.$cidade, 0, 0, 'L'); 
        $pdf->Cell($GLOBALS['ph'] * 7, 5, 'ESTADO: ', 0, 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 16, 5, $uf, 'R', 1, 'L');
    }else {//Se for Estrangeiro ...
        //Busca do nome do PaÌs ...
        $sql = "SELECT `pais` 
                FROM `paises` 
                WHERE `id_pais` = '$id_pais' LIMIT 1 ";
        $campos_pais = bancos::sql($sql);
//EndereÁo
        $pdf->Cell($GLOBALS['ph']*25, 5, 'ENDERE«O: ', 'L', 0, 'L');
        $pdf->Cell($GLOBALS['ph']*75, 5, $endereco, 'R', 1, 'L');
//MunicÌpio - PaÌs
        $pdf->Cell($GLOBALS['ph']*25, 5, 'MUNICÕPIO / PAÕS:', 'L', 0, 'L');
        $pdf->Cell($GLOBALS['ph']*75, 5, $cidade.' / '.$campos_pais[0]['pais'], 'R', 1, 'L');
    }
	
    if(strlen($cnpj_cpf) == 18) {//CNPJ ...
        $pdf->Cell($GLOBALS['ph'] * 25, 5, 'CNPJ: ', 'LB', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 20, 5, $cnpj_cpf, 'B', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 15, 5, 'INSCR. EST. N∫: ', 'B', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 40, 5, $insc_estadual, 'RB', 1, 'L');
    }else if(strlen($cnpj_cpf) == 14) {//CPF ...
        $pdf->Cell($GLOBALS['ph'] * 25, 5, 'CPF: ', 'LB', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 40, 5, $cnpj_cpf, 'B', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 8, 5, 'RG: ', 'B', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 27, 5, $rg, 'RB', 1, 'L');
    }

//Vendedores - Representante(s) ...
    $pdf->Cell($GLOBALS['ph'] * 25, 5, 'VENDEDORES: ', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph'] * 25, 5, $representantes, 1, 0, 'L');

//Copiador - Login ...
//Busca do nome do Usu·rio logado ...
    $sql = "SELECT `login` 
            FROM `logins` 
            WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
    $campos_login = bancos::sql($sql);
    $pdf->Cell($GLOBALS['ph'] * 25, 5, 'COPIADOR: ', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph'] * 25, 5, $campos_login[0]['login'], 1, 1, 'L');

//Vendedores - Representante(s) ...
    $pdf->Cell($GLOBALS['ph'] * 25, 7, 'VALOR POR EXTENSO: ', 1, 0, 'L');
//Se for de fora do Brasil ...
    if($id_pais != 31) {
        $pdf->Cell($GLOBALS['ph'] * 75, 7, ucfirst(extenso($valor_duplicata_rs[$i], '', 1)), 1, 1, 'L');
    }else {
        $pdf->Cell($GLOBALS['ph'] * 75, 7, ucfirst(extenso($valor_duplicata[$i], '', 1)), 1, 1, 'L');
    }
//ObservaÁ„o ...
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell($GLOBALS['ph'] * 25, 7, 'OBSERVA«√O: ', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph'] * 75, 7, '', 1, 1, 'L');
    $pdf->Cell($GLOBALS['ph'] * 100, 7, '', 1, 1, 'L');
    $pdf->Cell($GLOBALS['ph'] * 100, 7, '', 1, 1, 'L');
    $pdf->Cell($GLOBALS['ph'] * 100, 7, '', 1, 1, 'L');
    $pdf->Cell($GLOBALS['ph'] * 100, 7, '', 1, 1, 'L');
    $pdf->Ln(1);
    $pdf->Cell($GLOBALS['ph'] * 100, 5, '_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _', 0, 1, 'L');
    $pdf->Ln(2);
}
chdir('../../../../../pdf');
$file='../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>