<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral('/erp/albafer/modulo/rh/convenios_valores_idades/convenios_valores_idades.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>CONVÊNIO VALOR VS IDADE INCLUÍDO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>CONVÊNIO VALOR VS IDADE JÁ EXISTENTE.</font>";

if(!empty($_POST['txt_convenio_valor'])) {
    //Verifico se tem um outro "convenios_valores_vs_idades" que possui o mesmo valor de convênio e idade ...
    $sql = "SELECT `id_convenio_valor_idade` 
            FROM `convenios_valores_vs_idades` 
            WHERE `convenio_valor` = '$_POST[txt_convenio_valor]' 
            AND `idade` = '$_POST[txt_idade]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Convênio Valor vs Idade não existente ...
        $sql = "INSERT INTO `convenios_valores_vs_idades` (`id_convenio_valor_idade`, `convenio_valor`, `idade`) VALUES (NULL, '$_POST[txt_convenio_valor]', '$_POST[txt_idade]') ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Convênio Valor vs Idade já existente ...
        $valor = 2;
    }
}
?>
<html>
<title>.:: Incluir Convênio Valor(es) vs Idade(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Convênio Valor ...
    if(!texto('form', 'txt_convenio_valor', '3', '1234567890,.', 'CONVÊNIO VALOR', '2')) {
        return false
    }
//Idade ...
    if(!texto('form', 'txt_idade', '1', '1234567890,', 'IDADE', '1')) {
        return false
    }
    return limpeza_moeda('form', 'txt_convenio_valor, ')
}
</Script>
</head>
<body onload='document.form.txt_convenio_valor.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='50%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Convênio Valor(es) vs Idade(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Convênio Valor:</b>
        </td>
        <td>
            <input type='text' name='txt_convenio_valor' title='Digite o Convênio Valor' size='12' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Idade < :</b>
        </td>
        <td>
            <input type='text' name='txt_idade' title='Digite a Idade menor que' size='3' maxlength='3' onkeyup="verifica(this, 'aceita', 'numeros', '', event); if(this.value == 0) {this.value = ''}" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'convenios_valores_idades.php'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_convenio_valor.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>