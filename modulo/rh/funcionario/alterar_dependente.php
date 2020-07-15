<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/rh/funcionario/alterar.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>DEPENDENTE ALTERADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>DEPENDENTE J¡ EXISTENTE.</font>";

if(!empty($_POST['txt_nome'])) {
    //Tratamento com os campos de Data p/ poder gravar no Banco de Dados ...
    $data_nascimento = data::datatodate($_POST['txt_data_nascimento'], '-');
    
    /*Verifico se tem um outro "dependente" que possui o mesmo nome e Data de Nascimento para esse Funcion·rio diferente do registro atual 
    que est· sendo alterado ...*/
    $sql = "SELECT `id_dependente` 
            FROM `dependentes` 
            WHERE `id_funcionario` = '$_POST[id_funcionario_loop]' 
            AND `nome` = '$_POST[txt_nome]' 
            AND `data_nascimento` = '$data_nascimento' 
            AND `id_dependente` <> '$_POST[id_dependente]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Dependente n„o existente ...
        $sql = "UPDATE `dependentes` SET `nome` = '$_POST[txt_nome]', `data_nascimento` = '$data_nascimento' WHERE `id_dependente` = '$_POST[id_dependente]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Dependente j· existente ...
        $valor = 2;
    }
}

$id_dependente = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_dependente'] : $_GET['id_dependente'];

//Busca dados do Dependente do Funcion·rio que foi passado por par‚metro ...
$sql = "SELECT `id_funcionario`, `nome`, DATE_FORMAT(`data_nascimento`, '%d/%m/%Y') AS data_nascimento 
        FROM `dependentes` 
        WHERE `id_dependente` = '$id_dependente' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<title>.:: Alterar Dependente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Nome ...
    if(!texto('form', 'txt_nome', '3', 'qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.HGFDSAZXCVBNM‹¸·ÈßÌÛ˙¡…Õ¿‡”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’ ', 'NOME', '2')) {
        return false
    }
//Data de Nascimento ...
    if(!data('form', 'txt_data_nascimento', '4000', 'NASCIMENTO')) {
        return false
    }
//Aqui È para n„o atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que sÛ atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.opener.document.form.passo.value = 0
        window.opener.document.form.submit()
    }
}
</Script>
</head>
<body onload='document.form.txt_nome.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--****************************Controles de Tela****************************-->
<input type='hidden' name='id_dependente' value='<?=$id_dependente;?>'>
<input type='hidden' name='id_funcionario_loop' value='<?=$campos[0]['id_funcionario'];?>'>
<input type='hidden' name='nao_atualizar'>
<!--*************************************************************************-->
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Dependente(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Nome:</b>
        </td>
        <td>
            <input type='text' name='txt_nome' value='<?=$campos[0]['nome'];?>' title='Digite o Nome' size='52' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Nascimento:</b>
        </td>
        <td>
            <input type='text' name='txt_data_nascimento' value='<?=$campos[0]['data_nascimento'];?>' title='Digite a Data de Nascimento' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            &nbsp; <img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_nascimento&tipo_retorno=1', 'CALEND¡RIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_nome.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>