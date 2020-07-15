<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>CARGO ALTERADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>CARGO JÁ EXISTENTE.</font>";

if($passo == 1) {
    $sql = "SELECT * 
            FROM `cargos` 
            WHERE `id_cargo` = '$id_cargo' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Cargo(s) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Cargo ...
    if(!texto('form', 'txt_cargo', '3', '0123456789ãõÃÕáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛabcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZçÇ _-/', 'CARGO', '2')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_cargo.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<input type='hidden' name='hdd_cargo' value='<?=$_GET['id_cargo']?>'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Cargo(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Cargo:</b>
        </td>
        <td>
            <input type='text' name='txt_cargo' value='<?=$campos[0]['cargo'];?>' title='Digite o Cargo' size='20' maxlength='25' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_cargo.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 2) {
    $sql = "SELECT `id_cargo` 
            FROM `cargos` 
            WHERE `cargo` = '$_POST[txt_cargo]' 
            AND `id_cargo` <> '$_POST[hdd_cargo]' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 1) {
        $valor = 2;
    }else {
        $sql = "UPDATE `cargos` SET `cargo` = '$_POST[txt_cargo]' WHERE `id_cargo` = '$_POST[hdd_cargo]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar.php<?=$parametro;?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
    $sql = "SELECT * 
            FROM `cargos` 
            WHERE `ativo` = '1' ORDER BY cargo ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = '../../../html/index.php?valor=3'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Alterar Cargo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='5'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Alterar Cargo(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Cargo
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');window.location = 'alterar.php?passo=1&id_cargo=<?=$campos[$i]['id_cargo'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10'>
            <a href = 'alterar.php?passo=1&id_cargo=<?=$campos[$i]['id_cargo'];?>'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'></a>
        </td>
        <td align='left'>
            <?=$campos[$i]['cargo'];?>
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
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}
?>