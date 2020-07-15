<?
require('../../../lib/segurancas.php');
session_start('funcionarios');

$mensagem[1] = '<font class="confirmacao">SA&Iacute;DA DE CAIXA DE COMPRA EXCLU&Iacute;DO COM SUCESSO.</font>';

/*********************************************************************************************/
if(!empty($_POST['id_caixa_compra'])) {//Exclusão das Faixa(s) de Desconto(s) do Cliente
    $sql = "DELETE FROM `caixas_compras` WHERE `id_caixa_compra` = '$_POST[id_caixa_compra]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}
/*********************************************************************************************/

/******************************************************************************************/
/*********************************Lista somente as Saídas**********************************/
/******************************************************************************************/
/*Aqui eu listo as Entradas e Saídas que estão gravadas na tabela de "Caixa de Compras" no Período filtrado 
pelo Usuário c/ a Marcação que foi feita pelos Compradores no Cabeçalho de NF "Pago pelo Caixa Compras" ...*/
$sql = "SELECT cc.`id_caixa_compra`, cc.`valor_debito`, cc.`valor_credito`, 
        DATE_FORMAT(cc.`data_emissao`, '%d/%m/%Y') AS data_emissao, cc.observacao, f.`nome` 
        FROM `caixas_compras` cc 
        INNER JOIN `funcionarios` f ON f.`id_funcionario` = cc.`id_funcionario` 
        WHERE cc.`valor_debito` > 0 ORDER BY cc.`data_emissao` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas > 0) {
?>
<html>
<head>
<title>.:: Saída Caixa de Compra(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<table width='100%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='5'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Sa&iacute;da(s) do Caixa de Compra(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            &nbsp;
        </td>
        <td>
            Data de Emiss&atilde;o
        </td>
        <td>
            Funcion&atilde;rio
        </td>
        <td>
            Observa&ccedil;o
        </td>
        <td>
            D&eacute;bito
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?
                //Esse excluir só aparece p/ a Gladys 14, Roberto 62, Fábio Petroni 64 e Dárcio 98 porque programa ...
                if($_SESSION['id_funcionario'] == 14 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 64 || $_SESSION['id_funcionario'] == 98) {
            ?>
            <img src = '../../../../imagem/menu/excluir.png' border='0' onclick="excluir_saida_caixa_compras('<?=$campos[$i]['id_caixa_compra'];?>')" alt='Excluir' title='Excluir'>
            <?
                }else {
                    echo '-';
                }
            ?>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td align='left'>
            <?=utf8_encode($campos[$i]['observacao']);?>
        </td>
        <td align='right'>
        <?
            if($campos[$i]['valor_debito'] > 0) echo 'R$ '.number_format($campos[$i]['valor_debito'], 2, ',', '.');
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            &nbsp;
        </td>
    </tr>
</table>
</body>
</html>
<?}?>