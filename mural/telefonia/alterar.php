<?
require('../../lib/segurancas.php');
session_start('fucionarios');
//segurancas::geral($PHP_SELF, '../../');

$mensagem[1] = 'TELEFONE ALTERADO COM SUCESSO !';
$mensagem[2] = 'TELEFONE J¡ EXISTENTE !';

if(!empty($_POST['txt_nome'])) {
    //Aqui eu verifico se j· existe esse nome e departamento e ramal cadastrados ...
    $sql = "SELECT id_telefone 
            FROM `telefones` 
            WHERE `nome` = '$_POST[txt_nome]' 
            AND `departamento` = '$_POST[txt_departamento]' 
            AND `ramal` = '$_POST[txt_ramal]' 
            AND `id_telefone` <> '$_POST[hdd_telefone]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) {//J· existe um telefone dessa maneira ...
        $valor = 2;
    }else {//N„o encontrou esse telefone cadastrado, sendo assim posso cad. normalmente ...
        //Atualizando a Base de Dados ...
        $sql = "UPDATE `telefones` SET `nome` = '$_POST[txt_nome]', `departamento` = '$_POST[txt_departamento]', `ramal` = '$_POST[txt_ramal]' WHERE `id_telefone` = '$_POST[hdd_telefone]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }
?>
    <Script Language = 'JavaScript'>
        alert('<?=$mensagem[$valor];?>')
        window.location = 'telefonia.php'
    </Script>
<?
}
	
//Busco dados do Telefone passado por par‚metro ...
$sql = "SELECT * 
        FROM `telefones` 
        WHERE `id_telefone` = '$_GET[id_telefone]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Telefone ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Nome ...
    if(!texto('form', 'txt_nome', '1', '„ı√’Á«·ÈÌÛ˙¡…Õ”⁄‚ÍÓÙ˚¬ Œ‘€-abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_./ ', 'NOME', '2')) {
        return false
    }
//Departamento ...
    if(!texto('form', 'txt_departamento', '1', '„ı√’Á«·ÈÌÛ˙¡…Õ”⁄‚ÍÓÙ˚¬ Œ‘€-abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_. ', 'DEPARTAMENTO', '2')) {
        return false
    }
//Ramal ...
    if(!texto('form', 'txt_ramal', '1', '0123456789', 'RAMAL', '2')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_nome.focus()'>
<form name='form' method='post' action='' onSubmit='return validar()'>
<input type='hidden' name='hdd_telefone' value='<?=$_GET['id_telefone'];?>'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Telefone
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Nome:</b>
        </td>
        <td>
            <input type='text' name="txt_nome" value="<?=$campos[0]['nome'];?>" title='Digite o Nome' size='32' maxlength='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Departamento:</b>
        </td>
        <td>
            <input type='text' name="txt_departamento" value="<?=$campos[0]['departamento'];?>" title="Digite o Departamento" size="30" maxlength="20" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Ramal:</b>
        </td>
        <td>
            <input type='text' name="txt_ramal" value="<?=$campos[0]['ramal'];?>" title="Digite o Ramal" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size='6' maxlength='5' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'telefonia.php'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_nome.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>