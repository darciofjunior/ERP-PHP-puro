<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = '<font class="confirmacao">TIPO DE MOEDA ALTERADA COM SUCESSO. </font>';
$mensagem[2] = '<font class="erro">TIPO DE MOEDA JÁ EXISTENTE </font>';

if($passo == 1) {
//Trago Dados da Moeda passada por parâmetro ...
    $sql = "SELECT * 
            FROM `tipos_moedas` 
            WHERE `id_tipo_moeda` = '$_GET[id_tipo_moeda]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Tipo de Moeda ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Moeda
    if(!texto('form', 'txt_moeda', '2', "qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPÇLKJHGFDSAZXCVBNMáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛàÀãÃÕ1234567890,..', !@#$%¨&*()-_=§¹²³£¢/¬", 'MOEDA', '1')) {
        return false
    }
//Símbolo
    if(!texto('form', 'txt_simbolo', '1', "qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPÇLKJHGFDSAZXCVBNMáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛàÀãÃÕ1234567890,.€.', !@#$%¨&*/()-_=§¹²³£¢¬", 'SÍMBOLO DA MOEDA', '2')) {
        return false
    }
//Origem
    if(!texto('form', 'txt_origem', '2', "-=€!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,.Üüáé§íóúÁÉÍÀ'àºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ", 'ORIGEM', '1')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_moeda.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<input type='hidden' name='hdd_tipo_moeda' value='<?=$_GET['id_tipo_moeda'];?>'>
<table width='60%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Tipo da Moeda
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Moeda:</b>
        </td>
        <td>
            <input type='text' name='txt_moeda' value='<?=$campos[0]['moeda'];?>' title='Digite a Moeda' size='35' maxlength='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>S&iacute;mbolo:</b>
        </td>
        <td>
            <input type='text' name='txt_simbolo' value='<?=$campos[0]['simbolo'];?>' title='Digite o S&iacute;mbolo' size='5' maxlength='3' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Origem:</b>
        </td>
        <td>
            <input type='text' name='txt_origem' value='<?=$campos[0]['origem'];?>' title='Digite a Origem' size='35' maxlength='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Descri&ccedil;&atilde;o:
        </td>
        <td>
            <textarea name='txt_descricao' title='Digite a Descri&ccedil;&atilde;o' rows='1' cols='80' maxlength='80' class='caixadetexto'><?=$campos[0]['descricao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_moeda.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 2) {
//Verifico se existe um Tipo de Moeda com o mesmo "Nome" da que está sendo alterada pelo usuário ...
    $sql = "SELECT `moeda` 
            FROM `tipos_moedas` 
            WHERE `moeda` = '$_POST[txt_moeda]' 
            AND `ativo` = '1' 
            AND `id_tipo_moeda` <> '$_POST[hdd_tipo_moeda]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Não existe ...
        $sql = "UPDATE `tipos_moedas` SET `moeda` = '$_POST[txt_moeda]', `simbolo` = '$_POST[txt_simbolo]', `origem` = '$_POST[txt_origem]', `descricao` = '$_POST[txt_descricao]' WHERE `id_tipo_moeda` = '$_POST[hdd_tipo_moeda]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Já existe ...
        $valor = 2;
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'alterar.php<?=$parametro;?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
//Busca todos os Tipos de Moedas que estão cadastradas no Sistema ...
    $sql = "SELECT * 
            FROM `tipos_moedas` 
            WHERE `ativo` = '1' ORDER BY `moeda` ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = '../../../html/index.php?valor=3'
    </Script>
<?
        exit;
    }
?>
<html>
<head>
<head>
<title>.:: Alterar Tipo de Moeda ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Alterar Tipo de Moeda
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Moeda
        </td>
        <td>
            Símbolo
        </td>
        <td>
            Origem
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');window.location = 'alterar.php?passo=1&id_tipo_moeda=<?=$campos[$i]['id_tipo_moeda'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href='#'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href='#' class='link'>
                <?=$campos[$i]['moeda'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['simbolo'];?>
        </td>
        <td>
            <?=$campos[$i]['origem'];?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho'>
        <td colspan='4'>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>