<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='confirmacao'>PREENCHIMENTO INCORRETO P/ AS FALTAS DO ABSENTEISMO.</font>";
$mensagem[2] = "<font class='confirmacao'>ABSENTEISMO INCLUIDO COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>ABSENTEISMO JÁ EXISTENTE.</font>";
$mensagem[4] = "<font class='confirmacao'>ABSENTEÍSMO EXCLUÍDO COM SUCESSO.</font>";

if(!empty($_POST['id_plr_periodo'])) {//Exclusão do Absenteísmo ...
    $sql = "DELETE FROM `plr_absenteismos` WHERE `id_plr_periodo` = '$_POST[id_plr_periodo]' ";
    bancos::sql($sql);
    $valor = 4;
}
?>
<html>
<head>
<title>.:: Tabela de Absenteísmo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function excluir_absenteismo(id_plr_periodo) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
    if(mensagem == false) {
        return false
    }else {
        document.form.id_plr_periodo.value = id_plr_periodo
        document.form.submit()
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action=''>
<input type='hidden' name='id_plr_periodo'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)' align='center'>
    <tr class='atencao' align='center'>
        <td colspan='5'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Tabela de Absenteísmo
            &nbsp;&nbsp;-&nbsp;&nbsp;
            <a href='incluir.php' title='Incluir Absenteismo'>
                <img src = '../../../../imagem/menu/incluir.png' border='0'>
                <font color='#FFFF00'>
                    Incluir Absenteismo
                </font>
            </a>
        </td>
    </tr>
<?
/*Aqui eu verifico qtde de Períodos que estão cadastrados e que estão atrelados a 
algum absenteismo ...*/
    $sql = "SELECT plra.*, CONCAT(DATE_FORMAT(pp.data_inicial, '%d/%m/%Y'), ' à ', DATE_FORMAT(pp.data_final, '%d/%m/%Y')) AS periodo, pp.data_pagamento 
            FROM `plr_absenteismos` plra 
            INNER JOIN `plr_periodos` pp ON pp.id_plr_periodo = plra.id_plr_periodo 
            ORDER BY plra.id_plr_periodo DESC, plra.abs_qtde_faltas_anual ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='5'>
            NÃO HÁ ABSENTEISMO(S) CADASTRADO(S).
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
        <td colspan='5'>
            <font color='yellow'>
                <b>Período: </b>
            </font>
            <?=$campos[$i]['periodo'];?>
            &nbsp;
            <?
                $data_atual = date('Y-m-d');
//Se a Data Atual for maior do que a Data de Pagamento, então eu ignoro esse trecho de código
                if($data_atual > $campos[$i]['data_pagamento']) {
                    echo '&nbsp';
                }else {
            ?>
            <img src='../../../../imagem/menu/excluir.png' border='0' onclick="excluir_absenteismo('<?=$campos[0]['id_plr_periodo'];?>')" alt='Excluir Período' title='Excluir Período'>
            <?
                }
            ?>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <b>Qtde de Faltas <br/>Anual</b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Qtde de Faltas <br>Semestral</b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Valor Prêmio <br>Anual</b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Valor Prêmio <br>Semestral</b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>% Prêmio</b>
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
        <td>
            <?=$campos[$i]['percentagem_premio'].' %';?>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhadestaque'>
        <td colspan='5'>
            &nbsp;
        </td>
    </tr>
</table>
</form>
</body>
</html>