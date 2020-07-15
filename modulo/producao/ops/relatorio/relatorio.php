<?
require('../../../../lib/pdf/fpdf.php');
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
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

if(isset($chkt_op)) {//Quando forem feitas impressões pelas telas de Consultar e Alterar OP ...
    $vetor_ops = $chkt_op;
}else if(isset($id_ops)) {//Quando forem geradas OP(s) em Lote ...
    $vetor_ops = explode(',', $id_ops);
}else if(isset($id_op)) {//Quando for gerada uma única OP ...
    $vetor_ops = explode(',', $id_op);
}

foreach($vetor_ops as $id_op) {
    //Já faço uma marcação na O.P, p/ saber que está foi impressa e não exibir + na lista de O.P(s) Pendentes
    $sql = "UPDATE `ops` SET `impresso` = 'S' WHERE `id_op` = '$id_op' LIMIT 1 ";
    bancos::sql($sql);
    
    $pdf->Open();
    $pdf->SetTopMargin(3);
    $pdf->SetLeftMargin(3);
    $pdf->AddPage();
    $pdf->SetAutoPageBreak('false', 0);

    //Busca de alguns dados com o id_op ...
    $sql = "SELECT ops.*, pa.`referencia`, pa.`discriminacao`, pa.`operacao_custo`, pa.`desenho_para_op`, pa.`observacao` AS observacao_pa 
            FROM ops 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ops.`id_produto_acabado` 
            WHERE ops.`id_op` = '$id_op' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $id_produto_acabado = $campos[0]['id_produto_acabado'];
    $operacao_custo     = $campos[0]['operacao_custo'];

//Busca do id_produto_acabado_custo com o id_produto_acabado e operacao_custo ...
    $sql = "SELECT `id_produto_acabado_custo` 
            FROM `produtos_acabados_custos` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' 
            AND `operacao_custo` = '$operacao_custo' LIMIT 1 ";
    $campos_custo               = bancos::sql($sql);
    $id_produto_acabado_custo   = $campos_custo[0]['id_produto_acabado_custo'];

    //Comentado Temporariamente ...
    //LogoTipo da Empresa
    //$pdf->Image('../../../../imagem/logo_transparente.jpg', 7, 5, 28, 30, 'JPG');

    /********************************************Ordem de Produção********************************************/
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($GLOBALS['ph'] * 76, 5, 'ORDEM DE PRODUÇÃO', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 21, 5, 'N.º OP  '.$campos[0]['id_op'], 1, 1, 'C');
    $pdf->Ln(3);
    /****************************************************************************************************/
    //Ref:
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell($GLOBALS['ph'] * 3, 5, 'Ref: ', 'LTB', 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell($GLOBALS['ph'] * 16, 5, $campos[0]['referencia'], 'RTB', 0, 'C');

    //Qtde:
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell($GLOBALS['ph'] * 3, 5, 'Qtde: ', 'LTB', 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell($GLOBALS['ph'] * 12, 5, number_format($campos[0]['qtde_produzir'], 2, ',', '.'), 'RTB', 0, 'C');
    //Variável utilizada mais abaixo ...
    $nova_qtde_produzir = $campos[0]['qtde_produzir'];

    //Data de Emissão:
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell($GLOBALS['ph'] * 12.5, 5, 'Data de Emissão: ', 'LTB', 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell($GLOBALS['ph'] * 10, 5, data::datetodata($campos[0]['data_emissao'], '/'), 'RTB', 0, 'C');

    //Prazo de Entrega
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($GLOBALS['ph'] * 14, 5, 'Prazo de Entrega: ', 'LTB', 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell($GLOBALS['ph'] * 10.5, 5, data::datetodata($campos[0]['prazo_entrega'], '/'), 'RTB', 0, 'C');

    //Emissor
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($GLOBALS['ph'] * 16, 5, 'Emissor: ', 'LRT', 1, 'L');

    /****************************************************************************************************/	
    //Busca de dados da 2ª Etapa ...			
    $sql = "SELECT gpa.`id_familia`, pa.`referencia`, pac.`id_produto_insumo`, pac.`peca_corte`, pac.`qtde_lote`, pac.`comprimento_1`, pac.`comprimento_2` 
            FROM `produtos_acabados_custos` pac 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            WHERE pac.`id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
    $campos_etapa2 = bancos::sql($sql);
    //Busca dados do PI se realmente existir ...
    if($campos_etapa2[0]['id_produto_insumo'] > 0) {
        $sql = "SELECT ei.`qtde` AS estoque_pi, pi.`discriminacao`, pia.`densidade_aco` 
                FROM `produtos_insumos` pi 
                INNER JOIN `estoques_insumos` ei ON ei.`id_produto_insumo` = pi.`id_produto_insumo` 
                INNER JOIN `produtos_insumos_vs_acos` pia ON pia.`id_produto_insumo` = pi.`id_produto_insumo` 
                WHERE pi.`id_produto_insumo` = '".$campos_etapa2[0]['id_produto_insumo']."' LIMIT 1 ";
        $campos_pi_etapa2 = bancos::sql($sql);
    }
    $estoque_pi         = $campos_pi_etapa2[0]['estoque_pi'];
    $densidade 		= $campos_pi_etapa2[0]['densidade_aco'];
    $comprimento_total 	= ($campos_etapa2[0]['comprimento_1'] + $campos_etapa2[0]['comprimento_2']) / 1000;
    //O cálculo p/ Pinos com PA(s) = 'ESP' é o mesmo com 5% a mais da Quantidade ...
    $fator_perda            = ($campos_etapa2[0]['id_familia'] == 2 && $campos_etapa2[0]['referencia'] == 'ESP') ? 1.10 : 1.05;
    $peso_aco_kg 		= $densidade * $comprimento_total * $fator_perda;
    //Discriminação
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($GLOBALS['ph'] * 13, 5, 'Discriminação: ', 'LTB', 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell($GLOBALS['ph'] * 68, 5, $campos[0]['discriminacao'].' - Corte: '.$campos_etapa2[0]['comprimento_2'].' MM', 'RTB', 0, 'L');
    //Nome do Login que está emitindo a OP ...
    $sql = "SELECT `login` 
            FROM `logins` 
            WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
    $campos_login = bancos::sql($sql);
    $pdf->Cell($GLOBALS['ph'] * 16, 5, $campos_login[0]['login'], 'LRB', 1, 'L');

    /*Se o Item é Especial, então eu verifico se este está em orçamento, para ver para qual 
    Cliente que está sendo vendida esse PA ...*/
    if($campos[0]['referencia'] == 'ESP') {
        $sql = "SELECT IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente 
                FROM `orcamentos_vendas_itens` ovi 
                INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
                INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
                WHERE ovi.`id_produto_acabado` = '$id_produto_acabado' ORDER BY ov.`id_orcamento_venda` DESC LIMIT 1 ";
        $campos_cliente = bancos::sql($sql);
        if(count($campos_cliente) == 1) {//Se encontrou cliente ...
            $cliente = $campos_cliente[0]['cliente'];
        }else {//Caso não tenha encontrado ...
            $cliente = '-';
        }
    }else {//Não é um PA feito para Cliente, então ...
        $cliente = '-';
    }

    //Cliente
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($GLOBALS['ph'] * 6.5, 5, 'Cliente: ', 'LTB', 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell($GLOBALS['ph'] * 35.5, 5, $cliente, 'RTB', 0, 'L');

    //Obs. do PA
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($GLOBALS['ph'] * 10, 5, 'Obs. do PA: ', 'LTB', 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell($GLOBALS['ph'] * 45, 5, $campos[0]['observacao_pa'], 'RTB', 1, 'L');

    //Obs. da OP
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($GLOBALS['ph'] * 10, 5, 'Obs. da OP: ', 'LTB', 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell($GLOBALS['ph'] * 87, 5, $campos[0]['observacao'], 'RTB', 1, 'L');
    $pdf->Ln(3);

    /******************************Esse Controle vem de outra Tela******************************/
    //Aqui serve para atualizar a qtde do lote na Etapa 2, que já reflete nas outras etapas
    if($nova_qtde_produzir > 0) {
        $qtde_lote = $nova_qtde_produzir;
    }else {//Qtde Lote
        $qtde_lote = $campos_etapa2[0]['qtde_lote'];
    }
    /*******************************************************************************************/

    //Aqui são os cálculos para q Qtde do Lote do Custo
    $lote_custo_calculo1 = $peso_aco_kg * $qtde_lote / $campos_etapa2[0]['peca_corte'];
    $lote_custo_calculo2 = $lote_custo_calculo1 / $densidade;

    /********************************************Aço********************************************/
    //Aço:
    $pdf->SetFillColor(200, 200, 200);//Cor Cinza
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($GLOBALS['ph'] * 5, 5, 'AÇO: ', 1, 0, 'L', 1);
    $pdf->SetFont('Arial', '', 10);
    $complemento_aco = ($campos_etapa2[0]['peca_corte'] > 1) ? ' - '.$campos_etapa2[0]['peca_corte'].' (pçs/corte)' : '';

    $pdf->Cell($GLOBALS['ph'] * 92, 5, $campos_pi_etapa2[0]['discriminacao'].$complemento_aco.' - Estoque: '.$estoque_pi.' kg(s)', 1, 1, 'L');
    /****************************************************************************************************/

    //Qtde. [kg]:
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($GLOBALS['ph'] * 9, 5, 'Qtde. [kg]: ', 'LTB', 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell($GLOBALS['ph'] * 11, 5, number_format($lote_custo_calculo1, 2, ',', '.'), 'RTB', 0, 'L');

    //Qtde. [metros]:
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($GLOBALS['ph'] * 13, 5, 'Qtde. [metros]: ', 'LTB', 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell($GLOBALS['ph'] * 15, 5, number_format($lote_custo_calculo2, 2, ',', '.'), 'RTB', 0, 'L');

    //Almoxarife:
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($GLOBALS['ph'] * 12, 5, 'Almoxarife: ', 'LTB', 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell($GLOBALS['ph'] * 37, 5, '', 'RTB', 1, 'L');

    $pdf->Ln(3);

    /********************************************Tercerizados********************************************/
    /*Aqui traz todos os PI(s) da 5ª e 6ª Etapa - Trat. T&eacute;rmico / Galvanoplastia 
    e Usinagem Externo ...*/
    $sql = "SELECT pi.`discriminacao`, ppt.`fator`, ppt.`peso_aco`, ppt.`peso_aco_manual`, u.`sigla` 
            FROM `pacs_vs_pis_trat` ppt 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppt.`id_produto_insumo` 
            INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
            WHERE ppt.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY pi.`discriminacao` ";
    $campos_etapa5 = bancos::sql($sql);
    $linhas_etapa5 = count($campos_etapa5);
	
    //Aqui traz todos os PI(s) que estão relacionado ao id_produto_acabado da OP - 3ª Etapa ... 
    $sql = "SELECT pi.`discriminacao`, ppu.`qtde`, u.`sigla` 
            FROM `pacs_vs_pis_usis` ppu 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppu.`id_produto_insumo` 
            INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
            WHERE ppu.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY pi.`discriminacao` ";
    $campos_etapa6 = bancos::sql($sql);
    $linhas_etapa6 = count($campos_etapa6);

    if($linhas_etapa5 > 0 || $linhas_etapa6 > 0) {
        $contador = 1;
        if($linhas_etapa5 > 0) {
            //Disparando o Loop ...
            for($j = 0; $j < $linhas_etapa5; $j++) {
                //Serv Terc:
                $pdf->SetFillColor(200, 200, 200);//Cor Cinza ...

                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell($GLOBALS['ph'] * 10, 10, $contador.'ºServ Terc', 1, 0, 'L', 1);
                $pdf->SetFont('Arial', '', 7);
                //Peso Aço Manual está checado
                if($campos_etapa5[$j]['peso_aco_manual'] == 1) {
                    $peso_tt = number_format($campos_etapa5[$j]['peso_aco'], 3, ',', '.');
                }else {
                    $peso_tt = number_format($campos_etapa5[$j]['peso_aco'] * $campos_etapa5[$j]['fator'], 3, ',', '.');
                }
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell($GLOBALS['ph'] * 87, 10, $campos_etapa5[$j]['discriminacao'].' ('.$peso_tt.' '.$campos_etapa5[$j]['sigla'].' / pç) ', 1, 1, 'L');

                //Fornecedor:
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell($GLOBALS['ph'] * 65, 5, 'Fornecedor: ', 1, 0, 'L');

                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell($GLOBALS['ph'] * 32, 5, 'Qtde. Saída e Data:           /       /      ', 'RTL', 1, 'L');

                /****************************************************************************************************/

                //Aprovado p/ envio:
                $pdf->SetFont('Arial', 'B', 10);
                //Imagem do sim ...
                $pdf->Image('../../../../imagem/bloco_branco.jpg', 37, $pdf->GetY() + 1, 3, 3, 'JPG');
                //Imagem do não ...
                $pdf->Image('../../../../imagem/bloco_branco.jpg', 51, $pdf->GetY() + 1, 3, 3, 'JPG');
                $pdf->Cell($GLOBALS['ph'] * 65, 5, 'Aprovado p/ envio:     SIM        NÃO', 1, 0, 'L');

                $pdf->Cell($GLOBALS['ph'] * 16, 5, '', 'R', 0, 'R');
                $pdf->Cell($GLOBALS['ph'] * 16, 5, '', 'R', 1, 'R');

                //Resp. pelo envio:
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell($GLOBALS['ph'] * 65, 5, 'Resp. pelo envio: ', 1, 0, 'L');

                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell($GLOBALS['ph'] * 16, 5, '[pçs]', 'RBL', 0, 'R');
                $pdf->Cell($GLOBALS['ph'] * 16, 5, '[kg]', 'RBL', 1, 'R');

                /****************************************************************************************************/
                //1ª Linha ...
                $pdf->SetFont('Arial', 'B', 10);

                //Qtde. 1º Retorno e Data:
                $pdf->Cell($GLOBALS['ph'] * 34, 5, 'Qtde. 1º Retorno e Data:        /     /   ', 'LRT', 0, 'L');
                //Aprovado
                $pdf->Cell($GLOBALS['ph'] * 13.5, 5, 'Aprovado', 'LRT', 0, 'C');

                //Espaço em branco ...
                $pdf->Cell($GLOBALS['ph'] * 2, 5, '', 'LR', 0, 'C');

                $pdf->SetFont('Arial', 'B', 10);
                //2º Serviço:
                $pdf->Cell($GLOBALS['ph'] * 34, 5, 'Qtde. 2º Retorno e Data:        /     /   ', 'LRT', 0, 'L');
                //Aprovado
                $pdf->Cell($GLOBALS['ph'] * 13.5, 5, 'Aprovado', 'LRT', 1, 'C');


                //2ª Linha ...
                $pdf->Cell($GLOBALS['ph'] * 17, 5, '', 'LR', 0, 'L');
                $pdf->Cell($GLOBALS['ph'] * 17, 5, '', 'LR', 0, 'L');
                //Aprovado
                $pdf->Cell($GLOBALS['ph'] * 13.5, 5, '', 'LR', 0, 'C');

                //Espaço em branco ...
                $pdf->Cell($GLOBALS['ph'] * 2, 5, '', 'LR', 0, 'C');

                $pdf->Cell($GLOBALS['ph'] * 17, 5, '', 'LR', 0, 'L');
                $pdf->Cell($GLOBALS['ph'] * 17, 5, '', 'LR', 0, 'L');
                //Aprovado
                $pdf->Cell($GLOBALS['ph'] * 13.5, 5, '', 'LR', 1, 'C');

                //3ª Linha ...
                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell($GLOBALS['ph'] * 17, 5, '[pçs]', 'LRB', 0, 'R');
                $pdf->Cell($GLOBALS['ph'] * 17, 5, '[kg]', 'LRB', 0, 'R');
                //Aprovado
                $pdf->Cell($GLOBALS['ph'] * 13.5, 5, '', 'LRB', 0, 'C');

                //Espaço em branco ...
                $pdf->Cell($GLOBALS['ph'] * 2, 5, '', 'LR', 0, 'C');

                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell($GLOBALS['ph'] * 17, 5, '[pçs]', 'LRB', 0, 'R');
                $pdf->Cell($GLOBALS['ph'] * 17, 5, '[kg]', 'LRB', 0, 'R');
                //Aprovado
                $pdf->Cell($GLOBALS['ph'] * 13.5, 5, '', 'LRB', 1, 'C');

                $pdf->Ln(3);
                $contador++;
            }
        }

        if($linhas_etapa6 > 0) {
            //Disparando o Loop ...
            for($j = 0; $j < $linhas_etapa6; $j++) {
                //Serv Terc:
                $pdf->SetFillColor(200, 200, 200);//Cor Cinza ...

                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell($GLOBALS['ph'] * 10, 10, $contador.'ºServ Terc', 1, 0, 'L', 1);
                $pdf->SetFont('Arial', '', 7);

                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell($GLOBALS['ph'] * 87, 10, $campos_etapa6[$j]['discriminacao'].' ('.number_format($campos_etapa6[$j]['qtde'], 2, ',', '.').' '.$campos_etapa6[$j]['sigla'].') ', 1, 1, 'L');

                //Fornecedor:
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell($GLOBALS['ph'] * 65, 5, 'Fornecedor: ', 1, 0, 'L');

                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell($GLOBALS['ph'] * 32, 5, 'Qtde. Saída e Data:           /       /      ', 'RTL', 1, 'L');

                /****************************************************************************************************/

                //Aprovado p/ envio:
                $pdf->SetFont('Arial', 'B', 10);
                //Imagem do sim ...
                $pdf->Image('../../../../imagem/bloco_branco.jpg', 37, $pdf->GetY() + 1, 3, 3, 'JPG');
                //Imagem do não ...
                $pdf->Image('../../../../imagem/bloco_branco.jpg', 51, $pdf->GetY() + 1, 3, 3, 'JPG');
                $pdf->Cell($GLOBALS['ph'] * 65, 5, 'Aprovado p/ envio:     SIM        NÃO', 1, 0, 'L');

                $pdf->Cell($GLOBALS['ph'] * 16, 5, '', 'R', 0, 'R');
                $pdf->Cell($GLOBALS['ph'] * 16, 5, '', 'R', 1, 'R');

                //Resp. pelo envio:
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell($GLOBALS['ph'] * 65, 5, 'Resp. pelo envio: ', 1, 0, 'L');

                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell($GLOBALS['ph'] * 16, 5, '[pçs]', 'RBL', 0, 'R');
                $pdf->Cell($GLOBALS['ph'] * 16, 5, '[kg]', 'RBL', 1, 'R');

                /****************************************************************************************************/
                //1ª Linha ...
                $pdf->SetFont('Arial', 'B', 10);

                //Qtde. 1º Retorno e Data:
                $pdf->Cell($GLOBALS['ph'] * 34, 5, 'Qtde. 1º Retorno e Data:        /     /   ', 'LRT', 0, 'L');
                //Aprovado
                $pdf->Cell($GLOBALS['ph'] * 13.5, 5, 'Aprovado', 'LRT', 0, 'C');

                //Espaço em branco ...
                $pdf->Cell($GLOBALS['ph'] * 2, 5, '', 'LR', 0, 'C');

                $pdf->SetFont('Arial', 'B', 10);
                //2º Serviço:
                $pdf->Cell($GLOBALS['ph'] * 34, 5, 'Qtde. 2º Retorno e Data:        /     /   ', 'LRT', 0, 'L');
                //Aprovado
                $pdf->Cell($GLOBALS['ph'] * 13.5, 5, 'Aprovado', 'LRT', 1, 'C');


                //2ª Linha ...
                $pdf->Cell($GLOBALS['ph'] * 17, 5, '', 'LR', 0, 'L');
                $pdf->Cell($GLOBALS['ph'] * 17, 5, '', 'LR', 0, 'L');
                //Aprovado
                $pdf->Cell($GLOBALS['ph'] * 13.5, 5, '', 'LR', 0, 'C');

                //Espaço em branco ...
                $pdf->Cell($GLOBALS['ph'] * 2, 5, '', 'LR', 0, 'C');

                $pdf->Cell($GLOBALS['ph'] * 17, 5, '', 'LR', 0, 'L');
                $pdf->Cell($GLOBALS['ph'] * 17, 5, '', 'LR', 0, 'L');
                //Aprovado
                $pdf->Cell($GLOBALS['ph'] * 13.5, 5, '', 'LR', 1, 'C');

                //3ª Linha ...
                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell($GLOBALS['ph'] * 17, 5, '[pçs]', 'LRB', 0, 'R');
                $pdf->Cell($GLOBALS['ph'] * 17, 5, '[kg]', 'LRB', 0, 'R');
                //Aprovado
                $pdf->Cell($GLOBALS['ph'] * 13.5, 5, '', 'LRB', 0, 'C');

                //Espaço em branco ...
                $pdf->Cell($GLOBALS['ph'] * 2, 5, '', 'LR', 0, 'C');

                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell($GLOBALS['ph'] * 17, 5, '[pçs]', 'LRB', 0, 'R');
                $pdf->Cell($GLOBALS['ph'] * 17, 5, '[kg]', 'LRB', 0, 'R');
                //Aprovado
                $pdf->Cell($GLOBALS['ph'] * 13.5, 5, '', 'LRB', 1, 'C');

                $pdf->Ln(3);
                $contador++;
            }
        }
    }
    /********************************************Insumos / Componentes********************************************/
    /*Aqui traz todos os PI(s) - embalagens que estão relacionado ao id_produto_acabado 
    da OP - 1ª Etapa ...*/
    $sql = "SELECT pi.`discriminacao`, ppe.`pecas_por_emb` 
            FROM `pas_vs_pis_embs` ppe 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppe.`id_produto_insumo` 
            WHERE ppe.`id_produto_acabado` = '$id_produto_acabado' ORDER BY pi.`discriminacao` ";
    $campos_etapa1 = bancos::sql($sql);
    $linhas_etapa1 = count($campos_etapa1);

    //Aqui traz todos os PI(s) que estão relacionado ao id_produto_acabado da OP - 3ª Etapa ... 
    $sql = "SELECT pi.`discriminacao`, pp.`qtde` 
            FROM `pacs_vs_pis` pp 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = pp.`id_produto_insumo` 
            WHERE pp.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY pi.`discriminacao` ";
    $campos_etapa3 = bancos::sql($sql);
    $linhas_etapa3 = count($campos_etapa3);

    //Aqui traz todos os PA(s) que estão relacionado ao id_produto_acabado da OP - 7ª Etapa ...
    $sql = "SELECT pa.`referencia`, pa.`discriminacao`, pp.`qtde` 
            FROM `pacs_vs_pas` pp 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pp.`id_produto_acabado` 
            WHERE pp.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY pa.`discriminacao` ";
    $campos_etapa7 = bancos::sql($sql);
    $linhas_etapa7 = count($campos_etapa7);

    if($linhas_etapa1 > 0 || $linhas_etapa3 > 0 || $linhas_etapa7 > 0) {
    /*****************************************Cabeçalho*****************************************/
        $pdf->SetFillColor(200, 200, 200);//Cor Cinza
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($GLOBALS['ph'] * 15, 5, 'Qtde.', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 77, 5, 'Discriminação Insumo / Componente', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 5, 5, 'Etapa', 1, 1, 'C');

        $pdf->SetFont('Arial', '', 10);
        if($linhas_etapa1 > 0) {
            //Disparando o Loop ...
            for($j = 0; $j < $linhas_etapa1; $j++) {
                $pdf->Cell($GLOBALS['ph'] * 15, 5, number_format($campos_etapa1[$j]['pecas_por_emb'], 0, '', '.').' pçs / emb', 1, 0, 'C');
                $pdf->Cell($GLOBALS['ph'] * 77, 5, $campos_etapa1[$j]['discriminacao'], 1, 0, 'L');
                $pdf->Cell($GLOBALS['ph'] * 5, 5, '1', 1, 1, 'C');
            }
        }
	
        if($linhas_etapa3 > 0) {
            //Disparando o Loop ...
            for($j = 0; $j < $linhas_etapa3; $j++) {
                $pdf->Cell($GLOBALS['ph'] * 15, 5, number_format($campos_etapa3[$j]['qtde'], 0, '', '.'), 1, 0, 'C');
                $pdf->Cell($GLOBALS['ph'] * 77, 5, $campos_etapa3[$j]['discriminacao'], 1, 0, 'L');
                $pdf->Cell($GLOBALS['ph'] * 5, 5, '3', 1, 1, 'C');
            }
        }

        if($linhas_etapa7 > 0) {
            //Disparando o Loop ...
            for($j = 0; $j < $linhas_etapa7; $j++) {
                $pdf->Cell($GLOBALS['ph'] * 15, 5, number_format($campos_etapa7[$j]['qtde'], 0, '', '.'), 1, 0, 'C');
                $pdf->Cell($GLOBALS['ph'] * 77, 5, $campos_etapa7[$j]['referencia'].' - '.$campos_etapa7[$j]['discriminacao'], 1, 0, 'L');
                $pdf->Cell($GLOBALS['ph'] * 5, 5, '7', 1, 1, 'C');
            }
        }
        $pdf->Ln(5);
    }

    //Aqui traz todas as Máquina(s) que estão relacionado ao id_produto_acabado da OP - 4ª Etapa ...
    $sql = "SELECT m.`nome`, pm.`tempo_hs` 
            FROM `pacs_vs_maquinas` pm 
            INNER JOIN `maquinas` m ON m.`id_maquina` = pm.`id_maquina` 
            WHERE pm.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY m.`nome` ";
    $campos_etapa4 = bancos::sql($sql);
    $linhas_etapa4 = count($campos_etapa4);
    if($linhas_etapa4 > 0) {
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell($GLOBALS['ph'] * 25, 5, 'Processo / ', 'RLT', 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 7, 5, 'Tempo hs ', 'RLT', 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 49, 5, 'Tempo hs Real', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 10, 5, 'Operador', 'RLT', 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 6, 5, 'CQ', 'RLT', 1, 'C');
        $pdf->Cell($GLOBALS['ph'] * 25, 5, 'Máquina', 'RLB', 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 7, 5, 'Custo', 'RLB', 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 7, 5, 'Início', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 7, 5, 'Fim', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 7, 5, 'Início', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 7, 5, 'Fim', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 7, 5, 'Início', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 7, 5, 'Fim', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 7, 5, 'Total', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 10, 5, '', 'RLB', 0, 'C');
        $pdf->Cell($GLOBALS['ph'] * 6, 5, '', 'RLB', 1, 'C');
//Disparando o Loop ...
        $pdf->SetFont('Arial', '', 7);
        for($j = 0; $j < $linhas_etapa4; $j++) {
            $pdf->Cell($GLOBALS['ph'] * 25, 7, $campos_etapa4[$j]['nome'], 1, 0, 'L');
            /*Eu pego o Tempo do Custo e divido pelo tempo do Lote do Custo p/ saber o tempo unitário e 
            múltiplico pelo Total da Qtde de Peças a Produzir da OP ...*/
            $pdf->Cell($GLOBALS['ph'] * 7, 7, number_format(($campos_etapa4[$j]['tempo_hs'] / $campos_etapa2[0]['qtde_lote']) * $campos[0]['qtde_produzir'], 1, ',', '.'), 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph'] * 7, 7, '', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph'] * 7, 7, '', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph'] * 7, 7, '', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph'] * 7, 7, '', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph'] * 7, 7, '', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph'] * 7, 7, '', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph'] * 7, 7, '', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph'] * 10, 7, '', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph'] * 6, 7, '', 1, 1, 'C');
        }
        //Aqui eu gero 3 linhas extras, para alguma observação Extra ...
        for($j = 0; $j < 3; $j++) {
            $pdf->Cell($GLOBALS['ph'] * 25, 7, '', 1, 0, 'L');
            $pdf->Cell($GLOBALS['ph'] * 7, 7, '', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph'] * 7, 7, '', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph'] * 7, 7, '', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph'] * 7, 7, '', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph'] * 7, 7, '', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph'] * 7, 7, '', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph'] * 7, 7, '', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph'] * 7, 7, '', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph'] * 10, 7, '', 1, 0, 'C');
            $pdf->Cell($GLOBALS['ph'] * 6, 7, '', 1, 1, 'C');
        }
        $pdf->Ln(5);
    }

    //Estoque Aprovado na Inspeção Final:
    $pdf->SetFont('Arial', 'B', 12);
    //Imagem do sim ...
    $pdf->Image('../../../../imagem/bloco_branco.jpg', 83, $pdf->GetY() + 2, 3, 3, 'JPG');
    //Imagem do não ...
    $pdf->Image('../../../../imagem/bloco_branco.jpg', 106, $pdf->GetY() + 2, 3, 3, 'JPG');
    $pdf->Cell($GLOBALS['ph'] * 36, 8, 'Estoque aprovado na Inspeção Final: ', 1, 0, 'L', 1);
    $pdf->Cell($GLOBALS['ph'] * 19, 8, '     SIM             NÃO', 1, 0, 'L');

    //Resp. pelo envio:
    $pdf->Cell($GLOBALS['ph'] * 42, 8, 'Responsável: ', 1, 1, 'L');
    $pdf->Ln(5);
    /********************************************Qtde em Estoque********************************************/
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($GLOBALS['ph'] * 32, 8, 'Qtde em estoque: ', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph'] * 20, 8, 'Data:           /         /   ', 1, 0, 'L');
    $pdf->Cell($GLOBALS['ph'] * 45, 8, 'Visto: ', 1, 1, 'L');


    //Verifico se existe algum Item nesta 5ª Etapa que está com Indução ...
    $sql = "SELECT ppt.`id_pac_pi_trat` 
            FROM `pacs_vs_pis_trat` ppt 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppt.`id_produto_insumo` AND pi.`discriminacao` LIKE '%INDUÇÃO%' 
            WHERE ppt.`id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
    $campos_inducao = bancos::sql($sql);
    //Se existe, então o Sistema dá um aviso de modo a avisar o Depto. Técnico que precisa fazer furo de Centro ...
    if(count($campos_inducao) == 1) {
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell($GLOBALS['ph'] * 97, 8, '***INDUÇÃO - FAZER FURO DE CENTRO DO(S) 2 LADO(S) DA PEÇA***', 0, 1, 'C');
    }
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell($GLOBALS['ph'] * 97, 8, 'Data e Hora de Impressão: '.date('d/m/Y').' - '.date('H:i:s'), 0, 1, 'R');

    //Imagem
    if(!empty($campos[0]['desenho_para_op'])) {//Se existir algum desenho ...
        $pdf->AddPage();//Adiciono uma nova página somente p/ a figura ...
        $pdf->Image('../../../../imagem/fotos_produtos_acabados/'.$campos[0]['desenho_para_op'], 5, 2, 290, 220, 'PNG');
    //Medida da Imagem - 33 x 21.6 ...
        $pdf->Image('../../../../imagem/fotos_produtos_acabados/'.$campos[0]['desenho_para_op'], 1, 1, 216, 330, 'PNG');
    }
}

chdir('../../../../pdf');
$file = '../../../../pdf/'.basename(tempnam(str_replace(trim('/'),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file

//Esse controle só na Tela de Alterar OP(s), pois a idéia é de que se imprima uma OP e essa já desapareça na mesma hora da Tela Pós-Filtro ...
if($_POST['hdd_atualizar_alterar'] == 'S') {
    echo "<html><body onunload='opener.document.location = \"../".$_POST['hdd_arquivo_que_chamou_impressao'].$parametro."\"'></body><Script Language='JavaScript'>document.location='$file';</Script></html>";//JavaScript redirection
}else {
    echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
}
?>