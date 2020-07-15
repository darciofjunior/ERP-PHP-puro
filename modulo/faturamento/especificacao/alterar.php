<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
session_start('funcionarios');
segurancas::geral('/erp/albafer/modulo/faturamento/especificacao/opcoes.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>ESPECIFICA«√O ALTERADA COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>ESPECIFICA«√O J¡ EXISTENTE.</font>";

if($passo == 1) {
/*Verifico se existe uma outra EspecificaÁ„o com o mesmo nome dessa Atual que est· sendo alterada 
na Base de Dados ...*/
    $sql = "SELECT id_especificacao 
            FROM `especificacoes` 
            WHERE `especificacao` = '$_POST[txt_especificacao]' 
            AND `id_especificacao` <> '$_POST[hdd_especificacao]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//N„o encontrou nenhum, pode inserir no BD ...
        $sql = "UPDATE `especificacoes` SET `especificacao` = '$_POST[txt_especificacao]' WHERE `id_especificacao` = '$_POST[hdd_especificacao]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else {//EspecificaÁ„o j· existente
        $valor = 2;
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'alterar.php?id_especificacao=<?=$_POST['hdd_especificacao'];?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
//Busca os dados da EspecificaÁ„o passada por par‚metro ...
    $sql = "SELECT especificacao 
            FROM `especificacoes` 
            WHERE `id_especificacao` = '$_GET[id_especificacao]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<title>.:: Alterar EspecificaÁ„o ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//EspecificaÁ„o ...
    if(!texto('form', 'txt_especificacao', '1', '∫∞∫™abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ·ÈÌÛ˙¡…Õ”⁄„ı√’‚ÍÓÙ˚¬ Œ‘€‡¿()/_Á«.-0123456789 ', 'ESPECIFICA«√O', '1')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_especificacao.focus()'>
<form name='form' method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='hdd_especificacao' value="<?=$_GET['id_especificacao'];?>">
<table width='50%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='atencao' align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar EspecificaÁ„o
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>EspecificaÁ„o:</b>
        </td>
        <td>
            <input type="text" name="txt_especificacao" value="<?=$campos[0]['especificacao'];?>" title="Digite a EspecificaÁ„o" size="40" maxlength="55" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'opcoes.php'" class="botao">
            <input type="reset" name="cmd_redefinir" value="Redefinir" title="Redefinir" style="color:#ff9900;" onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_especificacao.focus()" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>