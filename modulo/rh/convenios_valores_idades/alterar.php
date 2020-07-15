<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral('/erp/albafer/modulo/rh/convenios_valores_idades/convenios_valores_idades.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>CONVÊNIO VALOR VS IDADE ALTERADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>CONVÊNIO VALOR VS IDADE JÁ EXISTENTE.</font>";

if(!empty($_POST['txt_convenio_valor'])) {
    //Verifico se tem um outro "convenios_valores_vs_idades" que possui o mesmo valor de convênio e idade diferente do atual que está sendo alterado ...
    $sql = "SELECT `id_convenio_valor_idade` 
            FROM `convenios_valores_vs_idades` 
            WHERE `convenio_valor` = '$_POST[txt_convenio_valor]' 
            AND `idade` = '$_POST[txt_idade]' 
            AND `id_convenio_valor_idade` <> '$_POST[id_convenio_valor_idade]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Convênio Valor vs Idade não existente ...
        $sql = "UPDATE `convenios_valores_vs_idades` SET `convenio_valor` = '$_POST[txt_convenio_valor]', `idade` = '$_POST[txt_idade]' WHERE `id_convenio_valor_idade` = '$_POST[id_convenio_valor_idade]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Convênio Valor vs Idade já existente ...
        $valor = 2;
    }
}

$id_convenio_valor_idade = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_convenio_valor_idade'] : $_GET['id_convenio_valor_idade'];

//Aqui eu trago dados do Desconto Cliente passado por parâmetro ...
$sql = "SELECT * 
        FROM `convenios_valores_vs_idades` 
        WHERE `id_convenio_valor_idade` = '$id_convenio_valor_idade' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<title>.:: Alterar Convênio Valor(es) vs Idade(s) ::.</title>
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
<input type='hidden' name='id_convenio_valor_idade' value='<?=$id_convenio_valor_idade;?>'>
<table width='50%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Convênio Valor(es) vs Idade(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Convênio Valor:</b>
        </td>
        <td>
            <input type='text' name='txt_convenio_valor' value='<?=number_format($campos[0]['convenio_valor'], 2, ',', '.');?>' title='Digite o Convênio Valor' size='12' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Idade < :</b>
        </td>
        <td>
            <input type='text' name='txt_idade' value='<?=$campos[0]['idade'];?>' title='Digite a Idade menor que' size='3' maxlength='3' onkeyup="verifica(this, 'aceita', 'numeros', '', event); if(this.value == 0) {this.value = ''}" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'convenios_valores_idades.php'" class='botao'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_convenio_valor.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>