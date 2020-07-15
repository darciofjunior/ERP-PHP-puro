<?
require('../../../lib/segurancas.php');
require('../../../lib/genericas.php');
require('../../../lib/data.php');
session_start('funcionarios');

//Se essa variável $_GET[ignorar_seguranca] = 1, então o Sistema não verifica a Segurança de Menu ...
if($_GET['ignorar_seguranca'] != 1) segurancas::geral('/erp/albafer/modulo/rh/hora_extra/opcoes_gerenciar_hora_extra.php', '../../../');

$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) FUNCIONÁRIO(S) NESSE PERÍODO.</font>";

//Utilizo essas variáveis mais abaixo na hora de fazer os cálculos de Hora Extra do funcionário ...
$valor_vale_refeicao_hora_extra    = genericas::variavel(35);

/****************************************************************************************************/
//Busca os detalhes de Hora(s) Extra(s) dos Funcionários Lançadas no Período passado por parâmetro ...
$sql = "SELECT e.`nomefantasia`, f.`nome`, fhe.* 
        FROM `funcionarios_horas_extras` fhe 
        INNER JOIN `funcionarios` f ON f.`id_funcionario` = fhe.`id_funcionario` 
        INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
        WHERE fhe.`data_hora_extra` BETWEEN '$_GET[txt_data_inicial]' AND '$_GET[txt_data_final]' 
        AND fhe.`id_funcionario` = '$_GET[id_funcionario_loop]' 
        AND f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) 
        AND f.`status` < '3' ORDER BY fhe.`id_funcionario`, fhe.`data_hora_extra` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Descritivo Histórico ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
<?
//Se não encontrou nenhum funcionário no intervalo especificado ...
    if($linhas == 0) {
?>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
<?
//Se existir pelo menos 1 Funcionário ...
    }else {
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Descritivo Histórico - 
            <font color='yellow'>
                Período de: 
            </font>
            <?=data::datetodata($_GET['txt_data_inicial'], '/');?>
            <font color='yellow'>
                à 
            </font>
            <?=data::datetodata($_GET['txt_data_final'], '/');?>
            -
            <font color='yellow'>
                Valor do VR: 
            </font>
            <?=number_format($valor_vale_refeicao_hora_extra, 2, ',', '.');?>
        </td>
    </tr>
<?
        $id_funcionario_anterior = '';
        for($i = 0; $i < $linhas; $i++) {
/*Aqui eu verifico se o Departamento Anterior é Diferente do Departamento Atual que está sendo listado
no loop, se for então eu atribuo o Departamento Atual p/ o Departamento Anterior ...*/
            if($id_funcionario_anterior != $campos[$i]['id_funcionario']) {
                $id_funcionario_anterior    = $campos[$i]['id_funcionario'];
                $qtde_vt_para_pagar         = 0;
                $qtde_vr_para_pagar         = 0;
                $qtde_hora_almoco_descontar = 0;
                $total_horas                = 0;
?>
    <tr class='linhadestaque' align='center'>
        <td align='left' colspan='7'>
            <font color='yellow'>
                Funcionário: 
            </font>
            <?=$campos[$i]['nome'];?> - 
            <font color='yellow'>
                    Empresa: 
            </font>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE'>
            <b>Data H. Extra</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Hora Inicial</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Hora Final</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Qtde de Horas</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Pagar VT</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Pagar VR</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Descontar Hora Almoço</b>
        </td>
    </tr>
<?
            }
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=data::datetodata($campos[$i]['data_hora_extra'], '/');?>
        </td>
        <td>
            <?=number_format($campos[$i]['hora_inicial'], 2, ':', '');?>
        </td>
        <td>
            <?=number_format($campos[$i]['hora_final'], 2, ':', '');?>
        </td>
        <td>
        <?
            echo number_format($campos[$i]['qtde_horas'], 2, ':', '');
//Aqui eu faço a separação das Horas e Minutos p/ calcular o Total mais abaixo ...
            $hora = strtok($campos[$i]['qtde_horas'], '.');//Pega até o Ponto
            $minuto = substr(strchr($campos[$i]['qtde_horas'], '.'), 1);//Pega a partir do Ponto
//Vai acumulando nessa variável p/ depois poder fazer o cálculo ...
            $total_horas+= $hora;
            $total_minutos+= $minuto;
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['pagar_vt'] == 'S') {
                echo '<font color="blue">SIM</font>';
                $qtde_vt_para_pagar++;
            }else {
                echo '<font color="red">NÃO</font>';
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['pagar_vr'] == 'S') {
                echo '<font color="blue">SIM</font>';
                $qtde_vr_para_pagar++;
            }else {
                echo '<font color="red">NÃO</font>';
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['descontar_hora_almoco'] == 'S') {
                echo '<font color="blue">SIM</font>';
                $qtde_hora_almoco_descontar++;
            }else {
                echo '<font color="red">NÃO</font>';
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='3' align='right'>
            <font color='yellow'>
                Sub Total Geral => 
            </font>
        </td>
        <td>
        <?
            $hora_inteira = intval($total_minutos / 60);
            $total_horas+= $hora_inteira;
//Cálculo da Sobra de Minutos ...
            $total_minutos-= $hora_inteira * 60;
            if($total_minutos < 10) $total_minutos = '0'.$total_minutos;
            echo number_format($total_horas.'.'.$total_minutos, 2, ':', '');
        ?>
        </td>
        <td>
            <?=$qtde_vt_para_pagar;?>
        </td>
        <td>
            <?=$qtde_vr_para_pagar;?>
        </td>
        <td>
            <?=$qtde_hora_almoco_descontar;?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='3' align='right'>
            <font color='yellow'>
                Descontar Hora(s) de Almoço => 
            </font>
        </td>
        <td>
            <?='-'.number_format($qtde_hora_almoco_descontar, 2, ':', '');?>
        </td>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='3' align='right'>
            <font color='yellow'>
                Total Geral => 
            </font>
        </td>
        <td>
        <?
//Desconto do Total de Horas a Qtde de Horas de Almoço ...
            $total_horas-= $qtde_hora_almoco_descontar;
            echo number_format($total_horas.'.'.$total_minutos, 2, ':', '');
        ?>
        </td>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            &nbsp;
        </td>
    </tr>
<?
    }
?>
</table>
</body>
</html>