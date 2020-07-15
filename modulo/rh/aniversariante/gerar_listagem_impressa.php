<?
require('../../../lib/pdf/fpdf.php');
require('../../../lib/segurancas.php');
/******************************************************************************/
/********************************Gerador de PDF********************************/
/******************************************************************************/
if(!empty($_FILES['txt_imagem'])) {
    /////////////////////////////////////// INÍCIO PDF /////////////////////////////////////////////////////////
    define('FPDF_FONTPATH', 'font/');
    $tipo_papel	= 'P';  // P=> Retrato L=>Paisagem
    $unidade	= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
    $formato_papel	= 'A4'; // A3, A4, A5, Letter, Legal
    $pdf            = new FPDF($tipo_papel, $unidade, $formato_papel);
    $pdf->Open();
    $pdf->SetTopMargin(20);
    $pdf->SetLeftMargin(8);
    $pdf->AddPage();

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
    
    $vetor_meses = array('', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');
    $pdf->SetFont('Arial', 'B', 24);
    $pdf->Cell($GLOBALS['ph'] * 100, 8, 'Lista de aniversariantes do mês de '.$vetor_meses[intval(date('m'))], 0, 0, 'C');
    
    $extensao = strchr($_FILES['txt_imagem']['name'], '.');
    $extensao = substr($extensao, 1, strlen($extensao));
    $pdf->Image($_FILES['txt_imagem']['tmp_name'], 25, 42, 170, 85, $extensao);
    
    $pdf->Ln(120);
    $pdf->SetFont('Arial', 'B', 20);

    //Trago somente os funcionarios que ainda trabalham aqui na Empresa no mês Corrente que está em "Vigência" ...
    $sql = "SELECT d.departamento, f.nome, SUBSTRING(f.data_nascimento, 6, 5) AS mes_dia_nascimento 
            FROM `funcionarios` f 
            INNER JOIN `departamentos` d ON d.`id_departamento` = f.`id_departamento` 
            WHERE SUBSTRING(f.`data_nascimento`, 6, 2) = '".date('m')."' 
            AND f.`status` < '3' ORDER BY DAY(f.data_nascimento), f.nome ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) {
        $pdf->Cell($GLOBALS['ph'] * 58, 14, $campos[$i]['nome'], 0, 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 30, 14, $campos[$i]['departamento'], 0, 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 12, 14, substr($campos[$i]['mes_dia_nascimento'], 3, 2).'/'.substr($campos[$i]['mes_dia_nascimento'], 0, 2), 0, 1, 'C');
    }
    chdir('../../../pdf');
    $file='../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
    chdir(dirname(__FILE__));
    $pdf->Output($file);//Save PDF to file
    echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
}
/******************************************************************************/
?>
<html>
<head>
<title>.:: Gerar Listagem Impressa ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../css/layout.css'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Imagem ...
    if(document.form.txt_imagem.value == '') {
        alert('DIGITE O ENDEREÇO DA IMAGEM OU PROCURE A IMAGEM !')
        document.form.txt_imagem.focus()
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_imagem.focus()'>
<form name='form' action='' method='post' onsubmit='return validar()' enctype='multipart/form-data'>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Gerar Listagem Impressa
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Imagem:</b>
        </td>
        <td>
            <input type='file' name='txt_imagem' title='Selecione uma Imagem' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</html>