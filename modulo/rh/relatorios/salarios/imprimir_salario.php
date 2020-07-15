<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');

//Essas variáveis serão utilizadas mais abaixo ...

//Busco a Qtde de Funcionarios existentes na Empresa tirando os Motoristas que estejam em "Férias ou Ativo" ...
$sql = "SELECT COUNT(f.`id_funcionario`) AS qtde_funcionarios 
        FROM `funcionarios` f 
        INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` AND c.`id_cargo` <> '89' 
        WHERE f.`status` < '2' 
        AND f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) ORDER BY f.`nome` ";
$campos_funcionario         = bancos::sql($sql);
$qtde_funcionarios          = $campos_funcionario[0]['qtde_funcionarios'];
$qtde_dias_medios_por_mes   = 21;
$valor_base_calculo_medico  = genericas::variavel(27);
$valor_litro_combustível    = genericas::variavel(29);
$valor_vale_refeicao        = genericas::variavel(36);
$valor_assinatura_intragrupo= genericas::variavel(49);
$valor_minuto_celular       = genericas::variavel(50);
$valor_alimentacao          = genericas::variavel(51);
$treinamento_qualif_albafer = genericas::variavel(52);
$treinamento_qualif_tool    = genericas::variavel(53);
?>
<html>
<head>
<title>.:: Relatório de Salário(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<form name='form' method='post'>
<table width='95%' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr class='linhacabecalho' align='center'>
        <td colspan='25'>
            <?
                if($cmb_empresa == 1) {
                    $empresa = ' - ALBAFER';
                }else if($cmb_empresa == 2) {
                    $empresa = ' - TOOL MASTER';
                }else if($cmb_empresa == 4) {
                    $empresa = ' - GRUPO';
                }else if(strtoupper($cmb_empresa) == 'T') {
                    $empresa = ' - TODAS EMPRESAS';
                }
            ?>
            Relatório de Salário(s)
            <font color='yellow'>
                <?=$empresa;?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='25'>
            Empresa: 
            <select name='cmb_empresa' title='Selecione a Empresa' class='combo'>
                <option value='t' style='color:red'>TODAS EMPRESAS</option>
                <? if($cmb_empresa == 1) {$selected = 'selected';}else {$selected='';}?>
                <option value='1' <?=$selected;?>>ALBAFER</option>
                <? if($cmb_empresa == 2) {$selected = 'selected';}else {$selected='';}?>
                <option value='2' <?=$selected;?>>TOOL MASTER</option>
                <? if($cmb_empresa == 4) {$selected = 'selected';}else {$selected='';}?>
                <option value='4' <?=$selected;?>>GRUPO</option>
            </select>
            &nbsp;-&nbsp;Departamento: 
            <select name='cmb_departamento' title='Selecione o Departamento' class='combo'>
            <?
                $sql = "SELECT id_departamento, departamento 
                        FROM `departamentos` 
                        WHERE `ativo` = '1' ORDER BY departamento ";
                echo combos::combo($sql, $cmb_departamento);
            ?>
            </select>
            &nbsp;
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
<?
//Significa que o usuário já selecionou alguma empresa no Relatório ...
if(!empty($cmb_empresa)) {
    if(empty($cmb_departamento)) $cmb_departamento = '%';
    if(strtoupper($cmb_empresa) == 'T') {//Selecionei para listar os Funcionários de Todas as Empresas
        $empresas[] = 1;//alba
        $empresas[] = 2;//tool
        $empresas[] = 4;//grupo
    }else {
        $empresas[] = $cmb_empresa;//empresa selecionada pela combo
    }
//Listo as Empresas de acordo com o que foi selecionado pelo usuário no Sistema ...
    $linhas_empresas = count($empresas);
//Disparo de Loop das Empresas
    for($h = 0; $h < $linhas_empresas; $h++) {
        $total_salarial_empresa = 0;
        $total_salarial_pd_pf   = 0;
//Listagem de Funcionários independente da Empresa em "Férias ou Ativo" ...

/*Só não exibo os funcionários Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes não são funcionários, simplesmente só possuem cadastrado 
no Sistema p/ poder acessar algumas telas ...*/
        $sql = "SELECT c.cargo, d.departamento, f.id_funcionario, f.nome, DATE_FORMAT(f.data_admissao, '%d/%m/%Y') AS data_admissao, 
                f.`tipo_salario`, f.`salario_pd`, f.`salario_pf`, f.`salario_premio`, f.`garantia_salarial`, 
                f.`comissao_ultimos3meses_pd`, f.`comissao_ultimos3meses_pf`, f.`dependentes_conv_medico`, 
                f.`qtde_litros_combustivel`, f.`debitar_celular` 
                FROM `funcionarios` f 
                INNER JOIN `cargos` c ON c.id_cargo = f.id_cargo AND c.id_cargo <> '89' 
                INNER JOIN `departamentos` d ON d.id_departamento = f.id_departamento AND d.`id_departamento` LIKE '$cmb_departamento' 
                WHERE f.`id_empresa` = ".$empresas[$h]." 
                AND f.`status` < '2' 
                AND f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) ORDER BY nome ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas > 0) {//Se a consulta retornou pelo menos 1 registro apresento o Cabeçalho da Empresa ...
?>
    <tr class='linhacabecalho'>
        <td colspan='25'>
            <font color='yellow'>
                Empresa: 
            </font>
            <?=genericas::nome_empresa($empresas[$h]);?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>Funcionário</td>
        <td rowspan='2'>Cargo</td>
        <td rowspan='2'>Data de<br/> Admissão</td>
        <td rowspan='2'>Tipo</td>
        <td colspan='6'>Salário R$</td>
        <td colspan='15'></td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>PD</td>
        <td>PF</td>
        <td>Com. PD<br/> + DSR</td>
        <td>Com. PF<br/> + DSR</td>
        <td>Prêmio</td>
        <td>
            <font title='Total Mensal PD + PF' style='cursor:help'>
                Tot. Mes <br/>PD + PF
            </font>
        </td>
        <td>
            <font title='Convênio Médico c/ Dependente' style='cursor:help'>
                Conv. Med<br/> c/ Dep
            </font>
        </td>
        <td>
            <font title='Combustível' style='cursor:help'>
                Comb
            </font>
        </td>
        <td>
            <font title='Parte Empresa' style='cursor:help'>
                Celular
            </font>
        </td>
        <td>
            <font title='Parte Empresa' style='cursor:help'>
                VT
            </font>
        </td>
        <td>VR</td>
        <td>VA</td>
        <td>
            <font title='Parte Empresa' style='cursor:help'>
                INSS <br/>(12%)
            </font>
        </td>
        <td>
            FGTS <br/>(8%)
        </td>
        <td>
            Custo Demissional<img src = '../../../../imagem/bloco_negro.gif' title='Multa 50% FGTS + Aviso Indenizado' style='cursor:help' width='5' height='5' border='0'>
        </td>
        <td>
            <font title='R$ 550,00 anual' style='cursor:help'>
                PLR
            </font>
        </td>
        <td>
            <font title='Treinamento e Requalificacão Profissional' style='cursor:help'>
                Contrib.<br/> Dissídio
            </font>
        </td>
        <td>
            13º + Férias<br/>
            c/ INSS + FGTS
        </td>
        <td>
            <font title='R$ 600,00 por ano' style='cursor:help'>
                Outros
            </font>
        </td>
        <td>
            Tot. Mes.<br/> Geral
        </td>
        <td>
            % Ac.<br/>
            s/ Sal. Total
        </td>
    </tr>
<?
//Listando os Funcionário(s) ...
            for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='right'>
        <td align='left'>
            <!--Coloquei esse nome de $id_funcionario_loop, p/ não dar conflito com a variável 
            "id_funcionário" da sessão-->
            <a href='../../funcionario/alterar_dados_profissionais.php?id_funcionario_loop=<?=$campos[$i]['id_funcionario'];?>&pop_up=1' class='html5lightbox'>
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                    <?=$campos[$i]['nome'];?>
                </font>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['cargo'];?>
        </td>
        <td align='center'>
        <?
            echo $campos[$i]['data_admissao'];

            $vetor_data         = data::diferenca_data(data::datatodate($campos[$i]['data_admissao'], '-'), date('Y-m-d'));
            $qtde_anos_registro = $vetor_data[0] / 365;
            
            echo '<br/><font color="red"><b>'.number_format($qtde_anos_registro, 1, ',', '.').' ano(s)</b></font>';
        ?>
        </td>
        <td align='center'>
        <?
            if($campos[$i]['tipo_salario'] == 1) {//Salário Horista ...
                echo '<font color="darkblue" title="Horista" style="cursor:help"><b>Hs</b></font>';
            }else {//Salário Mensalista ...
                echo '<font title="Mensalista" style="cursor:help"><b>M</b></font>';
            }
        ?>
        </td>
        <td>
            <?=number_format($campos[$i]['salario_pd'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['salario_pf'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['comissao_ultimos3meses_pd'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['comissao_ultimos3meses_pf'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['salario_premio'], 2, ',', '.');?>
        </td>
        <td>
        <?
            $total_mensal_pd            = $campos[$i]['salario_pd'] + $campos[$i]['comissao_ultimos3meses_pd'];
            $total_mensal_pf_sem_premio = $campos[$i]['salario_pf'] + $campos[$i]['comissao_ultimos3meses_pf'];
            $total_mensal_pf            = $campos[$i]['salario_pf'] + $campos[$i]['comissao_ultimos3meses_pf'] + $campos[$i]['salario_premio'];
            
            //Se o Salário do funcionário for do Tipo Horista multiplico por 220 p/ transformar em mensal ...
            if($campos[$i]['tipo_salario'] == 1) $total_mensal_pd*= 220;
            if($campos[$i]['tipo_salario'] == 1) $total_mensal_pf*= 220;
            $total_mensal_pd_pf         = $total_mensal_pd + $total_mensal_pf;
            
            if($total_mensal_pd_pf < $campos[$i]['garantia_salarial']) $total_mensal_pd_pf = $campos[$i]['garantia_salarial'];
            
            echo number_format($total_mensal_pd_pf, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            $convenio_medico = (1 + $campos[$i]['dependentes_conv_medico'] / 2) * $valor_base_calculo_medico;
            echo number_format($convenio_medico, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            $combustivel = $campos[$i]['qtde_litros_combustivel'] * $valor_litro_combustível;
            echo number_format($combustivel, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            $celular = 0;//Zero p/ não acumular valores do Loop anterior ...
            if($campos[$i]['debitar_celular'] == 'S') {
                //Se o funcionário for de Vendas, este tem direito há 150 minutos de celular que a empresa paga ...
                if(strtoupper($campos[$i]['departamento']) == 'VENDAS') {
                    $celular = $valor_assinatura_intragrupo + (150 * $valor_minuto_celular);
                    echo number_format($celular, 2, ',', '.');
                }else {
                    $celular = $valor_assinatura_intragrupo;
                    echo number_format($celular, 2, ',', '.');
                }
            }
        ?>
        </td>
        <td>
        <?
            //Zero essas variaveis p/ não acumular valores do Loop anterior ...
            $valor_vt_total_mes = 0;
            $valor_vt_empresa   = 0;
            //Aqui eu busco o Quanto que a Empresa paga de Vale Transporte ao Funcionário ...
            $sql = "SELECT vt.tipo_vt, vt.valor_unitario, fvt.* 
                    FROM `funcionarios_vs_vales_transportes` fvt 
                    INNER JOIN `vales_transportes` vt ON vt.id_vale_transporte = fvt.id_vale_transporte 
                    WHERE fvt.`id_funcionario` = '".$campos[$i]['id_funcionario']."' ";
            $campos_vt_por_mes  = bancos::sql($sql);
            $linhas_vt_por_mes  = count($campos_vt_por_mes);
            if($linhas_vt_por_mes > 0) {
                for($j = 0; $j < $linhas_vt_por_mes; $j++) $valor_vt_total_mes+= $campos_vt_por_mes[$j]['valor_unitario'] * $campos_vt_por_mes[$j]['qtde_vale'];
                $valor_vt_total_mes*= $qtde_dias_medios_por_mes;
                //Eu jogo 6%, pq o funcionário pode pagar até 6% de VT em cima do seu salário ...
                $valor_vt_mes_func = 0.06 * ($total_mensal_pd_pf - $campos[$i]['comissao_ultimos3meses_pd'] - $campos[$i]['comissao_ultimos3meses_pf']);
                if($valor_vt_mes_func >= $valor_vt_total_mes) {
                    $valor_vt_empresa = 0;//Ou seja o funcionário ganha bem e paga todo o seu Vale Transporte ...
                }else {
                    $valor_vt_empresa = $valor_vt_total_mes - $valor_vt_mes_func;
                }
                echo number_format($valor_vt_empresa, 2, ',', '.');
            }
        ?>
        </td>
        <td>
        <?
            $valor_vr = $valor_vale_refeicao * $qtde_dias_medios_por_mes;
            echo number_format($valor_vr, 2, ',', '.');
        ?>
        </td>
        <td>
            <?=number_format($valor_alimentacao, 2, ',', '.');?>
        </td>
        <td>
        <?
            //12% é uma % que Empregador paga ao governo, 8% é o que o empregado totalizando 20% ...
            $inss = $total_mensal_pd * 0.12;
            echo number_format($inss, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            $fgts = $total_mensal_pd * 0.08;
            //8% é uma % que Empregador paga ao governo ...
            echo number_format($fgts, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            /*Fórmula utilizada até 03/10/2018 porque o Roberto achou um furo com valores muito altos para funcionários 
            que tinham pouco tempo de casa ...*/
            /*$multa_fgts                 = $fgts * 0.50;

            //Aviso = 3 dias por ano Trabalhado ...
            $qtde_dias_indenizado       = ($qtde_anos_registro * 3 + 30);
            $salario_diario_pd_pf       = $total_mensal_pd_pf / 30;

            //Está sendo cobrado 50% no fim da Fórmula pois estimamos que o funcionário rende apenas essa porcentagem nesse período ...
            $aviso_indenizado_total     = $qtde_dias_indenizado * $salario_diario_pd_pf * 0.5;
            $aviso_indenizado_mensal    = $aviso_indenizado_total / $qtde_anos_registro / 12;
            $custo_demissional_mensal   = $multa_fgts + $aviso_indenizado_mensal;*/
        
            /******************************************************************/
            /***************Nova Fórmula à partir de 04/10/2018****************/
            /******************************************************************/
            
            //Aviso = 3 dias por ano Trabalhado ...
            $qtde_dias_indenizado       = $qtde_anos_registro * 3;
            $salario_diario_pd_pf       = $total_mensal_pd_pf / 30;
            $aviso_indenizado_normal    = 30 * $salario_diario_pd_pf * 0.5;
            
            //Está sendo cobrado 50% no fim da Fórmula pois estimamos que o funcionário rende apenas essa porcentagem nesse período ...
            $aviso_indenizado_tempo_casa    =  $qtde_dias_indenizado *  $salario_diario_pd_pf;
            $aviso_indenizado_total         =  $aviso_indenizado_tempo_casa + $aviso_indenizado_normal;
            $aviso_indenizado_mensal        =   $aviso_indenizado_total /  $qtde_meses_registro;
            $multa_fgts_mensal              = $fgts * 0.50;
            $custo_demissional_mensal       = $multa_fgts_mensal  + $aviso_indenizado_mensal;
            
            /******************************************************************/
            
            echo number_format($custo_demissional_mensal, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            $plr = 550 / 12;
            echo number_format($plr, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            //Se o Func for de Vendas ou Tecnico, a empresa nao paga essa Contribuicao pq o Func e de um outro Sindicato ...
            if(strpos(strtoupper($campos[$i]['cargo']), 'VEND') !== false || strpos(strtoupper($campos[$i]['cargo']), 'TECN') !== false) {
                $pagamento_anual = 0;
            }else {//Outros cargos, a Empresa paga normalmente ...
                if($empresas[$h] == 1) {//Se Albafer e um valor fixo que equivale a R$ 113,33 + 3xR$ 78,89 "Valor Pago em 2012 " ...
                    $pagamento_anual = $treinamento_qualif_albafer;
                }else if($empresas[$h] == 2) {//Se Tool Master e uma % sobre o Salario PD s/ Comissao ...
                    $pagamento_anual = $treinamento_qualif_tool * ($total_mensal_pd - $campos[$i]['comissao_ultimos3meses_pd']) / 100;
                }else {//Se Grupo nao existe esse valor devido o funcionario nao ter Registro ...
                    $pagamento_anual = 0;
                }
            }
            $pagamento_mensal   = $pagamento_anual / 12;
            echo number_format($pagamento_mensal, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            /*2,33 ??? É o salário Total + 1/3 do Salário Total "Férias" = 1,33 + 1 Salário Total de 13º = 2,33, eu divido
            por 12 para achar o Valor Mensal nessa Coluna ...*/
            $decimo_terceiro_ferias_pd = (2.33 / 12 * $total_mensal_pd);
            $decimo_terceiro_ferias_pd*= 1.2;//Multiplico por 1.2 pq nas Férias e 13º se incidem 20% de impostos INSS + FGTS ...
            $decimo_terceiro_ferias_pf = (2.33 / 12 * $total_mensal_pf_sem_premio);
            $decimo_terceiro_ferias_pd_pf = $decimo_terceiro_ferias_pd + $decimo_terceiro_ferias_pf;
            echo number_format($decimo_terceiro_ferias_pd_pf, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            /*Descartaveis R$ 900,00 mensal p/ 80 func ...
             * Uniformes R$ 6.000,00 por ano p/ 80 func
             * Computador R$ 65.000 "50 PCs x R$ 1.300,00 cada um" a cada 48 meses por funcionario ...
             * Demais Equip / Insumos Diversos R$ 1.500,00 mensal por 80 func
             * EPIs R$ 200,00 por mes p/ 80 func
             * Materia Escritorio R$ 800,00 por mes p/ 80 func ...
             * Concessionarias "Agua, Luz, Telefone" R$ 3.000,00 mensal p/ 80 func ...
             * Padaria R$ 700,00 / Mercado R$ 1.000,00 / Agua Potavel R$ 150,00 p/ 80 func */
            $descartaveis   = 900 / $qtde_funcionarios;
            $uniformes      = (6000 / 12) / $qtde_funcionarios;
            $computador     = (65000 / 48) / $qtde_funcionarios;
            $demais_equip   = 1500 / $qtde_funcionarios;
            $epis           = 200 / $qtde_funcionarios;
            $materia_escrit = 800 / $qtde_funcionarios;
            $concessionarias= 3000 / $qtde_funcionarios;
            $pad_merc_agua  = 1850 / $qtde_funcionarios;
            $outros         = ($descartaveis + $uniformes + $computador + $demais_equip + $epis + $materia_escrit + $concessionarias + $pad_merc_agua);
            echo number_format($outros, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            $total_mensal_geral = $total_mensal_pd_pf + $convenio_medico + $combustivel + $celular + $valor_vt_empresa + $valor_vr + $valor_alimentacao + $inss + $fgts + $custo_demissional_mensal + $plr + $pagamento_mensal + $decimo_terceiro_ferias_pd_pf + $outros;
            echo number_format($total_mensal_geral, 2, ',', '.');
            $total_salarial_empresa+= $total_mensal_geral;
            $total_salarial_empresa_geral+= $total_mensal_geral;
            $total_salarial_pd_pf+= $total_mensal_pd_pf;
            $total_salarial_pd_pf_geral+= $total_mensal_pd_pf;

            //Essas 2 percentagens abaixo estão sendo ignoradas nos nossos cálculos ...
            //Falta Média Anual = 1 mês - 1/21 = 4,8%
            //Tempo Morto = 1 hora diaria - 1/9 = 11%
        ?>
        </td>
        <td>
            <?=number_format(($total_mensal_geral / $total_mensal_pd_pf - 1) * 100, 1, ',', '.');?>
        </td>
    </tr>
<?
                if(($i + 1) == $linhas) {
?>
        <tr class='linhadestaque'>
            <td colspan='5'>
                Total de Funcionário(s) da Empresa: <?=$linhas;?>
            </td>
            <td colspan='4'>
                Total Salarial PD + PF: 
            </td>
            <td align='right'>
                <?=number_format($total_salarial_pd_pf, 2, ',', '.');?>
            </td>
            <td colspan='9'>
                &nbsp;
            </td>
            <td colspan='6' align='right'>
                Total Salarial da Empresa: <?=number_format($total_salarial_empresa, 2, ',', '.');?>
            </td>
	</tr>
<?
                $total_funcionario_geral+= $linhas;
                }
            }
        }
    }
?>
    <tr class='linhadestaque'>
        <td colspan='5'>
            <font color='yellow'>
                Total de Funcionário(s) Geral: <?=$total_funcionario_geral;?>
            </font>
        </td>
        <td colspan='4'>
            <font color='yellow'>
                Total Salarial PD + PF Geral 
            </font>
        </td>
        <td align='right'>
            <font color='yellow'>
                <?=number_format($total_salarial_pd_pf_geral, 2, ',', '.');?>
            </font>
        </td>
        <td colspan='9'>
            &nbsp;
        </td>
        <td colspan='6' align='right'>
            <font color='yellow'>
                Total Salarial Geral R$ <?=number_format($total_salarial_empresa_geral, 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='25'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='window.print()' style='color:black' class='botao'>
            <input type='button' name='cmd_imprimir_pdf' value='Imprimir PDF' title='Imprimir PDF' onclick="nova_janela('imprimir_pdf.php?cmb_departamento=<?=$cmb_departamento;?>', 'IMPRIMIR PDF', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" style='color:red' class='botao'>
        </td>
    </tr>
</table>
<?}?>
</form>
</body>
</html>