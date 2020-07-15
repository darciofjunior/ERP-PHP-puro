<?
require('../../../lib/pdf/fpdf.php');
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/genericas.php');
require('../../../lib/intermodular.php');
session_start('funcionarios');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');
error_reporting(0);
define('FPDF_FONTPATH', 'font/');

$tipo_papel		= "L";  // P=> Retrato L=>Paisagem
$unidade		= "mm"; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= "A4"; // A3, A4, A5, Letter, Legal
$pdf = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);
$pdf->SetLeftMargin(15);
$pdf->Ln(8);

global $pv, $ph; //valor baseado em mm do A4
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

//Variáveis utilizadas mais abaixo ...
/*
2158 - JRC
2209 - Imporpico
2321 - Hierros Yacare
2423 - Juan Bohm
3746 - Thyssen - Esse clientes possuem uma Exceção, onde não tem compras nos anos atuais, sendo assim eu 
retroajo com estes para o ano atual deles para 2007, p/ que daí possamos fazer as suas estastísticas ...*/
if($id_cliente == 2158 || $id_cliente == 2209 || $id_cliente == 2321 || $id_cliente == 2423 || $id_cliente == 3746) {
    $ano_atual = 2007;
}else {//Outros clientes, assumem o ano Atual ...
    $ano_atual = date('Y');
}
$dois_anos_atras 	= $ano_atual - 2;


$_GET['cmb_familia'] = explode(',', $_GET['cmb_familia']);

if($_GET['cmb_familia'][0] == '') {
    $familias_selecionadas = '%';
    $produtos = 'PRODUTOS (TODOS)';
}else {
    for($i = 0; $i < count($_GET['cmb_familia']); $i++) {
        $sql = "SELECT nome 
                FROM  `familias` 
                WHERE `id_familia` = '".$_GET['cmb_familia'][$i]."' LIMIT 1 ";
        $campos_familia = bancos::sql($sql);
        $familias_selecionadas.= $_GET['cmb_familia'][$i].', ';
        $produtos.= $campos_familia[0]['nome'].' - ';
    }
    $familias_selecionadas 	= substr($familias_selecionadas, 0, strlen($familias_selecionadas) - 2);
    $produtos = substr($produtos, 0, strlen($produtos) - 2);
}

if($familias_selecionadas == '%') {
    $condicao_familia = " AND f.`id_familia` LIKE '$familias_selecionadas' ";
}else {
    $condicao_familia = " AND f.`id_familia` IN ($familias_selecionadas) ";
}

/************************Produtos Vendidos para o Cliente nos últimos 3 anos**********************/
//Listagem de Todos os Produtos Vendidos para o Cliente ...
$sql = "SELECT SUM(nfsi.qtde - nfsi.qtde_devolvida) qtde_anual, c.razaosocial cliente, ged.id_empresa_divisao, 
        pa.id_produto_acabado, pa.referencia, pa.discriminacao, pa.preco_unitario, nfs.data_emissao, YEAR(nfs.data_emissao) AS ano 
        FROM `clientes` c 
        INNER JOIN `nfs` ON nfs.id_cliente = c.id_cliente 
        INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf 
        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = nfsi.id_produto_acabado 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
        INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
        INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
        INNER JOIN `familias` f ON f.id_familia = gpa.id_familia $condicao_familia 
        WHERE YEAR(nfs.`data_emissao`) >= '$dois_anos_atras' 
        AND nfs.`id_cliente` = '$id_cliente' 
        GROUP BY pa.id_produto_acabado, ano ORDER BY pa.discriminacao, ano ";
$campos = bancos::sql($sql);
$linhas	= count($campos);
$vetor_meses = array('', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');

if($linhas > 0) {//Existe pelo menos uma Compra da Família ou Famílias ...
	$pdf->SetFont('Arial', 'B', 16);
	$pdf->Cell($GLOBALS['ph']*100, 5, 'São Paulo, '.date('d').' de '.$vetor_meses[intval(date('m'))].' de '.$ano_atual.'.', 0, 1, 'L');
	$pdf->Ln(8);
	$pdf->Cell($GLOBALS['ph']*100, 5, 'Atenção cliente '.$campos[0]['cliente'].', referente: Estudo projeção trimestral de vendas.', 0, 1, 'L');
	$pdf->Ln(6);
	$pdf->Cell($GLOBALS['ph']*100, 5, 'Prezado senhor:', 0, 1, 'L');
	$pdf->Ln(8);
	$pdf->MultiCell($GLOBALS['ph']*100, 5, 'Com base numa avaliação de resultados das suas compras efetuadas conosco nos últimos meses, passamos abaixo o resultado desse estudo emitido pelo nosso sistema, para sua avaliação.', 0, 1, 'L');
	$pdf->Ln(5);
	
	$pdf->SetFont('Arial', 'B', 10);
	$pdf->SetTextColor(255, 0, 0);
	$pdf->Cell($GLOBALS['ph']*100, 5, 'PRODUTOS '.$produtos, 1, 1, 'C');

	$pdf->SetFont('Arial', 'B', 10);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell($GLOBALS['ph']*8, 5, 'Qtde Sug', 1, 0, 'C');
	$pdf->Cell($GLOBALS['ph']*8, 5, 'Qtde Aprov', 1, 0, 'C');
	$pdf->Cell($GLOBALS['ph']*8, 5, '1º Mês', 1, 0, 'C');
	$pdf->Cell($GLOBALS['ph']*8, 5, '2º Mês', 1, 0, 'C');
	$pdf->Cell($GLOBALS['ph']*8, 5, '3º Mês', 1, 0, 'C');
	$pdf->Cell($GLOBALS['ph']*10, 5, 'Referência', 1, 0, 'C');
	$pdf->Cell($GLOBALS['ph']*34, 5, 'Discriminação', 1, 0, 'C');
	$pdf->Cell($GLOBALS['ph']*8, 5, 'Preço', 1, 0, 'C');
	$pdf->Cell($GLOBALS['ph']*8, 5, 'Total', 1, 1, 'C');
		
	for($i = 0; $i < $linhas; $i++) {
		if($id_pa_antigo != $campos[$i]['id_produto_acabado']) {
			if(($campos[$i]['id_produto_acabado'] == $campos[$i + 1]['id_produto_acabado']) && ($campos[$i]['id_produto_acabado'] == $campos[$i + 2]['id_produto_acabado'])) {
				$qtde_anos_exibir = 3; 
			}else if(($campos[$i]['id_produto_acabado'] == $campos[$i + 1]['id_produto_acabado']) && ($campos[$i]['id_produto_acabado'] != $campos[$i + 2]['id_produto_acabado'])) {
				$qtde_anos_exibir = 2;
			}else {
				$qtde_anos_exibir = 1;
			}
			if($qtde_anos_exibir == 3) {
				if($campos[$i]['ano'] == $ano_atual - 2) {
					$qtde_2anos_atras = number_format($campos[$i]['qtde_anual'], 0, '', '');
					$qtde_1ano_atras = number_format($campos[$i + 1]['qtde_anual'], 0, '', '');
					$qtde_ano_atual = number_format($campos[$i + 2]['qtde_anual'], 0, '', '');
				}else if($campos[$i]['ano'] == $ano_atual - 1) {
					$qtde_2anos_atras = number_format(0, 0, '', '');
					$qtde_1ano_atras = number_format($campos[$i]['qtde_anual'], 0, '', '');
					$qtde_ano_atual = number_format($campos[$i + 1]['qtde_anual'], 0, '', '');
				}else if($campos[$i]['ano'] == $ano_atual) {
					$qtde_2anos_atras = number_format(0, 0, '', '');
					$qtde_1ano_atras = number_format(0, 0, '', '');
					$qtde_ano_atual = number_format($campos[$i]['qtde_anual'], 0, '', '');
				}
			}else if($qtde_anos_exibir == 2) {
				if($campos[$i]['ano'] == $ano_atual - 2 && $campos[$i + 1]['ano'] == $ano_atual - 1) {
					$qtde_2anos_atras = number_format($campos[$i]['qtde_anual'], 0, '', '');
					$qtde_1ano_atras = number_format($campos[$i + 1]['qtde_anual'], 0, '', '');
					$qtde_ano_atual = number_format(0, 0, '', '');
				}else if($campos[$i]['ano'] == $ano_atual - 2 && $campos[$i + 1]['ano'] == $ano_atual) {
					$qtde_2anos_atras = number_format($campos[$i]['qtde_anual'], 0, '', '');
					$qtde_1ano_atras = number_format(0, 0, '', '');
					$qtde_ano_atual = number_format($campos[$i + 1]['qtde_anual'], 0, '', '');
				}else if($campos[$i]['ano'] == $ano_atual - 1 && $campos[$i + 1]['ano'] == $ano_atual) {
					$qtde_2anos_atras = number_format(0, 0, '', '');
					$qtde_1ano_atras = number_format($campos[$i]['qtde_anual'], 0, '', '');
					$qtde_ano_atual = number_format($campos[$i + 1]['qtde_anual'], 0, '', '');
				}
			}else {
				if($campos[$i]['ano'] == $ano_atual - 2) {
					$qtde_2anos_atras = number_format($campos[$i]['qtde_anual'], 0, '', '');
					$qtde_1ano_atras = number_format(0, 0, '', '');
					$qtde_ano_atual = number_format(0, 0, '', '');
				}else if($campos[$i]['ano'] == $ano_atual - 1) {
					$qtde_2anos_atras = number_format(0, 0, '', '');
					$qtde_1ano_atras = number_format($campos[$i]['qtde_anual'], 0, '', '');
					$qtde_ano_atual = number_format(0, 0, '', '');
				}else if($campos[$i]['ano'] == $ano_atual) {
					$qtde_2anos_atras = number_format(0, 0, '', '');
					$qtde_1ano_atras = number_format(0, 0, '', '');
					$qtde_ano_atual = number_format($campos[$i]['qtde_anual'], 0, '', '');
				}
			}
			$qtde_projetada_por_mes = round(($qtde_2anos_atras + $qtde_1ano_atras + $qtde_ano_atual) / $_GET['cmb_qtde_meses'], 2);
			$qtde_projetada_trimestre = ceil($qtde_projetada_por_mes * 3);
			//A nova proposta passa a ser o quero o Cliente comprava antes + a Nova % ...
			$nova_proposta = ceil($qtde_projetada_trimestre * $_GET['txt_perc_projetada'] / 100);
			$nova_proposta_por_3meses = ($nova_proposta / 3);
			
			$mes1 = ceil($nova_proposta_por_3meses);
			$mes2 = ceil($nova_proposta_por_3meses);
			$mes3 = ceil($nova_proposta_por_3meses);
			
			if($mes1 + $mes2 + $mes3 != $nova_proposta) $mes2-= 1;
			if($mes1 + $mes2 + $mes3 != $nova_proposta) $mes3-= 1;
			
			$pdf->Cell($GLOBALS['ph']*8, 5, number_format(intval($nova_proposta), 2, ',', '.'), 1, 0, 'C');
			$pdf->Cell($GLOBALS['ph']*8, 5, '', 1, 0, 'C');
			$pdf->Cell($GLOBALS['ph']*8, 5, $mes1, 1, 0, 'C');
			$pdf->Cell($GLOBALS['ph']*8, 5, $mes2, 1, 0, 'C');
			$pdf->Cell($GLOBALS['ph']*8, 5, $mes3, 1, 0, 'C');
			$pdf->Cell($GLOBALS['ph']*10, 5, $campos[$i]['referencia'], 1, 0, 'C');
			$pdf->Cell($GLOBALS['ph']*34, 5, $campos[$i]['discriminacao'], 1, 0, 'L');
			//Busca o Último Preço negociado em Pedido no ano Anterior ...
			$sql = "SELECT pvi.preco_liq_final 
                                FROM `pedidos_vendas_itens` pvi 
                                INNER JOIN `pedidos_vendas` pv ON pv.id_pedido_venda = pvi.id_pedido_venda AND YEAR(pv.data_emissao) = '".(date('Y') - 1)."' AND pv.id_cliente = '$id_cliente' 
                                WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' ORDER BY id_pedido_venda_item DESC LIMIT 1 ";
			$campos_preco_unitario = bancos::sql($sql);
			if(count($campos_preco_unitario) == 1) {//Se existir uma Compra no ano Anterior ...
				$preco_unitario = $campos_preco_unitario[0]['preco_liq_final'];
				$marcacao		= '*';
			}else {//Se não existir eu pego o Preço de Lista cheio, dando a este todos os descontos necessários 
				//Aqui pego o representante e o desconto do cliente para o calculo ...
				$sql = "SELECT desconto_cliente 
                                        FROM `clientes_vs_representantes` 
                                        WHERE `id_cliente` = '$id_cliente' 
                                        AND `id_empresa_divisao` = '".$campos[$i]['id_empresa_divisao']."' LIMIT 1 ";
				$campos_desconto = bancos::sql($sql);
				if(count($campos_desconto) > 0) $desconto_cliente = (strtoupper($campos[$i]['referencia']) == 'ESP') ? 0 : $campos_desconto[0]['desconto_cliente'];
				$preco_unitario = $campos[$i]['preco_unitario'] * (1 - $desc_base_a_nac / 100) * (1 - $desc_base_b_nac / 100) * (1 - $desconto_cliente / 100);
				$marcacao		= '';
			}
			$pdf->Cell($GLOBALS['ph']*8, 5, $marcacao.'R$ '.number_format($preco_unitario, 2, ',', '.'), 1, 0, 'R');
			$pdf->Cell($GLOBALS['ph']*8, 5, 'R$ '.number_format(intval($nova_proposta) * $preco_unitario, 2, ',', '.'), 1, 1, 'R');

			$sub_total_projetado+= intval($nova_proposta) * $preco_unitario;
		}
		$id_pa_antigo = $campos[$i]['id_produto_acabado'];
	}
	
	$total_projetado+= $sub_total_projetado;
	
	$pdf->SetTextColor(255, 0, 0);
	$pdf->Cell($GLOBALS['ph']*100, 5, 'TOTAL GERAL TRIMESTRE PROJ R$ '.number_format($total_projetado, 2, ',', '.'), 1, 1, 'L');
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Ln(5);
	$pdf->SetFont('Arial', 'B', 13);
	
	$pdf->MultiCell($GLOBALS['ph']*100, 5, 'Conforme demonstrativo acima, a nossa sugestão é dividir o valor Total deste planejamento em 3 fornecimentos mensais. Nas seguintes condições:', 0, 1, 'L');
	$pdf->Ln(8);
	
	$pdf->Cell($GLOBALS['ph']*100, 5, '* Prazo Pgto em até 4 parcelas;', 0, 1, 'L');
	$pdf->Cell($GLOBALS['ph']*100, 5, '* Preço Tabela de Oferta N.º 6 de 2011 / 2012;', 0, 1, 'L');
	$pdf->Cell($GLOBALS['ph']*100, 5, '* Manteremos o Preço Firme até o final da entrega.', 0, 1, 'L');
	$pdf->Cell($GLOBALS['ph']*100, 5, '* Embalagem: Nas quantidades para efeito de pedido, consideramos embalagem fechada de Estoque.', 0, 1, 'L');
	$pdf->Ln(8);
	
	$pdf->Cell($GLOBALS['ph']*100, 5, 'Sem mais, ficamos no aguardo.', 0, 1, 'L');
	$pdf->Ln(4);
	
	$pdf->Cell($GLOBALS['ph']*100, 5, 'Atenciosamente', 0, 1, 'L');
	$pdf->Ln(4);
	
	$pdf->Cell($GLOBALS['ph']*100, 5, 'Grupo Albafer', 0, 1, 'L');
	$pdf->Ln(4);
}

/**************************Aqui eu Gravo a Projeção Trimestral do Cliente**************************/
if($cmb_familia_filtro == '%') {
	$produtos = 'PRODUTOS (TODOS)';
}else {
	$sql = "SELECT nome 
			FROM `familias` 
			WHERE `id_familia` = '$cmb_familia_filtro' LIMIT 1 ";
	$campos_familia = bancos::sql($sql);
	$produtos = $campos_familia[0]['nome'];
}

//Primeiramente eu verifico se já foi feita alguma projeção para o determinado e respectivo Cliente ...
$sql = "SELECT id_projecao_trimestral 
		FROM `projecoes_trimestrais` 
		WHERE `id_cliente` = '$_GET[id_cliente]' 
		AND `tipo_projecao` = 'C' 
		AND `tipo_produto` = '$produtos' LIMIT 1 ";
$campos = bancos::sql($sql);
if(count($campos) == 0) {//Não tem projeção, então insere no Banco a respectiva Projeção ...
	$sql = "INSERT INTO `projecoes_trimestrais` (`id_projecao_trimestral`, `id_cliente`, `id_funcionario`, `tipo_projecao`, `tipo_produto`, `qtde_meses`, `percentagem`, `valor_projecao`, `data_sys`) VALUES (NULL, '$_GET[id_cliente]', '$_SESSION[id_funcionario]', 'C', '$produtos', '$_GET[cmb_qtde_meses]', '$_GET[txt_perc_projetada]', '$total_projetado', '".date('Y-m-d H:i:s')."') ";
	bancos::sql($sql);
	$id_projecao_trimestral = id_registro;
}else {
	$sql = "UPDATE `projecoes_trimestrais` SET `id_funcionario` = '$_SESSION[id_funcionario]', `qtde_meses` = '$_GET[cmb_qtde_meses]', `percentagem` = '$_GET[txt_perc_projetada]', `valor_projecao` = '$total_projetado', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_projecao_trimestral` = '".$campos[0]['id_projecao_trimestral']."' LIMIT 1 ";
	bancos::sql($sql);
	$id_projecao_trimestral = $campos[0]['id_projecao_trimestral'];
}

//Aqui eu gero um Follow-Up para o Cliente ...
$sql = "SELECT id_cliente_contato 
        FROM `clientes_contatos` 
        WHERE `id_cliente` = '$_GET[id_cliente]' 
        AND `ativo` = '1' LIMIT 1 ";
$campos_contatos    = bancos::sql($sql);
$id_cliente_contato = $campos_contatos[0]['id_cliente_contato'];

$sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_cliente_contato`, `id_funcionario`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$_GET[id_cliente]', '$id_cliente_contato', '$_SESSION[id_funcionario]', '13', '(Projeção Realizada) próximo passo gerar o Pedido - Valor da Projeção = R$ ".number_format($total_projetado, 2, ',', '.')."', '".date('Y-m-d H:i:s')."') ";
bancos::sql($sql);
/**************************************************************************************************/
chdir('../../../pdf');
$file='../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>