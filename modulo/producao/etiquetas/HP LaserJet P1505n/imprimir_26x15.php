<?
require('../../../../lib/segurancas.php');
require('code13.php');

$qtde_etiquetas = (!empty($_POST['txt_pular'])) ? $_POST['txt_quantidade'] + $_POST['txt_pular'] : $_POST['txt_quantidade'];
if($qtde_etiquetas > 85) $qtde_etiquetas = 85;//Nunca podemos ultrapassar o N.º Limite de Etiquetas por página ...

if($_POST['opt_opcao'] == 1) {
    $rotulo_impressao = '-LT';
}else if($_POST['opt_opcao'] == 2) {
    $rotulo_impressao = '-OP';
}else if($_POST['opt_opcao'] == 3) {
    $rotulo_impressao = '-OE';
}else if($_POST['opt_opcao'] == 4) {
    $rotulo_impressao = '-OC';
}

if(!empty($_POST['cmb_pa_substitutivo'])) {//Só irá cair nessa situação quando for um Filtro por OP e o usuário escolher PA Substitutivo.
    $sql = "SELECT `referencia`, LOWER(`discriminacao`) AS discriminacao, `codigo_barra` 
            FROM `produtos_acabados` 
            WHERE `id_produto_acabado` = '$_POST[cmb_pa_substitutivo]' LIMIT 1 ";
}else {//Aqui é o caminho normal ...
    $sql = "SELECT `referencia`, LOWER(`discriminacao`) AS discriminacao, `codigo_barra` 
            FROM `produtos_acabados` 
            WHERE `id_produto_acabado` = '$_POST[hdd_produto_acabado]' LIMIT 1 ";
}
$campos = bancos::sql($sql);

$tipo_papel		= "P";  // P=> Retrato L=>Paisagem
$unidade		= "mm"; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= '26x15_5carreiras'; // A3, A4, A5, Letter, Legal
$pdf = new PDF($tipo_papel, $unidade, $formato_papel);

//Inicializando as variáveis ...
$linha_atual    = 0;
$inicio         =  $linha_atual;
$fim            =  $linha_atual + $qtde_etiquetas;
if($fim > $qtde_etiquetas) $fim = $qtde_etiquetas;

$pdf->Open();
$pdf->AddPage();
$tab   	= 20;
$enter 	= 1.4;
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
        $tab = 20;
        if($linha_atual <= 3) {
            $enter+= 14.4;
        }else if($linha_atual <= 9) {
            $enter+= 15.1;
        }else if($linha_atual <= 14) {
            $enter+= 14.8;
        }else {
            $enter+= 15.1;
        }
        $linha_atual++;
    }
    $pdf->SetFont('Arial', '', 5);
    //Se o usuário resolveu pular algumas etiquetas para Imprimir então ...
    if($_POST['txt_pular'] > $i) {
        $pdf->Text($tab, $enter, '');//Vai "escrevendo" em Branco até chegar na Etiqueta Desejada ...
    }else {
        $pcs_embalagem = (!empty($_POST['txt_pcs_embalagem']) && $_POST['txt_pcs_embalagem'] > 1) ? $_POST['txt_pcs_embalagem'].'-' : '';

        //Crio uma regra para que tenhamos no máximo 22 caracteres na primeira linha de Impressão da Etiqueta ...
        $caracteres_primeira_linha  = 22;
        $qtde_caracteres_embalagem  = strlen($pcs_embalagem);
        $caracteres_restante        = $caracteres_primeira_linha - $qtde_caracteres_embalagem;

        //Aqui eu monto um jeito de quebrar a discriminação em 2 partes ...
        for($j = 0; $j < strlen($campos[0]['discriminacao']); $j++) {
            if($j <= $caracteres_restante) {//Essa Discriminação será printada na 1ª Linha da Etiqueta ...
                $discriminacao1.= substr($campos[0]['discriminacao'], $j, 1);
            }else {//Essa Discriminação será printada na 2ª Linha da Etiqueta ...
                $discriminacao2.= substr($campos[0]['discriminacao'], $j, 1);
            }
        }
        $pdf->Text($tab, $enter, $pcs_embalagem.ucfirst($discriminacao1));
        $pdf->Text($tab, $enter + 1.6, $discriminacao2);

        if($campos[0]['referencia'] == 'ESP') $pdf->SetTextColor(255, 0, 0);//Na cor vermelha ...

        if(!empty($_POST['txt_numero']))    $numero = $_POST['txt_numero'].' - ';
        if(!empty($_POST['txt_data']))      $data   = substr($_POST['txt_data'], 0, 5).'/'.substr($_POST['txt_data'], 8, 2);
        $pdf->Text($tab, $enter + 3.2, $campos[0]['referencia'].$rotulo_impressao.$numero.$data);

        //Só irá exibir o Quadrado Vermelho quando o usuário selecionar a opção "Perfil Completo" ...
        if($_POST['opt_opcao_perfil'] == 2) $pdf->Image('../../../../imagem/bloco_vermelho.jpg', $tab + 17.2, $enter + 1.2, 2, 2, 'JPG');

        $pdf->SetTextColor(0, 0, 0);
        /************************Código de Barra************************/
        //Printa em Branco para não dar problema de pular linha ...
        if(empty($campos[0]['codigo_barra']) || $campos[0]['codigo_barra'] == '0000000000000') {
            $pdf->Text($tab + 1, $enter + 3.6, '');
        }else {//Printa o Código de Barra normal ...
            $pdf->EAN13($tab + 1, $enter + 3.6, $campos[0]['codigo_barra'], '3.6', '.18');
        }
        /***************************************************************/
    }
    $tab+= 26.7;
}
$pdf->Output();
?>