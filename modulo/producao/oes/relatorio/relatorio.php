<?
require('../../../../lib/pdf/fpdf.php');
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/variaveis/dp.php');
error_reporting(0);
session_start('funcionarios');

/////////////////////////////////////// INÍCIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel	= 'P';  // P=> Retrato L=>Paisagem
$unidade	= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= 'oficio2'; // A3, A4, A5, Letter, Legal
$pdf		= new FPDF($tipo_papel, $unidade, $formato_papel);

global $pv,$ph; //valor baseado em mm do A4
if($formato_papel == 'oficio2') {
    if($tipo_papel == 'P') {
        $pv = 330/100;
        $ph = 216/100;
    }else {
        $pv = 216/100;
        $ph = 330/100;
    }
}else {
    echo 'Formato não definido';
}

$pdf->Open();
$pdf->SetTopMargin(3);
$pdf->SetLeftMargin(5);
$pdf->AddPage();
$pdf->SetAutoPageBreak('false', 0);

//Procedimento normal de quando se carrega a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $chkt_oe = $_POST['chkt_oe'];
}else {
    $chkt_oe = $_GET['chkt_oe'];
}

$qtde_oes_impressas                     = 0;
$adicionou_pagina_atraves_de_desenho    = 'N';

foreach($chkt_oe as $id_oe) {
    //Já faço uma marcação na O.E, p/ saber que está foi impressa e não exibir + na lista de O.E(s) Pendentes
    $sql = "UPDATE `oes` SET `impresso` = 'S' WHERE `id_oe` = '$id_oe' LIMIT 1 ";
    bancos::sql($sql);
    
    //Busco de Dados das O.E(s) selecionadas ...
    $sql = "SELECT * 
            FROM `oes` 
            WHERE `id_oe` = '$id_oe' LIMIT 1 ";
    $campos = bancos::sql($sql);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell($GLOBALS['ph'] * 8, 5, 'O.E. N.º ', 'LTB', 0, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell($GLOBALS['ph'] * 39.5, 5, $id_oe, 'RTB', 0, 'L');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($GLOBALS['ph'] * 14, 5, 'Data de Emissão ', 'LTB', 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell($GLOBALS['ph'] * 33.5, 5, substr(data::datetodata($campos[0]['data_s'], '/'), 0, 10), 'RTB', 1, 'L');

    //Faço esse Tratamento porque podemos ter vários PA(s) de Saída ...
    $vetor_produto_acabado  = explode(',', $campos[0]['id_produto_acabado_s']);
    
    //Faço esse Tratamento porque podemos ter várias Qtde(s) de Saída ...
    $vetor_qtde_s           = explode(',', $campos[0]['qtde_s']);
    
    //Limpo essa variável para não dar conflito c/ os valores armazenados na mesma do loop anterior ...
    $pas_enviados = '';
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($GLOBALS['ph'] * 95, 5, 'Enviado(s): ', 'RLT', 1, 'L');
    $pdf->SetFont('Arial', '', 10);

    if(count($vetor_produto_acabado) == 1) {//Só existe 1 único PA Enviado ...
        $sql = "SELECT `referencia`, `discriminacao` 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '".$vetor_produto_acabado[0]."' LIMIT 1 ";
        $campos_pa = bancos::sql($sql);
        $pdf->MultiCell($GLOBALS['ph'] * 95, 5, $vetor_qtde_s[0].' '.$campos_pa[0]['referencia'].' * '.$campos_pa[0]['discriminacao'], 'RLB', 1, 'L');
    }else {//Mais de 1 PA Enviado ...
        foreach($vetor_produto_acabado as $j => $id_produto_acabado_loop) {
            $sql = "SELECT `referencia`, `discriminacao` 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado_loop' LIMIT 1 ";
            $campos_pa      = bancos::sql($sql);
            $pas_enviados.= $vetor_qtde_s[$j].' '.$campos_pa[0]['referencia'].' * '.$campos_pa[0]['discriminacao'];
            //Enquanto não chegar no último Registro, vou printando vírgula ...
            if(($j + 1) < count($vetor_produto_acabado)) $pas_enviados.= ', ';
        }
        $pdf->MultiCell($GLOBALS['ph'] * 95, 5, $pas_enviados, 'RLB', 1, 'L');
    }

    //Limpo essa variável para não dar conflito c/ os valores armazenados na mesma do loop anterior ...
    $pas_atrelados = '';
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($GLOBALS['ph'] * 95, 5, 'Observação: ', 'RLT', 1, 'L');
    $pdf->SetFont('Arial', '', 10);
    
    //Significa que Existem Itens da 7ª Etapa que estão atrelados a esse Produto Principal
    if(!empty($campos[0]['id_pas_atrelados'])) {
        $pas_atrelados.= $campos[0]['observacao_s'].chr(13);

        $sql = "SELECT IF(`referencia` = 'ESP', `discriminacao`, `referencia`) AS produto 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` IN (".$campos[0]['id_pas_atrelados'].") ";
        $campos_pa = bancos::sql($sql);
        $linhas_pa = count($campos_pa);
        //Acumulo nessa variável para apresentação ...
        for($j = 0; $j < $linhas_pa; $j++) $pas_atrelados.= $campos_pa[$j]['produto'].', ';
        
        $pdf->MultiCell($GLOBALS['ph'] * 95, 5, substr($pas_atrelados, 0, strlen($pas_atrelados) - 2), 'RLB', 1, 'L');
//Não existem Itens Atrelados, sendo assim só listo a observação normalmente
    }else {
        $pdf->MultiCell($GLOBALS['ph'] * 95, 5, $campos[0]['observacao_s'], 'RLB', 1, 'L');
    }
    
    //Qtde à Retornar ...
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($GLOBALS['ph'] * 14, 5, 'Qtde à Retornar: ', 'LTB', 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell($GLOBALS['ph'] * 6, 5, $campos[0]['qtde_a_retornar'], 'RTB', 0, 'L');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($GLOBALS['ph'] * 10, 5, 'Retornado: ', 'LTB', 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell($GLOBALS['ph'] * 65, 5, '_________ '.intermodular::pa_discriminacao($campos[0]['id_produto_acabado_e'], 0, 0, 0, 0, 1), 'RTB', 1, 'L');
    
    //Data ...
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($GLOBALS['ph'] * 4.5, 5, 'Data ', 'LTB', 0, 'L');
    $pdf->SetFont('Arial', '', 10); 
    $pdf->Cell($GLOBALS['ph'] * 15.5, 5, '________________', 'RTB', 0, 'L');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($GLOBALS['ph'] * 14, 5, 'Visto Embalagem', 'LTB', 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell($GLOBALS['ph'] * 23, 5, '________________________', 'RTB', 0, 'L');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($GLOBALS['ph'] * 12, 5, 'Visto Estoque', 'LTB', 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell($GLOBALS['ph'] * 26, 5, '___________________________', 'RTB', 1, 'L');
    
    //Observação ...
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($GLOBALS['ph'] * 20, 5, 'Observação ', 1, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell($GLOBALS['ph'] * 75, 5, '________________________________________________________________________________', 1, 1, 'C');
    
    $pdf->Ln(2);
    $pdf->Line(1, $pdf->GetY(), 214, $pdf->GetY());//Linha divisória ...
    $pdf->Ln(2);

    //Busca o Desenho do PA Retornado ...
    $sql = "SELECT `desenho_para_op` 
            FROM `produtos_acabados` 
            WHERE `id_produto_acabado` = '".$campos[0]['id_produto_acabado_e']."' LIMIT 1 ";
    $campos_desenho = bancos::sql($sql);
    if(!empty($campos_desenho[0]['desenho_para_op'])) {//Se existir desenho, então printo o mesmo ...
        $pdf->AddPage();//Adiciono uma nova página somente p/ a figura ...
        $pdf->Image('../../../../imagem/fotos_produtos_acabados/'.$campos_desenho[0]['desenho_para_op'], 5, 2, 290, 220, 'PNG');
        $pdf->AddPage();//Adiciono uma nova página p/ continuar reimprimindo a(s) OE(s) ...
        
        $adicionou_pagina_atraves_de_desenho = 'S';
    }
    $qtde_oes_impressas++;
    
    if($qtde_oes_impressas == 9) {//A cada 9 OE(s) que for(em) impressa(s), adiciono uma Nova Página ...
        if($adicionou_pagina_atraves_de_desenho == 'N') $pdf->AddPage();
        $qtde_oes_impressas                     = 0;
        $adicionou_pagina_atraves_de_desenho    = 'N';
    }
}

chdir('../../../../pdf');
$file = '../../../../pdf/'.basename(tempnam(str_replace(trim('/'),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file

//Esse controle só na Tela de Alterar OE(s), pois a idéia é de que se imprima uma OE e essa já desapareça na mesma hora da Tela Pós-Filtro ...
if($_POST['hdd_atualizar_alterar'] == 'S') {
    echo "<html><body onunload='opener.document.location = \"../alterar.php".$parametro."\"'></body><Script Language='JavaScript'>document.location='$file';</Script></html>";//JavaScript redirection
}else {
    echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
}
?>