<?
require('../../../../lib/segurancas.php');
require('code13.php');

$qtde_etiquetas = (!empty($_POST['txt_pular'])) ? $_POST['txt_quantidade'] + $_POST['txt_pular'] : $_POST['txt_quantidade'];
if($qtde_etiquetas > 27) $qtde_etiquetas = 27;//Nunca podemos ultrapassar o N.� Limite de Etiquetas por p�gina ...

if($_POST['opt_opcao'] == 1) {
	$rotulo_impressao = ' - Lote: ';
}else if($_POST['opt_opcao'] == 2) {
	$rotulo_impressao = ' - OP: ';
}else if($_POST['opt_opcao'] == 3) {
	$rotulo_impressao = ' - OE: ';
}else if($_POST['opt_opcao'] == 4) {
	$rotulo_impressao = ' - OC: ';
}

if(!empty($_POST['cmb_pa_substitutivo'])) {//S� ir� cair nessa situa��o quando for um Filtro por OP e o usu�rio escolher PA Substitutivo.
    $sql = "SELECT IF(pa.`desenho_para_etiqueta` = '', gpa.`desenho_para_conferencia`, pa.`desenho_para_etiqueta`) AS desenho_para_etiqueta, pa.`referencia`, UPPER(pa.`discriminacao`) AS discriminacao, pa.`codigo_barra` 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            WHERE pa.`id_produto_acabado` = '$_POST[cmb_pa_substitutivo]' LIMIT 1 ";
}else {//Aqui � o caminho normal ...
    $sql = "SELECT IF(pa.`desenho_para_etiqueta` = '', gpa.`desenho_para_conferencia`, pa.`desenho_para_etiqueta`) AS desenho_para_etiqueta, pa.`referencia`, UPPER(pa.`discriminacao`) AS discriminacao, pa.`codigo_barra` 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            WHERE pa.`id_produto_acabado` = '$_POST[hdd_produto_acabado]' LIMIT 1 ";
}
$campos = bancos::sql($sql);

$linha_atual 	= 1;//Valor Default ...
$linhas         = count($_POST['txt_qtde_total']);

$tipo_papel     = 'P';  // P=> Retrato L=>Paisagem
$unidade        = 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel  = 'etiqueta_a4255'; // A3, A4, A5, Letter, Legal
$pdf            = new PDF($tipo_papel, $unidade, $formato_papel);

//Inicializando as vari�veis ...
$linha_atual    = 0;
$inicio         =  $linha_atual;
$fim            =  $linha_atual + $qtde_etiquetas;
if($fim > $qtde_etiquetas) $fim = $qtde_etiquetas;

$pdf->Open();
$pdf->AddPage();
$tab   	= 6;
$enter 	= 14.3;
$espacador_entre_linhas = 0.2;

//EXIBE OS REGISTROS
for($i = $inicio; $i < $fim; $i++) {
    /**************************************************************************/
    //Aqui eu limpo essas vari�veis p/ n�o herdarem valores do Loop Anterior ...
    $espacos 		= 0;
    $discriminacao1 = '';
    $discriminacao2 = '';
    /**************************************************************************/
    if($i != 0 && ($i % 3 == 0)) {
        $tab = 6;
        if($i <= 3) {
            $enter+= 27;
        }else {
            $enter+= 27.2;
        }
        $linha_atual++;
    }
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetTextColor(0, 0, 0);
    //Se o usu�rio resolveu pular algumas etiquetas para Imprimir ent�o ...
    if($_POST['txt_pular'] > $i) {
            $pdf->Text($tab, $enter, '');//Vai "escrevendo" em Branco at� chegar na Etiqueta Desejada ...
    }else {
        if(!empty($campos[0]['desenho_para_etiqueta'])) {
            $pdf->Image('../../../../imagem/desenhos_grupos_pas/'.$campos[0]['desenho_para_etiqueta'], $tab + 5, $enter - 7.8, 39.1, 8.8, 'JPG');
        }
        $pdf->Text($tab, $enter + 3.2, $_POST['txt_pcs_embalagem'].' - '.$campos[0]['referencia']);

        //Aqui eu monto um jeito de quebrar a discrimina��o em 2 partes ...
        for($j = 0; $j < strlen($campos[0]['discriminacao']); $j++) {
            if($j <= 20) {//Essa Discrimina��o ser� printada na 1� Linha da Etiqueta ...
                $discriminacao1.= substr($campos[0]['discriminacao'], $j, 1);
            }else {//Essa Discrimina��o ser� printada na 2� Linha da Etiqueta ...
                $discriminacao2.= substr($campos[0]['discriminacao'], $j, 1);
            }
        }
        $pdf->Text($tab, $enter + 9.5, ucfirst($discriminacao1));
        $pdf->Text($tab, $enter + 15.4, $discriminacao2);
        /************************C�digo de Barra************************/
        //Printa em Branco para n�o dar problema de pular linha ...
        /*$pdf->SetTextColor(0, 0, 0);
        if(empty($campos[0]['codigo_barra']) || $campos[0]['codigo_barra'] == '0000000000000') {
                $pdf->Text($tab + 11, $enter + 9.8, '');
        }else {//Printa o C�digo de Barra normal ...
                $pdf->EAN13($tab + 11, $enter + 9.8, $campos[0]['codigo_barra'], '4.5', '.27');
        }*/
        /***************************************************************/
    }
    $tab+= 58;
}
$pdf->Output();
?>