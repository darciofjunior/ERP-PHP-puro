<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral('/erp/albafer/modulo/faturamento/especificacao/opcoes.php', '../../../');

if($passo == 1) {
//Aqui eu verifico se essa EspecificaÁ„o que est· sendo cadastrada j· existe na Base de Dados ...
    $sql = "SELECT id_especificacao 
            FROM `especificacoes` 
            WHERE `especificacao` = '$_POST[txt_especificacao]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//N„o encontrou nenhum, pode inserir no BD ...
        $sql = "INSERT INTO `especificacoes` (`id_especificacao`, `especificacao`) values (null, '$_POST[txt_especificacao]') ";
        bancos::sql($sql);
        $valor = 1;
    }else {//EspecificaÁ„o j· existente
        $valor = 2;
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'opcoes.php?valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<title>.:: Incluir EspecificaÁ„o ::.</title>
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
<body onload="document.form.txt_especificacao.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<table width='50%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir EspecificaÁ„o
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>EspecificaÁ„o:</b>
        </td>
        <td>
            <input type='text' name='txt_especificacao' title="Digite a EspecificaÁ„o" size="40" maxlength="55" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'opcoes.php'" class="botao">
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="redefinir('document.form', 'LIMPAR');document.form.txt_especificacao.focus()" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>