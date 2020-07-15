<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/rh/plr/gerenciar3/opcoes.php', '../../../../');
?>
<html>
<head>
<title>.:: Detalhes de Produtividade  ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Detalhes de Produtividade
        </td>
    </tr>
<?
//Aqui eu busco todas as Produtividades do PLR no per�odo passado por par�metro ...
    $sql = "SELECT prlp.*, CONCAT(DATE_FORMAT(pp.data_inicial, '%d/%m/%Y'), ' � ', DATE_FORMAT(pp.data_final, '%d/%m/%Y')) AS periodo 
            FROM `plr_produtividades` prlp 
            INNER JOIN `plr_periodos` pp ON pp.id_plr_periodo = prlp.id_plr_periodo 
            WHERE prlp.`id_plr_periodo` = '$_GET[cmb_periodo]' 
            ORDER BY prlp.`id_plr_periodo`, prlp.`data_inicial_sub_per` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='3'>
            N�O H� PRODUTIVIDADE(S) CADASTRADA(S) NESSE PER�ODO.
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
        <td colspan='3'>
            <font color='yellow'>
                <b>Per�odo: </b>
            </font>
            <?=$campos[$i]['periodo'];?>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <font size='1'>
                <b>Data Inicial Sub-Per�odo</b>
            </font>
        </td>
        <td bgcolor='#CCCCCC'>
            <font size='1'>
                <b>Data Final Sub-Per�odo</b>
            </font>
        </td>
        <td bgcolor='#CCCCCC'>
            <font size='1'>
                <b>Albaf�r + Tool</b>
            </font>
        </td>
    </tr>
<?
            }
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='right'>
        <td>
            <?=data::datetodata($campos[$i]['data_inicial_sub_per'], '/');?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_final_sub_per'], '/');?>
        </td>
        <td>
            <?='R$ '.number_format($campos[$i]['albafer_tool'], 2, ',', '.');?>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
</table>
</body>
</html>