<?
require('../../../../lib/pdf/fpdf.php');
require('../../../../lib/segurancas.php');
require('../../../../lib/cascates.php');
require('../../../../lib/custos.php');
require('../../../../lib/faturamentos.php');
segurancas::geral('/erp/albafer/modulo/vendas/pedidos/itens/consultar.php', '../../../../');

//Funcao...
function calcular_elemento_quimico($valor1, $valor2, $percentagem) {
    $qtde_elemento_quimico = (($percentagem / 100) * ($valor2 - $valor1) + $valor1); 
    return $qtde_elemento_quimico;
}

if(!empty($_POST['chkt_pedido_venda_item'])) {
/******************************************************************************************/
/*****************************************Relatório****************************************/
/******************************************************************************************/
    define('FPDF_FONTPATH', 'font/');
    $tipo_papel		= 'P';  // P=> Retrato L=>Paisagem
    $unidade		= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
    $formato_papel	= 'A4'; // A3, A4, A5, Letter, Legal
    $pdf			= new FPDF($tipo_papel, $unidade, $formato_papel);
    $pdf->Open();
    $pdf->SetLeftMargin(10);

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
    $vetor_meses = array('', 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro');

    foreach($_POST['chkt_pedido_venda_item'] as $i => $id_pedido_venda_item) {
            $pdf->AddPage();

            $pdf->Image('../../../../imagem/logosistema.jpg', 15, 5, 24, 26, 'JPG');
            $pdf->Image('../../../../imagem/marcas/cabri.png', 44, 20, 10, 10, 'PNG');
            $pdf->Image('../../../../imagem/marcas/heinz.png', 61, 20, 20, 10, 'PNG');
            $pdf->Image('../../../../imagem/marcas/tool.png', 85, 20, 42, 10, 'PNG');
            $pdf->Image('../../../../imagem/marcas/nvo.png', 134, 20, 20, 10, 'PNG');
            $pdf->Image('../../../../imagem/marcas/warrior.jpg', 156, 20, 42, 10, 'JPG');
            $pdf->Ln(12);
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->Cell($GLOBALS['ph']*75, 23, '________________________________________________________________________________________________', 0, 0, 'L');

            $pdf->Ln(25);

            $sql = "SELECT pa.id_produto_acabado, CONCAT(pa.referencia, ' / ', pa.discriminacao) AS detalhes, pa.operacao_custo 
                    FROM `pedidos_vendas_itens` pvi 
                    INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pvi.id_produto_acabado 
                    WHERE pvi.`id_pedido_venda_item` = '$id_pedido_venda_item' LIMIT 1 ";
            $campos = bancos::sql($sql);

            $pdf->Ln(8);
            $pdf->Cell($GLOBALS['ph']*95, 7, 'São Paulo, '.date('d').' de '.$vetor_meses[intval(date('m'))].' '.date('Y').'.', 0, 1, 'L');

            $pdf->SetFont('Arial', 'BU', 16);
            $pdf->Cell($GLOBALS['ph']*65, 23, 'C e r t i f i c a d o  d e  Q u a l i d a d e', 0, 0, 'L');
            $pdf->SetFont('Arial', 'B', 16);
            
            if(!empty($_POST['hdd_pedido_venda'])) {//Certificado acessado de dentro do Pedido de Vendas ...
                $pdf->Cell($GLOBALS['ph']*30, 23, 'N.º '.($i + 1).'-'.$_POST['hdd_pedido_venda'].'-0'.$_POST['txt_percentagem'].'/12', 0, 1, 'L');
            }else if(!empty($_POST['hdd_nf'])) {//Certificado acessado de dentro da Nota Fiscal de Saída ...
                $pdf->Cell($GLOBALS['ph']*30, 23, 'N.º '.($i + 1).'-'.faturamentos::buscar_numero_nf($_POST['hdd_nf'], 'S').'-0'.$_POST['txt_percentagem'].'/12', 0, 1, 'L');
            }

            //Aqui eu busco o Custo do PA do Item do Pedido e já trago a Matéria Prima "2ª" Etapa desse PA ...
            $sql = "SELECT id_produto_acabado_custo, id_produto_insumo 
                    FROM `produtos_acabados_custos` 
                    WHERE `id_produto_acabado` = '".$campos[0]['id_produto_acabado']."' 
                    AND `operacao_custo` = '".$campos[0]['operacao_custo']."' LIMIT 1 ";
            $campos_custo               = bancos::sql($sql);
            $id_produto_acabado_custo   = $campos_custo[0]['id_produto_acabado_custo'];

            //Se encontra na 2ª Etapa do Custo ...
            $materia_prima = '';//Limpa p/ não herdar valor do Loop anterior ...
            if($campos_custo[0]['id_produto_insumo'] > 0) {
                $sql = "SELECT pi.discriminacao 
                        FROM `produtos_insumos` pi 
                        WHERE pi.`id_produto_insumo` = '".$campos_custo[0]['id_produto_insumo']."' LIMIT 1 ";
                $campos_pi 				= bancos::sql($sql);
                $materia_prima		  	= $campos_pi[0]['discriminacao'];
                $id_fornecedor_default 	= custos::preco_custo_pi($campos_custo[0]['id_produto_insumo'], 0, 1);
                //Aqui eu busco a última NF que tem esse PI ...
                $sql = "SELECT `id_nfe_historico` 
                        FROM `nfe_historicos` 
                        WHERE id_produto_insumo = '".$campos_custo[0]['id_produto_insumo']."' 
                        ORDER BY `id_nfe_historico` DESC LIMIT 1";
                $campo_historico = bancos::sql($sql);
                //Aqui atualizo o N.º de Corrida digitado na última NF desse Produto Insumo ...
                $sql = "UPDATE `nfe_historicos` SET `num_corrida` = '".$_POST[txt_num_corrida][$i]."' WHERE `id_nfe_historico` = '".$campo_historico[0]['id_nfe_historico']."' AND `num_corrida` = '' LIMIT 1 ";
                bancos::sql($sql);		
                //Busco a Razão Social do Fornecedor Default ...
                $sql = "SELECT razaosocial 
                        FROM `fornecedores` 
                        WHERE `id_fornecedor` = '$id_fornecedor_default' LIMIT 1 ";
                $campos_fornecedor		= bancos::sql($sql);
                $fornecedor				= $campos_fornecedor[0]['razaosocial'];
            }else {//Se não existe Matéria Prima p/ o PA do Custo ...
                //Verifico se esse PA do Item de Pedido possui outro PA na 7ª Etapa ...
                $sql = "SELECT pp.id_produto_acabado, pa.operacao_custo 
                        FROM `pacs_vs_pas` pp 
                        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pp.id_produto_acabado 
                        WHERE pp.id_produto_acabado_custo = '$id_produto_acabado_custo' LIMIT 1 ";
                $campos_custo_7etapa = bancos::sql($sql);
                if(count($campos_custo_7etapa) == 1) {
                    //Aqui eu busco a Matéria Prima "2ª" Etapa desse PA que está na 7ª Etapa ...
                    $sql = "SELECT id_produto_acabado_custo, id_produto_insumo 
                            FROM `produtos_acabados_custos` 
                            WHERE `id_produto_acabado` = '".$campos_custo_7etapa[0]['id_produto_acabado']."' 
                            AND `operacao_custo` = '".$campos_custo_7etapa[0]['operacao_custo']."' LIMIT 1 ";
                    $campos_custo               = bancos::sql($sql);
                    $id_produto_acabado_custo   = $campos_custo[0]['id_produto_acabado_custo'];
                    //Se encontra na 2ª Etapa do Custo ...
                    $materia_prima = '';//Limpa p/ não herdar valor do Loop anterior ...
                    if($campos_custo[0]['id_produto_insumo'] > 0) {
                        $sql = "SELECT pi.discriminacao 
                                FROM `produtos_insumos` pi 
                                WHERE pi.`id_produto_insumo` = '".$campos_custo[0]['id_produto_insumo']."' LIMIT 1 ";
                        $campos_pi 				= bancos::sql($sql);
                        $materia_prima		  	= $campos_pi[0]['discriminacao'];
                        $id_fornecedor_default 	= custos::preco_custo_pi($campos_custo[0]['id_produto_insumo'], 0, 1);
                        //Aqui eu busco a última NF que tem esse PI ...
                        $sql = "SELECT `id_nfe_historico` 
                                FROM `nfe_historicos` 
                                WHERE id_produto_insumo = '".$campos_custo[0]['id_produto_insumo']."' 
                                ORDER BY `id_nfe_historico` DESC LIMIT 1";
                        $campo_historico = bancos::sql($sql);
                        //Aqui atualizo o N.º de Corrida digitado na última NF desse Produto Insumo ...
                        $sql = "UPDATE `nfe_historicos` SET `num_corrida` = '".$_POST[txt_num_corrida][$i]."' WHERE `id_nfe_historico` = '".$campo_historico[0]['id_nfe_historico']."' AND `num_corrida` = '' LIMIT 1 ";
                        bancos::sql($sql);		
                        //Busco a Razão Social do Fornecedor Default ...
                        $sql = "SELECT razaosocial 
                                FROM `fornecedores` 
                                WHERE `id_fornecedor` = '$id_fornecedor_default' LIMIT 1 ";
                        $campos_fornecedor		= bancos::sql($sql);
                        $fornecedor				= $campos_fornecedor[0]['razaosocial'];
                    }
                }
            }
            //Se encontra na 5ª Etapa do Custo ...
            $tratamento_termico = '';
            $sql = "SELECT pi.discriminacao 
                    FROM `pacs_vs_pis_trat` ppt 
                    INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = ppt.id_produto_insumo 
                    WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
            $campos_tratamento_termico 	= bancos::sql($sql);
            $tratamento_termico             = $campos_tratamento_termico[0]['discriminacao'];

            $pdf->Ln(10);
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->Cell($GLOBALS['ph']*20, 7, 'Produtos:', 'LRT', 0, 'L');
            $pdf->Cell($GLOBALS['ph']*75, 7, $campos[0]['detalhes'], 'LRT', 1, 'L');
            $pdf->Cell($GLOBALS['ph']*20, 7, 'Tolerância:', 'LR', 0, 'L');
            $pdf->Cell($GLOBALS['ph']*75, 7, strtolower($_POST['txt_tolerancia'][$i]), 'LR', 1, 'L');
            $pdf->Cell($GLOBALS['ph']*20, 7, 'Tratamento Térmico:', 'LR', 0, 'L');
            $pdf->Cell($GLOBALS['ph']*75, 7, strtoupper($tratamento_termico), 'LR', 1, 'L');
            $pdf->Cell($GLOBALS['ph']*20, 7, 'Matéria-Prima:', 'LR', 0, 'L');
            $pdf->Cell($GLOBALS['ph']*75, 7, strtoupper($materia_prima), 'LR', 1, 'L');
            $pdf->Cell($GLOBALS['ph']*20, 7, 'Fornecedor / Corrida:', 'LRB', 0, 'L');
            $pdf->Cell($GLOBALS['ph']*75, 7, $fornecedor.' / '.$_POST['txt_num_corrida'][$i], 'LRB', 1, 'L');
            $pdf->Ln(15);

            $sql = "SELECT qac.* 
                            FROM `produtos_insumos` pi
                            INNER JOIN `produtos_insumos_vs_acos` pia ON pia.`id_produto_insumo` = pi.`id_produto_insumo`
                            INNER JOIN `qualidades_acos` qa ON qa.`id_qualidade_aco` = pia.`id_qualidade_aco`
                            INNER JOIN `qualidades_acos_vs_composicoes` qac ON qac.`id_qualidade_aco` = qa.`id_qualidade_aco`
                            WHERE pi.`id_produto_insumo` = '".$campos_custo[0]['id_produto_insumo']."' LIMIT 1";
            $campos_composicoes	= bancos::sql($sql);

            $pdf->Cell($GLOBALS['ph']*98, 7, 'COMPOSIÇÃO QUÍMICA APROXIMADA DO AÇO [%]', 1, 1, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, 'C', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, 'SI', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, 'Mn', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, 'P', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, 'S', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, 'Cr', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, 'Ni', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, 'Mo', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, 'W', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, 'Ti', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, 'V', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, 'Cu', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, 'Al', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, 'Co', 1, 1, 'C');

            $qtde_carbono 		= calcular_elemento_quimico($campos_composicoes[0]['carbono1'], $campos_composicoes[0]['carbono2'], $_POST['txt_percentagem']);
            $qtde_silicio 		= calcular_elemento_quimico($campos_composicoes[0]['silicio1'], $campos_composicoes[0]['silicio2'], $_POST['txt_percentagem']);
            $qtde_manganes 		= calcular_elemento_quimico($campos_composicoes[0]['manganes1'], $campos_composicoes[0]['manganes2'], $_POST['txt_percentagem']);
            $qtde_fosforo 		= calcular_elemento_quimico($campos_composicoes[0]['fosforo1'], $campos_composicoes[0]['fosforo2'], $_POST['txt_percentagem']);
            $qtde_enxofre 		= calcular_elemento_quimico($campos_composicoes[0]['enxofre1'], $campos_composicoes[0]['enxofre2'], $_POST['txt_percentagem']);
            $qtde_cromo 		= calcular_elemento_quimico($campos_composicoes[0]['cromo1'], $campos_composicoes[0]['cromo2'], $_POST['txt_percentagem']);
            $qtde_niquel 		= calcular_elemento_quimico($campos_composicoes[0]['niquel1'], $campos_composicoes[0]['niquel2'], $_POST['txt_percentagem']);
            $qtde_molibdenio 	= calcular_elemento_quimico($campos_composicoes[0]['molibdenio1'], $campos_composicoes[0]['molibdenio2'], $_POST['txt_percentagem']);
            $qtde_tungstenio 	= calcular_elemento_quimico($campos_composicoes[0]['tungstenio1'], $campos_composicoes[0]['tungstenio2'], $_POST['txt_percentagem']);
            $qtde_titanio 		= calcular_elemento_quimico($campos_composicoes[0]['titanio1'], $campos_composicoes[0]['titanio2'], $_POST['txt_percentagem']);
            $qtde_vanadio 		= calcular_elemento_quimico($campos_composicoes[0]['vanadio1'], $campos_composicoes[0]['vanadio2'], $_POST['txt_percentagem']);
            $qtde_cobre 		= calcular_elemento_quimico($campos_composicoes[0]['cobre1'], $campos_composicoes[0]['cobre2'], $_POST['txt_percentagem']);
            $qtde_aluminio 		= calcular_elemento_quimico($campos_composicoes[0]['aluminio1'], $campos_composicoes[0]['aluminio2'], $_POST['txt_percentagem']);
            $qtde_cobalto 		= calcular_elemento_quimico($campos_composicoes[0]['cobalto1'], $campos_composicoes[0]['cobalto2'], $_POST['txt_percentagem']);

            $qtde_carbono 		= (!empty($qtde_carbono)) ? number_format($qtde_carbono, 3, ',', '.') : '';
            $qtde_silicio 		= (!empty($qtde_silicio)) ? number_format($qtde_silicio, 3, ',', '.') : '';
            $qtde_manganes 		= (!empty($qtde_manganes)) ? number_format($qtde_manganes, 3, ',', '.') : '';
            $qtde_fosforo 		= (!empty($qtde_fosforo)) ? number_format($qtde_fosforo, 3, ',', '.') : '';
            $qtde_enxofre 		= (!empty($qtde_enxofre)) ? number_format($qtde_enxofre, 3, ',', '.') : '';
            $qtde_cromo 		= (!empty($qtde_cromo)) ? number_format($qtde_cromo, 3, ',', '.') : '';
            $qtde_niquel 		= (!empty($qtde_niquel)) ? number_format($qtde_niquel, 3, ',', '.') : '';
            $qtde_molibdenio 	= (!empty($qtde_molibdenio)) ? number_format($qtde_molibdenio, 3, ',', '.') : '';
            $qtde_tungstenio 	= (!empty($qtde_tungstenio)) ? number_format($qtde_tungstenio, 3, ',', '.') : '';
            $qtde_titanio 		= (!empty($qtde_titanio)) ? number_format($qtde_titanio, 3, ',', '.') : '';
            $qtde_vanadio 		= (!empty($qtde_vanadio)) ? number_format($qtde_vanadio, 3, ',', '.') : '';
            $qtde_cobre 		= (!empty($qtde_cobre)) ? number_format($qtde_cobre, 3, ',', '.') : '';
            $qtde_aluminio 		= (!empty($qtde_aluminio)) ? number_format($qtde_aluminio, 3, ',', '.') : '';
            $qtde_cobalto 		= (!empty($qtde_cobalto)) ? number_format($qtde_cobalto, 3, ',', '.') : '';

            $pdf->Cell($GLOBALS['ph']*7, 7, $qtde_carbono, 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, $qtde_silicio, 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, $qtde_manganes, 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, $qtde_fosforo, 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, $qtde_enxofre, 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, $qtde_cromo , 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, $qtde_niquel, 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, $qtde_molibdenio, 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, $qtde_tungstenio, 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, $qtde_titanio, 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, $qtde_vanadio, 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, $qtde_cobre, 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, $qtde_aluminio, 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph']*7, 7, $qtde_cobalto, 1, 0, 'C');

            $pdf->Ln(25);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell($GLOBALS['ph']*95, 5, 'Certificado gerado automaticamente.', 0, 1, 'L');

            $pdf->Ln(30);
            
            /*Sem assinatura porque decidimos que é melhor o Depto. Técnico conferir antes de assinar 
            - mudança realizada em 26/07/2016 ...*/
            //$pdf->Image('../../../../imagem/assinatura_certificado.png', 160, 200, 24, 26, 'PNG');
            $pdf->Cell($GLOBALS['ph']*60, 7, '', 0, 0, '');
            $pdf->Cell($GLOBALS['ph']*40, 7, '____________________________________', 0, 1, 'C');
            $pdf->Cell($GLOBALS['ph']*60, 7, '', 0, 0, '');
            $pdf->Cell($GLOBALS['ph']*40, 7, 'Depto. Técnico', 0, 1, 'C');
            $pdf->Cell($GLOBALS['ph']*60, 7, '', 0, 0, '');
            $pdf->Cell($GLOBALS['ph']*40, 7, 'Grupo Albafér', 0, 1, 'C');

            $pdf->Ln(30);
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell($GLOBALS['ph']*7, 1, 'Central de Atendimento:', 0, 1, 'L');
            $pdf->Cell($GLOBALS['ph']*7, 5, '_________________________________________________________________________________________________________________________', 0, 1, 'L');
            $pdf->Cell($GLOBALS['ph']*7, 3, 'ALBAFÉR - Indústria e Comércio de Ferramentas Ltda. - TOOL MASTER - Indústria Metalúrgica Ltda.', 0, 1, 'L');
            $pdf->Cell($GLOBALS['ph']*7, 3, 'Rua Dias da Silva, 1173/1183 - Vila Maria - São Paulo - SP - Brasil - CEP: 02114-001', 0, 1, 'L');
            $pdf->Cell($GLOBALS['ph']*7, 3, 'PABX (Fone/Fax): (+55 11)2972-5655                 -                 Visite-nos em: www.grupoalbafer.com.br.', 0, 1, 'L');
    }

    chdir('../../../../pdf');
    $file='../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
    chdir(dirname(__FILE__));
    $pdf->Output($file);//Save PDF to file
    echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
    exit;
}
/******************************************************************************************/

/*Esse certificado de Qualidade pode ser acessado de 2 locais: 

1º) Aqui dentro do Pedido de Vendas ...
2º) Dentro da Nota Fiscal de Saída ...*/

if(!empty($_GET['id_pedido_venda'])) {//Certificado acessado de dentro do Pedido de Vendas ...
    $sql = "SELECT gpa.tolerancia, pvi.id_pedido_venda_item, pa.id_produto_acabado, pa.referencia, pa.discriminacao, pa.operacao_custo, pvi.qtde, pvi.preco_liq_final 
            FROM `pedidos_vendas_itens` pvi
            INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pvi.id_produto_acabado 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
            INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
            WHERE pvi.id_pedido_venda = '$_GET[id_pedido_venda]' ORDER BY pa.discriminacao ";
}else if(!empty($_GET['id_nf'])) {//Certificado acessado de dentro da Nota Fiscal de Saída ...
    $sql = "SELECT gpa.tolerancia, pvi.id_pedido_venda_item, pa.id_produto_acabado, pa.referencia, pa.discriminacao, pa.operacao_custo, pvi.qtde, pvi.preco_liq_final 
            FROM `nfs_itens` nfsi 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = nfsi.`id_pedido_venda_item` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            WHERE nfsi.`id_nf` = '$_GET[id_nf]' ORDER BY pvi.id_pedido_venda, pa.discriminacao ";
}
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Certificado de Qualidade ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'certificado_qualidade.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos = document.form.elements
    var cont_checkbox_selecionados = 0, total_linhas = 0
    var total_itens_pedidos = eval('<?=$linhas;?>')
    for (var i = 0; i < elementos.length; i++) {
        if (elementos[i].type == 'checkbox') {
            if(elementos[i].name == 'chkt_pedido_venda_item[]') {//Só vasculho os checkbox de Pedidos de Vendas ...
                if(elementos[i].checked) cont_checkbox_selecionados++
                total_linhas++
            }
        }
    }
    if(cont_checkbox_selecionados == 0) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }
    if(document.form.txt_percentagem.value < 10 || document.form.txt_percentagem.value > 90) {
        alert('PERCENTAGEM INVÁLIDA !!! VALORES VÁLIDOS DE 10 À 90 !')
        document.form.txt_percentagem.focus()
        document.form.txt_percentagem.select()
        return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='95%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr></tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='8'>
            Certificado de Qualidade - 
            <?$percentagem = rand(10, 90);?>
            Percentagem: <input type='text' name="txt_percentagem" value="<?=$percentagem?>" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" maxlength="2" size="3" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Qtde
        </td>
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Preço Líq. Final
        </td>
        <td>
            Tolerância
        </td>
        <td>
            N° Corrida
        </td>				
        <td>
            Total
        </td>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class='checkbox'>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $total = $campos[$i]['qtde'] * $campos[$i]['preco_liq_final'];
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=intval($campos[$i]['qtde']);?>
        </td>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td align='right'>
            R$ <?=number_format($campos[$i]['preco_liq_final'], 2, ',', '.');?>
        </td>
        <td>
            <input type='text' name="txt_tolerancia[]" id="txt_tolerancia<?=$i;?>" value="<?=$campos[$i]['tolerancia'];?>" maxlength="5" size="6" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" class='textdisabled' disabled>
        </td>		
        <td>
            <?
                $sql = "SELECT id_produto_insumo 
                        FROM `produtos_acabados_custos` 
                        WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                        AND `operacao_custo` = '".$campos[$i]['operacao_custo']."' LIMIT 1 ";
                $campos_custo 		= bancos::sql($sql);
                if($campos_custo[0]['id_produto_insumo'] > 0) {
                    //Busco o N.º da Corrida na Última NF em que foi feita a compra desse Aço ...
                    $sql = "SELECT nfeh.num_corrida 
                            FROM `nfe_historicos` nfeh 
                            INNER JOIN `nfe` ON nfe.id_nfe = nfeh.id_nfe 
                            WHERE nfeh.`id_produto_insumo` = '".$campos_custo[0]['id_produto_insumo']."' ORDER BY nfeh.id_nfe_historico DESC LIMIT 1 ";
                    $campos_nfe		= bancos::sql($sql);
                    $num_corrida	= $campos_nfe[0]['num_corrida'];
                }
            ?>
            <input type='text' name='txt_num_corrida[]' id='txt_num_corrida<?=$i;?>' value="<?=$num_corrida;?>" maxlength="15" size="17" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class="textdisabled" disabled>
        </td>
        <td align=right>
            R$ <?=number_format($total, 2, ',', '.');?>
        </td>						
        <td>
            <input type='checkbox' name='chkt_pedido_venda_item[]' id='chkt_pedido_venda_item<?=$i;?>' value="<?=$campos[$i]['id_pedido_venda_item'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class="checkbox">
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
        <?
            if(!empty($_GET['id_pedido_venda'])) {//Certificado acessado de dentro do Pedido de Vendas ...
                $url = 'outras_opcoes.php?id_pedido_venda='.$_GET['id_pedido_venda'];
            }else if(!empty($_GET['id_nf'])) {//Certificado acessado de dentro da Nota Fiscal de Saída ...
                $url = '../../../faturamento/nota_saida/itens/outras_opcoes.php?id_nf='.$_GET['id_nf'];
            }
        ?>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '<?=$url;?>'" class='botao'>
            <input type='submit' name='cmd_imprimir' value='Imprimir' title='Imprimir' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<input type='hidden' name='hdd_pedido_venda' value='<?=$_GET['id_pedido_venda'];?>'>
<input type='hidden' name='hdd_nf' value='<?=$_GET['id_nf'];?>'>
</form>
</body>