<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>ESPECIFICA��O INCLUIDA COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>ESPECIFICA��O J� EXISTENTE.</font>";
$mensagem[3] = "<font class='confirmacao'>ESPECIFICA��O EXCLU�DA COM SUCESSO.</font>";

if(!empty($_POST['hdd_especificacao'])) {//Exclus�o da Especifica��o ...
    $sql = "DELETE FROM `especificacoes` WHERE `id_especificacao` = '$_POST[hdd_especificacao]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 3;
}
?>
<html>
<head>
<title>.:: Tabela de Especifica��o ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function excluir_item(id_especificacao) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
    if(mensagem == false) {
        return false
    }else {
        document.form.hdd_especificacao.value = id_especificacao
        document.form.submit()
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action=''>
<input type='hidden' name='hdd_especificacao'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='atencao' align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Tabela de Especifica��o
        </td>
    </tr>
<?
//Aqui eu busco todas as Especifica��es cadastradas no Banco de Dados ...
    $sql = "SELECT * 
            FROM `especificacoes` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='4'>
            N�O H� ESPECIFICA��O(�ES) CADASTRADA(S).
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <b>C�digo</b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Especifica��o</b>
        </td>
        <td width='30' bgcolor='#CCCCCC'>
            &nbsp;
        </td>
        <td width='30' bgcolor='#CCCCCC'>
            &nbsp;
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas ; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='right'>
        <td align='center'>
            <?=$campos[$i]['id_especificacao'];?>
        </td>
        <td>
            <?=$campos[$i]['especificacao'];?>
        </td>
        <td>
            <img src='../../../imagem/menu/alterar.png' border='0' onclick="window.location = 'alterar.php?id_especificacao=<?=$campos[$i]['id_especificacao'];?>'" alt="Alterar Especifica��o" title="Alterar Especifica��o">
        </td>
        <td>
            <img src='../../../imagem/menu/excluir.png' border='0' onclick="excluir_item('<?=$campos[$i]['id_especificacao'];?>')" alt="Excluir Especifica��o" title="Excluir Especifica��o">
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhadestaque'>
        <td colspan='4'>
            <a href='incluir.php' title='Incluir Especifica��o'>
                <font color='#FFFF00'>
                    Incluir Especifica��o
                </font>
            </a>
        </td>
    </tr>
</table>
</form>
</body>
</html>