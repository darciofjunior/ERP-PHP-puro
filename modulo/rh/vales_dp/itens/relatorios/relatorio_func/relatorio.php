<?
require('../../../../../../lib/pdf/fpdf.php');
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/comunicacao.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/depto_pessoal.php');
require('../../../../../../lib/genericas.php');
require('../../../../../../lib/variaveis/dp.php');
require('../../../../../../lib/variaveis/intermodular.php');

error_reporting(0);
session_start('funcionarios');

/////////////////////////////////////// IN�CIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel	= 'P';  // P=> Retrato L=>Paisagem
$unidade	= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= 'A4'; // A3, A4, A5, Letter, Legal
$pdf            = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
$pdf->AddPage();
$pdf->SetTopMargin(2);//2.5 deixar este valor
$pdf->SetLeftMargin(12);
$pdf->SetAutoPageBreak('false', 0);

$vetor_tipos_vale = depto_pessoal::tipos_vale();

/*Atrav�s da Data de Holerith eu busco qual � o id Data desta data de Holerith, vou utilizar esse id na 
parte de Holeriths ...*/
$sql = "SELECT id_vale_data 
        FROM `vales_datas` 
        WHERE `data` = '$_GET[cmb_data_holerith]' LIMIT 1 ";
$campos         = bancos::sql($sql);
$id_vale_data   = $campos[0]['id_vale_data'];

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
    echo 'Formato n�o definido';
}

/*Busca de Funcion�rios na Data de Holerith e Desconto de Vale passado por par�metro, "todas as Empresas" 

Obs: S� listo os usu�rios que possuem a Data de Admiss�o menor do que a Data de Holerith - porque sen�o dar� erro
no Sistema, um funcion�rio admitindo recentemente jamais ter� um holerith de 2, 3 anos atr�s por exemplo ... */
if($opt_descontar == 'PF') {
/*Listagem de Funcion�rios que ainda est�o trabalhando ...
* S� n�o exibo os funcion�rios Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes n�o s�o funcion�rios, simplesmente s� possuem cadastrado 
no Sistema p/ poder acessar algumas telas ... //and f.id_funcionario not in (1, 2, 62, 66, 68, 91, 114) 
*/
    $sql = "SELECT DISTINCT(f.id_funcionario), e.id_empresa, e.nomefantasia, f.id_cargo, f.id_empresa, f.codigo_barra, f.nome, f.tipo_salario, f.salario_pd, f.salario_pf, f.salario_premio, f.garantia_salarial, f.pensao_alimenticia, f.valor_metalcred, f.cod_banco, f.agencia, f.conta_corrente 
            FROM `funcionarios` f 
            INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
            WHERE f.`status` < '3' 
            AND f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) 
            AND f.`data_admissao` <= '$_GET[cmb_data_holerith]' ORDER BY e.nomefantasia, f.nome ";
//Busca de Funcion�rios no Desconto de Vale passado por par�metro, "exceto a Empresa Grupo" ...
}else {
/*Listagem de Funcion�rios que ainda est�o trabalhando ...
* S� n�o exibo os funcion�rios Default (1,2), ADAMO 91 e DIRETO BR 114 e os 
diretores Roberto 62, Dona Sandra 66 e Wilson 68 porque estes n�o s�o funcion�rios, simplesmente 
s� possuem cadastrado no Sistema p/ poder acessar algumas telas ... 
//and f.id_funcionario not in (1, 2, 62, 66, 68, 91, 114) */
    $sql = "SELECT DISTINCT(f.id_funcionario), e.id_empresa, e.nomefantasia, f.id_cargo, f.id_empresa, f.codigo_barra, f.nome, f.tipo_salario, f.salario_pd, f.salario_pf, f.salario_premio, f.garantia_salarial, f.pensao_alimenticia, f.valor_pensao_alimenticia, f.valor_metalcred, f.cod_banco, f.agencia, f.conta_corrente 
            FROM `funcionarios` f 
            INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` AND e.`id_empresa` <> '4' 
            WHERE f.`status` < '3' 
            AND f.`salario_pd` > '0' 
            AND f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) 
            AND f.`data_admissao` <= '$_GET[cmb_data_holerith]' ORDER BY e.nomefantasia, f.nome ";
}
$campos = bancos::sql($sql);
$linhas = count($campos);
//Se n�o existir nenhum Vale ent�o retorno essa mensagem informando ao usu�rio ...
if($linhas == 0) {
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell($GLOBALS['ph'] * 90, 4.5, 'N�O EXISTE(M) VALE(S) NESSA DATA DE HOLERITH ESPECIFICADA.', 0, 1, 'C');
//Se existir pelo menos 1 Vale ...
}else {
    $id_empresa_antigo              = '';
    $holeriths_impressos_por_pagina = 0;
    
    /*Aqui nesse array eu guardo quais os 2 maiores funcion�rios "Representantes" 
    que fizeram a maior venda na Data de Holerith especificada ...*/
    $sql = "SELECT `id_funcionario` 
            FROM `funcionarios_vs_holeriths` 
            WHERE `id_vale_data` = '$id_vale_data' 
            ORDER BY `perc_atingida_sobre_cotas_vendas_diretas` DESC LIMIT 2 ";
    $campos_os_melhores                         = bancos::sql($sql);
    $id_funcionario_campeao_os_melhores         = $campos_os_melhores[0]['id_funcionario'];
    $id_funcionario_vice_campeao_os_melhores    = $campos_os_melhores[1]['id_funcionario'];
    
    //Disparo do Loop ...
    for($i = 0; $i < $linhas; $i++) {
        //Busca de Todos os Vale(s) do Funcion�rio do Loop na Data de Holerith e Desconto de Vale especificado ...
        $id_funcionario_loop = $campos[$i]['id_funcionario'];

        //Aqui mostra independente de ser PD ou PF ...
        $sql = "SELECT `valor_liquido_holerith`, `dias_horas_trabalhadas`, `outros_rend_prop`, 
                `faltas_dia_hr`, `atrasos_hr_min`, `dsr_hr_min`, `comissao_alba`, `comissao_tool`, 
                `comissao_grupo`, `dsr_alba`, `dsr_tool`, `dsr_grupo`, `hora_extra`, 
                `premio_produtividade_trabalho`, `premio_tdc`, `premio_industrializados`, 
                `perc_atingida_sobre_cotas_vendas_diretas`, `perc_atingida_sobre_cotas_supervisao`, 
                `subtotal_premios`, `data_sys_comissao`, `data_sys`, `observacao` 
                FROM `funcionarios_vs_holeriths` 
                WHERE `id_funcionario` = '$id_funcionario_loop' 
                AND `id_vale_data` = '$id_vale_data' LIMIT 1 ";
        $campos_funcionario_holerith = bancos::sql($sql);
        if(count($campos_funcionario_holerith) == 1) {
            $valor_liquido_holerith = $campos_funcionario_holerith[0]['valor_liquido_holerith'];
            $dias_horas_trabalhadas = $campos_funcionario_holerith[0]['dias_horas_trabalhadas'];
            $outros_rend_prop       = $campos_funcionario_holerith[0]['outros_rend_prop'] + $campos[$i]['salario_premio'];
            $faltas_dia_hr          = $campos_funcionario_holerith[0]['faltas_dia_hr'];
            $atrasos_hr_min         = $campos_funcionario_holerith[0]['atrasos_hr_min'];
            $dsr_hr_min             = $campos_funcionario_holerith[0]['dsr_hr_min'];
//Aqui eu trago a Comiss�o de acordo com a Empresa
            if($campos[$i]['id_empresa'] == 1) {//Se a Empresa = Albafer ...
                $comissao_pd    = $campos_funcionario_holerith[0]['comissao_alba'];
                $comissao_pf    = $campos_funcionario_holerith[0]['comissao_tool'] + $campos_funcionario_holerith[0]['comissao_grupo'];
                $dsr_pd         = $campos_funcionario_holerith[0]['dsr_alba'];
                $dsr_pf         = $campos_funcionario_holerith[0]['dsr_tool'] + $campos_funcionario_holerith[0]['dsr_grupo'];
            }else if($campos[$i]['id_empresa'] == 2) {//Se a Empresa = Tool Master ...
                $comissao_pd    = $campos_funcionario_holerith[0]['comissao_tool'];
                $comissao_pf    = $campos_funcionario_holerith[0]['comissao_alba'] + $campos_funcionario_holerith[0]['comissao_grupo'];
                $dsr_pd         = $campos_funcionario_holerith[0]['dsr_tool'];
                $dsr_pf         = $campos_funcionario_holerith[0]['dsr_alba'] + $campos_funcionario_holerith[0]['dsr_grupo'];
            }else if($campos[$i]['id_empresa'] == 4) {//Se a Empresa = Grupo ...
                $comissao_pd    = 0;//N�o � Registrado, ah !! n�o tem carteira assinada, sem direito ...
                $comissao_pf    = $campos_funcionario_holerith[0]['comissao_alba'] + $campos_funcionario_holerith[0]['comissao_tool'] + $campos_funcionario_holerith[0]['comissao_grupo'];
                $dsr_pd         = 0;//N�o � Registrado, ah !! n�o tem carteira assinada, sem direito ...
                $dsr_pf         = $campos_funcionario_holerith[0]['dsr_alba'] + $campos_funcionario_holerith[0]['dsr_tool'] + $campos_funcionario_holerith[0]['dsr_grupo'];
            }
            $hora_extra_fer_sab         = $campos_funcionario_holerith[0]['hora_extra'];
            $data_sys_comissao          = data::datetodata(substr($campos_funcionario_holerith[0]['data_sys_comissao'], 0, 10), '/').' '.substr($campos_funcionario_holerith[0]['data_sys_comissao'], 11, 8);
            $observacao_func_holerith   = $campos_funcionario_holerith[0]['observacao'];
        }else {//N�o encontrou nenhum Registro ...
            $comissao_pd    = 0;
            $comissao_pf    = 0;
        }
//S� na 1� vez que eu igualo a vari�vel de Empresa p/ n�o dar problema ...
        if($i == 0) $id_empresa_antigo = $campos[$i]['id_empresa'];
//Comparo se j� foi trocada 
        if($id_empresa_antigo != $campos[$i]['id_empresa']) {//Empresa diferente ...
//Aki significa que mudou para outra Empresa, e sendo assim solicito uma nova p�gina ...
            $id_empresa_antigo = $campos[$i]['id_empresa'];

            //Significa que j� foram impressos 2 registros daquela p�gina, e sendo assim solicito uma nova p�gina ...
            if($holeriths_impressos_por_pagina == 2) {
                $holeriths_impressos_por_pagina = 0;
                $pdf->AddPage();
            }else if($holeriths_impressos_por_pagina == 1) {
                //Aqui com esse comando eu mando fazer a impress�o do Segundo Vale no meio da Folha
                $pdf->SetY(167.5);
                //Espa�o entre um funcion�rio e outro ...
                $pdf->Cell($GLOBALS['ph']*90, 4.5, '-------------------------------------------------------------------------------------------------------------------------------------------------------------', 0, 1, 'L');
            }
        }else {//Ainda � a mesma Empresa
            if($opt_descontar == 'PF') {//PF ...
                //Significa que j� foram impressos 2 registros daquela p�gina, e sendo assim solicito uma nova p�gina ...
                if($holeriths_impressos_por_pagina == 2) {
                    $holeriths_impressos_por_pagina = 0;
                    $pdf->AddPage();
                }else if($holeriths_impressos_por_pagina == 1) {
                    //Aqui com esse comando eu mando fazer a impress�o do Segundo Vale no meio da Folha
                    $pdf->SetY(167.5);
                    //Espa�o entre um funcion�rio e outro ...
                    $pdf->Cell($GLOBALS['ph']*90, 4.5, '-------------------------------------------------------------------------------------------------------------------------------------------------------------', 0, 1, 'L');
                }
            }else {//PD ...
                //Significa que j� foram impressos 2 registros daquela p�gina, e sendo assim solicito uma nova p�gina ...
                if($holeriths_impressos_por_pagina == 2) {
                    $holeriths_impressos_por_pagina = 0;
                    $pdf->AddPage();
                }else if($holeriths_impressos_por_pagina == 1) {
                    //Aqui com esse comando eu mando fazer a impress�o do Segundo Vale no meio da Folha
                    $pdf->SetY(167.5);
                    //Espa�o entre um funcion�rio e outro ...
                    $pdf->Cell($GLOBALS['ph']*90, 4.5, '-------------------------------------------------------------------------------------------------------------------------------------------------------------', 0, 1, 'L');
                }
            }
        }
        
        if($opt_descontar == 'PD' || ($opt_descontar == 'PF' && ($campos[$i]['salario_pf'] + $campos[$i]['salario_premio'] + $outros_rend_prop > 0) || ($campos[$i]['salario_pd'] + $campos[$i]['salario_pf'] + $campos[$i]['salario_premio'] + $outros_rend_prop > 0 && $campos[$i]['id_empresa'] == 4) || ($comissao_pd + $comissao_pf > 0))) {
            //modelo
            //$pdf->Rect($GLOBALS['ph']*5.9, 8, $GLOBALS['ph']*89.9,13, 'D');
            //Dados do Funcion�rio ...
            $pdf->SetFont('Arial', 'B', 9);
            //SetFillColor(100, 100, 100);
            $pdf->SetLineWidth(0.5);
            //$pdf->Cell($GLOBALS['ph']*7, 4.5, 'NOME - ', 1, 0, 'L');
            //$pdf->SetFont('Arial', '', 9);
            $pdf->Cell($GLOBALS['ph']*45, 4.5, 'NOME - '.strtoupper($campos[$i]['nome']), 1, 0, 'L');
            //$pdf->SetFont('Arial', 'B', 9);
//N.�
            //$pdf->Cell($GLOBALS['ph']*3, 4.5, 'N.� ', 0, 0, 'L');
            //$pdf->SetFont('Arial', '', 9);
            $pdf->Cell($GLOBALS['ph']*20, 4.5, 'N.� '.$campos[$i]['codigo_barra'], 1, 0, 'L');
            //$pdf->SetFont('Arial', 'B', 9);
//Empresa
            //$pdf->Cell($GLOBALS['ph']*10, 4.5, 'EMPRESA - ', 0, 0, 'L');
            //$pdf->SetFont('Arial', '', 9);
            $pdf->Cell($GLOBALS['ph']*25, 4.5, 'EMPRESA - '.strtoupper($campos[$i]['nomefantasia']), 1, 1, 'L');
            $pdf->Ln(1);
            $pdf->SetLineWidth(0);

/***************************************************************************************************/
//Data de Holerith
            $pdf->SetFont('Arial', 'B', 9);
            //$pdf->Cell($GLOBALS['ph']*19, 4.5, 'DATA DE HOLERITH - ', 0, 0, 'L');
            //$pdf->SetFont('Arial', '', 9);
            $pdf->Cell($GLOBALS['ph']*45, 4.5, 'DATA DE HOLERITH - '.data::datetodata($_GET['cmb_data_holerith'], '/'), 1, 0, 'L');
//Tipo de Sal�rio
            //$pdf->SetFont('Arial', 'B', 9);
            //$pdf->Cell($GLOBALS['ph']*6, 4.5, 'TIPO - ', 0, 0, 'L');
            //$pdf->SetFont('Arial', '', 9);
            if($campos[$i]['tipo_salario'] == 1) {//Horista
                $pdf->Cell($GLOBALS['ph']*20, 4.5, 'TIPO - '.'HORISTA', 1, 0, 'L');
            }else {//Mensalista
                $pdf->Cell($GLOBALS['ph']*20, 4.5, 'TIPO - '.'MENSALISTA', 1, 0, 'L');
            }
//Descontos
            //$pdf->SetFont('Arial', 'B', 9);
            //$pdf->Cell($GLOBALS['ph']*11, 4.5, 'HOLERITH - ', 0, 0, 'L');
            //$pdf->SetFont('Arial', '', 9);
            $pdf->Cell($GLOBALS['ph']*25, 4.5, 'HOLERITH - '.$opt_descontar, 1, 1, 'L');
            $pdf->Ln(1);
/******************************************Desconto PF******************************************/
//Somente se for Desconto PF que eu exibo essa linha ...
/***********************************************************************************************/
            //$pdf->SetFillColor(255, 255, 255);//Cor Branca
            $pdf->SetFillColor(200, 200, 200);//Cor Cinza
            $pdf->Cell($GLOBALS['ph'] * 30, 4.5, 'Discrimina��o', 1, 0, 'C', 1);
            $pdf->Cell($GLOBALS['ph'] * 30, 4.5, 'Data Emiss�o / Outros', 1, 0, 'C', 1);
            $pdf->Cell($GLOBALS['ph'] * 15, 4.5, 'Cr�dito R$', 1, 0, 'C', 1);
            $pdf->Cell($GLOBALS['ph'] * 15, 4.5, 'D�bito R$', 1, 1, 'C', 1);
            $pdf->SetFont('Arial', '', 9);

            if(count($campos_funcionario_holerith) == 1) {
                if($opt_descontar == 'PF') {//Por Fora ...
                    //1) Qtde Dias / Hs Trabalhado(as)
                    if($campos[$i]['tipo_salario'] == 1) {//Horista
//Quando o Tipo do Sal�rio � Horista, ent�o existe essa separa��o dos dias com as Horas ...
                        $dias_trabalhados               = strtok($dias_horas_trabalhadas, '.');
                        $horas_trabalhadas              = substr(strchr($dias_horas_trabalhadas, '.'), 1, strlen($dias_horas_trabalhadas));
/*****************************************************************************************/
                        $texto = 'Qtde Hora(s) Trabalhada(s)';
                        //Quando a Empresa for Grupo, ent�o eu tenho que calcular o Sal�rio baseado no PD + PF
                        if($campos[$i]['id_empresa'] == 4) {//Grupo
                            $salario_bruto  = ($campos[$i]['salario_pd'] + $campos[$i]['salario_pf']);
                        }else {//Nas outras empresas, eu s� me baseio PF ...
                            $salario_bruto  = $campos[$i]['salario_pf'];
                        }

                        $qtde_dias_hrs_trab             = ($dias_trabalhados + ($horas_trabalhadas / 60));
                        $calc_qtde_dias_hrs_trab        = $qtde_dias_hrs_trab * $salario_bruto;
                        $calc_qtde_dias_hrs_trab_sal    = $calc_qtde_dias_hrs_trab;

                        $dias_horas_trabalhadas         = number_format($dias_horas_trabalhadas, 2, ':', '');
                    }else {//Mensalista
                        $texto = 'Qtde Dia(s) Trabalhado(s)';
                        //Quando a Empresa for Grupo, ent�o eu tenho que calcular o Sal�rio baseado no PD + PF
                        if($campos[$i]['id_empresa'] == 4) {//Grupo
                            $salario_bruto = $campos[$i]['salario_pd'] + $campos[$i]['salario_pf'];
                        }else {//Nas outras empresas, eu s� me baseio PF ...
                            $salario_bruto = $campos[$i]['salario_pf'];
                        }
                        $calc_qtde_dias_hrs_trab        = $salario_bruto;

                        //No c�lculo PF, eu pego o "Sal�rio do func" divido por 30 e multiplico pela qtde de Dias digitados ...
                        $calc_qtde_dias_hrs_trab_sal    = ($calc_qtde_dias_hrs_trab / 30) * $dias_horas_trabalhadas;
                        $dias_horas_trabalhadas         = number_format($dias_horas_trabalhadas, 0, '', '');
                    }
                    $pdf->Cell($GLOBALS['ph']*30, 4.5, $texto, 1, 0, 'L');
                    $pdf->Cell($GLOBALS['ph']*30, 4.5, $dias_horas_trabalhadas, 1, 0, 'C');
                    //Quando o Valor for = 0, ent�o eu n�o printo essa linha ...
                    if($calc_qtde_dias_hrs_trab_sal == 0) {
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R');
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                    }else if($calc_qtde_dias_hrs_trab_sal > 0) {//para Saber qual coluna Cr�dito ou D�bito
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($calc_qtde_dias_hrs_trab_sal, 2, ',', '.'), 1, 0, 'R');
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                    }else {
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R');
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($calc_qtde_dias_hrs_trab_sal, 2, ',', '.'), 1, 1, 'R');
                    }
                }else {//Por Dentro ...
                    if($campos[$i]['tipo_salario'] == 1) {//Horista
//Quando o Tipo do Sal�rio � Horista, ent�o existe essa separa��o dos dias com as Horas ...
                        $dias_trabalhados = strtok($dias_horas_trabalhadas, '.');
                        $horas_trabalhadas = substr(strchr($dias_horas_trabalhadas, '.'), 1, strlen($dias_horas_trabalhadas));
/*****************************************************************************************/
                        $texto = 'Qtde Hora(s) Trabalhada(s)';
                        //Quando a Empresa for Grupo, ent�o eu tenho que calcular o Sal�rio baseado no PD + PF
                        if($campos[$i]['id_empresa'] == 4) {//Grupo
                            $salario_bruto              = ($campos[$i]['salario_pd'] + $campos[$i]['salario_pf']);
                        }else {//Nas outras empresas, eu s� me baseio PF ...
                            $salario_bruto              = $campos[$i]['salario_pf'];
                        }
                        $calc_qtde_dias_hrs_trab        = ($dias_trabalhados + ($horas_trabalhadas / 60)) * $salario_bruto;
                        /*Igualei essa vari�vel igual a de cima, p/ que n�o d� erro no relat�rio, essa vari�vel n�o existia 
                        no PD, D�rcio*/
                        $calc_qtde_dias_hrs_trab_sal    = $calc_qtde_dias_hrs_trab;
                    }else {//Mensalista
                        $texto = 'Qtde Dia(s) Trabalhado(s)';
                        //Quando a Empresa for Grupo, ent�o eu tenho que calcular o Sal�rio baseado no PD + PF
                        if($campos[$i]['id_empresa'] == 4) {//Grupo
                            $salario_bruto              = ($campos[$i]['salario_pd'] + $campos[$i]['salario_pf']);
                        }else {//Nas outras empresas, eu s� me baseio PF
                            $salario_bruto              = $campos[$i]['salario_pf'];
                        }
                        $calc_qtde_dias_hrs_trab        = $salario_bruto;
                        //No c�lculo PD, eu pego o "Sal�rio do func" divido por 30 e multiplico pela qtde de Dias digitados ...
                        $calc_qtde_dias_hrs_trab_sal    = ($calc_qtde_dias_hrs_trab / 30) * $dias_horas_trabalhadas;
                    }
                    $pdf->Cell($GLOBALS['ph']*30, 4.5, $texto, 1, 0, 'L');
                    $pdf->Cell($GLOBALS['ph']*30, 4.5, $dias_horas_trabalhadas, 1, 0, 'C');
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R');
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                }
/***********************************************************************************************/
                //Se o Vale foi feito por Fora ent�o busco a comiss�o do que foi feito por Fora ...
                if($opt_descontar == 'PF') {
                    if($campos[$i]['id_cargo'] == 47) {//Se o Cargo = Representante Interno, ent�o ...
                        $comissao_rs = $comissao_pd + $comissao_pf;
                        $dsr_rs = $dsr_pd + $dsr_pf;
                    }else {//Se for outro cargo ...
                        $comissao_rs = $comissao_pf;
                        $dsr_rs = $dsr_pf;
                    }
                }else {//Se o Vale foi feito por Dentro ent�o busco a comiss�o do que foi feito por Dentro ...
                    if($campos[$i]['id_cargo'] == 47) {//Se o Cargo = Representante Interno, ent�o ...
                        $comissao_rs = 0;//A comiss�o passa a ser Zerada ...
                        $dsr_rs = 0;//O DSR passa a ser Zerado ...
                    }else {//Se for outro cargo ...
                        $comissao_rs = $comissao_pd;
                        $dsr_rs = $dsr_pd;
                    }
                }
//2) Qtde Falta(s) / Hs ou Dia(s)
                if($campos[$i]['tipo_salario'] == 1) {//Horista
                    $texto = 'Qtde Falta(s)';
                    $calc_qtde_faltas = - ($faltas_dia_hr * ($salario_bruto));
                    $sigla = ' Hs';
                }else {//Mensalista
                    $texto = 'Qtde Falta(s)';
                    $sigla = ' Dia(s)';
                    $calc_qtde_faltas = - ($calc_qtde_dias_hrs_trab / 30 * $faltas_dia_hr);
                }
                $pdf->Cell($GLOBALS['ph']*30, 4.5, $texto, 1, 0, 'L');
                $pdf->Cell($GLOBALS['ph']*30, 4.5, $faltas_dia_hr.$sigla, 1, 0, 'C');

                if($opt_descontar == 'PF') {//Por Fora ...
//Quando o Valor for = 0, ent�o eu n�o printo essa linha ...
                    if($calc_qtde_faltas == 0) {
                        $pdf->Cell($GLOBALS['ph'] * 15, 4.5, '', 1, 0, 'R');
                        $pdf->Cell($GLOBALS['ph'] * 15, 4.5, '', 1, 1, 'R');
                    }else if($calc_qtde_faltas > 0) {//para Saber qual coluna Cr�dito ou D�bito
                        $pdf->Cell($GLOBALS['ph'] * 15, 4.5, number_format($calc_qtde_faltas, 2, ',', '.'), 1, 0, 'R');
                        $pdf->Cell($GLOBALS['ph'] * 15, 4.5, '', 1, 1, 'R');
                    }else {
                        $pdf->Cell($GLOBALS['ph'] * 15, 4.5, '', 1, 0, 'R');
                        $pdf->Cell($GLOBALS['ph'] * 15, 4.5, number_format($calc_qtde_faltas, 2, ',', '.'), 1, 1, 'R');
                    }
                }else {//Por Dentro ...
                    $pdf->Cell($GLOBALS['ph'] * 15, 4.5, '', 1, 0, 'R');
                    $pdf->Cell($GLOBALS['ph'] * 15, 4.5, '', 1, 1, 'R');
                }
//3) Qtde Atrasos
                $hora_qtde_atrasos = strtok($atrasos_hr_min, '.');//Pega at� a 1� ocorr�ncia encontrada
                $minuto_qtde_atrasos = substr(strchr($atrasos_hr_min, '.'), 1);//Pega a partir 1� ocorr�ncia encontrada
                if($campos[$i]['tipo_salario'] == 1) {//Horista
                    $texto = 'Qtde Atrasos';
                    $calc_qtde_atrasos = - (($hora_qtde_atrasos + $minuto_qtde_atrasos / 60) * ($salario_bruto));
                }else {//Mensalista
                    $texto = 'Qtde Atrasos';
                    $calc_qtde_atrasos = - ($calc_qtde_dias_hrs_trab / 220 * ($hora_qtde_atrasos + $minuto_qtde_atrasos / 60));
                }
                $sigla = ' h:m';
                $pdf->Cell($GLOBALS['ph']*30, 4.5, $texto, 1, 0, 'L');
                $pdf->Cell($GLOBALS['ph']*30, 4.5, $atrasos_hr_min.$sigla, 1, 0, 'C');
                if($opt_descontar == 'PF') {//Por Fora ...
                    //Quando o Valor for = 0, ent�o eu n�o printo essa linha ...
                    if($calc_qtde_atrasos == 0) {
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R');
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                    }else if($calc_qtde_atrasos > 0) {//para Saber qual coluna Cr�dito ou D�bito
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($calc_qtde_atrasos, 2, ',', '.'), 1, 0, 'R');
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                    }else {
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R');
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($calc_qtde_atrasos, 2, ',', '.'), 1, 1, 'R');
                    }
                }else {
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R');
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                }
//4) DSR (sobre Faltas e Atrasos)
                if($campos[$i]['tipo_salario'] == 1) {//Horista
                    $hora_qtde_dsr = strtok($dsr_hr_min, '.');//Pega at� a 1� ocorr�ncia encontrada
                    $minuto_qtde_dsr = substr(strchr($dsr_hr_min, '.'), 1);//Pega a partir 1� ocorr�ncia encontrada
                    $calc_qtde_dsr = - (($hora_qtde_dsr + $minuto_qtde_dsr / 60) * ($salario_bruto));
                    $texto = 'DSR (sobre Faltas e Atrasos)';
                    $sigla =' h:m';
                }else {//Mensalista
                    $dsr_dias = number_format($dsr_hr_min, 0, '', '');
                    $calc_qtde_dsr = - ($calc_qtde_dias_hrs_trab / 30 * $dsr_dias);
                    $texto = 'DSR (sobre Faltas e Atrasos)';
                    $sigla =' Dias';
                }
                $pdf->Cell($GLOBALS['ph']*30, 4.5, $texto, 1, 0, 'L');
                $pdf->Cell($GLOBALS['ph']*30, 4.5,$dsr_hr_min.$sigla, 1, 0, 'C');
                if($opt_descontar == 'PF') {//Por Fora ...
                    //Quando o Valor for = 0, ent�o eu n�o printo essa linha ...
                    if($calc_qtde_dsr == 0) {
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R');
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                    }else if($calc_qtde_dsr > 0) {//para Saber qual coluna Cr�dito ou D�bito
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($calc_qtde_dsr, 2, ',', '.'), 1, 0, 'R');
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                    }else {
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R');
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($calc_qtde_dsr, 2, ',', '.'), 1, 1, 'R');
                    }
                }else {
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R');
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                }
//5) Hora Extra (Feriado ao S�bado) - A % da Hora Extra � de 30% em cima da hora normal ...
                if($opt_descontar == 'PF') {//Somente no Por Fora que aparece essa linha ...
                    $pdf->Cell($GLOBALS['ph']*30, 4.5, 'Hora Extra (Feriado ao S�bado)', 1, 0, 'L');
                    $pdf->Cell($GLOBALS['ph']*30, 4.5, number_format($hora_extra_fer_sab, 2, ':', ''), 1, 0, 'C');

                    $so_hora_extra_fer_sab = strtok($hora_extra_fer_sab, '.');//Pega at� a 1� ocorr�ncia encontrada
                    $so_min_extra_fer_sab = substr(strchr($hora_extra_fer_sab, '.'), 1);//Pega a partir 1� ocorr�ncia encontrada
                    $nova_hora_extra_fer_sab = ($so_hora_extra_fer_sab + $so_min_extra_fer_sab / 60);

                    //Se o funcion�rio trabalhou num feriado de s�bado, ent�o ele ganha 50% do valor de sua hora Extra ...
                    $aliquota_hora_extra = ($hora_extra_fer_sab > 0) ? 1.50 : 1.30;//Normal � 30% ...

                    //Quando a Empresa for Grupo, ent�o eu tenho que calcular o Sal�rio baseado no PD + PF
                    if($campos[$i]['id_empresa'] == 4) {//Grupo
                        $salario_bruto = ($campos[$i]['salario_pd'] + $campos[$i]['salario_pf']);
                    }else {
                        $salario_bruto = $campos[$i]['salario_pf'];
                    }

                    if($campos[$i]['tipo_salario'] == 1) {//Horista
                        $calc_credito = $nova_hora_extra_fer_sab * $salario_bruto * $aliquota_hora_extra;
                    }else {//Mensalista
                        $calc_credito = $nova_hora_extra_fer_sab * ($salario_bruto / 220) * $aliquota_hora_extra;
                    }
                    $calc_credito = round($calc_credito, 2);

                    $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($calc_credito, 2, ',', '.'), 1, 0, 'R');
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                }else {//No PD, eu somente exibo as horas lan�adas ...
                    $pdf->Cell($GLOBALS['ph']*30, 4.5, 'Hora Extra (Feriado ao S�bado)', 1, 0, 'L');
                    $pdf->Cell($GLOBALS['ph']*30, 4.5, number_format($hora_extra_fer_sab, 2, ':', ''), 1, 0, 'C');
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R');
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                }
//Aqui mostra independente de ser PD ou PF ...
//6) Comiss�o
                $comissao_rs-=$dsr_rs;//Aqui nessa parte eu s� preciso descontar o DSR ...
                $pdf->Cell($GLOBALS['ph']*30, 4.5, 'Comiss�o', 1, 0, 'L');
//Quando o Valor for = 0, ent�o eu n�o printo essa linha ...
                if($data_sys_comissao == '00/00/0000 00:00:00') {
                    $pdf->Cell($GLOBALS['ph']*30, 4.5, '', 1, 0, 'C');
                }else {
                    $pdf->Cell($GLOBALS['ph']*30, 4.5, $data_sys_comissao, 1, 0, 'C');
                }
//Quando o Valor for = 0, ent�o eu n�o printo essa linha ...
                if($comissao_rs == 0) {
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R');
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                }else if($comissao_rs > 0) {//para Saber qual coluna Cr�dito ou D�bito
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($comissao_rs, 2, ',', '.'), 1, 0, 'R');
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                }else {
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R');
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($comissao_rs, 2, ',', '.'), 1, 1, 'R');
                }
//7) DSR Comiss�o
                $pdf->Cell($GLOBALS['ph']*30, 4.5, 'DSR Comiss�o', 1, 0, 'L');
                $pdf->Cell($GLOBALS['ph']*30, 4.5,'', 1, 0, 'C');
//Quando o Valor for = 0, ent�o eu n�o printo essa linha ...
                if($dsr_rs == 0) {
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R');
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                }else if($dsr_rs > 0) {//para Saber qual coluna Cr�dito ou D�bito
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($dsr_rs, 2, ',', '.'), 1, 0, 'R');
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                }else {
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R');
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($dsr_rs, 2, ',', '.'), 1, 1, 'R');
                }

                $saldo_garantial_salarial = 0;//Zero essa vari�vel p/ n�o herdar valores do Loop Anterior ...
//8) Garantia Salarial
                if($opt_descontar == 'PF') {
                    //Estou movendo por causa que a Garantia Salarial utiliza esse $sub_total_sal_rec ...
                    $sub_total_sal_rec = $calc_qtde_dias_hrs_trab_sal + $calc_qtde_faltas + $calc_qtde_atrasos + $calc_qtde_dsr;
                    
                    if($campos[$i]['garantia_salarial'] != 0) {
                        $garantial_salarial = ($campos[$i]['garantia_salarial'] * $dias_horas_trabalhadas / 30) + $calc_qtde_faltas + $calc_qtde_atrasos;
                        
                        $pdf->Cell($GLOBALS['ph'] * 30, 4.5, 'Garantia Salarial = R$ '.number_format($garantial_salarial, 2, ',', '.'), 1, 0, 'L');
                        $pdf->Cell($GLOBALS['ph'] * 30, 4.5, '', 1, 0, 'R');
                       
                        $salario_sem_garantia = ($comissao_rs + $dsr_rs + $sub_total_sal_rec);
                        
                        $saldo_garantial_salarial = ($garantial_salarial - $salario_sem_garantia);
                        if($saldo_garantial_salarial < 0) $saldo_garantial_salarial = 0;

                        $pdf->Cell($GLOBALS['ph'] * 15, 4.5, number_format($saldo_garantial_salarial, 2, ',', '.'), 1, 0, 'R');
                        $pdf->Cell($GLOBALS['ph'] * 15, 4.5, '', 1, 1, 'R');
                    }
                }
/******************************************Desconto PD******************************************/
//Somente se for Desconto PD que eu exibo essa linha, ou se o Funcion�rio for da Empresa Grupo, pois se � Grupo n�o tem Registro ...
/***********************************************************************************************/
                if($opt_descontar == 'PD' || $campos[$i]['id_empresa'] == 4) {
//9) Pens�o Aliment�cia
                    $pensao_alimenticia = $campos[$i]['pensao_alimenticia'];
                    $valor_pensao_alimenticia = $campos[$i]['valor_pensao_alimenticia'] * (-1);

                    $pdf->Cell($GLOBALS['ph']*30, 4.5, 'Pens�o Aliment�cia', 1, 0, 'L');
                    if($pensao_alimenticia == 0) {
                            $pdf->Cell($GLOBALS['ph']*30, 4.5, '', 1, 0, 'C');
                    }else {
                            $pdf->Cell($GLOBALS['ph']*30, 4.5, $pensao_alimenticia, 1, 0, 'C');
                    }
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R');
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($valor_pensao_alimenticia, 2, ',', '.'), 1, 1, 'R');

/*10) Mensalidade MetalCred - o sistema posterior a essa Data exibe essa linha fixo, porque n�o existiam 
Tipos de Vale como sendo MetalCred. Esses Vales MetalCred foram criados no ERP a partir do 
dia 23/05/2013 ...*/
                    if($_GET['cmb_data_holerith'] < '2013-05-23') {
                        $valor_metalcred = $campos[$i]['valor_metalcred'] * (-1);
                        if($valor_metalcred < 0) {//S� exibe essa linha se existir valor de Credito Consignado ...
                            $pdf->Cell($GLOBALS['ph']*30, 4.5, 'Mensalidade MetalCred', 1, 0, 'L');
                            $pdf->Cell($GLOBALS['ph']*30, 4.5, '', 1, 0, 'C');
                            $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R');
                            $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($valor_metalcred, 2, ',', '.'), 1, 1, 'R');
                        }
                    }
                }
/******************************************Desconto PF******************************************/
//Somente se for Desconto PF que eu exibo essa linha ...
/***********************************************************************************************/
                if($opt_descontar == 'PF') {
//11) Proporc 13� + FER
                    //Move mais para cima essa linha no dia 30/06/2015 devido os c�lculos de Garantia Salarial ...
                    //$sub_total_sal_rec = $calc_qtde_dias_hrs_trab_sal + $calc_qtde_faltas + $calc_qtde_atrasos + $calc_qtde_dsr;
/*S� p/ os funcs Wilson Baldez, Wellington Baldez e Lucas Faria Marques q n�o existe esse c�lculo ]= 0, 
p/ os outros funcion�rios normal ...*/
                    if($id_funcionario_loop == 100 || $id_funcionario_loop == 145 || $id_funcionario_loop == 167) {
                        $proporc_13_fer = 0;
                    }else {//Outros Funcion�rios ...
                        $proporc_13_fer = ($comissao_rs + $dsr_rs + $sub_total_sal_rec + $saldo_garantial_salarial) * 0.194;//Aqui � uma propor��o do 13�
                    }
                    $pdf->Cell($GLOBALS['ph']*30, 4.5, 'Proporc 13� + FER', 1, 0, 'L');
                    $pdf->Cell($GLOBALS['ph']*30, 4.5, '', 1, 0, 'C');
//Quando o Valor for = 0, ent�o eu n�o printo essa linha ...
                    if($proporc_13_fer == 0) {
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R');
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                    }else if($proporc_13_fer > 0) {//para Saber qual coluna Cr�dito ou D�bito
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($proporc_13_fer, 2, ',', '.'), 1, 0, 'R');
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                    }else {
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R');
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($proporc_13_fer, 2, ',', '.'), 1, 1, 'R');
                    }
//12) Pr�mio Os Melhores
                    if($id_funcionario_campeao_os_melhores == $id_funcionario_loop) {
                        $pdf->Cell($GLOBALS['ph']*30, 4.5, 'Pr�mio "Os Melhores"', 1, 0, 'L');
                        $pdf->Cell($GLOBALS['ph']*30, 4.5, 'Campe�o', 1, 0, 'C');
                        
                        //Essa vari�vel sempre ser� o Pr�mio de Vice-Campe�o + 50% ...
                        $premio_campeao_os_melhores = genericas::variavel(75) * 1.5;
                        
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($premio_campeao_os_melhores, 2, ',', '.').' (DEMO)', 1, 0, 'R');
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                        
                        $outros_rend_prop+= $premio_campeao_os_melhores;
                    }
                    if($id_funcionario_vice_campeao_os_melhores == $id_funcionario_loop) {
                        $pdf->Cell($GLOBALS['ph']*30, 4.5, 'Pr�mio "Os Melhores"', 1, 0, 'L');
                        $pdf->Cell($GLOBALS['ph']*30, 4.5, 'Vice-Campe�o', 1, 0, 'C');
                        
                        $premio_vice_campeao_os_melhores = genericas::variavel(75);
                        
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($premio_vice_campeao_os_melhores, 2, ',', '.').' (DEMO)', 1, 0, 'R');
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                        
                        $outros_rend_prop+= $premio_vice_campeao_os_melhores;
                    }
//13) SubTotal Pr�mio de Vendas s/ Proporcionalidade
                    if($campos_funcionario_holerith[0]['subtotal_premios'] > 0) {
                        $pdf->Cell($GLOBALS['ph']*30, 4.5, 'SubTotal Pr�mio de Vendas s/ Proporc.', 1, 0, 'L');
                        $pdf->Cell($GLOBALS['ph']*30, 4.5, '', 1, 0, 'C');
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($campos_funcionario_holerith[0]['subtotal_premios'], 2, ',', '.').' (DEMO)', 1, 0, 'R');
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');

                        $outros_rend_prop+= $campos_funcionario_holerith[0]['subtotal_premios'];
                    }
//14) Outros Rendimentos Proporcionais
                    $pdf->Cell($GLOBALS['ph']*30, 4.5, 'Outros Rend. + Pr�mio s/ Proporc.', 1, 0, 'L');
                    $pdf->Cell($GLOBALS['ph']*30, 4.5, $dias_horas_trabalhadas, 1, 0, 'C');

                    if($campos[$i]['tipo_salario'] == 1) {//Horista
                        $premio_holerith = $qtde_dias_hrs_trab * $campos[$i]['salario_premio'];
                    }else {//Mensalita ...
                        $premio_holerith = $campos[$i]['salario_premio'] / 30 * $qtde_dias_hrs_trab;
                    }
                    $outros_rend_prop+= $premio_holerith;
                    
//Quando o Valor for = 0, ent�o eu n�o printo essa linha ...
                    if($outros_rend_prop == 0) {
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R');
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                    }else if($outros_rend_prop > 0) {//para Saber qual coluna Cr�dito ou D�bito
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($outros_rend_prop, 2, ',', '.'), 1, 0, 'R');
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                    }else {
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R');
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($outros_rend_prop, 2, ',', '.'), 1, 1, 'R');
                    }
                }
/******************************************Desconto PF******************************************/
//Somente se for Desconto PF que eu exibo essa linha ...
/***********************************************************************************************/
                if($opt_descontar == 'PF') {
                    $pdf->SetFont('Arial', 'B', 9);
                    $sub_total_receber_rs = $sub_total_sal_rec + $calc_credito + $proporc_13_fer + $outros_rend_prop + $comissao_rs + $dsr_rs + $saldo_garantial_salarial;
                    $pdf->Cell($GLOBALS['ph']*60, 4.5, 'Sub-Total � Receber', 1, 0, 'L', 1);
//Quando o Valor for = 0, ent�o eu n�o printo essa linha ...
                    if($sub_total_receber_rs == 0) {
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R', 1);
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R', 1);
                    }else if($sub_total_receber_rs > 0) {//para Saber qual coluna Cr�dito ou D�bito
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($sub_total_receber_rs, 2, ',', '.'), 1, 0, 'R', 1);
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R',1);
                    }else {
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R',1);
                        $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($sub_total_receber_rs, 2, ',', '.'), 1, 1, 'R', 1);
                    }
                    $pdf->SetFont('Arial', '', 9);
                }
            }
/***************************************************************************************************/
/***********************Daqui em Diante eu Exibo todos os Vale(s) do Funcion�rio na Data de Holerith 
especificada pelo usu�rio***************************************************************************/
            $pdf->SetFont('Arial', '', 9);
            
            //S� exibo os Vales ativos ...
            $sql = "SELECT `tipo_vale`, `financeira`, `parcelamento`, `data_emissao`, `valor`, `observacao` 
                    FROM `vales_dps` 
                    WHERE `id_funcionario` = '$id_funcionario_loop' 
                    AND `data_debito` = '$_GET[cmb_data_holerith]' 
                    AND `descontar_pd_pf` = '$opt_descontar' 
                    AND `descontado` = 'N' 
                    AND `ativo` = '1' ";
            $campos_vales_dps = bancos::sql($sql);
            $linhas_vales_dps = count($campos_vales_dps);
            $total_descontos = 0;//Controle do Total de Vale(s) do Funcion�rio Independente do Tipo de Vale ...
            //Disparo do Segundo Loop ...
            for($j = 0; $j < $linhas_vales_dps; $j++) {
                //Se o Tipo do Vale = Credito Consignado, ent�o eu concateno com a Financeira e N.� de Parcelas ...
                $parcelamento = ($campos_vales_dps[$j]['tipo_vale'] == 14) ? ' '.$campos_vales_dps[$j]['financeira'].' ('.$campos_vales_dps[$j]['parcelamento'].') ': '';
                //Se o Tipo do Vale = Avulso, ent�o eu concateno com a observa��o ao lado
                $observacao = ($campos_vales_dps[$j]['tipo_vale'] == 2) ? ' - '.$campos_vales_dps[$j]['observacao'] : '';

                $pdf->Cell($GLOBALS['ph']*30, 4.5, $vetor_tipos_vale[$campos_vales_dps[$j]['tipo_vale']].$parcelamento.$observacao, 1, 0, 'L');
                $pdf->Cell($GLOBALS['ph']*30, 4.5, data::datetodata($campos_vales_dps[$j]['data_emissao'], '/'), 1, 0, 'C');
                $vales = $campos_vales_dps[$j]['valor']*(-1);

//Quando o Valor for = 0, ent�o eu n�o printo essa linha ...
                if($vales == 0) {
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R');
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                }else if($vales > 0) {//para Saber qual coluna Cr�dito ou D�bito
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($vales, 2, ',', '.'), 1, 0, 'R');
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                }else {
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R');
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($vales, 2, ',', '.'), 1, 1, 'R');
                }
                $total_descontos+= $vales;
            }
            /*Do total de Descontos eu tamb�m abato o Valor Pens�o Aliment�cia e Valor Credito 
            Consignado caso exista no Cadastro do Func ...*/
            $total_descontos+= $valor_pensao_alimenticia + $valor_metalcred;
/******************************************Desconto PD******************************************/
//Somente se for Desconto PD que eu exibo essa linha ...
/***********************************************************************************************/
            if($opt_descontar == 'PD') {
                $pdf->SetFont('Arial', 'B', 9);
//11) Observa��o
                $pdf->MultiCell($GLOBALS['ph']*90, 5, 'OBSERVA��O: '.$observacao_func_holerith, 1, 'L');
            }
//Total de Vale do Funcion�rio ...
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell($GLOBALS['ph']*60, 4.5, 'TOTAL DESCONTO ', 1, 0, 'L', 1);
//Quando o Valor for = 0, ent�o eu n�o printo essa linha ...
            if($total_descontos == 0) {
                $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R', 1);
                $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R', 1);
            }else if($total_descontos > 0) {//para Saber qual coluna Cr�dito ou D�bito
                $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($total_descontos, 2, ',', '.'), 1, 0, 'R', 1);
                $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R', 1);
            }else {
                $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R', 1);
                $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($total_descontos, 2, ',', '.'), 1, 1, 'R', 1);
            }
/******************************************Desconto PF******************************************/
//Somente se for Desconto PF que eu exibo essa linha ...
/***********************************************************************************************/
            if($opt_descontar == 'PF') {//C�lculo do Total � Receber PF em R$
                $total_receber_pf = $sub_total_receber_rs + $total_descontos;
                $pdf->Cell($GLOBALS['ph']*60, 4.5, 'Total � Receber PF', 1, 0, 'L',1);
//Quando o Valor for = 0, ent�o eu n�o printo essa linha ...
                if($total_receber_pf == 0) {
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R');
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                }else if($total_receber_pf > 0) {//para Saber qual coluna Cr�dito ou D�bito
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($total_receber_pf, 2, ',', '.'), 1, 0, 'R',1);
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R',1);
                }else {
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R',1);
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($total_receber_pf, 2, ',', '.'), 1, 1, 'R',1);
                }
            }
/******************************************Desconto PF******************************************/
//Somente se for Desconto PF que eu exibo essa linha ...
/***********************************************************************************************/
            if($opt_descontar == 'PF') {
                $total_receber = $sub_total_receber_rs + $valor_liquido_holerith + $total_descontos;
//Para facilitar na hora de se pagar os funcionarios, faco um arredondamento na variavel $total_receber ...

                if($total_receber - intval($total_receber) > 0.75) {//Se no $total_receber a parte de Centavos for maior do que 75 ...
                    $total_receber = intval($total_receber) + 1;//Arredonda 1 real a mais na parte inteira no $total_receber ...
                }else if($total_receber - intval($total_receber) > 0.50) {//Se no $total_receber a parte de Centavos for maior do que 50 ...
                    $total_receber = intval($total_receber) + 0.75;//Somo 75 centavos no $total_receber ...
                }else if($total_receber - intval($total_receber) > 0.25) {//Se no $total_receber a parte de Centavos for maior do que 25 ...
                    $total_receber = intval($total_receber) + 0.50;//Somo 50 centavos no $total_receber ...
                }else if(($total_receber - intval($total_receber) <= 0.25) && ($total_receber - intval($total_receber) > 0.00)) {
                    $total_receber = intval($total_receber) + 0.25;//Somo 25 centavos no $total_receber ...
                }
//Tenho esse lembrete p/ que fique bem claro p/ o usu�rio ...
                $valor_negativo = ($total_receber < 0) ? '(Valor Negativo)   ' : '';
//C�lculo do Total � Receber em R$ ...
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell($GLOBALS['ph']*60, 4.5, 'Total � Receber (Arredondado a cada R$ 0,25) ', 1, 0, 'L',1);
//Quando o Valor for = 0, ent�o eu n�o printo essa linha ...
                if($total_receber == 0) {
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R');
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R');
                }else if($total_receber > 0) {//para Saber qual coluna Cr�dito ou D�bito
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, number_format($total_receber, 2, ',', '.'), 1, 0, 'R', 1);
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 1, 'R', 1);
                }else {
                    /*Somente no vale "PF" que o ERP far� esse controle p/ enviar e-mail p/ a Dona Sandra "Diretora" 
                    e a pessoa do RH lembrando que precisa ser gerado um Vale Negativo ...*/
                    if($opt_descontar == 'PF') {
                        $funcionarios_com_vales_negativos.= '<br/>'.$campos[$i]['nome'];
                    }
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, '', 1, 0, 'R', 1);
                    $pdf->Cell($GLOBALS['ph']*15, 4.5, $valor_negativo.number_format($total_receber, 2, ',', '.'), 1, 1, 'R', 1);
                }
//Aqui eu guardo o Valor Total � Receber na Tabela de Funcion�rios vs Holeriths (Cr�ditos) ...
                $data_sys = date('Y-m-d H:i:s');
//Primeiro eu verifico se j� existe esse id_funcionario na Tabela ...
                $sql = "SELECT `id_funcionario_vs_holerith` 
                        FROM `funcionarios_vs_holeriths` 
                        WHERE `id_funcionario` = '$id_funcionario_loop' 
                        AND `id_vale_data` = '$id_vale_data' ";
                $campos_holerith = bancos::sql($sql);
                if(count($campos_holerith) == 0) {//Ainda n�o existe, ent�o eu gravo na Base de Dados ...
                    $sql = "INSERT INTO `funcionarios_vs_holeriths` (`id_funcionario_vs_holerith`, `id_funcionario`, `id_vale_data`, `valor_total_receber`, `data_sys`) VALUES (NULL, '$id_funcionario_loop', '$id_vale_data', '$total_receber', '$data_sys') ";
                    bancos::sql($sql);
                }else {//J� existe, sendo assim eu s� altero na Base de Dados ...
                    $sql = "UPDATE `funcionarios_vs_holeriths` SET `valor_total_receber` = '$total_receber', `data_sys` = '$data_sys' WHERE `id_funcionario_vs_holerith` = '".$campos_holerith[0]['id_funcionario_vs_holerith']."' LIMIT 1 ";
                    bancos::sql($sql);
                }
//Exibi��o dos Dados Banc�rios ...
                $pdf->SetFont('Arial', '', 9);
                if(empty($cadastro_banco[$campos[$i]['cod_banco']][0])) {//Certifico que ele n�o foi deletado, Luis ..
                    $banco = $cadastro_banco[$campos[$i]['cod_banco']][1];
                }else {
                    $banco = $cadastro_banco[$campos[$i]['cod_banco']][0];
                }
                $pdf->Cell($GLOBALS['ph']*90, 4.5, 'Banco - '.$banco.' / Ag�ncia - '.$campos[$i]['agencia'].' / Conta Corrente - '.$campos[$i]['conta_corrente'], 1, 1, 'L',1);
            }
/******************************************Desconto PF******************************************/
//Somente se for Desconto PF que eu exibo essa linha ...
/***********************************************************************************************/
            if($opt_descontar == 'PF') {//C�lculo do Total � Receber PF em R$
                $pdf->Ln(9);
                $pdf->Cell($GLOBALS['ph']*45, 4.5, '', '', 0, 'C');
                $pdf->Cell($GLOBALS['ph']*45, 4.5, strtoupper($campos[$i]['nome']), 'T', 1, 'C');
            }
/***************************************************************************************************/
            $holeriths_impressos_por_pagina++;
        }
    }
}

/******************************************************************************/
/************E-mail lembrando que se tem que gerar Vales Negativos*************/
/******************************************************************************/
if(!empty($funcionarios_com_vales_negativos) && $_SESSION['id_funcionario'] != 98) {//Somente o Funcion�rio "98 D�rcio" que nunca gerar� esse Tipo de E-mail ...
    $mensagem_email = 'Saldo negativo Holerith com vencimento em '.data::datetodata($_GET['cmb_data_holerith'], '/').'.<br/><br/>';
    $mensagem_email.= 'Segue abaixo a rela��o do(s) Funcion�rio(s) que possuem Vale Negativo: <br/>';
    $mensagem_email.= $funcionarios_com_vales_negativos.'<br/><br/>';
    $mensagem_email.= 'Favor gerar os vales para os funcion�rios na fun��o => ';
    $mensagem_email.= 'Menu Depto Pessoal / Folha de Pagto / Holerith / Gerenciar Vales / Bot�o Incluir Vale / Op��o ';
    comunicacao::email('ERP - GRUPO ALBAFER', $gerar_vales_negativos, '', 'Gerar Vale(s) Negativo(s)', $mensagem_email);
}
/******************************************************************************/

chdir('../../../../../../pdf');
$file = '../../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<html><body></body><Script Language='JavaScript'>document.location='$file';</Script></html>";//JavaScript redirection
?>