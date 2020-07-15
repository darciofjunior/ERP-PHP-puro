<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/data.php');
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../../../');
define('FPDF_FONTPATH', 'font/');

//Com o id_antecipação, eu busco quem é o Fornecedor
$sql = "SELECT p.`id_fornecedor` 
        FROM `antecipacoes` a 
        INNER JOIN `pedidos` p ON p.`id_pedido` = a.`id_pedido` 
        WHERE a.`id_antecipacao` = '$_GET[id_antecipacao]' LIMIT 1 ";
$campos 	= bancos::sql($sql);
$id_fornecedor 	= $campos[0]['id_fornecedor'];

//Busca os dados normalmente agora com o id_fornecedor ...
$sql = "SELECT f.*, ufs.`sigla`  
        FROM `fornecedores` f 
        LEFT JOIN `ufs` ON ufs.`id_uf` = f.`id_uf` 
        WHERE f.`id_fornecedor` = '$id_fornecedor' 
        AND f.`ativo` = '1' LIMIT 1 ";
$campos = bancos::sql($sql);

$pdf = new FPDF();
$pdf->Open();
$pdf->SetLeftMargin(1);
$pdf->AddPage();
$pdf->SetLeftMargin(1);

$razaosocial            = $campos[0]['razaosocial'];
$endereco 		= $campos[0]['endereco'];

if(!empty($campos[0]['cnpj_cpf'])) {//Campo está preenchido ...
    if(strlen($campos[0]['cnpj_cpf']) == 11) {//CPF ...
        $rotulo_cnpj_cpf    = 'CPF:';
        $cnpj_cpf           = substr($campos[0]['cnpj_cpf'], 0, 3).'.'.substr($campos[0]['cnpj_cpf'], 3, 3).'.'.substr($campos[0]['cnpj_cpf'], 6, 3).'-'.substr($campos[0]['cnpj_cpf'], 9, 2);
    }else {//CNPJ ...
        $rotulo_cnpj_cpf    = 'CNPJ:';
        $cnpj_cpf           = substr($campos[0]['cnpj_cpf'], 0, 2).'.'.substr($campos[0]['cnpj_cpf'], 2, 3).'.'.substr($campos[0]['cnpj_cpf'], 5, 3).'/'.substr($campos[0]['cnpj_cpf'], 8, 4).'-'.substr($campos[0]['cnpj_cpf'], 12, 2);
    }
}

$cep 			= ($campos[0]['cep'] == '00000-000') ? '' : $campos[0]['cep'];
$cidade 		= $campos[0]['cidade'];
$estado 		= $campos[0]['sigla'];
$bairro 		= $campos[0]['bairro'];
$fone1 			= $campos[0]['fone1'];
if(empty($fone1)) $fone1 = '';

$fone2 			= $campos[0]['fone2'];
$fone2 			= (empty($fone2)) ? '' : ' / '.$fone2;
$fones 			= $fone1.$fone2;
$fax 			= $campos[0]['fax'];

$sql = "SELECT fp.`banco`, fp.`agencia`, fp.`num_cc`, fp.`correntista` 
        FROM `antecipacoes` a 
        INNER JOIN `fornecedores_propriedades` fp ON fp.`id_fornecedor_propriedade` = a.`id_fornecedor_propriedade` 
        WHERE a.`id_antecipacao` = '$id_antecipacao' LIMIT 1 ";
$campos_fornecedores_propriedades = bancos::sql($sql);
if(count($campos_fornecedores_propriedades) == 1) {
    $bank 		= (!empty($campos_fornecedores_propriedades[0]['banco'])) ? $campos_fornecedores_propriedades[0]['banco'] : '';
    $agencia 		= (!empty($campos_fornecedores_propriedades[0]['agencia'])) ? '*AG:'.$campos_fornecedores_propriedades[0]['agencia'] : '';
    $conta_corrente     = (!empty($campos_fornecedores_propriedades[0]['num_cc'])) ? '*C/C:'.$campos_fornecedores_propriedades[0]['num_cc'] : '';
    $correntista        = (!empty($campos_fornecedores_propriedades[0]['correntista'])) ? '*'.$campos_fornecedores_propriedades[0]['correntista'] : '';
}//Fim dos dados bancários ...

//Geração do Relatório PDF
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(208, 5, 'VIA FINANCEIRO', 0, 1, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(208, 5, 'DADOS BANCÁRIOS', 1, 1, 'C');
$pdf->Cell(208, 5, '', 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(34, 5, 'FORNECEDOR:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(172, 5, $id_fornecedor.' - '.$razaosocial, 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(34, 5, 'ENDEREÇO:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(172, 5, $endereco.', '.$campos[0]['num_complemento'], 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(34, 5, 'BAIRRO:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(32, 5, $bairro, 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(38, 5, 'CIDADE:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(45, 5, $cidade.' - '.$estado, 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(38, 5, 'CEP:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(30, 5, $cep, 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(34, 5, 'FONE(S):', 0, 0, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(32, 5, $fones, 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(38, 5, 'FAX:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(30, 5, $fax, 0, 1, 'L');

$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(34, 5, 'BANCO:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(32, 5, $bank, 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(38, 5, 'AGÊNCIA:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(170, 5, $agencia, 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(34, 5, 'No. C/C:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(32, 5, $conta_corrente, 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(38, 5, 'CORRENTISTA:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(170, 5, $correntista, 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(34, 5, $rotulo_cnpj_cpf, 0, 0, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(32, 5, $cnpj_cpf, 0, 1, 'L');

$sql = "SELECT a.*, tp.`pagamento`, p.`tipo_nota`, p.`desc_ddl`, CONCAT(tm.`simbolo`, ' ') AS tipo_moeda 
        FROM `antecipacoes` a 
        INNER JOIN `pedidos` p ON p.`id_pedido` = a.`id_pedido` 
        INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = p.`id_tipo_moeda` 
        INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = a.`id_tipo_pagamento_recebimento` 
        WHERE a.`id_antecipacao` = '$id_antecipacao' ORDER BY a.`data` ";
$campos = bancos::sql($sql);
$linhas = count($campos);

$descricao_ddl  = $campos[0]['desc_ddl'];
$qtde_caracter 	= strlen($descricao_ddl);

for($i = 0; $i < $qtde_caracter; $i++) {
    if(substr($descricao_ddl, $i, 1) != '-') {
        $descricao_ddl = $descricao_ddl.substr($descricao_ddl, $i, 1);
    }else {
        $i = $qtde_caracter;
    }
}
$descricao_ddl 	= trim($descricao_ddl);
$tipo           = ($campos[0]['tipo_nota'] == 1) ? 'NF' : 'SGD';
$id_pedido      = $campos[0]['id_pedido'];
$pagamento      = $campos[0]['pagamento'];

$sql = "SELECT e.`nomefantasia` 
        FROM `pedidos` p 
        INNER JOIN `empresas` e ON e.`id_empresa` = p.`id_empresa` 
        WHERE p.`id_pedido` = '$id_pedido' LIMIT 1 ";
$campos_empresa	= bancos::sql($sql);
$nome_fantasia 	= $campos_empresa[0]['nomefantasia'];

$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 13);
$pdf->Cell(208, 5, 'ANTECIPAÇÃO N.º '.$id_antecipacao.'   DO PEDIDO N.º '.$id_pedido.' - '.$nome_fantasia.' - '.$tipo, 1, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 5, 'VALOR', 1, 0, 'C');
$pdf->Cell(34, 5, 'DATA EMISSÃO', 1, 0, 'C');
$pdf->Cell(34, 5, 'DATA VENC.', 1, 0, 'C');
$pdf->Cell(90, 5, 'TIPO DE PAGAMENTO', 1, 1, 'C');

for($i = 0; $i < $linhas; $i++) {
    $pdf->Cell(50, 5, $campos[$i]['tipo_moeda'].str_replace('.', ',', $campos[$i]['valor']), 1, 0, 'C');
    $data_emissao = $campos[$i]['data_sys'];
    $data_emissao = data::datetodata($data_emissao, '/');
    $data_inclusao = $campos[$i]['data'];
    $data_inclusao = data::datetodata($data_inclusao, '/');
    $data_incusao = data::adicionar_data_hora($data_inclusao, $descricao_ddl);
    $pdf->Cell(34, 5, $data_emissao, 1, 0, 'C');
    $pdf->Cell(34, 5, $data_inclusao, 1, 0, 'C');
    $pdf->Cell(90, 5, $pagamento, 1, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(27, 5, 'OBSERVAÇÃO: ', 'TL', 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    $observacao = $campos[$i]['observacao'];
    $contador_letra = strlen($observacao);
    if($contador_letra > 130) {
        $observacao1 = substr($observacao, 0, 130);
        $observacao2 = substr($observacao, 130, $contador_letra);
    }else {
        $observacao1 = $observacao;
    }
    $pdf->Cell(181, 5, ' '.$observacao1, 'TR', 1, 'L');
    $pdf->Cell(208, 5, $observacao2, 'LBR', 1, 'L');
    $pdf->SetFont('Arial', '', 12);
}

//Calculo para verificar em qual localização vai estar o rodapé
$calculo = 10 - $linhas;
//Rodapé da Primeira Via
for($i = 0; $i < $calculo; $i++) $pdf->Cell(10, 5, '', 0, 1);

$pdf->SetFont('Arial', '', 8);
$pdf->Cell(14, 5, 'VISTOS', 'TLR', 0, 'L');
$pdf->Cell(46, 5, 'EMISSOR', 'TLR', 0, 'L');
$pdf->Cell(48, 5, 'CONFERENTE', 'TLR', 0, 'L');
$pdf->Cell(48, 5, 'GERÊNCIA', 'TLR', 0, 'L');
$pdf->Cell(52, 5, 'DIRETORIA', 'TLR', 1, 'L');
$pdf->Cell(14, 5, '', 'BLR', 0, 'L');
$pdf->Cell(46, 5, '', 'BLR', 0, 'L');
$pdf->Cell(48, 5, '', 'BLR', 0, 'L');
$pdf->Cell(48, 5, '', 'BLR', 0, 'L');
$pdf->Cell(52, 5, '', 'BLR', 1, 'L');
$pdf->Ln(5);
//Linha divisória
$pdf->Line(1, 153, 221, 153);
//Via de Compras
//Dados do Fornecedor
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(208, 5, 'VIA COMPRAS', 0, 1, 'R');
$pdf->Cell(208, 5, '', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(38, 5, 'FORNECEDOR:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(170, 5, $id_fornecedor.' - '.$razaosocial, 0, 1, 'L');
$pdf->Ln(5);

//Dados das Antecipações
$pdf->SetFont('Arial', 'B', 13);
$pdf->Cell(208, 5, 'ANTECIPAÇÃO Nº '.$id_antecipacao.' DO PEDIDO N.º '.$id_pedido.' - '.$nome_fantasia.' - '.$tipo, 1, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 5, 'VALOR', 1, 0, 'C');
$pdf->Cell(34, 5, 'DATA EMISSÃO', 1, 0, 'C');
$pdf->Cell(34, 5, 'DATA VENC.', 1, 0, 'C');
$pdf->Cell(90, 5, 'TIPO PAGAMENTO', 1, 1, 'C');

for($i = 0; $i < $linhas; $i++) {
    $pdf->Cell(50, 5, $campos[$i]['tipo_moeda'].str_replace('.', ',', $campos[$i]['valor']), 1, 0, 'C');
    $data_emissao = $campos[$i]['data_sys'];
    $data_emissao = data::datetodata($data_emissao, '/');
    
    $data_inclusao = $campos[$i]['data'];
    $data_inclusao = data::datetodata($data_inclusao, '/');
    
    $pdf->Cell(34, 5, $data_emissao, 1, 0, 'C');
    $pdf->Cell(34, 5, $data_inclusao, 1, 0, 'C');
    $pdf->Cell(90, 5, $pagamento, 1, 1, 'C');

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(30, 5, 'DADOS BANCARIOS:', 'TBL', 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(100, 5, $bank.$agencia.$conta_corrente.$correntista, 'TB', 1, 'L');

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(27, 5, 'OBSERVAÇÃO: ', 'TL', 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    $observacao = $campos[$i]['observacao'];
    $contador_letra = strlen($observacao);
    if($contador_letra > 130) {
        $observacao1 = substr($observacao, 0, 130);
        $observacao2 = substr($observacao, 130, $contador_letra);
    }else {
        $observacao1 = $observacao;
    }
    $pdf->Cell(181, 5, ' '.$observacao1, 'TR', 1, 'L');
    $pdf->Cell(208, 5, $observacao2, 'LBR', 1, 'L');
}

//Vistos
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(208, 5, '', 0, 1, 'L');
$pdf->Cell(14, 5, 'VISTOS', 'TLR', 0, 'L');
$pdf->Cell(46, 5, 'EMISSOR', 'TLR', 0, 'L');
$pdf->Cell(48, 5, 'CONFERENTE', 'TLR', 0, 'L');
$pdf->Cell(48, 5, 'GERÊNCIA', 'TLR', 0, 'L');
$pdf->Cell(52, 5, 'DIRETORIA', 'TLR', 1, 'L');
$pdf->Cell(14, 5, '', 'BLR', 0, 'L');
$pdf->Cell(46, 5, '', 'BLR', 0, 'L');
$pdf->Cell(48, 5, '', 'BLR', 0, 'L');
$pdf->Cell(48, 5, '', 'BLR', 0, 'L');
$pdf->Cell(52, 5, '', 'BLR', 1, 'L');

//Cálculo para gerar o Rodapé da Pagina
$calculo = 9 - $linhas;
for($i = 0; $i < $calculo; $i++)    $pdf->Cell(10, 5, '', 0, 1);

//Vistos
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(208, 5, 'Inclusão de antecipação na Nota Fiscal => Não esquecer de incluir antecipação quando der entrada na nota fiscal.', 1, 1);
$pdf->Cell(208, 5, '', 0, 1);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(14, 5, 'VISTOS', 'TLR', 0, 'L');
$pdf->Cell(46, 5, 'EMISSOR', 'TLR', 0, 'L');
$pdf->Cell(48, 5, 'CONFERENTE', 'TLR', 0, 'L');
$pdf->Cell(48, 5, 'GERÊNCIA', 'TLR', 0, 'L');
$pdf->Cell(52, 5, 'DIRETORIA', 'TLR', 1, 'L');
$pdf->Cell(14, 5, '', 'BLR', 0, 'L');
$pdf->Cell(46, 5, '', 'BLR', 0, 'L');
$pdf->Cell(48, 5, '', 'BLR', 0, 'L');
$pdf->Cell(48, 5, '', 'BLR', 0, 'L');
$pdf->Cell(52, 5, '', 'BLR', 1, 'L');

chdir('../../../../../pdf');
$file='../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><SCRIPT>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>