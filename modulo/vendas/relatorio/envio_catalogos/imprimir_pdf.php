<?
require('../../../../lib/pdf/fpdf.php');
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');
require('../../../../lib/genericas.php');
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');

function Heade($pagina) {
    global $pdf;
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell($GLOBALS['ph'] * 102, 7, 'Impressão Relatório de Envio de Catálogo(s) - Página '.$pagina, 1, 1, 'C');

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell($GLOBALS['ph'] * 51, 7, 'Cliente', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 23, 7, 'Cidade', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 4, 7, 'UF', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 14, 7, 'Representante', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 10, 7, 'Data Envio', 1, 1, 'C');
}

/////////////////////////////////////// INÍCIO PDF ///////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');

$tipo_papel		= 'P';  // P=> Retrato L=>Paisagem
$unidade		= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel          = 'A4'; // A3, A4, A5, Letter, Legal
$pdf                    = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
$pdf->SetTopMargin(2);
$pdf->SetLeftMargin(2);
$pdf->AddPage();
global $pv, $ph; //valor baseado em mm do A4

if($formato_papel == 'A4') {
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

/******************************************************************************/
//Essas variáveis p/ controles de Quebra de Página, serão utilizadas mais abaixo ...
$indice_impresso            = 0;
$pagina                     = 1;//Página Corrente ...
//Definição da variável "$qtde_registros_por_pagina" para a Primeira Página apenas ...
$qtde_registros_por_pagina  = 41;
/******************************************************************************/

Heade($pagina);//Chamo o Cabeçalho a Princípio ...

//Trago todos os clientes que já foram enviados os catalogos..
$sql = "SELECT IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, c.`id_cliente`, c.`cidade`, 
        DATE_FORMAT(c.`data_envio_catalogo`, '%d/%m/%Y') AS data_envio_catalogo, ufs.`sigla` 
        FROM `clientes` c                    
        INNER JOIN `ufs` ON ufs.`id_uf` = c.`id_uf` 
        WHERE `catalogo_enviado` = 'S'
        ORDER BY c.razaosocial";    
$campos = bancos::sql($sql);    
$linhas = count($campos);

for($i = 0; $i < $linhas; $i++) {
    /*Toda vez que a variável "$indice_impresso" se igualar a variável "$qtde_registros_por_pagina", 
    então gero uma Nova Página ...*/
    if($indice_impresso != 0 && ($indice_impresso % $qtde_registros_por_pagina == 0)) {
        $pdf->AddPage();
        $indice_impresso = 0;
        $pagina++;
        Heade($pagina);
    }
    $pdf->SetFont('Arial', '', 8);
    
    $pdf->Cell($GLOBALS['ph'] * 51, 7, $campos[$i]['cliente'], 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph'] * 23, 7, $campos[$i]['cidade'], 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 4, 7, $campos[$i]['sigla'], 1, 0, 'C');
    
    //Busco o Representante desse Cliente ...
    $sql = "SELECT `nome_fantasia` 
            FROM `clientes_vs_representantes` cr 
            INNER JOIN `representantes` r ON r.`id_representante` = cr.`id_representante` 
            WHERE cr.`id_cliente` = '".$campos[$i]['id_cliente']."' LIMIT 1 ";
    $campos_representante = bancos::sql($sql);

    $pdf->Cell($GLOBALS['ph'] * 14, 7, $campos_representante[0]['nome_fantasia'], 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 10, 7, $campos[$i]['data_envio_catalogo'], 1, 1, 'C');
    
    $indice_impresso++;
}
$pdf->Cell($GLOBALS['ph'] * 102, 7, '', 1, 1, 'C');

chdir('../../../../pdf');
$file='../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>