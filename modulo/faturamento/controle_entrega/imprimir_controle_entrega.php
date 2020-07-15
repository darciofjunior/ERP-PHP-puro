<?
require('../../../lib/pdf/fpdf.php');
require('../../../lib/segurancas.php');
require('../../../lib/calculos.php');
require('../../../lib/cascates.php');
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../../lib/genericas.php');//Essa biblioteca é requerida dentro da Intermodular ...
require('../../../lib/intermodular.php');//Essa biblioteca é utilizada dentro da Biblioteca 'faturamentos' ...
segurancas::geral('/erp/albafer/modulo/faturamento/controle_entrega/controle_entrega.php', '../../../');

function verificar_vide_notas($id_nf, $id_cliente, $id_empresa_nota, $numero_nf_ac = '') {
    //Aqui vai acumulando todos os Núms. de Nota
    $numero_nf_ac.= faturamentos::buscar_numero_nf($id_nf, 'S').' <- ';

    $sql = "SELECT `id_nf` 
            FROM `nfs` 
            WHERE `id_cliente` = '$id_cliente' 
            AND `id_nf_vide_nota` = '$id_nf' ORDER BY numero_nf ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($j = 0; $j < $linhas; $j++) {
        //Busco um Representante ...
        $sql = "SELECT `id_representante` 
                FROM `nfs_itens` 
                WHERE `id_nf` = '$id_nf' LIMIT 1 ";
        $campos_representante = bancos::sql($sql);

        //Registro um Follow-UP com o nome do Motorista que fará a Entrega da Mercadoria ...
        $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_representante`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_cliente', '".$campos_representante[0]['id_representante']."', '$_SESSION[id_funcionario]', '$id_nf', '5', 'Entregue por: ".$_POST['cmb_motorista']." ', '".date('Y-m-d')."'); ";
        bancos::sql($sql);
        
        //Aqui eu atualizo as Vide-Notas vinculadas a NF que foi passada por parâmetro ...
        $sql = "UPDATE `nfs` SET `status` = 4, `tipo_despacho` = '3', `data_saida_entrada` = '".date('Y-m-d')."' 
                WHERE `id_nf` = '".$campos[$j]['id_nf']."' LIMIT 1 ";
        bancos::sql($sql);
        $numero_nf_ac = verificar_vide_notas($campos[$j]['id_nf'], $id_cliente, $id_empresa_nota, $numero_nf_ac);
    }
    return $numero_nf_ac;
}

/******************************************************************************************/
/*****************************************Relatório****************************************/
/******************************************************************************************/

define('FPDF_FONTPATH', 'font/');
$tipo_papel		= 'P';  // P=> Retrato L=>Paisagem
$unidade		= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel          = 'A4'; // A3, A4, A5, Letter, Legal
$pdf			= new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
$pdf->SetLeftMargin(2);
$pdf->SetTopMargin(2);
global $pv,$ph; //valor baseado em mm do A4

if(!empty($formato_papel)) {
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

$data       = 'Data: '.date('d/m/Y');
$motorista  = 'Motorista: '.$_POST['cmb_motorista'];

$pdf->AddPage();
$pdf->Cell($GLOBALS['ph'] * 102, 279, '', 'TFLBR', 0, 'C');
$pdf->Image('../../../imagem/logosistema.jpg', 11, 3, 25, 29, 'JPG');
$pdf->Image('../../../imagem/marcas/cabri.png', 38, 3, 11, 11, 'PNG');
$pdf->Image('../../../imagem/marcas/heinz.png', 61, 3, 18, 10, 'PNG');
$pdf->Image('../../../imagem/marcas/tool.png', 38, 24, 17, 6, 'PNG');
$pdf->Image('../../../imagem/marcas/nvo.png', 50, 15, 12, 7, 'PNG');
$pdf->Image('../../../imagem/marcas/warrior.jpg', 61, 23, 17, 7, 'JPG');
$pdf->SetFont('Arial', '', 9);
$pdf->Ln(3);
$pdf->Cell($GLOBALS['ph'] * 40, 5, '', 0, 0, 'C');
$pdf->Cell($GLOBALS['ph'] * 35, 5, 'Rua Dias da Silva, 1.183 - V. Maria', 0, 0, 'L');
$pdf->SetFont('Arial', 'U', 9);
$pdf->Cell($GLOBALS['ph'] * 23, 5, 'Controle de Entregas', 0, 1, 'C');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell($GLOBALS['ph'] * 40, 5, '', 0, 0, 'C');
$pdf->Cell($GLOBALS['ph'] * 72, 5, 'Cep: 02114-002 - São Paulo - SP', 0, 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 40, 5, '', 0, 0, 'C');
$pdf->Cell($GLOBALS['ph'] * 35, 5, 'PABX: (11) 2972-5655', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($GLOBALS['ph'] * 25, 5, $motorista, 0, 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 40, 5, '', 0, 0, 'C');
$pdf->Cell($GLOBALS['ph'] * 72, 5, 'Site: www.gupoalbafer.com.br', 0, 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 40, 5, '', 0, 0, 'C');
$pdf->Cell($GLOBALS['ph'] * 35, 5, 'E-mail: albafer@grupoalbafer.com.br', 0, 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 25, 5, $data, 0, 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 72, 5, '_________________________________________________________________________________________________________', 0, 0, 'L');
$pdf->Ln(7);

//Tratamento p/ não furar o SQL abaixo ...
$id_nfs = (!empty($_POST['chkt_nf'])) ? implode($_POST['chkt_nf'], ',') : 0;

//Aqui eu agrupo todas as NFs selecionadas da Tela anterior por Cliente ...
$sql = "SELECT c.`razaosocial`, c.`id_pais`, c.`cidade`, 
        CONCAT(c.`endereco`, ', ', c.`num_complemento`, ' - ', c.`bairro`, ' - ', c.`cep`, ' - ', c.`cidade`) AS logradouro, 
        nfs.`id_transportadora`, nfs.`id_cliente`, nfs.`id_empresa`, nfs.`id_nf_vide_nota`, 
        nfs.`suframa` 
        FROM `nfs` 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
        WHERE nfs.`id_nf` IN ($id_nfs) GROUP BY nfs.`id_cliente` ORDER BY nfs.`data_emissao` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetLeftMargin(3);
    
//Limpo essas variáveis p/ não herdar Valores do Loop Anterior ...
    $valor_total_nota_por_cliente       = 0;
    $numeros_notas_fiscais_por_cliente  = '';
    $qtde_volume_total_por_cliente      = 0;
    $peso_bruto_total_por_cliente       = 0;
    $menor_data_emissao_por_cliente     = '';
    
/*Aqui eu busco todas as Notas Fiscais do Cliente do Loop, baseado nos checkbox que foram selecionados 
na tela abaixo, esse "for" nas linhas mais abaixo com índice $j serve para fazer agrupamento de Notas Fiscais por Cliente ...*/
    $sql = "SELECT `id_nf`, `id_empresa`, DATE_FORMAT(`data_emissao`, '%d/%m/%Y') AS data_emissao 
            FROM `nfs` 
            WHERE `id_nf` IN ($id_nfs) 
            AND `id_cliente` = '".$campos[$i]['id_cliente']."' ORDER BY `data_emissao` DESC ";
    $campos_nf = bancos::sql($sql);
    $linhas_nf = count($campos_nf);
    for($j = 0; $j < $linhas_nf; $j++) {
        //Pego Dados de Peso da Nota Fiscal do Loop ...
        $calculo_peso_nf 	= faturamentos::calculo_peso_nf($campos_nf[$j]['id_nf']);
        $qtde_volume_total_por_cliente+= $calculo_peso_nf['qtde_caixas'];
        $peso_bruto_total_por_cliente+= $calculo_peso_nf['peso_bruto_total'];
        
        //Pego o N.º da Nota Fiscal do Loop ...
        $vide_notas = verificar_vide_notas($campos_nf[$j]['id_nf'], $campos[$i]['id_cliente'], $campos_nf[$j]['id_empresa']);
        $vide_notas = substr($vide_notas, 0, strlen($vide_notas) - 4);
        $numeros_notas_fiscais_por_cliente.= $vide_notas.', ';

        //Pego o Valor Total da Nota Fiscal do Loop ...
        $nota_sgd               = ($campos_nf[$j]['id_empresa'] == 1 || $campos_nf[$j]['id_empresa'] == 2) ? 'N' : 'S';
        $calculo_total_impostos = calculos::calculo_impostos(0, $campos_nf[$j]['id_nf'], 'NF');
        $valor_total_nota_por_cliente+= $calculo_total_impostos['valor_total_nota'];
        
        /*Se ficar em Loop várias vezes p/ o determinado Cliente, estou tranquilo porque que essa variável 
        sempre herdará a Menor Data de Emissão, devido essa Query começar a sua ordenação por Data Emissão DESC ..., 
        Essa variável "$menor_data_emissao_por_cliente" vai sobrepondo as Datas de Emissão ...*/
        $menor_data_emissao_por_cliente = $campos_nf[$j]['data_emissao'];
        
        //Busco um Representante ...
        $sql = "SELECT `id_representante` 
                FROM `nfs_itens` 
                WHERE `id_nf` = '".$campos_nf[$j]['id_nf']."' LIMIT 1 ";
        $campos_representante = bancos::sql($sql);
        
        /**********************************************************************************************/
        //Registro um Follow-UP com o nome do Motorista que fará a Entrega da Mercadoria ...
        $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_representante`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '".$campos[$i]['id_cliente']."', '".$campos_representante[0]['id_representante']."', '$_SESSION[id_funcionario]', '".$campos_nf[$j]['id_nf']."', '5', 'Entregue por: ".$_POST['cmb_motorista']." ', '".date('Y-m-d')."'); ";
        bancos::sql($sql);
        
        //Aqui eu atualizo o Status da Nota Fiscal do Loop p/ "Despachada" e mais alguns campos ...
        $sql = "UPDATE `nfs` SET `status` = 4, `tipo_despacho` = '3', `data_saida_entrada` = '".date('Y-m-d')."' 
                WHERE `id_nf` = '".$campos_nf[$j]['id_nf']."' LIMIT 1 ";
        bancos::sql($sql);
        
        //Verifico se já foi registrado esse "id_nf" na tabela `controles_entregas` ...
        $sql = "SELECT `id_controle_entrega`
                FROM `controles_entregas`
                WHERE `id_nf` = '".$campos_nf[$j]['id_nf']."' LIMIT 1 ";
        $campos_controle_entrega = bancos::sql($sql);
        if(count($campos_controle_entrega) == 0) {//Ainda não foi registrado, sendo assim vou gerar um registro ...
            /*Esse outro INSERT nessa tabela `controles_entregas` me servirá p´/ visualizar tudo o que foi 
            Entregue no Relatório de "Controle de Entregas" esta no Módulo de Faturamento ...*/
            $sql = "INSERT INTO `controles_entregas` (`id_controle_entrega`, `id_nf`, `qtde_volume`, `peso`, `motorista`, `data_sys`) 
                    VALUES (NULL, '".$campos_nf[$j]['id_nf']."', '".$calculo_peso_nf['qtde_caixas']."', '".$calculo_peso_nf['peso_bruto_total']."', '$_POST[cmb_motorista]', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
        }
    }
    $numeros_notas_fiscais_por_cliente = substr($numeros_notas_fiscais_por_cliente, 0, strlen($numeros_notas_fiscais_por_cliente) - 2);

//Se a Transportadora for Diferente de Nosso Carro, busco o endereço da Transportadora ...
    if($campos[$i]['id_transportadora'] != 795 && $campos[$i]['id_transportadora'] != 1098) {
        $sql = "SELECT IF(nome_fantasia = '', nome, nome_fantasia) AS transportadora, CONCAT(endereco, ', ', num_complemento, ' - ', bairro, ' - ', cep, ' - ', cidade) AS logradouro 
                FROM `transportadoras` 
                WHERE `id_transportadora` = '".$campos[$i]['id_transportadora']."' LIMIT 1 ";
        $campos_transportadora  = bancos::sql($sql);

        $pdf->Cell($GLOBALS['ph'] * 7, 7, 'Firma: ', 'TL', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 70, 7, $campos[$i]['razaosocial'].' ('.$campos[$i]['cidade'].')', 'T', 0, 'L');
        $pdf->SetFont('Arial', 'U', 11);
        $pdf->Cell($GLOBALS['ph'] * 24, 7, 'Qtde Vol: '.$qtde_volume_total_por_cliente.' - Peso: '.number_format($peso_bruto_total_por_cliente, '1', ',', '.').' Kg', 'TR', 1, 'L');
        $pdf->SetFont('Arial', '', 11);          
        $pdf->Cell($GLOBALS['ph'] * 15, 7, 'Transportadora: ', 'L', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 86, 7, $campos_transportadora[0]['transportadora'], 'R', 1, 'L');
        if(!empty($_POST['txt_observacao'][$i])) {
            $pdf->Cell($GLOBALS['ph'] * 101, 7, 'Observação: '.ucwords(strtolower($_POST['txt_observacao'][$i])), 'LR', 1, 'L');
        }
        $pdf->Cell($GLOBALS['ph'] * 13, 7, 'End. Transp.: ', 'L', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 88, 7, $campos_transportadora[0]['logradouro'], 'R', 1, 'L');

        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell($GLOBALS['ph'] * 3, 7, 'NF: ', 'LB', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 24, 7,  $numeros_notas_fiscais_por_cliente, 'B', 0, 'L');

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 6, 7, 'Vlr R$ ', 'B', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 9, 7, number_format($valor_total_nota_por_cliente, 2, ',', '.'), 'B', 0, 'L');

        $pdf->Cell($GLOBALS['ph'] * 8, 7, 'Emissão: ', 'B', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 10, 7, $menor_data_emissao_por_cliente, 'B', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 41, 7, 'Chegada: __________ Hs. Saída: __________ Hs.', 'BR', 1, 'L');
    }else {
        $pdf->Cell($GLOBALS['ph'] * 7, 7, 'Firma: ', 'TL', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 70, 7, $campos[$i]['razaosocial'].' ('.$campos[$i]['cidade'].')', 'T', 0, 'L');
        $pdf->SetFont('Arial', 'U', 11);
        $pdf->Cell($GLOBALS['ph'] * 24, 7, 'Qtde Vol: '.$qtde_volume_total_por_cliente.' - Peso: '.number_format($peso_bruto_total_por_cliente, '1', ',', '.').' Kg', 'TR', 1, 'L');
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell($GLOBALS['ph'] * 6, 7, 'End.: ', 'L', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 97, 7, $campos[$i]['logradouro'], 'R', 1, 'L');
        if(!empty($_POST['txt_observacao'][$i])) {
            $pdf->Cell($GLOBALS['ph'] * 101, 7, 'Observação: '.ucwords(strtolower($_POST['txt_observacao'][$i])), 'LR', 1, 'L');    
        }
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell($GLOBALS['ph'] * 3, 7, 'NF: ', 'LB', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 24, 7, $numeros_notas_fiscais_por_cliente, 'B', 0, 'L');

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($GLOBALS['ph'] * 6, 7, 'Vlr R$ ', 'B', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 9, 7, number_format($valor_total_nota_por_cliente, 2, ',', '.'), 'B', 0, 'L');
        
        $pdf->Cell($GLOBALS['ph'] * 8, 7, 'Emissão: ', 'B', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 10, 7, $menor_data_emissao_por_cliente, 'B', 0, 'L');        
        $pdf->Cell($GLOBALS['ph'] * 41, 7, 'Chegada: __________ Hs. Saída: __________ Hs.', 'BR', 1, 'L');        
    }
    $soma_valor_nf+=            $valor_total_nota_por_cliente;
    $soma_qtde_volume+=         $qtde_volume_total_por_cliente; 
    $soma_peso_bruto_volume+=   $peso_bruto_total_por_cliente;
    
    if($pdf->GetY() > 220) $pdf->AddPage();//Equivale a +/- 9 Clientes por Página ...
}

if(!empty($_POST['hdd_fornecedores_atrelados'])) {
    //Aqui é arrancada a última vírgula do hidden que foi submetido, p/ não gerar um registro de Fornecedor a mais ...
    $_POST['hdd_fornecedores_atrelados'] = substr($_POST['hdd_fornecedores_atrelados'], 0, strlen($_POST['hdd_fornecedores_atrelados']) - 1);
    $vetor_fornecedor = explode(',', $_POST['hdd_fornecedores_atrelados']);
    foreach($vetor_fornecedor as $i => $id_fornecedor) {
        if(in_array($i, $_POST['hdd_indice'])) {
            $qtde_volume            = $_POST['txt_qtde_volume'][$i];
            $peso_bruto_total       = $_POST['txt_peso_bruto'][$i];
        }else {
            $qtde_volume            = '';
            $peso_bruto_total       = '';
        } 
        $sql = "SELECT CONCAT(f.`razaosocial`, '(', f.`nomefantasia`, ')') AS fornecedor, CONCAT(f.`endereco`, ', ', f.`num_complemento`, ', ', f.`bairro`, ' - ', f.`cidade`, ' - ', f.`cep`, ' - ', UPPER(ufs.`sigla`)) AS logradouro 
                FROM `fornecedores` f 
                LEFT JOIN `ufs` ON ufs.`id_uf` = f.`id_uf` 
                WHERE f.`id_fornecedor` = '$id_fornecedor' ";
        $campos = bancos::sql($sql);
        $pdf->Cell($GLOBALS['ph'] * 11, 7, 'Fornecedor: ', 'TL', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 92, 7, ucwords(strtolower($campos[0]['fornecedor'])), 'TR', 1, 'L');
        $pdf->Cell($GLOBALS['ph'] * 13, 7, 'End. Fornec.: ', 'L', 0, 'L');
        $pdf->Cell($GLOBALS['ph'] * 90, 7, ucwords(strtolower($campos[0]['logradouro'])), 'R', 1, 'L');
        $pdf->Cell($GLOBALS['ph'] * 72, 7, 'Chegada: _____________ Hs. Saída: _____________ Hs.', 'LB', 0, 'L');  
        $pdf->SetFont('Arial', 'U', 11);
        $pdf->Cell($GLOBALS['ph'] * 31, 7, 'Qtde Volume: '.$qtde_volume.' - Peso: '.$peso_bruto_total.' Kg', 'BR', 1, 'L'); 
        $pdf->SetFont('Arial', '', 11);
        $soma_qtde_volume           += $qtde_volume; 
        $soma_peso_bruto_volume     += $peso_bruto_total;
    }
}

//Esse Rodapé sempre terá de sair Impresso no fim da Página ...
$pdf->SetY(224);
$pdf->SetLeftMargin(6);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell($GLOBALS['ph'] * 35, 6, 'Total Qtde Volume: '.$soma_qtde_volume.' ', 1, 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 35, 6, 'Total Peso Bruto Volume: '.number_format($soma_peso_bruto_volume, 1, ',', '.').' Kg', 1, 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 29, 6, 'Total Valor R$ '.number_format($soma_valor_nf, 2, ',', '.').' ', 1, 1, 'L');

$pdf->SetFont('Arial', '', 11);
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'P / Manhã', 'TFL', 0, 'C');
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Conf. Estoque', 'TL', 0, 'C');
$pdf->Cell($GLOBALS['ph'] * 29, 7, 'Conf. Portaria', 'TLR', 1, 'C');
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Hora Saída: _________________', 'LR', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 35, 7, '', 'LR', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 29, 7, '', 'LR', 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Hora Chegada: _______________', 'L', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 35, 7, '  ______________________________', 'L', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 29, 7, ' _________________________', 'LR', 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Total KM: ___________________', 'LB', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 35, 7, '', 'LB', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 29, 7, '', 'LBR', 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'R$ p/ Hora - R$ _______________', 'LR', 0, 'L');
$pdf->SetFont('Arial', 'U', 11);
$pdf->Cell($GLOBALS['ph'] * 64, 7, 'Observações', 'R', 1, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Total Hora X _______________', 'LR', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 64, 7, '___________________________________________________________', 'R', 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Total à Pagar R$ ______________', 'LRB', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 64, 7, '___________________________________________________________', 'RB', 1, 'L');

chdir('../../../pdf');
$file='../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>