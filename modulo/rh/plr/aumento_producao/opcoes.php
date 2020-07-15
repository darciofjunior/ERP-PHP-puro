<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='erro'>PRODUTIVIDADE J� EXISTENTE.</font>";
$mensagem[2] = "<font class='confirmacao'>PRODUTIVIDADE INCLUIDO COM SUCESSO.</font>";
$mensagem[3] = "<font class='confirmacao'>PRODUTIVIDADE EXCLU�DO COM SUCESSO.</font>";

if(!empty($_POST['id_plr_periodo'])) {//Exclus�o do Produtividade ...
    $sql = "DELETE FROM `plr_aumento_producoes` where id_plr_periodo = '$_POST[id_plr_periodo]' ";
    bancos::sql($sql);
    $valor = 3;
}
?>
<html>
<head>
<title>.:: Tabela de Produtividade ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function excluir_aumento_producao(id_plr_periodo) {
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
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Tabela de Produtividade
            &nbsp;&nbsp;-&nbsp;&nbsp;
            <a href='incluir.php' title='Incluir Produtividade'>
                <img src = '../../../../imagem/menu/incluir.png' border='0'>
                <font color='#FFFF00'>
                    Incluir Produtividade
                </font>
            </a>
        </td>
    </tr>
<?
//Aqui eu busco todos os Aumentos de Produ��es do PLR ...
    $sql = "SELECT plrap.*, CONCAT(DATE_FORMAT(pp.data_inicial, '%d/%m/%Y'), ' � ', DATE_FORMAT(pp.data_final, '%d/%m/%Y')) AS periodo, pp.data_pagamento 
            FROM `plr_aumento_producoes` plrap 
            INNER JOIN `plr_periodos` pp ON pp.id_plr_periodo = plrap.id_plr_periodo 
            ORDER BY plrap.id_plr_periodo DESC, plrap.producao_anual ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='4'>
            N�O H� PRODU��O(�ES) MENSAL(IS) CADASTRADO(S).
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
            <font color="yellow">
                    <b>Per�odo: </b>
            </font>
            <?=$campos[$i]['periodo'];?>
            &nbsp;
        <?
            $data_atual = date('Y-m-d');
//Se a Data Atual for maior do que a Data de Pagamento, ent�o eu ignoro esse trecho de c�digo
            if($data_atual > $campos[$i]['data_pagamento']) {
                echo '&nbsp';
            }else {
        ?>
                <img src='../../../../imagem/menu/excluir.png' border='0' onclick="excluir_aumento_producao('<?=$campos[$i]['id_plr_periodo'];?>')" alt='Excluir Produtividade' title='Excluir Produtividade'>
        <?
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <b>Produ��o <br>Anual</b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Produ��o <br>Semestral</b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Valor Pr�mio <br>Anual</b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Valor Pr�mio <br>Semestral</b>
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
    <tr class='linhadestaque'>
        <td colspan='4'>
            &nbsp;
        </td>
    </tr>
</table>
</form>
</body>
</html>