<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/rh/plr/gerenciar/opcoes.php', '../../../../');
?>
<html>
<head>
<title>.:: Detalhes de Absenteismo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Detalhes de Absenteismo
        </td>
    </tr>
<?
//Aqui eu busco todos os Absenteismos do PLR ...
    $sql = "SELECT plra.*, CONCAT(DATE_FORMAT(pp.data_inicial, '%d/%m/%Y'), ' à ', DATE_FORMAT(pp.data_final, '%d/%m/%Y')) AS periodo 
            FROM `plr_absenteismos` plra 
            INNER JOIN `plr_periodos` pp ON pp.`id_plr_periodo` = plra.`id_plr_periodo` 
            WHERE plra.`id_plr_periodo` = '$_GET[cmb_periodo]' 
            ORDER BY plra.id_plr_periodo, plra.abs_qtde_faltas_anual ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='4'>
            NÃO HÁ ABSENTEISMO(S) CADASTRADO(S) NESSE PERÍODO.
        </td>
    </tr>
<?
    }else {
        $id_plr_periodo_anterior = '';
        for($i = 0; $i < $linhas ; $i++) {
/*Aqui eu verifico se o Período Anterior é Diferente do Período Atual que está sendo listado
no loop, se for então eu atribuo o Período Atual p/ o Período Anterior ...*/
            if($id_plr_periodo_anterior != $campos[$i]['id_plr_periodo']) {
                $id_plr_periodo_anterior = $campos[$i]['id_plr_periodo'];
?>
    <tr class='linhadestaque'>
        <td colspan='4'>
            <font color='yellow'>
                <b>Período: </b>
            </font>
            <?=$campos[$i]['periodo'];?>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <font size='1'>
                <b>Qtde de Faltas <br>Anual</b>
            </font>
        </td>
        <td bgcolor='#CCCCCC'>
            <font size='1'>
                <b>Qtde de Faltas <br>Semestral</b>
            </font>
        </td>
        <td bgcolor='#CCCCCC'>
            <font size='1'>
                <b>Valor Prêmio <br>Anual</b>
            </font>
        </td>
        <td bgcolor='#CCCCCC'>
            <font size='1'>
                <b>Valor Prêmio <br>Semestral</b>
            </font>
        </td>
    </tr>
<?
            }
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='right'>
        <td align='center'>
            <?=$campos[$i]['abs_qtde_faltas_anual'];?>
        </td>
        <td align='center'>
            <?='<= '.number_format($campos[$i]['abs_qtde_faltas_anual'] / 2, 1, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($campos[$i]['abs_valor_premio_anual'], 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($campos[$i]['abs_valor_premio_anual'] / 2, 2, ',', '.');?>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
                &nbsp;
        </td>
    </tr>
</table>
</body>
</html>