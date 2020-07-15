<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/depto_pessoal.php');
require('../../../lib/genericas.php');

$mensagem[1] = "<font class='atencao'>NÃO EXISTE NENHUM FUNCIONÁRIO CADASTRADO.</font>";
$mensagem[2] = "<font class='confirmacao'>HORA(S) EXTRA(S) INCLUÍDA(S) COM SUCESSO.</font>";
$mensagem[3] = "<font class='confirmacao'>HORA(S) EXTRA(S) ALTERADA(S) COM SUCESSO.</font>";

//Utilizo essas variáveis mais abaixo na hora de fazer os cálculos de Hora Extra do funcionário ...
$valor_vale_refeicao_hora_extra = genericas::variavel(35);

$data_atual     = date('Y-m-d');
$data_sys       = date('Y-m-d H:i:s');
$data_inicial   = data::datatodate($_GET['txt_data_inicial'], '-');
$data_final     = data::datatodate($_GET['txt_data_final'], '-');
$data_pagamento = data::datatodate($_GET['txt_data_pagamento'], '-');

//Se a Data Atual for maior do que a Data de Pagamento, então eu ignoro esse trecho de código
if($data_atual > $data_pagamento) $ignorar = 1;
/****************************************************************************************************/
if($ignorar != 1) {//Significa que a Data de Pagamento, ainda está dentro do Prazo ...
/****************************************************************************/
/****************************Deletar Funcionários****************************/
/****************************************************************************/
/*Controle p/ Deletar os funcionários em que anteriormente foram geradas Horas Extras, mas que hoje 
já não se encontram mais nessa lista de Horas Extras dentro do período especificado ...*/
    $sql = "SELECT `id_funcionario_he_rel`, `id_funcionario` 
            FROM `funcionarios_hes_rel` 
            WHERE (`data_inicial` BETWEEN '$data_inicial' AND '$data_final') ";
    $campos_horas_rel = bancos::sql($sql);
    $linhas_horas_rel = count($campos_horas_rel);
//Disparo do Loop ...
    for($i = 0; $i < $linhas_horas_rel; $i++) {
        $id_funcionario_he_rel = $campos_horas_rel[$i]['id_funcionario_he_rel'];
        $id_funcionario_loop = $campos_horas_rel[$i]['id_funcionario'];
//Verifico se esse funcionário hoje ainda se encontra essa relação de Horas Extras do Sistema ...
        $sql = "SELECT `id_funcionario_hora_extra` 
                FROM `funcionarios_horas_extras` 
                WHERE `id_funcionario` = '$id_funcionario_loop' 
                AND `data_hora_extra` BETWEEN '$data_inicial' AND '$data_final' LIMIT 1 ";
        $campos = bancos::sql($sql);
//Se esse usuário já não se encontra mais, então eu deleto esse da Tabela de Relatórios ...
        if(count($campos) == 0) {
            $sql = "DELETE FROM `funcionarios_hes_rel` WHERE `id_funcionario_he_rel` = '$id_funcionario_he_rel' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
/****************************************************************************/
//Variáveis auxiliar ...
    $total_horas    = 0;
    $total_minutos  = 0;
    $pagar_vr       = 0;

//Busca da Qtde de Hora(s) Extra(s) dos Funcionários Lançadas no Período especificados pelo usuário 
    $sql = "SELECT `id_funcionario`, `qtde_horas`, `pagar_vr` 
            FROM `funcionarios_horas_extras` 
            WHERE `data_hora_extra` BETWEEN '$data_inicial' AND '$data_final' 
            ORDER BY `id_funcionario` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
//Disparo do Loop ...
    for($i = 0; $i <= $linhas; $i++) {
        $id_funcionario_loop = $campos[$i]['id_funcionario'];
        
        //Aqui eu faço a separação das Horas e Minutos p/ calcular o Total mais abaixo ...
        $hora   = strtok($campos[$i]['qtde_horas'], '.');//Pega até o Ponto ...
        $minuto = substr(strchr($campos[$i]['qtde_horas'], '.'), 1);//Pega a partir do Ponto ...
//Vai acumulando nessa variável p/ depois poder fazer o cálculo ...
        $total_horas+= $hora;
        $total_minutos+= $minuto;
        if($campos[$i]['pagar_vr'] == 'S') $pagar_vr++;
        
        if(($id_funcionario_loop != $campos[$i + 1]['id_funcionario'])) {//Funcionário atual diferente do próximo ...
            $hora_inteira = intval($total_minutos / 60);
            $total_horas+= $hora_inteira - $pagar_vr;
//Cálculo da Sobra de Minutos ...
            $total_minutos-= $hora_inteira * 60;
            if($total_minutos < 10) $total_minutos = '0'.$total_minutos;
            $horario_extra = $total_horas.'.'.$total_minutos;
//Busca de Dados no Cadastro de Funcionários referentes a salário ...
            $sql = "SELECT `salario_pd`, `salario_pf`, `tipo_salario` 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = '$id_funcionario_loop' LIMIT 1 ";
            $campos_func        = bancos::sql($sql);
            $valor_hora_salario = $campos_func[0]['salario_pd'] + $campos_func[0]['salario_pf'];
            //Salário, se for Mensalista, transformo em Hora ...
            if($campos_func[0]['tipo_salario'] == 2) $valor_hora_salario/= 220;
            $salario_mensal = round($valor_hora_salario * 220, 2);
            /******************************************Controle com o Add de Hora Extra******************************************/
            $vetor_valores          = depto_pessoal::valores_hora_extra($data_pagamento, $salario_mensal);
            $adicional_hora_extra   = $vetor_valores['adicional_hora_extra'];
            $valor_hora_extra_min   = $vetor_valores['valor_hora_extra_min'];
            
            //Valor da Hora Extra ...
            $valor_hora_extra = $valor_hora_salario * (1 + $adicional_hora_extra / 100);
            $valor_hora_extra = round(max($valor_hora_extra, $valor_hora_extra_min), 2);
            /********************************************************************************************************************/
//Variáveis p/ o cálculo do Valor Extra à Receber ...
            $hora           = strtok($horario_extra, '.');//Pega até o Ponto
            $minuto         = substr(strchr($horario_extra, '.'), 1);//Pega a partir do Ponto
            $extra_receber  = (($hora + $minuto / 60) * ($valor_hora_extra));
/*Verifico se todas as Horas Extras do Funcionário Corrente já foram gravadas na Tabela de Relatórios 
no período especificado ...*/
            $sql = "SELECT `id_funcionario_he_rel` 
                    FROM `funcionarios_hes_rel` 
                    WHERE (`data_inicial` BETWEEN '$data_inicial' AND '$data_final') 
                    AND `id_funcionario` = '$id_funcionario_loop' LIMIT 1 ";
            $campos_horas_rel = bancos::sql($sql);
            if(count($campos_horas_rel) == 0) {//Não achou funcionário no Relatório, Insert ...
                $insert_extendido.= " (NULL, '$id_funcionario_loop', '$data_pagamento', '$data_inicial', '$data_final', '$horario_extra', '$extra_receber', '$data_sys'), ";
            }else {//Achou sendo assim só faço Update ...
                $sql = "UPDATE `funcionarios_hes_rel` SET `qtde_horas` = '$horario_extra', `extra_receber` = '$extra_receber' WHERE `id_funcionario_he_rel` = '".$campos_horas_rel[0]['id_funcionario_he_rel']."' LIMIT 1 ";
                bancos::sql($sql);
            }
            //Limpo as variáveis p/ não herdar valores p/ os próximos Loops ...
            $total_horas    = 0;
            $total_minutos  = 0;
            $pagar_vr       = 0;
        }
    }
//Se existir essa variável...
    if(!empty($insert_extendido)) {
        $insert_extendido = substr($insert_extendido, 0, strlen($insert_extendido) - 2);
//Gravando as Horas Extras dos Funcionários ...
        $sql = "INSERT INTO `funcionarios_hes_rel` (`id_funcionario_he_rel`, `id_funcionario`, `data_pagamento`, `data_inicial`, `data_final`, `qtde_horas`, `extra_receber`, `data_sys`) VALUES 
                $insert_extendido ";
        bancos::sql($sql);
    }
}
/****************************************************************************************************/
//Busca de todos os funcionários que estão gravados na Tabela de Relatórios no período especificado ...
$sql = "SELECT e.`nomefantasia`, f.`id_funcionario`, f.`tipo_salario`, f.`salario_pd`, f.`salario_pf`, f.`nome`, fhr.* 
        FROM `funcionarios` f 
        INNER JOIN `funcionarios_hes_rel` fhr ON fhr.`id_funcionario` = f.`id_funcionario` AND fhr.`data_inicial` BETWEEN '$data_inicial' AND '$data_final' 
        INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
        WHERE f.`status` < '3' 
        AND f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) ORDER BY f.`nome` ";
$campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Consultar Hora(s) Extra(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function imprimir_hora_extra() {
//Abrindo Pop-Up ...
    nova_janela('relatorios/relatorio.php?txt_data_inicial=<?=$data_inicial;?>&txt_data_final=<?=$data_final;?>&data_pagamento=<?=$data_pagamento;?>', 'CONSULTAR', 'F')
}
</Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='8'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Controlar Hora(s) Extra(s)
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td colspan='8'>
            <font color='yellow'>
                Data Inicial: 
            </font>
            <?=$_GET['txt_data_inicial'];?>
            &nbsp;
            <font color='yellow'>
                Data Final:
            </font>
            <?=$_GET['txt_data_final'];?>
            &nbsp;
            <font color='yellow'>
                Data do Pagamento: 
            </font>
            <?=$_GET['txt_data_pagamento'];?>
            <?
                //Significa que já aconteceu a Data de Pagamento ...
                if($ignorar == 1) echo ' - <font color="darkred">JÁ FOI REALIZADO O PAGAMENTO</font>';
            ?>
        </td>
    </tr>
<?
//Se não encontrou nenhum funcionário no intervalo especificado ...
if($linhas == 0) {
?>
    <tr>
        <td></td>
    </tr>
    <tr align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="parent.location = 'opcoes_gerenciar_hora_extra.php'" class='botao'>
        </td>
    </tr>
<?
//Se existir pelo menos 1 Funcionário ...
}else {
?>
    <tr class='linhadestaque' align='center'>
        <td>Funcionário</td>
        <td>Empresa</td>
        <td>Vlr da <br>H. Extra</td>
        <td>Qtde <br>de Horas</td>
        <td>Vlr à Receber <br>H. Extra</td>
        <td>Vlr VT</td>
        <td>Vlr VR</td>
        <td>Vlr Total à Receber <br>H. Extra</td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
        $id_funcionario_he_rel = $campos[$i]['id_funcionario_he_rel'];
//Busca na Tabela de Hora(s) Extra(s) a Qtde Total de VT do Funcionário corrente no Período especificados ...
        $sql = "SELECT COUNT(`pagar_vt`) AS qtde_vt_para_pagar 
                FROM `funcionarios_horas_extras` 
                WHERE `id_funcionario` = ".$campos[$i]['id_funcionario']." 
                AND `data_hora_extra` BETWEEN '$data_inicial' AND '$data_final' 
                AND `pagar_vt` = 'S' ";
        $campos_pagar_vt    = bancos::sql($sql);
        $qtde_vt_para_pagar = $campos_pagar_vt[0]['qtde_vt_para_pagar'];
//Busca na Tabela de Hora(s) Extra(s) a Qtde Total de VR do Funcionário corrente no Período especificados ...
        $sql = "SELECT COUNT(`pagar_vr`) AS qtde_vr_para_pagar 
                FROM `funcionarios_horas_extras` 
                WHERE `id_funcionario` = '".$campos[$i]['id_funcionario']."' 
                AND `data_hora_extra` BETWEEN '$data_inicial' AND '$data_final' 
                AND `pagar_vr` = 'S' ";
        $campos_pagar_vr    = bancos::sql($sql);
        $qtde_vr_para_pagar = $campos_pagar_vr[0]['qtde_vr_para_pagar'];
//Busca na Tabela de Hora(s) Extra(s) a Qtde Total de VR do Funcionário corrente no Período especificados ...
        $sql = "SELECT COUNT(`descontar_hora_almoco`) AS qtde_hora_almoco_descontar 
                FROM `funcionarios_horas_extras` 
                WHERE `id_funcionario` = '".$campos[$i]['id_funcionario']."' 
                AND `data_hora_extra` BETWEEN '$data_inicial' AND '$data_final' 
                AND `pagar_vr` = 'S' ";
        $campos_pagar_vr            = bancos::sql($sql);
        $qtde_hora_almoco_descontar = $campos_pagar_vr[0]['qtde_hora_almoco_descontar'];
/****************************************************************************************************/
//Atualizo os Dados de VT e VR na Tabela de Relatórios dentro do período especificado ...
        $sql = "UPDATE `funcionarios_hes_rel` SET `qtde_vt_para_pagar` = '$qtde_vt_para_pagar', `qtde_vr_para_pagar` = '$qtde_vr_para_pagar', `qtde_hora_almoco_descontar` = '$qtde_hora_almoco_descontar' WHERE `id_funcionario_he_rel` = '$id_funcionario_he_rel' LIMIT 1 ";
        bancos::sql($sql);
/****************************************************************************************************/
//Coloquei esse nome de $id_funcionario_loop, p/ não dar conflito com a variável "id_funcionário" da sessão
        $url = "javascript:nova_janela('../funcionario/alterar_dados_profissionais.php?id_funcionario_loop=".$campos[$i]['id_funcionario']."&pop_up=1', 'DETALHES', '', '', '', '', 550, 900, 'c', 'c', '', '', 's', 's', '', '', '') ";
        $valor_hora_salario = $campos[$i]['salario_pd'] + $campos[$i]['salario_pf'];
//Salário, se for Mensalista, transformo em Hora ...
        if($campos[$i]['tipo_salario'] == 2) $valor_hora_salario/= 220;
        $salario_mensal = round($valor_hora_salario * 220, 2);
        /******************************************Controle com o Add de Hora Extra******************************************/
        $vetor_valores          = depto_pessoal::valores_hora_extra($data_pagamento, $salario_mensal);
        $adicional_hora_extra   = $vetor_valores['adicional_hora_extra'];
        $valor_hora_extra_min   = $vetor_valores['valor_hora_extra_min'];
        //Valor da Hora Extra ...
        $valor_hora_extra = $valor_hora_salario * (1 + $adicional_hora_extra / 100);
        $valor_hora_extra = round(max($valor_hora_extra, $valor_hora_extra_min), 2);
        /********************************************************************************************************************/
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <a href="<?=$url;?>" title='Detalhes Funcionário' class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
            &nbsp;
            <a href = 'descritivo_historico.php?id_funcionario_loop=<?=$campos[$i]['id_funcionario'];?>&txt_data_inicial=<?=$data_inicial;?>&txt_data_final=<?=$data_final;?>' class='html5lightbox'>
                <img src = '../../../imagem/visualizar_detalhes.png' title='Descritivo Histórico' alt='Descritivo Histórico' border='0'>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td align='right'>
            <font title="<?='Vlr Hora Salário R$ '.number_format($valor_hora_salario, 2, ',', '.');?>" style="cursor:help">
                <?='R$ '.number_format($valor_hora_extra, 2, ',', '.');?>
            </font>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde_horas'], 2, ':', '');?>
        </td>
        <td align='right'>
            <?='R$ '.number_format($campos[$i]['extra_receber'], 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
//Aqui eu busco o Valor Diário de VT consumido por um Funcionário ...
            $sql = "SELECT SUM(fvt.`qtde_vale` * vt.`valor_unitario`) AS vlr_diario_vt 
                    FROM `funcionarios_vs_vales_transportes` fvt 
                    INNER JOIN `vales_transportes` vt ON vt.`id_vale_transporte` = fvt.`id_vale_transporte` AND vt.`ativo` = '1' 
                    WHERE fvt.`id_funcionario` = ".$campos[$i]['id_funcionario']." ";
            $campos_funcionarios_vt = bancos::sql($sql);
            $vlr_diario_vt          = $campos_funcionarios_vt[0]['vlr_diario_vt'];
            $total_pagar_vt         = $vlr_diario_vt * $qtde_vt_para_pagar;
            if($total_pagar_vt != 0) echo 'R$ '.number_format($total_pagar_vt, 2, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
//Aqui, eu pego a Qtde de VR do Funcionário e multiplico pelo Valor do Vale Refeição def. em Variáveis ...
            $total_pagar_vr = $valor_vale_refeicao_hora_extra * $qtde_vr_para_pagar;
            if($total_pagar_vr != 0) echo 'R$ '.number_format($valor_vale_refeicao_hora_extra * $qtde_vr_para_pagar, 2, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
            $vlr_total_receber_he = $campos[$i]['extra_receber'] + $total_pagar_vr + $total_pagar_vt;
            /*************************Financeiro*************************/
            //Arredondamento para o Financeiro, não ter que pagar com tantas moedas de centavo ...
            $reais                  = intval($vlr_total_receber_he);
            $centavos               = round($vlr_total_receber_he - intval($vlr_total_receber_he), 2);
            $vlr_total_receber_he   = ($centavos <= 0.50) ? $reais.'.50' : $reais + 1;
            /************************************************************/
            echo 'R$ '.number_format($vlr_total_receber_he, 2, ',', '.');
            $vlr_total_receber_he_geral+= $vlr_total_receber_he;
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhadestaque'>
        <td colspan='7' align='right'>
            <font color='yellow' size='2'>
                <b>Vlr Total Extra(s) à Receber H. Extra Geral: </b>
            </font>
        </td>
        <td align='right'>
            <?='R$ '.number_format($vlr_total_receber_he_geral, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="parent.location = 'opcoes_gerenciar_hora_extra.php'" class='botao'>
            <input type='button' name='cmd_imprimir_hora_extra' value='Imprimir Hora(s) Extra(s)' title='Imprimir Hora(s) Extra(s)' onclick='imprimir_hora_extra()' style='color:black' class='botao'>
        </td>
    </tr>
<?
    }
?>
</table>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>
<pre>
              TABELA VÁLIDA PARA DATA DE PAGAMENTO A PARTIR DE 05/07/2018
                       SALÁRIO ATÉ
              MENSAL               P/ HORA         %H. EXTRA   H. EXTRA MÍNIMA
<	 R$            -   	 R$        -   	     45,00 	 R$					 
<	 R$       1.500,00 	 R$     6,82 	     42,50 	 R$     9,72
<	 R$       1.750,00 	 R$     7,95 	     40,00 	 R$    11,14
<	 R$       2.000,00 	 R$     9,09 	     37,50 	 R$    12,50
<	 R$       2.400,00 	 R$    10,91 	     35,00 	 R$    14,73
<	 R$       2.800,00 	 R$    12,73 	     32,50 	 R$    16,86
<	 R$       3.200,00 	 R$    14,55 	     30,00 	 R$    18,91
 

* Já vem descontado da Qtde de Horas, o Total de Hora(s) do VR de acordo com a Qtde de VR(s) utilizado(s) por funcionário
</pre>