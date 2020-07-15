<?
require('../lib/segurancas.php');
//segurancas::geral($PHP_SELF, '../');
session_start('funcionarios');

if(!empty($_POST['txt_titulo'])) {//Atualiza a Mensagem ...
    $sql = "UPDATE `helps` SET `titulo` = '$_POST[txt_titulo]', `mensagem` = '$_POST[txt_mensagem]' WHERE `id_help` = '$_POST[id_help]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        window.location = 'visualizar.php?id_help=<?=$_POST['id_help'];?>&valor=1'
    </Script>
<?
}

//Busca a ajuda do id passado por parâmetro
$sql = "SELECT * 
        FROM `helps` 
        WHERE `id_help` = '$_GET[id_help]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Ajuda ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Título
    if(!texto('form', 'txt_titulo', '3', '1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJHGFDSAZXCVBNM,.ÜüáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛãõÃÕ.,%&*$()@#<>ªº°Ø:; ', 'TÍTULO', '2')) {
        return false
    }
//Mensagem
    if(document.form.txt_mensagem.value == '') {
        alert('DIGITE A MENSAGEM !')
        document.form.txt_mensagem.focus()
        return false
    }
}
</Script>
</head>
<body onload="document.form.txt_titulo.focus()">
<form name="form" method="post" action='' onSubmit="return validar()">
<input type='hidden' name='id_help' value='<?=$_GET['id_help'];?>'>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Ajuda
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Título:</b>
        </td>
        <td>
            <input type="text" name="txt_titulo" value="<?=$campos[0]['titulo'];?>" title="Digite o Título" maxlength="55" size="40" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Mensagem:</b>
        </td>
        <td>
            <textarea name='txt_mensagem' cols='85' rows='3' title="Digite a Mensagem" class='caixadetexto'><?=$campos[0]['mensagem'];?></textarea>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            <input type="button" name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'visualizar.php?id_help=<?=$_GET['id_help'];?>'" class="botao">
            <input type="button" name="cmd_Redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_titulo.focus()" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_Salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<font color='red'><b>Legenda:</b></font>

<b>b -> Negrito;</b>
<b>i -> Itálico;</b>
<b>u -> Sublinhado;</b>
<b>br -> Pula Linha</b>
<b>p -> Pula 2 Linhas</b>
<b>font color='cor_desejada' -> Colorir</b>

<font color='blue'><b>Observação:</b></font>

Esses comandos tem q estar entre sinal de maior e menor <>
</pre>