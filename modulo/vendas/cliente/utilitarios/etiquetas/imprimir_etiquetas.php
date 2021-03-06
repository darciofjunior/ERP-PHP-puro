<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/vendas/cliente/utilitarios/etiquetas/etiquetas.php', '../../../../../');
error_reporting(1);

/*Significa que foi selecionado esse checkbox da Tela Anterior, e sendo assim eu trago de acordo com o 
que foi selecionado pelo Usu�rio*/
if($_POST['chkt_preencher_ignorar_depto'] == 1) {
    $departamento = strtoupper($_POST['txt_departamento']);
}else {//Busca do Nome do Departamento da Pr�pria Combo de Departamento selecionada ...
    $sql = "SELECT departamento 
            FROM `departamentos` 
            WHERE `id_departamento` = '$_POST[cmb_departamento]' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos_departamento    = bancos::sql($sql);
    $departamento           = $campos_departamento[0]['departamento'];
}

/*Aqui dispara o vetor de clientes selecionados*/
foreach($_POST['cmb_clientes_selecionados'] as $id_cliente_selecionado) $clientes.= $id_cliente_selecionado.', ';
$clientes = substr($clientes, 0, strlen($clientes) - 2);

$sql = "SELECT c.id_cliente, TRIM(IF(LENGTH(c.razaosocial) > 40, IF(c.nomefantasia = '', SUBSTRING_INDEX(c.razaosocial, ' ', 6), c.nomefantasia), c.razaosocial)) AS cliente, SUBSTRING_INDEX(c.endereco, ' ', 6) AS endereco, c.num_complemento, c.bairro, c.cep, c.cidade, c.id_pais, ufs.sigla 
        FROM `clientes` c 
        LEFT JOIN `ufs` ON ufs.id_uf = c.id_uf 
        WHERE c.id_cliente IN ($clientes) 
        AND LENGTH(c.cep) = '9' 
        AND c.`ativo` = '1' ORDER BY ufs.sigla, c.cidade, cliente ";
$campos     = bancos::sql($sql);
$registros  = count($campos);

/////////////////////////////////////// IN�CIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel		= 'P';  // P=> Retrato L=>Paisagem
$unidade		= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel          = 'etiqueta_clientes'; // A3, A4, A5, Letter, Legal
$pdf = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->SetTopMargin(1);
$pdf->SetLeftMargin(15);
$pdf->Open();
$pdf->AddPage();
global $pv,$ph; //valor baseado em mm do A4
if($formato_papel == 'etiqueta_clientes') {
    if($tipo_papel == 'P') {
        $pv = 295/100;
        $ph = 205/100;
    }else {
        $pv = 205/100;
        $ph = 295/100;
    }
}else {
    echo 'Formato n�o definido.';
}
$pdf->SetFont('Arial', '', 10);
$linha  = 1;//Valor Default ...

for($i = 0; $i < $registros; $i+=2) {
    //Posso ter apenas 12 linhas em uma p�gina, quando chegar na 13� eu mando inserir uma nova p�gina ...
    if($linha == 13) {
        $linha = 1;
        $pdf->SetTopMargin(1);
        $pdf->AddPage(); 
        $espacador_entre_linhas = 0;
    }
//1� Linha da Etiquetagem
    //Aqui � a Primeira Coluna de Etiquetas, Coluna Esquerda ...
    $pdf->Cell(100, 5, $campos[$i]['cliente'], 0, 0, 'L');
    //Aqui � a Segunda Coluna de Etiquetas, Coluna Direita ...
    $pdf->Cell(100, 5, $campos[$i + 1]['cliente'], 0, 1, 'L');
	
//2� Linha da Etiquetagem
    //Aqui � a Primeira Coluna de Etiquetas, Coluna Esquerda ...
    $end_sem_bairro = $campos[$i]['endereco'].', '.$campos[$i]['num_complemento'];
    if(!empty($campos[$i]['bairro'])) $bairro.=' - '.strtoupper($campos[$i]['bairro']);
    $end_com_bairro = $end_sem_bairro.$bairro;

    if(strlen($end_com_bairro) > 40) {//Se o endereco junto c/ o bairro > 40, imprime o ender. s/ bairro
        $endereco = $end_sem_bairro;
    }else {//Imprime o endere�o completo
        $endereco = $end_com_bairro;
    }
    $pdf->Cell(100, 5, $endereco, 0, 0, 'L');
    //Aqui � a Segunda Coluna de Etiquetas, Coluna Direita ...
    $end_sem_bairro = $campos[$i + 1]['endereco'].', '.$campos[$i + 1]['num_complemento'];
    if(!empty($campos[$i + 1]['bairro'])) $bairro.=' - '.strtoupper($campos[$i + 1]['bairro']);
    $end_com_bairro = $end_sem_bairro.$bairro;

    if(strlen($end_com_bairro) > 40) {//Se o endereco junto c/ o bairro > 40, imprime o ender. s/ bairro
        $endereco = $end_sem_bairro;
    }else {//Imprime o endere�o completo
        $endereco = $end_com_bairro;
    }
    $pdf->Cell(100, 5, $endereco, 0, 1, 'L');

//3� Linha da Etiquetagem	
    //Aqui � a Primeira Coluna de Etiquetas, Coluna Esquerda ...
    if($_POST['chkt_preencher_contato'] == 1) {//Checkbox da tela anterior q traz de acordo com o que foi selecionado pelo user ...
        //Aqui � a Primeira Coluna de Etiquetas, Coluna Esquerda ...
        $pdf->Cell(100, 5, 'A/C DEPTO. '.$departamento.' - '.strtoupper($_POST['txt_contato']), 0, 0, 'L');
        //Aqui � a Segunda Coluna de Etiquetas, Coluna Direita ...
        $pdf->Cell(100, 5, 'A/C DEPTO. '.$departamento.' - '.strtoupper($_POST['txt_contato']), 0, 1, 'L');
    }else {//N�o tem como fazer o SQL de quem � o Contato do Cliente, se o Usu�rio ao menos n�o selecionou o Depto. ...
        if($_POST['chkt_preencher_ignorar_depto'] == 1) {
            //Aqui � a Primeira Coluna de Etiquetas, Coluna Esquerda ...
            $pdf->Cell(100, 5, 'A/C DEPTO. '.$departamento.' - '.strtoupper($_POST['txt_contato']), 0, 0, 'L');
            //Aqui � a Segunda Coluna de Etiquetas, Coluna Direita ...
            $pdf->Cell(100, 5, 'A/C DEPTO. '.$departamento.' - '.strtoupper($_POST['txt_contato']), 0, 1, 'L');
        }else {//Aqui � o �nico caso em que temos 4 linhas p/ a etiqueta ...
            //Aqui � a Primeira Coluna de Etiquetas, Coluna Esquerda ...
            $pdf->Cell(100, 5, $campos[$i]['cep'].' - '.$campos[$i]['cidade'].' - '.$campos[$i]['sigla'], 0, 0, 'L');
            //Aqui � a Segunda Coluna de Etiquetas, Coluna Direita ...
            $pdf->Cell(100, 5, $campos[$i + 1]['cep'].' - '.$campos[$i + 1]['cidade'].' - '.$campos[$i + 1]['sigla'], 0, 1, 'L');
//4� Linha da Etiquetagem			
            $sql = "SELECT SUBSTRING_INDEX(nome, ' ', 2) AS nome 
                    FROM `clientes_contatos` 
                    WHERE `id_cliente` = '".$campos[$i]['id_cliente']."' 
                    AND `id_departamento` = '$_POST[cmb_departamento]' 
                    AND `ativo` = '1' LIMIT 1 ";
            $campos_departamento = bancos::sql($sql);
            if(count($campos_departamento) == 1) {//Busca o Contato do Cliente que est� no Loop de acordo com Depto. que foi selecionado na combo ...
                $pdf->Cell(100, 5, 'A/C DEPTO. '.$departamento.' - '.strtoupper($campos_departamento[0]['nome']), 0, 0, 'L');
            }else {
                $pdf->Cell(100, 5, 'A/C DEPTO. '.$departamento, 0, 0, 'L');
            }
            //Aqui � a Segunda Coluna de Etiquetas, Coluna Direita ...
            $sql = "SELECT SUBSTRING_INDEX(nome, ' ', 2) AS nome 
                    FROM `clientes_contatos` 
                    WHERE `id_cliente` = '".$campos[$i + 1]['id_cliente']."' 
                    AND `id_departamento` = '$_POST[cmb_departamento]' 
                    AND `ativo` = '1' LIMIT 1 ";
            $campos_departamento = bancos::sql($sql);
            if(count($campos_departamento) == 1) {//Busca o Contato do Cliente que est� no Loop de acordo com Depto. que foi selecionado na combo ...
                $pdf->Cell(100, 5, 'A/C DEPTO. '.$departamento.' - '.strtoupper($campos_departamento[0]['nome']), 0, 1, 'L');
            }else {
                $pdf->Cell(100, 5, 'A/C DEPTO. '.$departamento, 0, 1, 'L');
            }
        }
    }
    $pdf->Ln(5);
    $linha++;//A cada linha de Etiqueta que vai sendo printada, eu vou somando nessa vari�vel ...
    if($linha <= 2) {
        $espacador_entre_linhas = 1;
    }else if($linha <= 4) {
        $espacador_entre_linhas = 2;
    }else if($linha <= 6) {
        $espacador_entre_linhas = 3;
    }else if($linha <= 8) {
        $espacador_entre_linhas = 2;
    }else {
        $espacador_entre_linhas = 2.5;
    }
    $pdf->Ln($espacador_entre_linhas);
}
chdir('../../../../../pdf');
$file='../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>