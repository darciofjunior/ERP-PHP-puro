<?
require('../../../../lib/pdf/fpdf.php');
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/estoque_i_c/nivel_estoque/index.php', '../../../../');

function cabecalho($id_cotacao) {
    global $pdf;
    $pdf->Image('../../../../imagem/logosistema.jpg', 15, 3, 38, 38, 'JPG');
    $pdf->SetLeftMargin(10);
    $pdf->SetFont('Arial', 'BI', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(4);
    $pdf->Cell(50, 10, '', 0, 0, 'L');
    $pdf->SetFont('Arial', 'BI', 11);
/******************************************Tool Master******************************************/
    //Busca de Dados da Empresa Tool Master ...
    $sql = "SELECT `razaosocial`, `cnpj` 
            FROM `empresas` 
            WHERE `id_empresa` = '2' LIMIT 1 ";
    $campos_empresa = bancos::sql($sql);
    $cnpj           = substr($campos_empresa[0]['cnpj'], 0, 2).'.'.substr($campos_empresa[0]['cnpj'], 2, 3).'.'.substr($campos_empresa[0]['cnpj'], 5, 3).'/'.substr($campos_empresa[0]['cnpj'], 8, 4).'-'.substr($campos_empresa[0]['cnpj'], 12, 2);
    
    $pdf->Cell(130, 5, '* '.$campos_empresa[0]['razaosocial'].'                       CNPJ: '.$cnpj, 0, 1, 'L');
    $pdf->Cell(50, 10, '', 0, 0, 'L');
/******************************************Albafér******************************************/
//Busca de Dados da Empresa Albafer ...
    $sql = "SELECT * 
            FROM `empresas` 
            WHERE `id_empresa` = '1' LIMIT 1 ";
    $campos_empresa = bancos::sql($sql);
    $cnpj           = substr($campos_empresa[0]['cnpj'], 0, 2).'.'.substr($campos_empresa[0]['cnpj'], 2, 3).'.'.substr($campos_empresa[0]['cnpj'], 5, 3).'/'.substr($campos_empresa[0]['cnpj'], 8, 4).'-'.substr($campos_empresa[0]['cnpj'], 12, 2);

    $pdf->Cell(130, 5, '* '.$campos_empresa[0]['razaosocial'].'   CNPJ: '.$cnpj, 0, 1, 'L');
    $pdf->SetFont('Arial', 'BI', 10);
    $pdf->Cell(50, 10, '', 0, 0, 'L');
    $pdf->Cell(130, 5, $campos_empresa[0]['endereco'].', Nº '.$campos_empresa[0]['numero'].' - '.strtoupper($campos_empresa[0]['bairro']).' - '.strtoupper($campos_empresa[0]['cidade']).' - CEP: '.$campos_empresa[0]['cep'], 0, 1, 'L');
    $pdf->Cell(50, 10, '', 0, 0, 'L');
    $pdf->Cell(60, 5, 'FONE/FAX: ('.$campos_empresa[0]['ddd_comercial'].') '.$campos_empresa[0]['telefone_comercial'], 0, 0, 'L');
    $pdf->Cell(70, 5, 'E-MAIL: compras@grupoalbafer.com.br', 0, 0, 'L');
    $pdf->Cell(70, 5, 'SITE: '.$campos_empresa[0]['home'], 0, 0, 'L');

    $pdf->Ln(6);
    $pdf->SetLeftMargin(3);
    $pdf->Cell(50, 5, '', 0, 0, 'L');
    $mes = intval(date('m'));
    $pdf->Cell(130, 5, 'São Paulo, '.date('d').' de '.data::mes($mes).' de '.date('Y').'.', 0, 0, 'L');
    $pdf->SetFont('Arial', 'BI', 12);
    $pdf->Cell(30, 5, 'Cotação Nº '.$id_cotacao, 0, 1, 'L');
    $pdf->SetFont('Arial', 'BI', 10);
    $pdf->Ln(7);
    $pdf->SetLeftMargin(3);
    $pdf->Line(1,43,280,43);
}

function subcabecalho($fornecedor, $fone1, $fax, $departamento, $ac, $funcionario, $email, $site) {
    global $pdf;
    $pdf->SetLeftMargin(3);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(25, 5, 'Fornecedor: ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(130, 5, $fornecedor, 0, 0, 'L');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(30, 5, 'Departamento: ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(75, 5, $departamento, 0, 1, 'L');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(25, 5, 'Fone:', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(30, 5, $fone1.' / ', 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(10, 5, 'Fax:', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(90, 5, $fax, 0, 0, 'L');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(30, 5, 'E-Mail', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(75, 5, $email, 0, 1, 'L', '', 'mailto:'.$email.'?subject=aaaa&body=xxxx&attachment='.chunk_split(base64_encode(file_get_contents('gym.docx'))));

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(25, 5, 'A/C:', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(130, 5, $ac, 0, 0, 'L');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(30, 5, 'Comprador: ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(75, 5, $funcionario, 0, 1, 'L');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(25, 5, 'Site:', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(130, 5, $site, 0, 1, 'L');
    $pdf->SetFont('Arial', 'B', 12);

    $pdf->SetLeftMargin(3);
    $pdf->SetTextColor(0,0,255);
    $pdf->Ln(2);
    $pdf->Cell(100, 5, 'Passamos abaixo os dados necessários p/ orçamento de fornecimento para os seguintes itens:', 0, 1, 'L');
    $pdf->Ln(2);
    $pdf->SetTextColor(0, 0, 0);
}

function tabela() {//Rótulos dos campos
    global $pdf;
    $pdf->SetLeftMargin(3);
    $pdf->SetFont('Arial', '', 10);
//Primeira Linha
    $pdf->Cell(75, 5, 'Discriminação', 'TLR', 0, 'C');
    $pdf->Cell(20, 5, 'Marca', 'TLR', 0, 'C');
    $pdf->Cell(21, 5, 'Qtde Pedida', 'TLR', 0, 'C');
    $pdf->Cell(28, 5, 'Preço Fat. Nac.', 'TLR', 0, 'C');
    $pdf->Cell(30, 5, 'Prazo Pgto. Dias', 'TLR', 0, 'C');
    $pdf->Cell(23, 5, 'Desc A/V %', 'TLR', 0, 'C');
    $pdf->Cell(21, 5, 'Desc SGD %', 'TLR', 0, 'C');
    $pdf->Cell(12, 5, 'IPI %', 'TLR', 0, 'C');
    $pdf->Cell(15, 5, 'ICMS %', 'TLR', 0, 'C');
    $pdf->Cell(25, 5, 'Prazo de Ent', 'TLR', 1, 'C');
}

define('FPDF_FONTPATH','font/');
$pdf = new FPDF();
$pdf->FPDF('L', 'mm', 'Letter');
$pdf->SetLeftMargin(3);
$pdf->Open();

$vetor_fornecedores = explode(',', $_POST['id_fornecedores']);

foreach ($vetor_fornecedores as $i => $id_fornecedor) {
    $sql = "SELECT razaosocial, fone1, email, fax, site 
            FROM `fornecedores` 
            WHERE `id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
    $campos     = bancos::sql($sql);

    if($_POST['chkt_lista_fornecedor'] == 'S') {
        $trazer_campos_lista    = ", fpi.* ";
        $inner_join_lista       = " INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.id_produto_insumo = ci.id_produto_insumo AND fpi.id_fornecedor = '$id_fornecedor' ";
    }
    
    //Lista todos os Itens da Cotação passada por parâmetro ...
    $sql = "SELECT g.referencia, pi.discriminacao, pi.prazo_entrega, ci.qtde_pedida $trazer_campos_lista 
            FROM `cotacoes_itens` ci 
            INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = ci.id_produto_insumo 
            INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
            $inner_join_lista 
            WHERE ci.id_cotacao = '$_POST[id_cotacao]' ORDER BY pi.discriminacao ";
    $campos_lista   = bancos::sql($sql);
    $linhas_lista   = count($campos_lista);
    $pdf->AddPage();//Insere uma Nova Página e reseta as variáveis p/ umk Novo Fornecedor ...
    $valor_pagina = '';
    $registro = 0;
    $pagina = 1;
    
    cabecalho($_POST['id_cotacao']);
    subcabecalho($campos[0]['razaosocial'], $campos[0]['fone1'], $campos[0]['fax'], $_POST['txt_departamento'][$i], $_POST['txt_ac_cuidados'][$i], $_POST['funcionario'], $campos[0]['email'], $campos[0]['site']);
    tabela($campos[0]['razaosocial'], $campos[0]['fone1'], $campos[0]['fax'], $_POST['txt_departamento'][$i], $_POST['txt_ac_cuidados'][$i], $_POST['funcionario'], $pagina);
    
    for($i = 0; $i < $linhas_lista; $i++) {
        if(empty($valor_pagina)) {
            if($registro == 22) {//Atingiu o Limite da Página ...
                $pdf->Cell(280, 10, $pagina, 0, 0, 'C');
                $pdf->AddPage();
                $pagina++;
                $registro = 0;
                subcabecalho($campos[0]['razaosocial'], $campos[0]['fone1'], $campos[0]['fax'], $_POST['txt_departamento'][$i], $_POST['txt_ac_cuidados'][$i], $_POST['funcionario'], $campos[0]['email'], $campos[0]['site']);
                tabela();
                $valor_pagina = 1;
            }
        }else {
            if($registro == 22) { //demais codicoes
                $pdf->Cell(280, 10, $pagina, 0, 0, 'C');
                $pdf->AddPage();
                $pagina++;
                $registro = 0;
                subcabecalho($campos[0]['razaosocial'], $campos[0]['fone1'], $campos[0]['fax'], $_POST['txt_departamento'][$i], $_POST['txt_ac_cuidados'][$i], $_POST['funcionario'], $campos[0]['email'], $campos[0]['site']);
                tabela();
                $valor_pagina = 1;
            }
        }

        $preco_faturado = ($campos_lista[$i]['preco_faturado'] == '0.00' || empty($campos_lista[$i]['preco_faturado'])) ? '' : number_format($campos_lista[$i]['preco_faturado'], 2, ',', '.');
        $prazo_pgto     = ($campos_lista[$i]['prazo_pgto_ddl'] == '0.0' || empty($campos_lista[$i]['prazo_pgto_ddl'])) ? '' : number_format($campos_lista[$i]['prazo_pgto_ddl'], 1, ',', '.');
        $desc_avista    = ($campos_lista[$i]['desc_vista'] == '0.0' || empty($campos_lista[$i]['desc_vista'])) ? '' : number_format($campos_lista[$i]['desc_vista'], 1, ',', '.');
        $desc_sgd       = ($campos_lista[$i]['desc_sgd'] == '0.0' || empty($campos_lista[$i]['desc_sgd'])) ? '' : number_format($campos_lista[$i]['desc_sgd'], 1, ',', '.');
        $ipi            = ($campos_lista[$i]['ipi'] == 0 || empty($campos_lista[$i]['ipi'])) ? '' : $campos_lista[$i]['ipi'];
        $icms           = ($campos_lista[$i]['icms'] == 0 || empty($campos_lista[$i]['icms'])) ? '' : $campos_lista[$i]['icms'];
        $prazo_entrega  = ($_POST['chkt_lista_fornecedor'] == 'S') ? number_format($campos_lista[$i]['prazo_entrega'], 2, ',', '.') : '';
		
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(75, 5, $campos_lista[$i]['discriminacao'], 1, 0, 'L'); //Discriminação
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(20, 5, '', 1, 0, 'R');
        $pdf->Cell(21, 5, number_format($campos_lista[$i]['qtde_pedida'], 2, ',', '.'), 1, 0, 'R');//Qtde Pedida
        $pdf->Cell(28, 5, $preco_faturado, 1, 0, 'R');
        $pdf->Cell(30, 5, $prazo_pgto, 1, 0, 'R');
        $pdf->Cell(23, 5, $desc_avista, 1, 0, 'R');
        $pdf->Cell(21, 5, $desc_sgd, 1, 0, 'R');
        $pdf->Cell(12, 5, $ipi, 1, 0, 'R');
        $pdf->Cell(15, 5, $icms, 1, 0, 'R');
        $pdf->Cell(25, 5, $prazo_entrega, 1, 1, 'C');
        $registro++;
    }
    $registro-= 22;
    for($j = 0; $j < $registro; $j++) $pdf->Cell('10', '5', '', 0, 1);
    $pdf->Cell(280, 10, $pagina, 0, 0, 'C');
}

chdir('../../../../pdf');
$file='../../../../pdf/'.basename(tempnam(getcwd()), '').'Cotacao_de_Compras_Grupo_Albafer_'.$_POST['id_cotacao'].'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><SCRIPT>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>