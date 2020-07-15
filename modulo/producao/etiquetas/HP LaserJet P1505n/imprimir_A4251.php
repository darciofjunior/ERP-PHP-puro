<?
require('../../../../lib/segurancas.php');
require('code13.php');

$qtde_etiquetas = (!empty($_POST['txt_pular'])) ? $_POST['txt_quantidade'] + $_POST['txt_pular'] : $_POST['txt_quantidade'];
if($qtde_etiquetas > 65) $qtde_etiquetas = 65;//Nunca podemos ultrapassar o N.º Limite de Etiquetas por página ...

if($_POST['opt_opcao'] == 1) {
	$rotulo_impressao = ' - LT';
}else if($_POST['opt_opcao'] == 2) {
	$rotulo_impressao = ' - OP';
}else if($_POST['opt_opcao'] == 3) {
	$rotulo_impressao = ' - OE';
}else if($_POST['opt_opcao'] == 4) {
	$rotulo_impressao = ' - OC';
}

if(!empty($_POST['cmb_pa_substitutivo'])) {//Só irá cair nessa situação quando for um Filtro por OP e o usuário escolher PA Substitutivo.
    $sql = "SELECT IF(pa.`desenho_para_etiqueta` = '', gpa.`desenho_para_conferencia`, pa.`desenho_para_etiqueta`) AS desenho_para_etiqueta, pa.referencia, LOWER(pa.discriminacao) AS discriminacao, pa.codigo_barra 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            WHERE `id_produto_acabado` = '$_POST[cmb_pa_substitutivo]' LIMIT 1 ";
}else {//Aqui é o caminho normal ...
    $sql = "SELECT IF(pa.`desenho_para_etiqueta` = '', gpa.`desenho_para_conferencia`, pa.`desenho_para_etiqueta`) AS desenho_para_etiqueta, pa.referencia, LOWER(pa.discriminacao) AS discriminacao, pa.codigo_barra 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            WHERE pa.`id_produto_acabado` = '$_POST[hdd_produto_acabado]' LIMIT 1 ";
}
$campos = bancos::sql($sql);

$tipo_papel		= "P";  // P=> Retrato L=>Paisagem
$unidade		= "mm"; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= 'etiqueta_a4251'; // A3, A4, A5, Letter, Legal
$pdf = new PDF($tipo_papel, $unidade, $formato_papel);

//Inicializando as variáveis ...
$linha_atual    = 0;
$inicio         =  $linha_atual;
$fim            =  $linha_atual + $qtde_etiquetas;
if($fim > $qtde_etiquetas) $fim = $qtde_etiquetas;

$pdf->Open();
$pdf->AddPage();
$tab   	= 2.5;
$enter 	= 14.3;
$espacador_entre_linhas = 0.2;

//EXIBE OS REGISTROS
for($i = $inicio; $i < $fim; $i++) {    
	/**************************************************************************/
	//Aqui eu limpo essas variáveis p/ não herdarem valores do Loop Anterior ...
	$espacos 		= 0;
	$discriminacao1 = '';
	$discriminacao2 = '';
	/**************************************************************************/
	if($i != 0 && ($i % 5 == 0)) {
		$tab = 2.5;
		$enter+= 18.6;
		$linha_atual++;
	}
	$pdf->SetFont('Arial', '', 6.5);
	$pdf->SetTextColor(0, 0, 0);
	//Se o usuário resolveu pular algumas etiquetas para Imprimir então ...
	if($_POST['txt_pular'] > $i) {
		$pdf->Text($tab, $enter, '');//Vai "escrevendo" em Branco até chegar na Etiqueta Desejada ...
	}else {
		//Aqui eu monto um jeito de quebrar a discriminação em 2 partes ...
		for($j = 0; $j < strlen($campos[0]['discriminacao']); $j++) {
			if($j <= 23) {//Essa Discriminação será printada na 1ª Linha da Etiqueta ...
				$discriminacao1.= substr($campos[0]['discriminacao'], $j, 1);
			}else {//Essa Discriminação será printada na 2ª Linha da Etiqueta ...
				$discriminacao2.= substr($campos[0]['discriminacao'], $j, 1);
			}
		}
		$pcs_embalagem = (!empty($_POST['txt_pcs_embalagem']) && $_POST['txt_pcs_embalagem'] > 1) ? $_POST['txt_pcs_embalagem'].' - ' : '';
		if(!empty($campos[0]['desenho_para_etiqueta'])) {                    
                    $pdf->Image('../../../../imagem/desenhos_grupos_pas/'.$campos[0]['desenho_para_etiqueta'], $tab + 3, $enter - 7.8, 26.8, 6, 'JPG');
		}                
		$pdf->Text($tab, $enter, $pcs_embalagem.ucfirst($discriminacao1));
		$pdf->Text($tab, $enter + 2.2, $discriminacao2);
		if(!empty($_POST['txt_data'])) 		$data 	= substr($_POST['txt_data'], 0, 5).'/'.substr($_POST['txt_data'], 8, 2);
		if(!empty($_POST['txt_numero']))	$numero = $_POST['txt_numero'].' - ';
		$pdf->Text($tab, $enter + 4.3, $campos[0]['referencia'].$rotulo_impressao.$numero.$data);
		/************************Código de Barra************************/
		//Printa em Branco para não dar problema de pular linha ...
		$pdf->SetTextColor(0, 0, 0);
		if(empty($campos[0]['codigo_barra']) || $campos[0]['codigo_barra'] == '0000000000000') {
			$pdf->Text($tab + 4, $enter + 4.8, '');
		}else {//Printa o Código de Barra normal ...
			$pdf->EAN13($tab + 6, $enter + 4.8, $campos[0]['codigo_barra'], '3', '.21');
		}
		/***************************************************************/
	}
	$tab+= 35.7;
}
$pdf->Output();
?>