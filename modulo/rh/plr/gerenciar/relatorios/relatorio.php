<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
require('../../../../../lib/num_por_extenso_em_rs.php');
segurancas::geral('/erp/albafer/modulo/rh/plr/gerenciar2/opcoes.php', '../../../../../');
error_reporting(0);

/////////////////////////////////////// INÍCIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel	= 'P';  // P=> Retrato L=>Paisagem
$unidade	= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= 'A4'; // A3, A4, A5, Letter, Legal
$pdf = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->SetLeftMargin(6);
$pdf->SetTopMargin(12);
$pdf->Open();
$pdf->SetAutoPageBreak('false', 0);

global $pv,$ph; //valor baseado em mm do A4
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

//Lista todos os PLR(s) lançados no Período passado por parâmetro dos Funcionários ...
$sql = "SELECT e.id_empresa, e.razaosocial, CONCAT(SUBSTRING(e.cnpj, 1, 2), '.', SUBSTRING(e.cnpj, 3, 3), '.', SUBSTRING(e.cnpj, 6, 3), '/', substring(e.cnpj, 9, 4), '-', SUBSTRING(e.cnpj, 13, 2)) AS cnpj, 
        f.nome, f.sindicalizado, CONCAT(DATE_FORMAT(data_inicial, '%d/%m/%Y'), ' à ', DATE_FORMAT(data_final, '%d/%m/%Y')) AS periodo, pf.* 
        FROM `plr_funcionarios` pf 
        INNER JOIN `plr_periodos` pp ON pp.id_plr_periodo = pf.id_plr_periodo 
        INNER JOIN `funcionarios` f ON f.id_funcionario = pf.id_funcionario AND f.`id_funcionario` IN (".$_GET['id_funcs_imprimir'].") 
        INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa 
        WHERE pf.`id_plr_periodo` = '$_GET[cmb_periodo]' ORDER BY e.id_empresa, f.nome ";
$campos = bancos::sql($sql);
$linhas = count($campos);
//Significa que ainda não foi gerado nenhum PLR p/ o(s) funcionário(s) no período selecionado ...
if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        alert('SALVE PRIMEIRO O(S) DADO(S) DE PLR P/ DEPOIS GERAR O RELATÓRIO DE IMPRESSÃO !')
        window.close()
    </Script>
<?	
}else {//Significa que já foi gerado pelo menos 1 PLR p/ o(s) func(s) selecionado(s) ...
    for($i = 0; $i < $linhas; $i++) {
//Se a Empresa for Albafer ou Tool Master, então imprimir 2 vias por folha ...
        if($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2) {
//Se for Grupo então só imprime uma única via ...
            $qtde_vias = 2;
        }else {
            $qtde_vias = 1;
        }
        for($j = 0; $j < $qtde_vias; $j++) {
            if($qtde_vias == 2) {//Se for 2 vias de Impressão por funcionário então ...
//Só imprime 2 cópias por folha ...
                if($j % 2 == 0) $pdf->AddPage();
            }else {//Apenas 1 única via de Impressão por folha, no caso de funcs. sem Registro ...
//Só imprime 2 cópias por folha ...
                if($i % 2 == 0) $pdf->AddPage();
            }
            $pdf->Cell($GLOBALS['ph']*97, 7, '', 'LRT', 1, 'L');
//Títulos ...
            $pdf->SetFont('Arial', 'B', 10.5);
            $pdf->Cell($GLOBALS['ph']*97, 5, 'EMPREGADOR - '.$campos[$i]['razaosocial'].' - CNPJ - '.$campos[$i]['cnpj'], 'LR', 1, 'C');
            $pdf->Cell($GLOBALS['ph']*97, 5, 'PARTICIPAÇÃO NOS LUCROS OU RESULTADOS NO PERÍODO - '.$campos[$i]['periodo'], 'LR', 1, 'C');
//Linha em Branco ...
            $pdf->Cell($GLOBALS['ph']*97, 10, '', 'LR', 1, 'L');
//Nome ...
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell($GLOBALS['ph']*11, 5.2, '    Nome: ', 'L', 0, 'L');
            $pdf->SetFont('Arial', '', 14);
            $pdf->Cell($GLOBALS['ph']*86, 5.2, strtoupper($campos[$i]['nome']), 'R', 1, 'L');
//Qtde de Faltas ...
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell($GLOBALS['ph']*21, 5.2, '    Qtde de Faltas: ', 'L', 0, 'L');
            $pdf->SetFont('Arial', '', 14);
            $pdf->Cell($GLOBALS['ph']*76, 5.2, number_format($campos[$i]['qtde_faltas'], 1, ',', '.').' dias', 'R', 1, 'L');
//Proporcionalidade ...
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell($GLOBALS['ph']*25, 5.2, '    Proporcionalidade: ', 'L', 0, 'L');
            $pdf->SetFont('Arial', '', 14);
            $pdf->Cell($GLOBALS['ph']*72, 5.2, $campos[$i]['qtde_meses'].'/6', 'R', 1, 'L');
//Sindicalizado ...
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell($GLOBALS['ph']*20, 5.2, '    Sindicalizado: ', 'L', 0, 'L');
            $pdf->SetFont('Arial', '', 14);
            if($campos[$i]['sindicalizado'] == 'S') {//Quando não é descontado então ...
                $sindicalizado = 'SIM';
            }else {//Se já é descontado ...
                $sindicalizado = 'NÃO';
            }
            $pdf->Cell($GLOBALS['ph']*77, 5.2, $sindicalizado, 'R', 1, 'L');
//Linha em Branco ...
            $pdf->Cell($GLOBALS['ph']*97, 5, '', 'LR', 1, 'L');
//Vlr Red Absenteismo ...
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell($GLOBALS['ph']*32, 5.2, '    Valor Red. Absenteismo: ', 'L', 0, 'L');
            $pdf->SetFont('Arial', '', 14);
            $pdf->Cell($GLOBALS['ph']*65, 5.2, 'R$ '.number_format($campos[$i]['valor_red_absenteismo'], 2, ',', '.'), 'R', 1, 'L');
//Vlr Aumento Produção ...
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell($GLOBALS['ph']*33, 5.2, '    Valor Aumento Produção: ', 'L', 0, 'L');
            $pdf->SetFont('Arial', '', 14);
            $pdf->Cell($GLOBALS['ph']*64, 5.2, 'R$ '.number_format($campos[$i]['valor_aumento_producao'], 2, ',', '.'), 'R', 1, 'L');
//Total de Vencimentos ...
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell($GLOBALS['ph']*29, 5.2, '    Total de Vencimentos: ', 'L', 0, 'L');
            $pdf->SetFont('Arial', '', 14);
            $pdf->Cell($GLOBALS['ph']*68, 5.2, 'R$ '.number_format($campos[$i]['valor_red_absenteismo'] + $campos[$i]['valor_aumento_producao'], 2, ',', '.'), 'R', 1, 'L');
//Linha em Branco ...
            $pdf->Cell($GLOBALS['ph']*97, 7, '', 'LR', 1, 'L');
//Desconto Sindicato ...
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell($GLOBALS['ph']*26, 5.2, '    Desconto Sindicato: ', 'L', 0, 'L');
            $pdf->SetFont('Arial', '', 14);
            $pdf->Cell($GLOBALS['ph']*71, 5.2, ' R$ '.number_format($campos[$i]['desconto_sindicato'], 2, ',', '.'), 'R', 1, 'L');
//Linha em Branco ...
            $pdf->Cell($GLOBALS['ph']*97, 3, '', 'LR', 1, 'L');
//Valor Líquido ...
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell($GLOBALS['ph']*19, 5.2, '    Valor Líquido: ', 'L', 0, 'L');
            $pdf->SetFont('Arial', '', 14);
            $pdf->Cell($GLOBALS['ph']*78, 5.2, ' R$ '.number_format($campos[$i]['valor_total'], 2, ',', '.').' ('.ucfirst(extenso($campos[$i]['valor_total'], '', 1)).')', 'R', 1, 'L');
//Linha em Branco ...
            $pdf->Cell($GLOBALS['ph']*97, 10, '', 'LR', 1, 'L');
//Declaro ter ...
            $pdf->SetFont('Arial', '', 10.5);
            $pdf->Cell($GLOBALS['ph']*97, 5.2, '     DECLARO TER RECEBIDO A IMPORTÂNCIA LÍQUIDA DISCRIMINADA NESTE RECIBO.', 'LR', 1, 'L');
//Linha em Branco ...
            $pdf->Cell($GLOBALS['ph']*97, 11, '', 'LR', 1, 'L');
//Assinatura do Funcionário ...
            $pdf->SetFont('Arial', '', 10.5);
            $pdf->Cell($GLOBALS['ph']*57, 5.2, '     _______________________________', 'L', 0, 'L');
            $pdf->Cell($GLOBALS['ph']*40, 5.2, '             __________________________', 'R', 1, 'L');
            $pdf->Cell($GLOBALS['ph']*57, 5.2, '                       ASSINATURA', 'L', 0, 'L');
            $pdf->Cell($GLOBALS['ph']*40, 5.2, 'DATA', 'R', 1, 'C');
//Linha em Branco ...
            $pdf->Cell($GLOBALS['ph']*97, 2, '', 'LRB', 1, 'L');
            $pdf->Ln(10);
        }
    }
}

chdir('../../../../../pdf');
$file='../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>