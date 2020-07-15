<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='confirmacao'>PERÍODO INCLUÍDO COM SUCESSO.</font>";
$mensagem[2] = "<font class='confirmacao'>PERÍODO EXCLUÍDO COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>ESSE PERÍODO NÃO PODE SER EXCLUÍDO, DEVIDO ESTAR EM USO.</font>";

if(!empty($id_plr_periodo)) {//Exclusão dos Períodos ...
/*Antes de excluir esse período, eu verifico se o mesmo se encontra em uso em 
algum outro lugar ...*/
    $sql = "SELECT DISTINCT(id_plr_absenteismo) 
            FROM `plr_absenteismos` 
            WHERE `id_plr_periodo` = '$id_plr_periodo' 
            UNION
            SELECT DISTINCT(id_plr_aumento_producao) 
            FROM `plr_aumento_producoes` 
            WHERE `id_plr_periodo` = '$id_plr_periodo' 
            UNION 
            SELECT DISTINCT(id_plr_produtividade) 
            FROM `plr_produtividades` 
            WHERE `id_plr_periodo` = '$id_plr_periodo' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
        $sql = "DELETE FROM `plr_periodos` WHERE `id_plr_periodo` = '$id_plr_periodo' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 2;
    }else {
        $valor = 3;
    }
}
?>
<html>
<head>
<title>.:: Opções de PLR ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function avancar() {
//Período ...
    if(!combo('form', 'cmb_periodo', '', 'SELECIONE O PERÍODO !')) {
        return false
    }
    window.location = 'gerenciar.php?cmb_periodo='+document.form.cmb_periodo.value
}
</Script>
</head>
<body onload='document.form.cmb_periodo.focus()'>
<form name='form' method='post' action=''>
<input type='hidden' name='id_plr_periodo'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Gerenciar PLR Antigo
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <b>Período: </b>
            <select name='cmb_periodo' title='Selecione o Período' class='combo'>
            <?
//Aqui eu só listo o 1º Período, porque ele foi feito com outra regra de cálculo ...
                $sql = "SELECT id_plr_periodo, CONCAT(DATE_FORMAT(data_inicial, '%d/%m/%Y'), ' à ', DATE_FORMAT(data_final, '%d/%m/%Y'), ' - Pagto: ', DATE_FORMAT(data_pagamento, '%d/%m/%Y')) AS periodo 
                        FROM `plr_periodos` 
                        ORDER BY id_plr_periodo LIMIT 1 ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_avançar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' onclick='avancar()' class='botao'>
        </td>
    </tr>
</table>	
<br>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='atencao' align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align="center">
        <td colspan='3'>
            Período(s)
        </td>
    </tr>
<?
//Aqui eu busco todos as Períodos do PLR ...
    $sql = "SELECT * 
            FROM `plr_periodos` 
            ORDER BY data_inicial LIMIT 1 ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='3'>
            <font size='-1'>
                NÃO HÁ PERÍODO(S) CADASTRADO(S).
            </font>
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <b>Data Inicial</b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Data Final</b>
        </td>
        <td bgcolor='#CCCCCC'>
            <font title="Data de Pagamento" style='cursor:help'>
                <b>Data Pagto</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=data::datetodata($campos[0]['data_inicial'], '/');?>
        </td>
        <td>
            <?=data::datetodata($campos[0]['data_final'], '/');?>
        </td>
        <td>
            <?=data::datetodata($campos[0]['data_pagamento'], '/');?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
</table>
</form>
</body>
</html>