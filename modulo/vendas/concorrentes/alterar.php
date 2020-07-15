<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/concorrentes/concorrentes.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>CONCORRENTE ALTERADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>CONCORRENTE J¡ EXISTENTE.</font>";

//Tratamento com as vari·veis que vem por par‚metro ...
$id_concorrente = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_concorrente'] : $_GET['id_concorrente'];

if(!empty($_POST['txt_nome'])) {
    //Verifico se existe um outro Concorrente com o mesmo nome diferente do atual que foi digitado pelo usu·rio no BD ...
    $sql = "SELECT id_concorrente 
            FROM `concorrentes` 
            WHERE `nome` = '$_POST[txt_nome]' 
            AND `id_concorrente` <> '$_POST[id_concorrente]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Concorrente n„o existente, posso alterar ...
        $data_usa = data::datatodate($_POST['txt_data'], '-');
        $sql = "UPDATE `concorrentes` SET `nome` = '$_POST[txt_nome]', `lista_preco_origem` = '$_POST[txt_lista_preco_origem]', `data` = '$data_usa', `fonte_pesquisa` = '$_POST[txt_fonte_pesquisa]', `condicao` = '$_POST[txt_condicao]', `observacao` = '$_POST[txt_observacao]' WHERE `id_concorrente` = '$id_concorrente' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Concorrente j· existente ...
        $valor = 2;
    }
}

//Trago dados do Concorrente passado por par‚metro ...
$sql = "SELECT * 
        FROM `concorrentes` 
        WHERE `id_concorrente` = '$id_concorrente' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<title>.:: Alterar Concorrente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Nome do Concorrente ...
    if(!texto('form', 'txt_nome', '1', '·ÈÌÛ˙¡…Õ”⁄„ı√’Á«‚ÍÓÙ˚¬ Œ‘€abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-/._()0123456789+ ', 'NOME DO CONCORRENTE', '2')) {
        return false
    }
//Lista de PreÁo Origem ...
    if(!texto('form', 'txt_lista_preco_origem', '1', '·ÈÌÛ˙¡…Õ”⁄„ı√’Á«‚ÍÓÙ˚¬ Œ‘€abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-/._()0123456789+% ', 'ORIGEM DA LISTA DE PRE«O', '1')) {
        return false
    }
//Data ...
    if(!data('form', 'txt_data', "4000", 'LISTA DO CONCORRENTE')) {
        return false
    }
//Fonte de Pesquisa ...
    if(!texto('form', 'txt_fonte_pesquisa', '1', '·ÈÌÛ˙¡…Õ”⁄„ı√’Á«‚ÍÓÙ˚¬ Œ‘€abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-/._()0123456789+ ', 'FONTE DE PESQUISA', '1')) {
        return false
    }
//CondiÁ„o ...
    if(!texto('form', 'txt_condicao', '1', '·ÈÌÛ˙¡…Õ”⁄„ı√’Á«‚ÍÓÙ˚¬ Œ‘€abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-/._()0123456789+ ', 'CONDI«√O', '1')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_nome.focus()'>
<form name='form' method="post" action='' onsubmit='return validar()'>
<input type='hidden' name='id_concorrente' value='<?=$id_concorrente;?>'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Concorrente
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Nome:</b>
        </td>
        <td>
            <input type='text' name="txt_nome" value="<?=$campos[0]['nome'];?>" title="Digite o Nome" size="50" maxlength="50" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Lista PreÁo Origem:</b>
        </td>
        <td>
            <input type='text' name="txt_lista_preco_origem" value="<?=$campos[0]['lista_preco_origem'];?>" title="Digite a Origem da Lista de PreÁo" size="50" maxlength="50" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data:</b>
        </td>
        <td>
        <?
            $data_normal = data::datetodata($campos[0]['data'], '/');
        ?>
            <input type='text' name="txt_data" value="<?=$data_normal;?> "title="Digite a Data" size="13" maxlength="10" onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            &nbsp;<img src="../../../imagem/calendario.gif" width="10" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../calendario/calendario.php?campo=txt_data&tipo_retorno=1', 'CALEND¡RIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Fonte da Pesquisa:</b>
        </td>
        <td>
            <input type='text' name="txt_fonte_pesquisa" value="<?=$campos[0]['fonte_pesquisa'];?>" title="Digite a Fonta de Pesquisa" size="50" maxlength="50" onkeyup="" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>CondiÁ„o:</b>
        </td>
        <td> 
            <input type='text' name="txt_condicao" value="<?=$campos[0]['condicao'];?>" title="Digite a CondiÁ„o do Concorrente" size="50" maxlength="50" onkeyup="" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            ObservaÁ„o:
        </td>
        <td>
            <textarea name='txt_observacao' cols='63' rows='8' maxlength='500' class='caixadetexto'><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align="center">
        <td colspan="2">
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'concorrentes.php'" class="botao">
            <input type="reset" name="cmd_redefinir" value="Redefinir" title="Redefinir" style="color:#ff9900;" onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_nome.focus()" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>