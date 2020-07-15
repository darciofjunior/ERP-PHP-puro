<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>BANCO ALTERADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>BANCO JÁ EXISTE.</font>";

if($passo == 1) {
    //Aqui eu busco dados do id_banco passado por parâmetro ...
    $sql = "SELECT `banco`, `pagweb` 
            FROM `bancos` 
            WHERE `id_banco` = '$_GET[id_banco]' 
            AND `ativo` = '1' ";
    $campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Banco(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Banco ...
    if(!texto('form', 'txt_banco', '2', "-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,.Üüáé§íóúÁÉÍÀ'àºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ", 'BANCO', '2')) {
        return false
    }
//Pagina Web ...
    if(!texto('form', 'txt_pagina_web', '5', "-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,.Üüáé§íóúÁÉÍÀ'àºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ", 'PÁGINA WEB', '1')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_banco.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<input type='hidden' name='hdd_banco' value='<?=$_GET['id_banco'];?>'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
<tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Banco
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Banco:</b>
        </td>
        <td>
            <input type='text' name='txt_banco' value='<?=$campos[0]['banco'];?>' title='Digite o Banco' size='35' maxlength='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>P&aacute;gina Web:</b>
        </td>
        <td>
            <input type='text' name='txt_pagina_web' value='<?=$campos[0]['pagweb'];?>' title='Digite a Página Web' size='85' maxlength='80' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_banco.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 2) {
    //Aqui eu verifico se existe algum outro banco com o mesmo nome do Banco Corrente ...
    $sql = "SELECT `id_banco` 
            FROM `bancos` 
            WHERE `banco` = '$_POST[txt_banco]' 
            AND `id_banco` <> '$_POST[hdd_banco]' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Não existe ...
        $sql = "UPDATE `bancos` SET `banco` = '$_POST[txt_banco]', `pagweb` = '$_POST[txt_pagina_web]' WHERE `id_banco` = '$_POST[hdd_banco]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Já existe, sendo assim não posso estar fazendo nenhuma alteração ...
        $valor = 2;
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar.php?valor=<?=$valor;?>'
    </Script>
<?
}else {
    //Aqui eu busco todos os Bancos cadastrados no sistema ...
    $sql = "SELECT * 
            FROM `bancos` 
            WHERE `ativo` = '1' ORDER BY `banco` ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = '../../../html/index.php?valor=3'
        </Script>
<?
        exit;
    }
?>
<html>
<head>
<title>.:: Alterar Banco(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Alterar Banco(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Banco
        </td>
        <td>
            Página Web
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
        $url = "window.location = 'alterar.php?passo=1&id_banco=".$campos[$i]['id_banco']."'";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td onclick="<?=$url;?>" width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="<?=$url;?>">
            <a href='#' class='link'>
                <?=$campos[$i]['banco'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['pagweb'];?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align="center">
        <td colspan='3'>
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