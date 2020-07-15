<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/rh/plr/gerenciar3/opcoes.php', '../../../../');
?>
<html>
<head>
<title>.:: Detalhes de Aumento de Produ��o ::.</title>
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
            Detalhes de Aumento Produ��o
        </td>
    </tr>
<?
//Aqui eu busco todos os Aumentos de Produ��es do PLR ...
    $sql = "SELECT plrap.*, CONCAT(DATE_FORMAT(pp.data_inicial, '%d/%m/%Y'), ' � ', DATE_FORMAT(pp.data_final, '%d/%m/%Y')) AS periodo 
            FROM `plr_aumento_producoes` plrap 
            INNER JOIN `plr_periodos` pp ON pp.id_plr_periodo = plrap.id_plr_periodo 
            WHERE plrap.`id_plr_periodo` = '$_GET[cmb_periodo]' 
            ORDER BY plrap.id_plr_periodo, plrap.producao_anual ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='5'>
            N�O H� AUMENTO DE PRODU��O(�ES) CADASTRADO(S) NESSE PER�ODO.
        </td>
    </tr>
<?
    }else {
        $id_plr_periodo_anterior = '';
        for($i = 0; $i < $linhas ; $i++) {
/*Aqui eu verifico se o Per�odo Anterior � Diferente do Per�odo Atual que est� sendo listado
no loop, se for ent�o eu atribuo o Per�odo Atual p/ o Per�odo Anterior ...*/
            if($id_plr_periodo_anterior != $campos[$i]['id_plr_periodo']) {
                $id_plr_periodo_anterior = $campos[$i]['id_plr_periodo'];
?>
    <tr class='linhadestaque'>
        <td colspan='4'>
            <font color='yellow'>
                <b>Per�odo: </b>
            </font>
            <?=$campos[$i]['periodo'];?>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <font size='1'>
                <b>Produ��o <br>Anual</b>
            </font>
        </td>
        <td bgcolor='#CCCCCC'>
            <font size='1'>
                <b>Produ��o <br>Semestral</b>
            </font>
        </td>
        <td bgcolor='#CCCCCC'>
            <font size='1'>
                <b>Valor Pr�mio <br>Anual</b>
            </font>
        </td>
        <td bgcolor='#CCCCCC'>
            <font size='1'>
                <b>Valor Pr�mio <br>Semestral</b>
            </font>
        </td>
    </tr>
<?
            }
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='right'>
        <td>
            <?='R$ '.number_format($campos[$i]['producao_anual'], 2, ',', '.');?>
        </td>
        <td>
            <?='>= R$ '.number_format($campos[$i]['producao_anual'] / 2, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($campos[$i]['valor_premio_anual'], 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($campos[$i]['valor_premio_anual'] / 2, 2, ',', '.');?>
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