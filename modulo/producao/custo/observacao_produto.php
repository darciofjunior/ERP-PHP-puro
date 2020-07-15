<?
require('../../../lib/segurancas.php');
session_start('funcionarios');

if($tela == 1) {//Veio da tela de Todos os P.A.
    segurancas::geral('/erp/albafer/modulo/producao/custo/industrial/pa_componente_todos.php', '../../../');
}else if($tela == 2) {//Veio da tela dos P.A. do Tipo Esp.
    segurancas::geral('/erp/albafer/modulo/producao/custo/industrial/pa_componente_esp.php', '../../../');
}
$mensagem[1] = "<font class='confirmacao'>OBSERVAÇÃO DO PRODUTO ALTERADA COM SUCESSO.</font>";

if(isset($_POST[txt_observacao_produto])) {
    $sql = "UPDATE `produtos_acabados` SET `observacao` = '$_POST[txt_observacao_produto]' WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}

$id_produto_acabado = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_produto_acabado'] : $_GET['id_produto_acabado'];

$sql = "SELECT referencia, discriminacao, observacao 
        FROM `produtos_acabados` 
        WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Observação do Produto ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    //Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) window.opener.document.form.submit()
}
</Script>
</head>
<body onload='document.form.txt_observacao_produto.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_produto_acabado' value="<?=$id_produto_acabado;?>">
<input type='hidden' name='nao_atualizar'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'> 
        <td colspan='2'>
            Alterar Observação do Produto
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='2'>
            <font color="#FFFF00">Ref.:</font> 
                <?=$campos[0]['referencia'];?>
                <font color="#FFFF00">Discrim.:</font> 
                    <?=$campos[0]['discriminacao'];?>
                </font>
            <font color='#FFFFFF' size='-1'>&nbsp;</font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>Observação do Produto:</td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <textarea name='txt_observacao_produto' cols='85' rows='3' title='Digite a Observa&ccedil;&atilde;o do Produto' class='caixadetexto'><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick='document.form.txt_observacao_produto.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>