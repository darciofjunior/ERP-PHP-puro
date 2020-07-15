<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
require('../../../../../lib/faturamentos.php');
require('../../../../classes/nf_carta_correcao/class_carta_correcao.php');
session_start('funcionarios');//É necessário termos a Sessão porque o $_SESSION['id_modulo'] está dentro da mesma ...

if($_SESSION['id_modulo'] == 3) {//Se foi acessado do Módulo de Compras ou Principal ...
    segurancas::geral('/erp/albafer/modulo/compras/nf_carta_correcao/itens/consultar.php', '../../../../../');
}else {//Se foi acessado do Módulo de Faturamento ...
    segurancas::geral('/erp/albafer/modulo/faturamento/nf_carta_correcao/itens/consultar.php', '../../../../../');
}

//Busca dos Itens que estão em irregularidade na Carta de Correção ...
$sql = "SELECT cci.id_especificacao, cci.retificacao, e.especificacao 
        FROM `cartas_correcoes_itens` cci 
        INNER JOIN `especificacoes` e ON e.id_especificacao = cci.id_especificacao 
        WHERE cci.`id_carta_correcao` = '$_GET[id_carta_correcao]' ORDER BY e.id_especificacao ";
$campos_itens = bancos::sql($sql);
$linhas_itens = count($campos_itens);
/*Se não existir nenhum Item cadastrado nessa Carta, não tem o porque da mesma existir, então forço o usuário 
a cadastrar pelo menos 1 Item ...*/
if($linhas_itens == 0) {
?>
    <Script Language = 'JavaScript'>
        alert('ESSA CARTA DE CORREÇÃO NÃO POSSUI NENHUMA ESPECIFICAÇÃO CADASTRADA !')
        window.close()
    </Script>
<?
    exit;
}

/////////////////////////////////////// INCIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel		= "P";  // P=> Retrato L=>Paisagem
$unidade		= "mm"; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= "nota_fiscal"; // A3, A4, A5, Letter, Legal, Ofício ...
$pdf=new FPDF($tipo_papel, $unidade, $formato_papel);
global $pv,$ph; //valor baseado em mm do A4
if($formato_papel=="nota_fiscal") {
	if($tipo_papel=="P") {
		$pv=295/100;
		$ph=205/100;
	}else {
		$pv=205/100;
		$ph=295/100;
	}
}else {
	echo "Formato não definido";
}

//Essa variável "$numero_vias", vem por parâmetro ...
for($via_atual = 0; $via_atual < $numero_vias; $via_atual++) {
	$pdf->Open();
	$pdf->SetLeftMargin(10);
	$pdf->SetTopMargin(5);
	$pdf->AddPage();
	$pdf->SetFont('Arial', '', 11);

//Busca da Empresa da NF ...
	$dados          = carta_correcao::dados_nfs($_GET['id_carta_correcao']);
	$id_empresa_nf 	= $dados['id_empresa_nf'];
	$id_nota        = $dados['id_nota'];
	$tipo_nota      = $dados['tipo_nota'];
	if($tipo_nota == 'NFE') {//Se for uma NF de Entrada ...
            $email = 'compras@grupoalbafer.com.br';
            $mensagem_nota = 'Vossa';
	}else {//Se for uma NF de Saída ...
            $email = 'vendas@grupoalbafer.com.br';
            $mensagem_nota = 'Nossa';
	}
	$id_negociador	= $dados['id_negociador'];
	$numero_nf      = $dados['numero_nf'];
	$data_emissao	= $dados['data_emissao'];

//Busca dos Dados da Empresa ...
	$sql = "SELECT * 
                FROM `empresas` 
                WHERE `id_empresa` = '$id_empresa_nf' LIMIT 1 ";
	$campos_empresa = bancos::sql($sql);
	$cnpj 	= substr($campos_empresa[0]['cnpj'], 0, 2).'.'.substr($campos_empresa[0]['cnpj'], 2, 3).'.'.substr($campos_empresa[0]['cnpj'], 5, 3).'/'.substr($campos_empresa[0]['cnpj'], 8, 4).'-'.substr($campos_empresa[0]['cnpj'], 12, 2);
	$ie 	= substr($campos_empresa[0]['ie'], 0, 3).'.'.substr($campos_empresa[0]['ie'], 3, 3).'.'.substr($campos_empresa[0]['ie'], 6, 3).'.'.substr($campos_empresa[0]['ie'], 9, 3);

//Dados da Empresa da NF ...
	$pdf->Cell($GLOBALS['ph']*100, 5, $campos_empresa[0]['razaosocial'], 0, 1, 'C');
	$pdf->Cell($GLOBALS['ph']*100, 5, $campos_empresa[0]['endereco'].', '.$campos_empresa[0]['numero'].' - '.$campos_empresa[0]['bairro'].' - '.$campos_empresa[0]['cidade'].' - CEP: '.$campos_empresa[0]['cep'], 0, 1, 'C');
	$pdf->Cell($GLOBALS['ph']*100, 5, 'FONE/FAX: (11) '.$campos_empresa[0]['telefone_comercial'].' # E-MAIL: '.$email, 0, 1, 'C');
	$pdf->Cell($GLOBALS['ph']*100, 5, 'CNPJ: '.$cnpj.' # INSC.EST: '.$ie, 0, 1, 'C');
	$pdf->Ln(4);

//Comunicado ...
	$pdf->Cell($GLOBALS['ph']*100, 5, 'COMUNICADO DE IRREGULARIDADE EM DOCUMENTO FISCAL', 0, 1, 'C');
	$pdf->Ln(4);

	$vetor_meses = array('', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');

	$pdf->Cell($GLOBALS['ph']*100, 5, 'São Paulo, '.date('d').' de '.$vetor_meses[intval(date('m'))].' de '.date('Y'), 0, 1, 'C');
	$pdf->Ln(7);
//Dados do Cliente, NF ...
	if($tipo_nota == 'NFE') {
		$sql = "SELECT * 
                        FROM `fornecedores` 
                        WHERE `id_fornecedor` = '$id_negociador' LIMIT 1 ";
		$campos_negociador 	= bancos::sql($sql);
		$negociador 		= $campos_negociador[0]['razaosocial'];
		$endereco           	= $campos_negociador[0]['endereco'].' - '.$campos_negociador[0]['bairro'];
		$cep                    = $campos_negociador[0]['cep'];
		$cidade                 = $campos_negociador[0]['cidade'];
		$uf                     = $campos_negociador[0]['uf'];
		$serie                  = 2;
	}else {
		$sql = "SELECT c.*, ufs.sigla 
                        FROM `clientes` c 
                        LEFT JOIN `ufs` ON ufs.id_uf = c.id_uf 
                        WHERE c.`id_cliente` = '$id_negociador' LIMIT 1 ";
		$campos_negociador 	= bancos::sql($sql);
		$negociador 		= $campos_negociador[0]['razaosocial'];
		$endereco               = $campos_negociador[0]['endereco'].', '.$campos_negociador[0]['num_complemento'].' - '.$campos_negociador[0]['bairro'];
		$cep                    = $campos_negociador[0]['cep'];
		$cidade                 = $campos_negociador[0]['cidade'];
		$uf                     = $campos_negociador[0]['sigla'];
		$serie                  = 1;
	}
	$pdf->Cell($GLOBALS['ph']*76.6, 5, 'À', 0, 1, 'L');
	$pdf->Cell($GLOBALS['ph']*76.6, 5, $negociador, 0, 1, 'L');
	$pdf->Cell($GLOBALS['ph']*76.6, 5, $endereco, 0, 1, 'L');
	if(!empty($cep)) {
		$pdf->Cell($GLOBALS['ph']*76.6, 5, 'CEP - '.$cep.' - '.$cidade.' - '.$uf, 0, 1, 'L');
	}
	$pdf->Ln(7);

//Prezados Senhores ...
	$pdf->Cell($GLOBALS['ph']*76.6, 5, 'Prezado(s) Senhor(es)', 0, 1, 'L');
	$pdf->Ln(7);

//Conferência de Documento Fiscal ...
	$pdf->Cell($GLOBALS['ph']*76.6, 5, 'Ref.: Conferência de documento fiscal e comunicação de incorreções da '.$mensagem_nota.' Nota Fiscal n.º '.$numero_nf.' serie '.$serie.' de '.$data_emissao, 0, 1, 'L');
	$pdf->Ln(7);

//Em face ...
	$pdf->MultiCell($GLOBALS['ph']*100, 5, 'Em face do que determina a legislação fiscal vigente, vimos pela presente comunicar-lhe(s) que a Nota Fiscal em referência contém a(s) irregularidade(s) que abaixo apontamos, cuja correção solicitamos seja providenciada imediatamente, considerando as correções adiante efetuadas, além das providências cabíveis:', 0, 1, 'L');
	$pdf->Ln(4);

	$pdf->SetFont('Arial', 'B', 9.5);
//Listagem de todas as especificações cadastradas no Sistema ...
	$pdf->SetFillColor(200, 200, 200);//Cor Cinza
	$pdf->Cell($GLOBALS['ph']*9, 5, 'CÓDIGO', 1, 0, 'C', 1);
	$pdf->Cell($GLOBALS['ph']*26, 5, 'ESPECIFICAÇÃO', 1, 0, 'C', 1);
	$pdf->Cell($GLOBALS['ph']*9, 5, 'CÓDIGO', 1, 0, 'C', 1);
	$pdf->Cell($GLOBALS['ph']*26, 5, 'ESPECIFICAÇÃO', 1, 0, 'C', 1);
	$pdf->Cell($GLOBALS['ph']*9, 5, 'CÓDIGO', 1, 0, 'C', 1);
	$pdf->Cell($GLOBALS['ph']*26, 5, 'ESPECIFICAÇÃO', 1, 1, 'C', 1);

	$pdf->SetFont('Arial', '', 9.5);
	$sql = "SELECT id_especificacao, especificacao 
                FROM especificacoes ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	for($i = 0; $i < $linhas; $i++) {
            if(($i != 0) && (($i + 1) % 3 == 0)) {
                $quebrar_linha = 1;
            }else {
                $quebrar_linha = 0;
            }
            $pdf->Cell($GLOBALS['ph']*9, 5, $campos[$i]['id_especificacao'], 1, 0, 'C');
    //No último registro eu tenho que quebrar a linha mesmo ...
            if($i + 1 == $linhas) {
                $quebrar_linha = 1;
            }
            $pdf->Cell($GLOBALS['ph']*26, 5, $campos[$i]['especificacao'], 1, $quebrar_linha, 'L');
	}
	$pdf->Ln(4);

	$pdf->SetFont('Arial', 'B', 9.5);
//Listagem de todos os Itens cadastrados na Carta de Correção ...
	$pdf->Cell($GLOBALS['ph']*10, 5, 'CÓDIGO', 1, 0, 'C', 1);
	$pdf->Cell($GLOBALS['ph']*25, 5, 'ESPECIFICAÇÃO', 1, 0, 'C', 1);
	$pdf->Cell($GLOBALS['ph']*70, 5, 'RETIFICAÇÃO', 1, 1, 'C', 1);
	
	$pdf->SetFont('Arial', '', 9.5);
	for($i = 0; $i < $linhas_itens; $i++) {
		$pdf->Cell($GLOBALS['ph']*10, 5, $campos_itens[$i]['id_especificacao'], 1, 0, 'C');
		$pdf->Cell($GLOBALS['ph']*25, 5, $campos_itens[$i]['especificacao'], 1, 0, 'L');
		$pdf->Cell($GLOBALS['ph']*70, 5, $campos_itens[$i]['retificacao'], 1, 1, 'L');
	}

//Espaço fixo depois dos Itens da Carta ...
	$pdf->SetY(250);

	$pdf->SetFont('Arial', '', 11);
//Para evitar-se qualquer ...
	$pdf->MultiCell($GLOBALS['ph']*90, 5, 'Para evitar-se qualquer sanção fiscal, solicitamos acusarem o recebimento desta, na cópia que a acompanha, devendo a via de V.S(as) ficar arquivada juntamente com a Nota Fiscal em questão.', 0, 1, 'L');
	$pdf->Ln(7);

//Sem outro ...
	$pdf->Cell($GLOBALS['ph']*76.6, 5, 'Sem outro motivo para o momento, subscrevemo-nos', 0, 1, 'L');
	$pdf->Ln(7);

//Acusamos ...
	$pdf->Rect(12, 278, 100, 47.5);
	$pdf->Cell($GLOBALS['ph']*76.6, 5, '                     Acusamos recebimento da 1ª via ', 0, 1, 'L');
	$pdf->Cell($GLOBALS['ph']*76.6, 5, '', 0, 1, 'L');
	$pdf->Cell($GLOBALS['ph']*76.6, 5, '', 0, 1, 'L');
	$pdf->Cell($GLOBALS['ph']*76.6, 5, '   ____________________________________________                                                Atenciosamente,', 0, 1, 'L');
	$pdf->Cell($GLOBALS['ph']*76.6, 5, '                                     (local e data)                                  ', 0, 1, 'L');
	$pdf->Cell($GLOBALS['ph']*76.6, 5, '', 0, 1, 'L');
	$pdf->Cell($GLOBALS['ph']*76.6, 5, '', 0, 1, 'L');
	$pdf->Cell($GLOBALS['ph']*76.6, 5, '   ____________________________________________      ________________________________________________________', 0, 1, 'L');
	$pdf->Cell($GLOBALS['ph']*76.6, 5, '                           (carimbo e assinatura)                                             '.$campos_empresa[0]['razaosocial'], 0, 1, 'L');
	$pdf->Cell($GLOBALS['ph']*76.6, 5, '', 0, 1, 'L');
}

chdir('../../../../../pdf');
$file='../../../../../pdf/'.basename(tempnam(getcwd()), '').basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>