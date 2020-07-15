<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = '<font class="confirmacao">PRODU��O MENSAL EXCLU�DA COM SUCESSO.</font>';

if(!empty($_POST['hdd_plr_produtividade'])) {//Exclus�o das Produ��o Mensal ...
    $sql = "DELETE FROM `plr_produtividades` WHERE `id_plr_produtividade` = '$_POST[hdd_plr_produtividade]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}
?>
<html>
<head>
<title>.:: Tabela de Produ��o Mensal ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function excluir_item(id_plr_produtividade) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
    if(mensagem == false) {
        return false
    }else {
        document.form.hdd_plr_produtividade.value = id_plr_produtividade
        document.form.submit()
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action=''>
<input type='hidden' name='hdd_plr_produtividade'>
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr class="atencao" align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='4'>
            Tabela de Produ��o Mensal
            &nbsp;&nbsp;-&nbsp;&nbsp;
            <a href='incluir.php' title='Incluir Produ��o Mensal'>
                <img src = '../../../../imagem/menu/incluir.png' border='0'>
                <font color='#FFFF00'>
                    Incluir Produ��o Mensal
                </font>
            </a>
        </td>
    </tr>
<?
//Aqui eu busco todas as Produ��es Mensais do PLR ...
    $sql = "SELECT prlp.*, CONCAT(DATE_FORMAT(pp.data_inicial, '%d/%m/%Y'), ' � ', DATE_FORMAT(pp.data_final, '%d/%m/%Y')) AS periodo, pp.data_pagamento 
            FROM `plr_produtividades` prlp 
            INNER JOIN `plr_periodos` pp ON pp.id_plr_periodo = prlp.id_plr_periodo 
            ORDER BY prlp.id_plr_periodo DESC, prlp.data_inicial_sub_per ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <tr class='atencao' align="center">
        <td colspan='4'>
            N�O H� PRODU��O(�ES) MENSAL(IS) CADASTRADA(S).
        </td>
    </tr>
<?
    }else {
//Busca o �ltimo ID da Tabela para fazer uma compara��o mais abaixo ...
        $sql = "SELECT id_plr_produtividade as id_plr_produtividade_maior 
                FROM `plr_produtividades` 
                ORDER BY id_plr_produtividade DESC LIMIT 1 ";
        $campos_id_produtividade = bancos::sql($sql);
        $id_plr_produtividade_maior = $campos_id_produtividade[0]['id_plr_produtividade_maior'];
        $id_plr_periodo_anterior = '';
        for($i = 0; $i < $linhas; $i++) {
/*Aqui eu verifico se o Per�odo Anterior � Diferente do Per�odo Atual que est� sendo listado
no loop, se for ent�o eu atribuo o Per�odo Atual p/ o Per�odo Anterior ...*/
            if($id_plr_periodo_anterior != $campos[$i]['id_plr_periodo']) {
                    $id_plr_periodo_anterior = $campos[$i]['id_plr_periodo'];
?>
    <tr class="linhadestaque">
        <td colspan='4'>
            <font color="yellow">
                <b>Per�odo: </b>
            </font>
            <?=$campos[$i]['periodo'];?>
        </td>
    </tr>
    <tr class="linhanormal" align="center">
        <td bgcolor="#CCCCCC">
            <b>Data Inicial Sub-Per�odo</b>
        </td>
        <td bgcolor="#CCCCCC">
            <b>Data Final Sub-Per�odo</b>
        </td>
        <td bgcolor="#CCCCCC">
            <b>Albaf�r + Tool</b>
        </td>
        <td width="30" bgcolor="#CCCCCC">
            &nbsp;
        </td>
    </tr>
<?
            }
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="right">
        <td>
            <?=data::datetodata($campos[$i]['data_inicial_sub_per'], '/');?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_final_sub_per'], '/');?>
        </td>
        <td>
            <?='R$ '.number_format($campos[$i]['albafer_tool'], 2, ',', '.');?>
        </td>
        <td>
        <?
//S� posso estar excluindo o �ltimo registro ...
            if($id_plr_produtividade_maior == $campos[$i]['id_plr_produtividade']) {
                $data_atual = date('Y-m-d');
//Se a Data Atual for maior do que a Data de Pagamento, ent�o eu ignoro esse trecho de c�digo
                if($data_atual > $campos[$i]['data_pagamento']) {
                        echo '&nbsp';
                }else {
        ?>
            <img src="../../../../imagem/menu/excluir.png" border='0' onClick="excluir_item('<?=$campos[$i]['id_plr_produtividade'];?>')" alt="Excluir Produ��o Mensal" title="Excluir Produ��o Mensal">
        <?
                }
            }
        ?>
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