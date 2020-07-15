<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='confirmacao'>C�DIGO(S) DE M�QUINA EXCLU�DO COM SUCESSO.</font>";

if($passo == 1) {
    if(!empty($_GET['id_maquina_codigo_maquina'])) {//Exclus�o do C�digo de M�quina da M�quina ...
        $sql = "DELETE FROM `maquinas_vs_codigos_maquinas` WHERE `id_maquina_codigo_maquina` = '$_GET[id_maquina_codigo_maquina]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }
?>
<html>
<head>
<title>.:: M�quina(s) para Gerenciar C�digo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function excluir_item(id_maquina_codigo_maquina) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
    if(mensagem == true) window.location = '<?=$PHP_SELF;?>?passo=1&id_maquina=<?=$_GET['id_maquina'];?>&id_maquina_codigo_maquina='+id_maquina_codigo_maquina
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            C�digo(s) da M�quina: 
            <font color='yellow'>
            <?
                $sql = "SELECT `nome` 
                        FROM `maquinas` 
                        WHERE `id_maquina` = '$_GET[id_maquina]' LIMIT 1 ";
                $campos = bancos::sql($sql);
                echo $campos[0]['nome'];
            ?>
            </font>
        </td>
    </tr>
<?
    //Aqui vasculha todos os C�digos de M�quina atrelado para esta m�quina ...
    $sql = "SELECT * 
            FROM `maquinas_vs_codigos_maquinas` 
            WHERE `id_maquina` = '$_GET[id_maquina]' ORDER BY `codigo_maquina` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='3'>
            N�O H� C�DIGO(S) CADASTRADO(S) PARA ESTA M�QUINA.
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhadestaque' align='center'>
        <td>
            C�digo da M�quina
        </td>
        <td width='30'>&nbsp;</td>
        <td width='30'>&nbsp;</td>
    </tr>
<?
        for($i = 0; $i < $linhas ; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['codigo_maquina'];?>
        </td>
        <td>
            <img src = '../../../../imagem/menu/alterar.png' border='0' onclick="window.location = 'alterar_codigo_maquina.php?id_maquina_codigo_maquina=<?=$campos[$i]['id_maquina_codigo_maquina'];?>'" alt='Alterar C�digo da M�quina' title='Alterar C�digo da M�quina'>
        </td>
        <td>
            <img src = '../../../../imagem/menu/excluir.png' border='0' onclick="excluir_item('<?=$campos[$i]['id_maquina_codigo_maquina'];?>')" alt='Excluir C�digo da M�quina' title='Excluir C�digo da M�quina'>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhadestaque' align="left">
        <td colspan='3'>
            <a href = 'incluir_codigo_maquina.php?id_maquina=<?=$_GET['id_maquina'];?>' title='Incluir C�digo(s) de M�quina'>
                <font color="#FFFF00">
                    Incluir C�digo(s) de M�quina
                </font>
            </a>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'maquina_vs_codigo_maquina.php'" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else {
?>
<html>
<head>
<title>.:: M�quina(s) para Gerenciar C�digo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<body>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            M�quina(s) para Gerenciar C�digo(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            M�quina
        </td>
        <td>
            Valor
        </td>
        <td>
            M�q. por Func.
        </td>
        <td>
            Anos p/ Amort.
        </td>
        <td>
            Porc. Ferr.
        </td>
        <td>
            Sal. M�dio M�q.
        </td>
        <td>
            Custo Hora M�q.
        </td>
    </tr>
<?
    //Aqui eu fa�o uma listagem de todas as m�quinas da F�brica que est�o cadastradas no ERP ...
    $sql = "SELECT * 
            FROM `maquinas` 
            WHERE `ativo` = '1' ORDER BY `nome` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="window.location = 'maquina_vs_codigo_maquina.php?passo=1&id_maquina=<?=$campos[$i]['id_maquina'];?>'" align='center'>
        <td width='10'>
            <a href='#' class='link'>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='left'>
            <a href='#' class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td align='right'>
            <?='R$ '.number_format($campos[$i]['valor'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde_maq_vs_func'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['duracao'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['porc_ferramental'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?='R$ '.number_format($campos[$i]['salario_medio'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?='R$ '.number_format($campos[$i]['custo_h_maquina'], 2, ',', '.');?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            &nbsp;
        </td>
    </tr>
</table>
</body>
</html>
<?}?>