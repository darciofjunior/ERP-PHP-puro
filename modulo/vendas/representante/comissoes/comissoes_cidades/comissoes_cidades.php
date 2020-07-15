<?
require('../../../../../lib/segurancas.php');
if(empty($_GET['pop_up'])) {//Significa que essa Tela abriu de forma normal, n�o como sendo Pop-UP ...
    require '../../../../../lib/menu/menu.php';
    segurancas::geral($PHP_SELF, '../../../../../');
}
$mensagem[1] = '<font class="confirmacao">COMISS�O POR CIDADE EXCLU�DA COM SUCESSO.</font>';

if(!empty($_POST['id_comissao_cidade'])) {//Exclus�o das Comiss�es por Cidade
    $sql = "DELETE FROM `comissoes_cidades` WHERE `id_comissao_cidade` = '$_POST[id_comissao_cidade]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}
?>
<html>
<head>
<title>.:: Cidades que pagamos Comiss�o igual Cidade de SP ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function excluir_item(id_comissao_cidade) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
    if(mensagem == false) {
        return false
    }else {
        document.form.id_comissao_cidade.value = id_comissao_cidade
        document.form.submit()
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action=''>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='atencao' align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Cidades que pagamos Comiss�o igual Cidade de SP
        </td>
    </tr>
<?
//Aqui vasculha todas as Comiss�es por Cidade
    $sql = "SELECT * 
            FROM `comissoes_cidades` 
            ORDER BY comissao_cidade ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='2'>
            N�O H� COMISS�O(�ES) POR CIDADE(S) CADASTRADAS.
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <b>Cidade</b>
        </td>
        <td width='30' bgcolor='#CCCCCC'>
            &nbsp;
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas ; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <?=$campos[$i]['comissao_cidade'];?>
        </td>
        <td>
        <?
            //S� exibo esse link, quando essa Tela n�o foi aberta como sendo Pop-UP ...
            if(empty($_GET['pop_up'])) {//N�o � Pop-UP ...
        ?>
            <img src = '../../../../../imagem/menu/excluir.png' border='0' onclick="excluir_item('<?=$campos[$i]['id_comissao_cidade'];?>')" alt='Excluir Comiss�o' title='Excluir Comiss�o'>
        <?
            }
        ?>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhadestaque'>
        <td colspan='2'>
        <?
            //S� exibo esse link, quando essa Tela n�o foi aberta como sendo Pop-UP ...
            if(empty($_GET['pop_up'])) {//N�o � Pop-UP ...
        ?>
            <a href='incluir_comissao_cidade.php' title='Incluir Comiss�o por Cidade'>
                <font color='#FFFF00'>
                    Incluir Comiss�o por Cidade
                </font>
            </a>
        <?
            }else {//� Pop-Up ...
                echo '&nbsp;';
            }
        ?>
        </td>
    </tr>
</table>
</form>
</body>
</html>