<?
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/rh/holerith/gerenciar_folha/gerenciar_folha.php', '../../../../../');
$mensagem[1] = "<font class='confirmacao'>OBSERVAÇÃO ALTERADA COM SUCESSO.</font>";

if(!empty($_POST['txt_observacao'])) {
    $observacao = strtolower($_POST['txt_observacao']);
    $sql = "UPDATE `funcionarios_vs_holeriths` SET `observacao` = '$observacao' WHERE `id_funcionario_vs_holerith` = '$_POST[id_funcionario_vs_holerith]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}

$id_funcionario_vs_holerith = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_funcionario_vs_holerith'] : $_GET['id_funcionario_vs_holerith'];
/*****************************************************************************************************/
//Busca dos Dados do Funcionário com o id_funcionario passado por parâmetro ...
$sql = "SELECT f.nome, fh.observacao 
	FROM `funcionarios` f 
	INNER JOIN `funcionarios_vs_holeriths` fh ON fh.id_funcionario = f.id_funcionario 
	WHERE fh.`id_funcionario_vs_holerith` = '$id_funcionario_vs_holerith' LIMIT 1 ";
$campos = bancos::sql($sql);
/*****************************************************************************************************/
?>
<html>
<head>
<title>.:: Observação do Funcionário ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    if(document.form.txt_observacao.value == '') {
        alert('DIGITE A OBSERVAÇÃO !')
        document.form.txt_observacao.focus()
        return false
    }
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.document.form.submit()
}
</Script>
</head>
<body onload='document.form.txt_observacao.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onSubmit='return validar()'>
<input type='hidden' name='id_funcionario_vs_holerith' value='<?=$id_funcionario_vs_holerith;?>'>
<input type='hidden' name='nao_atualizar'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Observação do Funcionário: 
            <font color='yellow'>
                <?=$campos[0]['nome'];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Observação:</b>
        </td>
        <td>
            <textarea name='txt_observacao' title='Digite a Observação' maxlength='255' cols='85' rows='3' class='caixadetexto'><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_observacao.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_registrar' value='Registrar' title='Registrar' onclick="document.form.passo.value=''" style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>